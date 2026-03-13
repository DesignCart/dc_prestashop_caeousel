/**
 * DC Filemanager – front (modal, grid, dropzone).
 * Delegacja zdarzeń: działa też dla wierszy dodanych dynamicznie (np. w tabeli).
 */
(function () {
	'use strict';

	var ALLOWED_EXT = ['jpg', 'jpeg', 'webp', 'png', 'svg'];
	var MAX_SIZE = 3 * 1024 * 1024;

	function getConfig(container) {
		if (!container) return {};
		return {
			apiUrl: container.getAttribute('data-dc-filemanager-api') || (window.DC_FILEMANAGER && window.DC_FILEMANAGER.apiUrl),
			baseUrl: (container.getAttribute('data-dc-filemanager-base') || (window.DC_FILEMANAGER && window.DC_FILEMANAGER.baseUrl) || '').replace(/\/?$/, '/')
		};
	}

	function createModal() {
		var wrap = document.createElement('div');
		wrap.className = 'dc-fm-overlay';
		wrap.innerHTML =
			'<div class="dc-fm-modal" role="dialog">' +
			'  <div class="dc-fm-header">' +
			'    <h3 class="dc-fm-title">Menadżer obrazków</h3>' +
			'    <button type="button" class="dc-fm-close" aria-label="Zamknij">&times;</button>' +
			'  </div>' +
			'  <div class="dc-fm-toolbar">' +
			'    <button type="button" class="dc-fm-btn dc-fm-btn-up" title="Katalog wyżej">&#8679;</button>' +
			'    <button type="button" class="dc-fm-btn dc-fm-btn-refresh" title="Odśwież">&#8635;</button>' +
			'    <button type="button" class="dc-fm-btn dc-fm-btn-upload">Wgraj</button>' +
			'    <input type="file" class="dc-fm-file-input" accept=".jpg,.jpeg,.webp,.png,.svg" multiple style="display:none">' +
			'    <input type="text" class="dc-fm-search" placeholder="Szukaj...">' +
			'  </div>' +
			'  <div class="dc-fm-body">' +
			'    <div class="dc-fm-dropzone-hint">Upuść pliki tutaj (jpg, png, webp, svg, max 3 MB)</div>' +
			'    <div class="dc-fm-breadcrumb"></div>' +
			'    <div class="dc-fm-content"></div>' +
			'  </div>' +
			'</div>';
		document.body.appendChild(wrap);
		return wrap;
	}

	function getModal() {
		var el = document.querySelector('.dc-fm-overlay');
		if (!el) el = createModal();
		return el;
	}

	function openModal(apiUrl, baseUrl, onSelect) {
		var overlay = getModal();
		overlay._dcFm = { apiUrl: apiUrl, baseUrl: baseUrl, onSelect: onSelect, path: '', data: null };
		overlay.classList.add('dc-fm-open');
		overlay.querySelector('.dc-fm-search').value = '';
		loadList(overlay);
		bindModalEvents(overlay, overlay.querySelector('.dc-fm-file-input'));
	}

	function closeModal() {
		var overlay = document.querySelector('.dc-fm-overlay.dc-fm-open');
		if (overlay) overlay.classList.remove('dc-fm-open');
	}

	function loadList(overlay) {
		var cfg = overlay._dcFm;
		if (!cfg || !cfg.apiUrl) return;
		var content = overlay.querySelector('.dc-fm-content');
		content.innerHTML = '<div class="dc-fm-loading">Ładowanie...</div>';
		var url = cfg.apiUrl + (cfg.apiUrl.indexOf('?') >= 0 ? '&' : '?') + 'action=list&path=' + encodeURIComponent(cfg.path || '');

		fetch(url)
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res.ok) {
					content.innerHTML = '<div class="dc-fm-error">' + (res.error || 'Błąd') + '</div>';
					return;
				}
				cfg.data = res;
				renderBreadcrumb(overlay);
				renderGrid(overlay);
			})
			.catch(function () {
				content.innerHTML = '<div class="dc-fm-error">Błąd połączenia</div>';
			});
	}

	function renderBreadcrumb(overlay) {
		var cfg = overlay._dcFm;
		var breadcrumb = overlay.querySelector('.dc-fm-breadcrumb');
		var parts = (cfg.path || '').split('/').filter(Boolean);
		var html = '<a href="#" data-path="">img/dc_filemenager</a>';
		var acc = '';
		parts.forEach(function (p) {
			acc += (acc ? '/' : '') + p;
			html += ' / <a href="#" data-path="' + escapeAttr(acc) + '">' + escapeHtml(p) + '</a>';
		});
		breadcrumb.innerHTML = html;
		breadcrumb.querySelectorAll('a').forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				cfg.path = a.getAttribute('data-path') || '';
				loadList(overlay);
			});
		});
	}

	function escapeAttr(s) {
		return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
	}
	function escapeHtml(s) {
		return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	function renderGrid(overlay) {
		var cfg = overlay._dcFm;
		var content = overlay.querySelector('.dc-fm-content');
		var search = (overlay.querySelector('.dc-fm-search') || {}).value || '';
		var q = search.toLowerCase().trim();
		var folders = (cfg.data && cfg.data.folders) || [];
		var images = (cfg.data && cfg.data.images) || [];
		if (q) {
			folders = folders.filter(function (f) { return f.name.toLowerCase().indexOf(q) >= 0; });
			images = images.filter(function (im) { return im.name.toLowerCase().indexOf(q) >= 0; });
		}
		var html = '<div class="dc-fm-grid">';
		folders.forEach(function (f) {
			html += '<div class="dc-fm-item dc-fm-item-folder" data-path="' + escapeAttr(f.path) + '">' +
				'<div class="dc-fm-thumb">&#128193;</div><div class="dc-fm-label">' + escapeHtml(f.name) + '</div></div>';
		});
		images.forEach(function (im) {
			var url = cfg.baseUrl.replace(/\/?$/, '/') + im.rel;
			html += '<div class="dc-fm-item dc-fm-item-image" data-url="' + escapeAttr(url) + '" data-rel="' + escapeAttr(im.rel) + '">' +
				'<div class="dc-fm-thumb"><img src="' + escapeAttr(url) + '" alt="" loading="lazy"></div>' +
				'<div class="dc-fm-label">' + escapeHtml(im.name) + '</div></div>';
		});
		html += '</div>';
		if (folders.length === 0 && images.length === 0) {
			html = '<div class="dc-fm-empty">Brak plików. Przeciągnij tutaj obrazy (jpg, png, webp, svg, max 3 MB).</div>';
		}
		content.innerHTML = html;
		content.querySelectorAll('.dc-fm-item-folder').forEach(function (el) {
			el.addEventListener('click', function () {
				cfg.path = el.getAttribute('data-path') || '';
				loadList(overlay);
			});
		});
		content.querySelectorAll('.dc-fm-item-image').forEach(function (el) {
			el.addEventListener('click', function () {
				var url = el.getAttribute('data-url');
				if (cfg.onSelect && url) cfg.onSelect(url);
				closeModal();
			});
		});
	}

	function bindModalEvents(overlay, fileInput) {
		var modal = overlay.querySelector('.dc-fm-modal');
		var body = overlay.querySelector('.dc-fm-body');
		var cfg = overlay._dcFm;

		overlay.querySelector('.dc-fm-close').onclick = closeModal;
		overlay.onclick = function (e) { if (e.target === overlay) closeModal(); };
		modal.onclick = function (e) { e.stopPropagation(); };

		overlay.querySelector('.dc-fm-btn-up').onclick = function () {
			var p = (cfg.path || '').split('/').filter(Boolean);
			p.pop();
			cfg.path = p.join('/');
			loadList(overlay);
		};
		overlay.querySelector('.dc-fm-btn-refresh').onclick = function () { loadList(overlay); };
		overlay.querySelector('.dc-fm-btn-upload').onclick = function () { fileInput.click(); };

		var searchEl = overlay.querySelector('.dc-fm-search');
		searchEl.oninput = searchEl.onkeyup = function () { renderGrid(overlay); };

		fileInput.onchange = function () {
			uploadFiles(overlay, fileInput.files);
			fileInput.value = '';
		};

		body.ondragover = function (e) { e.preventDefault(); body.classList.add('dc-fm-dragover'); };
		body.ondragleave = function (e) { e.preventDefault(); body.classList.remove('dc-fm-dragover'); };
		body.ondrop = function (e) {
			e.preventDefault();
			body.classList.remove('dc-fm-dragover');
			uploadFiles(overlay, e.dataTransfer.files);
		};
	}

	function uploadFiles(overlay, files) {
		var cfg = overlay._dcFm;
		if (!cfg || !cfg.apiUrl || !files || !files.length) return;
		var allowed = ALLOWED_EXT;
		var max = MAX_SIZE;
		var toSend = [];
		for (var i = 0; i < files.length; i++) {
			var f = files[i];
			var ext = (f.name.split('.').pop() || '').toLowerCase();
			if (allowed.indexOf(ext) < 0) continue;
			if (f.size > max) continue;
			toSend.push(f);
		}
		if (toSend.length === 0) return;
		var content = overlay.querySelector('.dc-fm-content');
		content.innerHTML = '<div class="dc-fm-loading">Wgrywanie...</div>';
		var idx = 0;
		function doNext() {
			if (idx >= toSend.length) {
				loadList(overlay);
				return;
			}
			var formData = new FormData();
			formData.append('action', 'upload');
			formData.append('path', cfg.path || '');
			formData.append('file', toSend[idx]);
			idx++;
			fetch(cfg.apiUrl, { method: 'POST', body: formData })
				.then(function (r) {
					return r.text().then(function (text) {
						try { return JSON.parse(text); } catch (e) { throw new Error(text || r.status); }
					});
				})
				.then(function (res) {
					if (res && res.ok) { doNext(); return; }
					content.innerHTML = '<div class="dc-fm-error">Błąd wgrywania: ' + (res && res.error ? escapeHtml(res.error) : 'nieznany') + '</div>';
				})
				.catch(function (err) {
					content.innerHTML = '<div class="dc-fm-error">Błąd wgrywania: ' + escapeHtml(err.message || 'połączenie') + '</div>';
				});
		}
		doNext();
	}

	// Delegacja: klik w .dc-fm-trigger lub .dc-browse-image wewnątrz .dc-filemanager
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.dc-fm-trigger') || e.target.closest('.dc-browse-image');
		if (!btn) return;
		var container = btn.closest('.dc-filemanager');
		if (!container) return;
		var input = container.querySelector('input[type="text"]') || container.querySelector('.dc-fm-input') || container.querySelector('.dc-box-image');
		var cfg = getConfig(container);
		if (!cfg.apiUrl) return;
		e.preventDefault();
		openModal(cfg.apiUrl, cfg.baseUrl, function (url) {
			if (input) {
				input.value = url;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
			var thumb = container.querySelector('.dc-fm-thumb-preview');
			if (thumb) {
				thumb.src = url;
				thumb.style.display = '';
			}
		});
	});
})();
