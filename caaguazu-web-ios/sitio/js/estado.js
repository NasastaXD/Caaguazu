// Lo que la persona guarda, en su propio teléfono: sin cuenta, sin servidor,
// sin sincronizar entre dispositivos. Calcado de Guardado.kt de la app.
//
// Desde 2.0.0 es sólo la lista de favoritos: el recorrido propio se arma en
// la app (ver pantallas/app.js). Lo que alguien haya dejado en la clave vieja
// `czu.recorrido` queda en su navegador, sin uso; no se borra por si vuelve.

const CLAVE_FAVORITOS = "czu.favoritos";

const escuchas = new Set();

function leer(clave) {
  try {
    const v = JSON.parse(localStorage.getItem(clave) || "[]");
    return Array.isArray(v) ? v : [];
  } catch {
    return [];
  }
}

function escribir(clave, lista) {
  try {
    localStorage.setItem(clave, JSON.stringify(lista));
  } catch {
    /* sin storage: no persiste, pero no rompe la visita actual */
  }
}

let favoritos = new Set(leer(CLAVE_FAVORITOS));

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
  for (const fn of escuchas) fn(id);
}

export function listaFavoritos() {
  return [...favoritos];
}

export function olvidarFavorito(id) {
  if (!favoritos.has(id)) return;
  favoritos.delete(id);
  escribir(CLAVE_FAVORITOS, [...favoritos]);
}
