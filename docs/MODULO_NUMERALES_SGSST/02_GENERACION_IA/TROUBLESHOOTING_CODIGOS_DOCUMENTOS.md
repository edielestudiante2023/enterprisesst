# Troubleshooting: Códigos de Documentos Incorrectos (DOC-GEN-001)

## 📅 Fecha del Problema
**15/02/2026**

## 🔴 Síntoma Reportado
Documentos generados muestran código genérico "DOC-GEN-001" en lugar de códigos específicos:
- Política de Discriminación: **DOC-GEN-001** (debería ser POL-DIS-001)
- Política de Violencias de Género: **DOC-GEN-001** (debería ser POL-VGE-001)

## 🔍 Causa Raíz

### Arquitectura Dual de Códigos
El sistema tiene DOS fuentes para obtener códigos de documentos:

1. **Factory (Nueva arquitectura)** - `app/Libraries/DocumentosSSTTypes/`
   - Clases PHP con método `getCodigoDocumento()`
   - Ejemplo: `PoliticaDiscriminacion::getCodigoDocumento()` → `'POL-DIS'`

2. **Tabla BD (Legacy)** - `tbl_doc_plantillas.codigo_sugerido`
   - Tabla antigua con códigos hardcodeados
   - Se consulta en `DocumentosSSTController::obtenerCodigoPlantilla()`

### Flujo de Generación de Código

**Archivo:** `app/Controllers/DocumentosSSTController.php`

**Líneas críticas:**
```php
// Línea 1468 - Llamada al generar nuevo documento
$codigoDocumento = $this->generarCodigoDocumento($idCliente, $tipo);

// Línea 110-117 - Busca en tabla BD (NO usa Factory)
protected function obtenerCodigoPlantilla(string $tipoDocumento): ?string
{
    $plantilla = $this->db->table('tbl_doc_plantillas')
        ->select('codigo_sugerido')
        ->where('tipo_documento', $tipoDocumento)
        ->where('activo', 1)
        ->get()
        ->getRow();

    return $plantilla?->codigo_sugerido;
}

// Línea 127-145 - Genera código
protected function generarCodigoDocumento(int $idCliente, string $tipoDocumento): string
{
    $codigoBase = $this->obtenerCodigoPlantilla($tipoDocumento);

    if (!$codigoBase) {
        // ⚠️ FALLBACK GENÉRICO cuando no encuentra en BD
        log_message('error', "Tipo de documento '$tipoDocumento' no tiene plantilla configurada");
        $codigoBase = 'DOC-GEN';  // → Genera "DOC-GEN-001"
    }

    $consecutivo = $this->db->table('tbl_documentos_sst')
        ->where('id_cliente', $idCliente)
        ->where('tipo_documento', $tipoDocumento)
        ->countAllResults() + 1;

    return $codigoBase . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
}
```

### El Problema
**El Factory NO se consulta** para obtener códigos. El sistema:
1. ✅ Busca en `tbl_doc_plantillas`
2. ❌ Si no encuentra → usa fallback `'DOC-GEN'`
3. ❌ NUNCA consulta `DocumentoSSTFactory::crear($tipo)->getCodigoDocumento()`

## 📊 Análisis de Discrepancias

### Políticas del Numeral 2.1.1

| tipo_documento | Factory (PHP) | tbl_doc_plantillas | tbl_doc_tipo_configuracion | Estado |
|----------------|--------------|-------------------|---------------------------|---------|
| `politica_alcohol_drogas` | POL-ALC | **POL-ADT** | ✅ Existe | ⚠️ DISCREPANCIA |
| `politica_acoso_laboral` | POL-ACO | POL-ACO | ✅ Existe | ✅ OK |
| `politica_violencias_genero` | POL-VGE | ❌ **NO EXISTE** | ✅ Existe | ❌ FALTA |
| `politica_discriminacion` | POL-DIS | ❌ **NO EXISTE** | ✅ Existe | ❌ FALTA |
| `politica_desconexion_laboral` | POL-DES | ❌ **NO EXISTE** | ✅ Existe | ❌ FALTA |

### Documentos Afectados en BD
Documentos generados con código incorrecto "DOC-GEN-001":
- `politica_violencias_genero` → debería ser `POL-VGE-001`
- `politica_discriminacion` → debería ser `POL-DIS-001`

## 💡 Solución Propuesta

### Opción A: Agregar códigos a `tbl_doc_plantillas` (Quick Fix)
✅ Rápido
❌ Mantiene arquitectura dual
❌ No aprovecha Factory

