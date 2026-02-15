# Integración Marco Normativo en SweetAlert de Verificación

**Fecha:** 2026-02-15
**Estado:** ✅ Implementado y verificado completamente
**Módulo:** Insumos IA - Pregeneración

---

## 📋 Resumen Ejecutivo

Se completó la integración del **Marco Normativo** (Insumos IA - Pregeneración) en el SweetAlert de verificación de datos que aparece antes de generar documentos con IA. Esto permite al consultor verificar que la IA usará normativa actualizada antes de iniciar la generación.

**Objetivo alcanzado:** Garantizar que cada sección del documento generada con IA consulte elementos ciertos desde la base de datos:
- ✅ Marco normativo vigente (pregeneración)
- ✅ Plan de Trabajo (PTA)
- ✅ Indicadores SST
- ✅ Contexto del cliente

---

## 🎯 Problema Identificado

Al usar el botón **"Generar TODO con IA"**, el SweetAlert mostraba:
- ✅ Actividades del Plan de Trabajo
- ✅ Indicadores relacionados
- ✅ Contexto del cliente
- ❌ **Faltaba:** Marco normativo (pregeneración)

El consultor necesitaba verificar que el marco normativo estaba vigente y sería usado por la IA antes de generar el documento completo.

---

## ✨ Solución Implementada

### 1. Backend: Dos campos de texto

**Archivo:** `app/Controllers/DocumentosSSTController.php`
**Método:** `previsualizarDatos()` (líneas 439-464)

**Cambio crítico:** Agregar campo `texto_completo` al JSON de respuesta

```php
$marcoNormativoInfo = [
    'existe' => false,
    'vigente' => false,
    'texto_preview' => '',      // 200 caracteres para resumen
    'texto_completo' => '',      // ⭐ NUEVO: Texto completo para SweetAlert
    'fecha' => '',
    'dias' => 0,
    'metodo' => '',
];

if ($infoMarco['existe']) {
    $marcoNormativoInfo = [
        'existe' => true,
        'vigente' => $infoMarco['vigente'],
        'texto_preview' => mb_substr($infoMarco['texto'], 0, 200) . '...',
        'texto_completo' => $infoMarco['texto'],  // Texto completo
        'fecha' => $infoMarco['fecha'],
        'dias' => $infoMarco['dias'],
        'metodo' => $infoMarco['metodo'],
    ];
}
```

**Razón:** El SweetAlert necesita mostrar el texto completo del marco normativo (puede tener 2,000+ caracteres), no solo un preview.

---

### 2. Frontend: Dos SweetAlerts secuenciales para "Generar TODO"

**Archivo:** `app/Views/documentos_sst/generar_con_ia.php`

#### SweetAlert 1: Marco Normativo Completo (líneas 1238-1283)

Muestra **solo** el marco normativo con scroll para leer el contenido completo:

```javascript
// SWEETALERT 1: MARCO NORMATIVO COMPLETO
if (data && data.marco_normativo) {
    let htmlMarco = '<div style="text-align: left; max-height: 500px; overflow-y: auto;">';

    if (data.marco_normativo.existe) {
        const esVigente = data.marco_normativo.vigente;
        const colorEstado = esVigente ? '#28a745' : '#dc3545';
        const textoEstado = esVigente ? 'Vigente ✅' : 'Vencido ⚠️';

        htmlMarco += '<div style="background: #f8f9fa; padding: 12px; border-radius: 6px; margin-bottom: 15px;">';
        htmlMarco += '<p><strong>Estado:</strong> <span style="color: ' + colorEstado + '; font-weight: bold;">' + textoEstado + '</span></p>';
        htmlMarco += '<p><strong>Actualizado hace:</strong> ' + data.marco_normativo.dias + ' días</p>';
        htmlMarco += '<p><strong>Fecha:</strong> ' + data.marco_normativo.fecha + '</p>';
        htmlMarco += '<p><strong>Método:</strong> ' + data.marco_normativo.metodo + '</p>';
        htmlMarco += '</div>';

        htmlMarco += '<h6><strong>📄 Texto completo del marco normativo:</strong></h6>';
        htmlMarco += '<div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 4px; padding: 12px; font-size: 0.9rem; line-height: 1.6; color: #212529; white-space: pre-wrap;">';
        htmlMarco += data.marco_normativo.texto_completo || 'Sin contenido';  // ⭐ TEXTO COMPLETO
        htmlMarco += '</div>';
    }

    await Swal.fire({
        title: '📋 Marco Normativo Vigente',
        html: htmlMarco,
        icon: data.marco_normativo.existe ? 'info' : 'warning',
        confirmButtonText: 'Continuar',
        width: '700px'
    });
}
```

