<?php
/**
 * Conocimiento del asistente, desde el panel.
 *
 * El asistente vive en `caaguazu-app-api`. Lo que sabe además de lo publicado
 * —el «conocimiento»— se edita acá, para los profesores, sin pasar por
 * wp-admin. Las claves, los proveedores y la personalidad siguen en wp-admin:
 * el panel no las ve ni las toca.
 *
 * Como las demás conexiones con otro plugin, pregunta por los MÉTODOS que usa,
 * no por la clase. Si la API del sitio es vieja o no está, la sección dice que
 * el asistente no está disponible en vez de romperse.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Asistente_Panel {

	private static $instance = null;

	/** Capability que gatea la sección: el Profesor y el admin. */
	const CAP = 'promotur_manage_asistente';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'save_asistente', array( $this, 'handle_save' ) );
	}

	/**
	 * ¿La API del sitio trae lo que esta sección usa? (0.9.1 o posterior.)
	 *
	 * @return bool
	 */
	public static function disponible() {
		return method_exists( 'CZUAPI_Asistente', 'set_conocimiento' )
			&& method_exists( 'CZUAPI_Asistente', 'conocimiento' );
	}

	/** @return string El conocimiento tal como está guardado, o '' si no hay API. */
	public static function conocimiento() {
		return self::disponible() ? (string) CZUAPI_Asistente::conocimiento() : '';
	}

	public function handle_save() {
		if ( ! self::disponible() || ! caaguazu_account_can( 'promotor', self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
		}

		$texto = isset( $_POST['conocimiento'] ) ? wp_unslash( $_POST['conocimiento'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		CZUAPI_Asistente::set_conocimiento( $texto );

		// Registrado: cambiar lo que el asistente dice es de quien lo cambió.
		PROMOTUR_Audit::log( 'asistente_conocimiento', array(
			'payload' => array( 'caracteres' => mb_strlen( (string) $texto ) ),
		) );

		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
		wp_safe_redirect( promotur_url( 'panel/asistente' ) );
		exit;
	}
}
