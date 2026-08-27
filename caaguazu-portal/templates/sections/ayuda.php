<?php
/** Ayuda / Acerca de: explica qué hace cada parte del portal. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$page_title = __( 'Ayuda', 'caaguazu-portal' );
$body = function () {
	// [ icono, título, descripción, cap-requerida (o '' para todos) ]
	$bloques = array(
		array( 'home',   __( 'Inicio', 'caaguazu-portal' ), __( 'Tu resumen del día: fichas que esperan revisión, contenido que necesita correcciones y accesos rápidos según tu rol.', 'caaguazu-portal' ), '' ),
		array( 'edit',   __( 'Nueva ficha', 'caaguazu-portal' ), __( 'El editor guiado para crear destinos. Completá los campos y el checklist; el sistema te avisa si falta algo antes de enviar la ficha a revisión.', 'caaguazu-portal' ), 'promotur_edit_destino' ),
		array( 'image',  __( 'Salida de campo', 'caaguazu-portal' ), __( 'Sacá fotos, anotá información y guardá la ubicación GPS mientras estás en el lugar, incluso sin señal. Después podés sincronizar todo como borrador cuando vuelva la conexión.', 'caaguazu-portal' ), 'promotur_create_draft' ),
		array( 'doc',    __( 'Mis contenidos', 'caaguazu-portal' ), __( 'Todas tus fichas, ordenadas por estado: borrador, enviada, en revisión, necesita cambios o publicada.', 'caaguazu-portal' ), 'promotur_create_draft' ),
		array( 'inbox',  __( 'Cola de revisión', 'caaguazu-portal' ), __( 'Para Promotores: revisá las fichas enviadas, asignate una, aprobala y publicala o devolvela con comentarios para que el autor haga los cambios necesarios.', 'caaguazu-portal' ), 'promotur_review_content' ),
		array( 'tasks',  __( 'Tareas', 'caaguazu-portal' ), __( 'Asignaciones con fecha límite y una lista de lo que todavía falta cubrir. Los Mini Promotores pueden reclamar los huecos disponibles.', 'caaguazu-portal' ), 'promotur_view_own_tasks' ),
		array( 'star',   __( 'Curaduría', 'caaguazu-portal' ), __( 'Elegí qué destinos aparecen destacados en la portada y configurá un banner de temporada. Los cambios se reflejan en la web pública sin tocar el código.', 'caaguazu-portal' ), 'promotur_curate_featured' ),
		array( 'shield', __( 'Moderación', 'caaguazu-portal' ), __( 'Aprobá o descartá reseñas, respondé o derivá consultas de visitantes y atendé los reportes de información desactualizada.', 'caaguazu-portal' ), 'promotur_moderate' ),
		array( 'team',   __( 'Equipo', 'caaguazu-portal' ), __( 'Gestioná a los Mini Promotores: revisá su producción, nivel de confianza y enlaces de invitación.', 'caaguazu-portal' ), 'promotur_manage_team' ),
		array( 'chart',  __( 'Reportes', 'caaguazu-portal' ), __( 'Consultá la producción por autor, los destinos más vistos, las búsquedas sin resultado y el estado general del contenido.', 'caaguazu-portal' ), 'promotur_view_reports' ),
		array( 'user',   __( 'Mi perfil', 'caaguazu-portal' ), __( 'Consultá tu portafolio público, las vistas de tus fichas y tu progreso de nivel de confianza.', 'caaguazu-portal' ), '' ),
	);
	?>
	<div class="promotur-eyebrow"><?php esc_html_e( 'Cómo funciona', 'caaguazu-portal' ); ?></div>
	<h2 class="promotur-h2"><?php esc_html_e( '¿Qué hace cada sección?', 'caaguazu-portal' ); ?></h2>
	<p class="promotur-muted" style="max-width:60ch">
		<?php esc_html_e( 'Este es el portal de los Promotores Turísticos: una web turística pública con un espacio de trabajo editorial detrás. Los Mini Promotores crean las fichas de destino y los Promotores las revisan y publican.', 'caaguazu-portal' ); ?>
	</p>

	<h3 class="promotur-h3"><?php esc_html_e( 'El flujo editorial', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-card">
		<p style="margin:0">
			<span class="promotur-pill is-draft"><?php esc_html_e( 'Borrador', 'caaguazu-portal' ); ?></span> →
			<span class="promotur-pill is-sent"><?php esc_html_e( 'Enviado', 'caaguazu-portal' ); ?></span> →
			<span class="promotur-pill is-review"><?php esc_html_e( 'En revisión', 'caaguazu-portal' ); ?></span> →
			<span class="promotur-pill is-changes"><?php esc_html_e( 'Necesita cambios', 'caaguazu-portal' ); ?></span> /
			<span class="promotur-pill is-published"><?php esc_html_e( 'Publicado', 'caaguazu-portal' ); ?></span>
		</p>
		<p class="promotur-muted" style="margin:.6rem 0 0">
			<?php esc_html_e( 'Solo las fichas aprobadas por un Promotor llegan al público. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel te da más autonomía, como editar fichas publicadas sin una nueva revisión y, finalmente, publicar directamente.', 'caaguazu-portal' ); ?>
		</p>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Las secciones', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-grid promotur-grid--2">
		<?php foreach ( $bloques as $b ) :
			if ( $b[3] && ! promotur_can( $b[3] ) ) { continue; } ?>
			<div class="promotur-card">
				<div class="promotur-quick" style="border:0;padding:0;margin-bottom:.4rem">
					<?php echo promotur_icon( $b[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<strong><?php echo esc_html( $b[1] ); ?></strong>
				</div>
				<p class="promotur-muted" style="margin:0"><?php echo esc_html( $b[2] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Extras', 'caaguazu-portal' ); ?></h3>
	<ul class="promotur-muted">
		<li><?php esc_html_e( 'Podés instalar el portal como app (PWA) y consultar parte del contenido sin conexión desde el menú lateral.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'Cada ficha pública puede tener reseñas, indicaciones para llegar, un código QR para imprimir y un botón para agregarla a «Mi viaje».', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'Podés cambiar entre modo claro y oscuro desde la barra superior.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'El acceso es solo por invitación. Pedí tu enlace al equipo de Turismo.', 'caaguazu-portal' ); ?></li>
	</ul>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
