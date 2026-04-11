<?php
$esAccidente = ($inv['tipo_evento'] ?? '') === 'accidente';
$tituloTipo = $esAccidente ? 'Accidente de Trabajo' : 'Incidente de Trabajo';
$testigos = $testigos ?? [];
$evidencias = $evidencias ?? [];
$medidas = $medidas ?? [];
?>

<div class="container-fluid px-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h5 class="mb-0">Investigacion de <?= $tituloTipo ?></h5>
        <span class="badge bg-<?= ($inv['estado'] ?? '') === 'completo' ? 'success' : 'secondary' ?>" style="font-size:13px;">
            <i class="fas fa-<?= ($inv['estado'] ?? '') === 'completo' ? 'check-circle' : 'pencil-alt' ?>"></i>
            <?= ucfirst($inv['estado'] ?? 'borrador') ?>
        </span>
    </div>

    <!-- Toolbar -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <?php if (!empty($inv['ruta_pdf'])): ?>
        <a href="/inspecciones/investigacion-accidente/pdf/<?= $inv['id'] ?>" target="_blank" class="btn btn-sm btn-pwa-primary" style="background:#bd9751; color:#fff; border:none; border-radius:6px;">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if (($inv['estado'] ?? '') === 'completo'): ?>
        <a href="/inspecciones/investigacion-accidente/regenerar/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-secondary"
           onclick="return confirm('Regenerar el PDF con la plantilla actual?')">
            <i class="fas fa-sync-alt"></i> Regenerar PDF
        </a>
        <a href="/inspecciones/investigacion-accidente/enviar-email/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-secondary"
           onclick="return confirm('Enviar el PDF por email?')">
            <i class="fas fa-envelope"></i> Enviar Email
        </a>
        <?php endif; ?>
        <a href="/inspecciones/investigacion-accidente/firma/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-dark">
            <i class="fas fa-signature"></i> Firmas
        </a>
        <a href="/inspecciones/investigacion-accidente" class="btn btn-sm btn-outline-dark">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Datos del Evento -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-exclamation-triangle"></i> Datos del Evento</h6>
            <table class="table table-sm table-borderless mb-0" style="font-size:14px;">
                <tr>
                    <td class="text-muted" style="width:40%;">Tipo de evento</td>
                    <td>
                        <?php if ($esAccidente): ?>
                            <span class="badge bg-danger">Accidente</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Incidente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($esAccidente && !empty($inv['gravedad'])): ?>
                <tr>
                    <td class="text-muted">Gravedad</td>
                    <td class="fw-bold"><?= ucfirst(esc($inv['gravedad'])) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Fecha del evento</td>
                    <td><?= date('d/m/Y', strtotime($inv['fecha_evento'])) ?></td>
                </tr>
                <?php if (!empty($inv['hora_evento'])): ?>
                <tr>
                    <td class="text-muted">Hora</td>
                    <td><?= date('h:i A', strtotime($inv['hora_evento'])) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['lugar_exacto'])): ?>
                <tr>
                    <td class="text-muted">Lugar exacto</td>
                    <td><?= esc($inv['lugar_exacto']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Descripcion</td>
                    <td><?= nl2br(esc($inv['descripcion_detallada'] ?? '')) ?></td>
                </tr>
                <?php if (!empty($inv['fecha_investigacion'])): ?>
                <tr>
                    <td class="text-muted">Fecha de investigacion</td>
                    <td><?= date('d/m/Y', strtotime($inv['fecha_investigacion'])) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Datos del Trabajador -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-user-injured"></i> <?= $esAccidente ? 'Trabajador Lesionado' : 'Trabajador Involucrado' ?></h6>
            <table class="table table-sm table-borderless mb-0" style="font-size:14px;">
                <tr>
                    <td class="text-muted" style="width:40%;">Nombre</td>
                    <td class="fw-bold"><?= esc($inv['nombre_trabajador'] ?? '') ?></td>
                </tr>
                <?php if (!empty($inv['documento_trabajador'])): ?>
                <tr>
                    <td class="text-muted">Documento</td>
                    <td><?= esc($inv['documento_trabajador']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['cargo_trabajador'])): ?>
                <tr>
                    <td class="text-muted">Cargo</td>
                    <td><?= esc($inv['cargo_trabajador']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['area_trabajador'])): ?>
                <tr>
                    <td class="text-muted">Area</td>
                    <td><?= esc($inv['area_trabajador']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['antiguedad_trabajador'])): ?>
                <tr>
                    <td class="text-muted">Antiguedad</td>
                    <td><?= esc($inv['antiguedad_trabajador']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['tipo_vinculacion'])): ?>
                <tr>
                    <td class="text-muted">Tipo vinculacion</td>
                    <td><?= ucfirst(str_replace('_', ' ', esc($inv['tipo_vinculacion']))) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['jornada_habitual'])): ?>
                <tr>
                    <td class="text-muted">Jornada habitual</td>
                    <td><?= esc($inv['jornada_habitual']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Datos de Lesion (solo accidente) -->
    <?php if ($esAccidente): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-band-aid"></i> Datos de la Lesion</h6>
            <table class="table table-sm table-borderless mb-0" style="font-size:14px;">
                <?php if (!empty($inv['parte_cuerpo_lesionada'])): ?>
                <tr>
                    <td class="text-muted" style="width:40%;">Parte del cuerpo</td>
                    <td><?= esc($inv['parte_cuerpo_lesionada']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['tipo_lesion'])): ?>
                <tr>
                    <td class="text-muted">Tipo de lesion</td>
                    <td><?= esc($inv['tipo_lesion']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['agente_accidente'])): ?>
                <tr>
                    <td class="text-muted">Agente del accidente</td>
                    <td><?= esc($inv['agente_accidente']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['mecanismo_accidente'])): ?>
                <tr>
                    <td class="text-muted">Mecanismo</td>
                    <td><?= esc($inv['mecanismo_accidente']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['dias_incapacidad'])): ?>
                <tr>
                    <td class="text-muted">Dias de incapacidad</td>
                    <td><?= esc($inv['dias_incapacidad']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inv['numero_furat'])): ?>
                <tr>
                    <td class="text-muted">No. FURAT</td>
                    <td><?= esc($inv['numero_furat']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Potencial de Dano (solo incidente) -->
    <?php if (!$esAccidente && !empty($inv['potencial_danio'])): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-bolt"></i> Potencial de Dano</h6>
            <p class="mb-0" style="font-size:14px;"><?= nl2br(esc($inv['potencial_danio'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Analisis Causal -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-search"></i> Analisis Causal (Res. 1401/2007)</h6>

            <?php if (!empty($inv['actos_substandar']) || !empty($inv['condiciones_substandar'])): ?>
            <p class="fw-bold mb-1" style="font-size:13px;">Causas Inmediatas</p>
            <?php if (!empty($inv['actos_substandar'])): ?>
            <div class="mb-2">
                <small class="text-muted">Actos subestandar:</small>
                <p style="font-size:14px;"><?= nl2br(esc($inv['actos_substandar'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inv['condiciones_substandar'])): ?>
            <div class="mb-3">
                <small class="text-muted">Condiciones subestandar:</small>
                <p style="font-size:14px;"><?= nl2br(esc($inv['condiciones_substandar'])) ?></p>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($inv['factores_personales']) || !empty($inv['factores_trabajo'])): ?>
            <p class="fw-bold mb-1" style="font-size:13px;">Causas Basicas</p>
            <?php if (!empty($inv['factores_personales'])): ?>
            <div class="mb-2">
                <small class="text-muted">Factores personales:</small>
                <p style="font-size:14px;"><?= nl2br(esc($inv['factores_personales'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inv['factores_trabajo'])): ?>
            <div class="mb-3">
                <small class="text-muted">Factores del trabajo:</small>
                <p style="font-size:14px;"><?= nl2br(esc($inv['factores_trabajo'])) ?></p>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($inv['metodologia_analisis'])): ?>
            <div class="mb-2">
                <small class="text-muted">Metodologia:</small>
                <span style="font-size:14px;">
                    <?php
                    $metodos = ['arbol_causas' => 'Arbol de causas', 'espina_pescado' => 'Espina de pescado (Ishikawa)', '5_porques' => '5 Porques', 'otra' => 'Otra'];
                    echo esc($metodos[$inv['metodologia_analisis']] ?? $inv['metodologia_analisis']);
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($inv['descripcion_analisis'])): ?>
            <div>
                <small class="text-muted">Descripcion del analisis:</small>
                <p style="font-size:14px;"><?= nl2br(esc($inv['descripcion_analisis'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Equipo Investigador -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-users"></i> Equipo Investigador</h6>
            <?php
            $equipo = [
                ['label' => 'Jefe Inmediato', 'nombre' => $inv['investigador_jefe_nombre'] ?? '', 'cargo' => $inv['investigador_jefe_cargo'] ?? '', 'firma' => $firmas['jefe'] ?? ''],
                ['label' => 'Representante COPASST', 'nombre' => $inv['investigador_copasst_nombre'] ?? '', 'cargo' => $inv['investigador_copasst_cargo'] ?? '', 'firma' => $firmas['copasst'] ?? ''],
                ['label' => 'Responsable SST', 'nombre' => $inv['investigador_sst_nombre'] ?? '', 'cargo' => $inv['investigador_sst_cargo'] ?? '', 'firma' => $firmas['sst'] ?? ''],
            ];
            foreach ($equipo as $miembro):
                if (empty($miembro['nombre'])) continue;
            ?>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:14px;">
                <div>
                    <strong><?= esc($miembro['nombre']) ?></strong>
                    <br><small class="text-muted"><?= esc($miembro['cargo']) ?> - <?= $miembro['label'] ?></small>
                </div>
                <div>
                    <?php if (!empty($miembro['firma'])): ?>
                        <img src="<?= $miembro['firma'] ?>" style="max-height:40px; max-width:100px;">
                    <?php else: ?>
                        <span class="badge bg-secondary" style="font-size:11px;">Sin firma</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Testigos -->
    <?php if (!empty($testigos)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-user-friends"></i> Testigos (<?= count($testigos) ?>)</h6>
            <?php foreach ($testigos as $t): ?>
            <div class="border-bottom py-2" style="font-size:14px;">
                <div class="fw-bold"><?= esc($t['nombre'] ?? '') ?></div>
                <div class="text-muted" style="font-size:12px;">Cargo: <?= esc($t['cargo'] ?? '') ?></div>
                <?php if (!empty($t['declaracion'])): ?>
                <div class="mt-1"><?= nl2br(esc($t['declaracion'])) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Evidencia Fotografica -->
    <?php if (!empty($evidencias)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-camera"></i> Evidencia Fotografica (<?= count($evidencias) ?>)</h6>
            <div class="row g-2">
                <?php foreach ($evidencias as $ev): ?>
                <div class="col-6">
                    <?php if (!empty($ev['imagen'])): ?>
                    <a href="<?= base_url($ev['imagen']) ?>" target="_blank">
                        <img src="<?= base_url($ev['imagen']) ?>" class="img-fluid rounded" style="max-height:120px; object-fit:cover; width:100%;">
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($ev['descripcion'])): ?>
                    <div style="font-size:12px; color:#666; margin-top:4px;"><?= esc($ev['descripcion']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Medidas Correctivas -->
    <?php if (!empty($medidas)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-tools"></i> Medidas Correctivas (<?= count($medidas) ?>)</h6>
            <?php
            $tipoLabels = ['fuente' => 'En la fuente', 'medio' => 'En el medio', 'trabajador' => 'En el trabajador'];
            $estadoColors = ['pendiente' => 'warning', 'en_proceso' => 'info', 'cumplida' => 'success'];
            foreach ($medidas as $m):
            ?>
            <div class="border-bottom py-2" style="font-size:14px;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-secondary" style="font-size:11px;"><?= $tipoLabels[$m['tipo_medida'] ?? ''] ?? ucfirst($m['tipo_medida'] ?? '') ?></span>
                    <span class="badge bg-<?= $estadoColors[$m['estado'] ?? ''] ?? 'secondary' ?>" style="font-size:11px;"><?= ucfirst(str_replace('_', ' ', $m['estado'] ?? '')) ?></span>
                </div>
                <div class="mt-1"><?= esc($m['descripcion'] ?? '') ?></div>
                <div class="text-muted" style="font-size:12px;">
                    <?php if (!empty($m['responsable'])): ?>
                        <i class="fas fa-user"></i> <?= esc($m['responsable']) ?>
                    <?php endif; ?>
                    <?php if (!empty($m['fecha_cumplimiento'])): ?>
                        &middot; <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($m['fecha_cumplimiento'])) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-4"></div>
</div>
