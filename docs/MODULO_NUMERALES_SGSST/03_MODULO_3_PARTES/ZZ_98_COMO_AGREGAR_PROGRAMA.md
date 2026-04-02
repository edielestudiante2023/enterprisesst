# INSTRUCTIVO: Como Agregar un Nuevo Programa Type B (3 Partes)

## Resumen

Este instructivo describe el proceso paso a paso para agregar un **nuevo programa** al sistema SST con arquitectura de **3 Partes** (Type B). Un programa Type B tiene:

| Parte | Que genera | Tabla BD | Campo clave |
|-------|-----------|----------|-------------|
| **Parte 1** | Actividades para el Plan de Trabajo | `tbl_pta_cliente` | `tipo_servicio` |
| **Parte 2** | Indicadores de medicion | `tbl_indicadores_sst` | `categoria` |
| **Parte 3** | Documento formal con IA | `tbl_documentos_sst` | `tipo_documento` |

**Gold Standard de referencia**: Programa de Inspecciones (estandar 4.2.4, `programa_inspecciones`).

> **¿Por que este y no otro?** Inspecciones obtuvo 92/100 en completeness: patron mas limpio y replicable,
> usa `GeneradorIAController` (como todos los demas), separacion clara de las 3 partes en Services/Handler/Vistas,
> script SQL completo, y `getContextoBase()` bien implementado que consume actividades + indicadores reales.
>
> **Archivos del gold standard para copiar y adaptar:**
> - Service Parte 1: `app/Services/ActividadesInspeccionesService.php` (511 lineas)
> - Service Parte 2: `app/Services/IndicadoresInspeccionesService.php` (338 lineas)
> - Handler Parte 3: `app/Libraries/DocumentosSSTTypes/ProgramaInspecciones.php` (222 lineas)
> - Vista Parte 1:   `app/Views/generador_ia/programa_inspecciones.php`
> - Vista Parte 2:   `app/Views/generador_ia/indicadores_programa_inspecciones.php`
> - Script SQL:      `app/SQL/agregar_programa_inspecciones.php` (177 lineas)
> - Controller:      `GeneradorIAController.php` metodos lineas 2289-2465 (7 metodos)
> - Rutas:           `Routes.php` lineas 938-946 (7 rutas generador-ia) + 1047-1049 (2 rutas documentos-sst)

---

## Catalogo Aspiracional: 50+ Programas por Categoria de Riesgo

La vista `procedimientos_seguridad.php` (numeral 4.2.3) contiene un **accordion** con el catalogo completo de programas. Los implementados se muestran activos con boton "Crear con IA"; los pendientes se muestran en gris con "(Proximamente)".

El catalogo se define en el array `$catalogoProgramas` de la vista. Al implementar un nuevo programa, se debe:
1. Cambiar `'implementado' => false` a `true` en el item correspondiente
2. Asignar el `'key'` con el `tipo_documento` del nuevo programa
3. Agregar la configuracion en el array `$programasImplementados`

### Estado actual del catalogo (12 categorias):

