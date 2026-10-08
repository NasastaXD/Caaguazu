<?php
/**
 * Auditoría de canjes: una fila por intento, éxito o no. Existe para que un
 * admin entienda un "no pude entrar" sin tener que leer logs de servidor —
 * en particular el caso más común y menos autoexplicativo: un email que ya
 * tenía cuenta y quedó rechazado a propósito (ver class-link.php::resolve()).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CEADSSO_Log {

	/**
	 * @return string
	 */
	private static function table() {
		$t = CEADSSO_Install::tables();
		return $t['log'];
	}

	/**
	 * Registra un intento de canje.
	 *
	 * @param string   $resultado 'ok'|'rechazado'|'error'
	 * @param string   $motivo    clave corta, ej. 'email_existente', 'rol_desconocido'
	 * @param array    $datos     { cead_uid?, email?, rol_cead?, account_id? }
	 */
	public static function record( $resultado, $motivo, array $datos = array() ) {
		global $wpdb;
		$wpdb->insert( self::table(), array( // phpcs:ignore WordPress.DB
			'cead_uid'   => isset( $datos['cead_uid'] ) ? (int) $datos['cead_uid'] : null,
			'email'      => isset( $datos['email'] ) ? mb_substr( (string) $datos['email'], 0, 190 ) : null,
			'rol_cead'   => isset( $datos['rol_cead'] ) ? mb_substr( (string) $datos['rol_cead'], 0, 60 ) : null,
			'resultado'  => sanitize_key( $resultado ),
			'motivo'     => $motivo ? sanitize_key( $motivo ) : null,
			'account_id' => isset( $datos['account_id'] ) ? (int) $datos['account_id'] : null,
			'created_at' => current_time( 'mysql', true ),
		) );

		/*
		 * La lista de «roles que llegaron y se rechazaron» se cachea diez
		 * minutos, y esa lista es LA pantalla donde se arregla un rechazo por
		 * rol. Sin esto, quien acaba de rebotar avisa, el admin abre
		 * Herramientas → Acceso desde el CEAD y ve «Ninguno» durante los
		 * siguientes diez minutos: la pantalla que existe para resolverlo
		 * niega que haya algo que resolver. Un rechazo nuevo invalida el
		 * caché, que es barato y pasa poquísimas veces.
		 */
		if ( 'ok' !== $resultado && class_exists( 'CEADSSO_Validacion' ) ) {
			CEADSSO_Validacion::olvidar_cache();
		}
	}

	/**
	 * Últimos intentos, para la pantalla de admin.
	 *
	 * @param int $limit
	 * @return array[]
	 */
	public static function recent( $limit = 50 ) {
		global $wpdb;
		$table = self::table();
		$rows  = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
			"SELECT * FROM {$table} ORDER BY id DESC LIMIT %d",
			(int) $limit
		), ARRAY_A );
		return $rows ? $rows : array();
	}
}
