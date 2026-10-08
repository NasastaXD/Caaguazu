import { Api } from "../api.js";
import { t, idiomaActual } from "../idioma.js";
import { escapar, Icono, fechaCorta, filaArticulo, error } from "../piezas.js";
import { compartir } from "../compartir.js";

export async function render(el, { id, vigente }) {
  el.innerHTML = `
    <div class="cabeza-pantalla"><span class="esqueleto" style="width:44px;height:44px;border-radius:999px"></span></div>
    <div class="esqueleto esqueleto--linea" style="width:40%"></div>
    <div class="esqueleto esqueleto--linea" style="width:85%;height:28px;margin-top:10px"></div>
    <div class="esqueleto esqueleto--bloque" style="margin-top:18px"></div>`;

  let a;
  try {
    a = await Api.articulo(id);
  } catch {
    if (vigente()) el.innerHTML = `<div class="cabeza-pantalla"><button type="button" class="boton-icono" data-volver aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button></div>${error()}`;
    return;
  }
  if (!vigente()) return;

  const autores = (a.autores ?? []).map((x) => x.nombre).filter(Boolean).join(", ");
  const meta = [autores, fechaCorta(a.publicado)].filter(Boolean).join(" · ");
  const parcial = a.traducido === false && idiomaActual() !== "es";
  const fuentes = Array.isArray(a.fuentes) ? a.fuentes : [];
  const relacionados = a.relacionados ?? [];

  el.innerHTML = `
    <div class="cabeza-pantalla" style="justify-content:space-between">
      <button type="button" class="boton-icono" data-volver aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button>
      <button type="button" class="boton-icono" id="art-compartir" aria-label="${escapar(t("diag.compartir"))}">${Icono.compartir}</button>
    </div>

    <article class="ficha" style="margin-top:0;padding-top:0">
      <header>
        ${a.antetitulo ? `<div class="eyebrow">${escapar(a.antetitulo)}</div>` : ""}
        <h1 class="ficha__titulo">${escapar(a.titulo)}</h1>
        ${a.subtitulo ? `<p class="apagado" style="margin:8px 0 0;font-size:1.05rem">${escapar(a.subtitulo)}</p>` : ""}
        ${meta ? `<p class="debil" style="margin:10px 0 0">${escapar(meta)}</p>` : ""}
      </header>

      ${a.portada?.url ? `
        <figure style="margin:0">
          <img src="${escapar(a.portada.url)}" alt="${escapar(a.portada.alt || "")}" style="width:100%;border-radius:var(--r-3)">
          ${a.portada.credito ? `<figcaption class="debil" style="margin-top:6px">${escapar(a.portada.credito)}</figcaption>` : ""}
        </figure>` : ""}

      ${parcial ? `<div class="aviso-parcial">${escapar(t("ficha.parcial"))}</div>` : ""}
      ${a.entradilla ? `<p class="entradilla">${escapar(a.entradilla)}</p>` : ""}
      <div class="prosa">${a.cuerpo_html || ""}</div>

      ${(a.etiquetas ?? []).length ? `<div class="chips">${a.etiquetas.map((e) => `<span class="chip chip--quieto">${escapar(e.nombre)}</span>`).join("")}</div>` : ""}

      ${fuentes.length ? `
        <section>
          <h2 class="eyebrow" style="margin-bottom:8px">${escapar(t("ficha.fuentes"))}</h2>
          <div class="fuentes">${fuentes.map(escapar).join("\n")}</div>
        </section>` : ""}

      ${relacionados.length ? `
        <section>
          <h2 class="subtitulo" style="margin-bottom:10px">${escapar(t("ficha.relacionados"))}</h2>
          <div class="lista">${relacionados.map(filaArticulo).join("")}</div>
        </section>` : ""}
    </article>
  `;

  el.querySelector("#art-compartir").addEventListener("click", () => compartir({ titulo: a.titulo, ruta: `articulo/${a.id}` }));
}
