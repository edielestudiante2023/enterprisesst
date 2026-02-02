# Plan de Refactorización: Sistema de Carpetas Documentación SST

## Estado Actual

- **Archivo monolítico:** `carpeta.php` con 1,040+ líneas
- **Tipos de carpeta:** 6 (5 especiales + 1 genérica)
- **Documentos existentes:** Pocos (buen momento para refactorizar)
- **Riesgo:** BAJO si se hace ahora, ALTO si se pospone

---

## ¿Qué pasa con lo construido?

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  LO CONSTRUIDO SE MANTIENE 100%                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ✓ Base de datos         → NO SE TOCA (mismas tablas, mismos datos)        │
│  ✓ Controladores         → CAMBIOS MÍNIMOS (solo qué vista cargar)         │
│  ✓ Modelos               → NO SE TOCAN                                      │
│  ✓ Rutas                 → NO SE TOCAN                                      │
│  ✓ Documentos existentes → SIGUEN FUNCIONANDO IGUAL                        │
│  ✓ URLs                  → NO CAMBIAN (/documentacion/carpeta/166)         │
│                                                                             │
│  SOLO CAMBIA: Cómo se organiza el código de las vistas                     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Estructura Propuesta

```
app/Views/documentacion/
├── carpeta.php                    # Vista base (layout común) ~150 líneas
├── _components/                   # Componentes reutilizables
│   ├── header.php                 # Navbar + breadcrumb
│   ├── card_carpeta.php           # Card del título de carpeta
│   ├── tabla_documentos_sst.php   # Tabla de documentos SST
│   ├── panel_fases.php            # Timeline de fases
│   ├── lista_subcarpetas.php      # Grid de subcarpetas
│   ├── lista_documentos.php       # Tabla de documentos genéricos
│   └── modal_adjuntar.php         # Modal para adjuntar escaneados
└── _tipos/                        # Contenido específico por tipo
    ├── responsables_sst.php       # 1.1.1 - Botón + lógica específica
    ├── responsabilidades_sgsst.php# 1.1.2 - Dropdown 3 documentos
    ├── presupuesto_sst.php        # 1.1.3 - Botón presupuesto
    ├── capacitacion_sst.php       # 1.2.1 - Botón IA + fases
    ├── archivo_documental.php     # 2.5.1 - Maestra documentos
    └── generica.php               # Carpetas sin tipo especial
```

---

## Plan de Ejecución (5 Fases)

### FASE 1: Preparación (30 min)
**Objetivo:** Crear estructura sin romper nada

```bash
# Crear directorios
mkdir app/Views/documentacion/_components
mkdir app/Views/documentacion/_tipos
```

**Archivos a crear:**
- [ ] `_components/header.php` (vacío por ahora)
- [ ] `_components/tabla_documentos_sst.php` (vacío)
- [ ] `_tipos/generica.php` (vacío)

**Verificación:** La aplicación sigue funcionando igual

---

### FASE 2: Extraer Componentes Comunes (1-2 horas)
**Objetivo:** Mover HTML reutilizable a componentes

#### 2.1 Extraer Header (navbar + breadcrumb)
```php
// _components/header.php
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <!-- ... contenido del navbar ... -->
</nav>
<nav aria-label="breadcrumb" class="mb-4">
    <!-- ... contenido del breadcrumb ... -->
</nav>
```

#### 2.2 Extraer Card de Carpeta
```php
// _components/card_carpeta.php
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-folder-fill text-warning me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <!-- ... -->
            </div>
            <div class="col-md-4 text-end">
                <?= $slot_botones ?? '' ?>  <!-- Aquí va el contenido específico -->
            </div>
        </div>
    </div>
</div>
```

#### 2.3 Extraer Tabla de Documentos SST
```php
// _components/tabla_documentos_sst.php
<?php if (!empty($documentosSSTAprobados)): ?>
<div class="table-responsive">
    <table class="table table-hover mb-0">
        <!-- ... toda la tabla ... -->
    </table>
</div>
<?php else: ?>
<div class="text-center py-4">
    <!-- mensaje vacío -->
</div>
<?php endif; ?>
```

