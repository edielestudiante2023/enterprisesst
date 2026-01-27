<?php
/**
 * Convierte contenido Markdown a HTML para exportación
 * Maneja: tablas Markdown, negritas, cursivas, listas y párrafos
 */
if (!function_exists('convertirMarkdownAHtml')) {
    function convertirMarkdownAHtml($texto, $esWord = true) {
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
                    $resultado[] = renderizarTablaHtml($tablaActual, $esWord);
                    $tablaActual = [];
                    $enTabla = false;
                }

                // Procesar línea de texto normal
                if (!empty($lineaTrim)) {
                    // PRIMERO: Convertir markdown a placeholders antes de escapar
                    // Negrita: **texto** -> marcador temporal
                    $lineaProcesada = preg_replace('/\*\*([^*]+)\*\*/', '{{BOLD_START}}$1{{BOLD_END}}', $lineaTrim);
                    // Cursiva: *texto* -> marcador temporal (pero no dentro de negrita)
                    $lineaProcesada = preg_replace('/(?<!\{)\*([^*]+)\*(?!\})/', '{{ITALIC_START}}$1{{ITALIC_END}}', $lineaProcesada);

                    // SEGUNDO: Escapar HTML
                    $lineaProcesada = htmlspecialchars($lineaProcesada, ENT_QUOTES, 'UTF-8');

                    // TERCERO: Convertir placeholders a HTML
                    $lineaProcesada = str_replace(['{{BOLD_START}}', '{{BOLD_END}}'], ['<b>', '</b>'], $lineaProcesada);
                    $lineaProcesada = str_replace(['{{ITALIC_START}}', '{{ITALIC_END}}'], ['<i>', '</i>'], $lineaProcesada);

                    // Detectar lista
                    if (preg_match('/^[-•]\s+(.+)$/', $lineaTrim, $m)) {
                        $contenidoLista = preg_replace('/\*\*([^*]+)\*\*/', '<b>$1</b>', $m[1]);
                        $contenidoLista = preg_replace('/(?<!\<)\*([^*]+)\*(?!\>)/', '<i>$1</i>', $contenidoLista);
                        $resultado[] = '<li>' . $contenidoLista . '</li>';
                    } else {
                        $resultado[] = '<p style="margin: 2px 0;">' . $lineaProcesada . '</p>';
                    }
                } else {
                    // Línea vacía
                    $resultado[] = '';
                }
            }
        }

        // Cerrar tabla pendiente al final
        if ($enTabla && !empty($tablaActual)) {
            $resultado[] = renderizarTablaHtml($tablaActual, $esWord);
        }

        // Agrupar listas
        $html = implode("\n", $resultado);
        $html = preg_replace('/(<li>.*?<\/li>\s*)+/s', '<ul style="margin: 3px 0 3px 15px; padding-left: 0;">$0</ul>', $html);

        return $html;
    }
}

