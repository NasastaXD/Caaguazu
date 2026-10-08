<?php
/**
 * Comprobación del espejo web para iOS: que WordPress no le agregue una barra
 * final a los archivos que sirve.
 *
 *   php tools/verificar-web-ios.php
 *
 * Sale con código 1 si algo falla, igual que las otras verificaciones.
 *
 * POR QUÉ EXISTE
 *
 * `redirect_canonical()` de WordPress corre en el mismo hook que el
 * despachador de este plugin (`template_redirect`), y ve `/ios/js/app.js`
 * como un permalink al que le falta la barra final: manda un 301 a
 * `/ios/js/app.js/` antes de que el plugin llegue a servir nada. El
 * navegador sigue el redirect y el archivo se sirve igual — pero `app.js` es
 * un módulo ES con imports relativos (`from "./idioma.js"`), y esos se
 * resuelven contra la URL final, CON la barra puesta:
 * `js/app.js/idioma.js`, un path que no existe. El import falla, el módulo
 * entero no carga, y la pantalla queda en blanco sin ningún error a la
 * vista — el único síntoma es que todo archivo bajo `/ios/` devuelve 301
 * antes del 200, y eso sólo se ve mirando las cabeceras, nunca desde el
 * navegador. Así se vio en producción: `caaguazu.net/ios/` cargaba
 * (200 en `index.html`), pero la pantalla quedaba negra.
 *
 * `CZUWIOS_Servidor::sin_canonical()` cancela ese redirect —usando el filtro
 * que `redirect_canonical()` ya expone para esto— sólo para pedidos de este
 * plugin. Acá se prueba esa función en aislamiento: no hace falta un
 * WordPress entero para confirmar que devuelve `false` cuando corresponde.
 *
 * Se corre sin WordPress: probar esto no puede costar levantar un sitio.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ . '/' );

// Dobles mínimos: lo único que el archivo del plugin ejecuta al cargarse.
// `add_filter` SÍ registra —y no es adorno—: la comprobación de abajo prueba
// no sólo que `sin_canonical()` devuelva lo correcto llamada a mano, sino que
// el plugin de verdad la enganche al filtro. Sin este registro, un `add_filter`
// borrado por error seguiría pasando la comprobación de la función sola.
$GLOBALS['czu_filtros'] = array();
function add_filter( $hook, $cb ) {
	$GLOBALS['czu_filtros'][ $hook ][] = $cb;
}
function add_action( $hook, $cb ) {}
function register_activation_hook( $file, $cb ) {}
function register_deactivation_hook( $file, $cb ) {}
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_basename( $file ) { return basename( dirname( $file ) ) . '/' . basename( $file ); }

$GLOBALS['czu_query_vars'] = array();
function get_query_var( $var ) {
	return $GLOBALS['czu_query_vars'][ $var ] ?? '';
}

// Para las reglas, los ajustes y la URL pública (2.0.0).
$GLOBALS['czu_reglas'] = array();
function add_rewrite_rule( $patron, $destino, $donde ) {
	$GLOBALS['czu_reglas'][ $patron ] = $destino;
}
$GLOBALS['czu_opciones'] = array();
function get_option( $clave, $defecto = false ) {
	return $GLOBALS['czu_opciones'][ $clave ] ?? $defecto;
}
function esc_html( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES, 'UTF-8' ); }
function rest_url( $ruta = '' ) { return 'https://ejemplo.test/wp-json/' . $ruta; }
function home_url( $ruta = '' ) { return 'https://ejemplo.test' . $ruta; }

require dirname( __DIR__ ) . '/caaguazu-web-ios/caaguazu-web-ios.php';

$verde = "\033[32m"; $rojo = "\033[31m"; $gris = "\033[90m"; $fin = "\033[0m";
$fallos = 0;

function comprobar( $etiqueta, $obtenido, $esperado ) {
	global $fallos, $verde, $rojo, $gris, $fin;
	$ok = ( $obtenido === $esperado );
	if ( ! $ok ) { $fallos++; }
	printf( "%s  %-58s  %s\n",
		$ok ? $verde . 'ok  ' . $fin : $rojo . 'FALLA' . $fin,
		$etiqueta,
		$ok ? '' : $gris . 'esperaba ' . var_export( $esperado, true ) . ', dio ' . var_export( $obtenido, true ) . $fin
	);
}

echo "\n" . $gris . '== CZUWIOS_Servidor::sin_canonical() ==' . $fin . "\n";

$servidor = CZUWIOS_Servidor::instance();

$enganchado = false;
foreach ( $GLOBALS['czu_filtros']['redirect_canonical'] ?? array() as $cb ) {
	if ( is_array( $cb ) && $cb[0] === $servidor && 'sin_canonical' === $cb[1] ) {
		$enganchado = true;
	}
}
comprobar( 'sin_canonical() está enganchada al filtro redirect_canonical', $enganchado, true );

$GLOBALS['czu_query_vars']['czuwios_archivo'] = 'js/app.js';
comprobar(
	'un archivo de /ios/ cancela el redirect canónico',
	$servidor->sin_canonical( 'https://caaguazu.net/ios/js/app.js/' ),
	false
);

$GLOBALS['czu_query_vars']['czuwios_archivo'] = '';
comprobar(
	'una URL que no es de este plugin no se toca',
	$servidor->sin_canonical( 'https://caaguazu.net/alguna-pagina/' ),
	'https://caaguazu.net/alguna-pagina/'
);

/*
 * Las dos direcciones (2.0.0). Se comprueba la regla real que registra el
 * plugin, ejecutada como la ejecuta WordPress —una regex contra el path sin
 * la barra inicial—, no una copia escrita a mano acá.
 */
