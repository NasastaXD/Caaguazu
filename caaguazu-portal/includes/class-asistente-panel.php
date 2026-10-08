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
	const PESTANAS = array( 'conocimiento', 'personalidad', 'memoria', 'fuentes', 'probar' );

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'save_asistente', array( $this, 'handle_save' ) );
		PROMOTUR_Acciones::datos( 'asistente_probar', array( $this, 'probar' ) );
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
		$this->registrar( 'asistente_fuentes', array( 'activas' => CZUAPI_Asistente::normalizar_fuentes( $elegidas ) ) );
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
}
