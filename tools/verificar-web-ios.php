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

echo "\n";
if ( $fallos ) {
	echo $rojo . "  $fallos comprobación/es fallaron." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  La web de turismo responde en sus dos direcciones, sin barras de más ni enlaces peligrosos.' . $fin . "\n\n";
