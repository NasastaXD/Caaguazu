<?php
/**
 * Inventario de atractivos: lista, detalle y markers del mapa.
 *
 * Lee del CPT `promotur_destino` de caaguazu-portal. No duplica el modelo ni
 * reimplementa la visibilidad: el estado editorial manda, y solo `publicado`
 * llega a `post_status = publish`.
 *
 * Tres endpoints y no uno porque las tres pantallas necesitan cosas muy
 * distintas: el mapa quiere miles de pines mínimos, la lista quiere lo justo
 * para pintar una tarjeta, y el detalle quiere todo. Servir el detalle
 * completo a las tres es lo que hace que una lista de 128 fichas descargue
 * megabytes para mostrar foto, nombre y precio.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Inventario {

	private static $instance = null;

	/** Meta de rango de precio: entero 0–4 (0 = gratis). Ver nota abajo. */
	const META_RANGO_PRECIO = '_promotur_rango_precio';

	/** Meta con los IDs de artículos relacionados. */
	const META_ARTICULOS = '_promotur_articulos_rel';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function register_routes() {
		register_rest_route( CZUAPI_NS, '/inventario', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'lista' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'categoria'  => array( 'type' => 'integer' ),
				'etiqueta'   => array( 'type' => 'integer' ),
				'bbox'       => array( 'type' => 'string' ),  // "minLng,minLat,maxLng,maxLat"
				'buscar'     => array( 'type' => 'string' ),
				'tipo_item'  => array( 'type' => 'string' ),  // sitio | evento
				'pagina'     => array( 'type' => 'integer', 'default' => 1 ),
				'por_pagina' => array( 'type' => 'integer', 'default' => 20 ),
			),
		) );

		register_rest_route( CZUAPI_NS, '/inventario/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'detalle' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( CZUAPI_NS, '/mapa/markers', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'markers' ),
			'permission_callback' => '__return_true',
		) );
	}

	/* --------------------------------------------------------------------- */

	public function lista( $request ) {
		$pagina     = max( 1, (int) $request->get_param( 'pagina' ) );
		$por_pagina = min( 100, max( 1, (int) $request->get_param( 'por_pagina' ) ) );

		$args = array_merge( czuapi_args_publicado(), array(
			'post_type'      => PROMOTUR_Destinos::CPT,
			'posts_per_page' => $por_pagina,
			'paged'          => $pagina,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$tax_query = array();
		if ( $request->get_param( 'categoria' ) ) {
			$tax_query[] = array(
				'taxonomy' => CZUAPI_Taxonomias::TAX_CATEGORIA,
				'field'    => 'term_id',
				'terms'    => (int) $request->get_param( 'categoria' ),
			);
		}
		// Filtrar por etiqueta exacta (un id de término, no texto): esto sí es
		// "buscar por tag" de verdad, y no paga el precio de `buscar` — un
		// tax_query es tan barato acá como el de categoría, arriba.
		if ( $request->get_param( 'etiqueta' ) ) {
			$tax_query[] = array(
				'taxonomy' => CZUAPI_Taxonomias::TAX_ETIQUETA,
				'field'    => 'term_id',
				'terms'    => (int) $request->get_param( 'etiqueta' ),
			);
		}
		if ( $tax_query ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		// `buscar` sigue siendo texto libre sobre título y cuerpo, como el
		// buscador nativo de WordPress: no matchea contra el nombre de una
		// etiqueta. Para eso está `etiqueta` arriba, con el id exacto — el
		// cliente lo resuelve mostrando chips de `GET /etiquetas`.
		if ( $request->get_param( 'buscar' ) ) {
			$args['s'] = sanitize_text_field( (string) $request->get_param( 'buscar' ) );
		}

		/*
		 * Un solo `meta_query` armado en un lugar: el filtro por tipo y el
		 * recuadro del mapa pueden llegar juntos, y asignar cada uno por su lado
		 * hacía que el segundo pisara al primero sin que nadie se enterara.
		 */
		$meta_query = array();

		$tipo_item = sanitize_key( (string) $request->get_param( 'tipo_item' ) );
		if ( in_array( $tipo_item, array( 'sitio', 'evento' ), true ) ) {
			/*
			 * «sitio» incluye las fichas sin el meta cargado: son las de antes
			 * de que el tipo existiera, y todas eran sitios. Filtrar por
			 * igualdad estricta las dejaría afuera de su propia colección.
			 */
			$meta_query[] = ( 'evento' === $tipo_item )
				? array( 'key' => PROMOTUR_Destinos::META_TIPO_ITEM, 'value' => 'evento' )
				: array(
					'relation' => 'OR',
					array( 'key' => PROMOTUR_Destinos::META_TIPO_ITEM, 'value' => 'evento', 'compare' => '!=' ),
					array( 'key' => PROMOTUR_Destinos::META_TIPO_ITEM, 'compare' => 'NOT EXISTS' ),
				);
		}

		$bbox = $this->parse_bbox( (string) $request->get_param( 'bbox' ) );
		if ( $bbox ) {
			$meta_query[] = array( 'key' => '_promotur_lat', 'value' => array( $bbox['min_lat'], $bbox['max_lat'] ), 'type' => 'DECIMAL(10,6)', 'compare' => 'BETWEEN' );
			$meta_query[] = array( 'key' => '_promotur_lng', 'value' => array( $bbox['min_lng'], $bbox['max_lng'] ), 'type' => 'DECIMAL(10,6)', 'compare' => 'BETWEEN' );
		}

		if ( $meta_query ) {
			$meta_query['relation'] = 'AND';
			$args['meta_query']     = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		$q     = new WP_Query( $args );
		$items = array();
		foreach ( $q->posts as $post ) {
			$items[] = $this->item_lista( $post );
		}

		// ETag corto: la lista cambia cada vez que se publica o se despublica
		// algo, así que no conviene guardarla mucho, pero sí ahorra el cuerpo
		// entero cuando el cliente pide de nuevo la misma página sin cambios.
		return CZUAPI_Response::with_etag(
			CZUAPI_Response::paginado( $items, $q->found_posts, $pagina, $por_pagina ),
			$request,
			60
		);
	}

	/**
	 * Elemento de lista: solo lo que pinta una tarjeta.
	 *
	 * @param WP_Post $post
	 * @return array
	 */
	private function item_lista( $post ) {
		$id = $post->ID;
		return array(
			'id'              => (int) $id,
			'tipo'            => 'destino',
			// Sitio o evento. `tipo` sigue diciendo de qué colección salió
			// esto —y vale `destino` para los dos— mientras que `tipo_item`
			// dice qué es. Se separan a propósito: un cliente que ya filtraba
			// por `tipo` no cambia de comportamiento al aparecer los eventos.
			'tipo_item'       => $this->tipo_item( $id ),
			'fechas'          => $this->fechas( $id ),
			'titulo'          => get_the_title( $post ),
			'categoria'       => czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA ),
			// Se suma acá —antes sólo la traía el detalle— para que un chip de
			// tag en la tarjeta de lista no obligue a pedir el detalle antes
			// de poder mostrarlo. Es barata: WP_Query ya precargó los términos
			// de toda la página en una sola consulta, así que esto no agrega
			// ninguna.
			'etiquetas'       => $this->etiquetas( $id ),
			'coordenadas'     => $this->coordenadas( $id ),
			'google_maps'     => $this->google_maps( $id ),
			'portada'         => $this->portada( $id ),
			'rango_precio'    => $this->rango_precio( $id ),
			'horario_resumen' => (string) get_post_meta( $id, '_promotur_horario', true ),
			'actualizado'     => czuapi_fecha( $post->post_modified_gmt ),
		);
	}

	public function detalle( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( ! $post || PROMOTUR_Destinos::CPT !== $post->post_type || 'publish' !== $post->post_status ) {
			return CZUAPI_Response::no_encontrado();
		}

		$m = function ( $key ) use ( $id ) {
			return (string) get_post_meta( $id, $key, true );
		};

		// El detalle pesa más que un ítem de lista —galería, historia, artículos
		// relacionados— y cambia menos seguido: una ficha publicada se edita de
		// vez en cuando, no todo el tiempo. Un ETag más largo que el de la lista
		// evita que la app la vuelva a bajar entera cada vez que la abre.
		return CZUAPI_Response::with_etag( array(
			'id'          => $id,
			'tipo'        => 'destino',
			'tipo_item'   => $this->tipo_item( $id ),
			'fechas'      => $this->fechas( $id ),
			'titulo'      => get_the_title( $post ),
			'categoria'   => czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA ),
			'etiquetas'   => $this->etiquetas( $id ),
			'coordenadas' => $this->coordenadas( $id ),
			// El enlace de Google Maps es ahora el modo principal de cargar la
			// ubicación en el panel, y el que la app usa para llevar a la
			// gente hasta el lugar. Viene siempre que haya ubicación: si el
			// promotor cargó sólo coordenadas, el panel arma el enlace con el
			// pin, así el cliente tiene un solo camino y no dos.
			'google_maps' => $this->google_maps( $id ),
			'portada'     => $this->portada( $id ),
			'galeria'     => $this->galeria( $id ),
			'video'       => $m( '_promotur_video' ) ? $m( '_promotur_video' ) : null,
			/*
			 * `practicos` y `acceso` adelgazaron: se fueron `duracion`,
			 * `servicios`, `temporada`, `como_llegar`, `referencia`, y —en
			 * esta misma poda— `gancho` y `accesibilidad`.
			 *
			 * No es una poda de la API sino del modelo: esos campos salieron
			 * de la ficha porque no le hacían falta a la app. Cómo llegar lo
			 * resuelve `google_maps`, que es lo que la app abre de todos
			 * modos; el gancho no decía nada que el título y la portada no
			 * dijeran ya, y la accesibilidad se llenaba con frases sueltas sin
			 * ningún criterio común.
			 *
			 * Las claves desaparecen del objeto en vez de venir vacías: una
			 * clave con cadena vacía se sigue pintando como una fila en blanco
			 * en la ficha, y eso es peor que no estar.
			 */
			'practicos'   => array(
				'horario'      => $m( '_promotur_horario' ),
				'costo'        => $m( '_promotur_costo' ),
				'rango_precio' => $this->rango_precio( $id ),
				'contacto'     => $m( '_promotur_contacto' ),
			),
			'acceso'      => array(
				'estado_camino' => $m( '_promotur_estado_camino' ),
			),
			// Antes viajaba como `articulo_html` —el nombre se copió sin
			// pensar de la respuesta de Artículos al armar esta, y una ficha
			// no es un artículo—. La app no la estaba levantando: buscaba
			// `descripcion` y esa clave nunca existió acá.
			'descripcion'            => apply_filters( 'the_content', $post->post_content ),
			'articulos_relacionados' => $this->articulos_relacionados( $id ),
			'fuentes'                => $m( '_promotur_fuentes' ),
			'autor'                  => czuapi_autor( $id ),
			'actualizado'            => czuapi_fecha( $post->post_modified_gmt ),
		), $request, 180 );
	}

	/**
	 * Markers: payload deliberadamente mínimo.
	 *
	 * Va separado de /inventario porque el mapa carga TODO de una y no
	 * paginado: sumarle un solo campo de texto acá se multiplica por la
	 * cantidad de pines.
	 */
	public function markers( $request ) {
		$out = array();

		// Destinos.
		$destinos = get_posts( array_merge( czuapi_args_publicado(), array(
			'post_type'      => PROMOTUR_Destinos::CPT,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) ) );
		/*
		 * `fields => 'ids'` es justo lo que hace liviana esta consulta, pero
		 * tiene un costo escondido: al pedir sólo IDs, WP_Query se salta el
		 * precargado de metas y términos que hace con posts completos. Sin
		 * este precargado, cada `get_post_meta()`/`get_the_terms()` del loop
		 * de abajo dispara su propia consulta — con miles de sitios, miles de
		 * consultas en una sola respuesta. Precargar acá, una vez, es la
		 * diferencia entre dos consultas y dos mil.
		 */
		if ( $destinos ) {
			update_meta_cache( 'post', $destinos );
			update_object_term_cache( $destinos, PROMOTUR_Destinos::CPT );
		}
		foreach ( $destinos as $id ) {
			$coord = $this->coordenadas( $id );
			if ( ! $coord ) { continue; }
			$cat     = czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA );
			$out[]   = array(
				'id'        => (int) $id,
				'tipo'      => 'destino',
				// El pin de un evento se dibuja distinto al de un sitio, y el
				// mapa no puede pedir el detalle de cada uno para saber cuál es.
				'tipo_item' => $this->tipo_item( $id ),
				'lat'       => $coord['lat'],
				'lng'       => $coord['lng'],
				'categoria' => $cat ? $cat['id'] : null,
			);
		}

		// Eventos con lugar propio o heredado de su destino.
		foreach ( CZUAPI_Eventos::instance()->markers() as $marker ) {
			$out[] = $marker;
		}

		return CZUAPI_Response::with_etag( $out, $request, 120 );
	}

	/* --------------------------------------------------------------------- */
	/*  Piezas                                                                */
	/* --------------------------------------------------------------------- */

	/**
	 * Sitio o evento.
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
	 * Cuándo pasa, si es un evento. `null` en un sitio: un sitio no tiene
	 * fechas, y devolver un objeto con dos nulos adentro obligaría al cliente a
	 * mirar dos niveles para saber lo mismo.
	 *
	 * @return array|null { inicio, fin, en_curso, terminado }
	 */
	private function fechas( $id ) {
		if ( 'evento' !== $this->tipo_item( $id ) ) {
			return null;
		}
		$inicio = (string) get_post_meta( $id, '_promotur_evento_inicio', true );
		$fin    = (string) get_post_meta( $id, '_promotur_evento_fin', true );
		if ( '' === $inicio ) {
			return null;
		}

		// Un evento sin fecha de fin dura ese día: es lo que la gente quiere
		// decir cuando carga sólo el inicio, y deja que «terminado» signifique
		// algo en vez de ser siempre falso.
		$ts_inicio = strtotime( $inicio . ' UTC' );
		$ts_fin    = '' !== $fin ? strtotime( $fin . ' UTC' ) : strtotime( gmdate( 'Y-m-d 23:59:59', $ts_inicio ) . ' UTC' );
		$ahora     = time();

		return array(
			'inicio'    => czuapi_fecha( $inicio ),
			'fin'       => '' !== $fin ? czuapi_fecha( $fin ) : null,
			'en_curso'  => ( $ahora >= $ts_inicio && $ahora <= $ts_fin ),
			'terminado' => ( $ahora > $ts_fin ),
		);
	}

	/**
	 * El enlace de Google Maps de una ficha, o null.
	 *
	 * Se delega en el panel (`PROMOTUR_Destinos::maps_url()`) y no se
	 * reimplementa acá: el panel es el que sabe si el promotor pegó un enlace
	 * o cargó coordenadas, y cuál gana. El fallback existe por si esta capa
	 * corre contra una versión del panel anterior a ese método — la API no
	 * puede caerse porque el otro plugin todavía no se actualizó.
	 *
	 * @return string|null
	 */
	private function google_maps( $id ) {
		if ( method_exists( 'PROMOTUR_Destinos', 'maps_url' ) ) {
			$url = PROMOTUR_Destinos::maps_url( $id );
			return $url ? $url : null;
		}
		$coord = $this->coordenadas( $id );
		if ( ! $coord ) {
			return null;
		}
		return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $coord['lat'] . ',' . $coord['lng'] );
	}

	/**
	 * @return array|null { lat, lng }
	 */
	private function coordenadas( $id ) {
		$lat = get_post_meta( $id, '_promotur_lat', true );
		$lng = get_post_meta( $id, '_promotur_lng', true );
		if ( '' === $lat || '' === $lng || null === $lat || null === $lng ) {
			return null;
		}
		return array( 'lat' => (float) $lat, 'lng' => (float) $lng );
	}

	private function portada( $id ) {
		$att = (int) get_post_meta( $id, '_promotur_portada', true );
		if ( ! $att ) {
			$att = (int) get_post_thumbnail_id( $id );
		}
		return czuapi_imagen( $att, (string) get_post_meta( $id, '_promotur_credito_fotos', true ) );
	}

	private function galeria( $id ) {
		$raw = get_post_meta( $id, '_promotur_galeria', true );
		$ids = is_array( $raw ) ? $raw : array_filter( array_map( 'intval', explode( ',', (string) $raw ) ) );
		$out = array();
		foreach ( $ids as $att ) {
			$img = czuapi_imagen( (int) $att );
			if ( $img ) { $out[] = $img; }
		}
		return $out;
	}

	private function etiquetas( $id ) {
		$terms = get_the_terms( $id, CZUAPI_Taxonomias::TAX_ETIQUETA );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return array();
		}
		return array_map( 'czuapi_termino', $terms );
	}

	/**
	 * Rango de precio: entero 0–4, o null si el promotor no lo cargó.
	 *
	 * Existe además del texto libre `_promotur_costo`, no en su reemplazo: el
	 * número permite filtrar y pintar el indicador de la tarjeta, y el texto
	 * dice cosas que un número no ("entrada libre, estacionamiento 5.000 Gs").
	 * Es una decisión editorial del promotor, no algo calculable.
	 *
	 * @return int|null
	 */
	private function rango_precio( $id ) {
		$v = get_post_meta( $id, self::META_RANGO_PRECIO, true );
		if ( '' === $v || null === $v ) {
			return null;
		}
		return max( 0, min( 4, (int) $v ) );
	}

	/**
	 * Artículos relacionados, con lo mínimo para pintar la tarjeta y navegar.
	 *
	 * @return array[]
	 */
	private function articulos_relacionados( $id ) {
		$raw = get_post_meta( $id, self::META_ARTICULOS, true );
		$ids = is_array( $raw ) ? $raw : array_filter( array_map( 'intval', explode( ',', (string) $raw ) ) );
		if ( ! $ids ) {
			return array();
		}

		$posts = get_posts( array(
			'post_type'      => CZUAPI_Articulos::CPT,
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => 12,
			'post_status'    => 'publish',
		) );

		$out = array();
		foreach ( $posts as $p ) {
			$out[] = array(
				'id'      => (int) $p->ID,
				'titulo'  => get_the_title( $p ),
				'portada' => czuapi_imagen( (int) get_post_thumbnail_id( $p->ID ), '', 'medium' ),
			);
		}
		return $out;
	}

	/**
	 * "minLng,minLat,maxLng,maxLat" → array, o null si no parsea.
	 *
	 * @return array|null
	 */
	private function parse_bbox( $bbox ) {
		if ( ! $bbox ) {
			return null;
		}
		$p = array_map( 'trim', explode( ',', $bbox ) );
		if ( count( $p ) !== 4 ) {
			return null;
		}
		foreach ( $p as $v ) {
			if ( ! is_numeric( $v ) ) { return null; }
		}
		return array(
			'min_lng' => (float) $p[0],
			'min_lat' => (float) $p[1],
			'max_lng' => (float) $p[2],
			'max_lat' => (float) $p[3],
		);
	}
}
