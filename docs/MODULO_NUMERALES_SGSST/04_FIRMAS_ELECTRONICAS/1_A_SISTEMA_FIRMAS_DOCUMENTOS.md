# Sistema de Firmas Electrónicas para Documentos SST

## Resumen

Este documento explica cómo implementar el sistema de firmas electrónicas en cualquier documento del SG-SST. El sistema permite solicitar firmas por correo electrónico, registrar evidencia legal y mostrar las firmas en el documento final.

---

## ⚠️ REGLA CRÍTICA DE AUDITORÍA (Actualizado 2026-02-02)

**TODOS los documentos técnicos del SG-SST DEBEN incluir la firma de "Elaboró / Consultor SST".**

Esta regla es **OBLIGATORIA** para cumplimiento de auditorías de la Resolución 0312/2019.

### Estructura mínima de firmas para auditoría:

| Rol | Responsable | Obligatorio |
|-----|-------------|-------------|
| **ELABORÓ** | Consultor SST | ✅ Siempre |
| **APROBÓ** | Representante Legal | ✅ Siempre |
| **REVISÓ** | Vigía SST / Delegado SST / COPASST | ⚠️ Según estándares |

### Documentos corregidos en esta actualización:

- `responsabilidades_rep_legal.php` - Agregada firma Elaboró (Consultor)
- `presupuesto_preview.php` - Agregada firma Elaboró (Consultor)
- `presupuesto_pdf.php` - Agregada firma Elaboró (Consultor)
- `presupuesto_word.php` - Agregada firma Elaboró (Consultor)
- `presupuesto_sst.php` - Agregada firma Elaboró (Consultor)
- `word_template.php` - Corregida sección firmasRepLegalYSegundo

**NO se permite** generar documentos técnicos sin la firma del Consultor/Elaboró.

---

## 1. Tipos de Firma Según Documento

El sistema determina automáticamente el tipo de firma según estas variables:

| Variable | Condición | Tipo de Firma |
|----------|-----------|---------------|
| `$esFirmaFisica` | `tipo_firma === 'fisica'` o `tipo_documento === 'responsabilidades_trabajadores_sgsst'` | Tabla múltiples trabajadores |
| `$soloFirmaConsultor` | `solo_firma_consultor` o `tipo_documento === 'responsabilidades_responsable_sgsst'` | **1 firmante**: Responsable SST |
| `$soloFirmaRepLegal` | `solo_firma_rep_legal` | **2 firmantes**: Elaboró (Consultor) + Aprobó (Rep. Legal) ⚠️ CORREGIDO |
| `$firmasRepLegalYSegundo` | Doc responsabilidades rep legal + segundo firmante | **3 firmantes**: Elaboró (Consultor) + Aprobó (Rep. Legal) + Revisó (Vigía/Delegado) ⚠️ CORREGIDO |
| `$esDosFirmantesPorDefinicion` | `firmantesDefinidos = ['representante_legal', 'responsable_sst']` | **2 firmantes**: Elaboró (Responsable SST) + Aprobó (Rep. Legal) |
| `$esSoloDosFirmantes` | `estandares <= 10` y NO `requiere_delegado` | **2 firmantes**: Elaboró (Consultor) + Aprobó (Rep. Legal) |
| Default | `estandares > 10` o `requiere_delegado` | **3 firmantes**: Elaboró (Consultor) + Revisó (Vigía/COPASST/Delegado) + Aprobó (Rep. Legal) |

---

## 2. Arquitectura del Sistema

### 2.1 Tablas de Base de Datos

```sql
-- Solicitudes de firma
tbl_doc_firma_solicitudes
├── id_solicitud (PK)
├── id_documento (FK → tbl_documentos_sst)
├── token (único, para enlace de firma)
├── firmante_tipo ('delegado_sst', 'representante_legal', 'elaboro', 'reviso', 'responsable_sst')
├── firmante_nombre
├── firmante_email
├── firmante_cargo
├── firmante_documento (cédula)
├── orden_firma (1, 2, 3...)
├── estado ('pendiente', 'esperando', 'firmado', 'cancelado', 'expirado')
├── fecha_firma
└── created_at

-- Evidencias de firma
tbl_doc_firma_evidencias
├── id_evidencia (PK)
├── id_solicitud (FK)
├── ip_address
├── user_agent
├── geolocalizacion
├── tipo_firma ('draw', 'upload', 'typed')
├── firma_imagen (base64 data URI)
├── hash_documento (SHA256)
└── created_at
```

### 2.2 Archivos Clave

| Archivo | Descripción |
|---------|-------------|
| `app/Controllers/FirmaElectronicaController.php` | Controlador principal de firmas |
| `app/Models/DocFirmaModel.php` | Modelo para gestión de firmas |
| `app/Views/firma/solicitar.php` | Vista para crear solicitud |
| `app/Views/firma/firmar.php` | Vista pública para firmar |
| `app/Views/firma/estado.php` | Vista de estado de firmas |

---

