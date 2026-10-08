<?php
/**
 * Servidor de prueba de la web de turismo: el PLUGIN DE VERDAD, sin WordPress.
 *
 *   WEB_PLUGIN=caaguazu-web-ios php -S 127.0.0.1:8123 tools/web-prueba/router.php
 *
 * Carga `caaguazu-web-ios.php` con las pocas funciones de WordPress que usa
 * dobladas, y le pasa cada pedido a `CZUWIOS_Servidor::despachar()` — el mismo
 * código que corre en producción: las cabeceras, el ETag, el 304, la
 * reescritura de versiones, `ajustes.json`. Lo que NO es el plugin son dos
 * cosas, y las dos son datos:
 *
 *  - la API (`/wp-json/czu-app/v1/…`): respuestas reales de producción
 *    guardadas en `api/` (con las fotos redirigidas a `/uploads/foto.png`);
 *  - las opciones de wp-admin (los enlaces a las tiendas).
 *
 * Por qué existe: probar la web con un servidor de archivos cualquiera deja
 * sin probar justo lo que más falla —lo que el plugin le dice al navegador—.
 * Y permite correr DOS versiones del plugin con la misma web, que es lo único
 * que reproduce una actualización con el navegador del teléfono todavía
 * guardando archivos de la anterior.
 *
 * Variables de entorno:
 *   WEB_PLUGIN      carpeta del plugin (por defecto, el del repo)
 *   WEB_LATENCIA_MS cuánto se demora cada pedido, que es lo que cuesta
 *                   arrancar WordPress en un hosting compartido (0 = nada)
 *   WEB_ASISTENTE   «1» para que GET /asistente diga que está disponible
 *   WEB_TIENDAS     «0» para no cargar enlaces de tienda
 *   WEB_LIMITE_PHP N: con más de N pedidos a la vez pasando por «WordPress»
 *                   (todo menos la carpeta del plugin), el N+1 recibe el
 *                   «Database Error» (500) que da el hosting cuando se le
 *                   acaban las conexiones a MySQL. Es la falla de producción.
 *   WEB_REGISTRO    archivo donde anotar cada pedido que llega («304 GET /ruta»).
 *                   El log del servidor de PHP no sirve para esto: con varios
 *                   procesos y un `exit` en el medio, no anota la respuesta.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ . '/' );

$plugin_dir = rtrim( getenv( 'WEB_PLUGIN' ) ?: dirname( __DIR__, 2 ) . '/caaguazu-web-ios', '/' );
$latencia   = (int) getenv( 'WEB_LATENCIA_MS' );
$origen     = 'http://' . ( $_SERVER['HTTP_HOST'] ?? '127.0.0.1' );

if ( $registro = getenv( 'WEB_REGISTRO' ) ) {
	// Corre al terminar, incluso tras el `exit` del plugin: ahí ya se sabe qué
	// se contestó.
	register_shutdown_function( function () use ( $registro ) {
		file_put_contents( $registro, http_response_code() . ' ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND | LOCK_EX );
	} );
}

/* ---------------------------------------------------------------------------
 * WordPress, doblado: sólo lo que el plugin llama.
 * ------------------------------------------------------------------------ */

