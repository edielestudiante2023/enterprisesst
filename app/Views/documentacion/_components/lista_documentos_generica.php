<?php
/**
 * Componente: Lista de Documentos para Carpetas Genéricas
 * Variables: $documentos, $cliente, $carpeta
 */
?>
<div class="col-12">
    <h6 class="text-muted mb-3"><i class="bi bi-file-earmark-text me-2"></i>Documentos</h6>
    <?php if (empty($documentos)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No hay documentos en esta carpeta</p>
                <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) ?>"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Crear documento
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Código</th>
                            <th>Nombre</th>
                            <th style="width: 100px;">Versión</th>
                            <th style="width: 130px;">Estado IA</th>
                            <th style="width: 130px;">Estado Doc</th>
                            <th style="width: 100px;">Actualizado</th>
                            <th style="width: 120px;"></th>
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

                                $estadoDocClass = match($doc['estado'] ?? 'borrador') {
                                    'borrador' => 'bg-info',
                                    'en_revision' => 'bg-warning text-dark',
                                    'pendiente_firma' => 'bg-purple text-white',
                                    'aprobado' => 'bg-success',
                                    'obsoleto' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                            ?>
                            <tr class="doc-row">
                                <td>
                                    <div class="doc-estado-indicator <?= $estadoIA ?>"></div>
                                    <code><?= esc($doc['codigo']) ?></code>
                                </td>
                                <td>
                                    <a href="/documentacion/ver/<?= $doc['id_documento'] ?>" class="text-decoration-none fw-medium">
                                        <?= esc($doc['nombre']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">v<?= $doc['version_actual'] ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $estadoIAClass ?>">
                                        <i class="bi <?= $estadoIAIcon ?> me-1"></i><?= $estadoIAText ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge estado-badge <?= $estadoDocClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $doc['estado'] ?? 'borrador')) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($doc['updated_at'])) ?>
                                    </small>
                                </td>
                                <td>
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
        </div>

        <!-- Leyenda de estados IA -->
        <div class="mt-3 p-3 bg-white rounded shadow-sm">
            <small class="text-muted me-3"><strong>Estados IA:</strong></small>
            <span class="badge estado-ia-pendiente me-2"><i class="bi bi-clock-fill me-1"></i>Pendiente</span>
            <small class="text-muted me-3">Sin contenido generado</small>
            <span class="badge estado-ia-creado me-2"><i class="bi bi-pencil-fill me-1"></i>Creado</span>
            <small class="text-muted me-3">Contenido generado, pendiente aprobación</small>
            <span class="badge estado-ia-aprobado me-2"><i class="bi bi-check-circle-fill me-1"></i>Aprobado</span>
            <small class="text-muted">Todas las secciones aprobadas</small>
        </div>
    <?php endif; ?>
</div>
