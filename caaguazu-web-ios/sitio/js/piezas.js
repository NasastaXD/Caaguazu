// Piezas de interfaz reutilizables. Sin framework: cada función devuelve un
// string de HTML y quien la llama lo mete con innerHTML.

import { t } from "./idioma.js";
import { esFavorito } from "./estado.js";

export function escapar(s) {
  return String(s ?? "").replace(/[&<>"']/g, (c) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
  }[c]));
}

// Íconos: los mismos trazos del panel (promotur_icon() en caaguazu-portal),
// línea de 1,8 con puntas redondeadas. Los que el panel no tiene se dibujan
// con la misma regla. Nada de emojis haciendo de ícono: cada sistema los
// dibuja distinto y no toman el color del texto.
const trazo = (d) =>
  `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;

export const Icono = {
  inicio: trazo('<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>'),
  buscar: trazo('<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>'),
  articulo: trazo('<path d="M4 5h13v14a2 2 0 0 0 2 2H5a1 1 0 0 1-1-1Z"/><path d="M17 9h3v10a2 2 0 0 1-2 2"/><path d="M7 9h7M7 13h7M7 17h4"/>'),
  ruta: trazo('<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.5 6H15a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6h6.5"/>'),
  asistente: trazo('<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.9A8 8 0 1 1 21 12Z"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01"/>'),
  ajustes: trazo('<path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/>'),
  pin: trazo('<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>'),
  mapa: trazo('<path d="m9 4-6 2.5v13.5l6-2.5 6 2.5 6-2.5V4l-6 2.5Z"/><path d="M9 4v13.5M15 6.5V20"/>'),
  filtro: trazo('<path d="M4 7h16M7 12h10M10 17h4"/>'),
  volver: trazo('<path d="m15 6-6 6 6 6"/>'),
  seguir: trazo('<path d="m9 6 6 6-6 6"/>'),
  cerrar: trazo('<path d="M6 6l12 12M18 6 6 18"/>'),
  tilde: trazo('<path d="M20 6 9 17l-5-5"/>'),
  nueva: trazo('<path d="M12 5v14M5 12h14"/>'),
  compartir: trazo('<path d="M12 3v12"/><path d="m8 7 4-4 4 4"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7"/>'),
  copiar: trazo('<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>'),
  corazon: `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.6-9.3C1 7.9 2.9 4.5 6.3 4.5c2.1 0 3.6 1.2 4.6 2.7 1-1.5 2.5-2.7 4.6-2.7 3.4 0 5.3 3.4 3.9 6.7C19.5 15.9 12 20.5 12 20.5Z"/></svg>`,
  corazonBorde: trazo('<path d="M12 20.5s-7.5-4.6-9.6-9.3C1 7.9 2.9 4.5 6.3 4.5c2.1 0 3.6 1.2 4.6 2.7 1-1.5 2.5-2.7 4.6-2.7 3.4 0 5.3 3.4 3.9 6.7C19.5 15.9 12 20.5 12 20.5Z"/>'),
  calendario: trazo('<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>'),
  enviar: trazo('<path d="M12 19V5"/><path d="m6 11 6-6 6 6"/>'),
  movil: trazo('<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>'),
  sol: trazo('<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/>'),
  idioma: trazo('<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>'),
  externo: trazo('<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/>'),
  // Tiendas: siluetas simples, no los logos oficiales.
  android: `<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true"><path d="M17.5 9.4 19 6.8a.4.4 0 0 0-.7-.4l-1.5 2.6A9 9 0 0 0 12 8a9 9 0 0 0-4.8 1L5.7 6.4a.4.4 0 0 0-.7.4l1.5 2.6A8.3 8.3 0 0 0 2.5 16h19a8.3 8.3 0 0 0-4-6.6ZM7.5 13.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm9 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/></svg>`,
  manzana: `<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true"><path d="M16.4 12.6c0-2.3 1.9-3.4 2-3.5a4.3 4.3 0 0 0-3.4-1.8c-1.4-.2-2.8.8-3.5.8-.8 0-1.9-.8-3.1-.8a4.6 4.6 0 0 0-3.9 2.4c-1.7 2.9-.4 7.2 1.2 9.6.8 1.2 1.8 2.5 3 2.4 1.2 0 1.7-.8 3.1-.8 1.5 0 1.9.8 3.1.8 1.3 0 2.1-1.2 2.9-2.3a10 10 0 0 0 1.3-2.7 4.2 4.2 0 0 1-2.7-4.1ZM14.1 5.6A4.2 4.2 0 0 0 15 2.5a4.3 4.3 0 0 0-2.8 1.5 4 4 0 0 0-1 3 3.6 3.6 0 0 0 2.9-1.4Z"/></svg>`,
  marca: trazo('<path d="M12 21V11"/><path d="M12 11c0-4 2-6 6-7-1 4-3 6-6 7Z"/><path d="M12 11C12 7 10 5 4 4c1 4 3 6 8 7Z"/>'),
};

