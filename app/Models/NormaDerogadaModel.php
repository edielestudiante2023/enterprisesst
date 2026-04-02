<?php

namespace App\Models;

use CodeIgniter\Model;

class NormaDerogadaModel extends Model
{
    protected $table = 'tbl_normas_derogadas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'norma_derogada', 'norma_reemplazo', 'texto_original',
        'reportado_por', 'fecha_reporte', 'activo'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Retorna todas las normas derogadas activas
     */
    public function getActivas(): array
    {
        return $this->where('activo', 1)
                     ->orderBy('fecha_reporte', 'DESC')
                     ->findAll();
    }

    /**
     * Verifica si una norma ya está reportada como derogada
     */
    public function existeNorma(string $normaDerogada): bool
    {
        return $this->where('activo', 1)
                     ->like('norma_derogada', $normaDerogada, 'both')
                     ->countAllResults() > 0;
    }
}
