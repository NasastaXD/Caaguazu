import { t, idiomaActual, idiomasDisponibles, elegirIdioma, cargarTextos } from "../idioma.js";
import { escapar, Icono } from "../piezas.js";
import { listaFavoritos, listaRecorrido } from "../estado.js";

export async function render(contenedor) {
  pintar(contenedor);
}

function pintar(contenedor) {
  contenedor.innerHTML = `
    <div style="margin:-4px 0 var(--entre-secciones)">
      <button class="boton-perfil" id="perfil-volver" style="width:36px;height:36px" aria-label="volver">${Icono.volver}</button>
    </div>
    <h1 class="titulo-pantalla">${escapar(t("perfil.general"))}</h1>

    <div class="seccion">
      <div class="fila-perfil">
        <span>${escapar(t("perfil.idioma"))}</span>
        <button class="enlace" id="abrir-idioma">${escapar(nombreIdiomaActual())}</button>
      </div>
      <div class="fila-perfil">
        <span>${escapar(t("rec.mio"))}</span>
        <span class="descripcion">${listaRecorrido().length}</span>
      </div>
      <div class="fila-perfil">
        <span>♥ favoritos</span>
        <span class="descripcion">${listaFavoritos().length}</span>
      </div>
      <div class="fila-perfil">
        <span>${escapar(t("perfil.acercaDe"))}</span>
        <span class="descripcion">${escapar(t("app.nombre"))}</span>
      </div>
    </div>

    <div id="zona-idioma"></div>
  `;

  contenedor.querySelector("#perfil-volver").addEventListener("click", () => history.back());
  contenedor.querySelector("#abrir-idioma").addEventListener("click", () => abrirIdioma(contenedor));
}

function nombreIdiomaActual() {
  return idiomasDisponibles().find((i) => i.codigo === idiomaActual())?.nombre ?? idiomaActual();
}

function abrirIdioma(contenedor) {
  const zona = contenedor.querySelector("#zona-idioma");
  zona.innerHTML = `
    <div class="fondo-hoja" id="fondo-idioma"></div>
    <div class="hoja-inferior">
      <div class="manija"></div>
      <h2 class="titulo-seccion">${escapar(t("perfil.idioma"))}</h2>
      ${idiomasDisponibles().map((i) => `
        <button class="fila-idioma" data-idioma="${i.codigo}" style="width:100%;text-align:left">
          <span>${escapar(i.nombre)}</span>
          ${i.codigo === idiomaActual() ? `<span class="tilde">${Icono.tilde}</span>` : ""}
        </button>`).join("")}
    </div>
  `;

  const cerrar = () => (zona.innerHTML = "");
  zona.querySelector("#fondo-idioma").addEventListener("click", cerrar);
  zona.querySelectorAll("[data-idioma]").forEach((b) =>
    b.addEventListener("click", async () => {
      elegirIdioma(b.dataset.idioma);
      await cargarTextos();
      cerrar();
      location.reload();
    }),
  );
}
