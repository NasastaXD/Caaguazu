// Cliente de czu-app/v1. Los nombres de campo son los del JSON que manda la
// API —`tipo_item`, `google_maps`, `descripcion`, `cuerpo_html`—, no los de
// los modelos Kotlin de la app. Hasta 1.2.0 esta web leía los de Kotlin
// (`tipoItem`, `googleMaps`, `articuloHtml`…), que en el JSON no existen: la
// descripción de una ficha, por ejemplo, no se mostraba nunca.

import { idiomaActual } from "./idioma.js";
import { ajuste } from "./config.js";

function url(ruta, params = {}) {
  const p = new URLSearchParams();
  for (const [clave, valor] of Object.entries(params)) {
    if (valor === null || valor === undefined || valor === "") continue;
    p.set(clave, valor);
  }
  const query = p.toString();
  return ajuste().api + ruta + (query ? "?" + query : "");
}

async function pedir(ruta, params = {}) {
  const respuesta = await fetch(url(ruta, params));
  if (!respuesta.ok) throw new Error(`${respuesta.status} en ${ruta}`);
  return respuesta.json();
}

/** Error del asistente con lo que la pantalla necesita para explicarlo. */
export class ErrorAsistente extends Error {
  constructor(estado, codigo, esperaSeg) {
    super(codigo || String(estado));
    this.estado = estado;
    this.codigo = codigo;
    this.esperaSeg = esperaSeg;
  }
}

export const Api = {
  categorias: () => pedir("categorias", { idioma: idiomaActual() }),
  etiquetas: () => pedir("etiquetas", { idioma: idiomaActual() }),

  inventario: ({ categoria, etiqueta, buscar, tipoItem, pagina = 1, porPagina = 20 } = {}) =>
    pedir("inventario", {
      idioma: idiomaActual(),
      categoria,
      etiqueta,
      buscar,
      tipo_item: tipoItem,
      pagina,
      por_pagina: porPagina,
    }),

  ficha: (id) => pedir(`inventario/${id}`, { idioma: idiomaActual() }),
  marcadores: () => pedir("mapa/markers"),
  eventos: () => pedir("eventos", { idioma: idiomaActual() }),

  articulos: ({ pagina = 1, porPagina = 20, categoria, etiqueta, buscar } = {}) =>
    pedir("articulos", { idioma: idiomaActual(), pagina, por_pagina: porPagina, categoria, etiqueta, buscar }),
  articulo: (id) => pedir(`articulos/${id}`, { idioma: idiomaActual() }),

  idiomas: () => pedir("idiomas"),
  textos: (idioma) => pedir(`strings/${idioma}`),

  /** ¿Está prendido el asistente? Cualquier falla —incluido un 404 de una
   * API que todavía no lo tiene— es «no»: la pestaña simplemente no aparece. */
  asistenteDisponible: async () => {
    try {
      const r = await fetch(url("asistente"));
      if (!r.ok) return false;
      const j = await r.json();
      return j?.disponible === true;
    } catch {
      return false;
    }
  },

  preguntar: async ({ mensaje, conversacion }) => {
    let r;
    try {
      r = await fetch(url("asistente"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ mensaje, conversacion: conversacion || undefined, idioma: idiomaActual() }),
      });
    } catch {
      throw new ErrorAsistente(0, "sin_red");
    }
    const cuerpo = await r.json().catch(() => ({}));
    if (!r.ok) {
      const espera = Number(r.headers.get("Retry-After")) || Number(cuerpo?.error?.detalle?.espera_seg) || 0;
      throw new ErrorAsistente(r.status, cuerpo?.error?.codigo, espera);
    }
    return cuerpo;
  },
};
