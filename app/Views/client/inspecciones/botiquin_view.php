<?php
// Agrupar elementos por grupo
$grupos = [];
foreach ($elementos as $clave => $config) {
    $grupos[$config['grupo']][$clave] = $config;
}

function estadoColorClient(string $estado): string {
    $map = [
        'BUEN ESTADO'     => 'text-success',
        'ESTADO REGULAR'  => 'text-warning',
        'MAL ESTADO'      => 'text-danger',
        'SIN EXISTENCIAS' => 'text-danger',
        'VENCIDO'         => 'text-danger',
        'NO APLICA'       => 'text-muted',
    ];
    return $map[$estado] ?? 'text-dark';
}
?>

<div class="page-header">
    <h1><i class="fas fa-first-aid me-2"></i> Inspección de Botiquín</h1>
    <a href="<?= base_url('client/inspecciones/botiquin') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Fecha inspección</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <?php if (!empty($inspeccion['ubicacion_botiquin'])): ?>
            <tr><td class="text-muted">Ubicación</td><td><?= esc($inspeccion['ubicacion_botiquin']) ?></td></tr>
            <?php endif; ?>
            <tr><td class="text-muted">Tipo botiquín</td><td><?= esc($inspeccion['tipo_botiquin'] ?? 'LONA') ?></td></tr>
            <tr><td class="text-muted">Estado botiquín</td><td><strong class="<?= estadoColorClient($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?></strong></td></tr>
        </table>
    </div>
</div>

