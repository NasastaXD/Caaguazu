<?php
/**
 * Custom Post Type: Suscriptores del newsletter. Gestión interna, no
 * público — mismo patrón que caaguazu_report (sin REST, sin archive).
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_subscriber_cpt() {
	register_post_type( 'caaguazu_subscriber', array(
		'labels' => array(
			'name'          => __( 'Suscriptores', 'caaguazu' ),
			'singular_name' => __( 'Suscriptor', 'caaguazu' ),
			'menu_name'     => __( 'Suscriptores', 'caaguazu' ),
			'search_items'  => __( 'Buscar suscriptores', 'caaguazu' ),
			'not_found'     => __( 'No hay suscriptores todavía.', 'caaguazu' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-email',
		'has_archive'         => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'supports'            => array( 'title' ),
	) );
}
add_action( 'init', 'caaguazu_register_subscriber_cpt' );