export function precioA(rango) {
  if (rango === null || rango === undefined || rango === "") return "";
  const n = Number(rango);
  if (Number.isNaN(n)) return "";
  if (n <= 0) return t("precio.gratis");
  return "$".repeat(Math.min(n, 4));
}

export function fechaCorta(iso) {
  if (!iso) return "";
  try {
    return new Date(iso).toLocaleDateString(document.documentElement.lang || "es", { day: "numeric", month: "short" });
  } catch {
    return "";
  }
}

export function fechaLarga(iso) {
  if (!iso) return "";
  try {
    return new Date(iso).toLocaleDateString(document.documentElement.lang || "es", {
      weekday: "long", day: "numeric", month: "long", hour: "2-digit", minute: "2-digit",
    });
  } catch {
    return "";
  }
}

/** Botón de favorito. `extra` para la variante que flota sobre la portada. */
export function botonFavorito(id) {
  const activo = esFavorito(id);
  return `<button type="button" class="corazon ${activo ? "activo" : ""}" data-favorito="${id}"
    aria-pressed="${activo}" aria-label="${escapar(t("web.guardar"))}">${activo ? Icono.corazon : Icono.corazonBorde}</button>`;
}

/** Tarjeta de un lugar del inventario (lista o detalle: misma forma). */
export function tarjetaLugar(item, i = 0) {
  const foto = item.portada?.url ?? "";
  const evento = item.tipo_item === "evento" && item.fechas;
  const ahora = evento && item.fechas.en_curso;
  const meta = [
    item.categoria?.nombre,
    evento && !ahora ? fechaCorta(item.fechas.inicio) : "",
    precioA(item.rango_precio ?? item.practicos?.rango_precio),
  ].filter(Boolean).join(" · ");
  return `
    <a class="tarjeta aparece" style="--i:${i}" href="#/ficha/${item.id}">
      <div class="tarjeta__foto">
        ${foto ? `<img src="${escapar(foto)}" alt="${escapar(item.portada?.alt || "")}" loading="lazy">` : ""}
        ${evento ? `<span class="sello ${ahora ? "sello--ahora" : ""}">${escapar(ahora ? t("evento.enCurso") : t("web.evento"))}</span>` : ""}
        ${botonFavorito(item.id)}
      </div>
      <div class="tarjeta__cuerpo">
        <div class="tarjeta__titulo">${escapar(item.titulo)}</div>
        ${meta ? `<div class="tarjeta__meta">${escapar(meta)}</div>` : ""}
      </div>
    </a>`;
}

/** Tarjeta de categoría: foto arriba, nombre y descripción abajo. Las dos
 * cosas llegan desde la 0.7.0 de la API (`imagen`, `descripcion`). */
export function tarjetaCategoria(cat, i = 0) {
  const foto = cat.imagen?.url ?? "";
  return `
    <a class="tarjeta aparece" style="--i:${i}" href="#/buscar?categoria=${cat.id}">
      <div class="tarjeta__foto">
        ${foto ? `<img src="${escapar(foto)}" alt="${escapar(cat.imagen?.alt || "")}" loading="lazy">` : ""}
      </div>
      <div class="tarjeta__cuerpo">
        <div class="tarjeta__titulo">${escapar(cat.nombre)}</div>
        ${cat.descripcion ? `<p class="tarjeta__texto">${escapar(cat.descripcion)}</p>` : ""}
      </div>
    </a>`;
}

