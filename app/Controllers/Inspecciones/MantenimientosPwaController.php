<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\MantenimientoModel;
use App\Models\VencimientosMantenimientoModel;
use App\Models\ClientModel;

class MantenimientosPwaController extends BaseController
{
    protected $vencimientoModel;
    protected $mantenimientoModel;
    protected $clientModel;

    public function __construct()
    {
        $this->vencimientoModel = new VencimientosMantenimientoModel();
        $this->mantenimientoModel = new MantenimientoModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Listado de vencimientos por cliente
     */
    public function list($idCliente = null)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        $vencimientos = [];
        $clienteSeleccionado = null;

        if ($idCliente) {
            $clienteSeleccionado = $this->clientModel->find($idCliente);

            $builder = $this->vencimientoModel
                ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
                ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
                ->where('tbl_vencimientos_mantenimientos.id_cliente', $idCliente);

            if ($role !== 'admin') {
                $builder->where('tbl_vencimientos_mantenimientos.id_consultor', $userId);
            }

            $vencimientos = $builder->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
                ->findAll();
        }

        // Enriquecer datos de cada vencimiento
        $hoy = date('Y-m-d');
        $conteos = ['sin ejecutar' => 0, 'ejecutado' => 0, 'CERRADA' => 0, 'CERRADA POR FIN CONTRATO' => 0];

        foreach ($vencimientos as &$v) {
            $estado = $v['estado_actividad'];
            $conteos[$estado] = ($conteos[$estado] ?? 0) + 1;

            if ($estado === 'sin ejecutar') {
                $diff = (strtotime($v['fecha_vencimiento']) - strtotime($hoy)) / 86400;
                $v['dias_diff'] = (int) round($diff);

                if ($diff < 0) {
                    $v['color'] = 'danger';
                    $v['label'] = abs($v['dias_diff']) . ' dia(s) vencido';
                } elseif ($diff <= 15) {
                    $v['color'] = 'warning';
                    $v['label'] = 'en ' . $v['dias_diff'] . ' dia(s)';
                } else {
                    $v['color'] = 'gold';
                    $v['label'] = 'en ' . $v['dias_diff'] . ' dia(s)';
                }
            } else {
                $v['color'] = 'success';
                $v['label'] = $estado;
            }
        }
        unset($v);

        $data = [
            'title'               => 'Mantenimientos',
            'idCliente'           => $idCliente,
            'clienteSeleccionado' => $clienteSeleccionado,
            'vencimientos'        => $vencimientos,
            'conteos'             => $conteos,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/mantenimientos/list', $data),
            'title'   => 'Mantenimientos',
        ]);
    }

    /**
     * Formulario crear nuevo vencimiento
     */
    public function create($idCliente = null)
    {
        $data = [
            'title'     => 'Nuevo Vencimiento',
            'idCliente' => $idCliente,
            'cliente'   => $idCliente ? $this->clientModel->find($idCliente) : null,
            'edit'      => false,
            'vencimiento' => null,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/mantenimientos/form', $data),
            'title'   => 'Nuevo Vencimiento',
        ]);
    }

    /**
     * Guardar nuevo vencimiento
     */
    public function store()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $idMantenimiento = $this->request->getPost('id_mantenimiento');
        $fechaVencimiento = $this->request->getPost('fecha_vencimiento');
        $observaciones = $this->request->getPost('observaciones');

        if (!$idCliente || !$idMantenimiento || !$fechaVencimiento) {
            session()->setFlashdata('error', 'Faltan campos obligatorios');
            return redirect()->back();
        }

        $this->vencimientoModel->insert([
            'id_cliente'        => $idCliente,
            'id_mantenimiento'  => $idMantenimiento,
            'id_consultor'      => session()->get('user_id'),
            'fecha_vencimiento' => $fechaVencimiento,
            'estado_actividad'  => 'sin ejecutar',
            'observaciones'     => $observaciones,
        ]);

        session()->setFlashdata('msg', 'Vencimiento creado');
        return redirect()->to('/inspecciones/mantenimientos/cliente/' . $idCliente);
    }

    /**
     * Formulario editar vencimiento
     */
    public function edit($id)
    {
        $vencimiento = $this->vencimientoModel
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->find($id);

        if (!$vencimiento) {
            session()->setFlashdata('error', 'Vencimiento no encontrado');
            return redirect()->to('/inspecciones/mantenimientos');
        }

        $data = [
            'title'       => 'Editar Vencimiento',
            'idCliente'   => $vencimiento['id_cliente'],
            'cliente'     => $this->clientModel->find($vencimiento['id_cliente']),
            'edit'        => true,
            'vencimiento' => $vencimiento,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/mantenimientos/form', $data),
            'title'   => 'Editar Vencimiento',
        ]);
    }

    /**
     * Guardar cambios de vencimiento
     */
    public function update($id)
    {
        $vencimiento = $this->vencimientoModel->find($id);
        if (!$vencimiento) {
            session()->setFlashdata('error', 'Vencimiento no encontrado');
            return redirect()->to('/inspecciones/mantenimientos');
        }

        $this->vencimientoModel->update($id, [
            'fecha_vencimiento' => $this->request->getPost('fecha_vencimiento'),
            'observaciones'     => $this->request->getPost('observaciones'),
        ]);

        session()->setFlashdata('msg', 'Vencimiento actualizado');
        return redirect()->to('/inspecciones/mantenimientos/cliente/' . $vencimiento['id_cliente']);
    }

    /**
     * AJAX: Marcar como ejecutado
     */
    public function markEjecutado($id)
    {
        $vencimiento = $this->vencimientoModel->find($id);
        if (!$vencimiento) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado']);
        }

        $this->vencimientoModel->update($id, [
            'estado_actividad'  => 'ejecutado',
            'fecha_realizacion' => date('Y-m-d'),
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Eliminar vencimiento (solo sin ejecutar)
     */
    public function delete($id)
    {
        $vencimiento = $this->vencimientoModel->find($id);
        if (!$vencimiento) {
            session()->setFlashdata('error', 'No encontrado');
            return redirect()->to('/inspecciones/mantenimientos');
        }

        if ($vencimiento['estado_actividad'] !== 'sin ejecutar') {
            session()->setFlashdata('error', 'Solo se pueden eliminar vencimientos sin ejecutar');
            return redirect()->to('/inspecciones/mantenimientos/cliente/' . $vencimiento['id_cliente']);
        }

        $idCliente = $vencimiento['id_cliente'];
        $this->vencimientoModel->delete($id);

        session()->setFlashdata('msg', 'Vencimiento eliminado');
        return redirect()->to('/inspecciones/mantenimientos/cliente/' . $idCliente);
    }

    /**
     * API: Catalogo de tipos de mantenimiento
     */
    public function apiCatalog()
    {
        $catalog = $this->mantenimientoModel->orderBy('detalle_mantenimiento', 'ASC')->findAll();
        return $this->response->setJSON($catalog);
    }

    /**
     * API: Crear nuevo tipo en catalogo
     */
    public function apiAddCatalog()
    {
        $detalle = trim($this->request->getJSON(true)['detalle_mantenimiento'] ?? '');
        if (empty($detalle)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Nombre requerido']);
        }

        $this->mantenimientoModel->insert(['detalle_mantenimiento' => $detalle]);
        $id = $this->mantenimientoModel->getInsertID();

        return $this->response->setJSON([
            'success' => true,
            'id_mantenimiento' => $id,
            'detalle_mantenimiento' => $detalle,
        ]);
    }

    /**
     * API: Todos los vencimientos de un cliente
     */
    public function apiVencimientos($idCliente)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        $builder = $this->vencimientoModel
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $idCliente);

        if ($role !== 'admin') {
            $builder->where('tbl_vencimientos_mantenimientos.id_consultor', $userId);
        }

        $vencimientos = $builder->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
            ->findAll();

        return $this->response->setJSON($vencimientos);
    }
}
