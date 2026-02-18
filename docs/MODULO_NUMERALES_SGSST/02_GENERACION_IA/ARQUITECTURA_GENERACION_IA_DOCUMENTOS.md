# Arquitectura de Generación IA — Reglas de Juego

> Última actualización: 2026-02-18
> Reemplaza la versión anterior que documentaba la arquitectura incorrecta (PHP como fuente de prompts).

---

## Regla de Oro

> **La base de datos es la ÚNICA fuente de verdad para todo lo estático.**
> **Las clases PHP existen ÚNICAMENTE para lógica que requiere ejecución de código.**
> **Nada estático se hardcodea en PHP.**

---

## Qué es "estático" vs "lógica"

| Dato | ¿Estático o lógica? | Fuente |
|------|---------------------|--------|
| Nombre del documento | Estático | BD → `tbl_doc_tipo_configuracion.nombre` |
| Estándar (ej: 2.1.1) | Estático | BD → `tbl_doc_tipo_configuracion.estandar` |
| Flujo (`secciones_ia` / `programa_con_pta`) | Estático | BD → `tbl_doc_tipo_configuracion.flujo` |
| Lista de secciones (key, nombre, número) | Estático | BD → `tbl_doc_secciones_config` |
| Prompt de cada sección | Estático | BD → `tbl_doc_secciones_config.prompt_ia` |
| Firmantes requeridos | Estático | BD → `tbl_doc_firmantes_config` |
| Código base del documento (ej: POL-SST) | Estático | BD → `tbl_doc_plantillas.codigo_sugerido` |
| Contexto PTA + Indicadores (Tipo B) | Lógica PHP | Clase PHP → `getContextoBase()` |
| Contexto cliente base | Lógica PHP | `AbstractDocumentoSST::getContextoBase()` |

---

## Las Tres Tablas de Configuración

```
tbl_doc_tipo_configuracion          ← TIPO de documento
├── tipo_documento (snake_case)     ← identificador único
├── nombre                          ← nombre legible
├── descripcion
├── estandar                        ← numeral resolución 0312
├── flujo                           ← 'secciones_ia' | 'programa_con_pta'
├── categoria
└── activo

tbl_doc_secciones_config            ← SECCIONES del documento
├── id_tipo_config (FK)
├── seccion_key                     ← key único por sección
├── nombre                          ← nombre legible
├── numero                          ← orden visible al usuario
├── prompt_ia                       ← instrucciones para OpenAI ← FUENTE ÚNICA
├── tipo_contenido
├── es_obligatoria
└── orden

tbl_doc_firmantes_config            ← FIRMANTES del documento
├── id_tipo_config (FK)
├── firmante_tipo                   ← 'representante_legal' | 'consultor_sst' | etc.
├── rol_display
├── orden
└── activo
```

Estas tablas se administran desde: `/listSeccionesConfig`

---

## Flujo de Generación con IA (cómo funciona realmente)

```
Usuario hace clic "Generar con IA" (sección X)
                    │
                    ▼
POST /documentos/generar-seccion
  {tipo: 'politica_sst_general', seccion: 'objetivo', id_cliente: 23}
                    │
                    ▼
generarSeccionIA() → generarConIAReal()
                    │
    ┌───────────────┼───────────────────────────────────┐
    │               │                                   │
    ▼               ▼                                   ▼
  PASO 1          PASO 2                              PASO 3
  prompt          contexto base                       nombre/número
  de BD           de PHP                              de BD
    │               │                                   │
    ▼               ▼                                   ▼
DocumentoConfig  Factory::crear()                  DocumentoConfig
Service::        → getContextoBase()               Service::
obtenerPrompt    (consulta BD si Tipo B)           obtenerSecciones()
Seccion()
    │               │                                   │
    └───────────────┴───────────────────────────────────┘
                    │
                    ▼
          IADocumentacionService::generarSeccion()
                    │
                    ▼
                OpenAI API
                    │
                    ▼
            Contenido generado ✅
```

### Si no existe prompt en BD para esa sección:
```
DocumentoConfigService::obtenerPromptSeccion() → null
                    │
                    ▼
            ERROR claro en log:
            "Sección '{key}' del tipo '{tipo}' no tiene prompt_ia en BD.
             Configúralo en /listSeccionesConfig"
                    │
                    ▼
        Response: {success: false, message: 'Sección sin prompt configurado'}
```

**No hay fallback a PHP.** Un error de configuración debe verse como error, no silenciarse.

---

## Responsabilidades de la Clase PHP

### Qué SÍ hace la clase PHP

| Método | Propósito | ¿Quién lo tiene? |
|--------|-----------|-----------------|
| `getTipoDocumento()` | Identifica el tipo (requerido por Factory) | Todas las clases |
| `getContextoBase()` | Consulta BD para construir contexto IA | Tipo B sobrescribe; Tipo A usa el de Abstract |

### Qué NO hace la clase PHP

