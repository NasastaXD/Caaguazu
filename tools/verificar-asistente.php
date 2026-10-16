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
// Y las de la voz y el oído de la presentación, que tampoco pueden salir.
define( 'CZUAPI_VOZ_KEY', 'voz9f8e7d6c5b4a3210' );
define( 'CZUAPI_OIDO_KEY', 'oido0a1b2c3d4e5f6789' );

$GLOBALS['czu_opciones'] = array();
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['czu_opciones'] ) ? $GLOBALS['czu_opciones'][ $k ] : $d; }
function wp_strip_all_tags( $s ) { return trim( strip_tags( (string) $s ) ); }
function wp_date( $f, $ts = null ) { return gmdate( $f, null === $ts ? time() : $ts ); }
function sanitize_email( $s ) { return (string) $s; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function __( $s, $d = '' ) { return $s; }
function add_action( ...$a ) {}
function wp_generate_password( $n = 12, $especiales = true ) { return str_repeat( 'a', $n ); }
function is_wp_error( $x ) { return false; }
function wp_remote_retrieve_response_code( $r ) { return $r['response']['code']; }
function wp_remote_retrieve_body( $r ) { return $r['body']; }
/* El HTTP de prueba: guarda lo que se mandó y contesta lo que se le cargó. */
$GLOBALS['http_enviado'] = null;
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 200 ), 'body' => '{"text":"hola cómo funciona esto"}' );
function wp_remote_post( $url, $args ) {
	$GLOBALS['http_enviado']    = array( $url, $args );
	$GLOBALS['http_llamadas'][] = array( $url, $args );
	// Con cola, cada llamada contesta lo siguiente; sin cola, siempre lo mismo.
	return ! empty( $GLOBALS['http_cola'] ) ? array_shift( $GLOBALS['http_cola'] ) : $GLOBALS['http_respuesta'];
}
function esc_url_raw( $u ) { return (string) $u; }
/* Transitorios en memoria, para probar la charla de la presentación. */
$GLOBALS['czu_transitorios'] = array();
function get_transient( $k ) { return $GLOBALS['czu_transitorios'][ $k ] ?? false; }
function set_transient( $k, $v, $t = 0 ) { $GLOBALS['czu_transitorios'][ $k ] = $v; return true; }
function delete_transient( $k ) { unset( $GLOBALS['czu_transitorios'][ $k ] ); return true; }
function wp_generate_uuid4() { return 'uuid-prueba-1234'; }
function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }

$raiz = dirname( __DIR__ );
require $raiz . '/caaguazu-app-api/includes/class-asistente-fuentes.php';
require $raiz . '/caaguazu-app-api/includes/class-asistente.php';
require $raiz . '/caaguazu-app-api/includes/class-presentacion.php';

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

$con_borrador = array(
	'F12' => array( 'tipo' => 'ficha', 'id' => 12, 'titulo' => 'Salto Cascada', 'tipo_item' => 'sitio', 'borrador' => false ),
	'F40' => array( 'tipo' => 'ficha', 'id' => 40, 'titulo' => 'En camino', 'tipo_item' => 'sitio', 'borrador' => true ),
);
$c = $F::citas( 'Abre a las 8 [F12]. Y hay otro lugar [F40].', $con_borrador );
comprobar( 'un borrador se usa pero no se ofrece como enlace (la app no lo puede abrir)', array_column( $c['fuentes'], 'id' ), array( 12 ) );
comprobar( 'y su marca también sale del texto', $c['texto'], 'Abre a las 8. Y hay otro lugar.' );

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

