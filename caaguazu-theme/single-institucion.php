<?php
/**
 * Ficha individual de una Institución (V5, civic CMS).
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$tipo      = caaguazu_primary_term_name( get_the_ID(), 'tipo_institucion' );
	$direccion = get_post_meta( get_the_ID(), '_caaguazu_inst_direccion', true );
	$telefono  = get_post_meta( get_the_ID(), '_caaguazu_inst_telefono', true );
	$horario   = get_post_meta( get_the_ID(), '_caaguazu_inst_horario', true );
	$email     = get_post_meta( get_the_ID(), '_caaguazu_inst_email', true );
	$web       = get_post_meta( get_the_ID(), '_caaguazu_inst_sitio_web', true );
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'institucion' ) ); ?>"><?php esc_html_e( 'Instituciones', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<article class="container page-hero">
		<div>
			<?php if ( $tipo ) : ?><p class="eyebrow"><?php echo esc_html( $tipo ); ?></p><?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $direccion ) : ?><p class="sub"><?php echo esc_html( $direccion ); ?></p><?php endif; ?>
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
				__( 'Dirección', 'caaguazu' ) => $direccion,
				__( 'Teléfono', 'caaguazu' )  => $telefono,
				__( 'Horario', 'caaguazu' )   => $horario,
				__( 'Correo', 'caaguazu' )    => $email,
			) );
			if ( $web ) :
				?>
				<a class="arrow" href="<?php echo esc_url( $web ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Sitio web', 'caaguazu' ); ?></a>
			<?php endif; ?>
		</div>
		<?php echo caaguazu_render_trust_meta( get_the_ID() ); ?>
	</div>

	<?php
endwhile;

get_footer();
