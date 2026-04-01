<?php

use App\Libraries\AccessLibrary;

/**
 * Helper wrapper para AccessLibrary.
 * Carga automáticamente en autoload o via helper('access_library').
 */

if (!function_exists('getAccessesByStandard')) {
    /**
     * Retorna IDs de accesos disponibles según el estándar del cliente.
     */
    function getAccessesByStandard(string $standard = 'Mensual'): array
    {
        return AccessLibrary::getAccessesByStandard($standard);
    }
}

if (!function_exists('get_accesses_by_standard')) {
    /**
     * Retorna accesos completos (id_acceso, nombre, dimension) según el estándar.
     */
    function get_accesses_by_standard(string $standard = 'Mensual'): array
    {
        $all = AccessLibrary::getAll();
        $result = [];
        foreach ($all as $id => $access) {
            $result[] = [
                'id_acceso' => $id,
                'nombre'    => $access['name'],
                'dimension' => $access['dimension'],
            ];
        }
        return $result;
    }
}

if (!function_exists('getAccess')) {
    /**
     * Obtiene info de un acceso por su ID.
     */
    function getAccess(int $idAcceso): ?array
    {
        return AccessLibrary::getAccess($idAcceso);
    }
}

if (!function_exists('getAllAccesses')) {
    /**
     * Retorna todos los accesos disponibles.
     */
    function getAllAccesses(): array
    {
        return AccessLibrary::getAll();
    }
}

if (!function_exists('getAccessesGroupedByDimension')) {
    /**
     * Retorna accesos agrupados por dimensión PHVA.
     */
    function getAccessesGroupedByDimension(): array
    {
        return AccessLibrary::getGroupedByDimension();
    }
}