| # | Categoria | Total | Con IA | Programas |
|---|----------|-------|--------|-----------|
| 1 | **Riesgo Biomecanico** | 5 | 1 | **PVE Biomecanico** (implementado), Pausas Activas, Higiene Postural, Manejo Manual Cargas, Ergonomia VDT |
| 2 | **Riesgo Psicosocial** | 5 | 1 | **PVE Psicosocial** (implementado), Prevencion Estres, Prevencion Acoso, Bienestar/Clima, Equilibrio Vida-Trabajo |
| 3 | **Riesgo Quimico** | 5 | 0 | PVE Quimico, Manejo Sustancias Quimicas, Gestion Hojas SDS, Proteccion Respiratoria, Vigilancia Cancerigenos |
| 4 | **Riesgo Fisico** | 5 | 0 | PVE Auditivo, PVE Visual, Control Ruido, Control Iluminacion, Temperaturas Extremas |
| 5 | **Riesgo Biologico** | 4 | 0 | PVE Biologico, Bioseguridad, Gestion Residuos Biologicos, Vacunacion Ocupacional |
| 6 | **Riesgo de Seguridad** | 5 | 0 | Trabajo en Alturas, Espacios Confinados, Trabajo en Caliente, Bloqueo/Etiquetado LOTO, Seguridad Vial |
| 7 | **Riesgo Electrico** | 3 | 0 | Riesgo Electrico RETIE, Mantenimiento Electrico Preventivo, Seguridad Instalaciones Electricas |
| 8 | **Riesgo Locativo** | 3 | 0 | Orden y Aseo 5S, Senalizacion/Demarcacion, Inspecciones de Seguridad |
| 9 | **Riesgo Tecnologico** | 3 | 0 | Prevencion Incendios, Gestion Materiales Peligrosos, Seguridad Maquinaria/Equipos |
| 10 | **Riesgo Publico** | 3 | 0 | Prevencion Riesgo Publico, Seguridad Desplazamientos, Gestion Riesgo Atraco |
| 11 | **Riesgo Natural** | 3 | 0 | Preparacion Sismos, Prevencion Inundaciones, Gestion Fenomenos Naturales |
| 12 | **Transversales** | 6 | 0 | EPP, Reportes Actos/Condiciones Inseguras, Gestion Contratistas, Vigilancia Salud, Rehabilitacion/Retorno, Gestion Cambio |

**Total: ~50 programas | 2 implementados | ~48 pendientes**

### Para implementar un programa del catalogo:

1. Elegir el programa del catalogo (ej: "PVE de Riesgo Quimico")
2. Definir la nomenclatura (Paso 0 abajo)
3. Seguir los 13 pasos de este instructivo
4. Al final, actualizar `procedimientos_seguridad.php`:
   - En `$catalogoProgramas`: `'key' => 'pve_riesgo_quimico', 'implementado' => true`
   - En `$programasImplementados`: agregar bloque de configuracion
5. En `DocumentacionController.php`:
   - Agregar al array `$programasImplementados` dentro de `carpeta()`
   - Agregar al `whereIn` de `procedimientos_seguridad` en filtro de documentos
   - Agregar al `whereIn` de soportes si aplica

---

## Nomenclatura (Definir PRIMERO)

Antes de tocar codigo, definir los 6 identificadores del nuevo programa:

| Concepto | Donde se usa | Formato | Ejemplo (Biomecanico) |
|----------|-------------|---------|----------------------|
| `tipo_documento` | Factory, BD, URL Part 3 | `snake_case` | `pve_riesgo_biomecanico` |
| `tipo_servicio` | PTA, filtros Part 1 | Texto libre | `PVE Riesgo Biomecanico` |
| `categoria` | Indicadores, filtros Part 2 | `snake_case` | `pve_biomecanico` |
| Clase PHP | Factory key → class | `PascalCase` | `PveRiesgoBiomecanico` |
| `tipoCarpetaFases` | FasesDocumentoService | `snake_case` | `pve_riesgo_biomecanico` |
| Slug URL (kebab) | Routes generador-ia | `kebab-case` | `pve-riesgo-biomecanico` |

> **REGLA CRITICA**: El `tipo_documento` en la URL de generacion SIEMPRE va en **snake_case**:
> `/documentos/generar/pve_riesgo_biomecanico/18` (correcto)
> ~~`/documentos/generar/pve-riesgo-biomecanico/18`~~ (INCORRECTO - no matchea Factory)

---

## Paso 1: Service Parte 1 — Actividades

**Crear**: `app/Services/Actividades{NombrePrograma}Service.php`

**Referencia (gold standard)**: `app/Services/ActividadesInspeccionesService.php`

### Estructura:

