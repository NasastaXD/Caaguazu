/* Caaguazú — front-end JS */
(function(){
  // Lightbox de galería: cualquier .gallery-grid img abre un <dialog> nativo
  // con navegación por teclado y foco gestionado. Sin librerías externas.
  var images = Array.prototype.slice.call(document.querySelectorAll('.gallery-grid img'));
  if (!images.length || typeof HTMLDialogElement === 'undefined') return;

  var dialog = document.createElement('dialog');
  dialog.className = 'lightbox';
  dialog.innerHTML =
    '<button type="button" class="lightbox-close" aria-label="Cerrar">&times;</button>' +
    '<button type="button" class="lightbox-prev" aria-label="Anterior">&lsaquo;</button>' +
    '<img class="lightbox-img" alt="">' +
    '<p class="lightbox-caption"></p>' +
    '<button type="button" class="lightbox-next" aria-label="Siguiente">&rsaquo;</button>';
  document.body.appendChild(dialog);

  var imgEl = dialog.querySelector('.lightbox-img'),
      captionEl = dialog.querySelector('.lightbox-caption'),
      current = 0,
      lastFocused = null;

  function show(i){
    current = (i + images.length) % images.length;
    imgEl.src = images[current].currentSrc || images[current].src;
    imgEl.alt = images[current].alt || '';
    captionEl.textContent = images[current].alt || '';
  }
  function open(i){
    lastFocused = document.activeElement;
    show(i);
    dialog.showModal();
  }

  images.forEach(function(img, i){
    img.setAttribute('tabindex', '0');
    img.setAttribute('role', 'button');
    img.style.cursor = 'zoom-in';
    img.addEventListener('click', function(){ open(i); });
    img.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' '){ e.preventDefault(); open(i); }
    });
  });

  dialog.querySelector('.lightbox-close').addEventListener('click', function(){ dialog.close(); });
  dialog.querySelector('.lightbox-prev').addEventListener('click', function(){ show(current - 1); });
  dialog.querySelector('.lightbox-next').addEventListener('click', function(){ show(current + 1); });
  dialog.addEventListener('click', function(e){ if (e.target === dialog) dialog.close(); });
  dialog.addEventListener('keydown', function(e){
    if (e.key === 'ArrowLeft') show(current - 1);
    if (e.key === 'ArrowRight') show(current + 1);
  });
  dialog.addEventListener('close', function(){ if (lastFocused) lastFocused.focus(); });
})();

(function(){
  // Búsqueda instantánea: sugerencias progresivas vía el endpoint core
  // /wp-json/wp/v2/search (cubre page/post — Noticias, Agenda y Educación
  // viven como post nativo desde la 1.5 — y los CPTs civicos de V5:
  // caaguazu_artisan, institucion, lugar, servicio, proyecto — todos
  // show_in_rest) — sin endpoint propio.
  var input = document.getElementById('caaguazu-search-input');
  var list = document.getElementById('caaguazu-search-suggest');
  if (!input || !list || !window.fetch) return;

  var typeLabels = {
    page: 'Página',
    post: 'Publicación',
    caaguazu_artisan: 'Artesano',
    institucion: 'Institución',
    lugar: 'Lugar',
    servicio: 'Servicio',
    proyecto: 'Proyecto'
  };
  var timer = null, activeIndex = -1, items = [];

  function close(){
    list.hidden = true;
    list.textContent = '';
    items = [];
    activeIndex = -1;
    input.setAttribute('aria-expanded', 'false');
  }

  // Resultados vienen del REST core de WordPress: se arma el DOM a mano
  // con textContent (nunca innerHTML) para título/etiqueta, y la URL se
  // usa solo como valor de propiedad/atributo, nunca insertada como HTML.
  function render(results){
    items = results;
    activeIndex = -1;
    if (!results.length){ close(); return; }
    list.textContent = '';
    results.forEach(function(r, i){
      var label = typeLabels[r.subtype] || r.subtype;
      var li = document.createElement('li');
      li.setAttribute('role', 'option');
      li.id = 'cg-opt-' + i;
      li.dataset.url = r.url;

      var titleEl = document.createElement('span');
      titleEl.className = 's-title';
      titleEl.textContent = r.title;

      var typeEl = document.createElement('span');
      typeEl.className = 's-type';
      typeEl.textContent = label;

      li.appendChild(titleEl);
      li.appendChild(typeEl);
      list.appendChild(li);
    });
    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  }

  var endpoint = (window.caaguazuConfig && window.caaguazuConfig.restSearchUrl) || '/wp-json/wp/v2/search';

  input.addEventListener('input', function(){
    var q = input.value.trim();
    clearTimeout(timer);
    if (q.length < 2){ close(); return; }
    timer = setTimeout(function(){
      fetch(endpoint + '?search=' + encodeURIComponent(q) + '&per_page=6&subtype=page,post,caaguazu_artisan,institucion,lugar,servicio,proyecto')
        .then(function(res){ return res.ok ? res.json() : []; })
        .then(render)
        .catch(function(){ close(); });
    }, 250);
  });

  list.addEventListener('click', function(e){
    var li = e.target.closest('li[data-url]');
    if (li) window.location.href = li.dataset.url;
  });

  input.addEventListener('keydown', function(e){
    if (list.hidden) return;
    if (e.key === 'ArrowDown'){
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, items.length - 1);
    } else if (e.key === 'ArrowUp'){
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, 0);
    } else if (e.key === 'Enter' && activeIndex > -1){
      e.preventDefault();
      window.location.href = items[activeIndex].url;
      return;
    } else if (e.key === 'Escape'){
      close();
      return;
    } else {
      return;
    }
    Array.prototype.forEach.call(list.children, function(li, i){
      li.classList.toggle('active', i === activeIndex);
    });
  });

  document.addEventListener('click', function(e){
    if (e.target !== input && !list.contains(e.target)) close();
  });
})();

