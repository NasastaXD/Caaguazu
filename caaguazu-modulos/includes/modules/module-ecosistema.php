<?php
/**
 * Módulo Ecosistema — 3 tarjetas de sub-portales configurables desde el
 * Customizer, más la página estática "Ecosistema" que las presenta.
 *
 * Migrado desde caaguazu-theme/inc/customizer-defaults.php (función
 * caaguazu_ecosystem_defaults) e inc/customizer.php (sección "Ecosistema").
 * Reusa los helpers genéricos del Customizer que sí quedan en el theme
 * (caaguazu_add_text/url/image, ya usados por Hero/Identidad) — por eso el
 * hook de este módulo corre en `customize_register` como cualquier otro,
 * sin depender de que el theme "sepa" que este módulo existe.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_ecosystem_defaults() {
	return array(
		array(
			'tag'   => 'Turismo',
			'title' => 'Turismo',
			'body'  => 'Información sobre historia, oficio maderero, gastronomía y cultura guaraní del departamento.',
			'cta'   => 'Ver sección de Turismo',
			'url'   => function_exists( 'caaguazu_page_url' ) ? caaguazu_page_url( 'turismo' ) : home_url( '/turismo/' ),
			'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg/1280px-Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg',
		),
		array(
			'tag'   => 'cead.caaguazu.net',
			'title' => 'Centro de Estudios y Desarrollo',
			'body'  => 'Sub-portal dedicado a investigación, formación y proyectos de desarrollo sostenible para el departamento.',
			'cta'   => 'Ir al sitio del CEAD',
			'url'   => 'https://cead.caaguazu.net',
			'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'tag'   => 'Próximamente',
			'title' => 'Nuevo sub-portal',
			'body'  => 'Un nuevo espacio del ecosistema Caaguazú se encuentra en preparación y estará disponible próximamente.',
			'cta'   => 'Próximamente',
			'url'   => '',
			'image' => 'https://images.unsplash.com/photo-1519331379826-f10be5486c6f?auto=format&fit=crop&w=1400&q=80',
		),
	);
}

/**
 * Registra su propia sección en el panel "Contenido del Home" que el theme
 * ya crea. Si el theme activo no tiene ese panel (u otro theme sin panel
 * "caaguazu_home"), el Customizer igual muestra la sección, solo que sin
 * agrupar bajo ningún panel.
 */
function caaguazu_modulos_ecosistema_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'caaguazu_ecosystem', array(
		'title' => __( 'Ecosistema (3 sub-portales)', 'caaguazu-modulos' ),
		'panel' => 'caaguazu_home',
	) );

	$wp_customize->add_setting( 'eco_section_title', array(
		'default'           => 'Sub-portales del departamento',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'eco_section_title', array(
		'label'   => __( 'Título de la sección', 'caaguazu-modulos' ),
		'section' => 'caaguazu_ecosystem',
	) );
	$wp_customize->add_setting( 'eco_section_body', array(
		'default'           => 'Caaguazu.net centraliza el acceso a los sub-portales especializados del departamento. Cada uno conserva su propio contenido dentro de una misma identidad institucional.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'eco_section_body', array(
		'label'   => __( 'Descripción de la sección', 'caaguazu-modulos' ),
		'section' => 'caaguazu_ecosystem',
		'type'    => 'textarea',
	) );

	if ( ! function_exists( 'caaguazu_add_text' ) ) {
		return; // el theme activo no expone los helpers genéricos del Customizer.
	}

	$eco_defaults = caaguazu_ecosystem_defaults();
	for ( $i = 0; $i < 3; $i++ ) {
		$d = $eco_defaults[ $i ];
		caaguazu_add_text(  $wp_customize, "eco_{$i}_tag",   __( 'Tag (subdominio)', 'caaguazu-modulos' ), $d['tag'],   'caaguazu_ecosystem' );
		caaguazu_add_text(  $wp_customize, "eco_{$i}_title", __( 'Título', 'caaguazu-modulos' ),           $d['title'], 'caaguazu_ecosystem' );
		caaguazu_add_text(  $wp_customize, "eco_{$i}_body",  __( 'Descripción', 'caaguazu-modulos' ),      $d['body'],  'caaguazu_ecosystem', true );
		caaguazu_add_text(  $wp_customize, "eco_{$i}_cta",   __( 'Texto del CTA', 'caaguazu-modulos' ),    $d['cta'],   'caaguazu_ecosystem' );
		caaguazu_add_url(   $wp_customize, "eco_{$i}_url",   __( 'URL externa (vacío = "próximamente")', 'caaguazu-modulos' ), $d['url'], 'caaguazu_ecosystem' );
		caaguazu_add_image( $wp_customize, "eco_{$i}_image", __( 'Imagen', 'caaguazu-modulos' ),           $d['image'], 'caaguazu_ecosystem' );
	}
}
add_action( 'customize_register', 'caaguazu_modulos_ecosistema_customize_register' );

/**
 * Siembra la página "Ecosistema" en blanco al activar el plugin (si no
 * existe todavía) — page.php del theme le pinta el hero default.
 */
function caaguazu_modulos_seed_ecosistema_page() {
	if ( get_page_by_path( 'ecosistema' ) ) {
		return;
	}
	wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => __( 'Ecosistema', 'caaguazu-modulos' ),
		'post_name'   => 'ecosistema',
	) );
}

/**
 * Mismo problema que ya se resolvió para las demás páginas del theme:
 * `register_activation_hook` solo corre al activar ESTE plugin. Un sitio
 * que ya lo tenga activo y reciba una actualización nunca vuelve a
 * disparar ese hook, así que además hay un catch-up en `admin_init`.
 */
function caaguazu_modulos_catch_up_ecosistema() {
	if ( get_option( 'caaguazu_modulos_ecosistema_caught_up' ) ) {
		return;
	}
	caaguazu_modulos_seed_ecosistema_page();
	update_option( 'caaguazu_modulos_ecosistema_caught_up', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_catch_up_ecosistema' );

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'globe',
		'label' => __( 'Ecosistema', 'caaguazu-modulos' ),
		'url'   => function_exists( 'caaguazu_page_url' ) ? caaguazu_page_url( 'ecosistema' ) : home_url( '/ecosistema/' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'ecosistema',
		'label' => __( 'Ecosistema', 'caaguazu-modulos' ),
		'url'   => function_exists( 'caaguazu_page_url' ) ? caaguazu_page_url( 'ecosistema' ) : home_url( '/ecosistema/' ),
	);
	return $items;
} );
