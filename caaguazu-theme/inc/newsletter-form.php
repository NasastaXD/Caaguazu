<?php
/**
 * Captura de email para newsletter (footer), guardada como CPT interno.
 * Sin integración con un proveedor externo — es el punto de partida para
 * conectar uno más adelante sin cambiar el frontend.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_newsletter_form_html() {
	$status = isset( $_GET['newsletter'] ) ? sanitize_key( $_GET['newsletter'] ) : '';
	ob_start();
	?>
	<form class="newsletter-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="caaguazu_submit_newsletter">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ) ); ?>">
		<?php wp_nonce_field( 'caaguazu_submit_newsletter', 'caaguazu_newsletter_nonce' ); ?>
		<?php caaguazu_honeypot_field( 'caaguazu_hp_field_nl' ); ?>
		<label for="newsletter_email" class="screen-reader-text"><?php esc_html_e( 'Tu email', 'caaguazu' ); ?></label>
		<input type="email" id="newsletter_email" name="newsletter_email" required
			placeholder="<?php esc_attr_e( 'tu@email.com', 'caaguazu' ); ?>">
		<button type="submit"><?php esc_html_e( 'Suscribirme', 'caaguazu' ); ?></button>
	</form>
	<?php if ( 'ok' === $status ) : ?>
		<p class="newsletter-msg newsletter-ok"><?php esc_html_e( '¡Listo! Ya estás suscripto.', 'caaguazu' ); ?></p>
	<?php elseif ( 'error' === $status ) : ?>
		<p class="newsletter-msg newsletter-error"><?php esc_html_e( 'No pudimos suscribirte, probá de nuevo.', 'caaguazu' ); ?></p>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

function caaguazu_handle_newsletter_submission() {
	$redirect_to = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url( '/' );

	if ( ! isset( $_POST['caaguazu_newsletter_nonce'] ) || ! wp_verify_nonce( $_POST['caaguazu_newsletter_nonce'], 'caaguazu_submit_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect_to ) );
		exit;
	}

	if ( caaguazu_is_spam_submission( 'caaguazu_hp_field_nl' ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect_to ) );
		exit;
	}

	if ( caaguazu_rate_limit_exceeded( 'newsletter', 8 ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect_to ) );
		exit;
	}

	$email = isset( $_POST['newsletter_email'] ) ? sanitize_email( $_POST['newsletter_email'] ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect_to ) );
		exit;
	}

	$existing = get_posts( array(
		'post_type'      => 'caaguazu_subscriber',
		'post_status'    => 'any',
		'title'          => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( empty( $existing ) ) {
		wp_insert_post( array(
			'post_type'   => 'caaguazu_subscriber',
			'post_status' => 'publish',
			'post_title'  => $email,
		) );
	}

	wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect_to ) );
	exit;
}
add_action( 'admin_post_caaguazu_submit_newsletter', 'caaguazu_handle_newsletter_submission' );
add_action( 'admin_post_nopriv_caaguazu_submit_newsletter', 'caaguazu_handle_newsletter_submission' );
