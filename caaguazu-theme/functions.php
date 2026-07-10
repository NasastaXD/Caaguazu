<?php
/**
 * Caaguazú theme — bootstrap.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Fuente única de verdad: el header "Version:" de style.css (evita que quede
// desincronizada de la que compara inc/updater.php contra GitHub Releases).
define( 'CAAGUAZU_VERSION', wp_get_theme()->get( 'Version' ) );

/* ---------------------------------------------------------------------------
 * Setup
 * ------------------------------------------------------------------------ */

function caaguazu_setup() {
	load_theme_textdomain( 'caaguazu', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 300,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus( array(
		'primary' => __( 'Menú principal', 'caaguazu' ),
		'mobile'  => __( 'Menú móvil (drawer)', 'caaguazu' ),
	) );

	// Tamaños de imagen pensados para los hero y tarjetas.
	add_image_size( 'caaguazu-hero',   1600, 900,  true );
	add_image_size( 'caaguazu-card',   1200, 800,  true );
	add_image_size( 'caaguazu-square', 800,  800,  true );
}
add_action( 'after_setup_theme', 'caaguazu_setup' );

/* ---------------------------------------------------------------------------
 * Assets
 * ------------------------------------------------------------------------ */

function caaguazu_enqueue_assets() {
	// Google Fonts (Lato + Playfair Display) — igual al sitio original.
	wp_enqueue_style(
		'caaguazu-fonts',
		'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'caaguazu-main',
		get_theme_file_uri( '/assets/css/main.css' ),
		array( 'caaguazu-fonts' ),
		CAAGUAZU_VERSION
	);

	// Animaciones (Animate.css extraído + efectos propios) — ver
	// assets/css/animations.css y assets/js/animations.js.
	wp_enqueue_style(
		'caaguazu-animations',
		get_theme_file_uri( '/assets/css/animations.css' ),
		array( 'caaguazu-main' ),
		CAAGUAZU_VERSION
	);

	wp_enqueue_script(
		'caaguazu-main',
		get_theme_file_uri( '/assets/js/main.js' ),
		array(),
		CAAGUAZU_VERSION,
		true
	);
	wp_enqueue_script(
		'caaguazu-animations',
		get_theme_file_uri( '/assets/js/animations.js' ),
		array( 'caaguazu-main' ),
		CAAGUAZU_VERSION,
		true
	);
	// Eco-rail (sidebar derecho colapsable) — ver inc/sidebar.php.
	wp_enqueue_script(
		'caaguazu-sidebar',
		get_theme_file_uri( '/assets/js/sidebar.js' ),
		array( 'caaguazu-main' ),
		CAAGUAZU_VERSION,
		true
	);
	// Fronteras de módulos para el telón de transición (animations.js): un
	// entry por ecosistema registrado, con sus prefijos de URL y su ícono
	// ya renderizado (inc/icons.php) — el JS no reimplementa el mapa de
	// íconos, solo inyecta el <svg> que le llega hecho.
	$eco_config = array();
	foreach ( caaguazu_ecosystems() as $slug => $eco ) {
		$prefixes = empty( $eco['url_prefixes'] ) ? array() : (array) call_user_func( $eco['url_prefixes'] );
		if ( empty( $prefixes ) ) {
			continue;
		}
		$eco_config[] = array(
			'slug'     => $slug,
			'label'    => $eco['label'],
			'prefixes' => array_map( 'esc_url_raw', $prefixes ),
			'icon'     => caaguazu_icon( empty( $eco['home_icon'] ) ? 'pin' : $eco['home_icon'] ),
		);
	}
	wp_localize_script( 'caaguazu-main', 'caaguazuConfig', array(
		'restSearchUrl' => esc_url_raw( rest_url( 'wp/v2/search' ) ),
		'ecosystems'    => $eco_config,
		'i18nHome'      => __( 'Caaguazú', 'caaguazu' ),
		// Ícono del telón al volver al sitio institucional: mismo emoji de
		// árbol que el splash de entrada (header.php) y Turismo.
		'homeIcon'      => caaguazu_icon( '🌲' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'caaguazu_enqueue_assets' );

/**
 * Preconnect a fonts.googleapis y gstatic — gana algunos ms en first paint.
 */
function caaguazu_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://fonts.googleapis.com' );
		$hints[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'caaguazu_resource_hints', 10, 2 );

/**
 * <body data-page="..."> — el JS lo usa para activar el sticky/parallax en home.
 */
function caaguazu_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'page-home';
	}
	if ( is_page_template( 'page-templates/page-builder.php' ) ) {
		$classes[] = 'page-builder-canvas';
	}
	$eco = caaguazu_current_ecosystem();
	if ( $eco ) {
		$classes[] = 'eco-page';
		$classes[] = 'eco-' . $eco['slug'];
		$is_hub = caaguazu_ecosystem_is_hub( $eco );
		if ( $is_hub ) {
			$classes[] = 'eco-hub';
		}
		// Clases históricas (tourism-page/tourism-hub) que el CSS ya usa.
		if ( ! empty( $eco['legacy_classes']['context'] ) ) {
			$classes[] = $eco['legacy_classes']['context'];
		}
		if ( $is_hub && ! empty( $eco['legacy_classes']['hub'] ) ) {
			$classes[] = $eco['legacy_classes']['hub'];
		}
	}
	return $classes;
}
add_filter( 'body_class', 'caaguazu_body_class' );

/* ---------------------------------------------------------------------------
 * Includes
 * ------------------------------------------------------------------------ */

require get_template_directory() . '/inc/i18n.php';
require get_template_directory() . '/inc/icons.php';
require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/sidebar.php';
require get_template_directory() . '/inc/ecosystem-shell.php';
require get_template_directory() . '/inc/cpt-artisan.php';
require get_template_directory() . '/inc/demo-artisans.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/customizer-defaults.php';
require get_template_directory() . '/inc/core-pages-seeder.php';
require get_template_directory() . '/inc/search-filters.php';
require get_template_directory() . '/inc/spam-guard.php';
require get_template_directory() . '/inc/mailer.php';
require get_template_directory() . '/inc/cpt-report.php';
require get_template_directory() . '/inc/report-form.php';
require get_template_directory() . '/inc/contact-form.php';
require get_template_directory() . '/inc/glossary.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/map.php';
require get_template_directory() . '/inc/cpt-subscriber.php';
require get_template_directory() . '/inc/newsletter-form.php';
require get_template_directory() . '/inc/updater.php';
require get_template_directory() . '/inc/pwa.php';
