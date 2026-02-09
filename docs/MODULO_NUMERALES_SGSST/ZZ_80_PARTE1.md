# INSTRUCTIVO: Crear Módulo IA - PARTE 1: Generador de Actividades para el Plan de Trabajo

## Propósito de Este Documento

Este instructivo explica cómo crear la **PARTE 1** de un módulo de generación de documentos SST. La Parte 1 es el **Generador de Actividades** que alimenta el Plan de Trabajo Anual (PTA) del cliente con actividades específicas para el documento que se está implementando.

---

## Concepto Fundamental

Cada documento del SG-SST requiere actividades concretas que el cliente debe ejecutar durante el año. Estas actividades se registran en el **Plan de Trabajo Anual** y posteriormente se reflejan en el documento formal.

```
CONTEXTO DEL CLIENTE
        │
        ▼
┌───────────────────────────────┐
│   GENERADOR DE ACTIVIDADES    │
│         (PARTE 1)             │
│                               │
│  • Lee contexto del cliente   │
│  • Idea actividades acordes   │
│  • Ajusta cantidad según      │
│    estándares aplicables      │
│  • Guarda en Plan de Trabajo  │
└───────────────────────────────┘
        │
        ▼
   tbl_pta_cliente
   (Plan de Trabajo Anual)
        │
        ▼
   DOCUMENTO FORMAL
   (consume estas actividades)
```

---

## Arquitectura de la Parte 1

### Componentes a Crear

| # | Componente | Ubicación | Propósito |
|---|------------|-----------|-----------|
| 1 | Servicio | `app/Services/[NombreDocumento]Service.php` | Lógica de generación de actividades |
| 2 | Métodos en Controlador | `app/Controllers/GeneradorIAController.php` | Endpoints para la vista |
| 3 | Vista | `app/Views/generador_ia/[nombre_documento].php` | Interfaz de usuario |
| 4 | Rutas | `app/Config/Routes.php` | URLs de acceso |

---

## Paso 1: Crear el Servicio

### Ubicación
```
app/Services/[NombreDocumento]Service.php
```

### Estructura del Servicio

