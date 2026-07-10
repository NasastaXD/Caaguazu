<?php
/**
 * Módulo Servicios — CPT propio, mismo criterio que module-instituciones.php
 * y module-lugares.php: una ficha de servicio (trámite, guía práctica) no es
 * contenido cronológico, es información de referencia que se actualiza de
 * vez en cuando. Parte de V5 (civic CMS).
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_servicio_cpt() {
	register_post_type( 'servicio', array(
		'labels' => array(
			'name'               => __( 'Servicios', 'caaguazu-modulos' ),
			'singular_name'      => __( 'Servicio', 'caaguazu-modulos' ),
			'add_new'            => __( 'Añadir servicio', 'caaguazu-modulos' ),
			'add_new_item'       => __( 'Añadir nuevo servicio', 'caaguazu-modulos' ),
			'edit_item'          => __( 'Editar servicio', 'caaguazu-modulos' ),
			'view_item'          => __( 'Ver servicio', 'caaguazu-modulos' ),
			'search_items'       => __( 'Buscar servicios', 'caaguazu-modulos' ),
			'not_found'          => __( 'No se encontraron servicios.', 'caaguazu-modulos' ),
			'not_found_in_trash' => __( 'No hay servicios en la papelera.', 'caaguazu-modulos' ),
			'menu_name'          => __( 'Servicios', 'caaguazu-modulos' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-admin-tools',
		'has_archive'       => 'servicios',
		'rewrite'           => array( 'slug' => 'servicios' ),
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'show_in_nav_menus' => true,
	) );
}
add_action( 'init', 'caaguazu_register_servicio_cpt' );

function caaguazu_register_categoria_servicio_taxonomy() {
	register_taxonomy( 'categoria_servicio', 'servicio', array(
		'labels' => array(
			'name'          => __( 'Categorías de servicio', 'caaguazu-modulos' ),
			'singular_name' => __( 'Categoría de servicio', 'caaguazu-modulos' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'categoria-servicio' ),
	) );
}
add_action( 'init', 'caaguazu_register_categoria_servicio_taxonomy', 0 );

function caaguazu_servicio_ensure_terms() {
	caaguazu_ensure_terms( 'categoria_servicio', array(
		__( 'Trámites', 'caaguazu-modulos' ),
		__( 'Educación', 'caaguazu-modulos' ),
		__( 'Salud', 'caaguazu-modulos' ),
		__( 'Comunidad', 'caaguazu-modulos' ),
		__( 'Información municipal', 'caaguazu-modulos' ),
		__( 'Recursos útiles', 'caaguazu-modulos' ),
		__( 'Contactos', 'caaguazu-modulos' ),
	) );
}
add_action( 'init', 'caaguazu_servicio_ensure_terms', 20 );

/**
 * Estado del servicio: set cerrado de 4 valores — vive como meta con
 * sanitización por whitelist (no como taxonomía; es un estado, no una
 * clasificación cruzada, mismo criterio que _czu_estado_verificacion en
 * caaguazu-editor-ux).
 */
function caaguazu_servicio_estado_values() {
	return array(
		'disponible'    => __( 'Disponible', 'caaguazu-modulos' ),
		'proximamente'  => __( 'Próximamente', 'caaguazu-modulos' ),
		'en-revision'   => __( 'En revisión', 'caaguazu-modulos' ),
		'desactualizado'=> __( 'Desactualizado', 'caaguazu-modulos' ),
	);
}

function caaguazu_sanitize_servicio_estado( $value ) {
	return array_key_exists( $value, caaguazu_servicio_estado_values() ) ? $value : '';
}

