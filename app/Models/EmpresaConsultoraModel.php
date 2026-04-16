<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaConsultoraModel extends Model
{
    protected $table            = 'tbl_empresa_consultora';
    protected $primaryKey       = 'id_empresa_consultora';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'razon_social',
        'nit',
        'direccion',
        'telefono',
        'correo',
        'logo',
        'color_primario',
        'estado',
        'fecha_inicio_contrato',
        'plan',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'razon_social' => 'required|min_length[3]|max_length[200]',
        'estado'       => 'required|in_list[activo,inactivo,suspendido]',
    ];

    public function getActivas(): array
    {
        return $this->where('estado', 'activo')->findAll();
    }
}
