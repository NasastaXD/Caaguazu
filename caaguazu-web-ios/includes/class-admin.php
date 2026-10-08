<?php
/**
 * Lo único que este plugin pone en wp-admin: la pantalla de actualizaciones.
 *
 * Mismo motivo que `CZUAPI_Admin` del lado de la API: sin esto, cada
 * corrección —como la de la 1.0.1, que dejaba el sitio en pantalla negra—
 * exigía pedirle a quien tiene acceso al hosting que baje un zip y lo suba a
 * mano. Este plugin es temporal —se retira el día que exista una app nativa
 * de iOS—, pero mientras exista se actualiza como cualquier otro.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUWIOS_Admin {

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
		add_action( 'admin_post_czuwios_admin_updates', array( $this, 'handle_updates' ) );
	}

	public function menu() {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		add_menu_page(
			__( 'Web iOS', 'caaguazu-web-ios' ),
			__( 'Web iOS', 'caaguazu-web-ios' ),
			self::CAP,
			'czuwios-updates',
			array( $this, 'render_updates' ),
			'dashicons-smartphone',
			59 // debajo de «Portal Turismo» (57) y «Caaguazú API» (58).
		);
	}

	private function notice( $msg, $type = 'success' ) {
		set_transient( 'czuwios_admin_notice_' . get_current_user_id(), array( 'm' => $msg, 't' => $type ), 60 );
	}

	private function show_notice() {
		$n = get_transient( 'czuwios_admin_notice_' . get_current_user_id() );
		if ( ! $n ) {
			return;
		}
		delete_transient( 'czuwios_admin_notice_' . get_current_user_id() );
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $n['t'] ),
			wp_kses_post( $n['m'] )
		);
	}

	private function guard() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-web-ios' ) );
		}
	}

	public function render_updates() {
		$this->guard();

		$updater = function_exists( 'czuwios_updater' ) ? czuwios_updater() : null;

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$data      = get_plugin_data( CZUWIOS_FILE, false, false );
		$installed = $data['Version'] ? $data['Version'] : ( defined( 'CZUWIOS_VERSION' ) ? CZUWIOS_VERSION : '0' );

		$update     = $updater ? $updater->getUpdate() : null;
		$last_check = 0;
		if ( $updater && method_exists( $updater, 'getUpdateState' ) ) {
			$state      = $updater->getUpdateState();
			$last_check = $state ? (int) $state->getLastCheck() : 0;
		}

		$token_const = defined( 'CZUWIOS_GITHUB_TOKEN' ) && CZUWIOS_GITHUB_TOKEN;
		$token_opt   = (string) get_option( 'czuwios_github_token', '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Actualizaciones del espejo iOS', 'caaguazu-web-ios' ); ?></h1>
			<?php $this->show_notice(); ?>

			<?php if ( ! $updater ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.', 'caaguazu-web-ios' ); ?></p></div>
			<?php endif; ?>

			<?php if ( defined( 'CZUWIOS_VERSION' ) && version_compare( $installed, CZUWIOS_VERSION, '!=' ) ) : ?>
				<div class="notice notice-warning"><p>
					<?php
					printf(
						/* translators: 1: header version, 2: constant version */
						esc_html__( 'Atención: la versión del encabezado del plugin (%1$s) no coincide con CZUWIOS_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.', 'caaguazu-web-ios' ),
						esc_html( $installed ),
						esc_html( CZUWIOS_VERSION )
					);
					?>
				</p></div>
			<?php endif; ?>

			<table class="widefat striped" style="max-width:680px">
				<tbody>
					<tr><th style="width:200px"><?php esc_html_e( 'Versión instalada', 'caaguazu-web-ios' ); ?></th><td><code><?php echo esc_html( $installed ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Última disponible', 'caaguazu-web-ios' ); ?></th><td>
						<?php if ( $update && ! empty( $update->version ) ) : ?>
							<code><?php echo esc_html( $update->version ); ?></code>
							<?php
							$link = wp_nonce_url(
								self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( CZUWIOS_BASENAME ) ),
								'upgrade-plugin_' . CZUWIOS_BASENAME
							);
							?>
							&nbsp;<a class="button button-primary" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Actualizar ahora', 'caaguazu-web-ios' ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Estás al día.', 'caaguazu-web-ios' ); ?>
						<?php endif; ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Última comprobación', 'caaguazu-web-ios' ); ?></th><td>
						<?php echo $last_check ? esc_html( date_i18n( 'Y-m-d H:i', $last_check ) ) : esc_html__( 'nunca', 'caaguazu-web-ios' ); ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Repositorio', 'caaguazu-web-ios' ); ?></th><td><a href="<?php echo esc_url( CZUWIOS_REPO ); ?>" target="_blank" rel="noopener"><?php echo esc_html( CZUWIOS_REPO ); ?></a></td></tr>
				</tbody>
			</table>

			<p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'czuwios_admin_updates' ); ?>
					<input type="hidden" name="action" value="czuwios_admin_updates">
					<input type="hidden" name="op" value="check">
					<button class="button"><?php esc_html_e( 'Buscar actualizaciones ahora', 'caaguazu-web-ios' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'czuwios_admin_updates' ); ?>
					<input type="hidden" name="action" value="czuwios_admin_updates">
					<input type="hidden" name="op" value="reset">
					<button class="button"><?php esc_html_e( 'Limpiar caché del actualizador', 'caaguazu-web-ios' ); ?></button>
				</form>
			</p>

			<h2><?php esc_html_e( 'Token de GitHub', 'caaguazu-web-ios' ); ?></h2>
			<?php if ( $token_const ) : ?>
				<p><?php esc_html_e( 'Definido en wp-config.php mediante CZUWIOS_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.', 'caaguazu-web-ios' ); ?></p>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.', 'caaguazu-web-ios' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'czuwios_admin_updates' ); ?>
					<input type="hidden" name="action" value="czuwios_admin_updates">
					<input type="hidden" name="op" value="save_token">
					<table class="form-table"><tbody>
						<tr><th><?php esc_html_e( 'Token', 'caaguazu-web-ios' ); ?></th><td>
							<input type="password" name="github_token" class="regular-text" autocomplete="off" placeholder="<?php echo $token_opt ? esc_attr__( '•••• guardado (dejá vacío para conservarlo)', 'caaguazu-web-ios' ) : 'ghp_…'; ?>">
							<?php if ( $token_opt ) : ?>
								<p><label><input type="checkbox" name="clear_token" value="1"> <?php esc_html_e( 'Eliminar el token guardado', 'caaguazu-web-ios' ); ?></label></p>
							<?php endif; ?>
						</td></tr>
					</tbody></table>
					<p><button class="button button-primary"><?php esc_html_e( 'Guardar token', 'caaguazu-web-ios' ); ?></button></p>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	public function handle_updates() {
		$this->guard();
		check_admin_referer( 'czuwios_admin_updates' );
		$op      = sanitize_key( wp_unslash( $_POST['op'] ?? '' ) );
		$updater = function_exists( 'czuwios_updater' ) ? czuwios_updater() : null;

		switch ( $op ) {
			case 'check':
				if ( $updater ) {
					$update = $updater->checkForUpdates();
					delete_site_transient( 'update_plugins' );
					if ( $update && ! empty( $update->version ) ) {
						$this->notice( sprintf( __( 'Hay una nueva versión disponible: %s.', 'caaguazu-web-ios' ), '<code>' . esc_html( $update->version ) . '</code>' ) );
					} else {
						$this->notice( __( 'No hay actualizaciones: ya tenés la última versión.', 'caaguazu-web-ios' ) );
					}
				} else {
					$this->notice( __( 'El verificador de actualizaciones no está disponible.', 'caaguazu-web-ios' ), 'error' );
				}
				break;

			case 'reset':
				if ( $updater && method_exists( $updater, 'resetUpdateState' ) ) {
					$updater->resetUpdateState();
				}
				delete_site_transient( 'update_plugins' );
				$this->notice( __( 'Caché del actualizador limpiada.', 'caaguazu-web-ios' ) );
				break;

			case 'save_token':
				if ( defined( 'CZUWIOS_GITHUB_TOKEN' ) && CZUWIOS_GITHUB_TOKEN ) {
					$this->notice( __( 'El token está definido en wp-config.php y no se puede cambiar desde acá.', 'caaguazu-web-ios' ), 'error' );
					break;
				}
				$clear = ! empty( $_POST['clear_token'] );
				$token = sanitize_text_field( wp_unslash( $_POST['github_token'] ?? '' ) );
				if ( $clear ) {
					delete_option( 'czuwios_github_token' );
					$this->notice( __( 'Token eliminado.', 'caaguazu-web-ios' ) );
				} elseif ( '' !== $token ) {
					update_option( 'czuwios_github_token', $token, false );
					$this->notice( __( 'Token guardado.', 'caaguazu-web-ios' ) );
				} else {
					$this->notice( __( 'No hubo cambios en el token.', 'caaguazu-web-ios' ) );
				}
				break;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=czuwios-updates' ) );
		exit;
	}
}
