<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    public function loginPost()
    {
        $session = session();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $role = $this->request->getVar('role'); // 'client' o 'consultant'

        if ($role === 'client') {
            $clientModel = new ClientModel();
            $client = $clientModel->where('correo_cliente', $username)->first();

            if ($client && password_verify($password, $client['password'])) {
                // Verificar el estado del cliente
                if ($client['estado'] !== 'activo') {
                    // Si el estado no es 'Activo', redirigir a la vista específica
                    // Asegúrate de haber creado la vista en app/Views/client/suspended.php 
                    // o la que corresponda.
                    return view('client/suspended');
                }

                // Si el cliente está activo, continuar con el inicio de sesión normal
                $session->set([
                    'user_id'   => $client['id_cliente'],
                    'role'      => 'client',
                    'isLoggedIn' => true
                ]);
                return redirect()->to('/dashboard');
            }
        } elseif ($role === 'consultant') {
            $consultantModel = new ConsultantModel();
            $consultant = $consultantModel->where('correo_consultor', $username)->first();
            if ($consultant && password_verify($password, $consultant['password'])) {
                if ($consultant['rol'] === 'admin') {
                    $session->set([
                        'user_id'   => $consultant['id_consultor'],
                        'role'      => $consultant['rol'],
                        'isLoggedIn' => true
                    ]);
                    return redirect()->to('/admindashboard');
                } elseif ($consultant['rol'] === 'consultant') {
                    $session->set([
                        'user_id'   => $consultant['id_consultor'],
                        'role'      => $consultant['rol'],
                        'isLoggedIn' => true
                    ]);
                    return redirect()->to('/dashboardconsultant');
                }
            }
        }

        $session->setFlashdata('msg', 'Correo electrónico o contraseña incorrectos');
        return redirect()->to('/login');
    }

 



    public function logout()
{
    // Destruir la sesión por completo
    $session = session();
    $session->destroy(); // Esto eliminará la sesión en todas las ventanas

    // Redirigir al usuario a la página de inicio de sesión o página principal
    return redirect()->to('/login');
}
}
