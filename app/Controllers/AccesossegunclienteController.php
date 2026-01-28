<?php

namespace App\Controllers;

use App\Models\AccesoModel;
use CodeIgniter\Controller;

class AccesossegunclienteController extends Controller
{
    protected $accesoModel;
    protected $baseUrl;

    public function __construct()
    {
        $this->accesoModel = new AccesoModel();
        $this->baseUrl = base_url(); // URL base para vistas
    }

    public function listaccesosseguncliente()
    {
        $filters = $this->request->getGet(); // Obtener filtros del query string
        $query = $this->accesoModel;

        // Aplicar filtros si existen (con COLLATE para evitar errores en MySQL 8)
        $db = \Config\Database::connect();
        $collate = 'COLLATE utf8mb4_general_ci';
        if (!empty($filters['nombre'])) {
            $nombreEsc = $db->escapeLikeString($filters['nombre']);
            $query->where("nombre {$collate} LIKE '%{$nombreEsc}%'", null, false);
        }
        if (!empty($filters['url'])) {
            $urlEsc = $db->escapeLikeString($filters['url']);
            $query->where("url {$collate} LIKE '%{$urlEsc}%'", null, false);
        }

        $data['accesos'] = $query->findAll();
        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/listaccesosseguncliente', $data);
    }

    public function addaccesosseguncliente()
    {
        $data['baseUrl'] = $this->baseUrl;
        return view('consultant/addaccesosseguncliente', $data);
    }

    public function addpostaccesosseguncliente()
    {
        $data = $this->request->getPost();
        $this->accesoModel->insert($data);

        return redirect()->to('/accesosseguncliente/list');
    }

    public function editaccesosseguncliente($id)
    {
        $data['acceso'] = $this->accesoModel->find($id);
        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/editaccesosseguncliente', $data);
    }

    public function editpostaccesosseguncliente()
    {
        $id = $this->request->getPost('id_acceso');
        $data = $this->request->getPost();

        $this->accesoModel->update($id, $data);

        return redirect()->to('/accesosseguncliente/list');
    }

    public function deleteaccesosseguncliente($id)
    {
        $this->accesoModel->delete($id);

        return redirect()->to('/accesosseguncliente/list');
    }
}
