# Estructura de Almacenamiento de Miembros de Comités

## Fecha: 2026-02-03

## Resumen Ejecutivo

Los miembros de los comités SST se almacenan en la tabla `tbl_comite_miembros`. Esta tabla permite gestionar miembros de diferentes tipos de comités (COPASST, COCOLAB, Vigía SST, Brigada) con funcionalidades de:
- Registro de miembros principales y suplentes
- Control de roles (presidente, secretario, miembro)
- Permisos para crear/cerrar actas
- Retiro de miembros con historial

---

## Arquitectura de Tablas

### Diagrama de Relaciones

```
tbl_clientes (1) ──────────┬─────────────────────────> (N) tbl_comites
                           │                                   │
                           │                                   │ (1)
                           │                                   │
                           │                                   ▼
                           │                              (N) tbl_comite_miembros
                           │                                   │
                           │                                   │
                           ▼                                   │
              tbl_cliente_responsables_sst <───────────────────┘
                     (opcional FK: id_responsable)

tbl_tipos_comite (1) ──────────────────────────────> (N) tbl_comites
```

---

## Tabla Principal: `tbl_comite_miembros`

### Información General

| Propiedad | Valor |
|-----------|-------|
| **Nombre de tabla** | `tbl_comite_miembros` |
| **Modelo** | `App\Models\MiembroComiteModel` |
| **Llave primaria** | `id_miembro` |
| **Timestamps** | Sí (`created_at`, `updated_at`) |

