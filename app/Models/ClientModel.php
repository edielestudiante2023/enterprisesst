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
        'estado', 'id_consultor', 'logo', 'firma_representante_legal', 'estandares',
        'vendedor', 'persona_contacto_operaciones', 'persona_contacto_pagos',
        'horarios_y_dias', 'frecuencia_servicio', 'plazo_cartera',
        'fecha_cierre_facturacion', 'rut_archivo', 'camara_comercio_archivo',
        'cedula_rep_legal_archivo', 'oferta_comercial_archivo'
    ];

    /**
     * Determina si se debe aplicar el filtro de tenant automaticamente.
     * No aplica en CLI, cuando no hay sesion, o cuando el usuario es superadmin.
     */
    private function shouldApplyTenantFilter(): bool
    {
        if (is_cli()) return false;
        $session = session();
        if (!$session->get('isLoggedIn')) return false;
        if ($session->get('is_superadmin')) return false;
        if (!$session->get('id_empresa_consultora')) return false;
        return true;
    }

    /**
     * Aplica el filtro de tenant al builder interno del modelo.
     */
    private function applyTenantScope(): void
    {
        if ($this->shouldApplyTenantFilter()) {
            $empresaId = (int) session()->get('id_empresa_consultora');
            $this->whereIn('tbl_clientes.id_consultor', function ($sub) use ($empresaId) {
                return $sub->select('id_consultor')
                    ->from('tbl_consultor')
                    ->where('id_empresa_consultora', $empresaId);
            });
        }
    }

    /**
     * Override findAll: filtra por empresa automaticamente.
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->applyTenantScope();
        return parent::findAll($limit, $offset);
    }

    /**
     * Override find: si se busca por ID, verifica que pertenezca a la empresa.
     * Si se busca sin ID (null), aplica scope como findAll.
     */
    public function find($id = null)
    {
        $result = parent::find($id);

        // Si se busco por ID especifico, validar pertenencia a la empresa
        if ($id !== null && $result !== null && $this->shouldApplyTenantFilter()) {
            $row = is_array($result) && isset($result['id_consultor']) ? $result : null;
            if ($row) {
                $empresaId = (int) session()->get('id_empresa_consultora');
                $db = \Config\Database::connect();
                $consultor = $db->table('tbl_consultor')
                    ->where('id_consultor', $row['id_consultor'])
                    ->where('id_empresa_consultora', $empresaId)
                    ->get()->getRowArray();
                if (!$consultor) {
                    return null; // No pertenece a la empresa: como si no existiera
                }
            }
        }

        return $result;
    }

    /**
     * Override where()->findAll() pattern: el scope se aplica en findAll.
     * Override first(): aplica scope tambien.
     */
    public function first()
    {
        $this->applyTenantScope();
        return parent::first();
    }

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
