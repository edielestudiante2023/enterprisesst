<?php

namespace App\Models;

use CodeIgniter\Model;

class MatrizLegalModel extends Model
{
    protected $table = 'matriz_legal';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'sector',
        'tema',
        'subtema',
        'tipo_norma',
        'id_norma_legal',
        'anio',
        'descripcion_norma',
        'autoridad_emisora',
        'referente_nacional',
        'referente_internacional',
        'articulos_aplicables',
        'parametros',
        'notas_vigencia',
        'estado'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Sectores disponibles
    public static array $sectores = [
        'General' => 'General (aplica a todos)',
        'Salud' => 'Salud',
        'Construcción' => 'Construcción',
        'Minería' => 'Minería',
        'Hidrocarburos' => 'Hidrocarburos',
        'Transporte' => 'Transporte',
        'Educación' => 'Educación',
        'Manufactura' => 'Manufactura',
        'Agricultura' => 'Agricultura',
        'Comercio' => 'Comercio',
        'Servicios' => 'Servicios',
        'Otro' => 'Otro'
    ];

    // Tipos de norma
    public static array $tiposNorma = [
        'Ley' => 'Ley',
        'Decreto' => 'Decreto',
        'Decreto Ley' => 'Decreto Ley',
        'Resolución' => 'Resolución',
        'Circular' => 'Circular',
        'Acuerdo' => 'Acuerdo',
        'Sentencia' => 'Sentencia',
        'Concepto' => 'Concepto',
        'Norma Técnica' => 'Norma Técnica',
        'Otro' => 'Otro'
    ];

    // Estados
    public static array $estados = [
        'activa' => 'Activa',
        'derogada' => 'Derogada',
        'modificada' => 'Modificada'
    ];

    /**
     * Obtener todas las normas con filtros opcionales
     */
    public function getNormas(array $filtros = []): array
    {
        $builder = $this->builder();

        if (!empty($filtros['sector'])) {
            $builder->where('sector', $filtros['sector']);
        }

        if (!empty($filtros['tema'])) {
            $builder->like('tema', $filtros['tema']);
        }

        if (!empty($filtros['tipo_norma'])) {
            $builder->where('tipo_norma', $filtros['tipo_norma']);
        }

        if (!empty($filtros['anio'])) {
            $builder->where('anio', $filtros['anio']);
        }

        if (!empty($filtros['estado'])) {
            $builder->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['busqueda'])) {
            $builder->groupStart()
                    ->like('tema', $filtros['busqueda'])
                    ->orLike('subtema', $filtros['busqueda'])
                    ->orLike('id_norma_legal', $filtros['busqueda'])
                    ->orLike('descripcion_norma', $filtros['busqueda'])
                    ->orLike('autoridad_emisora', $filtros['busqueda'])
                    ->groupEnd();
        }

        return $builder->orderBy('anio', 'DESC')
                      ->orderBy('tipo_norma', 'ASC')
                      ->orderBy('id_norma_legal', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener normas para DataTables con server-side processing
     */
    public function getNormasDataTables(array $params): array
    {
        $builder = $this->builder();

        // Mapeo de columnas DataTables a campos de la BD
        $columnMap = [
            0 => null,           // Control expandir
            1 => 'sector',
            2 => 'tipo_norma',
            3 => 'id_norma_legal', // Columna "Norma"
            4 => 'anio',
            5 => 'tema',
            6 => 'autoridad_emisora',
            7 => 'estado',
            8 => null            // Acciones
        ];

        // Búsqueda global
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $builder->groupStart()
                    ->like('sector', $search)
                    ->orLike('tema', $search)
                    ->orLike('subtema', $search)
                    ->orLike('tipo_norma', $search)
                    ->orLike('id_norma_legal', $search)
                    ->orLike('descripcion_norma', $search)
                    ->orLike('autoridad_emisora', $search)
                    ->orLike('anio', $search)
                    ->groupEnd();
        }

        // Filtros por columna individual
        if (!empty($params['columns'])) {
            foreach ($params['columns'] as $index => $column) {
                if (!empty($column['search']['value']) && isset($columnMap[$index]) && $columnMap[$index] !== null) {
                    $campo = $columnMap[$index];
                    $valor = $column['search']['value'];

                    // Para campos exactos (selects)
                    if (in_array($campo, ['sector', 'tipo_norma', 'anio', 'estado'])) {
                        $builder->where($campo, $valor);
                    } else {
                        // Para campos de texto (inputs)
                        $builder->like($campo, $valor);
                    }
                }
            }
        }

        // Total filtrado
        $totalFiltered = $builder->countAllResults(false);

        // Ordenamiento
        $columns = ['id', 'sector', 'tipo_norma', 'id_norma_legal', 'anio', 'tema', 'autoridad_emisora', 'estado'];
        if (!empty($params['order'])) {
            foreach ($params['order'] as $order) {
                $colIdx = $order['column'];
                $dir = $order['dir'];
                if (isset($columnMap[$colIdx]) && $columnMap[$colIdx] !== null) {
                    $builder->orderBy($columnMap[$colIdx], $dir);
                }
            }
        } else {
            $builder->orderBy('anio', 'DESC')->orderBy('id', 'DESC');
        }

        // Paginación
        if (isset($params['start']) && isset($params['length']) && $params['length'] != -1) {
            $builder->limit($params['length'], $params['start']);
        }

        $data = $builder->get()->getResultArray();

        return [
            'data' => $data,
            'recordsTotal' => $this->countAll(),
            'recordsFiltered' => $totalFiltered
        ];
    }

