# 13 - Patron: Inspeccion N-ITEMS DINAMICOS

## Resumen

Dos tablas (master + detalle), dos modelos, filas dinamicas que el usuario agrega/elimina via JavaScript. El formulario usa `buildRow()` para generar HTML de cada fila, y el backend usa delete+reinsert con preservacion de fotos.

**Modulos que usan este patron:** Extintores (Fase 4), Gabinetes (Fase 6)

**Variante N-ITEMS FIJOS:** Senalizacion (Fase 3) — misma estructura pero items predefinidos, no se agregan/eliminan.

---

## Estructura de archivos

```
app/SQL/migrate_inspeccion_{modulo}.php                          — 2 tablas (master + detalle)
app/Models/Inspeccion{Modulo}Model.php                           — modelo master
app/Models/{Modulo}DetalleModel.php                              — modelo detalle
app/Controllers/Inspecciones/Inspeccion{Modulo}Controller.php    — controlador CRUD+PDF
app/Views/inspecciones/{modulo}/list.php                         — listado cards
app/Views/inspecciones/{modulo}/form.php                         — formulario con JS buildRow()
app/Views/inspecciones/{modulo}/view.php                         — vista read-only
app/Views/inspecciones/{modulo}/pdf.php                          — template DOMPDF
```

Total: 8 archivos nuevos + 3 archivos modificados (Routes.php, dashboard.php, InspeccionesController.php)

---

## Migracion SQL

### Tabla master
```sql
CREATE TABLE IF NOT EXISTS tbl_inspeccion_{modulo} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,
    -- Campos generales del modulo (planos)
    -- Fotos generales
    foto_1 VARCHAR(255) NULL,
    foto_2 VARCHAR(255) NULL,
    -- Resultado
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- FKs e indices
);
```

### Tabla detalle
```sql
CREATE TABLE IF NOT EXISTS tbl_{modulo}_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_inspeccion INT NOT NULL,
    numero INT NOT NULL,
    -- Campos por item (criterios SI/NO, estados, ubicacion, etc.)
    foto VARCHAR(255) NULL,
    observaciones TEXT NULL,
    CONSTRAINT fk_{tag}_detalle_insp FOREIGN KEY (id_inspeccion)
        REFERENCES tbl_inspeccion_{modulo}(id) ON DELETE CASCADE
);
```

La FK con `ON DELETE CASCADE` asegura que al eliminar la inspeccion se borran todos los detalles.

---

## Modelos

### Modelo master
Identico al patron PLANO (ver doc 12):
```php
getByConsultor(), getPendientesByConsultor(), getAllPendientes(), getByCliente()
```

### Modelo detalle
```php
class {Modulo}DetalleModel extends Model
{
    protected $table = 'tbl_{modulo}_detalle';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;  // sin timestamps en detalle

    public function getByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('numero', 'ASC')
            ->findAll();
    }

    public function deleteByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
```

---

## Controlador

### Constante CRITERIOS

Define los campos de inspeccion por item individual:

```php
public const CRITERIOS = [
    'campo_key' => [
        'label' => 'Nombre visible',
        'type'  => 'sino',                    // genera select SI/NO
    ],
    'campo_estado' => [
        'label'    => 'Estado general',
        'type'     => 'estado',
        'opciones' => ['BUENO', 'REGULAR', 'MALO'],  // genera select con opciones
    ],
];
```

### Metodos publicos
Mismos 10 que patron PLANO, pero `list()` agrega conteo de items:
```php
foreach ($inspecciones as &$insp) {
    $insp['total_items'] = $this->detalleModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
}
```

### Metodo privado adicional: save{Items}()

**Patron delete+reinsert con preservacion de fotos:**

```php
private function saveItems(int $idInspeccion): void
{
    // 1. Obtener items existentes para preservar fotos no re-subidas
    $existentes = [];
    foreach ($this->detalleModel->getByInspeccion($idInspeccion) as $item) {
        $existentes[$item['id']] = $item;
    }

    // 2. Borrar todos los detalles existentes
    $this->detalleModel->deleteByInspeccion($idInspeccion);

    // 3. Re-insertar desde datos POST
    $ubicaciones = $this->request->getPost('item_ubicacion') ?? [];
    $itemIds = $this->request->getPost('item_id') ?? [];
    $files = $this->request->getFiles();

    foreach ($ubicaciones as $i => $ubicacion) {
        // Preservar foto existente via item_id[]
        $existenteId = $itemIds[$i] ?? null;
        $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;
        $fotoPath = $existente['foto'] ?? null;

        // Si hay nueva foto, subirla
        if (isset($files['item_foto'][$i]) && $files['item_foto'][$i]->isValid()) {
            $file = $files['item_foto'][$i];
            $fileName = $file->getRandomName();
            $file->move($dir, $fileName);
            $fotoPath = 'uploads/inspecciones/{modulo}/fotos/' . $fileName;
        }

        // Insert con todos los criterios
        $this->detalleModel->insert([
            'id_inspeccion' => $idInspeccion,
            'numero'        => $i + 1,
            'ubicacion'     => $ubicacion,
            // ... criterios del POST
            'foto'          => $fotoPath,
            'observaciones' => ($this->request->getPost('item_obs') ?? [])[$i] ?? null,
        ]);
    }
}
```

