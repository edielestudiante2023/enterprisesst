<?php

namespace App\Controllers;

use App\Libraries\ContextoResolver;
use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * Controlador para el selector de contextos post-login.
 *
 * Una persona puede ser cliente y/o miembro de uno o varios comites a la vez.
 * Despues del login, si tiene 2+ contextos disponibles, AuthController la redirige aqui;
 * si tiene 1 contexto, AuthController la "ata" directamente y la lleva a su dashboard.
 *
 * Aqui el usuario:
 *   - GET /seleccionar-contexto                 -> ve cards con sus contextos
 *   - POST /seleccionar-contexto/atar           -> ata uno y redirige al dashboard
 *   - POST /salir-contexto                      -> limpia contexto y vuelve al selector
 */
class ContextoSelectorController extends Controller
{
    /**
     * Pantalla de selector. Recalcula contextos en cada visita (no confiar en cache).
     */
    public function index()
    {
        helper('contexto');
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idUsuario = $session->get('id_usuario');
        if (!$idUsuario) {
            return redirect()->to('/login')->with('msg', 'Sesion invalida');
        }

        $user = (new UserModel())->find($idUsuario);
        if (!$user) {
            $session->destroy();
            return redirect()->to('/login')->with('msg', 'Usuario no encontrado');
        }

        $contextos = ContextoResolver::getContextosDisponibles($user);

        // Si solo hay 1 contexto, atarlo y redirigir directo (no tiene sentido el selector).
        if (count($contextos) === 1) {
            return $this->atarYRedirigir($contextos[0]);
        }

        if (count($contextos) === 0) {
            $session->setFlashdata('msg', 'Tu usuario no tiene contextos disponibles. Contacta al administrador.');
            return redirect()->to('/login');
        }

        return view('auth/seleccionar_contexto', [
            'user'      => $user,
            'contextos' => $contextos,
        ]);
    }

    /**
     * Procesa el "atar" desde el selector.
     * Recibe POST con tipo, id_cliente, id_comite (este ultimo opcional para tipo cliente).
     */
    public function atar()
    {
        helper('contexto');
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idUsuario = $session->get('id_usuario');
        if (!$idUsuario) {
            return redirect()->to('/login')->with('msg', 'Sesion invalida');
        }

        $user = (new UserModel())->find($idUsuario);
        if (!$user) {
            $session->destroy();
            return redirect()->to('/login');
        }

        $tipo       = (string) $this->request->getPost('tipo');
        $idCliente  = $this->request->getPost('id_cliente');
        $idComiteIn = $this->request->getPost('id_comite');
        $idCliente  = $idCliente !== null && $idCliente !== '' ? (int) $idCliente : null;
        $idComite   = $idComiteIn !== null && $idComiteIn !== '' ? (int) $idComiteIn : null;

        // Verificar que ese contexto esta efectivamente disponible para este usuario.
        $ctx = ContextoResolver::contextoEsValidoParaUsuario($user, $tipo, $idCliente, $idComite);
        if ($ctx === null) {
            $session->setFlashdata('msg', 'Contexto no autorizado para tu usuario.');
            return redirect()->to('/seleccionar-contexto');
        }

        return $this->atarYRedirigir($ctx);
    }

    /**
     * Limpia el contexto activo y vuelve al selector.
     */
    public function salir()
    {
        helper('contexto');
        limpiarContextoEnSesion();
        return redirect()->to('/seleccionar-contexto');
    }

    /**
     * Helper interno: ata el contexto y redirige al dashboard correspondiente.
     */
    private function atarYRedirigir(array $contexto)
    {
        helper('contexto');
        atarContextoEnSesion($contexto);

        if ($contexto['tipo'] === 'cliente') {
            return redirect()->to('/dashboard');
        }
        // miembro
        return redirect()->to('/miembro/dashboard');
    }
}
