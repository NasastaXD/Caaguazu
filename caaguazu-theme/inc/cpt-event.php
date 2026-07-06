<?php
/**
 * Custom Post Type: Eventos y agenda cultural.
 *
 * Mismo patrón que cpt-news.php: CPT propio, público, show_in_rest,
 * meta de fecha/ubicación con metabox simple (sin ACF).
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_event_cpt() {
	register_post_type( 'caaguazu_event', array(
		'labels' => array(
			'name'               => __( 'Eventos', 'caaguazu' ),
			'singular_name'      => __( 'Evento', 'caaguazu' ),
			'add_new'            => __( 'Añadir evento', 'caaguazu' ),
			'add_new_item'       => __( 'Añadir nuevo evento', 'caaguazu' ),
			'edit_item'          => __( 'Editar evento', 'caaguazu' ),
			'view_item'          => __( 'Ver evento', 'caaguazu' ),
			'search_items'       => __( 'Buscar eventos', 'caaguazu' ),
			'not_found'          => __( 'No se encontraron eventos.', 'caaguazu' ),
			'not_found_in_trash' => __( 'No hay eventos en la papelera.', 'caaguazu' ),
			'menu_name'          => __( 'Eventos', 'caaguazu' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-calendar-alt',
		'has_archive'       => 'agenda',
		'rewrite'           => array( 'slug' => 'agenda' ),
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'show_in_nav_menus' => true,
	) );
}
add_action( 'init', 'caaguazu_register_event_cpt' );

function caaguazu_register_event_meta() {
	register_post_meta( 'caaguazu_event', '_caaguazu_event_date', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
	register_post_meta( 'caaguazu_event', '_caaguazu_event_location', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_event_meta' );

function caaguazu_event_metabox() {
	add_meta_box( 'caaguazu_event_meta', __( 'Datos del evento', 'caaguazu' ), 'caaguazu_event_metabox_html', 'caaguazu_event', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_event_metabox' );

function caaguazu_event_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_event_meta', 'caaguazu_event_meta_nonce' );
	$date     = get_post_meta( $post->ID, '_caaguazu_event_date', true );
	$location = get_post_meta( $post->ID, '_caaguazu_event_location', true );
	?>
	<p>
		<label for="caaguazu_event_date"><strong><?php esc_html_e( 'Fecha del evento', 'caaguazu' ); ?></strong></label><br>
		<input type="date" id="caaguazu_event_date" name="caaguazu_event_date" value="<?php echo esc_attr( $date ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_location"><strong><?php esc_html_e( 'Lugar', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_event_location" name="caaguazu_event_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
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
add_action( 'save_post_caaguazu_event', 'caaguazu_event_save_meta' );

/**
 * El archivo de eventos ordena por fecha del evento (ascendente), no por
 * fecha de publicación — así los próximos aparecen primero.
 */
function caaguazu_event_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'caaguazu_event' ) ) {
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
		'post_type'      => 'caaguazu_event',
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
