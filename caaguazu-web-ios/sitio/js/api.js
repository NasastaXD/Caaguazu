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

// Cuánto se espera antes de cada reintento. Cada pedido a la API arranca
// WordPress y abre una conexión a la base de datos, y un hosting compartido
// tiene un tope: cuando varios pedidos llegan juntos, los que sobran reciben un
// 500 («Database Error») que NO es de la API sino del momento. Esperar un
// instante y volver a pedir casi siempre alcanza, y es la diferencia entre una
// pantalla con su contenido y una con «algo salió mal».
const ESPERAS = [600, 1800];
const dormir = (ms) => new Promise((resolver) => setTimeout(resolver, ms));

async function pedir(ruta, params = {}) {
  let ultimo;
  for (let intento = 0; intento <= ESPERAS.length; intento++) {
    if (intento > 0) await dormir(ESPERAS[intento - 1]);
    let respuesta;
    try {
      respuesta = await fetch(url(ruta, params));
    } catch (e) {
      ultimo = e; // sin red un momento: se vuelve a probar
      continue;
    }
    if (respuesta.ok) return respuesta.json();
    ultimo = new Error(`${respuesta.status} en ${ruta}`);
    // Un 404 o un 400 es una respuesta: esperar no la cambia. Un 5xx sí puede.
    if (respuesta.status < 500) break;
  }
  throw ultimo;
}

/**
 * La pista que deja el panel al abrirse (`czu_revisa`, ver
 * promotur_pista_revisor()): dice que vale la pena preguntar por los
 * borradores. No es un permiso —eso lo decide la API— y sin ella no se pide
 * nada: un turista no paga un pedido de más para enterarse de que no es
 * profesor.
 */
export function hayPistaRevisor() {
  return /(?:^|;\s*)czu_revisa=1(?:;|$)/.test(document.cookie || "");
}

/** Si la API dice que no, la pista sobra: se borra para no volver a preguntar. */
function olvidarPistaRevisor() {
  document.cookie = "czu_revisa=; Max-Age=0; Path=/; SameSite=Lax";
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

  // `borrador` sólo lo usa quien revisa contenido: ver borradores() abajo.
  ficha: (id, { borrador = false } = {}) => pedir(`inventario/${id}`, { idioma: idiomaActual(), borrador: borrador ? 1 : "" }),
  marcadores: () => pedir("mapa/markers"),
  eventos: () => pedir("eventos", { idioma: idiomaActual() }),

  articulos: ({ pagina = 1, porPagina = 20, categoria, etiqueta, buscar } = {}) =>
    pedir("articulos", { idioma: idiomaActual(), pagina, por_pagina: porPagina, categoria, etiqueta, buscar }),
  articulo: (id, { borrador = false } = {}) => pedir(`articulos/${id}`, { idioma: idiomaActual(), borrador: borrador ? 1 : "" }),

  /** Lo no publicado, para quien lo revisa (el Profesor en el panel). La API
   * decide por la sesión del panel, que viaja en la cookie: esta web y el
   * panel viven en el mismo dominio. Sin permiso es `null`, sin error: para
   * la pantalla, «no sos profesor» y «no hay nada» se dibujan igual. */
  borradores: async () => {
    let r;
    try {
      r = await fetch(url("borradores", { idioma: idiomaActual() }), { credentials: "same-origin", cache: "no-store" });
    } catch {
      return null;
    }
    if (r.status === 401 || r.status === 403) {
      olvidarPistaRevisor();
      return null;
    }
    return r.ok ? r.json() : null;
  },

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
