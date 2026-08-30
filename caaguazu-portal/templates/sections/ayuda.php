<?php
/**
 * Ayuda: qué hace cada sección, gateada por la misma capability que la
 * sección de verdad.
 *
 * La lista de secciones sale de `PROMOTUR_Roles::sections()` —el mismo mapa
 * que decide qué se puede abrir— y no de una copia escrita a mano: una copia
 * se desactualiza sola apenas se agrega o se saca una sección, que es
 * exactamente lo que le pasó a esta pantalla (llegó a describir un sitio
 * público, reseñas y una curaduría que dejaron de existir hace versiones).
 * Acá sólo se escribe a mano la descripción de cada una; cuáles existen y
 * quién las ve lo decide el registro único.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$page_title = __( 'Ayuda', 'caaguazu-portal' );
$body = function () {
	/**
	 * icono + rótulo (el mismo texto que usa el menú lateral) + descripción,
	 * por sección. La clave tiene que ser una de PROMOTUR_Roles::sections();
	 * una sección sin entrada acá se muestra igual, con su slug de rótulo y
	 * sin descripción, en vez de desaparecer.
	 */
	$descripciones = array(
		'home'           => array( 'home',   __( 'Inicio', 'caaguazu-portal' ),             __( 'Tu resumen del día: lo que espera revisión, lo que necesita correcciones tuyas, y accesos rápidos según tu rol.', 'caaguazu-portal' ) ),
		'buscar'         => array( 'search', __( 'Buscar', 'caaguazu-portal' ),              __( 'Buscar entre las fichas, artículos y recorridos del panel.', 'caaguazu-portal' ) ),
		'editor'         => array( 'edit',   __( 'Nueva ficha', 'caaguazu-portal' ),         __( 'El editor guiado de una ficha —un sitio o un evento con fecha—, con checklist de mínimos que avisa si falta algo antes de enviar a revisión.', 'caaguazu-portal' ) ),
		'captura'        => array( 'image',  __( 'Salida de campo', 'caaguazu-portal' ),     __( 'Sacá una foto, anotá información y guardá la ubicación GPS en el lugar, incluso sin señal. Se sincroniza como borrador cuando vuelve la conexión.', 'caaguazu-portal' ) ),
		'mis-contenidos' => array( 'doc',    __( 'Mis contenidos', 'caaguazu-portal' ),      __( 'Todo lo que cargaste —fichas, artículos, recorridos—, ordenado por estado, con filtro para ver lo archivado y lo borrado.', 'caaguazu-portal' ) ),
		'inventario'     => array( 'pin',    __( 'Inventario turístico', 'caaguazu-portal' ), __( 'El catálogo de fichas publicadas del departamento. De acá se eligen las paradas al armar un recorrido.', 'caaguazu-portal' ) ),
		'articulos'      => array( 'nota',   __( 'Artículos', 'caaguazu-portal' ),           __( 'Las notas que la app muestra: título, foto de portada, entradilla, cuerpo y fuentes. Pasan por el mismo flujo de revisión que una ficha.', 'caaguazu-portal' ) ),
		'recorridos'     => array( 'ruta',   __( 'Recorridos', 'caaguazu-portal' ),          __( 'Se arman eligiendo sitios del inventario —hasta nueve—, cada uno con su propio texto, audio o video, en el orden del paseo.', 'caaguazu-portal' ) ),
		'revision'       => array( 'inbox',  __( 'Cola de revisión', 'caaguazu-portal' ),    __( 'La cola de lo que espera revisión: asignate una pieza, aprobala y publicala, o devolvela al autor con comentarios.', 'caaguazu-portal' ) ),
		'tareas'         => array( 'tasks',  __( 'Tareas', 'caaguazu-portal' ),              __( 'Encargos con fecha límite. Los Alumnos pueden reclamar los que están disponibles y marcarlos como hechos.', 'caaguazu-portal' ) ),
		'equipo'         => array( 'team',   __( 'Equipo', 'caaguazu-portal' ),              __( 'Quién entra al panel, con qué rol y su nivel de confianza. Cambiar el rol, suspender, sacar del panel, e invitar gente nueva.', 'caaguazu-portal' ) ),
		'reportes'       => array( 'chart',  __( 'Reportes', 'caaguazu-portal' ),            __( 'Producción por autor y salud del contenido: lo publicado sin portada, y lo que no se verifica hace más de seis meses.', 'caaguazu-portal' ) ),
		'biblioteca'     => array( 'image',  __( 'Biblioteca', 'caaguazu-portal' ),          __( 'La galería de fotos del panel: subir de a tandas, describir, dar crédito y borrar.', 'caaguazu-portal' ) ),
		'estructura'     => array( 'layout', __( 'Estructura', 'caaguazu-portal' ),          __( 'Las categorías y etiquetas de las fichas: crear, renombrar en su lugar, y borrar lo que no esté en uso.', 'caaguazu-portal' ) ),
		'perfil'         => array( 'user',   __( 'Mi perfil', 'caaguazu-portal' ),           __( 'Tu cuenta —nombre, correo, teléfono, foto y contraseña—, tu nivel de confianza y el portafolio de lo que publicaste.', 'caaguazu-portal' ) ),
	);
	?>
	<div class="promotur-eyebrow"><?php esc_html_e( 'Cómo funciona', 'caaguazu-portal' ); ?></div>
	<h2 class="promotur-h2"><?php esc_html_e( '¿Qué hace cada sección?', 'caaguazu-portal' ); ?></h2>
	<p class="promotur-muted" style="max-width:60ch">
		<?php esc_html_e( 'Este es el panel de los Promotores Turísticos: acá se escribe, se revisa y se publica todo lo que la app de Caaguazú muestra —fichas de destinos y eventos, artículos y recorridos—. Los Alumnos crean; los Profesores revisan y publican.', 'caaguazu-portal' ); ?>
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
			<?php esc_html_e( 'Solo lo aprobado por un Profesor llega a la app. La confianza se construye con cada aprobación: pasás de Aprendiz a Alumno Jr y luego a De confianza. Cada nivel da más autonomía, como editar algo publicado sin una nueva revisión y, en el último, publicar directamente.', 'caaguazu-portal' ); ?>
		</p>
		<p class="promotur-muted" style="margin:.6rem 0 0">
			<?php esc_html_e( 'Lo publicado también se puede despublicar, archivar o mandar a la papelera —y volver atrás desde ahí—, siempre en dos pasos: primero hay que sacarlo del aire antes de poder borrarlo.', 'caaguazu-portal' ); ?>
		</p>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Las secciones', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-grid promotur-grid--2">
		<?php foreach ( PROMOTUR_Roles::sections() as $slug => $cap ) :
			if ( 'ayuda' === $slug ) { continue; } // no hace falta explicarse a sí misma.
			if ( $cap && ! promotur_can( $cap ) ) { continue; }
			$def = isset( $descripciones[ $slug ] ) ? $descripciones[ $slug ] : array( 'doc', ucfirst( str_replace( '-', ' ', $slug ) ), '' );
			?>
			<div class="promotur-card">
				<div class="promotur-quick" style="border:0;padding:0;margin-bottom:.4rem">
					<?php echo promotur_icon( $def[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<strong><?php echo esc_html( $def[1] ); ?></strong>
				</div>
				<?php if ( $def[2] ) : ?>
					<p class="promotur-muted" style="margin:0"><?php echo esc_html( $def[2] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Extras', 'caaguazu-portal' ); ?></h3>
	<ul class="promotur-muted">
		<li><?php esc_html_e( 'Podés instalar el panel como app (PWA) desde el menú lateral.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'La salida de campo funciona sin conexión: lo que cargues se guarda en el teléfono y se sube solo cuando vuelve la señal.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'Podés cambiar entre modo claro y oscuro desde la barra superior.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'El acceso es solo por invitación. Pedí tu enlace a quien coordina el equipo.', 'caaguazu-portal' ); ?></li>
	</ul>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
