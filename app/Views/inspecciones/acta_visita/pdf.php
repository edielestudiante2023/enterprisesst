<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        p, h1, h2, h3, h4, h5, h6, table, div { margin: 0; padding: 0; }
        *, *::before, *::after { box-sizing: border-box; }
        br { line-height: 0.5; }

        /* Secciones */
        .seccion { margin-bottom: 8px; }
        .seccion-titulo {
            font-size: 11pt; font-weight: bold; color: #0d6efd;
            border-bottom: 1px solid #e9ecef; padding-bottom: 3px;
            margin-bottom: 5px; margin-top: 8px;
        }
        .seccion-contenido { text-align: justify; line-height: 1.2; }
        .seccion-contenido p { margin: 3px 0; }

        /* Tablas */
        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        /* Tabla datos generales */
        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 15%; }

        /* Listas */
        .seccion-contenido ul { margin: 3px 0 3px 15px; padding-left: 0; }
        .seccion-contenido li { margin-bottom: 2px; }

        /* Texto auxiliar */
        .texto-ok { color: #28a745; font-size: 9pt; }
        .texto-item { font-size: 9pt; padding-left: 12px; }

        /* Pie de documento */
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
                    <tr>
                        <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-007</td>
                    </tr>
                    <tr>
                        <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL ACTA -->
    <table class="datos-general">
        <tr>
            <td class="datos-label">MOTIVO:</td>
            <td><?= esc($acta['motivo']) ?></td>
            <td class="datos-label">HORARIO:</td>
            <td><?= date('h:i A', strtotime($acta['hora_visita'])) ?></td>
        </tr>
        <tr>
            <td class="datos-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="datos-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td>
        </tr>
    </table>

    <!-- 1. INTEGRANTES -->
    <div class="seccion">
        <div class="seccion-titulo">1. INTEGRANTES</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:40%;">NOMBRE</th>
                    <th style="width:30%;">ROL</th>
                    <th style="width:30%;">FIRMA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($integrantes as $integrante): ?>
                <tr>
                    <td><?= esc($integrante['nombre']) ?></td>
                    <td><?= esc($integrante['rol']) ?></td>
                    <td style="text-align:center;">
                        <?php
                        $tipoFirma = null;
                        if (strtoupper($integrante['rol']) === 'CLIENTE') $tipoFirma = 'administrador';
                        elseif (stripos($integrante['rol'], 'CONSULTOR') !== false) $tipoFirma = 'consultor';

                        if ($tipoFirma && !empty($firmas[$tipoFirma])):
                        ?>
                            <img src="<?= $firmas[$tipoFirma] ?>" style="max-height:56px; max-width:168px;">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- TEMAS ABIERTOS Y VENCIDOS -->
    <div class="seccion">
        <div class="seccion-titulo">2. TEMAS ABIERTOS Y VENCIDOS</div>
        <div class="seccion-contenido">
            <p><strong>MANTENIMIENTOS POR VENCER:</strong></p>
            <?php if (empty($mantenimientos)): ?>
                <p class="texto-ok">&#10003; Sin mantenimientos por vencer (proximos 30 dias)</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($mantenimientos as $m): ?>
                    <li><?= esc($m['descripcion_mantenimiento'] ?? 'Mantenimiento') ?> — Vence: <?= date('d/m/Y', strtotime($m['fecha_vencimiento'])) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p><strong>PENDIENTES ABIERTOS:</strong></p>
            <?php if (empty($pendientesAbiertos)): ?>
                <p class="texto-ok">&#10003; Sin pendientes abiertos</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($pendientesAbiertos as $p): ?>
                    <li><?= esc($p['tarea_actividad']) ?> — <?= esc($p['responsable'] ?? '') ?>
                        <?php if (!empty($p['fecha_asignacion'])): ?>
                            (<?= date('d/m/Y', strtotime($p['fecha_asignacion'])) ?>)
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. TEMAS -->
    <?php if (!empty($temas)): ?>
    <div class="seccion">
        <div class="seccion-titulo">3. TEMAS</div>
        <div class="seccion-contenido">
            <?php foreach ($temas as $i => $tema): ?>
                <p><strong>TEMA <?= $i + 1 ?>:</strong> <?= esc($tema['descripcion']) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ACTIVIDADES PTA GESTIONADAS -->
    <?php if (!empty($ptaCerradas)): ?>
    <div class="seccion">
        <div class="seccion-titulo">4. ACTIVIDADES PTA GESTIONADAS</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:15%;">NUMERAL</th>
                    <th style="width:50%;">ACTIVIDAD</th>
                    <th style="width:20%;">FECHA PROPUESTA</th>
                    <th style="width:15%;">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ptaCerradas as $pta): ?>
                <tr>
                    <td style="text-align:center;"><?= esc($pta['numeral_plandetrabajo'] ?? '') ?></td>
                    <td><?= esc($pta['actividad_plandetrabajo'] ?? '') ?></td>
                    <td style="text-align:center;"><?= !empty($pta['fecha_propuesta']) ? date('d/m/Y', strtotime($pta['fecha_propuesta'])) : '—' ?></td>
                    <td style="text-align:center; color:#28a745; font-weight:bold;">CERRADA</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- 5. OBSERVACIONES -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">5. OBSERVACIONES</div>
        <div class="seccion-contenido"><?= nl2br(esc($acta['observaciones'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- 6. CARTERA -->
    <?php if (!empty($acta['cartera'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">6. CARTERA</div>
        <div class="seccion-contenido"><?= nl2br(esc($acta['cartera'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- 7. COMPROMISOS -->
    <?php if (!empty($compromisos)): ?>
    <div class="seccion">
        <div class="seccion-titulo">7. COMPROMISOS</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:50%;">ACTIVIDAD</th>
                    <th style="width:25%;">FECHA DE CIERRE</th>
                    <th style="width:25%;">RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compromisos as $comp): ?>
                <tr>
                    <td><?= esc($comp['tarea_actividad']) ?></td>
                    <td style="text-align:center;"><?= !empty($comp['fecha_cierre']) ? date('d/m/Y', strtotime($comp['fecha_cierre'])) : '—' ?></td>
                    <td><?= esc($comp['responsable'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- FIRMAS -->
    <div style="margin-top: 25px;">
        <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            FIRMAS DE APROBACION
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <?php
                $firmaSlots = [
                    ['tipo' => 'administrador', 'label' => 'CLIENTE'],
                    ['tipo' => 'consultor', 'label' => 'CONSULTOR CYCLOID TALENT'],
                ];
                foreach ($firmaSlots as $slot):
                    $tipoKey = $slot['tipo'];
                    $nombreFirmante = '';
                    if ($tipoKey === 'consultor') {
                        $nombreFirmante = $consultor['nombre_consultor'] ?? '';
                    } else {
                        foreach ($integrantes as $int) {
                            if (strtoupper($int['rol']) === 'CLIENTE') {
                                $nombreFirmante = $int['nombre'];
                                break;
                            }
                        }
                    }
                ?>
                <th style="width: 50%; background-color: #e9ecef; color: #333;"><?= $slot['label'] ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($firmaSlots as $slot):
                    $tipoKey = $slot['tipo'];
                    $nombreFirmante = '';
                    if ($tipoKey === 'consultor') {
                        $nombreFirmante = $consultor['nombre_consultor'] ?? '';
                    } else {
                        foreach ($integrantes as $int) {
                            if (strtoupper($int['rol']) === 'CLIENTE') {
                                $nombreFirmante = $int['nombre'];
                                break;
                            }
                        }
                    }
                ?>
                <td style="vertical-align: top; padding: 12px; height: 100px;">
                    <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= esc($nombreFirmante) ?></div>
                    <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= $slot['label'] ?></div>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($firmaSlots as $slot): ?>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <?php if (!empty($firmas[$slot['tipo']])): ?>
                        <img src="<?= $firmas[$slot['tipo']] ?>" style="max-height: 56px; max-width: 168px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>

    <!-- PIE DE DOCUMENTO -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
    </div>

</body>
</html>
