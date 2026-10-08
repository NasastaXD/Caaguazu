// Router minimo por hash, sin build ni dependencias de framework.

import { cargarTextos, idiomasDisponibles, aplicarDisponibles, t } from "./idioma.js";
import { Api } from "./api.js";
import { escapar, Icono, estadoCargando } from "./piezas.js";
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

// El asistente no se importa como las demás pantallas: va con import()
// dinámico. Un import estático que falla —un archivo viejo en caché que no
// tiene lo que el nuevo le pide, un error de sintaxis— deja en blanco el
// espejo entero, como en la 1.0.1. Cargado aparte, si falla no hay botón y
// todo lo demás sigue igual.
let asistente = null;
let cargaAsistente = null;
let seccionActual = null;

function cargarAsistente() {
  if (!cargaAsistente) {
    cargaAsistente = import("./pantallas/asistente.js")
      .then((m) => {
        asistente = m;
        // El botón aparece o se va cuando el panel lo dice, no al arrancar:
        // la barra se repinta en ese momento.
        m.alCambiarDisponible(() => pintarBarra(seccionActual));
        return m;
      })
      .catch(() => null);
  }
  return cargaAsistente;
}

const PantallaAsistente = {
  render(lienzo, params) {
    // Camino rápido, sincrónico a propósito: es el del botón de la barra, y
    // iOS sólo sube el teclado si el foco llega dentro de ese mismo toque.
    if (asistente?.estaDisponible()) return asistente.render(lienzo, params);

    // Camino lento: un enlace directo a #/asistente, o recargar con la charla
    // abierta. Sin un asistente confirmado no hay charla: se va al inicio.
    lienzo.innerHTML = estadoCargando();
    return (async () => {
      const m = await cargarAsistente();
      const ok = Boolean(m) && (await m.consultarDisponible());
      if (leerHash().ruta !== "asistente") return;
      if (ok) m.render(lienzo, params);
      else location.replace("#/inicio");
    })();
  },
};

const RUTAS = [
  { patron: /^buscar$/, pantalla: Buscar, seccion: "buscar" },
  { patron: /^ficha\/(\d+)$/, pantalla: Ficha, seccion: null },
  { patron: /^articulos$/, pantalla: Articulos, seccion: "articulos" },
  { patron: /^articulo\/(\d+)$/, pantalla: Articulo, seccion: null },
  { patron: /^recorridos$/, pantalla: Recorridos, seccion: "recorridos" },
  { patron: /^recorrido\/(\d+)$/, pantalla: Recorrido, seccion: null },
  { patron: /^mapa$/, pantalla: Mapa, seccion: null },
  { patron: /^perfil$/, pantalla: Perfil, seccion: null },
  { patron: /^asistente$/, pantalla: PantallaAsistente, seccion: null },
  { patron: /^inicio$/, pantalla: Inicio, seccion: "inicio" },
];

function pintarBarra(seccionActiva) {
  // El botón del medio no es una quinta sección: es una puerta a la charla,
  // entre la segunda y la tercera, y nunca queda marcado como activo. Va sin
  // aria-label: su nombre accesible es el texto que se ve.
  const conAsistente = Boolean(asistente?.estaDisponible());
  barraInferior.innerHTML = `
    <a class="item-nav ${seccionActiva === "inicio" ? "activo" : ""}" href="#/inicio">
      <span class="icono">${Icono.inicio}</span><span class="etiqueta-nav">${escapar(t("nav.principal"))}</span>
    </a>
    <a class="item-nav ${seccionActiva === "buscar" ? "activo" : ""}" href="#/buscar">
      <span class="icono">${Icono.buscar}</span><span class="etiqueta-nav">${escapar(t("barra.buscar"))}</span>
    </a>
    ${conAsistente ? `<button type="button" class="boton-asistente" id="abrir-asistente">${escapar(t("nav.ia"))}</button>` : ""}
    <a class="item-nav ${seccionActiva === "articulos" ? "activo" : ""}" href="#/articulos">
      <span class="icono">${Icono.articulo}</span><span class="etiqueta-nav">${escapar(t("nav.articulos"))}</span>
    </a>
    <a class="item-nav ${seccionActiva === "recorridos" ? "activo" : ""}" href="#/recorridos">
      <span class="icono">${Icono.recorrido}</span><span class="etiqueta-nav">${escapar(t("nav.recorridos"))}</span>
    </a>
  `;
  // La barra crece con el círculo: el final de las listas no puede quedar
  // tapado.
  document.body.classList.toggle("con-asistente", conAsistente);
}

function leerHash() {
  const hash = (location.hash || "#/inicio").replace(/^#\/?/, "");
  const [ruta, query] = hash.split("?");
  return { ruta, query };
}

async function enrutar() {
  const { ruta, query } = leerHash();
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

  document.body.classList.toggle("en-asistente", ruta === "asistente");
  seccionActual = encontrada.seccion;

  window.scrollTo(0, 0);
  pintarBarra(encontrada.seccion);

  // Cada navegación pinta en un lienzo nuevo. Las pantallas escriben cuando
  // termina su fetch, sin fijarse si siguen siendo la de turno: una ficha
  // abierta desde la charla que termina de cargar después de volver escribe
  // en un div suelto, en vez de pisar la charla.
  const lienzo = document.createElement("div");
  // textContent y no replaceChildren: esta última necesita Safari 14, y el
  // espejo funciona desde iOS 13.4.
  contenedor.textContent = "";
  contenedor.appendChild(lienzo);
  await encontrada.pantalla.render(lienzo, params, id);
}

// Los corazones de las tarjetas se pintan en muchas pantallas distintas: un
// solo listener delegado en el body evita repetirlo en cada modulo.
document.addEventListener("click", (ev) => {
  const boton = ev.target.closest("[data-favorito]");
  if (!boton) return;
  ev.preventDefault();
  alternarFavorito(Number(boton.dataset.favorito));
});

// El botón del asistente es un <button> y no un <a href="#/asistente">: con el
// enlace, la charla se dibujaría en el hashchange, fuera del toque, y en iOS
// el teclado no subiría. pushState no dispara hashchange —no hay doble
// render— y enrutar() corre sincrónico hasta el render de la charla, que
// enfoca el campo todavía dentro del gesto. El `asistente: true` del estado
// es lo que le dice a la charla que volver puede ser history.back().
barraInferior.addEventListener("click", (ev) => {
  if (!ev.target.closest("#abrir-asistente")) return;
  history.pushState({ asistente: true }, "", "#/asistente");
  enrutar();
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
  // Sin await: no frena el primer pintado. El botón aparece cuando se sabe,
  // como en la app, y para entonces el módulo ya está cargado y la
  // disponibilidad confirmada, que es lo que necesita el camino rápido.
  cargarAsistente().then((m) => m?.consultarDisponible());
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
