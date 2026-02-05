# Modulo de Indicadores del SG-SST

## Fecha: 2026-02-04

## Objetivo
Documentar el modulo de Indicadores del SG-SST, sus multiples puntos de entrada, vistas disponibles y como todos alimentan una unica tabla en la base de datos.

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                    tbl_indicadores_sst                          │
│                    (Tabla unica en BD)                          │
└─────────────────────────────────────────────────────────────────┘
                              ▲
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌───────────────┐   ┌─────────────────┐   ┌─────────────────────┐
│ /indicadores- │   │ /induccion-     │   │ /generador-ia/      │
│ sst/{id}      │   │ etapas/{id}/    │   │ {id}/generar-       │
│               │   │ generar-        │   │ indicadores         │
│ CRUD completo │   │ indicadores     │   │                     │
│ de indicadores│   │                 │   │ Indicadores de      │
└───────────────┘   │ Indicadores de  │   │ capacitacion        │
        │           │ INDUCCION       │   │ segun estandares    │
        │           └─────────────────┘   └─────────────────────┘
        │                   │                       │
        │                   ▼                       ▼
        │           categoria='induccion'   categoria='capacitacion'
        │
        ▼
  Todas las categorias
```

**Principio clave:** Multiples puntos de entrada, una sola tabla de destino.

---

## Tabla de Base de Datos

**Tabla:** `tbl_indicadores_sst`

**Modelo:** `app/Models/IndicadorSSTModel.php`

```php
protected $table = 'tbl_indicadores_sst';
protected $primaryKey = 'id_indicador';
protected $allowedFields = [
    'id_cliente', 'id_actividad_pta', 'nombre_indicador', 'tipo_indicador',
    'categoria', 'formula', 'meta', 'unidad_medida', 'periodicidad',
    'numeral_resolucion', 'phva', 'valor_numerador', 'valor_denominador',
    'valor_resultado', 'fecha_medicion', 'cumple_meta', 'observaciones',
    'acciones_mejora', 'activo', 'created_by', 'updated_by'
];
```

**Tabla de historico:** `tbl_indicadores_sst_mediciones`

---

## Categorias de Indicadores

| Categoria | Nombre | Origen Tipico |
|-----------|--------|---------------|
| `capacitacion` | Capacitacion | Generador IA |
| `induccion` | Induccion | Modulo Etapas Induccion |
| `accidentalidad` | Accidentalidad | Manual |
| `ausentismo` | Ausentismo | Manual |
| `pta` | Plan de Trabajo Anual | Manual |
| `inspecciones` | Inspecciones | Manual |
| `emergencias` | Emergencias | Manual |
| `vigilancia` | Vigilancia Epidemiologica | Manual |
| `riesgos` | Gestion de Riesgos | Manual |
| `otro` | Otros | Manual |

---

## Rutas del Sistema (Routes.php)

**Archivo:** `app/Config/Routes.php`

### Modulo Principal de Indicadores SST

```php
// Indicadores del SG-SST
$routes->get('/indicadores-sst/(:num)', 'IndicadoresSSTController::index/$1');
$routes->get('/indicadores-sst/(:num)/crear', 'IndicadoresSSTController::crear/$1');
$routes->get('/indicadores-sst/(:num)/editar/(:num)', 'IndicadoresSSTController::editar/$1/$2');
$routes->post('/indicadores-sst/(:num)/guardar', 'IndicadoresSSTController::guardar/$1');
$routes->post('/indicadores-sst/(:num)/medir/(:num)', 'IndicadoresSSTController::registrarMedicion/$1/$2');
$routes->post('/indicadores-sst/(:num)/eliminar/(:num)', 'IndicadoresSSTController::eliminar/$1/$2');
$routes->post('/indicadores-sst/(:num)/generar-sugeridos', 'IndicadoresSSTController::generarSugeridos/$1');
$routes->get('/indicadores-sst/(:num)/api', 'IndicadoresSSTController::apiObtener/$1');
$routes->get('/indicadores-sst/(:num)/verificar', 'IndicadoresSSTController::apiVerificar/$1');
$routes->get('/indicadores-sst/historico/(:num)', 'IndicadoresSSTController::apiHistorico/$1');
```

### Generador IA - Indicadores

```php
$routes->get('/generador-ia/(:num)/preview-indicadores', 'GeneradorIAController::previewIndicadores/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores', 'GeneradorIAController::generarIndicadores/$1');
```

### Modulo Induccion - Indicadores

```php
$routes->get('/induccion-etapas/(:num)/generar-indicadores', 'InduccionEtapasController::generarIndicadores/$1');
$routes->post('/induccion-etapas/(:num)/enviar-indicadores', 'InduccionEtapasController::enviarIndicadores/$1');
```

---

## Vistas Disponibles

### 1. Modulo Principal de Indicadores SST

**Controlador:** `app/Controllers/IndicadoresSSTController.php`

| Vista | Ruta | Descripcion |
|-------|------|-------------|
| `indicadores_sst/index.php` | `/indicadores-sst/{idCliente}` | Dashboard de indicadores con categorias, mediciones y graficos |
| `indicadores_sst/formulario.php` | `/indicadores-sst/{idCliente}/crear` | Formulario para crear/editar indicador |
| `indicadores_sst/formulario.php` | `/indicadores-sst/{idCliente}/editar/{id}` | Mismo formulario en modo edicion |

**Archivo:** `app/Views/indicadores_sst/index.php`

**Funcionalidades:**
- Ver indicadores agrupados por categoria
- Filtrar por categoria
- Registrar mediciones (modal)
- Ver cumplimiento de metas
- Generar indicadores sugeridos automaticamente
- Eliminar indicadores

**Archivo:** `app/Views/indicadores_sst/formulario.php`

**Funcionalidades:**
- Crear nuevo indicador
- Editar indicador existente
- Ver historico de mediciones (en modo edicion)
- Campos: nombre, tipo, categoria, formula, meta, unidad, periodicidad, numeral, PHVA

---

### 2. Generador IA - Indicadores de Capacitacion

**Controlador:** `app/Controllers/GeneradorIAController.php`

| Vista | Ruta | Descripcion |
|-------|------|-------------|
| `generador_ia/index.php` | `/generador-ia/{idCliente}` | Dashboard del flujo IA (Paso 3: Indicadores) |

**Archivo:** `app/Views/generador_ia/index.php`

**Ubicacion en el flujo:**
```
1. Cronograma de Capacitaciones
2. Plan de Trabajo Anual (PTA)
3. Indicadores del SG-SST  ← AQUI
4. Programa de Capacitacion (documento final)
```

**Funcionalidades:**
- Ver estado actual de indicadores
- Generar indicadores sugeridos segun estandares (7, 21 o 60)
- Enlace a modulo principal de indicadores

**Limites por nivel de estandares:**
| Estandares | Max Indicadores |
|------------|-----------------|
| 7 (Microempresa) | 2 |
| 21 (Pequena empresa) | 3 |
| 60 (>50 trabajadores) | 4 |

---

### 3. Modulo Induccion - Indicadores de Induccion

**Controlador:** `app/Controllers/InduccionEtapasController.php`

**Servicio:** `app/Services/InduccionEtapasService.php`

| Vista | Ruta | Descripcion |
|-------|------|-------------|
| `induccion_etapas/generar_indicadores.php` | `/induccion-etapas/{idCliente}/generar-indicadores` | Propuesta de indicadores de induccion |

**Archivo:** `app/Views/induccion_etapas/generar_indicadores.php`

**Ubicacion en el flujo de Induccion:**
```
1. Ver etapas           → /induccion-etapas/{idCliente}
2. Generar etapas       → /induccion-etapas/{idCliente}/generar
3. Aprobar etapas       → POST aprobar
4. Generar PTA          → /induccion-etapas/{idCliente}/generar-pta
5. Enviar al PTA        → POST enviar-pta
6. Generar Indicadores  → /induccion-etapas/{idCliente}/generar-indicadores  ← AQUI
7. Enviar Indicadores   → POST enviar-indicadores → Redirige a carpeta 1.2.2
```

**Funcionalidades:**
- Ver propuesta de indicadores especificos de induccion
- Seleccionar cuales incluir (checkboxes)
- Editar formula, meta, periodicidad antes de enviar
- Enviar indicadores seleccionados al modulo principal

**Indicadores propuestos tipicos:**
- Cobertura de Induccion
- Cumplimiento del Programa de Induccion
- Oportunidad en la Induccion

---

## Puntos de Acceso (Navegacion)

### Desde Dashboard de Documentacion

**Archivo:** `app/Views/documentacion/dashboard.php` (linea 365)

```php
<a href="<?= base_url('generador-ia/' . $cliente['id_cliente']) ?>" class="card ...">
    <i class="bi bi-robot text-purple fs-4"></i>
    <h6>Generador IA</h6>
    <small>Cronograma y PTA</small>
