<?php
/**
 * Eco-rail: sidebar derecho colapsable de navegación del ecosistema.
 *
 * Rail flotante de íconos en el borde derecho (escritorio ancho); expandido
 * muestra las etiquetas. En viewports menores esta capa NO se duplica: ahí
 * el "sidebar derecho" es el drawer móvil de siempre (header.php), que en el
 * rework de motion adoptó el mismo lenguaje (panel desde la derecha, entrada
 * escalonada, Escape para cerrar).
 *
 * Los items salen del mismo filtro que alimenta los accesos rápidos del home
 * (`caaguazu_quick_access_items`: cada módulo/plugin se registra solo, con
 * ícono + etiqueta + URL, y el admin controla qué módulos hay activos — nada
 * hardcodeado acá aparte de Inicio y Buscar). `caaguazu_sidebar_items`
 * permite a un plugin ajustar la lista final del rail sin tocar los accesos
 * rápidos.
 *
 * CSS en main.css (bloque "Eco-rail"), comportamiento en assets/js/sidebar.js.
 * Sin JS el rail queda colapsado con todos sus links usables (los tooltips
 * son CSS puro); solo expandir/colapsar y el sonido opcional requieren JS.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Items del rail: Inicio + los accesos del portal (filtro de los módulos)
 * + Buscar al final. Cada item: array( 'icon', 'label', 'url' ).
 */
function caaguazu_sidebar_items() {
	$items = array(
		array( 'icon' => 'home', 'label' => __( 'Inicio', 'caaguazu' ), 'url' => home_url( '/' ) ),
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
 * Pinta el rail. Se llama desde footer.php en todas las páginas (elemento
 * fixed: su posición en el DOM no afecta el layout; al final del documento
 * el teclado recorre primero el contenido y después esta capa).
 */
function caaguazu_render_eco_rail() {
	$items = caaguazu_sidebar_items();
	if ( ! $items ) {
		return;
	}
	?>
	<nav class="eco-rail" id="ecoRail" aria-label="<?php esc_attr_e( 'Navegación del ecosistema', 'caaguazu' ); ?>">
		<button class="eco-rail-toggle" id="ecoRailToggle" aria-expanded="false" aria-controls="ecoRailNav"
			aria-label="<?php esc_attr_e( 'Menú del ecosistema', 'caaguazu' ); ?>"
			data-label="<?php esc_attr_e( 'Abrir menú', 'caaguazu' ); ?>">
			<span class="eco-rail-burger" aria-hidden="true"><i></i><i></i><i></i></span>
			<span class="lbl" aria-hidden="true"><?php esc_html_e( 'Colapsar', 'caaguazu' ); ?></span>
		</button>
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
			<button class="eco-rail-item eco-rail-sound" id="ecoRailSound" aria-pressed="false"
				data-label="<?php esc_attr_e( 'Sonido de interfaz', 'caaguazu' ); ?>">
				<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( 'sound' ); ?></span>
				<span class="lbl"><?php esc_html_e( 'Sonido de interfaz', 'caaguazu' ); ?></span>
			</button>
		</div>
	</nav>
	<?php
}
