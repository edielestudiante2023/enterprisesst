# HARDENING DE REPOSITORIO — enterprisesst

**Fecha:** 2026-04-05
**Aplicativo:** enterprisesst — Sistema de Gestion SG-SST para Empresas
**Empresa:** Cycloid Talent
**Preparado para:** Edwin Lopez (consultor de infraestructura)

---

## TABLA DE CONTENIDO

1. Descripcion del aplicativo
2. Mapa de base de datos
3. Inventario de API Keys y servicios externos
4. Documentacion del proyecto (README, CONTRIBUTING, .env.example)
5. Ramas de trabajo
6. Pipelines CI/CD (Gitea)
7. Organizacion del repositorio
8. Hallazgos criticos y acciones pendientes

---

## 1. DESCRIPCION DEL APLICATIVO

### Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.2 + CodeIgniter 4.6 |
| Base de datos | MySQL 8.0.45 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx (Ubuntu 24.04) — aaPanel (66.29.154.174) |
| Email | SendGrid API v3 |
| PDF | TCPDF 6.10 + DOMPDF 3.1 |
| Excel | PhpSpreadsheet 3.9 |
| IA | OpenAI GPT-4o / GPT-4o-mini |
| QR | chillerlan/php-qrcode 5.0 |
| Markdown | Parsedown 1.7 |

### Modulos principales (18)

| Modulo | Descripcion |
|--------|-------------|
| Documentos SGSST | 34+ documentos normativos generados con IA (politicas, programas, formatos) |
| Plan de Trabajo Anual (PTA) | Actividades PHVA por cliente, edicion inline, exportacion Excel |
| Evaluacion Estandares Minimos | Decreto 1072 / Res. 0312, evaluacion por ciclo, historial puntajes |
| Indicadores SST | 17 indicadores (frecuencia, severidad, ausentismo, cobertura) |
| Actas de Visita | Registro con fotos, firma, PDF, notificaciones |
| Actas de Reunion | Comites, asistentes, compromisos, votaciones, firma digital |
| Contratos | Ciclo completo: creacion, firma digital, PDF |
| Capacitaciones | Cronograma, asistencia, induccion por etapas |
| Matriz Legal | Marco normativo con generacion IA |
| Matriz de Comunicacion | Planificacion de comunicaciones SST |
| Firmas Digitales | Firma electronica via token por email |
| Comites Electorales | Procesos electorales COPASST/CCL con votacion electronica |
| Inspecciones | Locativa, extintores, botiquin, senalizacion |
| KPIs | 17 KPIs con definiciones, variables, periodos de medicion |
| Pendientes | Compromisos con conteo de dias |
| Presupuesto SST | Categorias, items, detalle de ejecucion |
| Mantenimientos | Control de mantenimientos y vencimientos |
| Chat Otto (IA) | Asistente IA con consultas SQL readonly |

### Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultant | Gestion de clientes asignados + generacion de documentos + chat IA |
| client | Portal readonly + chat Otto (solo SELECT) |

### Estructura del proyecto

