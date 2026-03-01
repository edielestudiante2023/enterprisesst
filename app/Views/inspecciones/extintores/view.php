<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de Extintores</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <?php if (!empty($inspeccion['fecha_vencimiento_global'])): ?>
                <tr><td class="text-muted">Vencimiento global</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_vencimiento_global'])) ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Total extintores</td><td><strong><?= $inspeccion['numero_extintores_totales'] ?? 0 ?></strong></td></tr>
            </table>
        </div>
    </div>

    <!-- Inventario -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">INVENTARIO</h6>
            <div class="row" style="font-size:13px;">
                <div class="col-6 mb-1"><span class="text-muted">ABC:</span> <?= $inspeccion['cantidad_abc'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">CO2:</span> <?= $inspeccion['cantidad_co2'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Solkaflam:</span> <?= $inspeccion['cantidad_solkaflam'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Agua:</span> <?= $inspeccion['cantidad_agua'] ?? 0 ?></div>
                <?php if (!empty($inspeccion['capacidad_libras'])): ?>
                <div class="col-12 mb-1"><span class="text-muted">Capacidad:</span> <?= esc($inspeccion['capacidad_libras']) ?></div>
                <?php endif; ?>
            </div>
            <hr style="margin:6px 0;">
            <div class="row" style="font-size:12px;">
                <div class="col-6 mb-1"><span class="text-muted">Und. residenciales:</span> <?= $inspeccion['cantidad_unidades_residenciales'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Porteria:</span> <?= $inspeccion['cantidad_porteria'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Oficina admin:</span> <?= $inspeccion['cantidad_oficina_admin'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Shut basuras:</span> <?= $inspeccion['cantidad_shut_basuras'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Salones comunales:</span> <?= $inspeccion['cantidad_salones_comunales'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Cuarto bombas:</span> <?= $inspeccion['cantidad_cuarto_bombas'] ?? 0 ?></div>
                <div class="col-6 mb-1"><span class="text-muted">Planta electrica:</span> <?= $inspeccion['cantidad_planta_electrica'] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <!-- Extintores inspeccionados -->
    <?php if (!empty($extintores)): ?>
    <div class="accordion mb-3" id="accordionViewExt">
        <?php foreach ($extintores as $i => $ext): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vext_<?= $i ?>">
                    Extintor #<?= $i + 1 ?>
                    <?php if (!empty($ext['observaciones'])): ?>
                    <small class="text-muted ms-2" style="font-size:11px;"><?= esc(mb_substr($ext['observaciones'], 0, 30)) ?>...</small>
                    <?php endif; ?>
                </button>
            </h2>
            <div id="vext_<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#accordionViewExt">
                <div class="accordion-body p-2">
                    <table class="table table-sm mb-2" style="font-size:12px;">
                        <?php foreach ($criterios as $key => $cfg): ?>
                        <tr>
                            <td class="text-muted" style="width:55%;"><?= $cfg['label'] ?></td>
                            <td>
                                <?php
                                $val = $ext[$key] ?? '';
                                $color = 'text-dark';
                                if (in_array($val, ['BUENO', 'CARGADO', 'NO'])) $color = 'text-success';
                                elseif (in_array($val, ['REGULAR'])) $color = 'text-warning';
                                elseif (in_array($val, ['MALO', 'SI', 'DESCARGADO'])) $color = 'text-danger';
                                elseif (in_array($val, ['NO APLICA', 'NO TIENE'])) $color = 'text-muted';
                                ?>
                                <strong class="<?= $color ?>"><?= esc($val) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!empty($ext['fecha_vencimiento'])): ?>
                        <tr><td class="text-muted">Fecha vencimiento</td><td><?= date('d/m/Y', strtotime($ext['fecha_vencimiento'])) ?></td></tr>
                        <?php endif; ?>
                    </table>
                    <?php if (!empty($ext['foto'])): ?>
                    <div class="mb-2">
                        <img src="<?= base_url($ext['foto']) ?>" class="img-fluid rounded"
                             style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($ext['observaciones'])): ?>
                    <p class="text-muted" style="font-size:12px; margin:0;"><i class="fas fa-comment-alt"></i> <?= esc($ext['observaciones']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Recomendaciones generales -->
    <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RECOMENDACIONES GENERALES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
        </div>
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

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/inspecciones/extintores/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/extintores/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>

        <!-- Doc 25: edit button always visible -->
        <a href="/inspecciones/extintores/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
    </div>
</div>
