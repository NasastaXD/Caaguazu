<?php
/**
 * Comprobación de las traducciones de contenido.
 *
 *   php tools/verificar-traducciones.php
 *
 * Sale con código 1 si algo falla, igual que las otras verificaciones.
 *
 * POR QUÉ EXISTE
 *
 * Un campo traducible se declara UNA vez —en `PROMOTUR_Traducciones::campos()`—
 * y de ahí salen cuatro cosas que tienen que estar de acuerdo: el formulario
 * del panel, el archivo que se baja, el importador que lo lee de vuelta, y la
 * clave con la que la API lo sirve.
 *
 * Las tres primeras recorren la misma lista, así que no se pueden separar. La
 * cuarta sí: la API sirve cada campo con la clave que ya tenía —`cuerpo` viaja
 * como `cuerpo_html`, `horario` como `horario_resumen`— y esa correspondencia
 * vive en una constante `MAPA_I18N` de cada endpoint. Un campo declarado
 * traducible que no esté en ese mapa se traduce, se guarda, se ve completo en
 * el panel… y la app lo sigue recibiendo en castellano. Nadie se entera: no hay
 * error, no hay dato faltante, sólo un texto que no cambia de idioma.
 *
 * Eso es lo que se comprueba acá, junto con la vuelta completa del archivo
 * —exportar, traducir, importar, leer— y con los rechazos que tiene que hacer
 * el importador: un archivo de otra pieza pisaría textos que nadie revisó, y
 * como los campos se llaman igual en las dos, el resultado se ve normal hasta
 * que alguien lee la app.
 *
 * Se corre sin WordPress: probar esto no puede costar levantar un sitio.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ );

$raiz   = dirname( __DIR__ );
$verde  = "\033[32m"; $rojo = "\033[31m"; $gris = "\033[90m"; $fin = "\033[0m";
$fallos = 0;

function comprobar( $etiqueta, $obtenido, $esperado ) {
	global $fallos, $verde, $rojo, $gris, $fin;
	$ok = ( $obtenido === $esperado );
	if ( ! $ok ) { $fallos++; }
	printf( "%s  %-56s  %s\n",
		$ok ? $verde . 'ok  ' . $fin : $rojo . 'FALLA' . $fin,
		$etiqueta,
		$ok ? '' : $gris . 'esperaba ' . var_export( $esperado, true ) . ', dio ' . var_export( $obtenido, true ) . $fin
	);
}

/* ---------------------------------------------------------------------------
 * Dobles mínimos de WordPress. Se stubea la base y nada más: la lógica que se
 * está probando corre de verdad.
 * ------------------------------------------------------------------------ */

$GLOBALS['metas']   = array();
$GLOBALS['campos']  = array();

function __( $t, $d = null ) { return $t; }
function _n( $s, $p, $n, $d = null ) { return 1 === $n ? $s : $p; }
function esc_html( $t ) { return $t; }
function apply_filters( $hook, $value ) { return $value; }
function wp_strip_all_tags( $t ) { return strip_tags( (string) $t ); }
function sanitize_title( $t ) { return strtolower( preg_replace( '/[^a-z0-9]+/i', '-', (string) $t ) ); }
function home_url( $p = '' ) { return 'https://caaguazu.net' . $p; }
function current_time( $f ) { return gmdate( 'Y-m-d H:i:s' ); }
function clean_post_cache( $id ) {}
function get_the_title( $p = null ) { return $GLOBALS['campos']['post_title'] ?? ''; }
function get_post( $id ) { return (object) array( 'ID' => (int) $id, 'post_type' => 'promotur_articulo' ); }

function get_post_field( $campo, $id = null ) {
	return $GLOBALS['campos'][ $campo ] ?? '';
}
function get_post_meta( $id, $key = '', $single = false ) {
	$v = $GLOBALS['metas'][ $id ][ $key ] ?? '';
	return $v;
}
function update_post_meta( $id, $key, $valor ) {
	$GLOBALS['metas'][ $id ][ $key ] = $valor;
	return true;
}
function delete_post_meta( $id, $key ) {
	unset( $GLOBALS['metas'][ $id ][ $key ] );
	return true;
}

