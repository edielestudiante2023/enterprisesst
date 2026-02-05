# Arquitectura de Generación IA para Documentos SST

## Regla Fundamental

⚠️ **TODOS los tipos de documento requieren una clase PHP en `DocumentosSSTTypes/` y registro en el Factory.**

Esto aplica tanto a documentos simples (`secciones_ia`) como a documentos con fases (`programa_con_pta`).

## Cuándo Aplica Este Documento

Este documento detalla la arquitectura para documentos con flujo **`programa_con_pta`** que tienen fases previas:
- Etapas/Configuración → PTA → Indicadores → Documento IA

**Ejemplos:** 1.2.1 Capacitación, 1.2.2 Inducción, 3.1.2 PyP Salud

Para documentos simples (flujo `secciones_ia`) sin fases, consulta [TROUBLESHOOTING_GENERACION_IA.md](./TROUBLESHOOTING_GENERACION_IA.md) - pero **igual necesitan clase PHP**.

---

## Problema Identificado (2026-02-04)

### Síntoma
Al hacer clic en "Generar con IA" en el documento **1.2.2 Programa de Inducción y Reinducción**, las secciones mostraban `[Seccion no definida]` o contenido genérico que NO usaba los datos reales de:
- Etapas de inducción (Fase 1)
- Actividades del PTA (Fase 2)
- Indicadores configurados (Fase 3)

### Causa Raíz
**Faltaba la clase `ProgramaInduccionReinduccion.php`** en el Factory de documentos.

El flujo de generación usa un patrón Factory donde cada tipo de documento tiene su propia clase que define:
1. Cómo obtener el contexto de la BD
2. Qué prompts usar para cada sección
3. Contenido estático de fallback

Sin la clase, el sistema caía en el método `generarContenidoSeccionLegacy()` que NO consulta las tablas específicas del módulo.

---

## Arquitectura de Generación IA

### Flujo Completo

```
Usuario hace clic en "Generar con IA"
              ↓
DocumentosSSTController::generarSeccionIA()
              ↓
DocumentoSSTFactory::crear('programa_induccion_reinduccion')
              ↓
┌─ ¿Existe clase en Factory? ────────────────────────────┐
│                                                         │
│  SÍ → Instancia ProgramaInduccionReinduccion           │
│       ↓                                                 │
│       getContextoBase() ← CONSULTA BD:                 │
│         • tbl_induccion_etapas (Fase 1)                │
│         • tbl_pta_cliente (Fase 2)                     │
│         • tbl_indicadores_sst (Fase 3)                 │
│         • tbl_cliente_contexto_sst (Contexto)          │
│       ↓                                                 │
│       getPromptParaSeccion() ← Prompt específico       │
│       ↓                                                 │
│       IADocumentacionService::generarSeccion()         │
│       ↓                                                 │
│       OpenAI genera contenido con DATOS REALES ✅      │
│                                                         │
│  NO → Método legacy (sin datos de BD) ❌               │
│       Retorna "[Seccion no definida]"                  │
└─────────────────────────────────────────────────────────┘
```

### Archivos Clave

| Archivo | Responsabilidad |
|---------|-----------------|
| `DocumentoSSTFactory.php` | Crea instancias del tipo de documento correcto |
| `ProgramaInduccionReinduccion.php` | Define secciones, contexto y prompts para 1.2.2 |
| `IADocumentacionService.php` | Llama a OpenAI con el contexto completo |
| `DocumentosSSTController.php` | Orquesta el flujo de generación |

---

## Cómo se Asegura que el 100% de las Secciones Usen Datos Reales

### 1. Método `getContextoBase()` - Inyección de Datos de BD

Cada clase de documento DEBE sobrescribir este método para consultar las tablas relevantes:

