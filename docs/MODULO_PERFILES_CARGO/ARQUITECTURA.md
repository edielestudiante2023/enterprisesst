# Módulo: Perfiles de Cargo

## 1. Objetivo

Permitir que cada cliente del aplicativo genere y gestione **Perfiles de Cargo** formales, con generación asistida por IA (objetivo del cargo, indicadores), competencias del cliente alimentadas desde el módulo Diccionario de Competencias, y un sistema de firmas dual:

- **Aprobador del perfil**: 1 firma única por versión vigente (jefe / talento humano / responsable).
- **Recibido del trabajador**: N firmas individuales — una por cada trabajador asignado al cargo.

Documento fuente de referencia: `docs/Perfil Analista Contable.docx` (modelo de cliente).

## 2. Alcance

- Cada cliente mantiene sus propios perfiles, uno por cargo (`tbl_cargos_cliente`).
- Las **funciones SST** y **funciones Talento Humano** son **transversales por cliente** (mismas para todos los cargos del cliente, pero cada cliente puede personalizar sus ítems). Texto del docx semilla las tablas iniciales.
- Las **competencias** se seleccionan desde el catálogo del cliente (`tbl_competencia_cliente`) vía Select2, con opción de precarga desde la matriz `tbl_cliente_competencia_cargo` cuando exista.
- Reutiliza:
  - `tbl_cargos_cliente` (módulo IPEVR GTC45).
  - `tbl_competencia_cliente` y `tbl_competencia_escala_cliente` (módulo Diccionario de Competencias).
  - `DocumentoVersionService` (versionamiento estándar del aplicativo).
  - `tbl_doc_firma_solicitudes` / `tbl_doc_firma_evidencias` para el aprobador.
- Acceso: dashboard del cliente → **"Perfiles de Cargo"** → listado por cargo. Categoría `talento_humano`. Sin numeral SGSST obligatorio.

## 3. Decisión arquitectónica clave: dos entidades

A diferencia del resto de documentos SST (1 documento por cliente), el Perfil de Cargo opera en dos niveles:

| Entidad | Cardinalidad | Versionable | Firmas |
|---|---|---|---|
| **Plantilla del Perfil del Cargo** | 1 por cargo del cliente | Sí (`DocumentoVersionService`) | Aprobador (1) |
| **Acuse de Recibido (entrega)** | 1 por trabajador asignado al cargo | No, ligado a versión vigente | Trabajador (N) |

Si un perfil aplica a 50 trabajadores → **1 plantilla** + **50 acuses**, cada uno con firma individual.

Cuando se aprueba una **nueva versión**, los acuses anteriores quedan asociados a la versión obsoleta y se genera un **nuevo lote de acuses** vinculados a la versión vigente (los acuses previos se conservan para auditoría).

## 4. Modelo de datos

### 4.1 `tbl_perfil_cargo`
Plantilla del perfil — una fila por cargo del cliente.

| columna | tipo | descripción |
|---|---|---|
| id_perfil_cargo | INT PK AUTO_INCREMENT | |
| id_cliente | INT FK → tbl_clientes | NOT NULL, ON DELETE CASCADE |
| id_cargo_cliente | INT FK → tbl_cargos_cliente | UNIQUE, ON DELETE CASCADE |
| objetivo_cargo | TEXT | generado por IA, editable |
| reporta_a | VARCHAR(150) | |
| colaboradores_a_cargo | VARCHAR(150) | |
| condiciones_laborales | JSON | { oficina, home_office, alternancia, in_house, otro } |
| edad_min | VARCHAR(20) | texto libre tipo ">25" |
| estado_civil | ENUM('soltero','casado','indiferente') | |
| genero | ENUM('masculino','femenino','indiferente') | |
| factores_riesgo | JSON | matriz checkboxes (físico, químico, biológico, psico, mecánico, eléctrico, locativo) |
| formacion_educacion | JSON | filas: educación, título, en_curso |
| conocimiento_complementario | JSON | filas: conocimiento, descripción, duración |
| experiencia_laboral | JSON | filas: cargo/actividad, tiempo |
| validacion_educacion_experiencia | TEXT | reemplazos permitidos |
| funciones_especificas | JSON | lista ordenada (IA puede sugerir) |
| aprobador_nombre | VARCHAR(150) | snapshot del aprobador |
| aprobador_cargo | VARCHAR(150) | snapshot del cargo del aprobador |
| aprobador_cedula | VARCHAR(30) | |
| fecha_aprobacion | DATE | |
| version_actual | INT DEFAULT 1 | |
| estado | ENUM('borrador','generado','aprobado','firmado','obsoleto') | |
| id_documento_sst | INT FK → tbl_documentos_sst | enlace al motor de versionamiento |
| created_at / updated_at | TIMESTAMP | |

