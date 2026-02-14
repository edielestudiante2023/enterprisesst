# Troubleshooting: Generación de Contenido con IA para Documentos SST

## Resumen

Este documento describe los problemas comunes al crear nuevos tipos de documento SST y cómo solucionarlos.

### Tipos de Flujo de Generación

| Flujo | Descripción | Cuándo Usar | Documentación |
|-------|-------------|-------------|---------------|
| `secciones_ia` | Prompts en BD, clase PHP simple que lee de BD | Documentos simples (políticas, procedimientos básicos) | **Este documento** |
| `programa_con_pta` | Clase PHP + Factory + consulta de fases previas | Documentos con fases (programas con PTA, indicadores) | [ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md](./ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md) |

**IMPORTANTE:**
- **AMBOS flujos requieren una clase PHP en `DocumentosSSTTypes/` y registro en el Factory**
- La diferencia es que `programa_con_pta` consulta tablas adicionales (etapas, PTA, indicadores)
- Si tu documento tiene fases (etapas → PTA → indicadores → documento IA), usa el flujo `programa_con_pta`

---

## Problema 1: "[Seccion no definida]" al generar contenido

### Síntoma

Al hacer clic en "Generar con IA", las secciones muestran `[Seccion no definida]` en lugar de contenido generado.

### Causa

El flujo de generación no está usando los prompts configurados en la BD (`tbl_doc_secciones_config.prompt_ia`).

### Diagnóstico

```sql
-- 1. Verificar que el tipo de documento existe
SELECT * FROM tbl_doc_tipo_configuracion
WHERE tipo_documento = 'NOMBRE_DEL_TIPO';

-- 2. Verificar que las secciones tienen prompts
SELECT
    numero,
    nombre,
    seccion_key,
    CASE
        WHEN prompt_ia IS NULL OR prompt_ia = ''
        THEN '❌ SIN PROMPT'
        ELSE '✅ OK'
    END as estado_prompt,
    LEFT(prompt_ia, 50) as inicio_prompt
FROM tbl_doc_secciones_config
WHERE id_tipo_config = (
    SELECT id_tipo_config
    FROM tbl_doc_tipo_configuracion
    WHERE tipo_documento = 'NOMBRE_DEL_TIPO'
)
ORDER BY numero;
```

### Solución

1. **Si faltan prompts en BD**: Agregar los prompts a `tbl_doc_secciones_config`
2. **Si hay prompts pero no se usan**: Verificar que `DocumentosSSTController::generarSeccionIA()` está consultando la BD primero

### Flujo correcto de generación

```
Usuario hace clic en "Generar con IA"
            ↓
generarSeccionIA()
            ↓
1. Obtener prompt desde BD (configService->obtenerPromptSeccion)
            ↓
┌─ ¿Existe prompt en BD? ─────────────────────┐
│                                              │
│  SI → generarConPromptBD()                  │
│       → Llamar a OpenAI con prompt de BD    │
│       → Retornar contenido generado ✅      │
│                                              │
│  NO → Fallback a método legacy              │
│       → Puede retornar "[Seccion no definida]" │
└──────────────────────────────────────────────┘
```

---

## Problema 2: Documento no aparece en la carpeta

### Síntoma

El documento existe en BD pero no aparece al navegar a la carpeta correspondiente.

### Diagnóstico

```sql
-- 1. Verificar mapeo plantilla-carpeta
SELECT * FROM tbl_doc_plantilla_carpeta
WHERE codigo_plantilla = 'CODIGO_PLANTILLA';

-- 2. Verificar que el código de carpeta existe
SELECT * FROM tbl_doc_carpetas
WHERE codigo = 'X.X.X';
```

### Solución

1. Agregar registro en `tbl_doc_plantilla_carpeta`
2. Agregar mapeo en `ClienteDocumentosSstController::mapearPlantillaATipoDocumento()`

---

## Problema 3: Vista de tipo no carga

### Síntoma

Error `ViewException: Archivo inválido: documentacion/_tipos/nombre_tipo.php`

### Causa

Falta crear la vista de tipo en `app/Views/documentacion/_tipos/`

### Solución

1. Crear el archivo `app/Views/documentacion/_tipos/{nombre_tipo}.php`
2. Agregar detección en `DocumentacionController::determinarTipoCarpetaFases()`

### Template de vista de tipo

