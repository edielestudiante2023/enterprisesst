<?php
/**
 * Vista de Tipo: 1.2.4 Reglamento de Higiene y Seguridad Industrial
 * Documento normativo generado con IA (Variante B - secciones_ia)
 *
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */

// Verificar si hay documento para el año actual
$hayAprobadoAnioActual = false;
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if (($d['anio'] ?? date('Y')) == date('Y')) {
            $hayAprobadoAnioActual = true;
            break;
        }
    }
}

$anioActual = date('Y');
?>

<!-- Card de Carpeta con Boton IA -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-shield-check text-success me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-success me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <span class="badge bg-info">Documento con IA</span>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-2"><?= esc($carpeta['descripcion']) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0 mt-2">
                        Documento obligatorio que establece las normas internas de higiene y seguridad industrial
                        para los trabajadores, en cumplimiento del Codigo Sustantivo del Trabajo (arts. 349-352),
                        Resolucion 1016/1989 y Decreto 1072/2015.
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($hayAprobadoAnioActual): ?>
                    <a href="<?= base_url('documentos/generar/reglamento_higiene_seguridad/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= $anioActual ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/reglamento_higiene_seguridad/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= $anioActual ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion del Auditor -->
<div class="alert alert-success border-0 mb-4">
    <div class="d-flex">
        <div class="me-3">
            <i class="bi bi-clipboard-check fs-4"></i>
        </div>
        <div>
            <strong>Requisito Legal:</strong>
            <p class="mb-0 small">
                Obligatorio para empresas con 10 o mas trabajadores permanentes segun Resolucion 1016 de 1989
                y articulos 349 a 352 del Codigo Sustantivo del Trabajo. Debe publicarse en lugar visible
                y darse a conocer a todos los trabajadores.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'reglamento_higiene_seguridad',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<?php if (!empty($subcarpetas)): ?>
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>
<?php endif; ?>