Índices: `idx_cliente`, `idx_cargo`, `idx_estado`.

### 4.2 `tbl_perfil_cargo_competencia`
Pivot competencias requeridas por el cargo.

| columna | tipo |
|---|---|
| id | INT PK |
| id_perfil_cargo | INT FK → tbl_perfil_cargo (CASCADE) |
| id_competencia | INT FK → tbl_competencia_cliente |
| nivel_requerido | TINYINT (1..5) — corresponde a `tbl_competencia_escala_cliente.nivel` |
| observacion | TEXT NULL |
| orden | INT |

Índice único: `(id_perfil_cargo, id_competencia)`.

**UI**: Select2 con AJAX a `/perfilesCargo/competencias/{id_cliente}` que devuelve las competencias activas. Si el cliente ya tiene asignaciones en `tbl_cliente_competencia_cargo` para ese `id_cargo_cliente`, **precargar** automáticamente.

### 4.3 `tbl_perfil_cargo_indicador`
Indicadores asociados al cargo (generados por IA a partir de las funciones).

| columna | tipo |
|---|---|
| id | INT PK |
| id_perfil_cargo | INT FK (CASCADE) |
| objetivo_proceso | TEXT |
| nombre_indicador | VARCHAR(200) |
| formula | TEXT |
| periodicidad | ENUM('mensual','bimestral','trimestral','semestral','anual') |
| meta | VARCHAR(200) |
| ponderacion | VARCHAR(20) |
| objetivo_calidad_impacta | VARCHAR(200) |
| generado_ia | TINYINT(1) |
| orden | INT |

### 4.4 `tbl_perfil_cargo_funcion_sst_cliente`
**Funciones SST transversales por cliente** — mismas para todos los cargos del cliente, editables.

| columna | tipo |
|---|---|
| id | INT PK |
| id_cliente | INT FK (CASCADE) |
| orden | INT |
| texto | TEXT |
| activo | TINYINT(1) DEFAULT 1 |

Seed: las **26 frases SST** del docx pueblan esta tabla para cada cliente.

### 4.5 `tbl_perfil_cargo_funcion_th_cliente`
**Funciones Talento Humano transversales por cliente.**

Misma estructura que 4.4. Seed: las **7 frases TH** del docx.

### 4.6 `tbl_trabajadores` (NUEVA — censo central del cliente)

Tabla creada como parte de este módulo. Censo general de trabajadores de cada cliente, reutilizable por futuros módulos.

| columna | tipo | descripción |
|---|---|---|
| id_trabajador | INT PK AUTO_INCREMENT | |
| id_cliente | INT FK → tbl_clientes | NOT NULL, ON DELETE CASCADE |
| id_cargo_cliente | INT FK → tbl_cargos_cliente | NULL permitido (puede no tener cargo asignado todavía) |
| nombres | VARCHAR(150) NOT NULL | |
| apellidos | VARCHAR(150) NOT NULL | |
| tipo_documento | ENUM('CC','CE','PA','TI','PEP') | |
| cedula | VARCHAR(30) NOT NULL | |
| email | VARCHAR(150) | NULL permitido (algunos trabajadores sin email) |
| telefono | VARCHAR(30) | |
| fecha_ingreso | DATE | |
| fecha_retiro | DATE NULL | |
| activo | TINYINT(1) DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

Índices:
- Único: `(id_cliente, cedula)`
- `idx_cliente`, `idx_cargo`, `idx_activo`

