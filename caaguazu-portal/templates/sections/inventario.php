<?php
/**
 * Inventario turístico: las fichas publicadas del departamento, con sus datos.
 *
 * QUÉ ES Y POR QUÉ ESTÁ SEPARADO DE «MIS CONTENIDOS»
 *
 * «Mis contenidos» es el escritorio de cada persona: lo que ella está
 * escribiendo, en cualquier estado. El inventario es otra cosa — es el catálogo
 * del departamento, lo que ya está publicado y lo que la app muestra. No tiene
 * dueño y lo mira todo el equipo.
 *
 * Y hace falta que exista como pantalla, no sólo como concepto: es de acá de
 * donde un recorrido saca sus paradas, y antes de armar un recorrido hay que
 * poder ver qué hay cargado y con qué datos. Sin esta lista, «elegí sitios del
 * inventario» era elegir de una lista que nadie podía leer entera.
 *
 *   /turismo-panel/inventario        el catálogo
 *   /turismo-panel/inventario/<id>   una ficha, con todos sus datos
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$detalle_id = isset( $promotur_id ) ? (int) $promotur_id : 0;

/* ----------------------------------------------------------------------
 * Una ficha del inventario
 * -------------------------------------------------------------------- */
if ( $detalle_id ) {
	$post = get_post( $detalle_id );
	if ( ! $post || PROMOTUR_Destinos::CPT !== $post->post_type ) {
		wp_die( esc_html__( 'No encontramos esa ficha.', 'caaguazu-portal' ), '', array( 'response' => 404 ) );
	}

	$grupos    = PROMOTUR_Destinos::fields();
	$estado    = PROMOTUR_Editorial::get_estado( $detalle_id );
	$autor     = promotur_account_display_name( promotur_owner_account_id( $detalle_id ) );
	$maps      = PROMOTUR_Destinos::maps_url( $detalle_id );
	$puede     = promotur_puede_editar_contenido( $detalle_id );

	$page_title = get_the_title( $post );
	$body = function () use ( $post, $detalle_id, $grupos, $estado, $autor, $maps, $puede ) {
		?>
		<div class="promotur-pagehead">
			<div>
				<a class="promotur-back" href="<?php echo esc_url( promotur_url( 'panel/inventario' ) ); ?>">&larr; <?php esc_html_e( 'Volver al inventario', 'caaguazu-portal' ); ?></a>
				<div class="promotur-eyebrow"><?php esc_html_e( 'Sitio del inventario', 'caaguazu-portal' ); ?></div>
				<h2 class="promotur-h2"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
				<p class="promotur-muted">
					<?php
					/* translators: %s = autor */
					printf( esc_html__( 'Cargó %s', 'caaguazu-portal' ), esc_html( $autor ) );
					?>
					· <span class="promotur-pill <?php echo esc_attr( PROMOTUR_Editorial::estado_class( $estado ) ); ?>"><?php echo esc_html( PROMOTUR_Editorial::estado_label( $estado ) ); ?></span>
				</p>
			</div>
			<?php if ( $puede ) : ?>
				<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( promotur_url( 'panel/editor/' . $detalle_id ) ); ?>"><?php esc_html_e( 'Editar la ficha', 'caaguazu-portal' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="promotur-card">
			<?php
			$portada = (int) get_post_meta( $detalle_id, '_promotur_portada', true );
			if ( $portada ) {
				echo wp_get_attachment_image( $portada, 'large', false, array( 'class' => 'promotur-review__cover' ) );
			}
			?>
			<div class="promotur-prose"><?php echo wp_kses_post( wpautop( $post->post_content ) ); ?></div>

			<?php if ( $maps ) : ?>
				<h4 class="promotur-review__grouptitle"><?php esc_html_e( 'Ubicación', 'caaguazu-portal' ); ?></h4>
				<p><a class="promotur-nota-enlace" href="<?php echo esc_url( $maps ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Abrir en Google Maps', 'caaguazu-portal' ); ?></a></p>
			<?php endif; ?>

			<?php foreach ( $grupos as $grupo ) :
				// Un grupo sin un solo dato cargado no dibuja su título: en una
				// ficha a medias, cuatro encabezados vacíos son ruido.
				$filas = array();
				foreach ( $grupo['fields'] as $key => $def ) {
					$val = get_post_meta( $detalle_id, $key, true );
					if ( '' === trim( (string) $val ) ) { continue; }
					$filas[ $def['label'] ] = (string) $val;
				}
				if ( ! $filas ) { continue; }
				?>
				<h4 class="promotur-review__grouptitle"><?php echo esc_html( $grupo['label'] ); ?></h4>
				<dl class="promotur-deflist">
					<?php foreach ( $filas as $etiqueta => $valor ) : ?>
						<dt><?php echo esc_html( $etiqueta ); ?></dt>
						<dd><?php echo esc_html( $valor ); ?></dd>
					<?php endforeach; ?>
				</dl>
			<?php endforeach; ?>
		</div>
		<?php
	};

	include PROMOTUR_DIR . 'templates/shell.php';
	return;
}

