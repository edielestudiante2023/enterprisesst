<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Acta de Constitucion <?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <?php
    // Funciones auxiliares
    function fechaLargaWord($fecha) {
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $timestamp = strtotime($fecha);
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('n', $timestamp) - 1];
        $anio = date('Y', $timestamp);
        return "$dia de $mes de $anio";
    }

    function fechaCortaWord($fecha) {
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

    $periodoInicio = fechaLargaWord($proceso['fecha_inicio_periodo'] ?? date('Y-m-d'));
    $periodoFin = fechaLargaWord($proceso['fecha_fin_periodo'] ?? date('Y-m-d', strtotime('+2 years')));
    $fechaEleccion = fechaLargaWord($proceso['fecha_inicio_votacion'] ?? date('Y-m-d'));

    // Logo en base64
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
            size: letter;
            margin: 2cm 1.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.0;
            color: #333;
            mso-line-height-rule: exactly;
        }

        p {
            margin: 2px 0;
            line-height: 1.0;
            mso-line-height-rule: exactly;
        }

        br {
            mso-data-placement: same-cell;
        }

        table {
            border-collapse: collapse;
        }

        /* Secciones */
        .seccion {
            margin-bottom: 6px;
        }

        .seccion-titulo {
            font-size: 11pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin-bottom: 4px;
            margin-top: 8px;
            line-height: 1.0;
        }

        .seccion-contenido {
            text-align: justify;
            line-height: 1.0;
        }

        /* Tablas de contenido */
        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 9pt;
        }

        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 3px 5px;
        }

        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }

        /* Listas */
        ul {
            margin: 2px 0 2px 15px;
            padding-left: 0;
            line-height: 1.0;
        }

        ol {
            margin: 2px 0 2px 15px;
            padding-left: 15px;
            line-height: 1.0;
        }

        li {
            margin-bottom: 1px;
            line-height: 1.0;
        }
    </style>
</head>
<body>

