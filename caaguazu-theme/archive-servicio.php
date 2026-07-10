<?php
/**
 * Archivo de Servicios (V5, civic CMS). Mismo patrón funcional que
 * Instituciones (`.aud`/`.aud-grid`, sin imagen grande).
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Servicios', 'caaguazu' ); ?></li>
	</ol>
</nav>

<section class="container page-hero">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Para la ciudadanía', 'caaguazu' ); ?></p>
		<h1><?php esc_html_e( 'Servicios', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'Trámites, guías prácticas e información útil sobre servicios disponibles en el departamento.', 'caaguazu' ); ?></p>
	</div>
</section>

<div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="aud-grid">
			<?php while ( have_posts() ) : the_post();
				$categoria   = caaguazu_primary_term_name( get_the_ID(), 'categoria_servicio' );
				$institucion = get_post_meta( get_the_ID(), '_caaguazu_serv_institucion', true );
				$estado      = get_post_meta( get_the_ID(), '_caaguazu_serv_estado', true );
				$estados     = function_exists( 'caaguazu_servicio_estado_values' ) ? caaguazu_servicio_estado_values() : array();
			?>
				<a class="aud" href="<?php the_permalink(); ?>">
					<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( 'tool' ); ?></span>
					<h3><?php the_title(); ?></h3>
					<?php if ( $categoria ) : ?><p class="desc"><?php echo esc_html( $categoria ); ?></p><?php endif; ?>
					<?php if ( $institucion ) : ?><p class="aud-meta"><?php echo esc_html( $institucion ); ?></p><?php endif; ?>
					<?php if ( $estado && isset( $estados[ $estado ] ) ) : ?>
						<span class="trust-badge aud-status trust-badge--<?php echo esc_attr( $estado ); ?>"><?php echo esc_html( $estados[ $estado ] ); ?></span>
					<?php endif; ?>
					<span class="arrow"><?php esc_html_e( 'Ver servicio', 'caaguazu' ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else :
		echo caaguazu_render_empty_state(
			__( 'Sin servicios todavía', 'caaguazu' ),
			__( 'Todavía no hay servicios cargados.', 'caaguazu' ),
			__( 'Esta sección reunirá información útil y verificable para la ciudadanía.', 'caaguazu' ),
			array( 'label' => __( 'Contactar', 'caaguazu' ), 'url' => caaguazu_page_url( 'contacto' ) )
		);
	endif; ?>
</div>

<?php get_footer();
