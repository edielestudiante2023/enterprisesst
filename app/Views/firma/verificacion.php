<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($valido ?? false) ? 'Certificado de Verificacion - ' . esc($documento['codigo'] ?? '') : 'Verificacion Invalida' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .cert-header {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
        }
        .cert-body {
            background: white;
            border: 2px solid #065f46;
            border-top: none;
            border-radius: 0 0 12px 12px;
            padding: 30px;
        }
        .cert-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.85rem;
        }
        .firma-card {
            border-left: 4px solid #10B981;
            background: #f0fdf4;
            border-radius: 0 8px 8px 0;
            padding: 16px;
            margin-bottom: 12px;
        }
        .firma-img {
            max-height: 50px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
        }
        .invalid-card {
            background: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <?php if (!($valido ?? false)): ?>
                    <!-- Verificacion invalida -->
                    <div class="invalid-card">
                        <i class="bi bi-shield-x text-danger" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 text-danger">Verificacion No Valida</h3>
                        <p class="text-muted mb-0">
                            El codigo de verificacion proporcionado no corresponde a ningun documento firmado en nuestro sistema.
                        </p>
                        <p class="text-muted">
                            Verifique que el codigo sea correcto e intente nuevamente.
                        </p>
                    </div>
                <?php else: ?>

                    <!-- Certificado valido -->
                    <div class="cert-header text-center">
                        <div class="cert-badge mb-3">
                            <i class="bi bi-patch-check-fill"></i>
                            DOCUMENTO VERIFICADO
                        </div>
                        <h3 class="mb-1">Certificado de Firma Electronica</h3>
                        <p class="mb-2 opacity-75">Ley 527 de 1999 - Decreto 2364 de 2012</p>
                        <div class="mt-3">
                            <small class="d-block opacity-75">Codigo de Verificacion</small>
                            <code class="fs-4 text-white bg-transparent"><?= esc($codigoVerificacion) ?></code>
                        </div>
                    </div>

                    <div class="cert-body">
                        <!-- Informacion del documento -->
                        <h5 class="mb-3"><i class="bi bi-file-earmark-text me-2 text-success"></i>Documento</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <small class="text-muted">Codigo</small>
                                <p class="fw-bold mb-1"><?= esc($documento['codigo'] ?? '') ?></p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Nombre</small>
                                <p class="fw-bold mb-1"><?= esc($documento['titulo'] ?? $documento['nombre'] ?? '') ?></p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Version</small>
                                <p class="mb-1"><?= $documento['version'] ?? '1' ?></p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Empresa</small>
                                <p class="mb-1"><?= esc($cliente['nombre_empresa'] ?? '') ?></p>
                            </div>
                        </div>

                        <hr>

                        <!-- Firmantes -->
                        <h5 class="mb-3"><i class="bi bi-pen me-2 text-success"></i>Firmantes</h5>
                        <?php if (!empty($firmas)): ?>
                            <?php foreach ($firmas as $firma): ?>
                                <?php if ($firma['estado'] === 'firmado'): ?>
                                <div class="firma-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                <?= esc($firma['firmante_nombre']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?= match($firma['firmante_tipo']) {
                                                    'delegado_sst' => 'Delegado SST',
                                                    'representante_legal' => 'Representante Legal',
                                                    'elaboro' => 'Elaboro',
                                                    'reviso' => 'Reviso',
                                                    default => ucfirst($firma['firmante_tipo'])
                                                } ?>
                                                <?php if (!empty($firma['firmante_cargo'])): ?>
                                                    &middot; <?= esc($firma['firmante_cargo']) ?>
                                                <?php endif; ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                Firmado: <?= date('d/m/Y H:i:s', strtotime($firma['fecha_firma'])) ?> UTC
                                            </small>
                                        </div>
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
                                        <?php if ($ev && !empty($ev['firma_imagen'])): ?>
                                            <img src="<?= $ev['firma_imagen'] ?>" class="firma-img" alt="Firma">
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($ev): ?>
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">
                                            <i class="bi bi-globe me-1"></i>IP: <?= esc($ev['ip_address'] ?? '') ?>
                                            <?php if (!empty($ev['hash_documento'])): ?>
                                                &middot; SHA-256: <code class="small"><?= substr($ev['hash_documento'], 0, 16) ?>...</code>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No se encontraron firmas.</p>
                        <?php endif; ?>

                        <hr>

                        <!-- QR y verificacion -->
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6><i class="bi bi-shield-check me-2 text-success"></i>Validez Legal</h6>
                                <p class="small text-muted mb-2">
                                    Este documento ha sido firmado electronicamente de conformidad con la
                                    Ley 527 de 1999 y el Decreto 2364 de 2012 de la Republica de Colombia.
                                    La firma electronica tiene la misma validez juridica que la firma manuscrita.
                                </p>
                                <p class="small text-muted mb-0">
                                    <strong>Verificar autenticidad:</strong><br>
                                    <code><?= base_url("firma/verificar/{$codigoVerificacion}") ?></code>
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php if (!empty($qrImage)): ?>
                                    <img src="<?= $qrImage ?>" alt="QR Verificacion" class="img-fluid" style="max-width: 150px;">
                                    <small class="d-block text-muted mt-1">Escanear para verificar</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