```
enterprisesst/
├── app/
│   ├── Commands/          # 2 comandos CLI (cron jobs)
│   ├── Config/            # Routes, Database, Filters, Security
│   ├── Controllers/       # ~201 controladores
│   ├── Database/          # Migraciones y seeders
│   ├── Filters/           # AuthFilter, ApiKeyFilter, AuthOrApiKeyFilter
│   ├── Helpers/           # Funciones auxiliares
│   ├── Libraries/         # 13 librerias de logica de negocio
│   ├── Models/            # ~98 modelos
│   ├── Services/          # 37+ servicios (IA, indicadores, documentos)
│   ├── SQL/               # Scripts de migracion
│   ├── Traits/            # Traits reutilizables
│   └── Views/             # Vistas PHP
├── docs/                  # Documentacion tecnica (24+ archivos)
├── public/                # Punto de entrada web (index.php)
├── scripts/               # Scripts utilitarios
├── sql/                   # Scripts SQL adicionales
├── tests/                 # Tests PHPUnit
├── translations/          # Archivos de traduccion
├── writable/              # Logs, cache, sesiones, uploads
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables
├── CONTRIBUTING.md        # Guia de contribucion
├── README.md              # Documentacion principal
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

### Cron jobs (2 tareas programadas)

| Comando | Frecuencia | Descripcion |
|---------|-----------|-------------|
| `php spark notificaciones:procesar-actas` | Diario | Procesa notificaciones de actas pendientes |
| `php spark pendientes:resumen` | Periodico | Genera resumen de pendientes y envia por email |

---

## 2. MAPA DE BASE DE DATOS

**Motor:** MySQL 8.0.45 (DigitalOcean Managed)
**Base de datos:** empresas_sst
**Tamano total:** 13.83 MB
**SSL:** Required

### Usuarios de base de datos

| Usuario | Permisos | Uso |
|---------|----------|-----|
| cycloid_userdb | Full access | Aplicacion principal (CRUD) |
| empresas_readonly | SELECT only (vistas v_* + tablas maestras) | Portal cliente (Chat Otto) |

### Resumen

- **136 tablas** (BASE TABLE)
- **43 vistas** (VIEW) — 38 con prefijo `v_` para portal cliente + 5 de negocio
- **84 foreign keys** definidas
- **52 tablas vacias** (38%) — modulos pendientes o en desarrollo
- **Engine:** 100% InnoDB

### Tablas principales por modulo

**Nucleo (7 tablas):** tbl_clientes (11 reg), tbl_usuarios (30), tbl_usuario_roles (12), tbl_roles (3), tbl_consultor (4), tbl_sesiones_usuario (132), tbl_agente_chat_log (11)

**Plan de trabajo (4 tablas):** tbl_pta_cliente (230 reg), tbl_pta_cliente_audit (81), tbl_pta_cliente_old (141), tbl_inventario_actividades_plandetrabajo (146)

**Evaluacion estandares (4 tablas):** evaluacion_inicial_sst (434 reg — 464 KB), estandares (15), estandares_accesos (502), tbl_estandares_minimos (60)

**Documentos SST (18 tablas):** tbl_documentos_sst (34 — 1.5 MB), tbl_doc_versiones_sst (32 — 1.6 MB), tbl_doc_secciones_config (339), tbl_doc_tipo_configuracion (34), tbl_doc_firmantes_config (67), tbl_doc_carpetas (472), tbl_doc_plantillas (75), tbl_doc_tipos (14), tbl_doc_firma_solicitudes (80), tbl_doc_firma_evidencias (49 — 1.5 MB), tbl_doc_firma_audit_log (404), + 7 tablas auxiliares

**Actas de visita (5 tablas):** tbl_acta_visita (3), tbl_acta_visita_integrantes (6), tbl_acta_visita_temas (10), tbl_acta_visita_fotos (0), tbl_acta_visita_pta (0)

**Actas de reunion (8 tablas):** tbl_actas (2), tbl_acta_asistentes (8 — 224 KB), tbl_acta_compromisos (0), tbl_acta_anexos (0), tbl_acta_votaciones (0), tbl_actas_notificaciones (13), tbl_actas_plantillas_orden (4), tbl_actas_tokens (0)

**Comites electorales (9 tablas):** tbl_comites (4), tbl_comite_miembros (8), tbl_candidatos_comite (29), tbl_procesos_electorales (4), tbl_votantes_proceso (92), tbl_votos_comite (45), tbl_jurados_proceso (4), + 3 tablas auxiliares

**Inspecciones (8 tablas):** tbl_inspeccion_locativa, tbl_inspeccion_extintores, tbl_inspeccion_botiquin, tbl_inspeccion_senalizacion (todas vacias) + 4 tablas detalle

**Capacitaciones (2 tablas):** capacitaciones_sst (68), tbl_cronog_capacitacion (74)

**KPIs (8 tablas):** tbl_kpis (17), tbl_kpi_definition (17), tbl_client_kpi (189 — 320 KB), tbl_kpi_type (4), tbl_kpi_policy (1), tbl_measurement_period (2), tbl_variable_numerator (15), tbl_variable_denominator (15)

**Indicadores SST (2 tablas):** tbl_indicadores_sst (190 — 272 KB), tbl_indicadores_sst_mediciones (0)

**Matriz legal (1 tabla):** matriz_legal (491 reg — 240 KB)

**Presupuesto (4 tablas):** tbl_presupuesto_sst (0), tbl_presupuesto_categorias (6), tbl_presupuesto_items (12), tbl_presupuesto_detalle (65)

**Reportes (3 tablas):** tbl_reporte (197 — 224 KB), report_type_table (25), detail_report (20)

**Otros:** tbl_contratos (6), tbl_pendientes (103), tbl_mantenimientos (13), tbl_marco_normativo (44 — 160 KB), tbl_vigias (2), client_policies (404), policy_types (46), accesos (61), dashboard_items (76), tbl_lookerstudio (5)

### Tabla central: tbl_clientes

26+ tablas dependen de `tbl_clientes.id_cliente` via foreign key. Es la entidad central del sistema.

Segunda tabla central: `tbl_consultor` con 8 FK entrantes.

### Tablas mas grandes por peso

| Tabla | Registros | Tamano |
|-------|-----------|--------|
| tbl_doc_versiones_sst | 32 | 1,632 KB |
| tbl_doc_firma_evidencias | 49 | 1,568 KB |
| tbl_documentos_sst | 34 | 1,568 KB |
| evaluacion_inicial_sst | 434 | 464 KB |
| tbl_client_kpi | 189 | 320 KB |
| tbl_indicadores_sst | 190 | 272 KB |
| matriz_legal | 491 | 240 KB |
| tbl_acta_asistentes | 8 | 224 KB |
| tbl_doc_secciones_config | 339 | 224 KB |
| tbl_reporte | 197 | 224 KB |

Las 3 tablas mas pesadas almacenan BLOBs/TEXT (contenido HTML de documentos y evidencias de firma), lo que explica su tamano alto con pocos registros.

### Tablas vacias (52 — 38% del total)

**Modulos sin uso:** tbl_acc_* (acciones correctivas, 4 tablas), tbl_inspeccion_* (inspecciones, 4 tablas + 4 detalle), tbl_informe_avances, tbl_cliente_sedes, tbl_cliente_transiciones, tbl_ciclos_visita

**Tablas auxiliares sin datos:** tbl_acta_compromisos, tbl_acta_anexos, tbl_acta_votaciones, tbl_actas_tokens, tbl_doc_documentos, tbl_doc_versiones, tbl_doc_secciones, tbl_doc_prompts, tbl_doc_exportaciones, tbl_doc_indicadores

**Posiblemente obsoletas:** prueba, tbl_plantillas_documentos_sst_old, tbl_log_procesos, matriz_comunicacion

### Vistas (43)

**Vistas de portal cliente (v_*):** v_acc_acciones, v_acc_hallazgos, v_acc_seguimientos, v_acta_compromisos, v_acta_visita, v_actas_comite, v_candidatos_comite, v_cliente_contexto, v_cliente_estandares, v_comite_miembros, v_contratos, v_cronog_capacitacion, v_doc_versiones_sst, v_documentos_sst, v_evaluacion_inicial, v_indicadores_mediciones, v_indicadores_sst, v_induccion_etapas, v_inspeccion_botiquin, v_inspeccion_extintores, v_inspeccion_locativa, v_inspeccion_senalizacion, v_lookerstudio, v_matrices, v_pendientes, v_presupuesto, v_presupuesto_detalle, v_procesos_electorales, v_pta_cliente, v_reportes, v_responsables_sst, v_vencimientos_mantenimientos, v_vigias

**Vistas de negocio:** cronograma_capacitaciones_cliente, evaluacion_inicial_cliente, pendientes_del_cliente, plan_de_trabajo_del_cliente, tbl_cliente, view_clientes_consultores, vista_cronograma_capacitaciones, vw_consumo_usuarios, vw_reporte_completo, vw_responsables_sst_activos

---

## 3. INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

### Resumen

| Servicio | Variable | Archivos | Estado |
|----------|----------|----------|--------|
| SendGrid | `SENDGRID_API_KEY` | 6+ | Activa |
| OpenAI | `OPENAI_API_KEY` | 5+ | Activa |
| Token API | `APP_API_KEY` | 2 | Activa — token interno |

### SendGrid

Usado en 6+ archivos para email transaccional: notificaciones de actas, recordatorios de pendientes, contratos, comites electorales.

**Patron:** `new \SendGrid(getenv('SENDGRID_API_KEY'))` y `curl` con Bearer token

**Archivos principales:**
- Libraries: SendGridMailer, InspeccionEmailNotifier
- Controllers: ComitesEleccionesController, ContractController
- Commands: ResumenPendientes, ProcesarNotificacionesActas

### OpenAI

Usado en 5+ archivos para IA generativa: generacion de documentos SST, chat Otto, matriz legal, cronograma capacitaciones.

**Patron:** cURL directo a `https://api.openai.com/v1/chat/completions`
**Modelos:** gpt-4o (principal), gpt-4o-mini (Otto, configurable via `OTTO_MODEL`)

