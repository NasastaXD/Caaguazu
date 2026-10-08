<?php
/**
 * Dónde caen las acciones del panel: guardar, enviar, aprobar, subir, invitar.
 *
 * Existe por un motivo concreto, no por prolijidad. Hasta acá esto colgaba de
 * `admin-post.php` y de `wp_ajax_*`, y `wp_ajax_*` —sin `nopriv`— sólo corre
 * para **usuarios de WordPress**. Un promotor con cuenta de `caaguazu-cuentas`
 * y sin usuario de WP recibía `0` en cada guardado. Lo mismo los nonces:
 * `wp_create_nonce()` se firma con el usuario de WP, no con la cuenta.
 *
 * El panel tiene su propio sistema de cuentas, así que tiene su propia puerta.
 * Dos, en realidad, las dos bajo `/turismo-panel` y las dos autenticadas con la
 * cuenta y nada más:
 *
 *     POST /turismo-panel/accion/{nombre}   ← un formulario; redirige de vuelta
 *     POST /turismo-panel/datos/{nombre}    ← el JavaScript; responde JSON
 *
 * Nadie que use el panel pasa por una pantalla de WordPress, ni necesita
 * tener un usuario de WordPress.
 */

defined( 'ABSPATH' ) || exit;

final class PROMOTUR_Acciones {

	/** Cada cuánto rota el token de formulario. */
	const VENTANA = 43200; // 12 h.

	/** @var PROMOTUR_Acciones|null */
	private static $instance = null;

	/**
	 * tipo => nombre => array( 'cb' => callable, 'cap' => string )
	 *
	 * @var array
	 */
	private static $registro = array( 'accion' => array(), 'datos' => array() );

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/* ----------------------------------------------------------------------
	 * Registro
	 * -------------------------------------------------------------------- */

	/**
	 * Un formulario. El handler termina redirigiendo.
	 *
	 * @param string   $nombre   [a-z0-9_-]
	 * @param callable $callback
	 * @param string   $cap      capability del panel, o '' si el handler la revisa.
	 */
	public static function formulario( $nombre, $callback, $cap = '' ) {
		self::$registro['accion'][ $nombre ] = array( 'cb' => $callback, 'cap' => $cap );
	}

	/**
	 * Una acción del JavaScript. El handler responde JSON.
	 */
	public static function datos( $nombre, $callback, $cap = '' ) {
		self::$registro['datos'][ $nombre ] = array( 'cb' => $callback, 'cap' => $cap );
	}

	/* ----------------------------------------------------------------------
	 * Token
	 *
	 * Mismo mecanismo que un nonce —HMAC con ventana— pero firmado con la
	 * **cuenta**, no con el usuario de WordPress. Para los formularios de
	 * acceso (todavía no hay cuenta) la firma va con cuenta 0, que es
	 * exactamente lo que hace WordPress con un visitante anónimo.
	 * -------------------------------------------------------------------- */

	private static function ventana() {
		return (int) floor( time() / self::VENTANA );
	}

	private static function firmar( $ambito, $ventana ) {
		$cuenta = function_exists( 'caaguazu_account_id' ) ? (int) caaguazu_account_id() : 0;
		return hash_hmac( 'sha256', $ambito . '|' . $cuenta . '|' . $ventana, wp_salt( 'auth' ) );
	}

	/**
	 * @param string $ambito Agrupa tokens; 'panel' sirve para todo el panel.
	 */
	public static function token( $ambito = 'panel' ) {
		return self::firmar( $ambito, self::ventana() );
	}

	/**
	 * Vale el de la ventana actual y el de la anterior: una pestaña abierta
	 * hace rato tiene que poder guardar.
	 */
	public static function token_valido( $token, $ambito = 'panel' ) {
		if ( ! is_string( $token ) || '' === $token ) {
			return false;
		}
		$v = self::ventana();
		return hash_equals( self::firmar( $ambito, $v ), $token )
			|| hash_equals( self::firmar( $ambito, $v - 1 ), $token );
	}

