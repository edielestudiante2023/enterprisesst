<?php

namespace App\Filters;

use App\Libraries\ContextoResolver;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro que verifica que la sesion tenga un contexto_activo atado.
 *
 * Si el usuario esta logueado pero no ha elegido contexto, lo redirige al selector.
 * Solo aplica a usuarios cuyo flujo usa contextos (client / miembro). Admin /
 * consultant / superadmin pasan derecho — no tienen contextos multiples.
 *
 * Las rutas del selector y de logout estan exentas (configurar en Filters.php).
 */
class ContextoFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('contexto');
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // admin / consultant / superadmin no usan el sistema de contextos.
        // Su 'role' es seteado al login sin pasar por contexto_activo.
        $role = $session->get('role');
        if (in_array($role, ['admin', 'consultant'], true) || $session->get('is_superadmin')) {
            return null;
        }

        // Si el contexto ya esta atado, todo OK.
        if (tieneContextoActivo()) {
            return null;
        }

        // Sesion legacy (pre-deploy): tiene role/user_id pero no contexto_activo.
        // Autocalcular contextos y atar transparentemente para no forzar logout.
        $idUsuario = $session->get('id_usuario');
        if ($idUsuario) {
            $user = (new UserModel())->find($idUsuario);
            if ($user) {
                $contextos = ContextoResolver::getContextosDisponibles($user);
                $session->set('contextos_disponibles', $contextos);
                if (count($contextos) === 1) {
                    atarContextoEnSesion($contextos[0]);
                    return null;
                }
            }
        }

        // Sin contexto y con multiples opciones: redirigir al selector.
        return redirect()->to('/seleccionar-contexto');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
