<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\SimplePendientesModel; // Usar el modelo simplificado
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvPendientes extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvpendientes');
    }

    public function upload()
    {
        $file = $this->request->getFile('file');
        
        if ($file->isValid() && !$file->hasMoved()) {
            // Mover el archivo a la carpeta writable/uploads
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            // Leer el archivo CSV utilizando PhpSpreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Validar encabezados
            $headers = $rows[0];
            $requiredHeaders = ['id_cliente', 'responsable', 'tarea_actividad', 'fecha_cierre', 'estado'];

            if ($headers !== $requiredHeaders) {
                return redirect()->to(base_url('consultant/csvpendientes'))
                    ->with('error', 'El archivo no tiene los encabezados requeridos.');
            }

            // Procesar las filas (omitimos la primera fila de encabezados)
            $model = new SimplePendientesModel(); // Modelo simplificado
            foreach (array_slice($rows, 1) as $row) {
                $data = [
                    'id_cliente' => $row[0],
                    'responsable' => $row[1],
                    'tarea_actividad' => $row[2],
                    'fecha_cierre' => date('Y-m-d', strtotime($row[3])),
                    'estado' => $row[4],
                ];

                // Insertar los datos sin restricciones
                $model->insert($data);
            }

            return redirect()->to(base_url('consultant/csvpendientes'))
                ->with('success', 'Archivo cargado exitosamente.');
        }

        return redirect()->to(base_url('consultant/csvpendientes'))
            ->with('error', 'Error al subir el archivo.');
    }
}
