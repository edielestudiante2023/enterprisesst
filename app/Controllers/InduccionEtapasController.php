<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\InduccionEtapasService;
use App\Models\InduccionEtapasModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;

/**
 * Controlador para gestionar las etapas del programa de inducción y reinducción
 *
 * Estándar: 1.2.2 Resolución 0312/2019
 *
 * Rutas:
 * - GET  /induccion-etapas/{idCliente}           → index (ver etapas)
 * - GET  /induccion-etapas/{idCliente}/generar   → generar (generar etapas con IA)
 * - POST /induccion-etapas/{idCliente}/generar   → generarPost (ejecutar generación)
 * - POST /induccion-etapas/{idCliente}/aprobar   → aprobar (aprobar etapas)
 * - GET  /induccion-etapas/{idCliente}/checklist-pta       → checklistPTA
 * - POST /induccion-etapas/{idCliente}/generar-pta        → generarPTA
 * - GET  /induccion-etapas/{idCliente}/generar-indicadores → generarIndicadores
 */
class InduccionEtapasController extends BaseController
{
    protected InduccionEtapasService $service;
    protected InduccionEtapasModel $etapasModel;
    protected ClientModel $clienteModel;
    protected ClienteContextoSstModel $contextoModel;

    public function __construct()
    {
        $this->service = new InduccionEtapasService();
        $this->etapasModel = new InduccionEtapasModel();
        $this->clienteModel = new ClientModel();
        $this->contextoModel = new ClienteContextoSstModel();
    }

    /**
     * Muestra las etapas de inducción de un cliente
     */
    public function index(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');
        $etapas = $this->service->getEtapas($idCliente, $anio);
        $contexto = $this->contextoModel->getByCliente($idCliente);

        // Calcular estadísticas
        $stats = $this->etapasModel->contarPorEstado($idCliente, $anio);
        $totalTemas = $this->etapasModel->contarTemasTotal($idCliente, $anio);

        return view('induccion_etapas/index', [
            'cliente' => $cliente,
            'etapas' => $etapas,
            'contexto' => $contexto,
            'anio' => $anio,
            'stats' => $stats,
            'totalTemas' => $totalTemas,
            'todasAprobadas' => $this->etapasModel->todasAprobadas($idCliente, $anio)
        ]);
    }

    /**
     * Muestra la pantalla de generación de etapas
     */
    public function generar(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contexto = $this->contextoModel->getByCliente($idCliente);
        if (!$contexto) {
            return redirect()->to("cliente/{$idCliente}/contexto-sst")
                ->with('warning', 'Debe configurar primero el contexto SST del cliente');
        }

        $anio = (int)date('Y');
        $etapasExistentes = $this->service->getEtapas($idCliente, $anio);

        // Decodificar peligros para mostrar
        $peligros = [];
        if (!empty($contexto['peligros_identificados'])) {
            $decoded = json_decode($contexto['peligros_identificados'], true);
            if (is_array($decoded)) {
                // Detectar si es array simple o agrupado por categorías
                $primerElemento = reset($decoded);
                if (is_string($primerElemento)) {
                    // Array simple: ["ruido", "iluminacion", ...]
                    foreach ($decoded as $item) {
                        // Determinar categoría basada en el nombre del peligro
                        $categoria = $this->categorizarPeligro($item);
                        $peligros[] = ['categoria' => $categoria, 'nombre' => $item];
                    }
                } else {
                    // Array agrupado: {"fisicos": ["ruido"], "quimicos": [...]}
                    foreach ($decoded as $categoria => $items) {
                        if (is_array($items)) {
                            foreach ($items as $item) {
                                $peligros[] = ['categoria' => $categoria, 'nombre' => $item];
                            }
                        }
                    }
                }
            }
        }

        return view('induccion_etapas/generar', [
            'cliente' => $cliente,
            'contexto' => $contexto,
            'peligros' => $peligros,
            'anio' => $anio,
            'etapasExistentes' => $etapasExistentes,
            'tieneEtapas' => !empty($etapasExistentes)
        ]);
    }

