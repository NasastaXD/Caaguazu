<?php
/**
 * Archivo de categoría — reemplaza los archive-caaguazu_{news,event,
 * educacion}.php de cuando Noticias/Agenda/Educación eran custom post
 * types propios. Ahora son Entradas nativas diferenciadas por Categoría,
 * así que este único template cubre los tres (y sus sub-categorías):
 * `caaguazu_category_family()` (inc/helpers.php) dice de cuál familia es
 * la categoría actual (o null si es una categoría ajena a este sistema,
 * creada a mano para otra cosa), y esa familia decide la copia del hero y
 * qué meta mostrar en cada tarjeta.
 *
 * @package Caaguazu
 */

get_header();

$term    = get_queried_object();
$family  = caaguazu_category_family( $term );
$today   = current_time( 'Y-m-d' );

$hero = array(
	'noticias'  => array( __( 'Noticias', 'caaguazu' ), __( 'Actualidad de Caaguazú', 'caaguazu' ), __( 'Comunicados, publicaciones y cobertura de los hechos que dan forma al departamento.', 'caaguazu' ) ),
	'agenda'    => array( __( 'Agenda', 'caaguazu' ), __( 'Eventos y calendario cultural', 'caaguazu' ), __( 'Festividades, ferias y actividades del departamento.', 'caaguazu' ) ),
	'educacion' => array( __( 'Educación', 'caaguazu' ), __( 'Escuelas, becas y programas educativos', 'caaguazu' ), __( 'Información sobre instituciones, becas municipales y programas educativos del departamento.', 'caaguazu' ) ),
);
list( $eyebrow, $title, $sub ) = isset( $hero[ $family ] ) ? $hero[ $family ] : array( __( 'Categoría', 'caaguazu' ), single_cat_title( '', false ), '' );

// Una sub-categoría (p. ej. "Cultura") usa su propio nombre como título,
// no el genérico de la familia — el eyebrow ya deja claro de qué familia es.
if ( $family && $term->slug !== $family ) {
	$title = $term->name;
}
?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<?php if ( $family && $term->slug !== $family ) : ?>
			<li>›</li>
			<li><a href="<?php echo esc_url( caaguazu_category_url( $family ) ); ?>"><?php echo esc_html( $eyebrow ); ?></a></li>
		<?php endif; ?>
		<li>›</li>
		<li aria-current="page"><?php echo esc_html( $title ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $sub ) : ?><p class="sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="news-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php if ( 'agenda' === $family ) :
					$date     = get_post_meta( get_the_ID(), '_caaguazu_event_date', true );
					$location = get_post_meta( get_the_ID(), '_caaguazu_event_location', true );
					$is_past  = $date && $date < $today;
				?>
					<article class="news event-card<?php echo $is_past ? ' event-past' : ''; ?>">
						<?php if ( $date ) : ?>
							<span class="event-datebox" aria-hidden="true">
								<strong><?php echo esc_html( date_i18n( 'j', strtotime( $date ) ) ); ?></strong>
								<span><?php echo esc_html( date_i18n( 'M', strtotime( $date ) ) ); ?></span>
							</span>
						<?php endif; ?>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
						<?php endif; ?>
						<div class="body">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="meta">
								<?php if ( $date ) : ?><?php echo esc_html( date_i18n( 'j \d\e F, Y', strtotime( $date ) ) ); ?><?php endif; ?>
								<?php if ( $location ) : ?> · <?php echo caaguazu_icon( 'pin' ); ?> <?php echo esc_html( $location ); ?><?php endif; ?>
							</p>
							<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver evento', 'caaguazu' ); ?></a>
						</div>
					</article>
				<?php elseif ( 'educacion' === $family ) :
					$tipo = caaguazu_educacion_primary_term( get_the_ID() );
					$stat = get_post_meta( get_the_ID(), '_caaguazu_edu_stat', true );
				?>
					<article class="news">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
						<?php endif; ?>
						<div class="body">
							<?php if ( $tipo ) : ?><span class="cat"><?php echo esc_html( $tipo ); ?></span><?php endif; ?>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="meta">
								<?php echo esc_html( get_the_date() ); ?>
								<?php if ( $stat ) : ?> · <strong><?php echo esc_html( $stat ); ?></strong><?php endif; ?>
							</p>
							<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
							<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'caaguazu' ); ?></a>
						</div>
					</article>
				<?php else :
					// Noticias, y también el fallback genérico para una
					// categoría ajena a este sistema (creada a mano).
					$cat  = 'noticias' === $family ? caaguazu_news_primary_term( get_the_ID() ) : '';
					$mins = 'noticias' === $family ? (int) get_post_meta( get_the_ID(), '_caaguazu_read_minutes', true ) : 0;
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
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<div class="wip">
			<p class="eyebrow"><?php esc_html_e( 'Sin contenido', 'caaguazu' ); ?></p>
			<p><?php esc_html_e( 'Todavía no se publicó contenido en esta categoría.', 'caaguazu' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer();