</a>
```

Flujo: `Dashboard Documentacion → Generador IA → Ver Indicadores`

---

### Desde Generador IA

**Archivo:** `app/Views/generador_ia/index.php` (linea 236)

```php
<a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" target="_blank"
   class="btn btn-outline-secondary btn-sm w-100">
    <i class="bi bi-eye me-1"></i>Ver Indicadores
</a>
```

---

### Desde Formulario de Contexto SST

**Archivo:** `app/Views/contexto/formulario.php` (linea 51)

```php
<a class="nav-link" href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>">
    Indicadores
</a>
```

---

### Desde PyP Salud (Generador IA)

**Archivo:** `app/Views/generador_ia/pyp_salud.php` (linea 259)

```php
<a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>"
   class="btn btn-sm btn-outline-primary">
    Ver Indicadores
</a>
```

---

## Acceso desde Dashboards de Consultor y Admin

### Dashboard del Consultor

**Archivo:** `app/Views/consultant/dashboard.php`

Se agrego una card con selector Select2 para acceder a Indicadores SST:

```html
<!-- Card: Indicadores SST -->
<div class="col-lg-5 col-md-6 mb-3">
    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
            <h5 class="text-white text-center mb-3">
                <i class="fas fa-chart-line me-2"></i>Indicadores del SG-SST
            </h5>
            <select id="selectClienteIndicadores" class="form-select">
                <!-- Opciones de clientes -->
            </select>
            <button type="button" id="btnIrIndicadores" class="btn btn-light w-100 py-2 fw-bold">
                <i class="fas fa-chart-line me-2"></i>Ir a Indicadores SST
            </button>
        </div>
    </div>
