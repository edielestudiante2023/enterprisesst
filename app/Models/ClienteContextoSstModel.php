<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteContextoSstModel extends Model
{
    protected $table = 'tbl_cliente_contexto_sst';
    protected $primaryKey = 'id_contexto';
    protected $allowedFields = [
        'id_cliente', 'actividad_economica_principal', 'codigo_ciiu_principal',
        'actividad_economica_secundaria', 'codigo_ciiu_secundario', 'sector_economico',
        'nivel_riesgo_arl', 'niveles_riesgo_arl', 'arl_actual',
        'total_trabajadores', 'trabajadores_directos', 'trabajadores_temporales',
        'contratistas_permanentes', 'numero_sedes', 'turnos_trabajo',
        'id_consultor_responsable',
        'tiene_copasst', 'tiene_vigia_sst', 'tiene_comite_convivencia', 'tiene_brigada_emergencias',
        'peligros_identificados', 'observaciones_contexto', 'estandares_aplicables',
        // Campos de firmantes
        'requiere_delegado_sst',
        'delegado_sst_nombre', 'delegado_sst_cargo', 'delegado_sst_email', 'delegado_sst_cedula',
        'representante_legal_nombre', 'representante_legal_cargo', 'representante_legal_email', 'representante_legal_cedula'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtiene el contexto SST de un cliente
     */
    public function getByCliente(int $idCliente): ?array
    {
        return $this->where('id_cliente', $idCliente)->first();
    }

    /**
     * Crea o actualiza el contexto SST de un cliente
     */
    public function saveContexto(int $idCliente, array $datos): bool
    {
        $existente = $this->getByCliente($idCliente);
        $datos['id_cliente'] = $idCliente;

        if ($existente) {
            return $this->update($existente['id_contexto'], $datos);
        } else {
            return $this->insert($datos) !== false;
        }
    }

    /**
     * Calcula y actualiza el nivel de estándares aplicables
     */
    public function calcularNivelEstandares(int $idCliente): int
    {
        $contexto = $this->getByCliente($idCliente);

        if (!$contexto) {
            return 60; // Por defecto el más alto
        }

        $trabajadores = (int) $contexto['total_trabajadores'];
        $riesgo = $contexto['nivel_riesgo_arl'];

        // Lógica según Resolución 0312/2019
        if ($trabajadores <= 10 && in_array($riesgo, ['I', 'II', 'III'])) {
            $nivel = 7;
        } elseif ($trabajadores >= 11 && $trabajadores <= 50 && in_array($riesgo, ['I', 'II', 'III'])) {
            $nivel = 21;
        } else {
            $nivel = 60;
        }

        // Actualizar en BD
        $this->where('id_cliente', $idCliente)
             ->set(['estandares_aplicables' => $nivel])
             ->update();

        return $nivel;
    }

    /**
     * Verifica si hay cambio de nivel al actualizar datos
     */
    public function detectarCambioNivel(int $idCliente, int $nuevoTrabajadores, string $nuevoRiesgo): array
    {
        $contexto = $this->getByCliente($idCliente);
        $nivelAnterior = $contexto['estandares_aplicables'] ?? 60;

        // Calcular nuevo nivel
        if ($nuevoTrabajadores <= 10 && in_array($nuevoRiesgo, ['I', 'II', 'III'])) {
            $nivelNuevo = 7;
        } elseif ($nuevoTrabajadores >= 11 && $nuevoTrabajadores <= 50 && in_array($nuevoRiesgo, ['I', 'II', 'III'])) {
            $nivelNuevo = 21;
        } else {
            $nivelNuevo = 60;
        }

        return [
            'cambio_detectado' => $nivelAnterior != $nivelNuevo,
            'nivel_anterior' => $nivelAnterior,
            'nivel_nuevo' => $nivelNuevo,
            'estandares_nuevos' => $nivelNuevo - $nivelAnterior
        ];
    }

    /**
     * Obtiene clientes por nivel de estándares
     */
    public function getClientesPorNivel(int $nivel): array
    {
        return $this->select('tbl_cliente_contexto_sst.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_cliente_contexto_sst.id_cliente')
                    ->where('estandares_aplicables', $nivel)
                    ->findAll();
    }

    /**
     * Obtiene peligros identificados como array
     */
    public function getPeligrosArray(int $idCliente): array
    {
        $contexto = $this->getByCliente($idCliente);

        if (!$contexto || empty($contexto['peligros_identificados'])) {
            return [];
        }

        $peligros = json_decode($contexto['peligros_identificados'], true);
        return is_array($peligros) ? $peligros : [];
    }

    /**
     * Guarda peligros identificados
     */
    public function savePeligros(int $idCliente, array $peligros): bool
    {
        return $this->where('id_cliente', $idCliente)
                    ->set(['peligros_identificados' => json_encode($peligros)])
                    ->update();
    }

    /**
     * Registra una transicion de nivel detectada
     */
    public function registrarTransicion(int $idCliente, int $nivelAnterior, int $nivelNuevo, string $motivo): bool
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_cliente_transiciones')->insert([
            'id_cliente' => $idCliente,
            'nivel_anterior' => $nivelAnterior,
            'nivel_nuevo' => $nivelNuevo,
            'motivo' => $motivo,
            'fecha_deteccion' => date('Y-m-d H:i:s'),
            'estado' => 'detectado'
        ]);
    }

    /**
     * Obtiene el texto descriptivo del nivel
     */
    public function getDescripcionNivel(int $nivel): string
    {
        return match($nivel) {
            7 => 'Basico (7 estandares) - Hasta 10 trabajadores, Riesgo I-III',
            21 => 'Intermedio (21 estandares) - 11-50 trabajadores, Riesgo I-III',
            60 => 'Completo (60 estandares) - Mas de 50 trabajadores o Riesgo IV-V',
            default => 'Nivel desconocido'
        };
    }

    /**
     * Valida si un cambio de contexto genera transicion y la registra automaticamente
     */
    public function procesarCambioContexto(int $idCliente, int $nuevoTrabajadores, string $nuevoRiesgo): array
    {
        $cambio = $this->detectarCambioNivel($idCliente, $nuevoTrabajadores, $nuevoRiesgo);

        if ($cambio['cambio_detectado']) {
            // Determinar motivo
            $contextoActual = $this->getByCliente($idCliente);
            $motivoParts = [];

            if (($contextoActual['total_trabajadores'] ?? 0) != $nuevoTrabajadores) {
                $motivoParts[] = "Cambio de {$contextoActual['total_trabajadores']} a {$nuevoTrabajadores} trabajadores";
            }

            if (($contextoActual['nivel_riesgo_arl'] ?? '') != $nuevoRiesgo) {
                $motivoParts[] = "Cambio de riesgo {$contextoActual['nivel_riesgo_arl']} a {$nuevoRiesgo}";
            }

            $motivo = implode('. ', $motivoParts);

            // Registrar la transicion
            $this->registrarTransicion(
                $idCliente,
                $cambio['nivel_anterior'],
                $cambio['nivel_nuevo'],
                $motivo
            );

            $cambio['transicion_registrada'] = true;
            $cambio['motivo'] = $motivo;
        }

        return $cambio;
    }
}
