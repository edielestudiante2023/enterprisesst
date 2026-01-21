<?php

namespace App\Controllers;

use App\Models\EvaluationModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ConsultantDashboardEstandaresController extends Controller
{
    public function index()
    {
        $session = session();

        // Verificar que el usuario esté autenticado
        if (!$session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        // Obtener TODOS los clientes
        $clientes = $clientModel->findAll();

        // Obtener TODAS las evaluaciones con JOIN a clientes
        $evaluaciones = $evaluationModel
            ->select('evaluacion_inicial_sst.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = evaluacion_inicial_sst.id_cliente')
            ->findAll();

        // Calcular métricas globales
        $totalCalificado = 0;
        $totalPosible = 0;

        foreach ($evaluaciones as $ev) {
            $totalCalificado += floatval($ev['valor'] ?? 0);
            $totalPosible += floatval($ev['puntaje_cuantitativo'] ?? 0);
        }

        // Agrupar por ciclo PHVA
        $phvaCounts = [];
        $phvaValores = [];
        foreach ($evaluaciones as $ev) {
            $ciclo = $ev['ciclo'];
            if (!empty($ciclo)) {
                $phvaCounts[$ciclo] = ($phvaCounts[$ciclo] ?? 0) + 1;
                $phvaValores[$ciclo] = ($phvaValores[$ciclo] ?? 0) + floatval($ev['valor'] ?? 0);
            }
        }

        // Agrupar por calificación (incluir vacíos)
        $calificacionCounts = [];
        foreach ($evaluaciones as $ev) {
            $calif = $ev['evaluacion_inicial'];
            // Si está vacío, asignar "SIN EVALUAR"
            if (empty($calif)) {
                $calif = 'SIN EVALUAR';
            }
            $calificacionCounts[$calif] = ($calificacionCounts[$calif] ?? 0) + 1;
        }

        // Agrupar por dimensión
        $dimensionCounts = [];
        foreach ($evaluaciones as $ev) {
            $dim = $ev['estandar'];
            if (!empty($dim)) {
                $dimensionCounts[$dim] = ($dimensionCounts[$dim] ?? 0) + floatval($ev['valor'] ?? 0);
            }
        }

        // Obtener valores únicos para los selectores
        $dimensionesUnicas = array_unique(array_column($evaluaciones, 'estandar'));

        // Para calificaciones, incluir vacíos como "SIN EVALUAR"
        $calificacionesUnicas = [];
        foreach ($evaluaciones as $ev) {
            $calif = $ev['evaluacion_inicial'];
            if (empty($calif)) {
                $calificacionesUnicas[] = 'SIN EVALUAR';
            } else {
                $calificacionesUnicas[] = $calif;
            }
        }
        $calificacionesUnicas = array_unique($calificacionesUnicas);

        $ciclosUnicos = array_unique(array_column($evaluaciones, 'ciclo'));

        $data = [
            'clientes' => $clientes,
            'evaluaciones' => $evaluaciones,
            'totalCalificado' => round($totalCalificado, 2),
            'totalPosible' => round($totalPosible, 2),
            'phvaCounts' => $phvaCounts,
            'phvaValores' => $phvaValores,
            'calificacionCounts' => $calificacionCounts,
            'dimensionCounts' => $dimensionCounts,
            'dimensionesUnicas' => array_filter($dimensionesUnicas),
            'calificacionesUnicas' => array_filter($calificacionesUnicas),
            'ciclosUnicos' => array_filter($ciclosUnicos)
        ];

        return view('consultant/dashboard_estandares_minimos', $data);
    }
}
