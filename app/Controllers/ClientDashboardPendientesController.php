<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ClientDashboardPendientesController extends Controller
{
    public function index($id_cliente)
    {
        $session = session();

        // Verificar que el usuario esté autenticado
        if (!$session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        $client = $clientModel->find($id_cliente);

        if (!$client) {
            return redirect()->to('/client/dashboard')->with('error', 'Cliente no encontrado');
        }

        $pendientes = $pendientesModel->where('id_cliente', $id_cliente)->findAll();

        // Calcular métricas principales
        $totalPendientes = count($pendientes);

        $diasTotal = 0;
        $countDias = 0;
        foreach ($pendientes as $pend) {
            if (isset($pend['conteo_dias']) && is_numeric($pend['conteo_dias'])) {
                $diasTotal += intval($pend['conteo_dias']);
                $countDias++;
            }
        }
        $promedioDias = $countDias > 0 ? round($diasTotal / $countDias, 1) : 0;

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($pendientes as $pend) {
            $estado = $pend['estado'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por responsable
        $responsableCounts = [];
        foreach ($pendientes as $pend) {
            $responsable = $pend['responsable'] ?? 'SIN ASIGNAR';
            $responsableCounts[$responsable] = ($responsableCounts[$responsable] ?? 0) + 1;
        }

        // Obtener estados únicos
        $estadosUnicos = array_unique(array_column($pendientes, 'estado'));

        // Obtener meses únicos para filtros
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
            'client' => $client,
            'pendientes' => $pendientes,
            'totalPendientes' => $totalPendientes,
            'promedioDias' => $promedioDias,
            'estadoCounts' => $estadoCounts,
            'responsableCounts' => $responsableCounts,
            'estadosUnicos' => array_filter($estadosUnicos),
            'mesesAsignacionUnicos' => $mesesAsignacionUnicos,
            'mesesCierreUnicos' => $mesesCierreUnicos
        ];

        return view('client/dashboard_pendientes', $data);
    }
}
