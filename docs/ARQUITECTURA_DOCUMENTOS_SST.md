# Arquitectura de Documentos SST - Patrón Strategy

## Resumen

Esta arquitectura permite agregar nuevos tipos de documentos del SG-SST de forma escalable, sin modificar el controlador principal. Cada tipo de documento es una clase independiente con sus propios prompts, secciones y lógica.

---

## El Problema (Antes)

```
DocumentosSSTController.php (MONOLÍTICO - 1000+ líneas)
│
├── getPromptsCapacitacion()      ← 200 líneas
├── getPromptsControlDocumental() ← 200 líneas (NUNCA SE USABA)
├── getPromptsPresupuesto()       ← 200 líneas
├── ... 100 métodos más
│
└── generarConIAReal() {
        $tipo = 'programa_capacitacion';  ← HARDCODED!
    }
```

**Problemas:**
- Todo el código en un solo archivo gigante
- Hardcoded a `programa_capacitacion`
- Agregar documento = modificar múltiples lugares
- Alto riesgo de romper funcionalidad existente
- Imposible de testear por separado

---

## La Solución: Patrón Strategy

```
┌─────────────────────────────────────────────────────────────┐
│  DocumentosSSTController                                    │
│                                                             │
│  generarConIA($tipo) {                                      │
│      $handler = DocumentoSSTFactory::crear($tipo);          │
│      return $handler->getPromptParaSeccion($seccion);       │
│  }                                                          │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
              DocumentoSSTFactory::crear('procedimiento_control_documental')
                            │
                            ▼
┌─────────────────────┐ ┌─────────────────────┐ ┌─────────────────────┐
│ ProgramaCapacitacion│ │ ControlDocumental   │ │ [Futuro Documento]  │
│                     │ │                     │ │                     │
│ getSecciones()      │ │ getSecciones()      │ │ getSecciones()      │
│ getPrompt($sec)     │ │ getPrompt($sec)     │ │ getPrompt($sec)     │
│ getFirmantes()      │ │ getFirmantes()      │ │ getFirmantes()      │
└─────────────────────┘ └─────────────────────┘ └─────────────────────┘
      1 archivo              1 archivo              1 archivo
```

---

## Estructura de Archivos

```
app/Libraries/DocumentosSSTTypes/
│
├── DocumentoSSTInterface.php          # Contrato que todos deben cumplir
├── AbstractDocumentoSST.php           # Código común (métodos helper)
├── DocumentoSSTFactory.php            # Crea la instancia correcta
│
├── ProgramaCapacitacion.php           # Documento: Programa de Capacitación
├── ProcedimientoControlDocumental.php # Documento: Control Documental
│
└── [NuevoDocumento].php               # Agregar aquí nuevos documentos
```

---

## Componentes

### 1. Interface (El Contrato)

```php
// DocumentoSSTInterface.php
interface DocumentoSSTInterface
{
    // Identificación
    public function getTipoDocumento(): string;      // 'programa_capacitacion'
    public function getNombre(): string;             // 'Programa de Capacitación'
    public function getDescripcion(): string;
    public function getEstandar(): ?string;          // '2.5.1' o null

    // Estructura
    public function getSecciones(): array;           // Lista de secciones
    public function getFirmantesRequeridos(int $estandares): array;

    // Generación de contenido
    public function getPromptParaSeccion(string $key, int $estandares): string;
    public function getContextoBase(array $cliente, ?array $contexto): string;
    public function getContenidoEstatico(...): string;  // Fallback sin IA
    public function validarSeccion(string $key, string $contenido): bool;

    // Códigos de documento (evita hardcoding)
    public function getCodigoBase(): string;         // 'PRG-CAP' (sin consecutivo)

    // URLs y Rutas (evita hardcoding)
    public function getSlugUrl(): string;            // 'procedimiento-control-documental'
    public function getUrlVistaPrevia(int $idCliente, int $anio): string;
    public function getUrlEditor(int $idCliente): string;
    public function getVistaPath(): string;          // 'documentos_sst/procedimiento_control_documental'
}
```

### 2. Clase Abstracta (Código Común)

