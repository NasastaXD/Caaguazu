<?php
/**
 * Módulo Educación — CPT `caaguazu_educacion` + taxonomía de tipo
 * (Escuelas / Becas / Programas / Estadísticas), demo seeder, y
 * auto-registro en nav/accesos rápidos del theme.
 *
 * Mismo patrón que module-noticias.php: el theme no sabe que este módulo
 * existe, se engancha solo vía los filtros `caaguazu_quick_access_items`/
 * `caaguazu_nav_items`.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_educacion_cpt() {
	register_post_type( 'caaguazu_educacion', array(
		'labels' => array(
			'name'               => __( 'Educación', 'caaguazu-modulos' ),
			'singular_name'      => __( 'Contenido educativo', 'caaguazu-modulos' ),
			'add_new'            => __( 'Añadir contenido', 'caaguazu-modulos' ),
			'add_new_item'       => __( 'Añadir contenido educativo', 'caaguazu-modulos' ),
			'edit_item'          => __( 'Editar contenido educativo', 'caaguazu-modulos' ),
			'new_item'           => __( 'Nuevo contenido educativo', 'caaguazu-modulos' ),
			'view_item'          => __( 'Ver contenido', 'caaguazu-modulos' ),
			'search_items'       => __( 'Buscar en Educación', 'caaguazu-modulos' ),
			'not_found'          => __( 'No se encontró contenido educativo.', 'caaguazu-modulos' ),
			'not_found_in_trash' => __( 'No hay contenido educativo en la papelera.', 'caaguazu-modulos' ),
			'menu_name'          => __( 'Educación', 'caaguazu-modulos' ),
		),
		'public'        => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-welcome-learn-more',
		'has_archive'   => 'educacion',
		'rewrite'       => array( 'slug' => 'educacion' ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
		'show_in_nav_menus' => true,
	) );

	register_taxonomy( 'caaguazu_edu_tipo', 'caaguazu_educacion', array(
		'labels' => array(
			'name'          => __( 'Tipo', 'caaguazu-modulos' ),
			'singular_name' => __( 'Tipo', 'caaguazu-modulos' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => array( 'slug' => 'educacion-tipo' ),
	) );
}
add_action( 'init', 'caaguazu_register_educacion_cpt' );

/**
 * Meta: dato destacado opcional (p. ej. "320 cupos", "94% cobertura"),
 * pensado para las entradas de tipo Beca/Estadística que suelen resumirse
 * en un número. Se muestra como badge en la tarjeta si está cargado.
 */
