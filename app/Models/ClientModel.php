<?php
namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'tbl_clientes';
    protected $primaryKey = 'id_cliente';
    protected $allowedFields = [
        'datetime', 'fecha_ingreso', 'nit_cliente', 'nombre_cliente', 'usuario',
        'password', 'correo_cliente', 'telefono_1_cliente', 'telefono_2_cliente',
        'direccion_cliente', 'persona_contacto_compras', 'codigo_actividad_economica',
        'nombre_rep_legal', 'cedula_rep_legal', 'fecha_fin_contrato', 'ciudad_cliente',
        'estado', 'id_consultor', 'logo', 'firma_representante_legal', 'estandares'
    ];

    /**
     * Obtiene un cliente con su contrato activo
     */
    public function getClientWithActiveContract($idCliente)
    {
        return $this->select('tbl_clientes.*,
                             tbl_contratos.id_contrato,
                             tbl_contratos.numero_contrato,
                             tbl_contratos.fecha_inicio as contrato_inicio,
                             tbl_contratos.fecha_fin as contrato_fin,
                             tbl_contratos.valor_contrato,
                             tbl_contratos.tipo_contrato,
                             tbl_contratos.estado as estado_contrato')
                    ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'", 'left')
                    ->where('tbl_clientes.id_cliente', $idCliente)
                    ->first();
    }

    /**
     * Obtiene clientes con contratos próximos a vencer
     */
    public function getClientsWithExpiringContracts($days = 30, $idConsultor = null)
    {
        $date = date('Y-m-d', strtotime("+{$days} days"));

        $builder = $this->select('tbl_clientes.*,
                                 tbl_contratos.id_contrato,
                                 tbl_contratos.numero_contrato,
                                 tbl_contratos.fecha_fin as contrato_fin,
                                 DATEDIFF(tbl_contratos.fecha_fin, CURDATE()) as dias_restantes')
                        ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'")
                        ->where('tbl_contratos.fecha_fin <=', $date)
                        ->where('tbl_contratos.fecha_fin >=', date('Y-m-d'))
                        ->orderBy('tbl_contratos.fecha_fin', 'ASC');

        if ($idConsultor) {
            $builder->where('tbl_clientes.id_consultor', $idConsultor);
        }

        return $builder->findAll();
    }

    /**
     * Obtiene el número total de contratos de un cliente
     */
    public function getClientTotalContracts($idCliente)
    {
        return $this->db->table('tbl_contratos')
                       ->where('id_cliente', $idCliente)
                       ->countAllResults();
    }

    /**
     * Obtiene el número de renovaciones de un cliente
     */
    public function getClientRenewalsCount($idCliente)
    {
        return $this->db->table('tbl_contratos')
                       ->where('id_cliente', $idCliente)
                       ->where('tipo_contrato', 'renovacion')
                       ->countAllResults();
    }

    /**
     * Obtiene clientes con estadísticas de contratos
     */
    public function getClientsWithContractStats($idConsultor = null)
    {
        $builder = $this->db->table('tbl_clientes c')
                           ->select("c.*,
                                    COUNT(ct.id_contrato) as total_contratos,
                                    SUM(CASE WHEN ct.tipo_contrato = 'renovacion' THEN 1 ELSE 0 END) as renovaciones,
                                    MIN(ct.fecha_inicio) as primer_contrato,
                                    MAX(CASE WHEN ct.estado = 'activo' THEN ct.fecha_fin END) as contrato_vigente_hasta")
                           ->join('tbl_contratos ct', 'ct.id_cliente = c.id_cliente', 'left')
                           ->groupBy('c.id_cliente');

        if ($idConsultor) {
            $builder->where('c.id_consultor', $idConsultor);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Verifica si un cliente tiene contrato activo
     */
    public function hasActiveContract($idCliente)
    {
        $count = $this->db->table('tbl_contratos')
                         ->where('id_cliente', $idCliente)
                         ->where('estado', 'activo')
                         ->countAllResults();

        return $count > 0;
    }

}
?>
