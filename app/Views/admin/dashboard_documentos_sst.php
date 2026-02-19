<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Monitor Documentos SST<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
<style>
    .page-header {
        background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
        color: #fff;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 25px;
    }
    .page-header h2 {
        margin: 0;
        font-weight: 700;
    }
    .page-header p {
        margin: 5px 0 0;
        opacity: 0.85;
    }

    /* Tarjetas de estado */
    .stat-card {
        border-radius: 12px;
        padding: 18px 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-card.active {
        border-color: #fff;
        box-shadow: 0 0 0 4px #1c2437, 0 8px 25px rgba(0,0,0,0.3);
        transform: translateY(-6px) scale(1.05);
    }
    .stat-card .stat-number {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }
    .stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }
    .stat-card .stat-icon {
        font-size: 1.3rem;
        margin-bottom: 5px;
    }

    .stat-total { background: linear-gradient(135deg, #1c2437, #2c3e50); color: #fff; }
    .stat-borrador { background: linear-gradient(135deg, #6c757d, #868e96); color: #fff; }
    .stat-generado { background: linear-gradient(135deg, #17a2b8, #20c997); color: #fff; }
    .stat-pendiente_firma { background: linear-gradient(135deg, #ffc107, #f39c12); color: #212529; }
    .stat-aprobado { background: linear-gradient(135deg, #28a745, #20c997); color: #fff; }
    .stat-firmado { background: linear-gradient(135deg, #1c2437, #bd9751); color: #fff; }
    .stat-obsoleto { background: linear-gradient(135deg, #dc3545, #c0392b); color: #fff; }

    /* Filtros */
    .filtros-container {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    /* Tabla */
    .tabla-container {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }

    /* Badges de estado en tabla */
    .badge-estado {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .badge-borrador { background: #6c757d; color: #fff; }
    .badge-generado { background: #17a2b8; color: #fff; }
    .badge-pendiente_firma { background: #ffc107; color: #212529; }
    .badge-aprobado { background: #28a745; color: #fff; }
    .badge-firmado { background: #1c2437; color: #fff; }
    .badge-obsoleto { background: #dc3545; color: #fff; }

    .badge-tipo-dinamico { background: #6f42c1; color: #fff; }
    .badge-tipo-pdf { background: #fd7e14; color: #fff; }
    .badge-tipo-manual { background: #20c997; color: #fff; }

    /* DataTables overrides */
    .dataTables_wrapper .dt-buttons .btn {
        border-radius: 8px;
        font-weight: 500;
    }
    .dataTables_filter input {
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .dataTables_length select {
        border-radius: 8px;
    }

    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 12px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= base_url('/admin/dashboard') ?>"><i class="bi bi-house me-1"></i>Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Monitor Documentos SST</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-file-earmark-text me-2"></i>Monitor de Documentos SST</h2>
                <p>Vista consolidada de todos los documentos generados en la plataforma</p>
            </div>
            <div>
                <span class="badge bg-light text-dark fs-6" id="badgeTotal">
                    <i class="bi bi-files me-1"></i><span id="spanTotalHeader">0</span> documentos
                </span>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-container">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i>Cliente</label>
                <select id="selectCliente" class="form-select" style="width: 100%;">
                    <option value="">-- Todos los clientes --</option>
                    <?php foreach ($clientes ?? [] as $cliente): ?>
                        <option value="<?= esc($cliente['id_cliente']) ?>">
                            <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold"><i class="bi bi-calendar-event me-1"></i>Desde</label>
                <input type="date" id="fechaDesde" class="form-control">
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold"><i class="bi bi-calendar-event me-1"></i>Hasta</label>
                <input type="date" id="fechaHasta" class="form-control">
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" id="btnFiltrar">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <button class="btn btn-outline-secondary" id="btnLimpiar">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estado -->
    <div class="row g-3 mb-4" id="tarjetasEstado">
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-total" data-estado="todos" onclick="filtrarPorEstado('todos', this)">
                <div class="stat-icon"><i class="bi bi-collection"></i></div>
                <div class="stat-number" id="stat-total">0</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-borrador" data-estado="borrador" onclick="filtrarPorEstado('borrador', this)">
                <div class="stat-icon"><i class="bi bi-pencil-square"></i></div>
                <div class="stat-number" id="stat-borrador">0</div>
                <div class="stat-label">Borrador</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-generado" data-estado="generado" onclick="filtrarPorEstado('generado', this)">
                <div class="stat-icon"><i class="bi bi-cpu"></i></div>
                <div class="stat-number" id="stat-generado">0</div>
                <div class="stat-label">Generado</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-pendiente_firma" data-estado="pendiente_firma" onclick="filtrarPorEstado('pendiente_firma', this)">
                <div class="stat-icon"><i class="bi bi-pen"></i></div>
                <div class="stat-number" id="stat-pendiente_firma">0</div>
                <div class="stat-label">Pend. Firma</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-aprobado" data-estado="aprobado" onclick="filtrarPorEstado('aprobado', this)">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-number" id="stat-aprobado">0</div>
                <div class="stat-label">Aprobado</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-firmado" data-estado="firmado" onclick="filtrarPorEstado('firmado', this)">
                <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div class="stat-number" id="stat-firmado">0</div>
                <div class="stat-label">Firmado</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card stat-obsoleto" data-estado="obsoleto" onclick="filtrarPorEstado('obsoleto', this)">
                <div class="stat-icon"><i class="bi bi-archive"></i></div>
                <div class="stat-number" id="stat-obsoleto">0</div>
                <div class="stat-label">Obsoleto</div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="tabla-container position-relative">
        <div class="loading-overlay" id="loadingOverlay">
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div class="fw-semibold text-muted">Cargando documentos...</div>
            </div>
        </div>
        <table id="tablaDocumentos" class="table table-hover table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Tipo Documento</th>
                    <th>Codigo</th>
                    <th>Titulo</th>
                    <th>Ano</th>
                    <th>Version</th>
                    <th>Estado</th>
                    <th>Creacion</th>
                    <th>Actualizacion</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
const BASE_URL = '<?= base_url() ?>';
let dataTable = null;
let estadoFiltroActivo = null;

$(document).ready(function() {
    // Select2
    $('#selectCliente').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Todos los clientes --',
        allowClear: true,
        width: '100%'
    });

    // Inicializar DataTable vacia
    dataTable = $('#tablaDocumentos').DataTable({
        data: [],
        columns: [
            { data: 'nombre_cliente', defaultContent: '-' },
            {
                data: 'tipo_documento',
                render: function(data) {
                    if (!data) return '-';
                    return data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                }
            },
            { data: 'codigo', defaultContent: '-' },
            { data: 'titulo', defaultContent: '-' },
            { data: 'anio', defaultContent: '-' },
            { data: 'version', defaultContent: '1' },
            {
                data: 'estado',
                render: function(data, type) {
                    if (type === 'filter' || type === 'sort') return data;
                    const labels = {
                        'borrador': 'Borrador',
                        'generado': 'Generado',
                        'en_revision': 'En Revisión',
                        'pendiente_firma': 'Pend. Firma',
                        'aprobado': 'Aprobado',
                        'firmado': 'Firmado',
                        'obsoleto': 'Obsoleto'
                    };
                    const label = labels[data] || data;
                    return `<span class="badge-estado badge-${data}">${label}</span>`;
                }
            },
            {
                data: 'created_at',
                render: function(data) {
                    if (!data) return '-';
                    return data.substring(0, 10);
                }
            },
            {
                data: 'updated_at',
                render: function(data) {
                    if (!data) return '-';
                    return data.substring(0, 10);
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar Excel',
                className: 'btn btn-success btn-sm',
                title: 'Monitor Documentos SST',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function(data, row, column, node) {
                            // Limpiar HTML tags para export
                            return data.replace(/<[^>]*>/g, '');
                        }
                    }
                }
            }
        ],
        order: [[7, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
        responsive: true,
        autoWidth: false
    });

    // Cargar datos iniciales
    cargarDatos();

    // Eventos
    $('#btnFiltrar').on('click', function() {
        estadoFiltroActivo = null;
        document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
        cargarDatos();
    });

    $('#btnLimpiar').on('click', function() {
        $('#selectCliente').val('').trigger('change');
        $('#fechaDesde').val('');
        $('#fechaHasta').val('');
        estadoFiltroActivo = null;
        document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
        cargarDatos();
    });
});

function cargarDatos() {
    const idCliente = $('#selectCliente').val();
    const fechaDesde = $('#fechaDesde').val();
    const fechaHasta = $('#fechaHasta').val();

    let params = new URLSearchParams();
    if (idCliente) params.append('id_cliente', idCliente);
    if (fechaDesde) params.append('fecha_desde', fechaDesde);
    if (fechaHasta) params.append('fecha_hasta', fechaHasta);

    $('#loadingOverlay').show();

    fetch(`${BASE_URL}/admin/dashboard-documentos-sst/data?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            $('#loadingOverlay').hide();
            if (data.success) {
                // Actualizar tabla
                dataTable.clear().rows.add(data.documentos).draw();

                // Actualizar tarjetas
                const stats = data.estadisticas;
                document.getElementById('stat-total').textContent = stats.total;
                document.getElementById('stat-borrador').textContent = stats.borrador;
                document.getElementById('stat-generado').textContent = stats.generado;
                document.getElementById('stat-pendiente_firma').textContent = stats.pendiente_firma;
                document.getElementById('stat-aprobado').textContent = stats.aprobado;
                document.getElementById('stat-firmado').textContent = stats.firmado;
                document.getElementById('stat-obsoleto').textContent = stats.obsoleto;
                document.getElementById('spanTotalHeader').textContent = stats.total;
            }
        })
        .catch(err => {
            $('#loadingOverlay').hide();
            console.error('Error cargando datos:', err);
        });
}

function filtrarPorEstado(estado, element) {
    // Toggle active
    document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));

    if (estadoFiltroActivo === estado) {
        // Desactivar filtro
        estadoFiltroActivo = null;
        dataTable.column(6).search('').draw();
    } else {
        element.classList.add('active');
        estadoFiltroActivo = estado;
        if (estado === 'todos') {
            dataTable.column(6).search('').draw();
        } else {
            dataTable.column(6).search('^' + estado + '$', true, false).draw();
        }
    }
}
</script>
<?= $this->endSection() ?>
