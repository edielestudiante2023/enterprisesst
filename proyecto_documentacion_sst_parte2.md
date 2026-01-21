# Proyecto de Documentación SST - Parte 2

## Resumen

Continuación de la Parte 1. Define los detalles de implementación del sistema de generación documental.

---

## 1. Campos del Módulo Contexto del Cliente

### 1.1 Datos básicos de la empresa

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Razón social | texto | ✅ | Encabezados, documentos |
| NIT | texto | ✅ | Identificación |
| Dirección principal | texto | ✅ | Documentos, emergencias |
| Ciudad/Municipio | texto | ✅ | Normativa local |
| Departamento | texto | ✅ | ARL, normativa |
| Teléfono | texto | ✅ | Contacto |
| Correo electrónico | texto | ✅ | Notificaciones |
| Representante legal | texto | ✅ | Firmas, aprobaciones |
| Cédula rep. legal | texto | ✅ | Firmas |
| Cargo rep. legal | texto | ✅ | Documentos |

### 1.2 Clasificación empresarial

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Actividad económica principal | texto + código CIIU | ✅ | Peligros, normativa |
| Actividad económica secundaria | texto + código CIIU | ⬜ | Peligros adicionales |
| Sector económico | select | ✅ | Contexto sectorial |
| Nivel de riesgo ARL | select (I-V) | ✅ | **Determina estándares** |
| ARL actual | select | ✅ | Reportes, afiliación |
| Clase de riesgo cotización | select (1-5) | ✅ | Cálculos |

### 1.3 Tamaño y estructura

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Total trabajadores | número | ✅ | **Determina estándares** |
| Trabajadores directos | número | ✅ | Cobertura programas |
| Trabajadores temporales | número | ⬜ | Cobertura programas |
| Contratistas permanentes | número | ⬜ | Alcance SG-SST |
| Número de sedes | número | ✅ | Alcance, emergencias |
| Turnos de trabajo | multi-select | ✅ | Programas, brigadas |

### 1.4 Sedes (tabla relacionada)

| Campo | Tipo | Obligatorio |
|-------|------|-------------|
| Nombre sede | texto | ✅ |
| Dirección | texto | ✅ |
| Ciudad | texto | ✅ |
| Trabajadores en sede | número | ✅ |
| Actividades principales | texto | ✅ |
| Es sede principal | boolean | ✅ |

### 1.5 Información SST

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Responsable SG-SST | texto | ✅ | Firmas, responsabilidades |
| Cargo responsable | texto | ✅ | Documentos |
| Licencia SST número | texto | ✅ | Validación |
| Licencia SST vigencia | fecha | ✅ | Alertas |
| Tiene COPASST | boolean | ✅ | Documentos requeridos |
| Tiene Vigía SST | boolean | ✅ | Alternativa a COPASST |
| Tiene Comité Convivencia | boolean | ✅ | Documentos requeridos |
| Tiene brigada emergencias | boolean | ✅ | Plan emergencias |

### 1.6 Peligros identificados (multi-select)

| Categoría | Ejemplos |
|-----------|----------|
| Físicos | Ruido, iluminación, vibración, temperaturas |
| Químicos | Gases, vapores, material particulado, líquidos |
| Biológicos | Virus, bacterias, hongos, parásitos |
| Biomecánicos | Postura, movimiento repetitivo, manipulación cargas |
| Psicosociales | Carga mental, jornada, relaciones, estrés |
| Condiciones de seguridad | Mecánico, eléctrico, locativo, trabajo en alturas |
| Fenómenos naturales | Sismo, inundación, vendaval |

### 1.7 Cálculo automático de estándares

```
SI (trabajadores <= 10) Y (riesgo IN [I, II, III]) ENTONCES
    estandares_aplicables = 7
SINO SI (trabajadores >= 11 Y trabajadores <= 50) Y (riesgo IN [I, II, III]) ENTONCES
    estandares_aplicables = 21
SINO
    estandares_aplicables = 60
FIN SI
```

### 1.8 Historial de contexto

El sistema debe guardar historial cuando cambian datos críticos:

```
cliente_contexto_historial {
    id
    cliente_id
    campo_modificado      → "total_trabajadores"
    valor_anterior        → "8"
    valor_nuevo           → "35"
    fecha_cambio
    usuario_id
    impacto               → "Cambio de 7 a 21 estándares"
}
```

---

## 2. Prompts de IA para Generación de Documentos

### 2.1 Estructura del prompt base

```
CONTEXTO DEL CLIENTE:
- Empresa: {razon_social}
- NIT: {nit}
- Actividad económica: {actividad_economica}
- Nivel de riesgo: {nivel_riesgo}
- Total trabajadores: {total_trabajadores}
- Sedes: {lista_sedes}
- Peligros identificados: {peligros}
- Responsable SG-SST: {responsable_sst}

DOCUMENTO A GENERAR:
- Tipo: {tipo_documento}
- Nombre: {nombre_documento}
- Sección actual: {numero_seccion} - {nombre_seccion}

CONTEXTO ADICIONAL DEL USUARIO:
{contexto_adicional}

INSTRUCCIONES:
Genera el contenido para la sección "{nombre_seccion}" del documento "{nombre_documento}".
El texto debe ser específico para esta empresa, usando sus datos reales.
Usa lenguaje técnico apropiado para documentos SG-SST.
No uses frases genéricas como "la empresa" - usa el nombre real.
```

