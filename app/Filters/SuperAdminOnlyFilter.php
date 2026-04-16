<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\TenantFilter;

class SuperAdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (is_cli()) return null;

        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (!TenantFilter::isSuperAdmin()) {
            return redirect()->to('/admin/dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
