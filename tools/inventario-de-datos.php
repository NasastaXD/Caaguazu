<?php
/**
 * El inventario de datos que la app tiene que poder clonar, generado desde el
 * código que los define.
 *
 *   php tools/inventario-de-datos.php              escribe docs/datos-para-la-app.md
 *   php tools/inventario-de-datos.php --verificar  falla si el doc quedó viejo
 *
 * POR QUÉ GENERADO Y NO ESCRITO
 *
 * Un documento con la lista de campos escrito a mano queda viejo el día que
 * alguien agrega un campo, y nadie se entera: el que se entera es el que
 * programa la app, tres semanas después, cuando le falta un dato que la web ya
 * tenía. Acá la lista sale de `fields()` de cada modelo —el mismo array que
 * dibuja el formulario del panel— así que no puede desfasarse del panel.
 *
 * LO QUE ESTE ARCHIVO SÍ DECIDE
 *
 * Cómo sale cada campo por la API. Eso no está en el modelo: el modelo dice
 * qué se carga, no cómo se publica, y hay campos que no se publican. La tabla
 * `salida()` de abajo es esa decisión, y el modo `--verificar` la mantiene
 * honesta con dos comprobaciones:
 *
 *   1. Cada campo del modelo tiene que estar en la tabla, y cada entrada de la
 *      tabla tiene que existir en el modelo. Agregar un campo al panel sin
 *      decidir si sale a la app rompe la verificación.
 *   2. Cada meta que la tabla declara publicada tiene que aparecer en el código
 *      de caaguazu-app-api. Si dice que sale y la API nunca la lee, miente.
 *
 * La segunda es una búsqueda de texto, no un análisis del programa: encuentra
 * la meta escrita como literal —directamente o en la constante que la define—
 * y no prueba que esa lectura llegue a la respuesta. Alcanza para lo que tiene
 * que atrapar, que es una fila inventada.
 */

// phpcs:disable

// --- Lo mínimo de WordPress para poder cargar los modelos. -------------------
define( 'ABSPATH', __DIR__ );
function __( $s, $d = '' ) { return $s; }
function add_action( ...$a ) {}
function register_post_type( ...$a ) {}
function register_post_meta( ...$a ) {}
function register_taxonomy( ...$a ) {}
function register_taxonomy_for_object_type( ...$a ) {}
function post_type_exists( $t ) { return false; }
function taxonomy_exists( $t ) { return false; }
function get_post_meta( ...$a ) { return ''; }
function update_post_meta( ...$a ) {}
function get_post_field( ...$a ) { return ''; }
function get_post_type( $id ) { return 'promotur_destino'; }
function wp_strip_all_tags( $s ) { return $s; }
function apply_filters( $h, $v ) { return $v; }
function get_option( $k, $d = false ) { return $d; }
function caaguazu_account_can( ...$a ) { return true; }
function caaguazu_account_id() { return 0; }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function sanitize_text_field( $s ) { return $s; }
function sanitize_textarea_field( $s ) { return $s; }
function esc_url_raw( $s ) { return $s; }
function get_posts( ...$a ) { return array(); }
function get_the_terms( ...$a ) { return array(); }
function is_wp_error( $x ) { return false; }

$raiz = dirname( __DIR__ );
require_once $raiz . '/caaguazu-portal/includes/class-destinos.php';
require_once $raiz . '/caaguazu-portal/includes/class-articulos.php';
require_once $raiz . '/caaguazu-portal/includes/class-recorridos.php';

/* -------------------------------------------------------------------------
 * La decisión: qué hace la API con cada campo del panel.
 *
 *   'json'  la clave con la que sale, con el camino cuando está anidada
 *           ('practicos.horario'), o null si no sale.
 *   'donde' 'lista', 'detalle', 'los dos', o el motivo de que no salga.
 * ---------------------------------------------------------------------- */
