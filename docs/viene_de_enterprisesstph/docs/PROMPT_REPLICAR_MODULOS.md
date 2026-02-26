# PROMPT PARA REPLICAR MÓDULO PWA INSPECCIONES EN APLICATIVO GEMELO

## Contexto

Módulo completo PWA "Inspecciones SST" con 25 controladores, 26 modelos, ~97 vistas, 25 migraciones SQL, 2 formularios públicos y dashboard unificado con 24 cards.

**Stack:** CodeIgniter 4, PHP 8.2, MySQL 8, DOMPDF para PDFs, Bootstrap 5 PWA.

---

## 1. CONTROLADORES (25 archivos)

### Dentro del grupo Inspecciones (autenticados — 23 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionesController.php          — Dashboard principal
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ActaVisitaController.php             — Acta de visita
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionLocativaController.php     — Inspección locativa
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionSenalizacionController.php — Señalización (37 ítems)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionExtintoresController.php   — Extintores (N dinámico)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionBotiquinController.php     — Botiquín (32 elementos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionGabineteController.php     — Gabinetes (N dinámico)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionComunicacionController.php — Comunicaciones
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionRecursosSeguridadController.php — Recursos seguridad
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProbabilidadPeligrosController.php  — Probabilidad peligros (11 items)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\MatrizVulnerabilidadController.php  — Matriz vulnerabilidad (25 criterios)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PlanEmergenciaController.php        — Plan emergencia (doc maestro, ~82 cols, 19 fotos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\EvaluacionSimulacroController.php   — Evaluación simulacro (277 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\HvBrigadistaController.php          — Hoja de vida brigadista (288 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\DotacionVigilanteController.php     — Dotación vigilante (398 líneas, 7 EPP)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\DotacionAseadoraController.php      — Dotación aseadora (399 líneas, 8 EPP)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\DotacionToderoController.php        — Dotación todero (407 líneas, 16 EPP)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\AuditoriaZonaResiduosController.php — Auditoría zona residuos (412 líneas, 12 ítems + 12 fotos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PreparacionSimulacroController.php  — Preparación simulacro (436 líneas, 9 TIME + EnumLists)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ReporteCapacitacionController.php   — Reporte capacitación (382 líneas, 5 fotos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\AsistenciaInduccionController.php   — Asistencia inducción (516 líneas, master-detalle + firmas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProgramaLimpiezaController.php      — Programa limpieza y desinfección (306 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\CartaVigiaPwaController.php         — Carta vigía SST
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\MantenimientosPwaController.php     — Mantenimientos
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PendientesPwaController.php         — Pendientes/compromisos
```

### Controladores públicos (SIN autenticación)
```
c:\xampp\htdocs\enterprisesstph\app\Controllers\SimulacroPublicoController.php        — Formulario público evaluación simulacro (280 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\HvBrigadistaPublicoController.php     — Formulario público HV brigadista (198 líneas)
```

---

## 2. MODELOS (17 archivos relevantes al módulo)

```
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaFotoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaIntegranteModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaTemaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionLocativaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\HallazgoLocativoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionSenalizacionModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ItemSenalizacionModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionExtintoresModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ExtintorDetalleModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionBotiquinModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ElementoBotiquinModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionGabineteModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\GabineteDetalleModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionComunicacionModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionRecursosSeguridadModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ProbabilidadPeligrosModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\MatrizVulnerabilidadModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\PlanEmergenciaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\EvaluacionSimulacroModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\HvBrigadistaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\CartaVigiaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\MantenimientoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\VencimientosMantenimientoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\PendientesModel.php
```

---

## 3. VISTAS (65 archivos)

### Layout compartido PWA
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\layout_pwa.php
```

### Dashboard
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\dashboard.php
```

### Acta de Visita (5 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\pdf.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\firma.php
```

### Inspección Locativa (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\pdf.php
```

### Señalización (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\pdf.php
```

### Extintores (5 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\pdf.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\_extintor_row.php
```

### Botiquín (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\pdf.php
```

### Gabinetes (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\pdf.php
```

### Comunicaciones (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\pdf.php
```

### Recursos de Seguridad (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\pdf.php
```

### Probabilidad de Peligros (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\pdf.php
```

### Matriz de Vulnerabilidad (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\pdf.php
```

### Plan de Emergencia (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\pdf.php
```

### Evaluación Simulacro (3 archivos internos + 1 público)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\simulacro\list.php        (128 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\simulacro\view.php        (160 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\simulacro\pdf.php         (266 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\simulacro\form_publico.php             (973 líneas — formulario público)
```

### Hoja de Vida Brigadista (3 archivos internos + 1 público)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\hv-brigadista\list.php    (121 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\hv-brigadista\view.php    (142 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\hv-brigadista\pdf.php     (265 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\hv-brigadista\form_publico.php         (715 líneas — formulario público)
```

### Carta Vigía (7 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\pdf.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\firma.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\firma_error.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\firma_success.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\email_firma.php
```

### Mantenimientos (2 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\mantenimientos\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\mantenimientos\form.php
```

### Pendientes (2 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\pendientes\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\pendientes\form.php
```

---

## 4. MIGRACIONES SQL (24 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_acta_visita.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_locativa.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_senalizacion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_extintores.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_botiquin.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_gabinetes.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_comunicaciones.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_recursos_seguridad.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_probabilidad_peligros.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_matriz_vulnerabilidad.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_plan_emergencia.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_reporte_capacitacion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_asistencia_induccion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_vigilante.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_todero.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_aseadora.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_auditoria_zona_residuos.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_preparacion_simulacro.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_evaluacion_simulacro.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_hv_brigadista.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_carta_vigia.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_firma_digital.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_codigo_verificacion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_pta_audit_table.php
```

### Ejecución
```bash
cd app/SQL
php migrate_NOMBRE.php              # LOCAL
DB_PROD_PASS=xxx php migrate_NOMBRE.php production   # PRODUCCIÓN
```

---

## 5. RUTAS — `c:\xampp\htdocs\enterprisesstph\app\Config\Routes.php`

### Grupo autenticado: `/inspecciones` (namespace `App\Controllers\Inspecciones`, filter `auth`)

```php
$routes->group('inspecciones', ['namespace' => 'App\Controllers\Inspecciones', 'filter' => 'auth'], function($routes) {

    // Dashboard
    $routes->get('/', 'InspeccionesController::dashboard');
    $routes->get('api/clientes', 'InspeccionesController::getClientes');

    // Acta de Visita (~12 rutas)
    $routes->get('acta-visita', 'ActaVisitaController::list');
    $routes->get('acta-visita/create', 'ActaVisitaController::create');
    $routes->get('acta-visita/create/(:num)', 'ActaVisitaController::create/$1');
    $routes->post('acta-visita/store', 'ActaVisitaController::store');
    $routes->get('acta-visita/edit/(:num)', 'ActaVisitaController::edit/$1');
    $routes->post('acta-visita/update/(:num)', 'ActaVisitaController::update/$1');
    $routes->get('acta-visita/view/(:num)', 'ActaVisitaController::view/$1');
    $routes->get('acta-visita/pdf/(:num)', 'ActaVisitaController::generatePdf/$1');
    $routes->post('acta-visita/finalizar/(:num)', 'ActaVisitaController::finalizar/$1');
    $routes->get('acta-visita/delete/(:num)', 'ActaVisitaController::delete/$1');
    $routes->post('acta-visita/enviar-firma/(:num)', 'ActaVisitaController::enviarFirma/$1');
    $routes->post('acta-visita/upload-foto', 'ActaVisitaController::uploadFoto');

    // Inspección Locativa (10 rutas)
    $routes->get('inspeccion-locativa', 'InspeccionLocativaController::list');
    $routes->get('inspeccion-locativa/create', 'InspeccionLocativaController::create');
    $routes->get('inspeccion-locativa/create/(:num)', 'InspeccionLocativaController::create/$1');
    $routes->post('inspeccion-locativa/store', 'InspeccionLocativaController::store');
    $routes->get('inspeccion-locativa/edit/(:num)', 'InspeccionLocativaController::edit/$1');
    $routes->post('inspeccion-locativa/update/(:num)', 'InspeccionLocativaController::update/$1');
    $routes->get('inspeccion-locativa/view/(:num)', 'InspeccionLocativaController::view/$1');
    $routes->get('inspeccion-locativa/pdf/(:num)', 'InspeccionLocativaController::generatePdf/$1');
    $routes->post('inspeccion-locativa/finalizar/(:num)', 'InspeccionLocativaController::finalizar/$1');
    $routes->get('inspeccion-locativa/delete/(:num)', 'InspeccionLocativaController::delete/$1');

    // Señalización (10 rutas)
    $routes->get('senalizacion', 'InspeccionSenalizacionController::list');
    $routes->get('senalizacion/create', 'InspeccionSenalizacionController::create');
    $routes->get('senalizacion/create/(:num)', 'InspeccionSenalizacionController::create/$1');
    $routes->post('senalizacion/store', 'InspeccionSenalizacionController::store');
    $routes->get('senalizacion/edit/(:num)', 'InspeccionSenalizacionController::edit/$1');
    $routes->post('senalizacion/update/(:num)', 'InspeccionSenalizacionController::update/$1');
    $routes->get('senalizacion/view/(:num)', 'InspeccionSenalizacionController::view/$1');
    $routes->get('senalizacion/pdf/(:num)', 'InspeccionSenalizacionController::generatePdf/$1');
    $routes->post('senalizacion/finalizar/(:num)', 'InspeccionSenalizacionController::finalizar/$1');
    $routes->get('senalizacion/delete/(:num)', 'InspeccionSenalizacionController::delete/$1');

    // Extintores (10 rutas)
    $routes->get('extintores', 'InspeccionExtintoresController::list');
    $routes->get('extintores/create', 'InspeccionExtintoresController::create');
    $routes->get('extintores/create/(:num)', 'InspeccionExtintoresController::create/$1');
    $routes->post('extintores/store', 'InspeccionExtintoresController::store');
    $routes->get('extintores/edit/(:num)', 'InspeccionExtintoresController::edit/$1');
    $routes->post('extintores/update/(:num)', 'InspeccionExtintoresController::update/$1');
    $routes->get('extintores/view/(:num)', 'InspeccionExtintoresController::view/$1');
    $routes->get('extintores/pdf/(:num)', 'InspeccionExtintoresController::generatePdf/$1');
    $routes->post('extintores/finalizar/(:num)', 'InspeccionExtintoresController::finalizar/$1');
    $routes->get('extintores/delete/(:num)', 'InspeccionExtintoresController::delete/$1');

    // Botiquín (10 rutas)
    $routes->get('botiquin', 'InspeccionBotiquinController::list');
    $routes->get('botiquin/create', 'InspeccionBotiquinController::create');
    $routes->get('botiquin/create/(:num)', 'InspeccionBotiquinController::create/$1');
    $routes->post('botiquin/store', 'InspeccionBotiquinController::store');
    $routes->get('botiquin/edit/(:num)', 'InspeccionBotiquinController::edit/$1');
    $routes->post('botiquin/update/(:num)', 'InspeccionBotiquinController::update/$1');
    $routes->get('botiquin/view/(:num)', 'InspeccionBotiquinController::view/$1');
    $routes->get('botiquin/pdf/(:num)', 'InspeccionBotiquinController::generatePdf/$1');
    $routes->post('botiquin/finalizar/(:num)', 'InspeccionBotiquinController::finalizar/$1');
    $routes->get('botiquin/delete/(:num)', 'InspeccionBotiquinController::delete/$1');

    // Gabinetes (10 rutas)
    $routes->get('gabinetes', 'InspeccionGabineteController::list');
    $routes->get('gabinetes/create', 'InspeccionGabineteController::create');
    $routes->get('gabinetes/create/(:num)', 'InspeccionGabineteController::create/$1');
    $routes->post('gabinetes/store', 'InspeccionGabineteController::store');
    $routes->get('gabinetes/edit/(:num)', 'InspeccionGabineteController::edit/$1');
    $routes->post('gabinetes/update/(:num)', 'InspeccionGabineteController::update/$1');
    $routes->get('gabinetes/view/(:num)', 'InspeccionGabineteController::view/$1');
    $routes->get('gabinetes/pdf/(:num)', 'InspeccionGabineteController::generatePdf/$1');
    $routes->post('gabinetes/finalizar/(:num)', 'InspeccionGabineteController::finalizar/$1');
    $routes->get('gabinetes/delete/(:num)', 'InspeccionGabineteController::delete/$1');

    // Comunicaciones (10 rutas)
    $routes->get('comunicaciones', 'InspeccionComunicacionController::list');
    $routes->get('comunicaciones/create', 'InspeccionComunicacionController::create');
    $routes->get('comunicaciones/create/(:num)', 'InspeccionComunicacionController::create/$1');
    $routes->post('comunicaciones/store', 'InspeccionComunicacionController::store');
    $routes->get('comunicaciones/edit/(:num)', 'InspeccionComunicacionController::edit/$1');
    $routes->post('comunicaciones/update/(:num)', 'InspeccionComunicacionController::update/$1');
    $routes->get('comunicaciones/view/(:num)', 'InspeccionComunicacionController::view/$1');
    $routes->get('comunicaciones/pdf/(:num)', 'InspeccionComunicacionController::generatePdf/$1');
    $routes->post('comunicaciones/finalizar/(:num)', 'InspeccionComunicacionController::finalizar/$1');
    $routes->get('comunicaciones/delete/(:num)', 'InspeccionComunicacionController::delete/$1');

    // Recursos de Seguridad (10 rutas)
    $routes->get('recursos-seguridad', 'InspeccionRecursosSeguridadController::list');
    $routes->get('recursos-seguridad/create', 'InspeccionRecursosSeguridadController::create');
    $routes->get('recursos-seguridad/create/(:num)', 'InspeccionRecursosSeguridadController::create/$1');
    $routes->post('recursos-seguridad/store', 'InspeccionRecursosSeguridadController::store');
    $routes->get('recursos-seguridad/edit/(:num)', 'InspeccionRecursosSeguridadController::edit/$1');
    $routes->post('recursos-seguridad/update/(:num)', 'InspeccionRecursosSeguridadController::update/$1');
    $routes->get('recursos-seguridad/view/(:num)', 'InspeccionRecursosSeguridadController::view/$1');
    $routes->get('recursos-seguridad/pdf/(:num)', 'InspeccionRecursosSeguridadController::generatePdf/$1');
    $routes->post('recursos-seguridad/finalizar/(:num)', 'InspeccionRecursosSeguridadController::finalizar/$1');
    $routes->get('recursos-seguridad/delete/(:num)', 'InspeccionRecursosSeguridadController::delete/$1');

    // Probabilidad de Peligros (10 rutas)
    $routes->get('probabilidad-peligros', 'ProbabilidadPeligrosController::list');
    $routes->get('probabilidad-peligros/create', 'ProbabilidadPeligrosController::create');
    $routes->get('probabilidad-peligros/create/(:num)', 'ProbabilidadPeligrosController::create/$1');
    $routes->post('probabilidad-peligros/store', 'ProbabilidadPeligrosController::store');
    $routes->get('probabilidad-peligros/edit/(:num)', 'ProbabilidadPeligrosController::edit/$1');
    $routes->post('probabilidad-peligros/update/(:num)', 'ProbabilidadPeligrosController::update/$1');
    $routes->get('probabilidad-peligros/view/(:num)', 'ProbabilidadPeligrosController::view/$1');
    $routes->get('probabilidad-peligros/pdf/(:num)', 'ProbabilidadPeligrosController::generatePdf/$1');
    $routes->post('probabilidad-peligros/finalizar/(:num)', 'ProbabilidadPeligrosController::finalizar/$1');
    $routes->get('probabilidad-peligros/delete/(:num)', 'ProbabilidadPeligrosController::delete/$1');

    // Matriz de Vulnerabilidad (10 rutas)
    $routes->get('matriz-vulnerabilidad', 'MatrizVulnerabilidadController::list');
    $routes->get('matriz-vulnerabilidad/create', 'MatrizVulnerabilidadController::create');
    $routes->get('matriz-vulnerabilidad/create/(:num)', 'MatrizVulnerabilidadController::create/$1');
    $routes->post('matriz-vulnerabilidad/store', 'MatrizVulnerabilidadController::store');
    $routes->get('matriz-vulnerabilidad/edit/(:num)', 'MatrizVulnerabilidadController::edit/$1');
    $routes->post('matriz-vulnerabilidad/update/(:num)', 'MatrizVulnerabilidadController::update/$1');
    $routes->get('matriz-vulnerabilidad/view/(:num)', 'MatrizVulnerabilidadController::view/$1');
    $routes->get('matriz-vulnerabilidad/pdf/(:num)', 'MatrizVulnerabilidadController::generatePdf/$1');
    $routes->post('matriz-vulnerabilidad/finalizar/(:num)', 'MatrizVulnerabilidadController::finalizar/$1');
    $routes->get('matriz-vulnerabilidad/delete/(:num)', 'MatrizVulnerabilidadController::delete/$1');

    // Plan de Emergencia (12 rutas — incluye check-inspecciones)
    $routes->get('plan-emergencia', 'PlanEmergenciaController::list');
    $routes->get('plan-emergencia/create', 'PlanEmergenciaController::create');
    $routes->get('plan-emergencia/create/(:num)', 'PlanEmergenciaController::create/$1');
    $routes->post('plan-emergencia/store', 'PlanEmergenciaController::store');
    $routes->get('plan-emergencia/edit/(:num)', 'PlanEmergenciaController::edit/$1');
    $routes->post('plan-emergencia/update/(:num)', 'PlanEmergenciaController::update/$1');
    $routes->get('plan-emergencia/view/(:num)', 'PlanEmergenciaController::view/$1');
    $routes->get('plan-emergencia/pdf/(:num)', 'PlanEmergenciaController::generatePdf/$1');
    $routes->post('plan-emergencia/finalizar/(:num)', 'PlanEmergenciaController::finalizar/$1');
    $routes->get('plan-emergencia/delete/(:num)', 'PlanEmergenciaController::delete/$1');
    $routes->get('plan-emergencia/check-inspecciones/(:num)', 'PlanEmergenciaController::checkInspeccionesCompletas/$1');

    // Evaluación Simulacro (5 rutas — solo lectura, viene de formulario público)
    $routes->get('simulacro', 'EvaluacionSimulacroController::list');
    $routes->get('simulacro/view/(:num)', 'EvaluacionSimulacroController::view/$1');
    $routes->get('simulacro/pdf/(:num)', 'EvaluacionSimulacroController::generatePdf/$1');
    $routes->post('simulacro/finalizar/(:num)', 'EvaluacionSimulacroController::finalizar/$1');
    $routes->get('simulacro/delete/(:num)', 'EvaluacionSimulacroController::delete/$1');

    // Hoja de Vida Brigadista (5 rutas — solo lectura, viene de formulario público)
    $routes->get('hv-brigadista', 'HvBrigadistaController::list');
    $routes->get('hv-brigadista/view/(:num)', 'HvBrigadistaController::view/$1');
    $routes->get('hv-brigadista/pdf/(:num)', 'HvBrigadistaController::generatePdf/$1');
    $routes->post('hv-brigadista/finalizar/(:num)', 'HvBrigadistaController::finalizar/$1');
    $routes->get('hv-brigadista/delete/(:num)', 'HvBrigadistaController::delete/$1');

    // Pendientes / Compromisos
    $routes->get('pendientes', 'PendientesPwaController::list');
    $routes->get('pendientes/cliente/(:num)', 'PendientesPwaController::list/$1');
    $routes->get('pendientes/create/(:num)', 'PendientesPwaController::create/$1');
    $routes->post('pendientes/store', 'PendientesPwaController::store');
    $routes->get('pendientes/edit/(:num)', 'PendientesPwaController::edit/$1');
    $routes->post('pendientes/update/(:num)', 'PendientesPwaController::update/$1');
    $routes->get('pendientes/delete/(:num)', 'PendientesPwaController::delete/$1');

    // Mantenimientos
    $routes->get('mantenimientos', 'MantenimientosPwaController::list');
    $routes->get('api/mantenimientos-catalog', 'MantenimientosPwaController::apiCatalog');
    $routes->get('api/vencimientos/(:num)', 'MantenimientosPwaController::apiVencimientos/$1');

    // Carta Vigía
    $routes->get('carta-vigia', 'CartaVigiaPwaController::list');
    // ... (más rutas carta vigía)
});
```

### Rutas públicas (SIN autenticación)

```php
// Formulario público Evaluación Simulacro
$routes->get('simulacro', 'SimulacroPublicoController::form');
$routes->get('simulacro/api/clientes', 'SimulacroPublicoController::getClientesActivos');
$routes->post('simulacro/save-step', 'SimulacroPublicoController::saveStep');
$routes->post('simulacro/upload-foto', 'SimulacroPublicoController::uploadFoto');
$routes->post('simulacro/store', 'SimulacroPublicoController::store');

// Formulario público HV Brigadista
$routes->get('hv-brigadista', 'HvBrigadistaPublicoController::form');
$routes->get('hv-brigadista/api/clientes', 'HvBrigadistaPublicoController::getClientesActivos');
$routes->post('hv-brigadista/store', 'HvBrigadistaPublicoController::store');

// Carta Vigía pública (firma)
$routes->get('carta-vigia/firmar/(:any)', 'Inspecciones\CartaVigiaPwaController::firmar/$1');
$routes->post('carta-vigia/procesar-firma', 'Inspecciones\CartaVigiaPwaController::procesarFirma');
$routes->get('carta-vigia/verificar/(:any)', 'Inspecciones\CartaVigiaPwaController::verificar/$1');
```

---

## 6. ASSETS PWA

```
c:\xampp\htdocs\enterprisesstph\public\manifest_inspecciones.json
c:\xampp\htdocs\enterprisesstph\public\sw_inspecciones.js
c:\xampp\htdocs\enterprisesstph\public\icons\icon-192.png
c:\xampp\htdocs\enterprisesstph\public\icons\icon-512.png
```

---

## 7. DOCUMENTACIÓN (toda la carpeta docs/)

```
c:\xampp\htdocs\enterprisesstph\docs\00_PLAN_MAESTRO.md
c:\xampp\htdocs\enterprisesstph\docs\01_ACTA_DE_VISITA.md
c:\xampp\htdocs\enterprisesstph\docs\02_DB_ACTA_VISITA.md
c:\xampp\htdocs\enterprisesstph\docs\03_PWA_LAYOUT.md
c:\xampp\htdocs\enterprisesstph\docs\04_ESTRATEGIA_FIRMAS.md
c:\xampp\htdocs\enterprisesstph\docs\05_ESTRATEGIA_OFFLINE.md
c:\xampp\htdocs\enterprisesstph\docs\06_ESTRATEGIA_NOTIFICACIONES.md
c:\xampp\htdocs\enterprisesstph\docs\07_ESTRATEGIA_PDF_UPLOAD.md
c:\xampp\htdocs\enterprisesstph\docs\08_ESTRATEGIA_AUTOGUARDADO.md
c:\xampp\htdocs\enterprisesstph\docs\09_DISENO_PDF_ACTA.md
c:\xampp\htdocs\enterprisesstph\docs\10_INSPECCION_LOCATIVA.md
c:\xampp\htdocs\enterprisesstph\docs\11_INPUT_FILE_CAMARA_GALERIA.md
c:\xampp\htdocs\enterprisesstph\docs\12_PATRON_INSPECCION_PLANA.md
c:\xampp\htdocs\enterprisesstph\docs\13_PATRON_INSPECCION_NITEMS.md
c:\xampp\htdocs\enterprisesstph\docs\14_PLAN_EMERGENCIA.md
c:\xampp\htdocs\enterprisesstph\docs\15_PATRON_DOCUMENTO_MAESTRO.md
c:\xampp\htdocs\enterprisesstph\docs\16_REPORTE_CAPACITACION.md
c:\xampp\htdocs\enterprisesstph\docs\17_ASISTENCIA_INDUCCION.md
c:\xampp\htdocs\enterprisesstph\docs\18_DOTACION_VIGILANTE.md
c:\xampp\htdocs\enterprisesstph\docs\19_DOTACION_TODERO.md
c:\xampp\htdocs\enterprisesstph\docs\20_DOTACION_ASEADORA.md
c:\xampp\htdocs\enterprisesstph\docs\21_AUDITORIA_ZONA_RESIDUOS.md
c:\xampp\htdocs\enterprisesstph\docs\22_PREPARACION_SIMULACRO.md
c:\xampp\htdocs\enterprisesstph\docs\integracion_vencimientos_inspecciones.md
c:\xampp\htdocs\enterprisesstph\docs\cambios-tabla-pta-cliente-nueva.md
```

---

## 8. ARCHIVOS DE REFERENCIA / TEXTO ESTÁTICO

```
c:\xampp\htdocs\enterprisesstph\y_appscriptbrigadista.txt
c:\xampp\htdocs\enterprisesstph\z_asistentes.txt
c:\xampp\htdocs\enterprisesstph\z_dotacion_vigilante.txt
c:\xampp\htdocs\enterprisesstph\z_plandeemergencia.txt
c:\xampp\htdocs\enterprisesstph\z_responsabilidadessst.txt
```

---

## 9. ESTRUCTURA DE CARPETAS COMPLETA

```
c:\xampp\htdocs\enterprisesstph\
├── app\
│   ├── Config\
│   │   └── Routes.php
│   ├── Controllers\
│   │   ├── Inspecciones\
│   │   │   ├── InspeccionesController.php              (dashboard)
│   │   │   ├── ActaVisitaController.php
│   │   │   ├── InspeccionLocativaController.php
│   │   │   ├── InspeccionSenalizacionController.php
│   │   │   ├── InspeccionExtintoresController.php
│   │   │   ├── InspeccionBotiquinController.php
│   │   │   ├── InspeccionGabineteController.php
│   │   │   ├── InspeccionComunicacionController.php
│   │   │   ├── InspeccionRecursosSeguridadController.php
│   │   │   ├── ProbabilidadPeligrosController.php
│   │   │   ├── MatrizVulnerabilidadController.php
│   │   │   ├── PlanEmergenciaController.php
│   │   │   ├── EvaluacionSimulacroController.php
│   │   │   ├── HvBrigadistaController.php
│   │   │   ├── CartaVigiaPwaController.php
│   │   │   ├── MantenimientosPwaController.php
│   │   │   └── PendientesPwaController.php
│   │   ├── SimulacroPublicoController.php              (público)
│   │   └── HvBrigadistaPublicoController.php           (público)
│   ├── Models\
│   │   ├── ActaVisitaModel.php
│   │   ├── ActaVisitaFotoModel.php
│   │   ├── ActaVisitaIntegranteModel.php
│   │   ├── ActaVisitaTemaModel.php
│   │   ├── InspeccionLocativaModel.php
│   │   ├── HallazgoLocativoModel.php
│   │   ├── InspeccionSenalizacionModel.php
│   │   ├── ItemSenalizacionModel.php
│   │   ├── InspeccionExtintoresModel.php
│   │   ├── ExtintorDetalleModel.php
│   │   ├── InspeccionBotiquinModel.php
│   │   ├── ElementoBotiquinModel.php
│   │   ├── InspeccionGabineteModel.php
│   │   ├── GabineteDetalleModel.php
│   │   ├── InspeccionComunicacionModel.php
│   │   ├── InspeccionRecursosSeguridadModel.php
│   │   ├── ProbabilidadPeligrosModel.php
│   │   ├── MatrizVulnerabilidadModel.php
│   │   ├── PlanEmergenciaModel.php
│   │   ├── EvaluacionSimulacroModel.php
│   │   ├── HvBrigadistaModel.php
│   │   ├── CartaVigiaModel.php
│   │   ├── MantenimientoModel.php
│   │   ├── VencimientosMantenimientoModel.php
│   │   └── PendientesModel.php
│   ├── SQL\
│   │   ├── migrate_acta_visita.php
│   │   ├── migrate_inspeccion_locativa.php
│   │   ├── migrate_inspeccion_senalizacion.php
│   │   ├── migrate_inspeccion_extintores.php
│   │   ├── migrate_inspeccion_botiquin.php
│   │   ├── migrate_inspeccion_gabinetes.php
│   │   ├── migrate_inspeccion_comunicaciones.php
│   │   ├── migrate_inspeccion_recursos_seguridad.php
│   │   ├── migrate_probabilidad_peligros.php
│   │   ├── migrate_matriz_vulnerabilidad.php
│   │   ├── migrate_plan_emergencia.php
│   │   ├── migrate_reporte_capacitacion.php
│   │   ├── migrate_asistencia_induccion.php
│   │   ├── migrate_dotacion_vigilante.php
│   │   ├── migrate_dotacion_todero.php
│   │   ├── migrate_dotacion_aseadora.php
│   │   ├── migrate_auditoria_zona_residuos.php
│   │   ├── migrate_preparacion_simulacro.php
│   │   ├── migrate_evaluacion_simulacro.php
│   │   ├── migrate_hv_brigadista.php
│   │   ├── migrate_carta_vigia.php
│   │   ├── migrate_firma_digital.php
│   │   ├── migrate_codigo_verificacion.php
│   │   └── migrate_pta_audit_table.php
│   └── Views\
│       ├── inspecciones\
│       │   ├── layout_pwa.php                          (layout compartido)
│       │   ├── dashboard.php
│       │   ├── acta_visita\        (5: list, form, view, pdf, firma)
│       │   ├── inspeccion_locativa\ (4: list, form, view, pdf)
│       │   ├── senalizacion\       (4: list, form, view, pdf)
│       │   ├── extintores\         (5: list, form, view, pdf, _extintor_row)
│       │   ├── botiquin\           (4: list, form, view, pdf)
│       │   ├── gabinetes\          (4: list, form, view, pdf)
│       │   ├── comunicaciones\     (4: list, form, view, pdf)
│       │   ├── recursos-seguridad\ (4: list, form, view, pdf)
│       │   ├── probabilidad-peligros\ (4: list, form, view, pdf)
│       │   ├── matriz-vulnerabilidad\ (4: list, form, view, pdf)
│       │   ├── plan-emergencia\    (4: list, form, view, pdf)
│       │   ├── simulacro\          (3: list, view, pdf)
│       │   ├── hv-brigadista\      (3: list, view, pdf)
│       │   ├── carta_vigia\        (7: list, form, pdf, firma, firma_error, firma_success, email_firma)
│       │   ├── mantenimientos\     (2: list, form)
│       │   └── pendientes\         (2: list, form)
│       ├── simulacro\
│       │   └── form_publico.php                        (formulario público)
│       └── hv-brigadista\
│           └── form_publico.php                        (formulario público)
├── public\
│   ├── manifest_inspecciones.json
│   ├── sw_inspecciones.js
│   └── icons\
│       ├── icon-192.png
│       └── icon-512.png
└── docs\
    ├── 00_PLAN_MAESTRO.md ... 22_PREPARACION_SIMULACRO.md
    └── (25 archivos de documentación)
```

---

## INSTRUCCIONES PARA REPLICAR

1. **Copiar TODOS los archivos** listados arriba a las mismas rutas relativas en el proyecto gemelo
2. **Ejecutar las 24 migraciones SQL** en el orden listado (primero LOCAL, luego PRODUCCIÓN)
3. **Verificar** que todas las tablas existen con el número correcto de columnas
4. **Assets PWA**: copiar manifest, service worker e iconos a `public/`
5. **Permisos carpetas uploads** en producción:
   - `uploads/inspecciones/` y subcarpetas → 775, owner www
   - `uploads/firmas/` → 775, owner www