| ❌ Prohibido | Por qué | Alternativa |
|-------------|---------|-------------|
| `getPromptParaSeccion()` con strings hardcodeados | El prompt vive en BD | BD → `tbl_doc_secciones_config.prompt_ia` |
| `getSecciones()` con array hardcodeado | Las secciones viven en BD | BD → `tbl_doc_secciones_config` |
| `getContenidoEstatico()` con texto hardcodeado | No hay fallback estático | Si no hay prompt en BD → error explícito |
| `getFirmantesRequeridos()` con array hardcodeado | Los firmantes viven en BD | BD → `tbl_doc_firmantes_config` |

> **Nota sobre clases existentes:** Las clases PHP actuales todavía tienen estos métodos por razones históricas.
> Se están migrando progresivamente. En clases nuevas no se agregan.

---

## Diferencia Tipo A vs Tipo B

| | Tipo A (`secciones_ia`) | Tipo B (`programa_con_pta`) |
|---|---|---|
| **Flujo** | Directo al editor IA | PTA → Indicadores → Editor IA |
| **Contexto IA** | Solo datos del cliente | Datos cliente + PTA + Indicadores |
| **`getContextoBase()`** | Usa el de `AbstractDocumentoSST` | Sobrescribe para consultar PTA e indicadores |
| **Clase PHP necesaria** | No (solo necesita BD) | Sí (para `getContextoBase()`) |
| **Ejemplos** | `politica_sst_general`, `procedimiento_control_documental` | `programa_capacitacion`, `programa_induccion_reinduccion` |

---

## Checklist: Crear un Nuevo Documento con Generación IA

### Paso 1 — Registrar en BD (PRIMERO, siempre)

1. Insertar en `tbl_doc_tipo_configuracion`:
   ```sql
   INSERT INTO tbl_doc_tipo_configuracion
     (tipo_documento, nombre, descripcion, estandar, flujo, categoria)
   VALUES
     ('mi_nuevo_documento', 'Nombre Legible', 'Descripción...', '3.1.5',
      'secciones_ia', 'procedimientos');
   ```

2. Insertar secciones en `tbl_doc_secciones_config`:
   ```sql
   INSERT INTO tbl_doc_secciones_config
     (id_tipo_config, numero, nombre, seccion_key, prompt_ia, tipo_contenido, es_obligatoria, orden)
   VALUES
     (@id_tipo, 1, 'Objetivo', 'objetivo',
      'Genera el objetivo del documento para {empresa}. Debe expresar...', 'texto', 1, 10),
     (@id_tipo, 2, 'Alcance', 'alcance',
      'Define el alcance del documento. Aplica a...', 'texto', 1, 20);
   ```
   > Los prompts deben ser específicos. Usar `{empresa}` como placeholder del nombre de empresa.

3. Insertar firmantes en `tbl_doc_firmantes_config`:
   ```sql
   INSERT INTO tbl_doc_firmantes_config
     (id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden)
   VALUES
     (@id_tipo, 'consultor_sst', 'Elaboró', 'Responsable SST', 1),
     (@id_tipo, 'representante_legal', 'Aprobó', 'Representante Legal', 2);
   ```

4. Verificar usando la pantalla admin: `/listSeccionesConfig`

### Paso 2 — Clase PHP (SOLO si es Tipo B)

Si el flujo es `programa_con_pta`, crear clase en `app/Libraries/DocumentosSSTTypes/`:

```php
class MiNuevoDocumento extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'mi_nuevo_documento'; // debe coincidir exactamente con BD
    }

    // SOLO sobrescribir si necesita datos de PTA/Indicadores
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        // Consultar tablas específicas del módulo
        $actividades = $this->obtenerActividadesPTA($idCliente, $anio);
        $indicadores = $this->obtenerIndicadores($idCliente);

        return parent::getContextoBase($cliente, $contexto) .
               "\n\nACTIVIDADES DEL PTA:\n" . $actividades .
               "\n\nINDICADORES:\n" . $indicadores;
    }

    // NO agregar: getSecciones(), getPromptParaSeccion(), getContenidoEstatico()
    // NO agregar: getFirmantesRequeridos() con arrays hardcodeados
    // Todo eso vive en BD.
}
```

### Paso 3 — Registrar en Factory (siempre que exista clase PHP)

```php
// DocumentoSSTFactory.php
private static array $tiposRegistrados = [
    // ... existentes ...
    'mi_nuevo_documento' => MiNuevoDocumento::class,
];
```

Si es Tipo A y no creaste clase PHP, el Factory lanzará `InvalidArgumentException`.
En ese caso asegúrate de que `generarConIAReal()` maneje el caso usando
`AbstractDocumentoSST` directamente cuando no existe clase específica.

### Paso 4 — Rutas de vista web

Registrar en `Routes.php` (kebab-case):
```php
$routes->get('/documentos-sst/(:num)/mi-nuevo-documento/(:num)',
             'DocumentosSSTController::verDocumento/$1/$2');
```

---

## Qué NO hacer (casos reales de problemas)

