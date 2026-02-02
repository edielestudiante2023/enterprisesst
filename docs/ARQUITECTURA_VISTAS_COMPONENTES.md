# Arquitectura de Vistas por Componentes y Tipos

## Resumen

Este documento explica el patrón de arquitectura implementado para manejar múltiples tipos de vistas desde una sola URL, utilizando componentes reutilizables y vistas específicas por tipo.

**Problema resuelto:** Un archivo monolítico de 1,000+ líneas que manejaba 6 tipos diferentes de carpetas con condicionales `if/elseif` anidados.

**Solución:** Separación en componentes reutilizables + vistas específicas por tipo + layout base.

---

## Estructura de Archivos

```
app/Views/{modulo}/
├── {vista_principal}.php          # Layout base (~80 líneas)
├── _components/                   # Componentes reutilizables
│   ├── styles.php                 # CSS del módulo
│   ├── header.php                 # Navbar + breadcrumb
│   ├── alertas.php                # Mensajes flash
│   ├── {componente_1}.php         # Componente específico
│   ├── {componente_2}.php         # Componente específico
│   └── scripts.php                # JavaScript del módulo
└── _tipos/                        # Vistas por tipo
    ├── generica.php               # Tipo por defecto
    ├── {tipo_1}.php               # Vista para tipo 1
    ├── {tipo_2}.php               # Vista para tipo 2
    └── {tipo_n}.php               # Vista para tipo N
```

### Convención de nombres

- `_components/` y `_tipos/` usan guión bajo para indicar que son directorios de soporte
- Los archivos de componentes describen su función: `tabla_documentos.php`, `panel_fases.php`
- Los archivos de tipo usan el identificador del tipo: `responsables_sst.php`, `presupuesto_sst.php`

---

## Flujo de Datos

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  1. URL: /{modulo}/carpeta/{id}                                             │
│     Ejemplo: /documentacion/carpeta/166                                     │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  2. CONTROLADOR                                                             │
│                                                                             │
│  public function carpeta($id) {                                            │
│      $entidad = $this->model->find($id);                                   │
│      $tipo = $this->determinarTipo($entidad);  // ← Detecta el tipo        │
│                                                                             │
│      // Determinar vista a cargar                                          │
│      $vistaTipo = $tipo ?? 'generica';                                     │
│      $vistaPath = "modulo/_tipos/{$vistaTipo}";                            │
│                                                                             │
│      // Verificar que existe                                                │
│      if (!is_file(APPPATH . "Views/{$vistaPath}.php")) {                   │
│          $vistaPath = 'modulo/_tipos/generica';                            │
│      }                                                                      │
│                                                                             │
│      return view('modulo/vista_principal', [                               │
│          'entidad' => $entidad,                                            │
│          'tipo' => $tipo,                                                  │
│          'vistaContenido' => $vistaPath,  // ← Pasa la ruta de la vista   │
│          // ... otros datos                                                 │
│      ]);                                                                    │
│  }                                                                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  3. LAYOUT BASE (vista_principal.php)                                       │
│                                                                             │
│  <!DOCTYPE html>                                                            │
│  <html>                                                                     │
│  <head>                                                                     │
│      <?= view('modulo/_components/styles') ?>                              │
│  </head>                                                                    │
│  <body>                                                                     │
│      <?= view('modulo/_components/header', [...]) ?>                       │
│      <?= view('modulo/_components/alertas') ?>                             │
│                                                                             │
│      <!-- Contenido específico del tipo -->                                │
│      <?= view($vistaContenido, [...]) ?>  ← Carga la vista del tipo       │
│                                                                             │
│      <?= view('modulo/_components/modal') ?>                               │
│      <?= view('modulo/_components/scripts') ?>                             │
│  </body>                                                                    │
│  </html>                                                                    │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  4. VISTA DE TIPO (_tipos/responsables_sst.php)                            │
│                                                                             │
│  <!-- Card específica para este tipo -->                                   │
│  <div class="card">                                                         │
│      <h4><?= $entidad['nombre'] ?></h4>                                    │
│      <button>Acción específica de este tipo</button>                       │
│  </div>                                                                     │
│                                                                             │
│  <!-- Usa componentes reutilizables -->                                    │
│  <?= view('modulo/_components/panel_fases', [...]) ?>                      │
│  <?= view('modulo/_components/tabla_documentos', [...]) ?>                 │
│  <?= view('modulo/_components/lista_subcarpetas', [...]) ?>                │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Anatomía de un Componente

