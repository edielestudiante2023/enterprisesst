<?php

namespace App\Controllers;

use App\Models\MantenimientoModel;

class MantenimientoController extends BaseController
{
    protected $mantenimientoModel;

    public function __construct()
    {
        $this->mantenimientoModel = new MantenimientoModel();
    }

    public function findAll()
    {
        $data['mantenimientos'] = $this->mantenimientoModel->findAll();
        return view('consultant/listMantenimientoController', $data);
    }

    public function addMantenimientoController()
    {
        return view('consultant/addMantenimientoController');
    }

    public function addPostMantenimientoController()
    {
        $this->mantenimientoModel->save([
            'detalle_mantenimiento' => $this->request->getPost('detalle_mantenimiento'),
        ]);
        return redirect()->to(base_url('mantenimientos'));
    }

    public function editMantenimientoController($id)
    {
        $data['mantenimiento'] = $this->mantenimientoModel->find($id);
        return view('consultant/editMantenimientoController', $data);
    }

    public function editPostMantenimientoController($id)
    {
        $this->mantenimientoModel->update($id, [
            'detalle_mantenimiento' => $this->request->getPost('detalle_mantenimiento'),
        ]);
        return redirect()->to(base_url('mantenimientos'));
    }

    public function deleteMantenimientoController($id)
    {
        $this->mantenimientoModel->delete($id);
        return redirect()->to(base_url('mantenimientos'));
    }
}
