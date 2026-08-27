<?php
/**
 * Control de la app desde el panel.
 *
 * La app Android no se publica de nuevo para cambiar una palabra o una imagen:
 * lee sus textos y sus medios de la API (`caaguazu-app-api`), que los guarda en
 * opciones. Hasta ahora esas opciones tenían endpoint pero no editor — o sea,
 * la promesa de "se cambia sin publicar un APK" existía a medias. Esta clase es
 * la otra mitad: el panel es la cabina de mando de la app.
 *
 * Tres cosas se controlan desde acá, y son exactamente las tres que la app lee
 * de servidor:
 *
 *   1. Textos de interfaz, por idioma (ES / EN / GN).
 *   2. Manifiesto de medios: qué imagen o animación va en cada clave.
 *   3. Icono y color de cada categoría, que la app usa en el mapa y las listas.
 *
 * Nada de esto se escribe a mano sobre las opciones del otro plugin: se pasa
 * por su API pública (`CZUAPI_UI_Content::set_strings()`, `set_manifest()`) y
 * por sus constantes de meta. Si mañana cambia el formato, cambia allá.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_App_Control {

	private static $instance = null;

	/** Capability que gatea toda la sección. */
	const CAP = 'promotur_manage_app';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'save_app', array( $this, 'handle_save' ) );
	}

	/* ---------------------------------------------------------------------
	 * Lectura (la usa templates/sections/app.php)
	 * ------------------------------------------------------------------ */

	/**
	 * Idiomas que maneja la app.
	 *
	 * @return string[]
	 */
	public static function locales() {
		return promotur_app_api_activa() ? (array) CZUAPI_UI_Content::LOCALES : array();
	}

	/**
	 * Textos de un idioma: los guardados, más las claves del set base que
	 * todavía nadie escribió. Se muestran todas juntas porque una clave que
	 * existe y está vacía es justamente lo que hay que ver para completarla.
	 *
	 * @param string $locale
	 * @return array clave => texto (puede ser '')
	 */
	public static function strings( $locale ) {
		if ( ! promotur_app_api_activa() ) {
			return array();
		}
		$guardados = CZUAPI_UI_Content::get_strings( $locale );
		$claves    = array_keys( CZUAPI_UI_Content::base() );

		// Las claves del set base primero y en su orden; después las que
		// agregó una persona, alfabéticas.
		$out = array();
		foreach ( $claves as $clave ) {
			$out[ $clave ] = isset( $guardados[ $clave ] ) ? $guardados[ $clave ] : '';
		}
		$extra = array_diff_key( $guardados, $out );
		ksort( $extra );

		return $out + $extra;
	}

	/**
	 * Manifiesto de medios tal como está guardado.
	 *
	 * @return array
	 */
	public static function manifest() {
		return promotur_app_api_activa() ? CZUAPI_UI_Content::get_manifest() : array();
	}

	/**
	 * Categorías con el icono y el color que usa la app.
	 *
	 * @return array[] { term_id, nombre, color, icono }
	 */
	public static function categorias() {
		if ( ! promotur_app_api_activa() ) {
			return array();
		}
		$terms = get_terms( array(
			'taxonomy'   => CZUAPI_Taxonomias::TAX_CATEGORIA,
			'hide_empty' => false,
		) );
		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$out = array();
		foreach ( $terms as $term ) {
			$out[] = array(
				'term_id' => (int) $term->term_id,
				'nombre'  => $term->name,
				'color'   => (string) get_term_meta( $term->term_id, CZUAPI_Taxonomias::META_COLOR, true ),
				'icono'   => (string) get_term_meta( $term->term_id, CZUAPI_Taxonomias::META_ICONO, true ),
			);
		}
		return $out;
	}

	/* ---------------------------------------------------------------------
	 * Escritura
	 * ------------------------------------------------------------------ */

	/**
	 * Un solo endpoint para los tres bloques: cada formulario manda cuál es el
	 * suyo en `bloque`, y sólo se toca eso. Guardar los textos no puede pisar
	 * los medios de rebote.
	 */
	public function handle_save() {
		if ( ! promotur_app_api_activa() || ! caaguazu_account_can( 'promotor', self::CAP ) ) {
			wp_die( esc_html__( 'No tenés autorización para hacer esto.', 'caaguazu-portal' ), '', array( 'response' => 403 ) );
		}

		$bloque  = isset( $_POST['bloque'] ) ? sanitize_key( wp_unslash( $_POST['bloque'] ) ) : '';
		$destino = promotur_url( 'panel/app' );

		switch ( $bloque ) {
			case 'textos':
				$locale  = isset( $_POST['locale'] ) ? sanitize_key( wp_unslash( $_POST['locale'] ) ) : '';
				$destino = add_query_arg( 'idioma', $locale, $destino );
				$this->guardar_textos( $locale );
				break;

			case 'medios':
				$this->guardar_medios();
				break;

			case 'categorias':
				$this->guardar_categorias();
				break;
		}

		wp_safe_redirect( $destino );
		exit;
	}

	/**
	 * @param string $locale
	 */
	private function guardar_textos( $locale ) {
		if ( ! in_array( $locale, self::locales(), true ) ) {
			return;
		}

		$textos = isset( $_POST['textos'] ) ? (array) wp_unslash( $_POST['textos'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$limpio = array();
		foreach ( $textos as $clave => $valor ) {
			$clave = sanitize_text_field( $clave );
			$valor = sanitize_textarea_field( $valor );
			// Un valor vacío no se guarda: guardarlo sería pisar el texto que
			// la app ya trae con una cadena en blanco. Sin la clave, la app cae
			// a su respaldo, que es lo que corresponde.
			if ( '' === $clave || '' === trim( $valor ) ) {
				continue;
			}
			$limpio[ $clave ] = $valor;
		}

		// Claves nuevas que se agregaron en el mismo envío.
		$nuevas  = isset( $_POST['nueva_clave'] ) ? (array) wp_unslash( $_POST['nueva_clave'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$valores = isset( $_POST['nuevo_valor'] ) ? (array) wp_unslash( $_POST['nuevo_valor'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $nuevas as $i => $clave ) {
			$clave = sanitize_text_field( $clave );
			$valor = isset( $valores[ $i ] ) ? sanitize_textarea_field( $valores[ $i ] ) : '';
			if ( '' === $clave || '' === trim( $valor ) ) {
				continue;
			}
			$limpio[ $clave ] = $valor;
		}

		CZUAPI_UI_Content::set_strings( $locale, $limpio );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	private function guardar_medios() {
		$medios = isset( $_POST['medios'] ) ? (array) wp_unslash( $_POST['medios'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		$manifiesto = array();
		foreach ( $medios as $entrada ) {
			if ( ! is_array( $entrada ) ) {
				continue;
			}
			$clave = isset( $entrada['clave'] ) ? sanitize_key( $entrada['clave'] ) : '';
			$medio = isset( $entrada['medio'] ) ? trim( (string) $entrada['medio'] ) : '';
			if ( '' === $clave || '' === $medio ) {
				continue;
			}

			$item = array( 'tipo' => isset( $entrada['tipo'] ) ? sanitize_key( $entrada['tipo'] ) : 'imagen' );

			// Un número es un adjunto de la mediateca; cualquier otra cosa, una
			// URL. Se decide acá para que quien carga no tenga que elegir entre
			// dos campos que hacen lo mismo.
			if ( ctype_digit( $medio ) ) {
				$item['id'] = (int) $medio;
			} else {
				$item['url'] = esc_url_raw( $medio );
			}

			if ( 'animacion' === $item['tipo'] ) {
				$item['formato'] = isset( $entrada['formato'] ) ? sanitize_key( $entrada['formato'] ) : 'lottie';
			} else {
				$item['alt'] = isset( $entrada['alt'] ) ? sanitize_text_field( $entrada['alt'] ) : '';
			}

			$manifiesto[ $clave ] = $item;
		}

		CZUAPI_UI_Content::set_manifest( $manifiesto );
		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}

	private function guardar_categorias() {
		$cats = isset( $_POST['categorias'] ) ? (array) wp_unslash( $_POST['categorias'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		foreach ( $cats as $term_id => $datos ) {
			$term_id = (int) $term_id;
			if ( $term_id <= 0 || ! is_array( $datos ) ) {
				continue;
			}
			$color = isset( $datos['color'] ) ? sanitize_hex_color( $datos['color'] ) : '';
			$icono = isset( $datos['icono'] ) ? sanitize_key( $datos['icono'] ) : '';

			// Se borra el meta en vez de guardarlo vacío: así la app distingue
			// "sin color elegido" de "color en blanco".
			if ( $color ) {
				update_term_meta( $term_id, CZUAPI_Taxonomias::META_COLOR, $color );
			} else {
				delete_term_meta( $term_id, CZUAPI_Taxonomias::META_COLOR );
			}
			if ( $icono ) {
				update_term_meta( $term_id, CZUAPI_Taxonomias::META_ICONO, $icono );
			} else {
				delete_term_meta( $term_id, CZUAPI_Taxonomias::META_ICONO );
			}
		}

		promotur_flash( __( 'Guardado', 'caaguazu-portal' ), 'success' );
	}
}
