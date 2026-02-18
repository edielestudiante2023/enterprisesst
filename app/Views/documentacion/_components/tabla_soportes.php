<?php
/**
 * Componente: Tabla de Soportes Adjuntados (Estilo Moderno)
 * Estilo visual homologo a tabla_documentos_sst.php (DataTables)
 * pero sin DataTables JS (tablas pequenas de 3-10 filas)
 *
 * Variables requeridas:
 *   $soportes       array  - Datos de soportes (tbl_documentos_sst rows)
 *   $titulo         string - Titulo del header (ej: "Soportes de Capacitacion COPASST")
 *
 * Variables opcionales:
 *   $subtitulo      string - Subtitulo bajo el titulo (default: "Documentos adjuntos")
 *   $icono          string - Clase Bootstrap Icon (default: "bi-paperclip")
 *   $colorHeader    string - Variante de color: "default"|"info"|"success"|"warning"|"primary"|"secondary" (default: "default")
 *   $emptyIcon      string - Icono para estado vacio (default: "bi-inbox")
 *   $emptyMessage   string - Mensaje estado vacio (default: "No hay soportes adjuntados aun.")
 *   $emptyHint      string - Hint bajo mensaje vacio (default: 'Use el boton "Adjuntar Soporte" para agregar evidencias.')
 *   $codigoDefault       string - Codigo por defecto si el soporte no tiene (default: "SOP")
 *   $mostrarAnio         bool   - Mostrar columna Ano (default: true)
 *   $columnaCodigoLabel  string - Label de la primera columna (default: "Codigo"). Usar "Origen / Entidad" para docs externos
 */

// Defaults
$soportes      = $soportes ?? [];
$titulo        = $titulo ?? 'Soportes Adjuntados';
$subtitulo     = $subtitulo ?? 'Documentos adjuntos';
$icono         = $icono ?? 'bi-paperclip';
$colorHeader   = $colorHeader ?? 'default';
$emptyIcon     = $emptyIcon ?? 'bi-inbox';
$emptyMessage  = $emptyMessage ?? 'No hay soportes adjuntados aun.';
$emptyHint     = $emptyHint ?? 'Use el boton "Adjuntar Soporte" para agregar evidencias.';
$codigoDefault = $codigoDefault ?? 'SOP';
$mostrarAnio   = $mostrarAnio ?? true;
$columnaCodigoLabel = $columnaCodigoLabel ?? 'Codigo';

// Mapeo de color a clase CSS
$headerClass = match($colorHeader) {
    'info'      => 'header-soportes-info',
    'success'   => 'header-soportes-success',
    'warning'   => 'header-soportes-warning',
    'primary'   => 'header-soportes-primary',
    'secondary' => 'header-soportes-secondary',
    default     => 'header-soportes'
};
?>

<!-- Estilos (se incluyen una sola vez por pagina) -->
<?php if (!defined('TABLA_SOPORTES_STYLES_LOADED')): ?>
    <?php define('TABLA_SOPORTES_STYLES_LOADED', true); ?>
    <?= view('documentacion/_components/tabla_soportes_styles') ?>
<?php endif; ?>

<!-- Tabla de Soportes con Estilo Moderno -->
<div class="card border-0 shadow-lg mb-4 tabla-soportes-card">
    <!-- Header con gradiente -->
    <div class="card-header <?= $headerClass ?>">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="bi <?= esc($icono) ?>"></i>
                </div>
                <div>
                    <span class="header-title"><?= esc($titulo) ?></span>
                    <small class="d-block header-subtitle"><?= esc($subtitulo) ?></small>
                </div>
            </h6>
            <div class="header-stats">
                <span class="stat-badge">
                    <i class="bi bi-files me-1"></i>
                    <span><?= count($soportes) ?></span> soporte<?= count($soportes) !== 1 ? 's' : '' ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if (!empty($soportes)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 tabla-soportes-moderna" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:120px"><?= esc($columnaCodigoLabel) ?></th>
                            <th>Descripcion</th>
                            <?php if ($mostrarAnio): ?>
                            <th style="width:80px">Ano</th>
                            <?php endif; ?>
                            <th style="width:100px">Fecha</th>
                            <th style="width:100px">Tipo</th>
                            <th style="width:140px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soportes as $s):
                            $esEnlace = !empty($s['url_externa']);
                            $url = $esEnlace ? $s['url_externa'] : ($s['archivo_pdf'] ?? '#');
                        ?>
                        <tr>
                            <td>
                                <span class="codigo-badge"><?= esc($s['codigo'] ?? $codigoDefault) ?></span>
                            </td>
                            <td>
                                <div class="nombre-soporte"><?= esc($s['titulo']) ?></div>
                                <?php if (!empty($s['observaciones'])): ?>
                                    <div class="obs-soporte"><?= esc($s['observaciones']) ?></div>
                                <?php endif; ?>
                            </td>
                            <?php if ($mostrarAnio): ?>
                            <td>
                                <span class="anio-badge"><?= esc($s['anio'] ?? date('Y')) ?></span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <span class="fecha-cell"><?= date('d/m/Y', strtotime($s['created_at'] ?? $s['fecha_aprobacion'] ?? 'now')) ?></span>
                            </td>
                            <td>
                                <?php if ($esEnlace): ?>
                                    <span class="tipo-badge-enlace">
                                        <i class="bi bi-link-45deg me-1"></i>Enlace
                                    </span>
                                <?php else: ?>
                                    <span class="tipo-badge-archivo">
                                        <i class="bi bi-file-earmark me-1"></i>Archivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="acciones-cell">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= esc($url) ?>" class="btn btn-outline-primary" target="_blank" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($esEnlace): ?>
                                    <a href="<?= esc($url) ?>" class="btn btn-outline-info" target="_blank" title="Abrir enlace externo">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= esc($url) ?>" class="btn btn-outline-danger" download title="Descargar">
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
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi <?= esc($emptyIcon) ?>"></i>
                </div>
                <h5><?= esc($emptyMessage) ?></h5>
                <p><?= esc($emptyHint) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
