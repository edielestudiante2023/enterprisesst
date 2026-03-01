# Guia Paso a Paso: Crear Documento Tipo A (Politica, Manual, Procedimiento o Programa Simple)

> **Uso:** Seguir esta guia en orden estricto cada vez que se necesite crear un documento nuevo
> que use el flujo `secciones_ia` (1 parte, sin PTA ni indicadores).
>
> **Placeholders:** Los valores entre `{LLAVES}` se reemplazan por los datos reales del documento.
>
> **Validada contra implementacion real:** `procedimiento_auditoria_anual` (6.1.2) — 2026-03-01.
> Ver `ProcedimientoAuditoriaAnual.md` para el ejemplo concreto.

---

## Antes de Empezar: Que es un Documento Tipo A

Un documento Tipo A genera contenido con IA usando **unicamente** el contexto del cliente.
No necesita Plan de Trabajo (PTA) ni Indicadores. El flujo en BD es `secciones_ia`.

**Aplica para:** Politicas, manuales, procedimientos, reglamentos, metodologias, mecanismos,
identificaciones, y programas simples que no requieren actividades ni indicadores.

**NO aplica para:** Programas con PTA e indicadores (esos son Tipo B, flujo `programa_con_pta`).

---

## PASO 1: Definir el Documento

**Que hacer:** Identificar todos los datos del documento nuevo.

**Entrada:** Un numeral de la Resolucion 0312/2019 o un requerimiento del cliente.

**Datos a definir:**

| Campo | Que es | Placeholder | Ejemplo |
|-------|--------|-------------|---------|
| `tipo_documento` | Identificador snake_case unico | `{TIPO_DOCUMENTO}` | `procedimiento_auditoria_anual` |
| Nombre completo | Como aparece en encabezados | `{NOMBRE_DOCUMENTO}` | Procedimiento de Auditoria Anual del SG-SST |
| Nombre corto | Para botones y titulos | `{NOMBRE_CORTO}` | Auditoria Anual |
| Estandar | Numeral Res. 0312/2019 | `{ESTANDAR}` | 6.1.2 |
| Categoria | Agrupacion logica | `{CATEGORIA}` | `procedimientos` |
| Flujo | Siempre para Tipo A | `secciones_ia` | (fijo) |
| Clase PHP (PascalCase) | Nombre de la clase | `{CLASE_PHP}` | `ProcedimientoAuditoriaAnual` |
| Metodo controller (camelCase) | Para vista web | `{METODO_CONTROLLER}` | `procedimientoAuditoriaAnual` |
| URL generacion | Editor IA (snake_case) | `/documentos/generar/{TIPO_DOCUMENTO}/{id}` | `/documentos/generar/procedimiento_auditoria_anual/18` |
| URL vista | Vista web (kebab-case) | `/documentos-sst/{id}/{TIPO_KEBAB}/{anio}` | `/documentos-sst/18/procedimiento-auditoria-anual/2026` |
| tipoCarpetaFases | Nombre CORTO de carpeta | `{TIPO_CARPETA}` | `auditoria_anual` |
| Codigo plantilla | Para tbl_doc_plantillas | `{CODIGO_PLANTILLA}` | `PRC-AUD` |
| Secciones | Lista numerada con key y nombre | (definir 5-12 secciones) | Ver nota abajo |
| Firmantes | Quienes firman | (tipicamente 2) | responsable_sst + representante_legal |

> **IMPORTANTE — tipoCarpetaFases vs tipo_documento:**
> El `{TIPO_CARPETA}` es un nombre CORTO para la carpeta, NO es el mismo que `{TIPO_DOCUMENTO}`.
> Ejemplos reales: `procedimiento_adquisiciones` → `adquisiciones_sst`, `procedimiento_auditoria_anual` → `auditoria_anual`.
> La vista `_tipos/` se llama `{TIPO_CARPETA}.php`.

**Secciones tipicas para un Tipo A:**