/* Lo que el profesor puede elegir desde el panel: cada valor tiene que quedar en un rango seguro. */
comprobar( 'memoria: turnos fuera de rango se recortan (ni negativos ni 500)', array( $A::normalizar_turnos( -3 ), $A::normalizar_turnos( 500 ), $A::normalizar_turnos( '7' ) ), array( 0, 20, 7 ) );
comprobar( 'memoria: horas entre 1 y 24', array( $A::normalizar_horas( 0 ), $A::normalizar_horas( 99 ), $A::normalizar_horas( 6 ) ), array( 1, 24, 6 ) );
comprobar( 'fuentes: sólo valores conocidos, en orden fijo', $A::normalizar_fuentes( array( 'articulos', 'basura', 'lugares', '<script>' ) ), array( 'lugares', 'articulos' ) );
comprobar( 'fuentes: lo que no es lista no prende nada', $A::normalizar_fuentes( 'lugares' ), array() );
comprobar( 'personalidad: los saltos de Windows no hacen diferencia para saber si es la de fábrica', $A::normalizar_texto( "a\r\nb" ), "a\nb" );

/* ------------------------------------------------------------------------- */
echo "\n== Presentación ==\n";

$P = 'CZUAPI_Presentacion';

/*
 * Lo que se manda a convertir en voz: el sintetizador lee todo tal cual, así
 * que un guion de lista se oiría como «guion» y una dirección web, letra por
 * letra.
 */
comprobar( 'la voz no lee guiones de lista: cada renglón es una frase', $P::para_voz( "Tres cosas:\n- la app\n- la web\n- el panel" ), 'Tres cosas: la app. la web. el panel.' );
comprobar( 'ni números de lista', $P::para_voz( "1. Primero\n2) Después" ), 'Primero. Después.' );
comprobar( 'ni direcciones web', $P::para_voz( 'Entrá a https://caaguazu.net/turismo para verlo.' ), 'Entrá a para verlo.' );
comprobar( 'no le agrega punto a lo que ya termina', $P::para_voz( '¿Qué tal?' ), '¿Qué tal?' );
comprobar( 'vacío es vacío', $P::para_voz( "  \n " ), '' );

comprobar( 'velocidad entre 0,5 y 2, con coma o con punto', array( $P::normalizar_velocidad( '0,2' ), $P::normalizar_velocidad( 9 ), $P::normalizar_velocidad( '1.25' ), $P::normalizar_velocidad( '' ) ), array( 0.5, 2.0, 1.25, 1.0 ) );
comprobar( 'idioma del dictado: una etiqueta BCP 47 o es-PY', array( $P::normalizar_idioma( 'es-AR' ), $P::normalizar_idioma( 'gn' ), $P::normalizar_idioma( 'es PY; drop' ), $P::normalizar_idioma( '' ) ), array( 'es-AR', 'gn', 'es-PY', 'es-PY' ) );
comprobar( 'código de sala: letras y números, en mayúsculas', array( $P::sala_valida( 'ab12cd' ), $P::sala_valida( 'ab' ), $P::sala_valida( '../x' ), $P::sala_valida( 'ABCDEFGHIJKLM' ) ), array( 'AB12CD', '', '', '' ) );

comprobar( 'el panel muestra sólo el final de una key', $P::pista_key( 'sk-abcdefgh1234' ), '…1234' );
comprobar( 'y nada si no hay', $P::pista_key( '' ), '' );
$error = $P::redactar( 'HTTP 401 — invalid key voz9f8e7d6c5b4a3210 / oido0a1b2c3d4e5f6789 / sk-prueba-1234567890abcdef' );
comprobar( 'un error del proveedor NUNCA lleva la key de la voz', false !== strpos( $error, 'voz9f8e7d6c5b4a3210' ), false );
comprobar( 'ni la del oído', false !== strpos( $error, 'oido0a1b2c3d4e5f6789' ), false );
comprobar( 'ni la del asistente', false !== strpos( $error, 'sk-prueba-1234567890abcdef' ), false );

/*
 * El recorte del prompt: con poco presupuesto, el guion —lo único que se
 * escribió para esta charla— es lo último que se cae.
 */
