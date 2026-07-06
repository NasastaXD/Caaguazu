<?php
/**
 * Siembra los 4 perfiles de artesanos ya redactados (migrados del sitio de
 * turismo) como posts reales del directorio dinámico, mismo patrón que
 * inc/demo-content.php / inc/demo-events.php.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_switch_theme', 'caaguazu_seed_demo_artisans' );

function caaguazu_seed_demo_artisans() {
	$existing = get_posts( array(
		'post_type'      => 'caaguazu_artisan',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$artisans = array(
		array(
			'title'   => 'Don Eulogio Benítez',
			'craft'   => 'Carpintero · 40 años de oficio',
			'location'=> 'Barrio San Blas',
			'quote'   => 'La madera te enseña paciencia. No podés apurarla.',
			'excerpt' => 'Cuarenta años tallando muebles a mano en el barrio San Blas.',
			'content' => '<p>Aprendió el oficio de su padre y hoy sigue trabajando con las mismas herramientas de mano que usaba de joven. Sus muebles se reconocen por el terminado natural, sin lacas industriales.</p>',
		),
		array(
			'title'   => 'Doña Catalina Ramírez',
			'craft'   => 'Pintora y barnizadora',
			'location'=> 'Ruta 7 km 178',
			'quote'   => 'Cada pieza pasa tres veces por mis manos antes de salir.',
			'excerpt' => 'El barniz final de buena parte de los muebles que salen de la Ruta 7.',
			'content' => '<p>Especialista en terminaciones: lija, tapaporo y barniz a mano. Trabaja piezas de varios talleres de la zona antes de que salgan a la venta.</p>',
		),
		array(
			'title'   => 'Mauricio Ovelar',
			'craft'   => 'Parquetero · tercera generación',
			'location'=> 'Caaguazú centro',
			'quote'   => 'Mi abuelo puso los pisos del primer polideportivo del país.',
			'excerpt' => 'Tercera generación de parqueteros, hoy al frente del taller familiar.',
			'content' => '<p>Continúa el oficio que empezó su abuelo. Hoy combina técnicas tradicionales de colocación con maquinaria moderna de corte.</p>',
		),
		array(
			'title'   => 'Familia Fernández',
			'craft'   => 'Juguetería tradicional',
			'location'=> 'Casillas Ruta 7',
			'quote'   => 'Vendemos lo mismo desde 1962. Autitos de cedro.',
			'excerpt' => 'Juguetes de madera tallados a mano desde 1962, sin cambiar el diseño.',
			'content' => '<p>Autitos, trompos y rompecabezas de cedro, vendidos en la misma casilla sobre la Ruta 7 desde hace más de sesenta años.</p>',
		),
	);

	foreach ( $artisans as $a ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'caaguazu_artisan',
			'post_status'  => 'publish',
			'post_title'   => $a['title'],
			'post_excerpt' => $a['excerpt'],
			'post_content' => $a['content'],
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_caaguazu_artisan_craft', $a['craft'] );
		update_post_meta( $post_id, '_caaguazu_artisan_location', $a['location'] );
		update_post_meta( $post_id, '_caaguazu_artisan_quote', $a['quote'] );
		update_post_meta( $post_id, '_caaguazu_demo', 1 );
	}
}
