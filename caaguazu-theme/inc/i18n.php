<?php
/**
 * Selector de idioma ES/GN funcional para strings fijas de interfaz.
 *
 * Estrategia cache-safe: el HTML servido es SIEMPRE idéntico (se imprimen
 * ambos idiomas, uno oculto con CSS); el toggle ocurre 100% en el navegador
 * (clase lang-gn en <html>, persistida en localStorage). No se traduce
 * contenido editable vía Customizer ni contenido de páginas/noticias/turismo
 * (texto libre — requeriría UI de traducción por campo, fuera de este scope).
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_i18n_dict() {
	return array(
		// Nav de fallback (helpers.php)
		'nav.sobre-caaguazu' => 'Caaguazu rehegua',
		'nav.servicios'      => 'Mba\'apo rembiapokue',
		'nav.noticias'       => 'Marandu',
		'nav.transparencia'  => 'Jehecharamby',
		'nav.turismo'        => 'Jehasa rape',
		'nav.ecosistema'     => 'Ecosistema',
		'nav.contacto'       => 'Ñemongeta',

		// Header / drawer
		'header.buscar' => 'Ejuhu',
		'header.skip'   => 'Eho tetepýpe',

		// Caaguazú en números (home)
		'stats.years'     => 'ary tembiasakue',
		'stats.sawmills'  => 'aserradero omba\'apóva',
		'stats.workshops' => 'carpintería-kuéra',
		'stats.families'  => 'família yvyra rehegua',

		// Footer
		'footer.subportales'   => 'Sub-portal-kuéra',
		'footer.servicios'     => 'Mba\'apo rembiapokue',
		'footer.institucional' => 'Institucional',
		'footer.contacto'      => 'Ñemongeta',

		// Búsqueda
		'search.chip.todos'     => 'Opavave',
		'search.chip.paginas'   => 'Kuatiarogue',
		'search.chip.noticias'  => 'Marandu',
		'search.placeholder'    => 'Eheka kuatiarogue ha marandu…',

		// Quiz del home
		'quiz.eyebrow' => 'Ore roipytyvõ ndeve',
		'quiz.title'   => 'Mba\'e nde ru Caaguazú-pe ko árape?',
		'quiz.intro'   => 'Embohovái peteĩ porandu ha roheka mo\'ã ndéve mamo eñepyrũ vaerã.',
		'quiz.opt.resident' => 'Che aiko ko\'ape',
		'quiz.opt.visitor'  => 'Aju ahecha',
		'quiz.opt.investor' => 'Aheka mba\'apo',
		'quiz.opt.student'  => 'Che temimbo\'e',
		'quiz.opt.other'    => 'Ambue',
		'quiz.result.resident.title'      => 'Nde oikóva ko\'ápe g̃uarã',
		'quiz.result.resident.primary'    => 'Ehecha mba\'apo rembiapokue',
		'quiz.result.resident.secondary'  => 'Ehecha jehecharamby',
		'quiz.result.visitor.title'       => 'Nde aju ahecha g̃uarã',
		'quiz.result.visitor.primary'     => 'Ehecha jehasa rape',
		'quiz.result.visitor.secondary'   => 'Ehecha ecosistema',
		'quiz.result.investor.title'      => 'Emba\'apo hag̃ua ko departamento-pe',
		'quiz.result.investor.primary'    => 'Ehecha mba\'apo rembiapokue',
		'quiz.result.investor.secondary'  => 'Ehecha ecosistema',
		'quiz.result.student.title'       => 'Nde temimbo\'e g̃uarã',
		'quiz.result.student.primary'     => 'Ehecha ecosistema',
		'quiz.result.student.secondary'   => 'Ñemongeta orendive',
		'quiz.result.other.title'         => 'Emombe\'u oréve remba\'e potáva',
		'quiz.result.other.primary'       => 'Eho ñemongeta-pe',
		'quiz.result.other.secondary'     => 'Eheka ko tenda-pe',

		// Formulario de reporte
		'report.hero.eyebrow'    => 'Tetãygua ñemongeta',
		'report.hero.sub'        => 'Yvy renonde\'ỹva jejavy, tataindy térã ambue mba\'e vai. Emombe\'u oréve mba\'épa oiko ha mamo.',
		'report.success'         => 'Aguyje! Rombohovái ne remimongeta ha oñemaña mo\'ã hese pe equipo.',
		'report.error'           => 'Ndaikatúi roiko katu ne remimongeta. Emyatyrõ umi campo ha eha\'ãjey.',
		'report.field.category'  => 'Mba\'eichagua',
		'report.field.location'  => 'Mamópa oiko (tape, barrio térã referencia)',
		'report.field.description'=> 'Emombe\'u mba\'épa oiko',
		'report.field.name'      => 'Réra (ndaha\'éi obligatorio)',
		'report.field.email'     => 'Email (ndaha\'éi obligatorio)',
		'report.field.phone'     => 'Telefono (ndaha\'éi obligatorio)',
		'report.submit'          => 'Emondo remimongeta',

		// Formulario de contacto
		'contact.success'        => 'Aguyje ore rembijerure! Rombohovái mo\'ã ndéve ko\'ẽrõ ha ambue.',
		'contact.error'          => 'Ndaikatúi romondo ne remimongeta. Emyatyrõ umi campo ha eha\'ãjey.',
		'contact.field.name'     => 'Réra',
		'contact.field.email'    => 'Email',
		'contact.field.subject'  => 'Mba\'erehepa',
		'contact.field.message'  => 'Ne remimongeta',
		'contact.submit'         => 'Emondo ne remimongeta',
	);
}

/**
 * Imprime ambos idiomas en el HTML (uno oculto vía CSS); el toggle real
 * ocurre en el navegador. $fallback_es se usa si la clave no está en el
 * diccionario, para no dejar texto vacío.
 */
function caaguazu_i18n( $key, $fallback_es = '' ) {
	echo caaguazu_i18n_html( $key, $fallback_es );
}

function caaguazu_i18n_html( $key, $fallback_es = '' ) {
	$dict = caaguazu_i18n_dict();
	$gn   = isset( $dict[ $key ] ) ? $dict[ $key ] : $fallback_es;
	return sprintf(
		'<span class="i18n-es">%s</span><span class="i18n-gn" hidden>%s</span>',
		esc_html( $fallback_es ),
		esc_html( $gn )
	);
}

/**
 * Variante para usar dentro de un mapa JSON (JS), donde no se puede
 * imprimir HTML directo: devuelve array [es, gn].
 */
function caaguazu_i18n_pair( $key, $fallback_es = '' ) {
	$dict = caaguazu_i18n_dict();
	return array(
		'es' => $fallback_es,
		'gn' => isset( $dict[ $key ] ) ? $dict[ $key ] : $fallback_es,
	);
}
