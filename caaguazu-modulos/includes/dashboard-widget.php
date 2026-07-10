<?php
/**
 * Widget del Escritorio "caaguazu.net editorial": atajos para crear
 * contenido de cada familia cívica + recordatorio de la única regla que
 * importa (nada de contenido inventado). Parte de V5 (civic CMS) — un
 * admin dashboard simple, no un panel custom aparte (ver README, sección
 * "Editor UX / dashboard").
 *
 * @package Caaguazu_Modulos
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_modulos_register_dashboard_widget() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'caaguazu_modulos_editorial',
		__( 'caaguazu.net editorial', 'caaguazu-modulos' ),
		'caaguazu_modulos_render_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'caaguazu_modulos_register_dashboard_widget' );

function caaguazu_modulos_render_dashboard_widget() {
	$quick_links = array(
		array( 'label' => __( 'Nueva noticia / evento / entrada de Educación', 'caaguazu-modulos' ), 'url' => admin_url( 'post-new.php' ) ),
		array( 'label' => __( 'Nueva institución', 'caaguazu-modulos' ), 'url' => admin_url( 'post-new.php?post_type=institucion' ) ),
		array( 'label' => __( 'Nuevo lugar', 'caaguazu-modulos' ), 'url' => admin_url( 'post-new.php?post_type=lugar' ) ),
		array( 'label' => __( 'Nuevo servicio', 'caaguazu-modulos' ), 'url' => admin_url( 'post-new.php?post_type=servicio' ) ),
		array( 'label' => __( 'Nuevo proyecto', 'caaguazu-modulos' ), 'url' => admin_url( 'post-new.php?post_type=proyecto' ) ),
	);
	?>
	<p><?php esc_html_e( 'Accesos rápidos para cargar contenido real del portal:', 'caaguazu-modulos' ); ?></p>
	<ul style="margin:0 0 12px;list-style:disc;padding-left:20px">
		<?php foreach ( $quick_links as $link ) : ?>
			<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<p>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_status=draft' ) ); ?>"><?php esc_html_e( 'Ver borradores de Entradas', 'caaguazu-modulos' ); ?></a>
		— <?php esc_html_e( 'Instituciones/Lugares/Servicios/Proyectos tienen su propio listado de borradores bajo su menú lateral.', 'caaguazu-modulos' ); ?>
	</p>
	<p style="padding:10px;background:#f6f7f7;border-left:3px solid #145A3A;margin:0">
		<strong><?php esc_html_e( 'Antes de publicar:', 'caaguazu-modulos' ); ?></strong>
		<?php esc_html_e( 'No publiques contenido sin fuente, revisión o redacción humana.', 'caaguazu-modulos' ); ?>
	</p>
	<?php
}