**Archivos:**
- Controllers: GeneradorIAController, MatrizLegalController, CronogcapacitacionController, MatrizComunicacionController
- Services: AgenteChatService

### HALLAZGOS CRITICOS DE SEGURIDAD

**CRITICO — Repositorio publico:**

El repositorio `github.com/edielestudiante2023/enterprisesst` es **PUBLICO**. El archivo `.env` contiene credenciales reales y esta trackeado en el historial de git. Las siguientes claves deben rotarse:

| Variable | Accion |
|----------|--------|
| `SENDGRID_API_KEY` | ROTAR INMEDIATAMENTE |
| `OPENAI_API_KEY` | ROTAR INMEDIATAMENTE |
| `APP_API_KEY` | Regenerar |
| `readonly.password` | Cambiar en BD |

**CRITICO — Credenciales en .env trackeado:**

El archivo `.env` esta en el `.gitignore` pero fue commitedo previamente al historial de git. Las credenciales estan expuestas en commits anteriores.

**ALTO — Endpoint OpenAI incorrecto:**

`MatrizComunicacionController.php` usa el endpoint `v1/responses` que no es estandar de OpenAI (el correcto es `v1/chat/completions`).

---

## 4. DOCUMENTACION DEL PROYECTO

### Archivos creados en el repositorio

