// Ajustes: idioma, tema, lo guardado y compartir la web. Nada de cuentas.

import { t, idiomaActual, idiomasDisponibles, elegirIdioma, cargarTextos } from "../idioma.js";
import { temaElegido, elegirTema } from "../tema.js";
import { ajuste } from "../config.js";
import { escapar, Icono, cabeceraPantalla, abrirHoja } from "../piezas.js";
import { listaFavoritos } from "../estado.js";
import { compartir } from "../compartir.js";

const TEMAS = [
  ["sistema", "web.temaSistema"],
  ["claro", "web.temaClaro"],
  ["oscuro", "web.temaOscuro"],
];

export async function render(el) {
  const nombreIdioma = idiomasDisponibles().find((i) => i.codigo === idiomaActual())?.nombre ?? idiomaActual();
  const nombreTema = t(TEMAS.find(([clave]) => clave === temaElegido())?.[1] ?? "web.temaSistema");

  el.innerHTML = `
    ${cabeceraPantalla(t("barra.ajustes"), { volver: true })}

    <section class="seccion">
      <div class="lista">
        <button type="button" class="fila" data-tema>
          <span class="fila__cuerpo"><span class="fila__titulo">${escapar(t("web.tema"))}</span></span>
          <span class="fila__fin">${escapar(nombreTema)} ${Icono.seguir}</span>
        </button>
        <button type="button" class="fila" data-idioma>
          <span class="fila__cuerpo"><span class="fila__titulo">${escapar(t("perfil.idioma"))}</span></span>
          <span class="fila__fin">${escapar(nombreIdioma)} ${Icono.seguir}</span>
        </button>
        <a class="fila" href="#/guardados">
          <span class="fila__cuerpo"><span class="fila__titulo">${escapar(t("web.guardados"))}</span></span>
          <span class="fila__fin">${listaFavoritos().length} ${Icono.seguir}</span>
        </a>
        <button type="button" class="fila" data-compartir>
          <span class="fila__cuerpo"><span class="fila__titulo">${escapar(t("web.compartirWeb"))}</span></span>
          <span class="fila__fin">${Icono.compartir}</span>
        </button>
      </div>
    </section>

    <section class="seccion">
      <div class="lista">
        <div class="fila" style="min-height:0">
          <span class="fila__cuerpo"><span class="fila__titulo">${escapar(t("diag.version"))}</span></span>
          <span class="fila__fin">${escapar(ajuste().version || "—")}</span>
        </div>
      </div>
      <p class="debil" style="margin:12px 2px 0">${escapar(t("web.sinCuenta"))}</p>
    </section>
  `;

  el.querySelector("[data-tema]").addEventListener("click", () => abrirTema(el));
  el.querySelector("[data-idioma]").addEventListener("click", () => abrirIdioma());
  el.querySelector("[data-compartir]").addEventListener("click", () => compartir({ titulo: t("app.nombre"), ruta: "inicio" }));
}

function abrirTema(el) {
  const actual = temaElegido();
  const hoja = abrirHoja(
    TEMAS.map(([clave, etiqueta]) => `
      <button type="button" class="opcion" data-elegir="${clave}" aria-pressed="${clave === actual}">
        <span>${escapar(t(etiqueta))}</span>
        ${clave === actual ? Icono.tilde : ""}
      </button>`).join(""),
    { titulo: t("web.tema") },
  );
  hoja.addEventListener("click", (ev) => {
    const b = ev.target.closest("[data-elegir]");
    if (!b) return;
    elegirTema(b.dataset.elegir);
    hoja.cerrar();
    render(el);
  });
}

function abrirIdioma() {
  const actual = idiomaActual();
  const hoja = abrirHoja(
    idiomasDisponibles().map((i) => `
      <button type="button" class="opcion" data-idioma="${escapar(i.codigo)}" aria-pressed="${i.codigo === actual}">
        <span>${escapar(i.nombre)}</span>
        ${i.codigo === actual ? Icono.tilde : ""}
      </button>`).join(""),
    { titulo: t("perfil.idioma") },
  );
  hoja.addEventListener("click", async (ev) => {
    const b = ev.target.closest("[data-idioma]");
    if (!b || b.dataset.idioma === actual) return;
    elegirIdioma(b.dataset.idioma);
    await cargarTextos();
    // Recargar es lo más seguro: los textos de la barra y de cada pantalla
    // se dibujan una vez, y no hay que volver a dibujarlos uno por uno.
    location.reload();
  });
}
