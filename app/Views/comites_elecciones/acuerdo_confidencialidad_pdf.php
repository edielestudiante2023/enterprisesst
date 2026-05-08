<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuerdo de Confidencialidad COCOLAB - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <?php
    // Auxiliares
    if (!function_exists('fechaLargaAcuerdo')) {
        function fechaLargaAcuerdo($fecha) {
            if (!$fecha) return '___________________';
            $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            $ts = strtotime($fecha);
            return date('d', $ts) . ' dias del mes de ' . $meses[(int)date('n', $ts) - 1] . ' del ano ' . date('Y', $ts);
        }
    }

    // Logo
    $logoPath = FCPATH . 'uploads/' . ($cliente['logo'] ?? '');
    $logoBase64 = '';
    if (!empty($cliente['logo']) && file_exists($logoPath)) {
        $ext = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
        $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Razon social del cliente
    $razonSocial = $cliente['razon_social'] ?? $cliente['nombre_cliente'] ?? 'la empresa';
    $razonSocialUpper = mb_strtoupper($razonSocial, 'UTF-8');

    $miembros        = $miembros        ?? [];
    $firmasElectronicas = $firmasElectronicas ?? [];
    $codigoFt        = $codigoFt        ?? 'FT-SST-018';
    ?>
    <style>
        @page { margin: 18mm 18mm 16mm 18mm; }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.32;
        }
        .header-acuerdo {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #1c2437;
            padding-bottom: 6px;
        }
        .header-acuerdo img {
            max-height: 56px;
            max-width: 170px;
            margin-bottom: 4px;
        }
        .header-acuerdo h1 {
            font-size: 13pt;
            color: #1c2437;
            margin: 2px 0 1px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-acuerdo .subtitulo {
            font-size: 11pt;
            color: #1c2437;
            font-weight: bold;
            margin: 0;
        }
        .header-acuerdo .codigo {
            font-size: 8pt;
            color: #6c757d;
            margin-top: 2px;
        }
        .seccion-grupal {
            margin-bottom: 8px;
            text-align: justify;
        }
        .seccion-grupal p { margin: 0; }
        h3.seccion-titulo {
            font-size: 11pt;
            color: #1c2437;
            margin: 10px 0 6px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #bd9751;
            text-transform: uppercase;
        }
        .miembro-acuerdo {
            page-break-before: always;
            page-break-inside: avoid;
        }
        .miembro-acuerdo:first-of-type {
            page-break-before: auto;
        }
        .datos-personales {
            background: #f8f9fa;
            border-left: 3px solid #1c2437;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 10pt;
        }
        .datos-personales .linea { margin: 0; }
        .datos-personales strong { color: #1c2437; }
        .clausulas {
            margin: 6px 0;
            text-align: justify;
        }
        .clausulas p { margin: 0 0 4px 0; }
        .clausulas ol {
            padding-left: 20px;
            margin: 4px 0;
        }
        .clausulas li {
            margin-bottom: 3px;
            text-align: justify;
        }
        .cierre-firma {
            margin-top: 8px;
            text-align: justify;
        }
        .cierre-firma p { margin: 0 0 4px 0; }
        .bloque-firma {
            margin-top: 14px;
            text-align: center;
        }
        .bloque-firma .imagen-firma {
            max-height: 48px;
            max-width: 150px;
            display: block;
            margin: 0 auto 2px auto;
        }
        .bloque-firma .placeholder-firma {
            border-bottom: 1px solid #555;
            width: 55%;
            margin: 18px auto 2px auto;
            height: 1px;
        }
        .bloque-firma .nombre-firma {
            font-weight: bold;
            font-size: 10pt;
            margin: 2px 0 0 0;
        }
        .bloque-firma .cedula-firma {
            font-size: 9pt;
            color: #444;
            margin: 0;
        }
        .bloque-firma .estado-firma {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 8pt;
            font-weight: bold;
        }
        .estado-firmado {
            background: #d4edda;
            color: #155724;
            border: 1px solid #155724;
        }
        .estado-pendiente {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #856404;
        }
        .footer-pagina {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
        }
    </style>
</head>
<body>

<!-- ENCABEZADO GRUPAL (solo en la primera pagina) -->
<div class="header-acuerdo">
    <?php if ($logoBase64): ?>
        <img src="<?= $logoBase64 ?>" alt="Logo">
    <?php endif; ?>
    <h1>Comite de Convivencia Laboral</h1>
    <div class="subtitulo">Acuerdo de Confidencialidad</div>
    <div class="codigo">Codigo: <?= esc($codigoFt) ?> &middot; <?= esc($razonSocial) ?></div>
</div>

<div class="seccion-grupal">
    <p>
        Los miembros del Comite de Convivencia Laboral de
        <strong><?= esc($razonSocialUpper) ?></strong>, elegidos por los trabajadores y los
        directivos de la entidad, en atencion a las obligaciones que deben cumplir y todas
        las personas que participen en las reuniones, en forma libre y voluntaria convienen
        suscribir el presente Acuerdo de Confidencialidad en los siguientes terminos:
    </p>
</div>

<?php if (empty($miembros)): ?>
    <div style="text-align:center; padding: 30px; color: #888; font-style: italic;">
        No hay miembros activos del Comite de Convivencia Laboral registrados.
    </div>
<?php else: ?>
    <?php foreach ($miembros as $i => $m):
        $nombre = trim($m['nombre'] ?? '');
        $cedula = trim($m['cedula'] ?? '');
        // Firma electronica del miembro (key: miembro_{idMiembro})
        $tipoFirmaKey = 'miembro_' . ($m['id_miembro'] ?? $i);
        $firma = $firmasElectronicas[$tipoFirmaKey] ?? null;
        $firmoOk = $firma && !empty($firma['evidencia']['firma_imagen']);
        $fechaFirma = $firmoOk ? ($firma['evidencia']['fecha_firma'] ?? null) : null;
    ?>
    <div class="miembro-acuerdo">
        <h3 class="seccion-titulo">Miembro <?= ($i + 1) ?>: <?= esc($nombre) ?></h3>

        <div class="datos-personales">
            <div class="linea">
                Yo, <strong><?= esc($nombre ?: '__________________________________________') ?></strong>,
                identificado(a) con documento de identidad numero
                <strong><?= esc($cedula ?: '___________________') ?></strong>,
                en mi calidad de miembro del Comite de Convivencia Laboral de
                <strong><?= esc($razonSocialUpper) ?></strong>,
            </div>
        </div>

        <div class="clausulas">
            <p>
                comprendo y tengo claro que, para efectos de este acuerdo, la
                <strong>CONFIDENCIALIDAD</strong> es entendida por toda aquella informacion
                propia de cada uno de los casos, bien sea de caracter tecnico, administrativo,
                laboral o legal, a la que normalmente no tengo acceso y por tanto debe permanecer
                en reserva. La informacion dejara de ser confidencial cuando sea de dominio
                publico por haber sido publicada por quien sea el titular o el dueno de la
                informacion, o cuando la informacion deba ser divulgada por disposicion legal
                o por orden judicial.
            </p>
            <p>En virtud de lo antes senalado, el(la) suscrito(a) se compromete a:</p>

            <ol>
                <li>
                    Manejar de manera confidencial la informacion que como tal sea conocida,
                    prestada, entregada y toda aquella que se genere en torno a ella como
                    resultado del funcionamiento del Comite de Convivencia Laboral.
                </li>
                <li>
                    Guardar confidencialidad sobre esa informacion y no emplearla en beneficio
                    propio o de terceros mientras conserve sus caracteristicas de
                    confidencialidad o mientras sea manejada como un caso de los que conoce
                    el Comite de Convivencia Laboral.
                </li>
                <li>
                    Mantener la reserva de la informacion de todos y cada uno de los documentos
                    que le son entregados, o de aquellos que son socializados en el seno del
                    Comite de Convivencia Laboral, asi como mantener la reserva de todas las
                    conversaciones que se susciten con los funcionarios de
                    <strong><?= esc($razonSocialUpper) ?></strong>, que son atendidos por el
                    Comite en cualquiera de las diligencias que deba ser adelantada.
                </li>
                <li>
                    Guardar con recelo toda la informacion con la que cuente y tenga acceso a
                    traves de su participacion dentro del Comite de Convivencia Laboral.
                </li>
            </ol>
        </div>

        <div class="cierre-firma">
            <p>
                En ese orden, es claro que el desconocimiento de los compromisos descritos
                puede conllevar a inhabilitar o retirar el actuar dentro de la toma de decision
                del Comite de Convivencia Laboral.
            </p>
            <p>
                Acepto voluntariamente, a los
                <?php if ($firmoOk && $fechaFirma): ?>
                    <strong><?= fechaLargaAcuerdo($fechaFirma) ?></strong>.
                <?php else: ?>
                    <strong>_____ dias del mes de _____________ del ano _______</strong>.
                <?php endif; ?>
            </p>
        </div>

        <div class="bloque-firma">
            <?php if ($firmoOk): ?>
                <img src="<?= $firma['evidencia']['firma_imagen'] ?>" class="imagen-firma" alt="Firma">
                <p class="nombre-firma"><?= esc($nombre) ?></p>
                <p class="cedula-firma">C.C. <?= esc($cedula) ?></p>
                <span class="estado-firma estado-firmado">FIRMADO ELECTRONICAMENTE</span>
            <?php else: ?>
                <div class="placeholder-firma"></div>
                <p class="nombre-firma"><?= esc($nombre ?: 'Firma') ?></p>
                <p class="cedula-firma">C.C. <?= esc($cedula ?: '___________________') ?></p>
                <span class="estado-firma estado-pendiente">PENDIENTE DE FIRMA</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="footer-pagina">
    <?= esc($codigoFt) ?> &middot; <?= esc($razonSocial) ?> &middot;
    Generado el <?= date('d/m/Y') ?> por EnterpriseSST
</div>

</body>
</html>