$GLOBALS['czu_opciones'] = array( 'czuapi_pres_conocimiento' => str_repeat( 'guion ', 400 ), 'czuapi_ia_presupuesto' => 4000 );
$prompt = $P::armar_sistema( array( 'catalogo' => str_repeat( 'catalogo ', 2000 ), 'detalle' => '' ), 'es' );
comprobar( 'con poco presupuesto se va el catálogo y queda el guion', array( false !== strpos( $prompt, '[GUION]' ), false !== strpos( $prompt, 'catalogo catalogo catalogo catalogo catalogo catalogo catalogo catalogo catalogo catalogo catalogo' ) ), array( true, false ) );
comprobar( 'las reglas de la voz van siempre', false !== strpos( $prompt, '# CÓMO SE ESCUCHA' ), true );
$GLOBALS['czu_opciones'] = array();

/* ------------------------------------------------------------------------- */
echo "\n== Transcripción: OpenAI y ElevenLabs ==\n";

$log = tempnam( sys_get_temp_dir(), 'oido' );
ini_set( 'error_log', $log );
$audio = tempnam( sys_get_temp_dir(), 'voz' );
file_put_contents( $audio, 'AUDIO-PRUEBA' );
$dicho = 'frase secreta de la sala';

/* ElevenLabs: su endpoint, su cabecera, sus campos. */
$GLOBALS['czu_opciones'] = array(
	'czuapi_pres_oido_modo'      => 'servidor',
	'czuapi_pres_oido_proveedor' => 'elevenlabs',
	'czuapi_pres_oido_idioma'    => 'es-PY',
	'czuapi_pres_oido_endpoint'  => 'https://api.openai.com/v1/audio/transcriptions', // quedó del proveedor anterior
	'czuapi_pres_oido_modelo'    => 'whisper-1',                                     // ídem
);
$r = $P::oir( $audio, 'audio/webm;codecs=opus' );
$cfg = $P::oido();
comprobar( 'ElevenLabs: el endpoint de OpenAI que quedó cargado se descarta', $cfg['endpoint'], 'https://api.elevenlabs.io/v1/speech-to-text' );
comprobar( 'ElevenLabs: el modelo de OpenAI que quedó cargado se descarta', $cfg['modelo'], 'scribe_v1' );
comprobar( 'ElevenLabs: la key va en xi-api-key, no en Bearer', $cfg['cabecera'], 'xi-api-key' );
$enviado = $GLOBALS['http_enviado'];
comprobar( 'ElevenLabs: se manda al endpoint de ElevenLabs', $enviado[0], 'https://api.elevenlabs.io/v1/speech-to-text' );
comprobar( 'ElevenLabs: la key va en la cabecera xi-api-key', $enviado[1]['headers']['xi-api-key'] ?? '', CZUAPI_OIDO_KEY );
comprobar( 'ElevenLabs: sin cabecera Authorization', isset( $enviado[1]['headers']['Authorization'] ), false );
comprobar( 'ElevenLabs: el modelo va en model_id', (bool) preg_match( '/name="model_id"\r\n\r\nscribe_v1\r\n/', $enviado[1]['body'] ), true );
comprobar( 'ElevenLabs: el idioma va en language_code, en dos letras', (bool) preg_match( '/name="language_code"\r\n\r\nes\r\n/', $enviado[1]['body'] ), true );
comprobar( 'ElevenLabs: el archivo viaja en el campo file', (bool) preg_match( '/name="file"; filename="voz\.webm"/', $enviado[1]['body'] ), true );
comprobar( 'ElevenLabs: y NO lleva los campos de OpenAI', array( (bool) strpos( $enviado[1]['body'], 'name="model"' ), (bool) strpos( $enviado[1]['body'], 'response_format' ) ), array( false, false ) );
comprobar( 'ElevenLabs: el texto que contesta se devuelve', array( $r['ok'], $r['texto'] ), array( true, 'hola cómo funciona esto' ) );

/* Un rechazo: la causa llega, sin la key. */
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 401 ), 'body' => '{"detail":{"message":"Invalid API key ' . CZUAPI_OIDO_KEY . '"}}' );
$r = $P::oir( $audio, 'audio/webm' );
comprobar( 'ElevenLabs: un 401 se informa con su código', array( $r['ok'], $r['code'] ), array( false, 401 ) );
comprobar( 'ElevenLabs: y la causa NUNCA lleva la key', false !== strpos( $r['error'], CZUAPI_OIDO_KEY ), false );

