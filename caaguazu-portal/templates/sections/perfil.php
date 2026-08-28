<?php
/**
 * Mi perfil: la cuenta, el nivel de confianza y lo publicado.
 *
 * La cuenta se edita acá y en ningún otro lado. Vive en `caaguazu-cuentas`,
 * que es un sistema aparte de los usuarios de WordPress: cambiar el nombre, el
 * correo, el teléfono, la foto o la contraseña de un promotor no tiene nada que
 * ver con `wp-admin/profile.php`, que edita otra cosa —un usuario de WordPress
 * que un promotor ni siquiera tiene.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$identity = promotur_current_identity();
$uid      = caaguazu_account_id();
// El portafolio son las tres cosas que se publican, no sólo las fichas: una
// persona que escribió cinco artículos tiene un portafolio, aunque no haya
// cargado ninguna ficha.
$pub      = get_posts( array(
	'post_type'      => PROMOTUR_Editorial::cpts(),
	'post_status'    => 'publish',
	'meta_query'     => array( array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $uid ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	'posts_per_page' => 50,
) );
$is_mini = ( 'promotur_mini' === promotur_user_role() );

$page_title = __( 'Mi perfil', 'caaguazu-portal' );
$body = function () use ( $identity, $uid, $pub, $is_mini ) {
	?>
	<div class="promotur-profile">
		<?php echo promotur_avatar( $identity, 'promotur-avatar--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div>
			<h2 class="promotur-h2"><?php echo esc_html( $identity['display_name'] ); ?></h2>
			<p class="promotur-muted">
				<?php echo esc_html( promotur_role_label() ); ?>
				<?php if ( $is_mini ) : ?> · <span class="promotur-pill is-approved"><?php echo esc_html( PROMOTUR_Stats::level_label( $uid ) ); ?></span><?php endif; ?>
			</p>
		</div>
	</div>

	<?php if ( $is_mini ) : ?>
		<div class="promotur-card promotur-trust">
			<h3 class="promotur-h3"><?php esc_html_e( 'Tu progreso de confianza', 'caaguazu-portal' ); ?></h3>
			<div class="promotur-trustbar">
				<?php
				$levels = PROMOTUR_Stats::levels();
				$cur    = PROMOTUR_Stats::get_level( $uid );
				$keys   = array_keys( $levels );
				$ci     = array_search( $cur, $keys, true );
				foreach ( $keys as $idx => $lk ) : ?>
					<span class="promotur-truststep<?php echo $idx <= $ci ? ' is-on' : ''; ?>"><?php echo esc_html( $levels[ $lk ] ); ?></span>
				<?php endforeach; ?>
			</div>
			<p class="promotur-muted">
				<?php
				if ( 'confianza' === $cur ) {
					esc_html_e( 'Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.', 'caaguazu-portal' );
				} elseif ( 'jr' === $cur ) {
					esc_html_e( 'Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».', 'caaguazu-portal' );
				} else {
					esc_html_e( 'Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.', 'caaguazu-portal' );
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="promotur-grid promotur-grid--3">
		<div class="promotur-card promotur-stat">
			<div class="promotur-card__head">
				<span><?php esc_html_e( 'Fichas publicadas', 'caaguazu-portal' ); ?></span>
				<span class="promotur-card__head-extra"><?php echo promotur_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</div>
			<div class="promotur-stat__caja">
				<span class="promotur-stat__n"><?php echo esc_html( count( $pub ) ); ?></span>
			</div>
		</div>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Mi portafolio', 'caaguazu-portal' ); ?></h3>
	<?php if ( empty( $pub ) ) : ?>
		<p class="promotur-muted"><?php esc_html_e( 'Todavía no tenés nada publicado.', 'caaguazu-portal' ); ?></p>
	<?php else : ?>
		<div class="promotur-list">
			<?php foreach ( $pub as $p ) : ?>
				<a class="promotur-row" href="<?php echo esc_url( PROMOTUR_Editorial::url_editor( $p ) ); ?>">
					<span class="promotur-row__main">
						<span class="promotur-row__title"><?php echo esc_html( get_the_title( $p ) ); ?></span>
					</span>
					<span class="promotur-pill is-published"><?php esc_html_e( 'Publicado', 'caaguazu-portal' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( 0 !== $uid ) : ?>

		<h3 class="promotur-h3 promotur-mt"><?php esc_html_e( 'Mis datos', 'caaguazu-portal' ); ?></h3>
		<div class="promotur-card">
			<form class="promotur-form" method="post" enctype="multipart/form-data"
				  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'perfil' ) ); ?>">
				<?php PROMOTUR_Acciones::campos(); ?>

				<label class="promotur-field">
					<span><?php esc_html_e( 'Nombre', 'caaguazu-portal' ); ?></span>
					<input type="text" name="display_name" required
						   value="<?php echo esc_attr( $identity['display_name'] ); ?>">
				</label>

				<div class="promotur-grid promotur-grid--2">
					<label class="promotur-field">
						<span><?php esc_html_e( 'Correo', 'caaguazu-portal' ); ?></span>
						<input type="email" name="email" required
							   value="<?php echo esc_attr( $identity['email'] ); ?>">
					</label>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Teléfono', 'caaguazu-portal' ); ?> <em><?php esc_html_e( 'opcional', 'caaguazu-portal' ); ?></em></span>
						<input type="tel" name="phone" value="<?php echo esc_attr( $identity['phone'] ); ?>">
					</label>
				</div>

				<label class="promotur-field">
					<span><?php esc_html_e( 'Foto', 'caaguazu-portal' ); ?> <em><?php esc_html_e( 'opcional, JPG/PNG/WEBP, hasta 5 MB', 'caaguazu-portal' ); ?></em></span>
					<input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
				</label>
				<p class="promotur-form-msg"><?php esc_html_e( 'Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.', 'caaguazu-portal' ); ?></p>

				<div>
					<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Guardar cambios', 'caaguazu-portal' ); ?></button>
				</div>
			</form>
		</div>

		<h3 class="promotur-h3 promotur-mt"><?php esc_html_e( 'Contraseña', 'caaguazu-portal' ); ?></h3>
		<div class="promotur-card">
			<form class="promotur-form" method="post"
				  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'clave' ) ); ?>">
				<?php PROMOTUR_Acciones::campos(); ?>

				<label class="promotur-field">
					<span><?php esc_html_e( 'Contraseña actual', 'caaguazu-portal' ); ?></span>
					<input type="password" name="clave_actual" autocomplete="current-password" required>
				</label>

				<div class="promotur-grid promotur-grid--2">
					<label class="promotur-field">
						<span><?php esc_html_e( 'Contraseña nueva', 'caaguazu-portal' ); ?></span>
						<input type="password" name="clave_nueva" autocomplete="new-password" required>
					</label>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Repetila', 'caaguazu-portal' ); ?></span>
						<input type="password" name="clave_repetir" autocomplete="new-password" required>
					</label>
				</div>

				<div>
					<button type="submit" class="promotur-btn"><?php esc_html_e( 'Cambiar contraseña', 'caaguazu-portal' ); ?></button>
				</div>
			</form>
		</div>

	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
