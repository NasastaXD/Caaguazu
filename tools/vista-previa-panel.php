<?php
/**
 * Vista previa del panel sin WordPress.
 *
 * Renderiza una plantilla de `caaguazu-portal` en un HTML suelto, con el
 * mínimo de WordPress y de las clases del plugin simulado acá adentro. Existe
 * por una razón concreta: un agente (o cualquiera) puede pasar el linter y no
 * haber visto nunca la pantalla dibujada. Esto permite mirarla.
 *
 *   php tools/vista-previa-panel.php sections/home > /tmp/home.html
 *   php tools/vista-previa-panel.php auth/login    > /tmp/login.html
 *
 * NO va dentro del plugin: es herramienta de repo, no código que se instale.
 * Los datos son de mentira y están puestos para poblar la maqueta — nada de
 * lo que sale de acá es contenido real ni sirve como captura de producción.
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'YEAR_IN_SECONDS', 31536000 );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'COOKIEPATH', '/' );
define( 'ENT_QUOTES_COMPAT', ENT_QUOTES );

$plugin = dirname( __DIR__ ) . '/caaguazu-portal/';
define( 'PROMOTUR_VERSION', '2.2.0' );
define( 'PROMOTUR_FILE', $plugin . 'caaguazu-portal.php' );
define( 'PROMOTUR_DIR', $plugin );
define( 'PROMOTUR_URI', './caaguazu-portal/' );
define( 'PROMOTUR_BASENAME', 'caaguazu-portal/caaguazu-portal.php' );
define( 'PROMOTUR_BASE', 'turismo-panel' );

/* ---------------------------------------------------------------------------
 * WordPress, lo mínimo
 * ------------------------------------------------------------------------ */

