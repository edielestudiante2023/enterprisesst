<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicion Masiva - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .fila-indicador input { font-size: 0.85rem; }
        .fila-indicador .resultado-cell { font-weight: 600; }
        .fila-indicador.cumple { background-color: rgba(25, 135, 84, 0.05); }
        .fila-indicador.no-cumple { background-color: rgba(220, 53, 69, 0.05); }
        .fila-indicador.ya-registrado { opacity: 0.7; }
        .badge-ya { font-size: 0.65rem; }
        .tabla-masiva th { font-size: 0.8rem; white-space: nowrap; }
        .tabla-masiva td { vertical-align: middle; }
        .grupo-categoria td { background-color: #e8f4fd !important; font-weight: 700; color: #0d6efd; border-left: 4px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-grid-3x3 me-2"></i>Medicion Masiva
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i><?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Controles -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Periodo</label>
                        <input type="month" id="inputPeriodo" class="form-control form-control-sm" value="<?= esc($periodo) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Fecha de Medicion</label>
                        <input type="date" id="inputFecha" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="cargarPeriodo()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Cargar Periodo
                        </button>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-info me-2" id="badgeTotal">0 indicadores</span>
                        <span class="badge bg-success me-2" id="badgeLlenos">0 completos</span>
                        <button type="button" class="btn btn-success btn-sm" onclick="guardarTodas()" id="btnGuardar">
                            <i class="bi bi-save me-1"></i>Guardar Todas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de mediciones -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover tabla-masiva mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:30px" class="text-center">#</th>
                                <th>Indicador</th>
                                <th style="width:90px">Meta</th>
                                <th style="width:120px">Numerador</th>
                                <th style="width:120px">Denominador</th>
                                <th style="width:100px" class="text-center">Resultado</th>
                                <th style="width:70px" class="text-center">Cumple</th>
                                <th style="width:180px">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyMediciones">
                            <?php
                            $catActual = '';
                            $n = 0;
                            foreach ($indicadores as $ind):
                                $id = $ind['id_indicador'];
                                $cat = $ind['categoria'] ?? 'otro';
                                $catInfo = $categorias[$cat] ?? ['nombre' => ucfirst($cat), 'icono' => 'bi-folder', 'color' => 'secondary'];
                                $medExistente = $mediciones[$id] ?? null;

                                // Header de categoria
                                if ($cat !== $catActual):
                                    $catActual = $cat;
                            ?>
                                <tr class="grupo-categoria">
                                    <td colspan="8">
                                        <i class="bi <?= $catInfo['icono'] ?> me-1"></i><?= esc($catInfo['nombre']) ?>
                                    </td>
                                </tr>
                            <?php endif; $n++; ?>
                                <tr class="fila-indicador <?= $medExistente ? 'ya-registrado' : '' ?>"
                                    data-id="<?= $id ?>"
                                    data-meta="<?= (float)($ind['meta'] ?? 0) ?>"
                                    data-unidad="<?= esc($ind['unidad_medida'] ?? '%') ?>">
                                    <td class="text-center text-muted small"><?= $n ?></td>
                                    <td>
                                        <span class="small"><?= esc($ind['nombre_indicador']) ?></span>
                                        <?php if ($medExistente): ?>
                                            <span class="badge bg-warning text-dark badge-ya ms-1">Ya registrado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= $ind['meta'] !== null ? $ind['meta'] . esc($ind['unidad_medida'] ?? '%') : '-' ?></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm input-num"
                                               step="0.01" placeholder="Num."
                                               value="<?= $medExistente ? esc($medExistente['valor_numerador'] ?? '') : '' ?>"
                                               onchange="calcularFila(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm input-den"
                                               step="0.01" placeholder="Den."
                                               value="<?= $medExistente ? esc($medExistente['valor_denominador'] ?? '') : '' ?>"
                                               onchange="calcularFila(this)">
                                    </td>
                                    <td class="text-center resultado-cell">
                                        <span class="resultado-valor">
                                            <?php if ($medExistente && $medExistente['valor_resultado'] !== null): ?>
                                                <?= number_format((float)$medExistente['valor_resultado'], 1) ?>%
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="text-center cumple-cell">-</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm input-obs"
                                               placeholder="Obs."
                                               value="<?= $medExistente ? esc($medExistente['observaciones'] ?? '') : '' ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const BASE_URL = '<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>';

    // Calcular resultado y cumplimiento de una fila
    function calcularFila(input) {
        const tr = input.closest('tr');
        const num = parseFloat(tr.querySelector('.input-num').value);
        const den = parseFloat(tr.querySelector('.input-den').value);
        const meta = parseFloat(tr.dataset.meta) || 0;
        const resultadoSpan = tr.querySelector('.resultado-valor');
        const cumpleCell = tr.querySelector('.cumple-cell');

        tr.classList.remove('cumple', 'no-cumple');

        if (!isNaN(num) && !isNaN(den) && den > 0) {
            const resultado = (num / den) * 100;
            resultadoSpan.textContent = resultado.toFixed(1) + '%';

            const cumple = resultado >= meta;
            cumpleCell.innerHTML = cumple
                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                : '<i class="bi bi-x-circle-fill text-danger"></i>';
            tr.classList.add(cumple ? 'cumple' : 'no-cumple');
        } else {
            resultadoSpan.textContent = '-';
            cumpleCell.textContent = '-';
        }

        actualizarContadores();
    }

    // Actualizar badges de conteo
    function actualizarContadores() {
        const filas = document.querySelectorAll('.fila-indicador');
        let total = filas.length;
        let llenos = 0;

        filas.forEach(tr => {
            const num = tr.querySelector('.input-num').value;
            const den = tr.querySelector('.input-den').value;
            if (num && den) llenos++;
        });

        document.getElementById('badgeTotal').textContent = total + ' indicadores';
        document.getElementById('badgeLlenos').textContent = llenos + ' completos';
    }

    // Cargar mediciones existentes de otro periodo
    function cargarPeriodo() {
        const periodo = document.getElementById('inputPeriodo').value;
        if (!periodo) return;
        window.location.href = BASE_URL + '/medicion-masiva?periodo=' + periodo;
    }

    // Guardar todas las mediciones
    async function guardarTodas() {
        const filas = document.querySelectorAll('.fila-indicador');
        const mediciones = [];

        filas.forEach(tr => {
            const num = tr.querySelector('.input-num').value;
            const den = tr.querySelector('.input-den').value;
            if (num && den) {
                mediciones.push({
                    id_indicador: tr.dataset.id,
                    valor_numerador: parseFloat(num),
                    valor_denominador: parseFloat(den),
                    observaciones: tr.querySelector('.input-obs').value || null
                });
            }
        });

        if (mediciones.length === 0) {
            alert('No hay mediciones para guardar. Complete al menos un par numerador/denominador.');
            return;
        }

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        try {
            const resp = await fetch(BASE_URL + '/medicion-masiva/guardar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    mediciones: mediciones,
                    periodo: document.getElementById('inputPeriodo').value,
                    fecha_medicion: document.getElementById('inputFecha').value
                })
            });

            const data = await resp.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        } catch (e) {
            alert('Error de conexion: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i>Guardar Todas';
        }
    }

    // Inicializar: calcular filas pre-llenadas y contadores
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.fila-indicador').forEach(tr => {
            const num = tr.querySelector('.input-num').value;
            const den = tr.querySelector('.input-den').value;
            if (num && den) calcularFila(tr.querySelector('.input-num'));
        });
        actualizarContadores();
    });
    </script>
</body>
</html>
