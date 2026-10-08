<?php
/**
 * wp-admin → Caaguazú API → Asistente.
 *
 * Va acá y no en el panel del equipo por lo mismo que el token de GitHub de
 * la pantalla de al lado: son credenciales de un servicio pago, y quien las
 * carga es quien administra el servidor, no quien escribe fichas. El panel
 * sigue siendo donde se escribe lo que el asistente LEE —fichas, artículos,
 * recorridos—, que es la parte que importa.
 *
 * Es la pantalla «CEADI · IA» del CEAD recortada a lo que el asistente usa:
 * los dos proveedores, los parámetros, la personalidad, el conocimiento, el
 * estado y una prueba por proveedor.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Asistente_Admin {

	private static $instance = null;

	/** Credenciales de un servicio pago: lo mismo que pide wp-admin para sus ajustes. */
	const CAP = 'manage_options';

	const PAGINA = 'czuapi-asistente';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Después del menú padre, que registra `CZUAPI_Admin` en la prioridad 10.
		add_action( 'admin_menu', array( $this, 'menu' ), 20 );
		add_action( 'admin_post_czuapi_asistente', array( $this, 'guardar' ) );
	}

	public function menu() {
		add_submenu_page(
			'czuapi-updates',
			__( 'Asistente', 'caaguazu-app-api' ),
			__( 'Asistente', 'caaguazu-app-api' ),
			self::CAP,
			self::PAGINA,
			array( $this, 'render' )
		);
	}

	private function guard() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-app-api' ) );
		}
	}

	private function aviso( $msg, $tipo = 'success' ) {
		set_transient( 'czuapi_ia_aviso_' . get_current_user_id(), array( 'm' => $msg, 't' => $tipo ), 120 );
	}

	/* --------------------------------------------------------------------- */

	public function render() {
		$this->guard();

		$aviso = get_transient( 'czuapi_ia_aviso_' . get_current_user_id() );
		delete_transient( 'czuapi_ia_aviso_' . get_current_user_id() );

		$url = admin_url( 'admin-post.php' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Asistente de la app', 'caaguazu-app-api' ); ?></h1>
			<p class="description" style="max-width:720px">
				<?php esc_html_e( 'Responde dudas turísticas dentro de la app con lo publicado en el panel: fichas, eventos, recorridos y artículos. Cada respuesta trae enlaces a las piezas de donde salió. Funciona con cualquier API compatible con OpenAI.', 'caaguazu-app-api' ); ?>
			</p>

			<?php if ( is_array( $aviso ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $aviso['t'] ); ?> is-dismissible"><p><?php echo esc_html( $aviso['m'] ); ?></p></div>
			<?php endif; ?>

			<?php $this->render_estado(); ?>

			<form method="post" action="<?php echo esc_url( $url ); ?>">
				<?php wp_nonce_field( 'czuapi_asistente' ); ?>
				<input type="hidden" name="action" value="czuapi_asistente">
				<input type="hidden" name="op" value="guardar">

				<h2><?php esc_html_e( 'Encendido', 'caaguazu-app-api' ); ?></h2>
				<table class="form-table"><tbody>
					<tr><th><?php esc_html_e( 'Asistente', 'caaguazu-app-api' ); ?></th><td>
						<label><input type="checkbox" name="activo" value="1" <?php checked( (bool) get_option( 'czuapi_ia_activo', 0 ) ); ?>>
						<?php esc_html_e( 'Encendido: la app muestra el botón', 'caaguazu-app-api' ); ?></label>
						<p class="description"><?php esc_html_e( 'Sin key cargada queda apagado aunque esté tildado.', 'caaguazu-app-api' ); ?></p>
					</td></tr>
				</tbody></table>

				<h2><?php esc_html_e( 'Proveedor principal', 'caaguazu-app-api' ); ?></h2>
				<?php $this->render_proveedor( 'czuapi_ia_', 'CZUAPI_IA_KEY', CZUAPI_Asistente::ENDPOINT_DEFAULT, CZUAPI_Asistente::MODEL_DEFAULT ); ?>

				<h2><?php esc_html_e( 'Proveedor de respaldo', 'caaguazu-app-api' ); ?></h2>
				<p class="description" style="max-width:720px"><?php esc_html_e( 'Entra solo cuando el principal falla. Conviene que sea de otra empresa: dos modelos del mismo proveedor se caen juntos. Cuenta sólo si tiene endpoint, modelo y key.', 'caaguazu-app-api' ); ?></p>
				<?php $this->render_proveedor( 'czuapi_ia2_', 'CZUAPI_IA2_KEY', '', '' ); ?>

				<h2><?php esc_html_e( 'Parámetros', 'caaguazu-app-api' ); ?></h2>
				<table class="form-table"><tbody>
					<?php
					$this->numero( 'temperatura', __( 'Temperatura', 'caaguazu-app-api' ), get_option( 'czuapi_ia_temperatura', '' ), '0.5', '0.1', __( 'Más baja, más apegada a las fuentes.', 'caaguazu-app-api' ) );
					$this->numero( 'max_tokens', __( 'Máx. tokens de respuesta', 'caaguazu-app-api' ), get_option( 'czuapi_ia_max_tokens', '' ), '800', '1', __( 'Pasado 2000 no se reintenta ante un corte, porque la espera se haría larga.', 'caaguazu-app-api' ) );
					$this->numero( 'presupuesto', __( 'Presupuesto de contexto (caracteres)', 'caaguazu-app-api' ), get_option( 'czuapi_ia_presupuesto', '' ), '40000', '1000', __( 'Se paga en cada pregunta. Si no alcanza, se recorta primero el catálogo, después el conocimiento y al final el detalle.', 'caaguazu-app-api' ) );
					$this->numero( 'memoria', __( 'Memoria (turnos)', 'caaguazu-app-api' ), get_option( 'czuapi_ia_memoria', '' ), '10', '1', __( 'Cuántas idas y vueltas recuerda de una charla, durante dos horas. 0 = sin memoria.', 'caaguazu-app-api' ) );
					$this->numero( 'tope_minuto', __( 'Preguntas por minuto (por IP)', 'caaguazu-app-api' ), get_option( 'czuapi_ia_tope_minuto', '' ), '8', '1', __( '0 = sin tope.', 'caaguazu-app-api' ) );
					$this->numero( 'tope_dia', __( 'Preguntas por día (por IP)', 'caaguazu-app-api' ), get_option( 'czuapi_ia_tope_dia', '' ), '80', '1', __( 'Cada pregunta cuesta: esto es lo que impide que alguien vacíe la cuenta. 0 = sin tope.', 'caaguazu-app-api' ) );
					?>
					<tr><th><?php esc_html_e( 'Razonamiento', 'caaguazu-app-api' ); ?></th><td>
						<?php $r = CZUAPI_Asistente::razonamiento(); ?>
						<select name="razonamiento">
							<option value="" <?php selected( $r, '' ); ?>><?php esc_html_e( 'Apagado', 'caaguazu-app-api' ); ?></option>
							<option value="low" <?php selected( $r, 'low' ); ?>>low</option>
							<option value="medium" <?php selected( $r, 'medium' ); ?>>medium</option>
							<option value="high" <?php selected( $r, 'high' ); ?>>high</option>
						</select>
						<p class="description"><?php esc_html_e( 'Sólo si el proveedor acepta reasoning_effort: a uno que no lo conoce le hace devolver error en todas las respuestas.', 'caaguazu-app-api' ); ?></p>
					</td></tr>
					<tr><th><?php esc_html_e( 'Aviso de caída', 'caaguazu-app-api' ); ?></th><td>
						<input type="email" name="aviso_email" class="regular-text" value="<?php echo esc_attr( (string) get_option( 'czuapi_ia_aviso_email', '' ) ); ?>" placeholder="<?php echo esc_attr( (string) get_option( 'admin_email', '' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Un correo por causa cada media hora, con qué pasó y qué hacer. Nunca lleva la key. Vacío = el correo del sitio.', 'caaguazu-app-api' ); ?></p>
					</td></tr>
				</tbody></table>

				<h2><?php esc_html_e( 'Personalidad', 'caaguazu-app-api' ); ?></h2>
				<p class="description" style="max-width:720px"><?php esc_html_e( 'Cómo habla y qué criterio tiene. Las reglas para citar las fuentes van aparte y no se pueden borrar desde acá: son las que hacen que la app pueda enlazar cada dato.', 'caaguazu-app-api' ); ?></p>
				<textarea name="persona" rows="18" class="large-text code"><?php echo esc_textarea( CZUAPI_Asistente::persona() ); ?></textarea>
				<p><label><input type="checkbox" name="persona_fabrica" value="1"> <?php esc_html_e( 'Volver a la personalidad de fábrica', 'caaguazu-app-api' ); ?></label></p>

				<h2><?php esc_html_e( 'Conocimiento', 'caaguazu-app-api' ); ?></h2>
				<p class="description" style="max-width:720px"><?php esc_html_e( 'Lo que no está en ninguna ficha y el asistente tiene que saber: teléfonos de emergencia, transporte, clima, cómo llegar a la ciudad. Va entero en cada pregunta, así que conviene corto.', 'caaguazu-app-api' ); ?></p>
				<textarea name="conocimiento" rows="10" class="large-text"><?php echo esc_textarea( CZUAPI_Asistente::conocimiento() ); ?></textarea>

				<?php submit_button( __( 'Guardar', 'caaguazu-app-api' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Probar', 'caaguazu-app-api' ); ?></h2>
			<p class="description" style="max-width:720px"><?php esc_html_e( 'Cada proveedor se prueba solo, sin respaldo: si la prueba del principal se fuera al respaldo, diría OK con el principal caído.', 'caaguazu-app-api' ); ?></p>
			<form method="post" action="<?php echo esc_url( $url ); ?>">
				<?php wp_nonce_field( 'czuapi_asistente' ); ?>
				<input type="hidden" name="action" value="czuapi_asistente">
				<input type="hidden" name="op" value="probar">
				<p>
					<input type="text" name="pregunta" class="large-text" required placeholder="<?php esc_attr_e( 'Una pregunta como la haría un turista', 'caaguazu-app-api' ); ?>">
				</p>
				<p>
					<select name="idioma">
						<?php foreach ( CZUAPI_Idiomas::soportados() as $codigo ) : ?>
							<option value="<?php echo esc_attr( $codigo ); ?>"><?php echo esc_html( $codigo ); ?></option>
						<?php endforeach; ?>
					</select>
					<button class="button button-primary" name="proveedor" value="principal"><?php esc_html_e( 'Probar el principal', 'caaguazu-app-api' ); ?></button>
					<button class="button" name="proveedor" value="respaldo"><?php esc_html_e( 'Probar el respaldo', 'caaguazu-app-api' ); ?></button>
				</p>
			</form>

			<form method="post" action="<?php echo esc_url( $url ); ?>">
				<?php wp_nonce_field( 'czuapi_asistente' ); ?>
				<input type="hidden" name="action" value="czuapi_asistente">
				<input type="hidden" name="op" value="regenerar">
				<p>
					<button class="button"><?php esc_html_e( 'Rearmar el catálogo', 'caaguazu-app-api' ); ?></button>
					<span class="description"><?php esc_html_e( 'Se rearma solo cuando algo se publica, se despublica o se edita, y cada quince minutos. Esto es para no esperar.', 'caaguazu-app-api' ); ?></span>
				</p>
			</form>
		</div>
		<?php
	}

	private function render_estado() {
		$filas = array();

		if ( CZUAPI_Asistente::activo() ) {
			$filas[] = array( __( 'Estado', 'caaguazu-app-api' ), __( 'Encendido: la app lo muestra.', 'caaguazu-app-api' ) );
		} elseif ( '' === CZUAPI_Asistente::key() ) {
			$filas[] = array( __( 'Estado', 'caaguazu-app-api' ), __( 'Apagado: falta la key del proveedor principal.', 'caaguazu-app-api' ) );
		} else {
			$filas[] = array( __( 'Estado', 'caaguazu-app-api' ), __( 'Apagado.', 'caaguazu-app-api' ) );
		}

		$filas[] = array(
			__( 'Respaldo', 'caaguazu-app-api' ),
			CZUAPI_Asistente::respaldo_activo()
				? ( CZUAPI_Asistente::en_respaldo()
					? __( 'Configurado, y atendió en la última hora porque falló el principal.', 'caaguazu-app-api' )
					: __( 'Configurado.', 'caaguazu-app-api' ) )
				: __( 'Sin configurar.', 'caaguazu-app-api' ),
		);

		$error = CZUAPI_Asistente::ultimo_error();
		if ( $error ) {
			$dg      = CZUAPI_Asistente::diagnostico( $error['code'], $error['error'] );
			$filas[] = array( __( 'Último error', 'caaguazu-app-api' ), $error['time'] . ' — ' . $error['error'] . ' · ' . $dg['causa'] . ' ' . $dg['arreglo'] );
		}

		$uso = CZUAPI_Asistente::ultimo_uso();
		if ( $uso ) {
			$cache   = null === $uso['cached']
				? __( 'el proveedor no informa caché', 'caaguazu-app-api' )
				: sprintf( __( '%d de caché', 'caaguazu-app-api' ), (int) $uso['cached'] );
			$filas[] = array(
				__( 'Última respuesta', 'caaguazu-app-api' ),
				sprintf( __( '%1$s — %2$d tokens de entrada (%3$s), %4$d de salida', 'caaguazu-app-api' ), $uso['time'], (int) $uso['prompt'], $cache, (int) $uso['completion'] ),
			);
		}

		$cat     = CZUAPI_Asistente_Fuentes::estado( 'es' );
		$filas[] = array(
			__( 'Catálogo (castellano)', 'caaguazu-app-api' ),
			$cat
				? sprintf( __( '%1$d piezas, %2$d caracteres', 'caaguazu-app-api' ), (int) $cat['piezas'], (int) $cat['caracteres'] )
				: __( 'Todavía no se armó: se arma con la primera pregunta.', 'caaguazu-app-api' ),
		);

		$prueba = get_transient( 'czuapi_ia_prueba_' . get_current_user_id() );
		delete_transient( 'czuapi_ia_prueba_' . get_current_user_id() );
		?>
		<?php if ( is_array( $prueba ) ) : ?>
			<div class="notice notice-<?php echo $prueba['ok'] ? 'success' : 'error'; ?>">
				<p><strong><?php echo esc_html( $prueba['titulo'] ); ?></strong></p>
				<p><?php echo esc_html( $prueba['resumen'] ); ?></p>
			</div>
		<?php endif; ?>
		<table class="widefat striped" style="max-width:860px;margin-top:12px">
			<tbody>
				<?php foreach ( $filas as $f ) : ?>
					<tr><th style="width:200px"><?php echo esc_html( $f[0] ); ?></th><td><?php echo esc_html( $f[1] ); ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Endpoint, interruptor de base URL, modelo y key de un proveedor.
	 *
	 * @param string $p          prefijo de las opciones
	 * @param string $constante  la constante de wp-config.php que pisa la key
	 */
	private function render_proveedor( $p, $constante, $endpoint_default, $modelo_default ) {
		$en_config = defined( $constante ) && constant( $constante );
		$guardada  = '' !== (string) get_option( $p . 'key', '' );
		?>
		<table class="form-table"><tbody>
			<tr><th><?php esc_html_e( 'Endpoint', 'caaguazu-app-api' ); ?></th><td>
				<input type="url" name="<?php echo esc_attr( $p ); ?>endpoint" class="large-text code" value="<?php echo esc_attr( (string) get_option( $p . 'endpoint', '' ) ); ?>" placeholder="<?php echo esc_attr( $endpoint_default ); ?>">
				<p><label><input type="checkbox" name="<?php echo esc_attr( $p ); ?>endpoint_base" value="1" <?php checked( (bool) get_option( $p . 'endpoint_base', 0 ) ); ?>>
				<?php
				printf(
					/* translators: %s: chat path */
					esc_html__( 'Es una base URL: agregarle %s', 'caaguazu-app-api' ),
					'<code>' . esc_html( CZUAPI_Asistente::CHAT_PATH ) . '</code>'
				);
				?>
				</label></p>
			</td></tr>
			<tr><th><?php esc_html_e( 'Modelo', 'caaguazu-app-api' ); ?></th><td>
				<input type="text" name="<?php echo esc_attr( $p ); ?>modelo" class="regular-text code" value="<?php echo esc_attr( (string) get_option( $p . 'modelo', '' ) ); ?>" placeholder="<?php echo esc_attr( $modelo_default ); ?>">
			</td></tr>
			<tr><th><?php esc_html_e( 'API key', 'caaguazu-app-api' ); ?></th><td>
				<?php if ( $en_config ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: constant name */
							esc_html__( 'Definida en wp-config.php con %s. Tiene prioridad sobre la guardada acá.', 'caaguazu-app-api' ),
							'<code>' . esc_html( $constante ) . '</code>'
						);
						?>
					</p>
				<?php else : ?>
					<input type="password" name="<?php echo esc_attr( $p ); ?>key" class="regular-text" autocomplete="new-password" placeholder="<?php echo $guardada ? esc_attr__( '•••• guardada (vacío la conserva)', 'caaguazu-app-api' ) : 'sk-…'; ?>">
					<?php if ( $guardada ) : ?>
						<p><label><input type="checkbox" name="<?php echo esc_attr( $p ); ?>key_borrar" value="1"> <?php esc_html_e( 'Borrar la key guardada', 'caaguazu-app-api' ); ?></label></p>
					<?php endif; ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: constant name */
							esc_html__( 'También se puede definir %s en wp-config.php, que no queda en la base de datos.', 'caaguazu-app-api' ),
							'<code>' . esc_html( $constante ) . '</code>'
						);
						?>
					</p>
				<?php endif; ?>
			</td></tr>
		</tbody></table>
		<?php
	}

	private function numero( $nombre, $etiqueta, $valor, $placeholder, $paso, $ayuda ) {
		?>
		<tr><th><?php echo esc_html( $etiqueta ); ?></th><td>
			<input type="number" name="<?php echo esc_attr( $nombre ); ?>" class="small-text" step="<?php echo esc_attr( $paso ); ?>" min="0" value="<?php echo esc_attr( (string) $valor ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
			<p class="description"><?php echo esc_html( $ayuda ); ?></p>
		</td></tr>
		<?php
	}

	/* --------------------------------------------------------------------- */

	public function guardar() {
		$this->guard();
		check_admin_referer( 'czuapi_asistente' );

		$op = sanitize_key( wp_unslash( $_POST['op'] ?? '' ) );

		switch ( $op ) {
			case 'guardar':
				$this->guardar_ajustes();
				$this->aviso( __( 'Guardado.', 'caaguazu-app-api' ) );
				break;

			case 'probar':
				$pregunta  = sanitize_text_field( wp_unslash( $_POST['pregunta'] ?? '' ) );
				$proveedor = 'respaldo' === sanitize_key( wp_unslash( $_POST['proveedor'] ?? '' ) ) ? 'respaldo' : 'principal';
				$idioma    = sanitize_key( wp_unslash( $_POST['idioma'] ?? 'es' ) );
				if ( ! in_array( $idioma, CZUAPI_Idiomas::soportados(), true ) ) {
					$idioma = CZUAPI_Idiomas::FALLBACK;
				}
				$r = '' === $pregunta
					? array( 'ok' => false, 'resumen' => __( 'Falta la pregunta.', 'caaguazu-app-api' ) )
					: CZUAPI_Asistente::probar( $pregunta, $proveedor, $idioma );
				set_transient( 'czuapi_ia_prueba_' . get_current_user_id(), array(
					'ok'      => $r['ok'],
					'titulo'  => 'respaldo' === $proveedor ? __( 'Prueba del respaldo', 'caaguazu-app-api' ) : __( 'Prueba del principal', 'caaguazu-app-api' ),
					'resumen' => $r['resumen'],
				), 120 );
				break;

			case 'regenerar':
				CZUAPI_Asistente_Fuentes::invalidar();
				$this->aviso( __( 'El catálogo se rearma con la próxima pregunta.', 'caaguazu-app-api' ) );
				break;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGINA ) );
		exit;
	}

	private function guardar_ajustes() {
		update_option( 'czuapi_ia_activo', empty( $_POST['activo'] ) ? 0 : 1, false );

		foreach ( array( 'czuapi_ia_', 'czuapi_ia2_' ) as $p ) {
			update_option( $p . 'endpoint', esc_url_raw( trim( wp_unslash( $_POST[ $p . 'endpoint' ] ?? '' ) ) ), false );
			update_option( $p . 'endpoint_base', empty( $_POST[ $p . 'endpoint_base' ] ) ? 0 : 1, false );
			update_option( $p . 'modelo', sanitize_text_field( wp_unslash( $_POST[ $p . 'modelo' ] ?? '' ) ), false );

			// La key: vacío la conserva, el tilde la borra. Nunca se vuelve a
			// mostrar, así que no hay forma de que una visita a esta pantalla
			// la deje en el HTML.
			if ( ! empty( $_POST[ $p . 'key_borrar' ] ) ) {
				delete_option( $p . 'key' );
			} else {
				$key = trim( sanitize_text_field( wp_unslash( $_POST[ $p . 'key' ] ?? '' ) ) );
				if ( '' !== $key ) {
					update_option( $p . 'key', $key, false );
				}
			}
		}

		// Los números vacíos se guardan vacíos: así rige el valor por defecto
		// del código, y un cambio de ese valor les llega a todos.
		$numeros = array(
			'temperatura' => 'floatval',
			'max_tokens'  => 'absint',
			'presupuesto' => 'absint',
			'memoria'     => 'absint',
			'tope_minuto' => 'absint',
			'tope_dia'    => 'absint',
		);
		foreach ( $numeros as $campo => $limpiar ) {
			$crudo = trim( (string) wp_unslash( $_POST[ $campo ] ?? '' ) );
			update_option( 'czuapi_ia_' . $campo, '' === $crudo ? '' : call_user_func( $limpiar, $crudo ), false );
		}

		$razona = sanitize_key( wp_unslash( $_POST['razonamiento'] ?? '' ) );
		update_option( 'czuapi_ia_razonamiento', in_array( $razona, array( 'low', 'medium', 'high' ), true ) ? $razona : '', false );
		update_option( 'czuapi_ia_aviso_email', sanitize_email( wp_unslash( $_POST['aviso_email'] ?? '' ) ), false );

		/*
		 * La personalidad igual a la de fábrica se guarda VACÍA: así, cuando la
		 * de fábrica mejore en una versión nueva, le llega a quien nunca la
		 * tocó. Guardarla copiada la congelaría en la versión de hoy.
		 */
		CZUAPI_Asistente::set_persona( wp_unslash( $_POST['persona'] ?? '' ), ! empty( $_POST['persona_fabrica'] ) );
		CZUAPI_Asistente::set_conocimiento( wp_unslash( $_POST['conocimiento'] ?? '' ) );
	}
}
