# INSTRUCTIVO PARTE 3: Generación del Documento Formal

## Resumen Ejecutivo

La **Parte 3** genera el **DOCUMENTO FORMAL** que CONSUME los datos de:

- **Parte 1**: Actividades del Plan de Trabajo (`tbl_pta_cliente`)
- **Parte 2**: Indicadores de medición (`tbl_indicadores_sst`)

Esta es la tercera y última fase del módulo de 3 partes para generación de documentos con IA.

---

## Arquitectura de 3 Partes (Vista Completa)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        MÓDULO DE GENERACIÓN DE DOCUMENTOS                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐         │
│  │     PARTE 1     │───▶│     PARTE 2     │───▶│     PARTE 3     │         │
│  │   Actividades   │    │   Indicadores   │    │   Documento     │         │
│  │  (Plan Trabajo) │    │   (Medición)    │    │   (Formal)      │         │
│  └────────┬────────┘    └────────┬────────┘    └────────┬────────┘         │
│           │                      │                      │                   │
│           ▼                      ▼                      ▼                   │
│    tbl_pta_cliente       tbl_indicadores_sst    tbl_documentos_sst         │
│    tipo_servicio='X'     categoria='X'          tipo_documento='X'         │
│           │                      │                      │                   │
│           └──────────────┬───────┘                      │                   │
│                          │                              │                   │
│                          ▼                              │                   │
│              ┌───────────────────────┐                  │                   │
│              │   getContextoBase()   │◀─────────────────┘                   │
│              │   CONSUME Parte 1 y 2 │                                      │
│              └───────────────────────┘                                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Flujo de la Parte 3

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              FLUJO PARTE 3                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. DocumentosSSTController::generarSeccion()                               │
│                           │                                                 │
│                           ▼                                                 │
│  2. DocumentoSSTFactory::crear('[tipo_documento]')                          │
│                           │                                                 │
│                           ▼                                                 │
│  3. Instancia: [TipoDocumento] (extiende AbstractDocumentoSST)              │
│                           │                                                 │
│                           ▼                                                 │
│  4. getContextoBase($cliente, $contexto)  ← MÉTODO CLAVE (sobrescrito)      │
│              │                      │                                       │
│              ▼                      ▼                                       │
│       ┌──────────────┐      ┌──────────────┐                               │
│       │   PARTE 1    │      │   PARTE 2    │                               │
│       │ Actividades  │      │ Indicadores  │                               │
│       │ tipo_servicio│      │  categoria   │                               │
│       └──────┬───────┘      └──────┬───────┘                               │
│              │                      │                                       │
│              └──────────┬───────────┘                                       │
│                         ▼                                                   │
│  5. Contexto enriquecido con datos REALES del cliente                       │
│                         │                                                   │
│                         ▼                                                   │
│  6. IA genera contenido personalizado                                       │
│                         │                                                   │
│                         ▼                                                   │
│  7. Se guarda en tbl_documentos_sst                                         │
│                         │                                                   │
│                         ▼                                                   │
│  8. DocumentoVersionService maneja versiones (v1.0, v1.1, etc.)            │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Arquitectura de Clases

### Patrón Factory

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         PATRÓN FACTORY                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  DocumentosSSTController                                                    │
│         │                                                                   │
│         │ $handler = DocumentoSSTFactory::crear('tipo_documento')           │
│         ▼                                                                   │
│  ┌─────────────────────────────────────┐                                   │
│  │       DocumentoSSTFactory           │                                   │
│  │  ┌─────────────────────────────┐    │                                   │
│  │  │ $tiposRegistrados = [       │    │                                   │
│  │  │   'tipo_a' => TipoA::class, │    │                                   │
│  │  │   'tipo_b' => TipoB::class, │    │                                   │
│  │  │   ...                       │    │                                   │
│  │  │ ]                           │    │                                   │
│  │  └─────────────────────────────┘    │                                   │
│  └─────────────────┬───────────────────┘                                   │
│                    │                                                        │
│                    ▼                                                        │
│  ┌─────────────────────────────────────┐                                   │
│  │     DocumentoSSTInterface           │  ← Contrato                       │
│  │  - getNombre()                      │                                   │
│  │  - getEstandar()                    │                                   │
│  │  - getSecciones()                   │                                   │
│  │  - getContextoBase()                │                                   │
│  │  - getPromptParaSeccion()           │                                   │
│  └─────────────────┬───────────────────┘                                   │
│                    │                                                        │
│                    ▼                                                        │
│  ┌─────────────────────────────────────┐                                   │
│  │     AbstractDocumentoSST            │  ← Implementación base            │
│  │  - getContextoBase() (por defecto)  │                                   │
│  │  - métodos comunes                  │                                   │
│  └─────────────────┬───────────────────┘                                   │
│                    │                                                        │
│        ┌───────────┴───────────┐                                           │
│        ▼                       ▼                                           │
│  ┌──────────────┐       ┌──────────────┐                                   │
│  │ [TipoDoc A]  │       │ [TipoDoc B]  │  ← Implementaciones específicas   │
│  │              │       │              │                                   │
│  │ SOBRESCRIBE: │       │ SOBRESCRIBE: │                                   │
│  │ getContexto  │       │ getContexto  │                                   │
│  │ Base()       │       │ Base()       │                                   │
│  └──────────────┘       └──────────────┘                                   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Componentes Principales