```php
// AbstractDocumentoSST.php
abstract class AbstractDocumentoSST implements DocumentoSSTInterface
{
    // Métodos helper que todos usan
    protected function usaVigiaSst(int $estandares): bool;
    protected function getTextoComite(int $estandares): string;
    protected function getNivelTexto(int $estandares): string;
    public function getNombreSeccion(string $seccionKey): string;
    public function getNumeroSeccion(string $seccionKey): int;

    // Implementaciones por defecto
    public function getContextoBase(array $cliente, ?array $contexto): string;
    public function validarSeccion(string $key, string $contenido): bool;
    public function getContenidoEstatico(...): string;

    // URLs (implementación por defecto basada en convenciones)
    public function getSlugUrl(): string;           // Convierte _ a -
    public function getUrlVistaPrevia(int $idCliente, int $anio): string;
    public function getUrlEditor(int $idCliente): string;
    public function getVistaPath(): string;
}
```

### 3. Factory (El Selector)

```php
// DocumentoSSTFactory.php
class DocumentoSSTFactory
{
    public static function crear(string $tipo): DocumentoSSTInterface
    {
        // 1. Busca en mapeo manual
        // 2. O por convención de nombres (snake_case → PascalCase)
        // 3. Lanza excepción si no existe
    }

    public static function existe(string $tipo): bool;
    public static function getTiposDisponibles(): array;
    public static function registrar(string $tipo, string $clase): void;
}
```

### 4. Clase Concreta (Un Documento)

```php
// ProcedimientoControlDocumental.php
class ProcedimientoControlDocumental extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'procedimiento_control_documental';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            // ... 14 secciones
        ];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento...",
            'alcance' => "Define el alcance del procedimiento...",
            // ... prompts específicos
        ];
        return $prompts[$seccionKey] ?? "Genera contenido para '{$seccionKey}'";
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['representante_legal', 'responsable_sst'];
    }
}
```

---

## Cómo Agregar un Nuevo Documento

### Paso 1: Crear la clase

```php
// app/Libraries/DocumentosSSTTypes/MatrizRiesgos.php
<?php

namespace App\Libraries\DocumentosSSTTypes;

class MatrizRiesgos extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'matriz_riesgos';  // Debe coincidir con tbl_documentos_sst.tipo_documento
    }

    public function getNombre(): string
    {
        return 'Matriz de Identificación de Peligros y Valoración de Riesgos';
    }

    public function getDescripcion(): string
    {
        return 'Herramienta para identificar peligros y valorar riesgos laborales';
    }

    public function getEstandar(): ?string
    {
        return '4.1.1';  // Estándar de la Resolución 0312/2019
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Metodología', 'key' => 'metodologia'],
            ['numero' => 3, 'nombre' => 'Identificación de Peligros', 'key' => 'peligros'],
            // ...
        ];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo' => "Genera el objetivo de la matriz de riesgos...",
            'metodologia' => "Describe la metodología GTC-45 para...",
            // ...
        ];
        return $prompts[$seccionKey] ?? "Genera contenido para '{$seccionKey}'";
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst'];  // Solo requiere firma del responsable
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        // Contenido fallback si la IA no está disponible
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';

        $contenidos = [
            'objetivo' => "{$nombreEmpresa} establece esta matriz para identificar...",
            // ...
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico(...);
    }
}
```

### Paso 2: ¡Listo!

El Factory detecta automáticamente la clase por convención de nombres:
- `matriz_riesgos` → busca `MatrizRiesgos`

**No necesitas modificar ningún otro archivo.**

### Paso 3 (Opcional): Registro manual

Si el nombre no sigue la convención snake_case → PascalCase:

```php
// En algún bootstrap o config
DocumentoSSTFactory::registrar('mi_documento_especial', MiClasePersonalizada::class);
```

---

## Flujo de Ejecución

```
Usuario hace clic en "Generar con IA" para sección "alcance"
                    │
                    ▼
DocumentosSSTController::generarSeccionIA()
    │
    ├── $tipo = 'procedimiento_control_documental' (del POST)
    │
    └── generarConIAReal($seccion, ..., $tipo)
                    │
                    ▼
        DocumentoSSTFactory::crear($tipo)
                    │
                    ▼
        new ProcedimientoControlDocumental()
                    │
                    ▼
        $handler->getPromptParaSeccion('alcance', 7)
                    │
                    ▼
        "Define el alcance del procedimiento..."
                    │
                    ▼
        IADocumentacionService::generarSeccion($datosIA)
                    │
                    ▼
        OpenAI genera el contenido
                    │
                    ▼
        Retorna al usuario
```

---

## Beneficios

| Aspecto | Antes | Después |
|---------|-------|---------|
| Agregar documento | Modificar 3+ archivos | Crear 1 archivo |
| Líneas por documento | Mezcladas en controlador | ~200 líneas aisladas |
| Riesgo de romper otro | Alto | Ninguno |
| Testing | Imposible unitario | Fácil por clase |
| Encontrar prompts | Buscar en 1000 líneas | Ir a la clase directa |
| Entender el código | Difícil | Claro |

