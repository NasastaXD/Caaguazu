import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, estadoCargando, estadoVacio, estadoError, fechaCorta } from "../piezas.js";

export async function render(contenedor) {
  contenedor.innerHTML = `
    <div class="cabecera">
      <h1 class="titulo-pantalla">${escapar(t("nav.articulos"))}</h1>
      <a class="boton-perfil" href="#/perfil" aria-label="perfil">👤</a>
    </div>
    <div id="cuerpo-articulos">${estadoCargando()}</div>
  `;
  await cargar(contenedor);
}

async function cargar(contenedor) {
  const cuerpo = contenedor.querySelector("#cuerpo-articulos");
  try {
    const pagina = await Api.articulos({ porPagina: 30 });
    const items = pagina.items ?? [];
    cuerpo.innerHTML = items.length
      ? items.map(tarjeta).join("")
      : estadoVacio();
  } catch {
    cuerpo.innerHTML = estadoError(() => cargar(contenedor));
  }
}

function tarjeta(a) {
  return `
    <a href="#/articulo/${a.id}" style="display:flex;gap:14px;margin-bottom:var(--entre-tarjetas)">
      <div style="width:96px;height:96px;flex:none;border-radius:var(--radio-media);overflow:hidden;background:var(--banda)">
        ${a.portada?.url ? `<img src="${escapar(a.portada.url)}" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">` : ""}
      </div>
      <div>
        ${a.antetitulo ? `<div class="texto-fecha">${escapar(a.antetitulo)}</div>` : ""}
        <div class="titular-tarjeta-articulo">${escapar(a.titulo)}</div>
        ${a.publicado ? `<div class="meta" style="color:var(--tinta-suave);font-size:13px;margin-top:2px">${escapar(fechaCorta(a.publicado))}</div>` : ""}
      </div>
    </a>`;
}
