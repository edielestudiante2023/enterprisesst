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
                ->whereIn('id_acceso', array_column($accesosData, 'id_acceso'))  // Obtener todos los accesos permitidos
                ->orderBy('FIELD(dimension, "Planear", "Hacer", "Verificar", "Actuar", "Indicadores")', '', false)  // Ordenar por dimensión
                ->findAll();

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

        // Obtener el ID del cliente desde la sesión
        $clientId = session()->get('user_id'); // Asegúrate de que 'user_id' almacene el ID del cliente

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Sesión no válida.');
        }

        // Obtener documentos por subtemas y campos relacionados
        $inspecciones = $reportModel
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
            ->where('tbl_reporte.id_report_type', 1) // ID para 'Hojas de cálculo interactivas'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();

        $reportes = $reportModel
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
            ->where('tbl_reporte.id_report_type', 2) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
        
            $aseo = $reportModel
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
            ->where('tbl_reporte.id_report_type', 3) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $vigilancia = $reportModel
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
            ->where('tbl_reporte.id_report_type', 4) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $ambiental = $reportModel
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
            ->where('tbl_reporte.id_report_type', 5) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $actasdevisita = $reportModel
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
            ->where('tbl_reporte.id_report_type', 6) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $capacitaciones = $reportModel
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
            ->where('tbl_reporte.id_report_type', 7) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $cincuentahoras = $reportModel
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
            ->where('tbl_reporte.id_report_type', 8) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $reporteministerio = $reportModel
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
            ->where('tbl_reporte.id_report_type', 9) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $cierredemes = $reportModel
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
            ->where('tbl_reporte.id_report_type', 10) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $emergencias = $reportModel
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
            ->where('tbl_reporte.id_report_type', 11) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $otrosproveedores = $reportModel
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
            ->where('tbl_reporte.id_report_type', 12) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $secretariasalud = $reportModel
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
            ->where('tbl_reporte.id_report_type', 13) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $lavadotanques = $reportModel
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
            ->where('tbl_reporte.id_report_type', 14) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $localescomerciales = $reportModel
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
            ->where('tbl_reporte.id_report_type', 15) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $fumigaciones = $reportModel
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
            ->where('tbl_reporte.id_report_type', 16) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $normatividad = $reportModel
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
            ->where('tbl_reporte.id_report_type', 17) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
            $contrato = $reportModel
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
            ->where('tbl_reporte.id_report_type', 19) // ID para 'Matrices'
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();

        $data = [
            'inspecciones' => $inspecciones,
            'reportes' => $reportes,
            'aseo' => $aseo,
            'vigilancia' => $vigilancia,
            'ambiental' => $ambiental,
            'actasdevisita' => $actasdevisita,
            'capacitaciones' => $capacitaciones,
            'cincuentahoras' => $cincuentahoras,
            'reporteministerio' => $reporteministerio,
            'cierredemes' => $cierredemes,
            'emergencias' => $emergencias,
            'otrosproveedores' => $otrosproveedores,
            'secretariasalud' => $secretariasalud,
            'lavadotanques' => $lavadotanques,
            'localescomerciales' => $localescomerciales,
            'fumigaciones' => $fumigaciones,
            'normatividad' => $normatividad,
            'contrato' => $contrato,
        ];

        return view('client/document_view', $data);
    }

}
