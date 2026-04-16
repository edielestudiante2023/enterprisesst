<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil del Cargo - <?= esc($cargo['nombre_cargo'] ?? '') ?></title>
<style>
    @page { margin: 100px 70px 80px 90px; }
    body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
    p, h1, h2, h3, h4, h5, h6, table, div { margin: 0; padding: 0; }
    *, *::before, *::after { box-sizing: border-box; }
    br { line-height: 0.5; }

    .seccion { margin-bottom: 8px; }
    .seccion-titulo {
        font-size: 11pt; font-weight: bold; color: #0d6efd;
        border-bottom: 1px solid #e9ecef; padding-bottom: 3px;
        margin-bottom: 5px; margin-top: 8px;
    }
    .seccion-contenido { text-align: justify; line-height: 1.2; }
    .seccion-contenido p { margin: 3px 0; }
    .seccion-contenido ul, .seccion-contenido ol { margin: 3px 0 3px 15px; padding-left: 15px; }
    .seccion-contenido li { margin-bottom: 2px; }

    table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
    table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; vertical-align: top; }
    table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

    table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
    table.datos-general td { border: 1px solid #999; padding: 5px 8px; vertical-align: top; }
    .datos-label { font-weight: bold; width: 22%; background-color: #f8f9fa; }

    .firma-aprobador { border: 1px solid #999; padding: 8px; text-align: center; margin-top: 8px; width: 60%; }

    .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
</style>
</head>
<body>

    <!-- ENCABEZADO ESTANDAR -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;" cellpadding="0" cellspacing="0">
        <tr>
            <td rowspan="2" style="width:100px; border:1px solid #333; padding:8px; text-align:center; vertical-align:middle; background:#fff;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
                <?php else: ?>
                    <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> <?= esc($codigo) ?></td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> <?= esc($version) ?></td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($vigencia)) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    PERFIL DEL CARGO
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. IDENTIFICACION DEL CARGO -->
    <div class="seccion">
        <div class="seccion-titulo">1. IDENTIFICACION DEL CARGO</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">NOMBRE DEL CARGO</td>
                <td><?= esc($cargo['nombre_cargo'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">REPORTA A</td>
                <td><?= esc($perfil['reporta_a'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">COLABORADORES A CARGO</td>
                <td><?= esc($perfil['colaboradores_a_cargo'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">CLIENTE</td>
                <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            </tr>
        </table>
    </div>

    <!-- 2. OBJETIVO DEL CARGO -->
    <div class="seccion">
        <div class="seccion-titulo">2. OBJETIVO DEL CARGO</div>
        <div class="seccion-contenido">
            <p><?= nl2br(esc($perfil['objetivo_cargo'] ?? '')) ?></p>
        </div>
    </div>

    <!-- 3. REQUISITOS BASICOS -->
    <div class="seccion">
        <div class="seccion-titulo">3. REQUISITOS BASICOS DEL CARGO</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">EDAD MINIMA</td>
                <td><?= esc($perfil['edad_min'] ?? '-') ?></td>
                <td class="datos-label">ESTADO CIVIL</td>
                <td><?= esc(ucfirst($perfil['estado_civil'] ?? '-')) ?></td>
                <td class="datos-label">GENERO</td>
                <td><?= esc(ucfirst($perfil['genero'] ?? '-')) ?></td>
            </tr>
            <?php if (!empty($perfil['validacion_educacion_experiencia'])): ?>
            <tr>
                <td class="datos-label">VALIDACION EDUCACION / EXPERIENCIA</td>
                <td colspan="5"><?= nl2br(esc($perfil['validacion_educacion_experiencia'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- 4. FUNCIONES ESPECIFICAS -->
    <div class="seccion">
        <div class="seccion-titulo">4. FUNCIONES Y RESPONSABILIDADES ESPECIFICAS DEL CARGO</div>
        <div class="seccion-contenido">
            <?php $funcEsp = (array)($perfil['funciones_especificas'] ?? []); ?>
            <?php if (!empty($funcEsp)): ?>
                <ol>
                    <?php foreach ($funcEsp as $f): ?>
                        <li><?= esc($f) ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p><em>Sin funciones especificas registradas.</em></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. COMPETENCIAS -->
    <div class="seccion">
        <div class="seccion-titulo">5. COMPETENCIAS REQUERIDAS PARA EL CARGO</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:55%;">Competencia</th>
                    <th style="width:25%;">Familia</th>
                    <th style="width:20%;">Nivel Requerido</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($competencias)): foreach ($competencias as $c): ?>
                    <tr>
                        <td><?= esc($c['nombre']) ?></td>
                        <td><?= esc($c['familia'] ?? '-') ?></td>
                        <td style="text-align:center;"><?= esc($c['nivel_requerido']) ?> / 5</td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center; color:#666;"><em>Sin competencias asignadas</em></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 6. FUNCIONES SST -->
    <div class="seccion">
        <div class="seccion-titulo">6. FUNCIONES EN SEGURIDAD Y SALUD EN EL TRABAJO</div>
        <div class="seccion-contenido">
            <?php if (!empty($funcionesSST)): ?>
                <ol>
                    <?php foreach ($funcionesSST as $f): ?>
                        <li><?= esc($f['texto']) ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p><em>Sin funciones transversales SST.</em></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 7. FUNCIONES TALENTO HUMANO -->
    <div class="seccion">
        <div class="seccion-titulo">7. FUNCIONES DE TALENTO HUMANO</div>
        <div class="seccion-contenido">
            <?php if (!empty($funcionesTH)): ?>
                <ol>
                    <?php foreach ($funcionesTH as $f): ?>
                        <li><?= esc($f['texto']) ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p><em>Sin funciones transversales de Talento Humano.</em></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 8. INDICADORES -->
    <div class="seccion">
        <div class="seccion-titulo">8. INDICADORES QUE APLICAN AL CARGO</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:22%;">Objetivo de Proceso</th>
                    <th style="width:18%;">Indicador</th>
                    <th style="width:22%;">Formula</th>
                    <th style="width:10%;">Periodicidad</th>
                    <th style="width:12%;">Meta</th>
                    <th style="width:8%;">Ponderacion</th>
                    <th style="width:8%;">Obj. Calidad</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($indicadores)): foreach ($indicadores as $i): ?>
                    <tr>
                        <td><?= esc($i['objetivo_proceso'] ?? '-') ?></td>
                        <td><?= esc($i['nombre_indicador'] ?? '') ?></td>
                        <td><?= esc($i['formula'] ?? '-') ?></td>
                        <td style="text-align:center;"><?= esc(ucfirst($i['periodicidad'] ?? '-')) ?></td>
                        <td style="text-align:center;"><?= esc($i['meta'] ?? '-') ?></td>
                        <td style="text-align:center;"><?= esc($i['ponderacion'] ?? '-') ?></td>
                        <td><?= esc($i['objetivo_calidad_impacta'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#666;"><em>Sin indicadores definidos</em></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 9. APROBACION -->
    <div class="seccion">
        <div class="seccion-titulo">9. APROBACION DEL PERFIL</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">NOMBRE</td>
                <td><?= esc($perfil['aprobador_nombre'] ?? '-') ?></td>
                <td class="datos-label">CARGO</td>
                <td><?= esc($perfil['aprobador_cargo'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="datos-label">CEDULA</td>
                <td><?= esc($perfil['aprobador_cedula'] ?? '-') ?></td>
                <td class="datos-label">FECHA DE APROBACION</td>
                <td><?= !empty($perfil['fecha_aprobacion']) ? date('d/m/Y', strtotime($perfil['fecha_aprobacion'])) : '-' ?></td>
            </tr>
        </table>
        <?php if (!empty($perfil['firma_aprobador_base64'])): ?>
            <div class="firma-aprobador">
                <img src="data:image/png;base64,<?= esc($perfil['firma_aprobador_base64']) ?>"
                     style="max-width:250px; max-height:80px;">
                <div style="font-size:8pt; color:#666; margin-top:4px; border-top:1px solid #333; padding-top:3px;">
                    Firma del aprobador
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- PIE DE DOCUMENTO -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente'] ?? '') ?><?= !empty($cliente['nit_cliente']) ? ' - NIT: ' . esc($cliente['nit_cliente']) : '' ?></p>
    </div>

</body>
</html>