```php
<?php
namespace App\Services;

use App\Models\PtaclienteModel;

class Actividades{NombrePrograma}Service
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    // 12 actividades distribuidas por mes (1-12), con ciclo PHVA
    public const ACTIVIDADES_{NOMBRE} = [
        [
            'mes' => 1,
            'actividad' => 'Descripcion de la actividad',
            'responsable' => 'Responsable SG-SST',
            'recursos' => 'Recurso humano, formatos',
            'ciclo_phva' => 'P',   // P, H, V o A
            'evidencia' => 'Formato diligenciado / Registro fotografico',
        ],
        // ... 11 mas
    ];

    /**
     * Preview: retorna las actividades sin guardar
     */
    public function preview(int $idCliente, int $anio): array { ... }

    /**
     * Generar: inserta actividades en tbl_pta_cliente
     * tipo_servicio = '{Tipo Servicio del Programa}'
     * numeral = '{numeral}'  (ej: '4.2.3')
     */
    public function generarActividades(int $idCliente, int $anio): array { ... }

    /**
     * Consultar actividades existentes del cliente
     */
    public function getActividadesCliente(int $idCliente, ?int $anio = null): array { ... }

    /**
     * Resumen para la vista (totales, porcentajes PHVA)
     */
    public function getResumenActividades(int $idCliente, int $anio): array { ... }
}
```

### Campos criticos en la insercion:

```php
$data = [
    'id_cliente'     => $idCliente,
    'mes'            => $act['mes'],
    'actividad'      => $act['actividad'],
    'responsable'    => $act['responsable'],
    'recursos'       => $act['recursos'],
    'ciclo_phva'     => $act['ciclo_phva'],
    'evidencia'      => $act['evidencia'],
    'tipo_servicio'  => 'PVE Riesgo Biomecanico',  // <-- CLAVE Part 1
    'numeral'        => '4.2.3',
    'anio'           => $anio,
    'estado'         => 'pendiente',
];
```

---

## Paso 2: Service Parte 2 — Indicadores

**Crear**: `app/Services/Indicadores{NombrePrograma}Service.php`

**Referencia (gold standard)**: `app/Services/IndicadoresInspeccionesService.php`

### Estructura:

```php
<?php
namespace App\Services;

use App\Models\IndicadorSSTModel;

class Indicadores{NombrePrograma}Service
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    // 6-8 indicadores con tipo (estructura, proceso, resultado)
    public const INDICADORES_{NOMBRE} = [
        [
            'nombre'        => 'Cumplimiento de Actividades del PVE',
            'tipo'          => 'proceso',     // estructura, proceso, resultado
            'formula'       => '(Ejecutadas / Programadas) x 100',
            'meta'          => 90,
            'unidad'        => '%',
            'periodicidad'  => 'trimestral',  // mensual, trimestral, semestral, anual
            'fuente_datos'  => 'Plan de trabajo anual',
        ],
        // ... 5-7 mas
    ];

    public function preview(int $idCliente, int $anio): array { ... }

    /**
     * Generar: inserta en tbl_indicadores_sst
     * categoria = '{categoria_indicador}'
     */
    public function generarIndicadores(int $idCliente, int $anio): array { ... }

    public function getIndicadoresCliente(int $idCliente, ?int $anio = null): array { ... }
}
```

### Campo critico en la insercion:

```php
$data = [
    'id_cliente'    => $idCliente,
    'categoria'     => 'pve_biomecanico',  // <-- CLAVE Part 2
    'nombre'        => $ind['nombre'],
    'tipo'          => $ind['tipo'],
    'formula'       => $ind['formula'],
    'meta'          => $ind['meta'],
    'unidad'        => $ind['unidad'],
    'periodicidad'  => $ind['periodicidad'],
    'fuente_datos'  => $ind['fuente_datos'],
    'anio'          => $anio,
];
```

---

## Paso 3: Document Class Parte 3

