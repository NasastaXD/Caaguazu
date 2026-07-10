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
 * Defaults de los 3 slots configurables de sub-portales EXTERNOS. Desde el
 * theme 3.0 las tarjetas de los ecosistemas internos (Turismo, Educación,
 * los que vengan) se derivan solas del registry `caaguazu_ecosystems` del
 * theme — los slots del Customizer quedan solo para sitios externos.
 *
 * Los 3 arrancan vacíos (título vacío = no se muestra): el CEAD
 * (cead.caaguazu.net) que ocupaba el slot 0 no corresponde acá — no es un
 * sub-portal real del departamento — así que se sacó del default. Un
 * admin puede seguir cargando cualquier sub-portal externo real a mano
 * desde Apariencia → Personalizar → Ecosistema.
 *
 * Nota de migración: los slots son posicionales (`eco_{i}_*`). Un sitio que
 * haya personalizado el slot 0 cuando el default era Turismo (o CEAD) verá
 * esa tarjeta vieja — se corrige vaciándola en Apariencia → Personalizar →
 * Ecosistema.
 */
function caaguazu_modulos_external_eco_defaults() {
	$empty_slot = array( 'tag' => '', 'title' => '', 'body' => '', 'cta' => '', 'url' => '', 'image' => '' );
	return array( $empty_slot, $empty_slot, $empty_slot );
}

/**
 * Tarjetas finales del hub Ecosistema, ya resueltas: internas (registry del
 * theme) + externas (slots del Customizer, salteando los vacíos). El theme
 * 3.0 usa esta función; con un theme viejo (sin registry), front-page.php
 * sigue cayendo en caaguazu_ecosystem_defaults() y nada cambia.
 */
function caaguazu_modulos_ecosystem_cards() {
	$cards = array();

	if ( function_exists( 'caaguazu_ecosystems' ) ) {
		foreach ( caaguazu_ecosystems() as $eco ) {
			$card    = isset( $eco['card'] ) ? $eco['card'] : array();
			$cards[] = array(
				'tag'      => $eco['label'],
				'title'    => $eco['label'],
				'body'     => isset( $card['body'] ) ? $card['body'] : '',
				'cta'      => sprintf( __( 'Ver sección de %s', 'caaguazu-modulos' ), $eco['label'] ),
				'url'      => caaguazu_ecosystem_hub_url( $eco ),
				'image'    => isset( $card['image'] ) ? $card['image'] : '',
				'external' => false,
			);
		}
	}

	$defaults = caaguazu_modulos_external_eco_defaults();
	foreach ( $defaults as $i => $d ) {
		$title = get_theme_mod( "eco_{$i}_title", $d['title'] );
		if ( '' === $title ) {
			continue; // slot vacío: no se muestra.
		}
		$image = get_theme_mod( "eco_{$i}_image", $d['image'] );
		if ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( (int) $image, 'large' );
		}
		$cards[] = array(
			'tag'      => get_theme_mod( "eco_{$i}_tag", $d['tag'] ),
			'title'    => $title,
			'body'     => get_theme_mod( "eco_{$i}_body", $d['body'] ),
			'cta'      => get_theme_mod( "eco_{$i}_cta", $d['cta'] ),
			'url'      => get_theme_mod( "eco_{$i}_url", $d['url'] ),
			'image'    => $image,
			'external' => true,
		);
	}

	return $cards;
}

/**
 * Registra su propia sección en el panel "Contenido del Home" que el theme
 * ya crea. Si el theme activo no tiene ese panel (u otro theme sin panel
 * "caaguazu_home"), el Customizer igual muestra la sección, solo que sin
 * agrupar bajo ningún panel.
 */
