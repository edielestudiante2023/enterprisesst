<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <?php
    // Usar codigo de documento pasado desde el controlador
    $codigo = $codigoDocumento ?? 'ACT-GEN';
    $version = $versionDocumento ?? '001';
    $tipoNombre = $comite['tipo_nombre'] ?? 'Reunion SST';

    // Convertir a mayusculas sin problemas de acentos
    $tipoNombreSinAcentos = str_replace(
        ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
        ['A','E','I','O','U','A','E','I','O','U','N','N'],
        $tipoNombre
    );
    $tituloDocumento = 'ACTA DE ' . strtoupper($tipoNombreSinAcentos);

    // Funcion para fecha en español
    function fechaEspanolWord($fecha) {
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
           ESTILOS ESTANDAR WORD - SG-SST
           Segun AA_WORD_ENCABEZADO.md, AA_WORD_CUERPO_DOCUMENTO.md
           ============================================ */

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

        /* ============================================
           SECCIONES DEL CONTENIDO
           ============================================ */
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

        /* ============================================
           TABLAS DE CONTENIDO
           ============================================ */
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

        /* ============================================
           TABLA INFO REUNION
           ============================================ */
        .info-reunion {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9pt;
        }

        .info-reunion td {
            padding: 3px 5px;
            border: 1px solid #999;
        }

        .info-reunion .label {
            background-color: #e9ecef;
            font-weight: bold;
            width: 15%;
        }

        /* ============================================
           ORDEN DEL DIA
           ============================================ */
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

        /* ============================================
           DESARROLLO DE PUNTOS
           ============================================ */
        .desarrollo-punto {
            margin-bottom: 6px;
            padding: 5px;
            background-color: #fafafa;
            border-left: 3px solid #0d6efd;
        }

        .desarrollo-punto h4 {
            margin: 0 0 3px 0;
            font-size: 10pt;
            color: #0d6efd;
        }

        .desarrollo-punto p {
            margin: 0;
            white-space: pre-line;
            font-size: 9pt;
            line-height: 1.0;
        }

        /* ============================================
           BADGES
           ============================================ */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
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
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- ============================================== -->
    <!-- ENCABEZADO ESTANDAR WORD                      -->
    <!-- ============================================== -->
    <table width="100%" border="1" cellpadding="0" cellspacing="0"
           style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
        <tr>
            <td width="80" rowspan="2" align="center" valign="middle"
                bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
                <?php if (!empty($cliente['logo'])): ?>
                    <img src="<?= $cliente['logo'] ?>" width="70" height="45" alt="Logo">
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
                        <td style="border-bottom:1px solid #333;"><?= esc($codigo) ?></td>
                    </tr>
                    <tr>
                        <td style="border-bottom:1px solid #333;"><b>Version:</b></td>
                        <td style="border-bottom:1px solid #333;"><?= esc($version) ?></td>
                    </tr>
                    <tr>
                        <td><b>Fecha:</b></td>
                        <td><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"
                style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                <?= esc($tituloDocumento) ?>
            </td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- 1. INFORMACION DE LA REUNION                  -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo">1. INFORMACION DE LA REUNION</div>
        <table class="info-reunion">
            <tr>
                <td class="label">Acta No:</td>
                <td><b><?= esc($acta['numero_acta']) ?></b></td>
                <td class="label">Fecha:</td>
                <td><?= fechaEspanolWord($acta['fecha_reunion']) ?></td>
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
            <tr>
                <th style="width: 30%; background-color: #0d6efd; color: white; padding: 4px 6px;">Nombre</th>
                <th style="width: 25%; background-color: #0d6efd; color: white; padding: 4px 6px;">Cargo</th>
                <th style="width: 20%; background-color: #0d6efd; color: white; padding: 4px 6px;">Representacion</th>
                <th style="width: 15%; background-color: #0d6efd; color: white; padding: 4px 6px;">Rol</th>
                <th style="width: 10%; background-color: #0d6efd; color: white; padding: 4px 6px;">Asistio</th>
            </tr>
            <?php foreach ($asistentes as $asist): ?>
            <tr>
                <td style="padding: 3px 5px;"><?= esc($asist['nombre_completo']) ?></td>
                <td style="padding: 3px 5px;"><?= esc($asist['cargo'] ?? '-') ?></td>
                <td style="padding: 3px 5px;"><?= ucfirst($asist['representacion'] ?? '-') ?></td>
                <td style="padding: 3px 5px;">
                    <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                        <span class="badge badge-warning"><?= ucfirst($asist['rol_comite']) ?></span>
                    <?php else: ?>
                        Miembro
                    <?php endif; ?>
                </td>
                <td style="text-align: center; padding: 3px 5px;">
                    <?php if ($asist['asistio']): ?>
                        <span class="badge badge-success">Si</span>
                    <?php else: ?>
                        <span class="badge badge-danger">No</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p style="font-size: 9pt; margin-top: 4px; line-height: 1.0;">
            <b>Total asistentes:</b> <?= count(array_filter($asistentes, fn($a) => $a['asistio'])) ?> de <?= count($asistentes) ?> |
            <b>Quorum:</b>
            <?php if ($quorumAlcanzado): ?>
                <span class="badge badge-success">Alcanzado</span>
            <?php else: ?>
                <span class="badge badge-danger">No alcanzado</span>
            <?php endif; ?>
        </p>
    </div>

    <!-- ============================================== -->
    <!-- 3. ORDEN DEL DIA                              -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo">3. ORDEN DEL DIA</div>
        <ol style="margin: 2px 0 2px 15px; padding-left: 15px; line-height: 1.0;">
            <?php
            $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
            if (!empty($ordenDia)):
                foreach ($ordenDia as $punto):
            ?>
            <li style="margin-bottom: 1px; line-height: 1.0;"><?= esc($punto['tema'] ?? '') ?></li>
            <?php
                endforeach;
            endif;
            ?>
        </ol>
    </div>

    <!-- ============================================== -->
    <!-- 4. DESARROLLO DE LA REUNION                   -->
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
            <tr>
                <th style="width: 5%; background-color: #198754; color: white; padding: 4px 6px;">#</th>
                <th style="width: 50%; background-color: #198754; color: white; padding: 4px 6px;">Compromiso</th>
                <th style="width: 25%; background-color: #198754; color: white; padding: 4px 6px;">Responsable</th>
                <th style="width: 20%; background-color: #198754; color: white; padding: 4px 6px;">Fecha Limite</th>
            </tr>
            <?php foreach ($compromisos as $i => $comp): ?>
            <tr>
                <td style="text-align: center; padding: 3px 5px;"><?= $i + 1 ?></td>
                <td style="padding: 3px 5px;"><?= esc($comp['descripcion']) ?></td>
                <td style="padding: 3px 5px;"><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                <td style="text-align: center; padding: 3px 5px;"><?= !empty($comp['fecha_limite']) ? date('d/m/Y', strtotime($comp['fecha_limite'])) : '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ============================================== -->
    <!-- 6. PROXIMA REUNION                            -->
    <!-- ============================================== -->
    <?php if (!empty($acta['proxima_reunion_fecha'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">6. PROXIMA REUNION</div>
        <p style="font-size: 9pt; line-height: 1.0;">
            <b>Fecha:</b> <?= fechaEspanolWord($acta['proxima_reunion_fecha']) ?>
            <?php if (!empty($acta['proxima_reunion_hora'])): ?>
                | <b>Hora:</b> <?= $acta['proxima_reunion_hora'] ?>
            <?php endif; ?>
            <?php if (!empty($acta['proxima_reunion_lugar'])): ?>
                | <b>Lugar:</b> <?= esc($acta['proxima_reunion_lugar']) ?>
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- ============================================== -->
    <!-- SECCION: FIRMAS DE LOS ASISTENTES             -->
    <!-- ============================================== -->
    <div class="seccion" style="margin-top: 20px;">
        <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
            FIRMAS DE LOS ASISTENTES
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333; padding: 4px 6px;">Nombre</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333; padding: 4px 6px;">Cargo / Rol</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333; padding: 4px 6px;">Firma</th>
            </tr>
            <?php
            $asistentesConFirma = array_filter($asistentes, fn($a) => $a['asistio']);
            foreach ($asistentesConFirma as $asist):
            ?>
            <tr>
                <td style="vertical-align: middle; padding: 5px;">
                    <b><?= esc($asist['nombre_completo']) ?></b>
                </td>
                <td style="vertical-align: middle; padding: 5px;">
                    <?= esc($asist['cargo'] ?? '-') ?>
                    <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                        <br><small style="color: #0d6efd;"><?= ucfirst($asist['rol_comite']) ?></small>
                    <?php endif; ?>
                </td>
                <td style="text-align: center; vertical-align: bottom; padding: 5px; height: 50px;">
                    <!-- Espacio para firma física -->
                    <div style="border-top: 1px solid #333; width: 80%; margin: 30px auto 0; padding-top: 2px;">
                        <small style="color: #666; font-size: 7pt;">Firma</small>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION: CONTROL DE CAMBIOS                   -->
    <!-- ============================================== -->
    <div class="seccion" style="margin-top: 20px;">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            CONTROL DE CAMBIOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 80px; background-color: #e9ecef; color: #333; padding: 4px 6px;">Version</th>
                <th style="background-color: #e9ecef; color: #333; padding: 4px 6px;">Descripcion del Cambio</th>
                <th style="width: 90px; background-color: #e9ecef; color: #333; padding: 4px 6px;">Fecha</th>
            </tr>
            <?php if (!empty($versiones)): ?>
                <?php foreach ($versiones as $ver): ?>
                <tr>
                    <td style="text-align: center; font-weight: bold; padding: 3px 5px;"><?= esc($ver['version_texto']) ?></td>
                    <td style="padding: 3px 5px;"><?= esc($ver['descripcion_cambio']) ?></td>
                    <td style="text-align: center; padding: 3px 5px;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td style="text-align: center; font-weight: bold; padding: 3px 5px;">1.0</td>
                    <td style="padding: 3px 5px;">Elaboracion inicial del acta</td>
                    <td style="text-align: center; padding: 3px 5px;"><?= date('d/m/Y', strtotime($acta['created_at'] ?? 'now')) ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- PIE DE DOCUMENTO                              -->
    <!-- ============================================== -->
    <div class="pie-documento">
        <p style="margin: 2px 0;"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
        <p style="margin: 2px 0;">Documento generado el <?= date('d/m/Y') ?></p>
    </div>
</body>
</html>
