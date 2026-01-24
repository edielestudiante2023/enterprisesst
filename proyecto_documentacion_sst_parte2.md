# Proyecto de Documentacion SST - Parte 2

## Resumen

Continuacion de la Parte 1. Define los detalles de implementacion del sistema de generacion documental.

---

## 1. Campos del Modulo Contexto del Cliente

### 1.1 Datos basicos de la empresa

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Razon social | texto | Si | Encabezados, documentos |
| NIT | texto | Si | Identificacion |
| Direccion principal | texto | Si | Documentos, emergencias |
| Ciudad/Municipio | texto | Si | Normativa local |
| Departamento | texto | Si | ARL, normativa |
| Telefono | texto | Si | Contacto |
| Correo electronico | texto | Si | Notificaciones |
| Representante legal | texto | Si | Firmas, aprobaciones |
| Cedula rep. legal | texto | Si | Firmas |
| Cargo rep. legal | texto | Si | Documentos |

### 1.2 Clasificacion empresarial

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Actividad economica principal | texto + codigo CIIU | Si | Peligros, normativa |
| Actividad economica secundaria | texto + codigo CIIU | No | Peligros adicionales |
| Sector economico | select | Si | Contexto sectorial |
| **Niveles de riesgo ARL** | **checkboxes multiples (I-V)** | Si | **Determina estandares** |
| ARL actual | select | Si | Reportes, afiliacion |

> **NOTA:** El campo "Clase de Riesgo Cotizacion" fue eliminado por no ser relevante para SST.

### 1.3 Tamano y estructura

| Campo | Tipo | Obligatorio | Uso en IA |
|-------|------|-------------|-----------|
| Total trabajadores | numero | Si | Referencia |
| Trabajadores directos | numero | Si | Cobertura programas |
| Trabajadores temporales | numero | No | Cobertura programas |
| Contratistas permanentes | numero | No | Alcance SG-SST |
| Numero de sedes | numero | Si | Alcance, emergencias |
| Turnos de trabajo | multi-select | Si | Programas, brigadas |

### 1.4 Estandares Aplicables (MANUAL)

**IMPORTANTE:** El campo "Estandares Aplicables" es **MANUAL**, definido por el consultor.

| Valor | Descripcion |
|-------|-------------|
| 7 | Nivel basico - Empresas pequenas riesgo bajo |
| 21 | Nivel intermedio |
| 60 | Nivel completo - Todas las empresas grandes o riesgo alto |

> El consultor define el nivel segun el oficio de la empresa y su criterio profesional, NO se calcula automaticamente.

### 1.5 Responsable SG-SST

**Campo:** Selector de consultores (`id_consultor_responsable`)

Al seleccionar un consultor, el sistema muestra automaticamente:
- Cedula del consultor
- Numero de licencia SST

Los datos provienen de `tbl_consultor`, evitando duplicacion.

### 1.6 Peligros identificados (multi-select)

| Categoria | Ejemplos |
|-----------|----------|
| Fisicos | Ruido, iluminacion, vibracion, temperaturas |
| Quimicos | Gases, vapores, material particulado, liquidos |
| Biologicos | Virus, bacterias, hongos, parasitos |
| Biomecanicos | Postura, movimiento repetitivo, manipulacion cargas |
| Psicosociales | Carga mental, jornada, relaciones, estres |
| Condiciones de seguridad | Mecanico, electrico, locativo, trabajo en alturas |
| Fenomenos naturales | Sismo, inundacion, vendaval |

---

## 2. Generacion de Documentos con IA

### 2.1 Tecnologia de IA

| Parametro | Valor |
|-----------|-------|
| **Proveedor** | **OpenAI** |
| **Modelo** | **GPT-4o-mini** |
| Temperatura | 0.3 (consistente, formal) |
| Max tokens | 2000 por seccion |

### 2.2 Estructura del prompt base

```text
CONTEXTO DEL CLIENTE:
- Empresa: {razon_social}
- NIT: {nit}
- Actividad economica: {actividad_economica}
- Niveles de riesgo: {niveles_riesgo}
- Total trabajadores: {total_trabajadores}
- Sedes: {lista_sedes}
- Peligros identificados: {peligros}
- Responsable SG-SST: {responsable_sst}

DOCUMENTO A GENERAR:
- Tipo: {tipo_documento}
- Nombre: {nombre_documento}
- Seccion actual: {seccion_key} - {titulo_seccion}

CONTEXTO ADICIONAL DEL USUARIO:
{contexto_adicional}

INSTRUCCIONES:
Genera el contenido para la seccion "{titulo_seccion}" del documento "{nombre_documento}".
El texto debe ser especifico para esta empresa, usando sus datos reales.
Usa lenguaje tecnico apropiado para documentos SG-SST.
No uses frases genericas como "la empresa" - usa el nombre real.
```

### 2.3 Estructura de Documentos - Librerias PHP

**Ubicacion:** `app/Libraries/DocumentosSST/`

Cada documento se define en una clase PHP que especifica:
- Codigo del documento (ej: PRG-EMO)
- Nombre completo
- Estandar relacionado (ej: 3.1.4)
- Carpeta PHVA donde se ubica
- Array de secciones con:
  - key (identificador unico)
  - titulo
  - orden
  - tipo (ia/fijo/tabla/manual)
  - prompt (para secciones IA)
  - variables requeridas
  - longitud maxima

### 2.4 Flujo de Regeneracion con Contexto

```text
1. Usuario ve seccion generada
2. No esta conforme, agrega contexto:
   "el cliente paga poco, solo incluir empleados directos"
3. Sistema regenera SOLO esa seccion
4. El prompt incluye:
   - Prompt original de la seccion
   - Contexto del cliente
   - Contexto adicional del usuario
5. Nueva generacion reemplaza la anterior
6. Se incrementa contador de regeneraciones
```

