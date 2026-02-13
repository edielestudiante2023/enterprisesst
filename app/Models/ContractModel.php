<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table = 'tbl_contratos';
    protected $primaryKey = 'id_contrato';
    protected $allowedFields = [
        'id_cliente',
        'numero_contrato',
        'fecha_inicio',
        'fecha_fin',
        'valor_contrato',
        'tipo_contrato',
        'estado',
        'observaciones',
        'clausula_cuarta_duracion',
        'clausula_primera_objeto',
        'created_at',
        'updated_at',
        // Campos para generación de contratos
        'nombre_rep_legal_cliente',
        'cedula_rep_legal_cliente',
        'direccion_cliente',
        'telefono_cliente',
        'email_cliente',
        'nombre_rep_legal_contratista',
        'cedula_rep_legal_contratista',
        'email_contratista',
        'id_consultor_responsable',
        'nombre_responsable_sgsst',
        'cedula_responsable_sgsst',
        'licencia_responsable_sgsst',
        'email_responsable_sgsst',
        'valor_mensual',
        'numero_cuotas',
        'frecuencia_visitas',
        'cuenta_bancaria',
        'banco',
        'tipo_cuenta',
        'contrato_generado',
        'fecha_generacion_contrato',
        'ruta_pdf_contrato',
        'contrato_enviado',
        'fecha_envio_contrato',
        'email_envio_contrato'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'id_cliente' => 'required|integer',
        'fecha_inicio' => 'required|valid_date',
        'fecha_fin' => 'required|valid_date',
        'tipo_contrato' => 'required|in_list[inicial,renovacion,ampliacion]',
        'estado' => 'required|in_list[activo,vencido,cancelado]'
    ];

    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El cliente es obligatorio',
            'integer' => 'El ID del cliente debe ser un número'
        ],
        'fecha_inicio' => [
            'required' => 'La fecha de inicio es obligatoria',
            'valid_date' => 'La fecha de inicio no es válida'
        ],
        'fecha_fin' => [
            'required' => 'La fecha de fin es obligatoria',
            'valid_date' => 'La fecha de fin no es válida'
        ]
    ];

    /**
     * Obtiene el contrato activo de un cliente
     */
    public function getActiveContract($idCliente)
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('estado', 'activo')
                    ->orderBy('fecha_fin', 'DESC')
                    ->first();
    }

    /**
     * Obtiene todos los contratos de un cliente (historial completo)
     */
    public function getClientContracts($idCliente, $orderBy = 'fecha_inicio', $orderDir = 'DESC')
    {
        return $this->where('id_cliente', $idCliente)
                    ->orderBy($orderBy, $orderDir)
                    ->findAll();
    }

    /**
     * Cuenta el número de renovaciones de un cliente
     */
    public function countRenewals($idCliente)
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('tipo_contrato', 'renovacion')
                    ->countAllResults();
    }

    /**
     * Obtiene la fecha del primer contrato de un cliente
     */
    public function getFirstContractDate($idCliente)
    {
        $firstContract = $this->where('id_cliente', $idCliente)
                              ->orderBy('fecha_inicio', 'ASC')
                              ->first();

        return $firstContract ? $firstContract['fecha_inicio'] : null;
    }

    /**
     * Obtiene contratos próximos a vencer (30 días o menos)
     */
    public function getExpiringContracts($days = 30)
    {
        $date = date('Y-m-d', strtotime("+{$days} days"));

        return $this->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.correo_cliente')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                    ->where('tbl_contratos.estado', 'activo')
                    ->where('tbl_contratos.fecha_fin <=', $date)
                    ->where('tbl_contratos.fecha_fin >=', date('Y-m-d'))
                    ->orderBy('tbl_contratos.fecha_fin', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene contratos vencidos que aún están marcados como activos
     */
    public function getExpiredActiveContracts()
    {
        return $this->select('tbl_contratos.*, tbl_clientes.nombre_cliente')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                    ->where('tbl_contratos.estado', 'activo')
                    ->where('tbl_contratos.fecha_fin <', date('Y-m-d'))
                    ->findAll();
    }

    /**
     * Actualiza el estado de contratos vencidos
     */
    public function updateExpiredContracts()
    {
        return $this->where('estado', 'activo')
                    ->where('fecha_fin <', date('Y-m-d'))
                    ->set(['estado' => 'vencido'])
                    ->update();
    }

    /**
     * Genera el siguiente número de contrato para un cliente
     */
    public function generateContractNumber($idCliente)
    {
        $count = $this->where('id_cliente', $idCliente)->countAllResults();
        $consecutive = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return 'CONT-' . str_pad($idCliente, 6, '0', STR_PAD_LEFT) . '-' . $consecutive;
    }

    /**
     * Calcula la antigüedad del cliente en meses
     */
    public function getClientAntiquity($idCliente)
    {
        $firstDate = $this->getFirstContractDate($idCliente);

        if (!$firstDate) {
            return 0;
        }

        $start = new \DateTime($firstDate);
        $end = new \DateTime();
        $interval = $start->diff($end);

        return ($interval->y * 12) + $interval->m;
    }

    /**
     * Obtiene estadísticas de contratos por consultor
     */
    public function getContractStatsByConsultant($idConsultor = null)
    {
        $builder = $this->db->table('tbl_contratos ct')
                            ->select("c.id_consultor, con.nombre_consultor,
                                     COUNT(ct.id_contrato) as total_contratos,
                                     SUM(CASE WHEN ct.estado = 'activo' THEN 1 ELSE 0 END) as contratos_activos,
                                     SUM(CASE WHEN ct.tipo_contrato = 'renovacion' THEN 1 ELSE 0 END) as renovaciones")
                            ->join('tbl_clientes c', 'c.id_cliente = ct.id_cliente')
                            ->join('tbl_consultores con', 'con.id_consultor = c.id_consultor')
                            ->groupBy('c.id_consultor, con.nombre_consultor');

        if ($idConsultor) {
            $builder->where('c.id_consultor', $idConsultor);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Obtiene el valor total de contratos activos
     */
    public function getTotalActiveContractsValue($idConsultor = null)
    {
        $builder = $this->select('SUM(valor_contrato) as total_valor')
                        ->where('estado', 'activo');

        if ($idConsultor) {
            $builder->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                    ->where('tbl_clientes.id_consultor', $idConsultor);
        }

        $result = $builder->first();
        return $result ? (float)$result['total_valor'] : 0;
    }
}