| Archivo | Descripcion |
|---------|-------------|
| `README.md` | Documentacion principal: stack, 18 modulos, roles, estructura, instalacion, cron jobs, deploy |
| `CONTRIBUTING.md` | Guia de contribucion: flujo de ramas, convencion de commits, reglas, proceso de revision |
| `.env.example` | Template con todas las variables de entorno necesarias (sin valores reales) |

### README.md incluye

- Stack tecnologico completo (8 componentes)
- 18 modulos con descripcion
- 3 roles de usuario con accesos
- Estructura de carpetas
- Requisitos previos e instrucciones de instalacion
- 11 variables de entorno documentadas
- 2 cron jobs con frecuencia y descripcion
- Instrucciones de deploy

### CONTRIBUTING.md incluye

- Flujo de ramas (main → develop → feature/ → hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:, test:, style:)
- Convencion de nombres de ramas (feature/modulo-desc, hotfix/bug-desc)
- 5 reglas (no push directo, no credenciales, no temporales, no destructivos)
- Proceso de revision con pipeline CI/CD
- Seccion de seguridad

### .env.example incluye

- Variables de entorno para BD principal y readonly (sin passwords)
- API Keys de SendGrid y OpenAI (vacias)
- Token interno APP_API_KEY (vacio)
- Configuracion de cache y entorno

---

## 5. RAMAS DE TRABAJO

### Estructura creada

