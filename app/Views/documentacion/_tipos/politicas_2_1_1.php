<?php
/**
 * Vista de Tipo: 2.1.1 Políticas de Seguridad y Salud en el Trabajo
 * Carpeta con dropdown para 5 políticas de SST
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $contextoCliente
 */

// Determinar si es Vigía o COPASST según nivel de estándares
$nivelEstandares = $contextoCliente['estandares_aplicables'] ?? 60;
$esVigia = $nivelEstandares <= 10;
$textoComite = $esVigia ? 'Vigía SST' : 'COPASST';

// Verificar qué políticas ya existen para el año actual
$docsExistentesTipos = [];
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['anio'] == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
$totalEsperado = 6; // 6 políticas
?>

<!-- Card de Carpeta con Dropdown de Políticas -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-shield-check text-primary me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-primary me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <p class="text-muted mb-0 mt-1">
                    Políticas del SG-SST firmadas, fechadas y comunicadas al <?= $textoComite ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Nueva Política
                    </button>
                <?php else: ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg me-1"></i>Nueva Política
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (!isset($docsExistentesTipos['politica_sst_general'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/politica_sst_general/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-shield-check me-2 text-primary"></i>Política SST General
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['politica_alcohol_drogas'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/politica_alcohol_drogas/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-cup-straw me-2 text-warning"></i>Política Alcohol y SPA
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['politica_acoso_laboral'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/politica_acoso_laboral/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-person-x me-2 text-danger"></i>Política Acoso Laboral
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['politica_violencias_genero'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/politica_violencias_genero/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-gender-ambiguous me-2 text-purple"></i>Política Violencias de Género
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['politica_discriminacion'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/politica_discriminacion/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-people me-2 text-info"></i>Política Discriminación
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!isset($docsExistentesTipos['politica_prevencion_emergencias'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/politica_prevencion_emergencias/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Política Emergencias
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (count($docsExistentesTipos) >= $totalEsperado): ?>
                            <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i>Todas creadas <?= date('Y') ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Info de Políticas Requeridas -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex">
        <i class="bi bi-info-circle me-3 fs-4"></i>
        <div>
            <h6 class="alert-heading mb-1">Políticas Requeridas - Resolución 0312/2019</h6>
            <small class="text-muted">
                El estándar 2.1.1 requiere las siguientes políticas firmadas y comunicadas al <?= $textoComite ?>:
            </small>
            <ol class="mb-0 mt-2 small">
                <li>Política de Seguridad y Salud en el Trabajo (SST)</li>
                <li>Política de Prevención del Consumo de Alcohol, Tabaco y SPA</li>
                <li>Política de Prevención del Acoso Laboral</li>
                <li>Política de Prevención del Acoso Sexual y Violencias de Género</li>
                <li>Política de Prevención de la Discriminación, Maltrato y Violencia</li>
                <li>Política de Prevención y Respuesta ante Emergencias</li>
            </ol>
        </div>
    </div>
</div>

<!-- Panel de Fases (si aplica) -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'politicas_2_1_1',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'politicas_2_1_1',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>
