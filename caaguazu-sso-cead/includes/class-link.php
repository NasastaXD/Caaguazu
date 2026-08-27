<?php
/**
 * Resolución de cuenta a partir de los claims del CEAD: encontrar, vincular
 * o crear — y aplicar el rol del panel `promotor` que le corresponde.
 *
 * Encadena las tres decisiones ya tomadas para esta integración:
 *   - Rol del CEAD → rol del panel `promotor`. El mapa vive en
 *     `CEADSSO_Roles`, y ahí está explicado por qué dejó de ser una constante:
 *     el CEAD es un WordPress y manda roles de WordPress, mientras que de este
 *     lado la identidad no es de WordPress.
 *   - Entran al panel `promotor` que ya existe (no uno aparte).
 *   - Un email que ya tiene cuenta SIN vincular se rechaza — no se vincula
 *     solo. Vincular automáticamente por email es la puerta de robo de
 *     cuenta que describe el contrato: quien controle ese email en el CEAD
 *     pasaría a manejar la cuenta existente. Lo vincula un admin a mano,
 *     desde Herramientas → Acceso desde el CEAD (class-admin.php).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CEADSSO_Link {

	/**
	 * Cuenta vinculada a un cead_uid (o null).
	 *
	 * @param int $cead_uid
	 * @return array|null fila de cuenta
	 */
	private static function account_for_cead_uid( $cead_uid ) {
		global $wpdb;
		$t   = CEADSSO_Install::tables();
		$row = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB
			"SELECT account_id FROM {$t['links']} WHERE cead_uid = %d",
			(int) $cead_uid
		), ARRAY_A );
		return $row ? Caaguazu_Cuentas_Accounts::get( (int) $row['account_id'] ) : null;
	}

	/**
	 * Registra el vínculo account_id ↔ cead_uid. Falla (false) si cualquiera
	 * de los dos ya está vinculado a otra cosa — no debería pasar si
	 * resolve() se usó como única puerta de entrada, pero la UNIQUE KEY de
	 * la tabla es la garantía real, esto es solo para devolver un booleano
	 * limpio en vez de un error de SQL.
	 *
	 * @param int $account_id
	 * @param int $cead_uid
	 * @return bool
	 */
	public static function link( $account_id, $cead_uid ) {
		global $wpdb;
		$t = CEADSSO_Install::tables();
		return (bool) $wpdb->insert( $t['links'], array( // phpcs:ignore WordPress.DB
			'account_id' => (int) $account_id,
			'cead_uid'   => (int) $cead_uid,
			'linked_at'  => current_time( 'mysql', true ),
		) );
	}

	/**
	 * Contraseña que no corresponde a ninguna que la persona haya elegido —
	 * la cuenta existe y entra por SSO, pero no hay ninguna contraseña real
	 * que alguien pueda adivinar. Si alguna vez quiere entrar directo, usa
	 * el flujo de recuperación que ya existe (class-auth.php del Portal).
	 *
	 * @return string
	 */
	private static function unusable_password_hash() {
		return Caaguazu_Cuentas_Passwords::hash( wp_generate_password( 64, true, true ) );
	}

	/**
	 * Punto de entrada único: claims validados del CEAD → cuenta lista con
	 * el grant del panel `promotor` aplicado.
	 *
	 * @param array $claims { cead_uid, email, nombre, telefono, rol, curso }
	 * @return array|WP_Error { account_id } en éxito.
	 */
	public static function resolve( array $claims ) {
		// El log guarda la columna `rol_cead`, y los claims traen la clave
		// `rol`. Sin esta línea, el rol quedaba en NULL en TODAS las filas del
		// registro —incluidas las de «rol desconocido»—, así que la pantalla de
		// admin mostraba «—» justo donde había que mirar. Era el motivo por el
		// que un rechazo por rol no se podía diagnosticar.
		$claims['rol_cead'] = isset( $claims['rol'] ) ? $claims['rol'] : '';

		$rol_portal = CEADSSO_Roles::resolver( $claims['rol_cead'] );
		if ( ! $rol_portal ) {
			CEADSSO_Log::record( 'rechazado', 'rol_desconocido', $claims );
			return new WP_Error(
				'rol_desconocido',
				__( 'Tu rol en el CEAD todavía no está habilitado para entrar al portal. Avisale a quien administra el portal: puede habilitarlo en un minuto.', 'caaguazu-sso-cead' )
			);
		}

		// El rol existe en el mapa, pero ¿existe en el panel? Si el panel no
		// está registrado todavía en el sistema de cuentas, el grant se
		// guardaría con un snapshot de capabilities vacío: la persona entraría
		// «con permiso» y recibiría un 403 en la primera pantalla. Es
		// exactamente la clase de fallo silencioso que hay que cortar acá.
		$roles_panel = CEADSSO_Roles::roles_del_panel();
		if ( ! isset( $roles_panel[ $rol_portal ] ) ) {
			CEADSSO_Log::record( 'error', 'rol_del_panel_inexistente', $claims );
			return new WP_Error(
				'rol_del_panel_inexistente',
				__( 'El acceso desde el CEAD está mal configurado de este lado. Avisale a quien administra el portal.', 'caaguazu-sso-cead' )
			);
		}

		$account = self::account_for_cead_uid( $claims['cead_uid'] );

		if ( ! $account ) {
			$existente = Caaguazu_Cuentas_Accounts::get_by_email( $claims['email'] );
			if ( $existente ) {
				// Decisión tomada a propósito: no se vincula solo (ver docblock).
				CEADSSO_Log::record( 'rechazado', 'email_existente', array_merge( $claims, array( 'account_id' => $existente['id'] ) ) );
				return new WP_Error(
					'email_existente',
					__( 'Ya existe una cuenta en el portal con tu email. Escribinos para vincularla con tu acceso del CEAD.', 'caaguazu-sso-cead' )
				);
			}

			$account_id = Caaguazu_Cuentas_Accounts::create_with_hash( array(
				'email'        => $claims['email'],
				'pass_hash'    => self::unusable_password_hash(),
				'display_name' => $claims['nombre'],
				'phone'        => $claims['telefono'],
				'status'       => 'active',
				'metadata'     => array(
					'cead_uid'   => $claims['cead_uid'],
					'cead_curso' => $claims['curso'],
				),
			) );
			if ( is_wp_error( $account_id ) ) {
				CEADSSO_Log::record( 'error', 'no_se_pudo_crear', $claims );
				return $account_id;
			}
			self::link( $account_id, $claims['cead_uid'] );
			$account = Caaguazu_Cuentas_Accounts::get( $account_id );
		}

		// Metadata siempre al día (nombre/curso pueden cambiar de un canje a otro).
		caaguazu_account_meta_set( $account['id'], 'cead_uid', $claims['cead_uid'] );
		caaguazu_account_meta_set( $account['id'], 'cead_curso', $claims['curso'] );

		// Se aplica en CADA canje, no solo al crear: si cambia el rol en el
		// CEAD (o le habían revocado el panel), el próximo acceso lo refleja.
		caaguazu_account_grant( $account['id'], 'promotor', $rol_portal, null, null );

		CEADSSO_Log::record( 'ok', null, array_merge( $claims, array( 'account_id' => $account['id'] ) ) );

		return array( 'account_id' => (int) $account['id'] );
	}
}
