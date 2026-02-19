<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de autorizacion para rutas client/*
 * Solo permite acceso a roles: client, admin, consultant
 */
class ClientFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $session->get('role');

        if (!in_array($role, ['client', 'admin', 'consultant'])) {
            switch ($role) {
                case 'miembro':
                    return redirect()->to('/miembro/dashboard');
                default:
                    return redirect()->to('/login');
            }
        }

        $session->set('last_activity', time());

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
