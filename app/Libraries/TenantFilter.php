<?php

namespace App\Libraries;

use Config\Database;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * TenantFilter
 *
 * Helper central de la capa multi-tenant por empresa consultora.
 * Lee de sesion el id_empresa_consultora y el flag is_superadmin.
 *
 * Ver: docs/MULTI_TENANT_EMPRESA_CONSULTORA/01_ARQUITECTURA.md
 */
class TenantFilter
{
    /**
     * id_empresa_consultora del usuario en sesion (null si no aplica)
     */
    public static function getEmpresaId(): ?int
    {
        $session = session();
        $id = $session->get('id_empresa_consultora');
        return $id !== null ? (int)$id : null;
    }

    /**
     * Usuario es superadmin (atraviesa todos los filtros)
     */
    public static function isSuperAdmin(): bool
    {
        return (bool)session()->get('is_superadmin');
    }

    /**
     * Resuelve la empresa consultora y estado a partir del registro de tbl_usuarios.
     * Devuelve array con id_empresa_consultora, razon_social, estado_empresa, is_superadmin.
     * Retorna null si no se pudo resolver (usuario superadmin sin empresa cae con id=null).
     */
    public static function resolverEmpresaDesdeUsuario(array $user): ?array
    {
        $tipo      = $user['tipo_usuario'] ?? null;
        $idEntidad = $user['id_entidad'] ?? null;
        $db        = Database::connect();

        $isSuperAdmin = ($tipo === 'superadmin');

        if ($isSuperAdmin) {
            // Superadmin: si tiene id_entidad consultor, se resuelve su empresa; sino, null.
            $empresa = null;
            if ($idEntidad) {
                $empresa = $db->table('tbl_consultor c')
                    ->select('e.id_empresa_consultora, e.razon_social, e.estado')
                    ->join('tbl_empresa_consultora e', 'e.id_empresa_consultora = c.id_empresa_consultora', 'left')
                    ->where('c.id_consultor', $idEntidad)
                    ->get()->getRowArray();
            }
            return [
                'id_empresa_consultora' => $empresa['id_empresa_consultora'] ?? null,
                'razon_social'          => $empresa['razon_social'] ?? null,
                'estado_empresa'        => $empresa['estado'] ?? null,
                'is_superadmin'         => true,
            ];
        }

        if (in_array($tipo, ['admin', 'consultant'], true)) {
            // Resolver via tbl_consultor
            $row = $db->table('tbl_consultor c')
                ->select('e.id_empresa_consultora, e.razon_social, e.estado')
                ->join('tbl_empresa_consultora e', 'e.id_empresa_consultora = c.id_empresa_consultora', 'left')
                ->where('c.id_consultor', $idEntidad)
                ->get()->getRowArray();
            if (!$row) return null;
            return [
                'id_empresa_consultora' => $row['id_empresa_consultora'] !== null ? (int)$row['id_empresa_consultora'] : null,
                'razon_social'          => $row['razon_social'] ?? null,
                'estado_empresa'        => $row['estado'] ?? null,
                'is_superadmin'         => false,
            ];
        }

        if (in_array($tipo, ['client', 'miembro'], true)) {
            // Resolver via tbl_clientes.id_consultor -> tbl_consultor.id_empresa_consultora
            $row = $db->table('tbl_clientes cli')
                ->select('e.id_empresa_consultora, e.razon_social, e.estado')
                ->join('tbl_consultor c', 'c.id_consultor = cli.id_consultor', 'left')
                ->join('tbl_empresa_consultora e', 'e.id_empresa_consultora = c.id_empresa_consultora', 'left')
                ->where('cli.id_cliente', $idEntidad)
                ->get()->getRowArray();
            if (!$row) return null;
            return [
                'id_empresa_consultora' => $row['id_empresa_consultora'] !== null ? (int)$row['id_empresa_consultora'] : null,
                'razon_social'          => $row['razon_social'] ?? null,
                'estado_empresa'        => $row['estado'] ?? null,
                'is_superadmin'         => false,
            ];
        }

        return null;
    }

    /**
     * Verifica si un consultor pertenece a la empresa del usuario en sesion.
     * Superadmin siempre pasa.
     */
    public static function consultorEnMiEmpresa(int $idConsultor): bool
    {
        if (self::isSuperAdmin()) return true;

        $empresaId = self::getEmpresaId();
        if ($empresaId === null) return false;

        $db = Database::connect();
        $row = $db->table('tbl_consultor')
            ->where('id_consultor', $idConsultor)
            ->where('id_empresa_consultora', $empresaId)
            ->get()->getRowArray();
        return !empty($row);
    }

