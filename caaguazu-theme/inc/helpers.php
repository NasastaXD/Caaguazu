<?php
/**
 * Helpers del theme.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Formatea una fecha 'Y-m-d' en español ("21 de julio, 2027") sin depender
 * de `date_i18n()` — el locale de WordPress puede no tener cargado el
 * archivo de traducción de nombres de mes en el servidor, y en ese caso
 * `date_i18n('F', ...)` cae en inglés aunque el resto del sitio esté en
 * español (bug visto en producción: "21 de June, 2027").
 */
function caaguazu_fecha_es( $when, $with_year = true ) {
	$meses = array(
		1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
		7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
	);
	$ts  = strtotime( $when );
	$dia = (int) date( 'j', $ts );
	$mes = $meses[ (int) date( 'n', $ts ) ];
	return $with_year ? sprintf( '%d de %s, %s', $dia, $mes, date( 'Y', $ts ) ) : sprintf( '%d de %s', $dia, $mes );
}

/**
 * Noticias, Agenda y Educación viven como Entradas nativas de WordPress
 * (post_type=post) diferenciadas por Categoría — no como custom post types
 * propios — para que el contenido esté donde cualquiera que usó WordPress
 * ya sabe buscarlo. Estos dos helpers son el punto único donde se resuelve
 * "¿de qué familia es esta categoría/entrada?", para no repetir la lógica
 * de árbol de categorías en cada template.
 */

/**
 * true si $post_id tiene asignada la categoría de slug $slug, o cualquiera
 * de sus sub-categorías — un editor normalmente solo tilda la más
 * específica (p. ej. "Cultura"), no también su padre ("Noticias"), así que
 * no alcanza con comparar categorías exactas.
 */
function caaguazu_post_in_category_tree( $post_id, $slug ) {
	$root = get_category_by_slug( $slug );
	if ( ! $root ) {
		return false;
	}
	$tree_ids     = array_merge( array( $root->term_id ), get_term_children( $root->term_id, 'category' ) );
	$post_cat_ids = wp_get_post_categories( $post_id );
	return (bool) array_intersect( $tree_ids, $post_cat_ids );
}

/**
 * Familia de un término de categoría ('noticias'|'agenda'|'educacion') si
 * el término (o alguno de sus ancestros) es una de las tres raíces que
 * declaran los módulos de contenido — o null si es una categoría ajena a
 * este sistema (p. ej. una que el admin creó a mano para otra cosa).
 */
function caaguazu_category_family( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return null;
	}
	$roots = array( 'noticias', 'agenda', 'educacion' );
	if ( in_array( $term->slug, $roots, true ) ) {
		return $term->slug;
	}
	foreach ( get_ancestors( $term->term_id, 'category' ) as $ancestor_id ) {
		$ancestor = get_category( $ancestor_id );
		if ( $ancestor && in_array( $ancestor->slug, $roots, true ) ) {
			return $ancestor->slug;
		}
	}
	return null;
}

/**
 * Misma idea que caaguazu_category_family() pero para un post en un
 * single: mira sus categorías asignadas (no la categoría "actual" de un
 * archivo) y devuelve la primera familia que encuentre.
 */
function caaguazu_post_category_family( $post_id ) {
	foreach ( get_the_category( $post_id ) as $term ) {
		$family = caaguazu_category_family( $term );
		if ( $family ) {
			return $family;
		}
	}
	return null;
}

/**
 * URL del archivo de una categoría por slug, con fallback a /{slug}/ si
 * todavía no existe (p. ej. plugin recién activado, antes de que el
 * catch-up de siembra haya corrido).
 */
