<?php

namespace App\Models;

use CodeIgniter\Model;

class MatrizLegalModel extends Model
{
    protected $table = 'matriz_legal';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'categoria',
        'clasificacion',
        'tema',
        'subtema',
        'tipo_norma',
        'id_norma_legal',
        'anio',
        'fecha_expedicion',
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

    // Categorias (hojas del compendio legal)
    public static array $categorias = [
        'Medicina Laboral' => 'Medicina Laboral',
        'Sistema General de SSS' => 'Sistema General de SSS',
        'Seguridad e Higiene Industrial' => 'Seguridad e Higiene Industrial',
        'COVID 19' => 'COVID 19',
        'Ambiente Nacional' => 'Ambiente Nacional',
        'Ambiente Regional' => 'Ambiente Regional',
        'Ambiente Bogotá' => 'Ambiente Bogotá'
    ];

    // Tipos de norma
    public static array $tiposNorma = [
        'Ley' => 'Ley',
        'Decreto' => 'Decreto',
        'Decreto Ley' => 'Decreto Ley',
        'Resolución' => 'Resolución',
        'Circular' => 'Circular',
        'Circular Externa' => 'Circular Externa',
        'Acuerdo' => 'Acuerdo',
        'Sentencia' => 'Sentencia',
        'Concepto' => 'Concepto',
        'Norma Técnica' => 'Norma Técnica',
        'Código Sustantivo del Trabajo' => 'Código Sustantivo del Trabajo',
        'Constitución' => 'Constitución',
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

        if (!empty($filtros['categoria'])) {
            $builder->where('categoria', $filtros['categoria']);
        }

        if (!empty($filtros['clasificacion'])) {
            $builder->where('clasificacion', $filtros['clasificacion']);
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
                    ->orLike('clasificacion', $filtros['busqueda'])
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
            0 => null,               // Control expandir
            1 => 'clasificacion',
            2 => 'tipo_norma',
            3 => 'id_norma_legal',   // Columna "Norma"
            4 => 'anio',
            5 => 'tema',
            6 => 'autoridad_emisora',
            7 => 'estado',
            8 => null                // Acciones
        ];

        // Filtro por categoria (enviado como parametro extra)
        if (!empty($params['categoria'])) {
            $builder->where('categoria', $params['categoria']);
        }

        // Filtro por clasificacion (enviado como parametro extra)
        if (!empty($params['clasificacion_filtro'])) {
            $builder->where('clasificacion', $params['clasificacion_filtro']);
        }

        // Busqueda global
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $builder->groupStart()
                    ->like('categoria', $search)
                    ->orLike('clasificacion', $search)
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

                    if (in_array($campo, ['tipo_norma', 'anio', 'estado', 'clasificacion'])) {
                        $builder->where($campo, $valor);
                    } else {
                        $builder->like($campo, $valor);
                    }
                }
            }
        }

        // Total filtrado
        $totalFiltered = $builder->countAllResults(false);

        // Ordenamiento
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

        // Paginacion
        if (isset($params['start']) && isset($params['length']) && $params['length'] != -1) {
            $builder->limit($params['length'], $params['start']);
        }

        $data = $builder->get()->getResultArray();

        // Total general (respetando filtro de categoria si existe)
        $totalBuilder = $this->builder();
        if (!empty($params['categoria'])) {
            $totalBuilder->where('categoria', $params['categoria']);
        }
        if (!empty($params['clasificacion_filtro'])) {
            $totalBuilder->where('clasificacion', $params['clasificacion_filtro']);
        }
        $recordsTotal = $totalBuilder->countAllResults();

        return [
            'data' => $data,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $totalFiltered
        ];
    }

    /**
     * Obtener categorias con conteo
     */
    public function getCategoriasConConteo(): array
    {
        return $this->db->query(
            "SELECT categoria, COUNT(*) as total FROM matriz_legal GROUP BY categoria ORDER BY total DESC"
        )->getResultArray();
    }

    /**
     * Obtener clasificaciones unicas por categoria
     */
    public function getClasificacionesPorCategoria(string $categoria): array
    {
        return $this->db->query(
            "SELECT clasificacion, COUNT(*) as total FROM matriz_legal WHERE categoria = ? AND clasificacion IS NOT NULL AND clasificacion != '' GROUP BY clasificacion ORDER BY clasificacion ASC",
            [$categoria]
        )->getResultArray();
    }

    /**
     * Obtener temas unicos para filtros
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
     * Obtener anios unicos para filtros
     */
    public function getAniosUnicos(): array
    {
        return $this->distinct()
                    ->select('anio')
                    ->where('anio IS NOT NULL')
                    ->where('anio >', 0)
                    ->orderBy('anio', 'DESC')
                    ->findAll();
    }

    /**
     * Importar desde CSV
     */
    public function importarCSV(array $datos, string $categoriaDefecto = 'Seguridad e Higiene Industrial'): array
    {
        $insertados = 0;
        $errores = [];

        $this->db->transStart();

        foreach ($datos as $index => $fila) {
            try {
                $registro = [
                    'categoria' => !empty($fila['categoria']) ? $fila['categoria'] : $categoriaDefecto,
                    'clasificacion' => $fila['clasificacion'] ?? '',
                    'tema' => $fila['tema'] ?? '',
                    'subtema' => $fila['subtema'] ?? '',
                    'tipo_norma' => $fila['tipo_norma'] ?? '',
                    'id_norma_legal' => $fila['id_norma_legal'] ?? '',
                    'anio' => (int)($fila['anio'] ?? 0),
                    'fecha_expedicion' => !empty($fila['fecha_expedicion']) ? $fila['fecha_expedicion'] : null,
                    'descripcion_norma' => $fila['descripcion_norma'] ?? '',
                    'autoridad_emisora' => $fila['autoridad_emisora'] ?? '',
                    'referente_nacional' => $fila['referente_nacional'] ?? '',
                    'referente_internacional' => $fila['referente_internacional'] ?? '',
                    'articulos_aplicables' => $fila['articulos_aplicables'] ?? '',
                    'parametros' => $fila['parametros'] ?? '',
                    'notas_vigencia' => $fila['notas_vigencia'] ?? '',
                    'estado' => 'activa'
                ];

                if (empty($registro['tema']) || empty($registro['tipo_norma']) || empty($registro['id_norma_legal'])) {
                    $errores[] = "Fila " . ($index + 2) . ": Faltan campos obligatorios";
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
     * Buscar norma por numero y tipo
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
     * Obtener estadisticas generales
     */
    public function getEstadisticas(): array
    {
        $db = \Config\Database::connect();

        $stats = [
            'total' => $this->countAll(),
            'por_categoria' => [],
            'por_tipo' => [],
            'por_estado' => [],
        ];

        // Por categoria
        $result = $db->query("SELECT categoria, COUNT(*) as total FROM matriz_legal GROUP BY categoria ORDER BY total DESC")->getResultArray();
        foreach ($result as $row) {
            $stats['por_categoria'][$row['categoria']] = (int)$row['total'];
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

        return $stats;
    }
}
