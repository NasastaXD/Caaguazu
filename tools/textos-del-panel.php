<?php
/**
 * Inventario de todos los textos que se ven en el panel.
 *
 *   php tools/textos-del-panel.php > docs/textos-del-panel.md
 *
 * Saca de las fuentes cada cadena que pasa por la capa de traducción —`__()`,
 * `esc_html_e()`, `_n()` y compañía— y arma una tabla por pantalla, con dónde
 * vive cada una y una columna vacía para escribir la nueva.
 *
 * Existe porque hoy el texto vive en el código: cambiar una palabra exige
 * publicar el plugin. Mientras eso siga así, esto es lo que hace la lista
 * revisable de una sentada en vez de rastrear 300 llamadas a mano. El día que
 * los textos se muevan a una fuente editable, este script es el que arma el
 * primer volcado de claves.
 */

// phpcs:disable

$raiz   = dirname( __DIR__ );
$plugin = $raiz . '/caaguazu-portal';

/** Archivo → { pantalla, grupo }. El orden manda en la salida. */
$mapa = array(
	// --- El armazón -------------------------------------------------------
	'includes/helpers.php'                  => array( 'Menú lateral (rótulos y secciones)', 'armazon' ),
	'templates/partials/sidebar.php'        => array( 'Menú lateral (pie)', 'armazon' ),
	'templates/partials/topbar.php'         => array( 'Barra superior', 'armazon' ),
	'templates/partials/bottomnav.php'      => array( 'Barra inferior (teléfono)', 'armazon' ),
	'includes/class-assets.php'             => array( 'Mensajes del JavaScript', 'armazon' ),
	'includes/class-notifications.php'      => array( 'Notificaciones', 'armazon' ),
	'includes/class-editorial.php'          => array( 'Estados del flujo editorial', 'armazon' ),
	'includes/class-roles.php'              => array( 'Nombres de los roles', 'armazon' ),

	// --- Las secciones ----------------------------------------------------
	'templates/sections/home.php'           => array( 'Inicio', 'secciones' ),
	'templates/sections/mis-contenidos.php' => array( 'Mis contenidos', 'secciones' ),
	'templates/sections/editor.php'         => array( 'Editor de ficha', 'secciones' ),
	'includes/class-destinos.php'           => array( 'Campos de la ficha', 'secciones' ),
	'templates/sections/captura.php'        => array( 'Salida de campo', 'secciones' ),
	'templates/sections/revision.php'       => array( 'Cola de revisión', 'secciones' ),
	'templates/sections/tareas.php'         => array( 'Tareas', 'secciones' ),
	'includes/class-tareas.php'             => array( 'Tareas (estados y avisos)', 'secciones' ),
	'templates/sections/equipo.php'         => array( 'Equipo', 'secciones' ),
	'templates/sections/reportes.php'       => array( 'Reportes', 'secciones' ),
	'templates/sections/biblioteca.php'     => array( 'Biblioteca', 'secciones' ),
	'templates/sections/estructura.php'     => array( 'Estructura', 'secciones' ),
	'templates/sections/buscar.php'         => array( 'Buscar', 'secciones' ),
	'templates/sections/perfil.php'         => array( 'Mi perfil', 'secciones' ),
	'includes/class-stats.php'              => array( 'Niveles de confianza', 'secciones' ),
	'templates/sections/app.php'            => array( 'App (control de la app móvil)', 'secciones' ),
	'includes/class-app-control.php'        => array( 'App (avisos)', 'secciones' ),
	'templates/sections/ayuda.php'          => array( 'Ayuda', 'secciones' ),
	'templates/sections/404.php'            => array( 'Sección inexistente', 'secciones' ),

	// --- Entrar y salir ---------------------------------------------------
	'templates/auth/login.php'              => array( 'Iniciar sesión', 'acceso' ),
	'templates/auth/registro.php'           => array( 'Registro', 'acceso' ),
	'templates/auth/recuperar.php'          => array( 'Recuperar contraseña', 'acceso' ),
	'templates/auth/restablecer.php'        => array( 'Contraseña nueva', 'acceso' ),
	'templates/auth-shell.php'              => array( 'Marco de acceso', 'acceso' ),
	'includes/class-auth.php'               => array( 'Errores y avisos de acceso', 'acceso' ),
	'includes/class-invitations.php'        => array( 'Invitaciones', 'acceso' ),
	'includes/class-router.php'             => array( 'Guardas de acceso', 'acceso' ),
	'includes/class-shell.php'              => array( 'Guardas de sección', 'acceso' ),
	'includes/class-pwa.php'                => array( 'Sin conexión (PWA)', 'acceso' ),

	// --- wp-admin ---------------------------------------------------------
	'includes/class-admin.php'              => array( 'Pantallas de wp-admin', 'admin' ),
	'includes/class-ajax.php'               => array( 'Respuestas del editor', 'admin' ),
	'includes/class-gestion-ajax.php'       => array( 'Respuestas de gestión', 'admin' ),
	'includes/class-audit.php'              => array( 'Auditoría', 'admin' ),
	'includes/class-install.php'            => array( 'Instalación', 'admin' ),
	'includes/class-i18n.php'               => array( 'Selector de idioma', 'admin' ),
	'caaguazu-portal.php'                   => array( 'Avisos del plugin', 'admin' ),
);

