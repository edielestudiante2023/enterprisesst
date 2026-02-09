# Impacto de la Activacion del Delegado SST

## Resumen Ejecutivo

Cuando se activa la opcion **"Requiere Delegado SST"** en el Contexto del cliente (`/contexto/{id_cliente}`), se modifica el comportamiento del sistema en multiples areas:

1. **Flujo de firmas electronicas** - El Delegado firma ANTES del Representante Legal
2. **Visualizacion en documentos** - Aparece una columna/fila adicional de firma
3. **Notificaciones por email** - Se envian correos secuenciales
4. **Estructura de firmantes** - Pasa de 2 a 3 firmantes en documentos genericos

---

## 1. Configuracion en Contexto SST

### Ubicacion
**Vista:** [formulario.php](../app/Views/contexto/formulario.php) (lineas 500-550)

**URL:** `/contexto/{id_cliente}`

### Campos de la Seccion "Firmantes de Documentos"

| Campo | Columna BD | Descripcion |
|-------|-----------|-------------|
| Toggle "Requiere Delegado SST" | `requiere_delegado_sst` | Boolean que activa el flujo |
| Nombre Completo | `delegado_sst_nombre` | Nombre para mostrar en documentos |
| Cargo | `delegado_sst_cargo` | Default: "Delegado SST" |
| Email | `delegado_sst_email` | **Requerido** para firma electronica |
| Cedula | `delegado_sst_cedula` | Documento de identidad |

### Comportamiento Visual del Formulario

```javascript
// Cuando se activa el toggle, se muestra la seccion de datos del Delegado
$('#requiereDelegadoSst').change(function() {
    if (this.checked) {
        $('#seccionDelegadoSst').removeClass('d-none');
        $('#firmaFinalLabel').text('Firma Final'); // Rep. Legal firma al final
    } else {
        $('#seccionDelegadoSst').addClass('d-none');
        $('#firmaFinalLabel').text('Firma Unica'); // Rep. Legal firma solo
    }
});
```

---

## 2. Impacto en el Flujo de Firmas Electronicas

### Controlador Principal
**Archivo:** [FirmaElectronicaController.php](../app/Controllers/FirmaElectronicaController.php)

### 2.1 Creacion de Solicitudes de Firma (lineas 79-167)

**SIN Delegado SST:**
```
1. Representante Legal → estado: 'pendiente' → Recibe email inmediato
```

**CON Delegado SST:**
```
1. Delegado SST → estado: 'pendiente' → Recibe email inmediato
2. Representante Legal → estado: 'esperando' → NO recibe email aun
```

### 2.2 Orden de Firma Secuencial

| Orden | Firmante | Estado Inicial | Accion |
|-------|----------|----------------|--------|
| 1 | Delegado SST | `pendiente` | Recibe email de firma |
| 2 | Representante Legal | `esperando` | Espera a que Delegado firme |

### 2.3 Activacion del Siguiente Firmante (lineas 707-724)

Cuando el Delegado completa su firma:

```php
// Verificar si hay siguiente firmante en la cadena
$siguienteFirmante = $this->firmaModel->getSiguienteFirmante($solicitud['id_documento']);

if ($siguienteFirmante) {
    // Activar siguiente firmante (Rep. Legal)
    $this->firmaModel->update($siguienteFirmante['id_solicitud'], [
        'estado' => 'pendiente'  // Cambia de 'esperando' a 'pendiente'
    ]);

    // Enviar correo al Representante Legal
    $this->enviarCorreoFirma($siguienteFirmante, $documento);
}
```

### 2.4 Validacion de Datos Completos

**Archivo:** [solicitar.php](../app/Views/firma/solicitar.php) (linea 169)

Para que se pueda crear la solicitud de firma, el Delegado debe tener **nombre Y email**:

```php
$tieneSegundoFirmante = !empty(trim($contexto['delegado_sst_nombre'] ?? ''))
                     && !empty(trim($contexto['delegado_sst_email'] ?? ''));
```

