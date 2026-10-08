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

async function pedir(ruta, params = {}, opciones) {
  const url = conQuery(ruta, params);
  const respuesta = await fetch(url, opciones);
  if (!respuesta.ok) {
    // El status viaja en el error: el asistente distingue un 404 («no hay
    // asistente») de un fallo de red, que no dice nada de él.
    const e = new Error(`${respuesta.status} en ${ruta}`);
    e.status = respuesta.status;
    throw e;
  }
  return respuesta.json();
}

/**
 * El único POST del espejo: la pregunta al asistente.
 *
 * El Content-Type va sí o sí: sin él WordPress no lee el cuerpo como JSON y
 * contesta 400 `mensaje_vacio` aunque la pregunta esté ahí. Sin cookies,
 * porque la API no las necesita y una sesión abierta del panel no tiene por
 * qué viajar con una pregunta de turista. `no-store`, porque una respuesta es
 * de una persona y de un momento.
 *
 * El error lleva el status y el `codigo` de la API para que la charla
 * distinga «lo apagaron» de «falló esta vez». El cuerpo se lee con cuidado:
 * un 502 o un 504 del hosting llega en HTML, no en JSON.
 */
// La app corta a los 50 s y ofrece reintentar. Sin este límite, un corte a mitad
// del pedido deja «Pensando…» para siempre y la charla bloqueada hasta que el
// navegador se rinda, que en un celular puede tardar minutos.
const ESPERA_PREGUNTA_MS = 50000;

async function enviar(ruta, cuerpo) {
  const controlador = new AbortController();
  const limite = setTimeout(() => controlador.abort(), ESPERA_PREGUNTA_MS);
  try {
    const respuesta = await fetch(URL_BASE + ruta, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(cuerpo),
      cache: "no-store",
      credentials: "omit",
      signal: controlador.signal,
    });
    if (!respuesta.ok) {
      const e = new Error(`${respuesta.status} en ${ruta}`);
      e.status = respuesta.status;
      try {
        e.codigo = (await respuesta.json())?.error?.codigo ?? null;
      } catch {
        e.codigo = null;
      }
      throw e;
    }
    return await respuesta.json();
  } finally {
    clearTimeout(limite);
  }
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

  // Sin ?idioma: no trae texto, sólo si hay asistente. Un servidor anterior a
  // la 0.9.0 contesta 404, y eso se lee como «no hay». `fresco` saltea la copia
  // de hasta 5 minutos que guarda el navegador y revalida contra el servidor:
  // hace falta cuando una pregunta acaba de fallar.
  asistente: ({ fresco = false } = {}) => pedir("asistente", {}, fresco ? { cache: "no-cache" } : undefined),

  // El idioma va en el cuerpo, como en ApiHttp.kt. JSON.stringify descarta la
  // clave que vale undefined, así que la primera pregunta sale sin
  // `conversacion` y el servidor arma una nueva.
  preguntar: (mensaje, conversacion) =>
    enviar("asistente", { mensaje, conversacion: conversacion || undefined, idioma: idiomaActual() }),

  idiomas: () => pedir("idiomas"),
  textos: (idioma) => pedir(`strings/${idioma}`),
  medios: () => pedir("media-manifest"),
};
