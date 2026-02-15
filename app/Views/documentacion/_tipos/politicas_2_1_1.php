<?php
/**
 * Vista de Tipo: 2.1.1 Políticas de Seguridad y Salud en el Trabajo
 * Carpeta con dropdown para 6 políticas de SST
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
$totalEsperado = 7; // 7 políticas (incluye Desconexión Laboral - Ley 2191/2022)
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
                            <?php
                            // Listado de políticas: siempre visibles (§3.7 ZZ_22_PROMPTREPARACIONES)
                            // Si ya existe, mostrar "Nueva versión" en lugar de ocultar
                            $politicas = [
                                'politica_sst_general'            => ['icono' => 'bi-shield-check',        'color' => 'text-primary', 'nombre' => 'Política SST General'],
                                'politica_alcohol_drogas'         => ['icono' => 'bi-cup-straw',           'color' => 'text-warning', 'nombre' => 'Política Alcohol y SPA'],
                                'politica_acoso_laboral'          => ['icono' => 'bi-person-x',            'color' => 'text-danger',  'nombre' => 'Política Acoso Laboral'],
                                'politica_violencias_genero'      => ['icono' => 'bi-gender-ambiguous',    'color' => 'text-purple',  'nombre' => 'Política Violencias de Género'],
                                'politica_discriminacion'         => ['icono' => 'bi-people',              'color' => 'text-info',    'nombre' => 'Política Discriminación'],
                                'politica_desconexion_laboral'    => ['icono' => 'bi-power',               'color' => 'text-success', 'nombre' => 'Política Desconexión Laboral'],
                                'politica_prevencion_emergencias' => ['icono' => 'bi-exclamation-triangle','color' => 'text-danger',  'nombre' => 'Política Emergencias'],
                            ];
                            foreach ($politicas as $tipo => $info):
                                $yaExiste = isset($docsExistentesTipos[$tipo]);
                                $url = base_url('documentos/generar/' . $tipo . '/' . $cliente['id_cliente']);
                            ?>
                            <li>
                                <?php if ($yaExiste): ?>
                                    <a href="<?= $url ?>" class="dropdown-item">
                                        <i class="bi bi-arrow-repeat me-2 text-success"></i>Nueva versión: <?= esc($info['nombre']) ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $url ?>" class="dropdown-item">
                                        <i class="bi <?= $info['icono'] ?> me-2 <?= $info['color'] ?>"></i><?= esc($info['nombre']) ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                            <?php if (count($docsExistentesTipos) >= $totalEsperado): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2 text-success"></i>Todas creadas <?= date('Y') ?></span></li>
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
                <li>Política de Desconexión Laboral (Ley 2191/2022)</li>
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
