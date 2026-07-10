<?php
/**
 * Directorio de artesanos — "los rostros de la madera".
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Artesanos', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Los rostros de la madera', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Directorio de artesanos', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Carpinteros, talladores, parqueteros y jugueteros que sostienen el oficio maderero de Caaguazú.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="aud-grid">
			<?php while ( have_posts() ) : the_post();
				$craft = get_post_meta( get_the_ID(), '_caaguazu_artisan_craft', true );
				$loc   = get_post_meta( get_the_ID(), '_caaguazu_artisan_location', true );
			?>
				<a class="aud artisan-card" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-square', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<h3><?php the_title(); ?></h3>
					<?php if ( $craft ) : ?><p class="desc"><?php echo esc_html( $craft ); ?><?php echo $loc ? ' · ' . esc_html( $loc ) : ''; ?></p><?php endif; ?>
					<span class="arrow"><?php esc_html_e( 'Ver perfil', 'caaguazu' ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else :
		echo caaguazu_render_empty_state(
			__( 'Sin perfiles', 'caaguazu' ),
			__( 'Todavía no hay artesanos cargados en el directorio.', 'caaguazu' )
		);
	endif; ?>
</div>

<?php get_footer();
