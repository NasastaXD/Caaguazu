import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, estadoCargando, estadoError, fechaCorta, Icono } from "../piezas.js";
import { compartir } from "../compartir.js";

export async function render(contenedor, params, id) {
  contenedor.innerHTML = estadoCargando();
  try {
    const a = await Api.articulo(id);
    pintar(contenedor, a);
  } catch {
    contenedor.innerHTML = estadoError(() => render(contenedor, params, id), volverAtras);
  }
}

/** Volver desde un error: a la charla si se llegó desde ella, a la lista si no. */
function volverAtras() {
  if (history.state?.desdeAsistente) history.back();
  else location.hash = "#/articulos";
}

function pintar(contenedor, a) {
  const autores = (a.autores ?? []).map((au) => au.nombre).filter(Boolean).join(", ");
  // Abierto desde una respuesta del asistente, volver regresa a la charla y
  // no a la lista de artículos: ahí el destino es lo anterior del historial.
  const desdeAsistente = history.state?.desdeAsistente === true;
  contenedor.innerHTML = `
    <div style="display:flex;justify-content:space-between;margin:-4px 0 14px">
      ${desdeAsistente
        ? `<button type="button" class="boton-icono" id="art-volver" aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button>`
        : `<a href="#/articulos" class="boton-perfil" style="width:36px;height:36px" aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</a>`}
      <button class="boton-perfil" id="art-compartir" style="width:36px;height:36px" aria-label="compartir">${Icono.compartir}</button>
    </div>
    ${a.portada?.url ? `<div style="border-radius:var(--radio-tarjeta);overflow:hidden;margin-bottom:20px;aspect-ratio:16/10;background:var(--banda)"><img src="${escapar(a.portada.url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>` : ""}
    ${a.antetitulo ? `<div class="texto-fecha">${escapar(a.antetitulo)}</div>` : ""}
    <h1 class="titular-articulo">${escapar(a.titulo)}</h1>
    ${a.subtitulo ? `<div class="descripcion" style="margin-bottom:10px">${escapar(a.subtitulo)}</div>` : ""}
    ${autores || a.publicado ? `<div class="meta" style="color:var(--tinta-suave);font-size:13px;margin-bottom:20px">${escapar([autores, fechaCorta(a.publicado)].filter(Boolean).join(" · "))}</div>` : ""}
    ${a.entradilla ? `<p class="bajada-articulo">${escapar(a.entradilla)}</p>` : ""}
    <div class="cuerpo-articulo">${a.cuerpoHtml || ""}</div>
    ${a.fuentes?.length ? `<div class="descripcion" style="margin-top:20px">${escapar(t("ficha.fuentes"))}: ${a.fuentes.map(escapar).join(", ")}</div>` : ""}
  `;

  contenedor.querySelector("#art-volver")?.addEventListener("click", () => history.back());
  contenedor.querySelector("#art-compartir").addEventListener("click", () => {
    compartir({ titulo: a.titulo, ruta: `articulo/${a.id}` });
  });
}
