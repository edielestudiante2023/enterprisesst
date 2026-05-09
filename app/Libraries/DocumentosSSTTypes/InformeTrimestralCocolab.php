<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase InformeTrimestralCocolab
 *
 * Informe trimestral de gestion del COCOLAB / Comite de Convivencia Laboral (numeral 1.1.8).
 *
 * Flujo: secciones_ia. Sobrescribe getContextoBase() para inyectar a la IA datos REALES del comite
 * consultados de las tablas del modulo /actas (filtrando por tbl_tipos_comite.codigo='COCOLAB'),
 * filtrados por anio + trimestre.
 *
 * Mantiene confidencialidad sobre los casos atendidos: los prompts en BD instruyen a la IA a NO
 * mencionar nombres de quejosos / denunciados ni areas individualizables.
 *
 * Ver docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/InformeTrimestralCocolab.md
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class InformeTrimestralCocolab extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'informe_trimestral_cocolab';
    }

    public function getNombre(): string
    {
        return 'Informe Trimestral de Gestion del COCOLAB';
    }

    public function getDescripcion(): string
    {
        return 'Informe trimestral del Comite de Convivencia Laboral con datos reales y casos en agregado anonimo.';
    }

    public function getEstandar(): ?string
    {
        return '1.1.8';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Resumen Ejecutivo',                                   'key' => 'resumen_ejecutivo'],
            ['numero' => 2, 'nombre' => 'Conformacion del COCOLAB',                            'key' => 'conformacion_comite'],
            ['numero' => 3, 'nombre' => 'Reuniones Realizadas',                                'key' => 'reuniones_realizadas'],
            ['numero' => 4, 'nombre' => 'Asistencia',                                          'key' => 'asistencia'],
            ['numero' => 5, 'nombre' => 'Casos / Quejas Atendidos',                            'key' => 'casos_atendidos'],
            ['numero' => 6, 'nombre' => 'Cumplimiento del Cronograma',                         'key' => 'cumplimiento_cronograma'],
            ['numero' => 7, 'nombre' => 'Hallazgos / Tendencias',                              'key' => 'hallazgos'],
            ['numero' => 8, 'nombre' => 'Recomendaciones del Consultor SST',                   'key' => 'recomendaciones_ia'],
            ['numero' => 9, 'nombre' => 'Compromisos / Plan de Accion del Proximo Trimestre',  'key' => 'plan_accion_proximo'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Sobrescribe getContextoBase para inyectar datos reales del COCOLAB en el trimestre.
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $idCliente = (int) ($cliente['id_cliente'] ?? 0);
        $anio      = (int) ($contexto['anio'] ?? date('Y'));
        $trimestre = (int) ($contexto['trimestre'] ?? 0);
        if ($trimestre < 1 || $trimestre > 4) {
            $trimestre = (int) ceil(((int) date('n')) / 3);
        }

        $base = parent::getContextoBase($cliente, $contexto);
        $rangoTrim = InformeTrimestralCopasst::rangoFechasTrimestre($anio, $trimestre);

        $bloque = $this->bloqueComite($idCliente, $anio, $rangoTrim, $trimestre);

        return $base
            . "\n\n============================================================\n"
            . "DATOS REALES DEL COCOLAB EN EL TRIMESTRE {$trimestre} DEL ANIO {$anio}\n"
            . "Periodo: {$rangoTrim['inicio']} a {$rangoTrim['fin']}\n"
            . "RECORDATORIO: mantener confidencialidad sobre los casos. NO nombres de quejosos / denunciados, NO areas individualizables.\n"
            . "============================================================\n"
            . $bloque;
    }

    /**
     * Datos crudos del COCOLAB para el periodo (texto plano para la IA).
     */
    protected function bloqueComite(int $idCliente, int $anio, array $rango, int $trimestre): string
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
            return "[ATENCION] No hay COCOLAB activo registrado para este cliente en /actas. La IA debera generar el informe asumiendo que aun no hay datos operativos del comite.\n";
        }

        $idComite = (int) $comite['id_comite'];
        $out  = "COMITE:\n";
        $out .= "- Tipo: {$comite['codigo']} ({$comite['tipo_nombre']})\n";
        $out .= "- Estado: {$comite['estado']}\n";
        $out .= "- Conformado: {$comite['fecha_conformacion']}\n";
        $out .= "- Vence: " . ($comite['fecha_vencimiento'] ?: 'sin fecha') . "\n";
        $out .= "- Dia preferido reunion: " . ($comite['dia_reunion_preferido'] ?: 'no definido') . "\n";
        $out .= "- Lugar habitual: " . ($comite['lugar_habitual'] ?: 'no definido') . "\n";
        $out .= "- Periodicidad esperada: cada {$comite['periodicidad_dias']} dias\n";
        $out .= "- Quorum minimo: {$comite['quorum_minimo_porcentaje']}%\n\n";

        $miembros = $db->table('tbl_comite_miembros')
            ->select('nombre_completo, cargo, area_dependencia, representacion, tipo_miembro, rol_comite, fecha_ingreso, fecha_retiro, estado')
            ->where('id_comite', $idComite)
            ->orderBy('tipo_miembro', 'ASC')->orderBy('rol_comite', 'ASC')
            ->get()->getResultArray();

        $out .= "MIEMBROS DEL COMITE (" . count($miembros) . "):\n";
        foreach ($miembros as $m) {
            $rol = $m['rol_comite'] ?: '';
            $linea = "- {$m['nombre_completo']} | {$m['cargo']} | rep={$m['representacion']} | tipo={$m['tipo_miembro']} | rol={$rol} | ingreso={$m['fecha_ingreso']}";
            if (!empty($m['fecha_retiro'])) {
                $linea .= " | retiro={$m['fecha_retiro']}";
            }
            $linea .= " | estado={$m['estado']}";
            $out .= $linea . "\n";
        }
        $out .= "\n";

        $actas = $db->table('tbl_actas')
            ->select('id_acta, numero_acta, fecha_reunion, hora_inicio, hora_fin, lugar, modalidad, quorum_requerido, quorum_presente, hay_quorum, orden_del_dia, conclusiones, observaciones, estado')
            ->where('id_comite', $idComite)
            ->where('anio', $anio)
            ->where('fecha_reunion >=', $rango['inicio'])
            ->where('fecha_reunion <=', $rango['fin'])
            ->orderBy('fecha_reunion', 'ASC')
            ->get()->getResultArray();

        $out .= "ACTAS / REUNIONES EN EL TRIMESTRE (" . count($actas) . "):\n";
        if (count($actas) === 0) {
            $out .= "  (sin actas registradas en el periodo)\n\n";
        }
        $idsActas = [];
        foreach ($actas as $a) {
            $idsActas[] = (int) $a['id_acta'];
            $orden = $a['orden_del_dia'] ? trim(strip_tags(InformeTrimestralCopasst::resumirJson($a['orden_del_dia']))) : '';
            $concl = $a['conclusiones'] ? trim(mb_substr($a['conclusiones'], 0, 400)) : '';
            $out .= "- Acta #{$a['numero_acta']} | {$a['fecha_reunion']} {$a['hora_inicio']}-{$a['hora_fin']} | {$a['modalidad']} | lugar: {$a['lugar']} | quorum: {$a['quorum_presente']}/{$a['quorum_requerido']} (" . ($a['hay_quorum'] ? 'SI' : 'NO') . ") | estado: {$a['estado']}\n";
            if ($orden) $out .= "   Orden del dia (resumen): {$orden}\n";
            if ($concl) $out .= "   Conclusiones: {$concl}\n";
            if (!empty($a['observaciones'])) $out .= "   Observaciones: " . trim(mb_substr($a['observaciones'], 0, 250)) . "\n";
        }
        $out .= "\n";

        // Asistencia agregada (raw SQL para evitar el bug del parser de QueryBuilder con CASE WHEN)
        if (!empty($idsActas)) {
            $placeholders = implode(',', array_fill(0, count($idsActas), '?'));
            $asist = $db->query(
                "SELECT nombre_completo, cargo,
                        COUNT(*) AS total,
                        SUM(asistio) AS asistio,
                        SUM(CASE WHEN asistio = 0 AND justificacion_ausencia IS NOT NULL AND justificacion_ausencia <> '' THEN 1 ELSE 0 END) AS justificadas
                 FROM tbl_acta_asistentes
                 WHERE id_acta IN ({$placeholders})
                 GROUP BY nombre_completo, cargo
                 ORDER BY asistio DESC",
                $idsActas
            )->getResultArray();

            $out .= "ASISTENCIA EN EL TRIMESTRE (por persona):\n";
            foreach ($asist as $a) {
                $tot = (int) $a['total'];
                $as  = (int) $a['asistio'];
                $jus = (int) $a['justificadas'];
                $pct = $tot > 0 ? round(($as / $tot) * 100) : 0;
                $out .= "- {$a['nombre_completo']} ({$a['cargo']}): {$as}/{$tot} = {$pct}% | ausencias justificadas: {$jus}\n";
            }
            $out .= "\n";
        }

        // Compromisos del trimestre
        if (!empty($idsActas)) {
            $comp = $db->table('tbl_acta_compromisos')
                ->select('numero_compromiso, descripcion, responsable_nombre, fecha_compromiso, fecha_vencimiento, fecha_cierre_efectiva, estado, porcentaje_avance, prioridad')
                ->whereIn('id_acta', $idsActas)
                ->orderBy('estado', 'ASC')->orderBy('fecha_vencimiento', 'ASC')
                ->get()->getResultArray();

            $out .= "COMPROMISOS GENERADOS EN EL TRIMESTRE (" . count($comp) . "):\n";
            foreach ($comp as $c) {
                $out .= "- #{$c['numero_compromiso']} | {$c['descripcion']} | resp: {$c['responsable_nombre']} | vence: {$c['fecha_vencimiento']} | estado: {$c['estado']} | avance: {$c['porcentaje_avance']}% | prioridad: {$c['prioridad']}\n";
            }
            $out .= "\n";
        }

        $abiertos = $db->table('tbl_acta_compromisos')
            ->select('numero_compromiso, descripcion, responsable_nombre, fecha_vencimiento, estado, porcentaje_avance')
            ->where('id_comite', $idComite)
            ->whereIn('estado', ['pendiente', 'en_proceso', 'vencido'])
            ->where('fecha_compromiso <=', $rango['fin'])
            ->orderBy('fecha_vencimiento', 'ASC')
            ->get()->getResultArray();

        $out .= "COMPROMISOS ABIERTOS AL CIERRE DEL TRIMESTRE (" . count($abiertos) . "):\n";
        foreach ($abiertos as $c) {
            $out .= "- #{$c['numero_compromiso']} | {$c['descripcion']} | resp: {$c['responsable_nombre']} | vence: {$c['fecha_vencimiento']} | estado: {$c['estado']} | avance: {$c['porcentaje_avance']}%\n";
        }

        return $out;
    }
}
