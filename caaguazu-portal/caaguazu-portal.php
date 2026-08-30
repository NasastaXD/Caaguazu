<?php
/**
 * Plugin Name:       Caaguazú Portal — Promotores Turísticos
 * Plugin URI:        https://turismo.caaguazu.net
 * Description:       Panel autenticado tipo app bajo /turismo-panel, instalable como PWA, donde el equipo escribe las tres cosas que la aplicación muestra —fichas del inventario turístico, artículos y recorridos— con un mismo flujo editorial: borrador → revisión → publicación. Corre sobre rutas propias y no depende del theme: trae su propio CSS y su propia tipografía, y desencola los del theme activo en sus rutas. La identidad de los promotores corre sobre el sistema de cuentas universal (caaguazu-cuentas): no son usuarios de WordPress.
 * Version:           3.9.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  caaguazu-cuentas
 * Author:            Municipalidad de Caaguazú
 * Author URI:        https://caaguazu.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       caaguazu-portal
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'PROMOTUR_VERSION', '3.9.4' );
define( 'PROMOTUR_DB_VERSION', 3 ); // se incrementa cuando cambia la estructura de datos.
define( 'PROMOTUR_FILE', __FILE__ );
define( 'PROMOTUR_DIR', plugin_dir_path( __FILE__ ) );
define( 'PROMOTUR_URI', plugin_dir_url( __FILE__ ) );
define( 'PROMOTUR_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Slug base del panel. TODO el panel —secciones, auth y PWA— cuelga de acá:
 * /turismo-panel, /turismo-panel/entrar, /turismo-panel/sw.js…
 *
 * Fuente única: el router arma las reglas de reescritura con esta constante y
 * promotur_url() arma las URLs con la misma, así que mover el panel de lugar
 * es cambiar una línea (y un flush de rewrite rules, que el plugin ya hace
 * solo al detectar un cambio de versión).
 */
define( 'PROMOTUR_BASE', 'turismo-panel' );

/** Repositorio público desde el que se publican los releases (auto-update). */
define( 'PROMOTUR_REPO', 'https://github.com/NasastaXD/Caaguazu/' );

/** Nombre del zip que el updater busca adjunto a cada release. */
define( 'PROMOTUR_ASSET', 'caaguazu-portal.zip' );

require_once PROMOTUR_DIR . 'includes/helpers.php';
require_once PROMOTUR_DIR . 'includes/class-roles.php';
require_once PROMOTUR_DIR . 'includes/class-install.php';
require_once PROMOTUR_DIR . 'includes/class-acciones.php';
require_once PROMOTUR_DIR . 'includes/class-router.php';
require_once PROMOTUR_DIR . 'includes/class-shell.php';
require_once PROMOTUR_DIR . 'includes/class-assets.php';
require_once PROMOTUR_DIR . 'includes/class-pwa.php';
require_once PROMOTUR_DIR . 'includes/class-invitations.php';
require_once PROMOTUR_DIR . 'includes/class-audit.php';
require_once PROMOTUR_DIR . 'includes/class-auth.php';
require_once PROMOTUR_DIR . 'includes/class-admin.php';
require_once PROMOTUR_DIR . 'includes/class-notifications.php';
require_once PROMOTUR_DIR . 'includes/class-destinos.php';
require_once PROMOTUR_DIR . 'includes/class-articulos.php';
require_once PROMOTUR_DIR . 'includes/class-recorridos.php';
require_once PROMOTUR_DIR . 'includes/class-editorial.php';
require_once PROMOTUR_DIR . 'includes/class-estados.php';
require_once PROMOTUR_DIR . 'includes/class-ajax.php';
require_once PROMOTUR_DIR . 'includes/class-tareas.php';
require_once PROMOTUR_DIR . 'includes/class-stats.php';
require_once PROMOTUR_DIR . 'includes/class-gestion-ajax.php';
/*
 * class-app-control.php NO se carga: la sección «App» está fuera de
 * circulación (ver promotur_app_api_activa() en includes/helpers.php). El
 * archivo queda en el repo, con su plantilla, para volver a enchufarlo.
 */
