<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DocumentVersionModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvVersionesDocumentosController extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvversionesdocumentos');
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
                $requiredHeaders = [
                    'client_id',
                    'policy_type_id',
                    'version_number',
                    'document_type',
                    'acronym',
                    'location',
                    'status',
                    'change_control'
                ];

                if ($headers !== $requiredHeaders) {
                    return redirect()->to(base_url('consultant/csvversionesdocumentos'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos: ' . implode(', ', $requiredHeaders));
                }

                // Procesar las filas (omitimos la primera fila de encabezados)
                $model = new DocumentVersionModel();
                foreach (array_slice($rows, 1) as $row) {
                    // Validar y preparar los datos antes de insertar
                    $data = [
                        'client_id'      => $row[0],
                        'policy_type_id' => $row[1],
                        'version_number' => $row[2],
                        'document_type'  => $row[3],
                        'acronym'        => $row[4],
                        'location'       => $row[5],
                        'status'         => $row[6],
                        'change_control' => $row[7],
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    // Insertar los datos en la base de datos
                    $model->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/csvversionesdocumentos'))
                    ->with('success', 'Archivo CSV procesado con éxito.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/csvversionesdocumentos'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/csvversionesdocumentos'))
            ->with('error', 'Error al subir el archivo.');
    }
}
