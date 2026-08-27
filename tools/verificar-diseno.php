<?php
/**
 * Comprobaciones del sistema de diseño del panel.
 *
 *   php tools/verificar-diseno.php          # informe completo
 *   php tools/verificar-diseno.php --breve  # sólo lo que falla
 *
 * Sale con código 1 si algo falla, para poder colgarlo de CI o de un hook.
 *
 * Por qué existe: una convención escrita en un README dura hasta el primer día
 * de apuro. Cada regla del encabezado de `caaguazu-portal.css` está acá abajo
 * como una comprobación que la contradice si alguien la rompe.
 *
 * Cada comprobación está escrita en las dos direcciones: detecta el caso malo
 * y NO marca el caso bueno. Una comprobación que siempre pasa da confianza sin
 * darla.
 */

// phpcs:disable

$raiz    = dirname( __DIR__ );
$plugin  = $raiz . '/caaguazu-portal';
$css     = $plugin . '/assets/css/caaguazu-portal.css';
$breve   = in_array( '--breve', $argv, true );
$limite  = in_array( '--todo', $argv, true ) ? 500 : 12;
$fallas  = 0;
$avisos  = 0;

/* --------------------------------------------------------------------------
 * Utilidades
 * ------------------------------------------------------------------------ */

function czu_archivos( $dir, $ext ) {
	$out = array();
	if ( ! is_dir( $dir ) ) { return $out; }
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( $f->isFile() && strtolower( $f->getExtension() ) === $ext && false === strpos( $f->getPathname(), '/vendor/' ) ) {
			$out[] = $f->getPathname();
		}
	}
	sort( $out );
	return $out;
}

function czu_rel( $ruta ) {
	return ltrim( str_replace( dirname( __DIR__ ), '', $ruta ), '/' );
}

$resultados = array();
function czu_check( $titulo, $malos, $nota = '' ) {
	global $resultados, $fallas;
	$resultados[] = array( 'titulo' => $titulo, 'malos' => $malos, 'nota' => $nota, 'tipo' => 'falla' );
	if ( $malos ) { $fallas += count( $malos ); }
}
function czu_info( $titulo, $items, $nota = '' ) {
	global $resultados, $avisos;
	$resultados[] = array( 'titulo' => $titulo, 'malos' => $items, 'nota' => $nota, 'tipo' => 'aviso' );
	if ( $items ) { $avisos += count( $items ); }
}

$fuente_css = file_get_contents( $css );

// El CSS sin los bloques de tokens: ahí adentro los colores literales son
// justamente lo que se declara, así que no cuentan.
$css_sin_tokens = preg_replace( '/:root\s*\{.*?\}/s', '', $fuente_css );
$css_sin_tokens = preg_replace( '/\[data-theme="dark"\]\s*\{.*?\}/s', '', $css_sin_tokens );

/* --------------------------------------------------------------------------
 * 1. Color: conjunto cerrado
 * ------------------------------------------------------------------------ */

preg_match_all( '/#[0-9a-fA-F]{3,8}\b|rgba?\([^)]*\)|hsla?\([^)]*\)|oklch\([^)]*\)/', $css_sin_tokens, $m );
czu_check(
	'Ningún color literal fuera del bloque de tokens',
	array_values( array_unique( $m[0] ) ),
	'Los colores viven en :root y en su gemelo de modo oscuro. Un #hex suelto en un componente es un token fuera del sistema que después nadie encuentra para cambiar.'
);

// Los tokens se nombran por rol, no por color.
preg_match_all( '/--([a-z0-9-]+):/', $fuente_css, $tk );
$por_color = array();
foreach ( array_unique( $tk[1] ) as $token ) {
	if ( preg_match( '/^(rojo|verde|azul|gris|negro|blanco|amarillo|naranja|violeta)/', $token ) ) {
		$por_color[] = '--' . $token;
	}
}
czu_check( 'Los tokens se nombran por rol, no por color', $por_color,
	'`--acento` sobrevive a que el acento pase a ser verde; `--verde` no.' );

/* --------------------------------------------------------------------------
 * 2. Forma: tres radios y una sombra
 * ------------------------------------------------------------------------ */

preg_match_all( '/border-radius:\s*([^;]+);/', $fuente_css, $r );
$radios_malos = array();
foreach ( array_unique( $r[1] ) as $valor ) {
	$limpio = trim( $valor );
	// Se permite cualquier combinación de los tokens de radio (una esquina
	// distinta por lado es legítimo: la cabecera de tarjeta redondea arriba).
	$sin_tokens = trim( preg_replace( '/var\(--r-(1|2|3|full)\)|\b0\b/', '', $limpio ) );
	if ( '' !== $sin_tokens ) {
		$radios_malos[] = $limpio;
	}
}
czu_check( 'Ningún radio fuera de --r-1 / --r-2 / --r-3 / --r-full', $radios_malos,
	'Dos o tres radios nombrados, no un número escrito a mano en cada componente.' );

