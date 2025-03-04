<?php

namespace App\Controllers;

use App\Models\CsvUploadModel;
use CodeIgniter\Controller;

class CsvUploadController extends Controller
{
    public function index()
    {
        // Muestra la vista con el formulario para subir el CSV
        return view('consultant/actualizar_pta_cliente/actualizar_pta_cliente');
    }

    public function upload()
    {
        // 1. Obtener el archivo
        $file = $this->request->getFile('csv_file');

        // 2. Validar el archivo
        if (!$file->isValid() || $file->getClientExtension() !== 'csv') {
            return redirect()
                ->to(base_url('consultant/actualizar_pta_cliente'))
                ->with('error', 'Archivo inválido. Solo se permiten archivos CSV.');
        }

        // 3. Leer el contenido del CSV en un array
        $csvData = [];
        if (($handle = fopen($file->getTempName(), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $csvData[] = $data;
            }
            fclose($handle);
        }

        // Si el CSV está vacío, mostramos error
        if (count($csvData) < 1) {
            return redirect()->to(base_url('consultant/actualizar_pta_cliente'))
                ->with('error', 'El archivo CSV está vacío o no tiene datos válidos.');
        }

        // Verificar si la primera fila es un encabezado (buscando "id_ptacliente")
        if (isset($csvData[0][0]) && strpos($csvData[0][0], 'id_ptacliente') !== false) {
            array_shift($csvData);
        }

        // Si después de quitar el encabezado no hay filas, se muestra error
        if (count($csvData) < 1) {
            return redirect()->to(base_url('consultant/actualizar_pta_cliente'))
                ->with('error', 'El archivo CSV está vacío o no tiene datos válidos.');
        }

        // Enviar los datos al modelo para su procesamiento
        $csvModel = new CsvUploadModel();
        $result   = $csvModel->processCsvData($csvData);

        // Redirigir a la misma vista con un mensaje de resultado
        return redirect()
            ->to(base_url('consultant/actualizar_pta_cliente'))
            ->with('message', $result['message']);
    }
}