La mayoria de procedimientos/politicas usan entre 5-12 secciones. Patron comun:

| # | seccion_key | Nombre | Uso tipico |
|---|-------------|--------|------------|
| 1 | `objetivo` | Objetivo | Proposito del documento |
| 2 | `alcance` | Alcance | A quien aplica |
| 3 | `definiciones` | Definiciones | Terminos clave |
| 4 | `marco_legal` | Marco Legal | Normativa colombiana |
| 5 | `responsabilidades` | Responsabilidades | Roles y funciones |
| 6+ | `{seccion_especifica}` | (Varia segun el documento) | Contenido tecnico |

> **NOTA:** La seccion `control_cambios` NO va como seccion del documento. El control de cambios
> lo maneja automaticamente el sistema de versionamiento (`tbl_doc_versiones_sst`).

**Documentacion de apoyo para este paso:**
> Ninguna — este paso es de analisis y conocimiento del negocio.
> Si no sabes que secciones lleva un tipo de documento, consulta la Resolucion 0312/2019
> o pregunta al especialista SST.

---

## PASO 2: Documentar el Diseno (.md PRIMERO)

**Que hacer:** Crear un archivo `.md` con todo lo definido en el Paso 1.

**Archivo a crear:** `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/{CLASE_PHP}.md`

**Contenido minimo del .md:**
```
# {NOMBRE_DOCUMENTO}
## Metadata
- tipo_documento: {TIPO_DOCUMENTO}
- estandar: {ESTANDAR}
- flujo: secciones_ia
- categoria: {CATEGORIA}
- tipoCarpetaFases: {TIPO_CARPETA}
- codigo_plantilla: {CODIGO_PLANTILLA}
## Secciones
[tabla con las N secciones, seccion_key, nombre, descripcion del prompt]
## Firmantes
[tabla con firmante_tipo, rol_display, orden, mostrar_licencia]
## Decisiones de diseno
[por que estas secciones, que normatividad aplica, que se descarto]
## URLs
- Generacion: /documentos/generar/{TIPO_DOCUMENTO}/{id}
- Vista: /documentos-sst/{id}/{TIPO_KEBAB}/{anio}
## Archivos creados y modificados
[lista de archivos — llenar al terminar la implementacion]
```

**Regla:** Documentacion-primero. NO escribir codigo hasta que el .md exista.

**Documentacion de apoyo:**
> - `memory/checklist-nuevo-documento.md` → Paso 0 (regla de documentar primero)

---

## PASO 3: Crear Script BD y Ejecutarlo

**Que hacer:** Insertar la configuracion del documento en las 5 tablas.

**Archivo a crear:** `app/SQL/agregar_{TIPO_DOCUMENTO}.php`

**Las 5 tablas a poblar (en este orden):**

1. **`tbl_doc_tipo_configuracion`** — Registro principal del tipo
   ```sql
   INSERT INTO tbl_doc_tipo_configuracion
   (tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
   VALUES ('{TIPO_DOCUMENTO}', '{NOMBRE_DOCUMENTO}',
           '{DESCRIPCION}', '{ESTANDAR}', 'secciones_ia', '{CATEGORIA}', '{ICONO}', {ORDEN})
   ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
   ```

2. **`tbl_doc_secciones_config`** — Secciones con prompts IA (FUENTE UNICA)
   ```sql
   INSERT INTO tbl_doc_secciones_config
   (id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
   SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
   FROM tbl_doc_tipo_configuracion tc
   CROSS JOIN (
       SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, 1 as orden,
              'Genera el objetivo de...' as prompt_ia
       UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2, '...'
       -- ... mas secciones
   ) s
   WHERE tc.tipo_documento = '{TIPO_DOCUMENTO}'
   ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
   ```
   > Los `prompt_ia` son la UNICA fuente de verdad para la IA. Deben ser detallados,
   > incluir normatividad especifica y pedir "personalizar segun actividad economica".

