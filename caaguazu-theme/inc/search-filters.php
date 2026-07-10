<?php
/**
 * Filtros de búsqueda reales (antes decorativos): tipo de contenido y,
 * si el tipo es Noticias, sub-categoría. 100% vía GET + pre_get_posts, sin JS.
 *
 * Noticias/Agenda ya no son post types propios (ver "Compatibilidad con
 * WordPress" en el README) — son Entradas nativas por Categoría, así que
 * el filtro por "tipo" filtra por Categoría en vez de por post_type.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * V5 (civic CMS): institucion/lugar/servicio/proyecto se suman a la lista
 * de tipos filtrables — a diferencia de noticias/agenda, que filtran por
 * Categoría sobre `post`, estos filtran directo por post_type (son CPTs
 * reales), así que no necesitan su propia rama en
 * caaguazu_filter_search_query().
 */
function caaguazu_search_type() {
	$allowed = array( 'any', 'page', 'noticias', 'agenda', 'institucion', 'lugar', 'servicio', 'proyecto' );
	$value   = isset( $_GET['tipo'] ) ? sanitize_key( wp_unslash( $_GET['tipo'] ) ) : 'any';
	return in_array( $value, $allowed, true ) ? $value : 'any';
}

/**
 * Sub-categoría de Noticias pedida por GET, validada contra el árbol real
 * de la categoría Noticias (no cualquier slug de categoría del sitio).
 */
function caaguazu_search_news_cat() {
	if ( ! isset( $_GET['news_cat'] ) ) {
		return '';
	}
	$slug = sanitize_title( wp_unslash( $_GET['news_cat'] ) );
	$term = get_category_by_slug( $slug );
	if ( ! $term || 'noticias' !== caaguazu_category_family( $term ) || 'noticias' === $term->slug ) {
		return '';
	}
	return $slug;
}

function caaguazu_search_filter_url( $type, $news_cat = '' ) {
	$args = array( 's' => get_search_query() );
	if ( 'any' !== $type ) {
		$args['tipo'] = $type;
	}
	if ( $news_cat ) {
		$args['news_cat'] = $news_cat;
	}
	return esc_url( add_query_arg( $args, home_url( '/' ) ) );
}

function caaguazu_filter_search_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$type = caaguazu_search_type();
	if ( in_array( $type, array( 'page', 'institucion', 'lugar', 'servicio', 'proyecto' ), true ) ) {
		$query->set( 'post_type', $type );
		return;
	}
	if ( 'noticias' === $type || 'agenda' === $type ) {
		$query->set( 'post_type', 'post' );
		$news_cat = 'noticias' === $type ? caaguazu_search_news_cat() : '';
		$query->set( 'category_name', $news_cat ? $news_cat : $type );
	}
	// 'any': no se toca post_type, WP busca en todo lo público del sitio.
}
add_action( 'pre_get_posts', 'caaguazu_filter_search_query' );
