<?php

namespace App\Controllers;

use App\Models\MatrizLegalModel;
use CodeIgniter\Controller;

class MatrizLegalController extends Controller
{
    protected MatrizLegalModel $model;
    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->model = new MatrizLegalModel();
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    /**
     * Vista principal - Lista de normas con DataTables
     */
    public function index()
    {
        $data = [
            'titulo' => 'Matriz Legal SST',
            'sectores' => MatrizLegalModel::$sectores,
            'tiposNorma' => MatrizLegalModel::$tiposNorma,
            'estados' => MatrizLegalModel::$estados,
            'temas' => $this->model->getTemasUnicos(),
            'anios' => $this->model->getAniosUnicos(),
            'estadisticas' => $this->model->getEstadisticas()
        ];

        return view('matriz_legal/index', $data);
    }

    /**
     * API para DataTables (server-side processing)
     */
    public function datatable()
    {
        $params = [
            'draw' => $this->request->getGet('draw'),
            'start' => $this->request->getGet('start') ?? 0,
            'length' => $this->request->getGet('length') ?? 10,
            'search' => $this->request->getGet('search') ?? [],
            'order' => $this->request->getGet('order') ?? [],
            'columns' => $this->request->getGet('columns') ?? []
        ];

        $result = $this->model->getNormasDataTables($params);

        return $this->response->setJSON([
            'draw' => intval($params['draw']),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data']
        ]);
    }

    /**
     * Ver detalle de una norma
     */
    public function ver(int $id)
    {
        $norma = $this->model->find($id);

        if (!$norma) {
            return redirect()->to('/matriz-legal')->with('error', 'Norma no encontrada');
        }

        return $this->response->setJSON([
            'success' => true,
            'norma' => $norma
        ]);
    }

    /**
     * Formulario para crear nueva norma
     */
    public function crear()
    {
        $data = [
            'titulo' => 'Nueva Norma Legal',
            'sectores' => MatrizLegalModel::$sectores,
            'tiposNorma' => MatrizLegalModel::$tiposNorma,
            'estados' => MatrizLegalModel::$estados,
            'norma' => null
        ];

        return view('matriz_legal/form', $data);
    }

