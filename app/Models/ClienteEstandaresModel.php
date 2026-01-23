<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteEstandaresModel extends Model
{
    protected $table = 'tbl_cliente_estandares';
    protected $primaryKey = 'id_cliente_estandar';
    protected $allowedFields = [
        'id_cliente', 'id_estandar', 'estado', 'fecha_cumplimiento',
        'evidencia_path', 'observaciones', 'calificacion'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtiene estándares de un cliente con información completa
     */
    public function getByClienteCompleto(int $idCliente): array
    {
        return $this->select('tbl_cliente_estandares.*,
                             tbl_estandares_minimos.ciclo_phva,
                             tbl_estandares_minimos.categoria,
                             tbl_estandares_minimos.categoria_nombre,
                             tbl_estandares_minimos.item,
                             tbl_estandares_minimos.nombre,
                             tbl_estandares_minimos.peso_porcentual,
                             tbl_estandares_minimos.documentos_sugeridos')
                    ->join('tbl_estandares_minimos', 'tbl_estandares_minimos.id_estandar = tbl_cliente_estandares.id_estandar')
                    ->where('tbl_cliente_estandares.id_cliente', $idCliente)
                    ->where('tbl_cliente_estandares.estado !=', 'no_aplica')
                    ->orderBy('tbl_estandares_minimos.item', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene estándares agrupados por ciclo PHVA
     */
    public function getByClienteGroupedPHVA(int $idCliente): array
    {
        $estandares = $this->getByClienteCompleto($idCliente);

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
     * Obtiene resumen de cumplimiento de un cliente
     */
    public function getResumenCumplimiento(int $idCliente): array
    {
        $result = $this->select('estado, COUNT(*) as cantidad')
                       ->where('id_cliente', $idCliente)
                       ->where('estado !=', 'no_aplica')
                       ->groupBy('estado')
                       ->findAll();

        $resumen = [
            'pendiente' => 0,
            'en_proceso' => 0,
            'cumple' => 0,
            'no_cumple' => 0,
            'total' => 0
        ];

        foreach ($result as $row) {
            $resumen[$row['estado']] = (int) $row['cantidad'];
            $resumen['total'] += (int) $row['cantidad'];
        }

        // Calcular porcentaje
        $resumen['porcentaje_cumplimiento'] = $resumen['total'] > 0
            ? round(($resumen['cumple'] / $resumen['total']) * 100, 2)
            : 0;

        return $resumen;
    }

    /**
     * Calcula porcentaje de cumplimiento ponderado
     * Retorna el porcentaje de cumplimiento (float) o los datos completos si $returnAll es true
     */
    public function getCumplimientoPonderado(int $idCliente, bool $returnAll = false)
    {
        $db = \Config\Database::connect();

        // Usar el stored procedure
        $query = $db->query("CALL sp_calcular_cumplimiento(?)", [$idCliente]);

        if ($query === false) {
            return $returnAll ? [] : 0.0;
        }

        $results = $query->getResultArray();
        $query->freeResult();

        if ($returnAll) {
            return $results;
        }

        // Buscar el registro TOTAL y retornar su porcentaje
        foreach ($results as $row) {
            if ($row['estado'] === 'TOTAL') {
                return (float) ($row['porcentaje'] ?? 0);
            }
        }

        return 0.0;
    }

    /**
     * Actualiza estado de un estándar
     */
    public function actualizarEstado(int $idCliente, int $idEstandar, string $estado, ?string $observaciones = null): bool
    {
        $data = ['estado' => $estado];

        if ($estado === 'cumple') {
            $data['fecha_cumplimiento'] = date('Y-m-d');
        }

        if ($observaciones !== null) {
            $data['observaciones'] = $observaciones;
        }

        return $this->where('id_cliente', $idCliente)
                    ->where('id_estandar', $idEstandar)
                    ->set($data)
                    ->update();
    }

    /**
     * Inicializa estándares para un cliente usando SP
     */
    public function inicializarParaCliente(int $idCliente): array
    {
        $db = \Config\Database::connect();

        $query = $db->query("CALL sp_inicializar_estandares_cliente(?)", [$idCliente]);
        $result = $query->getRowArray();
        $query->close();

        return $result ?? [];
    }

    /**
     * Obtiene estándares pendientes de un cliente
     */
    public function getPendientes(int $idCliente): array
    {
        return $this->select('tbl_cliente_estandares.*,
                             tbl_estandares_minimos.item,
                             tbl_estandares_minimos.nombre,
                             tbl_estandares_minimos.documentos_sugeridos')
                    ->join('tbl_estandares_minimos', 'tbl_estandares_minimos.id_estandar = tbl_cliente_estandares.id_estandar')
                    ->where('tbl_cliente_estandares.id_cliente', $idCliente)
                    ->where('tbl_cliente_estandares.estado', 'pendiente')
                    ->orderBy('tbl_estandares_minimos.item', 'ASC')
                    ->findAll();
    }

    /**
     * Registra evidencia para un estándar
     */
    public function registrarEvidencia(int $idCliente, int $idEstandar, string $path): bool
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('id_estandar', $idEstandar)
                    ->set(['evidencia_path' => $path])
                    ->update();
    }
}