function salida() {
	return array(
		'ficha' => array(
			'_promotur_tipo_item'      => array( 'json' => 'tipo_item',           'donde' => 'los dos' ),
			'_promotur_evento_inicio'  => array( 'json' => 'fechas.inicio',       'donde' => 'los dos' ),
			'_promotur_evento_fin'     => array( 'json' => 'fechas.fin',          'donde' => 'los dos' ),
			'_promotur_gancho'         => array( 'json' => 'gancho',              'donde' => 'los dos' ),
			'_promotur_portada'        => array( 'json' => 'portada',             'donde' => 'los dos' ),
			'_promotur_credito_fotos'  => array( 'json' => 'portada.credito',     'donde' => 'los dos' ),
			'_promotur_video'          => array( 'json' => 'video',               'donde' => 'detalle' ),
			'_promotur_maps'           => array( 'json' => 'google_maps',         'donde' => 'los dos', 'via' => 'PROMOTUR_Destinos::maps_url()' ),
			'_promotur_lat'            => array( 'json' => 'coordenadas.lat',     'donde' => 'los dos' ),
			'_promotur_lng'            => array( 'json' => 'coordenadas.lng',     'donde' => 'los dos' ),
			'_promotur_estado_camino'  => array( 'json' => 'acceso.estado_camino','donde' => 'detalle' ),
			'_promotur_accesibilidad'  => array( 'json' => 'acceso.accesibilidad','donde' => 'detalle' ),
			'_promotur_horario'        => array( 'json' => 'practicos.horario',   'donde' => 'detalle; en la lista, `horario_resumen`' ),
			'_promotur_costo'          => array( 'json' => 'practicos.costo',     'donde' => 'detalle' ),
			'_promotur_rango_precio'   => array( 'json' => 'rango_precio',        'donde' => 'los dos (en el detalle, dentro de `practicos`)' ),
			'_promotur_contacto'       => array( 'json' => 'practicos.contacto',  'donde' => 'detalle' ),
			'_promotur_fuentes'        => array( 'json' => 'fuentes',             'donde' => 'detalle' ),
		),
		'articulo' => array(
			'_articulo_antetitulo'  => array( 'json' => 'antetitulo',      'donde' => 'los dos' ),
			'_articulo_subtitulo'   => array( 'json' => 'subtitulo',       'donde' => 'los dos' ),
			'_articulo_autores'     => array( 'json' => 'autores[]',       'donde' => 'los dos' ),
			'_articulo_portada'     => array( 'json' => 'portada',         'donde' => 'los dos' ),
			'_articulo_pie_portada' => array( 'json' => 'portada.credito', 'donde' => 'los dos' ),
			'_articulo_fuentes'     => array( 'json' => 'fuentes[]',       'donde' => 'detalle' ),
		),
		'recorrido' => array(
			'_recorrido_portada'  => array( 'json' => 'portada',           'donde' => 'los dos' ),
			'_recorrido_duracion' => array( 'json' => 'duracion_estimada', 'donde' => 'los dos' ),
		),
	);
}

/**
 * Lo que la app recibe y no es un campo del formulario: metas que el panel
 * escribe por su cuenta, campos nativos del post y datos que la API calcula.
 */
