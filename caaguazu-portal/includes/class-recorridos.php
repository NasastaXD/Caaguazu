<?php
/**
 * CPT "Recorrido": una ruta armada con sitios del inventario turístico.
 *
 * QUÉ ES UN RECORRIDO
 *
 * Una lista ordenada de hasta nueve paradas, donde cada parada es un sitio que
 * ya existe en el inventario —una ficha publicada, no un lugar escrito de
 * nuevo— con un texto propio al lado: la historia, el dato curioso, por qué
 * está ahí y por qué en ese orden. Más audios o videos que acompañan el
 * recorrido, y los artículos que ya se escribieron sobre el tema.
 *
 * EL ORDEN ES EL CONTENIDO
 *
 * Un recorrido no es un conjunto de lugares: es una secuencia. Cambiar el
 * tercero por el quinto cambia el paseo. Por eso el orden se guarda explícito
 * (`orden`, 1..n) en vez de confiar en el orden del array, y por eso el editor
 * tiene botones para subir y bajar cada parada.
 *
 * NUEVE, NI UNA MÁS
 *
 * El tope no es arbitrario: la app manda el recorrido a Google Maps como una
 * ruta con waypoints, y ahí hay un límite duro de nueve puntos intermedios más
 * el destino. Un recorrido de doce paradas se cortaría solo, en silencio y en
 * el teléfono de alguien que ya salió de casa. Se corta acá, donde se puede
 * avisar.
 *
 * SE REGISTRA EN EL PANEL, NO EN LA API
 *
 * Mismo motivo que los artículos (ver class-articulos.php): el recorrido es
 * contenido humano con flujo editorial, y eso vive en el panel. La API lo lee.
 * El `post_type` es el mismo de siempre (`promotur_recorrido`) para no perder
 * los que ya estén cargados, incluidos los que arma la gente desde la app.
 *
 * DOS TIPOS EN EL MISMO CPT
 *
 *   prehecho   lo arma el equipo acá, pasa por revisión y se publica.
 *   usuario    lo arma alguien en la app, es privado y suyo.
 *
 * El panel sólo lista y edita los prehechos: los de usuario son de su dueño y
 * no se tocan desde acá.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Recorridos {

	private static $instance = null;

	const CPT = 'promotur_recorrido';

	/** Mismo meta de dueño que las fichas y los artículos. */
	const OWNER_META = '_caaguazu_owner';

	const META_TIPO      = '_recorrido_tipo';      // prehecho | usuario
	const META_PARADAS   = '_recorrido_paradas';   // array ordenado
	const META_DURACION  = '_recorrido_duracion';
	const META_MEDIOS    = '_recorrido_medios';    // audios y videos del recorrido
	const META_ARTICULOS = '_recorrido_articulos'; // IDs de artículos vinculados
	const META_HISTORIA  = '_recorrido_historia';  // bloque libre heredado de la API
	const META_CUENTA    = '_recorrido_cuenta';    // dueño de los de usuario

	/** Tope de paradas. Ver el porqué en la cabecera del archivo. */
	const MAX_PARADAS = 9;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 9 );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public static function singular() {
		return __( 'Recorrido', 'caaguazu-portal' );
	}

	public static function plural() {
		return __( 'Recorridos', 'caaguazu-portal' );
	}

	public static function seccion() {
		return 'recorridos';
	}

	public static function register_post_type() {
		if ( post_type_exists( self::CPT ) ) {
			return;
		}
		register_post_type( self::CPT, array(
			'labels' => array(
				'name'          => self::plural(),
				'singular_name' => self::singular(),
				'menu_name'     => __( 'Recorridos', 'caaguazu-portal' ),
				'add_new_item'  => __( 'Nuevo recorrido', 'caaguazu-portal' ),
				'edit_item'     => __( 'Editar recorrido', 'caaguazu-portal' ),
				'search_items'  => __( 'Buscar recorridos', 'caaguazu-portal' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'exclude_from_search'=> true,
			// El panel es la única pantalla de edición: la nativa de wp-admin
			// (lista + editor de bloques) queda apagada.
			'show_ui'            => false,
			'show_in_rest'       => false,
			'has_archive'        => false,
			'rewrite'            => false,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
		) );
	}

	/**
	 * Campos propios del recorrido. Título, resumen (excerpt) e introducción
	 * (content) son nativos y los dibuja el editor.
	 *
	 * @return array
	 */
	public static function fields() {
		return array(
			'identidad' => array(
				'label'  => __( 'Identidad', 'caaguazu-portal' ),
				'fields' => array(
					'_recorrido_portada' => array(
						'label' => __( 'Foto de portada', 'caaguazu-portal' ),
						'type'  => 'image',
						'req'   => true,
					),
					self::META_DURACION => array(
						'label' => __( 'Duración estimada', 'caaguazu-portal' ),
						'type'  => 'text',
						'req'   => true,
						'ayuda' => __( 'Cuánto lleva hacerlo entero, con las paradas: «media jornada», «4 h».', 'caaguazu-portal' ),
					),
				),
			),
		);
	}

	/**
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
	 * Lo que un recorrido necesita y no es un campo suelto: una introducción y
	 * por lo menos dos paradas. Con una sola parada no hay recorrido, hay una
	 * ficha.
	 *
	 * @param int $post_id
	 * @return array[]
	 */
	public static function checklist_extra( $post_id ) {
		$cuerpo  = $post_id ? get_post_field( 'post_content', $post_id ) : '';
		$paradas = $post_id ? self::paradas( $post_id ) : array();
		return array(
			array(
				'key'   => 'descripcion',
				'label' => __( 'Introducción del recorrido', 'caaguazu-portal' ),
				'done'  => '' !== trim( wp_strip_all_tags( (string) $cuerpo ) ),
			),
			array(
				'key'   => 'paradas',
				'label' => __( 'Al menos dos paradas', 'caaguazu-portal' ),
				'done'  => count( $paradas ) >= 2,
			),
		);
	}

	public function register_meta() {
		foreach ( array_keys( self::flat_fields() ) as $key ) {
			register_post_meta( self::CPT, $key, array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => function () { return caaguazu_account_can( 'promotor', 'promotur_edit_destino' ); },
			) );
		}
		foreach ( array( self::META_TIPO, self::META_CUENTA, '_promotur_estado', '_promotur_revisor', '_promotur_verificado_en', self::OWNER_META ) as $key ) {
			register_post_meta( self::CPT, $key, array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => function () { return caaguazu_account_can( 'promotor', 'promotur_edit_destino' ); },
			) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Paradas
	 * ------------------------------------------------------------------ */

	/**
	 * Las paradas guardadas, normalizadas y ordenadas.
	 *
	 * Se reordena y se renumera al leer, no sólo al guardar: una fila mal
	 * numerada por una edición vieja no puede dejar el recorrido en un orden
	 * distinto del que se ve en el editor.
	 *
	 * @param int $post_id
	 * @return array[] { orden, ref_tipo, ref_id, texto, media_tipo, media_url }
	 */
	public static function paradas( $post_id ) {
		$crudo = get_post_meta( $post_id, self::META_PARADAS, true );
		if ( ! is_array( $crudo ) ) {
			return array();
		}
		$out = array();
		foreach ( $crudo as $p ) {
			if ( ! is_array( $p ) || empty( $p['ref_id'] ) ) {
				continue;
			}
			$out[] = array(
				'orden'      => isset( $p['orden'] ) ? (int) $p['orden'] : count( $out ) + 1,
				'ref_tipo'   => isset( $p['ref_tipo'] ) ? (string) $p['ref_tipo'] : 'destino',
				'ref_id'     => (int) $p['ref_id'],
				// `nota` es como se llamaba en la primera versión de la API, y
				// hay recorridos de usuario guardados con esa clave. Se sigue
				// leyendo para no perderlos.
				'texto'      => isset( $p['texto'] ) ? (string) $p['texto'] : (string) ( $p['nota'] ?? '' ),
				'media_tipo' => isset( $p['media_tipo'] ) ? (string) $p['media_tipo'] : '',
				'media_url'  => isset( $p['media_url'] ) ? (string) $p['media_url'] : '',
			);
		}
		usort( $out, function ( $a, $b ) {
			return $a['orden'] <=> $b['orden'];
		} );
		$orden = 1;
		foreach ( $out as &$p ) {
			$p['orden'] = $orden++;
		}
		return $out;
	}

	/**
	 * Normaliza lo que manda el editor y lo guarda.
	 *
	 * Tres cosas que se hacen acá y no en el formulario, porque el formulario
	 * se puede saltear: se descartan las referencias que no son un sitio del
	 * inventario, se descartan los duplicados (una parada dos veces es un
	 * error de arrastre, no una intención) y se corta en MAX_PARADAS.
	 *
	 * @param int   $post_id
	 * @param array $crudo filas tal como llegan del POST
	 * @return array[] las paradas efectivamente guardadas
	 */
	public static function guardar_paradas( $post_id, $crudo ) {
		$paradas = array();
		$vistos  = array();

		foreach ( (array) $crudo as $fila ) {
			if ( ! is_array( $fila ) || empty( $fila['ref_id'] ) ) {
				continue;
			}
			$ref_id = (int) $fila['ref_id'];
			if ( isset( $vistos[ $ref_id ] ) ) {
				continue;
			}
			if ( PROMOTUR_Destinos::CPT !== get_post_type( $ref_id ) ) {
				continue;
			}
			$vistos[ $ref_id ] = true;

			$media_url  = isset( $fila['media_url'] ) ? esc_url_raw( trim( (string) $fila['media_url'] ) ) : '';
			$media_tipo = isset( $fila['media_tipo'] ) ? sanitize_key( $fila['media_tipo'] ) : '';
			if ( '' === $media_url ) {
				$media_tipo = '';
			} elseif ( ! in_array( $media_tipo, array( 'audio', 'video' ), true ) ) {
				$media_tipo = 'audio';
			}

			$paradas[] = array(
				'orden'      => count( $paradas ) + 1,
				'ref_tipo'   => 'destino',
				'ref_id'     => $ref_id,
				'texto'      => isset( $fila['texto'] ) ? sanitize_textarea_field( $fila['texto'] ) : '',
				'media_tipo' => $media_tipo,
				'media_url'  => $media_url,
			);

			if ( count( $paradas ) >= self::MAX_PARADAS ) {
				break;
			}
		}

		update_post_meta( $post_id, self::META_PARADAS, $paradas );
		return $paradas;
	}

	/* ---------------------------------------------------------------------
	 * Medios y artículos
	 * ------------------------------------------------------------------ */

	/**
	 * Audios y videos del recorrido entero (los de cada parada van en la
	 * parada).
	 *
	 * Se guardan como URL y no como adjunto: un audio guiado de veinte minutos
	 * no tiene por qué vivir en la mediateca de WordPress, y el equipo ya
	 * publica sus videos en otro lado.
	 *
	 * @param int $post_id
	 * @return array[] { tipo, url, titulo }
	 */
	public static function medios( $post_id ) {
		$crudo = get_post_meta( $post_id, self::META_MEDIOS, true );
		if ( ! is_array( $crudo ) ) {
			return array();
		}
		$out = array();
		foreach ( $crudo as $m ) {
			if ( ! is_array( $m ) || empty( $m['url'] ) ) {
				continue;
			}
			$out[] = array(
				'tipo'   => ( isset( $m['tipo'] ) && 'video' === $m['tipo'] ) ? 'video' : 'audio',
				'url'    => (string) $m['url'],
				'titulo' => isset( $m['titulo'] ) ? (string) $m['titulo'] : '',
			);
		}
		return $out;
	}

	/**
	 * @param int   $post_id
	 * @param array $crudo
	 * @return array[]
	 */
	public static function guardar_medios( $post_id, $crudo ) {
		$medios = array();
		foreach ( (array) $crudo as $fila ) {
			if ( ! is_array( $fila ) ) {
				continue;
			}
			$url = isset( $fila['url'] ) ? esc_url_raw( trim( (string) $fila['url'] ) ) : '';
			if ( '' === $url ) {
				continue;
			}
			$medios[] = array(
				'tipo'   => ( isset( $fila['tipo'] ) && 'video' === $fila['tipo'] ) ? 'video' : 'audio',
				'url'    => $url,
				'titulo' => isset( $fila['titulo'] ) ? sanitize_text_field( $fila['titulo'] ) : '',
			);
		}
		update_post_meta( $post_id, self::META_MEDIOS, $medios );
		return $medios;
	}

	/**
	 * IDs de los artículos vinculados, filtrados a los que todavía existen.
	 *
	 * @param int $post_id
	 * @return int[]
	 */
	public static function articulos( $post_id ) {
		$crudo = get_post_meta( $post_id, self::META_ARTICULOS, true );
		$ids   = is_array( $crudo ) ? array_map( 'intval', $crudo ) : array();
		$out   = array();
		foreach ( $ids as $id ) {
			if ( $id > 0 && PROMOTUR_Articulos::CPT === get_post_type( $id ) ) {
				$out[] = $id;
			}
		}
		return $out;
	}

	/**
	 * @param int   $post_id
	 * @param array $crudo IDs
	 * @return int[]
	 */
	public static function guardar_articulos( $post_id, $crudo ) {
		$ids = array();
		foreach ( (array) $crudo as $id ) {
			$id = (int) $id;
			if ( $id > 0 && PROMOTUR_Articulos::CPT === get_post_type( $id ) && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}
		update_post_meta( $post_id, self::META_ARTICULOS, $ids );
		return $ids;
	}

	/**
	 * ¿Es un recorrido del equipo (y no uno que alguien armó en la app)?
	 *
	 * Los de usuario son privados de su dueño: no se listan ni se editan desde
	 * el panel. Un recorrido sin tipo es de los viejos del equipo, de antes de
	 * que el meta existiera.
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public static function es_prehecho( $post_id ) {
		$tipo = (string) get_post_meta( $post_id, self::META_TIPO, true );
		return '' === $tipo || 'prehecho' === $tipo;
	}
}
