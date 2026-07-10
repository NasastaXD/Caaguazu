<?php
/**
 * Eco-rail v2: sidebar derecho del sitio (referencia: panel lateral tipo
 * app — rail de íconos colapsable + panel expandido con etiquetas).
 *
 * UNA sola capa de navegación con dos modos:
 *  - Escritorio ancho (≥1280px y ≥620px de alto): panel a ALTURA COMPLETA
 *    pegado al borde derecho, flotante y redondeado. Colapsado = rail de
 *    íconos con tooltips al hover/foco; expandido = íconos + etiquetas +
 *    wordmark. Estado persistido en localStorage.
 *  - Móvil (<1024px): ESTE panel es el drawer del sitio institucional —
 *    reemplaza al drawer clásico (header.php ya no lo imprime): se abre
 *    con la hamburguesa del header o el "Menú" del tabbar, entra desde la
 *    derecha con scrim, siempre en modo expandido (íconos + etiquetas),
 *    con el selector de idioma en el pie (antes vivía en el drawer viejo).
 *    En 1024–1279px no hay rail: el nav completo del header cubre todo.
 *
 * Los ecosistemas (Turismo/Educación) conservan su drawer propio del shell
 * (inc/ecosystem-shell.php, mismos IDs #drawer/#burger): sidebar.js solo
 * toma la hamburguesa cuando NO existe un #drawer en la página.
 *
 * Los items salen del mismo filtro que los accesos rápidos del home
 * (`caaguazu_quick_access_items`: cada módulo se registra solo, con ícono
 * + etiqueta + URL) más Inicio, Sobre Caaguazú y Buscar; ajustables con el
 * filtro `caaguazu_sidebar_items`.
 *
 * CSS en main.css (bloque "Eco-rail"), comportamiento en assets/js/sidebar.js.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Items del rail: Inicio + Sobre Caaguazú + los accesos del portal (filtro
 * de los módulos) + Buscar al final. Cada item: array( 'icon', 'label', 'url' ).
 */
function caaguazu_sidebar_items() {
	$items = array(
		array( 'icon' => 'home', 'label' => __( 'Inicio', 'caaguazu' ), 'url' => home_url( '/' ) ),
		array( 'icon' => 'pin', 'label' => __( 'Sobre Caaguazú', 'caaguazu' ), 'url' => caaguazu_page_url( 'sobre-caaguazu' ) ),
	);
	foreach ( caaguazu_quick_access_items() as $item ) {
		$items[] = $item;
	}
	$items[] = array( 'icon' => 'search', 'label' => __( 'Buscar', 'caaguazu' ), 'url' => home_url( '/?s=' ) );

	return apply_filters( 'caaguazu_sidebar_items', $items );
}

/**
 * true si $url "contiene" a la página actual (por prefijo de path) — para
 * marcar el item activo del rail. Dos casos especiales: una URL con query
 * string (como Buscar, '/?s=') nunca se marca, y la URL del home (cuyo path
 * es prefijo de TODO el sitio, también en instalaciones en subdirectorio)
 * solo matchea la portada exacta.
 */
function caaguazu_sidebar_item_is_active( $url ) {
	if ( wp_parse_url( $url, PHP_URL_QUERY ) ) {
		return false;
	}
	$item_path    = untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	$home_path    = untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	$current_path = untrailingslashit( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ) );

	if ( $item_path === $home_path ) {
		return $current_path === $home_path && is_front_page() && ! is_search();
	}
	return $item_path === $current_path || 0 === strpos( $current_path . '/', $item_path . '/' );
}

/**
 * Pinta el rail (y su scrim para el modo drawer móvil). Se llama desde
 * footer.php en todas las páginas — elemento fixed: su posición en el DOM
 * no afecta el layout; al final del documento el teclado recorre primero
 * el contenido y después esta capa.
 */
function caaguazu_render_eco_rail() {
	$items = caaguazu_sidebar_items();
	if ( ! $items ) {
		return;
	}
	?>
	<div class="eco-rail-bg" id="ecoRailBg"></div>
	<nav class="eco-rail" id="ecoRail" aria-label="<?php esc_attr_e( 'Navegación del ecosistema', 'caaguazu' ); ?>">
		<div class="eco-rail-head">
			<button class="eco-rail-toggle" id="ecoRailToggle" aria-expanded="false" aria-controls="ecoRailNav"
				aria-label="<?php esc_attr_e( 'Menú del ecosistema', 'caaguazu' ); ?>"
				data-label="<?php esc_attr_e( 'Abrir menú', 'caaguazu' ); ?>">
				<span class="eco-rail-burger" aria-hidden="true"><i></i><i></i><i></i></span>
			</button>
			<a class="eco-rail-brand lbl" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?><span class="tld">.net</span>
			</a>
		</div>
		<div class="eco-rail-nav" id="ecoRailNav">
			<?php foreach ( $items as $item ) :
				$active = caaguazu_sidebar_item_is_active( $item['url'] );
			?>
				<a class="eco-rail-item<?php echo $active ? ' active' : ''; ?>"
					href="<?php echo esc_url( $item['url'] ); ?>"
					data-label="<?php echo esc_attr( $item['label'] ); ?>"
					<?php echo $active ? 'aria-current="page"' : ''; ?>>
					<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( $item['icon'] ); ?></span>
					<span class="lbl"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<div class="eco-rail-foot">
			<?php /* Selector de idioma: misma clase .lang y data-lang que el
			   header/hero — main.js sincroniza todas las copias solo. En el
			   drawer viejo esta era la vía de acceso móvil al selector; acá
			   sigue cumpliendo ese rol (visible solo con el panel expandido). */ ?>
			<div class="lang eco-rail-lang" role="group" aria-label="<?php esc_attr_e( 'Idioma', 'caaguazu' ); ?>">
				<button class="on" data-lang="ES">ES</button>
				<button data-lang="GN">GN</button>
				<button data-lang="EN" disabled title="<?php esc_attr_e( 'Próximamente', 'caaguazu' ); ?>">EN</button>
			</div>
			<button class="eco-rail-item eco-rail-sound" id="ecoRailSound" aria-pressed="false"
				data-label="<?php esc_attr_e( 'Sonido de interfaz', 'caaguazu' ); ?>">
				<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( 'sound' ); ?></span>
				<span class="lbl"><?php esc_html_e( 'Sonido de interfaz', 'caaguazu' ); ?></span>
			</button>
		</div>
	</nav>
	<?php
}