function extras() {
	return array(
		'ficha' => array(
			array( 'post_title',                'titulo',                 'texto',    'El nombre del lugar o del evento.' ),
			array( 'post_content',              'articulo_html',          'HTML',     'La descripción larga, ya renderizada.' ),
			array( 'post_modified_gmt',         'actualizado',            'ISO 8601', 'Con esto y `/sync` la app sabe qué volver a bajar.' ),
			array( '_promotur_galeria',         'galeria[]',              'imagen[]', 'IDs de adjuntos; sale resuelta a objetos imagen.' ),
			array( '_promotur_articulos_rel',   'articulos_relacionados[]', 'ref[]',  'IDs de artículo; sale con id, título y portada.' ),
			array( '_caaguazu_owner',           'autor',                  'objeto',   'La cuenta dueña, no el `post_author` de WordPress.' ),
			array( 'promotur_categoria',        'categoria',              'término',  'Taxonomía. Una sola por ficha.' ),
			array( 'promotur_zona',             'zona',                   'término',  'Taxonomía. El distrito o la zona.' ),
			array( 'promotur_etiqueta',         'etiquetas[]',            'término[]','Taxonomía. Varias.' ),
			array( '— calculado —',             'google_maps',            'URL',      'El enlace pegado; si sólo hay coordenadas, uno armado con el pin. Nunca las dos formas.' ),
			array( '— calculado —',             'fechas.en_curso',        'bool',     'Sólo en eventos. Un evento sin fecha de fin dura ese día.' ),
			array( '— calculado —',             'fechas.terminado',       'bool',     'Sólo en eventos.' ),
		),
		'articulo' => array(
			array( 'post_title',        'titulo',       'texto',    '' ),
			array( 'post_excerpt',      'entradilla',   'texto',    'El párrafo de arranque. Se llamaba `bajada`.' ),
			array( 'post_content',      'cuerpo_html',  'HTML',     'El cuerpo de la nota, ya renderizado.' ),
			array( 'post_date_gmt',     'publicado',    'ISO 8601', '' ),
			array( 'post_modified_gmt', 'actualizado',  'ISO 8601', '' ),
			array( '_articulo_relacionados', 'relacionados[]', 'ref[]', 'No se carga desde el panel: si está vacío, la API sugiere hasta 6 de la misma categoría.' ),
			array( 'promotur_categoria', 'categoria',   'término',  '' ),
			array( 'promotur_etiqueta',  'etiquetas[]', 'término[]','' ),
		),
		'recorrido' => array(
			array( 'post_title',        'titulo',           'texto',   '' ),
			array( 'post_excerpt',      'resumen',          'texto',   '' ),
			array( 'post_content',      'articulo_html',    'HTML',    'La introducción del recorrido.' ),
			array( '_recorrido_tipo',   'tipo',             'texto',   '`prehecho` (lo arma el panel) o `usuario` (lo arma alguien en la app).' ),
			array( '_recorrido_paradas','paradas[]',        'objeto[]','Ver la forma abajo. El orden del array ES el recorrido.' ),
			array( '_recorrido_medios', 'medios[]',         'objeto[]','Audios y videos del recorrido entero.' ),
			array( '_recorrido_articulos', 'articulos[]',   'ref[]',   'Artículos vinculados, sólo los publicados.' ),
			array( '_recorrido_historia',  'historia',      'objeto',  'Bloque heredado: introducción, correlación, personas, curiosidades.' ),
			array( '— calculado —',     'cantidad_paradas', 'entero',  '' ),
			array( '— calculado —',     'costo_total',      'objeto',  '`{ hay_pago, detalle[] }` — los costos son texto libre, así que no se suman: se listan.' ),
			array( '— calculado —',     'fechas',           'objeto',  '`{ compatible, conflictos[] }` — si dos paradas son eventos que no se solapan, el recorrido no se hace en una salida.' ),
			array( '— calculado —',     'google_maps',      'URL',     'La ruta entera con los waypoints en orden, lista para abrir.' ),
		),
	);
}

/* ---------------------------------------------------------------------------
 * Comprobaciones
 * ------------------------------------------------------------------------ */

$modelos = array(
	'ficha'     => 'PROMOTUR_Destinos',
	'articulo'  => 'PROMOTUR_Articulos',
	'recorrido' => 'PROMOTUR_Recorridos',
);

$fallos = array();
$salida = salida();

// 1. Modelo y tabla de salida dicen lo mismo.
foreach ( $modelos as $clave => $class ) {
	$campos = array_keys( $class::flat_fields() );
	$tabla  = array_keys( $salida[ $clave ] );
	foreach ( array_diff( $campos, $tabla ) as $falta ) {
		$fallos[] = "{$clave}: el campo {$falta} está en el modelo y no en la tabla de salida. Decidí si sale a la app.";
	}
	foreach ( array_diff( $tabla, $campos ) as $sobra ) {
		$fallos[] = "{$clave}: la tabla de salida nombra {$sobra}, que ya no está en el modelo.";
	}
}

// 2. Lo que la tabla declara publicado, la API lo lee.
$fuente_api = '';
foreach ( glob( $raiz . '/caaguazu-app-api/includes/*.php' ) as $archivo ) {
	$fuente_api .= file_get_contents( $archivo );
}
$fuente_panel = '';
foreach ( glob( $raiz . '/caaguazu-portal/includes/*.php' ) as $archivo ) {
	$fuente_panel .= file_get_contents( $archivo );
}
foreach ( $salida as $clave => $filas ) {
	foreach ( $filas as $meta => $def ) {
		if ( null === $def['json'] ) { continue; }

		// Algunas metas la API no las lee: le pregunta al panel, que es el que
		// sabe cuál de dos campos gana (el enlace de Maps y las coordenadas es
		// el caso). Ahí se comprueba que ese método exista y que la nombre.
		if ( ! empty( $def['via'] ) ) {
			list( $cls, $met ) = explode( '::', str_replace( '()', '', $def['via'] ) );
			if ( ! method_exists( $cls, $met ) ) {
				$fallos[] = "{$clave}: {$meta} dice salir por {$def['via']}, que no existe.";
			} elseif ( false === strpos( $fuente_panel, "'" . $meta . "'" ) ) {
				$fallos[] = "{$clave}: {$meta} dice salir por {$def['via']}, y el panel no lo nombra en ningún lado.";
			} elseif ( false === strpos( $fuente_api, $met ) ) {
				$fallos[] = "{$clave}: {$meta} dice salir por {$def['via']}, y caaguazu-app-api nunca llama a ese método.";
			}
			continue;
		}

		if ( false === strpos( $fuente_api, "'" . $meta . "'" ) ) {
			$fallos[] = "{$clave}: la tabla dice que {$meta} sale como «{$def['json']}», y caaguazu-app-api no lo nombra en ningún lado.";
		}
	}
}