$GLOBALS['czu_reglas'] = array();
$GLOBALS['czu_qv']     = array();
function add_filter( ...$a ) {}
function add_action( ...$a ) {}
function register_activation_hook( ...$a ) {}
function register_deactivation_hook( ...$a ) {}
function plugin_dir_path( $f ) { return dirname( $f ) . '/'; }
function plugin_basename( $f ) { return basename( dirname( $f ) ) . '/' . basename( $f ); }
function add_rewrite_rule( $patron, $destino, $donde = '' ) { $GLOBALS['czu_reglas'][ $patron ] = $destino; }
function get_query_var( $v ) { return $GLOBALS['czu_qv'][ $v ] ?? ''; }
function get_option( $clave, $defecto = false ) {
	if ( 'czuwios_tienda_android' === $clave && '0' !== getenv( 'WEB_TIENDAS' ) ) {
		return 'https://play.google.com/store/apps/details?id=py.caaguazu.turismo';
	}
	return $defecto;
}
function update_option( ...$a ) { return true; }
function rest_url( $ruta = '' ) { return 'http://' . ( $_SERVER['HTTP_HOST'] ?? '127.0.0.1' ) . '/wp-json/' . $ruta; }
function home_url( $ruta = '' ) { return 'http://' . ( $_SERVER['HTTP_HOST'] ?? '127.0.0.1' ) . $ruta; }
function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
/** Donde el plugin está instalado en un WordPress de verdad. */
function plugins_url( $ruta = '', $plugin = '' ) { return 'http://' . ( $_SERVER['HTTP_HOST'] ?? '127.0.0.1' ) . '/wp-content/plugins/caaguazu-web-ios/' . ltrim( $ruta, '/' ); }
function wp_unslash( $v ) { return is_string( $v ) ? stripslashes( $v ) : $v; }
function sanitize_text_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function sanitize_key( $v ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $v ) ); }
function esc_html( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $v ) { return (string) $v; }
function status_header( $c ) { http_response_code( (int) $c ); }
/** Igual que la de WordPress: lo que 1.x pisaba después con su propio Cache-Control. */
function nocache_headers() {
	header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
}

$ruta = rawurldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

/* ---------------------------------------------------------------------------
 * La carpeta del plugin: la sirve el servidor web directo, SIN WordPress.
 * Se imita lo que contesta el hosting de producción (hcdn): los tipos, la
 * semana de caché y —lo que importa para probar— que nunca se cae.
 * ------------------------------------------------------------------------ */

$directa = '/wp-content/plugins/caaguazu-web-ios/sitio/';
if ( 0 === strpos( $ruta, $directa ) ) {
	$archivo = realpath( $plugin_dir . '/sitio/' . substr( $ruta, strlen( $directa ) ) );
	if ( ! $archivo || 0 !== strpos( $archivo, realpath( $plugin_dir . '/sitio' ) . '/' ) || ! is_file( $archivo ) ) {
		http_response_code( 404 );
		return true;
	}
	$tipos = array(
		'js' => 'application/x-javascript', 'css' => 'text/css', 'json' => 'application/json',
		'png' => 'image/png', 'woff2' => 'font/woff2', 'webmanifest' => 'text/plain', 'html' => 'text/html',
	);
	header( 'Content-Type: ' . ( $tipos[ strtolower( pathinfo( $archivo, PATHINFO_EXTENSION ) ) ] ?? 'application/octet-stream' ) );
	header( 'Cache-Control: public, max-age=604800' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $archivo ) ) . ' GMT' );
	header( 'ETag: W/"' . dechex( filesize( $archivo ) ) . '-' . dechex( filemtime( $archivo ) ) . '"' );
	readfile( $archivo );
	return true;
}

/* ---------------------------------------------------------------------------
 * El tope de conexiones del hosting. Cuenta cuántos pedidos están a la vez
 * adentro de «WordPress»; el que pasa el tope recibe el error de producción.
 * ------------------------------------------------------------------------ */

if ( $limite_php = (int) getenv( 'WEB_LIMITE_PHP' ) ) {
	$cuenta = fopen( sys_get_temp_dir() . '/czu-limite-' . ( $_SERVER['SERVER_PORT'] ?? '0' ) . '.cnt', 'c+' );
	$mover  = function ( $delta ) use ( $cuenta ) {
		flock( $cuenta, LOCK_EX );
		rewind( $cuenta );
		$n = max( 0, (int) stream_get_contents( $cuenta ) + $delta );
		ftruncate( $cuenta, 0 );
		rewind( $cuenta );
		fwrite( $cuenta, (string) $n );
		flock( $cuenta, LOCK_UN );
		return $n;
	};
	$en_vuelo = $mover( 1 );
	register_shutdown_function( function () use ( $mover ) { $mover( -1 ); } );
	if ( $en_vuelo > $limite_php ) {
		usleep( 30000 ); // el hosting tarda un poco en rendirse
		http_response_code( 500 );
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo '<!DOCTYPE html><html><head><title>Database Error</title></head><body><h1>Database Error</h1><p>Too many connections.</p></body></html>';
		return true;
	}
}