---

## JavaScript en form.php

### buildRow(num, data)

Genera el HTML de una fila de item individual:

```javascript
function buildItemRow(num, data) {
    data = data || {};
    let html = '<div class="card mb-2 item-row" id="item-' + num + '">';
    html += '<input type="hidden" name="item_id[]" value="' + (data.id || '') + '">';

    // Ubicacion
    html += '<input name="item_ubicacion[]" value="' + (data.ubicacion || '') + '">';

    // Criterios SI/NO (generados desde PHP CRITERIOS)
    // Criterios estado (select con opciones)

    // Foto
    html += '<input type="file" name="item_foto[' + num + ']" accept="image/*">';
    if (data.foto) {
        html += '<img src="/' + data.foto + '" class="img-thumbnail">';
    }

    // Observaciones
    html += '<textarea name="item_obs[]">' + (data.observaciones || '') + '</textarea>';

    // Boton eliminar
    html += '<button type="button" onclick="removeItem(' + num + ')">Eliminar</button>';
    html += '</div>';
    return html;
}
```

### Carga de items existentes (PHP -> JS)

```javascript
// Items existentes renderizados por PHP en el form
<?php foreach ($items as $i => $item): ?>
    container.insertAdjacentHTML('beforeend', buildItemRow(<?= $i ?>, <?= json_encode($item) ?>));
<?php endforeach; ?>
```

### Agregar nuevo item
```javascript
document.getElementById('btnAgregar').addEventListener('click', function() {
    counter++;
    container.insertAdjacentHTML('beforeend', buildItemRow(counter, {}));
});
```

---

## PDF — tabla detalle

```html
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Ubicacion</th>
            <th>Criterio 1</th>
            <th>Criterio 2</th>
            <!-- ... -->
            <th>Estado</th>
            <th>Foto</th>
            <th>Obs.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $i => $item): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($item['ubicacion']) ?></td>
            <td class="val-si/val-no"><?= esc($item['criterio']) ?></td>
            <!-- ... -->
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

Clases CSS para colores:
- `val-si` → verde (#155724)
- `val-no` → rojo (#721c24)
- `val-bueno` → verde
- `val-regular` → amarillo (#856404)
- `val-malo` → rojo

---

## Diferencias clave vs Patron PLANO

| Aspecto | PLANO | N-ITEMS |
|---------|-------|---------|
| Tablas | 1 | 2 (master + detalle) |
| Modelos | 1 | 2 |
| Campos items | En tabla master | En tabla detalle |
| JS buildRow() | No | Si |
| save{Items}() | No | Si (delete+reinsert) |
| Preservar fotos | Simple (update si nueva) | Complejo (lookup por ID antes de delete) |
| list() conteo | Simple | Agrega total_items por inspeccion |
| Archivos | 7 nuevos | 8 nuevos (modelo detalle extra) |

---

## Tabla de Fases (patron N-ITEMS)

| Fase | Modulo | Tablas | PDF | id_detailreport | Tag |
|------|--------|--------|-----|-----------------|-----|
| 3 | Senalizacion | tbl_inspeccion_senalizacion + tbl_items_senalizacion | FT-SST-201 | 11 | insp_senal_id |
| 4 | Extintores | tbl_inspeccion_extintores + tbl_extintor_detalle | FT-SST-202 | 12 | insp_ext_id |
| 6 | Gabinetes | tbl_inspeccion_gabinetes + tbl_gabinete_detalle | FT-SST-203 | 14 | insp_gab_id |

---

## Variante: N-ITEMS FIJOS (Senalizacion)

La Fase 3 (Senalizacion) es un hibrido:
- Tiene tabla detalle como N-items
- Pero los items son **fijos** (37 predefinidos en `ITEMS_DEFINITION`)
- NO se agregan/eliminan dinamicamente
- El controller pre-carga los 37 items con defaults en `create()`
- Incluye `recalcularCalificacion()` para scoring automatico con formula:
  `calificacion = 100 * (0.5 * parcial + total) / (noCumple + parcial + total)`
- Descripcion cualitativa segun rango (critico/bajo/medio/bueno/excelente)