---

## Documentos Implementados

| Tipo | Clase | Secciones | Estándar |
|------|-------|-----------|----------|
| `programa_capacitacion` | ProgramaCapacitacion | 13 | 1.2.1 |
| `procedimiento_control_documental` | ProcedimientoControlDocumental | 14 | 2.5.1 |

---

## Convención de Nombres

```
tipo_documento (snake_case)  →  NombreClase (PascalCase)
─────────────────────────────────────────────────────────
programa_capacitacion        →  ProgramaCapacitacion
procedimiento_control_documental → ProcedimientoControlDocumental
matriz_riesgos               →  MatrizRiesgos
plan_emergencias             →  PlanEmergencias
```

---

## Métodos Helper Disponibles

La clase `AbstractDocumentoSST` provee:

```php
// Determina si usa Vigía SST (≤10 trabajadores) o COPASST
$this->usaVigiaSst(7);  // true

// Retorna el texto correcto
$this->getTextoComite(7);   // "Vigía de SST"
$this->getTextoComite(21);  // "COPASST"

// Nivel descriptivo
$this->getNivelTexto(7);   // "básico"
$this->getNivelTexto(21);  // "intermedio"
$this->getNivelTexto(60);  // "avanzado"

// Obtener nombre/número de sección por key
$this->getNombreSeccion('alcance');  // "Alcance"
$this->getNumeroSeccion('alcance');  // 2
```

---

## Métodos de URLs (Anti-Hardcoding)

Cada documento conoce sus propias rutas. **Nunca hardcodear URLs en vistas o controladores.**

```php
// En el controlador - obtener URL de vista previa
$handler = DocumentoSSTFactory::crear('procedimiento_control_documental');
$url = $handler->getUrlVistaPrevia($idCliente, $anio);
// Resultado: /documentos-sst/11/procedimiento-control-documental/2026

// Slug para URLs (convierte _ a -)
$handler->getSlugUrl();
// 'procedimiento_control_documental' → 'procedimiento-control-documental'

// URL del editor
$handler->getUrlEditor($idCliente);
// /documentos/generar/procedimiento_control_documental/11

// Ruta de la vista
$handler->getVistaPath();
// 'documentos_sst/procedimiento_control_documental'
```

### Uso en Vistas

```php
// ❌ INCORRECTO (hardcoded)
<a href="<?= base_url('documentos-sst/' . $id . '/programa-capacitacion/' . $anio) ?>">

// ✅ CORRECTO (desde el controlador)
// Controlador pasa: 'urlVistaPrevia' => $handler->getUrlVistaPrevia($id, $anio)
<a href="<?= esc($urlVistaPrevia) ?>">
```

### Personalización por Documento

Si un documento necesita una URL diferente, sobrescribe el método:

```php
class DocumentoEspecial extends AbstractDocumentoSST
{
    public function getSlugUrl(): string
    {
        return 'mi-url-personalizada';  // En lugar de 'documento_especial'
    }
}
```

---

## Convención de Códigos de Documentos

### Estructura del Código

```text
CÓDIGO BASE + CONSECUTIVO = CÓDIGO COMPLETO
   ↓              ↓              ↓
 PRG-CAP    +    001     =   PRG-CAP-001
```

### Dónde se almacena cada parte

| Parte | Ubicación | Ejemplo |
|-------|-----------|---------|
| Código BASE | `tbl_doc_plantillas.codigo_sugerido` | `PRG-CAP`, `FT-SST`, `POL-SST` |
| Consecutivo | Generado automáticamente por cliente | `001`, `002`, `003` |
| Código COMPLETO | `tbl_documentos_sst.codigo_documento` | `PRG-CAP-001` |

### Reglas

```php
// ❌ INCORRECTO - Nunca hardcodear código completo en plantillas
INSERT INTO tbl_doc_plantillas (codigo_sugerido) VALUES ('FT-SST-004');

// ✅ CORRECTO - Solo el código BASE
INSERT INTO tbl_doc_plantillas (codigo_sugerido) VALUES ('FT-SST');

// El código completo se genera automáticamente:
// Cliente 1: FT-SST-001
// Cliente 2: FT-SST-001
// Mismo cliente, segundo doc: FT-SST-002
```

### Prefijos por Tipo de Documento

