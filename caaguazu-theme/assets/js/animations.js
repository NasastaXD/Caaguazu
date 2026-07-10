/**
 * Caaguazú — animaciones (contraparte de assets/css/animations.css).
 *
 * 1. Scroll-trigger: auto-marca el contenido sembrado por los plugins
 *    (tarjetas de Turismo, directorio de Locales, destacados del Portal)
 *    y lo anima al entrar en viewport — el contenido de plugins no se toca,
 *    todo pasa acá. Sin JS, todo queda visible como siempre.
 * 2. Ripple al clickear botones/tiles/tabbar.
 * 3. Partículas doradas (aserrín) flotando en heros a sangre (hub de
 *    Turismo, splash de entrada) — el hero institucional (V2, `.hero-civic`)
 *    es un panel claro sin sangre completa y queda fuera a propósito.
 * 4. Telón de transición al cruzar entre módulos (Caaguazú ↔ ecosistemas).
 *
 * Todo se apaga con prefers-reduced-motion, y el script entero se desactiva
 * si la página corre dentro de un <iframe> (ver más abajo) — el caso real
 * es el canvas de edición en vivo de un page builder (Elementor, Brizy):
 * cargan la URL real del sitio dentro de un iframe para editarla, y estas
 * animaciones no son solo "de más" ahí, activamente rompen la edición —
 * el telón de transición intercepta cualquier click en un link y tapa la
 * pantalla, las partículas quedan como overlays absolutos encima del
 * contenido (bloqueando clicks de selección), y el scroll-trigger deja
 * contenido en opacity:0 esperando un IntersectionObserver que dentro de
 * un iframe de builder puede no disparar nunca. Nada de esto es exclusivo
 * de un builder puntual: cualquiera que cargue el sitio en un iframe entra
 * en el mismo modo "solo contenido, sin florituras".
 */
