<?php
/**
 * Datos de prueba: los sitios de la investigación, cargados como borradores.
 *
 * POR QUÉ EXISTE
 *
 * Para la presentación hacía falta mostrar más que la única ficha publicada.
 * La investigación (`datos-prueba/sitios-caaguazu.json`, pedida con
 * `docs/pedido-investigacion-sitios.md`) trae los sitios y eventos del
 * distrito con sus fuentes. Esto los carga en el panel con un botón y los saca
 * con otro.
 *
 * LO QUE NO HACE, A PROPÓSITO
 *
 * No publica nada. Todo entra como BORRADOR y con la marca `_promotur_prueba`:
 * en la web lo ven sólo los profesores (la vista previa de borradores), la app
 * no lo recibe, y para que algo salga al público tiene que pasar por el mismo
 * flujo de revisión que cualquier ficha. Los textos los escribió una
 * herramienta de investigación, no el equipo: alguien los tiene que leer antes.
 *
 * Borrar es borrar de verdad (no la papelera): son datos que nunca existieron
 * para nadie más, y en la papelera quedarían mezclados con lo que el equipo
 * borró sin querer. Se va también lo que se creó para ellos —las categorías y
 * etiquetas nuevas que quedaron vacías, la foto bajada— y nada más.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Prueba {

	private static $instance = null;

	/** Marca de todo lo que se creó acá: fichas, fotos. */
	const META = '_promotur_prueba';

	/** Qué entrada del archivo es cada ficha, para no cargarla dos veces. */
	const META_CLAVE = '_promotur_prueba_clave';

	/** Términos que se crearon para la prueba (y que se borran si quedan vacíos). */
	const OPT_TERMINOS = 'promotur_prueba_terminos';

	/** Quién la puede cargar y borrar: quien revisa, que es quien la ve. */
	const CAP = 'promotur_review_content';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'datos_prueba', array( $this, 'handle' ) );
	}

	public static function archivo() {
		return PROMOTUR_DIR . 'datos-prueba/sitios-caaguazu.json';
	}

	/**
	 * La investigación, leída. Vacía si el archivo falta o no se entiende.
	 *
	 * @return array { herramienta, generado, sitios[] }
	 */
	public static function investigacion() {
		$crudo = is_readable( self::archivo() ) ? (string) file_get_contents( self::archivo() ) : '';
		$datos = json_decode( $crudo, true );
		if ( ! is_array( $datos ) || empty( $datos['sitios'] ) || ! is_array( $datos['sitios'] ) ) {
			return array( 'herramienta' => '', 'generado' => '', 'sitios' => array() );
		}
		return $datos;
	}

	/** @return int[] IDs de las fichas de prueba que hay ahora. */
	public static function cargadas() {
		return array_map( 'intval', get_posts( array(
			'post_type'      => PROMOTUR_Destinos::CPT,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		) ) );
	}

	/* ---------------------------------------------------------------------
	 * Las acciones del panel
	 * ------------------------------------------------------------------ */

	public function handle() {
		if ( ! caaguazu_account_can( 'promotor', self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
		}
		$hacer = isset( $_POST['hacer'] ) ? sanitize_key( wp_unslash( $_POST['hacer'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( 'cargar' === $hacer ) {
			$r = self::cargar( caaguazu_account_id() );
			PROMOTUR_Audit::log( 'prueba_cargada', array( 'payload' => $r ) );
			/* translators: %d: cantidad de fichas */
			promotur_flash( sprintf( __( 'Listo: %d fichas de prueba cargadas como borrador.', 'caaguazu-portal' ), $r['nuevas'] ), 'success' );
		} elseif ( 'borrar' === $hacer ) {
			$r = self::borrar();
			PROMOTUR_Audit::log( 'prueba_borrada', array( 'payload' => $r ) );
			/* translators: %d: cantidad de fichas */
			promotur_flash( sprintf( __( 'Listo: %d fichas de prueba borradas.', 'caaguazu-portal' ), $r['fichas'] ), 'success' );
		}

		wp_safe_redirect( add_query_arg( 'pestana', 'presentacion', promotur_url( 'panel/asistente' ) ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Cargar
	 * ------------------------------------------------------------------ */

	/**
	 * Carga lo que falte. Correrlo dos veces no duplica: cada entrada lleva su
	 * clave, y la que ya está se saltea.
	 *
	 * @param int $cuenta dueña de las fichas
	 * @return array { nuevas, ya_estaban, fotos, sin_foto }
	 */
	public static function cargar( $cuenta ) {
		$inv = self::investigacion();
		$out = array( 'nuevas' => 0, 'ya_estaban' => 0, 'fotos' => 0, 'sin_foto' => 0 );

		$existentes = array();
		foreach ( self::cargadas() as $id ) {
			$existentes[ (string) get_post_meta( $id, self::META_CLAVE, true ) ] = true;
		}

		foreach ( $inv['sitios'] as $s ) {
			if ( ! is_array( $s ) || empty( $s['titulo'] ) ) {
				continue;
			}
			$clave = sanitize_title( (string) $s['titulo'] );
			if ( isset( $existentes[ $clave ] ) ) {
				$out['ya_estaban']++;
				continue;
			}
			$id = self::crear( $s, $clave, (int) $cuenta, $inv );
			if ( ! $id ) {
				continue;
			}
			$out['nuevas']++;
			$existentes[ $clave ] = true;

			if ( ! empty( $s['fotos'][0]['url'] ) ) {
				if ( self::poner_foto( $id, $s['fotos'][0], (string) $s['titulo'] ) ) {
					$out['fotos']++;
				} else {
					$out['sin_foto']++;
				}
			}
		}

		if ( class_exists( 'CZUAPI_Asistente_Fuentes' ) ) {
			CZUAPI_Asistente_Fuentes::invalidar();
		}
		return $out;
	}

	/** Una ficha de la investigación, como borrador del panel. */
	private static function crear( array $s, $clave, $cuenta, array $inv ) {
		$id = wp_insert_post( array(
			'post_type'    => PROMOTUR_Destinos::CPT,
			'post_status'  => 'draft',
			'post_title'   => sanitize_text_field( (string) $s['titulo'] ),
			'post_content' => wp_kses_post( (string) ( $s['descripcion'] ?? '' ) ),
			'post_author'  => function_exists( 'caaguazu_service_user_id' ) ? caaguazu_service_user_id() : get_current_user_id(),
		), true );
		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}

		update_post_meta( $id, self::META, '1' );
		update_post_meta( $id, self::META_CLAVE, $clave );
		update_post_meta( $id, '_promotur_estado', 'borrador' );
		PROMOTUR_Destinos::set_owner( $id, $cuenta );

		$evento = 'evento' === ( $s['tipo'] ?? '' );
		update_post_meta( $id, PROMOTUR_Destinos::META_TIPO_ITEM, $evento ? 'evento' : 'sitio' );
		if ( $evento && ! empty( $s['fechas']['inicio'] ) ) {
			// Mismo formato que guarda el panel: UTC, como lo deja su editor.
			$inicio = strtotime( (string) $s['fechas']['inicio'] );
			$fin    = ! empty( $s['fechas']['fin'] ) ? strtotime( (string) $s['fechas']['fin'] ) : false;
			if ( $inicio ) {
				update_post_meta( $id, PROMOTUR_Destinos::META_INICIO, gmdate( 'Y-m-d H:i:s', $inicio ) );
			}
			if ( $fin ) {
				update_post_meta( $id, PROMOTUR_Destinos::META_FIN, gmdate( 'Y-m-d H:i:s', $fin ) );
			}
		}

		$texto = array(
			'_promotur_horario'  => $s['horario'] ?? '',
			'_promotur_costo'    => $s['costo'] ?? '',
			'_promotur_contacto' => $s['contacto'] ?? '',
		);
		foreach ( $texto as $meta => $valor ) {
			if ( '' !== trim( (string) $valor ) ) {
				update_post_meta( $id, $meta, sanitize_text_field( (string) $valor ) );
			}
		}
		if ( isset( $s['rango_precio'] ) && is_numeric( $s['rango_precio'] ) ) {
			update_post_meta( $id, '_promotur_rango_precio', max( 0, min( 4, (int) $s['rango_precio'] ) ) );
		}
		if ( in_array( $s['estado_camino'] ?? '', array( 'asfalto', 'ripio', 'tierra' ), true ) ) {
			update_post_meta( $id, '_promotur_estado_camino', $s['estado_camino'] );
		}
		if ( is_numeric( $s['lat'] ?? null ) && is_numeric( $s['lng'] ?? null ) ) {
			update_post_meta( $id, '_promotur_lat', (string) (float) $s['lat'] );
			update_post_meta( $id, '_promotur_lng', (string) (float) $s['lng'] );
		}
		if ( ! empty( $s['google_maps'] ) && 0 === strpos( (string) $s['google_maps'], 'https://' ) ) {
			update_post_meta( $id, '_promotur_maps', esc_url_raw( (string) $s['google_maps'] ) );
		}
		update_post_meta( $id, '_promotur_fuentes', self::fuentes( $s, $inv ) );

		$cat = self::termino( (string) ( $s['categoria'] ?? '' ), 'promotur_categoria' );
		if ( $cat ) {
			wp_set_object_terms( $id, array( $cat ), 'promotur_categoria' );
		}
		$etiquetas = array();
		foreach ( (array) ( $s['etiquetas'] ?? array() ) as $e ) {
			$t = self::termino( (string) $e, 'promotur_etiqueta' );
			if ( $t ) {
				$etiquetas[] = $t;
			}
		}
		if ( $etiquetas ) {
			wp_set_object_terms( $id, $etiquetas, 'promotur_etiqueta' );
		}

		return (int) $id;
	}

	/**
	 * Lo que la ficha no tiene dónde guardar —dirección, web, redes, de cuándo
	 * es el dato, las notas de la investigación— va a «Fuentes / referencias»,
	 * que es lo que lee quien revisa antes de publicar.
	 */
	public static function fuentes( array $s, array $inv ) {
		$lineas = array();
		$par    = static function ( $etiqueta, $valor ) use ( &$lineas ) {
			$valor = trim( (string) $valor );
			if ( '' !== $valor ) {
				$lineas[] = $etiqueta . ': ' . $valor;
			}
		};
		$par( 'Dirección', $s['direccion'] ?? '' );
		$par( 'Barrio o compañía', $s['compania_o_barrio'] ?? '' );
		$par( 'Ubicación', $s['ubicacion_precision'] ?? '' );
		$par( 'Web', $s['web'] ?? '' );
		$par( 'Redes', implode( ' · ', array_filter( (array) ( $s['redes'] ?? array() ) ) ) );
		if ( ! empty( $s['fechas']['recurrencia'] ) ) {
			$par( 'Cuándo', $s['fechas']['recurrencia'] . ( empty( $s['fechas']['proxima_confirmada'] ) ? ' (próxima fecha sin confirmar)' : '' ) );
		}
		$par( 'Dato de', $s['fecha_dato'] ?? '' );
		$par( 'Vigencia', $s['vigencia'] ?? '' );
		$par( 'Notas', $s['notas'] ?? '' );
		$fuentes = array_filter( array_map( 'strval', (array) ( $s['fuentes'] ?? array() ) ) );
		if ( $fuentes ) {
			$lineas[] = '';
			$lineas   = array_merge( $lineas, $fuentes );
		}
		$lineas[] = '';
		$lineas[] = sprintf( 'Datos de prueba: %s, %s.', $inv['herramienta'] ?: 'investigación', $inv['generado'] ?: '' );
		return sanitize_textarea_field( implode( "\n", $lineas ) );
	}

	/**
	 * Un término por su nombre. Si no existe se crea, y queda anotado para
	 * borrarlo con los datos de prueba si nadie más lo usa.
	 */
	private static function termino( $nombre, $taxonomia ) {
		$nombre = trim( $nombre );
		if ( '' === $nombre || ! taxonomy_exists( $taxonomia ) ) {
			return 0;
		}
		$t = get_term_by( 'name', $nombre, $taxonomia );
		if ( $t ) {
			return (int) $t->term_id;
		}
		$nuevo = wp_insert_term( $nombre, $taxonomia );
		if ( is_wp_error( $nuevo ) ) {
			return 0;
		}
		$creados   = (array) get_option( self::OPT_TERMINOS, array() );
		$creados[] = array( 'id' => (int) $nuevo['term_id'], 'tax' => $taxonomia );
		update_option( self::OPT_TERMINOS, $creados, false );
		return (int) $nuevo['term_id'];
	}

	/**
	 * Baja la foto (sólo las que vienen con licencia libre en la investigación)
	 * y la pone de portada, con su autor y su licencia como crédito.
	 */
	private static function poner_foto( $post_id, array $foto, $titulo ) {
		if ( 0 !== strpos( (string) $foto['url'], 'https://' ) ) {
			return false;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( (string) $foto['url'], 30 );
		if ( is_wp_error( $tmp ) ) {
			return false;
		}
		$adjunto = media_handle_sideload( array( 'name' => sanitize_file_name( sanitize_title( $titulo ) . '.jpg' ), 'tmp_name' => $tmp ), $post_id, $titulo );
		if ( is_wp_error( $adjunto ) ) {
			wp_delete_file( $tmp );
			return false;
		}
		update_post_meta( $adjunto, self::META, '1' );
		update_post_meta( $post_id, '_promotur_portada', (int) $adjunto );
		$credito = trim( implode( ' · ', array_filter( array( $foto['autor'] ?? '', $foto['licencia'] ?? '' ) ) ) );
		if ( '' !== $credito ) {
			update_post_meta( $post_id, '_promotur_credito_fotos', sanitize_text_field( 'Foto: ' . $credito ) );
		}
		return true;
	}

	/* ---------------------------------------------------------------------
	 * Borrar
	 * ------------------------------------------------------------------ */

	/**
	 * Saca todo lo de prueba: fichas, sus fotos y los términos creados para
	 * ellas que quedaron sin uso. Lo que el equipo cargó no se toca.
	 *
	 * @return array { fichas, fotos, terminos }
	 */
	public static function borrar() {
		$out = array( 'fichas' => 0, 'fotos' => 0, 'terminos' => 0 );

		$fotos = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		foreach ( $fotos as $id ) {
			if ( wp_delete_attachment( (int) $id, true ) ) {
				$out['fotos']++;
			}
		}
		foreach ( self::cargadas() as $id ) {
			if ( wp_delete_post( $id, true ) ) {
				$out['fichas']++;
			}
		}

		$quedan = array();
		foreach ( (array) get_option( self::OPT_TERMINOS, array() ) as $t ) {
			$term = get_term( (int) ( $t['id'] ?? 0 ), (string) ( $t['tax'] ?? '' ) );
			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}
			// Si alguien del equipo ya la usó, se queda. No sirve `count`: sólo
			// cuenta lo publicado, y un borrador del equipo también es uso.
			$en_uso = get_posts( array(
				// No 'any': deja afuera los tipos excluidos de la búsqueda.
				'post_type'      => PROMOTUR_Editorial::cpts(),
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'tax_query'      => array( array( 'taxonomy' => $term->taxonomy, 'field' => 'term_id', 'terms' => (int) $term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			) );
			if ( $en_uso ) {
				$quedan[] = $t;
				continue;
			}
			if ( ! is_wp_error( wp_delete_term( $term->term_id, $term->taxonomy ) ) ) {
				$out['terminos']++;
			}
		}
		update_option( self::OPT_TERMINOS, $quedan, false );

		if ( class_exists( 'CZUAPI_Asistente_Fuentes' ) ) {
			CZUAPI_Asistente_Fuentes::invalidar();
		}
		return $out;
	}
}
