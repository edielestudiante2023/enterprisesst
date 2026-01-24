# Proyecto de Documentacion SST - Parte 3

## Resumen

Esta parte documenta la implementacion de base de datos completada: tablas, stored procedures, funciones y la libreria de 60 estandares minimos segun Resolucion 0312/2019.

---

## 1. Estructura de Base de Datos Implementada

### 1.1 Tablas Creadas

| Tabla | Proposito |
|-------|-----------|
| tbl_estandares_minimos | Catalogo de 60 estandares Res. 0312/2019 |
| tbl_cliente_contexto_sst | Informacion extendida SST del cliente |
| tbl_cliente_estandares | Estandares aplicables por cliente |
| tbl_cliente_transiciones | Historial cambios de nivel (7-21-60) |
| tbl_doc_carpetas | Estructura de carpetas PHVA |
| tbl_doc_tipos | Tipos de documento (Programa, Plan, etc.) |
| tbl_doc_plantillas | Plantillas base por tipo (DEPRECADA - usar Librerias PHP) |
| tbl_doc_documentos | Documentos generados |
| tbl_doc_secciones | Secciones de cada documento |
| tbl_doc_versiones | Historial de versiones |
| tbl_doc_firma_solicitudes | Solicitudes de firma electronica |
| tbl_doc_firma_evidencias | Evidencia de firmas |
| tbl_doc_firma_audit_log | Auditoria del proceso de firma |
| tbl_doc_estandar_documentos | Relacion documento-estandar |
| tbl_cliente_sedes | Sedes del cliente |

> **NOTA:** La tabla `tbl_doc_plantillas` queda deprecada. La estructura de documentos se define en **Librerias PHP** ubicadas en `app/Libraries/DocumentosSST/`.

### 1.2 Archivo de Tablas

```
app/SQL/modulo_documentacion_sst.sql
```

---

## 2. Estandares Minimos Resolucion 0312/2019

### 2.1 Archivo de Datos

```
app/SQL/insert_estandares_minimos_0312.sql
```

### 2.2 Estructura del Estandar

```sql
tbl_estandares_minimos {
    id_estandar          INT AUTO_INCREMENT PRIMARY KEY
    ciclo_phva           ENUM('PLANEAR','HACER','VERIFICAR','ACTUAR')
    categoria            VARCHAR(5)      -- 'I', 'II', 'III', etc.
    categoria_nombre     VARCHAR(100)    -- Nombre descriptivo
    item                 VARCHAR(10)     -- '1.1.1', '2.1.1', etc.
    nombre               VARCHAR(255)    -- Nombre del estandar
    criterio             TEXT            -- Pregunta de auditoria
    peso_porcentual      DECIMAL(5,2)    -- Peso en evaluacion
    aplica_7             TINYINT(1)      -- Aplica a nivel 7?
    aplica_21            TINYINT(1)      -- Aplica a nivel 21?
    aplica_60            TINYINT(1)      -- Siempre 1
    modo_verificacion    TEXT            -- Como verificar cumplimiento
    documentos_sugeridos TEXT            -- Documentos relacionados
}
```

### 2.3 Distribucion de Estandares

| Ciclo PHVA | Categoria | Cantidad | Peso Total |
|------------|-----------|----------|------------|
| PLANEAR | I. Recursos | 11 | 10.0% |
| PLANEAR | II. Gestion Integral del SG-SST | 10 | 15.0% |
| HACER | III. Gestion de la Salud | 9 | 20.0% |
| HACER | IV. Gestion de Peligros y Riesgos | 17 | 30.0% |
| HACER | V. Gestion de Amenazas | 3 | 10.0% |
| VERIFICAR | VI. Verificacion del SG-SST | 6 | 5.0% |
| ACTUAR | VII. Mejoramiento | 4 | 10.0% |
| **TOTAL** | | **60** | **100%** |

### 2.4 Aplicabilidad por Nivel

**IMPORTANTE:** El nivel de estandares (7, 21, 60) es definido **MANUALMENTE** por el consultor, basado en su criterio profesional y el oficio de la empresa.

| Nivel | Descripcion |
|-------|-------------|
| 7 | Nivel basico - Definido por el consultor |
| 21 | Nivel intermedio - Definido por el consultor |
| 60 | Nivel completo - Todas las empresas grandes o riesgo alto |

> El sistema NO calcula automaticamente el nivel. El consultor lo define en el Contexto SST del cliente.

---

## 3. Stored Procedures Implementados

### 3.1 Ubicacion de Archivos

```
app/SQL/sp/
├── sp_01_calcular_nivel_estandares.sql   (DEPRECADO - nivel es manual)
├── sp_02_generar_carpetas_cliente.sql
├── sp_03_inicializar_estandares_cliente.sql
├── sp_04_detectar_cambio_nivel.sql
├── sp_05_calcular_cumplimiento.sql
├── sp_06_generar_codigo_documento.sql
├── sp_07_crear_version_documento.sql
└── fn_01_get_carpetas_json.sql
```

