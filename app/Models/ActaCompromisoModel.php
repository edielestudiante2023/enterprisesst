<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaCompromisoModel extends Model
{
    protected $table = 'tbl_acta_compromisos';
    protected $primaryKey = 'id_compromiso';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_acta',
        'id_comite',
        'id_cliente',
        'numero_compromiso',
        'descripcion',
        'punto_orden_del_dia',
        'responsable_nombre',
        'responsable_email',
        'responsable_id_miembro',
        'fecha_compromiso',
        'fecha_vencimiento',
        'fecha_cierre_efectiva',
        'estado',
        'porcentaje_avance',
        'prioridad',
        'evidencia_descripcion',
        'evidencia_archivo',
        'cerrado_por',
        'cerrado_at',
        'token_actualizacion',
        'ultima_notificacion_at',
        'total_notificaciones'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener compromisos de un acta
     */
    public function getByActa(int $idActa): array
    {
        return $this->select('*, fecha_vencimiento as fecha_limite')
                    ->where('id_acta', $idActa)
                    ->orderBy('numero_compromiso', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener compromisos de un comité
     */
    public function getByComite(int $idComite, ?string $estado = null): array
    {
        $builder = $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta')
                        ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta', 'left')
                        ->where('tbl_acta_compromisos.id_comite', $idComite);

        if ($estado) {
            $builder->where('tbl_acta_compromisos.estado', $estado);
        }

        return $builder->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')->findAll();
    }

    /**
     * Obtener compromisos de un cliente
     */
    public function getByCliente(int $idCliente, ?string $estado = null): array
    {
        $builder = $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta, tbl_tipos_comite.codigo as tipo_comite')
                        ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta')
                        ->join('tbl_comites', 'tbl_comites.id_comite = tbl_acta_compromisos.id_comite')
                        ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                        ->where('tbl_acta_compromisos.id_cliente', $idCliente);

        if ($estado) {
            $builder->where('tbl_acta_compromisos.estado', $estado);
        }

        return $builder->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')->findAll();
    }

    /**
     * Obtener compromisos pendientes de un responsable
     */
    public function getByResponsable(string $email, ?int $idCliente = null): array
    {
        $builder = $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta, tbl_tipos_comite.codigo as tipo_comite')
                        ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta')
                        ->join('tbl_comites', 'tbl_comites.id_comite = tbl_acta_compromisos.id_comite')
                        ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                        ->where('tbl_acta_compromisos.responsable_email', $email)
                        ->whereIn('tbl_acta_compromisos.estado', ['pendiente', 'en_proceso']);

        if ($idCliente) {
            $builder->where('tbl_acta_compromisos.id_cliente', $idCliente);
        }

        return $builder->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')->findAll();
    }

    /**
     * Obtener compromisos vencidos
     */
    public function getVencidos(?int $idCliente = null): array
    {
        $builder = $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta, tbl_tipos_comite.codigo as tipo_comite, tbl_clientes.nombre_cliente')
                        ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta')
                        ->join('tbl_comites', 'tbl_comites.id_comite = tbl_acta_compromisos.id_comite')
                        ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                        ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_compromisos.id_cliente')
                        ->whereIn('tbl_acta_compromisos.estado', ['pendiente', 'en_proceso'])
                        ->where('tbl_acta_compromisos.fecha_vencimiento <', date('Y-m-d'));

        if ($idCliente) {
            $builder->where('tbl_acta_compromisos.id_cliente', $idCliente);
        }

        return $builder->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')->findAll();
    }

    /**
     * Obtener compromisos próximos a vencer
     */
    public function getProximosAVencer(int $dias = 7, ?int $idCliente = null): array
    {
        $fechaLimite = date('Y-m-d', strtotime("+{$dias} days"));

        $builder = $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta, tbl_tipos_comite.codigo as tipo_comite')
                        ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta')
                        ->join('tbl_comites', 'tbl_comites.id_comite = tbl_acta_compromisos.id_comite')
                        ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                        ->whereIn('tbl_acta_compromisos.estado', ['pendiente', 'en_proceso'])
                        ->where('tbl_acta_compromisos.fecha_vencimiento >=', date('Y-m-d'))
                        ->where('tbl_acta_compromisos.fecha_vencimiento <=', $fechaLimite);

        if ($idCliente) {
            $builder->where('tbl_acta_compromisos.id_cliente', $idCliente);
        }

        return $builder->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')->findAll();
    }

    /**
     * Crear compromiso con número automático
     */
    public function crearCompromiso(array $data): int|false
    {
        // Obtener último número de compromiso del acta
        $ultimo = $this->where('id_acta', $data['id_acta'])
                       ->orderBy('numero_compromiso', 'DESC')
                       ->first();

        $data['numero_compromiso'] = ($ultimo['numero_compromiso'] ?? 0) + 1;
        $data['estado'] = 'pendiente';
        $data['porcentaje_avance'] = 0;

        // Generar token de actualización
        $data['token_actualizacion'] = bin2hex(random_bytes(32));

        return $this->insert($data);
    }

    /**
     * Obtener compromiso por token de actualización
     */
    public function getByToken(string $token): ?array
    {
        return $this->where('token_actualizacion', $token)->first();
    }

    /**
     * Actualizar estado del compromiso
     */
    public function actualizarEstado(int $idCompromiso, string $estado, ?int $porcentaje = null): bool
    {
        $data = ['estado' => $estado];

        if ($porcentaje !== null) {
            $data['porcentaje_avance'] = $porcentaje;
        }

        if ($estado === 'cumplido') {
            $data['fecha_cierre_efectiva'] = date('Y-m-d');
            $data['cerrado_at'] = date('Y-m-d H:i:s');
            $data['porcentaje_avance'] = 100;
        }

        return $this->update($idCompromiso, $data);
    }

    /**
     * Cerrar compromiso con evidencia
     */
    public function cerrarConEvidencia(int $idCompromiso, string $descripcion, ?string $archivo = null, string $cerradoPor = ''): bool
    {
        return $this->update($idCompromiso, [
            'estado' => 'cumplido',
            'porcentaje_avance' => 100,
            'fecha_cierre_efectiva' => date('Y-m-d'),
            'evidencia_descripcion' => $descripcion,
            'evidencia_archivo' => $archivo,
            'cerrado_por' => $cerradoPor,
            'cerrado_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marcar compromisos vencidos automáticamente
     */
    public function marcarVencidos(): int
    {
        $db = \Config\Database::connect();

        return $db->table($this->table)
                  ->whereIn('estado', ['pendiente', 'en_proceso'])
                  ->where('fecha_vencimiento <', date('Y-m-d'))
                  ->update(['estado' => 'vencido']);
    }

    /**
     * Obtener estadísticas de compromisos
     */
    public function getEstadisticas(int $idComite, ?int $anio = null): array
    {
        $builder = $this->where('id_comite', $idComite);

        if ($anio) {
            $builder->where('YEAR(fecha_compromiso)', $anio);
        }

        $compromisos = $builder->findAll();

        $stats = [
            'total' => count($compromisos),
            'pendientes' => 0,
            'en_proceso' => 0,
            'cumplidos' => 0,
            'vencidos' => 0,
            'cancelados' => 0,
            'cumplimiento_a_tiempo' => 0,
            'cumplimiento_tardio' => 0
        ];

        foreach ($compromisos as $c) {
            $stats[$c['estado'] === 'en_proceso' ? 'en_proceso' : $c['estado'] . 's']++;

            if ($c['estado'] === 'cumplido') {
                if ($c['fecha_cierre_efectiva'] <= $c['fecha_vencimiento']) {
                    $stats['cumplimiento_a_tiempo']++;
                } else {
                    $stats['cumplimiento_tardio']++;
                }
            }
        }

        $stats['porcentaje_cumplimiento'] = $stats['total'] > 0
            ? round(($stats['cumplidos'] / $stats['total']) * 100, 1)
            : 0;

        return $stats;
    }

    /**
     * Obtener compromisos para notificación semanal
     */
    public function getParaNotificacionSemanal(string $email): array
    {
        return [
            'pendientes' => $this->getByResponsable($email),
            'vencidos' => $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta')
                               ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta')
                               ->where('responsable_email', $email)
                               ->where('estado', 'vencido')
                               ->findAll(),
            'proximos' => $this->getProximosAVencer(7)
        ];
    }

    /**
     * Obtener compromisos pendientes de actas anteriores (para seguimiento)
     */
    public function getPendientesActasAnteriores(int $idComite): array
    {
        return $this->select('tbl_acta_compromisos.*, tbl_actas.numero_acta, tbl_actas.fecha_reunion')
                    ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta')
                    ->where('tbl_acta_compromisos.id_comite', $idComite)
                    ->whereIn('tbl_acta_compromisos.estado', ['pendiente', 'en_proceso', 'vencido'])
                    ->orderBy('tbl_actas.fecha_reunion', 'ASC')
                    ->orderBy('tbl_acta_compromisos.numero_compromiso', 'ASC')
                    ->findAll();
    }
}
