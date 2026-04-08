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
     * Vista principal - Lista de normas con DataTables y cards
     */
    public function index()
    {
        $data = [
            'titulo' => 'Matriz Legal SST',
            'categorias' => MatrizLegalModel::$categorias,
            'categoriasConConteo' => $this->model->getCategoriasConConteo(),
            'tiposNorma' => MatrizLegalModel::$tiposNorma,
            'estados' => MatrizLegalModel::$estados,
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
            'columns' => $this->request->getGet('columns') ?? [],
            'categoria' => $this->request->getGet('categoria') ?? '',
            'clasificacion_filtro' => $this->request->getGet('clasificacion_filtro') ?? '',
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
     * API para obtener clasificaciones de una categoria
     */
    public function clasificaciones(string $categoria)
    {
        $categoria = urldecode($categoria);
        $clasificaciones = $this->model->getClasificacionesPorCategoria($categoria);

        return $this->response->setJSON([
            'success' => true,
            'clasificaciones' => $clasificaciones
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
            'categorias' => MatrizLegalModel::$categorias,
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
            'categoria' => $this->request->getPost('categoria') ?: 'Seguridad e Higiene Industrial',
            'clasificacion' => $this->request->getPost('clasificacion'),
            'tema' => $this->request->getPost('tema'),
            'subtema' => $this->request->getPost('subtema'),
            'tipo_norma' => $this->request->getPost('tipo_norma'),
            'id_norma_legal' => $this->request->getPost('id_norma_legal'),
            'anio' => $this->request->getPost('anio'),
            'fecha_expedicion' => $this->request->getPost('fecha_expedicion') ?: null,
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
            'categorias' => MatrizLegalModel::$categorias,
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
            'categorias' => MatrizLegalModel::$categorias
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
                'message' => 'No se recibio un archivo valido'
            ]);
        }

        $extension = $file->getExtension();
        if (!in_array($extension, ['csv', 'txt'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El archivo debe ser CSV o TXT'
            ]);
        }

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
        $contador = 0;
        foreach ($lineas as $linea) {
            if (empty(trim($linea))) continue;
            if ($contador >= 10) break;

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
     * Procesar importacion CSV
     */
    public function procesarCSV()
    {
        $file = $this->request->getFile('archivo_csv');
        $categoriaDefecto = $this->request->getPost('categoria_defecto') ?: 'Seguridad e Higiene Industrial';

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibio un archivo valido'
            ]);
        }

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

            if (!empty($registro['tema']) || !empty($registro['tipo_norma']) || !empty($registro['id_norma_legal'])) {
                $datos[] = $registro;
            }
        }

        $resultado = $this->model->importarCSV($datos, $categoriaDefecto);

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
            'categoria' => ['categoria', 'categoría'],
            'clasificacion' => ['clasificacion', 'clasificación'],
            'tema' => ['tema'],
            'subtema' => ['subtema'],
            'tipo_norma' => ['tipo de norma', 'tipo_norma', 'tipo norma'],
            'id_norma_legal' => ['id norma legal', 'id_norma_legal', 'numero', 'número', 'norma'],
            'anio' => ['año', 'anio', 'ano'],
            'fecha_expedicion' => ['fecha', 'fecha_expedicion', 'fecha expedicion'],
            'descripcion_norma' => ['descripción de la norma legal', 'descripcion_norma', 'descripción', 'descripcion', 'tematica', 'temática'],
            'autoridad_emisora' => ['autoridad que lo emite', 'autoridad_emisora', 'autoridad', 'entidad', 'expedida por'],
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

    // ==================== MODULO IA ====================

    /**
     * Vista del modulo IA para buscar normas
     */
    public function buscarIA()
    {
        $data = [
            'titulo' => 'Buscar Norma con IA',
            'categorias' => MatrizLegalModel::$categorias,
            'tiposNorma' => MatrizLegalModel::$tiposNorma
        ];

        return view('matriz_legal/buscar_ia', $data);
    }

    /**
     * Procesar busqueda con IA
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
     * Buscar informacion de norma usando OpenAI
     */
    protected function buscarNormaConIA(string $consulta): array
    {
        $categoriasLista = implode(', ', array_keys(MatrizLegalModel::$categorias));

        $systemPrompt = "Eres un experto en legislacion colombiana de Seguridad y Salud en el Trabajo (SST).
Tu tarea es buscar y proporcionar informacion detallada sobre normas legales colombianas.

Cuando el usuario mencione una norma (ej: 'Resolucion 0312 de 2019', 'Decreto 1072 de 2015'),
debes responder UNICAMENTE con un JSON valido con la siguiente estructura:

{
    \"categoria\": \"Una de estas categorias: {$categoriasLista}\",
    \"clasificacion\": \"Sub-agrupacion tematica dentro de la categoria (ej: EVALUACIONES MEDICAS, ACCIDENTES, etc.)\",
    \"tema\": \"Tema principal de la norma (ej: Sistema General de Riesgos Laborales)\",
    \"subtema\": \"Subtema especifico si aplica\",
    \"tipo_norma\": \"Tipo de norma (Ley, Decreto, Resolucion, Circular, etc.)\",
    \"id_norma_legal\": \"Numero de la norma (ej: 0312, 1072, 1562)\",
    \"anio\": Anio como numero (ej: 2019),
    \"fecha_expedicion\": \"Fecha en formato YYYY-MM-DD si se conoce\",
    \"descripcion_norma\": \"Descripcion completa de que establece la norma (maximo 500 caracteres)\",
    \"autoridad_emisora\": \"Entidad que expide la norma (ej: Ministerio del Trabajo)\",
    \"referente_nacional\": \"x si es referente nacional, vacio si no\",
    \"referente_internacional\": \"x si tiene referente internacional, vacio si no\",
    \"articulos_aplicables\": \"Articulos mas relevantes para SST\",
    \"parametros\": \"Parametros o requisitos principales que establece la norma\",
    \"notas_vigencia\": \"Estado de vigencia, modificaciones o derogatorias\"
}

IMPORTANTE:
- Responde SOLO con el JSON, sin texto adicional
- La categoria debe ser una de las listadas arriba
- La clasificacion es una sub-agrupacion en MAYUSCULAS (ej: COPASST, RIESGO PSICOSOCIAL, ACCIDENTES)
- Si no encuentras informacion sobre la norma, responde con un JSON con campo 'error'
- Asegurate de que el anio sea un numero, no texto
- La informacion debe ser precisa y basada en la legislacion colombiana real";

        $userPrompt = "Busca en internet informacion actualizada sobre la siguiente norma colombiana y devuelve los datos en formato JSON:\n\n{$consulta}";

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
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Error de conexion: {$error}");
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            log_message('error', 'OpenAI Web Search Error: ' . json_encode($result));
            throw new \Exception($errorMsg);
        }

        // Extraer contenido de la respuesta
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

        // Fallback al formato antiguo
        if (empty($contenido) && isset($result['choices'][0]['message']['content'])) {
            $contenido = trim($result['choices'][0]['message']['content']);
        }

        if (empty($contenido)) {
            throw new \Exception('No se recibio respuesta de la IA');
        }

        // Limpiar marcadores de codigo
        $contenido = preg_replace('/^```json\s*/', '', $contenido);
        $contenido = preg_replace('/\s*```$/', '', $contenido);

        $norma = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'JSON Parse Error. Contenido: ' . $contenido);
            throw new \Exception('La IA no devolvio un JSON valido');
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
            'categoria' => $this->request->getPost('categoria') ?: 'Seguridad e Higiene Industrial',
            'clasificacion' => $this->request->getPost('clasificacion'),
            'tema' => $this->request->getPost('tema'),
            'subtema' => $this->request->getPost('subtema'),
            'tipo_norma' => $this->request->getPost('tipo_norma'),
            'id_norma_legal' => $this->request->getPost('id_norma_legal'),
            'anio' => (int)$this->request->getPost('anio'),
            'fecha_expedicion' => $this->request->getPost('fecha_expedicion') ?: null,
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
     * Exportar a CSV
     */
    public function exportar()
    {
        $normas = $this->model->orderBy('categoria', 'ASC')->orderBy('anio', 'DESC')->findAll();

        $filename = 'matriz_legal_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID', 'Categoria', 'Clasificacion', 'Tema', 'Subtema', 'Tipo de Norma', 'No. Norma', 'Ano',
            'Fecha Expedicion', 'Descripcion', 'Autoridad', 'Ref. Nacional', 'Ref. Internacional',
            'Articulos Aplicables', 'Parametros', 'Notas Vigencia', 'Estado'
        ], ';');

        foreach ($normas as $norma) {
            fputcsv($output, [
                $norma['id'],
                $norma['categoria'],
                $norma['clasificacion'],
                $norma['tema'],
                $norma['subtema'],
                $norma['tipo_norma'],
                $norma['id_norma_legal'],
                $norma['anio'],
                $norma['fecha_expedicion'],
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
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'CATEGORIA', 'CLASIFICACION', 'TEMA', 'SUBTEMA', 'TIPO DE NORMA',
            'ID NORMA LEGAL', 'AÑO', 'FECHA EXPEDICION',
            'DESCRIPCION DE LA NORMA LEGAL', 'AUTORIDAD QUE LO EMITE',
            'REFERENTE NACIONAL', 'REFERENTE INTERNACIONAL',
            'ARTICULOS APLICABLES', 'PARAMETROS', 'NOTAS VIGENCIAS / OBSERVACIONES'
        ], ';');

        fputcsv($output, [
            'Seguridad e Higiene Industrial', 'ACCIDENTES',
            'SISTEMA GENERAL DE RIESGOS LABORALES', 'Accidente de Trabajo',
            'Resolucion', '2851', '2015', '2015-07-28',
            'Por la cual se modifica el articulo 3 de la Resolucion 156 de 2005',
            'Ministerio de Trabajo', 'x', '', 'Todos',
            'El empleador debera notificar a la EPS y ARL sobre la ocurrencia del accidente.',
            'Vigente'
        ], ';');

        fclose($output);
        exit;
    }
}
