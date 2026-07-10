<?php
/**
 * Plugin Name:       Caaguazú Módulos
 * Plugin URI:        https://caaguazu.net
 * Description:       Módulos de contenido del portal (Noticias, Agenda, Ecosistema, Educación) como plugin — separados del theme para que el sitio funcione con cualquier apariencia y cada módulo se pueda activar/desactivar sin tocar código de presentación. Se registran solos en el nav y en los accesos rápidos del home vía los filtros `caaguazu_nav_items`/`caaguazu_quick_access_items` del theme.
 * Version:           1.7.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Thiago Juan Manuel Ávalos Crosta
 * Author URI:        mailto:thiagoavalos900@gmail.com
 * License:           Proprietary
 * Text Domain:       caaguazu-modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CAAGUAZU_MODULOS_VERSION', '1.7.2' );
define( 'CAAGUAZU_MODULOS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAAGUAZU_MODULOS_URI', plugin_dir_url( __FILE__ ) );

/**
 * Crea (o encuentra) una categoría nativa por nombre, con slug/padre
 * opcionales — helper compartido por los módulos que organizan su
 * contenido con Categorías de WordPress en vez de un custom post type
 * propio (Noticias, Agenda, Educación: desde la 1.5, todos viven como
 * Entradas nativas — "por algo usamos WordPress", como bien señaló quien
 * encargó este cambio).
 */
function caaguazu_ensure_category( $name, $slug = '', $parent_id = 0 ) {
	$term = term_exists( $name, 'category' );
	if ( ! $term ) {
		$args = array();
		if ( $slug ) {
			$args['slug'] = $slug;
		}
		if ( $parent_id ) {
			$args['parent'] = $parent_id;
		}
		$term = wp_insert_term( $name, 'category', $args );
	}
	return is_wp_error( $term ) ? 0 : (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

/**
 * Cada módulo es un archivo independiente en includes/modules/ que:
 *  1. Registra su propio contenido (categorías nativas + meta, o un CPT
 *     propio si el contenido no es cronológico como Noticias/Agenda/
 *     Educación — ver "Agregar un módulo nuevo" en el README).
 *  2. Se engancha a `caaguazu_quick_access_items` y `caaguazu_nav_items`
 *     (filtros expuestos por el theme en inc/helpers.php) para agregar su
 *     propia tarjeta/link — el theme no necesita saber que este módulo existe.
 *  3. Si el theme activo no expone esos filtros (otro theme, o una versión
 *     vieja de este), el módulo sigue funcionando igual: el contenido
 *     existe, solo no aparece en esos dos atajos de navegación.
 *
 * Agregar un módulo nuevo = copiar uno de estos archivos como plantilla y
 * sumarlo al require de abajo.
 */
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-noticias.php';
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-agenda.php';
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-ecosistema.php';
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-educacion.php';

// Auto-update desde los GitHub Releases del repo (mismo release que el
// theme; la versión propia sale del manifest.json del release).
require_once CAAGUAZU_MODULOS_DIR . 'includes/updater.php';
new Caaguazu_Component_Updater( __FILE__, CAAGUAZU_MODULOS_VERSION, 'caaguazu-modulos', 'Caaguazú Módulos' );

register_activation_hook( __FILE__, 'caaguazu_modulos_activate' );

function caaguazu_modulos_activate() {
	caaguazu_modulos_seed_noticias();
	caaguazu_modulos_seed_agenda();
	caaguazu_modulos_seed_ecosistema_page();
	caaguazu_modulos_seed_educacion();
	caaguazu_modulos_flatten_category_base();
	flush_rewrite_rules();
}

/**
 * Noticias/Agenda/Educación viven en Categorías nativas desde la 1.5 —
 * sin esto sus archivos cuelgan de /category/noticias/ en vez de
 * /noticias/. Se corre una sola vez (catch-up en un sitio ya activo, vía
 * admin_init, porque register_activation_hook no vuelve a disparar en una
 * actualización de plugin ya activo).
 */
function caaguazu_modulos_flatten_category_base() {
	if ( '' !== get_option( 'category_base' ) ) {
		update_option( 'category_base', '' );
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'caaguazu_modulos_flatten_category_base' );