preg_match_all( '/box-shadow:\s*([^;]+);/', $fuente_css, $sh );
$sombras_malas = array();
foreach ( array_unique( $sh[1] ) as $valor ) {
	$limpio = trim( $valor );
	if ( ! in_array( $limpio, array( 'var(--sombra)', 'var(--sombra-flotante)', 'none' ), true ) ) {
		$sombras_malas[] = $limpio;
	}
}
czu_check( 'Ninguna sombra fuera de --sombra / --sombra-flotante', $sombras_malas,
	'Dos sombras, cada una con su lugar: contacto y flotación. Un sistema donde cada tarjeta flota un poco no tiene jerarquía.' );

/* --------------------------------------------------------------------------
 * 3. Una familia tipográfica, sin gradientes, sin pedidos a terceros
 * ------------------------------------------------------------------------ */

// El bloque @font-face nombra la familia por definición: ahí no cuenta.
$css_sin_fontface = preg_replace( '/@font-face\s*\{.*?\}/s', '', $fuente_css );
preg_match_all( '/font-family:\s*([^;]+);/', $css_sin_fontface, $ff );
$familias = array();
foreach ( array_unique( $ff[1] ) as $valor ) {
	$limpio = trim( $valor );
	if ( 'var(--letra)' !== $limpio && 'inherit' !== $limpio ) {
		$familias[] = $limpio;
	}
}
czu_check( 'Una sola familia tipográfica (--letra)', $familias );

preg_match_all( '/(linear-gradient|radial-gradient|conic-gradient)\([^;]*/', $css_sin_tokens, $g );
$degrades = array();
foreach ( $g[0] as $valor ) {
	// La trama de rayas es el único motivo decorativo declarado, y vive en un
	// token: cualquier otro gradiente es decoración suelta.
	if ( false === strpos( $valor, 'repeating-' ) ) {
		$degrades[] = substr( trim( $valor ), 0, 60 );
	}
}
czu_check( 'Sin gradientes decorativos', array_values( array_unique( $degrades ) ) );

// Sólo cuentan las cargas reales (src/href/url()/fetch/enqueue), no una URL
// mencionada en un comentario: una librería vendoreada que cita su sitio en la
// licencia no le pide nada a nadie en tiempo de ejecución.
$busca_terceros = function ( array $archivos ) {
	$out = array();
	foreach ( $archivos as $f ) {
		$contenido = file_get_contents( $f );
		preg_match_all( '#(?:src=|href=|url\(|fetch\(|wp_enqueue_(?:script|style)\([^;]*?)[\'"]?(https?://[a-z0-9.-]+[^\'"\s)]*)#i', $contenido, $u );
		foreach ( array_unique( $u[1] ) as $url ) {
			if ( preg_match( '#(github\.com|caaguazu\.net|w3\.org|gnu\.org|wordpress\.org|schema\.org)#', $url ) ) { continue; }
			$out[] = czu_rel( $f ) . ' → ' . $url;
		}
	}
	return array_values( array_unique( $out ) );
};

// Superficie del panel: lo que se dibuja detrás del login.
$archivos_panel = array_merge(
	czu_archivos( $plugin . '/assets/css', 'css' ),
	array( $plugin . '/assets/js/caaguazu-portal.js' ),
	czu_archivos( $plugin . '/templates/partials', 'php' ),
	czu_archivos( $plugin . '/templates/sections', 'php' ),
	czu_archivos( $plugin . '/templates/auth', 'php' ),
	array( $plugin . '/templates/shell.php', $plugin . '/templates/auth-shell.php', $plugin . '/includes/class-assets.php' )
);
czu_check( 'Sin assets de terceros en el panel', $busca_terceros( $archivos_panel ),
	'Cada servicio externo es algo que puede cortarse o cambiar de precio. La tipografía se sirve desde el plugin.' );

/* --------------------------------------------------------------------------
 * 4. Clases: ninguna que se use y no exista, ninguna declarada que nadie use
 * ------------------------------------------------------------------------ */

preg_match_all( '/\.(promotur-[a-z0-9_-]+)/', $fuente_css, $cd );
$declaradas = array_values( array_unique( $cd[1] ) );

