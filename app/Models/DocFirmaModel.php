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
        $db->transStart();

        // Actualizar solicitud
        $this->update($idSolicitud, [
            'estado' => 'firmado',
            'fecha_firma' => date('Y-m-d H:i:s')
        ]);

        // Registrar evidencia
        $db->table('tbl_doc_firma_evidencias')->insert([
            'id_solicitud' => $idSolicitud,
            'ip_address' => $evidencia['ip_address'],
            'user_agent' => $evidencia['user_agent'],
            'fecha_hora_utc' => gmdate('Y-m-d H:i:s'),
            'geolocalizacion' => $evidencia['geolocalizacion'] ?? null,
            'tipo_firma' => $evidencia['tipo_firma'],
            'firma_imagen' => $evidencia['firma_imagen'],
            'hash_documento' => $evidencia['hash_documento'],
            'aceptacion_terminos' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Registrar en audit log
        $this->registrarAudit($idSolicitud, 'firma_completada', [
            'ip' => $evidencia['ip_address'],
            'tipo' => $evidencia['tipo_firma']
        ]);

        $db->transComplete();
        return $db->transStatus();
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
     * Genera código de verificación único para un documento firmado
     */
    public function generarCodigoVerificacion(int $idDocumento): string
    {
        $solicitudes = $this->where('id_documento', $idDocumento)
                           ->where('estado', 'firmado')
                           ->findAll();

        if (empty($solicitudes)) {
            return '';
        }

        // Combinar tokens de todas las firmas para crear un código único
        $tokens = array_column($solicitudes, 'token');
        $hash = hash('sha256', implode('|', $tokens) . '|' . $idDocumento);

        return strtoupper(substr($hash, 0, 12));
    }
}