| Componente | Archivo | Función |
|------------|---------|---------|
| **Factory** | `DocumentoSSTFactory.php` | Instancia el tipo de documento correcto |
| **Interface** | `DocumentoSSTInterface.php` | Contrato que todos los tipos implementan |
| **Clase Base** | `AbstractDocumentoSST.php` | Implementación común por defecto |
| **Tipo Específico** | `[TipoDocumento].php` | Sobrescribe `getContextoBase()` para traer datos de Parte 1 y 2 |
| **Versiones** | `DocumentoVersionService.php` | Maneja versionamiento (v1.0, v1.1, v2.0, etc.) |

---

## El Método Clave: getContextoBase()

### Por qué es importante

Este método es el **puente** entre las Partes 1, 2 y 3. Cada tipo de documento **SOBRESCRIBE** este método para:

1. Consultar actividades de Parte 1 (filtradas por `tipo_servicio`)
2. Consultar indicadores de Parte 2 (filtrados por `categoria`)
3. Formatear todo como contexto para enviar a la IA

### Implementación Base (AbstractDocumentoSST)

```php
// Archivo: app/Libraries/DocumentosSSTTypes/AbstractDocumentoSST.php

public function getContextoBase(array $cliente, ?array $contexto): string
{
    // Implementación por defecto: solo datos básicos del cliente
    $texto = "EMPRESA: {$cliente['nombre_cliente']}\n";
    $texto .= "ACTIVIDAD: " . ($contexto['actividad_economica'] ?? 'N/A') . "\n";
    $texto .= "TRABAJADORES: " . ($contexto['numero_trabajadores'] ?? 'N/A') . "\n";
    // ... etc

    return $texto;
}
```

### Implementación Específica (Sobrescrita)

```php
// Archivo: app/Libraries/DocumentosSSTTypes/[TipoDocumento].php

public function getContextoBase(array $cliente, ?array $contexto): string
{
    $idCliente = $cliente['id_cliente'];
    $anio = date('Y');

    // ═══════════════════════════════════════════════════════════════════
    // PARTE 1: Obtener actividades del Plan de Trabajo
    // ═══════════════════════════════════════════════════════════════════
    $actividadesTexto = $this->obtenerActividades($idCliente, $anio);

    // ═══════════════════════════════════════════════════════════════════
    // PARTE 2: Obtener indicadores
    // ═══════════════════════════════════════════════════════════════════
    $indicadoresTexto = $this->obtenerIndicadores($idCliente);

    // ═══════════════════════════════════════════════════════════════════
    // Construir contexto completo para la IA
    // ═══════════════════════════════════════════════════════════════════
    $contextoBase = "CONTEXTO DE LA EMPRESA\n";
    $contextoBase .= "Nombre: {$cliente['nombre_cliente']}\n";
    $contextoBase .= "Actividad económica: " . ($contexto['actividad_economica'] ?? 'N/A') . "\n";
    $contextoBase .= "Trabajadores: " . ($contexto['numero_trabajadores'] ?? 'N/A') . "\n\n";

    $contextoBase .= "DATOS DEL MÓDULO (Parte 1) - ACTIVIDADES\n";
    $contextoBase .= $actividadesTexto . "\n\n";

    $contextoBase .= "INDICADORES (Parte 2)\n";
    $contextoBase .= $indicadoresTexto . "\n";

    return $contextoBase;
}
```