**Crear**: `app/Libraries/DocumentosSSTTypes/{NombrePrograma}.php`

**Referencia (gold standard)**: `app/Libraries/DocumentosSSTTypes/ProgramaInspecciones.php`

### Estructura minima:

```php
<?php
namespace App\Libraries\DocumentosSSTTypes;

class {NombrePrograma} extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return '{tipo_documento}';  // snake_case, matchea Factory key
    }

    public function getNombre(): string
    {
        return 'Nombre Legible del Programa';
    }

    public function getDescripcion(): string { ... }

    public function getEstandar(): ?string
    {
        return '4.2.3';  // o el numeral que corresponda
    }

    /**
     * CRITICO: Secciones del documento (6-12 tipicamente)
     */
    public function getSecciones(): array
    {
        return [
            [
                'id' => 'objetivo',
                'titulo' => '1. Objetivo',
                'prompt' => 'Redacta el objetivo del programa...',
                'orden' => 1,
            ],
            // ... mas secciones
        ];
    }

    /**
     * CRITICO: Contexto que alimenta la IA
     * Debe consultar AMBAS fuentes: PTA + Indicadores
     */
    public function getContextoBase(int $idCliente): array
    {
        $db = \Config\Database::connect();

        // Part 1: Actividades del PTA
        $actividades = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', '{Tipo Servicio}')  // <-- matchea Part 1
            ->where('anio', date('Y'))
            ->get()->getResultArray();

        // Part 2: Indicadores
        $indicadores = $db->table('tbl_indicadores_sst')
            ->where('id_cliente', $idCliente)
            ->where('categoria', '{categoria}')  // <-- matchea Part 2
            ->where('anio', date('Y'))
            ->get()->getResultArray();

        // Contexto del cliente
        $contexto = $db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        return [
            'actividades_pta' => $actividades,
            'indicadores' => $indicadores,
            'contexto_cliente' => $contexto,
        ];
    }

    /**
     * Para el SweetAlert de verificacion de datos
     */
    public function getFiltroServicioPTA(): ?string
    {
        return '{Tipo Servicio}';  // Mismo valor que tipo_servicio en Part 1
    }

    public function getCategoriaIndicador(): ?string
    {
        return '{categoria}';  // Mismo valor que categoria en Part 2
    }
}
```

---

## Paso 4: Registrar en Factory

**Modificar**: `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

Agregar al array `$tiposRegistrados`:

```php
'{tipo_documento}' => {NombrePrograma}::class,
```

> **IMPORTANTE**: La key DEBE ser identica a lo que retorna `getTipoDocumento()` y al `tipo_documento` en la BD.

---

## Paso 5: Registrar Categoria de Indicadores

**Modificar**: `app/Models/IndicadorSSTModel.php`

Agregar al array `CATEGORIAS`:

```php
'{categoria}' => [
    'nombre' => 'Nombre Legible',
    'icono' => 'bi-{icono}',      // Bootstrap Icon
    'color' => '{color}',          // primary, success, warning, danger, info, purple
    'descripcion' => 'Descripcion corta (Estandar X.X.X)'
],
```

---

## Paso 6: Configurar Fases

**Modificar**: `app/Services/FasesDocumentoService.php`

Agregar al array `FASES_POR_CARPETA`:

```php
'{tipoCarpetaFases}' => [
    'pta_{nombre_corto}' => [
        'nombre' => 'Actividades {Nombre}',
        'descripcion' => 'Actividades de {descripcion} para el Plan de Trabajo Anual',
        'url_modulo' => '/pta-cliente-nueva/list/{cliente}',
        'url_generar' => '/generador-ia/{cliente}/{slug-kebab}',
        'orden' => 1
    ],
    'indicadores_{nombre_corto}' => [
        'nombre' => 'Indicadores {Nombre}',
        'descripcion' => 'Indicadores para medir el {nombre}',
        'url_modulo' => '/indicadores-sst/{cliente}',
        'url_generar' => '/generador-ia/{cliente}/indicadores-{slug-kebab}',
        'orden' => 2,
        'depende_de' => 'pta_{nombre_corto}'
    ]
],
```

---

## Paso 7: Routes

**Modificar**: `app/Config/Routes.php`

### Generador-IA (Parte 1 + Parte 2): 7 rutas

```php
// Parte 1: Actividades
$routes->get('/generador-ia/(:num)/{slug-kebab}', 'GeneradorIAController::{metodoVista}/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-{slug}', 'GeneradorIAController::previewActividades{Nombre}/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-{slug}', 'GeneradorIAController::generarActividades{Nombre}/$1');
$routes->get('/generador-ia/(:num)/resumen-{slug}', 'GeneradorIAController::resumen{Nombre}/$1');

