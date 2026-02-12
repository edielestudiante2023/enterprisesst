<?php

namespace App\Controllers;

use App\Models\IndicadorSSTModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;

/**
 * Controlador para gestionar indicadores del SG-SST
 */
class IndicadoresSSTController extends BaseController
{
    protected IndicadorSSTModel $indicadorModel;
    protected ClientModel $clienteModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Lista indicadores de un cliente
     */
    public function index(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Auto-seed: asegurar indicadores legales existan
        $this->asegurarIndicadoresLegales($idCliente);

        // Obtener contexto SST para saber estándares aplicables
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Filtro de categoría desde GET
        $categoriaFiltro = $this->request->getGet('categoria');

        // Obtener indicadores agrupados por categoría
        $indicadoresPorCategoria = $this->indicadorModel->getByClienteAgrupadosPorCategoria($idCliente);

        // Resumen por categoría (solo las que tienen indicadores)
        $resumenCategorias = $this->indicadorModel->getResumenPorCategoria($idCliente);

        // Verificar cumplimiento global
        $verificacion = $this->indicadorModel->verificarCumplimiento($idCliente);

        // Obtener sugerencias
        $sugerencias = $this->indicadorModel->generarIndicadoresSugeridos($idCliente, $estandares);

        $data = [
            'titulo' => 'Indicadores del SG-SST',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'indicadoresPorCategoria' => $indicadoresPorCategoria,
            'resumenCategorias' => $resumenCategorias,
            'categorias' => IndicadorSSTModel::CATEGORIAS,
            'categoriaFiltro' => $categoriaFiltro,
            'verificacion' => $verificacion,
            'sugerencias' => $sugerencias,
            'tiposIndicador' => IndicadorSSTModel::TIPOS_INDICADOR,
            'periodicidades' => IndicadorSSTModel::PERIODICIDADES,
            'fasesPhva' => IndicadorSSTModel::FASES_PHVA
        ];

        return view('indicadores_sst/index', $data);
    }

    /**
     * Formulario para crear nuevo indicador
     */
    public function crear(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Categoría preseleccionada desde GET
        $categoriaPreseleccionada = $this->request->getGet('categoria');

        $data = [
            'titulo' => 'Agregar Indicador',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'tiposIndicador' => IndicadorSSTModel::TIPOS_INDICADOR,
            'periodicidades' => IndicadorSSTModel::PERIODICIDADES,
            'fasesPhva' => IndicadorSSTModel::FASES_PHVA,
            'categorias' => IndicadorSSTModel::CATEGORIAS,
            'categoriaPreseleccionada' => $categoriaPreseleccionada,
            'indicador' => null
        ];

        return view('indicadores_sst/formulario', $data);
    }

