<?php

namespace App\Controllers;

use App\Models\EstandarAccesoModel;
use App\Models\EstandarModel;
use App\Models\AccesoModel;

class AccesosseguncontractualidadController extends BaseController
{
    protected $estandarAccesoModel;
    protected $estandarModel;
    protected $accesoModel;
    protected $baseUrl;

    public function __construct()
    {
        $this->estandarAccesoModel = new EstandarAccesoModel();
        $this->estandarModel = new EstandarModel();
        $this->accesoModel = new AccesoModel();
        $this->baseUrl = base_url(); // URL base para las vistas
    }

    public function listaccesosseguncontractualidad()
    {
        $data['estandares_accesos'] = $this->estandarAccesoModel
            ->select('estandares_accesos.id, estandares.nombre as estandar, accesos.nombre as acceso')
            ->join('estandares', 'estandares.id_estandar = estandares_accesos.id_estandar')
            ->join('accesos', 'accesos.id_acceso = estandares_accesos.id_acceso')
            ->findAll();

        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/listaccesosseguncontractualidad', $data);
    }

    public function addaccesosseguncontractualidad()
    {
        $data['estandares'] = $this->estandarModel->findAll();
        $data['accesos'] = $this->accesoModel->findAll();
        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/addaccesosseguncontractualidad', $data);
    }

    public function addpostaccesosseguncontractualidad()
    {
        $data = $this->request->getPost();
        $this->estandarAccesoModel->insert($data);

        return redirect()->to('/accesosseguncontractualidad/list');
    }

    public function editaccesosseguncontractualidad($id)
    {
        $data['estandar_acceso'] = $this->estandarAccesoModel->find($id);
        $data['estandares'] = $this->estandarModel->findAll();
        $data['accesos'] = $this->accesoModel->findAll();
        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/editaccesosseguncontractualidad', $data);
    }

    public function editpostaccesosseguncontractualidad()
    {
        $id = $this->request->getPost('id');
        $data = $this->request->getPost();

        $this->estandarAccesoModel->update($id, $data);

        return redirect()->to('/accesosseguncontractualidad/list');
    }

    public function deleteaccesosseguncontractualidad($id)
    {
        $this->estandarAccesoModel->delete($id);

        return redirect()->to('/accesosseguncontractualidad/list');
    }
}
