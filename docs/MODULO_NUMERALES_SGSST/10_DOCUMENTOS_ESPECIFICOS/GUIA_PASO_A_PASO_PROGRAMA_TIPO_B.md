# Guia Paso a Paso: Crear Programa Tipo B (3 Partes: Actividades + Indicadores + Documento)

> **Uso:** Seguir esta guia en orden estricto cada vez que se necesite crear un programa nuevo
> que use el flujo `programa_con_pta` (3 partes: PTA, Indicadores, Documento formal).
>
> **Ejemplo de referencia:** A lo largo de la guia se usa como ejemplo el numeral
> **4.2.4** "Programa de Inspecciones a instalaciones, maquinaria o equipos" (`programa_inspecciones`).
>
> **Programa de referencia ya implementado:** PVE Riesgo Biomecanico (`pve_riesgo_biomecanico`).
> Todos los patrones de codigo se copian de ahi.

---

## Antes de Empezar: Que es un Programa Tipo B

Un programa Tipo B genera contenido con IA usando **tres fuentes de datos en cadena**:

```
Parte 1 (Actividades PTA) → BD → Parte 2 (Indicadores) → BD → Parte 3 (Documento formal con IA)
```

La IA del documento formal CONSUME los datos reales generados en Parte 1 y Parte 2.
Sin datos previos en BD, la IA NO puede generar contenido confiable.

**Aplica para:** Programas de vigilancia epidemiologica (PVE), programas de prevencion,
programas de promocion, y cualquier programa que requiera actividades planificadas + indicadores de medicion.

**NO aplica para:** Politicas, manuales, procedimientos simples (esos son Tipo A, flujo `secciones_ia`).
Para Tipo A ver: `GUIA_PASO_A_PASO_DOCUMENTO_TIPO_A.md`

**Documentacion de arquitectura:**
> - `docs/.../03_MODULO_3_PARTES/AA_3_PARTES_PROGRAMA.md` → Concepto fundamental, cadena de dependencias
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Referencia tecnica de 13 pasos

---

## PASO 1: Definir el Programa (Nomenclatura)

**Que hacer:** Definir los 6 identificadores ANTES de tocar codigo.

**Entrada:** Un numeral de la Resolucion 0312/2019 + un requerimiento especifico.

**Ejemplo:**
> Numeral 4.2.4: "Realizacion de inspecciones a instalaciones, maquinaria o equipos con participacion del COPASST"
> → Necesitamos un "Programa de Inspecciones" con actividades, indicadores y documento formal.

**Los 6 identificadores a definir:**

| Concepto | Donde se usa | Formato | Ejemplo 4.2.4 |
|----------|-------------|---------|----------------|
| `tipo_documento` | Factory, BD, URL Part 3 | `snake_case` | `programa_inspecciones` |
| `tipo_servicio` | PTA, filtros Part 1 | Texto libre | `Programa de Inspecciones` |
| `categoria` | Indicadores, filtros Part 2 | `snake_case` | `inspecciones` |
| Clase PHP | Factory key → class | `PascalCase` | `ProgramaInspecciones` |
| `tipoCarpetaFases` | FasesDocumentoService | `snake_case` | `programa_inspecciones` |
| Slug URL (kebab) | Routes generador-ia | `kebab-case` | `programa-inspecciones` |

**Secciones del documento formal (Parte 3):**

| # | seccion_key | Nombre |
|---|-------------|--------|
| 1 | objetivo | Objetivo |
| 2 | alcance | Alcance |
| 3 | marco_normativo | Marco Normativo |
| 4 | definiciones | Definiciones |
| 5 | responsabilidades | Responsabilidades |
| 6 | tipos_inspecciones | Tipos de Inspecciones |
| 7 | metodologia | Metodologia de Inspeccion |
| 8 | cronograma_inspecciones | Cronograma de Inspecciones |
| 9 | hallazgos_acciones | Hallazgos y Acciones Correctivas |
| 10 | indicadores_gestion | Indicadores de Gestion |
| 11 | control_cambios | Control de Cambios |

**Firmantes:** responsable_sst (Elaboro) + representante_legal (Aprobo)

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Seccion "Nomenclatura"
> - Tabla de referencia de programas implementados al final de ZZ_98

---

## PASO 2: Documentar el Diseno (.md PRIMERO)

**Que hacer:** Crear un archivo `.md` con todo lo definido en el Paso 1.

