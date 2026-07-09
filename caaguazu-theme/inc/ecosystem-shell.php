<?php
/**
 * Sistema de ecosistemas: secciones grandes del sitio que se sienten como
 * su propia app — header, tabbar y columna de footer propios en vez del
 * chrome institucional, más una identidad de color distinta vía tokens.
 *
 * Generaliza lo que antes era el shell hardcodeado de Turismo
 * (inc/tourism-shell.php): ahora cualquier plugin puede registrar un
 * ecosistema con el filtro `caaguazu_ecosystems` y hereda todo el shell
 * sin que el theme sepa de su existencia. Turismo queda registrado acá
 * mismo (no en su plugin) porque su detector abarca contenido de TRES
 * plugins en dos repos (caaguazu-turismo, caaguazu-locales,
 * caaguazu-portal) — el theme es su único punto común; Educación
 * (caaguazu-modulos) es el ejemplo del patrón registrado desde un plugin.
 *
 * Forma de cada registro (la clave del array es el slug):
 *
 *   $ecos['turismo'] = array(
 *     'label'        => 'Turismo',   // wordmark del shell y label del telón
 *     'home_icon'    => '🌲',        // ícono del ítem "Inicio" del tabbar — clave de inc/icons.php, o un emoji/HTML literal
 *     'hub_url'      => callable,    // string: URL del hub (página o archive)
 *     'is_context'   => callable,    // bool: ¿la request actual es de este eco?
 *     'is_hub'       => callable,    // bool: header transparente sobre hero propio
 *     'active_slug'  => callable,    // string: sección a resaltar en nav/tabbar ('' = ninguna)
 *     'url_prefixes' => callable,    // string[]: URLs del eco (telón JS, animations.js)
 *     'items'        => callable,    // array[]: secciones {slug,label,short,icon,url}
 *     'card'         => array,       // opcional: {body,image} para la tarjeta del hub Ecosistema
 *     'legacy_classes' => array,     // opcional: body classes viejas a seguir emitiendo
 *   );
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registry completo. Cacheado por request: los plugins registran vía
 * add_filter al cargar, mucho antes del primer render.
 */
function caaguazu_ecosystems() {
	static $ecos = null;
	if ( null === $ecos ) {
		$ecos = array();
		foreach ( (array) apply_filters( 'caaguazu_ecosystems', array() ) as $slug => $eco ) {
			if ( empty( $eco['label'] ) || empty( $eco['is_context'] ) ) {
				continue;
			}
			$eco['slug']   = $slug;
			$ecos[ $slug ] = $eco;
		}
	}
	return $ecos;
}

/**
 * El ecosistema de la request actual, o null si es el sitio institucional.
 * Único punto de decisión que consultan header.php/footer.php/functions.php.
 */
function caaguazu_current_ecosystem() {
	static $current = false;
	if ( false === $current ) {
		$current = null;
		foreach ( caaguazu_ecosystems() as $eco ) {
			if ( call_user_func( $eco['is_context'] ) ) {
				$current = $eco;
				break;
			}
		}
	}
	return $current;
}

function caaguazu_ecosystem_is_hub( $eco ) {
	return ! empty( $eco['is_hub'] ) && call_user_func( $eco['is_hub'] );
}

function caaguazu_ecosystem_active_slug( $eco ) {
	return empty( $eco['active_slug'] ) ? '' : (string) call_user_func( $eco['active_slug'] );
}

function caaguazu_ecosystem_items( $eco ) {
	return empty( $eco['items'] ) ? array() : (array) call_user_func( $eco['items'] );
}

function caaguazu_ecosystem_hub_url( $eco ) {
	return empty( $eco['hub_url'] ) ? home_url( '/' ) : call_user_func( $eco['hub_url'] );
}

/**
 * Header del shell: mismas clases (.header/.header-inner/.logo/.nav) que el
 * header institucional, para heredar toda la mecánica ya resuelta (drawer,
 * tabbar, scroll, responsive) — pero con el wordmark del ecosistema, su
 * propia navegación y una salida directa al sitio institucional.
 */
