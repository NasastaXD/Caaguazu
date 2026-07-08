<?php
/**
 * Header del theme.
 *
 * @package Caaguazu
 */

$current_slug = caaguazu_current_page_slug();
$is_home      = caaguazu_is_home();
$in_tourism   = caaguazu_is_tourism_context();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<script>(function(){try{if(localStorage.getItem('caaguazuLang')==='GN'){document.documentElement.classList.add('lang-gn');}}catch(e){}})();</script>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#1B6B44">
<link rel="manifest" href="<?php echo esc_url( home_url( '/caaguazu-manifest.webmanifest' ) ); ?>">
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/icon-180.png' ); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Caaguazú">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( $current_slug ); ?>">
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php caaguazu_i18n( 'header.skip', __( 'Saltar al contenido', 'caaguazu' ) ); ?></a>

<?php if ( $in_tourism ) : ?>
	<?php caaguazu_render_tourism_header( $current_slug ); ?>
<?php else : ?>
	<header class="header <?php echo $is_home ? '' : 'solid'; ?>" id="header">
		<div class="header-inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — <?php esc_attr_e( 'inicio', 'caaguazu' ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="logo-name"><?php bloginfo( 'name' ); ?></span>
					<span class="logo-tld">.net</span>
				<?php endif; ?>
			</a>

			<nav class="nav" aria-label="<?php esc_attr_e( 'Principal', 'caaguazu' ); ?>">
				<?php caaguazu_render_nav( 'primary', $current_slug ); ?>
			</nav>

			<div class="header-actions">
				<a href="<?php echo esc_url( get_search_link() ? get_search_link() : home_url( '/?s=' ) ); ?>" class="icon-btn" aria-label="<?php esc_attr_e( 'Buscar', 'caaguazu' ); ?>"><?php echo caaguazu_icon( 'search' ); ?></a>
				<div class="lang" role="group" aria-label="<?php esc_attr_e( 'Idioma', 'caaguazu' ); ?>">
					<button class="on" data-lang="ES">ES</button>
					<button data-lang="GN">GN</button>
					<button data-lang="EN" disabled title="<?php esc_attr_e( 'Próximamente', 'caaguazu' ); ?>">EN</button>
				</div>
				<button class="icon-btn burger" id="burger" aria-label="<?php esc_attr_e( 'Abrir menú', 'caaguazu' ); ?>"><?php echo caaguazu_icon( 'menu' ); ?></button>
			</div>
		</div>
	</header>

	<div class="drawer-bg" id="drawerBg"></div>
	<aside class="drawer" id="drawer" aria-hidden="true">
		<button class="close" id="drawerClose" aria-label="<?php esc_attr_e( 'Cerrar', 'caaguazu' ); ?>">×</button>
		<?php caaguazu_render_nav( 'mobile', $current_slug ); ?>
		<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php caaguazu_i18n( 'header.buscar', __( 'Buscar', 'caaguazu' ) ); ?></a>
	</aside>
<?php endif; ?>

<?php if ( $in_tourism ) : ?>
	<?php caaguazu_render_tourism_tabbar( $current_slug ); ?>
<?php else : ?>
	<?php caaguazu_render_tabbar( $current_slug ); ?>
<?php endif; ?>

<main id="main">