/** $wpdb de mentira: `tocar()` escribe derecho en la tabla de posts. */
class TraduccionesDB {
	public $posts = 'wp_posts';
	public function update( $t, $datos, $where, $f = null, $wf = null ) {
		$GLOBALS['campos']['post_modified_gmt'] = $datos['post_modified_gmt'];
		return 1;
	}
}
$GLOBALS['wpdb'] = new TraduccionesDB();

class WP_Error {
	private $c, $m;
	public function __construct( $c, $m = '' ) { $this->c = $c; $this->m = $m; }
	public function get_error_code() { return $this->c; }
	public function get_error_message() { return $this->m; }
}
function is_wp_error( $x ) { return $x instanceof WP_Error; }

/** Editorial: sólo lo que las traducciones le preguntan. */
class PROMOTUR_Editorial {
	public static function tipo_de( $post ) { return $GLOBALS['tipo_actual'] ?? 'articulo'; }
}
class PROMOTUR_Recorridos {
	public static function paradas( $post_id ) { return $GLOBALS['paradas'] ?? array(); }
}

require $raiz . '/caaguazu-portal/includes/class-traducciones.php';
require $raiz . '/caaguazu-portal/includes/class-traducciones-archivo.php';

/* ---------------------------------------------------------------------------
 * 1. Todo campo traducible llega a la API
 * ------------------------------------------------------------------------ */

echo "\n" . $gris . '== Cada campo traducible tiene su clave en la API ==' . $fin . "\n";

/*
 * Los mapas se leen del código de los endpoints, no de una copia: una copia se
 * desincroniza y pasa a verificar el mapa que ya no corre.
 */
function czu_mapa_de( $archivo ) {
	$src = file_get_contents( $archivo );
	if ( ! preg_match( '/const MAPA_I18N = array\((.*?)\);/s', $src, $m ) ) {
		return array();
	}
	preg_match_all( "/'([a-z_.0-9]+)'\s*=>\s*'([a-z_.0-9]+)'/", $m[1], $pares, PREG_SET_ORDER );
	$out = array();
	foreach ( $pares as $p ) { $out[ $p[1] ] = $p[2]; }
	return $out;
}

$api = $raiz . '/caaguazu-app-api/includes/';

/*
 * Campos que la API resuelve a mano y no por el mapa, con el motivo. Un campo
 * que no esté ni en el mapa ni acá es un campo que se traduce y no llega.
 */
$a_mano = array(
	'destino' => array(
		'descripcion' => 'detalle(): pasa por the_content después de elegir el idioma',
		'horario'     => 'detalle(): vive anidado en practicos',
		'costo'       => 'detalle(): vive anidado en practicos',
	),
	'articulo' => array(
		'cuerpo' => 'detalle(): se elige en plano y recién después the_content',
	),
	'recorrido' => array(
		'titulo'            => 'formato(): la respuesta se arma entera a mano',
		'resumen'           => 'formato()',
		'cuerpo'            => 'formato(): articulo_html, con the_content',
		'duracion_estimada' => 'formato()',
	),
);

$mapas = array(
	'destino'  => czu_mapa_de( $api . 'class-inventario.php' ),
	'articulo' => czu_mapa_de( $api . 'class-articulos.php' ),
);

foreach ( array( 'destino', 'articulo', 'recorrido' ) as $tipo ) {
	foreach ( PROMOTUR_Traducciones::campos( $tipo ) as $clave => $def ) {
		$en_mapa = isset( $mapas[ $tipo ][ $clave ] );
		$en_mano = isset( $a_mano[ $tipo ][ $clave ] );
		comprobar( $tipo . '.' . $clave . ' llega a la API', $en_mapa || $en_mano, true );
	}
}

/*
 * Y al revés: un mapa que nombre un campo que ya no se traduce quedaría
 * pisando una clave de la respuesta con un texto que nadie puede escribir.
 */
foreach ( $mapas as $tipo => $mapa ) {
	$declarados = array_keys( PROMOTUR_Traducciones::campos( $tipo ) );
	foreach ( array_keys( $mapa ) as $clave ) {
		comprobar( $tipo . ': el mapa no inventa «' . $clave . '»', in_array( $clave, $declarados, true ), true );
	}
}

/* ---------------------------------------------------------------------------
 * 2. La vuelta completa del archivo
 * ------------------------------------------------------------------------ */

