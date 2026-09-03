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

echo "\n";
if ( $fallos ) {
	echo $rojo . "  $fallos comprobación/es fallaron." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  El espejo iOS no deja que WordPress le agregue una barra a sus archivos.' . $fin . "\n\n";
