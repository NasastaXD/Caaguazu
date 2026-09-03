// El mapa es la misma busqueda, dibujada sobre el lienzo. A diferencia de la
// app (MapLibre + pmtiles offline), esta pagina vive en internet: Leaflet +
// tiles raster de OpenStreetMap alcanza y no pide nada mas.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, precioA, Icono } from "../piezas.js";

const CENTRO_CAAGUAZU = [-25.4667, -56.0333];

export async function render(contenedor) {
  contenedor.innerHTML = `
    <div class="pantalla-mapa">
      <div id="mapa-leaflet"></div>
    </div>
    <div class="mapa-controles" style="display:flex;justify-content:space-between">
      <a href="#/buscar" class="ficha-boton-volver" style="position:static" aria-label="volver">${Icono.volver}</a>
    </div>
    <div class="atribucion-mapa">${escapar(t("mapa.atribucion"))}</div>
    <div id="tarjeta-pin"></div>
  `;

  if (typeof L === "undefined") {
    contenedor.querySelector("#mapa-leaflet").innerHTML =
      `<div class="estado">${escapar(t("mapa.error.detalle"))}</div>`;
    return;
  }

  const mapa = L.map("mapa-leaflet", { zoomControl: false }).setView(CENTRO_CAAGUAZU, 12);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: "",
  }).addTo(mapa);
  L.control.zoom({ position: "bottomright" }).addTo(mapa);

  try {
    const [marcadores, categorias] = await Promise.all([Api.marcadores(), Api.categorias()]);
    const colorPorCategoria = new Map(categorias.map((c) => [c.id, c.color]));

    for (const m of marcadores) {
      const color = colorPorCategoria.get(m.categoria) || "#96C8A2";
      const icono = L.divIcon({
        className: "",
        html: `<div style="width:16px;height:16px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>`,
        iconSize: [16, 16],
      });
      L.marker([m.lat, m.lng], { icon: icono }).addTo(mapa).on("click", () => mostrarPin(contenedor, m.id));
    }
  } catch {
    /* sin pines no rompe el mapa: la persona sigue pudiendo navegarlo */
  }
}

async function mostrarPin(contenedor, id) {
  const zona = contenedor.querySelector("#tarjeta-pin");
  zona.innerHTML = `<div class="tarjeta-pin">${escapar(t("estado.cargando"))}</div>`;
  try {
    const f = await Api.ficha(id);
    zona.innerHTML = `
      <a class="tarjeta-pin" href="#/ficha/${f.id}">
        ${f.portada?.url ? `<img src="${escapar(f.portada.url)}" alt="">` : ""}
        <div>
          <div class="titulo-tarjeta">${escapar(f.titulo)}</div>
          <div class="meta" style="color:var(--tinta-suave);font-size:13px">
            ${escapar([f.zona?.nombre, precioA(f.practicos?.rangoPrecio)].filter(Boolean).join(" · "))}
          </div>
        </div>
      </a>`;
  } catch {
    zona.innerHTML = "";
  }
}
