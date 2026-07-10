<?php
/**
 * Ficha individual de un Servicio (V5, civic CMS).
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$categoria   = caaguazu_primary_term_name( get_the_ID(), 'categoria_servicio' );
	$institucion = get_post_meta( get_the_ID(), '_caaguazu_serv_institucion', true );
	$requisitos  = get_post_meta( get_the_ID(), '_caaguazu_serv_requisitos', true );
	$horario     = get_post_meta( get_the_ID(), '_caaguazu_serv_horario', true );
	$contacto    = get_post_meta( get_the_ID(), '_caaguazu_serv_contacto', true );
	$enlace      = get_post_meta( get_the_ID(), '_caaguazu_serv_enlace', true );
	$estado      = get_post_meta( get_the_ID(), '_caaguazu_serv_estado', true );
	$estados     = caaguazu_servicio_estado_values();
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'servicio' ) ); ?>"><?php esc_html_e( 'Servicios', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<article class="container page-hero">
		<div>
			<?php if ( $categoria ) : ?><p class="eyebrow"><?php echo esc_html( $categoria ); ?></p><?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $institucion ) : ?><p class="sub"><?php echo esc_html( $institucion ); ?></p><?php endif; ?>
			<?php if ( $estado && isset( $estados[ $estado ] ) ) : ?>
				<span class="trust-badge trust-badge--<?php echo esc_attr( $estado ); ?>"><?php echo esc_html( $estados[ $estado ] ); ?></span>
			<?php endif; ?>
		</div>
	</article>

	<div class="container page-content">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<div class="entity-meta">
			<?php
			echo caaguazu_render_entity_meta( array(
				__( 'Requisitos', 'caaguazu' )         => $requisitos,
				__( 'Horario de atención', 'caaguazu' ) => $horario,
				__( 'Contacto', 'caaguazu' )            => $contacto,
			) );
			if ( $enlace ) :
				?>
				<a class="arrow" href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Enlace oficial', 'caaguazu' ); ?></a>
			<?php endif; ?>
		</div>
		<?php echo caaguazu_render_trust_meta( get_the_ID() ); ?>
	</div>

	<?php
endwhile;

get_footer();
