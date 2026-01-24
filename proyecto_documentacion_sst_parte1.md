# Proyecto de Documentacion SST - Parte 1

## Resumen Ejecutivo

Sistema de generacion y gestion documental para SG-SST con inteligencia artificial, control documental ISO y firma electronica.

---

## 1. Contexto del Proyecto

### 1.1 Problema que resuelve

- Clientes nuevos llegan con documentacion de otras empresas/profesionales
- No hay estandarizacion en los documentos
- El control documental es manual y propenso a errores
- No hay trazabilidad de cambios ni versiones

### 1.2 Decision clave

**Toda la documentacion se genera desde cero** en el sistema EnterpriseSST.

No se migra documentacion de terceros. Solo se conservan registros historicos obligatorios:
- Investigaciones de accidentes/incidentes
- Examenes medicos ocupacionales
- Registros de capacitacion con fechas
- Actas de COPASST/Convivencia
- Mediciones ambientales

---

## 2. Tipos de Documentos SG-SST

### 2.1 Clasificacion por tipo

| # | Tipo | Control completo | Version | Aprobacion |
|---|------|------------------|---------|------------|
| 1 | Politica | Si | Si | Si |
| 2 | Objetivos | Si | Si | Si |
| 3 | Programa | Si | Si | Si |
| 4 | Plan | Si | Si | Si |
| 5 | Procedimiento | Si | Si | Si |
| 6 | Protocolo | Si | Si | Si |
| 7 | Manual | Si | Si | Si |
| 8 | Informe | Parcial | Si | Parcial |
| 9 | Formato/Registro | No | No | No |
| 10 | Documentos de apoyo | Variable | Variable | Variable |

> **Nota:** Los registros NO se versionan, se fechan.

### 2.2 Estructura de Documentos - Librerias PHP

**IMPORTANTE:** La estructura de cada tipo de documento se define en **Librerias PHP**, NO en base de datos. Esto permite flexibilidad por tipo de documento.

**Ubicacion:** `app/Libraries/DocumentosSST/`

Cada tipo de documento tiene su propia cantidad de secciones segun su naturaleza:
- Programas: 8-13 secciones (variable segun complejidad)
- Procedimientos: 8 secciones
- Politicas: 5 secciones
- Planes: 10 secciones
- Protocolos: 7 secciones

### 2.3 Ejemplos de programas tipicos

- Programa de Capacitacion
- Programa de Vigilancia Epidemiologica (PVE Osteomuscular, Psicosocial, Visual)
- Programa de Medicina Preventiva
- Programa de Inspecciones
- Programa de Mantenimiento Preventivo
- Programa de Orden y Aseo
- Programa de EPP
- Programa de Evaluaciones Medicas Ocupacionales

---

## 3. Arquitectura del Sistema

### 3.1 Modulos principales

```
+-------------------------------------------------------------------+
|                    MODULO CONTEXTO CLIENTE                         |
|  - Razon social, NIT, actividad economica                          |
|  - Cantidad trabajadores                                           |
|  - Nivel de riesgo ARL (I, II, III, IV, V) - MULTIPLE              |
|  - Estandares minimos aplicables (7, 21 o 60) - MANUAL             |
|  - Sedes, turnos, procesos                                         |
|  - Peligros identificados                                          |
|  - Responsable SG-SST (selector de consultores)                    |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|              GENERADOR DE DOCUMENTOS (IA)                          |
|  - Librerias PHP definen estructura de cada documento              |
|  - OpenAI GPT-4o-mini genera contenido por seccion                 |
|  - Permite regenerar con contexto adicional                        |
|  - Aprobacion por seccion + aprobacion global                      |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|                 CONTROL DOCUMENTAL                                 |
|  - Encabezado con identificacion                                   |
|  - Bloque de firmas                                                |
|  - Historial de cambios                                            |
|  - Pie de pagina de control                                        |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|                 MODULO FIRMA (tipo DocuSeal)                       |
|  - Analista/Consultor firman en sistema                            |
|  - Cliente recibe link y firma electronicamente                    |
|  - Se genera PDF final con evidencia de firmas                     |
+-------------------------------------------------------------------+
```

### 3.2 Flujo del generador de documentos

```
1. Usuario selecciona tipo de documento (ej: "Programa de Capacitacion")
2. Sistema carga la Libreria PHP correspondiente
3. IA genera contenido para cada seccion definida en la libreria
4. Se presenta editor por secciones:

   +-----------------------------------------------------------+
   | SECCION 1: Objetivo                                        |
   | +-------------------------------------------------------+ |
   | | Texto generado por IA...                              | |
   | +-------------------------------------------------------+ |
   | +-------------------------------------------------------+ |
   | | Contexto adicional: [________________]                | |
   | +-------------------------------------------------------+ |
   | [Regenerar esta seccion]  [Aprobar seccion]               |
   +-----------------------------------------------------------+

5. Consultor revisa, agrega contexto, regenera si es necesario
   Ejemplo: "el cliente paga poco, ajustalo solo a empleados directos"
6. Se aprueba cada seccion individualmente
7. Opcion de aprobar documento completo
8. Al aprobar: se genera version y se envia a firma
```

---

## 4. Control Documental

### 4.1 Encabezado del documento

