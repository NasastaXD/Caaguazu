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

function caaguazu_ecosystem_defaults() {
	return array(
		array(
			'tag'   => 'Turismo',
			'title' => 'Turismo',
			'body'  => 'Información sobre historia, oficio maderero, gastronomía y cultura guaraní del departamento.',
			'cta'   => 'Ver sección de Turismo',
			'url'   => caaguazu_page_url( 'turismo' ),
			'image' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'tag'   => 'cead.caaguazu.net',
			'title' => 'Centro de Estudios y Desarrollo',
			'body'  => 'Sub-portal dedicado a investigación, formación y proyectos de desarrollo sostenible para el departamento.',
			'cta'   => 'Ir al sitio del CEAD',
			'url'   => 'https://cead.caaguazu.net',
			'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'tag'   => 'Próximamente',
			'title' => 'Nuevo sub-portal',
			'body'  => 'Un nuevo espacio del ecosistema Caaguazú se encuentra en preparación y estará disponible próximamente.',
			'cta'   => 'Próximamente',
			'url'   => '',
			'image' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1400&q=80',
		),
	);
}

function caaguazu_audiences_defaults() {
	return array(
		array( 'icon' => '👥',  'title' => 'Ciudadanos', 'body' => 'Información sobre trámites, servicios municipales y vida local.', 'cta' => 'Ver servicios',         'slug' => 'servicios' ),
		array( 'icon' => '💼',  'title' => 'Empresas',   'body' => 'Información sobre inversión, licencias y oportunidades de negocio.', 'cta' => 'Ver información para empresas', 'slug' => 'servicios' ),
		array( 'icon' => '🏞️', 'title' => 'Visitantes', 'body' => 'Información para planificar una visita a Caaguazú.',            'cta' => 'Planificar visita',      'slug' => 'ecosistema' ),
	);
}