<!-- Fotos del botiquín -->
<?php if (!empty($inspeccion['foto_1']) || !empty($inspeccion['foto_2'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">FOTOS DEL BOTIQUÍN</h6>
        <div class="row g-2">
            <?php foreach (['foto_1', 'foto_2'] as $campo): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                         style="max-height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Condiciones generales SI/NO -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">CONDICIONES GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:13px;">
            <?php
            $preguntas = [
                'instalado_pared'  => 'Instalado en la pared',
                'libre_obstaculos' => 'Libre de obstáculos',
                'lugar_visible'    => 'Localizado en lugar visible',
                'con_senalizacion' => 'Con señalización',
            ];
            foreach ($preguntas as $campo => $label):
                $valor = $inspeccion[$campo] ?? 'SI';
                $color = $valor === 'SI' ? 'text-success' : 'text-danger';
            ?>
            <tr>
                <td class="text-muted" style="width:65%;"><?= $label ?></td>
                <td><strong class="<?= $color ?>"><?= esc($valor) ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Elementos por grupo (accordion) -->
<div class="accordion mb-3" id="accordionBotClient">
    <?php $secNum = 0; foreach ($grupos as $grupoNombre => $items): $secNum++; ?>
    <div class="accordion-item" style="border:none; margin-bottom:2px;">
        <h2 class="accordion-header">
            <button class="accordion-button <?= $secNum > 1 ? 'collapsed' : '' ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#cbot_<?= $secNum ?>"
                    style="background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%); color:white; font-weight:600; font-size:14px;">
                <?= esc($grupoNombre) ?> (<?= count($items) ?>)
            </button>
        </h2>
        <div id="cbot_<?= $secNum ?>" class="accordion-collapse collapse <?= $secNum === 1 ? 'show' : '' ?>" data-bs-parent="#accordionBotClient">
            <div class="accordion-body p-2" style="background:white;">
                <table class="table table-sm mb-0" style="font-size:12px;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th>Elemento</th>
                            <th style="width:50px;">Cant.</th>
                            <th style="width:50px;">Min.</th>
                            <th>Estado</th>
                            <?php if ($grupoNombre === 'Antisepticos y soluciones'): ?>
                            <th>Vencimiento</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $clave => $config):
                            $data = $elementosData[$clave] ?? null;
                            $cantidad = (int)($data['cantidad'] ?? 0);
                            $estado = $data['estado'] ?? 'SIN EXISTENCIAS';
                            $cantColor = $cantidad >= $config['min'] ? 'text-success' : 'text-danger';
                        ?>
                        <tr>
                            <td><?= esc($config['label']) ?>
                                <?php if (!empty($config['medicamento'])): ?>
                                <span class="badge bg-info" style="font-size:9px;">Med</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?= $cantColor ?>"><strong><?= $cantidad ?></strong></td>
                            <td class="text-muted"><?= $config['min'] ?></td>
                            <td><span class="<?= estadoColorClient($estado) ?>"><?= esc($estado) ?></span></td>
                            <?php if ($config['venc']): ?>
                            <td>
                                <?php if (!empty($data['fecha_vencimiento'])): ?>
                                    <?php
                                    $fv = $data['fecha_vencimiento'];
                                    $vencido = $fv < date('Y-m-d');
                                    ?>
                                    <span class="<?= $vencido ? 'text-danger fw-bold' : '' ?>"><?= date('d/m/Y', strtotime($fv)) ?></span>
                                    <?php if ($vencido): ?> <i class="fas fa-exclamation-triangle text-danger" style="font-size:10px;"></i><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <?php elseif ($grupoNombre === 'Antisepticos y soluciones'): ?>
                            <td>-</td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($grupoNombre === 'Equipos de inmovilizacion'): ?>
                <div class="mt-2">
                    <?php if (!empty($inspeccion['foto_tabla_espinal'])): ?>
                    <div class="mb-2">
                        <small class="text-muted">Foto tabla espinal:</small>
                        <img src="/<?= esc($inspeccion['foto_tabla_espinal']) ?>" class="img-fluid rounded d-block mt-1"
                             style="max-height:80px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($inspeccion['obs_tabla_espinal'])): ?>
                    <p style="font-size:12px; margin:0 0 4px;"><i class="fas fa-comment-alt text-muted"></i> <?= esc($inspeccion['obs_tabla_espinal']) ?></p>
                    <?php endif; ?>

                    <table class="table table-sm mb-1" style="font-size:12px;">
                        <tr>
                            <td class="text-muted" style="width:55%;">Estado collares</td>
                            <td><strong class="<?= estadoColorClient($inspeccion['estado_collares'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_collares'] ?? 'BUEN ESTADO') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Estado inmovilizadores</td>
                            <td><strong class="<?= estadoColorClient($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') ?></strong></td>
                        </tr>
                    </table>

                    <div class="row g-2">
                        <?php if (!empty($inspeccion['foto_collares'])): ?>
                        <div class="col-6">
                            <small class="text-muted">Foto collares:</small>
                            <img src="/<?= esc($inspeccion['foto_collares']) ?>" class="img-fluid rounded d-block mt-1"
                                 style="max-height:80px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($inspeccion['foto_inmovilizadores'])): ?>
                        <div class="col-6">
                            <small class="text-muted">Foto inmovilizadores:</small>
                            <img src="/<?= esc($inspeccion['foto_inmovilizadores']) ?>" class="img-fluid rounded d-block mt-1"
                                 style="max-height:80px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recomendaciones -->
<?php if (!empty($inspeccion['recomendaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">RECOMENDACIONES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['recomendaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Pendientes generados -->
<?php if (!empty($inspeccion['pendientes_generados'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">PENDIENTES GENERADOS</h6>
        <pre style="font-size:12px; margin:0; white-space:pre-wrap; font-family:inherit;"><?= esc($inspeccion['pendientes_generados']) ?></pre>
    </div>
</div>
<?php endif; ?>

<!-- Botón PDF -->
<?php if (!empty($inspeccion['ruta_pdf'])): ?>
<div class="text-center mb-4">
    <a href="/inspecciones/botiquin/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pdf" target="_blank">
        <i class="fas fa-file-pdf me-2"></i> Descargar PDF
    </a>
</div>
<?php endif; ?>

<!-- Modal foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-1 text-center">
                <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
<script>
function openPhoto(src) {
    document.getElementById('photoFull').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>

<style>
    .accordion-button::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }
    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%) !important;
    }
</style>