3. **`tbl_doc_firmantes_config`** — Firmantes del documento
   ```sql
   INSERT INTO tbl_doc_firmantes_config
   (id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
   SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
   FROM tbl_doc_tipo_configuracion tc
   CROSS JOIN (
       SELECT 'responsable_sst' as firmante_tipo, 'Elaboro' as rol_display,
              'Elaboro / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
       UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
   ) f
   WHERE tc.tipo_documento = '{TIPO_DOCUMENTO}'
   ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
   ```

4. **`tbl_doc_plantillas`** — Plantilla con codigo sugerido
   ```sql
   INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
   SELECT 3, '{NOMBRE_DOCUMENTO}', '{CODIGO_PLANTILLA}', '{TIPO_DOCUMENTO}', '001', 1
   FROM DUAL
   WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = '{TIPO_DOCUMENTO}')
   ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
   ```

5. **`tbl_doc_plantilla_carpeta`** — Mapeo plantilla → carpeta en arbol
   ```sql
   INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
   VALUES ('{CODIGO_PLANTILLA}', '{ESTANDAR}')
   ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
   ```
   > Sin este mapeo, el documento no se asocia a la carpeta correcta en el arbol.

**Ejecutar:**
```bash
php app/SQL/agregar_{TIPO_DOCUMENTO}.php
```
LOCAL primero. PRODUCCION solo si LOCAL funciona.

**Documentacion de apoyo:**
> - `memory/checklist-nuevo-documento.md` → Paso 1 (BD)
> - `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md` → seccion "Las Tres Tablas de Configuracion" y "Checklist: Crear un Nuevo Documento"
> - `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 6 (plantilla completa del script SQL)

**Patron de referencia:** Copiar estructura de `app/SQL/agregar_procedimiento_adquisiciones.php`
o `app/SQL/agregar_procedimiento_auditoria_anual.php`

**Verificar despues de ejecutar:**
```sql
SELECT * FROM tbl_doc_tipo_configuracion WHERE tipo_documento = '{TIPO_DOCUMENTO}';
SELECT COUNT(*) FROM tbl_doc_secciones_config WHERE id_tipo_config = @id;
SELECT COUNT(*) FROM tbl_doc_firmantes_config WHERE id_tipo_config = @id;
```

---

## PASO 4: Crear Clase PHP y Registrar en Factory

**Que hacer:** Crear la clase del documento y registrarla en el Factory.

### 4A. Crear la clase

**Archivo a crear:** `app/Libraries/DocumentosSSTTypes/{CLASE_PHP}.php`

**Metodos a implementar:**

| Metodo | Que retorna | Obligatorio |
|--------|-------------|-------------|
| `getTipoDocumento()` | `'{TIPO_DOCUMENTO}'` (snake_case exacto de BD) | SI |
| `getNombre()` | Nombre legible del documento | SI |
| `getDescripcion()` | Descripcion corta | SI |
| `getEstandar()` | `'{ESTANDAR}'` | SI |
| `getSecciones()` | Array de secciones (fallback si BD vacia) | SI (legacy) |
| `getFirmantesRequeridos()` | Array de tipos de firmante | SI (legacy) |
| `getContenidoEstatico()` | Contenido fallback por seccion | SI (legacy) |

**Metodos que NO se implementan:**

| Metodo | Por que NO | Donde vive eso |
|--------|-----------|----------------|
| `getPromptParaSeccion()` | Eliminado — prompts en BD | `tbl_doc_secciones_config.prompt_ia` |
| `getContextoBase()` | Solo Tipo B lo sobrescribe | `AbstractDocumentoSST` tiene el generico |

**Documentacion de apoyo:**
> - `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md` → seccion "Responsabilidades de la Clase PHP" y "Que NO hace la clase PHP"
> - `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` → seccion "Referencia: Documentos Tipo A Existentes" (lista de clases existentes)
> - `memory/checklist-nuevo-documento.md` → Paso 2 (clase PHP)

**Patron de referencia:** Copiar estructura de `app/Libraries/DocumentosSSTTypes/ProcedimientoAdquisiciones.php`
o `app/Libraries/DocumentosSSTTypes/ProcedimientoAuditoriaAnual.php`

### 4B. Registrar en Factory

**Archivo a modificar:** `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

