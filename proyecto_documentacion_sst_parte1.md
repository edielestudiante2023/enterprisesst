# Proyecto de Documentación SST - Parte 1

## Resumen Ejecutivo

Sistema de generación y gestión documental para SG-SST con inteligencia artificial, control documental ISO y firma electrónica.

---

## 1. Contexto del Proyecto

### 1.1 Problema que resuelve

- Clientes nuevos llegan con documentación de otras empresas/profesionales
- No hay estandarización en los documentos
- El control documental es manual y propenso a errores
- No hay trazabilidad de cambios ni versiones

### 1.2 Decisión clave

**Toda la documentación se genera desde cero** en el sistema EnterpriseSST.

No se migra documentación de terceros. Solo se conservan registros históricos obligatorios:
- Investigaciones de accidentes/incidentes
- Exámenes médicos ocupacionales
- Registros de capacitación con fechas
- Actas de COPASST/Convivencia
- Mediciones ambientales

---

## 2. Tipos de Documentos SG-SST

### 2.1 Clasificación por tipo

| # | Tipo | Control completo | Versión | Aprobación |
|---|------|------------------|---------|------------|
| 1 | Política | ✅ | ✅ | ✅ |
| 2 | Objetivos | ✅ | ✅ | ✅ |
| 3 | Programa | ✅ | ✅ | ✅ |
| 4 | Plan | ✅ | ✅ | ✅ |
| 5 | Procedimiento | ✅ | ✅ | ✅ |
| 6 | Protocolo | ✅ | ✅ | ✅ |
| 7 | Manual | ✅ | ✅ | ✅ |
| 8 | Informe | ⚠️ Parcial | ✅ | ⚠️ |
| 9 | Formato/Registro | ❌ | ❌ | ❌ |
| 10 | Documentos de apoyo | ⚠️ Variable | ⚠️ | ⚠️ |

> **Nota:** Los registros NO se versionan, se fechan.

### 2.2 Estructura de un Programa (13 secciones)

| # | Sección | Descripción |
|---|---------|-------------|
| 1 | Introducción | Contexto, justificación del programa |
| 2 | Objetivos | General y específicos |
| 3 | Alcance | A quién aplica, áreas, sedes |
| 4 | Marco normativo | Leyes, decretos, resoluciones aplicables |
| 5 | Definiciones | Glosario de términos (si aplica) |
| 6 | Diagnóstico o línea base | Estado actual, punto de partida |
| 7 | Actividades | Qué se va a hacer |
| 8 | Cronograma | Cuándo se hace cada actividad |
| 9 | Indicadores | Cómo se mide el cumplimiento |
| 10 | Responsables | Quién ejecuta, quién supervisa |
| 11 | Recursos | Humanos, técnicos, financieros |
| 12 | Seguimiento y evaluación | Cómo se revisa el avance |
| 13 | Registros asociados | Formatos, evidencias que genera |

### 2.3 Ejemplos de programas típicos

- Programa de Capacitación
- Programa de Vigilancia Epidemiológica (PVE Osteomuscular, Psicosocial, Visual)
- Programa de Medicina Preventiva
- Programa de Inspecciones
- Programa de Mantenimiento Preventivo
- Programa de Orden y Aseo
- Programa de EPP

---

## 3. Arquitectura del Sistema

### 3.1 Módulos principales

```
┌─────────────────────────────────────────────────────────────────┐
│                    MÓDULO CONTEXTO CLIENTE                      │
│  - Razón social, NIT, actividad económica                       │
│  - Cantidad trabajadores                                        │
│  - Nivel de riesgo ARL (I, II, III, IV, V)                     │
│  - Estándares mínimos aplicables (7, 21 o 60)                  │
│  - Sedes, turnos, procesos                                      │
│  - Peligros identificados                                       │
│  - Datos del responsable SG-SST                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              GENERADOR DE DOCUMENTOS (IA)                       │
│  - Lee contexto del cliente                                     │
│  - Genera texto para cada sección                               │
│  - Permite regenerar con contexto adicional                     │
│  - Aprobación por sección + aprobación global                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                 CONTROL DOCUMENTAL                              │
│  - Encabezado con identificación                                │
│  - Bloque de firmas                                             │
│  - Historial de cambios                                         │
│  - Pie de página de control                                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                 MÓDULO FIRMA (tipo DocuSeal)                    │
│  - Analista/Consultor firman en sistema                         │
│  - Cliente recibe link y firma electrónicamente                 │
│  - Se genera PDF final con evidencia de firmas                  │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Flujo del generador de documentos

```
1. Usuario selecciona tipo de documento (ej: "Programa de Capacitación")
2. IA lee contexto del cliente desde BD
3. IA genera texto para cada una de las 13 secciones
4. Se presenta editor por secciones:

   ┌─────────────────────────────────────────────────────────┐
   │ SECCIÓN 1: Introducción                                  │
   │ ┌─────────────────────────────────────────────────────┐ │
   │ │ Texto generado por IA...                            │ │
   │ └─────────────────────────────────────────────────────┘ │
   │ ┌─────────────────────────────────────────────────────┐ │
   │ │ Contexto adicional: [________________]              │ │
   │ └─────────────────────────────────────────────────────┘ │
   │ [Regenerar esta sección]  [Aprobar sección ✓]          │
   └─────────────────────────────────────────────────────────┘

