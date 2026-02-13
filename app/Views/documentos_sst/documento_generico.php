<?php
/**
 * Vista genérica para documentos SST
 *
 * Esta vista puede renderizar cualquier tipo de documento SST usando
 * la configuración desde la BD (arquitectura escalable).
 *
 * Variables esperadas:
 * @var string $titulo Título de la página
 * @var array $cliente Datos del cliente
 * @var array $documento Documento actual
 * @var array $contenido Contenido decodificado del documento
 * @var int $anio Año del documento
 * @var array $versiones Historial de versiones
 * @var array $responsables Responsables SST
 * @var array $contexto Contexto SST del cliente
 * @var array|null $consultor Datos del consultor
 * @var array $firmasElectronicas Firmas electrónicas
 * @var array $firmantesDefinidos Lista de firmantes definidos para este tipo
 * @var array|null $tipoDocConfig Configuración del tipo de documento (opcional)
 * @var array $tablasDinamicas Datos de tablas dinámicas (opcional)
 */

// Obtener configuración si no viene pasada
$tipoDocConfig = $tipoDocConfig ?? null;
$estandar = $tipoDocConfig['estandar'] ?? $documento['estandar'] ?? '';
$categoria = $tipoDocConfig['categoria'] ?? 'documento';
$icono = $tipoDocConfig['icono'] ?? 'bi-file-text';