#### 2.4 Extraer Panel de Fases
```php
// _components/panel_fases.php
<?php if (isset($fasesInfo) && $fasesInfo && $fasesInfo['tiene_fases']): ?>
<div class="fases-panel">
    <!-- ... todo el panel de fases ... -->
</div>
<?php endif; ?>
```

**Verificación después de cada extracción:** La aplicación sigue funcionando

---

### FASE 3: Crear Vistas por Tipo (2-3 horas)
**Objetivo:** Separar la lógica específica de cada tipo

#### 3.1 Crear vista para tipo genérica
```php
// _tipos/generica.php
<?php
// Botones del header
$slot_botones = '
<a href="' . base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) . '"
   class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
</a>';
?>

<!-- Incluir componentes -->
<?= view('documentacion/_components/card_carpeta', compact('carpeta', 'slot_botones')) ?>

<!-- Contenido específico de carpeta genérica -->
<?= view('documentacion/_components/lista_subcarpetas', compact('subcarpetas')) ?>
<?= view('documentacion/_components/lista_documentos', compact('documentos', 'cliente')) ?>
```

#### 3.2 Crear vista para responsabilidades_sgsst
```php
// _tipos/responsabilidades_sgsst.php
<?php
$nivelEstandares = $contextoCliente['estandares_aplicables'] ?? 60;
$esVigia = $nivelEstandares <= 7;
$nombreDocRepLegal = $esVigia ? 'Resp. Rep. Legal + Vigia SST' : 'Resp. Rep. Legal + Delegado SST';
// ... lógica del dropdown ...

$slot_botones = '<div class="dropdown">...</div>';
?>

<?= view('documentacion/_components/card_carpeta', compact('carpeta', 'slot_botones')) ?>
<?= view('documentacion/_components/panel_fases', compact('fasesInfo', 'tipoCarpetaFases', 'cliente')) ?>
<?= view('documentacion/_components/tabla_documentos_sst', compact('documentosSSTAprobados', 'cliente', 'tipoCarpetaFases')) ?>
```

#### 3.3 Repetir para cada tipo
- [ ] `responsables_sst.php`
- [ ] `presupuesto_sst.php`
- [ ] `capacitacion_sst.php`
- [ ] `archivo_documental.php`

---

### FASE 4: Modificar Controlador (30 min)
**Objetivo:** Cargar la vista correcta según el tipo

```php
// DocumentacionController.php - método carpeta()

public function carpeta($idCarpeta)
{
    // ... código existente para obtener datos ...

    $tipoCarpetaFases = $this->determinarTipoCarpetaFases($carpeta);

    // NUEVO: Determinar qué vista cargar
    $vistaTipo = $tipoCarpetaFases ?? 'generica';
    $vistaPath = "documentacion/_tipos/{$vistaTipo}";

    // Verificar que existe, si no usar genérica
    if (!file_exists(APPPATH . "Views/{$vistaPath}.php")) {
        $vistaPath = 'documentacion/_tipos/generica';
    }

    // Datos comunes para todas las vistas
    $data = [
        'carpeta' => $carpeta,
        'cliente' => $cliente,
        'tipoCarpetaFases' => $tipoCarpetaFases,
        'documentosSSTAprobados' => $documentosSSTAprobados,
        'fasesInfo' => $fasesInfo,
        'subcarpetas' => $subcarpetas,
        'documentos' => $documentos,
        // ... otros datos ...
    ];

    // Cargar vista base con la vista específica del tipo
    return view('documentacion/carpeta', [
        'vistaContenido' => $vistaPath,
        ...$data
    ]);
}
```

---

### FASE 5: Refactorizar Vista Base (1 hora)
**Objetivo:** carpeta.php solo como layout

```php
// carpeta.php (NUEVO - solo ~100 líneas)
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($carpeta['nombre']) ?> - Documentacion SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/documentacion.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">

    <?= view('documentacion/_components/header', compact('cliente', 'carpeta', 'ruta')) ?>

    <div class="container-fluid py-4">
        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Contenido específico del tipo de carpeta -->
        <?= view($vistaContenido, $data) ?>

    </div>

    <!-- Modales comunes -->
    <?= view('documentacion/_components/modal_adjuntar') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/documentacion.js') ?>"></script>
</body>
</html>
```

