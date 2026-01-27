<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