/* ---------------------------------------------------------------------------
 * El documento
 * ------------------------------------------------------------------------ */

function tipo_legible( $def ) {
	$mapa = array(
		'text' => 'texto', 'textarea' => 'texto largo', 'url' => 'URL',
		'image' => 'imagen', 'coord' => 'número', 'select' => 'opción',
		'datetime' => 'fecha y hora',
	);
	$t = isset( $def['type'] ) ? $def['type'] : 'text';
	$out = isset( $mapa[ $t ] ) ? $mapa[ $t ] : $t;
	if ( 'select' === $t && ! empty( $def['options'] ) ) {
		$out .= ' (' . implode( ' · ', array_map( function ( $k ) { return '`' . $k . '`'; }, array_filter( array_keys( $def['options'] ), 'strlen' ) ) ) . ')';
	}
	return $out;
}

function tabla_modelo( $class, $filas_salida ) {
	$out  = "| Campo en el panel | Meta | Tipo | Obligatorio | Sale como | Dónde |\n";
	$out .= "| --- | --- | --- | --- | --- | --- |\n";
	foreach ( $class::fields() as $grupo ) {
		foreach ( $grupo['fields'] as $key => $def ) {
			$s     = $filas_salida[ $key ];
			$req   = ! empty( $def['req'] ) ? 'sí' : 'no';
			if ( ! empty( $def['solo'] ) ) {
				$req .= ', sólo en ' . $def['solo'] . 's';
			}
			$donde = $s['donde'];
			if ( ! empty( $s['via'] ) ) {
				$donde .= ' — vía `' . $s['via'] . '`';
			}
			$out .= sprintf(
				"| %s | `%s` | %s | %s | %s | %s |\n",
				$def['label'], $key, tipo_legible( $def ), $req,
				null === $s['json'] ? '—' : '`' . $s['json'] . '`',
				$donde
			);
		}
	}
	return $out;
}

function tabla_extras( $filas ) {
	$out  = "| De dónde sale | Sale como | Tipo | Nota |\n| --- | --- | --- | --- |\n";
	foreach ( $filas as $f ) {
		$origen = ( '— calculado —' === $f[0] ) ? '_calculado_' : '`' . $f[0] . '`';
		$out .= sprintf( "| %s | `%s` | %s | %s |\n", $origen, $f[1], $f[2], $f[3] );
	}
	return $out;
}

$ex = extras();
$doc = <<<'MD'
# Los datos que la app tiene que clonar

<!--
  GENERADO. No editar a mano: se regenera con

      php tools/inventario-de-datos.php

  y `npm run verificar` falla si el archivo quedó viejo. Lo que hay que
  editar es tools/inventario-de-datos.php, que es donde está la decisión de
  cómo sale cada campo.
-->

Tres cosas se cargan en el panel y viajan a la app: la **ficha** (que puede ser
un sitio o un evento), el **artículo** y el **recorrido**. Este documento lista
todos sus datos: cómo se llaman adentro de WordPress, cómo salen por la API, y
cuáles son obligatorios para que algo se pueda publicar.

El contrato de los endpoints —paginación, caché, autenticación— está en
[contrato-app-contenido.md](contrato-app-contenido.md). Acá están los campos.

## Lo que vale para las tres

**Sólo se publica lo publicado.** El estado editorial vive en `_promotur_estado`
y sólo `publicado` llega a `post_status = publish`. La API nunca sirve otra
cosa, así que la app no tiene que filtrar por estado: lo que le llega, va.

