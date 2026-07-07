<?php
/**
 * Plugin Name:       Caaguazú Módulos
 * Plugin URI:        https://caaguazu.net
 * Description:       Módulos de contenido del portal (Noticias, Agenda, Ecosistema) como plugin — separados del theme para que el sitio funcione con cualquier apariencia y cada módulo se pueda activar/desactivar sin tocar código de presentación. Se registran solos en el nav y en los accesos rápidos del home vía los filtros `caaguazu_nav_items`/`caaguazu_quick_access_items` del theme.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Departamento de Caaguazú
 * Author URI:        https://caaguazu.net
 * License:           Proprietary
 * Text Domain:       caaguazu-modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CAAGUAZU_MODULOS_VERSION', '1.1.0' );
define( 'CAAGUAZU_MODULOS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAAGUAZU_MODULOS_URI', plugin_dir_url( __FILE__ ) );

/**
 * Cada módulo es un archivo independiente en includes/modules/ que:
 *  1. Registra su propio CPT/datos (si aplica).
 *  2. Se engancha a `caaguazu_quick_access_items` y `caaguazu_nav_items`
 *     (filtros expuestos por el theme en inc/helpers.php) para agregar su
 *     propia tarjeta/link — el theme no necesita saber que este módulo existe.
 *  3. Si el theme activo no expone esos filtros (otro theme, o una versión
 *     vieja de este), el módulo sigue funcionando igual: el CPT/contenido
 *     existe, solo no aparece en esos dos atajos de navegación.
 *
 * Agregar un módulo nuevo = copiar uno de estos archivos como plantilla y
 * sumarlo al require de abajo.
 */
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-noticias.php';
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-agenda.php';
require_once CAAGUAZU_MODULOS_DIR . 'includes/modules/module-ecosistema.php';

register_activation_hook( __FILE__, 'caaguazu_modulos_activate' );

function caaguazu_modulos_activate() {
	caaguazu_modulos_seed_noticias();
	caaguazu_modulos_seed_agenda();
	caaguazu_modulos_seed_ecosistema_page();
	flush_rewrite_rules();
}