function caaguazu_register_servicio_meta() {
	$text_args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	foreach ( array(
		'_caaguazu_serv_institucion',
		'_caaguazu_serv_requisitos',
		'_caaguazu_serv_horario',
		'_caaguazu_serv_contacto',
		'_caaguazu_serv_enlace',
	) as $key ) {
		register_post_meta( 'servicio', $key, $text_args );
	}
	register_post_meta( 'servicio', '_caaguazu_serv_estado', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'caaguazu_sanitize_servicio_estado',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_servicio_meta' );

function caaguazu_servicio_metabox() {
	add_meta_box( 'caaguazu_servicio_meta', __( 'Datos del servicio', 'caaguazu-modulos' ), 'caaguazu_servicio_metabox_html', 'servicio', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_servicio_metabox' );

function caaguazu_servicio_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_servicio_meta', 'caaguazu_servicio_meta_nonce' );
	$institucion = get_post_meta( $post->ID, '_caaguazu_serv_institucion', true );
	$requisitos  = get_post_meta( $post->ID, '_caaguazu_serv_requisitos', true );
	$horario     = get_post_meta( $post->ID, '_caaguazu_serv_horario', true );
	$contacto    = get_post_meta( $post->ID, '_caaguazu_serv_contacto', true );
	$enlace      = get_post_meta( $post->ID, '_caaguazu_serv_enlace', true );
	$estado      = get_post_meta( $post->ID, '_caaguazu_serv_estado', true );
	?>
	<p>
		<label for="caaguazu_serv_institucion"><strong><?php esc_html_e( 'Institución responsable', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_serv_institucion" name="caaguazu_serv_institucion" value="<?php echo esc_attr( $institucion ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_serv_requisitos"><strong><?php esc_html_e( 'Requisitos', 'caaguazu-modulos' ); ?></strong></label><br>
		<textarea id="caaguazu_serv_requisitos" name="caaguazu_serv_requisitos" style="width:100%"><?php echo esc_textarea( $requisitos ); ?></textarea>
	</p>
	<p>
		<label for="caaguazu_serv_horario"><strong><?php esc_html_e( 'Horario de atención', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_serv_horario" name="caaguazu_serv_horario" value="<?php echo esc_attr( $horario ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_serv_contacto"><strong><?php esc_html_e( 'Contacto', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_serv_contacto" name="caaguazu_serv_contacto" value="<?php echo esc_attr( $contacto ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_serv_enlace"><strong><?php esc_html_e( 'Enlace oficial', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="url" id="caaguazu_serv_enlace" name="caaguazu_serv_enlace" value="<?php echo esc_attr( $enlace ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_serv_estado"><strong><?php esc_html_e( 'Estado', 'caaguazu-modulos' ); ?></strong></label><br>
		<select id="caaguazu_serv_estado" name="caaguazu_serv_estado" style="width:100%">
			<option value=""><?php esc_html_e( 'Sin definir', 'caaguazu-modulos' ); ?></option>
			<?php foreach ( caaguazu_servicio_estado_values() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $estado, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

function caaguazu_servicio_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_servicio_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_servicio_meta_nonce'], 'caaguazu_servicio_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_serv_institucion'] ) ) {
		update_post_meta( $post_id, '_caaguazu_serv_institucion', sanitize_text_field( $_POST['caaguazu_serv_institucion'] ) );
	}
	if ( isset( $_POST['caaguazu_serv_requisitos'] ) ) {
		update_post_meta( $post_id, '_caaguazu_serv_requisitos', sanitize_textarea_field( $_POST['caaguazu_serv_requisitos'] ) );
	}
	if ( isset( $_POST['caaguazu_serv_horario'] ) ) {
		update_post_meta( $post_id, '_caaguazu_serv_horario', sanitize_text_field( $_POST['caaguazu_serv_horario'] ) );
	}
	if ( isset( $_POST['caaguazu_serv_contacto'] ) ) {
		update_post_meta( $post_id, '_caaguazu_serv_contacto', sanitize_text_field( $_POST['caaguazu_serv_contacto'] ) );
	}
	if ( isset( $_POST['caaguazu_serv_enlace'] ) ) {
		update_post_meta( $post_id, '_caaguazu_serv_enlace', esc_url_raw( $_POST['caaguazu_serv_enlace'] ) );
	}
	if ( isset( $_POST['caaguazu_serv_estado'] ) ) {
		update_post_meta( $post_id, '_caaguazu_serv_estado', caaguazu_sanitize_servicio_estado( $_POST['caaguazu_serv_estado'] ) );
	}
}
add_action( 'save_post_servicio', 'caaguazu_servicio_save_meta' );

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'tool',
		'label' => __( 'Servicios', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'servicio' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'servicios',
		'label' => __( 'Servicios', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'servicio' ),
	);
	return $items;
} );
