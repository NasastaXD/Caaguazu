<?php
/**
 * Plugin Name:       Caaguazú Web (espejo iOS)
 * Plugin URI:        https://caaguazu.net
 * Description:       Sirve el espejo web de la app de turismo (HTML/CSS/JS sin build) bajo /ios/, para darle algo instalable a quien usa iPhone mientras no exista una app nativa. Temporal a propósito: se desinstala el día que esa app exista.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Municipalidad de Caaguazú
 * Author URI:        https://caaguazu.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       caaguazu-web-ios
 *
 * ---------------------------------------------------------------------------
 * POR QUÉ UN PLUGIN Y NO GITHUB PAGES NI UN SUBDOMINIO
 *
 * `caaguazu-web/` —el sitio en sí— se pensó primero para hostearse aparte
 * (GitHub Pages, un subdominio, Netlify). Eso quedó descartado: requiere
 * acceso a DNS y a un panel de hosting que esta sesión no tiene, y que nadie
 * tenía ganas de tramitar para algo temporal. Sirviéndolo desde acá, en el
 * mismo dominio, no hace falta ninguno de los dos: se instala como cualquier
 * otro plugin de este ecosistema y ya está online en `caaguazu.net/ios/`.
 *
 * POR QUÉ ES UN PLUGIN APARTE Y NO UN TOGGLE DEL THEME
 *
 * El theme del sitio está para rehacerse por completo (ver README del repo);
 * mezclar ahí una función pensada para durar semanas o meses la deja
 * enganchada a algo que va a cambiar por otros motivos, y complica sacarla el
 * día que ya no haga falta. Como plugin aparte, retirar el espejo es
 * desactivar y borrar esta carpeta — nada que desenredar de otro código, cero
 * base de datos propia (ver class-install.php... que no existe: no hay
 * ninguna).
 *
 * QUÉ HACE, EN UNA LÍNEA
 *
 * Registra `/ios/` como su propio espacio de URLs y sirve ahí, tal cual, los
 * archivos de `sitio/` — el mismo HTML/CSS/JS que iba a vivir en GitHub
 * Pages, sin ningún cambio: ya usaba rutas relativas (`css/estilo.css`,
 * `./index.html` en el manifest), así que mudarlo de un dominio propio a un
 * subdirectorio de éste no rompió nada.
 * ---------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CZUWIOS_VERSION', '1.1.0' );
define( 'CZUWIOS_FILE', __FILE__ );
define( 'CZUWIOS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CZUWIOS_BASENAME', plugin_basename( __FILE__ ) );

/** Dónde vive: caaguazu.net/ios/. Un solo lugar si algún día conviene otro. */
define( 'CZUWIOS_BASE', 'ios' );

/**
 * Repo y nombre del asset para el auto-updater. Mismo repo que
 * `caaguazu-portal` —los cinco componentes conviven ahí—, filtrado por el
 * nombre del zip: ver `czuwios_updater()`.
 */
define( 'CZUWIOS_REPO', 'https://github.com/NasastaXD/Caaguazu/' );
define( 'CZUWIOS_ASSET', 'caaguazu-web-ios.zip' );

/** La carpeta con los archivos de verdad — el espejo, calcado sin tocar. */
define( 'CZUWIOS_SITIO', CZUWIOS_DIR . 'sitio/' );

