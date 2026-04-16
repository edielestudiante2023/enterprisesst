<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class CompetenciaEscalaClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_competencia_escala_cliente';
    protected $primaryKey = 'id_escala';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'nivel',
        'nombre',
        'etiqueta',
        'descripcion',
    ];

    public function porCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('nivel', 'ASC')
            ->findAll();
    }
}
