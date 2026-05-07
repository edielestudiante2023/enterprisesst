<?php

namespace App\Libraries;

use App\Models\ClientModel;
use App\Models\MiembroComiteModel;

/**
 * Calcula los "contextos" disponibles para un usuario.
 *
 * Un contexto representa una identidad operativa: cliente o miembro de un comite especifico.
 * Una persona puede tener varios contextos si su email aparece en tbl_comite_miembros y/o
 * si tiene tipo_usuario='client'.
 *
 * Estructura de un contexto:
 *   [
 *     'tipo'          => 'cliente'|'miembro',
 *     'id_cliente'    => int,
 *     'id_comite'     => int|null,         // null si tipo='cliente'
 *     'id_tipo_comite'=> int|null,
 *     'codigo_comite' => 'COPASST'|'COCOLAB'|'BRIGADA'|'GENERAL'|null,
 *     'nombre_cliente'=> string,
 *     'nombre_tipo'   => string,           // ej "Comite Paritario..."
 *     'label'         => string,           // texto a mostrar en card
 *     'es_cocolab'    => bool,             // true si es Comite de Convivencia
 *     'rol_comite'    => string|null,      // presidente/secretario/miembro
 *     'email_miembro' => string|null,      // email exacto del registro en tbl_comite_miembros
 *   ]
 */
class ContextoResolver
{
    /**
     * Calcula todos los contextos disponibles para un usuario de tbl_usuarios.
     *
     * @param array $user Registro de tbl_usuarios
     * @return array Lista de contextos disponibles
     */
    public static function getContextosDisponibles(array $user): array
    {
        $contextos = [];
        $tipo = $user['tipo_usuario'] ?? '';
        $email = strtolower(trim($user['email'] ?? ''));

        // Admin/consultant/superadmin no usan el sistema de contextos.
        if (in_array($tipo, ['admin', 'consultant', 'superadmin'], true)) {
            return [];
        }

        // 1) Contexto cliente (si tipo_usuario='client' y tiene id_entidad)
        if ($tipo === 'client' && !empty($user['id_entidad'])) {
            $cliente = (new ClientModel())->find($user['id_entidad']);
            if ($cliente) {
                $contextos[] = [
                    'tipo'           => 'cliente',
                    'id_cliente'     => (int) $user['id_entidad'],
                    'id_comite'      => null,
                    'id_tipo_comite' => null,
                    'codigo_comite'  => null,
                    'nombre_cliente' => $cliente['nombre_cliente'] ?? '',
                    'nombre_tipo'    => 'Cliente',
                    'label'          => 'Cliente: ' . ($cliente['nombre_cliente'] ?? ''),
                    'es_cocolab'     => false,
                    'rol_comite'     => null,
                    'email_miembro'  => null,
                ];
            }
        }

        // 2) Contextos miembro (todas las membresias activas que matcheen el email)
        if ($email !== '') {
            $db = \Config\Database::connect();
            $rows = $db->table('tbl_comite_miembros m')
                ->select('m.id_miembro, m.email, m.id_comite, m.id_cliente, m.rol_comite, m.tipo_miembro,
                          tc.id_tipo as id_tipo_comite, tc.codigo, tc.nombre as nombre_tipo,
                          c.nombre_cliente')
                ->join('tbl_comites com', 'com.id_comite = m.id_comite', 'inner')
                ->join('tbl_tipos_comite tc', 'tc.id_tipo = com.id_tipo', 'inner')
                ->join('tbl_clientes c', 'c.id_cliente = m.id_cliente', 'inner')
                ->where('LOWER(TRIM(m.email))', $email)
                ->where('m.estado', 'activo')
                ->where('com.estado', 'activo')
                ->orderBy('c.nombre_cliente', 'ASC')
                ->orderBy('tc.codigo', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $r) {
                $codigo = $r['codigo'] ?? '';
                $contextos[] = [
                    'tipo'           => 'miembro',
                    'id_cliente'     => (int) $r['id_cliente'],
                    'id_comite'      => (int) $r['id_comite'],
                    'id_tipo_comite' => (int) $r['id_tipo_comite'],
                    'codigo_comite'  => $codigo,
                    'nombre_cliente' => $r['nombre_cliente'] ?? '',
                    'nombre_tipo'    => $r['nombre_tipo'] ?? '',
                    'label'          => self::etiquetaComite($codigo) . ': ' . ($r['nombre_cliente'] ?? ''),
                    'es_cocolab'     => $codigo === 'COCOLAB',
                    'rol_comite'     => $r['rol_comite'] ?? null,
                    'email_miembro'  => $r['email'] ?? null,
                ];
            }
        }

        return $contextos;
    }

    /**
     * Verifica que un contexto especifico (tipo + id) este realmente disponible para el usuario.
     * Se usa al "atar" un contexto desde el selector — nunca confiar en lo que viene del cliente.
     */
    public static function contextoEsValidoParaUsuario(array $user, string $tipo, ?int $idCliente, ?int $idComite): ?array
    {
        $contextos = self::getContextosDisponibles($user);

        foreach ($contextos as $c) {
            if ($c['tipo'] !== $tipo) continue;
            if ((int) $c['id_cliente'] !== (int) $idCliente) continue;
            // Para tipo cliente, id_comite debe ser null
            if ($tipo === 'cliente' && $c['id_comite'] === null && $idComite === null) {
                return $c;
            }
            // Para tipo miembro, id_comite debe coincidir
            if ($tipo === 'miembro' && (int) $c['id_comite'] === (int) $idComite) {
                return $c;
            }
        }

        return null;
    }

    /**
     * Etiqueta humana corta para un codigo de comite.
     */
    public static function etiquetaComite(string $codigo): string
    {
        $map = [
            'COPASST' => 'COPASST',
            'COCOLAB' => 'Comite de Convivencia',
            'BRIGADA' => 'Brigada de Emergencias',
            'GENERAL' => 'Comite General SST',
            'VIGIA'   => 'Vigia SST',
        ];
        return $map[$codigo] ?? $codigo;
    }

    /**
     * Icono Bootstrap Icons asociado a un contexto (para las cards del selector).
     */
    public static function iconoContexto(array $contexto): string
    {
        if ($contexto['tipo'] === 'cliente') return 'bi-building';
        $codigo = $contexto['codigo_comite'] ?? '';
        return [
            'COPASST' => 'bi-shield-check',
            'COCOLAB' => 'bi-shield-lock-fill',
            'BRIGADA' => 'bi-fire',
            'GENERAL' => 'bi-people',
            'VIGIA'   => 'bi-eye',
        ][$codigo] ?? 'bi-people';
    }
}