```php
<?php
/**
 * Componente: Nombre Descriptivo
 * Descripción breve de qué hace
 * Variables requeridas: $var1, $var2, $var3 (opcional)
 */

// Validación temprana - si no aplica, no renderizar nada
if (empty($datos_requeridos)) {
    return;
}
?>

<!-- HTML del componente -->
<div class="mi-componente">
    <?php foreach ($items as $item): ?>
        <div class="item">
            <?= esc($item['nombre']) ?>
        </div>
    <?php endforeach; ?>
</div>
```

### Principios de un buen componente

1. **Documentación al inicio**: Variables requeridas y opcionales
2. **Validación temprana**: Si no hay datos, no renderizar
3. **Escape de datos**: Siempre usar `esc()` para datos del usuario
4. **Sin lógica de negocio**: Solo presentación, la lógica va en el controlador
5. **Independiente**: No depende de variables globales

---

## Anatomía de una Vista de Tipo

```php
<?php
/**
 * Vista de Tipo: Nombre del Tipo
 * Código: X.X.X
 * Descripción de qué hace esta carpeta/entidad
 * Variables: $entidad, $cliente, $datos_especificos
 */

// Lógica específica de este tipo (mínima)
$hayDocumentoActual = false;
foreach ($documentos as $d) {
    if ($d['anio'] == date('Y')) {
        $hayDocumentoActual = true;
        break;
    }
}
?>

<!-- Card con UI específica de este tipo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4><?= esc($entidad['nombre']) ?></h4>
            </div>
            <div class="col-md-4 text-end">
                <!-- Botón específico de este tipo -->
                <?php if (!$hayDocumentoActual): ?>
                    <a href="<?= base_url('...') ?>" class="btn btn-success">
                        <i class="bi bi-plus"></i> Acción Específica
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Componentes reutilizables -->
<?= view('modulo/_components/panel_fases', [
    'fasesInfo' => $fasesInfo,
    'tipo' => 'mi_tipo',
    'cliente' => $cliente
]) ?>

<?= view('modulo/_components/tabla_documentos', [
    'documentos' => $documentos,
    'cliente' => $cliente
]) ?>

<?= view('modulo/_components/lista_subcarpetas', [
    'subcarpetas' => $subcarpetas ?? []
]) ?>
```

---

## Función de Detección de Tipo

```php
/**
 * Determina el tipo de entidad basado en sus propiedades
 * @param array $entidad La entidad a analizar
 * @return string|null El identificador del tipo o null para genérica
 */
protected function determinarTipo(array $entidad): ?string
{
    $nombre = strtolower($entidad['nombre'] ?? '');
    $codigo = strtolower($entidad['codigo'] ?? '');

    // Detección por código (más precisa)
    if ($codigo === '1.1.1') return 'responsables_sst';
    if ($codigo === '1.1.2') return 'responsabilidades_sgsst';
    if ($codigo === '1.1.3') return 'presupuesto_sst';
    if ($codigo === '1.2.1') return 'capacitacion_sst';
    if ($codigo === '2.5.1') return 'archivo_documental';

    // Detección por nombre (fallback)
    if (strpos($nombre, 'capacitacion') !== false) return 'capacitacion_sst';
    if (strpos($nombre, 'presupuesto') !== false) return 'presupuesto_sst';

    // Tipo genérico
    return null;
}
```

---

## Cómo Agregar un Nuevo Tipo

### Paso 1: Crear la vista del tipo

```bash
# Crear archivo
touch app/Views/modulo/_tipos/nuevo_tipo.php
```

