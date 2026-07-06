<?php
/**
 * Mapa interactivo (Leaflet) de puntos de interés/historia — shortcode
 * [caaguazu_mapa_puntos]. Complementa (no reemplaza) al [caaguazu_mapa] del
 * plugin Caaguazú Locales, que muestra negocios reales editables — el
 * nombre distinto evita que un add_shortcode() pise al otro (WP no avisa
 * si dos registran el mismo tag, gana el que se registra último).
 *
 * Única función del theme que depende de un recurso externo (Leaflet vía
 * CDN, igual que hacía el theme de turismo original): se carga solo en las
 * páginas que efectivamente usan el shortcode, no en todo el sitio.
 *
 * Coordenadas aproximadas alrededor del centro de Caaguazú — a ajustar por
 * el departamento con ubicaciones exactas cuando estén disponibles.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_map_points() {
	return array(
		array( 'name' => 'Ykua La Patria',                    'lat' => -25.4670, 'lng' => -56.0340, 'desc' => 'Manantial fundacional, 1845.' ),
		array( 'name' => 'Iglesia Inmaculada Concepción',     'lat' => -25.4660, 'lng' => -56.0335, 'desc' => 'Templo principal, plaza central.' ),
		array( 'name' => 'Mercado de Abasto',                  'lat' => -25.4650, 'lng' => -56.0350, 'desc' => 'A 4 cuadras del centro.' ),
		array( 'name' => 'Parque Techapyrã',                   'lat' => -25.4700, 'lng' => -56.0300, 'desc' => 'A 1 km del centro.' ),
		array( 'name' => 'Ruta de la Madera',                  'lat' => -25.4600, 'lng' => -56.0250, 'desc' => 'Ruta 7, km 175–185.' ),
	);
}

function caaguazu_map_shortcode() {
	static $rendered = 0;
	$rendered++;
	$points = wp_json_encode( caaguazu_map_points() );
	return sprintf(
		'<div class="caaguazu-map" id="caaguazu-map-%1$d" data-points=\'%2$s\' role="application" aria-label="%3$s"></div>',
		$rendered,
		esc_attr( $points ),
		esc_attr__( 'Mapa de puntos históricos de Caaguazú', 'caaguazu' )
	);
}
add_shortcode( 'caaguazu_mapa_puntos', 'caaguazu_map_shortcode' );

function caaguazu_map_assets() {
	if ( is_admin() ) {
		return;
	}
	global $post;
	if ( ! $post || ! has_shortcode( $post->post_content, 'caaguazu_mapa_puntos' ) ) {
		return;
	}

	wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4' );
	wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true );
	wp_enqueue_script( 'caaguazu-map', get_template_directory_uri() . '/assets/js/map.js', array( 'leaflet' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'caaguazu_map_assets' );
