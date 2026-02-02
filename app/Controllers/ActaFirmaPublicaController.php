<?php

namespace App\Controllers;

use App\Models\ActaModel;
use App\Models\ActaAsistenteModel;
use App\Models\ActaCompromisoModel;
use App\Models\ActaTokenModel;
use App\Models\ActaNotificacionModel;
use App\Models\ComiteModel;
use App\Models\ClientModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controlador para acciones públicas sin login (acceso por token)
 * - Firmar actas
 * - Actualizar compromisos
 * - Ver actas (solo lectura)
 */
class ActaFirmaPublicaController extends BaseController
{
    protected $actaModel;
    protected $asistentesModel;
    protected $compromisosModel;
    protected $tokenModel;
    protected $notificacionModel;

    public function __construct()
    {
        $this->actaModel = new ActaModel();
        $this->asistentesModel = new ActaAsistenteModel();
        $this->compromisosModel = new ActaCompromisoModel();
        $this->tokenModel = new ActaTokenModel();
        $this->notificacionModel = new ActaNotificacionModel();
    }

    /**
     * Página de firma de acta (acceso por token)
     */
    public function firmar(string $token)
    {
        // Buscar asistente por token
        $asistente = $this->asistentesModel->getByToken($token);

        if (!$asistente) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'El enlace de firma ha expirado o es inválido.',
                'sugerencia' => 'Solicite al secretario del comité que le reenvíe el enlace de firma.'
            ]);
        }

        // Verificar si ya firmó
        if ($asistente['estado_firma'] === 'firmado') {
            return view('actas/publico/ya_firmado', [
                'asistente' => $asistente,
                'mensaje' => 'Usted ya firmó esta acta.'
            ]);
        }

        // Obtener acta con detalles
        $acta = $this->actaModel->getConDetalles($asistente['id_acta']);

        if (!$acta || $acta['estado'] !== 'pendiente_firma') {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Esta acta ya no está disponible para firma.',
                'sugerencia' => 'El acta puede haber sido completada o anulada.'
            ]);
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comiteModel = new ComiteModel();
        $comite = $comiteModel->getConDetalles($acta['id_comite']);

        // Contar firmas actuales
        $firmados = $this->asistentesModel->contarFirmados($acta['id_acta']);
        $totalFirmantes = $this->asistentesModel->contarQuienesDebenFirmar($acta['id_acta']);

        return view('actas/publico/firmar', [
            'token' => $token,
            'asistente' => $asistente,
            'acta' => $acta,
            'cliente' => $cliente,
            'comite' => $comite,
            'firmados' => $firmados,
            'totalFirmantes' => $totalFirmantes
        ]);
    }

    /**
     * Procesar firma del acta
     */
    public function procesarFirma(string $token)
    {
        $asistente = $this->asistentesModel->getByToken($token);

        if (!$asistente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token inválido o expirado'
            ]);
        }

        if ($asistente['estado_firma'] === 'firmado') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya ha firmado esta acta'
            ]);
        }

        $firmaImagen = $this->request->getPost('firma_imagen');
        $observacion = $this->request->getPost('observacion');

        if (empty($firmaImagen)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe proporcionar su firma'
            ]);
        }

        // Validar que sea una imagen base64 válida
        if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $firmaImagen)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Formato de firma inválido'
            ]);
        }

        // Registrar firma
        $resultado = $this->asistentesModel->registrarFirma(
            $asistente['id_asistente'],
            $firmaImagen,
            $observacion
        );

        if ($resultado) {
            // Verificar si se completaron todas las firmas
            $acta = $this->actaModel->find($asistente['id_acta']);
            $firmados = $this->asistentesModel->contarFirmados($acta['id_acta']);
            $totalFirmantes = $this->asistentesModel->contarQuienesDebenFirmar($acta['id_acta']);

            $todasFirmadas = $firmados >= $totalFirmantes;

            // Notificar al consultor sobre la firma
            $this->notificarFirmaCompletada($asistente, $acta, $firmados, $totalFirmantes);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Firma registrada exitosamente',
                'firmados' => $firmados,
                'total' => $totalFirmantes,
                'acta_completa' => $todasFirmadas
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al registrar la firma'
        ]);
    }

    /**
     * Notificar al consultor que alguien firmó
     */
    protected function notificarFirmaCompletada(array $asistente, array $acta, int $firmados, int $total): void
    {
        // Obtener consultor del cliente
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        if (!empty($cliente['id_consultor'])) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($cliente['id_consultor']);

            if ($consultor && !empty($consultor['correo_consultor'])) {
                $this->notificacionModel->programar([
                    'id_cliente' => $acta['id_cliente'],
                    'tipo' => 'firma_completada',
                    'id_acta' => $acta['id_acta'],
                    'destinatario_email' => $consultor['correo_consultor'],
                    'destinatario_nombre' => $consultor['nombre_consultor'],
                    'destinatario_tipo' => 'consultor',
                    'asunto' => "Firma recibida - Acta {$acta['numero_acta']} ({$firmados}/{$total})",
                    'cuerpo' => "{$asistente['nombre_completo']} ha firmado el acta {$acta['numero_acta']}. " .
                               "Firmas completadas: {$firmados} de {$total}."
                ]);
            }
        }

        // Si todas las firmas están completas, notificar
        if ($firmados >= $total) {
            $this->notificarActaCompleta($acta);
        }
    }

    /**
     * Notificar que el acta está completamente firmada
     */
    protected function notificarActaCompleta(array $acta): void
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        // Notificar al consultor
        if (!empty($cliente['id_consultor'])) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($cliente['id_consultor']);

            if ($consultor && !empty($consultor['correo_consultor'])) {
                $this->notificacionModel->programar([
                    'id_cliente' => $acta['id_cliente'],
                    'tipo' => 'acta_firmada_completa',
                    'id_acta' => $acta['id_acta'],
                    'destinatario_email' => $consultor['correo_consultor'],
                    'destinatario_nombre' => $consultor['nombre_consultor'],
                    'destinatario_tipo' => 'consultor',
                    'asunto' => "Acta COMPLETADA - {$acta['numero_acta']}",
                    'cuerpo' => "El acta {$acta['numero_acta']} ha sido firmada por todos los asistentes y está lista."
                ]);
            }
        }

        // Notificar al cliente
        if (!empty($cliente['correo_cliente'])) {
            $this->notificacionModel->programar([
                'id_cliente' => $acta['id_cliente'],
                'tipo' => 'acta_firmada_completa',
                'id_acta' => $acta['id_acta'],
                'destinatario_email' => $cliente['correo_cliente'],
                'destinatario_nombre' => $cliente['nombre_cliente'],
                'destinatario_tipo' => 'cliente',
                'asunto' => "Acta de comité firmada - {$acta['numero_acta']}",
                'cuerpo' => "El acta {$acta['numero_acta']} ha sido firmada y está disponible para consulta."
            ]);
        }
    }

    /**
     * Página de confirmación de firma exitosa
     */
    public function firmaExitosa(string $token)
    {
        $asistente = $this->asistentesModel->where('token_firma', $token)->first();

        if (!$asistente || $asistente['estado_firma'] !== 'firmado') {
            return redirect()->to('/');
        }

        $acta = $this->actaModel->find($asistente['id_acta']);
        $firmados = $this->asistentesModel->contarFirmados($acta['id_acta']);
        $totalFirmantes = $this->asistentesModel->contarQuienesDebenFirmar($acta['id_acta']);

        return view('actas/publico/firma_exitosa', [
            'asistente' => $asistente,
            'acta' => $acta,
            'firmados' => $firmados,
            'totalFirmantes' => $totalFirmantes
        ]);
    }

    /**
     * Ver acta por token (solo lectura)
     */
    public function verActa(string $token)
    {
        $tokenData = $this->tokenModel->validarToken($token);

        if (!$tokenData || $tokenData['tipo'] !== 'ver_acta') {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'El enlace ha expirado o es inválido.'
            ]);
        }

        $acta = $this->actaModel->getConDetalles($tokenData['id_acta']);

        if (!$acta) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acta no encontrada.'
            ]);
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comiteModel = new ComiteModel();
        $comite = $comiteModel->getConDetalles($acta['id_comite']);

        // Marcar token como usado
        $this->tokenModel->marcarUsado($token);

        return view('actas/publico/ver_acta', [
            'acta' => $acta,
            'cliente' => $cliente,
            'comite' => $comite
        ]);
    }

    /**
     * Actualizar compromiso por token
     */
    public function actualizarTarea(string $token)
    {
        $compromiso = $this->compromisosModel->getByToken($token);

        if (!$compromiso) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'El enlace ha expirado o es inválido.'
            ]);
        }

        if ($compromiso['estado'] === 'cumplido') {
            return view('actas/publico/tarea_completada', [
                'compromiso' => $compromiso
            ]);
        }

        $acta = $this->actaModel->find($compromiso['id_acta']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($compromiso['id_cliente']);

        return view('actas/publico/actualizar_tarea', [
            'token' => $token,
            'compromiso' => $compromiso,
            'acta' => $acta,
            'cliente' => $cliente
        ]);
    }

    /**
     * Procesar actualización de tarea
     */
    public function procesarActualizacionTarea(string $token)
    {
        $compromiso = $this->compromisosModel->getByToken($token);

        if (!$compromiso) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token inválido'
            ]);
        }

        $estado = $this->request->getPost('estado');
        $porcentaje = $this->request->getPost('porcentaje');
        $evidencia = $this->request->getPost('evidencia');

        // Manejar archivo de evidencia si se subió
        $archivoEvidencia = null;
        $archivo = $this->request->getFile('archivo_evidencia');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $nuevoNombre = $archivo->getRandomName();
            $rutaDestino = WRITEPATH . 'uploads/evidencias/';

            if (!is_dir($rutaDestino)) {
                mkdir($rutaDestino, 0755, true);
            }

            $archivo->move($rutaDestino, $nuevoNombre);
            $archivoEvidencia = 'evidencias/' . $nuevoNombre;
        }

        if ($estado === 'cumplido') {
            $this->compromisosModel->cerrarConEvidencia(
                $compromiso['id_compromiso'],
                $evidencia ?? 'Completado por el responsable',
                $archivoEvidencia,
                $compromiso['responsable_nombre']
            );
        } else {
            $this->compromisosModel->update($compromiso['id_compromiso'], [
                'estado' => $estado,
                'porcentaje_avance' => $porcentaje ?? 0,
                'evidencia_descripcion' => $evidencia,
                'evidencia_archivo' => $archivoEvidencia
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarea actualizada correctamente'
        ]);
    }

    /**
     * Verificar código de verificación de acta
     */
    public function verificarActa()
    {
        $codigo = $this->request->getGet('codigo') ?? $this->request->getPost('codigo');

        if (empty($codigo)) {
            return view('actas/publico/verificar_acta', [
                'codigo' => null,
                'resultado' => null
            ]);
        }

        $acta = $this->actaModel->where('codigo_verificacion', strtoupper($codigo))->first();

        if (!$acta) {
            return view('actas/publico/verificar_acta', [
                'codigo' => $codigo,
                'resultado' => 'invalido'
            ]);
        }

        $acta = $this->actaModel->getConDetalles($acta['id_acta']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $comiteModel = new ComiteModel();
        $comite = $comiteModel->getConDetalles($acta['id_comite']);

        return view('actas/publico/verificar_acta', [
            'codigo' => $codigo,
            'resultado' => 'valido',
            'acta' => $acta,
            'cliente' => $cliente,
            'comite' => $comite
        ]);
    }
}
