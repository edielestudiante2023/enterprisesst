<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Miembros del <?= esc($tipoComiteCorto) ?> — <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
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
        .deco-top {
            height: 32px;
            background: linear-gradient(90deg, #2196f3 0%, #4caf50 100%);
            position: relative;
            margin-bottom: 4px;
        }
        .header {
            text-align: center;
            padding: 10px 0 14px 0;
            border-bottom: 2px solid #1c2437;
            margin-bottom: 18px;
        }
        .header .logo-emp {
            max-height: 70px;
            max-width: 200px;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 24px;
            color: #1c2437;
            margin: 0;
            font-weight: bold;
        }
        .header .empresa {
            font-size: 13px;
            color: #6c757d;
            margin: 4px 0 0 0;
        }
        .header .periodo {
            font-size: 16px;
            color: #4caf50;
            margin: 6px 0 0 0;
            font-weight: bold;
        }
        .seccion-titulo {
            font-size: 14px;
            color: #1c2437;
            font-weight: bold;
            margin: 16px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #bd9751;
        }

        /* Grid de miembros (Dompdf-friendly: usar table) */
        table.miembros { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.miembros td {
            width: 33.33%;
            padding: 6px;
            text-align: center;
            vertical-align: top;
        }
        .miembro {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px;
            background: #fafafa;
        }
        .miembro img {
            width: 95px;
            height: 95px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #1c2437;
            margin-bottom: 6px;
        }
        .miembro .placeholder-foto {
            display: inline-block;
            width: 95px;
            height: 95px;
            line-height: 95px;
            background: #e9ecef;
            color: #6c757d;
            border-radius: 50%;
            border: 2px solid #ccc;
            font-size: 28px;
            font-weight: bold;
        }
        .miembro .nombre {
            font-size: 11px;
            font-weight: bold;
            color: #1c2437;
            margin: 4px 0 2px 0;
        }
        .miembro .cargo-rol {
            font-size: 10px;
            color: #bd9751;
            font-weight: bold;
            margin: 0;
        }
        .miembro .area {
            font-size: 9px;
            color: #6c757d;
            margin: 2px 0 0 0;
        }
        .vacio {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 12px;
        }

        /* Mensaje del comite */
        .mensaje-box {
            background: #f8f9fa;
            border-left: 4px solid #1c2437;
            padding: 14px 18px;
            margin: 0 0 18px 0;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }
        .mensaje-box p { margin: 0 0 8px 0; }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
    </style>
</head>
<body>

<div class="deco-top"></div>

<div class="header">
    <?php if (!empty($logoBase64)): ?>
        <img src="<?= $logoBase64 ?>" class="logo-emp" alt="Logo">
    <?php endif; ?>
    <h1>Miembros del <?= esc($tipoComiteCorto) ?></h1>
    <p class="empresa"><?= esc($cliente['nombre_cliente'] ?? '') ?></p>
    <?php if (!empty($periodoInicio) && !empty($periodoFin)): ?>
        <p class="periodo"><?= esc(date('d/m/Y', strtotime($periodoInicio))) ?> a <?= esc(date('d/m/Y', strtotime($periodoFin))) ?></p>
    <?php endif; ?>
</div>

<?php
// Funcion local para renderizar grupo de miembros
$renderGrupo = function(array $miembros) {
    if (empty($miembros)) {
        echo '<div class="vacio">No hay miembros registrados.</div>';
        return;
    }
    echo '<table class="miembros"><tr>';
    $i = 0;
    foreach ($miembros as $m) {
        if ($i > 0 && $i % 3 === 0) echo '</tr><tr>';
        echo '<td><div class="miembro">';
        if (!empty($m['foto_base64'])) {
            echo '<img src="' . $m['foto_base64'] . '" alt="">';
        } else {
            $inicial = mb_substr(trim($m['nombre'] ?? '?'), 0, 1);
            echo '<div class="placeholder-foto">' . esc(mb_strtoupper($inicial)) . '</div>';
        }
        echo '<div class="nombre">' . esc($m['nombre'] ?? '') . '</div>';
        if (!empty($m['rol_comite'])) {
            echo '<div class="cargo-rol">' . esc(ucfirst($m['rol_comite'])) . '</div>';
        }
        if (!empty($m['cargo'])) {
            echo '<div class="area">' . esc($m['cargo']) . '</div>';
        }
        echo '</div></td>';
        $i++;
    }
    // Completar fila
    while ($i % 3 !== 0) {
        echo '<td></td>';
        $i++;
    }
    echo '</tr></table>';
};
?>

<!-- Mensaje del comite primero (formato carta: introduccion antes que datos) -->
<div class="mensaje-box">
    <?php
    $mensaje = $mensajeComite ?? '';
    foreach (explode("\n\n", $mensaje) as $parrafo):
        $parrafo = trim($parrafo);
        if ($parrafo === '') continue;
    ?>
        <p><?= nl2br(esc($parrafo)) ?></p>
    <?php endforeach; ?>
</div>

<div class="seccion-titulo">Representantes del Empleador</div>
<?php $renderGrupo($empleador ?? []); ?>

<div class="seccion-titulo">Representantes de los Trabajadores</div>
<?php $renderGrupo($trabajadores ?? []); ?>

<div class="footer">
    Documento generado el <?= esc(date('d/m/Y')) ?> por EnterpriseSST &middot;
    <?= esc($codigoFt ?? '') ?>
</div>

</body>
</html>