/* ---------------------------------------------------------------------------
 * Latencia: lo que cuesta, en un hosting compartido, arrancar WordPress.
 * ------------------------------------------------------------------------ */

if ( $latencia > 0 ) {
	usleep( $latencia * 1000 );
}


/* ---------------------------------------------------------------------------
 * La API: datos reales de producción.
 * ------------------------------------------------------------------------ */

if ( 0 === strpos( $ruta, '/wp-json/czu-app/v1/' ) ) {
	$r = substr( $ruta, strlen( '/wp-json/czu-app/v1/' ) );
	$archivos = array(
		'categorias'    => 'categorias',
		'etiquetas'     => 'etiquetas',
		'inventario'    => 'inventario',
		'inventario/260' => 'inventario-260',
		'eventos'       => 'eventos',
		'articulos'     => 'articulos',
		'recorridos'    => 'recorridos',
		'idiomas'       => 'idiomas',
		'mapa/markers'  => 'markers',
		'strings/es'    => 'strings-es',
		'strings/en'    => 'strings-en',
		'strings/pt'    => 'strings-pt',
	);
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Access-Control-Allow-Origin: *' );
	if ( 'asistente' === $r ) {
		if ( '1' === getenv( 'WEB_ASISTENTE' ) ) {
			echo '{"disponible":true}';
		} else {
			http_response_code( 404 );
			echo '{"error":{"codigo":"no_encontrado","mensaje":"No existe.","detalle":{}}}';
		}
		return true;
	}
	if ( isset( $archivos[ $r ] ) ) {
		echo str_replace( '__UPLOADS__\\/foto.png', str_replace( '/', '\\/', $origen ) . '\\/uploads\\/foto.png', file_get_contents( __DIR__ . '/api/' . $archivos[ $r ] . '.json' ) );
		return true;
	}
	http_response_code( 404 );
	echo '{"error":{"codigo":"no_encontrado","mensaje":"No existe.","detalle":{}}}';
	return true;
}

if ( '/uploads/foto.png' === $ruta ) {
	header( 'Content-Type: image/png' );
	header( 'Cache-Control: public, max-age=3600' );
	readfile( __DIR__ . '/foto.png' );
	return true;
}

/* ---------------------------------------------------------------------------
 * La web: el plugin de verdad.
 * ------------------------------------------------------------------------ */

require $plugin_dir . '/caaguazu-web-ios.php';
$servidor = CZUWIOS_Servidor::instance();
$servidor->reglas();

// Lo que hace WordPress con las reglas de reescritura: la primera que
// coincide con el path (sin la barra inicial) decide el destino.
$sin_barra = ltrim( $ruta, '/' );
foreach ( $GLOBALS['czu_reglas'] as $patron => $destino ) {
	if ( preg_match( '#' . $patron . '#', $sin_barra, $m ) ) {
		$destino = preg_replace_callback( '/\$matches\[(\d+)\]/', function ( $x ) use ( $m ) { return $m[ (int) $x[1] ] ?? ''; }, $destino );
		parse_str( (string) parse_url( $destino, PHP_URL_QUERY ), $qv );
		$GLOBALS['czu_qv'] = $qv;
		$servidor->despachar();
		return true; // despachar() hace exit; esto es por si no.
	}
}

http_response_code( 404 );
header( 'Content-Type: text/plain; charset=utf-8' );
echo 'Esto no es del plugin (en WordPress sería la página «en construcción» del theme).';
return true;
