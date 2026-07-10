<?php
/**
 * Módulo Educación — categoría "Educación" + 4 sub-categorías (Escuelas/
 * Becas/Programas/Estadísticas) sobre las Entradas nativas de WordPress,
 * demo seeder, y auto-registro en nav/accesos rápidos del theme + su
 * propio "ecosistema" (header/tabbar/paleta) en el shell genérico.
 *
 * Hasta la 1.4.0 este módulo era un CPT propio (`caaguazu_educacion`) con
 * su propia taxonomía (`caaguazu_edu_tipo`). Pasó a ser Entradas nativas +
 * Categoría — mismo motivo que module-noticias.php. El ecosistema propio
 * se mantiene: ahora se detecta por categoría en vez de por tipo de
 * contenido, pero el header/tabbar/paleta de Educación siguen igual.
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Categoría "Educación" + sus 4 sub-categorías, creadas si no existen
 * todavía. Memoizado por request: esto lo llaman varias funciones de este
 * archivo (seeder, migración, ítems del shell, prefijos de URL).
 */
function caaguazu_educacion_ensure_categories() {
	static $cats = null;
	if ( null !== $cats ) {
		return $cats;
	}

	$parent_id = caaguazu_ensure_category( 'Educación', 'educacion' );
	$children  = array(
		'Escuelas'     => '',
		'Becas'        => '',
		'Programas'    => '',
		'Estadísticas' => '',
	);
	$child_ids = array();
	foreach ( $children as $name => $slug ) {
		$child_ids[ $name ] = caaguazu_ensure_category( $name, $slug, $parent_id );
	}

	$cats = array( 'parent' => $parent_id, 'children' => $child_ids );
	return $cats;
}
add_action( 'init', 'caaguazu_educacion_ensure_categories', 20 );

/**
 * Meta: dato destacado opcional (p. ej. "320 cupos", "94% cobertura"),
 * pensado para las entradas de tipo Beca/Estadística que suelen resumirse
 * en un número. Se muestra como badge en la tarjeta si está cargado.
 */
