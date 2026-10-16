#!/usr/bin/env node
/**
 * Prueba la web de turismo (caaguazu-web-ios/sitio) en un navegador de verdad,
 * con la API simulada. No necesita WordPress ni red.
 *
 *   node tools/probar-web.mjs
 *
 * Sale con código 1 si algo falla. Es la hermana de auditar-movil.mjs: aquella
 * mide el panel, ésta la web pública.
 *
 * QUÉ CUBRE, Y POR QUÉ ESTO Y NO OTRA COSA
 *
 * Son las cosas que fallan EN SILENCIO — no tiran ningún error, sólo se ven
 * mal o no andan:
 *
 *  - Lo fijo (la barra del asistente, la capa del mapa) tiene que anclarse a la
 *    ventana. Un ancestro con `transform` —aunque la animación termine en
 *    `none`— lo ancla a la pantalla, y la barra queda pegada al contenido.
 *  - Nada se sale de la pantalla a 390 y a 360 px, y lo que se toca llega a
 *    44 px en un teléfono. El campo de búsqueda empujaba sus botones fuera de
 *    la pantalla por el ancho natural de un <input>.
 *  - La página abre aunque `ajustes.json` no responda. Sin un tope de tiempo,
 *    el arranque esperaba para siempre y la pantalla quedaba en blanco.
 *  - La página NUNCA queda vacía: antes de que app.js corra ya hay una
 *    pantalla de «Cargando…», y si la app no arranca (el script no carga, o
 *    se cuelga) aparece qué pasó y un botón para reintentar, con el informe.
 *    Es lo que faltaba cuando el teléfono mostraba un fondo liso y nada más.
 *  - Lo que el PLUGIN le dice al navegador, probado con el plugin de verdad
 *    (tools/web-prueba/router.php, sin WordPress): cada archivo de código
 *    lleva su versión en la URL, ningún módulo se baja dos veces, la segunda
 *    visita no vuelve a pedir nada de código, y una revalidación contesta 304
 *    aunque el hosting le haya vuelto débil el ETag.
 *  - Ninguna clave de texto sin traducir en pantalla, en los tres idiomas.
 *  - Los flujos: guardar un favorito sin abrir la ficha, cambiar de tema,
 *    preguntarle al asistente, el error de «demasiadas preguntas», las
 *    direcciones de 1.x, las tiendas, las hojas.
 *
 * Lo que NO prueba, a propósito: Leaflet real (el mapa se prueba con un doble
 * mínimo, que verifica el código de la web pero no a Leaflet) y la API real.
 */

import { createRequire } from 'module';
import { createServer } from 'http';
import { createServer as servidorTcp } from 'net';
import { spawn, spawnSync } from 'child_process';
import { readFileSync, existsSync, writeFileSync } from 'fs';
import { tmpdir } from 'os';
import { join, dirname, extname, normalize } from 'path';
import { fileURLToPath } from 'url';
import { deflateSync, crc32 } from 'zlib';

const aqui = dirname( fileURLToPath( import.meta.url ) );
const SITIO = join( aqui, '..', 'caaguazu-web-ios', 'sitio' );
const requerir = createRequire( import.meta.url );

let chromium;
try {
	( { chromium } = requerir( 'playwright' ) );
} catch ( e ) {
	console.error( 'Falta Playwright. Instalarlo con: npm i -D playwright && npx playwright install chromium' );
	process.exit( 2 );
}

const verde = '\x1b[32m', rojo = '\x1b[31m', gris = '\x1b[90m', fin = '\x1b[0m';
let fallas = 0;
function ok( nombre, cond, extra = '' ) {
	if ( ! cond ) { fallas++; }
	console.log( ` ${ cond ? verde + 'ok  ' : rojo + 'FALLA' }${ fin }  ${ nombre }${ ! cond && extra ? gris + '  → ' + extra + fin : '' }` );
}
function seccion( t ) { console.log( `\n${ gris }== ${ t } ==${ fin }` ); }

/* ---------------------------------------------------------------------------
 * Un servidor de archivos mínimo para sitio/. El plugin no está: lo que el
 * plugin arma (ajustes.json) se simula más abajo, en la red del navegador.
 * ------------------------------------------------------------------------ */

const TIPOS = { '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css', '.json': 'application/json', '.png': 'image/png', '.woff2': 'font/woff2', '.webmanifest': 'application/manifest+json' };
const servidor = createServer( ( req, res ) => {
	let ruta = decodeURIComponent( new URL( req.url, 'http://x' ).pathname );
	if ( ruta === '/' ) { ruta = '/index.html'; }
	const archivo = normalize( join( SITIO, ruta ) );
	if ( ! archivo.startsWith( SITIO ) || ! existsSync( archivo ) ) {
		res.writeHead( 404 ); res.end( 'no' ); return;
	}
	res.writeHead( 200, { 'Content-Type': TIPOS[ extname( archivo ) ] || 'application/octet-stream' } );
	res.end( readFileSync( archivo ) );
} );
await new Promise( ( r ) => servidor.listen( 0, '127.0.0.1', r ) );
const BASE = `http://127.0.0.1:${ servidor.address().port }/index.html`;

/* ---------------------------------------------------------------------------
 * Datos de ejemplo, con la forma que manda la API real (czu-app/v1).
 * ------------------------------------------------------------------------ */

const API = 'https://caaguazu.net/wp-json/czu-app/v1/';
const IMAGEN = { url: 'https://caaguazu.net/wp-content/uploads/foto.png', w: 388, h: 220, credito: 'Ministerio de Caaguazú', alt: '' };
const CATEGORIAS = [ { id: 66, slug: 'sitio-natural', nombre: 'Sitio Natural', descripcion: 'Corazón de la ciudad de Caaguazú', imagen: IMAGEN, padre: null, icono: '', color: '', marker: null, total: 1 } ];
const ETIQUETAS = [ { id: 70, slug: 'al-aire-libre', nombre: 'al aire libre', cuenta: 1 }, { id: 68, slug: 'gratis', nombre: 'gratis', cuenta: 1 } ];
const ITEM = { id: 260, tipo: 'destino', tipo_item: 'sitio', fechas: null, titulo: 'Ykua La Patria', categoria: { id: 66, slug: 'sitio-natural', nombre: 'Sitio Natural' }, etiquetas: ETIQUETAS, coordenadas: { lat: -25.472938, lng: -56.020984 }, google_maps: 'https://maps.app.goo.gl/9ZCqKsFdKy9tspcj8', portada: IMAGEN, rango_precio: 0, horario_resumen: 'Parque abierto, se visita de día.', idioma: 'es', traducido: false };
const FICHA = { ...ITEM, galeria: [], video: null, practicos: { horario: 'Parque abierto, se visita de día.', costo: 'Entrada libre.', rango_precio: 0, contacto: '' }, acceso: { estado_camino: 'asfalto' }, descripcion: '<p>Ykua La Patria es el manantial donde empezó Caaguazú.</p>', articulos_relacionados: [], fuentes: 'Reseña histórica de Caaguazú.', autor: null };
const IDIOMAS = { original: 'es', idiomas: [ { codigo: 'es', nombre: 'Español', original: true }, { codigo: 'en', nombre: 'English', original: false }, { codigo: 'pt', nombre: 'Português', original: false } ] };
const TIENDA_ANDROID = 'https://play.google.com/store/apps/details?id=py.caaguazu.turismo';

/** Un PNG de 388×220 hecho acá, para no depender de archivos ni de la red. */
function pngDePrueba() {
	const W = 388, H = 220;
	const filas = [];
	for ( let y = 0; y < H; y++ ) {
		const f = Buffer.alloc( 1 + W * 3 );
		for ( let x = 0; x < W; x++ ) {
			const c = y < 110 ? [ 120, 170, 205 ] : y < 160 ? [ 70, 130, 70 ] : [ 95, 75, 50 ];
			f[ 1 + x * 3 ] = c[ 0 ]; f[ 2 + x * 3 ] = c[ 1 ]; f[ 3 + x * 3 ] = c[ 2 ];
		}
		filas.push( f );
	}
	const trozo = ( tipo, datos ) => {
		const n = Buffer.alloc( 4 ); n.writeUInt32BE( datos.length );
		const c = Buffer.alloc( 4 ); c.writeUInt32BE( crc32( Buffer.concat( [ Buffer.from( tipo ), datos ] ) ) >>> 0 );
		return Buffer.concat( [ n, Buffer.from( tipo ), datos, c ] );
	};
	const ihdr = Buffer.alloc( 13 ); ihdr.writeUInt32BE( W, 0 ); ihdr.writeUInt32BE( H, 4 ); ihdr[ 8 ] = 8; ihdr[ 9 ] = 2;
	return Buffer.concat( [ Buffer.from( [ 0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a ] ), trozo( 'IHDR', ihdr ), trozo( 'IDAT', deflateSync( Buffer.concat( filas ) ) ), trozo( 'IEND', Buffer.alloc( 0 ) ) ] );
}
const FOTO = pngDePrueba();

const json = ( cuerpo, status = 200, headers = {} ) => ( { status, headers, contentType: 'application/json; charset=utf-8', body: JSON.stringify( cuerpo ) } );

/**
 * Pone la API y lo que arma el plugin. Opciones:
 *   tiendas   enlaces de tienda que devuelve ajustes.json
 *   asistente si GET /asistente dice disponible
 *   ajustesCuelga  ajustes.json NO responde nunca (mala señal)
 */
