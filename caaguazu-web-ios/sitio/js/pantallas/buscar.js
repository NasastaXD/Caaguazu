// Buscar no es un indice: una sola pantalla que cambia de cara. Sin nada
// pedido muestra el mosaico de categorias; en cuanto hay texto o un filtro,
// muestra resultados. Calcado de Buscar.kt/Filtros.kt.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, tarjetaLugar, tileCategoria, chip, estadoCargando, estadoVacio, estadoError, Icono } from "../piezas.js";

let categorias = [];
let etiquetas = [];
let filtro = { categoria: null, etiqueta: null, buscar: "", precioMax: null };
let debounce = null;

export async function render(contenedor, params) {
  filtro = {
    categoria: params.get("categoria") ? Number(params.get("categoria")) : null,
    etiqueta: params.get("etiqueta") ? Number(params.get("etiqueta")) : null,
    buscar: params.get("q") || "",
    precioMax: null,
  };

  contenedor.innerHTML = `
    <div class="cabecera">
      <h1 class="titulo-pantalla">${escapar(t("barra.buscar"))}</h1>
      <a class="boton-perfil" href="#/perfil" aria-label="perfil">👤</a>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:var(--entre-secciones)">
      <div class="buscador" style="flex:1;margin-bottom:0">
        ${Icono.buscar}
        <input id="campo-buscar" placeholder="${escapar(t("barra.buscar"))}" value="${escapar(filtro.buscar)}">
      </div>
      <button class="boton-filtro" id="abrir-filtros" aria-label="filtros">${Icono.filtro}</button>
      <a class="boton-filtro" href="#/mapa" aria-label="mapa">${Icono.pin}</a>
    </div>
    <div id="cuerpo-buscar">${estadoCargando()}</div>
    <div id="zona-hoja"></div>
  `;

  const campo = contenedor.querySelector("#campo-buscar");
  campo.addEventListener("input", () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      filtro.buscar = campo.value;
      cargar(contenedor);
    }, 350);
  });

  contenedor.querySelector("#abrir-filtros").addEventListener("click", () => abrirFiltros(contenedor));

  await Promise.all([cargarTerminos(), cargar(contenedor)]);
}

async function cargarTerminos() {
  try {
    [categorias, etiquetas] = await Promise.all([Api.categorias(), Api.etiquetas()]);
  } catch {
    categorias = [];
    etiquetas = [];
  }
}

function hayFiltroActivo() {
  return Boolean(filtro.categoria || filtro.etiqueta || filtro.buscar);
}

async function cargar(contenedor) {
  const cuerpo = contenedor.querySelector("#cuerpo-buscar");
  cuerpo.innerHTML = estadoCargando();

  if (!hayFiltroActivo()) {
    if (categorias.length === 0) await cargarTerminos();
    cuerpo.innerHTML = categorias.length
      ? `<div class="mosaico">${categorias.map(tileCategoria).join("")}</div>`
      : estadoVacio();
    return;
  }

  try {
    const pagina = await Api.inventario({
      categoria: filtro.categoria,
      etiqueta: filtro.etiqueta,
      buscar: filtro.buscar || undefined,
      porPagina: 40,
    });
    let items = pagina.items ?? [];
    if (filtro.precioMax !== null) {
      items = items.filter((i) => (i.rangoPrecio ?? i.rango_precio ?? 0) <= filtro.precioMax);
    }
    cuerpo.innerHTML = items.length
      ? `<div class="grilla-lugares">${items.map(tarjetaLugar).join("")}</div>`
      : estadoVacio(t("estado.vacio"));
  } catch {
    cuerpo.innerHTML = estadoError(() => cargar(contenedor));
  }
}

function abrirFiltros(contenedor) {
  const zona = contenedor.querySelector("#zona-hoja");
  zona.innerHTML = `
    <div class="fondo-hoja" id="fondo-hoja"></div>
    <div class="hoja-inferior">
      <div class="manija"></div>
      <h2 class="titulo-seccion">${escapar(t("filtro.titulo"))}</h2>

      <div class="grupo-filtro">
        <h4>${escapar(t("filtro.categoria"))}</h4>
        <div class="fila-chips">
          ${categorias.map((c) => chip(c.nombre, filtro.categoria === c.id, `data-cat="${c.id}"`)).join("")}
        </div>
      </div>

      <div class="grupo-filtro">
        <h4>${escapar(t("filtro.etiqueta"))}</h4>
        <div class="fila-chips">
          ${etiquetas.map((e) => chip(e.nombre, filtro.etiqueta === e.id, `data-etq="${e.id}"`)).join("")}
        </div>
      </div>

      <div class="grupo-filtro">
        <h4>${escapar(t("filtro.precio"))}</h4>
        <div class="fila-chips">
          ${[null, 0, 1, 2, 3].map((p) => chip(
            p === null ? t("filtro.limpiar") : p === 0 ? t("precio.gratis") : "$".repeat(p),
            filtro.precioMax === p,
            `data-precio="${p === null ? "" : p}"`,
          )).join("")}
        </div>
      </div>

      <button class="boton-primario" id="cerrar-hoja">${escapar(t("filtro.aplicar"))}</button>
    </div>
  `;

  zona.querySelectorAll("[data-cat]").forEach((b) =>
    b.addEventListener("click", () => {
      const id = Number(b.dataset.cat);
      filtro.categoria = filtro.categoria === id ? null : id;
      cargar(contenedor);
      abrirFiltros(contenedor);
    }),
  );
  zona.querySelectorAll("[data-etq]").forEach((b) =>
    b.addEventListener("click", () => {
      const id = Number(b.dataset.etq);
      filtro.etiqueta = filtro.etiqueta === id ? null : id;
      cargar(contenedor);
      abrirFiltros(contenedor);
    }),
  );
  zona.querySelectorAll("[data-precio]").forEach((b) =>
    b.addEventListener("click", () => {
      const v = b.dataset.precio;
      filtro.precioMax = v === "" ? null : Number(v);
      cargar(contenedor);
      abrirFiltros(contenedor);
    }),
  );

  const cerrar = () => (zona.innerHTML = "");
  zona.querySelector("#fondo-hoja").addEventListener("click", cerrar);
  zona.querySelector("#cerrar-hoja").addEventListener("click", cerrar);
}
