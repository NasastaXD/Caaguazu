<?php
/**
 * Artículos: la lista, y el editor de uno.
 *
 * Una sola sección para las dos cosas, como la cola de revisión:
 *   /turismo-panel/articulos          la lista
 *   /turismo-panel/articulos/nuevo    uno en blanco
 *   /turismo-panel/articulos/<id>     ese artículo
 *
 * El editor sigue el orden en que se escribe una nota —cabeza, foto, cuerpo,
 * fuentes— y no el orden en que están los campos en la base. Es la misma
 * decisión que la ficha guiada por grupos: quien carga no tiene que saber cómo
 * está guardado nada.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$segmento = isset( $promotur_id ) ? (string) $promotur_id : '';
$es_nuevo = ( 'nuevo' === $segmento );
$post_id  = ( ! $es_nuevo && $segmento ) ? (int) $segmento : 0;

/* ----------------------------------------------------------------------
 * Lista
 * -------------------------------------------------------------------- */
if ( ! $es_nuevo && ! $post_id ) {

	// Quien revisa ve todos los artículos; el resto, los suyos. Es el mismo
	// criterio que usa Mis contenidos, y el mismo que aplica el guard del
	// editor: la lista no muestra nada que después no se pueda abrir.
	$args = array(
		'post_type'      => PROMOTUR_Articulos::CPT,
		'post_status'    => 'any',
		'posts_per_page' => 100,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	);
	if ( ! promotur_can( 'promotur_review_content' ) ) {
		$args['meta_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'key'   => PROMOTUR_Articulos::OWNER_META,
			'value' => caaguazu_account_id(),
		) );
	}
	$articulos = get_posts( $args );

	$page_title = __( 'Artículos', 'caaguazu-portal' );
	$body = function () use ( $articulos ) {
		?>
		<div class="promotur-pagehead">
			<div>
				<div class="promotur-eyebrow"><?php esc_html_e( 'Redacción', 'caaguazu-portal' ); ?></div>
				<h2 class="promotur-h2"><?php esc_html_e( 'Artículos', 'caaguazu-portal' ); ?></h2>
			</div>
			<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/articulos/nuevo' ) ); ?>"><?php esc_html_e( '+ Nuevo artículo', 'caaguazu-portal' ); ?></a>
		</div>

		<?php if ( empty( $articulos ) ) : ?>
			<div class="promotur-card promotur-empty-box">
				<p><?php esc_html_e( 'Todavía no hay artículos escritos. Son las notas, historias y datos curiosos que la app muestra al lado de los lugares.', 'caaguazu-portal' ); ?></p>
				<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel/articulos/nuevo' ) ); ?>"><?php esc_html_e( 'Escribir el primero', 'caaguazu-portal' ); ?></a>
			</div>
		<?php else : ?>
			<div class="promotur-list">
				<?php foreach ( $articulos as $p ) :
					$estado  = PROMOTUR_Editorial::get_estado( $p->ID );
					$autores = PROMOTUR_Articulos::autores( $p->ID );
					?>
					<a class="promotur-row" href="<?php echo esc_url( promotur_url( 'panel/articulos/' . $p->ID ) ); ?>">
						<span class="promotur-row__main">
							<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ? get_the_title( $p ) : __( '(sin título)', 'caaguazu-portal' ) ); ?></span>
							<span class="promotur-row__meta">
								<?php
								echo esc_html( $autores ? implode( ', ', $autores ) : __( 'sin firma', 'caaguazu-portal' ) );
								echo ' · ' . esc_html( get_the_modified_date( '', $p ) );
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
 * Editor
 * -------------------------------------------------------------------- */
$post = $post_id ? get_post( $post_id ) : null;

