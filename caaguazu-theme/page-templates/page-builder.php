<?php
/**
 * Template Name: Página en blanco (editor visual)
 *
 * Plantilla "canvas" para editores visuales tipo Brizy: sin el hero/
 * breadcrumb que fuerza page.php, y sin envolver el contenido en
 * `.container` (tope 1200px) ni `.entry-content` (tope 760px + tipografía
 * propia del theme) — esos dos anchos son justamente lo que le rompe a un
 * builder sus secciones a sangre completa. Acá `the_content()` cae
 * directo dentro de `#main`, que no tiene padding ni ancho máximo propio,
 * así que lo que se diseñe en el builder ocupa el 100% del viewport tal
 * como se ve en su editor.
 *
 * El header/tabbar/footer institucionales se mantienen (así la página
 * sigue siendo parte navegable del sitio) — si en algún momento hace
 * falta un canvas total sin chrome, sacar los get_header()/get_footer().
 *
 * Elegible desde el editor de la página: Atributos de página → Plantilla.
 * Con Brizy activo, cualquier página (con esta plantilla o no) se edita
 * igual desde el botón "Edit with Brizy" — la plantilla solo decide qué
 * envoltorio usa el resultado en el front-end.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();