### 2.2 Prompts específicos por sección (Programas - 13 secciones)

#### Sección 1: Introducción
```
Genera una introducción de 2-3 párrafos para el {nombre_programa} de {razon_social}.

Debe incluir:
- Justificación de por qué la empresa necesita este programa
- Contexto de la actividad económica ({actividad_economica}) y sus riesgos
- Mención del marco normativo aplicable (Decreto 1072/2015, Resolución 0312/2019)
- Compromiso de la alta dirección

Longitud: 150-250 palabras
```

#### Sección 2: Objetivos
```
Genera los objetivos para el {nombre_programa} de {razon_social}.

Estructura:
OBJETIVO GENERAL:
- Un objetivo medible y alcanzable relacionado con el programa

OBJETIVOS ESPECÍFICOS:
- 3-5 objetivos que contribuyan al objetivo general
- Deben ser SMART (Específicos, Medibles, Alcanzables, Relevantes, Temporales)
- Relacionados con los peligros identificados: {peligros_relevantes}
```

#### Sección 3: Alcance
```
Define el alcance del {nombre_programa} para {razon_social}.

Debe especificar:
- A quién aplica (trabajadores directos, contratistas, visitantes)
- Áreas o procesos cubiertos
- Sedes incluidas: {lista_sedes}
- Exclusiones si las hay

Formato: Lista con viñetas, máximo 10 ítems
```

#### Sección 4: Marco normativo
```
Lista el marco normativo aplicable al {nombre_programa}.

Incluir obligatoriamente:
- Decreto 1072 de 2015 (artículos específicos)
- Resolución 0312 de 2019 (estándares relacionados)
- Normas específicas según el tipo de programa

Para actividad económica "{actividad_economica}" considerar normas sectoriales.

Formato: Tabla con columnas [Norma | Descripción | Aplicación]
```

#### Sección 5: Definiciones
```
Genera un glosario de términos técnicos para el {nombre_programa}.

Incluir:
- Términos técnicos del programa (mínimo 8, máximo 15)
- Definiciones basadas en normativa colombiana
- Términos específicos de la actividad económica si aplica

Formato: Lista alfabética [Término: Definición]
```

#### Sección 6: Diagnóstico o línea base
```
Genera la estructura de diagnóstico inicial para el {nombre_programa} de {razon_social}.

Considerando:
- Peligros identificados: {peligros_relevantes}
- Número de trabajadores expuestos: {trabajadores_expuestos}
- Condiciones actuales conocidas

Estructura:
1. Estado actual (qué se tiene)
2. Brechas identificadas (qué falta)
3. Priorización de intervenciones

Nota: Indicar "[COMPLETAR CON DATOS REALES]" donde se requiera información específica del diagnóstico.
```

#### Sección 7: Actividades
```
Genera el listado de actividades para el {nombre_programa} de {razon_social}.

Las actividades deben:
- Ser específicas y ejecutables
- Tener responsable asignable
- Poder medirse o verificarse
- Estar alineadas con los objetivos del programa

Cantidad: 8-15 actividades
Formato: Tabla [# | Actividad | Responsable | Frecuencia | Entregable]
```

#### Sección 8: Cronograma
```
Genera el cronograma anual para el {nombre_programa}.

Basado en las actividades de la sección anterior.
Distribuir actividades en los 12 meses del año.
Considerar:
- Actividades de inicio (primeros 3 meses)
- Actividades recurrentes (trimestral, semestral)
- Actividades de cierre (último trimestre)

Formato: Tabla con meses como columnas y actividades como filas
Marcar con "X" los meses de ejecución
```

#### Sección 9: Indicadores
```
Define los indicadores de gestión para el {nombre_programa}.

Cada indicador debe tener:
- Nombre del indicador
- Fórmula de cálculo
- Meta (valor objetivo)
- Frecuencia de medición
- Responsable de medición
- Fuente de datos

Incluir mínimo:
- 1 indicador de estructura (recursos)
- 1 indicador de proceso (ejecución)
- 1 indicador de resultado (impacto)
```

#### Sección 10: Responsables
```
Define los roles y responsabilidades para el {nombre_programa} de {razon_social}.

Roles a incluir:
- Alta dirección / Representante legal
- Responsable del SG-SST: {responsable_sst}
- COPASST / Vigía SST
- Trabajadores
- Otros roles específicos del programa

Formato: Tabla [Rol | Responsabilidades específicas]
```

#### Sección 11: Recursos
```
Identifica los recursos necesarios para el {nombre_programa}.

Categorías:
1. Recursos humanos (personal, competencias)
2. Recursos técnicos (equipos, herramientas)
3. Recursos financieros (presupuesto estimado)
4. Recursos de infraestructura

Ser específico para {razon_social} y su actividad económica.
```

#### Sección 12: Seguimiento y evaluación
```
Define el mecanismo de seguimiento y evaluación del {nombre_programa}.

Incluir:
- Frecuencia de seguimiento (mensual, trimestral)
- Responsable del seguimiento
- Herramienta de seguimiento (formato, sistema)
- Criterios de evaluación
- Acciones ante incumplimientos
- Revisión anual del programa
```

#### Sección 13: Registros asociados
```
Lista los registros y formatos asociados al {nombre_programa}.

Para cada registro indicar:
- Código del formato
- Nombre del formato
- Responsable de diligenciamiento
- Frecuencia
- Tiempo de retención

Usar códigos estándar del sistema EnterpriseSST.
```

