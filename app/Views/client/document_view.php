<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Documentos por Subtema - Enterprisesst</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

  <!-- DataTables Buttons CSS -->
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet" />

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <link
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.5.2/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />

  <style>
    /* 2) Sobrescribe variables o usa select2‑container para dar más color */
    .select2-container--bootstrap-5 .select2-selection--single {
      background-color: #fff3cd;
      /* fondo amarillo suave */
      border: 2px solid #ffc107;
      /* borde dorado más ancho */
      border-radius: 0.375rem;
      /* radio estilo Bootstrap */
      height: auto;
      padding: 0.5rem 1rem;
    }

    .select2-container--bootstrap-5 .select2-selection__placeholder,
    .select2-container--bootstrap-5 .select2-selection__rendered {
      color: rgb(80, 42, 167);
      /* texto oscuro para contraste */
      font-weight: 500;
    }

    .select2-container--bootstrap-5 .select2-selection--single:focus-within {
      border-color: rgb(41, 49, 122);
      /* naranja más vivo al enfocar */
      box-shadow: 0 0 0 .25rem rgba(253, 126, 20, .25);
    }

    /* tras tu tema de Select2 + Bootstrap‑5 */
    .select2-container--bootstrap-5 .select2-dropdown .select2-results__options {
      max-height: 100px;
      /* ajusta a lo que necesites */
      overflow-y: auto;
      /* añade scroll si es necesario */
      background-color: #fff;
      /* fondo blanco para dropdown */
    }

    /* 1) Que el contenedor Select2 (la caja donde escribes) quede por encima */
    .select2-container--bootstrap-5 {
      z-index: 1200;
    }

    /* 2) Que la lista desplegable también tenga z‑index alto + scroll */
    .select2-container--bootstrap-5 .select2-dropdown {
      z-index: 2000;
      /* mucho más que 1100 */
      max-height: 200px;
      /* altura máxima */
      overflow-y: auto;
      /* scroll vertical */
      margin-top: 5px;
      /* un pequeño espacio tras el input */
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background-color: #f9f9f9;
      color: #333;
    }

    .container {
      margin-top: 30px;
      max-width: 1200px;
    }

    .table-container {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      scroll-margin-top: 130px;
    }

    .table-container h2 {
      color: #333;
      font-weight: 600;
      font-size: 24px;
      margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_filter input {
      margin-left: 0.5em;
      display: inline-block;
      width: auto;
    }

    .empty-message {
      color: #333;
      font-size: 18px;
      text-align: center;
      padding: 20px;
    }

    .observaciones-cell {
      max-width: 40ch;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    @media (max-width: 768px) {
      .table-container {
        padding: 10px;
      }

      .table-container h2 {
        font-size: 20px;
      }

      .table th,
      .table td {
        font-size: 14px;
      }
    }
  </style>
</head>

<body>

  <!-- Navbar con logos y Select2 -->
  <nav
    style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1100; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo"
            style="height: 100px;" />
        </a>
      </div>
      <div style="display: flex; align-items: center; gap: 15px;">
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;" />
        </a>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid Logo"
            style="height: 100px;" />
        </a>
        <?php
        $sortedTopics = $topicsList;
        asort($sortedTopics);
        ?>
        <div style="width: 300px;">
          <select id="sectionsSelect" class="form-select">
            <option></option>
            <?php foreach ($sortedTopics as $key => $titulo): ?>
              <option value="<?= esc($key) ?>"><?= esc($titulo) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </nav>

  <!-- Espacio para navbar fijo -->
  <div style="height: 120px;"></div>

  <div class="container-fluid">
    <?php foreach ($topicsList as $key => $titulo): ?>
      <div class="table-container" id="<?= esc($key) ?>">
        <h2><?= esc($titulo) ?></h2>
        <?php $reportes = ${$key}; ?>
        <?php if (!empty($reportes)): ?>
          <table id="<?= esc($key) ?>Table"
            class="table table-hover table-striped dt-responsive nowrap"
            style="width:100%">
            <thead>
              <tr>
                <th>Título</th>
                <th>Enlace</th>
                <th>Estado</th>
                <th>Tipo de Reporte</th>
                <th>Detalle</th>
                <th>Observaciones</th>
                <th>Creado el</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportes as $reporte): ?>
                <tr>
                  <td><?= esc($reporte['titulo_reporte']) ?></td>
                  <td>
                    <a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary">
                      <i class="fas fa-file-alt me-1"></i> Ver
                    </a>
                  </td>
                  <td><?= esc($reporte['estado']) ?></td>
                  <td><?= esc($reporte['tipo_reporte']) ?></td>
                  <td><?= esc($reporte['detalle_reporte']) ?></td>
                  <td class="observaciones-cell"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="<?= esc($reporte['observaciones']) ?>">
                    <?= (strlen($reporte['observaciones']) > 40)
                      ? esc(substr($reporte['observaciones'], 0, 40)) . '...'
                      : esc($reporte['observaciones']) ?>
                  </td>
                  <td><?= esc($reporte['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="empty-message">Aún no hay reportes de <?= esc($titulo) ?> disponibles.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Select2 JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

  <script>
    $(document).ready(function() {
      // Inicializar tooltips
      $('[data-bs-toggle="tooltip"]').tooltip();

      // Iniciar DataTables
      var keys = <?= json_encode(array_keys($topicsList)) ?>;
      var dtConfig = {
        responsive: true,
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
        order: [
          [6, 'desc']
        ],
        paging: true,
        searching: true,
        lengthChange: true,
        pageLength: 5,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
      };
      keys.forEach(function(k) {
        $('#' + k + 'Table').DataTable(dtConfig);
      });

      // Matcher para substring en Select2
      function matchAnywhere(params, data) {
        if (!params.term || data.text.toLowerCase().includes(params.term.toLowerCase())) {
          return data;
        }
        return null;
      }

      // Inicializar Select2
      $('#sectionsSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ir a sección…',
        allowClear: true,
        matcher: matchAnywhere,
        width: '100%',
        dropdownParent: $(document.body)
      });

      // Al seleccionar, hacer scroll suave
      $('#sectionsSelect').on('select2:select', function(e) {
        var id = e.params.data.id;
        var target = $('#' + id);
        if (target.length) {
          $('html, body').animate({
            scrollTop: target.offset().top - 120
          }, 300);
        }
      });
    });
  </script>
</body>

</html>