**Nota**: por ahora la tabla se crea **vacía**. Se poblará por dos vías cuando se empiece a usar el módulo:
1. Importación masiva Excel/CSV desde la pantalla "Asignar trabajadores" (§8.3)
2. Creación manual fila a fila

Otras tablas existentes (`tbl_acta_visita_integrantes`, `tbl_participantes_comite`, `tbl_voluntarios_brigada`, etc.) podrían eventualmente migrar a referenciarla con FK, pero eso queda **fuera de alcance** de este módulo.

### 4.7 `tbl_perfil_cargo_acuse` ⭐ FIRMAS MULTI-TRABAJADOR

Una fila por trabajador-perfil-versión. Esta es la pieza clave del módulo.

| columna | tipo | descripción |
|---|---|---|
| id_acuse | INT PK | |
| id_perfil_cargo | INT FK (CASCADE) | |
| id_version | INT FK → tbl_doc_versiones_sst | versión vigente al momento de generar el acuse |
| id_trabajador | INT FK → tbl_trabajadores | ON DELETE RESTRICT |
| nombre_trabajador | VARCHAR(150) | snapshot al generar |
| cedula_trabajador | VARCHAR(30) | snapshot |
| cargo_trabajador | VARCHAR(150) | snapshot |
| email_trabajador | VARCHAR(150) | snapshot — destino del envío |
| estado | ENUM('pendiente','enviado','firmado','rechazado') | |
| token_firma | VARCHAR(64) UNIQUE | link público |
| fecha_envio | DATETIME | |
| fecha_firma | DATETIME | |
| firma_imagen | LONGTEXT | base64 (canvas/upload) |
| ip_firma | VARCHAR(45) | auditoría |
| user_agent | VARCHAR(255) | auditoría |
| pdf_acuse | VARCHAR(255) | path al PDF firmado individual |
| created_at / updated_at | TIMESTAMP | |

Índices:
- Único: `(id_perfil_cargo, id_version, id_trabajador)`
- `idx_token`, `idx_estado`

## 5. Aprobador del perfil (firma única)

Se reutiliza el sistema de firmas existente registrando una solicitud con `firmante_tipo='aprobador_perfil'` en `tbl_doc_firma_solicitudes` vinculada al `id_documento_sst` del perfil. Esto da gratis:

- Email vía SendGrid existente
- Captura de evidencia en `tbl_doc_firma_evidencias` (IP, user_agent, base64)
- Estados estándar (pendiente/firmado)
- Hash del documento al momento de la firma

Los snapshots `aprobador_nombre`/`aprobador_cargo`/`aprobador_cedula` se mantienen en `tbl_perfil_cargo` para impresión rápida sin joins.

## 6. Generación con IA (3 puntos)

| Botón | Input | Output |
|---|---|---|
| **Generar objetivo del cargo** | nombre_cargo + funciones_especificas | `objetivo_cargo` (1 párrafo) |
| **Generar indicadores** | funciones_especificas + objetivo_cargo | 3-5 filas en `tbl_perfil_cargo_indicador` |
| **Sugerir funciones específicas** *(opcional)* | nombre_cargo + área + objetivo (si existe) | lista de 8-12 funciones |

Cada uno es un endpoint puntual (no flujo `secciones_ia` completo) porque el documento no es plano: tiene tablas, JSON y pivots.

Reutiliza el wrapper de OpenAI ya usado en otros módulos:
- Modelo `gpt-4o-mini`
- System prompt compacto
- Inyectar fecha actual
- Regla: NO mostrar SQL ni razonamiento interno

Cada generación puebla campos editables — el usuario puede ajustar antes de aprobar.

## 7. Registro en motor de documentos (versionamiento)

Aunque la UI es custom, el perfil se **registra como documento Tipo A** para reutilizar versionamiento, snapshot y generación PDF/Word.

### 7.1 `tbl_doc_tipo_configuracion`
```
tipo_documento     = 'perfil_cargo'
nombre             = 'Perfil del Cargo'
flujo              = 'perfil_cargo'   (flujo nuevo, no usa secciones_ia plano)
categoria          = 'talento_humano'
estandar           = NULL
```