### 2.3 Parámetros de generación

| Parámetro | Valor recomendado |
|-----------|-------------------|
| Temperatura | 0.3 (consistente, formal) |
| Max tokens | 1500 por sección |
| Modelo | Claude Sonnet (balance costo/calidad) |

---

## 3. Lógica para Otros Tipos de Documentos

### 3.1 Matriz de estructuras por tipo

| Tipo documento | Secciones | Usa 13 secciones | Estructura especial |
|----------------|-----------|------------------|---------------------|
| Programa | 13 | ✅ | Estándar |
| Procedimiento | 8 | ❌ | Ver 3.2 |
| Plan | 10 | ❌ | Ver 3.3 |
| Política | 5 | ❌ | Ver 3.4 |
| Manual | Variable | ❌ | Ver 3.5 |
| Protocolo | 7 | ❌ | Ver 3.6 |
| Formato | N/A | ❌ | Solo campos |

### 3.2 Estructura de Procedimiento (8 secciones)

| # | Sección | Descripción |
|---|---------|-------------|
| 1 | Objetivo | Qué busca el procedimiento |
| 2 | Alcance | A quién y dónde aplica |
| 3 | Definiciones | Términos clave |
| 4 | Responsables | Quién ejecuta cada paso |
| 5 | Descripción del procedimiento | Pasos detallados (flujograma) |
| 6 | Documentos relacionados | Formatos, otros procedimientos |
| 7 | Control de cambios | Historial de versiones |
| 8 | Anexos | Diagramas, tablas de apoyo |

### 3.3 Estructura de Plan (10 secciones)

| # | Sección | Descripción |
|---|---------|-------------|
| 1 | Introducción | Contexto del plan |
| 2 | Objetivos | General y específicos |
| 3 | Alcance | Cobertura del plan |
| 4 | Marco normativo | Requisitos legales |
| 5 | Diagnóstico | Situación actual |
| 6 | Metas | Resultados esperados (cuantificables) |
| 7 | Actividades y cronograma | Qué, cuándo, quién |
| 8 | Presupuesto | Recursos financieros |
| 9 | Indicadores | Medición de cumplimiento |
| 10 | Seguimiento | Mecanismo de control |

### 3.4 Estructura de Política (5 secciones)

| # | Sección | Descripción |
|---|---------|-------------|
| 1 | Declaración | Compromiso de la alta dirección |
| 2 | Objetivos de la política | Qué busca lograr |
| 3 | Alcance | A quién aplica |
| 4 | Compromisos | Puntos específicos (8-12 ítems) |
| 5 | Comunicación y revisión | Cómo se difunde, cuándo se revisa |

### 3.5 Estructura de Manual SG-SST (capítulos variables)

| Capítulo | Contenido |
|----------|-----------|
| 1 | Información de la empresa |
| 2 | Política y objetivos SST |
| 3 | Organización del SG-SST |
| 4 | Planificación |
| 5 | Aplicación (Hacer) |
| 6 | Verificación |
| 7 | Mejora continua |
| 8 | Documentos y registros |

### 3.6 Estructura de Protocolo (7 secciones)

| # | Sección | Descripción |
|---|---------|-------------|
| 1 | Objetivo | Propósito del protocolo |
| 2 | Alcance | Situaciones donde aplica |
| 3 | Definiciones | Términos técnicos |
| 4 | Condiciones generales | Requisitos previos |
| 5 | Desarrollo | Pasos a seguir |
| 6 | Registros | Evidencias generadas |
| 7 | Referencias | Normativa, bibliografía |

---

## 4. Flujo Técnico de Firma Electrónica

### 4.1 Arquitectura del módulo de firma

```
┌─────────────────────────────────────────────────────────────────┐
│                    MÓDULO DE FIRMA ELECTRÓNICA                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │  Documento  │───►│  Generador  │───►│    PDF      │         │
│  │  Aprobado   │    │    PDF      │    │   Base      │         │
│  └─────────────┘    └─────────────┘    └──────┬──────┘         │
│                                               │                 │
│                                               ▼                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │              PROCESO DE FIRMA INTERNA                    │   │
│  │                                                          │   │
│  │  1. Analista (Elaboró)  ──► Firma en sistema            │   │
│  │  2. Consultor (Revisó)  ──► Firma en sistema            │   │
│  │                                                          │   │
│  │  Datos capturados:                                       │   │
│  │  - Usuario ID                                            │   │
│  │  - Fecha/hora UTC                                        │   │
│  │  - IP de origen                                          │   │
│  │  - Hash del documento                                    │   │
│  └─────────────────────────────────────────────────────────┘   │
│                          │                                      │
│                          ▼                                      │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │              PROCESO DE FIRMA EXTERNA (Cliente)          │   │
│  │                                                          │   │
│  │  1. Sistema genera token único                           │   │
│  │  2. Envía email al cliente con link                      │   │
│  │  3. Cliente accede a vista de firma                      │   │
│  │  4. Cliente revisa documento                             │   │
│  │  5. Cliente firma (canvas o typed)                       │   │
│  │  6. Sistema captura evidencia                            │   │
│  │  7. Se genera PDF final                                  │   │
│  └─────────────────────────────────────────────────────────┘   │
│                          │                                      │
│                          ▼                                      │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    PDF FINAL FIRMADO                     │   │
│  │                                                          │   │
│  │  - Documento completo                                    │   │
│  │  - Página de firmas con evidencia                        │   │
│  │  - Metadatos de firma embebidos                          │   │
│  │  - Código QR de verificación                             │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Modelo de datos para firmas

```
firma_solicitudes {
    id
    documento_id
    documento_version_id
    token                 → UUID único para el link
    estado                → pendiente, firmado, expirado, rechazado
    fecha_creacion
    fecha_expiracion      → token válido por X días
    fecha_firma
    firmante_tipo         → elaboro, reviso, aprobo
    firmante_interno_id   → usuario del sistema (si aplica)
    firmante_email        → email del firmante externo
    firmante_nombre       → nombre del firmante
    firmante_cargo        → cargo del firmante
    firmante_documento    → cédula/NIT
}

