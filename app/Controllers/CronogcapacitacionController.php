<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use App\Models\CapacitacionModel;
use CodeIgniter\Controller;

class CronogcapacitacionController extends Controller
{
    // Listar todos los cronogramas de capacitación
    public function listcronogCapacitacion()
    {
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtenemos todos los cronogramas
        $cronogramas = $cronogModel->findAll();

        // Iteramos los cronogramas para obtener los datos relacionados (nombre del cliente y capacitación)
        foreach ($cronogramas as &$cronograma) {
            // Obtenemos el nombre del cliente
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente['nombre_cliente'];

            // Obtenemos el nombre de la capacitación y el objetivo
            $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
            $cronograma['nombre_capacitacion'] = $capacitacion['capacitacion'];
            $cronograma['objetivo_capacitacion'] = $capacitacion['objetivo_capacitacion'];
        }

        // Pasamos los datos a la vista
        $data['cronogramas'] = $cronogramas;
        return view('consultant/list_cronogramas', $data);
    }

    // Mostrar formulario para agregar nuevo cronograma de capacitación
    public function addcronogCapacitacion()
    {
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtener clientes y capacitaciones para los selects del formulario
        $data['clientes'] = $clientModel->findAll();
        $data['capacitaciones'] = $capacitacionModel->findAll();

        return view('consultant/add_cronograma', $data);
    }

    // Guardar nuevo cronograma de capacitación
    public function addcronogCapacitacionPost()
    {
        $cronogModel = new CronogcapacitacionModel();

        // Recogemos los datos del formulario
        $numero_asistentes = $this->request->getPost('numero_de_asistentes_a_capacitacion');
        $numero_total_programados = $this->request->getPost('numero_total_de_personas_programadas');

        // Calcular el porcentaje de cobertura
        $porcentaje_cobertura = ($numero_total_programados > 0)
            ? number_format(($numero_asistentes / $numero_total_programados) * 100, 2)
            : 0;

        $data = [
            'id_capacitacion' => $this->request->getPost('id_capacitacion'),
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_programada' => $this->request->getPost('fecha_programada'),
            'fecha_de_realizacion' => $this->request->getPost('fecha_de_realizacion'),
            'estado' => $this->request->getPost('estado'),
            'perfil_de_asistentes' => $this->request->getPost('perfil_de_asistentes'),
            'nombre_del_capacitador' => $this->request->getPost('nombre_del_capacitador'),
            'horas_de_duracion_de_la_capacitacion' => $this->request->getPost('horas_de_duracion_de_la_capacitacion'),
            'indicador_de_realizacion_de_la_capacitacion' => $this->request->getPost('indicador_de_realizacion_de_la_capacitacion'),
            'numero_de_asistentes_a_capacitacion' => $numero_asistentes,
            'numero_total_de_personas_programadas' => $numero_total_programados,
            'porcentaje_cobertura' => $porcentaje_cobertura . '%', // Agregar el símbolo de porcentaje
            'numero_de_personas_evaluadas' => $this->request->getPost('numero_de_personas_evaluadas'),
            'promedio_de_calificaciones' => $this->request->getPost('promedio_de_calificaciones'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        if ($cronogModel->insert($data)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar cronograma');
        }
    }


    // Mostrar formulario para editar cronograma de capacitación
    public function editcronogCapacitacion($id)
    {
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtener el cronograma que se va a editar
        $data['cronograma'] = $cronogModel->find($id);

        // Obtener listas de clientes y capacitaciones para los selects del formulario
        $data['clientes'] = $clientModel->findAll();
        $data['capacitaciones'] = $capacitacionModel->findAll();

        return view('consultant/edit_cronograma', $data);
    }

    // Actualizar cronograma de capacitación
    public function editcronogCapacitacionPost($id)
    {
        $cronogModel = new CronogcapacitacionModel();

        $numero_asistentes = $this->request->getPost('numero_de_asistentes_a_capacitacion');
        $numero_total_programados = $this->request->getPost('numero_total_de_personas_programadas');

        // Calcular el porcentaje de cobertura
        $porcentaje_cobertura = ($numero_total_programados > 0)
            ? number_format(($numero_asistentes / $numero_total_programados) * 100, 2)
            : 0;

        $data = [
            'id_capacitacion' => $this->request->getPost('id_capacitacion'),
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_programada' => $this->request->getPost('fecha_programada'),
            'fecha_de_realizacion' => $this->request->getPost('fecha_de_realizacion'),
            'estado' => $this->request->getPost('estado'),
            'perfil_de_asistentes' => $this->request->getPost('perfil_de_asistentes'),
            'nombre_del_capacitador' => $this->request->getPost('nombre_del_capacitador'),
            'horas_de_duracion_de_la_capacitacion' => $this->request->getPost('horas_de_duracion_de_la_capacitacion'),
            'indicador_de_realizacion_de_la_capacitacion' => $this->request->getPost('indicador_de_realizacion_de_la_capacitacion'),
            'numero_de_asistentes_a_capacitacion' => $numero_asistentes,
            'numero_total_de_personas_programadas' => $numero_total_programados,
            'porcentaje_cobertura' => $porcentaje_cobertura . '%', // Agregar el símbolo de porcentaje
            'numero_de_personas_evaluadas' => $this->request->getPost('numero_de_personas_evaluadas'),
            'promedio_de_calificaciones' => $this->request->getPost('promedio_de_calificaciones'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        if ($cronogModel->update($id, $data)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar cronograma');
        }
    }


    // Eliminar cronograma de capacitación
    public function deletecronogCapacitacion($id)
    {
        // Instancia del modelo
        $cronogModel = new CronogcapacitacionModel();

        // Asegurarse de que el ID no esté vacío y sea válido
        if ($id) {
            // Eliminar el registro
            if ($cronogModel->delete($id)) {
                return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma eliminado exitosamente');
            } else {
                return redirect()->back()->with('msg', 'Error al eliminar el cronograma');
            }
        } else {
            return redirect()->back()->with('msg', 'ID inválido o no proporcionado');
        }
    }
}
