<?php
/**
 * Menú lateral: marca, buscador, grupos de navegación (gateados por
 * capability, con submenú y estado activo) y pie con perfil, ayuda, instalar
 * y salir.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$review_badge = PROMOTUR_Notifications::review_queue_count();
$tareas_badge = class_exists( 'PROMOTUR_Tareas' ) ? PROMOTUR_Tareas::pending_count_for( caaguazu_account_id() ) : 0;

/**
 * Dibuja un item del menú. Devuelve false si la cuenta no tiene su capability
 * —así el grupo puede saber si le quedó algún item visible—.
 */
$promotur_item = function ( $item, $badges ) {
	if ( ! promotur_can( $item['cap'] ) ) {
		return false;
	}
	$activa = promotur_route_activa( $item['route'] );
	$badge  = isset( $item['badge'] ) && ! empty( $badges[ $item['badge'] ] ) ? (int) $badges[ $item['badge'] ] : 0;
	$hijos  = array();
	foreach ( isset( $item['hijos'] ) ? $item['hijos'] : array() as $hijo ) {
		if ( promotur_can( $hijo['cap'] ) ) {
			$hijos[] = $hijo;
		}
	}
	// El submenú arranca abierto si la sección que se ve es el padre o uno de
	// sus hijos: nadie tiene que ir a buscar dónde está parado.
	$abierto = $activa;
	foreach ( $hijos as $hijo ) {
		$abierto = $abierto || promotur_route_activa( $hijo['route'] );
	}
	$sub_id = 'promotur-sub-' . sanitize_html_class( str_replace( '/', '-', $item['route'] ) );
	?>
	<a class="promotur-nav__item<?php echo $activa ? ' is-active' : ''; ?>"
	   href="<?php echo esc_url( promotur_url( $item['route'] ) ); ?>"<?php echo $activa ? ' aria-current="page"' : ''; ?>>
		<?php echo promotur_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG controlado ?>
		<span class="promotur-nav__label"><?php echo esc_html( $item['label'] ); ?></span>
		<?php if ( $badge ) : ?>
			<span class="promotur-nav__badge"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<?php if ( $hijos ) : ?>
			<span class="promotur-nav__caret" data-subnav-toggle="<?php echo esc_attr( $sub_id ); ?>"
			      role="button" tabindex="0" aria-expanded="<?php echo $abierto ? 'true' : 'false'; ?>"
			      aria-controls="<?php echo esc_attr( $sub_id ); ?>"
			      aria-label="<?php esc_attr_e( 'Abrir menú', 'caaguazu-portal' ); ?>">
				<?php echo promotur_icon( 'caret' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
		<?php endif; ?>
	</a>
	<?php if ( $hijos ) : ?>
		<div class="promotur-subnav" id="<?php echo esc_attr( $sub_id ); ?>"<?php echo $abierto ? '' : ' hidden'; ?>>
			<?php foreach ( $hijos as $hijo ) :
				$hijo_activo = promotur_route_activa( $hijo['route'] );
				?>
				<a class="promotur-subnav__item<?php echo $hijo_activo ? ' is-active' : ''; ?>"
				   href="<?php echo esc_url( promotur_url( $hijo['route'] ) ); ?>"<?php echo $hijo_activo ? ' aria-current="page"' : ''; ?>>
					<?php echo esc_html( $hijo['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
	return true;
};
?>
<aside class="promotur-sidebar" data-sidebar>
	<a class="promotur-brand" href="<?php echo esc_url( promotur_url( 'panel' ) ); ?>">
		<span class="promotur-brand__mark" aria-hidden="true"><?php echo promotur_icon( 'marca' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="promotur-brand__text"><?php bloginfo( 'name' ); ?></span>
	</a>

	<form class="promotur-sidesearch" method="get" action="<?php echo esc_url( promotur_url( 'panel/buscar' ) ); ?>" role="search">
		<span class="promotur-sidesearch__icon"><?php echo promotur_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<input type="search" name="q" data-buscador
		       placeholder="<?php esc_attr_e( 'Buscar…', 'caaguazu-portal' ); ?>"
		       aria-label="<?php esc_attr_e( 'Buscar', 'caaguazu-portal' ); ?>">
		<kbd aria-hidden="true">⌘K</kbd>
	</form>

	<nav class="promotur-nav" aria-label="<?php esc_attr_e( 'Navegación del panel', 'caaguazu-portal' ); ?>">
		<?php
		$badges = array( 'revision' => $review_badge, 'tareas' => $tareas_badge );
		foreach ( promotur_nav_grupos() as $grupo ) {
			// El rótulo se imprime primero, pero sólo se queda si el grupo
			// terminó con algún item visible para esta cuenta.
			ob_start();
			$visibles = 0;
			foreach ( $grupo['items'] as $item ) {
				$visibles += $promotur_item( $item, $badges ) ? 1 : 0;
			}
			$html = ob_get_clean();
			if ( ! $visibles ) {
				continue;
			}
			printf( '<div class="promotur-nav__group">%s</div>', esc_html( $grupo['label'] ) );
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- ya escapado arriba
		}
		?>
	</nav>

	<div class="promotur-sidebar__foot">
		<?php
		foreach ( promotur_nav_pie() as $item ) {
			$promotur_item( $item, array() );
		}
		?>
		<button type="button" class="promotur-nav__item promotur-install" data-install-app hidden>
			<?php echo promotur_icon( 'install' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="promotur-nav__label"><?php esc_html_e( 'Instalar app', 'caaguazu-portal' ); ?></span>
		</button>
		<a class="promotur-nav__item" href="<?php echo esc_url( promotur_url( 'salir' ) ); ?>">
			<?php echo promotur_icon( 'logout' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="promotur-nav__label"><?php esc_html_e( 'Cerrar sesión', 'caaguazu-portal' ); ?></span>
		</a>
	</div>
</aside>