// Las usadas salen de los atributos class= y de las asignaciones de className/
// classList del JS — no de cualquier cadena que empiece con "promotur-", que
// también son claves de localStorage e ids.
$usadas    = array();
$comodines = array();
$grupos    = array();
foreach ( array_merge( czu_archivos( $plugin . '/templates', 'php' ), czu_archivos( $plugin . '/includes', 'php' ), array( $plugin . '/assets/js/caaguazu-portal.js' ) ) as $f ) {
	/* Dos lecturas del mismo archivo, y la unión de las dos:

	   1. sin el PHP → captura los atributos class= literales, incluso cuando
	      intercalan una etiqueta PHP en el medio (que si no, corta la comilla);
	   2. sólo sin las etiquetas de apertura y cierre → captura las clases que
	      se imprimen desde adentro de un printf().

	   Después se filtra a lo que tiene forma de clase, así la basura que deja
	   la lectura 2 al cortar en una comilla del PHP no cuenta. */
	$crudo    = file_get_contents( $f );
	$lecturas = array(
		preg_replace( '/<\?php.*?\?' . '>/s', ' ', $crudo ),
		str_replace( array( '<?php', '?' . '>' ), ' ', $crudo ),
	);

	$trozos = array();
	foreach ( $lecturas as $contenido ) {
		if ( preg_match_all( '/class=["\']([^"\']*)["\']/', $contenido, $ca ) ) { $trozos = array_merge( $trozos, $ca[1] ); }
		if ( preg_match_all( '/className\s*=\s*["\']([^"\']*)["\']/', $contenido, $cn ) ) { $trozos = array_merge( $trozos, $cn[1] ); }
		if ( preg_match_all( '/classList\.(?:add|toggle|remove)\(\s*["\']([^"\']*)["\']/', $contenido, $cl ) ) { $trozos = array_merge( $trozos, $cl[1] ); }
	}

	foreach ( $trozos as $trozo ) {
		$grupo = array();
		foreach ( preg_split( '/\s+/', $trozo ) as $clase ) {
			if ( 0 !== strpos( $clase, 'promotur-' ) ) { continue; }
			// printf() y los modificadores dinámicos arman variantes:
			// "promotur-flash--%s", "promotur-field--<tipo>". Se guarda el
			// prefijo, que es lo único verificable.
			if ( false !== strpos( $clase, '%' ) ) {
				$comodines[] = substr( $clase, 0, strpos( $clase, '%' ) );
				continue;
			}
			if ( '-' === substr( $clase, -1 ) ) {
				$comodines[] = $clase;
				continue;
			}
			if ( ! preg_match( '/^promotur-[a-z0-9_-]+$/', $clase ) ) { continue; }
			$usadas[ $clase ] = true;
			$grupo[]          = $clase;
		}
		if ( $grupo ) {
			$grupos[ implode( ' ', $grupo ) ] = czu_rel( $f );
		}
	}
}
$usadas    = array_keys( $usadas );
$comodines = array_values( array_unique( $comodines ) );

/* Falla sólo el elemento que queda SIN NINGÚN estilo. Una clase suelta junto a
   otra que sí existe —`class="promotur-card promotur-invite"`— es un gancho
   semántico o de JS, no una pantalla sin dibujar: exigirle una regla propia
   sería inventar CSS para contentar al verificador. */
$huerfanos = array();
foreach ( $grupos as $grupo => $archivo ) {
	$tiene_estilo = false;
	foreach ( explode( ' ', $grupo ) as $clase ) {
		if ( in_array( $clase, $declaradas, true ) ) { $tiene_estilo = true; break; }
	}
	if ( ! $tiene_estilo ) {
		$huerfanos[] = $grupo . '  ' . $archivo;
	}
}
czu_check( 'Ningún elemento sin una sola clase con estilo', $huerfanos,
	'Una clase que una plantilla pide y el CSS no tiene es una pantalla sin estilo esperando su turno.' );

$todo_el_codigo = '';
foreach ( array_merge( czu_archivos( $plugin . '/templates', 'php' ), czu_archivos( $plugin . '/includes', 'php' ), czu_archivos( $plugin . '/assets/js', 'js' ) ) as $f ) {
	$todo_el_codigo .= file_get_contents( $f );
}