<?php
// =====================================
// FUNCION PARA ENCABEZADO REUTILIZABLE WORD
// =====================================
function renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre) {
?>
<table width="100%" border="1" cellpadding="0" cellspacing="0"
       style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
    <tr>
        <td width="80" rowspan="2" align="center" valign="middle"
            bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
            <?php if (!empty($logoBase64)): ?>
            <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo" style="background-color:#ffffff;">
            <?php else: ?>
            <b style="font-size:8pt;"><?= esc($cliente['nombre_cliente']) ?></b>
            <?php endif; ?>
        </td>
        <td align="center" valign="middle"
            style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <td width="120" rowspan="2" valign="middle"
            style="border:1px solid #333; padding:0; font-size:8pt;">
            <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                    <td style="border-bottom:1px solid #333;"><b>Codigo:</b></td>
                    <td style="border-bottom:1px solid #333;"><?= esc($codigoDocumento) ?></td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #333;"><b>Version:</b></td>
                    <td style="border-bottom:1px solid #333;"><?= str_pad($versionDocumento ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                </tr>
                <tr>
                    <td><b>Fecha:</b></td>
                    <td><?= date('d/m/Y') ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" valign="middle"
            style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            ACTA DE CONSTITUCION DEL <?= esc($tipoComiteNombre) ?>
        </td>
    </tr>
</table>
<?php
}
?>

<!-- =====================================
     SECCION 1: ACTA DE APERTURA DE ELECCIONES
     ===================================== -->

<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">1. ACTA DE APERTURA DE ELECCIONES</div>
    <div class="seccion-contenido">
        <p>
            En las instalaciones de <b><?= esc($cliente['nombre_cliente']) ?></b>,
            NIT <?= esc($cliente['nit_cliente']) ?>, ubicada en <?= esc($cliente['direccion_cliente'] ?? 'la ciudad') ?>,
            siendo las <?= date('H:i') ?> horas del dia <b><?= $fechaEleccion ?></b>,
            se reunieron los trabajadores convocados para participar en la eleccion de los representantes
            de los trabajadores al <b><?= $tipoComiteCorto ?></b>.
        </p>
        <p>
            Se procedio a dar apertura formal al proceso de votacion con la presencia de los siguientes jurados:
        </p>

        <?php if (!empty($jurados)): ?>
        <table class="tabla-contenido">
            <tr>
                <th style="width: 40%; background-color: #e9ecef; color: #333;">Nombre</th>
                <th style="width: 30%; background-color: #e9ecef; color: #333;">Documento</th>
                <th style="width: 30%; background-color: #e9ecef; color: #333;">Rol</th>
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
        <p><i>No se registraron jurados para este proceso.</i></p>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================
     SECCION 2: REGISTRO DE VOTANTES
     ===================================== -->

<br clear="all" style="page-break-before:always">
<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">2. REGISTRO DE VOTANTES</div>
    <div class="seccion-contenido">
        <p>
            De un total de <b><?= $totalVotantes ?></b> trabajadores habilitados para votar,
            ejercieron su derecho al voto <b><?= $votaronCount ?></b> personas,
            lo que representa una participacion del <b><?= $participacion ?>%</b>.
        </p>

        <?php if (!empty($votantes)): ?>
        <table class="tabla-contenido" style="font-size: 8pt;">
            <tr>
                <th style="width: 5%; background-color: #e9ecef; color: #333;">#</th>
                <th style="width: 40%; background-color: #e9ecef; color: #333;">Nombre Completo</th>
                <th style="width: 25%; background-color: #e9ecef; color: #333;">Documento</th>
                <th style="width: 30%; background-color: #e9ecef; color: #333;">Cargo</th>
            </tr>
            <?php $i = 1; foreach ($votantes as $v): ?>
            <tr>
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

<br clear="all" style="page-break-before:always">
<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">3. ACTA DE CIERRE DE VOTACIONES</div>
    <div class="seccion-contenido">
        <p>
            Siendo las <?= date('H:i') ?> horas del dia <?= fechaLargaWord($proceso['fecha_fin_votacion'] ?? date('Y-m-d')) ?>,
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
                <th style="width: 8%; background-color: #e9ecef; color: #333;">#</th>
                <th style="width: 42%; background-color: #e9ecef; color: #333;">Candidato</th>
                <th style="width: 20%; background-color: #e9ecef; color: #333;">Votos</th>
                <th style="width: 30%; background-color: #e9ecef; color: #333;">Resultado</th>
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
                    <b>PRINCIPAL</b>
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

        <p style="text-align: center; margin-top: 8px;">
            <b>Participacion:</b> <?= $votaronCount ?> de <?= $totalVotantes ?> votantes (<?= $participacion ?>%)
        </p>
        <?php else: ?>
        <p><i>No hay resultados de votacion registrados.</i></p>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================
     SECCION 5: CONFORMACION DEL COMITE
     ===================================== -->

<br clear="all" style="page-break-before:always">
<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div class="seccion">
    <div class="seccion-titulo">5. CONFORMACION DEL <?= $proceso['tipo_comite'] ?></div>
    <div class="seccion-contenido">
        <p>
            De acuerdo con los resultados de la votacion y las designaciones realizadas por el empleador,
            el <b><?= $tipoComiteCorto ?></b> queda conformado de la siguiente manera para el periodo
            <b><?= $periodoInicio ?></b> al <b><?= $periodoFin ?></b>:
        </p>

        <!-- Representantes del Empleador -->
        <div style="margin-top: 12px;">
            <div style="background-color: #198754; color: white; padding: 5px 8px; font-weight: bold; font-size: 9pt;">
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
        <div style="margin-top: 12px;">
            <div style="background-color: #0d6efd; color: white; padding: 5px 8px; font-weight: bold; font-size: 9pt;">
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

<div class="seccion" style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
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
<br clear="all" style="page-break-before:always">
<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #6c757d; color: white; padding: 5px 8px; border: none;">
        FIRMAS DE JURADOS DE VOTACION
    </div>
    <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
        <tr>
            <td width="25%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Rol</td>
            <td width="35%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Nombre / Documento</td>
            <td width="40%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Firma</td>
        </tr>
        <?php foreach ($jurados as $j): ?>
        <tr>
            <td style="text-align: center; font-weight: bold; padding: 8px; border: 1px solid #999; font-size: 8pt;"><?= ucfirst($j['rol']) ?></td>
            <td style="padding: 8px; border: 1px solid #999; font-size: 8pt;">
                <b><?= $j['nombres'] ?> <?= $j['apellidos'] ?></b><br>
                <span style="color: #666;">C.C. <?= $j['documento_identidad'] ?></span>
            </td>
            <td style="height: 50px; vertical-align: bottom; text-align: center; padding: 8px; border: 1px solid #999;">
                <div style="border-top: 1px solid #333; width: 70%; margin: 0 auto; padding-top: 3px;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
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

<br clear="all" style="page-break-before:always">
<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<div style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
        FIRMAS DE APROBACION
    </div>
    <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
        <tr>
            <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
                Aprobo / Representante Legal
            </td>
            <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
                Reviso / <?= $requiereDelegado ? 'Delegado SST' : ($estandares <= 21 ? 'Vigia SST' : 'COPASST') ?>
            </td>
        </tr>
        <tr>
            <!-- REPRESENTANTE LEGAL -->
            <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0;"><b>Nombre:</b> <?= esc($repLegalNombre) ?></p>
                <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
                <p style="margin: 2px 0;"><b>Documento:</b> <?= esc($repLegalCedula) ?></p>
            </td>
            <!-- DELEGADO/VIGIA SST -->
            <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0;"><b>Nombre:</b> <?= esc($delegadoNombre) ?></p>
                <p style="margin: 2px 0;"><b>Cargo:</b> <?= $requiereDelegado ? esc($delegadoCargo) : ($estandares <= 21 ? 'Vigia SST' : 'COPASST') ?></p>
                <p style="margin: 2px 0;"><b>Documento:</b> <?= esc($delegadoCedula) ?></p>
            </td>
        </tr>
        <tr>
            <!-- Fila de firmas alineadas -->
            <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
            <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- =====================================
     SECCION 10: FIRMAS DE MIEMBROS DEL COMITE
     ===================================== -->

<br clear="all" style="page-break-before:always">
<?php renderEncabezadoWord($logoBase64, $codigoDocumento, $versionDocumento, $cliente, $tipoComiteNombre); ?>

<!-- Representantes del Empleador -->
<div style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
        FIRMAS DE MIEMBROS - REPRESENTANTES DEL EMPLEADOR
    </div>
    <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
        <tr>
            <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Miembro 1</td>
            <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Miembro 2</td>
        </tr>
        <?php
        $allEmpleador = array_merge($empleadorPrincipales, $empleadorSuplentes);
        $chunks = array_chunk($allEmpleador, 2);
        foreach ($chunks as $pair):
        ?>
        <tr>
            <?php foreach ($pair as $e): ?>
            <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0; text-align: center;">
                    <b style="background-color: <?= $e['tipo_plaza'] === 'principal' ? '#198754' : '#0d6efd' ?>; color: white; padding: 1px 6px; font-size: 7pt;">
                        <?= strtoupper($e['tipo_plaza']) ?>
                    </b>
                </p>
                <p style="margin: 2px 0;"><b>Nombre:</b> <?= $e['nombres'] ?> <?= $e['apellidos'] ?></p>
                <p style="margin: 2px 0;"><b>C.C.:</b> <?= $e['documento_identidad'] ?></p>
                <p style="margin: 2px 0;"><b>Cargo:</b> <?= $e['cargo'] ?? '-' ?></p>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td style="border: 1px solid #999;"></td>
            <?php endif; ?>
        </tr>
        <tr>
            <?php foreach ($pair as $e): ?>
            <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td style="border: 1px solid #999;"></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Representantes de Trabajadores -->
<div style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
        FIRMAS DE MIEMBROS - REPRESENTANTES DE LOS TRABAJADORES
    </div>
    <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
        <tr>
            <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Miembro 1</td>
            <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Miembro 2</td>
        </tr>
        <?php
        $allTrabajadores = array_merge($principales, $suplentes);
        $chunks = array_chunk($allTrabajadores, 2);
        foreach ($chunks as $pair):
        ?>
        <tr>
            <?php foreach ($pair as $t): ?>
            <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0; text-align: center;">
                    <b style="background-color: <?= $t['tipo_plaza'] === 'principal' ? '#198754' : '#0d6efd' ?>; color: white; padding: 1px 6px; font-size: 7pt;">
                        <?= strtoupper($t['tipo_plaza']) ?>
                    </b>
                </p>
                <p style="margin: 2px 0;"><b>Nombre:</b> <?= $t['nombres'] ?> <?= $t['apellidos'] ?></p>
                <p style="margin: 2px 0;"><b>C.C.:</b> <?= $t['documento_identidad'] ?></p>
                <p style="margin: 2px 0;"><b>Cargo:</b> <?= $t['cargo'] ?? '-' ?></p>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td style="border: 1px solid #999;"></td>
            <?php endif; ?>
        </tr>
        <tr>
            <?php foreach ($pair as $t): ?>
            <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
            <?php endforeach; ?>
            <?php if (count($pair) === 1): ?>
            <td style="border: 1px solid #999;"></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- PIE DE DOCUMENTO -->
<div style="margin-top:20px; padding-top:10px; border-top:1px solid #ccc; text-align:center; font-size:8pt; color:#666;">
    <p style="margin: 2px 0;">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
    <p style="margin: 2px 0;"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
</div>

</body>
</html>