**Fechas** en ISO 8601 con zona (`2026-03-14T19:00:00+00:00`). Las que la gente
carga se guardan en UTC.

**Imágenes** salen siempre con la misma forma, no como un ID suelto, y valen
`null` cuando no hay foto:

```json
{ "url": "https://…/foo.jpg", "w": 1600, "h": 1067,
  "credito": "Foto: Fulano", "alt": "Texto alternativo" }
```

**Términos** (categoría, zona, etiqueta):

```json
{ "id": 7, "slug": "naturaleza", "nombre": "Naturaleza", "color": "#2E7D32" }
```

`color` sólo viene si el término lo tiene cargado.

**Autor**: `{ "id": 12, "nombre": "…" }`, o `null`. Es la **cuenta** del sistema
de cuentas propio, no un usuario de WordPress: el `post_author` de todo el
contenido es un usuario de servicio y no sirve para firmar nada.

**Referencias a otro contenido** (`articulos_relacionados`, `articulos`) salen
con `id`, `titulo` y `portada`: lo justo para pintar la tarjeta y navegar. Para
el resto, la app pide ese `id` a su endpoint.

**Sincronizar.** `GET /sync?since=…` devuelve, por colección, qué IDs cambiaron
y cuáles se borraron. Las colecciones son `inventario`, `eventos`, `articulos` y
`recorridos`. Sin `since`, o con uno más viejo que la retención de lápidas, la
respuesta viene con `completo: true` y hay que recargar todo.

---

## Ficha — sitio o evento

Una ficha es un lugar. Si además pasa en una fecha, es un evento: el mismo
formulario, los mismos campos, más cuándo empieza y cuándo termina. `tipo_item`
dice cuál de las dos es.

`GET /inventario` · `GET /inventario/{id}` · `GET /mapa/markers`

### Campos del formulario

{$ficha_tabla}

### Lo demás que viaja

{$ficha_extras}

### Eventos

Un evento sale por los dos lados y es el mismo objeto:

- **`GET /inventario?tipo_item=evento`** — la colección completa, con la ficha
  entera. `tipo_item=sitio` es lo contrario, e incluye las fichas cargadas antes
  de que el tipo existiera (todas eran sitios).
- **`GET /eventos`** — la agenda: ordenada por fecha de inicio y, por defecto,
  sólo lo que todavía no terminó. Acepta `desde`, `hasta` y `categoria`.

`/eventos` mezcla dos fuentes y lo dice en `origen`:

| `origen` | Qué es | Detalle completo en |
| --- | --- | --- |
| `ficha` | Una ficha con `tipo_item = evento`. El camino de hoy. | `/inventario/{id}` |
| `evento_legado` | El CPT viejo `promotur_evento`, editable sólo desde wp-admin. No se carga más, pero lo que ya existe sigue sirviéndose. | `/eventos/{id}` |

Para la app la diferencia importa en un solo punto: **un evento de `origen: ficha`
sincroniza en la colección `inventario`, no en `eventos`**, porque es una ficha.
Lo más simple es clonar `inventario` entero y armar la agenda del lado del
teléfono filtrando por `tipo_item` y ordenando por `fechas.inicio` — así la
agenda funciona sin conexión, que es lo que `/eventos` no puede dar.

En `/mapa/markers` los pines de ficha vienen con `tipo: "destino"` y
`tipo_item` para distinguirlos; los del CPT viejo, con `tipo: "evento"`.

---

## Artículo

`GET /articulos` · `GET /articulos/{id}`

### Campos del formulario

{$articulo_tabla}

Los tres campos que no están en esa tabla son los nativos del post, y son los
que llevan el peso de la nota: **título**, **entradilla** (`post_excerpt`) y
**cuerpo** (`post_content`). Los tres son obligatorios para publicar.

### Lo demás que viaja

{$articulo_extras}

**Autores.** `autores` es una lista, no una cadena: una nota puede tener dos
firmas. Cada una es `{ "nombre": "…", "cuenta": 12 | null }` — `cuenta` viene
cuando quien firma es una cuenta del sistema. Si el artículo no tiene firma
escrita, se cae a la cuenta que lo cargó.

**Fuentes.** Se cargan una por línea y salen como lista de cadenas, sin las
líneas vacías.

---

## Recorrido

