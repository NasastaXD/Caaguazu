<?php
/**
 * Flujo editorial: estados, checklist de mínimos, asignación de revisor y feedback.
 *
 * Vale para los TRES tipos de contenido del panel —ficha, artículo y
 * recorrido— y no sólo para la ficha, que es como nació. El flujo (borrador →
 * enviado → en revisión → aprobado → publicado, o devuelto con cambios) es el
 * mismo para los tres, y tiene que serlo: es el acuerdo de cómo trabaja el
 * equipo, no un detalle de la ficha.
 *
 * Lo único que cambia entre tipos es QUÉ mínimos hay que cumplir, y eso lo
 * declara cada clase de contenido con dos métodos: `fields()` (de donde salen
 * los campos marcados `req`) y `checklist_extra()` (lo que no es un campo
 * suelto: que haya cuerpo, que haya dos paradas, que haya ubicación). Acá no
 * hay un solo `if` por tipo.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Editorial {

	private static $instance = null;
	const FEEDBACK_TYPE = 'promotur_feedback';

	/**
	 * Los tipos de contenido que pasan por el flujo: clave → clase que los
	 * define.
	 *
	 * La clave es la que se usa en las URLs y en el log de auditoría
	 * (`destino_publicado`, `articulo_enviado`), así que no se cambia a la
	 * ligera: hay filas viejas escritas con ella.
	 *
	 * @return array clave => nombre de clase
	 */
	public static function tipos() {
		return array(
			'destino'   => 'PROMOTUR_Destinos',
			'articulo'  => 'PROMOTUR_Articulos',
			'recorrido' => 'PROMOTUR_Recorridos',
		);
	}

	/**
	 * Los post types que pasan por el flujo.
	 *
	 * @return string[]
	 */
	public static function cpts() {
		$out = array();
		foreach ( self::tipos() as $clase ) {
			$out[] = constant( $clase . '::CPT' );
		}
		return $out;
	}

	/**
	 * De un post a su clave de tipo, o '' si ese post no es del panel.
	 *
	 * @param int|WP_Post $post
	 * @return string
	 */
	public static function tipo_de( $post ) {
		$post_type = is_object( $post ) ? $post->post_type : get_post_type( $post );
		foreach ( self::tipos() as $clave => $clase ) {
			if ( constant( $clase . '::CPT' ) === $post_type ) {
				return $clave;
			}
		}
		return '';
	}

	/**
	 * La clase que define un tipo, por clave o por post.
	 *
	 * @param string $tipo
	 * @return string|null nombre de clase
	 */
	public static function clase( $tipo ) {
		$tipos = self::tipos();
		return isset( $tipos[ $tipo ] ) ? $tipos[ $tipo ] : null;
	}

	/**
	 * Nombre en singular de un tipo, para los textos de pantalla.
	 *
	 * @param string $tipo
	 * @return string
	 */
	public static function tipo_label( $tipo ) {
		$clase = self::clase( $tipo );
		if ( ! $clase ) {
			return '';
		}
		if ( method_exists( $clase, 'singular' ) ) {
			return call_user_func( array( $clase, 'singular' ) );
		}
		return __( 'Ficha', 'caaguazu-portal' );
	}

	/**
	 * A dónde lleva el enlace para editar una pieza de contenido.
	 *
	 * @param int|WP_Post $post
	 * @return string URL del panel, o '' si el post no es del panel
	 */
	public static function url_editor( $post ) {
		$id   = is_object( $post ) ? (int) $post->ID : (int) $post;
		$tipo = self::tipo_de( $post );
		switch ( $tipo ) {
			case 'destino':
				return promotur_url( 'panel/editor/' . $id );
			case 'articulo':
				return promotur_url( 'panel/articulos/' . $id );
			case 'recorrido':
				return promotur_url( 'panel/recorridos/' . $id );
		}
		return '';
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Mantener el feedback fuera del conteo de comentarios normales.
		add_filter( 'comments_clauses', array( $this, 'hide_feedback_from_public' ) );
	}

	/**
	 * Estados del flujo: slug → label.
	 */
	public static function estados() {
		return array(
			'borrador'        => __( 'Borrador', 'caaguazu-portal' ),
			'enviado'         => __( 'Enviado', 'caaguazu-portal' ),
			'en_revision'     => __( 'En revisión', 'caaguazu-portal' ),
			'necesita_cambios'=> __( 'Necesita cambios', 'caaguazu-portal' ),
			'aprobado'        => __( 'Aprobado', 'caaguazu-portal' ),
			'publicado'       => __( 'Publicado', 'caaguazu-portal' ),
			'despublicado'    => __( 'Despublicado', 'caaguazu-portal' ),
			'archivado'       => __( 'Archivado', 'caaguazu-portal' ),
		);
	}

	public static function estado_label( $slug ) {
		$e = self::estados();
		return isset( $e[ $slug ] ) ? $e[ $slug ] : ( $slug ? $slug : __( 'Borrador', 'caaguazu-portal' ) );
	}

	public static function get_estado( $post_id ) {
		$e = get_post_meta( $post_id, '_promotur_estado', true );
		return $e ? $e : 'borrador';
	}

	public static function set_estado( $post_id, $estado, $revisor = null ) {
		update_post_meta( $post_id, '_promotur_estado', $estado );
		if ( null !== $revisor ) {
			update_post_meta( $post_id, '_promotur_revisor', (int) $revisor );
		}
		// Visibilidad pública: solo "publicado" → publish.
		$status = ( 'publicado' === $estado ) ? 'publish' : 'draft';
		if ( get_post_status( $post_id ) !== $status ) {
			wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ) );
		}
		if ( 'publicado' === $estado ) {
			update_post_meta( $post_id, '_promotur_verificado_en', current_time( 'mysql' ) );
		}
		// Auditoría del ciclo editorial (log de posts). La acción lleva el
		// tipo adelante —`articulo_publicado`, `recorrido_enviado`— para que
		// el registro siga diciendo qué se movió y no sólo que algo se movió.
		$tipo = self::tipo_de( $post_id );
		if ( $tipo && class_exists( 'PROMOTUR_Audit' ) && in_array( $estado, array( 'enviado', 'publicado', 'necesita_cambios', 'aprobado', 'despublicado', 'archivado', 'borrador' ), true ) ) {
			PROMOTUR_Audit::log( $tipo . '_' . $estado, array(
				'entity_type' => $tipo,
				'entity_id'   => (int) $post_id,
				'payload'     => array( 'title' => get_the_title( $post_id ) ),
			) );
		}
	}

	/**
	 * Qué se puede hacer con esta pieza, en este estado, con esta cuenta.
	 *
	 * FUENTE ÚNICA, y por un motivo que ya se pagó una vez: si la lista de
	 * botones la arma la plantilla y el permiso lo comprueba el handler, los
	 * dos se separan. Aparece un botón que da 403, o —peor— un handler que
	 * acepta algo que ningún botón ofrecía. Acá se declara una vez y la
	 * consumen los dos: la UI dibuja lo que devuelve esto, y el servidor
	 * rechaza lo que no esté acá adentro.
	 *
	 * LAS REGLAS, Y POR QUÉ
	 *
	 *   Retirar        Lo mandé a revisión y me arrepentí. Vuelve a borrador,
	 *                  y sólo mientras nadie lo aprobó: retirar algo ya
	 *                  aprobado le borra el trabajo a quien lo revisó.
	 *   Despublicar    Sale de la app y se conserva entero. Es la operación
	 *                  que hacía falta y no existía: hasta ahora, lo único que
	 *                  se podía hacer con algo publicado que estaba mal era
	 *                  dejarlo publicado.
	 *   Volver a publicar   Lo inverso, sin repetir la revisión.
	 *   Archivar       Sale de circulación sin borrarse. Para lo que ya no va
	 *                  pero no se quiere perder.
	 *   Eliminar       A la papelera, no a la nada: se puede restaurar desde
	 *                  el panel (ver `PROMOTUR_Estados::restaurar()`). NO se
	 *                  ofrece sobre algo publicado — primero se despublica.
	 *                  Son dos clics en vez de uno, y esa fricción es a
	 *                  propósito: lo publicado lo está leyendo gente en la app.
	 *
	 * Quién: lo que toca la visibilidad pública —publicar, despublicar— pide
	 * `promotur_publish_destino`. Lo demás lo puede el dueño sobre lo suyo, o
	 * quien revisa sobre cualquier cosa.
	 *
	 * @param int $post_id
	 * @return array clave => { label, estado, confirmar, peligro }
	 */
	public static function transiciones( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id || ! self::tipo_de( $post_id ) ) {
			return array();
		}

		$estado  = self::get_estado( $post_id );
		$mio     = promotur_es_mio( $post_id );
		$reviso  = promotur_can( 'promotur_review_content' );
		$publico = promotur_can( 'promotur_publish_destino' );
		$puedo   = $mio || $reviso;

		if ( ! $puedo && ! $publico ) {
			return array();
		}

		$out = array();

		// Retirar de revisión: sólo mientras nadie lo aprobó.
		if ( in_array( $estado, array( 'enviado', 'en_revision' ), true ) && $puedo ) {
			$out['retirar'] = array(
				'label'     => __( 'Retirar de revisión', 'caaguazu-portal' ),
				'estado'    => 'borrador',
				'confirmar' => __( '¿Sacarlo de la cola de revisión y volverlo a borrador?', 'caaguazu-portal' ),
				'peligro'   => false,
			);
		}

		if ( 'publicado' === $estado && $publico ) {
			$out['despublicar'] = array(
				'label'     => __( 'Despublicar', 'caaguazu-portal' ),
				'estado'    => 'despublicado',
				'confirmar' => __( 'Esto está publicado y la app lo está mostrando. ¿Sacarlo de circulación? El contenido se conserva entero.', 'caaguazu-portal' ),
				'peligro'   => true,
			);
		}

		if ( in_array( $estado, array( 'despublicado', 'archivado', 'aprobado' ), true ) && $publico ) {
			$out['republicar'] = array(
				'label'     => __( 'Publicar de nuevo', 'caaguazu-portal' ),
				'estado'    => 'publicado',
				'confirmar' => '',
				'peligro'   => false,
			);
		}

		if ( ! in_array( $estado, array( 'publicado', 'archivado' ), true ) && $puedo ) {
			$out['archivar'] = array(
				'label'     => __( 'Archivar', 'caaguazu-portal' ),
				'estado'    => 'archivado',
				'confirmar' => __( '¿Archivarlo? Sale de circulación y se puede recuperar cuando quieras.', 'caaguazu-portal' ),
				'peligro'   => false,
			);
		}

		return $out;
	}

	/**
	 * ¿Se puede mandar a la papelera?
	 *
	 * Lo publicado no: primero se despublica. La fricción es a propósito —
	 * borrar de un clic algo que la gente está leyendo en la app es el error
	 * que no se puede deshacer con un «uy».
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public static function puede_borrar( $post_id ) {
		if ( ! self::tipo_de( $post_id ) ) {
			return false;
		}
		if ( 'publicado' === self::get_estado( $post_id ) ) {
			return false;
		}
		return promotur_es_mio( $post_id ) || promotur_can( 'promotur_review_content' );
	}

	/**
	 * Checklist de mínimos: cada ítem { key, label, done }.
	 *
	 * Se arma con lo que declara la clase del tipo, en este orden:
	 *
	 *   1. el título, que lo tienen los tres;
	 *   2. sus campos marcados `req` en `fields()`;
	 *   3. lo que agregue `checklist_extra()` — el cuerpo del artículo, las
	 *      dos paradas del recorrido, la ubicación de la ficha.
	 *
	 * `key` es lo que el JavaScript usa para tachar el ítem en vivo mientras
	 * se escribe: coincide con el `data-check` del campo del formulario.
	 *
	 * @param int    $post_id
	 * @param string $tipo si no se pasa, se deduce del post
	 * @return array[]
	 */
	public static function checklist( $post_id, $tipo = '' ) {
		if ( '' === $tipo ) {
			$tipo = $post_id ? self::tipo_de( $post_id ) : 'destino';
		}
		$clase = self::clase( $tipo );
		if ( ! $clase ) {
			return array();
		}

		$items = array();

		$items[] = array(
			'key'   => 'titulo',
			'label' => self::label_titulo( $tipo ),
			'done'  => $post_id && '' !== trim( (string) get_the_title( $post_id ) ),
		);

		foreach ( call_user_func( array( $clase, 'flat_fields' ) ) as $key => $def ) {
			if ( empty( $def['req'] ) ) { continue; }
			/*
			 * Un campo puede no aplicarle a esta pieza: la fecha de inicio es
			 * obligatoria en un evento y no existe en un sitio. Sin esto,
			 * «Empieza» quedaría como un mínimo sin cumplir en toda ficha que
			 * no sea un evento y ninguna se podría enviar jamás.
			 */
			if ( method_exists( $clase, 'aplica_campo' )
				&& ! call_user_func( array( $clase, 'aplica_campo' ), $def, $post_id ) ) {
				continue;
			}
			$val  = $post_id ? get_post_meta( $post_id, $key, true ) : '';
			$items[] = array(
				'key'   => $key,
				'label' => $def['label'],
				'done'  => '' !== trim( (string) $val ),
			);
		}

		if ( method_exists( $clase, 'checklist_extra' ) ) {
			foreach ( call_user_func( array( $clase, 'checklist_extra' ), $post_id ) as $extra ) {
				$items[] = $extra;
			}
		}

		return $items;
	}

	/**
	 * Cómo se llama el título en cada tipo. Un artículo no tiene "nombre", una
	 * ficha no tiene "titular".
	 *
	 * @param string $tipo
	 * @return string
	 */
	public static function label_titulo( $tipo ) {
		switch ( $tipo ) {
			case 'articulo':
				return __( 'Título', 'caaguazu-portal' );
			case 'recorrido':
				return __( 'Nombre del recorrido', 'caaguazu-portal' );
		}
		return __( 'Nombre del destino', 'caaguazu-portal' );
	}

	/**
	 * ¿Cumple todos los mínimos?
	 */
	public static function is_complete( $post_id, $tipo = '' ) {
		foreach ( self::checklist( $post_id, $tipo ) as $item ) {
			if ( ! $item['done'] ) { return false; }
		}
		return true;
	}

	/**
	 * Comentarios rápidos predefinidos para el feedback.
	 */
	public static function quick_feedback() {
		return array(
			__( 'Faltan fuentes o referencias.', 'caaguazu-portal' ),
			__( 'Mejorá las fotos: cuidá la luz, el encuadre y la portada.', 'caaguazu-portal' ),
			__( 'Verificá los horarios y los costos.', 'caaguazu-portal' ),
			__( 'Revisá la ortografía y la redacción.', 'caaguazu-portal' ),
			__( 'Comprobá que el enlace de Google Maps caiga en el lugar correcto.', 'caaguazu-portal' ),
			__( 'Revisá el orden de las paradas: no cuenta lo mismo al revés.', 'caaguazu-portal' ),
		);
	}

	/**
	 * Agrega un comentario de feedback al hilo de revisión.
	 *
	 * $account_id es un ID de cuenta del sistema de cuentas universal
	 * (caaguazu-cuentas), no un usuario de WordPress — por eso el comentario
	 * se guarda con `user_id => 0` (WordPress no tiene ningún usuario al que
	 * asociarlo) y el nombre/email se toman directo de la cuenta. 0 también
	 * cubre al bypass de administrador de WP (sin cuenta propia).
	 *
	 * @param int    $post_id
	 * @param int    $account_id
	 * @param string $text
	 * @return int
	 */
	public static function add_feedback( $post_id, $account_id, $text ) {
		$text = sanitize_textarea_field( $text );
		if ( '' === $text ) { return 0; }

		$name  = '';
		$email = '';
		if ( $account_id > 0 && class_exists( 'Caaguazu_Cuentas_Accounts' ) ) {
			$account = Caaguazu_Cuentas_Accounts::get( $account_id );
			if ( $account ) {
				$name  = $account['display_name'] ? $account['display_name'] : $account['email'];
				$email = $account['email'];
			}
		}
		if ( '' === $name && function_exists( 'wp_get_current_user' ) ) {
			// Bypass de administrador de WP: sin cuenta propia, se usa su identidad de WP.
			$wp_user = wp_get_current_user();
			$name    = $wp_user->display_name ? $wp_user->display_name : $wp_user->user_login;
			$email   = $wp_user->user_email;
		}

		return wp_insert_comment( array(
			'comment_post_ID'      => $post_id,
			'comment_content'      => $text,
			'comment_type'         => self::FEEDBACK_TYPE,
			'user_id'              => 0,
			'comment_author'       => $name,
			'comment_author_email' => $email,
			'comment_approved'     => 1,
		) );
	}

	/**
	 * Hilo de feedback de un destino.
	 *
	 * @return WP_Comment[]
	 */
	public static function get_feedback( $post_id ) {
		return get_comments( array(
			'post_id' => $post_id,
			'type'    => self::FEEDBACK_TYPE,
			'orderby' => 'comment_date',
			'order'   => 'ASC',
		) );
	}

	/**
	 * Excluye el feedback de las queries públicas de comentarios.
	 */
	public function hide_feedback_from_public( $clauses ) {
		if ( is_admin() ) { return $clauses; }
		global $wpdb;
		$clauses['where'] .= $wpdb->prepare( " AND {$wpdb->comments}.comment_type != %s", self::FEEDBACK_TYPE );
		return $clauses;
	}

	/**
	 * Color/clase de pill por estado (para la UI).
	 */
	public static function estado_class( $slug ) {
		$map = array(
			'borrador'         => 'is-draft',
			'enviado'          => 'is-sent',
			'en_revision'      => 'is-review',
			'necesita_cambios' => 'is-changes',
			'aprobado'         => 'is-approved',
			'publicado'        => 'is-published',
			'despublicado'     => 'is-muted',
			'archivado'        => 'is-muted',
		);
		return isset( $map[ $slug ] ) ? $map[ $slug ] : 'is-draft';
	}
}
