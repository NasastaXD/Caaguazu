<?php
/**
 * Las versiones en otro idioma de una ficha, un artículo o un recorrido.
 *
 * QUÉ SE TRADUCE, Y QUÉ NO
 *
 * La app se ve en varios idiomas; el panel no. Quien carga contenido escribe
 * en castellano y sigue escribiendo en castellano — el español es el original
 * y nunca se toca desde acá. Una traducción es una capa ENCIMA: si falta, la
 * app sirve el original, y nada se rompe por no estar traducido.
 *
 * De cada pieza se traduce solamente lo que una persona lee: títulos, textos,
 * el horario y el costo escritos con palabras. NO se traducen los datos que
 * son datos —coordenadas, enlaces, fechas, precios como número, nombres de
 * quien firma, fuentes—, ni las etiquetas ni las categorías: esas son del
 * sistema y se traducen una sola vez en `/strings`, no una vez por ficha.
 *
 * POR QUÉ UNA LISTA DECLARATIVA Y NO UN CAMPO POR IDIOMA
 *
 * Los campos traducibles se declaran una vez, acá, y de esa lista salen las
 * CUATRO cosas que tienen que estar de acuerdo: el formulario del panel, el
 * archivo que se exporta, el importador que lo lee de vuelta, y lo que la API
 * le sirve a la app. Cuando esas cuatro se escriben por separado, se separan:
 * aparece un campo en el formulario que el exportador no conoce, o la API
 * sirve traducido algo que nadie podía traducir. Con una sola lista, agregar
 * un campo es agregar un renglón.
 *
 * Lo mismo vale para los idiomas: sumar el guaraní es agregarlo a `idiomas()`.
 * Todo lo demás —pantalla, archivo, API— ya lo recorre.
 *
 * DÓNDE VIVE
 *
 * Un meta por idioma en el mismo post: `_promotur_i18n_en` es un array
 * `clave => texto`. Uno por idioma y no uno por campo para que exportar,
 * importar y borrar un idioma sean una sola operación, y para que sumar un
 * idioma no toque ni una fila de los que ya están.
 *
 * Adentro viaja además `_origen`: el `post_modified_gmt` del original en el
 * momento en que se guardó esa traducción. Es lo que permite avisar «esto se
 * tradujo, pero después cambió el castellano» — sin eso, una traducción vieja
 * y una al día se ven exactamente igual, y la app serviría texto que ya no
 * dice lo que dice el original.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Traducciones {

	/** Prefijo del meta donde vive cada idioma. */
	const META_PREFIJO = '_promotur_i18n_';

	/** Capability: traducir es cosa de profesores. */
	const CAP = 'promotur_traducir';

	/** Versión del formato del archivo de export/import. */
	const FORMATO = 'caaguazu-traduccion/1';

	/**
	 * El idioma en el que se escribe el contenido. No es un idioma más: es el
	 * original, no se guarda como traducción y no se puede borrar.
	 */
	const ORIGEN = 'es';

	/**
	 * Los idiomas a los que se puede traducir.
	 *
	 * El guaraní está previsto y todavía no pedido: sumarlo es agregar el
	 * renglón. Se filtra para poder hacerlo sin tocar el plugin.
	 *
	 * @return array clave => nombre en castellano (el panel es en castellano)
	 */
	public static function idiomas() {
		/**
		 * Idiomas a los que se traduce el contenido.
		 *
		 * @param array $idiomas clave ISO 639-1 => nombre
		 */
		return apply_filters( 'promotur_idiomas_traduccion', array(
			'en' => __( 'Inglés', 'caaguazu-portal' ),
			'pt' => __( 'Portugués', 'caaguazu-portal' ),
		) );
	}

	/**
	 * Todos los idiomas que la API puede servir, con el original adelante.
	 *
	 * @return string[]
	 */
	public static function idiomas_api() {
		return array_merge( array( self::ORIGEN ), array_keys( self::idiomas() ) );
	}

	/**
	 * @param string $locale
	 * @return bool
	 */
	public static function idioma_valido( $locale ) {
		return array_key_exists( (string) $locale, self::idiomas() );
	}

	/**
	 * @param string $locale
	 * @return string nombre, o la clave si no se conoce
	 */
	public static function idioma_label( $locale ) {
		$idiomas = self::idiomas();
		return isset( $idiomas[ $locale ] ) ? $idiomas[ $locale ] : (string) $locale;
	}

	/* ---------------------------------------------------------------------
	 * Qué se traduce de cada tipo
	 * ------------------------------------------------------------------- */

	/**
	 * Los campos traducibles de un tipo de contenido.
	 *
	 * Cada uno declara:
	 *   clave    con la que viaja en el meta, en el archivo y en la API
	 *   label    lo que se ve en el panel
	 *   formato  'texto' (un renglón) | 'parrafos' (varios) — sirve para
	 *            elegir el control del formulario y para avisarle a quien
	 *            traduce que los renglones en blanco separan párrafos
	 *   que_es   la explicación que va DENTRO del archivo exportado, para
	 *            que quien traduzca —persona o modelo— sepa qué está
	 *            traduciendo sin tener el panel a la vista
	 *   origen   de dónde sale el texto original: 'title', 'content',
	 *            'excerpt' o una meta key
	 *
	 * @param string $tipo destino|articulo|recorrido
	 * @return array[] clave => def
	 */
	public static function campos( $tipo ) {
		$mapa = array(
			'destino' => array(
				'titulo' => array(
					'label'   => __( 'Título', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => 'title',
					'que_es'  => 'El nombre del lugar o del evento, tal como se ve en la tarjeta y encabezando la ficha.',
				),
				'descripcion' => array(
					'label'   => __( 'Descripción', 'caaguazu-portal' ),
					'formato' => 'parrafos',
					'origen'  => 'content',
					'que_es'  => 'El texto que describe el lugar. Un renglón en blanco separa párrafos y hay que conservarlos.',
				),
				'horario' => array(
					'label'   => __( 'Horario', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => '_promotur_horario',
					'que_es'  => 'Cuándo se puede visitar, escrito con palabras («todos los días de 8 a 17», «sólo fines de semana»).',
				),
				'costo' => array(
					'label'   => __( 'Costo / entrada', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => '_promotur_costo',
					'que_es'  => 'Cuánto sale entrar, escrito con palabras. Los montos en guaraníes NO se convierten ni se cambian: se dejan igual.',
				),
			),

			'articulo' => array(
				'antetitulo' => array(
					'label'   => __( 'Ante título', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => '_articulo_antetitulo',
					'que_es'  => 'La línea corta que va arriba del título y ubica la nota.',
				),
				'titulo' => array(
					'label'   => __( 'Título', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => 'title',
					'que_es'  => 'El título de la nota.',
				),
				'subtitulo' => array(
					'label'   => __( 'Subtítulo', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => '_articulo_subtitulo',
					'que_es'  => 'La línea que amplía el título.',
				),
				'entradilla' => array(
					'label'   => __( 'Entradilla', 'caaguazu-portal' ),
					'formato' => 'parrafos',
					'origen'  => 'excerpt',
					'que_es'  => 'El párrafo de arranque: lo que se lee en la tarjeta y decide si alguien entra a la nota.',
				),
				'cuerpo' => array(
					'label'   => __( 'Cuerpo', 'caaguazu-portal' ),
					'formato' => 'parrafos',
					'origen'  => 'content',
					'que_es'  => 'La nota entera. Un renglón en blanco separa párrafos y hay que conservarlos, uno por uno.',
				),
			),

			'recorrido' => array(
				'titulo' => array(
					'label'   => __( 'Título', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => 'title',
					'que_es'  => 'El nombre del recorrido.',
				),
				'resumen' => array(
					'label'   => __( 'Resumen', 'caaguazu-portal' ),
					'formato' => 'parrafos',
					'origen'  => 'excerpt',
					'que_es'  => 'De qué se trata el recorrido, en pocas líneas: es lo que se lee en la tarjeta.',
				),
				'cuerpo' => array(
					'label'   => __( 'Introducción', 'caaguazu-portal' ),
					'formato' => 'parrafos',
					'origen'  => 'content',
					'que_es'  => 'El texto que presenta el recorrido antes de la primera parada.',
				),
				'duracion_estimada' => array(
					'label'   => __( 'Duración estimada', 'caaguazu-portal' ),
					'formato' => 'texto',
					'origen'  => '_recorrido_duracion',
					'que_es'  => 'Cuánto lleva hacerlo entero («media jornada», «4 h»).',
				),
			),
		);

		$campos = isset( $mapa[ $tipo ] ) ? $mapa[ $tipo ] : array();

		/**
		 * Permite sumar o sacar campos traducibles de un tipo.
		 *
		 * @param array  $campos
		 * @param string $tipo
		 */
		return apply_filters( 'promotur_campos_traducibles', $campos, $tipo );
	}

	/**
	 * Los campos traducibles de un post concreto.
	 *
	 * Se separa de `campos()` porque un recorrido suma un campo POR PARADA —el
	 * texto que explica esa parada—, y cuántas paradas tiene sólo se sabe
	 * mirando el post. El título de cada parada no está acá a propósito: sale
	 * de la ficha a la que apunta, y esa ficha se traduce sola.
	 *
	 * @param int $post_id
	 * @return array[] clave => def
	 */
	public static function campos_de( $post_id ) {
		$tipo = PROMOTUR_Editorial::tipo_de( $post_id );
		if ( ! $tipo ) {
			return array();
		}
		$campos = self::campos( $tipo );

		if ( 'recorrido' === $tipo && class_exists( 'PROMOTUR_Recorridos' ) ) {
			$paradas = PROMOTUR_Recorridos::paradas( $post_id );
			$n       = 0;
			foreach ( $paradas as $parada ) {
				$texto = isset( $parada['texto'] ) ? (string) $parada['texto'] : '';
				$n++;
				if ( '' === trim( $texto ) ) {
					continue; // una parada sin nota no tiene nada que traducir.
				}
				$campos[ 'parada.' . ( $n - 1 ) . '.texto' ] = array(
					/* translators: %d = número de parada */
					'label'   => sprintf( __( 'Parada %d — texto', 'caaguazu-portal' ), $n ),
					'formato' => 'parrafos',
					'origen'  => 'parada',
					'indice'  => $n - 1,
					'que_es'  => sprintf(
						'Lo que se cuenta en la parada %d del recorrido. El nombre del lugar NO va acá: sale de su propia ficha, que se traduce aparte.',
						$n
					),
				);
			}
		}

		return $campos;
	}

	/* ---------------------------------------------------------------------
	 * El texto original
	 * ------------------------------------------------------------------- */

	/**
	 * El texto en castellano de un campo traducible.
	 *
	 * @param int    $post_id
	 * @param string $clave
	 * @param array  $def
	 * @return string
	 */
	public static function original( $post_id, $clave, $def ) {
		$origen = isset( $def['origen'] ) ? $def['origen'] : '';
		switch ( $origen ) {
			case 'title':
				return (string) get_post_field( 'post_title', $post_id );
			case 'content':
				return (string) get_post_field( 'post_content', $post_id );
			case 'excerpt':
				return (string) get_post_field( 'post_excerpt', $post_id );
			case 'parada':
				$paradas = class_exists( 'PROMOTUR_Recorridos' ) ? PROMOTUR_Recorridos::paradas( $post_id ) : array();
				$i       = isset( $def['indice'] ) ? (int) $def['indice'] : -1;
				return isset( $paradas[ $i ]['texto'] ) ? (string) $paradas[ $i ]['texto'] : '';
			default:
				return (string) get_post_meta( $post_id, $origen, true );
		}
	}

	/* ---------------------------------------------------------------------
	 * Leer y escribir
	 * ------------------------------------------------------------------- */

	/**
	 * La traducción guardada de un post a un idioma.
	 *
	 * @param int    $post_id
	 * @param string $locale
	 * @return array clave => texto (sin las claves internas)
	 */
	public static function leer( $post_id, $locale ) {
		if ( ! self::idioma_valido( $locale ) ) {
			return array();
		}
		$datos = get_post_meta( (int) $post_id, self::META_PREFIJO . $locale, true );
		if ( ! is_array( $datos ) ) {
			return array();
		}
		unset( $datos['_origen'] );
		return $datos;
	}

	/**
	 * El texto de un campo en un idioma, o '' si no está traducido.
	 *
	 * @param int    $post_id
	 * @param string $clave
	 * @param string $locale
	 * @return string
	 */
	public static function texto( $post_id, $clave, $locale ) {
		$datos = self::leer( $post_id, $locale );
		return isset( $datos[ $clave ] ) ? (string) $datos[ $clave ] : '';
	}

	/**
	 * Guarda la traducción de un post a un idioma.
	 *
	 * Sólo se guardan los campos con texto: un campo vacío no es «traducido a
	 * la cadena vacía», es «sin traducir», y la diferencia importa —la API
	 * sirve el original justamente cuando falta—.
	 *
	 * Los campos que no existen para ese tipo se descartan en silencio: un
	 * archivo exportado antes de que se podara un campo tiene que poder
	 * importarse igual, sin obligar a nadie a editarlo a mano.
	 *
	 * @param int    $post_id
	 * @param string $locale
	 * @param array  $valores clave => texto
	 * @return int cuántos campos quedaron traducidos
	 */
	public static function guardar( $post_id, $locale, array $valores ) {
		$post_id = (int) $post_id;
		if ( ! self::idioma_valido( $locale ) || $post_id <= 0 ) {
			return 0;
		}

		$campos = self::campos_de( $post_id );
		$limpio = array();

		foreach ( $campos as $clave => $def ) {
			if ( ! array_key_exists( $clave, $valores ) ) {
				continue;
			}
			$texto = self::sanear( (string) $valores[ $clave ], $def );
			if ( '' === $texto ) {
				continue;
			}
			$limpio[ $clave ] = $texto;
		}

		$meta = self::META_PREFIJO . $locale;

		if ( ! $limpio ) {
			delete_post_meta( $post_id, $meta );
			self::tocar( $post_id );
			return 0;
		}

		/*
		 * Se toca la fecha de modificación ANTES de anotar la marca de agua, y
		 * las dos quedan con el mismo valor. El orden importa: si se anotara
		 * primero, la traducción recién guardada nacería marcada como
		 * «el castellano cambió después» de su propio guardado.
		 */
		$ahora = self::tocar( $post_id );

		// Contra qué versión del castellano se hizo esta traducción.
		$limpio['_origen'] = $ahora;

		update_post_meta( $post_id, $meta, $limpio );

		return count( $limpio ) - 1;
	}

	/**
	 * Mueve la fecha de modificación del post.
	 *
	 * NO es cosmético: `/sync` —el delta que la app usa para su caché
	 * offline— busca lo que cambió por `post_modified_gmt`, y guardar un meta
	 * no lo mueve. Sin esto, una traducción nueva no aparece nunca en el
	 * delta: la app la pide sólo si por otro motivo vuelve a bajar la pieza,
	 * y mientras tanto sigue mostrando el castellano sin que nada avise.
	 *
	 * Se escribe derecho en la tabla en vez de con `wp_update_post()` porque
	 * eso dispara `save_post` y `transition_post_status`, y una traducción no
	 * es una transición editorial: engancharía al flujo de revisión en algo
	 * que no le toca.
	 *
	 * @param int $post_id
	 * @return string la fecha nueva, en formato MySQL UTC
	 */
	private static function tocar( $post_id ) {
		global $wpdb;

		$gmt   = gmdate( 'Y-m-d H:i:s' );
		$local = current_time( 'mysql' );

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->posts,
			array( 'post_modified' => $local, 'post_modified_gmt' => $gmt ),
			array( 'ID' => (int) $post_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		clean_post_cache( (int) $post_id );

		return $gmt;
	}

	/**
	 * Limpieza del texto traducido.
	 *
	 * Un campo de un renglón se aplana; uno de párrafos conserva los saltos,
	 * que es justamente lo que lo hace párrafos. En los dos casos se saca todo
	 * HTML: el contenido del panel se escribe en texto plano y la app lo
	 * formatea, así que una traducción con etiquetas metería marcado que el
	 * original no tiene.
	 *
	 * @param string $texto
	 * @param array  $def
	 * @return string
	 */
	private static function sanear( $texto, $def ) {
		$texto = wp_strip_all_tags( $texto );
		if ( isset( $def['formato'] ) && 'parrafos' === $def['formato'] ) {
			$texto = str_replace( array( "\r\n", "\r" ), "\n", $texto );
			return trim( $texto );
		}
		return trim( preg_replace( '/\s+/u', ' ', $texto ) );
	}

	/**
	 * Borra un idioma entero de un post.
	 *
	 * @param int    $post_id
	 * @param string $locale
	 */
	public static function borrar( $post_id, $locale ) {
		if ( self::idioma_valido( $locale ) ) {
			delete_post_meta( (int) $post_id, self::META_PREFIJO . $locale );
		}
	}

	/* ---------------------------------------------------------------------
	 * Estado
	 * ------------------------------------------------------------------- */

	/**
	 * Cómo está un post en cada idioma.
	 *
	 * @param int $post_id
	 * @return array[] locale => { locale, nombre, hechos, total, estado, label }
	 *                 estado: sin_empezar | parcial | completa | desactualizada
	 */
	public static function estado( $post_id ) {
		$post_id  = (int) $post_id;
		$campos   = self::campos_de( $post_id );
		$total    = count( $campos );
		$modificado = (string) get_post_field( 'post_modified_gmt', $post_id );
		$out      = array();

		foreach ( self::idiomas() as $locale => $nombre ) {
			$datos  = get_post_meta( $post_id, self::META_PREFIJO . $locale, true );
			$datos  = is_array( $datos ) ? $datos : array();
			$origen = isset( $datos['_origen'] ) ? (string) $datos['_origen'] : '';
			unset( $datos['_origen'] );

			// Sólo cuentan los campos que hoy existen: si se podó un campo, una
			// traducción vieja no tiene que figurar como «más que completa».
			$hechos = 0;
			foreach ( $campos as $clave => $def ) {
				if ( isset( $datos[ $clave ] ) && '' !== trim( (string) $datos[ $clave ] ) ) {
					$hechos++;
				}
			}

			if ( 0 === $hechos ) {
				$estado = 'sin_empezar';
			} elseif ( $hechos < $total ) {
				$estado = 'parcial';
			} elseif ( $origen && $modificado && $origen < $modificado ) {
				// Está completa, pero el castellano cambió después.
				$estado = 'desactualizada';
			} else {
				$estado = 'completa';
			}

			$out[ $locale ] = array(
				'locale' => $locale,
				'nombre' => $nombre,
				'hechos' => $hechos,
				'total'  => $total,
				'estado' => $estado,
				'label'  => self::estado_label( $estado ),
			);
		}

		return $out;
	}

	/**
	 * @param string $estado
	 * @return string
	 */
	public static function estado_label( $estado ) {
		switch ( $estado ) {
			case 'completa':
				return __( 'Completa', 'caaguazu-portal' );
			case 'parcial':
				return __( 'A medias', 'caaguazu-portal' );
			case 'desactualizada':
				return __( 'El castellano cambió después', 'caaguazu-portal' );
			default:
				return __( 'Sin empezar', 'caaguazu-portal' );
		}
	}

	/**
	 * @param string $estado
	 * @return string clase de la píldora
	 */
	public static function estado_class( $estado ) {
		// Las mismas píldoras que usa el estado editorial, para que el panel
		// no tenga dos vocabularios de color.
		switch ( $estado ) {
			case 'completa':
				return 'is-approved';
			case 'parcial':
				return 'is-review';
			case 'desactualizada':
				return 'is-changes';
			default:
				return 'is-muted';
		}
	}

	/* ---------------------------------------------------------------------
	 * Lo que lee la API
	 * ------------------------------------------------------------------- */

	/**
	 * La pieza resuelta en un idioma: qué texto usar para cada campo, y si
	 * salió de la traducción o del original.
	 *
	 * Es EL punto por el que pasa la API, y por eso devuelve las dos cosas
	 * juntas: sin saber qué campos cayeron al original, un cliente no puede
	 * avisar «esta parte todavía no está traducida», y mostraría una mezcla de
	 * dos idiomas como si fuera una sola cosa.
	 *
	 * @param int    $post_id
	 * @param string $locale
	 * @return array { idioma, textos: clave=>texto, traducidos: clave[], completo: bool }
	 */
	public static function resolver( $post_id, $locale ) {
		$post_id = (int) $post_id;
		$campos  = self::campos_de( $post_id );

		$textos     = array();
		$traducidos = array();

		$guardado = self::idioma_valido( $locale ) ? self::leer( $post_id, $locale ) : array();

		foreach ( $campos as $clave => $def ) {
			if ( isset( $guardado[ $clave ] ) && '' !== trim( (string) $guardado[ $clave ] ) ) {
				$textos[ $clave ]     = (string) $guardado[ $clave ];
				$traducidos[]         = $clave;
				continue;
			}
			$textos[ $clave ] = self::original( $post_id, $clave, $def );
		}

		return array(
			'idioma'     => self::idioma_valido( $locale ) ? $locale : self::ORIGEN,
			'textos'     => $textos,
			'traducidos' => $traducidos,
			'completo'   => count( $traducidos ) === count( $campos ),
		);
	}
}