(function(){
  // Copiar link (botón "Copiar link" de share-buttons)
  document.querySelectorAll('.share-copy').forEach(function(btn){
    var original = btn.textContent;
    btn.addEventListener('click', function(){
      var url = btn.dataset.url;
      var done = function(){
        btn.textContent = '✓';
        btn.classList.add('copied');
        setTimeout(function(){ btn.textContent = original; btn.classList.remove('copied'); }, 1800);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done).catch(function(){});
      } else {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(ta);
      }
    });
  });
})();

(function(){
  // Sticky / compresión sólo cuando hay un hero a sangre detrás del header
  // transparente: la home y el hub de Turismo (su propio hero oscuro).
  var h = document.getElementById('header');
  var hasBleedHero = document.body.classList.contains('page-home') || document.body.classList.contains('eco-hub');
  if (h && hasBleedHero){
    var onScroll = function(){ h.classList.toggle('scrolled', window.scrollY > 100); };
    window.addEventListener('scroll', onScroll, {passive:true});
    onScroll();
  }
})();

(function(){
  // Reveal on scroll — sistema único del sitio (.reveal y [data-reveal],
  // estilos en main.css). El CSS que esconde los elementos está gateado
  // por html.motion-ready, que pone un script inline de header.php ANTES
  // del primer paint (mismas condiciones que acá): con JS apagado, sin
  // IntersectionObserver o con prefers-reduced-motion la clase no existe
  // y ningún contenido queda oculto ni se mueve.
  var els = document.querySelectorAll('.reveal, [data-reveal]');
  if (!els.length) return;
  if (!document.documentElement.classList.contains('motion-ready')) return;
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(en){
      if (!en.isIntersecting) return;
      var el = en.target;
      el.classList.add('in');
      io.unobserve(el);
      // Limpiar el delay del stagger cuando la entrada ya terminó: si
      // quedara, retrasaría también las transiciones de hover del elemento.
      setTimeout(function(){ el.style.transitionDelay = ''; }, 1000);
    });
  }, {threshold:0.15, rootMargin:'0px 0px -40px 0px'});
  // Stagger por grupo real (hermanos bajo el mismo padre, o el grupo
  // explícito [data-reveal-group]), no por índice global de página: las
  // tarjetas de una misma grilla entran en cascada corta y un elemento
  // suelto entra sin delay, sin importar cuántos reveals hubo antes.
  var groupCounts = [];
  els.forEach(function(el){
    var group = el.closest('[data-reveal-group]') || el.parentNode;
    var idx = -1;
    for (var g = 0; g < groupCounts.length; g++){
      if (groupCounts[g][0] === group){ idx = g; break; }
    }
    if (idx === -1){ groupCounts.push([group, 0]); idx = groupCounts.length - 1; }
    if (!el.style.transitionDelay){
      el.style.transitionDelay = (Math.min(groupCounts[idx][1], 5) * 70) + 'ms';
    }
    groupCounts[idx][1]++;
    io.observe(el);
  });
})();

(function(){
  // Drawer móvil (en móvil, el "sidebar derecho" del sitio). Además de
  // abrir/cerrar: Escape cierra, el foco entra al botón de cerrar al abrir
  // y vuelve a quien lo abrió al cerrar, Tab queda atrapado adentro
  // mientras está abierto, y el body no scrollea detrás del panel.
  var burger=document.getElementById('burger'),
      tabbarMenu=document.getElementById('tabbarMenu'),
      drawer=document.getElementById('drawer'),
      bg=document.getElementById('drawerBg'),
      close=document.getElementById('drawerClose'),
      opener=null;
  if (!drawer) return;
  function getFocusable(){
    var els = drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
    return Array.prototype.filter.call(els, function(el){ return el.offsetParent !== null; });
  }
  function open(e){
    opener = e && e.currentTarget ? e.currentTarget : null;
    drawer.classList.add('open'); bg.classList.add('open');
    drawer.setAttribute('aria-hidden','false');
    document.body.classList.add('drawer-open');
    close && close.focus();
  }
  function shut(){
    if (!drawer.classList.contains('open')) return;
    drawer.classList.remove('open'); bg.classList.remove('open');
    drawer.setAttribute('aria-hidden','true');
    document.body.classList.remove('drawer-open');
    if (opener){ opener.focus(); opener = null; }
  }
  burger && burger.addEventListener('click', open);
  tabbarMenu && tabbarMenu.addEventListener('click', open);
  close && close.addEventListener('click', shut);
  bg && bg.addEventListener('click', shut);
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') { shut(); return; }
    if (e.key === 'Tab' && drawer.classList.contains('open')){
      var focusables = getFocusable();
      if (!focusables.length) return;
      var first = focusables[0], last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first){
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last){
        e.preventDefault(); first.focus();
      }
    }
  });
})();

