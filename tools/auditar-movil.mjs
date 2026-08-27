/**
 * Auditoría del panel en teléfono.
 *
 *   node tools/auditar-movil.mjs          # 390 × 844 (teléfono chico)
 *   node tools/auditar-movil.mjs 360      # otro ancho
 *
 * Sale con código 1 si alguna pantalla falla, para poder colgarlo de CI.
 *
 * Comprueba dos cosas, en las 15 pantallas del panel, y las dos son reglas
 * escritas del proyecto, no gustos:
 *
 *   1. **Nada se sale de la pantalla.** Un desborde horizontal en un teléfono
 *      se traduce en una barra de scroll lateral y en texto cortado.
 *   2. **Nada que se toque baja de 44px de alto.** Buena parte del público es
 *      mayor y trabaja con el teléfono en la mano, muchas veces en el campo.
 *
 * Necesita Playwright con Chromium. Dibuja cada pantalla con
 * `tools/vista-previa-panel.php`, así que mide el HTML real del panel, no una
 * maqueta aparte.
 */

import { execFileSync } from 'node:child_process';
import { mkdtempSync, writeFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

const raiz = dirname( dirname( fileURLToPath( import.meta.url ) ) );
const requerir = createRequire( import.meta.url );

let chromium;
try {
	( { chromium } = requerir( 'playwright' ) );
} catch ( e ) {
	console.error( 'Falta Playwright. Instalarlo con: npm i -D playwright && npx playwright install chromium' );
	process.exit( 2 );
}

const ANCHO = Number( process.argv[ 2 ] ) || 390;
const ALTO = 844;
const MIN_TACTIL = 44;

const PANTALLAS = [
	'sections/home', 'sections/mis-contenidos', 'sections/editor', 'sections/captura',
	'sections/revision', 'sections/tareas', 'sections/equipo', 'sections/reportes',
	'sections/biblioteca', 'sections/estructura', 'sections/buscar', 'sections/perfil',
	'sections/app', 'sections/ayuda', 'auth/login',
];

const dir = mkdtempSync( join( tmpdir(), 'czu-movil-' ) );
const verde = '\x1b[32m', rojo = '\x1b[31m', gris = '\x1b[90m', fin = '\x1b[0m';

let fallas = 0;
const navegador = await chromium.launch();

console.log( `\n${ gris }Panel en ${ ANCHO }×${ ALTO }${ fin }\n` );

for ( const pantalla of PANTALLAS ) {
	const archivo = join( dir, pantalla.replace( '/', '-' ) + '.html' );
	writeFileSync( archivo, execFileSync( 'php', [ join( raiz, 'tools/vista-previa-panel.php' ), pantalla ], { encoding: 'utf8' } ) );

	const pagina = await navegador.newPage( { viewport: { width: ANCHO, height: ALTO } } );
	await pagina.goto( 'file://' + archivo );
	// El splash tapa la pantalla el primer segundo; acá estorba.
	await pagina.evaluate( () => { const s = document.querySelector( '[data-splash]' ); if ( s ) { s.remove(); } } );

	const r = await pagina.evaluate( ( minTactil ) => {
		const ancho = document.documentElement.clientWidth;
		const desborde = document.scrollingElement.scrollWidth - ancho;

		const anchos = [];
		document.querySelectorAll( 'body *' ).forEach( ( el ) => {
			const caja = el.getBoundingClientRect();
			if ( ! caja.width || caja.right <= ancho + 1 ) { return; }
			// El menú lateral vive fuera de pantalla a propósito hasta que se abre.
			if ( getComputedStyle( el ).position === 'fixed' && caja.left < 0 ) { return; }
			anchos.push( el.className.toString().split( ' ' )[ 0 ] || el.tagName );
		} );

		const chicos = [];
		document.querySelectorAll( 'a, button, input, select, [role="button"]' ).forEach( ( el ) => {
			const caja = el.getBoundingClientRect();
			if ( ! caja.width || ! caja.height ) { return; }
			if ( el.type === 'hidden' ) { return; }
			if ( caja.height < minTactil - 1 ) {
				chicos.push( ( el.className.toString().split( ' ' )[ 0 ] || el.tagName ) + ' (' + Math.round( caja.height ) + 'px)' );
			}
		} );

		return { desborde, anchos: [ ...new Set( anchos ) ], chicos: [ ...new Set( chicos ) ] };
	}, MIN_TACTIL );

	await pagina.close();

	const mal = r.desborde > 0 || r.chicos.length > 0;
	if ( mal ) { fallas++; }
	console.log( ` ${ mal ? rojo + '✗' + fin : verde + '✓' + fin }  ${ pantalla }` );
	if ( r.desborde > 0 ) {
		console.log( `      se sale ${ r.desborde }px: ${ r.anchos.slice( 0, 4 ).join( ', ' ) }` );
	}
	if ( r.chicos.length ) {
		console.log( `      táctil < ${ MIN_TACTIL }px: ${ r.chicos.slice( 0, 6 ).join( ', ' ) }` );
	}
}

await navegador.close();
rmSync( dir, { recursive: true, force: true } );

console.log( '' );
if ( fallas ) {
	console.log( `${ rojo }  ${ fallas } pantalla(s) con problemas en teléfono.${ fin }\n` );
	process.exit( 1 );
}
console.log( `${ verde }  Las ${ PANTALLAS.length } pantallas pasan.${ fin }\n` );
