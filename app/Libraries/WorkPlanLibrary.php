<?php

namespace App\Libraries;

use Exception;

/**
 * WorkPlanLibrary
 *
 * Librería para gestionar las plantillas de Plan de Trabajo Anual (PTA)
 * según el año del SGSST y tipo de servicio del cliente.
 *
 * Lee el archivo CSV maestro y filtra las actividades correspondientes
 * a cada combinación de año (1, 2, 3) y tipo de servicio (Mensual, Bimensual, Trimestral, Proyecto).
 */
class WorkPlanLibrary
{
    /**
     * Ruta al archivo CSV maestro con todas las plantillas
     */
    private const CSV_PATH = ROOTPATH . 'PTA2026.csv';

    /**
     * Mapeo de tipos de servicio a columnas del CSV
     */
    private const SERVICE_TYPE_COLUMNS = [
        'mensual' => 6,      // Columna MENSUAL
        'bimensual' => 7,    // Columna BIMENSUAL
        'trimestral' => 8,   // Columna TRIMESTRAL
        'proyecto' => 9,     // Columna PROYECTO
    ];

    /**
     * Mapeo de años a columnas del CSV
     */
    private const YEAR_COLUMNS = [
        1 => 3, // Columna CLIENTE AÑO 1
        2 => 4, // Columna CLIENTE AÑO 2
        3 => 5, // Columna CLIENTE AÑO 3
    ];

    /**
     * Obtiene las actividades del plan de trabajo filtradas por año y tipo de servicio
     *
     * @param int $idCliente ID del cliente
     * @param int $year Año del SGSST (1, 2 o 3)
     * @param string $serviceType Tipo de servicio: 'mensual', 'bimensual', 'trimestral', 'proyecto'
     * @return array Array de actividades listas para insertar en la BD
     * @throws Exception Si el archivo CSV no existe o los parámetros son inválidos
     */
    public function getActivities(int $idCliente, int $year, string $serviceType): array
    {
        // Validar parámetros
        $this->validateParameters($year, $serviceType);

        // Leer el CSV
        $csvData = $this->readCSV();

        // Filtrar actividades
        $activities = $this->filterActivities($csvData, $idCliente, $year, $serviceType);

        return $activities;
    }

    /**
     * Valida que los parámetros sean correctos
     */
    private function validateParameters(int $year, string $serviceType): void
    {
        if (!isset(self::YEAR_COLUMNS[$year])) {
            throw new Exception("Año inválido. Debe ser 1, 2 o 3. Recibido: {$year}");
        }

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
     * Filtra las actividades según año y tipo de servicio
     */
    private function filterActivities(array $csvData, int $idCliente, int $year, string $serviceType): array
    {
        $serviceType = strtolower($serviceType);
        $activities = [];
        $yearColumn = self::YEAR_COLUMNS[$year];
        $serviceColumn = self::SERVICE_TYPE_COLUMNS[$serviceType];
        $rowNumber = 1; // Para tracking de debugging

        // Saltar la primera fila (encabezados)
        foreach (array_slice($csvData, 1) as $row) {
            $rowNumber++;

            // Verificar que la fila tenga suficientes columnas
            if (count($row) < 11) {
                log_message('debug', "Fila {$rowNumber} omitida: solo tiene " . count($row) . " columnas");
                continue;
            }

            // Verificar que la actividad no esté vacía
            if (empty(trim($row[2] ?? ''))) {
                log_message('debug', "Fila {$rowNumber} omitida: actividad vacía");
                continue;
            }

            // Verificar si esta actividad aplica para el año Y tipo de servicio
            $appliesToYear = isset($row[$yearColumn]) && strtolower(trim($row[$yearColumn])) === 'x';
            $appliesToService = isset($row[$serviceColumn]) && strtolower(trim($row[$serviceColumn])) === 'x';

            // Si aplica a ambos criterios, agregar la actividad
            if ($appliesToYear && $appliesToService) {
                $activities[] = [
                    'id_cliente' => $idCliente,
                    'phva_plandetrabajo' => trim($row[0] ?? ''),
                    'numeral_plandetrabajo' => trim($row[1] ?? ''),
                    'actividad_plandetrabajo' => trim($row[2] ?? ''),
                    'responsable_sugerido_plandetrabajo' => 'CONSULTOR CYCLOID',
                    'observaciones' => '',
                    'fecha_propuesta' => date('Y-m-d'),
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $activityCount = count($activities);
        log_message('info', "WorkPlanLibrary: Filtradas {$activityCount} actividades para Cliente ID: {$idCliente}, Año: {$year}, Servicio: {$serviceType}");

        return $activities;
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

    /**
     * Obtiene los años disponibles
     *
     * @return array
     */
    public static function getAvailableYears(): array
    {
        return [1, 2, 3];
    }

    /**
     * Detecta el año actual del SGSST de un cliente basado en sus actividades existentes
     *
     * @param int $idCliente ID del cliente
     * @return int|null Año detectado (1, 2, 3) o null si no hay actividades
     */
    public function detectCurrentYear(int $idCliente): ?int
    {
        $db = \Config\Database::connect();

        // Buscar la fecha de creación de la primera actividad del cliente
        $query = $db->table('tbl_pta_cliente')
            ->select('MIN(created_at) as first_activity_date')
            ->where('id_cliente', $idCliente)
            ->get();

        $result = $query->getRow();

        if (!$result || !$result->first_activity_date) {
            return null; // No hay actividades, es cliente nuevo
        }

        // Calcular años transcurridos desde la primera actividad
        $firstActivityDate = new \DateTime($result->first_activity_date);
        $now = new \DateTime();
        $interval = $firstActivityDate->diff($now);
        $yearsPassed = $interval->y;

        // El año del SGSST es años transcurridos + 1, máximo 3
        $currentYear = min($yearsPassed + 1, 3);

        return $currentYear;
    }
}
