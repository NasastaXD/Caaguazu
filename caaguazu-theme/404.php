<?php
/**
 * Plantilla 404.
 *
 * @package Caaguazu
 */

get_header(); ?>

<section class="container page-hero">
	<div>
		<p class="eyebrow">404</p>
		<h1><?php esc_html_e( 'Página no encontrada', 'caaguazu' ); ?></h1>
		<p class="sub"><?php esc_html_e( 'La URL solicitada no existe o fue movida. Probá buscando o volvé al inicio.', 'caaguazu' ); ?></p>
		<p style="margin-top:32px">
			<a class="btn btn-white" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'caaguazu' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Buscar', 'caaguazu' ); ?></a>
		</p>
	</div>
</section>

<?php get_footer();
