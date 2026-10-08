// Piezas de interfaz reutilizables. Sin framework: cada funcion devuelve un
// string de HTML, y quien la llama lo mete en el DOM con innerHTML.

import { t } from "./idioma.js";
import { esFavorito } from "./estado.js";

export function escapar(s) {
  return String(s ?? "").replace(/[&<>"']/g, (c) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
  }[c]));
}

// Iconos propios, vectores minimos: no son fotografia de archivo, son trazo
// de interfaz. Sustituyen a lo que en la app sale de /media-manifest.
export const Icono = {
  volver: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>`,
  corazon: `<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.6-10-9.3C.5 8 2 4 6 4c2 0 3.5 1.2 4.5 2.8C11.5 5.2 13 4 15 4c4 0 5.5 4 4 7.7C19.5 16.4 12 21 12 21z"/></svg>`,
  corazonBorde: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7.5-4.6-10-9.3C.5 8 2 4 6 4c2 0 3.5 1.2 4.5 2.8C11.5 5.2 13 4 15 4c4 0 5.5 4 4 7.7C19.5 16.4 12 21 12 21z"/></svg>`,
  filtro: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 6h16M8 12h8M11 18h2"/></svg>`,
  buscar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>`,
  pin: `<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>`,
  calendario: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>`,
  cerrar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>`,
  tilde: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>`,
  perfil: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>`,
  compartir: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 10.5l6.8-3.9M8.6 13.5l6.8 3.9"/></svg>`,
  copiar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 01-1-1V4a1 1 0 011-1h10a1 1 0 011 1v1"/></svg>`,
  inicio: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11l8-7 8 7"/><path d="M6 10v9a1 1 0 001 1h4v-6h2v6h4a1 1 0 001-1v-9"/></svg>`,
  articulo: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>`,
  recorrido: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19l6-14 4 9 3-5 3 10"/></svg>`,
};

export function precioA(rango) {
  if (rango === null || rango === undefined) return "";
  if (rango <= 0) return t("precio.gratis");
  return "$".repeat(Math.min(rango, 4));
}

export function tarjetaLugar(item) {
  const foto = item.portada?.url ?? "";
  const activo = esFavorito(item.id);
  const esEvento = item.tipoItem === "evento";
  const meta = [item.zona?.nombre, precioA(item.rangoPrecio ?? item.rango_precio)].filter(Boolean).join(" · ");
  return `
    <a class="tarjeta-lugar" href="#/ficha/${item.id}">
      <div class="foto">
        ${foto ? `<img src="${escapar(foto)}" alt="" loading="lazy">` : ""}
        ${esEvento ? `<span class="badge-evento">${escapar(t("principal.eventos"))}</span>` : ""}
        <button class="corazon ${activo ? "activo" : ""}" data-favorito="${item.id}" aria-label="favorito">
          ${activo ? Icono.corazon : Icono.corazonBorde}
        </button>
      </div>
      <div class="titulo-tarjeta">${escapar(item.titulo)}</div>
      ${meta ? `<div class="meta">${escapar(meta)}</div>` : ""}
    </a>`;
}

export function tileCategoria(cat) {
  const foto = cat.portada?.url ?? "";
  return `
    <a class="tile-categoria" href="#/buscar?categoria=${cat.id}">
      ${foto ? `<img src="${escapar(foto)}" alt="" loading="lazy">` : ""}
      <div class="velo-degradado"></div>
      <span>${escapar(cat.nombre)}</span>
    </a>`;
}

export function chip(texto, elegido = false, attrs = "") {
  return `<button class="chip ${elegido ? "elegido" : ""}" ${attrs}>${escapar(texto)}</button>`;
}

export function estadoCargando() {
  return `<div class="estado">${escapar(t("estado.cargando"))}</div>`;
}

export function estadoVacio(mensaje) {
  return `<div class="estado">${escapar(mensaje || t("estado.vacio"))}</div>`;
}

export function estadoError(alReintentar) {
  const id = "r" + Math.random().toString(36).slice(2, 8);
  setTimeout(() => {
    document.getElementById(id)?.addEventListener("click", alReintentar);
  });
  return `<div class="estado">${escapar(t("estado.error"))}<br>
    <button id="${id}" class="boton-secundario" style="width:auto;display:inline-flex;margin-top:14px">${escapar(t("estado.reintentar"))}</button>
  </div>`;
}

export function fechaCorta(iso) {
  if (!iso) return "";
  try {
    const d = new Date(iso);
    return d.toLocaleDateString(document.documentElement.lang || "es", { day: "2-digit", month: "short" });
  } catch {
    return iso;
  }
}
