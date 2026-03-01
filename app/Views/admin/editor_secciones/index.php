<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editor de Secciones<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);">
                <div class="card-body text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-pencil-square me-2"></i>Editor de Secciones de Documentos
                            </h4>
                            <small class="opacity-75">Edición directa de secciones sin crear nueva versión · tbl_documentos_sst + tbl_doc_versiones_sst</small>
                        </div>
                        <a href="<?= site_url('consultant/dashboard') ?>" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtro por cliente -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <label class="form-label mb-0 fw-bold"><i class="bi bi-funnel me-1"></i>Filtrar:</label>
                </div>
                <div class="col-md-4">
                    <select id="filtroCliente" class="form-select form-select-sm">
                        <option value="">Todos los clientes</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= esc($c['nombre_cliente']) ?>"><?= esc($c['nombre_cliente']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaDocumentos" class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="background:#1c2437;color:#fff;">ID</th>
                            <th style="background:#1c2437;color:#fff;">Cliente</th>
                            <th style="background:#1c2437;color:#fff;">Tipo Documento</th>
                            <th style="background:#1c2437;color:#fff;">Código</th>
                            <th style="background:#1c2437;color:#fff;">Título</th>
                            <th style="background:#1c2437;color:#fff;">Versión</th>
                            <th style="background:#1c2437;color:#fff;">Estado</th>
                            <th style="background:#1c2437;color:#fff;">Actualizado</th>
                            <th style="background:#1c2437;color:#fff;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td><?= esc($doc['id_documento']) ?></td>
                            <td><?= esc($doc['nombre_cliente'] ?? 'N/A') ?></td>
                            <td><code><?= esc($doc['tipo_documento']) ?></code></td>
                            <td><span class="badge bg-dark"><?= esc($doc['codigo'] ?? '-') ?></span></td>
                            <td><?= esc(mb_substr($doc['titulo'] ?? '', 0, 50)) ?></td>
                            <td>
                                <?php if ($doc['version_texto']): ?>
                                    <span class="badge bg-primary">v<?= esc($doc['version_texto']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">v<?= esc($doc['version'] ?? '1') ?>.0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = match($doc['estado']) {
                                    'aprobado' => 'bg-success',
                                    'firmado' => 'bg-info',
                                    'pendiente_firma' => 'bg-warning text-dark',
                                    'borrador' => 'bg-secondary',
                                    default => 'bg-dark',
                                };
                                ?>
                                <span class="badge <?= $estadoBadge ?>"><?= esc($doc['estado']) ?></span>
                            </td>
                            <td><small><?= esc($doc['updated_at'] ?? '') ?></small></td>
                            <td>
                                <a href="<?= site_url('admin/editor-secciones/edit/' . $doc['id_documento']) ?>" class="btn btn-warning btn-sm" title="Editar secciones">
                                    <i class="bi bi-pencil-fill me-1"></i>Editar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var tabla = $('#tablaDocumentos').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        order: [[7, 'desc']],
        pageLength: 25,
    });

    // Filtro por cliente
    $('#filtroCliente').on('change', function() {
        tabla.column(1).search(this.value).draw();
    });
});
</script>
<?= $this->endSection() ?>
