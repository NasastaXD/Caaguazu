<?php
/**
 * Registro único de roles, capabilities y mapa sección → capability.
 * La UI (sidebar, accesos, secciones) se gatea por capability, nunca por rol hardcodeado.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Roles {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Definición de roles del portal: clave → { label, caps }.
	 *
	 * @return array
	 */
	public static function roles() {
		$promotor = array(
			'read'                     => true,
			'promotur_view_panel'      => true,
			'promotur_create_draft'    => true,
			'promotur_edit_destino'    => true,
			'promotur_review_content'  => true,
			'promotur_publish_destino' => true,
			'promotur_assign_tasks'    => true,
			'promotur_manage_team'     => true,
			'promotur_manage_users'    => true,
			'promotur_view_reports'    => true,
			'promotur_manage_media'    => true,
			'promotur_manage_structure'=> true,
			'promotur_manage_app'      => true,
			// Traducir a otro idioma es de Profesor y no de Alumno: una
			// traducción se publica tal cual en la app, sin pasar por
			// revisión —el flujo editorial revisa el castellano, que es el
			// original— así que quien la escribe está publicando.
			'promotur_traducir'        => true,
			'promotur_edit_profile'    => true,
			'upload_files'             => true,
		);

		$mini = array(
			'read'                  => true,
			'promotur_view_panel'   => true,
			'promotur_create_draft' => true,
			'promotur_edit_destino' => true,
			'promotur_view_own_tasks' => true,
			'promotur_edit_profile' => true,
			'upload_files'          => true,
		);

		$visitante = array(
			'read'                  => true,
			'promotur_view_panel'   => true,
			'promotur_edit_profile' => true,
		);

		return array(
			'promotur_promotor'  => array( 'label' => __( 'Profesor', 'caaguazu-portal' ), 'caps' => $promotor ),
			'promotur_mini'      => array( 'label' => __( 'Alumno', 'caaguazu-portal' ), 'caps' => $mini ),
			'promotur_visitante' => array( 'label' => __( 'Visitante', 'caaguazu-portal' ), 'caps' => $visitante ),
		);
	}

	/**
	 * Mapa sección del panel → capability requerida.
	 *
	 * @return array
	 */
	public static function sections() {
		$secciones = array(
			'home'           => 'promotur_view_panel',
			'buscar'         => 'promotur_view_panel',
			'ayuda'          => 'promotur_view_panel',
			'perfil'         => 'promotur_edit_profile',
			'editor'         => 'promotur_edit_destino',
			'captura'        => 'promotur_create_draft',
			'mis-contenidos' => 'promotur_create_draft',
			// Los tres tipos de contenido comparten sección de lista y de
			// edición: /articulos y /articulos/<id>, /recorridos y
			// /recorridos/<id>. El inventario es la lista de fichas publicadas
			// del departamento, y la puede mirar cualquiera del panel: es de
			// donde los recorridos sacan sus paradas.
			'inventario'     => 'promotur_view_panel',
			'articulos'      => 'promotur_create_draft',
			'recorridos'     => 'promotur_create_draft',
			'revision'       => 'promotur_review_content',
			'tareas'         => 'promotur_view_own_tasks',
			'equipo'         => 'promotur_manage_team',
			'reportes'       => 'promotur_view_reports',
			'biblioteca'     => 'promotur_manage_media',
			'estructura'     => 'promotur_manage_structure',
		);

		/*
		 * La sección «App» está desconectada a propósito (ver
		 * promotur_app_api_activa() en helpers.php). Su código sigue en el
		 * repo —includes/class-app-control.php y templates/sections/app.php—
		 * para poder volver a enchufarla de una línea cuando se resuelva lo
		 * que la rompía; mientras tanto su ruta cae en el 404 del panel, que
		 * es mejor que una pantalla que revienta.
		 */

		return $secciones;
	}

	/**
	 * Nombre legible de un rol del portal.
	 *
	 * @param string $key
	 * @return string
	 */
	public static function label( $key ) {
		$roles = self::roles();
		return isset( $roles[ $key ] ) ? $roles[ $key ]['label'] : $key;
	}

	/**
	 * Todas las capabilities personalizadas del portal (para asignar al admin).
	 *
	 * @return string[]
	 */
	public static function all_caps() {
		$caps = array();
		foreach ( self::roles() as $role ) {
			foreach ( $role['caps'] as $cap => $granted ) {
				if ( 0 === strpos( $cap, 'promotur_' ) ) {
					$caps[ $cap ] = true;
				}
			}
		}
		// Caps del CPT (capability_type promotur_destino) que el admin debe tener.
		foreach ( PROMOTUR_Destinos::cpt_caps() as $cap ) {
			$caps[ $cap ] = true;
		}
		return array_keys( $caps );
	}

	/**
	 * Crea los roles y otorga capabilities al administrador. Idempotente.
	 */
	public static function install() {
		foreach ( self::roles() as $key => $def ) {
			remove_role( $key ); // asegura caps actualizadas en re-activación
			add_role( $key, $def['label'], $def['caps'] );
		}

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( self::all_caps() as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Quita roles y capabilities (uninstall).
	 */
	public static function uninstall() {
		foreach ( array_keys( self::roles() ) as $key ) {
			remove_role( $key );
		}
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( self::all_caps() as $cap ) {
				$admin->remove_cap( $cap );
			}
		}
	}
}
