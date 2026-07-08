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
 *
 * Solo trae hardcodeado lo que el theme posee directamente (Sobre Caaguazú y
 * Contacto, siempre primero/último). Todo lo demás (Noticias, Agenda,
 * Ecosistema, Turismo, y cualquier módulo futuro) lo suma un plugin via el
 * filtro `caaguazu_nav_items` — cada item es
 * `array( 'slug', 'label', 'url', 'dropdown_cb' opcional )`; `dropdown_cb` es
 * un callable que pinta un `.nav-dropdown` (ver `caaguazu_render_turismo_dropdown()`
 * en el plugin Caaguazú Turismo como ejemplo de mega-menú).
 */
function caaguazu_render_fallback_nav( $current_slug = '' ) {
	$items   = array(
		array( 'slug' => 'sobre-caaguazu', 'label' => __( 'Sobre Caaguazú', 'caaguazu' ), 'url' => caaguazu_page_url( 'sobre-caaguazu' ) ),
	);
	$items   = apply_filters( 'caaguazu_nav_items', $items );
	$items[] = array( 'slug' => 'contacto', 'label' => __( 'Contacto', 'caaguazu' ), 'url' => caaguazu_page_url( 'contacto' ) );

	caaguazu_render_nav_item_list( $items, $current_slug );
}

/**
 * Pinta una lista de items de nav (`array('slug','label','url','dropdown_cb'?)`)
 * como `.nav-item`/`.nav-link` — extraído de `caaguazu_render_fallback_nav()`
 * para que el shell propio de Turismo (`inc/tourism-shell.php`) pueda
 * reusar exactamente el mismo markup/CSS con su propia lista de items.
 */