async function simular( p, { tiendas = { android: TIENDA_ANDROID, ios: '' }, asistente = true, ajustesCuelga = false } = {} ) {
	await p.route( API + '**', ( route ) => {
		const r = new URL( route.request().url() ).pathname.replace( '/wp-json/czu-app/v1/', '' );
		if ( r.startsWith( 'categorias' ) ) { return route.fulfill( json( CATEGORIAS ) ); }
		if ( r.startsWith( 'etiquetas' ) ) { return route.fulfill( json( ETIQUETAS ) ); }
		if ( r.startsWith( 'inventario/260' ) ) { return route.fulfill( json( FICHA ) ); }
		if ( r.startsWith( 'inventario' ) ) { return route.fulfill( json( { items: [ ITEM ], total: 1, pagina: 1, por_pagina: 20 } ) ); }
		if ( r.startsWith( 'eventos' ) ) { return route.fulfill( json( { items: [], total: 0, pagina: 1, por_pagina: 20 } ) ); }
		if ( r.startsWith( 'articulos' ) ) { return route.fulfill( json( { items: [], total: 0, pagina: 1, por_pagina: 20 } ) ); }
		if ( r.startsWith( 'idiomas' ) ) { return route.fulfill( json( IDIOMAS ) ); }
		if ( r.startsWith( 'strings' ) ) { return route.fulfill( json( {} ) ); }
		if ( r.startsWith( 'mapa/markers' ) ) { return route.fulfill( json( [ { id: 260, tipo: 'destino', tipo_item: 'sitio', lat: -25.472938, lng: -56.020984, categoria: 66 } ] ) ); }
		if ( r.startsWith( 'asistente' ) ) {
			return asistente ? route.fulfill( json( { disponible: true } ) ) : route.fulfill( json( { error: { codigo: 'no_encontrado' } }, 404 ) );
		}
		return route.fulfill( json( { error: { codigo: 'no_encontrado' } }, 404 ) );
	} );
	await p.route( '**/ajustes.json', ( route ) => {
		if ( ajustesCuelga ) { return; } // no responde: el pedido queda abierto
		return route.fulfill( json( { version: '2.0.0', api: API, tiendas } ) );
	} );
	await p.route( /caaguazu\.net\/wp-content\/uploads\//, ( route ) => route.fulfill( { status: 200, contentType: 'image/png', body: FOTO } ) );
	await p.route( /cdnjs\.cloudflare\.com|tile\.openstreetmap\.org|fonts\.g/, ( route ) => route.abort() );
}

/* ---------------------------------------------------------------------------
 * Navegador
 * ------------------------------------------------------------------------ */

const ejecutable = process.env.PLAYWRIGHT_CHROMIUM || '/opt/pw-browsers/chromium';
const navegador = await chromium.launch( existsSync( ejecutable ) ? { executablePath: ejecutable, args: [ '--no-sandbox' ] } : { args: [ '--no-sandbox' ] } );

async function pagina( { ancho = 390, ...resto } = {}, opciones = {} ) {
	const ctx = await navegador.newContext( { viewport: { width: ancho, height: 844 }, locale: 'es-AR', hasTouch: true, isMobile: true, ...resto } );
	const p = await ctx.newPage();
	const errores = [];
	p.on( 'pageerror', ( e ) => errores.push( e.message ) );
	await simular( p, opciones );
	return { ctx, p, errores };
}
const ir = async ( p, hash, espera = 1200 ) => { await p.goto( BASE + hash, { waitUntil: 'load' } ); await p.waitForTimeout( espera ); };

/* ---------------------------------------------------------------------------
 * 1. Cada pantalla: sin desborde, nada táctil chico, sin claves crudas
 * ------------------------------------------------------------------------ */

seccion( 'Pantallas: desborde, áreas táctiles y textos' );

const PANTALLAS = [ '#/inicio', '#/buscar', '#/ficha/260', '#/app', '#/ajustes', '#/guardados', '#/articulos', '#/asistente' ];

for ( const ancho of [ 390, 360 ] ) {
	for ( const tema of [ 'light', 'dark' ] ) {
		const { ctx, p, errores } = await pagina( { ancho, colorScheme: tema } );
		const problemas = [];
		for ( const hash of PANTALLAS ) {
			await ir( p, hash, 900 );
			const r = await p.evaluate( () => {
				const w = document.documentElement.clientWidth;
				const chicos = [ ...document.querySelectorAll( 'a, button, input' ) ].filter( ( e ) => {
					const c = e.getBoundingClientRect();
					if ( ! c.width || ! c.height || e.type === 'hidden' ) { return false; }
					// «Saltar al contenido» vive fuera de la pantalla y sólo se muestra
					// al enfocarlo con Tab: es para teclado, no se toca con el dedo.
					if ( e.classList.contains( 'saltar' ) ) { return false; }
					// Un enlace dentro de una tarjeta o fila es todo el bloque, no el texto.
					if ( e.closest( '.tarjeta, .fila, .globo, .aviso' ) && e.tagName === 'A' && ! e.classList.contains( 'chip' ) ) { return false; }
					return c.height < 43;
				} ).map( ( e ) => ( e.className || e.tagName ) + ' ' + Math.round( e.getBoundingClientRect().height ) );
				return {
					desborde: document.scrollingElement.scrollWidth - w,
					chicos: [ ...new Set( chicos ) ],
					crudas: [ ...document.body.innerText.matchAll( /\b(?:web|nav|perfil|filtro|ficha|estado|evento|principal|inv|diag|banda|accion|barra|precio|rec|mapa)\.[a-zA-Z]+(?:\.[a-zA-Z]+)*/g ) ].map( ( m ) => m[ 0 ] ),
				};
			} );
			if ( r.desborde > 0 ) { problemas.push( `${ hash } se sale ${ r.desborde }px` ); }
			if ( r.chicos.length ) { problemas.push( `${ hash } táctil < 44px: ${ r.chicos.slice( 0, 4 ).join( ', ' ) }` ); }
			if ( r.crudas.length ) { problemas.push( `${ hash } claves sin traducir: ${ r.crudas.join( ',' ) }` ); }
		}
		ok( `${ PANTALLAS.length } pantallas a ${ ancho}px, ${ tema === 'dark' ? 'oscuro' : 'claro' }`, problemas.length === 0 && errores.length === 0, problemas.concat( errores ).join( ' | ' ) );
		await ctx.close();
	}
}

// Los tres idiomas: ninguna clave sin traducir (la barra y una pantalla).
for ( const idioma of [ 'es', 'en', 'pt' ] ) {
	const { ctx, p } = await pagina( { locale: idioma } );
	await p.addInitScript( ( i ) => localStorage.setItem( 'czu.idioma', i ), idioma );
	await ir( p, '#/inicio' );
	const crudas = await p.evaluate( () => [ ...document.body.innerText.matchAll( /\b(?:web|nav|perfil|filtro|ficha|estado|evento|principal|inv|diag|banda|accion|barra|precio|rec|mapa)\.[a-zA-Z]+(?:\.[a-zA-Z]+)*/g ) ].map( ( m ) => m[ 0 ] ) );
	ok( `inicio en «${ idioma }» sin claves sin traducir`, crudas.length === 0, crudas.join( ',' ) );
	await ctx.close();
}

/* ---------------------------------------------------------------------------
 * 2. Lo fijo se ancla a la ventana
 * ------------------------------------------------------------------------ */

seccion( 'Lo fijo se ancla a la ventana' );
{
	const { ctx, p } = await pagina();
	await ir( p, '#/asistente' );
	const caja = await p.locator( '#redactar' ).boundingBox();
	ok( 'la barra del asistente queda sobre la barra de navegación, abajo de todo', caja && caja.y > 844 - 200 && caja.y + caja.height < 844, JSON.stringify( caja ) );
	await ir( p, '#/mapa' );
	const mapa = await p.locator( '.pantalla-mapa' ).boundingBox();
	ok( 'la capa del mapa cubre toda la ventana', mapa && Math.round( mapa.width ) === 390 && Math.round( mapa.height ) === 844, JSON.stringify( mapa ) );
	ok( 'en el mapa se oculta la barra de navegación', await p.locator( '#barra' ).isHidden() );
	await ir( p, '#/buscar' );
	await p.locator( '#abrir-filtros' ).click();
	await p.waitForTimeout( 700 );
	const hoja = await p.locator( '.hoja' ).boundingBox();
	ok( 'la hoja de filtros termina en el borde inferior', hoja && Math.round( hoja.y + hoja.height ) === 844, JSON.stringify( hoja ) );
	ok( '«Ver resultados» no queda tapado', await p.evaluate( () => { const b = document.querySelector( '[data-aplicar]' ).getBoundingClientRect(); return !! document.elementFromPoint( b.x + b.width / 2, b.y + b.height / 2 ).closest( '[data-aplicar]' ); } ) );
	await p.keyboard.press( 'Escape' );
	await p.waitForTimeout( 400 );
	ok( 'Escape cierra la hoja', await p.locator( '.hoja' ).count() === 0 );
	await ctx.close();
}

/* ---------------------------------------------------------------------------
 * 3. Arranque con mala señal
 * ------------------------------------------------------------------------ */

seccion( 'Arranque con mala señal' );
{
	const { ctx, p, errores } = await pagina( {}, { ajustesCuelga: true } );
	const t0 = Date.now();
	await p.goto( BASE + '#/inicio', { waitUntil: 'load' } );
	await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } ).catch( () => {} );
	const seg = ( Date.now() - t0 ) / 1000;
	ok( 'si ajustes.json no responde, la web abre igual (con tope de tiempo)', ( await p.locator( '#barra .barra__item' ).count() ) > 0 && seg < 8, `${ seg.toFixed( 1 ) }s` );
	await p.waitForTimeout( 800 );
	ok( 'y muestra contenido, no una pantalla en blanco', ( await p.locator( '#contenido' ).innerText() ).trim().length > 0 );
	ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
	await ctx.close();
}

/* ---------------------------------------------------------------------------
 * 4. Favoritos y tema
 * ------------------------------------------------------------------------ */

seccion( 'Favoritos y tema' );
{
	const { ctx, p, errores } = await pagina();
	await ir( p, '#/inicio' );
	await p.locator( '.grilla .corazon' ).first().click();
	await p.waitForTimeout( 300 );
	ok( 'guardar con el corazón no abre la ficha', ( await p.evaluate( () => location.hash ) ) === '#/inicio' );
	ok( 'queda guardado en el teléfono', ( await p.evaluate( () => localStorage.getItem( 'czu.favoritos' ) ) ) === '[260]' );
	await ir( p, '#/guardados' );
	ok( 'Guardados muestra la ficha', ( await p.locator( '.fila__titulo' ).first().textContent() ).includes( 'Ykua' ) );
	await p.locator( '[data-quitar]' ).first().click();
	await p.waitForTimeout( 200 );
	ok( 'quitarla deja el estado vacío', await p.locator( '.vacio' ).count() === 1 );
	ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
	await ctx.close();
}
{
	const { ctx, p } = await pagina( { colorScheme: 'light' } );
	await ir( p, '#/ajustes' );
	await p.locator( '[data-tema]' ).click();
	await p.locator( '[data-elegir="oscuro"]' ).click();
	await p.waitForTimeout( 500 );
	ok( 'elegir Oscuro cambia el tema', ( await p.evaluate( () => document.documentElement.dataset.theme ) ) === 'dark' );
	await p.reload( { waitUntil: 'load' } );
	await p.waitForTimeout( 700 );
	ok( 'el tema elegido sobrevive a recargar', ( await p.evaluate( () => document.documentElement.dataset.theme ) ) === 'dark' );
	await ctx.close();
}

/* ---------------------------------------------------------------------------
 * 5. Asistente
 * ------------------------------------------------------------------------ */

