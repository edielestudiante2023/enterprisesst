<?php

namespace App\Services;

/**
 * Servicio de generación de contenido para documentos SST usando OpenAI
 *
 * Utiliza GPT-4o-mini para balance entre costo y calidad
 */
class IADocumentacionService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected float $temperature = 0.3; // Bajo para consistencia y formalidad

    public function __construct()
    {
        // Leer configuración desde .env
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');

        if (empty($this->apiKey)) {
            log_message('error', 'OPENAI_API_KEY no está configurada en .env');
        }
    }

    /**
     * Genera contenido para una sección de documento
     */
    public function generarSeccion(array $datos): array
    {
        $prompt = $this->construirPrompt($datos);

        $response = $this->llamarAPI($prompt);

        if ($response['success']) {
            return [
                'success' => true,
                'contenido' => $response['contenido'],
                'prompt_usado' => $prompt,
                'tokens_usados' => $response['tokens'] ?? 0
            ];
        }

        return [
            'success' => false,
            'error' => $response['error'] ?? 'Error desconocido',
            'prompt_usado' => $prompt
        ];
    }

    /**
     * Construye el prompt completo para la IA
     */
    protected function construirPrompt(array $datos): string
    {
        $seccion = $datos['seccion'] ?? [];
        $documento = $datos['documento'] ?? [];
        $cliente = $datos['cliente'] ?? [];
        $contexto = $datos['contexto'] ?? [];
        $promptBase = $datos['prompt_base'] ?? '';
        $contextoAdicional = $datos['contexto_adicional'] ?? '';

        // Información de la empresa
        $razonSocial = $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'La empresa';
        $nit = $cliente['nit'] ?? '';
        $direccion = $cliente['direccion'] ?? '';
        $ciudad = $cliente['ciudad'] ?? '';

        // Información del contexto SST
        $actividadEconomica = $contexto['actividad_economica'] ?? '';
        $codigoCIIU = $contexto['codigo_ciiu'] ?? '';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? '';
        $totalTrabajadores = $contexto['total_trabajadores'] ?? '';
        $responsableSst = $contexto['responsable_sst'] ?? '';
        $licenciaSst = $contexto['licencia_sst'] ?? '';

        // Peligros identificados
        $peligros = '';
        if (!empty($contexto['peligros_identificados'])) {
            $peligrosArray = is_array($contexto['peligros_identificados'])
                ? $contexto['peligros_identificados']
                : json_decode($contexto['peligros_identificados'], true);
            if (is_array($peligrosArray)) {
                $peligros = implode(', ', $peligrosArray);
            }
        }

        // Observaciones y contexto cualitativo
        $observacionesContexto = $contexto['observaciones_contexto'] ?? '';

        // Construir prompt del sistema
        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) en Colombia.
Generas documentación técnica siguiendo las normas colombianas:
- Decreto 1072 de 2015 (Decreto Único del Sector Trabajo)
- Resolución 0312 de 2019 (Estándares Mínimos del SG-SST)
- ISO 45001:2018