| Prefijo | Tipo | Ejemplo |
|---------|------|---------|
| `POL-` | Política | POL-SST, POL-ADT |
| `PRG-` | Programa | PRG-CAP, PRG-PVE |
| `PLA-` | Plan | PLA-EME, PLA-PTA |
| `PRO-` | Procedimiento | PRO-DOC, PRO-INV |
| `FOR-` | Formato | FOR-ASI, FOR-EPP |
| `FT-` | Formato SST | FT-SST |
| `REG-` | Reglamento | REG-HSI, REG-COP |
| `MTZ-` | Matriz | MTZ-IPE, MTZ-LEG |
| `MAN-` | Manual | MAN-SST |
| `ACT-` | Acta | ACT-COP, ACT-CCL |

---

## Checklist: Agregar Nuevo Documento

```text
□ 1. Crear clase en app/Libraries/DocumentosSSTTypes/[NombreDocumento].php
     - extends AbstractDocumentoSST
     - Implementar: getTipoDocumento(), getNombre(), getDescripcion()
     - Implementar: getEstandar(), getSecciones(), getFirmantesRequeridos()
     - Implementar: getPromptParaSeccion(), getContenidoEstatico()

□ 2. Crear vista en app/Views/documentos_sst/[tipo_documento].php
     - Copiar estructura de programa_capacitacion.php o procedimiento_control_documental.php
     - Adaptar secciones según getSecciones()

□ 3. Agregar ruta en app/Config/Routes.php
     $routes->get('/documentos-sst/(:num)/[slug-url]/(:num)',
                  'DocumentosSSTController::[metodo]/$1/$2');

□ 4. Agregar método en DocumentosSSTController.php (opcional si usa vista genérica)

□ 5. Registrar en tbl_doc_plantillas (para código de documento)
     INSERT INTO tbl_doc_plantillas (tipo_documento, codigo_sugerido, ...)

□ 6. Agregar botón en carpeta.php correspondiente (app/Views/documentacion/carpeta.php)

□ 7. Probar:
     - Generación de secciones con IA
     - Vista previa
     - Exportación PDF/Word
     - Firmas electrónicas
```

---

## Compatibilidad

- ✅ Documentos existentes siguen funcionando (guardados en BD)
- ✅ Método legacy como fallback si Factory falla
- ✅ No requiere cambios en base de datos
- ✅ Códigos de documento siguen viniendo de `tbl_doc_plantillas`

---

## Auto-Corrección de Códigos Legacy

### Problema

Si un documento fue creado con un código hardcodeado incorrecto (ej: `FT-SST-004` en lugar de `FT-SST-001`), el código permanece en la base de datos hasta que se corrija.

### Solución Implementada

1. **Script de corrección masiva**: `app/SQL/corregir_codigo_presupuesto.php`
   - Ejecutar: `http://[dominio]/sql-runner/corregir_codigo_presupuesto`
   - Actualiza todos los registros existentes en `tbl_documentos_sst` y `tbl_doc_versiones_sst`

2. **Auto-corrección en tiempo de visualización** (DocumentacionController)

   ```php
   // Cuando se muestran documentos en la lista, se auto-corrige el código
   if ($docSST['tipo_documento'] === 'presupuesto_sst' && $docSST['codigo'] !== 'FT-SST-001') {
       $db->table('tbl_documentos_sst')
           ->where('id_documento', $docSST['id_documento'])
           ->update(['codigo' => 'FT-SST-001']);
       // También actualiza tbl_doc_versiones_sst
   }
   ```

3. **Auto-corrección al acceder al documento** (PzpresupuestoSstController::sincronizarConDocumentosSST)

   ```php
   // Corregir código si tiene el formato antiguo
   if ($documentoExistente['codigo'] !== $codigoCompleto) {
       $actualizaciones['codigo'] = $codigoCompleto;
   }
   ```

### Prevención Futura

Para evitar que se vuelvan a hardcodear códigos:

1. **Usar siempre `getCodigoBase()` + consecutivo** para generar códigos
2. **En tbl_doc_plantillas**: Solo almacenar el PREFIJO (ej: `FT-SST`), no el código completo
3. **En vistas**: Usar la variable `$codigoDocumento` pasada desde el controlador
4. **Revisar antes de commit**: Buscar patrones como `FT-SST-\d{3}` hardcodeados

---

## Autor

Implementado: 2026-01-31
Actualizado: 2026-01-31 (Auto-corrección de códigos legacy)
Patrón: Strategy + Factory
Framework: CodeIgniter 4