```php
<?php
/**
 * Vista de Tipo: [Nombre del Documento]
 * Código: X.X.X
 * Estándar: Resolución 0312/2019
 */
?>

<!-- Card del Documento -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start">
            <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1"><?= esc($carpeta['nombre']) ?></h5>
                <p class="text-muted mb-3">
                    Descripción del documento y su propósito.
                </p>

                <?php if (empty($documentosSSTAprobados)): ?>
                    <a href="<?= base_url('documentos/generar/TIPO_DOCUMENTO/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Generar Documento
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/TIPO_DOCUMENTO/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Editar / Nueva Versión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Fases -->
<?= view('documentacion/_components/panel_fases', ['fasesInfo' => $fasesInfo ?? null]) ?>

<!-- Tabla de Documentos -->
<?= view('documentacion/_components/tabla_documentos', ['documentosSSTAprobados' => $documentosSSTAprobados ?? []]) ?>
```

---

## Problema 4: Firmantes no aparecen en el documento

### Síntoma

El documento se genera pero no muestra firmantes o muestra firmantes incorrectos.

### Diagnóstico

```sql
-- Verificar firmantes configurados
SELECT * FROM tbl_doc_firmantes_config
WHERE id_tipo_config = (
    SELECT id_tipo_config
    FROM tbl_doc_tipo_configuracion
    WHERE tipo_documento = 'NOMBRE_DEL_TIPO'
)
ORDER BY orden;
```

### Solución

Agregar firmantes a `tbl_doc_firmantes_config` con:
- `firmante_tipo`: representante_legal, responsable_sst, delegado_sst, trabajador
- `rol_display`: Texto que aparece (Elaboró, Revisó, Aprobó)
- `orden`: Orden de aparición (1, 2, 3...)

---

## Checklist para nuevos tipos de documento

| # | Paso | Archivo/Tabla | Verificación |
|---|------|---------------|--------------|
| 1 | Configuración tipo | `tbl_doc_tipo_configuracion` | `SELECT * WHERE tipo_documento = '...'` |
| 2 | Secciones con prompts | `tbl_doc_secciones_config` | Verificar `prompt_ia` no vacío |
| 3 | Firmantes | `tbl_doc_firmantes_config` | Verificar orden y roles |
| 4 | Plantilla | `tbl_doc_plantillas` | Verificar código y tipo |
| 5 | Mapeo carpeta | `tbl_doc_plantilla_carpeta` | Verificar código carpeta |
| 6 | Vista de tipo | `_tipos/{nombre}.php` | Archivo existe |
| 7 | Detección carpeta | `DocumentacionController` | `determinarTipoCarpetaFases()` |
| 8 | Mapeo plantilla | `ClienteDocumentosSstController` | `mapearPlantillaATipoDocumento()` |
| 9 | **Clase PHP** | `DocumentosSSTTypes/{Nombre}.php` | **OBLIGATORIO - Clase existe** |
| 10 | **Registro en Factory** | `DocumentoSSTFactory.php` | **OBLIGATORIO - Tipo registrado** |
| 11 | **Test generación IA** | UI | Clic en "Generar con IA" funciona |

**⚠️ CRÍTICO:** Los pasos 9 y 10 son OBLIGATORIOS. Sin la clase PHP y el registro en el Factory, la generación con IA fallará con "[Seccion no definida]".

---

## Reglas de oro

### ✅ HACER

- Configurar todo en BD (tipos, secciones, firmantes, prompts)
- Usar `DocumentoConfigService` para leer configuración
- Usar `FirmanteService` para obtener firmantes
- Probar el flujo completo end-to-end después de crear un documento

### ❌ NO HACER

- Agregar tipos en constantes PHP hardcodeadas
- Crear controladores `Pz*` o `Hz*` nuevos
- Hardcodear códigos de documento en PHP
- Confiar solo en que los datos están en BD sin probar la UI

---

## Problema 5: Botón "Enviar a Firmas" no funciona en vista de generación IA

### Síntoma

Desde las rutas `/documentos/generar/{tipo}/{idCliente}`, el botón "Enviar a Firmas" está deshabilitado o no lleva a `/firma/solicitar/{id}`.

### Causa

La condición original requería que TODAS las secciones estuvieran aprobadas (`$todasSeccionesListas`) antes de habilitar el botón.

### Solución (Corregido 2026-02-02)

Se modificó `generar_con_ia.php` (líneas 256-274) para usar la condición de estado en lugar de requerir todas las secciones aprobadas:

