# PLAN: Replicar Firma Digital de Contratos + Generacion IA de Clausulas

## Para otra IA: Guia completa para implementar en el aplicativo gemelo

**Stack:** CodeIgniter 4 + MySQL + SendGrid + Dompdf + OpenAI (gpt-4o-mini)
**Proyecto origen:** EnterpriseSST (dashboard.cycloidtalent.com)

---

# PARTE 1: FIRMA DIGITAL DE CONTRATOS

## Flujo Completo
```
Consultor genera contrato PDF
  → Click "Enviar a Firmar Digitalmente"
  → POST /contracts/enviar-firma
  → Genera token 64-char hex + expiracion 7 dias
  → Envia email via SendGrid con link publico
  → Cliente abre /contrato/firmar/{token}
  → Ve datos del contrato + canvas para dibujar firma
  → Click "Aprobar y Firmar"
  → POST /contrato/procesar-firma
  → Guarda firma como PNG en uploads/firmas/
  → Actualiza BD: estado_firma='firmado', invalida token
  → Modal de exito
```

## Archivos a Copiar (7 archivos)

### 1. Rutas — `app/Config/Routes.php`

Agregar estas rutas:

```php
// Firma digital de contratos (autenticado)
$routes->post('/contracts/enviar-firma', 'ContractController::enviarFirma');
$routes->get('/contracts/estado-firma/(:num)', 'ContractController::estadoFirma/$1');

// Firma digital de contratos (publico - SIN autenticacion)
$routes->get('/contrato/firmar/(:segment)', 'ContractController::paginaFirmaContrato/$1');
$routes->post('/contrato/procesar-firma', 'ContractController::procesarFirmaContrato');
```

### 2. Columnas BD — ALTER TABLE

```sql
ALTER TABLE tbl_contratos ADD COLUMN token_firma VARCHAR(64) NULL;
ALTER TABLE tbl_contratos ADD COLUMN token_firma_expiracion DATETIME NULL;
ALTER TABLE tbl_contratos ADD COLUMN estado_firma ENUM('sin_enviar','pendiente_firma','firmado') DEFAULT 'sin_enviar';
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_nombre VARCHAR(255) NULL;
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_cedula VARCHAR(20) NULL;
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_imagen VARCHAR(500) NULL COMMENT 'Ruta al PNG de la firma';
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_ip VARCHAR(45) NULL;
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_fecha DATETIME NULL;
```

### 3. Modelo — `app/Models/ContractModel.php`

Agregar estos campos a `$allowedFields`:

```php
protected $allowedFields = [
    // ... campos existentes ...
    'token_firma',
    'token_firma_expiracion',
    'estado_firma',
    'firma_cliente_nombre',
    'firma_cliente_cedula',
    'firma_cliente_imagen',
    'firma_cliente_ip',
    'firma_cliente_fecha',
];
```

### 4. Controller — `app/Controllers/ContractController.php`

Agregar estos 4 metodos al final del controller:

