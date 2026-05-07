<?php

/**
 * Helper para acceder al contexto activo de la sesion.
 *
 * El contexto activo determina si la persona esta operando como cliente o como miembro
 * de un comite especifico. Cada vez que el usuario "ata" un contexto desde el selector,
 * la sesion guarda en 'contexto_activo' un array con los datos de ese contexto, y
 * tambien se sincronizan las claves legacy ('role', 'user_id', 'email_miembro')
 * para que filtros y controllers existentes sigan funcionando sin cambios.
 */

if (!function_exists('contextoActual')) {
    /**
     * Devuelve el contexto activo en sesion, o null si no hay.
     */
    function contextoActual(): ?array
    {
        $c = session()->get('contexto_activo');
        return is_array($c) ? $c : null;
    }
}

if (!function_exists('tieneContextoActivo')) {
    function tieneContextoActivo(): bool
    {
        return contextoActual() !== null;
    }
}

if (!function_exists('esContextoCliente')) {
    function esContextoCliente(): bool
    {
        $c = contextoActual();
        return $c !== null && ($c['tipo'] ?? '') === 'cliente';
    }
}

if (!function_exists('esContextoMiembro')) {
    function esContextoMiembro(): bool
    {
        $c = contextoActual();
        return $c !== null && ($c['tipo'] ?? '') === 'miembro';
    }
}

if (!function_exists('esContextoCocolab')) {
    /**
     * True si el contexto activo es miembro de Comite de Convivencia (modo confidencial).
     */
    function esContextoCocolab(): bool
    {
        $c = contextoActual();
        return $c !== null
            && ($c['tipo'] ?? '') === 'miembro'
            && ($c['codigo_comite'] ?? '') === 'COCOLAB';
    }
}

if (!function_exists('idClienteContexto')) {
    function idClienteContexto(): ?int
    {
        $c = contextoActual();
        return $c !== null ? (int) ($c['id_cliente'] ?? 0) : null;
    }
}

if (!function_exists('idComiteContexto')) {
    function idComiteContexto(): ?int
    {
        $c = contextoActual();
        if ($c === null) return null;
        $id = $c['id_comite'] ?? null;
        return $id !== null ? (int) $id : null;
    }
}

if (!function_exists('codigoComiteContexto')) {
    function codigoComiteContexto(): ?string
    {
        $c = contextoActual();
        return $c !== null ? ($c['codigo_comite'] ?? null) : null;
    }
}

if (!function_exists('labelContexto')) {
    function labelContexto(): string
    {
        $c = contextoActual();
        return $c !== null ? ($c['label'] ?? '') : '';
    }
}

if (!function_exists('atarContextoEnSesion')) {
    /**
     * Persiste un contexto en la sesion y sincroniza claves legacy para compat.
     * Esto centraliza el "set" para evitar inconsistencias.
     */
    function atarContextoEnSesion(array $contexto): void
    {
        $session = session();
        $session->set('contexto_activo', $contexto);

        // Sincronizar claves legacy segun el tipo
        if (($contexto['tipo'] ?? '') === 'cliente') {
            $session->set('role', 'client');
            $session->set('user_id', (int) $contexto['id_cliente']);
            $session->remove('email_miembro');
        } elseif (($contexto['tipo'] ?? '') === 'miembro') {
            $session->set('role', 'miembro');
            $session->set('user_id', (int) $contexto['id_cliente']);
            $session->set('email_miembro', $contexto['email_miembro'] ?? null);
        }
    }
}

if (!function_exists('contextosDisponiblesEnSesion')) {
    /**
     * Lista de contextos disponibles guardada en sesion (set durante login).
     */
    function contextosDisponiblesEnSesion(): array
    {
        $arr = session()->get('contextos_disponibles');
        return is_array($arr) ? $arr : [];
    }
}

if (!function_exists('tieneMultiplesContextos')) {
    /**
     * True si el usuario tiene 2+ contextos disponibles (mostrar boton "Cambiar contexto").
     */
    function tieneMultiplesContextos(): bool
    {
        return count(contextosDisponiblesEnSesion()) >= 2;
    }
}

if (!function_exists('limpiarContextoEnSesion')) {
    /**
     * Quita el contexto activo y las claves legacy para forzar re-seleccion.
     */
    function limpiarContextoEnSesion(): void
    {
        $session = session();
        $session->remove('contexto_activo');
        $session->remove('role');
        $session->remove('user_id');
        $session->remove('email_miembro');
    }
}
