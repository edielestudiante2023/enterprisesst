<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            border-bottom: 1px solid #e9ecef;
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
                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
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
                    <?php if (in_array($documento['estado'] ?? '', ['borrador', 'generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
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
                            <?php
                            $versionTextoDisplay = $documento['version'] . '.0';
                            if (!empty($versiones)) {
                                foreach ($versiones as $v) {
                                    if (($v['estado'] ?? '') === 'vigente') {
                                        $versionTextoDisplay = $v['version_texto'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            <span class="badge bg-light text-dark estado-badge">v<?= esc($versionTextoDisplay) ?></span>
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
                                <td class="label">Vigencia:</td>
                                <td><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
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
            /**
             * Funcion mejorada para convertir Markdown a HTML
             * Soporta: tablas, negritas, cursivas, listas, encabezados
             */
            if (!function_exists('convertirMarkdownAHtml')) {
                function convertirMarkdownAHtml($texto) {
                    if (empty($texto)) return '';

                    // 1. Primero convertir tablas Markdown
                    $texto = convertirTablasMarkdown($texto);

                    // 2. Dividir en partes HTML (tablas) y texto normal
                    $partes = preg_split('/(<div class="table-responsive[^>]*>.*?<\/div>)/s', $texto, -1, PREG_SPLIT_DELIM_CAPTURE);

                    $resultado = '';
                    foreach ($partes as $parte) {
                        if (strpos($parte, '<div class="table-responsive') === 0) {
                            // Es una tabla HTML, no modificar
                            $resultado .= $parte;
                        } else {
                            // Es texto, convertir markdown
                            $resultado .= convertirTextoMarkdown($parte);
                        }
                    }

                    return $resultado;
                }

                function convertirTablasMarkdown($texto) {
                    $lineas = explode("\n", $texto);
                    $enTabla = false;
                    $resultado = [];
                    $tablaHtml = '';
                    $esEncabezado = true;

                    foreach ($lineas as $linea) {
                        $lineaTrim = trim($linea);

                        // Normalizar tablas Markdown sin pipes al inicio/final
                        // "Col1 | Col2 | Col3" → "| Col1 | Col2 | Col3 |"
                        if (strpos($lineaTrim, '|') !== false && substr($lineaTrim, 0, 1) !== '|') {
                            $lineaTrim = '| ' . $lineaTrim . ' |';
                        }

                        // Detectar linea de tabla (empieza con |)
                        if (preg_match('/^\|(.+)\|$/', $lineaTrim)) {
                            // Ignorar linea separadora (|---|---|)
                            if (preg_match('/^\|[\s\-\:\|]+\|$/', $lineaTrim)) {
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
                                // Convertir negritas dentro de celdas
                                $celdaHtml = htmlspecialchars($celda);
                                $celdaHtml = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $celdaHtml);
                                $celdaHtml = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $celdaHtml);
                                $tablaHtml .= "<{$tag}{$estilo}>" . $celdaHtml . "</{$tag}>";
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

                function convertirTextoMarkdown($texto) {
                    // Escapar HTML primero (pero preservar saltos de linea)
                    $lineas = explode("\n", $texto);
                    $resultado = [];
                    $enLista = false;
                    $listaHtml = '';

                    foreach ($lineas as $linea) {
                        $lineaTrim = trim($linea);

                        // Detectar items de lista (- item o * item o numero. item)
                        if (preg_match('/^[\-\*]\s+(.+)$/', $lineaTrim, $matches) ||
                            preg_match('/^\d+\.\s+(.+)$/', $lineaTrim, $matches)) {

                            if (!$enLista) {
                                $enLista = true;
                                $listaHtml = '<ul class="mb-3">';
                            }

                            $itemTexto = htmlspecialchars($matches[1]);
                            // Convertir negritas y cursivas en el item
                            $itemTexto = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $itemTexto);
                            $itemTexto = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $itemTexto);
                            $listaHtml .= '<li>' . $itemTexto . '</li>';

                        } else {
                            // Si estabamos en lista, cerrarla
                            if ($enLista) {
                                $listaHtml .= '</ul>';
                                $resultado[] = $listaHtml;
                                $listaHtml = '';
                                $enLista = false;
                            }

                            // Linea normal
                            if (!empty($lineaTrim)) {
                                $lineaHtml = htmlspecialchars($linea);

                                // Convertir encabezados markdown (## Titulo)
                                if (preg_match('/^#{1,6}\s+(.+)$/', $lineaTrim, $matches)) {
                                    $nivel = strlen(preg_replace('/[^#]/', '', $lineaTrim));
                                    $tituloTexto = htmlspecialchars(trim($matches[1]));
                                    $tituloTexto = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $tituloTexto);
                                    $resultado[] = "<h{$nivel} class='mt-3 mb-2'>{$tituloTexto}</h{$nivel}>";
                                    continue;
                                }

                                // Convertir **negrita** y *cursiva*
                                $lineaHtml = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $lineaHtml);
                                $lineaHtml = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $lineaHtml);

                                $resultado[] = $lineaHtml;
                            } else {
                                $resultado[] = '<br>';
                            }
                        }
                    }

                    // Cerrar lista si termino en una
                    if ($enLista) {
                        $listaHtml .= '</ul>';
                        $resultado[] = $listaHtml;
                    }

                    // Unir con saltos de linea HTML
                    $html = '';
                    foreach ($resultado as $i => $item) {
                        if (strpos($item, '<ul') === 0 || strpos($item, '<h') === 0 ||
                            strpos($item, '<div') === 0 || strpos($item, '<br') === 0) {
                            $html .= $item;
                        } else {
                            // Es texto normal, agregar <br> si no es el ultimo
                            $html .= $item;
                            if ($i < count($resultado) - 1 && !empty(trim($item))) {
                                $nextItem = $resultado[$i + 1] ?? '';
                                if (strpos($nextItem, '<ul') !== 0 && strpos($nextItem, '<h') !== 0 &&
                                    strpos($nextItem, '<div') !== 0 && strpos($nextItem, '<br') !== 0) {
                                    $html .= '<br>';
                                }
                            }
                        }
                    }

                    return $html;
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
                                <?= convertirMarkdownAHtml($seccion['contenido'] ?? '') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- SECCION: CONTROL DE CAMBIOS (Imprimible) -->
            <!-- ============================================== -->
            <div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
                <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 8px 12px; margin-bottom: 0; border: none;">
                    CONTROL DE CAMBIOS
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="width: 80px; text-align: center; font-weight: bold; color: #333; border-top: none;">Version</th>
                            <th style="font-weight: bold; color: #333; border-top: none;">Descripcion del Cambio</th>
                            <th style="width: 90px; text-align: center; font-weight: bold; color: #333; border-top: none;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($versiones)): ?>
                            <?php foreach ($versiones as $idx => $ver): ?>
                            <tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                                <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                                <td><?= esc($ver['descripcion_cambio']) ?></td>
                                <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="text-align: center; font-weight: bold;">1.0</td>
                                <td>Elaboracion inicial del documento</td>
                                <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
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

            <?php
            // ================================================
            // FIRMA CONSULTOR: Prioridad electrónica > física
            // Según 2_AA_WEB.md Sección 16
            // ================================================
            $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
            $firmaConsultorFisica = $consultor['firma_consultor'] ?? '';
            ?>

            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 8px 12px; margin-bottom: 0; border: none;">
                    FIRMAS DE APROBACIÓN
                </div>

                <?php if ($requiereDelegado): ?>
                <!-- ========== PRIORIDAD MAXIMA: Delegado SST = 3 firmantes ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Elaboró</th>
                            <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Revisó / Delegado SST</th>
                            <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Aprobó</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST / ELABORO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($consultorCargo) ?></span>
                                </div>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- DELEGADO SST / REVISO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($delegadoCargo) ?></span>
                                </div>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaDelegadoPrioridad = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                                    if ($firmaDelegadoPrioridad && !empty($firmaDelegadoPrioridad['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaDelegadoPrioridad['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL / APROBO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($repLegalCargo) ?></span>
                                </div>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaRepLegalPrioridad = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegalPrioridad && !empty($firmaRepLegalPrioridad['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegalPrioridad['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php elseif ($esSoloDosFirmantes): ?>
                <!-- ========== 7 ESTANDARES SIN DELEGADO: Solo 2 firmantes ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="width: 50%; text-align: center; font-weight: bold; color: #333; border-top: none;">Elaboró / Consultor SST</th>
                            <th style="width: 50%; text-align: center; font-weight: bold; color: #333; border-top: none;">Aprobó / Representante Legal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST -->
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #333;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #333;">Cargo:</strong>
                                    <span><?= esc($consultorCargo) ?></span>
                                </div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #333;">Licencia SST:</strong>
                                    <span><?= esc($consultorLicencia) ?></span>
                                </div>
                                <?php endif; ?>
                                <!-- Firma posicionada al fondo (prioridad: electrónica > física) -->
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL -->
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #333;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #333;">Cargo:</strong>
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
                <!-- ========== 3 FIRMANTES: Vigía/COPASST ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Elaboró</th>
                            <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Revisó / <?= $estandares <= 21 ? 'Vigía SST' : 'COPASST' ?></th>
                            <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Aprobó</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST / ELABORO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($consultorCargo) ?></span>
                                </div>
                                <!-- Firma posicionada al fondo (prioridad: electrónica > física) -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- VIGIA/COPASST / REVISO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;">
                                        <?= $estandares <= 21 ? 'Vigía de SST' : 'COPASST' ?>
                                    </span>
                                </div>
                                <!-- Firma posicionada al fondo (prioridad: electrónica delegado > electrónica vigía > física vigía) -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaVigiaElectronica = ($firmasElectronicas ?? [])['delegado_sst'] ?? ($firmasElectronicas ?? [])['vigia_sst'] ?? null;
                                    if ($firmaVigiaElectronica && !empty($firmaVigiaElectronica['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaVigiaElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Vigía SST" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php elseif (!empty($vigia['firma_vigia'] ?? '')): ?>
                                        <img src="<?= base_url('uploads/' . $vigia['firma_vigia']) ?>" alt="Firma Vigía SST" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL / APROBO -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #333; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($repLegalCargo) ?></span>
                                </div>
                                <!-- Firma posicionada al fondo -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
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

    <!-- Modal Historial de Versiones (componente estandar) -->
    <?= view('documentos_sst/_components/modal_historial_versiones', [
        'id_documento' => $documento['id_documento'],
        'versiones' => $versiones ?? []
    ]) ?>

    <!-- Modal Nueva Version (componente estandar) -->
    <?= view('documentos_sst/_components/modal_nueva_version', [
        'id_documento' => $documento['id_documento'],
        'version_actual' => $versionTextoDisplay ?? '1.0',
        'tipo_documento' => 'programa_capacitacion'
    ]) ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
