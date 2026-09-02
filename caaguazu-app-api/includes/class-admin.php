<?php
/**
 * Lo único que este plugin pone en wp-admin: la pantalla de actualizaciones.
 *
 * Hasta la 0.8.0 este plugin no tenía auto-updater —se instalaba a mano,
 * bajando el zip de cada release—, a diferencia de `caaguazu-portal`, que sí
 * lo tiene desde el principio. Con la API sirviendo ya multi-idioma y con
 * releases cada vez más seguidos, quedarse en «bajar y subir a mano» es un
 * paso que alguien se va a olvidar. Esta pantalla es la misma idea que
 * `PROMOTUR_Admin::render_updates()` del panel, recortada a lo que hace
 * falta acá: no hay Registros ni Invitaciones que armar, sólo versión, botón
 * de actualizar y el token de GitHub por si el repo pasa a privado.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Admin {

	private static $instance = null;

	/** Misma capability que usa wp-admin para el resto de las actualizaciones. */
	const CAP = 'update_plugins';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_czuapi_admin_updates', array( $this, 'handle_updates' ) );
	}

	public function menu() {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		add_menu_page(
			__( 'Caaguazú API', 'caaguazu-app-api' ),
			__( 'Caaguazú API', 'caaguazu-app-api' ),
			self::CAP,
			'czuapi-updates',
			array( $this, 'render_updates' ),
			'dashicons-rest-api',
			58 // justo debajo de «Portal Turismo» (57).
		);
	}

	private function notice( $msg, $type = 'success' ) {
		set_transient( 'czuapi_admin_notice_' . get_current_user_id(), array( 'm' => $msg, 't' => $type ), 60 );
	}

	private function show_notice() {
		$n = get_transient( 'czuapi_admin_notice_' . get_current_user_id() );
		if ( ! $n ) {
			return;
		}
		delete_transient( 'czuapi_admin_notice_' . get_current_user_id() );
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $n['t'] ),
			wp_kses_post( $n['m'] )
		);
	}

	private function guard() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-app-api' ) );
		}
	}

	public function render_updates() {
		$this->guard();

		$updater = function_exists( 'czuapi_updater' ) ? czuapi_updater() : null;

		// Versión instalada (header del plugin) y datos del plugin.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$data      = get_plugin_data( CZUAPI_FILE, false, false );
		$installed = $data['Version'] ? $data['Version'] : ( defined( 'CZUAPI_VERSION' ) ? CZUAPI_VERSION : '0' );

		// Última versión disponible (caché del updater) y última comprobación.
		$update     = $updater ? $updater->getUpdate() : null;
		$last_check = 0;
		if ( $updater && method_exists( $updater, 'getUpdateState' ) ) {
			$state      = $updater->getUpdateState();
			$last_check = $state ? (int) $state->getLastCheck() : 0;
		}

		// Estado del token.
		$token_const = defined( 'CZUAPI_GITHUB_TOKEN' ) && CZUAPI_GITHUB_TOKEN;
		$token_opt   = (string) get_option( 'czuapi_github_token', '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Actualizaciones de la API', 'caaguazu-app-api' ); ?></h1>
			<?php $this->show_notice(); ?>

			<?php if ( ! $updater ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.', 'caaguazu-app-api' ); ?></p></div>
			<?php endif; ?>

			<?php if ( defined( 'CZUAPI_VERSION' ) && version_compare( $installed, CZUAPI_VERSION, '!=' ) ) : ?>
				<div class="notice notice-warning"><p>
					<?php
					printf(
						/* translators: 1: header version, 2: constant version */
						esc_html__( 'Atención: la versión del encabezado del plugin (%1$s) no coincide con CZUAPI_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.', 'caaguazu-app-api' ),
						esc_html( $installed ),
						esc_html( CZUAPI_VERSION )
					);
					?>
				</p></div>
			<?php endif; ?>

			<table class="widefat striped" style="max-width:680px">
				<tbody>
					<tr><th style="width:200px"><?php esc_html_e( 'Versión instalada', 'caaguazu-app-api' ); ?></th><td><code><?php echo esc_html( $installed ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Última disponible', 'caaguazu-app-api' ); ?></th><td>
						<?php if ( $update && ! empty( $update->version ) ) : ?>
							<code><?php echo esc_html( $update->version ); ?></code>
							<?php
							$link = wp_nonce_url(
								self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( CZUAPI_BASENAME ) ),
								'upgrade-plugin_' . CZUAPI_BASENAME
							);
							?>
							&nbsp;<a class="button button-primary" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Actualizar ahora', 'caaguazu-app-api' ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Estás al día.', 'caaguazu-app-api' ); ?>
						<?php endif; ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Última comprobación', 'caaguazu-app-api' ); ?></th><td>
						<?php echo $last_check ? esc_html( date_i18n( 'Y-m-d H:i', $last_check ) ) : esc_html__( 'nunca', 'caaguazu-app-api' ); ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Repositorio', 'caaguazu-app-api' ); ?></th><td><a href="<?php echo esc_url( CZUAPI_REPO ); ?>" target="_blank" rel="noopener"><?php echo esc_html( CZUAPI_REPO ); ?></a></td></tr>
				</tbody>
			</table>

			<p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'czuapi_admin_updates' ); ?>
					<input type="hidden" name="action" value="czuapi_admin_updates">
					<input type="hidden" name="op" value="check">
					<button class="button"><?php esc_html_e( 'Buscar actualizaciones ahora', 'caaguazu-app-api' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'czuapi_admin_updates' ); ?>
					<input type="hidden" name="action" value="czuapi_admin_updates">
					<input type="hidden" name="op" value="reset">
					<button class="button"><?php esc_html_e( 'Limpiar caché del actualizador', 'caaguazu-app-api' ); ?></button>
				</form>
			</p>

			<h2><?php esc_html_e( 'Token de GitHub', 'caaguazu-app-api' ); ?></h2>
			<?php if ( $token_const ) : ?>
				<p><?php esc_html_e( 'Definido en wp-config.php mediante CZUAPI_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.', 'caaguazu-app-api' ); ?></p>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.', 'caaguazu-app-api' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'czuapi_admin_updates' ); ?>
					<input type="hidden" name="action" value="czuapi_admin_updates">
					<input type="hidden" name="op" value="save_token">
					<table class="form-table"><tbody>
						<tr><th><?php esc_html_e( 'Token', 'caaguazu-app-api' ); ?></th><td>
							<input type="password" name="github_token" class="regular-text" autocomplete="off" placeholder="<?php echo $token_opt ? esc_attr__( '•••• guardado (dejá vacío para conservarlo)', 'caaguazu-app-api' ) : 'ghp_…'; ?>">
							<?php if ( $token_opt ) : ?>
								<p><label><input type="checkbox" name="clear_token" value="1"> <?php esc_html_e( 'Eliminar el token guardado', 'caaguazu-app-api' ); ?></label></p>
							<?php endif; ?>
						</td></tr>
					</tbody></table>
					<p><button class="button button-primary"><?php esc_html_e( 'Guardar token', 'caaguazu-app-api' ); ?></button></p>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	public function handle_updates() {
		$this->guard();
		check_admin_referer( 'czuapi_admin_updates' );
		$op      = sanitize_key( wp_unslash( $_POST['op'] ?? '' ) );
		$updater = function_exists( 'czuapi_updater' ) ? czuapi_updater() : null;

		switch ( $op ) {
			case 'check':
				if ( $updater ) {
					$update = $updater->checkForUpdates();
					delete_site_transient( 'update_plugins' );
					if ( $update && ! empty( $update->version ) ) {
						$this->notice( sprintf( __( 'Hay una nueva versión disponible: %s.', 'caaguazu-app-api' ), '<code>' . esc_html( $update->version ) . '</code>' ) );
					} else {
						$this->notice( __( 'No hay actualizaciones: ya tenés la última versión.', 'caaguazu-app-api' ) );
					}
				} else {
					$this->notice( __( 'El verificador de actualizaciones no está disponible.', 'caaguazu-app-api' ), 'error' );
				}
				break;

			case 'reset':
				if ( $updater && method_exists( $updater, 'resetUpdateState' ) ) {
					$updater->resetUpdateState();
				}
				delete_site_transient( 'update_plugins' );
				$this->notice( __( 'Caché del actualizador limpiada.', 'caaguazu-app-api' ) );
				break;

			case 'save_token':
				if ( defined( 'CZUAPI_GITHUB_TOKEN' ) && CZUAPI_GITHUB_TOKEN ) {
					$this->notice( __( 'El token está definido en wp-config.php y no se puede cambiar desde acá.', 'caaguazu-app-api' ), 'error' );
					break;
				}
				$clear = ! empty( $_POST['clear_token'] );
				$token = sanitize_text_field( wp_unslash( $_POST['github_token'] ?? '' ) );
				if ( $clear ) {
					delete_option( 'czuapi_github_token' );
					$this->notice( __( 'Token eliminado.', 'caaguazu-app-api' ) );
				} elseif ( '' !== $token ) {
					update_option( 'czuapi_github_token', $token, false );
					$this->notice( __( 'Token guardado.', 'caaguazu-app-api' ) );
				} else {
					$this->notice( __( 'No hubo cambios en el token.', 'caaguazu-app-api' ) );
				}
				break;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=czuapi-updates' ) );
		exit;
	}
}
