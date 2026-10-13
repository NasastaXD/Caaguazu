<?php
/**
 * Arma el conocimiento de prueba de la presentación a partir de la
 * investigación (`caaguazu-portal/datos-prueba/sitios-caaguazu.json`).
 *
 *   php tools/generar-conocimiento-prueba.php            # lo muestra
 *   php tools/generar-conocimiento-prueba.php --escribir # lo deja en el plugin
 *
 * Es texto PLACEHOLDER: lo que dice la investigación, tal cual, sin redacción
 * propia. Lo que la investigación no trae queda marcado ‹así› para que el
 * equipo lo escriba. `verificar-asistente.php` falla si el archivo del plugin
 * se desactualiza respecto del JSON.
 */

const CZU_JSON_PRUEBA    = __DIR__ . '/../caaguazu-portal/datos-prueba/sitios-caaguazu.json';
const CZU_TXT_PRUEBA     = __DIR__ . '/../caaguazu-app-api/datos/conocimiento-prueba.txt';

function czu_conocimiento_de_prueba( $ruta_json = CZU_JSON_PRUEBA ) {
	$d = json_decode( (string) file_get_contents( $ruta_json ), true );
	if ( ! is_array( $d ) || empty( $d['sitios'] ) ) {
		throw new RuntimeException( 'El JSON de la investigación no se pudo leer.' );
	}

	$meses = array( 1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' );
	$linea = static function ( $s ) use ( $meses ) {
		$p = array( $s['titulo'] . ' (' . $s['categoria'] . ')' );
		$p[] = trim( $s['descripcion'] );
		if ( ! empty( $s['fechas'] ) ) {
			$f = $s['fechas']['recurrencia'] ?? '';
			$p[] = 'Fecha: ' . $f . ( empty( $s['fechas']['proxima_confirmada'] ) ? ' (la próxima fecha no está confirmada)' : '' ) . '.';
		}
		if ( ! empty( $s['direccion'] ) ) {
			$p[] = 'Dónde: ' . $s['direccion'] . ( ! empty( $s['compania_o_barrio'] ) ? ', ' . $s['compania_o_barrio'] : '' ) . '.';
		}
		if ( ! empty( $s['horario'] ) ) {
			$p[] = 'Horario: ' . $s['horario'] . '.';
		}
		if ( ! empty( $s['costo'] ) ) {
			$p[] = 'Precio: ' . $s['costo'] . '.';
		}
		if ( 'confirmada' !== ( $s['vigencia'] ?? '' ) ) {
			$p[] = '‹dato a confirmar›';
		}
		return '- ' . implode( ' ', $p );
	};

	$sitios  = array_filter( $d['sitios'], static function ( $s ) { return 'sitio' === $s['tipo']; } );
	$eventos = array_filter( $d['sitios'], static function ( $s ) { return 'evento' === $s['tipo']; } );

	$out   = array();
	$out[] = '[DATOS DE PRUEBA · reemplazar] Generado de la investigación del ' . $d['generado'] . '; precios y horarios de 2023-2024 salvo que se diga otra cosa.';
	$out[] = 'Distrito: Caaguazú, Paraguay. Fundado el 8 de mayo de 1845 por once familias junto a los manantiales de Ykua La Patria. ‹descripción general del distrito›';
	$out[] = 'Cómo llegar: por la Ruta PY02; terminal de ómnibus en la Av. Dr. Juan Manuel Frutos. ‹cómo llegar desde Asunción›';
	$out[] = 'Contacto de turismo: ‹contacto›';
	$out[] = '';
	$out[] = 'LUGARES';
	foreach ( $sitios as $s ) {
		$out[] = $linea( $s );
	}
	$out[] = '';
	$out[] = 'EVENTOS';
	foreach ( $eventos as $s ) {
		$out[] = $linea( $s );
	}
	$out[] = '';
	$out[] = 'LO QUE FALTA: ‹gastronomía›, ‹alojamiento urbano›, ‹colonias y comunidades que reciben visitas›.';
	return implode( "\n", $out ) . "\n";
}

if ( isset( $argv[0] ) && realpath( $argv[0] ) === __FILE__ ) {
	$texto = czu_conocimiento_de_prueba();
	if ( in_array( '--escribir', $argv, true ) ) {
		file_put_contents( CZU_TXT_PRUEBA, $texto );
		fwrite( STDERR, 'Escrito: ' . realpath( CZU_TXT_PRUEBA ) . ' (' . strlen( $texto ) . " bytes)\n" );
	} else {
		echo $texto;
	}
}
