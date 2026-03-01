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
        .card-upload.drag-over {
            border-color: var(--gold-secondary);
            background: #fffdf5;
            transform: translateY(-3px);
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

        .step-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 25px;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: var(--gold-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .table-preview {
            font-size: 0.85rem;
        }

        .mapping-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .mapping-item .mapping-icon {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .mapping-success {
            color: #28a745;
        }

        .mapping-warning {
            color: #ffc107;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="<?= base_url('matriz-comunicacion') ?>">
                <i class="fas fa-project-diagram me-2"></i>Importar CSV
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('matriz-comunicacion/descargar-muestra') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-download me-1"></i>Descargar Muestra CSV
                </a>
                <a href="<?= base_url('matriz-comunicacion') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Matriz
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="page-header">
            <h2 class="mb-2"><i class="fas fa-file-csv me-2"></i><?= esc($titulo) ?></h2>
            <p class="mb-0 opacity-75">Suba un archivo CSV con protocolos de comunicacion para importarlos masivamente</p>
        </div>

        <!-- Paso 1: Subir archivo -->
        <div class="step-card" id="paso1">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number me-3">1</div>
                <h4 class="mb-0">Subir Archivo CSV</h4>
            </div>

            <div class="card-upload" id="dropZone" onclick="document.getElementById('archivoCSV').click()">
                <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--gold-primary)"></i>
                <h5>Arrastre su archivo aqui o haga clic para seleccionar</h5>
                <p class="text-muted mb-0">Formatos aceptados: .csv, .txt | Delimitadores: punto y coma (;), coma (,), tabulador, pipe (|)</p>
                <input type="file" id="archivoCSV" accept=".csv,.txt" style="display:none" onchange="previewArchivo()">
            </div>
            <div id="nombreArchivo" class="mt-2 text-center" style="display:none;"></div>
        </div>

        <!-- Paso 2: Preview -->
        <div class="step-card" id="paso2" style="display: none;">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number me-3">2</div>
                <h4 class="mb-0">Vista Previa</h4>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Mapeo de Columnas</h6>
                    <div id="mapeoColumnas"></div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Delimitador detectado:</strong> <span id="delimitadorDetectado"></span><br>
                        <strong>Total de filas:</strong> <span id="totalFilas"></span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-striped table-preview" id="tablaPreview">
                    <thead class="table-dark"></thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="cancelarImportacion()">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-gold btn-lg" onclick="procesarImportacion()">
                    <i class="fas fa-upload me-1"></i>Importar Todos los Registros
                </button>
            </div>
        </div>

        <!-- Paso 3: Resultados -->
        <div class="step-card" id="paso3" style="display: none;">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number me-3">3</div>
                <h4 class="mb-0">Resultados de Importacion</h4>
            </div>
            <div id="resultadoImportacion"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Drag & Drop
        const dropZone = document.getElementById('dropZone');

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                document.getElementById('archivoCSV').files = e.dataTransfer.files;
                previewArchivo();
            }
        });

        function previewArchivo() {
            const file = document.getElementById('archivoCSV').files[0];
            if (!file) return;

            $('#nombreArchivo').html(`<i class="fas fa-file-csv me-1"></i> <strong>${file.name}</strong> (${(file.size/1024).toFixed(1)} KB)`).show();

            let formData = new FormData();
            formData.append('archivo_csv', file);

            Swal.fire({
                title: 'Analizando archivo...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '<?= base_url('matriz-comunicacion/preview-csv') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        mostrarPreview(response);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Error al procesar el archivo', 'error');
                }
            });
        }

        const camposEsperados = {
            'categoria': 'Categoria',
            'situacion_evento': 'Situacion/Evento',
            'que_comunicar': 'Que Comunicar',
            'quien_comunica': 'Quien Comunica',
            'a_quien_comunicar': 'A Quien Comunicar',
            'mecanismo_canal': 'Mecanismo/Canal',
            'frecuencia_plazo': 'Frecuencia/Plazo',
            'registro_evidencia': 'Registro/Evidencia',
            'norma_aplicable': 'Norma Aplicable',
            'tipo': 'Tipo'
        };

        function mostrarPreview(data) {
            // Mapeo
            let mapeoHtml = '';
            for (let campo in camposEsperados) {
                let encontrado = data.mapeo.hasOwnProperty(campo);
                let icon = encontrado ?
                    '<i class="fas fa-check-circle mapping-icon mapping-success"></i>' :
                    '<i class="fas fa-exclamation-triangle mapping-icon mapping-warning"></i>';
                let status = encontrado ? `Columna ${data.mapeo[campo] + 1}` : 'No encontrada';
                mapeoHtml += `<div class="mapping-item">${icon}<strong>${camposEsperados[campo]}:</strong>&nbsp;${status}</div>`;
            }
            $('#mapeoColumnas').html(mapeoHtml);

            // Info
            let delNames = {';': 'Punto y coma (;)', ',': 'Coma (,)', '\t': 'Tabulador', '|': 'Pipe (|)'};
            $('#delimitadorDetectado').text(delNames[data.delimitador] || data.delimitador);
            $('#totalFilas').text(data.total_lineas);

            // Tabla preview
            let thead = '<tr>';
            for (let campo in camposEsperados) {
                thead += `<th>${camposEsperados[campo]}</th>`;
            }
            thead += '</tr>';
            $('#tablaPreview thead').html(thead);

            let tbody = '';
            data.preview.forEach(function(row) {
                tbody += '<tr>';
                for (let campo in camposEsperados) {
                    let val = row[campo] || '';
                    if (val.length > 60) val = val.substring(0, 60) + '...';
                    tbody += `<td>${val}</td>`;
                }
                tbody += '</tr>';
            });
            $('#tablaPreview tbody').html(tbody);

            $('#paso2').slideDown();
        }

        function procesarImportacion() {
            const file = document.getElementById('archivoCSV').files[0];
            if (!file) return;

            Swal.fire({
                title: 'Importando protocolos...',
                text: 'Esto puede tomar unos segundos',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            let formData = new FormData();
            formData.append('archivo_csv', file);

            $.ajax({
                url: '<?= base_url('matriz-comunicacion/procesar-csv') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();

                    let html = '';
                    if (response.success) {
                        html += `<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>
                            <strong>${response.insertados}</strong> protocolos importados correctamente</div>`;
                    } else {
                        html += `<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Error durante la importacion</div>`;
                    }

                    if (response.errores && response.errores.length > 0) {
                        html += `<div class="alert alert-warning"><strong>Errores (${response.errores.length}):</strong><ul>`;
                        response.errores.forEach(function(err) {
                            html += `<li>${err}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    html += `<div class="text-center mt-3">
                        <a href="<?= base_url('matriz-comunicacion') ?>" class="btn btn-gold">
                            <i class="fas fa-table me-1"></i>Ver Matriz de Comunicacion
                        </a>
                    </div>`;

                    $('#resultadoImportacion').html(html);
                    $('#paso3').slideDown();
                    $('#paso2').slideUp();
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Error al importar', 'error');
                }
            });
        }

        function cancelarImportacion() {
            $('#paso2').slideUp();
            $('#archivoCSV').val('');
            $('#nombreArchivo').hide();
        }
    </script>
</body>

</html>