if ( $post && PROMOTUR_Articulos::CPT === $post->post_type ) {
	if ( ! promotur_puede_editar_contenido( $post_id ) ) {
		wp_die( esc_html__( 'No podés editar este artículo.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
	}
} else {
	$post    = null;
	$post_id = 0;
}

$estado    = $post_id ? PROMOTUR_Editorial::get_estado( $post_id ) : 'borrador';
$checklist = PROMOTUR_Editorial::checklist( $post_id, 'articulo' );
$feedback  = $post_id ? PROMOTUR_Editorial::get_feedback( $post_id ) : array();
$grupos    = PROMOTUR_Articulos::fields();

$page_title = $post_id ? __( 'Editar artículo', 'caaguazu-portal' ) : __( 'Nuevo artículo', 'caaguazu-portal' );

$body = function () use ( $post, $post_id, $estado, $checklist, $feedback, $grupos ) {
	$titulo     = $post ? $post->post_title : '';
	$entradilla = $post ? $post->post_excerpt : '';
	$cuerpo     = $post ? $post->post_content : '';
	?>
	<div class="promotur-pagehead">
		<div>
			<a class="promotur-back" href="<?php echo esc_url( promotur_url( 'panel/articulos' ) ); ?>">&larr; <?php esc_html_e( 'Volver a los artículos', 'caaguazu-portal' ); ?></a>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Artículo', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php echo esc_html( $titulo ? $titulo : __( 'Nuevo artículo', 'caaguazu-portal' ) ); ?></h2>
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

	<div class="promotur-editor">
		<form class="promotur-form promotur-editor__form" data-editor-form data-tipo="articulo">
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<input type="hidden" name="tipo" value="articulo">

			<?php
			// La cabeza va antes que el título porque así se lee la nota: el
			// ante título es la primera línea que ve alguien en la app.
			$cabeza = $grupos['cabeza'];
			?>
			<fieldset class="promotur-fieldset">
				<legend><?php echo esc_html( $cabeza['label'] ); ?></legend>
				<div class="promotur-grid promotur-grid--2">
					<?php promotur_campo( '_articulo_antetitulo', $cabeza['fields']['_articulo_antetitulo'], $post_id ? get_post_meta( $post_id, '_articulo_antetitulo', true ) : '' ); ?>
				</div>

				<label class="promotur-field">
					<span><?php esc_html_e( 'Título', 'caaguazu-portal' ); ?> <em>*</em></span>
					<input type="text" name="titulo" value="<?php echo esc_attr( $titulo ); ?>" data-check="titulo" required>
				</label>

				<div class="promotur-grid promotur-grid--2">
					<?php
					promotur_campo( '_articulo_subtitulo', $cabeza['fields']['_articulo_subtitulo'], $post_id ? get_post_meta( $post_id, '_articulo_subtitulo', true ) : '' );
					promotur_campo( '_articulo_autores', $cabeza['fields']['_articulo_autores'], $post_id ? get_post_meta( $post_id, '_articulo_autores', true ) : '' );
					?>
				</div>
			</fieldset>

			<?php
			// Foto de portada, con su pie.
			$foto = $grupos['foto'];
			?>
			<fieldset class="promotur-fieldset">
				<legend><?php echo esc_html( $foto['label'] ); ?></legend>
				<div class="promotur-grid promotur-grid--2">
					<?php foreach ( $foto['fields'] as $key => $def ) {
						promotur_campo( $key, $def, $post_id ? get_post_meta( $post_id, $key, true ) : '' );
					} ?>
				</div>
			</fieldset>

			<fieldset class="promotur-fieldset">
				<legend><?php esc_html_e( 'Texto', 'caaguazu-portal' ); ?></legend>

				<label class="promotur-field">
					<span><?php esc_html_e( 'Entradilla', 'caaguazu-portal' ); ?> <em>*</em></span>
					<textarea name="entradilla" rows="3" data-check="entradilla"><?php echo esc_textarea( $entradilla ); ?></textarea>
					<small class="promotur-ayuda"><?php esc_html_e( 'El párrafo de arranque: lo que se lee en la tarjeta y decide si alguien entra o no.', 'caaguazu-portal' ); ?></small>
				</label>

				<label class="promotur-field">
					<span><?php esc_html_e( 'Cuerpo', 'caaguazu-portal' ); ?> <em>*</em></span>
					<textarea name="descripcion" rows="16" data-check="descripcion"><?php echo esc_textarea( $cuerpo ); ?></textarea>
					<small class="promotur-ayuda"><?php esc_html_e( 'Un párrafo por renglón en blanco. El público no lee bloques largos: cortá seguido.', 'caaguazu-portal' ); ?></small>
				</label>
			</fieldset>

			<fieldset class="promotur-fieldset">
				<legend><?php esc_html_e( 'Clasificación', 'caaguazu-portal' ); ?></legend>
				<div class="promotur-grid promotur-grid--2">
					<?php promotur_select_taxonomia( 'promotur_categoria', $post_id, 'categoria', __( 'Categoría', 'caaguazu-portal' ) ); ?>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Etiquetas', 'caaguazu-portal' ); ?></span>
						<input type="text" name="etiquetas" value="<?php echo esc_attr( promotur_etiquetas_texto( $post_id ) ); ?>">
						<small class="promotur-ayuda"><?php esc_html_e( 'Separadas por comas. Son las mismas etiquetas que usan las fichas: así una nota y un lugar se encuentran entre sí.', 'caaguazu-portal' ); ?></small>
					</label>
				</div>
			</fieldset>

			<?php
			$cierre = $grupos['cierre'];
			?>
			<fieldset class="promotur-fieldset">
				<legend><?php echo esc_html( $cierre['label'] ); ?></legend>
				<?php foreach ( $cierre['fields'] as $key => $def ) {
					promotur_campo( $key, $def, $post_id ? get_post_meta( $post_id, $key, true ) : '' );
				} ?>
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
				<p class="promotur-muted"><?php esc_html_e( 'Un artículo se envía a revisión cuando tiene esto. Lo aprueba el staff, igual que una ficha.', 'caaguazu-portal' ); ?></p>
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