```
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Aqui se unen los cambios antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

### Estado actual

| Rama | Estado | Commit actual |
|------|--------|---------------|
| main | Existente, en remoto | 9cbab9e (fix: todos los cards target_blank) |
| develop | Creada, pendiente push a remoto | Mismo commit que main |
| cycloid | Legacy — sera reemplazada por develop | Rama de trabajo actual |

### Proteccion de ramas (pendiente en Gitea)

- **main:** protegida, requiere PR, no push directo
- **develop:** protegida, requiere PR desde feature/

### Flujo de trabajo

- Nueva funcionalidad: `develop` → `feature/nombre` → PR a `develop` → PR a `main`
- Hotfix urgente: `main` → `hotfix/nombre` → PR a `main` + PR a `develop`

---

## 6. PIPELINES CI/CD

### Plataforma: Gitea con Gitea Runner (act_runner)

### Pipeline 1: Validar y Deploy a Dev/QA

**Archivo:** `.gitea/workflows/validate-and-deploy-qa.yml`
**Trigger:** Push/PR a develop o feature/*

```
git push → Gitea → Runner → Tests + Trivy + Semgrep + Secrets → Deploy SSH → LXC (Dev/QA)
```

| Job | Que hace | Bloquea si falla |
|-----|---------|------------------|
| test | `php -l` en todos los .php de app/ | Si |
| trivy | Escaneo de vulnerabilidades en dependencias (HIGH/CRITICAL) | Si |
| semgrep | Analisis estatico de seguridad (reglas PHP + secrets + security-audit) | Si |
| secrets-scan | Busca API keys hardcodeadas (SendGrid, OpenAI, DB) | Si |
| deploy-qa | SSH al LXC Dev/QA y ejecuta deploy | Solo en push a develop |

### Pipeline 2: Cutover a Produccion

**Archivo:** `.gitea/workflows/cutover-production.yml`
**Trigger:** Push a main (despues de merge de PR desde develop)

```
PR develop → main → Validacion → Trivy + Semgrep (paralelo) → Deploy SSH → aaPanel
                                                                          → Verificacion HTTP
```

| Job | Que hace |
|-----|---------|
| validate | Sintaxis PHP + busqueda de credenciales |
| trivy | Escaneo vulnerabilidades (paralelo con semgrep) |
| semgrep | Analisis estatico seguridad (paralelo con trivy) |
| deploy-production | SSH al aaPanel (66.29.154.174) + deploy + verificacion HTTP post-deploy |

**Todo por pipeline, nada manual.**

### Secrets necesarios en Gitea

**Para Dev/QA:** QA_HOST, QA_USER, QA_SSH_KEY, QA_PATH
**Para Produccion:** PROD_HOST, PROD_USER, PROD_SSH_KEY, PROD_PATH

### Flujo completo

```
feature/xxx → push → Validacion → PR a develop → Validacion → merge
                                                                 ↓
                                          Deploy automatico a LXC Dev/QA
                                                                 ↓
                                              Pruebas en QA (manuales o auto)
                                                                 ↓
                                          PR develop → main → Validacion → merge
                                                                             ↓
                                                     Cutover automatico a aaPanel
                                                                             ↓
                                                          Verificacion post-deploy
                                                                             ↓
                                                              EN PRODUCCION
