<?php
/**
 * Plugin Name:       Caaguazú Web turismo
 * Plugin URI:        https://caaguazu.net
 * Description:       La web de turismo de acceso fácil (HTML/CSS/JS sin build), en /turismo/ y en /ios/: el mismo contenido que la app, sin instalar nada ni crear una cuenta. Nació como espejo para iPhone mientras no exista una app nativa.
 * Version:           2.0.4
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
 * Registra `/turismo/` y `/ios/` (ver czuwios_bases()) como su propio
 * espacio de URLs y sirve ahí, tal cual, los
 * archivos de `sitio/` — el mismo HTML/CSS/JS que iba a vivir en GitHub
 * Pages, sin ningún cambio: ya usaba rutas relativas (`css/estilo.css`,
 * `./index.html` en el manifest), así que mudarlo de un dominio propio a un
 * subdirectorio de éste no rompió nada.
 * ---------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CZUWIOS_VERSION', '2.0.4' );
define( 'CZUWIOS_FILE', __FILE__ );
define( 'CZUWIOS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CZUWIOS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Dónde vive. La primera es la dirección que se anuncia —la que enlaza el
 * panel y la que conviene dar a la gente—; las demás siguen andando para
 * siempre, porque hay teléfonos que agregaron la web a su pantalla de inicio
 * con esa dirección y un ícono que deja de abrir no avisa por qué.
 *
 * `/turismo/` se sumó en 2.0.0, cuando la web dejó de ser «lo de iPhone» y
 * pasó a ser la versión de acceso fácil para cualquiera. `/ios/` es la de
 * 1.x. Ninguna de las dos se come `/turismo-panel/`: la regla exige la barra
 * justo después del nombre.
 */
function czuwios_bases() {
	return array( 'turismo', 'ios' );
}

/**
 * URL pública de la web, en su dirección principal. La usa el panel para su
 * acceso directo —preguntando antes `function_exists()`, para no depender de
 * que este plugin esté activo—.
 *
 * @param string $ruta
 * @return string
 */
function czuwios_url( $ruta = '' ) {
	$bases = czuwios_bases();
	return home_url( '/' . $bases[0] . '/' . ltrim( (string) $ruta, '/' ) );
}