---

## Métodos Auxiliares para Consumir Parte 1 y 2

### Obtener Actividades (Parte 1)

```php
/**
 * Obtiene actividades del Plan de Trabajo para este documento
 * CONSUME datos de Parte 1
 */
protected function obtenerActividades(int $idCliente, int $anio): string
{
    $db = \Config\Database::connect();

    $actividades = $db->table('tbl_pta_cliente')
        ->where('id_cliente', $idCliente)
        ->where('tipo_servicio', self::TIPO_SERVICIO)  // ← Vinculación con Parte 1
        ->where('YEAR(fecha_propuesta)', $anio)
        ->where('estado_actividad !=', 'CERRADA')
        ->orderBy('fecha_propuesta', 'ASC')
        ->get()
        ->getResultArray();

    if (empty($actividades)) {
        return "No hay actividades definidas para este documento.";
    }

    $texto = "Total: " . count($actividades) . " actividades\n\n";

    foreach ($actividades as $i => $act) {
        $texto .= ($i + 1) . ". {$act['actividad_plandetrabajo']}\n";
        $texto .= "   - Meta: {$act['meta_ptacliente']}\n";
        $texto .= "   - Responsable: {$act['responsable_sugerido_plandetrabajo']}\n";
        $texto .= "   - PHVA: {$act['phva_plandetrabajo']}\n\n";
    }

    return $texto;
}
```

### Obtener Indicadores (Parte 2)

```php
/**
 * Obtiene indicadores para este documento
 * CONSUME datos de Parte 2
 */
protected function obtenerIndicadores(int $idCliente): string
{
    $db = \Config\Database::connect();

    $indicadores = $db->table('tbl_indicadores_sst')
        ->where('id_cliente', $idCliente)
        ->where('categoria', self::CATEGORIA)  // ← Vinculación con Parte 2
        ->where('activo', 1)
        ->orderBy('tipo_indicador', 'ASC')
        ->get()
        ->getResultArray();

    if (empty($indicadores)) {
        return "No hay indicadores configurados para este documento.";
    }

    $texto = "Total: " . count($indicadores) . " indicadores\n\n";

    // Agrupar por tipo
    $porTipo = ['resultado' => [], 'proceso' => [], 'estructura' => []];
    foreach ($indicadores as $ind) {
        $tipo = $ind['tipo_indicador'] ?? 'proceso';
        $porTipo[$tipo][] = $ind;
    }

    foreach ($porTipo as $tipo => $inds) {
        if (!empty($inds)) {
            $texto .= strtoupper("INDICADORES DE " . $tipo) . ":\n";
            foreach ($inds as $i => $ind) {
                $texto .= ($i + 1) . ". {$ind['nombre_indicador']}\n";
                $texto .= "   - Fórmula: {$ind['formula']}\n";
                $texto .= "   - Meta: {$ind['meta']} {$ind['unidad_medida']}\n";
                $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
            }
        }
    }

    return $texto;
}
```

---

## Constantes de Vinculación

### En el Tipo de Documento

```php
// Archivo: app/Libraries/DocumentosSSTTypes/[TipoDocumento].php

class [TipoDocumento] extends AbstractDocumentoSST
{
    /**
     * Vinculación con Parte 1 (tbl_pta_cliente.tipo_servicio)
     */
    protected const TIPO_SERVICIO = '[nombre_tipo_servicio]';

    /**
     * Vinculación con Parte 2 (tbl_indicadores_sst.categoria)
     */
    protected const CATEGORIA = '[nombre_categoria]';

    // ...
}
```

### Tabla de Vinculaciones

| Parte | Tabla | Campo | Valor |
|-------|-------|-------|-------|
| **Parte 1** | `tbl_pta_cliente` | `tipo_servicio` | `'[nombre_documento]'` |
| **Parte 2** | `tbl_indicadores_sst` | `categoria` | `'[nombre_documento]'` |
| **Parte 3** | `tbl_documentos_sst` | `tipo_documento` | `'[nombre_documento]'` |

---

## DocumentoSSTFactory

### Estructura

