<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use CodeIgniter\Controller;

class UserController extends Controller
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Lista de usuarios
     */
    public function listUsers()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $users = $this->userModel->findAll();
        $roles = $this->roleModel->findAll();

        $data = [
            'users' => $users,
            'roles' => $roles,
        ];

        return view('admin/users/list_users', $data);
    }

    /**
     * Formulario para agregar usuario
     */
    public function addUser()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'roles'       => $this->roleModel->findAll(),
            'clients'     => $clientModel->findAll(),
            'consultants' => $consultantModel->findAll(),
        ];

        return view('admin/users/add_user', $data);
    }

    /**
     * Procesar formulario de agregar usuario
     */
    public function addUserPost()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $tipoUsuario = $this->request->getVar('tipo_usuario');
        $idEntidad = null;

        // Asignar id_entidad según el tipo de usuario
        if ($tipoUsuario === 'client') {
            $idEntidad = $this->request->getVar('id_cliente');
        } elseif (in_array($tipoUsuario, ['consultant', 'admin'])) {
            $idEntidad = $this->request->getVar('id_consultor');
        }

        $data = [
            'email'           => $this->request->getVar('email'),
            'password'        => $this->request->getVar('password'),
            'nombre_completo' => $this->request->getVar('nombre_completo'),
            'tipo_usuario'    => $tipoUsuario,
            'id_entidad'      => $idEntidad,
            'estado'          => $this->request->getVar('estado') ?? 'activo',
        ];

        // Validar que el email no exista
        if ($this->userModel->findByEmail($data['email'])) {
            return redirect()->back()->with('msg', 'El email ya está registrado.')->withInput();
        }

        // Guardar la contraseña en texto plano antes de crear el usuario (para enviar por email)
        $passwordPlano = $data['password'];

        // Crear usuario
        $userId = $this->userModel->createUser($data);

        if ($userId) {
            // Asignar rol según tipo de usuario
            $role = $this->roleModel->findByName($tipoUsuario);
            if ($role) {
                $this->roleModel->assignRoleToUser($userId, $role['id_rol']);
            }

            // Enviar credenciales por email al nuevo usuario
            $emailSent = $this->sendWelcomeEmail(
                $data['email'],
                $data['nombre_completo'],
                $passwordPlano
            );

            if ($emailSent) {
                return redirect()->to('/admin/users')->with('msg', 'Usuario creado exitosamente. Se han enviado las credenciales al correo: ' . $data['email']);
            } else {
                return redirect()->to('/admin/users')->with('msg', 'Usuario creado exitosamente, pero hubo un error al enviar las credenciales por email. Contraseña asignada: ' . $passwordPlano);
            }
        }

        return redirect()->back()->with('msg', 'Error al crear el usuario.')->withInput();
    }

    /**
     * Formulario para editar usuario
     */
    public function editUser($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'user'        => $user,
            'roles'       => $this->roleModel->findAll(),
            'clients'     => $clientModel->findAll(),
            'consultants' => $consultantModel->findAll(),
        ];

        return view('admin/users/edit_user', $data);
    }

    /**
     * Procesar formulario de editar usuario
     */
    public function editUserPost($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario no encontrado.');
        }

        $tipoUsuario = $this->request->getVar('tipo_usuario');
        $idEntidad = null;

        // Asignar id_entidad según el tipo de usuario
        if ($tipoUsuario === 'client') {
            $idEntidad = $this->request->getVar('id_cliente');
        } elseif (in_array($tipoUsuario, ['consultant', 'admin'])) {
            $idEntidad = $this->request->getVar('id_consultor');
        }

        $data = [
            'email'           => $this->request->getVar('email'),
            'nombre_completo' => $this->request->getVar('nombre_completo'),
            'tipo_usuario'    => $tipoUsuario,
            'id_entidad'      => $idEntidad,
            'estado'          => $this->request->getVar('estado'),
        ];

        // Si se proporciona nueva contraseña
        $newPassword = $this->request->getVar('password');
        if (!empty($newPassword)) {
            $data['password'] = $newPassword;
        }

        // Verificar que el email no esté en uso por otro usuario
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id_usuario'] != $id) {
            return redirect()->back()->with('msg', 'El email ya está en uso por otro usuario.')->withInput();
        }

        if ($this->userModel->updateUser($id, $data)) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario actualizado exitosamente.');
        }

        return redirect()->back()->with('msg', 'Error al actualizar el usuario.')->withInput();
    }

    /**
     * Eliminar usuario
     */
    public function deleteUser($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario no encontrado.');
        }

        // No permitir eliminar el propio usuario
        if ($session->get('user_id') == $id) {
            return redirect()->to('/admin/users')->with('msg', 'No puedes eliminar tu propia cuenta.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario eliminado exitosamente.');
        }

        return redirect()->to('/admin/users')->with('msg', 'Error al eliminar el usuario.');
    }

    /**
     * Cambiar estado de usuario (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario no encontrado.');
        }

        $newStatus = $user['estado'] === 'activo' ? 'inactivo' : 'activo';

        if ($this->userModel->update($id, ['estado' => $newStatus])) {
            return redirect()->to('/admin/users')->with('msg', 'Estado del usuario actualizado.');
        }

        return redirect()->to('/admin/users')->with('msg', 'Error al cambiar el estado.');
    }

    /**
     * Resetear contraseña de usuario
     */
    public function resetPassword($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['admin', 'consultant'])) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('msg', 'Usuario no encontrado.');
        }

        // Generar contraseña temporal
        $tempPassword = 'Temp' . rand(1000, 9999) . '!';

        if ($this->userModel->updateUser($id, ['password' => $tempPassword])) {
            // Enviar email con la nueva contraseña
            $emailSent = $this->sendTempPasswordEmail($user['email'], $user['nombre_completo'], $tempPassword);

            if ($emailSent) {
                return redirect()->to('/admin/users')->with('msg', "Contraseña reseteada exitosamente. Se ha enviado la nueva contraseña al correo: {$user['email']}");
            } else {
                return redirect()->to('/admin/users')->with('msg', "Contraseña reseteada pero hubo un error al enviar el email. Nueva contraseña temporal: {$tempPassword}");
            }
        }

        return redirect()->to('/admin/users')->with('msg', 'Error al resetear la contraseña.');
    }

    /**
     * Enviar email con contraseña temporal usando SendGrid
     */
    private function sendTempPasswordEmail($email, $nombre, $tempPassword)
    {
        $sendgridApiKey = getenv('SENDGRID_API_KEY') ?: 'SG.xxxxxx'; // Configurar en .env

        $emailData = [
            'personalizations' => [
                [
                    'to' => [
                        ['email' => $email, 'name' => $nombre]
                    ],
                    'subject' => 'Nueva Contraseña Temporal - Enterprise SST'
                ]
            ],
            'from' => [
                'email' => 'no-reply@cycloidtalent.com',
                'name' => 'Enterprise SST'
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $this->getTempPasswordEmailTemplate($nombre, $tempPassword)
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $sendgridApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Template HTML para email de contraseña temporal
     */
    private function getTempPasswordEmailTemplate($nombre, $tempPassword)
    {
        $loginUrl = base_url('/login');

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #1c2437, #2c3e50); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="color: #ffffff; margin: 0;">Enterprise SST</h1>
            </div>

            <div style="background: #ffffff; padding: 30px; border: 1px solid #e9ecef; border-top: none;">
                <h2 style="color: #1c2437;">Hola, ' . htmlspecialchars($nombre) . '</h2>

                <p>Tu contraseña ha sido restablecida por un administrador del sistema.</p>

                <p>Tu nueva contraseña temporal es:</p>

                <div style="background: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;">
                    <span style="font-size: 24px; font-weight: bold; color: #bd9751; letter-spacing: 2px;">' . htmlspecialchars($tempPassword) . '</span>
                </div>

                <p><strong>Por seguridad, te recomendamos cambiar esta contraseña inmediatamente después de iniciar sesión.</strong></p>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $loginUrl . '" style="background: linear-gradient(135deg, #1c2437, #2c3e50); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">Iniciar Sesión</a>
                </div>

                <p style="color: #666; font-size: 14px;">Si no solicitaste este cambio, por favor contacta al administrador del sistema inmediatamente.</p>
            </div>

            <div style="background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e9ecef; border-top: none;">
                <p style="margin: 0; color: #666; font-size: 12px;">© 2024 Cycloid Talent SAS - Todos los derechos reservados</p>
                <p style="margin: 5px 0 0; color: #666; font-size: 12px;">NIT: 901.653.912</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Enviar email de bienvenida con credenciales de acceso usando SendGrid
     */
    private function sendWelcomeEmail($email, $nombre, $password)
    {
        $sendgridApiKey = getenv('SENDGRID_API_KEY') ?: 'SG.xxxxxx';

        $emailData = [
            'personalizations' => [
                [
                    'to' => [
                        ['email' => $email, 'name' => $nombre]
                    ],
                    'subject' => 'Bienvenido a Enterprise SST - Tus credenciales de acceso'
                ]
            ],
            'from' => [
                'email' => 'no-reply@cycloidtalent.com',
                'name' => 'Enterprise SST'
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $this->getWelcomeEmailTemplate($nombre, $email, $password)
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $sendgridApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Template HTML para email de bienvenida
     */
    private function getWelcomeEmailTemplate($nombre, $email, $password)
    {
        $loginUrl = base_url('/login');

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #1c2437, #2c3e50); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="color: #ffffff; margin: 0;">Enterprise SST</h1>
            </div>

            <div style="background: #ffffff; padding: 30px; border: 1px solid #e9ecef; border-top: none;">
                <h2 style="color: #1c2437;">¡Bienvenido/a, ' . htmlspecialchars($nombre) . '!</h2>

                <p>Se ha creado tu cuenta en la plataforma <strong>Enterprise SST</strong>.</p>

                <p>A continuación encontrarás tus credenciales de acceso:</p>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Usuario (correo):</strong></p>
                    <p style="margin: 5px 0 15px; color: #1c2437; font-size: 16px;">' . htmlspecialchars($email) . '</p>

                    <p style="margin: 5px 0;"><strong>Contraseña:</strong></p>
                    <p style="margin: 5px 0; font-size: 20px; font-weight: bold; color: #bd9751; letter-spacing: 1px;">' . htmlspecialchars($password) . '</p>
                </div>

                <p><strong>Por seguridad, te recomendamos cambiar tu contraseña después de tu primer inicio de sesión.</strong></p>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $loginUrl . '" style="background: linear-gradient(135deg, #1c2437, #2c3e50); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">Iniciar Sesión</a>
                </div>

                <p style="color: #666; font-size: 14px;">Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar al administrador del sistema.</p>
            </div>

            <div style="background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e9ecef; border-top: none;">
                <p style="margin: 0; color: #666; font-size: 12px;">© 2024 Cycloid Talent SAS - Todos los derechos reservados</p>
                <p style="margin: 5px 0 0; color: #666; font-size: 12px;">NIT: 901.653.912</p>
            </div>
        </body>
        </html>';
    }
}
