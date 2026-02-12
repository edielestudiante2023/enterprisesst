<?php
/**
 * Vista de Tipo: 4.2.3 Elaboración de procedimientos, instructivos, fichas, protocolos
 * Multi-Programa con dropdown de 50+ programas organizados por categoria de riesgo
 *
 * PROGRAMAS IMPLEMENTADOS:
 * - PVE Riesgo Biomecánico (tipo_documento: pve_riesgo_biomecanico)
 * - PVE Riesgo Psicosocial (tipo_documento: pve_riesgo_psicosocial)
 *
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $soportesAdicionales, $programasFasesInfo
 */

$anioActual = date('Y');

// Tipos de documento existentes en la tabla
$docsExistentesTipos = [];
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        $docsExistentesTipos[$d['tipo_documento']] = true;
    }
}

// Programas implementados con su configuracion
$programasImplementados = [
    'pve_riesgo_biomecanico' => [
        'nombre' => 'PVE de Riesgo Biomecánico',
        'icono' => 'bi-body-text',
        'color' => 'warning',
        'url_generar' => base_url('documentos/generar/pve_riesgo_biomecanico/' . $cliente['id_cliente']),
        'url_parte1' => base_url('generador-ia/' . $cliente['id_cliente'] . '/pve-riesgo-biomecanico'),
        'tipoCarpetaFases' => 'pve_riesgo_biomecanico',
        'grupo' => 'biomecanico'
    ],
    'pve_riesgo_psicosocial' => [
        'nombre' => 'PVE de Riesgo Psicosocial',
        'icono' => 'bi-brain',
        'color' => 'purple',
        'url_generar' => base_url('documentos/generar/pve_riesgo_psicosocial/' . $cliente['id_cliente']),
        'url_parte1' => base_url('generador-ia/' . $cliente['id_cliente'] . '/pve-riesgo-psicosocial'),
        'tipoCarpetaFases' => 'pve_riesgo_psicosocial',
        'grupo' => 'psicosocial'
    ],
];