**Características:**
- Máxima altura 500px con scroll
- Muestra estado (vigente/vencido) con colores
- Metadata: días transcurridos, fecha actualización, método
- Texto completo del marco normativo con formato
- Width 700px para mejor lectura

#### SweetAlert 2: Resumen Completo (líneas 1289-1307)

Muestra **resumen** de todas las fuentes de datos:

```javascript
// Marco Normativo (Insumos IA - Pregeneración)
if (data.marco_normativo && data.marco_normativo.existe) {
    const esVigente = data.marco_normativo.vigente;
    const icono = esVigente ? '✅' : '⚠️';
    const estado = esVigente
        ? '<span style="color: #28a745; font-weight: bold;">Vigente</span>'
        : '<span style="color: #dc3545; font-weight: bold;">Vencido</span>';

    htmlResumen += '<h6><strong>' + icono + ' Marco Normativo:</strong></h6>';
    htmlResumen += '<div style="font-size: 0.85rem; padding-left: 20px; margin-bottom: 12px; background: #f8f9fa; padding: 8px; border-radius: 4px;">';
    htmlResumen += '<p><strong>Estado:</strong> ' + estado + '</p>';
    htmlResumen += '<p><strong>Actualizado:</strong> hace ' + data.marco_normativo.dias + ' días (' + data.marco_normativo.fecha + ')</p>';
    htmlResumen += '<p><strong>Método:</strong> ' + (data.marco_normativo.metodo || 'N/A') + '</p>';
    htmlResumen += '<p style="color: #6c757d; font-size: 0.8rem; font-style: italic;">' + (data.marco_normativo.texto_preview || 'Sin preview') + '</p>';
    htmlResumen += '</div>';
}
```

**Orden en el resumen:**
1. Plan de Trabajo (actividades)
2. Indicadores SST
3. **Marco Normativo** ⭐ NUEVO
4. Contexto del cliente

---

### 3. Integración en generación individual de secciones

**Archivo:** `app/Views/documentos_sst/generar_con_ia.php`
**Función:** `mostrarVerificacionDatos()`

También se agregó la sección de marco normativo al SweetAlert que aparece al generar secciones individuales con el botón "Generar con IA" de cada sección.

---

## ✅ Verificación de Inyección en Prompts IA

### Confirmación 1: IADocumentacionService.php

**Archivo:** `app/Services/IADocumentacionService.php` (líneas 228-234)

```php
// INSUMOS IA - Pregeneración: Marco normativo desde BD
$marcoNormativo = $datos['marco_normativo'] ?? '';
if (!empty($marcoNormativo)) {
    $userPrompt .= "\nMARCO NORMATIVO VIGENTE APLICABLE (fuente verificada con busqueda web, usar EXCLUSIVAMENTE este marco):\n";
    $userPrompt .= $marcoNormativo . "\n";
    $userPrompt .= "IMPORTANTE: Usa SOLO las normas listadas arriba. NO inventes ni agregues normas adicionales.\n";
}
```

✅ **Confirmado:** El marco normativo SÍ se inyecta en el prompt de usuario que se envía a GPT-4o-mini.

### Confirmación 2: DocumentosSSTController.php

**Archivo:** `app/Controllers/DocumentosSSTController.php` (líneas 664-684)

```php
// INSUMOS IA - Pregeneración: obtener marco normativo desde BD
$marcoService = new MarcoNormativoService();
$marcoNormativo = $marcoService->obtenerMarcoNormativo($tipoDocumento);

// Preparar datos para el servicio de IA
$datosIA = [
    'seccion' => [
        'numero_seccion' => $numeroSeccion,
        'nombre_seccion' => $nombreSeccion
    ],
    'documento' => [
        'tipo_nombre' => $documentoHandler->getNombre(),
        'nombre' => $documentoHandler->getNombre(),
        'tipo' => $tipoDocumento
    ],
    'cliente' => $cliente,
    'contexto' => $contexto,
    'prompt_base' => $promptBase,
    'contexto_adicional' => $contextoAdicional,
    'contexto_base' => $contextoBase,          // ← PTA + Indicadores
    'marco_normativo' => $marcoNormativo ?? ''  // ← Marco normativo
];
```

✅ **Confirmado:** El controller SÍ obtiene el marco normativo de BD y lo pasa al servicio de IA.