Agregar al array `$tiposRegistrados`:
```php
// {ESTANDAR} {NOMBRE_CORTO}
'{TIPO_DOCUMENTO}' => {CLASE_PHP}::class,
```

> **Nota:** Si `snakeToPascal('{TIPO_DOCUMENTO}')` produce exactamente `{CLASE_PHP}`,
> el Factory lo resuelve automaticamente. Si no coincide, el registro explicito es OBLIGATORIO.

---

## PASO 5: Crear Ruta y Metodo en Controller (Vista Web)

**Que hacer:** Registrar la URL de vista previa y crear el metodo que carga el documento.

### 5A. Agregar rutas en Routes.php

**Archivo a modificar:** `app/Config/Routes.php`

```php
// {ESTANDAR} {NOMBRE_CORTO}
$routes->get('/documentos-sst/(:num)/{TIPO_KEBAB}/(:num)',
    'DocumentosSSTController::{METODO_CONTROLLER}/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-{SOPORTE_KEBAB}',
    'DocumentosSSTController::adjuntarSoporte{METODO_ADJUNTAR}');
```

> **Regla de URLs:**
> - Generacion IA: snake_case → `/documentos/generar/{TIPO_DOCUMENTO}/18`
> - Vista web: kebab-case → `/documentos-sst/18/{TIPO_KEBAB}/2026`
> - Adjuntar soporte: kebab-case → `/documentos-sst/adjuntar-soporte-{SOPORTE_KEBAB}`
> - NUNCA mezclar.

> **NOTA:** La ruta de generacion `/documentos/generar/(:segment)/(:num)` es **generica** y
> no necesita registro individual — funciona automaticamente si BD tiene `tbl_doc_tipo_configuracion`.

### 5B. Crear metodo vista previa en DocumentosSSTController

**Archivo a modificar:** `app/Controllers/DocumentosSSTController.php`

Copiar el patron EXACTO de un metodo existente (ej: `identificacionAltoRiesgo()` linea ~6156).
El metodo debe:

1. Buscar documento en `tbl_documentos_sst` por id_cliente + `'{TIPO_DOCUMENTO}'` + anio
2. Decodificar contenido JSON
3. Normalizar secciones: `$this->normalizarSecciones($contenido['secciones'], '{TIPO_DOCUMENTO}')`
4. Obtener firmantes: `$this->configService->obtenerFirmantes('{TIPO_DOCUMENTO}')`
5. Cargar versiones, responsables, contexto, consultor, firmas electronicas
6. Retornar: `view('documentos_sst/documento_generico', $data)`

> **NO se necesita** crear una vista custom. Todos los Tipo A usan `documento_generico`.

### 5C. Crear metodo adjuntarSoporte

Usar el metodo generico existente `adjuntarSoporteGenerico()`:
```php
public function adjuntarSoporte{METODO_ADJUNTAR}()
{
    return $this->adjuntarSoporteGenerico(
        'soporte_{TIPO_SOPORTE}',     // tipo_documento del soporte
        '{CODIGO_SOPORTE}',           // codigo base (ej: SOP-AUA)
        'soporte_{TIPO_SOPORTE}_',    // prefijo del nombre de archivo
        'Soporte de {NOMBRE_CORTO}',
        'Soporte adjuntado exitosamente.'
    );
}
```

**Documentacion de apoyo:**
> - `memory/checklist-nuevo-documento.md` → Paso 3 (ruta + controller)
> - `memory/vista-web-estandar.md` → Estructura de la pagina web (toolbar, secciones, firmas, etc.)
> - `memory/toolbar-documentos.md` → 5 reglas obligatorias del toolbar (target blank, firmas, orden)