</div>
```

**JavaScript:**
```javascript
// Inicializar Select2
$('#selectClienteIndicadores').select2({
    theme: 'bootstrap-5',
    placeholder: '-- Buscar cliente por nombre o NIT --',
    allowClear: true,
    width: '100%'
});

// Habilitar/deshabilitar boton
$('#selectClienteIndicadores').on('change', function() {
    $('#btnIrIndicadores').prop('disabled', !$(this).val());
});

// Abrir en nueva pestana
$('#btnIrIndicadores').on('click', function() {
    var clienteId = $('#selectClienteIndicadores').val();
    if (clienteId) {
        window.open(base_url + 'indicadores-sst/' + clienteId, '_blank');
    }
});
```

---

### Dashboard del Admin

**Archivo:** `app/Views/consultant/admindashboard.php`

Se agrego la misma card con selector Select2 (identico al del consultor).

---

## Proteccion de Rutas (Filters.php)

**Archivo:** `app/Config/Filters.php`

**Estado actual:** Las rutas de indicadores ESTAN protegidas por el filtro `auth`.

```php
public array $filters = [
    'auth' => [
        'before' => [
            'dashboard',
            'dashboard/*',
            'admin/*',
            'consultor/*',
            'client/*',
            // ... otras rutas ...
            'actas/*',
            'comites-elecciones/*',
            'acciones-correctivas/*',
            'indicadores-sst/*',  // <- AGREGADO
        ],
    ],
];
```

**Proteccion:** Filtro `auth` + filtro `sessiontimeout` (global).

---

## Flujo de Datos

### Crear indicador manualmente

```
Usuario → /indicadores-sst/{id}/crear
       → IndicadoresSSTController::crear()
       → Vista formulario.php
       → POST /indicadores-sst/{id}/guardar
       → IndicadoresSSTController::guardar()
       → IndicadorSSTModel::insert()
       → tbl_indicadores_sst
