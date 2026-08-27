<?php
/**
 * Helpers del portal.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Nombre para mostrar de una cuenta (autor/revisor de una ficha, etc.).
 * Acepta un ID de cuenta del sistema de cuentas universal.
 *
 * @param int    $account_id
 * @param string $fallback texto si no se puede resolver (cuenta borrada, ID 0, etc.)
 * @return string
 */
function promotur_account_display_name( $account_id, $fallback = '—' ) {
	$account_id = (int) $account_id;
	if ( $account_id <= 0 || ! class_exists( 'Caaguazu_Cuentas_Accounts' ) ) {
		return $fallback;
	}
	$account = Caaguazu_Cuentas_Accounts::get( $account_id );
	if ( ! $account ) { return $fallback; }
	return $account['display_name'] ? $account['display_name'] : $account['email'];
}

/**
 * Miembros del equipo del panel "promotor": cuentas con grant activo,
 * opcionalmente filtradas por rol. Reemplaza a `get_users( array( 'role' =>
 * ... ) )` en las pantallas del panel (Equipo, Moderación, Reportes, Tareas)
 * ahora que los promotores no son usuarios de WordPress — el "quién puede
 * asignarse esto" vive en `caaguazu_grants`, no en `wp_users`.
 *
 * @param string|string[]|null $roles rol o roles a filtrar (null = todos los roles del panel)
 * @param bool                 $incluir_suspendidas sumar las cuentas suspendidas
 * @return array[] { id (int, ID de cuenta), email, display_name, status, role }
 */
function promotur_team_members( $roles = null, $incluir_suspendidas = false ) {
	if ( ! class_exists( 'Caaguazu_Cuentas_Install' ) ) { return array(); }
	global $wpdb;
	$t     = Caaguazu_Cuentas_Install::tables();
	$roles = $roles ? (array) $roles : null;

	$sql    = "SELECT a.id, a.email, a.display_name, a.status, g.role
		FROM {$t['grants']} g
		INNER JOIN {$t['accounts']} a ON a.id = g.account_id
		WHERE g.panel = %s AND g.status = 'active'";
	// Las suspendidas se piden a propósito: si no aparecen, nadie las puede
	// reactivar desde el panel.
	if ( ! $incluir_suspendidas ) {
		$sql .= " AND a.status = 'active'";
	}
	$params = array( 'promotor' );
	if ( $roles ) {
		$placeholders = implode( ',', array_fill( 0, count( $roles ), '%s' ) );
		$sql         .= " AND g.role IN ($placeholders)";
		$params       = array_merge( $params, $roles );
	}
	$sql .= ' ORDER BY a.display_name ASC';

	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB
	if ( ! $rows ) { return array(); }
	foreach ( $rows as &$r ) {
		$r['id']           = (int) $r['id'];
		$r['display_name'] = $r['display_name'] ? $r['display_name'] : $r['email'];
	}
	return $rows;
}

/**
 * URL absoluta a una ruta del panel.
 *
 * Todo cuelga de PROMOTUR_BASE (/turismo-panel), así que esta función es el
 * único lugar del plugin que sabe armar una URL del panel — mover el panel de
 * lugar es cambiar la constante, no rastrear home_url() por el código.
 *
 * Los identificadores internos de las rutas de auth ('login', 'restablecer')
 * se conservan —los usan el query var, el switch del dispatch y los
 * templates— y sólo se traducen acá a su slug público.
 *
 * @param string $route ej. 'panel/equipo', 'panel', 'login', 'salir'
 * @return string
 */
function promotur_url( $route = '' ) {
	$route = trim( (string) $route, '/' );

	$slugs = array(
		'panel'       => '',
		''            => '',
		'login'       => 'entrar',
		'restablecer' => 'recuperar/nueva',
	);
	if ( isset( $slugs[ $route ] ) ) {
		$route = $slugs[ $route ];
	} elseif ( 0 === strpos( $route, 'panel/' ) ) {
		$route = substr( $route, strlen( 'panel/' ) );
	}

	return home_url( '/' . PROMOTUR_BASE . ( '' === $route ? '' : '/' . $route ) );
}