---

## PASO 6: Integrar en Carpeta de Documentacion

**Que hacer:** Hacer que el documento aparezca en la carpeta del cliente y que los botones funcionen.

### 6A. Crear vista _tipos/

**Archivo a crear:** `app/Views/documentacion/_tipos/{TIPO_CARPETA}.php`

Hay 3 variantes posibles:

| Variante | Cuando usarla | Patron a copiar |
|----------|---------------|-----------------|
| A: Solo adjuntar soporte | La carpeta solo sube archivos | `afiliacion_srl.php` |
| B: Crear con IA + adjuntar | La carpeta genera documentos con IA | `adquisiciones_sst.php` |
| C: Dropdown multiples | La carpeta tiene varios tipos en dropdown | `politicas_2_1_1.php` |

La mayoria de Tipo A usa variante B. Elementos clave:
- Boton "Crear con IA" → enlaza a `/documentos/generar/{TIPO_DOCUMENTO}/{id_cliente}`
- Boton "Adjuntar" → abre modal que postea a `/documentos-sst/adjuntar-soporte-{SOPORTE_KEBAB}`
- `tabla_documentos_sst` con `tipoCarpetaFases` = `'{TIPO_CARPETA}'`
- `tabla_soportes` para soportes adjuntos

**Documentacion de apoyo:**
> - `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 1 (plantilla completa vista _tipos)
> - `memory/adjuntar-soporte.md` → Si la carpeta necesita carga de archivos ademas de IA

### 6B. Mapeo en DocumentacionController (4 cambios)

**Archivo a modificar:** `app/Controllers/DocumentacionController.php`

**Cambio 1 — `determinarTipoCarpetaFases()`:** Agregar condicion que detecte la carpeta:
```php
// {ESTANDAR}. {NOMBRE_CORTO}
// CUIDADO: codigos mas especificos ANTES (ej: 6.1.2 ANTES de 6.1.4)
if ($codigo === '{ESTANDAR}' ||
    strpos($nombre, '{PALABRA_CLAVE_1}') !== false && strpos($nombre, '{PALABRA_CLAVE_2}') !== false) {
    return '{TIPO_CARPETA}';
}
```

**Cambio 2 — array `in_array(...)` de documentosSSTAprobados:** Agregar `'{TIPO_CARPETA}'`:
```php
if (in_array($tipoCarpetaFases, ['capacitacion_sst', ..., '{TIPO_CARPETA}', ...])) {
```

**Cambio 3 — filtro `elseif` del query de tipo_documento:**
```php
} elseif ($tipoCarpetaFases === '{TIPO_CARPETA}') {
    // {ESTANDAR}: {NOMBRE_CORTO}
    $queryDocs->where('tipo_documento', '{TIPO_DOCUMENTO}');
}
```

**Cambio 4 — `soportesAdicionales`:** Agregar elseif para cargar soportes adjuntos:
```php
} elseif ($tipoCarpetaFases === '{TIPO_CARPETA}') {
    // {ESTANDAR} {NOMBRE_CORTO}
    $db = $db ?? \Config\Database::connect();
    $soportesAdicionales = $db->table('tbl_documentos_sst')
        ->where('id_cliente', $cliente['id_cliente'])
        ->where('tipo_documento', 'soporte_{TIPO_SOPORTE}')
        ->orderBy('created_at', 'DESC')
        ->get()->getResultArray();
}
```
> Sin este cambio, la tabla de soportes adjuntos NO mostrara nada en la carpeta.

**Documentacion de apoyo:**
> - `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 2 (DocumentacionController)

### 6C. Componente acciones_documento.php (2 cambios)

**Archivo a modificar:** `app/Views/documentacion/_components/acciones_documento.php`

**Cambio 1 — `$mapaRutas`:**
```php
'{TIPO_DOCUMENTO}' => '{TIPO_KEBAB}/' . $docSST['anio'],
```