```php
<?php if ($estadoDoc === 'firmado'): ?>
    <!-- Ver Firmas -->
<?php elseif ($estadoDoc === 'pendiente_firma'): ?>
    <!-- Estado Firmas -->
<?php elseif (in_array($estadoDoc, ['generado', 'aprobado', 'en_revision']) && $idDocumento): ?>
    <!-- ✅ BOTÓN ACTIVO: Enviar a Firmas -->
    <a href="<?= base_url('firma/solicitar/' . $idDocumento) ?>" class="btn btn-success btn-sm w-100">
        <i class="bi bi-pen me-1"></i>Enviar a Firmas
    </a>
<?php elseif ($idDocumento): ?>
    <!-- Documento existe pero estado no permite -->
<?php else: ?>
    <!-- Documento no existe aún -->
<?php endif; ?>
```

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `app/Views/documentos_sst/generar_con_ia.php` | Condición cambiada de `$todasSeccionesListas` a verificación de estado |
| `app/Controllers/FirmaElectronicaController.php` | Ya incluía 'generado' en estados permitidos (línea 56) |

### Regla de estados

El botón "Enviar a Firmas" ahora se habilita cuando:

1. El documento existe (`$idDocumento` no es null)
2. El estado es uno de: `borrador`, `generado`, `aprobado`, `en_revision`

**IMPORTANTE:** Se agregó `borrador` porque los documentos se crean inicialmente con ese estado cuando se guarda la primera sección (línea 1233 de DocumentosSSTController).

NO requiere que todas las secciones estén aprobadas.

---

## Problema 6: Toast de BD muestra conteo incorrecto de registros

### Síntoma

El toast de "Bases de datos consultadas" muestra más registros de los que realmente existen (ej: "9 indicadores" cuando solo hay 8).

### Causa

Las consultas SQL usan múltiples condiciones OR que pueden traer registros duplicados o de otros módulos:

```php
// ❌ INCORRECTO - puede traer duplicados
->groupStart()
    ->where('categoria', 'induccion')
    ->orLike('nombre_indicador', 'induccion', 'both')
    ->orLike('nombre_indicador', 'reinduccion', 'both')
    ->orWhere('numeral_resolucion', '1.2.2')
->groupEnd()
```

### Solución (Corregido 2026-02-04)

Simplificar la consulta usando SOLO el `numeral_resolucion` que es el identificador correcto:

```php
// ✅ CORRECTO - usa solo el numeral específico
->where('numeral_resolucion', '1.2.2')
->where('activo', 1)
```

### Regla

Cada documento SST tiene un `numeral_resolucion` único (1.2.1, 1.2.2, 3.1.2, etc.). Usar este campo para filtrar evita ambigüedades.

---

## Problema 7: Toasts desaparecen muy rápido

### Síntoma

Los toasts de notificación desaparecen antes de que el usuario pueda leer la información.

### Solución (Corregido 2026-02-04)

Se aumentaron las duraciones en `generar_con_ia.php`:

```javascript
const duraciones = {
    'database': 15000,  // 15 segundos (mucha información)
    'success': 6000,    // 6 segundos
    'warning': 6000,    // 6 segundos
    'error': 8000,      // 8 segundos (importante leer)
    'save': 5000        // 5 segundos
};
```

---

## Problema 8: Modal de progreso confunde al usuario

### Síntoma

El modal muestra "Generando: Indicadores del Programa - Seccion 2 de 13" y el usuario piensa que está regenerando los indicadores de la Fase 3.

### Causa

1. El texto "Generando" sugiere crear datos, no redactar texto
2. "Seccion 2 de 13" muestra el orden de generación, no el número real de la sección

### Solución (Corregido 2026-02-04)

Se cambió el mensaje para ser más claro:

**Antes:**
```
Generando: Indicadores del Programa
Seccion 2 de 13...
```

**Después:**
```
Redactando seccion 11: Indicadores del Programa
(2 de 13 secciones)
```

### Cambios en código

```javascript
// Agregar mapeo de números de sección
const seccionesNumeros = <?= json_encode(array_column($secciones, 'numero', 'key')) ?>;

// Función para obtener número
function getNumeroSeccion(key) {
    return seccionesNumeros[key] || '?';
}

// Mensaje mejorado
document.getElementById('progresoTitulo').textContent =
    `Redactando seccion ${numeroSeccion}: ${nombreSeccion}`;
document.getElementById('progresoDetalle').textContent =
    `(${i + 1} de ${ordenGeneracion.length} secciones)`;
```

---

## Problema 9: Error "Unknown column" en consultas de indicadores

