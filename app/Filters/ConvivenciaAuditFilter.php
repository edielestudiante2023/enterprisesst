<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de auditoria + timeout corto para sesiones de Comite de Convivencia.
 *
 * - Si el contexto activo es miembro de COCOLAB:
 *   1) Aplica timeout de inactividad de 10 minutos (vs 24h por defecto)
 *   2) Registra el request en tbl_auditoria_convivencia
 *
 * - Para otros contextos: no hace nada (passthrough).
 *
 * Cumple obligacion de trazabilidad y limitacion de exposicion de datos
 * confidenciales segun Ley 1010 / Res 3461.
 */
class ConvivenciaAuditFilter implements FilterInterface
{
    private const TIMEOUT_COCOLAB_SEGUNDOS = 600; // 10 minutos

    public function before(RequestInterface $request, $arguments = null)
    {
        helper('contexto');
        $session = session();

        if (!$session->get('isLoggedIn') || !esContextoCocolab()) {
            return null;
        }

        // Timeout corto: si la inactividad supera 10 min, cerrar sesion.
        $lastActivity = $session->get('last_activity');
        if ($lastActivity && (time() - $lastActivity) > self::TIMEOUT_COCOLAB_SEGUNDOS) {
            $session->destroy();
            return redirect()->to('/login')->with('msg', 'Sesion confidencial cerrada por inactividad (10 min).');
        }
        $session->set('last_activity', time());

        // Auditoria: registrar el acceso.
        try {
            $ctx = contextoActual();
            $db = \Config\Database::connect();
            $db->table('tbl_auditoria_convivencia')->insert([
                'id_usuario'     => (int) $session->get('id_usuario'),
                'email'          => (string) ($ctx['email_miembro'] ?? $session->get('email_miembro') ?? ''),
                'nombre_usuario' => (string) ($session->get('nombre_completo') ?? ''),
                'id_cliente'     => (int) ($ctx['id_cliente'] ?? 0),
                'id_comite'      => $ctx['id_comite'] ?? null,
                'metodo_http'    => $request->getMethod(),
                'ruta'           => $request->getUri()->getPath(),
                'parametros'     => $this->parametrosResumidos($request),
                'ip'             => $request->getIPAddress(),
                'user_agent'     => substr((string) $request->getUserAgent()->getAgentString(), 0, 500),
                'accion_resumen' => $this->resumirAccion($request),
            ]);
        } catch (\Throwable $e) {
            // No bloquear el request si falla la auditoria, solo logear.
            log_message('error', '[ConvivenciaAuditFilter] insert fail: ' . $e->getMessage());
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function parametrosResumidos(RequestInterface $request): string
    {
        $get  = $request->getGet() ?? [];
        $post = $request->getPost() ?? [];
        // No registrar passwords ni tokens
        foreach (['password', 'pass', 'token', 'token_recuperacion'] as $k) {
            unset($get[$k], $post[$k]);
        }
        $resumen = [];
        if (!empty($get))  $resumen['get'] = $get;
        if (!empty($post)) $resumen['post'] = $post;
        $json = json_encode($resumen, JSON_UNESCAPED_UNICODE);
        return substr($json !== false ? $json : '', 0, 2000);
    }

    private function resumirAccion(RequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $metodo = $request->getMethod();

        if (strpos($path, '/dashboard') !== false)        return 'Ver dashboard';
        if (strpos($path, '/comite/') !== false)          return 'Ver comite';
        if (strpos($path, '/acta/') !== false || strpos($path, '/actas') !== false) return 'Ver acta';
        if (strpos($path, '/compromiso') !== false)       return 'Gestion de compromisos';
        if (strpos($path, '/firma') !== false)            return 'Firma de acta';
        if ($metodo === 'POST')                           return 'Modificacion';
        return 'Acceso';
    }
}