```php
// =========================================================================
// FIRMA DIGITAL DE CONTRATOS
// =========================================================================

/**
 * Envia solicitud de firma digital al representante legal del cliente
 * POST /contracts/enviar-firma  {id_contrato: 9}
 */
public function enviarFirma()
{
    $idContrato = $this->request->getPost('id_contrato');

    $contract = $this->contractLibrary->getContractWithClient($idContrato);
    if (!$contract) {
        return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
    }

    // Validar que tenga PDF generado
    if (empty($contract['contrato_generado'])) {
        return redirect()->to('/contracts/view/' . $idContrato)
            ->with('error', 'Debe generar el PDF del contrato antes de enviarlo a firmar');
    }

    // Validar que no este ya firmado
    if (($contract['estado_firma'] ?? '') === 'firmado') {
        return redirect()->to('/contracts/view/' . $idContrato)
            ->with('error', 'Este contrato ya fue firmado');
    }

    // Validar email del cliente
    $emailCliente = $contract['email_cliente'] ?? '';
    if (empty($emailCliente)) {
        return redirect()->to('/contracts/view/' . $idContrato)
            ->with('error', 'El contrato no tiene email del representante legal del cliente');
    }

    // Generar token criptografico (64 chars hex)
    $token = bin2hex(random_bytes(32));
    $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Guardar token en BD
    $this->contractModel->update($idContrato, [
        'token_firma' => $token,
        'token_firma_expiracion' => $expiracion,
        'estado_firma' => 'pendiente_firma'
    ]);

    // URL publica de firma
    $urlFirma = base_url("contrato/firmar/{$token}");
    $nombreFirmante = $contract['nombre_rep_legal_cliente'] ?? 'Representante Legal';

    // Enviar email
    $enviado = $this->enviarEmailFirmaContrato(
        $emailCliente,
        $nombreFirmante,
        $contract,
        $urlFirma,
        'Se requiere su firma digital para el contrato de prestacion de servicios SST.',
        false
    );

    if (!$enviado) {
        // Revertir si falla el envio
        $this->contractModel->update($idContrato, [
            'token_firma' => null,
            'token_firma_expiracion' => null,
            'estado_firma' => 'sin_enviar'
        ]);
        return redirect()->to('/contracts/view/' . $idContrato)
            ->with('error', 'Error al enviar el correo. Verifique la configuracion de SendGrid.');
    }

    // Copia informativa al responsable SST si tiene email diferente
    $emailResponsable = $contract['email_responsable_sgsst'] ?? '';
    if (!empty($emailResponsable) && $emailResponsable !== $emailCliente) {
        $this->enviarEmailFirmaContrato(
            $emailResponsable,
            $contract['nombre_responsable_sgsst'] ?? 'Responsable SST',
            $contract,
            $urlFirma,
            'El Representante Legal debe firmar este contrato. Se le envia copia informativa.',
            true
        );
    }

    return redirect()->to('/contracts/view/' . $idContrato)
        ->with('success', 'Solicitud de firma enviada correctamente a ' . $emailCliente);
}

/**
 * Pagina publica de firma del contrato (SIN AUTH - acceso por token)
 * GET /contrato/firmar/{token}
 */
public function paginaFirmaContrato($token)
{
    $db = \Config\Database::connect();

    $contrato = $db->table('tbl_contratos')
        ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
        ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
        ->where('tbl_contratos.token_firma', $token)
        ->get()->getRowArray();

    if (!$contrato) {
        return view('contracts/firma_error_contrato', [
            'mensaje' => 'El enlace de firma no es valido o ya fue utilizado.'
        ]);
    }

    if (($contrato['estado_firma'] ?? '') === 'firmado') {
        return view('contracts/firma_error_contrato', [
            'mensaje' => 'Este contrato ya fue firmado anteriormente.'
        ]);
    }

    if (($contrato['estado_firma'] ?? '') !== 'pendiente_firma') {
        return view('contracts/firma_error_contrato', [
            'mensaje' => 'Este contrato no esta disponible para firma.'
        ]);
    }

    // Verificar expiracion
    if (!empty($contrato['token_firma_expiracion']) && strtotime($contrato['token_firma_expiracion']) < time()) {
        return view('contracts/firma_error_contrato', [
            'mensaje' => 'El enlace de firma ha expirado. Solicite un nuevo enlace.'
        ]);
    }

    return view('contracts/contrato_firma', [
        'contrato' => $contrato,
        'token' => $token
    ]);
}

/**
 * Procesar firma digital del contrato (POST publico, SIN AUTH)
 * POST /contrato/procesar-firma  {token, firma_nombre, firma_cedula, firma_imagen}
 */
public function procesarFirmaContrato()
{
    $token = $this->request->getPost('token');
    $firmaNombre = $this->request->getPost('firma_nombre');
    $firmaCedula = $this->request->getPost('firma_cedula');
    $firmaImagen = $this->request->getPost('firma_imagen'); // data:image/png;base64,...

    $db = \Config\Database::connect();

    // Validar token
    $contrato = $db->table('tbl_contratos')
        ->where('token_firma', $token)
        ->where('estado_firma', 'pendiente_firma')
        ->get()->getRowArray();

    if (!$contrato) {
        return $this->response->setJSON(['success' => false, 'message' => 'Token no valido']);
    }

    // Verificar expiracion
    if (!empty($contrato['token_firma_expiracion']) && strtotime($contrato['token_firma_expiracion']) < time()) {
        return $this->response->setJSON(['success' => false, 'message' => 'El enlace ha expirado']);
    }

    // Guardar imagen de firma como PNG
    $rutaFirma = null;
    if ($firmaImagen) {
        $firmaData = explode(',', $firmaImagen);
        $firmaDecoded = base64_decode(end($firmaData));
        $nombreArchivo = 'firma_contrato_' . $contrato['id_contrato'] . '_' . time() . '.png';
        $rutaFirma = 'uploads/firmas/' . $nombreArchivo;

        if (!is_dir(FCPATH . 'uploads/firmas')) {
            mkdir(FCPATH . 'uploads/firmas', 0755, true);
        }

        file_put_contents(FCPATH . $rutaFirma, $firmaDecoded);
    }

    // Actualizar contrato: marcar como firmado e invalidar token
    $db->table('tbl_contratos')
        ->where('id_contrato', $contrato['id_contrato'])
        ->update([
            'estado_firma' => 'firmado',
            'firma_cliente_nombre' => $firmaNombre,
            'firma_cliente_cedula' => $firmaCedula,
            'firma_cliente_imagen' => $rutaFirma,
            'firma_cliente_ip' => $this->request->getIPAddress(),
            'firma_cliente_fecha' => date('Y-m-d H:i:s'),
            'token_firma' => null,           // Invalida el token
            'token_firma_expiracion' => null
        ]);

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Contrato firmado correctamente'
    ]);
}

/**
 * Consultar estado de firma (GET autenticado, retorna JSON)
 */
public function estadoFirma($idContrato)
{
    $contract = $this->contractModel->find($idContrato);

    if (!$contract) {
        return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado']);
    }

    $data = [
        'success' => true,
        'estado_firma' => $contract['estado_firma'] ?? 'sin_enviar',
    ];

    if (($contract['estado_firma'] ?? '') === 'firmado') {
        $data['firma'] = [
            'nombre' => $contract['firma_cliente_nombre'],
            'cedula' => $contract['firma_cliente_cedula'],
            'fecha' => $contract['firma_cliente_fecha'],
            'ip' => $contract['firma_cliente_ip'],
        ];
    }

    return $this->response->setJSON($data);
}

/**
 * Envia email de firma via SendGrid API (cURL directo)
 */
private function enviarEmailFirmaContrato($email, $nombreFirmante, $contrato, $urlFirma, $mensaje, $esCopia = false)
{
    $apiKey = env('SENDGRID_API_KEY');
    if (empty($apiKey)) {
        log_message('error', 'SENDGRID_API_KEY no configurada');
        return false;
    }

    // Renderizar template de email
    $htmlEmail = view('contracts/email_contrato_firma', [
        'nombreFirmante' => $nombreFirmante,
        'contrato' => $contrato,
        'urlFirma' => $urlFirma,
        'mensaje' => $mensaje,
        'esCopia' => $esCopia
    ]);

    $subject = $esCopia
        ? "[Copia] Solicitud de Firma: Contrato SST - {$contrato['nombre_cliente']}"
        : "Solicitud de Firma: Contrato SST - {$contrato['nombre_cliente']}";

    $fromEmail = env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com');
    $fromName = env('SENDGRID_FROM_NAME', 'Enterprise SST');

    $data = [
        'personalizations' => [[
            'to' => [['email' => $email, 'name' => $nombreFirmante]],
            'subject' => $subject
        ]],
        'from' => ['email' => $fromEmail, 'name' => $fromName],
        'content' => [['type' => 'text/html', 'value' => $htmlEmail]]
    ];

    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        log_message('error', "SendGrid Error - HTTP {$httpCode}: {$response}");
    }

    return $httpCode >= 200 && $httpCode < 300;
}
```

