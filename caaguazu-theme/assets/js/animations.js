/**
 * Caaguazú — animaciones (contraparte de assets/css/animations.css).
 *
 * 1. Scroll-trigger: auto-marca el contenido sembrado por los plugins
 *    (tarjetas de Turismo, directorio de Locales, destacados del Portal)
 *    y lo anima al entrar en viewport — el contenido de plugins no se toca,
 *    todo pasa acá. Sin JS, todo queda visible como siempre.
 * 2. Ripple al clickear botones/tiles/tabbar.
 * 3. Tilt 3D del hero de la home (mouse, solo desktop con puntero fino).
 * 4. Partículas doradas (aserrín) flotando en los heros.
 * 5. Telón de transición al cruzar entre módulos (Caaguazú ↔ Turismo).
 *
 * Todo se apaga con prefers-reduced-motion.
 */
(function () {
  'use strict';

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------------
   * 1. Scroll-trigger sobre contenido de plugins (auto-tag, sin PHP)
   * ------------------------------------------------------------------ */
  if ('IntersectionObserver' in window) {
    // [selector, animación, threshold] — los selectores apuntan solo a
    // contenido below-the-fold; lo above-the-fold ya tiene .hero-fade/.reveal.
    var autoTargets = [
      ['.entry-content .eco-card',        'fadeInUp', 0.15],
      ['.entry-content .info-card',       'fadeInUp', 0.15],
      ['.entry-content .glossary-item',   'fadeInLeft', 0.15],
      ['.entry-content .gallery-grid img','zoomIn',   0.15],
      ['.entry-content .stats-grid .stat','fadeInUp', 0.5],
      ['.entry-content .cta-row .btn',    'fadeInUp', 0.5],
      ['.cgz-card',                       'fadeInUp', 0.15],
      ['.promotur-vitrina__card',         'fadeInUp', 0.15]
    ];

    autoTargets.forEach(function (t) {
      var els = document.querySelectorAll(t[0]);
      els.forEach(function (el, i) {
        if (el.dataset.animate) { return; } // no re-marcar
        el.dataset.animate = t[1];
        el.dataset.threshold = t[2];
        // Stagger entre hermanos del mismo grupo, con tope para que los
        // últimos de una grilla larga no tarden una eternidad.
        el.dataset.delay = Math.min(i % 6, 4) * 110;
        el.classList.add('scroll-animate');
        if (!reducedMotion) { el.style.opacity = 0; }
      });
    });

    document.querySelectorAll('.scroll-animate').forEach(function (el) {
      var threshold = parseFloat(el.dataset.threshold || 0.15);
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) { return; }
          if (reducedMotion) {
            el.style.opacity = 1;
          } else {
            setTimeout(function () {
              el.style.opacity = '';
              el.classList.add('animate__animated', 'animate__' + el.dataset.animate, 'animate__fast');
            }, parseInt(el.dataset.delay || 0, 10));
          }
          observer.unobserve(el);
        });
      }, { threshold: threshold });
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
   * 3. Tilt 3D del hero de la home
   * ------------------------------------------------------------------ */
  (function () {
    if (reducedMotion) { return; }
    if (!document.body.classList.contains('page-home')) { return; }
    if (!window.matchMedia('(pointer: fine) and (min-width: 1024px)').matches) { return; }
    var hero = document.querySelector('.hero');
    var inner = document.querySelector('.hero-inner');
    if (!hero || !inner) { return; }

    var raf = null;
    hero.addEventListener('mousemove', function (e) {
      if (raf) { return; }
      raf = requestAnimationFrame(function () {
        raf = null;
        var r = hero.getBoundingClientRect();
        var nx = (e.clientX - r.left) / r.width - 0.5;  // -0.5 .. 0.5
        var ny = (e.clientY - r.top) / r.height - 0.5;
        inner.style.setProperty('--tilt-y', (nx * 7).toFixed(2) + 'deg');
        inner.style.setProperty('--tilt-x', (ny * -5).toFixed(2) + 'deg');
      });
    }, { passive: true });
    hero.addEventListener('mouseleave', function () {
      inner.style.setProperty('--tilt-x', '0deg');
      inner.style.setProperty('--tilt-y', '0deg');
    });
  })();

  /* ------------------------------------------------------------------
   * 4. Partículas doradas (aserrín) en los heros
   * ------------------------------------------------------------------ */
  (function () {
    if (reducedMotion) { return; }
    var host = document.querySelector('body.page-home .hero') ||
               document.querySelector('.tourism-hero-full');
    if (!host) { return; }
    var box = document.createElement('div');
    box.className = 'cgz-particles';
    box.setAttribute('aria-hidden', 'true');
    for (var i = 0; i < 14; i++) {
      var p = document.createElement('span');
      var size = 2 + Math.random() * 4;
      p.style.width = p.style.height = size.toFixed(1) + 'px';
      p.style.left = (Math.random() * 100).toFixed(1) + '%';
      p.style.setProperty('--p-dur', (9 + Math.random() * 9).toFixed(1) + 's');
      p.style.setProperty('--p-delay', (Math.random() * -18).toFixed(1) + 's');
      p.style.setProperty('--p-op', (0.25 + Math.random() * 0.4).toFixed(2));
      p.style.setProperty('--p-rise', (45 + Math.random() * 45).toFixed(0) + 'vh');
      p.style.setProperty('--p-drift', ((Math.random() - 0.5) * 90).toFixed(0) + 'px');
      box.appendChild(p);
    }
    host.appendChild(box);
  })();

  /* ------------------------------------------------------------------
   * 5. Telón de transición entre módulos (Caaguazú ↔ Turismo)
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
    var prefixes = cfg.tourismPrefixes || [];
    if (!prefixes.length) { return; }

    var curtain = document.createElement('div');
    curtain.className = 'module-curtain';
    curtain.setAttribute('aria-hidden', 'true');
    var label = document.createElement('span');
    label.className = 'label';
    curtain.appendChild(label);
    document.body.appendChild(curtain);

    var inTourism = document.body.classList.contains('tourism-page');
    var navigating = false;

    document.addEventListener('click', function (e) {
      if (navigating || e.defaultPrevented || e.button !== 0) { return; }
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
      var a = e.target.closest && e.target.closest('a[href]');
      if (!a || a.target === '_blank' || a.hasAttribute('download')) { return; }
      var href = a.href;
      if (!href || href.indexOf(location.origin) !== 0 || href.indexOf('#') !== -1) { return; }

      var toTourism = prefixes.some(function (p) { return href.indexOf(p) === 0; });
      if (toTourism === inTourism) { return; } // no cruza la frontera de módulo

      e.preventDefault();
      navigating = true;
      label.textContent = toTourism ? (cfg.i18nTourism || 'Turismo') : (cfg.i18nHome || 'Caaguazú');
      curtain.classList.add(toTourism ? 'to-turismo' : 'to-caaguazu', 'in');
      try { sessionStorage.setItem('cgzModuleEnter', '1'); } catch (err) {}
      setTimeout(function () { location.href = href; }, 430);
    });
  })();
})();
