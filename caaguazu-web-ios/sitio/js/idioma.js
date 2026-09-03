// Idioma actual + fusion de textos: respaldo embebido (es = piso, +es/en/pt) y
// lo que mande /strings/{idioma} encima, sin reemplazarlo nunca del todo.
// Calcado de Idioma.kt y Textos.kt de Turismo-app-czu.

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

/** El castellano embebido es el piso de todo: una clave que falta en otro
 * idioma sale en castellano, nunca marcada. */
export async function cargarTextos() {
  const piso = await cargarEmbebido(ORIGINAL);
  const propio = actual === ORIGINAL ? {} : await cargarEmbebido(actual);
  let delServidor = {};
  try {
    delServidor = await fetch(`https://caaguazu.net/wp-json/czu-app/v1/strings/${actual}`).then((r) =>
      r.ok ? r.json() : {},
    );
  } catch {
    /* sin red: se sigue con el respaldo embebido */
  }
  textos = { ...piso, ...propio, ...delServidor };
}

async function cargarEmbebido(codigo) {
  try {
    return await fetch(`textos/${codigo}.json`).then((r) => (r.ok ? r.json() : {}));
  } catch {
    return {};
  }
}

export function t(clave) {
  return textos[clave] ?? clave;
}