require_once PROMOTUR_DIR . 'includes/class-cuenta.php';
require_once PROMOTUR_DIR . 'includes/class-medios.php';
require_once PROMOTUR_DIR . 'includes/class-estructura.php';
require_once PROMOTUR_DIR . 'includes/class-equipo.php';

/**
 * ¿Está activo el sistema de cuentas universal (caaguazu-cuentas)?
 *
 * Dependencia dura desde el cutover de identidad: el Portal ya no autentica
 * con usuarios de WordPress, así que sin caaguazu-cuentas activo el panel no
 * puede funcionar (login, capabilities y sesión dependen de su API).
 *
 * @return bool
 */
function promotur_cuentas_active() {
	return function_exists( 'caaguazu_is_logged_in' ) && class_exists( 'Caaguazu_Cuentas_Install' );
}

/**
 * Aviso en wp-admin si falta la dependencia dura de arriba.
 */
function promotur_missing_cuentas_notice() {
	if ( promotur_cuentas_active() ) { return; }
	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.', 'caaguazu-portal' ) .
		'</p></div>';
}
add_action( 'admin_notices', 'promotur_missing_cuentas_notice' );

/**
 * Registra el panel "promotor" en el sistema de cuentas universal
 * (caaguazu-cuentas). Reusa la misma definición de roles/caps de
 * PROMOTUR_Roles — una sola fuente de verdad — para que el grant de una
 * cuenta en este panel traiga exactamente las mismas capabilities que antes
 * tenía el rol de WordPress equivalente.
 */
function promotur_register_account_panel() {
	if ( ! promotur_cuentas_active() ) {
		return;
	}
	$roles = array();
	foreach ( PROMOTUR_Roles::roles() as $key => $def ) {
		$roles[ $key ] = array( 'label' => $def['label'], 'caps' => $def['caps'] );
	}
	caaguazu_register_panel( 'promotor', array(
		'label' => __( 'Portal de Promotores', 'caaguazu-portal' ),
		'roles' => $roles,
	) );
}
add_action( 'plugins_loaded', 'promotur_register_account_panel', 6 ); // después de caaguazu_cuentas_boot (prioridad 5).

/**
 * Arranque del plugin.
 */
function promotur_boot() {
	if ( ! promotur_cuentas_active() ) {
		// Sin caaguazu-cuentas no hay login/capabilities posibles: no se
		// levanta el router/shell (evita fatal errors por funciones
		// inexistentes) — sólo queda el aviso de wp-admin de arriba.
		return;
	}
	PROMOTUR_Roles::instance();
	PROMOTUR_Router::instance();
	PROMOTUR_Shell::instance();
	PROMOTUR_Assets::instance();
	PROMOTUR_PWA::instance();
	PROMOTUR_Auth::instance();
	PROMOTUR_Notifications::instance();
	PROMOTUR_Destinos::instance();
	PROMOTUR_Articulos::instance();
	PROMOTUR_Recorridos::instance();
	PROMOTUR_Editorial::instance();
	PROMOTUR_Estados::instance();
	PROMOTUR_Ajax::instance();
	PROMOTUR_Tareas::instance();
	PROMOTUR_Stats::instance();
	PROMOTUR_Gestion_Ajax::instance();
	PROMOTUR_Cuenta::instance();
	PROMOTUR_Medios::instance();
	PROMOTUR_Estructura::instance();
	PROMOTUR_Equipo::instance();
	PROMOTUR_Audit::instance();
	if ( is_admin() ) {
		PROMOTUR_Admin::instance();
	}

	promotur_init_updater();
}
add_action( 'plugins_loaded', 'promotur_boot' );

