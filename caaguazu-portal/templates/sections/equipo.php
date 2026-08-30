<?php
/**
 * Equipo: quién entra al panel, con qué rol, qué produce y qué invitaciones
 * quedan abiertas.
 *
 * Cambiar el rol, suspender y sacar del panel se hacía en wp-admin, sobre
 * usuarios de WordPress que los promotores no tienen. Se hace acá, sobre la
 * cuenta y su permiso, que es lo que de verdad decide quién entra.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$roles       = PROMOTUR_Roles::roles();
$levels      = PROMOTUR_Stats::levels();
$administra  = promotur_can( PROMOTUR_Equipo::CAP );
$invitaciones = $administra ? PROMOTUR_Equipo::invitaciones_abiertas() : array();

$page_title = __( 'Equipo', 'caaguazu-portal' );
$body = function () use ( $roles, $levels, $administra, $invitaciones ) {
	?>
	<div class="promotur-eyebrow"><?php esc_html_e( 'Tu equipo', 'caaguazu-portal' ); ?></div>
	<h2 class="promotur-h2"><?php esc_html_e( 'Equipo', 'caaguazu-portal' ); ?></h2>

	<?php if ( $administra ) : ?>
		<div class="promotur-card promotur-invite">
			<h3 class="promotur-h3"><?php esc_html_e( 'Invitar a alguien', 'caaguazu-portal' ); ?></h3>
			<p class="promotur-muted"><?php esc_html_e( 'Generá un enlace de invitación con el rol, el vencimiento y cuántas cuentas puede crear.', 'caaguazu-portal' ); ?></p>
			<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'invite' ) ); ?>" class="promotur-form">
				<?php PROMOTUR_Acciones::campos(); ?>
				<div class="promotur-grid promotur-grid--2">
					<label class="promotur-field">
						<span><?php esc_html_e( 'Rol', 'caaguazu-portal' ); ?></span>
						<select name="role">
							<?php foreach ( $roles as $rk => $rd ) : ?>
								<option value="<?php echo esc_attr( $rk ); ?>" <?php selected( 'promotur_mini', $rk ); ?>><?php echo esc_html( $rd['label'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Vence en (días)', 'caaguazu-portal' ); ?></span>
						<input type="number" name="expires_days" min="0" step="1" placeholder="0" list="promotur-dias-sugeridos">
						<small class="promotur-ayuda"><?php esc_html_e( 'Vacío o 0: no vence nunca.', 'caaguazu-portal' ); ?></small>
					</label>
					<label class="promotur-field">
						<span><?php esc_html_e( 'Cuántas cuentas puede crear', 'caaguazu-portal' ); ?></span>
						<input type="number" name="max_usos" min="0" step="1" value="1">
						<small class="promotur-ayuda"><?php esc_html_e( 'Vacío o 0: sin límite.', 'caaguazu-portal' ); ?></small>
					</label>
				</div>
				<datalist id="promotur-dias-sugeridos">
					<?php foreach ( PROMOTUR_Invitations::dias_sugeridos() as $d ) : ?>
						<option value="<?php echo esc_attr( $d ); ?>"></option>
					<?php endforeach; ?>
				</datalist>
				<div class="promotur-editor__actions">
					<button type="submit" class="promotur-btn promotur-btn--primary"><?php esc_html_e( 'Crear enlace', 'caaguazu-portal' ); ?></button>
				</div>
			</form>
		</div>
	<?php endif; ?>

	<?php foreach ( $roles as $role_key => $def ) :
		$users = promotur_team_members( $role_key, $administra );
		if ( empty( $users ) ) { continue; }
		$is_mini = ( 'promotur_mini' === $role_key );
		?>
		<h3 class="promotur-h3"><?php echo esc_html( $def['label'] ); ?> <span class="promotur-muted">(<?php echo count( $users ); ?>)</span></h3>
		<div class="promotur-list">
			<?php foreach ( $users as $u ) :
				$counts = PROMOTUR_Stats::author_counts( $u['id'] ); ?>
				<div class="promotur-card promotur-mod" data-user="<?php echo esc_attr( $u['id'] ); ?>">
					<div class="promotur-row__user">
						<?php echo promotur_avatar( $u, 'promotur-avatar--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span>
							<span class="promotur-row__title"><?php echo esc_html( $u['display_name'] ); ?></span>
							<span class="promotur-row__meta">
								<?php
								/* translators: 1: publicadas, 2: total */
								printf( esc_html__( '%1$d publicadas · %2$d en total', 'caaguazu-portal' ), $counts['publicadas'], $counts['total'] );
								if ( $is_mini ) {
									echo ' · ' . esc_html( PROMOTUR_Stats::level_label( $u['id'] ) );
								}
								?>
							</span>
						</span>
					</div>
					<?php if ( $is_mini ) : ?>
						<div class="promotur-inline-form">
							<span class="promotur-muted"><?php esc_html_e( 'Nivel de confianza:', 'caaguazu-portal' ); ?></span>
							<select data-nivel-select>
								<?php $cur = PROMOTUR_Stats::get_level( $u['id'] );
								foreach ( $levels as $lk => $ll ) : ?>
									<option value="<?php echo esc_attr( $lk ); ?>" <?php selected( $cur, $lk ); ?>><?php echo esc_html( $ll ); ?></option>
								<?php endforeach; ?>
							</select>
							<button type="button" class="promotur-btn promotur-btn--ghost promotur-btn--small" data-nivel-save><?php esc_html_e( 'Guardar', 'caaguazu-portal' ); ?></button>
							<span class="promotur-form-msg" data-form-msg aria-live="polite"></span>
						</div>
					<?php endif; ?>

					<?php if ( $administra && (int) $u['id'] !== (int) caaguazu_account_id() ) : ?>
						<?php $suspendida = isset( $u['status'] ) && 'active' !== $u['status']; ?>
						<div class="promotur-mod__gestion">
							<?php if ( $suspendida ) : ?>
								<span class="promotur-pill is-changes"><?php esc_html_e( 'Suspendida', 'caaguazu-portal' ); ?></span>
							<?php endif; ?>

							<form class="promotur-inline-form" method="post"
								  action="<?php echo esc_url( PROMOTUR_Acciones::url( 'equipo_rol' ) ); ?>">
								<?php PROMOTUR_Acciones::campos(); ?>
								<input type="hidden" name="cuenta" value="<?php echo esc_attr( $u['id'] ); ?>">
								<select name="rol" aria-label="<?php esc_attr_e( 'Rol', 'caaguazu-portal' ); ?>">
									<?php foreach ( $roles as $rk => $rd ) : ?>
										<option value="<?php echo esc_attr( $rk ); ?>" <?php selected( $u['role'], $rk ); ?>><?php echo esc_html( $rd['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<button type="submit" class="promotur-btn promotur-btn--ghost promotur-btn--small"><?php esc_html_e( 'Cambiar rol', 'caaguazu-portal' ); ?></button>
							</form>

							<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'equipo_estado' ) ); ?>">
								<?php PROMOTUR_Acciones::campos(); ?>
								<input type="hidden" name="cuenta" value="<?php echo esc_attr( $u['id'] ); ?>">
								<button type="submit" class="promotur-btn promotur-btn--ghost promotur-btn--small">
									<?php echo $suspendida ? esc_html__( 'Reactivar', 'caaguazu-portal' ) : esc_html__( 'Suspender', 'caaguazu-portal' ); ?>
								</button>
							</form>

							<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'equipo_quitar' ) ); ?>"
								  data-confirmar="<?php esc_attr_e( 'Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?', 'caaguazu-portal' ); ?>">
								<?php PROMOTUR_Acciones::campos(); ?>
								<input type="hidden" name="cuenta" value="<?php echo esc_attr( $u['id'] ); ?>">
								<button type="submit" class="promotur-btn promotur-btn--peligro promotur-btn--small"><?php esc_html_e( 'Sacar del panel', 'caaguazu-portal' ); ?></button>
							</form>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( $administra ) : ?>
		<h3 class="promotur-h3 promotur-mt"><?php esc_html_e( 'Invitaciones abiertas', 'caaguazu-portal' ); ?></h3>
		<div class="promotur-card">
			<?php if ( empty( $invitaciones ) ) : ?>
				<p class="promotur-muted"><?php esc_html_e( 'No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.', 'caaguazu-portal' ); ?></p>
			<?php else : ?>
				<div class="promotur-list">
					<?php foreach ( $invitaciones as $inv ) :
						$enlace = PROMOTUR_Invitations::registration_url( PROMOTUR_Invitations::plain_token( $inv ) );
						?>
						<div class="promotur-termino promotur-termino--invite">
							<div class="promotur-termino__fila">
								<span class="promotur-termino__nombre">
									<?php echo esc_html( PROMOTUR_Roles::label( $inv['role'] ) ); ?>
								</span>
								<span class="promotur-termino__uso">
									<span class="promotur-muted">
										<?php echo esc_html( PROMOTUR_Invitations::vence_texto( $inv ) ); ?>
										· <?php echo esc_html( PROMOTUR_Invitations::usos_texto( $inv ) ); ?>
									</span>
									<form method="post" action="<?php echo esc_url( PROMOTUR_Acciones::url( 'invitacion_revocar' ) ); ?>"
										  data-confirmar="<?php esc_attr_e( 'El enlace deja de servir. ¿Seguimos?', 'caaguazu-portal' ); ?>">
										<?php PROMOTUR_Acciones::campos(); ?>
										<input type="hidden" name="invitacion" value="<?php echo esc_attr( $inv['id'] ); ?>">
										<button type="submit" class="promotur-btn promotur-btn--peligro promotur-btn--small"><?php esc_html_e( 'Revocar', 'caaguazu-portal' ); ?></button>
									</form>
								</span>
							</div>
							<?php if ( $enlace ) : ?>
								<div class="promotur-copiar" data-copiar>
									<input type="text" readonly value="<?php echo esc_attr( $enlace ); ?>" data-copiar-valor aria-label="<?php esc_attr_e( 'Enlace de invitación', 'caaguazu-portal' ); ?>">
									<button type="button" class="promotur-btn promotur-btn--ghost promotur-btn--small" data-copiar-boton><?php esc_html_e( 'Copiar', 'caaguazu-portal' ); ?></button>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
