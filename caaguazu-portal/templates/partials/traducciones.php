<?php
/**
 * El bloque «Idiomas» de los tres editores.
 *
 * Va aparte del formulario del editor, y no adentro, por dos motivos: el
 * editor guarda por JavaScript contra `datos/save_contenido` y meterle un
 * juego de campos por idioma lo obligaría a saber de traducciones; y un
 * formulario anidado dentro de otro no existe en HTML — el navegador cierra el
 * de afuera y lo que quede abajo se envía solo.
 *
 * Sólo se dibuja con la pieza ya creada (no se puede traducir lo que todavía
 * no se escribió) y sólo para quien tiene `promotur_traducir`.
 *
 * Espera en el ámbito:
 *   $post_id  int
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $post_id ) || ! promotur_can( PROMOTUR_Traducciones::CAP ) ) {
	return;
}

$campos_i18n = PROMOTUR_Traducciones::campos_de( $post_id );
if ( ! $campos_i18n ) {
	return;
}

$estados_i18n = PROMOTUR_Traducciones::estado( $post_id );
?>
<section class="promotur-card promotur-i18n" id="idiomas">
	<div class="promotur-i18n__head">
		<div>
			<h3 class="promotur-h3"><?php esc_html_e( 'Idiomas', 'caaguazu-portal' ); ?></h3>
			<p class="promotur-muted">
				<?php esc_html_e( 'El castellano es el original y se edita arriba. Acá van las versiones en otro idioma que muestra la app. Lo que quede sin traducir se sirve en castellano.', 'caaguazu-portal' ); ?>
			</p>
		</div>
		<ul class="promotur-i18n__estados">
			<?php foreach ( $estados_i18n as $e ) : ?>
				<li>
					<strong><?php echo esc_html( $e['nombre'] ); ?></strong>
					<span class="promotur-pill <?php echo esc_attr( PROMOTUR_Traducciones::estado_class( $e['estado'] ) ); ?>">
						<?php echo esc_html( $e['label'] ); ?>
					</span>
					<span class="promotur-row__meta"><?php echo esc_html( $e['hechos'] . '/' . $e['total'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php
	/*
	 * Bajar y subir el archivo. Es el camino para traducir una pieza entera de
	 * una sola vez —a mano, con un traductor o con un modelo— en vez de cuadro
	 * por cuadro. El archivo lleva adentro sus propias instrucciones, así que
	 * se le puede pasar a cualquiera sin explicarle nada.
	 */
	?>
	<div class="promotur-i18n__archivo">
		<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'traduccion_bajar' ) ); ?>">
			<?php PROMOTUR_Acciones::campos(); ?>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<button type="submit" class="promotur-btn promotur-btn--ghost">
				<?php esc_html_e( 'Bajar para traducir', 'caaguazu-portal' ); ?>
			</button>
			<small class="promotur-ayuda">
				<?php esc_html_e( 'Un archivo .json con todos los textos de esta pieza y las instrucciones adentro.', 'caaguazu-portal' ); ?>
			</small>
		</form>

		<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'traduccion_subir' ) ); ?>" enctype="multipart/form-data">
			<?php PROMOTUR_Acciones::campos(); ?>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<label class="promotur-field">
				<span><?php esc_html_e( 'Subir traducciones', 'caaguazu-portal' ); ?></span>
				<input type="file" name="archivo" accept=".json,application/json" required>
			</label>
			<button type="submit" class="promotur-btn promotur-btn--ghost">
				<?php esc_html_e( 'Subir el archivo', 'caaguazu-portal' ); ?>
			</button>
			<small class="promotur-ayuda">
				<?php esc_html_e( 'El mismo archivo que bajaste, con las traducciones escritas. Los idiomas que vengan vacíos no se tocan.', 'caaguazu-portal' ); ?>
			</small>
		</form>
	</div>

	<?php foreach ( $estados_i18n as $locale => $e ) : ?>
		<?php $guardado = PROMOTUR_Traducciones::leer( $post_id, $locale ); ?>
		<details class="promotur-i18n__idioma"<?php echo 'sin_empezar' === $e['estado'] ? '' : ' open'; ?>>
			<summary>
				<span><?php echo esc_html( $e['nombre'] ); ?></span>
				<span class="promotur-pill <?php echo esc_attr( PROMOTUR_Traducciones::estado_class( $e['estado'] ) ); ?>">
					<?php echo esc_html( $e['label'] ); ?>
				</span>
			</summary>

			<?php if ( 'desactualizada' === $e['estado'] ) : ?>
				<p class="promotur-notice promotur-notice--info">
					<span><?php esc_html_e( 'Esta traducción está completa, pero el castellano se editó después. Repasala: la app está mostrando texto que ya no dice lo mismo que el original.', 'caaguazu-portal' ); ?></span>
				</p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'traduccion_guardar' ) ); ?>" class="promotur-form">
				<?php PROMOTUR_Acciones::campos(); ?>
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
				<input type="hidden" name="idioma" value="<?php echo esc_attr( $locale ); ?>">

				<?php foreach ( $campos_i18n as $clave => $def ) : ?>
					<?php
					$original = PROMOTUR_Traducciones::original( $post_id, $clave, $def );
					if ( '' === trim( $original ) ) {
						// Sin original no hay nada que traducir, y un cuadro
						// vacío al lado de otro vacío sólo ocupa lugar.
						continue;
					}
					$valor   = isset( $guardado[ $clave ] ) ? (string) $guardado[ $clave ] : '';
					$parrafo = ( isset( $def['formato'] ) && 'parrafos' === $def['formato'] );
					?>
					<div class="promotur-i18n__campo">
						<div class="promotur-i18n__original">
							<span class="promotur-i18n__etiqueta"><?php echo esc_html( $def['label'] ); ?></span>
							<p><?php echo nl2br( esc_html( $original ) ); ?></p>
						</div>
						<label class="promotur-field">
							<span><?php echo esc_html( $e['nombre'] ); ?></span>
							<?php if ( $parrafo ) : ?>
								<textarea name="t[<?php echo esc_attr( $clave ); ?>]" rows="6"><?php echo esc_textarea( $valor ); ?></textarea>
							<?php else : ?>
								<input type="text" name="t[<?php echo esc_attr( $clave ); ?>]" value="<?php echo esc_attr( $valor ); ?>">
							<?php endif; ?>
						</label>
					</div>
				<?php endforeach; ?>

				<div class="promotur-i18n__acciones">
					<button type="submit" class="promotur-btn promotur-btn--primary">
						<?php
						printf(
							/* translators: %s = nombre del idioma */
							esc_html__( 'Guardar el %s', 'caaguazu-portal' ),
							esc_html( $e['nombre'] )
						);
						?>
					</button>
					<small class="promotur-ayuda">
						<?php esc_html_e( 'Un cuadro vacío deja ese campo sin traducir, y la app lo muestra en castellano.', 'caaguazu-portal' ); ?>
					</small>
				</div>
			</form>
		</details>
	<?php endforeach; ?>
</section>