**Archivo a crear:** `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/{CLASE_PHP}.md`

**Ejemplo:** `docs/.../10_DOCUMENTOS_ESPECIFICOS/ProgramaInspecciones.md`

**Contenido minimo del .md:**
```
# Programa de Inspecciones (4.2.4)
- tipo_documento: programa_inspecciones
- tipo_servicio: Programa de Inspecciones
- categoria: inspecciones
- estandar: 4.2.4
- flujo: programa_con_pta
- firmantes: responsable_sst + representante_legal
- secciones: [tabla con las N secciones]
- actividades Part 1: [lista de 12 actividades con mes y ciclo PHVA]
- indicadores Part 2: [lista de 6-8 indicadores con tipo y meta]
```

**Regla:** Documentacion-primero. NO escribir codigo hasta que el .md exista.

---

## PASO 3: Buscar Programa de Referencia Similar

**Que hacer:** Identificar un programa ya implementado para copiar patrones.

**Donde buscar:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Tabla "Referencia Rapida: Programas Implementados"
> - Buscar en `app/Services/Actividades*.php` y `app/Services/Indicadores*.php`

**Archivos del programa de referencia (PVE Biomecanico):**

| Componente | Archivo |
|-----------|---------|
| Service Part 1 | `app/Services/ActividadesPveBiomecanicoService.php` |
| Service Part 2 | `app/Services/IndicadoresPveBiomecanicoService.php` |
| Document Class | `app/Libraries/DocumentosSSTTypes/PveRiesgoBiomecanico.php` |
| Vista Part 1 | `app/Views/generador_ia/pve_riesgo_biomecanico.php` |
| Vista Part 2 | `app/Views/generador_ia/indicadores_pve_biomecanico.php` |
| SQL Script | `app/SQL/agregar_pve_riesgo_biomecanico.php` |

**Criterio de seleccion:** Elegir el programa que mas se parezca al nuevo en:
- Cantidad de actividades
- Tipo de indicadores
- Secciones del documento

---

## PASO 4: Crear Script BD y Ejecutarlo

**Que hacer:** Insertar la configuracion del documento (Parte 3) en las 3 tablas de configuracion.

**Archivo a crear:** `app/SQL/agregar_{tipo_documento}.php`

**Las 3 tablas a poblar:**

1. **`tbl_doc_tipo_configuracion`** — Registro principal con `flujo = 'programa_con_pta'`
2. **`tbl_doc_secciones_config`** — Secciones con prompts IA
3. **`tbl_doc_firmantes_config`** — Firmantes

> **DIFERENCIA con Tipo A:** El campo `flujo` debe ser `'programa_con_pta'` (no `'secciones_ia'`).
> Esto hace que el SweetAlert consulte PTA + Indicadores ademas del contexto.

**Ejecutar:** `php app/SQL/agregar_{tipo_documento}.php`
LOCAL primero. PRODUCCION solo si LOCAL OK.

**Documentacion de apoyo:**
> - `docs/.../02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md` → "Las Tres Tablas de Configuracion"
> - `docs/.../03_MODULO_3_PARTES/ZZ_96_PARTE4.md` → Paso 3 (plantilla completa del script SQL)
> - Copiar estructura de `app/SQL/agregar_pve_riesgo_biomecanico.php`

---

## PASO 5: Crear Clase PHP Parte 3 + Registrar en Factory

**Que hacer:** Crear la clase del documento formal y registrarla.

### 5A. Crear la clase

**Archivo a crear:** `app/Libraries/DocumentosSSTTypes/{ClasePHP}.php`

**Metodos obligatorios (mismos que Tipo A):**
- `getTipoDocumento()` → snake_case exacto de BD
- `getNombre()` → nombre legible
- `getDescripcion()` → descripcion corta
- `getEstandar()` → numeral Res. 0312
- `getSecciones()` → array de secciones (fallback)
- `getFirmantesRequeridos()` → array de firmantes (fallback)
- `getContenidoEstatico()` → contenido fallback

**Metodo ADICIONAL que Tipo A NO tiene:**
- `getContextoBase()` → **SOBRESCRIBIR** para consultar PTA + Indicadores de BD

**Constantes ADICIONALES:**
```php
protected const TIPO_SERVICIO = '{tipo_servicio}';  // Vincula con Parte 1
protected const CATEGORIA = '{categoria}';           // Vincula con Parte 2
```

