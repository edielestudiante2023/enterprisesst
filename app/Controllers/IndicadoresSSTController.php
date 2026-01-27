<?php

namespace App\Controllers;

use App\Models\IndicadorSSTModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSSTModel;

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

        // Obtener contexto SST para saber estándares aplicables
        $contextoModel = new ClienteContextoSSTModel();
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

        $contextoModel = new ClienteContextoSSTModel();
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

        $contextoModel = new ClienteContextoSSTModel();
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
        $contextoModel = new ClienteContextoSSTModel();
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
}
