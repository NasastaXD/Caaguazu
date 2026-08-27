<?php
/** Reportes: producción por autor y salud del contenido.
 *
 * Se fueron "lo más visto" y "búsquedas sin resultado": las dos se alimentaban
 * de la vitrina web que este plugin publicaba, y esa vitrina ya no existe.
 * Mostrarlas ahora sería mostrar ceros para siempre. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$health  = PROMOTUR_Stats::content_health( 6 );
$autores = promotur_team_members( array( 'promotur_promotor', 'promotur_mini' ) );

$page_title = __( 'Reportes', 'caaguazu-portal' );
$body = function () use ( $health, $autores ) {
	?>
	<div class="promotur-eyebrow"><?php esc_html_e( 'Métricas', 'caaguazu-portal' ); ?></div>
	<h2 class="promotur-h2"><?php esc_html_e( 'Actividad del portal', 'caaguazu-portal' ); ?></h2>

	<h3 class="promotur-h3"><?php esc_html_e( 'Producción por autor', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-list">
		<?php foreach ( $autores as $u ) :
			$c = PROMOTUR_Stats::author_counts( $u['id'] );
			if ( 0 === $c['total'] ) { continue; } ?>
			<div class="promotur-row">
				<span class="promotur-row__main"><span class="promotur-row__title"><?php echo esc_html( $u['display_name'] ); ?></span></span>
				<span class="promotur-row__meta"><?php
					/* translators: 1: publicadas, 2: total */
					printf( esc_html__( '%1$d publicadas / %2$d', 'caaguazu-portal' ), $c['publicadas'], $c['total'] );
				?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Estado del contenido', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-grid promotur-grid--2">
		<div class="promotur-card">
			<strong><?php echo esc_html( count( $health['sin_foto'] ) ); ?></strong> <?php esc_html_e( 'fichas publicadas sin portada', 'caaguazu-portal' ); ?>
			<?php if ( $health['sin_foto'] ) : ?>
				<ul class="promotur-linklist">
					<?php foreach ( array_slice( $health['sin_foto'], 0, 6 ) as $p ) : ?>
						<li><a href="<?php echo esc_url( promotur_url( 'panel/editor/' . $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="promotur-card">
			<strong><?php echo esc_html( count( $health['viejas'] ) ); ?></strong> <?php esc_html_e( 'fichas sin verificar hace +6 meses', 'caaguazu-portal' ); ?>
			<?php if ( $health['viejas'] ) : ?>
				<ul class="promotur-linklist">
					<?php foreach ( array_slice( $health['viejas'], 0, 6 ) as $p ) : ?>
						<li><a href="<?php echo esc_url( promotur_url( 'panel/editor/' . $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
