<?php
/**
 * Archivo de Proyectos (V5, civic CMS). Reusa la tarjeta modular del hub de
 * Ecosistema (`.eco-card`/`.eco-grid`) con un badge de estado — Fase 11 del
 * pase de pulido: "proyectos: modular, orientado a ecosistema, con status
 * badges". No reemplaza el hub de Ecosistema (sub-portales completos como
 * Turismo/Educación/CEAD): Proyectos son iniciativas puntuales más chicas.
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Proyectos', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Ecosistema digital', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Proyectos', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Iniciativas digitales, comunitarias y educativas en preparación o en marcha, vinculadas a la ciudad.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="eco-grid">
			<?php while ( have_posts() ) : the_post();
				$area    = caaguazu_primary_term_name( get_the_ID(), 'area_proyecto' );
				$estado  = get_post_meta( get_the_ID(), '_caaguazu_proy_estado', true );
				$estados = caaguazu_proyecto_estado_values();
				$tag     = array_filter( array( $area, isset( $estados[ $estado ] ) ? $estados[ $estado ] : '' ) );
			?>
				<a class="eco-card" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<?php if ( $tag ) : ?><span class="eco-tag"><?php echo esc_html( implode( ' · ', $tag ) ); ?></span><?php endif; ?>
						<h3><?php the_title(); ?></h3>
						<p class="desc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<span class="arrow"><?php esc_html_e( 'Ver proyecto', 'caaguazu' ); ?></span>
					</div>
				</a>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else :
		echo caaguazu_render_empty_state(
			__( 'En preparación', 'caaguazu' ),
			__( 'Los proyectos digitales se irán incorporando progresivamente.', 'caaguazu' ),
			__( 'Esta sección reunirá iniciativas reales vinculadas a la ciudad.', 'caaguazu' ),
			array( 'label' => __( 'Proponer proyecto', 'caaguazu' ), 'url' => caaguazu_proposal_url( 'proyecto' ) )
		);
	endif; ?>
</div>

<?php get_footer();
