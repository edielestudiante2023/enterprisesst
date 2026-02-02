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

// Función para convertir Markdown a HTML
if (!function_exists('convertirMarkdownAHtml')) {
    function convertirMarkdownAHtml($texto) {
        if (empty($texto)) return '';

        // Si ya tiene HTML, devolverlo
        if (preg_match('/<(p|ol|ul|li|div|table|br)\b[^>]*>/i', $texto)) {
            return $texto;
        }

        // Separar por líneas y procesar
        $lineas = explode("\n", $texto);
        $html = '';
        $enLista = false;
        $tipoLista = '';

        foreach ($lineas as $linea) {
            $lineaTrim = trim($linea);

            // Saltar líneas vacías
            if (empty($lineaTrim)) {
                if ($enLista) {
                    $html .= "</{$tipoLista}>\n";
                    $enLista = false;
                }
                continue;
            }

            // Detectar listas numeradas
            if (preg_match('/^(\d+)\.\s+(.+)$/', $lineaTrim, $matches)) {
                if (!$enLista || $tipoLista !== 'ol') {
                    if ($enLista) $html .= "</{$tipoLista}>\n";
                    $html .= "<ol>\n";
                    $enLista = true;
                    $tipoLista = 'ol';
                }
                $contenidoItem = $matches[2];
                $contenidoItem = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $contenidoItem);
                $contenidoItem = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $contenidoItem);
                $html .= "<li>" . htmlspecialchars_decode(htmlspecialchars($contenidoItem, ENT_QUOTES, 'UTF-8')) . "</li>\n";
            }
            // Detectar viñetas
            elseif (preg_match('/^[-*•]\s+(.+)$/', $lineaTrim, $matches)) {
                if (!$enLista || $tipoLista !== 'ul') {
                    if ($enLista) $html .= "</{$tipoLista}>\n";
                    $html .= "<ul>\n";
                    $enLista = true;
                    $tipoLista = 'ul';
                }
                $contenidoItem = $matches[1];
                $contenidoItem = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $contenidoItem);
                $contenidoItem = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $contenidoItem);
                $html .= "<li>" . htmlspecialchars_decode(htmlspecialchars($contenidoItem, ENT_QUOTES, 'UTF-8')) . "</li>\n";
            }
            // Párrafo normal
            else {
                if ($enLista) {
                    $html .= "</{$tipoLista}>\n";
                    $enLista = false;
                }
                $lineaProcesada = $lineaTrim;
                $lineaProcesada = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $lineaProcesada);
                $lineaProcesada = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $lineaProcesada);
                $html .= "<p>" . htmlspecialchars_decode(htmlspecialchars($lineaProcesada, ENT_QUOTES, 'UTF-8')) . "</p>\n";
            }
        }

        if ($enLista) {
            $html .= "</{$tipoLista}>\n";
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

            <!-- Control de Cambios -->
            <?php if (!empty($versiones)): ?>
            <div class="seccion-titulo mt-4">
                <i class="bi bi-clock-history me-2"></i>CONTROL DE CAMBIOS
            </div>
            <div class="table-responsive">
                <table class="table table-bordered tabla-codigos">
                    <thead style="background-color: #0d6efd; color: white;">
                        <tr>
                            <th style="width: 60px;">Versión</th>
                            <th style="width: 90px;">Fecha</th>
                            <th>Descripción del Cambio</th>
                            <th style="width: 120px;">Elaboró</th>
                            <th style="width: 120px;">Aprobó</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($versiones as $v): ?>
                        <tr>
                            <td class="text-center"><?= str_pad($v['numero_version'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($v['fecha_autorizacion'])) ?></td>
                            <td><?= esc($v['descripcion_cambio']) ?></td>
                            <td><?= esc($v['responsable_elaboracion']) ?></td>
                            <td><?= esc($v['responsable_aprobacion']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Sección de Firmas usando componente reutilizable -->
            <?php
            // Determinar firmantes según configuración
            $firmantesArr = $firmantesDefinidos ?? [];
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

            // Construir array de firmantes para el componente
            $firmantesParaComponente = [];

            // Si hay firmantes definidos específicamente para este tipo
            if (!empty($firmantesArr) && is_array($firmantesArr)) {
                foreach ($firmantesArr as $tipo) {
                    switch ($tipo) {
                        case 'responsable_sst':
                            $firmantesParaComponente[] = [
                                'tipo' => 'responsable_sst',
                                'columna_encabezado' => 'Elaboró / Responsable del SG-SST',
                                'nombre' => $consultor['nombre_consultor'] ?? '',
                                'cargo' => 'Responsable del SG-SST',
                                'licencia' => $consultor['numero_licencia'] ?? '',
                                'mostrar_licencia' => true,
                                'firma_archivo' => $consultor['firma_consultor'] ?? null
                            ];
                            break;
                        case 'delegado_sst':
                            $firmantesParaComponente[] = [
                                'tipo' => 'delegado_sst',
                                'columna_encabezado' => 'Revisó / Delegado SST',
                                'nombre' => $contexto['delegado_sst_nombre'] ?? '',
                                'cargo' => $contexto['delegado_sst_cargo'] ?? 'Delegado SST',
                                'firma_imagen' => $firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'] ?? null
                            ];
                            break;
                        case 'representante_legal':
                            $firmantesParaComponente[] = [
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

            // Renderizar componente de firmas
            if (!empty($firmantesParaComponente)):
                echo view('documentos_sst/_components/firmas_documento', [
                    'firmantes' => $firmantesParaComponente,
                    'titulo' => 'FIRMAS DE APROBACIÓN',
                    'formato' => 'web'
                ]);
            endif;
            ?>

        </div>
    </div>

    <!-- Modal Historial de Versiones -->
    <div class="modal fade" id="modalHistorialVersiones" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-clock-history me-2"></i>Historial de Versiones
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($versiones)): ?>
                        <?php foreach ($versiones as $v): ?>
                        <div class="historial-version <?= $v['estado_version'] === 'vigente' ? 'vigente' : 'obsoleto' ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>Versión <?= $v['numero_version'] ?></strong>
                                    <span class="badge bg-<?= $v['estado_version'] === 'vigente' ? 'success' : 'secondary' ?> ms-2">
                                        <?= ucfirst($v['estado_version']) ?>
                                    </span>
                                </div>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($v['fecha_autorizacion'])) ?></small>
                            </div>
                            <p class="mb-1 mt-2"><?= esc($v['descripcion_cambio']) ?></p>
                            <small class="text-muted">
                                Elaboró: <?= esc($v['responsable_elaboracion']) ?> |
                                Aprobó: <?= esc($v['responsable_aprobacion']) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No hay historial de versiones disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