### 7.2 Snapshot
El `contenido_snapshot` de cada versión guarda el JSON completo del perfil incluyendo todas las tablas hijas serializadas (competencias, indicadores, funciones SST/TH, factores de riesgo). Permite restaurar versiones anteriores y descargar PDFs históricos.

### 7.3 `tbl_doc_firmantes_config`
| orden | firmante_tipo | rol_display |
|---|---|---|
| 1 | aprobador_perfil | Aprobador del Perfil |

(Las firmas de trabajadores NO van aquí — viven en `tbl_perfil_cargo_acuse`.)

## 8. UI / Pantallas

1. **Listado de perfiles** — `/perfilesCargo/{id_cliente}`
   - Tabla: cargo, área, versión, estado, # trabajadores asignados, # firmados, acciones.
   - Botón "Crear perfil" (selector de cargo desde `tbl_cargos_cliente`).

2. **Editor del perfil** — `/perfilesCargo/{id_cliente}/{id_cargo}`
   - Tabs:
     - **Identificación** — área, cargo, reporta a, colaboradores, condiciones laborales
     - **Requisitos básicos** — edad, estado civil, género
     - **Riesgos** — matriz checkboxes de factores de riesgo
     - **Formación** — educación + conocimiento complementario
     - **Experiencia**
     - **Competencias** — Select2 con AJAX al diccionario del cliente + nivel mínimo
     - **Funciones** — específicas del cargo (con botón IA) + tabla SST transversal (read-only del cliente con link a edición global) + tabla TH transversal
     - **Indicadores** — con botón "Generar con IA"
     - **Aprobación** — datos del aprobador + botón "Enviar a firma"
   - Modal historial de versiones, modal nueva versión (componentes reutilizables).

3. **Asignar trabajadores** — `/perfilesCargo/{id_perfil}/trabajadores`
   - Multi-select de trabajadores del cliente (filtrados por `id_cargo_cliente` o todos)
   - **Importador Excel/CSV** ⭐ — botón "Importar trabajadores" que sube un archivo con columnas (`tipo_documento, cedula, nombres, apellidos, email, telefono, fecha_ingreso`), valida (cédula única por cliente, email opcional), inserta en `tbl_trabajadores` y los autoasigna al cargo del perfil. Reporta filas insertadas / actualizadas / con error.
   - CRUD manual fila a fila (alta rápida sin importar)
   - Botón "Generar acuses para versión vigente" → crea N filas en `tbl_perfil_cargo_acuse`

4. **Panel de firmas** — `/perfilesCargo/{id_perfil}/firmas`
   - Tabla con estado por trabajador (pendiente / enviado / firmado / rechazado)
   - Acciones por fila: enviar email, copiar link, descargar PDF firmado
   - Bulk: "Enviar a todos los pendientes"

5. **Vista pública del trabajador** — `/perfil-acuse/{token}`
   - Renderiza el perfil completo (versión vinculada al acuse)
   - Captura de firma (canvas + opción upload)
   - Guarda evidencia en `tbl_perfil_cargo_acuse`
   - Genera PDF individual del acuse

6. **Edición global de funciones SST/TH del cliente** — `/perfilesCargo/{id_cliente}/funciones-transversales`
   - CRUD de los ítems en `tbl_perfil_cargo_funcion_sst_cliente` y `tbl_perfil_cargo_funcion_th_cliente`.
   - Cambios aquí impactan todos los perfiles del cliente al regenerar PDF (no fuerza nueva versión salvo que el usuario lo decida).

## 9. Flujo end-to-end

```
Crear cargo (módulo IPEVR)
   ↓
Crear perfil del cargo
   ↓
IA: generar objetivo del cargo (a partir de funciones)
   ↓
IA: generar indicadores (a partir de funciones + objetivo)
   ↓
Seleccionar competencias requeridas (Select2 → diccionario cliente)
   ↓
Aprobar perfil → solicitud firma aprobador → email → firmado
   ↓
Versión 1.0 creada (snapshot completo)
   ↓
Asignar 50 trabajadores → genera 50 acuses (estado=pendiente)
   ↓
Envío masivo email → cada trabajador firma vía link público
   ↓
PDF individual generado por cada acuse firmado
   ↓
[Cambio en funciones del cargo]
   ↓
Iniciar nueva versión (1.1 o 2.0) → editar → aprobar
   ↓
Regenerar lote de acuses para los trabajadores asignados (acuses anteriores quedan ligados a versión obsoleta para auditoría)
```