function caaguazu_category_url( $slug ) {
	$term = get_category_by_slug( $slug );
	if ( $term ) {
		$link = get_category_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return home_url( '/' . $slug . '/' );
}

/**
 * Devuelve un slug estable para <body data-page="..."> e usos similares.
 * El JS y el CSS originales esperaban valores: home, sobre-caaguazu, servicios,
 * noticias, turismo, ecosistema, contacto, buscar.
 */
function caaguazu_current_page_slug() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_singular( 'post' ) && 'noticias' === caaguazu_post_category_family( get_queried_object_id() ) ) {
		return 'noticias';
	}
	if ( is_category() && 'noticias' === caaguazu_category_family( get_queried_object() ) ) {
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
 * para que el shell de ecosistemas (`inc/ecosystem-shell.php`) pueda
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
 * front-page.php y algunos page-templates/ (Contacto, Reportar) arman su
 * HTML a mano y nunca llaman a `the_content()` — perfecto para el diseño
 * fijo del sitio, pero le saca a un editor visual (Elementor, Brizy) el
 * único gancho que necesita para poder reemplazar esa página con un
 * diseño propio: si nunca se llama a `the_content()`, Elementor no
 * detecta "área de contenido" y muestra un error en vez de la página.
 *
 * Si un admin decide rediseñar una de esas páginas con Elementor (queda
 * marcada con el post meta `_elementor_edit_mode = 'builder'`), este
 * helper hace que el template ceda el paso: imprime `the_content()` (que
 * Elementor ya reemplaza con lo que armó) en vez del diseño fijo del
 * theme, y devuelve `true` para que el template corte ahí mismo. Si la
 * página NO fue diseñada con un editor visual, no hace nada y devuelve
 * `false` — el theme sigue mostrando su diseño de siempre, sin cambios.
 *
 * `page.php` no necesita este helper para su caso normal (ya llama a
 * `the_content()` siempre que hay contenido), pero sí lo usa para el
 * caso de una página vacía + diseñada con Elementor (ver ese archivo).
 */
function caaguazu_maybe_render_builder_content() {
	if ( ! is_singular() || ! have_posts() ) {
		return false;
	}
	if ( 'builder' !== get_post_meta( get_queried_object_id(), '_elementor_edit_mode', true ) ) {
		return false;
	}
	get_header();
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	get_footer();
	return true;
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
 * Grilla de tarjetas del Ecosistema (Turismo, Educación, sub-portales
 * externos como CEAD) — misma tarjeta que usa front-page.php, extraída acá
 * para poder reusarla también en la página `ecosistema` (antes esa página,
 * al visitarla directo en vez de scrollear desde el home, no mostraba
 * ninguna de estas tarjetas). Recibe el array ya resuelto por
 * `caaguazu_modulos_ecosystem_cards()` (o el fallback de 3 slots viejo);
 * no imprime nada si viene vacío.
 */
function caaguazu_render_ecosystem_cards( $eco_cards, $section_title = '', $section_body = '' ) {
	// Una sola vez por request: en la página `ecosistema` la grilla llega
	// por DOS caminos a la vez — el filtro the_content del plugin (que
	// funciona incluso cuando un page builder tomó la página, ver
	// caaguazu_modulos_append_eco_grid()) y el `if` histórico de page.php.
	// El primero que llama gana; el segundo no imprime nada.
	static $rendered = false;
	if ( $rendered || ! $eco_cards ) {
		return;
	}
	$rendered = true;
	$section_title = $section_title ?: caaguazu_opt( 'eco_section_title', __( 'Sub-portales del departamento', 'caaguazu' ) );
	$section_body  = $section_body ?: caaguazu_opt( 'eco_section_body', __( 'Caaguazu.net centraliza el acceso a los sub-portales especializados del departamento. Cada uno conserva su propio contenido dentro de una misma identidad institucional.', 'caaguazu' ) );
	?>
	<section class="eco">
		<div class="container">
			<div class="section-head reveal">
				<p class="eyebrow"><?php esc_html_e( 'Ecosistema', 'caaguazu' ); ?></p>
				<h2><?php echo esc_html( $section_title ); ?></h2>
				<p><?php echo esc_html( $section_body ); ?></p>
			</div>
			<div class="eco-grid">
				<?php foreach ( $eco_cards as $card ) :
					$soon   = empty( $card['url'] );
					$tag_el = $soon ? 'div' : 'a';
					if ( $soon ) {
						$attrs = '';
					} elseif ( ! empty( $card['external'] ) ) {
						$attrs = sprintf( 'href="%s" target="_blank" rel="noreferrer"', esc_url( $card['url'] ) );
					} else {
						$attrs = sprintf( 'href="%s"', esc_url( $card['url'] ) );
					}
				?>
					<<?php echo $tag_el; ?> class="eco-card reveal <?php echo $soon ? 'soon' : ''; ?>" <?php echo $attrs; ?>>
						<div class="img"><img src="<?php echo esc_url( $card['image'] ); ?>" alt="" loading="lazy"></div>
						<div class="body">
							<span class="eco-tag"><?php echo esc_html( $card['tag'] ); ?></span>
							<h3><?php echo esc_html( $card['title'] ); ?></h3>
							<p class="desc"><?php echo esc_html( $card['body'] ); ?></p>
							<span class="arrow"><?php echo esc_html( $card['cta'] ); ?></span>
						</div>
					</<?php echo $tag_el; ?>>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Resuelve las tarjetas del Ecosistema (registry de plugins 1.4+, o el
 * fallback de 3 slots posicionales de un plugin viejo) — mismo cálculo que
 * antes vivía inline en front-page.php, ahora compartido con la página
 * `ecosistema` (ver `caaguazu_render_ecosystem_cards()`).
 */
function caaguazu_resolve_ecosystem_cards() {
	if ( function_exists( 'caaguazu_modulos_ecosystem_cards' ) ) {
		return caaguazu_modulos_ecosystem_cards();
	}
	if ( ! function_exists( 'caaguazu_ecosystem_defaults' ) ) {
		return array();
	}
	$eco_cards    = array();
	$eco_defaults = caaguazu_ecosystem_defaults();
	for ( $i = 0; $i < 3; $i++ ) {
		$d           = $eco_defaults[ $i ];
		$eco_cards[] = array(
			'tag'      => caaguazu_opt( "eco_{$i}_tag", $d['tag'] ),
			'title'    => caaguazu_opt( "eco_{$i}_title", $d['title'] ),
			'body'     => caaguazu_opt( "eco_{$i}_body", $d['body'] ),
			'cta'      => caaguazu_opt( "eco_{$i}_cta", $d['cta'] ),
			'url'      => caaguazu_opt( "eco_{$i}_url", $d['url'] ),
			'image'    => caaguazu_opt_image( "eco_{$i}_image", $d['image'] ),
			'external' => true,
		);
	}
	return $eco_cards;
}

/**
 * Tabbar fijo inferior (solo móvil): navegación tipo app, siempre alcanzable
 * con el pulgar, sin importar en qué página esté el usuario.
 */
function caaguazu_render_tabbar( $current_slug ) {
	$items = array(
		array( 'icon' => 'home',   'label' => __( 'Inicio', 'caaguazu' ),   'url' => home_url( '/' ),                              'match' => 'home' ),
		array( 'icon' => 'search', 'label' => __( 'Buscar', 'caaguazu' ),   'url' => home_url( '/?s=' ),                           'match' => 'buscar' ),
		array( 'icon' => 'news',   'label' => __( 'Noticias', 'caaguazu' ), 'url' => caaguazu_category_url( 'noticias' ), 'match' => 'noticias' ),
		// Ecosistema y no Turismo directo: el hub de Ecosistema es la puerta a
		// todos los sub-portales (Turismo, CEAD y los que vengan) — así sumar
		// un módulo nuevo no obliga a repensar el tabbar cada vez.
		array( 'icon' => 'globe',  'label' => __( 'Ecosistema', 'caaguazu' ), 'url' => caaguazu_page_url( 'ecosistema' ),          'match' => 'ecosistema' ),
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
