# PROMPT: Implementar 7 Módulos de Inspección Pendientes

## Contexto

Proyecto CodeIgniter 4 + PHP 8.2 + MySQL 8 + DOMPDF + Bootstrap 5 PWA.
Las **tablas SQL ya existen** en LOCAL y PRODUCCIÓN. La **documentación técnica** de cada módulo ya existe en `docs/`.
Falta implementar: Modelo + Controlador + Vistas (list, form, view, pdf) + Rutas + Dashboard para cada módulo.

**IMPORTANTE:** Lee el archivo de documentación indicado para cada módulo ANTES de implementarlo. Cada doc tiene el mapeo completo de columnas, constantes ITEMS, estructura del formulario, secciones del PDF y rutas.

---

## Archivos de referencia (LEER para entender el patrón)

```
docs/12_PATRON_INSPECCION_PLANA.md          — Patrón base para todos los módulos
app/Controllers/Inspecciones/InspeccionRecursosSeguridadController.php  — Controlador ejemplo (CRUD completo + PDF + fotos)
app/Models/InspeccionRecursosSeguridadModel.php                         — Modelo ejemplo
app/Views/inspecciones/recursos-seguridad/list.php                      — Vista lista ejemplo
app/Views/inspecciones/recursos-seguridad/form.php                      — Vista formulario ejemplo
app/Views/inspecciones/recursos-seguridad/view.php                      — Vista read-only ejemplo
app/Views/inspecciones/recursos-seguridad/pdf.php                       — Template DOMPDF ejemplo
app/Views/inspecciones/layout_pwa.php                                   — Layout compartido PWA
app/Controllers/Inspecciones/InspeccionesController.php                 — Dashboard (agregar conteos)
app/Views/inspecciones/dashboard.php                                    — Dashboard vista (agregar cards)
app/Config/Routes.php                                                   — Rutas (agregar dentro del grupo inspecciones)
```

---

## Patrón estándar por módulo (7 archivos nuevos + 3 modificaciones)

### Modelo — `app/Models/{Modulo}Model.php`
```php
namespace App\Models;
use CodeIgniter\Model;

class {Modulo}Model extends Model {
    protected $table = 'tbl_{tabla}';
    protected $primaryKey = 'id';
    protected $allowedFields = [/* TODOS los campos excepto id */];
    protected $useTimestamps = true;

    public function getByConsultor(int $id, ?string $estado = null) // JOIN tbl_clientes
    public function getPendientesByConsultor(int $id)                // estado = 'borrador'
    public function getAllPendientes()                                // todos los borradores
    public function getByCliente(int $id)                            // JOIN tbl_consultor
}
```

### Controlador — `app/Controllers/Inspecciones/{Modulo}Controller.php`
```php
namespace App\Controllers\Inspecciones;
// Métodos: list, create, store, edit, update, view, finalizar, generatePdf, delete
// Constante: ITEMS_EPP / ITEMS_ZONA / OPCIONES_* según el módulo
// Privados: getInspeccionPostData(), uploadFoto(), generarPdfInterno(), uploadToReportes()
```

### Vistas — `app/Views/inspecciones/{slug}/`
- `list.php` — Cards con filtro Select2, badge estado, SweetAlert delete
- `form.php` — enctype multipart, secciones según doc, autoguardado localStorage, preview fotos
- `view.php` — Read-only, fotos clickeables, botones Ver PDF / Editar
- `pdf.php` — DOMPDF portrait letter, CSS inline (NO flexbox/grid), tablas layout, fotos base64

### Rutas (10 por módulo)
```php
$routes->get('{slug}', '{Ctrl}::list');
$routes->get('{slug}/create', '{Ctrl}::create');
$routes->get('{slug}/create/(:num)', '{Ctrl}::create/$1');
$routes->post('{slug}/store', '{Ctrl}::store');
$routes->get('{slug}/edit/(:num)', '{Ctrl}::edit/$1');
$routes->post('{slug}/update/(:num)', '{Ctrl}::update/$1');
$routes->get('{slug}/view/(:num)', '{Ctrl}::view/$1');
$routes->get('{slug}/pdf/(:num)', '{Ctrl}::generatePdf/$1');
$routes->post('{slug}/finalizar/(:num)', '{Ctrl}::finalizar/$1');
$routes->get('{slug}/delete/(:num)', '{Ctrl}::delete/$1');
```