```php
// Archivo: app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php

class DocumentoSSTFactory
{
    /**
     * Mapeo de tipos registrados
     */
    private static array $tiposRegistrados = [
        'tipo_documento_a' => TipoDocumentoA::class,
        'tipo_documento_b' => TipoDocumentoB::class,
        // ... agregar nuevos tipos aquí
    ];

    /**
     * Cache de instancias (singleton por tipo)
     */
    private static array $instancias = [];

    /**
     * Crear instancia de documento
     */
    public static function crear(string $tipoDocumento): DocumentoSSTInterface
    {
        // Buscar en tipos registrados
        if (isset(self::$tiposRegistrados[$tipoDocumento])) {
            $clase = self::$tiposRegistrados[$tipoDocumento];
        } else {
            // Intentar por convención de nombres
            $clase = self::snakeToPascal($tipoDocumento);
            $clase = "App\\Libraries\\DocumentosSSTTypes\\{$clase}";
        }

        // Cache singleton
        if (!isset(self::$instancias[$tipoDocumento])) {
            self::$instancias[$tipoDocumento] = new $clase();
        }

        return self::$instancias[$tipoDocumento];
    }

    /**
     * Registrar un nuevo tipo dinámicamente
     */
    public static function registrar(string $tipoDocumento, string $clase): void
    {
        self::$tiposRegistrados[$tipoDocumento] = $clase;
    }

    /**
     * Obtener tipos disponibles
     */
    public static function getTiposDisponibles(): array
    {
        // Retorna lista con nombre, descripción, estándar, secciones
    }
}
```

### Registrar un Nuevo Tipo

Al crear un nuevo módulo de 3 partes, **agregar el tipo al Factory**:

```php
// En DocumentoSSTFactory.php

private static array $tiposRegistrados = [
    // ... tipos existentes ...

    // Agregar el nuevo tipo
    '[mi_tipo_documento]' => \App\Libraries\DocumentosSSTTypes\[MiTipoDocumento]::class,
];
```

---

## DocumentoVersionService

### Propósito

Maneja el versionamiento de documentos:

- Primera aprobación → v1.0
- Cambio MENOR → v1.1, v1.2, v1.3...
- Cambio MAYOR → v2.0, v3.0...

### Métodos Principales

```php
// Archivo: app/Services/DocumentoVersionService.php

class DocumentoVersionService
{
    /**
     * Iniciar nueva versión (pone documento en borrador)
     */
    public function iniciarNuevaVersion(int $idDoc, string $tipoCambio, string $descripcion): array

    /**
     * Aprobar versión (crea registro en historial, guarda snapshot)
     */
    public function aprobarVersion(int $idDoc, int $usuarioId, string $usuarioNombre): array

    /**
     * Crear versión inicial (v1.0)
     */
    public function crearVersionInicial(int $idDoc, int $usuarioId, string $usuarioNombre): array

    /**
     * Obtener historial de versiones
     */
    public function obtenerHistorial(int $idDoc, bool $soloVigente = false): array

    /**
     * Restaurar versión anterior
     */
    public function restaurarVersion(int $idDoc, int $idVersionRestaurar, int $usuarioId): array
}
```

### Cálculo de Versiones

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        CÁLCULO DE VERSIONES                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Primera aprobación ───────────────────────────────▶  v1.0                  │
│                                                                             │
│  Cambio MENOR (correcciones, ajustes menores) ────▶  v1.1, v1.2, v1.3...   │
│                                                                             │
│  Cambio MAYOR (restructuración, nuevas secciones) ▶  v2.0, v3.0, v4.0...   │
│                                                                             │
│  Ejemplo de progresión:                                                     │
│  v1.0 → v1.1 → v1.2 → v2.0 → v2.1 → v3.0                                   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Checklist de Implementación (Parte 3)

### Archivos a Crear

- [ ] `app/Libraries/DocumentosSSTTypes/[TipoDocumento].php`

### Modificaciones en Archivos Existentes

- [ ] `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` - Registrar el tipo
- [ ] Verificar que Parte 1 usa `tipo_servicio` correcto
- [ ] Verificar que Parte 2 usa `categoria` correcta

### Constantes a Definir en el Tipo de Documento

```php
class [TipoDocumento] extends AbstractDocumentoSST
{
    protected const TIPO_SERVICIO = '[valor_parte_1]';
    protected const CATEGORIA = '[valor_parte_2]';

    // Implementar métodos de DocumentoSSTInterface:
    public function getNombre(): string;
    public function getDescripcion(): string;
    public function getEstandar(): string;
    public function getSecciones(): array;
    public function getPromptParaSeccion(string $seccion): string;

    // SOBRESCRIBIR para consumir Parte 1 y 2:
    public function getContextoBase(array $cliente, ?array $contexto): string;
}
```

