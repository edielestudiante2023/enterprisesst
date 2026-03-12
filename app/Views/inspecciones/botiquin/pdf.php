<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 2cm 1.5cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        br { line-height: 0.5; }

        .seccion { margin-bottom: 8px; }
        .seccion-titulo {
            font-size: 11pt; font-weight: bold; color: #0d6efd;
            border-bottom: 1px solid #e9ecef; padding-bottom: 3px;
            margin-bottom: 5px; margin-top: 8px;
        }
        .seccion-contenido { text-align: justify; line-height: 1.2; }
        .seccion-contenido p { margin: 3px 0; }

        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 20%; }

        .foto-inline { max-width: 140px; max-height: 100px; border: 1px solid #ccc; }
        .foto-small { max-width: 100px; max-height: 70px; border: 1px solid #ccc; }

        .val-bueno { color: #155724; }
        .val-regular { color: #856404; }
        .val-malo { color: #721c24; }
        .val-na { color: #6c757d; }

        .pregunta-si { color: #155724; font-weight: bold; }
        .pregunta-no { color: #721c24; font-weight: bold; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>

    <!-- ENCABEZADO ESTANDAR -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;" cellpadding="0" cellspacing="0">
        <tr>
            <td rowspan="2" style="width:100px; border:1px solid #333; padding:8px; text-align:center; vertical-align:middle; background:#fff;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
                <?php else: ?>
                    <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-206</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    FORMATO INSPECCION DE BOTIQUIN TIPO B
                </div>
            </td>
        </tr>
    </table>

    <!-- FUNDAMENTACION -->
    <div class="seccion">
        <div class="seccion-titulo">FUNDAMENTACION</div>
        <div class="seccion-contenido">
            <p>Fundamentacion de la obligatoriedad de botiquines tipo B en empresas en Colombia:</p>
            <p><strong>1. Perspectiva de salud publica:</strong> La atencion oportuna y eficaz de lesiones y urgencias medicas puede disminuir la morbilidad y mortalidad. Un botiquin tipo B bien dotado permite prevenir complicaciones derivadas de lesiones o urgencias.</p>
            <p><strong>2. Enfoque de seguridad y bienestar:</strong> Un botiquin tipo B bien dotado minimiza los riesgos asociados a accidentes, lesiones o urgencias medicas en la empresa.</p>
            <p><strong>3. Aspectos legales y normativos:</strong> La normativa colombiana (Decreto 1072 de 2015, Resolucion 0312 de 2019) establece la obligatoriedad de contar con un botiquin tipo B en empresas como requisito minimo de seguridad y salud en el trabajo.</p>
            <p><strong>4. Consideraciones eticas:</strong> La disponibilidad de un botiquin tipo B garantiza el derecho fundamental a la salud de los trabajadores y visitantes de la empresa.</p>
            <p><strong>5. Fundamentacion cientifica:</strong> La Norma NTC 4198 establece los requisitos de dotacion y funcionamiento de los botiquines de primeros auxilios, garantizando su calidad, eficacia y seguridad.</p>
        </div>
    </div>

    <!-- DATOS GENERALES -->
    <div class="seccion">
        <div class="seccion-titulo">DATOS DE LA INSPECCION</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">CLIENTE:</td>
                <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
                <td class="datos-label">FECHA:</td>
                <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
            </tr>
            <tr>
                <td class="datos-label">CONSULTOR:</td>
                <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
                <td class="datos-label">UBICACION:</td>
                <td><?= esc($inspeccion['ubicacion_botiquin'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="datos-label">TIPO BOTIQUIN:</td>
                <td><?= esc($inspeccion['tipo_botiquin'] ?? 'LONA') ?></td>
                <td class="datos-label">ESTADO BOTIQUIN:</td>
                <td><?= esc($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?></td>
            </tr>
        </table>
    </div>

    <!-- FOTOS DEL BOTIQUIN -->
    <?php if (!empty($fotosBase64['foto_1']) || !empty($fotosBase64['foto_2'])): ?>
    <table style="width:100%; margin-bottom:8px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_1'])): ?>
            <td style="text-align:center; width:50%;">
                <img src="<?= $fotosBase64['foto_1'] ?>" class="foto-inline"><br>
                <span style="font-size:7pt; color:#888;">Foto 1</span>
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_2'])): ?>
            <td style="text-align:center; width:50%;">
                <img src="<?= $fotosBase64['foto_2'] ?>" class="foto-inline"><br>
                <span style="font-size:7pt; color:#888;">Foto 2</span>
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- CONDICIONES GENERALES -->
    <div class="seccion">
        <div class="seccion-titulo">CONDICIONES GENERALES</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">Instalado en la pared?</td>
                <td class="<?= ($inspeccion['instalado_pared'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['instalado_pared'] ?? 'SI') ?></td>
                <td class="datos-label">Libre de obstaculos?</td>
                <td class="<?= ($inspeccion['libre_obstaculos'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['libre_obstaculos'] ?? 'SI') ?></td>
            </tr>
            <tr>
                <td class="datos-label">Localizado visible?</td>
                <td class="<?= ($inspeccion['lugar_visible'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['lugar_visible'] ?? 'SI') ?></td>
                <td class="datos-label">Con senalizacion?</td>
                <td class="<?= ($inspeccion['con_senalizacion'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['con_senalizacion'] ?? 'SI') ?></td>
            </tr>
        </table>
    </div>

    <!-- TABLA DE ELEMENTOS -->
    <div class="seccion">
        <div class="seccion-titulo">ELEMENTOS DEL BOTIQUIN (32 items - NTC 4198)</div>

        <?php
        use App\Controllers\Inspecciones\InspeccionBotiquinController;
        $hoy = date('Y-m-d');

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
        <table class="tabla-contenido" style="margin:5px 0;">
            <thead>
                <tr>
                    <th colspan="<?= ($grupoNombre === 'Antisepticos y soluciones') ? 5 : 4 ?>" style="text-align:left; background-color:#e9ecef; color:#333;">
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
                    <td style="text-align:center; font-weight:bold; <?= $cantOk ? 'color:#155724;' : 'color:#721c24;' ?>"><?= $cantidad ?></td>
                    <td style="text-align:center; color:#888;"><?= $config['min'] ?></td>
                    <td style="text-align:center;" class="<?= $colorEstado($estado) ?>"><?= esc($estado) ?></td>
                    <?php if ($config['venc']): ?>
                    <td style="text-align:center;">
                        <?php if (!empty($data['fecha_vencimiento'])): ?>
                            <?php $vencido = $data['fecha_vencimiento'] < $hoy; ?>
                            <span style="<?= $vencido ? 'color:#721c24; font-weight:bold;' : '' ?>"><?= date('d/m/Y', strtotime($data['fecha_vencimiento'])) ?></span>
                        <?php else: ?>
                            <span style="color:#888;">-</span>
                        <?php endif; ?>
                    </td>
                    <?php elseif ($grupoNombre === 'Antisepticos y soluciones'): ?>
                    <td style="text-align:center;">-</td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endforeach; ?>
    </div>

    <!-- EQUIPOS ESPECIALES -->
    <div class="seccion">
        <div class="seccion-titulo">EQUIPOS ESPECIALES</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">Estado collares cervicales</td>
                <td class="<?= $colorEstado($inspeccion['estado_collares'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_collares'] ?? 'BUEN ESTADO') ?></td>
                <td class="datos-label">Estado inmovilizadores</td>
                <td class="<?= $colorEstado($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') ?></td>
            </tr>
            <?php if (!empty($inspeccion['obs_tabla_espinal'])): ?>
            <tr>
                <td class="datos-label">Obs. tabla espinal</td>
                <td colspan="3"><?= esc($inspeccion['obs_tabla_espinal']) ?></td>
            </tr>
            <?php endif; ?>
        </table>

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
                        <span style="font-size:7pt; color:#888;"><?= $label ?></span>
                    </td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        </table>
        <?php endif; ?>
    </div>

    <!-- RECOMENDACIONES -->
    <?php if (!empty($inspeccion['recomendaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">RECOMENDACIONES</div>
        <div class="seccion-contenido"><?= nl2br(esc($inspeccion['recomendaciones'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- PENDIENTES -->
    <?php if (!empty($inspeccion['pendientes_generados'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">COMPRA DE ELEMENTOS REQUERIDOS / PENDIENTES</div>
        <div class="seccion-contenido"><?= nl2br(esc($inspeccion['pendientes_generados'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- PIE DE DOCUMENTO -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
    </div>

</body>
</html>