/**
 * Las reglas de reescritura del panel, presentes siempre.
 *
 * Antes esto corría sólo en `admin_init` y sólo al cambiar de versión, y eso
 * tenía un agujero que se veía en la cara: hasta que alguien entrara a
 * wp-admin, `/turismo-panel` no existía como regla, WordPress lo resolvía como
 * 404 y el theme —que hoy tiene una sola plantilla— pintaba la página de obra
 * encima del panel. Lo mismo pasaba después de restaurar una base, de cambiar
 * los enlaces permanentes, o de instalar el plugin sin pasar por el escritorio.
 *
 * Ahora se comprueba en `init` que la regla base esté realmente guardada, y si
 * no está se vacía una vez. `get_option( 'rewrite_rules' )` está en el
 * autoload, así que la comprobación no agrega una consulta; el flush, que sí
 * es caro, sólo ocurre cuando falta de verdad.
 */
function promotur_asegurar_rewrite_rules() {
	$version_nueva = get_option( 'promotur_version' ) !== PROMOTUR_VERSION;

	/*
	 * SE COMPRUEBAN TODAS, NO UNA. Esto miraba sólo la regla del inicio del
	 * panel, y ese atajo deja pasar el estado más difícil de diagnosticar que
	 * tiene el plugin: con la regla base presente y la de
	 * `/turismo-panel/datos/` ausente, las pantallas se dibujan perfecto y nada
	 * de lo que se guarda funciona — la regla genérica de sección se come esas
	 * URLs y el shell devuelve su página 404 en HTML, que el JavaScript no
	 * puede parsear. Comprobar el juego completo cuesta lo mismo que comprobar
	 * una: `rewrite_rules` ya está en memoria.
	 *
	 * Y SE COMPRUEBA EL ORDEN, no sólo que estén. Comprobar presencia sola
	 * también tenía un punto ciego, y costó caro: con el comodín de sección
	 * guardado ANTES de las rutas de auth —todas presentes, todas inalcanzables
	 * porque el comodín matchea primero— el login redirigía al login y el
	 * navegador cortaba por «demasiadas redirecciones». Estaban las 22 reglas;
	 * lo que estaba mal era en qué orden. WP se queda con la primera que
	 * matchea, así que el orden ES parte de la definición.
	 */
	$reglas = get_option( 'rewrite_rules' );
	$mal    = ! is_array( $reglas );
	if ( ! $mal ) {
		$esperadas = array_keys( PROMOTUR_Router::reglas() );
		foreach ( $esperadas as $patron ) {
			if ( ! isset( $reglas[ $patron ] ) ) {
				$mal = true;
				break;
			}
		}
		// Mismas reglas, mismo orden relativo entre ellas. Las de WordPress y
		// las de otros plugins se ignoran: pueden estar intercaladas.
		if ( ! $mal ) {
			$guardadas = array_values( array_intersect( array_keys( $reglas ), $esperadas ) );
			$mal       = ( $guardadas !== $esperadas );
		}
	}

	if ( ! $version_nueva && ! $mal ) {
		return;
	}

	flush_rewrite_rules();

	/*
	 * Y se tira el caché de página, porque acaba de cambiar a dónde va una URL.
	 *
	 * Sin esto, arreglar el enrutado no arregla nada de cara a quien entra: el
	 * caché sigue sirviendo la respuesta vieja —una redirección, un 404— hasta
	 * que expire. Pasó exactamente eso: con el enrutado ya corregido y subido,
	 * `/turismo-panel/entrar` seguía devolviendo el bucle de redirecciones
	 * desde el caché de LiteSpeed, y la página buena sólo aparecía agregándole
	 * un parámetro cualquiera a la URL.
	 *
	 * Los dos son do_action / comprobaciones blandas: si el plugin de caché no
	 * está, no pasa nada.
	 */
	do_action( 'litespeed_purge_all' );          // LiteSpeed Cache v3+
	if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }  // WP Rocket
	if ( function_exists( 'w3tc_flush_all' ) )      { w3tc_flush_all(); }       // W3 Total Cache
	if ( function_exists( 'wp_cache_clear_cache' ) ) { wp_cache_clear_cache(); } // WP Super Cache

	update_option( 'promotur_version', PROMOTUR_VERSION );
}
// Prioridad 20: después de que el router (10) y los CPTs registraron las suyas,
// para que el flush escriba el juego completo y no uno a medias.
add_action( 'init', 'promotur_asegurar_rewrite_rules', 20 );

