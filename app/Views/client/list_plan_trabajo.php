<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de Trabajo Anual</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS para Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h2 {
            color: #343a40;
            font-weight: 600;
        }

        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .btn-dashboard {
            margin-bottom: 1.5rem;
        }

        /* Estilo para el tooltip */
        .tooltip-inner {
            max-width: 300px;
            white-space: normal;
        }

        /* Ajuste de altura de filas */
        tbody tr {
            height: 45px;
        }

        /* Ancho máximo de la columna "Actividad" */
        .actividad-column {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Estilos adicionales para asegurar la responsividad y apariencia */
        table.dataTable thead th,
        table.dataTable tfoot th {
            vertical-align: middle;
            text-align: center;
        }

        table.dataTable tbody td {
            vertical-align: middle;
            text-align: center;
        }

        /* Ajuste de ancho de columnas para evitar espacios excesivos */
        #planesTable th:nth-child(4), /* Actividad */
        #planesTable th:nth-child(10) /* Observaciones */ {
            width: 150px;
        }

        /* Botones adicionales */
        .dt-buttons {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <!-- Navbar fijo -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logos -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>
        </div>
    </nav>

    <!-- Espaciador para evitar que el contenido quede oculto debajo del navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="container-fluid mt-5">
        <h2 class="text-center mb-4">Plan de Trabajo Anual</h2>

        <!-- Botón para restablecer filtros -->
        <div class="d-flex justify-content-end mb-3">
            <button id="clearState" class="btn btn-secondary">Restablecer Filtros</button>
        </div>

        <!-- Mensaje de éxito -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <!-- Tabla interactiva con DataTables y filtros personalizados -->
        <div class="table-responsive">
            <table id="planesTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <!-- <th>Cliente</th> -->
                        <th>PHVA</th>
                        <!-- <th>Numeral</th> -->
                        <th>Actividad</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Cierre</th>
                        <th>Responsable</th>
                        <th>Estado de Actividad</th>
                        <th>Porcentaje de Avance</th>
                        <th>Semana</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <!-- <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th> -->
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <!-- <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th> -->
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                        <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($planes as $plan): ?>
                        <tr>
                            <!-- <td data-bs-toggle="tooltip" title="<?= esc($plan['nombre_cliente']) ?>"><?= esc($plan['nombre_cliente']) ?></td> -->
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['phva_plandetrabajo']) ?>"><?= esc($plan['phva_plandetrabajo']) ?></td>
                            <!-- <td data-bs-toggle="tooltip" title="<?= esc($plan['numeral_actividad']) ?>"><?= esc($plan['numeral_actividad']) ?></td> -->
                            <td class="actividad-column" data-bs-toggle="tooltip" title="<?= esc($plan['nombre_actividad']) ?>">
                                <?= strlen(esc($plan['nombre_actividad'])) > 40 ? substr(esc($plan['nombre_actividad']), 0, 40) . '...' : esc($plan['nombre_actividad']) ?>
                            </td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['fecha_propuesta']) ?>"><?= esc($plan['fecha_propuesta']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['fecha_cierre']) ?>"><?= esc($plan['fecha_cierre']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['responsable_sugerido_plandetrabajo']) ?>"><?= esc($plan['responsable_sugerido_plandetrabajo']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['estado_actividad']) ?>"><?= esc($plan['estado_actividad']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['porcentaje_avance']) ?>%"><?= esc($plan['porcentaje_avance']) ?>%</td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['semana']) ?>"><?= esc($plan['semana']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['observaciones']) ?>"><?= esc($plan['observaciones']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div class="container d-flex flex-column align-items-center">
            <!-- Company and Rights -->
            <p class="fw-bold mb-0">Cycloid Talent SAS</p>
            <p class="mb-2">Todos los derechos reservados © 2024</p>
            <p class="mb-2">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p class="mb-2">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p class="mb-3"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" class="text-dark">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" class="text-dark">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" class="text-dark">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" class="text-dark">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- Librerías necesarias -->
    <!-- jQuery 3.6.0 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS para Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <!-- JSZip para exportar a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- Buttons para Excel export -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <!-- Buttons para ColVis -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
    <!-- XSLX (si deseas mantener la funcionalidad de descarga personalizada) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Script para descargar a Excel utilizando XSLX -->
    <script>
        document.getElementById('downloadExcel')?.addEventListener('click', function () {
            // Selecciona la tabla
            const table = document.getElementById('planesTable');
            
            // Crea una hoja de cálculo a partir de la tabla
            const wb = XLSX.utils.table_to_book(table, {sheet: "Planes de Trabajo"});
            
            // Genera el archivo Excel y lo descarga
            XLSX.writeFile(wb, 'PlanesDeTrabajo.xlsx');
        });
    </script>

    <!-- Script para inicializar DataTables, filtros dinámicos y tooltips -->
    <script>
        $(document).ready(function() {
            // Inicialización de DataTables con las funcionalidades requeridas
            var table = $('#planesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json' // Archivo de idioma en español
                },
                pageLength: 10, // Número de registros por página
                responsive: true, // Diseño responsivo
                stateSave: true, // Persistencia del estado (filtros, paginación, etc.)
                dom: 'Bfrtip', // Estructura de control: Botones, filtro, tabla, paginación
                buttons: [
                    {
                        extend: 'colvis',
                        text: 'Mostrar/Ocultar Columnas',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Descargar Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible' // Exporta solo las columnas visibles
                        }
                    }
                ],
                initComplete: function() {
                    var api = this.api();

                    // Iterar sobre cada columna para agregar un filtro desplegable en el tfoot
                    api.columns().every(function() {
                        var column = this;
                        var select = $('select', column.footer());

                        // Obtener valores únicos y ordenarlos
                        var uniqueData = [];
                        column.data().unique().sort().each(function(d) {
                            if (d) { // Excluir valores nulos o vacíos
                                uniqueData.push(d);
                            }
                        });

                        // Agregar opciones al select
                        uniqueData.forEach(function(d) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });

                        // Event listener para filtrar la columna
                        select.on('change', function() {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                            column
                                .search(val ? '^' + val + '$' : '', true, false)
                                .draw();
                        });
                    });
                }
            });

         

            // Botón "Restablecer Filtros"
            $('#clearState').on('click', function () {
                // Remover el estado guardado de DataTables
                table.state.clear();
                // Remover el ítem específico de localStorage si corresponde
                localStorage.removeItem('DataTables_planesTable');
                // Recargar la página para aplicar los cambios
                location.reload();
            });

            // Inicializar tooltips de Bootstrap en todas las celdas con el atributo data-bs-toggle="tooltip"
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Inicializar tooltips al cargar la página
            initializeTooltips();

            // Re-inicializar tooltips después de cada redibujado de la tabla
            table.on('draw', function() {
                initializeTooltips();
            });
        });
    </script>

</body>

</html>