---

## Cronograma Sugerido

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  OPCIÓN A: Refactorización Completa (1 día)                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Mañana (4 horas):                                                          │
│  ├─ FASE 1: Preparación                           (30 min)                  │
│  ├─ FASE 2: Extraer componentes                   (2 horas)                 │
│  └─ FASE 3: Crear 2-3 vistas por tipo             (1.5 horas)              │
│                                                                             │
│  Tarde (4 horas):                                                           │
│  ├─ FASE 3: Completar vistas restantes            (2 horas)                 │
│  ├─ FASE 4: Modificar controlador                 (30 min)                  │
│  ├─ FASE 5: Refactorizar vista base               (1 hora)                  │
│  └─ Testing completo                              (30 min)                  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│  OPCIÓN B: Refactorización Gradual (1-2 semanas)                            │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Semana 1:                                                                  │
│  ├─ Día 1: FASE 1 + FASE 2 (componentes comunes)                           │
│  ├─ Día 2: Extraer 1 tipo (el más simple: generica)                        │
│  └─ Día 3-5: Seguir trabajando normal, el resto sigue en carpeta.php       │
│                                                                             │
│  Semana 2:                                                                  │
│  ├─ Cada vez que toques un tipo, extráelo                                  │
│  └─ Al final de la semana: todo migrado                                    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Checklist de Verificación

### Después de cada cambio:
- [ ] /documentacion/carpeta/164 (responsables_sst) funciona
- [ ] /documentacion/carpeta/166 (responsabilidades_sgsst) funciona
- [ ] /documentacion/carpeta/167 (presupuesto_sst) funciona
- [ ] /documentacion/carpeta/168 (capacitacion_sst) funciona
- [ ] /documentacion/carpeta/177 (archivo_documental) funciona
- [ ] Carpeta genérica funciona
- [ ] Crear documento funciona
- [ ] Ver documento funciona
- [ ] Descargar PDF funciona
- [ ] Firmas funcionan

### Al finalizar:
- [ ] carpeta.php tiene menos de 150 líneas
- [ ] Cada vista de tipo tiene menos de 200 líneas
- [ ] No hay código duplicado
- [ ] CSS extraído a archivo separado
- [ ] JS extraído a archivo separado

---

## Rollback Plan

Si algo sale mal durante la refactorización:

```bash
# El archivo original está en git
git checkout HEAD -- app/Views/documentacion/carpeta.php

# O si hiciste backup
cp app/Views/documentacion/carpeta.php.backup app/Views/documentacion/carpeta.php
```

---

## Beneficios Post-Refactorización

| Antes | Después |
|-------|---------|
| 1 archivo de 1040 líneas | 10+ archivos de ~150 líneas |
| Cambio en presupuesto puede romper capacitación | Cambios aislados |
| CSS/JS mezclado en HTML | Archivos separados, cacheables |
| Imposible testing | Testing por componente |
| 1 desarrollador a la vez | Trabajo paralelo posible |
| Deploy arriesgado | Deploy seguro |

---

## ¿Cuándo hacerlo?

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  RECOMENDACIÓN                                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  AHORA es el mejor momento porque:                                          │
│                                                                             │
│  ✓ Pocos documentos existentes (bajo riesgo)                               │
│  ✓ Ya conoces la estructura (el incidente del Excel te enseñó)             │
│  ✓ El costo de refactorizar AUMENTA con el tiempo                          │
│  ✓ 1 día ahora vs 1 semana después vs 1 mes con 100 empresas              │
│                                                                             │
│  Si no puedes ahora, al menos:                                              │
│  - Haz la FASE 1 y 2 (crear estructura, extraer componentes)               │
│  - Esto reduce el archivo a ~600 líneas                                    │
│  - El resto puede esperar                                                   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

**Última actualización:** 2026-02-01
