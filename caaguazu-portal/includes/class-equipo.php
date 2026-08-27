<?php
/**
 * El equipo: quién entra al panel, con qué rol, y las invitaciones abiertas.
 *
 * Esto vivía en wp-admin y estaba roto de raíz: la pantalla «Usuarios» listaba
 * usuarios de WordPress, les cambiaba el rol de WordPress y los suspendía con
 * una usermeta. Desde que la identidad pasó a `caaguazu-cuentas`, los
 * promotores no tienen usuario de WordPress: esa pantalla editaba a otra gente
 * —o a nadie—. Acá se edita lo que de verdad decide quién entra: la cuenta y su
 * permiso sobre el panel.
 */

defined( 'ABSPATH' ) || exit;

class PROMOTUR_Equipo {

	const CAP = 'promotur_manage_team';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		PROMOTUR_Acciones::formulario( 'equipo_rol', array( $this, 'cambiar_rol' ), self::CAP );
		PROMOTUR_Acciones::formulario( 'equipo_estado', array( $this, 'cambiar_estado' ), self::CAP );
		PROMOTUR_Acciones::formulario( 'equipo_quitar', array( $this, 'quitar' ), self::CAP );
		PROMOTUR_Acciones::formulario( 'invitacion_revocar', array( $this, 'revocar_invitacion' ), self::CAP );
	}

	private function volver( $mensaje, $tipo = 'success' ) {
		promotur_flash( $mensaje, $tipo );
		wp_safe_redirect( promotur_url( 'panel/equipo' ) );
		exit;
	}

	/**
	 * La cuenta sobre la que se opera.
	 *
	 * Nadie se edita a sí mismo desde acá: sacarse el propio permiso deja el
	 * panel sin nadie que administre, y para cambiar los datos propios está
	 * Mi perfil.
	 */
	private function cuenta_objetivo() {
		$id = isset( $_POST['cuenta'] ) ? (int) $_POST['cuenta'] : 0;
		if ( $id <= 0 ) {
			$this->volver( __( 'Esa persona no existe.', 'caaguazu-portal' ), 'error' );
		}
		if ( $id === (int) caaguazu_account_id() ) {
			$this->volver( __( 'A vos mismo no te podés editar desde acá.', 'caaguazu-portal' ), 'error' );
		}
		$cuenta = Caaguazu_Cuentas_Accounts::get( $id );
		if ( ! $cuenta ) {
			$this->volver( __( 'Esa persona no existe.', 'caaguazu-portal' ), 'error' );
		}
		return $cuenta;
	}

	/** Cómo llamarla en un aviso. */
	private function nombre( array $cuenta ) {
		return $cuenta['display_name'] ? $cuenta['display_name'] : $cuenta['email'];
	}

	public function cambiar_rol() {
		$cuenta = $this->cuenta_objetivo();
		$rol    = isset( $_POST['rol'] ) ? sanitize_key( wp_unslash( $_POST['rol'] ) ) : '';
		if ( ! isset( PROMOTUR_Roles::roles()[ $rol ] ) ) {
			$this->volver( __( 'Ese rol no existe.', 'caaguazu-portal' ), 'error' );
		}

		Caaguazu_Cuentas_Panels::instance()->grant( (int) $cuenta['id'], 'promotor', $rol, null, caaguazu_account_id() );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'equipo_rol', array(
				'entity_type' => 'account',
				'entity_id'   => (int) $cuenta['id'],
				'payload'     => array( 'rol' => $rol ),
			) );
		}
		$this->volver( sprintf(
			/* translators: 1 = nombre de la persona, 2 = rol nuevo */
			__( '%1$s ahora es %2$s.', 'caaguazu-portal' ),
			$this->nombre( $cuenta ),
			PROMOTUR_Roles::label( $rol )
		) );
	}

	/**
	 * Suspender o reactivar.
	 *
	 * Suspender corta el acceso en el pedido siguiente: la sesión se resuelve
	 * contra el estado de la cuenta en cada carga, no hace falta echar a nadie
	 * a mano.
	 */
	public function cambiar_estado() {
		$cuenta = $this->cuenta_objetivo();
		$nuevo  = ( 'active' === $cuenta['status'] ) ? 'suspended' : 'active';

		Caaguazu_Cuentas_Accounts::update( (int) $cuenta['id'], array( 'status' => $nuevo ) );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'equipo_estado', array(
				'entity_type' => 'account',
				'entity_id'   => (int) $cuenta['id'],
				'payload'     => array( 'estado' => $nuevo ),
			) );
		}

		$this->volver( 'suspended' === $nuevo
			? sprintf(
				/* translators: %s = nombre de la persona */
				__( 'Suspendimos a %s. No va a poder entrar hasta que la reactives.', 'caaguazu-portal' ),
				$this->nombre( $cuenta )
			)
			: sprintf(
				/* translators: %s = nombre de la persona */
				__( '%s puede volver a entrar.', 'caaguazu-portal' ),
				$this->nombre( $cuenta )
			)
		);
	}

	/**
	 * Sacar del panel. No borra la cuenta: la misma persona puede tener
	 * permiso sobre otros paneles del ecosistema, y borrarla los perdería
	 * todos.
	 */
	public function quitar() {
		$cuenta = $this->cuenta_objetivo();

		Caaguazu_Cuentas_Panels::instance()->revoke( (int) $cuenta['id'], 'promotor' );
		if ( class_exists( 'PROMOTUR_Audit' ) ) {
			PROMOTUR_Audit::log( 'equipo_quitado', array(
				'entity_type' => 'account',
				'entity_id'   => (int) $cuenta['id'],
			) );
		}
		$this->volver( sprintf(
			/* translators: %s = nombre de la persona */
			__( '%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.', 'caaguazu-portal' ),
			$this->nombre( $cuenta )
		) );
	}

	public function revocar_invitacion() {
		$id = isset( $_POST['invitacion'] ) ? (int) $_POST['invitacion'] : 0;
		if ( ! $id || ! PROMOTUR_Invitations::get( $id ) ) {
			$this->volver( __( 'Esa invitación ya no existe.', 'caaguazu-portal' ), 'error' );
		}
		PROMOTUR_Invitations::revoke( $id );
		$this->volver( __( 'Invitación revocada. Ese enlace ya no sirve.', 'caaguazu-portal' ) );
	}

	/** Las invitaciones que todavía se pueden usar. */
	public static function invitaciones_abiertas() {
		if ( ! class_exists( 'PROMOTUR_Invitations' ) ) {
			return array();
		}
		$abiertas = array();
		foreach ( PROMOTUR_Invitations::recent( 50 ) as $fila ) {
			if ( 'valid' === PROMOTUR_Invitations::status( $fila ) ) {
				$abiertas[] = $fila;
			}
		}
		return $abiertas;
	}
}
