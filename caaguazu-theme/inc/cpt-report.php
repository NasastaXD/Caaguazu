<?php
/**
 * Custom Post Type: Reportes ciudadanos ("Reportá un problema").
 *
 * Gestión interna (bache, alumbrado, basura, etc.), no contenido editorial:
 * sin archive, sin REST, no público en el front-end.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_report_categories() {
	return array(
		'bache'      => __( 'Bache', 'caaguazu' ),
		'alumbrado'  => __( 'Alumbrado público', 'caaguazu' ),
		'basura'     => __( 'Basura / residuos', 'caaguazu' ),
		'agua'       => __( 'Agua / desagüe', 'caaguazu' ),
		'arbolado'   => __( 'Arbolado', 'caaguazu' ),
		'seguridad'  => __( 'Seguridad', 'caaguazu' ),
		'otro'       => __( 'Otro', 'caaguazu' ),
	);
}

function caaguazu_register_report_cpt() {
	register_post_type( 'caaguazu_report', array(
		'labels' => array(
			'name'               => __( 'Reportes', 'caaguazu' ),
			'singular_name'      => __( 'Reporte', 'caaguazu' ),
			'add_new'            => __( 'Añadir reporte', 'caaguazu' ),
			'add_new_item'       => __( 'Añadir nuevo reporte', 'caaguazu' ),
			'edit_item'          => __( 'Editar reporte', 'caaguazu' ),
			'view_item'          => __( 'Ver reporte', 'caaguazu' ),
			'search_items'       => __( 'Buscar reportes', 'caaguazu' ),
			'not_found'          => __( 'No se encontraron reportes.', 'caaguazu' ),
			'not_found_in_trash' => __( 'No hay reportes en la papelera.', 'caaguazu' ),
			'menu_name'          => __( 'Reportes', 'caaguazu' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-warning',
		'has_archive'         => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor' ),
	) );
}
add_action( 'init', 'caaguazu_register_report_cpt' );

function caaguazu_register_report_meta() {
	$fields = array(
		'_caaguazu_report_category'      => 'string',
		'_caaguazu_report_location'      => 'string',
		'_caaguazu_report_contact_name'  => 'string',
		'_caaguazu_report_contact_email' => 'string',
		'_caaguazu_report_contact_phone' => 'string',
	);
	foreach ( $fields as $key => $type ) {
		register_post_meta( 'caaguazu_report', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
}
add_action( 'init', 'caaguazu_register_report_meta' );

function caaguazu_report_metabox() {
	add_meta_box(
		'caaguazu_report_meta',
		__( 'Datos del reporte', 'caaguazu' ),
		'caaguazu_report_metabox_html',
		'caaguazu_report',
		'side'
	);
}
add_action( 'add_meta_boxes', 'caaguazu_report_metabox' );

function caaguazu_report_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_report_meta', 'caaguazu_report_meta_nonce' );
	$cat   = get_post_meta( $post->ID, '_caaguazu_report_category', true );
	$loc   = get_post_meta( $post->ID, '_caaguazu_report_location', true );
	$name  = get_post_meta( $post->ID, '_caaguazu_report_contact_name', true );
	$email = get_post_meta( $post->ID, '_caaguazu_report_contact_email', true );
	$phone = get_post_meta( $post->ID, '_caaguazu_report_contact_phone', true );
	?>
	<p>
		<label for="caaguazu_report_category"><strong><?php esc_html_e( 'Categoría', 'caaguazu' ); ?></strong></label><br>
		<select id="caaguazu_report_category" name="caaguazu_report_category" style="width:100%">
			<?php foreach ( caaguazu_report_categories() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $cat, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="caaguazu_report_location"><strong><?php esc_html_e( 'Ubicación', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_report_location" name="caaguazu_report_location" value="<?php echo esc_attr( $loc ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_report_contact_name"><strong><?php esc_html_e( 'Nombre de contacto', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_report_contact_name" name="caaguazu_report_contact_name" value="<?php echo esc_attr( $name ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_report_contact_email"><strong><?php esc_html_e( 'Email de contacto', 'caaguazu' ); ?></strong></label><br>
		<input type="email" id="caaguazu_report_contact_email" name="caaguazu_report_contact_email" value="<?php echo esc_attr( $email ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_report_contact_phone"><strong><?php esc_html_e( 'Teléfono de contacto', 'caaguazu' ); ?></strong></label><br>
		<input type="text" id="caaguazu_report_contact_phone" name="caaguazu_report_contact_phone" value="<?php echo esc_attr( $phone ); ?>" style="width:100%">
	</p>
	<?php
}

function caaguazu_report_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_report_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_report_meta_nonce'], 'caaguazu_report_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_report_category'] ) ) {
		$cat = sanitize_key( $_POST['caaguazu_report_category'] );
		if ( array_key_exists( $cat, caaguazu_report_categories() ) ) {
			update_post_meta( $post_id, '_caaguazu_report_category', $cat );
		}
	}
	if ( isset( $_POST['caaguazu_report_location'] ) ) {
		update_post_meta( $post_id, '_caaguazu_report_location', sanitize_text_field( $_POST['caaguazu_report_location'] ) );
	}
	if ( isset( $_POST['caaguazu_report_contact_name'] ) ) {
		update_post_meta( $post_id, '_caaguazu_report_contact_name', sanitize_text_field( $_POST['caaguazu_report_contact_name'] ) );
	}
	if ( isset( $_POST['caaguazu_report_contact_email'] ) ) {
		update_post_meta( $post_id, '_caaguazu_report_contact_email', sanitize_email( $_POST['caaguazu_report_contact_email'] ) );
	}
	if ( isset( $_POST['caaguazu_report_contact_phone'] ) ) {
		update_post_meta( $post_id, '_caaguazu_report_contact_phone', sanitize_text_field( $_POST['caaguazu_report_contact_phone'] ) );
	}
}
add_action( 'save_post_caaguazu_report', 'caaguazu_report_save_meta' );

/**
 * Columnas de admin para triage rápido sin abrir cada reporte.
 */