// Parte 2: Indicadores
$routes->get('/generador-ia/(:num)/indicadores-{slug}', 'GeneradorIAController::indicadores{Nombre}/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-{slug}', 'GeneradorIAController::previewIndicadores{Nombre}/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-{slug}', 'GeneradorIAController::generarIndicadores{Nombre}/$1');
```

### Documentos-SST (Parte 3): 2 rutas

```php
$routes->get('/documentos-sst/(:num)/{slug-kebab}/(:num)', 'DocumentosSSTController::{metodoVista}/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-{slug}', 'DocumentosSSTController::adjuntarSoporte{Nombre}');
```

---

## Paso 8: Controller Methods — GeneradorIAController

**Modificar**: `app/Controllers/GeneradorIAController.php`

Agregar **7 metodos** siguiendo el patron de `pveRiesgoBiomecanico`:

```
1. {metodoVista}($idCliente)              → Vista Part 1 (carga vista generador_ia/{vista}.php)
2. previewActividades{Nombre}($idCliente)  → JSON con actividades para preview
3. generarActividades{Nombre}($idCliente)  → POST: inserta actividades en PTA
4. resumen{Nombre}($idCliente)             → JSON con resumen de actividades generadas
5. indicadores{Nombre}($idCliente)         → Vista Part 2 (carga vista generador_ia/indicadores_{vista}.php)
6. previewIndicadores{Nombre}($idCliente)  → JSON con indicadores para preview
7. generarIndicadores{Nombre}($idCliente)  → POST: inserta indicadores en BD
```

### Patron de metodo vista (Part 1):

```php
public function {metodoVista}(int $idCliente)
{
    $clienteModel = new ClienteModel();
    $cliente = $clienteModel->find($idCliente);
    if (!$cliente) return redirect()->to('/clientes');

    $service = new Actividades{Nombre}Service();
    $actividadesExistentes = $service->getActividadesCliente($idCliente, (int) date('Y'));

    return view('generador_ia/{vista}', [
        'cliente' => $cliente,
        'actividadesExistentes' => $actividadesExistentes,
        'anioActual' => (int) date('Y'),
    ]);
}
```

---

## Paso 9: Controller Methods — DocumentosSSTController

**Modificar**: `app/Controllers/DocumentosSSTController.php`

Agregar **2 metodos**:

```php
// Vista del documento Part 3
public function {metodoVista}(int $idCliente, int $anio)
{
    return $this->mostrarDocumentoGenerico($idCliente, '{tipo_documento}', $anio);
}

