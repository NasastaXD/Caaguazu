// Inicio: lo que hay para ver, en el orden en que alguien de visita lo busca.
// Una sección sin contenido no se dibuja — nada de títulos con «Ver todo» y
// un hueco abajo, que es como se veía con el inventario recién empezando.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import {
  escapar, Icono, tarjetaLugar, tarjetaCategoria, filaArticulo, botonAjustes,
  esqueletoGrilla, error, vacio, fechaCorta,
} from "../piezas.js";

export async function render(el, { vigente, hayAsistente }) {
  el.innerHTML = `
    <div class="cabeza">
      <a class="marca" href="#/inicio" aria-label="Caaguazú Turismo">
        <img src="assets/icon-192.png" alt="">
        <span>
          <span class="marca__nombre">Caaguazú</span><br>
          <span class="marca__lema">${escapar(t("web.lema"))}</span>
        </span>
      </a>
      ${botonAjustes()}
    </div>
    <a class="buscador" href="#/buscar">${Icono.buscar}<span>${escapar(t("web.buscarLugares"))}</span></a>
    <div id="cuerpo-inicio">${esqueletoGrilla(4)}</div>
  `;

  const [cats, inv, evs, arts] = await Promise.allSettled([
    Api.categorias(),
    Api.inventario({ porPagina: 12 }),
    Api.eventos(),
    Api.articulos({ porPagina: 3 }),
  ]);
  if (!vigente()) return;

  const cuerpo = el.querySelector("#cuerpo-inicio");
  const todoFallo = [cats, inv, evs, arts].every((r) => r.status === "rejected");
  if (todoFallo) {
    cuerpo.innerHTML = error();
    return;
  }

  const categorias = cats.status === "fulfilled" && Array.isArray(cats.value) ? cats.value : [];
  const lugares = inv.status === "fulfilled" ? inv.value.items ?? [] : [];
  const totalLugares = inv.status === "fulfilled" ? inv.value.total ?? lugares.length : 0;
  const articulos = arts.status === "fulfilled" ? arts.value.items ?? [] : [];
  const totalArticulos = arts.status === "fulfilled" ? arts.value.total ?? articulos.length : 0;

  // El evento que se destaca: el que está pasando, o si no el próximo. Sólo
  // los que salen de una ficha tienen pantalla propia acá (`ficha_id`).
  const eventos = evs.status === "fulfilled" ? evs.value.items ?? [] : [];
  const vivos = eventos.filter((e) => e.fechas && !e.fechas.terminado && e.ficha_id);
  const evento = vivos.find((e) => e.fechas.en_curso) ?? vivos[0];

  const partes = [];

  if (evento) {
    const ahora = evento.fechas.en_curso;
    partes.push(`
      <section class="seccion">
        <a class="tarjeta aparece" href="#/ficha/${evento.ficha_id}">
          <div class="tarjeta__foto" style="aspect-ratio:16/9">
            ${evento.portada?.url ? `<img src="${escapar(evento.portada.url)}" alt="" loading="lazy">` : ""}
            <span class="sello ${ahora ? "sello--ahora" : ""}">${escapar(ahora ? t("evento.enCurso") : fechaCorta(evento.fechas.inicio))}</span>
          </div>
          <div class="tarjeta__cuerpo">
            <div class="eyebrow">${escapar(t("principal.eventos"))}</div>
            <div class="tarjeta__titulo" style="font-size:1.1rem">${escapar(evento.titulo)}</div>
            ${evento.resumen ? `<p class="tarjeta__texto">${escapar(evento.resumen)}</p>` : ""}
          </div>
        </a>
      </section>`);
  }

  if (categorias.length) {
    partes.push(`
      <section class="seccion">
        <div class="seccion__cabeza"><h2 class="subtitulo">${escapar(t("web.categorias"))}</h2></div>
        <div class="carril">${categorias.map(tarjetaCategoria).join("")}</div>
      </section>`);
  }

  if (lugares.length) {
    partes.push(`
      <section class="seccion">
        <div class="seccion__cabeza">
          <h2 class="subtitulo">${escapar(t("web.paraVisitar"))}</h2>
          ${totalLugares > 6 ? `<a class="ver-todo" href="#/buscar?todo=1">${escapar(t("banda.verTodo"))}</a>` : ""}
        </div>
        <div class="grilla">${lugares.slice(0, 6).map(tarjetaLugar).join("")}</div>
      </section>`);
  }

  if (hayAsistente) {
    partes.push(`
      <section class="seccion">
        <a class="aviso aparece" href="#/asistente">
          <span class="aviso__icono">${Icono.asistente}</span>
          <span class="aviso__cuerpo">
            <span class="aviso__titulo">${escapar(t("web.ia.aviso.titulo"))}</span><br>
            <span class="aviso__texto">${escapar(t("web.ia.aviso.texto"))}</span>
          </span>
          <span class="fila__fin">${Icono.seguir}</span>
        </a>
      </section>`);
  }

  if (articulos.length) {
    partes.push(`
      <section class="seccion">
        <div class="seccion__cabeza">
          <h2 class="subtitulo">${escapar(t("web.articulos"))}</h2>
          ${totalArticulos > 3 ? `<a class="ver-todo" href="#/articulos">${escapar(t("banda.verTodo"))}</a>` : ""}
        </div>
        <div class="lista">${articulos.map(filaArticulo).join("")}</div>
      </section>`);
  }

  partes.push(`
    <section class="seccion">
      <a class="aviso aparece" href="#/app">
        <span class="aviso__icono">${Icono.ruta}</span>
        <span class="aviso__cuerpo">
          <span class="aviso__titulo">${escapar(t("web.app.aviso.titulo"))}</span><br>
          <span class="aviso__texto">${escapar(t("web.app.aviso.texto"))}</span>
        </span>
        <span class="fila__fin">${Icono.seguir}</span>
      </a>
    </section>`);

  const hayContenido = evento || categorias.length || lugares.length || articulos.length;
  cuerpo.innerHTML = (hayContenido ? "" : vacio(t("estado.vacio"))) + partes.join("");
}
