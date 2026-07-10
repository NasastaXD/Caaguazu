<?php
/**
 * Single de Entrada — reemplaza los single-caaguazu_{news,event,
 * educacion}.php de cuando Noticias/Agenda/Educación eran custom post
 * types propios. Ahora son Entradas nativas diferenciadas por Categoría:
 * `caaguazu_post_category_family()` (inc/helpers.php) dice de cuál
 * familia es esta entrada (o null si es una Entrada común, sin ninguna de
 * esas categorías), y esa familia decide qué meta mostrar en el hero.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$family = caaguazu_post_category_family( get_the_ID() );

	$archive_label = array(
		'noticias'  => __( 'Noticias', 'caaguazu' ),
		'agenda'    => __( 'Agenda', 'caaguazu' ),
		'educacion' => __( 'Educación', 'caaguazu' ),
	);
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<?php if ( $family ) : ?>
				<li>›</li>
				<li><a href="<?php echo esc_url( caaguazu_category_url( $family ) ); ?>"><?php echo esc_html( $archive_label[ $family ] ); ?></a></li>
			<?php endif; ?>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<article class="container page-hero">
		<div>
			<?php if ( 'agenda' === $family ) :
				$date     = get_post_meta( get_the_ID(), '_caaguazu_event_date', true );
				$location = get_post_meta( get_the_ID(), '_caaguazu_event_location', true );
			?>
				<p class="eyebrow"><?php esc_html_e( 'Evento', 'caaguazu' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<p class="sub">
					<?php if ( $date ) : ?><?php echo esc_html( caaguazu_fecha_es( $date ) ); ?><?php endif; ?>
					<?php if ( $location ) : ?> · <?php echo caaguazu_icon( 'pin' ); ?> <?php echo esc_html( $location ); ?><?php endif; ?>
				</p>
			<?php elseif ( 'educacion' === $family ) :
				$tipo = caaguazu_educacion_primary_term( get_the_ID() );
				$stat = get_post_meta( get_the_ID(), '_caaguazu_edu_stat', true );
			?>
				<?php if ( $tipo ) : ?><p class="eyebrow"><?php echo esc_html( $tipo ); ?></p><?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<p class="sub">
					<?php echo esc_html( get_the_date() ); ?>
					<?php if ( $stat ) : ?> · <strong><?php echo esc_html( $stat ); ?></strong><?php endif; ?>
				</p>
			<?php else :
				// Noticias, y también el fallback genérico para una Entrada
				// común (sin ninguna de las tres categorías del sistema).
				$cat  = 'noticias' === $family ? caaguazu_news_primary_term( get_the_ID() ) : '';
				$mins = 'noticias' === $family ? (int) get_post_meta( get_the_ID(), '_caaguazu_read_minutes', true ) : 0;
			?>
				<?php if ( $cat ) : ?><p class="eyebrow"><?php echo esc_html( $cat ); ?></p><?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<p class="sub">
					<?php echo esc_html( get_the_date() ); ?>
					<?php if ( $mins ) : ?> · <?php printf( esc_html__( '%d min de lectura', 'caaguazu' ), $mins ); ?><?php endif; ?>
				</p>
			<?php endif; ?>
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