**Cambio 2 — `$urlEditar`:**
```php
} elseif ($tipoDoc === '{TIPO_DOCUMENTO}') {
    $urlEditar = base_url('documentos/generar/{TIPO_DOCUMENTO}/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
```

> **Sin esto:** El boton "Ver" da 404 y el boton "Editar" no aparece.

**Documentacion de apoyo:**
> - `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 4 (acciones_documento)

### 6D. Componente tabla_documentos_sst.php (1 cambio)

**Archivo a modificar:** `app/Views/documentacion/_components/tabla_documentos_sst.php`

Agregar `'{TIPO_CARPETA}'` al array `$tiposConTabla`:
```php
$tiposConTabla = ['capacitacion_sst', ..., '{TIPO_CARPETA}', ...];
```

**Documentacion de apoyo:**
> - `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 3 (tabla_documentos_sst)

---

## PASO 7: Verificar que Todo Funciona

**Que hacer:** Probar el flujo completo end-to-end.

### Checklist de verificacion

**Generacion IA:**
- [ ] `/documentos/generar/{TIPO_DOCUMENTO}/{id_cliente}` carga sin errores
- [ ] Se ven las N secciones (NO `[Seccion no definida]`)
- [ ] SweetAlert aparece al clicar "Generar Todo con IA" (es AUTOMATICO, NO hay que implementarlo)
- [ ] Cada seccion genera contenido coherente con IA
- [ ] Guardar seccion funciona
- [ ] Aprobar seccion funciona
- [ ] Aprobar documento funciona (boton se habilita cuando todas las secciones estan aprobadas)

**Vista web:**
- [ ] `/documentos-sst/{id_cliente}/{TIPO_KEBAB}/{anio}` carga sin 404
- [ ] Toolbar muestra: PDF, Word, Editar, Firmas, Historial, Volver
- [ ] Todos los botones abren en nueva pestana (`target="_blank"`)
- [ ] PDF genera correctamente
- [ ] Word genera correctamente

**Carpeta:**
- [ ] La carpeta del numeral {ESTANDAR} muestra el boton "Crear con IA"
- [ ] Despues de generar, el documento aparece en la tabla
- [ ] Botones de acciones (Ver, Editar, PDF) funcionan

**Documentacion de apoyo para diagnosticar problemas:**
> - `memory/troubleshooting-ia.md` → 8 problemas comunes y sus fixes
> - `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/1_A_TROUBLESHOOTING_GENERACION_IA.md` → Diagnostico detallado con queries SQL
> - `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` → Diagrama de decision "SweetAlert muestra datos raros?"

---

## PASO 8: Guardar Progreso

**Que hacer:** Actualizar documentacion y progreso.

1. Actualizar el `.md` del Paso 2 con cualquier decision tomada durante la implementacion
2. Agregar al `.md` la lista de archivos creados y modificados
3. Si la sesion fue larga, actualizar `docs/CONTINUACION_CHAT.md` con el estado
4. Commit con los archivos creados/modificados

---

## Resumen: Archivos Tocados por Paso

| Paso | Archivos | Accion |
|------|----------|--------|
| 2 | `docs/.../10_DOCUMENTOS_ESPECIFICOS/{CLASE_PHP}.md` | CREAR |
| 3 | `app/SQL/agregar_{TIPO_DOCUMENTO}.php` | CREAR + EJECUTAR (5 tablas) |
| 4A | `app/Libraries/DocumentosSSTTypes/{CLASE_PHP}.php` | CREAR |
| 4B | `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` | MODIFICAR (agregar al array) |
| 5A | `app/Config/Routes.php` | MODIFICAR (GET vista + POST adjuntar) |
| 5B | `app/Controllers/DocumentosSSTController.php` | MODIFICAR (metodo vista + metodo adjuntar) |
| 6A | `app/Views/documentacion/_tipos/{TIPO_CARPETA}.php` | CREAR |
| 6B | `app/Controllers/DocumentacionController.php` | MODIFICAR (**4 cambios**: determinar, in_array, filtro, soportes) |
| 6C | `app/Views/documentacion/_components/acciones_documento.php` | MODIFICAR (2 cambios: mapaRutas + urlEditar) |
| 6D | `app/Views/documentacion/_components/tabla_documentos_sst.php` | MODIFICAR (agregar al array tiposConTabla) |