### 5B. Registrar en Factory

**Archivo a modificar:** `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_95_PARTE3.md` → getContextoBase(), constantes de vinculacion
> - `docs/.../03_MODULO_3_PARTES/ZZ_96_PARTE4.md` → Plantilla completa de clase
> - Copiar estructura de `app/Libraries/DocumentosSSTTypes/PveRiesgoBiomecanico.php`

---

## PASO 6: Crear Service Parte 1 (Actividades)

**Que hacer:** Crear el servicio que genera y guarda actividades en `tbl_pta_cliente`.

**Archivo a crear:** `app/Services/Actividades{NombrePrograma}Service.php`

**Estructura:**
- Constante `ACTIVIDADES_{NOMBRE}` con 12 actividades (una por mes, con ciclo PHVA)
- Metodo `preview()` → retorna actividades sin guardar
- Metodo `generarActividades()` → inserta en BD con `tipo_servicio = '{valor}'`
- Metodo `getActividadesCliente()` → consulta existentes
- Metodo `getResumenActividades()` → totales y porcentajes

**Campo critico:** `tipo_servicio` en la insercion DEBE coincidir con la constante de la clase Parte 3.

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_80_PARTE1.md` → Instructivo completo de Parte 1
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Paso 1
> - Copiar estructura de `app/Services/ActividadesPveBiomecanicoService.php`

---

## PASO 7: Crear Service Parte 2 (Indicadores)

**Que hacer:** Crear el servicio que genera y guarda indicadores en `tbl_indicadores_sst`.

**Archivo a crear:** `app/Services/Indicadores{NombrePrograma}Service.php`

**Estructura:**
- Constante `INDICADORES_{NOMBRE}` con 6-8 indicadores (estructura, proceso, resultado)
- Metodo `preview()` → retorna indicadores sin guardar
- Metodo `generarIndicadores()` → inserta en BD con `categoria = '{valor}'`
- Metodo `getIndicadoresCliente()` → consulta existentes

**Campo critico:** `categoria` en la insercion DEBE coincidir con la constante de la clase Parte 3.

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_81_PARTE2.md` → Instructivo completo de Parte 2
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Paso 2
> - Copiar estructura de `app/Services/IndicadoresPveBiomecanicoService.php`

---

## PASO 8: Registrar Categoria de Indicadores + Configurar Fases

### 8A. Registrar categoria en IndicadorSSTModel

**Archivo a modificar:** `app/Models/IndicadorSSTModel.php`

Agregar al array `CATEGORIAS`.

### 8B. Configurar fases en FasesDocumentoService

**Archivo a modificar:** `app/Services/FasesDocumentoService.php`

Agregar al array `FASES_POR_CARPETA` con las 2 fases (actividades + indicadores) y dependencia.

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Pasos 5 y 6

---

## PASO 9: Crear Rutas

**Que hacer:** Agregar ~9 rutas al sistema.

**Archivo a modificar:** `app/Config/Routes.php`

### Rutas Generador-IA (Parte 1 + Parte 2): 7 rutas

```
GET  /generador-ia/{id}/{slug-kebab}                    → Vista Part 1
GET  /generador-ia/{id}/preview-actividades-{slug}       → JSON preview
POST /generador-ia/{id}/generar-actividades-{slug}       → POST guardar
GET  /generador-ia/{id}/resumen-{slug}                   → JSON resumen
GET  /generador-ia/{id}/indicadores-{slug}               → Vista Part 2
GET  /generador-ia/{id}/preview-indicadores-{slug}       → JSON preview
POST /generador-ia/{id}/generar-indicadores-{slug}       → POST guardar
```

### Rutas Documentos-SST (Parte 3): 2 rutas

```
GET  /documentos-sst/{id}/{slug-kebab}/{anio}            → Vista documento
POST /documentos-sst/adjuntar-soporte-{slug}             → Adjuntar soporte
```

> **Regla URLs:** Generador-IA y vista web usan kebab-case. Generacion IA usa snake_case.

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Paso 7

---

## PASO 10: Crear Metodos en Controllers

### 10A. GeneradorIAController — 7 metodos

**Archivo a modificar:** `app/Controllers/GeneradorIAController.php`