/* Tiempo real (WebSocket): el token, la URL y el silencio. */
$GLOBALS['czu_opciones'] = array( 'czuapi_pres_oido_modo' => 'tiempo_real', 'czuapi_pres_oido_proveedor' => 'elevenlabs', 'czuapi_pres_oido_idioma' => 'es-PY', 'czuapi_pres_oido_silencio' => 1.2 );
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 200 ), 'body' => '{"token":"sutkn_abc123"}' );
$t = $P::crear_token_oido();
comprobar( 'tiempo real: crea el token contra el endpoint de ElevenLabs', array( $t['ok'], $t['token'], $GLOBALS['http_enviado'][0] ), array( true, 'sutkn_abc123', 'https://api.elevenlabs.io/v1/single-use-token/realtime_scribe' ) );
comprobar( 'tiempo real: la key viaja en la cabecera xi-api-key', $GLOBALS['http_enviado'][1]['headers']['xi-api-key'] ?? '', CZUAPI_OIDO_KEY );
$url = $P::url_tiempo_real( 'sutkn_abc123', 'es-PY', 1.2 );
parse_str( (string) parse_url( $url, PHP_URL_QUERY ), $q );
comprobar( 'tiempo real: la URL es wss con el modelo, el VAD y PCM 16 kHz', array( str_starts_with( $url, 'wss://' ), $q['model_id'], $q['commit_strategy'], $q['audio_format'], $q['language_code'], $q['token'] ), array( true, 'scribe_v2_realtime', 'vad', 'pcm_16000', 'es', 'sutkn_abc123' ) );
comprobar( 'tiempo real: la pausa que corta la frase llega a la URL', (float) $q['vad_silence_threshold_secs'], 1.2 );
comprobar( 'tiempo real: la URL no lleva la key (sólo el token de un uso)', false !== strpos( $url, CZUAPI_OIDO_KEY ), false );
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 401 ), 'body' => '{"detail":"Invalid API key ' . CZUAPI_OIDO_KEY . '"}' );
$t = $P::crear_token_oido();
comprobar( 'tiempo real: un rechazo informa el código y NUNCA la key', array( $t['ok'], $t['code'], false !== strpos( $t['error'], CZUAPI_OIDO_KEY ) ), array( false, 401, false ) );
comprobar( 'tiempo real: el silencio se acota a un rango sensato', array( $P::normalizar_silencio( 0 ), $P::normalizar_silencio( 99 ), $P::normalizar_silencio( 'x' ) ), array( $P::SILENCIO_DEFAULT, 5.0, $P::SILENCIO_DEFAULT ) );
comprobar( 'tiempo real: 0,1 sube al piso de 0,5 y «1,2» con coma se entiende', array( $P::normalizar_silencio( 0.1 ), $P::normalizar_silencio( '1,2' ) ), array( 0.5, 1.2 ) );

/* OpenAI: lo de siempre, sin tocarlo. */
$GLOBALS['czu_opciones'] = array( 'czuapi_pres_oido_modo' => 'servidor', 'czuapi_pres_oido_proveedor' => 'openai', 'czuapi_pres_oido_idioma' => 'es-PY' );
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 200 ), 'body' => '{"text":"hola"}' );
$r = $P::oir( $audio, 'audio/webm' );
$enviado = $GLOBALS['http_enviado'];
comprobar( 'OpenAI: endpoint y cabecera Bearer de siempre', array( $enviado[0], $enviado[1]['headers']['Authorization'] ?? '' ), array( 'https://api.openai.com/v1/audio/transcriptions', 'Bearer ' . CZUAPI_OIDO_KEY ) );
comprobar( 'OpenAI: campos model y response_format', array( (bool) strpos( $enviado[1]['body'], 'name="model"' ), (bool) strpos( $enviado[1]['body'], 'name="response_format"' ) ), array( true, true ) );