if (!function_exists('renderizarTablaHtml')) {
    function renderizarTablaHtml($filas, $esWord = true) {
        if (empty($filas)) return '';

        $html = '<table class="tabla-contenido" style="width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt;">';

        foreach ($filas as $idx => $celdas) {
            $html .= '<tr>';
            $esEncabezado = ($idx === 0);

            foreach ($celdas as $celda) {
                if ($esEncabezado) {
                    $html .= '<th style="border: 1px solid #999; padding: 4px 6px; background-color: #0d6efd; color: white; font-weight: bold;">' . htmlspecialchars($celda, ENT_QUOTES, 'UTF-8') . '</th>';
                } else {
                    $html .= '<td style="border: 1px solid #999; padding: 3px 5px;">' . htmlspecialchars($celda, ENT_QUOTES, 'UTF-8') . '</td>';
                }
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page {
            size: letter;
            margin: 2cm 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.0;
            color: #333;
            mso-line-height-rule: exactly;
        }
        p { margin: 2px 0; line-height: 1.0; mso-line-height-rule: exactly; }
        br { mso-data-placement: same-cell; }
        table { border-collapse: collapse; }
        .seccion { margin-bottom: 6px; }
        .seccion-titulo {
            font-size: 11pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin-bottom: 4px;
            margin-top: 8px;
            line-height: 1.0;
        }
        .seccion-contenido { text-align: justify; line-height: 1.0; }
        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 9pt;
        }
        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 2px 4px;
        }
        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }
        ul { margin: 2px 0 2px 15px; padding-left: 0; line-height: 1.0; }
        li { margin-bottom: 1px; line-height: 1.0; }
    </style>
</head>
<body>
    <!-- Encabezado del documento -->
    <table width="100%" border="1" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
        <tr>
            <td width="80" rowspan="2" align="center" valign="middle" style="border:1px solid #333; padding:5px;">
                <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
                <?php else: ?>
                <b style="font-size:8pt;"><?= esc($cliente['nombre_cliente']) ?></b>
                <?php endif; ?>
            </td>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
            </td>
            <td width="120" rowspan="2" valign="middle" style="border:1px solid #333; padding:0; font-size:8pt;">
                <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                    <tr><td style="border-bottom:1px solid #333;"><b>Codigo:</b></td><td style="border-bottom:1px solid #333;"><?= esc($documento['codigo'] ?? 'DOC-001') ?></td></tr>
                    <tr><td style="border-bottom:1px solid #333;"><b>Version:</b></td><td style="border-bottom:1px solid #333;"><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td></tr>
                    <tr><td><b>Fecha:</b></td><td><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                <?= esc(strtoupper($contenido['titulo'] ?? 'DOCUMENTO SST')) ?>
            </td>
        </tr>
    </table>

    <!-- Contenido -->
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
                                    echo '<th style="border: 1px solid #999; padding: 4px 6px; background-color: #0d6efd; color: white; font-weight: bold;">' . esc($enc) . '</th>';
                                }
                                echo '</tr>';
                            }
                            foreach ($contenidoSeccion['filas'] as $fila) {
                                echo '<tr>';
                                if (is_array($fila)) {
                                    foreach ($fila as $celda) {
                                        echo '<td style="border: 1px solid #999; padding: 3px 5px;">' . esc(is_array($celda) ? json_encode($celda) : $celda) . '</td>';
                                    }
                                } else {
                                    echo '<td style="border: 1px solid #999; padding: 3px 5px;">' . esc($fila) . '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                        $contenidoSeccion = '';
                    }

                    // Convertir contenido Markdown a HTML (incluye tablas mixtas con texto)
                    if (!empty($contenidoSeccion)) {
                        echo convertirMarkdownAHtml($contenidoSeccion, true);
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ============================================== -->
    <!-- SECCION: CONTROL DE CAMBIOS -->
    <!-- ============================================== -->
    <div class="seccion" style="margin-top: 20px;">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            CONTROL DE CAMBIOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
                <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
                <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
            </tr>
            <?php if (!empty($versiones)): ?>
                <?php foreach ($versiones as $ver): ?>
                <tr>
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
    <!-- SECCION: FIRMAS DE APROBACION -->
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

    // Datos del Representante Legal
    $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
    $repLegalCargo = 'Representante Legal';
    ?>

    <div style="margin-top: 20px;">
        <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
            FIRMAS DE APROBACION
        </div>

        <?php if ($esSoloDosFirmantes): ?>
        <!-- 2 firmantes -->
        <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
            <tr>
                <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Elaboro / Consultor SST</td>
                <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Aprobo / Representante Legal</td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 6px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($consultorNombre) ? esc($consultorNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($consultorCargo) ?></p>
                    <?php if (!empty($consultorLicencia)): ?>
                    <p style="margin: 2px 0;"><b>Licencia:</b> <?= esc($consultorLicencia) ?></p>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: top; padding: 6px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($repLegalCargo) ?></p>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 7pt;">Firma</span>
                    </div>
                </td>
                <td style="padding: 6px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 7pt;">Firma</span>
                    </div>
                </td>
            </tr>
        </table>

        <?php else: ?>
        <!-- 3 firmantes -->
        <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
            <tr>
                <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Elaboro</td>
                <td width="34%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
                    <?php if ($requiereDelegado): ?>
                    Reviso / Delegado SST
                    <?php else: ?>
                    Reviso / <?= $estandares <= 21 ? 'Vigia SST' : 'COPASST' ?>
                    <?php endif; ?>
                </td>
                <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Aprobo</td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($consultorNombre) ? esc($consultorNombre) : '____________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($consultorCargo) ?></p>
                </td>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b>
                        <?php if ($requiereDelegado && !empty($delegadoNombre)): ?>
                            <?= esc($delegadoNombre) ?>
                        <?php else: ?>
                            ____________
                        <?php endif; ?>
                    </p>
                    <p style="margin: 2px 0;"><b>Cargo:</b>
                        <?php if ($requiereDelegado): ?>
                            <?= esc($delegadoCargo) ?>
                        <?php else: ?>
                            <?= $estandares <= 21 ? 'Vigia SST' : 'COPASST' ?>
                        <?php endif; ?>
                    </p>
                </td>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '____________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($repLegalCargo) ?></p>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 6pt;">Firma</span>
                    </div>
                </td>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 6pt;">Firma</span>
                    </div>
                </td>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 6pt;">Firma</span>
                    </div>
                </td>
            </tr>
        </table>
        <?php endif; ?>
    </div>

    <div style="margin-top:20px; padding-top:10px; border-top:1px solid #ccc; text-align:center; font-size:8pt; color:#666;">
        <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
        <p>Documento generado el <?= date('d/m/Y') ?></p>
    </div>
</body>
</html>
