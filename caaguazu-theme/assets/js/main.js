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
  // /wp-json/wp/v2/search (ya cubre page/caaguazu_news/caaguazu_event/
  // caaguazu_artisan al ser show_in_rest) — sin endpoint propio.
  var input = document.getElementById('caaguazu-search-input');
  var list = document.getElementById('caaguazu-search-suggest');
  if (!input || !list || !window.fetch) return;

  var typeLabels = { page: 'Página', caaguazu_news: 'Noticia', caaguazu_event: 'Evento', caaguazu_artisan: 'Artesano' };
  var timer = null, activeIndex = -1, items = [];

  function close(){
    list.hidden = true;
    list.innerHTML = '';
    items = [];
    activeIndex = -1;
    input.setAttribute('aria-expanded', 'false');
  }

  function render(results){
    items = results;
    activeIndex = -1;
    if (!results.length){ close(); return; }
    list.innerHTML = results.map(function(r, i){
      var label = typeLabels[r.subtype] || r.subtype;
      return '<li role="option" id="cg-opt-' + i + '" data-url="' + r.url + '">' +
        '<span class="s-title">' + r.title + '</span><span class="s-type">' + label + '</span></li>';
    }).join('');
    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  }

  var endpoint = (window.caaguazuConfig && window.caaguazuConfig.restSearchUrl) || '/wp-json/wp/v2/search';

  input.addEventListener('input', function(){
    var q = input.value.trim();
    clearTimeout(timer);
    if (q.length < 2){ close(); return; }
    timer = setTimeout(function(){
      fetch(endpoint + '?search=' + encodeURIComponent(q) + '&per_page=6&subtype=page,caaguazu_news,caaguazu_event,caaguazu_artisan')
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
  // Parallax hero
  var m = document.getElementById('heroMedia');
  if (!m) return;
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  window.addEventListener('scroll', function(){
    m.style.transform = 'translate3d(0,' + (window.scrollY * 0.4) + 'px,0)';
  }, {passive:true});
})();

(function(){
  // Video toggle
  var v = document.getElementById('heroVideo'),
      b = document.getElementById('videoToggle');
  if (!v || !b) return;
  b.addEventListener('click', function(){
    if (v.paused){ v.play(); b.textContent='❚❚'; b.setAttribute('aria-label','Pausar video'); }
    else { v.pause(); b.textContent='▶'; b.setAttribute('aria-label','Reproducir video'); }
  });
})();

(function(){
  // Reveal on scroll
  var els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window)){
    els.forEach(function(el){ el.classList.add('in'); });
    return;
  }
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(en){
      if (en.isIntersecting){ en.target.classList.add('in'); io.unobserve(en.target); }
    });
  }, {threshold:0.2});
  els.forEach(function(el, i){
    el.style.transitionDelay = ((i % 3) * 80) + 'ms';
    io.observe(el);
  });
})();

(function(){
  // Drawer móvil
  var burger=document.getElementById('burger'),
      tabbarMenu=document.getElementById('tabbarMenu'),
      drawer=document.getElementById('drawer'),
      bg=document.getElementById('drawerBg'),
      close=document.getElementById('drawerClose');
  if (!drawer) return;
  function open(){ drawer.classList.add('open'); bg.classList.add('open'); drawer.setAttribute('aria-hidden','false'); }
  function shut(){ drawer.classList.remove('open'); bg.classList.remove('open'); drawer.setAttribute('aria-hidden','true'); }
  burger && burger.addEventListener('click', open);
  tabbarMenu && tabbarMenu.addEventListener('click', open);
  close && close.addEventListener('click', shut);
  bg && bg.addEventListener('click', shut);
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
  var isCurrentlyGn = document.documentElement.classList.contains('lang-gn');
  var current = document.querySelector('.lang button[data-lang="' + (isCurrentlyGn ? 'GN' : 'ES') + '"]');
  if (current) {
    document.querySelectorAll('.lang button').forEach(function(x){ x.classList.remove('on'); });
    current.classList.add('on');
  }
  document.querySelectorAll('.lang button[data-lang]').forEach(function(b){
    if (b.disabled) return;
    b.addEventListener('click', function(){
      b.parentNode.querySelectorAll('button').forEach(function(x){ x.classList.remove('on'); });
      b.classList.add('on');
      var isGn = b.dataset.lang === 'GN';
      document.documentElement.classList.toggle('lang-gn', isGn);
      try { localStorage.setItem('caaguazuLang', isGn ? 'GN' : 'ES'); } catch (e) {}
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
