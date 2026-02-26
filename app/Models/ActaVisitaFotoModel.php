<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVisitaFotoModel extends Model
{
    protected $table = 'tbl_acta_visita_fotos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_acta_visita', 'ruta_archivo', 'tipo', 'descripcion', 'created_at',
    ];
    protected $useTimestamps = false;

    /**
     * Fotos de un acta, por tipo
     */
    public function getByActa(int $idActa, ?string $tipo = null)
    {
        $builder = $this->where('id_acta_visita', $idActa)
            ->orderBy('created_at', 'ASC');

        if ($tipo) {
            $builder->where('tipo', $tipo);
        }

        return $builder->findAll();
    }
}
