<?php

namespace App\Libraries;

/**
 * Profesiograma — Sugeridor de examenes medicos con IA (gpt-4o-mini).
 *
 * Recibe el contexto del cliente + peligros IPEVR del cargo y sugiere
 * examenes medicos ocupacionales con justificacion clinica, frecuencias
 * personalizadas y observaciones que el cruce mecanico no puede dar.
 */
class ProfesiogramaIaSugeridor
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected string $model  = 'gpt-4o-mini';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    public function disponible(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param array $cliente         fila de tbl_clientes
     * @param array $contexto        fila de tbl_cliente_contexto_sst
     * @param array $cargo           fila de tbl_cargos_cliente
     * @param array $peligrosCargo   clasificaciones GTC45 del cargo extraidas de IPEVR
     * @param array $filasIpevr      filas IPEVR que mencionan este cargo
     * @param array $catalogoExamenes examenes disponibles en catalogo
     * @param array $yaAsignados     examenes ya asignados al cargo [{id_examen, momento}]
     * @return array ['ok'=>bool, 'sugerencias'=>[], 'error'=>?]
     */
    public function sugerir(
        array $cliente,
        array $contexto,
        array $cargo,
        array $peligrosCargo,
        array $filasIpevr,
        array $catalogoExamenes,
        array $yaAsignados
    ): array {
        if (!$this->disponible()) {
            return ['ok' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $prompt = $this->construirPrompt(
            $cliente, $contexto, $cargo, $peligrosCargo,
            $filasIpevr, $catalogoExamenes, $yaAsignados
        );

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user',   'content' => $prompt['user']],
            ],
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 120,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['ok' => false, 'error' => 'cURL: ' . $err];
        }
        if ($code !== 200) {
            return ['ok' => false, 'error' => "HTTP {$code}: " . substr((string)$resp, 0, 500)];
        }

        $json = json_decode((string)$resp, true);
        $contenido = $json['choices'][0]['message']['content'] ?? '';
        if (!$contenido) {
            return ['ok' => false, 'error' => 'Respuesta vacia de la IA'];
        }

        $parsed = json_decode($contenido, true);
        if (!is_array($parsed) || !isset($parsed['sugerencias']) || !is_array($parsed['sugerencias'])) {
            return ['ok' => false, 'error' => 'JSON invalido: ' . substr($contenido, 0, 300)];
        }

        return ['ok' => true, 'sugerencias' => $parsed['sugerencias']];
    }

    protected function construirPrompt(
        array $cli, array $ctx, array $cargo, array $peligros,
        array $filasIpevr, array $catalogoExamenes, array $yaAsignados
    ): array {
        // Construir lista de examenes del catalogo
        $catalogoTxt = '';
        foreach ($catalogoExamenes as $ex) {
            $catalogoTxt .= "  - id={$ex['id']} | {$ex['nombre']} | tipo={$ex['tipo_examen']} | aplica=[" .
                implode(',', json_decode($ex['clasificaciones_aplica'] ?? '[]', true) ?: []) . "]\n";
        }

        // Construir resumen de peligros del cargo desde IPEVR
        $peligrosTxt = '';
        foreach ($filasIpevr as $fila) {
            $desc = $fila['descripcion_peligro'] ?? '';
            $efec = $fila['efectos_posibles'] ?? '';
            $nr   = $fila['nr'] ?? '';
            $acep = $fila['aceptabilidad'] ?? '';
            $peligrosTxt .= "  - {$desc} | Efectos: {$efec} | NR={$nr} ({$acep})\n";
        }

        // Construir lista de ya asignados
        $yaAsigTxt = '';
        foreach ($yaAsignados as $a) {
            $yaAsigTxt .= "  - examen_id={$a['id_examen']} momento={$a['momento']} freq={$a['frecuencia']}\n";
        }

        $sector = $ctx['sector_economico'] ?? 'no especificado';
        $riesgo = $ctx['nivel_riesgo_arl'] ?? '';
        $obs    = $ctx['observaciones_contexto'] ?? '';

        $system = "Eres un medico especialista en salud ocupacional colombiano con experiencia en profesiogramas.\n\n"
            . "Tu tarea: analizar el cargo, sus peligros IPEVR y el contexto del cliente para sugerir examenes medicos ocupacionales.\n\n"
            . "REGLAS ESTRICTAS:\n"
            . "1. Responde UNICAMENTE con JSON valido: { \"sugerencias\": [ {...}, {...} ] }\n"
            . "2. Cada sugerencia debe tener EXACTAMENTE estos campos:\n"
            . "   - id_examen (int, DEBE ser un id valido del catalogo proporcionado)\n"
            . "   - nombre_examen (string, nombre del examen del catalogo)\n"
            . "   - momentos (array de strings: \"ingreso\", \"periodico\", \"retiro\")\n"
            . "   - frecuencia (string: \"anual\", \"semestral\", \"cada_2_anios\", \"unica_vez\", \"segun_caso\")\n"
            . "   - obligatorio (boolean: true si es indispensable, false si es recomendado)\n"
            . "   - justificacion (string: razon clinica ocupacional de por que este examen aplica para este cargo)\n"
            . "   - observaciones (string: indicaciones clinicas especificas, ej: 'Audiometria semestral por exposicion continua >85dB')\n"
            . "   - prioridad (string: \"alta\", \"media\", \"baja\")\n"
            . "3. SOLO sugiere examenes del catalogo proporcionado (usa los id exactos).\n"
            . "4. NO repitas examenes que ya estan asignados al cargo (lista proporcionada abajo).\n"
            . "5. Prioriza examenes segun nivel de riesgo (NR alto = prioridad alta).\n"
            . "6. Usa normativa colombiana: Res 2346/2007, Res 1918/2009, Decreto 1072/2015, GATISO.\n"
            . "7. Si el cargo tiene riesgo biomecanico alto, sugiere examenes osteomusculares con mayor frecuencia.\n"
            . "8. Si hay exposicion a quimicos, incluye examenes de laboratorio especificos.\n"
            . "9. Para trabajo en alturas o conduccion, incluir evaluaciones especializadas.\n"
            . "10. El examen medico ocupacional general (id=1 si existe) SIEMPRE debe sugerirse si no esta asignado.\n";

        $user = "CONTEXTO DEL CLIENTE:\n"
            . "- Empresa: " . ($cli['nombre_cliente'] ?? '') . "\n"
            . "- Sector: {$sector}\n"
            . "- Nivel riesgo ARL: {$riesgo}\n"
            . "- Trabajadores: " . ($ctx['total_trabajadores'] ?? '') . "\n"
            . "- Observaciones: {$obs}\n\n"
            . "CARGO A EVALUAR:\n"
            . "- Nombre: {$cargo['nombre_cargo']}\n"
            . "- Ocupantes: " . ($cargo['num_ocupantes'] ?? 'no definido') . "\n\n"
            . "PELIGROS IPEVR IDENTIFICADOS PARA ESTE CARGO:\n"
            . ($peligrosTxt ?: "  (ninguno especifico)\n")
            . "\nCLASIFICACIONES GTC45 DEL CARGO: " . implode(', ', $peligros) . "\n\n"
            . "CATALOGO DE EXAMENES DISPONIBLES:\n"
            . $catalogoTxt . "\n"
            . "EXAMENES YA ASIGNADOS A ESTE CARGO (NO repetir):\n"
            . ($yaAsigTxt ?: "  (ninguno)\n") . "\n"
            . "Sugiere los examenes medicos que faltan para este cargo. Responde solo con el JSON.";

        return ['system' => $system, 'user' => $user];
    }
}
