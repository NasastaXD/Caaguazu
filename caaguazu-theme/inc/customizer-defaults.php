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
			'title'   => 'Donde el bosque define el territorio',
			'body'    => '11.474 km² de mesetas, ríos y reservas en el corazón oriental del Paraguay. Caaguazú concentra una de las superficies forestales más significativas del país.',
			'image'   => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'eyebrow' => 'Historia',
			'title'   => 'Capital paraguaya de la madera',
			'body'    => "El nombre Caaguazú —ka'aguasu en guaraní— significa monte grande. Tres siglos de oficios alrededor de la madera definieron una identidad productiva única.",
			'image'   => 'https://images.unsplash.com/photo-1605092676920-8ac5ae40c7c8?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'eyebrow' => 'Gente',
			'title'   => 'Una comunidad con raíces y futuro',
			'body'    => 'Más de 550.000 caaguaceños hablan español y guaraní en igual medida. Una identidad bilingüe que vincula campo, ciudad y diáspora.',
			'image'   => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=1400&q=80',
		),
	);
}

function caaguazu_ecosystem_defaults() {
	return array(
		array(
			'tag'   => 'Turismo',
			'title' => 'Turismo y Eco-aventura',
			'body'  => 'Historia, oficio maderero, gastronomía y cultura guaraní. La Capital de la Madera del Paraguay.',
			'cta'   => 'Explorar Turismo',
			'url'   => caaguazu_page_url( 'turismo' ),
			'image' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'tag'   => 'cead.caaguazu.net',
			'title' => 'Centro de Estudios y Desarrollo',
			'body'  => 'Investigación, formación y proyectos para el desarrollo sostenible del departamento.',
			'cta'   => 'Conocer el CEAD',
			'url'   => 'https://cead.caaguazu.net',
			'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1400&q=80',
		),
		array(
			'tag'   => 'próximamente',
			'title' => 'Nuevo sub-portal',
			'body'  => 'Un nuevo espacio del ecosistema Caaguazú está en preparación. Pronto disponible.',
			'cta'   => 'Próximamente',
			'url'   => '',
			'image' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1400&q=80',
		),
	);
}

function caaguazu_audiences_defaults() {
	return array(
		array( 'icon' => '👥',  'title' => 'Ciudadanos', 'body' => 'Trámites, servicios y vida local.',     'cta' => 'Para vos',        'slug' => 'servicios' ),
		array( 'icon' => '💼',  'title' => 'Empresas',   'body' => 'Inversión, licencias y oportunidades.', 'cta' => 'Para tu negocio', 'slug' => 'servicios' ),
		array( 'icon' => '🏞️', 'title' => 'Visitantes', 'body' => 'Planificá tu visita a Caaguazú.',       'cta' => 'Para tu viaje',   'slug' => 'ecosistema' ),
	);
}
