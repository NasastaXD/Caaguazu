<?php
/**
 * Formulario público "Proponer institución/lugar/servicio/proyecto/evento":
 * sin login, con honeypot + rate-limit + nonce — mismo patrón que
 * report-form.php. Disponible como función de plantilla y como shortcode
 * [caaguazu_proponer]. El tipo llega preseleccionado vía `?tipo=` desde los
 * CTA "Proponer institución"/"Proponer un lugar"/etc. de los archivos de
 * V5 (ver archive-institucion.php y análogos) — igual editable a mano.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode( 'caaguazu_proponer', 'caaguazu_proposal_form_html' );

function caaguazu_proposal_form_html() {
	ob_start();
	$status = isset( $_GET['propuesta'] ) ? sanitize_key( $_GET['propuesta'] ) : '';
	$preselected = isset( $_GET['tipo'] ) ? sanitize_key( $_GET['tipo'] ) : '';
	?>
	<?php if ( 'ok' === $status ) : ?>
		<div class="form-success"><?php esc_html_e( '¡Gracias! Tu propuesta fue recibida y será revisada por el equipo correspondiente.', 'caaguazu' ); ?></div>
	<?php elseif ( 'error' === $status ) : ?>
		<div class="form-error"><?php esc_html_e( 'No pudimos registrar tu propuesta. Completá los campos obligatorios e intentá de nuevo.', 'caaguazu' ); ?></div>
	<?php endif; ?>

	<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="caaguazu_submit_proposal">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'caaguazu_submit_proposal', 'caaguazu_proposal_nonce' ); ?>
		<?php caaguazu_honeypot_field(); ?>

		<div class="field">
			<label for="proposal_type"><?php esc_html_e( 'Tipo de propuesta', 'caaguazu' ); ?></label>
			<select id="proposal_type" name="proposal_type" required>
				<?php foreach ( caaguazu_proposal_types() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $preselected, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="field">
			<label for="proposal_title"><?php esc_html_e( 'Nombre (de la institución, lugar, servicio, proyecto o evento)', 'caaguazu' ); ?></label>
			<input type="text" id="proposal_title" name="proposal_title" required>
		</div>

		<div class="field">
			<label for="proposal_description"><?php esc_html_e( 'Contanos de qué se trata', 'caaguazu' ); ?></label>
			<textarea id="proposal_description" name="proposal_description" required minlength="10"></textarea>
		</div>

		<div class="field-row">
			<div class="field">
				<label for="proposal_contact_name"><?php esc_html_e( 'Nombre (opcional)', 'caaguazu' ); ?></label>
				<input type="text" id="proposal_contact_name" name="proposal_contact_name">
			</div>
			<div class="field">
				<label for="proposal_contact_email"><?php esc_html_e( 'Email (opcional)', 'caaguazu' ); ?></label>
				<input type="email" id="proposal_contact_email" name="proposal_contact_email">
			</div>
		</div>
		<div class="field">
			<label for="proposal_contact_phone"><?php esc_html_e( 'Teléfono (opcional)', 'caaguazu' ); ?></label>
			<input type="tel" id="proposal_contact_phone" name="proposal_contact_phone">
		</div>

		<div class="form-submit">
			<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Enviar propuesta', 'caaguazu' ); ?></button>
		</div>
	</form>
	<?php
	return ob_get_clean();
}

function caaguazu_handle_proposal_submission() {
	$redirect_to = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url( '/' );

	if ( ! isset( $_POST['caaguazu_proposal_nonce'] ) || ! wp_verify_nonce( $_POST['caaguazu_proposal_nonce'], 'caaguazu_submit_proposal' ) ) {
		wp_safe_redirect( add_query_arg( 'propuesta', 'error', $redirect_to ) );
		exit;
	}

	if ( caaguazu_is_spam_submission() ) {
		// No delatar al bot: redirigir como si hubiese salido bien.
		wp_safe_redirect( add_query_arg( 'propuesta', 'ok', $redirect_to ) );
		exit;
	}

	if ( caaguazu_rate_limit_exceeded( 'proposal' ) ) {
		wp_safe_redirect( add_query_arg( 'propuesta', 'error', $redirect_to ) );
		exit;
	}

	$type          = isset( $_POST['proposal_type'] ) ? sanitize_key( $_POST['proposal_type'] ) : '';
	$title         = isset( $_POST['proposal_title'] ) ? sanitize_text_field( $_POST['proposal_title'] ) : '';
	$description   = isset( $_POST['proposal_description'] ) ? sanitize_textarea_field( $_POST['proposal_description'] ) : '';
	$contact_name  = isset( $_POST['proposal_contact_name'] ) ? sanitize_text_field( $_POST['proposal_contact_name'] ) : '';
	$contact_email = isset( $_POST['proposal_contact_email'] ) ? sanitize_email( $_POST['proposal_contact_email'] ) : '';
	$contact_phone = isset( $_POST['proposal_contact_phone'] ) ? sanitize_text_field( $_POST['proposal_contact_phone'] ) : '';

	$types = caaguazu_proposal_types();
	if ( ! array_key_exists( $type, $types ) || '' === $title || strlen( $description ) < 10 ) {
		wp_safe_redirect( add_query_arg( 'propuesta', 'error', $redirect_to ) );
		exit;
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'caaguazu_proposal',
		'post_status'  => 'pending',
		'post_title'   => sprintf( '%s — %s', $types[ $type ], $title ),
		'post_content' => $description,
	) );

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'propuesta', 'error', $redirect_to ) );
		exit;
	}

	update_post_meta( $post_id, '_caaguazu_proposal_type', $type );
	if ( $contact_name ) { update_post_meta( $post_id, '_caaguazu_proposal_contact_name', $contact_name ); }
	if ( $contact_email ) { update_post_meta( $post_id, '_caaguazu_proposal_contact_email', $contact_email ); }
	if ( $contact_phone ) { update_post_meta( $post_id, '_caaguazu_proposal_contact_phone', $contact_phone ); }

	caaguazu_notify_admin(
		sprintf( '[Caaguazú] Nueva propuesta: %s', $types[ $type ] ),
		"Tipo: {$types[$type]}\nNombre: {$title}\n\nDescripción:\n{$description}\n\nContacto: {$contact_name} {$contact_email} {$contact_phone}",
		$contact_email,
		$contact_name
	);

	wp_safe_redirect( add_query_arg( 'propuesta', 'ok', $redirect_to ) );
	exit;
}
add_action( 'admin_post_caaguazu_submit_proposal', 'caaguazu_handle_proposal_submission' );
add_action( 'admin_post_nopriv_caaguazu_submit_proposal', 'caaguazu_handle_proposal_submission' );