Reglas de redacción:
1. Usa lenguaje técnico pero claro y profesional
2. Siempre menciona el nombre real de la empresa, nunca uses 'la empresa'
3. Sé específico usando los datos proporcionados
4. Estructura el contenido con párrafos claros
5. Para listas usa viñetas o numeración
6. Para tablas usa formato Markdown
7. No incluyas encabezados (el sistema los agrega automáticamente)
8. Responde SOLO con el contenido de la sección, sin explicaciones adicionales";

        // Construir prompt del usuario
        $userPrompt = "CONTEXTO DEL CLIENTE:\n";
        $userPrompt .= "- Empresa: {$razonSocial}\n";
        if ($nit) $userPrompt .= "- NIT: {$nit}\n";
        if ($direccion) $userPrompt .= "- Dirección: {$direccion}, {$ciudad}\n";
        if ($actividadEconomica) $userPrompt .= "- Actividad económica: {$actividadEconomica}";
        if ($codigoCIIU) $userPrompt .= " (CIIU: {$codigoCIIU})";
        $userPrompt .= "\n";
        if ($nivelRiesgo) $userPrompt .= "- Nivel de riesgo ARL: {$nivelRiesgo}\n";
        if ($totalTrabajadores) $userPrompt .= "- Total trabajadores: {$totalTrabajadores}\n";
        if ($responsableSst) $userPrompt .= "- Responsable SG-SST: {$responsableSst}";
        if ($licenciaSst) $userPrompt .= " (Licencia: {$licenciaSst})";
        $userPrompt .= "\n";
        if ($peligros) $userPrompt .= "- Peligros identificados: {$peligros}\n";

        // Agregar observaciones del contexto si existen
        if (!empty($observacionesContexto)) {
            $userPrompt .= "\nOBSERVACIONES Y CONTEXTO REAL DE LA EMPRESA:\n";
            $userPrompt .= $observacionesContexto . "\n";
            $userPrompt .= "(Usa esta información para hacer el documento más relevante y específico)\n";
        }

        $userPrompt .= "\nDOCUMENTO A GENERAR:\n";
        $userPrompt .= "- Tipo: " . ($documento['tipo_nombre'] ?? 'Documento') . "\n";
        $userPrompt .= "- Nombre: " . ($documento['nombre'] ?? '') . "\n";
        $userPrompt .= "- Sección actual: " . ($seccion['numero_seccion'] ?? '') . ". " . ($seccion['nombre_seccion'] ?? '') . "\n";

        if ($contextoAdicional) {
            $userPrompt .= "\nCONTEXTO ADICIONAL DEL USUARIO:\n{$contextoAdicional}\n";
        }

        $userPrompt .= "\nINSTRUCCIÓN:\n";
        if ($promptBase) {
            $userPrompt .= $promptBase;
        } else {
            $userPrompt .= "Genera el contenido para la sección \"{$seccion['nombre_seccion']}\" del documento \"{$documento['nombre']}\".";
            $userPrompt .= " El texto debe ser específico para {$razonSocial}, usando sus datos reales.";
        }

        return json_encode([
            'system' => $systemPrompt,
            'user' => $userPrompt
        ]);
    }

    /**
     * Llama a la API de OpenAI
     */
    protected function llamarAPI(string $promptJson): array
    {
        $prompts = json_decode($promptJson, true);

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $prompts['system']],
                ['role' => 'user', 'content' => $prompts['user']]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => 2000
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false // Para desarrollo local
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "Error de conexión: {$error}"
            ];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'contenido' => trim($result['choices'][0]['message']['content']),
                'tokens' => $result['usage']['total_tokens'] ?? 0
            ];
        }

        return [
            'success' => false,
            'error' => 'Respuesta inesperada de la API'
        ];
    }

    /**
     * Genera contenido para todas las secciones de un documento
     */
    public function generarDocumentoCompleto(array $documento, array $cliente, array $contexto, array $secciones): array
    {
        $resultados = [];

        foreach ($secciones as $seccion) {
            $datos = [
                'seccion' => $seccion,
                'documento' => $documento,
                'cliente' => $cliente,
                'contexto' => $contexto
            ];

            $resultado = $this->generarSeccion($datos);
            $resultados[$seccion['numero_seccion']] = $resultado;

            // Pausa para evitar rate limiting
            usleep(500000); // 0.5 segundos
        }

        return $resultados;
    }

    /**
     * Obtiene prompts específicos por tipo de documento y sección
     */
    public function getPromptEspecifico(string $tipoDocumento, int $numeroSeccion, string $nombreSeccion): string
    {
        $prompts = $this->getPromptsBase();

        $tipoKey = strtoupper($tipoDocumento);

        if (isset($prompts[$tipoKey][$numeroSeccion])) {
            return $prompts[$tipoKey][$numeroSeccion];
        }

        // Prompt genérico si no hay específico
        return "Genera el contenido para la sección '{$nombreSeccion}' de forma clara y técnica, siguiendo las normas colombianas de SST.";
    }

    /**
     * Base de prompts específicos por tipo de documento (basado en diseño)
     */
    protected function getPromptsBase(): array
    {
        return [
            'PRG' => [ // Programa (13 secciones)
                1 => "Genera una introducción de 2-3 párrafos para el programa. Debe incluir:
- Justificación de por qué la empresa necesita este programa
- Contexto de la actividad económica y sus riesgos
- Mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019)
- Compromiso de la alta dirección
Longitud: 150-250 palabras",

                2 => "Genera los objetivos del programa.
Estructura:
OBJETIVO GENERAL:
- Un objetivo medible y alcanzable relacionado con el programa

OBJETIVOS ESPECÍFICOS:
- 3-5 objetivos que contribuyan al objetivo general
- Deben ser SMART (Específicos, Medibles, Alcanzables, Relevantes, Temporales)
- Relacionados con los peligros identificados de la empresa",

                3 => "Define el alcance del programa. Debe especificar:
- A quién aplica (trabajadores directos, contratistas, visitantes)
- Áreas o procesos cubiertos
- Sedes incluidas
- Exclusiones si las hay
Formato: Lista con viñetas, máximo 10 ítems",

                4 => "Lista el marco normativo aplicable al programa. Incluir:
- Decreto 1072 de 2015 (artículos específicos)
- Resolución 0312 de 2019 (estándares relacionados)
- Normas específicas según el tipo de programa
- Normas sectoriales según actividad económica
Formato: Tabla con columnas [Norma | Descripción | Aplicación]",

                5 => "Genera un glosario de términos técnicos para el programa. Incluir:
- Términos técnicos del programa (mínimo 8, máximo 15)
- Definiciones basadas en normativa colombiana
- Términos específicos de la actividad económica si aplica
Formato: Lista alfabética [Término: Definición]",

                6 => "Genera la estructura de diagnóstico inicial para el programa. Considerando los peligros identificados y el contexto de la empresa. Estructura:
1. Estado actual (qué se tiene)
2. Brechas identificadas (qué falta)
3. Priorización de intervenciones
Nota: Indica '[COMPLETAR CON DATOS REALES]' donde se requiera información específica del diagnóstico.",

                7 => "Genera el listado de actividades para el programa. Las actividades deben:
- Ser específicas y ejecutables
- Tener responsable asignable
- Poder medirse o verificarse
- Estar alineadas con los objetivos del programa
Cantidad: 8-15 actividades
Formato: Tabla [# | Actividad | Responsable | Frecuencia | Entregable]",

                8 => "Genera el cronograma anual para el programa. Basado en las actividades de la sección anterior. Distribuir actividades en los 12 meses del año.
Considerar:
- Actividades de inicio (primeros 3 meses)
- Actividades recurrentes (trimestral, semestral)
- Actividades de cierre (último trimestre)
Formato: Tabla con meses como columnas y actividades como filas. Marcar con 'X' los meses de ejecución.",

                9 => "Define los indicadores de gestión para el programa. Cada indicador debe tener:
- Nombre del indicador
- Fórmula de cálculo
- Meta (valor objetivo)
- Frecuencia de medición
- Responsable de medición
- Fuente de datos
Incluir mínimo:
- 1 indicador de estructura (recursos)
- 1 indicador de proceso (ejecución)
- 1 indicador de resultado (impacto)",

                10 => "Define los roles y responsabilidades para el programa. Roles a incluir:
- Alta dirección / Representante legal
- Responsable del SG-SST
- COPASST / Vigía SST
- Trabajadores
- Otros roles específicos del programa
Formato: Tabla [Rol | Responsabilidades específicas]",

                11 => "Identifica los recursos necesarios para el programa. Categorías:
1. Recursos humanos (personal, competencias)
2. Recursos técnicos (equipos, herramientas)
3. Recursos financieros (presupuesto estimado)
4. Recursos de infraestructura
Sé específico para la actividad económica de la empresa.",

                12 => "Define el mecanismo de seguimiento y evaluación del programa. Incluir:
- Frecuencia de seguimiento (mensual, trimestral)
- Responsable del seguimiento
- Herramienta de seguimiento (formato, sistema)
- Criterios de evaluación
- Acciones ante incumplimientos
- Revisión anual del programa",

                13 => "Lista los registros y formatos asociados al programa. Para cada registro indicar:
- Código del formato (usar prefijo FOR-)
- Nombre del formato
- Responsable de diligenciamiento
- Frecuencia
- Tiempo de retención
Usar códigos estándar: FOR-[TEMA]-[CONSECUTIVO]"
            ],

            'POL' => [ // Política (5 secciones)
                1 => "Genera la declaración de la política. Debe ser un texto formal que exprese el compromiso de la alta dirección con la seguridad y salud en el trabajo. Incluir mención a:
- Prevención de lesiones y enfermedades
- Cumplimiento legal
- Mejora continua
- Participación de trabajadores
Longitud: 1 párrafo de 100-150 palabras",

                2 => "Define los objetivos específicos de la política (4-6 objetivos):
- Proteger la seguridad y salud de los trabajadores
- Cumplir con requisitos legales
- Identificar y controlar peligros
- Promover la mejora continua
- Fomentar la participación",

                3 => "Define el alcance de la política:
- A quién aplica
- Sedes o ubicaciones
- Incluye contratistas, visitantes, etc.",

                4 => "Lista los compromisos específicos de la empresa (8-12 ítems). Cada compromiso debe ser concreto y verificable. Incluir compromisos sobre:
- Recursos
- Capacitación
- Gestión de peligros
- Cumplimiento legal
- Comunicación
- Investigación de incidentes",

                5 => "Define cómo se comunicará y revisará la política:
- Mecanismos de divulgación a trabajadores
- Frecuencia de revisión (anual mínimo)
- Disponibilidad para partes interesadas
- Criterios para actualización"
            ],

            'PRO' => [ // Procedimiento (8 secciones)
                1 => "Define el objetivo del procedimiento en máximo 2 líneas. Debe responder: ¿Para qué sirve este procedimiento?",

                2 => "Define el alcance: ¿A quién aplica? ¿Dónde aplica? ¿Cuándo aplica?",

                3 => "Lista las definiciones y términos clave necesarios para entender el procedimiento (5-10 términos)",

                4 => "Define los roles y responsabilidades de quienes participan en el procedimiento",

                5 => "Describe paso a paso el procedimiento. Usar numeración y ser muy específico. Incluir:
- Qué hacer
- Quién lo hace
- Cuándo
- Registros generados",

                6 => "Lista los documentos relacionados: otros procedimientos, formatos, registros",

                7 => "Incluir tabla de control de cambios con columnas: Versión | Fecha | Descripción del cambio | Aprobado por",

                8 => "Incluir anexos necesarios: diagramas de flujo, tablas de referencia, formatos"
            ],

            'PLA' => [ // Plan (10 secciones)
                1 => "Genera introducción del plan explicando su propósito y contexto",
                2 => "Define objetivo general y objetivos específicos del plan",
                3 => "Define el alcance del plan",
                4 => "Lista el marco normativo aplicable",
                5 => "Describe el diagnóstico o situación actual",
                6 => "Define las metas cuantificables del plan",
                7 => "Lista actividades con cronograma (mes a mes)",
                8 => "Detalla el presupuesto o recursos necesarios",
                9 => "Define los indicadores de seguimiento",
                10 => "Describe el mecanismo de seguimiento y control"
            ]
        ];
    }
}