## 3. Configuración de Firmantes por Tipo de Documento

### 3.1 En TIPOS_DOCUMENTO (constante del sistema)

Cada tipo de documento puede definir sus firmantes específicos:

```php
'procedimiento_control_documental' => [
    'nombre' => 'Procedimiento Control Documental',
    'firmantes' => ['representante_legal', 'responsable_sst'],  // Solo 2 firmantes
    // ...
],

'responsabilidades_responsable_sgsst' => [
    'nombre' => 'Responsabilidades Responsable SST',
    'solo_firma_consultor' => true,  // Solo 1 firmante
    // ...
],

'responsabilidades_rep_legal' => [
    'nombre' => 'Responsabilidades Rep. Legal',
    'solo_firma_rep_legal' => true,  // Solo Rep. Legal
    'segundo_firmante' => true,       // + Vigía/Delegado
    // ...
],

'responsabilidades_trabajadores_sgsst' => [
    'nombre' => 'Responsabilidades Trabajadores',
    'tipo_firma' => 'fisica',  // Tabla para múltiples firmas
    // ...
],
```

### 3.2 En Contexto del Cliente (tbl_cliente_contexto_sst)

| Campo | Descripción |
|-------|-------------|
| `estandares_aplicables` | 7, 21 o 60 - Determina complejidad |
| `requiere_delegado_sst` | Boolean - Si tiene delegado asignado |
| `delegado_sst_nombre` | Nombre del delegado |
| `delegado_sst_email` | Email para firma electrónica |
| `delegado_sst_cargo` | Cargo (default: Delegado SST) |
| `delegado_sst_cedula` | Cédula del delegado |
| `representante_legal_nombre` | Nombre del rep. legal |
| `representante_legal_email` | Email para firma electrónica |
| `representante_legal_cargo` | Cargo (default: Representante Legal) |
| `representante_legal_cedula` | Cédula del rep. legal |

---

## 4. Lógica de Determinación de Firmantes

### 4.1 Variables en la Vista

```php
<?php
// Datos del contexto
$estandares = $contexto['estandares_aplicables'] ?? 60;
$requiereDelegado = !empty($contexto['requiere_delegado_sst']);

// Firmantes definidos por el tipo de documento (tiene prioridad)
$firmantesDefinidosArr = $firmantesDefinidos ?? null;
$usaFirmantesDefinidos = !empty($firmantesDefinidosArr) && is_array($firmantesDefinidosArr);

// Caso: Solo 2 firmantes por definición del documento
// Cuando firmantesDefinidos = ['representante_legal', 'responsable_sst']
$esDosFirmantesPorDefinicion = $usaFirmantesDefinidos
    && in_array('representante_legal', $firmantesDefinidosArr)
    && in_array('responsable_sst', $firmantesDefinidosArr)
    && !in_array('delegado_sst', $firmantesDefinidosArr)
    && !in_array('vigia_sst', $firmantesDefinidosArr)
    && !in_array('copasst', $firmantesDefinidosArr);

// Caso: Solo 2 firmantes por estándares (7 estándares sin delegado)
$esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

// Datos del Consultor/Responsable SST
$consultorNombre = $consultor['nombre_consultor'] ?? '';
// El cargo cambia según el tipo de firma
$consultorCargo = $esDosFirmantesPorDefinicion ? 'Responsable del SG-SST' : 'Consultor SST';
$consultorLicencia = $consultor['numero_licencia'] ?? '';
$firmaConsultor = $consultor['firma_consultor'] ?? '';

// Datos del Delegado SST
$delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
$delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

// Datos del Representante Legal
$repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '';
$repLegalCargo = 'Representante Legal';
?>
```

### 4.2 Árbol de Decisión

```
┌─────────────────────────────────────────────────────────────────┐
│                    ¿Qué tipo de firma usar?                     │
└─────────────────────────────────────────────────────────────────┘
                              │
          ┌───────────────────┼───────────────────┐
          ▼                   ▼                   ▼
   ¿tipo_firma =        ¿solo_firma_        ¿firmantesDefinidos
     'fisica'?          consultor?           tiene valores?
          │                   │                   │
          ▼                   ▼                   ▼
   ┌──────────┐        ┌──────────┐        ┌──────────────┐
   │ FIRMA    │        │ 1 FIRMA  │        │ Usar array   │
   │ FÍSICA   │        │ Consultor│        │ definido     │
   │ (tabla)  │        │ /Resp SST│        └──────────────┘
   └──────────┘        └──────────┘               │
                                      ┌───────────┴───────────┐
                                      ▼                       ▼
                            ['rep_legal',           ['rep_legal',
                             'responsable_sst']      'delegado_sst']
                                      │                       │
                                      ▼                       ▼
                               ┌──────────┐           ┌──────────┐
                               │ 2 FIRMAS │           │ 2 FIRMAS │
                               │ Resp SST │           │ Rep.Legal│
                               │+Rep.Legal│           │+Delegado │
                               └──────────┘           └──────────┘

   Si NO hay firmantesDefinidos:
          │
          ▼
   ¿estandares <= 10 && !requiereDelegado?
          │
     ┌────┴────┐
     ▼         ▼
    SÍ        NO
     │         │
     ▼         ▼
┌──────────┐  ┌──────────────┐
│ 2 FIRMAS │  │ 3 FIRMANTES  │
│ Consultor│  │ Consultor    │
│+Rep.Legal│  │+Vigía/COPASST│
└──────────┘  │+Rep.Legal    │
              └──────────────┘
```

