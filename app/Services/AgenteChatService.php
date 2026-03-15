<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use App\Libraries\OttoTableMap;

/**
 * Servicio del Agente Virtual de Chat
 *
 * Interpreta consultas en lenguaje natural usando GPT-4,
 * genera SQL seguro y ejecuta contra la BD con auditoría completa.
 */
class AgenteChatService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected BaseConnection $db;
    protected array $schemaCache = [];

    /** Tablas que NUNCA se pueden modificar (DELETE/UPDATE) */
    protected array $tablasProtegidas = [
        'tbl_usuarios',
        'tbl_sesiones_usuario',
        'tbl_historial_passwords',
        'tbl_recuperacion_password',
        'tbl_roles',
        'tbl_roles_permisos',
        'tbl_permisos',
        'tbl_usuario_roles',
        'tbl_agente_chat_log',
        'migrations',
    ];

    /** Tablas que no se pueden ni leer */
    protected array $tablasOcultas = [
        'tbl_historial_passwords',
        'tbl_recuperacion_password',
    ];

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model  = env('OPENAI_MODEL', 'gpt-4o-mini');
        $this->db     = \Config\Database::connect();
    }

    /**
     * Procesa un mensaje del usuario y retorna la respuesta del agente.
     * $soloLectura = true: solo permite SELECT (modo cliente).
     */
    public function procesarMensaje(string $mensaje, array $historial, array $usuario, bool $soloLectura = false): array
    {
        // 1. Obtener schema compacto de la BD
        $schema = $this->getSchemaCompacto();

        // 2. Construir prompts para GPT
        $systemPrompt = $soloLectura
            ? $this->buildSystemPromptCliente($schema, $usuario)
            : $this->buildSystemPrompt($schema, $usuario);
        $messages = $this->buildMessages($systemPrompt, $historial, $mensaje);

        // 3. Llamar a GPT
        $gptResponse = $this->llamarGPT($messages);
        if (!$gptResponse['success']) {
            return [
                'success' => false,
                'mensaje' => 'Error al contactar la IA: ' . ($gptResponse['error'] ?? 'desconocido'),
                'tipo' => 'error'
            ];
        }

        // 4. Parsear la respuesta de GPT (puede contener SQL o solo texto)
        $parsed = $this->parsearRespuestaGPT($gptResponse['contenido']);

        // 5. Si hay SQL, validar y ejecutar (o pedir confirmación)
        if (!empty($parsed['sql'])) {
            // En modo solo lectura rechazar cualquier escritura
            if ($soloLectura) {
                $tipoSQL = strtoupper(trim(explode(' ', trim($parsed['sql']))[0]));
                if ($tipoSQL !== 'SELECT') {
                    return [
                        'success' => false,
                        'mensaje' => 'Lo siento, en tu perfil de cliente solo puedo responder consultas de información (SELECT). No puedo realizar modificaciones en el sistema.',
                        'tipo' => 'rechazado'
                    ];
                }
            }
            return $this->procesarSQL($parsed, $usuario, $mensaje, $gptResponse['tokens'] ?? 0);
        }

        // 6. Respuesta solo texto
        $this->logOperacion($usuario, $mensaje, null, 'NONE', null, 0, $parsed['texto'], $gptResponse['tokens'] ?? 0, 'ok');

        return [
            'success' => true,
            'mensaje' => $parsed['texto'],
            'tipo' => 'texto',
            'tokens' => $gptResponse['tokens'] ?? 0
        ];
    }

    /**
     * Ejecuta una operación previamente confirmada por el usuario
     */
    public function ejecutarOperacionConfirmada(string $sql, string $tipoOp, array $usuario, string $mensajeOriginal): array
    {
        // Re-validar seguridad
        $validacion = $this->validarSQL($sql, $tipoOp);
        if (!$validacion['valido']) {
            return [
                'success' => false,
                'mensaje' => 'Operación rechazada: ' . $validacion['razon'],
                'tipo' => 'error'
            ];
        }

        return $this->ejecutarSQL($sql, $tipoOp, $usuario, $mensajeOriginal);
    }

    // ─── Schema ────────────────────────────────────────────────────

    /**
     * Genera un schema compacto de todas las tablas para el prompt de GPT
     * Formato: tabla(col1, col2, col3 PK, col4 FK→otra_tabla)
     */
    protected function getSchemaCompacto(): string
    {
        if (!empty($this->schemaCache)) {
            return $this->schemaCache['schema'];
        }

        $cacheFile = WRITEPATH . 'cache/db_schema_chat.json';
        $cacheMaxAge = 3600; // 1 hora

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheMaxAge) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if ($cached) {
                $this->schemaCache = $cached;
                return $cached['schema'];
            }
        }

        $schema = $this->generarSchema();
        $cacheData = ['schema' => $schema, 'timestamp' => time()];
        $this->schemaCache = $cacheData;

        $dir = dirname($cacheFile);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($cacheFile, json_encode($cacheData));

        return $schema;
    }

    protected function generarSchema(): string
    {
        $lines = [];
        $tables = $this->db->query("SHOW TABLES")->getResultArray();

        foreach ($tables as $row) {
            $tableName = array_values($row)[0];

            // Saltar vistas y tablas ocultas
            if (preg_match('/^(v_|vw_)/', $tableName)) continue;
            if (in_array($tableName, $this->tablasOcultas)) continue;

            $columns = $this->db->query("SHOW COLUMNS FROM `{$tableName}`")->getResultArray();
            $colParts = [];
            foreach ($columns as $col) {
                $name = $col['Field'];
                $type = preg_replace('/\(.+\)/', '', $col['Type']); // int, varchar, text, etc.
                $extra = '';
                if ($col['Key'] === 'PRI') $extra = ' PK';
                elseif ($col['Key'] === 'MUL') $extra = ' FK';
                $colParts[] = "{$name}:{$type}{$extra}";
            }
            $lines[] = "{$tableName}(" . implode(', ', $colParts) . ")";
        }

        return implode("\n", $lines);
    }

    /**
     * Invalida la caché del schema (útil tras cambios de estructura)
     */
    public function invalidarCache(): void
    {
        $cacheFile = WRITEPATH . 'cache/db_schema_chat.json';
        if (file_exists($cacheFile)) unlink($cacheFile);
        $this->schemaCache = [];
    }

    // ─── GPT ───────────────────────────────────────────────────────

    protected function buildSystemPrompt(string $schema, array $usuario): string
    {
        $tablasProtegidas  = implode(', ', $this->tablasProtegidas);
        $mapaSemantico     = OttoTableMap::getPromptBlock();
        $directivaFiltrado = OttoTableMap::getGlobalDirectives();

        return <<<PROMPT
Eres Otto, el asistente virtual de EnterpriseSST (gestión de Seguridad y Salud en el Trabajo en Colombia).
Siempre responde de forma amable y profesional. Cuando te presentes, di que eres Otto.
El usuario es un {$usuario['rol']} con acceso autorizado al sistema.

Tu trabajo:
1. Interpretar consultas en lenguaje natural sobre la base de datos
2. Generar SQL MySQL válido cuando sea necesario
3. Explicar los resultados de forma clara y profesional

{$directivaFiltrado}

## MAPA SEMÁNTICO DE TABLAS (columnas verificadas con DESCRIBE real):
{$mapaSemantico}

## SCHEMA COMPLETO (referencia complementaria):
{$schema}

REGLAS ESTRICTAS:
1. Solo genera SQL SELECT, INSERT, UPDATE o DELETE. Nunca DDL (CREATE, ALTER, DROP, TRUNCATE).
2. TABLAS PROTEGIDAS (no se pueden modificar con UPDATE/DELETE/INSERT): {$tablasProtegidas}
   - Sí se pueden consultar con SELECT (excepto tbl_historial_passwords y tbl_recuperacion_password).
3. Nunca generes SQL que elimine TODOS los registros de una tabla (DELETE sin WHERE).
4. UPDATE siempre debe tener WHERE específico.
5. Para DELETE, siempre incluye WHERE con condición específica por ID.
6. Usa backticks para nombres de tablas y columnas.
7. Limita SELECT a 100 filas máximo con LIMIT si el usuario no especifica.
8. No expongas passwords, tokens ni datos sensibles de autenticación.

FORMATO DE RESPUESTA:
- Si necesitas ejecutar SQL, responde EXACTAMENTE así:
```sql
TU_QUERY_AQUÍ
```
Explicación de lo que hace la consulta.

- Si no necesitas SQL (pregunta general, explicación, etc.), responde en texto normal.
- Si el usuario pide algo peligroso o no permitido, explica por qué no puedes hacerlo.
- Siempre responde en español.
- Cuando muestres resultados de consultas, formátealos de forma legible.
PROMPT;
    }

    protected function buildSystemPromptCliente(string $schema, array $usuario): string
    {
        $idCliente     = $usuario['id_cliente'] ?? null;
        $nombreEmpresa = $usuario['nombre_empresa'] ?? 'tu empresa';
        $condicion     = $idCliente ? "id_cliente = {$idCliente}" : '';
        $mapaSemantico = OttoTableMap::getPromptBlock();

        return <<<PROMPT
Eres Otto, el asistente virtual de EnterpriseSST para el cliente {$nombreEmpresa}.
Siempre responde de forma amable y profesional. Cuando te presentes, di que eres Otto.

Tu función es responder preguntas del cliente sobre el estado de su empresa en el sistema SG-SST.

## MAPA SEMÁNTICO DE TABLAS (columnas verificadas con DESCRIBE real):
{$mapaSemantico}

## SCHEMA COMPLETO (referencia complementaria):
{$schema}

REGLAS ESTRICTAS:
1. SOLO puedes generar consultas SELECT. Nunca INSERT, UPDATE, DELETE ni DDL.
2. Siempre filtra los datos por el cliente: agrega WHERE {$condicion} (o AND {$condicion}) en todas tus consultas cuando la tabla tenga el campo id_cliente.
3. No expongas datos de otros clientes ni información confidencial de usuarios.
4. No expongas passwords, tokens ni datos de autenticación.
5. Limita SELECT a 50 filas máximo con LIMIT.

FORMATO DE RESPUESTA:
- Si necesitas ejecutar SQL, responde EXACTAMENTE así:
```sql
TU_QUERY_AQUÍ
```
Explicación clara del resultado.

- Si no necesitas SQL, responde en texto normal.
- Siempre responde en español de forma clara y amigable.
PROMPT;
    }

    protected function buildMessages(string $systemPrompt, array $historial, string $mensajeActual): array
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Agregar historial (últimos 10 mensajes para no exceder contexto)
        $historial = array_slice($historial, -10);
        foreach ($historial as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $mensajeActual];
        return $messages;
    }

    protected function llamarGPT(array $messages): array
    {
        $data = [
            'model'       => $this->model,
            'messages'     => $messages,
            'temperature'  => 0.1, // Muy bajo para precisión SQL
            'max_tokens'   => 4000
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "Error de conexión: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? "HTTP {$httpCode}"];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success'   => true,
                'contenido' => trim($result['choices'][0]['message']['content']),
                'tokens'    => $result['usage']['total_tokens'] ?? 0
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada de la API'];
    }

    // ─── SQL Processing ────────────────────────────────────────────

    protected function parsearRespuestaGPT(string $contenido): array
    {
        $sql = '';
        $texto = $contenido;

        // Extraer bloque SQL si existe
        if (preg_match('/```sql\s*\n?(.*?)\n?```/si', $contenido, $matches)) {
            $sql = trim($matches[1]);
            $texto = trim(preg_replace('/```sql\s*\n?.*?\n?```/si', '', $contenido));
        }

        return ['sql' => $sql, 'texto' => $texto];
    }

    protected function detectarTipoOperacion(string $sql): string
    {
        $sql = strtoupper(trim($sql));
        if (str_starts_with($sql, 'SELECT')) return 'SELECT';
        if (str_starts_with($sql, 'INSERT')) return 'INSERT';
        if (str_starts_with($sql, 'UPDATE')) return 'UPDATE';
        if (str_starts_with($sql, 'DELETE')) return 'DELETE';
        return 'NONE';
    }

    protected function detectarTablasAfectadas(string $sql): array
    {
        $tablas = [];
        // FROM, JOIN, UPDATE, INTO patterns
        preg_match_all('/(?:FROM|JOIN|UPDATE|INTO)\s+`?(\w+)`?/i', $sql, $matches);
        if (!empty($matches[1])) {
            $tablas = array_unique($matches[1]);
        }
        return $tablas;
    }

    protected function validarSQL(string $sql, string $tipoOp): array
    {
        // No DDL
        if (preg_match('/^\s*(CREATE|ALTER|DROP|TRUNCATE|GRANT|REVOKE)/i', $sql)) {
            return ['valido' => false, 'razon' => 'No se permiten operaciones DDL (CREATE, ALTER, DROP, TRUNCATE)'];
        }

        // No múltiples statements
        $cleanSql = preg_replace("/'[^']*'/", '', $sql); // quitar strings
        if (substr_count($cleanSql, ';') > 1) {
            return ['valido' => false, 'razon' => 'No se permiten múltiples sentencias SQL'];
        }

        $tablas = $this->detectarTablasAfectadas($sql);

        // Verificar tablas protegidas para escritura
        if (in_array($tipoOp, ['INSERT', 'UPDATE', 'DELETE'])) {
            foreach ($tablas as $tabla) {
                if (in_array($tabla, $this->tablasProtegidas)) {
                    return ['valido' => false, 'razon' => "La tabla '{$tabla}' está protegida y no se puede modificar"];
                }
            }
        }

        // Verificar tablas ocultas para lectura
        foreach ($tablas as $tabla) {
            if (in_array($tabla, $this->tablasOcultas)) {
                return ['valido' => false, 'razon' => "La tabla '{$tabla}' contiene datos sensibles y no se puede consultar"];
            }
        }

        // DELETE sin WHERE
        if ($tipoOp === 'DELETE' && !preg_match('/WHERE/i', $sql)) {
            return ['valido' => false, 'razon' => 'DELETE sin WHERE no está permitido'];
        }

        // UPDATE sin WHERE
        if ($tipoOp === 'UPDATE' && !preg_match('/WHERE/i', $sql)) {
            return ['valido' => false, 'razon' => 'UPDATE sin WHERE no está permitido'];
        }

        return ['valido' => true, 'razon' => ''];
    }

    protected function procesarSQL(array $parsed, array $usuario, string $mensajeOriginal, int $tokens): array
    {
        $sql    = $parsed['sql'];
        $texto  = $parsed['texto'];
        $tipoOp = $this->detectarTipoOperacion($sql);
        $tablas = $this->detectarTablasAfectadas($sql);

        // Validar seguridad
        $validacion = $this->validarSQL($sql, $tipoOp);
        if (!$validacion['valido']) {
            $this->logOperacion($usuario, $mensajeOriginal, $sql, $tipoOp, $tablas, 0, $validacion['razon'], $tokens, 'rechazado');
            return [
                'success' => false,
                'mensaje' => "Operación rechazada: {$validacion['razon']}",
                'tipo' => 'rechazado'
            ];
        }

        // SELECT se ejecuta directamente
        if ($tipoOp === 'SELECT') {
            return $this->ejecutarSQL($sql, $tipoOp, $usuario, $mensajeOriginal, $texto, $tokens);
        }

        // INSERT/UPDATE/DELETE requieren confirmación
        $this->logOperacion($usuario, $mensajeOriginal, $sql, $tipoOp, $tablas, 0, $texto, $tokens, 'pendiente_confirmacion');

        $response = [
            'success' => true,
            'tipo' => 'confirmacion',
            'operacion' => $tipoOp,
            'sql' => $sql,
            'explicacion' => $texto,
            'tablas' => $tablas,
            'tokens' => $tokens
        ];

        // DELETE requiere doble confirmación aritmética
        if ($tipoOp === 'DELETE') {
            $a = rand(2, 15);
            $b = rand(2, 15);
            $response['verificacion_aritmetica'] = [
                'pregunta' => "¿Cuánto es {$a} + {$b}?",
                'respuesta_correcta' => $a + $b,
                'a' => $a,
                'b' => $b
            ];
        }

        return $response;
    }

    protected function ejecutarSQL(string $sql, string $tipoOp, array $usuario, string $mensajeOriginal, string $texto = '', int $tokens = 0): array
    {
        $tablas = $this->detectarTablasAfectadas($sql);

        try {
            if ($tipoOp === 'SELECT') {
                $result = $this->db->query($sql);
                $rows = $result->getResultArray();
                $numRows = count($rows);

                $this->logOperacion($usuario, $mensajeOriginal, $sql, $tipoOp, $tablas, $numRows, "Consulta exitosa: {$numRows} filas", $tokens, 'ok');

                // Formatear resultados
                $mensaje = $texto ? "{$texto}\n\n" : '';
                if ($numRows === 0) {
                    $mensaje .= "La consulta no retornó resultados.";
                } else {
                    $mensaje .= "**{$numRows} resultado(s):**";
                }

                return [
                    'success' => true,
                    'tipo' => 'resultado',
                    'mensaje' => $mensaje,
                    'datos' => $rows,
                    'total_filas' => $numRows,
                    'sql' => $sql,
                    'tokens' => $tokens
                ];
            } else {
                // INSERT, UPDATE, DELETE
                $this->db->query($sql);
                $affected = $this->db->affectedRows();

                $this->logOperacion($usuario, $mensajeOriginal, $sql, $tipoOp, $tablas, $affected, "Operación exitosa: {$affected} filas afectadas", $tokens, 'ok');

                return [
                    'success' => true,
                    'tipo' => 'modificacion',
                    'mensaje' => "Operación **{$tipoOp}** ejecutada correctamente. **{$affected}** fila(s) afectada(s).",
                    'filas_afectadas' => $affected,
                    'sql' => $sql,
                    'tokens' => $tokens
                ];
            }
        } catch (\Exception $e) {
            $this->logOperacion($usuario, $mensajeOriginal, $sql, $tipoOp, $tablas, 0, $e->getMessage(), $tokens, 'error');

            return [
                'success' => false,
                'tipo' => 'error',
                'mensaje' => "Error al ejecutar la consulta: " . $e->getMessage(),
                'tokens' => $tokens
            ];
        }
    }

    // ─── Logging ───────────────────────────────────────────────────

    protected function logOperacion(array $usuario, string $mensaje, ?string $sql, string $tipoOp, ?array $tablas, int $filas, string $respuesta, int $tokens, string $estado): void
    {
        try {
            $this->db->table('tbl_agente_chat_log')->insert([
                'id_usuario'      => $usuario['id'],
                'rol_usuario'     => $usuario['rol'],
                'sesion_chat'     => $usuario['sesion_chat'] ?? '',
                'mensaje_usuario' => $mensaje,
                'sql_generado'    => $sql,
                'tipo_operacion'  => $tipoOp,
                'tablas_afectadas'=> $tablas ? implode(',', $tablas) : null,
                'filas_afectadas' => $filas,
                'respuesta_agente'=> mb_substr($respuesta, 0, 65000),
                'tokens_usados'   => $tokens,
                'estado'          => $estado,
                'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'AgenteChatService::logOperacion error: ' . $e->getMessage());
        }
    }

    // ─── Utilidades ────────────────────────────────────────────────

    /**
     * Retorna lista de tablas disponibles (para referencia del usuario)
     */
    public function getListaTablas(): array
    {
        $tables = $this->db->query("SHOW TABLES")->getResultArray();
        $result = [];
        foreach ($tables as $row) {
            $name = array_values($row)[0];
            if (preg_match('/^(v_|vw_)/', $name)) continue;
            if (in_array($name, $this->tablasOcultas)) continue;
            $result[] = $name;
        }
        return $result;
    }

    /**
     * Retorna las columnas de una tabla específica
     */
    public function getColumnasTabla(string $tabla): array
    {
        // Sanitizar nombre de tabla
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tabla)) {
            return [];
        }
        if (in_array($tabla, $this->tablasOcultas)) return [];

        try {
            $columns = $this->db->query("SHOW COLUMNS FROM `{$tabla}`")->getResultArray();
            return $columns;
        } catch (\Exception $e) {
            return [];
        }
    }
}
