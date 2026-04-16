<?php

namespace App\Models;

use CodeIgniter\Model;

class PerfilCargoIndicadorModel extends Model
{
    protected $table = 'tbl_perfil_cargo_indicador';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_perfil_cargo',
        'objetivo_proceso',
        'nombre_indicador',
        'formula',
        'periodicidad',
        'meta',
        'ponderacion',
        'objetivo_calidad_impacta',
        'generado_ia',
        'orden',
    ];

    public function porPerfil(int $idPerfilCargo): array
    {
        return $this->where('id_perfil_cargo', $idPerfilCargo)
                    ->orderBy('orden', 'ASC')
                    ->findAll();
    }

    public function reemplazarTodos(int $idPerfilCargo, array $indicadores, bool $generadoIa = false): void
    {
        $this->where('id_perfil_cargo', $idPerfilCargo)->delete();
        if (empty($indicadores)) return;

        $rows = [];
        foreach ($indicadores as $i => $ind) {
            $rows[] = [
                'id_perfil_cargo'          => $idPerfilCargo,
                'objetivo_proceso'         => $ind['objetivo_proceso'] ?? null,
                'nombre_indicador'         => $ind['nombre_indicador'] ?? '',
                'formula'                  => $ind['formula'] ?? null,
                'periodicidad'             => $ind['periodicidad'] ?? null,
                'meta'                     => $ind['meta'] ?? null,
                'ponderacion'              => $ind['ponderacion'] ?? null,
                'objetivo_calidad_impacta' => $ind['objetivo_calidad_impacta'] ?? null,
                'generado_ia'              => $generadoIa ? 1 : 0,
                'orden'                    => $ind['orden'] ?? ($i + 1),
            ];
        }
        $this->insertBatch($rows);
    }
}
