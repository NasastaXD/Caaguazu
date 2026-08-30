<?php
/**
 * Pulso del panel: producción por autor, salud del contenido y actividad
 * editorial por día.
 *
 * Ya no cuenta vistas ni búsquedas sin resultado: las dos se registraban desde
 * la vitrina web que este plugin publicaba, y esa vitrina se fue. La app tiene
 * su propia analítica; cuando haga falta medir algo de la app, el dato entra
 * por su lado y no reinventando un contador acá.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Stats {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/* ----- Permisos ----- */

	/**
	 * ¿Puede editar fichas ya publicadas sin pasar de nuevo por revisión?
	 * Sólo quien revisa contenido (o el bypass de administrador de WP cuando
	 * $account_id es 0).
	 *
	 * @param int $account_id
	 */
	public static function can_edit_published( $account_id ) {
		if ( $account_id <= 0 ) {
			return function_exists( 'caaguazu_wp_admin_bypass' ) && caaguazu_wp_admin_bypass();
		}
		return caaguazu_account_can( 'promotor', 'promotur_review_content', $account_id );
	}

	/**
	 * ¿Puede publicar directo (con auditoría posterior)?
	 * Sólo quien tiene la capability de publicar (o el bypass de
	 * administrador de WP cuando $account_id es 0).
	 *
	 * @param int $account_id
	 */
	public static function can_publish_directly( $account_id ) {
		if ( $account_id <= 0 ) {
			return function_exists( 'caaguazu_wp_admin_bypass' ) && caaguazu_wp_admin_bypass();
		}
		return caaguazu_account_can( 'promotor', 'promotur_publish_destino', $account_id );
	}

	/* ----- Series de actividad ----- */

	/**
	 * Cuántas veces pasó algo cada día, para las barras del panel.
	 *
	 * Sale del log de auditoría (`promotur_audit_log`), que ya registra el
	 * ciclo editorial completo con su timestamp — no se guarda ningún contador
	 * nuevo ni se inventa una serie: si un día no pasó nada, ese día es cero y
	 * se dibuja en cero.
	 *
	 * Una sola consulta agrupada por día para las dos ventanas (la actual y la
	 * anterior, que sirve de comparación). Hacerlo con una consulta por día
	 * serían 14 idas a la base para dibujar 7 barritas.
	 *
	 * @param string[] $actions acciones del log a contar (ver PROMOTUR_Audit::post_actions())
	 * @param int      $dias    largo de la ventana
	 * @return array{dias:array<int,array{fecha:string,n:int}>,total:int,previo:int}
	 */
	public static function serie_diaria( array $actions, $dias = 7 ) {
		$vacia = array( 'dias' => array(), 'total' => 0, 'previo' => 0 );
		if ( ! $actions || ! class_exists( 'PROMOTUR_Audit' ) ) {
			return $vacia;
		}
		$dias = max( 1, min( 60, (int) $dias ) );

		global $wpdb;
		$tabla = PROMOTUR_Audit::table();
		$hoy   = current_time( 'Y-m-d' );
		$desde = gmdate( 'Y-m-d', strtotime( $hoy . ' -' . ( 2 * $dias - 1 ) . ' days' ) );

		$placeholders = implode( ',', array_fill( 0, count( $actions ), '%s' ) );
		$params       = array_merge( $actions, array( $desde . ' 00:00:00' ) );

		$filas = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->prepare(
				"SELECT DATE(created_at) AS fecha, COUNT(*) AS n
				 FROM {$tabla}
				 WHERE action IN ($placeholders) AND created_at >= %s
				 GROUP BY DATE(created_at)",
				$params
			),
			ARRAY_A
		);

		$por_fecha = array();
		foreach ( (array) $filas as $fila ) {
			$por_fecha[ $fila['fecha'] ] = (int) $fila['n'];
		}

		$serie  = array();
		$total  = 0;
		$previo = 0;
		for ( $i = 2 * $dias - 1; $i >= 0; $i-- ) {
			$fecha = gmdate( 'Y-m-d', strtotime( $hoy . ' -' . $i . ' days' ) );
			$n     = isset( $por_fecha[ $fecha ] ) ? $por_fecha[ $fecha ] : 0;
			if ( $i < $dias ) {
				$serie[] = array( 'fecha' => $fecha, 'n' => $n );
				$total  += $n;
			} else {
				$previo += $n;
			}
		}

		return array( 'dias' => $serie, 'total' => $total, 'previo' => $previo );
	}

	/* ----- Producción ----- */

	/**
	 * Cuenta lo que produjo un autor —fichas, artículos y recorridos— por
	 * estado de publicación. Filtra por el meta de dueño real
	 * (`_caaguazu_owner`), no por post_author (que en todo lo creado desde el
	 * panel apunta al usuario de servicio).
	 *
	 * @param int $account_id
	 */
	public static function author_counts( $account_id ) {
		$meta_query = array( array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => (int) $account_id ) );
		$pub = new WP_Query( array( 'post_type' => PROMOTUR_Editorial::cpts(), 'post_status' => 'publish', 'meta_query' => $meta_query, 'posts_per_page' => 1, 'fields' => 'ids' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		$all = new WP_Query( array( 'post_type' => PROMOTUR_Editorial::cpts(), 'post_status' => array( 'publish', 'draft', 'pending' ), 'meta_query' => $meta_query, 'posts_per_page' => 1, 'fields' => 'ids' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		return array( 'publicadas' => (int) $pub->found_posts, 'total' => (int) $all->found_posts );
	}

	/**
	 * Salud del contenido: fichas publicadas sin portada y desactualizadas
	 * (> N meses). Mira sólo las fichas a propósito: la portada de un artículo
	 * es obligatoria para enviarlo, así que no puede faltar, y un recorrido no
	 * se "desactualiza" con el tiempo — se desactualiza cuando cambia una de
	 * sus paradas, que es otra pregunta.
	 *
	 * @return array{ sin_foto:WP_Post[], viejas:WP_Post[] }
	 */
	public static function content_health( $meses = 6 ) {
		$pub = get_posts( array( 'post_type' => PROMOTUR_Destinos::CPT, 'post_status' => 'publish', 'posts_per_page' => 200 ) );
		$sin_foto = array();
		$viejas   = array();
		$limite   = strtotime( "-{$meses} months" );
		foreach ( $pub as $p ) {
			if ( ! get_post_meta( $p->ID, '_promotur_portada', true ) && ! has_post_thumbnail( $p ) ) {
				$sin_foto[] = $p;
			}
			$verif = get_post_meta( $p->ID, '_promotur_verificado_en', true );
			$ref   = $verif ? strtotime( $verif ) : strtotime( $p->post_modified_gmt );
			if ( $ref && $ref < $limite ) { $viejas[] = $p; }
		}
		return array( 'sin_foto' => $sin_foto, 'viejas' => $viejas );
	}
}
