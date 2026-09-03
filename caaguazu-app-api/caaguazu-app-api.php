<?php
/**
 * Plugin Name:       Caaguazú App API
 * Plugin URI:        https://caaguazu.net
 * Description:       Capa REST que consume la app Android (Turismo App Czu). Expone el contenido turístico y la identidad del ecosistema bajo /wp-json/czu-app/v1/, sin depender del theme ni del sitio público — la app sigue funcionando aunque la web se rehaga entera.
 * Version:           0.8.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  caaguazu-cuentas, caaguazu-portal
 * Author:            Municipalidad de Caaguazú
 * Author URI:        https://caaguazu.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       caaguazu-app-api
 *
 * ---------------------------------------------------------------------------
 * POR QUÉ ES UN PLUGIN APARTE
 *
 * La app es un cliente más del mismo backend, pero deliberadamente NO comparte
 * ciclo de vida con el sitio público: el theme y las páginas de caaguazu.net
 * van a rehacerse, y ese trabajo no debe poder romper la API de una app ya
 * publicada en la tienda. Por eso esta capa:
 *
 *   - No usa el theme ni sus helpers. Nada de locate_template(), get_header(),
 *     ni de los filtros de nav/shell. Si el theme desaparece, la API sigue.
 *   - No renderiza HTML propio. Solo JSON.
 *   - Lee el contenido de donde ya vive (caaguazu-portal) en vez de duplicarlo.
 *
 * Lo que sí aporta son los campos que la app necesita y no existían (rango de
 * precio, artículos relacionados, icono y color por categoría) y el CPT Evento.
 *
 * Artículo y Recorrido nacieron acá y se mudaron a `caaguazu-portal`: los dos
 * son contenido humano con flujo editorial —se escriben, se revisan, los
 * aprueba el staff— y eso es lo que hace el panel. Mientras vivían acá, había
 * que cargarlos desde wp-admin y el flujo de revisión no los alcanzaba. El
 * `post_type` no cambió, así que no se perdió nada; esta capa los lee y los
 * sirve, y conserva un registro de respaldo por si corriera sin el panel.
 * ---------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CZUAPI_VERSION', '0.8.2' );
define( 'CZUAPI_FILE', __FILE__ );
define( 'CZUAPI_DIR', plugin_dir_path( __FILE__ ) );
define( 'CZUAPI_BASENAME', plugin_basename( __FILE__ ) );

/** Namespace REST. Versionado: un cambio incompatible sube a v2 y v1 sigue viva. */
define( 'CZUAPI_NS', 'czu-app/v1' );

/**
 * Repo y nombre del asset para el auto-updater. Mismo repo que
 * `caaguazu-portal` —los cuatro componentes conviven ahí—, filtrado por el
 * nombre del zip: ver `czuapi_updater()`.
 */
define( 'CZUAPI_REPO', 'https://github.com/NasastaXD/Caaguazu/' );
define( 'CZUAPI_ASSET', 'caaguazu-app-api.zip' );

require_once CZUAPI_DIR . 'includes/class-install.php';
require_once CZUAPI_DIR . 'includes/helpers.php';
require_once CZUAPI_DIR . 'includes/class-response.php';
require_once CZUAPI_DIR . 'includes/class-auth.php';
require_once CZUAPI_DIR . 'includes/class-idiomas.php';
require_once CZUAPI_DIR . 'includes/class-taxonomias.php';
require_once CZUAPI_DIR . 'includes/class-inventario.php';
require_once CZUAPI_DIR . 'includes/class-eventos.php';
require_once CZUAPI_DIR . 'includes/class-articulos.php';
require_once CZUAPI_DIR . 'includes/class-recorridos.php';
require_once CZUAPI_DIR . 'includes/class-ui-content.php';
require_once CZUAPI_DIR . 'includes/class-sync.php';
require_once CZUAPI_DIR . 'includes/class-admin.php';

/**
 * ¿Están las dependencias duras? La API no puede resolver identidad sin
 * caaguazu-cuentas ni contenido sin caaguazu-portal.
 *
 * @return bool
 */