### Dashboard — 2 cambios
1. **InspeccionesController.php**: import modelo, conteo completos, pendientes borradores, agregar a $data
2. **dashboard.php**: card en grid (icono + label + conteo) + sección pendientes (borradores)

---

## Los 7 módulos a implementar

### MÓDULO 1: Reporte de Capacitación
- **Doc:** `docs/16_REPORTE_CAPACITACION.md`
- **Tabla:** `tbl_reporte_capacitacion` (24 cols)
- **Slug:** `reporte-capacitacion`
- **Controlador:** `ReporteCapacitacionController`
- **Modelo:** `ReporteCapacitacionModel`
- **Fotos:** 5 (foto_listado_asistencia, foto_capacitacion, foto_evaluacion, foto_otros_1, foto_otros_2)
- **EnumList:** perfil_asistentes (TEXT comma-separated, checkboxes: contratistas, administrador, consejo_administracion, residentes, todos)
- **Campos especiales:** total_asistentes INT, porcentaje_asistencia DECIMAL(5,2), total_evaluados INT, resultado_evaluacion DECIMAL(5,2)
- **PDF:** FT-SST-211
- **id_detailreport:** 21
- **Tag:** `rep_cap_id:{id}`
- **Icono dashboard:** `fa-chalkboard-teacher`
- **Dir fotos:** `uploads/inspecciones/reporte-capacitacion/`
- **Dir PDFs:** `uploads/inspecciones/reporte-capacitacion/pdfs/`

### MÓDULO 2: Asistencia Inducción
- **Doc:** `docs/17_ASISTENCIA_INDUCCION.md`
- **Tablas:** `tbl_asistencia_induccion` (18 cols master) + `tbl_asistencia_induccion_asistente` (7 cols detalle)
- **Slug:** `asistencia-induccion`
- **Controlador:** `AsistenciaInduccionController`
- **Modelos:** `AsistenciaInduccionModel` + `AsistenciaInduccionAsistenteModel`
- **ENUM:** tipo_charla (induccion_reinduccion, reunion, charla, capacitacion, otros_temas)
- **Patrón especial:** Master-Detalle (N asistentes dinámicos con firma PNG cada uno)
- **PDF:** FT-SST-005 (siempre) + FT-SST-003 (si tipo=induccion_reinduccion)
- **id_detailreport:** 22 (FT-SST-005), 23 (FT-SST-003)
- **Tag:** `asist_ind_id:{id}`
- **Icono dashboard:** `fa-clipboard-list`
- **Dir fotos:** `uploads/inspecciones/asistencia-induccion/`
- **Dir firmas:** `uploads/inspecciones/asistencia-induccion/firmas/`
- **Dir PDFs:** `uploads/inspecciones/asistencia-induccion/pdfs/`

### MÓDULO 3: Dotación Vigilante
- **Doc:** `docs/18_DOTACION_VIGILANTE.md`
- **Tabla:** `tbl_dotacion_vigilante` (23 cols)
- **Slug:** `dotacion-vigilante`
- **Controlador:** `DotacionVigilanteController`
- **Modelo:** `DotacionVigilanteModel`
- **Fotos:** 2 (foto_cuerpo_completo, foto_cuarto_almacenamiento)
- **EPP Items:** 7 con ENUM(5) (bueno, regular, deficiente, no_tiene, no_aplica)
- **Constante:**
```php
public const ITEMS_EPP = [
    'uniforme'  => ['label' => 'Uniforme', 'icon' => 'fa-shirt'],
    'chaqueta'  => ['label' => 'Chaqueta / Chaleco Reflectivo', 'icon' => 'fa-vest'],
    'radio'     => ['label' => 'Radio de Comunicación', 'icon' => 'fa-walkie-talkie'],
    'baston'    => ['label' => 'Bastón de Mando / Linterna', 'icon' => 'fa-flashlight'],
    'calzado'   => ['label' => 'Calzado de Seguridad', 'icon' => 'fa-shoe-prints'],
    'gorra'     => ['label' => 'Gorra / Casco', 'icon' => 'fa-hard-hat'],
    'carne'     => ['label' => 'Carné de Identificación', 'icon' => 'fa-id-badge'],
];
public const ESTADOS_EPP = ['bueno','regular','deficiente','no_tiene','no_aplica'];
```
- **Campos texto:** contratista, servicio, nombre_cargo, actividades_frecuentes, concepto_final, observaciones
- **PDF:** FT-SST-213
- **id_detailreport:** 24
- **Tag:** `dot_vig_id:{id}`
- **Icono dashboard:** `fa-user-shield`

