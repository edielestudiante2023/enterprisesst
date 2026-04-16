# Fase 2 - Plan de Ejecucion (salvamento)

Este archivo queda como registro de los pasos comprometidos para Fase 2.
Si algo se rompe en mitad de la fase, sirve para retomar desde el punto exacto.

## Objetivo
Introducir la capa de sesion + helper central + guard sin cambiar la experiencia
actual del usuario. Al final de Fase 2:
- Todos los usuarios siguen viendo lo mismo que ven hoy (no hay filtros de listado aun).
- La sesion del usuario contiene `id_empresa_consultora` e `is_superadmin`.
- Cualquier acceso directo por `id_cliente` es validado contra la empresa del usuario.
- El usuario `head.consultant.cycloidtalent@gmail.com` queda como `superadmin`.

## Pasos

1. **Lectura previa** de los archivos involucrados:
   - `app/Controllers/AuthController.php`
   - `app/Controllers/BaseController.php`
   - `app/Config/Filters.php`
   - `app/Controllers/ClientController.php`

2. **Crear `app/Libraries/TenantFilter.php`** con metodos estaticos:
   - `getEmpresaId(): ?int`
   - `isSuperAdmin(): bool`
   - `resolverEmpresaDesdeUsuario(array $user): ?array`  -> devuelve [id_empresa, razon_social, estado]
   - `applyToClientQuery($builder)`  -> agrega WHERE por subquery a consultor de la empresa
   - `applyToConsultorQuery($builder)`  -> agrega WHERE id_empresa_consultora
   - `assertClientBelongsToTenant(int $idCliente): void`  -> lanza ForbiddenException si no pertenece
   - `clienteEnMiEmpresa(int $idCliente): bool`

3. **Modificar `AuthController::loginPost`**:
   - Tras login exitoso, llamar `TenantFilter::resolverEmpresaDesdeUsuario($user)`.
   - Si la empresa esta `suspendido` o `inactivo` y el usuario NO es superadmin: rechazar login con mensaje claro.
   - Guardar en sesion: `id_empresa_consultora`, `razon_social_empresa`, `estado_empresa`, `is_superadmin`.
   - Mantener TODA la logica existente; solo AGREGAR.

4. **Crear `app/Filters/TenantGuardFilter.php`**:
   - `before()`:
     - Si no hay sesion o `is_superadmin` -> return (pasa).
     - Buscar `id_cliente` en URI segments, POST, GET.
     - Si aparece y no pertenece a la empresa del usuario -> `throw PageNotFoundException` (mejor 404 que 403 para no leak info).
   - No corre en CLI.

5. **Registrar filter en `app/Config/Filters.php`**:
   - Alias `tenantguard` -> `App\Filters\TenantGuardFilter::class`.
   - Aplicar via `$filters` con listas explicitas (rutas que aceptan id_cliente).
   - NO usar `$globals` todavia para minimizar riesgo.

6. **Script CLI `scripts/multitenant_02_marcar_superadmin.php`**:
   - LOCAL primero, luego PROD.
   - UPDATE tbl_usuarios SET tipo_usuario='superadmin' WHERE email='head.consultant.cycloidtalent@gmail.com'.
   - Verificar antes/despues.

7. **Smoke test manual** (lo hace el usuario):
   - Login como head.consultant.cycloidtalent@gmail.com.
   - Verificar que llega al dashboard.
   - Abrir un cliente cualquiera.
   - Reportar si hay errores.

## Rollback rapido

Si algo se rompe en Fase 2:
- **Paso inmediato**: comentar el registro del filter en `app/Config/Filters.php` -> el guard deja de correr.
- **Sesion**: los campos nuevos son aditivos, no rompen nada si se ignoran.
- **tipo_usuario = superadmin**: revertir con UPDATE tbl_usuarios SET tipo_usuario='admin' WHERE email='...'.
- El script fase1 NO se toca (ya esta en PROD).

## Archivos a crear/modificar

**Nuevos:**
- `app/Libraries/TenantFilter.php`
- `app/Filters/TenantGuardFilter.php`
- `scripts/multitenant_02_marcar_superadmin.php`

**Modificados:**
- `app/Controllers/AuthController.php` (solo loginPost)
- `app/Config/Filters.php` (registro alias + ruteo)

## Post Fase 2 (NO hacer aun)
- Fase 3: blindar listados (findAll en ConsultantController, UserController, AdminDashboardController...).
- Fase 4: branding en documentos SST.
- Fase 5: CRUD empresas consultoras.