### Síntoma

Error SQL: `Unknown column 'numeral' in 'where clause'` o `Unknown column 'unidad' in 'field list'`

### Causa

Los nombres de columna en `tbl_indicadores_sst` son diferentes a los esperados:
- `numeral` → **`numeral_resolucion`**
- `unidad` → **`unidad_medida`**

### Solución

Siempre verificar los nombres de columna en la BD antes de escribir consultas:

```sql
DESCRIBE tbl_indicadores_sst;
```

### Nombres correctos en `tbl_indicadores_sst`

| Campo esperado | Campo real |
|----------------|------------|
| numeral | `numeral_resolucion` |
| unidad | `unidad_medida` |
| tipo | `tipo_indicador` |

---

## Problema 10: "[Seccion no definida]" por falta de clase PHP en Factory

### Síntoma

Al hacer clic en "Generar con IA", la sección 1 muestra `[Seccion no definida]` y las demás secciones muestran contenido de OTRO documento (ej: habla de "capacitación" o "inducción" cuando debería ser otro tema).

### Causa

**Falta la clase PHP en `DocumentosSSTTypes/` y/o no está registrada en el Factory.**

El controlador `DocumentosSSTController::generarSeccionIA()` SIEMPRE usa el Factory:

```php
$documentoHandler = DocumentoSSTFactory::crear($tipoDocumento);
$promptBase = $documentoHandler->getPromptParaSeccion($seccion, $estandares);
```

Si el Factory no encuentra la clase, lanza excepción y el contenido no se genera correctamente.

### Diagnóstico

```php
// Verificar si la clase existe
php -r "echo class_exists('App\Libraries\DocumentosSSTTypes\NombreClase') ? 'OK' : 'NO EXISTE';"

// Verificar si está en el Factory
// Revisar app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php
// Buscar en $tiposRegistrados
```

### Solución

1. **Crear la clase PHP** en `app/Libraries/DocumentosSSTTypes/{NombrePascalCase}.php`
   - Debe extender `AbstractDocumentoSST`
   - Puede leer prompts desde BD usando `DocumentoConfigService`

2. **Registrar en el Factory** en `DocumentoSSTFactory.php`:

```php
private static array $tiposRegistrados = [
    // ... otros tipos ...
    'mi_nuevo_documento' => MiNuevoDocumento::class,
];
```

### Template de clase para documentos simples (flujo secciones_ia)

```php
<?php
namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

class MiNuevoDocumento extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    public function getTipoDocumento(): string
    {
        return 'mi_nuevo_documento'; // Debe coincidir con BD
    }

    public function getNombre(): string
    {
        return 'Mi Nuevo Documento';
    }

    public function getDescripcion(): string
    {
        return 'Descripcion del documento';
    }

    public function getEstandar(): ?string
    {
        return 'X.X.X'; // Codigo del estandar 0312
    }

    public function getSecciones(): array
    {
        // Leer desde BD
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());
        if (!empty($seccionesBD)) {
            $secciones = [];
            foreach ($seccionesBD as $s) {
                $secciones[] = [
                    'numero' => (int)($s['numero'] ?? 0),
                    'nombre' => $s['nombre'] ?? '',
                    'key' => $s['key'] ?? $s['seccion_key'] ?? ''
                ];
            }
            return $secciones;
        }
        // Fallback
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            // ... mas secciones
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Leer desde BD
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);
        if (!empty($promptBD)) {
            return $promptBD;
        }
        // Fallback
        return "Genera el contenido para la seccion '{$seccionKey}'.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
```

### Ejemplo real: ProcedimientoMatrizLegal

Ver `app/Libraries/DocumentosSSTTypes/ProcedimientoMatrizLegal.php` como ejemplo de documento simple que lee prompts desde BD.

---

## Historial de correcciones

