# 15 - Patron: Documento Maestro Consolidado

## Resumen

Un formulario extenso con datos propios (~80 columnas, multiples fotos, multiples ENUMs) que al generar el PDF **consolida informacion de TODAS las inspecciones previas** del mismo cliente. El PDF resultante es el documento mas largo del sistema (~30+ paginas), combinando texto estatico + datos dinamicos + anexos de otras tablas.

**Modulos que usan este patron:** Plan de Emergencia (Fase 10)

Este patron es una **extension del patron Inspeccion PLANA** (doc 12) con dos diferencias fundamentales:
1. Escala: muchos mas campos, fotos y ENUMs que una inspeccion plana tipica
2. Consolidacion: el PDF no solo refleja datos de SU tabla, sino de TODAS las tablas de inspecciones previas

---

## Estructura de archivos

```
app/SQL/migrate_{modulo}.php                                     — 1 tabla (~80+ columnas)
app/Models/{Modulo}Model.php                                     — 1 modelo
app/Controllers/Inspecciones/{Modulo}Controller.php              — controlador extenso (~600+ lineas)
app/Views/inspecciones/{modulo}/list.php                         — listado cards
app/Views/inspecciones/{modulo}/form.php                         — formulario MUY largo (~26+ secciones)
app/Views/inspecciones/{modulo}/view.php                         — vista read-only
app/Views/inspecciones/{modulo}/pdf.php                          — template DOMPDF enorme (~800+ lineas)
```

Total: 7 archivos nuevos + 3 archivos modificados (Routes.php, dashboard.php, InspeccionesController.php)

---

## Diferencias vs Patron Inspeccion PLANA

| Aspecto | Inspeccion PLANA | Documento Maestro |
|---------|------------------|-------------------|
| Columnas DB | 20-40 | 80+ |
| Fotos | 2-6 | 15-20 |
| ENUMs | 0-2 | 5-8 |
| Secciones formulario | 5-8 | 20-26 |
| PDF paginas | 2-5 | 30+ |
| Datos en PDF | Solo propios | Propios + TODAS las inspecciones previas |
| Texto estatico en PDF | Minimo | Extenso (glosario, legislacion, descripciones) |
| Validacion pre-finalizar | Ninguna | Verificar inspecciones completas del cliente |
| JS en formulario | Basico | Condicionales, auto-llenado, autoguardado |

---

## Controlador — Estructura especifica

### Constantes necesarias

El controlador define constantes para:
- **Telefonos/datos por ciudad** — evita almacenar N columnas de datos fijos que se derivan de una sola seleccion
- **Labels legibles para ENUMs** — mapeo `valor_db => 'Label Legible'` para mostrar en vistas y PDF
- **Lista de campos foto** — array con nombres de todos los campos foto para iterar en upload/delete

```php
class {Modulo}Controller extends BaseController
{
    public const DATOS_POR_CIUDAD = [...]; // o TELEFONOS, etc.
    public const ENUM_LABELS = [...];      // para empresa_aseo, etc.
    public const FOTO_FIELDS = [...];      // 19 campos foto
}
```

### Metodos adicionales vs Inspeccion PLANA

| Metodo | Proposito |
|--------|-----------|
| `uploadAllPhotos($inspeccion)` | Itera FOTO_FIELDS, sube nuevas y preserva existentes |
| `verificarInspeccionesCompletas($idCliente)` | Verifica que todas las inspecciones previas esten completas |
| `checkInspeccionesCompletas($idCliente)` | Endpoint AJAX para validacion frontend |
| `generarPdfInterno($id)` | Carga datos de 8+ modelos/tablas adicionales para el PDF |

### Patron de upload multiple de fotos

```php
private function uploadAllPhotos(?array $inspeccion = null): array
{
    $data = [];
    foreach (self::FOTO_FIELDS as $campo) {
        $file = $this->request->getFile($campo);
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $data[$campo] = $this->uploadFoto($file, '{modulo}');
        } else {
            $data[$campo] = $inspeccion[$campo] ?? null; // mantener existente
        }
    }
    return $data;
}
```

### Patron de carga de inspecciones previas para PDF

