# Sistema de Prompts para Generación IA

## 📋 Objetivo
Documentar cómo se alimentan, procesan y ejecutan los prompts pregeneradores de la IA para cada sección de cada documento SST.

---

## 🔄 Flujo Completo del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                    1. DEFINICIÓN DE PROMPTS                     │
│                   (Clase PHP del Documento)                     │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                  2. OBTENCIÓN EN CONTROLLER                     │
│              (DocumentosSSTController::generarConIA)            │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                   3. RENDERIZADO EN FRONTEND                    │
│                 (app/Views/documentos_sst/generar_con_ia.php)   │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                4. EJECUCIÓN DE GENERACIÓN IA                    │
│           (DocumentosSSTController::generarSeccionIA)           │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                     5. LLAMADA A API IA                         │
│                (OpenAIService::generarContenido)                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 1️⃣ Definición de Prompts (Clase PHP)

### Ubicación
```
app/Libraries/DocumentosSSTTypes/
├── PoliticaDesconexionLaboral.php
├── PoliticaDiscriminacion.php
├── PoliticaAcosoLaboral.php
└── ...
```

### Método Clave: `getPromptParaSeccion()`

```php
public function getPromptParaSeccion(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
{
    $nombreEmpresa = $cliente['nombre_cliente'] ?? 'LA EMPRESA';
    $comite = $this->getTextoComite($estandares);

    $prompts = [
        'objetivo' => "Genera el objetivo de la Politica...
            IMPORTANTE: Maximo 2-3 parrafos.",

        'alcance' => "Define el alcance de la Politica...
            Para empresas de {$estandares} estandares, ajusta...",

        'marco_legal' => "Genera el marco normativo completo...
            NORMAS BASE OBLIGATORIAS:
            1. Ley 2191 de 2022...
            2. Decreto 1072 de 2015...
            NORMAS COMPLEMENTARIAS (buscar):
            - Resolucion 2646/2008...
            INSTRUCCION: Incluir TODAS las normas base + complementarias vigentes a {$anio}.",
    ];

    return $prompts[$seccionKey] ?? "Genera el contenido para '{$seccionKey}'.";
}
```

### Variables Dinámicas Disponibles

| Variable | Fuente | Ejemplo |
|----------|--------|---------|
| `{$nombreEmpresa}` | `$cliente['nombre_cliente']` | "CYCLOID TALENT SAS" |
| `{$estandares}` | `$contexto['estandares_aplicables']` | 7, 21, 60 |
| `{$comite}` | `getTextoComite($estandares)` | "Vigía SST" o "COPASST" |
| `{$anio}` | Parámetro método | 2026 |
| `{$nit}` | `$cliente['nit']` | "900123456-7" |
| `{$sector}` | `$contexto['sector_economico']` | "Tecnología" |

---

## 2️⃣ Obtención en Controller

### Archivo: `app/Controllers/DocumentosSSTController.php`

### Método: `generarConIA()`

```php
public function generarConIA(string $tipo, int $idCliente)
{
    // ...

    // Línea 246-250: Obtener handler desde Factory
    $documentoHandler = DocumentoSSTFactory::crear($tipo);

    // Línea 290: Secciones con prompts
    $data = [
        'secciones' => $secciones,  // Cada sección tiene 'prompt_ia' de BD
        'documentoHandler' => $documentoHandler,  // Clase PHP para getPromptParaSeccion()
        // ...
    ];

    return view('documentos_sst/generar_con_ia', $data);
}
```

### ¿De Dónde Vienen los Prompts?

**Fuente 1: Base de Datos** (`tbl_doc_secciones_config.prompt_ia`)
- Prompts genéricos almacenados en BD
- Se usan si NO hay clase PHP específica

**Fuente 2: Clase PHP** (`DocumentoHandler::getPromptParaSeccion()`)
- Prompts dinámicos con variables
- **PRIORIDAD SOBRE BD** si existe método
- Permite lógica condicional

### Ejemplo de Sección en BD:

