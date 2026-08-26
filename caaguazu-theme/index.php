<?php
/**
 * Plantilla única del theme.
 *
 * El theme no tiene ninguna otra plantilla, así que la jerarquía de
 * WordPress cae acá para cualquier URL del sitio (home, entradas, páginas,
 * archivos, búsqueda, 404): mientras dure la obra, todo el frontend muestra
 * esta misma página.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#e9ecfb">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<main class="obra">

		<div class="tarjeta">

			<header class="tarjeta__cabecera">
				<span class="marca">
					<?php caaguazu_marca(); ?>
					caaguazu.net
				</span>
				<span class="estado"><?php esc_html_e( 'En construcción', 'caaguazu' ); ?></span>
			</header>

			<div class="tarjeta__cuerpo">

				<div class="tarjeta__texto">
					<h1 class="titulo">
						caaguazu.net
						<span><?php esc_html_e( 'está siendo construida', 'caaguazu' ); ?></span>
					</h1>

					<p class="texto">
						<?php esc_html_e( 'Estamos rehaciendo el portal desde cero: más simple, más rápido y sin relleno. Lo primero en volver va a ser el panel de promotores turísticos.', 'caaguazu' ); ?>
					</p>
				</div>

				<div class="ilustracion">
					<?php caaguazu_ilustracion(); ?>
				</div>

			</div>

		</div>

		<p class="pie"><?php esc_html_e( 'Portal del Departamento de Caaguazú · Paraguay', 'caaguazu' ); ?></p>

	</main>

	<?php wp_footer(); ?>
</body>
</html>
