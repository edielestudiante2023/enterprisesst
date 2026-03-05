<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaVisitaModel;
use App\Models\InspeccionLocativaModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\InspeccionBotiquinModel;
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
        $actaModel = new ActaVisitaModel();
        $pendientes = $actaModel->getAllPendientes();
        $totalActas = $actaModel->where('estado', 'completo')->countAllResults();

        $locativaModel = new InspeccionLocativaModel();
        $pendientesLocativas = $locativaModel->getAllPendientes();
        $totalLocativas = $locativaModel->where('estado', 'completo')->countAllResults();

        $extintoresModel = new InspeccionExtintoresModel();
        $pendientesExtintores = $extintoresModel->getAllPendientes();
        $totalExtintores = $extintoresModel->where('estado', 'completo')->countAllResults();

        $botiquinModel = new InspeccionBotiquinModel();
        $pendientesBotiquin = $botiquinModel->getAllPendientes();
        $totalBotiquin = $botiquinModel->where('estado', 'completo')->countAllResults();

        $data = [
            'title'                  => 'Inspecciones SST',
            'pendientes'             => $pendientes,
            'pendientesLocativas'    => $pendientesLocativas,
            'pendientesExtintores'   => $pendientesExtintores,
            'pendientesBotiquin'     => $pendientesBotiquin,
            'totalActas'             => $totalActas,
            'totalLocativas'         => $totalLocativas,
            'totalExtintores'        => $totalExtintores,
            'totalBotiquin'          => $totalBotiquin,
            'nombre'                 => session()->get('nombre_usuario'),
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

        $clientes = $clientModel->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'")
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->findAll();

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