/**
 * Función mejorada para convertir Markdown a HTML
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
                // Si estábamos en una tabla, cerrarla
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

        // Cerrar tabla si terminó en una
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
                // Si estábamos en lista, cerrarla
                if ($enLista) {
                    $listaHtml .= '</ul>';
                    $resultado[] = $listaHtml;
                    $listaHtml = '';
                    $enLista = false;
                }

                // Línea normal
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

        // Cerrar lista si terminó en una
        if ($enLista) {
            $listaHtml .= '</ul>';
            $resultado[] = $listaHtml;
        }

        // Unir con saltos de línea HTML
        $html = '';
        foreach ($resultado as $i => $item) {
            if (strpos($item, '<ul') === 0 || strpos($item, '<h') === 0 ||
                strpos($item, '<div') === 0 || strpos($item, '<br') === 0) {
                $html .= $item;
            } else {
                // Es texto normal, agregar <br> si no es el último
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

        .seccion-titulo {
            font-size: 1.1rem;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
            margin-top: 25px;
        }

        .seccion-contenido {
            text-align: justify;
            line-height: 1.7;
        }

        .seccion-contenido p {
            margin-bottom: 10px;
        }

        .seccion-contenido ul, .seccion-contenido ol {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .seccion-contenido li {
            margin-bottom: 5px;
        }

        .tabla-codigos {
            font-size: 0.85rem;
        }

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

        .info-documento {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .seccion {
            margin-bottom: 25px;
            page-break-inside: avoid;
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

        .tabla-firmas td {
            vertical-align: top;
            min-height: 120px;
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

        @media print {
            .firma-section {
                page-break-inside: avoid;
            }
            .seccion:last-of-type {
                page-break-inside: avoid;
            }
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
                    <span class="ms-3">
                        <i class="bi <?= esc($icono) ?> me-1"></i>
                        <?= esc($cliente['nombre_cliente']) ?> - <?= esc($documento['titulo'] ?? 'Documento SST') ?> <?= $anio ?>
                    </span>
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
                    <?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2" target="_blank">
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

            <!-- Panel de Información del Documento (no imprimible) -->
            <div class="panel-aprobacion no-print">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-dark estado-badge"><?= esc($documento['codigo'] ?? 'Sin código') ?></span>
                            <span class="badge bg-light text-dark estado-badge">v<?= $documento['version'] ?? 1 ?>.0</span>
                            <?php
                            $estadoClass = match($documento['estado'] ?? 'borrador') {
                                'aprobado' => 'bg-success',
                                'firmado' => 'bg-success',
                                'generado' => 'bg-info',
                                'borrador' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $estadoClass ?> estado-badge">
                                <i class="bi bi-<?= in_array($documento['estado'] ?? '', ['aprobado', 'firmado']) ? 'check-circle' : 'pencil' ?> me-1"></i>
                                <?= ucfirst($documento['estado'] ?? 'borrador') ?>
                            </span>
                        </div>
                        <small class="opacity-75">
                            <?php if (($documento['estado'] ?? '') === 'aprobado' && !empty($documento['fecha_aprobacion'])): ?>
                                Aprobado el <?= date('d/m/Y H:i', strtotime($documento['fecha_aprobacion'])) ?>
                            <?php else: ?>
                                Última modificación: <?= date('d/m/Y H:i', strtotime($documento['updated_at'] ?? 'now')) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Info del documento simplificada (no imprimible) -->
            <div class="info-documento no-print">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Categoría:</small>
                        <span class="fw-bold"><?= ucfirst(esc($categoria)) ?></span>
                    </div>
                    <?php if ($estandar): ?>
                    <div class="col-md-4">
                        <small class="text-muted">Estándar:</small>
                        <span class="fw-bold"><?= esc($estandar) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <small class="text-muted">Cliente:</small>
                        <span class="fw-bold"><?= esc($cliente['nombre_cliente']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Encabezado formal del documento -->
            <table class="encabezado-formal">
                <tr>
                    <td class="encabezado-logo" rowspan="2">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo <?= esc($cliente['nombre_cliente']) ?>">
                        <?php else: ?>
                            <div style="font-size: 0.7rem; color: #666;">
                                <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="encabezado-titulo-central">
                        <div class="sistema">SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                    </td>
                    <td class="encabezado-info" rowspan="2">
                        <table class="encabezado-info-table">
                            <tr>
                                <td class="label">Código:</td>
                                <td><?= esc($documento['codigo'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Versión:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Fecha:</td>
                                <td><?= date('d/m/Y') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Página:</td>
                                <td>1 de 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc"><?= strtoupper(esc($documento['titulo'] ?? 'DOCUMENTO SST')) ?></div>
                    </td>
                </tr>
            </table>

            <!-- Secciones del documento -->
            <?php
            $secciones = $contenido['secciones'] ?? [];
            $tablasDinamicas = $tablasDinamicas ?? [];

            foreach ($secciones as $seccion):
                $titulo = $seccion['titulo'] ?? $seccion['nombre'] ?? '';
                $contenidoSeccion = $seccion['contenido'] ?? '';
                $key = $seccion['key'] ?? '';
                $tipoContenido = $seccion['tipo_contenido'] ?? 'texto';
                $tablaDinamica = $seccion['tabla_dinamica'] ?? null;
            ?>
                <?= view('documentos_sst/_components/seccion_documento', [
                    'seccion' => [
                        'titulo' => $titulo,
                        'key' => $key,
                        'contenido' => $contenidoSeccion,
                        'tipo_contenido' => $tipoContenido,
                        'tabla_dinamica' => $tablaDinamica
                    ],
                    'tablasDinamicas' => $tablasDinamicas,
                    'formato' => 'web'
                ]) ?>
            <?php endforeach; ?>

            <!-- Control de Cambios (SIEMPRE visible) -->
            <div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-journal-text me-2"></i>CONTROL DE CAMBIOS
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 100px; text-align: center; font-weight: 600; color: #495057; border-top: none;">Versión</th>
                            <th style="font-weight: 600; color: #495057; border-top: none;">Descripción del Cambio</th>
                            <th style="width: 130px; text-align: center; font-weight: 600; color: #495057; border-top: none;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($versiones)): ?>
                            <?php foreach ($versiones as $idx => $v): ?>
                            <tr style="<?= $idx % 2 === 0 ? 'background-color: #fff;' : 'background-color: #f8f9fa;' ?>">
                                <td style="text-align: center; vertical-align: middle;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">
                                        <?= esc($v['version_texto'] ?? str_pad($v['numero_version'], 3, '0', STR_PAD_LEFT)) ?>
                                    </span>
                                </td>
                                <td style="vertical-align: middle;"><?= esc($v['descripcion_cambio']) ?></td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;"><?= date('d/m/Y', strtotime($v['fecha_autorizacion'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">
                                        1.0
                                    </span>
                                </td>
                                <td style="vertical-align: middle;">Elaboración inicial del documento</td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================== -->
            <!-- SECCIÓN: FIRMAS - Según configuración del contexto -->
            <!-- Si requiere_delegado_sst: Consultor + Delegado SST + Rep. Legal -->
            <!-- Si 7 estándares sin delegado: Solo Consultor + Rep. Legal -->
            <!-- Si 21+ estándares sin delegado: Consultor + Vigía/COPASST + Rep. Legal -->
            <!-- ============================================== -->
            <?php
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

            // Determinar si son solo 2 firmantes (7 estándares SIN delegado)
            $esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

            // Datos del Consultor desde tbl_consultor
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            // Según 1_A_SISTEMA_FIRMAS: Tipo E (2 firmantes por estándares) = "Consultor SST"
            // Tipo D (firmantesDefinidos) = "Responsable del SG-SST"
            $consultorCargo = $esSoloDosFirmantes ? 'Consultor SST' : 'Responsable del SG-SST';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';

            // Datos del Delegado SST (si aplica)
            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

            // Datos del Representante Legal - primero del contexto, luego del cliente
            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
            $repLegalCargo = 'Representante Legal';

            // ================================================
            // FIRMA CONSULTOR: Prioridad electrónica > física
            // Según 2_AA_WEB.md Sección 16
            // ================================================
            $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
            $firmaConsultorFisica = $consultor['firma_consultor'] ?? '';
            ?>

            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACIÓN
                </div>

                <?php if ($esSoloDosFirmantes): ?>
                <!-- ========== 7 ESTÁNDARES SIN DELEGADO: Solo 2 firmantes (Tipo E) ========== -->
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
                            <!-- CONSULTOR SST / ELABORÓ (Tipo E: 2 firmantes por estándares) -->
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
                                <!-- Firma posicionada al fondo (prioridad: electrónica > física) -->
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor SST" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
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
                            <!-- RESPONSABLE SG-SST / ELABORÓ -->
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
                                <!-- Firma posicionada al fondo (prioridad: electrónica > física) -->
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Responsable SST" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- DELEGADO SST o COPASST / REVISÓ -->
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
                                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL / APROBÓ -->
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
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestión SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
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
                    html += '<thead><tr><th>Versión</th><th>Tipo</th><th>Descripción</th><th>Fecha</th><th>Autorizado por</th><th>Acciones</th></tr></thead><tbody>';

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
                                   class="btn btn-sm btn-outline-danger" title="Descargar PDF de esta versión">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                ${v.estado === 'obsoleto' ? `
                                <button type="button" class="btn btn-sm btn-outline-warning btn-restaurar"
                                        data-id="${v.id_version}" title="Restaurar esta versión">
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
                            if (confirm('¿Restaurar esta versión? El documento actual pasará a estado borrador.')) {
                                restaurarVersion(this.dataset.id);
                            }
                        });
                    });
                } else {
                    contenedor.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">No hay versiones registradas aún.<br>
                            Apruebe el documento para crear la primera versión.</p>
                        </div>`;
                }
            })
            .catch(error => {
                contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar historial</div>';
            });
        });

        // Función para restaurar versión
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
                alert('Error de conexión');
            });
        }
    });
    </script>
</body>
</html>