/* Los logs: qué pasó, y nunca la key ni lo dicho. */
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 200 ), 'body' => '{"text":"' . $dicho . '"}' );
$P::oir( $audio, 'audio/webm' );
$lineas = (string) file_get_contents( $log );
comprobar( 'el log dice que hubo un pedido, a qué destino y con qué modelo', (bool) strpos( $lineas, '[CzuApi][Oido] pedido: proveedor=elevenlabs' ) || (bool) strpos( $lineas, 'pedido: proveedor=openai' ), true );
comprobar( 'el log dice cuántos caracteres volvieron', (bool) strpos( $lineas, 'respuesta en' ) && (bool) strpos( $lineas, 'caracteres' ), true );
comprobar( 'el log NUNCA lleva la key', false !== strpos( $lineas, CZUAPI_OIDO_KEY ), false );
comprobar( 'el log NUNCA lleva lo que se dijo', false !== strpos( $lineas, $dicho ), false );
@unlink( $log ); @unlink( $audio );

/* ------------------------------------------------------------------------- */
echo "\n== Memoria, modelo y etiquetas de la presentación ==\n";

$GLOBALS['czu_opciones'] = array( 'czuapi_ia_memoria' => 0, 'czuapi_pres_turnos' => 20, 'czuapi_pres_modelo' => 'modelo-rapido', 'czuapi_ia_activo' => 1 );
define( 'CZUAPI_IA_KEY2_UNUSED', 1 );
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"Respuesta uno."}}]}' );
$GLOBALS['http_enviado'] = null;
// La memoria del asistente de la app está en 0: la de la presentación NO se entera.
$P::contestar( 'hola', '', 'es', false );
$P::contestar( 'y el horario?', 'uuid-prueba-1234', 'es', false );
$enviado = $GLOBALS['http_enviado'][1]['body'] ?? '';
$datos   = json_decode( $enviado, true );
comprobar( 'la presentación recuerda aunque la memoria del asistente esté en 0', count( $datos['messages'] ?? array() ), 4 );
comprobar( 'y lo que recuerda es lo que se dijo antes', $datos['messages'][1]['content'] ?? '', 'hola' );
comprobar( 'el modelo es el de la presentación, no el del asistente', $datos['model'] ?? '', 'modelo-rapido' );

$GLOBALS['czu_opciones']['czuapi_pres_turnos'] = 0;
$GLOBALS['czu_transitorios'] = array();
$P::contestar( 'uno', '', 'es', false );
$P::contestar( 'dos', 'uuid-prueba-1234', 'es', false );
$datos = json_decode( $GLOBALS['http_enviado'][1]['body'], true );
comprobar( 'con 0 turnos en la presentación, cada pregunta se contesta sola', count( $datos['messages'] ?? array() ), 2 );

/* Etiquetas: a la voz de ElevenLabs sólo las que entiende; a la pantalla, ninguna. */
$texto = 'Hola [pause] qué tal [laughs] vamos [hacer algo raro] [pausa] y [F12] listo.';
comprobar( 'a la voz: sólo las de la lista; las marcas internas y el resto se quitan', $P::etiquetas( $texto, true ), 'Hola [pause] qué tal [laughs] vamos y listo.' );
comprobar( 'a la pantalla: ninguna etiqueta', $P::etiquetas( $texto, false ), 'Hola qué tal vamos y listo.' );
comprobar( 'sin etiquetas, el texto sigue igual', $P::etiquetas( 'Abre de 8 a 17.', true ), 'Abre de 8 a 17.' );

