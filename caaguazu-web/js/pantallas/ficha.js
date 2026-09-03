// La ficha: hoja que sube sobre la foto fija, calcada de Ficha.kt.

import { Api } from "../api.js";
import { t, idiomaActual } from "../idioma.js";
import { escapar, precioA, estadoCargando, estadoError, Icono } from "../piezas.js";
import { esFavorito, alternarFavorito } from "../estado.js";
import { enlacePunto } from "../mapas.js";

export async function render(contenedor, params, id) {
  contenedor.innerHTML = estadoCargando();
  try {
    const ficha = await Api.ficha(id);
    pintar(contenedor, ficha);
  } catch {
    contenedor.innerHTML = estadoError(() => render(contenedor, params, id));
  }
}

function pintar(contenedor, f) {
  const activo = esFavorito(f.id);
  const parcial = !f.traducido && f.idioma !== "es" && idiomaActual() !== "es";

  const chips = (f.etiquetas ?? []).map((e) => `<span class="chip">${escapar(e.nombre)}</span>`).join("");

  const practicos = [
    f.practicos?.horario && [t("ficha.horario"), f.practicos.horario],
    f.practicos?.costo && [t("ficha.costo"), f.practicos.costo],
    f.practicos?.rangoPrecio != null && [t("ficha.costo"), precioA(f.practicos.rangoPrecio)],
    f.practicos?.contacto && [t("ficha.contacto"), f.practicos.contacto],
    f.acceso?.estadoCamino && [t("ficha.camino"), f.acceso.estadoCamino],
  ].filter(Boolean);

  const galeria = (f.galeria ?? []).filter((im) => im.url);

  const esEvento = f.tipoItem === "evento" && f.fechas;
  const puedeAgendar = esEvento && f.fechas.inicio;

  contenedor.innerHTML = `
    <div class="ficha-foto-fija">
      ${f.portada?.url ? `<img src="${escapar(f.portada.url)}" alt="">` : ""}
      <div class="velo-degradado"></div>
    </div>
    <button class="ficha-boton-volver" id="ficha-volver" aria-label="volver">${Icono.volver}</button>
    <button class="ficha-boton-favorito ${activo ? "activo" : ""}" id="ficha-favorito" aria-label="favorito">
      ${activo ? Icono.corazon : Icono.corazonBorde}
    </button>

    <div class="ficha-hoja">
      ${f.categoria?.nombre ? `<div class="descripcion" style="margin-bottom:4px">${escapar(f.categoria.nombre)}</div>` : ""}
      <h1 class="ficha-titulo">${escapar(f.titulo)}</h1>

      ${chips ? `<div class="fila-chips ficha-fila-chips">${chips}</div>` : ""}

      ${parcial ? `<div class="aviso-parcial">${escapar(t("ficha.parcial"))}</div>` : ""}

      ${practicos.length ? `
        <div class="ficha-practicos">
          ${practicos.map(([et, val]) => `
            <div class="dato-practico">
              <div class="etiqueta">${escapar(et)}</div>
              <div class="valor">${escapar(val)}</div>
            </div>`).join("")}
        </div>` : ""}

      ${puedeAgendar ? `
        <div style="margin-bottom:14px">
          <a class="boton-secundario" id="ficha-agendar">${Icono.calendario} ${escapar(t("ficha.agendar"))}</a>
        </div>` : ""}

      ${f.googleMaps || f.coordenadas ? `
        <div style="margin-bottom:var(--entre-secciones)">
          <a class="boton-primario" target="_blank" rel="noopener" href="${escapar(
            f.googleMaps || enlacePunto(f.coordenadas.lat, f.coordenadas.lng, f.titulo),
          )}">${Icono.pin} ${escapar(t("ficha.mapa"))}</a>
        </div>` : ""}

      ${f.articuloHtml ? `<div class="descripcion" style="margin-bottom:var(--entre-secciones)">${f.articuloHtml}</div>` : ""}

      ${galeria.length ? `
        <div class="seccion">
          <h2 class="titulo-seccion">${escapar(t("ficha.galeria"))}</h2>
          <div class="galeria-ficha">${galeria.map((im) => `<img src="${escapar(im.url)}" alt="${escapar(im.alt || "")}" loading="lazy">`).join("")}</div>
        </div>` : ""}

      ${f.autor?.nombre ? `<div class="descripcion">${escapar(t("ficha.autor"))}: ${escapar(f.autor.nombre)}</div>` : ""}
      ${f.fuentes ? `<div class="descripcion" style="margin-top:6px">${escapar(t("ficha.fuentes"))}: ${escapar(f.fuentes)}</div>` : ""}
    </div>
  `;

  contenedor.querySelector("#ficha-volver").addEventListener("click", () => history.back());
  contenedor.querySelector("#ficha-favorito").addEventListener("click", () => {
    alternarFavorito(f.id);
    pintar(contenedor, f);
  });

  if (puedeAgendar) {
    contenedor.querySelector("#ficha-agendar")?.addEventListener("click", (ev) => {
      ev.preventDefault();
      abrirCalendario(f);
    });
  }
}

function abrirCalendario(f) {
  const inicio = new Date(f.fechas.inicio);
  const fin = f.fechas.fin ? new Date(f.fechas.fin) : new Date(inicio.getTime() + 3600e3);
  const fmt = (d) => d.toISOString().replace(/[-:]/g, "").split(".")[0] + "Z";
  const params = new URLSearchParams({
    action: "TEMPLATE",
    text: f.titulo,
    dates: `${fmt(inicio)}/${fmt(fin)}`,
    details: f.googleMaps ? `${f.titulo} — ${f.googleMaps}` : f.titulo,
  });
  window.open(`https://calendar.google.com/calendar/render?${params.toString()}`, "_blank", "noopener");
}