```php
<?php

namespace App\Services;

use App\Models\ClienteContextoSstModel;
use App\Models\PtaClienteModel;

/**
 * Servicio para generar actividades del [Nombre del Documento]
 *
 * Este servicio:
 * 1. Lee el contexto del cliente
 * 2. Genera actividades específicas para el documento
 * 3. Permite personalización con IA
 * 4. Guarda las actividades en el Plan de Trabajo Anual
 */
class [NombreDocumento]Service
{
    protected $db;
    protected $openaiService;

    // Numeral del estándar según Resolución 0312/2019
    protected string $numeralEstandar = 'X.X.X';

    // Identificador del tipo de servicio en tbl_pta_cliente
    protected string $tipoServicio = '[Nombre del Documento]';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->openaiService = new OpenAIService();
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 1: OBTENER CONTEXTO DEL CLIENTE
     * ═══════════════════════════════════════════════════════════════
     *
     * Lee toda la información relevante del cliente para personalizar
     * las actividades generadas.
     */
    public function getContextoCliente(int $idCliente): array
    {
        // Datos básicos del cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        // Contexto SST (estándares, actividad económica, nivel de riesgo)
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        return [
            'cliente' => $cliente,
            'nombre_empresa' => $cliente['nombre_cliente'] ?? 'la empresa',
            'estandares' => (int)($contexto['estandares_aplicables'] ?? 7),
            'actividad_economica' => $contexto['actividad_economica_principal'] ?? 'No especificada',
            'nivel_riesgo' => $contexto['nivel_riesgo_arl'] ?? 'No especificado',
            'numero_trabajadores' => $contexto['numero_trabajadores'] ?? 0,
            'peligros_principales' => $contexto['peligros_principales'] ?? [],
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 2: DETERMINAR LÍMITE DE ACTIVIDADES
     * ═══════════════════════════════════════════════════════════════
     *
     * Calcula cuántas actividades generar según los estándares
     * aplicables al cliente.
     *
     * REGLA:
     * - 7 estándares  (empresas pequeñas, riesgo I-III)  → 3 actividades
     * - 21 estándares (empresas medianas, riesgo I-III)  → 5 actividades
     * - 60 estándares (empresas grandes o riesgo IV-V)   → 8 actividades
     */
    public function getLimiteActividades(int $estandares): int
    {
        return match(true) {
            $estandares <= 7  => 3,
            $estandares <= 21 => 5,
            default           => 8
        };
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 3: DEFINIR ACTIVIDADES BASE
     * ═══════════════════════════════════════════════════════════════
     *
     * Define las actividades típicas/base para este tipo de documento.
     * Estas son actividades genéricas que luego se personalizan con IA
     * según el contexto del cliente.
     *
     * IMPORTANTE: Las actividades deben estar orientadas 100% al tema
     * del documento. No mezclar actividades de otros programas.
     *
     * Cada actividad debe tener:
     * - actividad: Nombre corto de la actividad
     * - descripcion: Qué se debe hacer
     * - meta: Resultado esperado (SMART)
     * - responsable: Quién ejecuta
     * - phva: Fase del ciclo (PLANEAR/HACER/VERIFICAR/ACTUAR)
     * - periodicidad: Frecuencia de ejecución
     */
    public function getActividadesBase(): array
    {
        return [
            [
                'actividad' => '[Actividad 1 - Nombre corto]',
                'descripcion' => '[Descripción detallada de qué hacer]',
                'meta' => '[Meta SMART: específica, medible, alcanzable]',
                'responsable' => 'Responsable del SG-SST',
                'phva' => 'PLANEAR',
                'periodicidad' => 'Anual'
            ],
            [
                'actividad' => '[Actividad 2]',
                'descripcion' => '[Descripción]',
                'meta' => '[Meta SMART]',
                'responsable' => 'Responsable del SG-SST',
                'phva' => 'HACER',
                'periodicidad' => 'Trimestral'
            ],
            // ... más actividades base según el documento
            // Definir al menos 8 actividades para cubrir todos los escenarios
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 4: PREVIEW DE ACTIVIDADES
     * ═══════════════════════════════════════════════════════════════
     *
     * Genera una vista previa de las actividades SIN guardar en BD.
     * Permite al usuario revisar, editar y aprobar antes de guardar.
     *
     * @param int $idCliente ID del cliente
     * @param string|null $instrucciones Instrucciones adicionales para IA
     * @return array Actividades generadas para preview
     */
    public function previewActividades(int $idCliente, ?string $instrucciones = null): array
    {
        // 1. Obtener contexto del cliente
        $contexto = $this->getContextoCliente($idCliente);

        // 2. Determinar cantidad de actividades
        $limite = $this->getLimiteActividades($contexto['estandares']);

        // 3. Obtener actividades base (limitadas según estándares)
        $actividadesBase = array_slice($this->getActividadesBase(), 0, $limite);

        // 4. Si hay instrucciones, personalizar con IA
        if (!empty($instrucciones)) {
            $actividadesBase = $this->personalizarConIA(
                $actividadesBase,
                $contexto,
                $instrucciones
            );
        }

        return [
            'success' => true,
            'actividades' => $actividadesBase,
            'limite' => $limite,
            'estandares' => $contexto['estandares'],
            'contexto' => $contexto
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 5: PERSONALIZAR CON IA
     * ═══════════════════════════════════════════════════════════════
     *
     * Usa OpenAI para personalizar las actividades base según:
     * - Actividad económica del cliente
     * - Nivel de riesgo
     * - Peligros identificados
     * - Instrucciones adicionales del usuario
     */
    protected function personalizarConIA(array $actividades, array $contexto, string $instrucciones): array
    {
        $prompt = $this->construirPromptPersonalizacion($actividades, $contexto, $instrucciones);

        try {
            $respuesta = $this->openaiService->chat([
                'model' => 'gpt-4o-mini',
                'temperature' => 0.3,
                'max_tokens' => 2000,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

            return $this->parsearRespuestaIA($respuesta, $actividades);

        } catch (\Exception $e) {
            log_message('error', 'Error personalizando actividades con IA: ' . $e->getMessage());
            return $actividades; // Retornar actividades base sin personalizar
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 6: CONSTRUIR PROMPT DEL SISTEMA
     * ═══════════════════════════════════════════════════════════════
     *
     * Define el rol y las instrucciones generales para la IA.
     */
    protected function getSystemPrompt(): string
    {
        return "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es personalizar actividades para el Plan de Trabajo Anual de una empresa.

REGLAS:
1. Las actividades deben ser específicas para el contexto de la empresa
2. Las metas deben ser SMART (específicas, medibles, alcanzables, relevantes, temporales)
3. Usa terminología de la normativa colombiana (Decreto 1072/2015, Resolución 0312/2019)
4. Ajusta la complejidad según el tamaño de la empresa y nivel de riesgo
5. Responde SOLO en formato JSON válido

FORMATO DE RESPUESTA:
{
  \"actividades\": [
    {
      \"actividad\": \"Nombre corto\",
      \"descripcion\": \"Descripción detallada\",
      \"meta\": \"Meta SMART\",
      \"responsable\": \"Rol responsable\",
      \"phva\": \"PLANEAR|HACER|VERIFICAR|ACTUAR\",
      \"periodicidad\": \"Frecuencia\"
    }
  ]
}";
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 7: CONSTRUIR PROMPT DE PERSONALIZACIÓN
     * ═══════════════════════════════════════════════════════════════
     */
    protected function construirPromptPersonalizacion(array $actividades, array $contexto, string $instrucciones): string
    {
        $actividadesJson = json_encode($actividades, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$contexto['nombre_empresa']}
- Actividad económica: {$contexto['actividad_economica']}
- Nivel de riesgo ARL: {$contexto['nivel_riesgo']}
- Número de trabajadores: {$contexto['numero_trabajadores']}
- Estándares aplicables: {$contexto['estandares']}

ACTIVIDADES BASE A PERSONALIZAR:
{$actividadesJson}

INSTRUCCIONES DEL USUARIO:
{$instrucciones}

TAREA:
Personaliza las actividades anteriores considerando el contexto de la empresa.
Mantén la estructura pero adapta el contenido a la realidad de esta empresa.
Responde SOLO con el JSON de actividades personalizadas.";
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 8: GENERAR Y GUARDAR ACTIVIDADES
     * ═══════════════════════════════════════════════════════════════
     *
     * Guarda las actividades aprobadas en la tabla tbl_pta_cliente.
     * Esta es la acción final después del preview y edición.
     *
     * @param int $idCliente ID del cliente
     * @param array $actividades Actividades aprobadas por el usuario
     * @return array Resultado de la operación
     */
    public function generarActividades(int $idCliente, array $actividades): array
    {
        $anio = (int)date('Y');
        $creadas = 0;
        $existentes = 0;

        foreach ($actividades as $actividad) {
            // Verificar si ya existe esta actividad
            $existe = $this->db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('tipo_servicio', $this->tipoServicio)
                ->where('numeral_plandetrabajo', $this->numeralEstandar)
                ->like('actividad_plandetrabajo', $actividad['actividad'])
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            // Insertar nueva actividad
            $datos = [
                'id_cliente' => $idCliente,
                'tipo_servicio' => $this->tipoServicio,
                'numeral_plandetrabajo' => $this->numeralEstandar,
                'phva_plandetrabajo' => $actividad['phva'] ?? 'HACER',
                'actividad_plandetrabajo' => $this->formatearActividadPTA($actividad),
                'responsable_sugerido_plandetrabajo' => $actividad['responsable'] ?? 'Responsable del SG-SST',
                'fecha_propuesta' => $this->calcularFechaPropuesta($actividad['periodicidad'] ?? 'Anual', $anio),
                'estado_actividad' => 'ABIERTA',
                'porcentaje_avance' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('tbl_pta_cliente')->insert($datos);
            $creadas++;
        }

        return [
            'success' => true,
            'creadas' => $creadas,
            'existentes' => $existentes,
            'mensaje' => "Se crearon {$creadas} actividades en el Plan de Trabajo Anual."
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 9: FORMATEAR ACTIVIDAD PARA PTA
     * ═══════════════════════════════════════════════════════════════
     *
     * Combina los campos de la actividad en el formato que usa
     * la tabla tbl_pta_cliente.
     */
    protected function formatearActividadPTA(array $actividad): string
    {
        $texto = $actividad['actividad'];

        if (!empty($actividad['descripcion'])) {
            $texto .= "\n\nDescripción: " . $actividad['descripcion'];
        }

        if (!empty($actividad['meta'])) {
            $texto .= "\n\nMeta: " . $actividad['meta'];
        }

        return $texto;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 10: CALCULAR FECHA PROPUESTA
     * ═══════════════════════════════════════════════════════════════
     *
     * Determina la fecha propuesta según la periodicidad.
     */
    protected function calcularFechaPropuesta(string $periodicidad, int $anio): string
    {
        return match(strtolower($periodicidad)) {
            'mensual' => "{$anio}-01-31",
            'bimestral' => "{$anio}-02-28",
            'trimestral' => "{$anio}-03-31",
            'semestral' => "{$anio}-06-30",
            'anual' => "{$anio}-12-31",
            default => "{$anio}-12-31"
        };
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 11: OBTENER ACTIVIDADES EXISTENTES
     * ═══════════════════════════════════════════════════════════════
     *
     * Consulta las actividades ya guardadas en el PTA para este
     * documento y cliente.
     */
    public function getActividadesExistentes(int $idCliente, ?int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        return $this->db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', $this->tipoServicio)
            ->where('numeral_plandetrabajo', $this->numeralEstandar)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->orderBy('phva_plandetrabajo', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 12: ELIMINAR ACTIVIDAD
     * ═══════════════════════════════════════════════════════════════
     */
    public function eliminarActividad(int $idCliente, int $idPta): bool
    {
        return $this->db->table('tbl_pta_cliente')
            ->where('id_ptacliente', $idPta)
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', $this->tipoServicio)
            ->delete();
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 13: ELIMINAR TODAS LAS ACTIVIDADES
     * ═══════════════════════════════════════════════════════════════
     */
    public function eliminarTodasActividades(int $idCliente): int
    {
        $this->db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', $this->tipoServicio)
            ->where('numeral_plandetrabajo', $this->numeralEstandar)
            ->delete();

        return $this->db->affectedRows();
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO 14: REGENERAR ACTIVIDAD INDIVIDUAL
     * ═══════════════════════════════════════════════════════════════
     *
     * Permite mejorar una actividad específica con IA.
     */
    public function regenerarActividad(array $actividadActual, array $contexto, string $instrucciones): array
    {
        $prompt = "CONTEXTO DE LA EMPRESA:
- Actividad económica: {$contexto['actividad_economica']}
- Nivel de riesgo: {$contexto['nivel_riesgo']}
- Estándares: {$contexto['estandares']}

ACTIVIDAD ACTUAL:
" . json_encode($actividadActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

INSTRUCCIONES DE MEJORA:
{$instrucciones}

Mejora esta actividad según las instrucciones. Responde SOLO con el JSON de la actividad mejorada.";

        try {
            $respuesta = $this->openaiService->chat([
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 800,
                'messages' => [
                    ['role' => 'system', 'content' => $this->getSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            return json_decode($respuesta['choices'][0]['message']['content'], true) ?? $actividadActual;

        } catch (\Exception $e) {
            log_message('error', 'Error regenerando actividad: ' . $e->getMessage());
            return $actividadActual;
        }
    }
}
```

