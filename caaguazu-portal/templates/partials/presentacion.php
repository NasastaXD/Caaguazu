<?php
/**
 * Pestaña «Presentación» del asistente: la esfera que habla, para un escenario.
 *
 * Un solo formulario para todo —guion, voz y oído—, porque es una sola cosa
 * que se prepara para un día y se apaga después. Las keys no se vuelven a
 * mostrar nunca: el campo vacío quiere decir «dejala como está».
 *
 * Se incluye desde templates/sections/asistente.php, adentro de su $body, así
 * que `$accion` ya está definida.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$p = PROMOTUR_Asistente_Panel::presentacion();
?>
<?php if ( ! $p ) : ?>
	<div class="promotur-card promotur-empty-box">
		<h2 class="promotur-h2"><?php esc_html_e( 'La presentación no está disponible', 'caaguazu-portal' ); ?></h2>
		<p class="promotur-muted"><?php esc_html_e( 'Hace falta la API de la app 0.11.0 o posterior activa en este sitio.', 'caaguazu-portal' ); ?></p>
	</div>
<?php else : ?>
	<?php
	$proveedores = array(
		'navegador'  => __( 'La voz del navegador', 'caaguazu-portal' ),
		'minimax'    => 'MiniMax',
		'elevenlabs' => 'ElevenLabs',
		'openai'     => __( 'OpenAI o compatible', 'caaguazu-portal' ),
	);
	$proveedores_oido = array(
		'openai'     => __( 'OpenAI o compatible (Whisper)', 'caaguazu-portal' ),
		'elevenlabs' => 'ElevenLabs (Scribe)',
	);
	$modos = array(
		'navegador' => __( 'El navegador (Chrome)', 'caaguazu-portal' ),
		'servidor'  => __( 'El servidor (graba y transcribe)', 'caaguazu-portal' ),
		'tiempo_real' => __( 'ElevenLabs en tiempo real (manos libres, WebSocket)', 'caaguazu-portal' ),
	);
	?>
	<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>" autocomplete="off">
		<?php PROMOTUR_Acciones::campos(); ?>
		<input type="hidden" name="bloque" value="presentacion">
		<input type="hidden" name="pestana" value="presentacion">

		<div class="promotur-card">
			<div class="promotur-card__head">
				<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Presentación', 'caaguazu-portal' ); ?></span>
			</div>
			<p class="promotur-muted"><?php esc_html_e( 'Una página aparte, sólo con la esfera: escucha, piensa y contesta en voz alta. Prendela para el día de la presentación y apagala después.', 'caaguazu-portal' ); ?></p>
			<label class="promotur-field">
				<span><input type="checkbox" name="activo" value="1"<?php checked( $p['activa'] ); ?>> <?php esc_html_e( 'Prendida', 'caaguazu-portal' ); ?></span>
			</label>
			<?php if ( ! $p['hay_modelo'] ) : ?>
				<p class="promotur-form-msg is-error"><?php esc_html_e( 'Falta la key del asistente (se carga en la administración del sitio). Sin ella no puede contestar.', 'caaguazu-portal' ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $p['url'] ) : ?>
				<p class="promotur-muted"><?php echo esc_html( $p['url'] ); ?></p>
				<div>
					<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir la presentación', 'caaguazu-portal' ); ?></a>
				</div>
			<?php endif; ?>
		</div>

		<div class="promotur-card">
			<div class="promotur-card__head">
				<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Guion', 'caaguazu-portal' ); ?></span>
			</div>
			<label class="promotur-field">
				<span><?php esc_html_e( 'Personalidad', 'caaguazu-portal' ); ?></span>
				<textarea name="persona" rows="12"><?php echo esc_textarea( $p['persona'] ); ?></textarea>
			</label>
			<label class="promotur-field">
				<span><input type="checkbox" name="persona_fabrica" value="1"> <?php esc_html_e( 'Volver a la personalidad de fábrica', 'caaguazu-portal' ); ?></span>
			</label>
			<label class="promotur-field">
				<span><?php esc_html_e( 'Lo que sabe del proyecto', 'caaguazu-portal' ); ?></span>
				<textarea name="conocimiento" rows="16"><?php echo esc_textarea( $p['conocimiento'] ); ?></textarea>
			</label>
			<div class="promotur-grid promotur-grid--2">
				<label class="promotur-field">
					<span><?php esc_html_e( 'Preguntas anteriores que recuerda', 'caaguazu-portal' ); ?></span>
					<select name="turnos">
						<?php for ( $i = 0; $i <= 20; $i++ ) : ?>
							<option value="<?php echo (int) $i; ?>"<?php selected( $p['turnos'], $i ); ?>><?php echo (int) $i; ?></option>
						<?php endfor; ?>
					</select>
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Modelo', 'caaguazu-portal' ); ?></span>
					<input type="text" name="modelo" value="<?php echo esc_attr( $p['modelo'] ); ?>" placeholder="<?php echo esc_attr( $p['modelo_activo'] ); ?>">
				</label>
			</div>
			<p class="promotur-muted"><?php esc_html_e( 'Recuerda su propia charla, sin depender de la memoria del asistente de la app. El modelo va por la misma API y endpoint; vacío = el del asistente.', 'caaguazu-portal' ); ?></p>
			<label class="promotur-field">
				<span><input type="checkbox" name="catalogo" value="1"<?php checked( $p['catalogo'] ); ?>> <?php esc_html_e( 'Sumar lo publicado en la app (lugares, eventos, artículos)', 'caaguazu-portal' ); ?></span>
			</label>
			<label class="promotur-field">
				<span><input type="checkbox" name="borradores" value="1"<?php checked( ! empty( $p['borradores'] ) ); ?>> <?php esc_html_e( 'Incluir borradores (lo que todavía no se publicó)', 'caaguazu-portal' ); ?></span>
			</label>
			<label class="promotur-field">
				<span><input type="checkbox" name="web" value="1"<?php checked( ! empty( $p['web'] ) ); ?>> <?php esc_html_e( 'Buscar en internet lo que no sepa (sólo OpenAI)', 'caaguazu-portal' ); ?></span>
			</label>
		</div>

		<div class="promotur-card">
			<div class="promotur-card__head">
				<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Voz', 'caaguazu-portal' ); ?></span>
			</div>
			<p class="promotur-muted"><?php esc_html_e( 'Quién convierte la respuesta en voz. Si el proveedor falla en el medio, la página sigue con la voz del navegador.', 'caaguazu-portal' ); ?></p>
			<div class="promotur-grid promotur-grid--2">
				<label class="promotur-field">
					<span><?php esc_html_e( 'Proveedor', 'caaguazu-portal' ); ?></span>
					<select name="voz_proveedor">
						<?php foreach ( $proveedores as $clave => $nombre ) : ?>
							<option value="<?php echo esc_attr( $clave ); ?>"<?php selected( $p['voz_proveedor'], $clave ); ?>><?php echo esc_html( $nombre ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Voz (ID)', 'caaguazu-portal' ); ?></span>
					<input type="text" name="voz_id" value="<?php echo esc_attr( $p['voz_id'] ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Modelo', 'caaguazu-portal' ); ?></span>
					<input type="text" name="voz_modelo" value="<?php echo esc_attr( $p['voz_modelo'] ); ?>" placeholder="<?php esc_attr_e( 'El rápido del proveedor', 'caaguazu-portal' ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Velocidad (0,5 a 2)', 'caaguazu-portal' ); ?></span>
					<input type="number" name="voz_velocidad" min="0.5" max="2" step="0.05" value="<?php echo esc_attr( $p['voz_velocidad'] ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Endpoint', 'caaguazu-portal' ); ?></span>
					<input type="url" name="voz_endpoint" value="<?php echo esc_attr( $p['voz_endpoint'] ); ?>" placeholder="<?php esc_attr_e( 'El del proveedor', 'caaguazu-portal' ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'API key', 'caaguazu-portal' ); ?></span>
					<?php if ( $p['voz_key_fija'] ) : ?>
						<input type="password" value="" disabled placeholder="<?php esc_attr_e( 'Fijada en wp-config.php', 'caaguazu-portal' ); ?>">
					<?php else : ?>
						<input type="password" name="voz_key" value="" autocomplete="new-password"
							placeholder="<?php echo esc_attr( '' !== $p['voz_key'] ? sprintf( /* translators: %s: final de la key */ __( 'Cargada (%s). Vacío: no cambia', 'caaguazu-portal' ), $p['voz_key'] ) : __( 'Sin cargar', 'caaguazu-portal' ) ); ?>">
					<?php endif; ?>
				</label>
			</div>
			<label class="promotur-field">
				<span><input type="checkbox" name="etiquetas" value="1"<?php checked( $p['etiquetas'] ); ?>> <?php esc_html_e( 'Dejar que el asistente ponga expresiones en la voz (sólo ElevenLabs, y sólo con un modelo que las entienda)', 'caaguazu-portal' ); ?></span>
			</label>
			<?php if ( '' !== $p['voz_key'] && ! $p['voz_key_fija'] ) : ?>
				<label class="promotur-field">
					<span><input type="checkbox" name="voz_key_borrar" value="1"> <?php esc_html_e( 'Borrar la key de la voz', 'caaguazu-portal' ); ?></span>
				</label>
			<?php endif; ?>
		</div>

		<div class="promotur-card">
			<div class="promotur-card__head">
				<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Oído', 'caaguazu-portal' ); ?></span>
			</div>
			<p class="promotur-muted"><?php esc_html_e( 'Quién entiende lo que se dice. El navegador es gratis, pero usa el micrófono del equipo. El servidor graba y transcribe por pedido. En tiempo real, ElevenLabs escucha todo el tiempo y corta solo en cada pausa: el teléfono queda siempre escuchando, sin tocar nada (usa la key de ElevenLabs).', 'caaguazu-portal' ); ?></p>
			<div class="promotur-grid promotur-grid--2">
				<label class="promotur-field">
					<span><?php esc_html_e( 'Quién transcribe', 'caaguazu-portal' ); ?></span>
					<select name="oido_modo">
						<?php foreach ( $modos as $clave => $nombre ) : ?>
							<option value="<?php echo esc_attr( $clave ); ?>"<?php selected( $p['oido_modo'], $clave ); ?>><?php echo esc_html( $nombre ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Proveedor de la transcripción', 'caaguazu-portal' ); ?></span>
					<select name="oido_proveedor">
						<?php foreach ( $proveedores_oido as $clave => $nombre ) : ?>
							<option value="<?php echo esc_attr( $clave ); ?>"<?php selected( $p['oido_proveedor'], $clave ); ?>><?php echo esc_html( $nombre ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Silencio para cortar una frase (segundos)', 'caaguazu-portal' ); ?></span>
					<input type="number" name="oido_silencio" min="0.5" max="5" step="0.1" value="<?php echo esc_attr( $p['oido_silencio'] ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Idioma', 'caaguazu-portal' ); ?></span>
					<input type="text" name="oido_idioma" value="<?php echo esc_attr( $p['oido_idioma'] ); ?>" placeholder="es-PY">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Endpoint', 'caaguazu-portal' ); ?></span>
					<input type="url" name="oido_endpoint" value="<?php echo esc_attr( $p['oido_endpoint'] ); ?>" placeholder="<?php esc_attr_e( 'El del proveedor elegido', 'caaguazu-portal' ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'Modelo', 'caaguazu-portal' ); ?></span>
					<input type="text" name="oido_modelo" value="<?php echo esc_attr( $p['oido_modelo'] ); ?>" placeholder="<?php esc_attr_e( 'El del proveedor elegido', 'caaguazu-portal' ); ?>">
				</label>
				<label class="promotur-field">
					<span><?php esc_html_e( 'API key', 'caaguazu-portal' ); ?></span>
					<?php if ( $p['oido_key_fija'] ) : ?>
						<input type="password" value="" disabled placeholder="<?php esc_attr_e( 'Fijada en wp-config.php', 'caaguazu-portal' ); ?>">
					<?php else : ?>
						<input type="password" name="oido_key" value="" autocomplete="new-password"
							placeholder="<?php echo esc_attr( '' !== $p['oido_key'] ? sprintf( /* translators: %s: final de la key */ __( 'Cargada (%s). Vacío: no cambia', 'caaguazu-portal' ), $p['oido_key'] ) : __( 'Sin cargar', 'caaguazu-portal' ) ); ?>">
					<?php endif; ?>
				</label>
			</div>
			<?php if ( '' !== $p['oido_key'] && ! $p['oido_key_fija'] ) : ?>
				<label class="promotur-field">
					<span><input type="checkbox" name="oido_key_borrar" value="1"> <?php esc_html_e( 'Borrar la key del oído', 'caaguazu-portal' ); ?></span>
				</label>
			<?php endif; ?>
		</div>

		<div>
			<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar presentación', 'caaguazu-portal' ); ?></button>
		</div>
	</form>

	<?php if ( caaguazu_account_can( 'promotor', PROMOTUR_Prueba::CAP ) ) : ?>
		<?php
		$investigacion = PROMOTUR_Prueba::investigacion();
		$de_prueba     = count( PROMOTUR_Prueba::cargadas() );
		$accion_prueba = PROMOTUR_Acciones::url( 'datos_prueba' );
		?>
		<div class="promotur-card promotur-mt">
			<div class="promotur-card__head">
				<?php echo promotur_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Datos de prueba', 'caaguazu-portal' ); ?></span>
			</div>
			<p class="promotur-muted">
				<?php
				printf(
					/* translators: 1: cantidad de sitios en el archivo, 2: herramienta, 3: cantidad cargada */
					esc_html__( 'La investigación trae %1$d sitios y eventos (%2$s). Entran como borradores marcados de prueba: los ven sólo los profesores, en la web y en el panel, y no salen al público sin revisión. Para la demo, «Cargar y publicar» las deja públicas hasta que se borren. Cargados ahora: %3$d.', 'caaguazu-portal' ),
					(int) count( $investigacion['sitios'] ),
					esc_html( $investigacion['herramienta'] ?: '—' ),
					(int) $de_prueba
				);
				?>
			</p>
			<div class="promotur-grid promotur-grid--2">
				<form method="post" action="<?php echo esc_url( $accion_prueba ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="hacer" value="cargar">
					<button type="submit" class="promotur-btn promotur-btn--primary"<?php disabled( ! $investigacion['sitios'] ); ?>><?php esc_html_e( 'Cargar los de prueba', 'caaguazu-portal' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( $accion_prueba ); ?>" data-confirmar="<?php echo esc_attr__( 'Las fichas de prueba van a quedar PUBLICADAS: el público y la app las ven hasta que las borres. ¿Seguro?', 'caaguazu-portal' ); ?>">
					<?php PROMOTUR_Acciones::campos(); ?>
					<input type="hidden" name="hacer" value="publicar">
					<button type="submit" class="promotur-btn promotur-btn--ghost"<?php disabled( ! $investigacion['sitios'] ); ?>><?php esc_html_e( 'Cargar y publicar (demo)', 'caaguazu-portal' ); ?></button>
				</form>
				<?php if ( $de_prueba ) : ?>
					<form method="post" action="<?php echo esc_url( $accion_prueba ); ?>" data-confirmar="<?php echo esc_attr__( 'Se borran todas las fichas de prueba, para siempre. Lo que cargó el equipo no se toca. ¿Seguro?', 'caaguazu-portal' ); ?>">
						<?php PROMOTUR_Acciones::campos(); ?>
						<input type="hidden" name="hacer" value="borrar">
						<button type="submit" class="promotur-btn promotur-btn--ghost"><?php esc_html_e( 'Borrar los de prueba', 'caaguazu-portal' ); ?></button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="promotur-card promotur-mt" data-presentacion-voz
		data-msg-vacio="<?php echo esc_attr__( 'Escribí una frase.', 'caaguazu-portal' ); ?>"
		data-msg-enviando="<?php echo esc_attr__( 'Generando la voz…', 'caaguazu-portal' ); ?>"
		data-msg-error="<?php echo esc_attr__( 'No se pudo generar la voz.', 'caaguazu-portal' ); ?>"
		data-msg-listo="<?php echo esc_attr__( 'Lista en %s s.', 'caaguazu-portal' ); ?>">
		<div class="promotur-card__head">
			<?php echo promotur_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Probar la voz', 'caaguazu-portal' ); ?></span>
		</div>
		<p class="promotur-muted"><?php esc_html_e( 'Prueba lo guardado, no lo que está escrito arriba sin guardar.', 'caaguazu-portal' ); ?></p>
		<form class="promotur-form" data-voz-form>
			<label class="promotur-field">
				<span><?php esc_html_e( 'Frase', 'caaguazu-portal' ); ?></span>
				<textarea name="texto" rows="2" maxlength="500"></textarea>
			</label>
			<p class="promotur-form-msg" data-voz-msg aria-live="polite"></p>
			<div>
				<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Escuchar', 'caaguazu-portal' ); ?></button>
			</div>
		</form>
	</div>
<?php endif; ?>
