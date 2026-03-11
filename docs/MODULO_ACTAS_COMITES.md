# Modulo de Actas de Comites SST

## Arquitectura General

El modulo maneja actas de reuniones para 3 tipos de comites:
- **COPASST** (periodicidad mensual - 30 dias)
- **COCOLAB** (periodicidad trimestral - 90 dias)
- **BRIGADA**

### Tablas Principales

| Tabla | Descripcion |
|-------|-------------|
| `tbl_comites` | Comites activos por cliente (id_cliente, id_tipo, created_at) |
| `tbl_tipos_comite` | Tipos de comite con periodicidad_dias |
| `tbl_comite_miembros` | Miembros inscritos en cada comite |
| `tbl_actas` | Actas de reuniones (estado, fecha, orden del dia, desarrollo) |
| `tbl_acta_asistentes` | Asistentes de cada acta + firma electronica |
| `tbl_acta_compromisos` | Compromisos/tareas asignados en cada acta |
| `tbl_cliente_responsables_sst` | Responsables SST del cliente (incluye asesor_sst_externo) |

### Estados de un Acta

```
borrador → en_edicion → pendiente_firma → firmada
```

### Tipos de Usuario y Acceso

| Tipo | Controller | Vista Base | Acceso |
|------|-----------|------------|--------|
| Consultor SST | `ActasController` | `actas/` | Todos los clientes, CRUD completo |
| Miembro (con login) | `MiembroAuthController` | `actas/miembro_auth/` | Solo sus comites, crear/cerrar si tiene permiso |
| Miembro (con token) | `MiembroComiteController` | `actas/miembro/` | Solo sus comites via token en URL |

---

## Consultor SST Cycloid Talent (Asesor Externo)

### Concepto

El consultor SST de Cycloid Talent asiste como asesor tecnico a las reuniones de los comites de sus clientes. A diferencia de los miembros regulares:

- **NO se inserta en `tbl_comite_miembros`** (no es miembro de un comite especifico)
- **Se registra en `tbl_cliente_responsables_sst`** con `tipo_rol = 'asesor_sst_externo'`
- **Es transversal**: aparece automaticamente en TODOS los comites del cliente donde esta registrado
- **Firma las actas** igual que cualquier otro asistente

### Flujo Tecnico

#### 1. Registro del Asesor

Se registra en `tbl_cliente_responsables_sst` con:
```sql
tipo_rol = 'asesor_sst_externo'
activo = 1
id_cliente = [cliente donde asesora]
```

El ENUM `tipo_rol` incluye `'asesor_sst_externo'` (migrado en `fix_enum_asesor_externo.php`).

**No se crea usuario** para el asesor (logica en `ResponsablesSSTController::sincronizarMiembroComite()`).

#### 2. Inyeccion Dinamica en Miembros

`MiembroComiteModel::getActivosPorComite($idComite)` inyecta asesores al final de la lista:

```php
// Consulta los miembros reales del comite
$miembros = $this->where('id_comite', $idComite)->where('estado', 'activo')->findAll();

// Busca asesores del cliente en tbl_cliente_responsables_sst
$asesores = $db->table('tbl_cliente_responsables_sst')
    ->where('id_cliente', $comite['id_cliente'])
    ->where('tipo_rol', 'asesor_sst_externo')
    ->where('activo', 1)->get()->getResultArray();

// Los agrega como miembros virtuales
foreach ($asesores as $asesor) {
    $miembros[] = [
        'id_miembro' => 'asesor_' . $asesor['id_responsable'],  // ID virtual (string)
        'tipo_miembro' => 'asesor',
        'rol_comite' => 'asesor',
        'es_asesor_externo' => true,
        // ... datos del asesor
    ];
}
```

**Clave**: El `id_miembro` es un string `'asesor_X'` (no un INT). Esto permite identificarlo en formularios sin colisionar con IDs reales.

#### 3. Formulario Nueva Acta

Las vistas `nueva_acta.php` (consultor y miembro_auth) filtran y muestran asesores:

```php
$asesores = array_filter($miembros, fn($m) => ($m['tipo_miembro'] ?? '') === 'asesor');
```

Se muestran en seccion aparte "Consultor SST" con checkbox marcado por defecto.

#### 4. Guardar Acta con Asesor

Al guardar, los controllers detectan IDs que empiezan con `'asesor_'`:

**ActasController** (consultor):
```php
// agregarDesdeMiembros() SALTA asesores (es_asesor_externo = true)
$this->asistentesModel->agregarDesdeMiembros($idActa, $idComite);

// Luego agrega asesores marcados individualmente
foreach ($asistio as $idMiembro) {
    if (str_starts_with($idMiembro, 'asesor_')) {
        $idResponsable = (int) str_replace('asesor_', '', $idMiembro);
        $this->asistentesModel->agregarAsesorExterno($idActa, $idResponsable);
    }
}
```

**MiembroAuthController** (miembro):
```php
foreach ($asistentes as $idMiembro) {
    if (str_starts_with($idMiembro, 'asesor_')) {
        if (isset($asistio[$idMiembro])) {
            $this->asistentesModel->agregarAsesorExterno($idActa, $idResponsable);
        }
        continue;
    }
    // ... logica normal para miembros
}
```

#### 5. Almacenamiento en tbl_acta_asistentes

El asesor se guarda como fila normal con:

| Campo | Valor |
|-------|-------|
| `id_miembro` | `NULL` (no es miembro de tbl_comite_miembros) |
| `tipo_asistente` | `'asesor'` (ENUM agregado en `fix_enum_tipo_asistente_asesor.php`) |
| `nombre_completo` | Desde `tbl_cliente_responsables_sst` |
| `cargo` | Desde `tbl_cliente_responsables_sst` (fallback: 'Consultor SST') |
| `email` | Desde `tbl_cliente_responsables_sst` |
| `asistio` | `1` (siempre, solo se agrega si fue marcado) |
| `estado_firma` | `'pendiente'` |

#### 6. Firma Electronica

Una vez en `tbl_acta_asistentes`, el asesor entra en el flujo normal de firma:

1. `cerrarYEnviarAFirmas()` → `generarTokensFirma()` genera token para todos los `getPresentes()`
2. `enviarAFirmas()` envia email con enlace de firma via SendGrid
3. El asesor abre el enlace, ve la vista publica `actas/publico/firmar.php`, dibuja su firma
4. La firma se guarda en `firma_imagen` (base64)

**No hay tratamiento especial** — el asesor firma exactamente igual que cualquier miembro.

#### 7. Visualizacion

En todas las vistas que muestran asistentes, se detecta `tipo_asistente === 'asesor'`:

- **Badge**: `<span class="badge bg-info">Consultor SST</span>`
- **Representacion** (PDF/Word): "Consultor SST Cycloid Talent"
- **Rol** (PDF/Word): "Asesor"

Vistas actualizadas:
- `ver_acta.php`, `miembro_auth/ver_acta.php`, `miembro/ver_acta.php`, `publico/ver_acta.php`
- `estado_firmas.php`
- `pdf_acta.php`, `word_acta.php`
- `editar_acta.php` (matching por email para check de asistencia)

---

## Cumplimiento de Actas

### Formula

```
cumplimiento = (periodos_con_acta / periodos_esperados) * 100
```

- **periodos_esperados**: Se calcula desde `tbl_comites.created_at` (no desde enero)
- **periodicidad**: Viene de `tbl_tipos_comite.periodicidad_dias` (30 = mensual, 90 = trimestral)
- Solo cuenta periodos hasta el mes/trimestre actual (no cuenta periodos futuros)

### Metodos en ActaModel

```php
getEstadisticas($idComite, $anio)  // Retorna stats + cumplimiento + periodos_esperados
calcularPeriodosEsperados($comiteInfo, $anio)  // Desde created_at hasta hoy
contarPeriodosConActa($mesesConActa, $comiteInfo, $anio)  // Actas en periodos validos
```

### Visualizacion

Se muestra como:
```
85%
3 de 4 posibles en 2026
```

En las vistas: `actas/index.php` (dashboard comites), `actas/comite.php` (detalle comite), `miembro_auth/comite.php` (portal miembro).

---

## Compromisos

### Flujo

1. Se crean durante la reunion (formulario nueva_acta o editar_acta)
2. Cada compromiso tiene: descripcion, responsable, fecha_vencimiento, estado
3. Estados: `pendiente` → `en_proceso` → `cumplido` | `vencido`
4. Se muestran en la siguiente reunion como "Compromisos Pendientes"

### Vistas de Compromisos

- **Consultor**: `actas/{idCliente}/comite/{idComite}/compromisos` → `ActasController::compromisosComite()`
- **Miembro auth**: `miembro/comite/{idComite}/compromisos` → `MiembroAuthController::compromisosComite()`

---

## Migraciones SQL Ejecutadas

| Script | Descripcion | Ejecutado |
|--------|-------------|-----------|
| `fix_enum_asesor_externo.php` | Agregar `asesor_sst_externo` al ENUM tipo_rol en tbl_cliente_responsables_sst + recrear vista | Local + Produccion |
| `fix_enum_tipo_asistente_asesor.php` | Agregar `asesor` al ENUM tipo_asistente en tbl_acta_asistentes | Local + Produccion |

---

## Archivos Clave del Modulo

### Models
- `ActaModel.php` - CRUD actas + estadisticas/cumplimiento + cerrarYEnviarAFirmas
- `ActaAsistenteModel.php` - Asistentes + agregarDesdeMiembros + agregarAsesorExterno + tokens firma
- `ActaCompromisoModel.php` - CRUD compromisos
- `MiembroComiteModel.php` - Miembros + getActivosPorComite (inyecta asesores)
- `ResponsableSSTModel.php` - Responsables SST (incluye asesor_sst_externo)

### Controllers
- `ActasController.php` - Vista consultor (CRUD completo)
- `MiembroAuthController.php` - Vista miembro con login
- `MiembroComiteController.php` - Vista miembro con token
- `ResponsablesSSTController.php` - CRUD responsables (skip sync para asesor)

### Views
- `actas/nueva_acta.php` - Formulario crear acta (consultor)
- `actas/editar_acta.php` - Editar desarrollo de acta
- `actas/ver_acta.php` - Ver acta (solo lectura)
- `actas/pdf_acta.php` - Template PDF
- `actas/word_acta.php` - Template Word
- `actas/estado_firmas.php` - Dashboard estado de firmas
- `actas/publico/firmar.php` - Vista publica para firmar
- `actas/miembro_auth/nueva_acta.php` - Crear acta (miembro)
- `actas/miembro_auth/comite.php` - Dashboard comite (miembro)
- `actas/miembro_auth/compromisos_comite.php` - Compromisos (miembro)