```php
public function getContextoBase(array $cliente, ?array $contexto): string
{
    $idCliente = $cliente['id_cliente'] ?? 0;
    $anio = (int) date('Y');

    // FASE 1: Etapas de inducción
    $etapasTexto = $this->obtenerEtapasInduccion($idCliente, $anio);

    // FASE 2: Actividades del PTA
    $actividadesTexto = $this->obtenerActividadesInduccion($idCliente, $anio);

    // FASE 3: Indicadores
    $indicadoresTexto = $this->obtenerIndicadoresInduccion($idCliente);

    return "
============================================================
ETAPAS DEL PROCESO DE INDUCCIÓN (FASE 1)
============================================================
{$etapasTexto}

============================================================
ACTIVIDADES DE INDUCCIÓN EN EL PLAN DE TRABAJO (FASE 2)
============================================================
{$actividadesTexto}

============================================================
INDICADORES DE INDUCCIÓN (FASE 3)
============================================================
{$indicadoresTexto}
...";
}
```

### 2. Contexto del Cliente - Datos Automáticos

El servicio `IADocumentacionService::construirPrompt()` SIEMPRE incluye:

```php
// De tbl_clientes
$razonSocial = $cliente['nombre_cliente'];
$nit = $cliente['nit'];
$direccion = $cliente['direccion'];
$ciudad = $cliente['ciudad'];

// De tbl_cliente_contexto_sst
$actividadEconomica = $contexto['sector_economico'];
$nivelRiesgo = $contexto['nivel_riesgo_arl'];
$totalTrabajadores = $contexto['total_trabajadores'];
$tieneCopasst = $contexto['tiene_copasst'];
$tieneVigia = $contexto['tiene_vigia_sst'];
$tieneBrigada = $contexto['tiene_brigada_emergencias'];
$peligrosIdentificados = $contexto['peligros_identificados'];
$estandaresAplicables = $contexto['estandares_aplicables'];
// ... más de 20 campos
```

### 3. Prompts Específicos por Sección

Cada sección tiene instrucciones que OBLIGAN a usar los datos:

```php
'indicadores' => "Define los indicadores del Programa de Inducción.
IMPORTANTE: Usa los INDICADORES CONFIGURADOS listados en el contexto de la Fase 3.
NO inventes indicadores si hay configurados.
Para cada indicador presenta: nombre, tipo, fórmula, meta y periodicidad.",

'cronograma' => "Genera el cronograma de actividades del Programa de Inducción.
IMPORTANTE: Usa las ACTIVIDADES REALES del Plan de Trabajo listadas en el contexto de la Fase 2.
NO inventes actividades - usa las que están registradas en el PTA."
```

---

## Tablas Consultadas por Módulo

### 1.2.2 Programa de Inducción y Reinducción

| Fase | Tabla | Filtro | Datos Obtenidos |
|------|-------|--------|-----------------|
| 1 | `tbl_induccion_etapas` | `id_cliente`, `anio` | Etapas, temas, duración, responsable |
| 2 | `tbl_pta_cliente` | `id_cliente`, `anio`, `tipo_servicio LIKE 'Induccion'` | Actividades, fechas, responsables |
| 3 | `tbl_indicadores_sst` | `id_cliente`, `categoria='induccion'` | Indicadores, fórmulas, metas |
| - | `tbl_cliente_contexto_sst` | `id_cliente` | Contexto general de la empresa |

### 1.2.1 Programa de Capacitación (ProgramaCapacitacion.php)

| Fase | Tabla | Filtro |
|------|-------|--------|
| 1 | `tbl_capacitaciones_cliente` | `id_cliente`, `anio` |
| 2 | `tbl_pta_cliente` | `tipo_servicio='Capacitacion'` |
| 3 | `tbl_indicadores_sst` | `categoria='capacitacion'` |

### 3.1.2 Programa PyP Salud (ProgramaPromocionPrevencionSalud.php)

| Fase | Tabla | Filtro |
|------|-------|--------|
| 1 | `tbl_pta_cliente` | `tipo_servicio='Programa PyP Salud'` |
| 2 | `tbl_indicadores_sst` | `categoria='pyp_salud'` |

---

## Checklist para Nuevos Tipos de Documento

Al crear un nuevo tipo de documento que use fases con datos de BD:

