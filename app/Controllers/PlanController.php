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
        
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            
            // Usar PhpSpreadsheet para leer el archivo
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Validar encabezados
            $headers = $rows[0];
            $requiredHeaders = ['id_cliente', 'id_plandetrabajo', 'phva_plandetrabajo', 'numeral_plandetrabajo', 'actividad_plandetrabajo', 'responsable_sugerido_plandetrabajo'];
            if ($headers !== $requiredHeaders) {
                return redirect()->to(base_url('consultant/plan'))->with('error', 'El archivo no tiene los encabezados requeridos.');
            }

            // Procesar los datos (a partir de la fila 2)
            $planModel = new PlanModel();
            foreach (array_slice($rows, 1) as $row) {
                $data = [
                    'id_cliente' => $row[0],
                    'id_plandetrabajo' => $row[1],
                    'phva_plandetrabajo' => $row[2],
                    'numeral_plandetrabajo' => $row[3],
                    'actividad_plandetrabajo' => $row[4],
                    'responsable_sugerido_plandetrabajo' => $row[5],
                ];
                $planModel->insert($data);
            }

            return redirect()->to(base_url('consultant/plan'))->with('success', 'Archivo cargado exitosamente.');
        }

        return redirect()->to(base_url('consultant/plan'))->with('error', 'Error al subir el archivo.');
    }
}
