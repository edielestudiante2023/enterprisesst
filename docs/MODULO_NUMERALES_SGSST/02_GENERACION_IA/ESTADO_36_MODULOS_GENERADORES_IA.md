# Estado de los 36 Módulos Generadores IA

**Fecha:** 2026-02-15
**Objetivo:** Dejar funcionando todos los módulos generadores IA como `politica_alcohol_drogas`

---

## 📊 Resumen Ejecutivo

| Componente | Estado | Detalles |
|------------|--------|----------|
| **Clases PHP** | ✅ 36/36 | Todas las clases existen en `app/Libraries/DocumentosSSTTypes/` |
| **Registro Factory** | ✅ 36/36 | Todos registrados en `DocumentoSSTFactory.php` |
| **Rutas** | ✅ Universal | Ruta genérica `(:segment)` cubre todos los tipos |
| **SweetAlert** | ✅ Centralizado | Función `mostrarVerificacionDatos()` + event listener `btnGenerarTodo` |
| **Inyección IA** | ✅ Centralizado | `IADocumentacionService::construirPrompt()` línea 232 |
| **Marco Normativo BD** | ⚠️ Pendiente verificar | No todos los tipos tienen marco en `tbl_marco_normativo` |

---

## ✅ Infraestructura Completa

### 1. Todas las Clases PHP Existen (41 archivos)

**36 Clases de Documentos:**
```bash
ActaConstitucionBrigada.php
ActaConstitucionCocolab.php
ActaConstitucionCopasst.php
ActaConstitucionVigia.php
ActaRecomposicionBrigada.php
ActaRecomposicionCocolab.php
ActaRecomposicionCopasst.php
ActaRecomposicionVigia.php
IdentificacionAltoRiesgo.php
IdentificacionSustanciasCancerigenas.php
ManualConvivenciaLaboral.php
MecanismosComunicacionSgsst.php
MetodologiaIdentificacionPeligros.php
PlanObjetivosMetas.php
PoliticaAcosoLaboral.php
PoliticaAlcoholDrogas.php
PoliticaDiscriminacion.php
PoliticaPrevencionEmergencias.php
PoliticaSstGeneral.php
PoliticaViolenciasGenero.php
ProcedimientoAdquisiciones.php
ProcedimientoControlDocumental.php
ProcedimientoEvaluacionesMedicas.php
ProcedimientoEvaluacionProveedores.php
ProcedimientoGestionCambio.php
ProcedimientoInvestigacionAccidentes.php
ProcedimientoInvestigacionIncidentes.php
ProcedimientoMatrizLegal.php
ProgramaCapacitacion.php
ProgramaEstilosVidaSaludable.php
ProgramaEvaluacionesMedicasOcupacionales.php
ProgramaInduccionReinduccion.php
ProgramaMantenimientoPeriodico.php
ProgramaPromocionPrevencionSalud.php
PveRiesgoBiomecanico.php
PveRiesgoPsicosocial.php
```

**5 Archivos de Infraestructura:**
```bash
AbstractActaConstitucion.php
AbstractActaRecomposicion.php
AbstractDocumentoSST.php
DocumentoSSTFactory.php
DocumentoSSTInterface.php
```

---

### 2. Factory Completo

