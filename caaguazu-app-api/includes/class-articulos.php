<?php
/**
 * Endpoints de Artículo.
 *
 * EL CPT YA NO SE REGISTRA ACÁ
 *
 * `promotur_articulo` nació en este plugin y se mudó a `caaguazu-portal`
 * (ver PROMOTUR_Articulos). El motivo es simple: el artículo es contenido
 * humano que se escribe, se revisa y se aprueba, y eso lo hace el panel. Esta
 * capa lo lee y lo sirve, que es lo suyo.
 *
 * El `post_type` no cambió, así que no se perdió ni un artículo. Y queda un
 * registro de respaldo: si esta API corriera sin el panel —o con un panel
 * viejo—, el CPT igual existe y wp-admin lo puede editar. Nunca los dos:
 * `post_type_exists()` decide.
 *
 * EL AUTOR
 *
 * Lo que se publica NO es `post_author` (ver czuapi_autor() en helpers.php), y
 * en los artículos hay una vuelta más: la nota la firma quien la escribió, que
 * no siempre es la cuenta que la cargó. Si el artículo tiene firma escrita, esa
 * gana; si no, se cae a la cuenta dueña.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Articulos {

	private static $instance = null;

	const CPT = 'promotur_articulo';

	const META_PIE_PORTADA  = '_articulo_pie_portada';
	const META_ANTETITULO   = '_articulo_antetitulo';
	const META_SUBTITULO    = '_articulo_subtitulo';
	const META_AUTORES      = '_articulo_autores';
	const META_FUENTES      = '_articulo_fuentes';
	const META_PORTADA      = '_articulo_portada';
	const META_RELACIONADOS = '_articulo_relacionados';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Prioridad 11: después de que el panel registre el suyo (prioridad 9).
		add_action( 'init', array( $this, 'register_post_type' ), 11 );
	}

	public function register_post_type() {
		if ( post_type_exists( self::CPT ) ) {
			return; // lo registró el panel, que es su dueño.
		}
		register_post_type( self::CPT, array(
			'labels' => array(
				'name'          => __( 'Artículos', 'caaguazu-app-api' ),
				'singular_name' => __( 'Artículo', 'caaguazu-app-api' ),
				'menu_name'     => __( 'Artículos (app)', 'caaguazu-app-api' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => false,
			'menu_icon'          => 'dashicons-media-document',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'taxonomies'         => array( CZUAPI_Taxonomias::TAX_CATEGORIA ),
		) );
	}

	public function register_routes() {
		register_rest_route( CZUAPI_NS, '/articulos', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'lista' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'categoria'  => array( 'type' => 'integer' ),
				'etiqueta'   => array( 'type' => 'integer' ),
				'buscar'     => array( 'type' => 'string' ),
				'pagina'     => array( 'type' => 'integer', 'default' => 1 ),
				'por_pagina' => array( 'type' => 'integer', 'default' => 20 ),
			),
		) );

		register_rest_route( CZUAPI_NS, '/articulos/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'detalle' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function lista( $request ) {
		$pagina     = max( 1, (int) $request->get_param( 'pagina' ) );
		$por_pagina = min( 100, max( 1, (int) $request->get_param( 'por_pagina' ) ) );

		$args = array_merge( czuapi_args_publicado(), array(
			'post_type'      => self::CPT,
			'posts_per_page' => $por_pagina,
			'paged'          => $pagina,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$tax_query = array();
		if ( $request->get_param( 'categoria' ) ) {
			$tax_query[] = array(
				'taxonomy' => CZUAPI_Taxonomias::TAX_CATEGORIA,
				'field'    => 'term_id',
				'terms'    => (int) $request->get_param( 'categoria' ),
			);
		}
		// Filtrar por etiqueta, que es lo que hace útil el sistema de tags:
		// las etiquetas son las mismas que las de las fichas, así que una nota
		// y un lugar marcados «con niños» se encuentran entre sí.
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

		/*
		 * `buscar` es texto libre, no por etiqueta: el resumen de artículo no
		 * trae las etiquetas del post —sólo el detalle las tiene, igual que en
		 * inventario— así que filtrar la lista por tag no se puede resolver
		 * acá sin pagar el precio de cargar cada post entero para mirarle las
		 * etiquetas. Busca en título, entradilla y cuerpo, como el buscador
		 * nativo de WordPress.
		 */
		if ( $request->get_param( 'buscar' ) ) {
			$args['s'] = sanitize_text_field( (string) $request->get_param( 'buscar' ) );
		}

		$q     = new WP_Query( $args );
		$items = array();
		foreach ( $q->posts as $post ) {
			$items[] = $this->resumen( $post );
		}

		return CZUAPI_Response::with_etag(
			CZUAPI_Response::paginado( $items, $q->found_posts, $pagina, $por_pagina ),
			$request,
			60
		);
	}

	public function detalle( $request ) {
		$post = get_post( (int) $request['id'] );
		if ( ! $post || self::CPT !== $post->post_type || 'publish' !== $post->post_status ) {
			return CZUAPI_Response::no_encontrado();
		}

		$out = $this->resumen( $post );

		$out['cuerpo_html']  = apply_filters( 'the_content', $post->post_content );
		$out['fuentes']      = $this->fuentes( $post->ID );
		$out['categoria']    = czuapi_primer_termino( $post->ID, CZUAPI_Taxonomias::TAX_CATEGORIA );
		$out['relacionados'] = $this->relacionados( $post->ID );
		$out['actualizado']  = czuapi_fecha( $post->post_modified_gmt );

		return CZUAPI_Response::with_etag( $out, $request, 180 );
	}

	/**
	 * La cabeza de la nota: lo que alcanza para pintar una tarjeta y lo que
	 * encabeza el detalle. Es la misma forma en los dos lados a propósito —
	 * así el cliente puede abrir el detalle mostrando ya lo que tenía en la
	 * lista, sin esperar la respuesta.
	 *
	 * @param WP_Post $post
	 * @return array
	 */
	private function resumen( $post ) {
		$id = (int) $post->ID;
		return array(
			'id'         => $id,
			'antetitulo' => (string) get_post_meta( $id, self::META_ANTETITULO, true ),
			'titulo'     => get_the_title( $post ),
			'subtitulo'  => (string) get_post_meta( $id, self::META_SUBTITULO, true ),
			// Se movió acá desde el detalle: al ser la misma forma en lista y
			// detalle (ver docblock arriba), no había motivo para que sólo el
			// detalle la tuviera. Barata por la misma razón que en inventario:
			// WP_Query ya precargó los términos de toda la página.
			'etiquetas'  => $this->etiquetas( $id ),
			// `entradilla` es el párrafo de arranque escrito por la redacción
			// (el post_excerpt). Se llamaba `bajada` hasta acá: se renombró
			// para hablar el mismo idioma que el panel, donde el campo se
			// llama así y así lo escribe la gente.
			'entradilla' => get_the_excerpt( $post ),
			'portada'    => $this->portada( $id ),
			'autores'    => $this->autores( $id ),
			'publicado'  => czuapi_fecha( $post->post_date_gmt ),
		);
	}

	/**
	 * La portada, con su pie de foto y crédito.
	 *
	 * @return array|null
	 */
	private function portada( $id ) {
		$att = (int) get_post_meta( $id, self::META_PORTADA, true );
		if ( ! $att ) {
			$att = (int) get_post_thumbnail_id( $id );
		}
		return czuapi_imagen( $att, (string) get_post_meta( $id, self::META_PIE_PORTADA, true ) );
	}

	/**
	 * Quién firma. Lista, no cadena: una nota puede tener dos autores y el
	 * cliente tiene que poder mostrarlos como quiera.
	 *
	 * @return array[] { nombre, cuenta }
	 */
	private function autores( $id ) {
		$out = array();

		if ( class_exists( 'PROMOTUR_Articulos' ) && method_exists( 'PROMOTUR_Articulos', 'autores' ) ) {
			foreach ( PROMOTUR_Articulos::autores( $id ) as $nombre ) {
				$out[] = array( 'nombre' => $nombre, 'cuenta' => null );
			}
		}

		if ( $out ) {
			return $out;
		}

		// Sin firma escrita: la cuenta que lo cargó. Nunca `post_author`, que
		// en todo lo creado desde el panel es el usuario de servicio.
		$cuenta = czuapi_autor( $id );
		if ( $cuenta ) {
			$out[] = array( 'nombre' => $cuenta['nombre'], 'cuenta' => $cuenta['id'] );
		}
		return $out;
	}

	/**
	 * Las fuentes, una por línea. Se parten del lado del servidor porque el
	 * campo es un textarea y "una por línea" es la convención que se le pide a
	 * la redacción: dejar que cada cliente la interprete es cómo se terminan
	 * viendo dos fuentes pegadas en un renglón.
	 *
	 * @return string[]
	 */
	private function fuentes( $id ) {
		$crudo = (string) get_post_meta( $id, self::META_FUENTES, true );
		$out   = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $crudo ) as $linea ) {
			$linea = trim( $linea );
			if ( '' !== $linea ) {
				$out[] = $linea;
			}
		}
		return $out;
	}

	/**
	 * @return array[]
	 */
	private function etiquetas( $id ) {
		$terms = get_the_terms( $id, CZUAPI_Taxonomias::TAX_ETIQUETA );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return array();
		}
		return array_map( 'czuapi_termino', $terms );
	}

	/**
	 * Relacionados explícitos si los hay; si no, los más recientes de la misma
	 * categoría. Nunca se devuelve a sí mismo.
	 *
	 * @return array[]
	 */
	private function relacionados( $id ) {
		$raw = get_post_meta( $id, self::META_RELACIONADOS, true );
		$ids = is_array( $raw ) ? $raw : array_filter( array_map( 'intval', explode( ',', (string) $raw ) ) );

		$args = array(
			'post_type'      => self::CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'post__not_in'   => array( (int) $id ),
		);

		if ( $ids ) {
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
		} else {
			$cat = czuapi_primer_termino( $id, CZUAPI_Taxonomias::TAX_CATEGORIA );
			if ( ! $cat ) {
				return array();
			}
			$args['tax_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'taxonomy' => CZUAPI_Taxonomias::TAX_CATEGORIA,
				'field'    => 'term_id',
				'terms'    => $cat['id'],
			) );
		}

		$out = array();
		foreach ( get_posts( $args ) as $p ) {
			$out[] = array(
				'id'      => (int) $p->ID,
				'titulo'  => get_the_title( $p ),
				'portada' => czuapi_imagen( (int) get_post_thumbnail_id( $p->ID ), '', 'medium' ),
			);
		}
		return $out;
	}
}