firma_evidencias {
    id
    solicitud_id
    ip_address
    user_agent
    fecha_hora_utc
    geolocalizacion       → opcional
    tipo_firma            → draw (canvas), type (texto), upload (imagen)
    firma_imagen          → base64 o ruta de la firma
    hash_documento        → SHA-256 del PDF al momento de firmar
    aceptacion_terminos   → true (checkbox obligatorio)
}

firma_audit_log {
    id
    solicitud_id
    evento                → email_enviado, link_abierto, documento_visto, firma_iniciada, firma_completada
    fecha_hora
    ip_address
    detalles              → JSON con info adicional
}
```

### 4.3 Flujo del firmante externo (Cliente)

```
1. RECIBE EMAIL
   ┌────────────────────────────────────────────────────────┐
   │  Asunto: Documento pendiente de firma - {nombre_doc}   │
   │                                                        │
   │  Estimado/a {nombre_firmante},                         │
   │                                                        │
   │  {razon_social_consultora} le envía el documento       │
   │  "{nombre_documento}" para su revisión y firma.        │
   │                                                        │
   │  [REVISAR Y FIRMAR DOCUMENTO]                          │
   │                                                        │
   │  Este enlace expira en 7 días.                         │
   └────────────────────────────────────────────────────────┘

2. ABRE LINK → Vista de firma
   ┌────────────────────────────────────────────────────────┐
   │  EnterpriseSST - Firma de Documento                    │
   ├────────────────────────────────────────────────────────┤
   │                                                        │
   │  Documento: PRG-CAP-001 v1.0                           │
   │  Programa de Capacitación                              │
   │  Empresa: {razon_social_cliente}                       │
   │                                                        │
   │  ┌──────────────────────────────────────────────────┐  │
   │  │                                                  │  │
   │  │           [VISTA PREVIA DEL PDF]                 │  │
   │  │                                                  │  │
   │  │           Página 1 de 15                         │  │
   │  │                                                  │  │
   │  └──────────────────────────────────────────────────┘  │
   │                                                        │
   │  [Descargar PDF]                                       │
   │                                                        │
   └────────────────────────────────────────────────────────┘

3. PROCESO DE FIRMA
   ┌────────────────────────────────────────────────────────┐
   │  Firmar como: Alta Dirección (Aprobó)                  │
   ├────────────────────────────────────────────────────────┤
   │                                                        │
   │  Nombre completo: [Juan Pérez García        ]          │
   │  Documento:       [1234567890               ]          │
   │  Cargo:           [Gerente General          ]          │
   │                                                        │
   │  Tipo de firma:   ○ Dibujar  ● Escribir  ○ Subir      │
   │                                                        │
   │  ┌──────────────────────────────────────────────────┐  │
   │  │                                                  │  │
   │  │     Juan Pérez García                            │  │
   │  │     ─────────────────                            │  │
   │  │                                                  │  │
   │  └──────────────────────────────────────────────────┘  │
   │                                                        │
   │  ☑ He leído y acepto el documento. Confirmo que       │
   │    esta firma tiene la misma validez que mi firma     │
   │    manuscrita (Ley 527 de 1999).                      │
   │                                                        │
   │  [Cancelar]                    [FIRMAR DOCUMENTO]      │
   └────────────────────────────────────────────────────────┘

4. CONFIRMACIÓN
   ┌────────────────────────────────────────────────────────┐
   │  ✓ Documento firmado exitosamente                      │
   │                                                        │
   │  Se ha enviado una copia del documento firmado         │
   │  a su correo electrónico.                              │
   │                                                        │
   │  Código de verificación: ABC123XYZ                     │
   │                                                        │
   │  [Descargar PDF firmado]  [Cerrar]                     │
   └────────────────────────────────────────────────────────┘
```

### 4.4 Página de certificación de firmas (última página del PDF)

```
┌─────────────────────────────────────────────────────────────────┐
│                  CERTIFICADO DE FIRMAS ELECTRÓNICAS             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Documento: PRG-CAP-001 - Programa de Capacitación              │
│  Versión: 1.0                                                   │
│  Hash SHA-256: a1b2c3d4e5f6...                                  │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ELABORÓ                                                  │   │
│  │ Nombre: María García López                               │   │
│  │ Cargo: Analista SST                                      │   │
│  │ Fecha: 2026-01-20 14:32:15 UTC-5                         │   │
│  │ IP: 192.168.1.100                                        │   │
│  │ [Firma digital]                                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ REVISÓ                                                   │   │
│  │ Nombre: Carlos Rodríguez M.                              │   │
│  │ Cargo: Consultor SST - Lic. 12345                        │   │
│  │ Fecha: 2026-01-20 16:45:22 UTC-5                         │   │
│  │ IP: 192.168.1.105                                        │   │
│  │ [Firma digital]                                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ APROBÓ                                                   │   │
│  │ Nombre: Juan Pérez García                                │   │
│  │ Cargo: Gerente General                                   │   │
│  │ Documento: CC 1234567890                                 │   │
│  │ Fecha: 2026-01-21 09:15:03 UTC-5                         │   │
│  │ IP: 181.52.xxx.xxx                                       │   │
│  │ [Firma digital]                                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────┐                                                   │
│  │ [QR]     │  Verificar autenticidad:                         │
│  │          │  https://enterprisesst.com/verify/ABC123XYZ       │
│  └──────────┘                                                   │
│                                                                 │
│  Este documento fue firmado electrónicamente conforme a la      │
│  Ley 527 de 1999 y el Decreto 2364 de 2012 de Colombia.         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 4.5 Marco legal de firma electrónica en Colombia

