<?php
/**
 * Comprobación del registro de auditoría: que todo lo que se anota se vea.
 *
 *   php tools/verificar-auditoria.php
 *
 * Sale con código 1 si algo falla, igual que las otras verificaciones.
 *
 * POR QUÉ EXISTE
 *
 * «Registros» (wp-admin → Portal Turismo) no muestra la tabla entera: muestra
 * lo que matchea una lista de acciones, una por pestaña. O sea que la lista es
 * un FILTRO, y una acción que se escriba y no figure en ninguna de las dos
 * queda guardada donde nadie la va a ver.
 *
 * Eso es peor que no anotarla. Nadie abre phpMyAdmin: uno mira la pantalla, no
 * ve el evento y concluye que el evento no pasó. Pasó dos veces:
 *
 * - `account_registered` —el alta de verdad— nunca estuvo en la lista, que
 *   traía el `user_registered` viejo de WordPress. Un registro exitoso no
 *   aparecía en la pantalla que uno mira para saber si hubo un registro.
 * - `invitation_error` se agregó en la 3.9.2 para diagnosticar altas rotas, y
 *   nació invisible. La versión que tenía que explicar el bug no podía.
 *
 * Con las dos ausencias juntas, «invitación creada y después nada» se leía como
 * «todavía no la abrieron», cuando era «la abrieron y se rompió». Un bug de
 * tres versiones parecía otra cosa por culpa de un filtro.
 *
 * Un evento invisible no tira ningún error: se escribe bien, se guarda bien, y
 * la única señal es una pantalla vacía. Por eso está acá, contra las listas de
 * verdad —`PROMOTUR_Audit::user_actions()` y `post_actions()`, no una copia—.
 *
 * Se corre sin WordPress: probar esto no puede costar levantar un sitio.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ );

$portal = dirname( __DIR__ ) . '/caaguazu-portal';
require $portal . '/includes/class-audit.php';

$verde = "\033[32m"; $rojo = "\033[31m"; $gris = "\033[90m"; $fin = "\033[0m";

/*
 * Las acciones tal como se escriben. Sólo las literales: las de contenido se
 * arman con el tipo (`$tipo . '_publicado'`) y de ésas se encarga
 * `post_actions()`, que las genera con la misma vuelta.
 */
$anotadas = array();
$rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $portal ) );
foreach ( $rii as $archivo ) {
	if ( $archivo->isDir() || 'php' !== $archivo->getExtension() ) {
		continue;
	}
	if ( ! preg_match_all(
		"/PROMOTUR_Audit::log\(\s*'([a-z0-9_]+)'/",
		file_get_contents( $archivo->getPathname() ),
		$m
	) ) {
		continue;
	}
	foreach ( $m[1] as $accion ) {
		$anotadas[ $accion ][] = str_replace( $portal . '/', '', $archivo->getPathname() );
	}
}
ksort( $anotadas );

$visibles = array_merge( PROMOTUR_Audit::user_actions(), PROMOTUR_Audit::post_actions() );
$fallos   = 0;

echo "\n" . $gris . 'Auditoría — ' . count( $anotadas ) . ' acciones que se anotan, '
	. count( $visibles ) . " visibles en Registros" . $fin . "\n\n";

foreach ( $anotadas as $accion => $archivos ) {
	if ( in_array( $accion, $visibles, true ) ) {
		echo ' ' . $verde . '✓' . $fin . '  ' . $accion . "\n";
		continue;
	}
	$fallos++;
	echo ' ' . $rojo . '✗' . $fin . '  ' . $accion . "\n";
	echo '      ' . $gris . 'se anota en ' . implode( ', ', array_unique( $archivos ) ) . $fin . "\n";
	echo '      ' . $gris . 'y no está en ninguna lista de Registros: se guarda y no se ve.' . $fin . "\n";
}

/*
 * Y al revés: una acción listada que ya nadie escribe deja una pestaña
 * prometiendo algo que nunca va a aparecer. Es lo que hacía `user_registered`
 * después del cutover de identidad, y es lo que tapó a `account_registered`.
 * No es un error —no rompe nada— pero sí una lista que miente, así que se
 * avisa sin hacer fallar la corrida: las de contenido se generan por tipo y
 * puede haber tipos sin uso todavía.
 */
$muertas = array_diff( PROMOTUR_Audit::user_actions(), array_keys( $anotadas ) );
if ( $muertas ) {
	echo "\n " . $gris . 'ojo: listadas y que ya nadie escribe — ' . implode( ', ', $muertas ) . $fin . "\n";
}

echo "\n";
if ( $fallos ) {
	echo $rojo . "  $fallos acción(es) se anotan donde nadie las ve." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  Las ' . count( $anotadas ) . " acciones que se anotan se ven en Registros." . $fin . "\n\n";