/**
 * Traducciones.
 */
function promotur_load_textdomain() {
	load_plugin_textdomain( 'caaguazu-portal', false, dirname( PROMOTUR_BASENAME ) . '/languages' );
}
add_action( 'init', 'promotur_load_textdomain' );

/**
 * Token de GitHub para el auto-updater (repos privados / límite de rate más alto).
 * La constante PROMOTUR_GITHUB_TOKEN (wp-config.php) tiene prioridad sobre la opción
 * editable desde wp-admin → Portal Turismo → Actualizaciones.
 *
 * @return string Token, o cadena vacía si no hay ninguno configurado.
 */
function promotur_github_token() {
	if ( defined( 'PROMOTUR_GITHUB_TOKEN' ) && PROMOTUR_GITHUB_TOKEN ) {
		return (string) PROMOTUR_GITHUB_TOKEN;
	}
	return (string) get_option( 'promotur_github_token', '' );
}

/**
 * Accesor a la instancia del auto-updater (plugin-update-checker).
 * La página de Actualizaciones la usa para consultar versión/estado y forzar comprobaciones.
 *
 * @return \YahnisElsts\PluginUpdateChecker\v5p6\Plugin\UpdateChecker|null
 */
function promotur_updater() {
	static $updater = null;
	static $built   = false;

	if ( $built ) {
		return $updater;
	}
	$built = true;

	$loader = PROMOTUR_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $loader ) ) {
		return null;
	}
	require_once $loader;
	if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		return null;
	}

	$updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		PROMOTUR_REPO,
		PROMOTUR_FILE,
		'caaguazu-portal'
	);

	// Usar el .zip adjunto al release (no el zip del código fuente del repo).
	$api = method_exists( $updater, 'getVcsApi' ) ? $updater->getVcsApi() : null;
	if ( $api && method_exists( $api, 'enableReleaseAssets' ) ) {
		$api->enableReleaseAssets( '/' . preg_quote( PROMOTUR_ASSET, '/' ) . '$/i' );
	}

	/**
	 * En este repositorio conviven dos cosas que se publican por separado: el
	 * theme del sitio y este plugin. El updater saca la versión del tag del
	 * release, así que si agarrara el release del theme (v5.x) creería que hay
	 * una versión nueva del plugin (3.x) y descargaría lo que no es.
	 *
	 * La regla es simple y no depende de cómo se llamen los tags: sólo cuenta
	 * un release que traiga adjunto el zip de ESTE plugin. El de al lado trae
	 * el del theme y queda descartado.
	 */
	if ( $api && method_exists( $api, 'setReleaseFilter' ) ) {
		$api->setReleaseFilter( function ( $version, $release ) {
			foreach ( isset( $release->assets ) ? (array) $release->assets : array() as $asset ) {
				if ( isset( $asset->name ) && PROMOTUR_ASSET === $asset->name ) {
					return true;
				}
			}
			return false;
		} );
	}

	/**
	 * La versión que anuncia el tag del release.
	 *
	 * Los tags de este repo son `portal-3.1.1` y `theme-5.0.2`: uno por
	 * componente. Antes eran `vX.Y.Z` para los dos, y ahí estaba el problema —
	 * el repo ya traía los tags `v1.x` a `v5.0.1` del sistema viejo, así que
	 * cuando al panel le tocó `v3.1.0` el release no se publicó nunca: el tag
	 * ya existía.
	 *
	 * El detalle que obliga a este filtro: la librería saca la versión del tag
	 * con `ltrim( $tag, 'v' )`, y su plan B —leer el header `Version:` del
	 * archivo principal en el repo— no funciona acá porque el plugin vive en
	 * una subcarpeta y ella lo busca en la raíz. Sin esto, un tag
	 * `portal-3.1.1` se leería como versión «portal-3.1.1» y ninguna
	 * instalación vería jamás la actualización.
	 */
	if ( method_exists( $updater, 'addResultFilter' ) ) {
		$updater->addResultFilter( function ( $info ) {
			if ( isset( $info->version ) && preg_match( '/(\d+(?:\.\d+)*(?:[-+][A-Za-z0-9.]+)?)$/', (string) $info->version, $m ) ) {
				$info->version = $m[1];
			}
			return $info;
		} );
	}

	$token = promotur_github_token();
	if ( $token ) {
		$updater->setAuthentication( $token );
	}

	return $updater;
}