function __( $s, $d = '' ) { return $s; }
function _n( $uno, $varios, $n, $d = '' ) { return 1 === (int) $n ? $uno : $varios; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_url( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_js( $s ) { return addslashes( (string) $s ); }
function esc_html__( $s, $d = '' ) { return esc_html( $s ); }
function esc_attr__( $s, $d = '' ) { return esc_attr( $s ); }
function esc_html_e( $s, $d = '' ) { echo esc_html( $s ); }
function esc_attr_e( $s, $d = '' ) { echo esc_attr( $s ); }
function esc_textarea( $s ) { return esc_html( $s ); }
function wp_kses_post( $s ) { return $s; }
function wpautop( $s ) { return '<p>' . $s . '</p>'; }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_html_class( $s ) { return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $s ); }
function wp_unslash( $s ) { return $s; }
function number_format_i18n( $n ) { return number_format( (float) $n, 0, ',', '.' ); }
function home_url( $p = '/' ) { return 'https://caaguazu.net' . $p; }
function admin_url( $p = '' ) { return 'https://caaguazu.net/wp-admin/' . $p; }
function self_admin_url( $p = '' ) { return admin_url( $p ); }
function get_bloginfo( $k = '' ) { return 'name' === $k ? 'Caaguazú' : 'UTF-8'; }
function bloginfo( $k = '' ) { echo get_bloginfo( $k ); }
function language_attributes() { echo 'lang="es"'; }
function body_class( $extra = '' ) { echo 'class="' . esc_attr( $extra ) . '"'; }
function wp_body_open() {}
function add_action( ...$a ) {}
function add_filter( ...$a ) {}
function remove_action( ...$a ) {}
function apply_filters( $hook, $value ) { return $value; }
function do_action( ...$a ) {}
function wp_nonce_field( ...$a ) { echo '<input type="hidden" name="_wpnonce" value="vista-previa">'; }
function wp_create_nonce( $a = '' ) { return 'vista-previa'; }
function get_query_var( $k, $default = '' ) { return $default; }
function locate_template( $files ) { return ''; }
function get_transient( $k ) { return false; }
function set_transient( ...$a ) { return true; }
function delete_transient( $k ) { return true; }
function get_option( $k, $default = false ) { return $default; }
function update_option( ...$a ) { return true; }
function current_user_can( $cap ) { return true; }
function get_current_user_id() { return 1; }
function get_avatar_url( $email ) { return ''; }
function get_permalink( $p = null ) { return home_url( '/destino/ejemplo' ); }
function get_the_title( $p = null ) { return is_object( $p ) ? $p->post_title : ''; }
function get_the_modified_date( $f = '', $p = null ) { return '12 de agosto de 2026'; }
function human_time_diff( $a, $b = null ) { return 'hace 2 horas'; }
function current_time( $tipo = 'mysql' ) { return 'mysql' === $tipo ? gmdate( 'Y-m-d H:i:s' ) : gmdate( $tipo ); }
function date_i18n( $formato, $marca = null ) {
	// WordPress traduce los nombres de día; acá alcanza con los tres primeros
	// caracteres en español para que la maqueta se lea como se va a leer.
	$dias = array( 'dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb' );
	if ( 'D' === $formato ) { return $dias[ (int) gmdate( 'w', $marca ?: time() ) ]; }
	return gmdate( $formato, $marca ?: time() );
}
function add_query_arg( ...$a ) { return isset( $a[2] ) ? $a[2] : ( is_string( end( $a ) ) ? end( $a ) : '#' ); }
function remove_query_arg( ...$a ) { return '#'; }
function is_admin() { return false; }
function selected( $a, $b = true, $echo = true ) { $r = (string) $a === (string) $b ? ' selected' : ''; if ( $echo ) { echo $r; } return $r; }
function checked( $a, $b = true, $echo = true ) { $r = (string) $a === (string) $b ? ' checked' : ''; if ( $echo ) { echo $r; } return $r; }
function disabled( $a, $b = true, $echo = true ) { return ''; }
function get_post_meta( $id, $key = '', $single = false ) {
	$muestra = array(
		'_promotur_tipo'      => 'foto',
		'_promotur_vence'     => '2026-09-01',
		'_promotur_nombre'    => 'Visitante',
		'_promotur_email'     => 'visitante@ejemplo.test',
		'_promotur_mensaje'   => '¿Se puede visitar los domingos?',
		'_promotur_asignado'  => 0,
		'titulo'              => 'Salto Suizo',
		'gancho'              => 'Una caída de agua a 40 minutos del centro',
		'descripcion'         => 'Sendero corto, sombra y agua fría todo el año.',
	);
	return isset( $muestra[ $key ] ) ? $muestra[ $key ] : '';
}
function get_the_post_thumbnail_url( $p = null, $size = '' ) { return ''; }
function wp_get_attachment_image_url( $id, $size = '' ) { return ''; }
function wp_get_attachment_url( $id ) { return ''; }
function get_terms( $args = array() ) {
	$out = array();
	foreach ( array( 'Naturaleza', 'Gastronomía', 'Cultura' ) as $i => $nombre ) {
		$t          = new stdClass();
		$t->term_id = 30 + $i;
		$t->name    = $nombre;
		$out[]      = $t;
	}
	return $out;
}
function get_term_meta( $id, $key = '', $single = false ) {
	$muestra = array( 'czuapi_color' => '#2e7d32', 'czuapi_icono' => 'nature' );
	return isset( $muestra[ $key ] ) ? $muestra[ $key ] : '';
}
function is_wp_error( $x ) { return false; }
function sanitize_hex_color( $c ) { return $c; }
function wp_list_pluck( $arr, $field ) { return array(); }
function get_edit_post_link( $id ) { return '#'; }
function get_post( $id = null ) { $p = new stdClass(); $p->ID = (int) $id; $p->post_title = 'Salto Suizo'; return $p; }
function get_post_status( $id = null ) { return 'draft'; }
function get_post_modified_time( $f = 'U', $gmt = false, $p = null ) { return time() - 7200; }
function get_post_time( $f = 'U', $gmt = false, $p = null ) { return time() - 86400; }
function get_the_excerpt( $p = null ) { return 'Sendero corto, sombra y agua fría todo el año.'; }
function get_the_author_meta( $f, $id = 0 ) { return 'Ana Giménez'; }
function wp_trim_words( $t, $n = 55, $more = null ) { return $t; }
function has_post_thumbnail( $p = null ) { return false; }
function wp_head() {
	global $promotur_css;
	echo "<title>Vista previa — panel</title>\n<style>\n" . $promotur_css . "\n</style>\n";
}
function wp_footer() {}

/* ---------------------------------------------------------------------------
 * Sistema de cuentas (caaguazu-cuentas)
 * ------------------------------------------------------------------------ */

function caaguazu_account_id() { return 7; }
function caaguazu_is_logged_in() { return true; }
function caaguazu_current_account() {
	return array( 'id' => 7, 'display_name' => 'Achmad Hakim', 'email' => 'promotor@ejemplo.test', 'phone' => '' );
}
function caaguazu_account_can( $panel, $cap, $id = null ) { return true; }
function caaguazu_wp_admin_bypass() { return false; }
function caaguazu_register_panel( ...$a ) {}

class Caaguazu_Cuentas_Accounts {
	public static function get( $id ) { return caaguazu_current_account(); }
}
class Caaguazu_Cuentas_Install {
	public static function tables() { return array( 'accounts' => 'wp_caaguazu_accounts', 'grants' => 'wp_caaguazu_grants' ); }
}
class Caaguazu_Cuentas_Panels {
	public static function instance() { return new self(); }
	public function get_grant( $account_id, $panel ) { return array( 'role' => 'promotur_promotor', 'status' => 'active' ); }
}

/* ---------------------------------------------------------------------------
 * Clases del portal que tocan la base
 * ------------------------------------------------------------------------ */

/**
 * Las dos puertas del panel. Acá sólo hace falta que dibujen la URL y el campo
 * oculto; quien despacha de verdad es la clase real.
 */
class PROMOTUR_Acciones {
	public static function url( $nombre, $tipo = 'accion' ) { return promotur_url( $tipo . '/' . $nombre ); }
	public static function token( $ambito = 'panel' ) { return 'token-de-prueba'; }
	public static function campos( $ambito = 'panel' ) { echo '<input type="hidden" name="promotur_token" value="token-de-prueba">'; }
}
class PROMOTUR_Roles {
	public static function sections() { return array(); }
	public static function label( $key ) { return 'promotur_mini' === $key ? 'Mini Promotor' : 'Promotor'; }
	public static function roles() {
		return array(
			'promotur_promotor'  => array( 'label' => 'Promotor', 'caps' => array() ),
			'promotur_mini'      => array( 'label' => 'Mini Promotor', 'caps' => array() ),
			'promotur_visitante' => array( 'label' => 'Visitante', 'caps' => array() ),
		);
	}
}
class PROMOTUR_Destinos {
	const CPT = 'promotur_destino';
	const OWNER_META = '_promotur_owner';
	public static function owner_account_id( $post_id ) { return 7; }
	public static function fields() {
		return array(
			'basico' => array(
				'label'  => 'Lo básico',
				'fields' => array(
					'titulo' => array( 'label' => 'Nombre del lugar', 'type' => 'text', 'req' => true ),
					'gancho' => array( 'label' => 'Frase gancho', 'type' => 'text', 'req' => true ),
					'descripcion' => array( 'label' => 'Descripción', 'type' => 'textarea', 'req' => true ),
				),
			),
			'visita' => array(
				'label'  => 'La visita',
				'fields' => array(
					'como_llegar' => array( 'label' => 'Cómo llegar', 'type' => 'textarea', 'req' => false ),
					'horarios'    => array( 'label' => 'Horarios', 'type' => 'text', 'req' => false ),
					'precio'      => array( 'label' => 'Precio', 'type' => 'text', 'req' => false ),
				),
			),
		);
	}
}
class PROMOTUR_Notifications {
	public static function instance() { return new self(); }
	public static function review_queue_count() { return 4; }
	public function get_unread_count() { return 2; }
	public function get_items() {
		return array(
			array( 'title' => 'Ficha enviada a revisión', 'when' => 'hace 2 horas', 'url' => '#', 'icon' => 'inbox' ),
			array( 'title' => 'Ficha publicada', 'when' => 'ayer', 'url' => '#', 'icon' => 'check' ),
		);
	}
}
class PROMOTUR_Tareas {
	public static function pending_count_for( $uid ) { return 3; }
	public static function estados() { return array( 'abierta' => 'Abierta', 'tomada' => 'Tomada', 'hecha' => 'Hecha' ); }
	public static function get_estado( $t ) { return 'abierta'; }
	public static function is_assigned( $t, $uid = 0 ) { return false; }
	public static function visible_for( $uid ) {
		$out = array();
		foreach ( array( array( 'Fotografiar el Salto Suizo', 'abierta' ), array( 'Verificar horarios del museo', 'tomada' ) ) as $i => $m ) {
			$t             = new stdClass();
			$t->ID         = 200 + $i;
			$t->post_title   = $m[0];
			$t->post_content = 'Sacar tres fotos horizontales con buena luz.';
			$t->estado       = $m[1];
			$out[]           = $t;
		}
		return $out;
	}
}
class PROMOTUR_Audit {
	public static function post_actions() { return array( 'destino_created', 'destino_enviado', 'destino_publicado' ); }
	public static function table() { return 'wp_promotur_audit_log'; }
}
/* La API de la app: el panel la controla, así que la vista previa la simula
   con el mismo contrato público que expone el plugin real. */
class CZUAPI_UI_Content {
	const LOCALES = array( 'es', 'en', 'gn' );
	public static function base() {
		return array( 'nav.inventario' => 'Inventario', 'nav.mapa' => 'Mapa', 'nav.recorridos' => 'Recorridos', 'nav.articulos' => 'Artículos' );
	}
	public static function get_strings( $locale ) {
		return 'es' === $locale ? array( 'nav.mapa' => 'Mapa', 'inicio.saludo' => 'Bienvenido a Caaguazú' ) : array();
	}
	public static function get_manifest() {
		return array(
			'inicio.portada' => array( 'tipo' => 'imagen', 'id' => 42, 'alt' => 'Vista del salto' ),
			'ayuda.gesto'    => array( 'tipo' => 'animacion', 'url' => 'https://ejemplo.test/gesto.json', 'formato' => 'lottie' ),
		);
	}
	public static function set_strings( $locale, array $strings ) { return true; }
	public static function set_manifest( array $manifest ) { return true; }
}
class CZUAPI_Taxonomias {
	const TAX_CATEGORIA = 'promotur_categoria';
	const META_ICONO    = 'czuapi_icono';
	const META_COLOR    = 'czuapi_color';
}

class PROMOTUR_Stats {
	public static function views( $post_id ) { return 128; }
	public static function top_viewed( $limit = 8 ) { return array_slice( get_posts(), 0, 3 ); }
	public static function empty_searches() { return array( array( 'q' => 'termas', 'count' => 4 ), array( 'q' => 'cascada', 'count' => 2 ) ); }
	public static function content_health( $meses = 6 ) { return array( 'sin_foto' => array_slice( get_posts(), 0, 2 ), 'viejas' => array_slice( get_posts(), 2, 1 ) ); }
	public static function author_counts( $account_id ) { return array( 'total' => 9, 'publicadas' => 5 ); }
	public static function levels() { return array( 'nuevo' => 'Nuevo', 'confiable' => 'Confiable', 'experto' => 'Experto' ); }
	public static function get_level( $account_id ) { return 'confiable'; }
	public static function level_label( $account_id ) { return 'Confiable'; }
	public static function serie_diaria( array $actions, $dias = 7 ) {
		$muestra = array( 3, 7, 2, 9, 5, 11, 6 );
		$serie   = array();
		foreach ( array_slice( $muestra, 0, $dias ) as $i => $n ) {
			$serie[] = array( 'fecha' => gmdate( 'Y-m-d', time() - ( $dias - $i ) * DAY_IN_SECONDS ), 'n' => $n );
		}
		return array( 'dias' => $serie, 'total' => array_sum( array_slice( $muestra, 0, $dias ) ), 'previo' => 31 );
	}
}
class PROMOTUR_Editorial {
	public static function get_estado( $id ) { return isset( $id->estado ) ? $id->estado : 'borrador'; }
	public static function checklist( $post_id ) {
		return array(
			array( 'key' => 'titulo', 'label' => 'Nombre del lugar', 'done' => true ),
			array( 'key' => 'gancho', 'label' => 'Frase gancho', 'done' => true ),
			array( 'key' => 'descripcion', 'label' => 'Descripción', 'done' => false ),
			array( 'key' => 'portada', 'label' => 'Foto de portada', 'done' => false ),
			array( 'key' => 'ubicacion', 'label' => 'Ubicación', 'done' => true ),
		);
	}
	public static function get_feedback( $post_id ) {
		return array( array( 'autor' => 'Revisión', 'when' => 'ayer', 'texto' => 'Falta la foto de portada.' ) );
	}
	public static function quick_feedback() { return array( 'Falta foto', 'Falta ubicación', 'Revisar redacción' ); }
	public static function estado_class( $e ) {
		$map = array( 'borrador' => 'is-draft', 'enviado' => 'is-sent', 'en_revision' => 'is-review', 'necesita_cambios' => 'is-changes', 'aprobado' => 'is-approved', 'publicado' => 'is-published' );
		return isset( $map[ $e ] ) ? $map[ $e ] : 'is-muted';
	}
	public static function estado_label( $e ) {
		$map = array( 'borrador' => 'Borrador', 'enviado' => 'Enviado', 'en_revision' => 'En revisión', 'necesita_cambios' => 'Necesita cambios', 'aprobado' => 'Aprobado', 'publicado' => 'Publicado' );
		return isset( $map[ $e ] ) ? $map[ $e ] : $e;
	}
}
class PROMOTUR_PWA {
	public static function chrome_color( $dark = false ) { return $dark ? '#101012' : '#ffffff'; }
	public static function brand_hex() { return '#101012'; }
}

/** WP_Query, sólo lo que usan las plantillas del panel. */
class WP_Query {
	public $found_posts = 0;
	public function __construct( $args = array() ) {
		$this->found_posts = 12; // número de maqueta
	}
}

function get_posts( $args = array() ) {
	$muestra = array(
		array( 'Salto Suizo', 'borrador' ),
		array( 'Ykua La Patria', 'enviado' ),
		array( 'Museo de la Madera', 'en_revision' ),
		array( 'Cerro Verde', 'necesita_cambios' ),
		array( 'Iglesia Inmaculada Concepción', 'publicado' ),
	);
	$out = array();
	foreach ( $muestra as $i => $m ) {
		$p             = new stdClass();
		$p->ID         = 100 + $i;
		$p->post_title = $m[0];
		$p->estado     = $m[1];
		$out[]         = $p;
	}
	return $out;
}

/* ---------------------------------------------------------------------------
 * Render
 * ------------------------------------------------------------------------ */

/**
 * $wpdb de mentira. Se stubea la base y no las funciones del plugin: así
 * promotur_team_members() —y cualquier otra que consulte— corre de verdad, con
 * su SQL y su normalización, en vez de quedar tapada por un doble.
 */
class Promotur_Vista_Previa_DB {
	public $prefix = 'wp_';
	public function prepare( $sql, ...$args ) { return $sql; }
	public function get_var( $sql ) { return 0; }
	public function get_row( $sql, $output = null ) { return null; }
	public function get_results( $sql, $output = null ) {
		if ( false !== strpos( $sql, 'caaguazu_grants' ) ) {
			return array(
				array( 'id' => 7, 'email' => 'ana@ejemplo.test', 'display_name' => 'Ana Giménez', 'role' => 'promotur_promotor' ),
				array( 'id' => 8, 'email' => 'luis@ejemplo.test', 'display_name' => 'Luis Rojas', 'role' => 'promotur_mini' ),
			);
		}
		return array();
	}
	public function insert( ...$a ) { return true; }
}
$GLOBALS['wpdb'] = new Promotur_Vista_Previa_DB();

require $plugin . 'includes/helpers.php';

// Esta clase se carga de verdad (no se simula): es la que lee lo que la app
// tiene guardado, y la vista previa sirve justamente para ver ese resultado.
require $plugin . 'includes/class-app-control.php';

// El CSS se inyecta inline y las fuentes se apuntan al archivo real: si la
// vista previa se dibuja con otra tipografía, no sirve para juzgar el diseño.
$GLOBALS['promotur_css'] = str_replace(
	'url("../fonts/',
	'url("file://' . $plugin . 'assets/fonts/',
	file_get_contents( $plugin . 'assets/css/caaguazu-portal.css' )
);

$ruta = isset( $argv[1] ) ? $argv[1] : 'sections/home';

// El estado activo del menú sale de la sección que se está dibujando.
$GLOBALS['promotur_section'] = 0 === strpos( $ruta, 'sections/' ) ? substr( $ruta, strlen( 'sections/' ) ) : '';

// La vista previa dibuja el estado de una plantilla concreta: el estado del
// editorial de cada ficha lo pone el stub de get_posts(), no la base.
require $plugin . 'templates/' . $ruta . '.php';
