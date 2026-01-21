<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ConsultantDashboardPendientesController extends Controller
{
    public function index()
    {
        $session = session();

        // Verificar que el usuario esté autenticado
        if (!$session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        // Obtener TODOS los clientes
        $clientes = $clientModel->findAll();

        // Obtener TODOS los pendientes con JOIN a clientes
        $pendientes = $pendientesModel
            ->select('tbl_pendientes.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente')
            ->findAll();

        // Métricas globales
        $totalPendientes = count($pendientes);

        $diasTotal = 0;
        $countDias = 0;
        foreach ($pendientes as $pend) {
            if (!empty($pend['conteo_dias'])) {
                $diasTotal += intval($pend['conteo_dias']);
                $countDias++;
            }
        }
        $promedioDias = $countDias > 0 ? round($diasTotal / $countDias, 2) : 0;

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($pendientes as $pend) {
            $estado = $pend['estado'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por responsable
        $responsableCounts = [];
        foreach ($pendientes as $pend) {
            $resp = $pend['responsable'] ?? 'SIN ASIGNAR';
            $responsableCounts[$resp] = ($responsableCounts[$resp] ?? 0) + 1;
        }

        // Obtener valores únicos para los selectores
        $estadosUnicos = array_unique(array_column($pendientes, 'estado'));
        $responsablesUnicos = array_unique(array_column($pendientes, 'responsable'));

        // Obtener meses únicos para los selectores
        $mesesAsignacionUnicos = [];
        $mesesCierreUnicos = [];
        foreach ($pendientes as $pend) {
            if (!empty($pend['fecha_asignacion'])) {
                $fecha = date('Y-m', strtotime($pend['fecha_asignacion']));
                $mesesAsignacionUnicos[$fecha] = date('F Y', strtotime($pend['fecha_asignacion']));
            }
            if (!empty($pend['fecha_cierre'])) {
                $fecha = date('Y-m', strtotime($pend['fecha_cierre']));
                $mesesCierreUnicos[$fecha] = date('F Y', strtotime($pend['fecha_cierre']));
            }
        }

        $data = [
            'clientes' => $clientes,
            'pendientes' => $pendientes,
            'totalPendientes' => $totalPendientes,
            'promedioDias' => $promedioDias,
            'estadoCounts' => $estadoCounts,
            'responsableCounts' => $responsableCounts,
            'estadosUnicos' => array_filter($estadosUnicos),
            'responsablesUnicos' => array_filter($responsablesUnicos),
            'mesesAsignacionUnicos' => $mesesAsignacionUnicos,
            'mesesCierreUnicos' => $mesesCierreUnicos
        ];

        return view('consultant/dashboard_pendientes', $data);
    }
}
