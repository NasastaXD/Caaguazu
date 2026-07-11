<?php
/**
 * Meta fields editoriales del panel "caaguazu.net": Fuente/referencia,
 * Responsable del contenido, Estado de verificación, y el estado del
 * checklist editorial. Registrados con show_in_rest (Gutenberg los
 * necesita vía REST) y sanitize_callback + auth_callback por capacidad.
 * "Actualizado el" NO es un meta propio: se lee directo de post_modified
 * (WordPress ya lo mantiene solo, sin que un editor tenga que acordarse de
 * tipearlo) — ver caaguazu_render_trust_meta() en
 * caaguazu-theme/inc/helpers.php, que combina Fuente/Responsable/Estado
 * con esa fecha nativa para el frontend.
 *
 * @package Caaguazu_Editor_UX
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CZU_Editor_Meta {

	const META_FUENTE      = '_czu_fuente_referencia';
	const META_RESPONSABLE = '_czu_responsable_contenido';
	const META_ESTADO      = '_czu_estado_verificacion';
	const META_CHECKLIST   = '_czu_checklist_state';

	const ESTADO_VALUES = array( 'pendiente', 'revisado', 'verificado', 'desactualizado' );

	public function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta() {
		$auth_cb = function () {
			return current_user_can( 'edit_posts' );
		};

		$text_args = array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth_cb,
		);

		$estado_args = array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => array( $this, 'sanitize_estado' ),
			'auth_callback'     => $auth_cb,
		);

		/**
		 * Estado del checklist: lista de índices marcados separados por coma
		 * (p. ej. "0,2,4" — hay 6 ítems fijos en editor-plugin.js, índices
		 * 0-5). No es un dato editorial en sí (no se muestra en el
		 * frontend), es sólo para que el checklist recuerde su estado entre
		 * sesiones de edición en vez de reiniciarse cada vez — pedido
		 * explícito del equipo editorial (antes era puramente visual, ver
		 * README "Limitaciones conocidas").
		 */
		$checklist_args = array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => array( $this, 'sanitize_checklist' ),
			'auth_callback'     => $auth_cb,
		);

		foreach ( CZU_POST_TYPES as $post_type ) {
			register_post_meta( $post_type, self::META_FUENTE, $text_args );
			register_post_meta( $post_type, self::META_RESPONSABLE, $text_args );
			register_post_meta( $post_type, self::META_ESTADO, $estado_args );
			register_post_meta( $post_type, self::META_CHECKLIST, $checklist_args );
		}
	}

	/**
	 * Sólo uno de los 4 valores del select; cualquier otra cosa (incluido un
	 * intento de mandar HTML/JS por la REST API) cae al vacío = "sin estado".
	 */
	public function sanitize_estado( $value ) {
		return in_array( $value, self::ESTADO_VALUES, true ) ? $value : '';
	}

	/**
	 * Sólo dígitos y comas (p. ej. "0,2,4"); cualquier otra cosa cae al
	 * vacío = checklist sin marcar. No usa una whitelist de valores fijos
	 * como sanitize_estado() porque los índices son posicionales, no un set
	 * cerrado de strings.
	 */
	public function sanitize_checklist( $value ) {
		return preg_match( '/^[0-9]+(,[0-9]+)*$/', (string) $value ) ? $value : '';
	}
}
