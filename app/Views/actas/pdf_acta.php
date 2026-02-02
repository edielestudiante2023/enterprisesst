<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php
    // Usar código de documento pasado desde el controlador
    $codigo = $codigoDocumento ?? 'ACT-GEN';
    $version = $versionDocumento ?? '001';
    $tipoNombre = $comite['tipo_nombre'] ?? 'Reunion SST';

    // Convertir a mayúsculas sin problemas de acentos
    $tipoNombreSinAcentos = str_replace(
        ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
        ['A','E','I','O','U','A','E','I','O','U','N','N'],
        $tipoNombre
    );
    $tituloDocumento = 'ACTA DE ' . strtoupper($tipoNombreSinAcentos);

    // Función para fecha en español
    function fechaEspanol($fecha) {
        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $timestamp = strtotime($fecha);
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('n', $timestamp) - 1];
        $anio = date('Y', $timestamp);
        return "$dia de $mes de $anio";
    }
    ?>
    <title><?= $codigo ?> <?= $tituloDocumento ?></title>
    <style>
        /* ============================================
           ESTILOS ESTÁNDAR PDF - SG-SST
           Según AA_PDF_ENCABEZADO.md, AA_PDF_CUERPO_DOCUMENTO.md
           ============================================ */

        @page {
            margin: 2cm 1.5cm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.15;
            color: #333;
        }

        /* ============================================
           ENCABEZADO FORMAL
           ============================================ */
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

        .encabezado-info-table .valor {
            color: #333;
        }

        /* ============================================
           SECCIONES DEL CONTENIDO
           ============================================ */
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

        /* ============================================
           TABLAS DE CONTENIDO
           ============================================ */
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

        /* ============================================
           TABLA INFO REUNIÓN
           ============================================ */
        .info-reunion {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        .info-reunion td {
            padding: 5px 8px;
            border: 1px solid #999;
        }

        .info-reunion .label {
            background-color: #e9ecef;
            font-weight: bold;
            width: 15%;
        }

        /* ============================================
           ORDEN DEL DÍA
           ============================================ */
        .orden-dia-lista {
            margin: 3px 0 3px 15px;
            padding-left: 15px;
        }

        .orden-dia-lista li {
            margin-bottom: 2px;
        }

        /* ============================================
           DESARROLLO DE PUNTOS
           ============================================ */
        .desarrollo-punto {
            margin-bottom: 8px;
            padding: 8px;
            background-color: #fafafa;
            border-left: 3px solid #0d6efd;
        }

        .desarrollo-punto h4 {
            margin: 0 0 5px 0;
            font-size: 10pt;
            color: #0d6efd;
        }

        .desarrollo-punto p {
            margin: 0;
            white-space: pre-line;
            font-size: 9pt;
            line-height: 1.2;
        }

        /* ============================================
           BADGES
           ============================================ */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-success {
            background-color: #198754;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        /* ============================================
           PIE DE DOCUMENTO
           ============================================ */
        .pie-documento {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        br {
            line-height: 0.5;
        }
    </style>
</head>
<body>
    <!-- ============================================== -->
    <!-- ENCABEZADO FORMAL ESTÁNDAR                    -->
    <!-- ============================================== -->
    <table class="encabezado-formal" cellpadding="0" cellspacing="0">
        <tr>
            <td class="encabezado-logo" rowspan="2">
                <?php if (!empty($cliente['logo'])): ?>
                    <img src="<?= $cliente['logo'] ?>" alt="Logo">
                <?php else: ?>
                    <div style="font-size: 8pt; font-weight: bold;"><?= esc($cliente['nombre_cliente']) ?></div>
                <?php endif; ?>
            </td>
            <td class="encabezado-titulo-central">
                <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <td class="encabezado-info" rowspan="2">
                <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Codigo:</td>
                        <td class="valor"><?= esc($codigo) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Version:</td>
                        <td class="valor"><?= esc($version) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Fecha:</td>
                        <td class="valor"><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="encabezado-titulo-central">
                <div class="nombre-doc"><?= $tituloDocumento ?></div>
            </td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- 1. INFORMACIÓN DE LA REUNIÓN                  -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo">1. INFORMACION DE LA REUNION</div>
        <table class="info-reunion">
            <tr>
                <td class="label">Acta No:</td>
                <td><strong><?= esc($acta['numero_acta']) ?></strong></td>
                <td class="label">Fecha:</td>
                <td><?= fechaEspanol($acta['fecha_reunion']) ?></td>
            </tr>
            <tr>
                <td class="label">Hora:</td>
                <td><?= date('H:i', strtotime($acta['hora_inicio'])) ?> - <?= date('H:i', strtotime($acta['hora_fin'])) ?></td>
                <td class="label">Modalidad:</td>
                <td><?= ucfirst($acta['modalidad']) ?></td>
            </tr>
            <tr>
                <td class="label">Lugar:</td>
                <td colspan="3"><?= esc($acta['lugar'] ?? 'No especificado') ?></td>
            </tr>
            <?php if (!empty($acta['enlace_virtual'])): ?>
            <tr>
                <td class="label">Enlace:</td>
                <td colspan="3"><?= esc($acta['enlace_virtual']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Empresa:</td>
                <td><?= esc($cliente['nombre_cliente']) ?></td>
                <td class="label">NIT:</td>
                <td><?= esc($cliente['nit_cliente'] ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- 2. ASISTENTES                                 -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo">2. ASISTENTES</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width: 30%;">Nombre</th>
                    <th style="width: 20%;">Cargo</th>
                    <th style="width: 18%;">Representacion</th>
                    <th style="width: 15%;">Rol</th>
                    <th style="width: 10%;">Asistio</th>
                    <th style="width: 7%;">Firma</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asistentes as $asist): ?>
                <tr>
                    <td><?= esc($asist['nombre_completo']) ?></td>
                    <td><?= esc($asist['cargo'] ?? '-') ?></td>
                    <td><?= ucfirst($asist['representacion'] ?? '-') ?></td>
                    <td>
                        <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                            <span class="badge badge-warning"><?= ucfirst($asist['rol_comite']) ?></span>
                        <?php else: ?>
                            Miembro
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($asist['asistio']): ?>
                            <span class="badge badge-success">Si</span>
                        <?php else: ?>
                            <span class="badge badge-danger">No</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php if (!empty($asist['firma_imagen'])): ?>
                            <span class="badge badge-success">Si</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="font-size: 9pt; margin-top: 5px;">
            <strong>Total asistentes:</strong> <?= count(array_filter($asistentes, fn($a) => $a['asistio'])) ?> de <?= count($asistentes) ?> |
            <strong>Quorum:</strong>
            <?php if ($quorumAlcanzado): ?>
                <span class="badge badge-success">Alcanzado</span>
            <?php else: ?>
                <span class="badge badge-danger">No alcanzado</span>
            <?php endif; ?>
        </p>
    </div>

    <!-- ============================================== -->
    <!-- 3. ORDEN DEL DÍA                              -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo">3. ORDEN DEL DIA</div>
        <ol class="orden-dia-lista">
            <?php
            $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
            if (!empty($ordenDia)):
                foreach ($ordenDia as $punto):
            ?>
            <li><?= esc($punto['tema'] ?? '') ?></li>
            <?php
                endforeach;
            endif;
            ?>
        </ol>
    </div>

    <!-- ============================================== -->
    <!-- 4. DESARROLLO DE LA REUNIÓN                   -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo">4. DESARROLLO DE LA REUNION</div>
        <?php
        $desarrollo = is_string($acta['desarrollo']) ? json_decode($acta['desarrollo'], true) : $acta['desarrollo'];
        if (!empty($ordenDia)):
            foreach ($ordenDia as $punto):
                $numPunto = $punto['punto'] ?? '';
                $contenido = $desarrollo[$numPunto] ?? '';
        ?>
        <div class="desarrollo-punto">
            <h4><?= $numPunto ?>. <?= esc($punto['tema'] ?? '') ?></h4>
            <?php if (!empty($contenido)): ?>
                <p><?= esc($contenido) ?></p>
            <?php else: ?>
                <p style="color: #999; font-style: italic;">Sin desarrollo registrado</p>
            <?php endif; ?>
        </div>
        <?php
            endforeach;
        endif;
        ?>
    </div>

    <!-- ============================================== -->
    <!-- 5. COMPROMISOS                                -->
    <!-- ============================================== -->
    <?php if (!empty($compromisos)): ?>
    <div class="seccion">
        <div class="seccion-titulo">5. COMPROMISOS</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width: 5%; background-color: #198754;">#</th>
                    <th style="width: 50%; background-color: #198754;">Compromiso</th>
                    <th style="width: 25%; background-color: #198754;">Responsable</th>
                    <th style="width: 20%; background-color: #198754;">Fecha Limite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compromisos as $i => $comp): ?>
                <tr style="<?= $i % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                    <td style="text-align: center;"><?= $i + 1 ?></td>
                    <td><?= esc($comp['descripcion']) ?></td>
                    <td><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                    <td style="text-align: center;"><?= !empty($comp['fecha_limite']) ? date('d/m/Y', strtotime($comp['fecha_limite'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ============================================== -->
    <!-- 6. PRÓXIMA REUNIÓN                            -->
    <!-- ============================================== -->
    <?php if (!empty($acta['proxima_reunion_fecha'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">6. PROXIMA REUNION</div>
        <p style="font-size: 9pt;">
            <strong>Fecha:</strong> <?= fechaEspanol($acta['proxima_reunion_fecha']) ?>
            <?php if (!empty($acta['proxima_reunion_hora'])): ?>
                | <strong>Hora:</strong> <?= $acta['proxima_reunion_hora'] ?>
            <?php endif; ?>
            <?php if (!empty($acta['proxima_reunion_lugar'])): ?>
                | <strong>Lugar:</strong> <?= esc($acta['proxima_reunion_lugar']) ?>
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- ============================================== -->
    <!-- SECCIÓN: FIRMAS DE LOS ASISTENTES             -->
    <!-- ============================================== -->
    <div style="margin-top: 25px;">
        <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            FIRMAS DE LOS ASISTENTES
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Nombre</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Cargo / Rol</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Firma</th>
            </tr>
            <?php
            $asistentesConFirma = array_filter($asistentes, fn($a) => $a['asistio']);
            foreach ($asistentesConFirma as $idx => $asist):
            ?>
            <tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                <td style="vertical-align: middle; padding: 8px;">
                    <strong><?= esc($asist['nombre_completo']) ?></strong>
                </td>
                <td style="vertical-align: middle; padding: 8px;">
                    <?= esc($asist['cargo'] ?? '-') ?>
                    <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                        <br><small style="color: #0d6efd;"><?= ucfirst($asist['rol_comite']) ?></small>
                    <?php endif; ?>
                </td>
                <td style="text-align: center; vertical-align: bottom; padding: 8px; height: 60px;">
                    <?php if (!empty($asist['firma_imagen'])): ?>
                        <?php
                        $firmaImg = $asist['firma_imagen'];
                        if (strpos($firmaImg, 'data:image') !== 0) {
                            $firmaImg = 'data:image/png;base64,' . $firmaImg;
                        }
                        ?>
                        <img src="<?= $firmaImg ?>" style="max-height: 40px; max-width: 100px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666; font-size: 7pt;">Firma</small>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCIÓN: CONTROL DE CAMBIOS                   -->
    <!-- ============================================== -->
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
            <?php if (!empty($versiones)): ?>
                <?php foreach ($versiones as $idx => $ver): ?>
                <tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                    <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                    <td><?= esc($ver['descripcion_cambio']) ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td style="text-align: center; font-weight: bold;">1.0</td>
                    <td>Elaboracion inicial del acta</td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($acta['created_at'] ?? 'now')) ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- PIE DE DOCUMENTO                              -->
    <!-- ============================================== -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
    </div>
</body>
</html>
