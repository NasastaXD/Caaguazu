<?php
/**
 * Sistema de íconos propio del theme: SVG de trazo simple (mismo estilo que
 * el ícono del banner de instalación en inc/pwa.php) en vez de emojis. Los
 * plugins de módulos (accesos rápidos, tabbar, shell de Turismo) pasan una
 * clave corta ('home', 'search', ...) en vez de un carácter — el theme es el
 * único dueño de cómo se ve cada ícono, así toda la navegación del sitio usa
 * un sistema visual consistente sin importar qué plugin lo declaró.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Devuelve el <svg> de un ícono por clave. Si la clave no está en el mapa,
 * se asume que ya es un emoji/HTML literal (compatibilidad con algún
 * plugin de terceros que todavía pase eso) y se sanitiza tal cual.
 */
function caaguazu_icon( $key ) {
	$icons = array(
		'home'        => '<path d="M3 9l9-7 9 7"/><polyline points="9 22 9 12 15 12 15 22"/><path d="M5 10v10a1 1 0 0 0 1 1h3M19 10v10a1 1 0 0 1-1 1h-3"/>',
		'search'      => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
		'menu'        => '<line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>',
		'news'        => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><polyline points="14 3 14 8 19 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>',
		// Lapacho, no pino: copa de tres lóbulos redondeados (no un triángulo
		// tipo conífera) + tronco + 3 puntos de floración — el árbol nacional
		// del Paraguay, no un genérico "árbol" de clip-art.
		'tree'        => '<circle cx="8" cy="11" r="4.3"/><circle cx="16" cy="11" r="4.3"/><circle cx="12" cy="8" r="4.6"/><line x1="12" y1="15.2" x2="12" y2="21"/><circle cx="8.6" cy="9.2" r=".9" fill="currentColor" stroke="none"/><circle cx="15.2" cy="8.6" r=".9" fill="currentColor" stroke="none"/><circle cx="12" cy="12.6" r=".9" fill="currentColor" stroke="none"/>',
		'mail'        => '<path d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/><polyline points="3 6 12 13 21 6"/>',
		'calendar'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><line x1="16" y1="3" x2="16" y2="7"/><line x1="8" y1="3" x2="8" y2="7"/><line x1="3" y1="10" x2="21" y2="10"/>',
		'globe'       => '<circle cx="12" cy="12" r="9"/><line x1="3" y1="12" x2="21" y2="12"/><path d="M12 3a13.5 13.5 0 0 1 3.6 9 13.5 13.5 0 0 1-3.6 9 13.5 13.5 0 0 1-3.6-9A13.5 13.5 0 0 1 12 3z"/>',
		'user'        => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>',
		'wood'        => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.2"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/>',
		'nature'      => '<path d="M3 19 9 8l3.5 5.5L15 10l6 9z"/><circle cx="17.5" cy="6.5" r="1.8"/>',
		'food'        => '<path d="M4 11h16a1 1 0 0 1 1 1 7 7 0 0 1-7 7h-4a7 7 0 0 1-7-7 1 1 0 0 1 1-1z"/><path d="M8 11V8M12 11V6M16 11V8"/>',
		'celebration' => '<path d="M12 3l2.2 6.8H21l-5.6 4.1L17.6 21 12 16.9 6.4 21l2.2-7.1L3 9.8h6.8z"/>',
		'map'         => '<polygon points="2 7 8 4 16 7 22 4 22 18 16 21 8 18 2 21"/><line x1="8" y1="4" x2="8" y2="18"/><line x1="16" y1="7" x2="16" y2="21"/>',
		'pin'         => '<path d="M20 10c0 6.5-8 12-8 12s-8-5.5-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="2.6"/>',
		'target'      => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/><line x1="12" y1="1.5" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22.5"/><line x1="1.5" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22.5" y2="12"/>',
		'chart'       => '<line x1="4" y1="20.5" x2="20" y2="20.5"/><line x1="7.5" y1="17" x2="7.5" y2="12"/><line x1="12" y1="17" x2="12" y2="6"/><line x1="16.5" y1="17" x2="16.5" y2="9.5"/>',
		'book'        => '<path d="M12 6.2c-2.1-1.5-5-2.2-8-2.2v13.4c3 0 5.9.7 8 2.2 2.1-1.5 5-2.2 8-2.2V4c-3 0-5.9.7-8 2.2z"/><line x1="12" y1="6.2" x2="12" y2="19.6"/>',
		'back'        => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="11 18 5 12 11 6"/>',
		'close'       => '<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>',
	);

	if ( isset( $icons[ $key ] ) ) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[ $key ] . '</svg>';
	}

	return wp_kses_post( $key );
}
