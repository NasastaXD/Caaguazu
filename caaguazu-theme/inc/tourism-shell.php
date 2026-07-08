<?php
/**
 * Shell propio de la sección Turismo: header, tabbar y footer distintos del
 * chrome institucional mientras se navega dentro del ecosistema Turismo
 * (páginas sembradas por el plugin Caaguazú Turismo, perfiles de Caaguazú
 * Locales, fichas de destino de Caaguazú Portal). El theme no sabe nada del
 * contenido de esos plugins — solo expone el filtro
 * `caaguazu_tourism_shell_items` (mismo patrón que `caaguazu_nav_items`)
 * para que declaren sus propias secciones.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Único punto de verdad para "¿esto es parte del ecosistema Turismo?".
 * Reemplaza los dos cálculos redundantes que había antes: uno en
 * functions.php vía post meta, otro en page.php recorriendo ancestros.
 */
function caaguazu_is_tourism_context() {
	if ( is_page() && get_post_meta( get_queried_object_id(), '_caaguazu_tourism', true ) ) {
		return true;
	}
	if ( post_type_exists( 'cgz_local' ) && is_singular( 'cgz_local' ) ) {
		return true;
	}
	if ( post_type_exists( 'promotur_destino' ) && is_singular( 'promotur_destino' ) ) {
		return true;
	}
	return false;
}

/**
 * true solo en el hub raíz de Turismo (la página `turismo`) — decide si el
 * header va transparente (tiene su propio hero a sangre) o sólido, igual
 * que el header institucional lo hace para la home.
 */
function caaguazu_is_tourism_hub() {
	return is_page() && 'turismo' === get_post_field( 'post_name', get_queried_object_id() );
}

/**
 * Slug de la sección de primer nivel activa, para resaltarla en el nav del
 * shell — recorre ancestros hasta encontrar la página cuyo padre directo es
 * el hub `turismo`. Fuera del árbol de páginas de Turismo (p. ej. un perfil
 * de Locales) no hay sección que resaltar y devuelve ''.
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
 * URL del hub de Turismo, con el mismo fallback si el plugin no expone su
 * propio helper (no debería pasar dentro de un contexto de Turismo, pero
 * evita un fatal si alguna vez `cgz_local`/`promotur_destino` están activos
 * sin el plugin Caaguazú Turismo).
 */
function caaguazu_tourism_hub_url() {
	if ( function_exists( 'caaguazu_turismo_hub_url' ) ) {
		return caaguazu_turismo_hub_url();
	}
	return caaguazu_page_url( 'turismo' );
}

/**
 * Header propio de Turismo: mismas clases (.header/.header-inner/.logo/.nav)
 * que el header institucional, para heredar toda la mecánica ya resuelta
 * (drawer, tabbar, scroll, responsive) — pero con su propio wordmark, su
 * propio nav (vía `caaguazu_tourism_shell_items`) y una salida directa al
 * sitio institucional.
 */
function caaguazu_render_tourism_header( $current_slug ) {
	$hub_url = caaguazu_tourism_hub_url();
	$is_hub  = caaguazu_is_tourism_hub();
	$active  = caaguazu_tourism_active_section_slug();
	$items   = apply_filters( 'caaguazu_tourism_shell_items', array() );
	?>
	<header class="header tourism-header <?php echo $is_hub ? '' : 'solid'; ?>" id="header">
		<div class="header-inner">
			<div class="logo tourism-logo">
				<a class="tourism-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php esc_html_e( 'Caaguazú', 'caaguazu' ); ?></a>
				<a class="logo-name" href="<?php echo esc_url( $hub_url ); ?>"><?php esc_html_e( 'Turismo', 'caaguazu' ); ?></a>
			</div>

			<nav class="nav" aria-label="<?php esc_attr_e( 'Turismo', 'caaguazu' ); ?>">
				<?php caaguazu_render_nav_item_list( $items, $active ); ?>
			</nav>

			<div class="header-actions">
				<a href="<?php echo esc_url( get_search_link() ? get_search_link() : home_url( '/?s=' ) ); ?>" class="icon-btn" aria-label="<?php esc_attr_e( 'Buscar', 'caaguazu' ); ?>">🔍</a>
				<button class="icon-btn burger" id="burger" aria-label="<?php esc_attr_e( 'Abrir menú', 'caaguazu' ); ?>">☰</button>
			</div>
		</div>
	</header>

	<div class="drawer-bg" id="drawerBg"></div>
	<aside class="drawer" id="drawer" aria-hidden="true">
		<button class="close" id="drawerClose" aria-label="<?php esc_attr_e( 'Cerrar', 'caaguazu' ); ?>">×</button>
		<a class="tourism-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php esc_html_e( 'Volver a Caaguazú', 'caaguazu' ); ?></a>
		<?php caaguazu_render_nav_item_list( $items, $active ); ?>
	</aside>
	<?php
}

/**
 * Tabbar propio de Turismo: mismas clases que el tabbar institucional
 * (.tabbar/.tabbar-link), con ítems tomados del mismo filtro que arma el
 * nav (las primeras 3 secciones) más una salida directa a Caaguazú — así
 * "la barrita" deja de ser la del sitio general en cuanto se entra al
 * ecosistema, en vez de convivir con ella.
 */
function caaguazu_render_tourism_tabbar( $current_slug ) {
	$hub_url  = caaguazu_tourism_hub_url();
	$active   = caaguazu_tourism_active_section_slug();
	$sections = array_slice( apply_filters( 'caaguazu_tourism_shell_items', array() ), 0, 3 );

	$items = array(
		array( 'icon' => '🌳', 'label' => __( 'Inicio', 'caaguazu' ), 'url' => $hub_url, 'match' => 'turismo' ),
	);
	foreach ( $sections as $s ) {
		$items[] = array(
			'icon'  => isset( $s['icon'] ) ? $s['icon'] : '📍',
			'label' => isset( $s['short'] ) ? $s['short'] : $s['label'],
			'url'   => $s['url'],
			'match' => $s['slug'],
		);
	}

	echo '<nav class="tabbar" aria-label="' . esc_attr__( 'Navegación de Turismo', 'caaguazu' ) . '">';
	foreach ( $items as $item ) {
		printf(
			'<a class="tabbar-link%s" href="%s"><span class="tabbar-ico" aria-hidden="true">%s</span><span>%s</span></a>',
			( $active === $item['match'] ) ? ' active' : '',
			esc_url( $item['url'] ),
			wp_kses_post( $item['icon'] ),
			esc_html( $item['label'] )
		);
	}
	printf(
		'<a class="tabbar-link" href="%s"><span class="tabbar-ico" aria-hidden="true">↩</span><span>%s</span></a>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Caaguazú', 'caaguazu' )
	);
	echo '</nav>';
}
