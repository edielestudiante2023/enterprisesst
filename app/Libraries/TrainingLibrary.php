<?php

namespace App\Libraries;

use Exception;

/**
 * TrainingLibrary
 *
 * Librería para gestionar las plantillas de Cronograma de Capacitaciones
 * según el tipo de servicio del cliente.
 *
 * Lee el archivo CSV maestro y filtra las capacitaciones correspondientes
 * a cada tipo de servicio (Mensual, Bimensual, Trimestral, Proyecto).
 */
class TrainingLibrary
{
    /**
     * Ruta al archivo CSV maestro con todas las capacitaciones
     */
    private const CSV_PATH = ROOTPATH . 'capacitaciones ph.csv';

    /**
     * Mapeo de tipos de servicio a columnas del CSV
     */
    private const SERVICE_TYPE_COLUMNS = [
        'mensual' => 1,      // Columna MENSUAL
        'bimensual' => 2,    // Columna BIMENSUAL
        'trimestral' => 3,   // Columna TRIMESTRAL
        'proyecto' => 4,     // Columna PROYECTO
    ];

    /**
     * Obtiene las capacitaciones filtradas por tipo de servicio
     *
     * @param int $idCliente ID del cliente
     * @param string $serviceType Tipo de servicio: 'mensual', 'bimensual', 'trimestral', 'proyecto'
     * @return array Array de capacitaciones listas para insertar en la BD
     * @throws Exception Si el archivo CSV no existe o los parámetros son inválidos
     */
    public function getTrainings(int $idCliente, string $serviceType): array
    {
        // Validar parámetros
        $this->validateParameters($serviceType);

        // Leer el CSV
        $csvData = $this->readCSV();

        // Filtrar capacitaciones
        $trainings = $this->filterTrainings($csvData, $idCliente, $serviceType);

        return $trainings;
    }

    /**
     * Valida que los parámetros sean correctos
     */
    private function validateParameters(string $serviceType): void
    {
        $serviceType = strtolower($serviceType);
        if (!isset(self::SERVICE_TYPE_COLUMNS[$serviceType])) {
            throw new Exception("Tipo de servicio inválido. Debe ser: mensual, bimensual, trimestral o proyecto. Recibido: {$serviceType}");
        }
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
        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $csvData[] = $row;
        }

        fclose($handle);

        return $csvData;
    }

    /**
     * Filtra las capacitaciones según tipo de servicio
     */
    private function filterTrainings(array $csvData, int $idCliente, string $serviceType): array
    {
        $serviceType = strtolower($serviceType);
        $trainings = [];
        $serviceColumn = self::SERVICE_TYPE_COLUMNS[$serviceType];
        $rowNumber = 1; // Para tracking de debugging

        // Saltar la primera fila (encabezados)
        foreach (array_slice($csvData, 1) as $row) {
            $rowNumber++;

            // Verificar que la fila tenga suficientes columnas
            if (count($row) < 6) {
                log_message('debug', "Fila {$rowNumber} omitida: solo tiene " . count($row) . " columnas");
                continue;
            }

            // Verificar que la capacitación no esté vacía
            if (empty(trim($row[0] ?? ''))) {
                log_message('debug', "Fila {$rowNumber} omitida: capacitación vacía");
                continue;
            }

            // Verificar si esta capacitación aplica para el tipo de servicio
            $appliesToService = isset($row[$serviceColumn]) && strtolower(trim($row[$serviceColumn])) === 'x';

            // Si aplica, agregar la capacitación
            if ($appliesToService) {
                $trainings[] = [
                    'id_cliente' => $idCliente,
                    'nombre_capacitacion' => trim($row[0] ?? ''),
                    'objetivo_capacitacion' => '',
                    'observaciones' => '',
                    'estado' => 'PROGRAMADA',
                    'perfil_de_asistentes' => 'TODOS',
                    'nombre_del_capacitador' => 'CYCLOID TALENT',
                    'horas_de_duracion_de_la_capacitacion' => 1,
                    'indicador_de_realizacion_de_la_capacitacion' => 'SIN CALIFICAR',
                    'numero_de_asistentes' => 0,
                    'numero_total_de_personas_programadas' => 0,
                    'porcentaje_cobertura' => 0,
                    'fecha_programada' => date('Y-m-d'),
                    'fecha_de_realizacion' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $trainingCount = count($trainings);
        log_message('info', "TrainingLibrary: Filtradas {$trainingCount} capacitaciones para Cliente ID: {$idCliente}, Servicio: {$serviceType}");

        return $trainings;
    }

    /**
     * Obtiene los tipos de servicio disponibles
     *
     * @return array
     */
    public static function getServiceTypes(): array
    {
        return [
            'mensual' => 'Mensual',
            'bimensual' => 'Bimensual',
            'trimestral' => 'Trimestral',
            'proyecto' => 'Proyecto',
        ];
    }
}
