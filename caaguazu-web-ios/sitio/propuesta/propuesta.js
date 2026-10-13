// La demo del proyecto: tres pestañas (guía, asistente, panel). En pantalla
// ancha se ve dentro del teléfono dibujado; en un celular se abre entera.
// JavaScript plano, sin imports ni build.

(function () {
  "use strict";

  var DEMOS = {
    guia: { url: "../", titulo: "Guía" },
    asistente: { url: "../#/asistente", titulo: "Asistente" },
    panel: { url: "../vitrina/panel/home.html", titulo: "Panel" },
  };
  var $ = function (id) { return document.getElementById(id); };
  var actual = "guia";
  var ancho = window.matchMedia("(min-width: 900px)");

  function elegir(nombre) {
    if (!DEMOS[nombre]) return;
    actual = nombre;
    document.querySelectorAll(".pestana").forEach(function (p) {
      p.setAttribute("aria-selected", String(p.getAttribute("data-demo") === nombre));
    });
    document.querySelectorAll(".explicacion").forEach(function (e) {
      e.hidden = e.getAttribute("data-para") !== nombre;
    });
    if (ancho.matches) cargar($("marco-demo"), DEMOS[nombre].url);
  }

  /** Cambiar la dirección de un marco, sin recargar si ya está ahí. */
  function cargar(marco, url) {
    if (marco.getAttribute("src") !== url) marco.setAttribute("src", url);
  }

  function abrirCapa() {
    $("capa-titulo").textContent = DEMOS[actual].titulo;
    cargar($("marco-capa"), DEMOS[actual].url);
    $("capa").hidden = false;
    document.body.style.overflow = "hidden";
    // «Atrás» del teléfono cierra la demo en vez de salir de la página.
    history.pushState({ capa: true }, "");
  }

  function cerrarCapa() {
    $("capa").hidden = true;
    document.body.style.overflow = "";
  }

  document.querySelectorAll(".pestana").forEach(function (p) {
    p.addEventListener("click", function () { elegir(p.getAttribute("data-demo")); });
  });
  $("abrir-demo").addEventListener("click", abrirCapa);
  $("cerrar-capa").addEventListener("click", function () {
    if (history.state && history.state.capa) history.back();
    else cerrarCapa();
  });
  window.addEventListener("popstate", function () {
    if (!$("capa").hidden) cerrarCapa();
  });

  // El teléfono dibujado carga su demo recién cuando se acerca a la vista.
  if (ancho.matches) {
    var visto = false;
    var cargarSiHaceFalta = function () {
      if (!visto) { visto = true; elegir(actual); }
    };
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (entradas, obs) {
        if (entradas.some(function (e) { return e.isIntersecting; })) { cargarSiHaceFalta(); obs.disconnect(); }
      }, { rootMargin: "200px" }).observe($("marco-demo"));
    } else {
      cargarSiHaceFalta();
    }
  }
  ancho.addEventListener("change", function () { if (ancho.matches) elegir(actual); });
})();