// Catálogo de 50+ programas agrupados por categoría de riesgo
$catalogoProgramas = [
    'Riesgo Biomecánico' => [
        ['key' => 'pve_riesgo_biomecanico', 'nombre' => 'PVE de Riesgo Biomecánico / Osteomuscular', 'implementado' => true],
        ['key' => null, 'nombre' => 'Programa de Pausas Activas y Gimnasia Laboral', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Higiene Postural', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Manejo Manual de Cargas', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Ergonomía en Oficinas (VDT)', 'implementado' => false],
    ],
    'Riesgo Psicosocial' => [
        ['key' => 'pve_riesgo_psicosocial', 'nombre' => 'PVE de Riesgo Psicosocial', 'implementado' => true],
        ['key' => null, 'nombre' => 'Programa de Prevención del Estrés Laboral', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Prevención del Acoso Laboral', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Bienestar y Clima Organizacional', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Equilibrio Vida-Trabajo', 'implementado' => false],
    ],
    'Riesgo Químico' => [
        ['key' => null, 'nombre' => 'PVE de Riesgo Químico', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Manejo Seguro de Sustancias Químicas', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión de Hojas de Seguridad (SDS/MSDS)', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Protección Respiratoria', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Vigilancia de Agentes Cancerígenos', 'implementado' => false],
    ],
    'Riesgo Físico' => [
        ['key' => null, 'nombre' => 'PVE de Riesgo Auditivo (Conservación Auditiva)', 'implementado' => false],
        ['key' => null, 'nombre' => 'PVE de Riesgo Visual', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Control de Ruido', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Control de Iluminación', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Control de Temperaturas Extremas', 'implementado' => false],
    ],
    'Riesgo Biológico' => [
        ['key' => null, 'nombre' => 'PVE de Riesgo Biológico', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Bioseguridad', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión de Residuos Biológicos', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Vacunación Ocupacional', 'implementado' => false],
    ],
    'Riesgo de Seguridad' => [
        ['key' => null, 'nombre' => 'Programa de Trabajo Seguro en Alturas', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Trabajo en Espacios Confinados', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Trabajo en Caliente', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Bloqueo y Etiquetado (LOTO)', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Seguridad Vial', 'implementado' => false],
    ],
    'Riesgo Eléctrico' => [
        ['key' => null, 'nombre' => 'Programa de Riesgo Eléctrico (RETIE)', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Mantenimiento Eléctrico Preventivo', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Seguridad en Instalaciones Eléctricas', 'implementado' => false],
    ],
    'Riesgo Locativo' => [
        ['key' => null, 'nombre' => 'Programa de Orden y Aseo (5S)', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Señalización y Demarcación', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Inspecciones de Seguridad', 'implementado' => false],
    ],
    'Riesgo Tecnológico' => [
        ['key' => null, 'nombre' => 'Programa de Prevención de Incendios', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión de Materiales Peligrosos', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Seguridad en Maquinaria y Equipos', 'implementado' => false],
    ],
    'Riesgo Público' => [
        ['key' => null, 'nombre' => 'Programa de Prevención de Riesgo Público', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Seguridad en Desplazamientos', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión de Riesgo de Atraco/Robo', 'implementado' => false],
    ],
    'Riesgo Natural' => [
        ['key' => null, 'nombre' => 'Programa de Preparación ante Sismos', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Prevención ante Inundaciones', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión ante Fenómenos Naturales', 'implementado' => false],
    ],
    'Programas Transversales' => [
        ['key' => null, 'nombre' => 'Programa de Elementos de Protección Personal (EPP)', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Reportes de Actos y Condiciones Inseguras', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión de Contratistas', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Vigilancia de la Salud', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Rehabilitación y Retorno al Trabajo', 'implementado' => false],
        ['key' => null, 'nombre' => 'Programa de Gestión de Cambio', 'implementado' => false],
    ],
];

// Contar totales
$totalProgramas = 0;
$totalImplementados = 0;
foreach ($catalogoProgramas as $grupo => $programas) {
    $totalProgramas += count($programas);
    foreach ($programas as $p) {
        if ($p['implementado']) $totalImplementados++;
    }
}
?>

<!-- Card de Carpeta -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-shield-check text-primary me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <p class="text-muted mb-0 mt-2">
                    Elaboracion de procedimientos, instructivos, fichas, protocolos de seguridad y
                    programas de vigilancia epidemiologica (PVE) para
                    <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>.
                    <strong>Seleccione un programa del catalogo para comenzar.</strong>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-primary fs-6"><?= $totalImplementados ?> / <?= $totalProgramas ?></span>
                <small class="d-block text-muted mt-1">Programas disponibles con IA</small>
            </div>
        </div>
    </div>
</div>

<!-- Catálogo de Programas (Accordion) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-collection text-primary me-2"></i>Catalogo de Programas por Categoria de Riesgo
        </h5>
        <small class="text-muted"><?= $totalProgramas ?> programas organizados en <?= count($catalogoProgramas) ?> categorias</small>
    </div>
    <div class="card-body p-0">
        <div class="accordion" id="accordionProgramas">
            <?php $idx = 0; foreach ($catalogoProgramas as $grupo => $programas): $idx++; ?>
                <?php
                $implementadosGrupo = count(array_filter($programas, fn($p) => $p['implementado']));
                $collapseId = 'collapse' . preg_replace('/[^a-zA-Z0-9]/', '', $grupo);
                // Iconos por grupo
                $iconosGrupo = [
                    'Riesgo Biomecánico' => 'bi-body-text',
                    'Riesgo Psicosocial' => 'bi-brain',
                    'Riesgo Químico' => 'bi-droplet-half',
                    'Riesgo Físico' => 'bi-soundwave',
                    'Riesgo Biológico' => 'bi-bug',
                    'Riesgo de Seguridad' => 'bi-shield-exclamation',
                    'Riesgo Eléctrico' => 'bi-lightning',
                    'Riesgo Locativo' => 'bi-building',
                    'Riesgo Tecnológico' => 'bi-gear',
                    'Riesgo Público' => 'bi-people',
                    'Riesgo Natural' => 'bi-tsunami',
                    'Programas Transversales' => 'bi-arrows-fullscreen',
                ];
                $iconoGrupo = $iconosGrupo[$grupo] ?? 'bi-folder';
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $implementadosGrupo > 0 ? '' : 'collapsed' ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                            <i class="<?= $iconoGrupo ?> me-2"></i>
                            <strong><?= esc($grupo) ?></strong>
                            <span class="badge bg-secondary ms-2"><?= count($programas) ?></span>
                            <?php if ($implementadosGrupo > 0): ?>
                                <span class="badge bg-success ms-1"><?= $implementadosGrupo ?> con IA</span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $implementadosGrupo > 0 ? 'show' : '' ?>"
                         data-bs-parent="#accordionProgramas">
                        <div class="accordion-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($programas as $prog): ?>
                                    <?php if ($prog['implementado'] && isset($programasImplementados[$prog['key']])): ?>
                                        <?php
                                        $config = $programasImplementados[$prog['key']];
                                        $fasesPrograma = $programasFasesInfo[$prog['key']]['fases'] ?? null;
                                        $puedeGenerar = $fasesPrograma && ($fasesPrograma['puede_generar_documento'] ?? false);
                                        $hayDoc = isset($docsExistentesTipos[$prog['key']]);
                                        ?>
                                        <li class="list-group-item border-start border-3 border-success p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="<?= $config['icono'] ?> text-<?= $config['color'] ?> me-1"></i>
                                                        <?= esc($prog['nombre']) ?>
                                                        <span class="badge bg-success ms-1">Disponible</span>
                                                    </h6>
                                                    <!-- Mini panel de fases -->
                                                    <?php if ($fasesPrograma && !empty($fasesPrograma['fases'])): ?>
                                                        <div class="d-flex gap-2 mt-1">
                                                            <?php foreach ($fasesPrograma['fases'] as $faseKey => $fase): ?>
                                                                <?php
                                                                $estadoFase = $fase['estado'] ?? 'incompleta';
                                                                $iconoFase = match($estadoFase) {
                                                                    'completa' => 'bi-check-circle-fill text-success',
                                                                    'en_progreso' => 'bi-arrow-repeat text-warning',
                                                                    default => 'bi-circle text-muted'
                                                                };
                                                                ?>
                                                                <small class="text-muted">
                                                                    <i class="<?= $iconoFase ?> me-1"></i><?= esc($fase['nombre'] ?? $faseKey) ?>
                                                                </small>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-nowrap">
                                                    <?php if (!$puedeGenerar): ?>
                                                        <a href="<?= $config['url_parte1'] ?>" class="btn btn-sm btn-outline-primary" title="Ir a Parte 1: Actividades">
                                                            <i class="bi bi-play-circle me-1"></i>Comenzar
                                                        </a>
                                                    <?php elseif (!$hayDoc): ?>
                                                        <a href="<?= $config['url_generar'] ?>" class="btn btn-sm btn-success" title="Generar documento con IA">
                                                            <i class="bi bi-magic me-1"></i>Crear con IA
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= $config['url_generar'] ?>" class="btn btn-sm btn-outline-success" title="Generar nueva version">
                                                            <i class="bi bi-arrow-repeat me-1"></i>Nueva version
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php else: ?>
                                        <li class="list-group-item text-muted py-2 px-3">
                                            <i class="bi bi-circle me-1"></i>
                                            <?= esc($prog['nombre']) ?>
                                            <span class="badge bg-light text-muted ms-1">Proximamente</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Panel de Fases por Programa Implementado -->
<?php foreach ($programasImplementados as $tipoProg => $config): ?>
    <?php if (!empty($programasFasesInfo[$tipoProg])): ?>
        <?php $fasesPrograma = $programasFasesInfo[$tipoProg]; ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="<?= $config['icono'] ?> text-<?= $config['color'] ?> me-2"></i>
                    Fases: <?= esc($fasesPrograma['nombre']) ?>
                </h6>
            </div>
            <div class="card-body">
                <?= view('documentacion/_components/panel_fases', [
                    'fasesInfo' => $fasesPrograma['fases'] ?? null,
                    'tipoCarpetaFases' => $config['tipoCarpetaFases'],
                    'cliente' => $cliente,
                    'carpeta' => $carpeta,
                    'documentoExistente' => null
                ]) ?>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'procedimientos_seguridad',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<?php if (!empty($subcarpetas)): ?>
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas
    ]) ?>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- SECCION ADICIONAL: SOPORTES PVE                              -->
<!-- ============================================================ -->

<hr class="my-5">

<!-- Card de Soportes Adicionales -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">
                    <i class="bi bi-file-earmark-plus text-primary me-2"></i>
                    Soportes Adicionales PVE
                </h5>
                <p class="text-muted mb-0 small">
                    Adjunte evidencias complementarias: encuestas de sintomatologia, evaluaciones ergonomicas,
                    resultados de bateria psicosocial, informes de pausas activas, actas de talleres u otros soportes.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarPVE">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados (GRIS) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0">
            <i class="bi bi-paperclip me-2"></i>Soportes Adjuntados
        </h6>
    </div>
    <div class="card-body p-0">
        <?php $soportes = $soportesAdicionales ?? []; ?>
        <?php if (!empty($soportes)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Codigo</th>
                            <th>Descripcion</th>
                            <th style="width: 80px;">Ano</th>
                            <th style="width: 100px;">Fecha</th>
                            <th style="width: 90px;">Tipo</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soportes as $soporte): ?>
                            <?php
                            $esEnlace = !empty($soporte['url_externa']);
                            $urlArchivo = $esEnlace ? $soporte['url_externa'] : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-PVE') ?></code></td>
                                <td>
                                    <strong><?= esc($soporte['titulo']) ?></strong>
                                    <?php if (!empty($soporte['observaciones'])): ?>
                                        <br><small class="text-muted"><?= esc($soporte['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= esc($soporte['anio']) ?></span></td>
                                <td><small><?= date('d/m/Y', strtotime($soporte['created_at'] ?? 'now')) ?></small></td>
                                <td>
                                    <?php if ($esEnlace): ?>
                                        <span class="badge bg-info"><i class="bi bi-link-45deg"></i> Enlace</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark"><i class="bi bi-file-earmark"></i> Archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-primary" target="_blank" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (!$esEnlace): ?>
                                            <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-danger" download title="Descargar">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-center mb-0">
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mb-0 mt-2">No hay soportes adjuntados aún.</p>
                <small class="text-muted">Use el botón "Adjuntar Soporte" para agregar evidencias.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Adjuntar Soporte PVE -->
<div class="modal fade" id="modalAdjuntarPVE" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte PVE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarPVE" action="<?= base_url('documentos-sst/adjuntar-soporte-pve-biomecanico') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <input type="hidden" name="codigo_soporte" value="SOP-PVE">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Programa asociado</label>
                        <select class="form-select" name="programa_asociado" id="programaAsociadoPVE">
                            <option value="pve_biomecanico">PVE Riesgo Biomecánico</option>
                            <option value="pve_psicosocial">PVE Riesgo Psicosocial</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_pve" id="tipoCargaArchivoPVE" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoPVE">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_pve" id="tipoCargaEnlacePVE" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlacePVE">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="campoArchivoPVE">
                        <label for="archivo_soporte_pve" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_pve" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <div class="mb-3 d-none" id="campoEnlacePVE">
                        <label for="url_externa_pve" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_pve" name="url_externa" placeholder="https://drive.google.com/...">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_pve" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_pve" name="descripcion" required placeholder="Ej: Encuesta Cuestionario Nordico, Resultados Bateria Psicosocial...">
                    </div>

                    <div class="mb-3">
                        <label for="anio_pve" class="form-label">Ano</label>
                        <select class="form-select" id="anio_pve" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_pve" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_pve" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarPVE">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle archivo/enlace
document.querySelectorAll('input[name="tipo_carga_pve"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoPVE').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlacePVE').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_pve').required = isArchivo;
        document.getElementById('url_externa_pve').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_pve').value = '';
        } else {
            document.getElementById('url_externa_pve').value = '';
        }
    });
});

// Cambiar action del form segun programa seleccionado
document.getElementById('programaAsociadoPVE')?.addEventListener('change', function() {
    const form = document.getElementById('formAdjuntarPVE');
    if (this.value === 'pve_psicosocial') {
        form.action = '<?= base_url('documentos-sst/adjuntar-soporte-pve-psicosocial') ?>';
    } else {
        form.action = '<?= base_url('documentos-sst/adjuntar-soporte-pve-biomecanico') ?>';
    }
});

// Spinner al enviar
document.getElementById('formAdjuntarPVE')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarPVE');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
