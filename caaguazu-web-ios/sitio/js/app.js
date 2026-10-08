// Router mínimo por hash, sin build ni framework.

import { cargarAjustes } from "./config.js";
import { aplicarTema } from "./tema.js";
import { cargarTextos, aplicarDisponibles, idiomaActual, t } from "./idioma.js";
import { Api } from "./api.js";
import { escapar, Icono, cerrarHojaAbierta } from "./piezas.js";
import { alSuscribirEstado, esFavorito, alternarFavorito } from "./estado.js";

import * as Inicio from "./pantallas/inicio.js";
import * as Buscar from "./pantallas/buscar.js";
import * as Ficha from "./pantallas/ficha.js";
import * as Articulos from "./pantallas/articulos.js";
import * as Articulo from "./pantallas/articulo.js";
import * as Mapa from "./pantallas/mapa.js";
import * as Asistente from "./pantallas/asistente.js";
import * as App from "./pantallas/app.js";
import * as Ajustes from "./pantallas/ajustes.js";
import * as Guardados from "./pantallas/guardados.js";

const contenedor = document.getElementById("contenido");
const barra = document.getElementById("barra");

/** Se sabe al arrancar (GET /asistente). Hasta entonces, sin pestaña. */
let hayAsistente = false;

const RUTAS = [
  { patron: /^inicio$/, pantalla: Inicio, seccion: "inicio" },
  { patron: /^buscar$/, pantalla: Buscar, seccion: "buscar" },
  { patron: /^ficha\/(\d+)$/, pantalla: Ficha, seccion: null },
  { patron: /^articulos$/, pantalla: Articulos, seccion: "articulos" },
  { patron: /^articulo\/(\d+)$/, pantalla: Articulo, seccion: null },
  { patron: /^mapa$/, pantalla: Mapa, seccion: null, sinBarra: true },
  { patron: /^asistente$/, pantalla: Asistente, seccion: "asistente" },
  { patron: /^app$/, pantalla: App, seccion: "app" },
  { patron: /^ajustes$/, pantalla: Ajustes, seccion: null },
  { patron: /^guardados$/, pantalla: Guardados, seccion: null },
  // Direcciones de 1.x que pueden estar en un enlace compartido o en un
  // ícono de la pantalla de inicio. Los recorridos están en la app; el
  // perfil pasó a llamarse Ajustes.
  { patron: /^recorridos$/, redirigir: "app" },
  { patron: /^recorrido\/\d+$/, redirigir: "app" },
  { patron: /^perfil$/, redirigir: "ajustes" },
];

function pintarBarra(activa) {
  const items = [
    ["inicio", Icono.inicio, t("nav.principal")],
    ["buscar", Icono.buscar, t("barra.buscar")],
    ["articulos", Icono.articulo, t("web.articulos")],
    hayAsistente ? ["asistente", Icono.asistente, t("web.asistente")] : null,
    ["app", Icono.ruta, t("nav.recorridos")],
  ].filter(Boolean);

  barra.innerHTML = items
    .map(([clave, icono, etiqueta]) => {
      const activo = clave === activa;
      return `<a class="barra__item ${activo ? "activo" : ""}" href="#/${clave}" aria-label="${escapar(etiqueta)}"${
        activo ? ' aria-current="page"' : ""
      }>${icono}<span>${escapar(etiqueta)}</span></a>`;
    })
    .join("");
}

// Cada navegación tiene su número. Una pantalla lenta que termina de cargar
// cuando la persona ya se fue a otra no pinta encima: pregunta `vigente()`
// antes de tocar el DOM.
let turno = 0;
let primera = true;
/** Cuántas veces se navegó adentro de la web desde que se abrió. */
let internas = 0;