## 10. Plan de seed

Script `scripts/perfil_cargo_seed_transversal.php` (PHP CLI, idempotente):

1. Resolver clientes activos en `tbl_clientes`.
2. Para cada cliente sin filas en `tbl_perfil_cargo_funcion_sst_cliente`: insertar las **26 frases SST** del docx.
3. Para cada cliente sin filas en `tbl_perfil_cargo_funcion_th_cliente`: insertar las **7 frases TH** del docx.
4. Orden obligatorio: LOCAL primero → verificar → PRODUCCIÓN.

Las 26 frases SST y 7 frases TH se guardan en `scripts/data/perfil_cargo_funciones_seed.txt` para auditoría del seed.

## 11. Decisiones abiertas

### 11.1 Tabla de trabajadores del cliente — CERRADA ✅
Se crea **`tbl_trabajadores`** como tabla nueva del módulo (ver §4.6) — censo central del cliente, reutilizable por futuros módulos. Confirmado que NO existía previamente en el proyecto (solo había tablas de propósito específico: `tbl_acta_visita_integrantes`, `tbl_participantes_comite`, `tbl_voluntarios_brigada`).

Carga inicial: por ahora la tabla queda **vacía**. Se poblará vía:
1. Importador Excel/CSV en pantalla "Asignar trabajadores" (§8.3)
2. CRUD manual

Migración de las tablas existentes a referenciar `tbl_trabajadores` con FK queda **fuera de alcance**.

### 11.2 Email masivo
Recomendación: usar el wrapper SendGrid existente con cola de envío secuencial (no paralelo) para 50+ destinatarios, registrando cada envío en `tbl_perfil_cargo_acuse.fecha_envio`.

### 11.3 Aprobador del perfil
El docx deja "Nombre y cargo de quien aprueba el perfil" como **texto libre**. Recomendación: permitir entrada libre + opcional vincular a usuario del cliente para autenticación de firma.

### 11.4 Factores de riesgo del cargo
Recomendación v1: 100% manuales (checkboxes). En v2 se podría enlazar a la matriz IPEVR existente del cargo.

### 11.5 Política de versionamiento al cambiar funciones SST/TH transversales
Recomendación: cambios en las funciones transversales **no** disparan nueva versión automática de cada perfil — el PDF las renderiza al vuelo desde la tabla del cliente. Si el usuario quiere "congelarlas" en una versión, debe iniciar nueva versión manualmente.

### 11.6 Acuses al cambiar versión
**Mantener histórico + nuevo lote** para auditoría: los acuses firmados de versiones obsoletas no se borran ni invalidan; se crean nuevos para los trabajadores actualmente asignados al cargo, ligados a la nueva versión vigente.

## 12. Checklist de implementación

Orden documentación-primero (este archivo ya cumple paso 0).

- [x] **BD**: `scripts/perfil_cargo_schema.php` — crea las 7 tablas (`CREATE TABLE IF NOT EXISTS`), incluyendo **`tbl_trabajadores`** (§4.6). **Ejecutado LOCAL + PROD ✅**
- [x] **BD**: `scripts/perfil_cargo_config.php` — ALTER ENUMs (`flujo += perfil_cargo`, `firmante_tipo += aprobador_perfil`) + registro en `tbl_doc_tipo_configuracion` + firmante `aprobador_perfil` en `tbl_doc_firmantes_config`. **Ejecutado LOCAL + PROD ✅**
- [x] **BD**: `scripts/perfil_cargo_seed_transversal.php` — funciones SST y TH semilla por cliente. **Ejecutado LOCAL (9 clientes, 225 SST + 63 TH) + PROD (11 clientes, 275 SST + 77 TH) ✅**
- [x] **Modelos**: 7/7 creados
  - [x] `TrabajadorModel` ✅
  - [x] `PerfilCargoModel` ✅ (con decode JSON automático)
  - [x] `PerfilCargoCompetenciaModel` ✅ (con `precargarDesdeMatriz()` que integra Diccionario de Competencias)
  - [x] `PerfilCargoIndicadorModel` ✅
  - [x] `PerfilCargoFuncionSSTClienteModel` ✅
  - [x] `PerfilCargoFuncionTHClienteModel` ✅
  - [x] `PerfilCargoAcuseModel` ✅ (con `generarLote()`, `marcarFirmado()`, `contarPorPerfil()`)
