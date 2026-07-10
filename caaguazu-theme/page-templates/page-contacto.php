<?php
/**
 * Template Name: Página de contacto
 *
 * Info institucional + formulario de contacto con envío real de email.
 *
 * @package Caaguazu
 */

if ( caaguazu_maybe_render_builder_content() ) {
	return;
}

get_header();

while ( have_posts() ) :
	the_post();

	$org        = caaguazu_opt( 'contact_org', 'Thiago Juan Manuel Ávalos Crosta' );
	$disclaimer = caaguazu_opt( 'contact_disclaimer', __( 'Sitio sin afiliación gubernamental', 'caaguazu' ) );
	$city       = caaguazu_opt( 'contact_city', 'Ciudad de Caaguazú, Paraguay' );
	$phone      = caaguazu_opt( 'contact_phone', '' );
	$email      = caaguazu_opt( 'contact_email', 'thiagojuanma5@gmail.com' );
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<section class="container page-hero">
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Contacto', 'caaguazu' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<p class="sub"><?php esc_html_e( 'Datos de contacto y formulario para consultas.', 'caaguazu' ); ?></p>
		</div>
	</section>

	<?php
	$content = trim( wp_strip_all_tags( get_the_content() ) );
	if ( $content ) : ?>
		<div class="container page-content">
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="container page-content">
		<div class="contact-grid">
			<div>
				<p><strong><?php echo esc_html( $org ); ?></strong></p>
				<?php if ( $disclaimer ) : ?><p><em><?php echo esc_html( $disclaimer ); ?></em></p><?php endif; ?>
				<p><?php echo esc_html( $city ); ?></p>
				<?php if ( $phone ) : ?><p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
				<p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
			</div>
			<div>
				<?php echo caaguazu_contact_form_html(); ?>
			</div>
		</div>
	</div>

	<?php
endwhile;

get_footer();
