<?php
/**
 * Archivo de Lugares (V5, civic CMS). Reusa el patrón editorial de
 * Noticias (`.news`/`.news-grid`, con imagen) — a diferencia de
 * Instituciones/Servicios, acá la imagen sí aporta (identidad/turismo, ver
 * Fase 11 del pase de pulido: "cultura/turismo: editorial, imágenes
 * permitidas"). No reemplaza las páginas curadas de Turismo
 * (`caaguazu-turismo`): es un directorio abierto que cualquiera puede ir
 * sumando de a un lugar, sin tocar esa jerarquía fija.
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Lugares', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Descubrí Caaguazú', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Lugares', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Plazas, miradores, sitios históricos y rincones para conocer en el departamento.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="news-grid">
			<?php while ( have_posts() ) : the_post();
				$tipo       = caaguazu_primary_term_name( get_the_ID(), 'tipo_lugar' );
				$referencia = get_post_meta( get_the_ID(), '_caaguazu_lugar_referencia', true );
			?>
				<article class="news">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<?php if ( $tipo ) : ?><span class="cat"><?php echo esc_html( $tipo ); ?></span><?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $referencia ) : ?><p class="meta"><?php echo esc_html( $referencia ); ?></p><?php endif; ?>
						<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Conocer lugar', 'caaguazu' ); ?></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else :
		echo caaguazu_render_empty_state(
			__( 'Contenido en preparación', 'caaguazu' ),
			__( 'Contenido en preparación.', 'caaguazu' ),
			__( 'Pronto se incorporarán lugares, historias y referencias locales revisadas.', 'caaguazu' ),
			array( 'label' => __( 'Proponer un lugar', 'caaguazu' ), 'url' => caaguazu_proposal_url( 'lugar' ) )
		);
	endif; ?>
</div>

<?php get_footer();
