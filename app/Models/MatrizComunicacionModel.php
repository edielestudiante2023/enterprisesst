<?php

namespace App\Models;

use CodeIgniter\Model;

class MatrizComunicacionModel extends Model
{
    protected $table = 'matriz_comunicacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_cliente',
        'categoria',
        'situacion_evento',
        'que_comunicar',
        'quien_comunica',
        'a_quien_comunicar',
        'mecanismo_canal',
        'frecuencia_plazo',
        'registro_evidencia',
        'norma_aplicable',
        'tipo',
        'estado',
        'generado_por_ia'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Categorías de comunicación SST
    public static array $categorias = [
        'Accidentes de Trabajo' => 'Accidentes de Trabajo',
        'Incidentes' => 'Incidentes',
        'Enfermedades Laborales' => 'Enfermedades Laborales',
        'Emergencias' => 'Emergencias',
        'Convivencia Laboral' => 'Convivencia Laboral',
        'Peligros y Riesgos' => 'Peligros y Riesgos',
        'Resultados de Auditoría' => 'Resultados de Auditoría',
        'Cambios Normativos' => 'Cambios Normativos',
        'Capacitaciones' => 'Capacitaciones',
        'COPASST / Comité de Convivencia' => 'COPASST / Comité de Convivencia',
        'Comunicación Externa' => 'Comunicación Externa (ARL, EPS, MinTrabajo)',
    ];

    // Tipos de comunicación
    public static array $tipos = [
        'interna' => 'Interna',
        'externa' => 'Externa',
        'ambas' => 'Ambas',
    ];

