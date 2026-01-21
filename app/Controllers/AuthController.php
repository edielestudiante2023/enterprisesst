<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\UserModel;
use App\Models\SessionModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    /**
     * Login usando la nueva tabla tbl_usuarios
     * Detecta automáticamente el tipo de usuario (sin selector de rol)
     */
    public function loginPost()
    {
        $session = session();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $userModel = new UserModel();

        // Buscar usuario en la nueva tabla
        $user = $userModel->findByEmail($username);

        if ($user && password_verify($password, $user['password'])) {
            $tipoUsuario = $user['tipo_usuario'];

            // Verificar estado del usuario
            if ($user['estado'] !== 'activo') {
                if ($user['estado'] === 'bloqueado') {
                    return view('auth/blocked');
                }
                return view('client/suspended');
            }

            // Resetear intentos fallidos y actualizar último login
            $userModel->resetFailedAttempts($user['id_usuario']);

            // Registrar inicio de sesión
            $sessionModel = new SessionModel();
            $idSesion = $sessionModel->iniciarSesion(
                $user['id_usuario'],
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );

            // Configurar sesión según tipo de usuario (detección automática)
            if ($tipoUsuario === 'client') {
                $session->set([
                    'user_id'    => $user['id_entidad'], // id_cliente para compatibilidad
                    'id_usuario' => $user['id_usuario'],
                    'id_sesion'  => $idSesion, // ID de la sesión para tracking
                    'role'       => 'client',
                    'isLoggedIn' => true
                ]);
                return redirect()->to('/dashboard');

            } elseif ($tipoUsuario === 'admin') {
                $session->set([
                    'user_id'    => $user['id_entidad'], // id_consultor para compatibilidad
                    'id_usuario' => $user['id_usuario'],
                    'id_sesion'  => $idSesion, // ID de la sesión para tracking
                    'role'       => 'admin',
                    'isLoggedIn' => true
                ]);
                return redirect()->to('/admin/dashboard');

            } elseif ($tipoUsuario === 'consultant') {
                $session->set([
                    'user_id'    => $user['id_entidad'], // id_consultor para compatibilidad
                    'id_usuario' => $user['id_usuario'],
                    'id_sesion'  => $idSesion, // ID de la sesión para tracking
                    'role'       => 'consultant',
                    'isLoggedIn' => true
                ]);
                return redirect()->to('/consultor/dashboard');
            }
        }

        // Registrar intento fallido si el usuario existe
        if ($user) {
            $userModel->registerFailedAttempt($user['id_usuario']);
        }

        $session->setFlashdata('msg', 'Correo electrónico o contraseña incorrectos');
        return redirect()->to('/login');
    }

    /**
     * Vista para usuarios bloqueados
     */
    public function blocked()
    {
        return view('auth/blocked');
    }

    /**
     * Vista para solicitar recuperación de contraseña
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Procesar solicitud de recuperación de contraseña
     */
    public function forgotPasswordPost()
    {
        $session = session();
        $email = $this->request->getVar('email');

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            // No revelar si el email existe o no (seguridad)
            $session->setFlashdata('msg_success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
            return redirect()->to('/forgot-password');
        }

        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token en la base de datos
        $userModel->update($user['id_usuario'], [
            'token_recuperacion' => $token,
            'token_expira' => $expira
        ]);

        // Enviar email con SendGrid
        $resetLink = base_url('/reset-password/' . $token);
        $emailSent = $this->sendPasswordResetEmail($user['email'], $user['nombre_completo'], $resetLink);

        if ($emailSent) {
            $session->setFlashdata('msg_success', 'Se ha enviado un enlace de recuperación a tu correo electrónico.');
        } else {
            $session->setFlashdata('msg', 'Error al enviar el correo. Intenta de nuevo más tarde.');
        }

        return redirect()->to('/forgot-password');
    }

    /**
     * Enviar email de recuperación con SendGrid
     */
    private function sendPasswordResetEmail(string $toEmail, string $nombre, string $resetLink): bool
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Enterprisesst - Cycloid Talent");
        $email->setSubject("Recuperación de Contraseña - Enterprisesst");
        $email->addTo($toEmail);

        $emailContent = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #1c2437;'>Enterprisesst</h1>
                <p style='color: #bd9751; font-size: 14px;'>Propiedad Horizontal</p>
            </div>

            <h2 style='color: #1c2437;'>Hola {$nombre},</h2>

            <p style='color: #333; line-height: 1.6;'>
                Recibimos una solicitud para restablecer la contraseña de tu cuenta en Enterprisesst.
            </p>

            <p style='color: #333; line-height: 1.6;'>
                Haz clic en el siguiente botón para crear una nueva contraseña:
            </p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}'
                   style='display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #1c2437, #2c3e50);
                          color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold;
                          box-shadow: 0 4px 15px rgba(28, 36, 55, 0.3);'>
                    Restablecer Contraseña
                </a>
            </div>

            <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                Este enlace expirará en <strong>1 hora</strong>. Si no solicitaste restablecer tu contraseña,
                puedes ignorar este correo.
            </p>

            <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:
            </p>
            <p style='color: #007bff; font-size: 12px; word-break: break-all;'>
                {$resetLink}
            </p>

            <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;'>

            <p style='color: #999; font-size: 12px; text-align: center;'>
                Este correo fue enviado por Cycloid Talent SAS<br>
                <a href='https://cycloidtalent.com' style='color: #bd9751;'>www.cycloidtalent.com</a>
            </p>
        </div>
        ";

        $email->addContent("text/html", $emailContent);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        try {
            $response = $sendgrid->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email de recuperación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vista para restablecer contraseña (con token)
     */
    public function resetPassword($token)
    {
        $userModel = new UserModel();

        // Buscar usuario con ese token
        $user = $userModel->where('token_recuperacion', $token)
                          ->where('token_expira >', date('Y-m-d H:i:s'))
                          ->first();

        if (!$user) {
            $session = session();
            $session->setFlashdata('msg', 'El enlace de recuperación es inválido o ha expirado.');
            return redirect()->to('/forgot-password');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    /**
     * Procesar nueva contraseña
     */
    public function resetPasswordPost()
    {
        $session = session();
        $token = $this->request->getVar('token');
        $password = $this->request->getVar('password');
        $confirmPassword = $this->request->getVar('confirm_password');

        // Validar contraseñas
        if ($password !== $confirmPassword) {
            $session->setFlashdata('msg', 'Las contraseñas no coinciden.');
            return redirect()->to('/reset-password/' . $token);
        }

        if (strlen($password) < 6) {
            $session->setFlashdata('msg', 'La contraseña debe tener al menos 6 caracteres.');
            return redirect()->to('/reset-password/' . $token);
        }

        $userModel = new UserModel();

        // Buscar usuario con ese token
        $user = $userModel->where('token_recuperacion', $token)
                          ->where('token_expira >', date('Y-m-d H:i:s'))
                          ->first();

        if (!$user) {
            $session->setFlashdata('msg', 'El enlace de recuperación es inválido o ha expirado.');
            return redirect()->to('/forgot-password');
        }

        // Actualizar contraseña y limpiar token
        $userModel->update($user['id_usuario'], [
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'token_recuperacion' => null,
            'token_expira' => null,
            'intentos_fallidos' => 0,
            'estado' => 'activo' // Desbloquear si estaba bloqueado
        ]);

        $session->setFlashdata('msg_success', 'Tu contraseña ha sido actualizada exitosamente. Ya puedes iniciar sesión.');
        return redirect()->to('/login');
    }



    public function logout()
    {
        $session = session();

        // Cerrar la sesión de tracking antes de destruir
        $idSesion = $session->get('id_sesion');
        if ($idSesion) {
            $sessionModel = new SessionModel();
            $sessionModel->cerrarSesion($idSesion);
        }

        // Destruir la sesión por completo
        $session->destroy(); // Esto eliminará la sesión en todas las ventanas

        // Redirigir al usuario a la página de inicio de sesión o página principal
        return redirect()->to('/login');
    }
}
