<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVisitaTemaModel extends Model
{
    protected $table = 'tbl_acta_visita_temas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_acta_visita', 'descripcion', 'orden',
    ];
    protected $useTimestamps = false;

    /**
     * Temas de un acta, ordenados
     */
    public function getByActa(int $idActa)
    {
        return $this->where('id_acta_visita', $idActa)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    /**
     * Reemplaza todos los temas de un acta
     */
    public function replaceForActa(int $idActa, array $temas)
    {
        $this->where('id_acta_visita', $idActa)->delete();

        foreach ($temas as $i => $descripcion) {
            $this->insert([
                'id_acta_visita' => $idActa,
                'descripcion'    => $descripcion,
                'orden'          => $i + 1,
            ]);
        }
    }
}
