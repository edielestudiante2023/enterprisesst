<?php
/**
 * Template PDF genérico para documentos SST
 *
 * Puede renderizar cualquier tipo de documento usando configuración desde BD.
 *
 * Variables esperadas:
 * @var string $titulo Título del documento
 * @var array $cliente Datos del cliente
 * @var array $documento Documento actual
 * @var array $contenido Contenido decodificado
 * @var int $anio Año del documento
 * @var string $logoBase64 Logo del cliente en base64
 * @var array $versiones Historial de versiones
 * @var array $contexto Contexto SST del cliente
 * @var array|null $consultor Datos del consultor
 * @var string $firmaConsultorBase64 Firma del consultor en base64
 * @var array $firmasElectronicas Firmas electrónicas
 * @var array $firmantesDefinidos Lista de tipos de firmantes
 * @var array $tablasDinamicas Datos para tablas dinámicas (opcional)
 */

// Función para convertir Markdown a HTML para PDF
if (!function_exists('convertirMarkdownAHtmlPdf')) {
    function convertirMarkdownAHtmlPdf($texto) {
        if (empty($texto)) return '';

        if (preg_match('/<(p|ol|ul|li|div|table|br)\b[^>]*>/i', $texto)) {
            return $texto;
        }

        $lineas = explode("\n", $texto);
        $resultado = [];
        $tablaActual = [];
        $enTabla = false;

        foreach ($lineas as $linea) {
            $lineaTrim = trim($linea);

            if (preg_match('/^\|(.+)\|$/', $lineaTrim)) {
                if (preg_match('/^\|[\s\-\:\|]+\|$/', $lineaTrim)) continue;

                if (!$enTabla) {
                    $enTabla = true;
                    $tablaActual = [];
                }
                $celdas = array_map('trim', explode('|', trim($lineaTrim, '|')));
                $tablaActual[] = $celdas;
            } else {
                if ($enTabla && !empty($tablaActual)) {
                    $resultado[] = renderizarTablaPdfGen($tablaActual);
                    $tablaActual = [];
                    $enTabla = false;
                }

                if (!empty($lineaTrim)) {
                    $lineaProcesada = $lineaTrim;
                    $lineaProcesada = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $lineaProcesada);
                    $lineaProcesada = preg_replace('/(?<!\<)\*([^*]+)\*(?!\>)/', '<em>$1</em>', $lineaProcesada);

                    if (preg_match('/^[-•]\s+(.+)$/', $lineaTrim, $m)) {
                        $contenidoLista = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $m[1]);
                        $resultado[] = '<li>' . htmlspecialchars($contenidoLista, ENT_QUOTES, 'UTF-8') . '</li>';
                    } elseif (preg_match('/^(\d+)\.\s+(.+)$/', $lineaTrim, $m)) {
                        $contenidoLista = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $m[2]);
                        $resultado[] = '<li>' . htmlspecialchars($contenidoLista, ENT_QUOTES, 'UTF-8') . '</li>';
                    } else {
                        $resultado[] = '<p style="margin: 3px 0;">' . $lineaProcesada . '</p>';
                    }
                }
            }
        }

        if ($enTabla && !empty($tablaActual)) {
            $resultado[] = renderizarTablaPdfGen($tablaActual);
        }

        $html = implode("\n", $resultado);
        $html = preg_replace('/(<li>.*?<\/li>\s*)+/s', '<ul style="margin: 3px 0 3px 15px; padding-left: 0;">$0</ul>', $html);

        return $html;
    }
}