    /**
     * Obtener temas únicos para filtros
     */
    public function getTemasUnicos(): array
    {
        return $this->distinct()
                    ->select('tema')
                    ->where('tema IS NOT NULL')
                    ->where('tema !=', '')
                    ->orderBy('tema', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener subtemas únicos para filtros
     */
    public function getSubtemasUnicos(?string $tema = null): array
    {
        $builder = $this->distinct()
                        ->select('subtema')
                        ->where('subtema IS NOT NULL')
                        ->where('subtema !=', '');

        if ($tema) {
            $builder->where('tema', $tema);
        }

        return $builder->orderBy('subtema', 'ASC')->findAll();
    }

    /**
     * Obtener años únicos para filtros
     */
    public function getAniosUnicos(): array
    {
        return $this->distinct()
                    ->select('anio')
                    ->where('anio IS NOT NULL')
                    ->orderBy('anio', 'DESC')
                    ->findAll();
    }

    /**
     * Importar desde CSV
     */
    public function importarCSV(array $datos, string $sectorDefecto = 'General'): array
    {
        $insertados = 0;
        $errores = [];

        $this->db->transStart();

        foreach ($datos as $index => $fila) {
            try {
                $registro = [
                    'sector' => !empty($fila['sector']) ? $fila['sector'] : $sectorDefecto,
                    'tema' => $fila['tema'] ?? '',
                    'subtema' => $fila['subtema'] ?? '',
                    'tipo_norma' => $fila['tipo_norma'] ?? '',
                    'id_norma_legal' => $fila['id_norma_legal'] ?? '',
                    'anio' => (int)($fila['anio'] ?? 0),
                    'descripcion_norma' => $fila['descripcion_norma'] ?? '',
                    'autoridad_emisora' => $fila['autoridad_emisora'] ?? '',
                    'referente_nacional' => $fila['referente_nacional'] ?? '',
                    'referente_internacional' => $fila['referente_internacional'] ?? '',
                    'articulos_aplicables' => $fila['articulos_aplicables'] ?? '',
                    'parametros' => $fila['parametros'] ?? '',
                    'notas_vigencia' => $fila['notas_vigencia'] ?? '',
                    'estado' => 'activa'
                ];

                // Validar campos obligatorios
                if (empty($registro['tema']) || empty($registro['tipo_norma']) || empty($registro['id_norma_legal'])) {
                    $errores[] = "Fila " . ($index + 2) . ": Faltan campos obligatorios (tema, tipo_norma, id_norma_legal)";
                    continue;
                }

                $this->insert($registro);
                $insertados++;

            } catch (\Exception $e) {
                $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $this->db->transComplete();

        return [
            'insertados' => $insertados,
            'errores' => $errores,
            'exito' => $this->db->transStatus()
        ];
    }

    /**
     * Buscar norma por número y tipo
     */
    public function buscarPorNumero(string $tipoNorma, string $numero, ?int $anio = null): ?array
    {
        $builder = $this->where('tipo_norma', $tipoNorma)
                        ->where('id_norma_legal', $numero);

        if ($anio) {
            $builder->where('anio', $anio);
        }

        return $builder->first();
    }

    /**
     * Verificar si existe una norma
     */
    public function existeNorma(string $tipoNorma, string $numero, int $anio): bool
    {
        return $this->where('tipo_norma', $tipoNorma)
                    ->where('id_norma_legal', $numero)
                    ->where('anio', $anio)
                    ->countAllResults() > 0;
    }

    /**
     * Obtener estadísticas generales
     */
    public function getEstadisticas(): array
    {
        $db = \Config\Database::connect();

        $stats = [
            'total' => $this->countAll(),
            'por_sector' => [],
            'por_tipo' => [],
            'por_estado' => [],
            'por_anio' => []
        ];

        // Por sector
        $result = $db->query("SELECT sector, COUNT(*) as total FROM matriz_legal GROUP BY sector ORDER BY total DESC")->getResultArray();
        foreach ($result as $row) {
            $stats['por_sector'][$row['sector']] = (int)$row['total'];
        }

        // Por tipo de norma
        $result = $db->query("SELECT tipo_norma, COUNT(*) as total FROM matriz_legal GROUP BY tipo_norma ORDER BY total DESC")->getResultArray();
        foreach ($result as $row) {
            $stats['por_tipo'][$row['tipo_norma']] = (int)$row['total'];
        }

        // Por estado
        $result = $db->query("SELECT estado, COUNT(*) as total FROM matriz_legal GROUP BY estado ORDER BY total DESC")->getResultArray();
        foreach ($result as $row) {
            $stats['por_estado'][$row['estado']] = (int)$row['total'];
        }

        // Por año (últimos 10 años)
        $result = $db->query("SELECT anio, COUNT(*) as total FROM matriz_legal WHERE anio >= YEAR(CURDATE()) - 10 GROUP BY anio ORDER BY anio DESC")->getResultArray();
        foreach ($result as $row) {
            $stats['por_anio'][$row['anio']] = (int)$row['total'];
        }

        return $stats;
    }
}
