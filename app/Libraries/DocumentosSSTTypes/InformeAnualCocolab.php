<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase InformeAnualCocolab
 *
 * Informe anual de gestion del COCOLAB (numeral 1.1.8). Sobrescribe getContextoBase()
 * para inyectar todo el anio del comite (filtrando por tbl_tipos_comite.codigo='COCOLAB')
 * mas un comparativo trimestral.
 *
 * Mantiene confidencialidad sobre los casos atendidos.
 *
 * Ver docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/InformeAnualCocolab.md
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class InformeAnualCocolab extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'informe_anual_cocolab';
    }

    public function getNombre(): string
    {
        return 'Informe Anual de Gestion del COCOLAB';
    }

    public function getDescripcion(): string
    {
        return 'Informe anual del Comite de Convivencia Laboral con datos reales del comite y comparativo trimestral.';
    }

    public function getEstandar(): ?string
    {
        return '1.1.8';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1,  'nombre' => 'Resumen Ejecutivo Anual',                'key' => 'resumen_ejecutivo'],
            ['numero' => 2,  'nombre' => 'Conformacion del COCOLAB',               'key' => 'conformacion_comite'],
            ['numero' => 3,  'nombre' => 'Comparativo Trimestral',                 'key' => 'comparativo_trimestres'],
            ['numero' => 4,  'nombre' => 'Reuniones Realizadas en el Anio',        'key' => 'reuniones_realizadas'],
            ['numero' => 5,  'nombre' => 'Asistencia Anual',                       'key' => 'asistencia'],
            ['numero' => 6,  'nombre' => 'Casos / Quejas Atendidos en el Anio',    'key' => 'casos_atendidos'],
            ['numero' => 7,  'nombre' => 'Cumplimiento del Cronograma Anual',      'key' => 'cumplimiento_cronograma'],
            ['numero' => 8,  'nombre' => 'Hallazgos del Anio',                     'key' => 'hallazgos'],
            ['numero' => 9,  'nombre' => 'Recomendaciones del Consultor SST',     'key' => 'recomendaciones_ia'],
            ['numero' => 10, 'nombre' => 'Plan de Accion para el Proximo Anio',    'key' => 'plan_accion_proximo'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $idCliente = (int) ($cliente['id_cliente'] ?? 0);
        $anio      = (int) ($contexto['anio'] ?? date('Y'));

        $base = parent::getContextoBase($cliente, $contexto);

        $bloqueAnual = $this->bloqueAnual($idCliente, $anio);
        $comparativo = $this->bloqueComparativoTrimestral($idCliente, $anio);

        return $base
            . "\n\n============================================================\n"
            . "DATOS REALES DEL COCOLAB EN EL ANIO {$anio}\n"
            . "RECORDATORIO: mantener confidencialidad sobre los casos. NO nombres de quejosos / denunciados, NO areas individualizables.\n"
            . "============================================================\n"
            . $bloqueAnual
            . "\n\n============================================================\n"
            . "COMPARATIVO TRIMESTRAL DEL ANIO {$anio}\n"
            . "============================================================\n"
            . $comparativo;
    }

    protected function bloqueAnual(int $idCliente, int $anio): string
    {
        $db = \Config\Database::connect();

        $comite = $db->table('tbl_comites c')
            ->select('c.id_comite, c.fecha_conformacion, c.fecha_vencimiento, c.dia_reunion_preferido, c.lugar_habitual, c.estado, t.codigo, t.nombre AS tipo_nombre, t.periodicidad_dias, t.quorum_minimo_porcentaje')
            ->join('tbl_tipos_comite t', 't.id_tipo = c.id_tipo')
            ->where('c.id_cliente', $idCliente)
            ->where('t.codigo', 'COCOLAB')
            ->where('c.estado', 'activo')
            ->orderBy('c.created_at', 'DESC')
            ->get()->getRowArray();

        if (!$comite) {
            return "[ATENCION] No hay COCOLAB activo registrado para este cliente. La IA debera generar el informe asumiendo que aun no hay datos operativos del comite.\n";
        }

        $idComite = (int) $comite['id_comite'];
        $out  = "COMITE:\n";
        $out .= "- Tipo: {$comite['codigo']} ({$comite['tipo_nombre']})\n";
        $out .= "- Estado: {$comite['estado']}\n";
        $out .= "- Conformado: {$comite['fecha_conformacion']}\n";
        $out .= "- Vence: " . ($comite['fecha_vencimiento'] ?: 'sin fecha') . "\n";
        $out .= "- Periodicidad esperada: cada {$comite['periodicidad_dias']} dias (aprox " . round(365 / max(1, (int)$comite['periodicidad_dias'])) . " reuniones/anio)\n\n";

        $miembros = $db->table('tbl_comite_miembros')
            ->select('nombre_completo, cargo, representacion, tipo_miembro, rol_comite, fecha_ingreso, fecha_retiro, motivo_retiro, estado')
            ->where('id_comite', $idComite)
            ->orderBy('tipo_miembro', 'ASC')
            ->get()->getResultArray();

        $out .= "MIEMBROS DEL COMITE EN EL ANIO (" . count($miembros) . "):\n";
        foreach ($miembros as $m) {
            $linea = "- {$m['nombre_completo']} | {$m['cargo']} | rep={$m['representacion']} | tipo={$m['tipo_miembro']} | rol={$m['rol_comite']} | ingreso={$m['fecha_ingreso']}";
            if (!empty($m['fecha_retiro'])) {
                $linea .= " | retiro={$m['fecha_retiro']}";
                if (!empty($m['motivo_retiro'])) $linea .= " ({$m['motivo_retiro']})";
            }
            $linea .= " | estado={$m['estado']}";
            $out .= $linea . "\n";
        }
        $out .= "\n";

        $actas = $db->table('tbl_actas')
            ->select('id_acta, numero_acta, fecha_reunion, modalidad, hay_quorum, conclusiones, observaciones, estado')
            ->where('id_comite', $idComite)
            ->where('anio', $anio)
            ->orderBy('fecha_reunion', 'ASC')
            ->get()->getResultArray();

        $out .= "ACTAS DEL ANIO (" . count($actas) . "):\n";
        $idsActas = [];
        foreach ($actas as $a) {
            $idsActas[] = (int) $a['id_acta'];
            $concl = $a['conclusiones'] ? trim(mb_substr($a['conclusiones'], 0, 300)) : '';
            $out .= "- Acta #{$a['numero_acta']} | {$a['fecha_reunion']} | {$a['modalidad']} | quorum: " . ($a['hay_quorum'] ? 'SI' : 'NO') . " | estado: {$a['estado']}\n";
            if ($concl) $out .= "   Conclusiones: {$concl}\n";
        }
        $out .= "\n";

        if (!empty($idsActas)) {
            $placeholders = implode(',', array_fill(0, count($idsActas), '?'));
            $asist = $db->query(
                "SELECT nombre_completo, cargo,
                        COUNT(*) AS total,
                        SUM(asistio) AS asistio
                 FROM tbl_acta_asistentes
                 WHERE id_acta IN ({$placeholders})
                 GROUP BY nombre_completo, cargo
                 ORDER BY asistio DESC",
                $idsActas
            )->getResultArray();

            $out .= "ASISTENCIA ANUAL (por persona):\n";
            foreach ($asist as $a) {
                $tot = (int) $a['total'];
                $as  = (int) $a['asistio'];
                $pct = $tot > 0 ? round(($as / $tot) * 100) : 0;
                $out .= "- {$a['nombre_completo']} ({$a['cargo']}): {$as}/{$tot} = {$pct}%\n";
            }
            $out .= "\n";
        }

        $comp = $db->table('tbl_acta_compromisos')
            ->select('descripcion, responsable_nombre, fecha_compromiso, fecha_vencimiento, fecha_cierre_efectiva, estado, porcentaje_avance, prioridad')
            ->where('id_comite', $idComite)
            ->where('YEAR(fecha_compromiso)', $anio)
            ->orderBy('estado', 'ASC')
            ->get()->getResultArray();

        $out .= "COMPROMISOS DEL ANIO (" . count($comp) . "):\n";
        $totalComp = count($comp);
        $cerrados = 0; $vencidos = 0; $pend = 0;
        foreach ($comp as $c) {
            if ($c['estado'] === 'cumplido' || !empty($c['fecha_cierre_efectiva'])) $cerrados++;
            elseif ($c['estado'] === 'vencido') $vencidos++;
            else $pend++;
            $out .= "- {$c['descripcion']} | resp: {$c['responsable_nombre']} | vence: {$c['fecha_vencimiento']} | estado: {$c['estado']} | avance: {$c['porcentaje_avance']}%\n";
        }
        if ($totalComp > 0) {
            $out .= "\nResumen compromisos: total={$totalComp}, cerrados={$cerrados}, vencidos={$vencidos}, pendientes/en_proceso={$pend}\n";
        }

        return $out;
    }

    protected function bloqueComparativoTrimestral(int $idCliente, int $anio): string
    {
        $db = \Config\Database::connect();
        $comite = $db->table('tbl_comites c')
            ->select('c.id_comite')
            ->join('tbl_tipos_comite t', 't.id_tipo = c.id_tipo')
            ->where('c.id_cliente', $idCliente)
            ->where('t.codigo', 'COCOLAB')
            ->where('c.estado', 'activo')
            ->orderBy('c.created_at', 'DESC')
            ->get()->getRowArray();
        if (!$comite) return "(sin comite activo)\n";
        $idComite = (int) $comite['id_comite'];

        $out = "Trim | Reuniones | Asistencia % prom | Compromisos generados | Compromisos cerrados\n";
        for ($t = 1; $t <= 4; $t++) {
            $r = InformeTrimestralCopasst::rangoFechasTrimestre($anio, $t);

            $reuniones = $db->table('tbl_actas')
                ->where('id_comite', $idComite)
                ->where('anio', $anio)
                ->where('fecha_reunion >=', $r['inicio'])
                ->where('fecha_reunion <=', $r['fin'])
                ->countAllResults();

            $asistInfo = $db->query(
                "SELECT SUM(a.asistio) AS asistio, COUNT(*) AS total
                 FROM tbl_acta_asistentes a
                 JOIN tbl_actas ac ON ac.id_acta = a.id_acta
                 WHERE ac.id_comite = ?
                   AND ac.anio = ?
                   AND ac.fecha_reunion >= ?
                   AND ac.fecha_reunion <= ?",
                [$idComite, $anio, $r['inicio'], $r['fin']]
            )->getRowArray();

            $tot = (int) ($asistInfo['total'] ?? 0);
            $as  = (int) ($asistInfo['asistio'] ?? 0);
            $pctAsis = $tot > 0 ? round(($as / $tot) * 100) : 0;

            $compGen = $db->table('tbl_acta_compromisos c')
                ->join('tbl_actas ac', 'ac.id_acta = c.id_acta')
                ->where('ac.id_comite', $idComite)
                ->where('ac.anio', $anio)
                ->where('ac.fecha_reunion >=', $r['inicio'])
                ->where('ac.fecha_reunion <=', $r['fin'])
                ->countAllResults();

            $compCer = $db->table('tbl_acta_compromisos c')
                ->join('tbl_actas ac', 'ac.id_acta = c.id_acta')
                ->where('ac.id_comite', $idComite)
                ->where('ac.anio', $anio)
                ->where('ac.fecha_reunion >=', $r['inicio'])
                ->where('ac.fecha_reunion <=', $r['fin'])
                ->where('c.estado', 'cumplido')
                ->countAllResults();

            $out .= "T{$t}   | {$reuniones}         | {$pctAsis}%               | {$compGen}                     | {$compCer}\n";
        }
        return $out;
    }
}
