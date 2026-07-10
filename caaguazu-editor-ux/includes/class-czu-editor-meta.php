<?php
/**
 * Meta fields editoriales del panel "caaguazu.net": Fuente/referencia,
 * Responsable del contenido y Estado de verificación. Registrados con
 * show_in_rest (Gutenberg los necesita vía REST) y sanitize_callback +
 * auth_callback por capacidad. "Actualizado el" NO es un meta propio: se
 * lee directo de post_modified (WordPress ya lo mantiene solo, sin que un
 * editor tenga que acordarse de tipearlo) — ver caaguazu_render_trust_meta()
 * en caaguazu-theme/inc/helpers.php, que combina estos tres campos con esa
 * fecha nativa para el frontend.
 *
 * @package Caaguazu_Editor_UX
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZU_Editor_Meta {

	const META_FUENTE      = '_czu_fuente_referencia';
	const META_RESPONSABLE = '_czu_responsable_contenido';
	const META_ESTADO      = '_czu_estado_verificacion';

	const ESTADO_VALUES = array( 'pendiente', 'revisado', 'verificado', 'desactualizado' );

	public function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta() {
		$auth_cb = function () {
			return current_user_can( 'edit_posts' );
		};

		$text_args = array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth_cb,
		);

		$estado_args = array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => array( $this, 'sanitize_estado' ),
			'auth_callback'     => $auth_cb,
		);

		foreach ( CZU_POST_TYPES as $post_type ) {
			register_post_meta( $post_type, self::META_FUENTE, $text_args );
			register_post_meta( $post_type, self::META_RESPONSABLE, $text_args );
			register_post_meta( $post_type, self::META_ESTADO, $estado_args );
		}
	}

	/**
	 * Sólo uno de los 4 valores del select; cualquier otra cosa (incluido un
	 * intento de mandar HTML/JS por la REST API) cae al vacío = "sin estado".
	 */
	public function sanitize_estado( $value ) {
		return in_array( $value, self::ESTADO_VALUES, true ) ? $value : '';
	}
}
