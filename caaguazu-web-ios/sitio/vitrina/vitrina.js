// La vitrina: cambia de vista y escala los aparatos para que entren enteros.
// JavaScript plano, sin imports ni build, como la presentación.

(function () {
  "use strict";

  var VISTAS = ["app", "panel", "asistente"];
  var $ = function (id) { return document.getElementById(id); };

  /** Escala un aparato (de tamaño fijo) para que entre en su vista. */
  function encajar(aparato, vista) {
    if (!aparato || vista.hidden) return;
    var hueco = vista.getBoundingClientRect();
    var otros = 0;
    // Lo que comparte la fila (el QR) se descuenta del ancho.
    Array.prototype.forEach.call(vista.children, function (h) {
      if (h !== aparato && h.offsetWidth) otros += h.offsetWidth + 56;
    });
    var escala = Math.min(1, hueco.height / aparato.offsetHeight, (hueco.width - otros) / aparato.offsetWidth);
    aparato.style.transform = "scale(" + escala + ")";
    // Escalado con transform el aparato sigue ocupando su tamaño original en
    // la fila: el margen negativo devuelve lo que sobra.
    var sobraX = (aparato.offsetWidth * (1 - escala)) / 2;
    var sobraY = (aparato.offsetHeight * (1 - escala)) / 2;
    aparato.style.margin = -sobraY + "px " + -sobraX + "px";
  }

  function encajarTodo() {
    encajar($("telefono"), $("vista-app"));
    encajar($("ventana"), $("vista-panel"));
  }

  /** Los marcos se cargan la primera vez que se muestran, no antes. */
  function cargar(vista) {
    var marco = vista.querySelector("iframe[data-src]");
    if (marco && !marco.getAttribute("src")) marco.setAttribute("src", marco.getAttribute("data-src"));
  }

  function mostrar(nombre) {
    if (VISTAS.indexOf(nombre) === -1) nombre = VISTAS[0];
    document.querySelectorAll(".vista").forEach(function (v) {
      v.hidden = v.getAttribute("data-vista") !== nombre;
      if (!v.hidden) cargar(v);
    });
    document.querySelectorAll(".pestana").forEach(function (p) {
      p.setAttribute("aria-selected", String(p.getAttribute("data-vista") === nombre));
    });
    if (location.hash !== "#" + nombre) history.replaceState(null, "", "#" + nombre);
    encajarTodo();
  }

  function actual() {
    return (location.hash || "").replace("#", "") || VISTAS[0];
  }

  document.querySelectorAll(".pestana").forEach(function (p) {
    p.addEventListener("click", function (e) {
      e.preventDefault();
      mostrar(p.getAttribute("data-vista"));
    });
  });

  // 1/2/3 o las flechas (un control de diapositivas manda flechas o RePág).
  document.addEventListener("keydown", function (e) {
    var i = VISTAS.indexOf(actual());
    if (e.key >= "1" && e.key <= "3") mostrar(VISTAS[Number(e.key) - 1]);
    else if (e.key === "ArrowRight" || e.key === "PageDown") mostrar(VISTAS[Math.min(VISTAS.length - 1, i + 1)]);
    else if (e.key === "ArrowLeft" || e.key === "PageUp") mostrar(VISTAS[Math.max(0, i - 1)]);
  });

  window.addEventListener("hashchange", function () { mostrar(actual()); });
  window.addEventListener("resize", encajarTodo);

  // El QR lleva a la web de turismo: la misma app, en el teléfono de cada uno.
  window.addEventListener("load", function () {
    if (!window.qrcode) return;
    var q = window.qrcode(0, "M");
    q.addData(new URL("../", location.href).href);
    q.make();
    $("qr").innerHTML = q.createSvgTag(6, 24);
    encajarTodo();
  });

  mostrar(actual());
})();
