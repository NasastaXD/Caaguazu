<?php
/**
 * Perfil de artesano.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$craft = get_post_meta( get_the_ID(), '_caaguazu_artisan_craft', true );
	$loc   = get_post_meta( get_the_ID(), '_caaguazu_artisan_location', true );
	$quote = get_post_meta( get_the_ID(), '_caaguazu_artisan_quote', true );
?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li><a href="<?php echo esc_url( get_post_type_archive_link( 'caaguazu_artisan' ) ); ?>"><?php esc_html_e( 'Artesanos', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php the_title(); ?></li>
	</ol>
</nav>

<article class="container page-hero">
	<div>
		<?php if ( $craft ) : ?><p class="eyebrow"><?php echo esc_html( $craft ); ?></p><?php endif; ?>
		<h1><?php the_title(); ?></h1>
		<?php if ( $loc ) : ?><p class="sub">📍 <?php echo esc_html( $loc ); ?></p><?php endif; ?>
	</div>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="img"><?php the_post_thumbnail( 'caaguazu-square', array( 'loading' => 'eager' ) ); ?></div>
	<?php endif; ?>
</article>

<div class="container page-content">
	<div class="entry-content">
		<?php if ( $quote ) : ?><blockquote>«<?php echo esc_html( $quote ); ?>»</blockquote><?php endif; ?>
		<?php the_content(); ?>
	</div>
</div>

<?php
endwhile;

get_footer();
