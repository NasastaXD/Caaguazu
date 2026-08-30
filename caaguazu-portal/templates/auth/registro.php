<?php
/**
 * Registro INVITE-ONLY. Recibe: $error, $next, $token, $invite_status,
 * $invite_role, $invite_role_key, $invite_vence.
 *
 * La pantalla explica el proceso en vez de mostrar cuatro casillas sueltas.
 * Quien llega acá casi siempre es alguien que abre un enlace que le pasaron
 * por WhatsApp, no sabe qué es este panel, y tiene que decidir si le da su
 * correo y su teléfono a un sitio que nunca vio. Un formulario mudo, en ese
 * contexto, se abandona.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$error           = isset( $error ) ? $error : '';
$next            = isset( $next ) ? $next : '';
$token           = isset( $token ) ? $token : '';
$invite_status   = isset( $invite_status ) ? $invite_status : 'invalid';
$invite_role     = isset( $invite_role ) ? $invite_role : '';
$invite_role_key = isset( $invite_role_key ) ? $invite_role_key : '';
$invite_vence    = isset( $invite_vence ) ? $invite_vence : '';
$is_valid        = ( 'valid' === $invite_status );

$page_title = __( 'Crear cuenta', 'caaguazu-portal' );
$body = function () use ( $error, $next, $token, $invite_status, $invite_role, $invite_role_key, $invite_vence, $is_valid ) {
	?>
	<h1 class="promotur-auth__title"><?php esc_html_e( 'Crear tu cuenta', 'caaguazu-portal' ); ?></h1>
	<p class="promotur-auth__sub"><?php esc_html_e( 'Del Portal de Promotores Turísticos de Caaguazú: acá el equipo escribe lo que después muestra la app de turismo.', 'caaguazu-portal' ); ?></p>

	<?php if ( $error ) : ?>
		<div class="promotur-notice promotur-notice--error"><?php echo esc_html( $error ); ?></div>
	<?php endif; ?>

	<?php if ( ! $is_valid ) : ?>
		<div class="promotur-notice promotur-notice--info">
			<?php
			switch ( $invite_status ) {
				case 'agotada':
					esc_html_e( 'Este enlace ya se usó las veces que tenía permitidas. Pedí uno nuevo a quien te invitó.', 'caaguazu-portal' );
					break;
				case 'expired':
					esc_html_e( 'Este enlace venció. Pedí uno nuevo a quien te invitó.', 'caaguazu-portal' );
					break;
				case 'revoked':
					esc_html_e( 'Este enlace fue dado de baja. Pedí uno nuevo a quien te invitó.', 'caaguazu-portal' );
					break;
				default:
					esc_html_e( 'Para crear una cuenta hace falta un enlace de invitación. Lo genera un Profesor desde el panel, en Equipo; pediselo a quien te sumó al curso.', 'caaguazu-portal' );
			}
			?>
		</div>
		<div class="promotur-auth__links">
			<a href="<?php echo esc_url( promotur_url( 'login' ) ); ?>"><?php esc_html_e( 'Ya tengo una cuenta', 'caaguazu-portal' ); ?></a>
		</div>
		<?php
		return;
	endif; ?>

	<?php
	/*
	 * El <span> no es decorativo: .promotur-notice es un flex, así que un
	 * <strong> suelto junto al texto cuenta como OTRO ítem del flex y se
	 * separa —el rol terminaba flotando al costado, fuera de la frase—.
	 * Envuelto, la frase entera es un solo ítem y se lee de corrido.
	 */
	?>
	<div class="promotur-notice promotur-notice--success">
		<span>
			<?php
			/* translators: %s = rol con el que entra (Profesor, Alumno…) */
			printf( esc_html__( 'Tu invitación es válida. Vas a entrar como %s.', 'caaguazu-portal' ), '<strong>' . esc_html( $invite_role ) . '</strong>' ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</span>
	</div>

	<?php
	/*
	 * Qué va a poder hacer, en concreto. Sale del rol y no de un texto fijo
	 * porque es la diferencia que más importa entre los dos: un Profesor
	 * publica solo; un Alumno escribe y otro revisa. Enterarse de eso después
	 * de cargar una ficha entera es la peor forma de enterarse.
	 */
	$que_puede = array(
		'promotur_promotor' => __( 'Como Profesor vas a poder cargar y publicar contenido sin que lo revise nadie, revisar lo que cargan los Alumnos, y sumar gente al equipo.', 'caaguazu-portal' ),
		'promotur_mini'     => __( 'Como Alumno vas a poder cargar fichas, artículos y recorridos. Lo que escribas lo revisa un Profesor antes de que salga en la app.', 'caaguazu-portal' ),
	);
	if ( isset( $que_puede[ $invite_role_key ] ) ) : ?>
		<p class="promotur-muted"><?php echo esc_html( $que_puede[ $invite_role_key ] ); ?></p>
	<?php endif; ?>

	<h2 class="promotur-h3"><?php esc_html_e( 'Cómo sigue', 'caaguazu-portal' ); ?></h2>
	<ol class="promotur-pasos">
		<li><?php esc_html_e( 'Completás estos cuatro datos.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'Entrás al panel al instante: no hay que esperar que nadie apruebe nada.', 'caaguazu-portal' ); ?></li>
		<li><?php esc_html_e( 'De ahí en más entrás con tu correo y tu contraseña. Este enlace no se usa más.', 'caaguazu-portal' ); ?></li>
	</ol>

	<form class="promotur-form" method="post">
		<?php PROMOTUR_Acciones::campos( 'promotur_registro' ); ?>
		<input type="hidden" name="promotur_auth" value="registro">
		<input type="hidden" name="next" value="<?php echo esc_attr( $next ); ?>">
		<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">

		<label class="promotur-field">
			<span><?php esc_html_e( 'Tu nombre', 'caaguazu-portal' ); ?></span>
			<input type="text" name="user_login" autocomplete="name" required autofocus>
			<small class="promotur-ayuda"><?php esc_html_e( 'Como querés que te vean en el equipo, y como se firma lo que publiques. Podés cambiarlo después.', 'caaguazu-portal' ); ?></small>
		</label>
		<label class="promotur-field">
			<span><?php esc_html_e( 'Correo', 'caaguazu-portal' ); ?></span>
			<input type="email" name="email" autocomplete="email" required>
			<small class="promotur-ayuda"><?php esc_html_e( 'Con este correo vas a entrar de acá en adelante. Usá uno al que entres de verdad: es por donde se recupera la contraseña.', 'caaguazu-portal' ); ?></small>
		</label>
		<label class="promotur-field">
			<span><?php esc_html_e( 'Teléfono', 'caaguazu-portal' ); ?></span>
			<input type="tel" name="phone" autocomplete="tel" inputmode="tel" required placeholder="<?php esc_attr_e( 'Ej.: 0981 123 456', 'caaguazu-portal' ); ?>">
			<small class="promotur-ayuda"><?php esc_html_e( 'Para que el equipo te pueda ubicar. No se publica en ningún lado ni sale en la app.', 'caaguazu-portal' ); ?></small>
		</label>
		<label class="promotur-field">
			<span><?php esc_html_e( 'Contraseña', 'caaguazu-portal' ); ?></span>
			<input type="password" name="user_pass" autocomplete="new-password" minlength="6" required>
			<small class="promotur-ayuda"><?php esc_html_e( 'Seis caracteres o más. Es nueva, no la de tu correo.', 'caaguazu-portal' ); ?></small>
		</label>

		<button type="submit" class="promotur-btn promotur-btn--primary promotur-btn--block"><?php esc_html_e( 'Crear cuenta y entrar', 'caaguazu-portal' ); ?></button>
	</form>

	<?php if ( $invite_vence ) : ?>
		<p class="promotur-ayuda">
			<?php
			/* translators: %s = fecha en que vence el enlace de invitación */
			printf( esc_html__( 'Este enlace sirve hasta el %s.', 'caaguazu-portal' ), esc_html( date_i18n( 'j \d\e F', strtotime( $invite_vence ) ) ) );
			?>
		</p>
	<?php endif; ?>

	<div class="promotur-auth__links">
		<a href="<?php echo esc_url( promotur_url( 'login' ) ); ?>"><?php esc_html_e( 'Ya tengo una cuenta', 'caaguazu-portal' ); ?></a>
	</div>
	<?php
};

include PROMOTUR_DIR . 'templates/auth-shell.php';
