<?php
/**
 * Formulario de contacto con envío real de email (antes solo texto estático
 * en footer/customizer). Mismo patrón de nonce + honeypot + rate-limit que
 * el reporte ciudadano (inc/report-form.php).
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_contact_subjects() {
	return array(
		'general'  => __( 'Consulta general', 'caaguazu' ),
		'prensa'   => __( 'Prensa', 'caaguazu' ),
		'tramites' => __( 'Trámites', 'caaguazu' ),
		'otro'     => __( 'Otro', 'caaguazu' ),
	);
}

function caaguazu_contact_form_html() {
	ob_start();
	$status = isset( $_GET['contacto'] ) ? sanitize_key( $_GET['contacto'] ) : '';
	?>
	<?php if ( 'ok' === $status ) : ?>
		<div class="form-success"><?php caaguazu_i18n( 'contact.success', __( '¡Gracias por escribirnos! Te vamos a responder a la brevedad.', 'caaguazu' ) ); ?></div>
	<?php elseif ( 'error' === $status ) : ?>
		<div class="form-error"><?php caaguazu_i18n( 'contact.error', __( 'No pudimos enviar tu mensaje. Completá los campos obligatorios e intentá de nuevo.', 'caaguazu' ) ); ?></div>
	<?php endif; ?>

	<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="caaguazu_submit_contact">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'caaguazu_submit_contact', 'caaguazu_contact_nonce' ); ?>
		<?php caaguazu_honeypot_field(); ?>

		<div class="field-row">
			<div class="field">
				<label for="contact_name"><?php caaguazu_i18n( 'contact.field.name', __( 'Nombre', 'caaguazu' ) ); ?></label>
				<input type="text" id="contact_name" name="contact_name" required>
			</div>
			<div class="field">
				<label for="contact_email"><?php caaguazu_i18n( 'contact.field.email', __( 'Email', 'caaguazu' ) ); ?></label>
				<input type="email" id="contact_email" name="contact_email" required>
			</div>
		</div>

		<div class="field">
			<label for="contact_subject"><?php caaguazu_i18n( 'contact.field.subject', __( 'Asunto', 'caaguazu' ) ); ?></label>
			<select id="contact_subject" name="contact_subject" required>
				<?php foreach ( caaguazu_contact_subjects() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="field">
			<label for="contact_message"><?php caaguazu_i18n( 'contact.field.message', __( 'Mensaje', 'caaguazu' ) ); ?></label>
			<textarea id="contact_message" name="contact_message" required minlength="10"></textarea>
		</div>

		<div class="form-submit">
			<button type="submit" class="btn btn-primary"><?php caaguazu_i18n( 'contact.submit', __( 'Enviar mensaje', 'caaguazu' ) ); ?></button>
		</div>
	</form>
	<?php
	return ob_get_clean();
}

function caaguazu_handle_contact_submission() {
	$redirect_to = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url( '/' );

	if ( ! isset( $_POST['caaguazu_contact_nonce'] ) || ! wp_verify_nonce( $_POST['caaguazu_contact_nonce'], 'caaguazu_submit_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contacto', 'error', $redirect_to ) );
		exit;
	}

	if ( caaguazu_is_spam_submission() ) {
		wp_safe_redirect( add_query_arg( 'contacto', 'ok', $redirect_to ) );
		exit;
	}

	if ( caaguazu_rate_limit_exceeded( 'contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contacto', 'error', $redirect_to ) );
		exit;
	}

	$name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( $_POST['contact_name'] ) : '';
	$email   = isset( $_POST['contact_email'] ) ? sanitize_email( $_POST['contact_email'] ) : '';
	$subject = isset( $_POST['contact_subject'] ) ? sanitize_key( $_POST['contact_subject'] ) : '';
	$message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( $_POST['contact_message'] ) : '';

	$subjects = caaguazu_contact_subjects();
	if ( '' === $name || ! is_email( $email ) || ! array_key_exists( $subject, $subjects ) || strlen( $message ) < 10 ) {
		wp_safe_redirect( add_query_arg( 'contacto', 'error', $redirect_to ) );
		exit;
	}

	$sent = caaguazu_notify_admin(
		sprintf( '[Caaguazú] Contacto: %s', $subjects[ $subject ] ),
		"Nombre: {$name}\nEmail: {$email}\nAsunto: {$subjects[$subject]}\n\nMensaje:\n{$message}",
		$email,
		$name
	);

	wp_safe_redirect( add_query_arg( 'contacto', $sent ? 'ok' : 'error', $redirect_to ) );
	exit;
}
add_action( 'admin_post_caaguazu_submit_contact', 'caaguazu_handle_contact_submission' );
add_action( 'admin_post_nopriv_caaguazu_submit_contact', 'caaguazu_handle_contact_submission' );
