<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar Presupuesto SST <?= $anio ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .firma-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card-presupuesto {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .header-presupuesto {
            background: linear-gradient(135deg, #1a5f7a 0%, #2c3e50 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 25px;
        }
        .tabla-presupuesto {
            font-size: 0.8rem;
        }
        .tabla-presupuesto th {
            background-color: #1a5f7a;
            color: white;
            font-size: 0.75rem;
            padding: 6px;
        }
        .tabla-presupuesto td {
            padding: 4px 6px;
        }
        .categoria-row {
            background-color: #e8f4f8 !important;
            font-weight: bold;
        }
        .subtotal-row {
            background-color: #d4edda !important;
            font-weight: bold;
        }
        .total-row {
            background-color: #1a5f7a !important;
            color: white !important;
            font-weight: bold;
        }
        .firma-canvas {
            border: 2px dashed #ccc;
            border-radius: 8px;
            background: #fafafa;
            cursor: crosshair;
        }
        .firma-canvas:hover {
            border-color: #1a5f7a;
        }
        .btn-aprobar {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
        }
        .info-legal {
            font-size: 0.75rem;
            color: #666;
        }
    </style>
</head>
<body class="py-4">
    <div class="container firma-container">
        <!-- Logo y titulo -->
        <div class="text-center text-white mb-4">
            <h2><i class="bi bi-patch-check me-2"></i>Aprobacion de Presupuesto SST</h2>
            <p class="opacity-75"><?= esc($cliente['nombre_cliente']) ?> - Año <?= $anio ?></p>
        </div>

        <div class="card card-presupuesto">
            <!-- Header -->
            <div class="header-presupuesto">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><?= esc($codigoDocumento ?? 'FT-SST-001') ?> Asignacion de Recursos SG-SST</h4>
                        <p class="mb-0 opacity-75">Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="bg-white bg-opacity-10 rounded p-2">
                            <div class="fs-4 fw-bold">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></div>
                            <small>Total Presupuestado</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Info empresa -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <small class="text-muted">Empresa:</small>
                        <div class="fw-bold"><?= esc($cliente['nombre_cliente']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">NIT:</small>
                        <div><?= esc($cliente['nit_cliente'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Periodo:</small>
                        <div>Año <?= $anio ?></div>
                    </div>
                </div>

                <!-- Tabla de presupuesto -->
                <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered table-sm tabla-presupuesto mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th>Item</th>
                                <th>Actividad</th>
                                <?php foreach ($meses as $mes): ?>
                                    <th class="text-center"><?= $mes['nombre'] ?></th>
                                <?php endforeach; ?>
                                <th class="text-center">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): ?>
                                <!-- Categoria -->
                                <tr class="categoria-row">
                                    <td colspan="<?= 2 + count($meses) + 1 ?>">
                                        <?= $codigoCat ?>. <?= esc($categoria['nombre']) ?>
                                    </td>
                                </tr>

                                <!-- Items -->
                                <?php foreach ($categoria['items'] as $item): ?>
                                <tr>
                                    <td><?= esc($item['codigo_item']) ?></td>
                                    <td><?= esc($item['actividad']) ?></td>
                                    <?php foreach ($meses as $mes):
                                        $detalle = $item['detalles'][$mes['numero']] ?? null;
                                        $presup = $detalle ? floatval($detalle['presupuestado']) : 0;
                                    ?>
                                        <td class="text-end">
                                            <?= $presup > 0 ? '$' . number_format($presup, 0, ',', '.') : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-end fw-bold">
                                        $<?= number_format($item['total_presupuestado'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <!-- Subtotal -->
                                <?php $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'por_mes' => []]; ?>
                                <tr class="subtotal-row">
                                    <td colspan="2" class="text-end">Sub Total</td>
                                    <?php foreach ($meses as $mes):
                                        $totMes = $totCat['por_mes'][$mes['numero']] ?? ['presupuestado' => 0];
                                    ?>
                                        <td class="text-end">$<?= number_format($totMes['presupuestado'], 0, ',', '.') ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-end">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Total General -->
                            <tr class="total-row">
                                <td colspan="2" class="text-end">TOTAL GENERAL</td>
                                <?php foreach ($meses as $mes):
                                    $totMes = $totales['por_mes'][$mes['numero']] ?? ['presupuestado' => 0];
                                ?>
                                    <td class="text-end">$<?= number_format($totMes['presupuestado'], 0, ',', '.') ?></td>
                                <?php endforeach; ?>
                                <td class="text-end fs-6">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <!-- Seccion de Firma -->
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-pen me-2"></i>Firma de Aprobacion</h5>

                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="firmaNombre"
                                   value="<?= esc($cliente['representante_legal'] ?? $cliente['nombre_rep_legal'] ?? '') ?>"
                                   placeholder="Nombre del firmante" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numero de Cedula *</label>
                            <input type="text" class="form-control" id="firmaCedula"
                                   value="<?= esc($cliente['cedula_rep_legal'] ?? '') ?>"
                                   placeholder="Cedula de ciudadania" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Firma Digital * <small class="text-muted">(dibuje su firma)</small></label>
                            <canvas id="canvasFirma" class="firma-canvas w-100" height="150"></canvas>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLimpiarFirma">
                                    <i class="bi bi-eraser me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="text-muted mb-3">Al firmar este documento, usted declara:</h6>
                            <ul class="info-legal">
                                <li class="mb-2">He revisado el presupuesto de Seguridad y Salud en el Trabajo para el año <?= $anio ?>.</li>
                                <li class="mb-2">Apruebo la asignacion de recursos aqui detallada por un total de <strong>$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></strong>.</li>
                                <li class="mb-2">Entiendo que este presupuesto forma parte del Sistema de Gestion de Seguridad y Salud en el Trabajo de <?= esc($cliente['nombre_cliente']) ?>.</li>
                                <li>Esta firma digital tiene la misma validez que una firma manuscrita segun la Ley 527 de 1999.</li>
                            </ul>

                            <div class="alert alert-info mt-3 mb-0">
                                <small>
                                    <i class="bi bi-shield-check me-1"></i>
                                    Su firma sera registrada junto con la fecha, hora e IP de origen para garantizar la autenticidad del documento.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Botones -->
                <div class="text-center">
                    <button type="button" class="btn btn-aprobar btn-lg text-white" id="btnAprobarPresupuesto">
                        <i class="bi bi-patch-check me-2"></i>Aprobar y Firmar Presupuesto
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="card-footer text-center text-muted">
                <small>Documento generado por Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo</small>
            </div>
        </div>
    </div>

    <!-- Modal de exito -->
    <div class="modal fade" id="modalExito" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="text-success mb-3">
                        <i class="bi bi-check-circle" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success">Presupuesto Aprobado</h4>
                    <p class="text-muted">El presupuesto ha sido firmado y aprobado correctamente.</p>
                    <div class="mt-4">
                        <a href="<?= base_url('presupuesto/consulta/' . $token) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-2"></i>Ver Presupuesto Aprobado
                        </a>
                    </div>
                    <p class="small text-muted mt-3">Guarde este enlace para futuras consultas.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Canvas de firma
        const canvas = document.getElementById('canvasFirma');
        const ctx = canvas.getContext('2d');
        let dibujando = false;
        let tieneContenido = false;

        // Ajustar tamaño real del canvas
        canvas.width = canvas.offsetWidth;
        canvas.height = 150;

        // Configurar estilo de dibujo
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Funciones de dibujo
        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            return { x, y };
        }

        function iniciarDibujo(e) {
            dibujando = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function dibujar(e) {
            if (!dibujando) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            tieneContenido = true;
            e.preventDefault();
        }

        function terminarDibujo() {
            dibujando = false;
        }

        // Eventos mouse
        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', terminarDibujo);
        canvas.addEventListener('mouseleave', terminarDibujo);

        // Eventos touch
        canvas.addEventListener('touchstart', iniciarDibujo);
        canvas.addEventListener('touchmove', dibujar);
        canvas.addEventListener('touchend', terminarDibujo);

        // Limpiar firma
        document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            tieneContenido = false;
        });

        // Aprobar presupuesto
        document.getElementById('btnAprobarPresupuesto').addEventListener('click', function() {
            const nombre = document.getElementById('firmaNombre').value.trim();
            const cedula = document.getElementById('firmaCedula').value.trim();

            // Validaciones
            if (!nombre) {
                alert('Debe ingresar su nombre completo');
                document.getElementById('firmaNombre').focus();
                return;
            }

            if (!cedula) {
                alert('Debe ingresar su numero de cedula');
                document.getElementById('firmaCedula').focus();
                return;
            }

            if (!tieneContenido) {
                alert('Debe dibujar su firma en el recuadro');
                return;
            }

            // Confirmar
            if (!confirm('Esta seguro de aprobar este presupuesto?\n\nEsta accion no se puede deshacer.')) {
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            // Obtener imagen del canvas
            const firmaImagen = canvas.toDataURL('image/png');

            const data = new FormData();
            data.append('token', '<?= $token ?>');
            data.append('firma_nombre', nombre);
            data.append('firma_cedula', cedula);
            data.append('firma_imagen', firmaImagen);

            fetch('<?= base_url("presupuesto/firmar") ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Mostrar modal de exito
                    const modal = new bootstrap.Modal(document.getElementById('modalExito'));
                    modal.show();
                } else {
                    alert('Error: ' + (result.message || 'No se pudo procesar la firma'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-patch-check me-2"></i>Aprobar y Firmar Presupuesto';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexion. Intente nuevamente.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-patch-check me-2"></i>Aprobar y Firmar Presupuesto';
            });
        });
    });
    </script>
</body>
</html>