---

## 5. Implementación en Vistas

### 5.1 Botones en Barra de Herramientas (Vistas de Vista Previa)

Para vistas como `programa_capacitacion.php`, `procedimiento_control_documental.php`:

```php
<!-- Botón Solicitar Firmas -->
<?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
    <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2">
        <i class="bi bi-pen me-1"></i>Solicitar Firmas
    </a>
<?php endif; ?>

<!-- Botón Ver Firmas (firmado) -->
<?php if (($documento['estado'] ?? '') === 'firmado'): ?>
    <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-success btn-sm me-2">
        <i class="bi bi-patch-check me-1"></i>Ver Firmas
    </a>
<?php endif; ?>

<!-- Botón Estado Firmas (pendiente) -->
<?php if (($documento['estado'] ?? '') === 'pendiente_firma'): ?>
    <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-warning btn-sm me-2">
        <i class="bi bi-clock-history me-1"></i>Estado Firmas
    </a>
<?php endif; ?>
```

### 5.1.1 Botón en Vista de Generación IA (Corregido 2026-02-02)

Para la vista `generar_con_ia.php` (rutas `/documentos/generar/{tipo}/{id}`):

```php
<?php
$estadoDoc = $documento['estado'] ?? 'borrador';
$idDocumento = $documento['id_documento'] ?? null;
?>

<?php if ($estadoDoc === 'firmado'): ?>
    <!-- Documento ya firmado -->
    <div class="alert alert-success mb-2 py-2 px-3">
        <i class="bi bi-patch-check-fill me-1"></i>
        <small>Documento firmado y aprobado</small>
    </div>
    <a href="<?= base_url('firma/estado/' . $idDocumento) ?>" class="btn btn-outline-success btn-sm w-100">
        <i class="bi bi-eye me-1"></i>Ver Firmas
    </a>

<?php elseif ($estadoDoc === 'pendiente_firma'): ?>
    <!-- Esperando firmas -->
    <div class="alert alert-warning mb-2 py-2 px-3">
        <i class="bi bi-clock-history me-1"></i>
        <small>Pendiente de firmas</small>
    </div>
    <a href="<?= base_url('firma/estado/' . $idDocumento) ?>" class="btn btn-warning btn-sm w-100">
        <i class="bi bi-pen me-1"></i>Estado Firmas
    </a>

<?php elseif (in_array($estadoDoc, ['borrador', 'generado', 'aprobado', 'en_revision']) && $idDocumento): ?>
    <!-- ✅ LISTO PARA ENVIAR A FIRMAS (incluye borrador) -->
    <a href="<?= base_url('firma/solicitar/' . $idDocumento) ?>" class="btn btn-success btn-sm w-100">
        <i class="bi bi-pen me-1"></i>Enviar a Firmas
        <small class="d-block" style="font-size: 0.6rem;">El cliente revisara y firmara</small>
    </a>

<?php elseif ($idDocumento): ?>
    <!-- Documento existe pero estado no permite firmas -->
    <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
        <i class="bi bi-pen me-1"></i>Enviar a Firmas
        <small class="d-block" style="font-size: 0.6rem;">Estado: <?= esc($estadoDoc) ?></small>
    </button>

<?php else: ?>
    <!-- Documento no existe aún -->
    <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
        <i class="bi bi-pen me-1"></i>Enviar a Firmas
        <small class="d-block" style="font-size: 0.6rem;">Primero guarda el documento</small>
    </button>
<?php endif; ?>
```

**Nota importante:** La condición anterior usaba `$todasSeccionesListas` lo cual requería que todas las secciones estuvieran aprobadas. La nueva lógica solo verifica que el documento exista y tenga un estado válido.

### 5.2 Sección de Firmas - Tipo A: Solo Responsable SST (1 Firmante)

