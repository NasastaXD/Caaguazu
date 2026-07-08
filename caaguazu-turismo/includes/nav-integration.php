<?php
/**
 * Integración de Turismo con el nav y los accesos rápidos del theme —
 * migrado desde caaguazu-theme/inc/helpers.php. El theme no sabe nada de
 * Turismo en particular: solo expone los filtros `caaguazu_nav_items` y
 * `caaguazu_quick_access_items`, y un ítem de nav puede traer un
 * `dropdown_cb` (callable) para pintar un mega-menú propio.
 *
 * @package Caaguazu_Turismo
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Grupos de accesos directos a destinos reales de Turismo, para el
 * mega-menú del nav y el acordeón del drawer móvil. Las claves son los
 * wp_slug reales sembrados por includes/tourism-seeder.php (ver
 * includes/tourism-content.php).
 */
function caaguazu_turismo_menu_groups() {
	return array(
		__( 'La Capital de la Madera', 'caaguazu-turismo' ) => array(
			'la-capital-de-la-madera' => __( 'Introducción', 'caaguazu-turismo' ),
			'historia'                => __( 'Historia', 'caaguazu-turismo' ),
			'la-ruta-de-la-madera'    => __( 'La Ruta de la Madera', 'caaguazu-turismo' ),
			'artesanos'               => __( 'Artesanos', 'caaguazu-turismo' ),
		),
		__( 'Qué hacer', 'caaguazu-turismo' ) => array(
			'ykua-la-patria'       => __( 'Ykua La Patria', 'caaguazu-turismo' ),
			'patrimonio-religioso' => __( 'Patrimonio religioso', 'caaguazu-turismo' ),
			'mercado-municipal'    => __( 'Mercado de Abasto', 'caaguazu-turismo' ),
			'parques-y-naturaleza' => __( 'Parque Techapyrã', 'caaguazu-turismo' ),
		),
		__( 'Sabores', 'caaguazu-turismo' ) => array(
			'platos-tipicos' => __( 'Platos típicos', 'caaguazu-turismo' ),
			'donde-comer'    => __( 'Dónde comer', 'caaguazu-turismo' ),
			'mate-y-terere'  => __( 'Mate y tereré', 'caaguazu-turismo' ),
		),
		__( 'Planificá tu visita', 'caaguazu-turismo' ) => array(
			'como-llegar'      => __( 'Cómo llegar', 'caaguazu-turismo' ),
			'donde-alojarte'   => __( 'Dónde alojarte', 'caaguazu-turismo' ),
			'mapa-interactivo' => __( 'Mapa interactivo', 'caaguazu-turismo' ),
		),
	);
}

/**
 * Resuelve la URL real de una página de Turismo (anidada bajo turismo/…)
 * a partir de su wp_slug, reusando el mismo cálculo de ruta que usa el
 * seeder para no duplicar esa lógica.
 */
function caaguazu_tourism_page_url( $wp_slug ) {
	$pages = caaguazu_tourism_pages();
	if ( ! isset( $pages[ $wp_slug ] ) ) {
		return home_url( '/' );
	}
	$full_path = caaguazu_tourism_full_path( $wp_slug, $pages );
	$page      = get_page_by_path( $full_path );
	return $page ? get_permalink( $page ) : home_url( '/' . $full_path . '/' );
}

/**
 * URL del hub de Turismo, con el mismo fallback que caaguazu_page_url() del
 * theme (por si el theme activo no expone ese helper).
 */
function caaguazu_turismo_hub_url() {
	if ( function_exists( 'caaguazu_page_url' ) ) {
		return caaguazu_page_url( 'turismo' );
	}
	$page = get_page_by_path( 'turismo' );
	return $page ? get_permalink( $page ) : home_url( '/turismo/' );
}

/**
 * Mega-menú de Turismo: se pasa como `dropdown_cb` en el item de nav.
 */
function caaguazu_render_turismo_dropdown() {
	echo '<div class="nav-dropdown nav-dropdown--mega">';
	foreach ( caaguazu_turismo_menu_groups() as $group_label => $links ) {
		echo '<div class="nav-dropdown-col"><h4>' . esc_html( $group_label ) . '</h4>';
		foreach ( $links as $slug => $label ) {
			printf( '<a class="nav-dropdown-link" href="%s">%s</a>', esc_url( caaguazu_tourism_page_url( $slug ) ), esc_html( $label ) );
		}
		echo '</div>';
	}
	echo '</div>';
}

add_filter( 'caaguazu_quick_access_items', function ( $items ) {
	$items[] = array(
		'icon'  => 'tree',
		'label' => __( 'Turismo', 'caaguazu-turismo' ),
		'url'   => caaguazu_turismo_hub_url(),
	);
	return $items;
} );

add_filter( 'caaguazu_nav_items', function ( $items ) {
	$items[] = array(
		'slug'        => 'turismo',
		'label'       => __( 'Turismo', 'caaguazu-turismo' ),
		'url'         => caaguazu_turismo_hub_url(),
		'dropdown_cb' => 'caaguazu_render_turismo_dropdown',
	);
	return $items;
} );

/**
 * Las 6 secciones de primer nivel de Turismo (las mismas de la grilla del
 * hub) — a diferencia de caaguazu_turismo_menu_groups() (sub-secciones del
 * mega-menú), esta lista es la que usa el shell propio de Turismo que
 * expone el theme (header/tabbar/footer distintos mientras se navega
 * dentro del ecosistema) vía el filtro `caaguazu_tourism_shell_items`.
 */
function caaguazu_turismo_shell_sections() {
	return array(
		// slug => [ clave de ícono del theme (inc/icons.php), label completo (nav/footer), label corto (tabbar) ]
		'la-capital-de-la-madera' => array( 'wood', __( 'La Capital de la Madera', 'caaguazu-turismo' ), __( 'Historia', 'caaguazu-turismo' ) ),
		'que-hacer'               => array( 'nature', __( 'Qué hacer', 'caaguazu-turismo' ), __( 'Qué hacer', 'caaguazu-turismo' ) ),
		'platos-tipicos'          => array( 'food', __( 'Sabores', 'caaguazu-turismo' ), __( 'Sabores', 'caaguazu-turismo' ) ),
		'festividades'            => array( 'celebration', __( 'Vivir Caaguazú', 'caaguazu-turismo' ), __( 'Cultura', 'caaguazu-turismo' ) ),
		'como-llegar'             => array( 'map', __( 'Planificá tu visita', 'caaguazu-turismo' ), __( 'Visitar', 'caaguazu-turismo' ) ),
		'contacto'                => array( 'mail', __( 'Contacto', 'caaguazu-turismo' ), __( 'Contacto', 'caaguazu-turismo' ) ),
	);
}

add_filter( 'caaguazu_tourism_shell_items', function ( $items ) {
	foreach ( caaguazu_turismo_shell_sections() as $slug => $data ) {
		$items[] = array(
			'slug'  => $slug,
			'label' => $data[1],
			'short' => $data[2],
			'icon'  => $data[0],
			'url'   => caaguazu_tourism_page_url( $slug ),
		);
	}
	if ( post_type_exists( 'cgz_local' ) ) {
		$items[] = array(
			'slug'  => 'directorio',
			'label' => __( 'Directorio de locales', 'caaguazu-turismo' ),
			'short' => __( 'Directorio', 'caaguazu-turismo' ),
			'icon'  => 'pin',
			'url'   => get_post_type_archive_link( 'cgz_local' ),
		);
	}
	return $items;
} );