$grupos = array(
	'armazon'   => array( 'El armazón', 'Lo que se ve en todas las pantallas.' ),
	'secciones' => array( 'Las secciones', 'Una tabla por pantalla del panel.' ),
	'acceso'    => array( 'Entrar y salir', 'Acceso, registro, recuperación, invitaciones y errores de permiso.' ),
	'admin'     => array( 'wp-admin y mensajes de sistema', 'Pantallas de administración y respuestas de las acciones.' ),
);

/**
 * Saca las cadenas traducibles de un archivo, en orden de aparición y sin
 * repetir. Cada una con su línea.
 */
function czu_textos( $ruta ) {
	if ( ! file_exists( $ruta ) ) {
		return array();
	}
	$lineas = file( $ruta );
	$out    = array();
	$vistos = array();

	// __( 'x', 'dom' ) · esc_html__ · esc_attr__ · esc_html_e · esc_attr_e ·
	// _n( 'uno', 'varios', … ) · _x( 'x', 'contexto', … )
	$patron = '/\b(?:esc_html__|esc_attr__|esc_html_e|esc_attr_e|_n|_x|__)\(\s*((?:\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*")(?:\s*,\s*(?:\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"))?)/';

	foreach ( $lineas as $i => $linea ) {
		// Los huecos a propósito no pasan por la capa de traducción —no son
		// texto terminado— pero son justamente los que hay que escribir.
		if ( preg_match_all( '/\[FALTA:[^\]]*\]/', $linea, $f ) && false === strpos( $linea, '*' ) ) {
			foreach ( $f[0] as $falta ) {
				if ( isset( $vistos[ $falta ] ) ) { continue; }
				$vistos[ $falta ] = true;
				$out[]            = array( 'texto' => $falta, 'linea' => $i + 1 );
			}
		}
		if ( ! preg_match_all( $patron, $linea, $m ) ) {
			continue;
		}
		foreach ( $m[1] as $crudo ) {
			// Puede venir "singular', 'plural" (de _n): se parten las dos.
			preg_match_all( '/\'((?:\\\\.|[^\'\\\\])*)\'|"((?:\\\\.|[^"\\\\])*)"/', $crudo, $partes, PREG_SET_ORDER );
			$textos = array();
			foreach ( $partes as $parte ) {
				$texto = isset( $parte[2] ) && '' !== $parte[2] ? $parte[2] : $parte[1];
				// El segundo argumento suele ser el text domain: no es texto.
				if ( 'caaguazu-portal' === $texto ) { continue; }
				$textos[] = stripslashes( $texto );
			}
			foreach ( $textos as $texto ) {
				if ( '' === trim( $texto ) || isset( $vistos[ $texto ] ) ) { continue; }
				$vistos[ $texto ] = true;
				$out[]            = array( 'texto' => $texto, 'linea' => $i + 1 );
			}
		}
	}
	return $out;
}

