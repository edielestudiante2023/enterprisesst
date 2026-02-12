<?php

namespace App\Controllers;

use App\Models\ClienteReportModel;
use App\Models\DetailReportModel;
use App\Models\ReportTypeModel;
use CodeIgniter\Controller;

class ClienteReportController extends Controller
{
    public function index($idClienteParam = null)
    {
        // Obtener el ID del cliente desde la sesión o por URL (consultor/admin)
        $session = session();
        $role = $session->get('role');
        if ($idClienteParam && in_array($role, ['consultant', 'admin'])) {
            $clientId = $idClienteParam;
        } else {
            $clientId = $session->get('user_id');
        }

        // Verificar que el ID del cliente se obtenga correctamente
        if (is_null($clientId)) {
            return redirect()->to('/login')->with('error', 'Sesión inválida. Inicia sesión nuevamente.');
        }

        // Crear una instancia del modelo del reporte
        $reportModel = new ClienteReportModel();

        // Realizar la consulta con joins para traer los datos relacionados y asegurar que se ordenen correctamente
        $reports = $reportModel
            ->select('
                tbl_reporte.id_reporte,
                tbl_reporte.titulo_reporte,
                tbl_reporte.enlace,
                tbl_reporte.estado,
                tbl_reporte.observaciones,
                tbl_reporte.created_at,
                detail_report.detail_report AS detalle_reporte,
                report_type_table.report_type AS tipo_reporte,
                tbl_clientes.nombre_cliente AS cliente_nombre
            ')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte.id_cliente', 'left')
            ->where('tbl_reporte.id_cliente', $clientId)
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();

        // Pasar los reportes a la vista
        $data = [
            'reports' => $reports
        ];

        return view('client/report_dashboard', $data);
    }
}
