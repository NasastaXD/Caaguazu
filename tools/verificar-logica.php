<?php
/**
 * Comprobaciones de las dos piezas de lógica que no son "pegar campos".
 *
 *   php tools/verificar-logica.php
 *
 * Sale con código 1 si algo falla, igual que verificar-diseno.php.
 *
 * Sólo hay dos funciones en todo el ecosistema que TRANSFORMAN un dato en vez
 * de moverlo de un lado a otro, y las dos fallan en silencio si se equivocan:
 *
 *   CEADSSO_Roles::normalizar()          decide si una persona entra o no
 *   PROMOTUR_Destinos::coords_desde_maps() decide dónde cae un pin en el mapa
 *
 * Un pin corrido no tira ningún error: manda a alguien al lugar equivocado. Un
 * rol que no normaliza bien tampoco: rebota a una persona con un mensaje que
 * no dice por qué. Por eso están acá, con los casos reales de cada una — los
 * cuatro formatos de enlace que escribe Google, y las formas en que un
 * WordPress escribe el nombre de un rol.
 *
 * Se corren sin WordPress, con el mínimo de sus funciones simulado abajo:
 * probar esto no puede costar levantar un sitio.
 */

// phpcs:disable
define( 'ABSPATH', __DIR__ );
function __( $s, $d = '' ) { return $s; }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function get_option( $k, $d = false ) { return $d; }
function update_option( ...$a ) { return true; }
function apply_filters( $h, $v ) { return $v; }
function get_post_meta( ...$a ) { return ''; }
function wp_strip_all_tags( $s ) { return $s; }
function get_post_field( ...$a ) { return ''; }
function register_post_meta( ...$a ) {}
function add_action( ...$a ) {}
function caaguazu_account_can( ...$a ) { return true; }
function post_type_exists( $t ) { return false; }
function register_post_type( ...$a ) {}
function register_taxonomy_for_object_type( ...$a ) {}
function get_post_type( $id ) { return 'promotur_destino'; }
function update_post_meta( ...$a ) {}
function sanitize_textarea_field( $s ) { return $s; }
function esc_url_raw( $s ) { return $s; }
function sanitize_text_field( $s ) { return $s; }

$raiz = dirname( __DIR__ );
require $raiz . '/caaguazu-sso-cead/includes/class-roles.php';

$fallos = 0;
function comprobar( $etiqueta, $obtenido, $esperado ) {
	global $fallos;
	$ok = ( $obtenido === $esperado );
	if ( ! $ok ) { $fallos++; }
	printf( "%s  %-52s  %s\n", $ok ? 'ok  ' : 'FALLA', $etiqueta, var_export( $obtenido, true ) );
}

echo "\n== CEADSSO_Roles::normalizar ==\n";
comprobar( 'alumno_turismo (el del contrato)',  CEADSSO_Roles::normalizar( 'alumno_turismo' ), 'alumno' );
comprobar( 'docente_turismo (el del contrato)', CEADSSO_Roles::normalizar( 'docente_turismo' ), 'docente' );
comprobar( 'Cead_Docente_Turismo',              CEADSSO_Roles::normalizar( 'Cead_Docente_Turismo' ), 'docente' );
comprobar( 'DOCENTE',                           CEADSSO_Roles::normalizar( 'DOCENTE' ), 'docente' );
comprobar( 'cead-alumno (con guion)',           CEADSSO_Roles::normalizar( 'cead-alumno' ), 'alumno' );
comprobar( 'Alumno del curso (con espacios)',   CEADSSO_Roles::normalizar( 'Alumno del curso' ), 'alumno_del_curso' );
comprobar( 'Matrícula (con acento)',            CEADSSO_Roles::normalizar( 'Matrícula' ), 'matricula' );
comprobar( 'vacío',                             CEADSSO_Roles::normalizar( '' ), '' );
/*
 * El rol escrito como frase: al sacarle el sufijo del curso queda colgando la
 * preposición. «Dirección de Turismo» daba `direccion_de`, que no es ningún
 * rol y rebotaba a quien dirige la carrera.
 */
comprobar( 'Dirección de Turismo (queda la preposición)', CEADSSO_Roles::normalizar( 'Dirección de Turismo' ), 'direccion' );
comprobar( 'Direccion de Servicios Turisticos',           CEADSSO_Roles::normalizar( 'Direccion de Servicios Turisticos' ), 'direccion' );

