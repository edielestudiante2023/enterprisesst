<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaVisitaModel;
use App\Models\InspeccionLocativaModel;
use App\Models\ClientModel;
use App\Models\PendientesModel;
use App\Models\VencimientosMantenimientoModel;

class InspeccionesController extends BaseController
{
    /**
     * Dashboard principal de inspecciones (PWA)
     */
    public function dashboard()
    {
        $role = session()->get('role');
        $userId = session()->get('user_id');

        $actaModel = new ActaVisitaModel();

        // Documentos pendientes del consultor (o todos si es admin)
        if ($role === 'admin') {
            $pendientes = $actaModel->getAllPendientes();
        } else {
            $pendientes = $actaModel->getPendientesByConsultor($userId);
        }

        // Conteo de actas completas
        $totalActas = $actaModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Locativas
        $locativaModel = new InspeccionLocativaModel();
        $totalLocativas = $locativaModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes locativas (borradores)
        if ($role === 'admin') {
            $pendientesLocativas = $locativaModel->getAllPendientes();
        } else {
            $pendientesLocativas = $locativaModel->getPendientesByConsultor($userId);
        }

        $data = [
            'title'               => 'Inspecciones SST',
            'pendientes'          => $pendientes,
            'pendientesLocativas' => $pendientesLocativas,
            'totalActas'          => $totalActas,
            'totalLocativas'      => $totalLocativas,
            'nombre'              => session()->get('nombre_usuario'),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dashboard', $data),
            'title'   => 'Inspecciones SST',
        ]);
    }

    /**
     * API: Clientes del consultor con contrato activo
     */
    public function getClientes()
    {
        $clientModel = new ClientModel();
        $role = session()->get('role');
        $userId = session()->get('user_id');

        $builder = $clientModel->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'");

        if ($role === 'consultant') {
            $builder->where('tbl_clientes.id_consultor', $userId);
        }

        $clientes = $builder->orderBy('tbl_clientes.nombre_cliente', 'ASC')->findAll();

        return $this->response->setJSON($clientes);
    }

    /**
     * API: Pendientes abiertos de un cliente
     */
    public function getPendientes(int $idCliente)
    {
        $model = new PendientesModel();
        $pendientes = $model->where('id_cliente', $idCliente)
            ->where('estado', 'ABIERTA')
            ->orderBy('fecha_asignacion', 'DESC')
            ->findAll();

        return $this->response->setJSON($pendientes);
    }

    /**
     * API: Mantenimientos por vencer de un cliente (prox. 30 dias + vencidos)
     */
    public function getMantenimientos(int $idCliente)
    {
        try {
            $model = new VencimientosMantenimientoModel();
            $dateThreshold = date('Y-m-d', strtotime('+30 days'));

            $mantenimientos = $model->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.descripcion_mantenimiento')
                ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
                ->where('tbl_vencimientos_mantenimientos.id_cliente', $idCliente)
                ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
                ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
                ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
                ->findAll();

            return $this->response->setJSON($mantenimientos);
        } catch (\Exception $e) {
            // Tabla aun no creada - retornar array vacio
            return $this->response->setJSON([]);
        }
    }
}
