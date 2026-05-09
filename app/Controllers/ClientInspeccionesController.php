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
use App\Models\RegistroAsistenciaModel;
use App\Models\RegistroAsistenciaAsistenteModel;
use App\Controllers\Inspecciones\RegistroAsistenciaController;
use App\Models\EntregaDotacionModel;
use App\Models\EntregaDotacionAsistenteModel;
use App\Models\EntregaDotacionItemModel;
use App\Models\InspeccionEppModel;
use App\Models\HallazgoEppModel;
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

    // ─── REGISTRO DE ASISTENCIA ────────────────────────────

    public function listRegistroAsistencia()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new RegistroAsistenciaModel();
        $registros = $model->getByCliente((int)$clientId);

        $clientModel = new ClientModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Registro de Asistencia',
            'content' => view('client/inspecciones/registro_asistencia_list', [
                'registros'    => $registros,
                'tiposReunion' => RegistroAsistenciaController::TIPOS_REUNION,
            ]),
        ]);
    }

    public function viewRegistroAsistencia($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new RegistroAsistenciaModel();
        $registro = $model->find($id);
        if (!$registro || (int)$registro['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Registro no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new RegistroAsistenciaAsistenteModel();

        $data = [
            'inspeccion'   => $registro,
            'cliente'      => $clientModel->find($registro['id_cliente']),
            'consultor'    => $consultantModel->find($registro['id_consultor']),
            'asistentes'   => $asistenteModel->getByAsistencia($id),
            'tiposReunion' => RegistroAsistenciaController::TIPOS_REUNION,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Registro de Asistencia',
            'content' => view('client/inspecciones/registro_asistencia_view', $data),
        ]);
    }

    // ─── ENTREGAS DE DOTACION (read-only) ────────────────────

    public function listEntregaDotacion()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $entregaModel = new EntregaDotacionModel();
        $asistenteModel = new EntregaDotacionAsistenteModel();
        $entregas = $entregaModel->getByCliente((int)$clientId);

        foreach ($entregas as &$e) {
            $e['total_asistentes'] = $asistenteModel
                ->where('id_entrega_dotacion', $e['id'])->countAllResults(false);
            $e['total_firmados'] = $asistenteModel
                ->where('id_entrega_dotacion', $e['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
        }

        $clientModel = new ClientModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Entregas de Dotación',
            'content' => view('client/inspecciones/entrega_dotacion_list', [
                'entregas' => $entregas,
            ]),
        ]);
    }

    public function viewEntregaDotacion($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $entregaModel = new EntregaDotacionModel();
        $entrega = $entregaModel->find($id);
        if (!$entrega || (int)$entrega['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones/entrega-dotacion')->with('error', 'Entrega no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new EntregaDotacionAsistenteModel();
        $itemModel = new EntregaDotacionItemModel();
        $tallaModel = new \App\Models\EntregaDotacionAsistenteTallaModel();

        $items = $itemModel->getByEntrega((int)$id);
        $asistentes = $asistenteModel->getByEntrega((int)$id);
        foreach ($asistentes as &$a) {
            $a['tallas_map'] = $tallaModel->getMapByAsistente((int)$a['id']);
        }
        unset($a);

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Entrega de Dotación',
            'content' => view('client/inspecciones/entrega_dotacion_view', [
                'entrega'    => $entrega,
                'items'      => $items,
                'cliente'    => $clientModel->find($entrega['id_cliente']),
                'consultor'  => $entrega['id_consultor'] ? $consultantModel->find($entrega['id_consultor']) : null,
                'asistentes' => $asistentes,
            ]),
        ]);
    }

    // ─── INSPECCIONES DE EPP (read-only) ─────────────────────

    public function listInspeccionEpp()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $inspeccionModel = new InspeccionEppModel();
        $hallazgoModel = new HallazgoEppModel();
        $inspecciones = $inspeccionModel->getByCliente((int)$clientId);

        foreach ($inspecciones as &$insp) {
            $insp['total_hallazgos'] = $hallazgoModel
                ->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $clientModel = new ClientModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspecciones de EPP',
            'content' => view('client/inspecciones/inspeccion_epp_list', [
                'inspecciones' => $inspecciones,
            ]),
        ]);
    }

    public function viewInspeccionEpp($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $inspeccionModel = new InspeccionEppModel();
        $hallazgoModel = new HallazgoEppModel();
        $inspeccion = $inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de EPP',
            'content' => view('client/inspecciones/inspeccion_epp_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null,
                'hallazgos'  => $hallazgoModel->getByInspeccion((int)$id),
            ]),
        ]);
    }
}