Un camino de hasta **nueve paradas** en un orden que importa. Las paradas se
eligen del inventario turístico ya cargado —no se escriben— y cada una puede
llevar un texto propio (la historia del lugar, un dato curioso) y un audio o un
video.

`GET /recorridos` · `GET /recorridos/{id}` · `GET /mis-recorridos` (los que arma
la gente en la app; requieren sesión)

### Campos del formulario

{$recorrido_tabla}

Más el **título**, el **resumen** (`post_excerpt`) y la **introducción**
(`post_content`).

### Lo demás que viaja

{$recorrido_extras}

### Una parada

El orden del array es el recorrido. `orden` viene renumerado de 1 a N: si
alguien borró la parada 3, no queda un hueco.

```json
{
  "orden": 2,
  "ref_tipo": "destino",
  "ref_id": 118,
  "disponible": true,
  "titulo": "Salto Cristal",
  "portada": { "…": "…" },
  "categoria": { "…": "…" },
  "coordenadas": { "lat": -25.6, "lng": -56.1 },
  "google_maps": "https://…",
  "costo": "5.000 Gs",
  "horario": "8 a 17",
  "inicio": null,
  "fin": null,
  "texto": "Lo que el promotor escribió sobre esta parada.",
  "medio": { "tipo": "audio", "url": "https://…" }
}
```

`disponible: false` es una parada cuyo sitio dejó de estar publicado. Viene
igual, con `orden`, `ref_id` y `texto` y sin el resto: el recorrido no se
renumera solo cuando una ficha se despublica, y la app tiene que poder mostrar
que ahí falta algo en vez de saltearlo en silencio.

Una parada puede ser un sitio o un evento: si es un evento, `inicio` y `fin`
vienen cargados. Las dos claves vienen siempre —en `null` cuando es un sitio—
así la app lee todas las paradas igual.

### Un medio

```json
{ "tipo": "audio", "url": "https://…", "titulo": "La leyenda del salto" }
```

`tipo` es `audio` o `video`. Los del recorrido entero van en `medios[]`; el de
cada parada, en `parada.medio`.

---

## Campos que ya no existen

Estaban en la ficha y se sacaron: se llenaban con frases genéricas que no
ayudaban a decidir nada. **La API no los sirve.** El valor cargado sigue en su
meta por si alguna vez hace falta recuperarlo, pero nada lo lee.

{$retirados}

MD;

$doc = str_replace(
	array( '{$ficha_tabla}', '{$ficha_extras}', '{$articulo_tabla}', '{$articulo_extras}', '{$recorrido_tabla}', '{$recorrido_extras}', '{$retirados}' ),
	array(
		tabla_modelo( 'PROMOTUR_Destinos', $salida['ficha'] ),
		tabla_extras( $ex['ficha'] ),
		tabla_modelo( 'PROMOTUR_Articulos', $salida['articulo'] ),
		tabla_extras( $ex['articulo'] ),
		tabla_modelo( 'PROMOTUR_Recorridos', $salida['recorrido'] ),
		tabla_extras( $ex['recorrido'] ),
		implode( "\n", array_map( function ( $k ) { return '- `' . $k . '`'; }, PROMOTUR_Destinos::campos_retirados() ) ),
	),
	$doc
);

$destino = $raiz . '/docs/datos-para-la-app.md';

if ( $fallos ) {
	echo "\nEl inventario de datos no cierra:\n\n";
	foreach ( $fallos as $f ) { echo "  - {$f}\n"; }
	echo "\n";
	exit( 1 );
}

if ( in_array( '--verificar', $argv, true ) ) {
	$actual = file_exists( $destino ) ? file_get_contents( $destino ) : '';
	if ( $actual !== $doc ) {
		echo "\ndocs/datos-para-la-app.md quedó viejo. Regeneralo:\n\n    php tools/inventario-de-datos.php\n\n";
		exit( 1 );
	}
	echo "ok    docs/datos-para-la-app.md al día (" . count( PROMOTUR_Destinos::flat_fields() ) . " campos de ficha, "
		. count( PROMOTUR_Articulos::flat_fields() ) . " de artículo, "
		. count( PROMOTUR_Recorridos::flat_fields() ) . " de recorrido)\n";
	exit( 0 );
}

file_put_contents( $destino, $doc );
echo "Escrito docs/datos-para-la-app.md\n";