### 5. Vista Email — `app/Views/contracts/email_contrato_firma.php`

```php
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 22px;">Contrato de Prestacion de Servicios SST</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0;">Solicitud de Firma Digital</p>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">
            <p style="color: #333; font-size: 16px;">
                Estimado(a) <strong><?= esc($nombreFirmante) ?></strong>,
            </p>
            <p style="color: #555;"><?= esc($mensaje) ?></p>

            <!-- Resumen del contrato -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h3 style="color: #667eea; margin-top: 0; font-size: 16px;">Resumen del Contrato</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr><td style="padding: 8px 0; color: #666; font-weight: bold; width: 40%;">Numero:</td>
                        <td style="padding: 8px 0; color: #333;"><?= esc($contrato['numero_contrato']) ?></td></tr>
                    <tr><td style="padding: 8px 0; color: #666; font-weight: bold;">Contratante:</td>
                        <td style="padding: 8px 0; color: #333;"><?= esc($contrato['nombre_cliente']) ?></td></tr>
                    <tr><td style="padding: 8px 0; color: #666; font-weight: bold;">Contratista:</td>
                        <td style="padding: 8px 0; color: #333;">CYCLOID TALENT S.A.S.</td></tr>
                    <tr><td style="padding: 8px 0; color: #666; font-weight: bold;">Vigencia:</td>
                        <td style="padding: 8px 0; color: #333;">
                            <?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?>
                        </td></tr>
                    <tr><td style="padding: 8px 0; color: #666; font-weight: bold;">Valor:</td>
                        <td style="padding: 8px 0; color: #333;">$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> COP</td></tr>
                </table>
            </div>

            <!-- Boton CTA -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?= esc($urlFirma) ?>"
                   style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold;">
                    Revisar y Firmar Contrato
                </a>
            </div>

            <div style="background: #fff3cd; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <p style="color: #856404; margin: 0; font-size: 13px;">
                    <strong>Importante:</strong> Este enlace es personal e intransferible. Tiene validez de 7 dias.
                </p>
            </div>

            <?php if (!empty($esCopia)): ?>
            <div style="background: #d1ecf1; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <p style="color: #0c5460; margin: 0; font-size: 13px;">
                    <strong>Nota:</strong> Este correo es una copia informativa. La firma debe ser realizada por el Representante Legal.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;">
            <p style="color: #999; font-size: 12px; margin: 0;">
                Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo<br>
                Mensaje automatico - <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>
</body>
</html>
```