    /**
     * Formulario para editar indicador
     */
    public function editar(int $idCliente, int $idIndicador)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $indicador = $this->indicadorModel->find($idIndicador);
        if (!$indicador || $indicador['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Indicador no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Obtener histórico de mediciones
        $historico = $this->indicadorModel->getHistoricoMediciones($idIndicador);

        $data = [
            'titulo' => 'Editar Indicador',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'tiposIndicador' => IndicadorSSTModel::TIPOS_INDICADOR,
            'periodicidades' => IndicadorSSTModel::PERIODICIDADES,
            'fasesPhva' => IndicadorSSTModel::FASES_PHVA,
            'categorias' => IndicadorSSTModel::CATEGORIAS,
            'categoriaPreseleccionada' => null,
            'indicador' => $indicador,
            'historico' => $historico
        ];

        return view('indicadores_sst/formulario', $data);
    }

    /**
     * Guarda indicador (crear o actualizar)
     */
    public function guardar(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $idIndicador = $this->request->getPost('id_indicador');

        $datos = [
            'id_cliente' => $idCliente,
            'nombre_indicador' => $this->request->getPost('nombre_indicador'),
            'tipo_indicador' => $this->request->getPost('tipo_indicador') ?? 'proceso',
            'categoria' => $this->request->getPost('categoria') ?? 'otro',
            'formula' => $this->request->getPost('formula'),
            'meta' => $this->request->getPost('meta') ?: null,
            'unidad_medida' => $this->request->getPost('unidad_medida') ?? '%',
            'periodicidad' => $this->request->getPost('periodicidad') ?? 'trimestral',
            'numeral_resolucion' => $this->request->getPost('numeral_resolucion'),
            'phva' => $this->request->getPost('phva') ?? 'verificar',
            'observaciones' => $this->request->getPost('observaciones'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        // Validación básica
        if (empty($datos['nombre_indicador'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El nombre del indicador es obligatorio'
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'El nombre del indicador es obligatorio');
        }

        try {
            if ($idIndicador) {
                $this->indicadorModel->update($idIndicador, $datos);
                $mensaje = 'Indicador actualizado correctamente';
            } else {
                $this->indicadorModel->insert($datos);
                $mensaje = 'Indicador agregado correctamente';
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => $mensaje]);
            }

            return redirect()->to("indicadores-sst/{$idCliente}")->with('success', $mensaje);

        } catch (\Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $error]);
            }
            return redirect()->back()->withInput()->with('error', $error);
        }
    }

    /**
     * Registra una medición
     */
    public function registrarMedicion(int $idCliente, int $idIndicador)
    {
        $indicador = $this->indicadorModel->find($idIndicador);

        if (!$indicador || $indicador['id_cliente'] != $idCliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Indicador no encontrado']);
        }

        $datos = [
            'valor_numerador' => $this->request->getPost('valor_numerador'),
            'valor_denominador' => $this->request->getPost('valor_denominador'),
            'fecha_medicion' => $this->request->getPost('fecha_medicion') ?? date('Y-m-d'),
            'periodo' => $this->request->getPost('periodo') ?? date('Y-m'),
            'observaciones' => $this->request->getPost('observaciones'),
            'registrado_por' => session()->get('id_usuario')
        ];

        try {
            $this->indicadorModel->registrarMedicion($idIndicador, $datos);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Medición registrada correctamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina un indicador
     */
    public function eliminar(int $idCliente, int $idIndicador)
    {
        $indicador = $this->indicadorModel->find($idIndicador);

        if (!$indicador || $indicador['id_cliente'] != $idCliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Indicador no encontrado']);
            }
            return redirect()->back()->with('error', 'Indicador no encontrado');
        }

        try {
            $this->indicadorModel->delete($idIndicador);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Indicador eliminado']);
            }

            return redirect()->to("indicadores-sst/{$idCliente}")->with('success', 'Indicador eliminado');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Genera indicadores sugeridos con IA
     */
    public function generarSugeridos(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        try {
            $creados = $this->indicadorModel->crearIndicadoresSugeridos($idCliente, $estandares);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Se crearon {$creados} indicadores sugeridos",
                'creados' => $creados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Obtiene indicadores para documentos
     */
    public function apiObtener(int $idCliente)
    {
        $indicadores = $this->indicadorModel->getByCliente($idCliente);

        return $this->response->setJSON([
            'success' => true,
            'data' => $indicadores
        ]);
    }

    /**
     * API: Verifica cumplimiento de indicadores
     */
    public function apiVerificar(int $idCliente)
    {
        $verificacion = $this->indicadorModel->verificarCumplimiento($idCliente);

        return $this->response->setJSON([
            'success' => true,
            'data' => $verificacion
        ]);
    }

    /**
     * API: Obtiene histórico de mediciones
     */
    public function apiHistorico(int $idIndicador)
    {
        $historico = $this->indicadorModel->getHistoricoMediciones($idIndicador);

        return $this->response->setJSON([
            'success' => true,
            'data' => $historico
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // DASHBOARD JERÁRQUICO (ZZ_94)
    // ─────────────────────────────────────────────────────────

    /**
     * Dashboard jerárquico de indicadores SST
     */
    public function dashboard(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Auto-seed: asegurar indicadores legales existan
        $this->asegurarIndicadoresLegales($idCliente);

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $dashboardData = $this->indicadorModel->getDashboardData($idCliente);
        $minimos = $this->indicadorModel->getMinimosObligatorios($idCliente);

        $data = [
            'titulo' => 'Dashboard Indicadores SG-SST',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'dashboard' => $dashboardData,
            'minimos' => $minimos,
            'categorias' => IndicadorSSTModel::CATEGORIAS,
            'coloresTipo' => IndicadorSSTModel::COLORES_TIPO,
            'pesosTipo' => IndicadorSSTModel::PESOS_TIPO,
        ];

        return view('indicadores_sst/dashboard', $data);
    }

    /**
     * API: Datos JSON del dashboard jerárquico
     */
    public function apiDashboard(int $idCliente)
    {
        $dashboardData = $this->indicadorModel->getDashboardData($idCliente);
        $minimos = $this->indicadorModel->getMinimosObligatorios($idCliente);

        // Agregar indicadores individuales por categoría para Nivel 4
        $indicadoresPorCategoria = $this->indicadorModel->getByClienteAgrupadosPorCategoria($idCliente);

        // Incluir histórico para sparklines
        foreach ($indicadoresPorCategoria as $cat => &$inds) {
            foreach ($inds as &$ind) {
                $ind['historico'] = $this->indicadorModel->getHistoricoMediciones($ind['id_indicador'], 6);
            }
            unset($ind);
        }
        unset($inds);

        return $this->response->setJSON([
            'success' => true,
            'data' => array_merge($dashboardData, [
                'minimos' => $minimos,
                'indicadores_por_categoria' => $indicadoresPorCategoria,
                'categorias_config' => IndicadorSSTModel::CATEGORIAS
            ])
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // FICHAS TÉCNICAS DE INDICADORES (ZZ_99)
    // ─────────────────────────────────────────────────────────

    /**
     * Vista web de la Ficha Técnica de un indicador
     */
    public function fichaTecnica(int $idCliente, int $idIndicador)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)($this->request->getGet('anio') ?? date('Y'));
        $datosFicha = $this->indicadorModel->getDatosFichaTecnica($idIndicador, $anio);

        if (!$datosFicha || $datosFicha['indicador']['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Indicador no encontrado');
        }

        // Contexto SST del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        // Consultor
        $session = session();
        $consultorModel = new \App\Models\ConsultantModel();
        $consultor = $consultorModel->find($session->get('user_id'));

        // Consecutivo del indicador (posición entre los activos del cliente)
        $todosIndicadores = $this->indicadorModel->getByCliente($idCliente);
        $consecutivo = 1;
        foreach ($todosIndicadores as $idx => $ind) {
            if ($ind['id_indicador'] == $idIndicador) {
                $consecutivo = $idx + 1;
                break;
            }
        }

        // Representante legal nombre (fallback chain)
        $repLegalNombre = $contexto['representante_legal_nombre']
            ?? $cliente['nombre_rep_legal']
            ?? $cliente['representante_legal']
            ?? '';

        return view('indicadores_sst/ficha_tecnica', array_merge($datosFicha, [
            'cliente'          => $cliente,
            'contexto'         => $contexto,
            'consultor'        => $consultor,
            'consecutivo'      => $consecutivo,
            'repLegalNombre'   => $repLegalNombre,
        ]));
    }

    /**
     * Exportar Ficha Técnica en PDF
     */
    public function fichaTecnicaPDF(int $idCliente, int $idIndicador)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)($this->request->getGet('anio') ?? date('Y'));
        $datosFicha = $this->indicadorModel->getDatosFichaTecnica($idIndicador, $anio);

        if (!$datosFicha || $datosFicha['indicador']['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Indicador no encontrado');
        }

        // Contexto y consultor
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $session = session();
        $consultorModel = new \App\Models\ConsultantModel();
        $consultor = $consultorModel->find($session->get('user_id'));

        // Logo base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Firma consultor base64
        $firmaConsultorBase64 = '';
        if (!empty($consultor['firma_consultor'])) {
            $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
            if (file_exists($firmaPath)) {
                $firmaMime = mime_content_type($firmaPath);
                $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode(file_get_contents($firmaPath));
            }
        }

        // Consecutivo
        $todosIndicadores = $this->indicadorModel->getByCliente($idCliente);
        $consecutivo = 1;
        foreach ($todosIndicadores as $idx => $ind) {
            if ($ind['id_indicador'] == $idIndicador) {
                $consecutivo = $idx + 1;
                break;
            }
        }

        // Orientación según periodicidad
        $orientacion = ($datosFicha['indicador']['periodicidad'] === 'mensual') ? 'landscape' : 'portrait';

        // Chart base64 (se recibe como query param si fue generado en JS)
        $chartBase64 = $this->request->getGet('chart') ?? '';

        // Representante legal nombre (fallback chain)
        $repLegalNombre = $contexto['representante_legal_nombre']
            ?? $cliente['nombre_rep_legal']
            ?? $cliente['representante_legal']
            ?? '';

        $html = view('indicadores_sst/ficha_tecnica_pdf', array_merge($datosFicha, [
            'cliente'              => $cliente,
            'contexto'             => $contexto,
            'consultor'            => $consultor,
            'consecutivo'          => $consecutivo,
            'logoBase64'           => $logoBase64,
            'firmaConsultorBase64' => $firmaConsultorBase64,
            'orientacion'          => $orientacion,
            'chartBase64'          => $chartBase64,
            'repLegalNombre'       => $repLegalNombre,
        ]));

        // Generar PDF con DomPDF
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', $orientacion);
        $dompdf->render();

        $codigo = 'FT-IND-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
        $filename = $codigo . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $datosFicha['indicador']['nombre_indicador']) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($dompdf->output());
    }

    /**
     * Exportar Ficha Técnica en Word
     */
    public function fichaTecnicaWord(int $idCliente, int $idIndicador)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)($this->request->getGet('anio') ?? date('Y'));
        $datosFicha = $this->indicadorModel->getDatosFichaTecnica($idIndicador, $anio);

        if (!$datosFicha || $datosFicha['indicador']['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Indicador no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $session = session();
        $consultorModel = new \App\Models\ConsultantModel();
        $consultor = $consultorModel->find($session->get('user_id'));

        // Logo base64 con fondo blanco para Word
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->convertirImagenConFondoBlanco($logoPath);
            }
        }

        $todosIndicadores = $this->indicadorModel->getByCliente($idCliente);
        $consecutivo = 1;
        foreach ($todosIndicadores as $idx => $ind) {
            if ($ind['id_indicador'] == $idIndicador) {
                $consecutivo = $idx + 1;
                break;
            }
        }

        // Representante legal nombre (fallback chain)
        $repLegalNombre = $contexto['representante_legal_nombre']
            ?? $cliente['nombre_rep_legal']
            ?? $cliente['representante_legal']
            ?? '';

        $html = view('indicadores_sst/ficha_tecnica_word', array_merge($datosFicha, [
            'cliente'          => $cliente,
            'contexto'         => $contexto,
            'consultor'        => $consultor,
            'consecutivo'      => $consecutivo,
            'logoBase64'       => $logoBase64,
            'repLegalNombre'   => $repLegalNombre,
        ]));

        $codigo = 'FT-IND-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
        $filename = $codigo . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $datosFicha['indicador']['nombre_indicador']) . '.doc';

        return $this->response
            ->setHeader('Content-Type', 'application/msword')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($html);
    }

    /**
     * Vista web de la Matriz de Objetivos y Metas
     */
    public function matrizObjetivosMetas(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)($this->request->getGet('anio') ?? date('Y'));
        $indicadores = $this->indicadorModel->getByCliente($idCliente);

        // Para cada indicador, obtener resumen de mediciones
        foreach ($indicadores as &$ind) {
            $periodos = IndicadorSSTModel::getPeriodosParaPeriodicidad($ind['periodicidad'] ?? 'trimestral', $anio);
            $mediciones = $this->indicadorModel->getMedicionesAnio($ind['id_indicador'], $anio);

            $ind['periodos_data'] = [];
            foreach ($periodos as $p) {
                $m = $mediciones[$p['periodo']] ?? null;
                $ind['periodos_data'][] = [
                    'label'     => $p['label'],
                    'resultado' => $m['valor_resultado'] ?? null,
                    'cumple'    => $m['cumple_meta'] ?? null,
                ];
            }
        }
        unset($ind);

        return view('indicadores_sst/matriz_objetivos_metas', [
            'cliente'      => $cliente,
            'indicadores'  => $indicadores,
            'anio'         => $anio,
        ]);
    }

    /**
     * Exportar Matriz de Objetivos y Metas en PDF (landscape)
     */
    public function matrizObjetivosMetasPDF(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)($this->request->getGet('anio') ?? date('Y'));
        $indicadores = $this->indicadorModel->getByCliente($idCliente);

        foreach ($indicadores as &$ind) {
            $periodos = IndicadorSSTModel::getPeriodosParaPeriodicidad($ind['periodicidad'] ?? 'trimestral', $anio);
            $mediciones = $this->indicadorModel->getMedicionesAnio($ind['id_indicador'], $anio);

            $ind['periodos_data'] = [];
            foreach ($periodos as $p) {
                $m = $mediciones[$p['periodo']] ?? null;
                $ind['periodos_data'][] = [
                    'label'     => $p['label'],
                    'resultado' => $m['valor_resultado'] ?? null,
                    'cumple'    => $m['cumple_meta'] ?? null,
                ];
            }
        }
        unset($ind);

        // Logo base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $html = view('indicadores_sst/matriz_objetivos_metas_pdf', [
            'cliente'     => $cliente,
            'indicadores' => $indicadores,
            'anio'        => $anio,
            'logoBase64'  => $logoBase64,
        ]);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        $filename = 'Matriz_Objetivos_Metas_SST_' . $anio . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($dompdf->output());
    }

    // ─────────────────────────────────────────────────────────
    // GENERADOR IA DE INDICADORES (Grupos C+D)
    // ─────────────────────────────────────────────────────────

    /**
     * GET AJAX: Contexto completo + brechas para SweetAlert de verificacion
     */
    public function previsualizarContextoIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $service = new \App\Services\IndicadoresGeneralIAService();
            $contexto = $service->obtenerContextoCompleto($idCliente);

            return $this->response->setJSON([
                'success' => true,
                'data' => $contexto
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener contexto: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST AJAX: Genera sugerencias de indicadores con IA
     */
    public function previewIndicadoresIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $json = $this->request->getJSON(true);
        $instrucciones = $json['instrucciones'] ?? '';
        $categorias = $json['categorias'] ?? null;

        try {
            $service = new \App\Services\IndicadoresGeneralIAService();
            $resultado = $service->previewIndicadores($idCliente, $instrucciones, $categorias);

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar indicadores: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST AJAX: Guarda indicadores seleccionados por el consultor
     */
    public function guardarIndicadoresIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $json = $this->request->getJSON(true);
        $indicadores = $json['indicadores'] ?? [];

        if (empty($indicadores)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron indicadores para guardar'
            ]);
        }

        try {
            $service = new \App\Services\IndicadoresGeneralIAService();
            $resultado = $service->guardarIndicadoresSeleccionados($idCliente, $indicadores);

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultado,
                'message' => "Se crearon {$resultado['creados']} indicadores" .
                    ($resultado['existentes'] > 0 ? " ({$resultado['existentes']} ya existian)" : '')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST AJAX: Regenera un indicador individual con IA
     */
    public function regenerarIndicadorIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $json = $this->request->getJSON(true);
        $indicadorActual = $json['indicador'] ?? [];
        $instrucciones = $json['instrucciones'] ?? '';

        if (empty($instrucciones)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe proporcionar instrucciones para la IA'
            ]);
        }

        try {
            $service = new \App\Services\IndicadoresGeneralIAService();
            $contexto = $service->obtenerContextoCompleto($idCliente);

            $resultado = $service->regenerarIndicador($indicadorActual, $instrucciones, $contexto);

            return $this->response->setJSON($resultado);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al regenerar: ' . $e->getMessage()
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────
    // GENERADOR IA DE ACTIVIDADES DESDE INDICADORES
    // (Ingenieria Inversa: Indicadores → Actividades PTA)
    // ─────────────────────────────────────────────────────────

    /**
     * GET AJAX: Contexto + indicadores huerfanos para SweetAlert
     */
    public function previsualizarContextoActividades(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $service = new \App\Services\ActividadesDesdeIndicadoresService();
            $contexto = $service->obtenerContextoParaActividades($idCliente);

            return $this->response->setJSON([
                'success' => true,
                'data' => $contexto
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener contexto: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST AJAX: Genera sugerencias de actividades con IA
     */
    public function previewActividadesIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $json = $this->request->getJSON(true);
        $instrucciones = $json['instrucciones'] ?? '';
        $categorias = $json['categorias'] ?? null;

        try {
            $service = new \App\Services\ActividadesDesdeIndicadoresService();
            $resultado = $service->previewActividades($idCliente, $instrucciones, $categorias);

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar actividades: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST AJAX: Guarda actividades seleccionadas en tbl_pta_cliente
     */
    public function guardarActividadesIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $json = $this->request->getJSON(true);
        $actividades = $json['actividades'] ?? [];
        $anio = (int)($json['anio'] ?? date('Y'));

        if (empty($actividades)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron actividades para guardar'
            ]);
        }

        try {
            $service = new \App\Services\ActividadesDesdeIndicadoresService();
            $resultado = $service->guardarActividadesSeleccionadas($idCliente, $anio, $actividades);

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultado,
                'message' => "Se crearon {$resultado['creadas']} actividades en el Plan de Trabajo" .
                    ($resultado['existentes'] > 0 ? " ({$resultado['existentes']} ya existian)" : '')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST AJAX: Regenera una actividad individual con IA
     */
    public function regenerarActividadIA(int $idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $json = $this->request->getJSON(true);
        $actividadActual = $json['actividad'] ?? [];
        $instrucciones = $json['instrucciones'] ?? '';

        if (empty($instrucciones)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe proporcionar instrucciones para la IA'
            ]);
        }

        try {
            $service = new \App\Services\ActividadesDesdeIndicadoresService();
            $svcIndicadores = new \App\Services\IndicadoresGeneralIAService();
            $contexto = $svcIndicadores->obtenerContextoCompleto($idCliente);

            $resultado = $service->regenerarActividad($actividadActual, $instrucciones, $contexto);

            return $this->response->setJSON($resultado);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al regenerar: ' . $e->getMessage()
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────

    /**
     * Safety net: verifica y siembra indicadores legales si faltan.
     * Se llama desde index() y dashboard() para clientes existentes
     * que fueron creados antes de esta funcionalidad.
     */
    private function asegurarIndicadoresLegales(int $idCliente): void
    {
        try {
            if (!$this->indicadorModel->tieneIndicadoresLegales($idCliente)) {
                $resultado = $this->indicadorModel->crearIndicadoresLegales($idCliente);
                log_message('info', "Auto-seed indicadores legales para cliente {$idCliente}: " . json_encode($resultado));
            }
        } catch (\Exception $e) {
            log_message('error', "Error auto-seed indicadores legales cliente {$idCliente}: " . $e->getMessage());
        }
    }

    /**
     * Convierte imagen PNG con transparencia a fondo blanco para Word
     */
    private function convertirImagenConFondoBlanco(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            return '';
        }

        $mime = mime_content_type($imagePath);

        if ($mime !== 'image/png') {
            $imageData = file_get_contents($imagePath);
            return 'data:' . $mime . ';base64,' . base64_encode($imageData);
        }

        $imagenOriginal = @imagecreatefrompng($imagePath);
        if (!$imagenOriginal) {
            $imageData = file_get_contents($imagePath);
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        $ancho = imagesx($imagenOriginal);
        $alto = imagesy($imagenOriginal);

        $imagenConFondo = imagecreatetruecolor($ancho, $alto);
        $blanco = imagecolorallocate($imagenConFondo, 255, 255, 255);
        imagefill($imagenConFondo, 0, 0, $blanco);
        imagealphablending($imagenConFondo, true);
        imagesavealpha($imagenConFondo, true);
        imagecopy($imagenConFondo, $imagenOriginal, 0, 0, 0, 0, $ancho, $alto);

        ob_start();
        imagepng($imagenConFondo);
        $imageData = ob_get_clean();

        imagedestroy($imagenOriginal);
        imagedestroy($imagenConFondo);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