---

## Paso 2: Agregar Métodos al Controlador

### Archivo a Modificar
```
app/Controllers/GeneradorIAController.php
```

### Métodos a Agregar

```php
/**
 * ═══════════════════════════════════════════════════════════════════
 * MÓDULO: [NOMBRE DEL DOCUMENTO]
 * Estándar: X.X.X de la Resolución 0312/2019
 * ═══════════════════════════════════════════════════════════════════
 */

/**
 * Vista principal del generador de actividades
 * GET /generador-ia/{idCliente}/[nombre-documento]
 */
public function [nombreDocumento](int $idCliente)
{
    // Verificar permisos
    $cliente = $this->verificarAccesoCliente($idCliente);
    if (!$cliente) {
        return redirect()->back()->with('error', 'Cliente no encontrado');
    }

    // Cargar servicio
    $service = new \App\Services\[NombreDocumento]Service();

    // Obtener contexto y actividades existentes
    $contexto = $service->getContextoCliente($idCliente);
    $actividadesExistentes = $service->getActividadesExistentes($idCliente);
    $limite = $service->getLimiteActividades($contexto['estandares']);

    return view('generador_ia/[nombre_documento]', [
        'cliente' => $cliente,
        'contexto' => $contexto,
        'actividadesExistentes' => $actividadesExistentes,
        'limite' => $limite,
        'estandares' => $contexto['estandares'],
        'titulo' => '[Nombre del Documento] - Generador de Actividades'
    ]);
}

/**
 * Preview de actividades (AJAX)
 * GET /generador-ia/{idCliente}/preview-[nombre-documento]
 */
public function preview[NombreDocumento](int $idCliente)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403);
    }

    $service = new \App\Services\[NombreDocumento]Service();
    $instrucciones = $this->request->getGet('instrucciones');

    $resultado = $service->previewActividades($idCliente, $instrucciones);

    return $this->response->setJSON($resultado);
}

/**
 * Generar y guardar actividades (AJAX)
 * POST /generador-ia/{idCliente}/generar-[nombre-documento]
 */
public function generar[NombreDocumento](int $idCliente)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403);
    }

    $actividades = $this->request->getJSON(true)['actividades'] ?? [];

    if (empty($actividades)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se recibieron actividades para guardar'
        ]);
    }

    $service = new \App\Services\[NombreDocumento]Service();
    $resultado = $service->generarActividades($idCliente, $actividades);

    return $this->response->setJSON($resultado);
}

/**
 * Eliminar una actividad (AJAX)
 * DELETE /generador-ia/{idCliente}/eliminar-actividad-[nombre]/(:num)
 */
public function eliminarActividad[NombreDocumento](int $idCliente, int $idPta)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403);
    }

    $service = new \App\Services\[NombreDocumento]Service();
    $eliminado = $service->eliminarActividad($idCliente, $idPta);

    return $this->response->setJSON([
        'success' => $eliminado,
        'message' => $eliminado ? 'Actividad eliminada' : 'No se pudo eliminar'
    ]);
}

/**
 * Eliminar todas las actividades (AJAX)
 * DELETE /generador-ia/{idCliente}/eliminar-todas-[nombre]
 */
public function eliminarTodas[NombreDocumento](int $idCliente)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403);
    }

    $service = new \App\Services\[NombreDocumento]Service();
    $cantidad = $service->eliminarTodasActividades($idCliente);

    return $this->response->setJSON([
        'success' => true,
        'eliminadas' => $cantidad,
        'message' => "Se eliminaron {$cantidad} actividades"
    ]);
}

/**
 * Regenerar actividad individual con IA (AJAX)
 * POST /generador-ia/{idCliente}/regenerar-actividad-[nombre]
 */
public function regenerarActividad[NombreDocumento](int $idCliente)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403);
    }

    $datos = $this->request->getJSON(true);
    $actividadActual = $datos['actividad'] ?? [];
    $instrucciones = $datos['instrucciones'] ?? '';

    $service = new \App\Services\[NombreDocumento]Service();
    $contexto = $service->getContextoCliente($idCliente);

    $actividadMejorada = $service->regenerarActividad(
        $actividadActual,
        $contexto,
        $instrucciones
    );

    return $this->response->setJSON([
        'success' => true,
        'actividad' => $actividadMejorada
    ]);
}
```