```
1. {metodoVista}($idCliente)               → Carga vista Part 1
2. previewActividades{Nombre}($idCliente)   → JSON preview actividades
3. generarActividades{Nombre}($idCliente)   → POST guardar actividades
4. resumen{Nombre}($idCliente)              → JSON resumen
5. indicadores{Nombre}($idCliente)          → Carga vista Part 2
6. previewIndicadores{Nombre}($idCliente)   → JSON preview indicadores
7. generarIndicadores{Nombre}($idCliente)   → POST guardar indicadores
```

### 10B. DocumentosSSTController — 2 metodos

**Archivo a modificar:** `app/Controllers/DocumentosSSTController.php`

```
1. {metodoVista}($idCliente, $anio)  → Vista documento Part 3
2. adjuntarSoporte{Nombre}()         → Adjuntar soporte
```

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Pasos 8 y 9
> - Copiar patrones de metodos existentes de PVE Biomecanico

---

## PASO 11: Crear Vistas Generador-IA

**Que hacer:** Crear 2 vistas PHP para el generador.

**Archivos a crear:**
1. `app/Views/generador_ia/{nombre_programa}.php` — Vista Part 1 (Actividades)
2. `app/Views/generador_ia/indicadores_{nombre_programa}.php` — Vista Part 2 (Indicadores)

**Requisitos UX obligatorios:**
- Modal con cards editables (NO redirigir)
- Checkbox + "Seleccionar Todos"
- Edicion inline en cada card
- Panel "Mejorar con IA" por item
- Seccion Parte 3 con boton "Ir a Generar Documento" (habilitado solo si Part 1 y 2 completas)

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_77_PREPARACION.md` → Requisitos UX obligatorios
> - `docs/.../03_MODULO_3_PARTES/ZZ_80_PARTE1.md` → Estructura de vista Part 1
> - `docs/.../03_MODULO_3_PARTES/ZZ_81_PARTE2.md` → Estructura de vista Part 2
> - `docs/.../03_MODULO_3_PARTES/ZZ_97_PARTE5.md` → Patrones UX/UI reutilizables
> - Copiar de `app/Views/generador_ia/pve_riesgo_biomecanico.php`

---

## PASO 12: Integrar en Carpeta de Documentacion

**Que hacer:** Que el programa aparezca en la carpeta del cliente.

### 12A. Vista _tipos/ (si aplica)

Si el programa va dentro de una vista multi-programa (como procedimientos_seguridad.php para 4.2.3):
- Agregar al `$catalogoProgramas` con `'implementado' => true`
- Agregar al `$programasImplementados`

Si tiene su propia carpeta:
- Crear o modificar `app/Views/documentacion/_tipos/{nombre_vista}.php`

### 12B. DocumentacionController — 3 cambios

1. `determinarTipoCarpetaFases()` → detectar carpeta
2. `carpeta()` array de tipos → agregar al in_array
3. `carpeta()` filtro tipo_documento → agregar elseif

### 12C. acciones_documento.php — 2 cambios

1. `$mapaRutas` → agregar entrada
2. `$urlEditar` → agregar elseif

### 12D. tabla_documentos_sst.php — 1 cambio

Agregar al array `$tiposConTabla`.

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md` → Pasos 11 y 12
> - `docs/.../10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md` → Pasos 1-4

---

## PASO 13: Configurar SweetAlert de Verificacion

**Que hacer:** Registrar los filtros para que el SweetAlert muestre datos correctos.

**Archivo a modificar:** `app/Controllers/DocumentosSSTController.php`

2 cambios:
1. `getFiltroServicioPTA()` → agregar entrada con filtros de `tipo_servicio`
2. `getCategoriaIndicador()` → agregar entrada con `categoria`

> Sin esto, el SweetAlert NO mostrara las actividades ni indicadores del programa.

**Documentacion de apoyo:**
> - `docs/.../03_MODULO_3_PARTES/ZZ_90_PARTESWEETALERT.md` → Mapeo de filtros

---

## PASO 14: Verificar que Todo Funciona

### Checklist de verificacion

**Parte 1 — Actividades:**
- [ ] Vista `/generador-ia/{id}/{slug}` carga sin errores
- [ ] Preview muestra tabla con 12 actividades
- [ ] "Generar Actividades" inserta en `tbl_pta_cliente` con `tipo_servicio` correcto
- [ ] Resumen muestra totales y porcentajes PHVA