- [x] **Servicios IA** (3 endpoints): `PerfilCargoIAService` + `PerfilCargoController`. Probados contra OpenAI con `gpt-4o-mini` y `response_format=json_object`. **URL real:** `perfiles-cargo/ia/*` (kebab-case) ✅
  - [x] `POST /perfiles-cargo/ia/objetivo` ✅
  - [x] `POST /perfiles-cargo/ia/indicadores` ✅
  - [x] `POST /perfiles-cargo/ia/funciones` ✅
- [x] **Controlador**: `PerfilCargoController` con metodos `index`, `crear`, `editor`, `guardar`, `iaObjetivo`, `iaIndicadores`, `iaFunciones` ✅ (trabajadores/firmas pendientes)
- [x] **Vistas** (bloque base):
  - [x] Listado (`perfiles_cargo/index.php`) — tabla de cargos con estado del perfil, # trabajadores, botón Crear/Editar ✅
  - [x] Editor (`perfiles_cargo/editor.php`) — 6 tabs: Identificacion, Requisitos, Funciones, Indicadores, Competencias (Select2), Aprobacion (con canvas firma). Integrado con 3 botones IA + guardado AJAX + firma del aprobador ✅
  - [x] Trabajadores (`perfiles_cargo/trabajadores.php`) — tabla, CRUD modal, importador CSV con detección de separador automático ✅
  - [x] Acuses/panel de firmas (`perfiles_cargo/acuses.php`) — stats, selector de trabajadores, generación de lote, copiar link, descarga PDF individual ✅
  - [x] Vista pública trabajador (`perfiles_cargo/acuse_publico.php`) — muestra el perfil completo + canvas de firma, sin sesion ✅
  - [x] Vista de error (`perfiles_cargo/acuse_error.php`) ✅
  - [x] PDF del perfil (`perfiles_cargo/pdf_perfil.php`) — Dompdf, 9 secciones, firma aprobador embebida ✅
  - [x] PDF del acuse individual (`perfiles_cargo/pdf_acuse.php`) — Dompdf, con firma del trabajador + auditoria (IP, fecha) ✅
  - [ ] Edición funciones transversales SST/TH (CRUD) — pendiente, por ahora se leen desde seed
- [ ] **Integración firmas**:
  - [ ] Aprobador → reutiliza `tbl_doc_firma_solicitudes`
  - [ ] Trabajadores → flujo nuevo en `tbl_perfil_cargo_acuse`
- [ ] **Versionamiento**: integración con `DocumentoVersionService` (snapshot del JSON completo del perfil + hijos)
- [ ] **Generadores**:
  - [ ] PDF perfil completo (con firma aprobador)
  - [ ] PDF acuse individual por trabajador (con firma trabajador)
  - [ ] Word del perfil completo
- [ ] **Dashboard cliente**: agregar enlace "Perfiles de Cargo"
- [ ] **Verificación**: cliente piloto → crear cargo → generar perfil → IA objetivo → IA indicadores → seleccionar competencias → aprobar → asignar 3 trabajadores test → enviar firmas → firmar todos → descargar PDFs
- [ ] **Producción**: ejecutar scripts BD en orden (schema → config → seed)

## 13. Referencias

- `docs/Perfil Analista Contable.docx` — modelo de cliente (fuente de la estructura)
- `docs/MODULO_DICCIONARIO_COMPETENCIAS/ARQUITECTURA.md` — origen de competencias y escala
- `docs/MODULO_NUMERALES_SGSST/05_VERSIONAMIENTO/ZZ_98_HISTORIAL_VERSIONES.md` — sistema de versionamiento
- `docs/MODULO_NUMERALES_SGSST/1_A_SISTEMA_FIRMAS_DOCUMENTOS.md` — sistema de firmas
- `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md` — reglas IA