### MÓDULO 4: Dotación Todero
- **Doc:** `docs/19_DOTACION_TODERO.md`
- **Tabla:** `tbl_dotacion_todero` (32 cols)
- **Slug:** `dotacion-todero`
- **Controlador:** `DotacionToderoController`
- **Modelo:** `DotacionToderoModel`
- **Fotos:** 2
- **EPP Items:** 16 con ENUM(5) — misma estructura que Vigilante, más ítems
- **Constante:**
```php
public const ITEMS_EPP = [
    'tapabocas'         => ['label' => 'Tapabocas Desechable', 'icon' => 'fa-head-side-mask'],
    'guantes_nitrilo'   => ['label' => 'Guantes de Nitrilo', 'icon' => 'fa-hand'],
    'mascarilla_polvo'  => ['label' => 'Mascarilla para Polvo', 'icon' => 'fa-mask-face'],
    'guantes_nylon'     => ['label' => 'Guantes de Nylon', 'icon' => 'fa-mitten'],
    'guantes_caucho'    => ['label' => 'Guantes de Caucho', 'icon' => 'fa-mitten'],
    'gafas'             => ['label' => 'Gafas de Seguridad', 'icon' => 'fa-glasses'],
    'uniforme'          => ['label' => 'Uniforme', 'icon' => 'fa-shirt'],
    'sombrero'          => ['label' => 'Sombrero / Gorra', 'icon' => 'fa-hat-cowboy'],
    'zapato'            => ['label' => 'Zapato Antideslizante', 'icon' => 'fa-shoe-prints'],
    'casco'             => ['label' => 'Casco de Seguridad', 'icon' => 'fa-hard-hat'],
    'careta'            => ['label' => 'Careta Facial', 'icon' => 'fa-head-side-mask'],
    'protector_auditivo'=> ['label' => 'Protector Auditivo', 'icon' => 'fa-ear-listen'],
    'respirador'        => ['label' => 'Respirador', 'icon' => 'fa-lungs'],
    'guantes_vaqueta'   => ['label' => 'Guantes de Vaqueta', 'icon' => 'fa-mitten'],
    'botas_dielectricas'=> ['label' => 'Botas Dieléctricas', 'icon' => 'fa-boot'],
    'delantal_pvc'      => ['label' => 'Delantal PVC', 'icon' => 'fa-vest'],
];
```
- **PDF:** FT-SST-213
- **id_detailreport:** 25
- **Tag:** `dot_tod_id:{id}`
- **Icono dashboard:** `fa-broom`

