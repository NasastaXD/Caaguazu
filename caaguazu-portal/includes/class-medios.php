<?php
/**
 * La galería del panel.
 *
 * Antes esto era un cartel que decía «abrí la biblioteca de WordPress» y un
 * enlace a `wp-admin/upload.php`. Ese enlace era la galería: no había otra.
 * Ahora las fotos se ven, se suben, se describen y se borran acá adentro.
 *
 * Los archivos siguen guardándose donde WordPress guarda los archivos —eso es
 * plomería y no lo ve nadie—, pero cada foto que entra por el panel queda
 * marcada como suya y a nombre de la cuenta que la subió, no de un usuario de
 * WordPress.
 */

defined( 'ABSPATH' ) || exit;

class PROMOTUR_Medios {

	/** Marca de que la foto entró por el panel. */
	const META_PANEL = '_promotur_media';

	/** Cuenta que la subió (mismo espacio de IDs que las fichas). */
	const META_CUENTA = '_promotur_owner';

	/** A quién hay que acreditar la foto. */
	const META_CREDITO = '_promotur_credito';

	/** Cuántas por página. */
	const POR_PAGINA = 24;

	const FORMATOS = array( 'jpg', 'jpeg', 'png', 'webp', 'gif' );

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'media_subir', array( $this, 'subir' ), 'upload_files' );
		PROMOTUR_Acciones::formulario( 'media_guardar', array( $this, 'guardar' ), 'upload_files' );
		PROMOTUR_Acciones::formulario( 'media_borrar', array( $this, 'borrar' ), 'upload_files' );
	}

	/* ----------------------------------------------------------------------
	 * Leer
	 * -------------------------------------------------------------------- */

	/**
	 * Una página de la galería.
	 *
	 * @param array $args { pagina:int, busqueda:string, mias:bool }
	 * @return array{fotos:WP_Post[],total:int,paginas:int,pagina:int}
	 */
	public static function pagina( array $args = array() ) {
		$pagina   = max( 1, (int) ( $args['pagina'] ?? 1 ) );
		$busqueda = trim( (string) ( $args['busqueda'] ?? '' ) );

		$q = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => self::POR_PAGINA,
			'paged'          => $pagina,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		if ( '' !== $busqueda ) {
			$q['s'] = $busqueda;
		}
		if ( ! empty( $args['mias'] ) ) {
			$q['meta_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'key'   => self::META_CUENTA,
				'value' => (int) caaguazu_account_id(),
			) );
		}

		$query = new WP_Query( $q );
		return array(
			'fotos'   => $query->posts,
			'total'   => (int) $query->found_posts,
			'paginas' => (int) $query->max_num_pages,
			'pagina'  => $pagina,
		);
	}

	/** El crédito de una foto. */
	public static function credito( $id ) {
		return (string) get_post_meta( (int) $id, self::META_CREDITO, true );
	}

	/** ¿Puede la cuenta actual tocar esta foto? Las propias, siempre. */
	public static function puede_editar( $id ) {
		if ( caaguazu_account_can( 'promotor', 'promotur_manage_media' ) ) {
			return true;
		}
		$duena = (int) get_post_meta( (int) $id, self::META_CUENTA, true );
		$mia   = (int) caaguazu_account_id();
		return $duena > 0 && $mia > 0 && $duena === $mia;
	}

	/**
	 * Las fichas que usan esta foto de portada. Sirve para no borrar algo que
	 * está publicado sin avisar.
	 *
	 * @return int[] IDs de ficha
	 */
	public static function usada_en( $id ) {
		$q = new WP_Query( array(
			'post_type'      => PROMOTUR_Destinos::CPT,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 5,
			'meta_query'     => array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'key'   => '_promotur_portada',
				'value' => (int) $id,
			) ),
		) );
		return array_map( 'intval', $q->posts );
	}

	/* ----------------------------------------------------------------------
	 * Escribir
	 * -------------------------------------------------------------------- */

	private function volver( $mensaje, $tipo = 'success' ) {
		promotur_flash( $mensaje, $tipo );
		$destino = wp_get_referer();
		wp_safe_redirect( $destino ? $destino : promotur_url( 'panel/biblioteca' ) );
		exit;
	}

	/**
	 * Subir. Acepta varias de una: la gente saca fotos de a tandas.
	 */
	public function subir() {
		if ( empty( $_FILES['fotos']['name'][0] ) ) {
			$this->volver( __( 'No elegiste ninguna foto.', 'caaguazu-portal' ), 'error' );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$nombres  = array_map( 'sanitize_file_name', (array) wp_unslash( $_FILES['fotos']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$subidas  = 0;
		$rechazos = 0;

		foreach ( $nombres as $i => $nombre ) {
			if ( '' === $nombre ) {
				continue;
			}
			$tipo = wp_check_filetype( $nombre );
			if ( ! in_array( $tipo['ext'], self::FORMATOS, true ) ) {
				$rechazos++;
				continue;
			}
			// media_handle_upload lee $_FILES por nombre de campo y espera un
			// archivo suelto; con un campo múltiple hay que darle uno por vuelta.
			$_FILES['promotur_foto'] = array(
				'name'     => $nombre,
				'type'     => sanitize_text_field( wp_unslash( $_FILES['fotos']['type'][ $i ] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				'tmp_name' => sanitize_text_field( wp_unslash( $_FILES['fotos']['tmp_name'][ $i ] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				'error'    => (int) $_FILES['fotos']['error'][ $i ], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				'size'     => (int) $_FILES['fotos']['size'][ $i ], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			);
			$adjunto = media_handle_upload( 'promotur_foto', 0, array(), array( 'test_form' => false ) );
			unset( $_FILES['promotur_foto'] );

			if ( is_wp_error( $adjunto ) ) {
				$rechazos++;
				continue;
			}
			self::marcar( $adjunto );
			$subidas++;
		}

		if ( ! $subidas ) {
			$this->volver( __( 'No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.', 'caaguazu-portal' ), 'error' );
		}
		if ( $rechazos ) {
			$this->volver( sprintf(
				/* translators: 1 = fotos subidas, 2 = fotos rechazadas */
				_n( 'Subimos %1$d foto. %2$d quedó afuera por el formato.', 'Subimos %1$d fotos. %2$d quedaron afuera por el formato.', $subidas, 'caaguazu-portal' ),
				$subidas,
				$rechazos
			), 'info' );
		}
		$this->volver( sprintf(
			/* translators: %d = cuántas fotos se subieron */
			_n( 'Subimos %d foto.', 'Subimos %d fotos.', $subidas, 'caaguazu-portal' ),
			$subidas
		) );
	}

	/**
	 * Deja una foto a nombre del panel y de la cuenta que la subió.
	 *
	 * Público porque el editor de fichas sube por su propio camino y la marca
	 * igual: una foto es una foto, entre por donde entre.
	 */
	public static function marcar( $adjunto ) {
		$adjunto = (int) $adjunto;
		update_post_meta( $adjunto, self::META_PANEL, 1 );
		update_post_meta( $adjunto, self::META_CUENTA, (int) caaguazu_account_id() );
		if ( function_exists( 'caaguazu_service_user_id' ) ) {
			wp_update_post( array( 'ID' => $adjunto, 'post_author' => caaguazu_service_user_id() ) );
		}
	}

	/** Guardar la descripción y el crédito de una foto. */
	public function guardar() {
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
			$this->volver( __( 'Esa foto no existe.', 'caaguazu-portal' ), 'error' );
		}
		if ( ! self::puede_editar( $id ) ) {
			$this->volver( __( 'Esa foto la subió otra persona.', 'caaguazu-portal' ), 'error' );
		}

		$titulo  = isset( $_POST['titulo'] ) ? sanitize_text_field( wp_unslash( $_POST['titulo'] ) ) : '';
		$alt     = isset( $_POST['alt'] ) ? sanitize_text_field( wp_unslash( $_POST['alt'] ) ) : '';
		$credito = isset( $_POST['credito'] ) ? sanitize_text_field( wp_unslash( $_POST['credito'] ) ) : '';

		if ( '' !== $titulo ) {
			wp_update_post( array( 'ID' => $id, 'post_title' => $titulo ) );
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		update_post_meta( $id, self::META_CREDITO, $credito );

		$this->volver( __( 'Listo, guardamos la foto.', 'caaguazu-portal' ) );
	}

	/** Borrar. No deja borrar algo que está siendo la portada de una ficha. */
	public function borrar() {
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
			$this->volver( __( 'Esa foto no existe.', 'caaguazu-portal' ), 'error' );
		}
		if ( ! self::puede_editar( $id ) ) {
			$this->volver( __( 'Esa foto la subió otra persona.', 'caaguazu-portal' ), 'error' );
		}
		$usada = self::usada_en( $id );
		if ( $usada ) {
			$this->volver( sprintf(
				/* translators: %d = cuántas fichas usan la foto */
				_n( 'No la borramos: es la portada de %d ficha. Cambiala ahí primero.', 'No la borramos: es la portada de %d fichas. Cambiala ahí primero.', count( $usada ), 'caaguazu-portal' ),
				count( $usada )
			), 'error' );
		}

		wp_delete_attachment( $id, true );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'media_borrada', array( 'entity_type' => 'attachment', 'entity_id' => $id ) );
		}
		$this->volver( __( 'Foto borrada.', 'caaguazu-portal' ) );
	}
}
