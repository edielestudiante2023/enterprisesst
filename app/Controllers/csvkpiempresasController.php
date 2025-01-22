<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ClientKpiModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvKpiEmpresasController extends Controller
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

            try {
                // Leer el archivo CSV utilizando PhpSpreadsheet
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Definir los encabezados esperados
                $headers = $rows[0];
                $requiredHeaders = [
                    'year', 'month', 'kpi_interpretation', 'id_cliente', 'id_kpi_policy', 'id_objectives', 'id_kpis',
                    'id_kpi_type', 'id_kpi_definition', 'kpi_target', 'kpi_formula', 'data_source', 'id_data_owner',
                    'positions_should_know_result', 'periodicidad',
                    'variable_numerador_1', 'dato_variable_numerador_1', 'variable_denominador_1', 'dato_variable_denominador_1', 'valor_indicador_1',
                    'variable_numerador_2', 'dato_variable_numerador_2', 'variable_denominador_2', 'dato_variable_denominador_2', 'valor_indicador_2',
                    'variable_numerador_3', 'dato_variable_numerador_3', 'variable_denominador_3', 'dato_variable_denominador_3', 'valor_indicador_3',
                    'variable_numerador_4', 'dato_variable_numerador_4', 'variable_denominador_4', 'dato_variable_denominador_4', 'valor_indicador_4',
                    'variable_numerador_5', 'dato_variable_numerador_5', 'variable_denominador_5', 'dato_variable_denominador_5', 'valor_indicador_5',
                    'variable_numerador_6', 'dato_variable_numerador_6', 'variable_denominador_6', 'dato_variable_denominador_6', 'valor_indicador_6',
                    'variable_numerador_7', 'dato_variable_numerador_7', 'variable_denominador_7', 'dato_variable_denominador_7', 'valor_indicador_7',
                    'variable_numerador_8', 'dato_variable_numerador_8', 'variable_denominador_8', 'dato_variable_denominador_8', 'valor_indicador_8',
                    'variable_numerador_9', 'dato_variable_numerador_9', 'variable_denominador_9', 'dato_variable_denominador_9', 'valor_indicador_9',
                    'variable_numerador_10', 'dato_variable_numerador_10', 'variable_denominador_10', 'dato_variable_denominador_10', 'valor_indicador_10',
                    'variable_numerador_11', 'dato_variable_numerador_11', 'variable_denominador_11', 'dato_variable_denominador_11', 'valor_indicador_11',
                    'variable_numerador_12', 'dato_variable_numerador_12', 'variable_denominador_12', 'dato_variable_denominador_12', 'valor_indicador_12',
                    'gran_total_indicador', 'analisis_datos', 'seguimiento1', 'seguimiento2', 'seguimiento3'
                ];

                if ($headers !== $requiredHeaders) {
                    return redirect()->to(base_url('consultant/csvkpisempresas'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos: ' . implode(', ', $requiredHeaders));
                }

                // Procesar las filas (omitimos la primera fila de encabezados)
                $model = new ClientKpiModel();
                foreach (array_slice($rows, 1) as $row) {
                    $data = array_combine($headers, $row);
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['updated_at'] = date('Y-m-d H:i:s');

                    // Insertar el registro en la base de datos
                    $model->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/csvkpisempresas'))
                    ->with('success', 'Archivo CSV procesado con éxito.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/csvkpisempresas'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/csvkpisempresas'))
            ->with('error', 'Error al subir el archivo.');
    }
}
