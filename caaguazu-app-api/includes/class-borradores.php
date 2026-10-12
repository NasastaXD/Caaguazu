<?php
/**
 * Lo que todavía no está publicado, para quien lo revisa.
 *
 * La API pública sirve sólo `publicado`, y eso no cambia: esta clase no abre
 * nada al público. Lo que agrega es una vista PREVIA para las cuentas que en
 * el panel pueden revisar contenido (`promotur_review_content`: el Profesor,
 * no el Alumno), para ver en la web cómo va a quedar una ficha o un artículo
 * antes de aprobarlo — y, en una presentación, para mostrar lo que el equipo
 * está cargando y no sólo lo poco que ya salió.
 *
 * CÓMO SE SABE QUIÉN ES
 *
 * No hay login nuevo. La web vive en el mismo dominio que el panel y la cookie
 * de sesión de `caaguazu-cuentas` va con ruta `/`, así que llega también acá.
 * La app, si algún día lo usa, manda su token como en todo lo demás. Se pasa
 * el ID de cuenta explícito a `caaguazu_account_can()` para que el atajo de
 * los administradores de WordPress no entre por la ventana: en la API REST su
 * cookie no vale sin nonce, y una regla que depende de eso es una regla que
 * nadie entiende.
 *
 * QUÉ NO SE GUARDA EN NINGÚN LADO
 *
 * Toda respuesta de esta clase sale `private, no-store`. Una ficha en borrador
 * que un intermediario guardara y le sirviera a otro sería justo publicar lo
 * que nadie aprobó.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Borradores {

	private static $instance = null;

	/**
	 * Estados que cuentan como «en camino». Despublicado y archivado no: son
	 * cosas que salieron de circulación, no que están por entrar.
	 */
	const ESTADOS = array( 'borrador', 'enviado', 'en_revision', 'necesita_cambios', 'aprobado' );

	/** Quién puede ver la vista previa. */
	const CAP = 'promotur_review_content';

	/** Cuántas piezas de cada tipo, como mucho. Es una vista previa, no un listado. */
	const TOPE = 40;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/** La cuenta del pedido: el token de la app, o la cookie del panel. */
	public static function cuenta_id() {
		$id = CZUAPI_Auth::instance()->current_account_id();
		if ( $id <= 0 && function_exists( 'caaguazu_account_id' ) ) {
			$id = (int) caaguazu_account_id();
		}
		return (int) $id;
	}

	public static function puede_ver() {
		$id = self::cuenta_id();
		return $id > 0 && function_exists( 'caaguazu_account_can' ) && caaguazu_account_can( 'promotor', self::CAP, $id );
	}

	/** Estado editorial de una pieza; sin meta es un borrador, como en el panel. */
	public static function estado( $post_id ) {
		$e = (string) get_post_meta( (int) $post_id, '_promotur_estado', true );
		return '' !== $e ? $e : 'borrador';
	}

	/** ¿Es algo en camino de publicarse? */
	public static function en_camino( $post ) {
		return $post && 'draft' === $post->post_status && in_array( self::estado( $post->ID ), self::ESTADOS, true );
	}

	/**
	 * ¿Este pedido de detalle puede ver esta pieza aunque no esté publicada?
	 * Sólo si lo pidió (`?borrador=1`) y si quien pide puede: la URL pública
	 * de siempre nunca cambia de respuesta según quién la abre.
	 */
	public static function detalle_permitido( $post, $request ) {
		return (bool) $request->get_param( 'borrador' ) && self::en_camino( $post ) && self::puede_ver();
	}

	/** Que nadie entre el servidor y quien pidió se la guarde. */
	public static function privada( WP_REST_Response $res ) {
		$res->header( 'Cache-Control', 'private, no-store' );
		// LiteSpeed decide su caché por su propia cabecera, no por la de arriba.
		$res->header( 'X-LiteSpeed-Cache-Control', 'no-cache' );
		return $res;
	}

	/* --------------------------------------------------------------------- */
	/*  REST                                                                  */
	/* --------------------------------------------------------------------- */

	public function register_routes() {
		register_rest_route( CZUAPI_NS, '/borradores', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'lista' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'idioma' => array( 'type' => 'string' ),
			),
		) );
	}

	/**
	 * Fichas y artículos en camino, lo último que se tocó primero. Cada uno
	 * con la misma forma que en su lista pública más `estado`, así la web los
	 * dibuja con las mismas tarjetas.
	 */
	public function lista( $request ) {
		if ( self::cuenta_id() <= 0 ) {
			return self::privada( CZUAPI_Response::no_autenticado() );
		}
		if ( ! self::puede_ver() ) {
			return self::privada( CZUAPI_Response::sin_permiso() );
		}

		$idioma = CZUAPI_Idiomas::del_pedido( $request );
		$fichas = array();
		foreach ( self::consultar( PROMOTUR_Destinos::CPT ) as $post ) {
			$fichas[] = array_merge( CZUAPI_Inventario::instance()->item_lista( $post, $idioma ), array( 'estado' => self::estado( $post->ID ) ) );
		}
		$articulos = array();
		foreach ( self::consultar( CZUAPI_Articulos::CPT ) as $post ) {
			$articulos[] = array_merge( CZUAPI_Articulos::instance()->resumen( $post, $idioma ), array( 'estado' => self::estado( $post->ID ) ) );
		}

		return self::privada( new WP_REST_Response( array(
			'fichas'    => $fichas,
			'articulos' => $articulos,
		), 200 ) );
	}

	/** @return WP_Post[] */
	private static function consultar( $cpt ) {
		$q = new WP_Query( array(
			'post_type'           => $cpt,
			'post_status'         => 'draft',
			'posts_per_page'      => self::TOPE,
			'orderby'             => 'modified',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			// Sin el meta también cuenta: así nacen las piezas del panel.
			'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array( 'key' => '_promotur_estado', 'value' => self::ESTADOS, 'compare' => 'IN' ),
				array( 'key' => '_promotur_estado', 'compare' => 'NOT EXISTS' ),
			),
		) );
		return $q->posts;
	}
}