| Norma | Aplicación |
|-------|------------|
| Ley 527 de 1999 | Define y reglamenta mensajes de datos y firma electrónica |
| Decreto 2364 de 2012 | Reglamenta firma electrónica (no requiere certificado digital) |
| Ley 1581 de 2012 | Protección de datos personales |

> **Importante:** La firma electrónica simple (la que implementamos) tiene validez legal en Colombia. No se requiere firma digital certificada para documentos internos del SG-SST.

---

## 5. Códigos Estándar por Tipo de Documento

### 5.1 Estructura del código

```
[TIPO]-[TEMA]-[CONSECUTIVO]

Ejemplos:
PRG-CAP-001  → Programa de Capacitación, versión 1
PRO-INV-001  → Procedimiento de Investigación, versión 1
POL-SST-001  → Política de SST, versión 1
```

### 5.2 Prefijos por tipo de documento

| Tipo | Prefijo | Ejemplo |
|------|---------|---------|
| Política | POL | POL-SST-001 |
| Objetivo | OBJ | OBJ-SST-001 |
| Programa | PRG | PRG-CAP-001 |
| Plan | PLA | PLA-EME-001 |
| Procedimiento | PRO | PRO-INV-001 |
| Protocolo | PRT | PRT-BIO-001 |
| Manual | MAN | MAN-SST-001 |
| Informe | INF | INF-AUD-001 |
| Formato | FOR | FOR-ASI-001 |
| Instructivo | INS | INS-EPP-001 |
| Guía | GUA | GUA-ERG-001 |
| Matriz | MTZ | MTZ-PEL-001 |
| Acta | ACT | ACT-COP-001 |

### 5.3 Códigos de tema (segundo segmento)

| Código | Tema | Documentos relacionados |
|--------|------|-------------------------|
| SST | General SG-SST | Política, Manual, Objetivos |
| CAP | Capacitación | Programa, cronograma, registros |
| EME | Emergencias | Plan, brigadas, simulacros |
| INV | Investigación | Procedimiento, formatos |
| INS | Inspecciones | Programa, listas de chequeo |
| EPP | Elementos protección | Matriz, procedimiento entrega |
| PEL | Peligros y riesgos | Matriz, valoración |
| MED | Medicina preventiva | Programa, exámenes |
| PVE | Vigilancia epidemiológica | Programas SVE |
| AUD | Auditoría | Plan, informes |
| IND | Indicadores | Fichas, mediciones |
| COM | Comunicación | Procedimiento, plan |
| COP | COPASST | Actas, reglamento |
| CON | Convivencia | Comité, procedimiento |
| MAN | Mantenimiento | Programa, cronograma |
| ORD | Orden y aseo | Programa, inspecciones |
| ALT | Trabajo en alturas | Programa, permisos |
| QUI | Riesgo químico | Matriz, hojas seguridad |
| BIO | Riesgo biológico | Protocolo, EPP |
| PSI | Riesgo psicosocial | Programa, batería |
| ERG | Ergonomía | Programa, evaluaciones |
| VIA | Seguridad vial | PESV |
| LEG | Matriz legal | Requisitos, evaluación |

### 5.4 Consecutivo y versión

- El consecutivo (001, 002...) identifica documentos únicos del mismo tipo+tema
- La versión (v1.0, v1.1, v2.0) se maneja en el control documental, no en el código
- Ejemplo: PRG-CAP-001 siempre es "Programa de Capacitación", la versión cambia

### 5.5 Formatos: Códigos especiales

Los formatos tienen código adicional que indica a qué documento pertenecen:

```
FOR-[TEMA]-[CONSECUTIVO]-[SUFIJO]

Ejemplos:
FOR-CAP-001-ASI  → Formato asistencia (del Programa Capacitación)
FOR-CAP-001-EVA  → Formato evaluación (del Programa Capacitación)
FOR-INS-001-CHK  → Lista de chequeo (del Programa Inspecciones)
```

| Sufijo | Significado |
|--------|-------------|
| ASI | Asistencia |
| EVA | Evaluación |
| CHK | Lista chequeo |
| REG | Registro general |
| ACT | Acta |
| INF | Informe |
| PER | Permiso |
| ENT | Entrega |

---

## 6. Wireframes (Flujos principales)

### 6.1 Dashboard principal del cliente

