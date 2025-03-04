<?php

namespace App\Models;

use CodeIgniter\Model;

class CsvUploadModel extends Model
{
    protected $table      = 'tbl_pta_cliente';
    protected $primaryKey = 'id_ptacliente';

    /**
     * Limpia una cadena eliminando el BOM y espacios en blanco.
     */
    private function cleanString(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        return trim($value);
    }

    /**
     * Convierte fechas al formato YYYY-MM-DD.
     */
    private function parseDate(?string $value): ?string
    {
        $value = $this->cleanString((string)$value);
        if (empty($value)) {
            return null;
        }
        if (strpos($value, '/') !== false) {
            $parts = explode('/', $value);
            if (count($parts) === 3) {
                [$d, $m, $y] = $parts;
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }
        $time = strtotime($value);
        if ($time) {
            return date('Y-m-d', $time);
        }
        return null;
    }

    /**
     * Procesa los datos del CSV para actualizar o eliminar registros.
     */
    public function processCsvData(array $csvData): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        // Contadores para las filas procesadas
        $totalRows = 0;
        $modifiedCount = 0;
        $unchangedCount = 0;
        $deletedCount = 0;

        foreach ($csvData as $row) {
            // Si la fila no tiene al menos 6 elementos, forzamos la división usando el delimitador ";"
            if (count($row) < 6) {
                $row = explode(";", $row[0]);
            }
            if (count($row) < 6) {
                continue;
            }

            // Contamos cada fila procesada
            $totalRows++;

            // Orden esperado:
            // 0: id_ptacliente
            // 1: actividad_plandetrabajo
            // 2: responsable_sugerido_plandetrabajo
            // 3: fecha_propuesta
            // 4: estado_actividad
            // 5: accion
            $id_ptacliente = (int)$this->cleanString($row[0]);
            $actividad_plandetrabajo = $this->cleanString($row[1]);
            $responsable_plandetrabajo = $this->cleanString($row[2]);
            $fecha_propuesta = $this->parseDate($row[3]);
            $estado_actividad = $this->cleanString($row[4]);
            $accion = strtolower($this->cleanString($row[5]));

            if ($id_ptacliente <= 0) {
                continue;
            }

            // Procesar eliminación
            if (strpos($accion, 'eliminar') !== false) {
                $db->table($this->table)
                    ->where('id_ptacliente', $id_ptacliente)
                    ->delete();
                // Incrementamos el contador de eliminaciones (se asume 1 por cada eliminación)
                $deletedCount++;
                continue;
            }

            // Procesar actualización
            if (strpos($accion, 'actualizar') !== false) {
                $dataToUpdate = [
                    'actividad_plandetrabajo'          => $actividad_plandetrabajo,
                    'responsable_sugerido_plandetrabajo' => $responsable_plandetrabajo,
                    'fecha_propuesta'                  => $fecha_propuesta,
                    'estado_actividad'                 => $estado_actividad,
                ];
                $builder->where('id_ptacliente', $id_ptacliente)->update($dataToUpdate);
                $affected = $db->affectedRows();
                // Si se afectó al menos 1 fila, se cuenta como modificada; si no, se cuenta como sin cambio.
                if ($affected > 0) {
                    $modifiedCount++;
                } else {
                    $unchangedCount++;
                }
            }
        }

        $message = "Tu CSV tenía $totalRows filas. $deletedCount eliminadas, $modifiedCount modificadas y $unchangedCount sin cambios.";
        return [
            'status'  => 'success',
            'message' => $message,
        ];
    }
}