function caaguazu_render_nav_item_list( $items, $current_slug = '' ) {
	foreach ( $items as $item ) {
		$slug = isset( $item['slug'] ) ? $item['slug'] : '';
		$cls  = ( $slug && $slug === $current_slug ) ? ' active' : '';
		echo '<div class="nav-item">';
		printf( '<a class="nav-link%s" href="%s">%s</a>', esc_attr( $cls ), esc_url( $item['url'] ), caaguazu_i18n_html( 'nav.' . $slug, $item['label'] ) );
		if ( ! empty( $item['dropdown_cb'] ) && is_callable( $item['dropdown_cb'] ) ) {
			call_user_func( $item['dropdown_cb'] );
		}
		echo '</div>';
	}
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
 * Accesos rápidos: grid de destinos principales estilo dashboard de app,
 * para que el sitio se navegue como un portal de servicios en vez de un
 * blog. Contacto es lo único hardcodeado acá (y va siempre al final); todo
 * lo demás lo suma un plugin via el filtro `caaguazu_quick_access_items` —
 * cada item es `array( 'icon', 'label', 'url' )`. Ver caaguazu-modulos/ y
 * caaguazu-turismo/ para ejemplos de cómo un módulo se agrega solo.
 */
function caaguazu_quick_access_items() {
	$items   = apply_filters( 'caaguazu_quick_access_items', array() );
	$items[] = array( 'icon' => 'mail', 'label' => __( 'Contacto', 'caaguazu' ), 'url' => caaguazu_page_url( 'contacto' ) );
	return $items;
}

/**
 * Pinta la grilla de accesos rápidos.
 */
function caaguazu_render_quick_access() {
	echo '<section class="quick-access" aria-label="' . esc_attr__( 'Accesos rápidos', 'caaguazu' ) . '">';
	echo '<div class="container"><div class="qa-grid">';
	foreach ( caaguazu_quick_access_items() as $item ) {
		printf(
			'<a class="qa-tile reveal" href="%s"><span class="qa-ico" aria-hidden="true">%s</span><span class="qa-label">%s</span></a>',
			esc_url( $item['url'] ),
			caaguazu_icon( $item['icon'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</div></div></section>';
}

/**
 * Carrusel de destinos de Turismo para el home: scroll horizontal con
 * tarjetas que se asoman al borde, en vez de una grilla estática. Depende
 * del helper `caaguazu_tourism_page_url()` que expone el plugin Caaguazú
 * Turismo — si el plugin no está activo, esta sección no se imprime.
 */
function caaguazu_render_turismo_carousel() {
	if ( ! function_exists( 'caaguazu_tourism_page_url' ) ) {
		return;
	}
	$destinos = array(
		array( 'slug' => 'la-capital-de-la-madera', 'tag' => 'Historia y oficio', 'title' => 'La Capital de la Madera', 'desc' => 'Especies madereras y artesanos.' ),
		array( 'slug' => 'que-hacer', 'tag' => 'Atractivos', 'title' => 'Qué hacer', 'desc' => 'Ykua La Patria, Techapyrã.' ),
		array( 'slug' => 'platos-tipicos', 'tag' => 'Gastronomía', 'title' => 'Platos típicos', 'desc' => 'Ryguasu chyryry, sopa paraguaya.' ),
		array( 'slug' => 'como-llegar', 'tag' => 'Info práctica', 'title' => 'Cómo llegar', 'desc' => 'Desde Asunción o Ciudad del Este.' ),
	);
	echo '<section class="container">';
	echo '<div class="news-head reveal"><div class="section-head">';
	echo '<p class="eyebrow">' . esc_html__( 'Turismo', 'caaguazu' ) . '</p>';
	echo '<h2>' . esc_html__( 'Para descubrir en Caaguazú', 'caaguazu' ) . '</h2>';
	echo '</div><a class="arrow" href="' . esc_url( caaguazu_tourism_page_url( 'turismo' ) ) . '">' . esc_html__( 'Ver todo', 'caaguazu' ) . '</a></div>';
	echo '<div class="turismo-carousel reveal">';
	foreach ( $destinos as $d ) {
		printf(
			'<a class="turismo-card" href="%s"><span class="eco-tag">%s</span><h3>%s</h3><p>%s</p><span class="arrow">%s</span></a>',
			esc_url( caaguazu_tourism_page_url( $d['slug'] ) ),
			esc_html( $d['tag'] ),
			esc_html( $d['title'] ),
			esc_html( $d['desc'] ),
			esc_html__( 'Descubrir', 'caaguazu' )
		);
	}
	echo '</div></section>';
}

/**
 * Tabbar fijo inferior (solo móvil): navegación tipo app, siempre alcanzable
 * con el pulgar, sin importar en qué página esté el usuario.
 */
function caaguazu_render_tabbar( $current_slug ) {
	$items = array(
		array( 'icon' => 'home',   'label' => __( 'Inicio', 'caaguazu' ),   'url' => home_url( '/' ),                              'match' => 'home' ),
		array( 'icon' => 'search', 'label' => __( 'Buscar', 'caaguazu' ),   'url' => home_url( '/?s=' ),                           'match' => 'buscar' ),
		array( 'icon' => 'news',   'label' => __( 'Noticias', 'caaguazu' ), 'url' => get_post_type_archive_link( 'caaguazu_news' ), 'match' => 'noticias' ),
		array( 'icon' => 'tree',   'label' => __( 'Turismo', 'caaguazu' ),  'url' => caaguazu_page_url( 'turismo' ),               'match' => 'turismo' ),
	);
	echo '<nav class="tabbar" aria-label="' . esc_attr__( 'Navegación rápida', 'caaguazu' ) . '">';
	foreach ( $items as $item ) {
		printf(
			'<a class="tabbar-link%s%s" href="%s"><span class="tabbar-ico" aria-hidden="true">%s</span><span>%s</span></a>',
			( $current_slug === $item['match'] ) ? ' active' : '',
			! empty( $item['cta'] ) ? ' tabbar-cta' : '',
			esc_url( $item['url'] ),
			caaguazu_icon( $item['icon'] ),
			esc_html( $item['label'] )
		);
	}
	printf(
		'<button type="button" class="tabbar-link" id="tabbarMenu"><span class="tabbar-ico" aria-hidden="true">%s</span><span>%s</span></button>',
		caaguazu_icon( 'menu' ),
		esc_html__( 'Menú', 'caaguazu' )
	);
	echo '</nav>';
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
