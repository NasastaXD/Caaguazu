<?php
/**
 * Comprobación del enrutado del panel: qué ruta resuelve cada URL.
 *
 *   php tools/verificar-rutas.php
 *
 * Sale con código 1 si algo falla, igual que las otras verificaciones.
 *
 * POR QUÉ EXISTE
 *
 * El panel entero cuelga de un mapa de reglas de reescritura, y ese mapa tiene
 * un comodín —`^turismo-panel/(.+?)/?$`, que es cualquier sección— conviviendo
 * con una docena de rutas específicas: entrar, registro, recuperar, salir, el
 * enlace de invitación y los cuatro recursos de la PWA. WordPress se queda con
 * la PRIMERA regla que matchea, así que el orden no es cosmético: si el comodín
 * queda antes que las específicas, se las come todas y ninguna existe.
 *
 * Eso pasó, y no se vio venir. `add_rewrite_rule( …, 'top' )` no antepone regla
 * por regla: `WP_Rewrite::add_rule()` hace `array_merge( $extra_rules_top,
 * array( $regex => $query ) )`, que appendea dentro del grupo. El mapa estaba
 * escrito de menos a más específico creyendo lo contrario, así que el comodín
 * iba primero. Resultado: `/turismo-panel/entrar` caía en el guard del panel,
 * que redirige a `/turismo-panel/entrar`… que volvía a caer en el guard. El
 * navegador cortaba con «demasiadas redirecciones» y NADIE podía iniciar
 * sesión ni usar un enlace de invitación.
 *
 * Lo que lo hizo difícil de ver es que el panel parecía andar: quien ya tenía
 * la sesión abierta no pasa por /entrar, y guardar contenido funcionaba porque
 * `accion` y `datos` tienen una red de contención dentro de `dispatch()`. Sólo
 * fallaba lo que exige entrar de cero.
 *
 * Un error de orden no tira ningún error: cambia en silencio a dónde va una
 * URL. Por eso está acá, con las 27 URLs reales del panel y contra el mapa de
 * verdad —`PROMOTUR_Router::reglas()`, no una copia—, que es lo único que
 * garantiza que la comprobación siga valiendo cuando alguien agregue una ruta.
 *
 * Se corre sin WordPress: probar esto no puede costar levantar un sitio.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ );
define( 'PROMOTUR_BASE', 'turismo-panel' );

/*
 * Se cargan las clases de verdad —no una copia del mapa— para que esta
 * comprobación siga valiendo cuando alguien agregue una ruta: una copia se
 * desincroniza y pasa a verificar un mapa que ya no es el que corre. Las dos
 * son definiciones de clase que no ejecutan nada al cargarse, y su guarda de
 * ABSPATH pasa porque está definida arriba.
 */
$incluye = dirname( __DIR__ ) . '/caaguazu-portal/includes/';
require $incluye . 'class-router.php';
require $incluye . 'class-acciones.php';

/** Como WP::parse_request(): la primera regla que matchea, y gana. */
function czu_resolver( array $reglas, $path ) {
	foreach ( $reglas as $patron => $destino ) {
		if ( preg_match( '#' . $patron . '#', $path, $m ) ) {
			return preg_replace_callback( '/\$matches\[(\d+)\]/', function ( $x ) use ( $m ) {
				return isset( $m[ (int) $x[1] ] ) ? $m[ (int) $x[1] ] : '';
			}, $destino );
		}
	}
	return '(404 de WordPress)';
}

$casos = array(
	// Auth. Todo esto estuvo inalcanzable: es lo que hay que no perder de vista.
	'turismo-panel/entrar'               => 'index.php?promotur_route=login',
	'turismo-panel/entrar/'              => 'index.php?promotur_route=login',
	'turismo-panel/registro'             => 'index.php?promotur_route=registro',
	'turismo-panel/registro/'            => 'index.php?promotur_route=registro',
	'turismo-panel/recuperar'            => 'index.php?promotur_route=recuperar',
	'turismo-panel/recuperar/'           => 'index.php?promotur_route=recuperar',
	'turismo-panel/recuperar/nueva'      => 'index.php?promotur_route=restablecer',
	'turismo-panel/recuperar/nueva/'     => 'index.php?promotur_route=restablecer',
	'turismo-panel/salir'                => 'index.php?promotur_route=salir',
	'turismo-panel/salir/'               => 'index.php?promotur_route=salir',

	// El enlace de invitación, con y sin barra final: las dos formas circulan.
	'turismo-panel/i/aB3xY9'             => 'index.php?promotur_route=registro&promotur_invitacion=aB3xY9',
	'turismo-panel/i/aB3xY9/'            => 'index.php?promotur_route=registro&promotur_invitacion=aB3xY9',

	// PWA. Sin esto el service worker y el manifiesto devuelven HTML del panel.
	'turismo-panel/manifest.webmanifest' => 'index.php?promotur_route=pwa-manifest',
	'turismo-panel/sw.js'                => 'index.php?promotur_route=pwa-sw',
	'turismo-panel/icon-192.png'         => 'index.php?promotur_route=pwa-icon&promotur_size=192',
	'turismo-panel/offline'              => 'index.php?promotur_route=pwa-offline',
	'turismo-panel/offline/'             => 'index.php?promotur_route=pwa-offline',

	// Las dos puertas: formularios y JavaScript.
	'turismo-panel/datos/save_contenido' => 'index.php?promotur_route=datos&promotur_sub=save_contenido',
	'turismo-panel/accion/invite'        => 'index.php?promotur_route=accion&promotur_sub=invite',

	// El panel, que tiene que seguir resolviendo exactamente igual.
	'turismo-panel'                      => 'index.php?promotur_route=panel',
	'turismo-panel/'                     => 'index.php?promotur_route=panel',
	'turismo-panel/equipo'               => 'index.php?promotur_route=panel&promotur_sub=equipo',
	'turismo-panel/equipo/'              => 'index.php?promotur_route=panel&promotur_sub=equipo',
	'turismo-panel/editor/123'           => 'index.php?promotur_route=panel&promotur_sub=editor/123',
	'turismo-panel/recorridos/nuevo'     => 'index.php?promotur_route=panel&promotur_sub=recorridos/nuevo',

	// Rutas viejas → 301. Hay invitaciones ya repartidas con estas.
	'czu-login'                          => 'index.php?promotur_route=legacy&promotur_sub=entrar',
	'i/aB3xY9'                           => 'index.php?promotur_route=legacy&promotur_sub=i/aB3xY9',
	'turismo/panel/equipo'               => 'index.php?promotur_route=legacy&promotur_sub=equipo',
);

