<?php

namespace App\Controllers;

use App\Models\VigiaModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class VigiaController extends Controller
{
    // Muestra la lista de vigías
    public function listVigias()
    {
        $vigiaModel = new VigiaModel();
        $clientModel = new ClientModel();
    
        $vigias = $vigiaModel->findAll(); // Obtener todos los vigías
        $clientes = $clientModel->findAll(); // Obtener todos los clientes
    
        // Pasar ambas variables a la vista
        return view('consultant/list_vigias', [
            'vigias' => $vigias,
            'clientes' => $clientes
        ]);
    }
    

    // Muestra el formulario para añadir un vigía
    public function addVigia()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll(); // Obtener todos los clientes para el dropdown

        return view('consultant/add_vigia', ['clients' => $clients]);
    }

    // Procesa el formulario para añadir un nuevo vigía (método POST)
    public function saveVigia()
    {
        $vigiaModel = new VigiaModel();
        
        $file = $this->request->getFile('firma_vigia');
        $firmaFileName = null;

        // Verificar y mover la firma del vigía
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $firmaFileName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads', $firmaFileName);
        }

        $data = [
            'nombre_vigia' => $this->request->getVar('nombre_vigia'),
            'cedula_vigia' => $this->request->getVar('cedula_vigia'),
            'periodo_texto' => $this->request->getVar('periodo_texto'),
            'firma_vigia' => $firmaFileName,
            'id_cliente' => $this->request->getVar('id_cliente')
        ];

        if ($vigiaModel->save($data)) {
            return redirect()->to('/listVigias')->with('msg', 'Vigía agregado exitosamente');
        } else {
            return redirect()->to('/addVigia')->with('msg', 'Error al agregar vigía');
        }
    }

    // Muestra el formulario para editar un vigía
    public function editVigia($id)
    {
        $vigiaModel = new VigiaModel();
        $clientModel = new ClientModel();

        $vigia = $vigiaModel->find($id);
        $clients = $clientModel->findAll(); // Obtener todos los clientes para el dropdown

        if (!$vigia) {
            return redirect()->to('/listVigias')->with('error', 'Vigía no encontrado');
        }

        return view('consultant/edit_vigia', ['vigia' => $vigia, 'clients' => $clients]);
    }

    // Procesa el formulario para actualizar un vigía (método POST)
    public function updateVigia($id)
    {
        $vigiaModel = new VigiaModel();
        $vigia = $vigiaModel->find($id);

        if (!$vigia) {
            return redirect()->to('/listVigias')->with('msg', 'Vigía no encontrado');
        }

        $data = [
            'nombre_vigia' => $this->request->getVar('nombre_vigia'),
            'cedula_vigia' => $this->request->getVar('cedula_vigia'),
            'periodo_texto' => $this->request->getVar('periodo_texto'),
            'id_cliente' => $this->request->getVar('id_cliente')
        ];

        $file = $this->request->getFile('firma_vigia');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $firmaFileName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads', $firmaFileName);
            $data['firma_vigia'] = $firmaFileName;
        }

        if ($vigiaModel->update($id, $data)) {
            return redirect()->to('/listVigias')->with('msg', 'Vigía actualizado exitosamente');
        } else {
            return redirect()->to('editVigia/' . $id)->with('msg', 'Error al actualizar vigía');
        }
    }

    // Elimina un vigía
    public function deleteVigia($id)
    {
        $vigiaModel = new VigiaModel();

        if ($vigiaModel->delete($id)) {
            return redirect()->to('/listVigias')->with('msg', 'Vigía eliminado exitosamente');
        } else {
            return redirect()->to('/listVigias')->with('msg', 'Error al eliminar vigía');
        }
    }
}
