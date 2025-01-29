<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PlanModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PlanController extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo
        return view('consultant/cargarplandetrabjo');
    }

    public function upload()
    {
        $file = $this->request->getFile('file');
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Mover el archivo a la carpeta writable/uploads
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            try {
                // Usar PhpSpreadsheet para leer el archivo
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Validar encabezados
                $headers = $rows[0];
                $requiredHeaders = [
                    'id_cliente',
                    
                    'phva_plandetrabajo',
                    'numeral_plandetrabajo',
                    'actividad_plandetrabajo',
                    'responsable_sugerido_plandetrabajo'
                ];

                if ($headers !== $requiredHeaders) {
                    return redirect()->to(base_url('consultant/plan'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos: ' . implode(', ', $requiredHeaders));
                }

                // Procesar los datos (a partir de la fila 2)
                $planModel = new PlanModel();
                foreach (array_slice($rows, 1) as $row) {
                    $data = [
                        'id_cliente' => $row[0],
                        
                        'phva_plandetrabajo' => $row[1],
                        'numeral_plandetrabajo' => $row[2],
                        'actividad_plandetrabajo' => $row[3],
                        'responsable_sugerido_plandetrabajo' => $row[4],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Insertar los datos en la base de datos
                    $planModel->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/plan'))
                    ->with('success', 'Archivo cargado exitosamente.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/plan'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/plan'))
            ->with('error', 'Error al subir el archivo.');
    }
}
