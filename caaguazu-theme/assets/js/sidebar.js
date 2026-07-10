/**
 * Eco-rail v2 — comportamiento del sidebar derecho (markup en
 * inc/sidebar.php, estilos en main.css). Una capa, dos modos:
 *
 * · Escritorio (≥1280px y ≥620px de alto): expandir/colapsar el panel de
 *   altura completa (persistido en localStorage), Escape y click afuera
 *   colapsan. La animación es 100% CSS (clase .open).
 * · Móvil (<1024px): el rail es EL drawer institucional — se abre con la
 *   hamburguesa del header o el "Menú" del tabbar, con scrim, Escape y
 *   manejo de foco (entra al panel al abrir, vuelve a quien lo abrió al
 *   cerrar). Solo toma esos botones cuando NO existe un #drawer en la
 *   página: dentro de un ecosistema (Turismo/Educación) el drawer del
 *   shell sigue siendo el dueño de la hamburguesa (main.js lo maneja).
 *
 * Además: capa de sonido de interfaz OPT-IN (apagada por default, botón
 * aria-pressed en el pie del rail, recordada en localStorage) — un tick
 * corto y suave por Web Audio solo al abrir/cerrar el rail o el drawer
 * del shell. Nunca en hover, nunca antes de una interacción del usuario,
 * nunca con prefers-reduced-motion. Nada corre dentro de un <iframe>
 * (canvas de un page builder), mismo criterio que animations.js.
 */
