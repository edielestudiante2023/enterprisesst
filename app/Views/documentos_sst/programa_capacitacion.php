<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .documento-contenido { padding: 20px !important; }
            body { font-size: 11pt; }
            .encabezado-formal { page-break-inside: avoid; }
        }

        /* Encabezado formal tipo tabla */
        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }

        .encabezado-logo {
            width: 150px;
            padding: 10px;
            text-align: center;
        }

        .encabezado-logo img {
            max-width: 130px;
            max-height: 70px;
            object-fit: contain;
        }

        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }

        .encabezado-titulo-central .sistema {
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            padding: 8px 15px;
            border-bottom: 1px solid #333;
        }

        .encabezado-titulo-central .nombre-doc {
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            padding: 8px 15px;
        }

        .encabezado-info {
            width: 170px;
            padding: 0;
        }

        .encabezado-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 3px 8px;
            font-size: 0.75rem;
        }

        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }

        .encabezado-info-table .label {
            font-weight: bold;
        }

        .documento-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .documento-titulo {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            color: #0d6efd;
            margin-bottom: 10px;
        }

        .documento-empresa {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .seccion {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .seccion-titulo {
            font-size: 1.1rem;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .seccion-contenido {
            text-align: justify;
            line-height: 1.7;
        }

        .seccion-contenido ul, .seccion-contenido ol {
            margin-left: 20px;
        }

        .tabla-cronograma {
            font-size: 0.85rem;
        }

        .tabla-cronograma th {
            background-color: #0d6efd;
            color: white;
            font-weight: 600;
        }

        .firma-section {
            margin-top: 40px;
            padding-top: 0;
        }

        .firma-box {
            text-align: center;
            padding: 20px;
        }

        .firma-linea {
            border-top: 1px solid #333;
            width: 250px;
            margin: 40px auto 10px auto;
        }

        /* Estilos para tabla de control de cambios y firmas */
        .tabla-control-cambios th,
        .tabla-firmas th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .tabla-firmas td {
            vertical-align: top;
            min-height: 120px;
        }

        @media print {
            .firma-section {
                page-break-inside: avoid;
            }
            .seccion:last-of-type {
                page-break-inside: avoid;
            }
        }

        .info-documento {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .badge-version {
            font-size: 0.8rem;
        }

        /* Estilos para panel de aprobacion */
        .panel-aprobacion {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }

        .panel-aprobacion .estado-badge {
            font-size: 1rem;
            padding: 8px 16px;
        }

        .historial-version {
            border-left: 3px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 15px;
        }

        .historial-version.vigente {
            border-left-color: #198754;
        }

        .historial-version.obsoleto {
            border-left-color: #6c757d;
            opacity: 0.7;
        }

        .btn-aprobar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
        }

        .btn-aprobar:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Barra de herramientas -->
    <div class="no-print bg-dark text-white py-2 sticky-top">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= base_url('generador-ia/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Programa de Capacitacion <?= $anio ?></span>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalHistorialVersiones">
                        <i class="bi bi-clock-history me-1"></i>Historial
                    </button>
                    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $documento['id_documento']) ?>" class="btn btn-danger btn-sm me-2">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('documentos-sst/exportar-word/' . $documento['id_documento']) ?>" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-file-earmark-word me-1"></i>Word
                    </a>
                    <?php if (in_array($documento['estado'] ?? '', ['aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-pen me-1"></i>Solicitar Firmas
                        </a>
                    <?php endif; ?>
                    <?php if (($documento['estado'] ?? '') === 'firmado'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-success btn-sm me-2">
                            <i class="bi bi-patch-check me-1"></i>Ver Firmas
                        </a>
                    <?php endif; ?>
                    <?php if (($documento['estado'] ?? '') === 'pendiente_firma'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-warning btn-sm me-2">
                            <i class="bi bi-clock-history me-1"></i>Estado Firmas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Documento -->
    <div class="container my-4">
        <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">

            <!-- Panel de Informacion del Documento (no imprimible) -->
            <div class="panel-aprobacion no-print">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-dark estado-badge"><?= esc($documento['codigo'] ?? 'Sin codigo') ?></span>
                            <span class="badge bg-light text-dark estado-badge">v<?= $documento['version'] ?>.0</span>
                            <?php
                            $estadoClass = match($documento['estado']) {
                                'aprobado' => 'bg-success',
                                'generado' => 'bg-info',
                                'borrador' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $estadoClass ?> estado-badge">
                                <i class="bi bi-<?= $documento['estado'] === 'aprobado' ? 'check-circle' : 'pencil' ?> me-1"></i>
                                <?= ucfirst($documento['estado']) ?>
                            </span>
                        </div>
                        <small class="opacity-75">
                            <?php if ($documento['estado'] === 'aprobado' && !empty($documento['fecha_aprobacion'])): ?>
                                Aprobado el <?= date('d/m/Y H:i', strtotime($documento['fecha_aprobacion'])) ?>
                            <?php else: ?>
                                Ultima modificacion: <?= date('d/m/Y H:i', strtotime($documento['updated_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <!-- Acciones disponibles en la tabla de documentos de la carpeta -->
                    </div>
                </div>
            </div>

            <!-- Info del documento simplificada (no imprimible) -->
            <div class="info-documento no-print">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Tipo de documento:</small>
                        <span class="fw-bold">Programa</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Periodo:</small>
                        <span class="fw-bold"><?= $anio ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Cliente:</small>
                        <span class="fw-bold"><?= esc($cliente['nombre_cliente']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Encabezado formal del documento -->
            <table class="encabezado-formal">
                <tr>
                    <!-- Logo del cliente -->
                    <td class="encabezado-logo" rowspan="2">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo <?= esc($cliente['nombre_cliente']) ?>">
                        <?php else: ?>
                            <div style="font-size: 0.7rem; color: #666;">
                                <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </td>
                    <!-- Titulo del sistema -->
                    <td class="encabezado-titulo-central">
                        <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                    </td>
                    <!-- Info del documento -->
                    <td class="encabezado-info" rowspan="2">
                        <table class="encabezado-info-table">
                            <tr>
                                <td class="label">Codigo:</td>
                                <td><?= esc($documento['codigo'] ?? 'PRC-SST-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Entra en Vigor:</td>
                                <td><?= date('j M Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <!-- Nombre del documento -->
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc"><?= esc(strtoupper($contenido['titulo'] ?? 'PROGRAMA DE CAPACITACION EN SST')) ?></div>
                    </td>
                </tr>
            </table>

            <!-- Secciones del documento -->
            <?php
            // Funcion para convertir tablas Markdown a HTML (definida una sola vez)
            if (!function_exists('convertirTablaMarkdownSST')) {
                function convertirTablaMarkdownSST($texto) {
                    $lineas = explode("\n", $texto);
                    $enTabla = false;
                    $resultado = [];
                    $tablaHtml = '';
                    $esEncabezado = true;

                    foreach ($lineas as $linea) {
                        $lineaTrim = trim($linea);

                        // Detectar linea de tabla (empieza con |)
                        if (preg_match('/^\|(.+)\|$/', $lineaTrim)) {
                            // Ignorar linea separadora (|---|---|)
                            if (preg_match('/^\|[\s\-\|]+\|$/', $lineaTrim)) {
                                $esEncabezado = false;
                                continue;
                            }

                            if (!$enTabla) {
                                $enTabla = true;
                                $tablaHtml = '<div class="table-responsive mt-3 mb-3"><table class="table table-bordered table-sm" style="font-size: 0.85rem;">';
                            }

                            // Extraer celdas
                            $celdas = array_map('trim', explode('|', trim($lineaTrim, '|')));
                            $tag = $esEncabezado ? 'th' : 'td';
                            $estilo = $esEncabezado ? ' style="background-color: #0d6efd; color: white; font-weight: 600;"' : '';

                            $tablaHtml .= '<tr>';
                            foreach ($celdas as $celda) {
                                $tablaHtml .= "<{$tag}{$estilo}>" . htmlspecialchars($celda) . "</{$tag}>";
                            }
                            $tablaHtml .= '</tr>';
                        } else {
                            // Si estabamos en una tabla, cerrarla
                            if ($enTabla) {
                                $tablaHtml .= '</table></div>';
                                $resultado[] = $tablaHtml;
                                $tablaHtml = '';
                                $enTabla = false;
                                $esEncabezado = true;
                            }
                            $resultado[] = $linea;
                        }
                    }

                    // Cerrar tabla si termino en una
                    if ($enTabla) {
                        $tablaHtml .= '</table></div>';
                        $resultado[] = $tablaHtml;
                    }

                    return implode("\n", $resultado);
                }
            }
            ?>
            <?php if (!empty($contenido['secciones'])): ?>
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="seccion">
                        <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
                        <div class="seccion-contenido">
                            <?php if (isset($seccion['tipo']) && $seccion['tipo'] === 'tabla'): ?>
                                <!-- Tabla del cronograma -->
                                <?php if (!empty($seccion['contenido']['filas'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered tabla-cronograma">
                                            <thead>
                                                <tr>
                                                    <?php foreach ($seccion['contenido']['encabezados'] as $enc): ?>
                                                        <th><?= esc($enc) ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($seccion['contenido']['filas'] as $fila): ?>
                                                    <tr>
                                                        <td><?= esc($fila['mes']) ?></td>
                                                        <td><?= esc($fila['tema']) ?></td>
                                                        <td><?= esc($fila['duracion']) ?></td>
                                                        <td><?= esc($fila['dirigido_a']) ?></td>
                                                        <td><?= esc($fila['responsable']) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $fila['estado'] === 'Programada' ? 'warning' : 'success' ?>">
                                                                <?= esc($fila['estado']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay capacitaciones programadas para este periodo.</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Contenido de texto -->
                                <?php
                                $contenidoHtml = $seccion['contenido'];

                                // Primero convertir tablas Markdown (antes de escapar)
                                $contenidoHtml = convertirTablaMarkdownSST($contenidoHtml);

                                // Escapar el resto del contenido (pero preservar las tablas HTML)
                                $partes = preg_split('/(<div class="table-responsive[^>]*>.*?<\/div>)/s', $contenidoHtml, -1, PREG_SPLIT_DELIM_CAPTURE);
                                $contenidoHtml = '';
                                foreach ($partes as $parte) {
                                    if (strpos($parte, '<div class="table-responsive') === 0) {
                                        $contenidoHtml .= $parte; // No escapar tablas
                                    } else {
                                        $parteEscapada = esc($parte);
                                        // **texto** -> <strong>texto</strong>
                                        $parteEscapada = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $parteEscapada);
                                        // *texto* -> <em>texto</em>
                                        $parteEscapada = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $parteEscapada);
                                        // - item -> lista
                                        $parteEscapada = preg_replace('/^- (.+)$/m', '<li>$1</li>', $parteEscapada);
                                        $parteEscapada = preg_replace('/(<li>.*<\/li>\s*)+/', '<ul>$0</ul>', $parteEscapada);
                                        // Saltos de linea
                                        $parteEscapada = nl2br($parteEscapada);
                                        $contenidoHtml .= $parteEscapada;
                                    }
                                }

                                echo $contenidoHtml;
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- SECCION: CONTROL DE CAMBIOS (Imprimible) -->
            <!-- ============================================== -->
            <div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-journal-text me-2"></i>CONTROL DE CAMBIOS
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 100px; text-align: center; font-weight: 600; color: #495057; border-top: none;">Version</th>
                            <th style="font-weight: 600; color: #495057; border-top: none;">Descripcion del Cambio</th>
                            <th style="width: 130px; text-align: center; font-weight: 600; color: #495057; border-top: none;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($versiones)): ?>
                            <?php foreach ($versiones as $idx => $ver): ?>
                            <tr style="<?= $idx % 2 === 0 ? 'background-color: #fff;' : 'background-color: #f8f9fa;' ?>">
                                <td style="text-align: center; vertical-align: middle;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">
                                        <?= esc($ver['version_texto']) ?>
                                    </span>
                                </td>
                                <td style="vertical-align: middle;"><?= esc($ver['descripcion_cambio']) ?></td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">
                                        1.0
                                    </span>
                                </td>
                                <td style="vertical-align: middle;">Elaboracion inicial del documento</td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================== -->
            <!-- SECCION: FIRMAS - Segun configuracion del contexto -->
            <!-- Si requiere_delegado_sst: Consultor + Delegado SST + Rep. Legal -->
            <!-- Si 7 estandares sin delegado: Solo Consultor + Rep. Legal -->
            <!-- Si 21+ estandares sin delegado: Consultor + Vigia/COPASST + Rep. Legal -->
            <!-- ============================================== -->
            <?php
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

            // Determinar si son solo 2 firmantes (7 estándares SIN delegado)
            $esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

            // Datos del Consultor desde tbl_consultor
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            $consultorCargo = 'Consultor SST';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';

            // Datos del Delegado SST (si aplica)
            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

            // Datos del Representante Legal - primero del contexto, luego del cliente
            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
            $repLegalCargo = 'Representante Legal';
            ?>

            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACIÓN
                </div>

                <?php
                // Firma del consultor
                $firmaConsultor = $consultor['firma_consultor'] ?? '';
                ?>

                <?php if ($esSoloDosFirmantes): ?>
                <!-- ========== 7 ESTANDARES SIN DELEGADO: Solo 2 firmantes ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 50%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-badge me-1"></i>Elaboró / Consultor SST
                            </th>
                            <th style="width: 50%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-check me-1"></i>Aprobó / Representante Legal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST -->
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Cargo:</strong>
                                    <span><?= esc($consultorCargo) ?></span>
                                </div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Licencia SST:</strong>
                                    <span><?= esc($consultorLicencia) ?></span>
                                </div>
                                <?php endif; ?>
                                <!-- Firma posicionada al fondo -->
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL -->
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Cargo:</strong>
                                    <span><?= esc($repLegalCargo) ?></span>
                                </div>
                                <!-- Firma posicionada al fondo -->
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php
                                    $firmaRepLegal2col = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal2col && !empty($firmaRepLegal2col['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal2col['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php else: ?>
                <!-- ========== 3 FIRMANTES: Delegado SST o Vigía/COPASST ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-badge me-1"></i>Elaboró
                            </th>
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <?php if ($requiereDelegado): ?>
                                <i class="bi bi-shield-check me-1"></i>Revisó / Delegado SST
                                <?php else: ?>
                                <i class="bi bi-people me-1"></i>Revisó / <?= $estandares <= 21 ? 'Vigía SST' : 'COPASST' ?>
                                <?php endif; ?>
                            </th>
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-check me-1"></i>Aprobó
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST / ELABORO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($consultorCargo) ?></span>
                                </div>
                                <!-- Firma posicionada al fondo -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" alt="Firma Consultor" style="max-height: 40px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- DELEGADO SST o COPASST / REVISO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?php if ($requiereDelegado): ?>
                                            <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '' ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;">
                                        <?php if ($requiereDelegado): ?>
                                            <?= esc($delegadoCargo) ?>
                                        <?php else: ?>
                                            <?= $estandares <= 21 ? 'Vigía de SST' : 'COPASST' ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <!-- Firma posicionada al fondo -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                                    if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 40px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL / APROBO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($repLegalCargo) ?></span>
                                </div>
                                <!-- Firma posicionada al fondo -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 40px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Pie de documento -->
            <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Versiones -->
    <div class="modal fade" id="modalHistorialVersiones" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Historial de Versiones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="contenedorHistorial">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Cargando historial...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idDocumento = <?= $documento['id_documento'] ?>;

        // Cargar historial cuando se abre el modal
        document.getElementById('modalHistorialVersiones')?.addEventListener('show.bs.modal', function() {
            const contenedor = document.getElementById('contenedorHistorial');

            fetch('<?= base_url('documentos-sst/historial-versiones/') ?>' + idDocumento)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.versiones.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-hover">';
                    html += '<thead><tr><th>Version</th><th>Tipo</th><th>Descripcion</th><th>Fecha</th><th>Autorizado por</th><th>Acciones</th></tr></thead><tbody>';

                    data.versiones.forEach(v => {
                        const fecha = new Date(v.fecha_autorizacion).toLocaleDateString('es-CO', {
                            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                        });
                        const estadoBadge = v.estado === 'vigente'
                            ? '<span class="badge bg-success">Vigente</span>'
                            : '<span class="badge bg-secondary">Obsoleto</span>';
                        const tipoBadge = v.tipo_cambio === 'mayor'
                            ? '<span class="badge bg-danger">Mayor</span>'
                            : '<span class="badge bg-info">Menor</span>';

                        html += `<tr class="${v.estado === 'obsoleto' ? 'table-secondary' : ''}">
                            <td><strong>v${v.version_texto}</strong> ${estadoBadge}</td>
                            <td>${tipoBadge}</td>
                            <td>${v.descripcion_cambio}</td>
                            <td><small>${fecha}</small></td>
                            <td>${v.autorizado_por || 'N/A'}</td>
                            <td>
                                <a href="<?= base_url('documentos-sst/descargar-version-pdf/') ?>${v.id_version}"
                                   class="btn btn-sm btn-outline-danger" title="Descargar PDF de esta version">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                ${v.estado === 'obsoleto' ? `
                                <button type="button" class="btn btn-sm btn-outline-warning btn-restaurar"
                                        data-id="${v.id_version}" title="Restaurar esta version">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>` : ''}
                            </td>
                        </tr>`;
                    });

                    html += '</tbody></table></div>';
                    contenedor.innerHTML = html;

                    // Agregar eventos a botones de restaurar
                    contenedor.querySelectorAll('.btn-restaurar').forEach(btn => {
                        btn.addEventListener('click', function() {
                            if (confirm('¿Restaurar esta version? El documento actual pasara a estado borrador.')) {
                                restaurarVersion(this.dataset.id);
                            }
                        });
                    });
                } else {
                    contenedor.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">No hay versiones registradas aun.<br>
                            Apruebe el documento para crear la primera version.</p>
                        </div>`;
                }
            })
            .catch(error => {
                contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar historial</div>';
            });
        });

        // Funcion para restaurar version
        function restaurarVersion(idVersion) {
            const formData = new FormData();
            formData.append('id_version', idVersion);

            fetch('<?= base_url('documentos-sst/restaurar-version') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error de conexion');
            });
        }
    });
    </script>
</body>
</html>