### Opción B: Refactorizar para usar Factory primero (Arquitectura Correcta)
✅ Usa nueva arquitectura (Factory)
✅ Mantiene compatibilidad con tabla legacy
✅ Escalable y mantenible

**Elegimos Opción B** + Corrección de BD

### Implementación

#### 1. Modificar `generarCodigoDocumento()` para usar Factory
```php
protected function generarCodigoDocumento(int $idCliente, string $tipoDocumento): string
{
    $codigoBase = null;

    // PRIORIDAD 1: Intentar obtener desde Factory (nueva arquitectura)
    try {
        $handler = DocumentoSSTFactory::crear($tipoDocumento);
        if ($handler && method_exists($handler, 'getCodigoDocumento')) {
            $codigoBase = $handler->getCodigoDocumento();
        }
    } catch (\Exception $e) {
        log_message('info', "Factory no disponible para '$tipoDocumento': " . $e->getMessage());
    }

    // PRIORIDAD 2: Fallback a tabla legacy (compatibilidad)
    if (!$codigoBase) {
        $codigoBase = $this->obtenerCodigoPlantilla($tipoDocumento);
    }

    // PRIORIDAD 3: Fallback genérico (última opción)
    if (!$codigoBase) {
        log_message('error', "Tipo de documento '$tipoDocumento' sin código en Factory ni BD");
        $codigoBase = 'DOC-GEN';
    }

    // Generar consecutivo
    $consecutivo = $this->db->table('tbl_documentos_sst')
        ->where('id_cliente', $idCliente)
        ->where('tipo_documento', $tipoDocumento)
        ->countAllResults() + 1;

    return $codigoBase . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
}
```

#### 2. Agregar Códigos Faltantes a `tbl_doc_plantillas` (Compatibilidad)
Para documentos legacy que no tienen Factory:
```sql
INSERT INTO tbl_doc_plantillas
(tipo_documento, codigo_sugerido, activo)
VALUES
('politica_violencias_genero', 'POL-VGE', 1),
('politica_discriminacion', 'POL-DIS', 1),
('politica_desconexion_laboral', 'POL-DES', 1)
ON DUPLICATE KEY UPDATE
    codigo_sugerido = VALUES(codigo_sugerido),
    activo = 1;
```

#### 3. Corregir Documentos Existentes en BD
```sql
-- Corregir política de violencias de género
UPDATE tbl_documentos_sst
SET codigo = 'POL-VGE-001'
WHERE tipo_documento = 'politica_violencias_genero'
  AND codigo = 'DOC-GEN-001';

-- Corregir política de discriminación
UPDATE tbl_documentos_sst
SET codigo = 'POL-DIS-001'
WHERE tipo_documento = 'politica_discriminacion'
  AND codigo = 'DOC-GEN-001';

-- Corregir política de alcohol y drogas (discrepancia)
UPDATE tbl_documentos_sst
SET codigo = 'POL-ALC-001'
WHERE tipo_documento = 'politica_alcohol_drogas'
  AND codigo = 'POL-ADT-001';
```

#### 4. Sincronizar `tbl_doc_plantillas` con Factory
```sql
-- Actualizar código de alcohol_drogas para que coincida con Factory
UPDATE tbl_doc_plantillas
SET codigo_sugerido = 'POL-ALC'
WHERE tipo_documento = 'politica_alcohol_drogas';
```

## 📝 Plan de Acción

### Fase 1: Documentación ✅
- [x] Documentar causa raíz
- [x] Identificar discrepancias
- [x] Proponer solución

### Fase 2: Implementación
- [ ] Modificar `DocumentosSSTController::generarCodigoDocumento()` para usar Factory
- [ ] Crear script SQL de corrección: `app/SQL/corregir_codigos_documentos.php`
- [ ] Ejecutar script en LOCAL
- [ ] Ejecutar script en PRODUCCIÓN

### Fase 3: Verificación
- [ ] Generar documento de prueba con política nueva
- [ ] Verificar que use código del Factory
- [ ] Verificar documentos corregidos en BD

## 🔧 Prevención Futura

1. **Regla de Oro:** Toda clase del Factory DEBE tener `getCodigoDocumento()`
2. **Checklist nuevo documento:** Agregar código a Factory (NO a tabla BD)
3. **Deprecar:** `tbl_doc_plantillas.codigo_sugerido` gradualmente
4. **Test:** Validar que Factory retorna código antes de generar documento

## 📚 Referencias
- Factory: `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`
- Controller: `app/Controllers/DocumentosSSTController.php` (líneas 108-145)
- Tabla legacy: `tbl_doc_plantillas`
- Tabla config: `tbl_doc_tipo_configuracion`