final class CZUWIOS_Servidor {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'init', array( $this, 'reglas' ) );
		add_action( 'init', array( $this, 'reflushear_si_hace_falta' ) );
		add_action( 'template_redirect', array( $this, 'despachar' ) );
		add_filter( 'redirect_canonical', array( $this, 'sin_canonical' ) );
	}

	/**
	 * Que WordPress no le agregue una barra final a nada de acá.
	 *
	 * `redirect_canonical()` corre en `template_redirect` —el mismo hook
	 * que `despachar()`— y ve `/ios/js/app.js` como un permalink al que le
	 * falta la barra de su estructura, así que manda un 301 a
	 * `/ios/js/app.js/` ANTES de que este plugin llegue a servir nada. El
	 * navegador sigue el redirect y el archivo se sirve igual — pero
	 * `js/app.js` es un módulo ES con imports relativos (`from
	 * "./idioma.js"`), y esos se resuelven contra la URL final, con la
	 * barra puesta: `js/app.js/idioma.js`, un path que no existe. El
	 * import falla, el módulo entero no carga, y la pantalla queda en
	 * blanco sin ningún error a la vista — el único síntoma es que TODO
	 * archivo bajo `/ios/` devuelve 301 antes del 200, cosa que sólo se ve
	 * mirando las cabeceras, nunca desde el navegador.
	 *
	 * Se cancela con el filtro que `redirect_canonical()` ya expone para
	 * esto —devolver `false`— y sólo para pedidos de este plugin: no toca
	 * la canonicalización del resto del sitio.
	 *
	 * @param string|false $redirect_url
	 * @return string|false
	 */
	public function sin_canonical( $redirect_url ) {
		return get_query_var( 'czuwios_archivo' ) ? false : $redirect_url;
	}

	public function query_vars( $vars ) {
		$vars[] = 'czuwios_archivo';
		return $vars;
	}

	/**
	 * Dos reglas nada más, y sin comodín que se coma nada de WordPress: todo
	 * lo que cuelga de `/ios/` es de este plugin, así que no hay con qué
	 * chocar. `index.html` es el shell de una SPA que rutea por hash
	 * (`#/ficha/123`), no por path — el navegador nunca le pide al servidor
	 * una URL distinta de `/ios/` al navegar adentro de la app, sólo cuando
	 * alguien la abre por primera vez o la recarga.
	 */
	public function reglas() {
		add_rewrite_rule( '^' . CZUWIOS_BASE . '/?$', 'index.php?czuwios_archivo=index.html', 'top' );
		add_rewrite_rule( '^' . CZUWIOS_BASE . '/(.+)$', 'index.php?czuwios_archivo=$matches[1]', 'top' );
	}

	/**
	 * Flush de rewrite rules al activar, y también al detectar un cambio de
	 * versión — por si alguien actualiza el plugin subiendo los archivos a
	 * mano en vez de reactivarlo. Un solo dato guardado (la versión), nada
	 * más: no hay tablas ni configuración que este plugin necesite recordar.
	 */
	public function reflushear_si_hace_falta() {
		if ( get_option( 'czuwios_version' ) === CZUWIOS_VERSION ) {
			return;
		}
		flush_rewrite_rules();
		update_option( 'czuwios_version', CZUWIOS_VERSION );
	}

	/**
	 * Content-Type por extensión. Sólo las que existen en `sitio/` — no hace
	 * falta una lista genérica para servir seis tipos de archivo conocidos.
	 *
	 * @return array ext => mime
	 */
	private function tipos() {
		return array(
			'html'        => 'text/html; charset=utf-8',
			'css'         => 'text/css; charset=utf-8',
			'js'          => 'text/javascript; charset=utf-8',
			'json'        => 'application/json; charset=utf-8',
			'webmanifest' => 'application/manifest+json; charset=utf-8',
			'png'         => 'image/png',
		);
	}

	/**
	 * Sirve el archivo pedido, o 404 de WordPress si no es de acá.
	 *
	 * El guardia de verdad es `realpath()` + comprobar que el resultado siga
	 * DENTRO de `sitio/`: un `..` en la URL no alcanza para escapar de la
	 * carpeta, porque lo que decide no es la cadena sino dónde termina
	 * apuntando en el disco. Y sólo se sirve una extensión de la lista de
	 * arriba — nada que no sea exactamente lo que este espejo trae consigo.
	 */
	public function despachar() {
		$pedido = get_query_var( 'czuwios_archivo' );
		if ( ! is_string( $pedido ) || '' === $pedido ) {
			return; // no es una URL de /ios/: seguir como si este plugin no existiera.
		}

		$pedido = ltrim( $pedido, '/' );
		$ext    = strtolower( (string) pathinfo( $pedido, PATHINFO_EXTENSION ) );
		$tipos  = $this->tipos();

		if ( '' === $ext || ! isset( $tipos[ $ext ] ) ) {
			$this->error_404();
		}

		$real_sitio = realpath( CZUWIOS_SITIO );
		$real_pedido = realpath( CZUWIOS_SITIO . $pedido );

		if ( ! $real_sitio || ! $real_pedido || 0 !== strpos( $real_pedido, $real_sitio . DIRECTORY_SEPARATOR ) ) {
			$this->error_404();
		}

		nocache_headers();
		header( 'Content-Type: ' . $tipos[ $ext ] );
		// Los archivos versionan con el plugin entero, así que cachear un rato
		// es seguro: la próxima actualización cambia la versión y con ella el
		// flush de arriba, no hace falta invalidar por archivo.
		header( 'Cache-Control: public, max-age=3600' );
		readfile( $real_pedido ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
		exit;
	}

	private function error_404() {
		status_header( 404 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo 'No encontrado.';
		exit;
	}
}

function czuwios_boot() {
	CZUWIOS_Servidor::instance();
	czuwios_init_updater();
	if ( is_admin() ) {
		require_once CZUWIOS_DIR . 'includes/class-admin.php';
		CZUWIOS_Admin::instance();
	}
}
add_action( 'plugins_loaded', 'czuwios_boot' );

/**
 * Al activar: reglas puestas y flusheadas ya mismo, para no depender de que
 * alguien visite Ajustes → Enlaces permanentes antes de que `/ios/` ande.
 */
function czuwios_activar() {
	CZUWIOS_Servidor::instance()->reglas();
	flush_rewrite_rules();
	update_option( 'czuwios_version', CZUWIOS_VERSION );
}
register_activation_hook( __FILE__, 'czuwios_activar' );

/** Al desactivar: sólo el flush. Nada que borrar — no hay tablas ni opciones más. */
function czuwios_desactivar() {
	delete_option( 'czuwios_version' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'czuwios_desactivar' );

/* ---------------------------------------------------------------------------
 * Auto-updater desde GitHub Releases (plugin-update-checker vendoreado).
 *
 * Mismo mecanismo y mismo repositorio que `caaguazu-portal` y
 * `caaguazu-app-api` —los componentes del ecosistema conviven en
 * `NasastaXD/Caaguazu`—, así que la explicación larga de por qué hace falta
 * cada filtro está en el docblock de `promotur_updater()`. Acá sólo lo que
 * cambia por ser este plugin:
 *
 *   - El asset a buscar es `caaguazu-web-ios.zip`.
 *   - El tag es `web-ios-X.Y.Z`.
 *   - El token de GitHub es una constante y una opción propias
 *     (`CZUWIOS_GITHUB_TOKEN` / `czuwios_github_token`): cada plugin se
 *     instala y se retira por separado, aunque los tres apunten al mismo
 *     repo privado si algún día pasa a serlo.
 *
 * Este plugin es temporal a propósito —se retira el día que exista una app
 * nativa de iOS—, pero mientras exista va a recibir el mismo trato que el
 * resto: sin esto, cada corrección (como la de la 1.0.1) exigía pedirle a
 * alguien con acceso al hosting que baje un zip y lo suba a mano.
 * ------------------------------------------------------------------------ */

/**
 * Token de GitHub para el updater. La constante en wp-config.php gana sobre
 * la opción editable desde wp-admin → Web iOS → Actualizaciones.
 *
 * @return string Token, o cadena vacía si no hay ninguno configurado.
 */
function czuwios_github_token() {
	if ( defined( 'CZUWIOS_GITHUB_TOKEN' ) && CZUWIOS_GITHUB_TOKEN ) {
		return (string) CZUWIOS_GITHUB_TOKEN;
	}
	return (string) get_option( 'czuwios_github_token', '' );
}

/**
 * Accesor a la instancia del auto-updater (plugin-update-checker).
 * La página de Actualizaciones la usa para consultar versión/estado y forzar comprobaciones.
 *
 * @return \YahnisElsts\PluginUpdateChecker\v5p6\Plugin\UpdateChecker|null
 */
function czuwios_updater() {
	static $updater = null;
	static $built   = false;

	if ( $built ) {
		return $updater;
	}
	$built = true;

	$loader = CZUWIOS_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $loader ) ) {
		return null;
	}
	require_once $loader;
	if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		return null;
	}

	$updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		CZUWIOS_REPO,
		CZUWIOS_FILE,
		'caaguazu-web-ios'
	);

	// Usar el .zip adjunto al release (no el zip del código fuente del repo).
	$api = method_exists( $updater, 'getVcsApi' ) ? $updater->getVcsApi() : null;
	if ( $api && method_exists( $api, 'enableReleaseAssets' ) ) {
		$api->enableReleaseAssets( '/' . preg_quote( CZUWIOS_ASSET, '/' ) . '$/i' );
	}

	/*
	 * En este repositorio conviven varias cosas que se publican por separado
	 * (theme, panel, API, SSO, y esto), y sólo un release trae el zip de
	 * ESTE plugin. Sin este filtro, el updater agarraría cualquier release
	 * y ofrecería instalar lo que no es.
	 */
	if ( $api && method_exists( $api, 'setReleaseFilter' ) ) {
		$api->setReleaseFilter( function ( $version, $release ) {
			foreach ( isset( $release->assets ) ? (array) $release->assets : array() as $asset ) {
				if ( isset( $asset->name ) && CZUWIOS_ASSET === $asset->name ) {
					return true;
				}
			}
			return false;
		} );
	}

	/*
	 * La versión que anuncia el tag del release. Los tags de este repo son
	 * `web-ios-1.0.1`, y la librería la saca con `ltrim( $tag, 'v' )` —que
	 * acá no quita nada—, así que sin este filtro toda instalación vería la
	 * «versión disponible» como la cadena `web-ios-1.0.1`, que nunca es
	 * mayor que `1.0.1` para `version_compare()`: ninguna actualización se
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

	$token = czuwios_github_token();
	if ( $token ) {
		$updater->setAuthentication( $token );
	}

	return $updater;
}

/**
 * Auto-updater desde GitHub Releases (plugin-update-checker vendoreado).
 * Descarga el asset caaguazu-web-ios.zip adjunto al release más reciente.
 */
function czuwios_init_updater() {
	czuwios_updater();
}
