<?php
/**
 * Convierte contenido Markdown a HTML para exportación PDF
 * Maneja: tablas Markdown, negritas, cursivas, listas y párrafos
 */
if (!function_exists('convertirMarkdownAHtmlPdf')) {
    function convertirMarkdownAHtmlPdf($texto) {
        if (empty($texto)) return '';

        $lineas = explode("\n", $texto);
        $resultado = [];
        $tablaActual = [];
        $enTabla = false;

        foreach ($lineas as $linea) {
            $lineaTrim = trim($linea);

            // Detectar línea de tabla (comienza con |)
            if (preg_match('/^\|(.+)\|$/', $lineaTrim)) {
                // Ignorar línea separadora (|---|---|)
                if (preg_match('/^\|[\s\-\:\|]+\|$/', $lineaTrim)) {
                    continue;
                }

                if (!$enTabla) {
                    $enTabla = true;
                    $tablaActual = [];
                }

                // Extraer celdas
                $celdas = array_map('trim', explode('|', trim($lineaTrim, '|')));
                $tablaActual[] = $celdas;

            } else {
                // Si estábamos en una tabla, cerrarla
                if ($enTabla && !empty($tablaActual)) {
                    $resultado[] = renderizarTablaPdf($tablaActual);
                    $tablaActual = [];
                    $enTabla = false;
                }

                // Procesar línea de texto normal
                if (!empty($lineaTrim)) {
                    // PRIMERO: Preservar tags HTML existentes (<strong>, <em>) con placeholders
                    $lineaProcesada = preg_replace('/<strong>([^<]*)<\/strong>/', '{{HTML_BOLD_START}}$1{{HTML_BOLD_END}}', $lineaTrim);
                    $lineaProcesada = preg_replace('/<em>([^<]*)<\/em>/', '{{HTML_ITALIC_START}}$1{{HTML_ITALIC_END}}', $lineaProcesada);
                    $lineaProcesada = preg_replace('/<b>([^<]*)<\/b>/', '{{HTML_BOLD_START}}$1{{HTML_BOLD_END}}', $lineaProcesada);
                    $lineaProcesada = preg_replace('/<i>([^<]*)<\/i>/', '{{HTML_ITALIC_START}}$1{{HTML_ITALIC_END}}', $lineaProcesada);

                    // SEGUNDO: Convertir markdown a placeholders
                    // Negrita: **texto** -> marcador temporal
                    $lineaProcesada = preg_replace('/\*\*([^*]+)\*\*/', '{{BOLD_START}}$1{{BOLD_END}}', $lineaProcesada);
                    // Cursiva: *texto* -> marcador temporal (pero no dentro de negrita)
                    $lineaProcesada = preg_replace('/(?<!\{)\*([^*]+)\*(?!\})/', '{{ITALIC_START}}$1{{ITALIC_END}}', $lineaProcesada);

                    // TERCERO: Escapar HTML restante
                    $lineaProcesada = htmlspecialchars($lineaProcesada, ENT_QUOTES, 'UTF-8');

                    // CUARTO: Convertir todos los placeholders a HTML
                    $lineaProcesada = str_replace(['{{BOLD_START}}', '{{BOLD_END}}'], ['<strong>', '</strong>'], $lineaProcesada);
                    $lineaProcesada = str_replace(['{{ITALIC_START}}', '{{ITALIC_END}}'], ['<em>', '</em>'], $lineaProcesada);
                    $lineaProcesada = str_replace(['{{HTML_BOLD_START}}', '{{HTML_BOLD_END}}'], ['<strong>', '</strong>'], $lineaProcesada);
                    $lineaProcesada = str_replace(['{{HTML_ITALIC_START}}', '{{HTML_ITALIC_END}}'], ['<em>', '</em>'], $lineaProcesada);

                    // Detectar lista
                    if (preg_match('/^[-•]\s+(.+)$/', $lineaTrim, $m)) {
                        $contenidoLista = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $m[1]);
                        $contenidoLista = preg_replace('/(?<!\<)\*([^*]+)\*(?!\>)/', '<em>$1</em>', $contenidoLista);
                        $resultado[] = '<li>' . $contenidoLista . '</li>';
                    } else {
                        $resultado[] = '<p style="margin: 3px 0;">' . $lineaProcesada . '</p>';
                    }
                } else {
                    // Línea vacía
                    $resultado[] = '';
                }
            }
        }

        // Cerrar tabla pendiente al final
        if ($enTabla && !empty($tablaActual)) {
            $resultado[] = renderizarTablaPdf($tablaActual);
        }

        // Agrupar listas
        $html = implode("\n", $resultado);
        $html = preg_replace('/(<li>.*?<\/li>\s*)+/s', '<ul style="margin: 3px 0 3px 15px; padding-left: 0;">$0</ul>', $html);

        return $html;
    }
}

