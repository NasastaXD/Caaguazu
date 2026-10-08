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
			return route.fulfill( json( { respuesta: 'Podés visitar Ykua La Patria.', fuentes: [ { tipo: 'ficha', id: 260, titulo: 'Ykua La Patria', tipo_item: 'sitio' }, { tipo: 'recorrido', id: 9, titulo: 'Ruta fundacional' } ], conversacion: 'abc12345', idioma: 'es' } ) );
		}
		segundo = JSON.parse( route.request().postData() );
		return route.fulfill( json( { error: { codigo: 'muchos_pedidos', mensaje: 'x', detalle: { espera_seg: 30 } } }, 429, { 'Retry-After': '30' } ) );
	} );
	await ir( p, '#/asistente' );
	ok( 'la pestaña aparece cuando la API lo tiene prendido', ( await p.locator( '#barra .barra__item' ).allTextContents() ).some( ( t ) => /Asistente/.test( t ) ) );
	await p.locator( '[data-ejemplo]' ).first().click();
	await p.waitForSelector( '.globo--ia:not(.escribiendo)', { timeout: 5000 } );
	ok( 'muestra la respuesta', ( await p.locator( '.globo--ia' ).first().textContent() ).includes( 'Ykua La Patria' ) );
	const fuentes = await p.locator( '.globo__fuentes a' ).evaluateAll( ( as ) => as.map( ( a ) => a.getAttribute( 'href' ) ) );
	ok( 'las fuentes llevan a la ficha y, un recorrido, a la app', JSON.stringify( fuentes ) === '["#/ficha/260","#/app"]', JSON.stringify( fuentes ) );
	await p.locator( '#pregunta' ).fill( '¿y cómo llego?' );
	await p.locator( '#redactar button[type=submit]' ).click();
	await p.waitForSelector( '.globo--error', { timeout: 5000 } );
	ok( 'la segunda pregunta manda el id de la conversación', segundo?.conversacion === 'abc12345', JSON.stringify( segundo ) );
	ok( 'el 429 dice cuánto esperar', ( await p.locator( '.globo--error' ).textContent() ).includes( '30 segundos' ) );
	await p.locator( '.globo__fuentes a' ).first().click();
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
	ok( 'sin asistente: ni pestaña ni tarjeta', ! ( await p.locator( '#barra .barra__item' ).allTextContents() ).some( ( t ) => /Asistente/.test( t ) ) && await p.locator( 'a[href="#/asistente"]' ).count() === 0 );
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
}

await navegador.close();
servidor.close();

console.log( '' );
if ( fallas ) {
	console.log( `${ rojo }  ${ fallas } comprobación/es fallaron.${ fin }\n` );
	process.exit( 1 );
}
console.log( `${ verde }  La web de turismo anda.${ fin }\n` );