**Total:** 4 archivos nuevos + 6 archivos modificados

---

## Tabla de Placeholders

| Placeholder | Como se obtiene | Ejemplo |
|-------------|-----------------|---------|
| `{TIPO_DOCUMENTO}` | snake_case del nombre del documento | `procedimiento_auditoria_anual` |
| `{NOMBRE_DOCUMENTO}` | Nombre completo para encabezados | Procedimiento de Auditoria Anual del SG-SST |
| `{NOMBRE_CORTO}` | Nombre para botones/titulos | Auditoria Anual |
| `{ESTANDAR}` | Numeral de la Res. 0312/2019 | 6.1.2 |
| `{CATEGORIA}` | Tipo de documento | `procedimientos`, `politicas`, `programas` |
| `{CLASE_PHP}` | PascalCase de `{TIPO_DOCUMENTO}` | `ProcedimientoAuditoriaAnual` |
| `{METODO_CONTROLLER}` | camelCase de `{TIPO_DOCUMENTO}` | `procedimientoAuditoriaAnual` |
| `{TIPO_KEBAB}` | kebab-case de `{TIPO_DOCUMENTO}` | `procedimiento-auditoria-anual` |
| `{TIPO_CARPETA}` | Nombre CORTO para tipoCarpetaFases | `auditoria_anual` |
| `{CODIGO_PLANTILLA}` | Codigo corto del doc (3-4 letras) | `PRC-AUD` |
| `{ICONO}` | Icono Bootstrap Icons | `bi-clipboard-check` |
| `{TIPO_SOPORTE}` | snake_case para soportes adjuntos | `auditoria_anual` |
| `{SOPORTE_KEBAB}` | kebab-case para ruta soporte | `auditoria-anual` |
| `{CODIGO_SOPORTE}` | Codigo base del soporte | `SOP-AUA` |
| `{METODO_ADJUNTAR}` | PascalCase para metodo adjuntar | `AuditoriaAnual` |

---

## Indice de Documentacion de Referencia

### Obligatorio leer ANTES de implementar

| Archivo | Para que sirve | Cuando leerlo |
|---------|----------------|---------------|
| `memory/checklist-nuevo-documento.md` | Checklist maestro con los 5 pasos | Antes de empezar — resume todo el proceso |
| `docs/.../02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md` | Reglas de juego: BD fuente unica, PHP solo para logica | Antes del Paso 3 — entender que va en BD vs PHP |

### Leer segun el paso

| Archivo | Para que sirve | En que paso |
|---------|----------------|-------------|
| `docs/.../10_.../INSTRUCTIVO_DUPLICACION_MODULOS.md` | Plantillas de codigo para vista, controller, SQL | Pasos 3, 6A, 6B, 6C, 6D |
| `docs/.../02_GENERACION_IA/1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` | Diagnostico de problemas Tipo A, lista de docs existentes | Paso 4 (patron de clase) y Paso 7 (troubleshooting) |
| `memory/vista-web-estandar.md` | Estructura de la pagina web (toolbar, secciones, firmas) | Paso 5B (vista web) |
| `memory/toolbar-documentos.md` | 5 reglas obligatorias del toolbar | Paso 5B |
| `memory/adjuntar-soporte.md` | Patron para carpetas que ademas cargan archivos | Paso 6A (si aplica) |
| `memory/troubleshooting-ia.md` | 8 problemas comunes con diagnostico rapido | Paso 7 (si algo falla) |

