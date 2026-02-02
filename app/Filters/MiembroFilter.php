<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de autorización para usuarios tipo 'miembro'
 * Solo permite acceso a rutas de /miembro/* si el usuario tiene role='miembro'
 */
class MiembroFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Verificar si está logueado
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar que sea usuario tipo miembro
        $role = $session->get('role');

        if ($role !== 'miembro') {
            // Si no es miembro, redirigir a su dashboard correspondiente
            switch ($role) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'consultant':
                    return redirect()->to('/consultor/dashboard');
                case 'client':
                    return redirect()->to('/dashboard');
                default:
                    return redirect()->to('/login');
            }
        }

        // Actualizar última actividad
        $session->set('last_activity', time());

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción después
    }
}
