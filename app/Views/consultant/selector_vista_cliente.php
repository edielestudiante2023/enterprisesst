<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Vista del Cliente - Enterprise SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 50%, #c9a84c 100%);
            padding: 3rem 0;
            color: white;
        }
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 50px;
            font-size: 1.1rem;
            padding: 0.5rem;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
        }
        .btn-ver-dashboard {
            background: linear-gradient(135deg, #bd9751, #d4af37);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-ver-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(189, 151, 81, 0.4);
            color: white;
        }
        .btn-ver-dashboard:disabled {
            opacity: 0.5;
            transform: none;
            box-shadow: none;
        }
        .client-info {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .client-info.show {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="bi bi-eye me-2"></i>Ver Vista del Cliente
                    </h1>
                    <p class="mb-0 opacity-75">Seleccione un cliente para ver su dashboard tal como lo ve el cliente</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?= base_url(session()->get('role') === 'admin' ? 'admin/dashboard' : 'consultor/dashboard') ?>" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-building me-2 text-warning"></i>Seleccionar Cliente
                        </h5>

                        <?php if (empty($clientes)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                No hay clientes activos disponibles.
                            </div>
                        <?php else: ?>
                            <select id="selectCliente" class="form-select form-select-lg" style="width: 100%;">
                                <option value="">Buscar cliente por nombre o NIT...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id_cliente'] ?>"
                                            data-nit="<?= esc($cliente['nit_cliente'] ?? '') ?>"
                                            data-ciudad="<?= esc($cliente['ciudad_cliente'] ?? '') ?>"
                                            data-contacto="<?= esc($cliente['persona_contacto_compras'] ?? '') ?>"
                                            data-estandar="<?= esc($cliente['estandares'] ?? '') ?>">
                                        <?= esc($cliente['nombre_cliente']) ?>
                                        <?php if (!empty($cliente['nit_cliente'])): ?>
                                            - NIT: <?= esc($cliente['nit_cliente']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-search me-1"></i>
                                <?= count($clientes) ?> cliente(s) activo(s). Escriba para buscar.
                            </small>

                            <!-- Info del cliente seleccionado -->
                            <div id="clientInfo" class="client-info mt-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">NIT</small>
                                                <p class="mb-2 fw-bold" id="infoNit">-</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Ciudad</small>
                                                <p class="mb-2 fw-bold" id="infoCiudad">-</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Contacto</small>
                                                <p class="mb-2 fw-bold" id="infoContacto">-</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Estandar</small>
                                                <p class="mb-0 fw-bold" id="infoEstandar">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón para abrir dashboard -->
                            <div class="text-center mt-4">
                                <button id="btnVerDashboard" class="btn btn-ver-dashboard" disabled>
                                    <i class="bi bi-box-arrow-up-right me-2"></i>Abrir Dashboard del Cliente
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selectCliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cliente por nombre o NIT...',
                allowClear: true,
                language: {
                    noResults: function() { return "No se encontraron clientes"; },
                    searching: function() { return "Buscando..."; }
                }
            });

            var selectedClientId = null;

            $('#selectCliente').on('select2:select', function(e) {
                selectedClientId = e.params.data.id;
                var $option = $(e.params.data.element);

                // Mostrar info del cliente
                $('#infoNit').text($option.data('nit') || '-');
                $('#infoCiudad').text($option.data('ciudad') || '-');
                $('#infoContacto').text($option.data('contacto') || '-');
                $('#infoEstandar').text($option.data('estandar') || '-');
                $('#clientInfo').addClass('show');
                $('#btnVerDashboard').prop('disabled', false);
            });

            $('#selectCliente').on('select2:clear', function() {
                selectedClientId = null;
                $('#clientInfo').removeClass('show');
                $('#btnVerDashboard').prop('disabled', true);
            });

            $('#btnVerDashboard').on('click', function() {
                if (selectedClientId) {
                    window.open('<?= base_url('consultor/vista-cliente') ?>/' + selectedClientId, '_blank');
                }
            });
        });
    </script>
</body>
</html>
