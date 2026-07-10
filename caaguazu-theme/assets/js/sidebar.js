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
 * Además: capa de sonido de interfaz — encendida por default (pedido de
 * usuario), con botón opt-OUT (aria-pressed) en el pie del rail, recordado
 * en localStorage. Tres timbres distintos por Web Audio, todos cortos y
 * suaves ("quiet civic motion" también en audio, nunca un beep de UI
 * genérico): `tick()` para clicks/toggles, `chime()` para el cierre del
 * splash de entrada (un acorde breve, no un tick más) y `whoosh()` para el
 * telón de transición entre ecosistemas (un barrido tonal, sensación de
 * "cruzar una puerta"). Nunca en hover, nunca con prefers-reduced-motion.
 * El primer sonido de la página SIEMPRE requiere que el navegador ya haya
 * visto una interacción del usuario (autoplay policy de Web Audio) — si el
 * splash termina antes de cualquier click/tap, ese primer chime puede no
 * sonar; es una limitación del navegador, no un bug, y no bloquea nada
 * visual. Nada corre dentro de un <iframe> (canvas de un page builder),
 * mismo criterio que animations.js.
 */
(function () {
  'use strict';

  var inIframe = false;
  try { inIframe = window.self !== window.top; } catch (e) { inIframe = true; }
  if (inIframe) { return; }

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- Sonido de interfaz (ON por default, opt-out; compartido con el
     drawer del shell, el telón de transición y el splash) --- */
  var SOUND_KEY = 'cgzSound';
  var soundOn = true;
  try { soundOn = localStorage.getItem(SOUND_KEY) !== '0'; } catch (e) {}
  var audioCtx = null;

  function ctx() {
    audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
    // El navegador puede arrancar el contexto "suspended" hasta la primera
    // interacción real (autoplay policy) — pedir resume() no hace daño si
    // ya está corriendo, y deja el audio listo apenas haya un gesto.
    if (audioCtx.state === 'suspended') { audioCtx.resume().catch(function () {}); }
    return audioCtx;
  }

  function tone(c, t, freq, type, peak, dur) {
    var osc = c.createOscillator();
    var gain = c.createGain();
    osc.type = type || 'sine';
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.0001, t);
    gain.gain.exponentialRampToValueAtTime(peak, t + dur * 0.18);
    gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
    osc.connect(gain);
    gain.connect(c.destination);
    osc.start(t);
    osc.stop(t + dur + 0.02);
    return osc;
  }

  function tick(freq) {
    if (!soundOn || reducedMotion) { return; }
    try {
      var c = ctx(), t = c.currentTime;
      // Envolvente corta y muy baja: "tick de papel", no beep.
      tone(c, t, freq || 620, 'sine', 0.045, 0.085);
    } catch (e) {}
  }

  /* Chime de cierre del splash: acorde breve de dos notas (5ª justa,
     mismo intervalo "cívico/institucional" que un carillón de plaza), no
     un tick más — marca el único momento de la sesión que lo amerita. */
  function chime() {
    if (!soundOn || reducedMotion) { return; }
    try {
      var c = ctx(), t = c.currentTime;
      tone(c, t, 523.25, 'sine', 0.07, 0.5);        // Do5
      tone(c, t + 0.09, 784.0, 'sine', 0.055, 0.55); // Sol5, entra un pelo después
    } catch (e) {}
  }

  /* Whoosh del telón de transición: barrido tonal corto (glissando +
     lowpass que se abre), sensación de "cruzar una puerta" — distinto del
     tick de UI y del chime del splash, pensado para acompañar un slide
     lateral en vez de un click puntual. */
  function whoosh() {
    if (!soundOn || reducedMotion) { return; }
    try {
      var c = ctx(), t = c.currentTime, dur = 0.42;
      var osc = c.createOscillator();
      var filter = c.createBiquadFilter();
      var gain = c.createGain();
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(260, t);
      osc.frequency.exponentialRampToValueAtTime(120, t + dur);
      filter.type = 'lowpass';
      filter.frequency.setValueAtTime(300, t);
      filter.frequency.exponentialRampToValueAtTime(2200, t + dur * 0.5);
      filter.frequency.exponentialRampToValueAtTime(200, t + dur);
      gain.gain.setValueAtTime(0.0001, t);
      gain.gain.exponentialRampToValueAtTime(0.05, t + dur * 0.35);
      gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
      osc.connect(filter);
      filter.connect(gain);
      gain.connect(c.destination);
      osc.start(t);
      osc.stop(t + dur + 0.02);
    } catch (e) {}
  }

  // Expuesto para header.php (chime al cerrar el splash, script inline
  // que corre antes de que este archivo termine de cargar) y
  // animations.js (whoosh del telón) — ambos lo llaman de forma
  // defensiva (`window.caaguazuSound && ...`) por si este script todavía
  // no corrió cuando se dispara el evento.
  window.caaguazuSound = { tick: tick, chime: chime, whoosh: whoosh, isOn: function () { return soundOn; } };

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
