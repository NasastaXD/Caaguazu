<?php
/**
 * Bootstrap: theme support + encolado de estilos/scripts del editor.
 *
 * @package Caaguazu_Editor_UX
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZU_Editor_UX {

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'add_editor_style_support' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Activa el theme support 'editor-styles': WP envuelve el canvas en
	 * .editor-styles-wrapper (selector que usa assets/css/editor-content.css)
	 * y respeta disableCustomColors/FontSizes/Gradients. No requiere que el
	 * theme lo declare — un plugin puede activarlo igual, y no afecta el
	 * frontend en absoluto (sólo la pantalla de edición).
	 */
	public function add_editor_style_support() {
		add_theme_support( 'editor-styles' );
	}

	public function enqueue_editor_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		wp_enqueue_style(
			'czu-editor-ui',
			CZU_URI . 'assets/css/editor-ui.css',
			array( 'wp-edit-post' ),
			CZU_VERSION
		);

		// Mismas fuentes que el frontend (assets/css/main.css del theme) para
		// que el canvas se sienta parte del mismo sitio.
		wp_enqueue_style(
			'czu-editor-fonts',
			'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'czu-editor-content',
			CZU_URI . 'assets/css/editor-content.css',
			array( 'czu-editor-fonts' ),
			CZU_VERSION
		);

		$deps = array(
			'wp-plugins',
			'wp-edit-post',
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-i18n',
			'wp-dom-ready',
		);

		wp_enqueue_script(
			'czu-preview',
			CZU_URI . 'assets/js/preview.js',
			$deps,
			CZU_VERSION,
			true
		);

		wp_enqueue_script(
			'czu-editor-plugin',
			CZU_URI . 'assets/js/editor-plugin.js',
			array_merge( $deps, array( 'czu-preview' ) ),
			CZU_VERSION,
			true
		);

		wp_localize_script( 'czu-editor-plugin', 'czuEditorUX', array(
			'postType'      => isset( $screen->post_type ) ? $screen->post_type : '',
			'supportedType' => CZU_POST_TYPE,
		) );

		wp_set_script_translations( 'czu-editor-plugin', 'caaguazu-editor-ux' );
	}
}
