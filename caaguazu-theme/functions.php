<?php
/**
 * Caaguazú theme — bootstrap mínimo para la página de obra.
 *
 * El sitio está en reconstrucción: el theme sirve una sola plantilla
 * (index.php) para todo el frontend, sin menús, sin widgets, sin JavaScript
 * y sin ningún pedido a un tercero. Todo lo que WordPress encola de más
 * (block library, global styles, emojis) se saca acá para que la página
 * pese lo que dice pesar.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Fuente única de verdad: el header "Version:" de style.css (la misma que
// inc/updater.php compara contra el último GitHub Release).
define( 'CAAGUAZU_VERSION', wp_get_theme()->get( 'Version' ) );

/* ---------------------------------------------------------------------------
 * Setup
 * ------------------------------------------------------------------------ */

function caaguazu_setup() {
	load_theme_textdomain( 'caaguazu', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'caaguazu_setup' );

/* ---------------------------------------------------------------------------
 * Assets
 * ------------------------------------------------------------------------ */

function caaguazu_enqueue_assets() {
	// El Portal de promotores (turismo-panel) tiene su propio wp_head() y su
	// propio CSS —tokens y tipografía propios, nada prestado del theme— pero
	// este enqueue corre en TODA carga del sitio, panel incluido: sin esta
	// guarda, la hoja de esta página de obra (con su aro decorativo de fondo)
	// se colaba detrás del panel en cada pantalla.
	if ( '' !== get_query_var( 'promotur_route' ) ) {
		return;
	}

	wp_enqueue_style( 'caaguazu-obra', get_stylesheet_uri(), array(), CAAGUAZU_VERSION );

	// La página no usa bloques ni theme.json: nada de esto pinta nada, sólo
	// suma bytes y requests.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'caaguazu_enqueue_assets', 100 );

// Emojis: script + CSS inline en cada carga, para nada.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/**
 * Favicon desde el ícono del portal, salvo que ya haya un Site Icon cargado
 * en Ajustes → General (ese gana, WordPress lo imprime solo).
 */
function caaguazu_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	$icons = get_template_directory_uri() . '/assets/icons';
	printf( '<link rel="icon" href="%s/icon-192.png" sizes="192x192">' . "\n", esc_url( $icons ) );
	printf( '<link rel="apple-touch-icon" href="%s/icon-180.png">' . "\n", esc_url( $icons ) );
}
add_action( 'wp_head', 'caaguazu_favicon' );

/**
 * Mientras dure la obra no queremos que los buscadores indexen esta página
 * como si fuera el sitio. Al volver el sitio real, se saca esta línea.
 */
add_filter( 'wp_robots', 'wp_robots_no_robots' );

/* ---------------------------------------------------------------------------
 * Dibujos (SVG inline: no cuestan un request ni se pixelan)
 * ------------------------------------------------------------------------ */

/**
 * Isotipo del portal —las dos hojas dentro del aro de assets/icons/—,
 * redibujado en la paleta azul de esta página. Decorativo: al lado siempre
 * va el texto "caaguazu.net".
 */
function caaguazu_marca() {
	?>
	<svg viewBox="0 0 48 48" role="presentation" aria-hidden="true" focusable="false">
		<circle cx="24" cy="24" r="20" fill="#2a3fbf" />
		<g transform="translate(24 32) scale(.78)">
			<path transform="rotate(-19)" fill="#b9c3f5" d="M0 0C-7 -6 -7.5 -14 0 -20C7.5 -14 7 -6 0 0Z" />
			<path transform="rotate(19)" fill="#ffffff" d="M0 0C-7 -6 -7.5 -14 0 -20C7.5 -14 7 -6 0 0Z" />
		</g>
	</svg>
	<?php
}

/**
 * Escena de obra: monitor vallado con cinta, grúa, cartel de precaución y
 * conos. Vector plano dibujado a mano acá mismo — nada de stock, nada que
 * descargar.
 */
function caaguazu_ilustracion() {
	?>
	<svg viewBox="0 40 620 360" role="img"
	     aria-label="<?php esc_attr_e( 'Ilustración: un monitor cruzado por cinta de obra, una grúa con un cartel de precaución y conos de seguridad.', 'caaguazu' ); ?>">
		<defs>
			<pattern id="czu-rayas" width="26" height="26" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
				<rect width="26" height="26" fill="#ffc93c" />
				<rect width="13" height="26" fill="#1b2559" />
			</pattern>
		</defs>

		<!-- Mancha de fondo -->
		<path fill="#eef1fd" d="M110 120C150 40 300 30 400 60c100 30 192 60 196 150 4 90-76 162-196 174-140 14-280-4-330-84-40-64-10-110 40-180Z" />

		<!-- Suelo -->
		<rect x="40" y="370" width="540" height="4" rx="2" fill="#d7ddf7" />

		<!-- Grúa -->
		<g>
			<rect x="430" y="352" width="60" height="20" rx="5" fill="#1b2559" />
			<rect x="450" y="150" width="20" height="206" fill="#2a3fbf" />
			<path d="M450 320 470 280M470 320 450 280M450 260 470 220M470 260 450 220M450 200 470 160M470 200 450 160"
			      stroke="#5568d6" stroke-width="4" stroke-linecap="round" fill="none" />
			<rect x="348" y="138" width="212" height="16" rx="5" fill="#2a3fbf" />
			<rect x="528" y="122" width="30" height="34" rx="6" fill="#1b2559" />
			<path d="M392 154v62" stroke="#1b2559" stroke-width="3.5" stroke-linecap="round" />
		</g>

		<!-- Cartel de precaución colgando del cable -->
		<g transform="translate(392 262)">
			<rect x="-33" y="-33" width="66" height="66" rx="11" transform="rotate(45)"
			      fill="#ffc93c" stroke="#1b2559" stroke-width="4" />
			<rect x="-4.5" y="-21" width="9" height="26" rx="4.5" fill="#1b2559" />
			<circle cy="15" r="5" fill="#1b2559" />
		</g>

		<!-- Monitor vallado -->
		<g>
			<rect x="112" y="340" width="262" height="18" rx="9" fill="#2a3fbf" />
			<rect x="138" y="204" width="210" height="140" rx="12" fill="#1b2559" />
			<rect x="148" y="214" width="190" height="120" rx="6" fill="#2a3fbf" />
			<rect x="148" y="248" width="190" height="50" fill="url(#czu-rayas)" />
		</g>

		<!-- Conos -->
		<g fill="#ffc93c">
			<g transform="translate(92 370)">
				<path d="M-16 0-5-40h10L16 0Z" />
				<rect x="-11" y="-25" width="22" height="8" fill="#ffffff" opacity=".9" />
				<rect x="-21" y="-4" width="42" height="9" rx="4.5" fill="#e0a92b" />
			</g>
			<g transform="translate(424 370) scale(.8)">
				<path d="M-16 0-5-40h10L16 0Z" />
				<rect x="-11" y="-25" width="22" height="8" fill="#ffffff" opacity=".9" />
				<rect x="-21" y="-4" width="42" height="9" rx="4.5" fill="#e0a92b" />
			</g>
		</g>
	</svg>
	<?php
}

/* ---------------------------------------------------------------------------
 * Actualizaciones (GitHub Releases)
 * ------------------------------------------------------------------------ */

require_once get_template_directory() . '/inc/updater.php';
