<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\TenantFilter;

/**
 * TenantGuardFilter
 *
 * Guard central multi-tenant: si la request trae un id_cliente (via POST o GET),
 * valida que pertenezca a la empresa consultora del usuario en sesion.
 *
 * Por seguridad, en Fase 2 SOLO inspecciona POST/GET — no parsea segmentos de URI,
 * ya que distintos controllers usan posiciones distintas. Cobertura via URI se
 * agregara en Fase 3 con reglas especificas por ruta.
 *
 * Superadmin atraviesa sin validar.
 *
 * Ver: docs/MULTI_TENANT_EMPRESA_CONSULTORA/01_ARQUITECTURA.md
 */
class TenantGuardFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Solo HTTP; no correr en CLI
        if (is_cli()) return null;

        $session = session();
        if (!$session->get('isLoggedIn')) return null; // AuthFilter se encarga del no-login

        // Superadmin: bypass total
        if (TenantFilter::isSuperAdmin()) return null;

        // Si el usuario no tiene empresa resuelta, no validamos aqui (no romper login legacy)
        if (TenantFilter::getEmpresaId() === null) return null;

        // Buscar id_cliente en POST y GET
        $idCliente = $request->getPost('id_cliente');
        if ($idCliente === null) {
            $idCliente = $request->getGet('id_cliente');
        }

        if ($idCliente !== null && $idCliente !== '' && ctype_digit((string)$idCliente)) {
            TenantFilter::assertClientBelongsToTenant((int)$idCliente);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