seccion( 'Asistente' );
{
	const { ctx, p, errores } = await pagina();
	let n = 0, segundo = null;
	await p.route( API + 'asistente', ( route ) => {
		if ( route.request().method() === 'GET' ) { return route.fulfill( json( { disponible: true } ) ); }
		n++;
		if ( n === 1 ) {
			return route.fulfill( json( { respuesta: 'Podés visitar Ykua La Patria.', fuentes: [ { tipo: 'ficha', id: 260, titulo: 'Ykua La Patria', tipo_item: 'sitio' }, { tipo: 'recorrido', id: 9, titulo: 'Ruta fundacional' } ], enlaces: [ { titulo: 'Diario', url: 'https://diario.com.py/museo' }, { titulo: 'Malo', url: 'javascript:alert(1)' } ], conversacion: 'abc12345', idioma: 'es' } ) );
		}
		segundo = JSON.parse( route.request().postData() );
		return route.fulfill( json( { error: { codigo: 'muchos_pedidos', mensaje: 'x', detalle: { espera_seg: 30 } } }, 429, { 'Retry-After': '30' } ) );
	} );
	await ir( p, '#/asistente' );
	ok( 'la pestaña aparece cuando la API lo tiene prendido', await p.locator( '#barra a[href="#/asistente"]' ).count() === 1 );
	await p.locator( '[data-ejemplo]' ).first().click();
	await p.waitForSelector( '.globo--ia:not(.escribiendo)', { timeout: 5000 } );
	ok( 'muestra la respuesta', ( await p.locator( '.globo--ia' ).first().textContent() ).includes( 'Ykua La Patria' ) );
	const fuentes = await p.locator( '.globo__fuentes a:not([target])' ).evaluateAll( ( as ) => as.map( ( a ) => a.getAttribute( 'href' ) ) );
	ok( 'las fuentes llevan a la ficha y, un recorrido, a la app', JSON.stringify( fuentes ) === '["#/ficha/260","#/app"]', JSON.stringify( fuentes ) );
	const externos = await p.locator( '.globo__fuentes a[target="_blank"]' ).evaluateAll( ( as ) => as.map( ( a ) => a.getAttribute( 'href' ) + '|' + a.getAttribute( 'rel' ) ) );
	ok( 'los enlaces de internet abren aparte, y uno que no es http(s) no se muestra', JSON.stringify( externos ) === '["https://diario.com.py/museo|noopener noreferrer"]', JSON.stringify( externos ) );
	await p.locator( '#pregunta' ).fill( '¿y cómo llego?' );
	await p.locator( '#redactar button[type=submit]' ).click();
	await p.waitForSelector( '.globo--error', { timeout: 5000 } );
	ok( 'la segunda pregunta manda el id de la conversación', segundo?.conversacion === 'abc12345', JSON.stringify( segundo ) );
	ok( 'el 429 dice cuánto esperar', ( await p.locator( '.globo--error' ).textContent() ).includes( '30 segundos' ) );
	await p.locator( '.globo__fuentes a:not([target])' ).first().click();
	await p.waitForTimeout( 600 );
	await p.goBack();
	await p.waitForTimeout( 800 );
	ok( 'la charla sigue al volver de una fuente', ( await p.locator( '.globo' ).count() ) >= 3 );
	ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
	await ctx.close();
}
{
	const { ctx, p } = await pagina( {}, { asistente: false } );
	await ir( p, '#/inicio' );
	ok( 'sin asistente: ni pestaña ni tarjeta', await p.locator( '#barra a[href="#/asistente"]' ).count() === 0 && await p.locator( 'a[href="#/asistente"]' ).count() === 0 );
	await ir( p, '#/asistente' );
	ok( 'entrar a mano a /asistente lo explica en vez de romperse', await p.locator( '.vacio' ).count() === 1 );
	await ctx.close();
}

/* ---------------------------------------------------------------------------
 * 6. Direcciones de 1.x y tiendas
 * ------------------------------------------------------------------------ */

seccion( 'Direcciones de 1.x y tiendas' );
{
	const { ctx, p } = await pagina();
	await ir( p, '#/recorridos' );
	ok( '#/recorridos lleva a la pantalla de la app', ( await p.evaluate( () => location.hash ) ) === '#/app' );
	await ir( p, '#/recorrido/12' );
	ok( '#/recorrido/12 también', ( await p.evaluate( () => location.hash ) ) === '#/app' );
	await ir( p, '#/perfil' );
	ok( '#/perfil lleva a Ajustes', ( await p.evaluate( () => location.hash ) ) === '#/ajustes' );
	await ir( p, '#/lo-que-sea' );
	ok( 'una dirección desconocida vuelve al inicio', ( await p.evaluate( () => location.hash ) ) === '#/inicio' );
	await ctx.close();
}
{
	const iphone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1';
	const a = await pagina( { userAgent: iphone } );
	await ir( a.p, '#/app' );
	ok( 'iPhone sin App Store: se avisa que todavía no está', ( await a.p.locator( '.debil' ).allTextContents() ).some( ( t ) => /todavía no está en la tienda de tu teléfono/.test( t ) ) );
	ok( 'y se ofrece igual el enlace de Android', await a.p.locator( '.tienda' ).count() === 1 );
	await a.ctx.close();
	const b = await pagina( {}, { tiendas: { android: '', ios: '' } } );
	await ir( b.p, '#/app' );
	ok( 'sin tiendas cargadas: un aviso, ningún botón', await b.p.locator( '.tienda' ).count() === 0 && await b.p.locator( '.vacio' ).count() === 1 );
	await b.ctx.close();
	const c = await pagina( {}, { tiendas: { android: TIENDA_ANDROID, ios: 'https://apps.apple.com/app/id1' } } );
	await ir( c.p, '#/app' );
	const clases = await c.p.locator( '.tienda' ).evaluateAll( ( es ) => es.map( ( e ) => e.className ) );
	ok( 'con las dos tiendas, la primera es el botón fuerte y la otra va de costado', clases.length === 2 && ! /tienda--otra/.test( clases[ 0 ] ) && /tienda--otra/.test( clases[ 1 ] ), JSON.stringify( clases ) );
	await c.ctx.close();
}

/* ---------------------------------------------------------------------------
 * 7. Compartir y mapa
 * ------------------------------------------------------------------------ */

seccion( 'Compartir y mapa' );
{
	const { ctx, p, errores } = await pagina();
	await p.addInitScript( () => Object.defineProperty( navigator, 'share', { value: undefined, configurable: true } ) );
	await ir( p, '#/ficha/260' );
	await p.locator( '#ficha-compartir' ).click();
	await p.waitForSelector( '.hoja .qr svg', { timeout: 4000 } ).catch( () => {} );
	ok( 'compartir sin Web Share abre la hoja con el QR', await p.locator( '.hoja .qr svg' ).count() === 1 );
	ok( 'el QR va sobre blanco', await p.evaluate( () => getComputedStyle( document.querySelector( '.qr__papel' ) ).backgroundColor ) === 'rgb(255, 255, 255)' );
	ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
	await ctx.close();
}
{
	const { ctx, p, errores } = await pagina();
	const pedidos = [];
	await p.route( /cdnjs\.cloudflare\.com\/ajax\/libs\/leaflet\/1\.9\.4\/leaflet\.min\.(js|css)/, ( route ) => {
		const u = route.request().url();
		pedidos.push( u.split( '/' ).pop() );
		if ( u.endsWith( '.css' ) ) { return route.fulfill( { status: 200, contentType: 'text/css', body: '' } ); }
		// Doble mínimo: registra lo que hace el código de la web. NO es Leaflet.
		return route.fulfill( { status: 200, contentType: 'text/javascript', body: `
			window.__llamadas = [];
			const reg = ( n ) => () => { window.__llamadas.push( n ); return mapa; };
			const mapa = { setView: reg( 'setView' ), fitBounds: reg( 'fitBounds' ), on() { return this; }, addTo() { return this; } };
			window.L = { map: () => mapa, tileLayer: () => ( { addTo() { return this; } } ), control: { zoom: () => ( { addTo() { return this; } } ) }, divIcon: ( o ) => o,
				marker: () => { const m = { addTo() { return m; }, on( ev, fn ) { window.__clickPin = fn; return m; } }; window.__llamadas.push( 'marker' ); return m; } };` } );
	} );
	await ir( p, '#/inicio' );
	ok( 'Leaflet no se pide al abrir Inicio', pedidos.length === 0, pedidos.join() );
	await p.evaluate( () => { location.hash = '#/mapa'; } );
	await p.waitForTimeout( 1500 );
	ok( 'se pide recién al abrir el mapa', pedidos.sort().join() === 'leaflet.min.css,leaflet.min.js', pedidos.join() );
	ok( 'un pin por lugar', ( await p.evaluate( () => window.__llamadas ) ).filter( ( x ) => x === 'marker' ).length === 1 );
	await p.evaluate( () => window.__clickPin() );
	await p.waitForSelector( '.tarjeta-pin a', { timeout: 3000 } );
	ok( 'tocar un pin abre la tarjeta, que lleva a la ficha', ( await p.locator( '.tarjeta-pin a' ).getAttribute( 'href' ) ) === '#/ficha/260' );
	ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
	await ctx.close();
}

/* ---------------------------------------------------------------------------
 * 5. El plugin de verdad: arranque, versiones y caché
 * ------------------------------------------------------------------------ */

