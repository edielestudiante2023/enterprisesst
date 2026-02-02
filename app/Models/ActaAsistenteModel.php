<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaAsistenteModel extends Model
{
    protected $table = 'tbl_acta_asistentes';
    protected $primaryKey = 'id_asistente';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_acta',
        'id_miembro',
        'nombre_completo',
        'numero_documento',
        'cargo',
        'email',
        'tipo_asistente',
        'justificacion_ausencia',
        'asistio',
        'orden_firma',
        'token_firma',
        'token_expira',
        'estado_firma',
        'firma_imagen',
        'firma_ip',
        'firma_user_agent',
        'firma_fecha',
        'firma_observacion',
        'notificacion_enviada_at',
        'recordatorio_enviado_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Obtener asistentes de un acta con información del miembro
     */
    public function getByActa(int $idActa): array
    {
        return $this->select('tbl_acta_asistentes.*,
                              m.tipo_miembro, m.rol_comite, m.representacion')
                    ->join('tbl_comite_miembros m', 'm.id_miembro = tbl_acta_asistentes.id_miembro', 'left')
                    ->where('tbl_acta_asistentes.id_acta', $idActa)
                    ->orderBy('tbl_acta_asistentes.orden_firma', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener solo los que asistieron
     */
    public function getPresentes(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
                    ->where('asistio', 1)
                    ->orderBy('orden_firma', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener ausentes
     */
    public function getAusentes(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
                    ->where('asistio', 0)
                    ->findAll();
    }

    /**
     * Contar quienes deben firmar (los que asistieron)
     */
    public function contarQuienesDebenFirmar(int $idActa): int
    {
        return $this->where('id_acta', $idActa)
                    ->where('asistio', 1)
                    ->countAllResults();
    }

    /**
     * Contar firmados
     */
    public function contarFirmados(int $idActa): int
    {
        return $this->where('id_acta', $idActa)
                    ->where('estado_firma', 'firmado')
                    ->countAllResults();
    }

    /**
     * Obtener pendientes de firma
     */
    public function getPendientesFirma(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
                    ->where('asistio', 1)
                    ->where('estado_firma', 'pendiente')
                    ->findAll();
    }

    /**
     * Generar tokens de firma para todos los asistentes
     */
    public function generarTokensFirma(int $idActa): int
    {
        $asistentes = $this->getPresentes($idActa);
        $generados = 0;

        foreach ($asistentes as $asistente) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+7 days'));

            $this->update($asistente['id_asistente'], [
                'token_firma' => $token,
                'token_expira' => $expira,
                'estado_firma' => 'pendiente'
            ]);

            $generados++;
        }

        return $generados;
    }

    /**
     * Obtener asistente por token de firma
     */
    public function getByToken(string $token): ?array
    {
        $asistente = $this->where('token_firma', $token)->first();

        if (!$asistente) {
            return null;
        }

        // Verificar si el token ha expirado
        if ($asistente['token_expira'] && $asistente['token_expira'] < date('Y-m-d H:i:s')) {
            return null;
        }

        return $asistente;
    }

    /**
     * Registrar firma
     */
    public function registrarFirma(int $idAsistente, string $firmaImagen, ?string $observacion = null): bool
    {
        $request = \Config\Services::request();

        $resultado = $this->update($idAsistente, [
            'estado_firma' => 'firmado',
            'firma_imagen' => $firmaImagen,
            'firma_ip' => $request->getIPAddress(),
            'firma_user_agent' => $request->getUserAgent()->getAgentString(),
            'firma_fecha' => date('Y-m-d H:i:s'),
            'firma_observacion' => $observacion
        ]);

        if ($resultado) {
            // Verificar si se completaron todas las firmas
            $asistente = $this->find($idAsistente);
            $actaModel = new ActaModel();
            $actaModel->verificarFirmasCompletas($asistente['id_acta']);
        }

        return $resultado;
    }

    /**
     * Agregar asistentes desde miembros del comité
     */
    public function agregarDesdeMiembros(int $idActa, int $idComite): int
    {
        $miembroModel = new MiembroComiteModel();
        $miembros = $miembroModel->getActivosPorComite($idComite);

        $orden = 1;
        $agregados = 0;

        foreach ($miembros as $miembro) {
            $this->insert([
                'id_acta' => $idActa,
                'id_miembro' => $miembro['id_miembro'],
                'nombre_completo' => $miembro['nombre_completo'],
                'numero_documento' => $miembro['numero_documento'],
                'cargo' => $miembro['cargo'],
                'email' => $miembro['email'],
                'tipo_asistente' => 'miembro',
                'asistio' => 1, // Por defecto asistieron, se marca ausente manualmente
                'orden_firma' => $orden,
                'estado_firma' => 'pendiente'
            ]);

            $orden++;
            $agregados++;
        }

        return $agregados;
    }

    /**
     * Agregar invitado externo
     */
    public function agregarInvitado(int $idActa, array $datos): int|false
    {
        // Obtener último orden de firma
        $ultimo = $this->where('id_acta', $idActa)
                       ->orderBy('orden_firma', 'DESC')
                       ->first();

        $orden = ($ultimo['orden_firma'] ?? 0) + 1;

        return $this->insert([
            'id_acta' => $idActa,
            'id_miembro' => null,
            'nombre_completo' => $datos['nombre_completo'],
            'numero_documento' => $datos['numero_documento'] ?? null,
            'cargo' => $datos['cargo'] ?? null,
            'email' => $datos['email'] ?? null,
            'tipo_asistente' => 'invitado',
            'asistio' => 1,
            'orden_firma' => $orden,
            'estado_firma' => 'pendiente'
        ]);
    }

    /**
     * Marcar como ausente
     */
    public function marcarAusente(int $idAsistente, ?string $justificacion = null, bool $justificado = false): bool
    {
        return $this->update($idAsistente, [
            'asistio' => 0,
            'tipo_asistente' => $justificado ? 'ausente_justificado' : 'ausente',
            'justificacion_ausencia' => $justificacion,
            'estado_firma' => 'no_requerida'
        ]);
    }

    /**
     * Marcar como presente
     */
    public function marcarPresente(int $idAsistente): bool
    {
        return $this->update($idAsistente, [
            'asistio' => 1,
            'tipo_asistente' => 'miembro',
            'justificacion_ausencia' => null,
            'estado_firma' => 'pendiente'
        ]);
    }

    /**
     * Obtener asistentes que no han firmado después de X horas
     */
    public function getPendientesRecordatorio(int $horasDesdeEnvio = 48): array
    {
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$horasDesdeEnvio} hours"));

        return $this->select('tbl_acta_asistentes.*, tbl_actas.numero_acta, tbl_actas.fecha_reunion')
                    ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_asistentes.id_acta')
                    ->where('tbl_acta_asistentes.asistio', 1)
                    ->where('tbl_acta_asistentes.estado_firma', 'pendiente')
                    ->where('tbl_acta_asistentes.notificacion_enviada_at IS NOT NULL')
                    ->where('tbl_acta_asistentes.notificacion_enviada_at <', $fechaLimite)
                    ->where('(tbl_acta_asistentes.recordatorio_enviado_at IS NULL OR tbl_acta_asistentes.recordatorio_enviado_at < tbl_acta_asistentes.notificacion_enviada_at)')
                    ->where('tbl_actas.estado', 'pendiente_firma')
                    ->findAll();
    }

    /**
     * Calcular quórum presente
     */
    public function calcularQuorumPresente(int $idActa): int
    {
        return $this->where('id_acta', $idActa)
                    ->where('asistio', 1)
                    ->whereIn('tipo_asistente', ['miembro'])
                    ->countAllResults();
    }

    /**
     * Verificar si hay quórum
     */
    public function hayQuorum(int $idActa): bool
    {
        $acta = (new ActaModel())->find($idActa);
        if (!$acta) {
            return false;
        }

        $presente = $this->calcularQuorumPresente($idActa);
        return $presente >= $acta['quorum_requerido'];
    }
}
