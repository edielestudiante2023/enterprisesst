<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SessionModel;

class AuthFilter implements FilterInterface
{
    private const TIMEOUT_BY_ROLE = [
        'client'     => 300,   // 5 minutos
        'consultant' => 600,   // 10 minutos
        'admin'      => 900,   // 15 minutos
    ];

    private const DEFAULT_TIMEOUT = 600;

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $session->get('role');
        $lastActivity = $session->get('last_activity');
        $currentTime = time();
        $timeout = self::TIMEOUT_BY_ROLE[$role] ?? self::DEFAULT_TIMEOUT;

        if ($lastActivity && ($currentTime - $lastActivity) > $timeout) {
            $idSesion = $session->get('id_sesion');
            if ($idSesion) {
                $sessionModel = new SessionModel();
                $this->cerrarSesionConDuracion($sessionModel, $idSesion, $lastActivity);
            }
            $session->destroy();
            return redirect()->to('/login')->with('msg', 'Tu sesión ha expirado por inactividad.');
        }

        $session->set('last_activity', $currentTime);
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function cerrarSesionConDuracion(SessionModel $sessionModel, int $idSesion, int $lastActivity): void
    {
        $sesion = $sessionModel->find($idSesion);
        if (!$sesion || $sesion['estado'] !== 'activa') return;

        $inicio = strtotime($sesion['inicio_sesion']);
        $sessionModel->update($idSesion, [
            'fin_sesion' => date('Y-m-d H:i:s', $lastActivity),
            'duracion_segundos' => $lastActivity - $inicio,
            'estado' => 'expirada'
        ]);
    }
}