### ❌ Duplicar prompts en PHP cuando ya están en BD

```php
// MAL — el prompt también está en tbl_doc_secciones_config
public function getPromptParaSeccion(string $seccionKey, int $estandares): string
{
    return [
        'objetivo' => "Genera el objetivo...",  // ← hardcodeado, ignora BD
    ][$seccionKey] ?? '';
}
```

```php
// BIEN — no existe este método en clases nuevas
// El prompt se lee en generarConIAReal() vía DocumentoConfigService
```

### ❌ Agregar secciones al array PHP cuando ya están en BD

```php
// MAL
public function getSecciones(): array
{
    return [
        ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],  // ← duplicado de BD
    ];
}
```

### ❌ Silenciar ausencia de prompt con texto genérico

```php
// MAL — falla silenciosamente
$prompt = $documentoHandler->getPromptParaSeccion($seccion, $estandares);
if (empty($prompt)) {
    $prompt = "Genera contenido para la sección.";  // ← texto basura
}
```

```php
// BIEN — falla ruidosamente
$prompt = $this->configService->obtenerPromptSeccion($tipoDocumento, $seccionKey);
if (empty($prompt)) {
    log_message('error', "Sin prompt BD: tipo={$tipoDocumento}, seccion={$seccionKey}");
    return $this->response->setJSON([
        'success' => false,
        'message' => "La sección '{$seccionKey}' no tiene prompt configurado. Ve a /listSeccionesConfig"
    ]);
}
```

---

## Estado de Migración (2026-02-18) — COMPLETADO

| Componente | Estado |
|---|---|
| `tbl_doc_tipo_configuracion` | ✅ 31 tipos registrados |
| `tbl_doc_secciones_config` | ✅ 293 secciones con `prompt_ia` |
| `tbl_doc_firmantes_config` | ✅ Firmantes configurados |
| Admin `/listSeccionesConfig` | ✅ Rutas registradas en `Routes.php` (líneas 1403-1409) |
| `generarConIAReal()` | ✅ Lee prompt de BD vía `DocumentoConfigService` |
| `getPromptParaSeccion()` en clases PHP | ✅ Eliminado de 32 clases y de la interfaz |
| `getSecciones()` en clases PHP | ⚠️ Dead code (nadie la llama) — limpiar progresivamente |
| `getFirmantesRequeridos()` en clases PHP | ⚠️ Dead code — limpiar progresivamente |
| `getContenidoEstatico()` en clases PHP | 🔒 Aún en uso (líneas 279 y 1136 del controlador) |

---

## Discrepancias Factory vs BD (barrido 2026-02-18)

### En Factory PHP pero SIN entrada en BD → fallan en runtime

Si un usuario navega a `/documentos/generar/{tipo}/{id}`, `generarConIAReal()` lanzará
error explícito porque el tipo no existe en `tbl_doc_tipo_configuracion`.

| tipo_documento | Clase PHP | ¿Tiene secciones en BD? | Acción requerida |
|---|---|---|---|
| `acta_constitucion_brigada` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_constitucion_cocolab` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_constitucion_copasst` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_constitucion_vigia` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_recomposicion_brigada` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_recomposicion_cocolab` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_recomposicion_copasst` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `acta_recomposicion_vigia` | ✅ | ❌ | Registrar en BD o confirmar si usa otro flujo |
| `pve_riesgo_biomecanico` | ✅ | ❌ | Registrar en BD con secciones y prompts |
| `pve_riesgo_psicosocial` | ✅ | ❌ | Registrar en BD con secciones y prompts |

> **Nota sobre actas:** Los documentos de actas (constitución/recomposición) posiblemente usan
> un flujo diferente (no `/documentos/generar/`). Verificar con el módulo de Comités y Elecciones
> antes de registrar en BD.

### En BD pero SIN clase PHP en Factory → usan contexto base genérico

Estos tipos cargarán la página sin error (BD los tiene), pero `getContextoBase()` usará
`buildContextoBaseGenerico()` ya que no hay clase PHP. Solo es problema si son Tipo B.

| tipo_documento | ¿Tiene secciones? | Observación |
|---|---|---|
| `matriz_requisitos_legales` | ❌ 0 secciones | No generará nada — configurar secciones en BD |
| `plan_emergencias` | ❌ 0 secciones | No generará nada — configurar secciones en BD |
| `politica_sst` | ❌ 0 secciones | **Duplicado** de `politica_sst_general` — considerar eliminar |
| `reglamento_higiene_seguridad` | ❌ 0 secciones | No generará nada — configurar secciones en BD |

---

## Referencias

- Admin prompts: `/listSeccionesConfig`
- Service BD: `app/Services/DocumentoConfigService.php`
- Controlador generación: `app/Controllers/DocumentosSSTController.php` → `generarConIAReal()`
- Clases PHP: `app/Libraries/DocumentosSSTTypes/`
- Factory: `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`
- Troubleshooting: `1_A_TROUBLESHOOTING_GENERACION_IA.md`
