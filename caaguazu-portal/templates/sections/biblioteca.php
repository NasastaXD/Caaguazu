<?php
/**
 * La galería del panel.
 *
 * Hasta acá esta pantalla era un cartel con un enlace a la biblioteca de
 * WordPress. Ahora las fotos se ven, se suben de a tandas, se describen y se
 * borran sin salir del panel.
 *
 * Cada foto se edita en su propio bloque desplegable en vez de en una ventana
 * flotante: en el teléfono, que es donde se usa esto, una ventana modal tapa la
 * foto que estás describiendo.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$busqueda = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$mias     = ! empty( $_GET['mias'] ); // phpcs:ignore WordPress.Security.NonceVerification
$pagina   = isset( $_GET['pag'] ) ? max( 1, (int) $_GET['pag'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
$galeria  = PROMOTUR_Medios::pagina( array( 'pagina' => $pagina, 'busqueda' => $busqueda, 'mias' => $mias ) );
$base_url = promotur_url( 'panel/biblioteca' );

$page_title = __( 'Biblioteca', 'caaguazu-portal' );
$body = function () use ( $galeria, $busqueda, $mias, $base_url ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Medios', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php esc_html_e( 'Biblioteca de medios', 'caaguazu-portal' ); ?></h2>
		</div>
	</div>

	<?php /* ---------- Subir ---------- */ ?>
	<div class="promotur-card">
		<form class="promotur-form" method="post" enctype="multipart/form-data"
			  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'media_subir' ) ); ?>">
			<?php PROMOTUR_Acciones::campos(); ?>
			<label class="promotur-field">
				<span><?php esc_html_e( 'Subir fotos', 'caaguazu-portal' ); ?></span>
				<input type="file" name="fotos[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
			</label>
			<p class="promotur-form-msg"><?php esc_html_e( 'Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.', 'caaguazu-portal' ); ?></p>
			<div>
				<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Subir', 'caaguazu-portal' ); ?></button>
			</div>
		</form>
	</div>

	<?php /* ---------- Filtros ---------- */ ?>
	<form class="promotur-filtros" method="get" action="<?php echo esc_url( $base_url ); ?>">
		<input class="promotur-buscador" type="search" name="q" value="<?php echo esc_attr( $busqueda ); ?>"
			   placeholder="<?php esc_attr_e( 'Buscar una foto', 'caaguazu-portal' ); ?>"
			   aria-label="<?php esc_attr_e( 'Buscar una foto', 'caaguazu-portal' ); ?>">
		<label class="promotur-check">
			<input type="checkbox" name="mias" value="1" <?php checked( $mias ); ?>>
			<span><?php esc_html_e( 'Sólo las mías', 'caaguazu-portal' ); ?></span>
		</label>
		<button type="submit" class="promotur-btn promotur-btn--ghost"><?php esc_html_e( 'Filtrar', 'caaguazu-portal' ); ?></button>
	</form>

	<?php if ( empty( $galeria['fotos'] ) ) : ?>

		<div class="promotur-card promotur-empty-box">
			<p>
				<?php if ( '' !== $busqueda ) : ?>
					<?php esc_html_e( 'No encontramos ninguna foto con ese nombre.', 'caaguazu-portal' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Todavía no hay fotos. Subí las primeras acá arriba.', 'caaguazu-portal' ); ?>
				<?php endif; ?>
			</p>
		</div>

	<?php else : ?>

		<p class="promotur-muted">
			<?php
			printf(
				/* translators: %d = cuántas fotos hay */
				esc_html( _n( '%d foto', '%d fotos', $galeria['total'], 'caaguazu-portal' ) ),
				(int) $galeria['total']
			);
			?>
		</p>

		<div class="promotur-galeria">
			<?php foreach ( $galeria['fotos'] as $foto ) : ?>
				<?php
				$miniatura = wp_get_attachment_image_url( $foto->ID, 'medium' );
				$alt       = (string) get_post_meta( $foto->ID, '_wp_attachment_image_alt', true );
				$credito   = PROMOTUR_Medios::credito( $foto->ID );
				$editable  = PROMOTUR_Medios::puede_editar( $foto->ID );
				$usada     = PROMOTUR_Medios::usada_en( $foto->ID );
				?>
				<details class="promotur-foto">
					<summary class="promotur-foto__cara">
						<?php if ( $miniatura ) : ?>
							<img class="promotur-foto__img" src="<?php echo esc_url( $miniatura ); ?>"
								 alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
						<?php endif; ?>
						<span class="promotur-foto__pie">
							<span class="promotur-foto__nombre"><?php echo esc_html( get_the_title( $foto ) ); ?></span>
							<?php if ( '' === $alt ) : ?>
								<span class="promotur-pill is-muted"><?php esc_html_e( 'Sin descripción', 'caaguazu-portal' ); ?></span>
							<?php endif; ?>
						</span>
					</summary>

					<div class="promotur-foto__ficha">
						<?php if ( $usada ) : ?>
							<p class="promotur-form-msg">
								<?php
								printf(
									/* translators: %d = en cuántas fichas se usa la foto */
									esc_html( _n( 'Es la portada de %d ficha.', 'Es la portada de %d fichas.', count( $usada ), 'caaguazu-portal' ) ),
									count( $usada )
								);
								?>
							</p>
						<?php endif; ?>

						<?php if ( ! $editable ) : ?>
							<p class="promotur-form-msg"><?php esc_html_e( 'Esta foto la subió otra persona, así que sólo podés verla.', 'caaguazu-portal' ); ?></p>
						<?php else : ?>
							<form class="promotur-form" method="post"
								  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'media_guardar' ) ); ?>">
								<?php PROMOTUR_Acciones::campos(); ?>
								<input type="hidden" name="id" value="<?php echo esc_attr( $foto->ID ); ?>">

								<label class="promotur-field">
									<span><?php esc_html_e( 'Nombre', 'caaguazu-portal' ); ?></span>
									<input type="text" name="titulo" value="<?php echo esc_attr( get_the_title( $foto ) ); ?>">
								</label>
								<label class="promotur-field">
									<span><?php esc_html_e( 'Descripción', 'caaguazu-portal' ); ?></span>
									<input type="text" name="alt" value="<?php echo esc_attr( $alt ); ?>"
										   placeholder="<?php esc_attr_e( 'Qué se ve en la foto', 'caaguazu-portal' ); ?>">
								</label>
								<label class="promotur-field">
									<span><?php esc_html_e( 'Crédito', 'caaguazu-portal' ); ?> <em><?php esc_html_e( 'opcional', 'caaguazu-portal' ); ?></em></span>
									<input type="text" name="credito" value="<?php echo esc_attr( $credito ); ?>"
										   placeholder="<?php esc_attr_e( 'Quién la sacó', 'caaguazu-portal' ); ?>">
								</label>

								<div class="promotur-foto__acciones">
									<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar', 'caaguazu-portal' ); ?></button>
								</div>
							</form>

							<?php if ( ! $usada ) : ?>
								<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'media_borrar' ) ); ?>"
									  data-confirmar="<?php esc_attr_e( 'Se borra la foto y no se puede deshacer. ¿Seguimos?', 'caaguazu-portal' ); ?>">
									<?php PROMOTUR_Acciones::campos(); ?>
									<input type="hidden" name="id" value="<?php echo esc_attr( $foto->ID ); ?>">
									<button type="submit" class="promotur-btn promotur-btn--peligro"><?php esc_html_e( 'Borrar foto', 'caaguazu-portal' ); ?></button>
								</form>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

		<?php if ( $galeria['paginas'] > 1 ) : ?>
			<nav class="promotur-paginas">
				<?php if ( $galeria['pagina'] > 1 ) : ?>
					<a class="promotur-btn promotur-btn--ghost"
					   href="<?php echo esc_url( add_query_arg( 'pag', $galeria['pagina'] - 1, $base_url ) ); ?>"><?php esc_html_e( 'Anteriores', 'caaguazu-portal' ); ?></a>
				<?php endif; ?>
				<span class="promotur-muted">
					<?php
					printf(
						/* translators: 1 = página actual, 2 = total de páginas */
						esc_html__( 'Página %1$d de %2$d', 'caaguazu-portal' ),
						(int) $galeria['pagina'],
						(int) $galeria['paginas']
					);
					?>
				</span>
				<?php if ( $galeria['pagina'] < $galeria['paginas'] ) : ?>
					<a class="promotur-btn promotur-btn--ghost"
					   href="<?php echo esc_url( add_query_arg( 'pag', $galeria['pagina'] + 1, $base_url ) ); ?>"><?php esc_html_e( 'Siguientes', 'caaguazu-portal' ); ?></a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

	<?php endif; ?>
	<?php
};
include PROMOTUR_DIR . 'templates/shell.php';
