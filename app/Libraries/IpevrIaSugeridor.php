<?php

namespace App\Libraries;

/**
 * IPEVR GTC 45 — Generador de filas semilla con IA (gpt-4o-mini).
 *
 * Recibe el contexto del cliente + catalogo GTC 45 y devuelve un array
 * de filas pre-diligenciadas que el consultor puede revisar/editar.
 */
class IpevrIaSugeridor
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
     * @param array $contextoCliente  fila completa de tbl_cliente_contexto_sst
     * @param array $cliente          fila de tbl_clientes
     * @param array $catalogo         bundleFrontend() de Gtc45CatalogoModel
     * @param array $maestros         ['procesos'=>[], 'cargos'=>[], 'tareas'=>[], 'zonas'=>[]]
     * @param int   $cantidad         numero objetivo de filas
     * @return array ['ok'=>bool, 'filas'=>[], 'error'=>?]
     */
    public function sugerir(array $contextoCliente, array $cliente, array $catalogo, array $maestros, int $cantidad = 25, bool $modoAuto = false): array
    {
        if (!$this->disponible()) {
            return ['ok' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $prompt = $this->construirPrompt($contextoCliente, $cliente, $catalogo, $maestros, $cantidad, $modoAuto);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user',   'content' => $prompt['user']],
            ],
            'temperature' => 0.4,
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
        if (!is_array($parsed) || !isset($parsed['filas']) || !is_array($parsed['filas'])) {
            return ['ok' => false, 'error' => 'JSON invalido: ' . substr($contenido, 0, 300)];
        }

        return ['ok' => true, 'filas' => $parsed['filas']];
    }

    protected function construirPrompt(array $ctx, array $cli, array $cat, array $mae, int $cantidad, bool $modoAuto = false): array
    {
        $clasifs = array_map(fn($c) => $c['codigo'], $cat['clasificaciones']);
        $ndCodes = array_map(fn($n) => $n['codigo'], $cat['nd']);
        $neCodes = array_map(fn($n) => $n['codigo'], $cat['ne']);
        $ncCodes = array_map(fn($n) => $n['codigo'], $cat['nc']);

        $sector = $ctx['sector_economico'] ?? ($cli['actividad_economica'] ?? 'no especificado');
        $riesgo = $ctx['nivel_riesgo_arl'] ?? '';
        $trabajadores = $ctx['total_trabajadores'] ?? '';
        $peligrosRaw = $ctx['peligros_identificados'] ?? '';
        $peligros = is_string($peligrosRaw) ? $peligrosRaw : json_encode($peligrosRaw);
        $observaciones = $ctx['observaciones_contexto'] ?? '';

        $procesosTxt = implode(', ', array_column($mae['procesos'], 'nombre_proceso')) ?: '(no hay)';
        $cargosTxt   = implode(', ', array_column($mae['cargos'], 'nombre_cargo')) ?: '(no hay)';

        $system = "Eres un experto consultor SG-SST colombiano especializado en la metodologia GTC 45 de identificacion de peligros y valoracion de riesgos.\n\n"
            . "Tu tarea: generar filas pre-diligenciadas de una matriz IPEVR para un cliente real, basandote en su contexto empresarial.\n\n"
            . "REGLAS ESTRICTAS:\n"
            . "1. Responde UNICAMENTE con un JSON valido con la estructura: { \"filas\": [ {...}, {...} ] }\n"
            . "2. Cada fila debe tener exactamente estos campos:\n"
            . "   - proceso (string)\n"
            . "   - zona (string)\n"
            . "   - actividad (string)\n"
            . "   - tarea (string)\n"
            . "   - rutinaria (boolean)\n"
            . "   - cargos_expuestos (array de strings)\n"
            . "   - num_expuestos (int)\n"
            . "   - peligro_descripcion (string)\n"
            . "   - clasificacion (uno de: " . implode(' | ', $clasifs) . ")\n"
            . "   - efectos_posibles (string)\n"
            . "   - control_fuente, control_medio, control_individuo (strings, pueden ser 'Ninguno')\n"
            . "   - nd (uno de: " . implode(' | ', $ndCodes) . ")\n"
            . "   - ne (uno de: " . implode(' | ', $neCodes) . ")\n"
            . "   - nc (uno de: " . implode(' | ', $ncCodes) . ")\n"
            . "   - peor_consecuencia (string)\n"
            . "   - requisito_legal (string, citar norma colombiana aplicable)\n"
            . "   - medidas: objeto con eliminacion, sustitucion, ingenieria, administrativo, epp (strings)\n"
            . "3. No calcules NP ni NR, el sistema los calcula automaticamente.\n"
            . "4. Usa vocabulario tecnico colombiano SST (Resoluciones, Decreto 1072, etc.).\n"
            . ($modoAuto
                ? "5. Genera TODAS las filas que consideres necesarias para cubrir adecuadamente los procesos, cargos, tareas y peligros del contexto del cliente. No te limites a un numero fijo — prioriza cobertura completa. Incluye al menos una fila por cada proceso identificado y cada clasificacion de peligro relevante.\n"
                : "5. Genera EXACTAMENTE {$cantidad} filas diversas que cubran diferentes procesos y clasificaciones de peligro.\n"
            )
            . "6. Si el sector tiene peligros tipicos (ej. construccion -> caidas, trabajo alturas; oficinas -> biomecanico, psicosocial), priorizalos.\n";

        $cantidadInstruccion = $modoAuto
            ? "Genera TODAS las filas necesarias para una matriz IPEVR completa y funcional"
            : "Genera {$cantidad} filas de matriz IPEVR GTC 45";

        $user = "CONTEXTO DEL CLIENTE:\n"
            . "- Empresa: " . ($cli['nombre_cliente'] ?? '') . "\n"
            . "- Sector economico: {$sector}\n"
            . "- Nivel de riesgo ARL: {$riesgo}\n"
            . "- Total trabajadores: {$trabajadores}\n"
            . "- Peligros ya identificados por el cliente: {$peligros}\n"
            . "- Observaciones: {$observaciones}\n"
            . "- Procesos definidos: {$procesosTxt}\n"
            . "- Cargos definidos: {$cargosTxt}\n\n"
            . "{$cantidadInstruccion} coherentes con este contexto. Responde solo con el JSON.";

        return ['system' => $system, 'user' => $user];
    }
}
