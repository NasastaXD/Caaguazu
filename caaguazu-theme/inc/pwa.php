<?php
/**
 * Instalación como app (PWA): manifest, service worker mínimo y el aviso de
 * "instalar app" — para que Agregar a inicio (iOS/Android) funcione bien y
 * el ícono/nombre que se instala sea el correcto. Sin build step ni
 * dependencias: el manifest y el service worker se sirven vía rewrite rules
 * en la raíz del sitio (mismo patrón que caaguazu-portal, adaptado a un
 * theme en vez de un plugin propio).
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Caaguazu_PWA {

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
		add_action( 'wp_footer', array( $this, 'render_install_ui' ) );
	}

	public function query_vars( $vars ) {
		$vars[] = 'caaguazu_pwa_route';
		return $vars;
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^caaguazu-manifest\.webmanifest$', 'index.php?caaguazu_pwa_route=manifest', 'top' );
		add_rewrite_rule( '^caaguazu-sw\.js$', 'index.php?caaguazu_pwa_route=sw', 'top' );
	}

	public function dispatch() {
		$route = get_query_var( 'caaguazu_pwa_route' );
		if ( 'manifest' === $route ) {
			$this->render_manifest();
			exit;
		}
		if ( 'sw' === $route ) {
			$this->render_sw();
			exit;
		}
	}

	/**
	 * manifest.webmanifest — nombre, ícono y modo standalone para que
	 * "Agregar a inicio" instale algo que se parezca a una app.
	 */
	public function render_manifest() {
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		$icons_uri = get_template_directory_uri() . '/assets/icons/';
		$data      = array(
			'name'             => get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'Caaguazú',
			'short_name'       => 'Caaguazú',
			'description'      => 'Portal oficial del departamento de Caaguazú.',
			'start_url'        => home_url( '/?utm_source=pwa' ),
			'scope'            => home_url( '/' ),
			'display'          => 'standalone',
			'orientation'      => 'portrait',
			'background_color' => '#fdfdfb',
			'theme_color'      => '#123f2c',
			'lang'             => 'es',
			'icons'            => array(
				array( 'src' => $icons_uri . 'icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => $icons_uri . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => $icons_uri . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
			),
		);
		echo wp_json_encode( $data );
	}

	/**
	 * Service worker mínimo: network-first para navegación, con la home
	 * cacheada como fallback si no hay conexión. No pretende ser una app
	 * offline completa — solo cumple el requisito de instalabilidad de
	 * Chrome/Android y evita una pantalla en blanco sin señal.
	 */
	public function render_sw() {
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: /' );
		$version = defined( 'CAAGUAZU_VERSION' ) ? CAAGUAZU_VERSION : '1';
		?>
const CACHE = 'caaguazu-shell-v<?php echo esc_js( $version ); ?>';
const HOME  = '<?php echo esc_js( home_url( '/' ) ); ?>';

self.addEventListener('install', function (e) {
	self.skipWaiting();
});

self.addEventListener('activate', function (e) {
	e.waitUntil(
		caches.keys().then(function (keys) {
			return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); }));
		})
	);
});

self.addEventListener('fetch', function (e) {
	if (e.request.mode !== 'navigate') { return; }
	e.respondWith(
		fetch(e.request).then(function (res) {
			var copy = res.clone();
			caches.open(CACHE).then(function (c) { c.put(e.request, copy); });
			return res;
		}).catch(function () {
			return caches.match(e.request).then(function (cached) {
				return cached || caches.match(HOME);
			});
		})
	);
});
		<?php
	}

	/**
	 * Banner "Instalar app": en Chrome/Android usa el prompt nativo
	 * (beforeinstallprompt); en iOS Safari, que nunca lo va a disparar,
	 * muestra el tip manual (Compartir → Agregar a inicio). Se acuerda con
	 * localStorage si ya se instaló o si el usuario lo cerró, para no
	 * insistir en cada visita.
	 */
	public function render_install_ui() {
		?>
		<div class="install-banner" id="caaguazuInstallBanner" hidden>
			<span class="install-banner-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M4 21h16"/></svg>
			</span>
			<p><span id="caaguazuInstallText"><?php esc_html_e( 'Instalá Caaguazú como app en tu celular.', 'caaguazu' ); ?></span></p>
			<button type="button" class="install-banner-btn" id="caaguazuInstallBtn"><?php esc_html_e( 'Instalar', 'caaguazu' ); ?></button>
			<button type="button" class="install-banner-close" id="caaguazuInstallClose" aria-label="<?php esc_attr_e( 'Cerrar', 'caaguazu' ); ?>">&times;</button>
		</div>
		<script>
		(function () {
			var KEY = 'caaguazuInstallDismissed';
			var banner = document.getElementById('caaguazuInstallBanner');
			var text   = document.getElementById('caaguazuInstallText');
			var btn    = document.getElementById('caaguazuInstallBtn');
			var close  = document.getElementById('caaguazuInstallClose');
			if (!banner) { return; }

			var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
			if (standalone) { return; }
			try { if (localStorage.getItem(KEY)) { return; } } catch (e) {}

			function dismiss() {
				banner.hidden = true;
				try { localStorage.setItem(KEY, '1'); } catch (e) {}
			}
			close.addEventListener('click', dismiss);

			var deferredPrompt = null;
			window.addEventListener('beforeinstallprompt', function (e) {
				e.preventDefault();
				deferredPrompt = e;
				banner.hidden = false;
			});
			btn.addEventListener('click', function () {
				if (deferredPrompt) {
					deferredPrompt.prompt();
					deferredPrompt.userChoice.finally(dismiss);
				} else {
					dismiss();
				}
			});

			var ua = window.navigator.userAgent;
			var isIos = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
			if (isIos) {
				text.textContent = <?php echo wp_json_encode( __( 'Tocá Compartir y luego "Agregar a inicio" para instalar la app.', 'caaguazu' ) ); ?>;
				btn.hidden = true;
				banner.hidden = false;
			}

			if ('serviceWorker' in navigator) {
				window.addEventListener('load', function () {
					navigator.serviceWorker.register('/caaguazu-sw.js').catch(function () {});
				});
			}
		})();
		</script>
		<?php
	}
}
add_action( 'after_setup_theme', array( 'Caaguazu_PWA', 'instance' ) );

/**
 * `register_activation_hook` no existe para un theme — un sitio que ya
 * tuviera este theme activo antes de que existiera esta clase nunca
 * registraría las rewrite rules nuevas. Catch-up en `admin_init` (no en
 * cada visita pública, que es exactamente el bug que se corrigió en
 * caaguazu-portal por hacer esto mismo en `plugins_loaded`).
 */
function caaguazu_pwa_maybe_flush() {
	if ( get_option( 'caaguazu_pwa_flushed_v1' ) ) {
		return;
	}
	Caaguazu_PWA::add_rewrite_rules();
	flush_rewrite_rules();
	update_option( 'caaguazu_pwa_flushed_v1', 1 );
}
add_action( 'admin_init', 'caaguazu_pwa_maybe_flush' );
