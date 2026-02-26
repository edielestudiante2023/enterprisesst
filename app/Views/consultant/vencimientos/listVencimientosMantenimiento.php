<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listado de Vencimientos de Mantenimiento</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

  <style>
    .dataTables_filter {
      margin-bottom: 1rem;
    }
    .dt-buttons {
      margin-bottom: 1rem;
    }
    .action-buttons .btn {
      margin: 2px;
    }
    .table thead th {
      background-color: #f8f9fa;
    }
    .loading {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.8);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    tfoot input, tfoot select {
      width: 100%;
      padding: 3px;
      box-sizing: border-box;
    }
    .date-range-filter {
      margin-bottom: 15px;
    }
    .date-range-filter input {
      width: 150px;
      margin-right: 10px;
    }
    /* Estilos para resaltar vencimientos */
    .vencido {
      background-color: #ffebee !important;
      color: #c62828 !important;
    }
    .proximo-vencer {
      background-color: #fff8e1 !important;
      color: #f57f17 !important;
    }
    .ejecutado {
      background-color: #e8f5e8 !important;
      color: #2e7d32 !important;
    }
    /* Estilos para Select2 */
    .select2-container {
      width: 100% !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
      min-height: calc(1.5em + 0.75rem + 2px);
      padding: 0.375rem 0.75rem;
      font-size: 1rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
      padding-left: 0;
      color: #495057;
    }
  </style>
</head>

