<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class PerfilCargoFuncionTHClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_perfil_cargo_funcion_th_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'orden',
        'texto',
        'activo',
    ];

    public function porCliente(int $idCliente, bool $soloActivas = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivas) $q = $q->where('activo', 1);
        return $q->orderBy('orden', 'ASC')->findAll();
    }

    public function reemplazarTodas(int $idCliente, array $items): void
    {
        $this->where('id_cliente', $idCliente)->delete();
        if (empty($items)) return;

        $rows = [];
        foreach ($items as $i => $it) {
            $texto = is_array($it) ? ($it['texto'] ?? '') : (string)$it;
            if (trim($texto) === '') continue;
            $rows[] = [
                'id_cliente' => $idCliente,
                'orden'      => $i + 1,
                'texto'      => $texto,
                'activo'     => 1,
            ];
        }
        if (!empty($rows)) $this->insertBatch($rows);
    }
}