5. Consultor revisa, agrega contexto, regenera si es necesario
6. Se aprueba cada sección individualmente
7. Opción de aprobar documento completo
8. Al aprobar: mensaje indica la versión que se generará
```

---

## 4. Control Documental

### 4.1 Encabezado del documento

| Campo | Para qué sirve |
|-------|----------------|
| Nombre del documento | Identificación clara |
| Código | Evita duplicidad (ej: PRG-CAP) |
| Sistema | SG-SST / SIG |
| Proceso | SST / Talento Humano |
| Fecha de emisión | Inicio de vigencia |
| Versión | Control de cambios |
| Estado | Vigente / Obsoleto |

### 4.2 Bloque de firmas

| Rol | Quién es | Cómo firma |
|-----|----------|------------|
| **Elaboró** | Analista SST (equipo interno) | Firma en sistema |
| **Revisó** | Consultor/Responsable SST | Firma en sistema |
| **Aprobó** | Alta Dirección del cliente | Firma tipo DocuSeal |

### 4.3 Control de versiones

| Tipo de cambio | Ejemplos | Versión |
|----------------|----------|---------|
| **Menor** | Ortografía, redacción, cambio responsable sin cambio proceso | 1.1 → 1.2 → 1.3 |
| **Mayor** | Cambio normativo, metodología, alcance, proceso | 2.0 → 3.0 |

**Reglas:**
- Versión inicial siempre es 1.0
- Código es fijo por tipo (PRG-CAP = Programa de Capacitación)
- Consecutivo es la versión, no un número de documento

### 4.4 Historial de cambios

| Versión | Fecha | Descripción del cambio | Autorizado por |
|---------|-------|------------------------|----------------|
| 1.0 | 01/01/2025 | Creación inicial | [Nombre] |
| 1.1 | 15/06/2025 | Ajuste normativo | [Nombre] |
| 2.0 | 01/01/2026 | Revisión integral | [Nombre] |

### 4.5 Pie de página

- Código
- Versión
- Página X de Y
- Estado del documento

### 4.6 Quién aprueba qué

| Documento | Aprueba |
|-----------|---------|
| Política | Representante legal (cliente) |
| Objetivos | Alta Dirección (cliente) |
| Manual SG-SST | Alta Dirección (cliente) |
| Procedimientos | Alta Dirección (cliente) |
| Programas | Alta Dirección (cliente) |
| Plan Anual | Alta Dirección (cliente) |
| Informes | Responsable SG-SST |
| Registros | No aplica |

---

## 5. Modelo de Datos

### 5.1 Tablas principales

#### documentos
```
documentos {
    id
    cliente_id
    tipo_documento        → programa, procedimiento, plan, etc.
    codigo                → PRG-CAP, PRO-INV, etc.
    nombre
    version_actual
    estado                → borrador, en_revision, aprobado, obsoleto
    fecha_creacion
    fecha_aprobacion
    elaboro_id            → usuario (Analista SST)
    reviso_id             → usuario (Consultor)
    aprobo_id             → contacto cliente (Alta Dirección)
}
```

#### documento_secciones
```
documento_secciones {
    id
    documento_id
    numero_seccion        → 1, 2, 3... 13
    nombre_seccion        → "Introducción", "Objetivos", etc.
    contenido             → texto generado/editado
    contexto_adicional    → input del usuario para regenerar
    aprobado              → si/no
    fecha_aprobacion
    regeneraciones        → contador de veces regenerado
}
```

#### documento_versiones
```
documento_versiones {
    id
    documento_id
    version               → "1.0", "1.1", "2.0"
    fecha
    descripcion_cambio
    autorizado_por
    archivo_pdf           → ruta al PDF generado
    estado                → vigente / obsoleto
}
```

### 5.2 Tablas de seguimiento operativo

#### actividades_seguimiento
```
actividades_seguimiento {
    id
    cliente_id
    documento_id          → origen (qué programa lo generó)
    actividad             → descripción de la actividad
    responsable
    fecha_programada
    fecha_ejecucion
    estado                → pendiente, en_proceso, ejecutada, vencida
    evidencia             → archivo adjunto
    observaciones
}
```

#### indicadores_programas
```
indicadores_programas {
    id
    cliente_id
    documento_id          → origen (qué programa lo generó)
    nombre                → "Cobertura de capacitación"
    formula               → "(Capacitados / Total) x 100"
    meta                  → "≥ 90%"
    frecuencia            → mensual, trimestral, semestral, anual
    responsable
    estado                → activo / inactivo
    fecha_creacion
}
```

#### indicadores_mediciones
```
indicadores_mediciones {
    id
    indicador_id
    periodo               → "2026-Q1", "2026-01", etc.
    valor_obtenido
    cumple_meta           → si / no (calculado)
    observaciones
    evidencia             → archivo adjunto
    fecha_registro
    registrado_por
}
```

---

## 6. Flujo de Aprobación y Firma

### 6.1 Flujo completo

```
Analista elabora documento
         │
         ▼
