<?php
/**
 * Mi cuenta: nombre, correo, teléfono, foto y contraseña.
 *
 * Todo esto vive en `caaguazu-cuentas` —la tabla de cuentas, no la de usuarios
 * de WordPress— y hasta acá no había forma de tocarlo desde el panel: el único
 * camino era `wp-admin/profile.php`, que además edita *otra* cosa (el usuario
 * de WordPress, que un promotor ni siquiera tiene). Ahora se edita acá.
 */

defined( 'ABSPATH' ) || exit;

class PROMOTUR_Cuenta {

	/** Meta de la cuenta donde guardamos el adjunto de la foto. */
	const META_FOTO = 'promotur_foto';

	/** Meta de la cuenta con la fecha en que se subió esa foto (timestamp). */
	const META_FOTO_SUBIDA_EN = 'promotur_foto_subida_en';

	/** Nadie sube una foto de perfil más pesada que esto. */
	const FOTO_MAX_BYTES = 5 * 1024 * 1024;

	/**
	 * Cuánto dura una foto sin renovar antes de borrarse sola —salvo la de un
	 * Promotor, que no vence nunca—. Tres años: ni tan corto que borre la foto
	 * de alguien activo, ni tan largo que el servidor junte fotos de gente que
	 * ya no está.
	 */
	const FOTO_VIDA_DIAS = 1095;