function caaguazu_render_ecosystem_header( $eco ) {
	$hub_url = caaguazu_ecosystem_hub_url( $eco );
	$is_hub  = caaguazu_ecosystem_is_hub( $eco );
	$active  = caaguazu_ecosystem_active_slug( $eco );
	$items   = caaguazu_ecosystem_items( $eco );
	?>
	<header class="header eco-header <?php echo $is_hub ? '' : 'solid'; ?>" id="header">
		<div class="header-inner">
			<div class="logo eco-logo">
				<a class="eco-back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo caaguazu_icon( 'back' ); ?> <?php esc_html_e( 'Caaguazú', 'caaguazu' ); ?></a>
				<a class="logo-name" href="<?php echo esc_url( $hub_url ); ?>"><?php echo esc_html( $eco['label'] ); ?></a>
			</div>

			<nav class="nav" aria-label="<?php echo esc_attr( $eco['label'] ); ?>">
				<?php caaguazu_render_nav_item_list( $items, $active ); ?>
			</nav>

			<div class="header-actions">
				<a href="<?php echo esc_url( get_search_link() ? get_search_link() : home_url( '/?s=' ) ); ?>" class="icon-btn" aria-label="<?php esc_attr_e( 'Buscar', 'caaguazu' ); ?>"><?php echo caaguazu_icon( 'search' ); ?></a>
				<button class="icon-btn burger" id="burger" aria-label="<?php esc_attr_e( 'Abrir menú', 'caaguazu' ); ?>"><?php echo caaguazu_icon( 'menu' ); ?></button>
			</div>
		</div>
	</header>

	<div class="drawer-bg" id="drawerBg"></div>
	<aside class="drawer" id="drawer" aria-hidden="true">
		<button class="close" id="drawerClose" aria-label="<?php esc_attr_e( 'Cerrar', 'caaguazu' ); ?>">×</button>
		<a class="eco-back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo caaguazu_icon( 'back' ); ?> <?php esc_html_e( 'Volver a Caaguazú', 'caaguazu' ); ?></a>
		<?php caaguazu_render_nav_item_list( $items, $active ); ?>
	</aside>
	<?php
}

/**
 * Tabbar del shell: Inicio del eco + sus primeras 3 secciones + salida a
 * Caaguazú — reemplaza al tabbar institucional mientras se navega adentro.
 */
function caaguazu_render_ecosystem_tabbar( $eco ) {
	$hub_url  = caaguazu_ecosystem_hub_url( $eco );
	$active   = caaguazu_ecosystem_active_slug( $eco );
	$sections = array_slice( caaguazu_ecosystem_items( $eco ), 0, 3 );

	$items = array(
		array(
			'icon'  => empty( $eco['home_icon'] ) ? 'home' : $eco['home_icon'],
			'label' => __( 'Inicio', 'caaguazu' ),
			'url'   => $hub_url,
			'match' => $eco['slug'],
		),
	);
	foreach ( $sections as $s ) {
		$items[] = array(
			'icon'  => isset( $s['icon'] ) ? $s['icon'] : 'pin',
			'label' => isset( $s['short'] ) ? $s['short'] : $s['label'],
			'url'   => $s['url'],
			'match' => $s['slug'],
		);
	}

	echo '<nav class="tabbar" aria-label="' . esc_attr( sprintf( __( 'Navegación de %s', 'caaguazu' ), $eco['label'] ) ) . '">';
	foreach ( $items as $item ) {
		printf(
			'<a class="tabbar-link%s" href="%s"><span class="tabbar-ico" aria-hidden="true">%s</span><span>%s</span></a>',
			( $active === $item['match'] ) ? ' active' : '',
			esc_url( $item['url'] ),
			caaguazu_icon( $item['icon'] ),
			esc_html( $item['label'] )
		);
	}
	printf(
		'<a class="tabbar-link" href="%s"><span class="tabbar-ico" aria-hidden="true">%s</span><span>%s</span></a>',
		esc_url( home_url( '/' ) ),
		caaguazu_icon( 'back' ),
		esc_html__( 'Caaguazú', 'caaguazu' )
	);
	echo '</nav>';
}

/* ---------------------------------------------------------------------------
 * Ecosistema Turismo — primer consumidor del registry, con exactamente el
 * mismo comportamiento que tenía como shell hardcodeado.
 * ------------------------------------------------------------------------ */

/**
 * Detector de Turismo. Vive en el theme (no en caaguazu-turismo) porque
 * abarca contenido de tres plugins: páginas sembradas por Caaguazú Turismo,
 * perfiles/archivo de Caaguazú Locales y fichas/taxonomías de Caaguazú
 * Portal. Sigue expuesta con su nombre histórico: page.php y otros la usan.
 */
