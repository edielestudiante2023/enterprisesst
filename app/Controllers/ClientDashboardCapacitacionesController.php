<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ClientDashboardCapacitacionesController extends Controller
{
    public function index($id_cliente)
    {
        $session = session();

        // Verificar que el usuario esté autenticado
        if (!$session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $capacitacionModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();

        $client = $clientModel->find($id_cliente);

        if (!$client) {
            return redirect()->to('/client/dashboard')->with('error', 'Cliente no encontrado');
        }

        $capacitaciones = $capacitacionModel->where('id_cliente', $id_cliente)->findAll();

        // Calcular métricas principales
        $totalCapacitaciones = count($capacitaciones);

        $asistentesTotal = 0;
        $calificacionesTotal = 0;
        $countAsistentes = 0;
        $countCalificaciones = 0;

        foreach ($capacitaciones as $cap) {
            if (!empty($cap['numero_de_asistentes_a_capacitacion'])) {
                $asistentesTotal += intval($cap['numero_de_asistentes_a_capacitacion']);
                $countAsistentes++;
            }
            if (!empty($cap['promedio_de_calificaciones'])) {
                $calificacionesTotal += floatval($cap['promedio_de_calificaciones']);
                $countCalificaciones++;
            }
        }

        $promedioAsistentes = $countAsistentes > 0 ? round($asistentesTotal / $countAsistentes, 2) : 0;
        $promedioCalificaciones = $countCalificaciones > 0 ? round($calificacionesTotal / $countCalificaciones, 2) : 0;

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($capacitaciones as $cap) {
            $estado = $cap['estado'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por tipo de participantes
        $participantesCounts = [];
        foreach ($capacitaciones as $cap) {
            $tipo = $cap['perfil_de_asistentes'] ?? 'SIN DEFINIR';
            $participantesCounts[$tipo] = ($participantesCounts[$tipo] ?? 0) + 1;
        }

        // Obtener meses únicos para filtro
        $mesesUnicos = [];
        foreach ($capacitaciones as $cap) {
            if (!empty($cap['fecha_programada'])) {
                $fecha = date('Y-m', strtotime($cap['fecha_programada']));
                $mesesUnicos[$fecha] = date('F Y', strtotime($cap['fecha_programada']));
            }
        }

        // Obtener estados únicos
        $estadosUnicos = array_unique(array_column($capacitaciones, 'estado'));

        $data = [
            'client' => $client,
            'capacitaciones' => $capacitaciones,
            'totalCapacitaciones' => $totalCapacitaciones,
            'promedioAsistentes' => $promedioAsistentes,
            'promedioCalificaciones' => $promedioCalificaciones,
            'estadoCounts' => $estadoCounts,
            'participantesCounts' => $participantesCounts,
            'mesesUnicos' => $mesesUnicos,
            'estadosUnicos' => array_filter($estadosUnicos)
        ];

        return view('client/dashboard_capacitaciones', $data);
    }
}