	const CRON_LIMPIAR_FOTOS = 'promotur_limpiar_fotos_vencidas';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'perfil', array( $this, 'guardar_perfil' ) );
		PROMOTUR_Acciones::formulario( 'clave', array( $this, 'cambiar_clave' ) );

		add_action( self::CRON_LIMPIAR_FOTOS, array( $this, 'limpiar_fotos_vencidas' ) );
		// Se re-arma solo en cada carga si por lo que sea no está programado
		// —igual que las rewrite rules del router—, para no depender de que
		// el hook de activación llegue a correr en cada instalación.
		add_action( 'init', array( $this, 'asegurar_cron' ) );
	}

	public function asegurar_cron() {
		if ( ! wp_next_scheduled( self::CRON_LIMPIAR_FOTOS ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_LIMPIAR_FOTOS );
		}
	}

	/** ¿Hay cuenta propia? (un administrador de WP entra por bypass y no tiene). */
	private function cuenta_id() {
		return function_exists( 'caaguazu_account_id' ) ? (int) caaguazu_account_id() : 0;
	}

	private function volver( $mensaje, $tipo = 'success' ) {
		promotur_flash( $mensaje, $tipo );
		wp_safe_redirect( promotur_url( 'panel/perfil' ) );
		exit;
	}

	/* ----------------------------------------------------------------------
	 * Nombre, correo, teléfono y foto
	 * -------------------------------------------------------------------- */

	public function guardar_perfil() {
		$id = $this->cuenta_id();
		if ( ! $id ) {
			$this->volver( __( 'Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.', 'caaguazu-portal' ), 'error' );
		}

		$nombre = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
		$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$tel    = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		if ( '' === $nombre ) {
			$this->volver( __( 'Escribí tu nombre.', 'caaguazu-portal' ), 'error' );
		}
		if ( ! is_email( $email ) ) {
			$this->volver( __( 'Ese correo no parece válido.', 'caaguazu-portal' ), 'error' );
		}

		// El correo es la llave con la que se entra: no puede chocar con otra cuenta.
		$otro = Caaguazu_Cuentas_Accounts::get_by_email( $email );
		if ( $otro && (int) $otro['id'] !== $id ) {
			$this->volver( __( 'Ya hay una cuenta con ese correo.', 'caaguazu-portal' ), 'error' );
		}

		$ok = Caaguazu_Cuentas_Accounts::update( $id, array(
			'display_name' => $nombre,
			'email'        => $email,
			'phone'        => $tel,
		) );
		if ( ! $ok ) {
			$this->volver( __( 'No pudimos guardar los cambios. Probá de nuevo.', 'caaguazu-portal' ), 'error' );
		}

		$aviso = __( 'Listo, guardamos tus datos.', 'caaguazu-portal' );
		$foto  = $this->guardar_foto();
		if ( is_wp_error( $foto ) ) {
			$this->volver( $foto->get_error_message(), 'error' );
		}

		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'cuenta_editada', array( 'payload' => array( 'email' => $email ) ) );
		}
		$this->volver( $aviso );
	}

	/**
	 * La foto, si vino uno. Se guarda como adjunto y en la meta de la cuenta.
	 *
	 * @return true|WP_Error
	 */
	private function guardar_foto() {
		if ( empty( $_FILES['foto']['name'] ) ) {
			return true;
		}
		$tipo = wp_check_filetype( sanitize_file_name( wp_unslash( $_FILES['foto']['name'] ) ) );
		if ( ! in_array( $tipo['ext'], array( 'jpg', 'jpeg', 'png', 'webp' ), true ) ) {
			return new WP_Error( 'formato', __( 'La foto tiene que ser JPG, PNG o WEBP.', 'caaguazu-portal' ) );
		}
		if ( (int) $_FILES['foto']['size'] > self::FOTO_MAX_BYTES ) {
			return new WP_Error( 'tamano', __( 'La foto pesa más de 5 MB. Subí una más liviana.', 'caaguazu-portal' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$adjunto = media_handle_upload( 'foto', 0, array(), array( 'test_form' => false ) );
		if ( is_wp_error( $adjunto ) ) {
			return $adjunto;
		}
		// Que no quede a nombre del usuario 0 de WordPress.
		if ( function_exists( 'caaguazu_service_user_id' ) ) {
			wp_update_post( array( 'ID' => $adjunto, 'post_author' => caaguazu_service_user_id() ) );
		}
		caaguazu_account_meta_set( $this->cuenta_id(), self::META_FOTO, (int) $adjunto );
		caaguazu_account_meta_set( $this->cuenta_id(), self::META_FOTO_SUBIDA_EN, time() );
		return true;
	}

	/* ----------------------------------------------------------------------
	 * Retención de fotos
	 * -------------------------------------------------------------------- */

	/**
	 * Corre una vez por día (`self::CRON_LIMPIAR_FOTOS`). Borra la foto de
	 * perfil de las cuentas que la subieron hace más de `FOTO_VIDA_DIAS` y no
	 * son Promotor —Mini Promotor y Visitante, cuyo paso por el equipo suele
	 * ser más corto—. Un Promotor no tiene vencimiento: es quien de verdad
	 * usa el panel día a día.
	 *
	 * No hay tabla que liste "cuentas con esta meta": el metadata de
	 * caaguazu-cuentas es un JSON por fila, así que se recorren las cuentas
	 * que lo tienen seteado —pocas decenas en este proyecto, un recorrido
	 * diario no pesa nada— en vez de mantener un índice aparte para esto.
	 */
	public function limpiar_fotos_vencidas() {
		if ( ! class_exists( 'Caaguazu_Cuentas_Install' ) || ! class_exists( 'Caaguazu_Cuentas_Panels' ) ) {
			return;
		}
		global $wpdb;
		$tabla = Caaguazu_Cuentas_Install::tables()['accounts'];

		// Prefiltro por LIKE: descarta rápido las cuentas sin foto sin tener
		// que decodificar JSON de todas. Lo que importa lo confirma el
		// json_decode de abajo, este LIKE nunca decide solo.
		$filas = $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT id, metadata FROM {$tabla} WHERE metadata LIKE %s", '%"' . self::META_FOTO . '"%' ) // phpcs:ignore WordPress.DB
		);
		if ( ! $filas ) {
			return;
		}

		$limite = time() - self::FOTO_VIDA_DIAS * DAY_IN_SECONDS;
		foreach ( $filas as $fila ) {
			$meta = json_decode( (string) $fila->metadata, true );
			if ( ! is_array( $meta ) || empty( $meta[ self::META_FOTO ] ) ) {
				continue;
			}
			$subida = isset( $meta[ self::META_FOTO_SUBIDA_EN ] ) ? (int) $meta[ self::META_FOTO_SUBIDA_EN ] : 0;
			// Sin fecha de subida (foto de antes de esta versión): se le
			// pone la de hoy en vez de borrarla de sorpresa, y se la
			// vuelve a mirar dentro de FOTO_VIDA_DIAS.
			if ( ! $subida ) {
				caaguazu_account_meta_set( (int) $fila->id, self::META_FOTO_SUBIDA_EN, time() );
				continue;
			}
			if ( $subida > $limite ) {
				continue;
			}
			$grant = Caaguazu_Cuentas_Panels::instance()->get_grant( (int) $fila->id, 'promotor' );
			if ( $grant && 'promotur_promotor' === $grant['role'] ) {
				continue;
			}
			wp_delete_attachment( (int) $meta[ self::META_FOTO ], true );
			caaguazu_account_meta_delete( (int) $fila->id, self::META_FOTO );
			caaguazu_account_meta_delete( (int) $fila->id, self::META_FOTO_SUBIDA_EN );
		}
	}

	/* ----------------------------------------------------------------------
	 * Contraseña
	 * -------------------------------------------------------------------- */

	public function cambiar_clave() {
		$id = $this->cuenta_id();
		if ( ! $id ) {
			$this->volver( __( 'Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.', 'caaguazu-portal' ), 'error' );
		}

		$actual  = isset( $_POST['clave_actual'] ) ? (string) wp_unslash( $_POST['clave_actual'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$nueva   = isset( $_POST['clave_nueva'] ) ? (string) wp_unslash( $_POST['clave_nueva'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$repetir = isset( $_POST['clave_repetir'] ) ? (string) wp_unslash( $_POST['clave_repetir'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		$cuenta = Caaguazu_Cuentas_Accounts::get( $id );
		if ( ! $cuenta || ! Caaguazu_Cuentas_Passwords::verify( $actual, $cuenta['pass_hash'] ) ) {
			$this->volver( __( 'La contraseña actual no coincide.', 'caaguazu-portal' ), 'error' );
		}
		if ( $nueva !== $repetir ) {
			$this->volver( __( 'Las dos contraseñas nuevas tienen que ser iguales.', 'caaguazu-portal' ), 'error' );
		}

		$puesta = Caaguazu_Cuentas_Accounts::set_password( $id, $nueva );
		if ( is_wp_error( $puesta ) ) {
			$this->volver( $puesta->get_error_message(), 'error' );
		}
		if ( ! $puesta ) {
			$this->volver( __( 'No pudimos cambiar la contraseña. Probá de nuevo.', 'caaguazu-portal' ), 'error' );
		}

		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'clave_cambiada' );
		}
		$this->volver( __( 'Listo, cambiaste tu contraseña.', 'caaguazu-portal' ) );
	}

	/* ----------------------------------------------------------------------
	 * Lectura
	 * -------------------------------------------------------------------- */

	/** La foto de una cuenta, o '' si no tiene. */
	public static function foto_url( $account_id, $tamano = 'thumbnail' ) {
		$account_id = (int) $account_id;
		if ( $account_id <= 0 || ! function_exists( 'caaguazu_account_meta_get' ) ) {
			return '';
		}
		$adjunto = (int) caaguazu_account_meta_get( $account_id, self::META_FOTO, 0 );
		if ( ! $adjunto ) {
			return '';
		}
		return (string) wp_get_attachment_image_url( $adjunto, $tamano );
	}
}
