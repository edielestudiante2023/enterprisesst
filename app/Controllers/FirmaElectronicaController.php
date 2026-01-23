<?php

namespace App\Controllers;

use App\Models\DocFirmaModel;
use App\Models\DocDocumentoModel;
use App\Models\DocVersionModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use CodeIgniter\Controller;

/**
 * Firma Electronica tipo DocuSeal
 * Flujo: Delegado SST (opcional) → Representante Legal
 */
class FirmaElectronicaController extends Controller
{
    protected $firmaModel;
    protected $documentoModel;
    protected $versionModel;
    protected $clienteModel;
    protected $contextoModel;

    public function __construct()
    {
        $this->firmaModel = new DocFirmaModel();
        $this->documentoModel = new DocDocumentoModel();
        $this->versionModel = new DocVersionModel();
        $this->clienteModel = new ClientModel();
        $this->contextoModel = new ClienteContextoSstModel();
    }

    /**
     * Solicitar firma para un documento
     * Muestra formulario con flujo segun contexto del cliente
     */
    public function solicitar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        // Verificar estado del documento
        if (!in_array($documento['estado'], ['completado', 'en_revision'])) {
            return redirect()->back()->with('error', 'El documento debe estar completado para solicitar firmas');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);
        $estadoFirmas = $this->firmaModel->getEstadoFirmas($idDocumento);

        // Determinar flujo de firmas
        $requiereDelegado = (bool) ($contexto['requiere_delegado_sst'] ?? false);

