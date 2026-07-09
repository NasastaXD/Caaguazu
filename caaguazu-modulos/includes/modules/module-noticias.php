<?php
/**
 * Módulo Noticias — categoría "Noticias" (con 5 sub-categorías) sobre las
 * Entradas nativas de WordPress, demo seeder, y auto-registro en
 * nav/accesos rápidos del theme.
 *
 * Hasta la 1.4.0 este módulo era un CPT propio (`caaguazu_news`) con su
 * propia taxonomía (`caaguazu_news_cat`). Pasó a ser Entradas nativas +
 * Categoría para que el contenido viva en el lugar de siempre de
 * WordPress (Entradas → Todas las entradas), editable con las
 * herramientas que cualquiera que ya usó WordPress reconoce, en vez de
 * una pantalla de administración aparte.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Categoría "Noticias" + sus 5 sub-categorías, creadas si no existen
 * todavía. La sub-categoría "Turismo" lleva un slug propio
 * ('noticias-turismo'): "turismo" ya es el slug de la página hub del
 * ecosistema Turismo, y dos cosas no pueden compartir slug en WordPress.
 * Memoizado por request: esto lo llaman varias funciones de este archivo.
 */
function caaguazu_noticias_ensure_categories() {
	static $cats = null;
	if ( null !== $cats ) {
		return $cats;
	}

	$parent_id = caaguazu_ensure_category( 'Noticias', 'noticias' );
	$children  = array(
		'Desarrollo' => '',
		'Cultura'    => '',
		'Gobierno'   => '',
		'Turismo'    => 'noticias-turismo',
		'Comunidad'  => '',
	);
	$child_ids = array();
	foreach ( $children as $name => $slug ) {
		$child_ids[ $name ] = caaguazu_ensure_category( $name, $slug, $parent_id );
	}

	$cats = array( 'parent' => $parent_id, 'children' => $child_ids );
	return $cats;
}
add_action( 'init', 'caaguazu_noticias_ensure_categories', 20 );

/**
 * Meta: minutos de lectura (mostrado en la tarjeta como "4 min").
 */
function caaguazu_register_news_meta() {
	register_post_meta( 'post', '_caaguazu_read_minutes', array(
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_news_meta' );

/**
 * Metabox simple para el campo de minutos. Aparece en toda Entrada (no
 * solo en las de la categoría Noticias — WordPress no ofrece una forma
 * simple de condicionar metaboxes a una categoría tildada en el mismo
 * formulario); queda vacío/sin usar en cualquier otra.
 */
function caaguazu_news_metabox() {
	add_meta_box(
		'caaguazu_news_meta',
		__( 'Noticias: minutos de lectura', 'caaguazu-modulos' ),
		'caaguazu_news_metabox_html',
		'post',
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
		<?php esc_html_e( 'Solo aplica si la entrada está en la categoría Noticias. Se muestra como "X min de lectura". Dejar vacío para ocultar.', 'caaguazu-modulos' ); ?>
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
add_action( 'save_post_post', 'caaguazu_news_save_meta' );

/**
 * Etiqueta corta para la tarjeta: la sub-categoría más específica que
 * tenga asignada (salteando la categoría "Noticias" genérica si hay una
 * más puntual), o "Noticias" si no tiene ninguna otra.
 */
function caaguazu_news_primary_term( $post_id ) {
	$terms = get_the_category( $post_id );
	foreach ( $terms as $term ) {
		if ( 'noticias' !== $term->slug ) {
			return $term->name;
		}
	}
	return $terms ? $terms[0]->name : '';
}

/**
 * Sitios que ya tenían este módulo activo antes de la 1.5.0 (CPT propio
 * `caaguazu_news` + taxonomía `caaguazu_news_cat`) tienen ese contenido
 * "atrapado" en un tipo de contenido que ya no se registra. Lo pasa a
 * Entrada + Categoría nativa, conservando ID/fecha/contenido/meta —
 * get_posts() sigue encontrando el post type viejo por el valor crudo de
 * la columna, esté registrado o no.
 */
function caaguazu_modulos_migrate_noticias_from_cpt() {
	if ( get_option( 'caaguazu_modulos_noticias_migrated' ) ) {
		return;
	}

	$old_posts = get_posts( array(
		'post_type'      => 'caaguazu_news',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	if ( $old_posts ) {
		$cats = caaguazu_noticias_ensure_categories();
		foreach ( $old_posts as $post_id ) {
			$old_terms = wp_get_object_terms( $post_id, 'caaguazu_news_cat' );
			wp_update_post( array( 'ID' => $post_id, 'post_type' => 'post' ) );

			$term_ids = array();
			if ( ! is_wp_error( $old_terms ) ) {
				foreach ( $old_terms as $old_term ) {
					if ( isset( $cats['children'][ $old_term->name ] ) ) {
						$term_ids[] = (int) $cats['children'][ $old_term->name ];
					}
				}
			}
			if ( ! $term_ids ) {
				$term_ids[] = $cats['parent'];
			}
			wp_set_post_terms( $post_id, $term_ids, 'category' );
		}
	}

	update_option( 'caaguazu_modulos_noticias_migrated', 1 );
}

/**
 * Siembra 5 noticias demo (si no hay ninguna todavía en la categoría
 * Noticias). Migra primero cualquier resto del CPT viejo.
 */
function caaguazu_modulos_seed_noticias() {
	caaguazu_modulos_migrate_noticias_from_cpt();

	$cats    = caaguazu_noticias_ensure_categories();
	$all_ids = array_merge( array( $cats['parent'] ), array_values( $cats['children'] ) );

	$existing = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'category__in'   => $all_ids,
	) );
	if ( ! empty( $existing ) ) {
		return;
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
			'post_type'    => 'post',
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

		if ( isset( $cats['children'][ $n['cat'] ] ) ) {
			wp_set_post_terms( $post_id, array( (int) $cats['children'][ $n['cat'] ] ), 'category' );
		}
	}
}

/**
 * `register_activation_hook` solo corre al activar este plugin — un
 * sitio que ya lo tenga activo (el caso normal: esto es una
 * actualización, no una instalación nueva) nunca lo dispara. Catch-up en
 * `admin_init` para que la migración desde el CPT viejo corra sola en la
 * próxima visita a wp-admin.
 */
function caaguazu_modulos_catch_up_noticias() {
	if ( get_option( 'caaguazu_modulos_noticias_caught_up' ) ) {
		return;
	}
	caaguazu_modulos_seed_noticias();
	update_option( 'caaguazu_modulos_noticias_caught_up', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_catch_up_noticias' );

/**
 * Auto-registro en los accesos rápidos y el nav de fallback del theme.
 * Si el theme activo no define estos filtros, simplemente no se llaman
 * (add_filter no falla por eso) — el módulo sigue funcionando igual.
 */
add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'news',
		'label' => __( 'Noticias', 'caaguazu-modulos' ),
		'url'   => caaguazu_category_url( 'noticias' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'noticias',
		'label' => __( 'Noticias', 'caaguazu-modulos' ),
		'url'   => caaguazu_category_url( 'noticias' ),
	);
	return $items;
} );
