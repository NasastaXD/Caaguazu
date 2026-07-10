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

/**
 * V5 (civic CMS): "Tipo" y "Área" del evento — taxonomías propias sobre
 * post_type=post, además de (no en reemplazo de) la categoría Agenda. Un
 * evento sigue viviendo como Entrada+Categoría (no se migra a un CPT propio:
 * ver la nota en caaguazu-theme/README.md sobre por qué Noticias/Agenda/
 * Educación quedan así), esto sólo suma clasificación cruzada más fina.
 */
function caaguazu_register_evento_taxonomies() {
	register_taxonomy( 'tipo_evento', 'post', array(
		'labels' => array(
			'name'          => __( 'Tipos de evento', 'caaguazu-modulos' ),
			'singular_name' => __( 'Tipo de evento', 'caaguazu-modulos' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'tipo-evento' ),
	) );
	register_taxonomy( 'area_evento', 'post', array(
		'labels' => array(
			'name'          => __( 'Áreas de evento', 'caaguazu-modulos' ),
			'singular_name' => __( 'Área de evento', 'caaguazu-modulos' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'area-evento' ),
	) );
}
add_action( 'init', 'caaguazu_register_evento_taxonomies', 0 );

function caaguazu_evento_ensure_terms() {
	caaguazu_ensure_terms( 'tipo_evento', array(
		__( 'Cultural', 'caaguazu-modulos' ),
		__( 'Educativo', 'caaguazu-modulos' ),
		__( 'Comunitario', 'caaguazu-modulos' ),
		__( 'Deportivo', 'caaguazu-modulos' ),
		__( 'Municipal', 'caaguazu-modulos' ),
		__( 'Juvenil', 'caaguazu-modulos' ),
	) );
	caaguazu_ensure_terms( 'area_evento', array(
		__( 'Cultura', 'caaguazu-modulos' ),
		__( 'Educación', 'caaguazu-modulos' ),
		__( 'Comunidad', 'caaguazu-modulos' ),
		__( 'Turismo', 'caaguazu-modulos' ),
		__( 'Juventud', 'caaguazu-modulos' ),
		__( 'Servicios', 'caaguazu-modulos' ),
	) );
}
add_action( 'init', 'caaguazu_evento_ensure_terms', 20 );

/**
 * Estado del evento: set cerrado de 4 valores, mismo criterio que
 * caaguazu_servicio_estado_values() en module-servicios.php.
 */
function caaguazu_evento_estado_values() {
	return array(
		'proximo'       => __( 'Próximo', 'caaguazu-modulos' ),
		'realizado'     => __( 'Realizado', 'caaguazu-modulos' ),
		'cancelado'     => __( 'Cancelado', 'caaguazu-modulos' ),
		'reprogramado'  => __( 'Reprogramado', 'caaguazu-modulos' ),
	);
}

function caaguazu_sanitize_evento_estado( $value ) {
	return array_key_exists( $value, caaguazu_evento_estado_values() ) ? $value : '';
}

function caaguazu_register_event_meta() {
	$text_args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	foreach ( array(
		'_caaguazu_event_date',
		'_caaguazu_event_location',
		'_caaguazu_event_hora_inicio',
		'_caaguazu_event_hora_fin',
		'_caaguazu_event_organizador',
		'_caaguazu_event_contacto',
		'_caaguazu_event_enlace',
	) as $key ) {
		register_post_meta( 'post', $key, $text_args );
	}
	register_post_meta( 'post', '_caaguazu_event_estado', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'caaguazu_sanitize_evento_estado',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_event_meta' );

/**
 * Metabox de fecha/lugar/horario/organizador/estado. Aparece en toda
 * Entrada, igual que el de module-noticias.php — ver comentario ahí sobre
 * por qué no se puede condicionar a la categoría tildada en el mismo
 * formulario.
 */
function caaguazu_event_metabox() {
	add_meta_box( 'caaguazu_event_meta', __( 'Agenda: datos del evento', 'caaguazu-modulos' ), 'caaguazu_event_metabox_html', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'caaguazu_event_metabox' );

function caaguazu_event_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_event_meta', 'caaguazu_event_meta_nonce' );
	$date         = get_post_meta( $post->ID, '_caaguazu_event_date', true );
	$location     = get_post_meta( $post->ID, '_caaguazu_event_location', true );
	$hora_inicio  = get_post_meta( $post->ID, '_caaguazu_event_hora_inicio', true );
	$hora_fin     = get_post_meta( $post->ID, '_caaguazu_event_hora_fin', true );
	$organizador  = get_post_meta( $post->ID, '_caaguazu_event_organizador', true );
	$contacto     = get_post_meta( $post->ID, '_caaguazu_event_contacto', true );
	$enlace       = get_post_meta( $post->ID, '_caaguazu_event_enlace', true );
	$estado       = get_post_meta( $post->ID, '_caaguazu_event_estado', true );
	?>
	<p>
		<label for="caaguazu_event_date"><strong><?php esc_html_e( 'Fecha del evento', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="date" id="caaguazu_event_date" name="caaguazu_event_date" value="<?php echo esc_attr( $date ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_hora_inicio"><strong><?php esc_html_e( 'Hora de inicio', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="time" id="caaguazu_event_hora_inicio" name="caaguazu_event_hora_inicio" value="<?php echo esc_attr( $hora_inicio ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_hora_fin"><strong><?php esc_html_e( 'Hora de fin', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="time" id="caaguazu_event_hora_fin" name="caaguazu_event_hora_fin" value="<?php echo esc_attr( $hora_fin ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_location"><strong><?php esc_html_e( 'Lugar', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_event_location" name="caaguazu_event_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_organizador"><strong><?php esc_html_e( 'Organizador', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_event_organizador" name="caaguazu_event_organizador" value="<?php echo esc_attr( $organizador ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_contacto"><strong><?php esc_html_e( 'Contacto', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_event_contacto" name="caaguazu_event_contacto" value="<?php echo esc_attr( $contacto ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_enlace"><strong><?php esc_html_e( 'Enlace', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="url" id="caaguazu_event_enlace" name="caaguazu_event_enlace" value="<?php echo esc_attr( $enlace ); ?>" style="width:100%">
	</p>
	<p>
		<label for="caaguazu_event_estado"><strong><?php esc_html_e( 'Estado', 'caaguazu-modulos' ); ?></strong></label><br>
		<select id="caaguazu_event_estado" name="caaguazu_event_estado" style="width:100%">
			<option value=""><?php esc_html_e( 'Sin definir', 'caaguazu-modulos' ); ?></option>
			<?php foreach ( caaguazu_evento_estado_values() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $estado, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p style="color:#666;font-size:12px">
		<?php esc_html_e( 'Estos campos sólo aplican si la entrada está en la categoría Agenda.', 'caaguazu-modulos' ); ?>
	</p>
	<?php
}

function caaguazu_event_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_event_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_event_meta_nonce'], 'caaguazu_event_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$fields = array(
		'caaguazu_event_date'        => '_caaguazu_event_date',
		'caaguazu_event_location'    => '_caaguazu_event_location',
		'caaguazu_event_hora_inicio' => '_caaguazu_event_hora_inicio',
		'caaguazu_event_hora_fin'    => '_caaguazu_event_hora_fin',
		'caaguazu_event_organizador' => '_caaguazu_event_organizador',
		'caaguazu_event_contacto'    => '_caaguazu_event_contacto',
	);
	foreach ( $fields as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
		}
	}
	if ( isset( $_POST['caaguazu_event_enlace'] ) ) {
		update_post_meta( $post_id, '_caaguazu_event_enlace', esc_url_raw( $_POST['caaguazu_event_enlace'] ) );
	}
	if ( isset( $_POST['caaguazu_event_estado'] ) ) {
		update_post_meta( $post_id, '_caaguazu_event_estado', caaguazu_sanitize_evento_estado( $_POST['caaguazu_event_estado'] ) );
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
 * Antes sembraba 4 eventos demo (redacción de relleno, no verificada por
 * nadie del departamento) la primera vez que la categoría Agenda quedaba
 * vacía. Se saca a propósito: un portal cívico no debe mostrar contenido
 * que parece real pero no lo es — mejor un estado vacío honesto hasta que
 * haya eventos reales cargados por quien administra el sitio. Sigue
 * asegurando la categoría y migrando el CPT viejo si corresponde; ver
 * `caaguazu_modulos_trash_legacy_demo_content()` en caaguazu-modulos.php
 * para la limpieza de sitios que ya tenían los 4 eventos demo publicados.
 */
function caaguazu_modulos_seed_agenda() {
	caaguazu_modulos_migrate_agenda_from_cpt();
	caaguazu_agenda_ensure_category();
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
