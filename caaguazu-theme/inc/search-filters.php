<?php
/**
 * Filtros de búsqueda reales (antes decorativos): tipo de contenido y,
 * si el tipo es Noticias, categoría. 100% vía GET + pre_get_posts, sin JS.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_search_post_type() {
	$allowed = array( 'any', 'page', 'caaguazu_news' );
	$value   = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'any';
	return in_array( $value, $allowed, true ) ? $value : 'any';
}

function caaguazu_search_news_cat() {
	if ( ! isset( $_GET['news_cat'] ) ) {
		return '';
	}
	$slug = sanitize_title( wp_unslash( $_GET['news_cat'] ) );
	$term = get_term_by( 'slug', $slug, 'caaguazu_news_cat' );
	return $term ? $slug : '';
}

function caaguazu_search_filter_url( $post_type, $news_cat = '' ) {
	$args = array( 's' => get_search_query() );
	if ( 'any' !== $post_type ) {
		$args['post_type'] = $post_type;
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

	$post_type = caaguazu_search_post_type();
	if ( 'any' !== $post_type ) {
		$query->set( 'post_type', $post_type );
	}

	if ( 'caaguazu_news' === $post_type ) {
		$news_cat = caaguazu_search_news_cat();
		if ( $news_cat ) {
			$query->set( 'tax_query', array( array(
				'taxonomy' => 'caaguazu_news_cat',
				'field'    => 'slug',
				'terms'    => $news_cat,
			) ) );
		}
	}
}
add_action( 'pre_get_posts', 'caaguazu_filter_search_query' );