```sql
SELECT nombre, prompt_ia
FROM tbl_doc_secciones_config
WHERE id_tipo_config = 123 AND seccion_key = 'marco_legal';
```

| nombre | prompt_ia |
|--------|-----------|
| Marco Legal | Lista el marco normativo aplicable. |

---

## 3️⃣ Renderizado en Frontend

### Archivo: `app/Views/documentos_sst/generar_con_ia.php`

### Sección HTML (líneas ~120-180):

```php
<?php foreach ($secciones as $index => $seccion): ?>
    <div class="seccion-item">
        <h5><?= esc($seccion['nombre']) ?></h5>

        <?php if ($usaIA): ?>
            <!-- Botón Generar con IA -->
            <button
                class="btn btn-primary btn-generar-ia"
                data-seccion="<?= esc($seccion['key']) ?>"
                data-nombre="<?= esc($seccion['nombre']) ?>">
                <i class="bi bi-stars"></i> Generar con IA
            </button>
        <?php endif; ?>

        <!-- Textarea para contenido -->
        <textarea
            id="contenido_<?= esc($seccion['key']) ?>"
            class="form-control">
            <?= esc($seccion['contenido'] ?? '') ?>
        </textarea>
    </div>
<?php endforeach; ?>
```

### JavaScript para Generación IA (líneas ~500-600):

```javascript
$(document).on('click', '.btn-generar-ia', function() {
    const seccionKey = $(this).data('seccion');
    const seccionNombre = $(this).data('nombre');

    // AJAX para generar contenido
    $.ajax({
        url: '<?= base_url('documentos/generar-seccion-ia') ?>',
        method: 'POST',
        data: {
            id_cliente: <?= $cliente['id_cliente'] ?>,
            tipo: '<?= esc($tipo) ?>',
            seccion: seccionKey,
            anio: <?= $anio ?>
        },
        success: function(response) {
            if (response.success) {
                // Insertar contenido generado
                $(`#contenido_${seccionKey}`).val(response.contenido);
            }
        }
    });
});
```

---

## 4️⃣ Ejecución de Generación IA

### Archivo: `app/Controllers/DocumentosSSTController.php`

### Método: `generarSeccionIA()` (líneas ~596-700)

```php
public function generarSeccionIA()
{
    $idCliente = $this->request->getPost('id_cliente');
    $tipo = $this->request->getPost('tipo');
    $seccionKey = $this->request->getPost('seccion');
    $anio = $this->request->getPost('anio') ?? date('Y');

    // 1. Obtener cliente y contexto
    $cliente = $this->clienteModel->find($idCliente);
    $contexto = $contextoModel->getByCliente($idCliente);
    $estandares = $contexto['estandares_aplicables'] ?? 7;

    // 2. Obtener prompt desde CLASE PHP (prioridad)
    $prompt = null;
    try {
        $handler = DocumentoSSTFactory::crear($tipo);
        if (method_exists($handler, 'getPromptParaSeccion')) {
            $prompt = $handler->getPromptParaSeccion(
                $seccionKey,
                $cliente,
                $contexto,
                $estandares,
                $anio
            );
        }
    } catch (\Exception $e) {
        log_message('info', "Factory no disponible para '$tipo'");
    }

    // 3. Fallback: Obtener prompt desde BD
    if (!$prompt) {
        $seccionConfig = $this->db->table('tbl_doc_secciones_config')
            ->where('seccion_key', $seccionKey)
            ->get()
            ->getRowArray();

        $prompt = $seccionConfig['prompt_ia'] ?? "Genera contenido para {$seccionKey}";
    }

    // 4. Construir contexto completo para IA
    $contextoCompleto = $this->construirContextoIA(
        $cliente,
        $contexto,
        $tipo,
        $estandares
    );

    // 5. Llamar servicio IA
    $openAIService = new OpenAIService();
    $contenido = $openAIService->generarContenido(
        $prompt,
        $contextoCompleto
    );

    return $this->response->setJSON([
        'success' => true,
        'contenido' => $contenido,
        'prompt_usado' => $prompt  // Debug
    ]);
}
```

### Método Helper: `construirContextoIA()`

```php
protected function construirContextoIA($cliente, $contexto, $tipo, $estandares)
{
    return [
        'empresa' => [
            'nombre' => $cliente['nombre_cliente'],
            'nit' => $cliente['nit'],
            'sector' => $contexto['sector_economico'] ?? 'No especificado',
            'trabajadores' => $contexto['total_trabajadores'] ?? 0,
            'ciudad' => $cliente['ciudad'] ?? 'Colombia'
        ],
        'sgsst' => [
            'estandares' => $estandares,
            'nivel_riesgo' => $contexto['nivel_riesgo_arl'] ?? 'I',
            'comite' => $estandares <= 10 ? 'Vigía SST' : 'COPASST'
        ],
        'documento' => [
            'tipo' => $tipo,
            'anio' => date('Y')
        ]
    ];
}
```

---

## 5️⃣ Llamada a API IA

### Archivo: `app/Services/OpenAIService.php`

```php
public function generarContenido(string $prompt, array $contexto): string
{
    // Construir mensaje del sistema
    $systemMessage = "Eres un experto en Seguridad y Salud en el Trabajo en Colombia.
Generas contenido profesional para documentos del SG-SST.
Contexto de la empresa: " . json_encode($contexto, JSON_UNESCAPED_UNICODE);

    // Llamada a OpenAI API
    $response = $this->client->chat()->create([
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 2000
    ]);

    return $response['choices'][0]['message']['content'];
}
```

---

## 📊 Diagrama de Decisión: ¿Qué Prompt Se Usa?

```
┌────────────────────────────────────────┐
│ Generar Sección con IA                 │
└────────────────────────────────────────┘
                ↓
