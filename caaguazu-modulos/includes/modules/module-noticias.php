<?php
/**
 * Módulo Noticias — CPT `caaguazu_news` + taxonomía de categorías, demo
 * seeder, y auto-registro en nav/accesos rápidos del theme.
 *
 * Migrado desde caaguazu-theme/inc/cpt-news.php + inc/demo-content.php:
 * el theme mantiene sus templates (archive-caaguazu_news.php,
 * single-caaguazu_news.php) que siguen funcionando igual, ya que llaman a
 * estas mismas funciones por nombre.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_register_news_cpt() {
	register_post_type( 'caaguazu_news', array(
		'labels' => array(
			'name'               => __( 'Noticias', 'caaguazu-modulos' ),
			'singular_name'      => __( 'Noticia', 'caaguazu-modulos' ),
			'add_new'            => __( 'Añadir noticia', 'caaguazu-modulos' ),
			'add_new_item'       => __( 'Añadir nueva noticia', 'caaguazu-modulos' ),
			'edit_item'          => __( 'Editar noticia', 'caaguazu-modulos' ),
			'new_item'           => __( 'Nueva noticia', 'caaguazu-modulos' ),
			'view_item'          => __( 'Ver noticia', 'caaguazu-modulos' ),
			'search_items'       => __( 'Buscar noticias', 'caaguazu-modulos' ),
			'not_found'          => __( 'No se encontraron noticias.', 'caaguazu-modulos' ),
			'not_found_in_trash' => __( 'No hay noticias en la papelera.', 'caaguazu-modulos' ),
			'menu_name'          => __( 'Noticias', 'caaguazu-modulos' ),
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
			'name'          => __( 'Categorías de noticia', 'caaguazu-modulos' ),
			'singular_name' => __( 'Categoría', 'caaguazu-modulos' ),
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
		__( 'Datos de la noticia', 'caaguazu-modulos' ),
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
		<label for="caaguazu_read_minutes"><strong><?php esc_html_e( 'Minutos de lectura', 'caaguazu-modulos' ); ?></strong></label><br>
		<input type="number" id="caaguazu_read_minutes" name="caaguazu_read_minutes" value="<?php echo esc_attr( $mins ); ?>" min="1" max="60" style="width:80px">
	</p>
	<p style="color:#666;font-size:12px">
		<?php esc_html_e( 'Se muestra en las tarjetas como "X min de lectura". Dejar vacío para ocultar.', 'caaguazu-modulos' ); ?>
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

/**
 * Siembra 5 noticias demo al activar el plugin (si no hay ninguna todavía).
 */
