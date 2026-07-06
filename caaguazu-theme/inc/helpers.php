<?php
/**
 * Helpers del theme.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Devuelve un slug estable para <body data-page="..."> e usos similares.
 * El JS y el CSS originales esperaban valores: home, sobre-caaguazu, servicios,
 * noticias, transparencia, ecosistema, contacto, buscar.
 */
function caaguazu_current_page_slug() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_singular( 'caaguazu_news' ) || is_post_type_archive( 'caaguazu_news' ) || is_tax( 'caaguazu_news_cat' ) ) {
		return 'noticias';
	}
	if ( is_search() ) {
		return 'buscar';
	}
	$obj = get_queried_object();
	if ( $obj instanceof WP_Post && $obj->post_name ) {
		return $obj->post_name;
	}
	return 'page';
}

/**
 * Devuelve true si la página actual debe mostrar el header transparente (sólo home).
 */
function caaguazu_is_home() {
	return is_front_page();
}

/**
 * Renderiza el menú principal como una lista plana de <a> (no <ul>/<li>),
 * que es lo que espera el CSS original con `.nav > a`.
 */
function caaguazu_render_nav( $location = 'primary', $current_slug = '' ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		caaguazu_render_fallback_nav( $current_slug );
		return;
	}
	$menu  = wp_get_nav_menu_object( $locations[ $location ] );
	$items = $menu ? wp_get_nav_menu_items( $menu->term_id ) : array();
	if ( ! $items ) {
		caaguazu_render_fallback_nav( $current_slug );
		return;
	}
	foreach ( $items as $item ) {
		$is_active = '';
		if ( $current_slug && $item->object === 'page' ) {
			$page = get_post( (int) $item->object_id );
			if ( $page && $page->post_name === $current_slug ) {
				$is_active = ' class="active"';
			}
		}
		printf(
			'<a href="%s"%s>%s</a>',
			esc_url( $item->url ),
			$is_active,
			esc_html( $item->title )
		);
	}
}

/**
 * Menú por defecto si el admin todavía no configuró uno en Apariencia → Menús.
 * Coincide con el $NAV original.
 */
function caaguazu_render_fallback_nav( $current_slug = '' ) {
	$defaults = array(
		'sobre-caaguazu' => __( 'Sobre Caaguazú', 'caaguazu' ),
		'servicios'      => __( 'Servicios', 'caaguazu' ),
		'noticias'       => __( 'Noticias', 'caaguazu' ),
		'transparencia'  => __( 'Transparencia', 'caaguazu' ),
		'turismo'        => __( 'Turismo', 'caaguazu' ),
		'ecosistema'     => __( 'Ecosistema', 'caaguazu' ),
		'contacto'       => __( 'Contacto', 'caaguazu' ),
	);
	foreach ( $defaults as $slug => $label ) {
		$page = get_page_by_path( $slug );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
		$cls  = ( $slug === $current_slug ) ? ' class="active"' : '';
		printf( '<a href="%s"%s>%s</a>', esc_url( $url ), $cls, caaguazu_i18n_html( 'nav.' . $slug, $label ) );
	}
}

/**
 * Devuelve la URL de una página por slug, con fallback a home_url.
 */
function caaguazu_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/' . $slug . '/' );
}

/**
 * Lee un valor del Customizer aceptando un default; usa get_theme_mod.
 */
function caaguazu_opt( $key, $default = '' ) {
	$val = get_theme_mod( $key, $default );
	return $val === '' ? $default : $val;
}

/**
 * Devuelve una URL de imagen del Customizer, ya sea ID de adjunto o URL directa.
 */
function caaguazu_opt_image( $key, $default = '' ) {
	$val = get_theme_mod( $key, $default );
	if ( is_numeric( $val ) ) {
		$src = wp_get_attachment_image_url( (int) $val, 'caaguazu-card' );
		return $src ? $src : $default;
	}
	return $val ? $val : $default;
}
