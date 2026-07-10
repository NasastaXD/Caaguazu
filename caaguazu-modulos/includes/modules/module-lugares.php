<?php
/**
 * Módulo Lugares — CPT propio, mismo criterio que module-instituciones.php:
 * una ficha de lugar (plaza, mirador, sitio histórico) no es contenido
 * cronológico, es información que se actualiza de vez en cuando.
 *
 * No reemplaza las páginas de Turismo (`caaguazu-turismo`, jerarquía fija de
 * ~25 páginas curadas: historia, gastronomía, cultura guaraní) — Lugares es
 * un directorio abierto y extensible que cualquiera puede ir sumando de a
 * uno, sin tener que tocar la jerarquía fija de Turismo para cada lugar
 * nuevo. Parte de V5 (civic CMS).
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_lugar_cpt() {
	register_post_type( 'lugar', array(
		'labels' => array(
			'name'               => __( 'Lugares', 'caaguazu-modulos' ),
			'singular_name'      => __( 'Lugar', 'caaguazu-modulos' ),
			'add_new'            => __( 'Añadir lugar', 'caaguazu-modulos' ),
			'add_new_item'       => __( 'Añadir nuevo lugar', 'caaguazu-modulos' ),
			'edit_item'          => __( 'Editar lugar', 'caaguazu-modulos' ),
			'view_item'          => __( 'Ver lugar', 'caaguazu-modulos' ),
			'search_items'       => __( 'Buscar lugares', 'caaguazu-modulos' ),
			'not_found'          => __( 'No se encontraron lugares.', 'caaguazu-modulos' ),
			'not_found_in_trash' => __( 'No hay lugares en la papelera.', 'caaguazu-modulos' ),
			'menu_name'          => __( 'Lugares', 'caaguazu-modulos' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-location-alt',
		'has_archive'       => 'lugares',
		'rewrite'           => array( 'slug' => 'lugares' ),
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'show_in_nav_menus' => true,
	) );
}
add_action( 'init', 'caaguazu_register_lugar_cpt' );

function caaguazu_register_tipo_lugar_taxonomy() {
	register_taxonomy( 'tipo_lugar', 'lugar', array(
		'labels' => array(
			'name'          => __( 'Tipos de lugar', 'caaguazu-modulos' ),
			'singular_name' => __( 'Tipo de lugar', 'caaguazu-modulos' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'tipo-lugar' ),
	) );
}
add_action( 'init', 'caaguazu_register_tipo_lugar_taxonomy', 0 );

function caaguazu_lugar_ensure_terms() {
	caaguazu_ensure_terms( 'tipo_lugar', array(
		__( 'Turismo', 'caaguazu-modulos' ),
		__( 'Cultura', 'caaguazu-modulos' ),
		__( 'Historia', 'caaguazu-modulos' ),
		__( 'Naturaleza', 'caaguazu-modulos' ),
		__( 'Espacio público', 'caaguazu-modulos' ),
		__( 'Gastronomía', 'caaguazu-modulos' ),
		__( 'Referencia local', 'caaguazu-modulos' ),
	) );
}
add_action( 'init', 'caaguazu_lugar_ensure_terms', 20 );

function caaguazu_register_lugar_meta() {
	$args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	foreach ( array(
		'_caaguazu_lugar_direccion',
		'_caaguazu_lugar_horario',
		'_caaguazu_lugar_contacto',
		'_caaguazu_lugar_referencia',
		'_caaguazu_lugar_mapa_url',
		'_caaguazu_lugar_experiencia',
	) as $key ) {
		register_post_meta( 'lugar', $key, $args );
	}
}
add_action( 'init', 'caaguazu_register_lugar_meta' );

function caaguazu_lugar_metabox() {
	add_meta_box( 'caaguazu_lugar_meta', __( 'Datos del lugar', 'caaguazu-modulos' ), 'caaguazu_lugar_metabox_html', 'lugar', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_lugar_metabox' );

function caaguazu_lugar_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_lugar_meta', 'caaguazu_lugar_meta_nonce' );
	$direccion   = get_post_meta( $post->ID, '_caaguazu_lugar_direccion', true );
	$horario     = get_post_meta( $post->ID, '_caaguazu_lugar_horario', true );
	$contacto    = get_post_meta( $post->ID, '_caaguazu_lugar_contacto', true );
	$referencia  = get_post_meta( $post->ID, '_caaguazu_lugar_referencia', true );
	$mapa_url    = get_post_meta( $post->ID, '_caaguazu_lugar_mapa_url', true );
	$experiencia = get_post_meta( $post->ID, '_caaguazu_lugar_experiencia', true );
	?>
	<p>
		<label for="caaguazu_lugar_direccion"><strong><?php esc_html_e( 'Dirección', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_lugar_direccion" name="caaguazu_lugar_direccion" value="<?php echo esc_attr( $direccion ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_lugar_horario"><strong><?php esc_html_e( 'Horario', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_lugar_horario" name="caaguazu_lugar_horario" value="<?php echo esc_attr( $horario ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_lugar_contacto"><strong><?php esc_html_e( 'Contacto', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_lugar_contacto" name="caaguazu_lugar_contacto" value="<?php echo esc_attr( $contacto ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_lugar_referencia"><strong><?php esc_html_e( 'Referencia de ubicación', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_lugar_referencia" name="caaguazu_lugar_referencia" value="<?php echo esc_attr( $referencia ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Ej: frente a la plaza central', 'caaguazu-modulos' ); ?>">
	</p>
	<p>
		<label for="caaguazu_lugar_mapa_url"><strong><?php esc_html_e( 'Enlace de mapa', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="url" id="caaguazu_lugar_mapa_url" name="caaguazu_lugar_mapa_url" value="<?php echo esc_attr( $mapa_url ); ?>" style="width:100%" placeholder="https://maps.google.com/...">
	</p>
	<p>
		<label for="caaguazu_lugar_experiencia"><strong><?php esc_html_e( 'Tipo de experiencia', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_lugar_experiencia" name="caaguazu_lugar_experiencia" value="<?php echo esc_attr( $experiencia ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Ej: paseo familiar, visita breve, medio día', 'caaguazu-modulos' ); ?>">
	</p>
	<?php
}

function caaguazu_lugar_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_lugar_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_lugar_meta_nonce'], 'caaguazu_lugar_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$fields = array(
		'caaguazu_lugar_direccion'   => '_caaguazu_lugar_direccion',
		'caaguazu_lugar_horario'     => '_caaguazu_lugar_horario',
		'caaguazu_lugar_contacto'    => '_caaguazu_lugar_contacto',
		'caaguazu_lugar_referencia'  => '_caaguazu_lugar_referencia',
		'caaguazu_lugar_experiencia' => '_caaguazu_lugar_experiencia',
	);
	foreach ( $fields as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
		}
	}
	if ( isset( $_POST['caaguazu_lugar_mapa_url'] ) ) {
		update_post_meta( $post_id, '_caaguazu_lugar_mapa_url', esc_url_raw( $_POST['caaguazu_lugar_mapa_url'] ) );
	}
}
add_action( 'save_post_lugar', 'caaguazu_lugar_save_meta' );

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'pin',
		'label' => __( 'Lugares', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'lugar' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'lugares',
		'label' => __( 'Lugares', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'lugar' ),
	);
	return $items;
} );