/**
 * Auto-updater desde GitHub Releases (plugin-update-checker vendoreado).
 * Descarga el asset caaguazu-portal.zip adjunto al release más reciente.
 */
function promotur_init_updater() {
	promotur_updater();
}

/**
 * Migraciones de base de datos acumulativas. Se ejecutan al activar y al detectar
 * un cambio de PROMOTUR_DB_VERSION en admin_init (sin intervención manual).
 *
 * @param int $from versión de DB instalada actualmente
 */
function promotur_run_migrations( $from ) {
	// v2: tablas de invitaciones y auditoría (invite-only + logs).
	if ( $from < 2 ) {
		PROMOTUR_Install::create_tables();
	}
	// v3: invitaciones con vencimiento opcional (permanentes) y un límite de
	// usos configurable en vez de una sola vez fija. `create_tables()` sólo
	// agrega columnas que faltan —dbDelta no toca una columna que ya
	// existe—, así que `expires_at` necesita su propio ALTER para dejar de
	// ser NOT NULL en una instalación que ya tenía la tabla de la v2.
	if ( $from < 3 ) {
		global $wpdb;
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'promotur_invitations MODIFY expires_at DATETIME NULL' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		PROMOTUR_Install::create_tables(); // agrega max_usos y usos, que no existían.

		// Backfill: antes de esto una invitación era de un solo uso siempre,
		// sin excepción. Sin este paso, `max_usos` y `usos` quedarían en NULL
		// y 0 —los defaults de columnas nuevas— para TODA fila ya existente,
		// y eso las volvería «sin límite» de golpe: una invitación que ya se
		// había usado (y que por eso hoy figura como agotada) pasaría a
		// verse otra vez válida y reutilizable sin fin. Se preserva el
		// comportamiento de siempre —de un solo uso— y, para la que ya se
		// había usado, que siga contando como agotada.
		$table = $wpdb->prefix . 'promotur_invitations';
		$wpdb->query( "UPDATE {$table} SET max_usos = 1 WHERE max_usos IS NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( "UPDATE {$table} SET usos = 1 WHERE used_at IS NOT NULL AND usos = 0" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
	do_action( 'promotur_run_migrations', $from );
}

/**
 * Detecta un salto de versión de DB y corre las migraciones pendientes.
 */
function promotur_maybe_upgrade() {
	$installed = (int) get_option( 'promotur_db_version', 0 );
	if ( $installed < PROMOTUR_DB_VERSION ) {
		promotur_run_migrations( $installed );
		update_option( 'promotur_db_version', PROMOTUR_DB_VERSION );
	}
}
/*
 * En `init` y no sólo en `admin_init`, que es donde estaba.
 *
 * El motivo de existir de este plugin es que el equipo NO entre a wp-admin:
 * el contenido, las cuentas, la galería y el equipo se manejan en el panel.
 * Con la migración colgada de `admin_init`, un sitio donde nadie abre el
 * escritorio se quedaba con la base vieja para siempre — y una tabla a la que
 * le falta una columna no da un error visible: `$wpdb->insert()` devuelve
 * false y sigue. Así, crear una invitación «funcionaba» (aparecía el enlace)
 * pero no guardaba ninguna fila, y el enlace salía inválido al usarlo.
 *
 * Correrlo en `init` no cuesta: la guarda de arriba es una lectura de opción
 * ya cacheada, y sólo hace trabajo cuando la versión de verdad cambió.
 */
add_action( 'init', 'promotur_maybe_upgrade', 5 ); // antes del router (10).
add_action( 'admin_init', 'promotur_maybe_upgrade' );

register_activation_hook( __FILE__, array( 'PROMOTUR_Install', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PROMOTUR_Install', 'deactivate' ) );
