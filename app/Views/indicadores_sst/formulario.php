<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .toast-container { z-index: 9999; }
        .toast { min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,.15); margin-bottom: 8px; }
        .ia-assist-panel { border: 2px dashed #0d6efd; border-radius: 0.5rem; background: #f0f7ff; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-graph-up me-2"></i>Indicadores SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="bi bi-graph-up me-2"></i><?= $titulo ?>
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('clientes') ?>">Clientes</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>"><?= esc($cliente['nombre_cliente']) ?></a></li>
                        <li class="breadcrumb-item active"><?= $indicador ? 'Editar' : 'Agregar' ?></li>
                    </ol>
                </nav>
            </div>
            <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/guardar') ?>" method="POST">
                            <?php if ($indicador): ?>
                                <input type="hidden" name="id_indicador" value="<?= $indicador['id_indicador'] ?>">
                            <?php endif; ?>

                            <!-- Asistente IA -->
                            <?php if (!$indicador): ?>
                            <div class="ia-assist-panel p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-primary"><i class="bi bi-robot me-2"></i>Asistente IA</h6>
                                    <small class="text-muted"><?= esc($cliente['nombre_cliente']) ?> | <?= $estandares ?> Est.</small>
                                </div>
                                <p class="text-muted small mb-2">Describe que tipo de indicador necesitas y la IA llenara el formulario completo.</p>
                                <div class="row g-2 align-items-end">
                                    <div class="col">
                                        <input type="text" id="instruccionesIAForm" class="form-control form-control-sm"
                                               placeholder="Ej: Indicador de cobertura de examenes medicos ocupacionales...">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-primary btn-sm" id="btnGenerarIAForm" onclick="generarConIA()">
                                            <i class="bi bi-robot me-1"></i>Generar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Información básica -->
                            <h6 class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i>Información del Indicador</h6>

                            <div class="mb-3">
                                <label class="form-label">Nombre del Indicador <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_indicador" class="form-control"
                                       value="<?= $indicador['nombre_indicador'] ?? '' ?>"
                                       placeholder="Ej: Cumplimiento del Plan de Capacitación" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Categoria / Programa <span class="text-danger">*</span></label>
                                    <select name="categoria" class="form-select" required>
                                        <?php
                                        $categoriaActual = $indicador['categoria'] ?? $categoriaPreseleccionada ?? 'otro';
                                        foreach ($categorias as $key => $info):
                                        ?>
                                            <option value="<?= $key ?>"
                                                <?= ($categoriaActual === $key) ? 'selected' : '' ?>>
                                                <?= esc($info['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Agrupe el indicador por programa del SG-SST</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Indicador</label>
                                    <select name="tipo_indicador" class="form-select">
                                        <?php foreach ($tiposIndicador as $key => $nombre): ?>
                                            <option value="<?= $key ?>"
                                                <?= ($indicador && $indicador['tipo_indicador'] === $key) ? 'selected' : '' ?>>
                                                <?= esc($nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        Estructura: recursos y organizacion |
                                        Proceso: ejecucion de actividades |
                                        Resultado: impacto en SST
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Fase PHVA</label>
                                    <select name="phva" class="form-select">
                                        <?php foreach ($fasesPhva as $key => $nombre): ?>
                                            <option value="<?= $key ?>"
                                                <?= ($indicador && $indicador['phva'] === $key) ? 'selected' : '' ?>>
                                                <?= esc($nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Numeral Res. 0312</label>
                                    <input type="text" name="numeral_resolucion" class="form-control"
                                           value="<?= $indicador['numeral_resolucion'] ?? '' ?>"
                                           placeholder="Ej: 2.11.1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Periodicidad</label>
                                    <select name="periodicidad" class="form-select">
                                        <?php foreach ($periodicidades as $key => $nombre): ?>
                                            <option value="<?= $key ?>"
                                                <?= ($indicador && $indicador['periodicidad'] === $key) ? 'selected' : '' ?>>
                                                <?= esc($nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Fórmula y Meta -->
                            <h6 class="text-muted mb-3"><i class="bi bi-calculator me-1"></i>Fórmula y Meta</h6>

                            <div class="mb-3">
                                <label class="form-label">Fórmula</label>
                                <input type="text" name="formula" class="form-control"
                                       value="<?= $indicador['formula'] ?? '' ?>"
                                       placeholder="Ej: (Capacitaciones ejecutadas / Capacitaciones programadas) x 100">
                                <div class="form-text">Describe cómo se calcula el indicador</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta</label>
                                    <input type="number" name="meta" class="form-control" step="0.01"
                                           value="<?= $indicador['meta'] ?? '' ?>"
                                           placeholder="Ej: 100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Unidad de Medida</label>
                                    <select name="unidad_medida" class="form-select">
                                        <option value="%" <?= ($indicador['unidad_medida'] ?? '%') === '%' ? 'selected' : '' ?>>% (Porcentaje)</option>
                                        <option value="indice" <?= ($indicador['unidad_medida'] ?? '') === 'indice' ? 'selected' : '' ?>>Indice</option>
                                        <option value="cantidad" <?= ($indicador['unidad_medida'] ?? '') === 'cantidad' ? 'selected' : '' ?>>Cantidad</option>
                                        <option value="dias" <?= ($indicador['unidad_medida'] ?? '') === 'dias' ? 'selected' : '' ?>>Dias</option>
                                        <option value="horas" <?= ($indicador['unidad_medida'] ?? '') === 'horas' ? 'selected' : '' ?>>Horas</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"
                                          placeholder="Notas adicionales sobre el indicador..."><?= $indicador['observaciones'] ?? '' ?></textarea>
                            </div>

                            <!-- Estado -->
                            <div class="form-check mb-4">
                                <input type="checkbox" name="activo" value="1" class="form-check-input"
                                       <?= ($indicador['activo'] ?? 1) ? 'checked' : '' ?> id="checkActivo">
                                <label class="form-check-label" for="checkActivo">
                                    Indicador activo
                                </label>
                            </div>

                            <!-- Datos para Ficha Técnica -->
                            <div class="card bg-light border mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-text me-1"></i>Datos para Ficha Técnica (Opcional)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info py-2 mb-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Estos datos se utilizan para generar la Ficha Técnica formal del indicador.
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Definición del Indicador</label>
                                        <textarea name="definicion" class="form-control" rows="3"
                                                  placeholder="Describa qué mide este indicador y su propósito..."><?= esc($indicador['definicion'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Interpretación del Resultado</label>
                                        <textarea name="interpretacion" class="form-control" rows="2"
                                                  placeholder="Explique cómo interpretar el valor obtenido..."><?= esc($indicador['interpretacion'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Origen de los Datos</label>
                                        <input type="text" name="origen_datos" class="form-control"
                                               value="<?= esc($indicador['origen_datos'] ?? '') ?>"
                                               placeholder="Ej: Registro de accidentes, nómina, FURAT">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Cargo Responsable de la Medición</label>
                                        <input type="text" name="cargo_responsable" class="form-control"
                                               value="<?= esc($indicador['cargo_responsable'] ?? '') ?>"
                                               placeholder="Ej: Responsable del SG-SST">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">Cargos que Conocen el Resultado</label>
                                        <input type="text" name="cargos_conocer_resultado" class="form-control"
                                               value="<?= esc($indicador['cargos_conocer_resultado'] ?? '') ?>"
                                               placeholder="Ej: Gerencia, COPASST, trabajadores">
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i><?= $indicador ? 'Actualizar' : 'Guardar' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Histórico de mediciones (solo en edición) -->
                <?php if ($indicador && !empty($historico)): ?>
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history me-1"></i>Histórico de Mediciones
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Periodo</th>
                                            <th>Numerador</th>
                                            <th>Denominador</th>
                                            <th>Resultado</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historico as $med): ?>
                                            <tr>
                                                <td><?= esc($med['periodo']) ?></td>
                                                <td><?= $med['valor_numerador'] ?? '-' ?></td>
                                                <td><?= $med['valor_denominador'] ?? '-' ?></td>
                                                <td><strong><?= $med['valor_resultado'] !== null ? number_format($med['valor_resultado'], 1) : '-' ?></strong></td>
                                                <td>
                                                    <?php if ($med['cumple_meta'] === null): ?>
                                                        <span class="badge bg-secondary">-</span>
                                                    <?php elseif ($med['cumple_meta'] == 1): ?>
                                                        <span class="badge bg-success">Cumple</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">No cumple</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><small><?= date('d/m/Y', strtotime($med['fecha_registro'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toast stack -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastStack"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!$indicador): ?>
    <script>
    const BASE_URL = '<?= base_url() ?>';
    const ID_CLIENTE = <?= $cliente['id_cliente'] ?>;

    function mostrarToast(tipo, titulo, mensaje) {
        const stack = document.getElementById('toastStack');
        const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
        const ahora = new Date().toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        const configs = {
            success: { bg: 'bg-success', icon: 'bi-check-circle-fill', delay: 5000, autohide: true },
            error:   { bg: 'bg-danger',  icon: 'bi-x-circle-fill',     delay: 8000, autohide: true },
            ia:      { bg: 'bg-primary', icon: 'bi-robot',             delay: 5000, autohide: true },
            progress:{ bg: 'bg-primary', icon: '',                     delay: 60000, autohide: false }
        };
        const cfg = configs[tipo] || configs.ia;
        const spinnerHTML = tipo === 'progress'
            ? '<span class="spinner-border spinner-border-sm text-white"></span>'
            : `<i class="bi ${cfg.icon}"></i>`;
        const html = `
            <div id="${id}" class="toast" role="alert" data-bs-autohide="${cfg.autohide}" data-bs-delay="${cfg.delay}">
                <div class="toast-header ${cfg.bg} text-white">
                    ${spinnerHTML}
                    <strong class="me-auto ms-2">${titulo}</strong>
                    <small>${ahora}</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${mensaje}</div>
            </div>`;
        stack.insertAdjacentHTML('beforeend', html);
        const el = document.getElementById(id);
        const instance = new bootstrap.Toast(el);
        el.addEventListener('hidden.bs.toast', () => el.remove());
        instance.show();
        return { id, element: el, instance };
    }

    function cerrarToast(ref) { if (ref && ref.instance) ref.instance.hide(); }

    async function generarConIA() {
        const instrucciones = document.getElementById('instruccionesIAForm').value.trim();
        const categoriaSelect = document.querySelector('select[name="categoria"]');
        const categoria = categoriaSelect.value;

        const btn = document.getElementById('btnGenerarIAForm');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        const progreso = mostrarToast('progress', 'Generando...', 'La IA esta creando un indicador para la categoria seleccionada...');

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/ia/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    instrucciones: instrucciones || `Genera un indicador para la categoria ${categoria}`,
                    categorias: [categoria]
                })
            });
            const data = await resp.json();
            cerrarToast(progreso);

            if (!data.success || !data.data.indicadores || data.data.indicadores.length === 0) {
                throw new Error(data.message || 'No se generaron indicadores');
            }

            // Take the first non-duplicate indicator
            const indicadores = data.data.indicadores;
            let ind = indicadores.find(i => !i.ya_existe) || indicadores[0];

            // Fill form fields
            setVal('nombre_indicador', ind.nombre || '');
            setSelect('categoria', ind.categoria || categoria);
            setSelect('tipo_indicador', ind.tipo || 'proceso');
            setSelect('phva', ind.phva || 'verificar');
            setVal('numeral_resolucion', ind.numeral || '');
            setSelect('periodicidad', ind.periodicidad || 'trimestral');
            setVal('formula', ind.formula || '');
            setVal('meta', ind.meta ?? '');
            setSelectByValue('unidad_medida', ind.unidad || '%');
            setVal('definicion', ind.definicion || '');
            setVal('interpretacion', ind.interpretacion || '');
            setVal('origen_datos', ind.origen_datos || '');
            setVal('cargo_responsable', ind.cargo_responsable || '');
            setVal('cargos_conocer_resultado', ind.cargos_conocer_resultado || '');
            setVal('observaciones', ind.descripcion || '');

            mostrarToast('ia', 'Indicador Generado', `"${ind.nombre}" - Revise y ajuste los campos antes de guardar.`);

            // Highlight filled fields briefly
            document.querySelectorAll('.form-control, .form-select').forEach(el => {
                if (el.value) {
                    el.style.transition = 'background-color 0.5s';
                    el.style.backgroundColor = '#d1ecf1';
                    setTimeout(() => { el.style.backgroundColor = ''; }, 2000);
                }
            });

        } catch (e) {
            cerrarToast(progreso);
            mostrarToast('error', 'Error al Generar', e.message);
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot me-1"></i>Generar';
    }

    function setVal(name, value) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    }

    function setSelect(name, value) {
        const el = document.querySelector(`select[name="${name}"]`);
        if (!el) return;
        // Try exact match first
        for (let opt of el.options) {
            if (opt.value === value) { el.value = value; return; }
        }
        // Try case-insensitive
        for (let opt of el.options) {
            if (opt.value.toLowerCase() === value.toLowerCase()) { el.value = opt.value; return; }
        }
    }

    function setSelectByValue(name, value) {
        const el = document.querySelector(`select[name="${name}"]`);
        if (!el) return;
        // Try exact match
        for (let opt of el.options) {
            if (opt.value === value) { el.value = value; return; }
        }
        // Try partial match (e.g. "%" matches "% (Porcentaje)")
        for (let opt of el.options) {
            if (opt.value.includes(value) || value.includes(opt.value)) { el.value = opt.value; return; }
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>
