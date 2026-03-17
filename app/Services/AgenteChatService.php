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
    protected BaseConnection $db;       // default — para logging
    protected BaseConnection $dbExec;   // puede ser 'readonly' para el cliente
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

    public function __construct(string $dbGroup = 'default')
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model  = env('OTTO_MODEL', 'gpt-4o-mini');
        $this->db     = \Config\Database::connect('default');

        // Capa 1 DB: para el cliente usamos config dinámica (evita PHP 8.2 "Constant expression"
        // que ocurre con env() en inicializadores de propiedades de Database.php)
        if ($dbGroup === 'readonly') {
            $this->dbExec = \Config\Database::connect($this->buildReadonlyConfig());
        } else {
            $this->dbExec = \Config\Database::connect('default');
        }
    }

    /**
     * Construye la config de conexión readonly dinámicamente en tiempo de ejecución.
     * NO puede estar en Database.php porque PHP 8.2 prohíbe env() en property initializers.
     */
    protected function buildReadonlyConfig(): array
    {
        return [
            'DSN'          => '',
            'hostname'     => env('readonly.hostname', 'localhost'),
            'username'     => env('readonly.username', 'empresas_readonly'),
            'password'     => env('readonly.password', 'EmpresasReadOnly2026!'),
            'database'     => env('readonly.database', 'empresas_sst'),
            'DBDriver'     => 'MySQLi',
            'DBPrefix'     => '',
            'pConnect'     => false,
            'DBDebug'      => false,
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_general_ci',
            'swapPre'      => '',
            'encrypt'      => (bool) env('readonly.encrypt', false),
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => (int) env('readonly.port', 3306),
            'numberNative' => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];
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
        $now               = date('Y-m-d');
        $year              = date('Y');

        return <<<PROMPT
Eres Otto, el asistente virtual de EnterpriseSST (gestión de Seguridad y Salud en el Trabajo en Colombia).
Responde siempre de forma amable y profesional. Cuando te presentes, di que eres Otto.
El usuario es un {$usuario['rol']} con acceso autorizado al sistema.
Fecha actual: {$now}. Año en curso: {$year}. Usa estos valores en cualquier filtro de fecha o año.

{$directivaFiltrado}

## TABLAS Y VISTAS DISPONIBLES (columnas verificadas con DESCRIBE real):
{$mapaSemantico}

REGLAS ESTRICTAS:
1. NUNCA muestres el SQL generado en tu respuesta de texto. Solo explica el resultado en lenguaje natural.
2. NUNCA uses SELECT * — lista siempre las columnas específicas que necesitas. SELECT * puede devolver megabytes y causar fallos silenciosos.
3. JERARQUÍA DE ACCESO:
   - SELECT → usa siempre vistas `v_*` (tienen JOINs resueltos y textos legibles).
   - INSERT / UPDATE / DELETE → usa tablas `tbl_*` directamente.
   - Si la vista no existe, usa la tabla `tbl_*` con los JOINs necesarios.
4. MAYÉUTICA — ANTES DE EJECUTAR, pregunta SOLO si el parámetro falta por completo (no si viene incompleto):
   - ¿Qué cliente / empresa? → SOLO si el usuario NO mencionó ningún nombre ni referencia. Si dio un nombre parcial (ej: "ardurra", "colbus", "acme"), usa LIKE '%término%' directamente SIN pedir confirmación.
   - ¿Qué año o período? → SOLO si la consulta lo necesita y el usuario no dio ninguna pista de fecha.
   - ¿Qué estado? → SOLO si hay ambigüedad real (ej: "muéstrame las actividades" sin contexto de estado).
   - ¿Qué tipo o categoría? → SOLO si hay múltiples tipos y la respuesta cambia radicalmente según el tipo.
   REGLA CLAVE: si el usuario da cualquier pista de nombre (aunque sea 3 letras), ejecuta con LIKE '%pista%' — nunca pidas confirmación del nombre exacto.
5. BÚSQUEDA POR NOMBRE: NUNCA uses = para buscar por nombre. Siempre usa LIKE '%término%' (el usuario solo recuerda parte del nombre).
6. ESTADOS — traduce lenguaje natural al ENUM exacto de la BD:
   PTA (v_pta_cliente.estado_actividad):
   - "abiertas/pendientes/activas"  → IN ('ABIERTA','GESTIONANDO')
   - "en gestión/gestionando"       → = 'GESTIONANDO'
   - "cerradas"                     → IN ('CERRADA','CERRADA SIN EJECUCIÓN','CERRADA POR FIN CONTRATO')
   Pendientes (v_pendientes.estado):
   - "abiertas/pendientes"          → = 'ABIERTA'
   - "sin respuesta"                → = 'SIN RESPUESTA DEL CLIENTE'
   - "cerradas"                     → IN ('CERRADA','CERRADA POR FIN CONTRATO')
   Mantenimientos (v_vencimientos_mantenimientos.estado_actividad):
   - "por ejecutar/pendientes"      → = 'sin ejecutar'
   - "ejecutados"                   → = 'ejecutado'
   Capacitaciones (v_cronog_capacitacion.estado):
   - "programadas"                  → IN ('PROGRAMADA','REPROGRAMADA')
   - "ejecutadas/realizadas"        → = 'EJECUTADA'
   - "canceladas"                   → = 'CANCELADA POR EL CLIENTE'
   Documentos (v_documentos_sst.estado):
   - "firmados/aprobados"           → IN ('firmado','aprobado')
   - "pendientes de firma"          → = 'pendiente_firma'
   - "borradores"                   → = 'borrador'
   Hallazgos ACC (v_acc_hallazgos.estado):
   - "abiertos"                     → = 'abierto'
   - "en tratamiento/proceso"       → = 'en_tratamiento'
   - "cerrados"                     → IN ('cerrado','cerrado_no_efectivo')
   Acciones ACC (v_acc_acciones.estado):
   - "en ejecución"                 → = 'en_ejecucion'
   - "cerradas efectivas"           → = 'cerrada_efectiva'
   - "asignadas"                    → = 'asignada'
   Contratos/Clientes:
   - "activos"                      → = 'activo'
   - "vencidos"                     → = 'vencido'
7. Solo genera SELECT, INSERT, UPDATE o DELETE. Nunca DDL (CREATE, ALTER, DROP, TRUNCATE).
8. TABLAS PROTEGIDAS (no modificar): {$tablasProtegidas}
9. Nunca DELETE sin WHERE. Nunca UPDATE sin WHERE.
10. LIMIT 100 en SELECT si el usuario no especifica cantidad.
11. No expongas passwords, tokens ni datos de autenticación.

FORMATO DE RESPUESTA:
- Para ejecutar SQL, responde EXACTAMENTE así (sin mostrar el SQL al usuario):
```sql
TU_QUERY_AQUÍ
```
Luego escribe solo la explicación en lenguaje natural.
- Sin SQL: responde en texto normal.
- Siempre en español.
PROMPT;
    }

    protected function buildSystemPromptCliente(string $schema, array $usuario): string
    {
        $idCliente     = $usuario['id_cliente'] ?? null;
        $nombreEmpresa = $usuario['nombre_empresa'] ?? 'tu empresa';
        $condicion     = $idCliente ? "id_cliente = {$idCliente}" : '';
        $mapaSemantico = OttoTableMap::getPromptBlock();
        $now           = date('Y-m-d');
        $year          = date('Y');

        return <<<PROMPT
Eres Otto, el asistente virtual de EnterpriseSST para la empresa {$nombreEmpresa}.
Responde siempre de forma amable y profesional. Cuando te presentes, di que eres Otto.
Fecha actual: {$now}. Año en curso: {$year}. Usa estos valores en cualquier filtro de fecha o año.

Tu función es responder preguntas sobre el estado del SG-SST de {$nombreEmpresa}.

## TABLAS Y VISTAS DISPONIBLES:
{$mapaSemantico}

REGLAS ESTRICTAS:
1. SOLO SELECT. Nunca INSERT, UPDATE, DELETE ni DDL.
2. NUNCA muestres el SQL generado en tu respuesta. Solo explica el resultado en lenguaje natural.
3. NUNCA uses SELECT * — lista siempre columnas específicas.
4. JERARQUÍA: usa siempre vistas `v_*` para SELECT.
5. SCOPE OBLIGATORIO: toda consulta debe incluir WHERE {$condicion} (o AND {$condicion}) cuando la tabla tenga id_cliente. Sin esta condición el guardrail rechaza la query.
6. MAYÉUTICA — pregunta SOLO si el parámetro falta por completo. Si el usuario da cualquier pista de nombre, fecha o estado, ejecuta directamente con LIKE / filtro correspondiente sin pedir confirmación.
7. BÚSQUEDA POR NOMBRE: NUNCA uses = para buscar por nombre. Siempre LIKE '%término%'.
8. ESTADOS — traduce lenguaje natural al ENUM exacto:
   - "abiertas/pendientes/en gestión" en PTA      → estado_actividad IN ('ABIERTA','GESTIONANDO')
   - "cerradas" en PTA                            → IN ('CERRADA','CERRADA SIN EJECUCIÓN','CERRADA POR FIN CONTRATO')
   - "abiertas/pendientes" en pendientes          → estado = 'ABIERTA'
   - "sin respuesta" en pendientes                → = 'SIN RESPUESTA DEL CLIENTE'
   - "por ejecutar" en mantenimientos             → estado_actividad = 'sin ejecutar'
   - "programadas" en capacitaciones              → IN ('PROGRAMADA','REPROGRAMADA')
   - "firmados/aprobados" en documentos           → IN ('firmado','aprobado')
9. No expongas datos de otros clientes, passwords ni tokens.
10. LIMIT 50 en SELECT.

FORMATO DE RESPUESTA:
- Para ejecutar SQL:
```sql
TU_QUERY_AQUÍ
```
Luego escribe solo la explicación en lenguaje natural (sin mostrar el SQL).
- Sin SQL: responde en texto normal.
- Siempre en español.
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
                log_message('info', '[Otto] QUERY_START sql=' . substr($sql, 0, 120));
                $result = $this->dbExec->query($sql);
                $rows   = $result->getResultArray();
                $numRows = count($rows);

                // Sanitizar strings largos antes de enviar a OpenAI
                // Evita: json_encode silencioso, megabytes innecesarios, exceder max_tokens
                $rows = $this->sanitizarFilas($rows);

                log_message('info', '[Otto] QUERY_OK rows=' . $numRows);
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
                $this->dbExec->query($sql);
                $affected = $this->dbExec->affectedRows();

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

    // ─── Sanitización ──────────────────────────────────────────────

    /**
     * Sanitiza los rows antes de enviar a OpenAI.
     * - Fuerza UTF-8 para evitar json_encode silencioso
     * - Trunca strings > 800 chars (actividad_plandetrabajo, observaciones, etc.)
     * Ref: aprendizaje #7 — SELECT * con TEXT largo causa fallos silenciosos
     */
    protected function sanitizarFilas(array $rows): array
    {
        array_walk_recursive($rows, function (&$value) {
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                if (mb_strlen($value) > 800) {
                    $value = mb_substr($value, 0, 800) . '…';
                }
            }
        });
        return $rows;
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
