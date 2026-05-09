<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Talla que cada asistente registra para cada item global de una entrega de dotacion.
 * Combinacion (id_entrega_dotacion_asistente, id_entrega_dotacion_item) es unica.
 */
class EntregaDotacionAsistenteTallaModel extends Model
{
    protected $table = 'tbl_entrega_dotacion_asistente_talla';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_entrega_dotacion_asistente',
        'id_entrega_dotacion_item',
        'talla',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function getByAsistente(int $idAsistente): array
    {
        return $this->where('id_entrega_dotacion_asistente', $idAsistente)->findAll();
    }

    /**
     * Devuelve un mapa [id_item => talla] para un asistente.
     */
    public function getMapByAsistente(int $idAsistente): array
    {
        $rows = $this->getByAsistente($idAsistente);
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['id_entrega_dotacion_item']] = $r['talla'];
        }
        return $map;
    }

    public function deleteByAsistente(int $idAsistente): bool
    {
        return $this->where('id_entrega_dotacion_asistente', $idAsistente)->delete();
    }

    /**
     * Reemplaza todas las tallas de un asistente con las del mapa [id_item => talla].
     */
    public function replaceForAsistente(int $idAsistente, array $tallasPorItem): void
    {
        $this->deleteByAsistente($idAsistente);
        foreach ($tallasPorItem as $idItem => $talla) {
            $idItem = (int)$idItem;
            $tallaTrim = trim((string)$talla);
            if ($idItem <= 0) continue;
            $this->insert([
                'id_entrega_dotacion_asistente' => $idAsistente,
                'id_entrega_dotacion_item'      => $idItem,
                'talla'                         => $tallaTrim !== '' ? $tallaTrim : null,
            ]);
        }
    }
}