    /**
     * Guardar nueva norma
     */
    public function guardar()
    {
        $rules = [
            'tema' => 'required|min_length[3]',
            'tipo_norma' => 'required',
            'id_norma_legal' => 'required',
            'anio' => 'required|numeric|greater_than[1900]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'sector' => $this->request->getPost('sector') ?: 'General',
            'tema' => $this->request->getPost('tema'),
            'subtema' => $this->request->getPost('subtema'),
            'tipo_norma' => $this->request->getPost('tipo_norma'),
            'id_norma_legal' => $this->request->getPost('id_norma_legal'),
            'anio' => $this->request->getPost('anio'),
            'descripcion_norma' => $this->request->getPost('descripcion_norma'),
            'autoridad_emisora' => $this->request->getPost('autoridad_emisora'),
            'referente_nacional' => $this->request->getPost('referente_nacional') ? 'x' : '',
            'referente_internacional' => $this->request->getPost('referente_internacional') ? 'x' : '',
            'articulos_aplicables' => $this->request->getPost('articulos_aplicables'),
            'parametros' => $this->request->getPost('parametros'),
            'notas_vigencia' => $this->request->getPost('notas_vigencia'),
            'estado' => $this->request->getPost('estado') ?: 'activa'
        ];

        $id = $this->request->getPost('id');

        if ($id) {
            $this->model->update($id, $data);
            $mensaje = 'Norma actualizada correctamente';
        } else {
            $id = $this->model->insert($data);
            $mensaje = 'Norma creada correctamente';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $mensaje,
            'id' => $id
        ]);
    }

    /**
     * Editar norma existente
     */
    public function editar(int $id)
    {
        $norma = $this->model->find($id);

        if (!$norma) {
            return redirect()->to('/matriz-legal')->with('error', 'Norma no encontrada');
        }

        $data = [
            'titulo' => 'Editar Norma Legal',
            'sectores' => MatrizLegalModel::$sectores,
            'tiposNorma' => MatrizLegalModel::$tiposNorma,
            'estados' => MatrizLegalModel::$estados,
            'norma' => $norma
        ];

        return view('matriz_legal/form', $data);
    }

    /**
     * Eliminar norma
     */
    public function eliminar(int $id)
    {
        $norma = $this->model->find($id);

        if (!$norma) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Norma no encontrada'
            ]);
        }

        $this->model->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Norma eliminada correctamente'
        ]);
    }

    /**
     * Vista de carga CSV
     */
    public function importarCSV()
    {
        $data = [
            'titulo' => 'Importar Matriz Legal desde CSV',
            'sectores' => MatrizLegalModel::$sectores
        ];

        return view('matriz_legal/importar_csv', $data);
    }

    /**
     * Preview del CSV antes de importar
     */
    public function previewCSV()
    {
        $file = $this->request->getFile('archivo_csv');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibió un archivo válido'
            ]);
        }

        $extension = $file->getExtension();
        if (!in_array($extension, ['csv', 'txt'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El archivo debe ser CSV o TXT'
            ]);
        }

        // Leer archivo
        $content = file_get_contents($file->getTempName());
        // Detectar codificación y convertir a UTF-8
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');

        // Detectar delimitador
        $delimitador = $this->detectarDelimitador($content);

        // Parsear CSV
        $lineas = explode("\n", $content);
        $headers = str_getcsv(array_shift($lineas), $delimitador);

        // Limpiar headers
        $headers = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $headers);

        // Mapeo de columnas esperadas
        $mapeo = $this->mapearColumnas($headers);

        $datos = [];
        $contador = 0;
        foreach ($lineas as $linea) {
            if (empty(trim($linea))) continue;
            if ($contador >= 10) break; // Preview de 10 filas

            $fila = str_getcsv($linea, $delimitador);
            $registro = [];

            foreach ($mapeo as $campo => $indice) {
                $registro[$campo] = isset($fila[$indice]) ? trim($fila[$indice]) : '';
            }

            $datos[] = $registro;
            $contador++;
        }

        return $this->response->setJSON([
            'success' => true,
            'headers' => $headers,
            'mapeo' => $mapeo,
            'preview' => $datos,
            'total_lineas' => count($lineas),
            'delimitador' => $delimitador
        ]);
    }

    /**
     * Procesar importación CSV
     */
    public function procesarCSV()
    {
        $file = $this->request->getFile('archivo_csv');
        $sectorDefecto = $this->request->getPost('sector_defecto') ?: 'General';

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibió un archivo válido'
            ]);
        }

        // Leer archivo
        $content = file_get_contents($file->getTempName());
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');

        $delimitador = $this->detectarDelimitador($content);
        $lineas = explode("\n", $content);
        $headers = str_getcsv(array_shift($lineas), $delimitador);

        $headers = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $headers);

        $mapeo = $this->mapearColumnas($headers);

        $datos = [];
        foreach ($lineas as $linea) {
            if (empty(trim($linea))) continue;

            $fila = str_getcsv($linea, $delimitador);
            $registro = [];

            foreach ($mapeo as $campo => $indice) {
                $registro[$campo] = isset($fila[$indice]) ? trim($fila[$indice]) : '';
            }

            // Solo agregar si tiene datos mínimos
            if (!empty($registro['tema']) || !empty($registro['tipo_norma']) || !empty($registro['id_norma_legal'])) {
                $datos[] = $registro;
            }
        }

        $resultado = $this->model->importarCSV($datos, $sectorDefecto);

        return $this->response->setJSON([
            'success' => $resultado['exito'],
            'insertados' => $resultado['insertados'],
            'errores' => $resultado['errores'],
            'message' => "Se importaron {$resultado['insertados']} registros correctamente"
        ]);
    }

    /**
     * Detectar delimitador del CSV
     */
    protected function detectarDelimitador(string $content): string
    {
        $primeraLinea = strtok($content, "\n");
        $delimitadores = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];

        foreach (array_keys($delimitadores) as $del) {
            $delimitadores[$del] = substr_count($primeraLinea, $del);
        }

        return array_search(max($delimitadores), $delimitadores);
    }

    /**
     * Mapear columnas del CSV a campos de la tabla
     */
    protected function mapearColumnas(array $headers): array
    {
        $mapeo = [];
        $campos = [
            'sector' => ['sector'],
            'tema' => ['tema'],
            'subtema' => ['subtema'],
            'tipo_norma' => ['tipo de norma', 'tipo_norma', 'tipo norma'],
            'id_norma_legal' => ['id norma legal', 'id_norma_legal', 'numero', 'número', 'norma'],
            'anio' => ['año', 'anio', 'ano'],
            'descripcion_norma' => ['descripción de la norma legal', 'descripcion_norma', 'descripción', 'descripcion'],
            'autoridad_emisora' => ['autoridad que lo emite', 'autoridad_emisora', 'autoridad', 'entidad'],
            'referente_nacional' => ['referente nacional', 'referente_nacional', 'nacional'],
            'referente_internacional' => ['referente iternacional', 'referente internacional', 'referente_internacional', 'internacional'],
            'articulos_aplicables' => ['artículos aplicables', 'articulos_aplicables', 'artículos', 'articulos'],
            'parametros' => ['parámetros', 'parametros'],
            'notas_vigencia' => ['notas vigencias / observaciones', 'notas_vigencia', 'notas', 'observaciones', 'vigencia']
        ];

        foreach ($campos as $campo => $variantes) {
            foreach ($headers as $indice => $header) {
                $headerLimpio = strtolower(trim($header));
                foreach ($variantes as $variante) {
                    if ($headerLimpio === strtolower($variante)) {
                        $mapeo[$campo] = $indice;
                        break 2;
                    }
                }
            }
        }

        return $mapeo;
    }

    // ==================== MÓDULO IA ====================

    /**
     * Vista del módulo IA para buscar normas
     */
    public function buscarIA()
    {
        $data = [
            'titulo' => 'Buscar Norma con IA',
            'sectores' => MatrizLegalModel::$sectores,
            'tiposNorma' => MatrizLegalModel::$tiposNorma
        ];

        return view('matriz_legal/buscar_ia', $data);
    }

    /**
     * Procesar búsqueda con IA
     */
    public function procesarBusquedaIA()
    {
        $consulta = $this->request->getPost('consulta');

        if (empty($consulta)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe ingresar una consulta'
            ]);
        }

        if (empty($this->apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API Key de OpenAI no configurada'
            ]);
        }

        try {
            $resultado = $this->buscarNormaConIA($consulta);

            return $this->response->setJSON([
                'success' => true,
                'norma' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al consultar IA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar información de norma usando OpenAI
     */
    protected function buscarNormaConIA(string $consulta): array
    {
        $systemPrompt = "Eres un experto en legislación colombiana de Seguridad y Salud en el Trabajo (SST).
Tu tarea es buscar y proporcionar información detallada sobre normas legales colombianas.

Cuando el usuario mencione una norma (ej: 'Resolución 0312 de 2019', 'Decreto 1072 de 2015', 'Ley 1562 de 2012'),
debes responder ÚNICAMENTE con un JSON válido con la siguiente estructura:

{
    \"sector\": \"General o el sector específico si aplica\",
    \"tema\": \"Tema principal de la norma (ej: Sistema General de Riesgos Laborales)\",
    \"subtema\": \"Subtema específico si aplica\",
    \"tipo_norma\": \"Tipo de norma (Ley, Decreto, Resolución, Circular, etc.)\",
    \"id_norma_legal\": \"Número de la norma (ej: 0312, 1072, 1562)\",
    \"anio\": Año como número (ej: 2019),
    \"descripcion_norma\": \"Descripción completa de qué establece la norma (máximo 500 caracteres)\",
    \"autoridad_emisora\": \"Entidad que expide la norma (ej: Ministerio del Trabajo)\",
    \"referente_nacional\": \"x si es referente nacional, vacío si no\",
    \"referente_internacional\": \"x si tiene referente internacional, vacío si no\",
    \"articulos_aplicables\": \"Artículos más relevantes para SST\",
    \"parametros\": \"Parámetros o requisitos principales que establece la norma\",
    \"notas_vigencia\": \"Estado de vigencia, modificaciones o derogatorias\"
}

IMPORTANTE:
- Responde SOLO con el JSON, sin texto adicional
- Si no encuentras información sobre la norma, responde con un JSON con campo 'error'
- Asegúrate de que el año sea un número, no texto
- La información debe ser precisa y basada en la legislación colombiana real";

        $userPrompt = "Busca en internet información actualizada sobre la siguiente norma colombiana y devuelve los datos en formato JSON:\n\n{$consulta}";

        // Usar el nuevo endpoint de OpenAI Responses con búsqueda web
        $apiUrlResponses = 'https://api.openai.com/v1/responses';

        $data = [
            'model' => 'gpt-4o',
            'tools' => [
                ['type' => 'web_search_preview']
            ],
            'input' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.3
        ];

        $ch = curl_init($apiUrlResponses);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 90, // Más tiempo porque incluye búsqueda web
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Error de conexión: {$error}");
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            log_message('error', 'OpenAI Web Search Error: ' . json_encode($result));
            throw new \Exception($errorMsg);
        }

        // Extraer contenido de la respuesta del nuevo endpoint
        $contenido = '';
        if (isset($result['output'])) {
            foreach ($result['output'] as $item) {
                if ($item['type'] === 'message' && isset($item['content'])) {
                    foreach ($item['content'] as $content) {
                        if ($content['type'] === 'output_text') {
                            $contenido = trim($content['text']);
                            break 2;
                        }
                    }
                }
            }
        }

        // Fallback al formato antiguo si no encuentra en el nuevo
        if (empty($contenido) && isset($result['choices'][0]['message']['content'])) {
            $contenido = trim($result['choices'][0]['message']['content']);
        }

        if (empty($contenido)) {
            throw new \Exception('No se recibió respuesta de la IA');
        }

        // Limpiar posibles marcadores de código
        $contenido = preg_replace('/^```json\s*/', '', $contenido);
        $contenido = preg_replace('/\s*```$/', '', $contenido);

        $norma = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'JSON Parse Error. Contenido: ' . $contenido);
            throw new \Exception('La IA no devolvió un JSON válido');
        }

        if (isset($norma['error'])) {
            throw new \Exception($norma['error']);
        }

        return $norma;
    }

    /**
     * Guardar norma desde IA
     */
    public function guardarDesdeIA()
    {
        $data = [
            'sector' => $this->request->getPost('sector') ?: 'General',
            'tema' => $this->request->getPost('tema'),
            'subtema' => $this->request->getPost('subtema'),
            'tipo_norma' => $this->request->getPost('tipo_norma'),
            'id_norma_legal' => $this->request->getPost('id_norma_legal'),
            'anio' => (int)$this->request->getPost('anio'),
            'descripcion_norma' => $this->request->getPost('descripcion_norma'),
            'autoridad_emisora' => $this->request->getPost('autoridad_emisora'),
            'referente_nacional' => $this->request->getPost('referente_nacional') ?: '',
            'referente_internacional' => $this->request->getPost('referente_internacional') ?: '',
            'articulos_aplicables' => $this->request->getPost('articulos_aplicables'),
            'parametros' => $this->request->getPost('parametros'),
            'notas_vigencia' => $this->request->getPost('notas_vigencia'),
            'estado' => 'activa'
        ];

        // Verificar si ya existe
        if ($this->model->existeNorma($data['tipo_norma'], $data['id_norma_legal'], $data['anio'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Esta norma ya existe en la matriz legal'
            ]);
        }

        $id = $this->model->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Norma agregada correctamente a la matriz legal',
            'id' => $id
        ]);
    }

    /**
     * Exportar a Excel/CSV
     */
    public function exportar()
    {
        $normas = $this->model->orderBy('anio', 'DESC')->findAll();

        $filename = 'matriz_legal_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($output, [
            'ID', 'Sector', 'Tema', 'Subtema', 'Tipo de Norma', 'No. Norma', 'Año',
            'Descripción', 'Autoridad', 'Ref. Nacional', 'Ref. Internacional',
            'Artículos Aplicables', 'Parámetros', 'Notas Vigencia', 'Estado'
        ], ';');

        foreach ($normas as $norma) {
            fputcsv($output, [
                $norma['id'],
                $norma['sector'],
                $norma['tema'],
                $norma['subtema'],
                $norma['tipo_norma'],
                $norma['id_norma_legal'],
                $norma['anio'],
                $norma['descripcion_norma'],
                $norma['autoridad_emisora'],
                $norma['referente_nacional'],
                $norma['referente_internacional'],
                $norma['articulos_aplicables'],
                $norma['parametros'],
                $norma['notas_vigencia'],
                $norma['estado']
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Descargar CSV de muestra
     */
    public function descargarMuestra()
    {
        $filename = 'matriz_legal_muestra.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($output, [
            'TEMA',
            'SUBTEMA',
            'TIPO DE NORMA',
            'ID NORMA LEGAL',
            'AÑO',
            'DESCRIPCIÓN DE LA NORMA LEGAL',
            'AUTORIDAD QUE LO EMITE',
            'REFERENTE NACIONAL',
            'REFERENTE INTERNACIONAL',
            'ARTÍCULOS APLICABLES',
            'PARÁMETROS',
            'NOTAS VIGENCIAS / OBSERVACIONES'
        ], ';');

        // Fila de ejemplo 1
        fputcsv($output, [
            'SISTEMA GENERAL DE RIESGOS LABORALES',
            'Accidente de Trabajo',
            'Resolución',
            '2851',
            '2015',
            'Por la cual se modifica el artículo 3 de la Resolución 156 de 2005',
            'Ministerio de Trabajo',
            'x',
            '',
            'Todos',
            'El empleador deberá notificar a la EPS y ARL sobre la ocurrencia del accidente de trabajo dentro de los 2 días hábiles siguientes.',
            'Vigente'
        ], ';');

        // Fila de ejemplo 2
        fputcsv($output, [
            'SISTEMA GENERAL DE RIESGOS LABORALES',
            'Sistema de Gestión SST',
            'Decreto',
            '1072',
            '2015',
            'Decreto Único Reglamentario del Sector Trabajo',
            'Ministerio de Trabajo',
            'x',
            '',
            'Libro 2, Parte 2, Título 4, Capítulo 6',
            'Establece las directrices para implementar el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST)',
            'Vigente - Norma principal del SG-SST'
        ], ';');

        // Fila de ejemplo 3
        fputcsv($output, [
            'SISTEMA GENERAL DE RIESGOS LABORALES',
            'Estándares Mínimos',
            'Resolución',
            '0312',
            '2019',
            'Por la cual se definen los Estándares Mínimos del Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST',
            'Ministerio de Trabajo',
            'x',
            '',
            'Todos',
            'Define los estándares mínimos según el número de trabajadores y nivel de riesgo',
            'Vigente'
        ], ';');

        fclose($output);
        exit;
    }
}