### Estructura de Columnas

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_miembro` | INT (PK) | Identificador único del miembro |
| `id_comite` | INT (FK) | Referencia a `tbl_comites.id_comite` |
| `id_cliente` | INT (FK) | Referencia a `tbl_clientes.id_cliente` (desnormalizado para consultas rápidas) |
| `id_responsable` | INT (FK, nullable) | Referencia opcional a `tbl_cliente_responsables_sst.id_responsable` |
| `nombre_completo` | VARCHAR | Nombre completo del miembro |
| `tipo_documento` | VARCHAR | Tipo de documento (CC, CE, TI, etc.) |
| `numero_documento` | VARCHAR | Número de documento de identidad |
| `cargo` | VARCHAR | Cargo en la empresa |
| `area_dependencia` | VARCHAR | Área o dependencia donde trabaja |
| `email` | VARCHAR | Correo electrónico (usado para login y notificaciones) |
| `telefono` | VARCHAR | Número de teléfono |
| `representacion` | ENUM | `'empleador'` o `'trabajador'` |
| `tipo_miembro` | ENUM | `'principal'` o `'suplente'` |
| `rol_comite` | ENUM | `'presidente'`, `'secretario'`, o `'miembro'` |
| `puede_crear_actas` | TINYINT(1) | 1 = Puede crear actas, 0 = No puede |
| `puede_cerrar_actas` | TINYINT(1) | 1 = Puede cerrar/aprobar actas, 0 = No puede |
| `fecha_ingreso` | DATE | Fecha en que ingresó al comité |
| `fecha_retiro` | DATE (nullable) | Fecha en que fue retirado |
| `motivo_retiro` | TEXT (nullable) | Razón del retiro |
| `firma_imagen` | VARCHAR (nullable) | Ruta a imagen de firma |
| `estado` | ENUM | `'activo'` o `'retirado'` |
| `created_at` | DATETIME | Fecha de creación del registro |
| `updated_at` | DATETIME | Fecha de última actualización |

---

## Campos Clave Explicados

### `representacion`

Define a quién representa el miembro en el comité:
- **`empleador`**: Designado por la alta dirección/gerencia
- **`trabajador`**: Elegido por votación de los trabajadores

> **Normativa:** Según la Resolución 2013/1986 y Decreto 1072/2015, los comités paritarios (COPASST, COCOLAB) deben tener igual número de representantes del empleador y de los trabajadores.

### `tipo_miembro`

- **`principal`**: Miembro titular que participa activamente en reuniones
- **`suplente`**: Reemplaza al principal cuando este no puede asistir

> El quórum se calcula con base en los miembros **principales** activos.

### `rol_comite`

| Rol | Permisos por defecto | Descripción |
|-----|---------------------|-------------|
| `presidente` | `puede_crear_actas=1`, `puede_cerrar_actas=1` | Dirige las reuniones, firma actas |
| `secretario` | `puede_crear_actas=1`, `puede_cerrar_actas=1` | Elabora actas, gestiona documentación |
| `miembro` | Configurable | Participa y vota en reuniones |

### `id_responsable` (FK opcional)

Este campo permite vincular un miembro del comité con un registro existente en `tbl_cliente_responsables_sst`. Esto es útil cuando:
- El miembro ya está registrado como responsable SST (ej: Vigía, Coordinador Brigada)
- Se quiere mantener sincronizados los datos entre ambas tablas
- Se importan miembros desde el módulo de responsables

---

## Tablas Relacionadas

### `tbl_comites`

| Columna | Descripción |
|---------|-------------|
| `id_comite` | PK |
| `id_cliente` | FK a cliente |
| `id_tipo` | FK a tipo de comité |
| `fecha_conformacion` | Fecha de creación del comité |
| `fecha_vencimiento` | Fecha en que vence el período |
| `periodicidad_personalizada` | Días entre reuniones (sobrescribe el default) |
| `dia_reunion_preferido` | Día de la semana preferido |
| `hora_reunion_preferida` | Hora preferida |
| `lugar_habitual` | Lugar donde se reúnen normalmente |
| `estado` | `'activo'` o `'inactivo'` |

### `tbl_tipos_comite`

| Columna | Descripción |
|---------|-------------|
| `id_tipo` | PK |
| `codigo` | Código corto (COPASST, COCOLAB, VIGIA, BRIGADA) |
| `nombre` | Nombre completo del tipo de comité |
| `descripcion` | Descripción detallada |
| `periodicidad_dias` | Periodicidad default (ej: 30 días = mensual) |
| `dia_limite_mes` | Día límite del mes para reunión |
| `requiere_paridad` | 1 si requiere igual # empleador/trabajador |
| `requiere_quorum` | 1 si requiere quórum mínimo |
| `quorum_minimo_porcentaje` | Porcentaje mínimo para quórum |
| `vigencia_periodo_meses` | Duración del período del comité (ej: 24 meses) |

### `tbl_cliente_responsables_sst`

Tabla de responsables SST que puede servir como fuente de datos para miembros:

| Columna | Descripción |
|---------|-------------|
| `id_responsable` | PK |
| `id_cliente` | FK |
| `tipo_rol` | Rol específico (ej: `copasst_presidente`, `vigia_sst`) |
| `nombre_completo` | Nombre |
| `tipo_documento` | Tipo doc |
| `numero_documento` | Número doc |
| `cargo` | Cargo |
| `email` | Email |
| `telefono` | Teléfono |
| `licencia_sst_numero` | Para responsable SST |
| `activo` | Estado |

---

## Flujo de Datos

### 1. Creación de Miembro

```
Usuario (Consultor/Admin)
        │
        ▼
[Formulario nuevo_miembro]
        │
        ├──> Opción A: Seleccionar responsable existente
        │         │
        │         └──> Se copian datos de tbl_cliente_responsables_sst
        │               y se guarda id_responsable como FK
        │
        └──> Opción B: Ingresar datos manualmente
                  │
                  └──> Se guardan datos directamente
                        (id_responsable = NULL)
        │
        ▼
[ActasController::guardarMiembro()]
        │
        ├──> INSERT en tbl_comite_miembros
        │
        ├──> Si tiene email:
        │         │
        │         └──> Crear usuario en tbl_usuarios (tipo_usuario='miembro')
        │               │
        │               └──> Enviar credenciales por email (SendGrid)
        │
        └──> Redirect con mensaje de éxito
```

### 2. Edición de Miembro

```
[ActasController::editarMiembro($idComite, $idMiembro)]
        │
        ▼
[Formulario editar_miembro]
        │
        ▼
