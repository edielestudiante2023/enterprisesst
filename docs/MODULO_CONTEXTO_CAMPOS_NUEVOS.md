# Ampliacion del Contexto del Cliente SST

> Fecha: 2026-04-07 | Estado: DISEÑO

## Objetivo

Agregar ~18 campos nuevos a `tbl_cliente_contexto_sst` para capturar informacion operacional, de seguridad social y de infraestructura que hoy no se registra, y que es critica para que la IA genere documentos mas precisos y completos.

---

## Campos Nuevos por Categoria

### A. Horarios y Jornada Laboral (4 campos)

| Campo BD | Tipo | Ejemplo | Para que sirve |
|----------|------|---------|----------------|
| `horario_lunes_viernes` | VARCHAR(100) | "7:00 - 17:00" | IA menciona horarios reales en politicas y programas |
| `horario_sabado` | VARCHAR(100) | "8:00 - 13:00" o NULL | Determina si hay jornada sabatina |
| `trabaja_domingos_festivos` | ENUM('si','no','ocasional') DEFAULT 'no' | "ocasional" | Relevante para turnos, riesgo psicosocial |
| `descripcion_turnos` | TEXT NULL | "Vigilancia: 3 turnos rotativos 6am-2pm, 2pm-10pm, 10pm-6am" | Complementa el JSON `turnos_trabajo` con detalle real |

### B. Seguridad Social (5 campos)

| Campo BD | Tipo | Ejemplo | Para que sirve |
|----------|------|---------|----------------|
| `eps_principales` | VARCHAR(500) | "Sura, Sanitas, Nueva EPS" | IA referencia EPS en politicas de incapacidades y salud |
| `afp_principales` | VARCHAR(500) | "Porvenir, Proteccion" | Contexto de afiliacion |
| `caja_compensacion` | VARCHAR(100) | "Compensar" | Programas de bienestar |
| `tasa_cotizacion_arl` | DECIMAL(5,4) | 0.0052 (0.52%) | Calculo de presupuesto SST |
| `manejo_incapacidades` | TEXT NULL | "Empresa asume primeros 2 dias. EPS paga desde dia 3. Incapacidades >180 dias se remiten a AFP." | IA genera politica de incapacidades precisa |

### C. Datos Operacionales (3 campos)

| Campo BD | Tipo | Ejemplo | Para que sirve |
|----------|------|---------|----------------|
| `actividades_alto_riesgo` | JSON NULL | ["trabajo_alturas","espacios_confinados"] | Mas especifico que peligros genericos |
| `epp_por_cargo` | TEXT NULL | "Vigilantes: chaleco, linterna. Aseo: guantes, tapabocas" | IA detalla dotacion real en programas |
| `vehiculos_maquinaria` | TEXT NULL | "2 vehiculos administrativos, 1 montacargas" | Relevante para plan emergencias y mantenimiento |

### D. Historial SST (3 campos)

| Campo BD | Tipo | Ejemplo | Para que sirve |
|----------|------|---------|----------------|
| `accidentes_ultimo_anio` | INT DEFAULT 0 | 2 | Indicadores, contexto IA para investigacion |
| `tasa_ausentismo` | DECIMAL(5,2) NULL | 3.50 (%) | Indicadores PYP salud |
| `enfermedades_laborales_activas` | TEXT NULL | "1 caso tunel carpiano en area administrativa" | Contexto PVE biomecanico/psicosocial |

### E. Infraestructura (3 campos)

| Campo BD | Tipo | Ejemplo | Para que sirve |
|----------|------|---------|----------------|
| `numero_pisos` | INT DEFAULT 1 | 5 | Plan emergencias, evacuacion |
| `tiene_ascensor` | TINYINT(1) DEFAULT 0 | 1 | Plan emergencias |
| `sustancias_quimicas` | TEXT NULL | "Hipoclorito, detergentes industriales, pintura" | Plan emergencias, hojas de seguridad |

