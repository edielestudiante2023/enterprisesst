<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CronogcapacitacionModel; // Tu modelo ya existente
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvCronogramaDeCapacitacion extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvcronogramadecapacitacion');
    }

    public function upload()
    {
        $file = $this->request->getFile('file');
        
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();

            // Leer el archivo CSV utilizando PhpSpreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Validar encabezados
            $headers = $rows[0];
            $requiredHeaders = [
                'id_capacitacion',
                'id_cliente',
                'estado',
                'perfil_de_asistentes',
                'nombre_del_capacitador',
                'horas_de_duracion_de_la_capacitacion',
                'indicador_de_realizacion_de_la_capacitacion'
            ];

            if ($headers !== $requiredHeaders) {
                return redirect()->to(base_url('consultant/csvcronogramadecapacitacion'))
                    ->with('error', 'El archivo no tiene los encabezados requeridos.');
            }

            // Procesar las filas (omitimos la primera fila de encabezados)
            $model = new CronogcapacitacionModel();
            foreach (array_slice($rows, 1) as $row) {
                $data = [
                    'id_capacitacion' => $row[0],
                    'id_cliente' => $row[1],
                    'estado' => $row[2],
                    'perfil_de_asistentes' => $row[3],
                    'nombre_del_capacitador' => $row[4],
                    'horas_de_duracion_de_la_capacitacion' => $row[5],
                    'indicador_de_realizacion_de_la_capacitacion' => $row[6]
                ];
                $model->insert($data);
            }

            return redirect()->to(base_url('consultant/csvcronogramadecapacitacion'))
                ->with('success', 'Archivo cargado exitosamente.');
        }

        return redirect()->to(base_url('consultant/csvcronogramadecapacitacion'))
            ->with('error', 'Error al subir el archivo.');
    }
}
