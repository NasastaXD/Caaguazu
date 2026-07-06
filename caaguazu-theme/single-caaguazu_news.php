<?php
/**
 * Single de noticia.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$cat  = caaguazu_news_primary_term( get_the_ID() );
	$mins = (int) get_post_meta( get_the_ID(), '_caaguazu_read_minutes', true );
?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li><a href="<?php echo esc_url( get_post_type_archive_link( 'caaguazu_news' ) ); ?>"><?php esc_html_e( 'Noticias', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php the_title(); ?></li>
	</ol>
</nav>

<article class="container page-hero">
	<div>
		<?php if ( $cat ) : ?><p class="eyebrow"><?php echo esc_html( $cat ); ?></p><?php endif; ?>
		<h1><?php the_title(); ?></h1>
		<p class="sub">
			<?php echo esc_html( get_the_date() ); ?>
			<?php if ( $mins ) : ?> · <?php printf( esc_html__( '%d min de lectura', 'caaguazu' ), $mins ); ?><?php endif; ?>
		</p>
	</div>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="img"><?php the_post_thumbnail( 'caaguazu-hero', array( 'loading' => 'eager' ) ); ?></div>
	<?php endif; ?>
</article>

<div class="container page-content">
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
	<?php echo caaguazu_share_buttons( get_permalink(), get_the_title() ); ?>
</div>

<?php
endwhile;

get_footer();