```php
<?php if ($soloFirmaConsultor): ?>
<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;">
        <i class="bi bi-pen me-2"></i>FIRMA DE ACEPTACIÓN
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
        <thead>
            <tr style="background: #e9ecef;">
                <th style="text-align: center;">RESPONSABLE DEL SG-SST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 20px; text-align: center;">
                    <p><strong>Nombre:</strong> <?= esc($consultorNombre) ?></p>
                    <p><strong>Documento:</strong> <?= esc($consultor['cedula'] ?? '') ?></p>
                    <p><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></p>
                    <p><strong>Cargo:</strong> Responsable del SG-SST</p>
                </td>
            </tr>
            <tr>
                <td style="height: 80px; text-align: center; vertical-align: bottom;">
                    <?php if (!empty($firmaConsultor)): ?>
                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 40%; margin: 5px auto 0;">
                        <small>Firma</small>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

### 5.3 Sección de Firmas - Tipo B: Solo Rep. Legal (1 Firmante)

```php
<?php if ($soloFirmaRepLegal && !$firmasRepLegalYSegundo): ?>
<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;">
        <i class="bi bi-pen me-2"></i>FIRMA DE ACEPTACIÓN
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
        <thead>
            <tr style="background: #e9ecef;">
                <th style="text-align: center;">REPRESENTANTE LEGAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 20px; text-align: center;">
                    <p><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></p>
                    <p><strong>Documento:</strong> <?= esc($contexto['representante_legal_cedula'] ?? '') ?></p>
                    <p><strong>Cargo:</strong> Representante Legal</p>
                </td>
            </tr>
            <tr>
                <td style="height: 80px; text-align: center; vertical-align: bottom;">
                    <?php
                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                    ?>
                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 40%; margin: 5px auto 0;">
                        <small>Firma</small>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

### 5.4 Sección de Firmas - Tipo C: Rep. Legal + Vigía/Delegado (2 Firmantes)

Para documentos como "Responsabilidades del Representante Legal":

```php
<?php if ($firmasRepLegalYSegundo): ?>
<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;">
        <i class="bi bi-pen me-2"></i>FIRMAS DE ACEPTACIÓN
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
        <thead>
            <tr style="background: #e9ecef;">
                <th style="width: 50%; text-align: center;">REPRESENTANTE LEGAL</th>
                <th style="width: 50%; text-align: center;">
                    <?= $requiereDelegado ? 'DELEGADO SST' : 'VIGÍA SST' ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 15px;">
                    <p><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></p>
                    <p><strong>Documento:</strong> <?= esc($contexto['representante_legal_cedula'] ?? '') ?></p>
                    <p><strong>Cargo:</strong> Representante Legal</p>
                </td>
                <td style="padding: 15px;">
                    <p><strong>Nombre:</strong> <?= esc($delegadoNombre) ?></p>
                    <p><strong>Documento:</strong> <?= esc($contexto['delegado_sst_cedula'] ?? '') ?></p>
                    <p><strong>Cargo:</strong> <?= $requiereDelegado ? esc($delegadoCargo) : 'Vigía SST' ?></p>
                </td>
            </tr>
            <tr>
                <td style="height: 70px; text-align: center; vertical-align: bottom;">
                    <?php
                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                    ?>
                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 65%; margin: 5px auto 0;">
                        <small>Firma</small>
                    </div>
                </td>
                <td style="height: 70px; text-align: center; vertical-align: bottom;">
                    <?php
                    $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                    if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                    ?>
                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 65%; margin: 5px auto 0;">
                        <small>Firma</small>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

### 5.5 Sección de Firmas - Tipo D: Responsable SST + Rep. Legal (2 Firmantes por Definición)

Para documentos que definen `firmantes = ['representante_legal', 'responsable_sst']`:

```php
<?php if ($esDosFirmantesPorDefinicion): ?>
<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;">
        <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACIÓN
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
        <thead>
            <tr style="background: #e9ecef;">
                <th style="width: 50%; text-align: center;">
                    <i class="bi bi-person-badge me-1"></i>Elaboró / Responsable del SG-SST
                </th>
                <th style="width: 50%; text-align: center;">
                    <i class="bi bi-person-check me-1"></i>Aprobó / Representante Legal
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 20px; height: 180px; position: relative;">
                    <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                    <div><strong>Cargo:</strong> Responsable del SG-SST</div>
                    <?php if (!empty($consultorLicencia)): ?>
                    <div><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                    <?php endif; ?>
                    <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                        <?php if (!empty($firmaConsultor)): ?>
                            <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
                <td style="padding: 20px; height: 180px; position: relative;">
                    <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                    <div><strong>Cargo:</strong> Representante Legal</div>
                    <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                        <?php
                        $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                        if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                        ?>
                            <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

### 5.6 Sección de Firmas - Tipo E: Consultor + Rep. Legal (2 Firmantes - 7 Estándares)