$verde = "\033[32m"; $rojo = "\033[31m"; $gris = "\033[90m"; $fin = "\033[0m";
$reglas = PROMOTUR_Router::reglas();
$fallos = 0;

echo "\n" . $gris . 'Enrutado del panel — ' . count( $reglas ) . ' reglas, ' . count( $casos ) . " URLs" . $fin . "\n\n";

foreach ( $casos as $path => $esperado ) {
	$real = czu_resolver( $reglas, $path );
	if ( $real === $esperado ) {
		echo ' ' . $verde . '✓' . $fin . '  /' . $path . "\n";
		continue;
	}
	$fallos++;
	echo ' ' . $rojo . '✗' . $fin . '  /' . $path . "\n";
	echo '      ' . $gris . 'esperaba: ' . $esperado . $fin . "\n";
	echo '      ' . $gris . 'resolvió: ' . $real . $fin . "\n";
}

/*
 * El comodín tiene que ser la última regla del mapa. Es la comprobación que
 * habría cazado el bucle de redirecciones el día que se escribió: cualquier
 * ruta que quede después de `(.+?)` es inalcanzable, sin importar cuántas
 * URLs de arriba sigan pasando.
 */
$ultima = array_key_last( $reglas );
if ( '^' . PROMOTUR_BASE . '/(.+?)/?$' === $ultima ) {
	echo "\n " . $verde . '✓' . $fin . "  el comodín de sección es la última regla\n";
} else {
	$fallos++;
	echo "\n " . $rojo . '✗' . $fin . "  el comodín de sección NO es la última regla\n";
	echo '      ' . $gris . 'última: ' . $ultima . $fin . "\n";
	echo '      ' . $gris . 'Todo lo que quede después de `(.+?)` es inalcanzable.' . $fin . "\n";
}

/*
 * Y ninguna query var se puede llamar como un campo que viaje en un
 * formulario del panel.
 *
 * `WP::parse_request()` recorre las query vars públicas y le da prioridad a
 * `$_POST[ $var ]` sobre lo que matcheó la regla de reescritura. O sea que un
 * campo del formulario con el mismo nombre que una query var la pisa en cada
 * envío — y sólo en el envío, que es lo que lo vuelve difícil de encontrar.
 *
 * Pasó con el alta por invitación: la query var del token se llamaba
 * `promotur_token`, igual que el campo de seguridad que va en todos los
 * formularios. Abrir el enlace andaba (un GET no manda ese campo); completar
 * el formulario y enviarlo moría con «necesitás una invitación válida»,
 * porque el HMAC de seguridad había ocupado el lugar del token.
 */
$campos_de_formulario = array(
	PROMOTUR_Acciones::CAMPO_TOKEN, // el oculto de seguridad, en todos
	'promotur_auth',                // qué formulario de acceso se envió
);
$router  = new ReflectionClass( 'PROMOTUR_Router' );
$vars    = ( $router->newInstanceWithoutConstructor() )->query_vars( array() );
$chocan  = array_intersect( $vars, $campos_de_formulario );

if ( empty( $chocan ) ) {
	echo ' ' . $verde . '✓' . $fin . "  ninguna query var choca con un campo de formulario\n";
} else {
	$fallos++;
	echo ' ' . $rojo . '✗' . $fin . "  hay query vars que se llaman igual que un campo de formulario\n";
	foreach ( $chocan as $v ) {
		echo '      ' . $gris . $v . ' — el POST la pisa y el valor de la URL se pierde' . $fin . "\n";
	}
}

echo "\n";
if ( $fallos ) {
	echo $rojo . "  $fallos ruta(s) resuelven mal." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  Las ' . count( $casos ) . " URLs resuelven a donde tienen que resolver." . $fin . "\n\n";
