<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro dual: acepta autenticación por sesión (cookie) O por API Key (header).
 * - Si hay sesión activa (isLoggedIn) → pasa como auth normal
 * - Si hay header X-API-Key válido → pasa como acceso programático
 * - Si ninguno → rechaza con 401
 */
class AuthOrApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Intentar autenticación por sesión
        $session = session();
        if ($session->get('isLoggedIn')) {
            // Actualizar última actividad (como el AuthFilter normal)
            $session->set('last_activity', time());
            return null;
        }

        // 2. Intentar autenticación por API Key
        $apiKey = $request->getHeaderLine('X-API-Key');
        if (!empty($apiKey)) {
            $validKey = env('APP_API_KEY', '');
            if (!empty($validKey) && hash_equals($validKey, $apiKey)) {
                return null;
            }
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['success' => false, 'error' => 'API Key invalida']);
        }

        // 3. Ninguna autenticación válida
        // Si es petición AJAX/API, devolver JSON
        if ($request->isAJAX() || str_contains($request->getHeaderLine('Accept'), 'application/json')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'error' => 'Autenticacion requerida']);
        }

        // Si es navegador, redirigir al login
        return redirect()->to('/login');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
