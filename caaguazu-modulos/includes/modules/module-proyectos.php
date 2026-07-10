<?php
/**
 * Módulo Proyectos — CPT propio, mismo criterio que module-instituciones.php.
 * Antes de esto, "Proyectos digitales" no tenía ninguna fuente de contenido
 * propia más allá del hub de Ecosistema (ver nota histórica en
 * caaguazu-theme/README.md, sección de rediseño V2) — este módulo la crea.
 * No reemplaza Ecosistema: Ecosistema son los SUB-PORTALES completos
 * (Turismo, Educación, CEAD); Proyectos son iniciativas puntuales más chicas
 * (una app, un programa, una colaboración) que no ameritan su propio
 * sub-portal. Parte de V5 (civic CMS).
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_proyecto_cpt() {
	register_post_type( 'proyecto', array(
		'labels' => array(
			'name'               => __( 'Proyectos', 'caaguazu-modulos' ),
			'singular_name'      => __( 'Proyecto', 'caaguazu-modulos' ),
			'add_new'            => __( 'Añadir proyecto', 'caaguazu-modulos' ),
			'add_new_item'       => __( 'Añadir nuevo proyecto', 'caaguazu-modulos' ),
			'edit_item'          => __( 'Editar proyecto', 'caaguazu-modulos' ),
			'view_item'          => __( 'Ver proyecto', 'caaguazu-modulos' ),
			'search_items'       => __( 'Buscar proyectos', 'caaguazu-modulos' ),
			'not_found'          => __( 'No se encontraron proyectos.', 'caaguazu-modulos' ),
			'not_found_in_trash' => __( 'No hay proyectos en la papelera.', 'caaguazu-modulos' ),
			'menu_name'          => __( 'Proyectos', 'caaguazu-modulos' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-flag',
		'has_archive'       => 'proyectos',
		'rewrite'           => array( 'slug' => 'proyectos' ),
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'show_in_nav_menus' => true,
	) );
}
add_action( 'init', 'caaguazu_register_proyecto_cpt' );

function caaguazu_register_area_proyecto_taxonomy() {
	register_taxonomy( 'area_proyecto', 'proyecto', array(
		'labels' => array(
			'name'          => __( 'Áreas de proyecto', 'caaguazu-modulos' ),
			'singular_name' => __( 'Área de proyecto', 'caaguazu-modulos' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'area-proyecto' ),
	) );
}
add_action( 'init', 'caaguazu_register_area_proyecto_taxonomy', 0 );

function caaguazu_proyecto_ensure_terms() {
	caaguazu_ensure_terms( 'area_proyecto', array(
		__( 'Digital', 'caaguazu-modulos' ),
		__( 'Educación', 'caaguazu-modulos' ),
		__( 'Cultura', 'caaguazu-modulos' ),
		__( 'Comunidad', 'caaguazu-modulos' ),
		__( 'Juventud', 'caaguazu-modulos' ),
		__( 'Participación', 'caaguazu-modulos' ),
	) );
}
add_action( 'init', 'caaguazu_proyecto_ensure_terms', 20 );

/**
 * Estado del proyecto: set cerrado de 4 valores — meta con sanitización por
 * whitelist, mismo criterio que caaguazu_servicio_estado_values() en
 * module-servicios.php (un estado no es una clasificación cruzada, no
 * amerita ser taxonomía).
 */
function caaguazu_proyecto_estado_values() {
	return array(
		'en-preparacion' => __( 'En preparación', 'caaguazu-modulos' ),
		'activo'         => __( 'Activo', 'caaguazu-modulos' ),
		'pausado'        => __( 'Pausado', 'caaguazu-modulos' ),
		'finalizado'     => __( 'Finalizado', 'caaguazu-modulos' ),
	);
}

function caaguazu_sanitize_proyecto_estado( $value ) {
	return array_key_exists( $value, caaguazu_proyecto_estado_values() ) ? $value : '';
}

function caaguazu_register_proyecto_meta() {
	$text_args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	register_post_meta( 'proyecto', '_caaguazu_proy_responsable', $text_args );
	register_post_meta( 'proyecto', '_caaguazu_proy_enlace', $text_args );
	register_post_meta( 'proyecto', '_caaguazu_proy_fecha_inicio', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
	register_post_meta( 'proyecto', '_caaguazu_proy_estado', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'caaguazu_sanitize_proyecto_estado',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_proyecto_meta' );

function caaguazu_proyecto_metabox() {
	add_meta_box( 'caaguazu_proyecto_meta', __( 'Datos del proyecto', 'caaguazu-modulos' ), 'caaguazu_proyecto_metabox_html', 'proyecto', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_proyecto_metabox' );

function caaguazu_proyecto_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_proyecto_meta', 'caaguazu_proyecto_meta_nonce' );
	$responsable  = get_post_meta( $post->ID, '_caaguazu_proy_responsable', true );
	$enlace       = get_post_meta( $post->ID, '_caaguazu_proy_enlace', true );
	$fecha_inicio = get_post_meta( $post->ID, '_caaguazu_proy_fecha_inicio', true );
	$estado       = get_post_meta( $post->ID, '_caaguazu_proy_estado', true );
	?>
	<p>
		<label for="caaguazu_proy_responsable"><strong><?php esc_html_e( 'Responsable', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_proy_responsable" name="caaguazu_proy_responsable" value="<?php echo esc_attr( $responsable ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_proy_fecha_inicio"><strong><?php esc_html_e( 'Fecha de inicio', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="date" id="caaguazu_proy_fecha_inicio" name="caaguazu_proy_fecha_inicio" value="<?php echo esc_attr( $fecha_inicio ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_proy_enlace"><strong><?php esc_html_e( 'Enlace', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="url" id="caaguazu_proy_enlace" name="caaguazu_proy_enlace" value="<?php echo esc_attr( $enlace ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_proy_estado"><strong><?php esc_html_e( 'Estado', 'caaguazu-modulos' ); ?></strong></label><br>
		<select id="caaguazu_proy_estado" name="caaguazu_proy_estado" style="width:100%">
			<option value=""><?php esc_html_e( 'Sin definir', 'caaguazu-modulos' ); ?></option>
			<?php foreach ( caaguazu_proyecto_estado_values() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $estado, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

function caaguazu_proyecto_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_proyecto_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_proyecto_meta_nonce'], 'caaguazu_proyecto_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_proy_responsable'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proy_responsable', sanitize_text_field( $_POST['caaguazu_proy_responsable'] ) );
	}
	if ( isset( $_POST['caaguazu_proy_fecha_inicio'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proy_fecha_inicio', sanitize_text_field( $_POST['caaguazu_proy_fecha_inicio'] ) );
	}
	if ( isset( $_POST['caaguazu_proy_enlace'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proy_enlace', esc_url_raw( $_POST['caaguazu_proy_enlace'] ) );
	}
	if ( isset( $_POST['caaguazu_proy_estado'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proy_estado', caaguazu_sanitize_proyecto_estado( $_POST['caaguazu_proy_estado'] ) );
	}
}
add_action( 'save_post_proyecto', 'caaguazu_proyecto_save_meta' );

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'flag',
		'label' => __( 'Proyectos', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'proyecto' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'proyectos',
		'label' => __( 'Proyectos', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'proyecto' ),
	);
	return $items;
} );
