<?php
/**
 * Siembra eventos demo al activar el theme (si no hay ninguno todavía),
 * mismo patrón que inc/demo-content.php. Las fechas anuales (patronales,
 * aniversario) se calculan a la próxima ocurrencia desde hoy, así el
 * evento nunca aparece "vencido" sin importar cuándo se instale el theme.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_switch_theme', 'caaguazu_seed_demo_events' );

/**
 * Próxima fecha (Y-m-d) en que ocurre el día/mes dado, hoy o en el futuro.
 */
function caaguazu_next_occurrence( $month, $day ) {
	$year = (int) current_time( 'Y' );
	$date = sprintf( '%04d-%02d-%02d', $year, $month, $day );
	if ( $date < current_time( 'Y-m-d' ) ) {
		$date = sprintf( '%04d-%02d-%02d', $year + 1, $month, $day );
	}
	return $date;
}

function caaguazu_seed_demo_events() {
	$existing = get_posts( array(
		'post_type'      => 'caaguazu_event',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$events = array(
		array(
			'date'     => caaguazu_next_occurrence( 12, 8 ),
			'location' => 'Iglesia Inmaculada Concepción',
			'title'    => 'Fiesta Patronal de Caaguazú',
			'excerpt'  => 'Misa patronal, procesión e iluminación monumental de la Inmaculada Concepción.',
			'content'  => '<p>Cada 8 de diciembre, Caaguazú celebra a su patrona con una misa central en la Iglesia Inmaculada Concepción, seguida de procesión y la iluminación nocturna de los murales de Jorge Aguirre. La plaza se llena de puestos de comida y música hasta la noche.</p>',
		),
		array(
			'date'     => caaguazu_next_occurrence( 5, 8 ),
			'location' => 'Ykua La Patria',
			'title'    => 'Aniversario fundacional de Caaguazú',
			'excerpt'  => 'Acto conmemorativo en Ykua La Patria por un nuevo aniversario de la fundación de la ciudad (1845).',
			'content'  => '<p>El 8 de mayo se conmemora la fundación de Caaguazú junto al manantial de Ykua La Patria, donde once familias guaireñas se asentaron en 1845. El acto incluye ofrenda floral, palabras de autoridades locales y actividades culturales abiertas a la comunidad.</p>',
		),
		array(
			'date'     => caaguazu_next_occurrence( 9, 20 ),
			'location' => 'Ruta 7 — Ruta de la Madera',
			'title'    => 'Feria de Artesanos de la Ruta de la Madera',
			'excerpt'  => 'Carpinteros, talladores y jugueteros exhiben su trabajo en vivo a lo largo de la Ruta 7.',
			'content'  => '<p>Talleres abiertos a lo largo de la Ruta de la Madera para que visitantes vean de cerca el oficio maderero: torneado, tallado, ensamblado de muebles y juguetes tradicionales. Venta directa de artesanos a precio de taller.</p>',
		),
		array(
			'date'     => caaguazu_next_occurrence( 6, 21 ),
			'location' => 'Plaza central',
			'title'    => 'Ronda de tereré comunitario',
			'excerpt'  => 'Encuentro vecinal en la plaza: tereré, música y la cultura de la ronda paraguaya.',
			'content'  => '<p>Vecinos de distintos barrios se juntan en la plaza central para una ronda de tereré abierta a la comunidad, con música en vivo y puestos de comida típica.</p>',
		),
	);

	foreach ( $events as $e ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'caaguazu_event',
			'post_status'  => 'publish',
			'post_title'   => $e['title'],
			'post_excerpt' => $e['excerpt'],
			'post_content' => $e['content'],
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_caaguazu_event_date', $e['date'] );
		update_post_meta( $post_id, '_caaguazu_event_location', $e['location'] );
		update_post_meta( $post_id, '_caaguazu_demo', 1 );
	}
}
