<?php
/**
 * Categorías, zonas y etiquetas de las fichas.
 *
 * Esta pantalla también era un cartel —«gestioná estos elementos desde
 * WordPress»— con tres botones a `edit-tags.php`. Ahora se hace acá.
 *
 * Un detalle que no es de forma: borrar una categoría que está en uso deja
 * fichas sin clasificar y la app sin de dónde agruparlas, así que no se borra
 * nada que tenga fichas colgando; primero hay que moverlas.
 */

defined( 'ABSPATH' ) || exit;

class PROMOTUR_Estructura {

	const CAP = 'promotur_manage_structure';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'estructura_crear', array( $this, 'crear' ), self::CAP );
		PROMOTUR_Acciones::formulario( 'estructura_guardar', array( $this, 'guardar' ), self::CAP );
		PROMOTUR_Acciones::formulario( 'estructura_borrar', array( $this, 'borrar' ), self::CAP );
	}

	/**
	 * Las tres taxonomías del panel, con cómo se llaman para la gente.
	 *
	 * @return array clave => array{titulo,singular,ayuda}
	 */
	public static function grupos() {
		return array(
			'promotur_categoria' => array(
				'titulo'   => __( 'Categorías', 'caaguazu-portal' ),
				'singular' => __( 'Categoría', 'caaguazu-portal' ),
				// Una categoría tiene pantalla propia en la app, y esa pantalla
				// necesita algo más que un nombre. Una etiqueta no: es un chip
				// de filtro, y pedirle una foto y un párrafo a cada una sería
				// trabajo que nadie va a hacer.
				'extras'   => true,
			),
			'promotur_etiqueta' => array(
				'titulo'   => __( 'Etiquetas', 'caaguazu-portal' ),
				'singular' => __( 'Etiqueta', 'caaguazu-portal' ),
				'extras'   => false,
			),
		);
	}

	/** ¿Esta taxonomía lleva descripción e imagen? */
	public static function tiene_extras( $taxonomia ) {
		$grupos = self::grupos();
		return ! empty( $grupos[ $taxonomia ]['extras'] );
	}

	/**
	 * La meta key donde vive el adjunto de la imagen de un término.
	 *
	 * La constante vive en `caaguazu-app-api`, que es quien define qué lee la
	 * app. Si ese plugin no está, se usa el literal: el panel tiene que poder
	 * guardar igual, y el día que la API vuelva encuentra el dato donde va.
	 *
	 * @return string
	 */
	public static function meta_imagen() {
		return class_exists( 'CZUAPI_Taxonomias' ) ? CZUAPI_Taxonomias::META_IMAGEN : 'czuapi_imagen_id';
	}

	/**
	 * La meta key donde vive el nombre traducido de un término, en un idioma.
	 * Mismo acuerdo que `meta_imagen()`: la constante la define
	 * `caaguazu-app-api` —es quien la sirve—, y acá se cae a un literal si esa
	 * API no está activa, para poder guardar igual.
	 *
	 * @param string $locale
	 * @return string
	 */
	public static function meta_i18n( $locale ) {
		$prefijo = class_exists( 'CZUAPI_Taxonomias' ) ? CZUAPI_Taxonomias::META_I18N_PREFIJO : 'czuapi_i18n_';
		return $prefijo . $locale;
	}

	/** Los términos de una taxonomía, con cuántas fichas usan cada uno. */
	public static function terminos( $taxonomia ) {
		$terms = get_terms( array(
			'taxonomy'   => $taxonomia,
			'hide_empty' => false,
			'orderby'    => 'name',
		) );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	private function volver( $mensaje, $tipo = 'success' ) {
		promotur_flash( $mensaje, $tipo );
		wp_safe_redirect( promotur_url( 'panel/estructura' ) );
		exit;
	}

	/** La taxonomía que vino en el pedido, si es una de las nuestras. */
	private function taxonomia() {
		$tax = isset( $_POST['taxonomia'] ) ? sanitize_key( wp_unslash( $_POST['taxonomia'] ) ) : '';
		if ( ! isset( self::grupos()[ $tax ] ) ) {
			$this->volver( __( 'Eso no se puede editar desde acá.', 'caaguazu-portal' ), 'error' );
		}
		return $tax;
	}

	public function crear() {
		$tax    = $this->taxonomia();
		$nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
		if ( '' === $nombre ) {
			$this->volver( __( 'Escribí un nombre.', 'caaguazu-portal' ), 'error' );
		}
		if ( term_exists( $nombre, $tax ) ) {
			$this->volver( __( 'Ya existe una con ese nombre.', 'caaguazu-portal' ), 'error' );
		}

		$creado = wp_insert_term( $nombre, $tax );
		if ( is_wp_error( $creado ) ) {
			$this->volver( $creado->get_error_message(), 'error' );
		}
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'estructura_creada', array(
				'entity_type' => 'term',
				'entity_id'   => (int) $creado['term_id'],
				'payload'     => array( 'taxonomia' => $tax, 'nombre' => $nombre ),
			) );
		}
		/* translators: %s = el nombre que se creó */
		$this->volver( sprintf( __( 'Creamos «%s».', 'caaguazu-portal' ), $nombre ) );
	}

	public function guardar() {
		$tax    = $this->taxonomia();
		$id     = isset( $_POST['term_id'] ) ? (int) $_POST['term_id'] : 0;
		$nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
		if ( ! $id || '' === $nombre ) {
			$this->volver( __( 'Escribí un nombre.', 'caaguazu-portal' ), 'error' );
		}

		$campos = array( 'name' => $nombre );

		// Descripción e imagen sólo donde corresponden (ver grupos()). La
		// descripción va al campo nativo del término y no a un meta propio:
		// WordPress ya lo tiene, y tenerlo dos veces es tenerlo desincronizado.
		if ( self::tiene_extras( $tax ) && isset( $_POST['descripcion'] ) ) {
			$campos['description'] = sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ) );
		}

		$hecho = wp_update_term( $id, $tax, $campos );
		if ( is_wp_error( $hecho ) ) {
			$this->volver( $hecho->get_error_message(), 'error' );
		}

		if ( self::tiene_extras( $tax ) && isset( $_POST['imagen'] ) ) {
			$imagen = (int) $_POST['imagen'];
			// Un 0 es «sin imagen» y borra el meta, en vez de guardar un cero
			// que después hay que salir a interpretar.
			if ( $imagen > 0 ) {
				update_term_meta( $id, self::meta_imagen(), $imagen );
			} else {
				delete_term_meta( $id, self::meta_imagen() );
			}
		}

		// El nombre traducido, en las dos taxonomías —una etiqueta es un chip
		// de filtro en la misma pantalla que una categoría, y se lee en el
		// mismo idioma—. Gatea por `promotur_traducir` y no por `self::CAP`:
		// las dos capabilities las tiene hoy sólo Profesor, pero traducir es
		// el permiso que efectivamente describe esta acción, y es el que hay
		// que revisar si el día de mañana se separan.
		if ( promotur_can( PROMOTUR_Traducciones::CAP ) && isset( $_POST['i18n'] ) && is_array( $_POST['i18n'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$i18n = wp_unslash( $_POST['i18n'] );
			foreach ( PROMOTUR_Traducciones::idiomas() as $locale => $nombre_idioma ) {
				if ( ! isset( $i18n[ $locale ] ) || ! is_string( $i18n[ $locale ] ) ) {
					continue;
				}
				$texto = sanitize_text_field( $i18n[ $locale ] );
				if ( '' !== $texto ) {
					update_term_meta( $id, self::meta_i18n( $locale ), $texto );
				} else {
					delete_term_meta( $id, self::meta_i18n( $locale ) );
				}
			}
		}

		$this->volver( __( 'Listo, guardamos los cambios.', 'caaguazu-portal' ) );
	}

	public function borrar() {
		$tax = $this->taxonomia();
		$id  = isset( $_POST['term_id'] ) ? (int) $_POST['term_id'] : 0;
		$term = $id ? get_term( $id, $tax ) : null;
		if ( ! $term || is_wp_error( $term ) ) {
			$this->volver( __( 'Eso ya no existe.', 'caaguazu-portal' ), 'error' );
		}

		if ( (int) $term->count > 0 ) {
			$this->volver( sprintf(
				/* translators: %d = cuántas fichas la usan */
				_n( 'No la borramos: %d ficha la usa. Movelas primero.', 'No la borramos: %d fichas la usan. Movelas primero.', (int) $term->count, 'caaguazu-portal' ),
				(int) $term->count
			), 'error' );
		}

		wp_delete_term( $id, $tax );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'estructura_borrada', array(
				'entity_type' => 'term',
				'entity_id'   => $id,
				'payload'     => array( 'taxonomia' => $tax, 'nombre' => $term->name ),
			) );
		}
		/* translators: %s = el nombre que se borró */
		$this->volver( sprintf( __( 'Borramos «%s».', 'caaguazu-portal' ), $term->name ) );
	}
}
