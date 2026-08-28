<?php
/**
 * Mis contenidos: lo que escribió esta cuenta, de los tres tipos.
 *
 * Una sola lista y no tres pestañas: lo que alguien quiere saber al entrar es
 * «qué tengo a medias», y eso no se ordena por tipo, se ordena por cuándo lo
 * tocó. El tipo va como etiqueta en cada fila, y cada fila lleva al editor que
 * le corresponde.
 *
 * El filtro de estado existe por lo archivado y lo borrado: son cosas que salen
 * de circulación a propósito, y si desaparecieran de la única lista que las
 * muestra, recuperarlas exigiría abrir wp-admin — justo lo que este panel viene
 * evitando desde el cutover de identidad.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$filtro = isset( $_GET['estado'] ) ? sanitize_key( wp_unslash( $_GET['estado'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$uid    = caaguazu_account_id();

if ( 'papelera' === $filtro ) {
	$posts = PROMOTUR_Estados::papelera();
} else {
	$args = array(
		'post_type'      => PROMOTUR_Editorial::cpts(),
		'post_status'    => 'any',
		'meta_query'     => array( array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $uid ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		'posts_per_page' => 100,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	);
	if ( '' !== $filtro ) {
		$args['meta_query'][] = array( 'key' => '_promotur_estado', 'value' => $filtro );
		$args['meta_query']['relation'] = 'AND';
	}
	$posts = get_posts( $args );

	// Lo archivado sale de circulación: no aparece en la lista de todo, sólo
	// cuando se lo pide. Si no, lo que se dejó de lado sigue ocupando la
	// pantalla que se abre para ver qué hay a medias.
	if ( '' === $filtro ) {
		$posts = array_values( array_filter( $posts, function ( $p ) {
			return 'archivado' !== PROMOTUR_Editorial::get_estado( $p->ID );
		} ) );
	}
}

$page_title = __( 'Mis contenidos', 'caaguazu-portal' );
$body = function () use ( $posts, $filtro ) {
	$vistas = array(
		''             => __( 'En curso', 'caaguazu-portal' ),
		'publicado'    => __( 'Publicados', 'caaguazu-portal' ),
		'despublicado' => __( 'Despublicados', 'caaguazu-portal' ),
		'archivado'    => __( 'Archivados', 'caaguazu-portal' ),
		'papelera'     => __( 'Papelera', 'caaguazu-portal' ),
	);
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Tu producción', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Mis contenidos', 'caaguazu-portal' ); ?></h2>
		</div>
		<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/editor' ) ); ?>"><?php esc_html_e( '+ Nueva ficha', 'caaguazu-portal' ); ?></a>
	</div>

	<span class="promotur-seg" role="group" aria-label="<?php esc_attr_e( 'Filtrar por estado', 'caaguazu-portal' ); ?>">
		<?php foreach ( $vistas as $clave => $etiqueta ) :
			$url = '' === $clave
				? promotur_url( 'panel/mis-contenidos' )
				: add_query_arg( 'estado', $clave, promotur_url( 'panel/mis-contenidos' ) );
			?>
			<a class="promotur-seg__item<?php echo $clave === $filtro ? ' is-active' : ''; ?>"
			   href="<?php echo esc_url( $url ); ?>"<?php echo $clave === $filtro ? ' aria-current="page"' : ''; ?>>
				<?php echo esc_html( $etiqueta ); ?>
			</a>
		<?php endforeach; ?>
	</span>

	<?php if ( empty( $posts ) ) : ?>
		<div class="promotur-card promotur-empty-box promotur-mt">
			<?php if ( 'papelera' === $filtro ) : ?>
				<p><?php esc_html_e( 'La papelera está vacía.', 'caaguazu-portal' ); ?></p>
			<?php elseif ( '' !== $filtro ) : ?>
				<p><?php esc_html_e( 'No tenés nada en ese estado.', 'caaguazu-portal' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.', 'caaguazu-portal' ); ?></p>
				<div class="promotur-editor__actions">
					<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/editor' ) ); ?>"><?php esc_html_e( 'Nueva ficha', 'caaguazu-portal' ); ?></a>
					<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( promotur_url( 'panel/articulos/nuevo' ) ); ?>"><?php esc_html_e( 'Nuevo artículo', 'caaguazu-portal' ); ?></a>
					<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( promotur_url( 'panel/recorridos/nuevo' ) ); ?>"><?php esc_html_e( 'Nuevo recorrido', 'caaguazu-portal' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	<?php elseif ( 'papelera' === $filtro ) : ?>
		<p class="promotur-muted promotur-mt"><?php esc_html_e( 'Lo borrado se recupera acá, como borrador. Nada se pierde de verdad hasta que alguien lo vacíe.', 'caaguazu-portal' ); ?></p>
		<div class="promotur-list">
			<?php foreach ( $posts as $p ) : ?>
				<div class="promotur-row">
					<span class="promotur-row__main">
						<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ? get_the_title( $p ) : __( '(sin título)', 'caaguazu-portal' ) ); ?></span>
						<span class="promotur-row__meta"><?php echo esc_html( PROMOTUR_Editorial::tipo_label( PROMOTUR_Editorial::tipo_de( $p ) ) . ' · ' . get_the_modified_date( '', $p ) ); ?></span>
					</span>
					<button type="button" class="promotur-btn promotur-btn--ghost promotur-btn--small" data-restaurar="<?php echo esc_attr( $p->ID ); ?>">
						<?php esc_html_e( 'Recuperar', 'caaguazu-portal' ); ?>
					</button>
					<span class="promotur-form-msg" data-form-msg aria-live="polite"></span>
				</div>
			<?php endforeach; ?>
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
