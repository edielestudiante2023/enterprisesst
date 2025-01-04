<?php 

namespace App\Controllers;

use App\Models\TestModel;
use CodeIgniter\Controller;

class TestController extends Controller
{
    public function index()
    {
        return view('consultant/add_test');
    }

    public function addTestPost()
    {
        $testModel = new TestModel();

        $data = [
            'nombre_test' => $this->request->getVar('nombre_test'),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($testModel->save($data)) {
            session()->setFlashdata('msg', 'Registro agregado exitosamente');
        } else {
            session()->setFlashdata('msg', 'Error al agregar el registro');
        }

        return redirect()->to('/addTest');
    }
}
?>
