<?php
/**
 * Layout de auth: centrado, sin sidebar/topbar. Comparte el <head> del panel.
 * Recibe $page_title y $body (closure).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$page_title = isset( $page_title ) ? $page_title : __( 'Acceso', 'caaguazu-portal' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="">
<head>
<?php include __DIR__ . '/partials/head.php'; ?>
</head>
<body <?php body_class( 'promotur-app promotur-auth-page' ); ?>>
<?php wp_body_open(); ?>
	<main class="promotur-auth">
		<div class="promotur-auth__card">
			<a class="promotur-brand promotur-brand--auth" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="promotur-brand__mark" aria-hidden="true"><?php echo promotur_icon( 'marca' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<span class="promotur-brand__text"><?php bloginfo( 'name' ); ?></span>
			</a>
			<?php
			if ( isset( $body ) && is_callable( $body ) ) {
				$body();
			}
			?>
		</div>
	</main>
<?php wp_footer(); ?>
</body>
</html>
