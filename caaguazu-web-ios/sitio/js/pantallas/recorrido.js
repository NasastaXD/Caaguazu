import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, estadoCargando, estadoError, Icono } from "../piezas.js";
import { enlaceRecorrido, MAX_PARADAS_INTERMEDIAS } from "../mapas.js";

export async function render(contenedor, params, id) {
  contenedor.innerHTML = estadoCargando();
  try {
    const r = await Api.recorrido(id);
    pintar(contenedor, r);
  } catch {
    contenedor.innerHTML = estadoError(() => render(contenedor, params, id));
  }
}

function pintar(contenedor, r) {
  const paradas = r.paradas ?? [];
  const conPunto = paradas.filter((p) => p.disponible && p.coordenadas);
  const enlace = r.googleMaps || enlaceRecorrido(conPunto.map((p) => p.coordenadas));
  const noEntra = !r.googleMaps && conPunto.length >= 2 && !enlace;

  contenedor.innerHTML = `
    <div style="margin:-4px 0 14px">
      <a href="#/recorridos" class="boton-perfil" style="width:36px;height:36px" aria-label="volver">${Icono.volver}</a>
    </div>
    ${r.portada?.url ? `<div style="border-radius:var(--radio-tarjeta);overflow:hidden;margin-bottom:20px;aspect-ratio:16/9;background:var(--banda)"><img src="${escapar(r.portada.url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>` : ""}
    <h1 class="titulo-pagina">${escapar(r.titulo)}</h1>
    <div class="meta" style="color:var(--tinta-suave);margin:6px 0 var(--entre-secciones)">
      ${escapar([r.duracionEstimada, r.cantidadParadas ? `${r.cantidadParadas} ${t("rec.paradas").toLowerCase()}` : ""].filter(Boolean).join(" · "))}
    </div>

    ${r.resumen ? `<p class="descripcion" style="margin-bottom:var(--entre-secciones)">${escapar(r.resumen)}</p>` : ""}

    ${enlace ? `<a class="boton-primario" style="margin-bottom:var(--entre-secciones)" target="_blank" rel="noopener" href="${escapar(enlace)}">${Icono.pin} ${escapar(t("ficha.mapa"))}</a>`
      : noEntra ? `<div class="aviso-parcial">${escapar(`${t("rec.demasiadas")} (>${MAX_PARADAS_INTERMEDIAS})`)}</div>` : ""}

    <div class="seccion" style="margin-top:var(--entre-secciones)">
      <h2 class="titulo-seccion">${escapar(t("rec.paradas"))}</h2>
      ${paradas.map(parada).join("")}
    </div>

    ${r.costoTotal?.detalle?.length ? `
      <div class="seccion">
        <h2 class="titulo-seccion">${escapar(t("ficha.costo"))}</h2>
        <ul class="descripcion">${r.costoTotal.detalle.map((d) => `<li>${escapar(d)}</li>`).join("")}</ul>
      </div>` : ""}
  `;
}

function parada(p) {
  const disponible = p.disponible !== false;
  const contenido = disponible
    ? `<a href="#/ficha/${p.refId ?? p.ref_id}" style="display:contents">
        <div class="titulo-tarjeta" style="white-space:normal">${escapar(p.titulo)}</div>
        ${p.texto ? `<div class="descripcion">${escapar(p.texto)}</div>` : ""}
      </a>`
    : `<div class="titulo-tarjeta" style="white-space:normal">${escapar(t("rec.noDisponible"))}</div>`;
  return `
    <div class="parada ${disponible ? "" : "no-disponible"}">
      <div class="orden">${p.orden ?? ""}</div>
      <div class="cuerpo">${contenido}</div>
    </div>`;
}
