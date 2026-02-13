<?php

namespace App\Controllers;

use App\Models\ResponsableSSTModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\UserModel;

/**
 * Controlador para gestionar responsables del SG-SST
 */
class ResponsablesSSTController extends BaseController
{
    protected ResponsableSSTModel $responsableModel;
    protected ClientModel $clienteModel;

    public function __construct()
    {
        $this->responsableModel = new ResponsableSSTModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Lista responsables de un cliente
     */
    public function index(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener contexto SST para saber estándares aplicables
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Obtener responsables agrupados
        $responsablesAgrupados = $this->responsableModel->getByClienteAgrupados($idCliente);

        // Verificar roles obligatorios
        $verificacion = $this->responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        $data = [
            'titulo' => 'Responsables SG-SST',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estandares' => $estandares,
            'responsablesAgrupados' => $responsablesAgrupados,
            'verificacion' => $verificacion,
            'tiposRol' => ResponsableSSTModel::TIPOS_ROL
        ];

        return view('responsables_sst/index', $data);
    }

    /**
     * Formulario para crear nuevo responsable
     */
    public function crear(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $data = [
            'titulo' => 'Agregar Responsable',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'tiposRol' => $this->getRolesDisponibles($estandares),
            'responsable' => null
        ];

        return view('responsables_sst/formulario', $data);
    }

    /**
     * Formulario para editar responsable
     */
    public function editar(int $idCliente, int $idResponsable)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $responsable = $this->responsableModel->find($idResponsable);
        if (!$responsable || $responsable['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Responsable no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $data = [
            'titulo' => 'Editar Responsable',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'tiposRol' => $this->getRolesDisponibles($estandares),
            'responsable' => $responsable
        ];

        return view('responsables_sst/formulario', $data);
    }

    /**
     * Guarda responsable (crear o actualizar)
     */
    public function guardar(int $idCliente)
    {
        log_message('critical', '========== RESPONSABLES_SST::GUARDAR INICIO ==========');
        log_message('critical', 'PASO 0: idCliente=' . $idCliente);
        log_message('critical', 'PASO 0: POST completo = ' . json_encode($this->request->getPost()));

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            log_message('critical', 'PASO 1: CLIENTE NO ENCONTRADO - ABORTANDO');
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }
        log_message('critical', 'PASO 1: Cliente OK = ' . ($cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'SIN NOMBRE'));

        $idResponsable = $this->request->getPost('id_responsable');
        log_message('critical', 'PASO 2: id_responsable = ' . var_export($idResponsable, true));

        $datos = [
            'id_cliente' => $idCliente,
            'tipo_rol' => $this->request->getPost('tipo_rol'),
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'tipo_documento' => $this->request->getPost('tipo_documento') ?? 'CC',
            'numero_documento' => $this->request->getPost('numero_documento'),
            'cargo' => $this->request->getPost('cargo'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'licencia_sst_numero' => $this->request->getPost('licencia_sst_numero'),
            'licencia_sst_vigencia' => $this->request->getPost('licencia_sst_vigencia') ?: null,
            'formacion_sst' => $this->request->getPost('formacion_sst'),
            'fecha_inicio' => $this->request->getPost('fecha_inicio') ?: null,
            'fecha_fin' => $this->request->getPost('fecha_fin') ?: null,
            'acta_nombramiento' => $this->request->getPost('acta_nombramiento'),
            'observaciones' => $this->request->getPost('observaciones'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        log_message('critical', 'PASO 3: datos email = ' . var_export($datos['email'], true));

        // Validación básica
        if (empty($datos['nombre_completo']) || empty($datos['numero_documento']) || empty($datos['tipo_rol'])) {
            log_message('critical', 'PASO 4: VALIDACION BASICA FALLO - ABORTANDO');
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nombre, documento y tipo de rol son obligatorios'
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Nombre, documento y tipo de rol son obligatorios');
        }
        log_message('critical', 'PASO 4: Validacion basica OK');

        // Verificar si se debe crear usuario
        $crearUsuarioPost = $this->request->getPost('crear_usuario');
        $crearUsuarioVar = $this->request->getVar('crear_usuario');
        $crearUsuario = $crearUsuarioPost ?? $crearUsuarioVar;

        log_message('critical', 'PASO 5: crear_usuario POST = ' . var_export($crearUsuarioPost, true));
        log_message('critical', 'PASO 5: crear_usuario VAR  = ' . var_export($crearUsuarioVar, true));
        log_message('critical', 'PASO 5: crear_usuario FINAL = ' . var_export($crearUsuario, true));

        // Evaluar condiciones del IF por separado
        $condCrear = (bool)$crearUsuario;
        $condEmail = !empty($datos['email']);
        $condNuevo = !$idResponsable;
        log_message('critical', "PASO 6: CONDICIONES - crearUsuario={$condCrear}, emailNoVacio={$condEmail}, esNuevo={$condNuevo}");
        log_message('critical', "PASO 6: IF COMPLETO = " . var_export($condCrear && $condEmail && $condNuevo, true));

        try {
            if ($idResponsable) {
                $this->responsableModel->update($idResponsable, $datos);
                $mensaje = 'Responsable actualizado correctamente';
                $nuevoIdResponsable = $idResponsable;
                log_message('critical', "PASO 7: ACTUALIZADO responsable ID {$idResponsable}");
            } else {
                $nuevoIdResponsable = $this->responsableModel->insert($datos);
                $mensaje = 'Responsable agregado correctamente';
                log_message('critical', "PASO 7: CREADO responsable ID {$nuevoIdResponsable}");
            }

            // Crear usuario si se marcó la opción y hay email
            if ($crearUsuario && !empty($datos['email']) && !$idResponsable) {
                log_message('critical', 'PASO 8: >>> ENTRO AL BLOQUE DE CREAR USUARIO <<<');

                $userModel = new UserModel();
                $existeUsuario = $userModel->findByEmail($datos['email']);
                log_message('critical', 'PASO 9: findByEmail resultado = ' . var_export($existeUsuario, true));

                if (!$existeUsuario) {
                    log_message('critical', 'PASO 10: Email NO existe, procediendo a crear usuario');

                    $passwordTemp = $this->generarPasswordSeguro();
                    log_message('critical', 'PASO 11: Password generado = ' . $passwordTemp);

                    $datosUsuario = [
                        'email' => $datos['email'],
                        'password' => $passwordTemp,
                        'nombre_completo' => $datos['nombre_completo'],
                        'tipo_usuario' => 'client',
                        'id_entidad' => $idCliente,
                        'estado' => 'activo'
                    ];
                    log_message('critical', 'PASO 12: datosUsuario = ' . json_encode(array_diff_key($datosUsuario, ['password' => ''])));

                    log_message('critical', 'PASO 13: >>> LLAMANDO createUser() <<<');
                    $idUsuario = $userModel->createUser($datosUsuario);
                    log_message('critical', 'PASO 14: createUser() retorno = ' . var_export($idUsuario, true));

                    if ($idUsuario) {
                        log_message('critical', "PASO 15: USUARIO CREADO EXITOSAMENTE ID={$idUsuario}");

                        // Enviar credenciales por email
                        log_message('critical', 'PASO 16: Intentando enviar email...');
                        $emailEnviado = $this->enviarCredencialesEmail(
                            $datos['email'],
                            $datos['nombre_completo'],
                            $passwordTemp,
                            $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'Empresa'
                        );
                        log_message('critical', 'PASO 17: enviarCredencialesEmail resultado = ' . var_export($emailEnviado, true));

                        if ($emailEnviado) {
                            $mensaje .= '. Usuario creado y credenciales enviadas al email: ' . $datos['email'];
                        } else {
                            $mensaje .= '. Usuario creado pero error al enviar email. Password temporal: ' . $passwordTemp;
                        }
                    } else {
                        $erroresUsuario = $userModel->errors();
                        $errorMsg = !empty($erroresUsuario) ? implode(', ', $erroresUsuario) : 'Error desconocido';
                        log_message('critical', "PASO 15: FALLO createUser - errores: " . json_encode($erroresUsuario));
                        $mensaje .= '. Error al crear usuario: ' . $errorMsg;
                    }
                } else {
                    log_message('critical', 'PASO 10: EMAIL YA EXISTE en tbl_usuarios');
                    $mensaje .= '. Ya existe un usuario con ese email.';
                }
            } else {
                log_message('critical', 'PASO 8: >>> NO ENTRO AL BLOQUE - CONDICION FALSA <<<');
            }

            log_message('critical', 'PASO FINAL: mensaje = ' . $mensaje);
            log_message('critical', '========== RESPONSABLES_SST::GUARDAR FIN ==========');

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => $mensaje]);
            }

            return redirect()->to("responsables-sst/{$idCliente}")->with('success', $mensaje);

        } catch (\Throwable $e) {
            log_message('critical', 'PASO CATCH: EXCEPCION ' . get_class($e) . ': ' . $e->getMessage());
            log_message('critical', 'PASO CATCH: Archivo: ' . $e->getFile() . ':' . $e->getLine());
            log_message('critical', 'PASO CATCH: Trace: ' . $e->getTraceAsString());
            $error = 'Error al guardar: ' . $e->getMessage();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $error]);
            }
            return redirect()->back()->withInput()->with('error', $error);
        }
    }

    /**
     * Elimina un responsable
     */
    public function eliminar(int $idCliente, int $idResponsable)
    {
        $responsable = $this->responsableModel->find($idResponsable);

        if (!$responsable || $responsable['id_cliente'] != $idCliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Responsable no encontrado']);
            }
            return redirect()->back()->with('error', 'Responsable no encontrado');
        }

        try {
            $this->responsableModel->delete($idResponsable);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Responsable eliminado']);
            }

            return redirect()->to("responsables-sst/{$idCliente}")->with('success', 'Responsable eliminado');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * API: Obtiene responsables para documentos
     */
    public function apiObtener(int $idCliente)
    {
        $responsables = $this->responsableModel->getByCliente($idCliente);

        return $this->response->setJSON([
            'success' => true,
            'data' => $responsables
        ]);
    }

    /**
     * API: Verifica roles obligatorios
     */
    public function apiVerificar(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $verificacion = $this->responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        return $this->response->setJSON([
            'success' => true,
            'data' => $verificacion
        ]);
    }

    /**
     * Migra datos del contexto antiguo
     */
    public function migrar(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay contexto SST para migrar'
            ]);
        }

        $migrados = $this->responsableModel->migrarDesdeContexto($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Migración completada',
            'migrados' => $migrados
        ]);
    }

    /**
     * Obtiene roles disponibles según estándares
     *
     * Según Resolución 0312/2019:
     * - 7 estándares (< 10 trabajadores): Solo Vigía SST, NO COPASST
     * - 21 estándares (10-50 trabajadores): COPASST obligatorio, NO Vigía
     * - 60 estándares (> 50 trabajadores o Riesgo IV-V): COPASST + Comité Convivencia
     */
    private function getRolesDisponibles(int $estandares): array
    {
        $todos = ResponsableSSTModel::TIPOS_ROL;

        if ($estandares <= 7) {
            // 7 estándares: Solo Vigía SST, NO COPASST ni Comité Convivencia
            // Tampoco requiere Responsable interno del SG-SST (lo gestiona consultor externo)
            unset(
                $todos['responsable_sgsst'],
                $todos['copasst_presidente'],
                $todos['copasst_secretario'],
                $todos['copasst_representante_empleador'],
                $todos['copasst_representante_trabajadores'],
                $todos['copasst_suplente_empleador'],
                $todos['copasst_suplente_trabajadores'],
                $todos['comite_convivencia_presidente'],
                $todos['comite_convivencia_secretario'],
                $todos['comite_convivencia_representante_empleador'],
                $todos['comite_convivencia_representante_trabajadores'],
                $todos['comite_convivencia_suplente_empleador'],
                $todos['comite_convivencia_suplente_trabajadores']
            );
        } elseif ($estandares <= 21) {
            // 21 estándares: COPASST obligatorio, NO Vigía
            // Comité de Convivencia es obligatorio para todas las empresas
            unset(
                $todos['vigia_sst'],
                $todos['vigia_sst_suplente']
            );
        } else {
            // 60 estándares: COPASST + Comité Convivencia, NO Vigía
            unset(
                $todos['vigia_sst'],
                $todos['vigia_sst_suplente']
            );
        }

        return $todos;
    }

    /**
     * Genera una contraseña segura aleatoria
     */
    private function generarPasswordSeguro(): string
    {
        $mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $minusculas = 'abcdefghijklmnopqrstuvwxyz';
        $numeros = '0123456789';
        $especiales = '!@#$%';

        // Garantizar al menos uno de cada tipo
        $password = $mayusculas[random_int(0, strlen($mayusculas) - 1)];
        $password .= $minusculas[random_int(0, strlen($minusculas) - 1)];
        $password .= $numeros[random_int(0, strlen($numeros) - 1)];
        $password .= $especiales[random_int(0, strlen($especiales) - 1)];

        // Completar con caracteres aleatorios
        $todos = $mayusculas . $minusculas . $numeros;
        for ($i = 0; $i < 6; $i++) {
            $password .= $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezclar caracteres
        return str_shuffle($password);
    }

    /**
     * Envía credenciales de acceso por email usando SendGrid
     */
    private function enviarCredencialesEmail(string $email, string $nombre, string $password, string $nombreEmpresa): bool
    {
        $loginUrl = base_url('/login');
        $fromEmail = env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com');
        $fromName = env('SENDGRID_FROM_NAME', 'Enterprise SST');

        try {
            $emailObj = new \SendGrid\Mail\Mail();
            $emailObj->setFrom($fromEmail, $fromName);
            $emailObj->setSubject('Bienvenido a Enterprise SST - Credenciales de Acceso');
            $emailObj->addTo($email, $nombre);
            $emailObj->addContent('text/html', $this->getEmailTemplateBienvenida($nombre, $email, $password, $nombreEmpresa, $loginUrl));

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($emailObj);

            $statusCode = $response->statusCode();
            log_message('info', "ResponsablesSST: Email bienvenida enviado a {$email} - Status: {$statusCode}");

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            log_message('error', "ResponsablesSST: Error enviando email a {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Template HTML para email de bienvenida con credenciales
     */
    private function getEmailTemplateBienvenida(string $nombre, string $email, string $password, string $empresa, string $loginUrl): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #1c2437, #2c3e50); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="color: #ffffff; margin: 0;">Enterprise SST</h1>
                <p style="color: #bd9751; margin: 10px 0 0;">Sistema de Gestión en Seguridad y Salud en el Trabajo</p>
            </div>

            <div style="background: #ffffff; padding: 30px; border: 1px solid #e9ecef; border-top: none;">
                <h2 style="color: #1c2437;">¡Bienvenido, ' . htmlspecialchars($nombre) . '!</h2>

                <p>Se ha creado tu cuenta de acceso al sistema Enterprise SST para la empresa <strong>' . htmlspecialchars($empresa) . '</strong>.</p>

                <p>A continuación encontrarás tus credenciales de acceso:</p>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Email:</strong></td>
                            <td style="padding: 8px 0;">' . htmlspecialchars($email) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Contraseña:</strong></td>
                            <td style="padding: 8px 0;">
                                <span style="font-size: 18px; font-weight: bold; color: #bd9751; letter-spacing: 1px;">' . htmlspecialchars($password) . '</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <p style="color: #dc3545;"><strong>Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña después del primer inicio de sesión.</p>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $loginUrl . '" style="background: linear-gradient(135deg, #1c2437, #2c3e50); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Iniciar Sesión</a>
                </div>

                <p style="color: #666; font-size: 14px;">Si tienes problemas para acceder, contacta al administrador del sistema.</p>
            </div>

            <div style="background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e9ecef; border-top: none;">
                <p style="margin: 0; color: #666; font-size: 12px;">© ' . date('Y') . ' Cycloid Talent SAS - Todos los derechos reservados</p>
                <p style="margin: 5px 0 0; color: #666; font-size: 12px;">NIT: 901.653.912</p>
            </div>
        </body>
        </html>';
    }
}