```
┌─────────────────────────────────────────────────────────────────────────┐
│  EnterpriseSST    [Cliente: Afiancol Colombia ▼]    🔔 3    👤 Carlos R │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │
│  │ Estándares  │ │ Documentos  │ │ Actividades │ │ Indicadores │       │
│  │   18/21     │ │    45       │ │   8 pend.   │ │  2 vencidos │       │
│  │   86% ██░   │ │ ██████████░ │ │ █████░░░░░░ │ │ █░░░░░░░░░░ │       │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘       │
│                                                                         │
│  DOCUMENTACIÓN                                          [+ Nuevo doc ▼] │
│  ───────────────────────────────────────────────────────────────────── │
│  Vista: [📁 Carpetas]  [📋 Lista]  [📊 Kanban]                          │
│                                                                         │
│  📁 SG-SST 2026                                                         │
│  ├── 📁 1. PLANEAR                                                      │
│  │   ├── 📁 1.1 Recursos                                               │
│  │   │   ├── 📄 POL-SST-001 v1.0 ✓                    Aprobado         │
│  │   │   ├── 📄 PRG-CAP-001 v1.0 ✓                    Aprobado         │
│  │   │   └── 📄 PRG-CAP-002 v0.1 ⏳                   En revisión      │
│  │   └── 📁 1.2 Gestión Integral                                       │
│  │       └── 📄 PLA-TRA-001 v1.0 ✓                    Aprobado         │
│  ├── 📁 2. HACER                                                        │
│  │   ├── 📁 2.1 Gestión de Salud                     [4 documentos]    │
│  │   └── 📁 2.2 Peligros y Riesgos                   [6 documentos]    │
│  ├── 📁 3. VERIFICAR                                  [2 documentos]    │
│  └── 📁 4. ACTUAR                                     [1 documento]     │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 6.2 Generador de documentos

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Nuevo Documento                                              [X Cerrar]│
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  PASO 1 DE 3: Seleccionar tipo                                          │
│  ═══════════════════════════════                                        │
│                                                                         │
│  Tipo de documento:                                                     │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ ○ Política                                                       │   │
│  │ ○ Programa                    ← Más común                        │   │
│  │ ○ Procedimiento                                                  │   │
│  │ ○ Plan                                                           │   │
│  │ ○ Protocolo                                                      │   │
│  │ ○ Manual                                                         │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  Seleccione "Programa"...                                               │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ Programa de:                                                     │   │
│  │ ┌─────────────────────────────────────────────────────────────┐ │   │
│  │ │ ○ Capacitación                                              │ │   │
│  │ │ ○ Vigilancia Epidemiológica (PVE)                           │ │   │
│  │ │ ○ Medicina Preventiva                                       │ │   │
│  │ │ ○ Inspecciones                                              │ │   │
│  │ │ ○ Mantenimiento Preventivo                                  │ │   │
│  │ │ ○ Orden y Aseo                                              │ │   │
│  │ │ ○ EPP                                                       │ │   │
│  │ │ ○ Otro: [_________________________]                         │ │   │
│  │ └─────────────────────────────────────────────────────────────┘ │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│                                           [Cancelar]  [Continuar →]     │
└─────────────────────────────────────────────────────────────────────────┘
```

### 6.3 Editor de documento por secciones