---

## 🔍 Pruebas Realizadas

### 1. Verificación en Base de Datos

**Script creado:** `consultar_marco_temp.php` (raíz del proyecto)

```php
$stmt = $pdo->prepare("
    SELECT
        tipo_documento,
        fecha_actualizacion,
        metodo_actualizacion,
        activo,
        LENGTH(marco_normativo_texto) AS longitud_caracteres,
        marco_normativo_texto
    FROM tbl_marco_normativo
    WHERE tipo_documento = 'politica_alcohol_drogas'
      AND activo = 1
    LIMIT 1
");
```

**Resultado:**
- ✅ Marco normativo existe
- ✅ Longitud: 2,747 caracteres
- ✅ Contiene 7 normas completas:
  - Resolución 0312 de 2019
  - Ley 1562 de 2012
  - Decreto 1072 de 2015
  - Resolución 1016 de 1989
  - Ley 9 de 1979
  - Resolución 2646 de 2008
  - Ley 1010 de 2006

### 2. Visualización en SweetAlert

**Documento de prueba:** Política de Alcohol y Drogas (cliente 18)

**Flujo:**
1. Usuario abre `/documentos/generar/politica_alcohol_drogas/18?anio=2026`
2. Usuario hace clic en "Generar TODO con IA"
3. **SweetAlert 1** aparece mostrando:
   - Estado: Vigente ✅
   - Actualizado hace: 21 días
   - Fecha: 2026-01-25
   - Método: boton
   - **Texto completo:** 2,747 caracteres con las 7 normas (scroll habilitado)
4. Usuario hace clic en "Continuar"
5. **SweetAlert 2** aparece mostrando resumen con 4 fuentes:
   - ✅ Plan de Trabajo
   - ✅ Indicadores
   - ✅ Marco Normativo (preview)
   - ✅ Contexto del cliente

### 3. Verificación de código

**Lecturas realizadas:**
- ✅ `IADocumentacionService.php` líneas 210-260 → Inyección confirmada
- ✅ `DocumentosSSTController.php` líneas 620-699 → Obtención y paso de datos confirmado

---

## 📊 Flujo Completo de Datos

```
┌─────────────────────────────────────────┐
│  tbl_marco_normativo                    │
│  - tipo_documento = 'politica_...'      │
│  - marco_normativo_texto (2,747 chars)  │
│  - activo = 1                           │
└─────────────┬───────────────────────────┘
              │
              │ MarcoNormativoService::obtenerMarcoNormativo()
              ↓
┌─────────────────────────────────────────┐
│  DocumentosSSTController                │
│  ::generarConIAReal()                   │
│                                         │
│  $datosIA = [                           │
│    'contexto_base' => PTA + Indicadores │
│    'marco_normativo' => $texto          │← ⭐ PASO 1
│  ]                                      │
└─────────────┬───────────────────────────┘
              │
              │ IADocumentacionService::generarSeccion()
              ↓
┌─────────────────────────────────────────┐
│  IADocumentacionService                 │
│  ::construirPrompt()                    │
│                                         │
│  $userPrompt .= "\nMARCO NORMATIVO...   │← ⭐ PASO 2 (Inyección)
│  $userPrompt .= $marcoNormativo         │
└─────────────┬───────────────────────────┘
              │
              │ OpenAI API (GPT-4o-mini)
              ↓
┌─────────────────────────────────────────┐
│  GPT-4o-mini recibe prompt con:         │
│  - Datos del cliente                    │
│  - PTA (actividades)                    │
│  - Indicadores                          │
│  - Marco normativo vigente              │← ⭐ PASO 3 (Contexto IA)
│  - Instrucciones de generación          │
└─────────────┬───────────────────────────┘
              │
              │ Genera contenido con normas correctas
              ↓
┌─────────────────────────────────────────┐
│  Sección del documento generada         │
│  con normativa CIERTA desde BD          │← ✅ OBJETIVO CUMPLIDO
└─────────────────────────────────────────┘
```

---

## 📁 Archivos Modificados

