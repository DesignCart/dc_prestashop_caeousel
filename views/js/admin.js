(function () {
  'use strict';

  function run() {
    var addBtn = document.getElementById('dc-add-image-btn');
    var modal = document.getElementById('dc-carousel-image-modal');
    var modalTitle = document.getElementById('dc-carousel-modal-title');
    var formInModal = modal ? modal.querySelector('form.dc-filemanager') : null;

    // Otwórz zakładkę Obrazy gdy w URL jest tab=images (np. po zapisie)
    var search = window.location.search || '';
    if (search.indexOf('tab=images') !== -1) {
      var tabLink = document.querySelector('a[href="#dc-tab-images"]');
      if (tabLink && typeof jQuery !== 'undefined' && jQuery.fn.tab) {
        jQuery(tabLink).tab('show');
      }
    }

    // Upewnij się, że po odświeżeniu nie ma zostawionego tła modala
    if (typeof jQuery !== 'undefined') {
      jQuery('body').removeClass('modal-open');
      jQuery('.modal-backdrop').remove();
    }

    function resetImageForm() {
      if (!formInModal) return;
      var idInput = formInModal.querySelector('#dc_image_id');
      var pathInput = formInModal.querySelector('#dc_image_path');
      var linkInput = formInModal.querySelector('#dc_image_link');
      var thumb = formInModal.querySelector('.dc-fm-thumb-preview');
      if (idInput) idInput.value = '';
      if (pathInput) pathInput.value = '';
      if (linkInput) linkInput.value = '';
      if (thumb) {
        thumb.src = '';
        thumb.style.display = 'none';
      }
      formInModal.querySelectorAll('[id^="dc_image_title_"]').forEach(function (el) { el.value = ''; });
      formInModal.querySelectorAll('[id^="dc_image_description_"]').forEach(function (el) { el.value = ''; });
    }

    // Dodaj obraz – otwórz pusty modal
    if (addBtn && modal) {
      addBtn.addEventListener('click', function (e) {
        e.preventDefault();
        resetImageForm();
        if (modalTitle) modalTitle.textContent = modalTitle.getAttribute('data-title-add') || 'Dodaj obraz';
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
          jQuery(modal).modal('show');
        }
      });
    }

    // Edytuj obraz – pobierz dane przez AJAX, wypełnij formularz, otwórz modal (bez przeładowania)
    var configureUrlBase = formInModal ? formInModal.getAttribute('data-dc-configure-url') : '';
    var imagesTable = document.getElementById('dc-carousel-images-table');
    if (configureUrlBase && modal && imagesTable) {
      imagesTable.addEventListener('click', function (e) {
        var btn = e.target.closest('.dc-edit-image');
        if (!btn) return;
        e.preventDefault();
        var id = btn.getAttribute('data-id');
        if (!id) return;

        var req = new XMLHttpRequest();
        req.open('GET', configureUrlBase + id, true);
        req.onreadystatechange = function () {
          if (req.readyState !== 4) return;
          var slide;
          try {
            slide = JSON.parse(req.responseText);
          } catch (err) {
            return;
          }
          if (slide.error || !slide) return;

          var idInput = document.getElementById('dc_image_id');
          var pathInput = document.getElementById('dc_image_path');
          var linkInput = document.getElementById('dc_image_link');
          var thumb = formInModal ? formInModal.querySelector('.dc-fm-thumb-preview') : null;
          if (idInput) idInput.value = slide.id_dc_carousel_slide || '';
          if (pathInput) pathInput.value = slide.image || '';
          if (linkInput) linkInput.value = slide.link || '';
          if (thumb) {
            thumb.src = slide.image || '';
            thumb.style.display = slide.image ? '' : 'none';
          }

          var titles = slide.titles || {};
          var descriptions = slide.descriptions || {};
          formInModal.querySelectorAll('[id^="dc_image_title_"]').forEach(function (el) {
            var langId = el.id.replace('dc_image_title_', '');
            el.value = titles[langId] != null ? titles[langId] : (titles[parseInt(langId, 10)] != null ? titles[parseInt(langId, 10)] : '');
          });
          formInModal.querySelectorAll('[id^="dc_image_description_"]').forEach(function (el) {
            var langId = el.id.replace('dc_image_description_', '');
            el.value = descriptions[langId] != null ? descriptions[langId] : (descriptions[parseInt(langId, 10)] != null ? descriptions[parseInt(langId, 10)] : '');
          });

          if (modalTitle) modalTitle.textContent = modalTitle.getAttribute('data-title-edit') || 'Edytuj obraz';
          if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            jQuery(modal).modal('show');
          }
        };
        req.send();
      });
    }

    // Filemanager: konfiguracja z formularza w modalu
    if (formInModal) {
      var api = formInModal.getAttribute('data-dc-filemanager-api');
      var base = formInModal.getAttribute('data-dc-filemanager-base');
      window.DC_FILEMANAGER = window.DC_FILEMANAGER || {};
      if (api) window.DC_FILEMANAGER.apiUrl = api;
      if (base) window.DC_FILEMANAGER.baseUrl = base;
    }

    // Drag & drop kolejności wierszy
    var tbody = document.getElementById('dc-carousel-images-tbody');
    if (tbody) {
      var dragging = null;
      tbody.querySelectorAll('.dc-sortable-row').forEach(function (row) {
        row.setAttribute('draggable', 'true');
        row.addEventListener('dragstart', function () {
          dragging = row;
          row.classList.add('dc-dragging');
        });
        row.addEventListener('dragend', function () {
          row.classList.remove('dc-dragging');
          dragging = null;
        });
        row.addEventListener('dragover', function (e) {
          e.preventDefault();
          var target = row;
          if (!dragging || dragging === target) return;
          var rect = target.getBoundingClientRect();
          var next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
          tbody.insertBefore(dragging, next ? target.nextSibling : target);
        });
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
