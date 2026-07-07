<?php
/**
 * Siembra al activar las páginas estáticas que el theme necesita (Sobre
 * Caaguazú, Ecosistema, Contacto) y configura "Inicio" como página estática
 * de portada, para que el sitio no tenga ningún 404 ni quede en el índice
 * de blog por defecto de WordPress sin tocar nada a mano (antes eran pasos
 * manuales del README). Se crean en blanco a propósito: page.php ya sabe
 * pintar un hero default + "En construcción" cuando no hay contenido
 * cargado todavía; el admin las completa después. No pisa páginas que ya
 * existan por slug ni una portada ya configurada.
 *
 * Servicios y Reportar quedaron afuera a propósito (no se van a lanzar
 * todavía) — ver caaguazu_quick_access_items()/caaguazu_render_tabbar() en
 * inc/helpers.php, que tampoco los enlazan.
 *
 * `after_switch_theme` sólo dispara al ACTIVAR el theme, no cuando un sitio
 * ya activo recibe una actualización in-place vía inc/updater.php — por eso
 * además hay un catch-up en `admin_init` (gateado por un flag para no
 * repetir las consultas en cada carga del admin) que corre esta siembra al
 * menos una vez en cualquier sitio, sin importar cuándo se instaló el theme.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_switch_theme', 'caaguazu_seed_core_pages' );
add_action( 'after_switch_theme', 'caaguazu_seed_front_page', 20 );
add_action( 'admin_init', 'caaguazu_catch_up_core_pages' );

function caaguazu_seed_core_pages() {
	$pages = array(
		'sobre-caaguazu' => array( 'title' => __( 'Sobre Caaguazú', 'caaguazu' ) ),
		'ecosistema'     => array( 'title' => __( 'Ecosistema', 'caaguazu' ) ),
		'contacto'       => array( 'title' => __( 'Contacto', 'caaguazu' ), 'template' => 'page-templates/page-contacto.php' ),
	);

	foreach ( $pages as $slug => $data ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$post_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $data['title'],
			'post_name'   => $slug,
		) );
		if ( $post_id && ! is_wp_error( $post_id ) && ! empty( $data['template'] ) ) {
			update_post_meta( $post_id, '_wp_page_template', $data['template'] );
		}
	}

	flush_rewrite_rules();
}

function caaguazu_seed_front_page() {
	if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
		return;
	}

	$home = get_page_by_path( 'inicio' );
	if ( $home ) {
		$home_id = $home->ID;
	} else {
		$home_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Inicio', 'caaguazu' ),
			'post_name'   => 'inicio',
		) );
	}

	if ( $home_id && ! is_wp_error( $home_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}
}

/**
 * Red de seguridad para sitios que ya tenían el theme activo antes de que
 * este seeder existiera (o antes de que se agregara alguna página nueva a
 * la lista): al no volver a activarse el theme, `after_switch_theme` nunca
 * vuelve a disparar, y esas páginas quedarían en 404 para siempre pese a
 * actualizar el theme. Corre la misma siembra una vez por sitio.
 */
function caaguazu_catch_up_core_pages() {
	if ( get_option( 'caaguazu_core_pages_caught_up' ) ) {
		return;
	}
	caaguazu_seed_core_pages();
	caaguazu_seed_front_page();
	update_option( 'caaguazu_core_pages_caught_up', 1 );
}
