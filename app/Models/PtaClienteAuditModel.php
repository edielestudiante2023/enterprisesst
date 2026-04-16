<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class PtaClienteAuditModel extends Model
{
    use TenantScopedModel;

    protected $table      = 'tbl_pta_cliente_audit';
    protected $primaryKey = 'id_audit';

    protected $allowedFields = [
        'id_ptacliente',
        'id_cliente',
        'accion',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'id_usuario',
        'nombre_usuario',
        'email_usuario',
        'rol_usuario',
        'ip_address',
        'user_agent',
        'metodo',
        'descripcion',
        'fecha_accion'
    ];

    protected $useTimestamps = false;
}
