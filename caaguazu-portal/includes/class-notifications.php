<?php
/**
 * Centro de notificaciones. Las notificaciones se derivan en vivo del estado editorial;
 * "no leídas" = posteriores a la última marca de lectura (user meta).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Notifications {

	private static $instance = null;
	const READ_META = 'promotur_notifs_read_at';

	/**
	 * Memo de get_items() para el pedido en curso.
	 *
	 * La barra superior pide la lista y el contador en la misma pantalla, y el
	 * contador se calcula recorriendo la lista: sin esto, `get_items()` corre
	 * dos veces enteras en cada carga de CUALQUIER página del panel —el topbar
	 * está en el shell—. `null` es "todavía no se calculó"; una lista vacía es
	 * un resultado válido y se memoriza igual.
	 *
	 * @var array[]|null
	 */
	private $items_memo = null;

	/** Memo del contador de la cola, que piden el sidebar y también Inicio. */
	private static $cola_memo = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'mark_read', array( $this, 'handle_mark_read' ) );
	}

	/**
	 * Lista de notificaciones del usuario actual.
	 *
	 * @return array[] { icon, title, when, url, time }
	 */
	public function get_items() {
		if ( null !== $this->items_memo ) {
			return $this->items_memo;
		}

		$items = array();
		$uid   = caaguazu_account_id();
		if ( ! $uid && ! caaguazu_wp_admin_bypass() ) {
			$this->items_memo = $items;
			return $items;
		}

		// Para revisores: lo que espera revisión, de los tres tipos.
		if ( caaguazu_account_can( 'promotor', 'promotur_review_content' ) ) {
			/*
			 * Sin `fields => 'ids'` a propósito. Con IDs pelados, WP_Query se
			 * salta el precargado de posts, y después cada get_the_title() y
			 * cada get_post_time() de este loop dispara su propia consulta.
			 * Pidiendo los objetos, una sola consulta trae todo lo que el loop
			 * necesita —y este loop corre en el shell, o sea en todas las
			 * pantallas del panel—.
			 */
			$pending = get_posts( array(
				'post_type'      => PROMOTUR_Editorial::cpts(),
				'post_status'    => array( 'draft', 'pending' ),
				'posts_per_page' => 10,
				'meta_key'       => '_promotur_estado',
				'meta_value'     => 'enviado',
			) );
			foreach ( $pending as $post ) {
				// Una sola lectura de la fecha: `time` y `when` dicen lo mismo,
				// uno para ordenar y el otro para mostrar.
				$cuando = (int) get_post_time( 'U', true, $post );
				$items[] = array(
					'icon'  => 'inbox',
					'title' => sprintf( __( '«%s» está esperando revisión', 'caaguazu-portal' ), get_the_title( $post ) ),
					'time'  => $cuando,
					'when'  => human_time_diff( $cuando ) . ' ' . __( 'atrás', 'caaguazu-portal' ),
					'url'   => promotur_url( 'panel/revision/' . $post->ID ),
				);
			}
		}

		// Para autores: lo suyo que necesita cambios (filtra por el meta de
		// dueño real, no por post_author — ver PROMOTUR_Destinos::OWNER_META).
		// Objetos y no IDs, por el mismo motivo que arriba.
		$mine = $uid ? get_posts( array(
			'post_type'      => PROMOTUR_Editorial::cpts(),
			'post_status'    => array( 'draft', 'pending' ),
			'posts_per_page' => 10,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'AND',
				array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $uid ),
				array( 'key' => '_promotur_estado', 'value' => 'necesita_cambios' ),
			),
		) ) : array();
		foreach ( $mine as $post ) {
			$cuando  = (int) get_post_modified_time( 'U', true, $post );
			$items[] = array(
				'icon'  => 'edit',
				'title' => sprintf( __( '«%s» necesita algunos cambios', 'caaguazu-portal' ), get_the_title( $post ) ),
				'time'  => $cuando,
				'when'  => human_time_diff( $cuando ) . ' ' . __( 'atrás', 'caaguazu-portal' ),
				'url'   => PROMOTUR_Editorial::url_editor( $post ),
			);
		}

		usort( $items, function ( $a, $b ) { return $b['time'] <=> $a['time']; } );

		$this->items_memo = $items;
		return $items;
	}

	/**
	 * Cantidad de no leídas.
	 */
	public function get_unread_count() {
		$read_at = $this->read_at();
		$count   = 0;
		foreach ( $this->get_items() as $item ) {
			if ( $item['time'] > $read_at ) { $count++; }
		}
		return $count;
	}

	/**
	 * Momento de la última marca de lectura de la cuenta actual (timestamp).
	 * Vive en el metadata de la cuenta (o en usermeta de WP para el bypass
	 * de administrador, que no tiene cuenta propia).
	 */
	private function read_at() {
		$uid = caaguazu_account_id();
		if ( $uid > 0 ) {
			return (int) caaguazu_account_meta_get( $uid, self::READ_META, 0 );
		}
		return (int) get_user_meta( get_current_user_id(), self::READ_META, true );
	}

	/**
	 * Cuánto hay en la cola de revisión (para el badge del sidebar).
	 */
	public static function review_queue_count() {
		if ( null !== self::$cola_memo ) {
			return self::$cola_memo;
		}
		if ( ! caaguazu_account_can( 'promotor', 'promotur_review_content' ) ) {
			self::$cola_memo = 0;
			return 0;
		}
		$q = new WP_Query( array(
			'post_type'      => PROMOTUR_Editorial::cpts(),
			'post_status'    => array( 'draft', 'pending' ),
			'meta_query'     => array( array( 'key' => '_promotur_estado', 'value' => array( 'enviado', 'en_revision' ), 'compare' => 'IN' ) ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		) );
		self::$cola_memo = (int) $q->found_posts;
		return self::$cola_memo;
	}

	/**
	 * Marcar todo como leído. La sesión y el token ya los revisó
	 * PROMOTUR_Acciones.
	 */
	public function handle_mark_read() {
		// El memo vale para un pedido; marcar como leído cambia el contador, así
		// que se descarta antes de redirigir.
		$this->items_memo = null;
		$uid = caaguazu_account_id();
		if ( $uid > 0 ) {
			caaguazu_account_meta_set( $uid, self::READ_META, time() );
		} else {
			update_user_meta( get_current_user_id(), self::READ_META, time() );
		}
		promotur_flash( __( 'Notificaciones marcadas como leídas.', 'caaguazu-portal' ), 'success' );
		$back = wp_get_referer();
		wp_safe_redirect( $back ? $back : promotur_url( 'panel' ) );
		exit;
	}
}
