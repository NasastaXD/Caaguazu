<?php
/**
 * Defaults del Customizer — espejo de $IDENTITY, $ECOSYSTEM, $AUDIENCES
 * del data.php original. Se usan tanto al registrar settings como al
 * renderizar (si por alguna razón el setting está vacío).
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_identity_defaults() {
	return array(
		array(
			'eyebrow' => 'Geografía',
			'title'   => 'Territorio y superficie forestal',
			'body'    => 'Caaguazú tiene una superficie de 11.474 km², con mesetas, ríos y reservas naturales en el centro-este del Paraguay. Es uno de los departamentos con mayor superficie forestal del país.',
			'image'   => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'eyebrow' => 'Historia',
			'title'   => 'Capital paraguaya de la madera',
			'body'    => "El nombre Caaguazú proviene del guaraní ka'aguasu, que significa monte grande. Desde su fundación, la actividad maderera fue la base económica del departamento y hoy define su identidad productiva.",
			'image'   => 'https://images.unsplash.com/photo-1605092676920-8ac5ae40c7c8?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'eyebrow' => 'Población',
			'title'   => 'Una comunidad bilingüe',
			'body'    => 'Caaguazú tiene más de 550.000 habitantes. La mayoría se comunica en español y guaraní por igual, lo que hace del bilingüismo una característica central de la vida cotidiana del departamento.',
			'image'   => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=1400&q=80',
		),
	);
}

/* caaguazu_ecosystem_defaults() ahora vive en el plugin Caaguazú Módulos
   (caaguazu-modulos/includes/modules/module-ecosistema.php). */

