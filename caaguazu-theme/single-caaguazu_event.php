<?php
/**
 * Single de evento.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$date     = get_post_meta( get_the_ID(), '_caaguazu_event_date', true );
	$location = get_post_meta( get_the_ID(), '_caaguazu_event_location', true );
?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li><a href="<?php echo esc_url( get_post_type_archive_link( 'caaguazu_event' ) ); ?>"><?php esc_html_e( 'Agenda', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php the_title(); ?></li>
	</ol>
</nav>

<article class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Evento', 'caaguazu' ); ?></p>
		<h1><?php the_title(); ?></h1>
		<p class="sub">
			<?php if ( $date ) : ?><?php echo esc_html( date_i18n( 'j \d\e F, Y', strtotime( $date ) ) ); ?><?php endif; ?>
			<?php if ( $location ) : ?> · 📍 <?php echo esc_html( $location ); ?><?php endif; ?>
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
