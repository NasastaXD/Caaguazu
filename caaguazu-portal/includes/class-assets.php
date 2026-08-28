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
		/*
		 * Prioridad 999: los themes encolan su hoja con la prioridad que se les
		 * ocurra —el de este sitio usa 100— y desencolar algo antes de que se
		 * encole no hace nada. Esta es la última palabra.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'desencolar_el_theme' ), 999 );
	}

	/**
	 * Saca de las rutas del panel cualquier hoja de estilo que venga del theme
	 * activo.
	 *
	 * POR QUÉ ACÁ TAMBIÉN, SI EL THEME YA NO SE ENCOLA
	 *
	 * El theme 5.0.4 dejó de encolar su CSS en las rutas del panel, y eso
	 * arregló el síntoma: los aros de la página de obra ya no se ven detrás del
	 * panel. Pero esa guarda vive en el theme — y el theme es justamente lo que
	 * se va a tirar y rehacer. El día que llegue el theme nuevo, la protección
	 * se va con el viejo.
	 *
	 * La promesa que el panel viene haciendo desde la 3.0.0 —en su propia
	 * descripción y en `docs/panel-turismo.md`— es más fuerte que eso: «el sitio
	 * público se puede rehacer entero sin poder cambiarle la cara al panel». Eso
	 * sólo se sostiene si la defensa está de este lado. Hasta ahora estaba
	 * documentada y no implementada.
	 *
	 * Se busca por ORIGEN y no por nombre de handle (`caaguazu-obra`): el handle
	 * es de este theme y de hoy, y la regla que hay que sostener es «el panel no
	 * hereda del theme, sea cual sea». Con el theme actual esto no desencola
	 * nada, porque ya no hay nada encolado: es una red, no un parche.
	 */
	public function desencolar_el_theme() {
		if ( ! $this->is_portal_route() ) {
			return;
		}

		$estilos = wp_styles();
		if ( ! $estilos ) {
			return;
		}

		$carpetas = array_unique( array(
			get_stylesheet_directory_uri(),
			get_template_directory_uri(),
		) );

		// Una copia de la cola: dequeue la modifica mientras se recorre.
		foreach ( (array) $estilos->queue as $handle ) {
			if ( ! isset( $estilos->registered[ $handle ] ) ) {
				continue;
			}
			$src = (string) $estilos->registered[ $handle ]->src;
			if ( '' === $src ) {
				continue;
			}
			foreach ( $carpetas as $carpeta ) {
				if ( $carpeta && 0 === strpos( $src, $carpeta ) ) {
					wp_dequeue_style( $handle );
					break;
				}
			}
		}
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
			// Las dos puertas del panel. Antes esto apuntaba a `admin-ajax.php`
			// y `admin-post.php`; ya no: el panel recibe lo suyo en su propia
			// URL y lo autentica con la cuenta, no con un usuario de WordPress.
			'datosUrl'  => promotur_url( 'datos' ) . '/',
			'accionUrl' => promotur_url( 'accion' ) . '/',
			'token'     => PROMOTUR_Acciones::token(),
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
				'error'    => __( 'Algo salió mal. Probá de nuevo.', 'caaguazu-portal' ),
				'saved'    => __( 'Guardado', 'caaguazu-portal' ),
				'confirm'  => __( '¿Querés confirmar esta acción?', 'caaguazu-portal' ),
				'missing'  => __( 'Faltan algunos datos obligatorios.', 'caaguazu-portal' ),
				'photoUploaded' => __( 'Foto subida.', 'caaguazu-portal' ),
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