/**
 * URL a un asset del plugin.
 *
 * @param string $path ej. 'icons/icon-192.png'
 * @return string
 */
function promotur_asset( $path ) {
	return PROMOTUR_URI . 'assets/' . ltrim( $path, '/' );
}

/**
 * Carga un template del plugin permitiendo override desde el theme activo
 * en /<theme>/promotur/<ruta>.php. Espeja el patrón de caaguazu-locales.
 *
 * @param string $route ruta sin extensión, ej. 'sections/home'
 * @param array  $vars  variables disponibles dentro del template
 */
function promotur_template( $route, $vars = array() ) {
	$rel      = ltrim( $route, '/' ) . '.php';
	$override = locate_template( array( 'promotur/' . $rel ) );
	$file     = $override ? $override : PROMOTUR_DIR . 'templates/' . $rel;

	if ( ! file_exists( $file ) ) {
		$file = PROMOTUR_DIR . 'templates/sections/404.php';
	}
	if ( ! empty( $vars ) ) {
		extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
	}
	include $file;
}

/**
 * Rol de la cuenta actual en el panel, o ''.
 *
 * Antes esto aceptaba un ID de usuario de WordPress, para la pantalla de
 * wp-admin que listaba usuarios de WordPress con roles `promotur_*`. Esa
 * pantalla ya no existe —el equipo se administra en el panel, sobre cuentas—,
 * así que la rama legada se fue con ella. El rol sale del permiso de la cuenta
 * sobre el panel y de ningún otro lado.
 *
 * @return string
 */
function promotur_user_role() {
	if ( function_exists( 'caaguazu_account_id' ) && caaguazu_account_id() > 0 ) {
		$grant = Caaguazu_Cuentas_Panels::instance()->get_grant( caaguazu_account_id(), 'promotor' );
		return ( $grant && 'active' === $grant['status'] ) ? (string) $grant['role'] : '';
	}
	// Sin cuenta propia: un administrador del sitio entrando por el bypass.
	return current_user_can( 'manage_options' ) ? 'promotur_promotor' : '';
}

/**
 * Etiqueta legible del rol del portal de la cuenta actual.
 *
 * @return string
 */
function promotur_role_label() {
	$role = promotur_user_role();
	// El rol sólo puede salir de "promotur_promotor" sin una cuenta propia
	// (caaguazu_account_id() <= 0) por el bypass de administrador de WP.
	if ( 'promotur_promotor' === $role && function_exists( 'caaguazu_account_id' ) && caaguazu_account_id() <= 0 ) {
		return __( 'Administrador', 'caaguazu-portal' );
	}
	return $role ? PROMOTUR_Roles::label( $role ) : __( 'Invitado', 'caaguazu-portal' );
}

/**
 * Ruta/sección actualmente renderizada por el shell (para estado activo del menú).
 *
 * @return string
 */
function promotur_current_route() {
	return isset( $GLOBALS['promotur_section'] ) ? (string) $GLOBALS['promotur_section'] : '';
}

/**
 * Wrapper legible del chequeo de capability del panel "promotor" (cuenta
 * actual, con bypass de administrador de WP incluido — ver
 * caaguazu_account_can()).
 *
 * @param string $cap
 * @return bool
 */
function promotur_can( $cap ) {
	return function_exists( 'caaguazu_account_can' ) ? caaguazu_account_can( 'promotor', $cap ) : current_user_can( $cap );
}

/**
 * Identidad normalizada de "quién es" para la topbar/perfil/equipo: cuenta
 * propia si hay una logueada, o los datos del administrador de WP si entró
 * por el bypass. Evita que cada template tenga que resolver esa rama a mano.
 *
 * @return array{id:int,display_name:string,email:string,phone:string,is_admin_bypass:bool}
 */
function promotur_current_identity() {
	$account_id = function_exists( 'caaguazu_account_id' ) ? caaguazu_account_id() : 0;
	if ( $account_id > 0 ) {
		$account = caaguazu_current_account();
		return array(
			'id'              => $account_id,
			'display_name'    => $account['display_name'] ? $account['display_name'] : $account['email'],
			'email'           => $account['email'],
			'phone'           => (string) $account['phone'],
			'is_admin_bypass' => false,
		);
	}
	$wp_user = wp_get_current_user();
	return array(
		'id'              => 0,
		'display_name'    => $wp_user->display_name ? $wp_user->display_name : $wp_user->user_login,
		'email'           => (string) $wp_user->user_email,
		'phone'           => '',
		'is_admin_bypass' => true,
	);
}