function caaguazu_modulos_ecosistema_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'caaguazu_ecosystem', array(
		'title' => __( 'Ecosistema (sub-portales externos)', 'caaguazu-modulos' ),
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

	$eco_defaults = caaguazu_modulos_external_eco_defaults();
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
 * Redacción institucional de base de la página "Ecosistema" — page.php
 * del theme la imprime vía the_content() y además suma la grilla de
 * tarjetas real (caaguazu_render_ecosystem_cards()) a continuación, para
 * que visitar /ecosistema/ directo muestre lo mismo que scrollear desde
 * el home, no solo este texto.
 *
 * Los nombres de los ecosistemas (antes "Turismo"/"Educación" en negrita,
 * fijos) ahora salen del registry (`caaguazu_ecosystems()`) y van
 * enlazados a su hub real — texto en negrita sin link confundía (reporte
 * de usuario: "es solo texto sin redirigir a los portales"), y además
 * quedaba desactualizado apenas cambiaba qué ecosistemas hay (mencionaba
 * "CEAD" incluso después de sacarlo de los slots por defecto del hub, ver
 * caaguazu_modulos_external_eco_defaults()). Como CEAD nunca fue un
 * ecosistema real del registry (era un slot externo del Customizer), ya no
 * aparece acá tampoco.
 */
function caaguazu_modulos_ecosistema_page_content() {
	$intro = __( 'Caaguazu.net centraliza el acceso a los sub-portales especializados del departamento, cada uno con su propio contenido, identidad visual y equipo editorial, pero dentro de una misma identidad institucional compartida.', 'caaguazu-modulos' );

	$links = array();
	if ( function_exists( 'caaguazu_ecosystems' ) && function_exists( 'caaguazu_ecosystem_hub_url' ) ) {
		foreach ( caaguazu_ecosystems() as $eco ) {
			$links[] = '<a href="' . esc_url( caaguazu_ecosystem_hub_url( $eco ) ) . '">' . esc_html( $eco['label'] ) . '</a>';
		}
	}

	$second = $links
		? sprintf(
			/* translators: %s: lista de nombres de ecosistemas ya enlazados a su hub, ej. "Turismo, Educación" */
			__( 'Hoy conviven acá %s. A medida que se sumen nuevos, aparecen solos en la grilla de abajo — no hace falta rediseñar nada para incorporarlos.', 'caaguazu-modulos' ),
			implode( ', ', $links )
		)
		: __( 'A medida que se sumen sub-portales, aparecen solos en la grilla de abajo.', 'caaguazu-modulos' );

	return "<p>{$intro}</p>\n\n<p>{$second}</p>";
}

/**
 * Siembra la página "Ecosistema" al activar el plugin (si no existe
 * todavía), con la redacción institucional de base — page.php del theme le
 * pinta el hero default y, para este slug puntual, además la grilla de
 * tarjetas real (ver caaguazu_render_ecosystem_cards() en inc/helpers.php).
 */
function caaguazu_modulos_seed_ecosistema_page() {
	if ( get_page_by_path( 'ecosistema' ) ) {
		return;
	}
	wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => __( 'Ecosistema', 'caaguazu-modulos' ),
		'post_name'    => 'ecosistema',
		'post_content' => caaguazu_modulos_ecosistema_page_content(),
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

/**
 * Sitios que ya tenían la página "Ecosistema" sembrada en blanco (antes de
 * que este archivo empezara a cargarla con redacción real) la completan una
 * sola vez — solo si el admin no escribió ya algo ahí. Flag nuevo (no reusa
 * `caaguazu_modulos_ecosistema_caught_up`, que ya estaría en 1 en cualquier
 * sitio con este módulo activo desde antes de esto) — mismo motivo que el
 * resto de los flags "_v2"/"_v1" nuevos de esta migración.
 */
function caaguazu_modulos_backfill_ecosistema_content() {
	if ( get_option( 'caaguazu_modulos_ecosistema_content_seeded_v1' ) ) {
		return;
	}
	$page = get_page_by_path( 'ecosistema' );
	if ( $page && '' === trim( $page->post_content ) ) {
		wp_update_post( array( 'ID' => $page->ID, 'post_content' => caaguazu_modulos_ecosistema_page_content() ) );
	}
	update_option( 'caaguazu_modulos_ecosistema_content_seeded_v1', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_backfill_ecosistema_content' );

/**
 * Arregla la página "Ecosistema" en sitios que ya la tenían sembrada con la
 * redacción vieja (previa a este fix): mencionaba "Turismo" y "Educación" en
 * negrita pero SIN enlazar (texto muerto — clickear ahí no llevaba a ningún
 * lado, reporte de usuario) y todavía nombraba a "CEAD" aunque ya se había
 * sacado de la grilla de tarjetas por defecto. Solo pisa el contenido si
 * sigue siendo exactamente ese texto viejo auto-generado — si un admin ya
 * lo editó a mano, esta rutina no toca nada (mismo criterio que el resto de
 * los backfills de contenido de este archivo).
 */
function caaguazu_modulos_fix_ecosistema_links() {
	if ( get_option( 'caaguazu_modulos_ecosistema_links_fixed_v1' ) ) {
		return;
	}
	$page = get_page_by_path( 'ecosistema' );
	if ( $page ) {
		$old_text = "<p>Caaguazu.net centraliza el acceso a los sub-portales especializados del departamento, cada uno con su propio contenido, identidad visual y equipo editorial, pero dentro de una misma identidad institucional compartida.</p>\n\n<p>Hoy conviven acá el ecosistema de <strong>Turismo</strong> (destinos, gastronomía y cultura guaraní) y el de <strong>Educación</strong> (escuelas, becas municipales y programas educativos), además de sub-portales externos como CEAD. A medida que se sumen nuevos, aparecen solos en la grilla de abajo.</p>";
		if ( trim( $page->post_content ) === trim( $old_text ) ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_content' => caaguazu_modulos_ecosistema_page_content() ) );
		}
	}
	update_option( 'caaguazu_modulos_ecosistema_links_fixed_v1', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_fix_ecosistema_links' );

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
