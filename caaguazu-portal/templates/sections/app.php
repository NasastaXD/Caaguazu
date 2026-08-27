<?php
/**
 * App: la cabina de mando de la aplicación móvil.
 *
 * Tres bloques, uno por cada cosa que la app lee del servidor — textos por
 * idioma, manifiesto de medios, e icono y color de cada categoría. Cada bloque
 * se guarda por su cuenta: tocar los textos no puede pisar los medios.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/*
 * ESTA PANTALLA ESTÁ FUERA DE CIRCULACIÓN.
 *
 * La sección «app» no está registrada (ver PROMOTUR_Roles::sections()) y su
 * clase no se carga, así que esta plantilla no se alcanza por ninguna ruta del
 * panel. Queda en el repo con su código intacto para poder volver a
 * enchufarla; esta guarda existe para que, si alguien la vuelve a rutear sin
 * cargar la clase, vea un cartel en vez de un error fatal.
 */
if ( ! class_exists( 'PROMOTUR_App_Control' ) ) {
	$page_title = __( 'App', 'caaguazu-portal' );
	$body = function () {
		?>
		<div class="promotur-card promotur-empty-box">
			<div class="promotur-eyebrow"><?php esc_html_e( 'Fuera de servicio', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'La cabina de mando de la app está desconectada', 'caaguazu-portal' ); ?></h2>
			<p class="promotur-muted"><?php esc_html_e( 'Los textos y los medios de la aplicación se editan por ahora desde la administración del sitio. Se vuelve a enchufar cuando la API de la app esté en la versión que esta pantalla necesita.', 'caaguazu-portal' ); ?></p>
			<a class="promotur-btn promotur-btn--primary" href="<?php echo esc_url( promotur_url( 'panel' ) ); ?>"><?php esc_html_e( 'Volver al inicio del panel', 'caaguazu-portal' ); ?></a>
		</div>
		<?php
	};
	include PROMOTUR_DIR . 'templates/shell.php';
	return;
}

