<?php
namespace App\Models;

use CodeIgniter\Model;

class DocFirmaModel extends Model
{
    protected $table = 'tbl_doc_firma_solicitudes';
    protected $primaryKey = 'id_solicitud';
    protected $allowedFields = [
        'id_documento', 'id_version', 'token', 'estado',
        'fecha_expiracion', 'fecha_firma', 'firmante_tipo',
        'firmante_interno_id', 'firmante_email', 'firmante_nombre',
        'firmante_cargo', 'firmante_documento', 'orden_firma'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Crea solicitud de firma
     */
    public function crearSolicitud(array $datos): int
    {
        // Generar token único
        $datos['token'] = bin2hex(random_bytes(32));

        // Estado por defecto si no viene especificado
        if (!isset($datos['estado'])) {
            $datos['estado'] = 'pendiente';
        }

        $datos['fecha_expiracion'] = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->insert($datos);
        return $this->getInsertID();
    }

    /**
     * Obtiene solicitud por token
     */
    public function getByToken(string $token): ?array
    {
        return $this->select('tbl_doc_firma_solicitudes.*,
                             tbl_documentos_sst.codigo,
                             tbl_documentos_sst.titulo as documento_nombre,
                             tbl_documentos_sst.tipo_documento,
                             tbl_documentos_sst.version,
                             tbl_documentos_sst.estado as documento_estado,
                             tbl_clientes.nombre_cliente,
                             tbl_clientes.id_cliente')
                    ->join('tbl_documentos_sst', 'tbl_documentos_sst.id_documento = tbl_doc_firma_solicitudes.id_documento')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_documentos_sst.id_cliente')
                    ->where('tbl_doc_firma_solicitudes.token', $token)
                    ->first();
    }

    /**
     * Verifica si el token es válido
     */
    public function validarToken(string $token): array
    {
        $solicitud = $this->getByToken($token);

        if (!$solicitud) {
            return ['valido' => false, 'error' => 'Token no encontrado'];
        }

        if ($solicitud['estado'] !== 'pendiente') {
            return ['valido' => false, 'error' => 'Solicitud ya procesada'];
        }

        if (strtotime($solicitud['fecha_expiracion']) < time()) {
            $this->update($solicitud['id_solicitud'], ['estado' => 'expirado']);
            return ['valido' => false, 'error' => 'Token expirado'];
        }

        return ['valido' => true, 'solicitud' => $solicitud];
    }

    /**
     * Registra firma
     */
    public function registrarFirma(int $idSolicitud, array $evidencia): bool
    {
        $db = \Config\Database::connect();

        try {
            log_message('info', "registrarFirma: Iniciando para solicitud {$idSolicitud}");

            // Verificar estado actual de la solicitud
            $solicitudActual = $this->find($idSolicitud);
            if (!$solicitudActual) {
                log_message('error', "registrarFirma: Solicitud {$idSolicitud} no encontrada");
                return false;
            }
            log_message('info', "registrarFirma: Estado actual de solicitud: {$solicitudActual['estado']}");

            // Verificar si ya existe evidencia para esta solicitud
            $evidenciaExistente = $db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $idSolicitud)
                ->countAllResults();

            if ($evidenciaExistente > 0) {
                log_message('warning', "registrarFirma: Ya existe evidencia para solicitud {$idSolicitud}, actualizando estado");
                // Solo actualizar el estado si ya hay evidencia
                $this->update($idSolicitud, [
                    'estado' => 'firmado',
                    'fecha_firma' => date('Y-m-d H:i:s')
                ]);
                return true;
            }

            $db->transStart();

            // Actualizar solicitud usando query builder directo para estar en la misma transacción
            log_message('info', "registrarFirma: Actualizando estado solicitud {$idSolicitud}");
            $updateResult = $db->table('tbl_doc_firma_solicitudes')
                ->where('id_solicitud', $idSolicitud)
                ->update([
                    'estado' => 'firmado',
                    'fecha_firma' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if (!$updateResult) {
                $dbError = $db->error();
                log_message('error', "registrarFirma: Error actualizando solicitud {$idSolicitud}: " . ($dbError['message'] ?? 'Unknown'));
            } else {
                log_message('info', "registrarFirma: Solicitud actualizada correctamente");
            }

            // Registrar evidencia
            log_message('info', "registrarFirma: Insertando evidencia para solicitud {$idSolicitud}");
            $insertResult = $db->table('tbl_doc_firma_evidencias')->insert([
                'id_solicitud' => $idSolicitud,
                'ip_address' => $evidencia['ip_address'],
                'user_agent' => substr($evidencia['user_agent'] ?? '', 0, 500), // Limitar longitud
                'fecha_hora_utc' => gmdate('Y-m-d H:i:s'),
                'geolocalizacion' => $evidencia['geolocalizacion'] ?? null,
                'tipo_firma' => $evidencia['tipo_firma'],
                'firma_imagen' => $evidencia['firma_imagen'],
                'hash_documento' => $evidencia['hash_documento'],
                'aceptacion_terminos' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$insertResult) {
                $dbError = $db->error();
                log_message('error', 'Error inserting firma_evidencia: ' . ($dbError['message'] ?? 'Unknown error') . ' Code: ' . ($dbError['code'] ?? 'N/A'));
            } else {
                log_message('info', "registrarFirma: Evidencia insertada correctamente, ID: " . $db->insertID());
            }

            // Registrar en audit log
            log_message('info', "registrarFirma: Registrando audit log");
            $auditResult = $db->table('tbl_doc_firma_audit_log')->insert([
                'id_solicitud' => $idSolicitud,
                'evento' => 'firma_completada',
                'fecha_hora' => date('Y-m-d H:i:s'),
                'ip_address' => $evidencia['ip_address'],
                'detalles' => json_encode([
                    'ip' => $evidencia['ip_address'],
                    'tipo' => $evidencia['tipo_firma']
                ])
            ]);

            if (!$auditResult) {
                $dbError = $db->error();
                log_message('error', 'Error inserting audit_log: ' . ($dbError['message'] ?? 'Unknown error'));
            }

            $db->transComplete();

            $transStatus = $db->transStatus();
            log_message('info', "registrarFirma: Transacción completada con status: " . ($transStatus ? 'SUCCESS' : 'FAILED'));

            return $transStatus;
        } catch (\Exception $e) {
            log_message('error', 'Exception in registrarFirma: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Registra evento en audit log
     */
    public function registrarAudit(int $idSolicitud, string $evento, array $detalles = []): bool
    {
        return $this->db->table('tbl_doc_firma_audit_log')->insert([
            'id_solicitud' => $idSolicitud,
            'evento' => $evento,
            'fecha_hora' => date('Y-m-d H:i:s'),
            'ip_address' => $detalles['ip'] ?? service('request')->getIPAddress(),
            'detalles' => json_encode($detalles)
        ]);
    }

    /**
     * Obtiene solicitudes de un documento
     */
    public function getByDocumento(int $idDocumento): array
    {
        return $this->where('id_documento', $idDocumento)
                    ->orderBy('orden_firma', 'ASC')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene estado de firmas de un documento
     */
    public function getEstadoFirmas(int $idDocumento): array
    {
        $solicitudes = $this->getByDocumento($idDocumento);

        $estado = [
            'elaboro' => null,
            'reviso' => null,
            'aprobo' => null,
            'completo' => true
        ];

        foreach ($solicitudes as $sol) {
            $estado[$sol['firmante_tipo']] = $sol;
            if ($sol['estado'] !== 'firmado') {
                $estado['completo'] = false;
            }
        }

        return $estado;
    }

    /**
     * Verifica si todas las firmas están completas
     */
    public function firmasCompletas(int $idDocumento): bool
    {
        $pendientes = $this->where('id_documento', $idDocumento)
                           ->whereIn('estado', ['pendiente', 'esperando'])
                           ->countAllResults();

        return $pendientes === 0;
    }

    /**
     * Obtiene el siguiente firmante en la cadena (estado 'esperando')
     */
    public function getSiguienteFirmante(int $idDocumento): ?array
    {
        return $this->where('id_documento', $idDocumento)
                    ->where('estado', 'esperando')
                    ->orderBy('orden_firma', 'ASC')
                    ->first();
    }

    /**
     * Obtiene evidencia de firma
     */
    public function getEvidencia(int $idSolicitud): ?array
    {
        return $this->db->table('tbl_doc_firma_evidencias')
                       ->where('id_solicitud', $idSolicitud)
                       ->get()
                       ->getRowArray();
    }

    /**
     * Obtiene audit log de una solicitud
     */
    public function getAuditLog(int $idSolicitud): array
    {
        return $this->db->table('tbl_doc_firma_audit_log')
                       ->where('id_solicitud', $idSolicitud)
                       ->orderBy('fecha_hora', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Reenvía solicitud de firma (nuevo token)
     */
    public function reenviar(int $idSolicitud): string
    {
        $nuevoToken = bin2hex(random_bytes(32));

        $this->update($idSolicitud, [
            'token' => $nuevoToken,
            'estado' => 'pendiente',
            'fecha_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);

        $this->registrarAudit($idSolicitud, 'token_reenviado');

        return $nuevoToken;
    }

    /**
     * Cancela solicitud de firma
     */
    public function cancelar(int $idSolicitud): bool
    {
        $this->registrarAudit($idSolicitud, 'solicitud_cancelada');

        return $this->update($idSolicitud, ['estado' => 'cancelado']);
    }

    /**
     * Obtiene solicitudes pendientes (para recordatorios)
     */
    public function getPendientesRecordatorio(int $diasAntes = 2): array
    {
        $fecha = date('Y-m-d', strtotime("+{$diasAntes} days"));

        return $this->select('tbl_doc_firma_solicitudes.*,
                             tbl_documentos_sst.titulo as documento_nombre,
                             tbl_documentos_sst.codigo')
                    ->join('tbl_documentos_sst', 'tbl_documentos_sst.id_documento = tbl_doc_firma_solicitudes.id_documento')
                    ->where('tbl_doc_firma_solicitudes.estado', 'pendiente')
                    ->where('DATE(tbl_doc_firma_solicitudes.fecha_expiracion)', $fecha)
                    ->findAll();
    }

    /**
     * Obtiene todas las evidencias de firma para un documento
     */
    public function getEvidenciasPorDocumento(int $idDocumento): array
    {
        return $this->db->table('tbl_doc_firma_evidencias e')
                       ->select('e.*, s.firmante_nombre, s.firmante_tipo, s.firmante_cargo, s.firmante_documento, s.firmante_email, s.fecha_firma')
                       ->join('tbl_doc_firma_solicitudes s', 's.id_solicitud = e.id_solicitud')
                       ->where('s.id_documento', $idDocumento)
                       ->where('s.estado', 'firmado')
                       ->orderBy('s.orden_firma', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Dashboard: Obtiene todos los documentos con solicitudes de firma
     * Agrupados por documento con contadores de estado
     */
    public function getDashboardFirmas(?int $idConsultor = null, ?int $idCliente = null): array
    {
        $builder = $this->db->table('tbl_doc_firma_solicitudes s')
            ->select('
                s.id_documento,
                d.codigo,
                d.titulo,
                d.version,
                d.estado as estado_documento,
                d.tipo_documento,
                c.id_cliente,
                c.nombre_cliente,
                c.nit_cliente,
                COUNT(s.id_solicitud) as total_firmantes,
                SUM(CASE WHEN s.estado = "firmado" THEN 1 ELSE 0 END) as firmados,
                SUM(CASE WHEN s.estado = "pendiente" THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN s.estado = "esperando" THEN 1 ELSE 0 END) as esperando,
                SUM(CASE WHEN s.estado = "expirado" THEN 1 ELSE 0 END) as expirados,
                SUM(CASE WHEN s.estado = "cancelado" THEN 1 ELSE 0 END) as cancelados,
                MAX(s.fecha_firma) as ultima_firma,
                MAX(s.created_at) as fecha_solicitud
            ')
            ->join('tbl_documentos_sst d', 'd.id_documento = s.id_documento')
            ->join('tbl_clientes c', 'c.id_cliente = d.id_cliente')
            ->groupBy('s.id_documento')
            ->orderBy('fecha_solicitud', 'DESC');

        if ($idCliente) {
            $builder->where('c.id_cliente', $idCliente);
        } elseif ($idConsultor) {
            $builder->where('c.id_consultor', $idConsultor);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Genera código de verificación único para un documento firmado
     * IMPORTANTE: Ordenar siempre por id_solicitud para garantizar consistencia
     */
    public function generarCodigoVerificacion(int $idDocumento): string
    {
        $solicitudes = $this->where('id_documento', $idDocumento)
                           ->where('estado', 'firmado')
                           ->orderBy('id_solicitud', 'ASC')
                           ->findAll();

        if (empty($solicitudes)) {
            return '';
        }

        // Combinar tokens de todas las firmas para crear un código único
        // Ordenados por id_solicitud para garantizar consistencia
        $tokens = array_column($solicitudes, 'token');
        $hash = hash('sha256', implode('|', $tokens) . '|' . $idDocumento);

        return strtoupper(substr($hash, 0, 12));
    }
}
