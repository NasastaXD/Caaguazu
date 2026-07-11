<?php
/**
 * Siembra al activar las páginas estáticas que el theme necesita (Sobre
 * Caaguazú, Contacto) y configura "Inicio" como página estática de portada,
 * para que el sitio no tenga ningún 404 ni quede en el índice de blog por
 * defecto de WordPress sin tocar nada a mano (antes eran pasos manuales del
 * README). Se crean con contenido real (`caaguazu_core_pages_content()`) en
 * vez de en blanco: antes page.php pintaba solo un hero + "En construcción"
 * hasta que alguien cargaba texto a mano — ahora arrancan con una redacción
 * institucional de base, editable como cualquier Página de WordPress (y
 * reemplazable con Elementor/Brizy vía `caaguazu_maybe_render_builder_content()`,
 * ver "Compatibilidad con editores visuales" en el README). No pisa páginas
 * que ya existan por slug ni una portada ya configurada.
 *
 * Ecosistema, Noticias, Agenda y Turismo ya no viven acá — cada uno siembra
 * su propia página/CPT desde su plugin correspondiente (ver caaguazu-modulos/
 * y caaguazu-turismo/). Servicios y Reportar quedaron afuera a propósito
 * (no se van a lanzar todavía) — ver caaguazu_quick_access_items() en
 * inc/helpers.php, que tampoco los enlaza. "Proponer" sí se siembra: los CTA
 * "Proponer institución"/"Proponer un lugar"/etc. de los archivos de V5
 * (`archive-institucion.php` y análogos) apuntan a esta página, así que no
 * puede quedar sin sembrar sin dejarlos rotos.
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
add_action( 'admin_init', 'caaguazu_backfill_core_pages_content' );

/**
 * Redacción institucional de base para cada página core — punto único para
 * que la siembra inicial y el backfill de sitios ya activos (ver
 * `caaguazu_backfill_core_pages_content()`) usen exactamente el mismo texto.
 */
function caaguazu_core_pages_content() {
	return array(
		'sobre-caaguazu' => "<p>Caaguazú es un departamento del centro-este de Paraguay, con capital en Coronel Oviedo. Su nombre en guaraní significa \"monte grande\", en referencia a la selva que originalmente cubría la región.</p>\n\n<p>Fundada el 8 de mayo de 1845 junto al manantial de Ykua La Patria, la ciudad de Caaguazú nació del asentamiento de once familias guaireñas. Desde entonces, el departamento se consolidó como un cruce de caminos entre Asunción y Ciudad del Este, y como uno de los principales polos madereros del país — de ahí su identidad como \"Capital de la Madera\", con la Ruta 7 como columna vertebral de talleres, carpinteros y artesanos a lo largo de toda la Ruta de la Madera.</p>\n\n<p>Además de la actividad maderera, la economía departamental se apoya en la agricultura familiar, la ganadería y un turismo rural en crecimiento. La cultura guaraní sigue viva en el habla cotidiana, la gastronomía y las festividades locales, como la Fiesta Patronal del 8 de diciembre y el Festival de la Madera.</p>",
		'ecosistema'     => "<p>Caaguazu.net centraliza el acceso a los sub-portales especializados del departamento, cada uno con su propio contenido, identidad visual y equipo editorial, pero dentro de una misma identidad institucional compartida.</p>\n\n<p>Hoy conviven acá el ecosistema de <strong>Turismo</strong> (destinos, gastronomía y cultura guaraní) y el de <strong>Educación</strong> (escuelas, becas municipales y programas educativos), además de sub-portales externos como CEAD. A medida que se sumen nuevos, aparecen solos en la grilla de abajo — no hace falta rediseñar nada para incorporarlos.</p>",
		'contacto'       => '<p>' . __( 'Estos son los canales de contacto del sitio. Para consultas específicas, usá el formulario — para cualquier otra gestión, escribí directamente por email.', 'caaguazu' ) . '</p>',
		'proponer'       => '<p>' . __( 'Usá este formulario para proponer una institución, un lugar, un servicio, un proyecto o un evento que todavía no está en el portal. El equipo lo revisa antes de publicarlo.', 'caaguazu' ) . '</p>',
	);
}