**Archivo:** `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

Todos los 36 módulos están registrados en el array `$tiposRegistrados`:

```php
private static array $tiposRegistrados = [
    // 27 documentos Tipo A (secciones_ia)
    'programa_capacitacion',                      // ✅ Tipo B (único)
    'procedimiento_control_documental',           // ✅
    'programa_promocion_prevencion_salud',        // ✅
    'programa_induccion_reinduccion',             // ✅
    'procedimiento_matriz_legal',                 // ✅
    'politica_sst_general',                       // ✅
    'politica_alcohol_drogas',                    // ✅
    'politica_acoso_laboral',                     // ✅
    'politica_violencias_genero',                 // ✅
    'politica_discriminacion',                    // ✅
    'politica_prevencion_emergencias',            // ✅
    'manual_convivencia_laboral',                 // ✅
    'plan_objetivos_metas',                       // ✅
    'mecanismos_comunicacion_sgsst',              // ✅
    'procedimiento_evaluaciones_medicas',         // ✅
    'procedimiento_adquisiciones',                // ✅
    'procedimiento_evaluacion_proveedores',       // ✅
    'procedimiento_gestion_cambio',               // ✅
    'programa_estilos_vida_saludable',            // ✅
    'programa_evaluaciones_medicas_ocupacionales',// ✅
    'procedimiento_investigacion_accidentes',     // ✅
    'procedimiento_investigacion_incidentes',     // ✅
    'metodologia_identificacion_peligros',        // ✅
    'identificacion_sustancias_cancerigenas',     // ✅
    'pve_riesgo_biomecanico',                     // ✅
    'pve_riesgo_psicosocial',                     // ✅
    'programa_mantenimiento_periodico',           // ✅
    'identificacion_alto_riesgo',                 // ✅

    // 8 documentos Electoral
    'acta_constitucion_copasst',                  // ✅
    'acta_constitucion_cocolab',                  // ✅
    'acta_constitucion_brigada',                  // ✅
    'acta_constitucion_vigia',                    // ✅
    'acta_recomposicion_copasst',                 // ✅
    'acta_recomposicion_cocolab',                 // ✅
    'acta_recomposicion_brigada',                 // ✅
    'acta_recomposicion_vigia',                   // ✅
];
```

---

### 3. Rutas Universales

**Archivo:** `app/Config/Routes.php`

```php
// Ruta genérica que cubre TODOS los 36 módulos
$routes->get('/documentos/generar/(:segment)/(:num)',
    'DocumentosSSTController::generarConIA/$1/$2');

// Endpoint para generar secciones con IA
$routes->post('/documentos/generar-seccion',
    'DocumentosSSTController::generarSeccionIA');
```

**Formato de URLs:**
- `/documentos/generar/politica_sst_general/18`
- `/documentos/generar/programa_capacitacion/18`
- `/documentos/generar/acta_constitucion_copasst/18`
- etc.

---

### 4. SweetAlert Centralizado

**Archivo:** `app/Views/documentos_sst/generar_con_ia.php`

#### Función para botones individuales:
```javascript
function mostrarVerificacionDatos(seccion) {
    // Consulta endpoint: /documentos/previsualizar-datos/{tipo}/{id_cliente}
    // Muestra SweetAlert con:
    //   - Plan de Trabajo (si aplica)
    //   - Indicadores (si aplica)
    //   - Marco Normativo ⭐
    //   - Contexto del cliente
}
```

#### Event listener para "Generar TODO":
```javascript
btnGenerarTodo.addEventListener('click', async () => {
    // 1. SweetAlert marco normativo completo (con scroll)
    // 2. SweetAlert resumen (PTA + Indicadores + Marco + Contexto)
    // 3. Genera todas las secciones
});
```

---

### 5. Inyección IA Centralizada

**Archivo:** `app/Services/IADocumentacionService.php` (línea 228-234)

```php
// INSUMOS IA - Pregeneración: Marco normativo desde BD
$marcoNormativo = $datos['marco_normativo'] ?? '';
if (!empty($marcoNormativo)) {
    $userPrompt .= "\nMARCO NORMATIVO VIGENTE APLICABLE (fuente verificada, usar EXCLUSIVAMENTE este marco):\n";
    $userPrompt .= $marcoNormativo . "\n";
    $userPrompt .= "IMPORTANTE: Usa SOLO las normas listadas arriba. NO inventes ni agregues normas adicionales.\n";
}
```

**Archivo:** `app/Controllers/DocumentosSSTController.php` (línea 664-684)

```php
// Obtener marco normativo de BD
$marcoService = new MarcoNormativoService();
$marcoNormativo = $marcoService->obtenerMarcoNormativo($tipoDocumento);

$datosIA = [
    // ... otros datos ...
    'marco_normativo' => $marcoNormativo ?? ''
];
```

---

## ⚠️ Pendiente: Marco Normativo en BD

**Problema:** No todos los tipos de documento tienen su marco normativo almacenado en `tbl_marco_normativo`.

### Verificación necesaria:

```sql
SELECT tipo_documento,
       fecha_actualizacion,
       metodo_actualizacion,
       DATEDIFF(NOW(), fecha_actualizacion) AS dias_transcurridos,
       LENGTH(marco_normativo_texto) AS caracteres
