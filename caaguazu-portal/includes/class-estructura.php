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
			),
			'promotur_etiqueta' => array(
				'titulo'   => __( 'Etiquetas', 'caaguazu-portal' ),
				'singular' => __( 'Etiqueta', 'caaguazu-portal' ),
			),
		);
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

		$hecho = wp_update_term( $id, $tax, array( 'name' => $nombre ) );
		if ( is_wp_error( $hecho ) ) {
			$this->volver( $hecho->get_error_message(), 'error' );
		}
		$this->volver( __( 'Listo, cambiamos el nombre.', 'caaguazu-portal' ) );
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
