<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor - <?= esc($documento['codigo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .seccion-card { transition: all 0.2s; }
        .seccion-card.editing { border-color: #3B82F6; }
        .seccion-card.completed { border-left: 4px solid #10B981; }
        .seccion-card.pending { border-left: 4px solid #F59E0B; }
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
        }
        .editor-textarea {
            min-height: 300px;
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
        }
        .sidebar-sticky {
            position: sticky;
            top: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="toastNotificacion" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi me-2" id="toastIcono"></i>
                <strong class="me-auto" id="toastTitulo">Notificación</strong>
                <small id="toastTiempo">Ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMensaje">
                Mensaje aquí
            </div>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-pencil-square me-2"></i>Editor de Documento
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <code><?= esc($documento['codigo']) ?></code> - <?= esc($documento['nombre']) ?>
                </span>
                <a class="nav-link" href="<?= base_url('documentacion/ver/' . $documento['id_documento']) ?>">
                    <i class="bi bi-eye me-1"></i>Vista Previa
                </a>
                <a class="nav-link" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Panel lateral -->
            <div class="col-md-3">
                <div class="sidebar-sticky">
                    <!-- Acciones Masivas (al inicio) -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-lightning me-1"></i>Acciones Masivas</h6>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-primary w-100 mb-2" id="btnGenerarTodoIA">
                                <i class="bi bi-robot me-1"></i>Generar Todo con IA
                            </button>
                            <button type="button" class="btn btn-outline-success w-100 mb-2" id="btnGuardarTodos">
                                <i class="bi bi-save-fill me-1"></i>Guardar Todos
                            </button>
                            <button type="button" class="btn btn-success w-100 mb-2" id="btnAprobarTodos">
                                <i class="bi bi-check-all me-1"></i>Aprobar Todos
                            </button>
                            <div id="progresoMasivo" class="d-none">
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         id="barraProgresoMasivo" style="width: 0%">0%</div>
                                </div>
                                <small class="text-muted" id="textoProgresoMasivo">Procesando...</small>
                            </div>
                        </div>
                    </div>

                    <!-- Progreso -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">Progreso del Documento</h6>
                            <div class="progress progress-bar-custom mb-2">
                                <div class="progress-bar bg-success" style="width: <?= $progreso['porcentaje'] ?? 0 ?>%"></div>
                            </div>
                            <small class="text-muted">
                                <?= $progreso['completadas'] ?? 0 ?> de <?= $progreso['total'] ?? 0 ?> secciones
                            </small>
                        </div>
                    </div>

                    <!-- Lista de secciones -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Secciones</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (!empty($secciones)): ?>
                                <?php foreach ($secciones as $sec): ?>
                                    <a href="#seccion-<?= $sec['id_seccion'] ?>"
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span>
                                            <?= $sec['numero_seccion'] ?>. <?= esc($sec['nombre_seccion']) ?>
                                        </span>
                                        <?php if ($sec['aprobado']): ?>
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        <?php elseif (!empty($sec['contenido'])): ?>
                                            <i class="bi bi-circle-fill text-warning" style="font-size: 0.5rem;"></i>
                                        <?php else: ?>
                                            <i class="bi bi-circle text-muted" style="font-size: 0.5rem;"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acciones de navegación -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <a href="<?= base_url('documentacion/vista-previa/' . $documento['id_documento']) ?>"
                               class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-eye me-1"></i>Vista Previa
                            </a>
                            <?php if (($progreso['pendientes'] ?? 1) === 0): ?>
                                <a href="<?= base_url('documentacion/finalizar/' . $documento['id_documento']) ?>"
                                   class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-1"></i>Finalizar Documento
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-check-lg me-1"></i>Completar secciones primero
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9">
                <?php if (empty($secciones)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Este documento no tiene secciones definidas</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($secciones as $seccion): ?>
                        <div class="card border-0 shadow-sm mb-4 seccion-card <?= $seccion['aprobado'] ? 'completed' : 'pending' ?>"
                             id="seccion-<?= $seccion['id_seccion'] ?>">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?= $seccion['numero_seccion'] ?>. <?= esc($seccion['nombre_seccion']) ?>
                                </h5>
                                <div>
                                    <?php if ($seccion['aprobado']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check me-1"></i>Aprobada</span>
                                    <?php elseif (!empty($seccion['contenido'])): ?>
                                        <span class="badge bg-warning text-dark">En edición</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pendiente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <form class="form-seccion" data-id-seccion="<?= $seccion['id_seccion'] ?>">
                                    <div class="mb-3">
                                        <textarea class="form-control editor-textarea"
                                                  name="contenido"
                                                  placeholder="Escriba el contenido de esta sección..."><?= esc($seccion['contenido'] ?? '') ?></textarea>
                                    </div>

                                    <?php if (!empty($prompts[$seccion['numero_seccion']])): ?>
                                        <div class="alert alert-light border mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-lightbulb me-1"></i>
                                                <strong>Sugerencia:</strong> <?= esc($prompts[$seccion['numero_seccion']]) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Campo de contexto adicional para regenerar con IA -->
                                    <div class="mb-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-chat-dots"></i>
                                            </span>
                                            <input type="text" class="form-control contexto-adicional"
                                                   placeholder="Contexto adicional para IA (ej: 'enfocarse en riesgo químico', 'incluir trabajo en alturas')"
                                                   data-id-seccion="<?= $seccion['id_seccion'] ?>">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-generar-ia"
                                                    data-id-seccion="<?= $seccion['id_seccion'] ?>">
                                                <i class="bi bi-robot me-1"></i>Generar con IA
                                            </button>
                                            <?php if (!empty($seccion['contenido'])): ?>
                                                <button type="button" class="btn btn-outline-warning btn-sm btn-regenerar-ia"
                                                        data-id-seccion="<?= $seccion['id_seccion'] ?>">
                                                    <i class="bi bi-arrow-clockwise me-1"></i>Regenerar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-save me-1"></i>Guardar
                                            </button>
                                            <?php if (!$seccion['aprobado'] && !empty($seccion['contenido'])): ?>
                                                <button type="button" class="btn btn-success btn-sm btn-aprobar"
                                                        data-id-seccion="<?= $seccion['id_seccion'] ?>">
                                                    <i class="bi bi-check-lg me-1"></i>Aprobar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ==========================================
        // SISTEMA DE TOAST NOTIFICATIONS
        // ==========================================
        function mostrarToast(tipo, titulo, mensaje) {
            const toastEl = document.getElementById('toastNotificacion');
            const toastTitulo = document.getElementById('toastTitulo');
            const toastMensaje = document.getElementById('toastMensaje');
            const toastIcono = document.getElementById('toastIcono');
            const toastHeader = toastEl.querySelector('.toast-header');

            // Limpiar clases anteriores
            toastHeader.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white');
            toastIcono.classList.remove('bi-check-circle-fill', 'bi-x-circle-fill', 'bi-exclamation-triangle-fill', 'bi-info-circle-fill', 'text-success', 'text-danger', 'text-warning', 'text-info');

            // Configurar según tipo
            switch(tipo) {
                case 'success':
                    toastHeader.classList.add('bg-success', 'text-white');
                    toastIcono.classList.add('bi-check-circle-fill', 'text-white');
                    break;
                case 'error':
                    toastHeader.classList.add('bg-danger', 'text-white');
                    toastIcono.classList.add('bi-x-circle-fill', 'text-white');
                    break;
                case 'warning':
                    toastHeader.classList.add('bg-warning');
                    toastIcono.classList.add('bi-exclamation-triangle-fill', 'text-dark');
                    break;
                case 'info':
                    toastHeader.classList.add('bg-info', 'text-white');
                    toastIcono.classList.add('bi-info-circle-fill', 'text-white');
                    break;
            }

            toastTitulo.textContent = titulo;
            toastMensaje.textContent = mensaje;

            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        }

        // ==========================================
        // GUARDAR SECCIÓN INDIVIDUAL
        // ==========================================
        // Guardar sección
        document.querySelectorAll('.form-seccion').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const idSeccion = this.dataset.idSeccion;
                const contenido = this.querySelector('textarea[name="contenido"]').value;

                fetch('<?= base_url('documentacion/guardar-seccion') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id_seccion=${idSeccion}&contenido=${encodeURIComponent(contenido)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Feedback visual
                        const card = this.closest('.seccion-card');
                        card.classList.add('border-success');
                        setTimeout(() => card.classList.remove('border-success'), 2000);

                        // Toast de éxito
                        const nombreSeccion = card.querySelector('.card-header h5').textContent.trim();
                        mostrarToast('success', 'Guardado exitoso', `La sección "${nombreSeccion}" se guardó en la base de datos.`);
                    } else {
                        mostrarToast('error', 'Error al guardar', data.message || 'No se pudo guardar la sección en la base de datos.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarToast('error', 'Error de conexión', 'No se pudo conectar con el servidor para guardar.');
                });
            });
        });

        // Aprobar sección
        document.querySelectorAll('.btn-aprobar').forEach(btn => {
            btn.addEventListener('click', aprobarSeccion);
        });

        // Función para generar con IA
        function generarConIA(btn, idSeccion, esRegeneracion = false) {
            const card = btn.closest('.seccion-card');
            const textarea = card.querySelector('textarea');
            const contextoInput = card.querySelector('.contexto-adicional');
            const contextoAdicional = contextoInput ? contextoInput.value : '';

            const textoOriginal = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

            // Deshabilitar otros botones de la sección
            card.querySelectorAll('button').forEach(b => b.disabled = true);

            fetch('<?= base_url('documentacion/generar-ia') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id_seccion=${idSeccion}&contexto_adicional=${encodeURIComponent(contextoAdicional)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Insertar el contenido generado en el textarea
                    textarea.value = data.contenido;

                    // Feedback visual
                    card.classList.add('border-success');
                    card.classList.remove('pending');
                    setTimeout(() => card.classList.remove('border-success'), 3000);

                    // Limpiar contexto adicional
                    if (contextoInput) contextoInput.value = '';

                    // Toast de éxito
                    const nombreSeccion = card.querySelector('.card-header h5').textContent.trim();
                    mostrarToast('success', 'IA generó contenido', `Contenido generado para "${nombreSeccion}". Recuerda guardar los cambios.`);

                    // Mostrar tokens usados
                    if (data.tokens_usados) {
                        console.log('Tokens usados:', data.tokens_usados);
                    }

                    // Mostrar botón de aprobar si no existe
                    const btnContainer = card.querySelector('.d-flex.justify-content-between > div:last-child');
                    if (!card.querySelector('.btn-aprobar')) {
                        const btnAprobar = document.createElement('button');
                        btnAprobar.type = 'button';
                        btnAprobar.className = 'btn btn-success btn-sm btn-aprobar';
                        btnAprobar.dataset.idSeccion = idSeccion;
                        btnAprobar.innerHTML = '<i class="bi bi-check-lg me-1"></i>Aprobar';
                        btnAprobar.addEventListener('click', aprobarSeccion);
                        btnContainer.appendChild(btnAprobar);
                    }

                    // Mostrar botón de regenerar si no existe
                    const btnGenContainer = card.querySelector('.d-flex.justify-content-between > div:first-child');
                    if (!card.querySelector('.btn-regenerar-ia')) {
                        const btnRegen = document.createElement('button');
                        btnRegen.type = 'button';
                        btnRegen.className = 'btn btn-outline-warning btn-sm btn-regenerar-ia ms-1';
                        btnRegen.dataset.idSeccion = idSeccion;
                        btnRegen.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Regenerar';
                        btnRegen.addEventListener('click', function() {
                            generarConIA(this, idSeccion, true);
                        });
                        btnGenContainer.appendChild(btnRegen);
                    }
                } else {
                    mostrarToast('error', 'Error de IA', data.message || 'No se pudo generar contenido con IA.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast('error', 'Error de conexión', 'No se pudo conectar con el servidor para generar contenido.');
            })
            .finally(() => {
                // Rehabilitar botones
                card.querySelectorAll('button').forEach(b => b.disabled = false);
                btn.innerHTML = textoOriginal;
            });
        }

        // Generar con IA
        document.querySelectorAll('.btn-generar-ia').forEach(btn => {
            btn.addEventListener('click', function() {
                const idSeccion = this.dataset.idSeccion;
                generarConIA(this, idSeccion, false);
            });
        });

        // Regenerar con IA
        document.querySelectorAll('.btn-regenerar-ia').forEach(btn => {
            btn.addEventListener('click', function() {
                const idSeccion = this.dataset.idSeccion;
                generarConIA(this, idSeccion, true);
            });
        });

        // Función para aprobar sección
        function aprobarSeccion() {
            const btn = this;
            const idSeccion = this.dataset.idSeccion;
            const card = btn.closest('.seccion-card');
            const nombreSeccion = card.querySelector('.card-header h5').textContent.trim();

            // Deshabilitar botón mientras procesa
            btn.disabled = true;
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Aprobando...';

            fetch('<?= base_url('documentacion/aprobar-seccion') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id_seccion=${idSeccion}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('success', 'Aprobación exitosa', `La sección "${nombreSeccion}" fue aprobada y guardada en la base de datos.`);
                    // Recargar después de mostrar el toast
                    setTimeout(() => location.reload(), 1500);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = textoOriginal;
                    mostrarToast('error', 'Error al aprobar', data.message || 'No se pudo aprobar la sección en la base de datos.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
                mostrarToast('error', 'Error de conexión', 'No se pudo conectar con el servidor para aprobar.');
            });
        }

        // ==========================================
        // GENERAR TODO CON IA
        // ==========================================
        document.getElementById('btnGenerarTodoIA').addEventListener('click', async function() {
            const secciones = document.querySelectorAll('.seccion-card');
            const totalSecciones = secciones.length;

            if (totalSecciones === 0) {
                alert('No hay secciones para generar');
                return;
            }

            // Confirmar acción
            if (!confirm(`¿Generar contenido con IA para las ${totalSecciones} secciones?\n\nEsto puede tardar varios minutos. Las secciones que ya tienen contenido serán regeneradas.`)) {
                return;
            }

            // Mostrar progreso
            const progresoDiv = document.getElementById('progresoMasivo');
            const barra = document.getElementById('barraProgresoMasivo');
            const texto = document.getElementById('textoProgresoMasivo');
            progresoDiv.classList.remove('d-none');

            // Deshabilitar botones
            this.disabled = true;
            document.getElementById('btnGuardarTodos').disabled = true;

            let completadas = 0;
            let errores = 0;

            // Procesar secciones secuencialmente (para no sobrecargar la API)
            for (const card of secciones) {
                const idSeccion = card.id.replace('seccion-', '');
                const nombreSeccion = card.querySelector('.card-header h5').textContent.trim();
                const textarea = card.querySelector('textarea');
                const contextoInput = card.querySelector('.contexto-adicional');
                const contextoAdicional = contextoInput ? contextoInput.value : '';

                texto.textContent = `Generando: ${nombreSeccion}...`;

                try {
                    const response = await fetch('<?= base_url('documentacion/generar-ia') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_seccion=${idSeccion}&contexto_adicional=${encodeURIComponent(contextoAdicional)}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        textarea.value = data.contenido;
                        card.classList.add('border-success');
                        card.classList.remove('pending');
                        setTimeout(() => card.classList.remove('border-success'), 1000);

                        // Limpiar contexto
                        if (contextoInput) contextoInput.value = '';
                    } else {
                        errores++;
                        card.classList.add('border-danger');
                        console.error(`Error en sección ${nombreSeccion}:`, data.message);
                    }
                } catch (error) {
                    errores++;
                    card.classList.add('border-danger');
                    console.error(`Error de conexión en sección ${nombreSeccion}:`, error);
                }

                completadas++;
                const porcentaje = Math.round((completadas / totalSecciones) * 100);
                barra.style.width = `${porcentaje}%`;
                barra.textContent = `${porcentaje}%`;
            }

            // Finalizar
            texto.textContent = errores > 0
                ? `Completado con ${errores} errores. ${completadas - errores} de ${totalSecciones} secciones generadas.`
                : `¡Completado! ${completadas} secciones generadas exitosamente.`;

            barra.classList.remove('progress-bar-animated');
            barra.classList.add(errores > 0 ? 'bg-warning' : 'bg-success');

            // Toast de resultado
            if (errores > 0) {
                mostrarToast('warning', 'Generación parcial', `Se generaron ${completadas - errores} de ${totalSecciones} secciones. ${errores} errores.`);
            } else {
                mostrarToast('success', 'Generación completa', `Las ${completadas} secciones fueron generadas con IA. Recuerda guardar los cambios.`);
            }

            // Rehabilitar botones
            this.disabled = false;
            document.getElementById('btnGuardarTodos').disabled = false;

            // Recargar después de 2 segundos para actualizar el estado
            setTimeout(() => {
                if (confirm('¿Desea recargar la página para ver los cambios actualizados?')) {
                    location.reload();
                }
            }, 2000);
        });

        // ==========================================
        // GUARDAR TODOS
        // ==========================================
        document.getElementById('btnGuardarTodos').addEventListener('click', async function() {
            const forms = document.querySelectorAll('.form-seccion');
            const totalForms = forms.length;

            if (totalForms === 0) {
                alert('No hay secciones para guardar');
                return;
            }

            // Mostrar progreso
            const progresoDiv = document.getElementById('progresoMasivo');
            const barra = document.getElementById('barraProgresoMasivo');
            const texto = document.getElementById('textoProgresoMasivo');
            progresoDiv.classList.remove('d-none');
            barra.classList.remove('bg-success', 'bg-warning');
            barra.classList.add('progress-bar-animated');

            // Deshabilitar botones
            this.disabled = true;
            document.getElementById('btnGenerarTodoIA').disabled = true;

            let guardadas = 0;
            let errores = 0;

            texto.textContent = 'Guardando todas las secciones...';

            // Guardar todas en paralelo
            const promesas = Array.from(forms).map(async (form) => {
                const idSeccion = form.dataset.idSeccion;
                const contenido = form.querySelector('textarea[name="contenido"]').value;
                const card = form.closest('.seccion-card');

                try {
                    const response = await fetch('<?= base_url('documentacion/guardar-seccion') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_seccion=${idSeccion}&contenido=${encodeURIComponent(contenido)}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        guardadas++;
                        card.classList.add('border-success');
                        setTimeout(() => card.classList.remove('border-success'), 2000);
                    } else {
                        errores++;
                        card.classList.add('border-danger');
                    }
                } catch (error) {
                    errores++;
                    card.classList.add('border-danger');
                }

                // Actualizar progreso
                const completadas = guardadas + errores;
                const porcentaje = Math.round((completadas / totalForms) * 100);
                barra.style.width = `${porcentaje}%`;
                barra.textContent = `${porcentaje}%`;
            });

            await Promise.all(promesas);

            // Finalizar
            texto.textContent = errores > 0
                ? `Guardado con ${errores} errores. ${guardadas} de ${totalForms} secciones guardadas.`
                : `¡Completado! ${guardadas} secciones guardadas exitosamente.`;

            barra.classList.remove('progress-bar-animated');
            barra.classList.add(errores > 0 ? 'bg-warning' : 'bg-success');

            // Toast de resultado
            if (errores > 0) {
                mostrarToast('warning', 'Guardado parcial', `Se guardaron ${guardadas} de ${totalForms} secciones. ${errores} errores encontrados.`);
            } else {
                mostrarToast('success', 'Todo guardado', `Las ${guardadas} secciones fueron guardadas exitosamente en la base de datos.`);
            }

            // Rehabilitar botones
            this.disabled = false;
            document.getElementById('btnGenerarTodoIA').disabled = false;

            // Ocultar progreso después de 3 segundos
            setTimeout(() => {
                progresoDiv.classList.add('d-none');
                barra.style.width = '0%';
                barra.textContent = '0%';
            }, 3000);
        });

        // ==========================================
        // APROBAR TODOS
        // ==========================================
        document.getElementById('btnAprobarTodos').addEventListener('click', async function() {
            const secciones = document.querySelectorAll('.seccion-card');
            const totalSecciones = secciones.length;

            if (totalSecciones === 0) {
                alert('No hay secciones para aprobar');
                return;
            }

            // Verificar que todas tengan contenido
            let seccionesSinContenido = 0;
            secciones.forEach(card => {
                const textarea = card.querySelector('textarea');
                if (!textarea.value.trim()) {
                    seccionesSinContenido++;
                }
            });

            if (seccionesSinContenido > 0) {
                if (!confirm(`Hay ${seccionesSinContenido} secciones sin contenido. ¿Desea aprobar solo las que tienen contenido?`)) {
                    return;
                }
            }

            // Confirmar acción
            if (!confirm(`¿Aprobar todas las secciones con contenido?`)) {
                return;
            }

            // Mostrar progreso
            const progresoDiv = document.getElementById('progresoMasivo');
            const barra = document.getElementById('barraProgresoMasivo');
            const texto = document.getElementById('textoProgresoMasivo');
            progresoDiv.classList.remove('d-none');
            barra.classList.remove('bg-success', 'bg-warning');
            barra.classList.add('progress-bar-animated');

            // Deshabilitar botones
            this.disabled = true;
            document.getElementById('btnGenerarTodoIA').disabled = true;
            document.getElementById('btnGuardarTodos').disabled = true;

            let aprobadas = 0;
            let errores = 0;
            let omitidas = 0;

            texto.textContent = 'Aprobando secciones...';

            // Aprobar todas en paralelo
            const promesas = Array.from(secciones).map(async (card) => {
                const idSeccion = card.id.replace('seccion-', '');
                const textarea = card.querySelector('textarea');

                // Omitir secciones sin contenido
                if (!textarea.value.trim()) {
                    omitidas++;
                    return;
                }

                try {
                    const response = await fetch('<?= base_url('documentacion/aprobar-seccion') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_seccion=${idSeccion}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        aprobadas++;
                        card.classList.add('border-success', 'completed');
                        card.classList.remove('pending');
                    } else {
                        errores++;
                        card.classList.add('border-danger');
                    }
                } catch (error) {
                    errores++;
                    card.classList.add('border-danger');
                }

                // Actualizar progreso
                const procesadas = aprobadas + errores + omitidas;
                const porcentaje = Math.round((procesadas / totalSecciones) * 100);
                barra.style.width = `${porcentaje}%`;
                barra.textContent = `${porcentaje}%`;
            });

            await Promise.all(promesas);

            // Finalizar
            let mensaje = `¡Completado! ${aprobadas} secciones aprobadas.`;
            if (omitidas > 0) mensaje += ` ${omitidas} omitidas (sin contenido).`;
            if (errores > 0) mensaje += ` ${errores} errores.`;
            texto.textContent = mensaje;

            barra.classList.remove('progress-bar-animated');
            barra.classList.add(errores > 0 ? 'bg-warning' : 'bg-success');

            // Toast de resultado
            if (errores > 0) {
                mostrarToast('warning', 'Aprobación parcial', `Se aprobaron ${aprobadas} secciones. ${errores} errores. ${omitidas} omitidas.`);
            } else if (omitidas > 0) {
                mostrarToast('info', 'Aprobación completada', `${aprobadas} secciones aprobadas. ${omitidas} omitidas (sin contenido).`);
            } else {
                mostrarToast('success', 'Todo aprobado', `Las ${aprobadas} secciones fueron aprobadas y guardadas en la base de datos.`);
            }

            // Rehabilitar botones
            this.disabled = false;
            document.getElementById('btnGenerarTodoIA').disabled = false;
            document.getElementById('btnGuardarTodos').disabled = false;

            // Recargar después de 2 segundos
            setTimeout(() => {
                location.reload();
            }, 2000);
        });
    </script>
</body>
</html>
