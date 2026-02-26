<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }

        /* Encabezado */
        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 8px; vertical-align: middle; }
        .header-logo { width: 80px; text-align: center; }
        .header-logo img { max-width: 70px; max-height: 50px; }
        .header-title { text-align: center; font-size: 10px; font-weight: bold; line-height: 1.3; }
        .header-meta { width: 140px; font-size: 9px; }
        .header-meta-row { border-bottom: 1px solid #ccc; padding: 2px 0; }

        /* Título principal */
        .titulo-principal { text-align: center; font-size: 13px; font-weight: bold; margin: 12px 0 8px; color: #1c2437; }

        /* Datos del acta */
        .datos-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .datos-table td { padding: 3px 8px; font-size: 11px; }
        .datos-label { font-weight: bold; color: #555; width: 20%; }

        /* Secciones */
        .seccion { margin-bottom: 10px; }
        .seccion-titulo { background: #1c2437; color: #fff; padding: 4px 10px; font-size: 11px; font-weight: bold; margin-bottom: 4px; }

        /* Tablas de contenido */
        .tabla-contenido { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .tabla-contenido th { background: #e8e0d0; border: 1px solid #ccc; padding: 4px 8px; font-size: 10px; text-align: left; font-weight: bold; }
        .tabla-contenido td { border: 1px solid #ccc; padding: 4px 8px; font-size: 10px; }

        /* Temas abiertos */
        .tema-abierto { margin-bottom: 6px; }
        .tema-abierto-titulo { font-weight: bold; font-size: 10px; color: #555; margin-bottom: 2px; }
        .tema-abierto-ok { color: #28a745; font-size: 10px; }
        .tema-abierto-item { font-size: 10px; padding-left: 12px; }

        /* Firmas */
        .firmas-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .firmas-table td { text-align: center; padding: 8px 10px; vertical-align: bottom; width: 33%; }
        .firma-img { max-width: 150px; max-height: 60px; }
        .firma-nombre { font-size: 9px; border-top: 1px solid #333; padding-top: 3px; margin-top: 4px; font-weight: bold; }
        .firma-rol { font-size: 8px; color: #666; }

        /* Lista de items */
        .item-list { padding-left: 15px; margin: 4px 0; }
        .item-list li { font-size: 10px; margin-bottom: 2px; }

        /* Texto general */
        .texto-contenido { font-size: 10px; padding: 4px 8px; border: 1px solid #eee; background: #fafafa; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="3">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <span style="font-size:8px; color:#999;">SIN LOGO</span>
                <?php endif; ?>
            </td>
            <td class="header-title" rowspan="3">
                SISTEMA DE GESTION DE<br>
                SEGURIDAD Y SALUD EN EL TRABAJO<br><br>
                ACTA DE REUNION
            </td>
            <td class="header-meta">
                <div class="header-meta-row"><strong>Codigo:</strong> FT-SST-007</div>
            </td>
        </tr>
        <tr>
            <td class="header-meta">
                <div class="header-meta-row"><strong>Version:</strong> 001</div>
            </td>
        </tr>
        <tr>
            <td class="header-meta">
                <div><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></div>
            </td>
        </tr>
    </table>

    <!-- TITULO PRINCIPAL -->
    <div class="titulo-principal">ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA</div>

    <!-- DATOS DEL ACTA -->
    <table class="datos-table">
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
                        if (strtoupper($integrante['rol']) === 'ADMINISTRADOR') $tipoFirma = 'administrador';
                        elseif (stripos($integrante['rol'], 'VIG') !== false) $tipoFirma = 'vigia';
                        elseif (stripos($integrante['rol'], 'CONSULTOR') !== false) $tipoFirma = 'consultor';

                        if ($tipoFirma && !empty($firmas[$tipoFirma])):
                        ?>
                            <img src="<?= $firmas[$tipoFirma] ?>" class="firma-img">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- TEMAS ABIERTOS Y VENCIDOS -->
    <div class="seccion">
        <div class="seccion-titulo">TEMAS ABIERTOS Y VENCIDOS</div>

        <!-- Mantenimientos -->
        <div class="tema-abierto">
            <div class="tema-abierto-titulo">MANTENIMIENTOS POR VENCER:</div>
            <?php if (empty($mantenimientos)): ?>
                <div class="tema-abierto-ok">&#10003; Sin mantenimientos por vencer (proximos 30 dias)</div>
            <?php else: ?>
                <ul class="item-list">
                    <?php foreach ($mantenimientos as $m): ?>
                    <li><?= esc($m['descripcion_mantenimiento'] ?? 'Mantenimiento') ?> — Vence: <?= date('d/m/Y', strtotime($m['fecha_vencimiento'])) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Pendientes -->
        <div class="tema-abierto">
            <div class="tema-abierto-titulo">PENDIENTES ABIERTOS:</div>
            <?php if (empty($pendientesAbiertos)): ?>
                <div class="tema-abierto-ok">&#10003; Sin pendientes abiertos</div>
            <?php else: ?>
                <ul class="item-list">
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
        <div class="seccion-titulo">2. TEMAS</div>
        <?php foreach ($temas as $i => $tema): ?>
            <div style="font-size:10px; padding:3px 8px;">
                <strong>TEMA <?= $i + 1 ?>:</strong> <?= esc($tema['descripcion']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 4. OBSERVACIONES -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">4. OBSERVACIONES</div>
        <div class="texto-contenido"><?= nl2br(esc($acta['observaciones'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- 5. CARTERA -->
    <?php if (!empty($acta['cartera'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">5. CARTERA</div>
        <div class="texto-contenido"><?= nl2br(esc($acta['cartera'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- 6. COMPROMISOS -->
    <?php if (!empty($compromisos)): ?>
    <div class="seccion">
        <div class="seccion-titulo">6. COMPROMISOS</div>
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
                    <td><?= !empty($comp['fecha_cierre']) ? date('d/m/Y', strtotime($comp['fecha_cierre'])) : '—' ?></td>
                    <td><?= esc($comp['responsable'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- FIRMAS FINALES -->
    <table class="firmas-table">
        <tr>
            <?php
            $firmaSlots = [
                ['tipo' => 'administrador', 'label' => 'ADMINISTRADOR'],
                ['tipo' => 'vigia', 'label' => 'VIGIA SST'],
                ['tipo' => 'consultor', 'label' => 'CONSULTOR'],
            ];
            foreach ($firmaSlots as $slot):
                $tipoKey = $slot['tipo'];
                // Buscar nombre del integrante con este rol
                $nombreFirmante = '';
                if ($tipoKey === 'consultor') {
                    $nombreFirmante = $consultor['nombre_consultor'] ?? '';
                } else {
                    foreach ($integrantes as $int) {
                        if ($tipoKey === 'administrador' && strtoupper($int['rol']) === 'ADMINISTRADOR') {
                            $nombreFirmante = $int['nombre'];
                            break;
                        }
                        if ($tipoKey === 'vigia' && stripos($int['rol'], 'VIG') !== false) {
                            $nombreFirmante = $int['nombre'];
                            break;
                        }
                    }
                }
            ?>
            <td>
                <?php if (!empty($firmas[$tipoKey])): ?>
                    <img src="<?= $firmas[$tipoKey] ?>" class="firma-img"><br>
                <?php else: ?>
                    <div style="height:50px;"></div>
                <?php endif; ?>
                <div class="firma-nombre"><?= esc($nombreFirmante) ?></div>
                <div class="firma-rol"><?= $slot['label'] ?></div>
            </td>
            <?php endforeach; ?>
        </tr>
    </table>

</body>
</html>
