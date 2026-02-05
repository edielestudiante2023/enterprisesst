<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Acciones Correctivas</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table { border-collapse: collapse; }
        th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }
        th, td { border: 1px solid #333; padding: 5px 10px; }
        .header { font-size: 16px; font-weight: bold; }
        .subheader { font-size: 12px; color: #666; }
        .numero { text-align: center; }
        .fecha { text-align: center; }
        .vencida { background-color: #ffe6e6; }
    </style>
</head>
<body>
    <table>
        <!-- Encabezado -->
        <tr>
            <td colspan="12" class="header">MATRIZ DE ACCIONES CORRECTIVAS, PREVENTIVAS Y DE MEJORA</td>
        </tr>
        <tr>
            <td colspan="12" class="subheader">Cliente: <?= esc($cliente['nombre_cliente']) ?> | NIT: <?= esc($cliente['nit_cliente']) ?></td>
        </tr>
        <tr>
            <td colspan="12" class="subheader">Fecha de generacion: <?= date('d/m/Y H:i') ?></td>
        </tr>
        <tr><td colspan="12"></td></tr>

        <!-- Encabezados de columnas -->
        <tr>
            <th>ID</th>
            <th>Numeral</th>
            <th>Hallazgo</th>
            <th>Descripcion Accion</th>
            <th>Tipo</th>
            <th>Clasificacion</th>
            <th>Responsable</th>
            <th>Fecha Asignacion</th>
            <th>Fecha Compromiso</th>
            <th>Fecha Cierre</th>
            <th>Estado</th>
            <th>Causa Raiz</th>
        </tr>

        <!-- Datos -->
        <?php if (!empty($acciones)): ?>
        <?php foreach ($acciones as $a): ?>
        <?php
        $vencida = !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                   isset($a['fecha_compromiso']) && $a['fecha_compromiso'] < date('Y-m-d');
        ?>
        <tr class="<?= $vencida ? 'vencida' : '' ?>">
            <td class="numero"><?= $a['id_accion'] ?></td>
            <td class="numero"><?= $a['numeral_asociado'] ?? '-' ?></td>
            <td><?= esc($a['hallazgo_titulo'] ?? 'Sin titulo') ?></td>
            <td><?= esc($a['descripcion_accion']) ?></td>
            <td><?= ucfirst($a['tipo_accion']) ?></td>
            <td><?= ucwords(str_replace('_', ' ', $a['clasificacion_temporal'] ?? '-')) ?></td>
            <td><?= esc($a['responsable_usuario_nombre'] ?? $a['responsable_nombre'] ?? '-') ?></td>
            <td class="fecha"><?= !empty($a['fecha_asignacion']) ? date('d/m/Y', strtotime($a['fecha_asignacion'])) : '-' ?></td>
            <td class="fecha"><?= !empty($a['fecha_compromiso']) ? date('d/m/Y', strtotime($a['fecha_compromiso'])) : '-' ?></td>
            <td class="fecha"><?= !empty($a['fecha_cierre_real']) ? date('d/m/Y', strtotime($a['fecha_cierre_real'])) : '-' ?></td>
            <td><?= ucwords(str_replace('_', ' ', $a['estado'])) ?></td>
            <td><?= esc($a['causa_raiz_identificada'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="12" style="text-align: center;">No hay acciones registradas</td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Segunda tabla: Resumen -->
    <table style="margin-top: 30px;">
        <tr><td colspan="4"></td></tr>
        <tr>
            <td colspan="4" class="header">RESUMEN ESTADISTICO</td>
        </tr>
        <tr>
            <th>Indicador</th>
            <th>Valor</th>
            <th>Descripcion</th>
            <th>Meta</th>
        </tr>
        <tr>
            <td>Total Acciones</td>
            <td class="numero"><?= count($acciones) ?></td>
            <td>Acciones registradas en el periodo</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Acciones Correctivas</td>
            <td class="numero"><?= count(array_filter($acciones, fn($a) => $a['tipo_accion'] === 'correctiva')) ?></td>
            <td>Acciones de tipo correctiva</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Acciones Preventivas</td>
            <td class="numero"><?= count(array_filter($acciones, fn($a) => $a['tipo_accion'] === 'preventiva')) ?></td>
            <td>Acciones de tipo preventiva</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Acciones de Mejora</td>
            <td class="numero"><?= count(array_filter($acciones, fn($a) => $a['tipo_accion'] === 'mejora')) ?></td>
            <td>Acciones de tipo mejora</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Acciones Cerradas Efectivas</td>
            <td class="numero"><?= count(array_filter($acciones, fn($a) => $a['estado'] === 'cerrada_efectiva')) ?></td>
            <td>Cerradas con efectividad verificada</td>
            <td>&gt;80%</td>
        </tr>
        <tr>
            <td>Acciones Vencidas</td>
            <td class="numero"><?= count(array_filter($acciones, fn($a) =>
                !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                isset($a['fecha_compromiso']) && $a['fecha_compromiso'] < date('Y-m-d')
            )) ?></td>
            <td>Acciones fuera de plazo</td>
            <td>0</td>
        </tr>
    </table>
</body>
</html>