seccion( 'Arranque y caché con el plugin de verdad (PHP)' );
if ( spawnSync( 'php', [ '-v' ] ).status !== 0 ) {
	console.log( `${ gris }  (sin php en el equipo: se salta)${ fin }` );
} else {
	const DIRECTA = '/wp-content/plugins/caaguazu-web-ios/sitio/';

	/** Levanta el plugin de verdad (sin WordPress) en un puerto libre. */
	async function levantarPhp( env = {} ) {
		const puerto = await new Promise( ( r ) => { const t = servidorTcp(); t.listen( 0, '127.0.0.1', () => { const n = t.address().port; t.close( () => r( n ) ); } ); } );
		const registro = join( tmpdir(), `probar-web-${ puerto }.log` );
		writeFileSync( registro, '' );
		writeFileSync( join( tmpdir(), `czu-limite-${ puerto }.cnt` ), '0' );
		// WEB_LATENCIA_MS: lo que cuesta arrancar WordPress en un hosting compartido.
		const hijo = spawn( 'php', [ '-S', `127.0.0.1:${ puerto }`, join( aqui, 'web-prueba', 'router.php' ) ], { stdio: 'ignore', env: { ...process.env, PHP_CLI_SERVER_WORKERS: '12', WEB_LATENCIA_MS: '120', WEB_ASISTENTE: '0', WEB_REGISTRO: registro, ...env } } );
		for ( let i = 0; i < 50; i++ ) { try { await fetch( `http://127.0.0.1:${ puerto }/turismo/` ); break; } catch { await new Promise( ( r ) => setTimeout( r, 100 ) ); } }
		writeFileSync( registro, '' );
		// Lo que llegó de verdad al servidor (y no lo que contestó el caché del
		// navegador): ver WEB_REGISTRO en router.php.
		const pedidos = () => ( existsSync( registro ) ? readFileSync( registro, 'utf8' ).split( '\n' ).filter( Boolean ).map( ( l ) => { const [ estado, , ruta ] = l.split( ' ' ); return { estado, ruta }; } ) : [] );
		return { PHP: `http://127.0.0.1:${ puerto }`, pedidos, matar: () => hijo.kill() };
	}

	const abrir = async ( opciones = {} ) => {
		const ctx = await navegador.newContext( { viewport: { width: 390, height: 844 }, locale: 'es-AR', hasTouch: true, isMobile: true, ...opciones } );
		const p = await ctx.newPage();
		const errores = [];
		p.on( 'pageerror', ( e ) => errores.push( e.message ) );
		return { ctx, p, errores };
	};
	const esCodigo = ( ruta ) => /\/(js|css)\//.test( ruta );
	const esModulo = ( ruta ) => /\/js\/.+\.js(\?|$)/.test( ruta ) && ! /vendor\// .test( ruta );

	const srv = await levantarPhp();
	const { PHP, pedidos: pedidosAlServidor } = srv;

	// --- Primera visita -------------------------------------------------------
	{
		const { ctx, p, errores } = await abrir();
		const vistos = [];
		p.on( 'request', ( r ) => { const u = new URL( r.url() ); if ( u.origin === PHP ) { vistos.push( u.pathname + u.search ); } } );
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } );
		await p.waitForTimeout( 800 );

		ok( 'la app arranca y reemplaza la pantalla de «Cargando…»', await p.locator( '#arranque' ).count() === 0 && await p.locator( '.pantalla' ).count() === 1 );
		ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );

		const codigo = vistos.filter( esCodigo );
		ok( 'todo el código se pide con su versión en la URL', codigo.length > 15 && codigo.every( ( r ) => /\?v=\d+\.\d+\.\d+$/.test( r ) ), codigo.filter( ( r ) => ! /\?v=/.test( r ) ).join( ',' ) );
		ok( 'y TODO el código sale de la carpeta del plugin, sin pasar por PHP/WordPress', codigo.length > 15 && codigo.every( ( r ) => r.startsWith( DIRECTA ) ), codigo.filter( ( r ) => ! r.startsWith( DIRECTA ) ).join( ',' ) );
		const repetidos = Object.entries( codigo.reduce( ( a, r ) => ( ( a[ r ] = ( a[ r ] || 0 ) + 1 ), a ), {} ) ).filter( ( [ , n ] ) => n > 1 ).map( ( [ r ] ) => r );
		ok( 'ningún módulo se baja dos veces (con dos URLs distintas se ejecutaría dos veces)', repetidos.length === 0, repetidos.join( ',' ) );

		const html = await ( await fetch( PHP + '/turismo/' ) ).text();
		const precargados = new Set( [ ...html.matchAll( /rel="modulepreload" href="([^"]+)"/g ) ].map( ( m ) => m[ 1 ] ) );
		const sinPrecarga = vistos.filter( esModulo ).filter( ( r ) => ! precargados.has( r ) );
		ok( 'la precarga cubre todos los módulos que la app necesitó (si no, bajan en fila)', precargados.size > 15 && sinPrecarga.length === 0, sinPrecarga.join( ',' ) );

		// Lo que SÍ pasa por PHP/WordPress: tiene que ser poco, porque es lo que se cae.
		// (/uploads/ son las fotos de la API de prueba, no de este plugin.)
		const porPhp = vistos.filter( ( r ) => ! r.startsWith( DIRECTA ) && ! r.includes( '/wp-json/' ) && ! r.startsWith( '/uploads/' ) );
		ok( 'por PHP pasa sólo la página (y el manifest, que el navegador pide aparte)', porPhp.every( ( r ) => r === '/turismo/' || r.endsWith( 'manifest.webmanifest' ) ), porPhp.join( ',' ) );
		ok( 'los ajustes viajan en la página: ningún ajustes.json aparte', ! vistos.some( ( r ) => r.includes( 'ajustes.json' ) ) );
		ok( 'los textos y el ícono salen de la carpeta directa, con versión', vistos.filter( ( r ) => /textos\/es\.json|icon-192\.png/.test( r ) ).every( ( r ) => r.startsWith( DIRECTA ) ), vistos.filter( ( r ) => /textos\/|icon-192/.test( r ) ).join( ',' ) );
		await ctx.close();
	}

	// --- Segunda visita: nada de código vuelve al servidor --------------------
	{
		const { ctx, p } = await abrir();
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } );
		const antes = pedidosAlServidor().length;
		// Otra URL de la página, mismas URLs de código: una visita nueva, no un recargar.
		await p.goto( PHP + '/turismo/?visita=2#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } );
		await p.waitForTimeout( 500 );
		const segunda = pedidosAlServidor().slice( antes ).map( ( x ) => x.ruta );
		const codigo = segunda.filter( esCodigo );
		ok( 'en la segunda visita ningún js ni css vuelve al servidor', codigo.length === 0, codigo.slice( 0, 4 ).join( ',' ) );
		ok( 'sí se revalida la página (lo único que puede cambiar sin cambiar de URL)', segunda.some( ( r ) => r.startsWith( '/turismo/?visita=2' ) ), segunda.slice( 0, 6 ).join( ',' ) );
		await ctx.close();
	}

	// --- Revalidación: el ETag que vuelve cambiado por el hosting -------------
	{
		const url = PHP + '/turismo/js/app.js';
		const etag = ( await fetch( url ) ).headers.get( 'etag' );
		const estado = async ( cabecera ) => ( await fetch( url, { headers: { 'If-None-Match': cabecera } } ) ).status;
		ok( 'ETag exacto → 304', await estado( etag ) === 304 );
		ok( 'ETag vuelto débil por el servidor (W/"…") → 304', await estado( 'W/' + etag ) === 304 );
		ok( 'ETag con sufijo de compresión → 304', await estado( etag.replace( /"$/, '-gzip"' ) ) === 304 );
		ok( 'otro ETag → 200 con el archivo', await estado( '"otro"' ) === 200 );
		const version = ( await ( await fetch( PHP + '/turismo/ajustes.json' ) ).json() ).version;
		ok( 'por PHP, el código con su versión se cachea «para siempre»', /immutable/.test( ( await fetch( `${ url }?v=${ version }` ) ).headers.get( 'cache-control' ) ) );
		ok( 'una versión ajena no recibe esa promesa', ! /immutable/.test( ( await fetch( url + '?v=0.0.1' ) ).headers.get( 'cache-control' ) ) );
		// La página se revalida por LO QUE ENVÍA: si cambian los ajustes de wp-admin
		// sin tocar ningún archivo, un 304 la dejaría con los enlaces viejos.
		const pagina = await fetch( PHP + '/turismo/' );
		ok( 'la página trae un ETag y se revalida', Boolean( pagina.headers.get( 'etag' ) ) && ( await fetch( PHP + '/turismo/', { headers: { 'If-None-Match': pagina.headers.get( 'etag' ) } } ) ).status === 304 );
	}

	// --- Navegador sin import maps: la ruta por PHP, con versión en cada import
	{
		const { ctx, p, errores } = await abrir();
		await p.addInitScript( () => { Object.defineProperty( HTMLScriptElement, 'supports', { value: () => false } ); } );
		const vistos = [];
		p.on( 'request', ( r ) => { const u = new URL( r.url() ); if ( u.origin === PHP && esModulo( u.pathname ) ) { vistos.push( u.pathname + u.search ); } } );
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } );
		const porPhp = vistos.filter( ( r ) => r.startsWith( '/turismo/js/' ) );
		ok( 'sin import maps la app arranca igual, por la ruta de PHP', await p.locator( '#arranque' ).count() === 0 && porPhp.length > 10, `${ porPhp.length } módulos por PHP` );
		ok( 'y cada import lleva su versión (PHP se la pone)', porPhp.every( ( r ) => /\?v=\d+\.\d+\.\d+$/.test( r ) ), porPhp.filter( ( r ) => ! /\?v=/.test( r ) ).join( ',' ) );
		ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
		await ctx.close();
	}

	// --- La pantalla de arranque ---------------------------------------------
	{
		const { ctx, p } = await abrir();
		// app.js tarda: lo que se ve mientras tanto NO es una pantalla vacía.
		await p.route( '**/js/app.js*', async ( route ) => { await new Promise( ( r ) => setTimeout( r, 1800 ) ); await route.continue(); } );
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'commit' } );
		await p.waitForTimeout( 900 );
		ok( 'mientras la app carga hay una pantalla de «Cargando…», no un fondo liso', /Cargando/.test( await p.locator( '#contenido' ).innerText() ) );
		ok( 'y la barra vacía no se ve como una pastilla huérfana', await p.locator( '#barra' ).isHidden() );
		await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } );
		ok( 'cuando la app arranca, la pantalla de arranque desaparece', await p.locator( '#arranque' ).count() === 0 );
		await ctx.close();
	}
	{
		const { ctx, p } = await abrir();
		// El script de la app no carga (bloqueado, sin red, hosting caído).
		await p.route( '**/js/app.js*', ( route ) => route.abort() );
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#arranque-fallo:not([hidden])', { timeout: 3000 } ).catch( () => {} );
		const visible = await p.locator( '#arranque-fallo' ).isVisible();
		ok( 'si la app no carga, la página lo dice en vez de quedar vacía', visible );
		const boton = await p.locator( '#arranque-reintentar' ).boundingBox();
		ok( 'ofrece reintentar, con un botón táctil de 44px', Boolean( boton ) && boton.height >= 44, JSON.stringify( boton ) );
		await p.locator( '#arranque summary' ).click();
		const informe = await p.locator( '#arranque-informe' ).innerText();
		ok( 'el informe dice qué archivo no cargó y con qué navegador (para quien lo mire)', /js\/app\.js/.test( informe ) && /Mozilla/.test( informe ), informe.slice( 0, 160 ) );
		ok( 'en el idioma del teléfono', /No se pudo abrir la web/.test( await p.locator( '#arranque-titulo' ).innerText() ) );
		await ctx.close();
	}
	{
		const { ctx, p } = await abrir( { locale: 'pt-BR' } );
		await p.addInitScript( () => { try { localStorage.setItem( 'czu.idioma', 'pt' ); } catch {} } );
		await p.route( '**/js/app.js*', ( route ) => route.abort() );
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#arranque-fallo:not([hidden])', { timeout: 3000 } ).catch( () => {} );
		ok( 'y en portugués si ése es el idioma elegido', /Não foi possível abrir/.test( await p.locator( '#arranque-titulo' ).innerText() ) );
		await ctx.close();
	}
	{
		const { ctx, p } = await abrir();
		// El script nunca responde: ni error ni éxito. Es el caso que más cuesta
		// ver desde afuera, y el reloj falso evita esperar 15 segundos de verdad.
		await p.clock.install();
		await p.route( '**/js/app.js*', () => {} );
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'commit' } );
		// Hay que esperar a que el vigilante exista: si no, los timers que
		// adelanta el reloj todavía no están programados.
		await p.waitForFunction( () => window.czuArranque );
		await p.clock.fastForward( 6000 );
		ok( 'a los pocos segundos avisa que está tardando', /tardando/.test( await p.locator( '#arranque-texto' ).innerText() ) );
		ok( 'pero todavía no da la web por perdida', await p.locator( '#arranque-fallo' ).isHidden() );
		await p.clock.fastForward( 10000 );
		ok( 'si sigue sin arrancar, muestra el fallo y el botón', await p.locator( '#arranque-fallo' ).isVisible() && await p.locator( '#arranque-reintentar' ).isVisible() );
		await ctx.close();
	}

	// --- Los textos que edita el panel no frenan la web -----------------------
	{
		const { ctx, p, errores } = await abrir();
		// /strings no responde: depende de la API, y la API puede tardar.
		await p.route( '**/wp-json/czu-app/v1/strings/**', () => {} );
		const t0 = Date.now();
		await p.goto( PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#barra .barra__item', { timeout: 9000 } );
		const seg = ( Date.now() - t0 ) / 1000;
		ok( 'si /strings no responde, la web abre igual en pocos segundos', seg < 4, `${ seg.toFixed( 1 ) }s` );
		ok( 'con los textos que trae la propia web, no con claves crudas', ! /\b(?:web|nav|barra)\.[a-z]+/.test( await p.locator( '#barra' ).innerText() ) );
		ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
		await ctx.close();
	}
	srv.matar();

	// --- El hosting al límite: la falla de producción -------------------------
	//
	// Lo que pasaba: cada archivo pasaba por WordPress y el hosting, con un tope
	// de conexiones a la base de datos, contestaba «Database Error» (500) a los
	// que sobraban. A la web le faltaban archivos al azar y quedaba en blanco.
	// Acá el tope es de 2 pedidos a la vez por «WordPress» (todo menos la
	// carpeta del plugin) y cada uno tarda 150 ms: la ráfaga de una web de ~25
	// archivos lo pasa de lejos si el código no se sirve directo.
	{
		const lim = await levantarPhp( { WEB_LIMITE_PHP: '2', WEB_LATENCIA_MS: '150' } );
		const { ctx, p, errores } = await abrir();
		const respuestas = [];
		p.on( 'response', ( r ) => { if ( r.url().startsWith( lim.PHP ) ) { respuestas.push( { estado: r.status(), ruta: new URL( r.url() ).pathname } ); } } );
		await p.goto( lim.PHP + '/turismo/#/inicio', { waitUntil: 'load' } );
		await p.waitForSelector( '#barra .barra__item', { timeout: 12000 } ).catch( () => {} );
		await p.waitForTimeout( 5000 ); // alcanza para los reintentos de la API
		const codigo = respuestas.filter( ( r ) => esCodigo( r.ruta ) );
		ok( 'con el hosting al límite, ningún archivo de código falla', codigo.length > 15 && codigo.every( ( r ) => r.estado === 200 ), codigo.filter( ( r ) => r.estado !== 200 ).map( ( r ) => r.estado + ' ' + r.ruta ).join( ',' ) );
		ok( 'la app arranca', await p.locator( '#arranque' ).count() === 0 && await p.locator( '#barra .barra__item' ).count() > 0 );
		const fallos = respuestas.filter( ( r ) => r.estado >= 500 );
		console.log( `${ gris }       (500 que dio el hosting simulado, todos de la API: ${ fallos.length })${ fin }` );
		// Home pide cuatro cosas a la vez y arma lo que le llega: sin reintentos, un 500
		// hace desaparecer una sección SIN avisar (ni error ni hueco). Por eso se
		// mira que estén las dos que siempre hay.
		ok( 'aunque la API tropiece, Inicio termina completo gracias a los reintentos (categorías y lugares)', await p.locator( '.carril' ).count() === 1 && await p.locator( '.grilla' ).count() === 1 && await p.locator( '[data-reintentar]' ).count() === 0, ( await p.locator( '#contenido' ).innerText() ).slice( 0, 100 ).replace( /\n/g, ' ' ) );
		ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
		await ctx.close();
		lim.matar();
	}

	// --- La vista previa de lo no publicado (2.1.0) ---------------------------
	//
	// La cookie `czu_revisa` es la pista que deja el panel; el servidor de
	// prueba la toma además como la sesión del profesor (WEB_BORRADORES=1).
	const pre = await levantarPhp( { WEB_PRESENTACION: '1', WEB_BORRADORES: '1', WEB_LATENCIA_MS: '0' } );
	{
		const { ctx, p, errores } = await abrir();
		let pidio = false;
		p.on( 'request', ( r ) => { if ( r.url().includes( '/borradores' ) ) { pidio = true; } } );
		await p.goto( pre.PHP + '/turismo/#/inicio' );
		await p.waitForSelector( '.grilla', { timeout: 9000 } );
		ok( 'un turista no ve borradores', await p.locator( '#borradores' ).count() === 0 );
		ok( 'y su visita no le pide nada de más a la API', ! pidio );
		ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
		await ctx.close();
	}
	{
		const { ctx, p, errores } = await abrir();
		await ctx.addCookies( [ { name: 'czu_revisa', value: '1', url: pre.PHP } ] );
		await p.goto( pre.PHP + '/turismo/#/inicio' );
		await p.waitForSelector( '#borradores', { timeout: 9000 } ).catch( () => {} );
		ok( 'el profesor ve sus borradores en el inicio, con sello', await p.locator( '#borradores .sello--borrador' ).count() > 0 );
		await p.locator( '#borradores a[href="#/ficha/260?borrador=1"]' ).click();
		await p.waitForSelector( '.ficha__titulo', { timeout: 9000 } );
		ok( 'la ficha en borrador se abre, con sello y sin compartir', await p.locator( '.portada .sello--borrador' ).count() === 1 && await p.locator( '#ficha-compartir' ).count() === 0 );
		ok( 'sin errores de página', errores.length === 0, errores.join( '|' ) );
		await ctx.close();
	}

	// --- La presentación: la esfera y el teléfono como micrófono -------------
	//
	// Chromium sin red no transcribe: un dictado falso devuelve una frase fija,
	// con un parcial antes, como el de verdad. La API de prueba contesta sin
	// audio, así que también se ejercita el plan B de la voz del navegador.
	/**
	 * Un dedo apoyado en la pantalla del teléfono, con toques de verdad (CDP):
	 * pasan por touch-action y los pointer events como en un celular. Devuelve
	 * con qué levantarlo.
	 */
	const apoyarDedo = async ( p, { x = 195, y = 420 } = {} ) => {
		// «Empezar» termina de arrancar un instante después del toque.
		await p.waitForFunction( () => document.body.dataset.estado && document.body.dataset.estado !== 'inicio', null, { timeout: 9000 } ).catch( () => {} );
		const cdp = await p.context().newCDPSession( p );
		await cdp.send( 'Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [ { x, y } ] } );
		return async () => {
			await cdp.send( 'Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] } );
			await cdp.detach();
		};
	};
	const mantenerApretado = async ( p, ms, donde ) => {
		const levantar = await apoyarDedo( p, donde );
		await p.waitForTimeout( ms );
		await levantar();
	};
	const dictadoFalso = () => {
		class SR {
			start() {
				// Con cuántas pistas de micrófono abiertas arrancó el dictado: en un
				// celular tiene que ser cero (el micrófono es de uno solo a la vez).
				window.__pistasAlIniciar = window.__pistasActivas ? window.__pistasActivas() : 0;
				setTimeout( () => this.onaudiostart?.(), 60 );
				this.t1 = setTimeout( () => this.onresult?.( { resultIndex: 0, results: [ Object.assign( [ { transcript: 'hola cómo' } ], { isFinal: false } ) ] } ), 300 );
				this.t2 = setTimeout( () => this.stop(), 1200 );
			}
			stop() {
				if ( this.fin ) { return; }
				this.fin = true; clearTimeout( this.t1 ); clearTimeout( this.t2 );
				this.onresult?.( { resultIndex: 0, results: [ Object.assign( [ { transcript: 'cómo funciona esto' } ], { isFinal: true } ) ] } );
				setTimeout( () => this.onend?.(), 50 );
			}
			abort() { this.fin = true; setTimeout( () => this.onend?.(), 10 ); }
		}
		window.SpeechRecognition = SR;
		window.webkitSpeechRecognition = SR;
	};
	{
		ok( '/turismo/presentacion sin barra redirige a la carpeta, con su sala', ( await fetch( pre.PHP + '/turismo/presentacion?sala=ABCD', { redirect: 'manual' } ) ).headers.get( 'location' ) === '/turismo/presentacion/?sala=ABCD' );
		// El bucle que vio Safari: la dirección con barra se redirigía a sí misma.
		const conBarra = await fetch( pre.PHP + '/turismo/presentacion/?sala=ABCD', { redirect: 'manual' } );
		ok( '/turismo/presentacion/ (con barra) se sirve, sin redirigir de nuevo', conBarra.status === 200 && ! conBarra.headers.get( 'location' ), String( conBarra.status ) );
		const seguido = await fetch( pre.PHP + '/turismo/presentacion', { redirect: 'follow' } );
		ok( 'y desde la dirección sin barra se llega en una sola vuelta', seguido.status === 200 && seguido.redirected );

		const pantalla = await abrir( { viewport: { width: 1280, height: 720 }, hasTouch: false, isMobile: false } );
		await pantalla.p.addInitScript( dictadoFalso );
		await pantalla.p.goto( pre.PHP + '/turismo/presentacion/' );
		await pantalla.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
		await pantalla.p.click( '#empezar' );
		await pantalla.p.waitForTimeout( 400 );
		const video = () => pantalla.p.evaluate( () => { const v = document.getElementById( 'esfera' ); return { t: v.currentTime, vel: v.playbackRate, error: v.error && v.error.code }; } );
		const v1 = await video();
		await pantalla.p.waitForTimeout( 500 );
		const v2 = await video();
		ok( 'la esfera se mueve: el video corre (MP4 o WebM, el que el navegador pueda)', ! v2.error && v2.t > v1.t, JSON.stringify( v2 ) );
		await pantalla.p.keyboard.press( 'Space' );
		await pantalla.p.waitForTimeout( 400 );
		ok( 'la barra espaciadora (o el control de diapositivas) la pone a escuchar', await pantalla.p.evaluate( () => document.body.dataset.estado ) === 'escuchando' );
		ok( 'y la pantalla queda limpia: ni rótulo de estado ni lo que se va entendiendo', ( await pantalla.p.textContent( '#estado' ) ) === '' && ( await pantalla.p.textContent( '#respuesta' ) ) === '' );
		ok( 'y escuchando, la esfera acelera', ( await video() ).vel > v2.vel, `${ v2.vel } → ${ ( await video() ).vel }` );
		await pantalla.p.waitForFunction( () => document.querySelectorAll( '#respuesta .palabra' ).length > 5, null, { timeout: 9000 } ).catch( () => {} );
		ok( 'contesta, y en la pantalla se lee sólo la respuesta (no lo preguntado)', ( await pantalla.p.textContent( '#pregunta' ) ) === '' && await pantalla.p.locator( '#respuesta .palabra' ).count() > 5 );

		// La sala: el teléfono habla, la pantalla muestra y dice.
		await pantalla.p.keyboard.press( 'Escape' );
		await pantalla.p.keyboard.press( 's' );
		await pantalla.p.click( '#crear-sala' );
		const codigo = ( await pantalla.p.textContent( '#codigo' ) ).trim();
		ok( 'crea una sala con código y QR', /^[A-Z0-9]{6}$/.test( codigo ) && await pantalla.p.locator( '#qr svg' ).count() === 1 && await pantalla.p.locator( '#crear-sala' ).isHidden() );
		await pantalla.p.keyboard.press( 'Escape' );

		const tel = await abrir();
		await tel.p.addInitScript( dictadoFalso );
		await tel.p.goto( `${ pre.PHP }/turismo/presentacion/?sala=${ codigo }&modo=control` );
		await tel.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
		await tel.p.tap( '#empezar' );
		await tel.p.waitForTimeout( 300 );
		ok( 'el teléfono dice cómo se habla: «Mantené apretado para hablar»', ( await tel.p.textContent( '#estado' ) ) === 'Mantené apretado para hablar' );
		const levantar = await apoyarDedo( tel.p );
		const vioEscuchar = await pantalla.p.waitForFunction( () => document.body.dataset.estado === 'escuchando', null, { timeout: 5000 } ).then( () => true ).catch( () => false );
		ok( 'con el dedo apoyado, la pantalla ve que el teléfono está escuchando', vioEscuchar );
		await tel.p.waitForTimeout( 600 );
		await levantar();
		const dijo = await pantalla.p.waitForFunction( () => ( document.getElementById( 'respuesta' ).textContent || '' ).includes( 'cómo funciona esto' ), null, { timeout: 9000 } ).then( () => true ).catch( () => false );
		ok( 'y dice la respuesta a lo que se habló en el teléfono', dijo );
		ok( 'sin errores de página, ni en la pantalla ni en el teléfono', pantalla.errores.length + tel.errores.length === 0, [ ...pantalla.errores, ...tel.errores ].join( '|' ) );
		await tel.ctx.close();
		await pantalla.ctx.close();
	}

	// --- Un teléfono se mantiene apretado para hablar --------------------------
	//
	// Lo que fallaba: escuchando todo el tiempo tomaba el ruido de la sala
	// (contestaba solo) y con ruido no cerraba la frase (no contestaba nunca).
	// Ahora escucha sólo con el dedo apoyado y pregunta al soltar.
	{
		// Micrófono falso con señal (un tono) y pistas que se pueden contar.
		const microfonoFalso = () => {
			const pistas = [];
			const gum = async () => {
				const ctx = new AudioContext();
				await ctx.resume().catch( () => {} );
				const osc = ctx.createOscillator();
				osc.frequency.value = 220;
				const destino = ctx.createMediaStreamDestination();
				osc.connect( destino );
				osc.start();
				pistas.push( ...destino.stream.getAudioTracks() );
				return destino.stream;
			};
			Object.defineProperty( navigator, 'mediaDevices', { value: { getUserMedia: gum, enumerateDevices: async () => [] }, configurable: true } );
			window.__pistasActivas = () => pistas.filter( ( t ) => t.readyState === 'live' ).length;
		};

		// El WebSocket de ElevenLabs, simulado: anota lo que se le manda y, tras
		// cuatro envíos con sonido, devuelve una frase como lo haría el VAD.
		const websocketFalso = () => {
			window.__ws = { creados: [], chunks: [], ultimo: null };
			class WS {
				constructor( url ) {
					this.url = url;
					this.readyState = 0;
					this.conSonido = 0;
					window.__ws.creados.push( url );
					window.__ws.ultimo = this;
					setTimeout( () => {
						this.readyState = 1;
						this.onopen && this.onopen( {} );
						this.emitir( { message_type: 'session_started', session_id: 'prueba', config: {} } );
					}, 30 );
				}
				emitir( o ) { this.onmessage && this.onmessage( { data: JSON.stringify( o ) } ); }
				send( dato ) {
					const m = JSON.parse( dato );
					const bin = atob( m.audio_base_64 );
					let cero = true;
					for ( let i = 0; i < bin.length; i++ ) { if ( bin.charCodeAt( i ) !== 0 ) { cero = false; break; } }
					window.__ws.chunks.push( { cero, tipo: m.message_type, muestras: bin.length / 2, tasa: m.sample_rate, commit: m.commit } );
					if ( cero ) { return; }
					if ( ++this.conSonido === 4 ) {
						this.conSonido = 0;
						this.emitir( { message_type: 'partial_transcript', text: 'hola cómo' } );
						if ( ! window.__sinCommit ) { setTimeout( () => this.emitir( { message_type: 'committed_transcript', text: 'hola cómo funciona esto' } ), 250 ); }
					}
				}
				close() { this.readyState = 3; this.onclose && this.onclose( { code: 1000, reason: '' } ); }
			}
			window.WebSocket = WS;
		};

		/** Qué hace la página con el micrófono, vista desde afuera. */
		const estadoDe = ( pagina ) => pagina.evaluate( () => document.body.dataset.estado );
		const esperar = ( pagina, fn, ms = 9000 ) => pagina.waitForFunction( fn, null, { timeout: ms } ).then( () => true ).catch( () => false );

		// --- Dictado del navegador, teléfono directo -----------------------------
		{
			const tel = await abrir();
			await tel.p.addInitScript( microfonoFalso );
			await tel.p.addInitScript( dictadoFalso );
			await tel.p.goto( pre.PHP + '/turismo/presentacion/' );
			await tel.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await tel.p.tap( '#empezar' );

			const caja = await tel.p.locator( '#orbe' ).boundingBox();
			ok( 'la esfera es grande en un teléfono', caja.width >= 200, `${ Math.round( caja.width ) }px` );
			ok( 'el engranaje está a la vista en el teléfono, en su esquina', await tel.p.locator( '#abrir-ajustes' ).isVisible() );
			await tel.p.waitForTimeout( 1500 );
			ok( 'NO escucha solo: quieto, espera el dedo', await estadoDe( tel.p ) === 'listo' );
			// Los ajustes: tocar el engranaje no es hablar, y tocar afuera los cierra.
			await tel.p.tap( '#abrir-ajustes' );
			await tel.p.waitForTimeout( 200 );
			ok( 'tocar el engranaje abre los ajustes, sin ponerse a escuchar', await tel.p.locator( '#ajustes' ).isVisible() && await estadoDe( tel.p ) === 'listo' );
			await mantenerApretado( tel.p, 600, { x: 195, y: 150 } );
			await tel.p.waitForTimeout( 200 );
			ok( 'con los ajustes abiertos, tocar la pantalla los cierra (no habla)', await tel.p.locator( '#ajustes' ).isHidden() && await estadoDe( tel.p ) === 'listo' );
			ok( 'y dice cómo se habla', ( await tel.p.textContent( '#estado' ) ) === 'Mantené apretado para hablar' );

			// Un roce no es hablar.
			await mantenerApretado( tel.p, 120 );
			await tel.p.waitForTimeout( 400 );
			ok( 'un toque corto no pregunta nada, y recuerda que hay que mantener', await estadoDe( tel.p ) === 'listo' && ( await tel.p.textContent( '#pregunta' ) ) === '' && ( await tel.p.textContent( '#estado' ) ) === 'Mantené apretado para hablar' );

			const levantar = await apoyarDedo( tel.p );
			ok( 'con el dedo apoyado, escucha', await esperar( tel.p, () => document.body.dataset.estado === 'escuchando', 2000 ) );
			ok( 'y lo dice: «Escuchando»', ( await tel.p.textContent( '#estado' ) ) === 'Escuchando' );
			ok( 'el dictado arrancó sin que la página tuviera el micrófono abierto', await tel.p.evaluate( () => window.__pistasAlIniciar ) === 0, String( await tel.p.evaluate( () => window.__pistasAlIniciar ) ) );
			ok( 'cuando el micrófono confirma que se abrió, los aros se hacen fuertes', await esperar( tel.p, () => document.body.dataset.mic === 'abierto', 3000 ) );
			ok( 'los aros salen de la esfera mientras escucha', await tel.p.evaluate( () => getComputedStyle( document.querySelector( '.orbe__aro' ) ).animationName ) === 'aro' );
			ok( 'se lee lo que se va entendiendo', await esperar( tel.p, () => ( document.getElementById( 'respuesta' ).textContent || '' ).includes( 'hola' ), 2000 ) );
			await tel.p.waitForTimeout( 1400 );
			ok( 'una pausa con el dedo apoyado no corta: sigue escuchando la misma frase', await estadoDe( tel.p ) === 'escuchando' && ( await tel.p.textContent( '#pregunta' ) ) === '' );
			await levantar();
			ok( 'al soltar, pregunta y contesta', await esperar( tel.p, () => document.querySelectorAll( '#respuesta .palabra' ).length > 5, 9000 ) && ( await tel.p.textContent( '#pregunta' ) ).includes( 'cómo funciona esto' ) );
			await esperar( tel.p, () => document.body.dataset.estado === 'listo', 14000 );
			await tel.p.waitForTimeout( 1500 );
			ok( 'terminada la respuesta, NO vuelve a escuchar solo', await estadoDe( tel.p ) === 'listo' );
			ok( 'sin errores de página', tel.errores.length === 0, tel.errores.join( '|' ) );
			await tel.ctx.close();
		}

		// --- Si la respuesta tarda, la pantalla no se queda en silencio -----------
		{
			const lento = await levantarPhp( { WEB_PRESENTACION: '1', WEB_PRES_LENTA: '4500', WEB_LATENCIA_MS: '0' } );
			const esp = await abrir();
			await esp.p.addInitScript( dictadoFalso );
			await esp.p.goto( lento.PHP + '/turismo/presentacion/' );
			await esp.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await esp.p.tap( '#empezar' );
			await mantenerApretado( esp.p, 900 );
			await esp.p.waitForFunction( () => document.body.dataset.estado === 'pensando', null, { timeout: 12000 } ).catch( () => {} );
			await esp.p.waitForTimeout( 3600 );
			const frases = [ 'Un segundo…', 'Dejame pensar…', 'Buena pregunta…', 'Ya te cuento…' ];
			const enEspera = ( await esp.p.textContent( '#respuesta' ) ) || '';
			ok( 'a los tres segundos de pensar aparece una frase de espera', frases.includes( enEspera ) && await esp.p.locator( '#respuesta.espera' ).count() === 1, JSON.stringify( enEspera ) );
			await esperar( esp.p, () => document.querySelectorAll( '#respuesta .palabra' ).length > 0, 9000 );
			ok( 'cuando llega la respuesta, la frase de espera se borra y queda la respuesta', ! ( await esp.p.locator( '#respuesta.espera' ).count() ) && ( await esp.p.locator( '#respuesta .palabra' ).count() ) > 0 );
			ok( 'sin errores de página en la espera', esp.errores.length === 0, esp.errores.join( '|' ) );
			await esp.ctx.close();
			lento.matar();
		}

		// --- ?limpio=1 y ?debug=1 -------------------------------------------------
		{
			const limpio = await abrir();
			await limpio.p.addInitScript( microfonoFalso );
			await limpio.p.addInitScript( dictadoFalso );
			await limpio.p.goto( pre.PHP + '/turismo/presentacion/?limpio=1' );
			await limpio.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await limpio.p.tap( '#empezar' );
			await limpio.p.waitForTimeout( 300 );
			const quietoSinTexto = ( await limpio.p.textContent( '#estado' ) ) === '';
			const levantarLimpio = await apoyarDedo( limpio.p );
			await esperar( limpio.p, () => document.body.dataset.estado === 'escuchando', 4000 );
			ok( '?limpio=1 deja el teléfono sin texto, quieto y escuchando', quietoSinTexto && await estadoDe( limpio.p ) === 'escuchando' && ( await limpio.p.textContent( '#estado' ) ) === '' && ( await limpio.p.textContent( '#respuesta' ) ) === '' );
			await levantarLimpio();
			await limpio.ctx.close();

			const depurar = await abrir();
			await depurar.p.addInitScript( microfonoFalso );
			await depurar.p.addInitScript( dictadoFalso );
			await depurar.p.goto( pre.PHP + '/turismo/presentacion/?debug=1' );
			await depurar.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await depurar.p.tap( '#empezar' );
			await mantenerApretado( depurar.p, 900 );
			await depurar.p.waitForTimeout( 600 );
			const registro = await depurar.p.textContent( '.rastro' );
			ok( '?debug=1 escribe en pantalla qué hace el oído (táctil, micrófono, apretado, audiostart, resultado)', /táctil: sí/.test( registro ) && /micrófono sin abrir/.test( registro ) && /apretado/.test( registro ) && /audiostart/.test( registro ) && /resultado:/.test( registro ), registro.slice( 0, 300 ) );
			await depurar.ctx.close();

			// Safari en iPhone no deja dictar al apoyar el dedo (service-not-allowed):
			// lo dice y no queda en silencio.
			const iphone = await abrir();
			await iphone.p.addInitScript( () => {
				class SR {
					start() { setTimeout( () => { this.onerror?.( { error: 'service-not-allowed', message: 'Speech recognition service permission check has failed' } ); this.onend?.(); }, 20 ); }
					stop() {}
					abort() {}
				}
				window.SpeechRecognition = SR;
				window.webkitSpeechRecognition = SR;
			} );
			await iphone.p.goto( pre.PHP + '/turismo/presentacion/' );
			await iphone.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await iphone.p.tap( '#empezar' );
			await mantenerApretado( iphone.p, 800 );
			await iphone.p.waitForTimeout( 300 );
			ok( 'si el teléfono no deja dictar, lo dice (y queda dicho al soltar)', ( await iphone.p.textContent( '#estado' ) ) === 'Este teléfono no deja dictar a la página' );
			await iphone.ctx.close();
		}
	}

	// --- Tiempo real: ElevenLabs Scribe v2 Realtime por WebSocket ------------
	{
		const microfonoFalso = () => {
			const gum = async () => {
				const ctx = new AudioContext();
				await ctx.resume().catch( () => {} );
				const osc = ctx.createOscillator();
				osc.frequency.value = 220;
				const destino = ctx.createMediaStreamDestination();
				osc.connect( destino );
				osc.start();
				( window.__pistas ||= [] ).push( ...destino.stream.getAudioTracks() );
				return destino.stream;
			};
			Object.defineProperty( navigator, 'mediaDevices', { value: { getUserMedia: gum, enumerateDevices: async () => [] }, configurable: true } );
		};
		const websocketFalso = () => {
			window.__ws = { creados: [], chunks: [], ultimo: null };
			class WS {
				constructor( url ) {
					this.url = url;
					this.readyState = 0;
					this.conSonido = 0;
					window.__ws.creados.push( url );
					window.__ws.ultimo = this;
					// __demoraSesion: una conexión lenta (lo dicho mientras tanto no se pierde).
					setTimeout( () => {
						this.readyState = 1;
						this.onopen && this.onopen( {} );
						this.emitir( { message_type: 'session_started', session_id: 'prueba', config: {} } );
					}, window.__demoraSesion || 30 );
				}
				emitir( o ) { this.onmessage && this.onmessage( { data: JSON.stringify( o ) } ); }
				send( dato ) {
					const m = JSON.parse( dato );
					const bin = atob( m.audio_base_64 );
					let cero = true;
					for ( let i = 0; i < bin.length; i++ ) { if ( bin.charCodeAt( i ) !== 0 ) { cero = false; break; } }
					window.__ws.chunks.push( { cero, tipo: m.message_type, muestras: bin.length / 2, tasa: m.sample_rate, commit: m.commit } );
					// __mudo: no entiende nada (ruido sin palabras).
					if ( cero || window.__mudo ) { return; }
					if ( ++this.conSonido === 4 ) {
						this.conSonido = 0;
						this.emitir( { message_type: 'partial_transcript', text: 'hola cómo' } );
						if ( ! window.__sinCommit ) { setTimeout( () => this.emitir( { message_type: 'committed_transcript', text: 'hola cómo funciona esto' } ), 250 ); }
					}
				}
				close() { this.readyState = 3; this.onclose && this.onclose( { code: 1000, reason: '' } ); }
			}
			window.WebSocket = WS;
		};
		const esperar = ( pagina, fn, ms = 9000 ) => pagina.waitForFunction( fn, null, { timeout: ms } ).then( () => true ).catch( () => false );
		const estadoDe = ( pagina ) => pagina.evaluate( () => document.body.dataset.estado );
		const rtSrv = await levantarPhp( { WEB_PRESENTACION: '1', WEB_OIDO: 'tiempo_real', WEB_PRES_CORTA: '1', WEB_LATENCIA_MS: '0' } );

		// --- Teléfono directo -----------------------------------------------------
		{
			const tel = await abrir();
			await tel.p.addInitScript( microfonoFalso );
			await tel.p.addInitScript( websocketFalso );
			await tel.p.goto( rtSrv.PHP + '/turismo/presentacion/' );
			await tel.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await tel.p.tap( '#empezar' );
			await tel.p.waitForTimeout( 1500 );
			ok( 'quieto no se conecta a nada (ni escucha ni se paga entre preguntas)', ( await tel.p.evaluate( () => window.__ws.creados.length ) ) === 0 && ( await tel.p.textContent( '#estado' ) ) === 'Mantené apretado para hablar' );

			const levantar = await apoyarDedo( tel.p );
			ok( 'con el dedo apoyado abre el WebSocket', await esperar( tel.p, () => window.__ws.creados.length === 1, 6000 ) );
			ok( 'a la dirección que le dio el servidor (con su token)', ( await tel.p.evaluate( () => window.__ws.creados[ 0 ] ) ) === 'wss://fake.test/stt?token=sutkn_prueba' );
			await esperar( tel.p, () => window.__ws.chunks.length >= 1, 5000 );
			const primero = await tel.p.evaluate( () => window.__ws.chunks[ 0 ] );
			ok( 'manda audio como pide ElevenLabs: input_audio_chunk, 16 kHz, sin commit', primero && primero.tipo === 'input_audio_chunk' && primero.tasa === 16000 && primero.commit === false, JSON.stringify( primero ) );
			ok( 'en trozos de 0,1 a 1 s (lo que admite ElevenLabs)', primero && primero.muestras >= 1600 && primero.muestras <= 16000, String( primero && primero.muestras ) );
			ok( 'el micrófono manda sonido de verdad (no ceros) mientras escucha', ! ( await tel.p.evaluate( () => window.__ws.chunks.slice( 0, 3 ).every( ( c ) => c.cero ) ) ) );
			ok( 'escuchando, y lo dice', await esperar( tel.p, () => document.body.dataset.estado === 'escuchando', 3000 ) && ( await tel.p.textContent( '#estado' ) ) === 'Escuchando' );
			// El servidor cierra un tramo (una pausa) con el dedo todavía apoyado.
			await esperar( tel.p, () => ( document.getElementById( 'respuesta' ).textContent || '' ).includes( 'funciona' ), 6000 );
			await tel.p.waitForTimeout( 400 );
			ok( 'una pausa con el dedo apoyado no pregunta: junta lo dicho y sigue escuchando', await estadoDe( tel.p ) === 'escuchando' && ( await tel.p.textContent( '#pregunta' ) ) === '' );
			const nSoltar = await tel.p.evaluate( () => window.__ws.chunks.length );
			await levantar();
			// La voz de prueba termina al instante: se mira la pregunta, no el estado.
			ok( 'al soltar pregunta, con lo dicho', await esperar( tel.p, () => ( document.getElementById( 'pregunta' ).textContent || '' ).includes( 'cómo funciona esto' ), 6000 ) );
			const despues = await tel.p.evaluate( ( n ) => window.__ws.chunks.slice( n ), nSoltar );
			ok( 'al soltar manda silencio y pide cerrar el tramo (commit)', despues.some( ( c ) => c.cero ) && despues.some( ( c ) => c.commit === true ), JSON.stringify( despues.map( ( c ) => ( c.cero ? 0 : 1 ) + ( c.commit ? 'c' : '' ) ) ) );
			ok( 'contesta', await esperar( tel.p, () => document.querySelectorAll( '#respuesta .palabra' ).length > 0, 9000 ) );
			const nCerrada = await tel.p.evaluate( () => window.__ws.chunks.length );
			await esperar( tel.p, () => document.body.dataset.estado === 'listo', 14000 );
			await tel.p.waitForTimeout( 1200 );
			ok( 'y el micrófono queda cerrado (abierto, Safari baja o apaga la voz)', await tel.p.evaluate( () => ( window.__pistas || [] ).length > 0 && window.__pistas.every( ( x ) => x.readyState === 'ended' ) ) );
			ok( 'terminada la pregunta, la conexión se cerró: no manda nada más ni vuelve a escuchar solo', await estadoDe( tel.p ) === 'listo' && ( await tel.p.evaluate( () => window.__ws.chunks.length ) ) === nCerrada && ( await tel.p.evaluate( () => window.__ws.creados.length ) ) === 1 );

			// Ruido sin palabras: al soltar no inventa una pregunta.
			await tel.p.evaluate( () => { window.__mudo = true; } );
			await mantenerApretado( tel.p, 900 );
			ok( 'si no entendió nada, al soltar dice «No te escuché» y no pregunta', await esperar( tel.p, () => document.getElementById( 'estado' ).textContent === 'No te escuché', 5000 ) && ( await tel.p.textContent( '#pregunta' ) ) === '' );
			await tel.p.evaluate( () => { window.__mudo = false; } );

			// Un problema sin arreglo (saldo, key) no se reintenta: queda dicho.
			await tel.p.waitForTimeout( 4200 );
			const levantar2 = await apoyarDedo( tel.p );
			await esperar( tel.p, () => window.__ws.creados.length === 3, 6000 );
			await esperar( tel.p, () => window.__ws.chunks.length > 0 && window.__ws.ultimo.readyState === 1, 3000 );
			await tel.p.evaluate( () => window.__ws.ultimo.emitir( { message_type: 'quota_exceeded', error: 'sin saldo' } ) );
			await tel.p.waitForTimeout( 3500 );
			ok( 'quota_exceeded: no se reintenta y el mensaje queda en pantalla', ( await tel.p.evaluate( () => window.__ws.creados.length ) ) === 3 && ( await tel.p.textContent( '#estado' ) ) === 'No pude responder' );
			await levantar2();
			ok( 'sin errores de página', tel.errores.length === 0, tel.errores.join( '|' ) );
			await tel.ctx.close();
		}

		// --- Lo dicho mientras conecta no se pierde --------------------------------
		{
			const tel = await abrir();
			await tel.p.addInitScript( microfonoFalso );
			await tel.p.addInitScript( websocketFalso );
			await tel.p.addInitScript( () => { window.__demoraSesion = 1500; } );
			await tel.p.goto( rtSrv.PHP + '/turismo/presentacion/' );
			await tel.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await tel.p.tap( '#empezar' );
			await mantenerApretado( tel.p, 2600 );
			const trozos = await tel.p.evaluate( () => window.__ws.chunks );
			ok( 'con una conexión lenta, lo guardado sale apenas conecta, con sonido y en trozos de hasta 1 s', trozos.length > 0 && ! trozos[ 0 ].cero && trozos.every( ( c ) => c.muestras <= 16000 ), JSON.stringify( trozos.slice( 0, 4 ) ) );
			ok( 'y pregunta con lo que se dijo', await esperar( tel.p, () => ( document.getElementById( 'pregunta' ).textContent || '' ).includes( 'cómo funciona esto' ), 9000 ) );
			await tel.ctx.close();
		}

		// --- Si el servidor nunca cierra el tramo, pregunta con lo entendido -------
		{
			const tel = await abrir();
			await tel.p.addInitScript( microfonoFalso );
			await tel.p.addInitScript( websocketFalso );
			await tel.p.addInitScript( () => { window.__sinCommit = true; } );
			await tel.p.goto( rtSrv.PHP + '/turismo/presentacion/?debug=1' );
			await tel.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await tel.p.tap( '#empezar' );
			await mantenerApretado( tel.p, 1800 );
			ok( 'si el servidor nunca cierra el tramo, al soltar pregunta con lo que alcanzó a entender', await esperar( tel.p, () => ( document.getElementById( 'pregunta' ).textContent || '' ).includes( 'hola' ), 6000 ) );
			await tel.ctx.close();
		}

		// --- Teléfono + pantalla: el teléfono NO escucha mientras la pantalla habla ---
		{
			const pantalla = await abrir( { viewport: { width: 1280, height: 720 }, hasTouch: false, isMobile: false } );
			// La pantalla anota cada cambio de estado (con la hora): un sondeo no vería
			// una respuesta corta.
			await pantalla.p.addInitScript( () => {
				window.__estados = [];
				new MutationObserver( () => window.__estados.push( [ document.body.dataset.estado, Date.now() ] ) ).observe( document, { attributes: true, subtree: true, attributeFilter: [ 'data-estado' ] } );
			} );
			await pantalla.p.goto( rtSrv.PHP + '/turismo/presentacion/' );
			await pantalla.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await pantalla.p.click( '#empezar' );
			await pantalla.p.waitForTimeout( 300 );
			await pantalla.p.keyboard.press( 's' );
			await pantalla.p.click( '#crear-sala' );
			const codigo = ( await pantalla.p.textContent( '#codigo' ) ).trim();
			await pantalla.p.keyboard.press( 'Escape' );

			const tel = await abrir();
			await tel.p.addInitScript( microfonoFalso );
			await tel.p.addInitScript( websocketFalso );
			await tel.p.addInitScript( () => {
				window.__estados = [];
				new MutationObserver( () => window.__estados.push( [ document.body.dataset.estado, Date.now() ] ) ).observe( document, { attributes: true, subtree: true, attributeFilter: [ 'data-estado' ] } );
			} );
			await tel.p.goto( `${ rtSrv.PHP }/turismo/presentacion/?sala=${ codigo }&modo=control` );
			await tel.p.waitForFunction( () => ! document.getElementById( 'empezar' ).disabled, null, { timeout: 9000 } );
			await tel.p.tap( '#empezar' );
			await mantenerApretado( tel.p, 1800 );

			// Cada cambio de estado queda anotado con su hora, así el cruce se calcula
			// después sin que un sondeo se pierda una respuesta corta.
			await tel.p.waitForFunction( () => window.__estados.some( ( e ) => e[ 0 ] === 'pensando' ), null, { timeout: 15000 } ).catch( () => {} );
			await pantalla.p.waitForFunction( () => document.body.dataset.estado === 'hablando', null, { timeout: 15000 } ).catch( () => {} );
			await pantalla.p.waitForFunction( () => window.__estados.some( ( e ) => e[ 0 ] === 'hablando' ) && document.body.dataset.estado !== 'hablando', null, { timeout: 15000 } ).catch( () => {} );
			// La voz de prueba dura un instante y el teléfono puede no verla: espera
			// hasta su tope (7 s) antes de dejar hablar de nuevo.
			await tel.p.waitForTimeout( 8000 );
			// Terminada la respuesta, se vuelve a poder hablar.
			const levantar = await apoyarDedo( tel.p );
			const volvio = await esperar( tel.p, () => document.body.dataset.estado === 'escuchando', 3000 );
			await levantar();
			await tel.p.waitForTimeout( 300 );
			const [ ht, hp ] = [ await tel.p.evaluate( () => window.__estados ), await pantalla.p.evaluate( () => window.__estados ) ];
			// Tramos [desde, hasta) en que la pantalla habló.
			const tramosHabla = [];
			hp.forEach( ( e, i ) => { if ( e[ 0 ] === 'hablando' ) { tramosHabla.push( [ e[ 1 ], ( hp[ i + 1 ] || [ 0, Date.now() ] )[ 1 ] ] ); } } );
			const pantallaHablo = tramosHabla.length > 0;
			let choques = 0;
			ht.forEach( ( e, i ) => {
				if ( e[ 0 ] !== 'escuchando' ) { return; }
				const [ d, h ] = [ e[ 1 ], ( ht[ i + 1 ] || [ 0, Date.now() ] )[ 1 ] ];
				if ( tramosHabla.some( ( [ a2, b2 ] ) => d < b2 && h > a2 ) ) { choques++; }
			} );
			ok( 'la pantalla dijo la respuesta del teléfono', pantallaHablo );
			ok( 'el teléfono nunca escuchó mientras la pantalla hablaba', choques === 0, `${ choques } veces` );
			ok( 'y cuando la pantalla terminó, se puede volver a hablar', volvio );
			ok( 'sin errores de página, ni en la pantalla ni en el teléfono', pantalla.errores.length + tel.errores.length === 0, [ ...pantalla.errores, ...tel.errores ].join( '|' ) );
			await tel.ctx.close();
			await pantalla.ctx.close();
		}
		rtSrv.matar();
	}
	// --- La vitrina es la propuesta, en otra dirección ---------------------------
	{
		const iguales = readFileSync( join( aqui, '../caaguazu-web-ios/sitio/vitrina/index.html' ), 'utf8' ) === readFileSync( join( aqui, '../caaguazu-web-ios/sitio/propuesta/index.html' ), 'utf8' );
		ok( '/vitrina/ y /propuesta/ sirven la misma página (los dos index.html son idénticos)', iguales );
		const v = await abrir( { viewport: { width: 1440, height: 900 }, hasTouch: false, isMobile: false } );
		await v.p.goto( pre.PHP + '/turismo/vitrina/' );
		await v.p.waitForTimeout( 500 );
		ok( 'la vitrina abre la propuesta, con el aviso de trabajo en proceso', ( await v.p.textContent( 'h1' ) ).trim() === 'Caaguazú Go' && /Trabajo en proceso/i.test( await v.p.textContent( '.aviso' ) ) );
		await v.p.click( 'a[href="#demo"]' );
		await v.p.waitForTimeout( 1200 );
		await v.p.click( '[data-demo="panel"]' );
		await v.p.waitForTimeout( 900 );
		ok( 'desde /vitrina/ la maqueta del panel también carga', ( await v.p.evaluate( () => document.getElementById( 'marco-demo' ).contentWindow.location.pathname ) ).endsWith( '/turismo/vitrina/panel/home.html' ) && await v.p.frameLocator( '#marco-demo' ).locator( 'body' ).isVisible() );
		ok( 'sin errores de página en la vitrina', v.errores.length === 0, v.errores.join( '|' ) );
		await v.ctx.close();
	}

	// --- La propuesta, contada en una página (/turismo/propuesta/) --------------
	{
		const pc = await abrir( { viewport: { width: 1440, height: 900 }, hasTouch: false, isMobile: false } );
		await pc.p.goto( pre.PHP + '/turismo/propuesta/' );
		await pc.p.waitForTimeout( 500 );
		ok( 'la página de la propuesta abre, con su título', ( await pc.p.textContent( 'h1' ) ).trim() === 'Caaguazú Go' );
		ok( 'arriba de todo dice que es un trabajo en proceso y una vista previa', await pc.p.locator( '.aviso' ).isVisible() && /Trabajo en proceso/i.test( await pc.p.textContent( '.aviso' ) ) && /vista previa/i.test( await pc.p.textContent( '.aviso' ) ) );
		await pc.p.click( 'a[href="#demo"]' );
		await pc.p.waitForTimeout( 1200 );
		ok( 'en pantalla ancha, la guía real se ve dentro del teléfono', ( await pc.p.getAttribute( '#marco-demo', 'src' ) ) === '../' && await pc.p.locator( '.telefono' ).isVisible() );
		await pc.p.click( '[data-demo="asistente"]' );
		ok( 'la pestaña «Asistente» abre el asistente de chat de la web', ( await pc.p.getAttribute( '#marco-demo', 'src' ) ) === '../#/asistente' );
		await pc.p.click( '[data-demo="panel"]' );
		ok( 'la pestaña «Panel» abre la maqueta del panel, y dice que son datos de ejemplo', ( await pc.p.getAttribute( '#marco-demo', 'src' ) ) === '../vitrina/panel/home.html' && await pc.p.locator( 'text=Vista previa con datos de ejemplo' ).isVisible() );
		ok( 'no aparece nada de cobro a empresas', ! /empresa|cuota|Gs\.|₲/i.test( await pc.p.textContent( 'body' ) ) );
		ok( 'sin errores de página (computadora)', pc.errores.length === 0, pc.errores.join( '|' ) );
		await pc.ctx.close();

		const cel = await abrir();
		await cel.p.goto( pre.PHP + '/turismo/propuesta/' );
		await cel.p.waitForTimeout( 500 );
		ok( 'en un celular no se dibuja el teléfono (no entra): hay un botón para abrir', ( await cel.p.locator( '.telefono' ).isHidden() ) && await cel.p.locator( '#abrir-demo' ).isVisible() );
		ok( 'nada se sale de la pantalla del celular', await cel.p.evaluate( () => document.documentElement.scrollWidth <= window.innerWidth ) );
		await cel.p.tap( '[data-demo="panel"]' );
		await cel.p.tap( '#abrir-demo' );
		await cel.p.waitForTimeout( 800 );
		ok( '«Abrir» muestra la demo elegida a pantalla completa', await cel.p.locator( '#capa' ).isVisible() && ( await cel.p.getAttribute( '#marco-capa', 'src' ) ) === '../vitrina/panel/home.html' && ( await cel.p.textContent( '#capa-titulo' ) ) === 'Panel · Vista previa' );
		await cel.p.goBack();
		await cel.p.waitForTimeout( 400 );
		ok( '«atrás» del teléfono cierra la demo y se queda en la página', ( await cel.p.locator( '#capa' ).isHidden() ) && cel.p.url().includes( '/turismo/propuesta/' ) );
		await cel.p.tap( '#abrir-demo' );
		await cel.p.waitForTimeout( 300 );
		await cel.p.tap( '#cerrar-capa' );
		await cel.p.waitForTimeout( 400 );
		ok( 'la cruz también la cierra', await cel.p.locator( '#capa' ).isHidden() );
		ok( 'sin errores de página (celular)', cel.errores.length === 0, cel.errores.join( '|' ) );
		await cel.ctx.close();
	}

	pre.matar();
}

await navegador.close();
servidor.close();

console.log( '' );
if ( fallas ) {
	console.log( `${ rojo }  ${ fallas } comprobación/es fallaron.${ fin }\n` );
	process.exit( 1 );
}
console.log( `${ verde }  La web de turismo anda.${ fin }\n` );