function caaguazu_register_educacion_meta() {
	register_post_meta( 'caaguazu_educacion', '_caaguazu_edu_stat', array(
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_educacion_meta' );

function caaguazu_educacion_metabox() {
	add_meta_box(
		'caaguazu_educacion_meta',
		__( 'Datos educativos', 'caaguazu-modulos' ),
		'caaguazu_educacion_metabox_html',
		'caaguazu_educacion',
		'side'
	);
}
add_action( 'add_meta_boxes', 'caaguazu_educacion_metabox' );

function caaguazu_educacion_metabox_html( $post ) {
	wp_nonce_field( 'caaguazu_educacion_meta', 'caaguazu_educacion_meta_nonce' );
	$stat = get_post_meta( $post->ID, '_caaguazu_edu_stat', true );
	?>
	<p>
		<label for="caaguazu_edu_stat"><strong><?php esc_html_e( 'Dato destacado', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="text" id="caaguazu_edu_stat" name="caaguazu_edu_stat" value="<?php echo esc_attr( $stat ); ?>" style="width:100%" placeholder="320 cupos">
	</p>
	<p style="color:#666;font-size:12px">
		<?php esc_html_e( 'Se muestra como dato destacado en la tarjeta. Dejar vacío para ocultar.', 'caaguazu-modulos' ); ?>
	</p>
	<?php
}

function caaguazu_educacion_save_meta( $post_id ) {
	if ( ! isset( $_POST['caaguazu_educacion_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( $_POST['caaguazu_educacion_meta_nonce'], 'caaguazu_educacion_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['caaguazu_edu_stat'] ) ) {
		$stat = sanitize_text_field( $_POST['caaguazu_edu_stat'] );
		if ( '' !== $stat ) {
			update_post_meta( $post_id, '_caaguazu_edu_stat', $stat );
		} else {
			delete_post_meta( $post_id, '_caaguazu_edu_stat' );
		}
	}
}
add_action( 'save_post_caaguazu_educacion', 'caaguazu_educacion_save_meta' );

/**
 * Devuelve el tipo (Escuelas/Becas/Programas/Estadísticas) como etiqueta
 * corta para la tarjeta, igual que caaguazu_news_primary_term().
 */
function caaguazu_educacion_primary_term( $post_id ) {
	$terms = get_the_terms( $post_id, 'caaguazu_edu_tipo' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return $terms[0]->name;
	}
	return '';
}

/**
 * Siembra 4 entradas demo (una por tipo) al activar el plugin, si no hay
 * ninguna todavía.
 */
function caaguazu_modulos_seed_educacion() {
	$existing = get_posts( array(
		'post_type'      => 'caaguazu_educacion',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$tipos    = array( 'Escuelas', 'Becas', 'Programas', 'Estadísticas' );
	$tipo_ids = array();
	foreach ( $tipos as $tipo_name ) {
		$term = term_exists( $tipo_name, 'caaguazu_edu_tipo' );
		if ( ! $term ) {
			$term = wp_insert_term( $tipo_name, 'caaguazu_edu_tipo' );
		}
		if ( ! is_wp_error( $term ) ) {
			$tipo_ids[ $tipo_name ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	$entradas = array(
		array(
			'tipo'     => 'Escuelas',
			'days_ago' => 5,
			'stat'     => '18 aulas nuevas',
			'title'    => 'Colegio Nacional de Caaguazú suma nuevo laboratorio de informática',
			'excerpt'  => 'El establecimiento incorporó 18 aulas equipadas con conectividad, gracias a un convenio entre la Gobernación y el Ministerio de Educación.',
			'content'  => '<p>El Colegio Nacional de Caaguazú habilitó un nuevo laboratorio de informática con 18 aulas equipadas, fruto de un convenio entre la Gobernación departamental y el Ministerio de Educación y Ciencias.</p><p>La inversión busca acercar herramientas digitales a estudiantes de nivel medio en instituciones públicas del departamento, priorizando establecimientos con mayor matrícula rural.</p>',
		),
		array(
			'tipo'     => 'Becas',
			'days_ago' => 12,
			'stat'     => '320 cupos',
			'title'    => 'Becas municipales 2026 abren inscripciones para estudiantes de la Ruta de la Madera',
			'excerpt'  => 'El programa ofrece 320 cupos para estudiantes secundarios y terciarios de familias de bajos ingresos en los distritos de la Ruta de la Madera.',
			'content'  => '<p>La Municipalidad de Caaguazú, junto a las intendencias de la Ruta de la Madera, lanzó la convocatoria 2026 de becas estudiantiles. El programa cubre matrícula y materiales para 320 estudiantes de nivel secundario y terciario.</p><p>Las inscripciones se reciben en las oficinas de Acción Social de cada municipio hasta agotar cupos.</p>',
		),
		array(
			'tipo'     => 'Programas',
			'days_ago' => 20,
			'stat'     => '12 comunidades',
			'title'    => 'Programa "Escuela Va al Campo" lleva educación agrícola a comunidades rurales',
			'excerpt'  => 'La iniciativa combina huertas escolares con clases de agricultura familiar en 12 comunidades del interior del departamento.',
			'content'  => '<p>"Escuela Va al Campo" es un programa conjunto entre la Secretaría de Desarrollo y escuelas rurales que combina huertas escolares con formación práctica en agricultura familiar.</p><p>Ya alcanza a 12 comunidades del interior de Caaguazú, con foco en seguridad alimentaria y arraigo rural.</p>',
		),
		array(
			'tipo'     => 'Estadísticas',
			'days_ago' => 28,
			'stat'     => '94% cobertura',
			'title'    => 'Caaguazú alcanza el 94% de cobertura educativa en nivel primario',
			'excerpt'  => 'El último relevamiento del Ministerio de Educación ubica al departamento entre los de mejor cobertura en nivel primario del país.',
			'content'  => '<p>Según el último relevamiento del Ministerio de Educación y Ciencias, Caaguazú alcanzó una cobertura del 94% en nivel primario, por encima del promedio nacional.</p><p>El dato se atribuye a la ampliación de infraestructura escolar rural de los últimos años y a los programas de becas y transporte estudiantil.</p>',
		),
	);

	foreach ( $entradas as $e ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'caaguazu_educacion',
			'post_status'  => 'publish',
			'post_title'   => $e['title'],
			'post_excerpt' => $e['excerpt'],
			'post_content' => $e['content'],
			'post_date'    => date( 'Y-m-d H:i:s', strtotime( '-' . $e['days_ago'] . ' days' ) ),
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_caaguazu_edu_stat', $e['stat'] );
		update_post_meta( $post_id, '_caaguazu_demo', 1 );

		if ( isset( $tipo_ids[ $e['tipo'] ] ) ) {
			wp_set_post_terms( $post_id, array( (int) $tipo_ids[ $e['tipo'] ] ), 'caaguazu_edu_tipo' );
		}
	}
}

/**
 * `register_activation_hook` solo corre al activar este plugin; un sitio
 * que ya lo tenga activo y reciba una actualización con este módulo nuevo
 * nunca dispara ese hook. Mismo catch-up que ya usa module-ecosistema.php.
 */
function caaguazu_modulos_catch_up_educacion() {
	if ( get_option( 'caaguazu_modulos_educacion_caught_up' ) ) {
		return;
	}
	caaguazu_modulos_seed_educacion();
	update_option( 'caaguazu_modulos_educacion_caught_up', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_catch_up_educacion' );

/**
 * Auto-registro en los accesos rápidos y el nav de fallback del theme.
 */
add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'book',
		'label' => __( 'Educación', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'caaguazu_educacion' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'educacion',
		'label' => __( 'Educación', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'caaguazu_educacion' ),
	);
	return $items;
} );
