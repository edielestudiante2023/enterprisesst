# Acceso al Modulo de Actas desde Dashboards

## Fecha: 2026-02-03

## Objetivo
Dar acceso al modulo de Actas (`/actas/{id_cliente}`) desde los dashboards del consultor y superadmin, permitiendo seleccionar un cliente mediante un buscador Select2.

---

## Problema Inicial

El modulo de Actas existia en la ruta `/actas/{id_cliente}` pero:
1. No habia un enlace visible en los dashboards
2. Las rutas no estaban protegidas por el filtro de autenticacion
3. Los consultores y admins no tenian forma de acceder facilmente

---

## Solucion Implementada

### 1. Proteccion de Rutas (Filters.php)

**Archivo:** `app/Config/Filters.php`

Se agrego `'actas/*'` al filtro de autenticacion:

```php
public array $filters = [
    'auth' => [
        'before' => [
            // ... otras rutas ...
            'actas/*',  // <- Agregado
        ],
    ],
];
```

**Leccion aprendida:** Siempre verificar que las nuevas rutas esten protegidas por los filtros de autenticacion apropiados.

---

### 2. Nueva Ruta para Dashboard de Actas (Routes.php)

**Archivo:** `app/Config/Routes.php`

Se agrego una ruta sin parametro para el dashboard general:

```php
// Dashboard general de actas (todos los clientes)
$routes->get('/actas', 'ActasController::dashboard');

// Dashboard de comites por cliente (existente)
$routes->get('/actas/(:num)', 'ActasController::index/$1');
```

**Leccion aprendida:** El orden de las rutas importa. La ruta sin parametro debe ir ANTES de la ruta con parametro para que se capture correctamente.

---

### 3. Modificacion del Controlador del Consultor

**Archivo:** `app/Controllers/ConsultantController.php`

#### Cambios realizados:

1. Agregar el modelo `DashboardItemModel`:
```php
use App\Models\DashboardItemModel;
```

2. Modificar el metodo `index()` para pasar clientes e items:
```php
public function index()
{
    $clientModel = new ClientModel();
    $dashboardItemModel = new DashboardItemModel();

    // Obtener todos los clientes activos
    $clientes = $clientModel->where('estado', 'activo')->findAll();

    // Obtener items del dashboard (sin filtrar)
    $items = $dashboardItemModel->findAll();

    return view('consultant/dashboard', [
        'clientes' => $clientes,
        'items' => $items
    ]);
}
```

#### Errores encontrados y soluciones:

| Error | Causa | Solucion |
|-------|-------|----------|
| Tabla vacia (0 registros) | No se pasaba `$items` a la vista | Agregar `$items` al array de datos |
| Solo 3 clientes en lugar de 7 | Filtro por `id_consultor` incorrecto | Quitar filtro, mostrar todos los clientes |
| 0 clientes encontrados | `'Activo'` vs `'activo'` (mayusculas) | Usar `'activo'` en minuscula |
| Filtro de items no funcionaba | `rol = 'consultant'` no existe | Quitar filtro, usar `findAll()` |

**Leccion aprendida:** Siempre verificar:
- La capitalizacion de los valores en la base de datos (`activo` vs `Activo`)
- Los valores reales de los campos (`Administrador` vs `consultant`)
- Que se pasen TODOS los datos que la vista necesita

---

### 4. Modificacion del CustomDashboardController (Admin)

**Archivo:** `app/Controllers/CustomDashboardController.php`

```php
use App\Models\ClientModel;

public function index()
{
    // ... codigo existente ...

    // Agregar clientes para el selector
    $clientModel = new ClientModel();
    $data['clientes'] = $clientModel->where('estado', 'activo')->findAll();

    return view('consultant/admindashboard', $data);
}
```

---

### 5. Modificacion de las Vistas

#### Dashboard del Consultor (`app/Views/consultant/dashboard.php`)

**CSS agregado:**
```html
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
```

