<?php
/**
 * Meta fields editoriales del panel "caaguazu.net": Fuente/referencia y
 * Responsable del contenido. Registrados con show_in_rest (Gutenberg los
 * necesita vía REST) y sanitize_callback + auth_callback por capacidad.
 *
 * @package Caaguazu_Editor_UX
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZU_Editor_Meta {

	const META_FUENTE      = '_czu_fuente_referencia';
	const META_RESPONSABLE = '_czu_responsable_contenido';

	public function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta() {
		$args = array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		);

		register_post_meta( CZU_POST_TYPE, self::META_FUENTE, $args );
		register_post_meta( CZU_POST_TYPE, self::META_RESPONSABLE, $args );
	}
}
