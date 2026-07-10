<?php
/**
 * Módulo Instituciones — CPT propio (no Categoría sobre Entradas, a
 * diferencia de Noticias/Agenda/Educación): una institución no es contenido
 * cronológico tipo blog, es una ficha con datos estructurados (dirección,
 * teléfono, horario) que se actualiza de vez en cuando — el mismo criterio
 * que ya usa Artesanos (`caaguazu-theme/inc/cpt-artisan.php`), que es la
 * plantilla que sigue este archivo.
 *
 * Parte de V5 (civic CMS): antes de esto, "Instituciones" no tenía ninguna
 * fuente de contenido en el sitio.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_institucion_cpt() {
	register_post_type( 'institucion', array(
		'labels' => array(
			'name'               => __( 'Instituciones', 'caaguazu-modulos' ),
			'singular_name'      => __( 'Institución', 'caaguazu-modulos' ),
			'add_new'            => __( 'Añadir institución', 'caaguazu-modulos' ),
			'add_new_item'       => __( 'Añadir nueva institución', 'caaguazu-modulos' ),
			'edit_item'          => __( 'Editar institución', 'caaguazu-modulos' ),
			'view_item'          => __( 'Ver institución', 'caaguazu-modulos' ),
			'search_items'       => __( 'Buscar instituciones', 'caaguazu-modulos' ),
			'not_found'          => __( 'No se encontraron instituciones.', 'caaguazu-modulos' ),
			'not_found_in_trash' => __( 'No hay instituciones en la papelera.', 'caaguazu-modulos' ),
			'menu_name'          => __( 'Instituciones', 'caaguazu-modulos' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-building',
		'has_archive'       => 'instituciones',
		'rewrite'           => array( 'slug' => 'instituciones' ),
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'show_in_nav_menus' => true,
	) );
}
add_action( 'init', 'caaguazu_register_institucion_cpt' );

/**
 * Taxonomía "Tipo de institución" — clasificación, no jerárquica en el
 * sentido de categoría/sub-categoría (una institución tiene un tipo, no un
 * árbol). `hierarchical => true` sólo controla si la UI es de checkboxes
 * (como Categorías) en vez de un campo de tags libres — acá conviene
 * checkboxes porque los tipos son un set cerrado, no texto libre.
 */
function caaguazu_register_tipo_institucion_taxonomy() {
	register_taxonomy( 'tipo_institucion', 'institucion', array(
		'labels' => array(
			'name'          => __( 'Tipos de institución', 'caaguazu-modulos' ),
			'singular_name' => __( 'Tipo de institución', 'caaguazu-modulos' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'tipo-institucion' ),
	) );
}
add_action( 'init', 'caaguazu_register_tipo_institucion_taxonomy', 0 );

/**
 * Términos base del tipo de institución — clasificación, no contenido (ver
 * caaguazu_ensure_terms() en caaguazu-modulos.php). Se corre en `init` con
 * prioridad después de que la taxonomía quede registrada.
 */
function caaguazu_institucion_ensure_terms() {
	caaguazu_ensure_terms( 'tipo_institucion', array(
		__( 'Educativa', 'caaguazu-modulos' ),
		__( 'Municipal', 'caaguazu-modulos' ),
		__( 'Cultural', 'caaguazu-modulos' ),
		__( 'Comunitaria', 'caaguazu-modulos' ),
		__( 'Salud', 'caaguazu-modulos' ),
		__( 'Seguridad', 'caaguazu-modulos' ),
		__( 'Juvenil', 'caaguazu-modulos' ),
		__( 'Servicio público', 'caaguazu-modulos' ),
	) );
}
add_action( 'init', 'caaguazu_institucion_ensure_terms', 20 );

function caaguazu_register_institucion_meta() {
	$args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	foreach ( array(
		'_caaguazu_inst_direccion',
		'_caaguazu_inst_telefono',
		'_caaguazu_inst_horario',
		'_caaguazu_inst_sitio_web',
		'_caaguazu_inst_redes',
		'_caaguazu_inst_email',
	) as $key ) {
		register_post_meta( 'institucion', $key, $args );
	}
}
add_action( 'init', 'caaguazu_register_institucion_meta' );

function caaguazu_institucion_metabox() {
	add_meta_box( 'caaguazu_institucion_meta', __( 'Datos de la institución', 'caaguazu-modulos' ), 'caaguazu_institucion_metabox_html', 'institucion', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_institucion_metabox' );

function caaguazu_institucion_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_institucion_meta', 'caaguazu_institucion_meta_nonce' );
	$direccion = get_post_meta( $post->ID, '_caaguazu_inst_direccion', true );
	$telefono  = get_post_meta( $post->ID, '_caaguazu_inst_telefono', true );
	$horario   = get_post_meta( $post->ID, '_caaguazu_inst_horario', true );
	$web       = get_post_meta( $post->ID, '_caaguazu_inst_sitio_web', true );
	$redes     = get_post_meta( $post->ID, '_caaguazu_inst_redes', true );
	$email     = get_post_meta( $post->ID, '_caaguazu_inst_email', true );
	?>
	<p>
		<label for="caaguazu_inst_direccion"><strong><?php esc_html_e( 'Dirección', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_inst_direccion" name="caaguazu_inst_direccion" value="<?php echo esc_attr( $direccion ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_inst_telefono"><strong><?php esc_html_e( 'Teléfono', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_inst_telefono" name="caaguazu_inst_telefono" value="<?php echo esc_attr( $telefono ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_inst_horario"><strong><?php esc_html_e( 'Horario', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_inst_horario" name="caaguazu_inst_horario" value="<?php echo esc_attr( $horario ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_inst_email"><strong><?php esc_html_e( 'Correo de contacto', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="email" id="caaguazu_inst_email" name="caaguazu_inst_email" value="<?php echo esc_attr( $email ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_inst_sitio_web"><strong><?php esc_html_e( 'Sitio web', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="url" id="caaguazu_inst_sitio_web" name="caaguazu_inst_sitio_web" value="<?php echo esc_attr( $web ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_inst_redes"><strong><?php esc_html_e( 'Redes sociales', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_inst_redes" name="caaguazu_inst_redes" value="<?php echo esc_attr( $redes ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Enlace a Facebook/Instagram, separados por coma', 'caaguazu-modulos' ); ?>">
	</p>
	<?php
}

function caaguazu_institucion_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_institucion_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_institucion_meta_nonce'], 'caaguazu_institucion_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$fields = array(
		'caaguazu_inst_direccion' => '_caaguazu_inst_direccion',
		'caaguazu_inst_telefono'  => '_caaguazu_inst_telefono',
		'caaguazu_inst_horario'   => '_caaguazu_inst_horario',
		'caaguazu_inst_sitio_web' => '_caaguazu_inst_sitio_web',
		'caaguazu_inst_redes'     => '_caaguazu_inst_redes',
	);
	foreach ( $fields as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
		}
	}
	if ( isset( $_POST['caaguazu_inst_email'] ) ) {
		update_post_meta( $post_id, '_caaguazu_inst_email', sanitize_email( $_POST['caaguazu_inst_email'] ) );
	}
}
add_action( 'save_post_institucion', 'caaguazu_institucion_save_meta' );

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'building',
		'label' => __( 'Instituciones', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'institucion' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'instituciones',
		'label' => __( 'Instituciones', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'institucion' ),
	);
	return $items;
} );
