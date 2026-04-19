<?php

namespace App\Models;

use CodeIgniter\Model;
class CompetenciaNivelClienteModel extends Model
{
    protected $table = 'tbl_competencia_nivel_cliente';
    protected $primaryKey = 'id_competencia_nivel';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_competencia',
        'nivel_numero',
        'titulo_corto',
        'descripcion_conducta',
    ];

    public function porCompetencia(int $idCompetencia): array
    {
        return $this->where('id_competencia', $idCompetencia)
            ->orderBy('nivel_numero', 'ASC')
            ->findAll();
    }

    /**
     * Devuelve todos los niveles de todas las competencias del cliente,
     * indexados por id_competencia.
     */
    public function porCliente(int $idCliente): array
    {
        $rows = $this->db->table($this->table . ' n')
            ->select('n.*')
            ->join('tbl_competencia_cliente c', 'c.id_competencia = n.id_competencia', 'inner')
            ->where('c.id_cliente', $idCliente)
            ->orderBy('n.id_competencia', 'ASC')
            ->orderBy('n.nivel_numero', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['id_competencia']][] = $r;
        }
        return $out;
    }
}
