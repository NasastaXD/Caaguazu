import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, tarjetaLugar, estadoCargando, estadoError, fechaCorta } from "../piezas.js";

export async function render(contenedor) {
  contenedor.innerHTML = `
    <div class="cabecera">
      <h1 class="titulo-pantalla">${escapar(t("nav.principal"))}</h1>
      <a class="boton-perfil" href="#/perfil" aria-label="perfil">👤</a>
    </div>
    <div class="buscador" id="ir-buscar">
      ${"" /* icono buscar */}
      <span style="opacity:.6">${"\u{1F50D}"}</span>
      <input readonly placeholder="${escapar(t("barra.buscar"))}">
    </div>
    <div id="cuerpo-inicio">${estadoCargando()}</div>
  `;

  document.getElementById("ir-buscar").addEventListener("click", () => {
    location.hash = "#/buscar";
  });

  await cargar(contenedor);
}

async function cargar(contenedor) {
  const cuerpo = contenedor.querySelector("#cuerpo-inicio");
  try {
    const [categorias, inventario, eventos] = await Promise.all([
      Api.categorias(),
      Api.inventario({ porPagina: 12 }),
      Api.eventos(),
    ]);

    const items = inventario.items ?? [];
    const listaEventos = (eventos.items ?? []).filter((e) => e.fechas && !e.fechas.terminado);
    const enCurso = listaEventos.find((e) => e.fechas?.enCurso ?? e.fechas?.en_curso);
    const proximo = enCurso ?? listaEventos[0];

    cuerpo.innerHTML = `
      ${categorias.length ? `
        <div class="seccion">
          <div class="fila-titulo-seccion"><h2 class="titulo-seccion" style="margin:0">${escapar(t("nav.inventario"))}</h2></div>
          <div class="fila-chips">
            ${categorias.slice(0, 8).map((c) => `<a class="chip" href="#/buscar?categoria=${c.id}">${escapar(c.nombre)}</a>`).join("")}
          </div>
        </div>` : ""}

      ${proximo ? `
        <div class="seccion">
          <a class="destacado-evento" href="#/ficha/${proximo.id}">
            ${proximo.portada?.url ? `<img src="${escapar(proximo.portada.url)}" alt="" loading="lazy">` : ""}
            <div class="velo-degradado"></div>
            <div class="contenido">
              <span class="badge-ahora">${escapar((proximo.fechas?.enCurso ?? proximo.fechas?.en_curso) ? t("evento.enCurso") : t("principal.eventos"))}</span>
              <h3>${escapar(proximo.titulo)}</h3>
              <div class="fecha-evento">${escapar(fechaCorta(proximo.fechas?.inicio))}</div>
            </div>
          </a>
        </div>` : ""}

      <div class="seccion">
        <div class="fila-titulo-seccion">
          <h2 class="titulo-seccion" style="margin:0">${escapar(t("nav.inventario"))}</h2>
          <a class="enlace" href="#/buscar">${escapar(t("banda.verTodo"))}</a>
        </div>
        ${items.length ? `<div class="mosaico">${items.slice(0, 6).map(tarjetaLugar).join("")}</div>` : ""}
      </div>

      <div class="seccion">
        <div class="fila-titulo-seccion">
          <h2 class="titulo-seccion" style="margin:0">${escapar(t("nav.recorridos"))}</h2>
          <a class="enlace" href="#/recorridos">${escapar(t("banda.verTodo"))}</a>
        </div>
      </div>

      <div class="seccion">
        <div class="fila-titulo-seccion">
          <h2 class="titulo-seccion" style="margin:0">${escapar(t("nav.articulos"))}</h2>
          <a class="enlace" href="#/articulos">${escapar(t("banda.verTodo"))}</a>
        </div>
      </div>
    `;
  } catch {
    cuerpo.innerHTML = estadoError(() => cargar(contenedor));
  }
}
