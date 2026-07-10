<?php
/**
 * Restricción de bloques, template por defecto y limpieza de settings del
 * editor — todo vía APIs/filtros públicos de WordPress, nada de hacks al core.
 *
 * @package Caaguazu_Editor_UX
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZU_Editor_Settings {

	/**
	 * Bloques permitidos en Entradas: una nota editorial/cívica, no una
	 * landing. Nada de columns/group/cover/buttons/spacer ni bloques
	 * experimentales.
	 */
	const ALLOWED_BLOCKS = array(
		'core/paragraph',
		'core/heading',
		'core/image',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/separator',
		'core/embed',
		'core/video',
		'core/gallery',
	);

	public function __construct() {
		add_filter( 'allowed_block_types_all', array( $this, 'restrict_allowed_blocks' ), 10, 2 );
		add_filter( 'register_post_type_args', array( $this, 'set_default_template' ), 10, 2 );
		add_filter( 'block_editor_settings_all', array( $this, 'calm_editor_settings' ), 10, 2 );

		// Menos ruido en el inserter: sin patrones remotos del directorio de
		// WordPress.org ni patrones "core" (layouts que no aplican a una nota).
		add_filter( 'should_load_remote_block_patterns', '__return_false' );
		add_action( 'init', array( $this, 'declutter_post_type' ), 100 );
	}

	public function restrict_allowed_blocks( $allowed_blocks, $context ) {
		if ( isset( $context->post ) && CZU_POST_TYPE === get_post_type( $context->post ) ) {
			return self::ALLOWED_BLOCKS;
		}
		return $allowed_blocks;
	}

	/**
	 * Template inicial para Entradas nuevas: una guía de estructura (bajada +
	 * cuerpo), no una jaula — sin template_lock, se puede agregar, quitar o
	 * reordenar bloques libremente.
	 */
	public function set_default_template( $args, $post_type ) {
		if ( CZU_POST_TYPE !== $post_type ) {
			return $args;
		}

		$args['template'] = array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Escribí una bajada breve (1–2 oraciones) que resuma la nota…', 'caaguazu-editor-ux' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Continuá acá con el cuerpo principal de la nota…', 'caaguazu-editor-ux' ),
				),
			),
		);

		return $args;
	}

	/**
	 * Menos opciones de color/tipografía sueltas en el sidebar de bloque:
	 * el editor no usa theme.json, así que esto reemplaza los antiguos
	 * add_theme_support('disable-custom-*') para Entradas únicamente.
	 */
	public function calm_editor_settings( $settings, $context ) {
		if ( ! isset( $context->post ) || CZU_POST_TYPE !== get_post_type( $context->post ) ) {
			return $settings;
		}

		$settings['disableCustomColors']    = true;
		$settings['disableCustomGradients'] = true;
		$settings['disableCustomFontSizes'] = true;

		return $settings;
	}

	/**
	 * Saca ruido nativo de Entradas:
	 * - Metabox "Campos personalizados" (key/value crudo): no lo usa ningún
	 *   módulo — caaguazu-modulos agrega sus propios metaboxes con
	 *   add_meta_box, que no dependen de este 'supports'.
	 * - Soporte de "core-block-patterns": sin patrones de layout genéricos
	 *   en el inserter de una nota.
	 */
	public function declutter_post_type() {
		remove_post_type_support( CZU_POST_TYPE, 'custom-fields' );
		remove_theme_support( 'core-block-patterns' );
	}
}
