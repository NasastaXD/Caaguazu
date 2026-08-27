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
		return true;
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
