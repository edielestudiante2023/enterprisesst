<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contexto SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .header-gradient {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
        }
        .section-card {
            border-left: 4px solid #6366F1;
        }
        .form-check-input:checked {
            background-color: #6366F1;
            border-color: #6366F1;
        }
        .nivel-badge {
            font-size: 1.5rem;
            padding: 0.5rem 1.5rem;
        }
        .peligro-check {
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .peligro-check:hover {
            background-color: #f8f9fa;
        }
        .sticky-sidebar {
            position: sticky;
            top: 80px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark header-gradient sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-gear-fill me-2"></i>Contexto SST
            </a>
            <span class="navbar-text text-white">
                <strong><?= esc($cliente['nombre_cliente']) ?></strong>
            </span>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>">
                    <i class="bi bi-people-fill me-1"></i>Responsables SST
                </a>
                <a class="nav-link" href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>">
                    <i class="bi bi-graph-up me-1"></i>Indicadores SST
                </a>
                <a class="nav-link" href="<?= base_url('contexto') ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('contexto/guardar') ?>" method="POST" id="formContexto">
            <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">

            <div class="row">
                <!-- Columna principal -->
                <div class="col-lg-9">
                    <!-- Seccion 1: Datos de la Empresa -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-building text-primary me-2"></i>
                                1. Datos de la Empresa
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Razon Social</label>
                                    <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente']) ?>" disabled>
                                    <small class="text-muted">Dato del registro del cliente</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">NIT</label>
                                    <input type="text" class="form-control" value="<?= esc($cliente['nit_cliente'] ?? '') ?>" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Ciudad</label>
                                    <input type="text" class="form-control" value="<?= esc($cliente['ciudad_cliente'] ?? '') ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Representante Legal</label>
                                    <input type="text" class="form-control" value="<?= esc($cliente['nombre_rep_legal'] ?? '') ?>" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Cedula Rep. Legal</label>
                                    <input type="text" class="form-control" value="<?= esc($cliente['cedula_rep_legal'] ?? '') ?>" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Actividad Economica</label>
                                    <input type="text" class="form-control" value="<?= esc($cliente['codigo_actividad_economica'] ?? '') ?>" disabled>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Estos datos provienen del registro del cliente. Para modificarlos, edite el cliente directamente.
                            </div>
                        </div>
                    </div>

                    <!-- Seccion 2: Clasificacion Empresarial -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-diagram-3 text-primary me-2"></i>
                                2. Clasificacion Empresarial
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Sector Economico</label>
                                    <select name="sector_economico" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($sectores as $sector): ?>
                                            <option value="<?= esc($sector) ?>" <?= ($contexto['sector_economico'] ?? '') == $sector ? 'selected' : '' ?>>
                                                <?= esc($sector) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Codigo CIIU Secundario</label>
                                    <input type="text" name="codigo_ciiu_secundario" class="form-control"
                                           value="<?= esc($contexto['codigo_ciiu_secundario'] ?? '') ?>"
                                           placeholder="Ej: 4520">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-danger">Niveles de Riesgo ARL *</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php
                                        $nivelesRiesgoGuardados = json_decode($contexto['niveles_riesgo_arl'] ?? '[]', true) ?: [];
                                        // Compatibilidad con campo anterior (valor único)
                                        if (empty($nivelesRiesgoGuardados) && !empty($contexto['nivel_riesgo_arl'])) {
                                            $nivelesRiesgoGuardados = [$contexto['nivel_riesgo_arl']];
                                        }
                                        $nivelesDisponibles = [
                                            'I' => ['color' => 'success', 'desc' => 'Minimo'],
                                            'II' => ['color' => 'success', 'desc' => 'Bajo'],
                                            'III' => ['color' => 'warning', 'desc' => 'Medio'],
                                            'IV' => ['color' => 'danger', 'desc' => 'Alto'],
                                            'V' => ['color' => 'danger', 'desc' => 'Maximo']
                                        ];
                                        foreach ($nivelesDisponibles as $nivel => $info):
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="niveles_riesgo_arl[]"
                                                       value="<?= $nivel ?>" id="riesgo<?= $nivel ?>"
                                                       <?= in_array($nivel, $nivelesRiesgoGuardados) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="riesgo<?= $nivel ?>">
                                                    <span class="badge bg-<?= $info['color'] ?>">Riesgo <?= $nivel ?></span>
                                                    <small class="text-muted d-block"><?= $info['desc'] ?></small>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <small class="text-muted mt-1 d-block">Seleccione todos los niveles de riesgo que aplican</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-danger">Estandares Aplicables *</label>
                                    <select name="estandares_aplicables" class="form-select" required id="estandaresAplicables">
                                        <?php $estandaresActual = $contexto['estandares_aplicables'] ?? 60; ?>
                                        <option value="7" <?= $estandaresActual == 7 ? 'selected' : '' ?>>7 Estandares</option>
                                        <option value="21" <?= $estandaresActual == 21 ? 'selected' : '' ?>>21 Estandares</option>
                                        <option value="60" <?= $estandaresActual == 60 ? 'selected' : '' ?>>60 Estandares</option>
                                    </select>
                                    <small class="text-muted">Definido por el consultor</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ARL Actual</label>
                                    <select name="arl_actual" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($arls as $arl): ?>
                                            <option value="<?= esc($arl) ?>" <?= ($contexto['arl_actual'] ?? '') == $arl ? 'selected' : '' ?>>
                                                <?= esc($arl) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion 3: Tamano y Estructura -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-people text-primary me-2"></i>
                                3. Tamano y Estructura
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-danger">Total Trabajadores *</label>
                                    <input type="number" name="total_trabajadores" class="form-control" required min="1"
                                           value="<?= esc($contexto['total_trabajadores'] ?? 1) ?>" id="totalTrabajadores">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Trabajadores Directos</label>
                                    <input type="number" name="trabajadores_directos" class="form-control" min="0"
                                           value="<?= esc($contexto['trabajadores_directos'] ?? 1) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Trabajadores Temporales</label>
                                    <input type="number" name="trabajadores_temporales" class="form-control" min="0"
                                           value="<?= esc($contexto['trabajadores_temporales'] ?? 0) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Contratistas Permanentes</label>
                                    <input type="number" name="contratistas_permanentes" class="form-control" min="0"
                                           value="<?= esc($contexto['contratistas_permanentes'] ?? 0) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Numero de Sedes</label>
                                    <input type="number" name="numero_sedes" class="form-control" min="1"
                                           value="<?= esc($contexto['numero_sedes'] ?? 1) ?>">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold">Turnos de Trabajo</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php
                                        $turnosGuardados = json_decode($contexto['turnos_trabajo'] ?? '[]', true) ?: [];
                                        $turnosDisponibles = ['Diurno', 'Nocturno', 'Rotativo', 'Mixto', 'Fines de semana'];
                                        foreach ($turnosDisponibles as $turno):
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="turnos_trabajo[]"
                                                       value="<?= $turno ?>" id="turno<?= $turno ?>"
                                                       <?= in_array($turno, $turnosGuardados) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="turno<?= $turno ?>"><?= $turno ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion 4: Informacion SST -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-shield-check text-primary me-2"></i>
                                4. Informacion SST
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-danger">Responsable del SG-SST *</label>
                                    <select name="id_consultor_responsable" class="form-select" required>
                                        <option value="">Seleccione consultor...</option>
                                        <?php foreach ($consultores as $consultor): ?>
                                            <option value="<?= $consultor['id_consultor'] ?>"
                                                    data-cedula="<?= esc($consultor['cedula_consultor'] ?? '') ?>"
                                                    data-licencia="<?= esc($consultor['numero_licencia'] ?? '') ?>"
                                                    <?= ($contexto['id_consultor_responsable'] ?? '') == $consultor['id_consultor'] ? 'selected' : '' ?>>
                                                <?= esc($consultor['nombre_consultor']) ?>
                                                <?php if (!empty($consultor['numero_licencia'])): ?>
                                                    - Lic: <?= esc($consultor['numero_licencia']) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Consultor asignado como responsable del SG-SST</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Datos del Consultor Seleccionado</label>
                                    <div id="datosConsultorSeleccionado" class="alert alert-light border mb-0">
                                        <?php
                                        $consultorActual = null;
                                        foreach ($consultores as $c) {
                                            if (($contexto['id_consultor_responsable'] ?? '') == $c['id_consultor']) {
                                                $consultorActual = $c;
                                                break;
                                            }
                                        }
                                        if ($consultorActual): ?>
                                            <div><strong>Cedula:</strong> <?= esc($consultorActual['cedula_consultor'] ?? 'No registrada') ?></div>
                                            <div><strong>Licencia SST:</strong> <?= esc($consultorActual['numero_licencia'] ?? 'No registrada') ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">Seleccione un consultor</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3"><i class="bi bi-people-fill me-2"></i>Organos de Participacion</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tiene_copasst" value="1"
                                               id="tieneCopasst" <?= ($contexto['tiene_copasst'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tieneCopasst">
                                            <strong>COPASST</strong><br>
                                            <small class="text-muted">Comite Paritario</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tiene_vigia_sst" value="1"
                                               id="tieneVigia" <?= ($contexto['tiene_vigia_sst'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tieneVigia">
                                            <strong>Vigia SST</strong><br>
                                            <small class="text-muted">Empresas < 10 trabajadores</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tiene_comite_convivencia" value="1"
                                               id="tieneConvivencia" <?= ($contexto['tiene_comite_convivencia'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tieneConvivencia">
                                            <strong>Comite Convivencia</strong><br>
                                            <small class="text-muted">Obligatorio</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tiene_brigada_emergencias" value="1"
                                               id="tieneBrigada" <?= ($contexto['tiene_brigada_emergencias'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tieneBrigada">
                                            <strong>Brigada Emergencias</strong><br>
                                            <small class="text-muted">Plan de emergencias</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion 5: Peligros Identificados -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle text-primary me-2"></i>
                                5. Peligros Identificados
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Seleccione los peligros presentes en la empresa. Esta informacion se usara para generar programas de prevencion especificos.
                            </p>

                            <?php
                            $peligrosGuardados = json_decode($contexto['peligros_identificados'] ?? '[]', true) ?: [];
                            ?>

                            <div class="accordion" id="accordionPeligros">
                                <?php $index = 0; foreach ($peligrosDisponibles as $categoria => $peligros): $index++; ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button <?= $index > 1 ? 'collapsed' : '' ?>" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                                <strong><?= esc($categoria) ?></strong>
                                                <span class="badge bg-secondary ms-2" id="count<?= $index ?>">0</span>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index == 1 ? 'show' : '' ?>"
                                             data-bs-parent="#accordionPeligros">
                                            <div class="accordion-body">
                                                <div class="row g-2">
                                                    <?php foreach ($peligros as $clave => $nombre): ?>
                                                        <div class="col-md-6">
                                                            <div class="peligro-check">
                                                                <div class="form-check">
                                                                    <input class="form-check-input peligro-input" type="checkbox"
                                                                           name="peligros[]" value="<?= esc($clave) ?>"
                                                                           id="peligro_<?= esc($clave) ?>"
                                                                           data-categoria="<?= $index ?>"
                                                                           <?= in_array($clave, $peligrosGuardados) ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="peligro_<?= esc($clave) ?>">
                                                                        <?= esc($nombre) ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion 6: Contexto y Observaciones -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-journal-text text-primary me-2"></i>
                                6. Contexto y Observaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Documente aqui informacion cualitativa importante que no aparece en los documentos formales pero es clave para entender la realidad de la empresa y generar documentos SST relevantes.
                            </p>

                            <div class="alert alert-light border mb-3">
                                <h6 class="mb-2"><i class="bi bi-lightbulb me-2 text-warning"></i>Ejemplos de informacion util:</h6>
                                <ul class="mb-0 small">
                                    <li><strong>Operaciones reales:</strong> Actividades que se realizan pero no estan en la actividad economica formal</li>
                                    <li><strong>Cultura de seguridad:</strong> Nivel de compromiso de la direccion y los trabajadores con el SST</li>
                                    <li><strong>Exposicion a riesgos:</strong> Riesgos especificos observados en campo que no estan documentados</li>
                                    <li><strong>Estructura informal:</strong> Como funciona realmente la organizacion vs el organigrama</li>
                                    <li><strong>Contexto del sector:</strong> Particularidades del sector o region que afectan la seguridad</li>
                                    <li><strong>Historial de incidentes:</strong> Accidentes o casi-accidentes relevantes no reportados</li>
                                    <li><strong>Recursos disponibles:</strong> Limitaciones reales de presupuesto, tiempo o personal para SST</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Observaciones y Contexto de la Empresa</label>
                                <textarea name="observaciones_contexto" class="form-control" rows="8"
                                          placeholder="Describa aqui el contexto real de la empresa, observaciones de campo, cultura organizacional, riesgos no documentados, limitaciones, y cualquier informacion relevante para generar documentos SST personalizados..."
                                ><?= esc($contexto['observaciones_contexto'] ?? '') ?></textarea>
                                <small class="text-muted">
                                    Esta informacion sera utilizada por la IA para generar documentos mas relevantes y especificos para la empresa.
                                    <span class="text-info">Maximo 5000 caracteres.</span>
                                </small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Caracteres usados:</small>
                                            <span id="contadorCaracteres" class="fw-bold">0</span> / 5000
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Ultima actualizacion:</small>
                                            <span class="fw-bold">
                                                <?= !empty($contexto['updated_at']) ? date('d/m/Y H:i', strtotime($contexto['updated_at'])) : 'Sin guardar' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion 7: Firmantes de Documentos -->
                    <div class="card section-card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-pen text-primary me-2"></i>
                                7. Firmantes de Documentos
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Configure quienes firmaran los documentos del SG-SST. Para empresas grandes, puede configurar un Delegado SST que firma antes del Representante Legal.
                            </p>

                            <!-- Toggle Delegado SST -->
                            <div class="alert alert-light border mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requiere_delegado_sst" value="1"
                                           id="requiereDelegadoSst" <?= ($contexto['requiere_delegado_sst'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="requiereDelegadoSst">
                                        <strong>Requiere Delegado SST</strong><br>
                                        <small class="text-muted">
                                            Active esta opcion si los documentos deben ser firmados por un Delegado SST (ej: Gerente RRHH, Director Administrativo) antes del Representante Legal.
                                            <span class="text-info">Flujo: Delegado SST firma primero → luego Representante Legal</span>
                                        </small>
                                    </label>
                                </div>
                            </div>

                            <!-- Datos del Delegado SST (condicional) -->
                            <div id="seccionDelegadoSst" class="<?= ($contexto['requiere_delegado_sst'] ?? 0) ? '' : 'd-none' ?>">
                                <h6 class="mb-3 border-bottom pb-2">
                                    <i class="bi bi-person-badge me-2 text-warning"></i>
                                    Delegado SST (Firma Primero)
                                </h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nombre Completo</label>
                                        <input type="text" name="delegado_sst_nombre" class="form-control"
                                               value="<?= esc($contexto['delegado_sst_nombre'] ?? '') ?>"
                                               placeholder="Ej: Maria Garcia Lopez">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Cargo</label>
                                        <input type="text" name="delegado_sst_cargo" class="form-control"
                                               value="<?= esc($contexto['delegado_sst_cargo'] ?? '') ?>"
                                               placeholder="Ej: Gerente de Recursos Humanos">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Correo Electronico</label>
                                        <input type="email" name="delegado_sst_email" class="form-control"
                                               value="<?= esc($contexto['delegado_sst_email'] ?? '') ?>"
                                               placeholder="delegado@empresa.com">
                                        <small class="text-muted">Se enviara la solicitud de firma a este correo</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Numero de Cedula</label>
                                        <input type="text" name="delegado_sst_cedula" class="form-control"
                                               value="<?= esc($contexto['delegado_sst_cedula'] ?? '') ?>"
                                               placeholder="Ej: 1234567890">
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del Representante Legal -->
                            <h6 class="mb-3 border-bottom pb-2">
                                <i class="bi bi-person-check me-2 text-success"></i>
                                Representante Legal <span id="firmaFinalLabel" class="badge bg-success ms-2"><?= ($contexto['requiere_delegado_sst'] ?? 0) ? 'Firma Final' : 'Firma Unica' ?></span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombre Completo</label>
                                    <input type="text" name="representante_legal_nombre" class="form-control"
                                           value="<?= esc($contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '') ?>"
                                           placeholder="Nombre del representante legal">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Cargo</label>
                                    <input type="text" name="representante_legal_cargo" class="form-control"
                                           value="<?= esc($contexto['representante_legal_cargo'] ?? 'Representante Legal') ?>"
                                           placeholder="Ej: Gerente General">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Correo Electronico</label>
                                    <input type="email" name="representante_legal_email" class="form-control"
                                           value="<?= esc($contexto['representante_legal_email'] ?? $cliente['email_cliente'] ?? '') ?>"
                                           placeholder="representante@empresa.com">
                                    <small class="text-muted">Se enviara la solicitud de firma a este correo</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Numero de Cedula</label>
                                    <input type="text" name="representante_legal_cedula" class="form-control"
                                           value="<?= esc($contexto['representante_legal_cedula'] ?? $cliente['cedula_rep_legal'] ?? '') ?>"
                                           placeholder="Ej: 1234567890">
                                </div>
                            </div>

                            <!-- Resumen del flujo de firmas -->
                            <div class="alert alert-info mt-4 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Flujo de Firmas:</strong>
                                <div id="flujoFirmasResumen" class="mt-2">
                                    <?php if ($contexto['requiere_delegado_sst'] ?? 0): ?>
                                        <span class="badge bg-warning text-dark me-2">1. Delegado SST</span>
                                        <i class="bi bi-arrow-right me-2"></i>
                                        <span class="badge bg-success">2. Representante Legal</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Representante Legal (Firma Unica)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boton Guardar -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                        <a href="<?= base_url('contexto') ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Guardar Contexto
                        </button>
                    </div>
                </div>

                <!-- Columna lateral - Resumen -->
                <div class="col-lg-3">
                    <div class="sticky-sidebar">
                        <!-- Nivel de Estandares -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Nivel de Estandares</h6>
                                <div id="nivelEstandaresDisplay">
                                    <?php
                                    $nivel = $contexto['estandares_aplicables'] ?? 60;
                                    $colorNivel = $nivel == 7 ? 'success' : ($nivel == 21 ? 'warning' : 'primary');
                                    ?>
                                    <span class="badge bg-<?= $colorNivel ?> nivel-badge"><?= $nivel ?></span>
                                </div>
                                <small class="text-muted d-block mt-2">Estandares aplicables</small>
                                <small class="text-muted">Resolucion 0312/2019</small>
                            </div>
                        </div>

                        <!-- Calculo en tiempo real -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Calculo Automatico</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted">Trabajadores:</small>
                                    <span id="displayTrabajadores" class="float-end fw-bold"><?= $contexto['total_trabajadores'] ?? 1 ?></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Riesgo ARL:</small>
                                    <span id="displayRiesgo" class="float-end fw-bold"><?= $contexto['nivel_riesgo_arl'] ?? 'I' ?></span>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <small class="text-muted d-block">Nivel calculado:</small>
                                    <span id="nivelCalculado" class="badge bg-<?= $colorNivel ?> fs-5"><?= $nivel ?> estandares</span>
                                </div>
                            </div>
                        </div>

                        <!-- Peligros seleccionados -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Peligros</h6>
                            </div>
                            <div class="card-body">
                                <div id="contadorPeligros">
                                    <span class="badge bg-secondary fs-6"><?= count($peligrosGuardados) ?></span>
                                    <small class="text-muted ms-2">seleccionados</small>
                                </div>
                            </div>
                        </div>

                        <!-- Navegacion rapida -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-list me-2"></i>Secciones</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    1. Datos de la Empresa
                                </a>
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    2. Clasificacion Empresarial
                                </a>
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    3. Tamano y Estructura
                                </a>
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    4. Informacion SST
                                </a>
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    5. Peligros Identificados
                                </a>
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    6. Contexto y Observaciones
                                </a>
                                <a href="#" class="list-group-item list-group-item-action py-2">
                                    7. Firmantes de Documentos
                                </a>
                            </div>
                        </div>

                        <!-- Flujo de Firmas -->
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-pen me-2"></i>Flujo Firmas</h6>
                            </div>
                            <div class="card-body" id="cardFlujoFirmas">
                                <?php if ($contexto['requiere_delegado_sst'] ?? 0): ?>
                                    <div class="text-center">
                                        <span class="badge bg-warning text-dark d-block mb-2">1. Delegado SST</span>
                                        <i class="bi bi-arrow-down"></i>
                                        <span class="badge bg-success d-block mt-2">2. Rep. Legal</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <span class="badge bg-success">Rep. Legal</span>
                                        <small class="d-block text-muted mt-1">Firma unica</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Modulos Relacionados -->
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-grid me-2"></i>Modulos SST</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-people-fill me-2"></i>Responsables SST
                                    </a>
                                    <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-graph-up me-2"></i>Indicadores SST
                                    </a>
                                    <a href="<?= base_url('estandares/' . $cliente['id_cliente']) ?>" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-check2-square me-2"></i>Estandares
                                    </a>
                                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-folder me-2"></i>Documentacion
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Actualizar displays en tiempo real
        function actualizarDisplays() {
            const trabajadores = parseInt(document.getElementById('totalTrabajadores').value) || 1;
            const estandares = document.getElementById('estandaresAplicables').value;

            // Obtener niveles de riesgo seleccionados
            const riesgosChecked = document.querySelectorAll('input[name="niveles_riesgo_arl[]"]:checked');
            const riesgosSeleccionados = Array.from(riesgosChecked).map(cb => cb.value).join(', ');

            let color = 'primary';
            if (estandares == 7) color = 'success';
            else if (estandares == 21) color = 'warning';

            // Actualizar displays
            document.getElementById('displayTrabajadores').textContent = trabajadores;
            document.getElementById('displayRiesgo').textContent = riesgosSeleccionados || 'Ninguno';
            document.getElementById('nivelCalculado').textContent = estandares + ' estandares';
            document.getElementById('nivelCalculado').className = 'badge bg-' + color + ' fs-5';

            document.getElementById('nivelEstandaresDisplay').innerHTML =
                '<span class="badge bg-' + color + ' nivel-badge">' + estandares + '</span>';
        }

        document.getElementById('totalTrabajadores').addEventListener('input', actualizarDisplays);
        document.querySelectorAll('input[name="niveles_riesgo_arl[]"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', actualizarDisplays);
        });
        document.getElementById('estandaresAplicables').addEventListener('change', actualizarDisplays);

        // Contador de peligros por categoria
        function actualizarContadoresPeligros() {
            let total = 0;
            for (let i = 1; i <= 7; i++) {
                const checkboxes = document.querySelectorAll('.peligro-input[data-categoria="' + i + '"]:checked');
                const count = checkboxes.length;
                const badge = document.getElementById('count' + i);
                if (badge) {
                    badge.textContent = count;
                    badge.className = 'badge ms-2 ' + (count > 0 ? 'bg-primary' : 'bg-secondary');
                }
                total += count;
            }
            document.getElementById('contadorPeligros').innerHTML =
                '<span class="badge bg-' + (total > 0 ? 'primary' : 'secondary') + ' fs-6">' + total + '</span>' +
                '<small class="text-muted ms-2">seleccionados</small>';
        }

        document.querySelectorAll('.peligro-input').forEach(function(checkbox) {
            checkbox.addEventListener('change', actualizarContadoresPeligros);
        });

        // Inicializar contadores
        actualizarContadoresPeligros();

        // Toggle Delegado SST
        document.getElementById('requiereDelegadoSst').addEventListener('change', function() {
            const seccion = document.getElementById('seccionDelegadoSst');
            const firmaLabel = document.getElementById('firmaFinalLabel');
            const flujoResumen = document.getElementById('flujoFirmasResumen');
            const cardFlujo = document.getElementById('cardFlujoFirmas');

            if (this.checked) {
                seccion.classList.remove('d-none');
                firmaLabel.textContent = 'Firma Final';
                flujoResumen.innerHTML = `
                    <span class="badge bg-warning text-dark me-2">1. Delegado SST</span>
                    <i class="bi bi-arrow-right me-2"></i>
                    <span class="badge bg-success">2. Representante Legal</span>
                `;
                cardFlujo.innerHTML = `
                    <div class="text-center">
                        <span class="badge bg-warning text-dark d-block mb-2">1. Delegado SST</span>
                        <i class="bi bi-arrow-down"></i>
                        <span class="badge bg-success d-block mt-2">2. Rep. Legal</span>
                    </div>
                `;
            } else {
                seccion.classList.add('d-none');
                firmaLabel.textContent = 'Firma Unica';
                flujoResumen.innerHTML = `
                    <span class="badge bg-success">Representante Legal (Firma Unica)</span>
                `;
                cardFlujo.innerHTML = `
                    <div class="text-center">
                        <span class="badge bg-success">Rep. Legal</span>
                        <small class="d-block text-muted mt-1">Firma unica</small>
                    </div>
                `;
            }
        });

        // Actualizar datos del consultor seleccionado
        document.querySelector('select[name="id_consultor_responsable"]').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const container = document.getElementById('datosConsultorSeleccionado');

            if (this.value) {
                const cedula = selected.dataset.cedula || 'No registrada';
                const licencia = selected.dataset.licencia || 'No registrada';
                container.innerHTML = `
                    <div><strong>Cedula:</strong> ${cedula}</div>
                    <div><strong>Licencia SST:</strong> ${licencia}</div>
                `;
            } else {
                container.innerHTML = '<span class="text-muted">Seleccione un consultor</span>';
            }
        });

        // Contador de caracteres para observaciones
        const textareaObservaciones = document.querySelector('textarea[name="observaciones_contexto"]');
        const contadorCaracteres = document.getElementById('contadorCaracteres');

        function actualizarContadorCaracteres() {
            const length = textareaObservaciones.value.length;
            contadorCaracteres.textContent = length;

            if (length > 4500) {
                contadorCaracteres.classList.add('text-danger');
            } else if (length > 3500) {
                contadorCaracteres.classList.remove('text-danger');
                contadorCaracteres.classList.add('text-warning');
            } else {
                contadorCaracteres.classList.remove('text-danger', 'text-warning');
            }
        }

        textareaObservaciones.addEventListener('input', actualizarContadorCaracteres);
        textareaObservaciones.setAttribute('maxlength', '5000');

        // Inicializar contador
        actualizarContadorCaracteres();
    </script>
</body>
</html>