(function () {
  'use strict';

  var inIframe = false;
  try { inIframe = window.self !== window.top; } catch (e) { inIframe = true; }
  if (inIframe) { return; }

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- Sonido de interfaz (opt-in, compartido rail + drawer del shell) -- */
  var SOUND_KEY = 'cgzSound';
  var soundOn = false;
  try { soundOn = localStorage.getItem(SOUND_KEY) === '1'; } catch (e) {}
  var audioCtx = null;

  function tick(freq) {
    if (!soundOn || reducedMotion) { return; }
    try {
      audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
      var t = audioCtx.currentTime;
      var osc = audioCtx.createOscillator();
      var gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.value = freq || 620;
      // Envolvente corta y muy baja: "tick de papel", no beep.
      gain.gain.setValueAtTime(0.0001, t);
      gain.gain.exponentialRampToValueAtTime(0.045, t + 0.012);
      gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.085);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(t);
      osc.stop(t + 0.1);
    } catch (e) {}
  }

  /* --- Rail ------------------------------------------------------------ */
  var rail = document.getElementById('ecoRail');
  var toggle = document.getElementById('ecoRailToggle');
  var scrim = document.getElementById('ecoRailBg');

  if (rail && toggle) {
    var RAIL_KEY = 'cgzRail';
    var mqDesktop = window.matchMedia('(min-width: 1280px) and (min-height: 620px)');
    var mqMobile = window.matchMedia('(max-width: 1023.98px)');
    var opener = null;

    var isOpen = function () { return rail.classList.contains('open'); };

    var setOpen = function (open, silent) {
      rail.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (mqMobile.matches) {
        scrim && scrim.classList.toggle('open', open);
        rail.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
          toggle.focus();
        } else if (opener) {
          opener.focus();
          opener = null;
        }
      } else {
        try { localStorage.setItem(RAIL_KEY, open ? '1' : '0'); } catch (e) {}
      }
      if (!silent) { tick(open ? 660 : 520); }
    };

    // Estado inicial por modo: en escritorio se restaura lo persistido;
    // en móvil arranca cerrado y oculto para el lector de pantalla.
    var syncMode = function () {
      if (mqMobile.matches) {
        rail.classList.remove('open');
        scrim && scrim.classList.remove('open');
        rail.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
      } else {
        rail.removeAttribute('aria-hidden');
        scrim && scrim.classList.remove('open');
        var saved = false;
        try { saved = localStorage.getItem(RAIL_KEY) === '1'; } catch (e) {}
        rail.classList.toggle('open', saved && mqDesktop.matches);
        toggle.setAttribute('aria-expanded', rail.classList.contains('open') ? 'true' : 'false');
      }
    };
    syncMode();
    var onMq = function () { syncMode(); };
    if (mqMobile.addEventListener) { mqMobile.addEventListener('change', onMq); }
    else if (mqMobile.addListener) { mqMobile.addListener(onMq); }

    toggle.addEventListener('click', function () { setOpen(!isOpen()); });

    // Aperturas móviles: hamburguesa del header + "Menú" del tabbar — solo
    // si el drawer del shell de ecosistemas no está en la página (ahí la
    // hamburguesa le pertenece a él, ver main.js).
    if (!document.getElementById('drawer')) {
      ['burger', 'tabbarMenu'].forEach(function (id) {
        var btn = document.getElementById(id);
        btn && btn.addEventListener('click', function (e) {
          opener = e.currentTarget;
          setOpen(true);
        });
      });
    }

    scrim && scrim.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen()) {
        setOpen(false);
        if (!mqMobile.matches) { toggle.focus(); }
      }
    });
    document.addEventListener('click', function (e) {
      // Click afuera colapsa — solo en escritorio (en móvil eso ya lo hace
      // el scrim, y los botones que ABREN el panel están afuera de él).
      if (!mqMobile.matches && isOpen() && !rail.contains(e.target)) {
        setOpen(false);
      }
    });

    var soundBtn = document.getElementById('ecoRailSound');
    if (soundBtn) {
      var syncSound = function () {
        soundBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
      };
      syncSound();
      soundBtn.addEventListener('click', function () {
        soundOn = !soundOn;
        try { localStorage.setItem(SOUND_KEY, soundOn ? '1' : '0'); } catch (e) {}
        syncSound();
        if (soundOn) { tick(660); } // feedback inmediato de que quedó prendido
      });
    }
  }

  /* --- Animación de íconos + tick al click ------------------------------
   * Estilo de las referencias Lottie (home/search-to-x) en CSS puro: al
   * tocar un ítem con ícono se re-dibuja el trazo con un pop corto (clase
   * .ico-play, ver main.css; el pathLength="1" viene de inc/icons.php).
   * El tick de click cae en la misma capa de sonido opt-in de arriba —
   * suena solo si el usuario prendió "Sonido de interfaz". */
  var ICO_TARGETS = '.tabbar-link, .qa-tile, .eco-rail-item, .eco-rail-toggle, .icon-btn';
  var SOUND_TARGETS = ICO_TARGETS + ', .btn, .nav-link, .chip, .share-btn, .quiz-opt';
  var lastTick = 0;

  document.addEventListener('pointerdown', function (e) {
    if (!e.target.closest) { return; }

    var icoHost = e.target.closest(ICO_TARGETS);
    if (icoHost && !reducedMotion) {
      icoHost.classList.remove('ico-play');
      void icoHost.offsetWidth; // reinicia la animación si se toca seguido
      icoHost.classList.add('ico-play');
      setTimeout(function () { icoHost.classList.remove('ico-play'); }, 650);
    }

    // Throttle corto: un solo tick aunque el evento burbujee raro o el
    // usuario toque como metralleta.
    if (e.target.closest(SOUND_TARGETS)) {
      var now = Date.now();
      if (now - lastTick > 120) {
        lastTick = now;
        tick(580);
      }
    }
  }, { passive: true });

  // El ícono activo del tabbar se dibuja solo al llegar a la página — el
  // equivalente al "loop de entrada" de las referencias, una sola vez.
  if (!reducedMotion) {
    var activeTab = document.querySelector('.tabbar-link.active');
    if (activeTab) {
      activeTab.classList.add('ico-play');
      setTimeout(function () { activeTab.classList.remove('ico-play'); }, 700);
    }
  }

  /* --- Tick al abrir/cerrar el drawer del shell (si el sonido está on) -- */
  var drawer = document.getElementById('drawer');
  if (drawer && 'MutationObserver' in window) {
    var wasOpen = drawer.classList.contains('open');
    new MutationObserver(function () {
      var nowOpen = drawer.classList.contains('open');
      if (nowOpen !== wasOpen) {
        wasOpen = nowOpen;
        tick(nowOpen ? 660 : 520);
      }
    }).observe(drawer, { attributes: true, attributeFilter: ['class'] });
  }
})();
