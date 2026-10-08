<?php
/**
 * Comprobación de la lógica del asistente de la app.
 *
 *   php tools/verificar-asistente.php
 *
 * Sale con código 1 si algo falla, igual que las otras verificaciones.
 *
 * POR QUÉ EXISTE
 *
 * El asistente tiene cinco piezas que TRANSFORMAN un dato, y las cinco fallan
 * en silencio:
 *
 *   - La búsqueda. Si un plural no encuentra al singular, la ficha que
 *     contestaba la pregunta no viaja completa y el modelo contesta con menos
 *     de lo que había. No hay error: hay una respuesta peor.
 *   - Las citas. Una marca inventada por el modelo que pasara el filtro sería
 *     un enlace a una ficha que no existe; una marca que quedara en el texto,
 *     un «[F12]» suelto en lo que lee una persona mayor.
 *   - La limpieza del Markdown. Los asteriscos a la vista se leen como error.
 *   - El recorte por presupuesto. Si recorta en el orden equivocado, lo que se
 *     pierde es el detalle de la pregunta y queda el catálogo.
 *   - El aviso de caída. Tiene que decir qué hacer, y NUNCA llevar la key.
 *
 * Se corre sin WordPress: probar esto no puede costar levantar un sitio.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ );
define( 'CZUAPI_NS', 'czu-app/v1' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'WEEK_IN_SECONDS', 604800 );

// La key de mentira con la que se prueba que el aviso la tacha.
define( 'CZUAPI_IA_KEY', 'sk-prueba-1234567890abcdef' );

$GLOBALS['czu_opciones'] = array();
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['czu_opciones'] ) ? $GLOBALS['czu_opciones'][ $k ] : $d; }
function wp_strip_all_tags( $s ) { return trim( strip_tags( (string) $s ) ); }
function wp_date( $f, $ts = null ) { return gmdate( $f, null === $ts ? time() : $ts ); }
function sanitize_email( $s ) { return (string) $s; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function __( $s, $d = '' ) { return $s; }
function add_action( ...$a ) {}

$raiz = dirname( __DIR__ );
require $raiz . '/caaguazu-app-api/includes/class-asistente-fuentes.php';
require $raiz . '/caaguazu-app-api/includes/class-asistente.php';

$fallos = 0;
function comprobar( $etiqueta, $obtenido, $esperado ) {
	global $fallos;
	$ok = ( $obtenido === $esperado );
	if ( ! $ok ) { $fallos++; }
	printf( "%s  %-60s  %s\n", $ok ? 'ok  ' : 'FALLA', $etiqueta, $ok ? '' : 'obtenido: ' . var_export( $obtenido, true ) . '  esperado: ' . var_export( $esperado, true ) );
}

$F = 'CZUAPI_Asistente_Fuentes';
$A = 'CZUAPI_Asistente';

/* ------------------------------------------------------------------------- */
echo "\n== Palabras ==\n";

comprobar( 'tildes y mayúsculas fuera', $F::normalizar( 'Árbol ÑANDUTÍ, Ytú' ), 'arbol nanduti ytu' );
comprobar( 'las palabras vacías no cuentan', $F::tokenizar( '¿Dónde hay una cascada para ir con los chicos?' ), array( 'cascada', 'chicos' ) );
comprobar( 'tampoco en inglés ni portugués', $F::tokenizar( 'Where is the waterfall? Onde fica a cachoeira?' ), array( 'waterfall', 'fica', 'cachoeira' ) );
comprobar( '«Caaguazú» no suma: toda la app es Caaguazú', $F::tokenizar( 'museos de Caaguazú' ), array( 'museos' ) );
comprobar( 'sin repetidas', $F::tokenizar( 'fiesta fiesta FIESTA' ), array( 'fiesta' ) );

comprobar( 'plural encuentra al singular', $F::coincide( 'cascadas', 'cascada' ), true );
comprobar( 'y al revés', $F::coincide( 'museo', 'museos' ), true );
comprobar( 'palabras cortas sólo si son iguales', $F::coincide( 'ruta', 'rutas' ), true );
comprobar( 'un prefijo de tres letras no alcanza', $F::coincide( 'mar', 'marzo' ), false );
comprobar( 'demasiada diferencia no es la misma palabra', $F::coincide( 'casa', 'casamiento' ), false );

/* ------------------------------------------------------------------------- */
echo "\n== Búsqueda ==\n";

