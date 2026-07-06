<?php
/**
 * Custom Post Type: Artesanos — "los rostros de la madera".
 *
 * Convierte la página estática de perfiles (migrada del sitio de turismo)
 * en un directorio dinámico. Mismo patrón que cpt-news.php/cpt-event.php.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_artisan_cpt() {
	register_post_type( 'caaguazu_artisan', array(
		'labels' => array(
			'name'               => __( 'Artesanos', 'caaguazu' ),
			'singular_name'      => __( 'Artesano', 'caaguazu' ),
			'add_new'            => __( 'Añadir artesano', 'caaguazu' ),
			'add_new_item'       => __( 'Añadir nuevo artesano', 'caaguazu' ),
			'edit_item'          => __( 'Editar artesano', 'caaguazu' ),
			'view_item'          => __( 'Ver artesano', 'caaguazu' ),
			'search_items'       => __( 'Buscar artesanos', 'caaguazu' ),
			'not_found'          => __( 'No se encontraron artesanos.', 'caaguazu' ),
			'not_found_in_trash' => __( 'No hay artesanos en la papelera.', 'caaguazu' ),
			'menu_name'          => __( 'Artesanos', 'caaguazu' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-hammer',
		'has_archive'       => 'artesanos',
		'rewrite'           => array( 'slug' => 'artesanos' ),
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'show_in_nav_menus' => true,
	) );
}
add_action( 'init', 'caaguazu_register_artisan_cpt' );

function caaguazu_register_artisan_meta() {
	foreach ( array( '_caaguazu_artisan_craft', '_caaguazu_artisan_location', '_caaguazu_artisan_quote' ) as $key ) {
		register_post_meta( 'caaguazu_artisan', $key, array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
}
add_action( 'init', 'caaguazu_register_artisan_meta' );

function caaguazu_artisan_metabox() {
	add_meta_box( 'caaguazu_artisan_meta', __( 'Datos del artesano', 'caaguazu' ), 'caaguazu_artisan_metabox_html', 'caaguazu_artisan', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_artisan_metabox' );

function caaguazu_artisan_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_artisan_meta', 'caaguazu_artisan_meta_nonce' );
	$craft    = get_post_meta( $post->ID, '_caaguazu_artisan_craft', true );
	$location = get_post_meta( $post->ID, '_caaguazu_artisan_location', true );
	$quote    = get_post_meta( $post->ID, '_caaguazu_artisan_quote', true );
	?>
	<p>
		<label for="caaguazu_artisan_craft"><strong><?php esc_html_e( 'Oficio', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_artisan_craft" name="caaguazu_artisan_craft" value="<?php echo esc_attr( $craft ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_artisan_location"><strong><?php esc_html_e( 'Zona', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_artisan_location" name="caaguazu_artisan_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_artisan_quote"><strong><?php esc_html_e( 'Frase destacada', 'caaguazu' ); ?></strong></label><br>
		<textarea id="caaguazu_artisan_quote" name="caaguazu_artisan_quote" style="width:100%"><?php echo esc_textarea( $quote ); ?></textarea>
	</p>
	<?php
}

function caaguazu_artisan_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_artisan_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_artisan_meta_nonce'], 'caaguazu_artisan_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_artisan_craft'] ) ) {
		update_post_meta( $post_id, '_caaguazu_artisan_craft', sanitize_text_field( $_POST['caaguazu_artisan_craft'] ) );
	}
	if ( isset( $_POST['caaguazu_artisan_location'] ) ) {
		update_post_meta( $post_id, '_caaguazu_artisan_location', sanitize_text_field( $_POST['caaguazu_artisan_location'] ) );
	}
	if ( isset( $_POST['caaguazu_artisan_quote'] ) ) {
		update_post_meta( $post_id, '_caaguazu_artisan_quote', sanitize_textarea_field( $_POST['caaguazu_artisan_quote'] ) );
	}
}
add_action( 'save_post_caaguazu_artisan', 'caaguazu_artisan_save_meta' );
