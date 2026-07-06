<?php
/**
 * Template Name: Reportá un problema
 *
 * Formulario público de reportes ciudadanos (311).
 *
 * @package Caaguazu
 */

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
		<div class="grid">
			<div>
				<p class="eyebrow"><?php caaguazu_i18n( 'report.hero.eyebrow', __( 'Atención ciudadana', 'caaguazu' ) ); ?></p>
				<h1><?php the_title(); ?></h1>
				<p class="sub"><?php caaguazu_i18n( 'report.hero.sub', __( 'Bache, alumbrado, basura u otro problema en el espacio público. Contanos qué está pasando y dónde.', 'caaguazu' ) ); ?></p>
			</div>
		</div>
	</section>

	<div class="container page-content">
		<?php echo caaguazu_report_form_html(); ?>
	</div>

	<?php
endwhile;

get_footer();
