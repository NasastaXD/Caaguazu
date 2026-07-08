<?php
/**
 * Plugin Name:       Caaguazú Turismo
 * Plugin URI:        https://caaguazu.net
 * Description:       Módulo Turismo del portal: 22 páginas de historia, oficio maderero, gastronomía y cultura guaraní (migradas del sitio de turismo original), como plugin independiente del theme. Orquesta —sin modificar su código— los plugins Caaguazú Locales (directorio de negocios/reservas) y Caaguazú Portal (destinos de promotores), cuyos shortcodes ya están embebidos en el contenido sembrado acá. Se registra solo en el nav y los accesos rápidos del theme vía los filtros `caaguazu_nav_items`/`caaguazu_quick_access_items`.
 * Version:           1.6.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Departamento de Caaguazú
 * Author URI:        https://caaguazu.net
 * License:           Proprietary
 * Text Domain:       caaguazu-turismo
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CAAGUAZU_TURISMO_VERSION', '1.6.0' );
define( 'CAAGUAZU_TURISMO_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAAGUAZU_TURISMO_URI', plugin_dir_url( __FILE__ ) );

require_once CAAGUAZU_TURISMO_DIR . 'includes/tourism-content.php';
require_once CAAGUAZU_TURISMO_DIR . 'includes/tourism-seeder.php';
require_once CAAGUAZU_TURISMO_DIR . 'includes/nav-integration.php';

register_activation_hook( __FILE__, 'caaguazu_seed_tourism_on_activation' );
