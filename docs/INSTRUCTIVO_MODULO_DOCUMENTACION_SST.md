# Instructivo del Módulo de Documentación SST

## Resolución 0312 de 2019 - Sistema de Gestión de Seguridad y Salud en el Trabajo

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Acceso al Módulo](#2-acceso-al-módulo)
3. [Contexto SST del Cliente](#3-contexto-sst-del-cliente)
4. [Catálogo de 60 Estándares](#4-catálogo-de-60-estándares)
5. [Documentación por Cliente](#5-documentación-por-cliente)
6. [Cumplimiento PHVA](#6-cumplimiento-phva)
7. [Plantillas de Documentos](#7-plantillas-de-documentos)
8. [Flujo de Trabajo Recomendado](#8-flujo-de-trabajo-recomendado)
9. [Preguntas Frecuentes](#9-preguntas-frecuentes)

---

## 1. Introducción

El **Módulo de Documentación SST** permite gestionar el cumplimiento de los 60 estándares mínimos establecidos en la Resolución 0312 de 2019 para el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.

### Características principales:

- Catálogo completo de los 60 estándares organizados por ciclo PHVA
- Seguimiento de cumplimiento por cliente
- Generación de documentos a partir de plantillas predefinidas
- Clasificación por niveles de riesgo (7, 21 o 60 estándares)
- Dashboard visual de cumplimiento
- Contexto SST personalizado por cliente

### Ciclo PHVA

Los estándares están organizados según el ciclo de mejora continua:

| Ciclo | Nombre | Descripción |
|-------|--------|-------------|
| **P** | PLANEAR | Definición de políticas, objetivos, recursos y planificación |
| **H** | HACER | Implementación de medidas de prevención y control |
| **V** | VERIFICAR | Seguimiento, medición, auditoría y revisión |
| **A** | ACTUAR | Acciones correctivas, preventivas y de mejora |

---

## 2. Acceso al Módulo

### Desde el Dashboard del Consultor

1. Inicie sesión en el sistema
2. Acceda al **Dashboard del Consultor**
3. Ubique la sección **"Documentación SST - Resolución 0312/2019"**
4. Seleccione uno de los botones disponibles:

| Botón | Color | Función |
|-------|-------|---------|
| Contexto SST | Morado | Configurar información base del cliente |
| Catálogo 60 Estándares | Verde | Ver todos los estándares de referencia |
| Documentación por Cliente | Azul | Gestionar documentos de un cliente |
| Cumplimiento PHVA | Naranja | Ver estado de cumplimiento por cliente |
| Plantillas Documentos | Púrpura | Acceder a plantillas predefinidas |

### Selector de Clientes

Todos los módulos incluyen un **selector con búsqueda** (Select2):
- Busque por **nombre del cliente** o **NIT**
- Solo se muestran **clientes activos**
- Todos los consultores pueden ver todos los clientes

---

## 3. Contexto SST del Cliente

### ¿Qué es?

Es la información base de cada cliente que se utiliza para:
- Generación de documentos personalizados con IA
- Cálculo de estándares aplicables (7, 21 o 60)
- Identificación de peligros para programas de prevención
- Configuración de firmantes de documentos

### URL de acceso
```
/contexto                    → Selector de cliente
/contexto/{id_cliente}       → Formulario de contexto
```

### Secciones del formulario:

#### 1. Datos de la Empresa (solo lectura)
- Razón Social, NIT, Ciudad
- Representante Legal
- Actividad Económica

#### 2. Clasificación Empresarial
- **Sector Económico:** Seleccione el sector
- **Niveles de Riesgo ARL:** Seleccione TODOS los niveles que aplican (checkboxes)
  - Una empresa puede tener múltiples riesgos (ej: administrativos I, escoltas V)
- **Estándares Aplicables:** 7, 21 o 60 (definido manualmente por el consultor)
- **ARL Actual:** Seleccione la ARL

#### 3. Tamaño y Estructura
- Total de trabajadores (requerido)
- Trabajadores directos, temporales, contratistas
- Número de sedes
- Turnos de trabajo (checkboxes)

#### 4. Información SST
- **Responsable del SG-SST:** Seleccione un consultor del sistema
  - Los datos (cédula, licencia) se cargan automáticamente
- **Órganos de Participación:**
  - COPASST
  - Vigía SST
  - Comité de Convivencia
  - Brigada de Emergencias

#### 5. Peligros Identificados
Seleccione los peligros presentes en la empresa por categoría:
- Físicos
- Químicos
- Biológicos
- Biomecánicos
- Psicosociales
- Condiciones de Seguridad
- Fenómenos Naturales

#### 6. Firmantes de Documentos
- Toggle para activar Delegado SST
- Datos del Delegado SST (si aplica)
- Datos del Representante Legal

---

## 4. Catálogo de 60 Estándares

### ¿Qué es?

Es una vista de referencia que muestra los 60 estándares mínimos del SG-SST organizados por ciclo PHVA y categoría.

### URL de acceso
```
/estandares/catalogo
```

### Información mostrada por cada estándar:

- **Código:** Número del estándar (ej: 1.1.1, 2.1.1)
- **Nombre:** Descripción del estándar
- **Peso:** Porcentaje en la calificación total
- **Nivel:** Indica si aplica para 7, 21 o 60 estándares

### Niveles de aplicación

| Nivel | Aplica a | Criterio |
|-------|----------|----------|
| 7 estándares | Empresas ≤10 trabajadores, riesgo I-III | Nivel básico |
| 21 estándares | Empresas 11-50 trabajadores, riesgo I-III | Nivel intermedio |
| 60 estándares | Empresas >50 trabajadores o riesgo IV-V | Nivel completo |

### Categorías por ciclo PHVA

**PLANEAR:**
- I. Recursos
- II. Gestión Integral del SG-SST

**HACER:**
- III. Gestión de la Salud
- IV. Gestión de Peligros y Riesgos
- V. Gestión de Amenazas

**VERIFICAR:**
- VI. Verificación del SG-SST

**ACTUAR:**
- VII. Mejoramiento

---

## 5. Documentación por Cliente

### ¿Qué es?

Permite gestionar todos los documentos SST de un cliente específico, organizados en carpetas según el tipo de documento.

### URL de acceso
```
/documentacion                    → Selector de cliente
/documentacion/{id_cliente}       → Dashboard de documentos del cliente
```

### Flujo de uso:

1. **Seleccionar cliente:** Busque por nombre o NIT en el selector
2. **Ver dashboard:** Se visualizan las carpetas de documentos y estadísticas
3. **Navegar carpetas:** Acceder a documentos por tipo (Políticas, Programas, etc.)
4. **Crear documentos:** Usar plantillas para generar nuevos documentos

### Tipos de documentos disponibles:

| Código | Tipo | Descripción |
|--------|------|-------------|
| POL | Política | Declaraciones de compromiso de la dirección |
| PRG | Programa | Programas de gestión (capacitación, PVE, etc.) |
| PLA | Plan | Planes de trabajo y emergencias |
| PRO | Procedimiento | Procedimientos operativos |
| MAN | Manual | Manuales del sistema |
| FOR | Formato | Formatos y registros |
| PRO | Protocolo | Protocolos de actuación |

### Acciones disponibles:

- **Ver documento:** Consultar contenido y metadatos
- **Editar documento:** Modificar contenido (si tiene permisos)
- **Descargar PDF:** Exportar documento en formato PDF
- **Solicitar firma:** Enviar a firma electrónica
- **Ver historial:** Consultar versiones anteriores

---

## 6. Cumplimiento PHVA

### ¿Qué es?

Dashboard que muestra el estado de cumplimiento de los 60 estándares para un cliente específico, con indicadores visuales y estadísticas.

### URL de acceso
```
/estandares/seleccionar-cliente   → Selector de cliente
/estandares/{id_cliente}          → Dashboard de cumplimiento
```

### Indicadores mostrados:

1. **Cumplimiento General:** Porcentaje ponderado según pesos de cada estándar
2. **Resumen por estado:**
   - Cumple (verde)
   - En proceso (amarillo)
   - No cumple (rojo)
   - No aplica (gris)

3. **Vista por ciclo PHVA:** Progreso de cada fase del ciclo

### Estados de cumplimiento:

| Estado | Descripción | Color |
|--------|-------------|-------|
| `cumple` | Estándar completamente implementado | Verde |
| `en_proceso` | Implementación en curso | Amarillo |
| `no_cumple` | Sin implementar | Rojo |
| `no_aplica` | No aplica según nivel de riesgo | Gris |

### Cómo actualizar el estado de un estándar:

1. Localice el estándar en la lista
2. Haga clic en el estándar para ver detalles
3. Seleccione el nuevo estado
4. Agregue observaciones si es necesario
5. Guarde los cambios

### Inicialización de estándares:

Cuando un cliente es nuevo, debe inicializar sus estándares:

1. Acceda al dashboard de cumplimiento del cliente
2. Si no hay estándares, aparecerá el botón "Inicializar Estándares"
3. El sistema creará los 60 registros con estado inicial "no_cumple"
4. Los estándares que no apliquen según el nivel se marcarán automáticamente

---

## 7. Plantillas de Documentos

### ¿Qué es?

Catálogo de plantillas predefinidas para generar documentos SST con estructura estandarizada.

### URL de acceso
```
/documentacion/plantillas
```

### Plantillas disponibles:

#### Políticas (POL)
- Política de Seguridad y Salud en el Trabajo
- Política de No Alcohol, Drogas y Tabaco

#### Programas (PRG)
- Programa de Capacitación y Entrenamiento SST
- Programa de Vigilancia Epidemiológica

#### Planes (PLA)
- Plan de Trabajo Anual SST
- Plan de Emergencias

#### Procedimientos (PRO)
- Procedimiento de Identificación de Peligros y Valoración de Riesgos (IPVR)
- Procedimiento de Reporte e Investigación de Incidentes y Accidentes

### Estructura de una plantilla:

Cada plantilla contiene:
- **Secciones predefinidas:** Estructura del documento
- **Código sugerido:** Prefijo para el código del documento
- **Variables de contexto:** Datos del cliente que se insertan automáticamente

### Cómo usar una plantilla:

1. Acceda a **Plantillas de Documentos**
2. Seleccione la plantilla deseada
3. Haga clic en "Usar Plantilla"
4. Seleccione el cliente
5. Complete la información requerida
6. El sistema generará el documento con la estructura predefinida

---

## 8. Flujo de Trabajo Recomendado

### Para un cliente nuevo:

```
1. CONFIGURACIÓN INICIAL
   ├── Crear cliente en el sistema
   ├── Configurar Contexto SST
   │   ├── Definir niveles de riesgo ARL
   │   ├── Seleccionar estándares aplicables (7, 21 o 60)
   │   ├── Asignar consultor responsable
   │   └── Identificar peligros
   └── Inicializar estándares del cliente

2. DIAGNÓSTICO
   ├── Acceder a "Cumplimiento PHVA"
   ├── Revisar todos los estándares
   └── Marcar estado actual de cada uno

3. DOCUMENTACIÓN
   ├── Identificar documentos faltantes
   ├── Usar plantillas para crear documentos
   ├── Subir documentos existentes
   └── Vincular documentos a estándares

4. SEGUIMIENTO
   ├── Actualizar estados periódicamente
   ├── Revisar indicadores de cumplimiento
   └── Generar reportes de avance
```

### Ciclo de mejora continua:

```
PLANEAR → HACER → VERIFICAR → ACTUAR
    ↑                            │
    └────────────────────────────┘
```

---

## 9. Preguntas Frecuentes

### ¿Cómo sé qué nivel de estándares aplica a mi cliente?

El nivel lo define **manualmente el consultor** en el Contexto SST, considerando:
- **Número de trabajadores**
- **Nivel de riesgo** de la actividad económica principal

| Trabajadores | Riesgo I-III | Riesgo IV-V |
|--------------|--------------|-------------|
| ≤10 | 7 estándares | 60 estándares |
| 11-50 | 21 estándares | 60 estándares |
| >50 | 60 estándares | 60 estándares |

### ¿Una empresa puede tener varios niveles de riesgo?

Sí. En el Contexto SST puede seleccionar **múltiples niveles de riesgo ARL** usando checkboxes.

Ejemplo: Una empresa de seguridad privada puede tener:
- Riesgo I: Personal administrativo
- Riesgo II: Servicios generales
- Riesgo III: Comerciales
- Riesgo V: Escoltas

### ¿Quién define los estándares aplicables?

El **consultor** define manualmente si aplican 7, 21 o 60 estándares, según el análisis del cliente.

### ¿Puedo personalizar las plantillas?

Sí, los administradores pueden:
- Modificar la estructura de secciones
- Ajustar los prompts de generación
- Crear nuevas plantillas basadas en existentes

### ¿Cómo se calcula el porcentaje de cumplimiento?

El cumplimiento ponderado considera el **peso porcentual** de cada estándar:

```
Cumplimiento = Σ(peso de estándares cumplidos) / Σ(peso total aplicable) × 100
```

### ¿Qué pasa si un estándar no aplica?

Marque el estándar como "No Aplica". Este no se considerará en el cálculo del porcentaje de cumplimiento.

### ¿Puedo exportar el reporte de cumplimiento?

Sí, desde el dashboard de cumplimiento puede:
- Exportar a PDF
- Exportar a Excel
- Generar reporte para auditoría

### ¿Todos los consultores ven todos los clientes?

Sí. Para garantizar continuidad operativa, todos los consultores pueden acceder a todos los clientes activos.

---

## Soporte

Para dudas o problemas técnicos:
- Revise este instructivo
- Contacte al administrador del sistema
- Reporte errores en el sistema de tickets

---

**Versión del documento:** 1.1
**Última actualización:** Enero 2026
**Módulo:** Documentación SST - EnterpriseSST