**HTML del selector:**
```html
<!-- Modulo de Comites y Actas -->
<div class="mb-5">
    <h4 class="text-center mb-4">
        <i class="fas fa-users me-2"></i>Gestion de Comites y Actas
    </h4>
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                    <label class="text-white fw-bold mb-2">
                        <i class="fas fa-building me-2"></i>Seleccione un Cliente
                    </label>
                    <select id="selectClienteActas" class="form-select" style="width: 100%;">
                        <option value="">-- Buscar cliente --</option>
                        <?php foreach ($clientes ?? [] as $cliente): ?>
                            <option value="<?= esc($cliente['id_cliente']) ?>">
                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btnIrActas" class="btn btn-light w-100 py-2 fw-bold mt-3" disabled>
                        <i class="fas fa-clipboard-list me-2"></i>Ir a Gestion de Actas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

**JavaScript:**
```html
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('#selectClienteActas').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Buscar cliente por nombre o NIT --',
        allowClear: true,
        width: '100%'
    });

    // Habilitar/deshabilitar boton
    $('#selectClienteActas').on('change', function() {
        $('#btnIrActas').prop('disabled', !$(this).val());
    });

    // Abrir en nueva pestana
    $('#btnIrActas').on('click', function() {
        var clienteId = $('#selectClienteActas').val();
        if (clienteId) {
            window.open('<?= base_url('actas/') ?>' + clienteId, '_blank');
        }
    });
});
</script>
```

---

## Archivos Modificados (Resumen)

| Archivo | Tipo de Cambio |
|---------|----------------|
| `app/Config/Filters.php` | Agregar `actas/*` al filtro auth |
| `app/Config/Routes.php` | Agregar ruta `/actas` |
| `app/Controllers/ConsultantController.php` | Pasar `$clientes` e `$items` |
| `app/Controllers/CustomDashboardController.php` | Pasar `$clientes` |
| `app/Controllers/ActasController.php` | Agregar metodo `dashboard()` |
| `app/Views/consultant/dashboard.php` | Agregar Select2 y seccion de actas |
| `app/Views/consultant/admindashboard.php` | Agregar Select2 y seccion de actas |
| `app/Views/consultant/list_clients.php` | Agregar boton de actas en tabla |
| `app/Views/actas/dashboard.php` | Nueva vista (opcional) |

---

## Lecciones Aprendidas

### 1. Consistencia de Datos
- **Problema:** `'Activo'` vs `'activo'` en la base de datos
- **Solucion:** Verificar siempre los valores exactos con una consulta SQL directa
- **Comando util:** `mysql -u root database -e "SELECT DISTINCT campo FROM tabla;"`

### 2. Variables de Sesion
- **Problema:** Usar `id_usuario` cuando debia ser `user_id`
- **Leccion:** Revisar el AuthController para entender que variables se guardan en sesion
- **Campos de sesion para consultores:**
  - `user_id` = id_consultor (para filtrar clientes)
  - `id_usuario` = id en tabla usuarios
  - `role` = 'consultant' o 'admin'

### 3. Datos en Vistas
- **Problema:** La tabla dejo de funcionar porque no se pasaba `$items`
- **Leccion:** Al modificar un controlador, revisar TODO lo que la vista espera recibir
- **Practica:** Usar `$variable ?? []` en las vistas para evitar errores si falta la variable

### 4. Filtros de Datos
- **Problema:** Filtrar por `rol = 'consultant'` cuando solo existe `rol = 'Administrador'`
- **Leccion:** Verificar los valores reales en la base de datos antes de agregar filtros
- **Practica:** Probar queries en MySQL antes de implementar en codigo

### 5. Select2 Integration
- **Requisitos:**
  - CSS de Select2 y tema Bootstrap 5
  - JS de Select2 (despues de jQuery)
  - Inicializacion en `$(document).ready()`
- **Funcionalidades utiles:**
  - `allowClear: true` - permite limpiar seleccion
  - `placeholder` - texto cuando no hay seleccion
  - `theme: 'bootstrap-5'` - estilo consistente

---

## Consultas SQL Utiles para Debugging

```sql
-- Ver clientes activos
SELECT id_cliente, id_consultor, nombre_cliente, estado
FROM tbl_clientes WHERE estado = 'activo';

-- Ver valores unicos de estado
SELECT DISTINCT estado FROM tbl_clientes;

-- Ver items del dashboard
SELECT id, rol, tipo_proceso FROM dashboard_items;

-- Ver datos de sesion del usuario
SELECT id_usuario, email, tipo_usuario, id_entidad
FROM tbl_usuarios WHERE email LIKE '%nombre%';
```

---

## Resultado Final

- Consultores y admins pueden acceder al modulo de actas desde sus dashboards
- Selector con buscador Select2 permite encontrar clientes por nombre o NIT
- Boton abre la pagina de actas en una nueva pestana
- Tambien se agrego acceso desde la lista de clientes (boton azul en cada fila)
