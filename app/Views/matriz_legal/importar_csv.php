<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo) ?> - Enterprisesst</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark), var(--gold-primary));
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .card-upload {
            border: 3px dashed var(--gold-primary);
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: white;
            transition: all 0.3s;
            cursor: pointer;
        }

        .card-upload:hover,
        .card-upload.dragover {
            border-color: var(--primary-dark);
            background: #f8f9fa;
        }

        .card-upload i {
            font-size: 4rem;
            color: var(--gold-primary);
            margin-bottom: 20px;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, var(--gold-secondary), var(--gold-primary));
            color: white;
        }

        .preview-table {
            max-height: 400px;
            overflow-y: auto;
        }

        .preview-table th {
            position: sticky;
            top: 0;
            background: var(--primary-dark);
            color: white;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }

        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .step.active .step-number {
            background: var(--gold-primary);
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .mapping-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .mapping-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .mapping-item:last-child {
            border-bottom: none;
        }

        .mapping-campo {
            flex: 1;
            font-weight: 600;
        }

        .mapping-columna {
            flex: 1;
            color: var(--gold-primary);
        }

        .mapping-check {
            color: #28a745;
        }

        .mapping-warning {
            color: #ffc107;
        }

        #resultados {
            display: none;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="<?= base_url('matriz-legal') ?>">
                <i class="fas fa-file-csv me-2"></i><?= esc($titulo) ?>
            </a>
            <a href="<?= base_url('matriz-legal') ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver a Matriz Legal
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Header -->
        <div class="page-header text-center">
            <h2><i class="fas fa-file-import me-2"></i>Importar Matriz Legal desde CSV</h2>
            <p class="mb-0">Carga masiva de normas desde archivo CSV o Excel exportado a CSV</p>
        </div>

        <!-- Pasos -->
        <div class="step-indicator">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <span>Subir archivo</span>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <span>Previsualizar</span>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <span>Importar</span>
            </div>
        </div>

        <!-- Sección 1: Upload -->
        <div id="seccionUpload">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card-upload" id="dropZone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h4>Arrastra tu archivo CSV aquí</h4>
                        <p class="text-muted">o haz clic para seleccionar</p>
                        <input type="file" id="archivoCSV" accept=".csv,.txt" style="display: none;">
                        <button type="button" class="btn btn-gold mt-3" onclick="$('#archivoCSV').click()">
                            <i class="fas fa-folder-open me-2"></i>Seleccionar Archivo
                        </button>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Sector por defecto para registros sin sector:</label>
                        <select id="sectorDefecto" class="form-select">
                            <?php foreach ($sectores as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $key === 'General' ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Formato esperado del CSV:</h6>
                        <p class="mb-2">El archivo debe contener las siguientes columnas (orden flexible):</p>
                        <code>TEMA; SUBTEMA; TIPO DE NORMA; ID NORMA LEGAL; AÑO; DESCRIPCIÓN; AUTORIDAD; REFERENTE NACIONAL; REFERENTE INTERNACIONAL; ARTÍCULOS APLICABLES; PARÁMETROS; NOTAS VIGENCIA</code>
                        <p class="mt-2 mb-0"><small>Delimitadores soportados: punto y coma (;), coma (,), tabulador</small></p>
                        <hr>
                        <a href="<?= base_url('matriz-legal/descargar-muestra') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-2"></i>Descargar CSV de Muestra
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 2: Preview -->
        <div id="seccionPreview" style="display: none;">
            <div class="mapping-section">
                <h5><i class="fas fa-columns me-2"></i>Mapeo de Columnas Detectado</h5>
                <div id="mapeoColumnas"></div>
            </div>

            <div class="card">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-table me-2"></i>Vista Previa (primeras 10 filas)
                    <span class="badge bg-warning text-dark ms-2" id="totalLineas"></span>
                </div>
                <div class="card-body p-0">
                    <div class="preview-table">
                        <table class="table table-sm table-striped mb-0" id="tablaPreview">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-secondary me-2" onclick="reiniciar()">
                    <i class="fas fa-redo me-1"></i>Seleccionar otro archivo
                </button>
                <button type="button" class="btn btn-gold btn-lg" onclick="importar()">
                    <i class="fas fa-upload me-2"></i>Importar Datos
                </button>
            </div>
        </div>

        <!-- Sección 3: Resultados -->
        <div id="resultados" class="text-center">
            <div class="card">
                <div class="card-body py-5">
                    <div id="resultadoIcono"></div>
                    <h3 id="resultadoTitulo" class="mt-3"></h3>
                    <p id="resultadoMensaje"></p>
                    <div id="resultadoErrores" class="text-start mt-3" style="display:none;">
                        <h6>Errores encontrados:</h6>
                        <ul id="listaErrores" class="text-danger small"></ul>
                    </div>
                    <div class="mt-4">
                        <a href="<?= base_url('matriz-legal') ?>" class="btn btn-gold">
                            <i class="fas fa-list me-1"></i>Ver Matriz Legal
                        </a>
                        <button type="button" class="btn btn-secondary ms-2" onclick="reiniciar()">
                            <i class="fas fa-upload me-1"></i>Importar otro archivo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let archivoSeleccionado = null;

        // Drag & Drop
        const dropZone = document.getElementById('dropZone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', function(e) {
            let files = e.dataTransfer.files;
            if (files.length) {
                handleFile(files[0]);
            }
        });

        dropZone.addEventListener('click', function() {
            document.getElementById('archivoCSV').click();
        });

        document.getElementById('archivoCSV').addEventListener('change', function(e) {
            if (this.files.length) {
                handleFile(this.files[0]);
            }
        });

        function handleFile(file) {
            if (!file.name.match(/\.(csv|txt)$/i)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo no válido',
                    text: 'Por favor selecciona un archivo CSV o TXT'
                });
                return;
            }

            archivoSeleccionado = file;
            previsualizar();
        }

        function previsualizar() {
            let formData = new FormData();
            formData.append('archivo_csv', archivoSeleccionado);

            Swal.fire({
                title: 'Analizando archivo...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '<?= base_url('matriz-legal/preview-csv') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        mostrarPreview(response);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar el archivo'
                    });
                }
            });
        }

        function mostrarPreview(data) {
            // Actualizar pasos
            $('#step1').removeClass('active').addClass('completed');
            $('#step2').addClass('active');

            // Mostrar sección preview
            $('#seccionUpload').hide();
            $('#seccionPreview').show();

            // Mapeo de columnas
            let mapeoHtml = '';
            const campos = {
                'tema': 'Tema',
                'subtema': 'Subtema',
                'tipo_norma': 'Tipo de Norma',
                'id_norma_legal': 'No. Norma',
                'anio': 'Año',
                'descripcion_norma': 'Descripción',
                'autoridad_emisora': 'Autoridad',
                'referente_nacional': 'Ref. Nacional',
                'referente_internacional': 'Ref. Internacional',
                'articulos_aplicables': 'Artículos',
                'parametros': 'Parámetros',
                'notas_vigencia': 'Notas Vigencia'
            };

            for (let campo in campos) {
                let columna = data.mapeo[campo];
                let encontrado = columna !== undefined;
                mapeoHtml += `
                    <div class="mapping-item">
                        <span class="mapping-campo">${campos[campo]}</span>
                        <span class="mapping-columna">${encontrado ? data.headers[columna] : '-'}</span>
                        <span class="${encontrado ? 'mapping-check' : 'mapping-warning'}">
                            <i class="fas fa-${encontrado ? 'check-circle' : 'exclamation-triangle'}"></i>
                        </span>
                    </div>
                `;
            }
            $('#mapeoColumnas').html(mapeoHtml);

            // Total líneas
            $('#totalLineas').text(data.total_lineas + ' registros detectados');

            // Tabla preview
            let thead = '<tr>';
            for (let campo in campos) {
                thead += `<th>${campos[campo]}</th>`;
            }
            thead += '</tr>';
            $('#tablaPreview thead').html(thead);

            let tbody = '';
            data.preview.forEach(function(row) {
                tbody += '<tr>';
                for (let campo in campos) {
                    let valor = row[campo] || '';
                    if (valor.length > 100) {
                        valor = valor.substring(0, 100) + '...';
                    }
                    tbody += `<td>${escapeHtml(valor)}</td>`;
                }
                tbody += '</tr>';
            });
            $('#tablaPreview tbody').html(tbody);
        }

        function importar() {
            let formData = new FormData();
            formData.append('archivo_csv', archivoSeleccionado);
            formData.append('sector_defecto', $('#sectorDefecto').val());

            Swal.fire({
                title: 'Importando datos...',
                html: 'Por favor espera...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '<?= base_url('matriz-legal/procesar-csv') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();

                    // Actualizar pasos
                    $('#step2').removeClass('active').addClass('completed');
                    $('#step3').addClass('active completed');

                    // Mostrar resultados
                    $('#seccionPreview').hide();
                    $('#resultados').show();

                    if (response.success && response.insertados > 0) {
                        $('#resultadoIcono').html('<i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>');
                        $('#resultadoTitulo').text('Importación Exitosa');
                        $('#resultadoMensaje').html(`Se importaron <strong>${response.insertados}</strong> normas correctamente.`);
                    } else if (response.insertados === 0) {
                        $('#resultadoIcono').html('<i class="fas fa-exclamation-circle text-warning" style="font-size: 5rem;"></i>');
                        $('#resultadoTitulo').text('Sin registros importados');
                        $('#resultadoMensaje').text('No se importaron registros. Verifica el formato del archivo.');
                    } else {
                        $('#resultadoIcono').html('<i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>');
                        $('#resultadoTitulo').text('Error en la importación');
                        $('#resultadoMensaje').text(response.message);
                    }

                    if (response.errores && response.errores.length > 0) {
                        $('#resultadoErrores').show();
                        let erroresHtml = '';
                        response.errores.forEach(function(err) {
                            erroresHtml += `<li>${escapeHtml(err)}</li>`;
                        });
                        $('#listaErrores').html(erroresHtml);
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al importar los datos'
                    });
                }
            });
        }

        function reiniciar() {
            archivoSeleccionado = null;
            $('#archivoCSV').val('');

            // Reiniciar pasos
            $('.step').removeClass('active completed');
            $('#step1').addClass('active');

            // Mostrar sección upload
            $('#seccionUpload').show();
            $('#seccionPreview').hide();
            $('#resultados').hide();
            $('#resultadoErrores').hide();
        }

        function escapeHtml(text) {
            if (!text) return '';
            let div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
