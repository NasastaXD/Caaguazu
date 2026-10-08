// Idioma actual + fusion de textos: respaldo embebido (es = piso, +es/en/pt) y
// lo que mande /strings/{idioma} encima, sin reemplazarlo nunca del todo.
// Calcado de Idioma.kt y Textos.kt de Turismo-app-czu.

import { ajuste, conTiempo, urlSitio } from "./config.js";

const ORIGINAL = "es";
const SOPORTADOS = ["es", "en", "pt"];
const CLAVE_AJUSTE = "czu.idioma";

let actual = leerGuardado() || detectarDelTelefono();
let textos = {};
let disponibles = [
  { codigo: "es", nombre: "Español" },
  { codigo: "en", nombre: "English" },
  { codigo: "pt", nombre: "Português" },
];

function leerGuardado() {
  try {
    return localStorage.getItem(CLAVE_AJUSTE);
  } catch {
    return null;
  }
}

function detectarDelTelefono() {
  const del = (navigator.language || "es").slice(0, 2);
  return SOPORTADOS.includes(del) ? del : ORIGINAL;
}

export function idiomaActual() {
  return actual;
}

export function idiomasDisponibles() {
  return disponibles;
}

export function elegirIdioma(codigo) {
  actual = codigo;
  try {
    localStorage.setItem(CLAVE_AJUSTE, codigo);
  } catch {
    /* sin storage disponible, se pierde al recargar y no pasa nada grave */
  }
}

export function aplicarDisponibles(lista) {
  if (Array.isArray(lista) && lista.length > 0) disponibles = lista;
}

// Los textos se arman con tres capas, de menor a mayor prioridad: el castellano
// embebido (el piso), el idioma elegido embebido, y lo que el panel mande por
// /strings/{idioma}. Las dos primeras salen del mismo servidor que la página y
// alcanzan para dibujar; la tercera depende de la API y NO se espera de más.
let embebidos = {};
let delServidor = {};

function recomponer() {
  textos = { ...embebidos, ...delServidor };
}

/**
 * Lo embebido: el castellano y, si hace falta, el idioma elegido, pedidos a la
 * vez. Es todo lo que se necesita para dibujar la primera pantalla. Se vuelve
 * a llamar al cambiar de idioma.
 */
export async function cargarTextos() {
  const [piso, propio] = await Promise.all([
    cargarEmbebido(ORIGINAL),
    actual === ORIGINAL ? {} : cargarEmbebido(actual),
  ]);
  embebidos = { ...piso, ...propio };
  delServidor = {};
  recomponer();
}

/**
 * Lo que el panel edita encima de lo embebido. Va aparte de cargarTextos()
 * porque depende de la API: si la API tarda, la web no tiene por qué esperar.
 * Lo que llegue tarde entra igual y se ve en la próxima pantalla.
 */
export async function cargarTextosDelServidor(ms = 4000) {
  const idioma = actual;
  try {
    const r = await conTiempo(`${ajuste().api}strings/${idioma}`, {}, ms);
    const nuevos = r.ok ? await r.json() : {};
    // Si mientras tanto se cambió de idioma, estos ya no corresponden.
    if (idioma !== actual || !nuevos || typeof nuevos !== "object") return;
    delServidor = nuevos;
    recomponer();
  } catch {
    /* sin red: se sigue con el respaldo embebido */
  }
}

async function cargarEmbebido(codigo) {
  try {
    return await conTiempo(urlSitio(`textos/${codigo}.json`), {}, 6000).then((r) => (r.ok ? r.json() : {}));
  } catch {
    return {};
  }
}

export function t(clave) {
  return textos[clave] ?? clave;
}

/** t() con huecos: `tf("web.ia.espera", 30)` sobre «Probá de nuevo en %s segundos». */
export function tf(clave, ...valores) {
  let i = 0;
  return t(clave).replace(/%s/g, () => String(valores[i++] ?? ""));
}
