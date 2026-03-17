<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$noPendientes = $totalVotantes - $yaVotaron;
$participacion = $totalVotantes > 0 ? round(($yaVotaron / $totalVotantes) * 100, 1) : 0;
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admindashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>">Comites</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>">Proceso</a></li>
            <li class="breadcrumb-item active">Censo de Votantes</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Censo de Votantes
            </h2>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= esc($proceso['tipo_comite']) ?></p>
        </div>
        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Proceso
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Cards de estado clickeables -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm card-filtro h-100 active" data-filtro="todos" style="cursor:pointer; border-left: 4px solid #0d6efd !important;">
                <div class="card-body text-center py-3">
                    <div class="h2 mb-1 text-primary fw-bold"><?= $totalVotantes ?></div>
                    <div class="small text-muted"><i class="bi bi-people me-1"></i>Total Censo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm card-filtro h-100" data-filtro="votaron" style="cursor:pointer; border-left: 4px solid #198754 !important;">
                <div class="card-body text-center py-3">
                    <div class="h2 mb-1 text-success fw-bold"><?= $yaVotaron ?></div>
                    <div class="small text-muted"><i class="bi bi-check-circle me-1"></i>Ya Votaron</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm card-filtro h-100" data-filtro="pendientes" style="cursor:pointer; border-left: 4px solid #ffc107 !important;">
                <div class="card-body text-center py-3">
                    <div class="h2 mb-1 text-warning fw-bold"><?= $noPendientes ?></div>
                    <div class="small text-muted"><i class="bi bi-hourglass-split me-1"></i>Pendientes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body text-center py-3">
                    <div class="h2 mb-1 text-info fw-bold"><?= $participacion ?>%</div>
                    <div class="small text-muted"><i class="bi bi-bar-chart me-1"></i>Participacion</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formularios -->
        <div class="col-md-4">
            <!-- Agregar votante individual -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Agregar Votante</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/proceso/agregar-votante') ?>" method="post">
                        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Documento *</label>
                                <input type="text" name="documento_identidad" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" name="nombres" class="form-control" required placeholder="Ej: Juan Carlos Rodriguez Perez">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="cargo" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Area</label>
                                <input type="text" name="area" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-lg me-1"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Importar CSV -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar CSV</h6>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/plantilla-csv') ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-download me-1"></i>Plantilla
                    </a>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/proceso/importar-csv') ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
                        <div class="alert alert-info py-2 small">
                            <strong>Obligatorio:</strong> cedula;nombre<br>
                            <span class="text-muted">Opcional: ;email;cargo;area</span>
                        </div>
                        <div class="mb-2">
                            <input type="file" name="archivo_csv" class="form-control form-control-sm" accept=".csv,.txt" required>
                        </div>
                        <div class="mb-2">
                            <select name="separador" class="form-select form-select-sm">
                                <option value=";">Separador: punto y coma (;)</option>
                                <option value=",">Separador: coma (,)</option>
                                <option value="\t">Separador: tabulador</option>
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="tiene_encabezado" id="tieneEncabezado" checked>
                            <label class="form-check-label small" for="tieneEncabezado">Primera fila es encabezado</label>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-upload me-1"></i>Importar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Envio masivo de emails -->
            <?php if ($totalVotantes > 0 && $proceso['estado'] === 'votacion'): ?>
            <?php
            $conEmail = 0; $pendientesConEmail = 0;
            foreach ($votantes as $v) {
                if (!empty($v['email'])) { $conEmail++; if (!$v['ha_votado']) $pendientesConEmail++; }
            }
            ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>Notificar por Email</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2 text-muted"><?= $conEmail ?> con email | <?= $pendientesConEmail ?> pendientes</p>
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/enviar-enlaces-todos') ?>"
                          method="post" onsubmit="return confirm('¿Enviar email a todos los votantes pendientes?');">
                        <button type="submit" class="btn btn-info text-white btn-sm w-100" <?= $pendientesConEmail === 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-send me-1"></i>Enviar a Pendientes (<?= $pendientesConEmail ?>)
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de votantes -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Votantes Registrados</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary" id="lblFiltroActivo">Todos: <?= $totalVotantes ?></span>
                        <button id="btnDescargarExcel" class="btn btn-success btn-sm">
                            <i class="bi bi-file-earmark-excel me-1"></i>Descargar Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($votantes)): ?>
                    <div class="alert alert-warning mb-0">
                        No hay votantes registrados. Agregue votantes usando los formularios.
                    </div>
                    <?php else: ?>
                    <table id="tablaCenso" class="table table-hover table-sm w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($votantes as $v): ?>
                            <tr data-votado="<?= $v['ha_votado'] ? '1' : '0' ?>">
                                <td><code class="small"><?= esc($v['documento_identidad']) ?></code></td>
                                <td><?= esc(trim($v['nombres'] . ' ' . $v['apellidos'])) ?></td>
                                <td><small class="text-muted"><?= esc($v['cargo'] ?? '-') ?></small></td>
                                <td>
                                    <?php if ($v['ha_votado']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Voto</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$v['ha_votado']): ?>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" title="Copiar enlace"
                                                onclick="copiarEnlace('<?= base_url('votar/emitir/' . $v['token_acceso']) ?>')">
                                            <i class="bi bi-link-45deg"></i>
                                        </button>
                                        <?php if (!empty($v['email'])): ?>
                                        <form action="<?= base_url('comites-elecciones/votante/' . $v['id_votante'] . '/enviar-enlace') ?>"
                                              method="post" class="d-inline">
                                            <button type="submit" class="btn btn-outline-info btn-sm" title="Enviar email">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <small class="text-muted"><?= date('d/m H:i', strtotime($v['fecha_voto'])) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