// Adjuntar soporte
public function adjuntarSoporte{Nombre}()
{
    return $this->adjuntarSoporteGenerico('{tipo_documento}', '{slug-kebab}');
}
```

> Si el controller tiene metodo `mostrarDocumentoGenerico()`, usarlo. Si no, seguir el patron de los metodos existentes.

---

## Paso 10: Vistas Generador-IA

**Crear 2 vistas**:

1. `app/Views/generador_ia/{nombre_programa}.php` — Vista Part 1 (Actividades)
2. `app/Views/generador_ia/indicadores_{nombre_programa}.php` — Vista Part 2 (Indicadores)

**Referencia (gold standard)**: `app/Views/generador_ia/programa_inspecciones.php` y `indicadores_programa_inspecciones.php`

### Elementos clave de la vista Part 1:

- Navbar con color tematico + icono
- Card con info del programa
- Boton "Vista Previa" → AJAX GET a `preview-actividades-{slug}`
- Tabla con actividades (mes, actividad, responsable, ciclo PHVA, evidencia)
- Boton "Generar Actividades" → AJAX POST a `generar-actividades-{slug}`
- Seccion de resumen (actividades existentes, siguiente fase)

### Elementos clave de la vista Part 2:

- Misma estructura pero para indicadores
- Tabla con: nombre, tipo, formula, meta, periodicidad
- Boton "Generar Indicadores" → AJAX POST a `generar-indicadores-{slug}`
- Link "Ir a Fase 3" cuando indicadores esten generados

---

## Paso 11: acciones_documento.php

**Modificar**: `app/Views/documentacion/_components/acciones_documento.php`

### Agregar al array `$mapaRutas`:

```php
'{tipo_documento}' => '{slug-kebab}/' . $docSST['anio'],
```

### Agregar `$urlEditar`:

```php
} elseif ($tipoDoc === '{tipo_documento}') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/{slug-kebab}');
}
```

---

## Paso 12: DocumentacionController — Vista Carpeta

**Modificar**: `app/Controllers/DocumentacionController.php`

### Si el programa va DENTRO de una vista multi-programa (como 4.2.3):

1. Agregar el `tipo_documento` al `whereIn` de filtrado de documentos:

```php
// En la seccion de documentosSSTAprobados
if ($tipoCarpetaFases === 'procedimientos_seguridad') {
    $query->whereIn('tipo_documento', [
        'pve_riesgo_biomecanico',
        'pve_riesgo_psicosocial',
        '{nuevo_tipo_documento}',  // <-- agregar aqui
    ]);
}
```

2. Agregar al bloque `$programasFasesInfo`:

```php
$programasImplementados = [
    'pve_riesgo_biomecanico',
    'pve_riesgo_psicosocial',
    '{nuevo_tipoCarpetaFases}',  // <-- agregar aqui
];
```

3. Actualizar la vista `_tipos/procedimientos_seguridad.php` para mostrar el nuevo programa en el dropdown.

### Si el programa tiene su PROPIA carpeta (no multi-programa):

1. Agregar en `determinarTipoCarpetaFases()`:

```php
case '{codigo_numeral}':
    return '{tipoCarpetaFases}';
```

2. El sistema ya maneja automaticamente programas individuales con `FasesDocumentoService`.

---

## Paso 13: SQL Script

**Crear**: `app/SQL/agregar_{tipo_documento}.php`

**Referencia (gold standard)**: `app/SQL/agregar_programa_inspecciones.php`

Debe insertar en estas tablas:

```sql
-- 1. Tipo de documento
INSERT INTO tbl_tipos_documento_sst (tipo_documento, nombre, descripcion, estandar, activo)
VALUES ('{tipo_documento}', '{Nombre}', '{Descripcion}', '{numeral}', 1);

-- 2. Configuracion IA
INSERT INTO tbl_configuracion_ia_sst (tipo_documento, modelo_ia, temperatura, max_tokens, activo)
VALUES ('{tipo_documento}', 'gpt-4', 0.7, 4000, 1);

-- 3. Secciones del documento (una por seccion de getSecciones())
INSERT INTO tbl_secciones_documento_sst (tipo_documento, seccion_id, titulo, orden, activo)
VALUES ('{tipo_documento}', 'objetivo', '1. Objetivo', 1, 1);
-- ... una fila por seccion

