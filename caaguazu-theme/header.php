<?php
/**
 * Header del theme.
 *
 * @package Caaguazu
 */

$current_slug = caaguazu_current_page_slug();
$is_home      = caaguazu_is_home();
$current_eco  = caaguazu_current_ecosystem();
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

<?php if ( is_front_page() ) : ?>
	<?php /* Splash de entrada (estilo intro del sub-portal CEAD): el lapacho se
	   dibuja, florece, aparece el wordmark y el telón se levanta. Solo en la home,
	   una vez por sesión de navegador, clickeable para saltear. El script
	   inline corre ANTES del primer paint (es sincrónico dentro del body),
	   así no hay flash de contenido ni de splash indebido; sin JS el div
	   queda hidden y no molesta. Estilos en assets/css/animations.css. */ ?>
	<div class="cgz-splash" id="cgzSplash" hidden aria-hidden="true">
		<svg viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<circle class="ring" cx="48" cy="48" r="44" pathLength="100"/>
			<?php /* Lapacho: copa de tres lóbulos + tronco + floración — mismo
			   dibujo que el ícono 'tree' de inc/icons.php, redibujado a mano
			   acá porque necesita el trazado pathLength para la animación de
			   dibujado (inc/icons.php devuelve el <svg> ya armado, no sirve
			   para inyectar acá). Si se retoca el ícono, retocar también este. */ ?>
			<g transform="translate(13.2,12.6) scale(2.9)">
				<circle class="glyph" cx="8" cy="11" r="4.3" pathLength="100"/>
				<circle class="glyph" cx="16" cy="11" r="4.3" pathLength="100"/>
				<circle class="glyph" cx="12" cy="8" r="4.6" pathLength="100"/>
				<line class="glyph" x1="12" y1="15.2" x2="12" y2="21" pathLength="100"/>
				<circle class="bloom" cx="8.6" cy="9.2" r=".9" fill="currentColor" stroke="none"/>
				<circle class="bloom" cx="15.2" cy="8.6" r=".9" fill="currentColor" stroke="none"/>
				<circle class="bloom" cx="12" cy="12.6" r=".9" fill="currentColor" stroke="none"/>
			</g>
		</svg>
		<span class="name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		<span class="tag"><?php esc_html_e( 'Sitio web · No oficial', 'caaguazu' ); ?></span>
	</div>
	<script>
	(function () {
		var s = document.getElementById('cgzSplash');
		try {
			if (sessionStorage.getItem('cgzSplashSeen') ||
				window.matchMedia('(prefers-reduced-motion: reduce)').matches) { s.remove(); return; }
			sessionStorage.setItem('cgzSplashSeen', '1');
		} catch (e) { s.remove(); return; }
		s.hidden = false;
		document.documentElement.classList.add('cgz-splashing'); // pausa el hero-fade de abajo
		function out() {
			if (!s.parentNode) { return; }
			document.documentElement.classList.remove('cgz-splashing');
			s.classList.add('out');
			setTimeout(function () { s.remove(); }, 500);
		}
		s.addEventListener('click', out);
		setTimeout(out, 2300);
	})();
	</script>
<?php endif; ?>

<?php if ( $current_eco ) : ?>
	<?php caaguazu_render_ecosystem_header( $current_eco ); ?>
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

<?php if ( $current_eco ) : ?>
	<?php caaguazu_render_ecosystem_tabbar( $current_eco ); ?>
<?php else : ?>
	<?php caaguazu_render_tabbar( $current_slug ); ?>
<?php endif; ?>

<main id="main">