```php
<?php if ($esSoloDosFirmantes && !$esDosFirmantesPorDefinicion): ?>
<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;">
        <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACIÓN
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
        <thead>
            <tr style="background: #e9ecef;">
                <th style="width: 50%; text-align: center;">
                    <i class="bi bi-person-badge me-1"></i>Elaboró / Consultor SST
                </th>
                <th style="width: 50%; text-align: center;">
                    <i class="bi bi-person-check me-1"></i>Aprobó / Representante Legal
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 20px; height: 180px; position: relative;">
                    <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                    <div><strong>Cargo:</strong> Consultor SST</div>
                    <?php if (!empty($consultorLicencia)): ?>
                    <div><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                    <?php endif; ?>
                    <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                        <?php if (!empty($firmaConsultor)): ?>
                            <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
                <td style="padding: 20px; height: 180px; position: relative;">
                    <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                    <div><strong>Cargo:</strong> Representante Legal</div>
                    <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                        <?php
                        $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                        if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                        ?>
                            <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

### 5.7 Sección de Firmas - Tipo F: 3 Firmantes (Estándar 21+ o con Delegado)

```php
<?php if (!$esSoloDosFirmantes && !$esDosFirmantesPorDefinicion && !$soloFirmaConsultor && !$soloFirmaRepLegal && !$firmasRepLegalYSegundo): ?>
<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;">
        <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACIÓN
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
        <thead>
            <tr style="background: #e9ecef;">
                <th style="width: 33.33%; text-align: center;">
                    <i class="bi bi-person-badge me-1"></i>Elaboró
                </th>
                <th style="width: 33.33%; text-align: center;">
                    <?php if ($requiereDelegado): ?>
                        <i class="bi bi-shield-check me-1"></i>Revisó / Delegado SST
                    <?php else: ?>
                        <i class="bi bi-people me-1"></i>Revisó / <?= $estandares <= 21 ? 'Vigía SST' : 'COPASST' ?>
                    <?php endif; ?>
                </th>
                <th style="width: 33.33%; text-align: center;">
                    <i class="bi bi-person-check me-1"></i>Aprobó
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- CONSULTOR SST -->
                <td style="padding: 15px; height: 160px; position: relative;">
                    <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                    <div><strong>Cargo:</strong> Consultor SST</div>
                    <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                        <?php if (!empty($firmaConsultor)): ?>
                            <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
                <!-- DELEGADO/VIGÍA/COPASST -->
                <td style="padding: 15px; height: 160px; position: relative;">
                    <div><strong>Nombre:</strong> <?= $requiereDelegado ? esc($delegadoNombre) : '' ?></div>
                    <div><strong>Cargo:</strong>
                        <?= $requiereDelegado ? esc($delegadoCargo) : ($estandares <= 21 ? 'Vigía de SST' : 'COPASST') ?>
                    </div>
                    <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                        <?php
                        $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                        if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                        ?>
                            <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
                <!-- REPRESENTANTE LEGAL -->
                <td style="padding: 15px; height: 160px; position: relative;">
                    <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                    <div><strong>Cargo:</strong> Representante Legal</div>
                    <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                        <?php
                        $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                        if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                        ?>
                            <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                            <small>Firma</small>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

### 5.8 Sección de Firmas - Tipo G: Firma Física (Trabajadores)

Para documentos como "Responsabilidades de Trabajadores":

```php
<?php if ($esFirmaFisica): ?>
<!-- Salto de página para Word/PDF -->
<div style="page-break-before: always;"></div>

<!-- Instrucciones -->
<div style="background: #e7f3ff; padding: 10px; margin-bottom: 15px; border-left: 3px solid #0d6efd;">
    <strong>Instrucciones:</strong> Con mi firma certifico haber leído, comprendido y aceptado
    las responsabilidades aquí descritas en materia de Seguridad y Salud en el Trabajo.
</div>

<!-- Tabla de firmas múltiples -->
<table class="table table-bordered" style="font-size: 0.85rem;">
    <thead>
        <tr style="background: #f8f9fa;">
            <th style="width: 30px;">No.</th>
            <th style="width: 70px;">Fecha</th>
            <th>Nombre Completo</th>
            <th style="width: 80px;">Cédula</th>
            <th style="width: 100px;">Cargo / Área</th>
            <th style="width: 90px;">Firma</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $filasFirma = 15; // Número de filas para firmas
        for ($i = 1; $i <= $filasFirma; $i++):
        ?>
        <tr>
            <td style="text-align: center; height: 25px;"><?= $i ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php endfor; ?>
    </tbody>
</table>
<?php endif; ?>
```

---

## 6. Flujo de Firma Electrónica

### 6.1 Crear Solicitud

```
Usuario → /firma/solicitar/{id_documento}
       → Muestra resumen de firmantes del contexto
       → POST /firma/crear-solicitud
       → Crea registros en tbl_doc_firma_solicitudes
       → Envía email al primer firmante
       → Cambia estado documento a 'pendiente_firma'
```

### 6.2 Orden de Firmantes

El sistema crea las solicitudes en este orden:

1. **Delegado SST** (si `requiere_delegado_sst` = true) - orden_firma = 1, estado = 'pendiente'
2. **Representante Legal** - orden_firma = 2, estado = 'esperando' (hasta que delegado firme)

Si NO hay delegado:
1. **Representante Legal** - orden_firma = 1, estado = 'pendiente'

### 6.3 Proceso de Firma

```
Firmante recibe email → Click enlace con token
                     → /firma/firmar/{token}
                     → Dibuja/sube firma + acepta términos
                     → POST /firma/procesar-firma
                     → Guarda evidencia
                     → Si hay siguiente → activa y envía email
                     → Si es último → estado documento = 'firmado'
```

