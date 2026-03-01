<?php

namespace App\Controllers;

use App\Models\MatrizComunicacionModel;
use CodeIgniter\Controller;

class MatrizComunicacionController extends Controller
{
    protected MatrizComunicacionModel $model;
    protected string $apiKey;

    public function __construct()
    {
        $this->model = new MatrizComunicacionModel();
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    /**
     * Obtener id_cliente de la sesión
     */
    protected function getIdCliente(): ?int
    {
        $session = session();
        return $session->get('id_cliente') ?? $session->get('user_id') ?? null;
    }

    /**
     * Obtener datos del cliente para contexto IA
     */
    protected function getContextoCliente(int $idCliente): array
    {
        $db = \Config\Database::connect();

        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $contexto = $db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        return [
            'cliente' => $cliente,
            'contexto' => $contexto
        ];
    }

    // ==================== CRUD ====================

    /**
     * Vista principal - Lista de protocolos con DataTables
     */
    public function index()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return redirect()->to('/')->with('error', 'Debe seleccionar un cliente');
        }

        $data = [
            'titulo' => 'Matriz de Comunicación SST',
            'categorias' => MatrizComunicacionModel::$categorias,
            'tipos' => MatrizComunicacionModel::$tipos,
            'estados' => MatrizComunicacionModel::$estados,
            'estadisticas' => $this->model->getEstadisticas($idCliente),
            'id_cliente' => $idCliente
        ];