/**
 * Pequeño set de íconos SVG inline usados por el menú.
 *
 * @param string $name
 * @return string SVG markup
 */
function promotur_icon( $name ) {
	$paths = array(
		'home'    => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
		'doc'     => '<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4"/>',
		'edit'    => '<path d="M4 20h16"/><path d="M14 4l6 6L8 22H2v-6z"/>',
		'inbox'   => '<path d="M3 12h5l2 3h4l2-3h5"/><path d="M5 5h14l2 7v7H3v-7z"/>',
		'check'   => '<path d="M20 6 9 17l-5-5"/>',
		'tasks'   => '<path d="M9 6h11M9 12h11M9 18h11"/><path d="M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2"/>',
		'star'    => '<path d="m12 3 2.9 5.9 6.6.9-4.8 4.6 1.1 6.5L12 18l-5.8 3 1.1-6.5L2.5 9.8l6.6-.9z"/>',
		'shield'  => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>',
		'team'    => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 6a3 3 0 0 1 0 6"/><path d="M21 20a6 6 0 0 0-5-6"/>',
		'chart'   => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
		'image'   => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 17-5-5L5 21"/>',
		'layout'  => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 9v12"/>',
		'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'search'  => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
		'bell'    => '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M10 20a2 2 0 0 0 4 0"/>',
		'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
		'sun'     => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/>',
		'install' => '<path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M4 21h16"/>',
		'menu'    => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'help'    => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.8.4-1 .9-1 1.7"/><path d="M12 17h.01"/>',
		'caret'   => '<path d="m6 9 6 6 6-6"/>',
		'chevron' => '<path d="m9 6 6 6-6 6"/>',
		'moon'    => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/>',
		'movil'   => '<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>',
		'apps'    => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
		// Isotipo del portal (la planta). Definido acá y no suelto en cada
		// plantilla: lo usan el menú, el acceso y el splash.
		'marca'   => '<path d="M12 21V11"/><path d="M12 11c0-4 2-6 6-7-1 4-3 6-6 7Z"/><path d="M12 11C12 7 10 5 4 4c1 4 3 6 8 7Z"/>',
	);
	$d = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['doc'];
	return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}

/**
 * ¿Está activo el plugin de la API de la app (caaguazu-app-api)?
 *
 * El panel es la cabina de mando de la app: desde acá se editan sus textos,
 * sus medios y el icono y color de cada categoría. Pero es una dependencia
 * blanda —el panel funciona igual sin la app—, así que todo lo que la
 * controla se registra sólo si la API está instalada.
 *
 * @return bool
 */
function promotur_app_api_activa() {
	return class_exists( 'CZUAPI_UI_Content' ) && class_exists( 'CZUAPI_Taxonomias' );
}

/**
 * Items del menú del panel, agrupados como en el diseño: un bloque de trabajo
 * diario y otro de gestión, cada uno con su rótulo, y submenú donde una
 * sección tiene entradas que le cuelgan.
 *
 * Cada item: { route, label, icon, cap } y, opcionalmente, `badge` e `hijos`
 * (misma forma). Se filtran por capability en el sidebar: un item cuyo cap no
 * tiene la cuenta no se dibuja, y un grupo sin items visibles tampoco.
 *
 * Los rótulos de grupo se muestran en mayúsculas por CSS
 * (`.promotur-nav__group`), no por cómo estén escritos acá.
 *
 * @return array[] grupos: { label, items }
 */
