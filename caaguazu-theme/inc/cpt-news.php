<?php
/**
 * Custom Post Type: Noticias del departamento.
 *
 * Se usa un CPT propio (no posts) para mantener separadas las noticias
 * institucionales de cualquier blog secundario que el admin pueda querer
 * en el futuro.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_news_cpt() {
	register_post_type( 'caaguazu_news', array(
		'labels' => array(
			'name'               => __( 'Noticias', 'caaguazu' ),
			'singular_name'      => __( 'Noticia', 'caaguazu' ),
			'add_new'            => __( 'Añadir noticia', 'caaguazu' ),
			'add_new_item'       => __( 'Añadir nueva noticia', 'caaguazu' ),
			'edit_item'          => __( 'Editar noticia', 'caaguazu' ),
			'new_item'           => __( 'Nueva noticia', 'caaguazu' ),
			'view_item'          => __( 'Ver noticia', 'caaguazu' ),
			'search_items'       => __( 'Buscar noticias', 'caaguazu' ),
			'not_found'          => __( 'No se encontraron noticias.', 'caaguazu' ),
			'not_found_in_trash' => __( 'No hay noticias en la papelera.', 'caaguazu' ),
			'menu_name'          => __( 'Noticias', 'caaguazu' ),
		),
		'public'        => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-megaphone',
		'has_archive'   => 'noticias',
		'rewrite'       => array( 'slug' => 'noticias' ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
		'show_in_nav_menus' => true,
	) );

	register_taxonomy( 'caaguazu_news_cat', 'caaguazu_news', array(
		'labels' => array(
			'name'          => __( 'Categorías de noticia', 'caaguazu' ),
			'singular_name' => __( 'Categoría', 'caaguazu' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => array( 'slug' => 'noticias-categoria' ),
	) );
}
add_action( 'init', 'caaguazu_register_news_cpt' );

/**
 * Meta: minutos de lectura (mostrado en la tarjeta del home original como "4 min").
 */
function caaguazu_register_news_meta() {
	register_post_meta( 'caaguazu_news', '_caaguazu_read_minutes', array(
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_news_meta' );

/**
 * Metabox simple para el campo de minutos. Sin dependencias de ACF.
 */
function caaguazu_news_metabox() {
	add_meta_box(
		'caaguazu_news_meta',
		__( 'Datos de la noticia', 'caaguazu' ),
		'caaguazu_news_metabox_html',
		'caaguazu_news',
		'side'
	);
}
add_action( 'add_meta_boxes', 'caaguazu_news_metabox' );

function caaguazu_news_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_news_meta', 'caaguazu_news_meta_nonce' );
	$mins = get_post_meta( $post->ID, '_caaguazu_read_minutes', true );
	?>
	<p>
		<label for="caaguazu_read_minutes"><strong><?php esc_html_e( 'Minutos de lectura', 'caaguazu' ); ?></strong></label><br>
		<input type="number" id="caaguazu_read_minutes" name="caaguazu_read_minutes" value="<?php echo esc_attr( $mins ); ?>" min="1" max="60" style="width:80px">
	</p>
	<p style="color:#666;font-size:12px">
		<?php esc_html_e( 'Se muestra en las tarjetas como "X min de lectura". Dejar vacío para ocultar.', 'caaguazu' ); ?>
	</p>
	<?php
}

function caaguazu_news_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_news_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_news_meta_nonce'], 'caaguazu_news_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_read_minutes'] ) ) {
		$mins = (int) $_POST['caaguazu_read_minutes'];
		if ( $mins > 0 ) {
			update_post_meta( $post_id, '_caaguazu_read_minutes', $mins );
		} else {
			delete_post_meta( $post_id, '_caaguazu_read_minutes' );
		}
	}
}
add_action( 'save_post_caaguazu_news', 'caaguazu_news_save_meta' );

/**
 * Devuelve la primera categoría de noticia como etiqueta corta para la tarjeta.
 */
function caaguazu_news_primary_term( $post_id ) {
	$terms = get_the_terms( $post_id, 'caaguazu_news_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return $terms[0]->name;
	}
	return '';
}
