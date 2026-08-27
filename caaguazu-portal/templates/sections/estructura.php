<?php
/**
 * Categorías, zonas y etiquetas.
 *
 * Antes esta pantalla mandaba a `edit-tags.php` de WordPress. Ahora se edita
 * acá: cada grupo lista lo que hay, con cuántas fichas lo usan, y deja crear,
 * renombrar y borrar. Renombrar en su lugar —sin pantalla aparte— porque son
 * nombres de una línea y abrir otra pantalla para cambiar una palabra es
 * desproporcionado.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$grupos = PROMOTUR_Estructura::grupos();
$puede  = promotur_can( PROMOTUR_Estructura::CAP );

$page_title = __( 'Estructura', 'caaguazu-portal' );
$body = function () use ( $grupos, $puede ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Organización', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Estructura del sitio', 'caaguazu-portal' ); ?></h2>
		</div>
	</div>

	<?php if ( ! $puede ) : ?>
		<div class="promotur-card promotur-empty-box">
			<p><?php esc_html_e( 'Esto lo organiza un Promotor. Podés ver cómo está armado, pero no cambiarlo.', 'caaguazu-portal' ); ?></p>
		</div>
	<?php endif; ?>

	<?php foreach ( $grupos as $tax => $grupo ) : ?>
		<?php $terminos = PROMOTUR_Estructura::terminos( $tax ); ?>

		<h3 class="promotur-h3 promotur-mt"><?php echo esc_html( $grupo['titulo'] ); ?></h3>
		<p class="promotur-muted"><?php echo esc_html( $grupo['ayuda'] ); ?></p>

		<div class="promotur-card">
			<?php if ( empty( $terminos ) ) : ?>
				<p class="promotur-muted"><?php esc_html_e( 'Todavía no hay ninguna.', 'caaguazu-portal' ); ?></p>
			<?php else : ?>
				<div class="promotur-list">
					<?php foreach ( $terminos as $termino ) : ?>
						<div class="promotur-termino">
							<?php if ( $puede ) : ?>
								<form class="promotur-termino__nombre" method="post"
									  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'estructura_guardar' ) ); ?>">
									<?php PROMOTUR_Acciones::campos(); ?>
									<input type="hidden" name="taxonomia" value="<?php echo esc_attr( $tax ); ?>">
									<input type="hidden" name="term_id" value="<?php echo esc_attr( $termino->term_id ); ?>">
									<input type="text" name="nombre" value="<?php echo esc_attr( $termino->name ); ?>"
										   aria-label="<?php echo esc_attr( $grupo['singular'] ); ?>">
									<button type="submit" class="promotur-btn promotur-btn--ghost promotur-btn--small"><?php esc_html_e( 'Guardar', 'caaguazu-portal' ); ?></button>
								</form>
							<?php else : ?>
								<span class="promotur-termino__nombre"><?php echo esc_html( $termino->name ); ?></span>
							<?php endif; ?>

							<span class="promotur-termino__uso">
								<span class="promotur-pill is-muted">
									<?php
									printf(
										/* translators: %d = cuántas fichas usan esto */
										esc_html( _n( '%d ficha', '%d fichas', (int) $termino->count, 'caaguazu-portal' ) ),
										(int) $termino->count
									);
									?>
								</span>
								<?php if ( $puede && 0 === (int) $termino->count ) : ?>
									<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'estructura_borrar' ) ); ?>"
										  data-confirmar="<?php esc_attr_e( 'Se borra y no se puede deshacer. ¿Seguimos?', 'caaguazu-portal' ); ?>">
										<?php PROMOTUR_Acciones::campos(); ?>
										<input type="hidden" name="taxonomia" value="<?php echo esc_attr( $tax ); ?>">
										<input type="hidden" name="term_id" value="<?php echo esc_attr( $termino->term_id ); ?>">
										<button type="submit" class="promotur-btn promotur-btn--peligro promotur-btn--small"><?php esc_html_e( 'Borrar', 'caaguazu-portal' ); ?></button>
									</form>
								<?php endif; ?>
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $puede ) : ?>
				<form class="promotur-inline-form promotur-mt" method="post"
					  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'estructura_crear' ) ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="taxonomia" value="<?php echo esc_attr( $tax ); ?>">
					<input type="text" name="nombre" required
						   placeholder="<?php echo esc_attr( $grupo['singular'] ); ?>"
						   aria-label="<?php echo esc_attr( $grupo['singular'] ); ?>">
					<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Agregar', 'caaguazu-portal' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( $puede && promotur_app_api_activa() ) : ?>
		<p class="promotur-muted promotur-mt">
			<a class="promotur-nota-enlace" href="<?php echo esc_url( promotur_url( 'panel/app' ) ); ?>"><?php esc_html_e( 'El ícono y el color con que la app muestra cada categoría se eligen en App →', 'caaguazu-portal' ); ?></a>
		</p>
	<?php endif; ?>
	<?php
};
include PROMOTUR_DIR . 'templates/shell.php';
