# Troubleshooting: Marco Legal IA No Incluye Todas las Normas

## 📅 Fecha del Problema
**15/02/2026**

## 🔴 Síntoma Reportado
La sección **Marco Legal** generada por IA solo incluye 2-3 normas en lugar de las 7 normas base requeridas, faltando críticos:
- **Decreto 1072 de 2015** (Decreto Único Reglamentario del Sector Trabajo)
- **Resolución 0312 de 2019** (Estándares Mínimos del SG-SST)

**Ejemplo observado en Política Desconexión Laboral:**
- ✅ Ley 2191/2022 (presente)
- ✅ Ley 1010/2006 (presente)
- ❌ Decreto 1072/2015 (falta)
- ❌ Resolución 0312/2019 (falta)
- ❌ Código Sustantivo del Trabajo (falta)
- ❌ Constitución Art. 53 (falta)

## 🔍 Causa Raíz

### Arquitectura del Flujo IA

El sistema tiene un módulo de **Insumos IA - Pregeneración** que consulta marco normativo desde BD:

**Flujo actual:**
```
DocumentosSSTController::generarConIAReal()
    ↓
    [Línea 683-684] MarcoNormativoService::obtenerMarcoNormativo($tipo)
        ↓
        MarcoNormativoModel::getByTipoDocumento($tipo)
            ↓
            SELECT * FROM tbl_marco_normativo WHERE tipo_documento = ? AND activo = 1
    ↓
    [Línea 702] 'marco_normativo' => $marcoNormativo ?? ''
    ↓
IADocumentacionService::generarSeccion($datosIA)
    ↓
    [Línea 228-234] Si marco_normativo existe:
        $userPrompt .= "MARCO NORMATIVO VIGENTE APLICABLE (fuente verificada con búsqueda web, usar EXCLUSIVAMENTE este marco):\n";
        $userPrompt .= $marcoNormativo . "\n";
        $userPrompt .= "IMPORTANTE: Usa SOLO las normas listadas arriba. NO inventes ni agregues normas adicionales.\n";
```

### El Problema

**Conflicto de instrucciones** para la sección `marco_legal`:

1. **Prompt de la clase PHP** (PoliticaDesconexionLaboral.php línea ~220):
   ```
   "DEBES incluir OBLIGATORIAMENTE estas 7 normas (no omitir ninguna):
   1. Decreto 1072 de 2015...
   2. Resolución 0312 de 2019...
   [etc.]"
   ```

2. **IADocumentacionService** sobrescribe con:
   ```
   "Usa SOLO las normas listadas arriba [del marco_normativo BD].
   NO inventes ni agregues normas adicionales."
   ```

**Resultado:** La IA recibe instrucciones CONTRADICTORIAS:
- Prompt: "Incluye estas 7 normas obligatorias"
- Sistema: "Usa SOLO las normas de BD, no agregues otras"

La IA prioriza la instrucción del sistema (más autoritativa) e ignora el prompt.

### Por Qué Pasa Esto

La tabla `tbl_marco_normativo` almacena marco normativo **pregenerado** para reutilizar en OTRAS secciones (introducción, alcance, etc.). Esto tiene sentido para contexto general.

**PERO** para la sección `marco_legal`:
- **NO debería usar marco_normativo de BD** porque esa sección ES donde se GENERA el marco normativo completo
- Pasar un marco viejo/incompleto desde BD bloquea la generación correcta

## 💡 Solución Propuesta

### Principio Arquitectónico
```
marco_legal → GENERA marco normativo
otras secciones → USAN marco normativo (como contexto)
```

### Implementación

**Modificar DocumentosSSTController::generarConIAReal()** para excluir marco_normativo cuando la sección es `marco_legal`:

```php
// INSUMOS IA - Pregeneración: obtener marco normativo desde BD
// EXCEPTO para la sección marco_legal (que ES el marco que se está generando)
$marcoNormativo = null;
if ($seccion !== 'marco_legal') {
    $marcoService = new MarcoNormativoService();
    $marcoNormativo = $marcoService->obtenerMarcoNormativo($tipoDocumento);
}
```

**Archivo:** `app/Controllers/DocumentosSSTController.php`
**Líneas:** 682-684

### Beneficios

1. ✅ **Marco Legal** se genera limpio según prompt de la clase PHP
2. ✅ **Otras secciones** siguen usando marco_normativo como contexto
3. ✅ No rompe funcionalidad existente
4. ✅ Mantiene separación de responsabilidades

## 📝 Flujo Corregido

