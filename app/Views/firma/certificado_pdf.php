<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
        }
        .page {
            padding: 30px 40px;
        }
        .header {
            background: #065f46;
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 10px;
            opacity: 0.85;
        }
        .header .codigo {
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            background: rgba(255,255,255,0.15);
            display: inline-block;
            padding: 4px 16px;
            border-radius: 4px;
            letter-spacing: 2px;
        }
        .badge-verificado {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 8px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #065f46;
            border-bottom: 2px solid #065f46;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            color: #6b7280;
            width: 130px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .firma-table {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }
        .firma-table th {
            background: #f3f4f6;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            text-align: left;
            border-bottom: 1px solid #d1d5db;
        }
        .firma-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .firma-table tr:last-child td {
            border-bottom: none;
        }
        .check {
            color: #10b981;
            font-weight: bold;
        }
        .firma-img {
            max-height: 35px;
            max-width: 100px;
        }
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #065f46;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .footer-text {
            display: table-cell;
            vertical-align: top;
            width: 70%;
        }
        .footer-qr {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            width: 30%;
        }
        .legal-text {
            font-size: 9px;
            color: #6b7280;
            line-height: 1.4;
        }
        .qr-img {
            width: 100px;
            height: 100px;
        }
        .hash-box {
            background: #f3f4f6;
            padding: 6px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 8px;
            word-break: break-all;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="badge-verificado">DOCUMENTO VERIFICADO</div>
            <h1>CERTIFICADO DE FIRMA ELECTRONICA</h1>
            <div class="subtitle">Ley 527 de 1999 - Decreto 2364 de 2012 - Republica de Colombia</div>
            <div class="codigo"><?= esc($codigoVerificacion ?? '') ?></div>
        </div>

        <!-- Informacion del documento -->
        <div class="section">
            <div class="section-title">Informacion del Documento</div>
            <table class="info-table">
                <tr>
                    <td class="label">Codigo</td>
                    <td><?= esc($documento['codigo'] ?? '') ?></td>
                    <td class="label">Version</td>
                    <td><?= $documento['version'] ?? '1' ?></td>
                </tr>
                <tr>
                    <td class="label">Nombre</td>
                    <td colspan="3"><?= esc($documento['titulo'] ?? $documento['nombre'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">Empresa</td>
                    <td colspan="3"><?= esc($cliente['nombre_empresa'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">NIT</td>
                    <td><?= esc($cliente['nit'] ?? '') ?></td>
                    <td class="label">Estado</td>
                    <td><strong style="color: #065f46;">FIRMADO</strong></td>
                </tr>
            </table>
        </div>

        <!-- Registro de firmas -->
        <div class="section">
            <div class="section-title">Registro de Firmas</div>
            <table class="firma-table">
                <thead>
                    <tr>
                        <th style="width: 20px;"></th>
                        <th>Firmante</th>
                        <th>Tipo</th>
                        <th>Cargo</th>
                        <th>Fecha Firma</th>
                        <th>IP</th>
                        <th>Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($firmas)): ?>
                        <?php foreach ($firmas as $firma): ?>
                            <?php if ($firma['estado'] === 'firmado'): ?>
                            <tr>
                                <td class="check">&#10003;</td>
                                <td><strong><?= esc($firma['firmante_nombre']) ?></strong></td>
                                <td>
                                    <?= match($firma['firmante_tipo']) {
                                        'delegado_sst' => 'Delegado SST',
                                        'representante_legal' => 'Rep. Legal',
                                        'elaboro' => 'Elaboro',
                                        'reviso' => 'Reviso',
                                        default => ucfirst($firma['firmante_tipo'])
                                    } ?>
                                </td>
                                <td><?= esc($firma['firmante_cargo'] ?? '') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($firma['fecha_firma'])) ?></td>
                                <td>
                                    <?php
                                    $ev = null;
                                    if (!empty($evidencias)) {
                                        foreach ($evidencias as $e) {
                                            if (($e['id_solicitud'] ?? '') == $firma['id_solicitud']) {
                                                $ev = $e;
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <?= esc($ev['ip_address'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php if ($ev && !empty($ev['firma_imagen'])): ?>
                                        <img src="<?= $ev['firma_imagen'] ?>" class="firma-img">
                                    <?php else: ?>
                                        <em>Interna</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Evidencia tecnica -->
        <div class="section">
            <div class="section-title">Evidencia Tecnica</div>
            <?php if (!empty($evidencias)): ?>
                <?php foreach ($evidencias as $ev): ?>
                <div style="margin-bottom: 8px;">
                    <strong style="font-size: 10px;">Hash SHA-256 del documento al momento de firma:</strong>
                    <div class="hash-box"><?= esc($ev['hash_documento'] ?? '') ?></div>
                </div>
                <?php break; // Solo mostrar un hash, es el mismo documento ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-text">
                    <div class="legal-text">
                        <strong>Validez Legal</strong><br>
                        Este certificado acredita que el documento referenciado ha sido firmado electronicamente
                        de conformidad con la Ley 527 de 1999 y el Decreto 2364 de 2012 de la Republica de Colombia.
                        La firma electronica aqui registrada tiene la misma validez juridica que la firma manuscrita,
                        segun lo establecido en el articulo 7 de la citada ley.
                        <br><br>
                        <strong>Verificacion</strong><br>
                        Para verificar la autenticidad de este certificado, visite:<br>
                        <?= base_url("firma/verificar/" . ($codigoVerificacion ?? '')) ?>
                        <br><br>
                        <em>Documento generado automaticamente por EnterpriseSST.</em>
                        <br>
                        <em>Fecha de emision: <?= date('d/m/Y H:i:s') ?> UTC</em>
                    </div>
                </div>
                <div class="footer-qr">
                    <?php if (!empty($qrImage)): ?>
                        <img src="<?= $qrImage ?>" class="qr-img" alt="QR Verificacion">
                        <br>
                        <small style="font-size: 8px; color: #6b7280;">Escanear para verificar</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
