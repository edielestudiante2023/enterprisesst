<?php
$isEdit = !empty($inv);
$action = $isEdit ? '/inspecciones/investigacion-accidente/update/' . $inv['id'] : '/inspecciones/investigacion-accidente/store';
$testigos = $testigos ?? [];
$evidencias = $evidencias ?? [];
$medidas = $medidas ?? [];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="investigacionForm">
        <?= csrf_field() ?>

        <!-- Errores de validacion -->
        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('msg') ?>
        </div>
        <?php endif; ?>

        <!-- Accordion -->
        <div class="accordion mt-2" id="accordionInvestigacion">

            <!-- 1. DATOS DEL EVENTO -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secEvento">
                        1. Datos del Evento
                    </button>
                </h2>
                <div id="secEvento" class="accordion-collapse collapse show" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <!-- Cliente -->
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_cliente" value="<?= esc($inv['id_cliente']) ?>">
                        <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Tipo de evento -->
                        <div class="mb-3">
                            <label class="form-label">Tipo de evento *</label>
                            <select name="tipo_evento" id="tipo_evento" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="accidente" <?= ($inv['tipo_evento'] ?? '') === 'accidente' ? 'selected' : '' ?>>Accidente de trabajo</option>
                                <option value="incidente" <?= ($inv['tipo_evento'] ?? '') === 'incidente' ? 'selected' : '' ?>>Incidente de trabajo</option>
                            </select>
                        </div>

                        <!-- Gravedad (solo accidente) -->
                        <div class="mb-3 seccion-accidente" style="display:<?= ($inv['tipo_evento'] ?? '') === 'accidente' ? 'block' : 'none' ?>;">
                            <label class="form-label">Gravedad *</label>
                            <select name="gravedad" id="gravedad" class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="leve" <?= ($inv['gravedad'] ?? '') === 'leve' ? 'selected' : '' ?>>Leve</option>
                                <option value="grave" <?= ($inv['gravedad'] ?? '') === 'grave' ? 'selected' : '' ?>>Grave</option>
                                <option value="mortal" <?= ($inv['gravedad'] ?? '') === 'mortal' ? 'selected' : '' ?>>Mortal</option>
                            </select>
                        </div>

                        <!-- Fecha y hora evento -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha del evento *</label>
                                <input type="date" name="fecha_evento" class="form-control" value="<?= $inv['fecha_evento'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hora del evento</label>
                                <input type="time" name="hora_evento" class="form-control" value="<?= $inv['hora_evento'] ?? '' ?>">
                            </div>
                        </div>

                        <!-- Lugar exacto -->
                        <div class="mb-3">
                            <label class="form-label">Lugar exacto del evento</label>
                            <input type="text" name="lugar_exacto" class="form-control" value="<?= esc($inv['lugar_exacto'] ?? '') ?>" placeholder="Ej: Bodega principal, segundo piso...">
                        </div>

                        <!-- Descripcion detallada -->
                        <div class="mb-3">
                            <label class="form-label">Descripcion detallada del evento *</label>
                            <textarea name="descripcion_detallada" class="form-control" rows="4" placeholder="Describa con detalle lo ocurrido..." required><?= esc($inv['descripcion_detallada'] ?? '') ?></textarea>
                        </div>

                        <!-- Fecha investigacion -->
                        <div class="mb-0">
                            <label class="form-label">Fecha de la investigacion</label>
                            <input type="date" name="fecha_investigacion" class="form-control" value="<?= $inv['fecha_investigacion'] ?? date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. DATOS DEL TRABAJADOR -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secTrabajador">
                        2. <span id="titulo-trabajador"><?= ($inv['tipo_evento'] ?? '') === 'incidente' ? 'Trabajador Involucrado' : 'Trabajador Lesionado' ?></span>
                    </button>
                </h2>
                <div id="secTrabajador" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="nombre_trabajador" class="form-control" value="<?= esc($inv['nombre_trabajador'] ?? '') ?>" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Documento</label>
                                <input type="text" name="documento_trabajador" class="form-control" value="<?= esc($inv['documento_trabajador'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="cargo_trabajador" class="form-control" value="<?= esc($inv['cargo_trabajador'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Area</label>
                                <input type="text" name="area_trabajador" class="form-control" value="<?= esc($inv['area_trabajador'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Antiguedad</label>
                                <input type="text" name="antiguedad_trabajador" class="form-control" value="<?= esc($inv['antiguedad_trabajador'] ?? '') ?>" placeholder="Ej: 2 anios 3 meses">
                            </div>
                        </div>
                        <div class="row g-2 mb-0">
                            <div class="col-6">
                                <label class="form-label">Tipo de vinculacion</label>
                                <select name="tipo_vinculacion" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="directo" <?= ($inv['tipo_vinculacion'] ?? '') === 'directo' ? 'selected' : '' ?>>Directo</option>
                                    <option value="temporal" <?= ($inv['tipo_vinculacion'] ?? '') === 'temporal' ? 'selected' : '' ?>>Temporal</option>
                                    <option value="contratista" <?= ($inv['tipo_vinculacion'] ?? '') === 'contratista' ? 'selected' : '' ?>>Contratista</option>
                                    <option value="cooperativa" <?= ($inv['tipo_vinculacion'] ?? '') === 'cooperativa' ? 'selected' : '' ?>>Cooperativa</option>
                                    <option value="prestacion_servicios" <?= ($inv['tipo_vinculacion'] ?? '') === 'prestacion_servicios' ? 'selected' : '' ?>>Prestacion de servicios</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Jornada habitual</label>
                                <input type="text" name="jornada_habitual" class="form-control" value="<?= esc($inv['jornada_habitual'] ?? '') ?>" placeholder="Ej: 7am - 5pm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. DATOS DE LESION (solo accidente) -->
            <div class="accordion-item seccion-accidente" style="display:<?= ($inv['tipo_evento'] ?? '') === 'accidente' || empty($inv['tipo_evento']) ? 'block' : 'none' ?>;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secLesion">
                        3. Datos de la Lesion
                    </button>
                </h2>
                <div id="secLesion" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Parte del cuerpo lesionada</label>
                                <input type="text" name="parte_cuerpo_lesionada" class="form-control" value="<?= esc($inv['parte_cuerpo_lesionada'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Tipo de lesion</label>
                                <input type="text" name="tipo_lesion" class="form-control" value="<?= esc($inv['tipo_lesion'] ?? '') ?>" placeholder="Ej: Fractura, herida, contusion...">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Agente del accidente</label>
                                <input type="text" name="agente_accidente" class="form-control" value="<?= esc($inv['agente_accidente'] ?? '') ?>" placeholder="Ej: Maquinaria, escalera...">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Mecanismo del accidente</label>
                                <input type="text" name="mecanismo_accidente" class="form-control" value="<?= esc($inv['mecanismo_accidente'] ?? '') ?>" placeholder="Ej: Caida, golpe, atrapamiento...">
                            </div>
                        </div>
                        <div class="row g-2 mb-0">
                            <div class="col-6">
                                <label class="form-label">Dias de incapacidad</label>
                                <input type="number" name="dias_incapacidad" class="form-control" value="<?= esc($inv['dias_incapacidad'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label">No. FURAT</label>
                                <input type="text" name="numero_furat" class="form-control" value="<?= esc($inv['numero_furat'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. POTENCIAL DE DANO (solo incidente) -->
            <div class="accordion-item seccion-incidente" style="display:<?= ($inv['tipo_evento'] ?? '') === 'incidente' ? 'block' : 'none' ?>;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPotencial">
                        4. Potencial de Dano
                    </button>
                </h2>
                <div id="secPotencial" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <div class="mb-0">
                            <label class="form-label">Que pudo haber pasado si el incidente hubiera escalado?</label>
                            <textarea name="potencial_danio" class="form-control" rows="4" placeholder="Describa el potencial de dano: que lesion o dano pudo haberse generado..."><?= esc($inv['potencial_danio'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. ANALISIS CAUSAL -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secAnalisis">
                        5. Analisis Causal (Res. 1401/2007)
                    </button>
                </h2>
                <div id="secAnalisis" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <p class="text-muted mb-3" style="font-size:12px;"><strong>Causas Inmediatas</strong> - Circunstancias que se presentaron justo antes del evento</p>
                        <div class="mb-3">
                            <label class="form-label">Actos subestandar</label>
                            <textarea name="actos_substandar" class="form-control" rows="3" placeholder="Acciones u omisiones del trabajador que generaron el evento..."><?= esc($inv['actos_substandar'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Condiciones subestandar</label>
                            <textarea name="condiciones_substandar" class="form-control" rows="3" placeholder="Condiciones fisicas o del ambiente de trabajo que contribuyeron..."><?= esc($inv['condiciones_substandar'] ?? '') ?></textarea>
                        </div>

                        <hr>
                        <p class="text-muted mb-3" style="font-size:12px;"><strong>Causas Basicas</strong> - Razones de fondo por las cuales se presentaron las causas inmediatas</p>
                        <div class="mb-3">
                            <label class="form-label">Factores personales</label>
                            <textarea name="factores_personales" class="form-control" rows="3" placeholder="Falta de conocimiento, habilidad, motivacion, capacidad fisica..."><?= esc($inv['factores_personales'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Factores del trabajo</label>
                            <textarea name="factores_trabajo" class="form-control" rows="3" placeholder="Falta de normas, supervision inadecuada, herramientas defectuosas..."><?= esc($inv['factores_trabajo'] ?? '') ?></textarea>
                        </div>

                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Metodologia de analisis utilizada</label>
                            <select name="metodologia_analisis" class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="arbol_causas" <?= ($inv['metodologia_analisis'] ?? '') === 'arbol_causas' ? 'selected' : '' ?>>Arbol de causas</option>
                                <option value="espina_pescado" <?= ($inv['metodologia_analisis'] ?? '') === 'espina_pescado' ? 'selected' : '' ?>>Espina de pescado (Ishikawa)</option>
                                <option value="5_porques" <?= ($inv['metodologia_analisis'] ?? '') === '5_porques' ? 'selected' : '' ?>>5 Porques</option>
                                <option value="otra" <?= ($inv['metodologia_analisis'] ?? '') === 'otra' ? 'selected' : '' ?>>Otra</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Descripcion del analisis</label>
                            <textarea name="descripcion_analisis" class="form-control" rows="4" placeholder="Detalle el analisis causal realizado..."><?= esc($inv['descripcion_analisis'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. EQUIPO INVESTIGADOR -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secEquipo">
                        6. Equipo Investigador
                    </button>
                </h2>
                <div id="secEquipo" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <p class="text-muted mb-3" style="font-size:12px;">Jefe inmediato del trabajador</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="investigador_jefe_nombre" class="form-control" value="<?= esc($inv['investigador_jefe_nombre'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="investigador_jefe_cargo" class="form-control" value="<?= esc($inv['investigador_jefe_cargo'] ?? '') ?>">
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="font-size:12px;">Representante COPASST / Vigia SST</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="investigador_copasst_nombre" class="form-control" value="<?= esc($inv['investigador_copasst_nombre'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="investigador_copasst_cargo" class="form-control" value="<?= esc($inv['investigador_copasst_cargo'] ?? '') ?>">
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="font-size:12px;">Responsable del SG-SST</p>
                        <div class="row g-2 mb-0">
                            <div class="col-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="investigador_sst_nombre" class="form-control" value="<?= esc($inv['investigador_sst_nombre'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="investigador_sst_cargo" class="form-control" value="<?= esc($inv['investigador_sst_cargo'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. TESTIGOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secTestigos">
                        7. Testigos (<span id="countTestigos"><?= count($testigos) ?></span>)
                    </button>
                </h2>
                <div id="secTestigos" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <div id="testigosContainer">
                            <?php if (!empty($testigos)): ?>
                                <?php foreach ($testigos as $i => $t): ?>
                                <div class="card mb-3 testigo-row">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Testigo #<span class="testigo-num"><?= $i + 1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-testigo" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <input type="text" name="testigo_nombre[]" class="form-control form-control-sm" value="<?= esc($t['nombre'] ?? '') ?>" placeholder="Nombre">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="testigo_cargo[]" class="form-control form-control-sm" value="<?= esc($t['cargo'] ?? '') ?>" placeholder="Cargo">
                                            </div>
                                        </div>
                                        <textarea name="testigo_declaracion[]" class="form-control form-control-sm" rows="2" placeholder="Declaracion del testigo..."><?= esc($t['declaracion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddTestigo">
                            <i class="fas fa-plus"></i> Agregar testigo
                        </button>
                    </div>
                </div>
            </div>

            <!-- 8. EVIDENCIA FOTOGRAFICA -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secEvidencias">
                        8. Evidencia Fotografica (<span id="countEvidencias"><?= count($evidencias) ?></span>)
                    </button>
                </h2>
                <div id="secEvidencias" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <div id="evidenciasContainer">
                            <?php if (!empty($evidencias)): ?>
                                <?php foreach ($evidencias as $i => $ev): ?>
                                <div class="card mb-3 evidencia-row">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="evidencia_id[]" value="<?= $ev['id'] ?? '' ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Evidencia #<span class="evidencia-num"><?= $i + 1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-evidencia" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="evidencia_descripcion[]" class="form-control form-control-sm" value="<?= esc($ev['descripcion'] ?? '') ?>" placeholder="Descripcion de la evidencia">
                                        </div>
                                        <?php if (!empty($ev['imagen'])): ?>
                                            <div class="mb-1">
                                                <img src="<?= base_url($ev['imagen']) ?>" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer;" onclick="openPhoto(this.src)">
                                            </div>
                                        <?php endif; ?>
                                        <div class="photo-input-group">
                                            <input type="file" name="evidencia_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                                            </div>
                                            <div class="preview-img mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddEvidencia">
                            <i class="fas fa-plus"></i> Agregar evidencia
                        </button>
                    </div>
                </div>
            </div>

            <!-- 9. MEDIDAS CORRECTIVAS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secMedidas">
                        9. Medidas Correctivas (Art. 12 Res. 1401) (<span id="countMedidas"><?= count($medidas) ?></span>)
                    </button>
                </h2>
                <div id="secMedidas" class="accordion-collapse collapse" data-bs-parent="#accordionInvestigacion">
                    <div class="accordion-body">
                        <div id="medidasContainer">
                            <?php if (!empty($medidas)): ?>
                                <?php foreach ($medidas as $i => $m): ?>
                                <div class="card mb-3 medida-row">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Medida #<span class="medida-num"><?= $i + 1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-medida" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <select name="medida_tipo[]" class="form-select form-select-sm">
                                                    <option value="fuente" <?= ($m['tipo_medida'] ?? '') === 'fuente' ? 'selected' : '' ?>>En la fuente</option>
                                                    <option value="medio" <?= ($m['tipo_medida'] ?? '') === 'medio' ? 'selected' : '' ?>>En el medio</option>
                                                    <option value="trabajador" <?= ($m['tipo_medida'] ?? '') === 'trabajador' ? 'selected' : '' ?>>En el trabajador</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="medida_estado[]" class="form-select form-select-sm">
                                                    <option value="pendiente" <?= ($m['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                    <option value="en_proceso" <?= ($m['estado'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                                                    <option value="cumplida" <?= ($m['estado'] ?? '') === 'cumplida' ? 'selected' : '' ?>>Cumplida</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="medida_descripcion[]" class="form-control form-control-sm" rows="2" placeholder="Descripcion de la medida correctiva"><?= esc($m['descripcion'] ?? '') ?></textarea>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="text" name="medida_responsable[]" class="form-control form-control-sm" value="<?= esc($m['responsable'] ?? '') ?>" placeholder="Responsable">
                                            </div>
                                            <div class="col-6">
                                                <input type="date" name="medida_fecha[]" class="form-control form-control-sm" value="<?= $m['fecha_cumplimiento'] ?? '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddMedida">
                            <i class="fas fa-plus"></i> Agregar medida correctiva
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Botones de accion -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <?php if ($isEdit): ?>
            <a href="/inspecciones/investigacion-accidente/firma/<?= $inv['id'] ?>" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-signature"></i> Ir a Firmas
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Modal para ver foto ampliada -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-1 text-center">
                <img id="photoModalImg" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $inv['id_cliente'] ?? ($idCliente ?? '') ?>';

    // --- Select2 para clientes (solo en creacion) ---
    <?php if (!$isEdit): ?>
    $.ajax({
        url: '/inspecciones/api/clientes',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteId && c.id_cliente == clienteId) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });
    <?php endif; ?>

    // --- Show/hide secciones segun tipo_evento ---
    $('#tipo_evento').on('change', function() {
        var esAccidente = $(this).val() === 'accidente';
        $('.seccion-accidente').toggle(esAccidente);
        $('.seccion-incidente').toggle(!esAccidente && $(this).val() === 'incidente');
        $('#titulo-trabajador').text(esAccidente ? 'Trabajador Lesionado' : 'Trabajador Involucrado');
    });

    // --- TESTIGOS: Agregar/eliminar ---
    function updateTestigos() {
        const rows = document.querySelectorAll('.testigo-row');
        document.getElementById('countTestigos').textContent = rows.length;
        rows.forEach((row, i) => { row.querySelector('.testigo-num').textContent = i + 1; });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-testigo')) {
            e.target.closest('.testigo-row').remove();
            updateTestigos();
        }
    });

    document.getElementById('btnAddTestigo').addEventListener('click', function() {
        const num = document.querySelectorAll('.testigo-row').length + 1;
        const html = `
            <div class="card mb-3 testigo-row">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Testigo #<span class="testigo-num">${num}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-testigo" style="min-height:32px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="text" name="testigo_nombre[]" class="form-control form-control-sm" placeholder="Nombre">
                        </div>
                        <div class="col-6">
                            <input type="text" name="testigo_cargo[]" class="form-control form-control-sm" placeholder="Cargo">
                        </div>
                    </div>
                    <textarea name="testigo_declaracion[]" class="form-control form-control-sm" rows="2" placeholder="Declaracion del testigo..."></textarea>
                </div>
            </div>`;
        document.getElementById('testigosContainer').insertAdjacentHTML('beforeend', html);
        updateTestigos();
    });

    // --- EVIDENCIAS: Agregar/eliminar ---
    function updateEvidencias() {
        const rows = document.querySelectorAll('.evidencia-row');
        document.getElementById('countEvidencias').textContent = rows.length;
        rows.forEach((row, i) => { row.querySelector('.evidencia-num').textContent = i + 1; });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-evidencia')) {
            e.target.closest('.evidencia-row').remove();
            updateEvidencias();
        }
    });

    document.getElementById('btnAddEvidencia').addEventListener('click', function() {
        const num = document.querySelectorAll('.evidencia-row').length + 1;
        const html = `
            <div class="card mb-3 evidencia-row">
                <div class="card-body p-2">
                    <input type="hidden" name="evidencia_id[]" value="">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Evidencia #<span class="evidencia-num">${num}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-evidencia" style="min-height:32px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="evidencia_descripcion[]" class="form-control form-control-sm" placeholder="Descripcion de la evidencia">
                    </div>
                    <div class="photo-input-group">
                        <input type="file" name="evidencia_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
            </div>`;
        document.getElementById('evidenciasContainer').insertAdjacentHTML('beforeend', html);
        updateEvidencias();
    });

    // --- MEDIDAS CORRECTIVAS: Agregar/eliminar ---
    function updateMedidas() {
        const rows = document.querySelectorAll('.medida-row');
        document.getElementById('countMedidas').textContent = rows.length;
        rows.forEach((row, i) => { row.querySelector('.medida-num').textContent = i + 1; });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-medida')) {
            e.target.closest('.medida-row').remove();
            updateMedidas();
        }
    });

    document.getElementById('btnAddMedida').addEventListener('click', function() {
        const num = document.querySelectorAll('.medida-row').length + 1;
        const html = `
            <div class="card mb-3 medida-row">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Medida #<span class="medida-num">${num}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-medida" style="min-height:32px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <select name="medida_tipo[]" class="form-select form-select-sm">
                                <option value="fuente">En la fuente</option>
                                <option value="medio">En el medio</option>
                                <option value="trabajador">En el trabajador</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="medida_estado[]" class="form-select form-select-sm">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="cumplida">Cumplida</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea name="medida_descripcion[]" class="form-control form-control-sm" rows="2" placeholder="Descripcion de la medida correctiva"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="text" name="medida_responsable[]" class="form-control form-control-sm" placeholder="Responsable">
                        </div>
                        <div class="col-6">
                            <input type="date" name="medida_fecha[]" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>`;
        document.getElementById('medidasContainer').insertAdjacentHTML('beforeend', html);
        updateMedidas();
    });

    // --- Botones Galeria ---
    document.addEventListener('click', function(e) {
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!galleryBtn) return;
        const group = galleryBtn.closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        input.removeAttribute('capture');
        input.click();
    });

    // --- Preview de fotos ---
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const input = e.target;
        const group = input.closest('.photo-input-group');
        const previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;
        previewDiv.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });
});
</script>
