<?php
/**
 * Autenticación del portal: login, registro (con token de invitación),
 * recuperar/restablecer contraseña y salir.
 *
 * Corre enteramente sobre el sistema de cuentas universal (caaguazu-cuentas,
 * plugin hermano) — ninguna persona del panel tiene ya un usuario de
 * WordPress. Los administradores siguen entrando por wp-login.php/wp-admin
 * como siempre (ver PROMOTUR_Router::maybe_block_wp_login()); esta clase no
 * los toca.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Auth {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'invite', array( $this, 'handle_create_invite' ) );
		// Invite-only: se desactiva el alta nativa de WordPress (el registro de
		// cuentas ya no pasa por wp_insert_user en absoluto).
		add_filter( 'option_users_can_register', '__return_zero' );

		// Auditoría de login: wp_login/wp_login_failed (WP) ya no se disparan
		// para los promotores, que ahora inician sesión por caaguazu-cuentas.
		add_action( 'caaguazu_cuentas_logged_in', array( $this, 'audit_login_success' ) );
		add_action( 'caaguazu_cuentas_login_failed', array( $this, 'audit_login_failed' ) );
	}

	/**
	 * Genera un link de invitación, desde la sección Equipo del panel.
	 *
	 * No manda el enlace por el flash: un transient de 60 segundos que se
	 * borra al leerse es plata contada para copiar una URL larga en el
	 * teléfono, y si la página se recarga antes de copiarla se pierde sin
	 * dejar rastro (aunque la invitación siga válida). El enlace se reconstruye
	 * desde la metadata cada vez que hace falta —ver
	 * PROMOTUR_Invitations::plain_token()— así que la lista de «Invitaciones
	 * abiertas» lo muestra de nuevo cada vez que se entra a la pantalla, con
	 * su propio botón de copiar, no sólo la primera vez.
	 */
	public function handle_create_invite() {
		if ( ! caaguazu_account_can( 'promotor', 'promotur_manage_team' ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ) );
		}
		$role = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : 'promotur_mini';
		// Vacío o 0 en cualquiera de los dos es la elección explícita de «sin
		// límite» —create() lo entiende igual—, no un valor que haya que
		// completar con un default.
		$dias     = isset( $_POST['expires_days'] ) ? (int) $_POST['expires_days'] : 0;
		$usos_max = isset( $_POST['max_usos'] ) ? (int) $_POST['max_usos'] : 1;
		$tokens   = PROMOTUR_Invitations::create( array( 'role' => $role, 'expires_days' => $dias, 'max_usos' => $usos_max, 'count' => 1 ) );

		// Si no se guardó, se dice acá y no se descubre cuando alguien abre un
		// enlace que no existe. Ver el porqué en PROMOTUR_Invitations::create().
		if ( empty( $tokens ) ) {
			promotur_flash( __( 'No se pudo crear la invitación: la base de datos rechazó el registro. Avisale a quien administra el sitio.', 'caaguazu-portal' ), 'error' );
		} else {
			promotur_flash( __( 'Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».', 'caaguazu-portal' ), 'success' );
		}
		wp_safe_redirect( promotur_url( 'panel/equipo' ) );
		exit;
	}

	/**
	 * Auditoría: login exitoso de una cuenta (caaguazu-cuentas).
	 *
	 * @param array $account
	 */
	public function audit_login_success( $account ) {
		if ( ! class_exists( 'PROMOTUR_Audit' ) ) { return; }
		$id = (int) $account['id'];
		PROMOTUR_Audit::log( 'login_success', array( 'user_id' => $id, 'entity_type' => 'account', 'entity_id' => $id ) );
	}

	/**
	 * Auditoría: intento de login fallido (email inexistente, contraseña
	 * incorrecta, o cuenta suspendida/inactiva).
	 *
	 * @param string     $email
	 * @param array|null $account
	 */
	public function audit_login_failed( $email, $account = null ) {
		if ( ! class_exists( 'PROMOTUR_Audit' ) ) { return; }
		PROMOTUR_Audit::log( 'login_failed', array(
			'user_id'     => $account ? (int) $account['id'] : 0,
			'entity_type' => 'account',
			'entity_id'   => $account ? (int) $account['id'] : null,
			'payload'     => array( 'email' => substr( (string) $email, 0, 190 ) ),
		) );
	}

	/**
	 * Renderiza (y procesa) una pantalla de auth.
	 *
	 * @param string $route login|registro|recuperar|restablecer
	 */
	public function render( $route ) {
		// Ya logueado: a /czu-login o /registro no tiene sentido entrar.
		if ( caaguazu_is_logged_in() && in_array( $route, array( 'login', 'registro' ), true ) ) {
			wp_safe_redirect( $this->safe_next() );
			exit;
		}

		$vars = array( 'error' => '', 'notice' => '', 'next' => $this->raw_next(), 'token' => '' );

		switch ( $route ) {
			case 'login':       $vars = $this->process_login( $vars ); break;
			case 'registro':    $vars = $this->process_register( $vars ); break;
			case 'recuperar':   $vars = $this->process_recover( $vars ); break;
			case 'restablecer': $vars = $this->process_reset( $vars ); break;
		}

		promotur_template( 'auth/' . $route, $vars );
	}

	/* --------------------------------------------------------------------- */

	private function raw_next() {
		return isset( $_REQUEST['next'] ) ? esc_url_raw( wp_unslash( $_REQUEST['next'] ) ) : '';
	}

	/**
	 * Destino seguro post-login (whitelist al propio host).
	 */
	private function safe_next() {
		$next = $this->raw_next();
		if ( $next ) {
			$validated = wp_validate_redirect( $next, '' );
			if ( $validated ) {
				return $validated;
			}
		}
		return promotur_url( 'panel' );
	}

	/**
	 * El token de los formularios de acceso. Mismo mecanismo que el resto del
	 * panel; acá todavía no hay cuenta, así que se firma con cuenta 0 — que es
	 * lo mismo que hace WordPress con un visitante anónimo, pero sin depender
	 * de que exista un usuario de WordPress.
	 */
	private function verify( $action ) {
		$campo = PROMOTUR_Acciones::CAMPO_TOKEN;
		$token = isset( $_POST[ $campo ] ) ? sanitize_text_field( wp_unslash( $_POST[ $campo ] ) ) : '';
		return PROMOTUR_Acciones::token_valido( $token, $action );
	}

	/* ----- Login ----- */
	private function process_login( $vars ) {
		if ( empty( $_POST['promotur_auth'] ) || 'login' !== $_POST['promotur_auth'] ) {
			return $vars;
		}
		if ( ! $this->verify( 'promotur_login' ) ) {
			$vars['error'] = __( 'Tu sesión venció. Recargá la página.', 'caaguazu-portal' );
			return $vars;
		}
		$email    = sanitize_email( wp_unslash( $_POST['user_login'] ?? '' ) );
		$password = (string) ( $_POST['user_pass'] ?? '' );
		$remember = ! empty( $_POST['remember'] );

		$account = caaguazu_account_login( $email, $password, $remember );
		if ( is_wp_error( $account ) ) {
			$vars['error'] = $account->get_error_message();
			return $vars;
		}
		wp_safe_redirect( $this->safe_next() );
		exit;
	}

	/**
	 * Un alta que no salió, anotada en el registro de auditoría.
	 *
	 * Existe porque un alta fallida no dejaba ninguna huella: la pantalla
	 * mostraba el error a quien lo estaba sufriendo y ahí moría. Cuando el
	 * campo de seguridad pisó al token de la invitación (ver la constante
	 * `PROMOTUR_Acciones::CAMPO_TOKEN`), en wp-admin se veía la invitación
	 * creada y después nada, como si nadie hubiera intentado usarla — y era
	 * justo al revés. El motivo y los primeros caracteres del token que llegó
	 * alcanzan para distinguir «no la usaron» de «la usaron y se rompió».
	 *
	 * @param array  $vars
	 * @param string $motivo  Clave corta, para poder buscarla.
	 * @param string $mensaje Lo que ve la persona.
	 * @param array  $extra   Contexto, sin datos personales.
	 * @return array
	 */
	private function fallo_registro( $vars, $motivo, $mensaje, $extra = array() ) {
		$vars['error'] = $mensaje;
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'registro_fallido', array(
				'user_id'     => 0,
				'entity_type' => 'invitation',
				'payload'     => array_merge( array( 'motivo' => $motivo ), $extra ),
			) );
		}
		return $vars;
	}

	/* ----- Registro (INVITE-ONLY) ----- */
	private function process_register( $vars ) {
		// Token de invitación (de la query var o del POST).
		$token  = sanitize_text_field( get_query_var( 'promotur_invitacion' ) );
		$origen = $token ? 'url' : '';
		if ( ! $token && isset( $_REQUEST['token'] ) ) {
			$token  = sanitize_text_field( wp_unslash( $_REQUEST['token'] ) );
			$origen = $token ? 'campo' : '';
		}
		$row    = PROMOTUR_Invitations::find_by_token( $token );
		$status = PROMOTUR_Invitations::status( $row );

		$vars['token']          = $token;
		$vars['invite_status']  = $status;            // valid|agotada|expired|revoked|invalid
		$vars['invite_role']    = $row ? PROMOTUR_Roles::label( $row['role'] ) : '';
		// La clave del rol además de la etiqueta: la pantalla explica qué va a
		// poder hacer la persona, y eso depende del rol, no de cómo se llame.
		$vars['invite_role_key'] = $row ? (string) $row['role'] : '';
		$vars['invite_vence']    = ( $row && ! empty( $row['expires_at'] ) ) ? (string) $row['expires_at'] : '';

		if ( empty( $_POST['promotur_auth'] ) || 'registro' !== $_POST['promotur_auth'] ) {
			return $vars;
		}
		if ( ! $this->verify( 'promotur_registro' ) ) {
			return $this->fallo_registro( $vars, 'sesion_vencida', __( 'Tu sesión venció. Recargá la página.', 'caaguazu-portal' ) );
		}
		// Sólo con invitación válida (invite-only).
		if ( 'valid' !== $status ) {
			/*
			 * El estado dice por qué no sirve, y el prefijo del token con su
			 * origen dice QUÉ llegó: si el token viene de la URL y aun así es
			 * `invalid`, o si su prefijo no se parece a un token nuestro, lo
			 * que falla no es la invitación sino lo que la transporta.
			 */
			return $this->fallo_registro(
				$vars,
				'invitacion_' . $status,
				__( 'Necesitás una invitación válida para registrarte.', 'caaguazu-portal' ),
				array(
					'token'  => substr( (string) $token, 0, 8 ),
					'largo'  => strlen( (string) $token ),
					'origen' => $origen,
				)
			);
		}

		$display_name = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
		$email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$phone        = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$pass         = (string) ( $_POST['user_pass'] ?? '' );

		if ( ! $display_name || ! is_email( $email ) || '' === $phone || ! Caaguazu_Cuentas_Passwords::is_valid( $pass ) ) {
			return $this->fallo_registro(
				$vars,
				'datos_incompletos',
				__( 'Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.', 'caaguazu-portal' ),
				array( 'invitacion' => (int) $row['id'] )
			);
		}
		if ( Caaguazu_Cuentas_Accounts::email_exists( $email ) ) {
			return $this->fallo_registro(
				$vars,
				'email_duplicado',
				__( 'Ese email ya está registrado.', 'caaguazu-portal' ),
				array( 'invitacion' => (int) $row['id'] )
			);
		}

		$role = array_key_exists( $row['role'], PROMOTUR_Roles::roles() ) ? $row['role'] : 'promotur_visitante';

		$account = Caaguazu_Cuentas_Auth::instance()->register( array(
			'email'        => $email,
			'password'     => $pass,
			'display_name' => $display_name,
			'phone'        => $phone,
		) );
		if ( is_wp_error( $account ) ) {
			return $this->fallo_registro(
				$vars,
				'alta_rechazada',
				$account->get_error_message(),
				array( 'invitacion' => (int) $row['id'], 'codigo' => $account->get_error_code() )
			);
		}
		$account_id = (int) $account['id'];

		caaguazu_account_grant( $account_id, 'promotor', $role, null, null );
		caaguazu_account_meta_set( $account_id, 'invited_via', (int) $row['id'] );
		PROMOTUR_Invitations::mark_used( (int) $row['id'], $account_id );
		PROMOTUR_Audit::log( 'account_registered', array( 'entity_type' => 'account', 'entity_id' => $account_id, 'payload' => array( 'role' => $role ) ) );

		wp_safe_redirect( $this->safe_next() );
		exit;
	}

	/* ----- Recuperar (solicitar) ----- */
	private function process_recover( $vars ) {
		if ( empty( $_POST['promotur_auth'] ) || 'recuperar' !== $_POST['promotur_auth'] ) {
			return $vars;
		}
		if ( ! $this->verify( 'promotur_recuperar' ) ) {
			$vars['error'] = __( 'Tu sesión venció. Recargá la página.', 'caaguazu-portal' );
			return $vars;
		}
		$email = sanitize_email( wp_unslash( $_POST['user_login'] ?? '' ) );
		Caaguazu_Cuentas_Auth::instance()->request_reset( $email, function ( $account_email, $token ) {
			// login/key: mismos nombres de campo que espera templates/auth/restablecer.php.
			return add_query_arg(
				array( 'login' => rawurlencode( $account_email ), 'key' => rawurlencode( $token ) ),
				promotur_url( 'recuperar/restablecer' )
			);
		} );
		// No revelamos si el email existe: mensaje siempre genérico.
		$vars['notice'] = __( 'Si la cuenta existe, te enviamos un email con las instrucciones.', 'caaguazu-portal' );
		return $vars;
	}

	/* ----- Restablecer (con login+key: mismos nombres que usa el template) ----- */
	private function process_reset( $vars ) {
		// $_REQUEST cubre tanto el GET del link del email como el POST del
		// formulario (que reenvía login/key como campos ocultos).
		$email = sanitize_email( wp_unslash( $_REQUEST['login'] ?? '' ) );
		$token = sanitize_text_field( wp_unslash( $_REQUEST['key'] ?? '' ) );
		$vars['login'] = $email;
		$vars['key']   = $token;

		$check = ( $email && $token ) ? Caaguazu_Cuentas_Auth::instance()->check_reset( $email, $token ) : new WP_Error( 'missing', '' );
		$vars['valid_key'] = ! is_wp_error( $check );

		if ( empty( $_POST['promotur_auth'] ) || 'restablecer' !== $_POST['promotur_auth'] ) {
			if ( $email && $token && is_wp_error( $check ) ) {
				$vars['error'] = __( 'El enlace para restablecer la contraseña venció o no es válido.', 'caaguazu-portal' );
			}
			return $vars;
		}
		if ( ! $this->verify( 'promotur_restablecer' ) ) {
			$vars['error'] = __( 'Tu sesión venció. Recargá la página.', 'caaguazu-portal' );
			return $vars;
		}
		$pass1  = (string) ( $_POST['pass1'] ?? '' );
		$result = Caaguazu_Cuentas_Auth::instance()->reset( $email, $token, $pass1 );
		if ( is_wp_error( $result ) ) {
			$vars['error'] = $result->get_error_message();
			return $vars;
		}
		wp_safe_redirect( promotur_url( 'login' ) . '?reset=1' );
		exit;
	}

	/* ----- Salir ----- */
	public function logout() {
		caaguazu_account_logout();
		wp_safe_redirect( promotur_url( 'login' ) );
		exit;
	}

}
