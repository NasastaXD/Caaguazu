<?php
/**
 * Helpers del theme.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Devuelve un slug estable para <body data-page="..."> e usos similares.
 * El JS y el CSS originales esperaban valores: home, sobre-caaguazu, servicios,
 * noticias, turismo, ecosistema, contacto, buscar.
 */
function caaguazu_current_page_slug() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_singular( 'caaguazu_news' ) || is_post_type_archive( 'caaguazu_news' ) || is_tax( 'caaguazu_news_cat' ) ) {
		return 'noticias';
	}
	if ( is_search() ) {
		return 'buscar';
	}
	$obj = get_queried_object();
	if ( $obj instanceof WP_Post && $obj->post_name ) {
		return $obj->post_name;
	}
	return 'page';
}

/**
 * Devuelve true si la página actual debe mostrar el header transparente (sólo home).
 */
function caaguazu_is_home() {
	return is_front_page();
}

/**
 * Renderiza el menú principal. Cada item de nivel 1 es un <div class="nav-item">;
 * si tiene hijos (en un menú real de WP, vía menu_item_parent) se agrega un
 * mega-menú desplegable con esos hijos — así una sección con muchas subpáginas
 * (como Turismo) es alcanzable en un clic desde cualquier parte del sitio,
 * en vez de tener que bajar hub por hub.
 */
function caaguazu_render_nav( $location = 'primary', $current_slug = '' ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		caaguazu_render_fallback_nav( $current_slug );
		return;
	}
	$menu  = wp_get_nav_menu_object( $locations[ $location ] );
	$items = $menu ? wp_get_nav_menu_items( $menu->term_id ) : array();
	if ( ! $items ) {
		caaguazu_render_fallback_nav( $current_slug );
		return;
	}

	$children = array();
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent ) {
			$children[ $item->menu_item_parent ][] = $item;
		}
	}

	foreach ( $items as $item ) {
		if ( $item->menu_item_parent ) {
			continue; // se pintan como hijos de su padre, no de nuevo en el nivel 1.
		}
		$is_active = '';
		if ( $current_slug && 'page' === $item->object ) {
			$page = get_post( (int) $item->object_id );
			if ( $page && $page->post_name === $current_slug ) {
				$is_active = ' active';
			}
		}
		$kids = isset( $children[ $item->ID ] ) ? $children[ $item->ID ] : array();
		echo '<div class="nav-item">';
		printf(
			'<a class="nav-link%s" href="%s">%s</a>',
			esc_attr( $is_active ),
			esc_url( $item->url ),
			esc_html( $item->title )
		);
		if ( $kids ) {
			echo '<div class="nav-dropdown"><div class="nav-dropdown-col">';
			foreach ( $kids as $kid ) {
				printf( '<a class="nav-dropdown-link" href="%s">%s</a>', esc_url( $kid->url ), esc_html( $kid->title ) );
			}
			echo '</div></div>';
		}
		echo '</div>';
	}
}

/**
 * Menú por defecto si el admin todavía no configuró uno en Apariencia → Menús.
 * Turismo lleva un mega-menú con las secciones/destinos reales (Ykua La
 * Patria, Techapyrã, etc.) para que se llegue en un clic desde cualquier
 * página, sin tener que pasar por el hub y después por su sub-hub.
 */
function caaguazu_render_fallback_nav( $current_slug = '' ) {
	$defaults = array(
		'sobre-caaguazu' => __( 'Sobre Caaguazú', 'caaguazu' ),
		'servicios'      => __( 'Servicios', 'caaguazu' ),
		'noticias'       => __( 'Noticias', 'caaguazu' ),
		'agenda'         => __( 'Agenda', 'caaguazu' ),
		'turismo'        => __( 'Turismo', 'caaguazu' ),
		'ecosistema'     => __( 'Ecosistema', 'caaguazu' ),
		'contacto'       => __( 'Contacto', 'caaguazu' ),
	);
	foreach ( $defaults as $slug => $label ) {
		$page = get_page_by_path( $slug );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
		$cls  = ( $slug === $current_slug ) ? ' active' : '';
		echo '<div class="nav-item">';
		printf( '<a class="nav-link%s" href="%s">%s</a>', esc_attr( $cls ), esc_url( $url ), caaguazu_i18n_html( 'nav.' . $slug, $label ) );
		if ( 'turismo' === $slug ) {
			caaguazu_render_turismo_dropdown();
		}
		echo '</div>';
	}
}

/**
 * Grupos de accesos directos a destinos reales de Turismo, para el
 * mega-menú del nav y el acordeón del drawer móvil. Las claves son los
 * wp_slug reales sembrados por inc/tourism-seeder.php (ver inc/tourism-content.php).
 */
