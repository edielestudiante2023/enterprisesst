<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ClientPoliciesModel; // Modelo para la tabla 'client_policies'
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvPoliticasParaDocumentosController extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvpoliticasparadocumentos');
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
                // Leer el archivo CSV utilizando PhpSpreadsheet
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Validar encabezados
                $headers = $rows[0];
                $requiredHeaders = ['client_id', 'policy_type_id', 'policy_content'];

                if ($headers !== $requiredHeaders) {
                    return redirect()->to(base_url('consultant/csvpoliticasparadocumentos'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos: ' . implode(', ', $requiredHeaders));
                }

                // Procesar las filas (omitimos la primera fila de encabezados)
                $model = new ClientPoliciesModel();
                foreach (array_slice($rows, 1) as $row) {
                    // Crear un array asociativo para insertar en la base de datos
                    $data = [
                        'client_id' => $row[0],
                        'policy_type_id' => $row[1],
                        'policy_content' => $row[2],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Insertar los datos en la tabla
                    $model->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/csvpoliticasparadocumentos'))
                    ->with('success', 'Archivo cargado exitosamente.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/csvpoliticasparadocumentos'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/csvpoliticasparadocumentos'))
            ->with('error', 'Error al subir el archivo.');
    }
}