---

## Flujo Completo del Módulo de 3 Partes

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    FLUJO COMPLETO: 3 PARTES                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ PARTE 1: Generador de Actividades                                    │   │
│  │ Vista: /generador-ia/{id}/[nombre-documento]                         │   │
│  │ Guarda en: tbl_pta_cliente (tipo_servicio = '[nombre]')              │   │
│  └──────────────────────────────┬──────────────────────────────────────┘   │
│                                 │                                           │
│                                 ▼                                           │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ PARTE 2: Generador de Indicadores                                    │   │
│  │ Vista: /generador-ia/{id}/indicadores-[nombre-documento]             │   │
│  │ Valida: que Parte 1 esté completa                                    │   │
│  │ Guarda en: tbl_indicadores_sst (categoria = '[nombre]')              │   │
│  └──────────────────────────────┬──────────────────────────────────────┘   │
│                                 │                                           │
│                                 ▼                                           │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ PARTE 3: Generador del Documento Formal                              │   │
│  │ Vista: /documentacion/{id} → Sección del documento                   │   │
│  │ Factory: DocumentoSSTFactory::crear('[tipo_documento]')              │   │
│  │ Consume: Parte 1 (tipo_servicio) + Parte 2 (categoria)               │   │
│  │ Guarda en: tbl_documentos_sst (tipo_documento = '[nombre]')          │   │
│  │ Versiones: DocumentoVersionService                                   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## SweetAlert de Verificacion de Datos

> **Documentacion completa movida a:** [ZZ_90_PARTESWEETALERT.md](ZZ_90_PARTESWEETALERT.md)
>
> Este es un control critico que muestra al usuario exactamente que datos
> (actividades, indicadores, contexto) alimentaran la IA antes de generar.

---

## Notas Importantes

1. **getContextoBase() es OBLIGATORIO sobrescribir** si el documento necesita datos de Parte 1 y/o Parte 2.

2. **Las constantes TIPO_SERVICIO y CATEGORIA** deben coincidir exactamente con los valores usados en Parte 1 y Parte 2.

3. **Registrar en Factory**: Todo nuevo tipo de documento debe registrarse en `DocumentoSSTFactory::$tiposRegistrados`.

4. **El contexto se envía a la IA**: Todo lo que retorne `getContextoBase()` será parte del prompt que recibe la IA para generar el contenido.

5. **Versionamiento automático**: `DocumentoVersionService` calcula automáticamente si es v1.1 o v2.0 según el tipo de cambio.

---

## Relación con ZZ_99_SEGMENT_NUEVO_TIPO_DOCUMENTO_SST.md

Este documento (**ZZ_90_PARTE3.md**) explica la **arquitectura y flujo** del módulo de 3 partes.

Para la **implementación paso a paso**, ver:
📄 **[ZZ_99_SEGMENT_NUEVO_TIPO_DOCUMENTO_SST.md](ZZ_99_SEGMENT_NUEVO_TIPO_DOCUMENTO_SST.md)**

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO DE APRENDIZAJE                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. PRIMERO leer ESTE documento (ZZ_90_PARTE3.md)              │
│     → Entender el concepto de módulo de 3 partes               │
│     → Entender cómo se vinculan tipo_servicio y categoria      │
│     → Entender getContextoBase() y métodos de consumo          │
│                                                                 │
│  2. LUEGO usar ZZ_99_SEGMENT... como guía de implementación    │
│     → Copiar plantilla de clase                                │
│     → Copiar script SQL                                        │
│     → Seguir checklist                                         │
│                                                                 │
│  3. SI el documento es de 3 partes:                            │
│     → Agregar constantes TIPO_SERVICIO y CATEGORIA             │
│     → Sobrescribir getContextoBase() con métodos de ESTE doc   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

| Documento | Propósito | Responde a |
|-----------|-----------|------------|
| **ZZ_90_PARTE3.md** (este) | Arquitectura y flujo | "¿QUÉ es y POR QUÉ funciona así?" |
| **ZZ_99_SEGMENT...md** | Implementación paso a paso | "¿CÓMO lo creo?" |

---

## Botón de Navegación a Parte 3 (En la Vista del Generador IA)

### ⚠️ OBLIGATORIO: Agregar Sección Parte 3 en la Vista

