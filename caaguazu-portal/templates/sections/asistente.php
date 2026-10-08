<?php
/**
 * Asistente: lo que sabe, cómo habla, cuánto recuerda, de qué fuentes saca sus
 * respuestas, y una prueba para conversar con él. Lo editan los profesores.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$disponible = PROMOTUR_Asistente_Panel::disponible();
$pestana    = PROMOTUR_Asistente_Panel::pestana_actual();

$etiquetas = array(
	'conocimiento' => __( 'Conocimiento', 'caaguazu-portal' ),
	'personalidad' => __( 'Personalidad', 'caaguazu-portal' ),
	'memoria'      => __( 'Memoria', 'caaguazu-portal' ),
	'fuentes'      => __( 'Fuentes', 'caaguazu-portal' ),
	'probar'       => __( 'Probar', 'caaguazu-portal' ),
);
$nombres_fuentes = array(
	'lugares'    => __( 'Lugares', 'caaguazu-portal' ),
	'eventos'    => __( 'Eventos que vienen', 'caaguazu-portal' ),
	'recorridos' => __( 'Recorridos', 'caaguazu-portal' ),
	'articulos'  => __( 'Artículos', 'caaguazu-portal' ),
);

$page_title = __( 'Asistente', 'caaguazu-portal' );
$body = function () use ( $disponible, $pestana, $etiquetas, $nombres_fuentes ) {
	$accion = PROMOTUR_Acciones::url( 'save_asistente' );
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Portal', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Asistente', 'caaguazu-portal' ); ?></h2>
		</div>
	</div>

	<?php if ( ! $disponible ) : ?>
		<div class="promotur-card promotur-empty-box">
			<h2 class="promotur-h2"><?php esc_html_e( 'El asistente no está disponible', 'caaguazu-portal' ); ?></h2>
			<p class="promotur-muted"><?php esc_html_e( 'Hace falta la API de la app 0.10.0 o posterior activa en este sitio.', 'caaguazu-portal' ); ?></p>
		</div>
	<?php else : ?>

		<nav class="promotur-seg promotur-seg--ancho promotur-mb" aria-label="<?php esc_attr_e( 'Partes del asistente', 'caaguazu-portal' ); ?>">
			<?php foreach ( $etiquetas as $clave => $texto ) : ?>
				<a class="promotur-seg__item<?php echo $clave === $pestana ? ' is-active' : ''; ?>"
				   href="<?php echo esc_url( add_query_arg( 'pestana', $clave, promotur_url( 'panel/asistente' ) ) ); ?>"
				   <?php echo $clave === $pestana ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $texto ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php if ( 'conocimiento' === $pestana ) : ?>
			<div class="promotur-card">
				<div class="promotur-card__head">
					<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'Conocimiento', 'caaguazu-portal' ); ?></span>
				</div>
				<p class="promotur-muted"><?php esc_html_e( 'Lo que el asistente sabe además de lo publicado en el panel: horarios, consejos, contactos. Escribí datos, no instrucciones.', 'caaguazu-portal' ); ?></p>
				<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="bloque" value="conocimiento">
					<input type="hidden" name="pestana" value="conocimiento">
					<label class="promotur-field">
						<span><?php esc_html_e( 'Lo que sabe el asistente', 'caaguazu-portal' ); ?></span>
						<textarea name="conocimiento" rows="16"><?php echo esc_textarea( PROMOTUR_Asistente_Panel::conocimiento() ); ?></textarea>
					</label>
					<p class="promotur-muted">
						<?php
						/* translators: %s: cantidad de caracteres */
						printf( esc_html__( 'Caracteres: %s. Si el presupuesto de contexto no alcanza, el asistente recorta esto después del catálogo.', 'caaguazu-portal' ), esc_html( number_format_i18n( mb_strlen( PROMOTUR_Asistente_Panel::conocimiento() ) ) ) );
						?>
					</p>
					<div>
						<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar conocimiento', 'caaguazu-portal' ); ?></button>
					</div>
				</form>
			</div>

		<?php elseif ( 'personalidad' === $pestana ) : ?>
			<div class="promotur-card">
				<div class="promotur-card__head">
					<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'Personalidad', 'caaguazu-portal' ); ?></span>
				</div>
				<p class="promotur-muted"><?php esc_html_e( 'Cómo habla el asistente: el tono, qué hace y qué no. Si quitás las reglas de qué sabe y qué no sabe, puede dejar de decir cuándo no tiene un dato.', 'caaguazu-portal' ); ?></p>
				<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="bloque" value="personalidad">
					<input type="hidden" name="pestana" value="personalidad">
					<label class="promotur-field">
						<span><?php esc_html_e( 'Personalidad', 'caaguazu-portal' ); ?></span>
						<textarea name="persona" rows="18"><?php echo esc_textarea( PROMOTUR_Asistente_Panel::persona() ); ?></textarea>
					</label>
					<label class="promotur-field">
						<span><input type="checkbox" name="persona_fabrica" value="1"> <?php esc_html_e( 'Volver a la personalidad de fábrica', 'caaguazu-portal' ); ?></span>
					</label>
					<div>
						<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar personalidad', 'caaguazu-portal' ); ?></button>
					</div>
				</form>
			</div>

		<?php elseif ( 'memoria' === $pestana ) : ?>
			<div class="promotur-card">
				<div class="promotur-card__head">
					<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'Memoria', 'caaguazu-portal' ); ?></span>
				</div>
				<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="bloque" value="memoria">
					<input type="hidden" name="pestana" value="memoria">
					<div class="promotur-grid promotur-grid--2">
						<label class="promotur-field">
							<span><?php esc_html_e( 'Preguntas anteriores que recuerda', 'caaguazu-portal' ); ?></span>
							<select name="turnos">
								<?php for ( $i = 0; $i <= 20; $i++ ) : ?>
									<option value="<?php echo (int) $i; ?>"<?php selected( PROMOTUR_Asistente_Panel::memoria_turnos(), $i ); ?>><?php echo (int) $i; ?></option>
								<?php endfor; ?>
							</select>
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'Horas que dura una charla guardada', 'caaguazu-portal' ); ?></span>
							<select name="horas">
								<?php for ( $h = 1; $h <= 24; $h++ ) : ?>
									<option value="<?php echo (int) $h; ?>"<?php selected( PROMOTUR_Asistente_Panel::memoria_horas(), $h ); ?>><?php echo (int) $h; ?></option>
								<?php endfor; ?>
							</select>
						</label>
					</div>
					<p class="promotur-muted"><?php esc_html_e( 'Con 0 preguntas anteriores, cada pregunta se contesta sola.', 'caaguazu-portal' ); ?></p>
					<div>
						<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar memoria', 'caaguazu-portal' ); ?></button>
					</div>
				</form>

				<form class="promotur-form promotur-mt" method="post" action="<?php echo esc_url( $accion ); ?>" data-confirmar="<?php echo esc_attr__( 'Se olvidan todas las charlas guardadas, de todos. ¿Seguro?', 'caaguazu-portal' ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="bloque" value="olvidar">
					<input type="hidden" name="pestana" value="memoria">
					<p class="promotur-muted"><?php esc_html_e( 'Corta la memoria de todas las charlas a la vez. Las preguntas en curso empiezan de cero.', 'caaguazu-portal' ); ?></p>
					<div>
						<button type="submit" class="promotur-btn promotur-btn--ghost"><?php esc_html_e( 'Olvidar todas las charlas', 'caaguazu-portal' ); ?></button>
					</div>
				</form>
			</div>

		<?php elseif ( 'fuentes' === $pestana ) : ?>
			<?php $activas = PROMOTUR_Asistente_Panel::fuentes(); ?>
			<div class="promotur-card">
				<div class="promotur-card__head">
					<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'Fuentes', 'caaguazu-portal' ); ?></span>
				</div>
				<p class="promotur-muted"><?php esc_html_e( 'De qué saca el asistente sus respuestas. Lo que está apagado no entra y no se puede citar.', 'caaguazu-portal' ); ?></p>
				<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="bloque" value="fuentes">
					<input type="hidden" name="pestana" value="fuentes">
					<?php foreach ( $nombres_fuentes as $clave => $texto ) : ?>
						<label class="promotur-field">
							<span><input type="checkbox" name="fuentes[]" value="<?php echo esc_attr( $clave ); ?>"<?php checked( ! empty( $activas[ $clave ] ) ); ?>> <?php echo esc_html( $texto ); ?></span>
						</label>
					<?php endforeach; ?>
					<?php if ( ! array_filter( $activas ) ) : ?>
						<p class="promotur-form-msg is-error"><?php esc_html_e( 'Sin ninguna fuente, el asistente no puede contestar con datos.', 'caaguazu-portal' ); ?></p>
					<?php endif; ?>
					<div>
						<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar fuentes', 'caaguazu-portal' ); ?></button>
					</div>
				</form>
			</div>

		<?php else : ?>
			<div class="promotur-card" data-asistente-prueba
				data-msg-vacio="<?php echo esc_attr__( 'Escribí una pregunta.', 'caaguazu-portal' ); ?>"
				data-msg-enviando="<?php echo esc_attr__( 'Pensando…', 'caaguazu-portal' ); ?>"
				data-msg-error="<?php echo esc_attr__( 'No se pudo responder ahora.', 'caaguazu-portal' ); ?>"
				data-msg-fuentes="<?php echo esc_attr__( 'Fuentes:', 'caaguazu-portal' ); ?>">
				<div class="promotur-card__head">
					<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'Probar', 'caaguazu-portal' ); ?></span>
				</div>
				<p class="promotur-muted"><?php esc_html_e( 'Preguntale como lo haría un turista. La charla queda en la memoria por el tiempo que elegiste, igual que las de la app.', 'caaguazu-portal' ); ?></p>
				<div class="promotur-chat__log" data-chat-log aria-live="polite"></div>
				<form class="promotur-form" data-chat-form>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Pregunta', 'caaguazu-portal' ); ?></span>
						<textarea name="mensaje" rows="2" maxlength="1000"></textarea>
					</label>
					<p class="promotur-form-msg" data-chat-msg aria-live="polite"></p>
					<div>
						<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Preguntar', 'caaguazu-portal' ); ?></button>
						<button type="button" class="promotur-btn promotur-btn--ghost" data-chat-nueva><?php esc_html_e( 'Nueva conversación', 'caaguazu-portal' ); ?></button>
					</div>
				</form>
			</div>
		<?php endif; ?>

	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
