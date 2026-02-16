# Marco Normativo - Insumos IA Pregeneración

**Módulo:** Generación de documentos SST con IA
**Estado:** ✅ Implementado y en producción
**Última actualización:** 2026-02-15

---

## 📚 Documentación Disponible

### 1. [INSUMOS_IA_PREGENERACION.md](INSUMOS_IA_PREGENERACION.md) ⭐ **PRINCIPAL**
**Lectura obligatoria** - Documento maestro del módulo

Contiene:
- Problema y solución
- Las 4 opciones de actualización del marco normativo
- Estructura de tabla BD (con versionamiento)
- Endpoints API completos
- Integración con OpenAI Responses API
- Inyección en el prompt de generación IA
- UI: Panel sidebar y modal de edición
- Mejoras UX implementadas (SweetAlert, contexto IA, orden botones)
- Scripts de migración ejecutados
- Pruebas realizadas
- Próximos pasos pendientes

### 2. [INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md](INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md) ⭐ **NUEVO**
**Integración del marco normativo en SweetAlert de verificación**

Documenta la implementación completa de:
- ✅ Marco normativo en SweetAlert "Generar TODO con IA" (dos alertas secuenciales)
- ✅ Marco normativo en SweetAlert de secciones individuales
- ✅ Backend: campo `texto_completo` en endpoint `previsualizarDatos()`
- ✅ Verificación de inyección en prompts IA (`IADocumentacionService.php`)
- ✅ Flujo completo de datos desde BD hasta GPT-4o-mini
- ✅ Pruebas realizadas (2,747 caracteres, 7 normas)
- ✅ Confirmación: cada sección SÍ usa elementos ciertos de BD

**Fecha:** 2026-02-15

### 3. [VALORES_HARDCODEADOS_MARCO_NORMATIVO.md](VALORES_HARDCODEADOS_MARCO_NORMATIVO.md)
Análisis técnico de valores hardcodeados vs dinámicos

Identifica y documenta:
- ✅ **Año en placeholder:** Ya corregido a dinámico
- 🔴 **Vigencia 90 días:** Pendiente hacer dinámico en UI
- 🟡 **Timeout 90 segundos:** Aceptable, pero mejorable
- 🟢 **Modelo GPT-4o:** Constante técnica (correcto)
- 🟢 **Temperatura 0.3:** Diseño intencional (correcto)
- 🟢 **Diccionario nombres:** Catálogo estático con fallback

Incluye plan de acción priorizado.

---

## 🚀 Estado de Implementación

| Componente | Estado | Notas |
|------------|--------|-------|
| **Tabla BD** | ✅ Creada | Con versionamiento completo |
| **Migración historial** | ✅ Ejecutada | LOCAL + PRODUCCIÓN |
| **Modelo** | ✅ Completo | `MarcoNormativoModel.php` con historial |
| **Servicio** | ✅ Completo | `MarcoNormativoService.php` + Responses API |
| **Endpoints** | ✅ Completos | 3 endpoints AJAX funcionando |
| **UI Sidebar** | ✅ Completo | Panel colapsable con 4 opciones |
| **Modal Edición** | ✅ Completo | Textarea + contexto IA + botones |
| **Inyección Prompt** | ✅ Completo | `IADocumentacionService.php` integrado |
| **Versionamiento** | ✅ Implementado | Auditoría completa de cambios |
| **Contexto IA** | ✅ Implementado | Textarea personalizable |
| **Año dinámico** | ✅ Corregido | `<?= date('Y') ?>` |
| **SweetAlert verificación** | ✅ Completo | Marco normativo en 2 SweetAlerts (individual + batch) |
| **Texto completo** | ✅ Implementado | Campo `texto_completo` en endpoint |
| **Verificación inyección** | ✅ Confirmado | Código revisado, flujo completo documentado |

---

## 🔧 Cambios Críticos Realizados

### 1. Versionamiento Completo (2026-02-15)

**Antes:**
```sql
UNIQUE KEY idx_tipo_documento (tipo_documento)
```
Solo 1 versión por tipo → Sobreescritura

**Después:**
```sql
INDEX idx_tipo_activo (tipo_documento, activo, fecha_actualizacion DESC)
```
Múltiples versiones → Historial completo

**Impacto:** Ahora se guarda auditoría de todos los cambios del marco normativo.

### 2. UX Mejorado (2026-02-15)

- ✅ **Orden botones:** [Consultar IA] primero, [Ver/Editar] segundo
- ✅ **SweetAlert educativo:** Explica el proceso antes de consultar IA
- ✅ **Toast mejorado:** Mensaje más informativo al guardar
- ✅ **Contexto IA:** Textarea para personalizar la consulta a GPT-4o

