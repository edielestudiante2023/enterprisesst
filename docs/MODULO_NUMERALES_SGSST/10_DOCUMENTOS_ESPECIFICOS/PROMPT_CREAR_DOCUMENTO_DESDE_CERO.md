# Prompt Generico: Crear Documento SST Desde Cero

> **Uso:** Copiar desde `---PROMPT INICIO---` hasta `---PROMPT FIN---`, reemplazar los `{PLACEHOLDERS}` y pegar en un chat nuevo de Claude Code.

---

## Variables a reemplazar ANTES de pegar

| Placeholder | Que poner | Notas |
|-------------|-----------|-------|
| `{ESTANDAR}` | Numero del estandar Res. 0312/2019 | Ej: `2.9.1`, `3.1.7` |
| `{NOMBRE_COMPLETO}` | Nombre formal del documento | Como aparece en la Resolucion |
| `{NOMBRE_CORTO}` | Nombre abreviado para UI | Para botones y titulos |
| `{TIPO_DOCUMENTO_SNAKE}` | snake_case del tipo_documento | Sera la clave en BD, Factory y PHP |
| `{TIPO_KEBAB}` | kebab-case para URLs de vista previa | Solo para rutas web |
| `{CLASE_PHP}` | PascalCase del nombre de clase | Para el archivo .php |
| `{CATEGORIA}` | Categoria del documento | `politicas`, `programas`, `procedimientos`, `planes`, `otros` |
| `{ICONO}` | Icono Bootstrap Icons | Ej: `bi-folder-check`, `bi-shield-check` |
| `{CODIGO_CARPETA}` | Codigo de carpeta en tbl_documentacion_empresa | Ej: `2.9.1`, `5.1.1` |
| `{DESCRIPCION_DOCUMENTO}` | Descripcion para BD | 1-2 frases que expliquen el documento |
| `{NORMATIVIDAD_PRINCIPAL}` | Marco legal principal | Lista de normas aplicables |
| `{N_SECCIONES}` | Numero de secciones | Cantidad total |
| `{LISTADO_SECCIONES}` | Lista numerada de secciones | Formato: `N. seccion_key \| Nombre \| Descripcion del prompt` |
| `{FIRMANTES}` | Quienes firman | Formato: `tipo (Rol, mostrar_licencia=0/1) + tipo (Rol, mostrar_licencia=0/1)` |
| `{TIPO_VISTA}` | Tipo de vista _tipos/ | `Variante A (Adjuntar Soporte)` o `Variante B (Crear con IA)` o `Variante C (Dropdown multiples)` |
| `{METODO_CONTROLLER}` | Nombre del metodo camelCase | Para DocumentosSSTController |

> **NOTA:** El placeholder `{FLUJO}` NO se pone aqui. Se determina en el Paso -1 del prompt (pregunta taxativa al usuario).

### Formato de `{LISTADO_SECCIONES}`
```
1. seccion_key_uno | Nombre Seccion 1 | Instrucciones para la IA sobre que generar en esta seccion
2. seccion_key_dos | Nombre Seccion 2 | Instrucciones para la IA sobre que generar en esta seccion
3. seccion_key_tres | Nombre Seccion 3 | Instrucciones para la IA sobre que generar en esta seccion
...
```

---

## ---PROMPT INICIO---

