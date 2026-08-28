<?php
/**
 * Recorridos: la lista, y el armador de uno.
 *
 *   /turismo-panel/recorridos          la lista
 *   /turismo-panel/recorridos/nuevo    uno en blanco
 *   /turismo-panel/recorridos/<id>     ese recorrido
 *
 * EL ARMADOR
 *
 * Es la única pantalla del panel que no es un formulario plano, porque lo que
 * edita tampoco lo es: un recorrido es una secuencia. Cada parada es una fila
 * con el sitio elegido del inventario, el texto que la acompaña —la historia,
 * el dato curioso—, un audio o video opcional, y dos botones para moverla de
 * lugar.
 *
 * Las filas se numeran de nuevo en el navegador cada vez que algo se mueve, y
 * el servidor las vuelve a numerar al guardar según el orden en que llegan.
 * Ninguno de los dos confía en el otro, y el orden que se ve es siempre el
 * orden que se guarda.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$segmento = isset( $promotur_id ) ? (string) $promotur_id : '';
$es_nuevo = ( 'nuevo' === $segmento );
$post_id  = ( ! $es_nuevo && $segmento ) ? (int) $segmento : 0;

/* ----------------------------------------------------------------------
 * Lista
 * -------------------------------------------------------------------- */
if ( ! $es_nuevo && ! $post_id ) {

	$args = array(
		'post_type'      => PROMOTUR_Recorridos::CPT,
		'post_status'    => 'any',
		'posts_per_page' => 100,
		'orderby'        => 'modified',
		'order'          => 'DESC',
		// Sólo los del equipo: los que arma la gente en la app son privados de
		// su dueño y no se listan ni se editan desde acá.
		'meta_query'     => array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'key'     => PROMOTUR_Recorridos::META_TIPO,
			'value'   => 'usuario',
			'compare' => '!=',
		) ),
	);
	$recorridos = get_posts( $args );

	$page_title = __( 'Recorridos', 'caaguazu-portal' );
	$body = function () use ( $recorridos ) {
		?>
		<div class="promotur-pagehead">
			<div>
				<div class="promotur-eyebrow"><?php esc_html_e( 'Rutas armadas', 'caaguazu-portal' ); ?></div>
				<h2 class="promotur-h2"><?php esc_html_e( 'Recorridos', 'caaguazu-portal' ); ?></h2>
			</div>
			<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/recorridos/nuevo' ) ); ?>"><?php esc_html_e( '+ Nuevo recorrido', 'caaguazu-portal' ); ?></a>
		</div>

		<?php if ( empty( $recorridos ) ) : ?>
			<div class="promotur-card promotur-empty-box">
				<p><?php esc_html_e( 'Todavía no hay recorridos. Un recorrido encadena hasta nueve sitios del inventario en un orden pensado, con su historia al lado.', 'caaguazu-portal' ); ?></p>
				<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/recorridos/nuevo' ) ); ?>"><?php esc_html_e( 'Armar el primero', 'caaguazu-portal' ); ?></a>
			</div>
		<?php else : ?>
			<div class="promotur-list">
				<?php foreach ( $recorridos as $p ) :
					$estado  = PROMOTUR_Editorial::get_estado( $p->ID );
					$paradas = PROMOTUR_Recorridos::paradas( $p->ID );
					$dura    = (string) get_post_meta( $p->ID, PROMOTUR_Recorridos::META_DURACION, true );
					?>
					<a class="promotur-row" href="<?php echo esc_url( promotur_url( 'panel/recorridos/' . $p->ID ) ); ?>">
						<span class="promotur-row__main">
							<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ? get_the_title( $p ) : __( '(sin título)', 'caaguazu-portal' ) ); ?></span>
							<span class="promotur-row__meta">
								<?php
								/* translators: %d = cuántas paradas tiene el recorrido */
								echo esc_html( sprintf( _n( '%d parada', '%d paradas', count( $paradas ), 'caaguazu-portal' ), count( $paradas ) ) );
								if ( $dura ) {
									echo ' · ' . esc_html( $dura );
								}
								?>
							</span>
						</span>
						<span class="promotur-pill <?php echo esc_attr( PROMOTUR_Editorial::estado_class( $estado ) ); ?>"><?php echo esc_html( PROMOTUR_Editorial::estado_label( $estado ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php
	};

	include PROMOTUR_DIR . 'templates/shell.php';
	return;
}

