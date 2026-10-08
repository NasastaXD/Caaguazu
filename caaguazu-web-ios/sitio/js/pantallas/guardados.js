// Lo guardado con el corazón. Son fichas que se piden de a una al abrir esta
// pantalla; si alguna ya no existe, se la saca de la lista sin hacer ruido.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, Icono, cabeceraPantalla, esqueletoLista, vacio, error, precioA } from "../piezas.js";
import { listaFavoritos, alternarFavorito } from "../estado.js";

export async function render(el, { vigente }) {
  el.innerHTML = `
    ${cabeceraPantalla(t("web.guardados"), { volver: true })}
    <div id="cuerpo">${esqueletoLista(3)}</div>`;

  const cuerpo = el.querySelector("#cuerpo");
  const ids = listaFavoritos();
  if (!ids.length) {
    cuerpo.innerHTML = vacio(t("web.guardadosVacio"));
    return;
  }

  const resultados = await Promise.allSettled(ids.map((id) => Api.ficha(id)));
  if (!vigente()) return;

  const fichas = resultados.filter((r) => r.status === "fulfilled").map((r) => r.value);
  if (!fichas.length) {
    cuerpo.innerHTML = error();
    return;
  }

  cuerpo.innerHTML = `<div class="lista">${fichas.map(fila).join("")}</div>`;
  cuerpo.querySelector(".lista").addEventListener("click", (ev) => {
    const quitar = ev.target.closest("[data-quitar]");
    if (!quitar) return;
    const id = Number(quitar.dataset.quitar);
    alternarFavorito(id);
    quitar.closest(".fila").remove();
    if (!cuerpo.querySelector(".fila")) cuerpo.innerHTML = vacio(t("web.guardadosVacio"));
  });
}

function fila(f) {
  const meta = [f.categoria?.nombre, precioA(f.practicos?.rango_precio)].filter(Boolean).join(" · ");
  return `
    <div class="fila">
      <a class="fila__cuerpo" href="#/ficha/${f.id}" style="display:flex;align-items:center;gap:14px;min-width:0">
        <span class="fila__miniatura">${f.portada?.url ? `<img src="${escapar(f.portada.url)}" alt="">` : ""}</span>
        <span style="min-width:0">
          <span class="fila__titulo" style="display:block">${escapar(f.titulo)}</span>
          ${meta ? `<span class="fila__meta">${escapar(meta)}</span>` : ""}
        </span>
      </a>
      <button type="button" class="boton-icono" data-quitar="${f.id}" aria-label="${escapar(t("web.quitarGuardado"))}">${Icono.cerrar}</button>
    </div>`;
}