**Parte 2 — Indicadores:**
- [ ] Vista `/generador-ia/{id}/indicadores-{slug}` carga sin errores
- [ ] Prerequisito: muestra alerta si Part 1 no esta completa
- [ ] Preview muestra 6-8 indicadores
- [ ] "Generar Indicadores" inserta en `tbl_indicadores_sst` con `categoria` correcta

**Parte 3 — Documento:**
- [ ] `/documentos/generar/{tipo_documento}/{id}` carga sin errores
- [ ] SweetAlert muestra actividades + indicadores + contexto
- [ ] IA genera contenido que REFERENCIA actividades e indicadores reales
- [ ] PDF y Word generan correctamente

**Carpeta:**
- [ ] El programa aparece en la carpeta del numeral
- [ ] Fases muestran progreso (Part 1 completa → Part 2 habilitada → Part 3 habilitada)
- [ ] Botones de acciones funcionan

**Documentacion de apoyo para diagnosticar problemas:**
> - `memory/troubleshooting-ia.md` → Problemas comunes
> - `docs/.../02_GENERACION_IA/1_A_TROUBLESHOOTING_GENERACION_IA.md` → Diagnostico detallado
> - `docs/.../03_MODULO_3_PARTES/ZZ_90_PARTESWEETALERT.md` → Si SweetAlert no muestra datos
> - `docs/.../03_MODULO_3_PARTES/ZZ_91_MENSAJESTOAST.md` → Si toasts no aparecen

---

## Resumen: Archivos por Paso

| Paso | Archivos | Accion |
|------|----------|--------|
| 2 | `docs/.../10_DOCUMENTOS_ESPECIFICOS/{Clase}.md` | CREAR |
| 4 | `app/SQL/agregar_{tipo}.php` | CREAR + EJECUTAR |
| 5A | `app/Libraries/DocumentosSSTTypes/{Clase}.php` | CREAR |
| 5B | `DocumentoSSTFactory.php` | MODIFICAR |
| 6 | `app/Services/Actividades{Nombre}Service.php` | CREAR |
| 7 | `app/Services/Indicadores{Nombre}Service.php` | CREAR |
| 8A | `app/Models/IndicadorSSTModel.php` | MODIFICAR |
| 8B | `app/Services/FasesDocumentoService.php` | MODIFICAR |
| 9 | `app/Config/Routes.php` | MODIFICAR (~9 rutas) |
| 10A | `app/Controllers/GeneradorIAController.php` | MODIFICAR (7 metodos) |
| 10B | `app/Controllers/DocumentosSSTController.php` | MODIFICAR (2 metodos) |
| 11 | `app/Views/generador_ia/*.php` | CREAR (2 vistas) |
| 12A | `app/Views/documentacion/_tipos/*.php` | CREAR o MODIFICAR |
| 12B | `app/Controllers/DocumentacionController.php` | MODIFICAR (3 cambios) |
| 12C | `acciones_documento.php` | MODIFICAR (2 cambios) |
| 12D | `tabla_documentos_sst.php` | MODIFICAR (1 cambio) |
| 13 | `DocumentosSSTController.php` | MODIFICAR (2 mapeos) |

**Total: 5 archivos nuevos + 9 archivos modificados = 14 cambios**

---

## Diferencias Clave con Tipo A

| Aspecto | Tipo A | Tipo B |
|---------|--------|--------|
| Flujo BD | `secciones_ia` | `programa_con_pta` |
| Partes | 1 (solo documento) | 3 (actividades + indicadores + documento) |
| getContextoBase() | Hereda de AbstractDocumentoSST | **SOBRESCRIBE** para consultar PTA + Indicadores |
| Services | Ninguno | 2 (Actividades + Indicadores) |
| Vistas generador_ia | Ninguna | 2 (actividades + indicadores) |
| Metodos controller adicionales | 0 | 7 (GeneradorIAController) |
| Rutas adicionales | 0 | ~7 (generador-ia) |
| FasesDocumentoService | No aplica | Configurar 2 fases con dependencia |
| SweetAlert | Solo contexto | Contexto + PTA + Indicadores |
| Archivos totales | ~8 | ~14 |

---

*Guia creada: 2026-03-01*
*Validada con implementacion de: programa_inspecciones (4.2.4)*
*Basada en: ZZ_98_COMO_AGREGAR_PROGRAMA.md + GUIA_PASO_A_PASO_DOCUMENTO_TIPO_A.md*