/* ----------------------------------------------------------------------
 * El catálogo
 * -------------------------------------------------------------------- */
$busqueda = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$sitios   = promotur_inventario( $busqueda );

$page_title = __( 'Inventario turístico', 'caaguazu-portal' );
$body = function () use ( $sitios, $busqueda ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'El catálogo del departamento', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Inventario turístico', 'caaguazu-portal' ); ?></h2>
		</div>
		<?php if ( promotur_can( 'promotur_edit_destino' ) ) : ?>
			<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/editor' ) ); ?>"><?php esc_html_e( '+ Nueva ficha', 'caaguazu-portal' ); ?></a>
		<?php endif; ?>
	</div>

	<p class="promotur-muted"><?php esc_html_e( 'Los sitios publicados: lo que la app muestra hoy, y de donde los recorridos toman sus paradas.', 'caaguazu-portal' ); ?></p>

	<form class="promotur-filtros" method="get" action="<?php echo esc_url( promotur_url( 'panel/inventario' ) ); ?>" role="search">
		<input class="promotur-buscador" type="search" name="q" value="<?php echo esc_attr( $busqueda ); ?>"
		       placeholder="<?php esc_attr_e( 'Buscar un sitio…', 'caaguazu-portal' ); ?>"
		       aria-label="<?php esc_attr_e( 'Buscar en el inventario', 'caaguazu-portal' ); ?>">
		<button type="submit" class="promotur-btn promotur-btn--ghost"><?php esc_html_e( 'Buscar', 'caaguazu-portal' ); ?></button>
	</form>

	<?php if ( empty( $sitios ) ) : ?>
		<div class="promotur-card promotur-empty-box">
			<p>
				<?php
				echo '' !== $busqueda
					? esc_html__( 'Ningún sitio publicado coincide con esa búsqueda.', 'caaguazu-portal' )
					: esc_html__( 'Todavía no hay fichas publicadas. Una ficha entra al inventario cuando el staff la aprueba.', 'caaguazu-portal' );
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="promotur-list">
			<?php foreach ( $sitios as $p ) :
				$cat    = get_the_terms( $p->ID, 'promotur_categoria' );
				$partes = array();
				// Cuándo pasa va primero cuando la ficha es un evento: en una
				// lista de lugares, la fecha es lo que distingue a los que hay
				// que agarrar antes de que se terminen.
				if ( 'evento' === PROMOTUR_Destinos::tipo_item( $p->ID ) ) {
					$inicio = (string) get_post_meta( $p->ID, PROMOTUR_Destinos::META_INICIO, true );
					$marca  = $inicio ? strtotime( $inicio ) : 0;
					$partes[] = $marca
						? sprintf( __( 'Evento · %s', 'caaguazu-portal' ), date_i18n( 'j M Y, H:i', $marca ) )
						: __( 'Evento · sin fecha', 'caaguazu-portal' );
				}
				if ( $cat && ! is_wp_error( $cat ) ) { $partes[] = $cat[0]->name; }
				?>
				<a class="promotur-row" href="<?php echo esc_url( promotur_url( 'panel/inventario/' . $p->ID ) ); ?>">
					<span class="promotur-row__main">
						<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ); ?></span>
						<span class="promotur-row__meta"><?php echo esc_html( implode( ' · ', $partes ) ); ?></span>
					</span>
					<?php if ( ! PROMOTUR_Destinos::tiene_ubicacion( $p->ID ) ) : ?>
						<span class="promotur-pill is-changes"><?php esc_html_e( 'Sin ubicación', 'caaguazu-portal' ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
