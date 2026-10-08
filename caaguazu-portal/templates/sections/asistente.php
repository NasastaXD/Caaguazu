<?php
/**
 * Asistente: lo que sabe además de lo publicado. Lo editan los profesores.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$disponible = PROMOTUR_Asistente_Panel::disponible();
$texto      = PROMOTUR_Asistente_Panel::conocimiento();

$page_title = __( 'Asistente', 'caaguazu-portal' );
$body = function () use ( $disponible, $texto ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Contenido', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Asistente', 'caaguazu-portal' ); ?></h2>
		</div>
	</div>

	<?php if ( ! $disponible ) : ?>
		<div class="promotur-card promotur-empty-box">
			<h2 class="promotur-h2"><?php esc_html_e( 'El asistente no está disponible', 'caaguazu-portal' ); ?></h2>
			<p class="promotur-muted"><?php esc_html_e( 'Hace falta la API de la app 0.9.1 o posterior activa en este sitio.', 'caaguazu-portal' ); ?></p>
		</div>
	<?php else : ?>
		<div class="promotur-card">
			<div class="promotur-card__head">
				<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Conocimiento', 'caaguazu-portal' ); ?></span>
			</div>
			<p class="promotur-muted"><?php esc_html_e( 'Lo que el asistente sabe además de lo publicado en el panel: horarios, consejos, contactos. Escribí datos, no instrucciones.', 'caaguazu-portal' ); ?></p>

			<form class="promotur-form" method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'save_asistente' ) ); ?>">
				<?php PROMOTUR_Acciones::campos(); ?>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Lo que sabe el asistente', 'caaguazu-portal' ); ?></span>
					<textarea name="conocimiento" rows="16"><?php echo esc_textarea( $texto ); ?></textarea>
				</label>
				<p class="promotur-muted">
					<?php
					/* translators: %s: cantidad de caracteres */
					printf( esc_html__( 'Caracteres: %s. Si el presupuesto de contexto no alcanza, el asistente recorta esto después del catálogo.', 'caaguazu-portal' ), esc_html( number_format_i18n( mb_strlen( $texto ) ) ) );
					?>
				</p>
				<div>
					<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar conocimiento', 'caaguazu-portal' ); ?></button>
				</div>
			</form>
		</div>
	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