```
┌─────────────────────────────────────────────────────────────────────────┐
│  PRG-CAP-001 - Programa de Capacitación                    [Vista previa]│
│  Cliente: Afiancol Colombia                                              │
├──────────────────────┬──────────────────────────────────────────────────┤
│                      │                                                   │
│  SECCIONES           │  SECCIÓN 2: OBJETIVOS                            │
│  ─────────────       │  ═══════════════════════════════════════════════ │
│                      │                                                   │
│  ✓ 1. Introducción   │  ┌─────────────────────────────────────────────┐ │
│  ● 2. Objetivos      │  │ OBJETIVO GENERAL                            │ │
│  ○ 3. Alcance        │  │                                             │ │
│  ○ 4. Marco normat.  │  │ Desarrollar las competencias en SST del     │ │
│  ○ 5. Definiciones   │  │ personal de Afiancol Colombia, garantizando │ │
│  ○ 6. Diagnóstico    │  │ una cobertura mínima del 90% de los         │ │
│  ○ 7. Actividades    │  │ trabajadores durante el año 2026.           │ │
│  ○ 8. Cronograma     │  │                                             │ │
│  ○ 9. Indicadores    │  │ OBJETIVOS ESPECÍFICOS                       │ │
│  ○ 10. Responsables  │  │                                             │ │
│  ○ 11. Recursos      │  │ 1. Realizar inducción en SST al 100% de    │ │
│  ○ 12. Seguimiento   │  │    los trabajadores nuevos en su primera   │ │
│  ○ 13. Registros     │  │    semana de ingreso.                       │ │
│                      │  │                                             │ │
│  ─────────────       │  │ 2. Ejecutar reinducción anual al 100%      │ │
│  Progreso: 1/13      │  │    del personal.                            │ │
│  ████░░░░░░░░░ 8%    │  │                                             │ │
│                      │  │ 3. Capacitar al COPASST en sus funciones   │ │
│                      │  │    durante el primer trimestre.             │ │
│                      │  │                                             │ │
│                      │  └─────────────────────────────────────────────┘ │
│                      │                                                   │
│                      │  Contexto adicional para regenerar:              │
│                      │  ┌─────────────────────────────────────────────┐ │
│                      │  │ Agregar objetivo sobre brigada de           │ │
│                      │  │ emergencias...                              │ │
│                      │  └─────────────────────────────────────────────┘ │
│                      │                                                   │
│                      │  [🔄 Regenerar]  [✏️ Editar manual]  [✓ Aprobar] │
│                      │                                                   │
├──────────────────────┴──────────────────────────────────────────────────┤
│  [← Anterior]        Guardado automático ✓        [Siguiente →]         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 6.4 Vista previa y aprobación final

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Vista Previa - PRG-CAP-001                                   [X Cerrar]│
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ ┌─────────────────────────────────────────────────────────────┐ │   │
│  │ │                     [LOGO EMPRESA]                          │ │   │
│  │ │                                                             │ │   │
│  │ │  PROGRAMA DE CAPACITACIÓN                                   │ │   │
│  │ │                                                             │ │   │
│  │ │  Código: PRG-CAP-001        Versión: 1.0                    │ │   │
│  │ │  Sistema: SG-SST            Estado: Borrador                │ │   │
│  │ │  Proceso: SST               Fecha: 2026-01-21               │ │   │
│  │ ├─────────────────────────────────────────────────────────────┤ │   │
│  │ │                                                             │ │   │
│  │ │  1. INTRODUCCIÓN                                            │ │   │
│  │ │                                                             │ │   │
│  │ │  El presente Programa de Capacitación de Afiancol           │ │   │
│  │ │  Colombia establece las directrices...                      │ │   │
│  │ │                                                             │ │   │
│  │ └─────────────────────────────────────────────────────────────┘ │   │
│  │                                                                 │   │
│  │  Página 1 de 15    [◄] [►]    [Zoom: 100% ▼]                   │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  ⚠️ APROBAR DOCUMENTO                                          │   │
│  │                                                                 │   │
│  │  Esta acción generará la VERSIÓN 1.0 del documento.            │   │
│  │                                                                 │   │
│  │  • Se registrará su firma como "Elaboró/Revisó"                │   │
│  │  • Se enviará al cliente para firma de aprobación              │   │
│  │  • El documento quedará en estado "Pendiente firma cliente"    │   │
│  │                                                                 │   │
│  │  Enviar solicitud de firma a:                                  │   │
│  │  [Juan Pérez - Gerente General ▼]                              │   │
│  │  Email: juan.perez@afiancol.com                                │   │
│  │                                                                 │   │
│  │  [Cancelar]                    [✓ Aprobar y enviar a firma]    │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  Exportar: [📄 PDF borrador]  [📝 Word]                                 │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 6.5 Panel de transición de estándares

```
┌─────────────────────────────────────────────────────────────────────────┐
│  ⚠️ Cambio de Nivel Detectado - Afiancol Colombia                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  El cliente ha actualizado su información:                              │
│                                                                         │
│  ┌───────────────────────┐         ┌───────────────────────┐           │
│  │  ANTES                │         │  AHORA                │           │
│  │  ─────────────────    │   ──►   │  ─────────────────    │           │
│  │  8 trabajadores       │         │  35 trabajadores      │           │
│  │  Riesgo II            │         │  Riesgo II            │           │
│  │  7 estándares         │         │  21 estándares        │           │
│  └───────────────────────┘         └───────────────────────┘           │
│                                                                         │
│  DIAGNÓSTICO DE BRECHA                                                  │
│  ═════════════════════════════════════════════════════════════════════ │
│                                                                         │
│  Estándares actuales:     ████████████████████░░░░░░░░░  7/21 (33%)    │
│  Estándares nuevos:       14 estándares adicionales requeridos          │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ #  │ Estándar                           │ Documento    │ Estado │   │
│  ├────┼────────────────────────────────────┼──────────────┼────────┤   │
│  │ 8  │ Programa de capacitación anual     │ PRG-CAP      │ ⬜ Pend│   │
│  │ 9  │ Inducción y reinducción en SST     │ PRO-IND      │ ⬜ Pend│   │
│  │ 10 │ Curso virtual 50 horas             │ Registro     │ ⬜ Pend│   │
│  │ 11 │ COPASST conformado y funcionando   │ ACT-COP      │ ⬜ Pend│   │
│  │ ...│ ...                                │ ...          │ ...    │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  [Ver todos los 14 estándares nuevos]                                   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  ACCIONES RECOMENDADAS                                          │   │
│  │                                                                 │   │
│  │  [📋 Generar plan de transición]  Crea cronograma de 90 días   │   │
│  │  [📄 Crear documentos faltantes]  Inicia generación con IA     │   │
│  │  [⏰ Recordar después]            Posponer esta alerta          │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 7. Reglas de Cambios Menores vs Mayores

### 7.1 Definición

| Tipo | Impacto | Versión | Requiere |
|------|---------|---------|----------|
| **Menor** | No afecta el fondo del documento | X.1, X.2, X.3... | Solo revisión interna |
| **Mayor** | Cambia metodología, alcance o requisitos | X+1.0 | Nueva aprobación completa |

### 7.2 Ejemplos de cambios menores (incremento decimal)

| Cambio | Ejemplo | Versión |
|--------|---------|---------|
| Corrección ortográfica | "capactación" → "capacitación" | 1.0 → 1.1 |
| Ajuste de redacción | Mejorar claridad de un párrafo | 1.1 → 1.2 |
| Cambio de responsable | "Ana García" → "Pedro López" (mismo cargo) | 1.2 → 1.3 |
| Actualización de cargo | "Coordinador" → "Jefe de SST" | 1.3 → 1.4 |
| Corrección de fecha | Error en fecha de cronograma | 1.4 → 1.5 |
| Ajuste formato | Cambio de logo, encabezado | 1.5 → 1.6 |
| Agregar definición | Nueva palabra al glosario | 1.6 → 1.7 |

