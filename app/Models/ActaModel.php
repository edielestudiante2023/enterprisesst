<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaModel extends Model
{
    protected $table = 'tbl_actas';
    protected $primaryKey = 'id_acta';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_comite',
        'id_cliente',
        'numero_acta',
        'consecutivo_anual',
        'anio',
        'tipo_acta',
        'fecha_reunion',
        'hora_inicio',
        'hora_fin',
        'lugar',
        'modalidad',
        'enlace_virtual',
        'quorum_requerido',
        'quorum_presente',
        'hay_quorum',
        'orden_del_dia',
        'desarrollo',
        'conclusiones',
        'observaciones',
        'proxima_reunion_fecha',
        'proxima_reunion_hora',
        'proxima_reunion_lugar',
        'estado',
        'total_firmantes',
        'firmantes_completados',
        'fecha_cierre',
        'cerrada_por',
        'codigo_verificacion',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // NO usar casts automáticos - manejar JSON manualmente para evitar conflictos
    // protected array $casts = [
    //     'orden_del_dia' => 'json-array',
    //     'desarrollo' => 'json-array'
    // ];

    /**
     * Obtener actas de un comité
     */
    public function getByComite(int $idComite, ?int $anio = null): array
    {
        $builder = $this->where('id_comite', $idComite);

        if ($anio) {
            $builder->where('anio', $anio);
        }

        return $builder->orderBy('fecha_reunion', 'DESC')->findAll();
    }

    /**
     * Obtener actas de un cliente (todos los comités)
     */
    public function getByCliente(int $idCliente, ?int $anio = null): array
    {
        $builder = $this->select('tbl_actas.*, tbl_tipos_comite.codigo as tipo_comite, tbl_tipos_comite.nombre as nombre_comite')
                        ->join('tbl_comites', 'tbl_comites.id_comite = tbl_actas.id_comite')
                        ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                        ->where('tbl_actas.id_cliente', $idCliente);

        if ($anio) {
            $builder->where('tbl_actas.anio', $anio);
        }

        return $builder->orderBy('tbl_actas.fecha_reunion', 'DESC')->findAll();
    }

    /**
     * Obtener acta con todos los detalles
     */
    public function getConDetalles(int $idActa): ?array
    {
        $acta = $this->select('tbl_actas.*, tbl_tipos_comite.codigo as tipo_comite,
                               tbl_tipos_comite.nombre as nombre_comite,
                               tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
                     ->join('tbl_comites', 'tbl_comites.id_comite = tbl_actas.id_comite')
                     ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                     ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_actas.id_cliente')
                     ->find($idActa);

        if ($acta) {
            // Decodificar JSON si viene como string
            if (is_string($acta['orden_del_dia'])) {
                $acta['orden_del_dia'] = json_decode($acta['orden_del_dia'], true) ?? [];
            }
            if (is_string($acta['desarrollo'])) {
                $acta['desarrollo'] = json_decode($acta['desarrollo'], true) ?? [];
            }

            // Obtener asistentes
            $asistentesModel = new ActaAsistenteModel();
            $acta['asistentes'] = $asistentesModel->getByActa($idActa);

            // Obtener compromisos
            $compromisosModel = new ActaCompromisoModel();
            $acta['compromisos'] = $compromisosModel->getByActa($idActa);
        }

        return $acta;
    }

    /**
     * Generar número de acta consecutivo
     */
    public function generarNumeroActa(int $idComite, int $anio): array
    {
        // Obtener tipo de comité
        $comiteModel = new ComiteModel();
        $comite = $comiteModel->getConDetalles($idComite);
        $codigo = $comite['codigo'] ?? 'GEN';

        // Obtener último consecutivo del año
        $ultimo = $this->where('id_comite', $idComite)
                       ->where('anio', $anio)
                       ->orderBy('consecutivo_anual', 'DESC')
                       ->first();

        $consecutivo = ($ultimo['consecutivo_anual'] ?? 0) + 1;
        $numeroActa = sprintf('ACT-%s-%d-%03d', $codigo, $anio, $consecutivo);

        return [
            'numero_acta' => $numeroActa,
            'consecutivo_anual' => $consecutivo,
            'anio' => $anio
        ];
    }

    /**
     * Crear acta nueva
     */
    public function crearActa(array $data): int|false
    {
        // Generar número de acta
        $anio = date('Y', strtotime($data['fecha_reunion']));
        $numeros = $this->generarNumeroActa($data['id_comite'], $anio);

        $data['numero_acta'] = $numeros['numero_acta'];
        $data['consecutivo_anual'] = $numeros['consecutivo_anual'];
        $data['anio'] = $numeros['anio'];
        $data['estado'] = 'borrador';

        // Calcular quórum requerido
        $miembroModel = new MiembroComiteModel();
        $data['quorum_requerido'] = $miembroModel->calcularQuorumRequerido($data['id_comite']);

        // Convertir arrays a JSON manualmente
        if (isset($data['orden_del_dia']) && is_array($data['orden_del_dia'])) {
            $data['orden_del_dia'] = json_encode($data['orden_del_dia'], JSON_UNESCAPED_UNICODE);
        } else {
            $data['orden_del_dia'] = '[]';
        }

        if (isset($data['desarrollo']) && is_array($data['desarrollo'])) {
            $data['desarrollo'] = json_encode($data['desarrollo'], JSON_UNESCAPED_UNICODE);
        } else {
            $data['desarrollo'] = '[]';
        }

        return $this->insert($data);
    }

    /**
     * Cerrar acta y enviar a firmas
     */
    public function cerrarYEnviarAFirmas(int $idActa, int $cerradaPor): bool
    {
        $acta = $this->find($idActa);
        if (!$acta || $acta['estado'] !== 'borrador' && $acta['estado'] !== 'en_edicion') {
            return false;
        }

        // Contar asistentes que deben firmar
        $asistentesModel = new ActaAsistenteModel();
        $totalFirmantes = $asistentesModel->contarQuienesDebenFirmar($idActa);

        if ($totalFirmantes === 0) {
            return false;
        }

        // Actualizar acta
        $this->update($idActa, [
            'estado' => 'pendiente_firma',
            'total_firmantes' => $totalFirmantes,
            'firmantes_completados' => 0,
            'fecha_cierre' => date('Y-m-d H:i:s'),
            'cerrada_por' => $cerradaPor
        ]);

        // Generar tokens de firma para cada asistente
        $asistentesModel->generarTokensFirma($idActa);

        return true;
    }

    /**
     * Verificar y completar firma de acta
     */
    public function verificarFirmasCompletas(int $idActa): bool
    {
        $acta = $this->find($idActa);
        if (!$acta) {
            return false;
        }

        $asistentesModel = new ActaAsistenteModel();
        $totalFirmantes = $asistentesModel->contarQuienesDebenFirmar($idActa);
        $firmados = $asistentesModel->contarFirmados($idActa);

        $this->update($idActa, [
            'firmantes_completados' => $firmados
        ]);

        // Si todos firmaron, marcar como firmada
        if ($firmados >= $totalFirmantes && $totalFirmantes > 0) {
            $codigoVerificacion = $this->generarCodigoVerificacion($idActa);

            $this->update($idActa, [
                'estado' => 'firmada',
                'codigo_verificacion' => $codigoVerificacion
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generar código de verificación único
     */
    public function generarCodigoVerificacion(int $idActa): string
    {
        $acta = $this->find($idActa);
        $hash = hash('sha256', $idActa . '|' . $acta['numero_acta'] . '|' . time() . '|' . random_bytes(16));
        return strtoupper(substr($hash, 0, 12));
    }

    /**
     * Obtener actas pendientes de firma
     */
    public function getPendientesFirma(int $idCliente): array
    {
        return $this->select('tbl_actas.*, tbl_tipos_comite.codigo as tipo_comite')
                    ->join('tbl_comites', 'tbl_comites.id_comite = tbl_actas.id_comite')
                    ->join('tbl_tipos_comite', 'tbl_tipos_comite.id_tipo = tbl_comites.id_tipo')
                    ->where('tbl_actas.id_cliente', $idCliente)
                    ->where('tbl_actas.estado', 'pendiente_firma')
                    ->orderBy('tbl_actas.fecha_cierre', 'ASC')
                    ->findAll();
    }

    /**
     * Verificar si existe acta del mes para un comité
     */
    public function existeActaDelMes(int $idComite, int $anio, int $mes): bool
    {
        return $this->where('id_comite', $idComite)
                    ->where('anio', $anio)
                    ->where('MONTH(fecha_reunion)', $mes)
                    ->countAllResults() > 0;
    }

    /**
     * Obtener comités sin acta del mes actual
     */
    public function getComitesSinActaMes(int $idCliente): array
    {
        $anio = (int) date('Y');
        $mes = (int) date('m');

        $db = \Config\Database::connect();

        return $db->query("
            SELECT c.*, tc.codigo, tc.nombre as tipo_nombre, tc.dia_limite_mes
            FROM tbl_comites c
            JOIN tbl_tipos_comite tc ON tc.id_tipo = c.id_tipo
            WHERE c.id_cliente = ?
              AND c.estado = 'activo'
              AND tc.periodicidad_dias IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM tbl_actas a
                  WHERE a.id_comite = c.id_comite
                    AND a.anio = ?
                    AND MONTH(a.fecha_reunion) = ?
              )
        ", [$idCliente, $anio, $mes])->getResultArray();
    }

    /**
     * Obtener estadísticas de actas por comité
     */
    public function getEstadisticas(int $idComite, int $anio): array
    {
        $actas = $this->getByComite($idComite, $anio);

        $stats = [
            'total' => count($actas),
            'firmadas' => 0,
            'pendientes_firma' => 0,
            'borrador' => 0,
            'meses_con_acta' => [],
        ];

        foreach ($actas as $acta) {
            switch ($acta['estado']) {
                case 'firmada':
                    $stats['firmadas']++;
                    break;
                case 'pendiente_firma':
                    $stats['pendientes_firma']++;
                    break;
                default:
                    $stats['borrador']++;
            }

            $mes = (int) date('m', strtotime($acta['fecha_reunion']));
            $stats['meses_con_acta'][] = $mes;
        }

        $stats['meses_con_acta'] = array_unique($stats['meses_con_acta']);
        $stats['cumplimiento'] = $anio == date('Y')
            ? round(count($stats['meses_con_acta']) / date('n') * 100, 1)
            : round(count($stats['meses_con_acta']) / 12 * 100, 1);

        return $stats;
    }
}