function promotur_nav_grupos() {
	$grupos = array(
		array(
			'label' => __( 'GESTIÓN', 'caaguazu-portal' ),
			'items' => array(
				array( 'route' => 'panel',                'label' => __( 'Inicio', 'caaguazu-portal' ),           'icon' => 'home',  'cap' => 'promotur_view_panel' ),
				array(
					'route' => 'panel/mis-contenidos',
					'label' => __( 'Mis contenidos', 'caaguazu-portal' ),
					'icon'  => 'doc',
					'cap'   => 'promotur_create_draft',
					'hijos' => array(
						array( 'route' => 'panel/editor',  'label' => __( 'Nueva ficha', 'caaguazu-portal' ),     'icon' => 'edit',  'cap' => 'promotur_edit_destino' ),
						array( 'route' => 'panel/captura', 'label' => __( 'Salida de campo', 'caaguazu-portal' ), 'icon' => 'image', 'cap' => 'promotur_create_draft' ),
					),
				),
				array( 'route' => 'panel/revision', 'label' => __( 'Cola de revisión', 'caaguazu-portal' ), 'icon' => 'inbox', 'cap' => 'promotur_review_content', 'badge' => 'revision' ),
				array( 'route' => 'panel/tareas',   'label' => __( 'Tareas', 'caaguazu-portal' ),           'icon' => 'tasks', 'cap' => 'promotur_view_own_tasks', 'badge' => 'tareas' ),
			),
		),
		array(
			'label' => __( 'PORTAL', 'caaguazu-portal' ),
			'items' => array(
				array( 'route' => 'panel/equipo',     'label' => __( 'Equipo', 'caaguazu-portal' ),     'icon' => 'team',   'cap' => 'promotur_manage_team' ),
				array( 'route' => 'panel/reportes',   'label' => __( 'Reportes', 'caaguazu-portal' ),   'icon' => 'chart',  'cap' => 'promotur_view_reports' ),
				array( 'route' => 'panel/biblioteca', 'label' => __( 'Biblioteca', 'caaguazu-portal' ), 'icon' => 'image',  'cap' => 'promotur_manage_media' ),
				array( 'route' => 'panel/estructura', 'label' => __( 'Estructura', 'caaguazu-portal' ), 'icon' => 'layout', 'cap' => 'promotur_manage_structure' ),
			),
		),
	);

	if ( promotur_app_api_activa() ) {
		$grupos[1]['items'][] = array(
			'route' => 'panel/app',
			'label' => __( 'App', 'caaguazu-portal' ),
			'icon'  => 'movil',
			'cap'   => 'promotur_manage_app',
		);
	}

	/**
	 * @param array[] $grupos
	 */
	return apply_filters( 'promotur_nav_grupos', $grupos );
}

/**
 * Items del pie del menú: lo que no es navegación de trabajo (identidad,
 * ayuda) y vive separado por una línea.
 *
 * @return array[]
 */
function promotur_nav_pie() {
	return apply_filters( 'promotur_nav_pie', array(
		array( 'route' => 'panel/perfil', 'label' => __( 'Mi perfil', 'caaguazu-portal' ), 'icon' => 'user', 'cap' => 'promotur_edit_profile' ),
		array( 'route' => 'panel/ayuda',  'label' => __( 'Ayuda', 'caaguazu-portal' ),     'icon' => 'help', 'cap' => 'promotur_view_panel' ),
	) );
}

/**
 * Lista plana de todos los items del menú (grupos + hijos + pie).
 *
 * Se conserva porque es el contrato viejo del filtro `promotur_nav_items` y
 * porque hay pantallas que sólo necesitan "todos los destinos posibles" sin
 * importarles el agrupamiento.
 *
 * @return array[]
 */
function promotur_nav_items() {
	$items = array();
	foreach ( promotur_nav_grupos() as $grupo ) {
		foreach ( $grupo['items'] as $item ) {
			$hijos = isset( $item['hijos'] ) ? $item['hijos'] : array();
			unset( $item['hijos'] );
			$items[] = $item;
			foreach ( $hijos as $hijo ) {
				$items[] = $hijo;
			}
		}
	}
	foreach ( promotur_nav_pie() as $item ) {
		$items[] = $item;
	}
	return apply_filters( 'promotur_nav_items', $items );
}

/**
 * ¿Esta ruta del menú es la que se está viendo?
 *
 * @param string $route ej. 'panel/equipo'
 * @return bool
 */