var filtroActivo = 'todos';

// Filtro personalizado DataTables
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
    if (filtroActivo === 'todos') return true;
    var fila = $(settings.nTable).find('tbody tr').eq(dataIndex);
    var votado = fila.data('votado');
    if (filtroActivo === 'votaron') return votado == '1';
    if (filtroActivo === 'pendientes') return votado == '0';
    return true;
});

var tabla = $('#tablaCenso').DataTable({
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
    },
    pageLength: 25,
    order: [[3, 'asc']],
    columnDefs: [{ orderable: false, targets: 4 }]
});

// Cards clickeables
$('.card-filtro').on('click', function() {
    filtroActivo = $(this).data('filtro');

    $('.card-filtro').css('opacity', '0.55').css('transform', 'scale(0.97)');
    $(this).css('opacity', '1').css('transform', 'scale(1)');

    var labels = { todos: 'Todos: <?= $totalVotantes ?>', votaron: 'Votaron: <?= $yaVotaron ?>', pendientes: 'Pendientes: <?= $noPendientes ?>' };
    $('#lblFiltroActivo').text(labels[filtroActivo]);

    tabla.draw();
});

function copiarEnlace(url) {
    navigator.clipboard.writeText(url).then(() => {
        var btn = event.currentTarget;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-link-45deg"></i>', 1500);
    });
}

$('#btnDescargarExcel').on('click', function() {
    var etiquetasFiltro = { todos: 'Todos', votaron: 'Votaron', pendientes: 'Pendientes' };
    var nombreFiltro = etiquetasFiltro[filtroActivo] || 'Todos';
    var fechaHoy = new Date().toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
    var nombreArchivo = 'Censo_<?= esc($proceso['tipo_comite']) ?>_<?= $proceso['id_proceso'] ?>_' + nombreFiltro + '_' + new Date().toISOString().slice(0,10) + '.xlsx';

    // Filas de encabezado informativo
    var filas = [
        ['CENSO DE VOTANTES - <?= strtoupper(esc($proceso['tipo_comite'])) ?>'],
        [],
        ['Empresa:',    '<?= esc($cliente['nombre_cliente']) ?>'],
        ['Comité:',     '<?= esc($proceso['tipo_comite']) ?>'],
        ['Proceso N°:', '<?= $proceso['id_proceso'] ?>'],
        ['Estado:',     '<?= ucfirst(esc($proceso['estado'])) ?>'],
        ['Exportado:',  fechaHoy],
        ['Filtro:',     etiquetasFiltro[filtroActivo] || 'Todos'],
        [],
        ['Documento', 'Nombre', 'Cargo', 'Estado']
    ];

    tabla.rows({ search: 'applied' }).nodes().each(function(row) {
        var $row = $(row);
        var documento = $row.find('td:eq(0)').text().trim();
        var nombre    = $row.find('td:eq(1)').text().trim();
        var cargo     = $row.find('td:eq(2)').text().trim();
        var estado    = $row.find('td:eq(3)').text().trim();
        filas.push([documento, nombre, cargo, estado]);
    });

    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.aoa_to_sheet(filas);
    ws['!cols'] = [{ wch: 18 }, { wch: 38 }, { wch: 25 }, { wch: 14 }];
    XLSX.utils.book_append_sheet(wb, ws, 'Censo');
    XLSX.writeFile(wb, nombreArchivo);
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
