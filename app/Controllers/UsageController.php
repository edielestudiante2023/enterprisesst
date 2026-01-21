<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class UsageController extends Controller
{
    protected $sessionModel;

    public function __construct()
    {
        $this->sessionModel = new SessionModel();
    }

    /**
     * Dashboard principal de consumo
     */
    public function index()
    {
        $session = session();
        // Permitir acceso a admin y consultant
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        // Obtener parámetros de filtro
        $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01'); // Primer día del mes
        $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d'); // Hoy

        // Marcar sesiones expiradas
        $this->sessionModel->marcarSesionesExpiradas();

        // Obtener datos
        $data = [
            'resumenUsuarios' => $this->sessionModel->getResumenConsumo($fechaInicio, $fechaFin),
            'estadisticas' => $this->sessionModel->getEstadisticasGenerales($fechaInicio, $fechaFin),
            'sesionesPorDia' => $this->sessionModel->getSesionesPorDia($fechaInicio, $fechaFin),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ];

        return view('admin/usage/dashboard', $data);
    }

    /**
     * Ver detalle de consumo de un usuario específico
     */
    public function userDetail($idUsuario)
    {
        $session = session();
        // Permitir acceso a admin y consultant
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($idUsuario);

        if (!$user) {
            return redirect()->to('/admin/usage')->with('msg', 'Usuario no encontrado.');
        }

        $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01');
        $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');

        $data = [
            'user' => $user,
            'historial' => $this->sessionModel->getHistorialUsuario($idUsuario, 100),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ];

        return view('admin/usage/user_detail', $data);
    }

    /**
     * Exportar reporte de consumo a CSV
     */
    public function exportCsv()
    {
        $session = session();
        // Permitir acceso a admin y consultant
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01');
        $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');

        $resumen = $this->sessionModel->getResumenConsumo($fechaInicio, $fechaFin);

        // Preparar CSV
        $filename = 'consumo_usuarios_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM para Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeceras
        fputcsv($output, [
            'ID Usuario',
            'Nombre',
            'Email',
            'Tipo',
            'Total Sesiones',
            'Tiempo Total',
            'Última Sesión',
            'Promedio por Sesión'
        ], ';');

        // Datos
        foreach ($resumen as $row) {
            fputcsv($output, [
                $row['id_usuario'],
                $row['nombre_completo'],
                $row['email'],
                $row['tipo_usuario'],
                $row['total_sesiones'],
                $row['tiempo_total_formato'],
                $row['ultima_sesion'] ?? 'Nunca',
                gmdate('H:i:s', (int)$row['promedio_duracion_segundos'])
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * API para obtener datos de gráfica (AJAX)
     */
    public function chartData()
    {
        $session = session();
        // Permitir acceso a admin y consultant
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(401);
        }

        $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01');
        $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');

        $sesionesPorDia = $this->sessionModel->getSesionesPorDia($fechaInicio, $fechaFin);

        $labels = [];
        $sesiones = [];
        $usuarios = [];
        $tiempos = [];

        foreach ($sesionesPorDia as $row) {
            $labels[] = date('d/m', strtotime($row['fecha']));
            $sesiones[] = (int)$row['total_sesiones'];
            $usuarios[] = (int)$row['usuarios_unicos'];
            $tiempos[] = round((int)$row['tiempo_total'] / 3600, 2); // Convertir a horas
        }

        return $this->response->setJSON([
            'labels' => $labels,
            'sesiones' => $sesiones,
            'usuarios' => $usuarios,
            'tiempos' => $tiempos
        ]);
    }
}
