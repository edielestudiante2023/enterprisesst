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

        // Información de la empresa (campos de tbl_clientes)
        $razonSocial = $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'La empresa';
        $nit = $cliente['nit_cliente'] ?? $cliente['nit'] ?? '';
        $direccion = $cliente['direccion_cliente'] ?? $cliente['direccion'] ?? '';
        $ciudad = $cliente['ciudad_cliente'] ?? $cliente['ciudad'] ?? '';
        $representanteLegalCliente = $cliente['nombre_rep_legal'] ?? '';

        // Información del contexto SST (campos de tbl_cliente_contexto_sst)
        $actividadEconomica = $contexto['sector_economico'] ?? '';
        $codigoCIIU = $cliente['codigo_actividad_economica'] ?? $contexto['codigo_ciiu_secundario'] ?? '';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? '';
        $nivelesRiesgo = $contexto['niveles_riesgo_arl'] ?? '';
        $totalTrabajadores = $contexto['total_trabajadores'] ?? '';
        $trabajadoresDirectos = $contexto['trabajadores_directos'] ?? '';
        $contratistas = $contexto['contratistas_permanentes'] ?? 0;
        $responsableSst = $contexto['responsable_sgsst_nombre'] ?? '';
        $responsableSstCargo = $contexto['responsable_sgsst_cargo'] ?? '';
        $licenciaSst = $contexto['licencia_sst_numero'] ?? '';
        $licenciaVigencia = $contexto['licencia_sst_vigencia'] ?? '';
        $arl = $contexto['arl_actual'] ?? '';
        $representanteLegal = $contexto['representante_legal_nombre'] ?? $representanteLegalCliente ?? '';
        $representanteLegalCargo = $contexto['representante_legal_cargo'] ?? '';
        $numeroSedes = $contexto['numero_sedes'] ?? 1;
        $turnosTrabajo = $contexto['turnos_trabajo'] ?? '';
        $tieneCopasst = $contexto['tiene_copasst'] ?? 0;
        $tieneVigia = $contexto['tiene_vigia_sst'] ?? 0;
        $tieneBrigada = $contexto['tiene_brigada_emergencias'] ?? 0;
        $tieneComiteConvivencia = $contexto['tiene_comite_convivencia'] ?? 0;
        $estandaresAplicables = $contexto['estandares_aplicables'] ?? 60;

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

        // Turnos de trabajo
        $turnosTexto = '';
        if (!empty($turnosTrabajo)) {
            $turnosArray = is_array($turnosTrabajo)
                ? $turnosTrabajo
                : json_decode($turnosTrabajo, true);
            if (is_array($turnosArray)) {
                $turnosTexto = implode(', ', $turnosArray);
            }
        }

        // Niveles de riesgo (puede ser un array JSON)
        $nivelesRiesgoTexto = '';
        if (!empty($nivelesRiesgo)) {
            $nivelesArray = is_array($nivelesRiesgo)
                ? $nivelesRiesgo
                : json_decode($nivelesRiesgo, true);
            if (is_array($nivelesArray)) {
                $nivelesRiesgoTexto = implode(', ', $nivelesArray);
            }
        }

        // Observaciones y contexto cualitativo
        $observacionesContexto = $contexto['observaciones_contexto'] ?? '';

        // Obtener restricciones específicas según estándares aplicables
        $restricciones = $this->getRestriccionesPorEstandares((int)$estandaresAplicables, $tieneCopasst, $tieneVigia);

        // Construir prompt del sistema
        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) en Colombia.
Generas documentación técnica siguiendo las normas colombianas:
- Decreto 1072 de 2015 (Decreto Único del Sector Trabajo)
- Resolución 0312 de 2019 (Estándares Mínimos del SG-SST)
- ISO 45001:2018

Reglas de redacción:
1. Usa lenguaje técnico pero claro y profesional
2. Siempre menciona el nombre real de la empresa '{$razonSocial}', nunca uses 'la empresa'
3. Sé específico usando los datos proporcionados
4. Estructura el contenido con párrafos claros
5. Para listas usa viñetas (-)  o numeración (1. 2. 3.)
6. PROHIBIDO usar tablas Markdown (no uses el caracter |)
7. No incluyas encabezados (el sistema los agrega automáticamente)
8. Responde SOLO con el contenido de la sección, sin explicaciones adicionales
9. Usa **negritas** para títulos de subsecciones

