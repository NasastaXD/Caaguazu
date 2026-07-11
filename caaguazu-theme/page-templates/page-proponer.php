<?php
/**
 * Template Name: Proponer
 *
 * Formulario público de propuestas ciudadanas (institución, lugar,
 * servicio, proyecto o evento) — ver inc/proposal-form.php.
 *
 * @package Caaguazu
 */

if ( caaguazu_maybe_render_builder_content() ) {
	return;
}

get_header();

while ( have_posts() ) :
	the_post();
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
			<p class="eyebrow"><?php esc_html_e( 'Participación ciudadana', 'caaguazu' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<p class="sub"><?php esc_html_e( '¿Conocés una institución, un lugar, un servicio, un proyecto o un evento que todavía no está en el portal? Contanos y lo revisamos.', 'caaguazu' ); ?></p>
		</div>
	</section>

	<div class="container page-content">
		<?php echo caaguazu_proposal_form_html(); ?>
	</div>

	<?php
endwhile;

get_footer();