function promotur_route_activa( $route ) {
	$actual = promotur_current_route();
	$seg    = ( 'panel' === $route ) ? 'home' : substr( $route, strlen( 'panel/' ) );
	if ( 'home' === $seg ) {
		return 'home' === $actual;
	}
	return '' !== $actual && 0 === strpos( $actual, $seg );
}

/**
 * Teléfono de la cuenta actual (columna `phone` de caaguazu_accounts).
 *
 * @return string
 */
function promotur_user_phone() {
	if ( ! function_exists( 'caaguazu_current_account' ) ) {
		return '';
	}
	$account = caaguazu_current_account();
	return $account ? (string) $account['phone'] : '';
}

/**
 * Mensaje efímero (flash) por visitante, vía transient. Patrón PRG.
 *
 * La clave usa el ID de cuenta si hay una logueada, o el ID de WordPress
 * prefijado ("wp123") para el bypass de administrador — así nunca colisiona
 * un ID de cuenta con un ID de usuario de WP que casualmente coincida.
 *
 * @param string|null $msg  null = leer y limpiar; string = setear
 * @param string      $type info|success|error
 * @return array|null  al leer: { message, type } o null
 */
function promotur_flash( $msg = null, $type = 'info' ) {
	$account_id = function_exists( 'caaguazu_account_id' ) ? caaguazu_account_id() : 0;
	if ( $account_id > 0 ) {
		$uid = $account_id;
	} else {
		$wp_id = get_current_user_id();
		if ( ! $wp_id ) { return null; }
		$uid = 'wp' . $wp_id;
	}
	$key = 'promotur_flash_' . $uid;

	if ( null === $msg ) {
		$data = get_transient( $key );
		if ( false !== $data ) {
			delete_transient( $key );
			return is_array( $data ) ? $data : null;
		}
		return null;
	}

	set_transient( $key, array( 'message' => (string) $msg, 'type' => $type ), 60 );
	return null;
}

/**
 * Avatar: la foto que la persona subió, o sus iniciales.
 *
 * Antes esto pedía la imagen a Gravatar con el hash del correo. Dos problemas:
 * mandaba el correo de cada promotor a un tercero en cada carga de pantalla, y
 * la foto se editaba en un sitio ajeno. Ahora la foto es de la cuenta —la sube
 * la persona en Mi perfil— y si no hay, quedan las iniciales.
 *
 * Acepta un ID de cuenta o una identidad ya armada (la de
 * promotur_current_identity()).
 *
 * @param int|array $identity
 * @param string    $extra_class
 * @return string HTML
 */
function promotur_avatar( $identity, $extra_class = '' ) {
	if ( is_array( $identity ) ) {
		$id   = (int) ( $identity['id'] ?? 0 );
		$name = (string) ( $identity['display_name'] ?? '' );
		if ( '' === $name ) {
			$name = (string) ( $identity['email'] ?? '' );
		}
	} else {
		$id     = (int) $identity;
		$cuenta = ( $id > 0 && class_exists( 'Caaguazu_Cuentas_Accounts' ) ) ? Caaguazu_Cuentas_Accounts::get( $id ) : null;
		$name   = $cuenta ? (string) ( $cuenta['display_name'] ? $cuenta['display_name'] : $cuenta['email'] ) : '';
	}
	if ( '' === trim( $name ) ) {
		return '';
	}

	$foto = class_exists( 'PROMOTUR_Cuenta' ) ? PROMOTUR_Cuenta::foto_url( $id ) : '';
	if ( $foto ) {
		return sprintf(
			'<span class="promotur-avatar %s"><img src="%s" alt="" width="36" height="36" loading="lazy"></span>',
			esc_attr( $extra_class ),
			esc_url( $foto )
		);
	}

	$parts    = preg_split( '/\s+/', trim( $name ) );
	$initials = strtoupper( mb_substr( $parts[0], 0, 1 ) . ( isset( $parts[1] ) ? mb_substr( $parts[1], 0, 1 ) : '' ) );
	return sprintf(
		'<span class="promotur-avatar promotur-avatar--initials %s" aria-hidden="true">%s</span>',
		esc_attr( $extra_class ),
		esc_html( $initials )
	);
}