-- 4. Plantilla prompt (opcional, si usa prompts dinamicos)
INSERT INTO tbl_plantillas_prompt_sst (tipo_documento, seccion_id, prompt_template, activo)
VALUES ('{tipo_documento}', 'objetivo', 'Redacta el objetivo...', 1);
```

---

## Checklist de Verificacion

Despues de implementar todos los pasos, verificar:

```
[ ] 1. Service Part 1 creado y tiene ACTIVIDADES_{NOMBRE} con 12 items
[ ] 2. Service Part 2 creado y tiene INDICADORES_{NOMBRE} con 6-8 items
[ ] 3. Document Class creada con:
      [ ] getTipoDocumento() retorna snake_case correcto
      [ ] getSecciones() tiene 6-12 secciones con prompts
      [ ] getContextoBase() consulta PTA + Indicadores + Contexto
      [ ] getFiltroServicioPTA() y getCategoriaIndicador() configurados
[ ] 4. Factory: tipo_documento registrado en $tiposRegistrados
[ ] 5. IndicadorSSTModel: categoria agregada a CATEGORIAS
[ ] 6. FasesDocumentoService: fases configuradas con dependencia
[ ] 7. Routes: 7 rutas generador-ia + 2 rutas documentos-sst
[ ] 8. GeneradorIAController: 7 metodos nuevos
[ ] 9. DocumentosSSTController: 2 metodos nuevos
[ ] 10. Vistas generador_ia: 2 vistas creadas (actividades + indicadores)
[ ] 11. acciones_documento.php: mapaRutas + urlEditar
[ ] 12. DocumentacionController: mapping y/o dropdown actualizado
[ ] 13. SQL ejecutado en la BD
```

### Pruebas funcionales:

```
[ ] Navegar a la carpeta del numeral → ver programa en la vista
[ ] Part 1: Preview actividades → tabla con 12 filas
[ ] Part 1: Generar actividades → insertadas en tbl_pta_cliente
[ ] Part 2: Preview indicadores → tabla con 6-8 filas
[ ] Part 2: Generar indicadores → insertados en tbl_indicadores_sst
[ ] Fases: despues de Part 1 + Part 2 → boton "Crear con IA" habilitado
[ ] Part 3: Generar documento → secciones generadas con IA alimentada de PTA + Indicadores
[ ] Part 3: Exportar PDF → documento formateado correctamente
[ ] Part 3: Exportar Word → documento formateado correctamente
[ ] Acciones: botones PDF, Ver, Editar funcionan desde la tabla de documentos
[ ] SweetAlert: verificacion de datos muestra actividades + indicadores correctos
```

---

## Diagrama de Archivos

```
app/
├── Services/
│   ├── Actividades{Nombre}Service.php     ← CREAR (Part 1)
│   └── Indicadores{Nombre}Service.php     ← CREAR (Part 2)
├── Libraries/DocumentosSSTTypes/
│   ├── {NombrePrograma}.php               ← CREAR (Part 3)
│   └── DocumentoSSTFactory.php            ← MODIFICAR (registro)
├── Models/
│   └── IndicadorSSTModel.php              ← MODIFICAR (categoria)
├── Config/
│   └── Routes.php                         ← MODIFICAR (~9 rutas)
├── Controllers/
│   ├── GeneradorIAController.php          ← MODIFICAR (7 metodos)
│   ├── DocumentosSSTController.php        ← MODIFICAR (2 metodos)
│   └── DocumentacionController.php        ← MODIFICAR (mapping/dropdown)
├── Views/
│   ├── generador_ia/
│   │   ├── {nombre_programa}.php          ← CREAR (vista Part 1)
│   │   └── indicadores_{nombre}.php       ← CREAR (vista Part 2)
│   └── documentacion/_components/
│       └── acciones_documento.php         ← MODIFICAR (2 entries)
├── SQL/
│   └── agregar_{tipo_documento}.php       ← CREAR (SQL script)
└── Services/
    └── FasesDocumentoService.php           ← MODIFICAR (fases)
