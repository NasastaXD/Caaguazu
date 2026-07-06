<?php
/**
 * Envío de notificaciones por mail al equipo de gestión, compartido por
 * los formularios de reporte ciudadano y contacto.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_notify_admin( $subject, $body, $reply_to_email = '', $reply_to_name = '' ) {
	$to      = caaguazu_opt( 'contact_email', get_option( 'admin_email' ) );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( $reply_to_email && is_email( $reply_to_email ) ) {
		$from = $reply_to_name ? sprintf( '%s <%s>', $reply_to_name, $reply_to_email ) : $reply_to_email;
		$headers[] = 'Reply-To: ' . $from;
	}

	return wp_mail( $to, $subject, $body, $headers );
}
