<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tipos de Documentos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c5aa0;
            --secondary-color: #f8f9fa;
            --accent-color: #0d6efd;
            --danger-color: #dc3545;
            --success-color: #198754;
            --warning-color: #fd7e14;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Navbar mejorado */
        .navbar-custom {
            background: linear-gradient(90deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 15px 0;
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar-logos {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .navbar-logos img {
            height: 80px;
            transition: transform 0.3s ease;
        }

        .navbar-logos img:hover {
            transform: scale(1.05);
        }

        .navbar-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 100%;
            margin: 15px auto 0;
            padding: 0 20px;
            gap: 20px;
        }

        .action-item {
            text-align: center;
        }

        .action-item h2 {
            margin: 0 0 8px 0;
            font-size: 14px;
            font-weight: 600;
            color: #495057;
        }

        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
        }

        .btn-success-custom {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
            border: none;
        }

        .btn-danger-custom {
            background: linear-gradient(45deg, var(--danger-color), #e74c3c);
            color: white;
            border: none;
        }

        /* Contenido principal */
        .main-content {
            margin-top: 180px;
            padding: 0 20px;
            max-width: 100%;
        }

        .page-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        /* Contenedor de tabla mejorado */
        .table-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 0;
            overflow: hidden;
        }

        /* DataTable personalizado */
        .dataTables_wrapper {
            width: 100% !important;
        }

        #documentTypesTable {
            width: 100% !important;
            margin: 0 !important;
        }

        #documentTypesTable thead th {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            border: none;
            padding: 15px 10px;
            font-size: 14px;
        }

        #documentTypesTable tbody td {
            vertical-align: middle;
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }

        #documentTypesTable tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
            transform: scale(1.001);
            transition: all 0.2s ease;
        }

        /* Botones de acción mejorados */
        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin: 0 2px;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-warning-action {
            background: linear-gradient(45deg, var(--warning-color), #ffc107);
            color: white;
        }

        .btn-danger-action {
            background: linear-gradient(45deg, var(--danger-color), #e74c3c);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            color: white;
        }

        /* Filtros mejorados */
        #documentTypesTable tfoot th {
            background: #f8f9fa;
            border-top: 2px solid var(--primary-color);
            padding: 10px;
        }

        .form-control-sm, .form-select-sm {
            border-radius: 6px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-control-sm:focus, .form-select-sm:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Controles de DataTable */
        .dataTables_length select,
        .dataTables_filter input {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 6px 12px;
        }

        .dataTables_info {
            color: #6c757d;
            font-weight: 500;
        }

        .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            color: var(--primary-color);
        }

        .page-link:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Footer mejorado */
        .footer-custom {
            background: linear-gradient(90deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 30px 0;
            border-top: 1px solid rgba(176, 190, 197, 0.3);
            margin-top: 50px;
            text-align: center;
            color: #3A3F51;
            font-size: 14px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 15px;
        }

        .social-links a {
            transition: transform 0.3s ease;
        }

        .social-links a:hover {
            transform: scale(1.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-logos {
                flex-direction: column;
                gap: 10px;
            }

            .navbar-logos img {
                height: 60px;
            }

            .navbar-actions {
                flex-direction: column;
                gap: 10px;
            }

            .main-content {
                margin-top: 280px;
                padding: 0 10px;
            }

            .table-container {
                padding: 15px;
                border-radius: 10px;
            }

            .page-title {
                font-size: 2rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-container {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>

<body>
    <!-- Navbar mejorado -->
    <nav class="navbar-custom">
        <div class="navbar-content">
            <div class="navbar-logos">
                <div><a href="https://dashboard.cycloidtalent.com/login"><img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo"></a></div>
                <div><a href="https://cycloidtalent.com/index.php/consultoria-sst"><img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo"></a></div>
                <div><a href="https://cycloidtalent.com/"><img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo"></a></div>
            </div>
        </div>
        <div class="navbar-actions">
            <div class="action-item">
                <h2><i class="fas fa-tachometer-alt"></i> Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn-custom btn-primary-custom">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </div>
            <button id="clearState" class="btn-custom btn-danger-custom">
                <i class="fas fa-redo"></i> Restablecer Filtros
            </button>
            <div class="action-item">
                <h2><i class="fas fa-plus-circle"></i> Añadir Registro</h2>
                <a href="<?= base_url('/addPolicyType') ?>" class="btn-custom btn-success-custom" target="_blank">
                    <i class="fas fa-file-plus"></i> Nuevo Registro
                </a>
            </div>
        </div>
    </nav>
<div style="height:40px;"></div>
    <!-- Contenido principal -->
    <div class="main-content">
        <h1 class="page-title">
            <i class="fas fa-file-alt"></i> Gestión de Tipos de Documentos
        </h1>

        <div class="table-container">
            <div class="table-responsive">
                <table id="documentTypesTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 8%;"><i class="fas fa-hashtag"></i> ID</th>
                            <th style="width: 35%;"><i class="fas fa-tag"></i> Nombre del Tipo</th>
                            <th style="width: 42%;"><i class="fas fa-info-circle"></i> Descripción</th>
                            <th style="width: 15%;"><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Tipo</th>
                            <th>Descripción</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($policyTypes as $type): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $type['id'] ?></td>
                                <td class="fw-semibold"><?= $type['type_name'] ?></td>
                                <td><?= $type['description'] ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('/editPolicyType/' . $type['id']) ?>" 
                                       class="btn-action btn-warning-action" 
                                       title="Editar registro">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('/deletePolicyType/' . $type['id']) ?>" 
                                       class="btn-action btn-danger-action" 
                                       title="Eliminar registro"
                                       onclick="return confirm('¿Seguro que deseas eliminar este tipo de política?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer mejorado -->
    <footer class="footer-custom">
        <p style="margin:0; font-weight:bold; font-size: 16px;">
            <i class="fas fa-building"></i> Cycloid Talent SAS - © 2024
        </p>
        <p style="margin:8px 0; color: #6c757d;">
            <i class="fas fa-id-card"></i> NIT: 901.653.912
        </p>
        <p style="margin:8px 0;">
            <i class="fas fa-globe"></i> Sitio oficial: 
            <a href="https://cycloidtalent.com/" target="_blank" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                cycloidtalent.com
            </a>
        </p>
        <div class="social-links">
            <a href="https://www.facebook.com/CycloidTalent" target="_blank" title="Facebook">
                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height:28px;">
            </a>
            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" title="LinkedIn">
                <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height:28px;">
            </a>
            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" title="Instagram">
                <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height:28px;">
            </a>
            <a href="https://www.tiktok.com/@cycloid_talent" target="_blank" title="TikTok">
                <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height:28px;">
            </a>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Configuración mejorada de DataTable
        var table = $('#documentTypesTable').DataTable({
            stateSave: true,
            responsive: true,
            autoWidth: false,
            scrollX: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            language: { 
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es_es.json",
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron registros",
                info: "Mostrando página _PAGE_ de _PAGES_ (_TOTAL_ registros total)",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            columnDefs: [
                { 
                    targets: [0], 
                    className: 'text-center',
                    width: '8%'
                },
                { 
                    targets: [1], 
                    width: '35%'
                },
                { 
                    targets: [2], 
                    width: '42%'
                },
                { 
                    targets: [3], 
                    className: 'text-center',
                    orderable: false,
                    width: '15%'
                }
            ],
            initComplete: function() {
                var api = this.api();
                
                // Aplicar filtros personalizados
                api.columns().every(function(index) {
                    var column = this;
                    var footer = $(column.footer());
                    footer.empty();

                    if (index === 0) {
                        // ID: búsqueda numérica
                        $('<input type="number" class="form-control form-control-sm" placeholder="Buscar ID" style="width: 100%;" />')
                            .appendTo(footer)
                            .on('keyup change clear', function() {
                                if (column.search() !== this.value) {
                                    column.search(this.value).draw();
                                }
                            });
                    } else if (index === 1) {
                        // Nombre: select de valores únicos
                        var select = $('<select class="form-select form-select-sm" style="width: 100%;"><option value="">Todos los tipos</option></select>')
                            .appendTo(footer)
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });
                        
                        column.data().unique().sort().each(function(d) {
                            if (d) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            }
                        });
                    } else if (index === 2) {
                        // Descripción: búsqueda de texto
                        $('<input type="text" class="form-control form-control-sm" placeholder="Buscar en descripción" style="width: 100%;" />')
                            .appendTo(footer)
                            .on('keyup change clear', function() {
                                if (column.search() !== this.value) {
                                    column.search(this.value).draw();
                                }
                            });
                    }
                });

                // Animación de entrada para las filas
                $('#documentTypesTable tbody tr').each(function(index) {
                    $(this).css({
                        'opacity': '0',
                        'transform': 'translateY(20px)'
                    }).delay(index * 50).animate({
                        'opacity': '1'
                    }, 300).css('transform', 'translateY(0px)');
                });
            }
        });

        // Botón de restablecer filtros mejorado
        $('#clearState').on('click', function() {
            // Limpiar estado de DataTable
            table.state.clear();
            
            // Limpiar búsquedas
            table.search('').columns().search('').draw();
            
            // Limpiar campos de filtro
            $('#documentTypesTable tfoot input, #documentTypesTable tfoot select').val('');
            
            // Feedback visual
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Restableciendo...');
            
            setTimeout(() => {
                $(this).html('<i class="fas fa-redo"></i> Restablecer Filtros');
            }, 1000);
        });

        // Mejorar confirmación de eliminación
        $(document).on('click', '.btn-danger-action', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var row = $(this).closest('tr');
            var typeName = row.find('td:nth-child(2)').text();
            
            if (confirm('⚠️ ¿Estás seguro de que deseas eliminar el tipo de documento "' + typeName + '"?\n\nEsta acción no se puede deshacer.')) {
                // Animación de salida
                row.fadeOut(300, function() {
                    window.location.href = href;
                });
            }
        });

        // Tooltips para botones de acción
        $('[title]').each(function() {
            $(this).attr('data-bs-toggle', 'tooltip');
        });

        // Efecto hover mejorado para filas
        $('#documentTypesTable tbody').on('mouseenter', 'tr', function() {
            $(this).addClass('table-hover-effect');
        }).on('mouseleave', 'tr', function() {
            $(this).removeClass('table-hover-effect');
        });
    });
    </script>
</body>
</html>