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
                    'responsable_sugerido_plandetrabajo',
                    'observaciones',
                    'fecha_propuesta'
                ];

                if ($headers !== $requiredHeaders) {
                    unlink($filePath);
                    return redirect()->to(base_url('consultant/plan'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos: ' . implode(', ', $requiredHeaders));
                }

                // Contadores para el reporte
                $totalRows = count($rows) - 1; // Excluir encabezado
                $successCount = 0;
                $errorCount = 0;
                $errors = [];

                // Procesar los datos (a partir de la fila 2)
                $planModel = new PlanModel();
                foreach (array_slice($rows, 1) as $index => $row) {
                    $rowNumber = $index + 2; // +2 porque empezamos desde fila 2 (índice 1 + encabezado)

                    try {
                        // Validar que los campos requeridos no estén vacíos
                        if (empty($row[0]) || empty($row[3])) {
                            $errors[] = "Fila {$rowNumber}: Campo 'id_cliente' o 'actividad_plandetrabajo' vacío";
                            $errorCount++;
                            continue;
                        }

                        // Formatear la fecha con manejo flexible
                        $fechaFormateada = $this->formatDateFlexible($row[6]);
                        if ($fechaFormateada === null) {
                            $errors[] = "Fila {$rowNumber}: Fecha inválida '{$row[6]}'";
                            $errorCount++;
                            continue;
                        }

                        $data = [
                            'id_cliente' => $row[0],
                            'phva_plandetrabajo' => $row[1] ?? '',
                            'numeral_plandetrabajo' => $row[2] ?? '',
                            'actividad_plandetrabajo' => $row[3],
                            'responsable_sugerido_plandetrabajo' => $row[4] ?? '',
                            'observaciones' => $row[5] ?? '',
                            'fecha_propuesta' => $fechaFormateada,
                            'estado_actividad' => 'ABIERTA',  // Estado por defecto al importar
                            'porcentaje_avance' => 0,          // Porcentaje inicial
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];

                        // Insertar los datos en la base de datos
                        if ($planModel->insert($data)) {
                            $successCount++;
                        } else {
                            $errors[] = "Fila {$rowNumber}: Error al insertar en la base de datos";
                            $errorCount++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                        $errorCount++;
                    }
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                // Preparar mensaje de resultado
                $message = "<strong>Importación completada:</strong><br>";
                $message .= "✓ Total de filas procesadas: {$totalRows}<br>";
                $message .= "✓ Registros cargados exitosamente: {$successCount}<br>";

                if ($errorCount > 0) {
                    $message .= "✗ Registros con errores: {$errorCount}<br><br>";
                    $message .= "<strong>Detalle de errores:</strong><br>";
                    $message .= "<ul class='mb-0'>";
                    // Limitar a los primeros 10 errores para no sobrecargar la vista
                    $errorsToShow = array_slice($errors, 0, 10);
                    foreach ($errorsToShow as $error) {
                        $message .= "<li>" . htmlspecialchars($error) . "</li>";
                    }
                    if (count($errors) > 10) {
                        $message .= "<li>... y " . (count($errors) - 10) . " errores más</li>";
                    }
                    $message .= "</ul>";
                }

                $flashType = ($errorCount === 0) ? 'success' : (($successCount > 0) ? 'warning' : 'error');

                return redirect()->to(base_url('consultant/plan'))
                    ->with($flashType, $message);

            } catch (\Exception $e) {
                // Eliminar el archivo si existe
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return redirect()->to(base_url('consultant/plan'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/plan'))
            ->with('error', 'Error al subir el archivo.');
    }

    /**
     * Formatea fechas de manera flexible aceptando múltiples formatos
     * Soporta: dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd, yyyy/mm/dd, d/m/yyyy, etc.
     */
    private function formatDateFlexible($date)
    {
        if (empty($date)) {
            return null;
        }

        // Si ya es una fecha válida en formato Y-m-d, retornarla
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Lista de formatos comunes a intentar
        $formats = [
            'd/m/Y',    // 31/12/2025
            'd-m-Y',    // 31-12-2025
            'd/m/y',    // 31/12/25
            'd-m-y',    // 31-12-25
            'Y/m/d',    // 2025/12/31
            'Y-m-d',    // 2025-12-31
            'm/d/Y',    // 12/31/2025
            'm-d-Y',    // 12-31-2025
            'd.m.Y',    // 31.12.2025
            'Y.m.d',    // 2025.12.31
        ];

        // Intentar cada formato
        foreach ($formats as $format) {
            $dateObj = \DateTime::createFromFormat($format, $date);
            if ($dateObj !== false) {
                // Validar que la fecha sea real (no 31/02/2025)
                $errors = \DateTime::getLastErrors();
                if ($errors['warning_count'] == 0 && $errors['error_count'] == 0) {
                    return $dateObj->format('Y-m-d');
                }
            }
        }

        // Si no funcionó ningún formato, intentar strtotime como último recurso
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        // Si todo falla, retornar null
        return null;
    }
}