| Fecha | Problema | Solución |
|-------|----------|----------|
| 2026-02-02 | Prompts de BD no se usaban en generación IA | Modificado `generarSeccionIA()` para consultar BD primero |
| 2026-02-02 | Botón "Enviar a Firmas" no funcionaba en `/documentos/generar/` | Modificada condición en `generar_con_ia.php` para usar estados válidos |
| 2026-02-04 | Toast mostraba conteo incorrecto de indicadores (9 vs 8) | Simplificada consulta SQL usando solo `numeral_resolucion` |
| 2026-02-04 | Toasts desaparecían muy rápido | Aumentadas duraciones (database=15s, success=6s, error=8s) |
| 2026-02-04 | Modal "Generando" confundía al usuario | Cambiado a "Redactando sección X" con número real |
| 2026-02-04 | Error "Unknown column 'numeral'" | Corregido a `numeral_resolucion` |
| 2026-02-04 | Error "Unknown column 'unidad'" | Corregido a `unidad_medida` |
| 2026-02-04 | "[Seccion no definida]" en procedimiento_matriz_legal | Creada clase `ProcedimientoMatrizLegal.php` y registrada en Factory |
| 2026-02-13 | "[Seccion no definida]" + SweetAlert colgado en identificacion_alto_riesgo | Faltaba clase PHP. Creada `IdentificacionAltoRiesgo.php` y registrada en Factory. Causa raiz: SQL y vista existian pero la clase PHP no se creo (checklist de ZZ_96 tenia la clase como ultimo paso) |
| 2026-02-13 | SweetAlert mostraba 19 actividades PTA y 17 indicadores no relacionados en doc Tipo A | `previsualizarDatos()` no diferenciaba flujo `secciones_ia` vs `programa_con_pta`. Fix: backend detecta flujo y salta queries PTA/indicadores para Tipo A. Frontend solo muestra contexto. Ver [1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md](./1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md) |

---

## Problema 11: Asteriscos visibles en el contenido (`**texto**` en lugar de negrita)

### Síntoma

El contenido generado por IA muestra literalmente `**CLIENTE DE VALIDACION**` con los asteriscos visibles, en lugar de mostrar el texto en **negrita**.

### Causa

El contenido generado por IA usa sintaxis Markdown (`**texto**` para negrita, `*texto*` para cursiva). Si la vista muestra el contenido directamente con `<?= $seccion['contenido'] ?>`, los asteriscos aparecen literalmente.

### Solución: Usar Parsedown

**Parsedown** es una librería PHP que convierte Markdown a HTML. Ya está instalada en el proyecto.

#### Instalación (si no está)

```bash
composer require erusev/parsedown
```

#### Implementación en Vistas

```php
<!-- ✅ CORRECTO: Crear instancia ANTES del loop -->
<?php if (!empty($contenido['secciones'])): ?>
    <?php $parsedown = new \Parsedown(); ?>
    <?php foreach ($contenido['secciones'] as $seccion): ?>
        <div class="seccion">
            <h3 class="seccion-titulo">
                <?= esc($seccion['numero'] ?? '') ?>. <?= esc($seccion['titulo']) ?>
            </h3>
            <div class="seccion-contenido">
                <?= $parsedown->text($seccion['contenido'] ?? '') ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
```

#### ❌ ERROR COMÚN (NO hacer esto)

```php
<!-- INCORRECTO: Muestra asteriscos literales -->
<?= $seccion['contenido'] ?? '' ?>

<!-- INCORRECTO: Crear instancia dentro del loop (ineficiente) -->
<?php foreach ($contenido['secciones'] as $seccion): ?>
    <?= (new \Parsedown())->text($seccion['contenido']) ?>
<?php endforeach; ?>
```

### Conversiones que realiza Parsedown

| Markdown | HTML Resultante |
|----------|-----------------|
| `**texto**` | `<strong>texto</strong>` |
| `__texto__` | `<strong>texto</strong>` |
| `*texto*` | `<em>texto</em>` |
| `_texto_` | `<em>texto</em>` |
| `# Título` | `<h1>Título</h1>` |
| `- item` | `<ul><li>item</li></ul>` |
| `1. item` | `<ol><li>item</li></ol>` |

### Vistas que ya usan Parsedown

| Vista | Ruta |
|-------|------|
| Plan Objetivos y Metas | `app/Views/documentos_sst/plan_objetivos_metas.php` |

### ⚠️ REGLA

Cuando se crea una **nueva vista de documento SST**, verificar que el contenido de las secciones pase por `$parsedown->text()` para evitar que aparezcan asteriscos en el texto.

### Documentación relacionada

Ver también: [2_AA_ WEB.md](./2_AA_ WEB.md) - Sección 19: Conversión Markdown a HTML

---

## Referencias

- [PROMPT_NUEVO_DOCUMENTO_SST.md](../../PROMPT_NUEVO_DOCUMENTO_SST.md) - Guía completa para crear documentos
- [DocumentoConfigService.php](../../app/Services/DocumentoConfigService.php) - Servicio de configuración
- [FirmanteService.php](../../app/Services/FirmanteService.php) - Servicio de firmantes
