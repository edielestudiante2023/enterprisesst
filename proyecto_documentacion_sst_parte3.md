# Proyecto de Documentación SST - Parte 3

## Resumen

Esta parte documenta la implementación de base de datos completada: tablas, stored procedures, funciones y la librería de 60 estándares mínimos según Resolución 0312/2019.

---

## 1. Estructura de Base de Datos Implementada

### 1.1 Tablas Creadas (19 tablas)

| Tabla | Propósito | Relaciones clave |
|-------|-----------|------------------|
| `tbl_estandares_minimos` | Catálogo de 60 estándares Res. 0312/2019 | Base para cliente_estandares |
| `tbl_cliente_contexto_sst` | Información extendida SST del cliente | FK: tbl_clientes |
| `tbl_cliente_estandares` | Estándares aplicables por cliente | FK: clientes, estandares |
| `tbl_cliente_transiciones` | Historial cambios de nivel (7→21→60) | FK: clientes |
| `tbl_doc_carpetas` | Estructura de carpetas PHVA | Self-referencing, FK: clientes |
| `tbl_doc_tipos` | Tipos de documento (Programa, Plan, etc.) | - |
| `tbl_doc_plantillas` | Plantillas base por tipo | FK: doc_tipos |
| `tbl_doc_documentos` | Documentos generados | FK: clientes, carpetas, tipos |
| `tbl_doc_secciones` | Secciones de cada documento | FK: documentos |
| `tbl_doc_versiones` | Historial de versiones | FK: documentos |
| `tbl_doc_firma_solicitudes` | Solicitudes de firma electrónica | FK: documentos, versiones |
| `tbl_doc_firma_evidencias` | Evidencia de firmas | FK: solicitudes |
| `tbl_doc_firma_audit_log` | Auditoría del proceso de firma | FK: solicitudes |
| `tbl_doc_estandar_documentos` | Relación documento↔estándar | FK: documentos, estandares |
| `tbl_doc_contexto_adicional` | Contexto del usuario para IA | FK: documentos |
| `tbl_doc_exportaciones` | Registro de exportaciones PDF/Word | FK: documentos |
| `tbl_doc_codigos_tema` | Catálogo de códigos de tema | - |
| `tbl_doc_codigos_tipo` | Catálogo de prefijos por tipo | - |
| `tbl_cliente_sedes` | Sedes del cliente | FK: clientes |

### 1.2 Archivo de Tablas

```
app/SQL/modulo_documentacion_sst.sql
```

---

## 2. Estándares Mínimos Resolución 0312/2019

### 2.1 Archivo de Datos

```
app/SQL/insert_estandares_minimos_0312.sql
```

### 2.2 Estructura del Estándar

```sql
tbl_estandares_minimos {
    id_estandar          INT AUTO_INCREMENT PRIMARY KEY
    ciclo_phva           ENUM('PLANEAR','HACER','VERIFICAR','ACTUAR')
    categoria            VARCHAR(5)      -- 'I', 'II', 'III', etc.
    categoria_nombre     VARCHAR(100)    -- Nombre descriptivo
    item                 VARCHAR(10)     -- '1.1.1', '2.1.1', etc.
    nombre               VARCHAR(255)    -- Nombre del estándar
    peso_porcentual      DECIMAL(5,2)    -- Peso en evaluación
    aplica_7             TINYINT(1)      -- ¿Aplica a empresas 7 estándares?
    aplica_21            TINYINT(1)      -- ¿Aplica a empresas 21 estándares?
    aplica_60            TINYINT(1)      -- Siempre 1
    modo_verificacion    TEXT            -- Cómo verificar cumplimiento
    documentos_sugeridos TEXT            -- Documentos relacionados
}
```

### 2.3 Distribución de Estándares

| Ciclo PHVA | Categoría | Cantidad | Peso Total |
|------------|-----------|----------|------------|
| PLANEAR | I. Recursos | 11 | 10.0% |
| PLANEAR | II. Gestión Integral del SG-SST | 10 | 15.0% |
| HACER | III. Gestión de la Salud | 9 | 20.0% |
| HACER | IV. Gestión de Peligros y Riesgos | 17 | 30.0% |
| HACER | V. Gestión de Amenazas | 3 | 10.0% |
| VERIFICAR | VI. Verificación del SG-SST | 6 | 5.0% |
| ACTUAR | VII. Mejoramiento | 4 | 10.0% |
| **TOTAL** | | **60** | **100%** |

### 2.4 Aplicabilidad por Tamaño

| Nivel | Trabajadores | Riesgo | Estándares |
|-------|--------------|--------|------------|
| Básico | ≤ 10 | I, II, III | 7 |
| Intermedio | 11-50 | I, II, III | 21 |
| Completo | > 50 o cualquier tamaño | IV, V | 60 |

### 2.5 Estándares del Nivel 7 (Básico)

```
1.1.1  Responsable del SG-SST
1.1.4  Afiliación al Sistema de Seguridad Social
1.2.1  Programa de capacitación anual
1.2.2  Inducción y reinducción en SST
2.4.1  Plan que identifica objetivos, metas, responsabilidad
3.1.1  Indicadores estructura, proceso y resultado
4.1.2  Acciones preventivas y correctivas con base en resultados
```