```

---

## 7. ORGANIZACION DEL REPOSITORIO

### Estado del repositorio

| Aspecto | Estado actual | Accion |
|---------|--------------|--------|
| Visibilidad | PUBLICO en GitHub | Migrar a Gitea privado |
| .gitignore | Presente, parcialmente configurado | Actualizar (falta .claude/) |
| .env.example | Creado con todas las variables | OK |
| Archivos basura | 23+ scripts CLI + 3 CSVs + 3 .md vacios trackeados | Pendiente limpieza |

### .gitignore — Estado actual

**Bien configurado:**
- .env excluido (pero fue commitedo antes)
- vendor/ excluido
- writable/cache, logs, session excluidos
- .vscode/, .idea/ excluidos
- composer-setup.php, composer excluidos

**Falta agregar:**
- `.claude/` — carpeta local del IDE Claude Code
- `*.stackdump` — archivos de debug
- `cli_*.php`, `tmp_*.php`, `temp_*.php` — scripts temporales en raiz
- `*.csv` — archivos de datos de prueba en raiz

### Archivos basura trackeados en git (pendiente limpieza)

**Scripts CLI/temporales en raiz (23 archivos):**
- cli_fix_accesos_url.php, cli_fix_phva.php, cli_igualar_7e_a_7a.php
- cli_indicadores_base_capacitacion.php, cli_limpiar_clientes_prueba.php
- actualizar_sp_produccion.php, check_firma_tables.php
- consultar_marco_temp.php, consultar_todos_marcos.php
- ejecutar_*.php (6 variantes)
- execute_criterio.php
- limpiar_*.php (2 variantes)
- revisar_contenido_generado.php, temp_debug.php
- verificar_*.php (6 variantes)

**Archivos CSV de datos en raiz:**
- AUTOEVAULACION AFIANCOL 16 DICIEMBRE (1).csv (38 KB)
- csvevaluacionestandaresminimosph.csv (60 KB)
- FT-SST-004 PRESUPUESTO 2025.xlsx - PRESUPUESTO 2024.csv
- MUESTRA MATRIZ LEGAL.csv

**Archivos markdown basura en raiz:**
- AA_ copy 2.md (0 bytes)
- AA_ copy 3.md (0 bytes)
- AA_ copy 4.md (7.6 KB)

**Otros:**
- bash.exe.stackdump

**15+ archivos .md sueltos en raiz** que deberian moverse a `docs/`:
- libreria_estandares_0312_2019.md
- PROMPT_NUEVO_DOCUMENTO_SST.md
- proyecto_documentacion_sst_parte1.md a parte9.md
- REVISION_CONTROL_DOCUMENTAL.md

### Estado del deploy en produccion

El aplicativo enterprisesst **NO esta desplegado en el servidor aaPanel (66.29.154.174) (66.29.154.174) actualmente**. Los aplicativos existentes en /www/wwwroot/ son: cycloidtalent, tat_cycloid, heroicos, psirysk, cycloidmanagement, auditorias, kpi — ninguno usa la BD `empresas_sst`.

---

## 8. HALLAZGOS CRITICOS Y ACCIONES PENDIENTES

### Prioridad CRITICA

| # | Accion | Responsable |
|---|--------|-------------|
| 1 | Hacer repo privado o migrar a Gitea | Consultor/Cliente |
| 2 | Rotar TODAS las API Keys (OpenAI, SendGrid, APP_API_KEY) | Cliente |
| 3 | Cambiar password readonly BD (`EmpresasReadOnly2026!` expuesta) | Cliente |
| 4 | Purgar historial git del archivo .env (contiene credenciales reales) | Cliente |

### Prioridad ALTA

| # | Accion | Responsable |
|---|--------|-------------|
| 5 | Push de rama develop al remoto | Cliente |
| 6 | Configurar proteccion de ramas en Gitea | Consultor |
| 7 | Configurar secrets en Gitea para pipelines CI/CD | Consultor |
| 8 | Desplegar aplicativo en servidor aaPanel (66.29.154.174) | Consultor/Cliente |
| 9 | Agregar `.claude/` y patrones temporales al .gitignore | Cliente |

### Prioridad MEDIA

| # | Accion | Responsable |
|---|--------|-------------|
| 10 | Limpiar 23+ scripts temporales del repo (commit de limpieza) | Cliente |
| 11 | Mover 15+ .md sueltos de raiz a docs/ | Cliente |
| 12 | Eliminar archivos basura (AA_ copy *.md, CSVs de prueba) | Cliente |
| 13 | Centralizar email en clase EmailService (SendGrid usado en 6+ archivos) | Cliente |
| 14 | Centralizar OpenAI en un servicio unico | Cliente |
| 15 | Corregir endpoint OpenAI en MatrizComunicacionController (v1/responses → v1/chat/completions) | Cliente |

---

*Documento generado el 2026-04-05. Preparado como entregable del proceso de hardening del repositorio enterprisesst.*
