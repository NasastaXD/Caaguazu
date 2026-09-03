import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, estadoCargando, estadoError, fechaCorta, Icono } from "../piezas.js";

export async function render(contenedor, params, id) {
  contenedor.innerHTML = estadoCargando();
  try {
    const a = await Api.articulo(id);
    pintar(contenedor, a);
  } catch {
    contenedor.innerHTML = estadoError(() => render(contenedor, params, id));
  }
}

function pintar(contenedor, a) {
  const autores = (a.autores ?? []).map((au) => au.nombre).filter(Boolean).join(", ");
  contenedor.innerHTML = `
    <div style="margin:-4px 0 14px">
      <a href="#/articulos" class="boton-perfil" style="width:36px;height:36px" aria-label="volver">${Icono.volver}</a>
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
}
