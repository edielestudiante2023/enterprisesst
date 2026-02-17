# Plan: Separar naturaleza de "Generar con IA" individual vs "Generar Todo"

## Problema
Ambos botones ejecutan el mismo pipeline pesado (`getContextoBase` + marco normativo + prompt). El input del usuario en el textarea queda sepultado bajo ~3000 tokens de datos BD y la IA lo ignora.

## Solución
Agregar parámetro `modo` al endpoint existente `POST /documentos/generar-seccion`:
- `modo=completo` (default, usado por "Generar Todo"): pipeline actual sin cambios
- `modo=regenerar` (usado por botones individuales): modo ligero

## Archivos a modificar (3)

### 1. `app/Views/documentos_sst/generar_con_ia.php` (JS)

**Función `generarSeccion(seccionKey)`** (~línea 1037):
- Agregar parámetro `modo` a la función: `generarSeccion(seccionKey, modo = 'regenerar')`
- Cuando `modo=regenerar`: enviar también `contenido_actual` (el valor del textarea de la sección)
- El body AJAX incluirá: `&modo=${modo}&contenido_actual=${encodeURIComponent(contenidoActual)}`

**Event listener botones individuales** (~línea 1026):
- `.btn-generar, .btn-regenerar` → llaman `generarSeccion(seccion, 'regenerar')` (modo ligero)

**Event listener "Generar Todo"** (~línea 1408):
- En el loop, llamar `generarSeccion(seccionKey, 'completo')` (pipeline completo)

### 2. `app/Controllers/DocumentosSSTController.php`

**Método `generarSeccionIA()`** (~línea 614):
- Leer `$modo = $this->request->getPost('modo') ?? 'completo'`
- Leer `$contenidoActual = $this->request->getPost('contenido_actual') ?? ''`
- Si `modo=regenerar`:
  - NO llamar `$documentoHandler->getContextoBase()` → pasar string vacío
  - NO llamar `MarcoNormativoService` → pasar null
  - NO llamar `getMetadataConsultas()` → no aplica
  - Sí obtener `getPromptParaSeccion()` (estructura de la sección)
  - Pasar `contenido_actual` en `$datosIA`
- Si `modo=completo`: pipeline actual sin cambios

Refactorizar en `generarConIAReal()`: agregar parámetro `$modo` y `$contenidoActual`:
```php
protected function generarConIAReal(..., string $modo = 'completo', string $contenidoActual = ''): string
{
    $documentoHandler = DocumentoSSTFactory::crear($tipoDocumento);
    $nombreSeccion = $documentoHandler->getNombreSeccion($seccion);
    $numeroSeccion = $documentoHandler->getNumeroSeccion($seccion);
    $promptBase = $documentoHandler->getPromptParaSeccion($seccion, $estandares);

    // DIFERENCIA según modo
    if ($modo === 'regenerar') {
        $contextoBase = '';       // Sin queries BD pesadas
        $marcoNormativo = null;   // Sin marco normativo
    } else {
        $contextoBase = $documentoHandler->getContextoBase($cliente, $contexto);
        $marcoNormativo = ...;    // Pipeline actual
    }

    $datosIA = [
        ...
        'contenido_actual' => $contenidoActual,  // NUEVO
        'modo' => $modo                           // NUEVO
    ];
}
```

### 3. `app/Services/IADocumentacionService.php`

**Método `construirPrompt()`** (~línea 59):
- Leer `$modo = $datos['modo'] ?? 'completo'`
- Leer `$contenidoActual = $datos['contenido_actual'] ?? ''`

Cuando `modo=regenerar`:
- El `contexto_base` ya viene vacío → los `if (!empty(...))` lo saltan automáticamente
- El `marco_normativo` ya viene null → lo salta automáticamente
- Si hay `contenido_actual`, agregarlo como referencia:
  ```
  CONTENIDO ACTUAL DE LA SECCIÓN (usa como referencia para mejorar):
  {$contenidoActual}
  ```
- Elevar prioridad del `contexto_adicional` del usuario:
  ```
  INSTRUCCIÓN PRINCIPAL DEL USUARIO (PRIORIDAD MÁXIMA - seguir estas indicaciones):
  {$contextoAdicional}
  ```
- El `prompt_base` de la sección se mantiene (da estructura) pero con nota:
  ```
  NOTA: Si las instrucciones del usuario contradicen esta guía, PRIORIZA las instrucciones del usuario.
  ```

## Flujo resultante

### "Generar con IA" individual (modo=regenerar)
```
Usuario escribe "enfócate en riesgo psicosocial"
  → JS envía: modo=regenerar, contexto_adicional, contenido_actual
  → Controller: salta getContextoBase() y marco normativo
  → IA recibe: empresa básica + sección estructura + contenido actual + "enfócate en riesgo psicosocial" (PRIORIDAD MÁXIMA)
  → Resultado: IA obedece la instrucción directamente
```

### "Generar Todo" (modo=completo)
```
  → JS envía: modo=completo (sin contexto_adicional)
  → Controller: pipeline completo (getContextoBase + marco + metadata)
  → IA recibe: empresa + PTA + indicadores + etapas + marco normativo + prompt sección
  → Resultado: generación inicial basada en datos reales
```

## Lo que NO cambia
- Rutas (mismo endpoint POST /documentos/generar-seccion)
- Botón "Regenerar" (usa mismo modo=regenerar que "Generar con IA")
- SweetAlerts de verificación previos
- El system prompt de IADocumentacionService (reglas de redacción, escalado por estándares)
- Los prompts por sección (`getPromptParaSeccion`) se mantienen como guía estructural
