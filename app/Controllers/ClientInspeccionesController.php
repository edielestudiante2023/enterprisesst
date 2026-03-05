<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ActaVisitaModel;
use App\Models\ActaVisitaIntegranteModel;
use App\Models\ActaVisitaTemaModel;
use App\Models\ActaVisitaFotoModel;
use App\Models\PendientesModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\ExtintorDetalleModel;
use App\Controllers\Inspecciones\InspeccionExtintoresController;
use App\Models\InspeccionBotiquinModel;
use App\Models\ElementoBotiquinModel;
use App\Controllers\Inspecciones\InspeccionBotiquinController;
use App\Models\InspeccionLocativaModel;
use App\Models\HallazgoLocativoModel;
use CodeIgniter\Controller;

class ClientInspeccionesController extends Controller
{
    /**
     * Verificar sesión de cliente y retornar ID, o null si no autenticado.
     */
    private function getClientId()
    {
        $session = session();
        if ($session->get('role') !== 'client') {
            return null;
        }
        return $session->get('user_id');
    }

    // ─── ACTAS DE VISITA ────────────────────────────────────

    public function listActas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $actaModel = new ActaVisitaModel();
        $inspecciones = $actaModel
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_visita', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Actas de Visita',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'acta_visita',
                'titulo'       => 'Actas de Visita',
                'campo_fecha'  => 'fecha_visita',
                'base_url'     => 'client/inspecciones/actas-visita',
            ]),
        ]);
    }

    public function viewActa($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $actaModel = new ActaVisitaModel();
        $acta = $actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'acta'        => $acta,
            'cliente'     => $clientModel->find($acta['id_cliente']),
            'consultor'   => $consultantModel->find($acta['id_consultor']),
            'integrantes' => (new ActaVisitaIntegranteModel())->getByActa($id),
            'temas'       => (new ActaVisitaTemaModel())->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos' => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Acta de Visita',
            'content' => view('client/inspecciones/acta_visita_view', $data),
        ]);
    }

    // ─── INSPECCIONES DE EXTINTORES ─────────────────────────

    public function listExtintores()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionExtintoresModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones de Extintores',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'extintores',
                'titulo'       => 'Inspecciones de Extintores',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/extintores',
            ]),
        ]);
    }

    public function viewExtintores($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionExtintoresModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'extintores' => (new ExtintorDetalleModel())->getByInspeccion($id),
            'criterios'  => InspeccionExtintoresController::CRITERIOS,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de Extintores',
            'content' => view('client/inspecciones/extintores_view', $data),
        ]);
    }

    // ─── INSPECCIONES DE BOTIQUÍN ────────────────────────────

    public function listBotiquin()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionBotiquinModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones de Botiquín',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'botiquin',
                'titulo'       => 'Inspecciones de Botiquín',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/botiquin',
            ]),
        ]);
    }

    public function viewBotiquin($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionBotiquinModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $elementosRaw = (new ElementoBotiquinModel())->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $clientModel->find($inspeccion['id_cliente']),
            'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
            'elementos'     => InspeccionBotiquinController::ELEMENTOS,
            'elementosData' => $elementosData,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de Botiquín',
            'content' => view('client/inspecciones/botiquin_view', $data),
        ]);
    }

    // ─── INSPECCIONES LOCATIVAS ──────────────────────────────

    public function listLocativas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionLocativaModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones Locativas',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'locativa',
                'titulo'       => 'Inspecciones Locativas',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/locativas',
            ]),
        ]);
    }

    public function viewLocativa($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionLocativaModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'hallazgos'  => (new HallazgoLocativoModel())->getByInspeccion($id),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección Locativa',
            'content' => view('client/inspecciones/locativa_view', $data),
        ]);
    }
}