[ActasController::actualizarMiembro()]
        │
        └──> UPDATE en tbl_comite_miembros
             (No se modifica id_responsable ni usuario)
```

### 3. Retiro de Miembro

```
[ActasController::retirarMiembro($idMiembro)]
        │
        ├──> Recibe motivo_retiro via POST
        │
        └──> UPDATE tbl_comite_miembros SET
                estado = 'retirado',
                fecha_retiro = CURDATE(),
                motivo_retiro = :motivo
```

**Importante:** El retiro es un **soft delete**. El miembro no se elimina, se marca como `estado='retirado'`. Esto permite:
- Mantener historial de quién fue miembro
- Registrar la razón del retiro
- Cumplir con requisitos de trazabilidad documental

---

## Métodos del Modelo MiembroComiteModel

### Consultas de Miembros

| Método | Descripción |
|--------|-------------|
| `getActivosPorComite($idComite)` | Retorna miembros activos ordenados por tipo y rol |
| `getActivosPorTipo($idComite, $tipo)` | Miembros por tipo ('principal'/'suplente') |
| `getPresidente($idComite)` | Obtiene el presidente activo |
| `getSecretario($idComite)` | Obtiene el secretario activo |
| `getPuedenCrearActas($idComite)` | Miembros con permiso de crear actas |
| `getPuedenCerrarActas($idComite)` | Miembros con permiso de cerrar actas |
| `getByRepresentacion($idComite, $rep)` | Por representación (empleador/trabajador) |

### Conteos y Validaciones

| Método | Descripción |
|--------|-------------|
| `contarActivos($idComite)` | Total de miembros activos |
| `contarPorTipo($idComite, $tipo)` | Cuenta principales o suplentes |
| `calcularQuorumRequerido($idComite)` | Retorna: floor(principales/2) + 1 |
| `verificarParidad($idComite)` | Verifica igual # empleador/trabajador |

### Acciones

| Método | Descripción |
|--------|-------------|
| `retirarMiembro($id, $motivo)` | Cambia estado a 'retirado' con fecha y motivo |

---

## Reemplazo de Miembros

### Proceso Actual

El sistema **NO reemplaza automáticamente** un miembro por otro. El flujo es:

1. **Retirar miembro existente:**
   - El consultor usa el botón "Retirar"
   - Ingresa el motivo (renuncia, terminación contrato, etc.)
   - El miembro queda con `estado='retirado'`

2. **Agregar nuevo miembro:**
   - El consultor agrega un nuevo miembro
   - Puede asignarle el mismo rol/representación que el anterior
   - El nuevo miembro empieza con `estado='activo'`

### Escenarios de Reemplazo

| Escenario | Acción |
|-----------|--------|
| Miembro principal renuncia | Retirar principal → El suplente puede asumir funciones temporalmente → Agregar nuevo principal |
| Cambio de presidente | Retirar presidente actual → Agregar nuevo miembro con `rol_comite='presidente'` |
| Vencimiento de período | Crear nuevo comité con nueva fecha → Migrar o agregar miembros |
| Actualización de datos | Editar miembro existente (no es reemplazo) |

### Historial de Miembros

Para ver el historial de todos los miembros (activos y retirados) de un comité:

```sql
SELECT *
FROM tbl_comite_miembros
WHERE id_comite = :id_comite
ORDER BY estado ASC, fecha_retiro DESC, fecha_ingreso ASC;
```

---

## Integración con Usuarios (tbl_usuarios)

Cuando se crea un miembro con email, el sistema:

1. Verifica si ya existe usuario con ese email
2. Si no existe:
   - Crea usuario con `tipo_usuario='miembro'`
   - Genera password seguro aleatorio
   - Envía credenciales por email (SendGrid)
3. Si ya existe: solo muestra mensaje informativo

### Estructura Usuario Miembro

```php
$datosUsuario = [
    'email' => $miembro['email'],
    'password' => $passwordGenerado,
    'nombre_completo' => $miembro['nombre_completo'],
    'tipo_usuario' => 'miembro',
    'id_entidad' => $comite['id_cliente'],
    'estado' => 'activo'
];
```

---

## Validaciones de Negocio

### Paridad (COPASST, COCOLAB)

```php
// En MiembroComiteModel::verificarParidad()
$empleador = count($this->getByRepresentacion($idComite, 'empleador'));
$trabajador = count($this->getByRepresentacion($idComite, 'trabajador'));