### 7.3 Ejemplos de cambios mayores (incremento entero)

| Cambio | Ejemplo | Versión |
|--------|---------|---------|
| Cambio normativo | Nueva resolución modifica requisitos | 1.X → 2.0 |
| Cambio de metodología | De IPER a GTC-45 | 2.X → 3.0 |
| Cambio de alcance | Incluir nuevas sedes/procesos | 3.X → 4.0 |
| Reestructuración | Reorganizar secciones completas | 4.X → 5.0 |
| Revisión integral anual | Revisión programada del programa | 5.X → 6.0 |
| Cambio de objetivos | Nuevas metas, indicadores diferentes | 6.X → 7.0 |
| Fusión/división | Unir dos programas o dividir uno | 7.X → 8.0 |

### 7.4 Reglas de negocio en el sistema

```
AL GUARDAR CAMBIOS EN DOCUMENTO:

1. Sistema detecta qué campos cambiaron

2. Clasificación automática:

   campos_menores = [
       "contenido_texto" (si < 20% del texto),
       "responsable_nombre",
       "responsable_cargo",
       "formato_presentacion",
       "correcciones_ortograficas"
   ]

   campos_mayores = [
       "objetivos",
       "alcance",
       "metodologia",
       "indicadores",
       "actividades" (si > 30% cambian),
       "marco_normativo" (si se agregan/eliminan normas)
   ]

3. SI todos los cambios están en campos_menores:
   → Sugerir versión menor (X.Y+1)
   → Solo requiere aprobación del Consultor

4. SI algún cambio está en campos_mayores:
   → Requerir versión mayor (X+1.0)
   → Requiere nuevo ciclo de firmas completo

5. Usuario puede override (con justificación):
   → "Forzar como cambio menor porque..."
   → Se registra en auditoría
```

### 7.5 Flujo de aprobación según tipo de cambio

```
CAMBIO MENOR (v1.0 → v1.1)
──────────────────────────
Consultor edita
     │
     ▼
Sistema sugiere: "Cambio menor detectado"
     │
     ▼
Consultor confirma
     │
     ▼
Nueva versión 1.1 generada
     │
     ▼
PDF actualizado automáticamente
(Sin nueva firma del cliente)


CAMBIO MAYOR (v1.X → v2.0)
──────────────────────────
Consultor edita
     │
     ▼
Sistema alerta: "Cambio mayor detectado"
     │
     ▼
Confirma descripción del cambio
     │
     ▼
Documento pasa a estado "En revisión"
     │
     ▼
Nuevo ciclo completo:
  1. Revisión por secciones
  2. Aprobación interna
  3. Envío a firma del cliente
  4. Generación de v2.0
```

### 7.6 Historial de cambios (generado automáticamente)

```
┌─────────────────────────────────────────────────────────────────────────┐
│  HISTORIAL DE CAMBIOS - PRG-CAP-001                                     │
├─────────┬────────────┬─────────────────────────────────┬───────────────┤
│ Versión │ Fecha      │ Descripción                     │ Autorizado    │
├─────────┼────────────┼─────────────────────────────────┼───────────────┤
│ 1.0     │ 2026-01-15 │ Creación inicial                │ Juan Pérez    │
│ 1.1     │ 2026-02-20 │ Corrección ortográfica sec. 3   │ Carlos R.     │
│ 1.2     │ 2026-03-10 │ Cambio responsable mediciones   │ Carlos R.     │
│ 2.0     │ 2026-07-01 │ Actualización por Res. nueva    │ Juan Pérez    │
│ 2.1     │ 2026-08-15 │ Ajuste meta indicador cobertura │ Carlos R.     │
└─────────┴────────────┴─────────────────────────────────┴───────────────┘
```

---

## 8. Formatos de Exportación

### 8.1 Tipos de exportación

| Formato | Propósito | Características |
|---------|-----------|-----------------|
| **PDF oficial** | Documento con validez | Firmas electrónicas, control documental, QR verificación |
| **PDF borrador** | Revisión previa | Marca de agua "BORRADOR - SIN VALIDEZ" |
| **Word (.docx)** | Copia del cliente | Editable, para archivo en OneDrive/SharePoint |
| **ZIP carpeta** | Backup completo | Todos los documentos de una categoría |

### 8.2 Características del Word exportado

- Formato editable completo
- Encabezado y pie de página configurados
- Estilos aplicados (Título 1, Título 2, Normal, etc.)
- Tablas formateadas
- Sin firmas (el cliente las pone si desea en su copia)
- Nota al final: "Documento generado por EnterpriseSST - Copia del cliente"

### 8.3 Propiedad de los documentos

> **Principio:** La documentación generada es **propiedad del cliente**. EnterpriseSST es el motor de generación y control, pero si el contrato termina, el cliente conserva todos sus documentos en formato editable (Word).

---

## 9. Próximos Pasos

- [ ] Crear librería de 60 estándares mínimos (Resolución 0312/2019)
- [ ] Mapear documentos requeridos por cada estándar
- [ ] Definir cuáles estándares aplican en 7, 21 y 60
- [ ] Crear plantillas base para cada tipo de documento
- [ ] Diseñar mockups de alta fidelidad (Figma)

---

*Documento generado: Enero 2026*
*Proyecto: EnterpriseSST - Módulo de Documentación*
*Parte 2 de 3*
