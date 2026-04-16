<?php

namespace App\Controllers;

use App\Models\EmpresaConsultoraModel;
use App\Models\ConsultantModel;
use App\Models\UserModel;
use App\Libraries\TenantFilter;
use CodeIgniter\Controller;

class EmpresaConsultoraController extends Controller
{
    /**
     * Listado de todas las empresas (superadmin only)
     */
    public function index()
    {
        $model = new EmpresaConsultoraModel();
        $empresas = $model->findAll();

        $consultantModel = new ConsultantModel();
        $userModel = new UserModel();
        foreach ($empresas as &$emp) {
            $emp['num_consultores'] = $consultantModel
                ->where('id_empresa_consultora', $emp['id_empresa_consultora'])
                ->countAllResults(false);
            $emp['num_usuarios'] = $userModel
                ->whereIn('tipo_usuario', ['admin', 'consultant', 'superadmin'])
                ->like('id_entidad', '') // placeholder
                ->countAllResults(false);
        }

        // Contar consultores y clientes por empresa con query directo
        $db = \Config\Database::connect();
        foreach ($empresas as &$emp) {
            $idEmp = $emp['id_empresa_consultora'];
            $emp['num_consultores'] = (int)$db->query(
                "SELECT COUNT(*) as c FROM tbl_consultor WHERE id_empresa_consultora = ?", [$idEmp]
            )->getRow()->c;
            $emp['num_clientes'] = (int)$db->query(
                "SELECT COUNT(*) as c FROM tbl_clientes WHERE id_consultor IN (SELECT id_consultor FROM tbl_consultor WHERE id_empresa_consultora = ?)", [$idEmp]
            )->getRow()->c;
        }

        return view('admin/empresas_consultoras/index', ['empresas' => $empresas]);
    }

    /**
     * Formulario para crear empresa (superadmin only)
     */
    public function create()
    {
        return view('admin/empresas_consultoras/form', [
            'empresa' => null,
            'modo' => 'crear',
        ]);
    }

    /**
     * Guardar nueva empresa + opcionalmente crear usuario admin
     */
    public function store()
    {
        $model = new EmpresaConsultoraModel();

        $data = [
            'razon_social'          => $this->request->getPost('razon_social'),
            'nit'                   => $this->request->getPost('nit'),
            'direccion'             => $this->request->getPost('direccion'),
            'telefono'              => $this->request->getPost('telefono'),
            'correo'                => $this->request->getPost('correo'),
            'color_primario'        => $this->request->getPost('color_primario'),
            'estado'                => 'activo',
            'fecha_inicio_contrato' => $this->request->getPost('fecha_inicio_contrato') ?: null,
            'plan'                  => $this->request->getPost('plan'),
        ];

        // Logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $nuevoNombre = 'empresa_' . time() . '.' . $logo->getExtension();
            $logo->move(ROOTPATH . 'public/uploads/empresas', $nuevoNombre);
            $data['logo'] = 'uploads/empresas/' . $nuevoNombre;
        }

        $idEmpresa = $model->insert($data);
        if ($idEmpresa === false) {
            return redirect()->back()->with('error', 'Error al crear empresa: ' . implode(', ', $model->errors()))->withInput();
        }

        // Crear usuario admin si se proporcionó email
        $emailAdmin = $this->request->getPost('email_admin');
        $nombreAdmin = $this->request->getPost('nombre_admin');
        $mensajeExtra = '';

        if ($emailAdmin && $nombreAdmin) {
            $resultado = $this->crearPrimerAdmin((int)$idEmpresa, $emailAdmin, $nombreAdmin);
            if ($resultado['ok']) {
                $mensajeExtra = " | Admin creado: {$emailAdmin} — Contraseña temporal: {$resultado['password']}";

                // Enviar email de bienvenida
                $emailEnviado = $this->enviarEmailBienvenida(
                    $emailAdmin, $nombreAdmin, $resultado['password'], $data['razon_social']
                );
                $mensajeExtra .= $emailEnviado
                    ? ' | Email de bienvenida enviado.'
                    : ' | (Email no pudo enviarse, entrega la contraseña manualmente)';
            } else {
                $mensajeExtra = " | Error creando admin: {$resultado['error']}";
            }
        }

