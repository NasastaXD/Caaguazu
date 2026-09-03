// Cliente de czu-app/v1. Calcado de ApiHttp.kt de Turismo-app-czu.
// Sin build, sin dependencias: fetch() directo, la API tiene CORS abierto.

import { idiomaActual } from "./idioma.js";

const URL_BASE = "https://caaguazu.net/wp-json/czu-app/v1/";

function conQuery(ruta, params = {}) {
  const p = new URLSearchParams();
  for (const [clave, valor] of Object.entries(params)) {
    if (valor === null || valor === undefined || valor === "") continue;
    p.set(clave, valor);
  }
  const query = p.toString();
  return URL_BASE + ruta + (query ? "?" + query : "");
}

async function pedir(ruta, params = {}) {
  const url = conQuery(ruta, params);
  const respuesta = await fetch(url);
  if (!respuesta.ok) throw new Error(`${respuesta.status} en ${ruta}`);
  return respuesta.json();
}

export const Api = {
  categorias: () => pedir("categorias", { idioma: idiomaActual() }),
  etiquetas: () => pedir("etiquetas", { idioma: idiomaActual() }),

  inventario: ({ categoria, zona, etiqueta, buscar, tipoItem, pagina = 1, porPagina = 20 } = {}) =>
    pedir("inventario", {
      idioma: idiomaActual(),
      categoria,
      zona,
      etiqueta,
      buscar,
      tipo_item: tipoItem,
      pagina,
      por_pagina: porPagina,
    }),

  ficha: (id) => pedir(`inventario/${id}`, { idioma: idiomaActual() }),

  marcadores: () => pedir("mapa/markers"),

  eventos: ({ desde, hasta } = {}) =>
    pedir("eventos", { idioma: idiomaActual(), desde, hasta }),

  evento: (id) => pedir(`eventos/${id}`, { idioma: idiomaActual() }),

  recorridos: () => pedir("recorridos", { idioma: idiomaActual() }),
  recorrido: (id) => pedir(`recorridos/${id}`, { idioma: idiomaActual() }),

  articulos: ({ pagina = 1, categoria, etiqueta, buscar } = {}) =>
    pedir("articulos", { idioma: idiomaActual(), pagina, categoria, etiqueta, buscar }),

  articulo: (id) => pedir(`articulos/${id}`, { idioma: idiomaActual() }),

  idiomas: () => pedir("idiomas"),
  textos: (idioma) => pedir(`strings/${idioma}`),
  medios: () => pedir("media-manifest"),
};