    /**
     * Verifica si un cliente pertenece a la empresa del usuario en sesion.
     * Superadmin siempre pasa.
     */
    public static function clienteEnMiEmpresa(int $idCliente): bool
    {
        if (self::isSuperAdmin()) return true;

        $empresaId = self::getEmpresaId();
        if ($empresaId === null) return false;

        $db = Database::connect();
        $row = $db->table('tbl_clientes cli')
            ->select('c.id_empresa_consultora')
            ->join('tbl_consultor c', 'c.id_consultor = cli.id_consultor', 'left')
            ->where('cli.id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$row) return false;
        return (int)($row['id_empresa_consultora'] ?? 0) === $empresaId;
    }

    /**
     * Lanza PageNotFoundException si el cliente no pertenece a la empresa del usuario.
     * Usado por TenantGuardFilter. 404 en vez de 403 para no leak de info.
     */
    public static function assertClientBelongsToTenant(int $idCliente): void
    {
        if (!self::clienteEnMiEmpresa($idCliente)) {
            log_message('warning', 'TenantFilter::assertClientBelongsToTenant - denegado id_cliente=' . $idCliente
                . ' user_id=' . (session()->get('id_usuario') ?? 'null')
                . ' empresa=' . (self::getEmpresaId() ?? 'null'));
            throw PageNotFoundException::forPageNotFound('Recurso no encontrado.');
        }
    }

    /**
     * Aplica filtro de empresa a un query builder sobre tbl_clientes.
     * El builder debe estar apuntando a tbl_clientes (o con alias con id_consultor accesible).
     * No hace nada si el usuario es superadmin.
     */
    public static function applyToClientQuery(BaseBuilder $builder, string $aliasClientes = 'tbl_clientes'): BaseBuilder
    {
        if (self::isSuperAdmin()) return $builder;

        $empresaId = self::getEmpresaId();
        if ($empresaId === null) {
            // Sin empresa -> no ve nada
            $builder->where('1', 0, false);
            return $builder;
        }

        // WHERE id_consultor IN (SELECT id_consultor FROM tbl_consultor WHERE id_empresa_consultora = ?)
        $builder->whereIn(
            $aliasClientes . '.id_consultor',
            function ($sub) use ($empresaId) {
                return $sub->select('id_consultor')
                    ->from('tbl_consultor')
                    ->where('id_empresa_consultora', $empresaId);
            }
        );
        return $builder;
    }

    /**
     * Devuelve array de id_cliente que pertenecen a la empresa del usuario en sesion.
     * Cachea el resultado en sesion para no repetir la query en cada request.
     * Superadmin o CLI retornan null (= sin filtro).
     */
    public static function getMyClientIds(): ?array
    {
        if (is_cli()) return null;
        $session = session();
        if (!$session->get('isLoggedIn')) return null;
        if ($session->get('is_superadmin')) return null;

        $empresaId = self::getEmpresaId();
        if ($empresaId === null) return [];

        // Cache en memoria de request (evita repetir query)
        static $cache = [];
        if (isset($cache[$empresaId])) return $cache[$empresaId];

        $db = Database::connect();
        $rows = $db->query(
            "SELECT cli.id_cliente
             FROM tbl_clientes cli
             INNER JOIN tbl_consultor c ON c.id_consultor = cli.id_consultor
             WHERE c.id_empresa_consultora = ?",
            [$empresaId]
        )->getResultArray();

        $ids = array_map('intval', array_column($rows, 'id_cliente'));
        $cache[$empresaId] = $ids;
        return $ids;
    }

    /**
     * Aplica filtro de empresa a un query builder sobre tbl_usuarios.
     * Un usuario pertenece a la empresa si:
     *  - es admin/consultant/superadmin y su id_entidad es un consultor de la empresa, O
     *  - es client/miembro y su id_entidad es un cliente cuyo consultor pertenece a la empresa.
     */
    public static function applyToUserQuery(BaseBuilder $builder, string $aliasUsuarios = 'tbl_usuarios'): BaseBuilder
    {
        if (self::isSuperAdmin()) return $builder;

        $empresaId = self::getEmpresaId();
        if ($empresaId === null) {
            $builder->where('1', 0, false);
            return $builder;
        }

        $empresaIdInt = (int)$empresaId;
        $subConsultores = "(SELECT id_consultor FROM tbl_consultor WHERE id_empresa_consultora = {$empresaIdInt})";
        $subClientes    = "(SELECT id_cliente FROM tbl_clientes WHERE id_consultor IN {$subConsultores})";

        $cond = "(
            ({$aliasUsuarios}.tipo_usuario IN ('admin','consultant','superadmin') AND {$aliasUsuarios}.id_entidad IN {$subConsultores})
            OR
            ({$aliasUsuarios}.tipo_usuario IN ('client','miembro') AND {$aliasUsuarios}.id_entidad IN {$subClientes})
        )";
        $builder->where($cond, null, false);
        return $builder;
    }

    /**
     * Aplica filtro de empresa a un query builder sobre tbl_consultor.
     */
    public static function applyToConsultorQuery(BaseBuilder $builder, string $aliasConsultor = 'tbl_consultor'): BaseBuilder
    {
        if (self::isSuperAdmin()) return $builder;

        $empresaId = self::getEmpresaId();
        if ($empresaId === null) {
            $builder->where('1', 0, false);
            return $builder;
        }

        $builder->where($aliasConsultor . '.id_empresa_consultora', $empresaId);
        return $builder;
    }
}