| Campo | Para que sirve |
|-------|----------------|
| Nombre del documento | Identificacion clara |
| Codigo | Evita duplicidad (ej: PRG-EMO) |
| Sistema | SG-SST / SIG |
| Proceso | SST / Talento Humano |
| Fecha de emision | Inicio de vigencia |
| Version | Control de cambios |
| Estado | Vigente / Obsoleto |

### 4.2 Bloque de firmas

| Rol | Quien es | Como firma |
|-----|----------|------------|
| **Elaboro** | Analista SST (equipo interno) | Firma en sistema |
| **Reviso** | Consultor/Responsable SST | Firma en sistema |
| **Aprobo** | Alta Direccion del cliente | Firma tipo DocuSeal |

### 4.3 Control de versiones

| Tipo de cambio | Ejemplos | Version |
|----------------|----------|---------|
| **Menor** | Ortografia, redaccion, cambio responsable | 1.1, 1.2, 1.3 |
| **Mayor** | Cambio normativo, metodologia, alcance | 2.0, 3.0 |

**Reglas:**
- Version inicial siempre es 1.0
- Codigo es fijo por tipo (PRG-EMO = Programa Examenes Medicos)
- Consecutivo es la version, no un numero de documento

### 4.4 Historial de cambios

| Version | Fecha | Descripcion del cambio | Autorizado por |
|---------|-------|------------------------|----------------|
| 1.0 | 01/01/2025 | Creacion inicial | [Nombre] |
| 1.1 | 15/06/2025 | Ajuste normativo | [Nombre] |
| 2.0 | 01/01/2026 | Revision integral | [Nombre] |

### 4.5 Quien aprueba que

| Documento | Aprueba |
|-----------|---------|
| Politica | Representante legal (cliente) |
| Objetivos | Alta Direccion (cliente) |
| Manual SG-SST | Alta Direccion (cliente) |
| Procedimientos | Alta Direccion (cliente) |
| Programas | Alta Direccion (cliente) |
| Plan Anual | Alta Direccion (cliente) |
| Informes | Responsable SG-SST |
| Registros | No aplica |

---

## 5. Modelo de Datos

### 5.1 Tablas principales (YA IMPLEMENTADAS)

Las tablas usan prefijo `tbl_doc_` y estan implementadas:

#### tbl_doc_documentos (tabla principal)
```
tbl_doc_documentos {
    id_documento
    id_cliente
    tipo_documento        -> programa, procedimiento, plan, etc.
    codigo                -> PRG-EMO, PRO-INV, etc.
    nombre
    version_actual
    estado                -> borrador, en_revision, aprobado, obsoleto
    id_carpeta            -> FK a tbl_doc_carpetas
    fecha_creacion
    fecha_aprobacion
    created_by            -> usuario (Analista SST)
}
```

#### tbl_doc_secciones
```
tbl_doc_secciones {
    id_seccion
    id_documento
    seccion_key           -> 'objetivo', 'alcance', etc.
    titulo
    orden
    tipo                  -> 'ia', 'fijo', 'tabla', 'manual'
    contenido_generado    -> Lo que genero la IA
    contexto_adicional    -> Input del usuario para regenerar
    contenido_editado     -> Si el usuario edito manualmente
    contenido_final       -> El contenido definitivo para el PDF
    prompt_usado
    modelo_ia
    tokens_usados
    regeneraciones
}
```

#### tbl_doc_versiones
```
tbl_doc_versiones {
    id_version
    id_documento
    version               -> "1.0", "1.1", "2.0"
    fecha
    descripcion_cambio
    autorizado_por
    contenido_completo    -> JSON snapshot de todas las secciones
    estado                -> vigente / obsoleto
}
```

---

## 6. Flujo de Aprobacion y Firma

### 6.1 Flujo completo

```
Analista elabora documento
         |
         v
Consultor revisa cada seccion
         |
         v
    +-------------+
    |  Preview    | <- Vista previa del documento completo
    |  documento  |
    +-------------+
         |
         v
    +-----------------------------+
    | Aprobar documento?          |
    |                             |
    | "Esta accion generara la    |
    |  version X.0 del documento" |
    |                             |
    | [Cancelar]  [Aprobar]       |
    +-----------------------------+
         |
         v
Consultor aprueba -> Sistema registra firma Elaboro/Reviso
         |
         v
Se envia link al cliente (Alta Direccion)
         |
         v
Cliente firma electronicamente (tipo DocuSeal)
         |
         v
Se genera PDF final con:
  - Documento completo
  - Firmas electronicas
  - Evidencia de firma (fecha, hora, IP)
  - Version registrada en BD
```

---

## 7. Tecnologias Definidas

| Componente | Tecnologia |
|------------|------------|
| Backend | PHP 8 + CodeIgniter 4 |
| Frontend | Bootstrap 5 + JavaScript vanilla |
| Base de datos | MySQL 8 (XAMPP local + DigitalOcean produccion) |
| **IA** | **OpenAI API (GPT-4o-mini)** |
| PDF | Dompdf |
| Estructura documentos | **Librerias PHP** |

---

## 8. Referencias Normativas

- **Decreto 1072 de 2015** - Decreto Unico Reglamentario del Sector Trabajo
- **Resolucion 0312 de 2019** - Estandares Minimos del SG-SST
- **ISO 9001:2015** - Clausula 7.5 (Informacion documentada)
- **ISO 45001:2018** - Clausula 7.5 (Informacion documentada)

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 1 de 7*
