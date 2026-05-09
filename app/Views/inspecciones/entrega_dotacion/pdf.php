<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        p, h1, h2, h3, h4, h5, h6, table, div { margin: 0; padding: 0; }
        *, *::before, *::after { box-sizing: border-box; }

        .seccion { margin-bottom: 8px; }
        .seccion-titulo { font-size: 11pt; font-weight: bold; color: #0d6efd; border-bottom: 1px solid #e9ecef; padding-bottom: 3px; margin-bottom: 5px; margin-top: 8px; }
        .seccion-contenido { text-align: justify; line-height: 1.2; }

        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; vertical-align: top; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 22%; background:#f8f9fa; }

        .firma-img { max-width: 200px; max-height: 80px; border-bottom: 1px solid #999; }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }
        .pendiente-text { color:#d97706; font-style:italic; font-size:9pt; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }

        .firma-section { margin-top: 18px; }
        .firma-box { border: 1px solid #999; padding: 12px; min-height: 110px; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
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
                    SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Código:</span> FT-SST-234</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Versión:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    ENTREGA DE DOTACIÓN / EPP
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DE LA ENTREGA -->
    <table class="datos-general">
        <tr>
            <td class="datos-label">CLIENTE:</td>
            <td colspan="3"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="datos-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?></td>
            <td class="datos-label">HORA:</td>
            <td><?= !empty($entrega['hora']) ? date('g:i A', strtotime($entrega['hora'])) : '-' ?></td>
        </tr>
        <?php if (!empty($entrega['lugar']) || !empty($entrega['responsable_entrega'])): ?>
        <tr>
            <td class="datos-label">LUGAR:</td>
            <td><?= esc($entrega['lugar'] ?? '-') ?></td>
            <td class="datos-label">RESPONSABLE:</td>
            <td><?= esc($entrega['responsable_entrega'] ?? '-') ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($entrega['tipo_dotacion'])): ?>
        <tr>
            <td class="datos-label">TIPO DOTACIÓN:</td>
            <td colspan="3"><?= esc($entrega['tipo_dotacion']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($consultor)): ?>
        <tr>
            <td class="datos-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- DATOS DEL OPERARIO -->
    <div class="seccion">
        <div class="seccion-titulo">OPERARIO QUE RECIBE</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">NOMBRE COMPLETO:</td>
                <td colspan="3"><strong><?= esc($asistente['nombre_completo']) ?></strong></td>
            </tr>
            <tr>
                <td class="datos-label">DOCUMENTO:</td>
                <td><?= esc($asistente['tipo_documento'] ?? '') ?> <?= esc($asistente['numero_documento'] ?? '-') ?></td>
                <td class="datos-label">CARGO:</td>
                <td><?= esc($asistente['cargo'] ?? '-') ?></td>
            </tr>
            <?php if (!empty($asistente['area_dependencia'])): ?>
            <tr>
                <td class="datos-label">ÁREA / DEPENDENCIA:</td>
                <td colspan="3"><?= esc($asistente['area_dependencia']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ELEMENTOS ENTREGADOS -->
    <div class="seccion">
        <div class="seccion-titulo">ELEMENTOS ENTREGADOS</div>
        <?php if (!empty($items)): ?>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:55%;">DESCRIPCIÓN</th>
                    <th style="width:13%;">CANTIDAD</th>
                    <th style="width:12%;">TALLA</th>
                    <th style="width:15%;">MARCA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $it): ?>
                <tr>
                    <td style="text-align:center; font-weight:bold;"><?= $i + 1 ?></td>
                    <td><?= esc($it['descripcion']) ?></td>
                    <td style="text-align:center;"><?= esc($it['cantidad']) ?></td>
                    <td style="text-align:center;"><?= esc($it['talla'] ?? '-') ?></td>
                    <td><?= esc($it['marca'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-text">Sin elementos registrados.</p>
        <?php endif; ?>
    </div>

    <!-- CONSTANCIA DE RECIBIDO -->
    <div class="seccion">
        <div class="seccion-titulo">CONSTANCIA DE RECIBIDO</div>
        <p style="font-size:9pt; text-align:justify; margin:5px 0;">
            Yo, <strong><?= esc($asistente['nombre_completo']) ?></strong>
            <?php if (!empty($asistente['numero_documento'])): ?>
                identificado(a) con <?= esc($asistente['tipo_documento']) ?> No. <?= esc($asistente['numero_documento']) ?>,
            <?php else: ?>,<?php endif; ?>
            declaro haber recibido a satisfacción los elementos de dotación / EPP relacionados en el cuadro anterior, en buen estado y conforme a lo solicitado.
            Me comprometo a usarlos adecuadamente, conservarlos, mantenerlos en buen estado y reportar de manera oportuna cualquier daño, pérdida o deterioro.
        </p>
    </div>

    <!-- FIRMA -->
    <div class="firma-section">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:50%; vertical-align:top; padding-right:10px;">
                    <div class="firma-box" style="text-align:center;">
                        <?php if (!empty($firmaBase64)): ?>
                            <img src="<?= $firmaBase64 ?>" class="firma-img">
                        <?php else: ?>
                            <span class="pendiente-text">Sin firma</span>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:center; font-size:9pt; margin-top:5px;">
                        <strong><?= esc($asistente['nombre_completo']) ?></strong>
                        <?php if (!empty($asistente['numero_documento'])): ?>
                        <br><?= esc($asistente['tipo_documento']) ?> <?= esc($asistente['numero_documento']) ?>
                        <?php endif; ?>
                        <?php if (!empty($asistente['firmado_at'])): ?>
                        <br><span style="font-size:8pt; color:#666;">Firmado: <?= date('d/m/Y H:i', strtotime($asistente['firmado_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="width:50%; vertical-align:top; padding-left:10px;">
                    <div class="firma-box" style="text-align:center;">
                        <span class="empty-text">________________________________</span>
                    </div>
                    <div style="text-align:center; font-size:9pt; margin-top:5px;">
                        <strong>RESPONSABLE DE LA ENTREGA</strong>
                        <?php if (!empty($entrega['responsable_entrega'])): ?>
                        <br><?= esc($entrega['responsable_entrega']) ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <?php if (!empty($entrega['observaciones'])): ?>
    <div class="seccion" style="margin-top:15px;">
        <div class="seccion-titulo">OBSERVACIONES</div>
        <div class="seccion-contenido"><p><?= nl2br(esc($entrega['observaciones'])) ?></p></div>
    </div>
    <?php endif; ?>

    <div class="pie-documento">
        <p>Documento generado por EnterpriseSST &middot; <?= date('d/m/Y H:i') ?></p>
    </div>
</body>
</html>
