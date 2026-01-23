<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($documento['codigo']) ?> - <?= esc($documento['nombre']) ?></title>
    <style>
        @page {
            margin: 2cm;
            @top-center {
                content: "<?= esc($documento['codigo']) ?>";
            }
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
            }
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }
        .logo {
            width: 80px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1E40AF;
        }
        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #1E40AF;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .meta-table td {
            border: 1px solid #E5E7EB;
            padding: 8px;
        }
        .meta-table .label {
            background-color: #F3F4F6;
            font-weight: bold;
            width: 25%;
        }
        h2 {
            color: #1E40AF;
            font-size: 13pt;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 5px;
            margin-top: 25px;
        }
        .section-content {
            text-align: justify;
        }
        .section-content p {
            margin-bottom: 10px;
        }
        .section-content ul, .section-content ol {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.1);
            z-index: -1;
        }
        .firmas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .firmas-table td {
            width: 33%;
            text-align: center;
            padding: 20px 10px 10px;
            vertical-align: top;
        }
        .firma-linea {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 50px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #E5E7EB;
            font-size: 9pt;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <?php if ($esBorrador ?? false): ?>
        <div class="watermark">BORRADOR</div>
    <?php endif; ?>

    <!-- Encabezado -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 20%;">
                    <!-- Logo placeholder -->
                    <div style="width: 80px; height: 60px; background: #F3F4F6; text-align: center; line-height: 60px; font-size: 10pt; color: #6B7280;">
                        LOGO
                    </div>
                </td>
                <td style="width: 60%; text-align: center;">
                    <div class="company-name"><?= esc($cliente['nombre_cliente']) ?></div>
                    <div style="font-size: 10pt;">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                </td>
                <td style="width: 20%; text-align: right; font-size: 10pt;">
                    <strong><?= esc($documento['codigo']) ?></strong><br>
                    Versión: <?= $documento['version_actual'] ?><br>
                    Página <span class="page-num"></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Título -->
    <div class="doc-title"><?= esc($documento['nombre']) ?></div>

    <!-- Metadatos -->
    <table class="meta-table">
        <tr>
            <td class="label">Código:</td>
            <td><?= esc($documento['codigo']) ?></td>
            <td class="label">Versión:</td>
            <td><?= $documento['version_actual'] ?></td>
        </tr>
        <tr>
            <td class="label">Fecha Creación:</td>
            <td><?= date('d/m/Y', strtotime($documento['created_at'])) ?></td>
            <td class="label">Última Actualización:</td>
            <td><?= date('d/m/Y', strtotime($documento['updated_at'])) ?></td>
        </tr>
        <tr>
            <td class="label">Estado:</td>
            <td colspan="3"><?= ucfirst(str_replace('_', ' ', $documento['estado'])) ?></td>
        </tr>
    </table>

    <!-- Contenido -->
    <?php if (!empty($secciones)): ?>
        <?php foreach ($secciones as $seccion): ?>
            <h2><?= $seccion['numero_seccion'] ?>. <?= esc($seccion['nombre_seccion']) ?></h2>
            <div class="section-content">
                <?= $seccion['contenido'] ?? '<em>Sin contenido</em>' ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Firmas -->
    <?php if (!empty($firmas)): ?>
        <table class="firmas-table">
            <tr>
                <?php foreach ($firmas as $firma): ?>
                    <td>
                        <div class="firma-linea">
                            <strong><?= esc($firma['firmante_nombre'] ?? 'Pendiente') ?></strong><br>
                            <span style="font-size: 10pt;"><?= esc($firma['firmante_cargo'] ?? '') ?></span><br>
                            <span style="font-size: 9pt; color: #6B7280;">
                                <?= ucfirst($firma['firmante_tipo']) ?>
                                <?php if ($firma['estado'] === 'firmado'): ?>
                                    <br>Firmado: <?= date('d/m/Y', strtotime($firma['fecha_firma'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    <?php endif; ?>

    <!-- Pie de página -->
    <div class="footer">
        <p>
            <strong>Documento controlado.</strong> Una vez impreso se considera copia no controlada.
            Verificar vigencia en el sistema documental.
        </p>
        <p>
            Generado por EnterpriseSST | <?= date('d/m/Y H:i:s') ?>
        </p>
    </div>
</body>
</html>
