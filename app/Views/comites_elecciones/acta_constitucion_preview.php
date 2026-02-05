<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
// Documento (para sistema de firmas electrónicas - se conectará posteriormente)
$documento = $documento ?? ['id_documento' => null, 'estado' => 'borrador'];

// Funciones auxiliares
function fechaLargaActa($fecha) {
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $timestamp = strtotime($fecha);
    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);
    return "$dia de $mes de $anio";
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

$periodoInicio = fechaLargaActa($proceso['fecha_inicio_periodo'] ?? date('Y-m-d'));
$periodoFin = fechaLargaActa($proceso['fecha_fin_periodo'] ?? date('Y-m-d', strtotime('+2 years')));
$fechaEleccion = fechaLargaActa($proceso['fecha_inicio_votacion'] ?? date('Y-m-d'));
?>

<style>
@media print {
    .no-print { display: none !important; }
    .documento-contenido { padding: 20px !important; }
    body { font-size: 11pt; }
}

.panel-aprobacion {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    color: white;
}

.info-documento {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.encabezado-formal {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

.encabezado-formal td {
    border: 1px solid #333;
    vertical-align: middle;
}

.encabezado-logo {
    width: 150px;
    padding: 10px;
    text-align: center;
}

.encabezado-logo img {
    max-width: 130px;
    max-height: 70px;
    object-fit: contain;
}

.encabezado-titulo-central {
    text-align: center;
    padding: 0;
}

.encabezado-titulo-central .sistema {
    font-size: 0.85rem;
    font-weight: bold;
    color: #333;
    padding: 8px 15px;
    border-bottom: 1px solid #333;
}

.encabezado-titulo-central .nombre-doc {
    font-size: 0.85rem;
    font-weight: bold;
    color: #333;
    padding: 8px 15px;
}

.encabezado-info {
    width: 170px;
    padding: 0;
}

.encabezado-info-table {
    width: 100%;
    border-collapse: collapse;
}

.encabezado-info-table td {
    border: none;
    border-bottom: 1px solid #333;
    padding: 3px 8px;
    font-size: 0.75rem;
}

.encabezado-info-table tr:last-child td {
    border-bottom: none;
}

.encabezado-info-table .label {
    font-weight: bold;
}

.seccion {
    margin-bottom: 25px;
    page-break-inside: avoid;
}

.seccion-titulo {
    font-size: 1.1rem;
    font-weight: bold;
    color: #0d6efd;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.seccion-contenido {
    text-align: justify;
    line-height: 1.7;
}

.tabla-miembros {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}

.tabla-miembros th, .tabla-miembros td {
    border: 1px solid #dee2e6;
    padding: 10px;
    text-align: left;
}

.tabla-miembros th {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    font-weight: bold;
}

.tabla-miembros .principal {
    background-color: #d1e7dd;
}

.tabla-miembros .suplente {
    background-color: #cfe2ff;
}
</style>

<!-- BARRA DE HERRAMIENTAS -->
<div class="no-print bg-dark text-white py-2 sticky-top">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>"
                   class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Proceso
                </a>
            </div>
            <div class="d-flex gap-2">
                <?php
                $estadoDoc = $documento['estado'] ?? 'borrador';
                $idDocumento = $documento['id_documento'] ?? null;
                ?>
                <?php if ($estadoDoc === 'firmado'): ?>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/firmas/estado') ?>" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-patch-check me-1"></i>Ver Firmas
                    </a>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/firmas') ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Agregar Firmantes
                    </a>
                <?php elseif ($estadoDoc === 'pendiente_firma'): ?>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/firmas/estado') ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-clock-history me-1"></i>Estado Firmas
                    </a>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/firmas') ?>" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Agregar Firmantes
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/firmas') ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-pen me-1"></i>Solicitar Firmas
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acta/pdf') ?>"
                   class="btn btn-danger btn-sm" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF
                </a>
                <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acta/descargar') ?>"
                   class="btn btn-success btn-sm">
                    <i class="bi bi-download me-1"></i>Descargar PDF
                </a>
                <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acta/word') ?>"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-file-earmark-word me-1"></i>Descargar Word
                </a>
                <button onclick="window.print()" class="btn btn-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container my-4">
    <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">

        <!-- PANEL DE APROBACION -->
        <div class="panel-aprobacion no-print">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="badge bg-dark"><?= $codigoDocumento ?? 'FT-SST-013' ?></span>
                        <span class="badge bg-light text-dark">v<?= $versionDocumento ?? '1' ?>.0</span>
                        <span class="badge bg-<?= $proceso['estado'] === 'completado' ? 'success' : 'warning' ?>">
                            <i class="bi bi-<?= $proceso['estado'] === 'completado' ? 'check-circle' : 'clock' ?> me-1"></i>
                            <?= $proceso['estado'] === 'completado' ? 'Completado' : 'En Proceso' ?>
                        </span>
                    </div>
                    <h5 class="mb-1">Acta de Constitucion - <?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></h5>
                    <small class="opacity-75"><?= esc($cliente['nombre_cliente']) ?></small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-people me-1"></i><?= $totalVotantes ?> votantes
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-check2-all me-1"></i><?= $participacion ?>% participacion
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFO DOCUMENTO -->
        <div class="info-documento no-print">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Tipo de documento:</small><br>
                    <span class="fw-bold">Acta de Constitucion</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Periodo:</small><br>
                    <span class="fw-bold"><?= date('Y', strtotime($proceso['fecha_inicio_periodo'] ?? date('Y-m-d'))) ?> - <?= date('Y', strtotime($proceso['fecha_fin_periodo'] ?? date('Y-m-d', strtotime('+2 years')))) ?></span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Fecha eleccion:</small><br>
                    <span class="fw-bold"><?= date('d/m/Y', strtotime($proceso['fecha_inicio_votacion'] ?? date('Y-m-d'))) ?></span>
                </div>
            </div>
        </div>

        <!-- ENCABEZADO FORMAL -->
        <table class="encabezado-formal">
            <tr>
                <td class="encabezado-logo" rowspan="2">
                    <?php if (!empty($cliente['logo'])): ?>
                    <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo">
                    <?php else: ?>
                    <span class="text-muted">LOGO</span>
                    <?php endif; ?>
                </td>
                <td class="encabezado-titulo-central">
                    <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                </td>
                <td class="encabezado-info" rowspan="2">
                    <table class="encabezado-info-table">
                        <tr>
                            <td class="label">Codigo:</td>
                            <td><?= $codigoDocumento ?? 'FT-SST-013' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Version:</td>
                            <td><?= $versionDocumento ?? '1' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Fecha:</td>
                            <td><?= date('d/m/Y') ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="encabezado-titulo-central">
                    <div class="nombre-doc">ACTA DE CONSTITUCION DEL <?= $tipoComiteNombre ?></div>
                </td>
            </tr>
        </table>

        <!-- SECCION 1: ACTA DE APERTURA -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="bi bi-play-circle me-2"></i>1. ACTA DE APERTURA DE ELECCIONES
            </div>
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
                <table class="tabla-miembros">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Nombre</th>
                            <th style="width: 25%;">Documento</th>
                            <th style="width: 25%;">Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jurados as $j): ?>
                        <tr>
                            <td><?= $j['nombres'] ?> <?= $j['apellidos'] ?></td>
                            <td>C.C. <?= $j['documento_identidad'] ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($j['rol']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No se registraron jurados para este proceso.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCION 2: REGISTRO DE VOTANTES -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="bi bi-person-check me-2"></i>2. REGISTRO DE VOTANTES
            </div>
            <div class="seccion-contenido">
                <p>
                    De un total de <strong><?= $totalVotantes ?></strong> trabajadores habilitados para votar,
                    ejercieron su derecho al voto <strong><?= $votaronCount ?></strong> personas,
                    lo que representa una participacion del <strong><?= $participacion ?>%</strong>.
                </p>

                <?php if (!empty($votantes)): ?>
                <div class="table-responsive">
                    <table class="tabla-miembros" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Nombre Completo</th>
                                <th style="width: 25%;">Documento</th>
                                <th style="width: 30%;">Cargo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($votantes as $v): ?>
                            <tr>
                                <td class="text-center"><?= $i++ ?></td>
                                <td><?= $v['nombres'] ?> <?= $v['apellidos'] ?></td>
                                <td>C.C. <?= $v['documento_identidad'] ?></td>
                                <td><?= $v['cargo'] ?? '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCION 3: ACTA DE CIERRE -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="bi bi-stop-circle me-2"></i>3. ACTA DE CIERRE DE VOTACIONES
            </div>
            <div class="seccion-contenido">
                <p>
                    Siendo las <?= date('H:i') ?> horas, se procedio a cerrar la votacion.
                    Los jurados de votacion procedieron a realizar el conteo de votos en presencia
                    de los testigos designados, obteniendo los siguientes resultados.
                </p>
            </div>
        </div>

        <!-- SECCION 4: RESULTADO DE VOTACIONES -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="bi bi-bar-chart me-2"></i>4. RESULTADO DE LAS VOTACIONES
            </div>
            <div class="seccion-contenido">
                <p>El escrutinio de los votos arrojo los siguientes resultados para los candidatos de los trabajadores:</p>

                <?php if (!empty($resultadosVotacion)): ?>
                <table class="tabla-miembros">
                    <thead>
                        <tr>
                            <th style="width: 8%;">#</th>
                            <th style="width: 42%;">Candidato</th>
                            <th style="width: 20%; text-align: center;">Votos</th>
                            <th style="width: 30%; text-align: center;">Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pos = 1;
                        foreach ($resultadosVotacion as $r):
                            $esPrincipal = $pos <= $proceso['plazas_principales'];
                            $esSuplente = !$esPrincipal && $pos <= ($proceso['plazas_principales'] + $proceso['plazas_suplentes']);
                        ?>
                        <tr class="<?= $esPrincipal ? 'principal' : ($esSuplente ? 'suplente' : '') ?>">
                            <td class="text-center"><?= $pos ?></td>
                            <td><?= $r['nombres'] ?> <?= $r['apellidos'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $r['votos_obtenidos'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($esPrincipal): ?>
                                <span class="badge bg-success">PRINCIPAL</span>
                                <?php elseif ($esSuplente): ?>
                                <span class="badge bg-info">SUPLENTE</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">No elegido</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $pos++; endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>No hay resultados de votacion registrados.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCION 5: CONFORMACION DEL COMITE -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="bi bi-people me-2"></i>5. CONFORMACION DEL <?= $proceso['tipo_comite'] ?>
            </div>
            <div class="seccion-contenido">
                <p>
                    De acuerdo con los resultados de la votacion y las designaciones realizadas por el empleador,
                    el <strong><?= $tipoComiteCorto ?></strong> queda conformado de la siguiente manera para el periodo
                    <strong><?= $periodoInicio ?></strong> al <strong><?= $periodoFin ?></strong>:
                </p>

                <!-- Representantes del Empleador -->
                <h6 class="mt-4 mb-3" style="color: #198754;">
                    <i class="bi bi-building me-2"></i>REPRESENTANTES DEL EMPLEADOR
                </h6>
                <table class="tabla-miembros">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Nombre</th>
                            <th style="width: 35%;">Cargo</th>
                            <th style="width: 20%; text-align: center;">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleadorPrincipales as $e): ?>
                        <tr class="principal">
                            <td><?= $e['nombres'] ?> <?= $e['apellidos'] ?></td>
                            <td><?= $e['cargo'] ?? '-' ?></td>
                            <td class="text-center"><span class="badge bg-success">Principal</span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php foreach ($empleadorSuplentes as $e): ?>
                        <tr class="suplente">
                            <td><?= $e['nombres'] ?> <?= $e['apellidos'] ?></td>
                            <td><?= $e['cargo'] ?? '-' ?></td>
                            <td class="text-center"><span class="badge bg-info">Suplente</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Representantes de Trabajadores -->
                <h6 class="mt-4 mb-3" style="color: #0d6efd;">
                    <i class="bi bi-person-check me-2"></i>REPRESENTANTES DE LOS TRABAJADORES
                </h6>
                <table class="tabla-miembros">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Nombre</th>
                            <th style="width: 35%;">Cargo</th>
                            <th style="width: 20%; text-align: center;">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($principales as $t): ?>
                        <tr class="principal">
                            <td><?= $t['nombres'] ?> <?= $t['apellidos'] ?></td>
                            <td><?= $t['cargo'] ?? '-' ?></td>
                            <td class="text-center"><span class="badge bg-success">Principal</span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php foreach ($suplentes as $t): ?>
                        <tr class="suplente">
                            <td><?= $t['nombres'] ?> <?= $t['apellidos'] ?></td>
                            <td><?= $t['cargo'] ?? '-' ?></td>
                            <td class="text-center"><span class="badge bg-info">Suplente</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCION 6: FUNCIONES DEL COMITE -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="bi bi-list-task me-2"></i>6. FUNCIONES DEL <?= $proceso['tipo_comite'] ?>
            </div>
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
                <?php else: ?>
                <p>Las funciones del comite seran las establecidas en la normatividad vigente aplicable.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCION 7: CONTROL DE CAMBIOS -->
        <div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
            <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                <i class="bi bi-journal-text me-2"></i>CONTROL DE CAMBIOS
            </div>
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                        <th style="width: 100px; text-align: center;">Version</th>
                        <th>Descripcion del Cambio</th>
                        <th style="width: 130px; text-align: center;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">
                            <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px;">
                                v<?= $versionDocumento ?? '1' ?>.0
                            </span>
                        </td>
                        <td>Creacion del documento - Acta de Constitucion del <?= $proceso['tipo_comite'] ?> periodo <?= $proceso['anio'] ?></td>
                        <td style="text-align: center;"><?= date('d/m/Y') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SECCION 8: FIRMAS DE JURADOS DE VOTACION -->
        <?php if (!empty($jurados)): ?>
        <div class="seccion" style="margin-top: 40px; page-break-inside: avoid;">
            <div class="seccion-titulo" style="background: linear-gradient(90deg, #6c757d, #495057); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                <i class="bi bi-person-badge me-2"></i>FIRMAS DE JURADOS DE VOTACION
            </div>
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background: #e9ecef;">
                        <th style="width: 25%; text-align: center;">Rol</th>
                        <th style="width: 35%;">Nombre / Documento</th>
                        <th style="width: 40%; text-align: center;">Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jurados as $j): ?>
                    <?php
                    $tipoFirmaJurado = 'jurado_' . $j['id_jurado'];
                    $firmaJurado = ($firmasElectronicas ?? [])[$tipoFirmaJurado] ?? null;
                    ?>
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= ucfirst($j['rol']) ?></span>
                        </td>
                        <td>
                            <strong><?= $j['nombres'] ?> <?= $j['apellidos'] ?></strong><br>
                            <small class="text-muted">C.C. <?= $j['documento_identidad'] ?></small>
                        </td>
                        <td style="height: 60px; vertical-align: bottom; text-align: center;">
                            <?php if ($firmaJurado && !empty($firmaJurado['evidencia']['firma_imagen'])): ?>
                                <img src="<?= $firmaJurado['evidencia']['firma_imagen'] ?>" style="max-height: 40px; max-width: 120px;"><br>
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 70%; margin: 0 auto; padding-top: 5px;">
                                <small style="color: #666;">Firma</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- SECCION 9: FIRMAS DE APROBACION (Rep Legal + Delegado/Vigia) -->
        <?php
        // Datos del contexto para firmas
        $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '';
        $repLegalCedula = $contexto['representante_legal_cedula'] ?? $cliente['cedula_rep_legal'] ?? '';
        $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
        $delegadoCedula = $contexto['delegado_sst_cedula'] ?? '';
        $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
        $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
        $estandares = $contexto['estandares_aplicables'] ?? 60;
        ?>
        <div class="seccion" style="margin-top: 40px; page-break-inside: avoid;">
            <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
            </div>
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background: #e9ecef;">
                        <th style="width: 50%; text-align: center;">
                            <i class="bi bi-person-check me-1"></i>Aprobo / Representante Legal
                        </th>
                        <th style="width: 50%; text-align: center;">
                            <i class="bi bi-shield-check me-1"></i>Reviso / <?= $requiereDelegado ? 'Delegado SST' : ($estandares <= 21 ? 'Vigia SST' : 'COPASST') ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="margin-bottom: 5px;">
                                <strong>Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 180px;">
                                    <?= esc($repLegalNombre) ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 5px;">
                                <strong>Cargo:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 180px;">
                                    Representante Legal
                                </span>
                            </div>
                            <div style="margin-bottom: 5px;">
                                <strong>C.C.:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px;">
                                    <?= esc($repLegalCedula) ?>
                                </span>
                            </div>
                        </td>
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="margin-bottom: 5px;">
                                <strong>Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 180px;">
                                    <?= esc($delegadoNombre) ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 5px;">
                                <strong>Cargo:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 180px;">
                                    <?= $requiereDelegado ? esc($delegadoCargo) : ($estandares <= 21 ? 'Vigia SST' : 'COPASST') ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 5px;">
                                <strong>C.C.:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px;">
                                    <?= esc($delegadoCedula) ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 80px; text-align: center; vertical-align: bottom;">
                            <?php
                            $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                            if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                            ?>
                                <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px; max-width: 150px;"><br>
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 65%; margin: 5px auto 0; padding-top: 5px;">
                                <small style="color: #666;">Firma</small>
                            </div>
                        </td>
                        <td style="height: 80px; text-align: center; vertical-align: bottom;">
                            <?php
                            $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? ($firmasElectronicas ?? [])['vigia_sst'] ?? null;
                            if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                            ?>
                                <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" style="max-height: 50px; max-width: 150px;"><br>
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 65%; margin: 5px auto 0; padding-top: 5px;">
                                <small style="color: #666;">Firma</small>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SECCION 10: FIRMAS DE MIEMBROS DEL COMITE -->
        <div class="seccion" style="margin-top: 40px; page-break-inside: avoid;">
            <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                <i class="bi bi-people me-2"></i>FIRMAS DE MIEMBROS DEL <?= $proceso['tipo_comite'] ?>
            </div>

            <!-- Representantes del Empleador -->
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #d1e7dd, #badbcc);">
                        <th colspan="2" class="text-center" style="color: #0f5132;">
                            <i class="bi bi-building me-1"></i>REPRESENTANTES DEL EMPLEADOR
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $allEmpleador = array_merge($empleadorPrincipales, $empleadorSuplentes);
                    $chunks = array_chunk($allEmpleador, 2);
                    foreach ($chunks as $pair):
                    ?>
                    <tr>
                        <?php foreach ($pair as $e): ?>
                        <?php
                        $tipoFirmaEmpleador = 'empleador_' . $e['tipo_plaza'] . '_' . $e['id_candidato'];
                        $firmaEmpleador = ($firmasElectronicas ?? [])[$tipoFirmaEmpleador] ?? null;
                        ?>
                        <td style="width: 50%; padding: 15px; vertical-align: top;">
                            <div class="text-center mb-2">
                                <span class="badge bg-<?= $e['tipo_plaza'] === 'principal' ? 'success' : 'info' ?>">
                                    <?= ucfirst($e['tipo_plaza']) ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 3px; font-size: 0.8rem;">
                                <strong>Nombre:</strong> <?= $e['nombres'] ?> <?= $e['apellidos'] ?>
                            </div>
                            <div style="margin-bottom: 3px; font-size: 0.8rem;">
                                <strong>C.C.:</strong> <?= $e['documento_identidad'] ?>
                            </div>
                            <div style="margin-bottom: 3px; font-size: 0.8rem;">
                                <strong>Cargo:</strong> <?= $e['cargo'] ?? '-' ?>
                            </div>
                            <div style="text-align: center; margin-top: 15px;">
                                <?php if ($firmaEmpleador && !empty($firmaEmpleador['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaEmpleador['evidencia']['firma_imagen'] ?>" style="max-height: 40px; max-width: 120px;"><br>
                                <?php endif; ?>
                                <div style="border-top: 1px solid #333; width: 70%; margin: 0 auto; padding-top: 3px;">
                                    <small style="color: #666;">Firma</small>
                                </div>
                            </div>
                        </td>
                        <?php endforeach; ?>
                        <?php if (count($pair) === 1): ?>
                        <td style="width: 50%;"></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Representantes de Trabajadores -->
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none; margin-top: -1px;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #cfe2ff, #b6d4fe);">
                        <th colspan="2" class="text-center" style="color: #084298;">
                            <i class="bi bi-person-check me-1"></i>REPRESENTANTES DE LOS TRABAJADORES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $allTrabajadores = array_merge($principales, $suplentes);
                    $chunks = array_chunk($allTrabajadores, 2);
                    foreach ($chunks as $pair):
                    ?>
                    <tr>
                        <?php foreach ($pair as $t): ?>
                        <?php
                        $tipoFirmaTrabajador = 'trabajador_' . $t['tipo_plaza'] . '_' . $t['id_candidato'];
                        $firmaTrabajador = ($firmasElectronicas ?? [])[$tipoFirmaTrabajador] ?? null;
                        ?>
                        <td style="width: 50%; padding: 15px; vertical-align: top;">
                            <div class="text-center mb-2">
                                <span class="badge bg-<?= $t['tipo_plaza'] === 'principal' ? 'success' : 'info' ?>">
                                    <?= ucfirst($t['tipo_plaza']) ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 3px; font-size: 0.8rem;">
                                <strong>Nombre:</strong> <?= $t['nombres'] ?> <?= $t['apellidos'] ?>
                            </div>
                            <div style="margin-bottom: 3px; font-size: 0.8rem;">
                                <strong>C.C.:</strong> <?= $t['documento_identidad'] ?>
                            </div>
                            <div style="margin-bottom: 3px; font-size: 0.8rem;">
                                <strong>Cargo:</strong> <?= $t['cargo'] ?? '-' ?>
                            </div>
                            <div style="text-align: center; margin-top: 15px;">
                                <?php if ($firmaTrabajador && !empty($firmaTrabajador['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaTrabajador['evidencia']['firma_imagen'] ?>" style="max-height: 40px; max-width: 120px;"><br>
                                <?php endif; ?>
                                <div style="border-top: 1px solid #333; width: 70%; margin: 0 auto; padding-top: 3px;">
                                    <small style="color: #666;">Firma</small>
                                </div>
                            </div>
                        </td>
                        <?php endforeach; ?>
                        <?php if (count($pair) === 1): ?>
                        <td style="width: 50%;"></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PIE DE DOCUMENTO -->
        <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
            <p class="mb-1">Documento generado el <?= date('d/m/Y H:i') ?> - Sistema de Gestion SST</p>
            <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
