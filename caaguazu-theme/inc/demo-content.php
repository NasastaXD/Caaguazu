<?php
/**
 * Siembra noticias demo al activar el theme, para que el home y el archivo
 * de Noticias no muestren el fallback placeholder si todavía no hay contenido
 * real cargado. Se resiembra si el admin borra todas las noticias y reactiva
 * el theme (no hay flag persistente de "ya sembrado").
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_switch_theme', 'caaguazu_seed_demo_news' );

function caaguazu_seed_demo_news() {
	$existing = get_posts( array(
		'post_type'      => 'caaguazu_news',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$categories = array( 'Desarrollo', 'Cultura', 'Gobierno', 'Turismo', 'Comunidad' );
	$cat_ids    = array();
	foreach ( $categories as $cat_name ) {
		$term = term_exists( $cat_name, 'caaguazu_news_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $cat_name, 'caaguazu_news_cat' );
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $cat_name ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	$news = array(
		array(
			'cat'      => 'Desarrollo',
			'days_ago' => 3,
			'minutes'  => 4,
			'title'    => 'Caaguazú lanza programa de reforestación con escuelas rurales',
			'excerpt'  => 'La iniciativa involucra a más de 40 instituciones educativas en la plantación de especies nativas y eucalipto de ciclo corto.',
			'content'  => '<p>La Secretaría de Desarrollo del departamento presentó un programa de reforestación que involucra a escuelas rurales de todo Caaguazú. Durante los próximos seis meses, estudiantes y docentes participarán de jornadas de plantación en terrenos linderos a los establecimientos educativos.</p><p>El proyecto busca recuperar superficie forestal nativa y, al mismo tiempo, dar continuidad al ciclo productivo del eucalipto que sostiene buena parte de la industria maderera local.</p>',
		),
		array(
			'cat'      => 'Cultura',
			'days_ago' => 9,
			'minutes'  => 3,
			'title'    => 'Festival de la Madera celebra su 15ª edición',
			'excerpt'  => 'Tres días de exposiciones, talleres de carpintería tradicional y gastronomía local en el centro de la ciudad.',
			'content'  => '<p>Con la participación de carpinteros, talladores y artesanos de toda la Ruta de la Madera, el Festival de la Madera llega a su 15ª edición. La agenda incluye demostraciones en vivo, una feria de productos terminados y espacios gastronómicos con platos típicos.</p><p>La actividad, organizada junto a la Asociación de Madereros, se realiza en la plaza central y es de entrada libre.</p>',
		),
		array(
			'cat'      => 'Gobierno',
			'days_ago' => 16,
			'minutes'  => 5,
			'title'    => 'Nuevas plataformas digitales simplifican trámites departamentales',
			'excerpt'  => 'Más de 30 gestiones ya pueden iniciarse en línea desde el portal de servicios, reduciendo tiempos de espera presencial.',
			'content'  => '<p>El departamento avanza en la digitalización de sus trámites más solicitados. Certificados, habilitaciones y consultas que antes requerían presencia física ahora pueden iniciarse desde el portal de Servicios.</p><p>La mesa de entrada seguirá disponible para quienes prefieran hacer el trámite en persona.</p>',
		),
		array(
			'cat'      => 'Turismo',
			'days_ago' => 23,
			'minutes'  => 4,
			'title'    => 'Ykua La Patria suma señalética histórica renovada',
			'excerpt'  => 'El parque fundacional de Caaguazú estrena cartelería con la historia del manantial y su rol durante la Guerra de la Triple Alianza.',
			'content'  => '<p>El sitio donde nació Caaguazú en 1845 renovó su señalética para visitantes. Los nuevos carteles narran la historia del manantial, su vínculo con la fundación de la ciudad y el episodio de la Guerra de la Triple Alianza.</p><p>La actualización es parte de un plan más amplio para poner en valor los atractivos turísticos del departamento.</p>',
		),
		array(
			'cat'      => 'Comunidad',
			'days_ago' => 30,
			'minutes'  => 3,
			'title'    => 'Mercado de Abasto amplía su horario los fines de semana',
			'excerpt'  => 'Productores locales pidieron más horas de venta los sábados y domingos; el municipio respondió ampliando el horario habitual.',
			'content'  => '<p>A pedido de los feriantes del Mercado de Abasto, el municipio extendió el horario de atención los fines de semana. La medida busca dar más previsibilidad a los productores que llegan desde zonas rurales del departamento.</p><p>El mercado sigue siendo uno de los puntos de encuentro más concurridos del centro de Caaguazú.</p>',
		),
	);

	foreach ( $news as $n ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'caaguazu_news',
			'post_status'  => 'publish',
			'post_title'   => $n['title'],
			'post_excerpt' => $n['excerpt'],
			'post_content' => $n['content'],
			'post_date'    => date( 'Y-m-d H:i:s', strtotime( '-' . $n['days_ago'] . ' days' ) ),
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_caaguazu_read_minutes', $n['minutes'] );
		update_post_meta( $post_id, '_caaguazu_demo', 1 );

		if ( isset( $cat_ids[ $n['cat'] ] ) ) {
			wp_set_post_terms( $post_id, array( (int) $cat_ids[ $n['cat'] ] ), 'caaguazu_news_cat' );
		}
	}
}