async function enrutar() {
  const hash = (location.hash || "#/inicio").replace(/^#\/?/, "");
  const [ruta, query] = hash.split("?");
  const params = new URLSearchParams(query || "");

  const encontrada = RUTAS.find((r) => r.patron.test(ruta));
  if (!encontrada) {
    location.replace("#/inicio");
    return;
  }
  if (encontrada.redirigir) {
    location.replace("#/" + encontrada.redirigir);
    return;
  }

  const mio = ++turno;
  const vigente = () => mio === turno;
  const match = ruta.match(encontrada.patron);
  const id = match[1] ? Number(match[1]) : null;

  cerrarHojaAbierta();
  document.body.className = "";
  barra.hidden = Boolean(encontrada.sinBarra);
  pintarBarra(encontrada.seccion);
  window.scrollTo(0, 0);

  // Una pantalla nueva entra con su animación: se reemplaza el nodo, así la
  // animación de CSS vuelve a correr.
  const pantalla = document.createElement("div");
  pantalla.className = "pantalla";
  contenedor.replaceChildren(pantalla);

  await encontrada.pantalla.render(pantalla, { params, id, vigente, hayAsistente });

  if (!vigente()) return;
  const h1 = pantalla.querySelector("h1");
  document.title = h1 && encontrada.seccion !== "inicio" ? `${h1.textContent.trim()} · Caaguazú Turismo` : "Caaguazú Turismo";
  // Al navegar —no al abrir— el lector de pantalla va al contenido nuevo.
  if (!primera) {
    contenedor.focus({ preventScroll: true });
    internas++;
  }
  primera = false;
}

/* --- Delegados: una sola escucha para todas las pantallas -------------- */

document.addEventListener("click", (ev) => {
  const fav = ev.target.closest("[data-favorito]");
  if (fav) {
    // Los corazones viven dentro de enlaces: sin esto, guardar un lugar
    // también lo abre.
    ev.preventDefault();
    ev.stopPropagation();
    alternarFavorito(Number(fav.dataset.favorito));
    return;
  }
  if (ev.target.closest("[data-volver]")) {
    ev.preventDefault();
    // Si se entró directo por un enlace compartido no hay «atrás» adentro de
    // la web: se va al inicio en vez de sacar a la persona del sitio.
    if (internas > 0) history.back();
    else location.hash = "#/inicio";
    return;
  }
  if (ev.target.closest("[data-reintentar]")) {
    ev.preventDefault();
    enrutar();
  }
});

alSuscribirEstado((idCambiado) => {
  document.querySelectorAll(`[data-favorito="${idCambiado}"]`).forEach((boton) => {
    const activo = esFavorito(idCambiado);
    boton.classList.toggle("activo", activo);
    boton.setAttribute("aria-pressed", String(activo));
    boton.innerHTML = activo ? Icono.corazon : Icono.corazonBorde;
    if (activo) {
      boton.classList.remove("late");
      void boton.offsetWidth; // reinicia la animación si se toca dos veces
      boton.classList.add("late");
    }
  });
});

/* --- Arranque ----------------------------------------------------------- */

async function iniciar() {
  await cargarAjustes();
  aplicarTema();
  await cargarTextos();
  document.documentElement.lang = idiomaActual();

  // Idiomas y asistente se preguntan en paralelo y sin frenar el primer
  // dibujado: si la red tarda, la web abre igual y la pestaña del asistente
  // aparece cuando llega la respuesta.
  Api.idiomas()
    .then((r) => {
      if (Array.isArray(r?.idiomas)) aplicarDisponibles(r.idiomas.map((i) => ({ codigo: i.codigo, nombre: i.nombre })));
    })
    .catch(() => {});

  Api.asistenteDisponible().then((si) => {
    if (si === hayAsistente) return;
    hayAsistente = si;
    const actual = RUTAS.find((r) => r.patron.test((location.hash || "#/inicio").replace(/^#\/?/, "").split("?")[0]));
    pintarBarra(actual?.seccion ?? null);
    // Inicio muestra la tarjeta del asistente: se vuelve a dibujar si es lo
    // que está abierto.
    if (actual?.seccion === "inicio" || actual?.seccion === "asistente") enrutar();
  });

  window.addEventListener("hashchange", enrutar);
  await enrutar();
}

iniciar();