if (!function_exists('renderizarTablaPdf')) {
    function renderizarTablaPdf($filas) {
        if (empty($filas)) return '';

        $html = '<table class="tabla-contenido" style="width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt;">';

        foreach ($filas as $idx => $celdas) {
            $html .= '<tr>';
            $esEncabezado = ($idx === 0);

            foreach ($celdas as $celda) {
                if ($esEncabezado) {
                    $html .= '<th style="border: 1px solid #999; padding: 5px 8px; background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">' . htmlspecialchars($celda, ENT_QUOTES, 'UTF-8') . '</th>';
                } else {
                    $html .= '<td style="border: 1px solid #999; padding: 5px 8px;">' . htmlspecialchars($celda, ENT_QUOTES, 'UTF-8') . '</td>';
                }
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?></title>
    <style>
        @page {
            margin: 2cm 1.5cm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.15;
            color: #333;
        }

        /* Encabezado formal */
        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }

        .encabezado-logo {
            width: 120px;
            padding: 8px;
            text-align: center;
        }

        .encabezado-logo img {
            max-width: 100px;
            max-height: 60px;
        }

        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }

        .encabezado-titulo-central .sistema {
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
            border-bottom: 1px solid #333;
        }

        .encabezado-titulo-central .nombre-doc {
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
        }

        .encabezado-info {
            width: 140px;
            padding: 0;
        }

        .encabezado-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 3px 6px;
            font-size: 8pt;
        }

        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }

        .encabezado-info-table .label {
            font-weight: bold;
        }

        /* Secciones */
        .seccion {
            margin-bottom: 8px;
        }

        .seccion-titulo {
            font-size: 11pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 3px;
            margin-bottom: 5px;
            margin-top: 8px;
        }

        .seccion-contenido {
            text-align: justify;
            line-height: 1.2;
        }

        .seccion-contenido p {
            margin: 3px 0;
        }

        .seccion-contenido ul {
            margin: 3px 0 3px 15px;
            padding-left: 0;
        }

        .seccion-contenido li {
            margin-bottom: 2px;
        }

        /* Tablas */
        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9pt;
        }

        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 5px 8px;
        }

        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Pie de pagina */
        .pie-documento {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        /* Reducir espacio en saltos de linea */
        br {
            line-height: 0.5;
        }

        /* Texto en negrita */
        strong, b {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Encabezado formal del documento -->
    <table class="encabezado-formal" style="width:100%;" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Logo del cliente -->
            <td class="encabezado-logo" rowspan="2" style="width:100px;" valign="middle" align="center">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="width:80px;height:auto;max-height:50px;">
                <?php else: ?>
                    <div style="font-size: 8pt;">
                        <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                    </div>
                <?php endif; ?>
            </td>
            <!-- Titulo del sistema -->
            <td class="encabezado-titulo-central" valign="middle">
                <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <!-- Info del documento -->
            <td class="encabezado-info" rowspan="2" style="width:130px;" valign="middle">
                <table class="encabezado-info-table" style="width:100%;" cellpadding="0" cellspacing="0">
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
                        <td><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <!-- Nombre del documento -->
            <td class="encabezado-titulo-central" valign="middle">
                <div class="nombre-doc"><?= esc(strtoupper($contenido['titulo'] ?? $documento['titulo'] ?? 'DOCUMENTO SST')) ?></div>
            </td>
        </tr>
    </table>

    <!-- Secciones del documento -->
    <?php if (!empty($contenido['secciones'])): ?>
        <?php foreach ($contenido['secciones'] as $seccion): ?>
            <div class="seccion">
                <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
                <div class="seccion-contenido">
                    <?php
                    $contenidoSeccion = $seccion['contenido'] ?? '';

                    // Si es array estructurado (tabla con filas)
                    if (is_array($contenidoSeccion)) {
                        if (isset($contenidoSeccion['filas'])) {
                            echo '<table class="tabla-contenido">';
                            if (!empty($contenidoSeccion['encabezados'])) {
                                echo '<tr>';
                                foreach ($contenidoSeccion['encabezados'] as $enc) {
                                    echo '<th style="border: 1px solid #999; padding: 5px 8px; background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">' . esc($enc) . '</th>';
                                }
                                echo '</tr>';
                            }
                            foreach ($contenidoSeccion['filas'] as $fila) {
                                echo '<tr>';
                                if (is_array($fila)) {
                                    foreach ($fila as $celda) {
                                        echo '<td style="border: 1px solid #999; padding: 5px 8px;">' . esc(is_array($celda) ? json_encode($celda) : $celda) . '</td>';
                                    }
                                } else {
                                    echo '<td style="border: 1px solid #999; padding: 5px 8px;">' . esc($fila) . '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                        $contenidoSeccion = '';
                    }

                    // Convertir contenido Markdown a HTML (incluye tablas mixtas con texto)
                    if (!empty($contenidoSeccion)) {
                        echo convertirMarkdownAHtmlPdf($contenidoSeccion);
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ============================================== -->
    <!-- SECCION: CONTROL DE CAMBIOS -->
    <!-- ============================================== -->
    <div class="seccion" style="margin-top: 25px;">
        <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            CONTROL DE CAMBIOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
                <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
                <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
            </tr>
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
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION: FIRMAS - Segun nivel de estandares -->
    <!-- 7 estandares: Solo Consultor + Representante Legal -->
    <!-- 21+ estandares: Consultor + COPASST/Vigia + Rep. Legal -->
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

    <div style="margin-top: 25px;">
        <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            FIRMAS DE APROBACIÓN
        </div>

        <?php if ($esSoloDosFirmantes): ?>
        <!-- ========== 7 ESTANDARES SIN DELEGADO: Solo 2 firmantes ========== -->
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 50%; background-color: #e9ecef; color: #333;">Elaboró / Consultor SST</th>
                <th style="width: 50%; background-color: #e9ecef; color: #333;">Aprobó / Representante Legal</th>
            </tr>
            <tr>
                <!-- CONSULTOR SST -->
                <td style="vertical-align: top; padding: 12px; height: 140px;">
                    <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= !empty($consultorNombre) ? esc($consultorNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= esc($consultorCargo) ?></div>
                    <?php if (!empty($consultorLicencia)): ?>
                    <div style="margin-bottom: 5px;"><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                    <?php endif; ?>
                </td>
                <!-- REPRESENTANTE LEGAL -->
                <td style="vertical-align: top; padding: 12px; height: 140px;">
                    <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= esc($repLegalCargo) ?></div>
                </td>
            </tr>
            <tr>
                <!-- Fila de firmas alineadas -->
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <?php if (!empty($firmaConsultorBase64)): ?>
                        <img src="<?= $firmaConsultorBase64 ?>" alt="Firma" style="max-height: 40px; max-width: 120px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <?php
                    $firmaRepLegalPdf2 = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                    if ($firmaRepLegalPdf2 && !empty($firmaRepLegalPdf2['evidencia']['firma_imagen'])):
                    ?>
                        <img src="<?= $firmaRepLegalPdf2['evidencia']['firma_imagen'] ?>" alt="Firma" style="max-height: 40px; max-width: 120px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
            </tr>
        </table>

        <?php else: ?>
        <!-- ========== 3 FIRMANTES: Delegado SST o Vigía/COPASST ========== -->
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Elaboró</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">
                    <?php if ($requiereDelegado): ?>
                    Revisó / Delegado SST
                    <?php else: ?>
                    Revisó / <?= $estandares <= 21 ? 'Vigía SST' : 'COPASST' ?>
                    <?php endif; ?>
                </th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Aprobó</th>
            </tr>
            <tr>
                <!-- CONSULTOR SST / ELABORO -->
                <td style="vertical-align: top; padding: 10px; height: 70px;">
                    <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($consultorNombre) ? esc($consultorNombre) : '________________' ?></div>
                    <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> <?= esc($consultorCargo) ?></div>
                </td>
                <!-- DELEGADO SST o COPASST/VIGIA / REVISO -->
                <td style="vertical-align: top; padding: 10px; height: 70px;">
                    <div style="margin-bottom: 4px; font-size: 9pt;">
                        <strong>Nombre:</strong>
                        <?php if ($requiereDelegado && !empty($delegadoNombre)): ?>
                            <?= esc($delegadoNombre) ?>
                        <?php else: ?>
                            ________________
                        <?php endif; ?>
                    </div>
                    <div style="margin-bottom: 4px; font-size: 9pt;">
                        <strong>Cargo:</strong>
                        <?php if ($requiereDelegado): ?>
                            <?= esc($delegadoCargo) ?>
                        <?php else: ?>
                            <?= $estandares <= 21 ? 'Vigía de SST' : 'COPASST' ?>
                        <?php endif; ?>
                    </div>
                </td>
                <!-- REPRESENTANTE LEGAL / APROBO -->
                <td style="vertical-align: top; padding: 10px; height: 70px;">
                    <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '________________' ?></div>
                    <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> <?= esc($repLegalCargo) ?></div>
                </td>
            </tr>
            <tr>
                <!-- Fila de firmas alineadas -->
                <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
                    <?php if (!empty($firmaConsultorBase64)): ?>
                        <img src="<?= $firmaConsultorBase64 ?>" alt="Firma" style="max-height: 35px; max-width: 100px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666; font-size: 7pt;">Firma</small>
                    </div>
                </td>
                <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
                    <?php
                    $firmaDelegadoPdf = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                    if ($firmaDelegadoPdf && !empty($firmaDelegadoPdf['evidencia']['firma_imagen'])):
                    ?>
                        <img src="<?= $firmaDelegadoPdf['evidencia']['firma_imagen'] ?>" alt="Firma" style="max-height: 35px; max-width: 100px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666; font-size: 7pt;">Firma</small>
                    </div>
                </td>
                <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
                    <?php
                    $firmaRepLegalPdf3 = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                    if ($firmaRepLegalPdf3 && !empty($firmaRepLegalPdf3['evidencia']['firma_imagen'])):
                    ?>
                        <img src="<?= $firmaRepLegalPdf3['evidencia']['firma_imagen'] ?>" alt="Firma" style="max-height: 35px; max-width: 100px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666; font-size: 7pt;">Firma</small>
                    </div>
                </td>
            </tr>
        </table>
        <?php endif; ?>
    </div>

    <!-- Pie de documento -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
    </div>
</body>
</html>
