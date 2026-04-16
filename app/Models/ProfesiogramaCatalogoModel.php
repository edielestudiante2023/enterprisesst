<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfesiogramaCatalogoModel extends Model
{
    protected $table = 'tbl_profesiograma_examenes_catalogo';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombre', 'tipo_examen', 'descripcion',
        'clasificaciones_aplica', 'normativa_referencia',
        'aplica_retiro', 'frecuencia_sugerida',
        'activo', 'orden',
    ];

    /**
     * Todos los examenes activos ordenados.
     */
    public function activos(): array
    {
        return $this->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    /**
     * Examenes que aplican para una clasificacion GTC45.
     * Busca en el JSON clasificaciones_aplica.
     */
    public function porClasificacion(string $codigoClasificacion): array
    {
        return $this->where('activo', 1)
            ->where("JSON_CONTAINS(clasificaciones_aplica, '\"$codigoClasificacion\"')", null, false)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    /**
     * Bundle para frontend (select options).
     */
    public function paraSelect(): array
    {
        $examenes = $this->activos();
        $agrupados = [];
        foreach ($examenes as $ex) {
            $tipo = $ex['tipo_examen'];
            $agrupados[$tipo][] = $ex;
        }
        return $agrupados;
    }
}