┌────────────────────────────────────────┐
│ ¿Existe clase PHP en Factory?         │
└────────────────────────────────────────┘
        ↓ SÍ              ↓ NO
┌───────────────┐   ┌───────────────────┐
│ ¿Tiene método │   │ Usar prompt de BD │
│ getPrompt...? │   │ (genérico)        │
└───────────────┘   └───────────────────┘
  ↓ SÍ    ↓ NO
┌─────┐  ┌─────────────┐
│ Usar│  │ Usar prompt │
│PHP  │  │ de BD       │
└─────┘  └─────────────┘
```

---

## 🔧 Cómo Personalizar Prompts

### Opción A: Modificar Clase PHP Existente

**Archivo:** `app/Libraries/DocumentosSSTTypes/PoliticaDesconexionLaboral.php`

**Ventajas:**
- ✅ Control total sobre el prompt
- ✅ Variables dinámicas disponibles
- ✅ Lógica condicional por estándares/contexto

**Desventajas:**
- ❌ Requiere deploy de código

### Opción B: Modificar Prompt en BD

**Tabla:** `tbl_doc_secciones_config`

```sql
UPDATE tbl_doc_secciones_config
SET prompt_ia = 'Nuevo prompt...'
WHERE seccion_key = 'marco_legal'
  AND id_tipo_config = (
      SELECT id_tipo_config
      FROM tbl_doc_tipo_configuracion
      WHERE tipo_documento = 'politica_desconexion_laboral'
  );
```

**Ventajas:**
- ✅ Cambio inmediato sin deploy
- ✅ Puede hacerse desde admin panel

**Desventajas:**
- ❌ Sin variables dinámicas
- ❌ Sin lógica condicional

---

## 🎯 Mejores Prácticas para Prompts

### 1. Estructura Clara

```
[ACCIÓN] + [CONTEXTO] + [REQUISITOS] + [FORMATO]
```

**Ejemplo:**
```
Genera el marco normativo completo aplicable a la Política...
[ACCIÓN: Genera]

Para {nombreEmpresa} con {estandares} estándares aplicables.
[CONTEXTO: Empresa específica]

OBLIGATORIO incluir TODAS las normas base + complementarias vigentes.
[REQUISITOS: Qué debe incluir]