function czuapi_deps_active() {
	return function_exists( 'caaguazu_account_can' )
		&& class_exists( 'Caaguazu_Cuentas_Accounts' )
		&& class_exists( 'PROMOTUR_Destinos' );
}

/**
 * Aviso en wp-admin si falta alguna dependencia.
 */
function czuapi_missing_deps_notice() {
	if ( czuapi_deps_active() ) { return; }
	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'Caaguazú App API necesita "Caaguazú Cuentas" y "Caaguazú Portal" activos. Mientras falte alguno, la API de la app no responde.', 'caaguazu-app-api' ) .
		'</p></div>';
}
add_action( 'admin_notices', 'czuapi_missing_deps_notice' );

/**
 * Arranque. Prioridad 20: después de que caaguazu-cuentas (5) y
 * caaguazu-portal (10) registraron su API y sus CPTs.
 */
function czuapi_boot() {
	if ( ! czuapi_deps_active() ) {
		return;
	}

	// CPTs: se registran siempre (no solo en REST) para que wp-admin pueda
	// verlos. Evento es de esta capa; Artículo y Recorrido los registra el
	// panel y acá sólo queda su respaldo, que no hace nada si el panel llegó
	// primero.
	CZUAPI_Eventos::instance();
	CZUAPI_Articulos::instance();
	CZUAPI_Recorridos::instance();
	CZUAPI_Taxonomias::instance();
	CZUAPI_Sync::instance();

	add_action( 'rest_api_init', 'czuapi_register_routes' );
}
add_action( 'plugins_loaded', 'czuapi_boot', 20 );

/*
 * El updater y su pantalla de wp-admin NO dependen de `czuapi_deps_active()`
 * a propósito: comprobar y bajar una versión nueva de ESTE plugin no necesita
 * que `caaguazu-cuentas` ni `caaguazu-portal` estén activos, y es justo la
 * herramienta que hace falta cuando algo anda mal — no puede quedar atada a
 * que el resto del ecosistema funcione.
 */
function czuapi_boot_admin() {
	czuapi_init_updater();
	if ( is_admin() ) {
		CZUAPI_Admin::instance();
	}
}
add_action( 'plugins_loaded', 'czuapi_boot_admin' );

/**
 * Registro de todas las rutas. Un solo punto para poder auditar la superficie
 * pública de un vistazo.
 */
function czuapi_register_routes() {
	CZUAPI_Auth::instance()->register_routes();
	CZUAPI_Idiomas::instance()->register_routes();
	CZUAPI_Taxonomias::instance()->register_routes();
	CZUAPI_Inventario::instance()->register_routes();
	CZUAPI_Eventos::instance()->register_routes();
	CZUAPI_Articulos::instance()->register_routes();
	CZUAPI_Recorridos::instance()->register_routes();
	CZUAPI_UI_Content::instance()->register_routes();
	CZUAPI_Sync::instance()->register_routes();
}

/**
 * Migración al detectar cambio de versión (sin re-activar).
 *
 * Refresca además las rewrite rules. En admin_init y no en plugins_loaded, que
 * corre en cada visita pública: mismo criterio que caaguazu-portal.
 */
function czuapi_maybe_upgrade() {
	if ( get_option( 'czuapi_version' ) === CZUAPI_VERSION ) {
		return;
	}
	CZUAPI_Install::create_tables();
	flush_rewrite_rules();
	update_option( 'czuapi_version', CZUAPI_VERSION );
}
add_action( 'admin_init', 'czuapi_maybe_upgrade' );

register_activation_hook( __FILE__, array( 'CZUAPI_Install', 'activate' ) );

/* ---------------------------------------------------------------------------
 * Auto-updater desde GitHub Releases (plugin-update-checker vendoreado).
 *
 * Mismo mecanismo y mismo repositorio que `caaguazu-portal` —los cuatro
 * componentes del ecosistema conviven en `NasastaXD/Caaguazu`—, así que la
 * explicación larga de por qué hace falta cada filtro está en el docblock de
 * `promotur_updater()`. Acá sólo lo que cambia por ser este plugin:
 *
 *   - El asset a buscar es `caaguazu-app-api.zip`, no el del panel.
 *   - El tag es `app-api-X.Y.Z`, no `portal-X.Y.Z`.
 *   - El token de GitHub es una constante y una opción propias
 *     (`CZUAPI_GITHUB_TOKEN` / `czuapi_github_token`): los dos plugins no
 *     comparten credencial aunque apunten al mismo repo privado, porque cada
 *     uno se instala y se retira por separado.
 * ------------------------------------------------------------------------ */