function caaguazu_register_educacion_meta() {
	register_post_meta( 'post', '_caaguazu_edu_stat', array(
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}
add_action( 'init', 'caaguazu_register_educacion_meta' );

/**
 * Metabox del dato destacado. Aparece en toda Entrada, igual que el de
 * module-noticias.php — ver comentario ahí sobre por qué no se puede
 * condicionar a la categoría tildada en el mismo formulario.
 */
function caaguazu_educacion_metabox() {
	add_meta_box(
		'caaguazu_educacion_meta',
		__( 'Educación: dato destacado', 'caaguazu-modulos' ),
		'caaguazu_educacion_metabox_html',
		'post',
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
		<?php esc_html_e( 'Solo aplica si la entrada está en la categoría Educación. Se muestra como dato destacado en la tarjeta. Dejar vacío para ocultar.', 'caaguazu-modulos' ); ?>
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
add_action( 'save_post_post', 'caaguazu_educacion_save_meta' );

/**
 * Devuelve el tipo (Escuelas/Becas/Programas/Estadísticas) como etiqueta
 * corta para la tarjeta, igual que caaguazu_news_primary_term().
 */
function caaguazu_educacion_primary_term( $post_id ) {
	$terms = get_the_category( $post_id );
	foreach ( $terms as $term ) {
		if ( 'educacion' !== $term->slug ) {
			return $term->name;
		}
	}
	return $terms ? $terms[0]->name : '';
}

/**
 * Sitios que ya tenían este módulo activo antes de la 1.5.0 (CPT propio
 * `caaguazu_educacion` + taxonomía `caaguazu_edu_tipo`) tienen ese
 * contenido atrapado en un tipo de contenido que ya no se registra. Lo
 * pasa a Entrada + Categoría nativa.
 */
function caaguazu_modulos_migrate_educacion_from_cpt() {
	if ( get_option( 'caaguazu_modulos_educacion_migrated' ) ) {
		return;
	}

	$old_posts = get_posts( array(
		'post_type'      => 'caaguazu_educacion',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	if ( $old_posts ) {
		$cats = caaguazu_educacion_ensure_categories();
		foreach ( $old_posts as $post_id ) {
			$old_terms = wp_get_object_terms( $post_id, 'caaguazu_edu_tipo' );
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

	update_option( 'caaguazu_modulos_educacion_migrated', 1 );
}

/**
 * Antes sembraba 4 entradas demo (redacción de relleno, no verificada por
 * nadie del departamento) la primera vez que la categoría Educación
 * quedaba vacía. Se saca a propósito: un portal cívico no debe mostrar
 * contenido que parece real pero no lo es — mejor un estado vacío
 * honesto hasta que haya entradas reales cargadas por quien administra
 * el sitio. Sigue asegurando las categorías y migrando el CPT viejo si
 * corresponde; ver `caaguazu_modulos_trash_legacy_demo_content()` en
 * caaguazu-modulos.php para la limpieza de sitios que ya tenían las 4
 * entradas demo publicadas.
 */
function caaguazu_modulos_seed_educacion() {
	caaguazu_modulos_migrate_educacion_from_cpt();
	caaguazu_educacion_ensure_categories();
}

/**
 * Ecosistema Educación — registro en el shell genérico del theme
 * (`caaguazu_ecosystems`, inc/ecosystem-shell.php): mientras se navega
 * dentro de Educación (archivo de la categoría o de sus sub-categorías,
 * singles en cualquiera de ellas), el theme reemplaza el chrome
 * institucional por el header/tabbar propios del eco, con la paleta
 * tinta/pizarra (body.eco-educacion en main.css). Antes esto se detectaba
 * por tipo de contenido; ahora por categoría — mismo comportamiento visual,
 * distinta fuente de verdad. Si el theme activo no expone el filtro, nada
 * de esto corre y el módulo sigue funcionando como contenido común.
 */

function caaguazu_educacion_is_context() {
	if ( is_singular( 'post' ) ) {
		return 'educacion' === caaguazu_post_category_family( get_queried_object_id() );
	}
	if ( is_category() ) {
		return 'educacion' === caaguazu_category_family( get_queried_object() );
	}
	return false;
}

function caaguazu_educacion_hub_url() {
	return caaguazu_category_url( 'educacion' );
}

function caaguazu_educacion_url_prefixes() {
	$cats     = caaguazu_educacion_ensure_categories();
	$prefixes = array();
	foreach ( array_merge( array( $cats['parent'] ), array_values( $cats['children'] ) ) as $cat_id ) {
		$term = get_category( $cat_id );
		if ( ! $term ) {
			continue;
		}
		$link = get_category_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$prefixes[] = $link;
		}
	}
	return array_values( array_unique( $prefixes ) );
}

/**
 * Sección activa a resaltar en el nav/tabbar del shell: la categoría
 * actual (en un archivo) o la categoría de Educación más específica de la
 * entrada (en un single).
 */
function caaguazu_educacion_active_slug() {
	if ( is_category() && 'educacion' === caaguazu_category_family( get_queried_object() ) ) {
		return get_queried_object()->slug;
	}
	if ( is_singular( 'post' ) ) {
		foreach ( get_the_category( get_queried_object_id() ) as $term ) {
			if ( 'educacion' === caaguazu_category_family( $term ) ) {
				return $term->slug;
			}
		}
	}
	return '';
}

/**
 * Secciones del shell: un ítem por sub-categoría (Escuelas/Becas/
 * Programas/Estadísticas), con link al archivo de cada una. Expuesto vía
 * su propio filtro (mismo patrón que `caaguazu_tourism_shell_items`) por
 * si otro plugin quiere sumar secciones al eco.
 */
function caaguazu_educacion_shell_items() {
	$icons = array(
		'escuelas'     => 'home',
		'becas'        => 'celebration',
		'programas'    => 'target',
		'estadisticas' => 'chart',
	);
	$cats  = caaguazu_educacion_ensure_categories();
	$items = array();
	foreach ( $cats['children'] as $cat_id ) {
		$term = get_category( $cat_id );
		if ( ! $term ) {
			continue;
		}
		$link = get_category_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$items[] = array(
			'slug'  => $term->slug,
			'label' => $term->name,
			'short' => $term->name,
			'icon'  => isset( $icons[ $term->slug ] ) ? $icons[ $term->slug ] : 'book',
			'url'   => $link,
		);
	}
	return apply_filters( 'caaguazu_educacion_shell_items', $items );
}

/**
 * Imagen de la tarjeta de Educación en el hub Ecosistema — editable desde
 * Personalizar → Contenido del Home → Educación (imagen), en vez de quedar
 * fija en el código. Default: foto real de la UTIC (Universidad Tecnológica
 * Intercontinental), provista por quien encargó el sitio como reemplazo
 * provisorio de un placeholder de stock — bundleada en el plugin (en vez de
 * hotlinkeada a un servicio externo) para no repetir el 404 que tenía la
 * imagen de Unsplash anterior. Pendiente: reemplazar por una foto del CEIC
 * (el colegio más grande de la ciudad) cuando esté disponible.
 */
function caaguazu_educacion_card_image() {
	return caaguazu_opt_image( 'educacion_card_image', CAAGUAZU_MODULOS_URI . 'assets/images/utic-educacion.jpg' );
}

add_action( 'customize_register', function ( $wp_customize ) {
	if ( ! function_exists( 'caaguazu_add_image' ) ) {
		return; // el theme activo no expone los helpers genéricos del Customizer.
	}
	$wp_customize->add_section( 'caaguazu_educacion_images', array(
		'title' => __( 'Educación (imagen)', 'caaguazu-modulos' ),
		'panel' => 'caaguazu_home',
	) );
	caaguazu_add_image( $wp_customize, 'educacion_card_image', __( 'Tarjeta de Educación en Ecosistema', 'caaguazu-modulos' ), caaguazu_educacion_card_image(), 'caaguazu_educacion_images' );
} );

add_filter( 'caaguazu_ecosystems', function ( $ecos ) {
	$ecos['educacion'] = array(
		'label'        => __( 'Educación', 'caaguazu-modulos' ),
		'home_icon'    => 'book',
		'hub_url'      => 'caaguazu_educacion_hub_url',
		'is_context'   => 'caaguazu_educacion_is_context',
		'is_hub'       => '__return_false', // sin hero a sangre: header siempre sólido
		'active_slug'  => 'caaguazu_educacion_active_slug',
		'url_prefixes' => 'caaguazu_educacion_url_prefixes',
		'items'        => 'caaguazu_educacion_shell_items',
		'card'         => array(
			'body'  => __( 'Escuelas, becas municipales, programas y estadísticas educativas del departamento.', 'caaguazu-modulos' ),
			'image' => caaguazu_educacion_card_image(),
		),
	);
	return $ecos;
} );

/**
 * `register_activation_hook` solo corre al activar este plugin — catch-up
 * en `admin_init` para que la migración desde el CPT viejo corra sola en
 * la próxima visita a wp-admin en un sitio que ya lo tenía activo. Flag
 * nuevo (v2): el flag viejo (`caaguazu_modulos_educacion_caught_up`) ya
 * estaba en `1` en cualquier sitio con este módulo desde antes de la
 * migración a Entradas nativas — reusarlo haría que el catch-up nunca
 * vuelva a correr y la migración real nunca se ejecute (mismo error que
 * ya se encontró y corrigió en la migración de páginas de Turismo).
 */
function caaguazu_modulos_catch_up_educacion() {
	if ( get_option( 'caaguazu_modulos_educacion_caught_up_v2' ) ) {
		return;
	}
	caaguazu_modulos_seed_educacion();
	update_option( 'caaguazu_modulos_educacion_caught_up_v2', 1 );
}
add_action( 'admin_init', 'caaguazu_modulos_catch_up_educacion' );

/**
 * Auto-registro en los accesos rápidos y el nav de fallback del theme.
 */
add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'book',
		'label' => __( 'Educación', 'caaguazu-modulos' ),
		'url'   => caaguazu_category_url( 'educacion' ),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'  => 'educacion',
		'label' => __( 'Educación', 'caaguazu-modulos' ),
		'url'   => caaguazu_category_url( 'educacion' ),
	);
	return $items;
} );