echo "\n" . $gris . '== Exportar, traducir, importar ==' . $fin . "\n";

$GLOBALS['tipo_actual'] = 'articulo';
$GLOBALS['campos'] = array(
	'post_title'        => 'El salto que se escucha antes de verse',
	'post_content'      => "Primer párrafo.\n\nSegundo párrafo.",
	'post_excerpt'      => 'Una caída de agua a veinte minutos del centro.',
	'post_modified_gmt' => '2026-09-01 10:00:00',
);
$GLOBALS['metas'] = array( 7 => array(
	'_articulo_antetitulo' => 'Ruta del agua',
	'_articulo_subtitulo'  => 'Cómo llegar y qué mirar',
) );

$archivo = PROMOTUR_Traducciones_Archivo::exportar( 7 );

comprobar( 'el archivo declara su formato', $archivo['_formato'], 'caaguazu-traduccion/1' );
comprobar( 'trae instrucciones adentro', ! empty( $archivo['_instrucciones']['que_hacer'] ), true );
comprobar( 'trae reglas de qué no traducir', ! empty( $archivo['_instrucciones']['reglas'] ), true );
comprobar( 'sabe de qué pieza es', $archivo['contenido']['id'], 7 );
comprobar( 'pide los dos idiomas', $archivo['idiomas'], array( 'en', 'pt' ) );
comprobar( 'cinco campos con texto', count( $archivo['campos'] ), 5 );

// Cada campo lleva el original y una clave vacía por idioma.
$primero = $archivo['campos'][0];
comprobar( 'cada campo explica qué es', '' !== $primero['que_es'], true );
comprobar( 'cada campo trae el castellano', isset( $primero['es'] ), true );
comprobar( 'cada campo trae la clave del inglés vacía', $primero['en'], '' );

// Se «traduce» y se sube.
foreach ( $archivo['campos'] as $i => $fila ) {
	$archivo['campos'][ $i ]['en'] = '[EN] ' . $fila['es'];
}
$resultado = PROMOTUR_Traducciones_Archivo::importar( 7, json_encode( $archivo ) );

comprobar( 'la importación no falla', is_wp_error( $resultado ), false );
comprobar( 'entraron los cinco campos en inglés', $resultado['idiomas']['en'] ?? 0, 5 );
comprobar( 'no ignoró ningún campo', $resultado['ignorados'], array() );
comprobar( 'el portugués quedó sin tocar', isset( $resultado['idiomas']['pt'] ), false );

$en = PROMOTUR_Traducciones::leer( 7, 'en' );
comprobar( 'el título quedó traducido', $en['titulo'], '[EN] El salto que se escucha antes de verse' );
comprobar(
	'los párrafos se conservan',
	$en['cuerpo'],
	"[EN] Primer párrafo.\n\nSegundo párrafo."
);

/*
 * Guardar una traducción tiene que mover `post_modified_gmt`: `/sync` busca
 * por esa fecha, así que sin el empujón una traducción nueva nunca entra en el
 * delta y la app cacheada sigue mostrando el castellano para siempre.
 */
comprobar(
	'guardar movió la fecha de modificación (para /sync)',
	'2026-09-01 10:00:00' !== $GLOBALS['campos']['post_modified_gmt'],
	true
);

$estado = PROMOTUR_Traducciones::estado( 7 );
comprobar( 'el inglés figura completo', $estado['en']['estado'], 'completa' );
comprobar( 'el portugués figura sin empezar', $estado['pt']['estado'], 'sin_empezar' );

// Y si el castellano se edita después, la traducción tiene que quedar marcada.
$GLOBALS['campos']['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', time() + 60 );
$estado = PROMOTUR_Traducciones::estado( 7 );
comprobar( 'editar el castellano marca la traducción vieja', $estado['en']['estado'], 'desactualizada' );

/* ---------------------------------------------------------------------------
 * 3. Lo que el importador tiene que rechazar
 * ------------------------------------------------------------------------ */

echo "\n" . $gris . '== El importador no confía en el archivo ==' . $fin . "\n";

$r = PROMOTUR_Traducciones_Archivo::importar( 7, 'esto no es json' );
comprobar( 'un archivo que no es JSON', is_wp_error( $r ) ? $r->get_error_code() : 'no rechazó', 'json_invalido' );

