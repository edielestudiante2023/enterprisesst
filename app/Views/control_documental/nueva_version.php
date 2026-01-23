<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Versión - <?= esc($documento['codigo'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .tipo-cambio-card {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .tipo-cambio-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .tipo-cambio-card.selected {
            border-color: #3B82F6;
        }
        .tipo-cambio-card.selected.mayor {
            border-color: #EF4444;
            background: #FEF2F2;
        }
        .tipo-cambio-card.selected.menor {
            border-color: #F59E0B;
            background: #FFFBEB;
        }
        .version-preview {
            font-size: 2rem;
            font-weight: bold;
            font-family: monospace;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-plus-circle me-2"></i>Nueva Versión
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/control-documental/historial/<?= $documento['id_documento'] ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Historial
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-plus me-2"></i>
                            Crear Nueva Versión de Documento
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Info del documento -->
                        <div class="alert alert-light border mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Documento:</strong> <?= esc($documento['nombre']) ?><br>
                                    <strong>Código:</strong> <code><?= esc($documento['codigo']) ?></code>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <strong>Versión actual:</strong>
                                    <span class="badge bg-primary fs-6"><?= esc($versionActual) ?></span>
                                </div>
                            </div>
                        </div>

                        <form action="/control-documental/crear-version/<?= $documento['id_documento'] ?>" method="post">
                            <!-- Tipo de cambio -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tipo de Cambio</label>
                                <p class="text-muted small">Seleccione si el cambio es menor (correcciones) o mayor (cambios estructurales)</p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card tipo-cambio-card menor" onclick="selectTipo('menor')">
                                            <div class="card-body text-center">
                                                <i class="bi bi-arrow-up-right text-warning" style="font-size: 2rem;"></i>
                                                <h5 class="mt-2 mb-1">Cambio Menor</h5>
                                                <p class="text-muted small mb-2">
                                                    Correcciones de redacción, actualización de fechas,
                                                    ajustes de formato, corrección de errores tipográficos
                                                </p>
                                                <div class="version-preview text-warning">
                                                    <?= esc($siguienteMenor) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card tipo-cambio-card mayor" onclick="selectTipo('mayor')">
                                            <div class="card-body text-center">
                                                <i class="bi bi-arrow-up text-danger" style="font-size: 2rem;"></i>
                                                <h5 class="mt-2 mb-1">Cambio Mayor</h5>
                                                <p class="text-muted small mb-2">
                                                    Cambios en objetivos, alcance, metodología,
                                                    nueva normatividad, reestructuración de contenido
                                                </p>
                                                <div class="version-preview text-danger">
                                                    <?= esc($siguienteMayor) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="tipo_cambio" id="tipo_cambio" value="menor" required>
                            </div>

                            <!-- Descripción del cambio -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Descripción del Cambio</label>
                                <textarea name="descripcion_cambio" class="form-control" rows="4" required
                                          placeholder="Describa detalladamente los cambios realizados en esta versión..."></textarea>
                                <div class="form-text">
                                    Esta descripción aparecerá en el historial de control de cambios del documento.
                                </div>
                            </div>

                            <!-- Ejemplos de cambios -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Ejemplos de descripción:</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-warning bg-opacity-10 border-warning">
                                            <div class="card-body py-2">
                                                <small class="fw-bold text-warning">Cambio Menor:</small>
                                                <ul class="small mb-0 ps-3">
                                                    <li>Actualización de fechas del cronograma</li>
                                                    <li>Corrección de errores ortográficos</li>
                                                    <li>Ajuste de formato en tablas</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-danger bg-opacity-10 border-danger">
                                            <div class="card-body py-2">
                                                <small class="fw-bold text-danger">Cambio Mayor:</small>
                                                <ul class="small mb-0 ps-3">
                                                    <li>Inclusión de nuevos peligros identificados</li>
                                                    <li>Actualización por nueva normatividad</li>
                                                    <li>Modificación de objetivos o alcance</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advertencia -->
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Nota:</strong> Al crear una nueva versión:
                                <ul class="mb-0 mt-2">
                                    <li>Se guardará un snapshot del contenido actual</li>
                                    <li>El documento volverá a estado "Borrador" para edición</li>
                                    <li>Deberá ser aprobado nuevamente con las firmas correspondientes</li>
                                </ul>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="/control-documental/historial/<?= $documento['id_documento'] ?>"
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>Crear Nueva Versión
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Seleccionar menor por defecto
        document.querySelector('.tipo-cambio-card.menor').classList.add('selected');

        function selectTipo(tipo) {
            // Limpiar selección
            document.querySelectorAll('.tipo-cambio-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Seleccionar
            document.querySelector(`.tipo-cambio-card.${tipo}`).classList.add('selected');
            document.getElementById('tipo_cambio').value = tipo;
        }
    </script>
</body>
</html>
