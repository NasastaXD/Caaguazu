import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, filaArticulo, cabeceraPantalla, botonAjustes, esqueletoLista, vacio, error } from "../piezas.js";

const POR_PAGINA = 20;

export async function render(el, { vigente }) {
  el.innerHTML = `
    ${cabeceraPantalla(t("web.articulos"), { derecha: botonAjustes() })}
    <div id="lista-articulos">${esqueletoLista(4)}</div>
    <div id="mas-articulos"></div>
  `;

  let pagina = 1;
  let cargados = 0;

  async function cargar() {
    const lista = el.querySelector("#lista-articulos");
    const mas = el.querySelector("#mas-articulos");
    try {
      const r = await Api.articulos({ pagina, porPagina: POR_PAGINA });
      if (!vigente()) return;
      const items = r.items ?? [];
      if (pagina === 1) {
        lista.innerHTML = items.length ? `<div class="lista"></div>` : vacio(t("web.sinArticulos"));
      }
      lista.querySelector(".lista")?.insertAdjacentHTML("beforeend", items.map((a, i) => filaArticulo(a, i)).join(""));
      cargados += items.length;
      mas.innerHTML = cargados < (r.total ?? 0)
        ? `<button type="button" class="boton boton--fantasma boton--ancho" style="margin-top:12px">${escapar(t("web.cargarMas"))}</button>`
        : "";
      mas.querySelector("button")?.addEventListener("click", (ev) => {
        ev.currentTarget.disabled = true;
        pagina++;
        cargar();
      });
    } catch {
      if (!vigente()) return;
      if (pagina === 1) lista.innerHTML = error();
      else mas.innerHTML = error();
    }
  }

  await cargar();
}
