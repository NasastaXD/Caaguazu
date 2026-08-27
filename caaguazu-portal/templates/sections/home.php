<?php
/**
 * Inicio / pulso accionable del panel.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$uid        = caaguazu_account_id();
$identity   = promotur_current_identity();
$can_review = caaguazu_account_can( 'promotor', 'promotur_review_content' );
$can_draft  = caaguazu_account_can( 'promotor', 'promotur_create_draft' );

/**
 * Cuenta contenido por estado (y dueño opcional), de los tres tipos: lo que
 * alguien tiene a medias no viene ordenado por tipo. El dueño se filtra por el
 * meta de dueño real (ver PROMOTUR_Destinos::OWNER_META), no por post_author.
 */
$count_by = function ( $estado, $owner = 0 ) {
	$meta_query = array( array( 'key' => '_promotur_estado', 'value' => (array) $estado, 'compare' => 'IN' ) );
	if ( $owner ) {
		$meta_query['relation'] = 'AND';
		$meta_query[] = array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $owner );
	}
	$q = new WP_Query( array(
		'post_type'      => PROMOTUR_Editorial::cpts(),
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	return (int) $q->found_posts;
};

$pulse = array();
if ( $can_review ) {
	$pulse[] = array( 'n' => PROMOTUR_Notifications::review_queue_count(), 'label' => __( 'Esperan revisión', 'caaguazu-portal' ), 'url' => 'panel/revision', 'icon' => 'inbox' );
	$pulse[] = array( 'n' => $count_by( 'publicado' ), 'label' => __( 'Publicados', 'caaguazu-portal' ), 'url' => 'panel/revision', 'icon' => 'check' );
}
if ( $can_draft ) {
	$pulse[] = array( 'n' => $count_by( 'necesita_cambios', $uid ), 'label' => __( 'Esperan tu corrección', 'caaguazu-portal' ), 'url' => 'panel/mis-contenidos', 'icon' => 'edit' );
	$pulse[] = array( 'n' => $count_by( array( 'borrador', 'enviado', 'en_revision' ), $uid ), 'label' => __( 'En proceso', 'caaguazu-portal' ), 'url' => 'panel/mis-contenidos', 'icon' => 'doc' );
}

// Actividad editorial de los últimos 7 días, del log de auditoría. Es la única
// serie temporal real que guarda el portal: no hay contadores por día de nada
// más, así que no hay más gráficos que este.
$serie = PROMOTUR_Stats::serie_diaria( PROMOTUR_Audit::post_actions(), 7 );
$tope  = 0;
foreach ( $serie['dias'] as $dia ) {
	$tope = max( $tope, $dia['n'] );
}

$page_title = __( 'Inicio', 'caaguazu-portal' );
$body = function () use ( $identity, $pulse, $serie, $tope, $can_draft, $can_review ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Tu actividad de hoy', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php
				/* translators: %s = nombre */
				printf( esc_html__( 'Hola, %s 👋', 'caaguazu-portal' ), esc_html( $identity['display_name'] ) );
			?></h2>
		</div>
	</div>

	<?php if ( ! empty( $pulse ) ) : ?>
		<div class="promotur-grid promotur-grid--3">
			<?php foreach ( $pulse as $p ) : ?>
				<a class="promotur-card promotur-card--link promotur-stat" href="<?php echo esc_url( promotur_url( $p['url'] ) ); ?>">
					<span class="promotur-card__head">
						<span><?php echo esc_html( $p['label'] ); ?></span>
						<span class="promotur-card__head-extra"><?php echo promotur_icon( $p['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</span>
					<span class="promotur-stat__caja promotur-stat__caja--fila">
						<span class="promotur-stat__n"><?php echo esc_html( number_format_i18n( $p['n'] ) ); ?></span>
						<span class="promotur-stat__ir"><?php echo promotur_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="promotur-card promotur-mt">
		<div class="promotur-card__head">
			<?php echo promotur_icon( 'chart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Actividad reciente', 'caaguazu-portal' ); ?></span>
		</div>
		<div class="promotur-stat__caja">
			<span class="promotur-stat__n"><?php echo esc_html( number_format_i18n( $serie['total'] ) ); ?></span>

			<?php
			// El trazo se arma en coordenadas de 0 a 100 y el SVG lo estira al
			// ancho de la tarjeta: así el gráfico se adapta sin recalcular nada
			// y sin JavaScript. `vector-effect` mantiene el grosor de la línea
			// aunque el estirado sea desparejo.
			$dias   = $serie['dias'];
			$cuenta = count( $dias );
			$puntos = array();
			$rejas  = '';
			foreach ( $dias as $i => $dia ) {
				$x = $cuenta > 1 ? ( $i / ( $cuenta - 1 ) ) * 100 : 50;
				$y = 92 - ( $tope > 0 ? ( $dia['n'] / $tope ) * 84 : 0 );
				$puntos[] = round( $x, 2 ) . ',' . round( $y, 2 );
				$rejas   .= sprintf( 'M%s,0 V100 ', round( $x, 2 ) );
			}
			$ultimo = $cuenta ? explode( ',', end( $puntos ) ) : array( 50, 50 );
			?>
			<div class="promotur-grafico" role="img"
			     aria-label="<?php echo esc_attr( sprintf( /* translators: %d = cantidad de días */ _n( 'Actividad de los últimos %d día', 'Actividad de los últimos %d días', $cuenta, 'caaguazu-portal' ), $cuenta ) ); ?>">
				<svg class="promotur-grafico__svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<path class="promotur-grafico__reja" d="<?php echo esc_attr( trim( $rejas ) ); ?>" vector-effect="non-scaling-stroke" />
					<polyline class="promotur-grafico__trazo" points="<?php echo esc_attr( implode( ' ', $puntos ) ); ?>" vector-effect="non-scaling-stroke" />
				</svg>
				<span class="promotur-grafico__hoy" style="left:<?php echo esc_attr( $ultimo[0] ); ?>%; top:<?php echo esc_attr( $ultimo[1] ); ?>%"></span>
			</div>

			<div class="promotur-grafico__dias">
				<?php foreach ( $dias as $dia ) : ?>
					<span><?php echo esc_html( date_i18n( 'D', strtotime( $dia['fecha'] ) ) ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Accesos rápidos', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-grid promotur-grid--3">
		<?php
		$quick = array();
		if ( caaguazu_account_can( 'promotor', 'promotur_edit_destino' ) ) {
			$quick[] = array( 'icon' => 'edit', 'label' => __( 'Crear una ficha', 'caaguazu-portal' ), 'url' => 'panel/editor' );
		}
		if ( $can_draft ) {
			$quick[] = array( 'icon' => 'doc', 'label' => __( 'Mis contenidos', 'caaguazu-portal' ), 'url' => 'panel/mis-contenidos' );
		}
		if ( $can_review ) {
			$quick[] = array( 'icon' => 'inbox', 'label' => __( 'Cola de revisión', 'caaguazu-portal' ), 'url' => 'panel/revision' );
		}
		if ( caaguazu_account_can( 'promotor', 'promotur_manage_team' ) ) {
			$quick[] = array( 'icon' => 'team', 'label' => __( 'Equipo', 'caaguazu-portal' ), 'url' => 'panel/equipo' );
		}
		$quick[] = array( 'icon' => 'user', 'label' => __( 'Mi perfil', 'caaguazu-portal' ), 'url' => 'panel/perfil' );
		foreach ( $quick as $qk ) :
			?>
			<a class="promotur-quick" href="<?php echo esc_url( promotur_url( $qk['url'] ) ); ?>">
				<?php echo promotur_icon( $qk['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php echo esc_html( $qk['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