```

**Total por programa: 5 archivos nuevos + 7 archivos modificados = 12 cambios**

---

## Referencia Rapida: Programas Implementados

| Programa | tipo_documento | tipo_servicio | categoria | Numeral | flujo BD | Estado |
|----------|---------------|---------------|-----------|---------|----------|--------|
| **Inspecciones** (GOLD STANDARD) | `programa_inspecciones` | `Programa de Inspecciones` | `inspecciones` | 4.2.4 | `programa_con_pta` | OK |
| Capacitacion | `programa_capacitacion` | `Programa Capacitación SST` | `capacitacion` | 3.1.1 | `programa_con_pta` | OK |
| Induccion y Reinduccion | `programa_induccion_reinduccion` | `Programa Induccion y Reinduccion` | - | 1.2.2 | `programa_con_pta` | OK |
| Plan Objetivos y Metas | `plan_objetivos_metas` | `Objetivos SG-SST` | - | 2.2.1 | `programa_con_pta` | OK |
| Promocion y Prevencion | `programa_promocion_prevencion_salud` | `Programa PyP Salud` | `promocion_prevencion_salud` | 3.1.2 | `programa_con_pta` | OK |
| PVE Biomecanico | `pve_riesgo_biomecanico` | `PVE Riesgo Biomecanico` | `pve_biomecanico` | 4.2.3 | `programa_con_pta` | OK |
| PVE Psicosocial | `pve_riesgo_psicosocial` | `PVE Riesgo Psicosocial` | `pve_psicosocial` | 4.2.3 | `programa_con_pta` | OK |

### Programas que generan actividades PTA pero NO son Tipo B (requieren regularizacion)

> **ALERTA**: Estos programas tienen Service de Parte 1 (generan actividades en `tbl_pta_cliente`)
> pero su `flujo` en `tbl_doc_tipo_configuracion` dice `secciones_ia` en vez de `programa_con_pta`.
> Esto causa que el documento final NO consuma las actividades/indicadores como contexto de IA.

| Programa | tipo_documento | tipo_servicio | flujo BD (incorrecto) | Deberia ser |
|----------|---------------|---------------|-----------------------|-------------|
| Estilos de Vida Saludable | `programa_estilos_vida_saludable` | `Estilos de Vida Saludable` | `secciones_ia` | `programa_con_pta` |
| Evaluaciones Medicas | `programa_evaluaciones_medicas_ocupacionales` | `Evaluaciones Medicas Ocupacionales` | `secciones_ia` | `programa_con_pta` |
| Mantenimiento Periodico | `programa_mantenimiento_periodico` | `Mantenimiento Periodico` | `secciones_ia` | `programa_con_pta` |

### Generadores de actividades PTA sin documento asociado

| Service | tipo_servicio | Proposito |
|---------|---------------|-----------|
| `PTAGeneratorService` | `Plan de Trabajo Anual` | Generador utilitario que llena PTA desde cronograma de capacitacion. No es documento. |

---

## Regla de Prevencion: Como evitar programas huerfanos

> **REGLA**: Todo programa que tenga un Service `Actividades{X}Service.php` que inserte en `tbl_pta_cliente`
> DEBE tener su registro en `tbl_doc_tipo_configuracion` con `flujo = 'programa_con_pta'`.
>
> Si un programa genera actividades PTA pero su flujo es `secciones_ia`, el documento se genera
> sin consumir esas actividades como contexto — es decir, la Parte 1 y Parte 2 se desperdician.
>
> **Verificacion rapida en BD:**
> ```sql
> -- Buscar programas con Service de actividades pero flujo incorrecto
> SELECT tipo_documento, nombre, flujo
> FROM tbl_doc_tipo_configuracion
> WHERE tipo_documento LIKE 'programa_%' AND flujo = 'secciones_ia';
> ```
> Si retorna filas, son candidatos a regularizar.