Consultor revisa cada sección
         │
         ▼
    ┌─────────────┐
    │  Preview    │ ← Vista previa del documento completo
    │  documento  │
    └─────────────┘
         │
         ▼
    ┌─────────────────────────────┐
    │ ¿Aprobar documento?         │
    │                             │
    │ "Esta acción generará la    │
    │  versión X.0 del documento" │
    │                             │
    │ [Cancelar]  [Aprobar]       │
    └─────────────────────────────┘
         │
         ▼
Consultor aprueba → Sistema registra firma Elaboró/Revisó
         │
         ▼
Se envía link al cliente (Alta Dirección)
         │
         ▼
Cliente firma electrónicamente (tipo DocuSeal)
         │
         ▼
Se genera PDF final con:
  - Documento completo
  - Firmas electrónicas
  - Evidencia de firma (fecha, hora, IP)
  - Versión registrada en BD
```

### 6.2 Datos que viajan al aprobar

Al aprobar un programa, automáticamente:

1. **Se guarda el documento** en `documentos` y `documento_secciones`
2. **Se crea versión** en `documento_versiones`
3. **El cronograma** (sección 8) se parsea y crea registros en `actividades_seguimiento`
4. **Los indicadores** (sección 9) se parsean y crean registros en `indicadores_programas`

---

## 7. Estructura de Indicadores

### 7.1 Campos estándar (para todos los programas)

| Campo | Ejemplo |
|-------|---------|
| Nombre del indicador | Cobertura de capacitación |
| Fórmula | (Trabajadores capacitados / Total trabajadores) x 100 |
| Meta | ≥ 90% |
| Frecuencia de medición | Trimestral |
| Responsable de medición | Responsable SG-SST |

### 7.2 Dashboard del consultor

El dashboard consolidado permite ver:

- Todos los indicadores de todos los programas de un cliente
- Estado de cumplimiento por periodo
- Indicadores vencidos (sin medición en el periodo correspondiente)
- Comparativo entre clientes
- Tendencias históricas

---

## 8. Pendientes para Parte 2

- [ ] Definir campos exactos del módulo contexto del cliente
- [ ] Estructurar prompts de IA para cada sección
- [ ] Definir si otros documentos (procedimientos, planes, políticas) siguen la misma lógica
- [ ] Detallar flujo técnico del módulo firma tipo DocuSeal
- [ ] Definir códigos estándar para cada tipo de documento
- [ ] Diseñar interfaces de usuario (wireframes)
- [ ] Definir reglas de negocio para cambios menores vs mayores

---

## 9. Referencias Normativas

- **Decreto 1072 de 2015** - Decreto Único Reglamentario del Sector Trabajo
- **Resolución 0312 de 2019** - Estándares Mínimos del SG-SST
- **ISO 9001:2015** - Cláusula 7.5 (Información documentada)
- **ISO 45001:2018** - Cláusula 7.5 (Información documentada)

---

*Documento generado: Enero 2026*
*Proyecto: EnterpriseSST - Módulo de Documentación*
