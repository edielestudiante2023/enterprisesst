<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaVisitaModel;
use App\Models\ActaVisitaIntegranteModel;
use App\Models\ActaVisitaTemaModel;
use App\Models\ActaVisitaFotoModel;
use App\Models\PendientesModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\ActaVisitaPtaModel;
use App\Models\PtaClienteNuevaModel;
use App\Libraries\InspeccionEmailNotifier;
use App\Models\VencimientosMantenimientoModel;
use App\Models\CicloVisitaModel;
use App\Services\PtaAuditService;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\InspeccionVersionTrait;
use App\Traits\ImagenCompresionTrait;

class ActaVisitaController extends BaseController
{
    use AutosaveJsonTrait;
    use InspeccionVersionTrait;
    use ImagenCompresionTrait;
    protected ActaVisitaModel $actaModel;
    protected ActaVisitaIntegranteModel $integranteModel;
    protected ActaVisitaTemaModel $temaModel;

    public function __construct()
    {
        $this->actaModel = new ActaVisitaModel();
        $this->integranteModel = new ActaVisitaIntegranteModel();
        $this->temaModel = new ActaVisitaTemaModel();
    }

    /**
     * Listado de actas del consultor
     */
    public function list()
    {
        $actas = $this->actaModel->select('tbl_acta_visita.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_visita.id_consultor', 'left')
            ->orderBy('tbl_acta_visita.fecha_visita', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Actas de Visita',
            'actas' => $actas,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/list', $data),
            'title'   => 'Actas de Visita',
        ]);
    }

    /**
     * Formulario de creación. Opcionalmente recibe id_cliente pre-seleccionado.
     */
    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Acta de Visita',
            'acta'       => null,
            'idCliente'  => $idCliente,
            'integrantes' => [],
            'temas'      => [],
            'fotos'      => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/form', $data),
            'title'   => 'Nueva Acta de Visita',
        ]);
    }

    /**
     * Guardar nueva acta (siempre como borrador)
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        // Validar campos mínimos
        if (!$isAutosave) {
            $rules = [
                'id_cliente'   => 'required|integer',
                'fecha_visita' => 'required|valid_date',
                'hora_visita'  => 'required',
                'motivo'       => 'required|min_length[3]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        // Insertar acta
        $actaData = [
            'id_cliente'            => $this->request->getPost('id_cliente'),
            'id_consultor'          => $userId,
            'fecha_visita'          => $this->request->getPost('fecha_visita'),
            'hora_visita'           => $this->request->getPost('hora_visita'),
            'ubicacion_gps'         => $this->request->getPost('ubicacion_gps'),
            'motivo'                => $this->request->getPost('motivo'),
            'modalidad'             => $this->request->getPost('modalidad') ?: 'Presencial',
            'cartera'               => $this->request->getPost('cartera'),
            'observaciones'         => $this->request->getPost('observaciones'),
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha') ?: null,
            'proxima_reunion_hora'  => $this->request->getPost('proxima_reunion_hora') ?: null,
            'estado'                => 'borrador',
        ];

        $this->actaModel->insert($actaData);
        $idActa = $this->actaModel->getInsertID();

        // Guardar integrantes
        $this->saveIntegrantes($idActa);

        // Guardar temas
        $this->saveTemas($idActa);

        // Guardar compromisos como pendientes
        $this->saveCompromisos($idActa);

        // Guardar fotos
        $this->saveFotos($idActa);

        // Procesar checkboxes PTA y Pendientes (solo en submit real)
        if (!$isAutosave) {
            $this->savePtaActividades($idActa);
            $this->savePendientesActividades($idActa);
        }

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idActa);
        }

        return redirect()->to('/inspecciones/acta-visita/edit/' . $idActa)
            ->with('msg', 'Acta guardada como borrador');
    }

    /**
     * Formulario de edición (solo borradores y pendiente_firma)
     */
    public function edit($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }
        $data = [
            'title'       => 'Editar Acta de Visita',
            'acta'        => $acta,
            'idCliente'   => $acta['id_cliente'],
            'integrantes' => $this->integranteModel->getByActa($id),
            'temas'       => $this->temaModel->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos' => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
            'ptaLinks'    => (new ActaVisitaPtaModel())->getByActa($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/form', $data),
            'title'   => 'Editar Acta',
        ]);
    }

    /**
     * Actualizar acta existente (mantiene estado borrador)
     */
    public function update($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'No se puede editar esta acta');
        }

        $actaData = [
            'id_cliente'            => $this->request->getPost('id_cliente'),
            'fecha_visita'          => $this->request->getPost('fecha_visita'),
            'hora_visita'           => $this->request->getPost('hora_visita'),
            'ubicacion_gps'         => $this->request->getPost('ubicacion_gps'),
            'motivo'                => $this->request->getPost('motivo'),
            'modalidad'             => $this->request->getPost('modalidad') ?: 'Presencial',
            'cartera'               => $this->request->getPost('cartera'),
            'observaciones'         => $this->request->getPost('observaciones'),
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha') ?: null,
            'proxima_reunion_hora'  => $this->request->getPost('proxima_reunion_hora') ?: null,
        ];

        $this->actaModel->update($id, $actaData);

        // Reemplazar integrantes, temas, compromisos
        $this->saveIntegrantes($id);
        $this->saveTemas($id);
        $this->saveCompromisos($id);
        $this->saveFotos($id);

        // Procesar checkboxes PTA y Pendientes (solo en submit real)
        if (!$this->isAutosaveRequest()) {
            $this->savePtaActividades($id);
            $this->savePendientesActividades($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        $redirect = $this->request->getPost('ir_a_firmas') ? '/inspecciones/acta-visita/pta/' . $id : '/inspecciones/acta-visita/edit/' . $id;

        return redirect()->to($redirect)->with('msg', 'Acta actualizada');
    }

    /**
     * Vista de solo lectura (actas completas)
     */
    public function view($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }

        $clientModel = new ClientModel();

        $data = [
            'title'       => 'Ver Acta de Visita',
            'acta'        => $acta,
            'cliente'     => $clientModel->find($acta['id_cliente']),
            'integrantes' => $this->integranteModel->getByActa($id),
            'temas'       => $this->temaModel->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos' => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
            'ptaCerradas' => (new ActaVisitaPtaModel())->getCerradasByActa($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/view', $data),
            'title'   => 'Ver Acta',
        ]);
    }

    /**
     * Vista intermedia PTA: cerrar actividades y agregar observaciones grupales
     */
    public function pta($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }

        // Si ya confirmo PTA, redirigir directo a firmas (una sola oportunidad)
        if (!empty($acta['pta_confirmado'])) {
            return redirect()->to('/inspecciones/acta-visita/firma/' . $id);
        }

        $ptaModel = new PtaClienteNuevaModel();
        $actividades = $ptaModel->getAbiertosByClienteYMes((int) $acta['id_cliente'], $acta['fecha_visita']);

        // Cargar estado previo
        $linkModel = new ActaVisitaPtaModel();
        $prevLinks = [];
        $links = $linkModel->where('id_acta_visita', $id)->findAll();
        foreach ($links as $link) {
            $prevLinks[$link['id_ptacliente']] = [
                'cerrada'       => (bool) $link['cerrada'],
                'justificacion' => $link['justificacion_no_cierre'] ?? '',
            ];
        }

        // Incluir actividades ya cerradas en esta acta (para mostrar checked+disabled)
        $cerradasEnActa = $linkModel->select('tbl_acta_visita_pta.*, tbl_pta_cliente.actividad_plandetrabajo, tbl_pta_cliente.numeral_plandetrabajo, tbl_pta_cliente.fecha_propuesta, tbl_pta_cliente.estado_actividad')
            ->join('tbl_pta_cliente', 'tbl_pta_cliente.id_ptacliente = tbl_acta_visita_pta.id_ptacliente', 'left')
            ->where('tbl_acta_visita_pta.id_acta_visita', $id)
            ->where('tbl_acta_visita_pta.cerrada', 1)
            ->findAll();

        $idsAbiertas = array_column($actividades, 'id_ptacliente');
        foreach ($cerradasEnActa as $ca) {
            if (!in_array($ca['id_ptacliente'], $idsAbiertas)) {
                $actividades[] = [
                    'id_ptacliente'            => $ca['id_ptacliente'],
                    'actividad_plandetrabajo'  => $ca['actividad_plandetrabajo'],
                    'numeral_plandetrabajo'    => $ca['numeral_plandetrabajo'],
                    'fecha_propuesta'          => $ca['fecha_propuesta'],
                    'estado_actividad'         => $ca['estado_actividad'],
                    '_ya_cerrada'              => true,
                ];
            }
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);

        $data = [
            'title'       => 'Actividades PTA',
            'acta'        => $acta,
            'cliente'     => $cliente,
            'actividades' => $actividades,
            'prevLinks'   => $prevLinks,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/pta', $data),
            'title'   => 'Actividades PTA',
        ]);
    }

    /**
     * Guardar PTA desde vista intermedia y redirigir a firmas
     */
    public function savePta($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }

        $this->savePtaActividades($id);
        $this->savePendientesActividades($id);

        // Marcar como confirmado (una sola oportunidad)
        $this->actaModel->update($id, ['pta_confirmado' => 1]);

        return redirect()->to('/inspecciones/acta-visita/firma/' . $id)->with('msg', 'Actividades PTA guardadas');
    }

    /**
     * Pantalla de firmas (paso a paso)
     */
    public function firma($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }
        // Cambiar estado a pendiente_firma
        if ($acta['estado'] === 'borrador') {
            $this->actaModel->update($id, ['estado' => 'pendiente_firma']);
        }

        $integrantes = $this->integranteModel->getByActa($id);

        // Determinar qué firmas se necesitan a partir de los integrantes del acta
        $firmantes = [];
        $consultorIntegrante = null;
        foreach ($integrantes as $integrante) {
            $rol = strtoupper($integrante['rol']);
            if ($rol === 'CLIENTE') {
                $firmantes[] = ['tipo' => 'administrador', 'nombre' => $integrante['nombre'], 'firmado' => !empty($acta['firma_administrador'])];
            } elseif (stripos($rol, 'CONSULTOR') !== false && $consultorIntegrante === null) {
                $consultorIntegrante = $integrante['nombre'];
            }
        }

        // Consultor siempre firma: preferir integrante del acta; fallback a consultor de sesión
        if ($consultorIntegrante === null) {
            $consultantModel = new ConsultantModel();
            $consultor = $consultantModel->find(session()->get('user_id'));
            $consultorIntegrante = $consultor['nombre_consultor'] ?? session()->get('nombre_usuario');
        }
        $firmantes[] = [
            'tipo'    => 'consultor',
            'nombre'  => $consultorIntegrante,
            'firmado' => !empty($acta['firma_consultor']),
        ];

        $data = [
            'title'    => 'Firmas del Acta',
            'acta'     => $acta,
            'firmantes' => $firmantes,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/firma', $data),
            'title'   => 'Firmas',
        ]);
    }

    /**
     * Guardar firma individual (AJAX)
     */
    public function saveFirma($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        $tipo = $this->request->getPost('tipo'); // administrador, vigia, consultor
        $firmaBase64 = $this->request->getPost('firma_imagen');

        if (!in_array($tipo, ['administrador', 'vigia', 'consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo de firma no válido']);
        }

        // Decodificar base64 a PNG
        $firmaData = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));

        // Guardar archivo
        $dir = FCPATH . 'uploads/inspecciones/firmas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = "firma_{$tipo}_{$id}_" . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        // Guardar ruta en BD
        $campo = "firma_{$tipo}";
        $this->actaModel->update($id, [
            $campo => "uploads/inspecciones/firmas/{$nombreArchivo}",
        ]);

        return $this->response->setJSON(['success' => true, 'campo' => $campo]);
    }

    /**
     * Finalizar acta: generar PDF + cargar a reportes + email + actualizar ciclo
     */
    public function finalizar($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        // Verificar que tiene firma del consultor (obligatoria)
        if (empty($acta['firma_consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Falta la firma del consultor']);
        }

        // Verificar firma del cliente si hay integrante con rol CLIENTE
        $integrantes = $this->integranteModel->getByActa($id);
        foreach ($integrantes as $integrante) {
            if (strtoupper($integrante['rol']) === 'CLIENTE' && empty($acta['firma_administrador'])) {
                return $this->response->setJSON(['success' => false, 'error' => 'Falta la firma del cliente']);
            }
        }

        // Generar PDF
        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error al generar PDF']);
        }

        // Actualizar acta
        $this->actaModel->update($id, [
            'ruta_pdf' => $pdfPath,
            'estado'   => 'completo',
        ]);

        // Auto-upload a tbl_reporte
        $acta = $this->actaModel->find($id); // Re-read with updated data
        $this->uploadToReportes($acta, $pdfPath);

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $acta['id_cliente'],
            (int) $acta['id_consultor'],
            'ACTA DE VISITA',
            $acta['fecha_visita'],
            $pdfPath,
            (int) $acta['id'],
            'ActaVisita'
        );
        $emailMsg = '';
        if ($emailResult['success']) {
            $emailMsg = $emailResult['message'];
        } else {
            $emailMsg = '(Email no enviado: ' . $emailResult['error'] . ')';
        }

        // ─── Hook: Actualizar ciclo de visita en tbl_ciclos_visita ───
        $this->actualizarCicloVisita($acta);

        // Registrar versión en sistema documental SST
        try {
            $idDoc = $this->registrarVersionDocumento(
                (int)$acta['id_cliente'],
                'acta_visita',
                'Actas de Visita',
                json_encode($acta),
                "Acta de visita realizada el {$acta['fecha_visita']}",
                'AV-' . $acta['id_cliente'],
                (int)date('Y', strtotime($acta['fecha_visita']))
            );
            if ($idDoc) {
                $this->actaModel->update($id, ['id_documento_sst' => $idDoc]);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'registrarVersionDocumento acta_visita failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success'   => true,
            'pdf_url'   => base_url($pdfPath),
            'email_msg' => $emailMsg,
        ]);
    }

    /**
     * Ver/descargar PDF - siempre regenera desde el template actual
     */
    public function generatePdf($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }

        // Regenerar PDF desde template actual
        $pdfRelPath = $this->generarPdfInterno($id);
        if (!$pdfRelPath) {
            return redirect()->back()->with('error', 'Error generando PDF');
        }
        $this->actaModel->update($id, ['ruta_pdf' => $pdfRelPath]);

        $pdfPath = FCPATH . $pdfRelPath;

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="acta_visita_' . $id . '.pdf"')
            ->setBody(file_get_contents($pdfPath));
    }

    /**
     * Regenerar PDF de un acta ya finalizada
     */
    public function regenerarPdf($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta || ($acta['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->actaModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $acta = $this->actaModel->find($id);
        $this->uploadToReportes($acta, $pdfPath);

        return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    /**
     * Eliminar acta (solo borradores)
     */
    public function delete($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }
        // Eliminar fotos del disco
        $fotos = (new ActaVisitaFotoModel())->getByActa($id);
        foreach ($fotos as $foto) {
            $path = FCPATH . $foto['ruta_archivo'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Eliminar firmas del disco
        foreach (['firma_administrador', 'firma_vigia', 'firma_consultor'] as $campo) {
            if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                unlink(FCPATH . $acta[$campo]);
            }
        }

        // CASCADE eliminará integrantes, temas, fotos automáticamente
        $this->actaModel->delete($id);

        return redirect()->to('/inspecciones/acta-visita')->with('msg', 'Acta eliminada');
    }

    /**
     * Enviar email con el PDF al cliente y consultor
     */
    public function enviarEmail($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta || $acta['estado'] !== 'completo' || empty($acta['ruta_pdf'])) {
            return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $acta['id_cliente'],
            (int) $acta['id_consultor'],
            'ACTA DE VISITA',
            $acta['fecha_visita'],
            $acta['ruta_pdf'],
            (int) $acta['id'],
            'ActaVisita'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('error', $result['error']);
    }

    /**
     * AJAX (auth): genera token de firma remota y devuelve URL para compartir por WhatsApp
     */
    public function generarTokenFirma(int $id)
    {
        $tipo = $this->request->getPost('tipo');
        if (!in_array($tipo, ['administrador', 'vigia', 'consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo invalido']);
        }

        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        $token     = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->actaModel->update($id, [
            'token_firma_remota'     => $token,
            'token_firma_tipo'       => $tipo,
            'token_firma_expiracion' => $expiracion,
        ]);

        $url = base_url("acta-visita/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'tipo' => $tipo]);
    }

    /**
     * Pagina publica: canvas de firma para el firmante remoto (sin auth)
     */
    public function firmarRemoto(string $token)
    {
        $acta = $this->actaModel->where('token_firma_remota', $token)->first();

        if (!$acta) {
            return view('inspecciones/acta_visita/firma_remota_error', [
                'mensaje' => 'Este enlace no es valido o ya fue usado.'
            ]);
        }

        if (strtotime($acta['token_firma_expiracion']) < time()) {
            return view('inspecciones/acta_visita/firma_remota_error', [
                'mensaje' => 'Este enlace ha expirado (7 dias). Pida uno nuevo al consultor.'
            ]);
        }

        $campoFirma = 'firma_' . $acta['token_firma_tipo'];
        if (!empty($acta[$campoFirma])) {
            return view('inspecciones/acta_visita/firma_remota_error', [
                'mensaje' => 'Esta firma ya fue registrada.'
            ]);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);

        $integrantes = $this->integranteModel->getByActa($acta['id']);
        $nombreFirmante = '';
        foreach ($integrantes as $integrante) {
            $rol  = strtoupper($integrante['rol']);
            $tipo = $acta['token_firma_tipo'];
            if ($tipo === 'administrador' && strpos($rol, 'ADMIN') !== false) {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
            if ($tipo === 'administrador' && strtoupper($integrante['rol']) === 'CLIENTE') {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
            if ($tipo === 'vigia' && strpos($rol, 'VIG') !== false) {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
            if ($tipo === 'consultor' && strpos($rol, 'CONSULTOR') !== false) {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
        }

        $temas       = $this->temaModel->getByActa($acta['id']);
        $compromisos = (new PendientesModel())->where('id_acta_visita', $acta['id'])->findAll();

        $pendientesAbiertos = (new PendientesModel())
            ->where('id_cliente', $acta['id_cliente'])
            ->where('estado', 'ABIERTA')
            ->groupStart()
                ->where('id_acta_visita IS NULL', null, false)
                ->orWhere('id_acta_visita !=', $acta['id'])
            ->groupEnd()
            ->findAll();

        $dateThreshold = date('Y-m-d', strtotime('+30 days'));
        $mantenimientos = [];
        try {
            $mantenimientos = (new VencimientosMantenimientoModel())
                ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
                ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
                ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
                ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
                ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
                ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
                ->findAll();
        } catch (\Exception $e) {
            // Tabla puede no existir aun
        }

        return view('inspecciones/acta_visita/firma_remota', [
            'token'              => $token,
            'acta'               => $acta,
            'cliente'            => $cliente,
            'tipo'               => $acta['token_firma_tipo'],
            'nombreFirmante'     => $nombreFirmante,
            'integrantes'        => $integrantes,
            'temas'              => $temas,
            'compromisos'        => $compromisos,
            'pendientesAbiertos' => $pendientesAbiertos,
            'mantenimientos'     => $mantenimientos,
        ]);
    }

    /**
     * AJAX publico: recibe y guarda la firma remota (sin auth, token es la autenticacion)
     */
    public function procesarFirmaRemota()
    {
        $token       = $this->request->getPost('token');
        $firmaBase64 = $this->request->getPost('firma_imagen');

        $acta = $this->actaModel->where('token_firma_remota', $token)->first();
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace invalido']);
        }

        if (strtotime($acta['token_firma_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
        }

        $tipo = $acta['token_firma_tipo'];
        $firmaData    = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));

        $dir = FCPATH . 'uploads/inspecciones/firmas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $nombreArchivo = "firma_{$tipo}_{$acta['id']}_" . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $campo = "firma_{$tipo}";
        $this->actaModel->update($acta['id'], [
            $campo                   => "uploads/inspecciones/firmas/{$nombreArchivo}",
            'token_firma_remota'     => null,
            'token_firma_tipo'       => null,
            'token_firma_expiracion' => null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Endpoint AJAX interno: carga actividades PTA abiertas para checkboxes
     */
    public function getPtaActividades()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        $fechaVisita = $this->request->getGet('fecha_visita');
        $idActa = (int) $this->request->getGet('id_acta');

        if (!$idCliente || !$fechaVisita) {
            return $this->response->setJSON(['actividades' => [], 'links' => []]);
        }

        $ptaModel = new PtaClienteNuevaModel();
        $actividades = $ptaModel->getAbiertosByClienteYMes($idCliente, $fechaVisita);

        // Si estamos editando, traer estado previo de checkboxes
        $links = [];
        if ($idActa) {
            $linkModel = new ActaVisitaPtaModel();
            $existentes = $linkModel->getByActa($idActa);
            foreach ($existentes as $link) {
                $links[$link['id_ptacliente']] = $link;
            }
        }

        // Determinar mes de visita para badge rezagada
        $mesVisita = (int) date('m', strtotime($fechaVisita));

        $result = [];
        foreach ($actividades as $act) {
            $mesPropuesta = (int) date('m', strtotime($act['fecha_propuesta']));
            $result[] = [
                'id_ptacliente'           => $act['id_ptacliente'],
                'numeral_plandetrabajo'   => $act['numeral_plandetrabajo'],
                'actividad_plandetrabajo' => $act['actividad_plandetrabajo'],
                'fecha_propuesta'         => $act['fecha_propuesta'],
                'rezagada'                => $mesPropuesta < $mesVisita,
            ];
        }

        return $this->response->setJSON([
            'actividades' => $result,
            'links'       => $links,
        ]);
    }

    // ========== MÉTODOS PRIVADOS ==========

    /**
     * Guardar/reemplazar integrantes del POST
     */
    private function saveIntegrantes(int $idActa): void
    {
        $nombres = $this->request->getPost('integrante_nombre') ?? [];
        $roles = $this->request->getPost('integrante_rol') ?? [];

        $integrantes = [];
        foreach ($nombres as $i => $nombre) {
            if (!empty(trim($nombre))) {
                $integrantes[] = [
                    'nombre' => trim($nombre),
                    'rol'    => $roles[$i] ?? '',
                ];
            }
        }

        $this->integranteModel->replaceForActa($idActa, $integrantes);
    }

    /**
     * Guardar/reemplazar temas del POST
     */
    private function saveTemas(int $idActa): void
    {
        $temas = $this->request->getPost('tema') ?? [];
        $temasLimpios = array_filter(array_map('trim', $temas));

        $this->temaModel->replaceForActa($idActa, array_values($temasLimpios));
    }

    /**
     * Guardar compromisos como pendientes en tbl_pendientes
     */
    private function saveCompromisos(int $idActa): void
    {
        $actividades = $this->request->getPost('compromiso_actividad') ?? [];
        $fechas = $this->request->getPost('compromiso_fecha') ?? [];
        $responsables = $this->request->getPost('compromiso_responsable') ?? [];

        $acta = $this->actaModel->find($idActa);
        $pendientesModel = new PendientesModel();

        // Eliminar pendientes anteriores de esta acta
        $pendientesModel->where('id_acta_visita', $idActa)->delete();

        foreach ($actividades as $i => $actividad) {
            if (!empty(trim($actividad))) {
                $pendientesModel->insert([
                    'id_cliente'      => $acta['id_cliente'],
                    'tarea_actividad' => trim($actividad),
                    'fecha_asignacion' => date('Y-m-d'),
                    'fecha_cierre'    => $fechas[$i] ?? null,
                    'responsable'     => $responsables[$i] ?? '',
                    'estado'          => 'ABIERTA',
                    'id_acta_visita'  => $idActa,
                ]);
            }
        }
    }

    /**
     * Guardar fotos subidas
     */
    private function saveFotos(int $idActa): void
    {
        $files = $this->request->getFiles();
        if (empty($files['fotos'])) {
            return;
        }

        $fotoModel = new ActaVisitaFotoModel();
        $dir = FCPATH . 'uploads/inspecciones/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($files['fotos'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);

                $fotoModel->insert([
                    'id_acta_visita' => $idActa,
                    'ruta_archivo'   => 'uploads/inspecciones/fotos/' . $fileName,
                    'tipo'           => 'foto',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Generar PDF con DOMPDF y guardarlo en disco
     */
    private function generarPdfInterno(int $id): ?string
    {
        $acta = $this->actaModel->find($id);
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        $integrantes = $this->integranteModel->getByActa($id);
        $temas = $this->temaModel->getByActa($id);
        $compromisos = (new PendientesModel())->where('id_acta_visita', $id)->findAll();

        // Pendientes abiertos del cliente
        $pendientesAbiertos = (new PendientesModel())
            ->where('id_cliente', $acta['id_cliente'])
            ->where('estado', 'ABIERTA')
            ->where('id_acta_visita IS NULL OR id_acta_visita !=', $id)
            ->findAll();

        // Mantenimientos por vencer
        $dateThreshold = date('Y-m-d', strtotime('+30 days'));
        $mantenimientos = [];
        try {
            $mantenimientos = (new VencimientosMantenimientoModel())
                ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
                ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
                ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
                ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
                ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
                ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
                ->findAll();
        } catch (\Exception $e) {
            // Tabla aún no creada
        }

        // Cargar firmas como base64 para incrustar en DOMPDF
        $firmas = [];
        foreach (['administrador', 'vigia', 'consultor'] as $tipo) {
            $campo = "firma_{$tipo}";
            if (!empty($acta[$campo])) {
                $path = FCPATH . $acta[$campo];
                if (file_exists($path)) {
                    $firmas[$tipo] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
                }
            }
        }

        // Logo del cliente
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Consultor
        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($acta['id_consultor']);

        // Fotos del acta convertidas a base64 para DOMPDF
        $fotosBase64 = [];
        $fotosModel = new ActaVisitaFotoModel();
        $fotos = $fotosModel->getByActa($id);
        foreach ($fotos as $foto) {
            $fotoPath = FCPATH . $foto['ruta_archivo'];
            if (file_exists($fotoPath)) {
                $fotosBase64[] = [
                    'data'        => $this->fotoABase64ParaPdf($fotoPath),
                    'descripcion' => $foto['descripcion'] ?? '',
                    'tipo'        => $foto['tipo'] ?? 'foto',
                ];
            }
        }

        $data = [
            'acta'                => $acta,
            'cliente'             => $cliente,
            'consultor'           => $consultor,
            'integrantes'         => $integrantes,
            'temas'               => $temas,
            'compromisos'         => $compromisos,
            'pendientesAbiertos'  => $pendientesAbiertos,
            'mantenimientos'      => $mantenimientos,
            'firmas'              => $firmas,
            'logoBase64'          => $logoBase64,
            'fotos'               => $fotosBase64,
            'ptaCerradas'         => (new ActaVisitaPtaModel())->getCerradasByActa($id),
        ];

        $html = view('inspecciones/acta_visita/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Guardar PDF
        $pdfDir = 'uploads/inspecciones/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'acta_visita_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Eliminar PDF anterior si existe
        if (!empty($acta['ruta_pdf']) && file_exists(FCPATH . $acta['ruta_pdf'])) {
            unlink(FCPATH . $acta['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Registra el PDF en tbl_reporte para que aparezca en reportes del cliente
     */
    private function uploadToReportes(array $acta, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();

        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) {
            return false;
        }

        $nitCliente = $cliente['nit_cliente'];

        // Verificar si ya existe un reporte para esta acta
        $existente = $reporteModel
            ->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 9)
            ->like('observaciones', 'acta_id:' . $acta['id'])
            ->first();

        // Copiar a uploads/{nit_cliente}/
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'acta_visita_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'ACTA DE VISITA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_visita'],
            'id_detailreport' => 9,
            'id_report_type'  => 6,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. acta_id:' . $acta['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Actualizar ciclo de visita al completar un acta
     */
    private function actualizarCicloVisita(array $acta): void
    {
        $cicloModel = new CicloVisitaModel();

        $mesActa  = (int)date('n', strtotime($acta['fecha_visita']));
        $anioActa = (int)date('Y', strtotime($acta['fecha_visita']));

        // Buscar ciclo pendiente para este cliente en el mes del acta
        $ciclo = $cicloModel->where('id_cliente', $acta['id_cliente'])
            ->where('mes_esperado', $mesActa)
            ->where('anio', $anioActa)
            ->first();

        if (!$ciclo) {
            return; // No hay ciclo registrado para este periodo
        }

        // Determinar estatus de agenda
        $estatusAgenda = 'cumple';
        if ($ciclo['fecha_agendada'] && $acta['fecha_visita'] !== $ciclo['fecha_agendada']) {
            $estatusAgenda = 'incumple'; // Fue en otro día del agendado
        }

        $cicloModel->update($ciclo['id'], [
            'fecha_acta'      => $acta['fecha_visita'],
            'id_acta'         => $acta['id'],
            'estatus_agenda'  => $estatusAgenda,
            'estatus_mes'     => 'cumple', // Si hay acta en el mes, el mes cumple
        ]);

        // Auto-generar siguiente ciclo
        $estandar = $ciclo['estandar'] ?? '';
        if ($estandar) {
            $cicloModel->generarSiguienteCiclo(
                (int)$acta['id_cliente'],
                $acta['fecha_visita'],
                $estandar,
                (int)$acta['id_consultor']
            );
        }
    }

    /**
     * Procesar checkboxes PTA del formulario:
     * - Cerrar actividades marcadas en tbl_pta_cliente
     * - Guardar justificaciones para las no marcadas
     * - Registrar auditoría
     */
    private function savePtaActividades(int $idActa): void
    {
        $ptaIds = $this->request->getPost('pta_actividad_id') ?? [];
        $checked = $this->request->getPost('pta_actividad_checked') ?? [];
        $justificaciones = $this->request->getPost('pta_justificacion') ?? [];

        if (empty($ptaIds)) {
            return;
        }

        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return;
        }

        $ptaModel = new PtaClienteNuevaModel();
        $linkModel = new ActaVisitaPtaModel();
        $metodo = 'ActaVisitaController::savePtaActividades';

        foreach ($ptaIds as $idPta) {
            $idPta = (int) $idPta;
            $ptaRecord = $ptaModel->find($idPta);

            if (!$ptaRecord) {
                continue;
            }

            // Validación de seguridad: verificar que la actividad pertenece al cliente del acta
            if ((int) $ptaRecord['id_cliente'] !== (int) $acta['id_cliente']) {
                continue;
            }

            $esCerrada = in_array((string) $idPta, $checked, true);

            // Guardar vínculo en tbl_acta_visita_pta
            $linkData = [
                'id_acta_visita'          => $idActa,
                'id_ptacliente'           => $idPta,
                'cerrada'                 => $esCerrada ? 1 : 0,
                'justificacion_no_cierre' => !$esCerrada ? ($justificaciones[$idPta] ?? null) : null,
            ];

            // Upsert: si ya existe el link, actualizar
            $existente = $linkModel->where('id_acta_visita', $idActa)
                ->where('id_ptacliente', $idPta)
                ->first();

            if ($existente) {
                $linkModel->update($existente['id'], $linkData);
            } else {
                $linkModel->insert($linkData);
            }

            // Si está marcada como cerrada y aún está ABIERTA en PTA, cerrarla
            if ($esCerrada && $ptaRecord['estado_actividad'] === 'ABIERTA') {
                $ptaModel->update($idPta, [
                    'estado_actividad'  => 'CERRADA',
                    'porcentaje_avance' => 100,
                    'fecha_cierre'      => $acta['fecha_visita'],
                ]);

                // Auditoría
                PtaAuditService::log($idPta, 'UPDATE', 'estado_actividad', 'ABIERTA', 'CERRADA', $metodo, (int) $acta['id_cliente']);
                PtaAuditService::log($idPta, 'UPDATE', 'porcentaje_avance', $ptaRecord['porcentaje_avance'], '100', $metodo, (int) $acta['id_cliente']);
                PtaAuditService::log($idPta, 'UPDATE', 'fecha_cierre', $ptaRecord['fecha_cierre'], $acta['fecha_visita'], $metodo, (int) $acta['id_cliente']);
            }

            // Si NO está cerrada, concatenar justificación en observaciones de PTA
            if (!$esCerrada && !empty($justificaciones[$idPta])) {
                $justTexto = trim($justificaciones[$idPta]);
                $prefijo = "[Acta Visita #{$idActa} - {$acta['fecha_visita']}] No cerrada: ";
                $nuevaObs = $prefijo . $justTexto;

                $obsActual = $ptaRecord['observaciones'] ?? '';
                $obsFinal = $obsActual ? $obsActual . "\n" . $nuevaObs : $nuevaObs;

                $ptaModel->update($idPta, ['observaciones' => $obsFinal]);

                PtaAuditService::log($idPta, 'UPDATE', 'observaciones', $obsActual, $obsFinal, $metodo, (int) $acta['id_cliente']);
            }
        }
    }

    /**
     * API: pendientes ABIERTOS de un cliente para mostrar en form acta_visita
     * Si se edita un acta, marca como ya cerrados los que tienen id_acta_visita = idActa
     */
    public function getPendientesAbiertos()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        $fechaVisita = $this->request->getGet('fecha_visita');
        $idActa = (int) $this->request->getGet('id_acta');

        if (!$idCliente || !$fechaVisita) {
            return $this->response->setJSON(['actividades' => [], 'links' => []]);
        }

        $pendientesModel = new \App\Models\PendientesModel();

        // Pendientes ABIERTOS del cliente +
        // pendientes ya CERRADOS por este acta especifica (para mostrar checkbox checked+disabled al editar)
        $builder = $pendientesModel->builder();
        $builder->where('id_cliente', $idCliente);
        if ($idActa) {
            $builder->groupStart()
                ->where('estado', 'ABIERTA')
                ->orGroupStart()
                    ->where('id_acta_visita', $idActa)
                    ->whereIn('estado', ['CERRADA', 'CERRADA POR FIN CONTRATO'])
                ->groupEnd()
            ->groupEnd();
        } else {
            $builder->where('estado', 'ABIERTA');
        }
        $rows = $builder->orderBy('fecha_cierre', 'ASC')->get()->getResultArray();

        $mesVisita = (int) date('m', strtotime($fechaVisita));

        $actividades = [];
        $links = [];
        foreach ($rows as $r) {
            $idPend = (int) $r['id_pendientes'];
            $mesPlazo = $r['fecha_cierre'] ? (int) date('m', strtotime($r['fecha_cierre'])) : 0;
            $cerradaEnEsteActa = $idActa && (int) ($r['id_acta_visita'] ?? 0) === $idActa
                && in_array($r['estado'], ['CERRADA', 'CERRADA POR FIN CONTRATO'], true);

            $actividades[] = [
                'id_pendientes'    => $idPend,
                'tarea_actividad'  => $r['tarea_actividad'],
                'responsable'      => $r['responsable'] ?? '',
                'fecha_cierre'     => $r['fecha_cierre'],
                'rezagada'         => $mesPlazo && $mesPlazo < $mesVisita,
                'estado'           => $r['estado'],
            ];
            if ($cerradaEnEsteActa) {
                $links[$idPend] = ['cerrada' => 1];
            }
        }

        return $this->response->setJSON([
            'actividades' => $actividades,
            'links'       => $links,
        ]);
    }

    /**
     * Procesar checkboxes de pendientes en form acta_visita:
     * - Cerrar pendientes marcados (estado=CERRADA, fecha_cierre_real, id_acta_visita)
     * - Para no marcados: concatenar justificacion en evidencia_para_cerrarla
     */
    private function savePendientesActividades(int $idActa): void
    {
        $pendIds = $this->request->getPost('pendiente_id') ?? [];
        $checked = $this->request->getPost('pendiente_checked') ?? [];
        $justificaciones = $this->request->getPost('pendiente_justificacion') ?? [];

        if (empty($pendIds)) return;

        $acta = $this->actaModel->find($idActa);
        if (!$acta) return;

        $pendientesModel = new \App\Models\PendientesModel();

        foreach ($pendIds as $idPend) {
            $idPend = (int) $idPend;
            $pendRecord = $pendientesModel->find($idPend);
            if (!$pendRecord) continue;

            // Validacion: debe pertenecer al cliente del acta
            if ((int) $pendRecord['id_cliente'] !== (int) $acta['id_cliente']) continue;

            $esCerrada = in_array((string) $idPend, $checked, true);

            // Cerrar pendiente marcado si aun esta ABIERTA
            if ($esCerrada && $pendRecord['estado'] === 'ABIERTA') {
                $pendientesModel->update($idPend, [
                    'estado'            => 'CERRADA',
                    'fecha_cierre_real' => $acta['fecha_visita'],
                    'id_acta_visita'    => $idActa,
                ]);
            }

            // No marcado con justificacion: concatenar en evidencia
            if (!$esCerrada && !empty($justificaciones[$idPend])) {
                $justTexto = trim($justificaciones[$idPend]);
                $prefijo = "[Acta Visita #{$idActa} - {$acta['fecha_visita']}] No cerrada: ";
                $nuevaObs = $prefijo . $justTexto;

                $obsActual = $pendRecord['evidencia_para_cerrarla'] ?? '';
                $obsFinal = $obsActual ? $obsActual . "\n" . $nuevaObs : $nuevaObs;

                $pendientesModel->update($idPend, ['evidencia_para_cerrarla' => $obsFinal]);
            }
        }
    }
}
