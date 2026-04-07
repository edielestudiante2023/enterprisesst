<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($mode === 'edit') ? 'Editar' : 'Nuevo' ?> Informe de Avances</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root { --primary-dark: #1c2437; --gold-primary: #bd9751; --gold-secondary: #d4af37; --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        body { background: var(--gradient-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .navbar-custom { background: #fff; box-shadow: 0 8px 32px rgba(28,36,55,0.15); padding: 15px 0; border-bottom: 2px solid var(--gold-primary); }
        .card-section { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-section .card-header { background: var(--primary-dark); color: #fff; border-radius: 16px 16px 0 0; font-weight: 600; }
        .card-section .card-header i { color: var(--gold-primary); }
        .btn-gold { background: var(--gold-primary); color: #fff; border: none; font-weight: 600; }
        .btn-gold:hover { background: var(--gold-secondary); color: #fff; }
        .btn-ia { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; font-weight: 600; }
        .btn-ia:hover { opacity: 0.9; color: #fff; }
        .metric-box { background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; border: 1px solid #e9ecef; }
        .metric-box .value { font-size: 1.8rem; font-weight: 700; color: var(--primary-dark); }
        .metric-box .label { font-size: 0.85rem; color: #6c757d; }
        .progress-custom { height: 24px; border-radius: 12px; }
        .progress-custom .progress-bar { border-radius: 12px; font-weight: 600; font-size: 0.8rem; }
        .badge-estado { font-size: 0.95rem; padding: 6px 16px; }
        .soporte-group { border: 1px dashed #dee2e6; border-radius: 12px; padding: 15px; margin-bottom: 10px; }
        .img-preview { max-height: 120px; border-radius: 8px; margin-top: 8px; }
        .pilar-card { background: #fff; border-radius: 14px; border: 1px solid #e9ecef; padding: 18px; height: 100%; transition: box-shadow .2s; }
        .pilar-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .pilar-card .pilar-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; margin-bottom: 8px; }
        .pilar-card .pilar-metric { font-size: 2rem; font-weight: 800; color: var(--primary-dark); line-height: 1; }
        .pilar-card .pilar-detail { font-size: 0.78rem; color: #6c757d; margin-top: 4px; }
        .pilar-card canvas { max-height: 160px; }
        .pilar-card .pilar-empty { text-align: center; padding: 30px 10px; color: #adb5bd; }
        .pilar-card .pilar-empty i { font-size: 2rem; margin-bottom: 8px; display: block; }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('informe-avances') ?>" style="color: var(--primary-dark);">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
            <span class="fw-bold" style="color: var(--primary-dark);">
                <i class="fas fa-chart-line me-2" style="color: var(--gold-primary);"></i>
                <?= ($mode === 'edit') ? 'Editar Informe' : 'Nuevo Informe de Avances' ?>
            </span>
        </div>
    </nav>

    <div class="container mt-3">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('msg') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form id="formInforme" action="<?= base_url('informe-avances/' . ($mode === 'edit' ? 'update/' . $informe['id'] : 'store')) ?>" method="POST" enctype="multipart/form-data">

            <!-- SECCION 1: Datos Generales -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-building me-2"></i>1. Datos Generales</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Ciclo PHVA <span class="text-danger">*</span></label>
                            <select name="anio" id="selectAnio" class="form-select" required>
                                <?php for ($y = 2026; $y <= 2030; $y++): ?>
                                <option value="<?= $y ?>" <?= ($informe['anio'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Periodo</label>
                            <select id="selectMes" class="form-select">
                                <option value="">Manual...</option>
                                <option value="prev">Mes anterior</option>
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="button" id="btnLiquidar" class="btn btn-gold w-100" disabled>
                                <i class="fas fa-camera me-1"></i>Liquidar Informe
                            </button>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Desde</label>
                            <input type="date" name="fecha_desde" id="fechaDesde" class="form-control" value="<?= esc($informe['fecha_desde'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Hasta</label>
                            <input type="date" name="fecha_hasta" id="fechaHasta" class="form-control" value="<?= esc($informe['fecha_hasta'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end" id="liquidarStatus"></div>
                    </div>
                </div>
            </div>

            <!-- SECCION 2: Indicadores por Pilar SG-SST -->
            <div class="card card-section">
                <div class="card-header py-3">
                    <i class="fas fa-chart-pie me-2"></i>2. Indicadores por Pilar SG-SST
                    <span id="metricasLoading" class="ms-2 d-none"><i class="fas fa-spinner fa-spin"></i> Calculando...</span>
                </div>
                <div class="card-body">
                    <!-- Hidden inputs for DB columns -->
                    <input type="hidden" name="puntaje_anterior" id="puntajeAnterior" value="<?= esc($informe['puntaje_anterior'] ?? '') ?>">
                    <input type="hidden" name="puntaje_actual" id="puntajeActual" value="<?= esc($informe['puntaje_actual'] ?? '') ?>">
                    <input type="hidden" name="diferencia_neta" id="diferenciaNeta" value="<?= esc($informe['diferencia_neta'] ?? '') ?>">
                    <input type="hidden" name="estado_avance" id="estadoAvance" value="<?= esc($informe['estado_avance'] ?? '') ?>">
                    <input type="hidden" name="indicador_plan_trabajo" id="indicadorPlanTrabajo" value="<?= esc($informe['indicador_plan_trabajo'] ?? '') ?>">
                    <input type="hidden" name="indicador_capacitacion" id="indicadorCapacitacion" value="<?= esc($informe['indicador_capacitacion'] ?? '') ?>">
                    <input type="hidden" name="metricas_desglose_json" id="metricasDesgloseJson" value="<?= esc($informe['metricas_desglose_json'] ?? '') ?>">

                    <div class="row g-3">
                        <!-- Pilar 1: Estandares Minimos -->
                        <div class="col-md-6">
                            <div class="pilar-card">
                                <div class="pilar-title"><i class="fas fa-clipboard-check me-1" style="color:#36A2EB"></i>Estandares Minimos (Res. 0312)</div>
                                <div class="row align-items-center">
                                    <div class="col-6 text-center">
                                        <div id="emptyEstandares" class="pilar-empty"><i class="fas fa-chart-pie"></i>Seleccione un cliente</div>
                                        <canvas id="chartEstandares" width="200" height="200" style="display:none"></canvas>
                                    </div>
                                    <div class="col-6">
                                        <div class="pilar-metric" id="metricEstandares">--</div>
                                        <div class="pilar-detail" id="detailEstandares">Cumplimiento actual</div>
                                        <div class="pilar-detail" id="anteriorEstandares"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pilar 2: Plan de Trabajo Anual -->
                        <div class="col-md-6">
                            <div class="pilar-card">
                                <div class="pilar-title"><i class="fas fa-tasks me-1" style="color:#28a745"></i>Plan de Trabajo Anual</div>
                                <div class="row align-items-center">
                                    <div class="col-6 text-center">
                                        <div id="emptyPlan" class="pilar-empty"><i class="fas fa-chart-pie"></i>Seleccione un cliente</div>
                                        <canvas id="chartPlanTrabajo" width="200" height="200" style="display:none"></canvas>
                                    </div>
                                    <div class="col-6">
                                        <div class="pilar-metric" id="metricPlan">--</div>
                                        <div class="pilar-detail" id="detailPlan">Actividades cerradas</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pilar 3: Programa de Capacitacion -->
                        <div class="col-md-6">
                            <div class="pilar-card">
                                <div class="pilar-title"><i class="fas fa-chalkboard-teacher me-1" style="color:#ffc107"></i>Programa de Capacitacion</div>
                                <div class="row align-items-center">
                                    <div class="col-6 text-center">
                                        <div id="emptyCap" class="pilar-empty"><i class="fas fa-chart-pie"></i>Seleccione un cliente</div>
                                        <canvas id="chartCapacitacion" width="200" height="200" style="display:none"></canvas>
                                    </div>
                                    <div class="col-6">
                                        <div class="pilar-metric" id="metricCap">--</div>
                                        <div class="pilar-detail" id="detailCap">Capacitaciones ejecutadas</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pilar 4: Compromisos / Pendientes -->
                        <div class="col-md-6">
                            <div class="pilar-card">
                                <div class="pilar-title"><i class="fas fa-exclamation-circle me-1" style="color:#dc3545"></i>Compromisos / Pendientes</div>
                                <div class="row align-items-center">
                                    <div class="col-6 text-center">
                                        <div id="emptyPend" class="pilar-empty"><i class="fas fa-chart-pie"></i>Seleccione un cliente</div>
                                        <canvas id="chartPendientes" width="200" height="200" style="display:none"></canvas>
                                    </div>
                                    <div class="col-6">
                                        <div class="pilar-metric" id="metricPend">--</div>
                                        <div class="pilar-detail" id="detailPend">Estado de compromisos</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCION 2b: Evolución Histórica del Cliente -->
            <div class="card card-section" id="seccionEvolucion" style="display:none">
                <div class="card-header py-3">
                    <i class="fas fa-chart-line me-2"></i>2b. Evolución Histórica del Cliente
                </div>
                <div class="card-body">
                    <div id="evolucionEmpty" class="text-center py-3 text-muted" style="display:none">
                        <i class="fas fa-info-circle me-1"></i>Sin datos históricos aún. Ejecute un Snapshot desde el panel admin.
                    </div>
                    <div class="row" id="evolucionCharts">
                        <div class="col-md-6">
                            <p class="text-center small text-muted fw-semibold mb-1">% Cumplimiento Estándares</p>
                            <canvas id="chartHistEstandares" height="140"></canvas>
                        </div>
                        <div class="col-md-6">
                            <p class="text-center small text-muted fw-semibold mb-1">% Actividades Abiertas Plan Trabajo</p>
                            <canvas id="chartHistPlan" height="140"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCION 3: Resumen de Avance -->
            <div class="card card-section">
                <div class="card-header py-3">
                    <i class="fas fa-file-alt me-2"></i>3. Resumen de Avance del Periodo
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" id="btnGenerarIA" class="btn btn-ia btn-sm" disabled>
                            <i class="fas fa-robot me-1"></i>Generar con IA
                            <span id="iaSpinner" class="d-none"><i class="fas fa-spinner fa-spin ms-1"></i></span>
                        </button>
                    </div>
                    <textarea name="resumen_avance" id="resumenAvance" class="form-control" rows="10" placeholder="Escriba el resumen de avance o genere con IA..."><?= esc($informe['resumen_avance'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 4: Actividades Cerradas en el Periodo -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-check-circle me-2"></i>4. Actividades Plan de Trabajo Anual Cerradas en el Periodo</div>
                <div class="card-body">
                    <div id="tablaCerradas" class="mb-2"></div>
                    <textarea name="actividades_cerradas_periodo" id="actividadesCerradas" class="d-none"><?= esc($informe['actividades_cerradas_periodo'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 4b: Actividades PTA No Cerradas -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-clock me-2"></i>Actividades Plan de Trabajo Anual No Cerradas en el Periodo</div>
                <div class="card-body">
                    <textarea name="actividades_no_cerradas_pta" id="actividadesNoCerradasPta" class="form-control" rows="5" placeholder="Se auto-pobla al cargar metricas..."><?= esc($informe['actividades_no_cerradas_pta'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 5: Actividades Abiertas -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-exclamation-triangle me-2"></i>5. Actividades Abiertas (Compromisos)</div>
                <div class="card-body">
                    <textarea name="actividades_abiertas" id="actividadesAbiertas" class="form-control" rows="5" placeholder="Se auto-pobla al seleccionar cliente..."><?= esc($informe['actividades_abiertas'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 6: Observaciones -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-comment-alt me-2"></i>6. Observaciones</div>
                <div class="card-body">
                    <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones adicionales..."><?= esc($informe['observaciones'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 7: Vencimientos de Mantenimientos (solo lectura) -->
            <?php if (!empty($vencimientos)): ?>
            <div class="card card-section" id="seccionVencimientos">
                <div class="card-header py-3" style="background:#dc3545;">
                    <i class="fas fa-exclamation-circle me-2"></i>7. Elementos con Vencimiento Proximo o Vencido
                    <span class="badge bg-light text-danger ms-2"><?= count($vencimientos) ?></span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%;" class="text-center">#</th>
                                <th style="width:35%;">Elemento</th>
                                <th style="width:18%;" class="text-center">Fecha Vencimiento</th>
                                <th style="width:14%;" class="text-center">Estado</th>
                                <th style="width:28%;">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $hoy = date('Y-m-d');
                                foreach ($vencimientos as $idx => $v):
                                    $vencido = ($v['fecha_vencimiento'] <= $hoy);
                            ?>
                            <tr class="<?= $vencido ? 'table-danger' : 'table-warning' ?>">
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><?= esc($v['detalle_mantenimiento'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $vencido ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $vencido ? 'VENCIDO' : 'PROXIMO' ?></span>
                                </td>
                                <td class="small"><?= esc($v['observaciones'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="px-3 py-2 text-muted small">
                        <i class="fas fa-info-circle me-1"></i>Esta seccion se incluye automaticamente en el PDF generado. No es editable.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <input type="hidden" name="enlace_dashboard" id="enlaceDashboard" value="<?= esc($informe['enlace_dashboard'] ?? '') ?>">

            <!-- 6 MODULOS ADICIONALES (se muestran al cargar metricas) -->

            <!-- Firma Electronica -->
            <div id="secFirma" class="card card-section d-none">
                <div class="card-header py-3"><i class="fas fa-signature me-2"></i>Firma Electronica</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="firmaTotal">0</div><div class="label">Solicitudes</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value text-success" id="firmaFirmados">0</div><div class="label">Firmados</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value text-warning" id="firmaPendientes">0</div><div class="label">Pendientes</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value text-danger" id="firmaExpirados">0</div><div class="label">Expirados</div></div></div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label small fw-bold">Tasa de firma</label>
                        <div class="progress progress-custom"><div class="progress-bar bg-success" id="firmaProgress" style="width:0%">0%</div></div>
                    </div>
                </div>
            </div>

            <!-- Documentos SST -->
            <div id="secDocsSst" class="card card-section d-none">
                <div class="card-header py-3"><i class="fas fa-file-alt me-2"></i>Documentos SST Creados</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><div class="metric-box"><div class="value" id="docsTotal">0</div><div class="label">Creados en periodo</div></div></div>
                        <div class="col-md-4"><div class="metric-box"><div class="value text-success" id="docsAprobados">0</div><div class="label">Aprobados</div></div></div>
                    </div>
                    <div id="docsTabla"></div>
                </div>
            </div>

            <!-- Indicadores SST -->
            <div id="secIndicadores" class="card card-section d-none">
                <div class="card-header py-3"><i class="fas fa-chart-bar me-2"></i>Indicadores SST</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="indActivos">0</div><div class="label">Activos</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="indMedidos">0</div><div class="label">Medidos</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value text-success" id="indCumplen">0</div><div class="label">Cumplen meta</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="indPct">0%</div><div class="label">Cumplimiento</div></div></div>
                    </div>
                    <div class="mt-3">
                        <div class="progress progress-custom"><div class="progress-bar bg-info" id="indProgress" style="width:0%">0%</div></div>
                    </div>
                </div>
            </div>

            <!-- Acciones Correctivas -->
            <div id="secAcciones" class="card card-section d-none">
                <div class="card-header py-3"><i class="fas fa-exclamation-triangle me-2"></i>Acciones Correctivas</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="accCierre">0%</div><div class="label">Cierre a tiempo</div><div class="small text-muted">Meta: 85%</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="accEfectividad">0%</div><div class="label">Efectividad</div><div class="small text-muted">Meta: 80%</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="accDias">0</div><div class="label">Dias promedio</div><div class="small text-muted">Meta: 30</div></div></div>
                        <div class="col-md-3"><div class="metric-box"><div class="value" id="accReincidencia">0%</div><div class="label">Reincidencia</div><div class="small text-muted">Meta: &le;10%</div></div></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><div class="metric-box"><div class="value" id="accHallazgos">0</div><div class="label">Hallazgos</div></div></div>
                        <div class="col-md-4"><div class="metric-box"><div class="value" id="accAcciones">0</div><div class="label">Acciones</div></div></div>
                        <div class="col-md-4"><div class="metric-box"><div class="value text-danger" id="accVencidas">0</div><div class="label">Vencidas</div></div></div>
                    </div>
                </div>
            </div>

            <!-- Actas de Comite -->
            <div id="secActas" class="card card-section d-none">
                <div class="card-header py-3"><i class="fas fa-gavel me-2"></i>Actas de Comite</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><div class="metric-box"><div class="value" id="actasReuniones">0</div><div class="label">Reuniones en periodo</div></div></div>
                    </div>
                    <div id="actasTabla" class="mb-3"></div>
                    <div id="actasCompromisos"></div>
                </div>
            </div>

            <!-- Inspecciones -->
            <div id="secInspecciones" class="card card-section d-none">
                <div class="card-header py-3"><i class="fas fa-clipboard-check me-2"></i>Inspecciones</div>
                <div class="card-body">
                    <div class="row g-3 mb-3" id="inspecTipos"></div>
                    <div id="inspecHallazgos"></div>
                </div>
            </div>

            <!-- SECCION 7: Soportes -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-paperclip me-2"></i>7. Soportes (hasta 4)</div>
                <div class="card-body">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="soporte-group">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Soporte <?= $i ?> - Titulo</label>
                                <input type="text" name="soporte_<?= $i ?>_texto" class="form-control" value="<?= esc($informe["soporte_{$i}_texto"] ?? '') ?>" placeholder="Descripcion del soporte">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Imagen</label>
                                <input type="file" name="soporte_<?= $i ?>_imagen" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-md-2 text-center">
                                <?php if (!empty($informe["soporte_{$i}_imagen"])): ?>
                                    <img src="<?= base_url($informe["soporte_{$i}_imagen"]) ?>" class="img-preview">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex gap-2 mb-5">
                <button type="submit" class="btn btn-gold btn-lg flex-fill">
                    <i class="fas fa-save me-2"></i>Guardar Borrador
                </button>
                <?php if ($mode === 'edit'): ?>
                <button type="button" id="btnFinalizar" class="btn btn-success btn-lg flex-fill">
                    <i class="fas fa-check-circle me-2"></i>Finalizar y Generar PDF
                </button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($mode === 'edit'): ?>
        <form id="formFinalizar" action="<?= base_url('informe-avances/finalizar/' . $informe['id']) ?>" method="POST" class="d-none"></form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    Chart.register(ChartDataLabels);

    const BASE = '<?= base_url() ?>/';
    const EDIT_MODE = <?= json_encode($mode === 'edit') ?>;
    const PRESELECT_CLIENTE = <?= json_encode($id_cliente) ?>;

    // Chart instances
    var charts = { estandares: null, plan: null, cap: null, pend: null };
    // Desglose data for JSON
    var desgloseData = {};

    // Color palettes (matching dashboards)
    const COLORS_PHVA = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
    const COLORS_ESTADO = ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1'];

    function createDoughnut(canvasId, labels, data, colors) {
        var ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: colors.slice(0, labels.length) }]
            },
            options: {
                responsive: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value, context) {
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            return total > 0 && value > 0 ? ((value / total) * 100).toFixed(0) + '%' : '';
                        }
                    }
                }
            }
        });
    }

    function destroyChart(key) {
        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
    }

    function showChart(canvasId, emptyId) {
        document.getElementById(emptyId).style.display = 'none';
        document.getElementById(canvasId).style.display = 'block';
    }

    function estadoBadgeClass(estado) {
        if (!estado) return 'bg-secondary';
        if (estado.includes('SIGNIFICATIVO')) return 'bg-success';
        if (estado.includes('MODERADO')) return 'bg-info';
        if (estado.includes('ESTABLE')) return 'bg-warning text-dark';
        return 'bg-danger';
    }

    // Check if canvas has actual content (not blank)
    function isCanvasBlank(canvas) {
        var ctx = canvas.getContext('2d');
        var data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        for (var i = 3; i < data.length; i += 4) {
            if (data[i] > 0) return false;
        }
        return true;
    }

    // Capture all chart canvases as base64 PNG (only non-blank)
    function captureChartImages() {
        var images = {};
        ['chartEstandares', 'chartPlanTrabajo', 'chartCapacitacion', 'chartPendientes'].forEach(function(id) {
            var canvas = document.getElementById(id);
            if (canvas && canvas.style.display !== 'none' && !isCanvasBlank(canvas)) {
                images[id] = canvas.toDataURL('image/png');
            }
        });
        return images;
    }

    // Async capture with double-rAF safety net
    function captureAndSetJson() {
        return new Promise(function(resolve) {
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    var json = Object.assign({}, desgloseData);
                    json.chart_images = captureChartImages();
                    $('#metricasDesgloseJson').val(JSON.stringify(json));
                    resolve();
                });
            });
        });
    }

    $(document).ready(function() {
        // Recordatorio antes de crear informe (solo modo crear)
        if (!EDIT_MODE) {
            Swal.fire({
                title: 'Antes de generar el informe',
                html: '<div style="text-align:left; font-size:0.95rem;">' +
                    '<p class="mb-2">Recuerde que debe haber actualizado:</p>' +
                    '<ul style="list-style:none; padding-left:0;">' +
                    '<li><i class="fas fa-check-circle text-success me-2"></i>Estado de evaluación de estándares mínimos</li>' +
                    '<li><i class="fas fa-check-circle text-success me-2"></i>Plan de Trabajo Anual</li>' +
                    '<li><i class="fas fa-check-circle text-success me-2"></i>Cronograma de capacitaciones</li>' +
                    '<li><i class="fas fa-check-circle text-success me-2"></i>Mantenimientos vencidos</li>' +
                    '<li><i class="fas fa-check-circle text-success me-2"></i>Pendientes registrados en acta de visita</li>' +
                    '</ul>' +
                    '<p class="mt-2 text-muted" style="font-size:0.85rem;">Si todo está actualizado haga clic en <b>Continuar</b>, de lo contrario gestione primero.</p>' +
                    '</div>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-arrow-right me-1"></i>Continuar',
                cancelButtonText: '<i class="fas fa-external-link-alt me-1"></i>Ir a gestionar',
                confirmButtonColor: '#bd9751',
                cancelButtonColor: '#1c2437',
                allowOutsideClick: false,
            }).then(function(result) {
                if (!result.isConfirmed) {
                    window.location.href = BASE + 'quick-access';
                }
            });
        }

        // Initialize Select2 immediately so it always renders
        $('#selectCliente').select2({ theme: 'bootstrap-5', placeholder: 'Buscar cliente...', allowClear: true });

        // Load clients
        $.getJSON(BASE + 'informe-avances/api/clientes', function(data) {
            data.forEach(function(c) {
                var opt = new Option(c.nombre_cliente + ' (' + c.nit_cliente + ')', c.id_cliente, false, false);
                $('#selectCliente').append(opt);
            });

            if (PRESELECT_CLIENTE) {
                $('#selectCliente').val(PRESELECT_CLIENTE).trigger('change');
                if (!EDIT_MODE) loadMetricas(PRESELECT_CLIENTE);
            }
        });

        $('#selectCliente').on('change', function() {
            var clienteId = $(this).val();
            if (clienteId && !EDIT_MODE) loadMetricas(clienteId);
            if (clienteId) loadVencimientos(clienteId);
            if (clienteId) loadHistorial(clienteId);
            $('#btnGenerarIA').prop('disabled', !clienteId);
            $('#btnLiquidar').prop('disabled', !clienteId);
        });

        // Botón Liquidar: snapshot individual del cliente
        $('#btnLiquidar').on('click', function() {
            var clienteId = $('#selectCliente').val();
            if (!clienteId) return;
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Liquidando...');
            $('#liquidarStatus').html('');
            $.ajax({
                url: BASE + 'informe-avances/api/liquidar/' + clienteId,
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                success: function(resp) {
                    if (resp.success) {
                        $('#liquidarStatus').html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>Snapshot tomado (' + resp.fecha + ')</span>');
                        // Recargar métricas e historial con datos frescos
                        loadMetricas(clienteId);
                        loadHistorial(clienteId);
                    } else {
                        $('#liquidarStatus').html('<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + (resp.error || 'Error') + '</span>');
                    }
                },
                error: function() {
                    $('#liquidarStatus').html('<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Error de conexión</span>');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-camera me-1"></i>Liquidar Informe');
                }
            });
        });

        // Recargar métricas e historial al cambiar año
        $('#selectAnio').on('change', function() {
            var clienteId = $('#selectCliente').val();
            if (clienteId && !EDIT_MODE) loadMetricas(clienteId);
            if (clienteId) loadHistorial(clienteId);
        });

        // Selector de mes → auto-llenar fechas desde/hasta
        $('#selectMes').on('change', function() {
            var val = $(this).val();
            if (!val) return;
            var anio = parseInt($('#selectAnio').val()) || new Date().getFullYear();
            var mes, lastDay;
            if (val === 'prev') {
                var today = new Date();
                var prevMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                mes = prevMonth.getMonth() + 1;
                anio = prevMonth.getFullYear();
                lastDay = new Date(anio, mes, 0).getDate();
            } else {
                mes = parseInt(val);
                lastDay = new Date(anio, mes, 0).getDate();
            }
            var mm = mes < 10 ? '0' + mes : '' + mes;
            $('#fechaDesde').val(anio + '-' + mm + '-01');
            $('#fechaHasta').val(anio + '-' + mm + '-' + (lastDay < 10 ? '0' + lastDay : lastDay));
        });

        if (EDIT_MODE && PRESELECT_CLIENTE) {
            loadHistorial(PRESELECT_CLIENTE);
            $('#btnGenerarIA').prop('disabled', false);
            // If editing, try to render charts from stored JSON
            var storedJson = $('#metricasDesgloseJson').val();
            if (storedJson) {
                try {
                    var stored = JSON.parse(storedJson);
                    desgloseData = stored;
                    renderDesgloseCharts(stored);
                } catch(e) {}
            }
            // Render stored cerradas text as table in edit mode
            var cerradasText = $('#actividadesCerradas').val();
            if (cerradasText && cerradasText.trim() !== '' && cerradasText.indexOf('No se cerraron') === -1) {
                var lines = cerradasText.trim().split('\n');
                var html = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Actividad</th><th>Numeral</th><th>PHVA</th><th>Responsable</th><th>Fecha Cierre</th></tr></thead><tbody>';
                lines.forEach(function(line) {
                    // Parse: - [numeral] actividad | PHVA: x | Resp: y | Cerrada: fecha
                    var m = line.match(/^\s*-\s*\[([^\]]*)\]\s*(.*?)\s*\|\s*PHVA:\s*(.*?)\s*\|\s*Resp:\s*(.*?)\s*\|\s*Cerrada:\s*(.*)/);
                    if (m) {
                        html += '<tr><td>'+esc(m[2])+'</td><td>'+esc(m[1])+'</td><td>'+esc(m[3])+'</td><td>'+esc(m[4])+'</td><td>'+esc(m[5])+'</td></tr>';
                    }
                });
                html += '</tbody></table>';
                $('#tablaCerradas').html(html);
            } else if (cerradasText && cerradasText.indexOf('No se cerraron') !== -1) {
                $('#tablaCerradas').html('<p class="text-muted mb-0">' + esc(cerradasText) + '</p>');
            }
        }

        function loadVencimientos(clienteId) {
            $.get(BASE + 'informe-avances/api/vencimientos/' + clienteId, function(resp) {
                var container = $('#seccionVencimientos');
                if (!resp.success || !resp.data || resp.data.length === 0) {
                    container.remove();
                    return;
                }
                var items = resp.data;
                var hoy = new Date().toISOString().slice(0, 10);
                var html = '<div class="card card-section" id="seccionVencimientos">'
                    + '<div class="card-header py-3" style="background:#dc3545;">'
                    + '<i class="fas fa-exclamation-circle me-2"></i>7. Elementos con Vencimiento Proximo o Vencido '
                    + '<span class="badge bg-light text-danger ms-2">' + items.length + '</span></div>'
                    + '<div class="card-body p-0"><table class="table table-sm table-hover mb-0">'
                    + '<thead class="table-light"><tr>'
                    + '<th style="width:5%;" class="text-center">#</th>'
                    + '<th style="width:35%;">Elemento</th>'
                    + '<th style="width:18%;" class="text-center">Fecha Vencimiento</th>'
                    + '<th style="width:14%;" class="text-center">Estado</th>'
                    + '<th style="width:28%;">Observaciones</th></tr></thead><tbody>';
                for (var i = 0; i < items.length; i++) {
                    var v = items[i];
                    var vencido = v.fecha_vencimiento <= hoy;
                    var cls = vencido ? 'table-danger' : 'table-warning';
                    var badge = vencido
                        ? '<span class="badge bg-danger">VENCIDO</span>'
                        : '<span class="badge bg-warning text-dark">PROXIMO</span>';
                    var fv = v.fecha_vencimiento.split('-');
                    var fechaFmt = fv[2] + '/' + fv[1] + '/' + fv[0];
                    html += '<tr class="' + cls + '"><td class="text-center">' + (i+1) + '</td>'
                        + '<td>' + (v.detalle_mantenimiento || 'N/A') + '</td>'
                        + '<td class="text-center">' + fechaFmt + '</td>'
                        + '<td class="text-center">' + badge + '</td>'
                        + '<td class="small">' + (v.observaciones || '') + '</td></tr>';
                }
                html += '</tbody></table>'
                    + '<div class="px-3 py-2 text-muted small"><i class="fas fa-info-circle me-1"></i>'
                    + 'Esta seccion se incluye automaticamente en el PDF generado. No es editable.</div>'
                    + '</div></div>';
                if (container.length) {
                    container.replaceWith(html);
                } else {
                    $(html).insertBefore($('input[name="enlace_dashboard"]'));
                }
            });
        }

        // Chart instances for evolution section
        var chartHistEst = null, chartHistPlan = null;

        function makeLineChart(canvasId, labels, values, label, color) {
            var ctx = document.getElementById(canvasId);
            if (!ctx) return null;
            return new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: values,
                        borderColor: color,
                        backgroundColor: color + '22',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    animation: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: function(v){ return v + '%'; } } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        var MES_NOMBRES = {'01':'Ene','02':'Feb','03':'Mar','04':'Abr','05':'May','06':'Jun',
                           '07':'Jul','08':'Ago','09':'Sep','10':'Oct','11':'Nov','12':'Dic'};

        function loadHistorial(clienteId) {
            var anio = $('#selectAnio').val() || new Date().getFullYear();
            $.getJSON(BASE + 'informe-avances/api/historial/' + clienteId + '?anio=' + anio, function(resp) {
                if (!resp.success) return;
                var est = resp.estandares || [];
                var plan = resp.plan || [];
                var seccion = $('#seccionEvolucion');

                if (est.length === 0 && plan.length === 0) {
                    seccion.show();
                    $('#evolucionCharts').hide();
                    $('#evolucionEmpty').show();
                    return;
                }

                seccion.show();
                $('#evolucionEmpty').hide();
                $('#evolucionCharts').show();

                function toLabels(arr) {
                    return arr.map(function(it) {
                        var parts = it.mes.split('-');
                        return (MES_NOMBRES[parts[1]] || parts[1]) + ' ' + parts[0].slice(2);
                    });
                }
                function toValues(arr) { return arr.map(function(it){ return it.promedio; }); }

                if (chartHistEst) { chartHistEst.destroy(); chartHistEst = null; }
                if (chartHistPlan) { chartHistPlan.destroy(); chartHistPlan = null; }

                if (est.length > 0) {
                    chartHistEst = makeLineChart('chartHistEstandares', toLabels(est), toValues(est), '% Estándares', '#667eea');
                }
                if (plan.length > 0) {
                    chartHistPlan = makeLineChart('chartHistPlan', toLabels(plan), toValues(plan), '% Abiertas Plan de Trabajo', '#f093fb');
                }
            });
        }

        function loadMetricas(clienteId) {
            var desde = $('#fechaDesde').val();
            var hasta = $('#fechaHasta').val();
            var anio = $('#selectAnio').val();
            var params = '?anio=' + anio;
            if (desde) params += '&fecha_desde=' + desde;
            if (hasta) params += '&fecha_hasta=' + hasta;

            $('#metricasLoading').removeClass('d-none');

            $.get(BASE + 'informe-avances/api/metricas/' + clienteId + params, function(resp) {
                if (!resp.success) return;
                var d = resp.data;

                // Hidden inputs
                $('#puntajeAnterior').val(d.puntaje_anterior);
                $('#puntajeActual').val(d.puntaje_actual);
                $('#diferenciaNeta').val(d.diferencia_neta);
                $('#estadoAvance').val(d.estado_avance);
                $('#indicadorPlanTrabajo').val(d.indicador_plan_trabajo);
                $('#indicadorCapacitacion').val(d.indicador_capacitacion);
                $('#enlaceDashboard').val(d.enlace_dashboard);

                // Store desglose data
                desgloseData = {
                    desglose_estandares: d.desglose_estandares || [],
                    desglose_plan_trabajo: d.desglose_plan_trabajo || [],
                    desglose_capacitacion: d.desglose_capacitacion || [],
                    desglose_pendientes: d.desglose_pendientes || [],
                    puntaje_actual: d.puntaje_actual,
                    puntaje_anterior: d.puntaje_anterior,
                    diferencia_neta: d.diferencia_neta,
                    estado_avance: d.estado_avance,
                    indicador_plan_trabajo: d.indicador_plan_trabajo,
                    indicador_capacitacion: d.indicador_capacitacion
                };

                renderDesgloseCharts(desgloseData);

                // Actividades
                if (!$('#actividadesAbiertas').val()) {
                    $('#actividadesAbiertas').val(d.actividades_abiertas);
                }
                if (!$('#actividadesCerradas').val()) {
                    $('#actividadesCerradas').val(d.actividades_cerradas_periodo);
                }
                if (!$('#actividadesNoCerradasPta').val()) {
                    $('#actividadesNoCerradasPta').val(d.actividades_no_cerradas_pta || '');
                }

                // Tabla cerradas
                if (d.actividades_cerradas_raw && d.actividades_cerradas_raw.length > 0) {
                    var html = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Actividad</th><th>Numeral</th><th>PHVA</th><th>Responsable</th><th>Fecha Cierre</th></tr></thead><tbody>';
                    d.actividades_cerradas_raw.forEach(function(a) {
                        html += '<tr><td>'+esc(a.actividad_plandetrabajo || '')+'</td><td>'+esc(a.numeral_plandetrabajo || '')+'</td><td>'+esc(a.phva_plandetrabajo || '')+'</td><td>'+esc(a.responsable_sugerido_plandetrabajo || '')+'</td><td>'+(a.fecha_cierre || 'Sin fecha')+'</td></tr>';
                    });
                    html += '</tbody></table>';
                    $('#tablaCerradas').html(html);
                } else {
                    $('#tablaCerradas').html('');
                }

                if (d.fecha_desde_sugerida && !$('#fechaDesde').val()) {
                    $('#fechaDesde').val(d.fecha_desde_sugerida);
                }

                // === 6 MODULOS ADICIONALES ===
                renderModulosAdicionales(d);

            }).always(function() {
                $('#metricasLoading').addClass('d-none');
            });
        }

        function renderDesgloseCharts(data) {
            // === Pilar 1: Estandares ===
            destroyChart('estandares');
            var est = data.desglose_estandares || [];
            if (est.length > 0) {
                showChart('chartEstandares', 'emptyEstandares');
                var labels = est.map(function(e) { return e.ciclo || 'Sin ciclo'; });
                var valores = est.map(function(e) { return parseFloat(e.total_valor) || 0; });
                charts.estandares = createDoughnut('chartEstandares', labels, valores, COLORS_PHVA);
            }
            var pa = data.puntaje_actual ?? 0;
            var pant = data.puntaje_anterior ?? 39.75;
            var diff = data.diferencia_neta ?? 0;
            $('#metricEstandares').text(pa.toFixed(1) + '%');
            var arrow = diff > 0 ? '&#9650;' : diff < 0 ? '&#9660;' : '';
            var diffColor = diff > 0 ? '#28a745' : diff < 0 ? '#dc3545' : '#6c757d';
            $('#anteriorEstandares').html('Anterior: ' + pant.toFixed(1) + '% <span style="color:' + diffColor + '">' + arrow + (diff > 0 ? '+' : '') + diff.toFixed(1) + ' pp</span>');
            // estado_avance se guarda en hidden pero no se muestra badge

            // === Pilar 2: Plan de Trabajo ===
            destroyChart('plan');
            var plan = data.desglose_plan_trabajo || [];
            if (plan.length > 0) {
                showChart('chartPlanTrabajo', 'emptyPlan');
                var pLabels = plan.map(function(p) { return p.estado_actividad || 'Sin estado'; });
                var pData = plan.map(function(p) { return parseInt(p.cantidad) || 0; });
                charts.plan = createDoughnut('chartPlanTrabajo', pLabels, pData, COLORS_ESTADO);
            }
            var ipw = data.indicador_plan_trabajo ?? 0;
            $('#metricPlan').text(ipw.toFixed(1) + '%');
            var totalPlan = plan.reduce(function(s, p) { return s + (parseInt(p.cantidad) || 0); }, 0);
            var cerradas = 0;
            plan.forEach(function(p) { if (p.estado_actividad === 'CERRADA') cerradas = parseInt(p.cantidad) || 0; });
            $('#detailPlan').text(cerradas + ' cerradas de ' + totalPlan + ' actividades');

            // === Pilar 3: Capacitacion ===
            destroyChart('cap');
            var cap = data.desglose_capacitacion || [];
            if (cap.length > 0) {
                showChart('chartCapacitacion', 'emptyCap');
                var cLabels = cap.map(function(c) { return c.estado || 'Sin estado'; });
                var cData = cap.map(function(c) { return parseInt(c.cantidad) || 0; });
                charts.cap = createDoughnut('chartCapacitacion', cLabels, cData, COLORS_ESTADO);
            }
            var icap = data.indicador_capacitacion ?? 0;
            $('#metricCap').text(icap.toFixed(1) + '%');
            var totalCap = cap.reduce(function(s, c) { return s + (parseInt(c.cantidad) || 0); }, 0);
            var ejecutadas = 0;
            cap.forEach(function(c) { if (c.estado === 'EJECUTADA') ejecutadas = parseInt(c.cantidad) || 0; });
            $('#detailCap').text(ejecutadas + ' ejecutadas de ' + totalCap + ' capacitaciones');

            // === Pilar 4: Pendientes ===
            destroyChart('pend');
            var pend = data.desglose_pendientes || [];
            if (pend.length > 0) {
                showChart('chartPendientes', 'emptyPend');
                var dLabels = pend.map(function(p) { return p.estado || 'Sin estado'; });
                var dData = pend.map(function(p) { return parseInt(p.cantidad) || 0; });
                charts.pend = createDoughnut('chartPendientes', dLabels, dData, COLORS_ESTADO);
            }
            var abiertosPend = 0, promDias = 0;
            pend.forEach(function(p) {
                if (p.estado === 'ABIERTA') { abiertosPend = parseInt(p.cantidad) || 0; promDias = parseFloat(p.promedio_dias) || 0; }
            });
            var totalPend = pend.reduce(function(s, p) { return s + (parseInt(p.cantidad) || 0); }, 0);
            $('#metricPend').text(abiertosPend + ' / ' + totalPend);
            $('#detailPend').text(abiertosPend + ' abiertos' + (promDias > 0 ? ' (prom ' + promDias.toFixed(0) + ' dias)' : ''));
        }

        // === RENDER 6 MODULOS ADICIONALES ===
        function renderModulosAdicionales(d) {
            // Ocultar todos primero
            $('#secFirma,#secDocsSst,#secIndicadores,#secAcciones,#secActas,#secInspecciones').addClass('d-none');

            // 1. Firma Electronica
            var fe = d.firma_electronica || {};
            if (fe.total_solicitudes > 0) {
                $('#secFirma').removeClass('d-none');
                $('#firmaTotal').text(fe.total_solicitudes);
                $('#firmaFirmados').text(fe.firmados || 0);
                $('#firmaPendientes').text(fe.pendientes || 0);
                $('#firmaExpirados').text(fe.expirados || 0);
                var tasa = parseFloat(fe.tasa_firma || 0);
                $('#firmaProgress').css('width', tasa + '%').text(tasa.toFixed(1) + '%');
            }

            // 2. Documentos SST
            var ds = d.documentos_sst || {};
            if ((ds.total_creados || 0) > 0) {
                $('#secDocsSst').removeClass('d-none');
                $('#docsTotal').text(ds.total_creados);
                $('#docsAprobados').text(ds.aprobados_periodo || 0);
                var pt = ds.por_tipo || [];
                if (pt.length > 0) {
                    var h = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Tipo</th><th>Cantidad</th></tr></thead><tbody>';
                    pt.forEach(function(t) { h += '<tr><td>' + esc((t.tipo_documento || '').replace(/_/g, ' ')) + '</td><td class="text-center">' + t.cantidad + '</td></tr>'; });
                    h += '</tbody></table>';
                    $('#docsTabla').html(h);
                }
            }

            // 3. Indicadores SST
            var ind = d.indicadores_sst || {};
            if ((ind.total_activos || 0) > 0) {
                $('#secIndicadores').removeClass('d-none');
                $('#indActivos').text(ind.total_activos);
                $('#indMedidos').text(ind.medidos_periodo || 0);
                $('#indCumplen').text(ind.cumplen_meta || 0);
                var pct = parseFloat(ind.pct_cumplimiento || 0);
                $('#indPct').text(pct.toFixed(1) + '%');
                $('#indProgress').css('width', pct + '%').text(pct.toFixed(1) + '%');
            }

            // 4. Acciones Correctivas
            var ac = d.acciones_correctivas || {};
            if ((ac.acciones_total || 0) > 0) {
                $('#secAcciones').removeClass('d-none');
                var kp = ac.kpis || {};
                $('#accCierre').text((kp.cierre_a_tiempo || 0) + '%');
                $('#accEfectividad').text((kp.efectividad || 0) + '%');
                $('#accDias').text(kp.dias_promedio || 0);
                $('#accReincidencia').text((kp.reincidencia || 0) + '%');
                $('#accHallazgos').text(ac.hallazgos_total || 0);
                $('#accAcciones').text(ac.acciones_total || 0);
                $('#accVencidas').text(ac.acciones_vencidas || 0);
            }

            // 5. Actas de Comite
            var at = d.actas_comite || {};
            if ((at.reuniones_periodo || 0) > 0 || (at.compromisos && at.compromisos.total > 0)) {
                $('#secActas').removeClass('d-none');
                $('#actasReuniones').text(at.reuniones_periodo || 0);
                var pc = at.por_comite || [];
                if (pc.length > 0) {
                    var h = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Comite</th><th>Reuniones</th><th>Esperadas</th><th>Cumplimiento</th></tr></thead><tbody>';
                    pc.forEach(function(c) { h += '<tr><td>' + esc(c.tipo_comite) + '</td><td class="text-center">' + c.total_anio + '</td><td class="text-center">' + c.esperadas + '</td><td class="text-center">' + c.cumplimiento + '%</td></tr>'; });
                    h += '</tbody></table>';
                    $('#actasTabla').html(h);
                }
                var comp = at.compromisos || {};
                if (comp.total > 0) {
                    $('#actasCompromisos').html('<div class="row g-3"><div class="col-md-3"><div class="metric-box"><div class="value">' + comp.total + '</div><div class="label">Total</div></div></div><div class="col-md-3"><div class="metric-box"><div class="value text-success">' + comp.cumplidos + '</div><div class="label">Cumplidos</div></div></div><div class="col-md-3"><div class="metric-box"><div class="value text-warning">' + comp.pendientes + '</div><div class="label">Pendientes</div></div></div><div class="col-md-3"><div class="metric-box"><div class="value text-danger">' + comp.vencidos + '</div><div class="label">Vencidos</div></div></div></div>');
                }
            }

            // 6. Inspecciones
            var ins = d.inspecciones || {};
            if ((ins.total_inspecciones || 0) > 0) {
                $('#secInspecciones').removeClass('d-none');
                var pt = ins.por_tipo || {};
                var nombres = {locativa:'Locativa', extintores:'Extintores', botiquin:'Botiquin', senalizacion:'Senalizacion'};
                var colores = {locativa:'#0d6efd', extintores:'#dc3545', botiquin:'#198754', senalizacion:'#ffc107'};
                var h = '';
                Object.keys(nombres).forEach(function(k) {
                    var v = pt[k] || {total:0, completadas:0};
                    if (v.total > 0) {
                        h += '<div class="col-md-3"><div class="metric-box" style="border-left:4px solid ' + colores[k] + '"><div class="value">' + v.completadas + '/' + v.total + '</div><div class="label">' + nombres[k] + '</div></div></div>';
                    }
                });
                $('#inspecTipos').html(h);
                var hall = ins.hallazgos || {};
                if (hall.total > 0) {
                    $('#inspecHallazgos').html('<div class="alert alert-info mb-0"><i class="fas fa-search me-2"></i>Hallazgos locativos: <strong>' + hall.total + '</strong> encontrados, <strong>' + hall.corregidos + '</strong> corregidos, <strong>' + hall.pendientes + '</strong> pendientes</div>');
                }
            }
        }

        // Generar resumen con IA
        $('#btnGenerarIA').on('click', function() {
            var clienteId = $('#selectCliente').val();
            var desde = $('#fechaDesde').val();
            var hasta = $('#fechaHasta').val();

            if (!clienteId || !desde || !hasta) {
                alert('Seleccione cliente y fechas primero');
                return;
            }

            $(this).prop('disabled', true);
            $('#iaSpinner').removeClass('d-none');

            $.post(BASE + 'informe-avances/generar-resumen', {
                id_cliente: clienteId,
                fecha_desde: desde,
                fecha_hasta: hasta,
                anio: $('#selectAnio').val()
            }, function(resp) {
                if (resp.success) {
                    $('#resumenAvance').val(resp.resumen);
                } else {
                    alert('Error IA: ' + (resp.error || 'Error desconocido'));
                }
            }).fail(function() {
                alert('Error de conexion con el servidor');
            }).always(function() {
                $('#btnGenerarIA').prop('disabled', false);
                $('#iaSpinner').addClass('d-none');
            });
        });

        // Capture charts before form submit (async-safe)
        var isSubmitting = false;
        $('#formInforme').on('submit', function(e) {
            if (isSubmitting) return true; // allow native re-submit
            e.preventDefault();
            isSubmitting = true;
            captureAndSetJson().then(function() {
                document.getElementById('formInforme').submit();
            });
        });

        // Finalizar (async capture → save → finalize)
        $('#btnFinalizar').on('click', function() {
            if (confirm('Finalizar el informe? Se generara el PDF y no podra editarse.')) {
                captureAndSetJson().then(function() {
                    var formData = new FormData($('#formInforme')[0]);
                    $.ajax({
                        url: $('#formInforme').attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            $('#formFinalizar').submit();
                        },
                        error: function() {
                            $('#formFinalizar').submit();
                        }
                    });
                });
            }
        });
    });

    function esc(str) { var d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
    </script>
</body>
</html>
