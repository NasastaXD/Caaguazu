<?php
/**
 * Plugin Name:       Caaguazú Web (espejo iOS)
 * Plugin URI:        https://caaguazu.net
 * Description:       Sirve el espejo web de la app de turismo (HTML/CSS/JS sin build) bajo /ios/, para darle algo instalable a quien usa iPhone mientras no exista una app nativa. Temporal a propósito: se desinstala el día que esa app exista.
 * Version:           1.0.0
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

define( 'CZUWIOS_VERSION', '1.0.0' );
define( 'CZUWIOS_FILE', __FILE__ );
define( 'CZUWIOS_DIR', plugin_dir_path( __FILE__ ) );

/** Dónde vive: caaguazu.net/ios/. Un solo lugar si algún día conviene otro. */
define( 'CZUWIOS_BASE', 'ios' );

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
