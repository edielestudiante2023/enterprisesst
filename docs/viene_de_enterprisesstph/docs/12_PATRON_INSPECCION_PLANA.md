# 12 - Patron: Inspeccion PLANA

## Resumen

Una sola tabla, un solo modelo, campos fijos en la migracion SQL. NO tiene tabla detalle ni filas dinamicas.

**Modulos que usan este patron:** Locativa (Fase 2), Botiquin (Fase 5), Comunicaciones (Fase 7), Recursos de Seguridad (Fase 8)

---

## Estructura de archivos

```
app/SQL/migrate_inspeccion_{modulo}.php                          — 1 tabla
app/Models/Inspeccion{Modulo}Model.php                           — 1 modelo
app/Controllers/Inspecciones/Inspeccion{Modulo}Controller.php    — controlador CRUD+PDF
app/Views/inspecciones/{modulo}/list.php                         — listado cards
app/Views/inspecciones/{modulo}/form.php                         — formulario plano
app/Views/inspecciones/{modulo}/view.php                         — vista read-only
app/Views/inspecciones/{modulo}/pdf.php                          — template DOMPDF
```

Total: 7 archivos nuevos + 3 archivos modificados (Routes.php, dashboard.php, InspeccionesController.php)

---

## Migracion SQL

Script CLI en `app/SQL/migrate_inspeccion_{modulo}.php` con soporte local + production (SSL).

```sql
CREATE TABLE IF NOT EXISTS tbl_inspeccion_{modulo} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Campos especificos del modulo (ejemplo: comunicaciones)
    cant_{recurso1} INT NOT NULL DEFAULT 0,
    obs_{recurso1} TEXT NULL,
    cant_{recurso2} INT NOT NULL DEFAULT 0,
    obs_{recurso2} TEXT NULL,
    -- ... N recursos

    -- Fotos evidencia (genéricas o por recurso)
    foto_1 VARCHAR(255) NULL,
    foto_2 VARCHAR(255) NULL,

    -- Resultado
    observaciones_finales TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_insp_{tag}_cliente FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente),
    CONSTRAINT fk_insp_{tag}_consultor FOREIGN KEY (id_consultor) REFERENCES tbl_consultor(id_consultor),
    INDEX idx_insp_{tag}_cliente (id_cliente),
    INDEX idx_insp_{tag}_consultor (id_consultor),
    INDEX idx_insp_{tag}_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Modelo

```php
class Inspeccion{Modulo}Model extends Model
{
    protected $table = 'tbl_inspeccion_{modulo}';
    protected $primaryKey = 'id';
    protected $allowedFields = [/* todos los campos */];
    protected $useTimestamps = true;

