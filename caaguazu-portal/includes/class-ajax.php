<?php
/**
 * Handlers del flujo editorial: guardar y enviar contenido, asignar, aprobar,
 * devolver y subir medios.
 *
 * Un solo par de handlers —`save_contenido` / `submit_contenido`— para los tres
 * tipos de contenido del panel. La alternativa era tener `save_destino`,
 * `save_articulo` y `save_recorrido` con el 90 % del código repetido, y con eso
 * viene lo de siempre: se arregla un permiso en uno y se olvida en los otros
 * dos. Lo que cambia por tipo está declarado en la clase del tipo (qué campos
 * tiene) y en un método corto por tipo acá abajo (qué guarda que no sea un
 * campo suelto: las paradas de un recorrido, la portada de un artículo).
 *
 * `save_destino` y `submit_destino` siguen registrados como alias del genérico.
 * No es cortesía: la cola de capturas de la Salida de campo vive en el
 * `localStorage` del teléfono de cada promotor y sincroniza contra ese nombre.
 * Cambiarlo perdería capturas que ya están hechas y todavía no subieron.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Ajax {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$map = array(
			'save_contenido'   => 'save_contenido',
			'submit_contenido' => 'submit_contenido',
			// Alias históricos (ver la cabecera del archivo).
			'save_destino'     => 'save_contenido',
			'submit_destino'   => 'submit_contenido',
			'assign_review'    => 'assign_review',
			'approve'          => 'approve',
			'return_changes'   => 'return_changes',
			'upload_media'     => 'upload_media',
		);
		// Puerta propia del panel (`/turismo-panel/datos/…`), no `admin-ajax.php`:
		// `wp_ajax_*` sólo corre para usuarios de WordPress, y acá la identidad
		// la pone `caaguazu-cuentas`.
		foreach ( $map as $action => $method ) {
			PROMOTUR_Acciones::datos( $action, array( $this, $method ) );
		}
	}

	/**
	 * Guard: capability.
	 *
	 * La sesión y el token ya los revisó `PROMOTUR_Acciones` antes de llegar
	 * acá; lo que queda es el permiso, que cambia por acción.
	 */
	private function guard( $cap ) {
		if ( $cap && ! caaguazu_account_can( 'promotor', $cap ) ) {
			wp_send_json_error( array( 'message' => __( 'No tenés permiso para hacer esto.', 'caaguazu-portal' ) ), 403 );
		}
	}

	/**
	 * El tipo que viene en el pedido. Sin `tipo`, es una ficha: es lo que
	 * mandan los alias viejos y la cola de capturas.
	 *
	 * @return string
	 */
	private function tipo_del_pedido() {
		$tipo = isset( $_POST['tipo'] ) ? sanitize_key( wp_unslash( $_POST['tipo'] ) ) : 'destino'; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! PROMOTUR_Editorial::clase( $tipo ) ) {
			wp_send_json_error( array( 'message' => __( 'Ese tipo de contenido no existe.', 'caaguazu-portal' ) ), 400 );
		}
		return $tipo;
	}

	/**
	 * Sanitiza un valor según el tipo del campo.
	 */
	private function sanitize_field( $type, $raw ) {
		switch ( $type ) {
			case 'coord':    return '' === trim( (string) $raw ) ? '' : (float) $raw;
			case 'url':      return esc_url_raw( wp_unslash( $raw ) );
			case 'textarea': return sanitize_textarea_field( wp_unslash( $raw ) );
			// Un adjunto 0 no es un adjunto: es "no hay foto". Devolver el
			// entero pelado hacía que se guardara el meta con valor 0, y el
			// checklist —que sólo mira si el valor está vacío— daba la foto por
			// cargada. Con eso, un artículo sin portada pasaba el mínimo y se
			// podía enviar a revisión. La cadena vacía es la que dispara el
			// borrado del meta más abajo.
			case 'image':    return (int) $raw > 0 ? (int) $raw : '';
			case 'select':   return sanitize_key( wp_unslash( $raw ) );
			/*
			 * El navegador manda `2026-09-14T19:00`; se guarda `2026-09-14
			 * 19:00:00`, que es el formato de fecha de WordPress y el que las
			 * consultas por rango saben comparar. Lo que no parsea se descarta
			 * en vez de guardarse: una fecha inválida en la base es peor que
			 * una fecha ausente, porque ordena mal y no se nota.
			 */
			case 'datetime':
				$crudo = trim( (string) wp_unslash( $raw ) );
				if ( '' === $crudo ) { return ''; }
				$marca = strtotime( $crudo );
				return $marca ? gmdate( 'Y-m-d H:i:s', $marca ) : '';
			default:         return sanitize_text_field( wp_unslash( $raw ) );
		}
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Guarda (o crea) una pieza de contenido de cualquiera de los tres tipos.
	 */
	public function save_contenido() {
		$this->guard( 'promotur_edit_destino' );

		$tipo  = $this->tipo_del_pedido();
		$clase = PROMOTUR_Editorial::clase( $tipo );
		$cpt   = constant( $clase . '::CPT' );

		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		$title   = sanitize_text_field( wp_unslash( $_POST['titulo'] ?? '' ) );
		$content = wp_kses_post( wp_unslash( $_POST['descripcion'] ?? '' ) );
		$excerpt = sanitize_textarea_field( wp_unslash( $_POST['entradilla'] ?? '' ) );

		if ( $post_id ) {
			if ( get_post_type( $post_id ) !== $cpt || ! promotur_puede_editar_contenido( $post_id ) ) {
				wp_send_json_error( array( 'message' => __( 'No podés editar esto.', 'caaguazu-portal' ) ), 403 );
			}
			$campos = array( 'ID' => $post_id, 'post_title' => $title, 'post_content' => $content );
			// El extracto sólo se pisa si el formulario lo trae: el editor de
			// ficha no tiene ese campo y no debe borrarlo de rebote.
			if ( isset( $_POST['entradilla'] ) ) {
				$campos['post_excerpt'] = $excerpt;
			}
			wp_update_post( $campos );
		} else {
			// post_author: usuario de servicio (WordPress exige un autor
			// válido, pero ninguna persona del panel es ya un usuario de WP).
			// El dueño real queda en el meta de cuenta.
			$post_id = wp_insert_post( array(
				'post_type'    => $cpt,
				'post_status'  => 'draft',
				'post_title'   => $title ? $title : __( '(sin título)', 'caaguazu-portal' ),
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'post_author'  => caaguazu_service_user_id(),
			) );
			if ( is_wp_error( $post_id ) ) {
				wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
			}
			promotur_set_owner( $post_id, caaguazu_account_id() );
			if ( ! get_post_meta( $post_id, '_promotur_estado', true ) ) {
				update_post_meta( $post_id, '_promotur_estado', 'borrador' );
			}
			if ( 'recorrido' === $tipo ) {
				// Marca al recorrido como del equipo, para distinguirlo de los
				// que arma la gente en la app.
				update_post_meta( $post_id, PROMOTUR_Recorridos::META_TIPO, 'prehecho' );
			}
			if ( class_exists( 'PROMOTUR_Audit' ) ) {
				PROMOTUR_Audit::log( $tipo . '_created', array( 'entity_type' => $tipo, 'entity_id' => (int) $post_id, 'payload' => array( 'title' => $title ) ) );
			}
		}

		// Metadatos del modelo del tipo.
		foreach ( call_user_func( array( $clase, 'flat_fields' ) ) as $key => $def ) {
			if ( ! isset( $_POST['meta'][ $key ] ) ) { continue; }
			$value = $this->sanitize_field( $def['type'], $_POST['meta'][ $key ] );
			if ( '' === $value || null === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Taxonomías. Categoría es jerárquica (IDs); las etiquetas se escriben
		// sueltas, separadas por comas, y wp_set_object_terms crea las que
		// falten — que es cómo se etiqueta una nota mientras se la escribe.
		if ( isset( $_POST['categoria'] ) ) {
			wp_set_object_terms( $post_id, array_map( 'intval', (array) $_POST['categoria'] ), 'promotur_categoria' );
		}
		if ( isset( $_POST['etiquetas'] ) ) {
			$crudas    = sanitize_text_field( wp_unslash( $_POST['etiquetas'] ) );
			$etiquetas = array_values( array_filter( array_map( 'trim', explode( ',', $crudas ) ) ) );
			wp_set_object_terms( $post_id, $etiquetas, 'promotur_etiqueta' );
		}

		// Lo propio de cada tipo.
		$extra = array();
		switch ( $tipo ) {
			case 'destino':
				$extra = $this->guardar_extras_destino( $post_id );
				break;
			case 'articulo':
				$extra = $this->guardar_extras_articulo( $post_id );
				break;
			case 'recorrido':
				$extra = $this->guardar_extras_recorrido( $post_id );
				break;
		}

		// Confianza progresiva: editar algo PUBLICADO sin nivel suficiente lo deja
		// en re-revisión (sin bajarlo del aire); con nivel Jr+ la edición es directa.
		$message = __( 'Borrador guardado.', 'caaguazu-portal' );
		$mine    = caaguazu_account_id();
		if ( 'publicado' === PROMOTUR_Editorial::get_estado( $post_id )
			&& promotur_es_mio( $post_id )
			&& ! PROMOTUR_Stats::can_edit_published( $mine ) ) {
			update_post_meta( $post_id, '_promotur_estado', 'en_revision' ); // sigue público (post_status intacto)
			update_post_meta( $post_id, '_promotur_reedit', 1 );
			$message = __( 'Guardado. Como editaste algo ya publicado, tendrá que pasar por una nueva revisión.', 'caaguazu-portal' );
		}

		wp_send_json_success( array_merge( array(
			'post_id'   => $post_id,
			'tipo'      => $tipo,
			'checklist' => PROMOTUR_Editorial::checklist( $post_id, $tipo ),
			'complete'  => PROMOTUR_Editorial::is_complete( $post_id, $tipo ),
			'message'   => $message,
		), $extra ) );
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Ficha: derivar el pin del enlace de Google Maps.
	 *
	 * Se hace al guardar y no al leer para que el pin quede escrito en su meta:
	 * el mapa de la app consulta latitud y longitud con una `meta_query` de
	 * rango (el bbox), y eso no se puede hacer sobre un valor que se calcula al
	 * vuelo. Sólo se completa lo que está vacío — un pin corregido a mano gana
	 * siempre sobre el que trae el enlace.
	 *
	 * @param int $post_id
	 * @return array datos extra para la respuesta
	 */
	private function guardar_extras_destino( $post_id ) {
		$maps = (string) get_post_meta( $post_id, '_promotur_maps', true );
		if ( '' === $maps ) {
			return array();
		}
		$lat = (string) get_post_meta( $post_id, '_promotur_lat', true );
		$lng = (string) get_post_meta( $post_id, '_promotur_lng', true );
		if ( '' !== $lat && '' !== $lng ) {
			return array();
		}
		$coord = PROMOTUR_Destinos::coords_desde_maps( $maps );
		if ( ! $coord ) {
			// Un enlace corto no trae el punto. No es un error: se avisa para
			// que quien carga sepa por qué le siguen pidiendo las coordenadas.
			return array( 'aviso_maps' => __( 'De ese enlace no pudimos sacar el pin (los enlaces cortos no lo traen). Cargá la latitud y la longitud a mano, o pegá el enlace largo.', 'caaguazu-portal' ) );
		}
		update_post_meta( $post_id, '_promotur_lat', $coord['lat'] );
		update_post_meta( $post_id, '_promotur_lng', $coord['lng'] );
		return array( 'coordenadas' => $coord );
	}

	/**
	 * Artículo: la portada se guarda además como imagen destacada.
	 *
	 * Dos lugares para la misma foto porque hay dos pantallas que la buscan en
	 * lugares distintos: el editor del panel la guarda en su meta, y wp-admin
	 * —y cualquier cosa de WordPress que muestre una miniatura— mira la imagen
	 * destacada. Escribir las dos evita que la nota aparezca sin foto en una de
	 * las dos.
	 *
	 * @param int $post_id
	 * @return array
	 */
	private function guardar_extras_articulo( $post_id ) {
		$att = (int) get_post_meta( $post_id, '_articulo_portada', true );
		if ( $att > 0 ) {
			set_post_thumbnail( $post_id, $att );
		} else {
			delete_post_thumbnail( $post_id );
		}
		return array();
	}

	/**
	 * Recorrido: paradas, medios y artículos vinculados.
	 *
	 * @param int $post_id
	 * @return array
	 */
	private function guardar_extras_recorrido( $post_id ) {
		$extra = array();

		$att = (int) get_post_meta( $post_id, '_recorrido_portada', true );
		if ( $att > 0 ) {
			set_post_thumbnail( $post_id, $att );
		}

		if ( isset( $_POST['paradas'] ) ) {
			$enviadas = (array) wp_unslash( $_POST['paradas'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$guardadas = PROMOTUR_Recorridos::guardar_paradas( $post_id, $enviadas );
			// Contar las que llegaron con un sitio elegido, no las filas del
			// formulario: una fila en blanco no es una parada descartada.
			$con_sitio = 0;
			foreach ( $enviadas as $fila ) {
				if ( is_array( $fila ) && ! empty( $fila['ref_id'] ) ) { $con_sitio++; }
			}
			if ( $con_sitio > count( $guardadas ) ) {
				$extra['aviso_paradas'] = sprintf(
					/* translators: %d = tope de paradas */
					__( 'Un recorrido lleva hasta %d paradas, y sin repetir el mismo sitio. Guardamos las que entraron.', 'caaguazu-portal' ),
					PROMOTUR_Recorridos::MAX_PARADAS
				);
			}
		}

		if ( isset( $_POST['medios'] ) ) {
			PROMOTUR_Recorridos::guardar_medios( $post_id, (array) wp_unslash( $_POST['medios'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
		// El campo llega siempre (aunque vacío) para poder desvincular todos.
		PROMOTUR_Recorridos::guardar_articulos( $post_id, isset( $_POST['articulos'] ) ? (array) $_POST['articulos'] : array() );

		return $extra;
	}

	/* ------------------------------------------------------------------ */

	public function submit_contenido() {
		$this->guard( 'promotur_create_draft' );

		$tipo    = $this->tipo_del_pedido();
		$clase   = PROMOTUR_Editorial::clase( $tipo );
		$post_id = (int) ( $_POST['post_id'] ?? 0 );

		if ( ! $post_id || get_post_type( $post_id ) !== constant( $clase . '::CPT' ) || ! promotur_puede_editar_contenido( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Esto no se puede enviar.', 'caaguazu-portal' ) ), 403 );
		}
		if ( ! PROMOTUR_Editorial::is_complete( $post_id, $tipo ) ) {
			wp_send_json_error( array(
				'message'   => __( 'Faltan datos obligatorios. Completá el checklist antes de enviar.', 'caaguazu-portal' ),
				'checklist' => PROMOTUR_Editorial::checklist( $post_id, $tipo ),
			) );
		}

		$vuelta = promotur_url( 'panel/mis-contenidos' );

		// Confianza progresiva: nivel "De confianza" publica directo (con auditoría).
		if ( PROMOTUR_Stats::can_publish_directly( caaguazu_account_id() ) ) {
			PROMOTUR_Editorial::set_estado( $post_id, 'publicado' );
			PROMOTUR_Editorial::add_feedback( $post_id, caaguazu_account_id(), __( 'Publicación directa por nivel de confianza. Se hará una auditoría posterior.', 'caaguazu-portal' ) );
			wp_send_json_success( array( 'message' => __( '¡Publicado! Se aplicó tu nivel de confianza.', 'caaguazu-portal' ), 'redirect' => $vuelta ) );
		}

		PROMOTUR_Editorial::set_estado( $post_id, 'enviado' );
		wp_send_json_success( array( 'message' => __( '¡Enviado a revisión!', 'caaguazu-portal' ), 'redirect' => $vuelta ) );
	}

	/* ------------------------------------------------------------------ */

	/**
	 * El post que viene en el pedido, si es contenido del panel.
	 *
	 * @return int
	 */
	private function post_en_revision() {
		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! in_array( get_post_type( $post_id ), PROMOTUR_Editorial::cpts(), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Eso no existe o no es contenido del panel.', 'caaguazu-portal' ) ) );
		}
		return $post_id;
	}

	public function assign_review() {
		$this->guard( 'promotur_review_content' );
		$post_id = $this->post_en_revision();
		PROMOTUR_Editorial::set_estado( $post_id, 'en_revision', caaguazu_account_id() );
		wp_send_json_success( array( 'message' => __( 'Te asignaste la revisión.', 'caaguazu-portal' ) ) );
	}

	public function approve() {
		$this->guard( 'promotur_publish_destino' );
		$post_id = $this->post_en_revision();
		$comment = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
		if ( $comment ) {
			PROMOTUR_Editorial::add_feedback( $post_id, caaguazu_account_id(), $comment );
		}
		PROMOTUR_Editorial::set_estado( $post_id, 'publicado' );
		wp_send_json_success( array( 'message' => __( 'Aprobado y publicado.', 'caaguazu-portal' ), 'redirect' => promotur_url( 'panel/revision' ) ) );
	}

	public function return_changes() {
		$this->guard( 'promotur_review_content' );
		$post_id = $this->post_en_revision();
		$comment = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
		if ( '' === $comment ) {
			wp_send_json_error( array( 'message' => __( 'Escribí los comentarios para el autor.', 'caaguazu-portal' ) ) );
		}
		PROMOTUR_Editorial::add_feedback( $post_id, caaguazu_account_id(), $comment );
		PROMOTUR_Editorial::set_estado( $post_id, 'necesita_cambios' );
		wp_send_json_success( array( 'message' => __( 'Devuelto al autor con comentarios.', 'caaguazu-portal' ), 'redirect' => promotur_url( 'panel/revision' ) ) );
	}

	/* ------------------------------------------------------------------ */
	public function upload_media() {
		$this->guard( 'upload_files' );
		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No recibimos ninguna imagen.', 'caaguazu-portal' ) ) );
		}
		$check = wp_check_filetype( $_FILES['file']['name'] );
		if ( ! in_array( $check['ext'], array( 'jpg', 'jpeg', 'png', 'webp', 'gif' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Solo podés subir imágenes.', 'caaguazu-portal' ) ) );
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$id = media_handle_upload( 'file', 0 );
		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}
		// Una foto es una foto, entre por el editor o por la galería: queda a
		// nombre de la cuenta y aparece en la Biblioteca del panel.
		PROMOTUR_Medios::marcar( $id );
		wp_send_json_success( array(
			'id'    => $id,
			'thumb' => wp_get_attachment_image_url( $id, 'medium' ),
		) );
	}
}
