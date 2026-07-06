<?php
/**
 * Archivo de eventos / agenda cultural.
 *
 * @package Caaguazu
 */

get_header();

$today = current_time( 'Y-m-d' );
?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Agenda', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Agenda', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Eventos y calendario cultural', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Festividades, ferias y actividades del departamento.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="news-grid">
			<?php while ( have_posts() ) : the_post();
				$date     = get_post_meta( get_the_ID(), '_caaguazu_event_date', true );
				$location = get_post_meta( get_the_ID(), '_caaguazu_event_location', true );
				$is_past  = $date && $date < $today;
			?>
				<article class="news event-card<?php echo $is_past ? ' event-past' : ''; ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<?php if ( $date ) : ?>
							<span class="cat"><?php echo esc_html( date_i18n( 'j \d\e F, Y', strtotime( $date ) ) ); ?></span>
						<?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $location ) : ?><p class="meta">📍 <?php echo esc_html( $location ); ?></p><?php endif; ?>
						<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver evento', 'caaguazu' ); ?></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<div class="wip">
			<p class="eyebrow"><?php esc_html_e( 'Sin eventos', 'caaguazu' ); ?></p>
			<p><?php esc_html_e( 'Todavía no hay eventos cargados en la agenda.', 'caaguazu' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer();
