<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaVisitaModel;
use App\Models\InspeccionLocativaModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\InspeccionBotiquinModel;
use App\Models\InspeccionSenalizacionModel;
use App\Models\RegistroAsistenciaModel;
use App\Models\PausaActivaModel;
use App\Models\InvestigacionAccidenteModel;
use App\Models\ActaCapacitacionModel;
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

        $senalizacionModel = new InspeccionSenalizacionModel();
        $pendientesSenalizacion = $senalizacionModel->getAllPendientes();
        $totalSenalizacion = $senalizacionModel->where('estado', 'completo')->countAllResults();

        $registroAsistModel = new RegistroAsistenciaModel();
        $pendientesAsistencia = $registroAsistModel->getAllPendientes();
        $totalAsistencia = $registroAsistModel->where('estado', 'completo')->countAllResults();

        $pausasModel = new PausaActivaModel();
        $pendientesPausas = $pausasModel->getAllPendientes();
        $totalPausas = $pausasModel->where('estado', 'completo')->countAllResults();

        $invAccidenteModel = new InvestigacionAccidenteModel();
        $pendientesInvestigaciones = $invAccidenteModel->getAllPendientes();
        $totalInvestigaciones = $invAccidenteModel->where('estado', 'completo')->countAllResults();

        $actaCapModel = new ActaCapacitacionModel();
        $pendientesCapacitaciones = $actaCapModel->getAllPendientes();
        $totalCapacitaciones = $actaCapModel->where('estado', 'completo')->countAllResults();

        $data = [
            'title'                     => 'Inspecciones SST',
            'pendientes'                => $pendientes,
            'pendientesLocativas'       => $pendientesLocativas,
            'pendientesExtintores'      => $pendientesExtintores,
            'pendientesBotiquin'        => $pendientesBotiquin,
            'pendientesSenalizacion'    => $pendientesSenalizacion,
            'pendientesAsistencia'      => $pendientesAsistencia,
            'pendientesPausas'          => $pendientesPausas,
            'pendientesInvestigaciones' => $pendientesInvestigaciones,
            'pendientesCapacitaciones'  => $pendientesCapacitaciones,
            'totalActas'                => $totalActas,
            'totalLocativas'            => $totalLocativas,
            'totalExtintores'           => $totalExtintores,
            'totalBotiquin'             => $totalBotiquin,
            'totalSenalizacion'         => $totalSenalizacion,
            'totalAsistencia'           => $totalAsistencia,
            'totalPausas'               => $totalPausas,
            'totalInvestigaciones'      => $totalInvestigaciones,
            'totalCapacitaciones'       => $totalCapacitaciones,
            'nombre'                    => session()->get('nombre_usuario'),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dashboard', $data),
            'title'   => 'Inspecciones SST',
        ]);
    }

    /**
     * API: Clientes activos
     */
    public function getClientes()
    {
        $clientModel = new ClientModel();

        $clientes = $clientModel->select('id_cliente, nombre_cliente, nit_cliente')
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente', 'ASC')
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
