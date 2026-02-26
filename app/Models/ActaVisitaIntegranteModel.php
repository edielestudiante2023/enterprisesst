<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVisitaIntegranteModel extends Model
{
    protected $table = 'tbl_acta_visita_integrantes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_acta_visita', 'nombre', 'rol', 'orden',
    ];
    protected $useTimestamps = false;

    /**
     * Integrantes de un acta, ordenados
     */
    public function getByActa(int $idActa)
    {
        return $this->where('id_acta_visita', $idActa)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    /**
     * Reemplaza todos los integrantes de un acta
     */
    public function replaceForActa(int $idActa, array $integrantes)
    {
        $this->where('id_acta_visita', $idActa)->delete();

        foreach ($integrantes as $i => $integrante) {
            $this->insert([
                'id_acta_visita' => $idActa,
                'nombre'         => $integrante['nombre'],
                'rol'            => $integrante['rol'],
                'orden'          => $i + 1,
            ]);
        }
    }
}
