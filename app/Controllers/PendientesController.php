<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel; // Para obtener información del cliente
use CodeIgniter\Controller;

class PendientesController extends Controller
{
    // Listar todos los pendientes
    public function listPendientes()
    {
        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        // Obtener todos los pendientes
        $pendientes = $pendientesModel->findAll();

        // Añadir el nombre del cliente a cada pendiente
        foreach ($pendientes as &$pendiente) {
            $cliente = $clientModel->find($pendiente['id_cliente']);
            $pendiente['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente desconocido';
        }

        $data['pendientes'] = $pendientes;

        return view('consultant/list_pendientes', $data);
    }


    // Mostrar formulario para agregar nuevo pendiente
    public function addPendiente()
    {
        $clientModel = new ClientModel();
        $data['clientes'] = $clientModel->findAll(); // Obtener todos los clientes

        return view('consultant/add_pendiente', $data); // Cargar la vista del formulario
    }

    // Guardar nuevo pendiente
    public function addPendientePost()
    {
        $pendientesModel = new PendientesModel();

        // Recogemos los datos del formulario
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'responsable' => $this->request->getPost('responsable'),
            'tarea_actividad' => $this->request->getPost('tarea_actividad'),
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'estado' => $this->request->getPost('estado'),
            'estado_avance' => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla' => $this->request->getPost('evidencia_para_cerrarla'),
        ];

        // Obtener la fecha de creación
        $createdAt = date('Y-m-d H:i:s'); // La fecha actual como fecha de creación
        $data['created_at'] = $createdAt;

        // Validar que la fecha de cierre no sea menor que la fecha de creación
        if ($data['fecha_cierre'] && strtotime($data['fecha_cierre']) < strtotime($createdAt)) {
            return redirect()->back()->with('msg', 'Error: La fecha de cierre no puede ser anterior a la fecha de creación.')->withInput();
        }

        // Validar que si hay fecha de cierre, el estado no puede ser ABIERTA
        if (!empty($data['fecha_cierre']) && $data['estado'] === 'ABIERTA') {
            return redirect()->back()->with('msg', 'Error: No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.')->withInput();
        }

        // Calcular conteo_dias
        if ($data['estado'] === 'ABIERTA') {
            $data['conteo_dias'] = (strtotime(date('Y-m-d H:i:s')) - strtotime($createdAt)) / (60 * 60 * 24);
        } elseif ($data['estado'] === 'CERRADA' && !empty($data['fecha_cierre'])) {
            $data['conteo_dias'] = (strtotime($data['fecha_cierre']) - strtotime($createdAt)) / (60 * 60 * 24);
        }

        if ($pendientesModel->insert($data)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar pendiente');
        }
    }

    // Mostrar formulario para editar un pendiente
    public function editPendiente($id)
    {
        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        $data['pendiente'] = $pendientesModel->find($id); // Obtener el pendiente que se va a editar
        $data['clientes'] = $clientModel->findAll(); // Obtener todos los clientes

        return view('consultant/edit_pendiente', $data); // Cargar la vista del formulario
    }

    // Actualizar pendiente
    public function editPendientePost($id)
    {
        $pendientesModel = new PendientesModel();

        // Recogemos los datos del formulario
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'responsable' => $this->request->getPost('responsable'),
            'tarea_actividad' => $this->request->getPost('tarea_actividad'),
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'estado' => $this->request->getPost('estado'),
            'estado_avance' => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla' => $this->request->getPost('evidencia_para_cerrarla'),
        ];

        // Obtener la fecha de creación
        $pendienteActual = $pendientesModel->find($id);
        $createdAt = $pendienteActual['created_at'];

        // Validar que la fecha de cierre no sea menor que la fecha de creación
        if ($data['fecha_cierre'] && strtotime($data['fecha_cierre']) < strtotime($createdAt)) {
            return redirect()->back()->with('msg', 'Error: La fecha de cierre no puede ser anterior a la fecha de creación.')->withInput();
        }

        // Validar que si hay fecha de cierre, el estado no puede ser ABIERTA
        if (!empty($data['fecha_cierre']) && $data['estado'] === 'ABIERTA') {
            return redirect()->back()->with('msg', 'Error: No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.')->withInput();
        }

        // Calcular conteo_dias
        if ($data['estado'] === 'ABIERTA') {
            $data['conteo_dias'] = (strtotime(date('Y-m-d H:i:s')) - strtotime($createdAt)) / (60 * 60 * 24);
        } elseif ($data['estado'] === 'CERRADA' && !empty($data['fecha_cierre'])) {
            $data['conteo_dias'] = (strtotime($data['fecha_cierre']) - strtotime($createdAt)) / (60 * 60 * 24);
        }

        if ($pendientesModel->update($id, $data)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar pendiente');
        }
    }

    // Eliminar pendiente
    public function deletePendiente($id)
    {
        $pendientesModel = new PendientesModel();

        if ($pendientesModel->delete($id)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar pendiente');
        }
    }
}
