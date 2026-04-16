# Continuacion Chat — Multi-tenant por Empresa Consultora

**Fecha:** 2026-04-16
**Branch:** cycloid
**Estado:** Implementacion completa. Pendiente deploy a PROD y smoke test final.

---

## QUE SE HIZO

### Fase 1 — BD (LOCAL + PROD completados)
- Creada `tbl_empresa_consultora` (razon_social, nit, logo, estado, plan, etc.)
- Agregado `id_empresa_consultora` a `tbl_consultor` + backfill (todos → empresa 1 = Cycloid)
- ENUM `tbl_usuarios.tipo_usuario` ampliado con `superadmin`
- Script: `scripts/multitenant_01_schema.php` (idempotente, LOCAL + PROD)

### Fase 2 — Sesion + Guard (LOCAL + PROD)
- `AuthController::loginPost` inyecta `id_empresa_consultora`, `razon_social_empresa`, `is_superadmin` en sesion
- Rama `superadmin` en login: role='admin' + tipo_real='superadmin' + is_superadmin=true
- Bloqueo de login si empresa esta suspendida/inactiva (salvo superadmin)
- `app/Libraries/TenantFilter.php` — helper central (getEmpresaId, isSuperAdmin, resolverEmpresa, applyToClientQuery, applyToConsultorQuery, applyToUserQuery, assertClientBelongsToTenant, clienteEnMiEmpresa, getMyClientIds)
- `app/Filters/TenantGuardFilter.php` — verifica id_cliente en POST/GET
- `app/Filters/SuperAdminOnlyFilter.php` — bloquea rutas solo-superadmin
- Registrados en `app/Config/Filters.php`
- head.consultant.cycloidtalent@gmail.com marcado como superadmin (LOCAL + PROD)
- Script: `scripts/multitenant_02_marcar_superadmin.php`

### Fase 3 — Blindar listados
- ConsultantController: index, listClients, addClient, editClient, listConsultants
- ConsultantDashboardController: index, listClients, listConsultants, addClient, editClient
- AdminDashboardController: index, addClient, listConsultants, addConsultantPost
- UserController: listUsers, addUser
- Todos usan `TenantFilter::applyToClientQuery/applyToConsultorQuery/applyToUserQuery`

### Fase 4 — Branding + restricciones
- `CYCLOID TALENT` → `ASESOR SST` en CronogcapacitacionController (lineas 540, 871)
- Modulo contratos bloqueado para no-superadmin (SuperAdminOnlyFilter en routes `contracts/*`)
- NO se toco: URLs cycloidtalent.com en footers, email sender SendGrid, emails personales Cycloid

### Fase 5 — CRUD empresas + proteccion global
- `app/Controllers/EmpresaConsultoraController.php` — CRUD completo
  - Superadmin: listado todas las empresas, crear con primer admin, editar, toggle estado
  - Admin: "Mi Empresa" (solo edita la suya)
  - crearPrimerAdmin(): crea consultor + usuario con password temporal
- `app/Views/admin/empresas_consultoras/index.php` — listado con DataTable
- `app/Views/admin/empresas_consultoras/form.php` — crear/editar con logo upload
- Rutas en Routes.php: admin/empresas-consultoras/*, admin/mi-empresa
- Card "Superadmin > Empresas Consultoras" en admindashboard.php (solo superadmin)
- Card "Mi Empresa" en admindashboard.php (admin no-superadmin)
- `id_empresa_consultora` se fuerza al crear consultor (3 controllers)

### Proteccion global a nivel de modelo
- `app/Models/Traits/TenantScopedModel.php` — trait con override de findAll, first, update, delete
- Aplicado a **65 modelos** con id_cliente via script `scripts/multitenant_04_aplicar_trait.php`
- `ClientModel.php` — override propio de findAll, find, first con filtro tenant
- **Lecturas**: findAll/first filtran por empresa automaticamente
- **Escrituras**: update/delete verifican pertenencia del registro antes de ejecutar; lanzan RuntimeException si no pertenece
- Superadmin y CLI: bypass total en las 4 operaciones

### Empresa de prueba (SOLO LOCAL)
- "TEST Colega SAS" (id=2) con consultor, cliente y usuario admin
- Login: colega.test@example.com / colega123
- Rollback: `php scripts/multitenant_03_empresa_prueba.php --rollback`
- Usuario pidio NO borrarla (la usa para demo a junta directiva)

---

## QUE FALTA

### Pendiente inmediato
- [ ] **Deploy a PROD**: git add/commit/push de todo el codigo de Fases 2-5 (la BD ya esta en PROD desde Fase 1+2)
- [ ] **Smoke test PROD**: login superadmin en produccion, verificar que todo funciona
- [ ] **Smoke test escrituras**: usuario confirmo que lecturas estan OK, falta confirmar que update/delete bloquean correctamente

### Mejoras futuras (NO urgentes)
- [ ] Envio de email con credenciales al crear empresa (hoy muestra password en flash message)
- [ ] CRUD usuarios por empresa (hoy se usa el CRUD general de UserController)
- [ ] Dashboard del colega: ocultar tiles de modulos que no aplican (ej: contratos)
- [ ] Branding avanzado: color_primario de empresa en UI
- [ ] Facturacion / plan por empresa (campos existen pero no hay logica)

---

## ARCHIVOS CREADOS

```
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
scripts/multitenant_01_schema.php
scripts/multitenant_02_marcar_superadmin.php
scripts/multitenant_03_empresa_prueba.php
scripts/multitenant_04_aplicar_trait.php
public/uploads/empresas/ (directorio para logos)
```

## ARCHIVOS MODIFICADOS

```
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
app/Models/UserModel.php — superadmin en validacion tipo_usuario (ya en PROD via Fase 1)
app/Views/consultant/admindashboard.php — cards Superadmin / Mi Empresa
65 modelos en app/Models/ — trait TenantScopedModel agregado (via script automatico)
```

## DECISIONES TOMADAS

1. **No denormalizar id_empresa_consultora en tbl_clientes** — se hereda via id_consultor
2. **Superadmin reutiliza rutas admin/** — role='admin' en sesion, tipo_real='superadmin'
3. **Guard por POST/GET id_cliente** en TenantGuardFilter (no parsea URI segments)
4. **Proteccion principal a nivel de MODELO** (trait), no de controller ni middleware
5. **No tocar URLs de Cycloid en footers** — riesgo alto, impacto bajo
6. **No tocar email sender** — infraestructura compartida
7. **Modulo contratos exclusivo superadmin** — el colega no lo necesita
8. **Lecturas Y escrituras protegidas** — findAll/first filtran; update/delete validan pertenencia

## FLUJO GIT PARA DEPLOY

```bash
git add .
git commit -m "feat: multi-tenant por empresa consultora — aislamiento completo"
git checkout main
git merge cycloid
git push origin main
git checkout cycloid
```

## PROMPT PARA CONTINUAR EN NUEVO CHAT

```
Estoy retomando el trabajo de multi-tenant por empresa consultora.
Lee docs/CONTINUACION_CHAT.md y docs/MULTI_TENANT_EMPRESA_CONSULTORA/01_ARQUITECTURA.md
para contexto completo.

Estado: implementacion completa en LOCAL, pendiente [lo que necesites].
```