$locales = PROMOTUR_App_Control::locales();
$idioma  = isset( $_GET['idioma'] ) ? sanitize_key( wp_unslash( $_GET['idioma'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! in_array( $idioma, $locales, true ) ) {
	$idioma = $locales ? $locales[0] : 'es';
}

$textos = PROMOTUR_App_Control::strings( $idioma );
$medios = PROMOTUR_App_Control::manifest();
$cats   = PROMOTUR_App_Control::categorias();

$page_title = __( 'App', 'caaguazu-portal' );
$body = function () use ( $locales, $idioma, $textos, $medios, $cats ) {
	$accion = PROMOTUR_Acciones::url( 'save_app' );
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Aplicación', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'App', 'caaguazu-portal' ); ?></h2>
		</div>
	</div>

	<?php /* ---------- Textos ---------- */ ?>
	<div class="promotur-card">
		<div class="promotur-card__head">
			<?php echo promotur_icon( 'doc' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Textos', 'caaguazu-portal' ); ?></span>
			<span class="promotur-seg" role="group" aria-label="<?php esc_attr_e( 'Idioma', 'caaguazu-portal' ); ?>">
				<?php foreach ( $locales as $loc ) : ?>
					<a class="promotur-seg__item<?php echo $loc === $idioma ? ' is-active' : ''; ?>"
					   href="<?php echo esc_url( add_query_arg( 'idioma', $loc, promotur_url( 'panel/app' ) ) ); ?>">
						<?php echo esc_html( strtoupper( $loc ) ); ?>
					</a>
				<?php endforeach; ?>
			</span>
		</div>

		<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
			<?php PROMOTUR_Acciones::campos(); ?>
			<input type="hidden" name="bloque" value="textos">
			<input type="hidden" name="locale" value="<?php echo esc_attr( $idioma ); ?>">

			<?php foreach ( $textos as $clave => $valor ) : ?>
				<label class="promotur-field">
					<span><code><?php echo esc_html( $clave ); ?></code></span>
					<input type="text" name="textos[<?php echo esc_attr( $clave ); ?>]" value="<?php echo esc_attr( $valor ); ?>">
				</label>
			<?php endforeach; ?>

			<?php // Dos filas en blanco para sumar claves sin recargar la página. ?>
			<?php for ( $i = 0; $i < 2; $i++ ) : ?>
				<div class="promotur-grid promotur-grid--2">
					<label class="promotur-field">
						<span><?php esc_html_e( 'Clave', 'caaguazu-portal' ); ?></span>
						<input type="text" name="nueva_clave[]" value="">
					</label>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Texto', 'caaguazu-portal' ); ?></span>
						<input type="text" name="nuevo_valor[]" value="">
					</label>
				</div>
			<?php endfor; ?>

			<div>
				<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar cambios', 'caaguazu-portal' ); ?></button>
			</div>
		</form>
	</div>

	<?php /* ---------- Medios ---------- */ ?>
	<div class="promotur-card promotur-mt">
		<div class="promotur-card__head">
			<?php echo promotur_icon( 'image' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Medios', 'caaguazu-portal' ); ?></span>
			<a class="promotur-card__head-extra promotur-btn promotur-btn--ghost promotur-btn--small"
			   href="<?php echo esc_url( promotur_url( 'panel/biblioteca' ) ); ?>">
				<?php esc_html_e( 'Ir a la biblioteca', 'caaguazu-portal' ); ?>
			</a>
		</div>

		<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
			<?php PROMOTUR_Acciones::campos(); ?>
			<input type="hidden" name="bloque" value="medios">

			<?php
			// Las entradas guardadas, más una en blanco para agregar.
			$filas = $medios;
			$filas[''] = array();
			$n = 0;
			foreach ( $filas as $clave => $entrada ) :
				$tipo   = isset( $entrada['tipo'] ) ? $entrada['tipo'] : 'imagen';
				$medio  = isset( $entrada['id'] ) ? (string) $entrada['id'] : ( isset( $entrada['url'] ) ? $entrada['url'] : '' );
				$alt    = isset( $entrada['alt'] ) ? $entrada['alt'] : '';
				$forma  = isset( $entrada['formato'] ) ? $entrada['formato'] : '';
				$n++;
				?>
				<div class="promotur-fieldset">
					<div class="promotur-grid promotur-grid--2">
						<label class="promotur-field">
							<span><?php esc_html_e( 'Clave', 'caaguazu-portal' ); ?></span>
							<input type="text" name="medios[<?php echo esc_attr( $n ); ?>][clave]" value="<?php echo esc_attr( $clave ); ?>">
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'Tipo', 'caaguazu-portal' ); ?></span>
							<select name="medios[<?php echo esc_attr( $n ); ?>][tipo]">
								<option value="imagen"<?php selected( $tipo, 'imagen' ); ?>><?php esc_html_e( 'Imagen', 'caaguazu-portal' ); ?></option>
								<option value="animacion"<?php selected( $tipo, 'animacion' ); ?>><?php esc_html_e( 'Animación', 'caaguazu-portal' ); ?></option>
							</select>
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'URL o ID', 'caaguazu-portal' ); ?></span>
							<input type="text" name="medios[<?php echo esc_attr( $n ); ?>][medio]" value="<?php echo esc_attr( $medio ); ?>">
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'Texto alternativo', 'caaguazu-portal' ); ?></span>
							<input type="text" name="medios[<?php echo esc_attr( $n ); ?>][alt]" value="<?php echo esc_attr( $alt ); ?>">
						</label>
						<label class="promotur-field">
							<span><?php esc_html_e( 'Formato', 'caaguazu-portal' ); ?></span>
							<input type="text" name="medios[<?php echo esc_attr( $n ); ?>][formato]" value="<?php echo esc_attr( $forma ); ?>">
						</label>
					</div>
				</div>
			<?php endforeach; ?>

			<div>
				<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar cambios', 'caaguazu-portal' ); ?></button>
			</div>
		</form>
	</div>

	<?php /* ---------- Categorías ---------- */ ?>
	<div class="promotur-card promotur-mt">
		<div class="promotur-card__head">
			<?php echo promotur_icon( 'layout' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Categorías', 'caaguazu-portal' ); ?></span>
		</div>

		<?php if ( empty( $cats ) ) : ?>
			<p class="promotur-muted"><?php esc_html_e( 'Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.', 'caaguazu-portal' ); ?></p>
			<a class="promotur-btn promotur-btn--ghost" href="<?php echo esc_url( promotur_url( 'panel/estructura' ) ); ?>"><?php esc_html_e( 'Estructura', 'caaguazu-portal' ); ?></a>
		<?php else : ?>
			<form class="promotur-form" method="post" action="<?php echo esc_url( $accion ); ?>">
				<?php PROMOTUR_Acciones::campos(); ?>
				<input type="hidden" name="bloque" value="categorias">

				<?php foreach ( $cats as $cat ) : ?>
					<div class="promotur-fieldset">
						<div class="promotur-grid promotur-grid--3">
							<label class="promotur-field">
								<span><?php esc_html_e( 'Nombre', 'caaguazu-portal' ); ?></span>
								<input type="text" value="<?php echo esc_attr( $cat['nombre'] ); ?>" readonly>
							</label>
							<label class="promotur-field">
								<span><?php esc_html_e( 'Color', 'caaguazu-portal' ); ?></span>
								<input type="color" name="categorias[<?php echo esc_attr( $cat['term_id'] ); ?>][color]"
								       value="<?php echo esc_attr( $cat['color'] ? $cat['color'] : '#101012' ); ?>">
							</label>
							<label class="promotur-field">
								<span><?php esc_html_e( 'Icono', 'caaguazu-portal' ); ?></span>
								<input type="text" name="categorias[<?php echo esc_attr( $cat['term_id'] ); ?>][icono]" value="<?php echo esc_attr( $cat['icono'] ); ?>">
							</label>
						</div>
					</div>
				<?php endforeach; ?>

				<div>
					<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar cambios', 'caaguazu-portal' ); ?></button>
				</div>
			</form>
		<?php endif; ?>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
