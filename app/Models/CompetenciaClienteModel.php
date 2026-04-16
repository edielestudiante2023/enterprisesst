<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class CompetenciaClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_competencia_cliente';
    protected $primaryKey = 'id_competencia';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'numero',
        'codigo',
        'nombre',
        'definicion',
        'pregunta_clave',
        'familia',
        'activo',
    ];

    public function porCliente(int $idCliente, bool $soloActivas = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivas) $q = $q->where('activo', 1);
        return $q->orderBy('numero', 'ASC')->findAll();
    }

    /**
     * Devuelve las competencias del cliente agrupadas por familia.
     * Cada familia contiene su lista ordenada por numero.
     */
    public function porClienteAgrupadas(int $idCliente): array
    {
        $rows = $this->porCliente($idCliente, true);
        $out  = [];
        foreach ($rows as $r) {
            $fam = $r['familia'] ?? 'sin_familia';
            $out[$fam][] = $r;
        }
        return $out;
    }

    public function contarPorCliente(int $idCliente): int
    {
        return $this->where('id_cliente', $idCliente)->countAllResults();
    }
}
