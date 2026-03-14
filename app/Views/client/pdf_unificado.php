<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Unificado - <?= esc($client['nombre_cliente']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1c2437;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }

        .dimension-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-planear { background: #e3f2fd; color: #1565c0; }
        .badge-hacer { background: #e8f5e9; color: #2e7d32; }
        .badge-verificar { background: #fff3e0; color: #ef6c00; }
        .badge-actuar { background: #fce4ec; color: #c62828; }

        .doc-item {
            padding: 0.6rem 1rem;
            border-left: 3px solid #dee2e6;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            background: #fafbfc;
            border-radius: 0 6px 6px 0;
        }

        .doc-item-planear { border-left-color: #1565c0; }
        .doc-item-hacer { border-left-color: #2e7d32; }
        .doc-item-verificar { border-left-color: #ef6c00; }
        .doc-item-actuar { border-left-color: #c62828; }

        .btn-generate {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
            transition: all 0.3s ease;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
            color: white;
        }

        .btn-generate:disabled {
            opacity: 0.7;
            transform: none;
            cursor: not-allowed;
        }

        .progress-container {
            display: none;
            margin-top: 1.5rem;
        }

        .progress {
            height: 24px;
            border-radius: 12px;
        }

        .progress-bar {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            transition: width 0.5s ease;
            border-radius: 12px;
            font-weight: 600;
        }

        .header-logo {
            max-height: 60px;
        }

        .status-msg {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="main-card mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <?php if (!empty($client['logo'])): ?>
                        <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo" class="header-logo">
                    <?php endif; ?>
                </div>
                <div class="col">
                    <h3 class="mb-1"><i class="fas fa-file-pdf text-danger me-2"></i> PDF Unificado SG-SST</h3>
                    <p class="text-muted mb-0">
                        <strong><?= esc($client['nombre_cliente']) ?></strong>
                        <?php if (!empty($consultant)): ?>
                            &mdash; Consultor: <?= esc($consultant['nombre_consultor']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-auto">
                    <span class="badge bg-secondary fs-6"><?= $totalDocs ?> documentos</span>
                </div>
            </div>
        </div>

        <!-- Documentos agrupados por dimensión PHVA -->
        <div class="main-card mb-4">
            <h5 class="mb-3"><i class="fas fa-list-check me-2"></i> Documentos incluidos en el PDF</h5>

            <?php foreach ($groupedDocs as $dimension => $docs): ?>
                <?php $dimLower = strtolower($dimension); ?>
                <div class="mb-3">
                    <span class="dimension-badge badge-<?= $dimLower ?>">
                        <i class="fas fa-<?= $dimLower === 'planear' ? 'clipboard-list' : ($dimLower === 'hacer' ? 'cogs' : ($dimLower === 'verificar' ? 'check-double' : 'sync-alt')) ?> me-1"></i>
                        <?= $dimension ?>
                    </span>
                    <div class="mt-2">
                        <?php foreach ($docs as $idAcceso => $doc): ?>
                            <div class="doc-item doc-item-<?= $dimLower ?>">
                                <i class="fas fa-file-alt text-muted me-2"></i>
                                <?= esc($doc['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($firstContractDate): ?>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-calendar me-2"></i>
                    <?php
                    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');
                    ?>
                    Fecha base del contrato: <strong><?= strftime('%d de %B de %Y', strtotime($firstContractDate)) ?></strong>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>PENDIENTE DE CONTRATO</strong> — La fecha de los documentos se mostrará como la fecha de creación en BD.
                </div>
            <?php endif; ?>
        </div>

        <!-- Botón de generación -->
        <div class="main-card text-center">
            <button type="button" id="btnGenerar" class="btn btn-generate">
                <i class="fas fa-file-pdf me-2"></i> Generar y Descargar PDF Unificado
            </button>

            <div class="progress-container" id="progressContainer">
                <div class="progress">
                    <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%">0%</div>
                </div>
                <div class="status-msg text-muted" id="statusMsg">
                    <i class="fas fa-spinner fa-spin me-1"></i> Generando documentos, por favor espere...
                </div>
            </div>

            <div id="successMsg" style="display:none;" class="mt-3">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    PDF generado exitosamente.
                </div>
            </div>

            <div id="errorMsg" style="display:none;" class="mt-3">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorText">Error al generar el PDF.</span>
                </div>
            </div>
        </div>

        <!-- Volver al dashboard -->
        <div class="text-center mt-3">
            <a href="<?= base_url('/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btnGenerar').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Generando...');

                $('#progressContainer').slideDown();
                $('#successMsg, #errorMsg').hide();

                // Progreso simulado
                let progress = 0;
                const progressInterval = setInterval(function() {
                    if (progress < 90) {
                        progress += Math.random() * 4 + 1;
                        if (progress > 90) progress = 90;
                        $('#progressBar').css('width', Math.round(progress) + '%').text(Math.round(progress) + '%');
                    }
                }, 1000);

                // Fetch real con blob
                const formData = new FormData();
                formData.append('id_cliente', '<?= $client['id_cliente'] ?>');
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                fetch('<?= base_url('/generarPdfUnificado') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    if (!response.ok) {
                        return response.text().then(function(text) { throw new Error(text); });
                    }
                    return response.blob();
                })
                .then(function(blob) {
                    clearInterval(progressInterval);
                    $('#progressBar').css('width', '100%').text('100%');
                    $('#statusMsg').html('<i class="fas fa-check-circle me-1 text-success"></i> Proceso completado.');
                    $('#successMsg').fadeIn();

                    // Disparar descarga
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'SG-SST_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']) ?>_<?= date('Y-m-d') ?>.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();

                    btn.prop('disabled', false);
                    btn.html('<i class="fas fa-file-pdf me-2"></i> Generar y Descargar PDF Unificado');
                })
                .catch(function(err) {
                    clearInterval(progressInterval);
                    $('#progressBar').css('width', '100%').addClass('bg-danger').text('Error');
                    $('#statusMsg').html('<i class="fas fa-times-circle me-1 text-danger"></i> Error en la generación.');
                    $('#errorText').text('Error: ' + err.message);
                    $('#errorMsg').fadeIn();

                    btn.prop('disabled', false);
                    btn.html('<i class="fas fa-file-pdf me-2"></i> Generar y Descargar PDF Unificado');
                });
            });
        });
    </script>
</body>

</html>
