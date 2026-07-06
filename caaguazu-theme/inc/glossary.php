<?php
/**
 * Glosario guaraní interactivo: shortcode [gn]término[/gn] que pinta un
 * tooltip accesible (pronunciación + definición) sobre términos guaraní
 * usados en el contenido. El diccionario es el mismo que alimenta la
 * página "Guaraní en nuestra ciudad" migrada del sitio de turismo.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_glossary_terms() {
	return array(
		"ka'a guasu"      => array( 'term' => "Ka'a Guasu",       'ipa' => "kaˈʔa guaˈsu",     'def' => 'Monte grande / selva grande — el nombre original del territorio de Caaguazú.' ),
		'ykua'            => array( 'term' => 'Ykua',             'ipa' => 'ɨˈkua',            'def' => 'Manantial / fuente de agua, como en Ykua La Patria, el manantial fundacional.' ),
		'ryguasu chyryry' => array( 'term' => 'Ryguasu chyryry',  'ipa' => 'rɨguaˈsu tʃɨrɨˈrɨ', 'def' => 'Gallina frita tradicional, plato emblema de Caaguazú.' ),
		"mandi'o"         => array( 'term' => "Mandi'o",          'ipa' => 'manˈdiʔo',         'def' => 'Mandioca / yuca.' ),
		'tererê'          => array( 'term' => 'Tereré',           'ipa' => 'tereˈre',          'def' => 'Yerba mate fría, el ritual social paraguayo por excelencia.' ),
		'terere'          => array( 'term' => 'Tereré',           'ipa' => 'tereˈre',          'def' => 'Yerba mate fría, el ritual social paraguayo por excelencia.' ),
		'poha ñana'       => array( 'term' => 'Poha ñana',        'ipa' => 'poˈha ɲaˈna',      'def' => 'Plantas medicinales.' ),
		'techapyrã'       => array( 'term' => 'Techapyrã',        'ipa' => 'tetʃapɨˈɾã',       'def' => 'Mucho por ver.' ),
		'sopa paraguaya'  => array( 'term' => 'Sopa paraguaya',   'ipa' => 'ˈsopa paɾaˈɣwaja', 'def' => 'Pastel de maíz — pese al nombre, no es una sopa.' ),
		'vori vori'       => array( 'term' => 'Vori vori',        'ipa' => 'ˈvori ˈvori',      'def' => 'Sopa con bolitas de maíz.' ),
		'kesu'            => array( 'term' => 'Kesu',             'ipa' => 'keˈsu',            'def' => 'Queso fresco paraguayo.' ),
	);
}

/**
 * [gn]Ka'a Guasu[/gn] o [gn term="tererê"]tereré[/gn] si el texto visible
 * difiere de la clave del diccionario (mayúsculas, variantes, etc).
 */
function caaguazu_glossary_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'term' => '' ), $atts, 'gn' );
	$key  = sanitize_text_field( $atts['term'] ? $atts['term'] : $content );
	$key  = strtolower( trim( $key ) );

	$dict = caaguazu_glossary_terms();
	if ( ! isset( $dict[ $key ] ) ) {
		return esc_html( $content );
	}
	$entry = $dict[ $key ];

	return sprintf(
		'<span class="gn-term" tabindex="0" role="button" aria-describedby="%1$s"><span class="i18n-es">%2$s</span><span class="i18n-gn" hidden>%2$s</span><span class="gn-tip" id="%1$s" role="tooltip"><strong>%3$s</strong><br><em>/%4$s/</em><br>%5$s</span></span>',
		esc_attr( 'gn-tip-' . md5( $key ) . '-' . wp_unique_id() ),
		esc_html( $content ? $content : $entry['term'] ),
		esc_html( $entry['term'] ),
		esc_html( $entry['ipa'] ),
		esc_html( $entry['def'] )
	);
}
add_shortcode( 'gn', 'caaguazu_glossary_shortcode' );
