<?php
/**
 * `/eventos`: lo que pasa en una fecha, venga de donde venga.
 *
 * DOS FUENTES, UNA LISTA
 *
 * Desde que la ficha del panel tiene tipo de item, un evento se carga como
 * ficha —con `_promotur_tipo_item = evento` y sus fechas— y así hereda el
 * modelo entero: gancho, galería, fuentes, flujo editorial, enlace de Google
 * Maps. Ese es el camino nuevo y el único que alguien puede usar hoy desde el
 * panel.
 *
 * El CPT `promotur_evento` de este plugin es el camino viejo, editable sólo
 * desde wp-admin y con la mitad de los campos. No se borra porque lo que ya
 * está cargado ahí sigue existiendo y los teléfonos lo tienen en caché: sacarlo
 * de la API lo haría desaparecer sin lápida. Así que `/eventos` mezcla las dos
 * fuentes, las ordena por fecha de inicio y las devuelve con la misma forma. El
 * cliente no tiene que saber de dónde salió cada una; si le importa, `origen`
 * se lo dice.
 *
 * El lugar puede venir de tres formas: la ficha trae el suyo; el evento viejo
 * puede referenciar un destino ya cargado (lo habitual — la fiesta es EN tal
 * lugar) o tener coordenadas propias. La API resuelve las tres a un mismo
 * bloque `lugar` con lat/lng ya listas, para que el cliente no ramifique.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Eventos {

	private static $instance = null;

	const CPT = 'promotur_evento';

	const META_INICIO   = '_evento_inicio';
	const META_FIN      = '_evento_fin';
	const META_LUGAR_ID = '_evento_lugar_ref';  // ID de promotur_destino, o 0
	const META_LAT      = '_evento_lat';
	const META_LNG      = '_evento_lng';
	const META_COSTO    = '_evento_costo';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	public function register_post_type() {
		register_post_type( self::CPT, array(
			'labels' => array(
				'name'          => __( 'Eventos', 'caaguazu-app-api' ),
				'singular_name' => __( 'Evento', 'caaguazu-app-api' ),
				'menu_name'     => __( 'Eventos', 'caaguazu-app-api' ),
			),
			'public'          => true,
			'show_ui'         => true,
			'show_in_rest'    => false, // la app usa /czu-app/v1/eventos, no el REST nativo
			'menu_icon'       => 'dashicons-calendar-alt',
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'     => false,
			'rewrite'         => array( 'slug' => 'evento' ),
			'taxonomies'      => array( CZUAPI_Taxonomias::TAX_CATEGORIA ),
		) );

		foreach ( array( self::META_INICIO, self::META_FIN, self::META_COSTO ) as $key ) {
			register_post_meta( self::CPT, $key, array(
				'type' => 'string', 'single' => true, 'show_in_rest' => false,
			) );
		}
		foreach ( array( self::META_LAT, self::META_LNG ) as $key ) {
			register_post_meta( self::CPT, $key, array(
				'type' => 'number', 'single' => true, 'show_in_rest' => false,
			) );
		}
		register_post_meta( self::CPT, self::META_LUGAR_ID, array(
			'type' => 'integer', 'single' => true, 'show_in_rest' => false,
		) );
	}

	public function register_routes() {
		register_rest_route( CZUAPI_NS, '/eventos', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'lista' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'desde'      => array( 'type' => 'string' ), // ISO 8601
				'hasta'      => array( 'type' => 'string' ),
				'categoria'  => array( 'type' => 'integer' ),
				'pagina'     => array( 'type' => 'integer', 'default' => 1 ),
				'por_pagina' => array( 'type' => 'integer', 'default' => 20 ),
				'idioma'     => array( 'type' => 'string' ),   // es | en | pt
			),
		) );

		register_rest_route( CZUAPI_NS, '/eventos/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'detalle' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'idioma' => array( 'type' => 'string' ),
			),
		) );
	}

	public function lista( $request ) {
		$pagina     = max( 1, (int) $request->get_param( 'pagina' ) );
		$por_pagina = min( 100, max( 1, (int) $request->get_param( 'por_pagina' ) ) );

		// Por defecto: lo que todavía no terminó. Un evento pasado no le sirve
		// a un turista, y hacer que el cliente filtre implica bajarlos todos.
		$desde = $request->get_param( 'desde' );
		$hasta = $request->get_param( 'hasta' );
		$desde = $desde ? gmdate( 'Y-m-d H:i:s', strtotime( (string) $desde ) ) : gmdate( 'Y-m-d H:i:s' );
		$hasta = $hasta ? gmdate( 'Y-m-d H:i:s', strtotime( (string) $hasta ) ) : '';
		$cat   = (int) $request->get_param( 'categoria' );

		/*
		 * Las dos fuentes se traen enteras y se paginan acá, en vez de pedirle a
		 * la base una sola consulta: son dos CPT con metas de nombre distinto y
		 * `WP_Query` no sabe ordenar por «esta meta o esta otra». El tope de 200
		 * por fuente está muy por encima de lo que un departamento agenda en una
		 * temporada, y evita que un error de carga se lleve puesta la memoria.
		 */
		$idioma = CZUAPI_Idiomas::del_pedido( $request );
		$items  = array_merge(
			$this->de_fichas( $desde, $hasta, $cat, $idioma ),
			$this->de_cpt_viejo( $desde, $hasta, $cat )
		);

		usort( $items, function ( $a, $b ) {
			return strcmp( (string) $a['inicio'], (string) $b['inicio'] );
		} );

		$total = count( $items );
		$pagos = array_slice( $items, ( $pagina - 1 ) * $por_pagina, $por_pagina );

		return CZUAPI_Response::with_etag(
			CZUAPI_Response::paginado( array_values( $pagos ), $total, $pagina, $por_pagina ),
			$request,
			60
		);
	}

	/**
	 * Los eventos que salieron de una ficha del panel.
	 *
	 * @param string $desde 'Y-m-d H:i:s'
	 * @param string $hasta 'Y-m-d H:i:s' o ''
	 * @param int    $cat   term_id de categoría, o 0
	 * @return array[]
	 */
	private function de_fichas( $desde, $hasta, $cat, $idioma = 'es' ) {
		$meta_query = array(
			array( 'key' => PROMOTUR_Destinos::META_TIPO_ITEM, 'value' => 'evento' ),
			array( 'key' => PROMOTUR_Destinos::META_INICIO, 'value' => $desde, 'compare' => '>=', 'type' => 'DATETIME' ),
		);
		if ( '' !== $hasta ) {
			$meta_query[] = array( 'key' => PROMOTUR_Destinos::META_INICIO, 'value' => $hasta, 'compare' => '<=', 'type' => 'DATETIME' );
		}
		$meta_query['relation'] = 'AND';

		$args = array_merge( czuapi_args_publicado(), array(
			'post_type'      => PROMOTUR_Destinos::CPT,
			'posts_per_page' => 200,
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		if ( $cat ) {
			$args['tax_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'taxonomy' => CZUAPI_Taxonomias::TAX_CATEGORIA,
				'field'    => 'term_id',
				'terms'    => $cat,
			) );
		}

		$out = array();
		foreach ( get_posts( $args ) as $post ) {
			$out[] = $this->formato_ficha( $post, $idioma );
		}
		return $out;
	}

	/**
	 * Los eventos del CPT viejo, los que se cargaron antes de que la ficha
	 * tuviera tipo de item.
	 *
	 * @return array[]
	 */
	private function de_cpt_viejo( $desde, $hasta, $cat ) {
		$meta_query = array(
			array( 'key' => self::META_INICIO, 'value' => $desde, 'compare' => '>=', 'type' => 'DATETIME' ),
		);
		if ( '' !== $hasta ) {
			$meta_query[] = array( 'key' => self::META_INICIO, 'value' => $hasta, 'compare' => '<=', 'type' => 'DATETIME' );
			$meta_query['relation'] = 'AND';
		}

		$args = array_merge( czuapi_args_publicado(), array(
			'post_type'      => self::CPT,
			'posts_per_page' => 200,
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		if ( $cat ) {
			$args['tax_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'taxonomy' => CZUAPI_Taxonomias::TAX_CATEGORIA,
				'field'    => 'term_id',
				'terms'    => $cat,
			) );
		}

		$out = array();
		foreach ( get_posts( $args ) as $post ) {
			$out[] = $this->formato( $post, false );
		}
		return $out;
	}

	public function detalle( $request ) {
		$post = get_post( (int) $request['id'] );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return CZUAPI_Response::no_encontrado();
		}

		/*
		 * Un evento cargado como ficha tiene mucho más que contar que lo que
		 * entra en la forma de evento —galería, fuentes, artículos vinculados—,
		 * y eso ya lo sirve el detalle del inventario. Se delega en vez de
		 * mantener dos detalles que dicen casi lo mismo y se desincronizan.
		 */
		if ( PROMOTUR_Destinos::CPT === $post->post_type ) {
			if ( 'evento' !== $this->tipo_item( $post->ID ) ) {
				return CZUAPI_Response::no_encontrado();
			}
			return CZUAPI_Inventario::instance()->detalle( $request );
		}

		if ( self::CPT !== $post->post_type ) {
			return CZUAPI_Response::no_encontrado();
		}
		return CZUAPI_Response::with_etag( $this->formato( $post, true ), $request, 180 );
	}

	/**
	 * Una ficha de tipo evento, con la forma de la lista de eventos.
	 *
	 * Los campos son los mismos que los del evento viejo para que el cliente
	 * pinte una sola tarjeta; lo que se agrega es `origen` y `ficha_id`, que es
	 * lo que necesita para saber que el detalle completo está en
	 * `/inventario/{id}`.
	 *
	 * @param WP_Post $post
	 * @return array
	 */
	private function formato_ficha( $post, $idioma = 'es' ) {
		$id  = $post->ID;
		$lat = get_post_meta( $id, '_promotur_lat', true );
		$lng = get_post_meta( $id, '_promotur_lng', true );

		/*
		 * Esto es una FICHA del panel, así que se traduce como cualquier otra.
		 * El evento del CPT viejo —`formato()`, más abajo— no: no pasa por el
		 * panel, así que no hay dónde escribirle una traducción. Los dos
		 * traen igual `idioma` y `traducido`, para que el cliente lea una sola
		 * forma y `traducido: false` le diga cuál es cuál.
		 */
		$i18n = CZUAPI_Idiomas::textos( $id, $idioma );
		$t    = function ( $campo, $porDefecto ) use ( $i18n ) {
			return isset( $i18n['textos'][ $campo ] ) ? $i18n['textos'][ $campo ] : $porDefecto;
		};

		return array(
			'id'        => (int) $id,
			'tipo'      => 'evento',
			'origen'    => 'ficha',
			'ficha_id'  => (int) $id,
			'idioma'    => $i18n['idioma'],
			'traducido' => $i18n['traducido'],
			'titulo'    => $t( 'titulo', get_the_title( $post ) ),
			'inicio'    => czuapi_fecha( (string) get_post_meta( $id, PROMOTUR_Destinos::META_INICIO, true ) ),
			'fin'       => czuapi_fecha( (string) get_post_meta( $id, PROMOTUR_Destinos::META_FIN, true ) ),
			'lugar'     => ( '' === $lat || '' === $lng ) ? null : array(
				'ref_tipo' => 'destino',
				'ref_id'   => (int) $id,
				'nombre'   => $t( 'titulo', get_the_title( $post ) ),
				'lat'      => (float) $lat,
				'lng'      => (float) $lng,
			),
			'costo'     => $t( 'costo', (string) get_post_meta( $id, '_promotur_costo', true ) ),
			'categoria' => czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA, $idioma ),
			'portada'   => czuapi_imagen( (int) get_post_thumbnail_id( $id ) ),
			// Antes leía el gancho, que se sacó de la ficha por redundante.
			// El resumen de tarjeta ahora sale de la descripción, igual que
			// en el evento del CPT viejo (`formato()` más abajo).
			// La ficha no tiene un «resumen» propio: el de la tarjeta sale de
			// la descripción, que sí se traduce.
			'resumen'   => $t( 'descripcion', get_the_excerpt( $post ) ),
		);
	}

	/**
	 * Sitio o evento, sin depender de que el panel esté al día.
	 *
	 * @return string
	 */
	private function tipo_item( $id ) {
		if ( method_exists( 'PROMOTUR_Destinos', 'tipo_item' ) ) {
			return PROMOTUR_Destinos::tipo_item( $id );
		}
		return 'evento' === get_post_meta( $id, '_promotur_tipo_item', true ) ? 'evento' : 'sitio';
	}

	/**
	 * @param WP_Post $post
	 * @param bool    $completo incluye cuerpo del artículo
	 * @return array
	 */
	private function formato( $post, $completo ) {
		$id = $post->ID;

		$out = array(
			'id'        => (int) $id,
			'tipo'      => 'evento',
			'origen'    => 'evento_legado',
			'ficha_id'  => null,
			'titulo'    => get_the_title( $post ),
			'inicio'    => czuapi_fecha( (string) get_post_meta( $id, self::META_INICIO, true ) ),
			'fin'       => czuapi_fecha( (string) get_post_meta( $id, self::META_FIN, true ) ),
			'lugar'     => $this->lugar( $id ),
			'costo'     => (string) get_post_meta( $id, self::META_COSTO, true ),
			'categoria' => czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA ),
			// Pedido por el lado de la app: las tarjetas de evento llevan foto
			// y el payload original no la traía.
			'portada'   => czuapi_imagen( (int) get_post_thumbnail_id( $id ) ),
			'resumen'   => get_the_excerpt( $post ),
			// El CPT viejo no pasa por el panel, así que no hay dónde
			// escribirle una traducción. Las claves vienen igual para que el
			// cliente no tenga dos formas de leer la misma tarjeta.
			'idioma'    => CZUAPI_Idiomas::FALLBACK,
			'traducido' => false,
		);

		if ( $completo ) {
			$out['articulo_html'] = apply_filters( 'the_content', $post->post_content );
			$out['autor']         = czuapi_autor( $id );
			$out['actualizado']   = czuapi_fecha( $post->post_modified_gmt );
		}

		return $out;
	}

	/**
	 * Lugar resuelto: si referencia un destino, hereda sus coordenadas; si no,
	 * usa las propias. El cliente recibe siempre la misma forma.
	 *
	 * @return array|null
	 */
	private function lugar( $id ) {
		$ref = (int) get_post_meta( $id, self::META_LUGAR_ID, true );

		if ( $ref > 0 && 'publish' === get_post_status( $ref ) ) {
			return array(
				'ref_tipo' => 'destino',
				'ref_id'   => $ref,
				'nombre'   => get_the_title( $ref ),
				'lat'      => (float) get_post_meta( $ref, '_promotur_lat', true ),
				'lng'      => (float) get_post_meta( $ref, '_promotur_lng', true ),
			);
		}

		$lat = get_post_meta( $id, self::META_LAT, true );
		$lng = get_post_meta( $id, self::META_LNG, true );
		if ( '' === $lat || '' === $lng ) {
			return null;
		}
		return array(
			'ref_tipo' => 'propio',
			'ref_id'   => null,
			'nombre'   => '',
			'lat'      => (float) $lat,
			'lng'      => (float) $lng,
		);
	}

	/**
	 * Markers de eventos vigentes, para /mapa/markers.
	 *
	 * @return array[]
	 */
	public function markers() {
		$ids = get_posts( array_merge( czuapi_args_publicado(), array(
			'post_type'      => self::CPT,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'key'     => self::META_INICIO,
				'value'   => gmdate( 'Y-m-d H:i:s' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			) ),
		) ) );

		// Ver el comentario equivalente en CZUAPI_Inventario::markers(): sin
		// esto, cada evento del mapa dispara sus propias consultas de meta y
		// de categoría.
		if ( $ids ) {
			update_meta_cache( 'post', $ids );
			update_object_term_cache( $ids, self::CPT );
		}

		$out = array();
		foreach ( $ids as $id ) {
			$lugar = $this->lugar( $id );
			if ( ! $lugar || ! $lugar['lat'] ) { continue; }
			$cat   = czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA );
			$out[] = array(
				'id'        => (int) $id,
				'tipo'      => 'evento',
				'tipo_item' => 'evento',
				'lat'       => (float) $lugar['lat'],
				'lng'       => (float) $lugar['lng'],
				'categoria' => $cat ? $cat['id'] : null,
			);
		}
		return $out;
	}
}