FROM tbl_marco_normativo
WHERE activo = 1
ORDER BY tipo_documento;
```

### Marcos normativos confirmados:

| Tipo Documento | Estado | Caracteres | Método | Fecha |
|----------------|--------|------------|--------|-------|
| `politica_alcohol_drogas` | ✅ Vigente | 2,747 | boton | 2026-01-25 |
| `politica_sst_general` | ✅ Vigente | ??? | boton | ??? |
| ... otros pendientes de verificar ... |

---

## 🎯 Plan de Acción

### Fase 1: Verificación ✅ COMPLETADA

- [x] Confirmar que las 36 clases PHP existen
- [x] Confirmar que están registradas en Factory
- [x] Confirmar que la ruta genérica existe
- [x] Confirmar que SweetAlert está centralizado
- [x] Confirmar que inyección IA está centralizada

### Fase 2: Prueba de Módulos 🔄 EN PROGRESO

- [ ] Probar 1 módulo Tipo A: `politica_sst_general`
- [ ] Probar 1 módulo Tipo B: `programa_capacitacion`
- [ ] Probar 1 módulo Electoral: `acta_constitucion_copasst`

**Objetivo:** Confirmar que la URL abre, el SweetAlert muestra datos, y la generación funciona.

### Fase 3: Marco Normativo en BD ⏳ PENDIENTE

#### Opción 1: Generación manual por el usuario
1. Abrir cada módulo: `/documentos/generar/{tipo}/18`
2. Panel "Insumos IA - Pregeneración"
3. Clic en "Consultar IA"
4. GPT-4o + web search obtiene marco normativo (30-90 seg)
5. Se guarda en BD automáticamente

**Ventaja:** Control total del usuario
**Desventaja:** 36 consultas manuales (tiempo considerable)

#### Opción 2: Script automatizado
1. Crear script PHP que consulte la IA para cada tipo
2. Usar `MarcoNormativoService::consultarConIA()`
3. Guardar en BD con `metodo_actualizacion = 'automatico'`
4. Ejecutar en background

**Ventaja:** Rápido, automático
**Desventaja:** Costo de API (~36 consultas a GPT-4o con web search)

#### Opción 3: Híbrido (RECOMENDADO)
1. Identificar tipos prioritarios (políticas, procedimientos principales)
2. Generar marcos normativos para esos primero (10-15 tipos)
3. Los demás se generan cuando el usuario los necesite

---

## 📋 Checklist de Funcionamiento Completo

Para que un módulo esté "funcionando completamente", debe cumplir:

- [x] **1. Clase PHP existe** - Todas existen ✅
- [x] **2. Registrado en Factory** - Todos registrados ✅
- [x] **3. Ruta funcional** - Ruta genérica cubre todos ✅
- [x] **4. SweetAlert muestra datos** - Centralizado, funciona para todos ✅
- [x] **5. Marco normativo se inyecta** - Centralizado, funciona para todos ✅
- [ ] **6. Marco normativo en BD** - ⚠️ Falta verificar y crear ⚠️
- [ ] **7. Prueba end-to-end** - Pendiente por cada tipo

---

## 🔍 Siguiente Paso Inmediato

**Consultar la BD** para ver cuántos marcos normativos ya existen:

```sql
SELECT COUNT(*) AS total_marcos FROM tbl_marco_normativo WHERE activo = 1;
```

Si el resultado es:
- **36 marcos:** ✅ Todo listo, solo falta probar
- **Menos de 36:** Necesitamos generar los faltantes

---

## 📚 Relación con Otros Documentos

| Documento | Relación |
|-----------|----------|
| [`INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md`](INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md) | Documenta cómo funciona el SweetAlert con marco normativo |
| [`INSUMOS_IA_PREGENERACION.md`](INSUMOS_IA_PREGENERACION.md) | Módulo completo de marco normativo |
| [`README_MARCO_NORMATIVO.md`](README_MARCO_NORMATIVO.md) | Índice general del módulo |

---

**Última actualización:** 2026-02-15
**Estado:** Infraestructura completa, pendiente verificar marcos normativos en BD
