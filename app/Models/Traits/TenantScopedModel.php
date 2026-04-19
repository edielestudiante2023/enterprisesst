<?php

namespace App\Models\Traits;

use App\Libraries\TenantFilter;

/**
 * Trait TenantScopedModel
 *
 * Agrega filtrado automatico por empresa consultora a cualquier modelo
 * que tenga una columna `id_cliente`.
 *
 * Uso:
 *   class MiModel extends Model {
 *       use TenantScopedModel;
 *       ...
 *   }
 *
 * Automaticamente filtra findAll(), first() y find() para que solo
 * devuelvan registros de clientes de la empresa del usuario en sesion.
 * Superadmin y CLI no se filtran.
 *
 * Ver: docs/MULTI_TENANT_EMPRESA_CONSULTORA/01_ARQUITECTURA.md
 */
trait TenantScopedModel
{
    private function shouldApplyTenantScope(): bool
    {
        if (is_cli()) return false;
        $session = session();
        if (!$session->get('isLoggedIn')) return false;
        if ($session->get('is_superadmin')) return false;
        if (!$session->get('id_empresa_consultora')) return false;
        return true;
    }

    /**
     * Verifica si la tabla del modelo tiene la columna id_cliente.
     * Cachea el resultado en memoria para evitar consultas repetidas.
     */
    private function tableHasIdCliente(): bool
    {
        static $cache = [];
        $table = $this->table;
        if (isset($cache[$table])) return $cache[$table];

        // Heuristica rapida: si el modelo declara 'id_cliente' en allowedFields,
        // entonces la columna existe en su tabla.
        if (property_exists($this, 'allowedFields') && is_array($this->allowedFields)
            && in_array('id_cliente', $this->allowedFields, true)) {
            return $cache[$table] = true;
        }

        // Fallback: consultar INFORMATION_SCHEMA
        try {
            $db = \Config\Database::connect();
            $exists = $db->query(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = 'id_cliente'
                 LIMIT 1",
                [$table]
            )->getRowArray();
            return $cache[$table] = (bool)$exists;
        } catch (\Throwable $e) {
            return $cache[$table] = false;
        }
    }

    private function applyTenantScopeToBuilder(): void
    {
        if (!$this->shouldApplyTenantScope()) return;
        if (!$this->tableHasIdCliente()) return; // tabla sin id_cliente: no filtra

        $clientIds = TenantFilter::getMyClientIds();
        if ($clientIds === null) return; // superadmin/CLI

        if (empty($clientIds)) {
            // No tiene clientes: no devolver nada
            $this->where('1', 0, false);
            return;
        }

        $this->whereIn($this->table . '.id_cliente', $clientIds);
    }

    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->applyTenantScopeToBuilder();
        return parent::findAll($limit, $offset);
    }

    public function first()
    {
        $this->applyTenantScopeToBuilder();
        return parent::first();
    }

    /**
     * Valida que un registro pertenezca a la empresa del usuario antes de permitir la operacion.
     * Retorna true si la operacion puede continuar, lanza excepcion si no.
     */
    private function assertRecordBelongsToTenant($id, string $operation): bool
    {
        if ($id === null) return true; // operacion masiva, no validamos por ID individual
        if (!$this->shouldApplyTenantScope()) return true;
        if (!$this->tableHasIdCliente()) return true; // tabla sin id_cliente: no valida

        $record = parent::find($id);
        if (!$record) return true; // registro no existe, el parent manejara el error

        // Si el registro tiene id_cliente, verificar pertenencia
        if (isset($record['id_cliente'])) {
            $clientIds = TenantFilter::getMyClientIds();
            if ($clientIds !== null && !in_array((int)$record['id_cliente'], $clientIds, true)) {
                log_message('warning', "TenantScope BLOCKED {$operation} on {$this->table} id={$id}"
                    . ' user_id=' . (session()->get('id_usuario') ?? 'null')
                    . ' empresa=' . (session()->get('id_empresa_consultora') ?? 'null'));
                throw new \RuntimeException('No tienes permiso para ' .
                    ($operation === 'delete' ? 'eliminar' : 'modificar') . ' este registro.');
            }
        }

        return true;
    }

    /**
     * Override update: valida pertenencia antes de ejecutar.
     */
    public function update($id = null, $row = null): bool
    {
        $this->assertRecordBelongsToTenant($id, 'update');
        return parent::update($id, $row);
    }

    /**
     * Override delete: valida pertenencia antes de ejecutar.
     */
    public function delete($id = null, bool $purge = false)
    {
        $this->assertRecordBelongsToTenant($id, 'delete');
        return parent::delete($id, $purge);
    }
}
