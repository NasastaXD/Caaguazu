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
 *
 * «DEL EQUIPO»
 *
 * Quien revisa (el Promotor, que es el rol con que entran los docentes) puede
 * pasar a ver lo de todos, borradores incluidos. Hacía falta: el editor ya le
 * dejaba abrir y corregir una ficha ajena, pero no había ningún lugar donde
 * encontrarla — el inventario sólo muestra lo publicado y la cola de revisión
 * sólo lo enviado, así que un borrador de un colega era invisible hasta que
 * su dueño lo mandaba. Artículos y recorridos ya se listaban enteros en sus
 * secciones; las fichas eran las únicas que se escapaban.
 *
 * Se gatea con la misma capability que ya decide «puede editar lo ajeno» y
 * «ve la papelera entera», para que ver y editar no queden desparejos.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$filtro = isset( $_GET['estado'] ) ? sanitize_key( wp_unslash( $_GET['estado'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$uid    = caaguazu_account_id();

$ve_equipo = promotur_can( 'promotur_review_content' );
$equipo    = $ve_equipo && isset( $_GET['de'] ) && 'equipo' === sanitize_key( wp_unslash( $_GET['de'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

if ( 'papelera' === $filtro ) {
	$posts = PROMOTUR_Estados::papelera();
} else {
	$meta_query = array( 'relation' => 'AND' );
	if ( $equipo ) {
		// Lo de todos, menos los recorridos que arma la gente en la app: son
		// privados de su dueño y el panel no los lista ni los edita (ver
		// templates/sections/recorridos.php). Fichas y artículos no tienen
		// ese meta, de ahí el NOT EXISTS.
		$meta_query[] = array(
			'relation' => 'OR',
			array( 'key' => PROMOTUR_Recorridos::META_TIPO, 'compare' => 'NOT EXISTS' ),
			array( 'key' => PROMOTUR_Recorridos::META_TIPO, 'value' => 'usuario', 'compare' => '!=' ),
		);
	} else {
		$meta_query[] = array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $uid );
	}
	if ( '' !== $filtro ) {
		$meta_query[] = array( 'key' => '_promotur_estado', 'value' => $filtro );
	}

	$posts = get_posts( array(
		'post_type'      => PROMOTUR_Editorial::cpts(),
		'post_status'    => 'any',
		'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
		'posts_per_page' => 100,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	) );

	// Lo archivado sale de circulación: no aparece en la lista de todo, sólo
	// cuando se lo pide. Si no, lo que se dejó de lado sigue ocupando la
	// pantalla que se abre para ver qué hay a medias.
	if ( '' === $filtro ) {
		$posts = array_values( array_filter( $posts, function ( $p ) {
			return 'archivado' !== PROMOTUR_Editorial::get_estado( $p->ID );
		} ) );
	}
}

$page_title = $equipo ? __( 'Contenidos del equipo', 'caaguazu-portal' ) : __( 'Mis contenidos', 'caaguazu-portal' );
$body = function () use ( $posts, $filtro, $ve_equipo, $equipo ) {
	$vistas = array(
		''             => __( 'En curso', 'caaguazu-portal' ),
		'publicado'    => __( 'Publicados', 'caaguazu-portal' ),
		'despublicado' => __( 'Despublicados', 'caaguazu-portal' ),
		'archivado'    => __( 'Archivados', 'caaguazu-portal' ),
		'papelera'     => __( 'Papelera', 'caaguazu-portal' ),
	);
	$base = promotur_url( 'panel/mis-contenidos' );

	// Cambiar de estado no saca de «Del equipo»: cada selector conserva lo
	// que eligió el otro.
	$url_estado = function ( $clave ) use ( $base, $equipo ) {
		$url = $equipo ? add_query_arg( 'de', 'equipo', $base ) : $base;
		return '' === $clave ? $url : add_query_arg( 'estado', $clave, $url );
	};
	$url_de = function ( $de ) use ( $base, $filtro ) {
		$url = '' === $filtro ? $base : add_query_arg( 'estado', $filtro, $base );
		return 'equipo' === $de ? add_query_arg( 'de', 'equipo', $url ) : $url;
	};

	// Una búsqueda de nombre por dueña distinta, no una por fila: con cien
	// filas de cinco personas son cinco consultas, no cien.
	$nombres = array();
	$autora  = function ( $post_id ) use ( &$nombres ) {
		$cuenta = promotur_owner_account_id( $post_id );
		if ( ! isset( $nombres[ $cuenta ] ) ) {
			$nombres[ $cuenta ] = promotur_account_display_name( $cuenta );
		}
		return $nombres[ $cuenta ];
	};
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php echo $equipo ? esc_html__( 'Lo que escribe todo el equipo', 'caaguazu-portal' ) : esc_html__( 'Tu producción', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php echo $equipo ? esc_html__( 'Contenidos del equipo', 'caaguazu-portal' ) : esc_html__( 'Mis contenidos', 'caaguazu-portal' ); ?></h2>
		</div>
		<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/editor' ) ); ?>"><?php esc_html_e( '+ Nueva ficha', 'caaguazu-portal' ); ?></a>
	</div>

	<div class="promotur-segs">
		<?php if ( $ve_equipo ) : ?>
			<span class="promotur-seg" role="group" aria-label="<?php esc_attr_e( 'De quién', 'caaguazu-portal' ); ?>">
				<?php foreach ( array( '' => __( 'Míos', 'caaguazu-portal' ), 'equipo' => __( 'Del equipo', 'caaguazu-portal' ) ) as $de => $etiqueta ) :
					$activo = ( 'equipo' === $de ) === $equipo;
					?>
					<a class="promotur-seg__item<?php echo $activo ? ' is-active' : ''; ?>"
					   href="<?php echo esc_url( $url_de( $de ) ); ?>"<?php echo $activo ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $etiqueta ); ?>
					</a>
				<?php endforeach; ?>
			</span>
		<?php endif; ?>

		<span class="promotur-seg" role="group" aria-label="<?php esc_attr_e( 'Filtrar por estado', 'caaguazu-portal' ); ?>">
			<?php foreach ( $vistas as $clave => $etiqueta ) : ?>
				<a class="promotur-seg__item<?php echo $clave === $filtro ? ' is-active' : ''; ?>"
				   href="<?php echo esc_url( $url_estado( $clave ) ); ?>"<?php echo $clave === $filtro ? ' aria-current="page"' : ''; ?>>
					<?php echo esc_html( $etiqueta ); ?>
				</a>
			<?php endforeach; ?>
		</span>
	</div>

	<?php if ( empty( $posts ) ) : ?>
		<div class="promotur-card promotur-empty-box promotur-mt">
			<?php if ( 'papelera' === $filtro ) : ?>
				<p><?php esc_html_e( 'La papelera está vacía.', 'caaguazu-portal' ); ?></p>
			<?php elseif ( $equipo && '' !== $filtro ) : ?>
				<p><?php esc_html_e( 'Nadie del equipo tiene nada en ese estado.', 'caaguazu-portal' ); ?></p>
			<?php elseif ( $equipo ) : ?>
				<p><?php esc_html_e( 'Nadie del equipo tiene nada en curso.', 'caaguazu-portal' ); ?></p>
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
				$meta = PROMOTUR_Editorial::tipo_label( $tipo ) . ' · ' . get_the_modified_date( '', $p );
				if ( $equipo ) {
					$meta = $autora( $p->ID ) . ' · ' . $meta;
				}
				?>
				<a class="promotur-row" href="<?php echo esc_url( $url ); ?>">
					<span class="promotur-row__main">
						<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ? get_the_title( $p ) : __( '(sin título)', 'caaguazu-portal' ) ); ?></span>
						<span class="promotur-row__meta"><?php echo esc_html( $meta ); ?></span>
					</span>
					<span class="promotur-pill <?php echo esc_attr( PROMOTUR_Editorial::estado_class( $estado ) ); ?>"><?php echo esc_html( PROMOTUR_Editorial::estado_label( $estado ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
