<?php

namespace App\Libraries;

use App\Models\InformeAvancesModel;

class MetricasInformeService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Calcula cumplimiento de estándares desde evaluacion_inicial_sst
     * Filtrado por año PHVA usando YEAR(updated_at) — la última evaluación del ciclo
     */
    public function calcularCumplimientoEstandares(int $idCliente, int $anio): float
    {
        $result = $this->db->table('evaluacion_inicial_sst')
            ->select('SUM(valor) as total_maximo, SUM(puntaje_cuantitativo) as total_logrado')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(updated_at)', $anio)
            ->get()
            ->getRowArray();

        if (!$result || floatval($result['total_maximo']) == 0) {
            return 0.0;
        }

        $raw = (floatval($result['total_logrado']) / floatval($result['total_maximo'])) * 100;
        return round(min($raw, 100.0), 2);
    }

    /**
     * Obtiene puntaje del informe anterior del mismo cliente EN EL MISMO AÑO.
     * Si es primer informe del ciclo, retorna 39.75 (línea base Res. 0312/2019).
     */
    public function getPuntajeAnterior(int $idCliente, int $anio): float
    {
        $model = new InformeAvancesModel();
        $ultimo = $model->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->where('anio', $anio)
            ->orderBy('fecha_hasta', 'DESC')
            ->first();

        return $ultimo ? floatval($ultimo['puntaje_actual']) : 39.75;
    }

    /**
     * Calcula fecha_desde:
     * - Si hay informe previo en el mismo año: día siguiente al último
     * - Si es primer informe del ciclo: 1 de enero del año seleccionado
     */
    public function getFechaDesde(int $idCliente, int $anio): string
    {
        $model = new InformeAvancesModel();
        $ultimo = $model->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->where('anio', $anio)
            ->orderBy('fecha_hasta', 'DESC')
            ->first();

        if ($ultimo) {
            $fecha = new \DateTime($ultimo['fecha_hasta']);
            $fecha->modify('+1 day');
            return $fecha->format('Y-m-d');
        }

        return "{$anio}-01-01";
    }

    /**
     * Indicador plan de trabajo: % actividades cerradas del total del año.
     * Usa fecha_cierre de tbl_pta_cliente (fecha real de negocio, no audit).
     * Total = actividades creadas en el año (por created_at).
     * Cerradas = actividades con fecha_cierre dentro del año.
     */
    public function calcularIndicadorPlanTrabajo(int $idCliente, int $anio): float
    {
        $inicioAnio = "{$anio}-01-01";
        $finAnio = "{$anio}-12-31";

        // Total de actividades PTA del cliente para este ciclo
        $totalResult = $this->db->table('tbl_pta_cliente')
            ->selectCount('*', 'total')
            ->where('id_cliente', $idCliente)
            ->where('created_at >=', $inicioAnio . ' 00:00:00')
            ->where('created_at <=', $finAnio . ' 23:59:59')
            ->get()
            ->getRowArray();

        $total = intval($totalResult['total'] ?? 0);
        if ($total == 0) {
            return 0.0;
        }

        // Cerradas en el año — por fecha_cierre (fecha real de negocio)
        $cerradasResult = $this->db->table('tbl_pta_cliente')
            ->selectCount('*', 'cerradas')
            ->where('id_cliente', $idCliente)
            ->where('fecha_cierre >=', $inicioAnio)
            ->where('fecha_cierre <=', $finAnio)
            ->whereIn('estado_actividad', ['CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO'])
            ->get()
            ->getRowArray();

        $cerradas = intval($cerradasResult['cerradas'] ?? 0);

        return round(($cerradas / $total) * 100, 2);
    }

    /**
     * Indicador capacitación: % ejecutadas del total del año
     * Filtrado por fecha_programada dentro del año
     */
    public function calcularIndicadorCapacitacion(int $idCliente, int $anio): float
    {
        $inicioAnio = "{$anio}-01-01";
        $finAnio = "{$anio}-12-31";

        $result = $this->db->table('tbl_cronog_capacitacion')
            ->select("COUNT(*) as total, SUM(CASE WHEN estado = 'EJECUTADA' THEN 1 ELSE 0 END) as ejecutadas")
            ->where('id_cliente', $idCliente)
            ->where('fecha_programada >=', $inicioAnio)
            ->where('fecha_programada <=', $finAnio)
            ->get()
            ->getRowArray();

        if (!$result || intval($result['total']) == 0) {
            return 0.0;
        }

        return round((intval($result['ejecutadas']) / intval($result['total'])) * 100, 2);
    }

    /**
     * Lista de pendientes abiertos del cliente, filtrado por año (fecha_asignacion)
     */
    public function getActividadesAbiertas(int $idCliente, int $anio): string
    {
        $inicioAnio = "{$anio}-01-01 00:00:00";
        $finAnio = "{$anio}-12-31 23:59:59";

        $rows = $this->db->table('tbl_pendientes')
            ->select('tarea_actividad, responsable, fecha_asignacion')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'ABIERTA')
            ->where('fecha_asignacion >=', $inicioAnio)
            ->where('fecha_asignacion <=', $finAnio)
            ->orderBy('fecha_asignacion', 'DESC')
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return 'No hay actividades abiertas en este ciclo.';
        }

        $lines = [];
        foreach ($rows as $row) {
            $fecha = $row['fecha_asignacion'] ? date('d/m/Y', strtotime($row['fecha_asignacion'])) : 'S/F';
            $lines[] = "- {$row['tarea_actividad']} (Resp: {$row['responsable']}, Desde: {$fecha})";
        }

        return implode("\n", $lines);
    }

    /**
     * Actividades PTA cerradas en el periodo.
     * Usa tbl_pta_cliente.fecha_cierre (fecha real de negocio).
     */
    public function getActividadesCerradasPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        return $this->db->table('tbl_pta_cliente')
            ->select('actividad_plandetrabajo, numeral_plandetrabajo, phva_plandetrabajo, responsable_sugerido_plandetrabajo, fecha_cierre, estado_actividad')
            ->where('id_cliente', $idCliente)
            ->where('fecha_cierre >=', $desde)
            ->where('fecha_cierre <=', $hasta)
            ->whereIn('estado_actividad', ['CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO'])
            ->orderBy('fecha_cierre', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Formatea actividades cerradas para almacenar como texto
     */
    public function formatActividadesCerradas(array $actividades): string
    {
        if (empty($actividades)) {
            return 'No se cerraron actividades del PTA en este periodo.';
        }

        $lines = [];
        foreach ($actividades as $act) {
            $fecha = date('d/m/Y', strtotime($act['fecha_cierre']));
            $actividad = $act['actividad_plandetrabajo'] ?? 'Sin nombre';
            $numeral = $act['numeral_plandetrabajo'] ?? '';
            $phva = $act['phva_plandetrabajo'] ?? '';
            $resp = $act['responsable_sugerido_plandetrabajo'] ?? 'Sin asignar';
            $lines[] = "- [{$numeral}] {$actividad} | PHVA: {$phva} | Resp: {$resp} | Cerrada: {$fecha}";
        }

        return implode("\n", $lines);
    }

    /**
     * Determina estado de avance según diferencia neta
     */
    public function calcularEstadoAvance(float $diferencia): string
    {
        if ($diferencia > 5) {
            return 'AVANCE SIGNIFICATIVO';
        } elseif ($diferencia >= 1) {
            return 'AVANCE MODERADO';
        } elseif ($diferencia == 0) {
            return 'ESTABLE';
        } else {
            return 'REINICIO DE CICLO PHVA - BAJA PUNTAJE';
        }
    }

    /**
     * Genera enlace al dashboard del cliente
     */
    public function getEnlaceDashboard(int $idCliente): string
    {
        return base_url("consultant/dashboard-estandares?cliente={$idCliente}");
    }

    // ─── DESGLOSES POR PILAR (para gráficas Chart.js) — filtrados por año ───

    public function getDesgloseEstandares(int $idCliente, int $anio): array
    {
        return $this->db->table('evaluacion_inicial_sst')
            ->select("ciclo, SUM(valor) as total_valor, SUM(puntaje_cuantitativo) as total_posible, COUNT(*) as cantidad")
            ->where('id_cliente', $idCliente)
            ->where('YEAR(updated_at)', $anio)
            ->groupBy('ciclo')
            ->get()
            ->getResultArray();
    }

    public function getDesglosePlanTrabajo(int $idCliente, int $anio): array
    {
        $inicioAnio = "{$anio}-01-01 00:00:00";
        $finAnio = "{$anio}-12-31 23:59:59";

        return $this->db->table('tbl_pta_cliente')
            ->select("estado_actividad, COUNT(*) as cantidad")
            ->where('id_cliente', $idCliente)
            ->where('created_at >=', $inicioAnio)
            ->where('created_at <=', $finAnio)
            ->groupBy('estado_actividad')
            ->get()
            ->getResultArray();
    }

    public function getDesgloseCapacitacion(int $idCliente, int $anio): array
    {
        $inicioAnio = "{$anio}-01-01";
        $finAnio = "{$anio}-12-31";

        return $this->db->table('tbl_cronog_capacitacion')
            ->select("estado, COUNT(*) as cantidad")
            ->where('id_cliente', $idCliente)
            ->where('fecha_programada >=', $inicioAnio)
            ->where('fecha_programada <=', $finAnio)
            ->groupBy('estado')
            ->get()
            ->getResultArray();
    }

    public function getDesglosePendientes(int $idCliente, int $anio): array
    {
        $inicioAnio = "{$anio}-01-01 00:00:00";
        $finAnio = "{$anio}-12-31 23:59:59";

        return $this->db->table('tbl_pendientes')
            ->select("estado, COUNT(*) as cantidad, ROUND(AVG(conteo_dias), 1) as promedio_dias")
            ->where('id_cliente', $idCliente)
            ->where('fecha_asignacion >=', $inicioAnio)
            ->where('fecha_asignacion <=', $finAnio)
            ->groupBy('estado')
            ->get()
            ->getResultArray();
    }

    /**
     * Recopila actividades del periodo para el prompt de IA
     */
    public function recopilarActividadesPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        $actividades = [];

        // Actas de visita en el periodo
        $actas = $this->db->table('tbl_acta_visita')
            ->select('fecha_visita, motivo')
            ->where('id_cliente', $idCliente)
            ->where('fecha_visita >=', $desde)
            ->where('fecha_visita <=', $hasta)
            ->orderBy('fecha_visita', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($actas as $a) {
            $actividades[] = "Visita ({$a['fecha_visita']}): {$a['motivo']}";
        }

        // Capacitaciones ejecutadas en el periodo
        $caps = $this->db->table('tbl_cronog_capacitacion')
            ->select('fecha_programada, nombre_capacitacion, estado')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'EJECUTADA')
            ->where('fecha_programada >=', $desde)
            ->where('fecha_programada <=', $hasta)
            ->orderBy('fecha_programada', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($caps as $c) {
            $actividades[] = "Capacitación ejecutada ({$c['fecha_programada']}): {$c['nombre_capacitacion']}";
        }

        // PTA cerradas en el periodo (por fecha_cierre real)
        $cerradas = $this->getActividadesCerradasPeriodo($idCliente, $desde, $hasta);
        foreach ($cerradas as $t) {
            $actividades[] = "PTA cerrada ({$t['fecha_cierre']}): {$t['actividad_plandetrabajo']}";
        }

        // Pendientes cerrados en el periodo
        $pendientes = $this->db->table('tbl_pendientes')
            ->select('tarea_actividad, fecha_cierre')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'CERRADA')
            ->where('fecha_cierre >=', $desde)
            ->where('fecha_cierre <=', $hasta)
            ->orderBy('fecha_cierre', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($pendientes as $p) {
            $actividades[] = "Compromiso cerrado ({$p['fecha_cierre']}): {$p['tarea_actividad']}";
        }

        return $actividades;
    }

    /**
     * Calcula todas las métricas de un cliente para el informe, filtradas por año PHVA
     */
    public function calcularTodas(int $idCliente, string $fechaDesde, string $fechaHasta, int $anio): array
    {
        $puntajeActual = $this->calcularCumplimientoEstandares($idCliente, $anio);
        $puntajeAnterior = $this->getPuntajeAnterior($idCliente, $anio);
        $diferencia = round($puntajeActual - $puntajeAnterior, 2);
        $estadoAvance = $this->calcularEstadoAvance($diferencia);

        $actividadesCerradas = $this->getActividadesCerradasPeriodo($idCliente, $fechaDesde, $fechaHasta);

        return [
            'puntaje_actual'               => $puntajeActual,
            'puntaje_anterior'             => $puntajeAnterior,
            'diferencia_neta'              => $diferencia,
            'estado_avance'                => $estadoAvance,
            'indicador_plan_trabajo'       => $this->calcularIndicadorPlanTrabajo($idCliente, $anio),
            'indicador_capacitacion'       => $this->calcularIndicadorCapacitacion($idCliente, $anio),
            'actividades_abiertas'         => $this->getActividadesAbiertas($idCliente, $anio),
            'actividades_cerradas_periodo' => $this->formatActividadesCerradas($actividadesCerradas),
            'actividades_cerradas_raw'     => $actividadesCerradas,
            'enlace_dashboard'             => $this->getEnlaceDashboard($idCliente),
            'fecha_desde_sugerida'         => $this->getFechaDesde($idCliente, $anio),
            // Desgloses por pilar (para gráficas)
            'desglose_estandares'      => $this->getDesgloseEstandares($idCliente, $anio),
            'desglose_plan_trabajo'    => $this->getDesglosePlanTrabajo($idCliente, $anio),
            'desglose_capacitacion'    => $this->getDesgloseCapacitacion($idCliente, $anio),
            'desglose_pendientes'      => $this->getDesglosePendientes($idCliente, $anio),
        ];
    }
}
