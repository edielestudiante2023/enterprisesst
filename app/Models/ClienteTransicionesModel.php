<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteTransicionesModel extends Model
{
    protected $table = 'tbl_cliente_transiciones';
    protected $primaryKey = 'id_transicion';
    protected $allowedFields = [
        'id_cliente', 'nivel_anterior', 'nivel_nuevo', 'motivo',
        'fecha_deteccion', 'fecha_aplicacion', 'estado', 'aplicado_por'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = false;

    /**
     * Obtiene transiciones de un cliente
     */
    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->orderBy('fecha_deteccion', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene transiciones pendientes de un cliente
     */
    public function getPendientes(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('estado', 'detectado')
                    ->orderBy('fecha_deteccion', 'DESC')
                    ->findAll();
    }

    /**
     * Registra una transición usando el SP
     */
    public function detectarCambio(int $idCliente, int $nuevoTrabajadores, string $nuevoRiesgo): array
    {
        $db = \Config\Database::connect();

        $query = $db->query("CALL sp_detectar_cambio_nivel(?, ?, ?)", [
            $idCliente,
            $nuevoTrabajadores,
            $nuevoRiesgo
        ]);

        $result = $query->getRowArray();
        $query->close();

        return $result ?? ['alerta' => 'SIN_CAMBIO'];
    }

    /**
     * Aplica una transición (actualiza estándares del cliente)
     */
    public function aplicarTransicion(int $idTransicion, int $aplicadoPor): bool
    {
        $transicion = $this->find($idTransicion);

        if (!$transicion || $transicion['estado'] !== 'detectado') {
            return false;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Actualizar contexto del cliente con nuevo nivel
        $db->table('tbl_cliente_contexto_sst')
           ->where('id_cliente', $transicion['id_cliente'])
           ->update(['estandares_aplicables' => $transicion['nivel_nuevo']]);

        // Reinicializar estándares del cliente
        $db->query("CALL sp_inicializar_estandares_cliente(?)", [$transicion['id_cliente']]);

        // Marcar transición como aplicada
        $this->update($idTransicion, [
            'estado' => 'aplicado',
            'fecha_aplicacion' => date('Y-m-d H:i:s'),
            'aplicado_por' => $aplicadoPor
        ]);

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Rechaza una transición
     */
    public function rechazarTransicion(int $idTransicion, string $motivo): bool
    {
        return $this->update($idTransicion, [
            'estado' => 'rechazado',
            'motivo' => $motivo
        ]);
    }

    /**
     * Obtiene todas las transiciones pendientes (para alertas)
     */
    public function getAllPendientes(): array
    {
        return $this->select('tbl_cliente_transiciones.*, tbl_clientes.nombre_cliente')
                    ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_cliente_transiciones.id_cliente')
                    ->where('tbl_cliente_transiciones.estado', 'detectado')
                    ->orderBy('fecha_deteccion', 'DESC')
                    ->findAll();
    }
}