echo "\n" . $gris . '== Direcciones: /turismo/ y /ios/ ==' . $fin . "\n";

$servidor->reglas();
function resuelve( $path ) {
	foreach ( $GLOBALS['czu_reglas'] as $patron => $destino ) {
		if ( preg_match( '#' . $patron . '#', $path, $m ) ) {
			return preg_replace_callback( '/\$matches\[(\d+)\]/', function ( $x ) use ( $m ) { return $m[ (int) $x[1] ]; }, $destino );
		}
	}
	return null;
}
comprobar( '/turismo/ abre la web', resuelve( 'turismo/' ), 'index.php?czuwios_archivo=index.html' );
comprobar( '/turismo sin barra también', resuelve( 'turismo' ), 'index.php?czuwios_archivo=index.html' );
comprobar( '/turismo/js/app.js sirve el archivo', resuelve( 'turismo/js/app.js' ), 'index.php?czuwios_archivo=js/app.js' );
comprobar( '/ios/ sigue abriendo la web', resuelve( 'ios/' ), 'index.php?czuwios_archivo=index.html' );
comprobar( '/ios/css/estilo.css sigue sirviendo', resuelve( 'ios/css/estilo.css' ), 'index.php?czuwios_archivo=css/estilo.css' );
comprobar( '/turismo-panel/ NO es de este plugin', resuelve( 'turismo-panel/' ), null );
comprobar( '/turismo-panel/entrar NO es de este plugin', resuelve( 'turismo-panel/entrar' ), null );
comprobar( 'czuwios_url() apunta a la dirección principal', czuwios_url(), 'https://ejemplo.test/turismo/' );

echo "\n" . $gris . '== ajustes.json: la API y las tiendas ==' . $fin . "\n";

$aj = $servidor->ajustes();
comprobar( 'la API sale de rest_url()', $aj['api'], 'https://ejemplo.test/wp-json/czu-app/v1/' );
comprobar( 'sin tiendas cargadas, vienen vacías', array( $aj['tiendas']['android'], $aj['tiendas']['ios'] ), array( '', '' ) );

$GLOBALS['czu_opciones'][ CZUWIOS_OPT_ANDROID ] = 'https://play.google.com/store/apps/details?id=py.caaguazu.turismo';
$GLOBALS['czu_opciones'][ CZUWIOS_OPT_IOS ]     = '  https://apps.apple.com/app/id123  ';
$aj = $servidor->ajustes();
comprobar( 'un enlace https a Google Play pasa', $aj['tiendas']['android'], 'https://play.google.com/store/apps/details?id=py.caaguazu.turismo' );
comprobar( 'los espacios de más se recortan', $aj['tiendas']['ios'], 'https://apps.apple.com/app/id123' );