```

### Crear indicadores desde Generador IA

```
Usuario → /generador-ia/{id}
       → Clic "Generar Indicadores"
       → POST /generador-ia/{id}/generar-indicadores
       → GeneradorIAController::generarIndicadores()
       → IndicadorSSTModel::crearIndicadoresSugeridos()
       → tbl_indicadores_sst (categoria='capacitacion')
```

### Crear indicadores desde Induccion

```
Usuario → /induccion-etapas/{id}/generar-indicadores
       → InduccionEtapasController::generarIndicadores()
       → InduccionEtapasService::prepararIndicadores()
       → Vista generar_indicadores.php
       → POST /induccion-etapas/{id}/enviar-indicadores
       → InduccionEtapasController::enviarIndicadores()
       → InduccionEtapasService::enviarIndicadores()
       → IndicadorSSTModel::insert()
       → tbl_indicadores_sst (categoria='induccion')
       → Redirige a carpeta 1.2.2
```

---

## Archivos del Modulo (Resumen)

| Archivo | Tipo | Descripcion |
|---------|------|-------------|
| `app/Models/IndicadorSSTModel.php` | Modelo | Modelo principal, constantes, metodos CRUD |
| `app/Controllers/IndicadoresSSTController.php` | Controlador | CRUD completo de indicadores |
| `app/Controllers/GeneradorIAController.php` | Controlador | Generacion con IA (metodos de indicadores) |
| `app/Controllers/InduccionEtapasController.php` | Controlador | Indicadores de induccion |
| `app/Services/InduccionEtapasService.php` | Servicio | Logica de negocio para induccion |
| `app/Views/indicadores_sst/index.php` | Vista | Dashboard principal de indicadores |
| `app/Views/indicadores_sst/formulario.php` | Vista | Crear/editar indicador |
| `app/Views/generador_ia/index.php` | Vista | Flujo IA (incluye paso de indicadores) |
| `app/Views/induccion_etapas/generar_indicadores.php` | Vista | Propuesta de indicadores de induccion |

---

## Constantes del Modelo

**Archivo:** `app/Models/IndicadorSSTModel.php`

### Tipos de Indicador (Res. 0312/2019)

```php
public const TIPOS_INDICADOR = [
    'estructura' => 'Indicador de Estructura',
    'proceso' => 'Indicador de Proceso',
    'resultado' => 'Indicador de Resultado'
];
```

### Periodicidades

```php
public const PERIODICIDADES = [
    'mensual' => 'Mensual',
    'trimestral' => 'Trimestral',
    'semestral' => 'Semestral',
    'anual' => 'Anual'
];
```

### Fases PHVA

```php
public const FASES_PHVA = [
    'planear' => 'PLANEAR',
    'hacer' => 'HACER',
    'verificar' => 'VERIFICAR',
    'actuar' => 'ACTUAR'
];
```

---

## Consultas SQL Utiles

```sql
-- Ver todos los indicadores de un cliente
SELECT * FROM tbl_indicadores_sst WHERE id_cliente = 18;

-- Ver indicadores por categoria
SELECT categoria, COUNT(*) as total
FROM tbl_indicadores_sst
WHERE id_cliente = 18
GROUP BY categoria;

-- Ver historico de mediciones
SELECT * FROM tbl_indicadores_sst_mediciones
WHERE id_indicador = 5
ORDER BY fecha_registro DESC;

-- Ver indicadores que no cumplen meta
SELECT nombre_indicador, valor_resultado, meta, cumple_meta
FROM tbl_indicadores_sst
WHERE id_cliente = 18 AND cumple_meta = 0;

-- Verificar categorias existentes
SELECT DISTINCT categoria FROM tbl_indicadores_sst;
```

---

## Resultado Final

- **Modulo centralizado:** Todos los indicadores van a `tbl_indicadores_sst`
- **Multiples origenes:** Se pueden crear desde 3 puntos diferentes
- **Diferenciacion por categoria:** Cada origen usa su propia categoria
- **Vista unificada:** `/indicadores-sst/{idCliente}` muestra TODOS los indicadores
- **Integracion con flujos:** Generador IA e Induccion usan indicadores como paso intermedio