    // 4 metodos estandar:
    public function getByConsultor(int $idConsultor, ?string $estado = null)
    public function getPendientesByConsultor(int $idConsultor)  // estado = 'borrador'
    public function getAllPendientes()                           // estado = 'borrador'
    public function getByCliente(int $idCliente)
}
```

Todos hacen JOIN con `tbl_clientes` o `tbl_consultor` para traer `nombre_cliente` / `nombre_consultor`.

---

## Controlador

### Constante RECURSOS

Define la metadata para iterar en vistas y recoger datos del POST:

```php
public const RECURSOS = [
    'key_recurso' => [
        'label' => 'Nombre visible',
        'icon'  => 'fa-icono',
        'hint'  => 'Criterios entre parentesis',  // opcional
    ],
    // ... N recursos
];
```

### Metodos publicos (10)

| Metodo | Descripcion |
|--------|-------------|
| `list()` | Cards con filtro Select2. Admin ve todos, consultor solo los suyos |
| `create($idCliente?)` | Form plano con constante RECURSOS |
| `store()` | Validacion + insert + uploadFoto + redirect a edit |
| `edit($id)` | Si completo -> redirect a view. Carga datos |
| `update($id)` | Update + preservar fotos + check finalizar |
| `view($id)` | Read-only con cliente, consultor, constante RECURSOS |
| `finalizar($id)` | generarPdfInterno() + update estado=completo + uploadToReportes() |
| `generatePdf($id)` | Regenera PDF y sirve inline |
| `delete($id)` | Solo borradores. Elimina fotos + PDF |

### Metodos privados (4)

**`getInspeccionPostData()`**
Itera la constante para recoger campos dinamicamente:
```php
foreach (self::RECURSOS as $key => $info) {
    $data['obs_' . $key] = $this->request->getPost('obs_' . $key);
    // Si tiene cantidad: $data['cant_' . $key] = (int) $this->request->getPost('cant_' . $key);
}
```

**`uploadFoto($campo, $dir)`**
Patron estandar: getFile() -> isValid() -> getRandomName() -> move(). Retorna ruta relativa o null.

**`generarPdfInterno($id)`**
- DOMPDF portrait letter
- Logo cliente a base64
- Fotos a base64
- Dir: `uploads/inspecciones/{modulo}/pdfs/`
- Borra PDF anterior si existe

**`uploadToReportes($insp, $pdfPath)`**
- `id_report_type = 6`
- `id_detailreport = N` (ver tabla de fases)
- Tag: `insp_{tag}_id:{id}`
- Copia PDF a `uploads/{nit}/`
- Update si existe, insert si no

---

## Vistas

### list.php
- Cards con cliente, fecha, badge estado
- Filtro Select2 por cliente (AJAX `/inspecciones/api/clientes`)
- SweetAlert para confirmar eliminacion
- Botones: Editar/Eliminar (borrador) o Ver/PDF (completo)

### form.php
- Datos Generales: cliente (Select2 AJAX), fecha inspeccion
- Recursos: foreach constante RECURSOS -> card con icono + label + campos
- Fotos evidencia: input file (con preview si ya existe)
- Observaciones finales: textarea
- Botones: Guardar borrador + Finalizar (con confirm)
- **Autoguardado localStorage**: key `{modulo}_draft_{id|new}`, 30s + 2s debounce

### view.php
- Datos generales (cliente, fecha, consultor)
- Recursos con colores (cantidad 0=rojo, >0=verde; o solo texto)
- Fotos con modal ampliacion
- Observaciones finales
- Botones: Ver PDF / Editar

### pdf.php — Template DOMPDF
```
Header corporativo (logo + SG-SST + codigo FT-SST-XXX + version + fecha)
Titulo principal
Introduccion (texto del z_{modulo}.txt resumido)
Tabla datos de la inspeccion
Tabla/seccion de recursos inspeccionados
Fotos evidencia (base64)
Observaciones finales
```
CSS: sin flexbox/grid/calc/variables. Tablas para layout. px units. Base64 images.

---

## Integracion Dashboard + Rutas

### InspeccionesController.php
```php
use App\Models\Inspeccion{Modulo}Model;
// ... en dashboard():
$model = new Inspeccion{Modulo}Model();
$total{Modulo} = $model->where('id_consultor', $userId)->where('estado', 'completo')->countAllResults();
// Pendientes borradores
if ($role === 'admin') {
    $pendientes{Modulo} = $model->getAllPendientes();
} else {
    $pendientes{Modulo} = $model->getPendientesByConsultor($userId);
}
// Agregar a $data
```

### dashboard.php
1. Seccion pendientes (cards borrador con "Continuar editando")
2. Card en grid: `<a href="/inspecciones/{modulo}" class="card-tipo">` con icono + total

### Routes.php (10 rutas)
```php
$routes->get('{modulo}', 'Inspeccion{Modulo}Controller::list');
$routes->get('{modulo}/create', 'Inspeccion{Modulo}Controller::create');
$routes->get('{modulo}/create/(:num)', 'Inspeccion{Modulo}Controller::create/$1');
$routes->post('{modulo}/store', 'Inspeccion{Modulo}Controller::store');
$routes->get('{modulo}/edit/(:num)', 'Inspeccion{Modulo}Controller::edit/$1');
$routes->post('{modulo}/update/(:num)', 'Inspeccion{Modulo}Controller::update/$1');
$routes->get('{modulo}/view/(:num)', 'Inspeccion{Modulo}Controller::view/$1');
$routes->get('{modulo}/pdf/(:num)', 'Inspeccion{Modulo}Controller::generatePdf/$1');
$routes->post('{modulo}/finalizar/(:num)', 'Inspeccion{Modulo}Controller::finalizar/$1');
$routes->get('{modulo}/delete/(:num)', 'Inspeccion{Modulo}Controller::delete/$1');
```

---

## Tabla de Fases (patron PLANO)

| Fase | Modulo | Tabla | PDF | id_detailreport | Tag reporte |
|------|--------|-------|-----|-----------------|-------------|
| 2 | Locativa | tbl_inspeccion_locativa | FT-SST-200 | 10 | insp_loc_id |
| 5 | Botiquin | tbl_inspeccion_botiquin | FT-SST-202B | 13 | insp_bot_id |
| 7 | Comunicaciones | tbl_inspeccion_comunicaciones | FT-SST-204 | 15 | insp_com_id |
| 8 | Recursos Seguridad | tbl_inspeccion_recursos_seguridad | FT-SST-210 | 16 | insp_rec_id |