---

## Paso 3: Configurar Rutas

### Archivo a Modificar
```
app/Config/Routes.php
```

### Rutas a Agregar

```php
// ═══════════════════════════════════════════════════════════════════
// GENERADOR IA - MÓDULO [X.X.X] - [NOMBRE DEL DOCUMENTO]
// ═══════════════════════════════════════════════════════════════════

// Vista principal
$routes->get(
    'generador-ia/(:num)/[nombre-documento]',
    'GeneradorIAController::[nombreDocumento]/$1'
);

// Preview de actividades
$routes->get(
    'generador-ia/(:num)/preview-[nombre-documento]',
    'GeneradorIAController::preview[NombreDocumento]/$1'
);

// Generar y guardar actividades
$routes->post(
    'generador-ia/(:num)/generar-[nombre-documento]',
    'GeneradorIAController::generar[NombreDocumento]/$1'
);

// Eliminar una actividad
$routes->delete(
    'generador-ia/(:num)/eliminar-actividad-[nombre]/(:num)',
    'GeneradorIAController::eliminarActividad[NombreDocumento]/$1/$2'
);

// Eliminar todas las actividades
$routes->delete(
    'generador-ia/(:num)/eliminar-todas-[nombre]',
    'GeneradorIAController::eliminarTodas[NombreDocumento]/$1'
);

// Regenerar actividad individual
$routes->post(
    'generador-ia/(:num)/regenerar-actividad-[nombre]',
    'GeneradorIAController::regenerarActividad[NombreDocumento]/$1'
);
```

---

## Paso 4: Crear la Vista

### Archivo a Crear
```
app/Views/generador_ia/[nombre_documento].php
```

### Estructura de la Vista

