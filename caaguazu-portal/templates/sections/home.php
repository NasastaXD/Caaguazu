<?php
/**
 * Inicio / pulso accionable del panel.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$uid        = caaguazu_account_id();
$identity   = promotur_current_identity();
$can_review = caaguazu_account_can( 'promotor', 'promotur_review_content' );
$can_draft  = caaguazu_account_can( 'promotor', 'promotur_create_draft' );

/**
 * Cuenta destinos por estado (y dueño opcional). El dueño se filtra por el
 * meta de dueño real (ver PROMOTUR_Destinos::OWNER_META), no por post_author.
 */
$count_by = function ( $estado, $owner = 0 ) {
	$meta_query = array( array( 'key' => '_promotur_estado', 'value' => (array) $estado, 'compare' => 'IN' ) );
	if ( $owner ) {
		$meta_query['relation'] = 'AND';
		$meta_query[] = array( 'key' => PROMOTUR_Destinos::OWNER_META, 'value' => $owner );
	}
	$q = new WP_Query( array(
		'post_type'      => PROMOTUR_Destinos::CPT,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	return (int) $q->found_posts;
};

$pulse = array();
if ( $can_review ) {
	$pulse[] = array( 'n' => PROMOTUR_Notifications::review_queue_count(), 'label' => __( 'esperan revisión', 'caaguazu-portal' ), 'url' => 'panel/revision', 'icon' => 'inbox' );
	$pulse[] = array( 'n' => $count_by( 'publicado' ), 'label' => __( 'publicados', 'caaguazu-portal' ), 'url' => 'panel/revision', 'icon' => 'check' );
}
if ( $can_draft ) {
	$pulse[] = array( 'n' => $count_by( 'necesita_cambios', $uid ), 'label' => __( 'esperan tu corrección', 'caaguazu-portal' ), 'url' => 'panel/mis-contenidos', 'icon' => 'edit' );
	$pulse[] = array( 'n' => $count_by( array( 'borrador', 'enviado', 'en_revision' ), $uid ), 'label' => __( 'en proceso', 'caaguazu-portal' ), 'url' => 'panel/mis-contenidos', 'icon' => 'doc' );
}

// Actividad editorial de los últimos 7 días, del log de auditoría. Es la única
// serie temporal real que guarda el portal: no hay contadores por día de nada
// más, así que no hay más gráficos que este.
$serie = PROMOTUR_Stats::serie_diaria( PROMOTUR_Audit::post_actions(), 7 );
$tope  = 0;
foreach ( $serie['dias'] as $dia ) {
	$tope = max( $tope, $dia['n'] );
}

$page_title = __( 'Inicio', 'caaguazu-portal' );
$body = function () use ( $identity, $pulse, $serie, $tope, $can_draft, $can_review ) {
	?>
	<div class="promotur-pagehead">
		<div>
			<div class="promotur-eyebrow"><?php esc_html_e( 'Tu actividad de hoy', 'caaguazu-portal' ); ?></div>
			<h2 class="promotur-h2"><?php
				/* translators: %s = nombre */
				printf( esc_html__( 'Hola, %s 👋', 'caaguazu-portal' ), esc_html( $identity['display_name'] ) );
			?></h2>
		</div>
	</div>

	<?php if ( ! empty( $pulse ) ) : ?>
		<div class="promotur-grid promotur-grid--3">
			<?php foreach ( $pulse as $p ) : ?>
				<a class="promotur-card promotur-card--link promotur-stat" href="<?php echo esc_url( promotur_url( $p['url'] ) ); ?>">
					<span class="promotur-stat__head">
						<span class="promotur-stat__label"><?php echo esc_html( $p['label'] ); ?></span>
						<span class="promotur-stat__icon"><?php echo promotur_icon( $p['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</span>
					<span class="promotur-stat__n"><?php echo esc_html( number_format_i18n( $p['n'] ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="promotur-card promotur-mt">
		<div class="promotur-card__head">
			<?php echo promotur_icon( 'chart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Actividad reciente', 'caaguazu-portal' ); ?></span>
		</div>
		<div class="promotur-stat__foot">
			<span class="promotur-stat__n"><?php echo esc_html( number_format_i18n( $serie['total'] ) ); ?></span>
			<span class="promotur-spark" role="img"
			      aria-label="<?php echo esc_attr( sprintf( /* translators: %d = cantidad de días */ _n( 'Actividad de los últimos %d día', 'Actividad de los últimos %d días', count( $serie['dias'] ), 'caaguazu-portal' ), count( $serie['dias'] ) ) ); ?>">
				<?php
				$hoy = count( $serie['dias'] ) - 1;
				foreach ( $serie['dias'] as $i => $dia ) :
					// Altura proporcional al día más movido de la ventana. Un
					// día sin actividad queda como un tope de 2px (el mínimo
					// del CSS): se ve que ese día existe y que fue cero.
					$alto = $tope > 0 ? round( 36 * $dia['n'] / $tope ) : 0;
					?>
					<span class="promotur-spark__bar<?php echo $i === $hoy ? ' is-on' : ''; ?>" style="height:<?php echo esc_attr( $alto ); ?>px"></span>
				<?php endforeach; ?>
			</span>
		</div>
	</div>

	<h3 class="promotur-h3"><?php esc_html_e( 'Accesos rápidos', 'caaguazu-portal' ); ?></h3>
	<div class="promotur-grid promotur-grid--3">
		<?php
		$quick = array();
		if ( caaguazu_account_can( 'promotor', 'promotur_edit_destino' ) ) {
			$quick[] = array( 'icon' => 'edit', 'label' => __( 'Crear una ficha', 'caaguazu-portal' ), 'url' => 'panel/editor' );
		}
		if ( $can_draft ) {
			$quick[] = array( 'icon' => 'doc', 'label' => __( 'Mis contenidos', 'caaguazu-portal' ), 'url' => 'panel/mis-contenidos' );
		}
		if ( $can_review ) {
			$quick[] = array( 'icon' => 'inbox', 'label' => __( 'Cola de revisión', 'caaguazu-portal' ), 'url' => 'panel/revision' );
		}
		if ( caaguazu_account_can( 'promotor', 'promotur_manage_team' ) ) {
			$quick[] = array( 'icon' => 'team', 'label' => __( 'Equipo', 'caaguazu-portal' ), 'url' => 'panel/equipo' );
		}
		$quick[] = array( 'icon' => 'user', 'label' => __( 'Mi perfil', 'caaguazu-portal' ), 'url' => 'panel/perfil' );
		foreach ( $quick as $qk ) :
			?>
			<a class="promotur-quick" href="<?php echo esc_url( promotur_url( $qk['url'] ) ); ?>">
				<?php echo promotur_icon( $qk['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php echo esc_html( $qk['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/shell.php';