=== REGLAS CRÍTICAS DE ESCALADO SEGÚN RESOLUCIÓN 0312/2019 ===
Esta empresa tiene {$estandaresAplicables} ESTÁNDARES APLICABLES.

{$restricciones['descripcion']}

LÍMITES OBLIGATORIOS PARA ESTA EMPRESA:
{$restricciones['limites']}

ADVERTENCIAS:
{$restricciones['advertencias']}";

        // Construir prompt del usuario
        $userPrompt = "CONTEXTO DEL CLIENTE:\n";
        $userPrompt .= "- Empresa: {$razonSocial}\n";
        if ($nit) $userPrompt .= "- NIT: {$nit}\n";
        if ($direccion) $userPrompt .= "- Dirección: {$direccion}, {$ciudad}\n";
        if ($actividadEconomica) $userPrompt .= "- Actividad económica: {$actividadEconomica}";
        if ($codigoCIIU) $userPrompt .= " (CIIU: {$codigoCIIU})";
        $userPrompt .= "\n";
        if ($arl) $userPrompt .= "- ARL: {$arl}\n";
        if ($nivelRiesgo) $userPrompt .= "- Nivel de riesgo ARL: {$nivelRiesgo}";
        if ($nivelesRiesgoTexto) $userPrompt .= " (Niveles presentes: {$nivelesRiesgoTexto})";
        $userPrompt .= "\n";

        // Información de personal
        $userPrompt .= "\nINFORMACIÓN DE PERSONAL:\n";
        if ($totalTrabajadores) $userPrompt .= "- Total trabajadores: {$totalTrabajadores}\n";
        if ($trabajadoresDirectos) $userPrompt .= "- Trabajadores directos: {$trabajadoresDirectos}\n";
        if ($contratistas > 0) $userPrompt .= "- Contratistas permanentes: {$contratistas}\n";
        if ($numeroSedes) $userPrompt .= "- Número de sedes: {$numeroSedes}\n";
        if ($turnosTexto) $userPrompt .= "- Turnos de trabajo: {$turnosTexto}\n";

        // Comités y estructuras
        $userPrompt .= "\nESTRUCTURAS SST:\n";
        $userPrompt .= "- " . ($tieneCopasst ? "Tiene COPASST" : ($tieneVigia ? "Tiene Vigía SST (empresa < 10 trabajadores)" : "Sin COPASST/Vigía")) . "\n";
        if ($tieneComiteConvivencia) $userPrompt .= "- Tiene Comité de Convivencia Laboral\n";
        if ($tieneBrigada) $userPrompt .= "- Tiene Brigada de Emergencias\n";
        $userPrompt .= "- Estándares mínimos aplicables: {$estandaresAplicables} (según Res. 0312/2019)\n";

        // Responsables - Cargar desde la nueva tabla de responsables
        $userPrompt .= "\nRESPONSABLES DEL SG-SST:\n";
        $responsablesInfo = $this->cargarResponsables($cliente['id_cliente'] ?? 0, $estandaresAplicables);
        if (!empty($responsablesInfo)) {
            $userPrompt .= $responsablesInfo;
        } else {
            // Fallback a datos del contexto antiguo
            if ($representanteLegal) {
                $userPrompt .= "- Representante Legal: {$representanteLegal}";
                if ($representanteLegalCargo) $userPrompt .= " ({$representanteLegalCargo})";
                $userPrompt .= "\n";
            }
            if ($responsableSst) {
                $userPrompt .= "- Responsable SG-SST: {$responsableSst}";
                if ($responsableSstCargo) $userPrompt .= " ({$responsableSstCargo})";
                if ($licenciaSst) $userPrompt .= " - Licencia: {$licenciaSst}";
                if ($licenciaVigencia) $userPrompt .= " (vigencia: {$licenciaVigencia})";
                $userPrompt .= "\n";
            }
        }

        if ($peligros) $userPrompt .= "\nPELIGROS IDENTIFICADOS: {$peligros}\n";

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
     * Obtiene restricciones específicas según los estándares aplicables
     * Esto es CRÍTICO para generar documentos proporcionales al tamaño de la empresa
     */
    protected function getRestriccionesPorEstandares(int $estandares, bool $tieneCopasst, bool $tieneVigia): array
    {
        if ($estandares <= 7) {
            // Empresas muy pequeñas: menos de 10 trabajadores, riesgo I-III
            return [
                'descripcion' => "EMPRESA MICRO (7 estándares): Menos de 10 trabajadores, Riesgo I, II o III.
Esta es una empresa MUY PEQUEÑA. La documentación debe ser SIMPLIFICADA y BÁSICA.
NO requiere licencia SST para el responsable. Usa Vigía SST (NO COPASST).
NO tiene Comité de Convivencia Laboral (menos de 10 trabajadores).",

                'limites' => "- Temas de capacitación: MÁXIMO 5-6 temas básicos (inducción, riesgo específico, emergencias)
- Indicadores: MÁXIMO 2-3 indicadores simples (cobertura capacitación, accidentalidad, cumplimiento)
- Actividades del programa: MÁXIMO 6-8 actividades esenciales
- Marco normativo: MÁXIMO 5-6 normas principales (no listas extensas)
- Recursos: MÍNIMOS y proporcionales (sin auditorios, sin equipos especiales)
- Roles: Solo 3-4 roles (Representante Legal, Responsable SST, Vigía SST, Trabajadores)
- Cronograma: Actividades trimestrales o semestrales (no mensuales)
- Glosario: MÁXIMO 8 términos esenciales",

                'advertencias' => "- NUNCA menciones COPASST (usa 'Vigía SST')
- NUNCA menciones Comité de Convivencia Laboral
- NUNCA incluyas recursos físicos como auditorios, salas de capacitación
- NUNCA generes más de 6 temas de capacitación
- NUNCA generes más de 3 indicadores
- La documentación debe poder gestionarse por 1 persona
- Prioriza lo ESENCIAL sobre lo IDEAL"
            ];

        } elseif ($estandares <= 21) {
            // Empresas pequeñas: 11-50 trabajadores, riesgo I-III
            return [
                'descripcion' => "EMPRESA PEQUEÑA (21 estándares): Entre 11 y 50 trabajadores, Riesgo I, II o III.
Documentación de complejidad MODERADA. Requiere COPASST.
El responsable del SG-SST puede ser técnico o tecnólogo en SST.",

                'limites' => "- Temas de capacitación: MÁXIMO 8-10 temas
- Indicadores: MÁXIMO 4-5 indicadores
- Actividades del programa: MÁXIMO 10-12 actividades
- Marco normativo: MÁXIMO 8-10 normas relevantes
- Recursos: Moderados y proporcionales
- Roles: 5-6 roles (incluye COPASST)
- Cronograma: Actividades mensuales/bimestrales
- Glosario: MÁXIMO 12 términos",

                'advertencias' => "- Usa COPASST (no Vigía SST)
- Incluye Comité de Convivencia Laboral
- Recursos proporcionales a 11-50 trabajadores
- Balance entre cumplimiento y practicidad"
            ];

        } else {
            // Empresas medianas/grandes: más de 50 trabajadores o riesgo IV-V
            return [
                'descripcion' => "EMPRESA MEDIANA/GRANDE (60 estándares): Más de 50 trabajadores O cualquier tamaño con Riesgo IV o V.
Documentación COMPLETA según todos los estándares de la Res. 0312/2019.
Requiere profesional con licencia en SST. Sistema de gestión robusto.",

                'limites' => "- Temas de capacitación: 12-18 temas según matriz de peligros
- Indicadores: 6-8 indicadores (estructura, proceso, resultado)
- Actividades del programa: 15-20 actividades
- Marco normativo: Completo según aplique
- Recursos: Completos según necesidad
- Roles: Todos los roles necesarios
- Cronograma: Actividades frecuentes según necesidad
- Glosario: 12-15 términos",

                'advertencias' => "- Documentación completa y detallada
- COPASST con todos sus integrantes
- Comité de Convivencia Laboral
- Brigada de emergencias estructurada
- Puede requerir recursos especializados"
            ];
        }
    }

    /**
     * Carga los responsables desde la nueva tabla
     */
    protected function cargarResponsables(int $idCliente, int $estandares): string
    {
        if ($idCliente <= 0) {
            return '';
        }

        try {
            $responsableModel = new \App\Models\ResponsableSSTModel();
            $responsables = $responsableModel->getByCliente($idCliente);

            if (empty($responsables)) {
                return '';
            }

            $info = '';
            $tiposRol = \App\Models\ResponsableSSTModel::TIPOS_ROL;

            foreach ($responsables as $resp) {
                $nombreRol = $tiposRol[$resp['tipo_rol']] ?? $resp['tipo_rol'];
                $info .= "- {$nombreRol}: {$resp['nombre_completo']}";
                if (!empty($resp['cargo'])) {
                    $info .= " ({$resp['cargo']})";
                }
                if ($resp['tipo_rol'] === 'responsable_sgsst') {
                    if (!empty($resp['licencia_sst_numero'])) {
                        $info .= " - Licencia SST: {$resp['licencia_sst_numero']}";
                    }
                    if (!empty($resp['formacion_sst'])) {
                        $info .= " - Formación: {$resp['formacion_sst']}";
                    }
                }
                $info .= "\n";
            }

            return $info;
        } catch (\Exception $e) {
            log_message('error', 'Error cargando responsables: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Base de prompts específicos por tipo de documento (basado en diseño)
     */
    protected function getPromptsBase(): array
    {
        return [
            'PRG' => [ // Programa (13 secciones)
                1 => "Genera una introducción para el programa. Debe incluir:
- Justificación de por qué la empresa necesita este programa
- Contexto de la actividad económica y sus riesgos
- Mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019)
- Compromiso de la alta dirección
IMPORTANTE: Ajusta la extensión según el tamaño de empresa (7 est: 100-150 palabras, 21 est: 150-200 palabras, 60 est: 200-250 palabras)",

                2 => "Genera los objetivos del programa.
Estructura:
OBJETIVO GENERAL:
- Un objetivo medible y alcanzable relacionado con el programa

OBJETIVOS ESPECÍFICOS (ajustar cantidad según estándares):
- 7 estándares: 2-3 objetivos básicos
- 21 estándares: 3-4 objetivos
- 60 estándares: 4-5 objetivos
Deben ser SMART y relacionados con los peligros identificados de la empresa",

                3 => "Define el alcance del programa. Debe especificar:
- A quién aplica (trabajadores directos, contratistas si aplica)
- Áreas o procesos cubiertos
- Sedes incluidas
IMPORTANTE: Para empresas de 7 estándares, el alcance es simple (pocos trabajadores, probablemente 1 sede). Máximo 5-6 ítems para 7 est, 8 ítems para 21 est, 10 ítems para 60 est.",

                4 => "Lista el marco normativo aplicable al programa.

CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: MÁXIMO 4-5 normas
- 21 estándares: MÁXIMO 6-8 normas
- 60 estándares: Según aplique

PROHIBIDO: NO uses tablas (|), NO uses formato tabla Markdown.

USA ESTE FORMATO EXACTO:

**Decreto 1072 de 2015**
Artículos 2.2.4.6.11 y 2.2.4.6.12: Establece la obligación de capacitación en SST.

**Resolución 0312 de 2019**
Estándar 1.2.1: Define los requisitos del programa de capacitación PYP.

Repite este patrón para cada norma. Solo incluye normas que apliquen directamente.",

                5 => "Genera un glosario de términos técnicos para el programa.
AJUSTAR CANTIDAD:
- 7 estándares: MÁXIMO 8 términos esenciales
- 21 estándares: MÁXIMO 12 términos
- 60 estándares: 12-15 términos
Definiciones basadas en normativa colombiana. Solo términos que realmente se usen en el documento.",

                6 => "Genera la estructura de diagnóstico inicial para el programa.
Estructura:
1. Estado actual (qué se tiene)
2. Brechas identificadas (qué falta)
3. Priorización de intervenciones
IMPORTANTE: Para 7 estándares, el diagnóstico es básico y simplificado.
Nota: Indica '[COMPLETAR CON DATOS REALES]' donde se requiera información específica.",

                7 => "Genera el listado de actividades para el programa.
CANTIDAD SEGÚN ESTÁNDARES (ESTO ES OBLIGATORIO):
- 7 estándares: MÁXIMO 6-8 actividades esenciales
- 21 estándares: 10-12 actividades
- 60 estándares: 15-18 actividades

FORMATO OBLIGATORIO (NO usar tablas):
Numerar cada actividad así:
**1. [Nombre de la actividad]**
- Responsable: [quien ejecuta]
- Frecuencia: [anual/semestral/trimestral]
- Entregable: [documento o registro generado]

Las actividades deben ser específicas, ejecutables y proporcionales al tamaño de la empresa.",

                8 => "Genera el cronograma anual para el programa.
AJUSTAR FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Actividades TRIMESTRALES o SEMESTRALES (pocas actividades, espaciadas)
- 21 estándares: Actividades BIMESTRALES o TRIMESTRALES
- 60 estándares: Actividades MENSUALES o según necesidad

FORMATO OBLIGATORIO (NO usar tablas):
Para cada actividad indicar:
**[Nombre actividad]:** [meses de ejecución separados por coma]

Ejemplo:
**Inducción SST:** Según ingreso de personal
**Capacitación en emergencias:** Marzo, Septiembre
**Evaluación del programa:** Diciembre

La cantidad de actividades debe coincidir con la sección anterior.",

                9 => "Define los indicadores de gestión para el programa.
CANTIDAD OBLIGATORIA SEGÚN ESTÁNDARES:
- 7 estándares: EXACTAMENTE 2-3 indicadores simples
- 21 estándares: 4-5 indicadores
- 60 estándares: 6-8 indicadores completos

FORMATO OBLIGATORIO (NO usar tablas):
Para cada indicador:
**[Nombre del indicador]**
- Fórmula: [cómo se calcula]
- Meta: [valor objetivo, ej: ≥90%]
- Frecuencia: [trimestral/semestral/anual]

Ejemplo:
**Cobertura de capacitación**
- Fórmula: (Trabajadores capacitados / Total trabajadores) x 100
- Meta: ≥90%
- Frecuencia: Semestral",

                10 => "Define los roles y responsabilidades para el programa.
ROLES SEGÚN ESTÁNDARES (OBLIGATORIO):
- 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGÍA SST -no COPASST-, Trabajadores)
- 21 estándares: 5-6 roles (incluye COPASST)
- 60 estándares: Todos los roles necesarios
ADVERTENCIA: Si son 7 estándares, NUNCA mencionar COPASST, usar 'Vigía de Seguridad y Salud en el Trabajo'

FORMATO OBLIGATORIO (NO usar tablas):
Para cada rol:
**[Nombre del rol]**
- [Responsabilidad 1]
- [Responsabilidad 2]
- [Responsabilidad 3]",

                11 => "Identifica los recursos necesarios para el programa.
PROPORCIONALIDAD OBLIGATORIA:
- 7 estándares: Recursos MÍNIMOS (tiempo del responsable, materiales básicos, NO incluir auditorios/salas/equipos especiales)
- 21 estándares: Recursos moderados
- 60 estándares: Recursos completos según necesidad
ADVERTENCIA para 7 est: Si la empresa es 100% virtual/remota (ver observaciones del contexto), NO incluir recursos de infraestructura física.
Categorías: 1. Recursos humanos, 2. Recursos técnicos, 3. Recursos financieros",

                12 => "Define el mecanismo de seguimiento y evaluación del programa.
AJUSTAR SEGÚN ESTÁNDARES:
- 7 estándares: Seguimiento TRIMESTRAL o SEMESTRAL, simple, una persona responsable
- 21 estándares: Seguimiento BIMESTRAL o TRIMESTRAL
- 60 estándares: Seguimiento según complejidad
Incluir: Frecuencia, Responsable, Herramienta de seguimiento, Criterios de evaluación",

                13 => "Lista los registros y formatos asociados al programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: MÁXIMO 4-5 formatos esenciales
- 21 estándares: 6-8 formatos
- 60 estándares: Todos los necesarios

FORMATO OBLIGATORIO (NO usar tablas):
Para cada formato:
**[Código] - [Nombre del formato]**
- Responsable: [quien lo diligencia]
- Frecuencia: [cuando se usa]

Ejemplo:
**FOR-CAP-001 - Registro de Asistencia a Capacitación**
- Responsable: Responsable SST
- Frecuencia: Por cada capacitación

NO crear formatos innecesarios para el tamaño de la empresa."
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