### 6. Vista Firma Publica — `app/Views/contracts/contrato_firma.php`

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma de Contrato SST - <?= esc($contrato['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .firma-container { max-width: 900px; margin: 0 auto; }
        .card-contrato { border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .header-contrato { background: linear-gradient(135deg, #1a5f7a 0%, #2c3e50 100%); color: white; border-radius: 12px 12px 0 0; padding: 25px; }
        .firma-canvas { border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; cursor: crosshair; }
        .firma-canvas:hover { border-color: #1a5f7a; }
        .btn-firmar { background: linear-gradient(135deg, #28a745 0%, #218838 100%); border: none; padding: 12px 40px; font-size: 1.1rem; }
        .info-legal { font-size: 0.75rem; color: #666; }
        .parte-card { border-left: 4px solid #667eea; background: #f8f9fa; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 15px; }
        .parte-card.contratista { border-left-color: #764ba2; }
        .detalle-label { font-size: 0.8rem; color: #888; margin-bottom: 2px; }
        .detalle-valor { font-weight: 600; color: #333; }
    </style>
</head>
<body class="py-4">
    <div class="container firma-container">
        <div class="text-center text-white mb-4">
            <h2><i class="bi bi-pen me-2"></i>Firma de Contrato</h2>
            <p class="opacity-75"><?= esc($contrato['nombre_cliente']) ?></p>
        </div>

        <div class="card card-contrato">
            <div class="header-contrato">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">Contrato <?= esc($contrato['numero_contrato']) ?></h4>
                        <p class="mb-0 opacity-75">Contrato de Prestacion de Servicios SST</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-warning text-dark fs-6">Pendiente de Firma</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Partes del contrato -->
                <h5 class="mb-3"><i class="bi bi-people me-2"></i>Partes del Contrato</h5>

                <div class="parte-card">
                    <div class="detalle-label">EL CONTRATANTE</div>
                    <div class="detalle-valor"><?= esc($contrato['nombre_cliente']) ?></div>
                    <div class="text-muted small">NIT: <?= esc($contrato['nit_cliente']) ?></div>
                    <div class="text-muted small">Rep. Legal: <?= esc($contrato['nombre_rep_legal_cliente']) ?> - C.C. <?= esc($contrato['cedula_rep_legal_cliente']) ?></div>
                </div>

                <div class="parte-card contratista">
                    <div class="detalle-label">EL CONTRATISTA</div>
                    <div class="detalle-valor">CYCLOID TALENT S.A.S.</div>
                    <div class="text-muted small">Rep. Legal: <?= esc($contrato['nombre_rep_legal_contratista']) ?></div>
                </div>

                <hr>

                <!-- Detalles -->
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Detalles del Contrato</h5>
                <div class="row mb-3">
                    <div class="col-md-3"><div class="detalle-label">Inicio</div><div class="detalle-valor"><?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?></div></div>
                    <div class="col-md-3"><div class="detalle-label">Fin</div><div class="detalle-valor"><?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?></div></div>
                    <div class="col-md-3"><div class="detalle-label">Valor</div><div class="detalle-valor">$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?></div></div>
                    <div class="col-md-3"><div class="detalle-label">Visitas</div><div class="detalle-valor"><?= esc($contrato['frecuencia_visitas'] ?? 'N/A') ?></div></div>
                </div>

                <hr>

                <!-- Seccion de firma -->
                <h5 class="mb-3"><i class="bi bi-pen me-2"></i>Firma del Representante Legal</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="firmaNombre" value="<?= esc($contrato['nombre_rep_legal_cliente'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Numero de Cedula *</label>
                            <input type="text" class="form-control" id="firmaCedula" value="<?= esc($contrato['cedula_rep_legal_cliente'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Firma Digital *</label>
                            <ul class="nav nav-tabs nav-fill mb-2" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panelDibujar"><i class="bi bi-pencil me-1"></i>Dibujar</button></li>
                                <li class="nav-item"><button class="nav-link" id="tab-subir" data-bs-toggle="tab" data-bs-target="#panelSubir"><i class="bi bi-upload me-1"></i>Subir imagen</button></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="panelDibujar">
                                    <canvas id="canvasFirma" class="firma-canvas w-100" height="150"></canvas>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnLimpiarFirma"><i class="bi bi-eraser me-1"></i>Limpiar</button>
                                </div>
                                <div class="tab-pane fade" id="panelSubir">
                                    <div class="border rounded p-3 text-center" style="background: #fafafa; min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <div id="previewSubida" style="display:none;" class="mb-2"><img id="imgPreview" src="" style="max-height: 120px;"></div>
                                        <div id="placeholderSubida"><i class="bi bi-image text-muted" style="font-size: 2rem;"></i><p class="text-muted small mb-2">PNG o JPG (max 2MB)</p></div>
                                        <input type="file" id="inputFirmaArchivo" accept="image/png,image/jpeg" class="form-control form-control-sm" style="max-width: 300px;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnLimpiarSubida" style="display:none;"><i class="bi bi-x-circle me-1"></i>Quitar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="text-muted mb-3">Al firmar, usted declara:</h6>
                            <ul class="info-legal">
                                <li class="mb-2">He revisado el contrato N° <?= esc($contrato['numero_contrato']) ?>.</li>
                                <li class="mb-2">Acepto los terminos por $<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> COP.</li>
                                <li class="mb-2">Autorizo la ejecucion del <?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?>.</li>
                                <li>Firma valida segun Ley 527 de 1999.</li>
                            </ul>
                            <div class="alert alert-info mt-3 mb-0"><small><i class="bi bi-shield-check me-1"></i>Se registra fecha, hora e IP.</small></div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="text-center">
                    <button type="button" class="btn btn-firmar btn-lg text-white" id="btnFirmarContrato">
                        <i class="bi bi-pen me-2"></i>Aprobar y Firmar Contrato
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal exito -->
    <div class="modal fade" id="modalExito" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="text-success mb-3"><i class="bi bi-check-circle" style="font-size: 4rem;"></i></div>
                    <h4 class="text-success">Contrato Firmado Exitosamente</h4>
                    <p class="text-muted">Puede cerrar esta ventana.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // === CANVAS DE FIRMA ===
        const canvas = document.getElementById('canvasFirma');
        const ctx = canvas.getContext('2d');
        let dibujando = false, tieneContenidoCanvas = false;

        canvas.width = canvas.offsetWidth;
        canvas.height = 150;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            return { x: (e.clientX || e.touches[0].clientX) - rect.left, y: (e.clientY || e.touches[0].clientY) - rect.top };
        }
        function iniciar(e) { dibujando = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
        function dibujar(e) { if (!dibujando) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); tieneContenidoCanvas = true; e.preventDefault(); }
        function parar() { dibujando = false; }

        canvas.addEventListener('mousedown', iniciar); canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', parar); canvas.addEventListener('mouseleave', parar);
        canvas.addEventListener('touchstart', iniciar); canvas.addEventListener('touchmove', dibujar);
        canvas.addEventListener('touchend', parar);

        document.getElementById('btnLimpiarFirma').addEventListener('click', () => { ctx.clearRect(0, 0, canvas.width, canvas.height); tieneContenidoCanvas = false; });

        // === SUBIR IMAGEN ===
        let imagenSubidaBase64 = null;
        document.getElementById('inputFirmaArchivo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!file.type.match('image/(png|jpeg|jpg)')) { alert('Solo PNG o JPG'); this.value = ''; return; }
            if (file.size > 2 * 1024 * 1024) { alert('Max 2MB'); this.value = ''; return; }
            const reader = new FileReader();
            reader.onload = ev => {
                imagenSubidaBase64 = ev.target.result;
                document.getElementById('imgPreview').src = imagenSubidaBase64;
                document.getElementById('previewSubida').style.display = 'block';
                document.getElementById('placeholderSubida').style.display = 'none';
                document.getElementById('btnLimpiarSubida').style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        });
        document.getElementById('btnLimpiarSubida').addEventListener('click', () => {
            imagenSubidaBase64 = null;
            document.getElementById('inputFirmaArchivo').value = '';
            document.getElementById('previewSubida').style.display = 'none';
            document.getElementById('placeholderSubida').style.display = 'block';
            document.getElementById('btnLimpiarSubida').style.display = 'none';
        });

        function obtenerFirma() {
            if (document.getElementById('tab-subir').classList.contains('active')) return imagenSubidaBase64;
            return tieneContenidoCanvas ? canvas.toDataURL('image/png') : null;
        }

        // === FIRMAR ===
        document.getElementById('btnFirmarContrato').addEventListener('click', function() {
            const nombre = document.getElementById('firmaNombre').value.trim();
            const cedula = document.getElementById('firmaCedula').value.trim();
            if (!nombre) { alert('Ingrese nombre'); return; }
            if (!cedula) { alert('Ingrese cedula'); return; }
            const firma = obtenerFirma();
            if (!firma) { alert('Debe firmar'); return; }
            if (!confirm('¿Firmar este contrato? Esta accion no se puede deshacer.')) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            const data = new FormData();
            data.append('token', '<?= $token ?>');
            data.append('firma_nombre', nombre);
            data.append('firma_cedula', cedula);
            data.append('firma_imagen', firma);

            fetch('<?= base_url("contrato/procesar-firma") ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(result => {
                if (result.success) { new bootstrap.Modal(document.getElementById('modalExito')).show(); }
                else { alert('Error: ' + (result.message || 'Intente de nuevo')); btn.disabled = false; btn.innerHTML = '<i class="bi bi-pen me-2"></i>Aprobar y Firmar Contrato'; }
            })
            .catch(() => { alert('Error de conexion'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-pen me-2"></i>Aprobar y Firmar Contrato'; });
        });
    });
    </script>
</body>
</html>
```

### 7. Vista Error — `app/Views/contracts/firma_error_contrato.php`

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Enlace no valido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card error-card mx-auto">
            <div class="card-body text-center py-5 px-4">
                <div class="text-danger mb-4"><i class="bi bi-exclamation-triangle" style="font-size: 5rem;"></i></div>
                <h3 class="text-danger mb-3">Enlace No Valido</h3>
                <p class="text-muted mb-4"><?= esc($mensaje) ?></p>
                <div class="alert alert-light"><small class="text-muted">Contacte al administrador o solicite un nuevo enlace.</small></div>
            </div>
        </div>
    </div>
</body>
</html>
```

---

# PARTE 2: GENERACION DE CLAUSULAS CON IA (OpenAI)

## Flujo Completo
```
Formulario de edicion de contrato
  → Seccion "Clausula Primera" o "Clausula Cuarta"
  → Click "Generar con IA"
  → SweetAlert2 modal pide datos contractuales
  → fetch POST /contracts/generar-clausula-ia (o clausula1-ia)
  → Backend construye prompt + llama OpenAI gpt-4o-mini
  → Retorna JSON {success, texto}
  → JS inserta texto en textarea
  → Toolbar aparece: "Regenerar" | "Refinar" | "Limpiar"
  → "Refinar" envia modo_refinamiento=true + texto_actual + instrucciones
  → Al guardar: POST /contracts/save-and-generate/{id} → genera PDF con Dompdf
```

## Archivos a Copiar (4 archivos)

### 1. Rutas adicionales — `app/Config/Routes.php`

```php
// Generacion IA de clausulas
$routes->post('/contracts/generar-clausula-ia', 'ContractController::generarClausulaIA');
$routes->post('/contracts/generar-clausula1-ia', 'ContractController::generarClausula1IA');

// Guardar y generar PDF
$routes->post('/contracts/save-and-generate/(:num)', 'ContractController::saveAndGeneratePDF/$1');
$routes->get('/contracts/download-pdf/(:num)', 'ContractController::downloadPDF/$1');
```

### 2. Servicio IA — `app/Services/IADocumentacionService.php`

Metodo clave que llama a OpenAI:

```php
<?php
namespace App\Services;

class IADocumentacionService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected float $temperature = 0.3; // Bajo para consistencia legal

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    /**
     * Genera contenido libre con un prompt directo
     * Usado por ContractController para clausulas
     */
    public function generarContenido(string $prompt, int $maxTokens = 1500): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new \Exception('OpenAI Error: ' . ($error['error']['message'] ?? "HTTP {$httpCode}"));
        }

        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
}
```

### 3. Metodos IA en Controller — Agregar a `ContractController.php`

```php
/**
 * Genera Clausula Cuarta (Duracion) con IA
 * POST /contracts/generar-clausula-ia
 * Body JSON: {id_cliente, plazo_ejecucion, duracion_contrato, fecha_inicio, fecha_fin,
 *             porcentaje_anticipo, condiciones_pago, terminacion_anticipada,
 *             obligaciones_especiales, contexto_adicional, texto_actual, modo_refinamiento}
 */
public function generarClausulaIA()
{
    $json = $this->request->getJSON(true);

    $idCliente = $json['id_cliente'] ?? null;
    $plazoEjecucion = $json['plazo_ejecucion'] ?? '';
    $duracionContrato = $json['duracion_contrato'] ?? '';
    $fechaInicio = $json['fecha_inicio'] ?? '';
    $fechaFin = $json['fecha_fin'] ?? '';
    $porcentajeAnticipo = $json['porcentaje_anticipo'] ?? '';
    $condicionesPago = $json['condiciones_pago'] ?? '';
    $terminacionAnticipada = $json['terminacion_anticipada'] ?? '';
    $obligacionesEspeciales = $json['obligaciones_especiales'] ?? '';
    $contextoAdicional = $json['contexto_adicional'] ?? '';
    $textoActual = $json['texto_actual'] ?? '';
    $modoRefinamiento = $json['modo_refinamiento'] ?? false;

    // Formatear fechas a espanol largo
    $fechaInicioF = $fechaInicio ? $this->formatearFechaLarga($fechaInicio) : '';
    $fechaFinF = $fechaFin ? $this->formatearFechaLarga($fechaFin) : '';

    // Info del cliente
    $infoCliente = '';
    if ($idCliente) {
        $client = $this->clientModel->find($idCliente);
        if ($client) {
            $infoCliente = "Datos del cliente (EL CONTRATANTE): {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}.";
        }
    }

    // Construir prompt
    if ($modoRefinamiento && !empty($textoActual)) {
        $prompt = "Eres un abogado experto en contratos SST en Colombia.\n\n" .
            "Tienes este texto de Clausula Cuarta:\n\n--- TEXTO ACTUAL ---\n{$textoActual}\n--- FIN ---\n\n" .
            "Aplica estas modificaciones:\n{$contextoAdicional}\n\n" .
            ($infoCliente ? $infoCliente . "\n\n" : "") .
            "REGLAS: Partes en MAYUSCULAS (EL CONTRATANTE, EL CONTRATISTA). Fechas reales, nunca placeholders.\n" .
            "Responde SOLO con el texto de la clausula.";
    } else {
        $acuerdos = [];
        if ($plazoEjecucion) $acuerdos[] = "Plazo: {$plazoEjecucion}";
        if ($duracionContrato) $acuerdos[] = "Duracion: {$duracionContrato}";
        if ($fechaInicioF) $acuerdos[] = "Inicio: {$fechaInicioF}";
        if ($fechaFinF) $acuerdos[] = "Fin: {$fechaFinF}";
        if ($porcentajeAnticipo) $acuerdos[] = "Anticipo: {$porcentajeAnticipo}";
        if ($condicionesPago) $acuerdos[] = "Pago: {$condicionesPago}";
        if ($terminacionAnticipada) $acuerdos[] = "Terminacion anticipada: {$terminacionAnticipada}";
        if ($obligacionesEspeciales) $acuerdos[] = "Obligaciones: {$obligacionesEspeciales}";

        $prompt = "Eres un abogado experto en contratos de prestacion de servicios SST en Colombia.\n\n" .
            "Genera la CLAUSULA CUARTA (Duracion y Plazo) con estos acuerdos:\n\n" .
            ($infoCliente ? $infoCliente . "\n\n" : "") .
            implode("\n", $acuerdos) . "\n\n" .
            "Incluir: 1) Plazo de ejecucion 2) Duracion 3) PARAGRAFO PRIMERO (terminacion anticipada) 4) PARAGRAFO SEGUNDO (sin prorroga automatica)\n" .
            "REGLAS: Partes en MAYUSCULAS. Fechas reales en espanol largo. Lenguaje juridico formal.\n" .
            "Responde SOLO con el texto.";
    }

    try {
        $iaService = new \App\Services\IADocumentacionService();
        $texto = $iaService->generarContenido($prompt, 1500);
        return $this->response->setJSON(['success' => true, 'texto' => trim($texto)]);
    } catch (\Exception $e) {
        return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
    }
}

/**
 * Genera Clausula Primera (Objeto) con IA
 * POST /contracts/generar-clausula1-ia
 */
public function generarClausula1IA()
{
    $json = $this->request->getJSON(true);

    $idCliente = $json['id_cliente'] ?? null;
    $descripcion = $json['descripcion_servicio'] ?? 'Diseno e implementacion del SG-SST';
    $tipoConsultor = $json['tipo_consultor'] ?? 'externo';
    $nombre = $json['nombre_coordinador'] ?? '';
    $cedula = $json['cedula_coordinador'] ?? '';
    $licencia = $json['licencia_coordinador'] ?? '';
    $contexto = $json['contexto_adicional'] ?? '';
    $textoActual = $json['texto_actual'] ?? '';
    $modoRefinar = $json['modo_refinamiento'] ?? false;

    $infoCliente = '';
    if ($idCliente) {
        $client = $this->clientModel->find($idCliente);
        if ($client) $infoCliente = "Cliente: {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}.";
    }

    $infoCoord = $nombre ? "Profesional SST: {$nombre}, cedula {$cedula}, licencia {$licencia}." : '';

    if ($modoRefinar && !empty($textoActual)) {
        $prompt = "Eres abogado experto SST Colombia.\n\nTexto actual:\n{$textoActual}\n\nModificaciones:\n{$contexto}\n\n{$infoCliente}\n{$infoCoord}\n\nRespuesta: solo texto clausula.";
    } else {
        $delegacion = $tipoConsultor === 'externo'
            ? "\nIMPORTANTE: Incluir parrafo de DELEGACION DE VISITAS (consultor externo puede delegar visitas a otros profesionales del equipo).\n"
            : '';
        $prompt = "Eres abogado experto SST Colombia.\n\nGenera CLAUSULA PRIMERA (Objeto) con:\n{$infoCliente}\nServicio: {$descripcion}\n{$infoCoord}\n{$delegacion}\n" .
            "Mencionar plataforma EnterpriseSST. Referenciar Resolucion 0312 de 2019.\n" .
            "Partes en MAYUSCULAS. Responde SOLO con texto.";
    }

    try {
        $iaService = new \App\Services\IADocumentacionService();
        $texto = $iaService->generarContenido($prompt, 1500);
        return $this->response->setJSON(['success' => true, 'texto' => trim($texto)]);
    } catch (\Exception $e) {
        return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
    }
}

private function formatearFechaLarga(string $fecha): string
{
    $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
              7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
    $ts = strtotime($fecha);
    if (!$ts) return $fecha;
    return (int)date('j',$ts) . ' de ' . $meses[(int)date('n',$ts)] . ' de ' . date('Y',$ts);
}
```

### 4. Frontend JS — En la vista `edit_contract_data.php`

El JS completo de los SweetAlert + fetch ya esta incluido en la vista del formulario.
Los archivos fuente originales son:

- `app/Views/contracts/edit_contract_data.php` — lineas 400-827 contienen TODO el JS

**Patron general del JS:**
1. `abrirSweetAlertIA()` → SweetAlert2 con inputs → recolecta datos → `generarClausulaConIA(datos, false)`
2. `abrirRefinar()` → SweetAlert2 con textarea instrucciones → `generarClausulaConIA({...texto_actual, modo_refinamiento: true}, true)`
3. `generarClausulaConIA(datos, esRefinamiento)` → fetch POST → inserta en textarea → muestra toolbar
4. `limpiarClausula()` → confirm → vacia textarea

Lo mismo se repite para Clausula 1 con funciones `abrirSweetAlertIAClausula1()`, `generarClausula1ConIA()`, etc.

---

# PARTE 3: GENERACION PDF CON DOMPDF

## Archivo: `app/Libraries/ContractPDFGenerator.php`

**Copiar completo** — genera HTML con todas las clausulas y lo convierte a PDF con Dompdf.

Logica clave:
- `buildClausulaObjeto($data)`: Si `$data['clausula_primera_objeto']` existe → usa ese texto. Si no → usa texto hardcoded de fallback.
- `buildClausulaDuracion($data)`: Si `$data['clausula_cuarta_duracion']` existe → usa ese texto. Si no → calcula duracion desde fechas.
- Las demas clausulas (2da, 3ra, 5ta-13va) son texto fijo.
- `buildSignaturesHTML($data)`: Genera bloque de firmas con imagenes base64. Busca `firma_cliente_imagen` para la firma digital del cliente.

---

# PARTE 4: VARIABLES DE ENTORNO (.env)

```
# SendGrid (emails)
SENDGRID_API_KEY=SG.xxxxx
SENDGRID_FROM_EMAIL=notificacion@tudominio.com
SENDGRID_FROM_NAME=Tu App

# OpenAI (generacion IA)
OPENAI_API_KEY=sk-xxxxx
OPENAI_MODEL=gpt-4o-mini
```

## Dependencias Composer

```
composer require dompdf/dompdf
composer require sendgrid/sendgrid
```

---

# RESUMEN: ORDEN DE IMPLEMENTACION

1. **ALTER TABLE** — Agregar columnas de firma a tbl_contratos
2. **Model** — Agregar campos a $allowedFields
3. **Service** — Crear IADocumentacionService.php (si no existe)
4. **Controller** — Agregar metodos de firma + metodos de IA
5. **Vistas** — Crear 3 vistas: contrato_firma.php, email_contrato_firma.php, firma_error_contrato.php
6. **Library** — Copiar ContractPDFGenerator.php (si no existe)
7. **Rutas** — Agregar todas las rutas
8. **Frontend** — Agregar JS de SweetAlert + fetch en la vista de edicion
9. **.env** — Configurar SENDGRID_API_KEY y OPENAI_API_KEY
10. **Composer** — Instalar dompdf y sendgrid
