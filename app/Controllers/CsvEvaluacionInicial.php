<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\EvaluationModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvEvaluacionInicial extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvevaluacioninicial');
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
        $requiredHeaders = [
            'id_cliente', 'ciclo', 'estandar', 'detalle_estandar', 'estandares_minimos', 'numeral',
            'numerales_del_cliente', 'siete', 'veintiun', 'sesenta', 'item_del_estandar', 'evaluacion_inicial',
            'valor', 'puntaje_cuantitativo', 'item', 'criterio', 'modo_de_verificacion', 'calificacion',
            'nivel_de_evaluacion', 'observaciones'
        ];

        if ($headers !== $requiredHeaders) {
            return redirect()->to(base_url('consultant/csvevaluacioninicial'))
                ->with('error', 'El archivo no tiene los encabezados requeridos.');
        }

        // Procesar las filas (omitimos la primera fila de encabezados)
        $model = new \App\Models\SimpleEvaluationModel();
        foreach (array_slice($rows, 1) as $row) {
            $data = [
                'id_cliente' => $row[0],
                'ciclo' => $row[1],
                'estandar' => $row[2],
                'detalle_estandar' => $row[3],
                'estandares_minimos' => $row[4],
                'numeral' => $row[5],
                'numerales_del_cliente' => $row[6],
                'siete' => $row[7],
                'veintiun' => $row[8],
                'sesenta' => $row[9],
                'item_del_estandar' => $row[10],
                'evaluacion_inicial' => $row[11],
                'valor' => $row[12],
                'puntaje_cuantitativo' => $row[13],
                'item' => $row[14],
                'criterio' => $row[15],
                'modo_de_verificacion' => $row[16],
                'calificacion' => $row[17],
                'nivel_de_evaluacion' => $row[18],
                'observaciones' => $row[19],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Insertar los datos
            $model->insert($data);
        }

        return redirect()->to(base_url('consultant/csvevaluacioninicial'))
            ->with('success', 'Archivo cargado exitosamente.');
    }

    return redirect()->to(base_url('consultant/csvevaluacioninicial'))
        ->with('error', 'Error al subir el archivo.');
}

}