Si faltan datos, el sistema muestra advertencia y no permite enviar.

---

## 3. Impacto en Documentos - Seccion de Firmas

### 3.1 Logica de Determinacion de Firmantes

**Archivo:** [FirmanteService.php](../app/Services/FirmanteService.php) (lineas 269-299)

```php
// Si estandares > 10 o requiere delegado: agregar revisor
if ($estandares > 10 || $requiereDelegado) {
    if ($requiereDelegado) {
        // DELEGADO SST como revisor
        $firmantes[] = [
            'tipo' => 'delegado_sst',
            'columna_encabezado' => 'Reviso / Delegado SST',
            'nombre' => $contexto['delegado_sst_nombre'] ?? '',
            'cargo' => $contexto['delegado_sst_cargo'] ?? 'Delegado SST',
            // ...
        ];
    } else {
        // Vigia o COPASST segun estandares
        $cargoRevisor = $estandares <= 21 ? 'Vigia de SST' : 'COPASST';
        // ...
    }
}
```

### 3.2 Regla de Prioridad (IMPORTANTE)

> **Si `requiere_delegado_sst = true`, el Delegado SST SIEMPRE aparece en las firmas, independientemente de la configuracion del tipo de documento.**

Orden de prioridad:
```
1. ¿requiere_delegado_sst = true? → 3 FIRMANTES (Elaboro + Delegado + Aprobo)
2. ¿firmantesDefinidos tiene valores? → Usar array definido
3. ¿estandares <= 10 y NO delegado? → 2 FIRMANTES
4. DEFAULT → 3 FIRMANTES (con Vigia/COPASST)
```

---

## 4. Vistas Afectadas por el Delegado SST

### 4.1 Plantillas de Documentos (Vista Previa HTML)

| Archivo | Lineas Clave | Impacto |
|---------|--------------|---------|
| [documento_generico.php](../app/Views/documentos_sst/documento_generico.php) | 623-797 | Muestra firma Delegado en columna central |
| [programa_capacitacion.php](../app/Views/documentos_sst/programa_capacitacion.php) | 638-812 | Firma Delegado como "Reviso" |
| [procedimiento_control_documental.php](../app/Views/documentos_sst/procedimiento_control_documental.php) | 804-1153 | Prioriza Delegado sobre config de documento |
| [asignacion_responsable.php](../app/Views/documentos_sst/asignacion_responsable.php) | 325-476 | Firma Delegado en 3 columnas |
| [responsabilidades_responsable_sst.php](../app/Views/documentos_sst/responsabilidades_responsable_sst.php) | 276-279 | Solo si no es solo_firma_consultor |
| [responsabilidades_rep_legal.php](../app/Views/documentos_sst/responsabilidades_rep_legal.php) | 193, 328 | Delegado como segundo firmante |
| [presupuesto_preview.php](../app/Views/documentos_sst/presupuesto_preview.php) | 324, 369 | Firma Delegado visible |
| [presupuesto_sst.php](../app/Views/documentos_sst/presupuesto_sst.php) | 475-483 | Estado de firma Delegado |

### 4.2 Plantillas PDF

| Archivo | Lineas Clave | Impacto |
|---------|--------------|---------|
| [pdf_template.php](../app/Views/documentos_sst/pdf_template.php) | 527-1049 | Tabla de 3 columnas para firmas |
| [pdf_template_generico.php](../app/Views/documentos_sst/pdf_template_generico.php) | 395-401 | Switch case para tipo delegado_sst |
| [presupuesto_pdf.php](../app/Views/documentos_sst/presupuesto_pdf.php) | 416-501 | Firma Delegado en PDF |

### 4.3 Plantillas Word

| Archivo | Lineas Clave | Impacto |
|---------|--------------|---------|
| [word_template.php](../app/Views/documentos_sst/word_template.php) | 314-347 | Tabla de firmas con 3 celdas |

### 4.4 Vistas de Firma Electronica

