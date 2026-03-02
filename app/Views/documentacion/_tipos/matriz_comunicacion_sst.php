<?php
/**
 * Vista de Tipo: 2.8.1.1 Matriz de Comunicacion SST
 * Sub-carpeta de 2.8.1 para gestionar la matriz de comunicacion del SG-SST
 * SIN boton de crear documento - el documento se crea desde el modulo
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $soportesAdicionales, $subcarpetas
 */
?>

<!-- Card de Carpeta -->
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
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url('matriz-comunicacion') ?>" class="btn btn-primary" target="_blank">
                    <i class="bi bi-diagram-3 me-1"></i>Abrir Matriz de Comunicacion
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-diagram-3 me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Matriz de Comunicacion SST</h6>
            <p class="mb-0 small">
                Protocolos de comunicacion interna y externa del SG-SST de
                <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>.
                Define que comunicar, quien comunica, a quien, por que canal y en que plazo
                ante situaciones como accidentes, emergencias, acoso laboral, enfermedades laborales, entre otros.
            </p>
        </div>
    </div>
</div>

<!-- Acceso rapido al modulo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Modulo de Matriz de Comunicacion</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Gestione los protocolos de comunicacion SST: cree protocolos manualmente, genere con IA o importe desde CSV.</p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('matriz-comunicacion') ?>" class="btn btn-outline-primary" target="_blank">
                <i class="bi bi-table me-1"></i>Ver Matriz
            </a>
            <a href="<?= base_url('matriz-comunicacion/generar-ia') ?>" class="btn btn-outline-success" target="_blank">
                <i class="bi bi-magic me-1"></i>Generar con IA
            </a>
            <a href="<?= base_url('matriz-comunicacion/importar') ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>Importar CSV
            </a>
        </div>
    </div>
</div>

<!-- Procedimiento asociado -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Procedimiento de Matriz de Comunicacion</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Documento que establece la metodologia para identificar, documentar y mantener los protocolos de comunicacion del SG-SST.</p>
        <?php
        $hayAprobadoAnioActual = false;
        if (!empty($documentosSSTAprobados)) {
            foreach ($documentosSSTAprobados as $d) {
                if (($d['anio'] ?? '') == date('Y')) {
                    $hayAprobadoAnioActual = true;
                    break;
                }
            }
        }
        ?>
        <?php if ($hayAprobadoAnioActual): ?>
            <a href="<?= base_url('documentos/generar/procedimiento_matriz_comunicacion/' . $cliente['id_cliente']) ?>"
               class="btn btn-outline-success">
                <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= date('Y') ?>
            </a>
        <?php else: ?>
            <a href="<?= base_url('documentos/generar/procedimiento_matriz_comunicacion/' . $cliente['id_cliente']) ?>"
               class="btn btn-success">
                <i class="bi bi-magic me-1"></i>Crear Procedimiento con IA <?= date('Y') ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'matriz_comunicacion_sst',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Soportes -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Soportes Matriz de Comunicacion',
    'subtitulo' => 'Evidencias de comunicacion SST',
    'icono' => 'bi-diagram-3',
    'colorHeader' => 'primary',
    'codigoDefault' => 'SOP-MCO',
    'emptyIcon' => 'bi-diagram-3',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar evidencias de comunicacion.'
]) ?>
