<?php
/**
 * Template Name: Reportá un problema
 *
 * Formulario público de reportes ciudadanos (311).
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
		<div class="grid">
			<div>
				<p class="eyebrow"><?php caaguazu_i18n( 'report.hero.eyebrow', __( 'Atención ciudadana', 'caaguazu' ) ); ?></p>
				<h1><?php the_title(); ?></h1>
				<p class="sub"><?php caaguazu_i18n( 'report.hero.sub', __( 'Bache, alumbrado, basura u otro problema en el espacio público. Contanos qué está pasando y dónde.', 'caaguazu' ) ); ?></p>
			</div>
		</div>
	</section>

	<?php $stats = caaguazu_report_stats(); ?>
	<?php if ( $stats['received'] > 0 ) : ?>
		<section class="container">
			<div class="stats-grid stats-grid--compact">
				<div class="stat">
					<span class="stat-num"><?php echo esc_html( number_format_i18n( $stats['received'] ) ); ?></span>
					<span class="stat-label"><?php caaguazu_i18n( 'report.stats.received', __( 'reportes recibidos', 'caaguazu' ) ); ?></span>
				</div>
				<div class="stat">
					<span class="stat-num"><?php echo esc_html( number_format_i18n( $stats['resolved'] ) ); ?></span>
					<span class="stat-label"><?php caaguazu_i18n( 'report.stats.resolved', __( 'atendidos', 'caaguazu' ) ); ?></span>
				</div>
				<div class="stat">
					<span class="stat-num"><?php echo esc_html( number_format_i18n( $stats['this_month'] ) ); ?></span>
					<span class="stat-label"><?php caaguazu_i18n( 'report.stats.month', __( 'este mes', 'caaguazu' ) ); ?></span>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="container page-content">
		<?php echo caaguazu_report_form_html(); ?>
	</div>

	<?php
endwhile;

get_footer();