$items = array(
	'F1' => array( 'indice' => array( 'titulo' => $F::tokenizar( 'Salto Cascada del Yhú' ), 'meta' => $F::tokenizar( 'Naturaleza' ), 'cuerpo' => $F::tokenizar( 'Una caída de agua en el monte' ) ) ),
	'F2' => array( 'indice' => array( 'titulo' => $F::tokenizar( 'Museo municipal' ), 'meta' => $F::tokenizar( 'Cultura' ), 'cuerpo' => $F::tokenizar( 'Cerca hay una cascada chica' ) ) ),
	'A3' => array( 'indice' => array( 'titulo' => $F::tokenizar( 'Las fiestas patronales' ), 'meta' => $F::tokenizar( 'Tradición, articulo' ), 'cuerpo' => $F::tokenizar( 'La procesión sale de la iglesia' ) ) ),
);
$rank = $F::rankear( $F::tokenizar( '¿Hay cascadas lindas?' ), $items );
comprobar( 'la del título gana a la que la nombra al pasar', array_keys( $rank ), array( 'F1', 'F2' ) );
comprobar( 'título pesa 3, cuerpo 1', array_values( $rank ), array( 3, 1 ) );
comprobar( 'lo que no coincide no entra', isset( $rank['A3'] ), false );
comprobar( 'una pregunta sin palabras no trae nada', $F::rankear( $F::tokenizar( '¿y?' ), $items ), array() );
comprobar( 'una palabra cuenta una vez, por su mejor lugar', $F::puntaje( array( 'cascada' ), array( 'titulo' => array( 'cascada' ), 'meta' => array( 'cascada' ), 'cuerpo' => array( 'cascada' ) ) ), 3 );

/* ------------------------------------------------------------------------- */
echo "\n== Citas ==\n";

comprobar( 'marcas sueltas y agrupadas, en orden, sin repetir', $F::refs_en( 'Abre a las 8 [F12]. También [F12, A3] y [ R5 ].' ), array( 'F12', 'A3', 'R5' ) );
comprobar( 'lo que no es una marca no se toma', $F::refs_en( 'Ver [nota], [F] o [X12].' ), array() );

$refs = array(
	'F12' => array( 'tipo' => 'ficha', 'id' => 12, 'titulo' => 'Salto Cascada', 'tipo_item' => 'sitio' ),
	'A3'  => array( 'tipo' => 'articulo', 'id' => 3, 'titulo' => 'Fiestas', 'tipo_item' => '' ),
);
$c = $F::citas( "Abre de 8 a 17 [F12]. La fiesta es en julio [A3, F99].\n\nNada más [Z1].", $refs );
comprobar( 'las marcas salen del texto, y el espacio con ellas', $c['texto'], "Abre de 8 a 17. La fiesta es en julio.\n\nNada más [Z1]." );
comprobar( 'una marca que no existe no se convierte en enlace', count( $c['fuentes'] ), 2 );
comprobar( 'la fuente lleva tipo, id y título', $c['fuentes'][0], array( 'tipo' => 'ficha', 'id' => 12, 'titulo' => 'Salto Cascada', 'tipo_item' => 'sitio' ) );
comprobar( 'un artículo no lleva tipo_item', array_key_exists( 'tipo_item', $c['fuentes'][1] ), false );

$muchas = array();
for ( $i = 1; $i <= 9; $i++ ) { $muchas[ 'F' . $i ] = array( 'tipo' => 'ficha', 'id' => $i, 'titulo' => 'L' . $i, 'tipo_item' => 'sitio' ); }
comprobar( 'nunca más de seis fuentes', count( $F::citas( '[F1][F2][F3][F4][F5][F6][F7][F8][F9]', $muchas )['fuentes'] ), 6 );

/* ------------------------------------------------------------------------- */
echo "\n== Limpieza ==\n";

comprobar( 'negritas sin asteriscos', $F::limpiar( '**Horario:** de 8 a 17' ), 'Horario: de 8 a 17' );
comprobar( 'títulos sin numerales', $F::limpiar( "## Qué hacer\nIr temprano" ), "Qué hacer\nIr temprano" );
comprobar( 'viñetas con guion', $F::limpiar( "* uno\n• dos" ), "- uno\n- dos" );
comprobar( 'no más de un renglón en blanco', $F::limpiar( "a\n\n\n\nb" ), "a\n\nb" );

/* ------------------------------------------------------------------------- */
echo "\n== Líneas del catálogo ==\n";

comprobar( 'precio 0 es gratis', $F::precio( 0 ), 'gratis' );
comprobar( 'precio sin cargar no se dice', $F::precio( null ), '' );
comprobar( 'precio fuera de rango se acota', $F::precio( 9 ), '4 de 4' );
comprobar( 'recortar corta por palabra y lo marca', $F::recortar( 'una frase bastante larga para cortar', 20 ), 'una frase bastante…' );
comprobar( 'lo que entra no se toca', $F::recortar( 'corta', 20 ), 'corta' );

$linea = $F::linea_ficha( 'F7', array(
	'titulo'          => 'Fiesta  del Poncho',
	'categoria'       => array( 'nombre' => 'Fiestas' ),
	'etiquetas'       => array( array( 'nombre' => 'Familia' ), array( 'nombre' => 'Noche' ) ),
	'fechas'          => array( 'inicio' => '2026-10-10T22:00:00+00:00', 'fin' => null, 'en_curso' => true ),
	'horario_resumen' => '',
	'rango_precio'    => 0,
), 'Música y comida típica.' );
comprobar( 'una línea dice qué es, cuándo, cuánto y de qué se trata', $linea, '[F7] Fiesta del Poncho (Fiestas; Familia, Noche) — cuándo: 10/10/2026 22:00 (en curso) — precio: gratis — Música y comida típica.' );

