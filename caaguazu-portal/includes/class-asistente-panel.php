<?php
/**
 * Asistente, desde el panel: lo que sabe, cómo habla, cuánto recuerda, de qué
 * fuentes saca sus respuestas, y una prueba para conversar con él.
 *
 * El asistente vive en `caaguazu-app-api`. Esta sección lo maneja por las
 * funciones públicas de esa API. Las claves, los proveedores y el presupuesto
 * de contexto siguen en wp-admin: el panel no los ve ni los toca.
 *
 * Como las demás conexiones con otro plugin, pregunta por los MÉTODOS que usa,
 * no por la clase. Si la API del sitio es vieja o no está, la sección dice que
 * el asistente no está disponible en vez de romperse.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Asistente_Panel {

	private static $instance = null;

	/** Capability que gatea la sección: el Profesor y el admin. */
	const CAP = 'promotur_manage_asistente';

	/** Pestañas de la sección, en orden. */
	const PESTANAS = array( 'conocimiento', 'personalidad', 'memoria', 'fuentes', 'probar', 'presentacion' );

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'save_asistente', array( $this, 'handle_save' ) );
		PROMOTUR_Acciones::datos( 'asistente_probar', array( $this, 'probar' ) );
		PROMOTUR_Acciones::datos( 'presentacion_probar_voz', array( $this, 'probar_voz' ) );
	}

	/**
	 * ¿La API del sitio trae todo lo que esta sección usa? (0.10.0 o posterior.)
	 *
	 * @return bool
	 */
	public static function disponible() {
		$metodos = array(
			'conocimiento', 'set_conocimiento', 'persona', 'set_persona',
			'memoria_turnos', 'memoria_ttl', 'set_memoria', 'olvidar_conversaciones',
			'fuentes_activas', 'set_fuentes', 'charlar', 'activo',
		);
		foreach ( $metodos as $m ) {
			if ( ! method_exists( 'CZUAPI_Asistente', $m ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * ¿La API trae la presentación? (0.11.0 o posterior.) Va aparte de
	 * `disponible()`: con una API 0.10.0 las otras cinco pestañas andan igual.
	 *
	 * @return bool
	 */
	public static function presentacion_disponible() {
		if ( ! class_exists( 'CZUAPI_Presentacion' ) ) {
			return false;
		}
		foreach ( array( 'guardar', 'voz', 'oido', 'persona', 'conocimiento', 'hablar', 'encendida', 'pista_key', 'key_fija' ) as $m ) {
			if ( ! method_exists( 'CZUAPI_Presentacion', $m ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Todo lo que la pestaña muestra, ya leído. Las keys NUNCA viajan a la
	 * página: sólo si hay una y en qué termina.
	 *
	 * @return array
	 */
	public static function presentacion() {
		if ( ! self::presentacion_disponible() ) {
			return array();
		}
		$voz  = CZUAPI_Presentacion::voz();
		$oido = CZUAPI_Presentacion::oido();
		return array(
			'activa'          => CZUAPI_Presentacion::encendida(),
			'hay_modelo'      => '' !== CZUAPI_Asistente::key(),
			'persona'         => CZUAPI_Presentacion::persona(),
			'conocimiento'    => CZUAPI_Presentacion::conocimiento(),
			'turnos'          => CZUAPI_Presentacion::memoria_turnos(),
			'etiquetas'       => CZUAPI_Presentacion::etiquetas_activas() || (bool) get_option( 'czuapi_pres_etiquetas', 0 ),
			'modelo'          => (string) get_option( 'czuapi_pres_modelo', '' ),
			'modelo_activo'   => CZUAPI_Presentacion::modelo(),
			'catalogo'        => CZUAPI_Presentacion::usa_catalogo(),
			'borradores'      => method_exists( 'CZUAPI_Presentacion', 'usa_borradores' ) && CZUAPI_Presentacion::usa_borradores(),
			'web'             => method_exists( 'CZUAPI_Presentacion', 'usa_web' ) && CZUAPI_Presentacion::usa_web(),
			'voz_proveedor'   => $voz['proveedor'],
			'voz_endpoint'    => (string) get_option( 'czuapi_pres_voz_endpoint', '' ),
			'voz_modelo'      => (string) get_option( 'czuapi_pres_voz_modelo', '' ),
			'voz_id'          => $voz['voz'],
			'voz_velocidad'   => $voz['velocidad'],
			'voz_key'         => CZUAPI_Presentacion::pista_key( $voz['key'] ),
			'voz_key_fija'    => CZUAPI_Presentacion::key_fija( 'voz' ),
			'oido_modo'       => $oido['modo'],
			'oido_proveedor'  => $oido['proveedor'],
			'oido_silencio'   => $oido['silencio'],
			'oido_endpoint'   => (string) get_option( 'czuapi_pres_oido_endpoint', '' ),
			'oido_modelo'     => (string) get_option( 'czuapi_pres_oido_modelo', '' ),
			'oido_idioma'     => $oido['idioma'],
			'oido_key'        => CZUAPI_Presentacion::pista_key( $oido['key'] ),
			'oido_key_fija'   => CZUAPI_Presentacion::key_fija( 'oido' ),
			'url'             => function_exists( 'czuwios_url' ) ? czuwios_url( 'presentacion/' ) : '',
		);
	}

	/** Pestaña pedida en la URL, o la primera si no hay una válida. */
	public static function pestana_actual() {
		$p = isset( $_GET['pestana'] ) ? sanitize_key( wp_unslash( $_GET['pestana'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		return in_array( $p, self::PESTANAS, true ) ? $p : self::PESTANAS[0];
	}

	/* ---------------------------------------------------------------------
	 * Lectura (la usa templates/sections/asistente.php)
	 * ------------------------------------------------------------------ */

	/** @return string */
	public static function conocimiento() {
		return self::disponible() ? (string) CZUAPI_Asistente::conocimiento() : '';
	}

	/** La personalidad tal como se usa hoy (la de fábrica si no hay otra). */
	public static function persona() {
		return self::disponible() ? (string) CZUAPI_Asistente::persona() : '';
	}

	/** @return int Cuántas preguntas anteriores recuerda cada charla. */
	public static function memoria_turnos() {
		return self::disponible() ? (int) CZUAPI_Asistente::memoria_turnos() : 0;
	}

	/** @return int Cuántas horas dura una charla guardada. */
	public static function memoria_horas() {
		return self::disponible() ? (int) round( CZUAPI_Asistente::memoria_ttl() / HOUR_IN_SECONDS ) : 2;
	}

	/** @return array<string,bool> Qué fuentes están prendidas. */
	public static function fuentes() {
		return self::disponible() ? CZUAPI_Asistente::fuentes_activas() : array();
	}

	/** ¿El asistente busca en internet lo que no está publicado? (API 0.11.6.) */
	public static function web() {
		return self::disponible() && method_exists( 'CZUAPI_Asistente', 'web_activa' ) && CZUAPI_Asistente::web_activa();
	}

	/** ¿El asistente lee también los borradores? (Necesita la API 0.11.5.) */
	public static function borradores() {
		return self::disponible() && method_exists( 'CZUAPI_Asistente', 'borradores_activos' ) && CZUAPI_Asistente::borradores_activos();
	}

	/* ---------------------------------------------------------------------
	 * Escritura: un solo endpoint, un bloque por formulario. Guardar la
	 * personalidad no puede pisar las fuentes, ni al revés.
	 * ------------------------------------------------------------------ */

	public function handle_save() {
		if ( ! self::disponible() || ! caaguazu_account_can( 'promotor', self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
		}

		$bloque  = isset( $_POST['bloque'] ) ? sanitize_key( wp_unslash( $_POST['bloque'] ) ) : '';
		$pestana = isset( $_POST['pestana'] ) ? sanitize_key( wp_unslash( $_POST['pestana'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! in_array( $pestana, self::PESTANAS, true ) ) {
			$pestana = self::PESTANAS[0];
		}

		switch ( $bloque ) {
			case 'conocimiento':
				$this->guardar_conocimiento();
				break;
			case 'personalidad':
				$this->guardar_personalidad();
				break;
			case 'memoria':
				$this->guardar_memoria();
				break;
			case 'fuentes':
				$this->guardar_fuentes();
				break;
			case 'olvidar':
				$this->olvidar();
				$pestana = 'memoria';
				break;
			case 'presentacion':
				$this->guardar_presentacion();
				$pestana = 'presentacion';
				break;
		}

		wp_safe_redirect( add_query_arg( 'pestana', $pestana, promotur_url( 'panel/asistente' ) ) );
		exit;
	}

	private function guardar_conocimiento() {
		$texto = isset( $_POST['conocimiento'] ) ? wp_unslash( $_POST['conocimiento'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		CZUAPI_Asistente::set_conocimiento( $texto );
		$this->registrar( 'asistente_conocimiento', array( 'caracteres' => mb_strlen( (string) $texto ) ) );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	private function guardar_personalidad() {
		$texto   = isset( $_POST['persona'] ) ? wp_unslash( $_POST['persona'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$fabrica = ! empty( $_POST['persona_fabrica'] ); // phpcs:ignore WordPress.Security.NonceVerification
		CZUAPI_Asistente::set_persona( $texto, $fabrica );
		$this->registrar( 'asistente_personalidad', array( 'caracteres' => mb_strlen( (string) $texto ), 'fabrica' => $fabrica ) );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	private function guardar_memoria() {
		$turnos = isset( $_POST['turnos'] ) ? (int) $_POST['turnos'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$horas  = isset( $_POST['horas'] ) ? (int) $_POST['horas'] : 2; // phpcs:ignore WordPress.Security.NonceVerification
		CZUAPI_Asistente::set_memoria( $turnos, $horas );
		$this->registrar( 'asistente_memoria', array(
			'turnos' => CZUAPI_Asistente::normalizar_turnos( $turnos ),
			'horas'  => CZUAPI_Asistente::normalizar_horas( $horas ),
		) );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	private function guardar_fuentes() {
		$elegidas = isset( $_POST['fuentes'] ) ? (array) wp_unslash( $_POST['fuentes'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		CZUAPI_Asistente::set_fuentes( $elegidas );
		$borradores = ! empty( $_POST['borradores'] ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( method_exists( 'CZUAPI_Asistente', 'set_borradores' ) ) {
			CZUAPI_Asistente::set_borradores( $borradores );
		}
		$web = ! empty( $_POST['web'] ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( method_exists( 'CZUAPI_Asistente', 'set_web' ) ) {
			CZUAPI_Asistente::set_web( $web );
		}
		$this->registrar( 'asistente_fuentes', array( 'activas' => CZUAPI_Asistente::normalizar_fuentes( $elegidas ), 'borradores' => $borradores, 'web' => $web ) );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	/**
	 * Toda la presentación en un solo formulario: es una sola cosa que se
	 * prepara para un día. Las keys llegan vacías salvo que se cargue una
	 * nueva, y la API entiende vacío como «dejala como está».
	 */
	private function guardar_presentacion() {
		if ( ! self::presentacion_disponible() ) {
			return;
		}
		$campos = array(
			'activo', 'catalogo', 'borradores', 'web', 'persona', 'persona_fabrica', 'conocimiento', 'turnos', 'modelo', 'etiquetas',
			'voz_proveedor', 'voz_endpoint', 'voz_key', 'voz_key_borrar', 'voz_modelo', 'voz_id', 'voz_velocidad',
			'oido_modo', 'oido_proveedor', 'oido_silencio', 'oido_endpoint', 'oido_key', 'oido_key_borrar', 'oido_modelo', 'oido_idioma',
		);
		$datos = array();
		foreach ( $campos as $c ) {
			$datos[ $c ] = isset( $_POST[ $c ] ) ? wp_unslash( $_POST[ $c ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		}
		CZUAPI_Presentacion::guardar( $datos );

		// Al registro va QUÉ cambió, nunca una key.
		$this->registrar( 'asistente_presentacion', array(
			'activa'      => ! empty( $datos['activo'] ),
			'catalogo'    => ! empty( $datos['catalogo'] ),
			'borradores'  => ! empty( $datos['borradores'] ),
			'web'         => ! empty( $datos['web'] ),
			'voz'         => (string) $datos['voz_proveedor'],
			'oido'        => (string) $datos['oido_modo'] . '/' . (string) $datos['oido_proveedor'],
			'key_voz'     => '' !== trim( (string) $datos['voz_key'] ) ? 'nueva' : ( ! empty( $datos['voz_key_borrar'] ) ? 'borrada' : 'igual' ),
			'key_oido'    => '' !== trim( (string) $datos['oido_key'] ) ? 'nueva' : ( ! empty( $datos['oido_key_borrar'] ) ? 'borrada' : 'igual' ),
			'guion'       => mb_strlen( (string) $datos['conocimiento'] ),
		) );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	private function olvidar() {
		CZUAPI_Asistente::olvidar_conversaciones();
		$this->registrar( 'asistente_olvidar', array() );
		promotur_flash( __( 'Listo: las charlas guardadas se olvidaron.', 'caaguazu-portal' ), 'success' );
	}

	/** Cambiar lo que el asistente sabe, dice o recuerda es de quien lo cambió: queda registrado. */
	private function registrar( $accion, array $payload ) {
		PROMOTUR_Audit::log( $accion, array( 'payload' => $payload ) );
	}

	/* ---------------------------------------------------------------------
	 * Prueba: el profesor conversa con el asistente como lo haría un turista.
	 * Usa la misma pipeline que la app (charlar), sin el tope por IP: quien
	 * pregunta ya está autenticado. La charla queda en la memoria por el
	 * tiempo elegido, igual que cualquier otra.
	 * ------------------------------------------------------------------ */

	public function probar() {
		if ( ! self::disponible() || ! caaguazu_account_can( 'promotor', self::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ) ), 403 );
		}
		if ( ! CZUAPI_Asistente::activo() ) {
			wp_send_json_error( array( 'message' => __( 'El asistente está apagado, o no tiene key cargada. Se enciende en la administración del sitio.', 'caaguazu-portal' ) ), 503 );
		}

		$mensaje = isset( $_POST['mensaje'] ) ? trim( sanitize_textarea_field( (string) wp_unslash( $_POST['mensaje'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' === $mensaje ) {
			wp_send_json_error( array( 'message' => __( 'Escribí una pregunta.', 'caaguazu-portal' ) ), 400 );
		}
		$mensaje      = mb_substr( $mensaje, 0, CZUAPI_Asistente::MAX_MENSAJE );
		$conversacion = isset( $_POST['conversacion'] ) ? sanitize_text_field( wp_unslash( $_POST['conversacion'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$r = CZUAPI_Asistente::charlar( $mensaje, $conversacion, 'es' );
		if ( ! $r['ok'] ) {
			wp_send_json_error( array( 'message' => __( 'No se pudo responder ahora. El motivo queda en el registro de errores del asistente.', 'caaguazu-portal' ) ), 502 );
		}

		wp_send_json_success( array(
			'respuesta'    => (string) $r['respuesta'],
			'fuentes'      => $r['fuentes'],
			'conversacion' => (string) $r['conversacion'],
		) );
	}

	/**
	 * Prueba de la voz: dice una frase con lo que está guardado y devuelve el
	 * audio para escucharlo ahí mismo. Lo que se prueba es lo GUARDADO —no lo
	 * que está escrito en el formulario—, igual que la prueba del chat.
	 */
	public function probar_voz() {
		if ( ! self::presentacion_disponible() || ! caaguazu_account_can( 'promotor', self::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ) ), 403 );
		}
		$texto = isset( $_POST['texto'] ) ? trim( sanitize_textarea_field( (string) wp_unslash( $_POST['texto'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' === $texto ) {
			wp_send_json_error( array( 'message' => __( 'Escribí una frase.', 'caaguazu-portal' ) ), 400 );
		}

		$r = CZUAPI_Presentacion::hablar( mb_substr( $texto, 0, 500 ) );
		if ( ! $r['ok'] ) {
			// El error ya viene sin keys (ver CZUAPI_Presentacion::redactar):
			// a quien prueba le sirve leer qué contestó el proveedor.
			wp_send_json_error( array( 'message' => (string) $r['error'] ), 502 );
		}
		wp_send_json_success( array(
			'audio' => 'data:' . $r['mime'] . ';base64,' . base64_encode( $r['audio'] ),
			'seg'   => round( (float) $r['seg'], 1 ),
		) );
	}
}