        return redirect()->to('/admin/empresas-consultoras')
            ->with('msg', "Empresa '{$data['razon_social']}' creada exitosamente.{$mensajeExtra}");
    }

    /**
     * Formulario editar empresa (superadmin ve cualquiera, admin solo la suya)
     */
    public function edit($id = null)
    {
        $model = new EmpresaConsultoraModel();

        if ($id === null || !TenantFilter::isSuperAdmin()) {
            $id = TenantFilter::getEmpresaId();
        }

        if (!$id) {
            return redirect()->to('/admin/dashboard')->with('error', 'Empresa no encontrada.');
        }

        $empresa = $model->find($id);
        if (!$empresa) {
            return redirect()->to('/admin/dashboard')->with('error', 'Empresa no encontrada.');
        }

        return view('admin/empresas_consultoras/form', [
            'empresa' => $empresa,
            'modo' => 'editar',
        ]);
    }

    /**
     * Actualizar empresa
     */
    public function update($id)
    {
        $model = new EmpresaConsultoraModel();

        // Admin solo puede editar la suya
        if (!TenantFilter::isSuperAdmin()) {
            $miEmpresa = TenantFilter::getEmpresaId();
            if ((int)$id !== $miEmpresa) {
                return redirect()->to('/admin/dashboard')->with('error', 'No tienes acceso.');
            }
        }

        $empresa = $model->find($id);
        if (!$empresa) {
            return redirect()->to('/admin/empresas-consultoras')->with('error', 'Empresa no encontrada.');
        }

        $data = [
            'razon_social'          => $this->request->getPost('razon_social'),
            'nit'                   => $this->request->getPost('nit'),
            'direccion'             => $this->request->getPost('direccion'),
            'telefono'              => $this->request->getPost('telefono'),
            'correo'                => $this->request->getPost('correo'),
            'color_primario'        => $this->request->getPost('color_primario'),
            'fecha_inicio_contrato' => $this->request->getPost('fecha_inicio_contrato') ?: null,
            'plan'                  => $this->request->getPost('plan'),
        ];

        // Solo superadmin puede cambiar estado
        if (TenantFilter::isSuperAdmin()) {
            $estado = $this->request->getPost('estado');
            if ($estado) $data['estado'] = $estado;
        }

        // Logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $nuevoNombre = 'empresa_' . $id . '_' . time() . '.' . $logo->getExtension();
            $logo->move(ROOTPATH . 'public/uploads/empresas', $nuevoNombre);
            $data['logo'] = 'uploads/empresas/' . $nuevoNombre;
        }

        $model->update($id, $data);

        $redirect = TenantFilter::isSuperAdmin() ? '/admin/empresas-consultoras' : '/admin/mi-empresa';
        return redirect()->to($redirect)->with('msg', "Empresa actualizada exitosamente.");
    }

    /**
     * Cambiar estado de empresa (superadmin only)
     */
    public function toggleEstado($id)
    {
        $model = new EmpresaConsultoraModel();
        $empresa = $model->find($id);
        if (!$empresa) {
            return redirect()->to('/admin/empresas-consultoras')->with('error', 'Empresa no encontrada.');
        }

        $nuevoEstado = $empresa['estado'] === 'activo' ? 'suspendido' : 'activo';
        $model->update($id, ['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === 'activo' ? 'activada' : 'suspendida';
        return redirect()->to('/admin/empresas-consultoras')
            ->with('msg', "Empresa '{$empresa['razon_social']}' {$accion}.");
    }

    /**
     * Mi Empresa (admin no-superadmin)
     */
    public function miEmpresa()
    {
        return $this->edit(null);
    }

    /**
     * Crear primer consultor + usuario admin para una empresa nueva
     */
    private function crearPrimerAdmin(int $idEmpresa, string $email, string $nombre): array
    {
        $consultantModel = new ConsultantModel();
        $userModel = new UserModel();

        // Verificar que el email no exista
        if ($userModel->findByEmail($email)) {
            return ['ok' => false, 'error' => "El email {$email} ya está registrado."];
        }

        // Crear consultor
        $idConsultor = $consultantModel->insert([
            'nombre_consultor'      => $nombre,
            'correo_consultor'      => $email,
            'id_empresa_consultora' => $idEmpresa,
            'rol'                   => 'admin',
        ]);

        if (!$idConsultor) {
            return ['ok' => false, 'error' => 'Error creando consultor.'];
        }

        // Generar password temporal
        $password = substr(str_shuffle('abcdefghijkmnpqrstuvwxyz23456789'), 0, 10);

        $idUsuario = $userModel->createUser([
            'email'           => $email,
            'password'        => $password,
            'nombre_completo' => $nombre,
            'tipo_usuario'    => 'admin',
            'id_entidad'      => $idConsultor,
            'estado'          => 'activo',
        ]);

        if (!$idUsuario) {
            return ['ok' => false, 'error' => 'Error creando usuario: ' . implode(', ', $userModel->getLastErrors())];
        }

        return ['ok' => true, 'password' => $password, 'id_usuario' => $idUsuario, 'id_consultor' => $idConsultor];
    }

    /**
     * Envia email de bienvenida al nuevo admin de empresa consultora
     */
    private function enviarEmailBienvenida(string $email, string $nombre, string $password, string $razonSocial): bool
    {
        $loginUrl = base_url('/login');

        $html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
            <!-- Header -->
            <div style='background: linear-gradient(135deg, #1c2437, #2c3e50); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 28px;'>EnterpriseSST</h1>
                <p style='color: #bd9751; margin: 5px 0 0; font-size: 14px;'>Sistemas que Evolucionan</p>
            </div>

            <!-- Body -->
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0;'>
                <h2 style='color: #1c2437; margin-top: 0;'>Bienvenido, {$nombre}</h2>

                <p style='color: #333; line-height: 1.6;'>
                    Tu empresa <strong>{$razonSocial}</strong> ha sido registrada exitosamente en el ecosistema
                    <strong>EnterpriseSST</strong>, la plataforma integral para la gestion del Sistema de Gestion
                    de Seguridad y Salud en el Trabajo (SG-SST).
                </p>

                <div style='background: #f8f9fa; border-left: 4px solid #bd9751; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;'>
                    <h3 style='color: #1c2437; margin-top: 0; font-size: 16px;'>Tus credenciales de acceso</h3>
                    <table style='width: 100%;'>
                        <tr>
                            <td style='padding: 5px 0; color: #666; width: 120px;'>Email:</td>
                            <td style='padding: 5px 0; color: #333; font-weight: bold;'>{$email}</td>
                        </tr>
                        <tr>
                            <td style='padding: 5px 0; color: #666;'>Contrasena:</td>
                            <td style='padding: 5px 0; color: #333; font-weight: bold; font-family: monospace; font-size: 16px;'>{$password}</td>
                        </tr>
                    </table>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$loginUrl}'
                       style='display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #1c2437, #2c3e50);
                              color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                        Ingresar a EnterpriseSST
                    </a>
                </div>

                <h3 style='color: #1c2437; font-size: 16px;'>Primeros pasos</h3>
                <ol style='color: #333; line-height: 1.8;'>
                    <li>Ingresa con las credenciales de arriba</li>
                    <li>Ve a <strong>Mi Empresa</strong> para completar los datos de tu empresa (NIT, logo, etc.)</li>
                    <li>Crea tus consultores desde el menu <strong>Consultores</strong></li>
                    <li>Crea tus clientes y comienza a gestionar su SG-SST</li>
                </ol>

                <p style='color: #666; font-size: 13px; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e0e0e0;'>
                    <strong>Importante:</strong> Te recomendamos cambiar tu contrasena temporal desde
                    la opcion de recuperacion de contrasena en el login.
                </p>
            </div>

            <!-- Footer -->
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 12px 12px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='color: #999; font-size: 12px; margin: 0;'>
                    Este correo fue enviado por EnterpriseSST — Plataforma de Gestion SG-SST
                </p>
            </div>
        </div>
        ";

        try {
            $sendgridEmail = new \SendGrid\Mail\Mail();
            $sendgridEmail->setFrom(
                getenv('SENDGRID_FROM_EMAIL') ?: 'notificacion.cycloidtalent@cycloidtalent.com',
                getenv('SENDGRID_FROM_NAME') ?: 'EnterpriseSST'
            );
            $sendgridEmail->setSubject("Bienvenido a EnterpriseSST — {$razonSocial}");
            $sendgridEmail->addTo($email, $nombre);
            $sendgridEmail->addContent('text/html', $html);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($sendgridEmail);

            $ok = $response->statusCode() >= 200 && $response->statusCode() < 300;
            if (!$ok) {
                log_message('error', 'Email bienvenida falló: status=' . $response->statusCode() . ' body=' . $response->body());
            }
            return $ok;
        } catch (\Exception $e) {
            log_message('error', 'Email bienvenida excepción: ' . $e->getMessage());
            return false;
        }
    }
}