export function filaArticulo(a, i = 0) {
  const foto = a.portada?.url ?? "";
  const autores = (a.autores ?? []).map((x) => x.nombre).filter(Boolean).join(", ");
  const meta = [autores, fechaCorta(a.publicado)].filter(Boolean).join(" · ");
  return `
    <a class="fila aparece" style="--i:${i}" href="#/articulo/${a.id}">
      <div class="fila__miniatura">${foto ? `<img src="${escapar(foto)}" alt="" loading="lazy">` : ""}</div>
      <div class="fila__cuerpo">
        ${a.antetitulo ? `<div class="eyebrow">${escapar(a.antetitulo)}</div>` : ""}
        <div class="fila__titulo">${escapar(a.titulo)}</div>
        ${meta ? `<div class="fila__meta">${escapar(meta)}</div>` : ""}
      </div>
    </a>`;
}

export function chip(texto, elegido = false, attrs = "") {
  return `<button type="button" class="chip ${elegido ? "elegido" : ""}" aria-pressed="${elegido}" ${attrs}>${escapar(texto)}</button>`;
}

export function cabeceraPantalla(titulo, { volver = false, derecha = "" } = {}) {
  return `
    <div class="cabeza-pantalla">
      ${volver ? `<button type="button" class="boton-icono" data-volver aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button>` : ""}
      <h1 class="titulo">${escapar(titulo)}</h1>
      ${derecha}
    </div>`;
}

export function botonAjustes() {
  return `<a class="boton-icono" href="#/ajustes" aria-label="${escapar(t("barra.ajustes"))}">${Icono.ajustes}</a>`;
}

/* --- Estados --------------------------------------------------------- */

export function esqueletoGrilla(n = 4) {
  return `<div class="grilla">${Array.from({ length: n }, () => `<div class="esqueleto esqueleto--tarjeta"></div>`).join("")}</div>`;
}

export function esqueletoLista(n = 3) {
  return `<div class="lista">${Array.from({ length: n }, () => `<div class="esqueleto" style="height:96px"></div>`).join("")}</div>`;
}

export function vacio(mensaje, accionHtml = "") {
  return `<div class="vacio"><p>${escapar(mensaje || t("estado.vacio"))}</p>${accionHtml}</div>`;
}

/** Error con «Intentar de nuevo». El botón se engancha por delegación en
 * app.js (data-reintentar), así no queda un setTimeout colgado por pantalla. */
export function error() {
  return `<div class="vacio"><p>${escapar(t("estado.error"))}</p>
    <button type="button" class="boton boton--fantasma" data-reintentar>${escapar(t("estado.reintentar"))}</button></div>`;
}

/* --- Hoja inferior ----------------------------------------------------- */

/**
 * Abre una hoja con su fondo y devuelve el elemento de la hoja. Se cierra
 * tocando el fondo, con Escape, o llamando a la función que devuelve
 * `.cerrar`. Cierra con su animación de salida, no de golpe.
 */
export function abrirHoja(htmlInterior, { titulo = "" } = {}) {
  let zona = document.getElementById("zona-hoja");
  if (!zona) {
    zona = document.createElement("div");
    zona.id = "zona-hoja";
    document.body.appendChild(zona);
  }
  const anterior = document.activeElement;
  zona.className = "";
  zona.innerHTML = `
    <div class="fondo-hoja" data-cerrar-hoja></div>
    <div class="hoja" role="dialog" aria-modal="true" ${titulo ? `aria-label="${escapar(titulo)}"` : ""} tabindex="-1">
      <div class="hoja__manija"></div>
      ${titulo ? `<h2 class="hoja__titulo">${escapar(titulo)}</h2>` : ""}
      ${htmlInterior}
    </div>`;
  const hoja = zona.querySelector(".hoja");

  let cerrada = false;
  const cerrar = () => {
    if (cerrada) return;
    cerrada = true;
    document.removeEventListener("keydown", alTeclado);
    zona.className = "cerrando";
    const fin = () => {
      zona.innerHTML = "";
      zona.className = "";
      anterior?.focus?.();
    };
    if (matchMedia("(prefers-reduced-motion: reduce)").matches) fin();
    else setTimeout(fin, 180);
  };
  const alTeclado = (ev) => {
    if (ev.key === "Escape") cerrar();
  };
  document.addEventListener("keydown", alTeclado);
  zona.querySelector("[data-cerrar-hoja]").addEventListener("click", cerrar);
  hoja.focus();
  hoja.cerrar = cerrar;
  return hoja;
}

/** Una hoja abierta se cierra sola al cambiar de pantalla. */
export function cerrarHojaAbierta() {
  document.querySelector("#zona-hoja .hoja")?.cerrar?.();
}