---

## 7. Variables del Controlador

El controlador debe pasar estas variables a la vista:

```php
// En el controlador
public function verDocumento($idCliente, $tipoDocumento, $anio)
{
    // ... código existente ...

    // Obtener firmas electrónicas
    $firmasElectronicas = $this->obtenerFirmasElectronicas($documento['id_documento']);

    // Datos para la vista
    $data = [
        'documento' => $documento,
        'cliente' => $cliente,
        'contexto' => $this->contextoModel->getByCliente($idCliente),
        'consultor' => $this->consultorModel->find($idConsultor),
        'firmasElectronicas' => $firmasElectronicas,
        'firmantesDefinidos' => TIPOS_DOCUMENTO[$tipoDocumento]['firmantes'] ?? null,
        // ... otras variables
    ];

    return view('documentos_sst/' . $tipoDocumento, $data);
}

private function obtenerFirmasElectronicas($idDocumento)
{
    $firmasElectronicas = [];
    $db = \Config\Database::connect();

    $solicitudes = $db->table('tbl_doc_firma_solicitudes')
        ->where('id_documento', $idDocumento)
        ->where('estado', 'firmado')
        ->get()
        ->getResultArray();

    foreach ($solicitudes as $sol) {
        $evidencia = $db->table('tbl_doc_firma_evidencias')
            ->where('id_solicitud', $sol['id_solicitud'])
            ->get()
            ->getRowArray();

        $firmasElectronicas[$sol['firmante_tipo']] = [
            'solicitud' => $sol,
            'evidencia' => $evidencia
        ];
    }

    return $firmasElectronicas;
}
```

---

## 8. Resumen de Tipos de Firma

| Tipo | Firmantes | Cuándo Aplica | Título Sección |
|------|-----------|---------------|----------------|
| A | 1: Responsable SST | `solo_firma_consultor = true` | FIRMA DE ACEPTACIÓN |
| B | 1: Rep. Legal | `solo_firma_rep_legal = true` | FIRMA DE ACEPTACIÓN |
| C | 2: Rep. Legal + Vigía/Delegado | Responsabilidades Rep. Legal | FIRMAS DE ACEPTACIÓN |
| D | 2: Responsable SST + Rep. Legal | `firmantes = ['representante_legal', 'responsable_sst']` | FIRMAS DE APROBACIÓN |
| E | 2: Consultor + Rep. Legal | 7 estándares sin delegado | FIRMAS DE APROBACIÓN |
| F | 3: Consultor + Vigía/COPASST + Rep. Legal | 21+ estándares o con delegado | FIRMAS DE APROBACIÓN |
| G | Múltiples (física) | `tipo_firma = 'fisica'` | Tabla con filas vacías |

---

## 9. Checklist de Implementación

Para agregar firmas a un nuevo documento:

- [ ] Definir en `TIPOS_DOCUMENTO` si tiene firmantes especiales
- [ ] Agregar botones en barra de herramientas (sección 5.1)
- [ ] Agregar lógica de determinación de firmantes (sección 4.1)
- [ ] Agregar sección de firmas según tipo (secciones 5.2-5.8)
- [ ] Pasar variables desde controlador: `$contexto`, `$consultor`, `$firmasElectronicas`, `$firmantesDefinidos`
- [ ] Configurar datos de firmantes en contexto del cliente

---

## 10. Archivos de Referencia

Vistas que implementan correctamente cada tipo:

| Tipo | Archivo de Referencia |
|------|----------------------|
| D (2 firmantes definidos) | `procedimiento_control_documental.php` |
| E (2 firmantes 7 estándares) | `programa_capacitacion.php` |
| F (3 firmantes) | `documento_generico.php` |
| A (solo consultor) | `responsabilidades_responsable_sst.php` |
| C (Rep Legal + Delegado) | `responsabilidades_rep_legal.php` |
| G (firma física) | `responsabilidades_trabajadores.php` |

---

## 11. Lecciones Aprendidas (2026-02-02)

### 11.1 Estado 'borrador' es crítico

**Problema:** El botón "Enviar a Firmas" no se activaba en la vista de generación IA (`/documentos/generar/{tipo}/{id}`).

**Causa raíz:** Los documentos se crean con estado `borrador` cuando se guarda la primera sección (línea 1233 de `DocumentosSSTController.php`), pero la condición original no incluía este estado.

**Solución:** Agregar `'borrador'` a la lista de estados permitidos:

```php
// ANTES (no funcionaba para documentos nuevos)
<?php elseif (in_array($estadoDoc, ['generado', 'aprobado', 'en_revision']) && $idDocumento): ?>

// DESPUÉS (correcto)
<?php elseif (in_array($estadoDoc, ['borrador', 'generado', 'aprobado', 'en_revision']) && $idDocumento): ?>
```

### 11.2 Ciclo de vida de estados de documento

