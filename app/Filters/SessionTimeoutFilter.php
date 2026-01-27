<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SessionModel;

class SessionTimeoutFilter implements FilterInterface
{
    /**
     * Timeouts de inactividad por tipo de usuario (en segundos)
     */
    private const TIMEOUTS = [
        'client'     => 300,  // 5 minutos
        'consultant' => 1800, // 30 minutos
        'admin'      => 900,  // 15 minutos
    ];

    /**
     * Ejecutar antes de la petición
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Asegurar zona horaria de Colombia
        date_default_timezone_set('America/Bogota');

        $session = session();

        // Si no hay sesión activa, no hacer nada
        if (!$session->get('isLoggedIn')) {
            return;
        }

        $role = $session->get('role');
        $lastActivity = $session->get('last_activity');

        // Si no hay última actividad registrada, establecerla
        if (!$lastActivity) {
            $session->set('last_activity', time());
            return;
        }

        // Obtener timeout según el rol
        $timeout = self::TIMEOUTS[$role] ?? self::TIMEOUTS['client'];

        // Verificar si ha pasado el tiempo de inactividad
        $inactiveTime = time() - $lastActivity;

        if ($inactiveTime > $timeout) {
            // Cerrar sesión de tracking en la base de datos
            $idSesion = $session->get('id_sesion');
            if ($idSesion) {
                $sessionModel = new SessionModel();
                $sessionModel->cerrarSesionPorTimeout($idSesion, $lastActivity);
            }

            // Destruir la sesión
            $session->destroy();

            // Redirigir al login con mensaje
            return redirect()->to('/login')->with('msg', 'Tu sesión ha expirado por inactividad.');
        }

        // Actualizar última actividad
        $session->set('last_activity', time());
    }

    /**
     * Ejecutar después de la petición
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita acción después
    }
}
