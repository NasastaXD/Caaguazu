<?php
/**
 * Invitaciones (invite-only) en tabla custom. El token en claro se guarda en
 * `metadata` —no sólo su hash— para poder reconstruir el link corto
 * /i/<token> (`registration_url()` + `plain_token()`) cada vez que haga
 * falta, no una sola vez: el panel y wp-admin lo muestran mientras la
 * invitación siga abierta, con su botón de copiar. Modelado en el plugin
 * CEAD (modules/auth/class-invitations.php).
 *
 * `invited_by` y `used_by_user_id` son IDs de cuenta del sistema de cuentas
 * universal (caaguazu-cuentas), no de wp_users — no hay FK real (columnas
 * BIGINT simples), así que el cambio de espacio de IDs no requiere migración
 * de esquema, sólo de significado hacia adelante.
 *
 * `expires_at` y `max_usos` son NULL cuando no aplican —enlace permanente,
 * o sin límite de cuentas—, no un valor grande a modo de infinito: `status()`
 * los trata como «no corresponde», así que un enlace permanente nunca puede
 * dar `expired` ni uno sin límite `agotada`.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Invitations {

	/**
	 * Días sugeridos para el <datalist> del campo de vencimiento —no una
	 * lista cerrada: el campo acepta cualquier número, o quedar vacío para
	 * que el enlace no venza nunca. Un solo lugar para que el panel y
	 * wp-admin ofrezcan las mismas sugerencias sin repetirlas cada uno por su
	 * lado.
	 *
	 * @return int[]
	 */
	public static function dias_sugeridos() {
		return array( 1, 3, 7, 14, 30, 90, 365 );
	}

	/**
	 * Techo de días para no guardar un disparate por un cero de más tipeado
	 * a mano (no es un tope de producto: dejar el campo vacío da un enlace
	 * permanente, que es la ausencia total de techo).
	 */
	const MAX_DIAS = 3650; // ~10 años

	/** Techo de usos por el mismo motivo — dejar el campo vacío es sin límite. */
	const MAX_USOS = 100000;

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'promotur_invitations';
	}

	public static function hash( $token ) {
		return hash( 'sha256', (string) $token );
	}

	public static function token() {
		return wp_generate_password( 16, false, false ); // 16 alfanuméricos
	}

	/**
	 * Crea N invitaciones.
	 *
	 * `expires_days` y `max_usos` en 0, vacío o ausentes son a propósito
	 * «sin límite»: un enlace permanente para un grupo que se anota durante
	 * meses, o uno que sirve para cualquier cantidad de cuentas, son casos
	 * de uso reales y no un descuido — no un valor mágico que haya que
	 * adivinar.
	 *
	 * @param array $args { role, email, expires_days, max_usos, count }
	 * @return string[] tokens en claro (única oportunidad de verlos)
	 */
	public static function create( $args = array() ) {
		global $wpdb;
		$args = wp_parse_args( $args, array(
			'role'         => 'promotur_mini',
			'email'        => '',
			'expires_days' => null,
			'max_usos'     => 1,
			'count'        => 1,
		) );

		$role  = array_key_exists( $args['role'], PROMOTUR_Roles::roles() ) ? $args['role'] : 'promotur_mini';
		$count = max( 1, min( 100, (int) $args['count'] ) );
		$email = $args['email'] ? sanitize_email( $args['email'] ) : null;

		$dias = (int) $args['expires_days'];
		// Acota sólo si hay un número — 0/vacío se guarda tal cual: permanente.
		$expires = $dias > 0 ? gmdate( 'Y-m-d H:i:s', time() + ( min( $dias, self::MAX_DIAS ) * DAY_IN_SECONDS ) ) : null;

		$usos_max = (int) $args['max_usos'];
		$usos_max = $usos_max > 0 ? min( $usos_max, self::MAX_USOS ) : null;

		$now = current_time( 'mysql', 1 );
		$by  = caaguazu_account_id(); // 0 si la crea un administrador de WP (bypass), no rompe el insert.

		$tokens = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$token = self::token();
			$wpdb->insert( self::table(), array(
				'token_hash' => self::hash( $token ),
				'email'      => $email,
				'role'       => $role,
				'invited_by' => $by,
				'expires_at' => $expires,
				'max_usos'   => $usos_max,
				'created_at' => $now,
				'metadata'   => wp_json_encode( array( 'token' => $token ) ),
			), array( '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' ) );

			$tokens[] = $token;
			if ( class_exists( 'PROMOTUR_Audit' ) ) {
				PROMOTUR_Audit::log( 'invitation_created', array(
					'entity_type' => 'invitation',
					'entity_id'   => (int) $wpdb->insert_id,
					'payload'     => array( 'role' => $role, 'expires_at' => $expires, 'max_usos' => $usos_max ),
				) );
			}
		}
		return $tokens;
	}

	public static function find_by_token( $token ) {
		global $wpdb;
		if ( ! $token ) { return null; }
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE token_hash = %s', self::hash( $token ) ), ARRAY_A );
		return $row ?: null;
	}

	public static function get( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', (int) $id ), ARRAY_A );
		return $row ?: null;
	}

	/**
	 * Estado calculado: valid|agotada|expired|revoked|invalid.
	 *
	 * «Agotada» reemplaza a la vieja «usada»: con un límite de usos
	 * configurable, una invitación no se apaga la primera vez que alguien
	 * se registra con ella, sólo cuando llega al máximo que se le puso —que
	 * por default sigue siendo 1, o sea que el caso más común (invitar a una
	 * sola persona) se sigue comportando exactamente igual que antes.
	 */
	public static function status( $row ) {
		if ( ! $row ) { return 'invalid'; }
		if ( ! empty( $row['revoked_at'] ) ) { return 'revoked'; }
		// NULL = no vence nunca.
		if ( ! empty( $row['expires_at'] ) && strtotime( $row['expires_at'] . ' UTC' ) < time() ) { return 'expired'; }
		// NULL = sin límite de usos.
		if ( null !== $row['max_usos'] && (int) $row['usos'] >= (int) $row['max_usos'] ) { return 'agotada'; }
		return 'valid';
	}

	public static function status_label( $status ) {
		$map = array(
			'valid'   => __( 'Válida', 'caaguazu-portal' ),
			'agotada' => __( 'Agotada', 'caaguazu-portal' ),
			'expired' => __( 'Expirada', 'caaguazu-portal' ),
			'revoked' => __( 'Revocada', 'caaguazu-portal' ),
			'invalid' => __( 'Inválida', 'caaguazu-portal' ),
		);
		return $map[ $status ] ?? $status;
	}

	/**
	 * Registra un uso: suma uno a `usos` y anota quién fue la última cuenta
	 * creada con este enlace. Con un límite de más de uno, `used_by_user_id`
	 * deja de identificar «a quién se le dio»: quién usó cada vez queda en
	 * el log de auditoría, una fila por registro (`invitation_used`).
	 */
	public static function mark_used( $id, $user_id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::table() . ' SET usos = usos + 1, used_at = %s, used_by_user_id = %d WHERE id = %d',
			current_time( 'mysql', 1 ), (int) $user_id, (int) $id
		) );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'invitation_used', array( 'entity_type' => 'invitation', 'entity_id' => (int) $id, 'user_id' => (int) $user_id ) );
		}
	}

	/**
	 * Cuánto vence, para mostrar. NULL es «no vence nunca», no un dato que
	 * falta — así que no hay fecha que formatear.
	 */
	public static function vence_texto( $row ) {
		if ( empty( $row['expires_at'] ) ) {
			return __( 'No vence', 'caaguazu-portal' );
		}
		/* translators: %s = fecha en que vence la invitación */
		return sprintf( __( 'Vence el %s', 'caaguazu-portal' ), date_i18n( 'j \d\e F', strtotime( $row['expires_at'] ) ) );
	}

	/** Cuántas veces se usó, para mostrar. NULL en max_usos es «sin límite». */
	public static function usos_texto( $row ) {
		$usos = (int) $row['usos'];
		if ( null === $row['max_usos'] ) {
			/* translators: %d = cuántas cuentas ya se crearon con esta invitación */
			return sprintf( _n( '%d cuenta creada, sin límite', '%d cuentas creadas, sin límite', $usos, 'caaguazu-portal' ), $usos );
		}
		/* translators: 1: usos, 2: el máximo permitido */
		return sprintf( __( '%1$d de %2$d', 'caaguazu-portal' ), $usos, (int) $row['max_usos'] );
	}

	public static function revoke( $id ) {
		global $wpdb;
		$wpdb->update( self::table(), array( 'revoked_at' => current_time( 'mysql', 1 ) ), array( 'id' => (int) $id ), array( '%s' ), array( '%d' ) );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'invitation_revoked', array( 'entity_type' => 'invitation', 'entity_id' => (int) $id ) );
		}
	}

	/**
	 * Últimas invitaciones (para wp-admin).
	 *
	 * @return array[]
	 */
	public static function recent( $limit = 50 ) {
		global $wpdb;
		$limit = max( 1, min( 200, (int) $limit ) );
		return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' ORDER BY id DESC LIMIT %d', $limit ), ARRAY_A );
	}

	/** Token en claro almacenado en metadata (para reconstruir el link). */
	public static function plain_token( $row ) {
		if ( empty( $row['metadata'] ) ) { return ''; }
		$meta = json_decode( $row['metadata'], true );
		return is_array( $meta ) && ! empty( $meta['token'] ) ? $meta['token'] : '';
	}

	/** Link corto de registro. */
	public static function registration_url( $token ) {
		return promotur_url( 'i/' . rawurlencode( $token ) );
	}
}
