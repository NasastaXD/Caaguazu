<?php
/**
 * Plugin Name:       Caaguazú Editor UX
 * Plugin URI:        https://caaguazu.net
 * Description:       Simplifica el editor de bloques (Gutenberg) para las Entradas del portal: menos bloques, menos ruido visual, vista previa más cómoda y un panel editorial propio. Gutenberg sigue siendo el motor — este plugin sólo reordena la experiencia alrededor.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Thiago Juan Manuel Ávalos Crosta
 * Author URI:        mailto:thiagoavalos900@gmail.com
 * License:           Proprietary
 * Text Domain:       caaguazu-editor-ux
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CZU_VERSION', '1.0.0' );
define( 'CZU_DIR', plugin_dir_path( __FILE__ ) );
define( 'CZU_URI', plugin_dir_url( __FILE__ ) );

// MVP: sólo Entradas nativas ('post'). Noticias/Agenda/Educación ya viven
// ahí como Categorías (caaguazu-modulos), así que quedan cubiertas gratis;
// no se toca ningún otro post type (p. ej. caaguazu_artisan).
define( 'CZU_POST_TYPE', 'post' );

require_once CZU_DIR . 'includes/class-czu-editor-ux.php';
require_once CZU_DIR . 'includes/class-czu-editor-settings.php';
require_once CZU_DIR . 'includes/class-czu-editor-meta.php';

function czu_editor_ux_init() {
	new CZU_Editor_UX();
	new CZU_Editor_Settings();
	new CZU_Editor_Meta();
}
add_action( 'plugins_loaded', 'czu_editor_ux_init' );
