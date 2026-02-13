<?php
/**
 * Componente: Lista de Documentos Genericos (Estilo Moderno)
 * Variables requeridas: $documentos, $cliente, $carpeta, $tipoCarpetaFases (opcional)
 * Solo se muestra para carpetas sin tipo especial (tipoCarpetaFases = null)
 */
if (isset($tipoCarpetaFases)) {
    return;
}
?>

<!-- Estilos soportes (reutilizamos los tokens visuales) -->
<?php if (!defined('TABLA_SOPORTES_STYLES_LOADED')): ?>
    <?php define('TABLA_SOPORTES_STYLES_LOADED', true); ?>
    <?= view('documentacion/_components/tabla_soportes_styles') ?>
<?php endif; ?>

<div class="col-12">
    <div class="card border-0 shadow-lg mb-4 tabla-soportes-card">
        <!-- Header con gradiente -->
        <div class="card-header header-soportes-secondary">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <span class="header-title">Documentos</span>
                        <small class="d-block header-subtitle">Archivos de la carpeta</small>
                    </div>
                </h6>
                <?php if (!empty($documentos)): ?>
                <div class="header-stats">
                    <span class="stat-badge">
                        <i class="bi bi-files me-1"></i>
                        <span><?= count($documentos) ?></span> documento<?= count($documentos) !== 1 ? 's' : '' ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-file-earmark-x"></i>
                    </div>
                    <h5>No hay documentos en esta carpeta</h5>
                    <p>
                        <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) ?>"
                           class="btn btn-outline-primary btn-sm mt-2">
                            <i class="bi bi-plus-lg me-1"></i>Crear documento
                        </a>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 tabla-soportes-moderna" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Codigo</th>
                                <th>Nombre</th>
                                <th style="width: 100px;">Version</th>
                                <th style="width: 130px;">Estado IA</th>
                                <th style="width: 130px;">Estado Doc</th>
                                <th style="width: 100px;">Actualizado</th>
                                <th style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $doc): ?>
                                <?php
                                    $estadoIA = $doc['estado_ia'] ?? 'pendiente';
                                    $estadoIAClass = match($estadoIA) {
                                        'aprobado' => 'estado-ia-aprobado',
                                        'creado' => 'estado-ia-creado',
                                        default => 'estado-ia-pendiente'
                                    };
                                    $estadoIAIcon = match($estadoIA) {
                                        'aprobado' => 'bi-check-circle-fill',
                                        'creado' => 'bi-pencil-fill',
                                        default => 'bi-clock-fill'
                                    };
                                    $estadoIAText = match($estadoIA) {
                                        'aprobado' => 'Aprobado',
                                        'creado' => 'Creado',
                                        default => 'Pendiente'
                                    };

                                    $estadoDoc = $doc['estado'] ?? 'borrador';
                                    $estadoDocBadge = match($estadoDoc) {
                                        'borrador' => 'estado-borrador',
                                        'en_revision' => 'estado-pendiente-firma',
                                        'pendiente_firma' => 'estado-pendiente-firma',
                                        'aprobado' => 'estado-aprobado',
                                        'obsoleto' => 'estado-generado',
                                        default => 'estado-default'
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <span class="codigo-badge"><?= esc($doc['codigo']) ?></span>
                                    </td>
                                    <td>
                                        <a href="/documentacion/ver/<?= $doc['id_documento'] ?>" class="text-decoration-none nombre-soporte">
                                            <?= esc($doc['nombre']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="anio-badge">v<?= $doc['version_actual'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $estadoIAClass ?>">
                                            <i class="bi <?= $estadoIAIcon ?> me-1"></i><?= $estadoIAText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="estado-badge-moderno <?= $estadoDocBadge ?>">
                                            <i class="bi bi-circle-fill me-1" style="font-size:0.5rem"></i>
                                            <?= ucfirst(str_replace('_', ' ', $estadoDoc)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fecha-cell">
                                            <?= date('d/m/Y', strtotime($doc['updated_at'])) ?>
                                        </span>
                                    </td>
                                    <td class="acciones-cell">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/documentacion/ver/<?= $doc['id_documento'] ?>"
                                               class="btn btn-outline-primary" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/documentacion/editar/<?= $doc['id_documento'] ?>"
                                               class="btn btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/exportar/pdf/<?= $doc['id_documento'] ?>"
                                               class="btn btn-outline-danger" title="PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Leyenda de estados IA -->
                <div class="mt-0 p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <small class="text-muted me-3"><strong>Estados IA:</strong></small>
                    <span class="badge estado-ia-pendiente me-2"><i class="bi bi-clock-fill me-1"></i>Pendiente</span>
                    <small class="text-muted me-3">Sin contenido generado</small>
                    <span class="badge estado-ia-creado me-2"><i class="bi bi-pencil-fill me-1"></i>Creado</span>
                    <small class="text-muted me-3">Contenido generado, pendiente aprobacion</small>
                    <span class="badge estado-ia-aprobado me-2"><i class="bi bi-check-circle-fill me-1"></i>Aprobado</span>
                    <small class="text-muted">Todas las secciones aprobadas</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