<body>
  <div class="container-fluid py-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Listado de Vencimientos de Mantenimiento</h2>
      </div>
      <div class="card-body">
        <!-- Mensajes de exito -->
        <?php if (session()->getFlashdata('msg')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if (isset($mostrar_filtro) && $mostrar_filtro): ?>
        <!-- Formulario de filtro por cliente -->
        <div class="alert alert-info mb-4">
          <h5><i class="fas fa-filter me-2"></i>Filtrar por Cliente</h5>
          <p>Para mejorar la velocidad de carga, debe seleccionar un cliente antes de mostrar los datos.</p>
        </div>

        <form method="GET" action="<?= current_url() ?>">
          <div class="row mb-4">
            <div class="col-md-8">
              <label for="cliente_id" class="form-label"><strong>Buscar y Seleccionar Cliente:</strong></label>
              <select name="cliente_id" id="cliente_id" class="form-select select2-cliente" required>
                <option value="">-- Busque y seleccione un cliente --</option>
                <?php if (!empty($clientes)): ?>
                  <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= esc($cliente['id_cliente']) ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>Cargar Vencimientos
              </button>
            </div>
          </div>
        </form>
        <?php else: ?>

        <!-- Informacion del cliente seleccionado -->
        <div class="alert alert-success mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <i class="fas fa-user me-2"></i><strong>Cliente seleccionado:</strong>
              <?php
                $clienteActual = null;
                foreach ($clientes as $cliente) {
                  if ($cliente['id_cliente'] == $cliente_seleccionado) {
                    $clienteActual = $cliente['nombre_cliente'];
                    break;
                  }
                }
                echo esc($clienteActual);
              ?>
            </div>
            <a href="<?= current_url() ?>?cambiar_cliente=1" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-exchange-alt me-2"></i>Cambiar Cliente
            </a>
          </div>
        </div>

        <!-- Botones de acciones principales -->
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="btn-group">
              <a href="<?= site_url('vencimientos/add') ?>?cliente_id=<?= esc($cliente_seleccionado) ?>" id="btn-agregar" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Agregar Nuevo
              </a>
              <a href="<?= base_url('vencimientos/send-emails') ?>" class="btn btn-warning">
                <i class="fas fa-envelope me-2"></i>Enviar Recordatorios
              </a>
              <button type="submit" class="btn btn-info" form="sendSelectedForm">
                <i class="fas fa-paper-plane me-2"></i>Enviar a Seleccionados
              </button>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!isset($mostrar_filtro) || !$mostrar_filtro): ?>
        <!-- Filtros superiores -->
        <div class="row mb-3">
          <div class="col-md-3">
            <label for="topFilter_cliente"><strong>Filtrar por Cliente:</strong></label>
            <input type="text" id="topFilter_cliente" class="form-control" placeholder="Buscar Cliente" />
          </div>
          <div class="col-md-6">
            <label><strong>Rango de Fechas de Vencimiento:</strong></label>
            <div class="date-range-filter">
              <input type="date" id="filter_fecha_vencimiento_inicio" class="form-control d-inline-block" placeholder="Desde" />
              <input type="date" id="filter_fecha_vencimiento_fin" class="form-control d-inline-block" placeholder="Hasta" />
            </div>
          </div>
          <div class="col-md-3">
            <button type="button" id="resetFilters" class="btn btn-secondary mt-4">
              <i class="fas fa-undo me-2"></i>Limpiar Filtros
            </button>
          </div>
        </div>

        <!-- Tabla de Vencimientos Sin Ejecutar -->
        <?php if (!empty($vencimientos_sin_ejecutar)): ?>
        <div class="mb-4">
          <h4 class="text-danger mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>Vencimientos Sin Ejecutar
            <span class="badge bg-danger"><?= count($vencimientos_sin_ejecutar) ?></span>
          </h4>
          <form id="sendSelectedForm" method="post" action="<?= site_url('vencimientos/send-selected-emails') ?>">
            <table id="vencimientosSinEjecutarTable" class="table table-striped table-bordered table-hover">
              <thead class="table-danger">
                <tr>
                  <th><input type="checkbox" id="selectAllSinEjecutar" class="form-check-input" /></th>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Consultor</th>
                  <th>Mantenimiento</th>
                  <th>Fecha de Vencimiento</th>
                  <th>Estado</th>
                  <th>Observaciones</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($vencimientos_sin_ejecutar as $vencimiento): ?>
                  <?php
                    $fecha_vencimiento = $vencimiento['fecha_vencimiento'];
                    $clase_fila = '';
                    if (!empty($fecha_vencimiento) && $fecha_vencimiento != '0000-00-00') {
                      $fecha_venc = new DateTime($fecha_vencimiento);
                      $hoy = new DateTime();
                      $diff = $hoy->diff($fecha_venc);

                      if ($fecha_venc < $hoy) {
                        $clase_fila = 'vencido';
                      } elseif ($diff->days <= 30 && $fecha_venc > $hoy) {
                        $clase_fila = 'proximo-vencer';
                      }
                    }
                  ?>
                  <tr class="<?= $clase_fila ?>">
                    <td>
                      <input type="checkbox" class="form-check-input email-checkbox-sin-ejecutar"
                             name="selected[]" value="<?= esc($vencimiento['id']) ?>" />
                    </td>
                    <td><?= esc($vencimiento['id']) ?></td>
                    <td><?= esc($vencimiento['cliente']) ?></td>
                    <td><?= esc($vencimiento['consultor']) ?></td>
                    <td><?= esc($vencimiento['mantenimiento']) ?></td>
                    <td data-order="<?= (!empty($vencimiento['fecha_vencimiento']) && $vencimiento['fecha_vencimiento'] != '0000-00-00') ? strtotime(esc($vencimiento['fecha_vencimiento'])) : 0 ?>">
                      <?= (!empty($vencimiento['fecha_vencimiento']) && $vencimiento['fecha_vencimiento'] != '0000-00-00') ? date('d/m/Y', strtotime(esc($vencimiento['fecha_vencimiento']))) : '' ?>
                    </td>
                    <td><span class="badge bg-warning text-dark"><?= esc($vencimiento['estado_actividad']) ?></span></td>
                    <td><?= esc($vencimiento['observaciones']) ?></td>
                    <td class="action-buttons">
                      <a href="<?= site_url('vencimientos/edit/' . esc($vencimiento['id'])) ?>?cliente_id=<?= esc($cliente_seleccionado) ?>"
                         class="btn btn-sm btn-primary btn-editar" data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a href="<?= site_url('vencimientos/delete/' . esc($vencimiento['id'])) ?>"
                         class="btn btn-sm btn-danger"
                         onclick="return confirm('Estas seguro de eliminar este vencimiento?');"
                         data-bs-toggle="tooltip" title="Eliminar">
                        <i class="fas fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </form>
        </div>
        <?php endif; ?>

        <!-- Tabla de Vencimientos Ejecutados -->
        <?php if (!empty($vencimientos_ejecutados)): ?>
        <div class="mb-4">
          <h4 class="text-success mb-3">
            <i class="fas fa-check-circle me-2"></i>Vencimientos Ejecutados
            <span class="badge bg-success"><?= count($vencimientos_ejecutados) ?></span>
          </h4>
          <table id="vencimientosEjecutadosTable" class="table table-striped table-bordered table-hover">
            <thead class="table-success">
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Consultor</th>
                <th>Mantenimiento</th>
                <th>Fecha de Vencimiento</th>
                <th>Fecha de Realizacion</th>
                <th>Estado</th>
                <th>Observaciones</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vencimientos_ejecutados as $vencimiento): ?>
                <tr class="ejecutado">
                  <td><?= esc($vencimiento['id']) ?></td>
                  <td><?= esc($vencimiento['cliente']) ?></td>
                  <td><?= esc($vencimiento['consultor']) ?></td>
                  <td><?= esc($vencimiento['mantenimiento']) ?></td>
                  <td data-order="<?= (!empty($vencimiento['fecha_vencimiento']) && $vencimiento['fecha_vencimiento'] != '0000-00-00') ? strtotime(esc($vencimiento['fecha_vencimiento'])) : 0 ?>">
                    <?= (!empty($vencimiento['fecha_vencimiento']) && $vencimiento['fecha_vencimiento'] != '0000-00-00') ? date('d/m/Y', strtotime(esc($vencimiento['fecha_vencimiento']))) : '' ?>
                  </td>
                  <td data-order="<?= (!empty($vencimiento['fecha_realizacion']) && $vencimiento['fecha_realizacion'] != '0000-00-00') ? strtotime(esc($vencimiento['fecha_realizacion'])) : 0 ?>">
                    <?= (!empty($vencimiento['fecha_realizacion']) && $vencimiento['fecha_realizacion'] != '0000-00-00') ? date('d/m/Y', strtotime(esc($vencimiento['fecha_realizacion']))) : '-' ?>
                  </td>
                  <td><span class="badge bg-success"><?= esc($vencimiento['estado_actividad']) ?></span></td>
                  <td><?= esc($vencimiento['observaciones']) ?></td>
                  <td class="action-buttons">
                    <a href="<?= site_url('vencimientos/edit/' . esc($vencimiento['id'])) ?>?cliente_id=<?= esc($cliente_seleccionado) ?>"
                       class="btn btn-sm btn-primary btn-editar" data-bs-toggle="tooltip" title="Editar">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= site_url('vencimientos/delete/' . esc($vencimiento['id'])) ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Estas seguro de eliminar este vencimiento?');"
                       data-bs-toggle="tooltip" title="Eliminar">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Loading indicator -->
  <div class="loading d-none">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
      // Inicializar Select2 para el selector de cliente
      $('.select2-cliente').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Busque y seleccione un cliente --',
        allowClear: true,
        language: {
          noResults: function() {
            return "No se encontraron resultados";
          },
          searching: function() {
            return "Buscando...";
          }
        }
      });

      // Cargar cliente desde localStorage si existe (para sincronizacion con quick-access)
      var storedClient = localStorage.getItem('selectedClient');
      var urlParams = new URLSearchParams(window.location.search);
      var cambiarCliente = urlParams.get('cambiar_cliente');

      // Solo auto-cargar si no hay cliente_id en URL y NO se esta cambiando de cliente manualmente
      if (storedClient && !window.location.search.includes('cliente_id') && !cambiarCliente) {
        $('.select2-cliente').val(storedClient).trigger('change');

        // Auto-click en "Cargar Vencimientos" despues de seleccionar cliente
        setTimeout(function() {
          $('form[action="<?= current_url() ?>"]').submit();
        }, 500);
      }

      // Mantener cliente seleccionado desde la URL
      function mantenerClienteSeleccionado() {
        var urlParams = new URLSearchParams(window.location.search);
        var clienteId = urlParams.get('cliente_id');
        if (clienteId) {
          $('.select2-cliente').val(clienteId).trigger('change');

          // Guardar en localStorage para sincronizacion
          localStorage.setItem('selectedClient', clienteId);

          // Actualizar el filtro superior con el nombre del cliente
          var nombreCliente = $('.select2-cliente option:selected').text();
          if (nombreCliente && nombreCliente !== '-- Busque y seleccione un cliente --') {
            $('#topFilter_cliente').val(nombreCliente);
          }
        }
      }

      // Ejecutar al cargar la pagina
      mantenerClienteSeleccionado();

      // Escuchar cambios en el Select2 para actualizar el filtro superior y localStorage
      $('.select2-cliente').on('change', function() {
        var clienteId = $(this).val();
        var nombreCliente = $(this).find('option:selected').text();

        // Guardar en localStorage
        if (clienteId) {
          localStorage.setItem('selectedClient', clienteId);
        } else {
          localStorage.removeItem('selectedClient');
        }

        // Actualizar filtro superior
        if (nombreCliente && nombreCliente !== '-- Busque y seleccione un cliente --') {
          $('#topFilter_cliente').val(nombreCliente);
        } else {
          $('#topFilter_cliente').val('');
        }
      });

      // Inicializar tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      <?php if (!isset($mostrar_filtro) || !$mostrar_filtro): ?>
      // Inicializar DataTable para vencimientos sin ejecutar
      var tableSinEjecutar = null;
      if ($('#vencimientosSinEjecutarTable').length) {
        tableSinEjecutar = $('#vencimientosSinEjecutarTable').DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
          },
          order: [[5, 'asc']],
          pageLength: 10,
          dom: 'Bfrtip',
          buttons: [
            {
              extend: 'excel',
              text: '<i class="fas fa-file-excel me-2"></i>Exportar Sin Ejecutar',
              className: 'btn btn-warning',
              exportOptions: {
                columns: [1,2,3,4,5,6,7]
              }
            }
          ],
          columnDefs: [
            {
              targets: [0, 8],
              orderable: false,
              searchable: false
            },
            {
              targets: [5],
              type: 'num',
              orderSequence: ['asc', 'desc']
            }
          ],
          responsive: true
        });
      }

      // Inicializar DataTable para vencimientos ejecutados
      var tableEjecutados = null;
      if ($('#vencimientosEjecutadosTable').length) {
        tableEjecutados = $('#vencimientosEjecutadosTable').DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
          },
          order: [[5, 'desc']],
          pageLength: 10,
          dom: 'Bfrtip',
          buttons: [
            {
              extend: 'excel',
              text: '<i class="fas fa-file-excel me-2"></i>Exportar Ejecutados',
              className: 'btn btn-success',
              exportOptions: {
                columns: [0,1,2,3,4,5,6,7]
              }
            }
          ],
          columnDefs: [
            {
              targets: [8],
              orderable: false,
              searchable: false
            },
            {
              targets: [4, 5],
              type: 'num',
              orderSequence: ['asc', 'desc']
            }
          ],
          responsive: true
        });
      }

      // Filtro de rango de fechas para "Fecha de Vencimiento"
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var min = $('#filter_fecha_vencimiento_inicio').val();
        var max = $('#filter_fecha_vencimiento_fin').val();

        if (!min && !max) return true;

        var currentTable = settings.nTable.id;
        var columnIndex = (currentTable === 'vencimientosSinEjecutarTable') ? 5 : 4;

        var cell = $(settings.nTable).find('tbody tr').eq(dataIndex).find('td:eq(' + columnIndex + ')');
        var orderVal = cell.attr('data-order');
        var timestamp = orderVal ? parseInt(orderVal) * 1000 : 0;

        var minDate = min ? new Date(min).setHours(0, 0, 0, 0) : null;
        var maxDate = max ? new Date(max).setHours(23, 59, 59, 999) : null;

        if (minDate && !maxDate) {
          return timestamp >= minDate;
        }
        if (!minDate && maxDate) {
          return timestamp <= maxDate;
        }
        if (minDate && maxDate) {
          return timestamp >= minDate && timestamp <= maxDate;
        }
        return true;
      });

      // Actualizar tablas al cambiar los inputs de fecha
      $('#filter_fecha_vencimiento_inicio, #filter_fecha_vencimiento_fin').change(function() {
        if (tableSinEjecutar) tableSinEjecutar.draw();
        if (tableEjecutados) tableEjecutados.draw();
      });

      // Sincronizar filtro superior de cliente con las tablas
      $('#topFilter_cliente').on('keyup change', function() {
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        if (tableSinEjecutar) tableSinEjecutar.column(2).search(val ? val : '', true, false).draw();
        if (tableEjecutados) tableEjecutados.column(1).search(val ? val : '', true, false).draw();
      });

      // Checkbox "Seleccionar todos" para tabla sin ejecutar
      $('#selectAllSinEjecutar').change(function() {
        $('.email-checkbox-sin-ejecutar').prop('checked', $(this).prop('checked'));
      });
      $(document).on('change', '.email-checkbox-sin-ejecutar', function() {
        var allChecked = $('.email-checkbox-sin-ejecutar:checked').length === $('.email-checkbox-sin-ejecutar').length;
        $('#selectAllSinEjecutar').prop('checked', allChecked);
      });

      // Boton de reinicio de filtros
      $('#resetFilters').click(function() {
        $('#topFilter_cliente').val('');
        $('#filter_fecha_vencimiento_inicio').val('');
        $('#filter_fecha_vencimiento_fin').val('');
        if (tableSinEjecutar) {
          tableSinEjecutar.columns().search('').draw();
        }
        if (tableEjecutados) {
          tableEjecutados.columns().search('').draw();
        }
      });

      // Mostrar/ocultar indicador de carga
      $(document)
        .ajaxStart(function() {
          $('.loading').removeClass('d-none');
        })
        .ajaxStop(function() {
          $('.loading').addClass('d-none');
        });

      // Manejar filtro persistente de cliente
      function updateClientFilter() {
        var urlParams = new URLSearchParams(window.location.search);
        var clienteSeleccionado = urlParams.get('cliente_id');

        if (clienteSeleccionado) {
          // Actualizar URLs de botones de agregar y editar
          $('#btn-agregar').attr('href', function(i, href) {
            return href.split('?')[0] + '?cliente_id=' + encodeURIComponent(clienteSeleccionado);
          });
          $('.btn-editar').each(function() {
            $(this).attr('href', function(i, href) {
              return href.split('?')[0] + '?cliente_id=' + encodeURIComponent(clienteSeleccionado);
            });
          });
        }
      }

      // Verificar si hay un cliente en la URL al cargar la pagina
      function checkClienteFromURL() {
        var urlParams = new URLSearchParams(window.location.search);
        var clienteParam = urlParams.get('cliente_id');
        if (clienteParam) {
          var nombreCliente = '';
          $('#cliente_id option').each(function() {
            if ($(this).val() === clienteParam) {
              nombreCliente = $(this).text();
              return false;
            }
          });

          if (nombreCliente && nombreCliente !== '-- Busque y seleccione un cliente --') {
            $('#topFilter_cliente').val(nombreCliente);
            if (tableSinEjecutar) {
              tableSinEjecutar.column(2).search(nombreCliente, true, false).draw();
            }
            if (tableEjecutados) {
              tableEjecutados.column(1).search(nombreCliente, true, false).draw();
            }
          }
        }
      }

      // Ejecutar al cargar la pagina
      checkClienteFromURL();

      // Actualizar URLs cuando cambie el filtro superior de cliente
      $('#topFilter_cliente').on('change keyup', function() {
        updateClientFilter();
      });

      // Llamar updateClientFilter inicialmente
      updateClientFilter();
      <?php endif; ?>

      // Ejecutar updateClientFilter para todos los casos
      updateClientFilter();
    });
  </script>
</body>
</html>