echo "\n== CEADSSO_Roles::resolver ==\n";
comprobar( 'alumno_turismo → mini',    CEADSSO_Roles::resolver( 'alumno_turismo' ), 'promotur_mini' );
comprobar( 'docente_turismo → promotor', CEADSSO_Roles::resolver( 'docente_turismo' ), 'promotur_promotor' );
comprobar( 'subscriber (rol de WP) → mini', CEADSSO_Roles::resolver( 'subscriber' ), 'promotur_mini' );
comprobar( 'Cead_Docente → promotor',  CEADSSO_Roles::resolver( 'Cead_Docente' ), 'promotur_promotor' );
comprobar( 'administrator → nada (no se regala el panel)', CEADSSO_Roles::resolver( 'administrator' ), null );
comprobar( 'editor → nada',            CEADSSO_Roles::resolver( 'editor' ), null );
comprobar( 'cualquier_cosa → nada',    CEADSSO_Roles::resolver( 'cualquier_cosa' ), null );

/*
 * El rol tal como lo escribe una persona en el CEAD, con espacios y
 * mayúsculas. Un WordPress manda el slug del rol, pero también manda el
 * nombre visible cuando quien configuró el emisor puso ese: los dos tienen
 * que entrar.
 */
comprobar( '«Docente Turismo» (nombre visible) → promotor', CEADSSO_Roles::resolver( 'Docente Turismo' ), 'promotur_promotor' );
comprobar( '«Alumno Turismo» (nombre visible) → mini',      CEADSSO_Roles::resolver( 'Alumno Turismo' ), 'promotur_mini' );
comprobar( 'direccion_turismo → promotor',                 CEADSSO_Roles::resolver( 'direccion_turismo' ), 'promotur_promotor' );
comprobar( '«Dirección de Turismo» → promotor',            CEADSSO_Roles::resolver( 'Dirección de Turismo' ), 'promotur_promotor' );

/*
 * Y que nadie vuelva a mutilar el rol ANTES de que el normalizador lo vea.
 *
 * `normalizar()` convierte los separadores —espacio y guion pasan a `_`— y
 * después usa ese `_` para sacar el prefijo del colegio y el sufijo del curso.
 * `sanitize_key()` no convierte: BORRA. Con él en el medio, «Docente Turismo»
 * llega como «docenteturismo», que ya no pierde el sufijo y no matchea nada,
 * y la persona rebota con «tu rol todavía no está habilitado» sin que haya
 * ningún error en ningún lado. Es lo que hacía el canje.
 *
 * Se mira el código y no el resultado porque el daño ocurre una capa antes de
 * lo que estas comprobaciones pueden llamar: `redeem()` sale a la red.
 */
$redeem = file_get_contents( $raiz . '/caaguazu-sso-cead/includes/class-redeem.php' );
preg_match( "/'rol'\s*=>\s*([a-z_]+)\(/", $redeem, $m );
comprobar(
	'el canje no mutila el rol antes de normalizarlo',
	isset( $m[1] ) ? $m[1] : '(no se encontró la línea)',
	'sanitize_text_field'
);

echo "\n== PROMOTUR_Destinos::coords_desde_maps ==\n";
require $raiz . '/caaguazu-portal/includes/class-destinos.php';
$casos = array(
	'enlace de «Compartir» (!3d!4d gana sobre @)' => array(
		'https://www.google.com/maps/place/Salto+Suizo/@-25.6800,-56.4400,17z/data=!3m1!4b1!4m6!3m5!1s0x0:0x0!8m2!3d-25.6811!4d-56.4422',
		array( 'lat' => -25.6811, 'lng' => -56.4422 ),
	),
	'barra de dirección (@lat,lng,zoom)' => array(
		'https://www.google.com/maps/@-25.4669,-56.0175,15z',
		array( 'lat' => -25.4669, 'lng' => -56.0175 ),
	),
	'búsqueda con ?query=' => array(
		'https://www.google.com/maps/search/?api=1&query=-25.4669,-56.0175',
		array( 'lat' => -25.4669, 'lng' => -56.0175 ),
	),
	'query con la coma escapada (%2C)' => array(
		'https://www.google.com/maps/search/?api=1&query=-25.4669%2C-56.0175',
		array( 'lat' => -25.4669, 'lng' => -56.0175 ),
	),
	'dos números pegados a mano' => array(
		'-25.4669, -56.0175',
		array( 'lat' => -25.4669, 'lng' => -56.0175 ),
	),
	'enlace corto: no trae el punto' => array( 'https://maps.app.goo.gl/AbCdEf123', null ),
	'texto cualquiera'              => array( 'el salto que está pasando el puente', null ),
	'vacío'                         => array( '', null ),
	'fuera del planeta (lat 991)'   => array( 'https://www.google.com/maps/@991.5,-56.0175,15z', null ),
);
foreach ( $casos as $etiqueta => $caso ) {
	comprobar( $etiqueta, PROMOTUR_Destinos::coords_desde_maps( $caso[0] ), $caso[1] );
}

echo "\n";
if ( $fallos ) { echo "$fallos comprobación/es fallaron\n"; exit( 1 ); }
echo "Todo bien.\n";