function caaguazu_is_tourism_context() {
	if ( is_page() && get_post_meta( get_queried_object_id(), '_caaguazu_tourism', true ) ) {
		return true;
	}
	// Perfiles Y archivos de Locales/Portal: el ítem "Directorio de locales"
	// del propio shell apunta al archivo de cgz_local — si el archivo
	// renderizara con el chrome institucional, navegar el shell te sacaría
	// del shell.
	if ( post_type_exists( 'cgz_local' ) && ( is_singular( 'cgz_local' ) || is_post_type_archive( 'cgz_local' ) ) ) {
		return true;
	}
	if ( post_type_exists( 'promotur_destino' ) && ( is_singular( 'promotur_destino' ) || is_post_type_archive( 'promotur_destino' ) ) ) {
		return true;
	}
	foreach ( array( 'promotur_categoria', 'promotur_zona', 'promotur_etiqueta' ) as $tax ) {
		if ( taxonomy_exists( $tax ) && is_tax( $tax ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Prefijos de URL del ecosistema Turismo — espeja el detector de arriba
 * pero en términos de URLs, para el telón de transición del JS.
 */
function caaguazu_tourism_url_prefixes() {
	$prefixes = array( caaguazu_page_url( 'turismo' ) );
	if ( post_type_exists( 'cgz_local' ) ) {
		$archive = get_post_type_archive_link( 'cgz_local' );
		if ( $archive ) {
			$prefixes[] = $archive;
		}
	}
	if ( post_type_exists( 'promotur_destino' ) ) {
		$archive = get_post_type_archive_link( 'promotur_destino' );
		if ( $archive ) {
			$prefixes[] = $archive;
		}
	}
	foreach ( array( 'promotur_categoria', 'promotur_zona', 'promotur_etiqueta' ) as $tax ) {
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$tax_obj = get_taxonomy( $tax );
		if ( $tax_obj && ! empty( $tax_obj->rewrite['slug'] ) ) {
			$prefixes[] = home_url( '/' . $tax_obj->rewrite['slug'] . '/' );
		}
	}
	return array_values( array_unique( $prefixes ) );
}

/**
 * true solo en el hub raíz de Turismo (la página `turismo`) — header
 * transparente sobre su hero a sangre, igual que la home institucional.
 */
function caaguazu_is_tourism_hub() {
	return is_page() && 'turismo' === get_post_field( 'post_name', get_queried_object_id() );
}

/**
 * Slug de la sección de primer nivel activa de Turismo — recorre ancestros
 * hasta la página cuyo padre directo es el hub `turismo`.
 */
function caaguazu_tourism_active_section_slug() {
	if ( ! caaguazu_is_tourism_context() ) {
		return '';
	}
	$id = get_queried_object_id();
	if ( ! $id || ! is_page() ) {
		return ''; // p. ej. un perfil de Locales/Portal: no cuelga del árbol de páginas de Turismo.
	}
	if ( 'turismo' === get_post_field( 'post_name', $id ) ) {
		return 'turismo';
	}
	$chain = array_merge( array( $id ), get_post_ancestors( $id ) );
	foreach ( $chain as $candidate_id ) {
		$parent_id = wp_get_post_parent_id( $candidate_id );
		if ( $parent_id && 'turismo' === get_post_field( 'post_name', $parent_id ) ) {
			return get_post_field( 'post_name', $candidate_id );
		}
	}
	return get_post_field( 'post_name', $id );
}

/**
 * URL del hub de Turismo, con fallback si el plugin no expone su helper.
 */
function caaguazu_tourism_hub_url() {
	if ( function_exists( 'caaguazu_turismo_hub_url' ) ) {
		return caaguazu_turismo_hub_url();
	}
	return caaguazu_page_url( 'turismo' );
}

/**
 * Imagen de portada del hub de Turismo (hero a sangre de `page.php`) y de
 * su tarjeta en el hub Ecosistema — editables desde Personalizar → Contenido
 * del Home → Turismo (imágenes), en vez de quedar fijas en el código.
 */
function caaguazu_tourism_hub_hero_image() {
	return caaguazu_opt_image( 'tourism_hub_hero_image', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Carpenter_in_his_workshop.jpg/1280px-Carpenter_in_his_workshop.jpg' );
}

function caaguazu_tourism_card_image() {
	return caaguazu_opt_image( 'tourism_card_image', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg/1280px-Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg' );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'caaguazu_turismo_images', array(
		'title' => __( 'Turismo (imágenes)', 'caaguazu' ),
		'panel' => 'caaguazu_home',
	) );
	caaguazu_add_image( $wp_customize, 'tourism_hub_hero_image', __( 'Portada del hub de Turismo', 'caaguazu' ), caaguazu_tourism_hub_hero_image(), 'caaguazu_turismo_images' );
	caaguazu_add_image( $wp_customize, 'tourism_card_image', __( 'Tarjeta de Turismo en Ecosistema', 'caaguazu' ), caaguazu_tourism_card_image(), 'caaguazu_turismo_images' );
} );

add_filter( 'caaguazu_ecosystems', function ( $ecos ) {
	$ecos['turismo'] = array(
		'label'        => __( 'Turismo', 'caaguazu' ),
		'home_icon'    => '🌲', // emoji a propósito (no un ícono SVG dibujado) — mismo emoji que el splash de entrada.
		'hub_url'      => 'caaguazu_tourism_hub_url',
		'is_context'   => 'caaguazu_is_tourism_context',
		'is_hub'       => 'caaguazu_is_tourism_hub',
		'active_slug'  => 'caaguazu_tourism_active_section_slug',
		'url_prefixes' => 'caaguazu_tourism_url_prefixes',
		'items'        => function () {
			// Filtro histórico: lo pueblan caaguazu-turismo, caaguazu-locales
			// y caaguazu-portal — se mantiene tal cual para no tocar esos plugins.
			return apply_filters( 'caaguazu_tourism_shell_items', array() );
		},
		'card'         => array(
			'body'  => __( 'Información sobre historia, oficio maderero, gastronomía y cultura guaraní del departamento.', 'caaguazu' ),
			'image' => caaguazu_tourism_card_image(),
		),
		// Las reglas CSS existentes (tokens madera, anchos del hub) siguen
		// colgadas de estas clases — se emiten junto a las eco-* genéricas.
		'legacy_classes' => array(
			'context' => 'tourism-page',
			'hub'     => 'tourism-hub',
		),
	);
	return $ecos;
}, 5 );
