<?php
/**
 * Custom Post Type: Propuestas ciudadanas ("Proponer institución/lugar/
 * servicio/proyecto/evento"). Mismo criterio que cpt-report.php: gestión
 * interna, no contenido editorial — sin archive, sin REST, no público en
 * el front-end. Antes, los CTA "Proponer institución"/"Proponer un
 * lugar"/etc. de los archivos de V5 apuntaban al formulario genérico de
 * Contacto; esto les da un canal propio y estructurado, sin mezclarlos con
 * el 311 de reportes de problemas (`caaguazu_report`), que es otro dominio.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_proposal_types() {
	return array(
		'institucion' => __( 'Institución', 'caaguazu' ),
		'lugar'       => __( 'Lugar', 'caaguazu' ),
		'servicio'    => __( 'Servicio', 'caaguazu' ),
		'proyecto'    => __( 'Proyecto', 'caaguazu' ),
		'evento'      => __( 'Evento', 'caaguazu' ),
		'otro'        => __( 'Otra información', 'caaguazu' ),
	);
}

function caaguazu_register_proposal_cpt() {
	register_post_type( 'caaguazu_proposal', array(
		'labels' => array(
			'name'               => __( 'Propuestas', 'caaguazu' ),
			'singular_name'      => __( 'Propuesta', 'caaguazu' ),
			'add_new'            => __( 'Añadir propuesta', 'caaguazu' ),
			'add_new_item'       => __( 'Añadir nueva propuesta', 'caaguazu' ),
			'edit_item'          => __( 'Editar propuesta', 'caaguazu' ),
			'view_item'          => __( 'Ver propuesta', 'caaguazu' ),
			'search_items'       => __( 'Buscar propuestas', 'caaguazu' ),
			'not_found'          => __( 'No se encontraron propuestas.', 'caaguazu' ),
			'not_found_in_trash' => __( 'No hay propuestas en la papelera.', 'caaguazu' ),
			'menu_name'          => __( 'Propuestas', 'caaguazu' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-lightbulb',
		'has_archive'         => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor' ),
	) );
}
add_action( 'init', 'caaguazu_register_proposal_cpt' );

function caaguazu_register_proposal_meta() {
	$fields = array(
		'_caaguazu_proposal_type'          => 'string',
		'_caaguazu_proposal_contact_name'  => 'string',
		'_caaguazu_proposal_contact_email' => 'string',
		'_caaguazu_proposal_contact_phone' => 'string',
	);
	foreach ( $fields as $key => $type ) {
		register_post_meta( 'caaguazu_proposal', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
}
add_action( 'init', 'caaguazu_register_proposal_meta' );

function caaguazu_proposal_metabox() {
	add_meta_box(
		'caaguazu_proposal_meta',
		__( 'Datos de la propuesta', 'caaguazu' ),
		'caaguazu_proposal_metabox_html',
		'caaguazu_proposal',
		'side'
	);
}
add_action( 'add_meta_boxes', 'caaguazu_proposal_metabox' );

function caaguazu_proposal_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_proposal_meta', 'caaguazu_proposal_meta_nonce' );
	$type  = get_post_meta( $post->ID, '_caaguazu_proposal_type', true );
	$name  = get_post_meta( $post->ID, '_caaguazu_proposal_contact_name', true );
	$email = get_post_meta( $post->ID, '_caaguazu_proposal_contact_email', true );
	$phone = get_post_meta( $post->ID, '_caaguazu_proposal_contact_phone', true );
	?>
	<p>
		<label for="caaguazu_proposal_type"><strong><?php esc_html_e( 'Tipo', 'caaguazu' ); ?></strong></label><br>
		<select id="caaguazu_proposal_type" name="caaguazu_proposal_type" style="width:100%">
			<?php foreach ( caaguazu_proposal_types() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="caaguazu_proposal_contact_name"><strong><?php esc_html_e( 'Nombre de contacto', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_proposal_contact_name" name="caaguazu_proposal_contact_name" value="<?php echo esc_attr( $name ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_proposal_contact_email"><strong><?php esc_html_e( 'Email de contacto', 'caaguazu' ); ?></strong></label><br>
		<input type="email" id="caaguazu_proposal_contact_email" name="caaguazu_proposal_contact_email" value="<?php echo esc_attr( $email ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_proposal_contact_phone"><strong><?php esc_html_e( 'Teléfono de contacto', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_proposal_contact_phone" name="caaguazu_proposal_contact_phone" value="<?php echo esc_attr( $phone ); ?>" style="width:100%">
	</p>
	<?php
}

function caaguazu_proposal_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_proposal_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_proposal_meta_nonce'], 'caaguazu_proposal_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_proposal_type'] ) ) {
		$type = sanitize_key( $_POST['caaguazu_proposal_type'] );
		if ( array_key_exists( $type, caaguazu_proposal_types() ) ) {
			update_post_meta( $post_id, '_caaguazu_proposal_type', $type );
		}
	}
	if ( isset( $_POST['caaguazu_proposal_contact_name'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proposal_contact_name', sanitize_text_field( $_POST['caaguazu_proposal_contact_name'] ) );
	}
	if ( isset( $_POST['caaguazu_proposal_contact_email'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proposal_contact_email', sanitize_email( $_POST['caaguazu_proposal_contact_email'] ) );
	}
	if ( isset( $_POST['caaguazu_proposal_contact_phone'] ) ) {
		update_post_meta( $post_id, '_caaguazu_proposal_contact_phone', sanitize_text_field( $_POST['caaguazu_proposal_contact_phone'] ) );
	}
}
add_action( 'save_post_caaguazu_proposal', 'caaguazu_proposal_save_meta' );

/**
 * Columnas de admin para triage rápido, mismo criterio que cpt-report.php.
 */
function caaguazu_proposal_admin_columns( $columns ) {
	$columns['caaguazu_proposal_type']    = __( 'Tipo', 'caaguazu' );
	$columns['caaguazu_proposal_contact'] = __( 'Contacto', 'caaguazu' );
	return $columns;
}
add_filter( 'manage_caaguazu_proposal_posts_columns', 'caaguazu_proposal_admin_columns' );

function caaguazu_proposal_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'caaguazu_proposal_type':
			$type  = get_post_meta( $post_id, '_caaguazu_proposal_type', true );
			$types = caaguazu_proposal_types();
			echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : $type );
			break;
		case 'caaguazu_proposal_contact':
			$name  = get_post_meta( $post_id, '_caaguazu_proposal_contact_name', true );
			$email = get_post_meta( $post_id, '_caaguazu_proposal_contact_email', true );
			echo esc_html( trim( $name . ( $email ? " ({$email})" : '' ) ) );
			break;
	}
}
add_action( 'manage_caaguazu_proposal_posts_custom_column', 'caaguazu_proposal_admin_column_content', 10, 2 );