### 3. Año Dinámico (2026-02-15)

- ✅ **Placeholder actualizado:** De "2023-2024" hardcodeado → `<?= date('Y') ?>` dinámico

---

## 📖 Flujo de Uso

### Opción 1: Auto-actualización
1. Checkbox "Auto si >90 días" activo
2. Al cargar página, sistema verifica vigencia
3. Si venció, consulta automática a GPT-4o + web search
4. Guarda en BD con `metodo = 'automatico'`

### Opción 2: Consulta manual (botón)
1. Clic en "Consultar IA" (sidebar o modal)
2. SweetAlert explica el proceso
3. Usuario confirma
4. Consulta a GPT-4o + web search (30-90 seg)
5. Guarda en BD con `metodo = 'boton'`

### Opción 3: Confirmación al generar
1. Checkbox "Preguntar al generar" activo
2. Usuario hace clic en "Generar con IA"
3. Sistema pregunta si desea actualizar marco
4. Si acepta, ejecuta consulta IA
5. Guarda con `metodo = 'confirmacion'`

### Opción 4: Edición manual
1. Clic en "Ver/Editar"
2. Modal muestra textarea con contenido actual
3. Opcional: Agregar contexto IA para personalizar
4. Opcional: Consultar IA desde el modal
5. Editar manualmente el texto
6. Guardar con `metodo = 'manual'`

---

## 🔍 Verificación de Estado Actual

### BD - Local
```sql
-- Verificar índice compuesto (debe existir)
SHOW INDEX FROM tbl_marco_normativo WHERE Key_name = 'idx_tipo_activo';

-- Verificar UNIQUE constraint (NO debe existir)
SHOW INDEX FROM tbl_marco_normativo WHERE Key_name = 'idx_tipo_documento';

-- Ver versiones de politica_sst_general
SELECT id, LEFT(marco_normativo_texto, 50) AS preview,
       fecha_actualizacion, metodo_actualizacion, activo
FROM tbl_marco_normativo
WHERE tipo_documento = 'politica_sst_general'
ORDER BY fecha_actualizacion DESC;
```

### Código - Verificaciones
```bash
# Año dinámico en placeholder
grep "date('Y')" app/Views/documentos_sst/generar_con_ia.php

# Método guardar() usa INSERT (no UPDATE)
grep -A 10 "function guardar" app/Models/MarcoNormativoModel.php | grep "insert"

# Inyección en prompt
grep "marco_normativo" app/Services/IADocumentacionService.php
```

---

## ⚠️ Pendientes Identificados

### 🔴 Prioridad Alta
1. **Hacer vigencia_dias dinámico en UI**
   - Actualmente hardcodeado "90 días" en labels/mensajes
   - Debería leer el valor real de BD y mostrarlo

### 🟡 Prioridad Media
2. **Dashboard de marco normativo**
   - Vista de todos los tipos con marco normativo
   - Última actualización, vigencia, método
   - Acceso rápido a editar cada uno

3. **Historial visual en modal**
   - Mostrar timeline de versiones
   - Ver diferencias entre versiones (diff)
   - Restaurar versión anterior

---

## 🧪 Casos de Prueba Validados

✅ **Consulta IA (opción 2):** `politica_sst_general` - Marco normativo obtenido de GPT-4o

✅ **Edición manual (opción 4):** Política de Acoso Sexual - Texto modificado y guardado

✅ **Versionamiento:** Múltiples versiones guardadas con `activo = 0/1`

✅ **Inyección en prompt:** Marco normativo presente al generar con IA

✅ **Contexto IA:** Textarea funcional con placeholder dinámico año 2026

✅ **SweetAlert "Generar TODO":** `politica_alcohol_drogas` - Dos alertas secuenciales mostrando marco normativo completo (2,747 caracteres, 7 normas) + resumen

✅ **Verificación de código:** `IADocumentacionService.php` línea 232 inyecta marco normativo, `DocumentosSSTController.php` línea 666 obtiene de BD

✅ **Flujo completo documentado:** Desde `tbl_marco_normativo` hasta GPT-4o-mini, confirmado con elementos ciertos

---

## 📞 Soporte

**Preguntas sobre el módulo:** Ver [`INSUMOS_IA_PREGENERACION.md`](INSUMOS_IA_PREGENERACION.md)

**Valores hardcodeados:** Ver [`VALORES_HARDCODEADOS_MARCO_NORMATIVO.md`](VALORES_HARDCODEADOS_MARCO_NORMATIVO.md)

**Problemas técnicos:** Revisar logs de CodeIgniter en `writable/logs/`