function caaguazu_modulos_seed_noticias() {
	$existing = get_posts( array(
		'post_type'      => 'caaguazu_news',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$categories = array( 'Desarrollo', 'Cultura', 'Gobierno', 'Turismo', 'Comunidad' );
	$cat_ids    = array();
	foreach ( $categories as $cat_name ) {
		$term = term_exists( $cat_name, 'caaguazu_news_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $cat_name, 'caaguazu_news_cat' );
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $cat_name ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	$news = array(
		array(
			'cat'      => 'Desarrollo',
			'days_ago' => 3,
			'minutes'  => 4,
			'title'    => 'Caaguazú lanza programa de reforestación con escuelas rurales',
			'excerpt'  => 'La iniciativa involucra a más de 40 instituciones educativas en la plantación de especies nativas y eucalipto de ciclo corto.',
			'content'  => '<p>La Secretaría de Desarrollo del departamento presentó un programa de reforestación que involucra a escuelas rurales de todo Caaguazú. Durante los próximos seis meses, estudiantes y docentes participarán de jornadas de plantación en terrenos linderos a los establecimientos educativos.</p><p>El proyecto busca recuperar superficie forestal nativa y, al mismo tiempo, dar continuidad al ciclo productivo del eucalipto que sostiene buena parte de la industria maderera local.</p>',
		),
		array(
			'cat'      => 'Cultura',
			'days_ago' => 9,
			'minutes'  => 3,
			'title'    => 'Festival de la Madera celebra su 15ª edición',
			'excerpt'  => 'Tres días de exposiciones, talleres de carpintería tradicional y gastronomía local en el centro de la ciudad.',
			'content'  => '<p>Con la participación de carpinteros, talladores y artesanos de toda la Ruta de la Madera, el Festival de la Madera llega a su 15ª edición. La agenda incluye demostraciones en vivo, una feria de productos terminados y espacios gastronómicos con platos típicos.</p><p>La actividad, organizada junto a la Asociación de Madereros, se realiza en la plaza central y es de entrada libre.</p>',
		),
		array(
			'cat'      => 'Gobierno',
			'days_ago' => 16,
			'minutes'  => 5,
			'title'    => 'Nuevas plataformas digitales simplifican trámites departamentales',
			'excerpt'  => 'Más de 30 gestiones ya pueden iniciarse en línea desde el portal de servicios, reduciendo tiempos de espera presencial.',
			'content'  => '<p>El departamento avanza en la digitalización de sus trámites más solicitados. Certificados, habilitaciones y consultas que antes requerían presencia física ahora pueden iniciarse desde el portal de Servicios.</p><p>La mesa de entrada seguirá disponible para quienes prefieran hacer el trámite en persona.</p>',
		),
		array(
			'cat'      => 'Turismo',
			'days_ago' => 23,
			'minutes'  => 4,
			'title'    => 'Ykua La Patria suma señalética histórica renovada',
			'excerpt'  => 'El parque fundacional de Caaguazú estrena cartelería con la historia del manantial y su rol durante la Guerra de la Triple Alianza.',
			'content'  => '<p>El sitio donde nació Caaguazú en 1845 renovó su señalética para visitantes. Los nuevos carteles narran la historia del manantial, su vínculo con la fundación de la ciudad y el episodio de la Guerra de la Triple Alianza.</p><p>La actualización es parte de un plan más amplio para poner en valor los atractivos turísticos del departamento.</p>',
		),
		array(
			'cat'      => 'Comunidad',
			'days_ago' => 30,
			'minutes'  => 3,
			'title'    => 'Mercado de Abasto amplía su horario los fines de semana',
			'excerpt'  => 'Productores locales pidieron más horas de venta los sábados y domingos; el municipio respondió ampliando el horario habitual.',
			'content'  => '<p>A pedido de los feriantes del Mercado de Abasto, el municipio extendió el horario de atención los fines de semana. La medida busca dar más previsibilidad a los productores que llegan desde zonas rurales del departamento.</p><p>El mercado sigue siendo uno de los puntos de encuentro más concurridos del centro de Caaguazú.</p>',
		),
	);

	foreach ( $news as $n ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'caaguazu_news',
			'post_status'  => 'publish',
			'post_title'   => $n['title'],
			'post_excerpt' => $n['excerpt'],
			'post_content' => $n['content'],
			'post_date'    => date( 'Y-m-d H:i:s', strtotime( '-' . $n['days_ago'] . ' days' ) ),
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_caaguazu_read_minutes', $n['minutes'] );
		update_post_meta( $post_id, '_caaguazu_demo', 1 );

		if ( isset( $cat_ids[ $n['cat'] ] ) ) {
			wp_set_post_terms( $post_id, array( (int) $cat_ids[ $n['cat'] ] ), 'caaguazu_news_cat' );
		}
	}
}

/**
 * Auto-registro en los accesos rápidos y el nav de fallback del theme.
 * Si el theme activo no define estos filtros, simplemente no se llaman
 * (add_filter no falla por eso) — el módulo sigue funcionando igual.
 */
add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => '📰',
		'label' => __( 'Noticias', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'caaguazu_news' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'noticias',
		'label' => __( 'Noticias', 'caaguazu-modulos' ),
		'url'   => get_post_type_archive_link( 'caaguazu_news' ),
	);
	return $items;
} );
