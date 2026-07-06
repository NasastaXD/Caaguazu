<?php
/**
 * Plantilla de fallback. WordPress la usa cuando no hay otra más específica.
 *
 * @package Caaguazu
 */

get_header(); ?>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Caaguazú', 'caaguazu' ); ?></p>
		<h1><?php
			if ( is_home() ) {
				single_post_title();
			} else {
				the_archive_title();
			}
		?></h1>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="news-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="news">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="meta"><?php echo esc_html( get_the_date() ); ?></p>
						<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
						<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'caaguazu' ); ?></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No hay contenido para mostrar todavía.', 'caaguazu' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer();