FORMATO: Lista con viñetas descriptivas, orden cronológico.
[FORMATO: Cómo presentar]
```

### 2. Variables Dinámicas

Siempre usar variables para datos del cliente:
```php
"Para {$nombreEmpresa} (NIT {$nit}), con {$estandares} estándares..."
```

NO hardcodear:
```php
"Para LA EMPRESA, con 21 estándares..."  // ❌ MAL
```

### 3. Instrucciones Explícitas

**Bueno:**
```
OBLIGATORIO incluir TODAS las siguientes normas:
1. Ley 2191 de 2022
2. Decreto 1072 de 2015
...
IMPORTANTE: NO omitir ninguna norma base.
```

**Malo:**
```
Lista las normas aplicables.
```

### 4. Normas Complementarias

Invitar a la IA a buscar actualizaciones:
```
NORMAS COMPLEMENTARIAS (buscar e incluir si aplican):
- Resoluciones posteriores a 2022 que complementen la Ley 2191
- Circulares del Ministerio del Trabajo vigentes a {$anio}
- Jurisprudencia relevante de la Corte Constitucional
```

---

## 🧪 Validación de Prompts

### Checklist por Sección

- [ ] **Variables dinámicas:** ¿Usa `{$nombreEmpresa}`, `{$estandares}`, etc.?
- [ ] **Normas base:** ¿Lista explícitamente las normas obligatorias?
- [ ] **Normas complementarias:** ¿Invita a buscar actualizaciones?
- [ ] **Contexto SST:** ¿Incluye normatividad SST (Decreto 1072, Res. 0312)?
- [ ] **Formato:** ¿Especifica formato de salida (lista, tabla, párrafos)?
- [ ] **Año vigente:** ¿Usa `{$anio}` para normatividad actualizada?
- [ ] **Comité correcto:** ¿Usa `{$comite}` para Vigía/COPASST?

### Script de Prueba

Crear documento de prueba:
```bash
# Generar política con empresa ficticia
curl -X POST https://dashboard.cycloidtalent.com/documentos/generar-seccion-ia \
  -d "id_cliente=999" \
  -d "tipo=politica_desconexion_laboral" \
  -d "seccion=marco_legal" \
  -d "anio=2026"
```

Verificar:
1. ¿Incluye todas las normas base?
2. ¿Agregó normas complementarias relevantes?
3. ¿Usó el nombre correcto de la empresa?
4. ¿Menciona Vigía SST o COPASST según corresponda?

---

## 📚 Documentos para Validar

### Políticas Numeral 2.1.1 (7 documentos)

| Documento | Clase PHP | Prompt Marco Legal | Estado |
|-----------|-----------|-------------------|--------|
| Política SST General | `PoliticaSSTGeneral.php` | ✅ Tiene | Validar |
| Política Alcohol/SPA | `PoliticaAlcoholDrogas.php` | ✅ Tiene | Validar |
| Política Acoso Laboral | `PoliticaAcosoLaboral.php` | ✅ Tiene | Validar |
| Política Violencias Género | `PoliticaViolenciasGenero.php` | ✅ Tiene | Validar |
| Política Discriminación | `PoliticaDiscriminacion.php` | ✅ Tiene | Validar |
| **Política Desconexión Laboral** | `PoliticaDesconexionLaboral.php` | ✅ **MEJORADO** | ✅ OK |
| Política Emergencias | `PoliticaPrevencionEmergencias.php` | ✅ Tiene | Validar |

---

## 🔄 Próximos Pasos

1. **Validar prompts actuales** de las 7 políticas
2. **Estandarizar formato** de Marco Legal (Enfoque B)
3. **Implementar SweetAlert** para normas adicionales del consultor
4. **Extender a otros documentos** (~36 módulos totales)

---

## 📖 Referencias

- Clase base: `app/Libraries/DocumentosSSTTypes/AbstractDocumentoSST.php`
- Factory: `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`
- Controller: `app/Controllers/DocumentosSSTController.php`
- Vista generador: `app/Views/documentos_sst/generar_con_ia.php`
- Servicio IA: `app/Services/OpenAIService.php`
