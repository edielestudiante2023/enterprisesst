# Multi-tenant por Empresa Consultora — Estado Final

**Fecha:** 2026-04-16 a 2026-04-17
**Branch principal:** main (ya desplegado en PROD)

Este documento resume el estado final de la implementacion multi-tenant por empresa consultora.
Para arquitectura detallada ver `01_ARQUITECTURA.md`.

---

## Objetivo del proyecto

Permitir que consultores externos a Cycloid Talent SAS (ej: "Ingeniero Pepito") usen la
plataforma EnterpriseSST para gestionar el SG-SST de SUS propios clientes, con aislamiento
completo: no ven datos de Cycloid ni de otras empresas, y Cycloid solo los ve como superadmin.

---

## Que se implemento

### Fase 1 — BD (LOCAL + PROD)

- Nueva tabla `tbl_empresa_consultora` (razon_social, nit, logo, estado, plan, fecha_inicio_contrato)
- Columna `id_empresa_consultora` en `tbl_consultor` + backfill (todos los existentes → empresa 1 = Cycloid)
- ENUM `tbl_usuarios.tipo_usuario` ampliado con `superadmin`
- Script: `scripts/multitenant_01_schema.php`

### Fase 2 — Sesion + Guard (LOCAL + PROD)

- `AuthController::loginPost` inyecta `id_empresa_consultora`, `razon_social_empresa`, `is_superadmin` en sesion
- Rama `superadmin` en login (role='admin' con tipo_real='superadmin')
- Login bloqueado si empresa esta suspendida/inactiva (salvo superadmin)
- `app/Libraries/TenantFilter.php` — helper central
- `app/Filters/TenantGuardFilter.php` — valida id_cliente en POST/GET
- `app/Filters/SuperAdminOnlyFilter.php` — bloquea rutas solo-superadmin (contratos, CRUD empresas)
- head.consultant.cycloidtalent@gmail.com marcado como superadmin (script 02)

### Fase 3 — Blindar listados

- ConsultantController, ConsultantDashboardController, AdminDashboardController, UserController
- Todos usan `TenantFilter::applyToClientQuery/applyToConsultorQuery/applyToUserQuery`

### Fase 4 — Branding + restricciones

- `CYCLOID TALENT` → `ASESOR SST` en CronogcapacitacionController
- Modulo `/contracts/*` solo superadmin
- NO se toco: URLs cycloidtalent.com en footers, email sender SendGrid, emails personales

### Fase 5 — CRUD empresas + proteccion global

- `EmpresaConsultoraController.php` — CRUD completo
- Vistas en `app/Views/admin/empresas_consultoras/`
- Ruta `/admin/empresas-consultoras/*` (superadmin) y `/admin/mi-empresa` (admin)
- Cards condicionales en dashboard (Superadmin / Mi Empresa)
- `id_empresa_consultora` se fuerza al crear consultor

### Proteccion global a nivel de modelo

- `app/Models/Traits/TenantScopedModel.php` aplicado a 58 modelos (despues de limpieza)
- **Lecturas**: `findAll()` y `first()` filtran por empresa automaticamente
- **Escrituras**: `update()` y `delete()` validan pertenencia antes de ejecutar; lanzan RuntimeException si no pertenece
- `ClientModel.php` — override propio de `findAll/find/first`
- Superadmin y CLI: bypass total en las 4 operaciones
- El trait verifica si la tabla tiene la columna `id_cliente` antes de aplicar filtro (previene errores SQL)

### Email de bienvenida

- Al crear una empresa con "Primer Usuario Admin", se envia email via SendGrid
- Diseño con branding EnterpriseSST, credenciales, link al login, guia de primeros pasos
- `EmpresaConsultoraController::enviarEmailBienvenida()`

---

## Riesgos conocidos aceptados

### 1. Rutas de documentos SST que reciben `$idDocumento` directo (6 metodos)

Estas rutas no cargan primero el cliente para validar tenant:
- `DocumentosSSTController::generarPDF($idDocumento)`
- `DocumentosSSTController::exportarPDF($idDocumento)`
- `DocumentosSSTController::publicarPDF($idDocumento)`
- `DocumentosSSTController::exportarWord($idDocumento)`
- `DocumentosSSTController::historialVersiones($idDocumento)`
- `DocumentosSSTController::descargarVersionPDF($idVersion)`

**Decision**: Riesgo aceptado. Requiere URL-crafting deliberado; los usuarios finales no son
desarrolladores y no tienen ese vector. Si en el futuro se requiere proteccion, agregar
`TenantFilter::assertDocumentoBelongsToTenant()` al inicio de cada metodo.

### 2. Tablas con `id_cliente` sin modelo con trait (15 tablas)

Tablas como `tbl_documentos_sst`, `tbl_doc_actividades`, `tbl_cliente_sedes`, `tbl_presupuesto_sst`,
`tbl_votantes_proceso`, etc. Acceden via query builder directo sin modelo dedicado.

**Decision**: La mayoria de controllers que las usan pasan por `ClientModel::find()` primero,
que valida tenant. El riesgo residual es bajo y aceptado.