```
┌─────────────┐     Guardar      ┌──────────┐     Aprobar      ┌──────────┐
│  No existe  │ ──────────────▶  │ borrador │ ──────────────▶  │ aprobado │
│ (id=NULL)   │    1ra sección   │          │    documento     │          │
└─────────────┘                  └──────────┘                  └──────────┘
                                      │                              │
                                      │ Enviar a Firmas              │ Enviar a Firmas
                                      ▼                              ▼
                                ┌──────────────────┐          ┌──────────────────┐
                                │ pendiente_firma  │          │ pendiente_firma  │
                                └──────────────────┘          └──────────────────┘
                                      │                              │
                                      │ Todas las firmas             │
                                      ▼                              ▼
                                ┌──────────┐                  ┌──────────┐
                                │ firmado  │                  │ firmado  │
                                └──────────┘                  └──────────┘
```

### 11.3 Archivos que requieren sincronización

Cuando se modifica la lógica de estados permitidos, se deben actualizar **AMBOS** archivos:

| Archivo | Ubicación | Propósito |
|---------|-----------|-----------|
| `generar_con_ia.php` | Línea 256 | Mostrar/ocultar botón en UI |
| `FirmaElectronicaController.php` | Línea 56 | Validar acceso al endpoint |

**Si solo se actualiza uno, habrá inconsistencias.**

### 11.4 Técnica de debug recomendada

Para diagnosticar problemas con el botón de firmas, agregar temporalmente:

```php
<!-- DEBUG TEMPORAL -->
<div class="alert alert-info py-1 px-2 mb-2" style="font-size: 0.7rem;">
    <strong>DEBUG:</strong> estado=<?= esc($estadoDoc) ?>, id=<?= esc($idDocumento ?? 'NULL') ?>
</div>
<!-- FIN DEBUG -->
```

Esto permite ver rápidamente si el problema es:
- `id=NULL` → El documento no existe en BD (falta guardar)
- `estado=X` → El estado no está en la lista permitida

### 11.5 Regla de oro para el botón

> **El botón "Enviar a Firmas" debe activarse cuando el documento EXISTE, no cuando esté "completo".**
>
> El usuario debe poder enviar a firmas un documento en cualquier momento después de guardarlo, sin importar si todas las secciones están aprobadas.

### 11.6 Prioridad de firmantes: `requiere_delegado` SIEMPRE gana (Corregido 2026-02-02)

**Problema:** El documento `procedimiento_control_documental` tiene configurados solo 2 firmantes en BD (`responsable_sst`, `representante_legal`). Cuando el cliente tiene `requiere_delegado_sst = true`, la firma del Delegado NO aparecía.

**Causa raíz:** La condición `$esDosFirmantesPorDefinicion` tenía prioridad absoluta sobre `$requiereDelegado`.

**Código ANTES (incorrecto):**
```php
<?php if ($esDosFirmantesPorDefinicion): ?>
    // 2 firmantes - IGNORABA $requiereDelegado
<?php elseif ($esSoloDosFirmantes): ?>
    // 2 firmantes
<?php else: ?>
    // 3 firmantes
<?php endif; ?>
```

**Código DESPUÉS (correcto):**
```php
<?php if ($requiereDelegado): ?>
    // 3 firmantes - PRIORIDAD MÁXIMA: si cliente tiene delegado, SIEMPRE aparece
<?php elseif ($esDosFirmantesPorDefinicion): ?>
    // 2 firmantes - solo si NO hay delegado
<?php elseif ($esSoloDosFirmantes): ?>
    // 2 firmantes
<?php else: ?>
    // 3 firmantes
<?php endif; ?>
```

**Regla de oro para firmantes:**

> **Si el cliente tiene `requiere_delegado_sst = true`, el Delegado SST SIEMPRE debe aparecer en las firmas, independientemente de la configuración del tipo de documento.**

### 11.7 Orden de prioridad para determinar firmantes

```
1. ¿Cliente tiene requiere_delegado_sst = true?
   └─ SÍ → 3 FIRMANTES (Consultor + Delegado + Rep. Legal) ← PRIORIDAD MÁXIMA

2. ¿Documento tiene firmantesDefinidos específicos?
   └─ SÍ → Usar firmantes definidos (2 o 3 según configuración)

3. ¿Cliente tiene ≤10 estándares Y NO tiene delegado?
   └─ SÍ → 2 FIRMANTES (Consultor + Rep. Legal)

4. DEFAULT (>10 estándares)
   └─ 3 FIRMANTES (Consultor + Vigía/COPASST + Rep. Legal)
```

### 11.8 Actualización dinámica del botón de firmas (Corregido 2026-02-05)

**Problema:** El botón "Enviar a Firmas" no se activa automáticamente cuando se guarda la primera sección (momento en que se crea el documento en BD).

**Causa raíz:** Las variables PHP (`$idDocumento`, `$estadoDoc`) se evalúan **UNA SOLA VEZ** al cargar la página. Si el documento no existía al cargar, estas variables quedan con valores iniciales (`null`, `'borrador'`) aunque el documento se cree después vía AJAX.

**Diagrama del problema:**