---

## Archivos que DEBEN actualizarse

### Nivel 1 — Obligatorio (datos entran y salen)

| # | Archivo | Que cambiar |
|---|---------|-------------|
| 1 | `app/SQL/migrate_contexto_campos_nuevos.php` | **CREAR** - ALTER TABLE con 18 columnas nuevas |
| 2 | `app/Models/ClienteContextoSstModel.php` | Agregar 18 campos a `$allowedFields` |
| 3 | `app/Controllers/ContextoClienteController.php` | `guardar()`: leer 18 campos del POST. `getContextoJson()`: incluir en JSON |
| 4 | `app/Views/contexto/formulario.php` | Agregar 5 secciones nuevas al formulario |

### Nivel 2 — Propagacion a IA (los campos nuevos alimentan generacion)

| # | Archivo | Que cambiar |
|---|---------|-------------|
| 5 | `app/Services/IADocumentacionService.php` | `construirPrompt()`: agregar campos nuevos al system prompt |
| 6 | `app/Services/ObjetivosSgsstService.php` | `construirContextoCompleto()`: agregar campos nuevos |
| 7 | `app/Libraries/DocumentosSSTTypes/AbstractDocumentoSST.php` | `getContextoBase()`: incluir horarios, seguridad social, infraestructura |

### Nivel 3 — Consumidores especificos (usan campos puntuales)

| # | Archivo | Campo(s) relevante(s) |
|---|---------|----------------------|
| 8 | Services de Indicadores (10+ archivos) | `accidentes_ultimo_anio`, `tasa_ausentismo` |
| 9 | `app/Libraries/OttoTableMap.php` | Mapear columnas nuevas para consulta Otto |
| 10 | PVE Biomecanico/Psicosocial services | `enfermedades_laborales_activas`, `descripcion_turnos` |
| 11 | Plan Emergencias document type | `numero_pisos`, `tiene_ascensor`, `sustancias_quimicas` |
| 12 | Politica Incapacidades document type | `manejo_incapacidades`, `eps_principales` |
| 13 | Presupuesto SST | `tasa_cotizacion_arl` |

### Nivel 4 — No requiere cambio (se benefician automaticamente)

Todos los demas archivos que usan `getByCliente()` obtienen los campos nuevos en el array, pero no los usan explicitamente. Si la IA los recibe via Nivel 2, los documentos generados ya los reflejaran sin tocar cada DocumentType individual.

---

## Punto clave: Estrategia de propagacion

**NO es necesario tocar los 49 DocumentTypes individuales.** La IA recibe el contexto completo via:

```
AbstractDocumentoSST::getContextoBase()  →  prompt de sistema
IADocumentacionService::construirPrompt()  →  cada seccion del documento
```

Si estos 2 archivos incluyen los campos nuevos en el texto del prompt, la IA automaticamente los incorpora en TODOS los documentos que genere.

Los unicos consumidores que necesitan cambio explicito son los que hacen calculos con campos especificos (indicadores, presupuesto).

---

## Orden de implementacion

1. **Migracion BD** (ALTER TABLE)
2. **Model** (allowedFields)
3. **Controller** (guardar + getContextoJson)
4. **Vista formulario** (5 secciones nuevas)
5. **IADocumentacionService** (prompt)
6. **ObjetivosSgsstService** (contexto completo)
7. **AbstractDocumentoSST** (contexto base)
8. **OttoTableMap** (mapeo)
9. **Probar** con un cliente real

---

## Notas

- Todos los campos nuevos son NULL por defecto → no rompe clientes existentes
- Los campos TEXT permiten formato libre → el consultor describe la realidad del cliente
- Los campos JSON (actividades_alto_riesgo) siguen el patron de peligros_identificados
- `turnos_trabajo` (JSON existente) se MANTIENE como checklist rapido, `descripcion_turnos` (TEXT nuevo) lo complementa con detalle
