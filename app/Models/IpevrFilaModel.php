<?php

namespace App\Models;

use CodeIgniter\Model;

class IpevrFilaModel extends Model
{
    protected $table = 'tbl_ipevr_fila';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_matriz',
        'orden',
        'id_proceso','proceso_texto',
        'id_zona','zona_texto',
        'actividad',
        'id_tarea','tarea_texto',
        'rutinaria',
        'cargos_expuestos','num_expuestos',
        'id_peligro_catalogo','descripcion_peligro','id_clasificacion','efectos_posibles',
        'control_fuente','control_medio','control_individuo',
        'id_nd','id_ne','np','id_np','id_nc','nr','id_nivel_riesgo','aceptabilidad',
        'peor_consecuencia','requisito_legal',
        'medida_eliminacion','medida_sustitucion','medida_ingenieria','medida_administrativa','medida_epp',
        'origen_fila','estado_fila',
    ];

    public function porMatriz(int $idMatriz): array
    {
        return $this->where('id_matriz', $idMatriz)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function siguienteOrden(int $idMatriz): int
    {
        $max = $this->where('id_matriz', $idMatriz)->selectMax('orden')->first();
        return (int)($max['orden'] ?? 0) + 1;
    }

    public function contarPorMatriz(int $idMatriz): int
    {
        return $this->where('id_matriz', $idMatriz)->countAllResults();
    }
}
