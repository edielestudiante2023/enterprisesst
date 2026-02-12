<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de Reportes</title>
  <!-- Bootstrap CSS, DataTables y DataTables Buttons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet" />
  <!-- Select2 CSS para el select con input text -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <!-- Iconos Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    /* Variables CSS para consistencia */
    :root {
      --primary-color: #007bff;
      --primary-dark: #0056b3;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --light-bg: #f8f9fa;
      --border-radius: 8px;
      --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Estilos generales mejorados */
    .container-fluid {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: var(--border-radius);
      padding: 30px;
      margin-top: 20px;
      box-shadow: var(--box-shadow);
    }

    h2, h3 {
      color: var(--primary-dark);
      font-weight: 600;
      margin-bottom: 25px;
    }

    /* Estilos para filtros superiores */
    .row.g-3 {
      background: white;
      padding: 20px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      margin-bottom: 25px;
    }

    .form-label {
      color: var(--primary-dark);
      font-weight: 500;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      border: 2px solid #e9ecef;
      border-radius: var(--border-radius);
      padding: 10px 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    /* La tabla ocupa el ancho completo */
    table.dataTable {
      width: 100%;
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }

    /* Estilos generales para celdas de la tabla */
    table.dataTable thead th {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      font-weight: 600;
      border: none;
      padding: 15px 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
    }

    table.dataTable tbody td {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
      padding: 12px 8px;
      border-bottom: 1px solid #e9ecef;
    }

    table.dataTable tbody tr:hover {
      background-color: rgba(0, 123, 255, 0.05);
    }

    table.dataTable tfoot th {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
    }

    /* Clases para columnas sin truncamiento */
    td.title-col,
    td.tipodoc-col,
    td.tiporeporte-col {
      white-space: normal;
      overflow: visible;
      text-overflow: clip;
    }

    /* Columna Observaciones (se trunca a 40 caracteres) */
    td.observaciones-col {
      max-width: 40ch;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Columna Enlace: muestra solo un icono */
    td.enlace-col {
      text-align: center;
    }

    /* Estilo para los filtros en el tfoot */
    tfoot th {
      padding: 12px 10px;
      background: linear-gradient(135deg, var(--light-bg), #dee2e6);
      border-top: 2px solid var(--primary-color);
    }

    /* Alinear la búsqueda a la izquierda y mejorar visibilidad */
    div.dataTables_filter {
      text-align: left !important;
      margin-bottom: 20px;
      padding: 15px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    div.dataTables_filter label {
      color: white !important;
      font-weight: bold;
      font-size: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    div.dataTables_filter input {
      margin-left: 10px !important;
      padding: 8px 15px !important;
      border: 2px solid #ffffff !important;
      border-radius: 25px !important;
      font-size: 14px !important;
      width: 300px !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
      transition: all 0.3s ease !important;
    }

    div.dataTables_filter input:focus {
      outline: none !important;
      border-color: #ffc107 !important;
      box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3) !important;
      transform: scale(1.02) !important;
    }

    /* Botones armonizados */
    .btn {
      border-radius: var(--border-radius);
      font-weight: 500;
      padding: 8px 16px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--success-color), #1e7e34);
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    }

    .btn-warning {
      background: linear-gradient(135deg, var(--warning-color), #d39e00);
      color: #212529;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .btn-warning:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    }

    .btn-danger {
      background: linear-gradient(135deg, var(--danger-color), #bd2130);
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    }

    /* Botones de DataTables */
    .dt-buttons {
      margin-bottom: 20px;
    }

    .dt-buttons .btn {
      margin-right: 8px;
      border-radius: var(--border-radius);
    }

    /* Icono de detalles: cursor pointer */
    .details-control {
      cursor: pointer;
      margin-right: 5px;
      color: var(--primary-color);
      font-size: 18px;
      transition: all 0.3s ease;
    }

    .details-control:hover {
      color: var(--primary-dark);
      transform: scale(1.1);
    }

    /* Alertas mejoradas */
    .alert {
      border-radius: var(--border-radius);
      border: none;
      box-shadow: var(--box-shadow);
    }

    /* Estilos para tarjetas clickeables */
    .card-clickable {
      cursor: pointer;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .card-clickable:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card-clickable.active {
      border: 3px solid #ffeb3b !important;
      box-shadow: 0 0 25px rgba(255, 235, 59, 0.8), 0 0 10px rgba(255, 255, 255, 0.5) !important;
      transform: scale(1.08) !important;
      position: relative;
    }

    .card-clickable.active::after {
      content: '✓';
      position: absolute;
      top: 5px;
      right: 5px;
      background: #ffeb3b;
      color: #000;
      width: 25px;
      height: 25px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 16px;
    }

    .card-year {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 10px;
      min-height: 80px;
    }

    .card-month {
      min-height: 70px;
    }

    .card-status {
      min-height: 90px;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #4e73df;
      border-left: 4px solid #4e73df;
      padding-left: 10px;
      margin: 20px 0 15px 0;
    }
  </style>
</head>

<body class="bg-light">
  <!-- Navbar (sin cambios en estructura) -->
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
      <!-- Logo izquierdo -->
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
        </a>
      </div>
      <!-- Logo centro -->
      <div>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
        </a>
      </div>
      <!-- Logo derecho -->
      <div>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
        </a>
      </div>
    </div>
    <!-- Fila de botones -->
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
      <!-- Botón izquierdo -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mt-1">Ir a DashBoard</a>
      </div>
      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addReport') ?>" class="btn btn-success btn-sm mt-1">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <!-- Espaciado para evitar que el contenido quede oculto por el navbar -->
  <div style="height: 200px;"></div>

  <!-- Contenedor fluid -->
  <div class="container-fluid my-4">
    <!-- Encabezado con título y filtros -->
    <div class="mb-4">
      <h2 class="mb-3">Lista de Reportes</h2>

      <!-- Mensaje informativo -->
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>Filtros Dinámicos:</strong> Las tarjetas de año y mes son interactivas.
        Haz clic sobre ellas para filtrar la tabla instantáneamente. Puedes combinar múltiples filtros.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <!-- Sección de Filtros por Año -->
      <div class="d-flex justify-content-between align-items-center">
        <div class="section-title mb-0">
          <i class="fas fa-calendar-alt"></i> Filtrar por Año
        </div>
        <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
          <i class="fas fa-times"></i> Limpiar Filtros de Tarjetas
        </button>
      </div>
      <div class="row mb-4 mt-2" id="yearCards">
        <!-- Se generarán dinámicamente con JavaScript -->
      </div>

      <!-- Tarjetas mensuales (clickeables) -->
      <div class="section-title">
        <i class="fas fa-calendar-week"></i> Filtrar por Mes
      </div>
      <div class="row mb-4">
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="1">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Enero</h6>
              <p class="card-text text-center" id="countEnero">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="2">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Febrero</h6>
              <p class="card-text text-center" id="countFebrero">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="3">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Marzo</h6>
              <p class="card-text text-center" id="countMarzo">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="4">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Abril</h6>
              <p class="card-text text-center" id="countAbril">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="5">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Mayo</h6>
              <p class="card-text text-center" id="countMayo">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="6">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Junio</h6>
              <p class="card-text text-center" id="countJunio">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="7">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Julio</h6>
              <p class="card-text text-center" id="countJulio">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="8">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Agosto</h6>
              <p class="card-text text-center" id="countAgosto">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="9">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Sept.</h6>
              <p class="card-text text-center" id="countSeptiembre">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="10">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Oct.</h6>
              <p class="card-text text-center" id="countOctubre">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="11">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Nov.</h6>
              <p class="card-text text-center" id="countNoviembre">0</p>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-1">
          <div class="card text-white bg-info card-clickable card-month" data-month="12">
            <div class="card-body p-2">
              <h6 class="card-title text-center mb-0">Dic.</h6>
              <p class="card-text text-center" id="countDiciembre">0</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 align-items-end">
        <!-- Filtro por Cliente -->
        <div class="col-md-6">
          <label for="clientFilter" class="form-label">Filtrar por Cliente:</label>
          <select id="clientFilter" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($clients as $client) : ?>
              <option value="<?= htmlspecialchars($client['nombre_cliente']) ?>"><?= htmlspecialchars($client['nombre_cliente']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Filtro por Fecha Desde -->
        <div class="col-md-3">
          <label for="dateFrom" class="form-label">Fecha Desde:</label>
          <input type="date" id="dateFrom" class="form-control">
        </div>
        <!-- Filtro por Fecha Hasta -->
        <div class="col-md-3">
          <label for="dateTo" class="form-label">Fecha Hasta:</label>
          <input type="date" id="dateTo" class="form-control">
        </div>
      </div>
    </div>

    <h3 class="mb-3">Reportes</h3>

    <?php if (session()->get('msg')) : ?>
      <div class="alert alert-info" style="background: linear-gradient(135deg, #d1ecf1, #bee5eb); border-left: 4px solid var(--primary-color);">
        <i class="bi bi-info-circle"></i> <?= session()->get('msg') ?>
      </div>
    <?php endif; ?>

    <?php if (isset($reports) && !empty($reports)) : ?>
      <!-- Botón para restablecer filtros y exportar a Excel -->
      <div class="mb-3" style="background: white; padding: 15px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
        <button id="clearState" class="btn btn-danger">
          <i class="bi bi-arrow-clockwise"></i> Restablecer Filtros
        </button>
      </div>

      <table id="reportTable" class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Acciones</th>
            <th>Fecha de Creación</th>
            <th>Enlace</th>
            <th>ID</th>
            <th>Título del Reporte</th>
            <th>Tipo de Documento</th>
            <th>Tipo de Reporte</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>ID Cliente</th>
            <th>Nombre del Cliente</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <!-- Sin filtro -->
            <th>Acciones</th>
            <!-- Dropdown Filtering -->
            <th>Fecha de Creación</th>
            <th>Enlace</th>
            <!-- Sin filtro -->
            <th>ID</th>
            <!-- Text Input Filtering -->
            <th>Título del Reporte</th>
            <!-- Dropdown Filtering -->
            <th>Tipo de Documento</th>
            <th>Tipo de Reporte</th>
            <!-- Dropdown Filtering -->
            <th>Estado</th>
            <!-- Text Input Filtering -->
            <th>Observaciones</th>
            <!-- Sin filtro -->
            <th>ID Cliente</th>
            <!-- Text Input Filtering -->
            <th>Nombre del Cliente</th>
          </tr>
        </tfoot>
        <tbody>
          <?php foreach ($reports as $report) : ?>
            <tr>
              <!-- Columna Acciones: incluye icono para fila expandible y botones de Editar/Eliminar -->
              <td>
                <i class="bi bi-plus-square details-control"></i>
                <a href="<?= base_url('/editReport/' . $report['id_reporte']) ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar Reporte">Editar</a>
                <a href="<?= base_url('/deleteReport/' . $report['id_reporte']) ?>" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar Reporte" onclick="return confirm('¿Está seguro de eliminar este reporte?');">Eliminar</a>
              </td>
              <!-- Columna Fecha de Creación -->
              <td data-bs-toggle="tooltip" title="Fecha de Creación: <?= htmlspecialchars($report['created_at']) ?>">
                <?= htmlspecialchars($report['created_at']) ?>
              </td>
              <!-- Columna Enlace: se muestra un icono; se guarda el link en data-link -->
              <td class="enlace-col" data-link="<?= htmlspecialchars($report['enlace']) ?>" data-bs-toggle="tooltip" title="Abrir documento">
                <a href="<?= htmlspecialchars($report['enlace']) ?>" target="_blank">
                  <i class="bi bi-link-45deg"></i>
                </a>
              </td>
              <!-- Columna ID -->
              <td data-bs-toggle="tooltip" title="ID Reporte: <?= $report['id_reporte'] ?>">
                <?= $report['id_reporte'] ?>
              </td>
              <!-- Columna Título del Reporte -->
              <td data-bs-toggle="tooltip" title="Título: <?= htmlspecialchars($report['titulo_reporte']) ?>">
                <?= htmlspecialchars($report['titulo_reporte']) ?>
              </td>
              <!-- Columna Tipo de Documento -->
              <td data-bs-toggle="tooltip" title="Tipo de Documento: <?= htmlspecialchars($details[array_search($report['id_detailreport'], array_column($details, 'id_detailreport'))]['detail_report'] ?? 'N/A') ?>">
                <?= htmlspecialchars($details[array_search($report['id_detailreport'], array_column($details, 'id_detailreport'))]['detail_report'] ?? 'N/A') ?>
              </td>
              <!-- Columna Tipo de Reporte -->
              <td data-bs-toggle="tooltip" title="Tipo de Reporte: <?= htmlspecialchars($reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?? '') ?>">
                <?= htmlspecialchars($reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?? '') ?>
              </td>
              <!-- Columna Estado -->
              <td data-bs-toggle="tooltip" title="Estado: <?= htmlspecialchars($report['estado']) ?>">
                <?= htmlspecialchars($report['estado']) ?>
              </td>
              <!-- Columna Observaciones -->
              <td class="observaciones-col" data-bs-toggle="tooltip" title="Observaciones: <?= htmlspecialchars($report['observaciones']) ?>">
                <?= htmlspecialchars($report['observaciones']) ?>
              </td>
              <!-- Columna ID Cliente -->
              <td data-bs-toggle="tooltip" title="ID Cliente: <?= htmlspecialchars($report['id_cliente']) ?>">
                <?= htmlspecialchars($report['id_cliente']) ?>
              </td>
              <!-- Columna Nombre del Cliente -->
              <td data-bs-toggle="tooltip" title="Nombre del Cliente: <?= htmlspecialchars($clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?? '') ?>">
                <?= htmlspecialchars($clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?? '') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else : ?>
      <p class="text-muted">No hay reportes disponibles.</p>
    <?php endif; ?>

    <!-- Sección para Descargar Documentación por Contrato -->
    <div class="mt-5 mb-4">
      <div class="section-title">
        <i class="fas fa-download"></i> Descargar Documentación por Contrato
      </div>
      <div class="row g-3 align-items-end" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 20px; border-radius: 10px;">
        <div class="col-md-6">
          <label for="clientDownload" class="form-label text-white fw-bold">Seleccionar Cliente:</label>
          <select id="clientDownload" class="form-select">
            <option value="">-- Seleccione un cliente --</option>
            <?php foreach ($clients as $client) : ?>
              <option value="<?= $client['id_cliente'] ?>"><?= htmlspecialchars($client['nombre_cliente']) ?> (NIT: <?= htmlspecialchars($client['nit_cliente']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="button" id="btnVerDocumentacion" class="btn btn-light w-100" disabled>
            <i class="fas fa-eye"></i> Ver Documentación
          </button>
        </div>
        <div class="col-md-3">
          <button type="button" id="btnDescargarZip" class="btn btn-warning w-100" disabled>
            <i class="fas fa-file-archive"></i> Descargar ZIP
          </button>
        </div>
        <div class="col-12">
          <small class="text-white">
            <i class="fas fa-info-circle"></i> Seleccione un cliente para ver o descargar todos los documentos registrados durante el período de su último contrato.
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer (sin cambios) -->
  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
      <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
      <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
      <p style="margin: 5px 0;">NIT: 901.653.912</p>
      <p style="margin: 5px 0;">
        Sitio oficial:
        <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">
          https://cycloidtalent.com/
        </a>
      </p>
      <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
      <div style="display: flex; gap: 15px; justify-content: center;">
        <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
        </a>
        <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
        </a>
      </div>
    </div>
  </footer>

  <!-- Scripts: jQuery, Bootstrap, DataTables y Buttons -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <script>
    // Función para generar el contenido del row child details
    function format(d) {
      // d[0]: Acciones (omitido)
      // d[1]: Fecha de Creación
      // d[2]: Enlace
      // d[3]: ID
      // d[4]: Título del Reporte
      // d[5]: Tipo de Documento
      // d[6]: Tipo de Reporte
      // d[7]: Estado
      // d[8]: Observaciones
      // d[9]: ID Cliente
      // d[10]: Nombre del Cliente
      return '<div style="overflow:auto;">' +
        '<table style="width:100%;" class="table table-sm table-borderless">' +
        '<tr><td style="width:30%;"><strong>Fecha de Creación:</strong></td><td style="width:70%;">' + d[1] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Enlace:</strong></td><td style="width:70%;">' + d[2] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>ID:</strong></td><td style="width:70%;">' + d[3] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Título del Reporte:</strong></td><td style="width:70%;">' + d[4] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Tipo de Documento:</strong></td><td style="width:70%;">' + d[5] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Tipo de Reporte:</strong></td><td style="width:70%;">' + d[6] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Estado:</strong></td><td style="width:70%;">' + d[7] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Observaciones:</strong></td><td style="width:70%;">' + d[8] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>ID Cliente:</strong></td><td style="width:70%;">' + d[9] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Nombre del Cliente:</strong></td><td style="width:70%;">' + d[10] + '</td></tr>' +
        '</table>' +
        '</div>';
    }

    $(document).ready(function () {
      // Variables globales para filtros activos
      var activeYear = null;
      var activeMonth = null;

      const table = $('#reportTable').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        pageLength: 20,
        lengthMenu: [
          [20, 50, 100],
          [20, 50, 100]
        ],
        // Ordenación por defecto por la columna ID (índice 3)
        order: [
          [3, "desc"]
        ],
        // Se deshabilita el ordenamiento en las columnas "Acciones" (índice 0) y "Enlace" (índice 2)
        columnDefs: [
          {
            targets: [0, 2],
            orderable: false,
            searchable: false
          },
          {
            targets: 8, // "Observaciones" ahora es la columna índice 8
            className: "observaciones-col"
          }
        ],
        stateSave: true,
        stateSaveCallback: function (settings, data) {
          localStorage.setItem('DataTables_reportTable', JSON.stringify(data));
        },
        stateLoadCallback: function (settings) {
          return JSON.parse(localStorage.getItem('DataTables_reportTable'));
        },
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
            className: 'btn btn-success btn-sm',
            exportOptions: {
              // Excluir columnas "Acciones" (índice 0) y "Enlace" (índice 2)
              columns: function (idx, data, node) {
                return idx !== 0 && idx !== 2;
              }
            },
            title: 'Lista de Reportes',
            filename: 'Lista_de_Reportes',
            titleAttr: 'Exportar a Excel',
            customize: function (xlsx) {
              var sheet = xlsx.xl.worksheets['sheet1.xml'];
              $('row:first c', sheet).attr('s', '2');
            }
          },
          {
            extend: 'colvis',
            text: '<i class="bi bi-eye"></i> Visibilidad de Columnas',
            className: 'btn btn-secondary btn-sm',
            titleAttr: 'Mostrar u Ocultar Columnas'
          }
        ],
        initComplete: function () {
          var api = this.api();
          // Recorremos todas las columnas para colocar los filtros en el footer
          api.columns().every(function () {
            var column = this;
            var columnIdx = column.index();
            var $footerCell = $(column.footer()).empty();

            // Filtros de tipo input (texto) para: Título del Reporte (índice 4), Observaciones (índice 8) y Nombre del Cliente (índice 10)
            if ([4, 8, 10].indexOf(columnIdx) !== -1) {
              var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar...">')
                .appendTo($footerCell)
                .on('keyup change', function () {
                  if (column.search() !== this.value) {
                    column.search(this.value).draw();
                  }
                });
              var state = api.state.loaded();
              if (state && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                input.val(state.columns[columnIdx].search.search);
              }
            }
            // Filtros dropdown para: Fecha de Creación (índice 1), Tipo de Documento (índice 5), Tipo de Reporte (índice 6) y Estado (índice 7)
            else if ([1, 5, 6, 7].indexOf(columnIdx) !== -1) {
              var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                .appendTo($footerCell)
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? '^' + val + '$' : '', true, false).draw();
                });
              $footerCell.data('select', select);

              var state = api.state.loaded();
              if (state && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                var searchVal = state.columns[columnIdx].search.search.replace(/^\^|\$$/g, '');
                select.val(searchVal);
              }
            }
          });
        },
        // Actualizar opciones de filtros dropdown en cada draw
        drawCallback: function (settings) {
          var api = this.api();
          api.columns().every(function () {
            var column = this;
            var columnIdx = column.index();
            if ([1, 5, 6, 7].indexOf(columnIdx) !== -1) {
              var $footerCell = $(column.footer());
              var select = $footerCell.data('select');
              if (select) {
                var currentVal = select.val();
                select.find('option:not(:first)').remove();
                column.data({ filter: 'applied' }).unique().sort().each(function (d, j) {
                  var text = $('<div>').html(d).text();
                  select.append('<option value="' + text + '">' + text + '</option>');
                });
                select.val(currentVal);
              }
            }
          });
        }
      });

      // Generar tarjetas de años dinámicamente
      function generateYearCards() {
        if (!table) return;

        var yearCounts = {};

        // Contar reportes por año basado en created_at (columna 1)
        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var createdAt = data[1]; // Columna "Fecha de Creación"
          if (createdAt) {
            var parts = createdAt.split("-");
            if (parts.length >= 1) {
              var year = parts[0];
              yearCounts[year] = (yearCounts[year] || 0) + 1;
            }
          }
        });

        var yearArray = Object.keys(yearCounts).sort().reverse();
        var yearCardsHtml = '';

        yearArray.forEach(function(year) {
          var count = yearCounts[year];
          yearCardsHtml += `
            <div class="col-6 col-md-2">
              <div class="card text-white card-year card-clickable" data-year="${year}">
                <div class="card-body text-center p-3">
                  <h4 class="card-title mb-1">${year}</h4>
                  <p class="mb-0" style="font-size: 1.5rem; font-weight: bold;">${count}</p>
                  <small style="font-size: 0.75rem;">reportes</small>
                </div>
              </div>
            </div>
          `;
        });

        $('#yearCards').html(yearCardsHtml);
      }

      // Actualizar contadores de meses
      function updateMonthlyCounts() {
        if (!table) return;

        var monthlyCounts = {
          1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0,
          7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0
        };

        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var createdAt = data[1]; // Columna "Fecha de Creación"
          if (createdAt) {
            var parts = createdAt.split("-");
            if (parts.length >= 2) {
              var month = parseInt(parts[1], 10);
              if (month >= 1 && month <= 12) {
                monthlyCounts[month]++;
              }
            }
          }
        });

        $('#countEnero').text(monthlyCounts[1]);
        $('#countFebrero').text(monthlyCounts[2]);
        $('#countMarzo').text(monthlyCounts[3]);
        $('#countAbril').text(monthlyCounts[4]);
        $('#countMayo').text(monthlyCounts[5]);
        $('#countJunio').text(monthlyCounts[6]);
        $('#countJulio').text(monthlyCounts[7]);
        $('#countAgosto').text(monthlyCounts[8]);
        $('#countSeptiembre').text(monthlyCounts[9]);
        $('#countOctubre').text(monthlyCounts[10]);
        $('#countNoviembre').text(monthlyCounts[11]);
        $('#countDiciembre').text(monthlyCounts[12]);
      }

      // Función para aplicar filtros combinados
      function applyFilters() {
        if (!table) return;

        $.fn.dataTable.ext.search.pop(); // Limpiar filtros personalizados previos

        $.fn.dataTable.ext.search.push(
          function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'reportTable') {
              return true;
            }

            var createdAt = data[1] || ''; // Columna 1: Fecha de Creación

            // Filtro por año
            if (activeYear) {
              if (!createdAt.startsWith(activeYear)) {
                return false;
              }
            }

            // Filtro por mes
            if (activeMonth) {
              if (createdAt) {
                var parts = createdAt.split("-");
                if (parts.length >= 2) {
                  var month = parseInt(parts[1], 10);
                  if (month !== parseInt(activeMonth)) {
                    return false;
                  }
                } else {
                  return false;
                }
              } else {
                return false;
              }
            }

            return true;
          }
        );

        table.draw();
        generateYearCards();
        updateMonthlyCounts();
      }

      // Click en tarjetas de año
      $(document).on('click', '.card-year', function() {
        var year = $(this).data('year');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeYear = null;
        } else {
          $('.card-year').removeClass('active');
          $(this).addClass('active');
          activeYear = year;
        }

        applyFilters();
      });

      // Click en tarjetas de mes
      $(document).on('click', '.card-month', function() {
        var month = $(this).data('month');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeMonth = null;
        } else {
          $('.card-month').removeClass('active');
          $(this).addClass('active');
          activeMonth = month;
        }

        applyFilters();
      });

      // Botón para limpiar todos los filtros de tarjetas
      $('#btnClearCardFilters').on('click', function() {
        activeYear = null;
        activeMonth = null;

        $('.card-year').removeClass('active');
        $('.card-month').removeClass('active');

        $.fn.dataTable.ext.search.pop();

        if (table) {
          table.draw();
          generateYearCards();
          updateMonthlyCounts();
        }
      });

      // Actualizar contadores cuando la tabla se redibuja
      table.on('draw', function() {
        updateMonthlyCounts();
      });

      // Inicializar contadores y tarjetas de año
      updateMonthlyCounts();
      generateYearCards();

      // Evento para la fila expandible (row child details)
      $('#reportTable tbody').on('click', 'td .details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
          row.child.hide();
          $(this).removeClass('bi-dash-square').addClass('bi-plus-square');
        } else {
          row.child(format(row.data())).show();
          $(this).removeClass('bi-plus-square').addClass('bi-dash-square');
        }
      });

      // Inicializamos Select2 en el select de clientes
      $('#clientFilter').select2({
        placeholder: "Seleccione un cliente",
        allowClear: true,
        width: 'resolve'
      });

      // Evento para filtrar la tabla según el cliente seleccionado
      $('#clientFilter').on('change', function () {
        var selected = $(this).val();
        table.column(10).search(selected ? '^' + selected + '$' : '', true, false).draw();
        // Actualizar contadores después del filtrado
        setTimeout(function() {
          updateMonthlyCounts();
          generateYearCards();
        }, 100);
      });

      // Función para aplicar filtro de fechas
      function applyDateFilter() {
        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();
        
        $.fn.dataTable.ext.search.pop(); // Remover filtro anterior si existe
        
        if (dateFrom || dateTo) {
          $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
              if (settings.nTable.id !== 'reportTable') {
                return true;
              }
              
              var dateCreated = data[1]; // Columna Fecha de Creación (índice 1)
              
              // Convertir fecha de la tabla al formato YYYY-MM-DD para comparación
              var tableDateParts = dateCreated.split(' ')[0]; // Tomar solo la parte de fecha
              var tableDate = new Date(tableDateParts).getTime();
              
              var fromDate = dateFrom ? new Date(dateFrom).getTime() : null;
              var toDate = dateTo ? new Date(dateTo + ' 23:59:59').getTime() : null;
              
              if (fromDate && toDate) {
                return tableDate >= fromDate && tableDate <= toDate;
              } else if (fromDate) {
                return tableDate >= fromDate;
              } else if (toDate) {
                return tableDate <= toDate;
              }
              
              return true;
            }
          );
        }
        
        table.draw();
      }

      // Eventos para los campos de fecha
      $('#dateFrom, #dateTo').on('change', function() {
        applyDateFilter();
      });

      // Botón para restablecer estado y recargar la página
      $('#clearState').on('click', function () {
        localStorage.removeItem('DataTables_reportTable');
        table.state.clear();
        // Limpiar filtros de fecha
        $('#dateFrom, #dateTo').val('');
        $.fn.dataTable.ext.search.pop(); // Remover filtro de fechas
        location.reload();
      });

      // ============================================
      // Precargar cliente desde localStorage (Quick Access)
      // ============================================
      var storedClientId = localStorage.getItem('selectedClient');
      if (storedClientId) {
        // Mapeo de id_cliente a nombre_cliente
        var clientMap = {
          <?php foreach ($clients as $client): ?>
          '<?= $client['id_cliente'] ?>': '<?= addslashes($client['nombre_cliente']) ?>',
          <?php endforeach; ?>
        };

        var clientName = clientMap[storedClientId];
        if (clientName) {
          // Seleccionar el cliente en el filtro
          $('#clientFilter').val(clientName).trigger('change');
          console.log('Cliente precargado desde Quick Access: ' + clientName);
        }
      }

      // Inicializamos Select2 en el select de descarga de documentación
      $('#clientDownload').select2({
        placeholder: "-- Seleccione un cliente --",
        allowClear: true,
        width: '100%'
      });

      // Guardar en localStorage al cambiar filtro de cliente (Quick Access)
      $('#clientFilter').on('change.quickaccess', function () {
        var selected = $(this).val();
        if (selected) {
          var reverseClientMap = {
            <?php foreach ($clients as $client): ?>
            '<?= addslashes($client['nombre_cliente']) ?>': '<?= $client['id_cliente'] ?>',
            <?php endforeach; ?>
          };
          var clientId = reverseClientMap[selected];
          if (clientId) {
            localStorage.setItem('selectedClient', clientId);
          }
        } else {
          localStorage.removeItem('selectedClient');
        }
      });

      // ============================================
      // Descarga de Documentación por Contrato
      // ============================================

      // Habilitar/deshabilitar botones según selección de cliente
      $('#clientDownload').on('change', function() {
        var clientId = $(this).val();
        if (clientId) {
          $('#btnVerDocumentacion, #btnDescargarZip').prop('disabled', false);
        } else {
          $('#btnVerDocumentacion, #btnDescargarZip').prop('disabled', true);
        }
      });

      // Botón Ver Documentación - abre vista de selección de contrato/fechas
      $('#btnVerDocumentacion').on('click', function() {
        var clientId = $('#clientDownload').val();
        if (clientId) {
          window.open('<?= base_url("/contracts/seleccionar-documentacion/") ?>' + clientId, '_blank');
        }
      });

      // Botón Descargar ZIP - abre vista de selección de contrato/fechas
      $('#btnDescargarZip').on('click', function() {
        var clientId = $('#clientDownload').val();
        if (clientId) {
          window.open('<?= base_url("/contracts/seleccionar-documentacion/") ?>' + clientId, '_blank');
        }
      });
    });
  </script>

</body>

</html>