- [ ] **1. Crear clase** en `app/Libraries/DocumentosSSTTypes/`
  - Nombre: `{TipoDocumento}.php` (PascalCase)
  - Extender: `AbstractDocumentoSST`

- [ ] **2. Implementar métodos obligatorios:**
  ```php
  getTipoDocumento(): string    // 'programa_xyz'
  getNombre(): string           // 'Programa XYZ'
  getDescripcion(): string      // Descripción larga
  getEstandar(): ?string        // '1.2.2'
  getSecciones(): array         // Lista de secciones
  getFirmantesRequeridos(): array
  ```

- [ ] **3. Sobrescribir `getContextoBase()`:**
  - Consultar tablas de fases previas
  - Formatear datos como texto para la IA
  - Incluir instrucciones de uso

- [ ] **4. Implementar `getPromptParaSeccion()`:**
  - Primero consultar BD (`DocumentoConfigService`)
  - Fallback a prompts estáticos
  - Incluir instrucciones de usar datos del contexto

- [ ] **5. Registrar en Factory** ⚠️ **OBLIGATORIO**:
  ```php
  // DocumentoSSTFactory.php
  private static array $tiposRegistrados = [
      'programa_xyz' => ProgramaXyz::class,
  ];
  ```
  **Sin este paso, la generación mostrará "[Seccion no definida]"**

- [ ] **6. Probar generación:**
  - Verificar que cada sección muestre datos reales
  - Verificar que NO aparezca "[Seccion no definida]"
  - Si falla, revisar pasos 1 y 5 primero

---

## Estructura del Prompt Enviado a OpenAI

```
┌─────────────────────────────────────────────────────────┐
│ SYSTEM PROMPT (IADocumentacionService)                  │
│ - Reglas de redacción                                   │
│ - Normativa colombiana (0312/2019, 1072/2015)          │
│ - Restricciones según estándares (7/21/60)             │
└─────────────────────────────────────────────────────────┘
                          +
┌─────────────────────────────────────────────────────────┐
│ USER PROMPT                                             │
│                                                         │
│ CONTEXTO DEL CLIENTE: (automático)                     │
│ - Nombre, NIT, dirección                               │
│ - Actividad económica, nivel riesgo                    │
│ - Trabajadores, COPASST/Vigía                          │
│ - Peligros identificados                               │
│                                                         │
│ CONTEXTO BASE DEL DOCUMENTO: (getContextoBase)         │
│ - Etapas configuradas (Fase 1)                         │
│ - Actividades del PTA (Fase 2)                         │
│ - Indicadores configurados (Fase 3)                    │
│                                                         │
│ INSTRUCCIÓN: (getPromptParaSeccion)                    │
│ "Genera el contenido de la sección X usando los        │
│  datos REALES listados arriba..."                      │
└─────────────────────────────────────────────────────────┘
```

---

## Solución Implementada (2026-02-04)

### Archivos Creados/Modificados

1. **CREADO:** `app/Libraries/DocumentosSSTTypes/ProgramaInduccionReinduccion.php`
   - 13 secciones definidas
   - `getContextoBase()` consulta 3 tablas
   - Prompts específicos para inducción

2. **MODIFICADO:** `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`
   - Agregado registro: `'programa_induccion_reinduccion' => ProgramaInduccionReinduccion::class`

### Resultado

Ahora al generar cualquier sección del documento 1.2.2, la IA recibe:
- ✅ Datos reales de las etapas configuradas
- ✅ Actividades reales del Plan de Trabajo
- ✅ Indicadores reales configurados
- ✅ Contexto completo del cliente (peligros, trabajadores, comités, etc.)

---

## Referencias

- [TROUBLESHOOTING_GENERACION_IA.md](./TROUBLESHOOTING_GENERACION_IA.md) - Problemas comunes
- [PROMPT_NUEVO_DOCUMENTO_SST.md](../PROMPT_NUEVO_DOCUMENTO_SST.md) - Guía para crear documentos
- `app/Services/IADocumentacionService.php` - Servicio de IA
- `app/Services/DocumentoConfigService.php` - Lectura de prompts desde BD
