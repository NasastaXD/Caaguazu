<?php
/**
 * Lo poco que queda en wp-admin: el registro de auditoría y las
 * actualizaciones del plugin.
 *
 * Acá vivían también «Usuarios» e «Invitaciones». Usuarios estaba roto de
 * raíz: listaba usuarios de WordPress, les cambiaba el rol de WordPress y los
 * suspendía con una usermeta, cuando desde el cutover de identidad los
 * promotores no tienen usuario de WordPress —esa pantalla editaba a otra gente,
 * o a nadie—. Las dos se rehicieron en el panel, en Equipo, sobre la cuenta y
 * su permiso sobre el panel, que es lo que decide de verdad quién entra.
 *
 * Lo que queda es de administrador del sitio, y ninguna de las dos cosas la
 * necesita nadie del equipo.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Admin {

	private static $instance = null;
	const CAP = 'promotur_manage_users';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Capability para la página de Actualizaciones (la tienen los administradores). */
	const CAP_UPDATES = 'update_plugins';

	/**
	 * Capability de la página de Invitaciones. Es la misma que gatea la
	 * sección Equipo del panel (`PROMOTUR_Equipo::CAP`): quien puede invitar
	 * desde ahí puede invitar desde acá. No se referencia esa constante
	 * directamente para no atarse al orden en que se cargan los `require` del
	 * plugin — es la misma cadena, nombrada dos veces a propósito.
	 */
	const CAP_TEAM = 'promotur_manage_team';

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_menu', array( $this, 'ocultar_tags_nativas' ), 999 );
		add_action( 'admin_post_promotur_admin_updates', array( $this, 'handle_updates' ) );
		add_action( 'admin_post_promotur_admin_invite', array( $this, 'handle_invite' ) );
	}

	/**
	 * Saca «Etiquetas» del menú de Entradas.
	 *
	 * Es la taxonomía `post_tag` que trae WordPress de fábrica para el
	 * `post_type` nativo `post` —nada que ver con `promotur_etiqueta`, la
	 * que arma y usa el panel—. Nadie del equipo escribe entradas nativas
	 * (el contenido real vive en los CPT propios), así que ese menú no
	 * sirve para nada acá: sólo estorba a quien entra a wp-admin.
	 *
	 * No se desregistra la taxonomía —sigue funcionando si algo la usa
	 * por debajo—, sólo se saca el enlace del menú.
	 */
	public function ocultar_tags_nativas() {
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
	}

	/* ----- Menú ----- */
	public function menu() {
		$puede_logs         = current_user_can( self::CAP );
		$puede_updates      = current_user_can( self::CAP_UPDATES );
		$puede_invitaciones = current_user_can( self::CAP_TEAM );
		if ( ! $puede_logs && ! $puede_updates && ! $puede_invitaciones ) { return; }

		// El menú padre y su slug por defecto se gatean por la primera
		// capability que tenga el rol, en este orden de preferencia.
		if ( $puede_logs ) {
			$parent_cap = self::CAP;
			$parent_cb  = array( $this, 'render_logs' );
		} elseif ( $puede_updates ) {
			$parent_cap = self::CAP_UPDATES;
			$parent_cb  = array( $this, 'render_updates' );
		} else {
			$parent_cap = self::CAP_TEAM;
			$parent_cb  = array( $this, 'render_invitaciones' );
		}
		add_menu_page( __( 'Portal Turismo', 'caaguazu-portal' ), __( 'Portal Turismo', 'caaguazu-portal' ), $parent_cap, 'promotur', $parent_cb, 'dashicons-palmtree', 57 );

		if ( $puede_logs ) {
			add_submenu_page( 'promotur', __( 'Registros', 'caaguazu-portal' ), __( 'Registros', 'caaguazu-portal' ), self::CAP, 'promotur', array( $this, 'render_logs' ) );
		}
		if ( $puede_invitaciones ) {
			add_submenu_page( 'promotur', __( 'Invitaciones', 'caaguazu-portal' ), __( 'Invitaciones', 'caaguazu-portal' ), self::CAP_TEAM, 'promotur-invitaciones', array( $this, 'render_invitaciones' ) );
		}
		add_submenu_page( 'promotur', __( 'Actualizaciones', 'caaguazu-portal' ), __( 'Actualizaciones', 'caaguazu-portal' ), self::CAP_UPDATES, 'promotur-updates', array( $this, 'render_updates' ) );
	}

	private function notice( $msg, $type = 'success' ) {
		set_transient( 'promotur_admin_notice_' . get_current_user_id(), array( 'm' => $msg, 't' => $type ), 60 );
	}
	private function show_notice() {
		$n = get_transient( 'promotur_admin_notice_' . get_current_user_id() );
		if ( $n ) {
			delete_transient( 'promotur_admin_notice_' . get_current_user_id() );
			printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $n['t'] ), wp_kses_post( $n['m'] ) );
		}
	}
	private function guard() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ) );
		}
	}

	/* ================= LOGS ================= */
	public function render_logs() {
		$this->guard();
		$tab   = isset( $_GET['tab'] ) && 'posts' === $_GET['tab'] ? 'posts' : 'usuarios'; // phpcs:ignore WordPress.Security.NonceVerification
		$paged = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification

		$actions = 'posts' === $tab
			? PROMOTUR_Audit::post_actions()
			: PROMOTUR_Audit::user_actions();

		$res = PROMOTUR_Audit::query( array( 'actions' => $actions, 'paged' => $paged, 'per_page' => 50 ) );
		$base = admin_url( 'admin.php?page=promotur-logs&tab=' . $tab );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Registros', 'caaguazu-portal' ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php echo 'usuarios' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=promotur-logs&tab=usuarios' ) ); ?>"><?php esc_html_e( 'Usuarios', 'caaguazu-portal' ); ?></a>
				<a class="nav-tab <?php echo 'posts' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=promotur-logs&tab=posts' ) ); ?>"><?php esc_html_e( 'Entradas', 'caaguazu-portal' ); ?></a>
			</h2>
			<table class="widefat striped">
				<thead><tr>
					<th><?php esc_html_e( 'Fecha', 'caaguazu-portal' ); ?></th><th><?php esc_html_e( 'Usuario', 'caaguazu-portal' ); ?></th>
					<th><?php esc_html_e( 'Acción', 'caaguazu-portal' ); ?></th><th><?php esc_html_e( 'Elemento', 'caaguazu-portal' ); ?></th>
					<th><?php esc_html_e( 'IP', 'caaguazu-portal' ); ?></th><th><?php esc_html_e( 'Detalle', 'caaguazu-portal' ); ?></th>
				</tr></thead>
				<tbody>
				<?php if ( empty( $res['rows'] ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No hay registros.', 'caaguazu-portal' ); ?></td></tr>
				<?php else : foreach ( $res['rows'] as $r ) :
					$u = $r['user_id'] ? get_userdata( $r['user_id'] ) : null; ?>
					<tr>
						<td><?php echo esc_html( $r['created_at'] ); ?></td>
						<td><?php echo esc_html( $u ? $u->display_name : ( $r['user_id'] ? '#' . $r['user_id'] : '—' ) ); ?></td>
						<td><code><?php echo esc_html( $r['action'] ); ?></code></td>
						<td><?php echo esc_html( trim( $r['entity_type'] . ' ' . ( $r['entity_id'] ? '#' . $r['entity_id'] : '' ) ) ); ?></td>
						<td><?php echo esc_html( $r['ip'] ); ?></td>
						<td><?php echo $r['payload'] ? '<code>' . esc_html( $r['payload'] ) . '</code>' : '—'; ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
			<?php if ( $res['pages'] > 1 ) : ?>
				<p class="tablenav"><span class="pagination-links">
					<?php for ( $i = 1; $i <= $res['pages']; $i++ ) : ?>
						<a class="button button-small <?php echo $i === $paged ? 'button-primary' : ''; ?>" href="<?php echo esc_url( $base . '&paged=' . $i ); ?>"><?php echo (int) $i; ?></a>
					<?php endfor; ?>
				</span></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ================= INVITACIONES =================
	 *
	 * Vive acá además de en Equipo (panel) porque no todo el que necesita
	 * invitar entra al panel: alguien que recién está armando el equipo puede
	 * preferir wp-admin, y esto no depende de usuarios de WordPress —inserta
	 * en la misma tabla custom que ya usa el panel, con `invited_by` en 0
	 * cuando lo crea un administrador de WP—, así que reabrirlo acá no repite
	 * el error de «Usuarios» (ver la cabecera de este archivo).
	 */
	private function guard_invitaciones() {
		if ( ! current_user_can( self::CAP_TEAM ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ) );
		}
	}

	public function render_invitaciones() {
		$this->guard_invitaciones();
		$roles = PROMOTUR_Roles::roles();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Invitaciones', 'caaguazu-portal' ); ?></h1>
			<?php $this->show_notice(); ?>

			<h2><?php esc_html_e( 'Invitar a alguien', 'caaguazu-portal' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'promotur_admin_invite' ); ?>
				<input type="hidden" name="action" value="promotur_admin_invite">
				<input type="hidden" name="op" value="create">
				<table class="form-table"><tbody>
					<tr>
						<th><label for="promotur-invite-role"><?php esc_html_e( 'Rol', 'caaguazu-portal' ); ?></label></th>
						<td>
							<select id="promotur-invite-role" name="role">
								<?php foreach ( $roles as $rk => $rd ) : ?>
									<option value="<?php echo esc_attr( $rk ); ?>" <?php selected( 'promotur_mini', $rk ); ?>><?php echo esc_html( $rd['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="promotur-invite-dias"><?php esc_html_e( 'Vence en (días)', 'caaguazu-portal' ); ?></label></th>
						<td>
							<input type="number" id="promotur-invite-dias" name="expires_days" min="0" step="1" placeholder="0" list="promotur-dias-sugeridos" style="width:100px">
							<datalist id="promotur-dias-sugeridos">
								<?php foreach ( PROMOTUR_Invitations::dias_sugeridos() as $d ) : ?>
									<option value="<?php echo esc_attr( $d ); ?>"></option>
								<?php endforeach; ?>
							</datalist>
							<p class="description"><?php esc_html_e( 'Vacío o 0: no vence nunca.', 'caaguazu-portal' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="promotur-invite-usos"><?php esc_html_e( 'Cuántas cuentas puede crear', 'caaguazu-portal' ); ?></label></th>
						<td>
							<input type="number" id="promotur-invite-usos" name="max_usos" min="0" step="1" value="1" style="width:100px">
							<p class="description"><?php esc_html_e( 'Vacío o 0: sin límite.', 'caaguazu-portal' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="promotur-invite-email"><?php esc_html_e( 'Email (opcional)', 'caaguazu-portal' ); ?></label></th>
						<td><input type="email" id="promotur-invite-email" name="email" class="regular-text"></td>
					</tr>
				</tbody></table>
				<p><button class="button button-primary"><?php esc_html_e( 'Crear enlace', 'caaguazu-portal' ); ?></button></p>
			</form>

			<h2><?php esc_html_e( 'Invitaciones abiertas', 'caaguazu-portal' ); ?></h2>
			<?php
			$abiertas = class_exists( 'PROMOTUR_Equipo' ) ? PROMOTUR_Equipo::invitaciones_abiertas() : array();
			if ( empty( $abiertas ) ) :
				?>
				<p><?php esc_html_e( 'No hay ninguna esperando.', 'caaguazu-portal' ); ?></p>
			<?php else : ?>
				<table class="widefat striped" style="max-width:900px">
					<thead><tr>
						<th><?php esc_html_e( 'Rol', 'caaguazu-portal' ); ?></th>
						<th><?php esc_html_e( 'Vence', 'caaguazu-portal' ); ?></th>
						<th><?php esc_html_e( 'Usos', 'caaguazu-portal' ); ?></th>
						<th><?php esc_html_e( 'Enlace', 'caaguazu-portal' ); ?></th>
						<th></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $abiertas as $inv ) :
							$enlace = PROMOTUR_Invitations::registration_url( PROMOTUR_Invitations::plain_token( $inv ) );
							?>
							<tr>
								<td><?php echo esc_html( PROMOTUR_Roles::label( $inv['role'] ) ); ?></td>
								<td><?php echo esc_html( PROMOTUR_Invitations::vence_texto( $inv ) ); ?></td>
								<td><?php echo esc_html( PROMOTUR_Invitations::usos_texto( $inv ) ); ?></td>
								<td>
									<?php if ( $enlace ) : ?>
										<input type="text" readonly value="<?php echo esc_attr( $enlace ); ?>" style="width:340px" onclick="this.select()">
										<button type="button" class="button" data-promotur-copiar="<?php echo esc_attr( $enlace ); ?>"><?php esc_html_e( 'Copiar', 'caaguazu-portal' ); ?></button>
									<?php endif; ?>
								</td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline"
										  onsubmit="return confirm('<?php echo esc_js( __( 'El enlace deja de servir. ¿Seguimos?', 'caaguazu-portal' ) ); ?>');">
										<?php wp_nonce_field( 'promotur_admin_invite' ); ?>
										<input type="hidden" name="action" value="promotur_admin_invite">
										<input type="hidden" name="op" value="revoke">
										<input type="hidden" name="invitacion" value="<?php echo esc_attr( $inv['id'] ); ?>">
										<button type="submit" class="button"><?php esc_html_e( 'Revocar', 'caaguazu-portal' ); ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<script>
		// Un botón de copiar mínimo: esta pantalla de wp-admin no carga el
		// bundle del panel (ese sólo se encola en las rutas del panel), así
		// que no hay un initCopiar() del que colgarse acá.
		document.querySelectorAll('[data-promotur-copiar]').forEach(function (boton) {
			boton.addEventListener('click', function () {
				var texto = boton.getAttribute('data-promotur-copiar');
				var listo = function () {
					var original = boton.textContent;
					boton.textContent = '<?php echo esc_js( __( 'Copiado', 'caaguazu-portal' ) ); ?>';
					setTimeout(function () { boton.textContent = original; }, 1600);
				};
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(texto).then(listo, function () {
						var caja = document.createElement('textarea');
						caja.value = texto; caja.style.position = 'fixed'; caja.style.opacity = '0';
						document.body.appendChild(caja); caja.select();
						try { document.execCommand('copy'); listo(); } catch (e) {}
						document.body.removeChild(caja);
					});
				}
			});
		});
		</script>
		<?php
	}

	public function handle_invite() {
		$this->guard_invitaciones();
		check_admin_referer( 'promotur_admin_invite' );
		$op = sanitize_key( wp_unslash( $_POST['op'] ?? '' ) );

		if ( 'create' === $op ) {
			$role     = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : 'promotur_mini';
			// Vacío o 0 en cualquiera de los dos: sin vencimiento / sin límite
			// de usos. create() lo entiende igual.
			$dias     = isset( $_POST['expires_days'] ) ? (int) $_POST['expires_days'] : 0;
			$usos_max = isset( $_POST['max_usos'] ) ? (int) $_POST['max_usos'] : 1;
			$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$tokens   = PROMOTUR_Invitations::create( array( 'role' => $role, 'expires_days' => $dias, 'max_usos' => $usos_max, 'email' => $email, 'count' => 1 ) );
			if ( empty( $tokens ) ) {
				// Ver el porqué en PROMOTUR_Invitations::create(): un enlace que
				// se muestra sin fila detrás es peor que un error.
				$this->notice( __( 'No se pudo crear la invitación: la base de datos rechazó el registro. Revisá que las tablas del plugin estén al día.', 'caaguazu-portal' ), 'error' );
			} else {
				$this->notice( __( 'Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».', 'caaguazu-portal' ) );
			}
		} elseif ( 'revoke' === $op ) {
			$id = isset( $_POST['invitacion'] ) ? (int) $_POST['invitacion'] : 0;
			if ( $id && PROMOTUR_Invitations::get( $id ) ) {
				PROMOTUR_Invitations::revoke( $id );
				$this->notice( __( 'Invitación revocada. Ese enlace ya no sirve.', 'caaguazu-portal' ) );
			} else {
				$this->notice( __( 'Esa invitación ya no existe.', 'caaguazu-portal' ), 'error' );
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=promotur-invitaciones' ) );
		exit;
	}

	/* ================= ACTUALIZACIONES ================= */
	private function guard_updates() {
		if ( ! current_user_can( self::CAP_UPDATES ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ) );
		}
	}

	public function render_updates() {
		$this->guard_updates();

		$updater = function_exists( 'promotur_updater' ) ? promotur_updater() : null;

		// Versión instalada (header del plugin) y datos del plugin.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$data      = get_plugin_data( PROMOTUR_FILE, false, false );
		$installed = $data['Version'] ? $data['Version'] : ( defined( 'PROMOTUR_VERSION' ) ? PROMOTUR_VERSION : '0' );

		// Última versión disponible (caché del updater) y última comprobación.
		$update      = $updater ? $updater->getUpdate() : null;
		$last_check  = 0;
		if ( $updater && method_exists( $updater, 'getUpdateState' ) ) {
			$state      = $updater->getUpdateState();
			$last_check = $state ? (int) $state->getLastCheck() : 0;
		}

		// Estado del token.
		$token_const = defined( 'PROMOTUR_GITHUB_TOKEN' ) && PROMOTUR_GITHUB_TOKEN;
		$token_opt   = (string) get_option( 'promotur_github_token', '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Actualizaciones del portal', 'caaguazu-portal' ); ?></h1>
			<?php $this->show_notice(); ?>

			<?php if ( ! $updater ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.', 'caaguazu-portal' ); ?></p></div>
			<?php endif; ?>

			<?php if ( defined( 'PROMOTUR_VERSION' ) && version_compare( $installed, PROMOTUR_VERSION, '!=' ) ) : ?>
				<div class="notice notice-warning"><p>
					<?php printf(
						/* translators: 1: header version, 2: constant version */
						esc_html__( 'Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.', 'caaguazu-portal' ),
						esc_html( $installed ),
						esc_html( PROMOTUR_VERSION )
					); ?>
				</p></div>
			<?php endif; ?>

			<table class="widefat striped" style="max-width:680px">
				<tbody>
					<tr><th style="width:200px"><?php esc_html_e( 'Versión instalada', 'caaguazu-portal' ); ?></th><td><code><?php echo esc_html( $installed ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Última disponible', 'caaguazu-portal' ); ?></th><td>
						<?php if ( $update && ! empty( $update->version ) ) : ?>
							<code><?php echo esc_html( $update->version ); ?></code>
							<?php
							$link = wp_nonce_url(
								self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( PROMOTUR_BASENAME ) ),
								'upgrade-plugin_' . PROMOTUR_BASENAME
							);
							?>
							&nbsp;<a class="button button-primary" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Actualizar ahora', 'caaguazu-portal' ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Estás al día.', 'caaguazu-portal' ); ?>
						<?php endif; ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Última comprobación', 'caaguazu-portal' ); ?></th><td>
						<?php echo $last_check ? esc_html( date_i18n( 'Y-m-d H:i', $last_check ) ) : esc_html__( 'nunca', 'caaguazu-portal' ); ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Repositorio', 'caaguazu-portal' ); ?></th><td><a href="<?php echo esc_url( PROMOTUR_REPO ); ?>" target="_blank" rel="noopener"><?php echo esc_html( PROMOTUR_REPO ); ?></a></td></tr>
				</tbody>
			</table>

			<p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'promotur_admin_updates' ); ?>
					<input type="hidden" name="action" value="promotur_admin_updates">
					<input type="hidden" name="op" value="check">
					<button class="button"><?php esc_html_e( 'Buscar actualizaciones ahora', 'caaguazu-portal' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'promotur_admin_updates' ); ?>
					<input type="hidden" name="action" value="promotur_admin_updates">
					<input type="hidden" name="op" value="reset">
					<button class="button"><?php esc_html_e( 'Limpiar caché del actualizador', 'caaguazu-portal' ); ?></button>
				</form>
			</p>

			<h2><?php esc_html_e( 'Token de GitHub', 'caaguazu-portal' ); ?></h2>
			<?php if ( $token_const ) : ?>
				<p><?php esc_html_e( 'Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.', 'caaguazu-portal' ); ?></p>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.', 'caaguazu-portal' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'promotur_admin_updates' ); ?>
					<input type="hidden" name="action" value="promotur_admin_updates">
					<input type="hidden" name="op" value="save_token">
					<table class="form-table"><tbody>
						<tr><th><?php esc_html_e( 'Token', 'caaguazu-portal' ); ?></th><td>
							<input type="password" name="github_token" class="regular-text" autocomplete="off" placeholder="<?php echo $token_opt ? esc_attr__( '•••• guardado (dejá vacío para conservarlo)', 'caaguazu-portal' ) : 'ghp_…'; ?>">
							<?php if ( $token_opt ) : ?>
								<p><label><input type="checkbox" name="clear_token" value="1"> <?php esc_html_e( 'Eliminar el token guardado', 'caaguazu-portal' ); ?></label></p>
							<?php endif; ?>
						</td></tr>
					</tbody></table>
					<p><button class="button button-primary"><?php esc_html_e( 'Guardar token', 'caaguazu-portal' ); ?></button></p>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	public function handle_updates() {
		$this->guard_updates();
		check_admin_referer( 'promotur_admin_updates' );
		$op      = sanitize_key( wp_unslash( $_POST['op'] ?? '' ) );
		$updater = function_exists( 'promotur_updater' ) ? promotur_updater() : null;

		switch ( $op ) {
			case 'check':
				if ( $updater ) {
					$update = $updater->checkForUpdates();
					delete_site_transient( 'update_plugins' );
					if ( $update && ! empty( $update->version ) ) {
						$this->notice( sprintf( __( 'Hay una nueva versión disponible: %s.', 'caaguazu-portal' ), '<code>' . esc_html( $update->version ) . '</code>' ) );
					} else {
						$this->notice( __( 'No hay actualizaciones: ya tenés la última versión.', 'caaguazu-portal' ) );
					}
				} else {
					$this->notice( __( 'El verificador de actualizaciones no está disponible.', 'caaguazu-portal' ), 'error' );
				}
				break;

			case 'reset':
				if ( $updater && method_exists( $updater, 'resetUpdateState' ) ) {
					$updater->resetUpdateState();
				}
				delete_site_transient( 'update_plugins' );
				$this->notice( __( 'Caché del actualizador limpiada.', 'caaguazu-portal' ) );
				break;

			case 'save_token':
				if ( defined( 'PROMOTUR_GITHUB_TOKEN' ) && PROMOTUR_GITHUB_TOKEN ) {
					$this->notice( __( 'El token está definido en wp-config.php y no se puede cambiar desde acá.', 'caaguazu-portal' ), 'error' );
					break;
				}
				$clear = ! empty( $_POST['clear_token'] );
				$token = sanitize_text_field( wp_unslash( $_POST['github_token'] ?? '' ) );
				if ( $clear ) {
					delete_option( 'promotur_github_token' );
					$this->notice( __( 'Token eliminado.', 'caaguazu-portal' ) );
				} elseif ( '' !== $token ) {
					update_option( 'promotur_github_token', $token, false );
					$this->notice( __( 'Token guardado.', 'caaguazu-portal' ) );
				} else {
					$this->notice( __( 'No hubo cambios en el token.', 'caaguazu-portal' ) );
				}
				if ( class_exists( 'PROMOTUR_Audit' ) ) {
					PROMOTUR_Audit::log( 'update_settings', array( 'entity_type' => 'plugin' ) );
				}
				break;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=promotur-updates' ) );
		exit;
	}
}
