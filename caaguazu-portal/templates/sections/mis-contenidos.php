<?php
/**
 * Mis contenidos: lo que escribió esta cuenta, de los tres tipos.
 *
 * Una sola lista y no tres pestañas: lo que alguien quiere saber al entrar es
 * «qué tengo a medias», y eso no se ordena por tipo, se ordena por cuándo lo
 * tocó. El tipo va como etiqueta en cada fila, y cada fila lleva al editor que
 * le corresponde.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$uid   = caaguazu_account_id();
$posts = get_posts( array(
	'post_type'      => PROMOTUR_Editorial::cpts(),
	'post_status'    => 'any',
	'meta_query'     => array( array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $uid ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	'posts_per_page' => 100,
	'orderby'        => 'modified',
	'order'          => 'DESC',
) );

$page_title = __( 'Mis contenidos', 'caaguazu-portal' );
$body = function () use ( $posts ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Tu producción', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Mis contenidos', 'caaguazu-portal' ); ?></h2>
		</div>
		<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/editor' ) ); ?>"><?php esc_html_e( '+ Nueva ficha', 'caaguazu-portal' ); ?></a>
	</div>

	<?php if ( empty( $posts ) ) : ?>
		<div class="promotur-card promotur-empty-box">
			<p><?php esc_html_e( 'Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.', 'caaguazu-portal' ); ?></p>
			<div class="promotur-editor__actions">
				<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/editor' ) ); ?>"><?php esc_html_e( 'Nueva ficha', 'caaguazu-portal' ); ?></a>
				<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( promotur_url( 'panel/articulos/nuevo' ) ); ?>"><?php esc_html_e( 'Nuevo artículo', 'caaguazu-portal' ); ?></a>
				<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( promotur_url( 'panel/recorridos/nuevo' ) ); ?>"><?php esc_html_e( 'Nuevo recorrido', 'caaguazu-portal' ); ?></a>
			</div>
		</div>
	<?php else : ?>
		<div class="promotur-list">
			<?php foreach ( $posts as $p ) :
				$estado = PROMOTUR_Editorial::get_estado( $p->ID );
				$tipo   = PROMOTUR_Editorial::tipo_de( $p );
				$url    = PROMOTUR_Editorial::url_editor( $p );
				if ( '' === $url ) { continue; }
				?>
				<a class="promotur-row" href="<?php echo esc_url( $url ); ?>">
					<span class="promotur-row__main">
						<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ? get_the_title( $p ) : __( '(sin título)', 'caaguazu-portal' ) ); ?></span>
						<span class="promotur-row__meta"><?php echo esc_html( PROMOTUR_Editorial::tipo_label( $tipo ) . ' · ' . get_the_modified_date( '', $p ) ); ?></span>
					</span>
					<span class="promotur-pill <?php echo esc_attr( PROMOTUR_Editorial::estado_class( $estado ) ); ?>"><?php echo esc_html( PROMOTUR_Editorial::estado_label( $estado ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
