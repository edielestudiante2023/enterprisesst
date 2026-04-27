# Continuidad de versiones SG-SST para clientes que ya tienen documentacion

**Audiencia:** consultores admin de empresas consultoras (incluido Cycloid Talent)
**Para:** ingresar al aplicativo clientes que llegan con su propio SG-SST ya armado, conservando codigos y trazabilidad de versiones.

---

## 1. Contexto

Cuando un consultor empieza a llevar el SG-SST de una empresa que **ya tenia documentacion previa** (manuales, politicas, programas, procedimientos con sus propios codigos y versiones), surge la pregunta:

> ¿Como hago para que el aplicativo continue desde donde el cliente venia, en vez de empezar todo desde cero?

Este documento responde esa pregunta con 3 opciones segun el volumen documental del cliente.

---

## 2. Como funciona el sistema (resumen)

El aplicativo maneja cada documento con dos niveles:

| Tabla | Campo clave | Que controla |
|-------|-------------|--------------|
| `tbl_documentos_sst` | `codigo` | Codigo del documento (ej. `MAN-SST-001-PEPITO`) |
| `tbl_documentos_sst` | `version` | Numero entero de la version actual (1, 2, 3...) |
| `tbl_doc_versiones_sst` | `version_texto` | Etiqueta visible (`1.0`, `1.1`, `2.0`...) |
| `tbl_doc_versiones_sst` | `estado` | `vigente` / `obsoleto` |
| `tbl_doc_versiones_sst` | `contenido_snapshot` | Snapshot del contenido aprobado |

**Ambos campos `codigo` y `version` son editables.** El aplicativo continua su flujo normal de versionamiento desde el valor que pongas.

Detalles tecnicos en: `docs/MODULO_NUMERALES_SGSST/05_VERSIONAMIENTO/1_AA_VERSIONAMIENTO.md`

---

## 3. Matriz de decision

| Cantidad de docs del cliente | Tiene historial relevante | Opcion recomendada |
|------------------------------|---------------------------|-------------------|
| 1 - 10 | NO (solo importa la version actual) | **Opcion A — Continuacion manual** |
| 1 - 10 | SI (quieren conservar trazabilidad) | **Opcion B — Siembra de version inicial** |
| 11 - 30 | SI o NO | **Opcion B en lote** (con script SQL repetitivo) |
| 30+ | SI o NO | **Opcion C — Migracion masiva con CSV** (a construir cuando se necesite) |

---

## 4. Opcion A — Continuacion manual (pocos documentos)

### Cuando usar

- Cliente con menos de 10 documentos.
- No interesa conservar el historial completo, solo el codigo y arrancar desde version 1.0 en el aplicativo.

### Paso a paso

1. **Entrar al aplicativo como admin del cliente** (consultor admin de la empresa consultora).
2. Crear el cliente en el sistema si aun no existe.
3. Para cada documento que ya trae el cliente:
   1. Ir al panel de generacion de documentos del modulo correspondiente (ej. Politicas, Programas, Procedimientos).
   2. Generar el documento con IA o manualmente.
   3. **Antes de aprobar**, editar el campo `codigo` con el codigo historico del cliente.
      - Ejemplo: si el cliente tenia `POL-SGSST-001-INDPEPITO`, dejarlo igual.
   4. Cargar / pegar el contenido existente del cliente en el editor.
   5. Aprobar el documento. Queda como **version 1.0** con el codigo historico.
4. De ahi en adelante, los cambios siguen el flujo normal: 1.0 → 1.1 → 2.0 → ...

### Limitacion

Se pierde el rastro de versiones anteriores (1.0 a 3.5 que tenia el cliente quedan fuera del aplicativo). Solo se conserva el codigo.

---

## 5. Opcion B — Siembra de version inicial (con historial)

### Cuando usar

- Cliente que esta en version 3.x, 4.x, etc. y quiere conservar la trazabilidad.
- Auditorias futuras pueden requerir ver "se llego a v3.5 antes del aplicativo".

