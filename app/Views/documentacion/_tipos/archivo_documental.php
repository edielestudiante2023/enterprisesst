<?php
/**
 * Vista de Tipo: 2.5.1 Archivo Documental
 * Carpeta maestra con todos los documentos del SG-SST
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */

// Verificar si ya existe procedimiento_control_documental para el año actual
$hayProcedimientoAnioActual = false;
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if (($d['tipo_documento'] ?? '') === 'procedimiento_control_documental' && $d['anio'] == date('Y')) {
            $hayProcedimientoAnioActual = true;
            break;
        }
    }
}
?>

<!-- Card de Carpeta con Botón Procedimiento -->
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
                <?php if ($hayProcedimientoAnioActual): ?>
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
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Documentos SST (todos los documentos) -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'archivo_documental',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>
