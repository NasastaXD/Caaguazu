<?php
/**
 * Perfil de un local turístico (CPT `cgz_local` del plugin Caaguazú
 * Locales). El plugin busca este archivo vía locate_template() y lo usa
 * en vez de su propio fallback — así el perfil se ve con la identidad
 * visual del theme en vez de con clases utilitarias del theme de turismo
 * original, que acá no existen.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();

	$id        = get_the_ID();
	$tipo      = get_post_meta( $id, '_cgz_tipo', true );
	$types     = function_exists( 'cgz_local_types' ) ? cgz_local_types() : array();
	$direccion = get_post_meta( $id, '_cgz_direccion', true );
	$horario   = get_post_meta( $id, '_cgz_horario', true );
	$telefono  = get_post_meta( $id, '_cgz_telefono', true );
	$eyebrow   = isset( $types[ $tipo ] ) ? $types[ $tipo ] : __( 'Local turístico', 'caaguazu' );
	?>

	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'cgz_local' ) ); ?>"><?php esc_html_e( 'Locales', 'caaguazu' ); ?></a></li>
			<li>›</li>
			<li aria-current="page"><?php the_title(); ?></li>
		</ol>
	</nav>

	<section class="container page-hero">
		<div class="grid">
			<div>
				<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php if ( $direccion ) : ?><p class="sub">📍 <?php echo esc_html( $direccion ); ?></p><?php endif; ?>
			</div>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="img"><?php the_post_thumbnail( 'caaguazu-hero', array( 'loading' => 'eager' ) ); ?></div>
			<?php endif; ?>
		</div>
	</section>

	<div class="container page-content">
		<div class="contact-grid">
			<div>
				<div class="entry-content" style="margin-bottom:0">
					<?php the_content(); ?>
				</div>
				<?php echo do_shortcode( '[caaguazu_resenas id="' . (int) $id . '"]' ); ?>
			</div>
			<div>
				<?php
				$booking = class_exists( 'CGZ_Booking' ) ? CGZ_Booking::render_widget( $id ) : '';
				if ( $booking ) {
					echo $booking; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
				<?php if ( $direccion || $horario || $telefono ) : ?>
					<div class="aud" style="margin-top:24px">
						<h3><?php esc_html_e( 'Información', 'caaguazu' ); ?></h3>
						<ul style="margin-top:16px;display:flex;flex-direction:column;gap:8px;font-size:14px;color:var(--text-soft)">
							<?php if ( $direccion ) : ?><li><strong><?php esc_html_e( 'Dirección:', 'caaguazu' ); ?></strong> <?php echo esc_html( $direccion ); ?></li><?php endif; ?>
							<?php if ( $horario ) : ?><li><strong><?php esc_html_e( 'Horario:', 'caaguazu' ); ?></strong> <?php echo esc_html( $horario ); ?></li><?php endif; ?>
							<?php if ( $telefono ) : ?><li><strong><?php esc_html_e( 'Teléfono:', 'caaguazu' ); ?></strong> <?php echo esc_html( $telefono ); ?></li><?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
endwhile;

get_footer();
