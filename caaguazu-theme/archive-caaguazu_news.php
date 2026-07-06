<?php
/**
 * Archivo de noticias.
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Noticias', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Noticias', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Actualidad de Caaguazú', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Comunicados, publicaciones y cobertura de los hechos que dan forma al departamento.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="news-grid">
			<?php while ( have_posts() ) : the_post();
				$cat   = caaguazu_news_primary_term( get_the_ID() );
				$mins  = (int) get_post_meta( get_the_ID(), '_caaguazu_read_minutes', true );
			?>
				<article class="news">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<?php if ( $cat ) : ?><span class="cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="meta">
							<?php echo esc_html( get_the_date() ); ?>
							<?php if ( $mins ) : ?> · <?php printf( esc_html__( '%d min de lectura', 'caaguazu' ), $mins ); ?><?php endif; ?>
						</p>
						<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
						<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'caaguazu' ); ?></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<div class="wip">
			<p class="eyebrow"><?php esc_html_e( 'Sin noticias', 'caaguazu' ); ?></p>
			<p><?php esc_html_e( 'Todavía no se publicaron noticias.', 'caaguazu' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer();