function caaguazu_seed_core_pages() {
	$content = caaguazu_core_pages_content();
	$pages   = array(
		'sobre-caaguazu' => array( 'title' => __( 'Sobre Caaguazú', 'caaguazu' ), 'content' => $content['sobre-caaguazu'] ),
		'contacto'       => array( 'title' => __( 'Contacto', 'caaguazu' ), 'template' => 'page-templates/page-contacto.php', 'content' => $content['contacto'] ),
		'proponer'       => array( 'title' => __( 'Proponer', 'caaguazu' ), 'template' => 'page-templates/page-proponer.php', 'content' => $content['proponer'] ),
	);

	foreach ( $pages as $slug => $data ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$post_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $data['title'],
			'post_name'    => $slug,
			'post_content' => $data['content'],
		) );
		if ( $post_id && ! is_wp_error( $post_id ) && ! empty( $data['template'] ) ) {
			update_post_meta( $post_id, '_wp_page_template', $data['template'] );
		}
	}

	flush_rewrite_rules();
}

/**
 * Sitios que ya tenían estas páginas sembradas en blanco (antes de que este
 * archivo empezara a cargarlas con redacción real) las completa una sola
 * vez — solo si el admin no escribió ya algo ahí (nunca pisa una edición
 * real). Flag nuevo (no reusa `caaguazu_core_pages_caught_up`, que ya
 * estaría en 1 en cualquier sitio con el theme activo desde antes de esto):
 * mismo motivo por el que la migración de Noticias/Agenda/Educación usó
 * flags nuevos en vez de reusar los viejos.
 */
function caaguazu_backfill_core_pages_content() {
	if ( get_option( 'caaguazu_core_pages_content_seeded_v1' ) ) {
		return;
	}
	$content = caaguazu_core_pages_content();
	foreach ( array( 'sobre-caaguazu', 'contacto' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && '' === trim( $page->post_content ) ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_content' => $content[ $slug ] ) );
		}
	}
	update_option( 'caaguazu_core_pages_content_seeded_v1', 1 );
}

/**
 * Regeneración puntual: el admin borró "Sobre Caaguazú" en WP porque un
 * guardado con Elementor la había dejado con contenido raro (mismo origen
 * que el bug de la página Ecosistema, ver
 * caaguazu_modulos_append_eco_grid() en caaguazu-modulos) y prefirió
 * sacarla antes que dejarla rota. El catch-up normal
 * (caaguazu_catch_up_core_pages()) no la vuelve a sembrar porque su flag
 * ya está en 1 desde mucho antes de que la borraran — esta rutina, con un
 * flag nuevo, reintenta la siembra una vez; `caaguazu_seed_core_pages()`
 * ya solo inserta lo que falte (saltea 'contacto' si sigue existiendo).
 */
function caaguazu_reseed_sobre_caaguazu() {
	if ( get_option( 'caaguazu_sobre_caaguazu_reseed_v1' ) ) {
		return;
	}
	caaguazu_seed_core_pages();
	update_option( 'caaguazu_sobre_caaguazu_reseed_v1', 1 );
}
add_action( 'admin_init', 'caaguazu_reseed_sobre_caaguazu' );

/**
 * "Proponer" es una página core nueva (V5.1) — un sitio que ya venía activo
 * antes de que existiera tiene el flag `caaguazu_core_pages_caught_up` en 1
 * desde mucho antes, así que el catch-up normal no la sembraría nunca. Flag
 * propio, mismo patrón que `caaguazu_reseed_sobre_caaguazu()`;
 * `caaguazu_seed_core_pages()` ya sólo inserta lo que falte.
 */
function caaguazu_catch_up_proponer_page() {
	if ( get_option( 'caaguazu_proponer_page_seeded_v1' ) ) {
		return;
	}
	caaguazu_seed_core_pages();
	update_option( 'caaguazu_proponer_page_seeded_v1', 1 );
}
add_action( 'admin_init', 'caaguazu_catch_up_proponer_page' );

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
