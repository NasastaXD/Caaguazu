// Router minimo por hash, sin build ni dependencias de framework.

import { cargarTextos, idiomasDisponibles, aplicarDisponibles, t } from "./idioma.js";
import { Api } from "./api.js";
import { escapar, Icono } from "./piezas.js";
import { alSuscribirEstado, esFavorito, alternarFavorito } from "./estado.js";

import * as Inicio from "./pantallas/inicio.js";
import * as Buscar from "./pantallas/buscar.js";
import * as Ficha from "./pantallas/ficha.js";
import * as Articulos from "./pantallas/articulos.js";
import * as Articulo from "./pantallas/articulo.js";
import * as Recorridos from "./pantallas/recorridos.js";
import * as Recorrido from "./pantallas/recorrido.js";
import * as Mapa from "./pantallas/mapa.js";
import * as Perfil from "./pantallas/perfil.js";

const contenedor = document.getElementById("contenido");
const barraInferior = document.getElementById("barra-inferior");

const SECCIONES = ["inicio", "buscar", "articulos", "recorridos"];

const RUTAS = [
  { patron: /^buscar$/, pantalla: Buscar, seccion: "buscar" },
  { patron: /^ficha\/(\d+)$/, pantalla: Ficha, seccion: null },
  { patron: /^articulos$/, pantalla: Articulos, seccion: "articulos" },
  { patron: /^articulo\/(\d+)$/, pantalla: Articulo, seccion: null },
  { patron: /^recorridos$/, pantalla: Recorridos, seccion: "recorridos" },
  { patron: /^recorrido\/(\d+)$/, pantalla: Recorrido, seccion: null },
  { patron: /^mapa$/, pantalla: Mapa, seccion: null },
  { patron: /^perfil$/, pantalla: Perfil, seccion: null },
  { patron: /^inicio$/, pantalla: Inicio, seccion: "inicio" },
];

function pintarBarra(seccionActiva) {
  barraInferior.innerHTML = `
    <a class="item-nav ${seccionActiva === "inicio" ? "activo" : ""}" href="#/inicio">
      <span class="icono">${Icono.inicio}</span><span class="etiqueta-nav">${escapar(t("nav.principal"))}</span>
    </a>
    <a class="item-nav ${seccionActiva === "buscar" ? "activo" : ""}" href="#/buscar">
      <span class="icono">${Icono.buscar}</span><span class="etiqueta-nav">${escapar(t("barra.buscar"))}</span>
    </a>
    <a class="item-nav ${seccionActiva === "articulos" ? "activo" : ""}" href="#/articulos">
      <span class="icono">${Icono.articulo}</span><span class="etiqueta-nav">${escapar(t("nav.articulos"))}</span>
    </a>
    <a class="item-nav ${seccionActiva === "recorridos" ? "activo" : ""}" href="#/recorridos">
      <span class="icono">${Icono.recorrido}</span><span class="etiqueta-nav">${escapar(t("nav.recorridos"))}</span>
    </a>
  `;
}

async function enrutar() {
  const hash = (location.hash || "#/inicio").replace(/^#\/?/, "");
  const [ruta, query] = hash.split("?");
  const params = new URLSearchParams(query || "");

  const encontrada = RUTAS.find((r) => r.patron.test(ruta));
  if (!encontrada) {
    location.hash = "#/inicio";
    return;
  }

  const match = ruta.match(encontrada.patron);
  const id = match[1] ? Number(match[1]) : null;

  document.documentElement.lang = document.documentElement.lang || "es";
  barraInferior.style.display = encontrada.seccion || ruta === "inicio" ? "flex" : "none";
  document.body.classList.toggle("con-barra", Boolean(barraInferior.style.display === "flex"));

  window.scrollTo(0, 0);
  pintarBarra(encontrada.seccion);
  await encontrada.pantalla.render(contenedor, params, id);
}

// Los corazones de las tarjetas se pintan en muchas pantallas distintas: un
// solo listener delegado en el body evita repetirlo en cada modulo.
document.addEventListener("click", (ev) => {
  const boton = ev.target.closest("[data-favorito]");
  if (!boton) return;
  ev.preventDefault();
  alternarFavorito(Number(boton.dataset.favorito));
});

alSuscribirEstado(() => {
  document.querySelectorAll("[data-favorito]").forEach((boton) => {
    const activo = esFavorito(Number(boton.dataset.favorito));
    boton.classList.toggle("activo", activo);
    boton.innerHTML = activo ? Icono.corazon : Icono.corazonBorde;
  });
});

async function iniciar() {
  await cargarTextos();
  try {
    const idiomas = await Api.idiomas();
    if (Array.isArray(idiomas?.idiomas)) {
      aplicarDisponibles(idiomas.idiomas.map((i) => ({ codigo: i.codigo, nombre: i.nombre })));
    }
  } catch {
    /* sin red: se sigue con el respaldo de tres idiomas */
  }

  window.addEventListener("hashchange", enrutar);
  await enrutar();
}

iniciar();
