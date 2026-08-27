<?php
/**
 * Pantalla de administración del acceso desde el CEAD.
 *
 * Tres cosas, en el orden en que hacen falta cuando algo no anda:
 *
 *   1. El diagnóstico — qué está bien y qué no, con qué hacer en cada caso.
 *   2. El mapa de roles — la equivalencia entre los roles del CEAD (que es un
 *      WordPress) y los del panel (que no lo es), editable acá.
 *   3. Vincular a mano una cuenta existente que un canje rechazó por email
 *      duplicado, más el registro de los últimos intentos.
 *
 * El diagnóstico va primero a propósito: es lo que se busca cuando alguien
 * escribe «no puedo entrar», y hasta ahora no existía.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CEADSSO_Admin {

	private static $instance = null;
	private $notice = null;

	/** Resultado de la prueba de red, si se pidió en este pedido. */
	private $endpoint = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	public function menu() {
		add_management_page(
			__( 'Acceso desde el CEAD', 'caaguazu-sso-cead' ),
			__( 'Acceso desde el CEAD', 'caaguazu-sso-cead' ),
			'manage_options',
			'caaguazu-sso-cead',
			array( $this, 'render' )
		);
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Un solo nonce para las tres acciones de la pantalla: es un solo
		// formulario conceptual —la configuración del acceso— aunque se envíe
		// por partes.
		$valido = ! empty( $_POST['ceadsso_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['ceadsso_nonce'] ), 'ceadsso_admin' );
		if ( $valido ) {
			if ( isset( $_POST['ceadsso_probar'] ) ) {
				$this->endpoint = CEADSSO_Validacion::probar_endpoint();
			} elseif ( isset( $_POST['ceadsso_roles'] ) ) {
				$this->handle_roles_submit();
			} elseif ( isset( $_POST['ceadsso_email'] ) ) {
				$this->handle_link_submit();
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Acceso desde el CEAD', 'caaguazu-sso-cead' ); ?></h1>

			<?php if ( $this->notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $this->notice['type'] ); ?>"><p><?php echo esc_html( $this->notice['text'] ); ?></p></div>
			<?php endif; ?>

			<?php
			$this->render_diagnostico();
			$this->render_mapa_roles();
			$this->render_vincular();
			$this->render_log();
			?>
		</div>
		<?php
	}

	/* --------------------------------------------------------------------- */
	/*  1. Diagnóstico                                                        */
	/* --------------------------------------------------------------------- */

	private function render_diagnostico() {
		$items = CEADSSO_Validacion::comprobar();
		if ( $this->endpoint ) {
			$items[] = $this->endpoint;
		}
		?>
		<h2><?php esc_html_e( 'Cómo está la integración', 'caaguazu-sso-cead' ); ?></h2>
		<p><?php esc_html_e( 'Cinco piezas tienen que estar bien a la vez para que el acceso de un clic funcione. Acá se ven las cinco, y qué hacer con la que falle.', 'caaguazu-sso-cead' ); ?></p>

		<table class="widefat striped">
			<tbody>
				<?php foreach ( $items as $item ) :
					$marca = array( 'ok' => '✔', 'aviso' => '!', 'falla' => '✖' );
					?>
					<tr>
						<td style="width:2em"><strong><?php echo esc_html( isset( $marca[ $item['estado'] ] ) ? $marca[ $item['estado'] ] : '?' ); ?></strong></td>
						<td style="width:20em"><strong><?php echo esc_html( $item['titulo'] ); ?></strong></td>
						<td>
							<?php echo esc_html( $item['detalle'] ); ?>
							<?php if ( ! empty( $item['arreglo'] ) ) : ?>
								<br><em><?php echo esc_html( $item['arreglo'] ); ?></em>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" style="margin-top:1em">
			<?php wp_nonce_field( 'ceadsso_admin', 'ceadsso_nonce' ); ?>
			<p>
				<button type="submit" name="ceadsso_probar" value="1" class="button">
					<?php esc_html_e( 'Probar el endpoint del CEAD', 'caaguazu-sso-cead' ); ?>
				</button>
				<span class="description">
					<?php esc_html_e( 'Manda un código de prueba inválido al sitio del colegio para ver si hay alguien atendiendo. No consume el acceso de nadie.', 'caaguazu-sso-cead' ); ?>
				</span>
			</p>
		</form>
		<?php
	}

	/* --------------------------------------------------------------------- */
	/*  2. Mapa de roles                                                      */
	/* --------------------------------------------------------------------- */

	private function render_mapa_roles() {
		$mapa       = CEADSSO_Roles::mapa();
		$base       = CEADSSO_Roles::base();
		$roles      = CEADSSO_Roles::roles_del_panel();
		$pendientes = CEADSSO_Validacion::roles_sin_mapear();
		ksort( $mapa );
		?>
		<h2><?php esc_html_e( 'Qué rol del CEAD entra como qué', 'caaguazu-sso-cead' ); ?></h2>
		<p>
			<?php esc_html_e( 'El CEAD es un WordPress y manda sus roles de WordPress; el portal usa su propio sistema de cuentas, con los roles del panel de promotores. Esta tabla es el puente entre los dos.', 'caaguazu-sso-cead' ); ?>
			<br>
			<?php esc_html_e( 'Los nombres se comparan sin acentos, sin mayúsculas y sin el prefijo del colegio ni el sufijo del curso: «Cead_Docente_Turismo» y «docente» son lo mismo. Dejá el rol vacío para sacar una equivalencia.', 'caaguazu-sso-cead' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'ceadsso_admin', 'ceadsso_nonce' ); ?>
			<input type="hidden" name="ceadsso_roles" value="1">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Rol en el CEAD', 'caaguazu-sso-cead' ); ?></th>
						<th><?php esc_html_e( 'Entra al portal como', 'caaguazu-sso-cead' ); ?></th>
						<th><?php esc_html_e( 'De dónde sale', 'caaguazu-sso-cead' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $n = 0; ?>
					<?php foreach ( $mapa as $origen => $destino ) : $n++; ?>
						<tr>
							<td>
								<code><?php echo esc_html( $origen ); ?></code>
								<input type="hidden" name="mapa[<?php echo esc_attr( $n ); ?>][origen]" value="<?php echo esc_attr( $origen ); ?>">
							</td>
							<td><?php $this->select_rol( "mapa[{$n}][destino]", $destino, $roles ); ?></td>
							<td>
								<?php
								echo isset( $base[ $origen ] ) && $base[ $origen ] === $destino
									? esc_html__( 'Viene con el plugin', 'caaguazu-sso-cead' )
									: esc_html__( 'Lo cargó alguien acá', 'caaguazu-sso-cead' );
								?>
							</td>
						</tr>
					<?php endforeach; ?>

					<?php // Los roles que llegaron y se rechazaron, listos para resolver de un clic. ?>
					<?php foreach ( $pendientes as $rol ) : $n++; ?>
						<tr>
							<td>
								<code><?php echo esc_html( $rol ); ?></code>
								<input type="hidden" name="mapa[<?php echo esc_attr( $n ); ?>][origen]" value="<?php echo esc_attr( $rol ); ?>">
							</td>
							<td><?php $this->select_rol( "mapa[{$n}][destino]", '', $roles ); ?></td>
							<td><strong><?php esc_html_e( 'Llegó y se rechazó', 'caaguazu-sso-cead' ); ?></strong></td>
						</tr>
					<?php endforeach; ?>

					<?php // Dos filas en blanco para agregar equivalencias a mano. ?>
					<?php for ( $i = 0; $i < 2; $i++ ) : $n++; ?>
						<tr>
							<td><input type="text" name="mapa[<?php echo esc_attr( $n ); ?>][origen]" value="" class="regular-text" placeholder="<?php esc_attr_e( 'p. ej. auxiliar', 'caaguazu-sso-cead' ); ?>"></td>
							<td><?php $this->select_rol( "mapa[{$n}][destino]", '', $roles ); ?></td>
							<td>—</td>
						</tr>
					<?php endfor; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Guardar el mapa de roles', 'caaguazu-sso-cead' ) ); ?>
		</form>
		<?php
	}

	/**
	 * @param string $nombre
	 * @param string $actual
	 * @param array  $roles
	 */
	private function select_rol( $nombre, $actual, $roles ) {
		?>
		<select name="<?php echo esc_attr( $nombre ); ?>">
			<option value=""><?php esc_html_e( '— no entra —', 'caaguazu-sso-cead' ); ?></option>
			<?php foreach ( $roles as $clave => $etiqueta ) : ?>
				<option value="<?php echo esc_attr( $clave ); ?>" <?php selected( $actual, $clave ); ?>>
					<?php echo esc_html( $etiqueta ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	private function handle_roles_submit() {
		$filas = isset( $_POST['mapa'] ) ? (array) wp_unslash( $_POST['mapa'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$mapa  = array();
		foreach ( $filas as $fila ) {
			if ( ! is_array( $fila ) || empty( $fila['origen'] ) ) {
				continue;
			}
			$destino = isset( $fila['destino'] ) ? sanitize_key( $fila['destino'] ) : '';
			if ( '' === $destino ) {
				continue; // fila vaciada a propósito: se cae del mapa.
			}
			$mapa[ sanitize_text_field( $fila['origen'] ) ] = $destino;
		}

		CEADSSO_Roles::guardar_mapa( $mapa );
		$this->notice = array( 'type' => 'success', 'text' => __( 'Guardamos el mapa de roles. La próxima vez que esas personas entren desde el CEAD, pasan.', 'caaguazu-sso-cead' ) );
	}

	/* --------------------------------------------------------------------- */
	/*  3. Vincular a mano                                                    */
	/* --------------------------------------------------------------------- */

	private function render_vincular() {
		?>
		<h2><?php esc_html_e( 'Vincular una cuenta que ya existe', 'caaguazu-sso-cead' ); ?></h2>
		<p><?php esc_html_e( 'Cuando alguien entra desde el CEAD con un email que ya tiene cuenta en el portal (sin vincular todavía), el acceso se rechaza a propósito — vincular solo por email es la puerta de un robo de cuenta. Usá este formulario para confirmar el vínculo a mano.', 'caaguazu-sso-cead' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'ceadsso_admin', 'ceadsso_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="ceadsso_email"><?php esc_html_e( 'Email de la cuenta existente', 'caaguazu-sso-cead' ); ?></label></th>
					<td><input type="email" id="ceadsso_email" name="ceadsso_email" class="regular-text" required></td>
				</tr>
				<tr>
					<th><label for="ceadsso_uid"><?php esc_html_e( 'cead_uid (visible en el registro de abajo)', 'caaguazu-sso-cead' ); ?></label></th>
					<td><input type="number" id="ceadsso_uid" name="ceadsso_uid" class="regular-text" min="1" required></td>
				</tr>
			</table>
			<?php submit_button( __( 'Vincular', 'caaguazu-sso-cead' ) ); ?>
		</form>
		<?php
	}

	private function handle_link_submit() {
		$email    = isset( $_POST['ceadsso_email'] ) ? sanitize_email( wp_unslash( $_POST['ceadsso_email'] ) ) : '';
		$cead_uid = isset( $_POST['ceadsso_uid'] ) ? (int) $_POST['ceadsso_uid'] : 0;

		if ( ! is_email( $email ) || $cead_uid <= 0 ) {
			$this->notice = array( 'type' => 'error', 'text' => __( 'Datos inválidos.', 'caaguazu-sso-cead' ) );
			return;
		}

		$account = Caaguazu_Cuentas_Accounts::get_by_email( $email );
		if ( ! $account ) {
			$this->notice = array( 'type' => 'error', 'text' => __( 'No hay ninguna cuenta con ese email.', 'caaguazu-sso-cead' ) );
			return;
		}

		$linked = CEADSSO_Link::link( $account['id'], $cead_uid );
		if ( ! $linked ) {
			$this->notice = array( 'type' => 'error', 'text' => __( 'Esa cuenta o ese cead_uid ya están vinculados a otra cosa.', 'caaguazu-sso-cead' ) );
			return;
		}

		caaguazu_account_meta_set( $account['id'], 'cead_uid', $cead_uid );
		$this->notice = array( 'type' => 'success', 'text' => __( 'Cuenta vinculada. La próxima vez que entre desde el CEAD, va a caer en esa misma cuenta.', 'caaguazu-sso-cead' ) );
	}

	/* --------------------------------------------------------------------- */

	private function render_log() {
		?>
		<h2><?php esc_html_e( 'Últimos intentos de acceso', 'caaguazu-sso-cead' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Fecha', 'caaguazu-sso-cead' ); ?></th>
					<th><?php esc_html_e( 'Resultado', 'caaguazu-sso-cead' ); ?></th>
					<th><?php esc_html_e( 'Motivo', 'caaguazu-sso-cead' ); ?></th>
					<th>cead_uid</th>
					<th><?php esc_html_e( 'Email', 'caaguazu-sso-cead' ); ?></th>
					<th><?php esc_html_e( 'Rol CEAD', 'caaguazu-sso-cead' ); ?></th>
					<th><?php esc_html_e( 'Cuenta', 'caaguazu-sso-cead' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $filas = CEADSSO_Log::recent(); ?>
				<?php if ( ! $filas ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'Todavía nadie intentó entrar desde el CEAD.', 'caaguazu-sso-cead' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $filas as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['created_at'] ); ?></td>
						<td><?php echo esc_html( $row['resultado'] ); ?></td>
						<td><?php echo esc_html( $row['motivo'] ? $row['motivo'] : '—' ); ?></td>
						<td><?php echo esc_html( $row['cead_uid'] ? $row['cead_uid'] : '—' ); ?></td>
						<td><?php echo esc_html( $row['email'] ? $row['email'] : '—' ); ?></td>
						<td><?php echo esc_html( $row['rol_cead'] ? $row['rol_cead'] : '—' ); ?></td>
						<td><?php echo esc_html( $row['account_id'] ? $row['account_id'] : '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
