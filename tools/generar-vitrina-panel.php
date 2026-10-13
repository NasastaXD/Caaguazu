<?php
/**
 * Arma las pantallas del panel que muestra la vitrina (`/turismo/vitrina/`).
 *
 *   php tools/generar-vitrina-panel.php
 *
 * Son las mismas plantillas de `caaguazu-portal`, dibujadas con la vista
 * previa (`vista-previa-panel.php`) y sus datos de mentira: nada de lo que se
 * ve es contenido real. Se guardan como HTML suelto dentro del espejo web y
 * los enlaces del menú se reescriben para pasar de una pantalla a otra sin
 * salir de la vitrina; el resto de los botones no hace nada.
 *
 * Se vuelve a correr cuando cambia el diseño del panel.
 */

$raiz    = dirname( __DIR__ );
$destino = $raiz . '/caaguazu-web-ios/sitio/vitrina/panel/';
$base    = 'https://caaguazu.net/turismo-panel/';

$secciones = array( 'home', 'mis-contenidos', 'revision', 'tareas', 'inventario', 'articulos', 'recorridos', 'equipo', 'reportes', 'biblioteca', 'estructura', 'asistente' );

if ( ! is_dir( $destino ) ) {
	mkdir( $destino, 0755, true );
}

$hechas = array();
foreach ( $secciones as $s ) {
	$html = shell_exec( 'php ' . escapeshellarg( __DIR__ . '/vista-previa-panel.php' ) . ' ' . escapeshellarg( 'sections/' . $s ) . ' 2>/dev/null' );
	if ( ! is_string( $html ) || strlen( $html ) < 1000 ) {
		fwrite( STDERR, "sin dibujar: $s\n" );
		continue;
	}
	$hechas[] = $s;
	file_put_contents( $destino . $s . '.html', $html );
}

// Segunda pasada: con la lista de lo que existe, los enlaces.
$navegacion = '<script>'
	. '(function(){'
	. 'var hay=' . json_encode( $hechas ) . ';'
	. 'document.addEventListener("click",function(e){'
	. 'var a=e.target.closest("a[href],button[type=submit]");if(!a)return;'
	. 'e.preventDefault();'
	. 'var h=a.getAttribute("href")||"";'
	. 'var s=h.replace(' . json_encode( $base ) . ',"").replace(/[\/?#].*$/,"")||"home";'
	. 'if(hay.indexOf(s)!==-1)location.href=s+".html";'
	. '},true);'
	. 'document.addEventListener("submit",function(e){e.preventDefault();},true);'
	// Con el foco adentro del panel, las teclas de la vitrina (1/2/3, flechas
	// del control de diapositivas) igual tienen que llegar afuera.
	. 'document.addEventListener("keydown",function(e){'
	. 'if(/^[1-3]$|^Arrow(Left|Right)$|^Page(Up|Down)$/.test(e.key)&&parent!==window)parent.document.dispatchEvent(new KeyboardEvent("keydown",{key:e.key}));'
	. '});'
	. '})();'
	. '</script>';

foreach ( $hechas as $s ) {
	$ruta = $destino . $s . '.html';
	$html = file_get_contents( $ruta );
	// La persona de la maqueta: genérica, para que nadie la lea como real.
	$html = str_replace( array( 'Achmad Hakim', '>AH<' ), array( 'Docente', '>D<' ), $html );
	// Sin nombres ni íconos de la instalación real.
	$html = preg_replace( '#<link rel="(manifest|icon|apple-touch-icon)"[^>]*>#', '', $html );
	// Las fuentes del panel, desde las de la web (son las mismas Inter).
	$html = preg_replace( '#url\("?[^")]*caaguazu-portal/assets/fonts/(inter-[0-9]+\.woff2)"?\)#', 'url("../../fuentes/$1")', $html );
	$html = str_replace( '</body>', $navegacion . '</body>', $html );
	file_put_contents( $ruta, $html );
}

fwrite( STDERR, 'Pantallas: ' . implode( ', ', $hechas ) . "\n" );
