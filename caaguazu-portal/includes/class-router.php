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
	 */
	public function query_vars( $vars ) {
		$vars[] = 'promotur_route';
		$vars[] = 'promotur_sub';
		$vars[] = 'promotur_size';
		$vars[] = 'promotur_token';
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
	 * Reglas de reescritura.
	 *
	 * OJO con el orden: `add_rewrite_rule( …, 'top' )` **antepone**, así que la
	 * última regla agregada es la primera en evaluarse. Por eso acá se agregan
	 * de menos a más específica — al revés de como se leen.
	 *
	 * Estático para reutilizar en la activación, antes del flush.
	 */
	public static function add_rewrite_rules() {
		$base = PROMOTUR_BASE;

		// 1. Panel (lo más genérico: cualquier sección y su id opcional).
		add_rewrite_rule( '^' . $base . '/(.+?)/?$', 'index.php?promotur_route=panel&promotur_sub=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $base . '/?$',       'index.php?promotur_route=panel', 'top' );

		// 2. Auth. El identificador interno de la ruta de login sigue siendo
		//    'login' (query var, switch, template); sólo el slug público es
		//    /entrar.
		add_rewrite_rule( '^' . $base . '/entrar/?$',          'index.php?promotur_route=login', 'top' );
		add_rewrite_rule( '^' . $base . '/registro/?$',        'index.php?promotur_route=registro', 'top' );
		add_rewrite_rule( '^' . $base . '/recuperar/nueva/?$', 'index.php?promotur_route=restablecer', 'top' );
		add_rewrite_rule( '^' . $base . '/recuperar/?$',       'index.php?promotur_route=recuperar', 'top' );
		add_rewrite_rule( '^' . $base . '/salir/?$',           'index.php?promotur_route=salir', 'top' );
		add_rewrite_rule( '^' . $base . '/i/([^/]+)/?$',       'index.php?promotur_route=registro&promotur_token=$matches[1]', 'top' );

		// 3. PWA.
		add_rewrite_rule( '^' . $base . '/manifest\.webmanifest$', 'index.php?promotur_route=pwa-manifest', 'top' );
		add_rewrite_rule( '^' . $base . '/sw\.js$',                'index.php?promotur_route=pwa-sw', 'top' );
		add_rewrite_rule( '^' . $base . '/icon-([0-9]+)\.png$',    'index.php?promotur_route=pwa-icon&promotur_size=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $base . '/offline/?$',             'index.php?promotur_route=pwa-offline', 'top' );

		// 4. Rutas viejas → 301.
		foreach ( self::legacy_map() as $pattern => $destino ) {
			add_rewrite_rule( $pattern, 'index.php?promotur_route=legacy&promotur_sub=' . $destino, 'top' );
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

		nocache_headers();

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
				$this->guard_panel();
				status_header( 200 );
				$sub     = (string) get_query_var( 'promotur_sub' );
				$parts   = '' === $sub ? array() : explode( '/', trim( $sub, '/' ) );
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