/* El prompt sólo les habla de etiquetas cuando la voz es de ElevenLabs. */
$GLOBALS['czu_opciones']['czuapi_pres_voz_proveedor'] = 'elevenlabs';
$GLOBALS['czu_opciones']['czuapi_pres_etiquetas'] = 1;
$GLOBALS['czu_opciones']['czuapi_pres_voz_key'] = 'eleven-voz-1234567890';
$GLOBALS['czu_opciones']['czuapi_pres_voz_id'] = 'voz-x';
$prompt = $P::armar_sistema( array( 'catalogo' => '', 'detalle' => '' ), 'es' );
comprobar( 'con ElevenLabs, el prompt explica las etiquetas', str_contains( $prompt, '# ETIQUETAS DE EXPRESIÓN' ) && str_contains( $prompt, '[pause]' ), true );
$GLOBALS['czu_opciones']['czuapi_pres_voz_proveedor'] = 'minimax';
$prompt = $P::armar_sistema( array( 'catalogo' => '', 'detalle' => '' ), 'es' );
comprobar( 'con otra voz, el prompt no las menciona', str_contains( $prompt, '# ETIQUETAS DE EXPRESIÓN' ), false );

/* La pantalla nunca recibe las etiquetas, aunque el modelo las ponga. */
$GLOBALS['czu_opciones']['czuapi_pres_voz_proveedor'] = 'elevenlabs';
$GLOBALS['czu_opciones']['czuapi_pres_etiquetas'] = 0;
$GLOBALS['czu_transitorios'] = array();
$GLOBALS['http_respuesta'] = array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"Hola [laughs] buen día."}}]}' );
$r = $P::contestar( 'hola', '', 'es', false );
comprobar( 'la respuesta que se muestra no lleva etiquetas', $r['respuesta'], 'Hola buen día.' );
$GLOBALS['czu_opciones']['czuapi_pres_etiquetas'] = 0;
$prompt = $P::armar_sistema( array( 'catalogo' => '', 'detalle' => '' ), 'es' );
comprobar( 'con el interruptor apagado, aunque sea ElevenLabs, el prompt no habla de etiquetas', str_contains( $prompt, '# ETIQUETAS DE EXPRESIÓN' ), false );
comprobar( 'y la voz recibe las etiquetas sólo si el interruptor está prendido', $P::etiquetas( 'Hola [pause] listo', $P::etiquetas_activas() ), 'Hola listo' );
$GLOBALS['czu_opciones']['czuapi_pres_etiquetas'] = 1;
comprobar( 'prendido, la voz sí las recibe', $P::etiquetas( 'Hola [pause] listo', $P::etiquetas_activas() ), 'Hola [pause] listo' );

/* El conocimiento: el de prueba, con marcadores, hasta que el equipo guarde el suyo. */
unset( $GLOBALS['czu_opciones']['czuapi_pres_conocimiento'] );
comprobar( 'sin conocimiento guardado, va el de prueba con marcadores', str_contains( $P::conocimiento(), '‹descripción' ) && str_contains( $P::conocimiento(), 'Ykua La Patria' ), true );
comprobar( 'el de prueba trae los 40 lugares de la investigación y ningún evento', array( substr_count( $P::conocimiento(), "\n- " ), false !== strpos( $P::conocimiento(), 'EVENTOS' ) ), array( 40, false ) );
comprobar( 'trae datos de la investigación (precio, horario) y marca lo dudoso y lo que falta', str_contains( $P::conocimiento(), 'G. 35.000' ) && str_contains( $P::conocimiento(), '8:00 a 18:00' ) && str_contains( $P::conocimiento(), '‹dato a confirmar›' ) && str_contains( $P::conocimiento(), '‹contacto›' ), true );
require_once __DIR__ . '/generar-conocimiento-prueba.php';
comprobar( 'el archivo del plugin está al día con el JSON de la investigación (php tools/generar-conocimiento-prueba.php --escribir)', trim( file_get_contents( CZU_TXT_PRUEBA ) ), trim( czu_conocimiento_de_prueba() ) );
$GLOBALS['czu_opciones']['czuapi_pres_conocimiento'] = '';
comprobar( 'y si el equipo lo dejó vacío, queda vacío (no vuelve el de prueba)', $P::conocimiento(), '' );

