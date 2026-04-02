<?php

namespace App\Controllers;

use App\Models\DashboardItemModel;
use App\Models\ClientModel;

class CustomDashboardController extends BaseController
{
    public function index()
    {
        $session = session();

        $model = new DashboardItemModel();
        $items = $model->where('activo', 1)
                       ->orderBy('categoria ASC, orden ASC')
                       ->findAll();

        // Agrupar items por categoría
        $grouped = [];
        foreach ($items as $item) {
            $cat = $item['categoria'] ?? 'Sin categoría';
            $grouped[$cat][] = $item;
        }

        // Orden visual de categorías
        $ordenCategorias = [
            'Operación por Cliente',
            'Dashboards y Reportes',
            'Herramientas IA',
            'Cumplimiento SST - Res. 0312',
            'Capacitación y Planificación',
            'Gestión Documental',
            'Carga Masiva CSV',
            'Plataformas Colaborativas',
            'Administración del Sistema',
        ];

        $sortedGrouped = [];
        foreach ($ordenCategorias as $cat) {
            if (isset($grouped[$cat])) {
                $sortedGrouped[$cat] = $grouped[$cat];
            }
        }
        foreach ($grouped as $cat => $items) {
            if (!isset($sortedGrouped[$cat])) {
                $sortedGrouped[$cat] = $items;
            }
        }

        // Obtener datos del usuario en sesión
        $userModel = new \App\Models\UserModel();
        $data['usuario'] = null;
        if ($session->get('id_usuario')) {
            $data['usuario'] = $userModel->find($session->get('id_usuario'));
        }

        // Obtener clientes activos para el modal selector
        $clientModel = new ClientModel();
        $data['clientes'] = $clientModel->where('estado', 'activo')->findAll();
        $data['grouped'] = $sortedGrouped;

        return view('consultant/admindashboard', $data);
    }
}
