// Buscar es una sola pantalla que cambia de cara: sin nada pedido muestra las
// categorías; en cuanto hay texto, un filtro o «ver todo», muestra lugares.
//
// Lo que se pidió vive en la dirección (#/buscar?q=…&categoria=…), así que
// volver desde una ficha encuentra los mismos resultados. Se reescribe con
// history.replaceState, que no dispara hashchange: escribir no recarga la
// pantalla en cada letra.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import {
  escapar, Icono, tarjetaLugar, tarjetaCategoria, chip, cabeceraPantalla, botonAjustes,
  esqueletoGrilla, vacio, error, abrirHoja, precioA,
} from "../piezas.js";

let categorias = [];
let etiquetas = [];

export async function render(el, { params, vigente }) {
  const filtro = {
    buscar: params.get("q") || "",
    categoria: Number(params.get("categoria")) || null,
    etiqueta: Number(params.get("etiqueta")) || null,
    precio: params.has("precio") ? Number(params.get("precio")) : null,
    todo: params.get("todo") === "1",
  };

  el.innerHTML = `
    ${cabeceraPantalla(t("barra.buscar"), { derecha: botonAjustes() })}
    <div class="fila-buscar">
      <label class="buscador">
        ${Icono.buscar}
        <input id="campo-buscar" type="search" enterkeyhint="search" autocomplete="off"
          placeholder="${escapar(t("web.buscarLugares"))}" value="${escapar(filtro.buscar)}">
      </label>
      <button type="button" class="boton-icono" id="abrir-filtros" aria-label="${escapar(t("filtro.titulo"))}">${Icono.filtro}</button>
      <a class="boton-icono" href="#/mapa" aria-label="${escapar(t("web.verMapa"))}">${Icono.mapa}</a>
    </div>
    <div id="activos"></div>
    <div id="resultados">${esqueletoGrilla(4)}</div>
  `;

  const campo = el.querySelector("#campo-buscar");
  let espera = null;
  campo.addEventListener("input", () => {
    clearTimeout(espera);
    // Se espera a que la persona deje de escribir: una consulta por palabra,
    // no una por letra.
    espera = setTimeout(() => {
      filtro.buscar = campo.value.trim();
      actualizar();
    }, 350);
  });
  campo.addEventListener("keydown", (ev) => {
    if (ev.key === "Enter") {
      clearTimeout(espera);
      filtro.buscar = campo.value.trim();
      campo.blur();
      actualizar();
    }
  });
  el.querySelector("#abrir-filtros").addEventListener("click", () => abrirFiltros());

  el.addEventListener("click", (ev) => {
    const quitar = ev.target.closest("[data-quitar]");
    if (quitar) {
      filtro[quitar.dataset.quitar] = quitar.dataset.quitar === "buscar" ? "" : null;
      if (quitar.dataset.quitar === "buscar") campo.value = "";
      actualizar();
    }
    if (ev.target.closest("[data-ver-todo]")) {
      filtro.todo = true;
      actualizar();
    }
  });

  if (!categorias.length) {
    try {
      [categorias, etiquetas] = await Promise.all([Api.categorias(), Api.etiquetas()]);
    } catch {
      categorias = [];
      etiquetas = [];
    }
  }
  if (!vigente()) return;
  await cargar();

  function hayPedido() {
    return Boolean(filtro.buscar || filtro.categoria || filtro.etiqueta || filtro.precio !== null || filtro.todo);
  }

  function actualizar() {
    const p = new URLSearchParams();
    if (filtro.buscar) p.set("q", filtro.buscar);
    if (filtro.categoria) p.set("categoria", filtro.categoria);
    if (filtro.etiqueta) p.set("etiqueta", filtro.etiqueta);
    if (filtro.precio !== null) p.set("precio", filtro.precio);
    if (filtro.todo && !filtro.buscar && !filtro.categoria && !filtro.etiqueta) p.set("todo", "1");
    const q = p.toString();
    history.replaceState(null, "", "#/buscar" + (q ? "?" + q : ""));
    cargar();
  }

  function pintarActivos() {
    const zona = el.querySelector("#activos");
    const activos = [];
    if (filtro.buscar) activos.push(["buscar", `“${filtro.buscar}”`]);
    if (filtro.categoria) activos.push(["categoria", categorias.find((c) => c.id === filtro.categoria)?.nombre ?? t("filtro.categoria")]);
    if (filtro.etiqueta) activos.push(["etiqueta", etiquetas.find((e) => e.id === filtro.etiqueta)?.nombre ?? t("filtro.etiqueta")]);
    if (filtro.precio !== null) activos.push(["precio", filtro.precio === 0 ? t("precio.gratis") : `${t("web.hasta")} ${precioA(filtro.precio)}`]);
    zona.innerHTML = activos.length
      ? `<div class="chips" style="margin:-8px 0 18px">${activos
          .map(([clave, texto]) => `<button type="button" class="chip elegido" data-quitar="${clave}" aria-label="${escapar(t("filtro.limpiar"))}: ${escapar(texto)}">${escapar(texto)}&nbsp;${Icono.cerrar.replace('width="20" height="20"', 'width="14" height="14"')}</button>`)
          .join("")}</div>`
      : "";
  }

  async function cargar() {
    const zona = el.querySelector("#resultados");
    pintarActivos();

    if (!hayPedido()) {
      zona.innerHTML = categorias.length
        ? `<h2 class="subtitulo" style="margin-bottom:12px">${escapar(t("web.categorias"))}</h2>
           <div class="grilla">${categorias.map(tarjetaCategoria).join("")}</div>
           <button type="button" class="boton boton--fantasma boton--ancho" style="margin-top:16px" data-ver-todo>${escapar(t("web.verTodosLosLugares"))}</button>`
        : `<button type="button" class="boton boton--fantasma boton--ancho" data-ver-todo>${escapar(t("web.verTodosLosLugares"))}</button>`;
      return;
    }

    zona.innerHTML = esqueletoGrilla(4);
    const pedido = JSON.stringify(filtro);
    try {
      const pagina = await Api.inventario({
        buscar: filtro.buscar || undefined,
        categoria: filtro.categoria,
        etiqueta: filtro.etiqueta,
        porPagina: 60,
      });
      // Si mientras tanto se pidió otra cosa, esta respuesta llegó tarde.
      if (!vigente() || pedido !== JSON.stringify(filtro)) return;
      let items = pagina.items ?? [];
      if (filtro.precio !== null) items = items.filter((i) => (i.rango_precio ?? 0) <= filtro.precio);
      zona.innerHTML = items.length
        ? `<p class="debil" style="margin:0 0 10px">${escapar(items.length === 1 ? t("web.unResultado") : `${items.length} ${t("inv.resultados").toLowerCase()}`)}</p>
           <div class="grilla">${items.map(tarjetaLugar).join("")}</div>`
        : vacio(t("web.sinResultados"));
    } catch {
      if (vigente()) zona.innerHTML = error();
    }
  }

  function abrirFiltros() {
    const precios = [null, 0, 1, 2, 3];
    const hoja = abrirHoja(
      `${categorias.length ? `
        <div class="hoja__grupo">
          <h3 class="eyebrow">${escapar(t("filtro.categoria"))}</h3>
          <div class="chips">${categorias.map((c) => chip(c.nombre, filtro.categoria === c.id, `data-cat="${c.id}"`)).join("")}</div>
        </div>` : ""}
      ${etiquetas.length ? `
        <div class="hoja__grupo">
          <h3 class="eyebrow">${escapar(t("filtro.etiqueta"))}</h3>
          <div class="chips">${etiquetas.map((e) => chip(e.nombre, filtro.etiqueta === e.id, `data-etq="${e.id}"`)).join("")}</div>
        </div>` : ""}
      <div class="hoja__grupo">
        <h3 class="eyebrow">${escapar(t("filtro.precio"))}</h3>
        <div class="chips">${precios.map((p) => chip(
          p === null ? t("web.cualquiera") : p === 0 ? t("precio.gratis") : `${t("web.hasta")} ${"$".repeat(p)}`,
          filtro.precio === p,
          `data-precio="${p === null ? "" : p}"`,
        )).join("")}</div>
      </div>
      <button type="button" class="boton boton--primario boton--ancho" data-aplicar>${escapar(t("web.verResultados"))}</button>`,
      { titulo: t("filtro.titulo") },
    );

    const marcar = (selector, elegido) => {
      hoja.querySelectorAll(selector).forEach((b) => {
        const si = elegido(b);
        b.classList.toggle("elegido", si);
        b.setAttribute("aria-pressed", String(si));
      });
    };

    hoja.addEventListener("click", (ev) => {
      const cat = ev.target.closest("[data-cat]");
      const etq = ev.target.closest("[data-etq]");
      const pre = ev.target.closest("[data-precio]");
      if (cat) {
        const id = Number(cat.dataset.cat);
        filtro.categoria = filtro.categoria === id ? null : id;
        marcar("[data-cat]", (b) => Number(b.dataset.cat) === filtro.categoria);
      } else if (etq) {
        const id = Number(etq.dataset.etq);
        filtro.etiqueta = filtro.etiqueta === id ? null : id;
        marcar("[data-etq]", (b) => Number(b.dataset.etq) === filtro.etiqueta);
      } else if (pre) {
        filtro.precio = pre.dataset.precio === "" ? null : Number(pre.dataset.precio);
        marcar("[data-precio]", (b) => (b.dataset.precio === "" ? null : Number(b.dataset.precio)) === filtro.precio);
      } else if (ev.target.closest("[data-aplicar]")) {
        hoja.cerrar();
        actualizar();
      }
    });
  }
}
