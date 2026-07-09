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
			<?php /* Lapacho de verdad, no un ícono genérico de árbol (ese vive
			   aparte en inc/icons.php, clave 'tree', y puede seguir siendo
			   simple — se usa en navegación, no necesita ser botánicamente
			   preciso). Acá sí: tronco que crece primero (trazo con pathLength,
			   igual que el anillo) y encima una copa de 5 círculos rosados
			   superpuestos —sin trazo, solo relleno— que "florecen" con un
			   rebote escalonado. El rosa (no dorado) es lo que la hace leerse
			   como lapacho en flor y no como una silueta abstracta de rama. */ ?>
			<g transform="translate(16.8,16.3) scale(2.6)">
				<line class="glyph" x1="12" y1="15.8" x2="12" y2="21.5" pathLength="100"/>
				<circle class="canopy" cx="7.5" cy="12" r="4.6"/>
				<circle class="canopy" cx="16.5" cy="12" r="4.6"/>
				<circle class="canopy" cx="12" cy="10" r="5.6"/>
				<circle class="canopy" cx="8.8" cy="6.3" r="3.4"/>
				<circle class="canopy" cx="15.2" cy="6.3" r="3.4"/>
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
		setTimeout(out, 2600);
	})();
	</script>
<?php endif; ?>

<?php
/**
 * Si Elementor Pro (Theme Builder) tiene un header propio asignado y
 * activo para esta página, `elementor_theme_do_location()` ya lo imprimió
 * y devuelve true — el theme no agrega el suyo encima. `function_exists()`
 * hace que esto no cambie nada si Elementor Pro no está instalado (la
 * inmensa mayoría de los sitios): sigue exactamente el header de siempre.
 */
$elementor_header_done = function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' );
?>
<?php if ( $elementor_header_done ) : ?>
	<?php // Header servido por el Theme Builder de Elementor Pro — nada más que hacer acá. ?>
<?php elseif ( $current_eco ) : ?>
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
