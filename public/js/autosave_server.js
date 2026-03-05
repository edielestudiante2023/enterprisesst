/**
 * Server-Side Autosave Engine para Inspecciones PWA
 *
 * Uso en cada form view:
 *   initAutosave({
 *       formId: 'extForm',
 *       storeUrl: '/inspecciones/extintores/store',
 *       updateUrlBase: '/inspecciones/extintores/update/',
 *       editUrlBase: '/inspecciones/extintores/edit/',
 *       recordId: <?= $inspeccion['id'] ?? 'null' ?>,
 *       isEdit: <?= $isEdit ? 'true' : 'false' ?>,
 *       storageKey: 'ext_draft_<?= $inspeccion['id'] ?? "new" ?>',
 *       detailRowSelector: '.extintor-row',   // solo N-ITEMS
 *       detailIdInputName: 'ext_id[]',        // solo N-ITEMS
 *       intervalSeconds: 60,
 *   });
 */
(function () {
    'use strict';

    window.initAutosave = function (cfg) {
        var form = document.getElementById(cfg.formId);
        if (!form) return;

        var isEdit = !!cfg.isEdit;
        var recordId = cfg.recordId || null;
        var interval = (cfg.intervalSeconds || 60) * 1000;
        var saving = false;
        var submitted = false; // true después de un submit manual → bloquea todo autosave posterior
        var pendingSubmitBtn = null;
        var statusEl = document.getElementById('autoSaveStatus');
        var debounceTimer = null;
        var intervalId = null;

        // ── Marcar file inputs como dirty cuando el usuario selecciona archivo ──
        form.addEventListener('change', function (e) {
            if (e.target.type === 'file' && e.target.files && e.target.files.length > 0) {
                e.target.setAttribute('data-dirty', '1');
            }
        });

        // ── Construir FormData selectivamente ──
        function buildFormData() {
            var fd = new FormData();
            var els = form.elements;

            for (var i = 0; i < els.length; i++) {
                var el = els[i];
                if (!el.name || el.disabled) continue;
                if (el.type === 'file' || el.type === 'submit') continue;
                if (el.type === 'radio' && !el.checked) continue;
                if (el.type === 'checkbox' && !el.checked) continue;
                fd.append(el.name, el.value);
            }

            // Solo incluir file inputs con data-dirty="1" (fotos nuevas)
            var fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach(function (input) {
                if (input.getAttribute('data-dirty') === '1' && input.files.length > 0) {
                    var row = cfg.detailRowSelector ? input.closest(cfg.detailRowSelector) : null;
                    if (row) {
                        var allRows = form.querySelectorAll(cfg.detailRowSelector);
                        var rowIdx = Array.from(allRows).indexOf(row);
                        var baseName = input.name.replace('[]', '');
                        fd.append(baseName + '[' + rowIdx + ']', input.files[0]);
                    } else {
                        fd.append(input.name, input.files[0]);
                    }
                }
            });

            // Nunca enviar finalizar en autosave
            fd.delete('finalizar');

            return fd;
        }

        // ── Verificar campos mínimos para auto-crear ──
        function hasMinFields() {
            if (typeof cfg.minFieldsCheck === 'function') return cfg.minFieldsCheck();
            var cliente = form.querySelector('[name="id_cliente"]');
            var fecha = form.querySelector('[name="fecha_inspeccion"]');
            return cliente && cliente.value && fecha && fecha.value;
        }

        // ── URL destino ──
        function getUrl() {
            return (isEdit && recordId)
                ? cfg.updateUrlBase + recordId
                : cfg.storeUrl;
        }

        // ── Mostrar estado ──
        function showStatus(icon, text, color) {
            if (!statusEl) return;
            statusEl.innerHTML = '<i class="fas ' + icon + '" style="color:' + color + '"></i> ' + text;
        }

        // ── Guardar en localStorage como fallback ──
        function saveToLocalStorage() {
            try {
                var data = { _savedAt: new Date().toISOString() };
                var els = form.elements;
                for (var i = 0; i < els.length; i++) {
                    var el = els[i];
                    if (!el.name || el.type === 'file' || el.type === 'submit') continue;
                    if (el.type === 'radio' && !el.checked) continue;
                    data[el.name] = el.value;
                }
                localStorage.setItem(cfg.storageKey, JSON.stringify(data));
            } catch (e) { /* quota exceeded, etc */ }
        }

        // ── Core: ejecutar autosave ──
        function doAutosave() {
            if (saving || submitted) return;

            if (!hasMinFields()) {
                saveToLocalStorage();
                return;
            }

            saving = true;
            showStatus('fa-spinner fa-spin', 'Guardando...', '#999');

            var submits = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submits.forEach(function (btn) { btn.disabled = true; });

            var fd = buildFormData();
            var url = getUrl();

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Autosave': '1',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
                credentials: 'same-origin'
            })
            .then(function (resp) {
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                return resp.json();
            })
            .then(function (data) {
                if (submitted) return;
                if (data.success) {
                    showStatus('fa-cloud-upload-alt', 'Guardado ' + (data.saved_at || new Date().toLocaleTimeString()), '#28a745');

                    // Transición create → edit
                    if (!isEdit && data.id) {
                        recordId = data.id;
                        isEdit = true;
                        form.action = cfg.updateUrlBase + data.id;
                        if (cfg.editUrlBase && window.history && window.history.replaceState) {
                            window.history.replaceState(null, '', cfg.editUrlBase + data.id);
                        }
                        localStorage.removeItem(cfg.storageKey);
                        cfg.storageKey = cfg.storageKey.replace('_new', '_' + data.id);
                    }

                    // Actualizar hidden IDs de detail rows (N-ITEMS)
                    if (data.detail_ids && cfg.detailRowSelector && cfg.detailIdInputName) {
                        var rows = form.querySelectorAll(cfg.detailRowSelector);
                        rows.forEach(function (row, i) {
                            var idInput = row.querySelector('input[name="' + cfg.detailIdInputName + '"]');
                            if (idInput && data.detail_ids[i] !== undefined) {
                                idInput.value = data.detail_ids[i];
                            }
                        });
                    }

                    form.querySelectorAll('input[type="file"][data-dirty="1"]').forEach(function (input) {
                        input.removeAttribute('data-dirty');
                        input.value = '';
                    });

                } else {
                    showStatus('fa-exclamation-triangle', 'Error: ' + (data.message || ''), '#dc3545');
                }
            })
            .catch(function () {
                if (!submitted) {
                    showStatus('fa-exclamation-triangle', 'Sin conexión — guardado local', '#dc3545');
                    saveToLocalStorage();
                }
            })
            .finally(function () {
                saving = false;
                submits.forEach(function (btn) { btn.disabled = false; });

                if (pendingSubmitBtn) {
                    var btn = pendingSubmitBtn;
                    pendingSubmitBtn = null;
                    btn.click();
                }
            });
        }

        // ── Interceptar submit manual ──
        form.addEventListener('submit', function (e) {
            submitted = true;
            clearTimeout(debounceTimer);
            if (intervalId) { clearInterval(intervalId); intervalId = null; }
            localStorage.removeItem(cfg.storageKey);

            if (saving) {
                e.preventDefault();
                var activeBtn = document.activeElement;
                if (activeBtn && (activeBtn.type === 'submit' || activeBtn.tagName === 'BUTTON')) {
                    pendingSubmitBtn = activeBtn;
                }
                showStatus('fa-hourglass-half', 'Esperando guardado...', '#ffc107');
                return false;
            }
        });

        // ── Timer principal: cada 60s ──
        intervalId = setInterval(doAutosave, interval);

        // ── Debounce: guardar 5s después del último input ──
        form.addEventListener('input', function () {
            if (submitted) return;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doAutosave, 5000);
        });

        // ── Select2: guardar 3s después de cambio ──
        if (window.$ && $.fn.select2) {
            $(form).find('select').on('change', function () {
                if (submitted) return;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(doAutosave, 3000);
            });
        }

        // ── Interceptar form.submit() programático ──
        var nativeFormSubmit = form.submit.bind(form);
        form.submit = function () {
            submitted = true;
            clearTimeout(debounceTimer);
            if (intervalId) { clearInterval(intervalId); intervalId = null; }
            nativeFormSubmit();
        };

        // ── Estado inicial ──
        showStatus('fa-cloud', 'Autoguardado activado', '#999');
    };
})();
