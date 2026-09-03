// Favoritos y el recorrido propio que la persona arma, guardados en el
// telefono. Calcado de Guardado.kt: sin cuenta, sin servidor, dos listas.

const CLAVE_FAVORITOS = "czu.favoritos";
const CLAVE_RECORRIDO = "czu.recorrido";

const escuchas = new Set();

function leer(clave) {
  try {
    return JSON.parse(localStorage.getItem(clave) || "[]");
  } catch {
    return [];
  }
}

function escribir(clave, lista) {
  try {
    localStorage.setItem(clave, JSON.stringify(lista));
  } catch {
    /* sin storage: no persiste, pero no rompe la sesion actual */
  }
}

let favoritos = new Set(leer(CLAVE_FAVORITOS));
let recorrido = leer(CLAVE_RECORRIDO);

function avisar() {
  for (const fn of escuchas) fn();
}

export function alSuscribirEstado(fn) {
  escuchas.add(fn);
  return () => escuchas.delete(fn);
}

export function esFavorito(id) {
  return favoritos.has(id);
}

export function alternarFavorito(id) {
  if (favoritos.has(id)) favoritos.delete(id);
  else favoritos.add(id);
  escribir(CLAVE_FAVORITOS, [...favoritos]);
  avisar();
}

export function listaFavoritos() {
  return [...favoritos];
}

export function enRecorrido(id) {
  return recorrido.includes(id);
}

export function alternarEnRecorrido(id) {
  recorrido = recorrido.includes(id) ? recorrido.filter((x) => x !== id) : [...recorrido, id];
  escribir(CLAVE_RECORRIDO, recorrido);
  avisar();
}

export function vaciarRecorrido() {
  recorrido = [];
  escribir(CLAVE_RECORRIDO, recorrido);
  avisar();
}

export function listaRecorrido() {
  return [...recorrido];
}