function caaguazu_report_admin_columns( $columns ) {
	$columns['caaguazu_report_category'] = __( 'Categoría', 'caaguazu' );
	$columns['caaguazu_report_location'] = __( 'Ubicación', 'caaguazu' );
	$columns['caaguazu_report_contact']  = __( 'Contacto', 'caaguazu' );
	return $columns;
}
add_filter( 'manage_caaguazu_report_posts_columns', 'caaguazu_report_admin_columns' );

function caaguazu_report_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'caaguazu_report_category':
			$cat = get_post_meta( $post_id, '_caaguazu_report_category', true );
			$cats = caaguazu_report_categories();
			echo esc_html( isset( $cats[ $cat ] ) ? $cats[ $cat ] : $cat );
			break;
		case 'caaguazu_report_location':
			echo esc_html( get_post_meta( $post_id, '_caaguazu_report_location', true ) );
			break;
		case 'caaguazu_report_contact':
			$name  = get_post_meta( $post_id, '_caaguazu_report_contact_name', true );
			$email = get_post_meta( $post_id, '_caaguazu_report_contact_email', true );
			echo esc_html( trim( $name . ( $email ? " ({$email})" : '' ) ) );
			break;
	}
}
add_action( 'manage_caaguazu_report_posts_custom_column', 'caaguazu_report_admin_column_content', 10, 2 );

/**
 * Conteos públicos para mostrar que el canal de reportes funciona de
 * verdad, sin exponer el contenido de los reportes (el CPT sigue sin ser
 * público). "Resueltos" = post_status publish (el admin lo marca así al
 * atenderlo); "Recibidos este mes" = post_date dentro del mes calendario.
 */
function caaguazu_report_stats() {
	$received = wp_count_posts( 'caaguazu_report' );

	$this_month = get_posts( array(
		'post_type'      => 'caaguazu_report',
		'post_status'    => array( 'pending', 'publish' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'date_query'     => array( array( 'year' => (int) current_time( 'Y' ), 'month' => (int) current_time( 'n' ) ) ),
	) );

	return array(
		'received' => (int) $received->pending + (int) $received->publish,
		'resolved' => (int) $received->publish,
		'this_month' => count( $this_month ),
	);
}