```
┌─────────────────────────────────────────────────────────────────────┐
│                    CARGA DE PÁGINA (PHP)                            │
├─────────────────────────────────────────────────────────────────────┤
│  $documento = BD->buscar(cliente, tipo, año) → NULL (no existe)     │
│  $idDocumento = $documento['id_documento'] ?? null → NULL           │
│  $estadoDoc = $documento['estado'] ?? 'borrador' → 'borrador'       │
│                                                                     │
│  Botón renderizado: DESHABILITADO (porque $idDocumento = null)      │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    USUARIO TRABAJA (JavaScript)                     │
├─────────────────────────────────────────────────────────────────────┤
│  1. Genera secciones con IA                                         │
│  2. Hace clic en "Guardar" → AJAX POST a /documentos/guardar-seccion│
│  3. Backend CREA documento en BD con id_documento = 123             │
│  4. Respuesta JSON: {success: true}                                 │
│                                                                     │
│  ⚠️ PROBLEMA: El botón sigue DESHABILITADO porque PHP ya ejecutó   │
│     y la variable $idDocumento en el HTML sigue siendo null         │
└─────────────────────────────────────────────────────────────────────┘
```

**Solución implementada:**

#### Paso 1: Backend retorna `id_documento` (DocumentosSSTController.php)

```php
// Al crear documento nuevo
$this->db->table('tbl_documentos_sst')->insert([...]);
$nuevoIdDocumento = $this->db->insertID();

return $this->response->setJSON([
    'success' => true,
    'message' => 'Seccion guardada',
    'id_documento' => $nuevoIdDocumento,  // ← NUEVO
    'documento_creado' => true             // ← NUEVO
]);

// Al actualizar documento existente
return $this->response->setJSON([
    'success' => true,
    'message' => 'Seccion guardada',
    'id_documento' => $documento['id_documento']  // ← NUEVO
]);
```

#### Paso 2: JavaScript actualiza botón dinámicamente (generar_con_ia.php)

```javascript
// Variable para trackear el id_documento actual
let idDocumentoActual = <?= isset($documento['id_documento']) ? $documento['id_documento'] : 'null' ?>;

// Función que reemplaza el botón deshabilitado por uno activo
function actualizarBotonFirmas(idDocumento) {
    if (!idDocumento) return;

    idDocumentoActual = idDocumento;

    // Buscar el botón deshabilitado de firmas
    const contenedorAcciones = document.querySelector('.sidebar .d-grid.gap-2');
    const btnFirmasDeshabilitado = contenedorAcciones?.querySelector('button.btn-secondary[disabled]');

    if (btnFirmasDeshabilitado && btnFirmasDeshabilitado.innerHTML.includes('Enviar a Firmas')) {
        // Crear nuevo enlace activo
        const nuevoBtn = document.createElement('a');
        nuevoBtn.href = '<?= base_url("firma/solicitar/") ?>' + idDocumento;
        nuevoBtn.className = 'btn btn-success btn-sm w-100';
        nuevoBtn.innerHTML = '<i class="bi bi-pen me-1"></i>Enviar a Firmas...';

        // Reemplazar botón
        btnFirmasDeshabilitado.replaceWith(nuevoBtn);

        mostrarToast('info', 'Documento Creado', 'Ahora puedes enviarlo a firmas.');
    }
}

// En el handler de guardar sección:
const data = await response.json();
if (data.success) {
    // Si se creó el documento en esta operación, actualizar botón
    if (data.id_documento && !idDocumentoActual) {
        actualizarBotonFirmas(data.id_documento);
    } else if (data.id_documento) {
        idDocumentoActual = data.id_documento;
    }
}
```

**Archivos modificados:**

| Archivo | Líneas | Cambio |
| ------- | ------ | ------ |
| `DocumentosSSTController.php` | 1156-1178 | Retornar `id_documento` en respuesta JSON |
| `generar_con_ia.php` | 439-468 | Variable `idDocumentoActual` y función `actualizarBotonFirmas()` |
| `generar_con_ia.php` | 686-691 | Llamar `actualizarBotonFirmas()` en handler de guardar |
| `generar_con_ia.php` | 827-832 | Llamar `actualizarBotonFirmas()` en handler de guardar todo |

**Regla de oro:**

> **Si una vista permite crear documentos vía AJAX, el backend DEBE retornar el `id_documento` y el frontend DEBE actualizar dinámicamente cualquier UI que dependa de ese ID.**

### 11.9 Checklist actualizado para vistas con creación AJAX

Cuando implementes una vista que permita crear documentos vía AJAX:

- [ ] Backend retorna `id_documento` en respuesta JSON de `guardarSeccion`
- [ ] Frontend tiene variable JS para trackear `idDocumentoActual`
- [ ] Frontend tiene función para actualizar botón de firmas dinámicamente
- [ ] Handler de guardar llama a la función de actualización cuando `id_documento` aparece
- [ ] Handler de "Guardar Todo" también llama a la función de actualización