## 14. Riesgos conocidos y deuda técnica

Estado al cierre del sprint de entrega completa. Organizado por fase para facilitar retomas futuras.

### Fase A — Desbloquear testeo

| # | Item | Severidad | Estado |
|---|---|---|---|
| 5 | CSRF token en POST AJAX podria bloquear requests | 🔴 Alta (probable) | ✅ **FALSO POSITIVO** — el filtro `csrf` esta registrado como alias en `app/Config/Filters.php` pero NO se aplica ni globalmente (`$globals`) ni por ruta (`$filters`). `Security::$csrfProtection='cookie'` solo define como se almacenaria el token SI el filtro corriera, pero nunca se invoca. Smoke tests empiricos confirman que los endpoints AJAX responden con JSON normal sin 403. El `csrf_field()` en el form de importacion de trabajadores es redundante pero inocuo. No hay que tocar nada. |

### Fase B — Probar flujo end-to-end

| # | Item | Severidad | Estado |
|---|---|---|---|
| 1 | No probado en navegador — posibles bugs de UI/encoding | 🔴 Alta | Pendiente |

### Fase C — Deuda técnica visible

| # | Item | Severidad | Estado |
|---|---|---|---|
| 2 | Firma del aprobador se guarda en `condiciones_laborales._firma_aprobador_path` (JSON). Solucion v1 "sucia" — deberia migrarse a campo dedicado en `tbl_perfil_cargo` | 🟡 Media | Pendiente. Plan: ALTER TABLE add `firma_aprobador_path VARCHAR(255)`, migrar datos existentes del JSON al nuevo campo, actualizar `aprobadorFirmar()` y `pdf_perfil.php`. |
| 4 | Versionamiento con `DocumentoVersionService` no enganchado. El motor estandar del proyecto no se usa — se incrementa `version_actual` como entero manual | 🟡 Media | Pendiente. Plan: al aprobar, llamar `DocumentoVersionService::aprobarVersion()` con snapshot JSON del perfil + hijos. Al editar despues de aprobado, forzar `iniciarNuevaVersion()`. |

### Fase D — UX pequenos

| # | Item | Severidad | Estado |
|---|---|---|---|
| 10 | `trabajadorEliminar()` usa DELETE duro, cae a desactivar si hay FK RESTRICT — UX poco clara | 🟢 Baja | Pendiente. Plan: revisar primero si tiene acuses y ofrecer SweetAlert con dos opciones (desactivar / cancelar). |
| 8 | Link al modulo en dashboard consultor | 🟢 Baja | Pendiente. Requiere identificar donde vive el listado de clientes con accesos rapidos. |
| 7 | CRUD de funciones SST/TH transversales del cliente — solo seed, no UI | 🟢 Baja | Pendiente. Plan: vista `/perfiles-cargo/{id_cliente}/funciones-transversales` con dos tablas editables. |

### Fase E — Features aplazados

| # | Item | Severidad | Estado |
|---|---|---|---|
| 9 | PDF del perfil no incluye `formacion_educacion`, `conocimiento_complementario`, `experiencia_laboral` (campos JSON en BD sin UI ni render) | 🟢 Baja | Pendiente. Plan: UI estructurada en tab Requisitos + render en `pdf_perfil.php`. |
| 6 | Tab "Factores de riesgo" (matriz checkboxes del docx) sin UI — campo JSON en BD | 🟢 Baja | Pendiente. Plan: matriz visual de 5 categorias x ~30 items con checkboxes. |
| 3 | No hay envio masivo de acuses por email — solo links copiables | 🟡 Media | Pendiente. Plan: reutilizar wrapper SendGrid, cola secuencial, actualizar `fecha_envio` y estado `enviado`. |

## 15. Notas importantes del proyecto

- **CSRF esta desactivado** a nivel de framework en este proyecto. Cualquier POST AJAX nuevo funciona sin necesidad de incluir el token. Si en el futuro se activa CSRF globalmente, habra que auditar todos los fetch() del aplicativo, no solo los de este modulo.