comprobar( 'un javascript: nunca llega a la página', CZUWIOS_Servidor::enlace_tienda( 'javascript:alert(1)' ), '' );
comprobar( 'un http: sin TLS tampoco', CZUWIOS_Servidor::enlace_tienda( 'http://play.google.com/x' ), '' );
comprobar( 'basura sin esquema tampoco', CZUWIOS_Servidor::enlace_tienda( 'play.google.com/x' ), '' );

echo "\n" . $gris . '== Caché: el código se revalida, lo pesado se guarda ==' . $fin . "\n";

comprobar( 'js se revalida en cada carga', CZUWIOS_Servidor::cache_para( 'js' ), 'no-cache' );
comprobar( 'html se revalida en cada carga', CZUWIOS_Servidor::cache_para( 'html' ), 'no-cache' );
comprobar( 'css se revalida en cada carga', CZUWIOS_Servidor::cache_para( 'css' ), 'no-cache' );
comprobar( 'las fuentes se guardan una semana', CZUWIOS_Servidor::cache_para( 'woff2' ), 'public, max-age=604800' );
comprobar( 'un js sin versión en la URL se sigue revalidando', CZUWIOS_Servidor::cache_para( 'js', false ), 'no-cache' );
comprobar( 'un js con su versión en la URL se guarda «para siempre»', CZUWIOS_Servidor::cache_para( 'js', true ), 'public, max-age=31536000, immutable' );
comprobar( 'y un css igual', CZUWIOS_Servidor::cache_para( 'css', true ), 'public, max-age=31536000, immutable' );
comprobar( 'pero la página NUNCA, ni con ?v=', CZUWIOS_Servidor::cache_para( 'html', true ), 'no-cache' );
comprobar( 'ni los ajustes ni los textos', CZUWIOS_Servidor::cache_para( 'json', true ), 'no-cache' );

echo "\n" . $gris . '== Versiones en las URLs: que una actualización no pueda mezclar archivos ==' . $fin . "\n";

$v = '2.0.1';
comprobar( 'from "./x.js" lleva la versión',
	CZUWIOS_Servidor::estampar_js( 'import { a } from "./x.js";', $v ),
	'import { a } from "./x.js?v=2.0.1";' );
comprobar( 'from "../x.js" (módulos de pantallas) también',
	CZUWIOS_Servidor::estampar_js( "import * as P from '../piezas.js';", $v ),
	"import * as P from '../piezas.js?v=2.0.1';" );
comprobar( 'import "./x.js" (sin nombres) también',
	CZUWIOS_Servidor::estampar_js( 'import "./efecto.js";', $v ),
	'import "./efecto.js?v=2.0.1";' );
comprobar( 'un import partido en líneas también',
	CZUWIOS_Servidor::estampar_js( "import {\n  a,\n  b\n} from\n  \"./x.js\";", $v ),
	"import {\n  a,\n  b\n} from\n  \"./x.js?v=2.0.1\";" );
comprobar( 'import() dinámico también',
	CZUWIOS_Servidor::estampar_js( 'const m = await import("./lazy.js");', $v ),
	'const m = await import("./lazy.js?v=2.0.1");' );
comprobar( 'una URL absoluta no se toca (no es nuestra)',
	CZUWIOS_Servidor::estampar_js( 'import x from "https://cdn.test/x.js";', $v ),
	'import x from "https://cdn.test/x.js";' );
comprobar( 'una cadena cualquiera con ./x.js no se toca',
	CZUWIOS_Servidor::estampar_js( 'const ruta = "./x.js"; fetch("./datos.json");', $v ),
	'const ruta = "./x.js"; fetch("./datos.json");' );
comprobar( 'si ya trae versión no se le pone otra',
	CZUWIOS_Servidor::estampar_js( 'import { a } from "./x.js?v=1";', $v ),
	'import { a } from "./x.js?v=1";' );

comprobar( 'el HTML versiona el módulo, el css y el script clásico',
	CZUWIOS_Servidor::estampar_html( '<link rel="stylesheet" href="css/estilo.css"><script src="js/vendor/qrcode.js" defer></script><script type="module" src="js/app.js"></script>', $v ),
	'<link rel="stylesheet" href="css/estilo.css?v=2.0.1"><script src="js/vendor/qrcode.js?v=2.0.1" defer></script><script type="module" src="js/app.js?v=2.0.1"></script>' );
