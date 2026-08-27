<?php
/**
 * CPT "Artículo": la nota periodística que lee la app.
 *
 * POR QUÉ LO REGISTRA EL PANEL Y NO LA API DE LA APP
 *
 * Este CPT nació en `caaguazu-app-api`, y ahí estaba al revés: el artículo es
 * contenido humano que escribe, revisa y aprueba el equipo editorial, o sea
 * exactamente lo que hace el panel. La API es un lector. Con el CPT del lado
 * de la API, escribir un artículo obligaba a entrar a wp-admin y el flujo de
 * revisión —que ya existía y funcionaba— no lo alcanzaba.
 *
 * Ahora lo registra el panel, con el mismo `post_type` de siempre
 * (`promotur_articulo`) para no perder ni un artículo ya cargado, y la API se
 * corre: si el panel está activo, no vuelve a registrarlo (ver
 * CZUAPI_Articulos::register_post_type()).
 *
 * LA FORMA DE UN ARTÍCULO
 *
 * Las ocho piezas son las que pidió la redacción, y cada una tiene su lugar:
 *
 *   Ante título   meta   la línea corta de arriba del título
 *   Título        post_title
 *   Foto          featured image + pie de foto
 *   Autor/es      meta   texto libre: firman personas, no cuentas (ver abajo)
 *   Subtítulo     meta
 *   Entradilla    post_excerpt   el arranque que se lee en la tarjeta
 *   Cuerpo        post_content
 *   Fuentes       meta
 *
 * El autor va en un campo de texto y no se deduce de la cuenta: una nota la
 * firman una o varias personas, que no siempre son quien la cargó (un
 * corresponsal, alguien del CEAD, dos personas a la vez). La cuenta que la
 * cargó igual queda registrada aparte, en OWNER_META, porque es la que decide
 * quién puede editarla.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Articulos {

	private static $instance = null;

	const CPT = 'promotur_articulo';

	/** Mismo meta de dueño que las fichas: un solo espacio de IDs de cuenta. */
	const OWNER_META = '_caaguazu_owner';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// El CPT antes que nadie (9), para que la API no lo registre de nuevo.
		// Las taxonomías DESPUÉS de que PROMOTUR_Destinos las cree, que lo hace
		// en la prioridad por defecto: enganchar un artículo a una taxonomía
		// que todavía no existe no falla, no hace nada — y las etiquetas
		// quedarían silenciosamente fuera del artículo.
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 9 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 11 );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/** Cómo se llama esto para la gente. */
	public static function singular() {
		return __( 'Artículo', 'caaguazu-portal' );
	}

	public static function plural() {
		return __( 'Artículos', 'caaguazu-portal' );
	}

	/** Sección del panel donde se lista y se edita. */
	public static function seccion() {
		return 'articulos';
	}

	public static function register_post_type() {
		// Prioridad 9 y esta guarda: si por el orden de carga la API llegó
		// primero, no se pisa su registro (los dos declaran lo mismo, pero
		// registrar dos veces el mismo post_type es un aviso de PHP).
		if ( post_type_exists( self::CPT ) ) {
			return;
		}

		/*
		 * Mismo criterio que la ficha: el artículo se sirve por
		 * /wp-json/czu-app/v1/articulos, no por /articulo/<slug>. Si fuera
		 * público, esa URL la dibujaría el theme del sitio —hoy una página de
		 * obra— y Google indexaría notas que no se ven.
		 *
		 * Ojo, esto es un cambio respecto de cómo lo registraba la API, que lo
		 * dejaba `public => true` con slug /articulo/. Se corrige acá: nadie
		 * publicó todavía una URL de esas, y dejarla abierta es sembrar 404 con
		 * contenido adentro.
		 */
		register_post_type( self::CPT, array(
			'labels' => array(
				'name'          => self::plural(),
				'singular_name' => self::singular(),
				'menu_name'     => __( 'Artículos', 'caaguazu-portal' ),
				'add_new_item'  => __( 'Nuevo artículo', 'caaguazu-portal' ),
				'edit_item'     => __( 'Editar artículo', 'caaguazu-portal' ),
				'search_items'  => __( 'Buscar artículos', 'caaguazu-portal' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'exclude_from_search'=> true,
			'show_ui'            => true,
			'show_in_rest'       => false,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-media-document',
			'menu_position'      => 27,
			'has_archive'        => false,
			'rewrite'            => false,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
		) );
	}

	/**
	 * Categorías y etiquetas: las mismas que las fichas, no unas propias.
	 *
	 * Es una decisión, no una comodidad. La app agrupa y filtra por categoría
	 * en todas sus pantallas; si los artículos tuvieran su propio juego de
	 * categorías, «Paisaje natural» sería dos cosas distintas según de dónde
	 * la mires y no se podría cruzar una nota con el lugar del que habla.
	 */
	public static function register_taxonomies() {
		register_taxonomy_for_object_type( 'promotur_categoria', self::CPT );
		register_taxonomy_for_object_type( 'promotur_etiqueta', self::CPT );
	}

	/**
	 * Los campos del artículo, agrupados como se escribe una nota: primero la
	 * cabeza (lo que se ve en la tarjeta), después el cuerpo, al final de dónde
	 * salió.
	 *
	 * Título, entradilla y cuerpo no están acá: son campos nativos del post
	 * (title, excerpt, content) y los dibuja el editor por su cuenta.
	 *
	 * @return array grupo => [ key => { label, type, req, ayuda } ]
	 */
	public static function fields() {
		return array(
			'cabeza' => array(
				'label'  => __( 'Cabeza', 'caaguazu-portal' ),
				'fields' => array(
					'_articulo_antetitulo' => array(
						'label' => __( 'Ante título', 'caaguazu-portal' ),
						'type'  => 'text',
						'req'   => false,
						'ayuda' => __( 'La línea corta que va arriba del título y lo ubica: «Ruta de la madera», «Semana Santa».', 'caaguazu-portal' ),
					),
					'_articulo_subtitulo' => array(
						'label' => __( 'Subtítulo', 'caaguazu-portal' ),
						'type'  => 'text',
						'req'   => false,
					),
					'_articulo_autores' => array(
						'label' => __( 'Autor / autores', 'caaguazu-portal' ),
						'type'  => 'text',
						'req'   => true,
						'ayuda' => __( 'Quién firma la nota. Si son varios, separalos con comas.', 'caaguazu-portal' ),
					),
				),
			),
			'foto' => array(
				'label'  => __( 'Foto de portada', 'caaguazu-portal' ),
				'fields' => array(
					'_articulo_portada' => array(
						'label' => __( 'Foto', 'caaguazu-portal' ),
						'type'  => 'image',
						'req'   => true,
					),
					'_articulo_pie_portada' => array(
						'label' => __( 'Pie de foto y crédito', 'caaguazu-portal' ),
						'type'  => 'text',
						'req'   => true,
						'ayuda' => __( 'Qué se ve, y de quién es la foto.', 'caaguazu-portal' ),
					),
				),
			),
			'cierre' => array(
				'label'  => __( 'Fuentes', 'caaguazu-portal' ),
				'fields' => array(
					'_articulo_fuentes' => array(
						'label' => __( 'Fuentes', 'caaguazu-portal' ),
						'type'  => 'textarea',
						'req'   => true,
						'ayuda' => __( 'De dónde salió cada dato: una por línea.', 'caaguazu-portal' ),
					),
				),
			),
		);
	}

	/**
	 * Lista plana de las meta keys editables.
	 *
	 * @return array key => def
	 */
	public static function flat_fields() {
		$out = array();
		foreach ( self::fields() as $group ) {
			foreach ( $group['fields'] as $key => $def ) {
				$out[ $key ] = $def;
			}
		}
		return $out;
	}

	/**
	 * Ítems de checklist que no salen de un campo `req`: los tres campos
	 * nativos del post.
	 *
	 * @param int $post_id
	 * @return array[]
	 */
	public static function checklist_extra( $post_id ) {
		$excerpt = $post_id ? get_post_field( 'post_excerpt', $post_id ) : '';
		$cuerpo  = $post_id ? get_post_field( 'post_content', $post_id ) : '';
		return array(
			array(
				'key'   => 'entradilla',
				'label' => __( 'Entradilla', 'caaguazu-portal' ),
				'done'  => '' !== trim( (string) $excerpt ),
			),
			array(
				'key'   => 'descripcion',
				'label' => __( 'Cuerpo', 'caaguazu-portal' ),
				'done'  => '' !== trim( wp_strip_all_tags( (string) $cuerpo ) ),
			),
		);
	}

	public function register_meta() {
		$claves = array_keys( self::flat_fields() );
		$claves[] = '_promotur_estado';
		$claves[] = '_promotur_revisor';
		$claves[] = '_promotur_verificado_en';
		$claves[] = self::OWNER_META;

		foreach ( $claves as $key ) {
			register_post_meta( self::CPT, $key, array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => function () { return caaguazu_account_can( 'promotor', 'promotur_edit_destino' ); },
			) );
		}
	}

	/**
	 * La foto de portada: el meta propio, o la imagen destacada si alguien la
	 * cargó desde wp-admin. Se guardan las dos en paralelo al editar (ver
	 * PROMOTUR_Ajax::guardar_extras_articulo()) para que ninguna de las dos
	 * pantallas vea la nota sin foto.
	 *
	 * @param int $post_id
	 * @return int ID del adjunto, o 0
	 */
	public static function portada_id( $post_id ) {
		$att = (int) get_post_meta( $post_id, '_articulo_portada', true );
		return $att ? $att : (int) get_post_thumbnail_id( $post_id );
	}

	/**
	 * Los autores como lista, tal como se van a mostrar.
	 *
	 * @param int $post_id
	 * @return string[]
	 */
	public static function autores( $post_id ) {
		$crudo = (string) get_post_meta( $post_id, '_articulo_autores', true );
		$out   = array();
		foreach ( preg_split( '/\s*[,;]\s*|\s+y\s+/u', $crudo ) as $nombre ) {
			$nombre = trim( $nombre );
			if ( '' !== $nombre ) {
				$out[] = $nombre;
			}
		}
		return $out;
	}
}