/* Búsqueda en internet: sólo cuando el modelo avisa que el dato no está. */
$GLOBALS['czu_opciones'] = array( 'czuapi_ia_activo' => 1, 'czuapi_pres_web' => 1, 'czuapi_ia_endpoint' => 'https://api.openai.com/v1/chat/completions', 'czuapi_ia_modelo' => 'gpt-6-luna' );
comprobar( 'la dirección de la Responses API sale de la de chat', CZUAPI_Asistente::endpoint_respuestas( 'https://api.openai.com/v1/chat/completions' ), 'https://api.openai.com/v1/responses' );
comprobar( 'sin /chat/completions no hay de dónde sacarla', CZUAPI_Asistente::endpoint_respuestas( 'https://otro.test/api' ), '' );
$GLOBALS['http_llamadas'] = array();
$GLOBALS['http_cola']     = array(
	array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"No tengo el horario del museo. [WEB]"}}]}' ),
	array( 'response' => array( 'code' => 200 ), 'body' => json_encode( array( 'output' => array(
		array( 'type' => 'web_search_call', 'status' => 'completed' ),
		array( 'type' => 'message', 'content' => array( array(
			'type' => 'output_text',
			'text' => 'El museo abre de 8 a 12 ([diario.com.py](https://diario.com.py/museo)).',
			'annotations' => array(
				array( 'type' => 'url_citation', 'url' => 'https://diario.com.py/museo', 'title' => 'Museo de la Madera' ),
				array( 'type' => 'url_citation', 'url' => 'https://diario.com.py/museo', 'title' => 'Repetido' ),
				array( 'type' => 'url_citation', 'url' => 'javascript:alert(1)', 'title' => 'Malo' ),
			),
		) ) ),
	) ) ) ),
);
$r = CZUAPI_Asistente::preguntar( '¿a qué hora abre el museo?', array(), 'es', null, array( 'sin_fuentes' => true, 'web' => true ) );
comprobar( 'primero pregunta por chat y, con la marca, busca por la Responses API', array_map( static function ( $l ) { return $l[0]; }, $GLOBALS['http_llamadas'] ), array( 'https://api.openai.com/v1/chat/completions', 'https://api.openai.com/v1/responses' ) );
$pedido = json_decode( $GLOBALS['http_llamadas'][1][1]['body'], true );
comprobar( 'la búsqueda usa web_search, ubicada en Caaguazú, con el mismo modelo', array( $pedido['tools'][0]['type'], $pedido['tools'][0]['user_location']['city'], $pedido['model'] ), array( 'web_search', 'Caaguazú', 'gpt-6-luna' ) );
comprobar( 'el chat recibió la instrucción de la marca', false !== strpos( json_decode( $GLOBALS['http_llamadas'][0][1]['body'], true )['messages'][0]['content'], '[WEB]' ), true );
comprobar( 'la respuesta sale de internet, sin los enlaces en el texto, y con el aviso', $r['respuesta'], "El museo abre de 8 a 12.\n\n" . CZUAPI_Asistente::nota_web( 'es' ) );
comprobar( 'los enlaces van aparte, sin repetir y sólo http(s)', $r['enlaces'], array( array( 'titulo' => 'Museo de la Madera', 'url' => 'https://diario.com.py/museo' ) ) );
comprobar( 'lo de internet no se mezcla con las fuentes de la guía', $r['fuentes'], array() );

$GLOBALS['http_llamadas'] = array();
$GLOBALS['http_cola']     = array(
	array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"No tengo ese dato. [WEB]"}}]}' ),
	array( 'response' => array( 'code' => 500 ), 'body' => '{"error":"caído"}' ),
);
$r = CZUAPI_Asistente::preguntar( '¿y el teléfono?', array(), 'es', null, array( 'sin_fuentes' => true, 'web' => true ) );
comprobar( 'si la búsqueda falla, queda la respuesta de siempre, sin la marca', array( $r['ok'], $r['respuesta'], $r['enlaces'] ), array( true, 'No tengo ese dato.', array() ) );

