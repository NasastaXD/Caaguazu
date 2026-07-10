<?php
/**
 * Header del theme.
 *
 * @package Caaguazu
 */

$current_slug = caaguazu_current_page_slug();
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
	<?php /* Splash de entrada — animación de logo "brote" (calcada de una
	   referencia en video aportada por quien encargó el sitio, fotograma a
	   fotograma): (1) ocho hojitas aparecen chicas en órbita alrededor del
	   centro, como un spinner, (2) crecen hacia adentro hasta formar una
	   roseta simétrica en dos tonos de verde, (3) la roseta se repliega y en
	   su lugar brotan DOS hojas grandes (el brote final, una de cada tono),
	   (4) debajo se escribe "Caaguazú.net" en cursiva (Playfair Display
	   italic) con un barrido de revelado izquierda→derecha estilo escritura,
	   y (5) el tagline aparece suave antes de que el panel entero se levante
	   en barrido revelando el hero. La línea de progreso de abajo marca la
	   duración (y hace de borde dorado del panel al levantarse). Solo en la
	   home, una vez por sesión de navegador, clickeable para saltear. El
	   script inline corre ANTES del primer paint (es sincrónico dentro del
	   body), así no hay flash de contenido ni de splash indebido; sin JS el
	   div queda hidden y no molesta. Estilos en assets/css/animations.css. */ ?>
	<div class="cgz-splash" id="cgzSplash" hidden aria-hidden="true">
		<div class="cgz-splash-inner">
			<div class="cgz-splash-bloom" aria-hidden="true">
				<svg viewBox="0 0 120 120">
					<?php /* Roseta: 8 pétalos-hoja (base en el centro, punta hacia
					   afuera), alternando dos tonos. --i escalona la entrada. */ ?>
					<g class="rosette">
						<?php for ( $i = 0; $i < 8; $i++ ) : ?>
							<g class="petal" style="--i:<?php echo (int) $i; ?>" transform="rotate(<?php echo (int) ( $i * 45 ); ?> 60 60)">
								<path class="leaf <?php echo $i % 2 ? 'dark' : 'light'; ?>" d="M60 56 C52 46 52 30 60 22 C68 30 68 46 60 56 Z"/>
							</g>
						<?php endfor; ?>
					</g>
					<?php /* Brote final: dos hojas grandes (una de cada tono) que
					   crecen desde la base cuando la roseta se repliega. */ ?>
					<g class="sprout">
						<path class="leaf light sl" d="M58 92 C36 84 28 60 40 42 C62 48 70 74 58 92 Z"/>
						<path class="leaf dark sr" d="M62 92 C84 84 92 60 80 42 C58 48 50 74 62 92 Z"/>
					</g>
				</svg>
			</div>
			<span class="script">Caaguazú.net</span>
			<span class="tag"><?php esc_html_e( 'Sitio web · No oficial', 'caaguazu' ); ?></span>
		</div>
		<span class="cgz-splash-progress" aria-hidden="true"></span>
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
			setTimeout(function () { s.remove(); }, 450);
		}
		s.addEventListener('click', out);
		setTimeout(out, 3000);
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
	<header class="header solid" id="header">
		<?php /* Barra de utilidad cívica (solo desde tablet, ver "Header
		   transformation" del plan V2) — reusa las mismas páginas/URLs que ya
		   enlaza el footer, así no hay una segunda fuente de verdad para
		   "dónde está Accesibilidad/Contacto/Mapa del sitio". */ ?>
		<div class="util-bar">
			<div class="util-bar-inner">
				<span class="util-tag"><?php esc_html_e( 'Portal ciudadano de Caaguazú', 'caaguazu' ); ?></span>
				<nav class="util-links" aria-label="<?php esc_attr_e( 'Enlaces de utilidad', 'caaguazu' ); ?>">
					<a href="<?php echo esc_url( caaguazu_page_url( 'sobre-caaguazu' ) ); ?>"><?php esc_html_e( 'Accesibilidad', 'caaguazu' ); ?></a>
					<a href="<?php echo esc_url( caaguazu_page_url( 'contacto' ) ); ?>"><?php esc_html_e( 'Contacto', 'caaguazu' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Mapa del sitio', 'caaguazu' ); ?></a>
				</nav>
			</div>
		</div>
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
		<?php /* El selector ES/GN/EN del header (.header-actions .lang) se oculta
		   en mobile (@media min-width:768px en main.css) por falta de espacio en
		   la barra — esta copia en el drawer es la forma de llegar a él en
		   celular, que es donde vive la mayoría de las visitas. Mismo markup
		   (misma clase .lang y data-lang), así el JS de assets/js/main.js lo
		   detecta y mantiene ambas copias sincronizadas sin código aparte. */ ?>
		<div class="lang drawer-lang" role="group" aria-label="<?php esc_attr_e( 'Idioma', 'caaguazu' ); ?>">
			<button class="on" data-lang="ES">ES</button>
			<button data-lang="GN">GN</button>
			<button data-lang="EN" disabled title="<?php esc_attr_e( 'Próximamente', 'caaguazu' ); ?>">EN</button>
		</div>
	</aside>
<?php endif; ?>

<?php if ( $current_eco ) : ?>
	<?php caaguazu_render_ecosystem_tabbar( $current_eco ); ?>
<?php else : ?>
	<?php caaguazu_render_tabbar( $current_slug ); ?>
<?php endif; ?>

<main id="main">
