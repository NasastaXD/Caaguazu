<?php
/**
 * Formulario público "Reportá un problema" (311 ciudadano): sin login,
 * con honeypot + rate-limit + nonce. Disponible como función de plantilla
 * y como shortcode [caaguazu_reportar].
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode( 'caaguazu_reportar', 'caaguazu_report_form_html' );

function caaguazu_report_form_html() {
	ob_start();
	$status = isset( $_GET['reporte'] ) ? sanitize_key( $_GET['reporte'] ) : '';
	?>
	<?php if ( 'ok' === $status ) : ?>
		<div class="form-success"><?php caaguazu_i18n( 'report.success', __( '¡Gracias! Tu reporte fue recibido y será revisado por el equipo correspondiente.', 'caaguazu' ) ); ?></div>
	<?php elseif ( 'error' === $status ) : ?>
		<div class="form-error"><?php caaguazu_i18n( 'report.error', __( 'No pudimos registrar tu reporte. Completá los campos obligatorios e intentá de nuevo.', 'caaguazu' ) ); ?></div>
	<?php endif; ?>

	<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="caaguazu_submit_report">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'caaguazu_submit_report', 'caaguazu_report_nonce' ); ?>
		<?php caaguazu_honeypot_field(); ?>

		<div class="field">
			<label for="report_category"><?php caaguazu_i18n( 'report.field.category', __( 'Categoría', 'caaguazu' ) ); ?></label>
			<select id="report_category" name="report_category" required>
				<?php foreach ( caaguazu_report_categories() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="field">
			<label for="report_location"><?php caaguazu_i18n( 'report.field.location', __( 'Ubicación (calle, barrio o referencia)', 'caaguazu' ) ); ?></label>
			<input type="text" id="report_location" name="report_location" required>
		</div>

		<div class="field">
			<label for="report_description"><?php caaguazu_i18n( 'report.field.description', __( 'Descripción del problema', 'caaguazu' ) ); ?></label>
			<textarea id="report_description" name="report_description" required minlength="10"></textarea>
		</div>

		<div class="field-row">
			<div class="field">
				<label for="report_contact_name"><?php caaguazu_i18n( 'report.field.name', __( 'Nombre (opcional)', 'caaguazu' ) ); ?></label>
				<input type="text" id="report_contact_name" name="report_contact_name">
			</div>
			<div class="field">
				<label for="report_contact_email"><?php caaguazu_i18n( 'report.field.email', __( 'Email (opcional)', 'caaguazu' ) ); ?></label>
				<input type="email" id="report_contact_email" name="report_contact_email">
			</div>
		</div>
		<div class="field">
			<label for="report_contact_phone"><?php caaguazu_i18n( 'report.field.phone', __( 'Teléfono (opcional)', 'caaguazu' ) ); ?></label>
			<input type="tel" id="report_contact_phone" name="report_contact_phone">
		</div>

		<div class="form-submit">
			<button type="submit" class="btn btn-primary"><?php caaguazu_i18n( 'report.submit', __( 'Enviar reporte', 'caaguazu' ) ); ?></button>
		</div>
	</form>
	<?php
	return ob_get_clean();
}

function caaguazu_handle_report_submission() {
	$redirect_to = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url( '/' );

	if ( ! isset( $_POST['caaguazu_report_nonce'] ) || ! wp_verify_nonce( $_POST['caaguazu_report_nonce'], 'caaguazu_submit_report' ) ) {
		wp_safe_redirect( add_query_arg( 'reporte', 'error', $redirect_to ) );
		exit;
	}

	if ( caaguazu_is_spam_submission() ) {
		// No delatar al bot: redirigir como si hubiese salido bien.
		wp_safe_redirect( add_query_arg( 'reporte', 'ok', $redirect_to ) );
		exit;
	}

	if ( caaguazu_rate_limit_exceeded( 'report' ) ) {
		wp_safe_redirect( add_query_arg( 'reporte', 'error', $redirect_to ) );
		exit;
	}

	$category    = isset( $_POST['report_category'] ) ? sanitize_key( $_POST['report_category'] ) : '';
	$location    = isset( $_POST['report_location'] ) ? sanitize_text_field( $_POST['report_location'] ) : '';
	$description = isset( $_POST['report_description'] ) ? sanitize_textarea_field( $_POST['report_description'] ) : '';
	$contact_name  = isset( $_POST['report_contact_name'] ) ? sanitize_text_field( $_POST['report_contact_name'] ) : '';
	$contact_email = isset( $_POST['report_contact_email'] ) ? sanitize_email( $_POST['report_contact_email'] ) : '';
	$contact_phone = isset( $_POST['report_contact_phone'] ) ? sanitize_text_field( $_POST['report_contact_phone'] ) : '';

	$categories = caaguazu_report_categories();
	if ( ! array_key_exists( $category, $categories ) || '' === $location || strlen( $description ) < 10 ) {
		wp_safe_redirect( add_query_arg( 'reporte', 'error', $redirect_to ) );
		exit;
	}

	$title_excerpt = implode( ' ', array_slice( preg_split( '/\s+/', $description ), 0, 6 ) );
	$post_id = wp_insert_post( array(
		'post_type'    => 'caaguazu_report',
		'post_status'  => 'pending',
		'post_title'   => sprintf( '%s — %s', $categories[ $category ], $title_excerpt ),
		'post_content' => $description,
	) );

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'reporte', 'error', $redirect_to ) );
		exit;
	}

	update_post_meta( $post_id, '_caaguazu_report_category', $category );
	update_post_meta( $post_id, '_caaguazu_report_location', $location );
	if ( $contact_name ) { update_post_meta( $post_id, '_caaguazu_report_contact_name', $contact_name ); }
	if ( $contact_email ) { update_post_meta( $post_id, '_caaguazu_report_contact_email', $contact_email ); }
	if ( $contact_phone ) { update_post_meta( $post_id, '_caaguazu_report_contact_phone', $contact_phone ); }

	caaguazu_notify_admin(
		sprintf( '[Caaguazú] Nuevo reporte: %s', $categories[ $category ] ),
		"Categoría: {$categories[$category]}\nUbicación: {$location}\n\nDescripción:\n{$description}\n\nContacto: {$contact_name} {$contact_email} {$contact_phone}",
		$contact_email,
		$contact_name
	);

	wp_safe_redirect( add_query_arg( 'reporte', 'ok', $redirect_to ) );
	exit;
}
add_action( 'admin_post_caaguazu_submit_report', 'caaguazu_handle_report_submission' );
add_action( 'admin_post_nopriv_caaguazu_submit_report', 'caaguazu_handle_report_submission' );