$GLOBALS['http_llamadas'] = array();
$GLOBALS['http_cola']     = array( array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"No tengo ese dato. [WEB]"}}]}' ) );
$r = CZUAPI_Asistente::preguntar( '¿y el teléfono?', array(), 'es', null, array( 'sin_fuentes' => true, 'web' => false ) );
comprobar( 'con la búsqueda apagada no se busca, y la marca no llega a la persona', array( count( $GLOBALS['http_llamadas'] ), $r['respuesta'] ), array( 1, 'No tengo ese dato.' ) );
comprobar( 'apagado, el chat ni siquiera recibe la instrucción de la marca', false !== strpos( json_decode( $GLOBALS['http_llamadas'][0][1]['body'], true )['messages'][0]['content'], '[WEB]' ), false );
$GLOBALS['http_cola'] = array();

/* Borradores: apagados por defecto, y cada IA con su interruptor. */
unset( $GLOBALS['czu_opciones']['czuapi_pres_borradores'], $GLOBALS['czu_opciones']['czuapi_ia_borradores'] );
comprobar( 'los borradores arrancan apagados en las dos IA', array( $P::usa_borradores(), CZUAPI_Asistente::borradores_activos() ), array( false, false ) );
$GLOBALS['czu_opciones']['czuapi_pres_borradores'] = 1;
comprobar( 'el de la presentación no prende el del asistente', array( $P::usa_borradores(), CZUAPI_Asistente::borradores_activos() ), array( true, false ) );
unset( $GLOBALS['czu_opciones']['czuapi_pres_borradores'] );

/* La temperatura: la presentación tiene la suya, más alta que la del asistente. */
unset( $GLOBALS['czu_opciones']['czuapi_pres_temperatura'] );
comprobar( 'la presentación arranca con temperatura 0,9', $P::temperatura(), 0.9 );
comprobar( 'la temperatura se normaliza (coma, topes, vacío)', array( $P::normalizar_temperatura( '1,1' ), $P::normalizar_temperatura( 5 ), $P::normalizar_temperatura( -1 ), $P::normalizar_temperatura( '' ) ), array( 1.1, 1.5, 0.0, 0.9 ) );
$GLOBALS['czu_opciones'] = array( 'czuapi_ia_activo' => 1, 'czuapi_ia_endpoint' => 'https://api.openai.com/v1/chat/completions', 'czuapi_ia_modelo' => 'gpt-6-luna', 'czuapi_ia_temperatura' => 0.5 );
$GLOBALS['http_llamadas'] = array();
$GLOBALS['http_cola']     = array( array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"Hola."}}]}' ), array( 'response' => array( 'code' => 200 ), 'body' => '{"choices":[{"message":{"content":"Hola."}}]}' ) );
CZUAPI_Asistente::preguntar( 'hola', array(), 'es', null, array( 'sin_fuentes' => true, 'temperatura' => 0.9 ) );
CZUAPI_Asistente::preguntar( 'hola', array(), 'es', null, array( 'sin_fuentes' => true ) );
comprobar( 'la temperatura pedida llega al modelo, y sin pedirla va la del asistente', array( json_decode( $GLOBALS['http_llamadas'][0][1]['body'], true )['temperature'], json_decode( $GLOBALS['http_llamadas'][1][1]['body'], true )['temperature'] ), array( 0.9, 0.5 ) );

/* La persona: saludar una sola vez. */
comprobar( 'la persona dice que saluda una sola vez', str_contains( $P::persona_de_fabrica(), 'saludá UNA vez' ), true );

/* El título viaja como texto: sin las entidades que agrega wptexturize. */
if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $p = null ) { return 'Rustico&#8217;s Café &#8211; Restaurant &amp; Bar'; }
}
require_once $raiz . '/caaguazu-app-api/includes/helpers.php';
comprobar( 'el título sale sin entidades HTML', czuapi_titulo( 1 ), 'Rustico’s Café – Restaurant & Bar' );

/* ------------------------------------------------------------------------- */
echo "\n" . ( $fallos ? "\033[31m{$fallos} comprobación(es) fallaron.\033[0m\n" : "\033[32mTodo bien.\033[0m\n" );
exit( $fallos ? 1 : 0 );
