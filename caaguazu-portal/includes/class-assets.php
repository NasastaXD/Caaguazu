<?php
/**
 * Assets del portal: un único CSS + un único JS, encolados SOLO en rutas del portal.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Assets {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Rutas que cargan los assets del portal.
	 */
	private function is_portal_route() {
		$route = get_query_var( 'promotur_route' );
		return in_array( $route, array( 'panel', 'login', 'registro', 'recuperar', 'restablecer', 'pwa-offline' ), true );
	}

	public function enqueue() {
		if ( ! $this->is_portal_route() ) {
			return;
		}

		// El panel ya no hereda la tipografía del theme: la trae él, servida
		// desde el propio plugin. Un panel de trabajo no puede depender de que
		// el sitio público tenga tal o cual theme activo — ni de un CDN de
		// fuentes, que es una llamada a un tercero en el camino crítico.
		add_action( 'wp_head', array( $this, 'preload_fonts' ), 1 );

		wp_enqueue_style(
			'promotur',
			promotur_asset( 'css/caaguazu-portal.css' ),
			array(),
			PROMOTUR_VERSION
		);

		wp_enqueue_script(
			'promotur',
			promotur_asset( 'js/caaguazu-portal.js' ),
			array(),
			PROMOTUR_VERSION,
			true
		);

		wp_localize_script( 'promotur', 'PROMOTUR', array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'adminPost' => admin_url( 'admin-post.php' ),
			'nonce'     => wp_create_nonce( 'promotur' ),
			'swUrl'     => promotur_url( 'sw.js' ),
			'swScope'   => '/' . PROMOTUR_BASE . '/',
			'manifest'  => promotur_url( 'manifest.webmanifest' ),
			'urls'      => array(
				'panel' => promotur_url( 'panel' ),
				'login' => promotur_url( 'login' ),
				'salir' => promotur_url( 'salir' ),
			),
			'i18n'      => array(
				'install'  => __( 'Instalar app', 'caaguazu-portal' ),
				'sending'  => __( 'Enviando…', 'caaguazu-portal' ),
				'error'    => __( 'Ocurrió un error. Probá de nuevo.', 'caaguazu-portal' ),
				'saved'    => __( 'Guardado', 'caaguazu-portal' ),
				'confirm'  => __( '¿Confirmás esta acción?', 'caaguazu-portal' ),
				'missing'  => __( 'Faltan datos obligatorios', 'caaguazu-portal' ),
			),
		) );
	}

	/**
	 * Precarga de las dos variantes que se ven en el primer dibujado (texto y
	 * títulos). Las demás cargan cuando hagan falta: precargar todo compite
	 * con el CSS por el mismo ancho de banda.
	 */
	public function preload_fonts() {
		foreach ( array( 'inter-400.woff2', 'inter-600.woff2' ) as $file ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( promotur_asset( 'fonts/' . $file ) )
			);
		}
	}
}