La vista del generador IA (ej: `capacitacion_sst.php`, `objetivos_sgsst.php`) **DEBE incluir** una sección para navegar a la Parte 3 (documento formal).

### Estructura del Botón

```php
<!-- Parte 3: Documento del Programa -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <span class="badge bg-white text-success me-2">3</span>
                    Documento del Programa de [NOMBRE]
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-2">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            El documento formal consolida:
                        </p>
                        <ul class="mb-3">
                            <li>Las actividades definidas en el Plan de Trabajo (Parte 1)</li>
                            <li>Los indicadores de medición configurados (Parte 2)</li>
                            <li>Datos del contexto de la empresa</li>
                        </ul>

                        <?php
                        // Validar que Parte 1 y Parte 2 estén completas
                        $parte1Completa = $resumenActividades['completo'] ?? false;
                        $parte2Completa = $verificacionIndicadores['completo'] ?? false;
                        $puedeGenerarDocumento = $parte1Completa && $parte2Completa;
                        ?>

                        <?php if ($puedeGenerarDocumento): ?>
                            <div class="alert alert-success small mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>Listo para generar</strong> - Parte 1 y Parte 2 completadas
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning small mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Requisitos pendientes:</strong>
                                <ul class="mb-0 mt-1">
                                    <?php if (!$parte1Completa): ?>
                                        <li>Complete la Parte 1 (Actividades)</li>
                                    <?php endif; ?>
                                    <?php if (!$parte2Completa): ?>
                                        <li>Complete la Parte 2 (Indicadores)</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                        <!-- ⚠️ IMPORTANTE: Usar snake_case (guiones bajos), NO kebab-case (guiones) -->
                        <a href="<?= base_url('documentos/generar/{tipo_documento}/' . $cliente['id_cliente']) ?>"
                           class="btn btn-success btn-lg <?= !$puedeGenerarDocumento ? 'disabled' : '' ?>">
                            <i class="bi bi-file-earmark-plus me-2"></i>
                            Ir a Generar Documento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### ⚠️ IMPORTANTE: Formato del tipo_documento en la URL

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                     FORMATO DEL TIPO_DOCUMENTO EN URL                        │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ✅ CORRECTO (snake_case - guiones bajos):                                  │
│     /documentos/generar/programa_capacitacion/18                            │
│     /documentos/generar/programa_promocion_prevencion_salud/18              │
│                                                                              │
│  ❌ INCORRECTO (kebab-case - guiones):                                      │
│     /documentos/generar/programa-capacitacion/18                            │
│     /documentos/generar/programa-promocion-prevencion-salud/18              │
│                                                                              │
│  El segment de la URL DEBE coincidir EXACTAMENTE con:                       │
│  - tbl_doc_tipo_configuracion.tipo_documento                                │
│  - DocumentoSSTFactory::$tiposRegistrados key                               │
│  - El método getTipoDocumento() de la clase                                 │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Ejemplos de URLs Correctas

| Módulo | tipo_documento | URL Correcta |
|--------|----------------|--------------|
| Capacitación SST | `programa_capacitacion` | `/documentos/generar/programa_capacitacion/{id}` |
| Objetivos SG-SST | `plan_objetivos_metas` | `/documentos/generar/plan_objetivos_metas/{id}` |
| PyP Salud | `programa_promocion_prevencion_salud` | `/documentos/generar/programa_promocion_prevencion_salud/{id}` |

### Checklist del Botón Parte 3

- [ ] Agregar sección Parte 3 después de Parte 1 y Parte 2 en la vista
- [ ] Verificar que `$resumenActividades` (o equivalente) esté disponible en la vista
- [ ] Verificar que `$verificacionIndicadores` esté disponible en la vista
- [ ] Usar **snake_case** en el `tipo_documento` de la URL (NO kebab-case)
- [ ] Deshabilitar botón si Parte 1 o Parte 2 no están completas
- [ ] Mostrar alerta indicando qué requisitos faltan

---

## Próximos Pasos

Este es un análisis inicial de la Parte 3. En iteraciones futuras se documentará:

- [ ] Estructura completa de `DocumentoSSTInterface`
- [ ] Implementación detallada de `AbstractDocumentoSST`
- [ ] Métodos del controlador `DocumentosSSTController`
- [ ] Rutas y vistas de la Parte 3
- [ ] Template completo para crear un nuevo tipo de documento
