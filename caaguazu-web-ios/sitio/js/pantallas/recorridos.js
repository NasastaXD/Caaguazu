import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, estadoCargando, estadoVacio, estadoError } from "../piezas.js";

export async function render(contenedor) {
  contenedor.innerHTML = `
    <div class="cabecera">
      <h1 class="titulo-pantalla">${escapar(t("nav.recorridos"))}</h1>
      <a class="boton-perfil" href="#/perfil" aria-label="perfil">👤</a>
    </div>
    <div id="cuerpo-recorridos">${estadoCargando()}</div>
  `;
  await cargar(contenedor);
}

async function cargar(contenedor) {
  const cuerpo = contenedor.querySelector("#cuerpo-recorridos");
  try {
    const pagina = await Api.recorridos();
    const items = pagina.items ?? [];
    cuerpo.innerHTML = items.length ? items.map(tarjeta).join("") : estadoVacio(t("rec.vacio"));
  } catch {
    cuerpo.innerHTML = estadoError(() => cargar(contenedor));
  }
}

function tarjeta(r) {
  return `
    <a href="#/recorrido/${r.id}" style="display:block;margin-bottom:var(--entre-tarjetas)">
      <div style="border-radius:var(--radio-tarjeta);overflow:hidden;aspect-ratio:16/9;background:var(--banda);box-shadow:var(--sombra-tarjeta);position:relative">
        ${r.portada?.url ? `<img src="${escapar(r.portada.url)}" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">` : ""}
      </div>
      <div class="titulo-tarjeta" style="margin-top:10px;white-space:normal">${escapar(r.titulo)}</div>
      <div class="meta" style="color:var(--tinta-suave);font-size:13px">
        ${[r.duracionEstimada, r.cantidadParadas ? `${r.cantidadParadas} ${t("rec.paradas").toLowerCase()}` : ""].filter(Boolean).map(escapar).join(" · ")}
      </div>
    </a>`;
}
