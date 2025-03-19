<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use App\Models\CapacitacionModel;
use CodeIgniter\Controller;

class CronogramaCapacitacionController extends Controller
{
    // Método para mostrar los cronogramas de un cliente específico
    public function listCronogramasCliente($id_cliente)
    {
        // Instanciar los modelos
        $cronogramaModel   = new CronogcapacitacionModel();
        $clientModel       = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtener todos los cronogramas del cliente indicado
        $cronogramas = $cronogramaModel->where('id_cliente', $id_cliente)->findAll();

        // Enriquecer cada registro con el nombre del cliente y el de la capacitación
        foreach ($cronogramas as &$cronograma) {
            // Obtener datos del cliente
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente ? $cliente['nombre_cliente'] : 'No disponible';

            // Obtener datos de la capacitación
            $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
            $cronograma['nombre_capacitacion'] = $capacitacion ? $capacitacion['capacitacion'] : 'No disponible';
        }

        // Enviar los datos a la vista (se utiliza el mismo nombre de vista que antes)
        return view('client/list_cronogramas', ['cronogramas' => $cronogramas]);
    }
}
