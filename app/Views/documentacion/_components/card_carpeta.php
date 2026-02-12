<?php
/**
 * Componente: Card de Carpeta con Botones
 * Variables requeridas: $carpeta, $cliente, $tipoCarpetaFases, $fasesInfo,
 *                       $documentosSSTAprobados, $contextoCliente (opcional)
 */
?>
<!-- Header de carpeta -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-folder-fill text-warning me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Crear con IA
                    </button>
                <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsabilidades_sgsst'): ?>
                    <!-- 1.1.2: Dropdown con los 3 documentos de responsabilidades -->
                    <?php
                    $nivelEstandares = $contextoCliente['estandares_aplicables'] ?? 60;
                    $esVigia = $nivelEstandares <= 7;
                    $nombreDocRepLegal = $esVigia
                        ? 'Resp. Rep. Legal + Vigia SST'
                        : 'Resp. Rep. Legal + Delegado SST';
                    $docsExistentesTipos = [];
                    if (!empty($documentosSSTAprobados)) {
                        foreach ($documentosSSTAprobados as $d) {
                            if ($d['anio'] == date('Y')) {
                                $docsExistentesTipos[$d['tipo_documento']] = true;
                            }
                        }
                    }
                    $totalEsperado = 3;
                    ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (!isset($docsExistentesTipos['responsabilidades_rep_legal_sgsst'])): ?>
                            <li>
                                <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-responsabilidades-rep-legal') ?>" method="post">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-person-badge me-2 text-primary"></i><?= esc($nombreDocRepLegal) ?>
                                    </button>
                                </form>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['responsabilidades_responsable_sgsst'])): ?>
                            <li>
                                <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-responsabilidades-responsable-sst') ?>" method="post">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-person-gear me-2 text-success"></i>Resp. Responsable SG-SST
                                    </button>
                                </form>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['responsabilidades_trabajadores_sgsst'])): ?>
                            <li>
                                <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-responsabilidades-trabajadores') ?>" method="post">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-people me-2 text-warning"></i>Resp. Trabajadores (Imprimible)
                                    </button>
                                </form>
                            </li>
                            <?php endif; ?>
                            <?php if (count($docsExistentesTipos) >= $totalEsperado): ?>
                            <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i>Todos creados <?= date('Y') ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php elseif (isset($tipoCarpetaFases) && in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst'])): ?>
                    <?php
                    $hayAprobadoAnioActual = false;
                    if (!empty($documentosSSTAprobados)) {
                        foreach ($documentosSSTAprobados as $d) {
                            if ($d['anio'] == date('Y')) {
                                $hayAprobadoAnioActual = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <?php if (!$hayAprobadoAnioActual): ?>
                        <?php if ($tipoCarpetaFases === 'responsables_sst'): ?>
                            <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-asignacion-responsable-sst') ?>" method="post" style="display:inline;">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-file-earmark-plus me-1"></i>Generar Asignacion <?= date('Y') ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
                               class="btn btn-success">
                                <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'presupuesto_sst'): ?>
                    <!-- 1.1.3 Presupuesto SST: Enlace directo al modulo -->
                    <a href="<?= base_url('documentos-sst/presupuesto/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-currency-dollar me-1"></i>Abrir Presupuesto SST
                    </a>
                <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'archivo_documental'): ?>
                    <!-- 2.5.1 Control Documental: Enlace al procedimiento -->
                    <?php
                    $hayProcedimientoAnio = false;
                    if (!empty($documentosSSTAprobados)) {
                        foreach ($documentosSSTAprobados as $d) {
                            if (($d['tipo_documento'] ?? '') === 'procedimiento_control_documental' && $d['anio'] == date('Y')) {
                                $hayProcedimientoAnio = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <?php if ($hayProcedimientoAnio): ?>
                        <a href="<?= base_url('documentos/generar/procedimiento_control_documental/' . $cliente['id_cliente']) ?>"
                           class="btn btn-outline-success">
                            <i class="bi bi-arrow-repeat me-1"></i>Nueva versión <?= date('Y') ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('documentos/generar/procedimiento_control_documental/' . $cliente['id_cliente']) ?>"
                           class="btn btn-primary">
                            <i class="bi bi-file-earmark-text me-1"></i>Procedimiento Control Documental
                        </a>
                    <?php endif; ?>
                <?php elseif (!isset($tipoCarpetaFases)): ?>
                    <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) ?>"
                       class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
