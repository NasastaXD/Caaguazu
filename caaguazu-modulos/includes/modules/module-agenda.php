<?php
/**
 * Módulo Agenda — categoría "Agenda" sobre las Entradas nativas de
 * WordPress, demo seeder, y auto-registro en nav/accesos rápidos del theme.
 *
 * Hasta la 1.4.0 este módulo era un CPT propio (`caaguazu_event`). Pasó a
 * ser Entradas nativas + Categoría — mismo motivo que module-noticias.php.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Categoría "Agenda", creada si no existe todavía. Memoizada por request.
 */
function caaguazu_agenda_ensure_category() {
	static $cat_id = null;
	if ( null === $cat_id ) {
		$cat_id = caaguazu_ensure_category( 'Agenda', 'agenda' );
	}
	return $cat_id;
}
add_action( 'init', 'caaguazu_agenda_ensure_category', 20 );

function caaguazu_register_event_meta() {
	register_post_meta( 'post', '_caaguazu_event_date', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
	register_post_meta( 'post', '_caaguazu_event_location', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_event_meta' );

/**
 * Metabox de fecha/lugar. Aparece en toda Entrada, igual que el de
 * module-noticias.php — ver comentario ahí sobre por qué no se puede
 * condicionar a la categoría tildada en el mismo formulario.
 */
function caaguazu_event_metabox() {
	add_meta_box( 'caaguazu_event_meta', __( 'Agenda: fecha y lugar', 'caaguazu-modulos' ), 'caaguazu_event_metabox_html', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_event_metabox' );

function caaguazu_event_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_event_meta', 'caaguazu_event_meta_nonce' );
	$date     = get_post_meta( $post->ID, '_caaguazu_event_date', true );
	$location = get_post_meta( $post->ID, '_caaguazu_event_location', true );
	?>
	<p>
		<label for="caaguazu_event_date"><strong><?php esc_html_e( 'Fecha del evento', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="date" id="caaguazu_event_date" name="caaguazu_event_date" value="<?php echo esc_attr( $date ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_location"><strong><?php esc_html_e( 'Lugar', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_event_location" name="caaguazu_event_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
	</p>
	<p style="color:#666;font-size:12px">
		<?php esc_html_e( 'Solo aplica si la entrada está en la categoría Agenda.', 'caaguazu-modulos' ); ?>
	</p>
	<?php
}

function caaguazu_event_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_event_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_event_meta_nonce'], 'caaguazu_event_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_event_date'] ) ) {
		update_post_meta( $post_id, '_caaguazu_event_date', sanitize_text_field( $_POST['caaguazu_event_date'] ) );
	}
	if ( isset( $_POST['caaguazu_event_location'] ) ) {
		update_post_meta( $post_id, '_caaguazu_event_location', sanitize_text_field( $_POST['caaguazu_event_location'] ) );
	}
}
add_action( 'save_post_post', 'caaguazu_event_save_meta' );

/**
 * El archivo de la categoría Agenda ordena por fecha del evento
 * (ascendente), no por fecha de publicación — así los próximos aparecen
 * primero.
 */
function caaguazu_event_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_category( 'agenda' ) ) {
		return;
	}
	$query->set( 'meta_key', '_caaguazu_event_date' );
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', 'ASC' );
}
add_action( 'pre_get_posts', 'caaguazu_event_archive_order' );

/**
 * Próximos eventos ordenados por fecha ascendente (excluye los que ya pasaron).
 */
function caaguazu_upcoming_events( $limit = 1 ) {
	$today = current_time( 'Y-m-d' );
	return new WP_Query( array(
		'post_type'      => 'post',
		'category_name'  => 'agenda',
		'posts_per_page' => $limit,
		'no_found_rows'  => true,
		'meta_key'       => '_caaguazu_event_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => array( array(
			'key'     => '_caaguazu_event_date',
			'value'   => $today,
			'compare' => '>=',
			'type'    => 'DATE',
		) ),
	) );
}

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

/**
 * Sitios que ya tenían este módulo activo antes de la 1.5.0 (CPT propio
 * `caaguazu_event`) tienen ese contenido atrapado en un tipo de contenido
 * que ya no se registra. Lo pasa a Entrada + categoría Agenda.
 */
function caaguazu_modulos_migrate_agenda_from_cpt() {
	if ( get_option( 'caaguazu_modulos_agenda_migrated' ) ) {
		return;
	}

	$old_posts = get_posts( array(
		'post_type'      => 'caaguazu_event',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	if ( $old_posts ) {
		$cat_id = caaguazu_agenda_ensure_category();
		foreach ( $old_posts as $post_id ) {
			wp_update_post( array( 'ID' => $post_id, 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $cat_id ), 'category' );
		}
	}

	update_option( 'caaguazu_modulos_agenda_migrated', 1 );
}

/**
 * Siembra 4 eventos demo (si no hay ninguno todavía en la categoría
 * Agenda). Las fechas anuales se calculan a la próxima ocurrencia desde
 * hoy, así el evento nunca aparece "vencido" sin importar cuándo se
 * instale el plugin. Migra primero cualquier resto del CPT viejo.
 */
function caaguazu_modulos_seed_agenda() {
	caaguazu_modulos_migrate_agenda_from_cpt();

	$cat_id   = caaguazu_agenda_ensure_category();
	$existing = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'category__in'   => array( $cat_id ),
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
			'post_type'    => 'post',
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
		wp_set_post_terms( $post_id, array( $cat_id ), 'category' );
	}
}

/**
 * `register_activation_hook` solo corre al activar este plugin — catch-up
 * en `admin_init` para que la migración desde el CPT viejo corra sola en
 * la próxima visita a wp-admin en un sitio que ya lo tenía activo.
 */
function caaguazu_modulos_catch_up_agenda() {
	if ( get_option( 'caaguazu_modulos_agenda_caught_up' ) ) {
		return;
	}
	caaguazu_modulos_seed_agenda();
	update_option( 'caaguazu_modulos_agenda_caught_up', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_catch_up_agenda' );

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'calendar',
		'label' => __( 'Agenda', 'caaguazu-modulos' ),
		'url'   => caaguazu_category_url( 'agenda' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'agenda',
		'label' => __( 'Agenda', 'caaguazu-modulos' ),
		'url'   => caaguazu_category_url( 'agenda' ),
	);
	return $items;
} );