return [
    'empleador' => $empleador,
    'trabajador' => $trabajador,
    'hay_paridad' => $empleador === $trabajador,
    'diferencia' => abs($empleador - $trabajador)
];
```

### Quórum

```php
// En MiembroComiteModel::calcularQuorumRequerido()
$principales = $this->contarPorTipo($idComite, 'principal');
return (int) floor($principales / 2) + 1;
```

**Ejemplo:** Si hay 4 miembros principales, el quórum requerido es 3 (4/2 + 1 = 3).

---

## Consultas SQL Útiles

```sql
-- Miembros activos de un comité
SELECT m.*, c.id_cliente
FROM tbl_comite_miembros m
JOIN tbl_comites c ON c.id_comite = m.id_comite
WHERE m.id_comite = 5 AND m.estado = 'activo'
ORDER BY m.tipo_miembro, m.rol_comite;

-- Historial completo (incluye retirados)
SELECT m.*, c.id_cliente,
       CASE WHEN m.estado = 'retirado' THEN CONCAT('Retirado: ', m.motivo_retiro) ELSE 'Activo' END as status
FROM tbl_comite_miembros m
JOIN tbl_comites c ON c.id_comite = m.id_comite
WHERE m.id_comite = 5
ORDER BY m.estado, m.fecha_retiro DESC;

-- Verificar paridad de un comité
SELECT
    representacion,
    COUNT(*) as total
FROM tbl_comite_miembros
WHERE id_comite = 5 AND estado = 'activo'
GROUP BY representacion;

-- Miembros que pueden crear actas
SELECT * FROM tbl_comite_miembros
WHERE id_comite = 5 AND estado = 'activo' AND puede_crear_actas = 1;

-- Contar por tipo de miembro
SELECT
    tipo_miembro,
    COUNT(*) as total
FROM tbl_comite_miembros
WHERE id_comite = 5 AND estado = 'activo'
GROUP BY tipo_miembro;
```

---

## Rutas Relacionadas

| Ruta | Método | Controlador |
|------|--------|-------------|
| `GET /actas/{idCliente}/comite/{idComite}/miembro/nuevo` | nuevoMiembro | ActasController |
| `POST /actas/{idCliente}/comite/{idComite}/miembro/guardar` | guardarMiembro | ActasController |
| `GET /actas/{idCliente}/comite/{idComite}/miembro/{idMiembro}/editar` | editarMiembro | ActasController |
| `POST /actas/{idCliente}/comite/{idComite}/miembro/{idMiembro}/actualizar` | actualizarMiembro | ActasController |
| `POST /actas/miembro/{idMiembro}/retirar` | retirarMiembro | ActasController |
| `POST /actas/miembro/{idMiembro}/reenviar-acceso` | reenviarAccesoMiembro | ActasController |

---

## Vistas Relacionadas

| Vista | Descripción |
|-------|-------------|
| `app/Views/actas/nuevo_miembro.php` | Formulario para agregar miembro |
| `app/Views/actas/editar_miembro.php` | Formulario para editar miembro |
| `app/Views/actas/comite.php` | Lista de miembros del comité |

---

## Notas Importantes

1. **Soft Delete:** Los miembros nunca se eliminan físicamente, se marcan como `estado='retirado'`

2. **Desnormalización:** `id_cliente` se guarda tanto en `tbl_comites` como en `tbl_comite_miembros` para optimizar consultas

3. **Permisos automáticos:** Presidente y secretario siempre tienen `puede_crear_actas=1` y `puede_cerrar_actas=1`

4. **Email único:** El email del miembro se usa para crear su usuario en el sistema

5. **FK opcional:** `id_responsable` es opcional y permite vincular con responsables SST existentes

6. **Historial completo:** El sistema mantiene trazabilidad de todos los miembros que han pertenecido al comité
