<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ClientKpiModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class csvkpiempresasController extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvkpisempresas');
    }

    public function upload()
    {
        $file = $this->request->getFile('file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Mover el archivo a la carpeta writable/uploads
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            // Leer el archivo CSV utilizando PhpSpreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Asumimos que la primera fila contiene los encabezados correctos
            $headers = $rows[0];

            $model = new ClientKpiModel();
            // Procesar las filas (omitiendo la primera fila de encabezados)
            foreach (array_slice($rows, 1) as $row) {
                // Crear un array asociativo usando los encabezados como claves
                $data = array_combine($headers, $row);
                // Insertar el registro en la base de datos
                $model->insert($data);
            }

            return redirect()->to(base_url('consultant/csvkpisempresas'))
                ->with('success', 'Archivo CSV procesado con éxito.');
        }

        return redirect()->to(base_url('consultant/csvkpisempresas'))
            ->with('error', 'Error al subir el archivo.');
    }
}
