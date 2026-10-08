// El mapa: los lugares del inventario como pines. A diferencia de la app
// (MapLibre + mapa sin conexión), esta página ya vive en internet: Leaflet +
// las teselas de OpenStreetMap alcanzan.
//
// Leaflet se pide recién cuando alguien abre el mapa. Hasta 1.2.0 se cargaba
// en cada visita, aunque la mayoría nunca abriera esta pantalla.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, Icono, precioA, vacio } from "../piezas.js";

const CENTRO_CAAGUAZU = [-25.4667, -56.0333];
const LEAFLET = "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/";

let cargaLeaflet = null;
function cargarLeaflet() {
  if (window.L) return Promise.resolve();
  if (cargaLeaflet) return cargaLeaflet;
  cargaLeaflet = new Promise((resolver, rechazar) => {
    const css = document.createElement("link");
    css.rel = "stylesheet";
    css.href = LEAFLET + "leaflet.min.css";
    document.head.appendChild(css);
    const js = document.createElement("script");
    js.src = LEAFLET + "leaflet.min.js";
    js.onload = () => resolver();
    js.onerror = () => {
      cargaLeaflet = null;
      rechazar(new Error("leaflet"));
    };
    document.head.appendChild(js);
  });
  return cargaLeaflet;
}

export async function render(el, { vigente }) {
  el.innerHTML = `
    <div class="pantalla-mapa">
      <div id="mapa-leaflet" role="region" aria-label="${escapar(t("inv.mapa"))}"></div>
      <div class="flotantes">
        <button type="button" class="boton-icono" data-volver aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button>
      </div>
      <div class="atribucion">${escapar(t("mapa.atribucion"))}</div>
      <div id="tarjeta-pin"></div>
    </div>`;

  try {
    await cargarLeaflet();
  } catch {
    if (vigente()) el.querySelector("#mapa-leaflet").innerHTML = `<div style="padding:90px 20px">${vacio(t("mapa.error.detalle"))}</div>`;
    return;
  }
  if (!vigente()) return;

  const L = window.L;
  const mapa = L.map("mapa-leaflet", { zoomControl: false, attributionControl: false }).setView(CENTRO_CAAGUAZU, 13);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(mapa);
  L.control.zoom({ position: "bottomright" }).addTo(mapa);

  let marcadores = [];
  try {
    marcadores = await Api.marcadores();
  } catch {
    /* sin pines el mapa sigue andando: se puede mirar igual */
  }
  if (!vigente()) return;

  const puntos = [];
  for (const m of marcadores) {
    if (typeof m.lat !== "number" || typeof m.lng !== "number") continue;
    const icono = L.divIcon({ className: "", html: `<div class="pin"></div>`, iconSize: [18, 18], iconAnchor: [9, 9] });
    L.marker([m.lat, m.lng], { icon: icono, keyboard: true, title: "" })
      .addTo(mapa)
      .on("click", () => mostrarPin(el, m.id, vigente));
    puntos.push([m.lat, m.lng]);
  }
  if (puntos.length > 1) mapa.fitBounds(puntos, { padding: [60, 60], maxZoom: 15 });
  else if (puntos.length === 1) mapa.setView(puntos[0], 15);

  mapa.on("click", () => (el.querySelector("#tarjeta-pin").innerHTML = ""));
}

async function mostrarPin(el, id, vigente) {
  const zona = el.querySelector("#tarjeta-pin");
  zona.innerHTML = `<div class="tarjeta-pin"><div class="fila"><div class="esqueleto" style="height:48px;flex:1"></div></div></div>`;
  try {
    const f = await Api.ficha(id);
    if (!vigente()) return;
    const meta = [f.categoria?.nombre, precioA(f.practicos?.rango_precio)].filter(Boolean).join(" · ");
    zona.innerHTML = `
      <div class="tarjeta-pin">
        <a class="fila" href="#/ficha/${f.id}">
          <div class="fila__miniatura">${f.portada?.url ? `<img src="${escapar(f.portada.url)}" alt="">` : ""}</div>
          <div class="fila__cuerpo">
            <div class="fila__titulo">${escapar(f.titulo)}</div>
            ${meta ? `<div class="fila__meta">${escapar(meta)}</div>` : ""}
          </div>
          <span class="fila__fin">${Icono.seguir}</span>
        </a>
      </div>`;
  } catch {
    zona.innerHTML = "";
  }
}
