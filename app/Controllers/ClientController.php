<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\AccesoModel;
use App\Models\EstandarModel;
use App\Models\EstandarAccesoModel;
use CodeIgniter\Controller;
use App\Models\ReporteModel;

class ClientController extends Controller
{
    public function index()
    {
        $session = session();
        $clientId = $session->get('user_id');

        $model = new ClientModel();
        $client = $model->find($clientId);

        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado');
        }

        $data = [
            'client' => $client
        ];

        return view('client/dashboard', $data);
    }

    public function dashboard()
    {
        try {
            $session = session();

            // Obtener el ID del cliente desde la sesión
            $id_cliente = $session->get('user_id');
            if (!$id_cliente) {
                return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
            }

            // Obtener el cliente
            $clientModel = new ClientModel();
            $client = $clientModel->find($id_cliente);
            if (!$client) {
                return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
            }

            // Inicializar $accesos como un array vacío
            $accesos = [];

            // Obtener el estándar del cliente (por ejemplo '7A')
            $estandarNombre = $client['estandares'];

            // Instanciar el modelo de estandares y obtener el ID del estándar (por ejemplo 1 para '7A')
            $estandarModel = new EstandarModel();
            $estandar = $estandarModel->where('nombre', $estandarNombre)->first();

            if (!$estandar) {
                return redirect()->to('/login')->with('error', 'Estándar no encontrado.');
            }

            $id_estandar = $estandar['id_estandar'];  // Esto nos da el ID numérico del estándar

            // Obtener los accesos permitidos para el estándar usando el modelo EstandarAccesoModel
            $estandarAccesoModel = new EstandarAccesoModel();
            $accesosData = $estandarAccesoModel->where('id_estandar', $id_estandar)->findAll();

            // Si no hay accesos asociados al estándar
            if (empty($accesosData)) {
                echo "No hay accesos disponibles para el estándar $estandarNombre.";
                exit;
            }

            // Instanciar el modelo de accesos para obtener los detalles de cada acceso ordenado por la dimensión
            $accesoModel = new AccesoModel();

            // Obtener los accesos permitidos para el estándar usando el modelo EstandarAccesoModel
            $estandarAccesoModel = new EstandarAccesoModel();
            $accesosData = $estandarAccesoModel->where('id_estandar', $id_estandar)->findAll();

            // Obtener todos los accesos relacionados con el estándar y ordenarlos por la dimensión
            $accesos = $accesoModel
                ->whereIn('id_acceso', array_column($accesosData, 'id_acceso'))
                ->findAll();

            // Ordenar en PHP usando el ciclo PHVA
            $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];

            usort($accesos, function ($a, $b) use ($orden) {
                return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
            });


            // Pasar los accesos a la vista `dashboardclient`
            return view('client/dashboard', [
                'accesos' => $accesos,
                'client' => $client
            ]);
        } catch (\Exception $e) {
            echo "Ocurrió un error: " . $e->getMessage();
            exit;
        }
    }

    public function viewDocuments()
    {
        $reportModel = new ReporteModel();
        $clientId = session()->get('user_id');

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Sesión no válida.');
        }

        // Mapeo de claves con ID de reportes y títulos
        $topics = [
            'revisionCopasst'            => ['id' => 1,  'titulo' => 'Revisión del COPASST'],
            'comiteConvivencia'         => ['id' => 2,  'titulo' => 'Comité de Convivencia Laboral'],
            'actaVisitasSST'             => ['id' => 3,  'titulo' => 'Acta de Visitas SST'],
            'capacitacionSST'            => ['id' => 4,  'titulo' => 'Capacitación en SST'],
            'protocolosEmergencia'       => ['id' => 5,  'titulo' => 'Protocolos de Emergencia'],
            'talentoHumanoBienestar'     => ['id' => 6,  'titulo' => 'Talento Humano y Bienestar'],
            'analisisAccidentes'         => ['id' => 7,  'titulo' => 'Análisis de Accidentes de Trabajo'],
            'evaluacionFactores'         => ['id' => 8,  'titulo' => 'Evaluación de Factores Psicosociales'],
            'inspeccionPuestos'          => ['id' => 9,  'titulo' => 'Inspección de Puestos de Trabajo'],
            'planGestionAmbiental'       => ['id' => 10, 'titulo' => 'Plan de Gestión Ambiental'],
            'organigrama'                => ['id' => 11, 'titulo' => 'Organigrama'],
            'documentacionNormativa'     => ['id' => 12, 'titulo' => 'Documentación Normativa SST'],
            'proveedoresExternos'        => ['id' => 13, 'titulo' => 'Proveedores Externos'],
            'programaErgonomia'          => ['id' => 14, 'titulo' => 'Programa de Ergonomía'],
            'gestionResiduos'            => ['id' => 15, 'titulo' => 'Gestión de Residuos Peligrosos'],
            'seguimientoPlanes'          => ['id' => 16, 'titulo' => 'Seguimiento a Planes de Acción'],
            'programaRiesgos'            => ['id' => 17, 'titulo' => 'Programa de Gestión de Riesgos'],
            'matrizPeligros'             => ['id' => 19, 'titulo' => 'Matriz de Peligros'],
            'revisionIluminacion'        => ['id' => 20, 'titulo' => 'Revisión de Sistemas de Iluminación'],
            'arl'                        => ['id' => 21, 'titulo' => 'Temas Relacionados con la ARL'],
            'contratoycontractuales'     => ['id' => 22, 'titulo' => 'Contrato y Otros Documentos Contractuales'],
            'plandeemergencia'           => ['id' => 23, 'titulo' => 'Plan de Emergencia y Análisis Relacionados'],
            'inspecciones'               => ['id' => 24, 'titulo' => 'Inspecciones Efectuadas'],
        ];

        $data = ['topicsList' => []];

        foreach ($topics as $key => $info) {
            $data['topicsList'][$key] = $info['titulo'];

            $data[$key] = $reportModel
                ->select('
                tbl_reporte.id_reporte,
                tbl_reporte.titulo_reporte,
                tbl_reporte.enlace,
                tbl_reporte.estado,
                tbl_reporte.observaciones,
                tbl_reporte.created_at,
                tbl_reporte.updated_at,
                detail_report.detail_report AS detalle_reporte,
                report_type_table.report_type AS tipo_reporte,
                tbl_clientes.nombre_cliente AS cliente_nombre
            ')
                ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
                ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte.id_cliente', 'left')
                ->where('tbl_reporte.id_cliente', $clientId)
                ->where('tbl_reporte.id_report_type', $info['id'])
                ->orderBy('tbl_reporte.created_at', 'DESC')
                ->findAll();
        }

        return view('client/document_view', $data);
    }
}