### Paso a paso

**Paso 1 — Crear el documento en el aplicativo**
Igual que en Opcion A: generar, editar `codigo`, cargar contenido, aprobar.

Despues de aprobar, el documento tiene `version=1` y en `tbl_doc_versiones_sst` hay un registro con `version_texto='1.0'`.

**Paso 2 — Sembrar la version historica**
Ejecutar el siguiente SQL para "remontar" la version del aplicativo a la del cliente:

```sql
-- Reemplaza los valores entre {} con los del documento real
SET @id_documento = {ID_DOCUMENTO};       -- id en tbl_documentos_sst
SET @version_real = {VERSION_REAL};        -- ej. 3 si esta en 3.x
SET @version_texto = '{VERSION_TEXTO}';    -- ej. '3.5'
SET @descripcion = 'Punto de continuidad: version heredada del SG-SST previo al ingreso al aplicativo.';

-- 1. Actualizar el documento con la version real
UPDATE tbl_documentos_sst
SET version = @version_real
WHERE id_documento = @id_documento;

-- 2. Actualizar la version inicial creada para que refleje la historica
UPDATE tbl_doc_versiones_sst
SET version_texto = @version_texto,
    version = @version_real,
    descripcion_cambio = @descripcion,
    tipo_cambio = 'mayor'
WHERE id_documento = @id_documento
  AND estado = 'vigente';
```

**Paso 3 — Verificar**

```sql
SELECT d.id_documento, d.codigo, d.version,
       v.version_texto, v.estado, v.descripcion_cambio
FROM tbl_documentos_sst d
JOIN tbl_doc_versiones_sst v ON v.id_documento = d.id_documento
WHERE d.id_documento = @id_documento;
```

Esperado: ver `version=3, version_texto='3.5', estado='vigente'`.

**Paso 4 — Operacion normal**
La proxima edicion en el aplicativo va a llamar al servicio `DocumentoVersionService::aprobarVersion()` y va a calcular automaticamente:

- Si tipo_cambio = `menor`: 3.5 → 3.6
- Si tipo_cambio = `mayor`: 3.5 → 4.0

### Notas

- Esta operacion se hace **una sola vez por documento**, en el momento del onboarding del cliente.
- Si el cliente tiene un PDF firmado de su version actual, conviene archivarlo manualmente en `public/uploads/{nit_cliente}/historico/` para tenerlo disponible aunque no este en el sistema de versiones.
- El campo `contenido_snapshot` de la version sembrada queda con el contenido que se cargo en el editor; si se quiere reflejar el contenido historico exacto, pegarlo antes de aprobar el paso 1.

---

## 6. Opcion C — Migracion masiva (a construir si se necesita)

### Cuando usar

- Cliente con mas de 30 documentos.
- Onboarding de varios clientes simultaneos.

### Diseño previsto (no implementado)

1. **Plantilla CSV** con columnas:
   - `tipo_documento` (ej. `politica_sst`, `procedimiento_control_documental`)
   - `codigo_original`
   - `titulo`
   - `version_actual` (entera)
   - `version_texto` (ej. `3.5`)
   - `fecha_emision`
   - `ruta_archivo_pdf` (relativa, opcional)
   - `contenido_html` (opcional, si se quiere preservar texto editable)

2. **Script CLI**: `scripts/multitenant_07_importar_docs_cliente.php`
   - Argumento: `--id_cliente=N --csv=ruta/al/archivo.csv`
   - Por cada fila: crea `tbl_documentos_sst` + `tbl_doc_versiones_sst` con la version sembrada (mismo patron que Opcion B).
   - Reporta cuantos creo, cuantos fallaron, y por que.

3. **Vista admin** (opcional, futuro): pantalla `/admin/empresas-consultoras/importar-docs-historicos/{id_cliente}` con drag-and-drop del CSV y feedback en pantalla.

### Cuando construir