/** Opciones de wp-admin: el enlace a la app en cada tienda. */
define( 'CZUWIOS_OPT_ANDROID', 'czuwios_tienda_android' );
define( 'CZUWIOS_OPT_IOS', 'czuwios_tienda_ios' );

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
	 * Dos reglas por dirección, y sin comodín que se coma nada de WordPress:
	 * todo lo que cuelga de `/turismo/` o de `/ios/` es de este plugin, así
	 * que no hay con qué chocar. `index.html` es el shell de una SPA que rutea
	 * por hash (`#/ficha/123`), no por path — el navegador nunca le pide al
	 * servidor una URL distinta de la base al navegar adentro de la web, sólo
	 * cuando alguien la abre por primera vez o la recarga.
	 *
	 * Las dos direcciones sirven exactamente los mismos archivos: todo en
	 * `sitio/` usa rutas relativas, así que no hay nada que dependa de cuál
	 * se usó para entrar.
	 */
	public function reglas() {
		foreach ( czuwios_bases() as $base ) {
			add_rewrite_rule( '^' . $base . '/?$', 'index.php?czuwios_archivo=index.html', 'top' );
			add_rewrite_rule( '^' . $base . '/(.+)$', 'index.php?czuwios_archivo=$matches[1]', 'top' );
		}
	}

	/**
	 * Lo que la página necesita saber y no puede traer escrito: dónde está la
	 * API y adónde mandar a quien quiere los recorridos (las tiendas, que se
	 * cargan en wp-admin → Web turismo). Se sirve como `ajustes.json`, un
	 * archivo que no existe en `sitio/`: lo arma este plugin en cada pedido.
	 *
	 * La API sale de `rest_url()` y no de una constante: así la misma copia
	 * de la web anda en un sitio de prueba sin tocar una línea.
	 *
	 * @return array
	 */
	public function ajustes() {
		return array(
			'version' => CZUWIOS_VERSION,
			'api'     => rest_url( 'czu-app/v1/' ),
			'tiendas' => array(
				'android' => self::enlace_tienda( get_option( CZUWIOS_OPT_ANDROID, '' ) ),
				'ios'     => self::enlace_tienda( get_option( CZUWIOS_OPT_IOS, '' ) ),
			),
		);
	}

	/**
	 * Un enlace de tienda sólo se acepta si es https: va a un botón que la
	 * gente toca sin mirar, y un `javascript:` o un `http:` cargado por error
	 * en wp-admin no tiene que llegar nunca a la página.
	 *
	 * @param mixed $url
	 * @return string URL https, o '' si no hay o no sirve.
	 */
	public static function enlace_tienda( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url || 0 !== stripos( $url, 'https://' ) ) {
			return '';
		}
		return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
	}

	/**
	 * Cómo se cachea cada tipo de archivo.
	 *
	 * Hay tres casos, y la diferencia entre ellos es lo que evita los dos
	 * problemas opuestos —la web lenta y la web rota—:
	 *
	 * 1. CÓDIGO CON SU VERSIÓN EN LA URL (`js/app.js?v=2.0.1`): para siempre
	 *    (`immutable`). Ese archivo no puede cambiar sin que cambie la URL,
	 *    porque el HTML y cada `import` la arman con la versión del plugin
	 *    (ver `estampar_js()`). El teléfono lo baja una vez por versión y las
	 *    siguientes visitas no hacen ni una consulta.
	 * 2. CÓDIGO SIN VERSIÓN (la página en sí, y cualquier copia vieja que
	 *    todavía pida `js/app.js` a secas): `no-cache` con ETag, o sea se
	 *    revalida siempre y, si no cambió, es un 304 sin cuerpo. Hasta 1.2.0
	 *    esto se cacheaba una hora entera, y como los módulos se importan
	 *    entre sí, tras una actualización un teléfono podía quedarse con el
	 *    `index.html` nuevo y `js/piezas.js` viejo: la página se rompía sin
	 *    ningún error a la vista durante la hora que duraba el caché.
	 * 3. IMÁGENES Y FUENTES: una semana. No se importan entre sí ni cambian
	 *    con cada versión.
	 *
	 * @param string $ext
	 * @param bool   $versionado la URL pidió exactamente esta versión del plugin
	 * @return string valor de Cache-Control
	 */
	public static function cache_para( $ext, $versionado = false ) {
		if ( in_array( $ext, array( 'png', 'woff2' ), true ) ) {
			return 'public, max-age=604800';
		}
		if ( $versionado && in_array( $ext, array( 'css', 'js' ), true ) ) {
			return 'public, max-age=31536000, immutable';
		}
		return 'no-cache';
	}

	/**
	 * Le pone `?v=<versión>` a los `import` relativos de un módulo.
	 *
	 * Es lo que hace que la versión llegue a TODO el grafo de módulos y no
	 * sólo al primero: `app.js?v=2.0.1` importa `idioma.js?v=2.0.1`, que
	 * importa `config.js?v=2.0.1`… Una actualización cambia todas las URLs de
	 * golpe, así que es imposible mezclar un módulo nuevo con uno viejo. Y la
	 * misma URL en todos los importadores es además lo que hace que el
	 * navegador cargue cada módulo UNA vez: `config.js` y `config.js?v=…` son,
	 * para él, módulos distintos.
	 *
	 * Toca `from "./x.js"`, `import "./x.js"` e `import("./x.js")` —el dinámico
	 * también: uno sin versión metería una segunda copia del módulo en la
	 * página—. El fuente en disco queda sin versiones, por eso anda igual con
	 * un servidor de archivos cualquiera.
	 *
	 * @param string $js
	 * @param string $version
	 * @return string
	 */
	public static function estampar_js( $js, $version ) {
		return preg_replace_callback(
			'/(\bfrom\s*|\bimport\s*\(?\s*)(["\'])(\.{1,2}\/[^"\'?#]+\.js)\2/',
			function ( $m ) use ( $version ) {
				return $m[1] . $m[2] . $m[3] . '?v=' . rawurlencode( $version ) . $m[2];
			},
			$js
		);
	}

	/**
	 * Lo mismo para la página: el CSS y los scripts que pide el HTML. Las
	 * fuentes y los íconos no llevan versión a propósito: se cachean una
	 * semana igual, y la precarga de la fuente tiene que ser la MISMA URL que
	 * usa el CSS o el navegador la baja dos veces.
	 *
	 * @param string $html
	 * @param string $version
	 * @return string
	 */
	public static function estampar_html( $html, $version ) {
		return preg_replace(
			'/\b(src|href)="((?:js|css)\/[^"?#]+\.(?:js|css))"/',
			'$1="$2?v=' . rawurlencode( $version ) . '"',
			$html
		);
	}

	/**
	 * Los módulos que la página va a necesitar, en el orden en que los
	 * encuentra: el grafo de `import` estáticos que arranca en `js/app.js`.
	 *
	 * Sirve para `<link rel="modulepreload">`: sin esa lista, el navegador se
	 * entera de cada módulo recién cuando termina de leer al que lo importa, y
	 * los baja en fila —cada escalón es una vuelta de red completa, que en un
	 * teléfono con mala señal son segundos—. Con la lista los pide todos a la
	 * vez desde el primer byte de la página.
	 *
	 * @param string $entrada ruta relativa a `sitio/`
	 * @return string[] rutas relativas a `sitio/`
	 */
	public static function grafo_modulos( $entrada = 'js/app.js' ) {
		$orden = array();
		$cola  = array( $entrada );
		$base  = realpath( CZUWIOS_SITIO );
		while ( $cola ) {
			$rel = array_shift( $cola );
			if ( isset( $orden[ $rel ] ) ) {
				continue;
			}
			$ruta = realpath( CZUWIOS_SITIO . $rel );
			if ( ! $base || ! $ruta || 0 !== strpos( $ruta, $base . DIRECTORY_SEPARATOR ) || ! is_file( $ruta ) ) {
				continue;
			}
			$orden[ $rel ] = true;
			// Los dinámicos (`import(`) quedan afuera: son los que se cargan a
			// pedido, y precargarlos sería bajar lo que quizá nunca se use.
			if ( preg_match_all( '/(?:\bfrom\s*|\bimport\s*)(["\'])(\.{1,2}\/[^"\'?#]+\.js)\1/', (string) file_get_contents( $ruta ), $m ) ) {
				foreach ( $m[2] as $spec ) {
					$cola[] = self::normalizar_ruta( dirname( $rel ) . '/' . $spec );
				}
			}
		}
		return array_keys( $orden );
	}

	/** `js/pantallas/../config.js` → `js/config.js`. */
	private static function normalizar_ruta( $ruta ) {
		$partes = array();
		foreach ( explode( '/', $ruta ) as $parte ) {
			if ( '' === $parte || '.' === $parte ) {
				continue;
			}
			if ( '..' === $parte ) {
				array_pop( $partes );
				continue;
			}
			$partes[] = $parte;
		}
		return implode( '/', $partes );
	}

	/**
	 * Las etiquetas `<link rel="modulepreload">` de la página, con su versión.
	 *
	 * @param string $version
	 * @return string
	 */
	public static function precarga_modulos( $version ) {
		$links = array();
		foreach ( self::grafo_modulos() as $rel ) {
			$links[] = '<link rel="modulepreload" href="' . esc_html( $rel ) . '?v=' . rawurlencode( $version ) . '">';
		}
		return implode( "\n", $links );
	}

	/**
	 * Dónde sirve el servidor web los archivos de `sitio/` SIN pasar por PHP.
	 *
	 * Es la ruta (desde la raíz del sitio, sin dominio) de la carpeta del
	 * plugin: `/wp-content/plugins/caaguazu-web-ios/sitio/`. Cualquier archivo
	 * de ahí lo contesta el servidor directo, sin arrancar WordPress ni abrir
	 * una conexión a la base de datos.
	 *
	 * ESO ES LO QUE IMPORTA. Hasta 2.0.1 cada archivo —el CSS, cada uno de los
	 * ~20 módulos— se pedía por `/turismo/…` y pasaba por WordPress completo.
	 * Un teléfono los pide casi a la vez, y un hosting compartido tiene un tope
	 * de conexiones a la base de datos: pasado el tope, WordPress contesta
	 * «Database Error» (500) a los que sobran, y a la web le faltaban archivos
	 * al azar —el CSS, tres módulos, `qrcode.js`— y quedaba en blanco. Medido
	 * en producción: 22 pedidos en paralelo por `/turismo/js/…` devolvían 500 en
	 * más de la mitad; los mismos 22 por esta ruta, 200 todos, tres tandas.
	 *
	 * Ruta relativa a propósito (no `https://dominio/…`): tiene que ser del
	 * MISMO origen que la página, o los módulos pedirían permiso CORS.
	 *
	 * @return string '' si no se pudo averiguar (se sirve todo por PHP, como antes).
	 */
	public static function base_directa() {
		$ruta = wp_parse_url( plugins_url( 'sitio/', CZUWIOS_FILE ), PHP_URL_PATH );
		return is_string( $ruta ) && '' !== $ruta ? $ruta : '';
	}

	/**
	 * El mapa de importaciones: le dice al navegador que cada módulo de la
	 * carpeta directa se pide con su versión en la URL.
	 *
	 * Los archivos que sirve el servidor sin PHP no pasan por `estampar_js()`,
	 * así que sus `import "./x.js"` no llevan `?v=`. Y el servidor los deja
	 * guardar una semana: tras una actualización, un teléfono podría juntar un
	 * `app.js` nuevo con un `idioma.js` viejo. El import map cierra ese hueco
	 * desde el HTML —que sí es nuestro y se revalida siempre—: reescribe cada
	 * URL a su versión, y como el fuente de `sitio/` no se toca, la web sigue
	 * andando igual con un servidor de archivos cualquiera.
	 *
	 * Lo entienden Safari 16.4+, Chrome 89+ y Firefox 108+; el resto lo ignora
	 * (y la página, al no verlo, usa la ruta por PHP: ver `cargador()`).
	 *
	 * @param string $base    resultado de base_directa()
	 * @param string $version
	 * @return string etiqueta <script type="importmap">
	 */
	public static function mapa_importaciones( $base, $version ) {
		$imports = array();
		foreach ( self::grafo_modulos() as $rel ) {
			$imports[ $base . $rel ] = $base . $rel . '?v=' . rawurlencode( $version );
		}
		return '<script type="importmap">' . wp_json_encode( array( 'imports' => $imports ), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>';
	}

	/**
	 * La etiqueta que arranca la app. Es un script en línea y no un
	 * `<script type="module" src>` fijo porque tiene que elegir de dónde
	 * cargar: si el navegador entiende import maps, de la carpeta directa
	 * (rápido, sin PHP); si no, por `/turismo/js/…`, donde PHP le pone la
	 * versión a cada import. Sin esa segunda ruta, un iPhone viejo se quedaría
	 * sin la web en vez de tenerla un poco más lenta.
	 *
	 * @param string $base    resultado de base_directa()
	 * @param string $version
	 * @return string
	 */
	public static function cargador( $base, $version ) {
		$v = rawurlencode( $version );
		return '<script>(function () {'
			. ' var directo = !!(window.HTMLScriptElement && HTMLScriptElement.supports && HTMLScriptElement.supports("importmap"));'
			. ' var s = document.createElement("script"); s.type = "module";'
			. ' s.src = (directo ? ' . wp_json_encode( $base, JSON_UNESCAPED_SLASHES ) . ' : "") + "js/app.js?v=' . $v . '";'
			. ' document.body.appendChild(s); })();</script>';
	}

	/**
	 * Arma la página: le pone la versión y la dirección directa a cada
	 * archivo que pide, la lista de módulos a precargar, los ajustes adentro y
	 * el cargador de la app. El fuente `index.html` queda sin nada de esto y
	 * anda igual con un servidor de archivos cualquiera.
	 *
	 * @param string $html
	 * @param string $version
	 * @param string $base    base_directa(), o '' para servir todo por PHP
	 * @param array  $ajustes lo que devuelve ajustes()
	 * @return string
	 */
	public static function preparar_html( $html, $version, $base, $ajustes ) {
		$v = rawurlencode( $version );

		// Los ajustes viajan en la página: es un pedido menos, y un pedido que
		// pasa por WordPress es justo el que puede fallar.
		$json = wp_json_encode( $ajustes, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP );
		$html = str_replace( '<!--ajustes-->', '<script type="application/json" id="czu-ajustes">' . $json . '</script>', $html );

		if ( '' === $base ) {
			$html = self::estampar_html( $html, $version );
			return str_replace( '<!--precarga-->', self::precarga_modulos( $version ), $html );
		}

		$entrada = '<script type="module" src="js/app.js"></script>';
		$html    = str_replace( $entrada, self::cargador( $base, $version ), $html );

		// Código: directo y con versión. Fuentes e íconos: directo, sin versión
		// (nunca cambian, y la precarga de la fuente tiene que ser la MISMA URL
		// que usa el CSS). El manifest se queda por PHP: el servidor lo daría
		// como texto plano, y su `start_url` es relativo a donde vive.
		$html = preg_replace( '/\b(src|href)="((?:js|css)\/[^"?#]+\.(?:js|css))"/', '$1="' . $base . '$2?v=' . $v . '"', $html );
		$html = preg_replace( '/\bhref="((?:fuentes|assets)\/[^"?#]+)"/', 'href="' . $base . '$1"', $html );

		$precarga = self::mapa_importaciones( $base, $version );
		foreach ( self::grafo_modulos() as $rel ) {
			$precarga .= "\n" . '<link rel="modulepreload" href="' . esc_html( $base . $rel ) . '?v=' . $v . '">';
		}
		return str_replace( '<!--precarga-->', $precarga, $html );
	}

	/**
	 * ¿El `If-None-Match` del navegador corresponde a este ETag?
	 *
	 * La comparación que corresponde para una revalidación es la DÉBIL
	 * (RFC 7232 §3.2): un `W/"x"` coincide con `"x"`. Importa porque el
	 * servidor de este hosting (LiteSpeed) y los que comprimen con gzip
	 * reescriben el ETag al salir —lo vuelven débil o le pegan `-gzip`—, y con
	 * una comparación exacta ningún pedido de revalidación coincidía jamás: la
	 * página se bajaba entera en cada visita en vez de contestar 304.
	 *
	 * @param string $cabecera valor de If-None-Match
	 * @param string $etag     el ETag de este archivo, con sus comillas
	 * @return bool
	 */
	public static function etag_coincide( $cabecera, $etag ) {
		foreach ( explode( ',', (string) $cabecera ) as $candidato ) {
			$candidato = trim( $candidato );
			if ( '*' === $candidato ) {
				return true;
			}
			$candidato = preg_replace( '/^W\//', '', $candidato );
			$candidato = preg_replace( '/-(?:gzip|br|deflate)"$/', '"', $candidato );
			if ( $candidato === $etag ) {
				return true;
			}
		}
		return false;
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
			'woff2'       => 'font/woff2',
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

		// El único archivo que no está en disco: ver ajustes().
		if ( 'ajustes.json' === $pedido ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Cache-Control: no-cache' );
			echo wp_json_encode( $this->ajustes() );
			exit;
		}

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

		// «Versionado»: la URL pidió exactamente esta versión del plugin
		// (`?v=2.0.2`). Una `?v=` de otra versión no cuenta —no se le promete
		// «para siempre» a algo que ya quedó viejo—.
		$versionado = isset( $_GET['v'] ) && is_string( $_GET['v'] ) && CZUWIOS_VERSION === wp_unslash( $_GET['v'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// El código se arma en el momento (la página con sus direcciones, los
		// módulos con la versión en cada import); lo demás sale del disco tal
		// cual. El ETag del que se arma sale de LO QUE SE ENVÍA: la página
		// cambia con los ajustes de wp-admin sin que cambie ningún archivo, y
		// con un ETag de archivo un 304 la dejaría con los enlaces viejos.
		$contenido = null;
		if ( 'js' === $ext ) {
			$contenido = self::estampar_js( (string) file_get_contents( $real_pedido ), CZUWIOS_VERSION );
		} elseif ( 'html' === $ext ) {
			$contenido = self::preparar_html( (string) file_get_contents( $real_pedido ), CZUWIOS_VERSION, self::base_directa(), $this->ajustes() );
		}

		// La versión entra en el ETag además de la fecha y el tamaño: una
		// actualización que reescribe un archivo con el mismo largo en el
		// mismo segundo igual lo invalida. Ver cache_para().
		$etag = null === $contenido
			? '"' . md5( CZUWIOS_VERSION . '|' . filemtime( $real_pedido ) . '|' . filesize( $real_pedido ) ) . '"'
			: '"' . md5( CZUWIOS_VERSION . '|' . $contenido ) . '"';

		header( 'Content-Type: ' . $tipos[ $ext ] );
		header( 'Cache-Control: ' . self::cache_para( $ext, $versionado ) );
		header( 'ETag: ' . $etag );

		$si_no = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? trim( wp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( '' !== $si_no && self::etag_coincide( $si_no, $etag ) ) {
			status_header( 304 );
			exit;
		}

		if ( null !== $contenido ) {
			echo $contenido; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- archivo propio del plugin y ajustes ya codificados como JSON
			exit;
		}

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
