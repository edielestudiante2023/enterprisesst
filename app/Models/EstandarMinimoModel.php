<?php
namespace App\Models;

use CodeIgniter\Model;

class EstandarMinimoModel extends Model
{
    protected $table = 'tbl_estandares_minimos';
    protected $primaryKey = 'id_estandar';
    protected $allowedFields = [
        'ciclo_phva', 'categoria', 'categoria_nombre', 'item', 'nombre',
        'criterio', 'peso_porcentual', 'aplica_7', 'aplica_21', 'aplica_60',
        'documentos_sugeridos', 'activo'
    ];

    protected $returnType = 'array';

    /**
     * Obtiene estándares por ciclo PHVA
     */
    public function getByCicloPHVA(string $ciclo): array
    {
        return $this->where('ciclo_phva', $ciclo)
                    ->orderBy('item', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene estándares por categoría
     */
    public function getByCategoria(string $categoria): array
    {
        return $this->where('categoria', $categoria)
                    ->orderBy('item', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene estándares aplicables según nivel (7, 21 o 60)
     */
    public function getByNivel(int $nivel): array
    {
        $campo = 'aplica_60';
        if ($nivel === 7) {
            $campo = 'aplica_7';
        } elseif ($nivel === 21) {
            $campo = 'aplica_21';
        }

        return $this->where($campo, 1)
                    ->orderBy('item', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene estándares agrupados por ciclo PHVA
     */
    public function getGroupedByPHVA(): array
    {
        $estandares = $this->orderBy('item', 'ASC')->findAll();

        $grouped = [
            'PLANEAR' => [],
            'HACER' => [],
            'VERIFICAR' => [],
            'ACTUAR' => []
        ];

        foreach ($estandares as $estandar) {
            $grouped[$estandar['ciclo_phva']][] = $estandar;
        }

        return $grouped;
    }

    /**
     * Obtiene estándares agrupados por ciclo PHVA y luego por categoría
     * Formato: [ciclo => [categoria => [estandares]]]
     */
    public function getGroupedByCategoria(): array
    {
        $estandares = $this->orderBy('item', 'ASC')->findAll();

        if (empty($estandares)) {
            return [];
        }

        $grouped = [];
        foreach ($estandares as $estandar) {
            $ciclo = $estandar['ciclo_phva'] ?? 'PLANEAR';
            $categoria = $estandar['categoria_nombre'] ?? $estandar['categoria'] ?? 'Sin categoría';

            if (!isset($grouped[$ciclo])) {
                $grouped[$ciclo] = [];
            }
            if (!isset($grouped[$ciclo][$categoria])) {
                $grouped[$ciclo][$categoria] = [];
            }

            // Mapear campos para la vista
            $grouped[$ciclo][$categoria][] = [
                'id_estandar' => $estandar['id_estandar'],
                'numero_estandar' => $estandar['item'],
                'codigo' => $estandar['item'],
                'nombre' => $estandar['nombre'],
                'peso' => $estandar['peso_porcentual'],
                'nivel_minimo' => $estandar['aplica_7'] ? 7 : ($estandar['aplica_21'] ? 21 : 60),
                'ciclo_phva' => $ciclo,
                'categoria' => $categoria
            ];
        }

        return $grouped;
    }

    /**
     * Calcula el peso total de un conjunto de estándares
     */
    public function calcularPesoTotal(array $idsEstandares): float
    {
        if (empty($idsEstandares)) {
            return 0;
        }

        $result = $this->selectSum('peso_porcentual')
                       ->whereIn('id_estandar', $idsEstandares)
                       ->first();

        return (float) ($result['peso_porcentual'] ?? 0);
    }

    /**
     * Busca estándares por término
     */
    public function buscar(string $termino): array
    {
        $terminoEscapado = $this->db->escapeLikeString($termino);
        $collate = 'COLLATE utf8mb4_general_ci';

        return $this->where("(nombre {$collate} LIKE '%{$terminoEscapado}%' OR item {$collate} LIKE '%{$terminoEscapado}%' OR categoria_nombre {$collate} LIKE '%{$terminoEscapado}%')", null, false)
                    ->orderBy('item', 'ASC')
                    ->findAll();
    }
}
