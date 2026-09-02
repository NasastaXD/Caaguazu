<?php
/**
 * Las tres puertas de las traducciones: guardar a mano, bajar el archivo,
 * subirlo traducido.
 *
 * Las tres pasan por `PROMOTUR_Acciones::formulario()`, así que heredan la
 * sesión de cuenta y el token de seguridad del panel sin reimplementarlos, y
 * las tres exigen `promotur_traducir` — que sólo tiene Profesor.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Traducciones_Acciones {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$cap = PROMOTUR_Traducciones::CAP;
		PROMOTUR_Acciones::formulario( 'traduccion_guardar', array( $this, 'guardar' ), $cap );
		PROMOTUR_Acciones::formulario( 'traduccion_bajar', array( $this, 'bajar' ), $cap );
		PROMOTUR_Acciones::formulario( 'traduccion_subir', array( $this, 'subir' ), $cap );
	}

	/**
	 * De vuelta al editor de la pieza, con el mensaje.
	 *
	 * @param int    $post_id
	 * @param string $mensaje
	 * @param string $tipo
	 */
	private function volver( $post_id, $mensaje, $tipo = 'success' ) {
		promotur_flash( $mensaje, $tipo );
		$url = PROMOTUR_Editorial::url_editor( $post_id );
		wp_safe_redirect( $url ? $url : promotur_url( 'panel' ) );
		exit;
	}

	/**
	 * El post sobre el que se opera, o corta.
	 *
	 * @return int
	 */
	private function post_objetivo() {
		$id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $id <= 0 || ! PROMOTUR_Editorial::tipo_de( $id ) ) {
			promotur_flash( __( 'Ese contenido no existe.', 'caaguazu-portal' ), 'error' );
			wp_safe_redirect( promotur_url( 'panel' ) );
			exit;
		}
		return $id;
	}

	/**
	 * El idioma que viene en el formulario, o corta.
	 *
	 * @param int $post_id para poder volver al editor si está mal
	 * @return string
	 */
	private function idioma_del_pedido( $post_id ) {
		$locale = isset( $_POST['idioma'] ) ? sanitize_key( wp_unslash( $_POST['idioma'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! PROMOTUR_Traducciones::idioma_valido( $locale ) ) {
			$this->volver( $post_id, __( 'Ese idioma no está disponible.', 'caaguazu-portal' ), 'error' );
		}
		return $locale;
	}

	/* --------------------------------------------------------------------- */

	/** Guardar los cuadros de un idioma, escritos en el panel. */
	public function guardar() {
		$post_id = $this->post_objetivo();
		$locale  = $this->idioma_del_pedido( $post_id );

		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$crudo   = isset( $_POST['t'] ) && is_array( $_POST['t'] ) ? wp_unslash( $_POST['t'] ) : array();
		$valores = array();
		foreach ( $crudo as $clave => $texto ) {
			if ( is_string( $texto ) ) {
				$valores[ (string) $clave ] = $texto;
			}
		}

		// Se pisa el idioma entero y no se fusiona: los cuadros del formulario
		// son el estado completo de ese idioma, así que vaciar uno tiene que
		// poder vaciarlo de verdad.
		$hechos = PROMOTUR_Traducciones::guardar( $post_id, $locale, $valores );

		PROMOTUR_Audit::log( 'traduccion_guardada', array(
			'entity_type' => 'contenido',
			'entity_id'   => $post_id,
			'payload'     => array( 'idioma' => $locale, 'campos' => $hechos ),
		) );

		$this->volver(
			$post_id,
			sprintf(
				/* translators: %s = nombre del idioma */
				__( 'Guardamos la traducción al %s.', 'caaguazu-portal' ),
				PROMOTUR_Traducciones::idioma_label( $locale )
			)
		);
	}

	/**
	 * Bajar el archivo.
	 *
	 * Sale por `exit` con el JSON crudo y no por una redirección: es una
	 * descarga, y el único modo de que el navegador la reciba como archivo es
	 * responderla en el mismo pedido.
	 */
	public function bajar() {
		$post_id = $this->post_objetivo();

		// phpcs:ignore WordPress.Security.NonceVerification
		$pedidos = isset( $_POST['idiomas'] ) ? (array) wp_unslash( $_POST['idiomas'] ) : array();
		$pedidos = array_map( 'sanitize_key', $pedidos );

		$datos = PROMOTUR_Traducciones_Archivo::exportar( $post_id, $pedidos );
		if ( is_wp_error( $datos ) ) {
			$this->volver( $post_id, $datos->get_error_message(), 'error' );
		}

		$json = wp_json_encode( $datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( false === $json ) {
			$this->volver( $post_id, __( 'No pudimos armar el archivo.', 'caaguazu-portal' ), 'error' );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . PROMOTUR_Traducciones_Archivo::nombre( $post_id ) . '"' );
		header( 'Content-Length: ' . strlen( $json ) );

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $json;
		exit;
	}

	/** Subir el archivo traducido. */
	public function subir() {
		$post_id = $this->post_objetivo();

		if ( empty( $_FILES['archivo']['name'] ) ) {
			$this->volver( $post_id, __( 'No elegiste ningún archivo.', 'caaguazu-portal' ), 'error' );
		}
		if ( ! empty( $_FILES['archivo']['error'] ) ) {
			$this->volver( $post_id, __( 'El archivo no llegó completo. Probá de nuevo.', 'caaguazu-portal' ), 'error' );
		}

		$nombre = sanitize_file_name( wp_unslash( $_FILES['archivo']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$ext    = strtolower( (string) pathinfo( $nombre, PATHINFO_EXTENSION ) );
		if ( 'json' !== $ext ) {
			$this->volver( $post_id, __( 'El archivo tiene que ser el .json que bajaste desde acá.', 'caaguazu-portal' ), 'error' );
		}

		$tmp = isset( $_FILES['archivo']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['archivo']['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! $tmp || ! is_uploaded_file( $tmp ) ) {
			$this->volver( $post_id, __( 'No pudimos leer el archivo.', 'caaguazu-portal' ), 'error' );
		}

		/*
		 * Un tope de tamaño antes de leerlo: el archivo son textos de UNA
		 * pieza de contenido, así que dos megas ya es diez veces lo que puede
		 * pesar. Sin el tope, subir cualquier .json enorme se lee entero en
		 * memoria antes de que el validador tenga oportunidad de rechazarlo.
		 */
		if ( filesize( $tmp ) > 2 * MB_IN_BYTES ) {
			$this->volver( $post_id, __( 'Ese archivo es demasiado grande para ser una traducción.', 'caaguazu-portal' ), 'error' );
		}

		$json      = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$resultado = PROMOTUR_Traducciones_Archivo::importar( $post_id, $json );

		if ( is_wp_error( $resultado ) ) {
			PROMOTUR_Audit::log( 'traduccion_rechazada', array(
				'entity_type' => 'contenido',
				'entity_id'   => $post_id,
				'payload'     => array( 'motivo' => $resultado->get_error_code() ),
			) );
			$this->volver( $post_id, $resultado->get_error_message(), 'error' );
		}

		PROMOTUR_Audit::log( 'traduccion_importada', array(
			'entity_type' => 'contenido',
			'entity_id'   => $post_id,
			'payload'     => array( 'idiomas' => $resultado['idiomas'] ),
		) );

		$partes = array();
		foreach ( $resultado['idiomas'] as $locale => $cantidad ) {
			$partes[] = sprintf(
				/* translators: %1$d = cantidad de campos, %2$s = idioma */
				_n( '%1$d campo en %2$s', '%1$d campos en %2$s', $cantidad, 'caaguazu-portal' ),
				$cantidad,
				PROMOTUR_Traducciones::idioma_label( $locale )
			);
		}

		$mensaje = sprintf(
			/* translators: %s = lista de idiomas con su cantidad de campos */
			__( 'Subimos la traducción: %s.', 'caaguazu-portal' ),
			implode( ', ', $partes )
		);

		// Lo que el archivo traía y el modelo ya no tiene. Se avisa en vez de
		// tragárselo: casi siempre es un archivo viejo, y quien lo subió tiene
		// que saber que esa parte no entró.
		if ( ! empty( $resultado['ignorados'] ) ) {
			$mensaje .= ' ' . sprintf(
				/* translators: %s = lista de claves */
				__( 'Ignoramos campos que ya no existen: %s.', 'caaguazu-portal' ),
				implode( ', ', $resultado['ignorados'] )
			);
		}

		$this->volver( $post_id, $mensaje );
	}
}