    // Estados
    public static array $estados = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
    ];

    /**
     * Obtener protocolos con filtros opcionales (por cliente)
     */
    public function getProtocolos(int $idCliente, array $filtros = []): array
    {
        $builder = $this->builder();
        $builder->where('id_cliente', $idCliente);

        if (!empty($filtros['categoria'])) {
            $builder->where('categoria', $filtros['categoria']);
        }

        if (!empty($filtros['tipo'])) {
            $builder->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['estado'])) {
            $builder->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['busqueda'])) {
            $builder->groupStart()
                    ->like('situacion_evento', $filtros['busqueda'])
                    ->orLike('que_comunicar', $filtros['busqueda'])
                    ->orLike('quien_comunica', $filtros['busqueda'])
                    ->orLike('a_quien_comunicar', $filtros['busqueda'])
                    ->orLike('norma_aplicable', $filtros['busqueda'])
                    ->groupEnd();
        }

        return $builder->orderBy('categoria', 'ASC')
                      ->orderBy('situacion_evento', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener protocolos para DataTables con server-side processing
     */
    public function getProtocolosDataTables(int $idCliente, array $params): array
    {
        $builder = $this->builder();
        $builder->where('id_cliente', $idCliente);

        // Mapeo de columnas DataTables a campos de la BD
        $columnMap = [
            0 => null,                // Control expandir
            1 => 'categoria',
            2 => 'situacion_evento',
            3 => 'quien_comunica',
            4 => 'a_quien_comunicar',
            5 => 'tipo',
            6 => 'frecuencia_plazo',
            7 => 'estado',
            8 => null                 // Acciones
        ];

        // Búsqueda global
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $builder->groupStart()
                    ->like('categoria', $search)
                    ->orLike('situacion_evento', $search)
                    ->orLike('que_comunicar', $search)
                    ->orLike('quien_comunica', $search)
                    ->orLike('a_quien_comunicar', $search)
                    ->orLike('mecanismo_canal', $search)
                    ->orLike('norma_aplicable', $search)
                    ->groupEnd();
        }

        // Filtros por columna individual
        if (!empty($params['columns'])) {
            foreach ($params['columns'] as $index => $column) {
                if (!empty($column['search']['value']) && isset($columnMap[$index]) && $columnMap[$index] !== null) {
                    $campo = $columnMap[$index];
                    $valor = $column['search']['value'];

                    if (in_array($campo, ['categoria', 'tipo', 'estado'])) {
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
            $builder->orderBy('categoria', 'ASC')->orderBy('id', 'ASC');
        }

        // Paginación
        if (isset($params['start']) && isset($params['length']) && $params['length'] != -1) {
            $builder->limit($params['length'], $params['start']);
        }

        $data = $builder->get()->getResultArray();

        // Total sin filtros (pero siempre por cliente)
        $totalAll = $this->where('id_cliente', $idCliente)->countAllResults();

        return [
            'data' => $data,
            'recordsTotal' => $totalAll,
            'recordsFiltered' => $totalFiltered
        ];
    }

    /**
     * Obtener categorías únicas para filtros (por cliente)
     */
    public function getCategoriasUnicas(int $idCliente): array
    {
        return $this->distinct()
                    ->select('categoria')
                    ->where('id_cliente', $idCliente)
                    ->where('categoria IS NOT NULL')
                    ->where('categoria !=', '')
                    ->orderBy('categoria', 'ASC')
                    ->findAll();
    }

    /**
     * Importar desde CSV
     */
    public function importarCSV(int $idCliente, array $datos): array
    {
        $insertados = 0;
        $errores = [];

        $this->db->transStart();

        foreach ($datos as $index => $fila) {
            try {
                $registro = [
                    'id_cliente' => $idCliente,
                    'categoria' => $fila['categoria'] ?? '',
                    'situacion_evento' => $fila['situacion_evento'] ?? '',
                    'que_comunicar' => $fila['que_comunicar'] ?? '',
                    'quien_comunica' => $fila['quien_comunica'] ?? '',
                    'a_quien_comunicar' => $fila['a_quien_comunicar'] ?? '',
                    'mecanismo_canal' => $fila['mecanismo_canal'] ?? '',
                    'frecuencia_plazo' => $fila['frecuencia_plazo'] ?? '',
                    'registro_evidencia' => $fila['registro_evidencia'] ?? '',
                    'norma_aplicable' => $fila['norma_aplicable'] ?? '',
                    'tipo' => $fila['tipo'] ?? 'interna',
                    'estado' => 'activo'
                ];

                // Validar campos obligatorios
                if (empty($registro['categoria']) || empty($registro['situacion_evento']) || empty($registro['que_comunicar'])) {
                    $errores[] = "Fila " . ($index + 2) . ": Faltan campos obligatorios (categoria, situacion_evento, que_comunicar)";
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
     * Importar protocolos generados por IA (bulk)
     */
    public function importarDesdeIA(int $idCliente, array $protocolos): array
    {
        $insertados = 0;
        $errores = [];

        $this->db->transStart();

        foreach ($protocolos as $index => $protocolo) {
            try {
                $registro = [
                    'id_cliente' => $idCliente,
                    'categoria' => $protocolo['categoria'] ?? '',
                    'situacion_evento' => $protocolo['situacion_evento'] ?? '',
                    'que_comunicar' => $protocolo['que_comunicar'] ?? '',
                    'quien_comunica' => $protocolo['quien_comunica'] ?? '',
                    'a_quien_comunicar' => $protocolo['a_quien_comunicar'] ?? '',
                    'mecanismo_canal' => $protocolo['mecanismo_canal'] ?? '',
                    'frecuencia_plazo' => $protocolo['frecuencia_plazo'] ?? '',
                    'registro_evidencia' => $protocolo['registro_evidencia'] ?? '',
                    'norma_aplicable' => $protocolo['norma_aplicable'] ?? '',
                    'tipo' => $protocolo['tipo'] ?? 'interna',
                    'estado' => 'activo',
                    'generado_por_ia' => 1
                ];

                if (empty($registro['categoria']) || empty($registro['situacion_evento'])) {
                    $errores[] = "Protocolo " . ($index + 1) . ": Faltan campos obligatorios";
                    continue;
                }

                $this->insert($registro);
                $insertados++;

            } catch (\Exception $e) {
                $errores[] = "Protocolo " . ($index + 1) . ": " . $e->getMessage();
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
     * Verificar si existe un protocolo similar
     */
    public function existeProtocolo(int $idCliente, string $categoria, string $situacion): bool
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('categoria', $categoria)
                    ->like('situacion_evento', $situacion)
                    ->countAllResults() > 0;
    }

    /**
     * Obtener estadísticas por cliente
     */
    public function getEstadisticas(int $idCliente): array
    {
        $db = \Config\Database::connect();

        $stats = [
            'total' => $this->where('id_cliente', $idCliente)->countAllResults(),
            'por_categoria' => [],
            'por_tipo' => [],
            'por_estado' => [],
            'generados_ia' => 0
        ];

        // Por categoría
        $result = $db->query("SELECT categoria, COUNT(*) as total FROM matriz_comunicacion WHERE id_cliente = ? GROUP BY categoria ORDER BY total DESC", [$idCliente])->getResultArray();
        foreach ($result as $row) {
            $stats['por_categoria'][$row['categoria']] = (int)$row['total'];
        }

        // Por tipo
        $result = $db->query("SELECT tipo, COUNT(*) as total FROM matriz_comunicacion WHERE id_cliente = ? GROUP BY tipo ORDER BY total DESC", [$idCliente])->getResultArray();
        foreach ($result as $row) {
            $stats['por_tipo'][$row['tipo']] = (int)$row['total'];
        }

        // Por estado
        $result = $db->query("SELECT estado, COUNT(*) as total FROM matriz_comunicacion WHERE id_cliente = ? GROUP BY estado ORDER BY total DESC", [$idCliente])->getResultArray();
        foreach ($result as $row) {
            $stats['por_estado'][$row['estado']] = (int)$row['total'];
        }

        // Generados por IA
        $stats['generados_ia'] = $this->where('id_cliente', $idCliente)->where('generado_por_ia', 1)->countAllResults();

        return $stats;
    }
}