(function () {
  'use strict';

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var inIframe = false;
  try { inIframe = window.self !== window.top; } catch (e) { inIframe = true; }
  if (inIframe) { return; }

  /* ------------------------------------------------------------------
   * 1. Auto-tag de contenido de plugins (sin PHP): marca tarjetas/stats
   *    sembradas por los módulos con el MISMO sistema de reveal que usa
   *    el resto del sitio (.reveal/[data-reveal] + .in, ver main.css) —
   *    main.js corre antes (este script depende de él) así que estos
   *    elementos se observan acá con la misma mecánica. Sin JS, nada se
   *    marca y todo queda visible (la opacidad inicial la aplica la
   *    clase html.motion-ready, que también pone el JS).
   * ------------------------------------------------------------------ */
  if ('IntersectionObserver' in window && !reducedMotion) {
    // [selector, variante, threshold] — los selectores apuntan solo a
    // contenido below-the-fold; lo above-the-fold ya tiene .hero-fade/.reveal.
    var autoTargets = [
      ['.entry-content .eco-card',        'up',    0.15],
      ['.entry-content .info-card',       'up',    0.15],
      ['.entry-content .glossary-item',   'up',    0.15],
      ['.entry-content .gallery-grid img','scale', 0.15],
      ['.entry-content .stats-grid .stat','up',    0.5],
      ['.entry-content .cta-row .btn',    'up',    0.5],
      ['.cgz-card',                       'up',    0.15],
      ['.promotur-vitrina__card',         'up',    0.15]
    ];

    var autoTagged = [];
    autoTargets.forEach(function (t) {
      document.querySelectorAll(t[0]).forEach(function (el, i) {
        if (el.hasAttribute('data-reveal') || el.classList.contains('reveal')) { return; }
        el.setAttribute('data-reveal', t[1] === 'up' ? '' : t[1]);
        // Stagger entre hermanos del mismo grupo, con tope para que los
        // últimos de una grilla larga no tarden una eternidad.
        el.style.transitionDelay = (Math.min(i % 6, 4) * 70) + 'ms';
        autoTagged.push([el, t[2]]);
      });
    });

    autoTagged.forEach(function (pair) {
      var el = pair[0];
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) { return; }
          el.classList.add('in');
          observer.unobserve(el);
          // Igual que en main.js: el delay del stagger se limpia al
          // terminar la entrada para no retrasar los hovers del elemento.
          setTimeout(function () { el.style.transitionDelay = ''; }, 1000);
        });
      }, { threshold: pair[1] });
      observer.observe(el);
    });
  }

  /* ------------------------------------------------------------------
   * 2. Ripple al clickear
   * ------------------------------------------------------------------ */
  if (!reducedMotion) {
    var rippleTargets = '.btn, .quiz-opt, .qa-tile, .tabbar-link, .install-banner-btn';
    document.addEventListener('pointerdown', function (e) {
      var host = e.target.closest && e.target.closest(rippleTargets);
      if (!host) { return; }
      host.classList.add('fx-ripple');
      var rect = host.getBoundingClientRect();
      var size = Math.max(rect.width, rect.height);
      var wave = document.createElement('span');
      wave.className = 'fx-ripple-wave';
      wave.style.width = wave.style.height = size + 'px';
      wave.style.left = (e.clientX - rect.left - size / 2) + 'px';
      wave.style.top = (e.clientY - rect.top - size / 2) + 'px';
      host.appendChild(wave);
      setTimeout(function () { wave.remove(); }, 600);
    }, { passive: true });
  }

  /* ------------------------------------------------------------------
   * 3. Partículas doradas (aserrín) en heros a sangre y en el splash de
   *    entrada — cualquier superficie a sangre completa que exista en la
   *    página suma su propia caja de partículas (no es "la primera que
   *    encuentre": el splash y el hub de Turismo pueden convivir en la
   *    misma carga, y ambos deben tener las suyas).
   * ------------------------------------------------------------------ */
  (function () {
    if (reducedMotion) { return; }
    var hosts = [
      document.querySelector('.tourism-hero-full'),
      document.getElementById('cgzSplash')
    ].filter(Boolean);
    if (!hosts.length) { return; }

    hosts.forEach(function (host) {
      var box = document.createElement('div');
      box.className = 'cgz-particles';
      box.setAttribute('aria-hidden', 'true');
      // 9 partículas más tenues (antes 14 y más opacas): el aserrín tiene
      // que percibirse como textura ambiente, no como efecto en sí mismo
      // ("quiet civic motion" — el rework de motion bajó todo lo ambiental).
      for (var i = 0; i < 9; i++) {
        var p = document.createElement('span');
        var size = 2 + Math.random() * 4;
        p.style.width = p.style.height = size.toFixed(1) + 'px';
        p.style.left = (Math.random() * 100).toFixed(1) + '%';
        p.style.setProperty('--p-dur', (9 + Math.random() * 9).toFixed(1) + 's');
        p.style.setProperty('--p-delay', (Math.random() * -18).toFixed(1) + 's');
        p.style.setProperty('--p-op', (0.18 + Math.random() * 0.3).toFixed(2));
        p.style.setProperty('--p-rise', (45 + Math.random() * 45).toFixed(0) + 'vh');
        p.style.setProperty('--p-drift', ((Math.random() - 0.5) * 90).toFixed(0) + 'px');
        box.appendChild(p);
      }
      host.appendChild(box);
    });
  })();

  /* ------------------------------------------------------------------
   * 4. Telón de transición entre módulos (Caaguazú ↔ ecosistemas)
   *
   * caaguazuConfig.ecosystems trae un entry por ecosistema registrado
   * ({slug, label, prefixes}); el eco actual sale de la body class
   * eco-{slug}. Si un link cruza de un eco a otro (o al sitio
   * institucional), se muestra el telón con el label y el color
   * (.to-{slug}, ver animations.css) del destino.
   * ------------------------------------------------------------------ */
  (function () {
    // Al llegar desde el otro módulo: entrada suave del contenido.
    try {
      if (sessionStorage.getItem('cgzModuleEnter')) {
        sessionStorage.removeItem('cgzModuleEnter');
        document.body.classList.add('module-enter');
      }
    } catch (err) {}

    if (reducedMotion) { return; }
    var cfg = window.caaguazuConfig || {};
    var ecos = cfg.ecosystems || [];
    if (!ecos.length) { return; }

    var currentSlug = null;
    ecos.forEach(function (eco) {
      if (document.body.classList.contains('eco-' + eco.slug)) { currentSlug = eco.slug; }
    });

    function ecoForUrl(href) {
      for (var i = 0; i < ecos.length; i++) {
        var match = (ecos[i].prefixes || []).some(function (p) { return href.indexOf(p) === 0; });
        if (match) { return ecos[i]; }
      }
      return null;
    }

    var curtain = document.createElement('div');
    curtain.className = 'module-curtain';
    curtain.setAttribute('aria-hidden', 'true');
    curtain.innerHTML = '<span class="burst"></span><span class="icon"></span><span class="label"></span>';
    document.body.appendChild(curtain);
    var burst = curtain.querySelector('.burst');
    var icon = curtain.querySelector('.icon');
    var label = curtain.querySelector('.label');

    // Ráfaga de partículas radiando desde el centro — se arma de nuevo en
    // cada transición para que la dispersión salga distinta cada vez.
    function spawnBurst() {
      burst.innerHTML = '';
      var count = 12;
      for (var i = 0; i < count; i++) {
        var dot = document.createElement('span');
        var angle = (360 / count) * i + (Math.random() * 18 - 9);
        var dist = 55 + Math.random() * 55;
        dot.style.setProperty('--b-angle', angle.toFixed(1) + 'deg');
        dot.style.setProperty('--b-dist', dist.toFixed(0) + 'px');
        dot.style.setProperty('--b-delay', (Math.random() * 0.1).toFixed(2) + 's');
        burst.appendChild(dot);
      }
    }

    var navigating = false;

    document.addEventListener('click', function (e) {
      if (navigating || e.defaultPrevented || e.button !== 0) { return; }
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
      var a = e.target.closest && e.target.closest('a[href]');
      if (!a || a.target === '_blank' || a.hasAttribute('download')) { return; }
      var href = a.href;
      if (!href || href.indexOf(location.origin) !== 0 || href.indexOf('#') !== -1) { return; }

      var target = ecoForUrl(href);
      var targetSlug = target ? target.slug : null;
      if (targetSlug === currentSlug) { return; } // no cruza frontera de módulo

      e.preventDefault();
      navigating = true;
      label.textContent = target ? target.label : (cfg.i18nHome || 'Caaguazú');
      icon.innerHTML = target ? (target.icon || '') : (cfg.homeIcon || '');
      spawnBurst();
      curtain.classList.add(target ? 'to-' + target.slug : 'to-caaguazu', 'in');
      try { sessionStorage.setItem('cgzModuleEnter', '1'); } catch (err) {}
      setTimeout(function () { location.href = href; }, 520);
    });
  })();
})();
