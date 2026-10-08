<?php
/**
 * Las fuentes del asistente: de dónde saca lo que dice.
 *
 * QUÉ CUENTA COMO FUENTE
 *
 * Lo publicado en el panel y nada más: fichas (sitios y eventos), artículos y
 * recorridos. Es contenido que escribió una persona del equipo y que pasó por
 * el flujo editorial antes de llegar a `publicado`. Eso es lo que hace que sea
 * una fuente curada y no «lo que el modelo cree que sabe de Caaguazú»: un
 * horario que el modelo recuerda de internet puede tener tres años; el de la
 * ficha lo cargó alguien que fue a preguntar.
 *
 * Se lee A TRAVÉS de los endpoints de la propia API (`rest_do_request`, sin
 * HTTP de por medio), no de la base. Así el asistente ve exactamente lo que ve
 * la app: el mismo filtro de publicado, el mismo idioma con su caída campo por
 * campo al castellano, los mismos eventos. Leer la base directo sería una
 * segunda copia de esas reglas, y las copias se desincronizan.
 *
 * CÓMO LLEGA AL MODELO
 *
 * En dos niveles, porque mandar todo completo no entra y mandar sólo lo que
 * coincide por palabras pierde lo que se pregunta de otra forma:
 *
 *   - El CATÁLOGO: todo lo publicado, una línea por pieza. Le alcanza al modelo
 *     para saber qué existe aunque la pregunta no nombre nada («algo para
 *     hacer con chicos»), y es estable entre conversaciones, así que un
 *     proveedor con caché de prefijo lo cobra una vez y no en cada mensaje.
 *   - El DETALLE: la ficha completa de las pocas piezas que mejor coinciden con
 *     la pregunta, más la que se venía conversando.
 *
 * Cada pieza lleva una marca —[F12], [A3], [R5]— que el modelo copia al lado
 * de lo que afirma. La marca vuelve a la app como un enlace a la pieza: quien
 * pregunta puede abrir la ficha y ver con sus ojos de dónde salió el dato.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Asistente_Fuentes {

	/**
	 * Cuánto vive el catálogo armado. Además se invalida solo cuando algo se
	 * publica o se despublica (ver `al_cambiar()`); este vencimiento es para lo
	 * que cambia sin pasar por ahí, como una traducción guardada aparte.
	 */
	const TTL = 900;

	/** Opción con la generación del catálogo: subirla lo invalida entero. */
	const OPT_GENERACION = 'czuapi_ia_generacion';

	/** Topes de lectura, para que un error de carga no se lleve la memoria. */
	const MAX_FICHAS     = 800;
	const MAX_ARTICULOS  = 300;
	const MAX_RECORRIDOS = 100;

	/** Cuántas piezas viajan completas en cada pregunta. */
	const DETALLES = 4;

	/** Caracteres por pieza en el bloque de detalle. */
	const TOPE_DETALLE = 1800;

	/** Caracteres de la descripción corta en una línea del catálogo. */
	const TOPE_LINEA = 170;

	/** Cuántas fuentes se devuelven como mucho en una respuesta. */
	const MAX_CITAS = 6;

	/** Letra de la marca → tipo que entiende la app. */
	const TIPOS = array(
		'F' => 'ficha',
		'A' => 'articulo',
		'R' => 'recorrido',
	);

	/* --------------------------------------------------------------------- */
	/*  Invalidación                                                          */
	/* --------------------------------------------------------------------- */

	public static function hooks() {
		add_action( 'transition_post_status', array( __CLASS__, 'al_cambiar' ), 10, 3 );
		add_action( 'save_post', array( __CLASS__, 'al_guardar' ), 10, 2 );
	}

	/** Algo entró o salió de publicado. */
	public static function al_cambiar( $nuevo, $viejo, $post ) {
		if ( ( 'publish' === $nuevo || 'publish' === $viejo ) && self::es_fuente( $post ) ) {
			self::invalidar();
		}
	}

	/** Se editó algo ya publicado. */
	public static function al_guardar( $post_id, $post ) {
		if ( $post && 'publish' === $post->post_status && self::es_fuente( $post ) ) {
			self::invalidar();
		}
	}

	private static function es_fuente( $post ) {
		return $post instanceof WP_Post && in_array( $post->post_type, self::post_types(), true );
	}

	private static function post_types() {
		return array( PROMOTUR_Destinos::CPT, CZUAPI_Articulos::CPT, CZUAPI_Recorridos::CPT );
	}

	/**
	 * Tira el catálogo de todos los idiomas a la vez. Subir un número y no
	 * borrar transients por nombre: los idiomas los define el panel, y borrar
	 * por una lista fija dejaría vivo el de un idioma que se agregó después.
	 */
	public static function invalidar() {
		update_option( self::OPT_GENERACION, (int) get_option( self::OPT_GENERACION, 0 ) + 1, false );
	}

	/* --------------------------------------------------------------------- */
	/*  Lo que usa el asistente                                               */
	/* --------------------------------------------------------------------- */

	/**
	 * Las fuentes para una pregunta.
	 *
	 * @param string $mensaje   lo que se pregunta ahora
	 * @param string $idioma    código ya resuelto contra los soportados
	 * @param array  $historial turnos previos { role, content }
	 * @return array { catalogo: string, detalle: string, refs: array }
	 */
	public static function reunir( $mensaje, $idioma, $historial = array() ) {
		$cat = self::catalogo( $idioma );

		/*
		 * La pregunta anterior entra en la búsqueda: «¿y cuánto sale?» no dice
		 * de qué habla, y lo que lo dice quedó un turno atrás. Pesa lo mismo
		 * que la actual porque no hay forma confiable de saber cuál de las dos
		 * nombra el lugar.
		 */
		$consulta = (string) $mensaje;
		$previa   = self::ultimo( $historial, 'user' );
		if ( '' !== $previa ) {
			$consulta .= ' ' . $previa;
		}

		$ranking = self::rankear( self::tokenizar( $consulta ), $cat['items'] );

		/*
		 * Lo que se venía conversando va primero: si la respuesta anterior citó
		 * una ficha, la pregunta que sigue casi siempre es sobre esa ficha, y
		 * su nombre ya no aparece en lo que se escribe ahora.
		 */
		$elegidos = array();
		foreach ( array_slice( self::refs_en( self::ultimo( $historial, 'assistant' ) ), 0, 2 ) as $ref ) {
			if ( isset( $cat['items'][ $ref ] ) ) {
				$elegidos[ $ref ] = true;
			}
		}
		foreach ( array_keys( $ranking ) as $ref ) {
			if ( count( $elegidos ) >= self::DETALLES ) {
				break;
			}
			$elegidos[ $ref ] = true;
		}

		$detalle = array();
		foreach ( array_keys( $elegidos ) as $ref ) {
			$texto = self::detalle( $ref, $idioma );
			if ( '' !== $texto ) {
				$detalle[] = $texto;
			}
		}

		$refs = array();
		foreach ( $cat['items'] as $ref => $item ) {
			$refs[ $ref ] = array(
				'tipo'      => $item['tipo'],
				'id'        => $item['id'],
				'titulo'    => $item['titulo'],
				'tipo_item' => $item['tipo_item'],
			);
		}

		return array(
			'catalogo' => $cat['texto'],
			'detalle'  => implode( "\n\n", $detalle ),
			'refs'     => $refs,
		);
	}

	/**
	 * Cuántas piezas y caracteres tiene el catálogo ya armado, sin armarlo.
	 * Lo muestra la pantalla de wp-admin; armarlo ahí para mostrar un número
	 * sería pagar la consulta entera en cada visita.
	 *
	 * @return array|null { piezas, caracteres }
	 */
	public static function estado( $idioma = 'es' ) {
		$guardado = get_transient( self::clave( $idioma ) );
		if ( ! is_array( $guardado ) ) {
			return null;
		}
		return array(
			'piezas'     => count( $guardado['items'] ),
			'caracteres' => mb_strlen( $guardado['texto'] ),
		);
	}

	/* --------------------------------------------------------------------- */
	/*  El catálogo                                                           */
	/* --------------------------------------------------------------------- */

	private static function clave( $idioma ) {
		return 'czuapi_ia_cat_' . sanitize_key( $idioma ) . '_' . (int) get_option( self::OPT_GENERACION, 0 );
	}

	/**
	 * @return array { texto: string, items: array ref => item }
	 */
	public static function catalogo( $idioma ) {
		$clave    = self::clave( $idioma );
		$guardado = get_transient( $clave );
		if ( is_array( $guardado ) && isset( $guardado['texto'], $guardado['items'] ) ) {
			return $guardado;
		}

		$armado = self::armar( $idioma );
		set_transient( $clave, $armado, self::TTL );
		return $armado;
	}

	private static function armar( $idioma ) {
		$eventos = array();
		$lugares = array();
		$items   = array();
		$on      = CZUAPI_Asistente::fuentes_activas();

		foreach ( self::todas( '/inventario', $idioma, self::MAX_FICHAS ) as $f ) {
			$id     = (int) $f['id'];
			$evento = 'evento' === ( $f['tipo_item'] ?? '' );
			$fechas = isset( $f['fechas'] ) && is_array( $f['fechas'] ) ? $f['fechas'] : null;

			// Una fuente apagada no entra al catálogo: el modelo no puede citarla.
			if ( $evento ? empty( $on['eventos'] ) : empty( $on['lugares'] ) ) {
				continue;
			}

			// Un evento que ya pasó no le sirve a nadie que está planeando, y
			// recomendarlo sería mandar a alguien a un lugar vacío.
			if ( $evento && $fechas && ! empty( $fechas['terminado'] ) ) {
				continue;
			}

			$desc = self::descripcion_corta( $id, $idioma );
			$ref  = 'F' . $id;

			$linea = self::linea_ficha( $ref, $f, $desc );
			if ( $evento ) {
				$eventos[ (string) ( $fechas['inicio'] ?? '' ) . '|' . $ref ] = $linea;
			} else {
				$lugares[] = $linea;
			}

			$items[ $ref ] = self::item(
				'ficha',
				$id,
				(string) ( $f['titulo'] ?? '' ),
				$evento ? 'evento' : 'sitio',
				array( $f['categoria']['nombre'] ?? '', self::nombres( $f['etiquetas'] ?? array() ), $evento ? 'evento' : '' ),
				$desc . ' ' . self::cuerpo_indice( $id )
			);
		}
		ksort( $eventos );

		$recorridos = array();
		foreach ( empty( $on['recorridos'] ) ? array() : self::todas( '/recorridos', $idioma, self::MAX_RECORRIDOS ) as $r ) {
			$id           = (int) $r['id'];
			$ref          = 'R' . $id;
			$recorridos[] = self::linea_recorrido( $ref, $r );
			$items[ $ref ] = self::item(
				'recorrido',
				$id,
				(string) ( $r['titulo'] ?? '' ),
				'',
				array( 'recorrido' ),
				(string) ( $r['resumen'] ?? '' )
			);
		}

		$articulos = array();
		foreach ( empty( $on['articulos'] ) ? array() : self::todas( '/articulos', $idioma, self::MAX_ARTICULOS ) as $a ) {
			$id          = (int) $a['id'];
			$ref         = 'A' . $id;
			$articulos[] = self::linea_articulo( $ref, $a );
			$items[ $ref ] = self::item(
				'articulo',
				$id,
				(string) ( $a['titulo'] ?? '' ),
				'',
				array( $a['antetitulo'] ?? '', self::nombres( $a['etiquetas'] ?? array() ), 'articulo' ),
				( $a['subtitulo'] ?? '' ) . ' ' . ( $a['entradilla'] ?? '' )
			);
		}

		/*
		 * El orden es el de recorte: si el presupuesto no alcanza, el catálogo
		 * se corta por el final. Los eventos van primero porque son lo único
		 * que caduca; los artículos al final porque son lo que más se parece a
		 * algo que el detalle ya trae completo cuando hace falta.
		 */
		$bloques = array(
			'Eventos que vienen' => array_values( $eventos ),
			'Lugares'            => $lugares,
			'Recorridos'         => $recorridos,
			'Artículos'          => $articulos,
		);
		$texto = '';
		foreach ( $bloques as $titulo => $lineas ) {
			if ( $lineas ) {
				$texto .= ( '' === $texto ? '' : "\n\n" ) . '## ' . $titulo . "\n" . implode( "\n", $lineas );
			}
		}

		return array( 'texto' => $texto, 'items' => $items );
	}

	/**
	 * Todas las páginas de un endpoint de lista, hasta un tope.
	 *
	 * @return array[]
	 */
	private static function todas( $ruta, $idioma, $tope ) {
		$out    = array();
		$pagina = 1;
		do {
			$data = self::pedir( $ruta, array( 'idioma' => $idioma, 'pagina' => $pagina, 'por_pagina' => 100 ) );
			if ( ! is_array( $data ) || empty( $data['items'] ) ) {
				break;
			}
			foreach ( $data['items'] as $item ) {
				if ( is_array( $item ) && isset( $item['id'] ) ) {
					$out[] = $item;
				}
			}
			$total = (int) ( $data['total'] ?? 0 );
			$pagina++;
		} while ( count( $out ) < $total && count( $out ) < $tope );

		return array_slice( $out, 0, $tope );
	}

	/**
	 * Un GET a la propia API, sin salir del proceso.
	 *
	 * @return array|null el cuerpo de la respuesta, o null si no fue un 200
	 */
	private static function pedir( $ruta, $params = array() ) {
		$req = new WP_REST_Request( 'GET', '/' . CZUAPI_NS . $ruta );
		foreach ( $params as $k => $v ) {
			$req->set_param( $k, $v );
		}
		$res = rest_do_request( $req );
		if ( $res->is_error() || 200 !== $res->get_status() ) {
			return null;
		}
		$data = $res->get_data();
		return is_array( $data ) ? $data : null;
	}

	/**
	 * La descripción de una ficha para su línea del catálogo, en el idioma
	 * pedido si está traducida. La lista de `/inventario` no la trae —una
	 * tarjeta no la muestra— y el catálogo sin ella son sólo nombres: el
	 * modelo no tendría cómo saber que «Salto Itá» es una cascada.
	 */
	private static function descripcion_corta( $id, $idioma ) {
		$t    = CZUAPI_Idiomas::textos( $id, $idioma );
		$desc = isset( $t['textos']['descripcion'] ) ? $t['textos']['descripcion'] : (string) get_post_field( 'post_content', $id );
		return self::plano( $desc );
	}

	/** El cuerpo original, sólo para buscar: no viaja al modelo desde acá. */
	private static function cuerpo_indice( $id ) {
		return mb_substr( self::plano( (string) get_post_field( 'post_content', $id ) ), 0, 1500 );
	}

	private static function item( $tipo, $id, $titulo, $tipo_item, array $meta, $cuerpo ) {
		return array(
			'tipo'      => $tipo,
			'id'        => (int) $id,
			'titulo'    => $titulo,
			'tipo_item' => $tipo_item,
			'indice'    => array(
				'titulo' => self::tokenizar( $titulo ),
				'meta'   => self::tokenizar( implode( ' ', $meta ) ),
				'cuerpo' => array_slice( self::tokenizar( $cuerpo ), 0, 160 ),
			),
		);
	}

	/* --------------------------------------------------------------------- */
	/*  Líneas del catálogo                                                   */
	/* --------------------------------------------------------------------- */

	public static function linea_ficha( $ref, array $f, $desc ) {
		$partes = array( '[' . $ref . '] ' . self::una_linea( $f['titulo'] ?? '' ) );

		$tags = array();
		if ( ! empty( $f['categoria']['nombre'] ) ) {
			$tags[] = $f['categoria']['nombre'];
		}
		$etiquetas = self::nombres( $f['etiquetas'] ?? array() );
		if ( '' !== $etiquetas ) {
			$tags[] = $etiquetas;
		}
		if ( $tags ) {
			$partes[0] .= ' (' . implode( '; ', $tags ) . ')';
		}

		$fechas = isset( $f['fechas'] ) && is_array( $f['fechas'] ) ? $f['fechas'] : null;
		if ( $fechas && ! empty( $fechas['inicio'] ) ) {
			$cuando = self::fecha_local( $fechas['inicio'] );
			if ( ! empty( $fechas['fin'] ) ) {
				$cuando .= ' a ' . self::fecha_local( $fechas['fin'] );
			}
			if ( ! empty( $fechas['en_curso'] ) ) {
				$cuando .= ' (en curso)';
			}
			$partes[] = 'cuándo: ' . $cuando;
		}
		if ( ! empty( $f['horario_resumen'] ) ) {
			$partes[] = 'horario: ' . self::una_linea( $f['horario_resumen'] );
		}
		$precio = self::precio( $f['rango_precio'] ?? null );
		if ( '' !== $precio ) {
			$partes[] = 'precio: ' . $precio;
		}
		if ( '' !== trim( (string) $desc ) ) {
			$partes[] = self::recortar( $desc, self::TOPE_LINEA );
		}
		return implode( ' — ', $partes );
	}

	public static function linea_recorrido( $ref, array $r ) {
		$partes = array( '[' . $ref . '] ' . self::una_linea( $r['titulo'] ?? '' ) );
		if ( ! empty( $r['cantidad_paradas'] ) ) {
			$partes[] = (int) $r['cantidad_paradas'] . ' paradas';
		}
		if ( ! empty( $r['duracion_estimada'] ) ) {
			$partes[] = 'duración: ' . self::una_linea( $r['duracion_estimada'] );
		}
		if ( ! empty( $r['resumen'] ) ) {
			$partes[] = self::recortar( self::plano( $r['resumen'] ), self::TOPE_LINEA );
		}
		return implode( ' — ', $partes );
	}

	public static function linea_articulo( $ref, array $a ) {
		$partes = array( '[' . $ref . '] ' . self::una_linea( $a['titulo'] ?? '' ) );
		$bajada = trim( (string) ( $a['entradilla'] ?? '' ) );
		if ( '' === $bajada ) {
			$bajada = trim( (string) ( $a['subtitulo'] ?? '' ) );
		}
		if ( '' !== $bajada ) {
			$partes[] = self::recortar( self::plano( $bajada ), self::TOPE_LINEA );
		}
		return implode( ' — ', $partes );
	}

	/* --------------------------------------------------------------------- */
	/*  El detalle                                                            */
	/* --------------------------------------------------------------------- */

	/**
	 * Una pieza completa, en texto, lista para el prompt.
	 */
	private static function detalle( $ref, $idioma ) {
		$letra = substr( $ref, 0, 1 );
		$id    = (int) substr( $ref, 1 );
		$rutas = array( 'F' => '/inventario/', 'A' => '/articulos/', 'R' => '/recorridos/' );
		if ( ! isset( $rutas[ $letra ] ) || $id <= 0 ) {
			return '';
		}
		$d = self::pedir( $rutas[ $letra ] . $id, array( 'idioma' => $idioma ) );
		if ( ! $d ) {
			return '';
		}
		switch ( $letra ) {
			case 'F':
				return self::texto_ficha( $ref, $d );
			case 'A':
				return self::texto_articulo( $ref, $d );
			default:
				return self::texto_recorrido( $ref, $d );
		}
	}

	public static function texto_ficha( $ref, array $d ) {
		$l = array( '[' . $ref . '] ' . self::una_linea( $d['titulo'] ?? '' ) );

		$evento = 'evento' === ( $d['tipo_item'] ?? '' );
		$l[]    = 'Tipo: ' . ( $evento ? 'evento' : 'lugar' )
			. ( ! empty( $d['categoria']['nombre'] ) ? ' · ' . $d['categoria']['nombre'] : '' );

		$etiquetas = self::nombres( $d['etiquetas'] ?? array() );
		if ( '' !== $etiquetas ) {
			$l[] = 'Etiquetas: ' . $etiquetas;
		}
		$f = isset( $d['fechas'] ) && is_array( $d['fechas'] ) ? $d['fechas'] : null;
		if ( $f && ! empty( $f['inicio'] ) ) {
			$l[] = 'Cuándo: ' . self::fecha_local( $f['inicio'] )
				. ( ! empty( $f['fin'] ) ? ' a ' . self::fecha_local( $f['fin'] ) : '' )
				. ( ! empty( $f['en_curso'] ) ? ' (en curso ahora)' : '' );
		}

		$p = isset( $d['practicos'] ) && is_array( $d['practicos'] ) ? $d['practicos'] : array();
		if ( ! empty( $p['horario'] ) ) {
			$l[] = 'Horario: ' . self::una_linea( $p['horario'] );
		}
		if ( ! empty( $p['costo'] ) ) {
			$l[] = 'Costo: ' . self::una_linea( $p['costo'] );
		}
		$precio = self::precio( $p['rango_precio'] ?? null );
		if ( '' !== $precio ) {
			$l[] = 'Rango de precio: ' . $precio;
		}
		if ( ! empty( $p['contacto'] ) ) {
			$l[] = 'Contacto: ' . self::una_linea( $p['contacto'] );
		}
		if ( ! empty( $d['acceso']['estado_camino'] ) ) {
			$l[] = 'Estado del camino: ' . self::una_linea( $d['acceso']['estado_camino'] );
		}
		$l[] = ! empty( $d['google_maps'] ) ? 'Ubicación: cargada (la ficha abre el mapa del teléfono)' : 'Ubicación: no cargada';

		$desc = self::plano( $d['descripcion'] ?? '' );
		if ( '' !== $desc ) {
			$l[] = 'Descripción: ' . $desc;
		}
		if ( ! empty( $d['fuentes'] ) ) {
			$l[] = 'Fuentes de la ficha: ' . self::una_linea( is_array( $d['fuentes'] ) ? implode( '; ', $d['fuentes'] ) : $d['fuentes'] );
		}
		if ( isset( $d['traducido'] ) && false === $d['traducido'] && 'es' !== ( $d['idioma'] ?? 'es' ) ) {
			$l[] = '(Parte de esta ficha no está traducida y viene en castellano.)';
		}
		return self::recortar( implode( "\n", $l ), self::TOPE_DETALLE );
	}

	public static function texto_articulo( $ref, array $d ) {
		$l = array( '[' . $ref . '] ' . self::una_linea( $d['titulo'] ?? '' ) );
		if ( ! empty( $d['antetitulo'] ) ) {
			$l[] = 'Sección: ' . self::una_linea( $d['antetitulo'] );
		}
		if ( ! empty( $d['subtitulo'] ) ) {
			$l[] = 'Subtítulo: ' . self::una_linea( $d['subtitulo'] );
		}
		if ( ! empty( $d['publicado'] ) ) {
			$l[] = 'Publicado: ' . self::fecha_local( $d['publicado'], false );
		}
		$autores = array();
		foreach ( (array) ( $d['autores'] ?? array() ) as $a ) {
			if ( ! empty( $a['nombre'] ) ) {
				$autores[] = $a['nombre'];
			}
		}
		if ( $autores ) {
			$l[] = 'Firma: ' . implode( ', ', $autores );
		}
		if ( ! empty( $d['entradilla'] ) ) {
			$l[] = 'Entradilla: ' . self::plano( $d['entradilla'] );
		}
		$cuerpo = self::plano( $d['cuerpo_html'] ?? '' );
		if ( '' !== $cuerpo ) {
			$l[] = 'Texto: ' . $cuerpo;
		}
		if ( ! empty( $d['fuentes'] ) ) {
			$l[] = 'Fuentes del artículo: ' . self::una_linea( implode( '; ', (array) $d['fuentes'] ) );
		}
		return self::recortar( implode( "\n", $l ), self::TOPE_DETALLE );
	}

	public static function texto_recorrido( $ref, array $d ) {
		$l = array( '[' . $ref . '] ' . self::una_linea( $d['titulo'] ?? '' ) );
		if ( ! empty( $d['duracion_estimada'] ) ) {
			$l[] = 'Duración: ' . self::una_linea( $d['duracion_estimada'] );
		}
		if ( ! empty( $d['resumen'] ) ) {
			$l[] = 'Resumen: ' . self::plano( $d['resumen'] );
		}
		$paradas = array();
		$n       = 0;
		foreach ( (array) ( $d['paradas'] ?? array() ) as $p ) {
			// Una parada cuya ficha se despublicó viene sin título: no hay
			// nada que contar de ella y nombrarla sería mandar a alguien ahí.
			if ( ! is_array( $p ) || empty( $p['disponible'] ) ) {
				continue;
			}
			$n++;
			$nombre = $n . '. ' . self::una_linea( $p['titulo'] ?? '' );
			// La parada que es una ficha lleva su marca: así el modelo puede
			// citar el lugar, no sólo el recorrido. El evento del CPT viejo no
			// la lleva porque la app no tiene dónde abrirlo.
			if ( ! empty( $p['ref_id'] ) && 'destino' === ( $p['ref_tipo'] ?? '' ) ) {
				$nombre .= ' [F' . (int) $p['ref_id'] . ']';
			}
			if ( ! empty( $p['costo'] ) ) {
				$nombre .= ' — costo: ' . self::una_linea( $p['costo'] );
			}
			if ( ! empty( $p['texto'] ) ) {
				$nombre .= ' — ' . self::recortar( self::plano( $p['texto'] ), 200 );
			}
			$paradas[] = $nombre;
		}
		if ( $paradas ) {
			$l[] = "Paradas:\n" . implode( "\n", $paradas );
		}
		if ( isset( $d['costo_total']['hay_pago'] ) ) {
			$l[] = 'Alguna parada se paga: ' . ( $d['costo_total']['hay_pago'] ? 'sí' : 'no' );
		}
		$cuerpo = self::plano( $d['articulo_html'] ?? '' );
		if ( '' !== $cuerpo ) {
			$l[] = 'Texto: ' . $cuerpo;
		}
		return self::recortar( implode( "\n", $l ), self::TOPE_DETALLE );
	}

	/* --------------------------------------------------------------------- */
	/*  Búsqueda                                                              */
	/*                                                                        */
	/*  Funciones puras: no tocan WordPress, así se prueban sin levantarlo    */
	/*  (tools/verificar-asistente.php).                                      */
	/* --------------------------------------------------------------------- */

	/**
	 * Palabras vacías en los tres idiomas de la app. «caaguazu» también: toda
	 * la app es sobre Caaguazú, y dejarla sumaría un punto a cada pieza que la
	 * nombre sin decir nada sobre la pregunta.
	 */
	const VACIAS = 'a al algo alguna alguno algun ante antes aqui asi aun bien cada como con contra cual cuales cuando cuanto cuanta cuantos cuantas de del desde donde dos el ella ellas ellos en entre era eres es esa ese eso esta este esto estos estas estan esta hay hace hacer hasta la las le les lo los mas me mi mis mucho muy nada ni no nos o otra otro para pero poco por porque puede pueden puedo que quien quiero se sea ser si sin sobre son su sus tal tambien te tengo tiene tienen todo todos tu tus un una uno unos unas usted vos y ya yo hola gracias favor caaguazu '
		. 'about an and any are as at be can could do does for from have how i in is it me my near of on or the there this to what when where which who why with would you your '
		. 'ao aos as com da das do dos e em eu foi na nas no nos o os ou para pelo pela por qual quando que se sem seu sua tem um uma voce onde como mais muito obrigado ola';

	/** Sin tildes, en minúsculas, sólo letras y números. */
	public static function normalizar( $texto ) {
		$texto = mb_strtolower( (string) $texto, 'UTF-8' );
		$texto = strtr( $texto, array(
			'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
			'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'ẽ' => 'e',
			'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ĩ' => 'i',
			'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
			'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ũ' => 'u',
			'ñ' => 'n', 'ç' => 'c', 'ỹ' => 'y', 'ĝ' => 'g',
		) );
		return trim( (string) preg_replace( '/[^a-z0-9]+/', ' ', $texto ) );
	}

	/**
	 * Las palabras con peso de una pregunta o de un texto, sin repetir.
	 *
	 * @return string[]
	 */
	public static function tokenizar( $texto ) {
		static $vacias = null;
		if ( null === $vacias ) {
			$vacias = array_flip( preg_split( '/\s+/', trim( self::VACIAS ) ) );
		}
		$out = array();
		foreach ( explode( ' ', self::normalizar( $texto ) ) as $p ) {
			if ( strlen( $p ) < 3 || isset( $vacias[ $p ] ) ) {
				continue;
			}
			$out[ $p ] = true;
		}
		return array_keys( $out );
	}

	/**
	 * ¿Dos palabras dicen lo mismo? Igualdad, o una es el principio de la otra
	 * con poca diferencia: «cascada» y «cascadas», «museo» y «museos», «fiesta»
	 * y «fiestas». Sin esto, un plural en la pregunta no encuentra el singular
	 * de la ficha, que es la forma más común de no encontrar nada.
	 */
	public static function coincide( $a, $b ) {
		if ( $a === $b ) {
			return true;
		}
		$la = strlen( $a );
		$lb = strlen( $b );
		if ( min( $la, $lb ) < 4 || abs( $la - $lb ) > 3 ) {
			return false;
		}
		return $la < $lb ? 0 === strpos( $b, $a ) : 0 === strpos( $a, $b );
	}

	/**
	 * Puntaje de una pieza: 3 por palabra en el título, 2 en categoría o
	 * etiquetas, 1 en el cuerpo. Cada palabra de la pregunta cuenta una sola
	 * vez, por su mejor lugar: una ficha que repite «comida» veinte veces no
	 * le gana a otra que se llama «Comedor».
	 */
	public static function puntaje( array $preguntas, array $indice ) {
		$total = 0;
		foreach ( $preguntas as $q ) {
			foreach ( array( 'titulo' => 3, 'meta' => 2, 'cuerpo' => 1 ) as $campo => $peso ) {
				$hallada = false;
				foreach ( (array) ( $indice[ $campo ] ?? array() ) as $t ) {
					if ( self::coincide( $q, $t ) ) {
						$hallada = true;
						break;
					}
				}
				if ( $hallada ) {
					$total += $peso;
					break;
				}
			}
		}
		return $total;
	}

	/**
	 * Las piezas que coinciden, de más a menos. Sólo las que suman algo.
	 *
	 * @return array ref => puntaje
	 */
	public static function rankear( array $preguntas, array $items ) {
		if ( ! $preguntas ) {
			return array();
		}
		$out = array();
		foreach ( $items as $ref => $item ) {
			$p = self::puntaje( $preguntas, $item['indice'] ?? array() );
			if ( $p > 0 ) {
				$out[ $ref ] = $p;
			}
		}
		arsort( $out );
		return $out;
	}

	/* --------------------------------------------------------------------- */
	/*  Citas                                                                 */
	/* --------------------------------------------------------------------- */

	/** Una marca o un grupo de marcas: [F12], [F12, A3], [ R5 ]. */
	const PATRON_CITA = '/\[\s*([FAR]\d+(?:\s*[,;]\s*[FAR]\d+)*)\s*\]/';

	/**
	 * Las marcas de un texto, en el orden en que aparecen y sin repetir.
	 *
	 * @return string[]
	 */
	public static function refs_en( $texto ) {
		$out = array();
		if ( preg_match_all( self::PATRON_CITA, (string) $texto, $m ) ) {
			foreach ( $m[1] as $grupo ) {
				foreach ( preg_split( '/\s*[,;]\s*/', $grupo ) as $ref ) {
					$out[ $ref ] = true;
				}
			}
		}
		return array_keys( $out );
	}

	/**
	 * Separa la respuesta en lo que lee la persona y lo que abre la app.
	 *
	 * Una marca que no existe en las fuentes se descarta en silencio: el
	 * modelo pudo inventarla, y un enlace a una ficha que no existe es peor que
	 * ninguno. La marca se saca del texto siempre —existiera o no—, porque
	 * «[F12]» en medio de una frase no le dice nada a quien lee.
	 *
	 * @param string $contenido lo que devolvió el modelo
	 * @param array  $refs      ref => { tipo, id, titulo, tipo_item }
	 * @return array { texto: string, fuentes: array[] }
	 */
	public static function citas( $contenido, array $refs ) {
		$fuentes = array();
		foreach ( self::refs_en( $contenido ) as $ref ) {
			if ( ! isset( $refs[ $ref ] ) || count( $fuentes ) >= self::MAX_CITAS ) {
				continue;
			}
			$r      = $refs[ $ref ];
			$fuente = array(
				'tipo'   => $r['tipo'],
				'id'     => (int) $r['id'],
				'titulo' => (string) $r['titulo'],
			);
			if ( ! empty( $r['tipo_item'] ) ) {
				$fuente['tipo_item'] = $r['tipo_item'];
			}
			$fuentes[] = $fuente;
		}

		return array(
			'texto'   => self::limpiar( preg_replace( self::PATRON_CITA, '', (string) $contenido ) ),
			'fuentes' => $fuentes,
		);
	}

	/**
	 * Texto plano para la app: sin las marcas de Markdown que el modelo pone
	 * por costumbre aunque se le pida que no. La app muestra el texto tal cual,
	 * y un «**Horario:**» con los asteriscos a la vista se lee como un error.
	 */
	public static function limpiar( $texto ) {
		$texto = str_replace( array( "\r\n", "\r" ), "\n", (string) $texto );
		$texto = preg_replace( '/\*\*(.+?)\*\*/s', '$1', $texto );
		$texto = preg_replace( '/__(.+?)__/s', '$1', $texto );
		$texto = preg_replace( '/`([^`]*)`/', '$1', $texto );
		$texto = preg_replace( '/^\s{0,3}#{1,6}\s*/m', '', $texto );
		$texto = preg_replace( '/^(\s*)(?:\*|•)\s+/mu', '$1- ', $texto );
		// Lo que deja sacar una marca: el espacio antes del punto y los dobles.
		$texto = preg_replace( '/[ \t]+([.,;:!?])/', '$1', $texto );
		$texto = preg_replace( '/[ \t]{2,}/', ' ', $texto );
		$texto = preg_replace( '/[ \t]+$/m', '', $texto );
		$texto = preg_replace( "/\n{3,}/", "\n\n", $texto );
		return trim( $texto );
	}

	/* --------------------------------------------------------------------- */
	/*  Formato                                                               */
	/* --------------------------------------------------------------------- */

	/** HTML de la API a texto corrido. */
	public static function plano( $html ) {
		$texto = wp_strip_all_tags( (string) $html );
		$texto = html_entity_decode( $texto, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		return trim( (string) preg_replace( '/\s+/u', ' ', $texto ) );
	}

	private static function una_linea( $texto ) {
		return trim( (string) preg_replace( '/\s+/u', ' ', (string) $texto ) );
	}

	/** Corta por palabra y marca el corte. */
	public static function recortar( $texto, $tope ) {
		$texto = (string) $texto;
		if ( mb_strlen( $texto ) <= $tope ) {
			return $texto;
		}
		$corte   = mb_substr( $texto, 0, $tope );
		$espacio = mb_strrpos( $corte, ' ' );
		if ( false !== $espacio && $espacio > $tope * 0.6 ) {
			$corte = mb_substr( $corte, 0, $espacio );
		}
		return rtrim( $corte, " ,;:-—" ) . '…';
	}

	/** Los nombres de una lista de términos de la API. */
	private static function nombres( $terminos ) {
		$out = array();
		foreach ( (array) $terminos as $t ) {
			if ( is_array( $t ) && ! empty( $t['nombre'] ) ) {
				$out[] = $t['nombre'];
			}
		}
		return implode( ', ', $out );
	}

	/** 0 = gratis; 1 a 4, de barato a caro. Vacío si no está cargado. */
	public static function precio( $rango ) {
		if ( null === $rango || '' === $rango ) {
			return '';
		}
		$rango = max( 0, min( 4, (int) $rango ) );
		return 0 === $rango ? 'gratis' : $rango . ' de 4';
	}

	/** ISO de la API (UTC) a la hora local del sitio, que es la de Paraguay. */
	private static function fecha_local( $iso, $con_hora = true ) {
		$ts = strtotime( (string) $iso );
		if ( ! $ts ) {
			return (string) $iso;
		}
		return wp_date( $con_hora ? 'd/m/Y H:i' : 'd/m/Y', $ts );
	}

	/** El último mensaje de un rol en el historial. */
	private static function ultimo( array $historial, $rol ) {
		for ( $i = count( $historial ) - 1; $i >= 0; $i-- ) {
			if ( ( $historial[ $i ]['role'] ?? '' ) === $rol ) {
				return (string) ( $historial[ $i ]['content'] ?? '' );
			}
		}
		return '';
	}
}