/* ------------------------------------------------------------------------- */
echo "\n== Presupuesto ==\n";

$bloques = array( 'conocimiento' => str_repeat( 'k', 100 ), 'catalogo' => str_repeat( 'c', 500 ), 'detalle' => str_repeat( 'd', 200 ) );
$orden   = array( 'catalogo', 'conocimiento', 'detalle' );

$r = $A::recortar_bloques( $bloques, 10000, $orden );
comprobar( 'si entra, no se toca nada', $r['fuera'], array() );

$r = $A::recortar_bloques( $bloques, 600, $orden );
comprobar( 'se trunca el catálogo primero', $r['fuera'], array( 'catalogo (truncado)' ) );
comprobar( 'y el detalle queda entero', mb_strlen( $r['bloques']['detalle'] ), 200 );
comprobar( 'el total entra en lo disponible', mb_strlen( implode( '', $r['bloques'] ) ) <= 600, true );

$r = $A::recortar_bloques( $bloques, 150, $orden );
comprobar( 'con muy poco, cae el catálogo y se trunca lo siguiente', $r['fuera'], array( 'catalogo', 'conocimiento', 'detalle (truncado)' ) );
comprobar( 'pero algo del detalle llega igual', isset( $r['bloques']['detalle'] ), true );

/* ------------------------------------------------------------------------- */
echo "\n== Proveedor ==\n";

comprobar( 'base URL suma la ruta de chat', $A::endpoint_desde( 'https://api.ejemplo.com/v1/', true ), 'https://api.ejemplo.com/v1/chat/completions' );
comprobar( 'endpoint completo va tal cual', $A::endpoint_desde( 'https://api.deepseek.com/chat/completions', false ), 'https://api.deepseek.com/chat/completions' );
comprobar( 'conversación: un uuid sirve', $A::conversacion_valida( '3f2a9c1e-0b7d-4e2a-9f11-5c6d7e8f9a0b' ), '3f2a9c1e-0b7d-4e2a-9f11-5c6d7e8f9a0b' );
comprobar( 'conversación: cualquier otra cosa no', $A::conversacion_valida( "x'; DROP" ), '' );

/* ------------------------------------------------------------------------- */
echo "\n== Diagnóstico y aviso ==\n";

$dg = $A::diagnostico( 402 );
comprobar( '402 es saldo', $dg['causa'], 'Se acabó el crédito de la cuenta.' );
$dg = $A::diagnostico( 429, '{"error":{"message":"You exceeded your current quota"}}' );
comprobar( 'un 429 que dice «quota» también es saldo, no espera', $dg['causa'], 'Se acabó el crédito de la cuenta.' );
$dg = $A::diagnostico( 429, 'Rate limit reached' );
comprobar( 'un 429 de verdad se arregla esperando', $dg['causa'], 'El proveedor está limitando la cantidad de pedidos.' );
$dg = $A::diagnostico( 400, 'The model `x` does not exist' );
comprobar( 'modelo retirado', $dg['causa'], 'El modelo configurado ya no existe (lo retiraron o cambió de nombre).' );
$dg = $A::diagnostico( 401 );
comprobar( 'key inválida', $dg['causa'], 'La API key no es válida o fue revocada.' );
$dg = $A::diagnostico( 405 );
comprobar( '405 es la base URL mal marcada', $dg['arreglo'], 'Revisar la URL del endpoint y el interruptor «es una base URL».' );
$dg = $A::diagnostico( 0 );
comprobar( 'sin conexión', $dg['causa'], 'El servidor no pudo conectarse al proveedor (red, DNS o timeout).' );
$dg = $A::diagnostico( 503 );
comprobar( 'caído del otro lado', $dg['causa'], 'El proveedor está caído de su lado.' );

$aviso = $A::mensaje_caida( 'caido', array( 'code' => 401, 'bodyraw' => '{"error":"Invalid API key: sk-prueba-1234567890abcdef"}' ), 'api.deepseek.com' );
comprobar( 'el aviso NUNCA lleva la key configurada', false !== strpos( $aviso['cuerpo'], 'sk-prueba-1234567890abcdef' ), false );
comprobar( 'y dice que la tachó', false !== strpos( $aviso['cuerpo'], '[key oculta]' ), true );
$aviso = $A::mensaje_caida( 'respaldo', array( 'code' => 0, 'error' => 'cURL error 28: Bearer abcdefghijklmnop timed out' ), 'api.x.com' );
comprobar( 'ni un Bearer que venga en el error', false !== strpos( $aviso['cuerpo'], 'abcdefghijklmnop' ), false );
comprobar( 'el aviso de respaldo lo dice en el asunto', $aviso['asunto'], 'Asistente de la app: falló el proveedor principal' );

/* ------------------------------------------------------------------------- */
echo "\n" . ( $fallos ? "\033[31m{$fallos} comprobación(es) fallaron.\033[0m\n" : "\033[32mTodo bien.\033[0m\n" );
exit( $fallos ? 1 : 0 );
