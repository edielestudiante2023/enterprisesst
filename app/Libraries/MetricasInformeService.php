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

        // Firmas electrónicas completadas en el periodo
        $firmas = $this->db->table('tbl_doc_firma_solicitudes s')
            ->select('s.fecha_firma, d.titulo')
            ->join('tbl_documentos_sst d', 'd.id_documento = s.id_documento')
            ->where('d.id_cliente', $idCliente)
            ->where('s.estado', 'firmado')
            ->where('s.fecha_firma >=', $desde . ' 00:00:00')
            ->where('s.fecha_firma <=', $hasta . ' 23:59:59')
            ->orderBy('s.fecha_firma', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($firmas as $f) {
            $fecha = substr($f['fecha_firma'], 0, 10);
            $actividades[] = "Firma electrónica ({$fecha}): {$f['titulo']}";
        }

        // Inspecciones completadas en el periodo
        $tablesInsp = [
            'Locativa' => 'tbl_inspeccion_locativa',
            'Extintores' => 'tbl_inspeccion_extintores',
            'Botiquín' => 'tbl_inspeccion_botiquin',
            'Señalización' => 'tbl_inspeccion_senalizacion',
        ];
        foreach ($tablesInsp as $tipo => $table) {
            $insps = $this->db->table($table)
                ->select('fecha_inspeccion')
                ->where('id_cliente', $idCliente)
                ->where('estado', 'completada')
                ->where('fecha_inspeccion >=', $desde)
                ->where('fecha_inspeccion <=', $hasta)
                ->get()
                ->getResultArray();
            foreach ($insps as $i) {
                $actividades[] = "Inspección {$tipo} ({$i['fecha_inspeccion']})";
            }
        }

        // Actas de comité firmadas en el periodo
        $actasComite = $this->db->table('tbl_actas a')
            ->select('a.fecha_reunion, a.numero_acta, tc.codigo')
            ->join('tbl_comites c', 'c.id_comite = a.id_comite')
            ->join('tbl_tipos_comite tc', 'tc.id_tipo = c.id_tipo')
            ->where('a.id_cliente', $idCliente)
            ->where('a.estado', 'firmada')
            ->where('a.fecha_reunion >=', $desde)
            ->where('a.fecha_reunion <=', $hasta)
            ->orderBy('a.fecha_reunion', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($actasComite as $ac) {
            $actividades[] = "Acta {$ac['codigo']} #{$ac['numero_acta']} ({$ac['fecha_reunion']})";
        }

        // Acciones correctivas cerradas en el periodo
        $accCerradas = $this->db->table('tbl_acc_acciones ac')
            ->select('ac.fecha_cierre_real, ac.descripcion_accion, h.titulo')
            ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = ac.id_hallazgo')
            ->where('h.id_cliente', $idCliente)
            ->whereIn('ac.estado', ['cerrada_efectiva', 'cerrada_no_efectiva'])
            ->where('ac.fecha_cierre_real >=', $desde)
            ->where('ac.fecha_cierre_real <=', $hasta)
            ->orderBy('ac.fecha_cierre_real', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($accCerradas as $acc) {
            $actividades[] = "Acción correctiva cerrada ({$acc['fecha_cierre_real']}): {$acc['titulo']}";
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
            // Documentos cargados en el periodo
            'documentos_cargados_raw'  => $this->getDocumentosCargados($idCliente, $fechaDesde, $fechaHasta),
            // --- 6 módulos adicionales ---
            'firma_electronica'        => $this->getFirmaElectronicaPeriodo($idCliente, $fechaDesde, $fechaHasta),
            'documentos_sst'           => $this->getDocumentosSstPeriodo($idCliente, $fechaDesde, $fechaHasta),
            'indicadores_sst'          => $this->getIndicadoresSstPeriodo($idCliente, $anio),
            'acciones_correctivas'     => $this->getAccionesCorrectivasPeriodo($idCliente, $anio),
            'actas_comite'             => $this->getActasComitePeriodo($idCliente, $fechaDesde, $fechaHasta, $anio),
            'inspecciones'             => $this->getInspeccionesPeriodo($idCliente, $fechaDesde, $fechaHasta),
            'actividades_no_cerradas_pta' => $this->getActividadesNoCerradasPta($idCliente, $fechaDesde, $fechaHasta),
        ];
    }

    /**
     * Desglose PTA del periodo: actividades programadas para el periodo y su estado
     */
    public function getDesglosePtaPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        $programadas = $this->db->table('tbl_pta_cliente')
            ->select("estado_actividad, COUNT(*) as cantidad")
            ->where('id_cliente', $idCliente)
            ->where('fecha_propuesta >=', $desde)
            ->where('fecha_propuesta <=', $hasta)
            ->groupBy('estado_actividad')
            ->get()
            ->getResultArray();

        $total = 0;
        $cerradas = 0;
        $abiertas = 0;
        foreach ($programadas as $p) {
            $cant = intval($p['cantidad']);
            $total += $cant;
            if (in_array($p['estado_actividad'], ['CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO'])) {
                $cerradas += $cant;
            } elseif ($p['estado_actividad'] === 'ABIERTA') {
                $abiertas += $cant;
            }
        }

        return [
            'total_periodo' => $total,
            'cerradas_periodo' => $cerradas,
            'abiertas_periodo' => $abiertas,
            'desglose' => $programadas,
        ];
    }

    /**
     * Capacitaciones ejecutadas en el periodo con detalle
     */
    public function getCapacitacionesEjecutadas(int $idCliente, string $desde, string $hasta): array
    {
        return $this->db->table('tbl_cronog_capacitacion')
            ->select('fecha_programada, fecha_de_realizacion, nombre_capacitacion, objetivo_capacitacion, perfil_de_asistentes, nombre_del_capacitador, horas_de_duracion_de_la_capacitacion, numero_de_asistentes_a_capacitacion, numero_total_de_personas_programadas, porcentaje_cobertura, promedio_de_calificaciones, observaciones')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'EJECUTADA')
            ->where('fecha_programada >=', $desde)
            ->where('fecha_programada <=', $hasta)
            ->orderBy('fecha_programada', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Documentos cargados en tbl_reporte para un cliente en el periodo
     */
    public function getDocumentosCargados(int $idCliente, string $fechaDesde, string $fechaHasta): array
    {
        return $this->db->table('tbl_reporte')
            ->select('tbl_reporte.titulo_reporte, tbl_reporte.created_at, tbl_reporte.enlace, detail_report.detail_report, report_type_table.report_type')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->where('tbl_reporte.id_cliente', $idCliente)
            ->where('tbl_reporte.created_at >=', $fechaDesde . ' 00:00:00')
            ->where('tbl_reporte.created_at <=', $fechaHasta . ' 23:59:59')
            ->orderBy('tbl_reporte.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    // =====================================================================
    // 6 MÓDULOS ADICIONALES PARA INFORME DE AVANCES
    // =====================================================================

    /**
     * 1. Firma electrónica: solicitudes y estado en el periodo
     */
    public function getFirmaElectronicaPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        $row = $this->db->table('tbl_doc_firma_solicitudes s')
            ->select("
                COUNT(s.id_solicitud) as total_solicitudes,
                SUM(CASE WHEN s.estado = 'firmado' THEN 1 ELSE 0 END) as firmados,
                SUM(CASE WHEN s.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN s.estado = 'esperando' THEN 1 ELSE 0 END) as esperando,
                SUM(CASE WHEN s.estado = 'expirado' THEN 1 ELSE 0 END) as expirados,
                SUM(CASE WHEN s.estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                SUM(CASE WHEN s.estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados
            ")
            ->join('tbl_documentos_sst d', 'd.id_documento = s.id_documento')
            ->where('d.id_cliente', $idCliente)
            ->where('s.created_at >=', $desde . ' 00:00:00')
            ->where('s.created_at <=', $hasta . ' 23:59:59')
            ->get()
            ->getRowArray();

        $total = intval($row['total_solicitudes'] ?? 0);
        $firmados = intval($row['firmados'] ?? 0);

        return [
            'total_solicitudes' => $total,
            'firmados'          => $firmados,
            'pendientes'        => intval($row['pendientes'] ?? 0) + intval($row['esperando'] ?? 0),
            'expirados'         => intval($row['expirados'] ?? 0),
            'rechazados'        => intval($row['rechazados'] ?? 0),
            'cancelados'        => intval($row['cancelados'] ?? 0),
            'tasa_firma'        => $total > 0 ? round(($firmados / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 2. Documentos SST creados/aprobados en el periodo
     */
    public function getDocumentosSstPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        // Docs creados en el periodo
        $porTipo = $this->db->table('tbl_documentos_sst')
            ->select("tipo_documento, COUNT(*) as cantidad")
            ->where('id_cliente', $idCliente)
            ->where('created_at >=', $desde . ' 00:00:00')
            ->where('created_at <=', $hasta . ' 23:59:59')
            ->groupBy('tipo_documento')
            ->get()
            ->getResultArray();

        $totalCreados = array_sum(array_column($porTipo, 'cantidad'));

        // Docs aprobados en el periodo
        $aprobados = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'aprobado')
            ->where('updated_at >=', $desde . ' 00:00:00')
            ->where('updated_at <=', $hasta . ' 23:59:59')
            ->countAllResults();

        return [
            'total_creados'     => $totalCreados,
            'por_tipo'          => $porTipo,
            'aprobados_periodo' => $aprobados,
        ];
    }

    /**
     * 3. Indicadores SST: resumen de cumplimiento y mediciones
     */
    public function getIndicadoresSstPeriodo(int $idCliente, int $anio): array
    {
        $indicadorModel = new \App\Models\IndicadorSSTModel();

        $resumenCategoria = $indicadorModel->getResumenPorCategoria($idCliente);
        $cumplimiento = $indicadorModel->verificarCumplimiento($idCliente);

        // Mediciones registradas en el año
        $mediciones = $this->db->table('tbl_indicadores_sst_mediciones m')
            ->select("COUNT(m.id_medicion) as total_mediciones, SUM(CASE WHEN m.cumple_meta = 1 THEN 1 ELSE 0 END) as cumplen")
            ->join('tbl_indicadores_sst i', 'i.id_indicador = m.id_indicador')
            ->where('i.id_cliente', $idCliente)
            ->where('i.activo', 1)
            ->like('m.periodo', (string) $anio, 'after')
            ->get()
            ->getRowArray();

        $totalMediciones = intval($mediciones['total_mediciones'] ?? 0);
        $cumplen = intval($mediciones['cumplen'] ?? 0);

        return [
            'total_activos'     => $cumplimiento['total'] ?? 0,
            'medidos_periodo'   => $totalMediciones,
            'cumplen_meta'      => $cumplen,
            'pct_cumplimiento'  => $totalMediciones > 0 ? round(($cumplen / $totalMediciones) * 100, 1) : 0,
            'por_categoria'     => $resumenCategoria,
        ];
    }

    /**
     * 4. Acciones correctivas: KPIs y estadísticas (delega a AccionesCorrectivasService)
     */
    public function getAccionesCorrectivasPeriodo(int $idCliente, int $anio): array
    {
        try {
            $accService = new \App\Services\AccionesCorrectivasService();
            $kpis = $accService->calcularKPIs($idCliente, $anio);
            $dashboard = $accService->getDashboardData($idCliente);

            return [
                'kpis'                   => [
                    'cierre_a_tiempo' => $kpis['cierre_a_tiempo']['valor'] ?? 0,
                    'efectividad'     => $kpis['efectividad']['valor'] ?? 0,
                    'dias_promedio'   => $kpis['dias_promedio']['valor'] ?? 0,
                    'reincidencia'    => $kpis['reincidencia']['valor'] ?? 0,
                ],
                'hallazgos_total'        => $dashboard['estadisticas']['hallazgos']['total'] ?? 0,
                'hallazgos_abiertos'     => $dashboard['estadisticas']['hallazgos']['por_estado']['abierto'] ?? 0,
                'hallazgos_cerrados'     => $dashboard['estadisticas']['hallazgos']['por_estado']['cerrado'] ?? 0,
                'acciones_total'         => $dashboard['estadisticas']['acciones']['total'] ?? 0,
                'acciones_vencidas'      => $dashboard['estadisticas']['acciones']['vencidas'] ?? 0,
            ];
        } catch (\Exception $e) {
            log_message('error', 'MetricasInforme: Error acciones correctivas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 5. Actas de comité: reuniones, cumplimiento y compromisos
     */
    public function getActasComitePeriodo(int $idCliente, string $desde, string $hasta, int $anio): array
    {
        $comiteModel = new \App\Models\ComiteModel();
        $actaModel = new \App\Models\ActaModel();
        $compromisoModel = new \App\Models\ActaCompromisoModel();

        $comites = $comiteModel->getByCliente($idCliente);

        // Actas en el periodo específico
        $actasPeriodo = $this->db->table('tbl_actas')
            ->where('id_cliente', $idCliente)
            ->where('fecha_reunion >=', $desde)
            ->where('fecha_reunion <=', $hasta)
            ->countAllResults();

        $porComite = [];
        $totalCompromisos = 0;
        $totalCumplidos = 0;
        $totalVencidos = 0;
        $totalPendientes = 0;

        foreach ($comites as $comite) {
            $idComite = $comite['id_comite'];
            $statsActas = $actaModel->getEstadisticas($idComite, $anio);
            $statsComp = $compromisoModel->getEstadisticas($idComite, $anio);

            $porComite[] = [
                'tipo_comite'  => $comite['codigo'] ?? $comite['tipo_nombre'] ?? 'Comité',
                'total_anio'   => $statsActas['total'],
                'firmadas'     => $statsActas['firmadas'],
                'esperadas'    => $statsActas['periodos_esperados'],
                'cumplimiento' => $statsActas['cumplimiento'],
            ];

            $totalCompromisos += $statsComp['total'];
            $totalCumplidos += $statsComp['cumplidos'];
            $totalVencidos += $statsComp['vencidos'];
            $totalPendientes += $statsComp['pendientes'];
        }

        return [
            'reuniones_periodo' => $actasPeriodo,
            'por_comite'        => $porComite,
            'compromisos'       => [
                'total'      => $totalCompromisos,
                'cumplidos'  => $totalCumplidos,
                'pendientes' => $totalPendientes,
                'vencidos'   => $totalVencidos,
                'pct'        => $totalCompromisos > 0 ? round(($totalCumplidos / $totalCompromisos) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * 6. Inspecciones: conteo por tipo y hallazgos
     */
    public function getInspeccionesPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        $tables = [
            'locativa'      => 'tbl_inspeccion_locativa',
            'extintores'    => 'tbl_inspeccion_extintores',
            'botiquin'      => 'tbl_inspeccion_botiquin',
            'senalizacion'  => 'tbl_inspeccion_senalizacion',
        ];

        $porTipo = [];
        $totalInspecciones = 0;
        $totalCompletadas = 0;

        foreach ($tables as $tipo => $table) {
            $rows = $this->db->table($table)
                ->select("estado, COUNT(*) as cantidad")
                ->where('id_cliente', $idCliente)
                ->where('fecha_inspeccion >=', $desde)
                ->where('fecha_inspeccion <=', $hasta)
                ->groupBy('estado')
                ->get()
                ->getResultArray();

            $total = 0;
            $completadas = 0;
            foreach ($rows as $r) {
                $cant = intval($r['cantidad']);
                $total += $cant;
                if ($r['estado'] === 'completada') {
                    $completadas = $cant;
                }
            }

            $porTipo[$tipo] = ['total' => $total, 'completadas' => $completadas];
            $totalInspecciones += $total;
            $totalCompletadas += $completadas;
        }

        // Hallazgos locativos en el periodo
        $hallazgos = $this->db->table('tbl_hallazgo_locativo h')
            ->select("
                COUNT(*) as total,
                SUM(CASE WHEN h.fecha_correccion IS NOT NULL THEN 1 ELSE 0 END) as corregidos
            ")
            ->join('tbl_inspeccion_locativa i', 'i.id = h.id_inspeccion')
            ->where('i.id_cliente', $idCliente)
            ->where('i.fecha_inspeccion >=', $desde)
            ->where('i.fecha_inspeccion <=', $hasta)
            ->get()
            ->getRowArray();

        $totalHallazgos = intval($hallazgos['total'] ?? 0);
        $corregidos = intval($hallazgos['corregidos'] ?? 0);

        return [
            'por_tipo'           => $porTipo,
            'total_inspecciones' => $totalInspecciones,
            'total_completadas'  => $totalCompletadas,
            'hallazgos'          => [
                'total'      => $totalHallazgos,
                'corregidos' => $corregidos,
                'pendientes' => $totalHallazgos - $corregidos,
            ],
        ];
    }

    /**
     * Actividades PTA no cerradas en el periodo, con estado y observación
     */
    public function getActividadesNoCerradasPta(int $idCliente, string $desde, string $hasta): string
    {
        $estadosCerradas = ['CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO'];

        $rows = $this->db->table('tbl_pta_cliente')
            ->select('actividad_plandetrabajo, estado_actividad, observaciones')
            ->where('id_cliente', $idCliente)
            ->where('fecha_propuesta >=', $desde)
            ->where('fecha_propuesta <=', $hasta)
            ->whereNotIn('estado_actividad', $estadosCerradas)
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return '';
        }

        $lines = [];
        foreach ($rows as $r) {
            $actividad = $r['actividad_plandetrabajo'] ?? 'Sin nombre';
            $estado = $r['estado_actividad'] ?? 'Sin estado';
            $obs = trim($r['observaciones'] ?? '');
            $obs = $obs ?: 'Sin observación registrada';
            $lines[] = "- {$actividad} (Estado: {$estado} | Obs: {$obs})";
        }

        return implode("\n", $lines);
    }
}