$r = PROMOTUR_Traducciones_Archivo::importar( 7, '{"campos":[]}' );
comprobar( 'un JSON que no es de acá', is_wp_error( $r ) ? $r->get_error_code() : 'no rechazó', 'formato_desconocido' );

$de_otra = $archivo;
$de_otra['contenido']['id'] = 99;
$r = PROMOTUR_Traducciones_Archivo::importar( 7, json_encode( $de_otra ) );
comprobar( 'el archivo de OTRA pieza', is_wp_error( $r ) ? $r->get_error_code() : 'no rechazó', 'otro_contenido' );

$vacio = $archivo;
foreach ( $vacio['campos'] as $i => $f ) { $vacio['campos'][ $i ]['en'] = ''; $vacio['campos'][ $i ]['pt'] = ''; }
$r = PROMOTUR_Traducciones_Archivo::importar( 7, json_encode( $vacio ) );
comprobar( 'un archivo sin nada traducido', is_wp_error( $r ) ? $r->get_error_code() : 'no rechazó', 'nada_traducido' );

$inventado = $archivo;
$inventado['campos'][] = array( 'clave' => 'campo_que_no_existe', 'es' => 'x', 'en' => 'y' );
$r = PROMOTUR_Traducciones_Archivo::importar( 7, json_encode( $inventado ) );
comprobar( 'un campo inventado se ignora y se avisa', $r['ignorados'] ?? null, array( 'campo_que_no_existe' ) );

/* ---------------------------------------------------------------------------
 * 4. La caída al original, campo por campo
 * ------------------------------------------------------------------------ */

echo "\n" . $gris . '== Lo que no está traducido sale en castellano ==' . $fin . "\n";

PROMOTUR_Traducciones::guardar( 7, 'pt', array( 'titulo' => 'O salto que se ouve antes de se ver' ) );

$r = PROMOTUR_Traducciones::resolver( 7, 'pt' );
comprobar( 'el campo traducido sale en portugués', $r['textos']['titulo'], 'O salto que se ouve antes de se ver' );
comprobar( 'el que falta cae al castellano', $r['textos']['subtitulo'], 'Cómo llegar y qué mirar' );
comprobar( 'y se informa que está incompleto', $r['completo'], false );
comprobar( 'sólo un campo cuenta como traducido', $r['traducidos'], array( 'titulo' ) );

$r = PROMOTUR_Traducciones::resolver( 7, 'fr' );
comprobar( 'un idioma que no existe cae al original', $r['idioma'], 'es' );
comprobar( 'y devuelve el castellano', $r['textos']['titulo'], 'El salto que se escucha antes de verse' );

/* ---------------------------------------------------------------------------
 * 5. El recorrido, que suma un campo por parada
 * ------------------------------------------------------------------------ */

echo "\n" . $gris . '== El texto de cada parada ==' . $fin . "\n";

$GLOBALS['tipo_actual'] = 'recorrido';
$GLOBALS['paradas'] = array(
	array( 'orden' => 1, 'ref_id' => 100, 'texto' => 'Se llega por el camino viejo.' ),
	array( 'orden' => 2, 'ref_id' => 102, 'texto' => '' ),
	array( 'orden' => 3, 'ref_id' => 103, 'texto' => 'Acá se para a comer.' ),
);

$campos_rec = PROMOTUR_Traducciones::campos_de( 8 );
comprobar( 'la parada con texto es traducible', isset( $campos_rec['parada.0.texto'] ), true );
comprobar( 'la parada sin texto no aparece', isset( $campos_rec['parada.1.texto'] ), false );
comprobar( 'la tercera conserva su índice', isset( $campos_rec['parada.2.texto'] ), true );

comprobar(
	'el original de una parada sale de la parada',
	PROMOTUR_Traducciones::original( 8, 'parada.2.texto', $campos_rec['parada.2.texto'] ),
	'Acá se para a comer.'
);

/* ------------------------------------------------------------------------ */

echo "\n";
if ( $fallos ) {
	echo $rojo . "  $fallos comprobación/es fallaron." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  Las traducciones hacen lo que dicen.' . $fin . "\n\n";
