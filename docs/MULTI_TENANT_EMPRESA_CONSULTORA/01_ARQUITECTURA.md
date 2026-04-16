# Multi-tenant por Empresa Consultora

## Problema

Hoy la plataforma asume una sola empresa consultora (Cycloid Talent SAS): todos los consultores ven todos los clientes. Un colega externo quiere pagar por usar la plataforma para gestionar el SG-SST de sus propios clientes, sin ver los de Cycloid y sin que Cycloid vea los suyos (salvo el dueño para soporte).

## Modelo

### Tenant = `tbl_empresa_consultora`

Nueva tabla con razón social, NIT, logo, estado, etc. El tenant es la **empresa consultora**, no el cliente.

### Cadena de pertenencia

```
tbl_empresa_consultora (tenant)
        |
        | 1-N
        v
tbl_consultor (id_empresa_consultora FK)
        |
        | 1-N (via tbl_clientes.id_consultor)
        v
tbl_clientes
        |
        | 1-N
        v
tbl_doc_documentos y todo lo demás (hereda via id_cliente)
```

`tbl_clientes` **no** se denormaliza con `id_empresa_consultora` — se hereda via `id_consultor` para evitar desincronización.

### Roles

Se amplía `tbl_usuarios.tipo_usuario` con `superadmin`:

| Rol | Alcance |
|---|---|
| `superadmin` | Cycloid. Atraviesa todo filtro de tenant. Puede crear empresas consultoras, ver cualquier cliente. |
| `admin` | Administrador de SU empresa consultora. El colega será `admin` de su empresa. |
| `consultant` | Consultor de SU empresa. |
| `client`, `miembro` | Heredan empresa via `tbl_clientes.id_consultor`. |

### Sesión

En login, además de `user_id`, `role`, etc., se resuelve e inyecta:

- `id_empresa_consultora` (int)
- `is_superadmin` (bool)

Para `client`/`miembro` se resuelve via `tbl_clientes.id_consultor → tbl_consultor.id_empresa_consultora`.

## Estrategia de filtrado

**"Filter global + guard por cliente"** — no se auditan los ~378 controllers uno por uno.

### Capa 1 — Blindar listados (10-15 puntos)

Los puntos donde se enumeran clientes/consultores/usuarios SIN filtro de cliente individual (ej: dashboards, `findAll()` en ConsultantController, UserController, AdminDashboardController). Se les aplica un helper `TenantFilter::applyTo*QueryBuilder()`.

### Capa 2 — Guard central por `id_cliente`

Un filtro CodeIgniter (`TenantGuardFilter`) en rutas web autenticadas que, si la request tiene `id_cliente` en URL o POST, valida que ese cliente pertenezca a la empresa del usuario. Si no, 403.

Esto cubre automáticamente los cientos de endpoints que acceden por `id_cliente` sin modificarlos.

### Capa 3 — `superadmin` bypass

Ambas capas consultan `TenantFilter::isSuperAdmin()` primero y retornan sin filtrar si lo es.

## Branding en documentos SST

`getContextoBase()` agrega una clave `empresa_consultora` resuelta desde `id_cliente → id_consultor → id_empresa_consultora`. Las plantillas PDF/Word leen `empresa_consultora.razon_social`, `empresa_consultora.logo`, etc., en lugar de hardcodear "Cycloid Talent".

Cliente de Cycloid → documento con branding Cycloid.
Cliente del colega → documento con branding del colega.

## CRUD de empresas consultoras

Visible solo si `is_superadmin`. Permite crear empresa, subir logo, asignar el primer usuario `admin`, activar/suspender (corta el login de sus usuarios si deja de pagar).

## Estado de implementación

- Fase 1 (BD): tabla + alter consultor + ENUM superadmin + seed + backfill. Local y producción.
- Fase 2 (sesión + helpers): AuthController inyecta empresa, TenantFilter, TenantGuardFilter.
- Fase 3 (listados): blindar los puntos críticos.
- Fase 4 (branding docs).
- Fase 5 (CRUD empresas).

Ver plan completo en `C:\Users\elipt\.claude\plans\cheerful-munching-squirrel.md`.