---

## 3. Estructuras por Tipo de Documento

### 3.1 Matriz de estructuras

| Tipo documento | Secciones | Libreria PHP |
|----------------|-----------|--------------|
| Programa | 8-13 (variable) | ProgramaBase.php |
| Procedimiento | 8 | ProcedimientoBase.php |
| Plan | 10 | PlanBase.php |
| Politica | 5 | PoliticaBase.php |
| Protocolo | 7 | ProtocoloBase.php |
| Formato | N/A | Solo campos |

### 3.2 Estructura de Procedimiento (8 secciones)

| # | Seccion | Descripcion |
|---|---------|-------------|
| 1 | Objetivo | Que busca el procedimiento |
| 2 | Alcance | A quien y donde aplica |
| 3 | Definiciones | Terminos clave |
| 4 | Responsables | Quien ejecuta cada paso |
| 5 | Descripcion | Pasos detallados |
| 6 | Documentos relacionados | Formatos, otros procedimientos |
| 7 | Control de cambios | Historial de versiones |
| 8 | Anexos | Diagramas, tablas de apoyo |

### 3.3 Estructura de Politica (5 secciones)

| # | Seccion | Descripcion |
|---|---------|-------------|
| 1 | Declaracion | Compromiso de la alta direccion |
| 2 | Objetivos | Que busca lograr |
| 3 | Alcance | A quien aplica |
| 4 | Compromisos | Puntos especificos (8-12 items) |
| 5 | Comunicacion y revision | Como se difunde, cuando se revisa |

---

## 4. Flujo Tecnico de Firma Electronica

### 4.1 Modelo de datos para firmas

```sql
tbl_doc_firma_solicitudes {
    id
    documento_id
    documento_version_id
    token                 -- UUID unico para el link
    estado                -- pendiente, firmado, expirado, rechazado
    fecha_creacion
    fecha_expiracion      -- token valido por X dias
    fecha_firma
    firmante_tipo         -- elaboro, reviso, aprobo
    firmante_interno_id   -- usuario del sistema (si aplica)
    firmante_email        -- email del firmante externo
    firmante_nombre
    firmante_cargo
    firmante_documento    -- cedula/NIT
}

tbl_doc_firma_evidencias {
    id
    solicitud_id
    ip_address
    user_agent
    fecha_hora_utc
    geolocalizacion       -- opcional
    tipo_firma            -- draw (canvas), type (texto), upload (imagen)
    firma_imagen          -- base64 o ruta de la firma
    hash_documento        -- SHA-256 del PDF al momento de firmar
    aceptacion_terminos   -- true (checkbox obligatorio)
}
```

### 4.2 Flujo del firmante externo (Cliente)

```text
1. RECIBE EMAIL
   - Link unico con token
   - Expira en 7 dias

2. ABRE LINK
   - Vista previa del PDF
   - Puede descargar

3. PROCESO DE FIRMA
   - Ingresa nombre, documento, cargo
   - Dibuja firma en canvas
   - Acepta terminos (Ley 527 de 1999)

4. CONFIRMACION
   - Codigo de verificacion
   - Puede descargar PDF firmado
```

### 4.3 Marco legal de firma electronica en Colombia

| Norma | Aplicacion |
|-------|------------|
| Ley 527 de 1999 | Define y reglamenta mensajes de datos y firma electronica |
| Decreto 2364 de 2012 | Reglamenta firma electronica (no requiere certificado digital) |
| Ley 1581 de 2012 | Proteccion de datos personales |

---

## 5. Codigos Estandar por Tipo de Documento

### 5.1 Estructura del codigo

```text
[TIPO]-[TEMA]

Ejemplos:
PRG-EMO  -> Programa de Evaluaciones Medicas Ocupacionales
PRO-INV  -> Procedimiento de Investigacion
POL-SST  -> Politica de SST
```

### 5.2 Prefijos por tipo de documento

| Tipo | Prefijo |
|------|---------|
| Politica | POL |
| Programa | PRG |
| Plan | PLA |
| Procedimiento | PRO |
| Protocolo | PRT |
| Manual | MAN |
| Informe | INF |
| Formato | FOR |

### 5.3 Codigos de tema

| Codigo | Tema |
|--------|------|
| SST | General SG-SST |
| CAP | Capacitacion |
| EMO | Evaluaciones Medicas Ocupacionales |
| EME | Emergencias |
| INV | Investigacion |
| INS | Inspecciones |
| EPP | Elementos proteccion |
| PEL | Peligros y riesgos |
| PVE | Vigilancia epidemiologica |
| AUD | Auditoria |
| COM | Comunicacion |
| COP | COPASST |
| CON | Convivencia |

---

## 6. Reglas de Cambios Menores vs Mayores

### 6.1 Definicion

| Tipo | Impacto | Version | Requiere |
|------|---------|---------|----------|
| **Menor** | No afecta el fondo | X.1, X.2... | Solo revision interna |
| **Mayor** | Cambia metodologia, alcance | X+1.0 | Nueva aprobacion completa |

### 6.2 Ejemplos de cambios menores

- Correccion ortografica
- Ajuste de redaccion
- Cambio de responsable (mismo cargo)
- Actualizacion de cargo
- Correccion de fecha
- Ajuste formato

### 6.3 Ejemplos de cambios mayores

- Cambio normativo
- Cambio de metodologia
- Cambio de alcance
- Reestructuracion
- Revision integral anual
- Cambio de objetivos

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 2 de 7*