/* ----------------------------------------------------------------------
 * El armador
 * -------------------------------------------------------------------- */
$post = $post_id ? get_post( $post_id ) : null;

if ( $post && PROMOTUR_Recorridos::CPT === $post->post_type ) {
	if ( ! PROMOTUR_Recorridos::es_prehecho( $post_id ) ) {
		wp_die( esc_html__( 'Ese recorrido lo armó una persona en la app: es suyo y no se edita desde el panel.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
	}
	if ( ! promotur_puede_editar_contenido( $post_id ) ) {
		wp_die( esc_html__( 'No podés editar este recorrido.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
	}
} else {
	$post    = null;
	$post_id = 0;
}

$estado     = $post_id ? PROMOTUR_Editorial::get_estado( $post_id ) : 'borrador';
$checklist  = PROMOTUR_Editorial::checklist( $post_id, 'recorrido' );
$feedback   = $post_id ? PROMOTUR_Editorial::get_feedback( $post_id ) : array();
$grupos     = PROMOTUR_Recorridos::fields();
$paradas    = $post_id ? PROMOTUR_Recorridos::paradas( $post_id ) : array();
$medios     = $post_id ? PROMOTUR_Recorridos::medios( $post_id ) : array();
$vinculados = $post_id ? PROMOTUR_Recorridos::articulos( $post_id ) : array();
$inventario = promotur_inventario();
$articulos  = get_posts( array(
	'post_type'      => PROMOTUR_Articulos::CPT,
	'post_status'    => 'publish',
	'posts_per_page' => 200,
	'orderby'        => 'title',
	'order'          => 'ASC',
) );

$page_title = $post_id ? __( 'Editar recorrido', 'caaguazu-portal' ) : __( 'Nuevo recorrido', 'caaguazu-portal' );

$body = function () use ( $post, $post_id, $estado, $checklist, $feedback, $grupos, $paradas, $medios, $vinculados, $inventario, $articulos ) {
	$titulo  = $post ? $post->post_title : '';
	$resumen = $post ? $post->post_excerpt : '';
	$cuerpo  = $post ? $post->post_content : '';

	/**
	 * Dibuja una fila de parada. Se usa para las guardadas y, como molde
	 * oculto, para las que se agregan sin recargar: una sola definición de
	 * cómo es una parada, que es lo que evita que el molde y las filas reales
	 * se separen con el tiempo.
	 */
	$fila = function ( $indice, $parada, $inventario, $es_molde = false ) {
		$ref   = $parada ? (int) $parada['ref_id'] : 0;
		$texto = $parada ? (string) $parada['texto'] : '';
		$mtipo = $parada ? (string) $parada['media_tipo'] : '';
		$murl  = $parada ? (string) $parada['media_url'] : '';
		// El molde usa __i__ en vez de un número: el JavaScript lo reemplaza
		// por el índice real al clonarlo.
		$i = $es_molde ? '__i__' : (string) $indice;
		?>
		<div class="promotur-parada" data-parada>
			<div class="promotur-parada__cabeza">
				<span class="promotur-parada__n" data-parada-n aria-hidden="true"></span>
				<label class="promotur-field promotur-parada__sitio">
					<span><?php esc_html_e( 'Sitio del inventario', 'caaguazu-portal' ); ?></span>
					<select name="paradas[<?php echo esc_attr( $i ); ?>][ref_id]" data-parada-sitio>
						<option value=""><?php esc_html_e( '— Elegí un sitio —', 'caaguazu-portal' ); ?></option>
						<?php foreach ( $inventario as $sitio ) : ?>
							<option value="<?php echo esc_attr( $sitio->ID ); ?>" <?php selected( $ref, (int) $sitio->ID ); ?>><?php echo esc_html( get_the_title( $sitio ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<span class="promotur-parada__mover">
					<button type="button" class="promotur-iconbtn" data-parada-subir aria-label="<?php esc_attr_e( 'Subir esta parada', 'caaguazu-portal' ); ?>">↑</button>
					<button type="button" class="promotur-iconbtn" data-parada-bajar aria-label="<?php esc_attr_e( 'Bajar esta parada', 'caaguazu-portal' ); ?>">↓</button>
					<button type="button" class="promotur-iconbtn" data-parada-quitar aria-label="<?php esc_attr_e( 'Quitar esta parada', 'caaguazu-portal' ); ?>">✕</button>
				</span>
			</div>

			<label class="promotur-field">
				<span><?php esc_html_e( 'Qué contar en esta parada', 'caaguazu-portal' ); ?></span>
				<textarea name="paradas[<?php echo esc_attr( $i ); ?>][texto]" rows="3" placeholder="<?php esc_attr_e( 'La historia, el dato curioso, por qué está acá y no en otro lado…', 'caaguazu-portal' ); ?>"><?php echo esc_textarea( $texto ); ?></textarea>
			</label>

			<div class="promotur-grid promotur-grid--2">
				<label class="promotur-field">
					<span><?php esc_html_e( 'Audio o video de la parada', 'caaguazu-portal' ); ?></span>
					<select name="paradas[<?php echo esc_attr( $i ); ?>][media_tipo]">
						<option value="audio" <?php selected( $mtipo, 'audio' ); ?>><?php esc_html_e( 'Audio', 'caaguazu-portal' ); ?></option>
						<option value="video" <?php selected( $mtipo, 'video' ); ?>><?php esc_html_e( 'Video', 'caaguazu-portal' ); ?></option>
					</select>
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Enlace (opcional)', 'caaguazu-portal' ); ?></span>
					<input type="url" name="paradas[<?php echo esc_attr( $i ); ?>][media_url]" value="<?php echo esc_attr( $murl ); ?>">
				</label>
			</div>
		</div>
		<?php
	};
	?>
	<div class="promotur-pagehead">
		<div>
			<a class="promotur-back" href="<?php echo esc_url( promotur_url( 'panel/recorridos' ) ); ?>">&larr; <?php esc_html_e( 'Volver a los recorridos', 'caaguazu-portal' ); ?></a>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Recorrido', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php echo esc_html( $titulo ? $titulo : __( 'Nuevo recorrido', 'caaguazu-portal' ) ); ?></h2>
		</div>
		<span class="promotur-pill <?php echo esc_attr( PROMOTUR_Editorial::estado_class( $estado ) ); ?>"><?php echo esc_html( PROMOTUR_Editorial::estado_label( $estado ) ); ?></span>
	</div>

	<?php if ( ! empty( $feedback ) ) : ?>
		<div class="promotur-card promotur-feedback">
			<h3 class="promotur-h3"><?php esc_html_e( 'Comentarios del revisor', 'caaguazu-portal' ); ?></h3>
			<?php foreach ( $feedback as $c ) : ?>
				<div class="promotur-feedback__item">
					<strong><?php echo esc_html( $c->comment_author ); ?></strong>
					<span class="promotur-row__meta"><?php echo esc_html( human_time_diff( strtotime( $c->comment_date_gmt ) ) ); ?></span>
					<p><?php echo esc_html( $c->comment_content ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( empty( $inventario ) ) : ?>
		<div class="promotur-notice promotur-notice--info">
			<?php esc_html_e( 'Todavía no hay fichas publicadas, así que no hay sitios para poner de paradas. Un recorrido se arma con lo que ya está en el inventario.', 'caaguazu-portal' ); ?>
		</div>
	<?php endif; ?>

	<div class="promotur-editor">
		<form class="promotur-form promotur-editor__form" data-editor-form data-tipo="recorrido">
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<input type="hidden" name="tipo" value="recorrido">

			<label class="promotur-field">
				<span><?php esc_html_e( 'Nombre del recorrido', 'caaguazu-portal' ); ?> <em>*</em></span>
				<input type="text" name="titulo" value="<?php echo esc_attr( $titulo ); ?>" data-check="titulo" required>
			</label>

			<label class="promotur-field">
				<span><?php esc_html_e( 'Resumen', 'caaguazu-portal' ); ?></span>
				<input type="text" name="entradilla" value="<?php echo esc_attr( $resumen ); ?>">
				<small class="promotur-ayuda"><?php esc_html_e( 'Una línea: la que se lee en la tarjeta de la app.', 'caaguazu-portal' ); ?></small>
			</label>

			<label class="promotur-field">
				<span><?php esc_html_e( 'Introducción', 'caaguazu-portal' ); ?> <em>*</em></span>
				<textarea name="descripcion" rows="6" data-check="descripcion"><?php echo esc_textarea( $cuerpo ); ?></textarea>
				<small class="promotur-ayuda"><?php esc_html_e( 'De qué va el paseo y por qué las paradas van en ese orden.', 'caaguazu-portal' ); ?></small>
			</label>

			<?php promotur_campos( $grupos, $post_id ); ?>

			<?php /* ---------- Paradas ---------- */ ?>
			<fieldset class="promotur-fieldset promotur-paradas"
			          data-paradas
			          data-paradas-max="<?php echo esc_attr( PROMOTUR_Recorridos::MAX_PARADAS ); ?>"
			          data-paradas-lleno="<?php esc_attr_e( 'Un recorrido lleva hasta nueve paradas: es el tope que acepta Google Maps para una ruta, y la app manda el recorrido ahí.', 'caaguazu-portal' ); ?>">
				<legend><?php esc_html_e( 'Paradas', 'caaguazu-portal' ); ?> <em>*</em></legend>
				<p class="promotur-muted">
					<?php
					/* translators: %d = tope de paradas */
					printf( esc_html__( 'Elegí los sitios, contá algo de cada uno y acomodalos en el orden del paseo. Hasta %d.', 'caaguazu-portal' ), (int) PROMOTUR_Recorridos::MAX_PARADAS );
					?>
				</p>

				<div data-paradas-lista data-check="paradas">
					<?php foreach ( $paradas as $indice => $parada ) {
						$fila( $indice, $parada, $inventario );
					} ?>
				</div>

				<div class="promotur-editor__actions">
					<button type="button" class="promotur-btn promotur-btn--ghost promotur-btn--small" data-parada-agregar><?php esc_html_e( '+ Agregar parada', 'caaguazu-portal' ); ?></button>
					<span class="promotur-form-msg" data-paradas-msg aria-live="polite"></span>
				</div>

				<template data-parada-molde>
					<?php $fila( 0, null, $inventario, true ); ?>
				</template>
			</fieldset>

			<?php /* ---------- Audios y videos del recorrido ---------- */ ?>
			<fieldset class="promotur-fieldset">
				<legend><?php esc_html_e( 'Audios y videos del recorrido', 'caaguazu-portal' ); ?></legend>
				<p class="promotur-muted"><?php esc_html_e( 'Los que acompañan al recorrido entero: la presentación en audio, el video de apertura. Los de cada parada se cargan arriba, en la parada.', 'caaguazu-portal' ); ?></p>
				<?php
				// Las guardadas, más tres filas en blanco: cargar de a tres es
				// lo que evita tener que guardar para poder sumar la siguiente.
				$filas_medios = $medios;
				for ( $i = 0; $i < 3; $i++ ) {
					$filas_medios[] = array( 'tipo' => 'audio', 'url' => '', 'titulo' => '' );
				}
				foreach ( $filas_medios as $n => $medio ) : ?>
					<div class="promotur-grid promotur-grid--3">
						<label class="promotur-field">
							<span><?php esc_html_e( 'Tipo', 'caaguazu-portal' ); ?></span>
							<select name="medios[<?php echo esc_attr( $n ); ?>][tipo]">
								<option value="audio" <?php selected( $medio['tipo'], 'audio' ); ?>><?php esc_html_e( 'Audio', 'caaguazu-portal' ); ?></option>
								<option value="video" <?php selected( $medio['tipo'], 'video' ); ?>><?php esc_html_e( 'Video', 'caaguazu-portal' ); ?></option>
							</select>
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'Enlace', 'caaguazu-portal' ); ?></span>
							<input type="url" name="medios[<?php echo esc_attr( $n ); ?>][url]" value="<?php echo esc_attr( $medio['url'] ); ?>">
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'Título', 'caaguazu-portal' ); ?></span>
							<input type="text" name="medios[<?php echo esc_attr( $n ); ?>][titulo]" value="<?php echo esc_attr( $medio['titulo'] ); ?>">
						</label>
					</div>
				<?php endforeach; ?>
			</fieldset>

			<?php /* ---------- Artículos vinculados ---------- */ ?>
			<fieldset class="promotur-fieldset">
				<legend><?php esc_html_e( 'Artículos vinculados', 'caaguazu-portal' ); ?></legend>
				<?php if ( empty( $articulos ) ) : ?>
					<p class="promotur-muted"><?php esc_html_e( 'Todavía no hay artículos publicados para vincular.', 'caaguazu-portal' ); ?></p>
				<?php else : ?>
					<p class="promotur-muted"><?php esc_html_e( 'Las notas ya escritas que cuentan más sobre este recorrido. La app las ofrece al final.', 'caaguazu-portal' ); ?></p>
					<div class="promotur-vinculos">
						<?php foreach ( $articulos as $art ) : ?>
							<label class="promotur-check">
								<input type="checkbox" name="articulos[]" value="<?php echo esc_attr( $art->ID ); ?>" <?php checked( in_array( (int) $art->ID, $vinculados, true ) ); ?>>
								<?php echo esc_html( get_the_title( $art ) ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</fieldset>

			<div class="promotur-editor__actions">
				<button type="button" class="promotur-btn promotur-btn--ghost" data-action="save"><?php esc_html_e( 'Guardar borrador', 'caaguazu-portal' ); ?></button>
				<button type="button" class="promotur-btn promotur-btn--primary" data-action="submit"><?php esc_html_e( 'Enviar a revisión', 'caaguazu-portal' ); ?></button>
				<span class="promotur-form-msg" data-form-msg aria-live="polite"></span>
			</div>
		</form>

		<aside class="promotur-editor__side">
			<div class="promotur-card promotur-checklist" data-checklist>
				<h3 class="promotur-h3"><?php esc_html_e( 'Checklist de mínimos', 'caaguazu-portal' ); ?></h3>
				<p class="promotur-muted"><?php esc_html_e( 'Un recorrido se envía a revisión cuando tiene esto. Lo aprueba el staff, igual que una ficha.', 'caaguazu-portal' ); ?></p>
				<ul>
					<?php foreach ( $checklist as $item ) : ?>
						<li class="promotur-checklist__item<?php echo $item['done'] ? ' is-done' : ''; ?>" data-checklist-key="<?php echo esc_attr( $item['key'] ); ?>">
							<span class="promotur-checklist__box" aria-hidden="true"></span>
							<?php echo esc_html( $item['label'] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php promotur_acciones_de_estado( $post_id ); ?>
		</aside>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
