<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Recomposicion <?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></title>
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
        'COCOLAB' => 'Comite de Convivencia Laboral',
        'BRIGADA' => 'Brigada de Emergencias',
        'VIGIA' => 'Vigia SST'
    ][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];

    $motivosLabels = [
        'terminacion_contrato' => 'Terminacion del contrato de trabajo',
        'renuncia_voluntaria' => 'Renuncia voluntaria al comite',
        'sancion_disciplinaria' => 'Sancion disciplinaria por falta grave',
        'violacion_confidencialidad' => 'Violacion del deber de confidencialidad',
        'inasistencia_reiterada' => 'Inasistencia a mas de 3 reuniones consecutivas sin justificacion',
        'incumplimiento_funciones' => 'Incumplimiento reiterado de obligaciones',
        'fallecimiento' => 'Fallecimiento',
        'otro' => 'Otro motivo'
    ];

    $tiposIngreso = [
        'siguiente_votacion' => 'candidato con mayor votacion no elegido en el proceso electoral',
        'designacion_empleador' => 'designacion directa del empleador',
        'asamblea_extraordinaria' => 'eleccion en asamblea extraordinaria de trabajadores'
    ];

    $periodoInicio = fechaLargaPdf($proceso['fecha_inicio_periodo'] ?? date('Y-m-d'));
    $periodoFin = fechaLargaPdf($proceso['fecha_fin_periodo'] ?? date('Y-m-d', strtotime('+2 years')));
    $fechaRecomposicion = fechaLargaPdf($recomposicion['fecha_recomposicion']);
    $fechaSalida = fechaLargaPdf($recomposicion['fecha_efectiva_salida']);

    // Logo en base64
    $logoPath = FCPATH . 'uploads/' . ($cliente['logo'] ?? '');
    $logoBase64 = '';
    if (file_exists($logoPath) && !empty($cliente['logo'])) {
        $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Datos del entrante
    $nombreEntrante = $entrante
        ? trim($entrante['nombres'] . ' ' . $entrante['apellidos'])
        : trim(($recomposicion['entrante_nombres'] ?? '') . ' ' . ($recomposicion['entrante_apellidos'] ?? ''));
    $documentoEntrante = $entrante
        ? $entrante['documento_identidad']
        : ($recomposicion['entrante_documento'] ?? '');
    $cargoEntrante = $entrante
        ? $entrante['cargo']
        : ($recomposicion['entrante_cargo'] ?? '');

    // Nombre y documento del saliente
    $nombreSaliente = trim($saliente['nombres'] . ' ' . $saliente['apellidos']);
    $documentoSaliente = $saliente['documento_identidad'];
    $cargoSaliente = $saliente['cargo'];
    $representacionSaliente = $saliente['representacion'] === 'trabajador' ? 'los Trabajadores' : 'el Empleador';
    $tipoPlaza = ucfirst($saliente['tipo_plaza'] ?? 'principal');

    // Codigo del documento
    $codigoDocumento = 'FT-SST-' . ($proceso['tipo_comite'] === 'COCOLAB' ? '155' : '156');
    ?>
    <style>
        @page {
            margin: 2cm 1.5cm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
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
            margin-bottom: 15px;
        }

        .seccion-titulo {
            font-size: 11pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 3px;
            margin-bottom: 8px;
            margin-top: 12px;
        }

        .seccion-contenido {
            text-align: justify;
            line-height: 1.4;
        }

        .seccion-contenido p {
            margin: 5px 0;
        }

        /* Tablas */
        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9pt;
        }

        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 6px 8px;
        }

        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Listas */
        ol, ul {
            margin: 5px 0 5px 20px;
            padding-left: 15px;
        }

        li {
            margin-bottom: 3px;
        }

        /* Cuadro de reemplazo */
        .cuadro-reemplazo {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            background-color: #fff5f5;
        }

        .cuadro-reemplazo h4 {
            color: #dc3545;
            margin: 0 0 10px 0;
            font-size: 11pt;
        }

        .cuadro-ingreso {
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            background-color: #f0fff4;
        }

        .cuadro-ingreso h4 {
            color: #28a745;
            margin: 0 0 10px 0;
            font-size: 11pt;
        }

        /* Firmas */
        .firmas-container {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .firma-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 20px 2%;
            vertical-align: top;
        }

        .firma-linea {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 8px;
        }

        .firma-nombre {
            font-weight: bold;
            font-size: 10pt;
        }

        .firma-cargo {
            font-size: 9pt;
            color: #666;
        }

        /* Pie de documento */
        .pie-documento {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        .badge-tipo {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-a {
            background-color: #6c757d;
            color: white;
        }

        .badge-b {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>

<!-- Encabezado -->
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
                    <td>001</td>
                </tr>
                <tr>
                    <td class="label">Fecha:</td>
                    <td><?= fechaCortaPdf($recomposicion['fecha_recomposicion']) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="encabezado-titulo-central" valign="middle">
            <div class="nombre-doc">ACTA DE RECOMPOSICION DEL <?= esc($tipoComiteNombre) ?></div>
        </td>
    </tr>
</table>

<!-- Seccion 1: Introduccion -->
<div class="seccion">
    <div class="seccion-titulo">1. INTRODUCCION</div>
    <div class="seccion-contenido">
        <p>
            En la ciudad de <?= esc($cliente['ciudad_cliente'] ?? 'Colombia') ?>,
            el dia <strong><?= $fechaRecomposicion ?></strong>,
            se procede a formalizar la recomposicion numero <strong><?= $recomposicion['numero_recomposicion'] ?></strong>
            del <?= $tipoComiteCorto ?> de la empresa <strong><?= esc($cliente['nombre_cliente']) ?></strong>,
            identificada con NIT <strong><?= esc($cliente['nit_cliente']) ?></strong>,
            cuyo periodo de vigencia comprende desde el <?= $periodoInicio ?> hasta el <?= $periodoFin ?>.
        </p>
        <p>
            La presente recomposicion se realiza en cumplimiento de las disposiciones legales vigentes
            que regulan el funcionamiento del <?= $tipoComiteCorto ?>, garantizando la continuidad
            de sus funciones y responsabilidades en materia de seguridad y salud en el trabajo.
        </p>
    </div>
</div>

<!-- Seccion 2: Funciones del Comite -->
<div class="seccion">
    <div class="seccion-titulo">2. FUNCIONES DEL <?= strtoupper($tipoComiteCorto) ?></div>
    <div class="seccion-contenido">
        <?php if ($proceso['tipo_comite'] === 'COCOLAB'): ?>
        <p>De acuerdo con la Resolucion 652 de 2012, modificada por la Resolucion 1356 de 2012, el Comite de Convivencia Laboral tiene las siguientes funciones:</p>
        <ol>
            <li>Recibir y dar tramite a las quejas presentadas en las que se describan situaciones que puedan constituir acoso laboral.</li>
            <li>Examinar de manera confidencial los casos especificos en los que se formule queja o reclamo.</li>
            <li>Escuchar a las partes involucradas de manera individual sobre los hechos que dieron lugar a la queja.</li>
            <li>Adelantar reuniones con el fin de crear un espacio de dialogo entre las partes involucradas.</li>
            <li>Formular un plan de mejora concertado entre las partes para construir, renovar y promover la convivencia laboral.</li>
            <li>Hacer seguimiento a los compromisos adquiridos por las partes involucradas en la queja.</li>
            <li>Presentar a la alta direccion de la empresa las recomendaciones para el desarrollo efectivo de las medidas preventivas y correctivas del acoso laboral.</li>
        </ol>
        <?php else: ?>
        <p>De acuerdo con el Decreto 1072 de 2015, el COPASST tiene las siguientes funciones:</p>
        <ol>
            <li>Proponer y participar en actividades de capacitacion en seguridad y salud en el trabajo.</li>
            <li>Vigilar el desarrollo de las actividades de medicina, higiene y seguridad industrial.</li>
            <li>Colaborar en el analisis de las causas de los accidentes de trabajo y enfermedades profesionales.</li>
            <li>Visitar periodicamente los lugares de trabajo e inspeccionar los ambientes, maquinas, equipos y operaciones.</li>
            <li>Proponer actividades de capacitacion en salud ocupacional dirigidas a todos los niveles de la empresa.</li>
            <li>Servir como organismo de coordinacion entre el empleador y los trabajadores en la solucion de los problemas relativos a la SST.</li>
        </ol>
        <?php endif; ?>
    </div>
</div>

<!-- Seccion 3: Causales de Retiro -->
<div class="seccion">
    <div class="seccion-titulo">3. CAUSALES DE RETIRO DE MIEMBROS</div>
    <div class="seccion-contenido">
        <p>Conforme a la normatividad vigente, un miembro del <?= $tipoComiteCorto ?> puede ser retirado por las siguientes causales:</p>
        <ol>
            <li>Terminacion del contrato de trabajo.</li>
            <li>Renuncia voluntaria al comite.</li>
            <li>Sancion disciplinaria por falta grave.</li>
            <li>Violacion del deber de confidencialidad.</li>
            <li>Inasistencia a mas de tres (3) reuniones consecutivas sin justificacion.</li>
            <li>Incumplimiento reiterado de las obligaciones como miembro del comite.</li>
            <li>Fallecimiento.</li>
        </ol>
    </div>
</div>

<!-- Seccion 4: Miembro que Sale -->
<div class="seccion">
    <div class="seccion-titulo">4. MIEMBRO SALIENTE</div>
    <div class="seccion-contenido">
        <div class="cuadro-reemplazo">
            <h4>RETIRO DE MIEMBRO</h4>
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 35%; border: none; padding: 5px;"><strong>Nombre completo:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($nombreSaliente) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Documento de identidad:</strong></td>
                    <td style="border: none; padding: 5px;">C.C. <?= esc($documentoSaliente) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Cargo en la empresa:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($cargoSaliente) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Representacion:</strong></td>
                    <td style="border: none; padding: 5px;">Representante de <?= $representacionSaliente ?> (<?= $tipoPlaza ?>)</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Causal de retiro:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($motivosLabels[$recomposicion['motivo_salida']] ?? $recomposicion['motivo_salida']) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Fecha efectiva de salida:</strong></td>
                    <td style="border: none; padding: 5px;"><?= $fechaSalida ?></td>
                </tr>
                <?php if (!empty($recomposicion['motivo_detalle'])): ?>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Observaciones:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($recomposicion['motivo_detalle']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Seccion 5: Miembro Entrante -->
<div class="seccion">
    <div class="seccion-titulo">5. MIEMBRO ENTRANTE</div>
    <div class="seccion-contenido">
        <p>
            Para garantizar la continuidad del <?= $tipoComiteCorto ?>, se procede a incorporar
            un nuevo miembro mediante <strong><?= esc($tiposIngreso[$recomposicion['tipo_ingreso']] ?? $recomposicion['tipo_ingreso']) ?></strong>,
            quien ocupara la vacante dejada por el miembro saliente.
        </p>

        <div class="cuadro-ingreso">
            <h4>INGRESO DE NUEVO MIEMBRO</h4>
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 35%; border: none; padding: 5px;"><strong>Nombre completo:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($nombreEntrante) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Documento de identidad:</strong></td>
                    <td style="border: none; padding: 5px;">C.C. <?= esc($documentoEntrante) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Cargo en la empresa:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($cargoEntrante) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Representacion:</strong></td>
                    <td style="border: none; padding: 5px;">Representante de <?= $representacionSaliente ?> (<?= $tipoPlaza ?>)</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Tipo de ingreso:</strong></td>
                    <td style="border: none; padding: 5px;"><?= esc($tiposIngreso[$recomposicion['tipo_ingreso']] ?? $recomposicion['tipo_ingreso']) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 5px;"><strong>Fecha de ingreso:</strong></td>
                    <td style="border: none; padding: 5px;"><?= $fechaRecomposicion ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Seccion 6: Conformacion Actual del Comite -->
<div class="seccion">
    <div class="seccion-titulo">6. CONFORMACION ACTUAL DEL COMITE</div>
    <div class="seccion-contenido">
        <p>
            Posterior a esta recomposicion, el <?= $tipoComiteCorto ?> queda conformado de la siguiente manera.
            Los miembros marcados con <span class="badge-tipo badge-a">(A)</span> son continuantes del proceso original,
            y los marcados con <span class="badge-tipo badge-b">(B)</span> son nuevos integrantes por recomposicion.
        </p>

        <table class="tabla-contenido">
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Nombre</th>
                <th style="width: 20%;">Documento</th>
                <th style="width: 25%;">Cargo</th>
                <th style="width: 10%;">Plaza</th>
                <th style="width: 10%;">Tipo</th>
            </tr>
            <?php
            // Separar por representacion
            $trabajadores = array_filter($miembrosActuales, fn($m) => $m['representacion'] === 'trabajador');
            $empleadores = array_filter($miembrosActuales, fn($m) => $m['representacion'] === 'empleador');
            $contador = 1;
            ?>

            <!-- Representantes de Trabajadores -->
            <tr>
                <td colspan="6" style="background-color: #e3f2fd; font-weight: bold; text-align: center;">
                    REPRESENTANTES DE LOS TRABAJADORES
                </td>
            </tr>
            <?php foreach ($trabajadores as $m): ?>
            <tr>
                <td style="text-align: center;"><?= $contador++ ?></td>
                <td><?= esc($m['nombres'] . ' ' . $m['apellidos']) ?></td>
                <td style="text-align: center;">C.C. <?= esc($m['documento_identidad']) ?></td>
                <td><?= esc($m['cargo']) ?></td>
                <td style="text-align: center;"><?= ucfirst($m['tipo_plaza'] ?? 'Principal') ?></td>
                <td style="text-align: center;">
                    <?php if (isset($m['es_nuevo']) && $m['es_nuevo']): ?>
                        <span class="badge-tipo badge-b">(B)</span>
                    <?php else: ?>
                        <span class="badge-tipo badge-a">(A)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- Representantes del Empleador -->
            <tr>
                <td colspan="6" style="background-color: #e8f5e9; font-weight: bold; text-align: center;">
                    REPRESENTANTES DEL EMPLEADOR
                </td>
            </tr>
            <?php foreach ($empleadores as $m): ?>
            <tr>
                <td style="text-align: center;"><?= $contador++ ?></td>
                <td><?= esc($m['nombres'] . ' ' . $m['apellidos']) ?></td>
                <td style="text-align: center;">C.C. <?= esc($m['documento_identidad']) ?></td>
                <td><?= esc($m['cargo']) ?></td>
                <td style="text-align: center;"><?= ucfirst($m['tipo_plaza'] ?? 'Principal') ?></td>
                <td style="text-align: center;">
                    <?php if (isset($m['es_nuevo']) && $m['es_nuevo']): ?>
                        <span class="badge-tipo badge-b">(B)</span>
                    <?php else: ?>
                        <span class="badge-tipo badge-a">(A)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Seccion 7: Fundamento Legal -->
<?php if (!empty($recomposicion['justificacion_legal'])): ?>
<div class="seccion">
    <div class="seccion-titulo">7. FUNDAMENTO LEGAL</div>
    <div class="seccion-contenido">
        <p><?= nl2br(esc($recomposicion['justificacion_legal'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Seccion 8: Firmas -->
<div class="seccion firmas-container">
    <div class="seccion-titulo"><?= !empty($recomposicion['justificacion_legal']) ? '8' : '7' ?>. FIRMAS</div>
    <div class="seccion-contenido">
        <p>
            Para constancia de lo anterior, se firma la presente acta de recomposicion
            en la ciudad de <?= esc($cliente['ciudad_cliente'] ?? 'Colombia') ?>,
            a los <?= date('d', strtotime($recomposicion['fecha_recomposicion'])) ?> dias
            del mes de <?= strtolower(fechaLargaPdf($recomposicion['fecha_recomposicion'])) ?>.
        </p>

        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 48%; text-align: center; vertical-align: top; padding: 20px;">
                    <div class="firma-linea">
                        <div class="firma-nombre"><?= esc($cliente['nombre_rep_legal'] ?? 'Representante Legal') ?></div>
                        <div class="firma-cargo">Representante Legal</div>
                        <div class="firma-cargo">C.C. <?= esc($cliente['cedula_rep_legal'] ?? '') ?></div>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; text-align: center; vertical-align: top; padding: 20px;">
                    <div class="firma-linea">
                        <div class="firma-nombre"><?= esc($nombreEntrante) ?></div>
                        <div class="firma-cargo">Nuevo Miembro del <?= $tipoComiteCorto ?></div>
                        <div class="firma-cargo">C.C. <?= esc($documentoEntrante) ?></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Pie de documento -->
<div class="pie-documento">
    <?= esc($cliente['nombre_cliente']) ?> - NIT <?= esc($cliente['nit_cliente']) ?><br>
    Acta de Recomposicion del <?= $tipoComiteCorto ?> - Recomposicion No. <?= $recomposicion['numero_recomposicion'] ?>
</div>

</body>
</html>
