<?php
/**
 * Ficha individual de un Lugar (V5, civic CMS).
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	$tipo        = caaguazu_primary_term_name( get_the_ID(), 'tipo_lugar' );
	$direccion   = get_post_meta( get_the_ID(), '_caaguazu_lugar_direccion', true );
	$horario     = get_post_meta( get_the_ID(), '_caaguazu_lugar_horario', true );
	$contacto    = get_post_meta( get_the_ID(), '_caaguazu_lugar_contacto', true );
	$referencia  = get_post_meta( get_the_ID(), '_caaguazu_lugar_referencia', true );
	$mapa_url    = get_post_meta( get_the_ID(), '_caaguazu_lugar_mapa_url', true );
	$experiencia = get_post_meta( get_the_ID(), '_caaguazu_lugar_experiencia', true );
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'lugar' ) ); ?>"><?php esc_html_e( 'Lugares', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<article class="container page-hero">
		<div>
			<?php if ( $tipo ) : ?><p class="eyebrow"><?php echo esc_html( $tipo ); ?></p><?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $referencia ) : ?><p class="sub"><?php echo esc_html( $referencia ); ?></p><?php endif; ?>
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
				__( 'Dirección', 'caaguazu' )   => $direccion,
				__( 'Horario', 'caaguazu' )     => $horario,
				__( 'Contacto', 'caaguazu' )    => $contacto,
				__( 'Experiencia', 'caaguazu' ) => $experiencia,
			) );
			if ( $mapa_url ) :
				?>
				<a class="arrow" href="<?php echo esc_url( $mapa_url ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Ver en el mapa', 'caaguazu' ); ?></a>
			<?php endif; ?>
		</div>
		<?php echo caaguazu_share_buttons( get_permalink(), get_the_title() ); ?>
		<?php echo caaguazu_render_trust_meta( get_the_ID() ); ?>
	</div>

	<?php
endwhile;

get_footer();
