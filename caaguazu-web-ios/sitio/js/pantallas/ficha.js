// La ficha de un lugar: la foto arriba, y debajo lo que alguien necesita para
// ir — cómo llegar, horario, costo— antes que la historia del lugar.

import { Api } from "../api.js";
import { t, idiomaActual } from "../idioma.js";
import { escapar, Icono, precioA, fechaLarga, filaArticulo, botonFavorito, error } from "../piezas.js";
import { enlacePunto } from "../mapas.js";
import { compartir } from "../compartir.js";

export async function render(el, { id, vigente }) {
  el.innerHTML = `
    <div class="portada esqueleto" style="border-radius:0"></div>
    <div class="ficha">
      <div class="esqueleto esqueleto--linea" style="width:30%"></div>
      <div class="esqueleto esqueleto--linea" style="width:70%;height:28px"></div>
      <div class="esqueleto esqueleto--bloque"></div>
    </div>`;

  let f;
  try {
    f = await Api.ficha(id);
  } catch {
    if (vigente()) el.innerHTML = cabeceraError() + error();
    return;
  }
  if (!vigente()) return;

  const evento = f.tipo_item === "evento" && f.fechas;
  const parcial = f.traducido === false && idiomaActual() !== "es";
  // Los dos enlaces los carga el equipo en el panel y terminan en un href:
  // sólo se usan si son http(s), nunca un `javascript:` cargado por error.
  const web = (u) => (/^https?:\/\//i.test(String(u ?? "")) ? u : "");
  const comoLlegar = web(f.google_maps) || (f.coordenadas ? enlacePunto(f.coordenadas.lat, f.coordenadas.lng, f.titulo) : "");
  const video = web(f.video);

  const datos = [
    [t("ficha.horario"), f.practicos?.horario],
    [t("ficha.costo"), f.practicos?.costo || precioA(f.practicos?.rango_precio)],
    [t("ficha.contacto"), f.practicos?.contacto],
    [t("ficha.camino"), mayuscula(f.acceso?.estado_camino)],
  ].filter(([, v]) => v);

  const galeria = (f.galeria ?? []).filter((im) => im?.url);
  const relacionados = f.articulos_relacionados ?? [];

  el.innerHTML = `
    <div class="portada">
      ${f.portada?.url ? `<img src="${escapar(f.portada.url)}" alt="${escapar(f.portada.alt || "")}">` : ""}
      ${f.portada?.credito ? `<span class="portada__credito">${escapar(f.portada.credito)}</span>` : ""}
      <div class="flotantes">
        <button type="button" class="boton-icono" data-volver aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button>
        <div class="flotantes__der">
          <button type="button" class="boton-icono" id="ficha-compartir" aria-label="${escapar(t("diag.compartir"))}">${Icono.compartir}</button>
          ${botonFavorito(f.id)}
        </div>
      </div>
    </div>

    <article class="ficha">
      <div>
        ${f.categoria?.nombre ? `<a class="eyebrow" href="#/buscar?categoria=${f.categoria.id}">${escapar(f.categoria.nombre)}</a>` : ""}
        <h1 class="ficha__titulo">${escapar(f.titulo)}</h1>
      </div>

      ${evento ? `
        <div class="dato" style="padding:0;background:none;align-items:center">
          ${f.fechas.en_curso ? `<span class="sello sello--ahora" style="position:static">${escapar(t("evento.enCurso"))}</span>` : ""}
          <span class="apagado">${escapar(fechaLarga(f.fechas.inicio))}${f.fechas.fin ? ` — ${escapar(fechaLarga(f.fechas.fin))}` : ""}</span>
        </div>` : ""}

      ${(f.etiquetas ?? []).length ? `
        <div class="chips">${f.etiquetas.map((e) => `<a class="chip chip--quieto" href="#/buscar?etiqueta=${e.id}">${escapar(e.nombre)}</a>`).join("")}</div>` : ""}

      ${parcial ? `<div class="aviso-parcial">${escapar(t("ficha.parcial"))}</div>` : ""}

      <div class="acciones">
        ${comoLlegar ? `<a class="boton boton--primario" href="${escapar(comoLlegar)}" target="_blank" rel="noopener">${Icono.pin} ${escapar(t("web.comoLlegar"))}</a>` : ""}
        ${evento && f.fechas.inicio && !f.fechas.terminado ? `<button type="button" class="boton boton--fantasma" id="ficha-agendar">${Icono.calendario} ${escapar(t("ficha.agendar"))}</button>` : ""}
        ${video ? `<a class="boton boton--fantasma" href="${escapar(video)}" target="_blank" rel="noopener">${Icono.externo} ${escapar(t("web.verVideo"))}</a>` : ""}
      </div>

      ${datos.length ? `
        <div class="datos">
          ${datos.map(([etiqueta, valor]) => `
            <div class="dato"><span class="dato__etiqueta">${escapar(etiqueta)}</span><span class="dato__valor">${escapar(valor)}</span></div>`).join("")}
        </div>` : ""}

      ${f.descripcion ? `<div class="prosa">${f.descripcion}</div>` : ""}

      ${galeria.length ? `
        <section>
          <h2 class="subtitulo" style="margin-bottom:10px">${escapar(t("ficha.galeria"))}</h2>
          <div class="galeria">${galeria.map((im) => `<img src="${escapar(im.url)}" alt="${escapar(im.alt || "")}" loading="lazy">`).join("")}</div>
        </section>` : ""}

      ${relacionados.length ? `
        <section>
          <h2 class="subtitulo" style="margin-bottom:10px">${escapar(t("web.paraLeer"))}</h2>
          <div class="lista">${relacionados.map(filaArticulo).join("")}</div>
        </section>` : ""}

      ${f.fuentes ? `
        <section>
          <h2 class="eyebrow" style="margin-bottom:8px">${escapar(t("ficha.fuentes"))}</h2>
          <div class="fuentes">${escapar(f.fuentes)}</div>
        </section>` : ""}
    </article>
  `;

  el.querySelector("#ficha-compartir").addEventListener("click", () => compartir({ titulo: f.titulo, ruta: `ficha/${f.id}` }));
  el.querySelector("#ficha-agendar")?.addEventListener("click", () => abrirCalendario(f));
}

function cabeceraError() {
  return `<div class="cabeza-pantalla"><button type="button" class="boton-icono" data-volver aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button></div>`;
}

function mayuscula(s) {
  const v = String(s ?? "").trim();
  return v ? v.charAt(0).toUpperCase() + v.slice(1) : "";
}

function abrirCalendario(f) {
  const inicio = new Date(f.fechas.inicio);
  const fin = f.fechas.fin ? new Date(f.fechas.fin) : new Date(inicio.getTime() + 3600e3);
  const fmt = (d) => d.toISOString().replace(/[-:]/g, "").split(".")[0] + "Z";
  const params = new URLSearchParams({
    action: "TEMPLATE",
    text: f.titulo,
    dates: `${fmt(inicio)}/${fmt(fin)}`,
    details: f.google_maps ? `${f.titulo} — ${f.google_maps}` : f.titulo,
  });
  window.open(`https://calendar.google.com/calendar/render?${params.toString()}`, "_blank", "noopener");
}
