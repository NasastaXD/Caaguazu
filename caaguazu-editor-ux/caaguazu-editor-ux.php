<?php
/**
 * Plugin Name:       Caaguazú Editor UX
 * Plugin URI:        https://caaguazu.net
 * Description:       Simplifica el editor de bloques (Gutenberg) para el contenido cívico del portal: menos bloques, menos ruido visual, vista previa más cómoda y un panel editorial propio con metadatos de confianza. Gutenberg sigue siendo el motor — este plugin sólo reordena la experiencia alrededor.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Thiago Juan Manuel Ávalos Crosta
 * Author URI:        mailto:thiagoavalos900@gmail.com
 * License:           Proprietary
 * Text Domain:       caaguazu-editor-ux
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CZU_VERSION', '1.2.0' );
define( 'CZU_DIR', plugin_dir_path( __FILE__ ) );
define( 'CZU_URI', plugin_dir_url( __FILE__ ) );

/**
 * Post types que cubre este plugin. Arrancó (1.0) sólo con 'post' —
 * Noticias/Agenda/Educación ya viven ahí como Categorías (caaguazu-modulos),
 * así que quedaban cubiertas gratis. Desde 1.1 (V5, civic CMS) se suman los
 * CPTs nuevos de caaguazu-modulos (Instituciones/Lugares/Servicios/
 * Proyectos): mismo criterio — contenido editorial cívico escrito por una
 * persona, no páginas de layout libre — por eso comparten el mismo set de
 * bloques permitidos y el mismo panel. Cualquier OTRO post type del sitio
 * (p. ej. caaguazu_artisan) sigue con el editor de bloques estándar.
 */
define( 'CZU_POST_TYPES', array( 'post', 'institucion', 'lugar', 'servicio', 'proyecto' ) );

require_once CZU_DIR . 'includes/class-czu-editor-ux.php';
require_once CZU_DIR . 'includes/class-czu-editor-settings.php';
require_once CZU_DIR . 'includes/class-czu-editor-meta.php';
require_once CZU_DIR . 'includes/updater.php';

new Caaguazu_Component_Updater( __FILE__, CZU_VERSION, 'caaguazu-editor-ux', 'Caaguazú Editor UX' );

function czu_editor_ux_init() {
	new CZU_Editor_UX();
	new CZU_Editor_Settings();
	new CZU_Editor_Meta();
}
add_action( 'plugins_loaded', 'czu_editor_ux_init' );
