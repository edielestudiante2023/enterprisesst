<?php
/**
 * Vista de Tipo: 2.8.1 Mecanismos de Comunicación, Auto Reporte en SG-SST
 * Procedimiento simple con IA (sin fases previas)
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */

// Verificar si hay documento aprobado para el año actual
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

<!-- Card de Carpeta con Boton IA -->
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
                <?php if ($hayAprobadoAnioActual): ?>
                    <a href="<?= base_url('documentos/generar/mecanismos_comunicacion_sgsst/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva versión <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/mecanismos_comunicacion_sgsst/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-primary mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-megaphone me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Mecanismos de Comunicacion y Auto Reporte</h6>
            <p class="mb-0 small">
                Documento que establece los canales de comunicacion interna, externa y los procedimientos
                para que los trabajadores de <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>
                reporten condiciones de trabajo y salud, conforme al estandar 2.8.1 de la Resolucion 0312/2019.
            </p>
        </div>
    </div>
</div>

<!-- Requisitos del Estandar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Requisitos del Estandar 2.8.1</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Segun la Resolucion 0312/2019, este documento debe demostrar:</p>
        <ul class="mb-0">
            <li>Mecanismos eficaces para recibir y responder comunicaciones internas y externas del SG-SST</li>
            <li>Canales que permitan recolectar inquietudes, ideas y aportes de los trabajadores</li>
            <li>Procedimiento para el auto reporte de condiciones de trabajo y salud</li>
            <li>Evidencia de que la informacion es accesible para todos los niveles de la organizacion</li>
        </ul>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'mecanismos_comunicacion_sgsst',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?>
</div>
