<?php
/**
 * Lo que se puede hacer con una pieza de contenido además de escribirla:
 * retirarla de revisión, despublicarla, archivarla, eliminarla y recuperarla.
 *
 * POR QUÉ ESTO NO EXISTÍA Y HACÍA FALTA
 *
 * El panel sabía llevar una ficha de borrador a publicada y no sabía traerla de
 * vuelta. Una vez publicado algo, la única operación disponible era editarlo:
 * no había forma de sacarlo de la app, ni de archivarlo, ni de borrarlo. Y de
 * los ocho estados que `PROMOTUR_Editorial::estados()` declara —cada uno con su
 * pastilla de color— tres eran inalcanzables: `aprobado`, `despublicado` y
 * `archivado` estaban escritos y nada podía ponerlos.
 *
 * ELIMINAR ES LA PAPELERA, NO LA NADA
 *
 * `wp_trash_post()` y no `wp_delete_post()`: lo borrado se puede restaurar, y
 * desde el panel — nadie del equipo tiene que abrir wp-admin para recuperar algo
 * que borró sin querer, que es la regla que este plugin viene sosteniendo desde
 * el cutover de identidad.
 *
 * Además la papelera es lo que la app necesita: `CZUAPI_Sync` engancha
 * `wp_trash_post` y deja su lápida, así que el teléfono se entera de que eso
 * dejó de existir. Un borrado a mano en la base no avisaría a nadie y la ficha
 * seguiría en la caché de cada teléfono para siempre.
 *
 * QUIÉN PUEDE QUÉ
 *
 * No se decide acá: se decide en `PROMOTUR_Editorial::transiciones()` y en
 * `puede_borrar()`, que son también los que dibujan los botones. Este archivo
 * ejecuta y vuelve a preguntar — el servidor no confía en que el botón que
 * llegó sea uno de los que él mismo ofreció.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Estados {

	private static $instance = null;

	/** Meta donde queda por qué algo salió de circulación. */
	const META_MOTIVO = '_promotur_motivo_baja';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::datos( 'cambiar_estado', array( $this, 'cambiar_estado' ) );
		PROMOTUR_Acciones::datos( 'borrar_contenido', array( $this, 'borrar' ) );
		PROMOTUR_Acciones::datos( 'restaurar_contenido', array( $this, 'restaurar' ) );
	}

	/**
	 * El post que viene en el pedido, si es contenido del panel.
	 *
	 * @return int
	 */
	private function post_del_pedido() {
		$post_id = (int) ( $_POST['post_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $post_id || ! in_array( get_post_type( $post_id ), PROMOTUR_Editorial::cpts(), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Eso no existe o no es contenido del panel.', 'caaguazu-portal' ) ), 404 );
		}
		return $post_id;
	}

	/* ---------------------------------------------------------------------
	 * Cambiar de estado
	 * ------------------------------------------------------------------ */

	/**
	 * Un solo endpoint para las cuatro transiciones, y no cuatro endpoints:
	 * la que se pide tiene que estar entre las que `transiciones()` ofrece para
	 * esa pieza y esa cuenta, así que la comprobación es la misma en los cuatro
	 * casos. Escribirla cuatro veces es escribir cuatro lugares donde
	 * equivocarse.
	 */
	public function cambiar_estado() {
		$post_id = $this->post_del_pedido();
		$pedida  = isset( $_POST['transicion'] ) ? sanitize_key( wp_unslash( $_POST['transicion'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$disponibles = PROMOTUR_Editorial::transiciones( $post_id );
		if ( ! isset( $disponibles[ $pedida ] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Eso no se puede hacer con este contenido en el estado en que está.', 'caaguazu-portal' ),
			), 403 );
		}

		$transicion = $disponibles[ $pedida ];
		$motivo     = isset( $_POST['motivo'] ) ? sanitize_textarea_field( wp_unslash( $_POST['motivo'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		PROMOTUR_Editorial::set_estado( $post_id, $transicion['estado'] );

		// El motivo queda en el hilo de feedback, que es donde el autor ya mira
		// cuando algo se movió, y no en un meta que nadie abre.
		if ( '' !== $motivo ) {
			update_post_meta( $post_id, self::META_MOTIVO, $motivo );
			PROMOTUR_Editorial::add_feedback( $post_id, caaguazu_account_id(), $motivo );
		}

		wp_send_json_success( array(
			'message'  => $this->mensaje( $pedida ),
			'estado'   => $transicion['estado'],
			'etiqueta' => PROMOTUR_Editorial::estado_label( $transicion['estado'] ),
			'clase'    => PROMOTUR_Editorial::estado_class( $transicion['estado'] ),
			'reload'   => true,
		) );
	}

	/**
	 * @param string $transicion
	 * @return string
	 */
	private function mensaje( $transicion ) {
		switch ( $transicion ) {
			case 'retirar':
				return __( 'Lo sacamos de la cola de revisión. Volvió a borrador.', 'caaguazu-portal' );
			case 'despublicar':
				return __( 'Despublicado. Ya no se ve en la app; el contenido quedó entero.', 'caaguazu-portal' );
			case 'republicar':
				return __( 'Publicado de nuevo. La app lo va a ver en su próxima sincronización.', 'caaguazu-portal' );
			case 'archivar':
				return __( 'Archivado. Lo encontrás en Mis contenidos filtrando por archivados.', 'caaguazu-portal' );
		}
		return __( 'Listo.', 'caaguazu-portal' );
	}

	/* ---------------------------------------------------------------------
	 * Papelera
	 * ------------------------------------------------------------------ */

	public function borrar() {
		$post_id = $this->post_del_pedido();

		if ( ! PROMOTUR_Editorial::puede_borrar( $post_id ) ) {
			// Se distinguen los dos motivos porque llevan a dos cosas
			// distintas: uno se resuelve despublicando, el otro no se resuelve.
			$mensaje = ( 'publicado' === PROMOTUR_Editorial::get_estado( $post_id ) )
				? __( 'Esto está publicado y la app lo está mostrando. Despublicalo primero y después borralo.', 'caaguazu-portal' )
				: __( 'No podés borrar esto.', 'caaguazu-portal' );
			wp_send_json_error( array( 'message' => $mensaje ), 403 );
		}

		$titulo = get_the_title( $post_id );
		$tipo   = PROMOTUR_Editorial::tipo_de( $post_id );

		// A la papelera, no al vacío. Y `wp_trash_post` es además lo que hace
		// que la app se entere: CZUAPI_Sync engancha ese hook y deja su lápida.
		$hecho = wp_trash_post( $post_id );
		if ( ! $hecho ) {
			wp_send_json_error( array( 'message' => __( 'No pudimos borrarlo. Probá de nuevo.', 'caaguazu-portal' ) ) );
		}

		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( $tipo . '_borrado', array(
				'entity_type' => $tipo,
				'entity_id'   => (int) $post_id,
				'payload'     => array( 'title' => $titulo ),
			) );
		}

		wp_send_json_success( array(
			'message'  => __( 'Borrado. Está en la papelera: lo podés recuperar desde Mis contenidos.', 'caaguazu-portal' ),
			'redirect' => promotur_url( 'panel/mis-contenidos' ),
		) );
	}

	public function restaurar() {
		$post_id = (int) ( $_POST['post_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || ! in_array( $post->post_type, PROMOTUR_Editorial::cpts(), true ) || 'trash' !== $post->post_status ) {
			wp_send_json_error( array( 'message' => __( 'Eso no está en la papelera.', 'caaguazu-portal' ) ), 404 );
		}
		if ( ! promotur_es_mio( $post_id ) && ! promotur_can( 'promotur_review_content' ) ) {
			wp_send_json_error( array( 'message' => __( 'No podés recuperar esto.', 'caaguazu-portal' ) ), 403 );
		}

		wp_untrash_post( $post_id );

		/*
		 * `wp_untrash_post()` devuelve el post a su estado anterior, que en el
		 * contenido del panel puede ser `publish` — y eso lo devolvería a la
		 * app de rebote, sin que nadie lo decidiera. Se fuerza a borrador y el
		 * estado editorial acompaña: recuperar algo es traerlo de vuelta al
		 * taller, no volver a publicarlo.
		 */
		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
		PROMOTUR_Editorial::set_estado( $post_id, 'borrador' );

		wp_send_json_success( array(
			'message'  => __( 'Recuperado como borrador.', 'caaguazu-portal' ),
			'redirect' => PROMOTUR_Editorial::url_editor( $post_id ),
		) );
	}

	/* ---------------------------------------------------------------------
	 * Lectura
	 * ------------------------------------------------------------------ */

	/**
	 * Lo que esta cuenta tiene en la papelera.
	 *
	 * @param int $limite
	 * @return WP_Post[]
	 */
	public static function papelera( $limite = 50 ) {
		$args = array(
			'post_type'      => PROMOTUR_Editorial::cpts(),
			'post_status'    => 'trash',
			'posts_per_page' => (int) $limite,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);
		// Quien revisa ve la papelera entera; el resto, la suya.
		if ( ! promotur_can( 'promotur_review_content' ) ) {
			$args['meta_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'key'   => PROMOTUR_Destinos::OWNER_META,
				'value' => caaguazu_account_id(),
			) );
		}
		return get_posts( $args );
	}
}
