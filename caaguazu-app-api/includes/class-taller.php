<?php
/**
 * El taller de cuentos: la IA del sitio, prestada a un HTML de escritura.
 *
 * El taller es un archivo HTML suelto que no guarda ninguna key. Entra con la
 * cuenta del panel (POST /auth/login → token) y le pide a esta ruta que llame
 * al modelo. Endpoint, key y modelo por defecto son los del asistente
 * (wp-admin → Caaguazú API → Asistente): el mismo proveedor y el mismo gasto,
 * configurados en un solo lugar.
 *
 * Es una puerta a un modelo de lenguaje con el system prompt que mande el
 * cliente, así que NO es pública: hace falta un token de una cuenta con
 * `promotur_manage_asistente` (Profesor), el interruptor prendido en wp-admin,
 * y hay un tope de pedidos por cuenta y por día.
 *
 * GET  /taller  → si se puede usar, con qué modelo y cuántos pedidos quedan.
 * POST /taller  → { sistema, mensajes[], modelo?, temperatura?, max_tokens?,
 *                   extra?, stream? }. Con `stream` devuelve el SSE de la API
 *                   de chat tal cual lo manda el proveedor; sin él, JSON.
 *
 * @package CaaguazuAppApi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CZUAPI_Taller {

	const CAP              = 'promotur_manage_asistente';
	const TOPE_DIA_DEFAULT = 150;
	const MAX_SISTEMA      = 600000; // caracteres: la skill entera, con sus pasajes
	const MAX_MENSAJES     = 40;
	const MAX_MENSAJE      = 200000;
	const TIMEOUT          = 280; // un cuento entero tarda minutos, no segundos

	/** Lo único de los «parámetros extra» que pasa al proveedor. */
	const EXTRA_PERMITIDOS = array( 'thinking', 'reasoning_effort', 'reasoning', 'top_p', 'presence_penalty', 'frequency_penalty', 'stop', 'seed', 'verbosity' );

	/** @var CZUAPI_Taller|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function register_routes() {
		register_rest_route( CZUAPI_NS, '/taller', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'estado' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'llamar' ),
				'permission_callback' => '__return_true',
			),
		) );
	}

	/* --------------------------------------------------------------------- */
	/*  Configuración                                                         */
	/* --------------------------------------------------------------------- */

	/** Prendido en wp-admin y con un proveedor con key. */
	public static function activo() {
		return (bool) get_option( 'czuapi_taller_activo', 0 ) && '' !== CZUAPI_Asistente::key();
	}

	/** Pedidos por cuenta y por día. 0 = sin tope. */
	public static function tope_dia() {
		$v = get_option( 'czuapi_taller_tope_dia', '' );
		return ( '' === $v || null === $v ) ? self::TOPE_DIA_DEFAULT : absint( $v );
	}

	private static function clave_tope( $cuenta_id ) {
		return 'czuapi_taller_' . (int) $cuenta_id . '_' . gmdate( 'Ymd' );
	}

	public static function usados_hoy( $cuenta_id ) {
		return (int) get_transient( self::clave_tope( $cuenta_id ) );
	}

	/* --------------------------------------------------------------------- */
	/*  El pedido                                                             */
	/* --------------------------------------------------------------------- */

	/**
	 * Valida lo que manda el taller y arma el pedido al proveedor. Pura: se
	 * prueba sin WordPress.
	 *
	 * @param array  $body           JSON del taller
	 * @param string $modelo_defecto el del asistente
	 * @return array { ok, payload?, stream?, codigo?, mensaje? }
	 */
	public static function pedido( $body, $modelo_defecto ) {
		$body = is_array( $body ) ? $body : array();
		$mal  = static function ( $codigo, $mensaje ) {
			return array( 'ok' => false, 'codigo' => $codigo, 'mensaje' => $mensaje );
		};

		$sistema = isset( $body['sistema'] ) && is_string( $body['sistema'] ) ? $body['sistema'] : '';
		if ( mb_strlen( $sistema ) > self::MAX_SISTEMA ) {
			return $mal( 'sistema_largo', 'Las instrucciones son demasiado largas.' );
		}

		$crudos = isset( $body['mensajes'] ) && is_array( $body['mensajes'] ) ? array_values( $body['mensajes'] ) : array();
		if ( ! $crudos ) {
			return $mal( 'sin_mensajes', 'Falta el mensaje.' );
		}
		if ( count( $crudos ) > self::MAX_MENSAJES ) {
			return $mal( 'demasiados_mensajes', 'La charla es demasiado larga.' );
		}
		$mensajes = array();
		foreach ( $crudos as $m ) {
			$rol   = is_array( $m ) && isset( $m['role'] ) ? (string) $m['role'] : '';
			$texto = is_array( $m ) && isset( $m['content'] ) && is_string( $m['content'] ) ? $m['content'] : null;
			if ( ! in_array( $rol, array( 'user', 'assistant' ), true ) || null === $texto ) {
				return $mal( 'mensaje_invalido', 'Cada mensaje necesita un rol (user o assistant) y un texto.' );
			}
			if ( mb_strlen( $texto ) > self::MAX_MENSAJE ) {
				return $mal( 'mensaje_largo', 'Un mensaje es demasiado largo.' );
			}
			$mensajes[] = array( 'role' => $rol, 'content' => $texto );
		}

		$modelo = isset( $body['modelo'] ) && is_string( $body['modelo'] ) ? trim( $body['modelo'] ) : '';
		if ( '' !== $modelo && ! preg_match( '~^[A-Za-z0-9._:/@-]{1,120}$~', $modelo ) ) {
			return $mal( 'modelo_invalido', 'Ese nombre de modelo no es válido.' );
		}

		$payload = array(
			'model'    => '' !== $modelo ? $modelo : (string) $modelo_defecto,
			'messages' => array_merge(
				'' !== trim( $sistema ) ? array( array( 'role' => 'system', 'content' => $sistema ) ) : array(),
				$mensajes
			),
		);

		if ( isset( $body['temperatura'] ) && is_numeric( $body['temperatura'] ) ) {
			$payload['temperature'] = round( max( 0.0, min( 2.0, (float) $body['temperatura'] ) ), 2 );
		}
		if ( isset( $body['max_tokens'] ) && is_numeric( $body['max_tokens'] ) && (int) $body['max_tokens'] > 0 ) {
			$payload['max_tokens'] = min( 128000, (int) $body['max_tokens'] );
		}
		if ( isset( $body['extra'] ) && is_array( $body['extra'] ) ) {
			foreach ( $body['extra'] as $clave => $valor ) {
				if ( in_array( (string) $clave, self::EXTRA_PERMITIDOS, true ) ) {
					$payload[ $clave ] = $valor;
				}
			}
		}

		$stream            = ! empty( $body['stream'] );
		$payload['stream'] = $stream;

		return array( 'ok' => true, 'payload' => $payload, 'stream' => $stream );
	}

	/* --------------------------------------------------------------------- */
	/*  Rutas                                                                 */
	/* --------------------------------------------------------------------- */

	/** @return true|WP_REST_Response */
	private function guard() {
		$auth = CZUAPI_Auth::instance()->require_cap( self::CAP );
		if ( true !== $auth ) {
			return $auth;
		}
		if ( ! self::activo() ) {
			return CZUAPI_Response::error( 'taller_apagado', __( 'El taller está apagado en este sitio.', 'caaguazu-app-api' ), 503 );
		}
		return true;
	}

	public function estado( $request ) {
		$g = $this->guard();
		if ( true !== $g ) {
			return $g;
		}
		$cuenta = CZUAPI_Auth::instance()->current_account();
		$id     = (int) $cuenta['id'];
		$tope   = self::tope_dia();
		$res    = new WP_REST_Response( array(
			'disponible' => true,
			'modelo'     => CZUAPI_Asistente::modelo(),
			'cuenta'     => (string) ( $cuenta['display_name'] ?? '' ),
			'tope_dia'   => $tope,
			'usados_hoy' => self::usados_hoy( $id ),
		), 200 );
		$res->header( 'Cache-Control', 'no-store' );
		return $res;
	}

	public function llamar( $request ) {
		$g = $this->guard();
		if ( true !== $g ) {
			return $g;
		}
		$id = CZUAPI_Auth::instance()->current_account_id();

		$p = self::pedido( $request->get_json_params(), CZUAPI_Asistente::modelo() );
		if ( ! $p['ok'] ) {
			return CZUAPI_Response::error( $p['codigo'], $p['mensaje'], 400 );
		}

		$tope   = self::tope_dia();
		$usados = self::usados_hoy( $id );
		if ( $tope > 0 && $usados >= $tope ) {
			return CZUAPI_Response::error( 'tope_dia', sprintf( 'Llegaste a los %d pedidos de hoy. Mañana se renueva.', $tope ), 429 );
		}
		set_transient( self::clave_tope( $id ), $usados + 1, DAY_IN_SECONDS + HOUR_IN_SECONDS );

		$endpoint = CZUAPI_Asistente::endpoint();
		$key      = CZUAPI_Asistente::key();

		if ( $p['stream'] && function_exists( 'curl_init' ) ) {
			self::transmitir( $endpoint, $key, $p['payload'] );
			exit;
		}

		$payload           = $p['payload'];
		$payload['stream'] = false;
		$res = wp_remote_post( $endpoint, array(
			'timeout' => self::TIMEOUT,
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
		) );
		if ( is_wp_error( $res ) ) {
			return CZUAPI_Response::error( 'sin_respuesta', 'No se pudo hablar con el proveedor: ' . CZUAPI_Asistente::redactar( $res->get_error_message() ), 502 );
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		$raw  = (string) wp_remote_retrieve_body( $res );
		if ( 200 !== $code ) {
			return CZUAPI_Response::error( 'proveedor', self::mensaje_error( $code, $raw ), 502, array( 'estado' => $code ) );
		}
		$data  = json_decode( $raw, true );
		$texto = (string) ( $data['choices'][0]['message']['content'] ?? '' );
		$out   = new WP_REST_Response( array(
			'texto'  => $texto,
			'modelo' => (string) ( $data['model'] ?? $payload['model'] ),
		), 200 );
		$out->header( 'Cache-Control', 'no-store' );
		return $out;
	}

	/** Qué pasó, sin la key y sin el cuerpo entero del proveedor. */
	public static function mensaje_error( $code, $raw ) {
		$d       = CZUAPI_Asistente::diagnostico( $code, $raw );
		$detalle = '';
		$j       = json_decode( (string) $raw, true );
		if ( is_array( $j ) && isset( $j['error']['message'] ) && is_string( $j['error']['message'] ) ) {
			$detalle = ' (' . mb_substr( CZUAPI_Asistente::redactar( $j['error']['message'] ), 0, 240 ) . ')';
		}
		return sprintf( 'El proveedor respondió %d: %s%s %s', (int) $code, $d['causa'], $detalle, $d['arreglo'] );
	}

	/**
	 * Pasa el SSE del proveedor tal cual: el taller ya sabe leer el de la API
	 * de chat. Si el proveedor contesta JSON (no hace streaming) o falla, se
	 * manda un solo evento con el texto o con el error, en el mismo formato.
	 */
	private static function transmitir( $endpoint, $key, array $payload ) {
		$payload['stream'] = true;

		rest_send_cors_headers( false );
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
		@ini_set( 'zlib.output_compression', '0' ); // phpcs:ignore
		@set_time_limit( self::TIMEOUT + 20 ); // phpcs:ignore
		nocache_headers();
		header( 'Content-Type: text/event-stream; charset=utf-8' );
		header( 'X-Accel-Buffering: no' );
		header( 'X-LiteSpeed-Cache-Control: no-cache' );

		$estado = 0;
		$tipo   = '';
		$resto  = '';
		$paso   = false; // ¿pasó SSE del proveedor? Entonces su [DONE] ya llegó.
		$evento = static function ( array $dato ) {
			echo 'data: ' . wp_json_encode( $dato ) . "\n\n"; // phpcs:ignore
			flush();
		};

		$ch = curl_init( $endpoint );
		curl_setopt_array( $ch, array(
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => wp_json_encode( $payload ),
			CURLOPT_HTTPHEADER     => array( 'Authorization: Bearer ' . $key, 'Content-Type: application/json', 'Accept: text/event-stream' ),
			CURLOPT_TIMEOUT        => self::TIMEOUT,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_HEADERFUNCTION => static function ( $c, $linea ) use ( &$estado, &$tipo ) {
				if ( preg_match( '~^HTTP/\S+\s+(\d{3})~', $linea, $m ) ) {
					$estado = (int) $m[1];
				} elseif ( 0 === stripos( $linea, 'content-type:' ) ) {
					$tipo = strtolower( trim( substr( $linea, 13 ) ) );
				}
				return strlen( $linea );
			},
			CURLOPT_WRITEFUNCTION  => static function ( $c, $trozo ) use ( &$estado, &$tipo, &$resto, &$paso ) {
				// Lo que no es SSE sano (un error, un JSON entero) se junta y se
				// traduce al final; el SSE pasa en el momento.
				if ( 200 !== $estado || false === strpos( $tipo, 'event-stream' ) ) {
					if ( strlen( $resto ) < 2000000 ) {
						$resto .= $trozo;
					}
					return strlen( $trozo );
				}
				$paso = true;
				echo $trozo; // phpcs:ignore
				flush();
				return connection_aborted() ? 0 : strlen( $trozo );
			},
		) );
		curl_exec( $ch );
		$errno = curl_errno( $ch );
		$error = curl_error( $ch );
		curl_close( $ch );

		if ( 0 === $estado || ( $errno && '' === $resto && 200 !== $estado ) ) {
			$evento( array( 'error' => array( 'message' => 'No se pudo hablar con el proveedor: ' . CZUAPI_Asistente::redactar( $error ) ) ) );
		} elseif ( 200 !== $estado ) {
			$evento( array( 'error' => array( 'message' => self::mensaje_error( $estado, $resto ) ) ) );
		} elseif ( '' !== $resto ) {
			$j = json_decode( $resto, true );
			$t = (string) ( $j['choices'][0]['message']['content'] ?? '' );
			$evento( array( 'choices' => array( array( 'delta' => array( 'content' => $t ) ) ) ) );
		}
		if ( ! $paso ) {
			echo "data: [DONE]\n\n"; // phpcs:ignore
			flush();
		}
	}
}