| Archivo | Líneas | Cambio |
|---------|--------|--------|
| `app/Controllers/DocumentosSSTController.php` | 439-464 | Agregado campo `texto_completo` en JSON response |
| `app/Views/documentos_sst/generar_con_ia.php` | 1238-1283 | SweetAlert 1: Marco normativo completo |
| `app/Views/documentos_sst/generar_con_ia.php` | 1289-1307 | SweetAlert 2: Sección marco en resumen |
| `app/Views/documentos_sst/generar_con_ia.php` | (mostrarVerificacionDatos) | Agregado marco normativo en generación individual |
| `docs/MODULO_NUMERALES_SGSST/ZZ_90_PARTESWEETALERT.md` | Varias | Documentado marco normativo en SweetAlert |
| `docs/MODULO_NUMERALES_SGSST/INSUMOS_IA_PREGENERACION.md` | 248-277 | Confirmada integración con generación IA |

---

## 📁 Archivos Creados

| Archivo | Propósito |
|---------|-----------|
| `consultar_marco_temp.php` | Script temporal para verificar marco normativo en BD |
| `docs/MODULO_NUMERALES_SGSST/INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md` | Este documento |

---

## 🎓 Aprendizajes Clave

### 1. Dos flujos diferentes de "Generar con IA"

**Error inicial:** Se modificó solo `mostrarVerificacionDatos()` pero el usuario estaba probando con "Generar TODO con IA".

**Solución:** Identificar que hay DOS SweetAlerts diferentes:
- **Individual:** Función `mostrarVerificacionDatos()` (botones en cada sección)
- **Batch:** Inline SweetAlert en event listener `btnGenerarTodo` (botón superior)

**Lección:** Siempre verificar TODOS los puntos de entrada de una funcionalidad.

### 2. Preview vs Texto completo

**Error inicial:** Solo se mostraban 200 caracteres del marco normativo.

**Solución:** Agregar campo `texto_completo` en el backend para que el frontend pueda mostrar todo el contenido.

**Lección:** Separar datos para diferentes propósitos (preview para listas, completo para detalles).

### 3. Importancia de la documentación exhaustiva

El usuario necesitaba **certeza absoluta** de que el marco normativo se estaba usando en la generación IA, no solo mostrando en un SweetAlert.

**Solución:** Leer y documentar el código de inyección en `IADocumentacionService.php` y `DocumentosSSTController.php`.

**Lección:** Para funcionalidades críticas (normativa legal), la verificación del código fuente es indispensable.

---

## 🎯 Objetivo Cumplido

✅ **Confirmado:** Cada sección del documento generada con IA SÍ consulta elementos ciertos desde la base de datos:

1. ✅ **Marco normativo vigente** (pregeneración) → Inyectado en línea 232 de `IADocumentacionService.php`
2. ✅ **Plan de Trabajo** (PTA) → Incluido en `contexto_base`
3. ✅ **Indicadores SST** → Incluido en `contexto_base`
4. ✅ **Contexto del cliente** → Datos de `tbl_cliente` y `tbl_cliente_contexto_sst`

**El consultor puede confiar en que los documentos generados con IA usan normativa actualizada almacenada en la base de datos, no el conocimiento base desactualizado del modelo.**

---

## 📚 Relación con Otros Documentos

| Documento | Relación |
|-----------|----------|
| [`INSUMOS_IA_PREGENERACION.md`](INSUMOS_IA_PREGENERACION.md) | Documento maestro del módulo de marco normativo |
| [`ZZ_90_PARTESWEETALERT.md`](ZZ_90_PARTESWEETALERT.md) | Documentación completa del SweetAlert de verificación |
| [`VALORES_HARDCODEADOS_MARCO_NORMATIVO.md`](VALORES_HARDCODEADOS_MARCO_NORMATIVO.md) | Análisis de valores hardcodeados vs dinámicos |
| [`README_MARCO_NORMATIVO.md`](README_MARCO_NORMATIVO.md) | Índice general del módulo |

---

## ✅ Checklist de Verificación

- [x] Backend retorna `texto_preview` Y `texto_completo`
- [x] SweetAlert 1 muestra marco normativo completo con scroll
- [x] SweetAlert 2 muestra resumen con las 4 fuentes de datos
- [x] Marco normativo aparece en generación individual de secciones
- [x] Código de inyección verificado en `IADocumentacionService.php`
- [x] Código de obtención verificado en `DocumentosSSTController.php`
- [x] Base de datos contiene marco normativo válido (2,747 caracteres)
- [x] Documentación actualizada en `ZZ_90_PARTESWEETALERT.md`
- [x] Documentación actualizada en `INSUMOS_IA_PREGENERACION.md`
- [x] Script de prueba creado (`consultar_marco_temp.php`)

---

**Última actualización:** 2026-02-15
**Autor:** Claude Code + Usuario
**Estado:** ✅ Completado y documentado