### 2.6 Estándares del Nivel 21 (Intermedio)

Incluye los 7 básicos más 14 adicionales en:
- Gestión de peligros (matriz, EPP)
- Capacitación (curso 50 horas, COPASST)
- Prevención y preparación emergencias
- Investigación de accidentes
- Exámenes médicos ocupacionales

---

## 3. Stored Procedures Implementados

### 3.1 Ubicación de Archivos

```
app/SQL/sp/
├── sp_01_calcular_nivel_estandares.sql
├── sp_02_generar_carpetas_cliente.sql
├── sp_03_inicializar_estandares_cliente.sql
├── sp_04_detectar_cambio_nivel.sql
├── sp_05_calcular_cumplimiento.sql
├── sp_06_generar_codigo_documento.sql
├── sp_07_crear_version_documento.sql
└── fn_01_get_carpetas_json.sql
```

### 3.2 Descripción de Procedures

#### SP 1: sp_calcular_nivel_estandares

**Propósito:** Determina cuántos estándares aplican a un cliente según trabajadores y nivel de riesgo.

```sql
CALL sp_calcular_nivel_estandares(
    35,      -- total_trabajadores
    'II',    -- nivel_riesgo
    @result  -- OUT: 7, 21 o 60
);
SELECT @result;  -- Retorna: 21
```

**Lógica:**
- ≤10 trabajadores + Riesgo I-III → 7 estándares
- 11-50 trabajadores + Riesgo I-III → 21 estándares
- >50 trabajadores o Riesgo IV-V → 60 estándares

---

#### SP 2: sp_generar_carpetas_cliente

**Propósito:** Crea la estructura de carpetas PHVA para un cliente y año específico.

```sql
CALL sp_generar_carpetas_cliente(
    123,   -- id_cliente
    2026   -- año
);
```

**Genera:**
```
📁 SG-SST 2026
├── 📁 1. PLANEAR
│   ├── 📁 1.1 Recursos
│   │   ├── 📁 1.1.1 Responsable del SG-SST
│   │   ├── 📁 1.1.2 Responsabilidades en el SG-SST
│   │   └── ... (11 estándares)
│   └── 📁 1.2 Gestión Integral del SG-SST
│       └── ... (10 estándares)
├── 📁 2. HACER
│   ├── 📁 2.1 Gestión de la Salud
│   ├── 📁 2.2 Gestión de Peligros y Riesgos
│   └── 📁 2.3 Gestión de Amenazas
├── 📁 3. VERIFICAR
│   └── 📁 3.1 Verificación del SG-SST
└── 📁 4. ACTUAR
    └── 📁 4.1 Mejoramiento
```

---

#### SP 3: sp_inicializar_estandares_cliente

**Propósito:** Crea los registros de cumplimiento para un cliente según su nivel.

```sql
CALL sp_inicializar_estandares_cliente(123);
```

**Resultado:**
- Nivel 7: 7 estándares en estado 'pendiente', 53 en 'no_aplica'
- Nivel 21: 21 estándares en 'pendiente', 39 en 'no_aplica'
- Nivel 60: 60 estándares en 'pendiente'

---

#### SP 4: sp_detectar_cambio_nivel

**Propósito:** Detecta si un cambio en trabajadores/riesgo implica cambio de nivel de estándares.

```sql
CALL sp_detectar_cambio_nivel(
    123,   -- id_cliente
    35,    -- nuevo_total_trabajadores
    'II'   -- nuevo_nivel_riesgo
);
```

**Retorna:**
```
alerta: 'CAMBIO_DETECTADO' o 'SIN_CAMBIO'
nivel_anterior: 7
nivel_nuevo: 21
estandares_nuevos: 14
id_transicion: (ID del registro en tbl_cliente_transiciones)
```

---

#### SP 5: sp_calcular_cumplimiento

**Propósito:** Calcula el porcentaje de cumplimiento de estándares de un cliente.

```sql
CALL sp_calcular_cumplimiento(123);
```

**Retorna:**
```
estado    | cantidad | peso_total | porcentaje
----------|----------|------------|------------
cumple    | 15       | 45.5       | 45.5%
pendiente | 5        | 30.0       | 30.0%
en_proceso| 1        | 5.0        | 5.0%
TOTAL     | 21       | 45.5       | 45.5%
```

---

#### SP 6: sp_generar_codigo_documento

**Propósito:** Genera el código único para un nuevo documento.

```sql
CALL sp_generar_codigo_documento(
    123,      -- id_cliente
    'PRG',    -- codigo_tipo (Programa)
    'CAP',    -- codigo_tema (Capacitación)
    @codigo   -- OUT
);
SELECT @codigo;  -- Retorna: 'PRG-CAP-001' (o 002 si ya existe 001)
```

---

#### SP 7: sp_crear_version_documento

**Propósito:** Crea una nueva versión de un documento existente.

