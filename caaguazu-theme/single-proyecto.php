<?php
/**
 * Ficha individual de un Proyecto (V5, civic CMS).
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$area         = caaguazu_primary_term_name( get_the_ID(), 'area_proyecto' );
	$responsable  = get_post_meta( get_the_ID(), '_caaguazu_proy_responsable', true );
	$fecha_inicio = get_post_meta( get_the_ID(), '_caaguazu_proy_fecha_inicio', true );
	$enlace       = get_post_meta( get_the_ID(), '_caaguazu_proy_enlace', true );
	$estado       = get_post_meta( get_the_ID(), '_caaguazu_proy_estado', true );
	$estados      = caaguazu_proyecto_estado_values();
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'proyecto' ) ); ?>"><?php esc_html_e( 'Proyectos', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<article class="container page-hero">
		<div>
			<?php if ( $area ) : ?><p class="eyebrow"><?php echo esc_html( $area ); ?></p><?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $estado && isset( $estados[ $estado ] ) ) : ?>
				<p class="sub"><span class="trust-badge trust-badge--<?php echo esc_attr( $estado ); ?>"><?php echo esc_html( $estados[ $estado ] ); ?></span></p>
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
		<div class="entity-meta">
			<?php
			echo caaguazu_render_entity_meta( array(
				__( 'Responsable', 'caaguazu' )    => $responsable,
				__( 'Fecha de inicio', 'caaguazu' ) => $fecha_inicio ? caaguazu_fecha_es( $fecha_inicio ) : '',
			) );
			if ( $enlace ) :
				?>
				<a class="arrow" href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Ver más', 'caaguazu' ); ?></a>
			<?php endif; ?>
		</div>
		<?php echo caaguazu_render_trust_meta( get_the_ID() ); ?>
	</div>

	<?php
endwhile;

get_footer();
