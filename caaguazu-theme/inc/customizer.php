<?php
/**
 * Customizer del theme — todos los datos editables del home.
 *
 * Estrategia: usar theme_mods (no opciones globales), con defaults que
 * reproducen exactamente $IDENTITY, $ECOSYSTEM, $AUDIENCES y $NEWS del
 * sitio PHP original. Así el theme renderiza idéntico al activarlo, y
 * el admin va sobreescribiendo lo que quiera.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_customize_register( $wp_customize ) {

	/* ---------- Panel raíz ---------- */
	$wp_customize->add_panel( 'caaguazu_home', array(
		'title'    => __( 'Contenido del Home', 'caaguazu' ),
		'priority' => 30,
	) );

	/* ============================================================
	 * HERO
	 * ============================================================ */
	$wp_customize->add_section( 'caaguazu_hero', array(
		'title' => __( 'Hero', 'caaguazu' ),
		'panel' => 'caaguazu_home',
	) );

	$hero_fields = array(
		'hero_eyebrow' => array( 'label' => __( 'Eyebrow', 'caaguazu' ),    'default' => 'Departamento de Paraguay' ),
		'hero_title'   => array( 'label' => __( 'Título', 'caaguazu' ),     'default' => 'Caaguazú' ),
		'hero_sub'     => array( 'label' => __( 'Subtítulo', 'caaguazu' ),  'default' => 'Capital de la Madera' ),
		'hero_lead'    => array( 'label' => __( 'Texto lead', 'caaguazu' ), 'default' => 'Donde los bosques subtropicales, los oficios heredados y una comunidad bilingüe escriben el presente de un departamento que mira al futuro.', 'textarea' => true ),
	);
	foreach ( $hero_fields as $key => $cfg ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => isset( $cfg['textarea'] ) ? 'sanitize_textarea_field' : 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $cfg['label'],
			'section' => 'caaguazu_hero',
			'type'    => isset( $cfg['textarea'] ) ? 'textarea' : 'text',
		) );
	}

	// Video MP4 (URL) y poster (URL/imagen).
	$wp_customize->add_setting( 'hero_video_url', array(
		'default'           => 'https://videos.pexels.com/video-files/2491284/2491284-uhd_2560_1440_24fps.mp4',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'hero_video_url', array(
		'label'       => __( 'URL del video (MP4)', 'caaguazu' ),
		'description' => __( 'Dejar vacío para mostrar sólo la imagen poster.', 'caaguazu' ),
		'section'     => 'caaguazu_hero',
		'type'        => 'url',
	) );

	$wp_customize->add_setting( 'hero_poster', array(
		'default'           => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1920&q=80',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_poster', array(
		'label'   => __( 'Imagen poster del hero', 'caaguazu' ),
		'section' => 'caaguazu_hero',
	) ) );

	/* ============================================================
	 * IDENTIDAD (3 bloques)
	 * ============================================================ */
	$wp_customize->add_section( 'caaguazu_identity', array(
		'title' => __( 'Identidad (3 bloques)', 'caaguazu' ),
		'panel' => 'caaguazu_home',
	) );

	$identity_defaults = caaguazu_identity_defaults();
	for ( $i = 0; $i < 3; $i++ ) {
		$d = $identity_defaults[ $i ];
		caaguazu_add_text(  $wp_customize, "identity_{$i}_eyebrow", __( 'Eyebrow', 'caaguazu' ),  $d['eyebrow'], 'caaguazu_identity' );
		caaguazu_add_text(  $wp_customize, "identity_{$i}_title",   __( 'Título', 'caaguazu' ),   $d['title'],   'caaguazu_identity' );
		caaguazu_add_text(  $wp_customize, "identity_{$i}_body",    __( 'Texto', 'caaguazu' ),    $d['body'],    'caaguazu_identity', true );
		caaguazu_add_image( $wp_customize, "identity_{$i}_image",   __( 'Imagen', 'caaguazu' ),   $d['image'],   'caaguazu_identity' );
		// separador visual
		$wp_customize->add_setting( "identity_{$i}_sep", array( 'sanitize_callback' => '__return_true' ) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, "identity_{$i}_sep", array(
			'section'     => 'caaguazu_identity',
			'type'        => 'hidden',
		) ) );
	}

	/* ============================================================
	 * ECOSISTEMA (3 cards)
	 * ============================================================ */
	$wp_customize->add_section( 'caaguazu_ecosystem', array(
		'title' => __( 'Ecosistema (3 sub-portales)', 'caaguazu' ),
		'panel' => 'caaguazu_home',
	) );

	$wp_customize->add_setting( 'eco_section_title', array(
		'default' => 'Un portal, múltiples puertas',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'eco_section_title', array(
		'label'   => __( 'Título de la sección', 'caaguazu' ),
		'section' => 'caaguazu_ecosystem',
	) );
	$wp_customize->add_setting( 'eco_section_body', array(
		'default' => 'Caaguazu.net es el hub central de una red de sub-portales especializados. Cada uno mantiene su voz, todos comparten la misma identidad.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'eco_section_body', array(
		'label'   => __( 'Descripción de la sección', 'caaguazu' ),
		'section' => 'caaguazu_ecosystem',
		'type'    => 'textarea',
	) );

	$eco_defaults = caaguazu_ecosystem_defaults();
	for ( $i = 0; $i < 3; $i++ ) {
		$d = $eco_defaults[ $i ];
		caaguazu_add_text(  $wp_customize, "eco_{$i}_tag",   __( 'Tag (subdominio)', 'caaguazu' ), $d['tag'],   'caaguazu_ecosystem' );
		caaguazu_add_text(  $wp_customize, "eco_{$i}_title", __( 'Título', 'caaguazu' ),           $d['title'], 'caaguazu_ecosystem' );
		caaguazu_add_text(  $wp_customize, "eco_{$i}_body",  __( 'Descripción', 'caaguazu' ),      $d['body'],  'caaguazu_ecosystem', true );
		caaguazu_add_text(  $wp_customize, "eco_{$i}_cta",   __( 'Texto del CTA', 'caaguazu' ),    $d['cta'],   'caaguazu_ecosystem' );
		caaguazu_add_url(   $wp_customize, "eco_{$i}_url",   __( 'URL externa (vacío = "próximamente")', 'caaguazu' ), $d['url'], 'caaguazu_ecosystem' );
		caaguazu_add_image( $wp_customize, "eco_{$i}_image", __( 'Imagen', 'caaguazu' ),           $d['image'], 'caaguazu_ecosystem' );
	}

	/* ============================================================
	 * AUDIENCIAS (3 cards)
	 * ============================================================ */
	$wp_customize->add_section( 'caaguazu_audiences', array(
		'title' => __( 'Audiencias (3 tarjetas)', 'caaguazu' ),
		'panel' => 'caaguazu_home',
	) );

	$aud_defaults = caaguazu_audiences_defaults();
	for ( $i = 0; $i < 3; $i++ ) {
		$d = $aud_defaults[ $i ];
		caaguazu_add_text( $wp_customize, "aud_{$i}_icon",  __( 'Emoji', 'caaguazu' ),     $d['icon'],  'caaguazu_audiences' );
		caaguazu_add_text( $wp_customize, "aud_{$i}_title", __( 'Título', 'caaguazu' ),    $d['title'], 'caaguazu_audiences' );
		caaguazu_add_text( $wp_customize, "aud_{$i}_body",  __( 'Descripción', 'caaguazu' ), $d['body'], 'caaguazu_audiences', true );
		caaguazu_add_text( $wp_customize, "aud_{$i}_cta",   __( 'Texto del CTA', 'caaguazu' ), $d['cta'], 'caaguazu_audiences' );
		caaguazu_add_text( $wp_customize, "aud_{$i}_slug",  __( 'Slug de la página destino', 'caaguazu' ), $d['slug'], 'caaguazu_audiences' );
	}

	/* ============================================================
	 * FOOTER + IDENTIDAD GENERAL
	 * ============================================================ */
	$wp_customize->add_section( 'caaguazu_footer', array(
		'title' => __( 'Footer y contacto', 'caaguazu' ),
	) );
	caaguazu_add_text( $wp_customize, 'site_tagline_custom', __( 'Tagline', 'caaguazu' ), 'Capital de la Madera', 'caaguazu_footer' );
	caaguazu_add_text( $wp_customize, 'footer_about',  __( 'Texto del footer', 'caaguazu' ),
		'Portal oficial del departamento de Caaguazú, Paraguay. Información, servicios y acceso al ecosistema digital regional.',
		'caaguazu_footer', true );
	caaguazu_add_text( $wp_customize, 'contact_org',   __( 'Organización', 'caaguazu' ), 'Gobernación de Caaguazú', 'caaguazu_footer' );
	caaguazu_add_text( $wp_customize, 'contact_city',  __( 'Ciudad', 'caaguazu' ),       'Coronel Oviedo, Paraguay', 'caaguazu_footer' );
	caaguazu_add_text( $wp_customize, 'contact_phone', __( 'Teléfono', 'caaguazu' ),     '+595 (0) 000 000 000',     'caaguazu_footer' );
	caaguazu_add_text( $wp_customize, 'contact_email', __( 'Email', 'caaguazu' ),        'contacto@caaguazu.net',    'caaguazu_footer' );
}
add_action( 'customize_register', 'caaguazu_customize_register' );

/* ---------------------------------------------------------------------------
 * Helpers para reducir boilerplate
 * ------------------------------------------------------------------------ */

function caaguazu_add_text( $wp_customize, $id, $label, $default, $section, $textarea = false ) {
	$wp_customize->add_setting( $id, array(
		'default'           => $default,
		'sanitize_callback' => $textarea ? 'sanitize_textarea_field' : 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( $id, array(
		'label'   => $label,
		'section' => $section,
		'type'    => $textarea ? 'textarea' : 'text',
	) );
}

function caaguazu_add_url( $wp_customize, $id, $label, $default, $section ) {
	$wp_customize->add_setting( $id, array(
		'default'           => $default,
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( $id, array(
		'label'   => $label,
		'section' => $section,
		'type'    => 'url',
	) );
}

function caaguazu_add_image( $wp_customize, $id, $label, $default, $section ) {
	$wp_customize->add_setting( $id, array(
		'default'           => $default,
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array(
		'label'   => $label,
		'section' => $section,
	) ) );
}
