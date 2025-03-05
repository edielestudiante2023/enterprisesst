<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CronogcapacitacionModel; // Modelo existente
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvCronogramaDeCapacitacion extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvcronogramadecapacitacion');
    }

    private function formatDate($date) {
        if (empty($date)) return null;
        
        // Intenta convertir la fecha al formato correcto
        $timestamp = strtotime($date);
        if ($timestamp === false) return null;
        
        return date('Y-m-d', $timestamp);
    }

    public function upload()
    {
        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            // Mover el archivo a la carpeta writable/uploads
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            try {
                // Leer el archivo CSV utilizando PhpSpreadsheet
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Validar encabezados
                $headers = $rows[0];
                $requiredHeaders = [
                    'id_capacitacion',
                    'id_cliente',
                    'fecha_programada',
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
                        'fecha_programada' => $this->formatDate($row[2]),
                        'estado' => $row[3],
                        'perfil_de_asistentes' => $row[4],
                        'nombre_del_capacitador' => $row[5],
                        'horas_de_duracion_de_la_capacitacion' => $row[6],
                        'indicador_de_realizacion_de_la_capacitacion' => $row[7],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Insertar los datos
                    $model->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/csvcronogramadecapacitacion'))
                    ->with('success', 'Archivo cargado exitosamente.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/csvcronogramadecapacitacion'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/csvcronogramadecapacitacion'))
            ->with('error', 'Error al subir el archivo.');
    }
}
