<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Constitucion <?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></title>
    <?php
    // Funciones auxiliares
    function fechaLargaPdf($fecha) {
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $timestamp = strtotime($fecha);
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('n', $timestamp) - 1];
        $anio = date('Y', $timestamp);
        return "$dia de $mes de $anio";
    }

    function fechaCortaPdf($fecha) {
        return date('d/m/Y', strtotime($fecha));
    }

    $tipoComiteNombre = [
        'COPASST' => 'COMITE PARITARIO DE SEGURIDAD Y SALUD EN EL TRABAJO',
        'COCOLAB' => 'COMITE DE CONVIVENCIA LABORAL',
        'BRIGADA' => 'BRIGADA DE EMERGENCIAS',
        'VIGIA' => 'VIGIA DE SEGURIDAD Y SALUD EN EL TRABAJO'
    ][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];

    $tipoComiteCorto = [
        'COPASST' => 'COPASST',
        'COCOLAB' => 'Comite de Convivencia',
        'BRIGADA' => 'Brigada de Emergencias',
        'VIGIA' => 'Vigia SST'
    ][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];

    $periodoInicio = fechaLargaPdf($proceso['fecha_inicio_periodo'] ?? date('Y-m-d'));
    $periodoFin = fechaLargaPdf($proceso['fecha_fin_periodo'] ?? date('Y-m-d', strtotime('+2 years')));
    $fechaEleccion = fechaLargaPdf($proceso['fecha_inicio_votacion'] ?? date('Y-m-d'));

    // Logo en base64 (requerido para DOMPDF)
    $logoPath = FCPATH . 'uploads/' . ($cliente['logo'] ?? '');
    $logoBase64 = '';
    if (file_exists($logoPath) && !empty($cliente['logo'])) {
        $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Datos del contexto para firmas
    $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '';
    $repLegalCedula = $contexto['representante_legal_cedula'] ?? $cliente['cedula_rep_legal'] ?? '';
    $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
    $delegadoCedula = $contexto['delegado_sst_cedula'] ?? '';
    $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
    $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
    $estandares = $contexto['estandares_aplicables'] ?? 60;
    ?>
    <style>
        @page {
            margin: 2cm 1.5cm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.15;
            color: #333;
        }

        /* Encabezado formal */
        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }

        .encabezado-logo {
            width: 100px;
            padding: 8px;
            text-align: center;
            background-color: #ffffff;
        }

        .encabezado-logo img {
            max-width: 80px;
            max-height: 50px;
            background-color: #ffffff;
        }

        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }

        .encabezado-titulo-central .sistema {
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
            border-bottom: 1px solid #333;
        }

        .encabezado-titulo-central .nombre-doc {
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
        }

        .encabezado-info {
            width: 130px;
            padding: 0;
        }

        .encabezado-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 3px 6px;
            font-size: 8pt;
        }

        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }

        .encabezado-info-table .label {
            font-weight: bold;
        }

        /* Secciones */
        .seccion {
            margin-bottom: 8px;
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

        /* Tablas de contenido */
        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9pt;
        }

        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 5px 8px;
        }

        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Listas */
        ol, ul {
            margin: 3px 0 3px 15px;
            padding-left: 15px;
        }

        li {
            margin-bottom: 2px;
        }

        /* Salto de pagina */
        .page-break {
            page-break-before: always;
        }

        /* Pie de documento */
        .pie-documento {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>

<?php
// =====================================
// FUNCION PARA ENCABEZADO REUTILIZABLE
// =====================================
function renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre) {
?>
<table class="encabezado-formal" cellpadding="0" cellspacing="0">
    <tr>
        <td class="encabezado-logo" rowspan="2" valign="middle" align="center">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
            <?php else: ?>
                <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente']) ?></div>
            <?php endif; ?>
        </td>
        <td class="encabezado-titulo-central" valign="middle">
            <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
        </td>
        <td class="encabezado-info" rowspan="2" valign="middle">
            <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="label">Codigo:</td>
                    <td><?= esc($codigoDocumento) ?></td>
                </tr>
                <tr>
                    <td class="label">Version:</td>
                    <td><?= str_pad($versionDocumento ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                </tr>
                <tr>
                    <td class="label">Fecha:</td>
                    <td><?= date('d/m/Y') ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="encabezado-titulo-central" valign="middle">
            <div class="nombre-doc">ACTA DE CONSTITUCION DEL <?= esc($tipoComiteNombre) ?></div>
        </td>
    </tr>
</table>
<?php
}
?>

<!-- =====================================
     SECCION 1: ACTA DE APERTURA DE ELECCIONES
     ===================================== -->

<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">1. ACTA DE APERTURA DE ELECCIONES</div>
    <div class="seccion-contenido">
        <p>
            En las instalaciones de <strong><?= esc($cliente['nombre_cliente']) ?></strong>,
            NIT <?= esc($cliente['nit_cliente']) ?>, ubicada en <?= esc($cliente['direccion_cliente'] ?? 'la ciudad') ?>,
            siendo las <?= date('H:i') ?> horas del dia <strong><?= $fechaEleccion ?></strong>,
            se reunieron los trabajadores convocados para participar en la eleccion de los representantes
            de los trabajadores al <strong><?= $tipoComiteCorto ?></strong>.
        </p>
        <p>
            Se procedio a dar apertura formal al proceso de votacion con la presencia de los siguientes jurados:
        </p>

        <?php if (!empty($jurados)): ?>
        <table class="tabla-contenido">
            <tr>
                <th style="width: 40%;">Nombre</th>
                <th style="width: 30%;">Documento</th>
                <th style="width: 30%;">Rol</th>
            </tr>
            <?php foreach ($jurados as $j): ?>
            <tr>
                <td><?= $j['nombres'] ?> <?= $j['apellidos'] ?></td>
                <td style="text-align: center;">C.C. <?= $j['documento_identidad'] ?></td>
                <td style="text-align: center;"><?= ucfirst($j['rol']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p><em>No se registraron jurados para este proceso.</em></p>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================
     SECCION 2: REGISTRO DE VOTANTES
     ===================================== -->

<div class="page-break"></div>
<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">2. REGISTRO DE VOTANTES</div>
    <div class="seccion-contenido">
        <p>
            De un total de <strong><?= $totalVotantes ?></strong> trabajadores habilitados para votar,
            ejercieron su derecho al voto <strong><?= $votaronCount ?></strong> personas,
            lo que representa una participacion del <strong><?= $participacion ?>%</strong>.
        </p>

        <?php if (!empty($votantes)): ?>
        <table class="tabla-contenido" style="font-size: 8pt;">
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Nombre Completo</th>
                <th style="width: 25%;">Documento</th>
                <th style="width: 30%;">Cargo</th>
            </tr>
            <?php $i = 1; foreach ($votantes as $v): ?>
            <tr style="<?= $i % 2 === 0 ? 'background-color: #f8f9fa;' : '' ?>">
                <td style="text-align: center;"><?= $i++ ?></td>
                <td><?= $v['nombres'] ?> <?= $v['apellidos'] ?></td>
                <td style="text-align: center;">C.C. <?= $v['documento_identidad'] ?></td>
                <td><?= $v['cargo'] ?? '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================
     SECCION 3: ACTA DE CIERRE DE VOTACIONES
     ===================================== -->

<div class="page-break"></div>
<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">3. ACTA DE CIERRE DE VOTACIONES</div>
    <div class="seccion-contenido">
        <p>
            Siendo las <?= date('H:i') ?> horas del dia <?= fechaLargaPdf($proceso['fecha_fin_votacion'] ?? date('Y-m-d')) ?>,
            se procedio a cerrar la votacion.
            Los jurados de votacion procedieron a realizar el conteo de votos en presencia
            de los testigos designados, obteniendo los siguientes resultados.
        </p>
    </div>
</div>

<!-- =====================================
     SECCION 4: RESULTADO DE LAS VOTACIONES
     ===================================== -->

<div class="seccion">
    <div class="seccion-titulo">4. RESULTADO DE LAS VOTACIONES</div>
    <div class="seccion-contenido">
        <p>El escrutinio de los votos arrojo los siguientes resultados para los candidatos de los trabajadores:</p>

        <?php if (!empty($resultadosVotacion)): ?>
        <table class="tabla-contenido">
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 42%;">Candidato</th>
                <th style="width: 20%;">Votos</th>
                <th style="width: 30%;">Resultado</th>
            </tr>
            <?php
            $pos = 1;
            $totalVotosTabla = 0;
            foreach ($resultadosVotacion as $r):
                $esPrincipal = $pos <= $proceso['plazas_principales'];
                $esSuplente = !$esPrincipal && $pos <= ($proceso['plazas_principales'] + $proceso['plazas_suplentes']);
                $totalVotosTabla += $r['votos_obtenidos'];
            ?>
            <tr style="<?= $esPrincipal ? 'background-color: #d1e7dd;' : ($esSuplente ? 'background-color: #cfe2ff;' : '') ?>">
                <td style="text-align: center;"><?= $pos ?></td>
                <td><?= $r['nombres'] ?> <?= $r['apellidos'] ?></td>
                <td style="text-align: center; font-weight: bold;"><?= $r['votos_obtenidos'] ?></td>
                <td style="text-align: center;">
                    <?php if ($esPrincipal): ?>
                    <strong>PRINCIPAL</strong>
                    <?php elseif ($esSuplente): ?>
                    SUPLENTE
                    <?php else: ?>
                    No elegido
                    <?php endif; ?>
                </td>
            </tr>
            <?php $pos++; endforeach; ?>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="2" style="text-align: right;">TOTAL VOTOS:</td>
                <td style="text-align: center;"><?= $totalVotosTabla ?></td>
                <td></td>
            </tr>
        </table>

        <p style="text-align: center; margin-top: 10px;">
            <strong>Participacion:</strong> <?= $votaronCount ?> de <?= $totalVotantes ?> votantes (<?= $participacion ?>%)
        </p>
        <?php else: ?>
        <p><em>No hay resultados de votacion registrados.</em></p>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================
     SECCION 5: CONFORMACION DEL COMITE
     ===================================== -->

<div class="page-break"></div>
<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">5. CONFORMACION DEL <?= $proceso['tipo_comite'] ?></div>
    <div class="seccion-contenido">
        <p>
            De acuerdo con los resultados de la votacion y las designaciones realizadas por el empleador,
            el <strong><?= $tipoComiteCorto ?></strong> queda conformado de la siguiente manera para el periodo
            <strong><?= $periodoInicio ?></strong> al <strong><?= $periodoFin ?></strong>:
        </p>

        <!-- Representantes del Empleador -->
        <div style="margin-top: 15px;">
            <div style="background-color: #198754; color: white; padding: 6px 10px; font-weight: bold; font-size: 9pt;">
                REPRESENTANTES DEL EMPLEADOR
            </div>
            <table class="tabla-contenido" style="margin-top: 0;">
                <tr>
                    <th style="width: 45%; background-color: #e9ecef; color: #333;">Nombre</th>
                    <th style="width: 30%; background-color: #e9ecef; color: #333;">Documento</th>
                    <th style="width: 25%; background-color: #e9ecef; color: #333;">Tipo</th>
                </tr>
                <?php foreach ($empleadorPrincipales as $e): ?>
                <tr style="background-color: #d1e7dd;">
                    <td><?= $e['nombres'] ?> <?= $e['apellidos'] ?></td>
                    <td style="text-align: center;">C.C. <?= $e['documento_identidad'] ?></td>
                    <td style="text-align: center; font-weight: bold;">Principal</td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($empleadorSuplentes as $e): ?>
                <tr style="background-color: #cfe2ff;">
                    <td><?= $e['nombres'] ?> <?= $e['apellidos'] ?></td>
                    <td style="text-align: center;">C.C. <?= $e['documento_identidad'] ?></td>
                    <td style="text-align: center;">Suplente</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Representantes de Trabajadores -->
        <div style="margin-top: 15px;">
            <div style="background-color: #0d6efd; color: white; padding: 6px 10px; font-weight: bold; font-size: 9pt;">
                REPRESENTANTES DE LOS TRABAJADORES
            </div>
            <table class="tabla-contenido" style="margin-top: 0;">
                <tr>
                    <th style="width: 45%; background-color: #e9ecef; color: #333;">Nombre</th>
                    <th style="width: 30%; background-color: #e9ecef; color: #333;">Documento</th>
                    <th style="width: 25%; background-color: #e9ecef; color: #333;">Tipo</th>
                </tr>
                <?php foreach ($principales as $t): ?>
                <tr style="background-color: #d1e7dd;">
                    <td><?= $t['nombres'] ?> <?= $t['apellidos'] ?></td>
                    <td style="text-align: center;">C.C. <?= $t['documento_identidad'] ?></td>
                    <td style="text-align: center; font-weight: bold;">Principal</td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($suplentes as $t): ?>
                <tr style="background-color: #cfe2ff;">
                    <td><?= $t['nombres'] ?> <?= $t['apellidos'] ?></td>
                    <td style="text-align: center;">C.C. <?= $t['documento_identidad'] ?></td>
                    <td style="text-align: center;">Suplente</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<!-- =====================================
     SECCION 6: FUNCIONES DEL COMITE
     ===================================== -->

<div class="seccion">
    <div class="seccion-titulo">6. FUNCIONES DEL <?= $proceso['tipo_comite'] ?></div>
    <div class="seccion-contenido">
        <?php if ($proceso['tipo_comite'] === 'COPASST'): ?>
        <p>De conformidad con el Articulo 11 de la Resolucion 2013 de 1986 y el Decreto 1072 de 2015, son funciones del COPASST:</p>
        <ol>
            <li>Proponer y participar en actividades de capacitacion en salud ocupacional.</li>
            <li>Vigilar el desarrollo de las actividades del SG-SST.</li>
            <li>Colaborar en el analisis de accidentes de trabajo y enfermedades laborales.</li>
            <li>Realizar inspecciones periodicas a las instalaciones.</li>
            <li>Proponer medidas preventivas y correctivas.</li>
            <li>Servir como organismo de coordinacion entre empleador y trabajadores en SST.</li>
        </ol>
        <?php elseif ($proceso['tipo_comite'] === 'COCOLAB'): ?>
        <p>De conformidad con la Resolucion 652 de 2012, son funciones del Comite de Convivencia Laboral:</p>
        <ol>
            <li>Recibir y dar tramite a las quejas de acoso laboral.</li>
            <li>Examinar de manera confidencial los casos de acoso laboral.</li>
            <li>Escuchar a las partes involucradas de manera individual.</li>
            <li>Adelantar reuniones para crear un espacio de dialogo.</li>
            <li>Formular planes de mejora y hacer seguimiento.</li>
            <li>Presentar a la alta direccion recomendaciones preventivas.</li>
        </ol>
        <?php elseif ($proceso['tipo_comite'] === 'BRIGADA'): ?>
        <p>Son funciones de la Brigada de Emergencias:</p>
        <ol>
            <li>Actuar como primer respondiente en caso de emergencia.</li>
            <li>Participar en simulacros y entrenamientos periodicos.</li>
            <li>Verificar el estado de los equipos de emergencia.</li>
            <li>Apoyar la evacuacion del personal en caso de emergencia.</li>
            <li>Prestar primeros auxilios cuando sea necesario.</li>
            <li>Reportar condiciones de riesgo identificadas.</li>
        </ol>
        <?php else: ?>
        <p>De conformidad con la Resolucion 0312 de 2019, son funciones del Vigia de SST:</p>
        <ol>
            <li>Proponer y participar en actividades de capacitacion en SST.</li>
            <li>Vigilar el desarrollo de las actividades del SG-SST.</li>
            <li>Colaborar en el analisis de accidentes de trabajo.</li>
            <li>Realizar inspecciones periodicas a las instalaciones.</li>
            <li>Proponer medidas preventivas y correctivas.</li>
        </ol>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================
     SECCION 7: CONTROL DE CAMBIOS
     ===================================== -->

<div class="seccion" style="margin-top: 25px;">
    <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        CONTROL DE CAMBIOS
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
            <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
            <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;"><?= $versionDocumento ?? '1' ?>.0</td>
            <td>Creacion del documento - Acta de Constitucion del <?= $proceso['tipo_comite'] ?> periodo <?= $proceso['anio'] ?></td>
            <td style="text-align: center;"><?= date('d/m/Y') ?></td>
        </tr>
    </table>
</div>

<!-- =====================================
     SECCION 8: FIRMAS DE JURADOS DE VOTACION
     ===================================== -->

<?php if (!empty($jurados)): ?>
<div class="page-break"></div>
<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div style="margin-top: 25px;">
    <div style="background-color: #6c757d; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE JURADOS DE VOTACION
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 25%; background-color: #e9ecef; color: #333;">Rol</th>
            <th style="width: 35%; background-color: #e9ecef; color: #333;">Nombre / Documento</th>
            <th style="width: 40%; background-color: #e9ecef; color: #333;">Firma</th>
        </tr>
        <?php foreach ($jurados as $idx => $j): ?>
        <?php
        $tipoFirmaJurado = 'jurado_' . $j['id_jurado'];
        $firmaJurado = ($firmasElectronicas ?? [])[$tipoFirmaJurado] ?? null;
        ?>
        <tr style="<?= $idx % 2 === 1 ? 'background-color: #f8f9fa;' : '' ?>">
            <td style="text-align: center; font-weight: bold;"><?= ucfirst($j['rol']) ?></td>
            <td>
                <strong><?= $j['nombres'] ?> <?= $j['apellidos'] ?></strong><br>
                <span style="font-size: 8pt; color: #666;">C.C. <?= $j['documento_identidad'] ?></span>
            </td>
            <td style="height: 60px; vertical-align: bottom; text-align: center;">
                <?php if ($firmaJurado && !empty($firmaJurado['evidencia']['firma_imagen'])): ?>
                    <img src="<?= $firmaJurado['evidencia']['firma_imagen'] ?>" style="max-height: 40px; max-width: 120px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #333; width: 70%; margin: 0 auto; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<!-- =====================================
     SECCION 9: FIRMAS DE APROBACION
     ===================================== -->

<div class="page-break"></div>
<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div style="margin-top: 25px;">
    <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE APROBACION
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 50%; background-color: #e9ecef; color: #333;">Aprobo / Representante Legal</th>
            <th style="width: 50%; background-color: #e9ecef; color: #333;">Reviso / <?= $requiereDelegado ? 'Delegado SST' : ($estandares <= 21 ? 'Vigia SST' : 'COPASST') ?></th>
        </tr>
        <tr>
            <!-- REPRESENTANTE LEGAL -->
            <td style="vertical-align: top; padding: 12px; height: 100px;">
                <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> Representante Legal</div>
                <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Documento:</strong> <?= esc($repLegalCedula) ?></div>
            </td>
            <!-- DELEGADO/VIGIA SST -->
            <td style="vertical-align: top; padding: 12px; height: 100px;">
                <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= esc($delegadoNombre) ?></div>
                <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> <?= $requiereDelegado ? esc($delegadoCargo) : ($estandares <= 21 ? 'Vigia SST' : 'COPASST') ?></div>
                <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Documento:</strong> <?= esc($delegadoCedula) ?></div>
            </td>
        </tr>
        <tr>
            <!-- Fila de firmas alineadas -->
            <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                <?php
                $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                ?>
                    <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 56px; max-width: 168px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
            <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                <?php
                $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? ($firmasElectronicas ?? [])['vigia_sst'] ?? null;
                if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                ?>
                    <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" style="max-height: 56px; max-width: 168px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- =====================================
     SECCION 10: FIRMAS DE MIEMBROS DEL COMITE
     ===================================== -->

<div class="page-break"></div>
<?php renderEncabezadoPdf($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<!-- Representantes del Empleador -->
<div style="margin-top: 25px;">
    <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE MIEMBROS - REPRESENTANTES DEL EMPLEADOR
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 50%; background-color: #e9ecef; color: #333;">Miembro 1</th>
            <th style="width: 50%; background-color: #e9ecef; color: #333;">Miembro 2</th>
        </tr>
        <?php
        $allEmpleador = array_merge($empleadorPrincipales, $empleadorSuplentes);
        $chunks = array_chunk($allEmpleador, 2);
        foreach ($chunks as $pair):
        ?>
        <tr>
            <?php foreach ($pair as $e): ?>
            <td style="vertical-align: top; padding: 10px;">
                <div style="text-align: center; margin-bottom: 5px;">
                    <span style="background-color: <?= $e['tipo_plaza'] === 'principal' ? '#198754' : '#0d6efd' ?>; color: white; padding: 2px 8px; font-size: 7pt;">
                        <?= strtoupper($e['tipo_plaza']) ?>
                    </span>
                </div>
                <div style="font-size: 8pt; margin-bottom: 3px;"><strong>Nombre:</strong> <?= $e['nombres'] ?> <?= $e['apellidos'] ?></div>
                <div style="font-size: 8pt; margin-bottom: 3px;"><strong>C.C.:</strong> <?= $e['documento_identidad'] ?></div>
                <div style="font-size: 8pt; margin-bottom: 3px;"><strong>Cargo:</strong> <?= $e['cargo'] ?? '-' ?></div>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td></td>
            <?php endif; ?>
        </tr>
        <tr>
            <?php foreach ($pair as $e): ?>
            <?php
            $tipoFirmaEmpleador = 'empleador_' . $e['tipo_plaza'] . '_' . $e['id_candidato'];
            $firmaEmpleador = ($firmasElectronicas ?? [])[$tipoFirmaEmpleador] ?? null;
            ?>
            <td style="padding: 8px 10px; text-align: center; vertical-align: bottom; height: 50px;">
                <?php if ($firmaEmpleador && !empty($firmaEmpleador['evidencia']['firma_imagen'])): ?>
                    <img src="<?= $firmaEmpleador['evidencia']['firma_imagen'] ?>" style="max-height: 40px; max-width: 120px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #333; width: 75%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Representantes de Trabajadores -->
<div style="margin-top: 25px;">
    <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE MIEMBROS - REPRESENTANTES DE LOS TRABAJADORES
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 50%; background-color: #e9ecef; color: #333;">Miembro 1</th>
            <th style="width: 50%; background-color: #e9ecef; color: #333;">Miembro 2</th>
        </tr>
        <?php
        $allTrabajadores = array_merge($principales, $suplentes);
        $chunks = array_chunk($allTrabajadores, 2);
        foreach ($chunks as $pair):
        ?>
        <tr>
            <?php foreach ($pair as $t): ?>
            <td style="vertical-align: top; padding: 10px;">
                <div style="text-align: center; margin-bottom: 5px;">
                    <span style="background-color: <?= $t['tipo_plaza'] === 'principal' ? '#198754' : '#0d6efd' ?>; color: white; padding: 2px 8px; font-size: 7pt;">
                        <?= strtoupper($t['tipo_plaza']) ?>
                    </span>
                </div>
                <div style="font-size: 8pt; margin-bottom: 3px;"><strong>Nombre:</strong> <?= $t['nombres'] ?> <?= $t['apellidos'] ?></div>
                <div style="font-size: 8pt; margin-bottom: 3px;"><strong>C.C.:</strong> <?= $t['documento_identidad'] ?></div>
                <div style="font-size: 8pt; margin-bottom: 3px;"><strong>Cargo:</strong> <?= $t['cargo'] ?? '-' ?></div>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td></td>
            <?php endif; ?>
        </tr>
        <tr>
            <?php foreach ($pair as $t): ?>
            <?php
            $tipoFirmaTrabajador = 'trabajador_' . $t['tipo_plaza'] . '_' . $t['id_candidato'];
            $firmaTrabajador = ($firmasElectronicas ?? [])[$tipoFirmaTrabajador] ?? null;
            ?>
            <td style="padding: 8px 10px; text-align: center; vertical-align: bottom; height: 50px;">
                <?php if ($firmaTrabajador && !empty($firmaTrabajador['evidencia']['firma_imagen'])): ?>
                    <img src="<?= $firmaTrabajador['evidencia']['firma_imagen'] ?>" style="max-height: 40px; max-width: 120px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #333; width: 75%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- PIE DE DOCUMENTO -->
<div class="pie-documento">
    <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
    <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
</div>

</body>
</html>