| Archivo | Lineas Clave | Impacto |
|---------|--------------|---------|
| [solicitar.php](../app/Views/firma/solicitar.php) | 169-216 | Card con datos del Delegado |
| [firmar.php](../app/Views/firma/firmar.php) | 88 | Label "Delegado SST" |
| [estado.php](../app/Views/firma/estado.php) | 208 | Estado de firma del Delegado |
| [verificacion.php](../app/Views/firma/verificacion.php) | 125 | Verificacion publica |
| [certificado_pdf.php](../app/Views/firma/certificado_pdf.php) | 210 | Certificado de verificacion |
| [audit_log.php](../app/Views/firma/audit_log.php) | 57 | Log de auditoria |

---

## 5. Estructura de Tabla de Firmas

### 5.1 SIN Delegado SST (2 Firmantes)

```
┌─────────────────────────┬─────────────────────────┐
│     ELABORO             │     APROBO              │
│     Consultor SST       │     Representante Legal │
├─────────────────────────┼─────────────────────────┤
│ Nombre: Juan Perez      │ Nombre: Maria Garcia    │
│ Cargo: Consultor SST    │ Cargo: Rep. Legal       │
│ Licencia: 12345         │                         │
├─────────────────────────┼─────────────────────────┤
│       [FIRMA]           │       [FIRMA]           │
└─────────────────────────┴─────────────────────────┘
```

### 5.2 CON Delegado SST (3 Firmantes)

```
┌─────────────────┬─────────────────┬─────────────────┐
│    ELABORO      │    REVISO       │    APROBO       │
│  Consultor SST  │  Delegado SST   │  Rep. Legal     │
├─────────────────┼─────────────────┼─────────────────┤
│ Juan Perez      │ Pedro Lopez     │ Maria Garcia    │
│ Consultor SST   │ Delegado SST    │ Rep. Legal      │
│ Lic: 12345      │                 │                 │
├─────────────────┼─────────────────┼─────────────────┤
│    [FIRMA]      │    [FIRMA]      │    [FIRMA]      │
└─────────────────┴─────────────────┴─────────────────┘
```

---

## 6. Flujo Completo de Firma con Delegado SST

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO DE FIRMA ELECTRONICA                    │
│                      (Con Delegado SST)                          │
└─────────────────────────────────────────────────────────────────┘

     Consultor                    Sistema                    Firmantes
         │                           │                           │
         │  Clic "Enviar a Firmas"   │                           │
         │ ─────────────────────────►│                           │
         │                           │                           │
         │                           │ Crea solicitud Delegado   │
         │                           │ (orden=1, estado=pendiente)│
         │                           │                           │
         │                           │ Crea solicitud Rep.Legal  │
         │                           │ (orden=2, estado=esperando)│
         │                           │                           │
         │                           │ Envia email a Delegado    │
         │                           │ ─────────────────────────►│
         │                           │                           │
         │                           │      Delegado abre link   │
         │                           │◄─────────────────────────│
         │                           │                           │
         │                           │      Delegado firma       │
         │                           │◄─────────────────────────│
         │                           │                           │
         │                           │ Activa Rep.Legal          │
         │                           │ (estado → 'pendiente')    │
         │                           │                           │
         │                           │ Envia email a Rep.Legal   │
         │                           │ ─────────────────────────►│
         │                           │                           │
         │                           │    Rep.Legal abre link    │
         │                           │◄─────────────────────────│
         │                           │                           │
         │                           │    Rep.Legal firma        │
         │                           │◄─────────────────────────│
         │                           │                           │
         │   Email: Firmas completas │ Documento → 'firmado'     │
         │◄─────────────────────────│                           │
         │                           │                           │
         │                           │ Genera PDF final          │
         │                           │ Publica en reportList     │
         │                           │ Crea version aprobada     │
         │                           │                           │
         ▼                           ▼                           ▼
