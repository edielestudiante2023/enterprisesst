<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 70px 80px 90px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            padding: 15px 20px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 6px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 140px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 4px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 2px 4px; font-size: 8px; text-align: center; vertical-align: middle; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 4px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 3px 0 2px; }

        .val-bueno { color: #155724; }
        .val-regular { color: #856404; }
        .val-malo { color: #721c24; }
        .val-na { color: #6c757d; }

        .foto-inline { max-width: 140px; max-height: 100px; border: 1px solid #ccc; }
        .foto-small { max-width: 100px; max-height: 70px; border: 1px solid #ccc; }

        .pregunta-si { color: #155724; font-weight: bold; }
        .pregunta-no { color: #721c24; font-weight: bold; }

        .pendientes-box { border: 1px solid #ccc; padding: 6px 8px; font-size: 8px; line-height: 1.5; background: #fefefe; }
    </style>
</head>
<body>

    <!-- HEADER CORPORATIVO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-206<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">FORMATO INSPECCION DE BOTIQUIN TIPO B</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INSPECCION DE BOTIQUIN TIPO B<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION: NTC 4198 -->
    <div class="section-title">FUNDAMENTACION</div>
    <p class="intro-text">
        Fundamentacion de la obligatoriedad de botiquines tipo B en empresas en Colombia:
    </p>
    <p class="intro-subtitle">1. Perspectiva de salud publica:</p>
    <p class="intro-text">
        La atencion oportuna y eficaz de lesiones y urgencias medicas puede disminuir la morbilidad y mortalidad. Un botiquin tipo B bien dotado permite prevenir complicaciones derivadas de lesiones o urgencias.
    </p>
    <p class="intro-subtitle">2. Enfoque de seguridad y bienestar:</p>
    <p class="intro-text">
        Un botiquin tipo B bien dotado minimiza los riesgos asociados a accidentes, lesiones o urgencias medicas en la empresa.
    </p>
    <p class="intro-subtitle">3. Aspectos legales y normativos:</p>
    <p class="intro-text">
        La normativa colombiana (Decreto 1072 de 2015, Resolucion 0312 de 2019) establece la obligatoriedad de contar con un botiquin tipo B en empresas como requisito minimo de seguridad y salud en el trabajo.
    </p>
    <p class="intro-subtitle">4. Consideraciones eticas:</p>
    <p class="intro-text">
        La disponibilidad de un botiquin tipo B garantiza el derecho fundamental a la salud de los trabajadores y visitantes de la empresa.
    </p>
    <p class="intro-subtitle">5. Fundamentacion cientifica:</p>
    <p class="intro-text">
        La Norma NTC 4198 establece los requisitos de dotacion y funcionamiento de los botiquines de primeros auxilios, garantizando su calidad, eficacia y seguridad.
    </p>

    <!-- DATOS GENERALES -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
            <td class="info-label">UBICACION:</td>
            <td><?= esc($inspeccion['ubicacion_botiquin'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">TIPO BOTIQUIN:</td>
            <td><?= esc($inspeccion['tipo_botiquin'] ?? 'LONA') ?></td>
            <td class="info-label">ESTADO BOTIQUIN:</td>
            <td><?= esc($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?></td>
        </tr>
    </table>

    <!-- FOTOS DEL BOTIQUÍN -->
    <?php if (!empty($fotosBase64['foto_1']) || !empty($fotosBase64['foto_2'])): ?>
    <table style="width:100%; margin-bottom:8px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_1'])): ?>
            <td style="text-align:center; width:50%;">
                <img src="<?= $fotosBase64['foto_1'] ?>" class="foto-inline"><br>
                <span style="font-size:7px; color:#888;">Foto 1</span>
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_2'])): ?>
            <td style="text-align:center; width:50%;">
                <img src="<?= $fotosBase64['foto_2'] ?>" class="foto-inline"><br>
                <span style="font-size:7px; color:#888;">Foto 2</span>
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- CONDICIONES GENERALES (4 preguntas SI/NO) -->
    <div class="section-title">CONDICIONES GENERALES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Instalado en la pared?</td>
            <td class="<?= ($inspeccion['instalado_pared'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['instalado_pared'] ?? 'SI') ?></td>
            <td class="info-label">Libre de obstaculos?</td>
            <td class="<?= ($inspeccion['libre_obstaculos'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['libre_obstaculos'] ?? 'SI') ?></td>
        </tr>
        <tr>
            <td class="info-label">Localizado visible?</td>
            <td class="<?= ($inspeccion['lugar_visible'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['lugar_visible'] ?? 'SI') ?></td>
            <td class="info-label">Con senalizacion?</td>
            <td class="<?= ($inspeccion['con_senalizacion'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['con_senalizacion'] ?? 'SI') ?></td>
        </tr>
    </table>

    <!-- TABLA DE ELEMENTOS -->
<div class="section-title">ELEMENTOS DEL BOTIQUIN (32 items - NTC 4198)</div>

    <?php
    use App\Controllers\Inspecciones\InspeccionBotiquinController;
    $hoy = date('Y-m-d');

    // Agrupar por grupo
    $gruposPdf = [];
    foreach ($elementos as $clave => $config) {
        $gruposPdf[$config['grupo']][$clave] = $config;
    }

    $colorEstado = function($estado) {
        if ($estado === 'BUEN ESTADO') return 'val-bueno';
        if ($estado === 'ESTADO REGULAR') return 'val-regular';
        if (in_array($estado, ['MAL ESTADO', 'SIN EXISTENCIAS', 'VENCIDO'])) return 'val-malo';
        return 'val-na';
    };
    ?>

    <?php foreach ($gruposPdf as $grupoNombre => $items): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th colspan="<?= ($grupoNombre === 'Antisepticos y soluciones') ? 5 : 4 ?>" style="text-align:left; font-size:9px; background:#d1d5db;">
                    <?= esc($grupoNombre) ?>
                </th>
            </tr>
            <tr>
                <th style="width:45%; text-align:left;">Elemento</th>
                <th style="width:10%;">Cant.</th>
                <th style="width:10%;">Min.</th>
                <th style="width:20%;">Estado</th>
                <?php if ($grupoNombre === 'Antisepticos y soluciones'): ?>
                <th style="width:15%;">Vencimiento</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $clave => $config):
                $data = $elementosData[$clave] ?? null;
                $cantidad = (int)($data['cantidad'] ?? 0);
                $estado = $data['estado'] ?? 'SIN EXISTENCIAS';
                $cantOk = $cantidad >= $config['min'];
            ?>
            <tr>
                <td style="text-align:left;"><?= esc($config['label']) ?></td>
                <td style="font-weight:bold; <?= $cantOk ? 'color:#155724;' : 'color:#721c24;' ?>"><?= $cantidad ?></td>
                <td style="color:#888;"><?= $config['min'] ?></td>
                <td class="<?= $colorEstado($estado) ?>"><?= esc($estado) ?></td>
                <?php if ($config['venc']): ?>
                <td>
                    <?php if (!empty($data['fecha_vencimiento'])): ?>
                        <?php $vencido = $data['fecha_vencimiento'] < $hoy; ?>
                        <span style="<?= $vencido ? 'color:#721c24; font-weight:bold;' : '' ?>"><?= date('d/m/Y', strtotime($data['fecha_vencimiento'])) ?></span>
                    <?php else: ?>
                        <span style="color:#888;">-</span>
                    <?php endif; ?>
                </td>
                <?php elseif ($grupoNombre === 'Antisepticos y soluciones'): ?>
                <td>-</td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>

    <!-- EQUIPOS ESPECIALES -->
    <div class="section-title">EQUIPOS ESPECIALES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Estado collares cervicales</td>
            <td class="<?= $colorEstado($inspeccion['estado_collares'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_collares'] ?? 'BUEN ESTADO') ?></td>
            <td class="info-label">Estado inmovilizadores</td>
            <td class="<?= $colorEstado($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') ?></td>
        </tr>
        <?php if (!empty($inspeccion['obs_tabla_espinal'])): ?>
        <tr>
            <td class="info-label">Obs. tabla espinal</td>
            <td colspan="3"><?= esc($inspeccion['obs_tabla_espinal']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Fotos equipos especiales -->
    <?php
    $fotosEquipos = ['foto_tabla_espinal' => 'Tabla espinal', 'foto_collares' => 'Collares', 'foto_inmovilizadores' => 'Inmovilizadores'];
    $hayFotosEquipos = false;
    foreach ($fotosEquipos as $campo => $label) {
        if (!empty($fotosBase64[$campo])) { $hayFotosEquipos = true; break; }
    }
    ?>
    <?php if ($hayFotosEquipos): ?>
    <table style="width:100%; margin-top:6px; margin-bottom:8px;">
        <tr>
            <?php foreach ($fotosEquipos as $campo => $label): ?>
                <?php if (!empty($fotosBase64[$campo])): ?>
                <td style="text-align:center; width:33%;">
                    <img src="<?= $fotosBase64[$campo] ?>" class="foto-small"><br>
                    <span style="font-size:7px; color:#888;"><?= $label ?></span>
                </td>
                <?php endif; ?>
            <?php endforeach; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- RECOMENDACIONES -->
    <?php if (!empty($inspeccion['recomendaciones'])): ?>
    <div class="section-title">RECOMENDACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['recomendaciones'])) ?></p>
    <?php endif; ?>

    <!-- PENDIENTES GENERADOS -->
    <?php if (!empty($inspeccion['pendientes_generados'])): ?>
    <div class="section-title">COMPRA DE ELEMENTOS REQUERIDOS / PENDIENTES</div>
    <div class="pendientes-box">
        <?= nl2br(esc($inspeccion['pendientes_generados'])) ?>
    </div>
    <?php endif; ?>

</body>
</html>
