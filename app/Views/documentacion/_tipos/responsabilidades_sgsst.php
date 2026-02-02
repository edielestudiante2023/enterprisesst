<?php
/**
 * Vista de Tipo: 1.1.2 Responsabilidades en el SG-SST
 * Carpeta con dropdown para 3 documentos de responsabilidades
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $contextoCliente
 */

// Determinar si es Vigía o Delegado según nivel de estándares
$nivelEstandares = $contextoCliente['estandares_aplicables'] ?? 60;
$esVigia = $nivelEstandares <= 7;
$nombreDocRepLegal = $esVigia
    ? 'Resp. Rep. Legal + Vigía SST'
    : 'Resp. Rep. Legal + Delegado SST';

// Verificar qué documentos ya existen para el año actual
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

<!-- Card de Carpeta con Dropdown de Documentos -->
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
                        <i class="bi bi-lock me-1"></i>Nuevo Documento
                    </button>
                <?php else: ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Fases -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'responsabilidades_sgsst',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'responsabilidades_sgsst',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>