/* --------------------------------------------------------------------------
 * Salida
 * ------------------------------------------------------------------------ */

$hoy   = gmdate( 'Y-m-d' );
$total      = 0;
$n          = 0;
$cuerpo     = '';
$minusculas = array();
$faltantes  = array();

foreach ( $grupos as $clave => $grupo ) {
	$tablas = '';

	foreach ( $mapa as $archivo => $meta ) {
		if ( $meta[1] !== $clave ) { continue; }
		$textos = czu_textos( $plugin . '/' . $archivo );
		if ( ! $textos ) { continue; }

		$tablas .= "\n### " . $meta[0] . "\n\n";
		$tablas .= '`' . $archivo . "`\n\n";
		$tablas .= "| # | Texto actual | Línea | Nuevo texto |\n| --- | --- | --- | --- |\n";
		foreach ( $textos as $t ) {
			$n++;
			$total++;
			$texto = str_replace( array( '|', "\n" ), array( '\\|', ' ' ), $t['texto'] );
			$marcas = '';
			if ( preg_match( '/%[0-9]*\$?[sd]/', $texto ) ) { $marcas .= ' ⚠️'; }
			// Arranca en minúscula: son fragmentos pensados para leerse después
			// de un número ("4 esperan revisión"). Como título quedan raros.
			$primera = mb_substr( ltrim( $texto, '¿¡"“+' ), 0, 1 );
			if ( '' !== $primera && mb_strtolower( $primera ) === $primera && preg_match( '/\p{L}/u', $primera ) ) {
				$marcas .= ' 🔡';
				$minusculas[] = array( 'n' => $n, 'texto' => $texto, 'pantalla' => $meta[0] );
			}
			if ( false !== strpos( $texto, '[FALTA:' ) ) {
				$faltantes[] = array( 'n' => $n, 'texto' => $texto, 'pantalla' => $meta[0] );
			}
			$tablas .= sprintf( "| %d | `%s`%s | %d | |\n", $n, $texto, $marcas, $t['linea'] );
		}
	}

	if ( $tablas ) {
		$cuerpo .= "\n## " . $grupo[0] . "\n\n" . $grupo[1] . "\n" . $tablas;
	}
}

echo "# Textos del panel\n\n";
echo "Todo lo que un usuario lee en el panel, sacado de las fuentes el {$hoy}. **{$total} textos.**\n\n";
echo "Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.\n\n";
echo "- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.\n";
echo "- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1\$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.\n";
echo "- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.\n";
echo "- Los marcados con 🔡 arrancan en minúscula.\n";

if ( $faltantes ) {
	echo "\n## Sin escribir\n\nLo único que el panel muestra hoy y que nadie escribió todavía.\n\n";
	echo "| # | Hueco | Dónde |\n| --- | --- | --- |\n";
	foreach ( $faltantes as $f ) {
		printf( "| %d | `%s` | %s |\n", $f['n'], $f['texto'], $f['pantalla'] );
	}
}

if ( $minusculas ) {
	echo "\n## Empiezan en minúscula\n\n";
	echo "Casi todas son fragmentos escritos para leerse **después de un número** (\"4 esperan revisión\") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.\n\n";
	echo "| # | Texto | Dónde |\n| --- | --- | --- |\n";
	foreach ( $minusculas as $m ) {
		printf( "| %d | `%s` | %s |\n", $m['n'], $m['texto'], $m['pantalla'] );
	}
}

echo $cuerpo;