$sin_usar = array();
foreach ( $declaradas as $clase ) {
	if ( in_array( $clase, $usadas, true ) ) { continue; }
	// Hay clases que viajan como argumento —promotur_avatar( $x,
	// 'promotur-avatar--sm' ), body_class( 'promotur-auth-page' )— y no como
	// atributo. Para este aviso alcanza con que el nombre aparezca citado.
	if ( preg_match( '/[\'" ]' . preg_quote( $clase, '/' ) . '[\'" ]/', $todo_el_codigo ) ) { continue; }
	$cubierta = false;
	foreach ( $comodines as $prefijo ) {
		if ( '' !== $prefijo && 0 === strpos( $clase, $prefijo ) ) { $cubierta = true; break; }
	}
	if ( ! $cubierta ) { $sin_usar[] = $clase; }
}
czu_info( 'Clases declaradas que nadie usa', $sin_usar,
	'Sobra CSS: o falta usarlas, o hay que borrarlas.' );

/* --------------------------------------------------------------------------
 * 5. Texto e interacción
 * ------------------------------------------------------------------------ */

$faltas = array();
foreach ( array_merge( czu_archivos( $plugin . '/templates', 'php' ), czu_archivos( $plugin . '/includes', 'php' ) ) as $f ) {
	foreach ( file( $f ) as $i => $linea ) {
		if ( false !== strpos( $linea, '[FALTA:' ) && false === strpos( $linea, 'strpos' ) ) {
			$faltas[] = czu_rel( $f ) . ':' . ( $i + 1 ) . ' ' . trim( preg_replace( '/\s+/', ' ', $linea ) );
		}
	}
}
czu_info( 'Textos marcados como pendientes de escribir', $faltas,
	'Los escribe una persona. Un hueco visible se arregla; un párrafo plausible de relleno se publica.' );

$alertas = array();
foreach ( czu_archivos( $plugin . '/assets/js', 'js' ) as $f ) {
	foreach ( file( $f ) as $i => $linea ) {
		if ( preg_match( '/(?<![a-zA-Z.])alert\s*\(/', $linea ) && false === strpos( $linea, '*' ) ) {
			$alertas[] = czu_rel( $f ) . ':' . ( $i + 1 );
		}
	}
}
czu_check( 'Sin alert() del navegador', $alertas,
	'Un cartel del navegador tapa la pantalla, no dice dónde falló y en un teléfono se cierra sin querer.' );

/* --------------------------------------------------------------------------
 * 6. Rutas: una sola forma de armar una URL del panel
 * ------------------------------------------------------------------------ */

$urls = array();
foreach ( array_merge( czu_archivos( $plugin . '/templates', 'php' ), czu_archivos( $plugin . '/includes', 'php' ) ) as $f ) {
	if ( basename( $f ) === 'helpers.php' || basename( $f ) === 'class-router.php' ) { continue; }
	foreach ( file( $f ) as $i => $linea ) {
		if ( preg_match( '#home_url\(\s*[\'"]/(turismo-panel|czu-login|registro|recuperar|salir)#', $linea ) ) {
			$urls[] = czu_rel( $f ) . ':' . ( $i + 1 );
		}
	}
}
czu_check( 'Las URLs del panel se arman con promotur_url()', $urls,
	'Mover el panel de lugar tiene que ser cambiar PROMOTUR_BASE, no rastrear home_url() por el código.' );

/* --------------------------------------------------------------------------
 * Informe
 * ------------------------------------------------------------------------ */

$verde = "\033[32m"; $rojo = "\033[31m"; $ambar = "\033[33m"; $gris = "\033[90m"; $fin = "\033[0m";

echo "\n" . $gris . "Sistema de diseño del panel — " . czu_rel( $css ) . $fin . "\n\n";

foreach ( $resultados as $r ) {
	$ok    = empty( $r['malos'] );
	$aviso = 'aviso' === $r['tipo'];
	if ( $ok && $breve ) { continue; }

	$marca = $ok ? $verde . '✓' . $fin : ( $aviso ? $ambar . '!' . $fin : $rojo . '✗' . $fin );
	echo " $marca  {$r['titulo']}";
	echo $ok ? "\n" : $gris . ' (' . count( $r['malos'] ) . ")\n" . $fin;

	if ( ! $ok ) {
		foreach ( array_slice( $r['malos'], 0, $limite ) as $malo ) {
			echo "      · $malo\n";
		}
		if ( count( $r['malos'] ) > $limite ) {
			echo $gris . '      · … y ' . ( count( $r['malos'] ) - $limite ) . " más\n" . $fin;
		}
		if ( $r['nota'] ) {
			echo $gris . '      ' . $r['nota'] . "\n" . $fin;
		}
	}
}

echo "\n";
if ( $fallas ) {
	echo $rojo . "  $fallas cosa(s) rompen una regla del sistema." . $fin . "\n\n";
	exit( 1 );
}
echo $verde . '  Todo en regla.' . $fin . ( $avisos ? $gris . "  ($avisos aviso/s)" . $fin : '' ) . "\n\n";
exit( 0 );