### MÓDULO 5: Dotación Aseadora
- **Doc:** `docs/20_DOTACION_ASEADORA.md`
- **Tabla:** `tbl_dotacion_aseadora` (25 cols)
- **Slug:** `dotacion-aseadora`
- **Controlador:** `DotacionAseadoraController`
- **Modelo:** `DotacionAseadoraModel`
- **Fotos:** 2
- **EPP Items:** 8 con ENUM(5) — misma estructura
- **Constante:**
```php
public const ITEMS_EPP = [
    'tapabocas'      => ['label' => 'Tapabocas Desechable', 'icon' => 'fa-head-side-mask'],
    'guantes_nitrilo'=> ['label' => 'Guantes de Nitrilo', 'icon' => 'fa-hand'],
    'guantes_caucho' => ['label' => 'Guantes de Caucho Calibre 20, 25, 50', 'icon' => 'fa-mitten'],
    'gafas'          => ['label' => 'Gafas de Seguridad', 'icon' => 'fa-glasses'],
    'uniforme'       => ['label' => 'Uniforme', 'icon' => 'fa-shirt'],
    'sombrero'       => ['label' => 'Sombrero / Gorra', 'icon' => 'fa-hat-cowboy'],
    'zapato'         => ['label' => 'Zapato Antideslizante', 'icon' => 'fa-shoe-prints'],
    'botas_caucho'   => ['label' => 'Botas de Caucho', 'icon' => 'fa-boot'],
];
```
- **PDF:** FT-SST-213
- **id_detailreport:** 26
- **Tag:** `dot_ase_id:{id}`
- **Icono dashboard:** `fa-spray-can-sparkles`

### MÓDULO 6: Auditoría Zona de Residuos
- **Doc:** `docs/21_AUDITORIA_ZONA_RESIDUOS.md`
- **Tabla:** `tbl_auditoria_zona_residuos` (33 cols)
- **Slug:** `auditoria-zona-residuos`
- **Controlador:** `AuditoriaZonaResiduosController`
- **Modelo:** `AuditoriaZonaResiduosModel`
- **Fotos:** 12 (una por ítem: foto_{key})
- **Ítems:** 12 con estado_{key} ENUM(6) + foto_{key}. Excepción: proliferacion_plagas es texto libre (VARCHAR) + foto
- **ENUM(6):** bueno, regular, malo, deficiente, no_tiene, no_aplica
- **Constante:**
```php
public const ITEMS_ZONA = [
    'acceso'                => ['label' => 'Acceso', 'icon' => 'fa-door-open', 'tipo' => 'enum'],
    'techo_pared_pisos'     => ['label' => 'Techo, Pared y Pisos', 'icon' => 'fa-building', 'tipo' => 'enum'],
    'ventilacion'           => ['label' => 'Ventilación', 'icon' => 'fa-wind', 'tipo' => 'enum'],
    'prevencion_incendios'  => ['label' => 'Prevención y Control de Incendios', 'icon' => 'fa-fire-extinguisher', 'tipo' => 'enum'],
    'drenajes'              => ['label' => 'Drenajes', 'icon' => 'fa-water', 'tipo' => 'enum'],
    'proliferacion_plagas'  => ['label' => 'Proliferación de Plagas', 'icon' => 'fa-bug', 'tipo' => 'texto_libre'],
    'recipientes'           => ['label' => 'Recipientes', 'icon' => 'fa-trash-can', 'tipo' => 'enum'],
    'reciclaje'             => ['label' => 'Reciclaje', 'icon' => 'fa-recycle', 'tipo' => 'enum'],
    'iluminarias'           => ['label' => 'Iluminarias', 'icon' => 'fa-lightbulb', 'tipo' => 'enum'],
    'senalizacion'          => ['label' => 'Señalización', 'icon' => 'fa-sign-hanging', 'tipo' => 'enum'],
    'limpieza_desinfeccion' => ['label' => 'Limpieza y Desinfección', 'icon' => 'fa-spray-can', 'tipo' => 'enum'],
    'poseta'                => ['label' => 'Poceta', 'icon' => 'fa-sink', 'tipo' => 'enum'],
];
public const ESTADOS_ZONA = ['bueno','regular','malo','deficiente','no_tiene','no_aplica'];
```
- **En el form:** Si tipo=enum → select con ESTADOS_ZONA. Si tipo=texto_libre → input text. Ambos + foto
- **PDF:** FT-SST-214
- **id_detailreport:** 27
- **Tag:** `aud_res_id:{id}`
- **Icono dashboard:** `fa-dumpster`

