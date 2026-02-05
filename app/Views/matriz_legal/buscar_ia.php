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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .search-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 30px;
        }

        .search-input {
            font-size: 1.2rem;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .search-input:focus {
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px rgba(189, 151, 81, 0.2);
        }

        .btn-search {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .btn-search:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: white;
            transform: translateY(-2px);
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

        /* Preview vertical de norma */
        .preview-container {
            display: none;
            margin-top: 30px;
        }

        .preview-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .preview-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            color: white;
            padding: 20px;
        }

        .preview-body {
            padding: 0;
        }

        .preview-field {
            display: flex;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }

        .preview-field:hover {
            background: #f8f9fa;
        }

        .preview-field:last-child {
            border-bottom: none;
        }

        .preview-label {
            flex: 0 0 200px;
            padding: 15px 20px;
            background: #f8f9fa;
            font-weight: 600;
            color: var(--primary-dark);
            border-right: 3px solid var(--gold-primary);
            display: flex;
            align-items: flex-start;
        }

        .preview-label i {
            margin-right: 10px;
            color: var(--gold-primary);
            width: 20px;
        }

        .preview-value {
            flex: 1;
            padding: 15px 20px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .preview-value.editable {
            cursor: text;
        }

        .preview-value textarea,
        .preview-value input,
        .preview-value select {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px 12px;
        }

        .preview-value textarea {
            min-height: 80px;
            resize: vertical;
        }

        .preview-actions {
            padding: 20px;
            background: #f8f9fa;
            text-align: center;
            border-top: 2px solid #eee;
        }

        /* Robot animado */
        .robot-icon {
            font-size: 3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .loading-animation {
            display: none;
        }

        .loading-animation.active {
            display: block;
        }

        .loading-animation i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Sugerencias */
        .suggestions {
            margin-top: 20px;
        }

        .suggestion-chip {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            background: #f0f0f0;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .suggestion-chip:hover {
            background: var(--gold-primary);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="<?= base_url('matriz-legal') ?>">
                <i class="fas fa-robot me-2"></i><?= esc($titulo) ?>
            </a>
            <a href="<?= base_url('matriz-legal') ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver a Matriz Legal
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Header -->
        <div class="page-header text-center">
            <i class="fas fa-robot robot-icon mb-3"></i>
            <h2>Buscar Norma con Inteligencia Artificial</h2>
            <p class="mb-0">Escribe el nombre de una norma y la IA buscará toda la información por ti</p>
        </div>

        <!-- Búsqueda -->
        <div class="search-card">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="input-group mb-3">
                        <input type="text" id="consultaIA" class="form-control search-input"
                               placeholder="Ej: Resolución 0312 de 2019, Decreto 1072 de 2015, Ley 1562..."
                               autofocus>
                        <button class="btn btn-search" type="button" onclick="buscarConIA()">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>

                    <div class="suggestions text-center">
                        <small class="text-muted">Sugerencias:</small>
                        <div class="mt-2">
                            <span class="suggestion-chip" onclick="buscarSugerencia('Resolución 0312 de 2019')">Resolución 0312 de 2019</span>
                            <span class="suggestion-chip" onclick="buscarSugerencia('Decreto 1072 de 2015')">Decreto 1072 de 2015</span>
                            <span class="suggestion-chip" onclick="buscarSugerencia('Ley 1562 de 2012')">Ley 1562 de 2012</span>
                            <span class="suggestion-chip" onclick="buscarSugerencia('Resolución 2400 de 1979')">Resolución 2400 de 1979</span>
                            <span class="suggestion-chip" onclick="buscarSugerencia('Decreto 1295 de 1994')">Decreto 1295 de 1994</span>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div class="loading-animation text-center mt-4" id="loadingAnimation">
                        <i class="fas fa-cog fa-3x text-primary"></i>
                        <p class="mt-2">Buscando información de la norma...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview de resultados -->
        <div class="preview-container" id="previewContainer">
            <div class="preview-card">
                <div class="preview-header">
                    <h4 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>
                        <span id="previewTitulo">Información de la Norma</span>
                    </h4>
                    <small class="opacity-75">Revisa y edita la información antes de guardar</small>
                </div>

                <div class="preview-body">
                    <form id="formNormaIA">
                        <!-- Sector -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-industry"></i>Sector
                            </div>
                            <div class="preview-value">
                                <select name="sector" id="ia_sector" class="form-select">
                                    <?php foreach ($sectores as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Tema -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-bookmark"></i>Tema
                            </div>
                            <div class="preview-value">
                                <input type="text" name="tema" id="ia_tema" class="form-control" required>
                            </div>
                        </div>

                        <!-- Subtema -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-tag"></i>Subtema
                            </div>
                            <div class="preview-value">
                                <input type="text" name="subtema" id="ia_subtema" class="form-control">
                            </div>
                        </div>

                        <!-- Tipo de Norma -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-gavel"></i>Tipo de Norma
                            </div>
                            <div class="preview-value">
                                <select name="tipo_norma" id="ia_tipo_norma" class="form-select" required>
                                    <?php foreach ($tiposNorma as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Número -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-hashtag"></i>Número
                            </div>
                            <div class="preview-value">
                                <input type="text" name="id_norma_legal" id="ia_id_norma_legal" class="form-control" required>
                            </div>
                        </div>

                        <!-- Año -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-calendar"></i>Año
                            </div>
                            <div class="preview-value">
                                <input type="number" name="anio" id="ia_anio" class="form-control" required min="1900" max="2100">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-file-alt"></i>Descripción
                            </div>
                            <div class="preview-value">
                                <textarea name="descripcion_norma" id="ia_descripcion_norma" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Autoridad -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-building"></i>Autoridad
                            </div>
                            <div class="preview-value">
                                <input type="text" name="autoridad_emisora" id="ia_autoridad_emisora" class="form-control">
                            </div>
                        </div>

                        <!-- Referentes -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-globe"></i>Referentes
                            </div>
                            <div class="preview-value">
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="referente_nacional" id="ia_referente_nacional" class="form-check-input" value="x">
                                    <label class="form-check-label">Nacional</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="referente_internacional" id="ia_referente_internacional" class="form-check-input" value="x">
                                    <label class="form-check-label">Internacional</label>
                                </div>
                            </div>
                        </div>

                        <!-- Artículos Aplicables -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-list-ol"></i>Artículos Aplicables
                            </div>
                            <div class="preview-value">
                                <textarea name="articulos_aplicables" id="ia_articulos_aplicables" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Parámetros -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-cogs"></i>Parámetros
                            </div>
                            <div class="preview-value">
                                <textarea name="parametros" id="ia_parametros" rows="4"></textarea>
                            </div>
                        </div>

                        <!-- Notas Vigencia -->
                        <div class="preview-field">
                            <div class="preview-label">
                                <i class="fas fa-sticky-note"></i>Vigencia
                            </div>
                            <div class="preview-value">
                                <textarea name="notas_vigencia" id="ia_notas_vigencia" rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="preview-actions">
                    <button type="button" class="btn btn-secondary me-2" onclick="limpiarPreview()">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-gold btn-lg" onclick="guardarNormaIA()">
                        <i class="fas fa-plus-circle me-2"></i>Agregar a Matriz Legal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Enter para buscar
        $('#consultaIA').on('keypress', function(e) {
            if (e.which === 13) {
                buscarConIA();
            }
        });

        function buscarSugerencia(texto) {
            $('#consultaIA').val(texto);
            buscarConIA();
        }

        function buscarConIA() {
            let consulta = $('#consultaIA').val().trim();

            if (!consulta) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ingresa una consulta',
                    text: 'Escribe el nombre de la norma que deseas buscar'
                });
                return;
            }

            // Mostrar loading
            $('#loadingAnimation').addClass('active');
            $('#previewContainer').hide();

            $.ajax({
                url: '<?= base_url('matriz-legal/procesar-busqueda-ia') ?>',
                type: 'POST',
                data: { consulta: consulta },
                success: function(response) {
                    $('#loadingAnimation').removeClass('active');

                    if (response.success) {
                        mostrarPreview(response.norma);
                    } else {
                        // Extraer datos de la consulta y ofrecer ingreso manual
                        let datosExtraidos = extraerDatosDeConsulta(consulta);

                        Swal.fire({
                            icon: 'info',
                            title: 'Norma no encontrada en la base de conocimiento',
                            html: `<p>La IA no tiene información actualizada sobre esta norma.</p>
                                   <p><strong>¿Deseas ingresarla manualmente?</strong></p>
                                   <small class="text-muted">Se pre-llenarán los datos detectados: ${datosExtraidos.tipo_norma} ${datosExtraidos.id_norma_legal} de ${datosExtraidos.anio}</small>`,
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-edit me-1"></i> Ingresar Manualmente',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#bd9751'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                mostrarPreview(datosExtraidos);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    $('#loadingAnimation').removeClass('active');

                    // También ofrecer ingreso manual en caso de error
                    let datosExtraidos = extraerDatosDeConsulta(consulta);

                    Swal.fire({
                        icon: 'warning',
                        title: 'Error de conexión',
                        html: `<p>No se pudo conectar con la IA.</p>
                               <p><strong>¿Deseas ingresar la norma manualmente?</strong></p>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-edit me-1"></i> Ingresar Manualmente',
                        cancelButtonText: 'Reintentar',
                        confirmButtonColor: '#bd9751'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            mostrarPreview(datosExtraidos);
                        }
                    });
                }
            });
        }

        // Función para extraer datos de la consulta del usuario
        function extraerDatosDeConsulta(consulta) {
            let datos = {
                sector: 'General',
                tema: '',
                subtema: '',
                tipo_norma: '',
                id_norma_legal: '',
                anio: '',
                descripcion_norma: consulta,
                autoridad_emisora: '',
                referente_nacional: 'x',
                referente_internacional: ''
            };

            // Convertir a minúsculas para búsqueda
            let texto = consulta.toLowerCase();

            // Detectar tipo de norma
            if (texto.includes('ley')) {
                datos.tipo_norma = 'Ley';
            } else if (texto.includes('decreto ley') || texto.includes('decreto-ley')) {
                datos.tipo_norma = 'Decreto Ley';
            } else if (texto.includes('decreto')) {
                datos.tipo_norma = 'Decreto';
            } else if (texto.includes('resolución') || texto.includes('resolucion')) {
                datos.tipo_norma = 'Resolución';
            } else if (texto.includes('circular')) {
                datos.tipo_norma = 'Circular';
            } else if (texto.includes('acuerdo')) {
                datos.tipo_norma = 'Acuerdo';
            } else if (texto.includes('sentencia')) {
                datos.tipo_norma = 'Sentencia';
            }

            // Extraer número de norma (buscar patrones como "0312", "1072", "2354")
            let matchNumero = consulta.match(/\b(\d{1,5})\b/g);
            if (matchNumero) {
                // El primer número que no sea un año (4 dígitos comenzando con 19 o 20) es probablemente el número de norma
                for (let num of matchNumero) {
                    if (num.length <= 4 && !(num.length === 4 && (num.startsWith('19') || num.startsWith('20')))) {
                        datos.id_norma_legal = num;
                        break;
                    } else if (!datos.id_norma_legal && num.length <= 4) {
                        datos.id_norma_legal = num;
                    }
                }
            }

            // Extraer año (buscar 4 dígitos que empiecen con 19 o 20)
            let matchAnio = consulta.match(/\b(19\d{2}|20\d{2})\b/);
            if (matchAnio) {
                datos.anio = matchAnio[1];
            }

            // Si no encontramos número separado, intentar extraerlo del patrón "tipo XXXX de YYYY"
            if (!datos.id_norma_legal) {
                let matchPatron = consulta.match(/(?:ley|decreto|resoluci[oó]n|circular)\s+(\d+)/i);
                if (matchPatron) {
                    datos.id_norma_legal = matchPatron[1];
                }
            }

            // Detectar autoridad común
            if (texto.includes('ministerio de trabajo') || texto.includes('mintrabajo')) {
                datos.autoridad_emisora = 'Ministerio del Trabajo';
            } else if (texto.includes('ministerio de salud') || texto.includes('minsalud')) {
                datos.autoridad_emisora = 'Ministerio de Salud y Protección Social';
            }

            return datos;
        }

        function mostrarPreview(norma) {
            // Llenar formulario
            $('#ia_sector').val(norma.sector || 'General');
            $('#ia_tema').val(norma.tema || '');
            $('#ia_subtema').val(norma.subtema || '');
            $('#ia_tipo_norma').val(norma.tipo_norma || '');
            $('#ia_id_norma_legal').val(norma.id_norma_legal || '');
            $('#ia_anio').val(norma.anio || '');
            $('#ia_descripcion_norma').val(norma.descripcion_norma || '');
            $('#ia_autoridad_emisora').val(norma.autoridad_emisora || '');
            $('#ia_referente_nacional').prop('checked', norma.referente_nacional === 'x');
            $('#ia_referente_internacional').prop('checked', norma.referente_internacional === 'x');
            $('#ia_articulos_aplicables').val(norma.articulos_aplicables || '');
            $('#ia_parametros').val(norma.parametros || '');
            $('#ia_notas_vigencia').val(norma.notas_vigencia || '');

            // Actualizar título
            $('#previewTitulo').text(`${norma.tipo_norma} ${norma.id_norma_legal} de ${norma.anio}`);

            // Mostrar preview
            $('#previewContainer').fadeIn();

            // Scroll al preview
            $('html, body').animate({
                scrollTop: $('#previewContainer').offset().top - 100
            }, 500);
        }

        function limpiarPreview() {
            $('#formNormaIA')[0].reset();
            $('#previewContainer').hide();
        }

        function guardarNormaIA() {
            let formData = new FormData($('#formNormaIA')[0]);

            // Agregar valores de checkboxes
            formData.set('referente_nacional', $('#ia_referente_nacional').is(':checked') ? 'x' : '');
            formData.set('referente_internacional', $('#ia_referente_internacional').is(':checked') ? 'x' : '');

            // Validar campos requeridos
            if (!formData.get('tema') || !formData.get('tipo_norma') || !formData.get('id_norma_legal') || !formData.get('anio')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor completa los campos obligatorios: Tema, Tipo de Norma, Número y Año'
                });
                return;
            }

            Swal.fire({
                title: 'Guardando norma...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '<?= base_url('matriz-legal/guardar-desde-ia') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Norma Agregada',
                            text: response.message,
                            showCancelButton: true,
                            confirmButtonText: 'Ver Matriz Legal',
                            cancelButtonText: 'Buscar otra norma'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= base_url('matriz-legal') ?>';
                            } else {
                                limpiarPreview();
                                $('#consultaIA').val('').focus();
                            }
                        });
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
                        text: 'Error al guardar la norma'
                    });
                }
            });
        }
    </script>
</body>

</html>
