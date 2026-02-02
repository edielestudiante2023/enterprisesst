<?php

namespace App\Controllers;

use App\Models\TipoComiteModel;
use App\Models\ComiteModel;
use App\Models\MiembroComiteModel;
use App\Models\ActaModel;
use App\Models\ActaAsistenteModel;
use App\Models\ActaCompromisoModel;
use App\Models\ActaNotificacionModel;
use App\Models\ActaTokenModel;
use App\Models\PlantillaOrdenDiaModel;
use App\Models\ClientModel;
use App\Models\ResponsableSSTModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ActasController extends BaseController
{
    protected $tipoComiteModel;
    protected $comiteModel;
    protected $miembroModel;
    protected $actaModel;
    protected $asistentesModel;
    protected $compromisosModel;
    protected $notificacionModel;
    protected $tokenModel;
    protected $plantillaModel;

    public function __construct()
    {
        $this->tipoComiteModel = new TipoComiteModel();
        $this->comiteModel = new ComiteModel();
        $this->miembroModel = new MiembroComiteModel();
        $this->actaModel = new ActaModel();
        $this->asistentesModel = new ActaAsistenteModel();
        $this->compromisosModel = new ActaCompromisoModel();
        $this->notificacionModel = new ActaNotificacionModel();
        $this->tokenModel = new ActaTokenModel();
        $this->plantillaModel = new PlantillaOrdenDiaModel();
    }

    /**
     * Dashboard de comités de un cliente
     */
    public function index(int $idCliente)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $comites = $this->comiteModel->getByCliente($idCliente);
        $tiposComite = $this->tipoComiteModel->getActivos();

        // Estadísticas por comité
        foreach ($comites as &$comite) {
            $comite['stats'] = $this->actaModel->getEstadisticas($comite['id_comite'], date('Y'));
            $comite['miembros'] = $this->miembroModel->contarActivos($comite['id_comite']);
            $comite['compromisos_pendientes'] = count($this->compromisosModel->getByComite($comite['id_comite'], 'pendiente'));
        }

        // Comités sin acta del mes
        $sinActaMes = $this->actaModel->getComitesSinActaMes($idCliente);

        // Actas pendientes de firma
        $actasPendientes = $this->actaModel->getPendientesFirma($idCliente);

        // Compromisos vencidos
        $compromisosVencidos = $this->compromisosModel->getVencidos($idCliente);

        return view('actas/index', [
            'cliente' => $cliente,
            'comites' => $comites,
            'tiposComite' => $tiposComite,
            'sinActaMes' => $sinActaMes,
            'actasPendientes' => $actasPendientes,
            'compromisosVencidos' => $compromisosVencidos,
            'anioActual' => date('Y')
        ]);
    }

    /**
     * Ver detalle de un comité
     */
    public function verComite(int $idComite)
    {
        $comite = $this->comiteModel->getConDetalles($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $miembros = $this->miembroModel->getActivosPorComite($idComite);
        $actas = $this->actaModel->getByComite($idComite, date('Y'));
        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($idComite);
        $estadisticas = $this->actaModel->getEstadisticas($idComite, date('Y'));
        $paridad = $this->miembroModel->verificarParidad($idComite);

        return view('actas/comite', [
            'cliente' => $cliente,
            'comite' => $comite,
            'miembros' => $miembros,
            'actas' => $actas,
            'compromisosPendientes' => $compromisosPendientes,
            'estadisticas' => $estadisticas,
            'paridad' => $paridad,
            'anioActual' => date('Y')
        ]);
    }

    /**
     * Formulario para crear nuevo comité
     */
    public function nuevoComite(int $idCliente)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $tiposComite = $this->tipoComiteModel->getActivos();
        $comitesExistentes = $this->comiteModel->getByCliente($idCliente);
        $tiposUsados = array_column($comitesExistentes, 'id_tipo');

        return view('actas/nuevo_comite', [
            'cliente' => $cliente,
            'tiposComite' => $tiposComite,
            'tiposUsados' => $tiposUsados
        ]);
    }

    /**
     * Guardar nuevo comité
     */
    public function guardarComite(int $idCliente)
    {
        $data = [
            'id_cliente' => $idCliente,
            'id_tipo' => $this->request->getPost('id_tipo'),
            'fecha_conformacion' => $this->request->getPost('fecha_conformacion'),
            'lugar_habitual' => $this->request->getPost('lugar_habitual'),
            'dia_reunion_preferido' => $this->request->getPost('dia_reunion_preferido'),
            'hora_reunion_preferida' => $this->request->getPost('hora_reunion_preferida')
        ];

        $idComite = $this->comiteModel->crearComite($data);

        if ($idComite) {
            return redirect()->to("/actas/{$idCliente}/comite/{$idComite}")
                           ->with('success', 'Comité creado exitosamente. Ahora agregue los miembros.');
        }

        return redirect()->back()->with('error', 'Error al crear el comité')->withInput();
    }

    /**
     * Formulario para agregar miembro al comité
     */
    public function nuevoMiembro(int $idComite)
    {
        $comite = $this->comiteModel->getConDetalles($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        // Obtener responsables SST disponibles para este tipo de comité
        $responsableModel = new ResponsableSSTModel();
        $codigoComite = $comite['codigo'] ?? $comite['tipo_nombre'] ?? '';

        // Obtener responsables específicos del tipo de comité
        $responsablesComite = $responsableModel->getByTipoComite($comite['id_cliente'], $codigoComite);

        // Obtener todos los responsables para opción de selección general
        $todosResponsables = $responsableModel->getDisponiblesParaComite($comite['id_cliente']);

        // Obtener miembros actuales del comité
        $miembrosActuales = $this->miembroModel->getActivosPorComite($idComite);

        return view('actas/nuevo_miembro', [
            'cliente' => $cliente,
            'comite' => $comite,
            'responsablesComite' => $responsablesComite,
            'todosResponsables' => $todosResponsables,
            'miembrosActuales' => $miembrosActuales
        ]);
    }

    /**
     * Guardar nuevo miembro
     */
    public function guardarMiembro(int $idComite)
    {
        $comite = $this->comiteModel->find($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        // Verificar si se seleccionó un responsable existente
        $idResponsable = $this->request->getPost('id_responsable');

        $data = [
            'id_comite' => $idComite,
            'id_cliente' => $comite['id_cliente'],
            'id_responsable' => $idResponsable ?: null, // Link al responsable SST
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'tipo_documento' => $this->request->getPost('tipo_documento') ?: 'CC',
            'numero_documento' => $this->request->getPost('numero_documento'),
            'cargo' => $this->request->getPost('cargo'),
            'area_dependencia' => $this->request->getPost('area_dependencia'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'representacion' => $this->request->getPost('representacion'),
            'tipo_miembro' => $this->request->getPost('tipo_miembro'),
            'rol_comite' => $this->request->getPost('rol_comite'),
            'fecha_ingreso' => $this->request->getPost('fecha_ingreso') ?: date('Y-m-d'),
            'puede_crear_actas' => $this->request->getPost('puede_crear_actas') ? 1 : 0,
            'puede_cerrar_actas' => $this->request->getPost('puede_cerrar_actas') ? 1 : 0
        ];

        // Presidente y secretario pueden crear y cerrar actas por defecto
        if (in_array($data['rol_comite'], ['presidente', 'secretario'])) {
            $data['puede_crear_actas'] = 1;
            $data['puede_cerrar_actas'] = 1;
        }

        $idMiembro = $this->miembroModel->insert($data, true);

        if ($idMiembro) {
            $mensaje = 'Miembro agregado exitosamente';

            // Crear usuario en tbl_usuarios si tiene email
            if (!empty($data['email'])) {
                $userModel = new UserModel();
                $existeUsuario = $userModel->findByEmail($data['email']);

                if (!$existeUsuario) {
                    // Generar password temporal
                    $passwordTemp = $this->generarPasswordSeguro();

                    $datosUsuario = [
                        'email' => $data['email'],
                        'password' => $passwordTemp,
                        'nombre_completo' => $data['nombre_completo'],
                        'tipo_usuario' => 'miembro',
                        'id_entidad' => $comite['id_cliente'],
                        'estado' => 'activo'
                    ];

                    $idUsuario = $userModel->createUser($datosUsuario);

                    if ($idUsuario) {
                        // Obtener datos del cliente
                        $clienteModel = new ClientModel();
                        $cliente = $clienteModel->find($comite['id_cliente']);

                        // Enviar credenciales por email
                        $emailEnviado = $this->enviarCredencialesMiembro(
                            $data['email'],
                            $data['nombre_completo'],
                            $passwordTemp,
                            $cliente['nombre_cliente'] ?? 'la empresa',
                            $comite['tipo_nombre'] ?? 'Comité SST'
                        );

                        if ($emailEnviado) {
                            $mensaje .= '. Credenciales enviadas a ' . $data['email'];
                        } else {
                            $mensaje .= '. Usuario creado pero error al enviar email. Password: ' . $passwordTemp;
                        }
                    } else {
                        // Error al crear usuario - mostrar errores de validación
                        $errores = $userModel->getLastErrors();
                        $errorMsg = !empty($errores) ? implode(', ', $errores) : 'Error desconocido';
                        $mensaje .= '. ERROR al crear usuario: ' . $errorMsg;
                        log_message('error', "Error creando usuario miembro: {$errorMsg}");
                    }
                } else {
                    $mensaje .= '. Ya existe un usuario con ese email.';
                }
            }

            return redirect()->to("/actas/{$comite['id_cliente']}/comite/{$idComite}")
                           ->with('success', $mensaje);
        }

        return redirect()->back()->with('error', 'Error al agregar miembro')->withInput();
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
     * Enviar credenciales de acceso a miembro del comité
     */
    private function enviarCredencialesMiembro(string $email, string $nombre, string $password, string $nombreEmpresa, string $tipoComite): bool
    {
        $loginUrl = base_url('/login');

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px;'>Bienvenido al {$tipoComite}</h1>
            </div>
            <div style='padding: 30px; background: #f8fafc;'>
                <p style='color: #334155; font-size: 16px;'>Hola <strong>{$nombre}</strong>,</p>
                <p style='color: #334155; font-size: 14px;'>
                    Has sido registrado como miembro del <strong>{$tipoComite}</strong> de <strong>{$nombreEmpresa}</strong>.
                </p>
                <p style='color: #334155; font-size: 14px;'>
                    A continuacion te compartimos tus credenciales de acceso al sistema:
                </p>
                <div style='background: #e7f3ff; border: 1px solid #0d6efd; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                    <p style='margin: 5px 0; color: #334155;'><strong>Usuario:</strong> {$email}</p>
                    <p style='margin: 5px 0; color: #334155;'><strong>Contraseña:</strong> <code style='background: #fff; padding: 2px 8px; border-radius: 4px; font-size: 16px;'>{$password}</code></p>
                </div>
                <p style='color: #dc3545; font-size: 12px;'>
                    <strong>Importante:</strong> Te recomendamos cambiar tu contraseña despues del primer inicio de sesion.
                </p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$loginUrl}' style='background: #0d6efd; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>
                        Iniciar Sesion
                    </a>
                </div>
                <p style='color: #334155; font-size: 14px;'>
                    Desde el sistema podras:
                </p>
                <ul style='color: #334155; font-size: 14px;'>
                    <li>Ver las actas de reuniones</li>
                    <li>Consultar tus compromisos asignados</li>
                    <li>Firmar actas pendientes</li>
                </ul>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>
                    EnterpriseSST - Sistema de Gestion de Seguridad y Salud en el Trabajo
                </p>
            </div>
        </div>
        ";

        try {
            $emailObj = new \SendGrid\Mail\Mail();
            $emailObj->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $emailObj->setSubject("Credenciales de Acceso - {$tipoComite} - {$nombreEmpresa}");
            $emailObj->addTo($email, $nombre);
            $emailObj->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($emailObj);

            $statusCode = $response->statusCode();
            log_message('info', "SendGrid credenciales miembro enviado a {$email} - Status: {$statusCode}");

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error enviando credenciales miembro via SendGrid: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formulario para editar miembro del comité
     */
    public function editarMiembro(int $idComite, int $idMiembro)
    {
        $comite = $this->comiteModel->getConDetalles($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        $miembro = $this->miembroModel->find($idMiembro);

        if (!$miembro || $miembro['id_comite'] != $idComite) {
            return redirect()->back()->with('error', 'Miembro no encontrado');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        // Obtener responsables SST disponibles
        $responsableModel = new ResponsableSSTModel();
        $todosResponsables = $responsableModel->getDisponiblesParaComite($comite['id_cliente']);

        // Obtener miembros actuales del comité
        $miembrosActuales = $this->miembroModel->getActivosPorComite($idComite);

        return view('actas/editar_miembro', [
            'cliente' => $cliente,
            'comite' => $comite,
            'miembro' => $miembro,
            'todosResponsables' => $todosResponsables,
            'miembrosActuales' => $miembrosActuales
        ]);
    }

    /**
     * Actualizar miembro del comité
     */
    public function actualizarMiembro(int $idComite, int $idMiembro)
    {
        $comite = $this->comiteModel->find($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        $miembro = $this->miembroModel->find($idMiembro);

        if (!$miembro || $miembro['id_comite'] != $idComite) {
            return redirect()->back()->with('error', 'Miembro no encontrado');
        }

        $data = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'tipo_documento' => $this->request->getPost('tipo_documento') ?: 'CC',
            'numero_documento' => $this->request->getPost('numero_documento'),
            'cargo' => $this->request->getPost('cargo'),
            'area_dependencia' => $this->request->getPost('area_dependencia'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'representacion' => $this->request->getPost('representacion'),
            'tipo_miembro' => $this->request->getPost('tipo_miembro'),
            'rol_comite' => $this->request->getPost('rol_comite'),
            'puede_crear_actas' => $this->request->getPost('puede_crear_actas') ? 1 : 0,
            'puede_cerrar_actas' => $this->request->getPost('puede_cerrar_actas') ? 1 : 0
        ];

        // Presidente y secretario pueden crear y cerrar actas por defecto
        if (in_array($data['rol_comite'], ['presidente', 'secretario'])) {
            $data['puede_crear_actas'] = 1;
            $data['puede_cerrar_actas'] = 1;
        }

        if ($this->miembroModel->update($idMiembro, $data)) {
            return redirect()->to("/actas/{$comite['id_cliente']}/comite/{$idComite}")
                           ->with('success', 'Miembro actualizado exitosamente');
        }

        return redirect()->back()->with('error', 'Error al actualizar miembro')->withInput();
    }

    /**
     * Retirar miembro (solo consultor)
     */
    public function retirarMiembro(int $idMiembro)
    {
        $miembro = $this->miembroModel->find($idMiembro);

        if (!$miembro) {
            return $this->response->setJSON(['success' => false, 'message' => 'Miembro no encontrado']);
        }

        $motivo = $this->request->getPost('motivo');

        if ($this->miembroModel->retirarMiembro($idMiembro, $motivo)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Miembro retirado exitosamente']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al retirar miembro']);
    }

    /**
     * Reenviar credenciales de acceso a miembro del comité
     */
    public function reenviarAccesoMiembro(int $idMiembro)
    {
        $miembro = $this->miembroModel->find($idMiembro);

        if (!$miembro) {
            return $this->response->setJSON(['success' => false, 'message' => 'Miembro no encontrado']);
        }

        if (empty($miembro['email'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'El miembro no tiene email registrado']);
        }

        if ($miembro['estado'] !== 'activo') {
            return $this->response->setJSON(['success' => false, 'message' => 'El miembro no esta activo']);
        }

        $comite = $this->comiteModel->getConDetalles($miembro['id_comite']);
        $userModel = new UserModel();
        $usuario = $userModel->findByEmail($miembro['email']);

        // Generar nueva contraseña
        $passwordTemp = $this->generarPasswordSeguro();

        if ($usuario) {
            // Actualizar contraseña del usuario existente
            $userModel->updateUser($usuario['id_usuario'], ['password' => $passwordTemp]);
        } else {
            // Crear usuario si no existe
            $datosUsuario = [
                'email' => $miembro['email'],
                'password' => $passwordTemp,
                'nombre_completo' => $miembro['nombre_completo'],
                'tipo_usuario' => 'miembro',
                'id_entidad' => $comite['id_cliente'],
                'estado' => 'activo'
            ];
            $userModel->createUser($datosUsuario);
        }

        // Obtener datos del cliente
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $emailEnviado = $this->enviarCredencialesMiembro(
            $miembro['email'],
            $miembro['nombre_completo'],
            $passwordTemp,
            $cliente['nombre_cliente'] ?? 'la empresa',
            $comite['tipo_nombre'] ?? 'Comité SST'
        );

        if ($emailEnviado) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Credenciales enviadas a {$miembro['email']}"
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al enviar el email. Password temporal: ' . $passwordTemp
        ]);
    }

    /**
     * Vista instructivo para preparar reunión (paso previo a crear acta)
     */
    public function prepararReunion(int $idComite)
    {
        $comite = $this->comiteModel->getConDetalles($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        // Obtener compromisos pendientes para mostrar en el instructivo
        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($idComite);

        return view('actas/preparar_reunion', [
            'cliente' => $cliente,
            'comite' => $comite,
            'compromisosPendientes' => $compromisosPendientes
        ]);
    }

    /**
     * Formulario para crear nueva acta
     */
    public function nuevaActa(int $idComite)
    {
        $comite = $this->comiteModel->getConDetalles($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $miembros = $this->miembroModel->getActivosPorComite($idComite);
        $plantilla = $this->plantillaModel->getDefaultPorTipo($comite['id_tipo']);
        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($idComite);

        // Obtener última acta para próxima reunión sugerida
        $ultimaActa = $this->actaModel->where('id_comite', $idComite)
                                      ->orderBy('fecha_reunion', 'DESC')
                                      ->first();

        // Obtener datos del instructivo (si vienen por GET)
        $datosPreparados = [
            'objetivo' => $this->request->getGet('objetivo'),
            'puntos' => $this->request->getGet('punto') ?? [],
            'fecha' => $this->request->getGet('fecha') ?? date('Y-m-d'),
            'hora_inicio' => $this->request->getGet('hora_inicio') ?? '08:00',
            'hora_fin' => $this->request->getGet('hora_fin') ?? '09:00',
            'lugar' => $this->request->getGet('lugar') ?? '',
            'modalidad' => $this->request->getGet('modalidad') ?? 'presencial',
            'enlace_virtual' => $this->request->getGet('enlace_virtual') ?? ''
        ];

        return view('actas/nueva_acta', [
            'cliente' => $cliente,
            'comite' => $comite,
            'miembros' => $miembros,
            'plantilla' => $plantilla,
            'compromisosPendientes' => $compromisosPendientes,
            'ultimaActa' => $ultimaActa,
            'datosPreparados' => $datosPreparados
        ]);
    }

    /**
     * Guardar nueva acta
     */
    public function guardarActa(int $idComite)
    {
        $comite = $this->comiteModel->find($idComite);

        if (!$comite) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        // Preparar orden del día
        $ordenDelDia = [];
        $puntosOrden = $this->request->getPost('orden_punto') ?? [];
        $temasOrden = $this->request->getPost('orden_tema') ?? [];

        foreach ($puntosOrden as $i => $punto) {
            if (!empty($temasOrden[$i])) {
                $ordenDelDia[] = [
                    'punto' => (int) $punto,
                    'tema' => $temasOrden[$i]
                ];
            }
        }

        $data = [
            'id_comite' => $idComite,
            'id_cliente' => $comite['id_cliente'],
            'tipo_acta' => $this->request->getPost('tipo_acta') ?: 'ordinaria',
            'fecha_reunion' => $this->request->getPost('fecha_reunion'),
            'hora_inicio' => $this->request->getPost('hora_inicio'),
            'hora_fin' => $this->request->getPost('hora_fin'),
            'lugar' => $this->request->getPost('lugar'),
            'modalidad' => $this->request->getPost('modalidad') ?: 'presencial',
            'enlace_virtual' => $this->request->getPost('enlace_virtual'),
            'orden_del_dia' => $ordenDelDia,
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha'),
            'proxima_reunion_hora' => $this->request->getPost('proxima_reunion_hora'),
            'proxima_reunion_lugar' => $this->request->getPost('proxima_reunion_lugar'),
            'created_by' => session()->get('user_id')
        ];

        $idActa = $this->actaModel->crearActa($data);

        if ($idActa) {
            // Agregar asistentes desde miembros
            $this->asistentesModel->agregarDesdeMiembros($idActa, $idComite);

            // Procesar asistencia marcada
            $asistio = $this->request->getPost('asistio') ?? [];
            $asistentes = $this->asistentesModel->getByActa($idActa);

            foreach ($asistentes as $asistente) {
                if (!in_array($asistente['id_miembro'], $asistio)) {
                    $this->asistentesModel->marcarAusente($asistente['id_asistente']);
                }
            }

            // Calcular quórum
            $quorumPresente = $this->asistentesModel->calcularQuorumPresente($idActa);
            $hayQuorum = $this->asistentesModel->hayQuorum($idActa);

            $this->actaModel->update($idActa, [
                'quorum_presente' => $quorumPresente,
                'hay_quorum' => $hayQuorum ? 1 : 0
            ]);

            // Procesar compromisos agregados durante la creación
            $compDescripciones = $this->request->getPost('compromiso_descripcion') ?? [];
            $compResponsables = $this->request->getPost('compromiso_responsable_id') ?? [];
            $compFechas = $this->request->getPost('compromiso_fecha_vencimiento') ?? [];

            foreach ($compDescripciones as $i => $descripcion) {
                if (!empty($descripcion) && !empty($compResponsables[$i])) {
                    // Obtener datos del miembro responsable
                    $miembro = $this->miembroModel->find($compResponsables[$i]);

                    $this->compromisosModel->crearCompromiso([
                        'id_acta' => $idActa,
                        'id_comite' => $idComite,
                        'id_cliente' => $comite['id_cliente'],
                        'descripcion' => $descripcion,
                        'responsable_nombre' => $miembro['nombre_completo'] ?? '',
                        'responsable_email' => $miembro['email'] ?? '',
                        'responsable_id_miembro' => $compResponsables[$i],
                        'fecha_compromiso' => $this->request->getPost('fecha_reunion'),
                        'fecha_vencimiento' => $compFechas[$i] ?? date('Y-m-d', strtotime('+30 days'))
                    ]);
                }
            }

            $cantCompromisos = count(array_filter($compDescripciones));
            $msgExtra = $cantCompromisos > 0 ? " Se registraron {$cantCompromisos} compromiso(s)." : '';

            return redirect()->to("/actas/editar/{$idActa}")
                           ->with('success', 'Acta creada.' . $msgExtra . ' Complete el desarrollo de la reunión.');
        }

        return redirect()->back()->with('error', 'Error al crear el acta')->withInput();
    }

    /**
     * Editar acta (desarrollo de la reunión)
     */
    public function editarActa(int $idActa)
    {
        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Esta acta ya no puede editarse');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($acta['id_comite']);

        // Obtener miembros del comité y asistentes del acta
        $miembros = $this->miembroModel->getActivosPorComite($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);

        return view('actas/editar_acta', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'compromisosPendientes' => $compromisosPendientes,
            'miembros' => $miembros,
            'asistentes' => $asistentes
        ]);
    }

    /**
     * Actualizar desarrollo del acta
     */
    public function actualizarActa(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);

        if (!$acta || !in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Acta no encontrada o no editable');
        }

        // Preparar desarrollo
        $desarrollo = [];
        $puntosDesarrollo = $this->request->getPost('desarrollo_punto') ?? [];
        $descripcionesDesarrollo = $this->request->getPost('desarrollo_descripcion') ?? [];
        $decisionesDesarrollo = $this->request->getPost('desarrollo_decision') ?? [];

        foreach ($puntosDesarrollo as $i => $punto) {
            $desarrollo[] = [
                'punto' => (int) $punto,
                'descripcion' => $descripcionesDesarrollo[$i] ?? '',
                'decision' => $decisionesDesarrollo[$i] ?? ''
            ];
        }

        $data = [
            'desarrollo' => json_encode($desarrollo),
            'conclusiones' => $this->request->getPost('conclusiones'),
            'observaciones' => $this->request->getPost('observaciones'),
            'estado' => 'en_edicion'
        ];

        $this->actaModel->update($idActa, $data);

        // Procesar compromisos nuevos
        $compromisosDesc = $this->request->getPost('compromiso_descripcion') ?? [];
        $compromisosResp = $this->request->getPost('compromiso_responsable') ?? [];
        $compromisosEmail = $this->request->getPost('compromiso_email') ?? [];
        $compromisosVence = $this->request->getPost('compromiso_vencimiento') ?? [];

        foreach ($compromisosDesc as $i => $desc) {
            if (!empty($desc) && !empty($compromisosResp[$i])) {
                $this->compromisosModel->crearCompromiso([
                    'id_acta' => $idActa,
                    'id_comite' => $acta['id_comite'],
                    'id_cliente' => $acta['id_cliente'],
                    'descripcion' => $desc,
                    'responsable_nombre' => $compromisosResp[$i],
                    'responsable_email' => $compromisosEmail[$i] ?? null,
                    'fecha_compromiso' => $acta['fecha_reunion'],
                    'fecha_vencimiento' => $compromisosVence[$i] ?? date('Y-m-d', strtotime('+30 days'))
                ]);
            }
        }

        return redirect()->to("/actas/editar/{$idActa}")
                       ->with('success', 'Acta actualizada correctamente');
    }

    /**
     * Ver acta (solo lectura)
     */
    public function verActa(int $idActa)
    {
        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);

        return view('actas/ver_acta', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'asistentes' => $asistentes
        ]);
    }

    /**
     * Cerrar acta y enviar a firmas
     */
    public function cerrarActa(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);

        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acta no encontrada']);
        }

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Esta acta ya fue cerrada']);
        }

        // Verificar quórum
        if (!$this->asistentesModel->hayQuorum($idActa)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No hay quórum suficiente para cerrar el acta']);
        }

        $cerradaPor = session()->get('user_id');

        if ($this->actaModel->cerrarYEnviarAFirmas($idActa, $cerradaPor)) {
            // Programar notificaciones de firma
            $asistentes = $this->asistentesModel->getPresentes($idActa);
            $actaActualizada = $this->actaModel->find($idActa);

            foreach ($asistentes as $asistente) {
                if (!empty($asistente['email'])) {
                    $this->notificacionModel->programarSolicitudFirma(
                        $idActa,
                        $asistente['id_asistente'],
                        $asistente['email'],
                        $asistente['nombre_completo'],
                        $acta['id_cliente'],
                        $actaActualizada['numero_acta']
                    );

                    // Actualizar que se envió notificación
                    $this->asistentesModel->update($asistente['id_asistente'], [
                        'notificacion_enviada_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Acta cerrada. Se enviarán las solicitudes de firma.',
                'redirect' => "/actas/ver/{$idActa}"
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al cerrar el acta']);
    }

    /**
     * Enviar acta a firmas (alias de cerrarActa para formulario POST directo)
     */
    public function enviarAFirmas(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Esta acta ya fue cerrada');
        }

        // Verificar quórum
        if (!$this->asistentesModel->hayQuorum($idActa)) {
            return redirect()->back()->with('error', 'No hay quórum suficiente para cerrar el acta');
        }

        $cerradaPor = session()->get('user_id');

        if ($this->actaModel->cerrarYEnviarAFirmas($idActa, $cerradaPor)) {
            $asistentes = $this->asistentesModel->getPresentes($idActa);
            $actaActualizada = $this->actaModel->find($idActa);
            $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

            $enviados = 0;
            $emailsEnviados = [];
            $emailsFallidos = [];
            $sinEmail = [];

            foreach ($asistentes as $asistente) {
                if (!empty($asistente['email'])) {
                    // Generar token de firma
                    $token = bin2hex(random_bytes(32));
                    $this->asistentesModel->update($asistente['id_asistente'], [
                        'token_firma' => $token,
                        'token_expira' => date('Y-m-d H:i:s', strtotime('+7 days')),
                        'estado_firma' => 'pendiente'
                    ]);

                    // Enviar email real con SendGrid
                    $emailEnviado = $this->enviarEmailFirmaActa(
                        $asistente,
                        $token,
                        $actaActualizada,
                        $comite
                    );

                    if ($emailEnviado) {
                        $this->asistentesModel->update($asistente['id_asistente'], [
                            'notificacion_enviada_at' => date('Y-m-d H:i:s')
                        ]);
                        $enviados++;
                        $emailsEnviados[] = $asistente['email'];
                    } else {
                        $emailsFallidos[] = $asistente['email'];
                    }
                } else {
                    $sinEmail[] = $asistente['nombre_completo'];
                }
            }

            // Construir mensaje detallado
            $mensaje = "Acta cerrada y enviada a firmas.";
            if (!empty($emailsEnviados)) {
                $mensaje .= " Emails enviados a: " . implode(', ', $emailsEnviados) . ".";
            }
            if (!empty($emailsFallidos)) {
                $mensaje .= " FALLIDOS: " . implode(', ', $emailsFallidos) . ".";
            }
            if (!empty($sinEmail)) {
                $mensaje .= " Sin email: " . implode(', ', $sinEmail) . ".";
            }

            return redirect()->to("/actas/comite/{$acta['id_comite']}/acta/{$idActa}/firmas")
                           ->with('success', $mensaje);
        }

        return redirect()->back()->with('error', 'Error al cerrar el acta');
    }

    /**
     * Enviar email de solicitud de firma para acta usando SendGrid
     */
    private function enviarEmailFirmaActa(array $asistente, string $token, array $acta, array $comite): bool
    {
        $urlFirma = base_url("acta/firmar/{$token}");
        $tipoComite = $comite['tipo_nombre'] ?? 'Comité';
        $fechaReunion = date('d/m/Y', strtotime($acta['fecha_reunion']));

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma - Acta de Reunión</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$asistente['nombre_completo']}</strong>,</p>
                <p>Se requiere su firma electrónica para el acta de la reunión del <strong>{$tipoComite}</strong>.</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3B82F6;'>
                    <p style='margin: 5px 0;'><strong>Acta N°:</strong> {$acta['numero_acta']}</p>
                    <p style='margin: 5px 0;'><strong>Comité:</strong> {$tipoComite}</p>
                    <p style='margin: 5px 0;'><strong>Fecha reunión:</strong> {$fechaReunion}</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #3B82F6; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Acta
                    </a>
                </div>

                <p style='color: #666; font-size: 12px;'>O copie este enlace en su navegador:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 12px;'>{$urlFirma}</p>

                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>
                    <strong>Importante:</strong> Este enlace es personal e intransferible. No lo comparta con nadie.<br>
                    El enlace expirará en 7 días.
                </p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>
                    EnterpriseSST - Sistema de Gestión de Seguridad y Salud en el Trabajo
                </p>
            </div>
        </div>
        ";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $email->setSubject("Firma requerida: Acta {$acta['numero_acta']} - {$tipoComite}");
            $email->addTo($asistente['email'], $asistente['nombre_completo']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            $statusCode = $response->statusCode();
            log_message('info', "SendGrid acta firma email enviado a {$asistente['email']} - Status: {$statusCode}");

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email de firma de acta via SendGrid: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Estado de firmas del acta
     */
    public function estadoFirmas(int $idActa)
    {
        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

        // Obtener asistentes del acta para mostrar estado de firmas
        $asistentes = $this->asistentesModel->getByActa($idActa);

        return view('actas/estado_firmas', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'asistentes' => $asistentes
        ]);
    }

    /**
     * Reenviar notificación de firma
     */
    public function reenviarNotificacionFirma(int $idAsistente)
    {
        $asistente = $this->asistentesModel->find($idAsistente);

        if (!$asistente || $asistente['estado_firma'] !== 'pendiente') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede reenviar']);
        }

        $acta = $this->actaModel->find($asistente['id_acta']);

        // Generar nuevo token
        $token = bin2hex(random_bytes(32));
        $this->asistentesModel->update($idAsistente, [
            'token_firma' => $token,
            'token_expira' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);

        // Programar notificación
        $this->notificacionModel->programarRecordatorioFirma(
            $asistente['id_acta'],
            $idAsistente,
            $asistente['email'],
            $asistente['nombre_completo'],
            $acta['id_cliente'],
            $acta['numero_acta']
        );

        $this->asistentesModel->update($idAsistente, [
            'recordatorio_enviado_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Recordatorio programado']);
    }

    /**
     * Reenviar notificación de firma a todos los pendientes
     */
    public function reenviarTodos(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        if ($acta['estado'] !== 'pendiente_firma') {
            return redirect()->back()->with('error', 'El acta no está en estado de firma pendiente');
        }

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);
        $enviados = 0;
        $emailsEnviados = [];
        $emailsFallidos = [];

        foreach ($asistentes as $asistente) {
            // Solo a los que asistieron y no han firmado
            if ($asistente['asistio'] && empty($asistente['firma_fecha']) && !empty($asistente['email'])) {
                // Generar nuevo token
                $token = bin2hex(random_bytes(32));
                $this->asistentesModel->update($asistente['id_asistente'], [
                    'token_firma' => $token,
                    'token_expira' => date('Y-m-d H:i:s', strtotime('+7 days')),
                    'estado_firma' => 'pendiente'
                ]);

                // Enviar email real con SendGrid
                $emailEnviado = $this->enviarEmailFirmaActa($asistente, $token, $acta, $comite);

                if ($emailEnviado) {
                    $this->asistentesModel->update($asistente['id_asistente'], [
                        'recordatorio_enviado_at' => date('Y-m-d H:i:s')
                    ]);
                    $enviados++;
                    $emailsEnviados[] = $asistente['email'];
                } else {
                    $emailsFallidos[] = $asistente['email'];
                }
            }
        }

        if ($enviados > 0) {
            $mensaje = "Emails enviados a: " . implode(', ', $emailsEnviados);
            if (!empty($emailsFallidos)) {
                $mensaje .= ". FALLIDOS: " . implode(', ', $emailsFallidos);
            }
            return redirect()->back()->with('success', $mensaje);
        }

        if (!empty($emailsFallidos)) {
            return redirect()->back()->with('error', 'Error al enviar a: ' . implode(', ', $emailsFallidos));
        }

        return redirect()->back()->with('info', 'No hay asistentes pendientes de firma con email');
    }

    /**
     * Reenviar notificación de firma a un asistente específico
     */
    public function reenviarAsistente(int $idActa, int $idAsistente)
    {
        $acta = $this->actaModel->find($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        $asistente = $this->asistentesModel->find($idAsistente);

        if (!$asistente || $asistente['id_acta'] != $idActa) {
            return redirect()->back()->with('error', 'Asistente no encontrado');
        }

        if (!empty($asistente['firma_fecha'])) {
            return redirect()->back()->with('error', 'Este asistente ya firmó el acta');
        }

        if (empty($asistente['email'])) {
            return redirect()->back()->with('error', 'El asistente no tiene email registrado');
        }

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

        // Generar nuevo token
        $token = bin2hex(random_bytes(32));
        $this->asistentesModel->update($idAsistente, [
            'token_firma' => $token,
            'token_expira' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'estado_firma' => 'pendiente'
        ]);

        // Enviar email real con SendGrid
        $emailEnviado = $this->enviarEmailFirmaActa($asistente, $token, $acta, $comite);

        if ($emailEnviado) {
            $this->asistentesModel->update($idAsistente, [
                'recordatorio_enviado_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->back()->with('success', "Email enviado a {$asistente['nombre_completo']}");
        }

        return redirect()->back()->with('error', 'Error al enviar el email. Verifique la configuración de SendGrid.');
    }

    /**
     * Dashboard de compromisos
     */
    public function compromisos(int $idCliente)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $pendientes = $this->compromisosModel->getByCliente($idCliente, 'pendiente');
        $enProceso = $this->compromisosModel->getByCliente($idCliente, 'en_proceso');
        $vencidos = $this->compromisosModel->getVencidos($idCliente);
        $proximosVencer = $this->compromisosModel->getProximosAVencer(7, $idCliente);

        return view('actas/compromisos', [
            'cliente' => $cliente,
            'pendientes' => $pendientes,
            'enProceso' => $enProceso,
            'vencidos' => $vencidos,
            'proximosVencer' => $proximosVencer
        ]);
    }

    /**
     * Dashboard de compromisos por comité
     */
    public function compromisosComite(int $idCliente, int $idComite)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $comite = $this->comiteModel->getConDetalles($idComite);

        if (!$comite || $comite['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Comité no encontrado');
        }

        // Obtener filtros
        $filtros = [
            'estado' => $this->request->getGet('estado'),
            'responsable' => $this->request->getGet('responsable'),
            'desde' => $this->request->getGet('desde'),
            'hasta' => $this->request->getGet('hasta')
        ];

        // Obtener compromisos del comité
        $compromisos = $this->compromisosModel->getByComite($idComite);

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $compromisos = array_filter($compromisos, fn($c) => $c['estado'] === $filtros['estado']);
        }
        if (!empty($filtros['responsable'])) {
            $compromisos = array_filter($compromisos, fn($c) => ($c['responsable_id_miembro'] ?? '') == $filtros['responsable']);
        }

        // Calcular estadísticas
        $todosCompromisos = $this->compromisosModel->getByComite($idComite);
        $stats = [
            'pendientes' => count(array_filter($todosCompromisos, fn($c) => $c['estado'] === 'pendiente')),
            'en_progreso' => count(array_filter($todosCompromisos, fn($c) => $c['estado'] === 'en_proceso')),
            'completados' => count(array_filter($todosCompromisos, fn($c) => $c['estado'] === 'cumplido')),
            'vencidos' => count(array_filter($todosCompromisos, fn($c) => $c['estado'] === 'vencido'))
        ];

        // Obtener miembros para filtro
        $miembros = $this->miembroModel->getActivosPorComite($idComite);

        return view('actas/compromisos', [
            'cliente' => $cliente,
            'comite' => $comite,
            'compromisos' => array_values($compromisos),
            'stats' => $stats,
            'filtros' => $filtros,
            'miembros' => $miembros
        ]);
    }

    /**
     * Marcar compromiso como completado
     */
    public function completarCompromiso(int $idCompromiso)
    {
        $compromiso = $this->compromisosModel->find($idCompromiso);

        if (!$compromiso) {
            return redirect()->back()->with('error', 'Compromiso no encontrado');
        }

        $this->compromisosModel->actualizarEstado($idCompromiso, 'cumplido', 100);

        return redirect()->back()->with('success', 'Compromiso marcado como completado');
    }

    /**
     * Actualizar compromiso (desde modal o AJAX)
     */
    public function actualizarCompromiso(int $idCompromiso)
    {
        $compromiso = $this->compromisosModel->find($idCompromiso);

        if (!$compromiso) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Compromiso no encontrado']);
            }
            return redirect()->back()->with('error', 'Compromiso no encontrado');
        }

        // Datos del formulario
        $data = [
            'descripcion' => $this->request->getPost('descripcion'),
            'responsable_id_miembro' => $this->request->getPost('id_responsable'),
            'fecha_vencimiento' => $this->request->getPost('fecha_vencimiento'),
            'estado' => $this->request->getPost('estado'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        // Filtrar valores nulos
        $data = array_filter($data, fn($v) => $v !== null);

        // Si se marca como cumplido
        $estado = $this->request->getPost('estado');
        $evidencia = $this->request->getPost('evidencia');

        if ($estado === 'cumplido' && !empty($evidencia)) {
            $this->compromisosModel->cerrarConEvidencia(
                $idCompromiso,
                $evidencia,
                null,
                session()->get('user_name') ?? 'Usuario'
            );
        } else {
            $this->compromisosModel->update($idCompromiso, $data);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Compromiso actualizado']);
        }

        return redirect()->back()->with('success', 'Compromiso actualizado correctamente');
    }

    /**
     * Generar código de documento para actas según convención de control documental
     * El código identifica el TIPO de documento, NO incluye consecutivo
     * Formato: ACT-{tipo_abreviado}
     * Ejemplo: ACT-COP, ACT-CCL, ACT-BRI
     */
    protected function generarCodigoDocumento(string $tipoComite): string
    {
        // Mapeo de tipos de comité a códigos abreviados según estándar
        $codigosComite = [
            'COPASST' => 'COP',
            'COCOLAB' => 'CCL',
            'BRIGADA' => 'BRI',
            'GENERAL' => 'GEN'
        ];

        $codigoTipo = $codigosComite[$tipoComite] ?? substr($tipoComite, 0, 3);

        return 'ACT-' . $codigoTipo;
    }

    /**
     * Exportar acta a PDF
     */
    public function exportarPDF(int $idActa)
    {
        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);
        $compromisos = $this->compromisosModel->getByActa($idActa) ?? [];

        // Verificar quórum
        $quorumAlcanzado = $this->asistentesModel->hayQuorum($idActa);

        // Obtener código de documento desde BD (o generar si no existe)
        $codigoDocumento = $acta['codigo_documento'] ?? null;
        $versionDocumento = $acta['version_documento'] ?? '001';

        if (empty($codigoDocumento)) {
            // Fallback: generar código base si no está en BD
            $tipoComite = $comite['tipo_codigo'] ?? $comite['codigo'] ?? 'GENERAL';
            $codigoDocumento = $this->generarCodigoDocumento($tipoComite);
        }

        // Convertir logo a base64 para PDF
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        $cliente['logo'] = $logoBase64;

        // Generar HTML para PDF
        $html = view('actas/pdf_acta', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'asistentes' => $asistentes,
            'compromisos' => $compromisos,
            'quorumAlcanzado' => $quorumAlcanzado,
            'codigoDocumento' => $codigoDocumento,
            'versionDocumento' => $versionDocumento
        ]);

        // Usar Dompdf o similar
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = "{$codigoDocumento}_{$acta['numero_acta']}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($dompdf->output());
    }

    /**
     * Exportar acta a Word
     */
    public function exportarWord(int $idActa)
    {
        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);
        $compromisos = $this->compromisosModel->getByActa($idActa) ?? [];

        // Verificar quórum
        $quorumAlcanzado = $this->asistentesModel->hayQuorum($idActa);

        // Obtener código de documento desde BD (o generar si no existe)
        $codigoDocumento = $acta['codigo_documento'] ?? null;
        $versionDocumento = $acta['version_documento'] ?? '001';

        if (empty($codigoDocumento)) {
            // Fallback: generar código base si no está en BD
            $tipoComite = $comite['tipo_codigo'] ?? $comite['codigo'] ?? 'GENERAL';
            $codigoDocumento = $this->generarCodigoDocumento($tipoComite);
        }

        // Convertir logo a base64 para Word
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        $cliente['logo'] = $logoBase64;

        // Generar HTML para Word (usa template específico para Word)
        $html = view('actas/word_acta', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'asistentes' => $asistentes,
            'compromisos' => $compromisos,
            'quorumAlcanzado' => $quorumAlcanzado,
            'codigoDocumento' => $codigoDocumento,
            'versionDocumento' => $versionDocumento
        ]);

        $filename = "{$codigoDocumento}_{$acta['numero_acta']}.doc";

        return $this->response
            ->setHeader('Content-Type', 'application/msword')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($html);
    }

    /**
     * Obtener imagen de firma de un asistente
     */
    public function firmaImagen(int $idAsistente)
    {
        $asistente = $this->asistentesModel->find($idAsistente);

        if (!$asistente || empty($asistente['firma_imagen'])) {
            // Retornar imagen placeholder o 404
            return $this->response
                ->setStatusCode(404)
                ->setBody('Firma no encontrada');
        }

        $firmaBase64 = $asistente['firma_imagen'];

        // Si es base64 con prefijo data:image
        if (strpos($firmaBase64, 'data:image') === 0) {
            // Extraer tipo mime y datos
            preg_match('/data:image\/(\w+);base64,(.*)/', $firmaBase64, $matches);
            $tipo = $matches[1] ?? 'png';
            $datos = base64_decode($matches[2] ?? '');

            return $this->response
                ->setHeader('Content-Type', 'image/' . $tipo)
                ->setHeader('Cache-Control', 'max-age=3600')
                ->setBody($datos);
        }

        // Si es solo base64 sin prefijo
        $datos = base64_decode($firmaBase64);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'max-age=3600')
            ->setBody($datos);
    }
}