/**
 * Token de GitHub para el updater. La constante en wp-config.php gana sobre
 * la opción editable desde wp-admin → Caaguazú API → Actualizaciones.
 *
 * @return string Token, o cadena vacía si no hay ninguno configurado.
 */
function czuapi_github_token() {
	if ( defined( 'CZUAPI_GITHUB_TOKEN' ) && CZUAPI_GITHUB_TOKEN ) {
		return (string) CZUAPI_GITHUB_TOKEN;
	}
	return (string) get_option( 'czuapi_github_token', '' );
}

/**
 * Accesor a la instancia del auto-updater (plugin-update-checker).
 * La página de Actualizaciones la usa para consultar versión/estado y forzar comprobaciones.
 *
 * @return \YahnisElsts\PluginUpdateChecker\v5p6\Plugin\UpdateChecker|null
 */
function czuapi_updater() {
	static $updater = null;
	static $built   = false;

	if ( $built ) {
		return $updater;
	}
	$built = true;

	$loader = CZUAPI_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $loader ) ) {
		return null;
	}
	require_once $loader;
	if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		return null;
	}

	$updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		CZUAPI_REPO,
		CZUAPI_FILE,
		'caaguazu-app-api'
	);

	// Usar el .zip adjunto al release (no el zip del código fuente del repo).
	$api = method_exists( $updater, 'getVcsApi' ) ? $updater->getVcsApi() : null;
	if ( $api && method_exists( $api, 'enableReleaseAssets' ) ) {
		$api->enableReleaseAssets( '/' . preg_quote( CZUAPI_ASSET, '/' ) . '$/i' );
	}

	/*
	 * En este repositorio conviven cuatro cosas que se publican por separado
	 * (theme, panel, esta API y el SSO), y sólo un release trae el zip de
	 * ESTE plugin. Sin este filtro, el updater agarraría cualquier release
	 * —el del theme, el del panel— y ofrecería instalar lo que no es.
	 */
	if ( $api && method_exists( $api, 'setReleaseFilter' ) ) {
		$api->setReleaseFilter( function ( $version, $release ) {
			foreach ( isset( $release->assets ) ? (array) $release->assets : array() as $asset ) {
				if ( isset( $asset->name ) && CZUAPI_ASSET === $asset->name ) {
					return true;
				}
			}
			return false;
		} );
	}

	/*
	 * La versión que anuncia el tag del release. Los tags de este repo son
	 * `app-api-0.8.0`, y la librería la saca con `ltrim( $tag, 'v' )` —que
	 * acá no quita nada—, así que sin este filtro toda instalación vería la
	 * «versión disponible» como la cadena `app-api-0.8.0`, que nunca es mayor
	 * que `0.8.0` para `version_compare()`: ninguna actualización se
	 * ofrecería jamás. El plan B de la librería —leer el header `Version:`
	 * del archivo principal en la raíz del repo— tampoco sirve, porque el
	 * plugin vive en una subcarpeta.
	 */
	if ( method_exists( $updater, 'addResultFilter' ) ) {
		$updater->addResultFilter( function ( $info ) {
			if ( isset( $info->version ) && preg_match( '/(\d+(?:\.\d+)*(?:[-+][A-Za-z0-9.]+)?)$/', (string) $info->version, $m ) ) {
				$info->version = $m[1];
			}
			return $info;
		} );
	}

	$token = czuapi_github_token();
	if ( $token ) {
		$updater->setAuthentication( $token );
	}

	return $updater;
}

/**
 * Auto-updater desde GitHub Releases (plugin-update-checker vendoreado).
 * Descarga el asset caaguazu-app-api.zip adjunto al release más reciente.
 */
function czuapi_init_updater() {
	czuapi_updater();
}
