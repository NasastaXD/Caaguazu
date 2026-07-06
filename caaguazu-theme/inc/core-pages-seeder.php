<?php
/**
 * Siembra al activar las páginas estáticas que el theme necesita (Sobre
 * Caaguazú, Servicios, Ecosistema, Contacto, Reportar) y configura "Inicio"
 * como página estática de portada, para que el sitio no tenga ningún 404
 * ni quede en el índice de blog por defecto de WordPress sin tocar nada a
 * mano (antes eran pasos manuales del README). Se crean en blanco a
 * propósito: page.php ya sabe pintar un hero default + "En construcción"
 * cuando no hay contenido cargado todavía; el admin las completa después.
 * No pisa páginas que ya existan por slug ni una portada ya configurada.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_switch_theme', 'caaguazu_seed_core_pages' );
add_action( 'after_switch_theme', 'caaguazu_seed_front_page', 20 );

function caaguazu_seed_core_pages() {
	$pages = array(
		'sobre-caaguazu' => array( 'title' => __( 'Sobre Caaguazú', 'caaguazu' ) ),
		'servicios'      => array( 'title' => __( 'Servicios', 'caaguazu' ) ),
		'ecosistema'     => array( 'title' => __( 'Ecosistema', 'caaguazu' ) ),
		'contacto'       => array( 'title' => __( 'Contacto', 'caaguazu' ), 'template' => 'page-templates/page-contacto.php' ),
		'reportar'       => array( 'title' => __( 'Reportá un problema', 'caaguazu' ), 'template' => 'page-templates/page-reportar.php' ),
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