```php
<?php
/**
 * Vista de Tipo: Nuevo Tipo
 * Código: X.X.X
 */
?>

<div class="card border-0 shadow-sm mb-4">
    <!-- UI específica -->
</div>

<?= view('modulo/_components/componente_1', [...]) ?>
<?= view('modulo/_components/componente_2', [...]) ?>
```

### Paso 2: Agregar detección en el controlador

```php
protected function determinarTipo(array $entidad): ?string
{
    // ... casos existentes ...

    // NUEVO: Agregar detección del nuevo tipo
    if ($codigo === 'X.X.X') return 'nuevo_tipo';

    return null;
}
```

### Paso 3: Agregar a listas de tipos (si aplica)

Si el tipo necesita mostrar componentes específicos:

```php
// En componentes que filtran por tipo
$tiposConTabla = ['tipo_1', 'tipo_2', 'nuevo_tipo']; // ← Agregar aquí
```

### Paso 4: Probar

```
Visitar: /modulo/entidad/{id_del_nuevo_tipo}
Verificar: Se carga la vista correcta
```

---

## Beneficios de esta Arquitectura

| Aspecto | Antes (Monolítico) | Después (Componentes) |
|---------|-------------------|----------------------|
| **Líneas por archivo** | 1,000+ | ~80-150 |
| **Archivos** | 1 | 15-20 |
| **Riesgo de cambios** | Alto (afecta todo) | Bajo (aislado) |
| **Reutilización** | Ninguna | Alta |
| **Testing** | Difícil | Por componente |
| **Trabajo en equipo** | 1 persona | Paralelo |
| **Debugging** | Complejo | Directo |

---

## Cuándo Usar este Patrón

### Usar cuando:
- Una URL maneja múltiples "tipos" de contenido
- Hay UI compartida entre tipos (header, footer, alertas)
- Hay UI específica por tipo (botones, formularios, tablas)
- El archivo supera las 300-400 líneas
- Hay código duplicado entre secciones

### No usar cuando:
- La vista es simple (<200 líneas)
- Solo hay 1-2 tipos muy similares
- No hay reutilización posible

---

## Ejemplo Real: Documentación SST

```
URL: /documentacion/carpeta/{id}

Tipos detectados:
├── 1.1.1 → responsables_sst      → Botón "Generar Asignación"
├── 1.1.2 → responsabilidades     → Dropdown con 3 documentos
├── 1.1.3 → presupuesto_sst       → Botón "Abrir Presupuesto"
├── 1.2.1 → capacitacion_sst      → Botón "Crear con IA"
├── 2.5.1 → archivo_documental    → Tabla con TODOS los documentos
└── null  → generica              → Botón "Nuevo Documento"
```

Cada tipo tiene:
- Su propia card con botones específicos
- Combinación diferente de componentes
- Lógica de negocio específica

---

## Checklist para Implementar

- [ ] Identificar los tipos que manejará la vista
- [ ] Crear directorio `_components/`
- [ ] Crear directorio `_tipos/`
- [ ] Extraer CSS a `_components/styles.php`
- [ ] Extraer header/navbar a `_components/header.php`
- [ ] Extraer alertas a `_components/alertas.php`
- [ ] Extraer scripts a `_components/scripts.php`
- [ ] Identificar componentes reutilizables
- [ ] Crear componente por cada bloque reutilizable
- [ ] Crear vista para cada tipo en `_tipos/`
- [ ] Crear vista genérica como fallback
- [ ] Agregar función `determinarTipo()` en controlador
- [ ] Modificar método principal para cargar vista correcta
- [ ] Simplificar vista principal como layout
- [ ] Probar cada tipo
- [ ] Documentar en este archivo

---

## Rollback

Si algo falla, el código original está en git:

```bash
# Restaurar archivo original
git checkout HEAD -- app/Views/modulo/vista.php

# Restaurar controlador
git checkout HEAD -- app/Controllers/MiController.php
```

---

**Última actualización:** 2026-02-01
**Autor:** Refactorización con Claude Code