    /**
     * Ejecuta la generación de etapas
     */
    public function generarPost(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Cliente no encontrado'
            ]);
        }

        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        // Obtener etapas seleccionadas del formulario
        $etapasSeleccionadas = $this->request->getPost('etapas') ?? null;

        $resultado = $this->service->generarEtapas($idCliente, $anio, $etapasSeleccionadas);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($resultado);
        }

        if ($resultado['success']) {
            return redirect()->to("induccion-etapas/{$idCliente}")
                ->with('success', $resultado['mensaje']);
        }

        return redirect()->back()->with('error', $resultado['error']);
    }

    /**
     * Aprueba todas las etapas
     */
    public function aprobar(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Cliente no encontrado'
            ]);
        }

        $anio = $this->request->getPost('anio') ?? (int)date('Y');
        $userId = session()->get('id_usuario') ?? 1;

        $resultado = $this->service->aprobarTodas($idCliente, $anio, $userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => $resultado,
                'mensaje' => $resultado ? 'Etapas aprobadas correctamente' : 'Error al aprobar etapas'
            ]);
        }

        if ($resultado) {
            return redirect()->to("induccion-etapas/{$idCliente}")
                ->with('success', 'Todas las etapas han sido aprobadas');
        }

        return redirect()->back()->with('error', 'Error al aprobar las etapas');
    }

    /**
     * Muestra el checklist previo a la generación del PTA
     * GET /induccion-etapas/{idCliente}/checklist-pta
     */
    public function checklistPTA(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        if (!$this->etapasModel->todasAprobadas($idCliente, $anio)) {
            return redirect()->to("induccion-etapas/{$idCliente}")
                ->with('warning', 'Debe aprobar todas las etapas antes de generar el PTA');
        }

        return view('induccion_etapas/checklist_pta', [
            'cliente' => $cliente,
            'anio' => $anio,
            'checklistItems' => InduccionEtapasService::CHECKLIST_ITEMS
        ]);
    }

    /**
     * Genera las actividades PTA usando el checklist como contexto
     * POST /induccion-etapas/{idCliente}/generar-pta
     */
    public function generarPTA(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        if (!$this->etapasModel->todasAprobadas($idCliente, $anio)) {
            return redirect()->to("induccion-etapas/{$idCliente}")
                ->with('warning', 'Debe aprobar todas las etapas antes de generar el PTA');
        }

        // Recoger datos del checklist desde POST
        $checklistData = [
            'modalidad'      => $this->request->getPost('modalidad') ?? 'presencial',
            'items_marcados' => $this->request->getPost('checklist') ?? [],
            'notas'          => $this->request->getPost('notas') ?? '',
        ];

        // Preparar las actividades propuestas con contexto del checklist
        $preparacion = $this->service->prepararActividadesPTA($idCliente, $anio, $checklistData);

        if (!$preparacion['success']) {
            return redirect()->to("induccion-etapas/{$idCliente}")
                ->with('error', $preparacion['error']);
        }

        return view('induccion_etapas/generar_pta', [
            'cliente' => $cliente,
            'etapas' => $preparacion['etapas'],
            'actividades' => $preparacion['actividades'],
            'anio' => $anio,
            'total_temas_originales' => $preparacion['total_temas_originales'] ?? count($preparacion['actividades']),
            'consolidado_con_ia' => $preparacion['consolidado_con_ia'] ?? false
        ]);
    }

    /**
     * Envía las actividades seleccionadas al PTA
     * POST /induccion-etapas/{idCliente}/enviar-pta
     */
    public function enviarPTA(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = $this->request->getPost('anio') ?? (int)date('Y');
        $actividades = $this->request->getPost('actividades') ?? [];

        $resultado = $this->service->enviarActividadesPTA($idCliente, $actividades, $anio);

        if ($resultado['success']) {
            // Redirigir al siguiente paso: generar indicadores
            return redirect()->to("induccion-etapas/{$idCliente}/generar-indicadores")
                ->with('success', $resultado['mensaje'] . '. Ahora configura los indicadores.');
        }

        return redirect()->back()->with('error', $resultado['error'] ?? 'Error al enviar actividades');
    }

    /**
     * Muestra la vista de propuestas de indicadores
     * GET /induccion-etapas/{idCliente}/generar-indicadores
     */
    public function generarIndicadores(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Preparar los indicadores propuestos (sin insertar) - ahora usa IA
        $preparacion = $this->service->prepararIndicadores($idCliente);

        return view('induccion_etapas/generar_indicadores', [
            'cliente' => $cliente,
            'indicadores' => $preparacion['indicadores'],
            'actividades_pta' => $preparacion['actividades_pta'] ?? 0,
            'generado_con_ia' => $preparacion['generado_con_ia'] ?? false
        ]);
    }

    /**
     * Envía los indicadores seleccionados al módulo de indicadores
     * POST /induccion-etapas/{idCliente}/enviar-indicadores
     */
    public function enviarIndicadores(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $indicadores = $this->request->getPost('indicadores') ?? [];

        $resultado = $this->service->enviarIndicadores($idCliente, $indicadores);

        if ($resultado['success']) {
            // Buscar la carpeta de documentación 1.2.2 para ir al generador IA
            $carpetaModel = new \App\Models\DocCarpetaModel();
            $carpeta = $carpetaModel->getByCodigo($idCliente, '1.2.2');

            if ($carpeta) {
                // Redirigir a la carpeta de documentación para generar documento con IA
                return redirect()->to("documentacion/carpeta/{$carpeta['id_carpeta']}")
                    ->with('success', $resultado['mensaje'] . '. Ahora puedes generar el documento con IA.');
            }

            // Fallback: si no encuentra la carpeta, ir al dashboard de documentación
            return redirect()->to("documentacion/dashboard/{$idCliente}")
                ->with('success', $resultado['mensaje']);
        }

        return redirect()->back()->with('error', $resultado['error'] ?? 'Error al enviar indicadores');
    }

    /**
     * Ajusta un indicador individual usando IA basándose en el feedback del usuario
     * POST /induccion-etapas/{idCliente}/ajustar-indicador
     */
    public function ajustarIndicador(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Cliente no encontrado'
            ]);
        }

        $data = $this->request->getJSON(true);
        $indicador = $data['indicador'] ?? null;
        $feedback = $data['feedback'] ?? '';

        if (!$indicador || empty($feedback)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Datos incompletos'
            ]);
        }

        $resultado = $this->service->ajustarIndicadorConIA($indicador, $feedback, $idCliente);

        return $this->response->setJSON($resultado);
    }

    /**
     * API: Obtiene las etapas en formato JSON
     */
    public function getEtapasJson(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $etapas = $this->service->getEtapas($idCliente, $anio);

        return $this->response->setJSON([
            'success' => true,
            'etapas' => $etapas,
            'total' => count($etapas)
        ]);
    }

    /**
     * Aprueba una etapa individual
     */
    public function aprobarEtapa(int $idEtapa)
    {
        $userId = session()->get('id_usuario') ?? 1;
        $resultado = $this->etapasModel->aprobarEtapa($idEtapa, $userId);

        return $this->response->setJSON([
            'success' => $resultado,
            'mensaje' => $resultado ? 'Etapa aprobada' : 'Error al aprobar'
        ]);
    }

    /**
     * Edita los temas de una etapa
     */
    public function editarTemas(int $idEtapa)
    {
        $etapa = $this->etapasModel->find($idEtapa);
        if (!$etapa) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Etapa no encontrada'
            ]);
        }

        $temas = $this->request->getJSON(true)['temas'] ?? [];

        $resultado = $this->etapasModel->update($idEtapa, [
            'temas' => json_encode($temas, JSON_UNESCAPED_UNICODE),
            'es_personalizado' => 1
        ]);

        return $this->response->setJSON([
            'success' => $resultado,
            'mensaje' => $resultado ? 'Temas actualizados' : 'Error al actualizar'
        ]);
    }

    /**
     * Elimina un tema específico de una etapa
     * DELETE /induccion-etapas/etapa/{idEtapa}/tema/{temaIdx}
     */
    public function eliminarTema(int $idEtapa, int $temaIdx)
    {
        $etapa = $this->etapasModel->find($idEtapa);
        if (!$etapa) {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'Etapa no encontrada'
            ]);
        }

        if ($etapa['estado'] === 'aprobado') {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'No se puede modificar una etapa aprobada'
            ]);
        }

        $temas = json_decode($etapa['temas'], true) ?? [];

        if (!isset($temas[$temaIdx])) {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'Tema no encontrado'
            ]);
        }

        // Eliminar el tema
        array_splice($temas, $temaIdx, 1);

        $resultado = $this->etapasModel->update($idEtapa, [
            'temas' => json_encode($temas, JSON_UNESCAPED_UNICODE),
            'es_personalizado' => 1
        ]);

        return $this->response->setJSON([
            'success' => $resultado,
            'mensaje' => $resultado ? 'Tema eliminado correctamente' : 'Error al eliminar tema'
        ]);
    }

    /**
     * Agrega un nuevo tema a una etapa
     * POST /induccion-etapas/etapa/{idEtapa}/tema
     */
    public function agregarTema(int $idEtapa)
    {
        $etapa = $this->etapasModel->find($idEtapa);
        if (!$etapa) {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'Etapa no encontrada'
            ]);
        }

        if ($etapa['estado'] === 'aprobado') {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'No se puede modificar una etapa aprobada'
            ]);
        }

        $data = $this->request->getJSON(true);
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if (empty($nombre)) {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'El nombre del tema es obligatorio'
            ]);
        }

        $temas = json_decode($etapa['temas'], true) ?? [];

        // Agregar nuevo tema
        $temas[] = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'origen' => 'manual',
            'es_personalizado' => true
        ];

        $resultado = $this->etapasModel->update($idEtapa, [
            'temas' => json_encode($temas, JSON_UNESCAPED_UNICODE),
            'es_personalizado' => 1
        ]);

        return $this->response->setJSON([
            'success' => $resultado,
            'mensaje' => $resultado ? 'Tema agregado correctamente' : 'Error al agregar tema'
        ]);
    }

    /**
     * Desaprueba una etapa para permitir su edición
     * POST /induccion-etapas/etapa/{idEtapa}/desaprobar
     */
    public function desaprobarEtapa(int $idEtapa)
    {
        $etapa = $this->etapasModel->find($idEtapa);
        if (!$etapa) {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'Etapa no encontrada'
            ]);
        }

        $resultado = $this->etapasModel->update($idEtapa, [
            'estado' => 'borrador',
            'fecha_aprobacion' => null,
            'aprobado_por' => null
        ]);

        return $this->response->setJSON([
            'success' => $resultado,
            'mensaje' => $resultado ? 'Etapa desaprobada, ahora puede editarla' : 'Error al desaprobar'
        ]);
    }

    /**
     * Categoriza un peligro según su nombre
     */
    protected function categorizarPeligro(string $peligro): string
    {
        $peligro = strtolower($peligro);

        // Mapeo de peligros a categorías
        $categorias = [
            'fisico' => ['ruido', 'iluminacion', 'vibracion', 'temperaturas', 'radiaciones', 'presion'],
            'quimico' => ['polvos', 'fibras', 'humos', 'liquidos', 'gases', 'vapores', 'aerosoles', 'material_particulado'],
            'biologico' => ['virus', 'bacterias', 'hongos', 'parasitos', 'fluidos', 'animales', 'plantas'],
            'biomecanico' => ['postura', 'movimiento', 'manipulacion', 'esfuerzo', 'ergonomico'],
            'psicosocial' => ['gestion', 'condiciones_tarea', 'jornada', 'interfaz', 'relaciones', 'estres', 'acoso'],
            'condiciones_seguridad' => ['mecanico', 'electrico', 'locativo', 'tecnologico', 'accidentes', 'publico', 'trabajo_alturas', 'espacios_confinados'],
            'fenomenos_naturales' => ['sismo', 'terremoto', 'inundacion', 'derrumbe', 'precipitaciones']
        ];

        foreach ($categorias as $categoria => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($peligro, $keyword) !== false) {
                    return ucfirst(str_replace('_', ' ', $categoria));
                }
            }
        }

        return 'Otros';
    }
}