if (!function_exists('renderizarTablaPdfGen')) {
    function renderizarTablaPdfGen($filas) {
        if (empty($filas)) return '';

        $html = '<table style="width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt;">';
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
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
        }

        .encabezado-formal {
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }

        .encabezado-logo {
            padding: 5px;
            text-align: center;
        }

        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }

        .encabezado-titulo-central .sistema {
            font-size: 9pt;
            font-weight: bold;
            color: #333;
            padding: 5px 10px;
            border-bottom: 1px solid #333;
        }

        .encabezado-titulo-central .nombre-doc {
            font-size: 9pt;
            font-weight: bold;
            color: #333;
            padding: 5px 10px;
        }

        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 2px 5px;
            font-size: 8pt;
        }

        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }

        .encabezado-info-table .label {
            font-weight: bold;
        }

        .seccion {
            margin-bottom: 8px;
            page-break-inside: avoid;
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

        .seccion-contenido ul, .seccion-contenido ol {
            margin: 3px 0 3px 15px;
            padding-left: 15px;
        }

        .seccion-contenido li {
            margin-bottom: 2px;
        }

        .tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9pt;
        }

        .tabla-contenido th {
            border: 1px solid #999;
            padding: 5px 8px;
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .tabla-contenido td {
            border: 1px solid #999;
            padding: 5px 8px;
        }
    </style>
</head>
<body>
    <!-- Encabezado formal del documento -->
    <table class="encabezado-formal" style="width:100%;" cellpadding="0" cellspacing="0">
        <tr>
            <td class="encabezado-logo" rowspan="2" style="width:100px;" valign="middle" align="center">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="width:80px;height:auto;max-height:50px;">
                <?php else: ?>
                    <div style="font-size: 8pt;">
                        <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                    </div>
                <?php endif; ?>
            </td>
            <td class="encabezado-titulo-central" valign="middle">
                <div class="sistema">SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <td class="encabezado-info" rowspan="2" style="width:130px;" valign="middle">
                <table class="encabezado-info-table" style="width:100%;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Código:</td>
                        <td><?= esc($documento['codigo'] ?? 'DOC-SST-001') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Versión:</td>
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
            <td class="encabezado-titulo-central" valign="middle">
                <div class="nombre-doc"><?= esc(strtoupper($contenido['titulo'] ?? $documento['titulo'] ?? 'DOCUMENTO SST')) ?></div>
            </td>
        </tr>
    </table>

    <!-- Secciones del documento -->
    <?php if (!empty($contenido['secciones'])): ?>
        <?php foreach ($contenido['secciones'] as $seccion):
            $keySeccion = $seccion['key'] ?? '';
            $contenidoSeccion = $seccion['contenido'] ?? '';
            $tipoContenido = $seccion['tipo_contenido'] ?? 'texto';
            $tablaDinamica = $seccion['tabla_dinamica'] ?? null;
        ?>
            <div class="seccion">
                <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
                <div class="seccion-contenido">
                    <?php
                    // Mostrar contenido de texto
                    if (!empty($contenidoSeccion) && is_string($contenidoSeccion)) {
                        echo convertirMarkdownAHtmlPdf($contenidoSeccion);
                    }

                    // Manejar tablas dinámicas según el key de la sección
                    $tablasDinamicas = $tablasDinamicas ?? [];

                    // Sección: tipos_documentos
                    if ($keySeccion === 'tipos_documentos' && !empty($tiposDocumento ?? [])): ?>
                        <p style="margin: 8px 0 3px 0;">A continuación se presenta la clasificación de tipos de documentos del SG-SST:</p>
                        <?= view('documentos_sst/_components/tabla_dinamica', [
                            'datos' => $tiposDocumento,
                            'tipo' => 'tipos_documento',
                            'formato' => 'pdf'
                        ]) ?>
                    <?php endif;

                    // Sección: codificacion
                    if ($keySeccion === 'codificacion' && !empty($plantillas ?? [])): ?>
                        <p style="margin: 8px 0 3px 0;">Los códigos de los documentos del SG-SST son los siguientes:</p>
                        <?= view('documentos_sst/_components/tabla_dinamica', [
                            'datos' => $plantillas,
                            'tipo' => 'plantillas',
                            'formato' => 'pdf'
                        ]) ?>
                    <?php endif;

                    // Sección: listado_maestro
                    if ($keySeccion === 'listado_maestro' && !empty($listadoMaestro ?? [])): ?>
                        <?= view('documentos_sst/_components/tabla_dinamica', [
                            'datos' => $listadoMaestro,
                            'tipo' => 'listado_maestro',
                            'formato' => 'pdf'
                        ]) ?>
                    <?php endif;

                    // Tablas dinámicas genéricas desde configuración
                    if (!empty($tablasDinamicas[$tablaDinamica ?? ''])):
                        echo view('documentos_sst/_components/tabla_dinamica', [
                            'datos' => $tablasDinamicas[$tablaDinamica],
                            'tipo' => $tablaDinamica,
                            'formato' => 'pdf'
                        ]);
                    endif;
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Control de Cambios -->
    <div class="seccion" style="margin-top: 25px;">
        <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            CONTROL DE CAMBIOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 80px; background-color: #e9ecef; color: #333;">Versión</th>
                <th style="background-color: #e9ecef; color: #333;">Descripción del Cambio</th>
                <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
            </tr>
            <?php if (!empty($versiones)): ?>
                <?php foreach ($versiones as $v): ?>
                <tr>
                    <td style="text-align: center;"><?= str_pad($v['numero_version'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= esc($v['descripcion_cambio']) ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($v['fecha_autorizacion'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td style="text-align: center;">001</td>
                    <td>Creación del documento</td>
                    <td style="text-align: center;"><?= date('d/m/Y') ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Sección de Firmas -->
    <?php
    $firmantesArr = $firmantesDefinidos ?? [];
    $estandares = $contexto['estandares_aplicables'] ?? 60;
    $firmasElectronicas = $firmasElectronicas ?? [];

    // Construir array de firmantes
    $firmantesParaPdf = [];

    if (!empty($firmantesArr) && is_array($firmantesArr)) {
        foreach ($firmantesArr as $tipo) {
            switch ($tipo) {
                case 'responsable_sst':
                    $firmantesParaPdf[] = [
                        'tipo' => 'responsable_sst',
                        'columna_encabezado' => 'Elaboró / Responsable del SG-SST',
                        'nombre' => $consultor['nombre_consultor'] ?? '',
                        'cargo' => 'Responsable del SG-SST',
                        'licencia' => $consultor['numero_licencia'] ?? '',
                        'mostrar_licencia' => true,
                        'firma_archivo' => $consultor['firma_consultor'] ?? null
                    ];
                    break;
                case 'consultor_sst':
                    $firmantesParaPdf[] = [
                        'tipo' => 'consultor_sst',
                        'columna_encabezado' => 'Elaboró / Consultor SST',
                        'nombre' => $consultor['nombre_consultor'] ?? '',
                        'cargo' => 'Consultor SST',
                        'licencia' => $consultor['numero_licencia'] ?? '',
                        'mostrar_licencia' => true,
                        'firma_archivo' => $consultor['firma_consultor'] ?? null
                    ];
                    break;
                case 'delegado_sst':
                    $firmantesParaPdf[] = [
                        'tipo' => 'delegado_sst',
                        'columna_encabezado' => 'Revisó / Delegado SST',
                        'nombre' => $contexto['delegado_sst_nombre'] ?? '',
                        'cargo' => $contexto['delegado_sst_cargo'] ?? 'Delegado SST',
                        'firma_imagen' => $firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'] ?? null
                    ];
                    break;
                case 'vigia_sst':
                    $firmantesParaPdf[] = [
                        'tipo' => 'vigia_sst',
                        'columna_encabezado' => 'Revisó / Vigía de SST',
                        'nombre' => $contexto['vigia_sst_nombre'] ?? '',
                        'cargo' => 'Vigía de SST',
                        'firma_imagen' => $firmasElectronicas['vigia_sst']['evidencia']['firma_imagen'] ?? null
                    ];
                    break;
                case 'representante_legal':
                    $firmantesParaPdf[] = [
                        'tipo' => 'representante_legal',
                        'columna_encabezado' => 'Aprobó / Representante Legal',
                        'nombre' => $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '',
                        'cargo' => 'Representante Legal',
                        'firma_imagen' => $firmasElectronicas['representante_legal']['evidencia']['firma_imagen'] ?? null
                    ];
                    break;
            }
        }
    }

    // Renderizar firmas usando componente o fallback inline
    if (!empty($firmantesParaPdf)):
        $numFirmantes = count($firmantesParaPdf);
        $anchoColumna = match ($numFirmantes) {
            1 => '100%',
            2 => '50%',
            3 => '33.33%',
            default => (100 / $numFirmantes) . '%'
        };
    ?>
    <div style="margin-top: 25px;">
        <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            FIRMAS DE APROBACIÓN
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <?php foreach ($firmantesParaPdf as $firmante): ?>
                <th style="width: <?= $anchoColumna ?>; text-align: center; background-color: #e9ecef; color: #333;">
                    <?= esc($firmante['columna_encabezado']) ?>
                </th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($firmantesParaPdf as $firmante): ?>
                <td style="vertical-align: top; padding: 12px; height: 140px;">
                    <div style="margin-bottom: 5px;">
                        <strong>Nombre:</strong>
                        <?= !empty($firmante['nombre']) ? esc($firmante['nombre']) : '________________________' ?>
                    </div>
                    <div style="margin-bottom: 5px;">
                        <strong>Cargo:</strong>
                        <span><?= esc($firmante['cargo']) ?></span>
                    </div>
                    <?php if (!empty($firmante['mostrar_licencia']) && !empty($firmante['licencia'])): ?>
                    <div style="margin-bottom: 5px;">
                        <strong>Licencia SST:</strong>
                        <span><?= esc($firmante['licencia']) ?></span>
                    </div>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($firmantesParaPdf as $firmante): ?>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <?php
                    // Firma electrónica (imagen base64)
                    if (!empty($firmante['firma_imagen'])):
                    ?>
                        <img src="<?= $firmante['firma_imagen'] ?>" alt="Firma"
                             style="max-height: 56px; max-width: 168px; margin-bottom: 5px;"><br>
                    <?php
                    // Firma desde archivo (usar base64 si disponible)
                    elseif (!empty($firmante['firma_archivo']) && !empty($firmaConsultorBase64)):
                    ?>
                        <img src="<?= $firmaConsultorBase64 ?>" alt="Firma"
                             style="max-height: 56px; max-width: 168px; margin-bottom: 5px;"><br>
                    <?php endif; ?>

                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666; font-size: 7pt;">Firma</small>
                    </div>
                </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
    <?php endif; ?>

</body>
</html>