### 3. URLs cycloidtalent.com en footers de vistas

Muchas vistas tienen links hardcodeados a cycloidtalent.com/facebook/dashboard. No afecta
funcionalidad del colega pero aparecen en su UI.

**Decision**: Cambio complejo con alto riesgo de romper vistas. No se toca.

---

## Scripts CLI disponibles

Todos en `scripts/`:

| Script | Proposito |
|--------|-----------|
| `multitenant_01_schema.php` | Crea tabla + alter + ENUM + backfill. Idempotente. LOCAL y `--env=prod` |
| `multitenant_02_marcar_superadmin.php` | UPDATE tipo_usuario=superadmin para `head.consultant.cycloidtalent@gmail.com` |
| `multitenant_03_empresa_prueba.php` | Crea/remueve empresa "TEST Colega SAS" con consultor+cliente+usuario (solo LOCAL). Usa `--rollback` para borrar |
| `multitenant_04_aplicar_trait.php` | Agrega trait TenantScopedModel a modelos con id_cliente |
| `multitenant_05_diagnostico.php` | Audita modelos con trait vs tablas con id_cliente |
| `multitenant_06_limpiar_trait.php` | Remueve trait de modelos cuyas tablas no tienen id_cliente |

---

## Usuarios de prueba (solo LOCAL)

| Email | Password | Rol | Empresa |
|-------|----------|-----|---------|
| head.consultant.cycloidtalent@gmail.com | (la original) | superadmin | Cycloid Talent SAS |
| colega.test@example.com | colega123 | admin | TEST Colega SAS |

La empresa TEST Colega SAS fue creada para demo. NO borrarla sin consultar.

---

## Archivos creados (referencia)

```text
app/Libraries/TenantFilter.php
app/Models/Traits/TenantScopedModel.php
app/Models/EmpresaConsultoraModel.php
app/Filters/TenantGuardFilter.php
app/Filters/SuperAdminOnlyFilter.php
app/Controllers/EmpresaConsultoraController.php
app/Views/admin/empresas_consultoras/index.php
app/Views/admin/empresas_consultoras/form.php
docs/MULTI_TENANT_EMPRESA_CONSULTORA/01_ARQUITECTURA.md
docs/MULTI_TENANT_EMPRESA_CONSULTORA/02_FASE2_PLAN_EJECUCION.md
docs/MULTI_TENANT_EMPRESA_CONSULTORA/03_ESTADO_FINAL.md  (este archivo)
scripts/multitenant_01_schema.php ... multitenant_06_limpiar_trait.php
public/uploads/empresas/ (directorio para logos)
```

## Archivos modificados principales

```text
app/Config/Routes.php — rutas empresas consultoras
app/Config/Filters.php — tenantguard, superadminonly
app/Controllers/AuthController.php — sesion multi-tenant + rama superadmin
app/Controllers/ConsultantController.php — filtros tenant + forzar empresa en consultor
app/Controllers/ConsultantDashboardController.php — filtros tenant + forzar empresa
app/Controllers/AdminDashboardController.php — filtros tenant + forzar empresa
app/Controllers/UserController.php — filtros tenant
app/Controllers/CronogcapacitacionController.php — CYCLOID TALENT → ASESOR SST
app/Models/ClientModel.php — override findAll/find/first con filtro tenant
app/Models/ConsultantModel.php — id_empresa_consultora en allowedFields
app/Models/UserModel.php — superadmin en validacion tipo_usuario
app/Views/consultant/admindashboard.php — cards Superadmin / Mi Empresa
58 modelos en app/Models/ — trait TenantScopedModel
```

---

## Flujo para agregar un nuevo cliente externo (colega nuevo)

1. Como superadmin, entrar a `/admin/empresas-consultoras`
2. Click "+ Nueva Empresa"
3. Llenar datos de la empresa + nombre/email del primer admin
4. El sistema crea empresa + consultor + usuario + envia email de bienvenida
5. El colega recibe email, entra con sus credenciales, completa Mi Empresa
6. El colega crea sus consultores (quedan en su empresa automaticamente)
7. El colega crea sus clientes (solo puede asignar a sus consultores)
8. El colega opera normal: aislado de los demas

---

## Prompt para continuar en nuevo chat

```text
Estoy retomando el trabajo de multi-tenant por empresa consultora en EnterpriseSST.
Lee docs/MULTI_TENANT_EMPRESA_CONSULTORA/03_ESTADO_FINAL.md para el estado actual.
Estado: implementacion completa en LOCAL + PROD, funcionando.

Lo que quiero hacer ahora: [describir tarea]
```

---

## Commits relevantes en main

```text
2c6beaf feat: email de bienvenida al crear empresa consultora con primer admin
560919a fix: TenantScopedModel solo filtra tablas que realmente tienen id_cliente
075fb7c fix: remover TenantScopedModel de 7 modelos cuyas tablas no tienen id_cliente
711d270 feat: multi-tenant por empresa consultora — aislamiento completo (base)
```
