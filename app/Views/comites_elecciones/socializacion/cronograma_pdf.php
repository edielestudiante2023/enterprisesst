<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cronograma <?= esc($tipoComiteCorto) ?> <?= esc($anio ?? '') ?> — <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <style>
        @page { margin: 25px 30px; }
        * { box-sizing: border-box; }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1c2437;
            margin: 0;
            padding: 0;
        }
        .barra-superior {
            height: 6px;
            background: #c8102e;
            margin-bottom: 10px;
        }
        .header {
            margin-bottom: 18px;
        }
        .header table {
            width: 100%;
            border: 0;
        }
        .header td {
            vertical-align: middle;
        }
        .titulo-col h1 {
            font-size: 22px;
            font-weight: bold;
            color: #1c2437;
            margin: 0;
            line-height: 1.2;
        }
        .titulo-col .anio {
            font-size: 18px;
            color: #c8102e;
            font-weight: bold;
            margin-top: 2px;
        }
        .logo-col {
            text-align: right;
        }
        .logo-col img {
            max-height: 70px;
            max-width: 180px;
        }

        .mensaje {
            background: #f8f9fa;
            border-left: 4px solid #c8102e;
            padding: 14px 18px;
            margin: 12px 0 18px 0;
            font-size: 11px;
            line-height: 1.5;
        }
        .mensaje p { margin: 0 0 8px 0; }
        .mensaje p:last-child { margin: 0; }

        .tabla-cronograma {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .tabla-cronograma th {
            background: #c8102e;
            color: #fff;
            padding: 8px 6px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #1c2437;
        }
        .tabla-cronograma td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: center;
            font-size: 11px;
            background: #fff;
        }
        .tabla-cronograma tr:nth-child(even) td {
            background: #f9f9f9;
        }

        .col-2 {
            width: 100%;
        }
        .col-2 td.cell {
            width: 50%;
            vertical-align: top;
            padding: 0 6px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        .vacio {
            text-align: center;
            font-style: italic;
            color: #6c757d;
            background: #f5f5f5;
            border-radius: 4px;
            padding: 18px;
        }
    </style>
</head>
<body>

<div class="barra-superior"></div>

<div class="header">
    <table>
        <tr>
            <td class="titulo-col" style="width: 65%;">
                <h1>CRONOGRAMA DE REUNIONES<br><?= esc($tipoComiteCorto) ?></h1>
                <div class="anio"><?= esc($anio) ?></div>
            </td>
            <td class="logo-col" style="width: 35%;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo">
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<div class="mensaje">
    <?php
    $mensaje = $mensajeCronograma ?? '';
    foreach (explode("\n\n", $mensaje) as $parrafo):
        $parrafo = trim($parrafo);
        if ($parrafo === '') continue;
    ?>
        <p><?= nl2br(esc($parrafo)) ?></p>
    <?php endforeach; ?>
</div>

<?php
$reuniones = $reuniones ?? [];
$mesEsMap = [
    'January'=>'ENERO','February'=>'FEBRERO','March'=>'MARZO','April'=>'ABRIL',
    'May'=>'MAYO','June'=>'JUNIO','July'=>'JULIO','August'=>'AGOSTO',
    'September'=>'SEPTIEMBRE','October'=>'OCTUBRE','November'=>'NOVIEMBRE','December'=>'DICIEMBRE',
];
if (empty($reuniones)):
?>
    <div class="vacio">No se han registrado fechas en el cronograma.</div>
<?php else:
    // Formatear cada reunion: convertir fecha y hora
    $filas = [];
    foreach ($reuniones as $r) {
        $fecha = !empty($r['fecha']) ? date('d/m/Y', strtotime($r['fecha'])) : '';
        $hora  = !empty($r['hora'])  ? date('g:i A', strtotime($r['hora']))  : '';
        $mesIngles = !empty($r['fecha']) ? date('F', strtotime($r['fecha'])) : '';
        $mes   = $mesEsMap[$mesIngles] ?? $mesIngles;
        $filas[] = ['mes' => $mes, 'fecha' => $fecha, 'hora' => $hora];
    }
    $total = count($filas);
    $mitad = (int) ceil($total / 2);
    $col1 = array_slice($filas, 0, $mitad);
    $col2 = array_slice($filas, $mitad);
?>
    <table class="col-2">
        <tr>
            <td class="cell">
                <table class="tabla-cronograma">
                    <thead><tr><th>MES</th><th>FECHA</th><th>HORA</th></tr></thead>
                    <tbody>
                    <?php foreach ($col1 as $f): ?>
                        <tr>
                            <td><?= esc($f['mes']) ?></td>
                            <td><?= esc($f['fecha']) ?></td>
                            <td><?= esc($f['hora']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </td>
            <td class="cell">
                <?php if (!empty($col2)): ?>
                <table class="tabla-cronograma">
                    <thead><tr><th>MES</th><th>FECHA</th><th>HORA</th></tr></thead>
                    <tbody>
                    <?php foreach ($col2 as $f): ?>
                        <tr>
                            <td><?= esc($f['mes']) ?></td>
                            <td><?= esc($f['fecha']) ?></td>
                            <td><?= esc($f['hora']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </td>
        </tr>
    </table>
<?php endif; ?>

<div class="footer">
    Documento generado el <?= esc(date('d/m/Y')) ?> por EnterpriseSST &middot;
    <?= esc($codigoFt ?? '') ?>
</div>


</body>
</html>
