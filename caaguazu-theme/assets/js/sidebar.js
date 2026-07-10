/**
 * Eco-rail — comportamiento del sidebar derecho colapsable (markup en
 * inc/sidebar.php, estilos en main.css). Tres responsabilidades:
 *
 * 1. Expandir/colapsar el rail (persistido en localStorage), con Escape y
 *    click afuera para cerrar. La animación es 100% CSS (clase .open).
 * 2. Capa de sonido de interfaz OPT-IN: apagada por default, se prende con
 *    el botón del pie del rail (aria-pressed) y queda recordada. Un tick
 *    corto y suave (Web Audio, sin assets) solo en interacciones con
 *    significado: abrir/cerrar el rail o el drawer móvil y prender el
 *    propio toggle. Nunca en hover, nunca antes de una interacción del
 *    usuario (el AudioContext recién se crea al primer click con el sonido
 *    ya prendido), y nunca con prefers-reduced-motion.
 * 3. Nada de esto corre dentro de un <iframe> (canvas de un page builder),
 *    mismo criterio que animations.js.
 */
(function () {
  'use strict';

  var inIframe = false;
  try { inIframe = window.self !== window.top; } catch (e) { inIframe = true; }
  if (inIframe) { return; }

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- Sonido de interfaz (opt-in, compartido rail + drawer) ---------- */
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

  /* --- Rail ----------------------------------------------------------- */
  var rail = document.getElementById('ecoRail');
  var toggle = document.getElementById('ecoRailToggle');

  if (rail && toggle) {
    var RAIL_KEY = 'cgzRail';

    var setOpen = function (open, silent) {
      rail.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      try { localStorage.setItem(RAIL_KEY, open ? '1' : '0'); } catch (e) {}
      if (!silent) { tick(open ? 660 : 520); }
    };

    try {
      if (localStorage.getItem(RAIL_KEY) === '1') { setOpen(true, true); }
    } catch (e) {}

    toggle.addEventListener('click', function () {
      setOpen(!rail.classList.contains('open'));
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && rail.classList.contains('open')) {
        setOpen(false);
        toggle.focus();
      }
    });
    document.addEventListener('click', function (e) {
      if (rail.classList.contains('open') && !rail.contains(e.target)) {
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

  /* --- Tick al abrir/cerrar el drawer móvil (si el sonido está prendido) */
  var drawer = document.getElementById('drawer');
  if (drawer && 'MutationObserver' in window) {
    var wasOpen = drawer.classList.contains('open');
    new MutationObserver(function () {
      var isOpen = drawer.classList.contains('open');
      if (isOpen !== wasOpen) {
        wasOpen = isOpen;
        tick(isOpen ? 660 : 520);
      }
    }).observe(drawer, { attributes: true, attributeFilter: ['class'] });
  }
})();