```sql
CALL sp_crear_version_documento(
    456,                    -- id_documento
    'menor',                -- tipo_cambio ('menor' o 'mayor')
    'Corrección ortográfica en sección 3',  -- descripcion_cambio
    'Carlos Rodríguez'      -- autorizado_por
);
```

**Comportamiento:**
- Cambio menor: 1.0 → 1.1 (solo requiere revisión interna)
- Cambio mayor: 1.X → 2.0 (requiere nuevo ciclo de firmas)

**Acciones automáticas:**
1. Marca versión anterior como 'obsoleto'
2. Crea snapshot JSON del contenido actual
3. Genera nueva versión
4. Actualiza version_actual en documento

---

#### FN 1: fn_get_carpetas_json

**Propósito:** Retorna el árbol de carpetas raíz de un cliente en formato JSON.

```sql
SELECT fn_get_carpetas_json(123);
```

**Retorna:**
```json
[
  {
    "id": 1,
    "nombre": "SG-SST 2026",
    "codigo": null,
    "tipo": "phva",
    "icono": "folder-root",
    "color": null
  }
]
```

---

## 4. Instrucciones de Despliegue

### 4.1 Orden de Ejecución en DBeaver/phpMyAdmin

```
1. modulo_documentacion_sst.sql     -- Crea las 19 tablas
2. insert_estandares_minimos_0312.sql -- Inserta los 60 estándares
3. sp/sp_01_calcular_nivel_estandares.sql
4. sp/sp_02_generar_carpetas_cliente.sql
5. sp/sp_03_inicializar_estandares_cliente.sql
6. sp/sp_04_detectar_cambio_nivel.sql
7. sp/sp_05_calcular_cumplimiento.sql
8. sp/sp_06_generar_codigo_documento.sql
9. sp/sp_07_crear_version_documento.sql
10. sp/fn_01_get_carpetas_json.sql
```

### 4.2 Formato de Archivos SP (para DBeaver)

Cada archivo SP tiene este formato:

```sql
USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_nombre;

DELIMITER //

CREATE PROCEDURE sp_nombre(...)
BEGIN
    -- lógica
END //

DELIMITER ;
```

### 4.3 Verificación Post-Despliegue

```sql
-- Verificar tablas
SELECT COUNT(*) FROM information_schema.tables
WHERE table_schema = 'empresas_sst'
AND table_name LIKE 'tbl_doc%' OR table_name LIKE 'tbl_estandares%'
OR table_name LIKE 'tbl_cliente_contexto%';

-- Verificar estándares
SELECT COUNT(*) FROM tbl_estandares_minimos;  -- Debe ser 60

-- Verificar procedures
SHOW PROCEDURE STATUS WHERE Db = 'empresas_sst';  -- 7 procedures

-- Verificar función
SHOW FUNCTION STATUS WHERE Db = 'empresas_sst';   -- 1 función
```

---

## 5. Relación con Módulos Existentes

### 5.1 Integración con tbl_clientes

```sql
tbl_cliente_contexto_sst.id_cliente → tbl_clientes.id_cliente
tbl_cliente_estandares.id_cliente   → tbl_clientes.id_cliente
tbl_doc_documentos.id_cliente       → tbl_clientes.id_cliente
tbl_doc_carpetas.id_cliente         → tbl_clientes.id_cliente
```

### 5.2 Integración con Sistema de Usuarios

```sql
tbl_doc_documentos.creado_por       → tbl_usuarios.id_usuario
tbl_doc_versiones.autorizado_por    → Nombre del usuario
tbl_doc_firma_solicitudes.firmante_interno_id → tbl_usuarios.id_usuario
```

---

## 6. Próximos Pasos (Parte 4)

- [ ] Modelos CodeIgniter para las nuevas tablas
- [ ] Controladores del módulo de documentación
- [ ] Vistas (implementación de wireframes)
- [ ] Integración con API de IA para generación
- [ ] Módulo de firma electrónica
- [ ] Exportación PDF/Word

---

## 7. Archivos del Proyecto

```
proyecto_documentacion_sst_parte1.md  -- Conceptos, alcance, estructura general
proyecto_documentacion_sst_parte2.md  -- Prompts IA, wireframes, flujo firmas
proyecto_documentacion_sst_parte3.md  -- (Este archivo) BD implementada
libreria_estandares_0312_2019.md      -- Referencia completa de 60 estándares

app/SQL/
├── modulo_documentacion_sst.sql      -- 19 tablas
├── insert_estandares_minimos_0312.sql -- 60 estándares
└── sp/
    ├── sp_01_calcular_nivel_estandares.sql
    ├── sp_02_generar_carpetas_cliente.sql
    ├── sp_03_inicializar_estandares_cliente.sql
    ├── sp_04_detectar_cambio_nivel.sql
    ├── sp_05_calcular_cumplimiento.sql
    ├── sp_06_generar_codigo_documento.sql
    ├── sp_07_crear_version_documento.sql
    └── fn_01_get_carpetas_json.sql
```

---

*Documento generado: Enero 2026*
*Proyecto: EnterpriseSST - Módulo de Documentación*
*Parte 3 de 4*