La vista tiene dos partes principales:
1. **Vista principal** con contexto del cliente y botones de acción
2. **Modal de Preview** donde el usuario selecciona, edita y envía actividades

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- ═══════════════════════════════════════════════════════════════
         NAVBAR
         ═══════════════════════════════════════════════════════════════ -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-bullseye me-2"></i>Generador IA - [Nombre del Documento]
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- ═══════════════════════════════════════════════════════════════
             HEADER CON INFO DEL ESTÁNDAR
             ═══════════════════════════════════════════════════════════════ -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">[Nombre del Documento]</h4>
                <p class="text-muted mb-0">Estándar X.X.X - Resolución 0312/2019</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info fs-6"><?= $anio ?></span>
                <span class="badge bg-secondary fs-6"><?= $estandares ?> estándares</span>
            </div>
        </div>

        <!-- Alertas dinámicas -->
        <div id="alertContainer"></div>

        <!-- ═══════════════════════════════════════════════════════════════
             CARD: CONTEXTO DEL CLIENTE PARA LA IA
             ═══════════════════════════════════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Contexto para la IA
                </h6>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="collapseContexto">
                <div class="card-body">
                    <div class="row">
                        <!-- Datos del Cliente -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-building me-1"></i>Datos de la Empresa
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width:40%">Actividad económica:</td>
                                    <td><strong><?= esc($contexto['actividad_economica']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nivel de riesgo ARL:</td>
                                    <td>
                                        <?php
                                        $riesgo = $contexto['nivel_riesgo'] ?? 'N/A';
                                        $colorRiesgo = match($riesgo) {
                                            'I', 'II' => 'success',
                                            'III' => 'warning',
                                            'IV', 'V' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $colorRiesgo ?>"><?= $riesgo ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total trabajadores:</td>
                                    <td><strong><?= $contexto['numero_trabajadores'] ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estándares aplicables:</td>
                                    <td><span class="badge bg-info"><?= $estandares ?> estándares</span></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Límites según estándares -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-sliders me-1"></i>Límites según Estándares
                            </h6>
                            <div class="alert alert-light border small">
                                <div class="row">
                                    <div class="col-12">
                                        <strong class="text-success"><?= $limite ?></strong> actividades máximo
                                    </div>
                                </div>
                                <hr class="my-2">
                                <small class="text-muted">
                                    Según Res. 0312/2019: 7 est. = 3 act, 21 est. = 5 act, 60 est. = 8 act
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- ═══════════════════════════════════════════════════════════════
                         INSTRUCCIONES ADICIONALES PARA LA IA
                         ═══════════════════════════════════════════════════════════════ -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-chat-dots me-1"></i>Instrucciones adicionales para la IA
                                <small class="text-muted">(opcional)</small>
                            </label>
                            <textarea id="instruccionesIA" class="form-control" rows="3"
                                placeholder="Ej: Enfocar actividades en prevención de riesgo específico, incluir actividades trimestrales, la empresa tiene certificación ISO..."></textarea>
                            <small class="text-muted">
                                Describa necesidades específicas para personalizar las actividades.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             CARD: GENERADOR DE ACTIVIDADES
             ═══════════════════════════════════════════════════════════════ -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <span class="badge bg-white text-success me-2">1</span>
                            Actividades para el Plan de Trabajo
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual (<?= $anio ?>):</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Actividades definidas:</span>
                                <strong><?= count($actividadesExistentes) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Límite según estándares:</span>
                                <strong><?= $limite ?></strong>
                            </div>
                            <?php if (count($actividadesExistentes) >= 3): ?>
                                <div class="alert alert-success small mb-0 mt-2">
                                    <i class="bi bi-check-circle me-1"></i>Fase completa
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning small mb-0 mt-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Mínimo 3 actividades requeridas
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actividades típicas -->
                        <div class="alert alert-light small mb-3">
                            <strong>Actividades típicas:</strong>
                            <ul class="mb-0 mt-1">
                                <li>[Actividad típica 1]</li>
                                <li>[Actividad típica 2]</li>
                                <li>[Actividad típica 3]</li>
                                <li>[Actividad típica 4]</li>
                            </ul>
                        </div>

                        <!-- ═══════════════════════════════════════════════════════════════
                             BOTONES DE ACCIÓN
                             ═══════════════════════════════════════════════════════════════ -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-success" onclick="previewActividades()">
                                <i class="bi bi-eye me-1"></i>Ver Preview
                            </button>
                            <button type="button" class="btn btn-success" onclick="previewActividades()">
                                <i class="bi bi-magic me-1"></i>Generar Actividades
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             ACTIVIDADES EXISTENTES (si las hay)
             ═══════════════════════════════════════════════════════════════ -->
        <?php if (!empty($actividadesExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Actividades Definidas (<?= count($actividadesExistentes) ?>)
                </h5>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarTodas()">
                    <i class="bi bi-trash me-1"></i>Eliminar Todas
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Actividad</th>
                                <th>Responsable</th>
                                <th>PHVA</th>
                                <th>Estado</th>
                                <th style="width:80px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividadesExistentes as $act): ?>
                            <tr>
                                <td>
                                    <strong><?= esc(substr($act['actividad_plandetrabajo'], 0, 60)) ?></strong>
                                </td>
                                <td><?= esc($act['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST') ?></td>
                                <td><span class="badge bg-secondary"><?= $act['phva_plandetrabajo'] ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $act['estado_actividad'] == 'CERRADA' ? 'success' : 'warning' ?>">
                                        <?= esc($act['estado_actividad']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="eliminarActividad(<?= $act['id_ptacliente'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════
             MODAL: PREVIEW CON SELECCIÓN DE ACTIVIDADES

             Este es el componente más importante. Permite:
             1. Ver todas las actividades generadas
             2. Seleccionar cuáles enviar (con checkbox)
             3. Editar cada actividad inline
             4. Mejorar cada actividad con IA
             5. Enviar solo las seleccionadas a la BD
             ═══════════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalPreview" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-eye me-2"></i>Seleccionar Actividades
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-success"></div>
                            <p class="mt-2">Cargando actividades...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <!-- Contador de selección -->
                            <span id="contadorSeleccion" class="text-muted">0 actividades seleccionadas</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btnEnviarSeleccionados"
                                    onclick="enviarActividadesSeleccionadas()">
                                <i class="bi bi-send me-1"></i>Enviar al Plan de Trabajo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="toastNotification" class="toast" role="alert">
            <div class="toast-header">
                <i class="bi me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitle">Notificación</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ═══════════════════════════════════════════════════════════════════════
    // VARIABLES GLOBALES
    // ═══════════════════════════════════════════════════════════════════════
    const idCliente = <?= $cliente['id_cliente'] ?>;
    const anio = <?= $anio ?>;
    const limiteActividades = <?= $limite ?>;
    let actividadesData = [];

    // ═══════════════════════════════════════════════════════════════════════
    // FUNCIONES DE UTILIDAD
    // ═══════════════════════════════════════════════════════════════════════
    function showToast(type, title, message) {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');

        toast.className = 'toast';
        if (type === 'success') {
            toastIcon.className = 'bi bi-check-circle-fill text-success me-2';
            toast.classList.add('border-success');
        } else if (type === 'error') {
            toastIcon.className = 'bi bi-x-circle-fill text-danger me-2';
            toast.classList.add('border-danger');
        } else {
            toastIcon.className = 'bi bi-info-circle-fill text-primary me-2';
            toast.classList.add('border-primary');
        }

        toastTitle.textContent = title;
        toastBody.innerHTML = message;

        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getInstruccionesIA() {
        const textarea = document.getElementById('instruccionesIA');
        return textarea ? textarea.value.trim() : '';
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PREVIEW DE ACTIVIDADES
    // Abre el modal y carga las actividades desde el servidor
    // ═══════════════════════════════════════════════════════════════════════
    function previewActividades() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
        modal.show();

        const instrucciones = encodeURIComponent(getInstruccionesIA());
        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-[nombre-documento]?anio=${anio}&instrucciones=${instrucciones}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    actividadesData = data.data.actividades;
                    renderizarPreviewConCheckbox();
                } else {
                    document.getElementById('previewContent').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('previewContent').innerHTML =
                    `<div class="alert alert-danger">Error de conexión</div>`;
            });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // RENDERIZAR PREVIEW CON CHECKBOX
    // Genera el HTML de las actividades con:
    // - Checkbox para seleccionar
    // - Campos editables inline
    // - Panel colapsable "Mejorar con IA"
    // ═══════════════════════════════════════════════════════════════════════
    function renderizarPreviewConCheckbox() {
        let html = `
            <!-- Controles de selección masiva -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${actividadesData.length} actividades sugeridas</strong>
                    <small class="text-muted ms-2">(límite: ${limiteActividades})</small>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="seleccionarTodos(true)">
                        <i class="bi bi-check-all me-1"></i>Seleccionar Todos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodos(false)">
                        <i class="bi bi-x-lg me-1"></i>Deseleccionar
                    </button>
                </div>
            </div>

            <div class="alert alert-light small mb-3 border">
                <i class="bi bi-info-circle me-1"></i>
                Seleccione las actividades que desea incluir. Puede editar cada una antes de enviar.
            </div>`;

        // Generar card para cada actividad
        actividadesData.forEach((act, idx) => {
            const phvaColors = {
                'PLANEAR': 'primary',
                'HACER': 'success',
                'VERIFICAR': 'warning',
                'ACTUAR': 'danger'
            };

            html += `
            <div class="card mb-3 actividad-card border-start border-4 border-${phvaColors[act.phva] || 'secondary'}" data-idx="${idx}">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start">
                        <!-- ═══════════════════════════════════════════════════
                             CHECKBOX PARA SELECCIONAR
                             ═══════════════════════════════════════════════════ -->
                        <div class="form-check me-3 pt-1">
                            <input type="checkbox" class="form-check-input actividad-check"
                                   data-idx="${idx}" checked onchange="actualizarContador()">
                        </div>

                        <div class="flex-grow-1">
                            <!-- Header con título y badge PHVA -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1 me-2">
                                    <input type="text" class="form-control form-control-sm fw-bold actividad-titulo"
                                           data-idx="${idx}" value="${escapeHtml(act.actividad)}"
                                           placeholder="Título de la actividad">
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-${phvaColors[act.phva] || 'secondary'}">${act.phva}</span>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-2">
                                <textarea class="form-control form-control-sm actividad-descripcion"
                                          data-idx="${idx}" rows="2"
                                          placeholder="Descripción de la actividad">${escapeHtml(act.descripcion || '')}</textarea>
                            </div>

                            <!-- Meta -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="bi bi-flag"></i>
                                        </span>
                                        <input type="text" class="form-control actividad-meta" data-idx="${idx}"
                                               value="${escapeHtml(act.meta || '')}" placeholder="Meta cuantificable">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" class="form-control actividad-responsable" data-idx="${idx}"
                                               value="${escapeHtml(act.responsable || 'Responsable SST')}"
                                               placeholder="Responsable">
                                    </div>
                                </div>
                            </div>

                            <!-- PHVA y Periodicidad -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <select class="form-select form-select-sm actividad-phva" data-idx="${idx}">
                                        <option value="PLANEAR" ${act.phva === 'PLANEAR' ? 'selected' : ''}>PLANEAR</option>
                                        <option value="HACER" ${act.phva === 'HACER' ? 'selected' : ''}>HACER</option>
                                        <option value="VERIFICAR" ${act.phva === 'VERIFICAR' ? 'selected' : ''}>VERIFICAR</option>
                                        <option value="ACTUAR" ${act.phva === 'ACTUAR' ? 'selected' : ''}>ACTUAR</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select form-select-sm actividad-periodicidad" data-idx="${idx}">
                                        <option value="Mensual" ${act.periodicidad === 'Mensual' ? 'selected' : ''}>Mensual</option>
                                        <option value="Bimestral" ${act.periodicidad === 'Bimestral' ? 'selected' : ''}>Bimestral</option>
                                        <option value="Trimestral" ${act.periodicidad === 'Trimestral' ? 'selected' : ''}>Trimestral</option>
                                        <option value="Semestral" ${act.periodicidad === 'Semestral' ? 'selected' : ''}>Semestral</option>
                                        <option value="Anual" ${act.periodicidad === 'Anual' ? 'selected' : ''}>Anual</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ═══════════════════════════════════════════════════
                                 PANEL COLAPSABLE: MEJORAR CON IA
                                 ═══════════════════════════════════════════════════ -->
                            <div class="border-top pt-2 mt-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                            onclick="toggleIAPanel(${idx})">
                                        <i class="bi bi-robot me-1"></i>
                                        <small>Mejorar con IA</small>
                                        <i class="bi bi-chevron-down ms-1" id="iaChevron${idx}"></i>
                                    </button>
                                </div>
                                <div class="collapse mt-2" id="iaPanelActividad${idx}">
                                    <div class="card card-body bg-light border-0 p-2">
                                        <div class="mb-2">
                                            <textarea class="form-control form-control-sm instrucciones-ia-actividad"
                                                      data-idx="${idx}" rows="2"
                                                      placeholder="Ej: Hazlo más específico, enfoca en riesgo X, agrega meta trimestral..."></textarea>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-purple w-100"
                                                style="border-color:#9c27b0; color:#9c27b0;"
                                                onclick="regenerarActividadConIA(${idx})">
                                            <i class="bi bi-magic me-1"></i>Regenerar esta actividad
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        document.getElementById('previewContent').innerHTML = html;
        actualizarContador();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TOGGLE PANEL IA
    // Muestra/oculta el panel de instrucciones IA para cada actividad
    // ═══════════════════════════════════════════════════════════════════════
    function toggleIAPanel(idx) {
        const panel = document.getElementById(`iaPanelActividad${idx}`);
        const chevron = document.getElementById(`iaChevron${idx}`);

        if (panel.classList.contains('show')) {
            panel.classList.remove('show');
            chevron.classList.remove('bi-chevron-up');
            chevron.classList.add('bi-chevron-down');
        } else {
            panel.classList.add('show');
            chevron.classList.remove('bi-chevron-down');
            chevron.classList.add('bi-chevron-up');
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SELECCIONAR TODOS / DESELECCIONAR
    // ═══════════════════════════════════════════════════════════════════════
    function seleccionarTodos(seleccionar) {
        document.querySelectorAll('.actividad-check').forEach(cb => {
            cb.checked = seleccionar;
        });
        actualizarContador();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ACTUALIZAR CONTADOR DE SELECCIÓN
    // También valida si excede el límite permitido
    // ═══════════════════════════════════════════════════════════════════════
    function actualizarContador() {
        const total = document.querySelectorAll('.actividad-check:checked').length;
        document.getElementById('contadorSeleccion').textContent = `${total} actividades seleccionadas`;

        const btnEnviar = document.getElementById('btnEnviarSeleccionados');
        if (total === 0) {
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<i class="bi bi-send me-1"></i>Seleccione actividades';
        } else if (total > limiteActividades) {
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Excede límite (${limiteActividades})`;
        } else {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${total} al Plan de Trabajo`;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // OBTENER DATOS DE UNA ACTIVIDAD (desde los campos editados)
    // ═══════════════════════════════════════════════════════════════════════
    function getActividadData(idx) {
        return {
            actividad: document.querySelector(`.actividad-titulo[data-idx="${idx}"]`).value,
            descripcion: document.querySelector(`.actividad-descripcion[data-idx="${idx}"]`).value,
            meta: document.querySelector(`.actividad-meta[data-idx="${idx}"]`).value,
            responsable: document.querySelector(`.actividad-responsable[data-idx="${idx}"]`).value,
            phva: document.querySelector(`.actividad-phva[data-idx="${idx}"]`).value,
            periodicidad: document.querySelector(`.actividad-periodicidad[data-idx="${idx}"]`).value
        };
    }

    // ═══════════════════════════════════════════════════════════════════════
    // REGENERAR ACTIVIDAD CON IA
    // Envía instrucciones al servidor para mejorar una actividad específica
    // ═══════════════════════════════════════════════════════════════════════
    function regenerarActividadConIA(idx) {
        const instrucciones = document.querySelector(`.instrucciones-ia-actividad[data-idx="${idx}"]`).value;
        const actividadActual = getActividadData(idx);

        if (!instrucciones.trim()) {
            showToast('info', 'Instrucciones', 'Escriba instrucciones para que la IA mejore esta actividad');
            return;
        }

        const btn = event.target;
        const btnOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/regenerar-actividad-[nombre]`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                actividad: actividadActual,
                instrucciones: instrucciones,
                contexto_general: getInstruccionesIA()
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;

            if (data.success && data.data) {
                // Actualizar los campos con la respuesta de IA
                const nuevo = data.data;
                document.querySelector(`.actividad-titulo[data-idx="${idx}"]`).value = nuevo.actividad || actividadActual.actividad;
                document.querySelector(`.actividad-descripcion[data-idx="${idx}"]`).value = nuevo.descripcion || actividadActual.descripcion;
                document.querySelector(`.actividad-meta[data-idx="${idx}"]`).value = nuevo.meta || actividadActual.meta;
                document.querySelector(`.actividad-responsable[data-idx="${idx}"]`).value = nuevo.responsable || actividadActual.responsable;

                // Feedback visual
                const card = document.querySelector(`.actividad-card[data-idx="${idx}"]`);
                card.classList.add('border-success');
                setTimeout(() => card.classList.remove('border-success'), 2000);

                showToast('success', 'Actividad mejorada', 'La IA ha actualizado la actividad');
            } else {
                showToast('error', 'Error', data.message || 'No se pudo regenerar la actividad');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;
            showToast('error', 'Error de conexión', 'No se pudo conectar con el servidor');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ENVIAR ACTIVIDADES SELECCIONADAS AL PLAN DE TRABAJO
    // Solo envía las que tienen checkbox marcado
    // ═══════════════════════════════════════════════════════════════════════
    function enviarActividadesSeleccionadas() {
        const seleccionadas = [];

        // Obtener los valores EDITADOS de los campos, no los datos originales
        document.querySelectorAll('.actividad-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            seleccionadas.push(getActividadData(idx));
        });

        if (seleccionadas.length === 0) {
            showToast('warning', 'Sin selección', 'Seleccione al menos una actividad');
            return;
        }

        if (seleccionadas.length > limiteActividades) {
            showToast('warning', 'Límite excedido', `Máximo ${limiteActividades} actividades permitidas según estándares`);
            return;
        }

        if (!confirm(`¿Enviar ${seleccionadas.length} actividades al Plan de Trabajo?`)) return;

        const btn = document.getElementById('btnEnviarSeleccionados');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-[nombre-documento]`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                anio: anio,
                actividades: seleccionadas,
                instrucciones: getInstruccionesIA()
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

                const detalles = `<strong>${data.data?.creadas || 0}</strong> actividades enviadas al PTA<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existían`;
                showToast('success', 'Actividades Enviadas', detalles);

                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${seleccionadas.length} al Plan de Trabajo`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexión', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${seleccionadas.length} al Plan de Trabajo`;
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ELIMINAR ACTIVIDAD INDIVIDUAL
    // ═══════════════════════════════════════════════════════════════════════
    function eliminarActividad(idPta) {
        if (!confirm('¿Eliminar esta actividad?')) return;

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/eliminar-actividad-[nombre]/${idPta}`, {
            method: 'DELETE'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Eliminada', 'Actividad eliminada correctamente');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', 'Error', data.message);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ELIMINAR TODAS LAS ACTIVIDADES
    // ═══════════════════════════════════════════════════════════════════════
    function confirmarEliminarTodas() {
        if (!confirm('¿Eliminar TODAS las actividades? Esta acción no se puede deshacer.')) return;

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/eliminar-todas-[nombre]?anio=${anio}`, {
            method: 'DELETE'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Eliminadas', `${data.data?.eliminadas || 0} actividades eliminadas`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', 'Error', data.message);
            }
        });
    }
    </script>
</body>
</html>
```

---

## Tabla de Base de Datos

### Tabla Destino: `tbl_pta_cliente`

Las actividades generadas se guardan en esta tabla con los siguientes campos:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_ptacliente` | INT | Primary key |
| `id_cliente` | INT | FK al cliente |
| `tipo_servicio` | VARCHAR | Identificador del documento (ej: "Objetivos SG-SST") |
| `numeral_plandetrabajo` | VARCHAR | Numeral del estándar (ej: "2.2.1") |
| `phva_plandetrabajo` | ENUM | PLANEAR, HACER, VERIFICAR, ACTUAR |
| `actividad_plandetrabajo` | TEXT | Actividad + Descripción + Meta |
| `responsable_sugerido_plandetrabajo` | VARCHAR | Quién ejecuta |
| `fecha_propuesta` | DATE | Fecha límite |
| `estado_actividad` | ENUM | ABIERTA, CERRADA |
| `porcentaje_avance` | INT | 0-100 |

---

## Reglas de Cantidad de Actividades

| Estándares Aplicables | Cantidad de Actividades | Tipo de Empresa |
|----------------------|------------------------|-----------------|
| 7 estándares | 3 actividades | Pequeña (hasta 10 trabajadores, riesgo I-III) |
| 21 estándares | 5 actividades | Mediana (11-50 trabajadores, riesgo I-III) |
| 60 estándares | 8 actividades | Grande (50+ trabajadores o riesgo IV-V) |

---

## Checklist de Implementación

### Archivos a Crear
- [ ] `app/Services/[NombreDocumento]Service.php`
- [ ] `app/Views/generador_ia/[nombre_documento].php`

### Archivos a Modificar
- [ ] `app/Controllers/GeneradorIAController.php` (agregar 6 métodos)
- [ ] `app/Config/Routes.php` (agregar 6 rutas)

### Verificaciones
- [ ] El servicio tiene las actividades base definidas (mínimo 8)
- [ ] El numeral del estándar está correctamente configurado
- [ ] El tipo_servicio es único para este documento
- [ ] La vista muestra contexto del cliente
- [ ] El preview permite editar antes de guardar
- [ ] Las actividades se guardan correctamente en tbl_pta_cliente

---

## Flujo de Usuario

```
1. Usuario accede a /generador-ia/{idCliente}/[nombre-documento]
                    │
                    ▼
2. Ve contexto del cliente (estándares, actividad, riesgo)
                    │
                    ▼
3. Opcionalmente ingresa instrucciones adicionales
                    │
                    ▼
4. Click en "Ver Preview"
                    │
                    ▼
5. IA genera 3/5/8 actividades según estándares
                    │
                    ▼
6. Usuario revisa, edita, regenera individualmente
                    │
                    ▼
7. Click en "Enviar al Plan de Trabajo"
                    │
                    ▼
8. Actividades guardadas en tbl_pta_cliente
                    │
                    ▼
9. Listas para ser consumidas por el documento formal
```

---

## Notas Importantes

1. **Cada documento es independiente**: No mezclar actividades de diferentes documentos. Cada uno tiene su propio `tipo_servicio` y `numeral_plandetrabajo`.

2. **Las actividades deben ser específicas**: Orientadas 100% al tema del documento, no genéricas.

3. **Respetar los límites**: La cantidad de actividades debe ajustarse estrictamente a los estándares del cliente.

4. **La IA personaliza, no inventa**: Las actividades base son el punto de partida, la IA las adapta al contexto.

5. **El usuario tiene control**: Puede editar, regenerar o eliminar cualquier actividad antes de guardar.