### 3.2 Descripcion de Procedures

#### SP 2: sp_generar_carpetas_cliente

**Proposito:** Crea la estructura de carpetas PHVA para un cliente y anio especifico.

```sql
CALL sp_generar_carpetas_cliente(123, 2026);
```

**Genera:**

```
SG-SST 2026
├── 1. PLANEAR
│   ├── 1.1 Recursos
│   └── 1.2 Gestion Integral del SG-SST
├── 2. HACER
│   ├── 2.1 Gestion de la Salud
│   ├── 2.2 Gestion de Peligros y Riesgos
│   └── 2.3 Gestion de Amenazas
├── 3. VERIFICAR
│   └── 3.1 Verificacion del SG-SST
└── 4. ACTUAR
    └── 4.1 Mejoramiento
```

#### SP 3: sp_inicializar_estandares_cliente

**Proposito:** Crea los registros de cumplimiento para un cliente segun su nivel.

```sql
CALL sp_inicializar_estandares_cliente(123);
```

**Resultado:**
- Nivel 7: 7 estandares en estado 'pendiente', 53 en 'no_aplica'
- Nivel 21: 21 estandares en 'pendiente', 39 en 'no_aplica'
- Nivel 60: 60 estandares en 'pendiente'

#### SP 5: sp_calcular_cumplimiento

**Proposito:** Calcula el porcentaje de cumplimiento de estandares de un cliente.

```sql
CALL sp_calcular_cumplimiento(123);
```

**Retorna:**

| estado | cantidad | peso_total | porcentaje |
|--------|----------|------------|------------|
| cumple | 15 | 45.5 | 45.5% |
| pendiente | 5 | 30.0 | 30.0% |
| en_proceso | 1 | 5.0 | 5.0% |
| TOTAL | 21 | 45.5 | 45.5% |

#### SP 6: sp_generar_codigo_documento

**Proposito:** Genera el codigo unico para un nuevo documento.

```sql
CALL sp_generar_codigo_documento(123, 'PRG', 'EMO', @codigo);
SELECT @codigo;  -- Retorna: 'PRG-EMO'
```

#### SP 7: sp_crear_version_documento

**Proposito:** Crea una nueva version de un documento existente.

```sql
CALL sp_crear_version_documento(
    456,                    -- id_documento
    'menor',                -- tipo_cambio ('menor' o 'mayor')
    'Correccion ortografica',  -- descripcion_cambio
    'Carlos Rodriguez'      -- autorizado_por
);
```

**Comportamiento:**
- Cambio menor: 1.0 -> 1.1
- Cambio mayor: 1.X -> 2.0

---

## 4. Instrucciones de Despliegue

### 4.1 Orden de Ejecucion

```
1. modulo_documentacion_sst.sql     -- Crea las tablas
2. insert_estandares_minimos_0312.sql -- Inserta los 60 estandares
3. sp/sp_02_generar_carpetas_cliente.sql
4. sp/sp_03_inicializar_estandares_cliente.sql
5. sp/sp_05_calcular_cumplimiento.sql
6. sp/sp_06_generar_codigo_documento.sql
7. sp/sp_07_crear_version_documento.sql
8. sp/fn_01_get_carpetas_json.sql
```

### 4.2 Verificacion Post-Despliegue

```sql
-- Verificar estandares
SELECT COUNT(*) FROM tbl_estandares_minimos;  -- Debe ser 60

-- Verificar procedures
SHOW PROCEDURE STATUS WHERE Db = 'empresas_sst';

-- Verificar funcion
SHOW FUNCTION STATUS WHERE Db = 'empresas_sst';
```

---

## 5. Integracion con Modulos Existentes

### 5.1 Integracion con tbl_clientes

```sql
tbl_cliente_contexto_sst.id_cliente -> tbl_clientes.id_cliente
tbl_cliente_estandares.id_cliente   -> tbl_clientes.id_cliente
tbl_doc_documentos.id_cliente       -> tbl_clientes.id_cliente
tbl_doc_carpetas.id_cliente         -> tbl_clientes.id_cliente
```

### 5.2 Integracion con Sistema de Usuarios

```sql
tbl_doc_documentos.creado_por       -> tbl_usuarios.id_usuario
tbl_doc_versiones.autorizado_por    -> Nombre del usuario
tbl_doc_firma_solicitudes.firmante_interno_id -> tbl_usuarios.id_usuario
```

---

## 6. Archivos del Proyecto

```
app/SQL/
├── modulo_documentacion_sst.sql      -- Tablas
├── insert_estandares_minimos_0312.sql -- 60 estandares
└── sp/
    ├── sp_02_generar_carpetas_cliente.sql
    ├── sp_03_inicializar_estandares_cliente.sql
    ├── sp_05_calcular_cumplimiento.sql
    ├── sp_06_generar_codigo_documento.sql
    ├── sp_07_crear_version_documento.sql
    └── fn_01_get_carpetas_json.sql
```

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 3 de 7*