        return view('matriz_comunicacion/index', $data);
    }

    /**
     * API para DataTables (server-side processing)
     */
    public function datatable()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return $this->response->setJSON(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
        }

        $params = [
            'draw' => $this->request->getGet('draw'),
            'start' => $this->request->getGet('start') ?? 0,
            'length' => $this->request->getGet('length') ?? 10,
            'search' => $this->request->getGet('search') ?? [],
            'order' => $this->request->getGet('order') ?? [],
            'columns' => $this->request->getGet('columns') ?? []
        ];

        $result = $this->model->getProtocolosDataTables($idCliente, $params);

        return $this->response->setJSON([
            'draw' => intval($params['draw']),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data']
        ]);
    }

    /**
     * Ver detalle de un protocolo
     */
    public function ver(int $id)
    {
        $protocolo = $this->model->find($id);

        if (!$protocolo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Protocolo no encontrado'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'protocolo' => $protocolo
        ]);
    }

    /**
     * Guardar protocolo (crear o actualizar)
     */
    public function guardar()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no identificado']);
        }

        $rules = [
            'categoria' => 'required',
            'situacion_evento' => 'required|min_length[3]',
            'que_comunicar' => 'required|min_length[3]',
            'quien_comunica' => 'required',
            'a_quien_comunicar' => 'required',
            'mecanismo_canal' => 'required',
            'frecuencia_plazo' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'id_cliente' => $idCliente,
            'categoria' => $this->request->getPost('categoria'),
            'situacion_evento' => $this->request->getPost('situacion_evento'),
            'que_comunicar' => $this->request->getPost('que_comunicar'),
            'quien_comunica' => $this->request->getPost('quien_comunica'),
            'a_quien_comunicar' => $this->request->getPost('a_quien_comunicar'),
            'mecanismo_canal' => $this->request->getPost('mecanismo_canal'),
            'frecuencia_plazo' => $this->request->getPost('frecuencia_plazo'),
            'registro_evidencia' => $this->request->getPost('registro_evidencia'),
            'norma_aplicable' => $this->request->getPost('norma_aplicable'),
            'tipo' => $this->request->getPost('tipo') ?: 'interna',
            'estado' => $this->request->getPost('estado') ?: 'activo'
        ];

        $id = $this->request->getPost('id');

        if ($id) {
            $this->model->update($id, $data);
            $mensaje = 'Protocolo actualizado correctamente';
        } else {
            $id = $this->model->insert($data);
            $mensaje = 'Protocolo creado correctamente';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $mensaje,
            'id' => $id
        ]);
    }

    /**
     * Eliminar protocolo
     */
    public function eliminar(int $id)
    {
        $protocolo = $this->model->find($id);

        if (!$protocolo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Protocolo no encontrado'
            ]);
        }

        $this->model->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Protocolo eliminado correctamente'
        ]);
    }

    // ==================== CSV ====================

    /**
     * Vista de carga CSV
     */
    public function importarCSV()
    {
        $data = [
            'titulo' => 'Importar Matriz de Comunicación desde CSV',
            'categorias' => MatrizComunicacionModel::$categorias
        ];

        return view('matriz_comunicacion/importar_csv', $data);
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
     * Procesar importación CSV
     */
    public function procesarCSV()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no identificado']);
        }

        $file = $this->request->getFile('archivo_csv');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibió un archivo válido'
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

            if (!empty($registro['categoria']) || !empty($registro['situacion_evento'])) {
                $datos[] = $registro;
            }
        }

        $resultado = $this->model->importarCSV($idCliente, $datos);

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
            'categoria' => ['categoria', 'categoría', 'category'],
            'situacion_evento' => ['situacion', 'situación', 'evento', 'situacion_evento', 'situacion/evento'],
            'que_comunicar' => ['que comunicar', 'que_comunicar', 'qué comunicar', 'comunicar'],
            'quien_comunica' => ['quien comunica', 'quien_comunica', 'quién comunica', 'emisor'],
            'a_quien_comunicar' => ['a quien comunicar', 'a_quien_comunicar', 'a quién', 'destinatario', 'receptor'],
            'mecanismo_canal' => ['mecanismo', 'canal', 'mecanismo_canal', 'mecanismo/canal', 'medio'],
            'frecuencia_plazo' => ['frecuencia', 'plazo', 'frecuencia_plazo', 'frecuencia/plazo', 'cuando'],
            'registro_evidencia' => ['registro', 'evidencia', 'registro_evidencia', 'registro/evidencia'],
            'norma_aplicable' => ['norma', 'norma_aplicable', 'norma aplicable', 'legislacion'],
            'tipo' => ['tipo', 'tipo comunicacion', 'interna/externa']
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

    /**
     * Exportar a CSV
     */
    public function exportar()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return redirect()->to('/matriz-comunicacion')->with('error', 'Cliente no identificado');
        }

        $protocolos = $this->model->where('id_cliente', $idCliente)
            ->orderBy('categoria', 'ASC')
            ->findAll();

        $filename = 'matriz_comunicacion_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID', 'Categoría', 'Situación/Evento', 'Qué Comunicar', 'Quién Comunica',
            'A Quién Comunicar', 'Mecanismo/Canal', 'Frecuencia/Plazo',
            'Registro/Evidencia', 'Norma Aplicable', 'Tipo', 'Estado'
        ], ';');

        foreach ($protocolos as $p) {
            fputcsv($output, [
                $p['id'],
                $p['categoria'],
                $p['situacion_evento'],
                $p['que_comunicar'],
                $p['quien_comunica'],
                $p['a_quien_comunicar'],
                $p['mecanismo_canal'],
                $p['frecuencia_plazo'],
                $p['registro_evidencia'],
                $p['norma_aplicable'],
                $p['tipo'],
                $p['estado']
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
        $filename = 'matriz_comunicacion_muestra.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'CATEGORÍA', 'SITUACIÓN/EVENTO', 'QUÉ COMUNICAR', 'QUIÉN COMUNICA',
            'A QUIÉN COMUNICAR', 'MECANISMO/CANAL', 'FRECUENCIA/PLAZO',
            'REGISTRO/EVIDENCIA', 'NORMA APLICABLE', 'TIPO'
        ], ';');

        fputcsv($output, [
            'Accidentes de Trabajo',
            'Accidente de trabajo grave o mortal',
            'Ocurrencia del accidente, datos del trabajador, descripción de hechos',
            'Jefe inmediato / Responsable SST',
            'ARL, EPS, MinTrabajo, COPASST',
            'Teléfono + FURAT + Correo electrónico',
            'Inmediato (máximo 2 días hábiles)',
            'FURAT, Acta de investigación AT',
            'Decreto 1072/2015 Art. 2.2.4.1.6, Res. 1401/2007',
            'ambas'
        ], ';');

        fputcsv($output, [
            'Convivencia Laboral',
            'Presunto caso de acoso laboral o sexual',
            'Reporte formal con descripción de hechos',
            'Trabajador afectado / Testigo',
            'Comité de Convivencia Laboral / Alta Dirección',
            'Escrito confidencial / Buzón de denuncias',
            'Inmediato (dentro de 24 horas)',
            'Formato de queja confidencial, Acta de recepción',
            'Ley 1010/2006, Ley 1257/2008, Res. 2646/2008',
            'interna'
        ], ';');

        fputcsv($output, [
            'Emergencias',
            'Evento de emergencia (incendio, sismo, evacuación)',
            'Activación del plan de emergencia, instrucciones de evacuación',
            'Brigada de Emergencias / Coordinador',
            'Todos los trabajadores, Bomberos, Defensa Civil',
            'Alarma sonora + Megáfono + WhatsApp grupal',
            'Inmediato',
            'Bitácora de emergencias, Informe post-evento',
            'Decreto 1072/2015 Art. 2.2.4.6.25, Ley 1523/2012',
            'ambas'
        ], ';');

        fclose($output);
        exit;
    }

    // ==================== MÓDULO IA ====================

    /**
     * Vista del módulo IA para generar protocolos
     */
    public function generarIA()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return redirect()->to('/')->with('error', 'Debe seleccionar un cliente');
        }

        $ctx = $this->getContextoCliente($idCliente);

        $data = [
            'titulo' => 'Generar Matriz de Comunicación con IA',
            'categorias' => MatrizComunicacionModel::$categorias,
            'tipos' => MatrizComunicacionModel::$tipos,
            'id_cliente' => $idCliente,
            'cliente' => $ctx['cliente'],
            'contexto' => $ctx['contexto'],
            'estadisticas' => $this->model->getEstadisticas($idCliente)
        ];

        return view('matriz_comunicacion/generar_ia', $data);
    }

    /**
     * Modo 2: Generar protocolo individual con IA
     */
    public function procesarGeneracionIA()
    {
        $consulta = $this->request->getPost('consulta');
        $idCliente = $this->getIdCliente();

        if (empty($consulta)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe ingresar una situación o escenario'
            ]);
        }

        if (empty($this->apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API Key de OpenAI no configurada'
            ]);
        }

        try {
            $ctx = $idCliente ? $this->getContextoCliente($idCliente) : ['cliente' => null, 'contexto' => null];
            $protocolos = $this->generarProtocolosConIA($consulta, $ctx, 'individual');

            return $this->response->setJSON([
                'success' => true,
                'protocolos' => $protocolos
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al consultar IA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Modo 1: Generar matriz completa con IA (bulk)
     */
    public function generarBulkIA()
    {
        $idCliente = $this->getIdCliente();

        if (!$idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe seleccionar un cliente'
            ]);
        }

        if (empty($this->apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API Key de OpenAI no configurada'
            ]);
        }

        try {
            $ctx = $this->getContextoCliente($idCliente);
            $protocolos = $this->generarProtocolosConIA('', $ctx, 'bulk');

            return $this->response->setJSON([
                'success' => true,
                'protocolos' => $protocolos,
                'total' => count($protocolos)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar matriz: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar protocolos de comunicación usando OpenAI
     */
    protected function generarProtocolosConIA(string $consulta, array $ctx, string $modo): array
    {
        $cliente = $ctx['cliente'];
        $contexto = $ctx['contexto'];

        $nombreEmpresa = $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'Empresa';
        $sector = $contexto['sector_economico'] ?? 'General';
        $riesgo = $contexto['nivel_riesgo_arl'] ?? 'II';
        $trabajadores = $contexto['total_trabajadores'] ?? '10-50';
        $peligros = $contexto['peligros_identificados'] ?? '';
        $comite = (!empty($contexto['tiene_copasst']) && $contexto['tiene_copasst'] === 'si') ? 'COPASST' : 'Vigía SST';
        $arl = $contexto['arl_actual'] ?? '';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) colombiano, especializado en protocolos de comunicación según el Decreto 1072 de 2015 y la Resolución 0312 de 2019.

Tu tarea es generar filas para una Matriz de Comunicación del SG-SST. Cada fila debe contener EXACTAMENTE estos campos en un objeto JSON:
- \"categoria\": Categoría del protocolo (Accidentes de Trabajo, Incidentes, Enfermedades Laborales, Emergencias, Convivencia Laboral, Peligros y Riesgos, Resultados de Auditoría, Cambios Normativos, Capacitaciones, COPASST / Comité de Convivencia, Comunicación Externa)
- \"situacion_evento\": Descripción específica del evento o situación
- \"que_comunicar\": Qué información se debe transmitir
- \"quien_comunica\": Rol o persona responsable de la comunicación
- \"a_quien_comunicar\": Destinatario(s)
- \"mecanismo_canal\": Medio o canal de comunicación
- \"frecuencia_plazo\": Frecuencia o plazo para comunicar
- \"registro_evidencia\": Cómo se evidencia la comunicación
- \"norma_aplicable\": Norma legal colombiana que sustenta la obligación
- \"tipo\": \"interna\", \"externa\" o \"ambas\"

IMPORTANTE:
- Responde SOLO con un JSON válido: un array de objetos. Sin texto adicional.
- Basa todo en legislación colombiana real y vigente.
- Incluye números de artículos y años de las normas citadas.";

        if ($modo === 'bulk') {
            $userPrompt = "Genera una matriz de comunicación SST COMPLETA para la siguiente empresa colombiana:

CONTEXTO DE LA EMPRESA:
- Empresa: {$nombreEmpresa}
- Sector económico: {$sector}
- Nivel de riesgo ARL: {$riesgo}
- Total trabajadores: {$trabajadores}
- Peligros identificados: {$peligros}
- Comité: {$comite}
- ARL: {$arl}

CATEGORÍAS OBLIGATORIAS a cubrir (genera mínimo 2 protocolos por categoría):
1. Accidentes de Trabajo (AT leve, AT grave, AT mortal)
2. Incidentes (casi-accidentes, condiciones sub-estándar)
3. Enfermedades Laborales (presuntiva, calificada, recomendaciones médicas)
4. Emergencias (incendio, sismo, evacuación, derrame químico si aplica)
5. Convivencia Laboral (acoso laboral, acoso sexual, conflictos)
6. Peligros y Riesgos (identificación, reporte condiciones inseguras, actos inseguros)
7. Resultados de Auditoría (hallazgos, no conformidades, planes de mejora)
8. Cambios Normativos (nuevas leyes, resoluciones, decretos)
9. Capacitaciones (programación, resultados, evaluaciones)
10. {$comite} / Comité de Convivencia (actas, reuniones, recomendaciones)
11. Comunicación Externa (ARL, EPS, MinTrabajo, Secretaría de Salud)

Genera entre 25 y 35 protocolos que cubran TODAS las categorías.
Adapta los protocolos al sector {$sector} y nivel de riesgo {$riesgo}.
Usa '{$comite}' donde corresponda (no uses COPASST si la empresa tiene Vigía).";
        } else {
            $userPrompt = "Para la empresa {$nombreEmpresa} (sector: {$sector}, riesgo ARL: {$riesgo}, comité: {$comite}), genera los protocolos de comunicación SST para la siguiente situación:

\"{$consulta}\"

Genera entre 1 y 4 protocolos específicos para esta situación.
Incluye la norma colombiana aplicable con artículos específicos.
Adapta al contexto del sector {$sector}.";
        }

        $apiUrl = 'https://api.openai.com/v1/responses';

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

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => $modo === 'bulk' ? 120 : 90,
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
            log_message('error', 'OpenAI Matriz Comunicación Error: ' . json_encode($result));
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
            throw new \Exception('No se recibió respuesta de la IA');
        }

        // Limpiar marcadores de código
        $contenido = preg_replace('/^```json\s*/', '', $contenido);
        $contenido = preg_replace('/\s*```$/', '', $contenido);

        $protocolos = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'JSON Parse Error Matriz Comunicación. Contenido: ' . $contenido);
            throw new \Exception('La IA no devolvió un JSON válido');
        }

        // Si devolvió un objeto en vez de array, envolver
        if (isset($protocolos['categoria'])) {
            $protocolos = [$protocolos];
        }

        return $protocolos;
    }

    /**
     * Guardar protocolo(s) desde IA
     */
    public function guardarDesdeIA()
    {
        $idCliente = $this->getIdCliente();
        if (!$idCliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no identificado']);
        }

        $protocolosJson = $this->request->getPost('protocolos');

        if ($protocolosJson) {
            // Bulk save: recibe array JSON de protocolos
            $protocolos = json_decode($protocolosJson, true);

            if (!is_array($protocolos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos de protocolos inválidos'
                ]);
            }

            $resultado = $this->model->importarDesdeIA($idCliente, $protocolos);

            return $this->response->setJSON([
                'success' => $resultado['exito'],
                'message' => "Se guardaron {$resultado['insertados']} protocolos correctamente",
                'insertados' => $resultado['insertados'],
                'errores' => $resultado['errores']
            ]);
        }

        // Single save: recibe campos individuales por POST
        $data = [
            'id_cliente' => $idCliente,
            'categoria' => $this->request->getPost('categoria'),
            'situacion_evento' => $this->request->getPost('situacion_evento'),
            'que_comunicar' => $this->request->getPost('que_comunicar'),
            'quien_comunica' => $this->request->getPost('quien_comunica'),
            'a_quien_comunicar' => $this->request->getPost('a_quien_comunicar'),
            'mecanismo_canal' => $this->request->getPost('mecanismo_canal'),
            'frecuencia_plazo' => $this->request->getPost('frecuencia_plazo'),
            'registro_evidencia' => $this->request->getPost('registro_evidencia'),
            'norma_aplicable' => $this->request->getPost('norma_aplicable'),
            'tipo' => $this->request->getPost('tipo') ?: 'interna',
            'estado' => 'activo',
            'generado_por_ia' => 1
        ];

        // Verificar duplicado
        if ($this->model->existeProtocolo($idCliente, $data['categoria'], $data['situacion_evento'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe un protocolo similar para esta situación'
            ]);
        }

        $id = $this->model->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Protocolo agregado correctamente a la matriz',
            'id' => $id
        ]);
    }
}