(function(){
  // Los grupos del mega-menú (.nav-dropdown-col, <details open> por HTML)
  // arrancan colapsados en viewports angostos -- si no hay JS, se quedan
  // abiertos (mismo comportamiento que antes, sin regresión).
  var groups = document.querySelectorAll('.nav-dropdown-col');
  if (!groups.length) return;
  var mq = window.matchMedia('(min-width: 1024px)');
  function sync(){
    groups.forEach(function(d){
      if (mq.matches) { d.setAttribute('open', ''); }
      else { d.removeAttribute('open'); }
    });
  }
  sync();
  if (mq.addEventListener) { mq.addEventListener('change', sync); }
  else if (mq.addListener) { mq.addListener(sync); }
})();

(function(){
  // Selector de idioma ES/GN funcional: toggle 100% cliente, persistido en
  // localStorage. El HTML servido es siempre el mismo (dual-render en PHP),
  // así que esto no depende de cookies ni rompe ningún cache de página.
  // Hay DOS copias del widget en el DOM (header.php: una en la barra
  // .header-actions, oculta bajo los 768px, y otra en el drawer móvil para
  // llegar a él en celular) — sincroniza todas las que encuentre, no solo
  // la primera.
  function syncLangButtons() {
    var isGn = document.documentElement.classList.contains('lang-gn');
    document.querySelectorAll('.lang button[data-lang]').forEach(function(b){
      b.classList.toggle('on', b.dataset.lang === (isGn ? 'GN' : 'ES'));
    });
  }
  syncLangButtons();
  document.querySelectorAll('.lang button[data-lang]').forEach(function(b){
    if (b.disabled) return;
    b.addEventListener('click', function(){
      var isGn = b.dataset.lang === 'GN';
      document.documentElement.classList.toggle('lang-gn', isGn);
      try { localStorage.setItem('caaguazuLang', isGn ? 'GN' : 'ES'); } catch (e) {}
      syncLangButtons();
    });
  });
})();

(function(){
  // "Caaguazú en números": contadores animados al entrar en viewport.
  // El valor final ya viene renderizado del servidor; sin JS o con
  // prefers-reduced-motion se queda tal cual.
  var nums = document.querySelectorAll('.stat-num[data-count]');
  if (!nums.length) return;
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (!('IntersectionObserver' in window)) return;
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(en){
      if (!en.isIntersecting) return;
      io.unobserve(en.target);
      var el = en.target,
          target = parseInt(el.dataset.count, 10) || 0,
          final = el.textContent,
          start = null,
          dur = 1600;
      function step(ts){
        if (start === null) start = ts;
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = p < 1 ? Math.round(target * eased).toLocaleString('es-PY') : final;
        if (p < 1) requestAnimationFrame(step);
      }
      el.textContent = '0';
      requestAnimationFrame(step);
    });
  }, {threshold: 0.4});
  nums.forEach(function(el){ io.observe(el); });
})();

(function(){
  // Quiz del home: 1 pregunta -> panel de resultado con 2 links resueltos server-side
  var opts = document.querySelectorAll('.quiz-opt');
  var result = document.getElementById('quizResult');
  if (!opts.length || !result || !window.caaguazuQuizMap) return;
  function pick(field){
    return document.documentElement.classList.contains('lang-gn') ? field.gn : field.es;
  }
  opts.forEach(function(btn){
    btn.addEventListener('click', function(){
      opts.forEach(function(b){ b.classList.remove('selected'); });
      btn.classList.add('selected');
      var data = window.caaguazuQuizMap[btn.dataset.key];
      if (!data) return;
      result.innerHTML =
        '<h3>' + pick(data.title) + '</h3>' +
        '<div class="quiz-result-links">' +
          '<a class="btn btn-primary" href="' + data.primary_url + '">' + pick(data.primary_label) + '</a>' +
          '<a class="arrow" href="' + data.secondary_url + '">' + pick(data.secondary_label) + '</a>' +
        '</div>';
      result.hidden = false;
    });
  });
})();
