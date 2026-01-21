<?php

namespace App\Libraries;

use Exception;

/**
 * StandardsLibrary
 *
 * Librería para gestionar las plantillas de Evaluación de Estándares Mínimos.
 *
 * Lee el archivo CSV maestro y prepara los estándares para insertar
 * automáticamente cuando se crea un nuevo cliente.
 */
class StandardsLibrary
{
    /**
     * Ruta al archivo CSV maestro con todos los estándares mínimos
     */
    private const CSV_PATH = ROOTPATH . 'csvevaluacionestandaresminimosph.csv';

    /**
     * Obtiene todos los estándares mínimos listos para un cliente
     *
     * @param int $idCliente ID del cliente al que se asignarán los estándares
     * @return array Array de estándares listos para insertar en la BD
     * @throws Exception Si el archivo CSV no existe o no se puede leer
     */
    public function getStandards(int $idCliente): array
    {
        // Leer el CSV
        $csvData = $this->readCSV();

        // Preparar los estándares para el cliente
        $standards = $this->prepareStandards($csvData, $idCliente);

        return $standards;
    }

    /**
     * Lee el archivo CSV maestro
     */
    private function readCSV(): array
    {
        if (!file_exists(self::CSV_PATH)) {
            throw new Exception("Archivo CSV maestro no encontrado: " . self::CSV_PATH);
        }

        $csvData = [];
        $handle = fopen(self::CSV_PATH, 'r');

        if ($handle === false) {
            throw new Exception("No se pudo abrir el archivo CSV: " . self::CSV_PATH);
        }

        // Leer línea por línea
        while (($row = fgetcsv($handle, 4000, ';')) !== false) {
            $csvData[] = $row;
        }

        fclose($handle);

        return $csvData;
    }

    /**
     * Prepara los estándares para insertar en la base de datos
     *
     * @param array $csvData Datos del CSV
     * @param int $idCliente ID del cliente
     * @return array Array de estándares con el id_cliente reemplazado
     */
    private function prepareStandards(array $csvData, int $idCliente): array
    {
        $standards = [];
        $rowNumber = 1; // Para tracking de debugging

        // Saltar la primera fila (encabezados) y el BOM si existe
        foreach (array_slice($csvData, 1) as $row) {
            $rowNumber++;

            // Verificar que la fila tenga suficientes columnas (20 columnas según el CSV)
            if (count($row) < 20) {
                log_message('debug', "Fila {$rowNumber} omitida: solo tiene " . count($row) . " columnas");
                continue;
            }

            // Verificar que el ciclo no esté vacío (campo obligatorio)
            if (empty(trim($row[1] ?? ''))) {
                log_message('debug', "Fila {$rowNumber} omitida: ciclo vacío");
                continue;
            }

            // Preparar el registro con el id_cliente del nuevo cliente
            $standards[] = [
                'id_cliente' => $idCliente,  // Reemplazar con el ID del nuevo cliente
                'ciclo' => trim($row[1] ?? ''),
                'estandar' => trim($row[2] ?? ''),
                'detalle_estandar' => trim($row[3] ?? ''),
                'estandares_minimos' => trim($row[4] ?? ''),
                'numeral' => trim($row[5] ?? ''),
                'numerales_del_cliente' => trim($row[6] ?? ''),
                'siete' => trim($row[7] ?? ''),
                'veintiun' => trim($row[8] ?? ''),
                'sesenta' => trim($row[9] ?? ''),
                'item_del_estandar' => trim($row[10] ?? ''),
                'evaluacion_inicial' => trim($row[11] ?? ''),
                'valor' => trim($row[12] ?? ''),
                'puntaje_cuantitativo' => trim($row[13] ?? ''),
                'item' => trim($row[14] ?? ''),
                'criterio' => trim($row[15] ?? ''),
                'modo_de_verificacion' => trim($row[16] ?? ''),
                'calificacion' => trim($row[17] ?? ''),
                'nivel_de_evaluacion' => trim($row[18] ?? ''),
                'observaciones' => trim($row[19] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $standardsCount = count($standards);
        log_message('info', "StandardsLibrary: Preparados {$standardsCount} estándares para Cliente ID: {$idCliente}");

        return $standards;
    }

    /**
     * Obtiene el número total de estándares en el archivo maestro
     *
     * @return int Cantidad de estándares disponibles
     */
    public function getStandardsCount(): int
    {
        try {
            $csvData = $this->readCSV();
            // Restar 1 para excluir la fila de encabezados
            return count($csvData) - 1;
        } catch (Exception $e) {
            log_message('error', "StandardsLibrary: Error al contar estándares: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verifica si el archivo CSV existe y es legible
     *
     * @return bool True si el archivo existe y es legible
     */
    public static function csvFileExists(): bool
    {
        return file_exists(self::CSV_PATH) && is_readable(self::CSV_PATH);
    }
}
