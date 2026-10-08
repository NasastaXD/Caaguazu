<?php
/**
 * El idioma de una respuesta de contenido.
 *
 * CÓMO SE ELIGE
 *
 * Por `?idioma=`, y si no viene, por la cabecera `Accept-Language`. El
 * parámetro gana sobre la cabecera a propósito: la app deja elegir el idioma
 * adentro, y esa elección tiene que poder ser distinta a la del sistema
 * operativo del teléfono.
 *
 * Un idioma que no existe NO es un error: se cae al castellano y se sigue. Un
 * 400 acá dejaría una app sin contenido por haber pedido `fr`, cuando lo
 * correcto —y lo que el usuario espera— es ver el contenido igual. La
 * respuesta dice en qué idioma salió, así que el cliente se entera.
 *
 * QUÉ AGREGA A CADA RESPUESTA
 *
 *   idioma      en qué idioma están los textos de este objeto
 *   traducido   si TODOS sus campos traducibles estaban traducidos
 *
 * `traducido` importa porque la caída al original es campo por campo, no de a
 * una pieza entera: una ficha puede tener el título en inglés y la descripción
 * en castellano. Sin ese dato, el cliente muestra una mezcla de dos idiomas sin
 * poder avisar de nada; con él, puede poner «parcialmente traducido» y que la
 * persona entienda lo que está viendo.
 *
 * DÓNDE VIVE LA VERDAD
 *
 * En `caaguazu-portal`: la lista de idiomas, la de campos traducibles y los
 * textos guardados son del panel, que es donde se escriben. Acá no hay una
 * segunda copia de nada — sólo la traducción de esos nombres de campo a las
 * claves con las que cada endpoint ya sirve su contenido.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZUAPI_Idiomas {

	private static $instance = null;

	/** Cuando el panel no está (no debería pasar: es dependencia dura). */
	const FALLBACK = 'es';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function register_routes() {
		register_rest_route( CZUAPI_NS, '/idiomas', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'listar' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Los idiomas en los que se puede pedir contenido.
	 *
	 * @return string[]
	 */
	public static function soportados() {
		if ( class_exists( 'PROMOTUR_Traducciones' ) ) {
			return PROMOTUR_Traducciones::idiomas_api();
		}
		return array( self::FALLBACK );
	}

	/**
	 * El idioma pedido, ya resuelto contra lo que existe.
	 *
	 * @param WP_REST_Request $request
	 * @return string
	 */
	public static function del_pedido( $request ) {
		$soportados = self::soportados();

		$pedido = $request instanceof WP_REST_Request ? (string) $request->get_param( 'idioma' ) : '';
		$pedido = strtolower( trim( $pedido ) );
		if ( $pedido && in_array( $pedido, $soportados, true ) ) {
			return $pedido;
		}

		// Accept-Language, sólo si no vino el parámetro. Se mira nada más el
		// código de idioma: `pt-BR` y `pt-PT` son el mismo portugués para lo
		// que acá se sirve.
		$cabecera = $request instanceof WP_REST_Request ? (string) $request->get_header( 'accept_language' ) : '';
		foreach ( self::preferencias( $cabecera ) as $codigo ) {
			if ( in_array( $codigo, $soportados, true ) ) {
				return $codigo;
			}
		}

		return self::FALLBACK;
	}

	/**
	 * Los idiomas de un `Accept-Language`, del más querido al menos.
	 *
	 * @param string $cabecera
	 * @return string[] códigos de dos letras
	 */
	private static function preferencias( $cabecera ) {
		if ( '' === trim( (string) $cabecera ) ) {
			return array();
		}

		$items = array();
		foreach ( explode( ',', $cabecera ) as $parte ) {
			$parte  = trim( $parte );
			$trozos = explode( ';', $parte );
			$codigo = strtolower( trim( $trozos[0] ) );
			$codigo = substr( $codigo, 0, 2 );
			if ( ! preg_match( '/^[a-z]{2}$/', $codigo ) ) {
				continue;
			}
			$q = 1.0;
			if ( isset( $trozos[1] ) && preg_match( '/q=([0-9.]+)/', $trozos[1], $m ) ) {
				$q = (float) $m[1];
			}
			// El primero gana ante empate: `Accept-Language` está ordenado.
			if ( ! isset( $items[ $codigo ] ) || $q > $items[ $codigo ] ) {
				$items[ $codigo ] = $q;
			}
		}
		arsort( $items );
		return array_keys( $items );
	}

	/**
	 * Los textos de una pieza en un idioma, listos para pisar la salida.
	 *
	 * @param int    $post_id
	 * @param string $locale
	 * @return array { textos: clave=>texto, traducido: bool, idioma: string }
	 */
	public static function textos( $post_id, $locale ) {
		if ( ! class_exists( 'PROMOTUR_Traducciones' ) || self::FALLBACK === $locale ) {
			return array( 'textos' => array(), 'traducido' => false, 'idioma' => self::FALLBACK );
		}
		$r = PROMOTUR_Traducciones::resolver( (int) $post_id, $locale );
		return array(
			'textos'    => $r['textos'],
			'traducido' => (bool) $r['completo'],
			'idioma'    => $r['idioma'],
		);
	}

	/**
	 * Pisa las claves de una respuesta con los textos del idioma pedido.
	 *
	 * `$mapa` traduce nombre de campo del panel → clave de la respuesta, que
	 * NO siempre coinciden: el panel llama `cuerpo` a lo que un artículo sirve
	 * como `cuerpo_html`, y `resumen` a lo que un recorrido sirve igual pero
	 * una ficha sirve como `descripcion`. Ese desajuste es viejo y es de la
	 * API; corregirlo rompería clientes publicados, así que se resuelve acá,
	 * en un solo lugar y a la vista.
	 *
	 * @param array  $salida  la respuesta ya armada en castellano
	 * @param int    $post_id
	 * @param string $locale
	 * @param array  $mapa    clave del panel => clave de la respuesta
	 * @return array
	 */
	public static function aplicar( array $salida, $post_id, $locale, array $mapa ) {
		$r = self::textos( $post_id, $locale );

		$salida['idioma']    = $r['idioma'];
		$salida['traducido'] = $r['traducido'];

		if ( ! $r['textos'] ) {
			return $salida;
		}

		foreach ( $mapa as $campo => $clave ) {
			if ( ! isset( $r['textos'][ $campo ] ) ) {
				continue;
			}
			// Sólo se pisa lo que la respuesta ya traía: si un endpoint no
			// sirve un campo, traducirlo no puede hacérselo aparecer.
			if ( ! array_key_exists( $clave, $salida ) ) {
				continue;
			}
			$salida[ $clave ] = $r['textos'][ $campo ];
		}

		return $salida;
	}

	/* --------------------------------------------------------------------- */

	public function listar( $request ) {
		$idiomas = array();

		// El original primero, siempre: es en el que está escrito todo y el
		// que se sirve cuando falta una traducción.
		$idiomas[] = array(
			'codigo'   => self::FALLBACK,
			'nombre'   => 'Español',
			'original' => true,
		);

		if ( class_exists( 'PROMOTUR_Traducciones' ) ) {
			$nombres = array(
				'en' => 'English',
				'pt' => 'Português',
				'gn' => "Avañe'ẽ",
			);
			foreach ( PROMOTUR_Traducciones::idiomas() as $codigo => $nombre_es ) {
				$idiomas[] = array(
					'codigo'   => $codigo,
					// El nombre en su propio idioma, que es como se escribe un
					// selector de idioma: nadie busca «Inglés» en una lista que
					// está mirando porque no entiende el castellano.
					'nombre'   => isset( $nombres[ $codigo ] ) ? $nombres[ $codigo ] : $nombre_es,
					'original' => false,
				);
			}
		}

		return CZUAPI_Response::with_etag( array(
			'original'    => self::FALLBACK,
			'idiomas'     => $idiomas,
			// Cómo se pide, dicho en la propia respuesta: es la clase de dato
			// que si no está acá termina sólo en un documento que se pierde.
			'como_pedir'  => 'Agregá ?idioma=<codigo> a cualquier endpoint de contenido, o mandá Accept-Language. Un idioma desconocido no es error: se sirve el original.',
			'sin_traducir' => 'Cada objeto trae `idioma` y `traducido`. Con `traducido: false` alguno de sus textos vino en el original.',
		), $request, 3600 );
	}
}
