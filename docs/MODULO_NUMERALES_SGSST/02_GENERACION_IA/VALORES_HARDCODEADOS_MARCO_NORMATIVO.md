# Valores Hardcodeados - Sistema Marco Normativo

**Fecha:** 2026-02-15
**Módulo:** Insumos IA - Pregeneración

## Resumen

Este documento identifica todos los valores hardcodeados en el sistema de marco normativo y clasifica cuáles deberían ser configurables vs constantes del diseño.

---

## 1. Vigencia del Marco Normativo

### 🔴 HARDCODEADO - Debería ser configurable

**Valor:** 90 días
**Ubicaciones:**

1. **Tabla SQL** (`crear_tbl_marco_normativo.sql:16`)
   ```sql
   vigencia_dias INT DEFAULT 90
   ```

2. **Modelo** (`MarcoNormativoService.php:65, 68`)
   ```php
   'vigente' => $dias <= ($registro['vigencia_dias'] ?? 90)
   'vigencia_dias' => $registro['vigencia_dias'] ?? 90
   ```

3. **Vista - Label checkbox** (`generar_con_ia.php:212`)
   ```html
   Auto si >90 dias
   ```

4. **Vista - JavaScript** (`generar_con_ia.php:1540`)
   ```javascript
   // Opcion 1: Auto-actualizar si >90 dias y checkbox activo
   ```

5. **Vista - SweetAlert ayuda** (`generar_con_ia.php:1626, 1688`)
   ```html
   <li><strong>Auto si &gt;90 días:</strong> El sistema actualiza automáticamente...</li>
   ```

### ✅ Solución Propuesta
- Leer `vigencia_dias` de la BD (ya existe en la tabla, línea 16)
- Pasar como variable PHP a la vista
- Usar variable dinámica en labels y mensajes
- Eliminar fallback `?? 90`, usar siempre el valor de BD

---

## 2. Timeout de API OpenAI

### 🟡 HARDCODEADO - Podría ser configurable, pero es razonable como constante

**Valor:** 90 segundos
**Ubicaciones:**

1. **Servicio cURL** (`MarcoNormativoService.php:122`)
   ```php
   CURLOPT_TIMEOUT => 90
   ```

2. **Vista - Toast** (`generar_con_ia.php:1585`)
   ```javascript
   'Consultando... Esto puede tardar hasta 90 segundos.'
   ```

3. **Vista - SweetAlert** (`generar_con_ia.php:1642, 1704`)
   ```html
   Tiempo estimado: 30-90 segundos
   ```

### ✅ Solución Propuesta
- Crear constante de clase `const API_TIMEOUT = 90;`
- Reutilizar en todos los mensajes usando PHP o JavaScript
- **Razón:** Es un límite técnico de la API, no una preferencia de negocio

---

## 3. Modelo de OpenAI

### 🟢 HARDCODEADO - Constante del diseño (OK)

**Valor:** `gpt-4o`
**Ubicaciones:**

1. **Servicio** (`MarcoNormativoService.php:105`)
   ```php
   'model' => 'gpt-4o'
   ```

2. **Vista - SweetAlert** (`generar_con_ia.php:1642, 1704`)
   ```html
   Modelo: GPT-4o con búsqueda web
   ```

3. **Vista - Toast** (`generar_con_ia.php:1585`)
   ```javascript
   'Consultando marco normativo vigente con IA (GPT-4o + busqueda web)...'
   ```

### ✅ Decisión
**NO CAMBIAR.** GPT-4o es el único modelo de OpenAI que soporta `web_search_preview`. Si OpenAI lanza un modelo superior (ej: GPT-5), se actualizará manualmente en una sola constante.

---

## 4. Temperatura de OpenAI

### 🟢 HARDCODEADO - Constante del diseño (OK)

**Valor:** `0.3`
**Ubicación:**

1. **Servicio** (`MarcoNormativoService.php:110`)
   ```php
   'temperature' => 0.3
   ```

### ✅ Decisión
**NO CAMBIAR.** Temperatura baja (0.3) es apropiada para contenido legal/normativo que debe ser preciso y consistente. No es un parámetro que deba configurar el usuario.

---

## 5. Año Actual en Placeholders

### ✅ YA CORREGIDO - Ahora es dinámico (2026-02-15)

**Valor anterior:** "2023-2024" (hardcodeado)
**Valor actual:** `<?= date('Y') ?>` (dinámico)

**Ubicación:**
1. **Vista - Textarea contexto** (`generar_con_ia.php:564`)
   ```php
   placeholder="Ej: ..., 'Incluir legislación reciente <?= date('Y') ?>', ..."
   ```

### ✅ Solución Aplicada
Usa la función PHP `date('Y')` para obtener el año actual dinámicamente (2026).

**Razón del cambio:** El usuario cuestionó por qué el placeholder mostraba "2023-2024" cuando estamos en 2026. Era un valor obsoleto que no tenía sentido mantener hardcodeado.

---

## 6. Nombres de Documentos

### 🟢 HARDCODEADO - Diccionario estático (OK)

**Ubicación:**
1. **Servicio** (`MarcoNormativoService.php:203-213`)
   ```php
   protected function getNombreDocumento(string $tipo): string
   {
       $nombres = [
           'politica_sst_general' => 'Política de Seguridad y Salud en el Trabajo',
           'programa_capacitacion' => 'Programa de Capacitación en SST',
           // ...
       ];
   }
   ```

### ✅ Decisión
**NO CAMBIAR.** Es un mapeo snake_case → nombre legible. Tiene fallback dinámico para tipos no listados (línea 220).

---

## Plan de Acción

### Prioridad Alta 🔴
1. **Hacer vigencia_dias dinámico en la UI**
   - Eliminar referencias hardcodeadas a "90 días" en labels/mensajes
   - Usar el valor de `vigencia_dias` desde BD
   - Pasar como variable PHP a la vista

### Prioridad Media 🟡
2. **Crear constante para API_TIMEOUT**
   - Centralizar el valor 90 segundos
   - Reutilizar en mensajes de UI

### No Cambiar 🟢
3. **Mantener hardcodeados:**
   - Modelo GPT-4o (limitación de OpenAI)
   - Temperatura 0.3 (decisión de diseño)
   - Diccionario de nombres (catálogo estático)

---

## Conclusión

De los 6 grupos de valores hardcodeados identificados:
- ✅ **1 ya corregido:** Año actual en placeholders
- 🔴 **1 crítico:** Vigencia de 90 días debe ser dinámico
- 🟡 **1 mejorable:** Timeout de 90 segundos (baja prioridad)
- 🟢 **3 aceptables:** Modelo, temperatura, diccionario de nombres

**Próximo paso:** Implementar vigencia_dias dinámica en la UI.
