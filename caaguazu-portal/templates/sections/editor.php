<?php
/**
 * Editor de ficha turística: campos estructurados + checklist en vivo + medios.
 * $promotur_id = id de la ficha a editar (o null = nueva).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = isset( $promotur_id ) ? (int) $promotur_id : 0;
$post    = $post_id ? get_post( $post_id ) : null;

// Si edita una ficha ajena sin ser revisor/admin → bloquear. El dueño real
// se resuelve por el meta de cuenta, no por post_author (que en toda ficha
// creada desde el panel apunta al usuario de servicio).
if ( $post && PROMOTUR_Destinos::CPT === $post->post_type ) {
	if ( ! promotur_puede_editar_contenido( $post_id ) ) {
		wp_die( esc_html__( 'No podés editar esta ficha.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
	}
} elseif ( $post_id ) {
	$post    = null;
	$post_id = 0;
}

$estado    = $post_id ? PROMOTUR_Editorial::get_estado( $post_id ) : 'borrador';
$checklist = PROMOTUR_Editorial::checklist( $post_id, 'destino' );
$feedback  = $post_id ? PROMOTUR_Editorial::get_feedback( $post_id ) : array();
$groups    = PROMOTUR_Destinos::fields();

$page_title = $post_id ? __( 'Editar ficha', 'caaguazu-portal' ) : __( 'Nueva ficha', 'caaguazu-portal' );

$body = function () use ( $post, $post_id, $estado, $checklist, $feedback, $groups ) {
	$title   = $post ? $post->post_title : '';
	$content = $post ? $post->post_content : '';
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Ficha del destino', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php echo esc_html( $post_id ? $post->post_title : __( 'Nueva ficha', 'caaguazu-portal' ) ); ?></h2>
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
		<form class="promotur-form promotur-editor__form" data-editor-form data-tipo="destino">
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<input type="hidden" name="tipo" value="destino">

			<label class="promotur-field">
				<span><?php esc_html_e( 'Nombre del destino', 'caaguazu-portal' ); ?> <em>*</em></span>
				<input type="text" name="titulo" value="<?php echo esc_attr( $title ); ?>" data-check="titulo" required>
			</label>

			<label class="promotur-field">
				<span><?php esc_html_e( 'Descripción', 'caaguazu-portal' ); ?> <em>*</em></span>
				<textarea name="descripcion" rows="5" data-check="descripcion"><?php echo esc_textarea( $content ); ?></textarea>
			</label>

			<fieldset class="promotur-fieldset">
				<legend><?php esc_html_e( 'Clasificación', 'caaguazu-portal' ); ?></legend>
				<div class="promotur-grid promotur-grid--2">
					<?php
					promotur_select_taxonomia( 'promotur_categoria', $post_id, 'categoria', __( 'Categoría', 'caaguazu-portal' ) );
					promotur_select_taxonomia( 'promotur_zona', $post_id, 'zona', __( 'Zona', 'caaguazu-portal' ) );
					?>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Etiquetas', 'caaguazu-portal' ); ?></span>
						<input type="text" name="etiquetas" value="<?php echo esc_attr( promotur_etiquetas_texto( $post_id ) ); ?>">
						<small class="promotur-ayuda"><?php esc_html_e( 'Separadas por comas: «con niños», «gratis», «llega colectivo».', 'caaguazu-portal' ); ?></small>
					</label>
				</div>
			</fieldset>

			<?php
			foreach ( $groups as $gkey => $group ) : ?>
				<fieldset class="promotur-fieldset">
					<legend><?php echo esc_html( $group['label'] ); ?></legend>
					<div class="promotur-grid promotur-grid--2">
						<?php foreach ( $group['fields'] as $key => $def ) {
							promotur_campo( $key, $def, $post_id ? get_post_meta( $post_id, $key, true ) : '' );
						} ?>
					</div>
					<?php if ( 'ubicacion' === $gkey ) : ?>
						<div class="promotur-editor__actions">
							<button type="button" class="promotur-btn promotur-btn--ghost promotur-btn--small" data-geolocate><?php esc_html_e( '📍 Usar mi ubicación actual', 'caaguazu-portal' ); ?></button>
						</div>
					<?php endif; ?>
				</fieldset>
			<?php endforeach; ?>

			<div class="promotur-editor__actions">
				<button type="button" class="promotur-btn promotur-btn--ghost" data-action="save"><?php esc_html_e( 'Guardar borrador', 'caaguazu-portal' ); ?></button>
				<button type="button" class="promotur-btn promotur-btn--primary" data-action="submit"><?php esc_html_e( 'Enviar a revisión', 'caaguazu-portal' ); ?></button>
				<span class="promotur-form-msg" data-form-msg aria-live="polite"></span>
			</div>
		</form>

		<aside class="promotur-editor__side">
			<div class="promotur-card promotur-checklist" data-checklist>
				<h3 class="promotur-h3"><?php esc_html_e( 'Checklist de mínimos', 'caaguazu-portal' ); ?></h3>
				<p class="promotur-muted"><?php esc_html_e( 'Completá estos puntos antes de enviar la ficha a revisión.', 'caaguazu-portal' ); ?></p>
				<ul>
					<?php foreach ( $checklist as $item ) : ?>
						<li class="promotur-checklist__item<?php echo $item['done'] ? ' is-done' : ''; ?>" data-checklist-key="<?php echo esc_attr( $item['key'] ); ?>">
							<span class="promotur-checklist__box" aria-hidden="true"></span>
							<?php echo esc_html( $item['label'] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</aside>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