```
Necesito crear el documento "{NOMBRE_COMPLETO}" (estandar {ESTANDAR} de la Resolucion 0312/2019) DESDE CERO en el proyecto Enterprise SST. NO EXISTE NADA: ni BD, ni clase PHP, ni vista, ni documentacion.

## Datos del documento

| Campo | Valor |
|-------|-------|
| tipo_documento (snake_case) | `{TIPO_DOCUMENTO_SNAKE}` |
| Nombre completo | {NOMBRE_COMPLETO} |
| Nombre corto | {NOMBRE_CORTO} |
| Estandar | {ESTANDAR} |
| Categoria | `{CATEGORIA}` |
| Icono | `{ICONO}` |
| Descripcion | {DESCRIPCION_DOCUMENTO} |
| Clase PHP | `{CLASE_PHP}` |
| Metodo controller | `{METODO_CONTROLLER}` |
| URL generacion | `/documentos/generar/{TIPO_DOCUMENTO_SNAKE}/{id_cliente}` |
| URL vista previa | `/documentos-sst/{id_cliente}/{TIPO_KEBAB}/{anio}` |
| Carpeta BD | `{CODIGO_CARPETA}` |
| Firmantes | {FIRMANTES} |
| Vista _tipos/ | {TIPO_VISTA} |

## Secciones del documento ({N_SECCIONES})

{LISTADO_SECCIONES}

## Normatividad principal
{NORMATIVIDAD_PRINCIPAL}

## INSTRUCCIONES — seguir EN ORDEN ESTRICTO

### Paso -1: PREGUNTAS TAXATIVAS (OBLIGATORIO ANTES DE TODO)

ANTES de leer documentacion, ANTES de escribir codigo, ANTES de hacer cualquier cosa:
Hazle TODAS estas preguntas al usuario en un solo mensaje. **ESPERA las respuestas. NO continues hasta que responda TODAS.**

> **Pregunta 1 — Flujo del documento:**
> Este documento sera de **1 parte (Tipo A)** o de **3 partes (Tipo B)**?
> - **Tipo A (1 parte):** Solo secciones generadas con IA a partir del contexto del cliente. Flujo: `secciones_ia`. Ejemplo: politicas, reglamentos, procedimientos simples.
> - **Tipo B (3 partes):** Requiere PTA (Plan de Trabajo Anual) + Indicadores + Secciones IA. Flujo: `programa_con_pta`. Ejemplo: programas con plan de actividades e indicadores de gestion.
>
> **Pregunta 2 — Carpeta en BD:**
> La carpeta `{CODIGO_CARPETA}` ya existe en `tbl_documentacion_empresa` para los clientes, o hay que crearla?
> - Si **ya existe**: solo necesito mapear el documento a esa carpeta
> - Si **no existe**: necesito modificar el SP `sp_04_generar_carpetas_por_nivel.sql` y crear script de migracion para clientes existentes (esto es un paso adicional grande)
>
> **Pregunta 3 — Vista `_tipos/` existente:**
> Ya existe un archivo en `app/Views/documentacion/_tipos/` para esta carpeta?
> - Si **ya existe** (ej: como Variante A de solo upload): le agrego el boton "Crear con IA" SIN eliminar lo existente
> - Si **no existe**: lo creo desde cero
> Para verificar: buscar en `app/Views/documentacion/_tipos/` un archivo que corresponda al estandar `{ESTANDAR}`
>
> **Pregunta 4 — Documento de referencia:**
> Hay algun documento YA IMPLEMENTADO en el sistema que sea similar a este y sirva como patron?
> - Esto me ayuda a copiar la estructura de secciones, el estilo de prompts y el patron de contenido estatico
> - Ej: "es similar al procedimiento_adquisiciones" o "las secciones son parecidas al programa_capacitacion"
>
> **Pregunta 5 — Solo si respondio Tipo B en Pregunta 1:**
> - Cual es el `tipo_servicio` del PTA que mapea a este documento? (para `getFiltroServicioPTA()`)
> - Cual es la `categoria` de indicador que usa? (para `getCategoriaIndicador()`)
> - Si no lo sabes, dime y lo busco en la BD

**Validacion automatica (NO preguntar, verificar tu mismo):**
Despues de recibir las respuestas, verifica silenciosamente:
- `snakeToPascal('{TIPO_DOCUMENTO_SNAKE}')` produce `{CLASE_PHP}`? Si NO coincide (ej: `reglamento_higiene_seguridad` → `ReglamentoHigieneSeguridad` pero la clase es `ReglamentoHigieneSeguridadIndustrial`), se necesita registro EXPLICITO en el Factory. Avisale al usuario de esta discrepancia.
- El `{TIPO_DOCUMENTO_SNAKE}` ya existe en `tbl_doc_tipo_configuracion`? Ejecutar: `SELECT * FROM tbl_doc_tipo_configuracion WHERE tipo_documento = '{TIPO_DOCUMENTO_SNAKE}'`. Si ya existe, avisar al usuario: "ya hay un registro, voy a actualizar en vez de insertar".

### Paso 0: Leer documentacion segun las respuestas del usuario

Segun lo que haya respondido el usuario en el Paso -1:

**SIEMPRE leer (independiente del tipo):**
1. `memory/checklist-nuevo-documento.md` — flujo completo obligatorio
2. `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md` — REGLAS DE JUEGO: BD unica fuente, PHP solo getContextoBase()
3. `memory/troubleshooting-ia.md` — errores conocidos y como evitarlos

**Si Pregunta 1 = TIPO A (1 parte, `secciones_ia`):**
4. `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md` — guia de reparacion para docs de 1 parte

NO necesitas leer: ZZ_80_PARTE1.md, ZZ_81_PARTE2.md, ZZ_95_PARTE3.md, memory/modulo-3-partes-tipo-b.md (son de Tipo B)

**Si Pregunta 1 = TIPO B (3 partes, `programa_con_pta`):**
4. `memory/modulo-3-partes-tipo-b.md` — resumen condensado del modulo 3 partes (PTA, Indicadores, SweetAlert, Toast, Arboles decision)
5. `docs/MODULO_NUMERALES_SGSST/03_MODULO_3_PARTES/ZZ_95_PARTE3.md` — arquitectura completa del flujo 3-partes
6. `docs/MODULO_NUMERALES_SGSST/03_MODULO_3_PARTES/ZZ_96_PARTE4.md` — guia crear documento nuevo (contexto historico, puede estar desactualizado en algunos metodos)

**Si Pregunta 2 = carpeta NO existe:**
7. Leer el SP actual: `sp_04_generar_carpetas_por_nivel.sql` para entender la estructura de carpetas
8. Buscar un script de migracion de carpetas existente como patron (ej: `agregar_carpeta_1_2_4_reglamento.php`)

**Si Pregunta 3 = vista _tipos/ YA existe:**
9. Leer el archivo `_tipos/` existente para entender que funcionalidad tiene actualmente y que hay que agregar

**Si Pregunta 4 = hay documento de referencia:**
10. Leer la clase PHP del documento de referencia para copiar la estructura
11. Leer el script SQL del documento de referencia para copiar el patron de secciones/prompts

### Paso 1: Documentar (.md PRIMERO)
Crear `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/{CLASE_PHP}.md` con:
- Tabla resumen (tipo_documento, estandar, flujo segun respuesta del Paso -1, firmantes)
- Lista de secciones con seccion_key y descripcion del prompt
- Decisiones de diseno
- Normatividad aplicable
- Si es Tipo B: documentar tipo_servicio PTA y categoria indicador

### Paso 2: Script BD — `app/SQL/agregar_{TIPO_DOCUMENTO_SNAKE}.php`
Seguir EXACTAMENTE el patron de `app/SQL/agregar_procedimiento_adquisiciones.php`:
1. INSERT en `tbl_doc_tipo_configuracion`: tipo_documento, nombre, descripcion, estandar, **flujo segun Paso -1**, categoria, icono, orden
2. INSERT en `tbl_doc_secciones_config`: {N_SECCIONES} secciones con `prompt_ia` COMPLETO (fuente unica de verdad). Cada prompt debe:
   - Incluir normatividad especifica de la seccion
   - Dar instrucciones claras sobre contenido esperado
   - Indicar formato (texto, tabla, lista)
   - Decir "personalizar segun actividad economica de la empresa"
3. INSERT en `tbl_doc_firmantes_config`: {FIRMANTES}
4. INSERT en `tbl_doc_plantillas` y `tbl_doc_plantilla_carpeta` si hay mapeo de carpeta
5. Incluir conexion local + produccion con patron SSL
6. Incluir `ON DUPLICATE KEY UPDATE` en todos los INSERT
7. Incluir verificacion final (contar secciones y firmantes)
8. **EJECUTAR** local primero, produccion solo si local OK

### Paso 2B: Carpeta en documentacion (SOLO si Pregunta 2 = NO existe)
Si el usuario respondio que la carpeta `{CODIGO_CARPETA}` NO existe en `tbl_documentacion_empresa`:
1. Modificar el SP `sp_04_generar_carpetas_por_nivel.sql` para agregar la nueva carpeta
2. Crear script de migracion `app/SQL/agregar_carpeta_{codigo}.php` para clientes existentes (patron: `agregar_carpeta_1_2_4_reglamento.php`)
3. Ejecutar migracion local primero, produccion solo si local OK
4. Verificar que la carpeta aparece en la documentacion del cliente 18

Si el usuario respondio que la carpeta YA existe: saltar este paso.

### Paso 3: Clase PHP — `app/Libraries/DocumentosSSTTypes/{CLASE_PHP}.php`
Seguir patron de `app/Libraries/DocumentosSSTTypes/ReglamentoHigieneSeguridadIndustrial.php`:
- `extends AbstractDocumentoSST`
- Implementar: `getTipoDocumento()`, `getNombre()`, `getDescripcion()`, `getEstandar()`
- Implementar: `getSecciones()` (array con numero, nombre, key)
- Implementar: `getFirmantesRequeridos()` (array de tipos)
- Implementar: `getContenidoEstatico()` con contenido fallback para CADA seccion
- **NO implementar** `getPromptParaSeccion()` (eliminado — prompts van en BD)

**Solo si es Tipo B (respuesta del Paso -1):**
- ADEMAS sobrescribir `getContextoBase()` para consultar PTA + Indicadores
- Agregar entrada en `getFiltroServicioPTA()` del controller con el `tipo_servicio` correcto
- Agregar entrada en `getCategoriaIndicador()` del controller con la `categoria` correcta

Registrar en `DocumentoSSTFactory.php` → `$tiposRegistrados[]`
  - Si `snakeToPascal('{TIPO_DOCUMENTO_SNAKE}')` != `{CLASE_PHP}`: registro EXPLICITO con `'{TIPO_DOCUMENTO_SNAKE}' => {CLASE_PHP}::class`

### Paso 4: Ruta + Controller
1. **Routes.php**: agregar ruta kebab-case:
   `$routes->get('/documentos-sst/(:num)/{TIPO_KEBAB}/(:num)', 'DocumentosSSTController::{METODO_CONTROLLER}/$1/$2');`
2. **DocumentosSSTController.php**: crear metodo `{METODO_CONTROLLER}()` siguiendo patron EXACTO de `identificacionAltoRiesgo()` (linea ~6113):
   - Buscar documento en `tbl_documentos_sst` WHERE tipo_documento = '{TIPO_DOCUMENTO_SNAKE}'
   - `normalizarSecciones()` con '{TIPO_DOCUMENTO_SNAKE}'
   - `configService->obtenerFirmantes('{TIPO_DOCUMENTO_SNAKE}')`
   - View: `'documentos_sst/documento_generico'`

### Paso 5: Vista _tipos/ — `app/Views/documentacion/_tipos/`
Tipo de vista: {TIPO_VISTA}
- **Variante A (Adjuntar Soporte):** Modal de upload, sin boton IA. Patron de referencia: cualquier vista _tipos/ con modal de upload
- **Variante B (Crear con IA):** Boton "Crear con IA {anio}", verifica fases, tabla documentos SST. Patron de referencia: `capacitacion_sst.php`
- **Variante C (Dropdown multiples):** Dropdown con N documentos. Patron de referencia: `politicas_2_1_1.php`

Si la vista YA EXISTE con una variante y ahora necesita TAMBIEN otra:
→ Agregar la nueva funcionalidad SIN eliminar la existente

### Paso 6: Mapeo en acciones_documento.php
Verificar que `app/Views/documentacion/_components/acciones_documento.php` tenga:
1. En `$mapaRutas`: `'{TIPO_DOCUMENTO_SNAKE}' => '{TIPO_KEBAB}/' . $docSST['anio']`
2. En la cadena `elseif` de `$urlEditar`: entrada para '{TIPO_DOCUMENTO_SNAKE}'

### Paso 7: Mapeo en DocumentacionController.php
Verificar que `determinarTipoCarpetaFases()` tenga un case para codigo `{CODIGO_CARPETA}` que retorne el nombre de la vista `_tipos/` (sin extension).

### Paso 8: Verificacion
Ejecutar el script diagnostico (adaptar de `app/SQL/verificar_config_reglamento.php`) para verificar las 4 tablas.
Luego verificar manualmente:
- [ ] `/documentos/generar/{TIPO_DOCUMENTO_SNAKE}/18` → carga sin errores, muestra {N_SECCIONES} secciones
- [ ] SweetAlert aparece al clicar "Generar Todo con IA" (es AUTOMATICO, NO hay que implementarlo)
- [ ] Generacion IA por seccion funciona (cada seccion genera contenido coherente)
- [ ] Guardar y aprobar funciona
- [ ] `/documentos-sst/18/{TIPO_KEBAB}/2026` → carga sin 404
- [ ] Toolbar (PDF, Ver, Editar, Firmas) OK
- [ ] PDF y Word abren en nueva pestana

### Paso 9: Guardar progreso
Actualizar `docs/CONTINUACION_CHAT.md` con lo implementado.

## REGLAS CRITICAS (NO VIOLAR)

1. **PREGUNTAR PRIMERO:** No asumir NADA. Hacer las 5 preguntas del Paso -1 y ESPERAR respuestas antes de escribir una sola linea.
2. **LEER DOCUMENTACION CORRECTA:** Segun las respuestas, leer SOLO los archivos indicados para ese tipo. No leer documentacion de 3-partes si es Tipo A ni viceversa.
3. **CARPETA BD:** Si la carpeta no existe (Pregunta 2), hay que crearla ANTES de continuar. Esto implica SP + migracion. No saltarse este paso o el documento no aparecera en la documentacion del cliente.
4. **VISTA EXISTENTE:** Si la vista `_tipos/` ya existe (Pregunta 3), NO crearla desde cero. AGREGAR funcionalidad a la existente.
5. **BD es fuente unica de verdad** para prompts, secciones y firmantes. NO hardcodear en PHP.
6. **NO implementar** SweetAlert por separado — es codigo compartido en `generar_con_ia.php`
7. **NO implementar** `getPromptParaSeccion()` — fue eliminado, los prompts van en `tbl_doc_secciones_config.prompt_ia`
8. **URLs**: generacion usa snake_case, vista previa usa kebab-case. NUNCA mezclar.
9. **Orden**: preguntas → documentacion → .md → BD → clase PHP → ruta/controller → vista → mapeos → verificacion
10. Si algo falla, consultar `memory/troubleshooting-ia.md` antes de inventar soluciones
```

## ---PROMPT FIN---