        return view('firma/solicitar', [
            'documento' => $documento,
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estadoFirmas' => $estadoFirmas,
            'requiereDelegado' => $requiereDelegado
        ]);
    }

    /**
     * Crear solicitud de firma (POST)
     * Crea el flujo completo: Delegado SST (si aplica) → Representante Legal
     */
    public function crearSolicitud()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idDocumento = $this->request->getPost('id_documento');
        $documento = $this->documentoModel->find($idDocumento);
        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);

        $requiereDelegado = (bool) ($contexto['requiere_delegado_sst'] ?? false);
        $orden = 1;
        $solicitudesCreadas = [];

        // 1. Crear solicitud para Delegado SST (si aplica)
        if ($requiereDelegado && !empty($contexto['delegado_sst_email'])) {
            $datosDelegado = [
                'id_documento' => $idDocumento,
                'firmante_tipo' => 'delegado_sst',
                'firmante_email' => $contexto['delegado_sst_email'],
                'firmante_nombre' => $contexto['delegado_sst_nombre'],
                'firmante_cargo' => $contexto['delegado_sst_cargo'],
                'firmante_documento' => $contexto['delegado_sst_cedula'],
                'orden_firma' => $orden++,
                'estado' => 'pendiente'
            ];

            $idDelegado = $this->firmaModel->crearSolicitud($datosDelegado);
            if ($idDelegado) {
                $solicitudesCreadas[] = $this->firmaModel->find($idDelegado);
                $this->firmaModel->registrarAudit($idDelegado, 'solicitud_creada', [
                    'creado_por' => session()->get('id_usuario'),
                    'tipo' => 'delegado_sst'
                ]);
            }
        }

        // 2. Crear solicitud para Representante Legal (siempre)
        $datosRepLegal = [
            'id_documento' => $idDocumento,
            'firmante_tipo' => 'representante_legal',
            'firmante_email' => $contexto['representante_legal_email'],
            'firmante_nombre' => $contexto['representante_legal_nombre'],
            'firmante_cargo' => $contexto['representante_legal_cargo'] ?? 'Representante Legal',
            'firmante_documento' => $contexto['representante_legal_cedula'],
            'orden_firma' => $orden,
            'estado' => $requiereDelegado ? 'esperando' : 'pendiente' // Espera si hay delegado
        ];

        $idRepLegal = $this->firmaModel->crearSolicitud($datosRepLegal);
        if ($idRepLegal) {
            $solicitudesCreadas[] = $this->firmaModel->find($idRepLegal);
            $this->firmaModel->registrarAudit($idRepLegal, 'solicitud_creada', [
                'creado_por' => session()->get('id_usuario'),
                'tipo' => 'representante_legal'
            ]);
        }

        if (!empty($solicitudesCreadas)) {
            // Cambiar estado del documento
            $this->documentoModel->update($idDocumento, [
                'estado' => 'pendiente_firma'
            ]);

            // Enviar correo al primer firmante
            $primerFirmante = $solicitudesCreadas[0];
            $this->enviarCorreoFirma($primerFirmante, $documento);

            return redirect()->to("/firma/estado/{$idDocumento}")
                            ->with('success', 'Solicitud de firma enviada a ' . $primerFirmante['firmante_nombre']);
        }

        return redirect()->back()->with('error', 'Error al crear solicitud. Verifique que los datos de firmantes esten configurados en el contexto del cliente.');
    }

    /**
     * Enviar correo con enlace de firma
     */
    private function enviarCorreoFirma(array $solicitud, array $documento): bool
    {
        $email = \Config\Services::email();

        $urlFirma = base_url("firma/firmar/{$solicitud['token']}");
        $tipoFirmante = $solicitud['firmante_tipo'] === 'delegado_sst' ? 'Delegado SST' : 'Representante Legal';

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma Electronica</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$solicitud['firmante_nombre']}</strong>,</p>
                <p>Se requiere su firma electronica como <strong>{$tipoFirmante}</strong> para el siguiente documento del Sistema de Gestion de Seguridad y Salud en el Trabajo:</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p><strong>Documento:</strong> {$documento['nombre']}</p>
                    <p><strong>Codigo:</strong> {$documento['codigo']}</p>
                    <p><strong>Version:</strong> {$documento['version_actual']}</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #3B82F6; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Documento
                    </a>
                </div>

                <p style='color: #666; font-size: 12px;'>O copie este enlace en su navegador:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 12px;'>{$urlFirma}</p>

                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>
                    <strong>Importante:</strong> Este enlace es personal e intransferible. No lo comparta con nadie.<br>
                    El enlace expirara en 7 dias.
                </p>
            </div>
        </div>
        ";

        $email->setTo($solicitud['firmante_email']);
        $email->setSubject("Solicitud de Firma: {$documento['codigo']} - {$documento['nombre']}");
        $email->setMessage($mensaje);

        return $email->send();
    }

    /**
     * Vista pública para firmar (acceso por token)
     */
    public function firmar($token)
    {
        $validacion = $this->firmaModel->validarToken($token);

        if (!$validacion['valido']) {
            return view('firma/error', ['error' => $validacion['error']]);
        }

        $solicitud = $validacion['solicitud'];

        // Registrar que se abrió el link
        $this->firmaModel->registrarAudit($solicitud['id_solicitud'], 'link_abierto', [
            'ip' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ]);

        $documento = $this->documentoModel->getCompleto($solicitud['id_documento']);

        return view('firma/firmar', [
            'solicitud' => $solicitud,
            'documento' => $documento,
            'token' => $token
        ]);
    }

    /**
     * Procesar firma (POST público)
     */
    public function procesarFirma()
    {
        $token = $this->request->getPost('token');
        $validacion = $this->firmaModel->validarToken($token);

        if (!$validacion['valido']) {
            return $this->response->setJSON(['success' => false, 'error' => $validacion['error']]);
        }

        $solicitud = $validacion['solicitud'];

        // Validar aceptación de términos
        if ($this->request->getPost('acepto_terminos') !== '1') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Debe aceptar los términos para firmar'
            ]);
        }

        // Calcular hash del documento
        $documento = $this->documentoModel->find($solicitud['id_documento']);
        $seccionModel = new \App\Models\DocSeccionModel();
        $contenido = $seccionModel->getContenidoJson($solicitud['id_documento']);
        $hashDocumento = hash('sha256', $contenido);

        // Preparar evidencia
        $evidencia = [
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'geolocalizacion' => $this->request->getPost('geolocalizacion'),
            'tipo_firma' => $this->request->getPost('tipo_firma'),
            'firma_imagen' => $this->request->getPost('firma_imagen'),
            'hash_documento' => $hashDocumento
        ];

        $resultado = $this->firmaModel->registrarFirma($solicitud['id_solicitud'], $evidencia);

        if ($resultado) {
            // Verificar si hay siguiente firmante en la cadena
            $siguienteFirmante = $this->firmaModel->getSiguienteFirmante($solicitud['id_documento']);

            if ($siguienteFirmante) {
                // Activar siguiente firmante (cambiar de 'esperando' a 'pendiente')
                $this->firmaModel->update($siguienteFirmante['id_solicitud'], [
                    'estado' => 'pendiente'
                ]);

                // Enviar correo al siguiente firmante
                $documento = $this->documentoModel->find($solicitud['id_documento']);
                $this->enviarCorreoFirma($siguienteFirmante, $documento);

                $this->firmaModel->registrarAudit($siguienteFirmante['id_solicitud'], 'notificacion_enviada', [
                    'activado_por_firma_de' => $solicitud['firmante_nombre']
                ]);
            }

            // Verificar si todas las firmas están completas
            if ($this->firmaModel->firmasCompletas($solicitud['id_documento'])) {
                // Cambiar estado del documento a aprobado
                $this->documentoModel->cambiarEstado($solicitud['id_documento'], 'aprobado');

                // Crear versión oficial
                $this->versionModel->crearVersion(
                    $solicitud['id_documento'],
                    'mayor',
                    'Documento aprobado con firmas completas',
                    $solicitud['firmante_nombre']
                );
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento firmado exitosamente'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al procesar firma']);
    }

    /**
     * Confirmación de firma exitosa
     */
    public function confirmacion($token)
    {
        $solicitud = $this->firmaModel->getByToken($token);

        if (!$solicitud || $solicitud['estado'] !== 'firmado') {
            return redirect()->to('/');
        }

        return view('firma/confirmacion', [
            'solicitud' => $solicitud
        ]);
    }

    /**
     * Estado de firmas de un documento
     */
    public function estado($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $solicitudes = $this->firmaModel->getByDocumento($idDocumento);
        $estadoFirmas = $this->firmaModel->getEstadoFirmas($idDocumento);

        return view('firma/estado', [
            'documento' => $documento,
            'solicitudes' => $solicitudes,
            'estadoFirmas' => $estadoFirmas
        ]);
    }

    /**
     * Reenviar solicitud de firma
     */
    public function reenviar($idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $nuevoToken = $this->firmaModel->reenviar($idSolicitud);

        // TODO: Enviar email con nuevo link

        return redirect()->back()->with('success', 'Solicitud reenviada');
    }

    /**
     * Cancelar solicitud de firma
     */
    public function cancelar($idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $this->firmaModel->cancelar($idSolicitud);

        return redirect()->back()->with('success', 'Solicitud cancelada');
    }

    /**
     * Ver audit log de una solicitud
     */
    public function auditLog($idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $solicitud = $this->firmaModel->find($idSolicitud);
        $auditLog = $this->firmaModel->getAuditLog($idSolicitud);
        $evidencia = $this->firmaModel->getEvidencia($idSolicitud);

        return view('firma/audit_log', [
            'solicitud' => $solicitud,
            'auditLog' => $auditLog,
            'evidencia' => $evidencia
        ]);
    }

    /**
     * Verificar documento firmado (público)
     */
    public function verificar($codigoVerificacion)
    {
        // Buscar por código de verificación
        $solicitud = $this->firmaModel
            ->where('token', $codigoVerificacion)
            ->where('estado', 'firmado')
            ->first();

        if (!$solicitud) {
            return view('firma/verificacion', ['valido' => false]);
        }

        $documento = $this->documentoModel->getCompleto($solicitud['id_documento']);
        $evidencia = $this->firmaModel->getEvidencia($solicitud['id_solicitud']);
        $todasFirmas = $this->firmaModel->getByDocumento($solicitud['id_documento']);

        return view('firma/verificacion', [
            'valido' => true,
            'documento' => $documento,
            'solicitud' => $solicitud,
            'evidencia' => $evidencia,
            'firmas' => $todasFirmas
        ]);
    }

    /**
     * Firmar internamente (usuario del sistema)
     */
    public function firmarInterno($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $tipoFirma = $this->request->getPost('tipo_firma'); // elaboro, reviso

        // Crear solicitud interna
        $datos = [
            'id_documento' => $idDocumento,
            'firmante_tipo' => $tipoFirma,
            'firmante_interno_id' => session()->get('id_usuario'),
            'firmante_nombre' => session()->get('nombre'),
            'firmante_cargo' => session()->get('cargo') ?? 'Consultor SST'
        ];

        $idSolicitud = $this->firmaModel->crearSolicitud($datos);

        // Firmar inmediatamente
        $evidencia = [
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'tipo_firma' => 'internal',
            'firma_imagen' => null,
            'hash_documento' => hash('sha256', json_encode(['id' => $idDocumento, 'time' => time()]))
        ];

        $this->firmaModel->registrarFirma($idSolicitud, $evidencia);

        return redirect()->back()->with('success', "Documento firmado como '{$tipoFirma}'");
    }
}
