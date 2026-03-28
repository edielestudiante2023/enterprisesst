<?php

/**
 * Helper para resolver el ID de cliente efectivo.
 *
 * Cuando un consultant/admin usa "vista-cliente", el user_id en sesión
 * corresponde al consultor, no al cliente. Este helper permite que los
 * controladores de documentos usen el ID del cliente correcto.
 *
 * Prioridad:
 * 1. $urlClientId (parámetro de URL) si el usuario es consultant/admin
 * 2. session('vista_cliente_id') si el usuario es consultant/admin
 * 3. session('user_id') (comportamiento por defecto)
 */

if (!function_exists('getEffectiveClientId')) {
    /**
     * Retorna el ID del cliente efectivo según el contexto.
     *
     * @param int|string|null $urlClientId ID del cliente desde la URL (opcional)
     * @return int|null ID del cliente efectivo
     */
    function getEffectiveClientId($urlClientId = null)
    {
        $session = session();
        $role = $session->get('role');

        // Consultant/admin puede ver datos de cualquier cliente
        if (in_array($role, ['consultant', 'admin'])) {
            // Prioridad 1: parámetro de URL
            if ($urlClientId) {
                return (int) $urlClientId;
            }

            // Prioridad 2: ID de vista-cliente en sesión
            $vistaClienteId = $session->get('vista_cliente_id');
            if ($vistaClienteId) {
                return (int) $vistaClienteId;
            }
        }

        // Default: user_id de la sesión
        return $session->get('user_id');
    }
}
