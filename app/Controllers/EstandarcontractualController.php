<?php

namespace App\Controllers;

use App\Models\EstandarModel;
use CodeIgniter\Controller;

class EstandarcontractualController extends Controller
{
    protected $estandarModel;
    protected $baseUrl;

    public function __construct()
    {
        $this->estandarModel = new EstandarModel();
        $this->baseUrl = base_url(); // URL base para vistas
    }

    public function listestandarcontractual()
    {
        $filters = $this->request->getGet(); // Obtener filtros del query string
        $query = $this->estandarModel;

        // Aplicar filtros si existen (con COLLATE para evitar errores en MySQL 8)
        if (!empty($filters['nombre'])) {
            $db = \Config\Database::connect();
            $nombreEsc = $db->escapeLikeString($filters['nombre']);
            $query->where("nombre COLLATE utf8mb4_general_ci LIKE '%{$nombreEsc}%'", null, false);
        }

        $data['estandares'] = $query->findAll();
        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/listestandarcontractual', $data);
    }

    public function addestandarcontractual()
    {
        $data['baseUrl'] = $this->baseUrl;
        return view('consultant/addestandarcontractual', $data);
    }

    public function addpostestandarcontractual()
    {
        $data = $this->request->getPost();
        $this->estandarModel->insert($data);

        return redirect()->to('/estandarcontractual/list');
    }

    public function editestandarcontractual($id)
    {
        $data['estandar'] = $this->estandarModel->find($id);
        $data['baseUrl'] = $this->baseUrl;

        return view('consultant/editestandarcontractual', $data);
    }

    public function editpostestandarcontractual()
    {
        $id = $this->request->getPost('id_estandar');
        $data = $this->request->getPost();

        $this->estandarModel->update($id, $data);

        return redirect()->to('/estandarcontractual/list');
    }

    public function deleteestandarcontractual($id)
    {
        if ($id) { // Verificar que el ID exista
            $this->estandarModel->delete($id); // Realizar la eliminación segura
            return redirect()->to('/estandarcontractual/list'); // Redirigir a la lista
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Registro no encontrado');
        }
    }
    
}