comprobar( 'pero no las fuentes ni los íconos (su precarga tiene que coincidir con el css)',
	CZUWIOS_Servidor::estampar_html( '<link rel="preload" href="fuentes/inter-400.woff2"><link rel="icon" href="assets/icon-192.png">', $v ),
	'<link rel="preload" href="fuentes/inter-400.woff2"><link rel="icon" href="assets/icon-192.png">' );

// Todo `import` relativo que existe de verdad en sitio/ termina con versión, y
// ninguno queda afuera: es la garantía de que un grafo no se mezcla.
$sin_version = array();
$modulos     = array();
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( CZUWIOS_SITIO . 'js', FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $f ) {
	if ( 'js' !== $f->getExtension() || false !== strpos( $f->getPathname(), '/vendor/' ) ) {
		continue;
	}
	$modulos[] = substr( $f->getPathname(), strlen( CZUWIOS_SITIO ) );
	$estampado = CZUWIOS_Servidor::estampar_js( (string) file_get_contents( $f->getPathname() ), $v );
	if ( preg_match_all( '/(?:\bfrom\s*|\bimport\s*\(?\s*)(["\'])(\.{1,2}\/[^"\']+\.js)(\?[^"\']*)?\1/', $estampado, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $coincidencia ) {
			if ( ( $coincidencia[3] ?? '' ) !== '?v=' . $v ) {
				$sin_version[] = substr( $f->getPathname(), strlen( CZUWIOS_SITIO ) ) . ' → ' . $coincidencia[2];
			}
		}
	}
}
comprobar( 'ningún import de sitio/js queda sin versión', $sin_version, array() );

$grafo = CZUWIOS_Servidor::grafo_modulos();
sort( $modulos );
$del_grafo = $grafo;
sort( $del_grafo );
comprobar( 'el grafo que se precarga es EXACTAMENTE el de los módulos que hay', $del_grafo, $modulos );
comprobar( 'y arranca por app.js', $grafo[0], 'js/app.js' );
$precarga = CZUWIOS_Servidor::precarga_modulos( $v );
comprobar( 'la precarga trae una etiqueta por módulo, con versión', substr_count( $precarga, '<link rel="modulepreload" href="js/' ), count( $modulos ) );
comprobar( 'y todas con ?v=', substr_count( $precarga, '?v=' . $v . '">' ), count( $modulos ) );
$index = (string) file_get_contents( CZUWIOS_SITIO . 'index.html' );
comprobar( 'index.html tiene el marcador donde se inserta la precarga', substr_count( $index, '<!--precarga-->' ), 1 );

echo "\n" . $gris . '== Revalidación: el ETag que el hosting reescribe ==' . $fin . "\n";

$et = '"abc123"';
comprobar( 'exacto', CZUWIOS_Servidor::etag_coincide( $et, $et ), true );
comprobar( 'débil (W/"…"), como lo deja LiteSpeed o el CDN', CZUWIOS_Servidor::etag_coincide( 'W/' . $et, $et ), true );
comprobar( 'con sufijo de gzip, como lo deja Apache', CZUWIOS_Servidor::etag_coincide( '"abc123-gzip"', $et ), true );
comprobar( 'con sufijo de brotli', CZUWIOS_Servidor::etag_coincide( '"abc123-br"', $et ), true );
comprobar( 'una lista con el nuestro adentro', CZUWIOS_Servidor::etag_coincide( '"otro", W/"abc123"', $et ), true );
comprobar( 'el comodín', CZUWIOS_Servidor::etag_coincide( '*', $et ), true );
comprobar( 'uno distinto NO coincide', CZUWIOS_Servidor::etag_coincide( '"abc124"', $et ), false );
comprobar( 'vacío NO coincide', CZUWIOS_Servidor::etag_coincide( '', $et ), false );

echo "\n";
if ( $fallos ) {
	echo $rojo . "  $fallos comprobación/es fallaron." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  La web de turismo responde en sus dos direcciones, sin barras de más ni enlaces peligrosos.' . $fin . "\n\n";
