<?php

namespace App\Controllers;

use App\Models\PtaClienteNuevaModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ClientDashboardPlanTrabajoController extends Controller
{
    public function index($id_cliente)
    {
        $session = session();

        // Verificar que el usuario esté autenticado
        if (!$session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $ptaModel = new PtaClienteNuevaModel();
        $clientModel = new ClientModel();

        $client = $clientModel->find($id_cliente);

        if (!$client) {
            return redirect()->to('/client/dashboard')->with('error', 'Cliente no encontrado');
        }

        $actividades = $ptaModel->where('id_cliente', $id_cliente)->findAll();

        // Calcular métricas principales
        $totalActividades = count($actividades);

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($actividades as $act) {
            $estado = $act['estado_actividad'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por PHVA
        $phvaCounts = [];
        foreach ($actividades as $act) {
            $phva = $act['phva_plandetrabajo'] ?? 'SIN PHVA';
            $phvaCounts[$phva] = ($phvaCounts[$phva] ?? 0) + 1;
        }

        // Agrupar por responsable
        $responsableCounts = [];
        foreach ($actividades as $act) {
            $responsable = $act['responsable_definido_paralaactividad'] ??
                          $act['responsable_sugerido_plandetrabajo'] ?? 'SIN ASIGNAR';
            $responsableCounts[$responsable] = ($responsableCounts[$responsable] ?? 0) + 1;
        }

        // Obtener valores únicos para los selectores
        // Incluir todos los estados posibles del sistema
        $estadosUnicos = ['ABIERTA', 'CERRADA', 'GESTIONANDO', 'CERRADA SIN EJECUCIÓN'];

        $responsablesUnicos = [];
        foreach ($actividades as $act) {
            $resp = $act['responsable_definido_paralaactividad'] ??
                   $act['responsable_sugerido_plandetrabajo'] ?? null;
            if (!empty($resp)) {
                $responsablesUnicos[] = $resp;
            }
        }
        $responsablesUnicos = array_unique($responsablesUnicos);

        // Obtener fechas únicas para filtros
        $fechasPropuestaUnicas = [];
        $fechasCierreUnicas = [];

        foreach ($actividades as $act) {
            if (!empty($act['fecha_propuesta'])) {
                $fecha = date('Y-m', strtotime($act['fecha_propuesta']));
                $fechasPropuestaUnicas[$fecha] = date('F Y', strtotime($act['fecha_propuesta']));
            }
            if (!empty($act['fecha_cierre'])) {
                $fecha = date('Y-m', strtotime($act['fecha_cierre']));
                $fechasCierreUnicas[$fecha] = date('F Y', strtotime($act['fecha_cierre']));
            }
        }

        $data = [
            'client' => $client,
            'actividades' => $actividades,
            'totalActividades' => $totalActividades,
            'estadoCounts' => $estadoCounts,
            'phvaCounts' => $phvaCounts,
            'responsableCounts' => $responsableCounts,
            'estadosUnicos' => array_filter($estadosUnicos),
            'responsablesUnicos' => array_filter($responsablesUnicos),
            'fechasPropuestaUnicas' => $fechasPropuestaUnicas,
            'fechasCierreUnicas' => $fechasCierreUnicas
        ];

        return view('client/dashboard_plan_trabajo', $data);
    }
}
