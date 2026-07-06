<?php
/**
 * Anti-spam liviano compartido por los formularios públicos (reporte
 * ciudadano y contacto): honeypot + rate-limit por IP. Sin dependencias
 * externas ni servicios de terceros.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_honeypot_field( $name = 'caaguazu_hp_field' ) {
	printf(
		'<input type="text" name="%1$s" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">',
		esc_attr( $name )
	);
}

function caaguazu_is_spam_submission( $name = 'caaguazu_hp_field' ) {
	return ! empty( $_POST[ $name ] );
}

function caaguazu_get_client_ip() {
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}
	return '0.0.0.0';
}

/**
 * Máximo $max envíos por IP+bucket dentro de la ventana $window (segundos).
 * Devuelve true si ya se superó el límite (hay que bloquear el envío).
 */
function caaguazu_rate_limit_exceeded( $bucket, $max = 5, $window = HOUR_IN_SECONDS ) {
	$key   = 'caaguazu_rl_' . $bucket . '_' . md5( caaguazu_get_client_ip() );
	$count = (int) get_transient( $key );

	if ( $count >= $max ) {
		return true;
	}

	set_transient( $key, $count + 1, $window );
	return false;
}