### Leer si necesitas exportaciones o firmas

| Archivo | Para que sirve | Cuando |
|---------|----------------|--------|
| `memory/pdf-estandar.md` | Estilos PDF (encabezado, cuerpo, firmas, control cambios) | Si el PDF no se ve bien |
| `memory/word-estandar.md` | Estilos Word (diferencias con PDF, MSO directivas) | Si el Word no se ve bien |
| `memory/firmas-sistema.md` | Sistema de firmas, delegado SST, flujo electronico | Si las firmas no aparecen o hay error |
| `memory/versionamiento.md` | DocumentoVersionService, maquina de estados | Si el versionamiento falla |

---

## Errores Frecuentes y Donde Buscar

| Error | Causa probable | Documentacion |
|-------|---------------|---------------|
| `[Seccion no definida]` | Clase PHP falta o no esta en Factory | `1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` → Problema 1 |
| SweetAlert se queda cargando | Clase PHP falta o error en endpoint | `1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` → Problema 1 |
| SweetAlert muestra PTA de otros modulos | Es Tipo A pero backend no filtra por flujo | `1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` → Problema 2 |
| 404 en vista previa | Falta ruta en Routes.php o metodo en controller | `memory/checklist-nuevo-documento.md` → Paso 3 |
| Boton "Ver" no funciona | Falta en `$mapaRutas` de acciones_documento.php | `INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 4 |
| Boton "Editar" no aparece | Falta `elseif` en `$urlEditar` | `INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 4 |
| Documento no aparece en carpeta | Falta `determinarTipoCarpetaFases()` o filtro en `carpeta()` | `INSTRUCTIVO_DUPLICACION_MODULOS.md` → Paso 2 |
| Soportes no aparecen | Falta cambio 4 en DocumentacionController (soportesAdicionales) | Esta guia → Paso 6B cambio 4 |
| Asteriscos visibles en contenido | Falta Parsedown en vista | `1_A_TROUBLESHOOTING.md` → Problema 11 |
| Estado queda en "borrador" | Falta boton "Aprobar Documento" | `1_A_TROUBLESHOOTING.md` → seccion "Documento queda en borrador" |
| Codigo DOC-GEN-001 | Falta codigo en Factory o en `tbl_doc_plantillas` | `TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md` |

---

## Que NO Hacer

| Prohibido | Por que | Alternativa |
|-----------|---------|-------------|
| Hardcodear prompts en PHP | BD es fuente unica de verdad | Ponerlos en `tbl_doc_secciones_config.prompt_ia` |
| Implementar `getPromptParaSeccion()` | Fue eliminado de la arquitectura | Los prompts se leen de BD via `DocumentoConfigService` |
| Crear SweetAlert por separado | Es codigo compartido en `generar_con_ia.php` | Solo registrar en `tbl_doc_tipo_configuracion` con flujo correcto |
| Crear vista custom de documento | Todos los Tipo A usan `documento_generico` | Retornar `view('documentos_sst/documento_generico', $data)` |
| Mezclar snake_case y kebab-case | Generacion usa snake_case, vista usa kebab-case | Revisar regla de URLs en `memory/checklist-nuevo-documento.md` |
| Ejecutar script SQL en produccion primero | Puede romper datos | LOCAL primero, PRODUCCION solo si LOCAL OK |
| Poner `control_cambios` como seccion IA | El versionamiento lo maneja el sistema | El sistema usa `tbl_doc_versiones_sst` automaticamente |
| Usar `{TIPO_DOCUMENTO}` como `tipoCarpetaFases` | Son nombres diferentes | Definir `{TIPO_CARPETA}` como nombre CORTO |

---

*Guia creada: 2026-03-01*
*Validada contra implementacion real: procedimiento_auditoria_anual (6.1.2) — 2026-03-01*
*Ver `ProcedimientoAuditoriaAnual.md` para el ejemplo concreto de una implementacion completa.*