function caaguazu_turismo_menu_groups() {
	return array(
		__( 'La Capital de la Madera', 'caaguazu' ) => array(
			'la-capital-de-la-madera'    => __( 'Introducción', 'caaguazu' ),
			'historia'                   => __( 'Historia', 'caaguazu' ),
			'la-ruta-de-la-madera'        => __( 'La Ruta de la Madera', 'caaguazu' ),
			'artesanos'                   => __( 'Artesanos', 'caaguazu' ),
		),
		__( 'Qué hacer', 'caaguazu' ) => array(
			'ykua-la-patria'          => __( 'Ykua La Patria', 'caaguazu' ),
			'patrimonio-religioso'    => __( 'Patrimonio religioso', 'caaguazu' ),
			'mercado-municipal'       => __( 'Mercado de Abasto', 'caaguazu' ),
			'parques-y-naturaleza'    => __( 'Parque Techapyrã', 'caaguazu' ),
		),
		__( 'Sabores', 'caaguazu' ) => array(
			'platos-tipicos' => __( 'Platos típicos', 'caaguazu' ),
			'donde-comer'    => __( 'Dónde comer', 'caaguazu' ),
			'mate-y-terere'  => __( 'Mate y tereré', 'caaguazu' ),
		),
		__( 'Planificá tu visita', 'caaguazu' ) => array(
			'como-llegar'       => __( 'Cómo llegar', 'caaguazu' ),
			'donde-alojarte'    => __( 'Dónde alojarte', 'caaguazu' ),
			'mapa-interactivo'  => __( 'Mapa interactivo', 'caaguazu' ),
		),
	);
}

/**
 * Resuelve la URL real de una página de Turismo (anidada bajo turismo/…)
 * a partir de su wp_slug, reusando el mismo cálculo de ruta que usa el
 * seeder para no duplicar esa lógica.
 */
function caaguazu_tourism_page_url( $wp_slug ) {
	if ( ! function_exists( 'caaguazu_tourism_pages' ) || ! function_exists( 'caaguazu_tourism_full_path' ) ) {
		return home_url( '/' );
	}
	$pages = caaguazu_tourism_pages();
	if ( ! isset( $pages[ $wp_slug ] ) ) {
		return home_url( '/' );
	}
	$full_path = caaguazu_tourism_full_path( $wp_slug, $pages );
	$page      = get_page_by_path( $full_path );
	return $page ? get_permalink( $page ) : home_url( '/' . $full_path . '/' );
}

function caaguazu_render_turismo_dropdown() {
	echo '<div class="nav-dropdown nav-dropdown--mega">';
	foreach ( caaguazu_turismo_menu_groups() as $group_label => $links ) {
		echo '<div class="nav-dropdown-col"><h4>' . esc_html( $group_label ) . '</h4>';
		foreach ( $links as $slug => $label ) {
			printf( '<a class="nav-dropdown-link" href="%s">%s</a>', esc_url( caaguazu_tourism_page_url( $slug ) ), esc_html( $label ) );
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Devuelve la URL de una página por slug, con fallback a home_url.
 */
function caaguazu_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/' . $slug . '/' );
}

/**
 * Lee un valor del Customizer aceptando un default; usa get_theme_mod.
 */
function caaguazu_opt( $key, $default = '' ) {
	$val = get_theme_mod( $key, $default );
	return $val === '' ? $default : $val;
}

/**
 * Devuelve una URL de imagen del Customizer, ya sea ID de adjunto o URL directa.
 */
function caaguazu_opt_image( $key, $default = '' ) {
	$val = get_theme_mod( $key, $default );
	if ( is_numeric( $val ) ) {
		$src = wp_get_attachment_image_url( (int) $val, 'caaguazu-card' );
		return $src ? $src : $default;
	}
	return $val ? $val : $default;
}

/**
 * Botones de compartir (WhatsApp primero — es Paraguay —, X, Facebook,
 * copiar link). Sin SDK de terceros, solo intents por URL + Clipboard API.
 */
function caaguazu_share_buttons( $url, $title ) {
	$encoded_url   = rawurlencode( $url );
	$encoded_title = rawurlencode( $title );
	ob_start();
	?>
	<div class="share-buttons">
		<span class="share-label"><?php esc_html_e( 'Compartir', 'caaguazu' ); ?></span>
		<a class="share-btn" target="_blank" rel="noopener noreferrer"
			href="https://wa.me/?text=<?php echo esc_attr( $encoded_title . ' ' . $encoded_url ); ?>"
			aria-label="<?php esc_attr_e( 'Compartir por WhatsApp', 'caaguazu' ); ?>">WhatsApp</a>
		<a class="share-btn" target="_blank" rel="noopener noreferrer"
			href="https://twitter.com/intent/tweet?text=<?php echo esc_attr( $encoded_title ); ?>&url=<?php echo esc_attr( $encoded_url ); ?>"
			aria-label="<?php esc_attr_e( 'Compartir en X', 'caaguazu' ); ?>">X</a>
		<a class="share-btn" target="_blank" rel="noopener noreferrer"
			href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $encoded_url ); ?>"
			aria-label="<?php esc_attr_e( 'Compartir en Facebook', 'caaguazu' ); ?>">Facebook</a>
		<button type="button" class="share-btn share-copy" data-url="<?php echo esc_attr( $url ); ?>">
			<?php esc_html_e( 'Copiar link', 'caaguazu' ); ?>
		</button>
	</div>
	<?php
	return ob_get_clean();
}
