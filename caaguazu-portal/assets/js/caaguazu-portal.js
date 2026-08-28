/**
 * Caaguazú Portal — interacción del panel.
 * Chrome (tema, drawer, dropdowns), PWA, editor (checklist en vivo, guardar, subir, geo)
 * y acciones de revisión.
 */
(function () {
	'use strict';

	var CFG = window.PROMOTUR || {};
	var i18n = CFG.i18n || {};

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	/**
	 * POST a la puerta de datos del panel. data: FormData u objeto plano.
	 *
	 * La respuesta se lee como texto y recién después se parsea. Parece un
	 * rodeo y no lo es: cuando el servidor devuelve algo que NO es JSON —una
	 * página de error de PHP, un 404 en HTML, el aviso de un plugin de caché—
	 * `r.json()` explota con «Unexpected token <», y el catch de quien llama lo
	 * traduce a «Algo salió mal. Probá de nuevo.». Ese mensaje no distingue «tu
	 * sesión venció» de «esta URL no existe», que son dos problemas con dos
	 * arreglos distintos, y buscar a ciegas cuál de los dos es cuesta caro.
	 *
	 * Ahora el error dice el código HTTP y el principio de lo que vino. Es feo
	 * a propósito: un error que no se puede diagnosticar cuesta más que uno feo.
	 */
	function ajax(action, data) {
		var body;
		if (data instanceof FormData) { body = data; }
		else { body = new FormData(); Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); }); }
		body.append('promotur_token', CFG.token);
		return fetch(CFG.datosUrl + action, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (r) {
				return r.text().then(function (texto) {
					try {
						return JSON.parse(texto);
					} catch (e) {
						var pista = texto.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 120);
						return {
							success: false,
							data: { message: 'El servidor respondió ' + r.status + ' y no en el formato esperado. ' + (pista || 'Respuesta vacía.') }
						};
					}
				});
			});
	}

	/**
	 * Mensaje inline. Reemplaza a alert(): un cartel del navegador tapa la
	 * pantalla, no dice dónde falló y en un teléfono se cierra sin querer.
	 * tipo: '' | 'is-error' | 'is-success'
	 */
	function decir(el, texto, tipo) {
		if (!el) { return; }
		el.textContent = texto;
		el.className = 'promotur-form-msg' + (tipo ? ' ' + tipo : '');
	}

	ready(function () {
		initSplash();
		initTheme();
		initDrawer();
		initDropdowns();
		initConfirmar();
		initInstall();
		initServiceWorker();
		initSubnav();
		initAtajos();
		initEditor();
		initAcciones();
		initPapelera();
		initReview();
		initGestion();
		initCaptura();
	});

	/* ---------- Salida de campo (captura offline) ---------- */
	function initCaptura() {
		var root = document.querySelector('[data-captura]');
		var list = document.querySelector('[data-captura-list]');
		if (!root || !list) { return; }
		var KEY = 'promotur_capturas';
		var form = root.querySelector('[data-captura-form]');
		var msg = form.querySelector('[data-form-msg]');
		var photoInput = form.querySelector('[data-captura-photo]');
		var geoBtn = form.querySelector('[data-captura-geo]');
		var countEl = document.querySelector('[data-captura-count]');
		var syncBtn = document.querySelector('[data-captura-sync]');
		var photoData = null;

		function getQ() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
		function setQ(q) { try { localStorage.setItem(KEY, JSON.stringify(q)); } catch (e) { decir(msg, 'No hay espacio para guardar la foto offline.', 'is-error'); } }

		if (photoInput) {
			photoInput.addEventListener('change', function () {
				var f = photoInput.files && photoInput.files[0];
				if (!f) { photoData = null; return; }
				var r = new FileReader();
				r.onload = function () { photoData = r.result; };
				r.readAsDataURL(f);
			});
		}
		if (geoBtn && navigator.geolocation) {
			geoBtn.addEventListener('click', function () {
				geoBtn.disabled = true;
				navigator.geolocation.getCurrentPosition(function (p) {
					form.querySelector('[data-captura-lat]').value = p.coords.latitude.toFixed(6);
					form.querySelector('[data-captura-lng]').value = p.coords.longitude.toFixed(6);
					geoBtn.disabled = false;
				}, function () { geoBtn.disabled = false; }, { enableHighAccuracy: true, timeout: 8000 });
			});
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var q = getQ();
			q.push({
				id: Date.now(),
				titulo: form.querySelector('[name="titulo"]').value,
				nota: form.querySelector('[name="nota"]').value,
				lat: form.querySelector('[data-captura-lat]').value,
				lng: form.querySelector('[data-captura-lng]').value,
				photo: photoData
			});
			setQ(q);
			form.reset();
			photoData = null;
			render();
			if (msg) { msg.textContent = '✓ ' + (navigator.onLine ? 'Guardada' : 'Guardada offline'); msg.className = 'promotur-form-msg is-success'; }
		});

		function render() {
			var q = getQ();
			if (countEl) { countEl.textContent = '(' + q.length + ')'; }
			if (!q.length) { list.innerHTML = '<p class="promotur-muted">Sin capturas pendientes.</p>'; return; }
			list.innerHTML = '';
			q.forEach(function (item) {
				var row = document.createElement('div');
				row.className = 'promotur-row';
				row.innerHTML = '<span class="promotur-row__main"><span class="promotur-row__title">' + escapeHtml(item.titulo || '(sin nombre)') + '</span>' +
					'<span class="promotur-row__meta">' + (item.lat ? '📍 ' + item.lat + ', ' + item.lng : 'sin GPS') + (item.photo ? ' · 📷' : '') + '</span></span>';
				var rm = document.createElement('button');
				rm.type = 'button'; rm.className = 'promotur-iconbtn'; rm.textContent = '✕';
				rm.addEventListener('click', function () { setQ(getQ().filter(function (x) { return x.id !== item.id; })); render(); });
				row.appendChild(rm);
				list.appendChild(row);
			});
		}

		if (syncBtn) {
			syncBtn.addEventListener('click', function () {
				if (!navigator.onLine) { decir(msg, 'Necesitás conexión para sincronizar.', 'is-error'); return; }
				var q = getQ();
				if (!q.length) { return; }
				syncBtn.disabled = true;
				syncOne(q, 0);
			});
		}

		function syncOne(q, i) {
			if (i >= q.length) { syncBtn.disabled = false; render(); if (msg) { msg.textContent = '✓ Sincronizado'; msg.className = 'promotur-form-msg is-success'; } return; }
			var item = q[i];
			var afterPhoto = function (attachmentId) {
				var fd = new FormData();
				fd.append('post_id', '0');
				fd.append('titulo', item.titulo || '(sin título)');
				fd.append('descripcion', item.nota || '');
				if (item.lat) { fd.append('meta[_promotur_lat]', item.lat); }
				if (item.lng) { fd.append('meta[_promotur_lng]', item.lng); }
				if (attachmentId) { fd.append('meta[_promotur_portada]', attachmentId); }
				ajax('save_contenido', fd).then(function (r) {
					if (r.success) { setQ(getQ().filter(function (x) { return x.id !== item.id; })); }
					syncOne(q, i + 1);
				}).catch(function () { syncOne(q, i + 1); });
			};
			if (item.photo) {
				fetch(item.photo).then(function (res) { return res.blob(); }).then(function (blob) {
					var pf = new FormData();
					pf.append('file', blob, 'captura-' + item.id + '.jpg');
					ajax('upload_media', pf).then(function (r) { afterPhoto(r.success ? r.data.id : 0); }).catch(function () { afterPhoto(0); });
				}).catch(function () { afterPhoto(0); });
			} else { afterPhoto(0); }
		}

		function escapeHtml(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
		render();
	}

	/* ---------- Gestión (tareas, nivel) ---------- */
	function initGestion() {
		// Crear tarea.
		var tform = document.querySelector('[data-tarea-form]');
		if (tform) {
			tform.addEventListener('submit', function (e) {
				e.preventDefault();
				var msg = tform.querySelector('[data-form-msg]');
				var fd = new FormData(tform);
				if (msg) { msg.textContent = i18n.sending; msg.className = 'promotur-form-msg'; }
				ajax('create_tarea', fd).then(function (r) {
					if (!r.success) { if (msg) { msg.textContent = (r.data && r.data.message) || i18n.error; msg.className = 'promotur-form-msg is-error'; } return; }
					window.location.reload();
				}).catch(function () { if (msg) { msg.textContent = i18n.error; msg.className = 'promotur-form-msg is-error'; } });
			});
		}
		// Reclamar / completar tareas.
		document.querySelectorAll('[data-tarea]').forEach(function (card) {
			var id = card.getAttribute('data-tarea');
			card.querySelectorAll('[data-op]').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var map = { claim: 'claim_tarea', complete: 'complete_tarea' };
					var action = map[btn.getAttribute('data-op')];
					if (!action) { return; }
					btn.disabled = true;
					ajax(action, { id: id }).then(function (r) {
						if (r.success) { window.location.reload(); return; }
						btn.disabled = false;
						decir(card.querySelector('[data-form-msg]'), (r.data && r.data.message) || i18n.error, 'is-error');
					}).catch(function () {
						btn.disabled = false;
						decir(card.querySelector('[data-form-msg]'), i18n.error, 'is-error');
					});
				});
			});
		});
		// Guardar nivel de confianza.
		document.querySelectorAll('[data-user]').forEach(function (card) {
			var save = card.querySelector('[data-nivel-save]');
			if (!save) { return; }
			save.addEventListener('click', function () {
				var sel = card.querySelector('[data-nivel-select]');
				var msg = card.querySelector('[data-form-msg]');
				ajax('set_nivel', { user_id: card.getAttribute('data-user'), level: sel ? sel.value : '' }).then(function (r) {
					if (msg) { msg.textContent = r.success ? (r.data.message || i18n.saved) : ((r.data && r.data.message) || i18n.error); msg.className = 'promotur-form-msg ' + (r.success ? 'is-success' : 'is-error'); }
				});
			});
		});
	}

	/* ---------- Submenú del lateral ---------- */
	function initSubnav() {
		document.querySelectorAll('[data-subnav-toggle]').forEach(function (caret) {
			var panel = document.getElementById(caret.getAttribute('data-subnav-toggle'));
			if (!panel) { return; }
			function alternar(e) {
				// El caret vive dentro del enlace del padre: si no frenamos el
				// clic, abrir el submenú navega a la sección.
				e.preventDefault();
				e.stopPropagation();
				var abierto = !panel.hidden;
				panel.hidden = abierto;
				caret.setAttribute('aria-expanded', abierto ? 'false' : 'true');
			}
			caret.addEventListener('click', alternar);
			caret.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') { alternar(e); }
			});
		});
	}

	/* ---------- Atajos de teclado ---------- */
	function initAtajos() {
		// ⌘K / Ctrl+K enfoca el buscador — el mismo atajo que anuncia la tecla
		// dibujada al lado del campo. Un atajo anunciado y no implementado es
		// peor que no anunciarlo.
		document.addEventListener('keydown', function (e) {
			if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
				var campo = document.querySelector('[data-buscador]');
				if (campo) { e.preventDefault(); campo.focus(); campo.select(); }
			}
		});
	}

	/* ---------- Splash ---------- */
	function initSplash() {
		var splash = document.querySelector('[data-splash]');
		if (!splash) { return; }
		if (document.documentElement.classList.contains('promotur-no-splash')) { splash.classList.add('is-hidden'); return; }
		setTimeout(function () { splash.classList.add('is-hidden'); }, 1700);
	}

	/* ---------- Tema claro/oscuro ---------- */
	function initTheme() {
		document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var dark = document.documentElement.getAttribute('data-theme') === 'dark';
				document.documentElement.setAttribute('data-theme', dark ? '' : 'dark');
				try { localStorage.setItem('promotur-theme', dark ? 'light' : 'dark'); } catch (e) {}
			});
		});
	}

	/* ---------- Drawer móvil ---------- */
	function initDrawer() {
		var toggle = document.querySelector('[data-drawer-toggle]');
		var backdrop = document.querySelector('[data-drawer-backdrop]');
		function open() { document.body.classList.add('promotur-nav-open'); if (backdrop) { backdrop.hidden = false; } if (toggle) { toggle.setAttribute('aria-expanded', 'true'); } }
		function close() { document.body.classList.remove('promotur-nav-open'); if (backdrop) { backdrop.hidden = true; } if (toggle) { toggle.setAttribute('aria-expanded', 'false'); } }
		if (toggle) {
			toggle.addEventListener('click', function () {
				document.body.classList.contains('promotur-nav-open') ? close() : open();
			});
		}
		if (backdrop) { backdrop.addEventListener('click', close); }
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); } });
		window.addEventListener('resize', function () { if (window.innerWidth >= 900) { close(); } });
	}

	/* ---------- Dropdowns ---------- */
	function initDropdowns() {
		var groups = document.querySelectorAll('[data-dropdown]');
		groups.forEach(function (group) {
			var toggle = group.querySelector('[data-dropdown-toggle]');
			var panel = group.querySelector('[data-dropdown-panel]');
			if (!toggle || !panel) { return; }
			toggle.addEventListener('click', function (e) {
				e.stopPropagation();
				var open = !panel.hidden;
				closeAll();
				panel.hidden = open;
				toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
			});
		});
		function closeAll() {
			groups.forEach(function (g) {
				var p = g.querySelector('[data-dropdown-panel]');
				var t = g.querySelector('[data-dropdown-toggle]');
				if (p) { p.hidden = true; }
				if (t) { t.setAttribute('aria-expanded', 'false'); }
			});
		}
		document.addEventListener('click', closeAll);
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeAll(); } });
	}

	/* ---------- Confirmar lo que no se puede deshacer ---------- */
	function initConfirmar() {
		document.addEventListener('submit', function (e) {
			var form = e.target.closest ? e.target.closest('[data-confirmar]') : null;
			if (!form) { return; }
			if (!window.confirm(form.getAttribute('data-confirmar'))) { e.preventDefault(); }
		});
	}

	/* ---------- Instalar app (PWA) ---------- */
	var deferredPrompt = null;
	function initInstall() {
		var btn = document.querySelector('[data-install-app]');
		window.addEventListener('beforeinstallprompt', function (e) {
			e.preventDefault();
			deferredPrompt = e;
			if (btn) { btn.hidden = false; }
		});
		if (btn) {
			btn.addEventListener('click', function () {
				if (!deferredPrompt) { return; }
				deferredPrompt.prompt();
				deferredPrompt.userChoice.finally(function () { deferredPrompt = null; btn.hidden = true; });
			});
		}
	}

	function initServiceWorker() {
		if ('serviceWorker' in navigator && CFG.swUrl) {
			window.addEventListener('load', function () {
				navigator.serviceWorker.register(CFG.swUrl, { scope: CFG.swScope || '/' }).catch(function () {});
			});
		}
	}

	/* ---------- Editor ----------
	 *
	 * Uno solo para los tres tipos de contenido. El tipo viaja en un campo
	 * oculto del formulario, así que acá no hay ninguna rama por tipo: lo que
	 * cambia es qué campos tiene el formulario, y de eso se ocupa el servidor.
	 */
	function initEditor() {
		var form = document.querySelector('[data-editor-form]');
		if (!form) { return; }
		var msg = form.querySelector('[data-form-msg]');

		initParadas(form);
		initTipoItem(form);

		// Checklist en vivo.
		function refreshChecklist() {
			document.querySelectorAll('[data-checklist-key]').forEach(function (li) {
				var key = li.getAttribute('data-checklist-key');
				var field = form.querySelector('[data-check="' + cssEscape(key) + '"]');
				var done;
				if (!field) { return; }
				if (field.hasAttribute('data-paradas-lista')) {
					// Las paradas no son un campo con valor: el mínimo es
					// cuántas hay con un sitio elegido.
					done = contarParadas(field) >= 2;
				} else {
					done = String(field.value || '').trim() !== '';
				}
				li.classList.toggle('is-done', !!done);
			});
		}
		form.addEventListener('input', refreshChecklist);
		form.addEventListener('change', refreshChecklist);
		form.addEventListener('promotur:paradas', refreshChecklist);
		refreshChecklist();

		// Subida de imágenes.
		form.querySelectorAll('[data-upload]').forEach(function (box) {
			var input = box.querySelector('[data-upload-input]');
			var value = box.querySelector('[data-upload-value]');
			var preview = box.querySelector('[data-upload-preview]');
			if (!input) { return; }
			input.addEventListener('change', function () {
				if (!input.files || !input.files[0]) { return; }
				var fd = new FormData();
				fd.append('file', input.files[0]);
				setMsg(i18n.sending, '');
				ajax('upload_media', fd).then(function (res) {
					if (!res.success) { setMsg((res.data && res.data.message) || i18n.error, 'is-error'); return; }
					value.value = res.data.id;
					if (preview && res.data.thumb) { preview.style.backgroundImage = 'url(' + res.data.thumb + ')'; }
					setMsg(i18n.photoUploaded, 'is-success');
					refreshChecklist();
				}).catch(function () { setMsg(i18n.error, 'is-error'); });
			});
		});

		// Geolocalización.
		var geoBtn = form.querySelector('[data-geolocate]');
		if (geoBtn && navigator.geolocation) {
			geoBtn.addEventListener('click', function () {
				geoBtn.disabled = true;
				navigator.geolocation.getCurrentPosition(function (pos) {
					var lat = form.querySelector('[data-coord="lat"]');
					var lng = form.querySelector('[data-coord="lng"]');
					if (lat) { lat.value = pos.coords.latitude.toFixed(6); }
					if (lng) { lng.value = pos.coords.longitude.toFixed(6); }
					geoBtn.disabled = false;
					refreshChecklist();
				}, function () { geoBtn.disabled = false; setMsg(i18n.error, 'is-error'); });
			});
		}

		function save() {
			var fd = new FormData(form);
			setMsg(i18n.sending, '');
			return ajax('save_contenido', fd).then(function (res) {
				if (!res.success) { setMsg((res.data && res.data.message) || i18n.error, 'is-error'); return res; }
				var pid = form.querySelector('[name="post_id"]');
				if (pid && res.data.post_id) { pid.value = res.data.post_id; }
				renderChecklist(res.data.checklist);
				return res;
			});
		}

		function renderChecklist(list) {
			if (!list) { return; }
			list.forEach(function (item) {
				var li = document.querySelector('[data-checklist-key="' + cssEscape(item.key) + '"]');
				if (li) { li.classList.toggle('is-done', !!item.done); }
			});
		}

		form.querySelectorAll('[data-action]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var action = btn.getAttribute('data-action');
				setBusy(true);
				save().then(function (res) {
					if (!res || !res.success) { setBusy(false); return; }
					// Los avisos del servidor que no son errores —el enlace
					// corto de Maps del que no salió el pin, las paradas que no
					// entraron— se dicen acá y no se tragan.
					var aviso = res.data.aviso_maps || res.data.aviso_paradas || '';
					if (action === 'save') {
						setMsg(aviso || res.data.message || i18n.saved, aviso ? '' : 'is-success');
						setBusy(false);
						return;
					}
					var pid = form.querySelector('[name="post_id"]').value;
					var tipoInput = form.querySelector('[name="tipo"]');
					ajax('submit_contenido', { post_id: pid, tipo: tipoInput ? tipoInput.value : 'destino' }).then(function (r2) {
						setBusy(false);
						if (!r2.success) { setMsg((r2.data && r2.data.message) || i18n.missing, 'is-error'); if (r2.data && r2.data.checklist) { renderChecklist(r2.data.checklist); } return; }
						setMsg(r2.data.message, 'is-success');
						if (r2.data.redirect) { window.location.href = r2.data.redirect; }
					}).catch(function () { setBusy(false); setMsg(i18n.error, 'is-error'); });
				}).catch(function () { setBusy(false); setMsg(i18n.error, 'is-error'); });
			});
		});

		function setBusy(b) { form.querySelectorAll('[data-action]').forEach(function (x) { x.disabled = b; }); }
		function setMsg(text, cls) { if (msg) { msg.textContent = text; msg.className = 'promotur-form-msg ' + (cls || ''); } }
	}

	/* ---------- Sitio o evento ----------
	 *
	 * Los campos marcados `data-solo="evento"` —la fecha y la hora— sólo tienen
	 * sentido en un evento. Se ocultan en vez de deshabilitarse para que no
	 * ocupen lugar en una ficha que no los usa, y siguen viajando en el envío:
	 * si alguien cargó una fecha y después cambió el tipo a sitio, el dato no
	 * se pierde por haber tocado un desplegable.
	 *
	 * Esto es comodidad de pantalla y nada más: quien decide si la fecha es
	 * obligatoria es el checklist del servidor, que vuelve a mirar el tipo.
	 */
	function initTipoItem(form) {
		var selector = form.querySelector('[data-tipo-item]');
		if (!selector) { return; }
		var condicionales = form.querySelectorAll('[data-solo]');
		if (!condicionales.length) { return; }

		function aplicar() {
			var tipo = selector.value;
			condicionales.forEach(function (campo) {
				campo.hidden = campo.getAttribute('data-solo') !== tipo;
			});
		}
		selector.addEventListener('change', aplicar);
		aplicar();
	}

	/** Cuántas paradas tienen un sitio elegido de verdad. */
	function contarParadas(lista) {
		var n = 0;
		lista.querySelectorAll('[data-parada-sitio]').forEach(function (sel) {
			if (sel.value) { n++; }
		});
		return n;
	}

	/* ---------- Armador de paradas ----------
	 *
	 * El orden ES el contenido de un recorrido, así que mover una parada tiene
	 * que ser una operación de un toque y verse al instante. Se hace moviendo
	 * el nodo entero en el DOM —no reordenando datos y redibujando— para que
	 * lo que la persona escribió en el textarea viaje con su parada.
	 *
	 * Después de cada movimiento se renumeran dos cosas: el número que se ve, y
	 * el índice dentro del `name` de cada campo (`paradas[2][texto]`). El
	 * servidor igual renumera al guardar, pero si los índices se repitieran, el
	 * navegador mandaría dos valores para la misma clave y una parada pisaría a
	 * la otra antes de llegar.
	 */
	function initParadas(form) {
		var caja = form.querySelector('[data-paradas]');
		if (!caja) { return; }

		var lista = caja.querySelector('[data-paradas-lista]');
		var molde = caja.querySelector('[data-parada-molde]');
		var agregar = caja.querySelector('[data-parada-agregar]');
		var msg = caja.querySelector('[data-paradas-msg]');
		var max = parseInt(caja.getAttribute('data-paradas-max'), 10) || 9;
		var textoLleno = caja.getAttribute('data-paradas-lleno') || '';

		function filas() { return Array.prototype.slice.call(lista.querySelectorAll('[data-parada]')); }

		function renumerar() {
			filas().forEach(function (fila, i) {
				var n = fila.querySelector('[data-parada-n]');
				if (n) { n.textContent = String(i + 1); }
				fila.querySelectorAll('[name]').forEach(function (campo) {
					campo.name = campo.name.replace(/^paradas\[[^\]]*\]/, 'paradas[' + i + ']');
				});
			});
			if (agregar) { agregar.disabled = filas().length >= max; }
			if (msg) {
				msg.textContent = filas().length >= max ? textoLleno : '';
				msg.className = 'promotur-form-msg';
			}
			form.dispatchEvent(new Event('promotur:paradas'));
		}

		function enganchar(fila) {
			var mover = function (delta) {
				var todas = filas();
				var i = todas.indexOf(fila);
				var j = i + delta;
				if (j < 0 || j >= todas.length) { return; }
				if (delta < 0) { lista.insertBefore(fila, todas[j]); }
				else { lista.insertBefore(todas[j], fila); }
				renumerar();
				// Devolver el foco al botón que se tocó: si no, después de
				// mover una parada con el teclado el foco se pierde y hay que
				// volver a tabular desde arriba.
				var boton = fila.querySelector(delta < 0 ? '[data-parada-subir]' : '[data-parada-bajar]');
				if (boton) { boton.focus(); }
			};
			var subir = fila.querySelector('[data-parada-subir]');
			var bajar = fila.querySelector('[data-parada-bajar]');
			var quitar = fila.querySelector('[data-parada-quitar]');
			if (subir) { subir.addEventListener('click', function () { mover(-1); }); }
			if (bajar) { bajar.addEventListener('click', function () { mover(1); }); }
			if (quitar) {
				quitar.addEventListener('click', function () {
					fila.parentNode.removeChild(fila);
					renumerar();
				});
			}
		}

		filas().forEach(enganchar);

		if (agregar && molde) {
			agregar.addEventListener('click', function () {
				if (filas().length >= max) { return; }
				var html = molde.innerHTML.replace(/__i__/g, String(filas().length));
				var temp = document.createElement('div');
				temp.innerHTML = html;
				var fila = temp.querySelector('[data-parada]');
				if (!fila) { return; }
				lista.appendChild(fila);
				enganchar(fila);
				renumerar();
				var sel = fila.querySelector('[data-parada-sitio]');
				if (sel) { sel.focus(); }
			});
		}

		renumerar();
	}

	/* ---------- Acciones sobre el estado ----------
	 *
	 * Despublicar, retirar de revisión, archivar y borrar. Los botones los
	 * dibuja el servidor según lo que `PROMOTUR_Editorial::transiciones()`
	 * permita, así que acá no hay ninguna regla: se manda lo que dice el botón
	 * y el servidor vuelve a preguntar.
	 *
	 * La confirmación viaja en el propio botón (`data-confirmar-accion`) y no
	 * en un texto genérico: «¿Seguro?» no es lo mismo que «esto está publicado
	 * y la app lo está mostrando».
	 */
	function initAcciones() {
		var caja = document.querySelector('[data-acciones]');
		if (!caja) { return; }
		var postId = caja.getAttribute('data-acciones');
		var msg = caja.querySelector('[data-acciones-msg]');

		function pedir(accion, datos, boton) {
			var aviso = boton.getAttribute('data-confirmar-accion');
			if (aviso && !window.confirm(aviso)) { return; }
			botones(true);
			decir(msg, i18n.sending, '');
			ajax(accion, datos).then(function (r) {
				if (!r.success) {
					botones(false);
					decir(msg, (r.data && r.data.message) || i18n.error, 'is-error');
					return;
				}
				decir(msg, r.data.message || i18n.saved, 'is-success');
				if (r.data.redirect) { window.location.href = r.data.redirect; return; }
				// El estado cambió: lo que se puede hacer ahora es otra cosa,
				// y el checklist y la pastilla también. Recargar es más honesto
				// que remendar media pantalla a mano.
				window.location.reload();
			}).catch(function () {
				botones(false);
				decir(msg, i18n.error, 'is-error');
			});
		}

		function botones(deshabilitar) {
			caja.querySelectorAll('button').forEach(function (b) { b.disabled = deshabilitar; });
		}

		caja.querySelectorAll('[data-transicion]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				pedir('cambiar_estado', { post_id: postId, transicion: btn.getAttribute('data-transicion') }, btn);
			});
		});

		var borrar = caja.querySelector('[data-borrar]');
		if (borrar) {
			borrar.addEventListener('click', function () {
				pedir('borrar_contenido', { post_id: postId }, borrar);
			});
		}
	}

	/* ---------- Papelera: recuperar ---------- */
	function initPapelera() {
		document.querySelectorAll('[data-restaurar]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				btn.disabled = true;
				ajax('restaurar_contenido', { post_id: btn.getAttribute('data-restaurar') }).then(function (r) {
					if (r.success && r.data.redirect) { window.location.href = r.data.redirect; return; }
					btn.disabled = false;
					decir(btn.parentNode.querySelector('[data-form-msg]'), (r.data && r.data.message) || i18n.error, 'is-error');
				}).catch(function () { btn.disabled = false; });
			});
		});
	}

	/* ---------- Revisión ---------- */
	function initReview() {
		var box = document.querySelector('[data-review]');
		if (!box) { return; }
		var postId = box.getAttribute('data-review');
		var comment = box.querySelector('[data-review-comment]');
		var msg = box.querySelector('[data-form-msg]');

		box.querySelectorAll('[data-quickfb]').forEach(function (chip) {
			chip.addEventListener('click', function () {
				if (!comment) { return; }
				comment.value = (comment.value ? comment.value + '\n' : '') + chip.getAttribute('data-quickfb');
			});
		});

		box.querySelectorAll('[data-review-action]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var action = btn.getAttribute('data-review-action');
				var map = { assign: 'assign_review', approve: 'approve', return: 'return_changes' };
				if (action === 'return' && comment && !comment.value.trim()) {
					setMsg(i18n.missing, 'is-error'); return;
				}
				if (action === 'approve' && !confirm(i18n.confirm)) { return; }
				setBusy(true);
				ajax(map[action], { post_id: postId, comment: comment ? comment.value : '' }).then(function (res) {
					setBusy(false);
					if (!res.success) { setMsg((res.data && res.data.message) || i18n.error, 'is-error'); return; }
					setMsg(res.data.message, 'is-success');
					if (res.data.redirect) { window.location.href = res.data.redirect; }
					else { window.location.reload(); }
				}).catch(function () { setBusy(false); setMsg(i18n.error, 'is-error'); });
			});
		});

		function setBusy(b) { box.querySelectorAll('[data-review-action]').forEach(function (x) { x.disabled = b; }); }
		function setMsg(text, cls) { if (msg) { msg.textContent = text; msg.className = 'promotur-form-msg ' + (cls || ''); } }
	}

	/* CSS.escape con fallback (keys de meta tienen guiones bajos, seguro). */
	function cssEscape(s) {
		if (window.CSS && CSS.escape) { return CSS.escape(s); }
		return String(s).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
	}
})();
