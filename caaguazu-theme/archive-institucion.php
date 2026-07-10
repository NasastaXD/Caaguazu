<?php
/**
 * Archivo del directorio de Instituciones (V5, civic CMS). Reusa el patrón
 * "directorio funcional" que ya vivía en archive-caaguazu_artisan.php
 * (`.aud`/`.aud-grid`, sin imagen grande) — mismo criterio de la Fase 11 del
 * pase de pulido: contenido de utilidad, no editorial, no necesita foto.
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Instituciones', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Directorio', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Instituciones', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Escuelas, oficinas municipales, centros culturales y organizaciones que forman parte de Caaguazú.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="aud-grid">
			<?php while ( have_posts() ) : the_post();
				$tipo      = caaguazu_primary_term_name( get_the_ID(), 'tipo_institucion' );
				$direccion = get_post_meta( get_the_ID(), '_caaguazu_inst_direccion', true );
				$telefono  = get_post_meta( get_the_ID(), '_caaguazu_inst_telefono', true );
			?>
				<a class="aud" href="<?php the_permalink(); ?>">
					<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( 'building' ); ?></span>
					<h3><?php the_title(); ?></h3>
					<?php if ( $tipo ) : ?><p class="desc"><?php echo esc_html( $tipo ); ?></p><?php endif; ?>
					<?php if ( $direccion || $telefono ) : ?>
						<p class="aud-meta">
							<?php echo esc_html( $direccion ); ?>
							<?php if ( $direccion && $telefono ) : ?> · <?php endif; ?>
							<?php echo esc_html( $telefono ); ?>
						</p>
					<?php endif; ?>
					<span class="arrow"><?php esc_html_e( 'Ver institución', 'caaguazu' ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else :
		echo caaguazu_render_empty_state(
			__( 'Sin instituciones todavía', 'caaguazu' ),
			__( 'Todavía no hay instituciones registradas.', 'caaguazu' ),
			__( 'Esta sección reunirá información verificada de instituciones locales.', 'caaguazu' ),
			array( 'label' => __( 'Proponer institución', 'caaguazu' ), 'url' => caaguazu_page_url( 'contacto' ) )
		);
	endif; ?>
</div>

<?php get_footer();