Cuando un cliente real lo demande. Hasta entonces no vale la pena el esfuerzo: el patron de Opcion B se puede aplicar manualmente para 10-20 docs en menos de 30 minutos.

---

## 7. Reglas que NO se deben romper

1. **NO editar `codigo` despues de haber publicado un PDF al cliente.** El codigo aparece en cada PDF; cambiarlo retroactivamente rompe la trazabilidad documental del cliente. Si hay que corregir, se crea una nueva version mayor.

2. **NO saltar de version 1.0 directamente a 5.0 sin justificacion.** El campo `descripcion_cambio` es el rastro de auditoria; debe explicar el motivo del salto (ej. "homologacion de version historica al ingresar al aplicativo").

3. **NO usar Opcion B sobre un documento que ya tuvo cambios en el aplicativo.** Solo se aplica al documento recien creado, antes de su primera edicion en el sistema.

4. **NO duplicar codigos entre documentos del mismo cliente.** El sistema no lo bloquea hoy a nivel de BD, pero genera confusion. Validar manualmente o con el SQL:

   ```sql
   SELECT codigo, COUNT(*) FROM tbl_documentos_sst
   WHERE id_cliente = {ID} GROUP BY codigo HAVING COUNT(*) > 1;
   ```

5. **NO modificar versiones marcadas como `obsoleto`.** Solo lectura. Para revivir una version anterior, usar el endpoint `/documentos-sst/restaurar-version`.

---

## 8. Plantilla de email para el cliente

Cuando Liliana (o cualquier consultor admin) le explique al cliente como va a funcionar la transicion, puede usar esta plantilla:

```
Asunto: Continuidad de tu SG-SST en EnterpriseSST

Hola [nombre del cliente],

Para continuar tu Sistema de Gestion SST en nuestra plataforma, vamos a hacer
lo siguiente con la documentacion que ya tienes:

1. Cargaremos cada documento (politicas, programas, procedimientos) en el
   aplicativo manteniendo el codigo original que tu empresa ya conocia
   (ej. {ejemplo de codigo del cliente}).

2. La version actual en la que esta cada documento se "sembrara" como punto
   de partida en el sistema. Ejemplo: si tu Politica SST esta en version 3.5,
   en el sistema arrancaremos como version 3.5, no como 1.0.

3. De ahi en adelante, cada vez que actualicemos un documento contigo, el
   sistema le pondra el numero de version siguiente automaticamente y dejara
   trazabilidad de quien lo aprobo, cuando, y que cambio.

4. El historial de versiones anteriores al ingreso al aplicativo lo
   conservaremos como archivo (PDFs/Word) en la carpeta de tu empresa, asi
   tienes acceso completo en caso de auditoria.

¿Puedes enviarnos un listado de los documentos que tienes hoy con su codigo
y version actual? Con eso preparamos la carga inicial.

Cualquier duda me avisas.

Saludos,
[firma del consultor]
```

---

## 9. Resumen para superadmin de Cycloid

Si Liliana (admin de su empresa consultora) o cualquier otro consultor te pregunta:

> ¿Como le doy continuidad al SG-SST de un cliente que ya tenia codigos y versiones?

**Respuesta corta:** Opcion A para clientes pequeños, Opcion B (con SQL de siembra) para clientes con historial. La Opcion C se construye cuando un cliente real tenga 30+ documentos a migrar.

**Donde apuntarles:** este mismo archivo (`docs/MODULO_NUMERALES_SGSST/05_VERSIONAMIENTO/VERSIONES_SGSST_DECLIENTES_QUE_YA_LO_TIENEN.md`).

---

**Fecha de creacion:** 2026-04-19
**Autor:** Equipo Cycloid Talent SAS
**Documentos relacionados:**
- `1_AA_VERSIONAMIENTO.md` — Sistema de versionamiento detallado
- `SISTEMA_VERSIONES_ESTANDARIZADO.md` — Estandarizacion del flujo
- `ZZ_98_HISTORIAL_VERSIONES.md` — Historial de cambios al sistema