	/** El token que viene en el pedido, sea campo o cabecera. */
	private static function token_del_pedido() {
		if ( isset( $_POST[ self::CAMPO_TOKEN ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return sanitize_text_field( wp_unslash( $_POST[ self::CAMPO_TOKEN ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		if ( isset( $_SERVER['HTTP_X_PROMOTUR_TOKEN'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_PROMOTUR_TOKEN'] ) );
		}
		return '';
	}

	/* ----------------------------------------------------------------------
	 * URLs y campos, para las plantillas
	 * -------------------------------------------------------------------- */

	public static function url( $nombre, $tipo = 'accion' ) {
		return promotur_url( $tipo . '/' . $nombre );
	}

	/**
	 * Nombre del campo oculto de seguridad.
	 *
	 * Es una constante y no una cadena suelta para que se pueda comprobar que
	 * NINGUNA query var del router se llame igual. Ese choque ya rompió algo:
	 * la query var de la invitación se llamaba también `promotur_token`, y
	 * `WP::parse_request()` le da prioridad a `$_POST` sobre lo que matcheó la
	 * regla de reescritura — así que al enviar el formulario de alta, el HMAC
	 * de este campo pisaba el token de la invitación y el registro fallaba con
	 * «necesitás una invitación válida». Ver `tools/verificar-rutas.php`.
	 */
	const CAMPO_TOKEN = 'promotur_token';

	/** El único campo oculto que un formulario del panel necesita. */
	public static function campos( $ambito = 'panel' ) {
		printf(
			'<input type="hidden" name="%s" value="%s">',
			esc_attr( self::CAMPO_TOKEN ),
			esc_attr( self::token( $ambito ) )
		);
	}

	/* ----------------------------------------------------------------------
	 * Despacho
	 * -------------------------------------------------------------------- */

	/**
	 * @param string $tipo   'accion' | 'datos'
	 * @param string $nombre
	 */
	public function despachar( $tipo, $nombre ) {
		$json = ( 'datos' === $tipo );

		if ( ! isset( self::$registro[ $tipo ][ $nombre ] ) ) {
			$this->cortar( $json, __( 'Esa acción no existe.', 'caaguazu-portal' ), 404 );
		}

		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '' ) ) {
			$this->cortar( $json, __( 'Esa acción sólo acepta envíos.', 'caaguazu-portal' ), 405 );
		}

		$sesion = caaguazu_is_logged_in()
			|| ( function_exists( 'caaguazu_wp_admin_bypass' ) && caaguazu_wp_admin_bypass() );
		if ( ! $sesion ) {
			if ( $json ) {
				$this->cortar( true, __( 'Tu sesión venció. Volvé a entrar.', 'caaguazu-portal' ), 401 );
			}
			wp_safe_redirect( promotur_url( 'login' ) );
			exit;
		}

		if ( ! self::token_valido( self::token_del_pedido() ) ) {
			$this->cortar( $json, __( 'Tu sesión venció. Recargá la página.', 'caaguazu-portal' ), 403 );
		}

		$entrada = self::$registro[ $tipo ][ $nombre ];
		if ( $entrada['cap'] && ! promotur_can( $entrada['cap'] ) ) {
			$this->cortar( $json, __( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ), 403 );
		}

		call_user_func( $entrada['cb'] );

		// Un handler de formulario que no redirigió: lo devolvemos al panel.
		if ( ! $json ) {
			wp_safe_redirect( promotur_url( 'panel' ) );
		}
		exit;
	}

	private function cortar( $json, $mensaje, $codigo ) {
		if ( $json ) {
			wp_send_json_error( array( 'message' => $mensaje ), $codigo );
		}
		wp_die( esc_html( $mensaje ), '', array( 'response' => $codigo ) );
	}
}
