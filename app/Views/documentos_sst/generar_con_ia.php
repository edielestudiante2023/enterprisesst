<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .seccion-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        .seccion-card.generada {
            border-left-color: #198754;
        }
        .seccion-card.aprobada {
            border-left-color: #0d6efd;
            background-color: #f8f9ff;
        }
        .seccion-numero {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .btn-generar-ia {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-generar-ia:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
            color: white;
        }
        .contenido-seccion {
            min-height: 150px;
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        .progress-general {
            height: 8px;
        }
        .sidebar {
            position: sticky;
            top: 20px;
        }
        .nav-secciones .nav-link {
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 4px;
            color: #495057;
        }
        .nav-secciones .nav-link:hover {
            background-color: #e9ecef;
        }
        .nav-secciones .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-secciones .nav-link .bi-check-circle-fill {
            color: #198754;
        }
        .nav-secciones .nav-link.active .bi-check-circle-fill {
            color: #90EE90;
        }
        .btn-toggle-contexto {
            background-color: #f8f9fa;
            border: 1px dashed #6c757d;
        }
        .btn-toggle-contexto:hover {
            background-color: #e9ecef;
        }
        .contexto-ia-seccion {
            font-size: 0.9rem;
            border: 1px dashed #667eea;
            background-color: #f8f9ff;
        }
        .contexto-ia-seccion:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        /* Toast styling */
        .toast-container {
            z-index: 9999;
        }
        .toast {
            min-width: 300px;
        }
        /* Boton Vista Previa deshabilitado */
        #btnVistaPrevia.disabled {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }
        #btnVistaPrevia.disabled small {
            opacity: 0.8;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastNotificacion" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" id="toastHeader">
                <i class="bi me-2" id="toastIcono"></i>
                <strong class="me-auto" id="toastTitulo">Notificacion</strong>
                <small id="toastTiempo">Ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMensaje">
                Mensaje aqui
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-dark text-white py-3">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm me-3">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <span class="fs-5"><?= esc($tipoDoc['nombre']) ?></span>
                    <?php if (!empty($documento['codigo'])): ?>
                    <span class="badge bg-warning text-dark ms-2"><?= esc($documento['codigo']) ?></span>
                    <?php endif; ?>
                    <span class="badge bg-secondary ms-2"><?= $anio ?></span>
                </div>
                <div>
                    <span class="text-light me-3"><?= esc($cliente['nombre_cliente']) ?></span>
                    <button type="button" class="btn btn-success btn-sm" id="btnGenerarTodo">
                        <i class="bi bi-magic me-1"></i>Generar Todo con IA
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <?php
        // Verificar si hay una nueva version pendiente (documento en borrador con motivo)
        $versionPendiente = !empty($documento) && $documento['estado'] === 'borrador' && !empty($documento['motivo_version']);
        ?>
        <?php if ($versionPendiente): ?>
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div>
                    <strong>Editando para nueva version</strong><br>
                    <small>Motivo: <?= esc($documento['motivo_version']) ?></small><br>
                    <small class="text-muted">Modifica las secciones necesarias, guarda y aprueba. Al aprobar se creara la nueva version.</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar con navegacion de secciones -->
            <div class="col-md-3">
                <div class="sidebar">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Progreso del documento</h6>
                            <div class="progress progress-general mb-2">
                                <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted"><span id="seccionesCompletas">0</span> de <?= count($secciones) ?> secciones</small>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Secciones</h6>
                        </div>
                        <div class="card-body p-2">
                            <nav class="nav flex-column nav-secciones">
                                <?php foreach ($secciones as $seccion):
                                    $cont = $seccion['contenido'] ?? '';
                                    if (is_array($cont)) $cont = $cont['contenido'] ?? '';
                                    $hayContenido = !empty($cont);
                                ?>
                                    <a class="nav-link d-flex align-items-center" href="#seccion-<?= $seccion['key'] ?>" data-key="<?= $seccion['key'] ?>">
                                        <span class="me-2">
                                            <?php if (!empty($seccion['aprobado'])): ?>
                                                <i class="bi bi-check-circle-fill"></i>
                                            <?php elseif ($hayContenido): ?>
                                                <i class="bi bi-circle-fill text-warning" style="font-size: 0.6rem;"></i>
                                            <?php else: ?>
                                                <i class="bi bi-circle text-secondary" style="font-size: 0.6rem;"></i>
                                            <?php endif; ?>
                                        </span>
                                        <span class="small"><?= $seccion['numero'] ?>. <?= esc($seccion['nombre']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        </div>
                    </div>

                    <!-- Acciones rapidas -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Acciones</h6>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnGuardarTodo">
                                    <i class="bi bi-save me-1"></i>Guardar Todo
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAprobarTodo">
                                    <i class="bi bi-check-all me-1"></i>Aprobar Todo
                                </button>
                                <a href="<?= esc($urlVistaPrevia) ?>"
                                   class="btn btn-outline-dark btn-sm <?= $todasSeccionesListas ? '' : 'disabled' ?>"
                                   id="btnVistaPrevia"
                                   target="_blank"
                                   <?= $todasSeccionesListas ? '' : 'aria-disabled="true" tabindex="-1"' ?>>
                                    <i class="bi bi-eye me-1"></i>Vista Previa
                                    <?php if (!$todasSeccionesListas): ?>
                                    <small class="d-block text-muted" style="font-size: 0.65rem;">Guarda y aprueba todas</small>
                                    <?php endif; ?>
                                </a>
                                <hr class="my-3">
                                <?php
                                $estadoDoc = $documento['estado'] ?? 'borrador';
                                $idDocumento = $documento['id_documento'] ?? null;
                                ?>

                                <?php if ($estadoDoc === 'firmado'): ?>
                                    <!-- Documento ya firmado y aprobado automaticamente -->
                                    <div class="alert alert-success mb-2 py-2 px-3">
                                        <i class="bi bi-patch-check-fill me-1"></i>
                                        <small>Documento firmado y aprobado</small>
                                    </div>
                                    <a href="<?= base_url('firma/estado/' . $idDocumento) ?>" class="btn btn-outline-success btn-sm w-100">
                                        <i class="bi bi-eye me-1"></i>Ver Firmas
                                    </a>
                                <?php elseif ($estadoDoc === 'pendiente_firma'): ?>
                                    <!-- Esperando firmas -->
                                    <div class="alert alert-warning mb-2 py-2 px-3">
                                        <i class="bi bi-clock-history me-1"></i>
                                        <small>Pendiente de firmas</small>
                                    </div>
                                    <a href="<?= base_url('firma/estado/' . $idDocumento) ?>" class="btn btn-warning btn-sm w-100">
                                        <i class="bi bi-pen me-1"></i>Estado Firmas
                                    </a>
                                <?php elseif (in_array($estadoDoc, ['borrador', 'generado', 'aprobado', 'en_revision']) && $idDocumento): ?>
                                    <!-- Listo para enviar a firmas (documento existe y estado válido) -->
                                    <a href="<?= base_url('firma/solicitar/' . $idDocumento) ?>" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-pen me-1"></i>Enviar a Firmas
                                        <small class="d-block" style="font-size: 0.6rem;">El cliente revisara y firmara</small>
                                    </a>
                                <?php elseif ($idDocumento): ?>
                                    <!-- Documento existe pero estado no permite firmas -->
                                    <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                        <i class="bi bi-pen me-1"></i>Enviar a Firmas
                                        <small class="d-block" style="font-size: 0.6rem;">Estado: <?= esc($estadoDoc) ?></small>
                                    </button>
                                <?php else: ?>
                                    <!-- Documento no existe aún -->
                                    <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                        <i class="bi bi-pen me-1"></i>Enviar a Firmas
                                        <small class="d-block" style="font-size: 0.6rem;">Primero guarda el documento</small>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido principal - Secciones -->
            <div class="col-md-9">
                <?php foreach ($secciones as $seccion):
                    // Verificar contenido para clases CSS
                    $contCheck = $seccion['contenido'] ?? '';
                    if (is_array($contCheck)) $contCheck = $contCheck['contenido'] ?? '';
                    $hayContenidoCard = !empty($contCheck);
                ?>
                    <div class="card border-0 shadow-sm mb-4 seccion-card <?= $hayContenidoCard ? 'generada' : '' ?> <?= !empty($seccion['aprobado']) ? 'aprobada' : '' ?>"
                         id="seccion-<?= $seccion['key'] ?>"
                         data-key="<?= $seccion['key'] ?>">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="seccion-numero bg-primary text-white me-3">
                                    <?= $seccion['numero'] ?>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?= esc($seccion['nombre']) ?></h5>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if (!empty($seccion['aprobado'])): ?>
                                    <span class="badge bg-primary"><i class="bi bi-check-circle me-1"></i>Aprobada</span>
                                <?php elseif ($hayContenidoCard): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i>En edicion</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-hourglass me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                            // Obtener contenido como string
                            $contenidoSeccion = $seccion['contenido'] ?? '';
                            if (is_array($contenidoSeccion)) {
                                $contenidoSeccion = $contenidoSeccion['contenido'] ?? '';
                            }
                            $tieneContenido = !empty($contenidoSeccion);
                        ?>
                        <div class="card-body">
                            <!-- Contexto IA para esta seccion -->
                            <div class="mb-3">
                                <button class="btn btn-outline-secondary btn-sm w-100 text-start btn-toggle-contexto" type="button" data-bs-toggle="collapse" data-bs-target="#contexto-<?= $seccion['key'] ?>">
                                    <i class="bi bi-robot me-1"></i>Dar contexto a la IA
                                    <i class="bi bi-chevron-down float-end"></i>
                                </button>
                                <div class="collapse mt-2" id="contexto-<?= $seccion['key'] ?>">
                                    <textarea class="form-control contexto-ia-seccion"
                                              id="contexto-input-<?= $seccion['key'] ?>"
                                              rows="3"
                                              placeholder="Instrucciones adicionales para la IA al generar esta seccion. Ej: 'Enfocarse en riesgos quimicos', 'Incluir ejemplos practicos', 'Usar lenguaje tecnico'..."></textarea>
                                    <small class="text-muted">Este contexto se enviara junto con la solicitud de generacion.</small>
                                </div>
                            </div>

                            <textarea class="form-control contenido-seccion mb-3"
                                      id="contenido-<?= $seccion['key'] ?>"
                                      rows="8"
                                      placeholder="Haz clic en 'Generar con IA' para crear el contenido de esta seccion..."><?= esc($contenidoSeccion) ?></textarea>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-generar-ia btn-sm btn-generar" data-seccion="<?= $seccion['key'] ?>">
                                        <i class="bi bi-magic me-1"></i>Generar con IA
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-regenerar" data-seccion="<?= $seccion['key'] ?>" <?= !$tieneContenido ? 'disabled' : '' ?>>
                                        <i class="bi bi-arrow-clockwise me-1"></i>Regenerar
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-guardar" data-seccion="<?= $seccion['key'] ?>">
                                        <i class="bi bi-save me-1"></i>Guardar
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-aprobar" data-seccion="<?= $seccion['key'] ?>" <?= !$tieneContenido ? 'disabled' : '' ?>>
                                        <i class="bi bi-check-circle me-1"></i>Aprobar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal de progreso -->
    <div class="modal fade" id="modalProgreso" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Generando...</span>
                    </div>
                    <h5 id="progresoTitulo">Redactando documento...</h5>
                    <p class="text-muted mb-0" id="progresoDetalle">Consultando bases de datos...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aprobar Documento y Crear Version -->
    <div class="modal fade" id="modalAprobarDocumento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Aprobar Documento y Crear Version</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Al aprobar el documento:
                        <ul class="mb-0 mt-2">
                            <li>Se creara una version oficial en el historial</li>
                            <li>El documento quedara disponible en Vista Previa</li>
                            <li>Podra exportar a PDF o Word</li>
                            <li>Aparecera en el Control de Cambios</li>
                        </ul>
                    </div>
                    <form id="formAprobarDocumento">
                        <input type="hidden" name="id_documento" id="id_documento_aprobar" value="<?= $documento['id_documento'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Tipo de cambio</label>
                            <select name="tipo_cambio" class="form-select" required>
                                <option value="menor">Menor (correccion, ajuste de redaccion)</option>
                                <option value="mayor">Mayor (cambio de metodologia, normativo)</option>
                            </select>
                            <small class="text-muted">
                                Menor: v1.0 &rarr; v1.1 | Mayor: v1.x &rarr; v2.0
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripcion del cambio <span class="text-danger">*</span></label>
                            <textarea name="descripcion_cambio" class="form-control" rows="3" required
                                placeholder="Ej: Version inicial del documento, Actualizacion de cronograma, etc."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnConfirmarAprobacion">
                        <i class="bi bi-check-circle me-1"></i>Aprobar y Crear Version
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idCliente = <?= $cliente['id_cliente'] ?>;
        const tipo = '<?= $tipo ?>';
        const anio = <?= $anio ?>;
        const secciones = <?= json_encode(array_column($secciones, 'key')) ?>;
        const seccionesNombres = <?= json_encode(array_column($secciones, 'nombre', 'key')) ?>;
        const seccionesNumeros = <?= json_encode(array_column($secciones, 'numero', 'key')) ?>;
        const totalSecciones = <?= $totalSecciones ?>;
        let seccionesAprobadasCount = <?= $seccionesAprobadas ?>;

        const modalProgreso = new bootstrap.Modal(document.getElementById('modalProgreso'));

        // ==========================================
        // VERIFICACION DE VISTA PREVIA
        // ==========================================
        function verificarVistaPrevia() {
            const btnVistaPrevia = document.getElementById('btnVistaPrevia');
            if (!btnVistaPrevia) return;

            // Contar secciones aprobadas actualmente en el DOM
            const aprobadas = document.querySelectorAll('.seccion-card.aprobada').length;

            // Contar secciones con contenido guardado (tienen clase 'generada')
            const guardadas = document.querySelectorAll('.seccion-card.generada').length;

            // Solo habilitar si TODAS estan guardadas Y aprobadas
            if (aprobadas >= totalSecciones && guardadas >= totalSecciones) {
                // Habilitar el boton
                btnVistaPrevia.classList.remove('disabled');
                btnVistaPrevia.removeAttribute('aria-disabled');
                btnVistaPrevia.removeAttribute('tabindex');
                // Remover texto de ayuda si existe
                const helpText = btnVistaPrevia.querySelector('small');
                if (helpText) helpText.remove();

                mostrarToast('success', 'Documento Listo', 'Todas las secciones guardadas y aprobadas. Ya puedes ver la Vista Previa.');
            }
        }

        // ==========================================
        // SISTEMA DE TOAST NOTIFICATIONS
        // ==========================================
        function mostrarToast(tipo, titulo, mensaje) {
            const toastEl = document.getElementById('toastNotificacion');
            const toastTitulo = document.getElementById('toastTitulo');
            const toastMensaje = document.getElementById('toastMensaje');
            const toastIcono = document.getElementById('toastIcono');
            const toastHeader = document.getElementById('toastHeader');

            // Limpiar clases anteriores
            toastHeader.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary', 'text-white', 'text-dark');
            toastIcono.classList.remove('bi-check-circle-fill', 'bi-x-circle-fill', 'bi-exclamation-triangle-fill', 'bi-info-circle-fill', 'bi-robot', 'bi-save-fill', 'bi-magic', 'text-success', 'text-danger', 'text-warning', 'text-info', 'text-white');

            // Configurar segun tipo
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
                    toastHeader.classList.add('bg-warning', 'text-dark');
                    toastIcono.classList.add('bi-exclamation-triangle-fill', 'text-dark');
                    break;
                case 'info':
                    toastHeader.classList.add('bg-info', 'text-white');
                    toastIcono.classList.add('bi-info-circle-fill', 'text-white');
                    break;
                case 'ia':
                    toastHeader.classList.add('bg-primary', 'text-white');
                    toastIcono.classList.add('bi-robot', 'text-white');
                    break;
                case 'save':
                    toastHeader.classList.add('bg-success', 'text-white');
                    toastIcono.classList.add('bi-save-fill', 'text-white');
                    break;
                case 'database':
                    toastHeader.classList.add('bg-info', 'text-white');
                    toastIcono.classList.add('bi-database-check', 'text-white');
                    break;
            }

            toastTitulo.textContent = titulo;
            toastMensaje.innerHTML = mensaje; // Cambiado a innerHTML para permitir HTML

            // Duraciones: database=15s (mucha info), success/warning=6s, otros=5s
            const duraciones = { 'database': 15000, 'success': 6000, 'warning': 6000, 'save': 5000, 'error': 8000 };
            const delay = duraciones[tipo] || 5000;
            const toast = new bootstrap.Toast(toastEl, { delay: delay });
            toast.show();
        }

        // Mostrar toast especial con metadata de BD consultadas
        function mostrarToastBD(metadata) {
            if (!metadata || !metadata.tablas_consultadas) return;

            let mensaje = '<div class="small">';
            mensaje += '<strong class="d-block mb-1">' + metadata.resumen + '</strong>';
            mensaje += '<ul class="list-unstyled mb-0 mt-2" style="font-size: 0.85em;">';

            metadata.tablas_consultadas.forEach(tabla => {
                const icono = tabla.icono || 'bi-table';
                const registros = tabla.registros;
                const colorRegistros = registros > 0 ? 'text-success' : 'text-warning';

                mensaje += `<li class="mb-1">`;
                mensaje += `<i class="bi ${icono} me-1"></i>`;
                mensaje += `<strong>${tabla.descripcion}:</strong> `;
                mensaje += `<span class="${colorRegistros}">${registros} registros</span>`;

                // Mostrar datos si hay pocos
                if (tabla.datos && tabla.datos.length > 0 && tabla.datos.length <= 3) {
                    mensaje += `<br><small class="text-muted ms-3">→ ${tabla.datos.join(', ')}</small>`;
                } else if (tabla.datos && tabla.datos.length > 3) {
                    mensaje += `<br><small class="text-muted ms-3">→ ${tabla.datos.slice(0, 2).join(', ')}... (+${tabla.datos.length - 2} más)</small>`;
                }
                mensaje += `</li>`;
            });

            mensaje += '</ul></div>';

            mostrarToast('database', '✅ Bases de Datos Consultadas', mensaje);
        }

        function getNombreSeccion(key) {
            return seccionesNombres[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function getNumeroSeccion(key) {
            return seccionesNumeros[key] || '?';
        }

        function actualizarProgreso() {
            let completas = 0;
            secciones.forEach(key => {
                const textarea = document.getElementById('contenido-' + key);
                if (textarea && textarea.value.trim() !== '') {
                    completas++;
                }
            });
            const porcentaje = Math.round((completas / secciones.length) * 100);
            document.getElementById('progressBar').style.width = porcentaje + '%';
            document.getElementById('seccionesCompletas').textContent = completas;
        }

        // Generar seccion individual
        document.querySelectorAll('.btn-generar, .btn-regenerar').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const seccion = this.dataset.seccion;
                console.log('Click en boton generar, seccion:', seccion);
                generarSeccion(seccion);
            });
        });

        console.log('Event listeners agregados a', document.querySelectorAll('.btn-generar').length, 'botones');

        async function generarSeccion(seccionKey) {
            const btn = document.querySelector(`.btn-generar[data-seccion="${seccionKey}"]`);
            const textarea = document.getElementById('contenido-' + seccionKey);
            const card = document.getElementById('seccion-' + seccionKey);
            const contextoInput = document.getElementById('contexto-input-' + seccionKey);
            const contextoAdicional = contextoInput ? contextoInput.value.trim() : '';

            if (!btn) {
                console.error('Boton no encontrado para seccion:', seccionKey);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

            try {
                let body = `id_cliente=${idCliente}&tipo=${tipo}&seccion=${seccionKey}&anio=${anio}`;
                if (contextoAdicional) {
                    body += `&contexto_adicional=${encodeURIComponent(contextoAdicional)}`;
                }

                console.log('Enviando solicitud para seccion:', seccionKey, 'con contexto:', contextoAdicional);

                const response = await fetch('<?= base_url('documentos/generar-seccion') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    textarea.value = data.contenido;
                    card.classList.add('generada');
                    document.querySelector(`.btn-regenerar[data-seccion="${seccionKey}"]`).disabled = false;
                    document.querySelector(`.btn-aprobar[data-seccion="${seccionKey}"]`).disabled = false;
                    actualizarProgreso();

                    // Actualizar icono en sidebar
                    const navLink = document.querySelector(`.nav-secciones a[data-key="${seccionKey}"] span`);
                    if (navLink) {
                        navLink.innerHTML = '<i class="bi bi-circle-fill text-warning" style="font-size: 0.6rem;"></i>';
                    }

                    // Toast de exito
                    const usaIA = contextoAdicional ? ' con IA (OpenAI)' : '';
                    mostrarToast('ia', 'Contenido Generado' + usaIA, `Seccion "${getNombreSeccion(seccionKey)}" generada correctamente.`);

                    // Toast de BD consultadas (si hay metadata)
                    if (data.metadata_bd && data.metadata_bd.tablas_consultadas) {
                        setTimeout(() => {
                            mostrarToastBD(data.metadata_bd);
                        }, 500); // Pequeño delay para que se vean ambos toasts
                    }
                } else {
                    mostrarToast('error', 'Error al Generar', data.message || `No se pudo generar la seccion "${getNombreSeccion(seccionKey)}".`);
                }
            } catch (error) {
                mostrarToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor: ' + error.message);
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic me-1"></i>Generar con IA';
        }

        // Guardar seccion
        document.querySelectorAll('.btn-guardar').forEach(btn => {
            btn.addEventListener('click', async function() {
                const seccion = this.dataset.seccion;
                const textarea = document.getElementById('contenido-' + seccion);
                const card = document.getElementById('seccion-' + seccion);

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

                try {
                    const response = await fetch('<?= base_url('documentos/guardar-seccion') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_cliente=${idCliente}&tipo=${tipo}&seccion=${seccion}&anio=${anio}&contenido=${encodeURIComponent(textarea.value)}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.innerHTML = '<i class="bi bi-check me-1"></i>Guardado';
                        card.classList.add('generada');

                        // Toast de exito
                        mostrarToast('save', 'Seccion Guardada', `"${getNombreSeccion(seccion)}" guardada en la base de datos.`);

                        setTimeout(() => {
                            this.innerHTML = '<i class="bi bi-save me-1"></i>Guardar';
                        }, 2000);
                    } else {
                        mostrarToast('error', 'Error al Guardar', data.message || `No se pudo guardar "${getNombreSeccion(seccion)}".`);
                    }
                } catch (error) {
                    mostrarToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor: ' + error.message);
                }

                this.disabled = false;
            });
        });

        // Aprobar seccion
        document.querySelectorAll('.btn-aprobar').forEach(btn => {
            btn.addEventListener('click', async function() {
                const seccion = this.dataset.seccion;
                const card = document.getElementById('seccion-' + seccion);
                const textoOriginal = this.innerHTML;

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Aprobando...';

                try {
                    const response = await fetch('<?= base_url('documentos/aprobar-seccion') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_cliente=${idCliente}&tipo=${tipo}&seccion=${seccion}&anio=${anio}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        card.classList.add('aprobada');
                        card.querySelector('.badge').className = 'badge bg-primary';
                        card.querySelector('.badge').innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobada';

                        // Actualizar sidebar
                        const navLink = document.querySelector(`.nav-secciones a[data-key="${seccion}"] span`);
                        if (navLink) {
                            navLink.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                        }

                        // Toast de exito
                        mostrarToast('success', 'Seccion Aprobada', `"${getNombreSeccion(seccion)}" aprobada y lista para el documento final.`);

                        this.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Aprobada';

                        // Verificar si ya se puede habilitar Vista Previa
                        verificarVistaPrevia();
                    } else {
                        mostrarToast('error', 'Error al Aprobar', data.message || `No se pudo aprobar "${getNombreSeccion(seccion)}".`);
                        this.innerHTML = textoOriginal;
                        this.disabled = false;
                    }
                } catch (error) {
                    mostrarToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor: ' + error.message);
                    this.innerHTML = textoOriginal;
                    this.disabled = false;
                }
            });
        });

        // Generar todo - con jerarquia: primero secciones con datos de tablas, luego texto estatico
        document.getElementById('btnGenerarTodo').addEventListener('click', async function() {
            if (!confirm('Esto generara contenido para todas las secciones. Desea continuar?')) return;

            modalProgreso.show();
            let exitosas = 0;
            let errores = 0;

            // Jerarquia de generacion:
            // 1. Secciones que consumen datos de tablas (deben ir primero)
            const seccionesConDatos = ['cronograma', 'plan_trabajo', 'indicadores', 'responsabilidades', 'recursos'];
            // 2. Secciones de texto estatico
            const seccionesTexto = secciones.filter(s => !seccionesConDatos.includes(s));

            // Orden final: primero datos, luego texto
            const ordenGeneracion = [...seccionesConDatos.filter(s => secciones.includes(s)), ...seccionesTexto];

            for (let i = 0; i < ordenGeneracion.length; i++) {
                const seccionKey = ordenGeneracion[i];
                const nombreSeccion = getNombreSeccion(seccionKey);
                const numeroSeccion = getNumeroSeccion(seccionKey);
                document.getElementById('progresoTitulo').textContent = `Redactando seccion ${numeroSeccion}: ${nombreSeccion}`;
                document.getElementById('progresoDetalle').textContent = `(${i + 1} de ${ordenGeneracion.length} secciones)`;

                try {
                    await generarSeccion(seccionKey);
                    exitosas++;
                } catch (e) {
                    errores++;
                }
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            modalProgreso.hide();

            if (errores === 0) {
                mostrarToast('success', 'Generacion Completa', `Las ${exitosas} secciones fueron generadas exitosamente.`);
            } else {
                mostrarToast('warning', 'Generacion Parcial', `${exitosas} secciones generadas, ${errores} con errores.`);
            }
        });

        // Guardar todo
        document.getElementById('btnGuardarTodo').addEventListener('click', async function() {
            const btns = document.querySelectorAll('.btn-guardar');
            const total = btns.length;
            let guardadas = 0;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

            for (const btn of btns) {
                const seccion = btn.dataset.seccion;
                const textarea = document.getElementById('contenido-' + seccion);
                const card = document.getElementById('seccion-' + seccion);

                try {
                    const response = await fetch('<?= base_url('documentos/guardar-seccion') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_cliente=${idCliente}&tipo=${tipo}&seccion=${seccion}&anio=${anio}&contenido=${encodeURIComponent(textarea.value)}`
                    });

                    const data = await response.json();
                    if (data.success) {
                        guardadas++;
                        card.classList.add('generada');
                    }
                } catch (e) {
                    console.error('Error guardando', seccion, e);
                }
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            this.disabled = false;
            this.innerHTML = '<i class="bi bi-save me-1"></i>Guardar Todo';

            mostrarToast('save', 'Guardado Masivo', `${guardadas} de ${total} secciones guardadas en la base de datos.`);
        });

        // Aprobar todo
        document.getElementById('btnAprobarTodo').addEventListener('click', async function() {
            if (!confirm('Esto aprobara todas las secciones con contenido. Desea continuar?')) return;

            const btns = document.querySelectorAll('.btn-aprobar:not([disabled])');
            const total = btns.length;

            if (total === 0) {
                mostrarToast('warning', 'Sin Secciones', 'No hay secciones con contenido para aprobar.');
                return;
            }

            let aprobadas = 0;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Aprobando...';

            for (const btn of btns) {
                const seccion = btn.dataset.seccion;
                const card = document.getElementById('seccion-' + seccion);

                try {
                    const response = await fetch('<?= base_url('documentos/aprobar-seccion') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id_cliente=${idCliente}&tipo=${tipo}&seccion=${seccion}&anio=${anio}`
                    });

                    const data = await response.json();
                    if (data.success) {
                        aprobadas++;
                        card.classList.add('aprobada');
                        card.querySelector('.badge').className = 'badge bg-primary';
                        card.querySelector('.badge').innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobada';

                        const navLink = document.querySelector(`.nav-secciones a[data-key="${seccion}"] span`);
                        if (navLink) {
                            navLink.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                        }

                        btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Aprobada';
                        btn.disabled = true;
                    }
                } catch (e) {
                    console.error('Error aprobando', seccion, e);
                }
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            this.disabled = false;
            this.innerHTML = '<i class="bi bi-check-all me-1"></i>Aprobar Todo';

            if (aprobadas === total) {
                mostrarToast('success', 'Aprobacion Completa', `Las ${aprobadas} secciones fueron aprobadas exitosamente.`);
            } else {
                mostrarToast('warning', 'Aprobacion Parcial', `${aprobadas} de ${total} secciones aprobadas.`);
            }

            // Verificar si ya se puede habilitar Vista Previa
            verificarVistaPrevia();
        });

        // Smooth scroll para navegacion
        document.querySelectorAll('.nav-secciones a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ==========================================
        // APROBAR DOCUMENTO Y CREAR VERSION
        // ==========================================
        document.getElementById('btnConfirmarAprobacion')?.addEventListener('click', function() {
            const form = document.getElementById('formAprobarDocumento');
            const formData = new FormData(form);

            if (!formData.get('descripcion_cambio').trim()) {
                alert('Debe ingresar una descripcion del cambio');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Aprobando...';

            fetch('<?= base_url('documentos-sst/aprobar-documento') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('success', 'Documento Aprobado', 'Version ' + data.version + ' creada correctamente.');

                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAprobarDocumento'));
                    modal.hide();

                    // Actualizar boton
                    const btnAprobar = document.getElementById('btnAprobarDocumento');
                    btnAprobar.classList.remove('btn-success');
                    btnAprobar.classList.add('btn-outline-success');
                    btnAprobar.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Aprobado v' + data.version;
                    btnAprobar.disabled = true;

                    // Preguntar si desea ir a Vista Previa
                    setTimeout(() => {
                        if (confirm('Documento aprobado exitosamente.\n\n¿Desea ver la Vista Previa del documento?')) {
                            window.open('<?= esc($urlVistaPrevia) ?>', '_blank');
                        }
                    }, 500);
                } else {
                    alert('Error: ' + data.message);
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobar y Crear Version';
                }
            })
            .catch(error => {
                alert('Error de conexion: ' + error.message);
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobar y Crear Version';
            });
        });

        // Funcion para habilitar boton de aprobar documento cuando todas las secciones esten listas
        function habilitarAprobacionDocumento() {
            const btnAprobar = document.getElementById('btnAprobarDocumento');
            if (!btnAprobar) return;

            const aprobadas = document.querySelectorAll('.seccion-card.aprobada').length;
            const guardadas = document.querySelectorAll('.seccion-card.generada').length;

            if (aprobadas >= totalSecciones && guardadas >= totalSecciones) {
                btnAprobar.classList.remove('disabled');
                btnAprobar.disabled = false;
                btnAprobar.setAttribute('data-bs-toggle', 'modal');
                btnAprobar.setAttribute('data-bs-target', '#modalAprobarDocumento');
                btnAprobar.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobar Documento<small class="d-block" style="font-size: 0.6rem;">Crear version oficial</small>';
            }
        }

        // Llamar despues de aprobar todas las secciones
        const originalVerificarVistaPrevia = verificarVistaPrevia;
        verificarVistaPrevia = function() {
            originalVerificarVistaPrevia();
            habilitarAprobacionDocumento();
        };

        // Inicializar progreso
        actualizarProgreso();
    });
    </script>
</body>
</html>