```
Si sección == 'marco_legal':
    ↓
    NO consultar tbl_marco_normativo
    ↓
    Pasar solo prompt de la clase PHP
    ↓
    IA genera las 7 normas base + búsqueda complementaria

Si sección != 'marco_legal':
    ↓
    SÍ consultar tbl_marco_normativo
    ↓
    Pasar marco como contexto adicional
    ↓
    IA usa marco para contextualizar la sección
```

## 📊 Análisis de Impacto

### Documentos Afectados (7 políticas del numeral 2.1.1)

| Tipo Documento | Marco Legal en Prompt | Tiene en tbl_marco_normativo? | Estado |
|----------------|----------------------|------------------------------|---------|
| `politica_sst_general` | Sí (7+ normas) | ? | ⚠️ Verificar |
| `politica_alcohol_drogas` | Sí (7+ normas) | ? | ⚠️ Verificar |
| `politica_acoso_laboral` | Sí (7+ normas) | ? | ⚠️ Verificar |
| `politica_violencias_genero` | Sí (7+ normas) | ? | ⚠️ Verificar |
| `politica_discriminacion` | Sí (7+ normas) | ? | ⚠️ Verificar |
| `politica_desconexion_laboral` | ✅ Sí (7 normas base) | ❌ No | ❌ FALLA |
| `politica_prevencion_emergencias` | Sí (7+ normas) | ? | ⚠️ Verificar |

### Otros Documentos SST

Cualquier documento que tenga sección `marco_legal` o `marco_normativo` puede tener este problema si:
1. Tiene registro en `tbl_marco_normativo` desactualizado/incompleto
2. El prompt pide normas específicas
3. Las normas de BD no coinciden con las del prompt

## 🔧 Plan de Acción

### Fase 1: Fix del Controller ⏳
- [ ] Modificar `DocumentosSSTController::generarConIAReal()` líneas 682-684
- [ ] Agregar condicional: `if ($seccion !== 'marco_legal')`
- [ ] Documentar el cambio en comentarios

### Fase 2: Verificación
- [ ] Regenerar Marco Legal de `politica_desconexion_laboral`
- [ ] Confirmar que incluye las 7 normas base
- [ ] Verificar que otras secciones siguen funcionando

### Fase 3: Validación Transversal
- [ ] Revisar prompts de las otras 6 políticas (2.1.1)
- [ ] Estandarizar lista de normas base SST
- [ ] Verificar registros en `tbl_marco_normativo`

### Fase 4: Documentación
- [ ] Actualizar `SISTEMA_PROMPTS_IA.md` con esta regla
- [ ] Agregar a checklist de nuevo documento
- [ ] Documentar en memoria del proyecto

## 🛡️ Prevención Futura

### Reglas de Diseño

1. **Sección `marco_legal`:**
   - SIEMPRE genera contenido desde prompt de clase PHP
   - NUNCA usar marco_normativo de BD como restricción
   - BD solo se usa DESPUÉS de generar (para almacenar resultado)

2. **Otras secciones:**
   - PUEDEN usar marco_normativo como contexto adicional
   - NO debe ser restrictivo (no usar "SOLO estas normas")
   - Debe complementar el prompt, no bloquearlo

3. **Prompt IADocumentacionService:**
   - Reformular líneas 228-234 para que marco_normativo sea GUÍA, no RESTRICCIÓN
   - En lugar de "Usa SOLO estas normas", usar "Prioriza estas normas verificadas, pero incluye otras relevantes si las menciona el prompt"

### Checklist Nuevo Documento con Marco Legal

- [ ] Definir 7+ normas base en prompt
- [ ] Verificar que NO haya conflicto con `tbl_marco_normativo`
- [ ] Probar generación 2-3 veces para validar consistencia
- [ ] Revisar que incluya TODAS las normas del prompt

## 📚 Referencias

- **Controller:** `app/Controllers/DocumentosSSTController.php` (líneas 666-721)
- **Servicio IA:** `app/Services/IADocumentacionService.php` (líneas 228-234)
- **Servicio Marco:** `app/Services/MarcoNormativoService.php`
- **Modelo Marco:** `app/Models/MarcoNormativoModel.php`
- **Tabla BD:** `tbl_marco_normativo`
- **Clase Ejemplo:** `app/Libraries/DocumentosSSTTypes/PoliticaDesconexionLaboral.php`

## 📖 Lecciones Aprendidas

1. **Arquitectura dual puede causar conflictos:** BD como fuente de verdad vs Prompts como especificación
2. **Instrucciones del sistema prevalecen sobre prompts:** La IA prioriza instrucciones autoritativas ("SOLO", "NO agregues")
3. **Separación de responsabilidades:** Una sección que GENERA datos no debe recibir esos datos como restricción de entrada
4. **Testing crítico:** Validar TODAS las normas del prompt, no solo que "genere algo"