```php
private function generarPdfInterno(int $id): string
{
    $inspeccion = $this->model->find($id);
    $cliente = (new ClienteModel())->find($inspeccion['id_cliente']);

    // Cargar TODAS las inspecciones previas del mismo cliente
    $ultimaLocativa = (new InspeccionLocativaModel())
        ->where('id_cliente', $inspeccion['id_cliente'])
        ->where('estado', 'completo')
        ->orderBy('fecha_inspeccion', 'DESC')->first();

    $ultimaMatriz = (new MatrizVulnerabilidadModel())
        ->where('id_cliente', $inspeccion['id_cliente'])
        ->where('estado', 'completo')
        ->orderBy('fecha_inspeccion', 'DESC')->first();

    // ... repetir para cada tipo de inspeccion

    // Convertir TODAS las fotos a base64 (propias + de inspecciones previas)
    $fotosBase64 = [];
    foreach (self::FOTO_FIELDS as $campo) {
        if (!empty($inspeccion[$campo])) {
            $path = FCPATH . $inspeccion[$campo];
            if (file_exists($path)) {
                $fotosBase64[$campo] = 'data:image/...;base64,' . base64_encode(file_get_contents($path));
            }
        }
    }

    // Renderizar con DOMPDF
    $html = view('inspecciones/{modulo}/pdf', [
        'inspeccion' => $inspeccion,
        'cliente' => $cliente,
        'fotosBase64' => $fotosBase64,
        'ultimaLocativa' => $ultimaLocativa,
        'ultimaMatriz' => $ultimaMatriz,
        // ... todos los datos de inspecciones previas
    ]);

    // Generar PDF con DOMPDF
    $dompdf = new Dompdf(['defaultPaperSize' => 'letter', 'isRemoteEnabled' => true]);
    $dompdf->loadHtml($html);
    $dompdf->render();
    // ...
}
```

---

## Formulario — Complejidad JS

El formulario del Documento Maestro requiere mas JS que una inspeccion plana:

### 1. Autoguardado localStorage
```javascript
// Key: {modulo}_draft_{id|new}
// Intervalo: 30s + 2s debounce on change
// Solo campos texto/select (NO fotos - muy pesadas para localStorage)
```

### 2. Campos condicionales
```javascript
// Mostrar/ocultar secciones segun seleccion
// Ejemplo: casas → muestra pisos, apartamentos → muestra torres
$('#casas_o_apartamentos').on('change', function() {
    if ($(this).val() === 'casas') {
        $('#seccion_pisos').show();
        $('#seccion_torres').hide();
    } else {
        $('#seccion_pisos').hide();
        $('#seccion_torres').show();
    }
});
```

### 3. Auto-llenado por seleccion
```javascript
// Llenar tabla de datos fijos segun seleccion
// Ejemplo: seleccionar ciudad → llenar tabla de telefonos
$('#ciudad').on('change', function() {
    const telefonos = TELEFONOS_DATA[$(this).val()];
    // Llenar cada celda de la tabla
});
```

---

## PDF — Estrategia de texto estatico

El PDF contiene bloques extensos de texto estatico (glosario, legislacion, descripciones de riesgos, procedimientos). Este texto se embebe directamente en el template `pdf.php` como HTML, no se almacena en la base de datos.

**Fuente del texto:** archivo de referencia `z_{modulo}.txt` en la raiz del proyecto, que contiene el documento Word original convertido a texto plano.

**Ventaja:** No se necesita una tabla extra para "contenido estatico". El texto solo cambia cuando se actualiza el template PHP.

**Desventaja:** Si se necesita personalizar el texto por cliente, habria que migrar a BD. Por ahora todos los clientes comparten el mismo texto base.

---

## Validacion pre-finalizacion

A diferencia de una inspeccion plana (que se puede finalizar independientemente), el Documento Maestro tiene un **prerequisito**: todas las inspecciones parciales deben estar completas.

```
[Formulario Plan] → Click Finalizar → verificarInspeccionesCompletas()
    ├── Si hay faltantes → Error + lista de inspecciones faltantes
    └── Si todo OK → Generar PDF + marcar estado='completo' + upload a reportes
```

Este chequeo se ejecuta tanto en backend (antes de generar el PDF) como opcionalmente en frontend (AJAX endpoint para feedback inmediato).

---

## Consideraciones DOMPDF para documentos largos

- **Memoria:** documentos de 30+ paginas con ~20 imagenes base64 consumen mucha memoria. Configurar `memory_limit` alto en PHP
- **Tiempo:** la generacion puede tardar 10-30 segundos. Considerar timeout del servidor
- **Imagenes:** redimensionar fotos a maximo ~800px de ancho antes de convertir a base64
- **Page breaks:** usar `page-break-before: always` para separar secciones mayores
- **NO usar:** flexbox, grid, CSS variables, calc(), opacity — DOMPDF no los soporta
- **SI usar:** tables para layout, px para unidades, colores hex/rgb
