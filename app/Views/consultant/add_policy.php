<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agregar Política</title>
  <!-- Bootstrap CSS -->
  <link
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <!-- DataTables CSS (por si en el futuro agregas tablas) -->
  <link
    href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"
    rel="stylesheet"
  >
  <!-- Select2 CSS + tema Bootstrap4 -->
  <link
    href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
    rel="stylesheet"
  >
  <link
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
    rel="stylesheet"
  >

  <style>
    body {
      background-color: #f8f9fa;
      color: #333;
    }

    .container {
      max-width: 800px;
      margin-top: 20px;
    }

    h1, h2 {
      color: #495057;
    }

    .form-label {
      font-weight: bold;
    }

    .btn-primary,
    .btn-secondary {
      margin-top: 10px;
    }

    .dataTable-filter {
      display: none;
    }

    /* Mejoras para Select2 */
    .select2-container--bootstrap4 .select2-selection--single {
      height: calc(1.5em + 0.75rem + 2px) !important;
      padding: 0.375rem 0.75rem !important;
      font-size: 1rem !important;
      line-height: 1.5 !important;
      border: 1px solid #ced4da !important;
      border-radius: 0.375rem !important;
      background-color: #fff !important;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }

    .select2-container--bootstrap4 .select2-selection--single:focus {
      border-color: #80bdff !important;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
      color: #495057 !important;
      padding-left: 0 !important;
      padding-right: 20px !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
      color: #6c757d !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
      height: calc(1.5em + 0.75rem) !important;
      position: absolute !important;
      top: 1px !important;
      right: 1px !important;
      width: 20px !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
      border-color: #999 transparent transparent transparent !important;
      border-style: solid !important;
      border-width: 5px 4px 0 4px !important;
      height: 0 !important;
      left: 50% !important;
      margin-left: -4px !important;
      margin-top: -2px !important;
      position: absolute !important;
      top: 50% !important;
      width: 0 !important;
    }

    .select2-dropdown {
      border: 1px solid #ced4da !important;
      border-radius: 0.375rem !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    .select2-container--bootstrap4 .select2-results__option {
      padding: 0.5rem 0.75rem !important;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
      background-color: #007bff !important;
      color: #fff !important;
    }

    /* Validación visual */
    .is-invalid {
      border-color: #dc3545 !important;
    }

    .select2-container .select2-selection--single.is-invalid {
      border-color: #dc3545 !important;
    }

    .invalid-feedback {
      display: block;
      width: 100%;
      margin-top: 0.25rem;
      font-size: 0.875em;
      color: #dc3545;
    }

    /* Mejora para el textarea */
    #policy_content {
      min-height: 120px;
      resize: vertical;
    }

    /* Estilos para el formulario */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-control:focus {
      border-color: #80bdff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
  </style>
</head>

<body>

  <nav style="background-color: white; position: fixed; top:0; width:100%; z-index:1000; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
    <div class="d-flex justify-content-between align-items-center px-4">
      <a href="https://dashboard.cycloidtalent.com/login">
        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" height="100">
      </a>
      <a href="https://cycloidtalent.com/index.php/consultoria-sst">
        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" height="100">
      </a>
      <a href="https://cycloidtalent.com/">
        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" height="100">
      </a>
      <div class="text-center">
        <h2 class="h6 mb-1">Ir a Dashboard</h2>
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Dashboard</a>
      </div>
    </div>
  </nav>

  <div style="height: 160px;"></div> <!-- espacio para el nav fijo -->

  <div class="container">
    <h1 class="mb-4">Agregar Nuevo Texto a Documento</h1>

    <form id="policyForm" action="<?= base_url('/addPolicyPost') ?>" method="post">
      <div class="form-group">
        <label for="client_id" class="form-label">Cliente:</label>
        <select
          id="client_id"
          name="client_id"
          class="select2bs4 form-control"
          data-placeholder="Seleccione un cliente"
          style="width:100%;"
          required
        >
          <option value=""></option>
          <?php foreach ($clients as $client): ?>
            <option value="<?= esc($client['id_cliente']) ?>">
              <?= esc($client['nombre_cliente']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback" id="client_id_error"></div>
      </div>

      <div class="form-group">
        <label for="policy_type_id" class="form-label">Tipo de Documento:</label>
        <select
          id="policy_type_id"
          name="policy_type_id"
          class="select2bs4 form-control"
          data-placeholder="Seleccione un tipo de documento"
          style="width:100%;"
          required
        >
          <option value=""></option>
          <?php foreach ($policyTypes as $type): ?>
            <option value="<?= esc($type['id']) ?>">
              <?= esc($type['type_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback" id="policy_type_id_error"></div>
      </div>

      <div class="form-group">
        <label for="policy_content" class="form-label">Contenido:</label>
        <textarea
          id="policy_content"
          name="policy_content"
          class="form-control"
          rows="5"
          placeholder="Ingrese el contenido del documento..."
        ></textarea>
        <div class="invalid-feedback" id="policy_content_error"></div>
        <small class="form-text text-muted">Campo opcional.</small>
      </div>

      <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-plus-circle mr-2"></i>Agregar Texto
      </button>
    </form>
  </div>

  <footer class="mt-5 py-3 text-center" style="background: #fff; border-top:1px solid #B0BEC5; color:#3A3F51;">
    <strong>Cycloid Talent SAS</strong> | Todos los derechos reservados © 2024 | NIT: 901.653.912
    <br>
    Sitio: <a href="https://cycloidtalent.com/" target="_blank" class="text-primary">cycloidtalent.com</a>
    <div class="mt-2">
      <a href="https://www.facebook.com/CycloidTalent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" height="24"></a>
      <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" height="24"></a>
      <a href="https://www.instagram.com/cycloid_talent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" height="24"></a>
      <a href="https://www.tiktok.com/@cycloid_talent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" height="24"></a>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <script>
    $(function() {
      // DataTables (por si lo necesitas luego)
      $('table').DataTable();

      // Inicializar cada Select2 leyendo su propio placeholder
      $('.select2bs4').each(function() {
        var $sel = $(this);
        $sel.select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: $sel.data('placeholder'),
          allowClear: false
        });
      });

      // Validación del formulario
      $('#policyForm').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Limpiar errores previos
        $('.form-control, .select2-selection').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Validar cliente
        const client_id = $('#client_id').val();
        if (!client_id || client_id === '') {
          $('#client_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
          $('#client_id_error').text('Por favor seleccione un cliente.');
          isValid = false;
        }
        
        // Validar tipo de documento
        const policy_type_id = $('#policy_type_id').val();
        if (!policy_type_id || policy_type_id === '') {
          $('#policy_type_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
          $('#policy_type_id_error').text('Por favor seleccione un tipo de documento.');
          isValid = false;
        }
        
        // Validar contenido (opcional, ya no es requerido)
        const policy_content = $('#policy_content').val().trim();
        // Campo opcional - no validamos
        
        // Si todo es válido, enviar el formulario
        if (isValid) {
          this.submit();
        } else {
          // Scroll al primer error
          $('html, body').animate({
            scrollTop: $('.is-invalid').first().offset().top - 100
          }, 500);
        }
      });

      // Limpiar errores cuando el usuario empiece a corregir
      $('#client_id').on('change', function() {
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        $('#client_id_error').text('');
      });

      $('#policy_type_id').on('change', function() {
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        $('#policy_type_id_error').text('');
      });

      $('#policy_content').on('input', function() {
        $(this).removeClass('is-invalid');
        $('#policy_content_error').text('');
      });
    });
  </script>
</body>
</html>