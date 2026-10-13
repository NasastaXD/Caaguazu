// La vitrina: cambia de vista, escala los aparatos para que entren enteros y
// tiene un modo proyección sin nada más que los aparatos.
// JavaScript plano, sin imports ni build, como la presentación.

(function () {
  "use strict";

  var VISTAS = ["app", "panel"];
  var $ = function (id) { return document.getElementById(id); };
  var raiz = document.documentElement;

  /** Escala el grupo de aparatos (de tamaño fijo) para que entre en su vista. */
  function encajar(grupo) {
    var vista = grupo.closest(".vista");
    if (vista.hidden) return;
    grupo.style.transform = "none";
    grupo.style.margin = "0";
    var hueco = vista.getBoundingClientRect();
    var escala = Math.min(1, hueco.height / grupo.offsetHeight, hueco.width / grupo.offsetWidth);
    grupo.style.transform = "scale(" + escala + ")";
    // Escalado con transform, el grupo sigue ocupando su tamaño original: el
    // margen negativo devuelve lo que sobra para que quede centrado.
    var sobraX = (grupo.offsetWidth * (1 - escala)) / 2;
    var sobraY = (grupo.offsetHeight * (1 - escala)) / 2;
    grupo.style.margin = -sobraY + "px " + -sobraX + "px";
  }

  function encajarTodo() {
    document.querySelectorAll(".grupo").forEach(encajar);
  }

  /** Los marcos se cargan la primera vez que se muestran, no antes. */
  function cargar(vista) {
    vista.querySelectorAll("iframe[data-src]").forEach(function (marco) {
      if (!marco.getAttribute("src")) marco.setAttribute("src", marco.getAttribute("data-src"));
    });
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
    if (location.hash !== "#" + nombre) history.replaceState(null, "", location.search + "#" + nombre);
    encajarTodo();
  }

  function actual() {
    return (location.hash || "").replace("#", "") || VISTAS[0];
  }

  /* --- Modo proyección ---------------------------------------------------- */

  var relojQuieto = 0;
  function proyectar(si) {
    raiz.classList.toggle("proyectando", si);
    if (si && document.documentElement.requestFullscreen && !document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(function () {});
    }
    encajarTodo();
  }
  // Proyectando, el cursor se esconde si no se mueve.
  function movio() {
    raiz.classList.remove("quieto");
    clearTimeout(relojQuieto);
    relojQuieto = setTimeout(function () { raiz.classList.add("quieto"); }, 2000);
  }

  /* --- Teclado: 1/2, flechas (control de diapositivas) y P ---------------- */

  function tecla(e) {
    var i = VISTAS.indexOf(actual());
    if (e.key >= "1" && e.key <= String(VISTAS.length)) mostrar(VISTAS[Number(e.key) - 1]);
    else if (e.key === "ArrowRight" || e.key === "PageDown") mostrar(VISTAS[Math.min(VISTAS.length - 1, i + 1)]);
    else if (e.key === "ArrowLeft" || e.key === "PageUp") mostrar(VISTAS[Math.max(0, i - 1)]);
    else if (e.key === "p" || e.key === "P") proyectar(!raiz.classList.contains("proyectando"));
  }
  document.addEventListener("keydown", tecla);

  // Con el foco adentro de un aparato, las teclas igual tienen que llegar: los
  // marcos son del mismo sitio, así que se escuchan desde acá.
  document.querySelectorAll("iframe").forEach(function (marco) {
    marco.addEventListener("load", function () {
      try {
        marco.contentWindow.addEventListener("keydown", function (e) {
          if (/^[12pP]$|^Arrow(Left|Right)$|^Page(Up|Down)$/.test(e.key)) tecla(e);
        });
        marco.contentWindow.addEventListener("mousemove", movio);
      } catch (_) {
        /* otro origen: sin teclas desde adentro */
      }
    });
  });

  document.querySelectorAll(".pestana").forEach(function (p) {
    p.addEventListener("click", function (e) {
      e.preventDefault();
      mostrar(p.getAttribute("data-vista"));
    });
  });

  document.addEventListener("mousemove", movio);
  window.addEventListener("hashchange", function () { mostrar(actual()); });
  window.addEventListener("resize", encajarTodo);
  document.addEventListener("fullscreenchange", encajarTodo);

  // El QR lleva a la web de turismo: la misma app, en el teléfono de cada uno.
  window.addEventListener("load", function () {
    if (!window.qrcode) return;
    var q = window.qrcode(0, "M");
    q.addData(new URL("../", location.href).href);
    q.make();
    $("qr").innerHTML = q.createSvgTag(6, 24);
    encajarTodo();
  });

  if (/[?&]proyectar=1\b/.test(location.search)) proyectar(true);
  mostrar(actual());
})();
