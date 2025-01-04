<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use CodeIgniter\Controller;

class DocumentoController extends Controller
{
    public function mostrarDocumento()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id');

        // Instanciar los modelos
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Recuperar la información del cliente
        $cliente = $clientModel->find($clientId);

        // Recuperar la información del consultor asignado al cliente
        $consultor = $consultantModel->find($cliente['id_consultor']);

        // Verificar si se encontraron los datos
        if (!$cliente || !$consultor) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información requerida.');
        }

        // Pasar los datos a la vista
        $data = [
            'cliente' => $cliente,
            'consultor' => $consultor
        ];

        return view('client/documento', $data);
    }

    public function uploadPhoto()
{
    $consultantModel = new ConsultantModel();
    
    // Verificar si el formulario fue enviado
    if ($this->request->getMethod() === 'post') {
        $photo = $this->request->getFile('foto_consultor');
        
        if ($photo->isValid() && !$photo->hasMoved()) {
            // Generar un nombre único para la foto y moverla a la carpeta de destino
            $photoName = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads', $photoName);
            
            // Actualizar la base de datos con el nombre de la foto
            $consultantModel->update(session()->get('user_id'), ['foto_consultor' => $photoName]);
        }
    }

    return redirect()->to('/dashboardconsultant');
}

}
