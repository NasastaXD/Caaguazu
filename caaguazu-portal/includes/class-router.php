<?php
/**
 * Enrutador propio: query vars, rewrite rules, dispatch y guards.
 *
 * Todo el panel cuelga de PROMOTUR_BASE (/turismo-panel): secciones, auth y
 * PWA. Las rutas viejas —repartidas por la raíz del sitio— siguen existiendo
 * como redirecciones 301 permanentes: hay invitaciones ya enviadas con links
 * /i/<token> y promotores con la PWA instalada apuntando a /turismo/panel.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Router {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_action( 'template_redirect', array( $this, 'dispatch' ) );

		// Bloquear wp-login.php para usuarios del portal (con excepciones).
		add_action( 'login_init', array( $this, 'maybe_block_wp_login' ) );

		// WP no debe "adivinar" URLs y romper nuestras rutas.
		remove_action( 'template_redirect', 'redirect_guess_404_permalink' );
	}

	/**
	 * Query vars propias.
	 *
	 * NINGUNA se puede llamar como un campo que viaje en un formulario del
	 * panel. `WP::parse_request()` recorre las query vars públicas y le da
	 * prioridad a `$_POST[ $var ]` por encima de lo que matcheó la regla de
	 * reescritura, así que un campo del formulario con el mismo nombre que una
	 * query var la pisa en cada envío.
	 *
	 * Eso pasó: la de la invitación se llamaba `promotur_token`, igual que el
	 * campo de seguridad que `PROMOTUR_Acciones::campos()` mete en todos los
	 * formularios. Al ABRIR el enlace de invitación andaba —un GET no manda ese
	 * campo—, pero al ENVIAR el alta el HMAC de seguridad ocupaba el lugar del
	 * token y el registro moría con «necesitás una invitación válida»: el
	 * síntoma aparecía en el último paso y no se parecía a la causa.
	 * `tools/verificar-rutas.php` comprueba que no vuelva a chocar.
	 */
	public function query_vars( $vars ) {
		$vars[] = 'promotur_route';
		$vars[] = 'promotur_sub';
		$vars[] = 'promotur_size';
		$vars[] = 'promotur_invitacion';
		return $vars;
	}

	/**
	 * Rutas viejas → destino nuevo, relativo a PROMOTUR_BASE.
	 * Se sirven como 301 para no romper links ya repartidos.
	 *
	 * @return array patrón de reescritura => ruta nueva (puede llevar $matches)
	 */
	private static function legacy_map() {
		return array(
			'^czu-login/?$'                       => 'entrar',
			'^registro/?$'                        => 'registro',
			'^recuperar/restablecer/?$'           => 'recuperar/nueva',
			'^recuperar/?$'                       => 'recuperar',
			'^salir/?$'                           => 'salir',
			'^i/([^/]+)/?$'                       => 'i/$matches[1]',
			'^turismo/panel/?$'                   => '',
			'^turismo/panel/(.+?)/?$'             => '$matches[1]',
			'^promotur-manifest\.webmanifest$'    => 'manifest.webmanifest',
			'^promotur-sw\.js$'                   => 'sw.js',
			'^promotur-icon-([0-9]+)\.png$'       => 'icon-$matches[1].png',
			'^promotur-offline/?$'                => 'offline',
		);
	}

	/**
	 * Todas las reglas de reescritura del panel: patrón => destino.
	 *
	 * Es un mapa y no una lista de llamadas sueltas para que se pueda
	 * **verificar**: `promotur_asegurar_rewrite_rules()` recorre este mismo
	 * mapa y comprueba que las reglas guardadas las tengan todas. Antes esa
	 * comprobación miraba una sola —la del inicio del panel— y eso dejaba
	 * pasar el peor estado posible: las páginas del panel se dibujan, pero
	 * `/turismo-panel/datos/…` no existe como regla y se lo come la regla
	 * genérica de sección. Ver el porqué en dispatch().
	 *
	 * EL ORDEN ES EL DE ESTE ARRAY, Y VA DE LO MÁS ESPECÍFICO A LO MÁS
	 * GENÉRICO. `add_rewrite_rule( …, 'top' )` NO antepone regla por regla:
	 * `WP_Rewrite::add_rule()` hace `array_merge( $this->extra_rules_top,
	 * array( $regex => $query ) )`, que **appendea** dentro del grupo. «top»
	 * quiere decir que todo el grupo se evalúa antes que las reglas propias de
	 * WordPress, no que cada regla nueva salte por encima de la anterior.
	 * Dentro del grupo manda el orden de inserción, y WP se queda con la
	 * primera que matchea.
	 *
	 * Este comentario decía lo contrario, y el mapa estaba escrito al revés
	 * por creerlo: la regla genérica de sección quedaba primera y se comía
	 * TODO —login, registro, recuperar, salir, el enlace de invitación y los
	 * cuatro recursos de la PWA—. Cada una de esas URLs terminaba en el guard
	 * del panel, que redirige a login… que tampoco resolvía, así que el
	 * navegador rebotaba hasta cortar por «demasiadas redirecciones». Lo único
	 * que lo disimulaba era que el panel ya andaba con la sesión abierta y que
	 * `accion`/`datos` tienen una red de contención dentro de dispatch().
	 *
	 * Si se agrega una regla nueva: va ARRIBA de las dos genéricas del final.
	 *
	 * @return array patrón => destino
	 */
	public static function reglas() {
		$base = PROMOTUR_BASE;

		$reglas = array(
			// 1. Las dos puertas del panel: formularios y JavaScript. Antes que
			//    la genérica de sección, para que /turismo-panel/accion/x no se
			//    lea como una sección llamada "accion".
			'^' . $base . '/accion/([a-z0-9_-]+)/?$' => 'index.php?promotur_route=accion&promotur_sub=$matches[1]',
			'^' . $base . '/datos/([a-z0-9_-]+)/?$'  => 'index.php?promotur_route=datos&promotur_sub=$matches[1]',

			// 2. Auth. El identificador interno de la ruta de login sigue
			//    siendo 'login' (query var, switch, template); sólo el slug
			//    público es /entrar.
			'^' . $base . '/entrar/?$'          => 'index.php?promotur_route=login',
			'^' . $base . '/registro/?$'        => 'index.php?promotur_route=registro',
			'^' . $base . '/recuperar/nueva/?$' => 'index.php?promotur_route=restablecer',
			'^' . $base . '/recuperar/?$'       => 'index.php?promotur_route=recuperar',
			'^' . $base . '/salir/?$'           => 'index.php?promotur_route=salir',
			'^' . $base . '/i/([^/]+)/?$'       => 'index.php?promotur_route=registro&promotur_invitacion=$matches[1]',

			// 3. PWA.
			'^' . $base . '/manifest\.webmanifest$' => 'index.php?promotur_route=pwa-manifest',
			'^' . $base . '/sw\.js$'                => 'index.php?promotur_route=pwa-sw',
			'^' . $base . '/icon-([0-9]+)\.png$'    => 'index.php?promotur_route=pwa-icon&promotur_size=$matches[1]',
			'^' . $base . '/offline/?$'             => 'index.php?promotur_route=pwa-offline',
		);

		// 4. Rutas viejas → 301. No arrancan con la base, así que no las tapa
		//    la genérica de abajo; van acá igual para dejar el comodín último.
		foreach ( self::legacy_map() as $patron => $destino ) {
			$reglas[ $patron ] = 'index.php?promotur_route=legacy&promotur_sub=' . $destino;
		}

		// 5. Panel: el comodín. Va al final de todo, porque `(.+?)` matchea
		//    cualquier cosa colgada de la base y dejaría sin efecto a todo lo
		//    que venga después.
		$reglas[ '^' . $base . '/?$' ]       = 'index.php?promotur_route=panel';
		$reglas[ '^' . $base . '/(.+?)/?$' ] = 'index.php?promotur_route=panel&promotur_sub=$matches[1]';

		return $reglas;
	}

	/**
	 * Registra las reglas. Estático para reutilizar en la activación, antes
	 * del flush.
	 */
	public static function add_rewrite_rules() {
		foreach ( self::reglas() as $patron => $destino ) {
			add_rewrite_rule( $patron, $destino, 'top' );
		}
	}

	/**
	 * Despacho en template_redirect.
	 */
	public function dispatch() {
		$route = get_query_var( 'promotur_route' );
		if ( '' === $route || null === $route ) {
			return;
		}

		promotur_no_cachear();

		// El panel es una "app": sin admin bar de WordPress.
		add_filter( 'show_admin_bar', '__return_false' );

		switch ( $route ) {
			case 'legacy':
				// 301 a la ruta equivalente bajo /turismo-panel, conservando
				// la query string (?next=…, ?reset=1).
				$destino = ltrim( (string) get_query_var( 'promotur_sub' ), '/' );
				$url     = promotur_url( $destino );
				$args    = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
				unset( $args['promotur_route'], $args['promotur_sub'] );
				if ( $args ) {
					$url = add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $args ) ), $url );
				}
				wp_safe_redirect( $url, 301 );
				exit;

			case 'accion':
			case 'datos':
				PROMOTUR_Acciones::instance()->despachar(
					$route,
					sanitize_key( (string) get_query_var( 'promotur_sub' ) )
				);
				exit;

			case 'pwa-manifest':
				PROMOTUR_PWA::instance()->render_manifest();
				exit;
			case 'pwa-sw':
				PROMOTUR_PWA::instance()->render_sw();
				exit;
			case 'pwa-icon':
				PROMOTUR_PWA::instance()->render_icon( (int) get_query_var( 'promotur_size' ) );
				exit;
			case 'pwa-offline':
				status_header( 200 );
				PROMOTUR_PWA::instance()->render_offline();
				exit;

			case 'login':
			case 'registro':
			case 'recuperar':
			case 'restablecer':
				status_header( 200 );
				PROMOTUR_Auth::instance()->render( $route );
				exit;

			case 'salir':
				PROMOTUR_Auth::instance()->logout();
				exit;

			case 'panel':
				$sub   = (string) get_query_var( 'promotur_sub' );
				$parts = '' === $sub ? array() : explode( '/', trim( $sub, '/' ) );

				/*
				 * RED DE SEGURIDAD, y no una cortesía.
				 *
				 * Si las reglas guardadas perdieron la de `/datos/` o la de
				 * `/accion/` —una restauración de base, un plugin de caché que
				 * se guardó un juego viejo, un flush a medias—, la regla
				 * genérica de sección se come igual esas URLs y este case las
				 * recibe como si `datos` fuera una sección. Lo que pasaba
				 * entonces era lo peor de los dos mundos: el shell dibujaba su
				 * página 404 **con status 200 y en HTML**, el JavaScript hacía
				 * `r.json()` sobre eso, reventaba, y en pantalla salía «Algo
				 * salió mal. Probá de nuevo.» — sin guardar, sin subir la foto
				 * y sin decir por qué. Las páginas del panel seguían andando,
				 * así que nada delataba el origen.
				 *
				 * `datos` y `accion` son palabras reservadas: no hay ni puede
				 * haber una sección con esos nombres (ver
				 * PROMOTUR_Roles::sections()). Si llegan hasta acá, es esto.
				 */
				$puertas = array( 'accion' => 'accion', 'datos' => 'datos' );
				if ( isset( $parts[0], $parts[1] ) && isset( $puertas[ $parts[0] ] ) ) {
					PROMOTUR_Acciones::instance()->despachar(
						$puertas[ $parts[0] ],
						sanitize_key( $parts[1] )
					);
					exit;
				}

				$this->guard_panel();
				status_header( 200 );
				$section = ! empty( $parts[0] ) ? sanitize_key( $parts[0] ) : 'home';
				$id      = isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : null;
				PROMOTUR_Shell::instance()->render( $section, $id );
				exit;
		}
	}

	/**
	 * Guard del panel: sesión (cuenta propia, o admin de WP vía bypass) + capability base.
	 */
	private function guard_panel() {
		// caaguazu_is_logged_in() cubre la sesión propia; los administradores
		// de WordPress (no migrados a cuentas a propósito) entran por su
		// login de wp-admin de siempre — ver caaguazu_wp_admin_bypass().
		if ( ! caaguazu_is_logged_in() && ! ( function_exists( 'caaguazu_wp_admin_bypass' ) && caaguazu_wp_admin_bypass() ) ) {
			$current = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
			wp_safe_redirect( promotur_url( 'login' ) . '?next=' . rawurlencode( $current ) );
			exit;
		}
		if ( ! caaguazu_account_can( 'promotor', 'promotur_view_panel' ) ) {
			wp_die(
				esc_html__( 'No tenés acceso a este panel.', 'caaguazu-portal' ),
				esc_html__( 'Acceso denegado', 'caaguazu-portal' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Redirige wp-login.php al login del portal, salvo admins y acciones de sistema.
	 */
	public function maybe_block_wp_login() {
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';
		$exempt = array( 'logout', 'resetpass', 'rp', 'postpass', 'lostpassword', 'retrievepassword' );
		if ( in_array( $action, $exempt, true ) ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return; // admins conservan wp-login.php
		}
		if ( ! apply_filters( 'promotur_block_wp_login', true ) ) {
			return;
		}
		wp_safe_redirect( promotur_url( 'login' ) );
		exit;
	}
}
