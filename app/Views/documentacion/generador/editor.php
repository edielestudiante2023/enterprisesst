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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-pencil-square me-2"></i>Editor de Documento
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <code><?= esc($documento['codigo']) ?></code> - <?= esc($documento['nombre']) ?>
                </span>
                <a class="nav-link" href="/documentacion/ver/<?= $documento['id_documento'] ?>">
                    <i class="bi bi-eye me-1"></i>Vista Previa
                </a>
                <a class="nav-link" href="/documentacion/<?= $cliente['id_cliente'] ?>">
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

                    <!-- Acciones -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <a href="/documentacion/vista-previa/<?= $documento['id_documento'] ?>"
                               class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-eye me-1"></i>Vista Previa
                            </a>
                            <?php if (($progreso['pendientes'] ?? 1) === 0): ?>
                                <a href="/documentacion/finalizar/<?= $documento['id_documento'] ?>"
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
        // Guardar sección
        document.querySelectorAll('.form-seccion').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const idSeccion = this.dataset.idSeccion;
                const contenido = this.querySelector('textarea[name="contenido"]').value;

                fetch('/documentacion/guardar-seccion', {
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
                    } else {
                        alert(data.message || 'Error al guardar');
                    }
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

            fetch('/documentacion/generar-ia', {
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
                    alert(data.message || 'Error al generar contenido con IA');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al generar contenido');
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
            const idSeccion = this.dataset.idSeccion;

            fetch('/documentacion/aprobar-seccion', {
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
                    location.reload();
                } else {
                    alert(data.message || 'Error al aprobar');
                }
            });
        }
    </script>
</body>
</html>