### MÓDULO 7: Preparación Simulacro
- **Doc:** `docs/22_PREPARACION_SIMULACRO.md`
- **Tabla:** `tbl_preparacion_simulacro` (35 cols)
- **Slug:** `preparacion-simulacro`
- **Controlador:** `PreparacionSimulacroController`
- **Modelo:** `PreparacionSimulacroModel`
- **Fotos:** 2 (imagen_1, imagen_2)
- **ENUMs:** evento_simulado(sismo), alcance_simulacro(total,parcial), tipo_evacuacion(horizontal,vertical,mixta), entrega_formato_evaluacion(si,no)
- **EnumList (checkboxes → TEXT comma-separated):**
```php
public const OPCIONES_ALARMA = [
    'sirena' => 'Sirena', 'megafono' => 'Megáfono', 'radio_interno' => 'Radio interno',
];
public const OPCIONES_DISTINTIVOS = [
    'chaleco' => 'Chaleco', 'brazalete' => 'Brazalete', 'ninguno' => 'Ninguno',
];
public const OPCIONES_EQUIPOS = [
    'paletas_pare_siga' => 'Paletas de PARE y SIGA', 'chaleco_reflectivo' => 'Chaleco reflectivo',
    'megafono_pito' => 'Megáfono o pito', 'camilla' => 'Camilla', 'botiquin' => 'Botiquín',
    'radio_onda_corta' => 'Radio de onda corta', 'paleta_punto_encuentro' => 'Paleta Punto de Encuentro',
];
```
- **Cronograma:** 9 campos TIME (hora_inicio → agradecimiento_cierre). Tiempo_total calculado en PHP, NO almacenado
- **Brigadista líder:** nombre_brigadista_lider, email_brigadista_lider, whatsapp_brigadista_lider
- **Campos texto:** ubicacion (GPS), direccion, personal_no_evacua, puntos_encuentro, recurso_humano, observaciones
- **PDF:** FT-SST-223
- **id_detailreport:** 28
- **Tag:** `prep_sim_id:{id}`
- **Icono dashboard:** `fa-clipboard-check`

---

## Orden de implementación

Para cada módulo, en este orden:

1. **Modelo** (1 archivo)
2. **Controlador** (1 archivo — CRUD + PDF + uploadToReportes)
3. **Vistas** (4 archivos — list, form, view, pdf)
4. **Rutas** (10 rutas en Routes.php dentro del grupo inspecciones, DESPUÉS de plan-emergencia y ANTES de simulacro)
5. **Dashboard** (import + conteo en InspeccionesController.php, card + pendientes en dashboard.php)
6. **Verificar sintaxis PHP** de cada archivo creado

Orden sugerido de los 7 módulos:
1. Dotación Vigilante (más simple, 7 ítems EPP)
2. Dotación Aseadora (8 ítems, casi idéntico)
3. Dotación Todero (16 ítems, mismo patrón)
4. Auditoría Zona Residuos (12 ítems con foto cada uno, patrón diferente)
5. Reporte de Capacitación (5 fotos + EnumList + decimales)
6. Preparación Simulacro (9 TIME + 3 EnumList + brigadista)
7. Asistencia Inducción (master-detalle, N asistentes, firmas — el más complejo)

---

## Reglas importantes

- **CSS PDF:** NO usar flexbox, grid, calc(), ni variables CSS. Solo tablas para layout, px para unidades, base64 para imágenes
- **Fotos en PDF:** Convertir SIEMPRE a base64 con `mime_content_type()` + `base64_encode(file_get_contents())`
- **Autoguardado:** localStorage con key `{slug}_draft_{id|new}`, debounce 2s + intervalo 30s, NO guardar file inputs
- **Estado flow:** borrador → editar N veces → finalizar → completo (proteger edit/update/delete si completo)
- **uploadToReportes:** SIEMPRE `id_report_type = 6`, copiar PDF a `uploads/{nit_cliente}/`
- **Permisos:** Admin ve todo, consultor solo lo suyo (filtrar por id_consultor en session)
- **Los 3 módulos Dotación** comparten el mismo texto estático de PDF (FT-SST-213), solo cambia ITEMS_EPP
