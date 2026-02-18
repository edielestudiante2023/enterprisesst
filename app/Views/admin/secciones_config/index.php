<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Secciones Config<?= $this->endSection() ?>

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
                                <i class="bi bi-gear-fill me-2"></i>Configuración de Secciones de Documentos
                            </h4>
                            <small class="opacity-75">Gestión de secciones por tipo de documento · tbl_doc_secciones_config</small>
                        </div>
                        <a href="<?= site_url('addSeccionConfig') ?>" class="btn btn-warning btn-sm fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i>Nueva Sección
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

    <!-- Tabla -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaSeccionesConfig" class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="background:#1c2437;color:#fff;">#ID</th>
                            <th style="background:#1c2437;color:#fff;">Tipo Documento</th>
                            <th style="background:#1c2437;color:#fff;">N°</th>
                            <th style="background:#1c2437;color:#fff;">Nombre Sección</th>
                            <th style="background:#1c2437;color:#fff;">Seccion Key</th>
                            <th style="background:#1c2437;color:#fff;">Tipo Contenido</th>
                            <th style="background:#1c2437;color:#fff;">Tabla Dinám. Tipo</th>
                            <th style="background:#1c2437;color:#fff;" class="text-center">Obligatoria</th>
                            <th style="background:#1c2437;color:#fff;" class="text-center">Orden</th>
                            <th style="background:#1c2437;color:#fff;" class="text-center">Activo</th>
                            <th style="background:#1c2437;color:#fff;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($secciones as $s): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= esc($s['id_seccion_config']) ?></span></td>
                            <td>
                                <div class="fw-semibold"><?= esc($s['nombre_tipo_config'] ?? '—') ?></div>
                                <small class="text-muted"><?= esc($s['tipo_documento'] ?? '') ?></small>
                            </td>
                            <td><?= esc($s['numero']) ?></td>
                            <td><?= esc($s['nombre']) ?></td>
                            <td><code><?= esc($s['seccion_key']) ?></code></td>
                            <td>
                                <?php
                                $badges = [
                                    'texto'          => 'bg-primary',
                                    'tabla_dinamica' => 'bg-warning text-dark',
                                    'lista'          => 'bg-info text-dark',
                                    'mixto'          => 'bg-secondary',
                                ];
                                $badge = $badges[$s['tipo_contenido']] ?? 'bg-light text-dark';
                                ?>
                                <span class="badge <?= $badge ?>"><?= esc($s['tipo_contenido']) ?></span>
                            </td>
                            <td><?= $s['tabla_dinamica_tipo'] ? esc($s['tabla_dinamica_tipo']) : '<span class="text-muted">—</span>' ?></td>
                            <td class="text-center">
                                <?php if ($s['es_obligatoria']): ?>
                                    <i class="bi bi-check-circle-fill text-success" title="Sí"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash-circle text-muted" title="No"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= esc($s['orden']) ?></td>
                            <td class="text-center">
                                <?php if ($s['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('editSeccionConfig/' . $s['id_seccion_config']) ?>"
                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= site_url('deleteSeccionConfig/' . $s['id_seccion_config']) ?>"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar la sección «<?= esc($s['nombre']) ?>»? Esta acción no se puede deshacer.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<style>
    #tablaSeccionesConfig tfoot select {
        width: 100%;
        padding: 3px 5px;
        font-size: 0.78rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #333;
    }
    #tablaSeccionesConfig tfoot th {
        background-color: #e9ecef;
        padding: 6px 8px;
    }
</style>
<script>
$(document).ready(function () {
    // Pre-escanear el DOM ANTES de que DataTables lo procese
    // garantiza leer TODAS las filas (no solo la página actual) y texto limpio
    var cache = { 1: {}, 5: {}, 6: {}, 9: {} };

    $('#tablaSeccionesConfig tbody tr').each(function () {
        var celdas = $(this).find('td');

        // Col 1: value = tipo_documento (snake_case del <small>), label = nombre del <div.fw-semibold>
        var tipDoc = celdas.eq(1).find('small').text().trim();
        var tipNom = celdas.eq(1).find('.fw-semibold').text().trim();
        if (tipDoc) cache[1][tipDoc] = tipNom;

        // Cols 5, 6, 9: texto plano del <td>
        [5, 6, 9].forEach(function (c) {
            var t = celdas.eq(c).text().trim().replace(/\s+/g, ' ');
            if (t && t !== '—') cache[c][t] = true;
        });
    });

    $('#tablaSeccionesConfig').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        order: [[1, 'asc'], [8, 'asc']],
        columnDefs: [{ orderable: false, targets: [10] }],
        pageLength: 25,
        initComplete: function () {
            var api = this.api();

            // ── Col 1: Tipo Documento ──────────────────────────────────────────
            // Busca por la palabra exacta del tipo_documento (snake_case) con \b
            var $s1 = $('<select><option value="">— Todos —</option></select>')
                .appendTo($(api.column(1).footer()).empty())
                .on('change', function () {
                    var val = $(this).val();
                    var esc = $.fn.dataTable.util.escapeRegex(val);
                    api.column(1).search(val ? '\\b' + esc + '\\b' : '', true, false).draw();
                });
            Object.keys(cache[1]).sort().forEach(function (key) {
                $s1.append('<option value="' + key + '">' + cache[1][key] + '</option>');
            });

            // ── Cols 5 (exacto), 6 (substring), 9 (exacto) ───────────────────
            [[5, 'exact'], [6, 'substr'], [9, 'exact']].forEach(function (cfg) {
                var colIdx = cfg[0], mode = cfg[1];
                var $sel = $('<select><option value="">— Todos —</option></select>')
                    .appendTo($(api.column(colIdx).footer()).empty())
                    .on('change', function () {
                        var raw = $(this).val();
                        var esc = $.fn.dataTable.util.escapeRegex(raw);
                        if (mode === 'exact') {
                            // \s* absorbe el whitespace que DataTables conserva al stripear HTML
                            api.column(colIdx).search(raw ? '^\\s*' + esc + '\\s*$' : '', true, false).draw();
                        } else {
                            api.column(colIdx).search(raw, false, false).draw();
                        }
                    });
                Object.keys(cache[colIdx]).sort().forEach(function (txt) {
                    $sel.append('<option value="' + txt + '">' + txt + '</option>');
                });
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