```

---

## 7. Base de Datos - Tablas Afectadas

### 7.1 tbl_cliente_contexto_sst

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| `requiere_delegado_sst` | TINYINT(1) | 0=No, 1=Si |
| `delegado_sst_nombre` | VARCHAR(200) | Nombre completo |
| `delegado_sst_cargo` | VARCHAR(100) | Default: 'Delegado SST' |
| `delegado_sst_email` | VARCHAR(150) | Email para firma electronica |
| `delegado_sst_cedula` | VARCHAR(20) | Documento de identidad |

### 7.2 tbl_doc_firma_solicitudes

| Columna | Valor para Delegado |
|---------|---------------------|
| `firmante_tipo` | 'delegado_sst' |
| `orden_firma` | 1 (primero) |
| `estado` | 'pendiente' → 'firmado' |

---

## 8. Emails Enviados

### 8.1 Solicitud de Firma (Delegado)

**Asunto:** `Solicitud de Firma: {codigo} - {nombre_documento}`

**Contenido:**
- Saludo al Delegado SST
- Tipo de firmante: "Delegado SST"
- Datos del documento (codigo, version)
- Boton "Firmar Documento"
- Link de firma con token unico

### 8.2 Solicitud de Firma (Rep. Legal - Post Delegado)

El email al Representante Legal se envia **SOLO DESPUES** de que el Delegado haya firmado.

### 8.3 Notificacion Firmas Completas (Consultor)

Cuando el Rep. Legal (ultimo firmante) completa su firma:
- Se notifica al consultor asignado
- Incluye tabla con todos los firmantes y fechas

---

## 9. Casos de Uso Empresariales

### 9.1 Empresa Grande (21+ Estandares) CON Delegado

**Configuracion:**
- `estandares_aplicables` = 21 o 60
- `requiere_delegado_sst` = 1

**Firmantes:** Consultor → Delegado SST → Rep. Legal

**Uso tipico:** Empresas donde el Gerente de RRHH o Director Administrativo revisa documentos antes del Representante Legal.

### 9.2 Empresa Mediana SIN Delegado

**Configuracion:**
- `estandares_aplicables` = 21
- `requiere_delegado_sst` = 0

**Firmantes:** Consultor → Vigia SST → Rep. Legal

### 9.3 Empresa Pequena (7 Estandares)

**Configuracion:**
- `estandares_aplicables` = 7
- `requiere_delegado_sst` = 0

**Firmantes:** Consultor → Rep. Legal (solo 2)

---

## 10. Troubleshooting

### 10.1 El Delegado no aparece en la seccion de firmas

**Verificar:**
1. `requiere_delegado_sst = 1` en tbl_cliente_contexto_sst
2. Datos completos: nombre, email, cargo

### 10.2 El email no llega al Delegado

**Verificar:**
1. Email valido en `delegado_sst_email`
2. Configuracion de SendGrid
3. Logs: `writable/logs/log-*.php`

### 10.3 El Rep. Legal recibe email antes que el Delegado firme

**Esto NO deberia ocurrir.** El estado del Rep. Legal es `esperando` hasta que el Delegado firme.

### 10.4 La firma del Delegado no aparece en el PDF

**Verificar:**
1. La solicitud tiene `estado = 'firmado'`
2. Existe registro en `tbl_doc_firma_evidencias`
3. El campo `firma_imagen` contiene el base64

---

## 11. Archivos de Referencia Rapida

| Funcionalidad | Archivo Principal |
|---------------|-------------------|
| Configuracion UI | [formulario.php](../app/Views/contexto/formulario.php) |
| Logica de firmantes | [FirmanteService.php](../app/Services/FirmanteService.php) |
| Flujo de firmas | [FirmaElectronicaController.php](../app/Controllers/FirmaElectronicaController.php) |
| Vista firma publica | [firmar.php](../app/Views/firma/firmar.php) |
| Template PDF generico | [pdf_template.php](../app/Views/documentos_sst/pdf_template.php) |
| Template Word | [word_template.php](../app/Views/documentos_sst/word_template.php) |

---

**Documento creado:** 2026-02-03
**Ultima actualizacion:** 2026-02-03
