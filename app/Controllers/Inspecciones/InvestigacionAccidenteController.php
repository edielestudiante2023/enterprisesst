<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InvestigacionAccidenteModel;
use App\Models\InvestigacionTestigoModel;
use App\Models\InvestigacionEvidenciaModel;
use App\Models\InvestigacionMedidaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

class InvestigacionAccidenteController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected InvestigacionAccidenteModel $invModel;
    protected InvestigacionTestigoModel $testigoModel;
    protected InvestigacionEvidenciaModel $evidenciaModel;
    protected InvestigacionMedidaModel $medidaModel;

    public function __construct()
    {
        $this->invModel = new InvestigacionAccidenteModel();
        $this->testigoModel = new InvestigacionTestigoModel();
        $this->evidenciaModel = new InvestigacionEvidenciaModel();
        $this->medidaModel = new InvestigacionMedidaModel();
    }

    public function list()
    {
        $investigaciones = $this->invModel
            ->select('tbl_investigacion_accidente.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_investigacion_accidente.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_investigacion_accidente.id_consultor', 'left')
            ->orderBy('tbl_investigacion_accidente.fecha_evento', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/investigacion_accidente/list', [
                'title' => 'Investigación de Accidentes e Incidentes',
                'investigaciones' => $investigaciones,
            ]),
            'title' => 'Investigación AT/IT',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/investigacion_accidente/form', [
                'title' => 'Nueva Investigación',
                'inv' => null,
                'testigos' => [],
                'evidencias' => [],
                'medidas' => [],
                'idCliente' => $idCliente,
            ]),
            'title' => 'Nueva Investigación',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            $rules = [
                'id_cliente' => 'required|integer',
                'tipo_evento' => 'required|in_list[accidente,incidente]',
                'fecha_evento' => 'required|valid_date',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getPostData();
        $data['id_consultor'] = $userId;
        $data['creado_por_tipo'] = 'consultor';
        $data['estado'] = 'borrador';

        $this->invModel->insert($data);
        $id = $this->invModel->getInsertID();

        $this->saveTestigos($id);
        $this->saveEvidencias($id);
        $this->saveMedidas($id);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($id);
        }

        return redirect()->to('/inspecciones/investigacion-accidente/edit/' . $id)
            ->with('msg', 'Investigación guardada como borrador');
    }

    public function edit($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/investigacion_accidente/form', [
                'title' => 'Editar Investigación',
                'inv' => $inv,
                'testigos' => $this->testigoModel->getByInvestigacion($id),
                'evidencias' => $this->evidenciaModel->getByInvestigacion($id),
                'medidas' => $this->medidaModel->getByInvestigacion($id),
                'idCliente' => $inv['id_cliente'],
            ]),
            'title' => 'Editar Investigación',
        ]);
    }

    public function update($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $data = $this->getPostData();
        $this->invModel->update($id, $data);

        $this->saveTestigos($id);
        $this->saveEvidencias($id);
        $this->saveMedidas($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/investigacion-accidente/edit/' . $id)
            ->with('msg', 'Investigación actualizada');
    }

    public function view($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/investigacion_accidente/view', [
                'title' => 'Ver Investigación',
                'inv' => $inv,
                'cliente' => $clientModel->find($inv['id_cliente']),
                'consultor' => $inv['id_consultor'] ? $consultantModel->find($inv['id_consultor']) : null,
                'testigos' => $this->testigoModel->getByInvestigacion($id),
                'evidencias' => $this->evidenciaModel->getByInvestigacion($id),
                'medidas' => $this->medidaModel->getByInvestigacion($id),
            ]),
            'title' => 'Ver Investigación',
        ]);
    }

    /**
     * Página de firmas (canvas táctil + WhatsApp)
     */
    public function firma($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();

        // Pre-generar tokens de firma remota para cada firmante sin firma
        $tokensRemoto = [];
        foreach (['jefe', 'copasst', 'sst'] as $tipo) {
            if (empty($inv["firma_{$tipo}"])) {
                $token = bin2hex(random_bytes(32));
                $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));
                $this->invModel->update($id, [
                    'token_firma_remota' => $token,
                    'token_firma_tipo' => $tipo,
                    'token_firma_expiracion' => $expiracion,
                ]);
                $tokensRemoto[$tipo] = base_url("investigacion-accidente/firmar-remoto/{$token}");
            }
        }
        // Re-leer con último token guardado
        $inv = $this->invModel->find($id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/investigacion_accidente/firma', [
                'title' => 'Firmas del Equipo Investigador',
                'inv' => $inv,
                'cliente' => $clientModel->find($inv['id_cliente']),
                'tokensRemoto' => $tokensRemoto,
            ]),
            'title' => 'Firmas',
        ]);
    }

    /**
     * AJAX: guardar firma desde canvas
     */
    public function saveFirma($id)
    {
        $tipo = $this->request->getPost('tipo');
        $firmaBase64 = $this->request->getPost('firma_imagen');

        if (!in_array($tipo, ['jefe', 'copasst', 'sst'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo inválido']);
        }

        $inv = $this->invModel->find($id);
        if (!$inv) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrada']);
        }

        $firmaData = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));

        $dir = FCPATH . 'uploads/inspecciones/investigacion_accidente/firmas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = "firma_{$tipo}_{$id}_" . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $campo = "firma_{$tipo}";
        $this->invModel->update($id, [
            $campo => "uploads/inspecciones/investigacion_accidente/firmas/{$nombreArchivo}",
        ]);

        return $this->response->setJSON(['success' => true, 'campo' => $campo]);
    }

    /**
     * AJAX: genera token de firma remota y devuelve URL para WhatsApp
     */
    public function generarTokenFirma(int $id)
    {
        $tipo = $this->request->getPost('tipo');
        if (!in_array($tipo, ['jefe', 'copasst', 'sst'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo inválido']);
        }

        $inv = $this->invModel->find($id);
        if (!$inv) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrada']);
        }

        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->invModel->update($id, [
            'token_firma_remota' => $token,
            'token_firma_tipo' => $tipo,
            'token_firma_expiracion' => $expiracion,
        ]);

        $url = base_url("investigacion-accidente/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'tipo' => $tipo]);
    }

    /**
     * AJAX: enviar enlace de firma por email
     */
    public function enviarEnlaceFirma(int $id)
    {
        $email = $this->request->getPost('email');
        $url = $this->request->getPost('url');
        $tipo = $this->request->getPost('tipo');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Correo inválido']);
        }

        $inv = $this->invModel->find($id);
        if (!$inv) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrada']);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inv['id_cliente']);

        $tipoLabels = ['jefe' => 'Jefe Inmediato', 'copasst' => 'Representante COPASST', 'sst' => 'Responsable SST'];
        $tipoLabel = $tipoLabels[$tipo] ?? 'Investigador';
        $tipoEvento = $inv['tipo_evento'] === 'incidente' ? 'Incidente' : 'Accidente';

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0; font-size: 18px;'>Firma de Investigacion de {$tipoEvento}</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$tipoLabel}</strong>,</p>
                <p>Se requiere su firma para la investigacion de {$tipoEvento} de trabajo de la empresa <strong>" . esc($cliente['nombre_cliente'] ?? '') . "</strong>.</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <p style='margin: 5px 0;'><strong>Tipo:</strong> {$tipoEvento}</p>
                    <p style='margin: 5px 0;'><strong>Fecha evento:</strong> " . date('d/m/Y', strtotime($inv['fecha_evento'])) . "</p>
                    <p style='margin: 5px 0;'><strong>Firma como:</strong> {$tipoLabel}</p>
                </div>

                <div style='text-align: center; margin: 25px 0;'>
                    <a href='{$url}' style='display: inline-block; background: #bd9751; color: white; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; font-size: 15px;'>
                        Firmar ahora
                    </a>
                </div>

                <p style='color: #666; font-size: 12px;'>Este enlace expira en 7 dias. Si no puede hacer clic en el boton, copie y pegue esta URL en su navegador:</p>
                <p style='color: #888; font-size: 11px; word-break: break-all;'>{$url}</p>

                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>Este es un mensaje automatico del sistema EnterpriseSST.</p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>EnterpriseSST - Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
            </div>
        </div>";

        try {
            $mail = new \SendGrid\Mail\Mail();
            $mail->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $mail->setSubject("Firma requerida - Investigacion {$tipoEvento} - " . ($cliente['nombre_cliente'] ?? ''));
            $mail->addTo($email);
            $mail->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($mail);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return $this->response->setJSON(['success' => true]);
            }
            return $this->response->setJSON(['success' => false, 'error' => 'Error al enviar (código ' . $response->statusCode() . ')']);
        } catch (\Exception $e) {
            log_message('error', 'Error enviando enlace firma por email: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'Error al enviar el correo']);
        }
    }

    /**
     * Página pública: canvas de firma para el firmante remoto (sin auth)
     */
    public function firmarRemoto(string $token)
    {
        $inv = $this->invModel->where('token_firma_remota', $token)->first();

        if (!$inv) {
            return view('inspecciones/acta_visita/firma_remota_error', [
                'mensaje' => 'Este enlace no es válido o ya fue usado.'
            ]);
        }

        if (strtotime($inv['token_firma_expiracion']) < time()) {
            return view('inspecciones/acta_visita/firma_remota_error', [
                'mensaje' => 'Este enlace ha expirado (7 días). Pida uno nuevo al consultor.'
            ]);
        }

        $campoFirma = 'firma_' . $inv['token_firma_tipo'];
        if (!empty($inv[$campoFirma])) {
            return view('inspecciones/acta_visita/firma_remota_error', [
                'mensaje' => 'Esta firma ya fue registrada.'
            ]);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inv['id_cliente']);

        $tipoLabels = ['jefe' => 'Jefe Inmediato', 'copasst' => 'Representante COPASST', 'sst' => 'Responsable SST'];
        $nombreCampo = 'investigador_' . $inv['token_firma_tipo'] . '_nombre';

        return view('inspecciones/investigacion_accidente/firma_remota', [
            'token' => $token,
            'inv' => $inv,
            'cliente' => $cliente,
            'tipo' => $inv['token_firma_tipo'],
            'tipoLabel' => $tipoLabels[$inv['token_firma_tipo']] ?? 'Investigador',
            'nombreFirmante' => $inv[$nombreCampo] ?? '',
        ]);
    }

    /**
     * AJAX público: recibe y guarda la firma remota
     */
    public function procesarFirmaRemota()
    {
        $token = $this->request->getPost('token');
        $firmaBase64 = $this->request->getPost('firma_imagen');

        $inv = $this->invModel->where('token_firma_remota', $token)->first();
        if (!$inv) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace inválido']);
        }

        if (strtotime($inv['token_firma_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
        }

        $tipo = $inv['token_firma_tipo'];
        $firmaData = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));

        $dir = FCPATH . 'uploads/inspecciones/investigacion_accidente/firmas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $nombreArchivo = "firma_{$tipo}_{$inv['id']}_" . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $campo = "firma_{$tipo}";
        $this->invModel->update($inv['id'], [
            $campo => "uploads/inspecciones/investigacion_accidente/firmas/{$nombreArchivo}",
            'token_firma_remota' => null,
            'token_firma_tipo' => null,
            'token_firma_expiracion' => null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function finalizar($id)
    {
        $inv = $this->invModel->find($id);
        $isAjax = $this->request->isAJAX();

        if (!$inv) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'error' => 'No encontrada']);
            }
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'error' => 'Error al generar PDF']);
            }
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->invModel->update($id, [
            'estado' => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inv = $this->invModel->find($id);
        $this->uploadToReportes($inv, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int)$inv['id_cliente'],
            (int)$inv['id_consultor'],
            $inv['tipo_evento'] === 'incidente' ? 'INVESTIGACIÓN DE INCIDENTE DE TRABAJO' : 'INVESTIGACIÓN DE ACCIDENTE DE TRABAJO',
            $inv['fecha_evento'],
            $pdfPath,
            (int)$inv['id'],
            'InvestigacionAccidente'
        );

        $emailMsg = '';
        if ($emailResult['success']) {
            $emailMsg = $emailResult['message'];
        } else {
            $emailMsg = '(Email no enviado: ' . $emailResult['error'] . ')';
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'pdf_url' => base_url($pdfPath),
                'email_msg' => $emailMsg,
            ]);
        }

        $msg = 'Investigación finalizada y PDF generado. ' . $emailMsg;
        return redirect()->to('/inspecciones/investigacion-accidente/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->invModel->update($id, ['ruta_pdf' => $pdfPath]);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="investigacion_' . $inv['tipo_evento'] . '_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function regenerarPdf($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv || $inv['estado'] !== 'completo') {
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->invModel->update($id, ['ruta_pdf' => $pdfPath]);

        $inv = $this->invModel->find($id);
        $this->uploadToReportes($inv, $pdfPath);

        return redirect()->to("/inspecciones/investigacion-accidente/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function enviarEmail($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv || $inv['estado'] !== 'completo' || empty($inv['ruta_pdf'])) {
            return redirect()->to("/inspecciones/investigacion-accidente/view/{$id}")->with('error', 'Debe estar finalizado con PDF.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int)$inv['id_cliente'],
            (int)$inv['id_consultor'],
            $inv['tipo_evento'] === 'incidente' ? 'INVESTIGACIÓN DE INCIDENTE DE TRABAJO' : 'INVESTIGACIÓN DE ACCIDENTE DE TRABAJO',
            $inv['fecha_evento'],
            $inv['ruta_pdf'],
            (int)$inv['id'],
            'InvestigacionAccidente'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/investigacion-accidente/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/investigacion-accidente/view/{$id}")->with('error', $result['error']);
    }

    public function delete($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        // Eliminar evidencias del disco
        $evidencias = $this->evidenciaModel->getByInvestigacion($id);
        foreach ($evidencias as $e) {
            if (!empty($e['imagen']) && file_exists(FCPATH . $e['imagen'])) {
                unlink(FCPATH . $e['imagen']);
            }
        }

        // Eliminar firmas del disco
        foreach (['firma_jefe', 'firma_copasst', 'firma_sst'] as $campo) {
            if (!empty($inv[$campo]) && file_exists(FCPATH . $inv[$campo])) {
                unlink(FCPATH . $inv[$campo]);
            }
        }

        // Eliminar PDF del disco
        if (!empty($inv['ruta_pdf']) && file_exists(FCPATH . $inv['ruta_pdf'])) {
            unlink(FCPATH . $inv['ruta_pdf']);
        }

        // CASCADE elimina testigos, evidencias, medidas
        $this->invModel->delete($id);

        return redirect()->to('/inspecciones/investigacion-accidente')->with('msg', 'Investigación eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

    private function getPostData(): array
    {
        return [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'tipo_evento' => $this->request->getPost('tipo_evento'),
            'gravedad' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('gravedad') : null,
            'fecha_evento' => $this->request->getPost('fecha_evento'),
            'hora_evento' => $this->request->getPost('hora_evento'),
            'lugar_exacto' => $this->request->getPost('lugar_exacto'),
            'descripcion_detallada' => $this->request->getPost('descripcion_detallada'),
            'fecha_investigacion' => $this->request->getPost('fecha_investigacion'),
            'nombre_trabajador' => $this->request->getPost('nombre_trabajador'),
            'documento_trabajador' => $this->request->getPost('documento_trabajador'),
            'cargo_trabajador' => $this->request->getPost('cargo_trabajador'),
            'area_trabajador' => $this->request->getPost('area_trabajador'),
            'antiguedad_trabajador' => $this->request->getPost('antiguedad_trabajador'),
            'tipo_vinculacion' => $this->request->getPost('tipo_vinculacion'),
            'jornada_habitual' => $this->request->getPost('jornada_habitual'),
            'parte_cuerpo_lesionada' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('parte_cuerpo_lesionada') : null,
            'tipo_lesion' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('tipo_lesion') : null,
            'agente_accidente' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('agente_accidente') : null,
            'mecanismo_accidente' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('mecanismo_accidente') : null,
            'dias_incapacidad' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('dias_incapacidad') : null,
            'numero_furat' => $this->request->getPost('tipo_evento') === 'accidente' ? $this->request->getPost('numero_furat') : null,
            'potencial_danio' => $this->request->getPost('tipo_evento') === 'incidente' ? $this->request->getPost('potencial_danio') : null,
            'actos_substandar' => $this->request->getPost('actos_substandar'),
            'condiciones_substandar' => $this->request->getPost('condiciones_substandar'),
            'factores_personales' => $this->request->getPost('factores_personales'),
            'factores_trabajo' => $this->request->getPost('factores_trabajo'),
            'metodologia_analisis' => $this->request->getPost('metodologia_analisis'),
            'descripcion_analisis' => $this->request->getPost('descripcion_analisis'),
            'investigador_jefe_nombre' => $this->request->getPost('investigador_jefe_nombre'),
            'investigador_jefe_cargo' => $this->request->getPost('investigador_jefe_cargo'),
            'investigador_copasst_nombre' => $this->request->getPost('investigador_copasst_nombre'),
            'investigador_copasst_cargo' => $this->request->getPost('investigador_copasst_cargo'),
            'investigador_sst_nombre' => $this->request->getPost('investigador_sst_nombre'),
            'investigador_sst_cargo' => $this->request->getPost('investigador_sst_cargo'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];
    }

    private function saveTestigos(int $idInv): void
    {
        $this->testigoModel->deleteByInvestigacion($idInv);

        $nombres = $this->request->getPost('testigo_nombre') ?? [];
        $cargos = $this->request->getPost('testigo_cargo') ?? [];
        $declaraciones = $this->request->getPost('testigo_declaracion') ?? [];

        foreach ($nombres as $i => $nombre) {
            if (empty(trim($nombre))) continue;
            $this->testigoModel->insert([
                'id_investigacion' => $idInv,
                'nombre' => trim($nombre),
                'cargo' => $cargos[$i] ?? null,
                'declaracion' => $declaraciones[$i] ?? null,
            ]);
        }
    }

    private function saveEvidencias(int $idInv): void
    {
        $existentes = [];
        foreach ($this->evidenciaModel->getByInvestigacion($idInv) as $e) {
            $existentes[$e['id']] = $e;
        }

        $this->evidenciaModel->deleteByInvestigacion($idInv);

        $descripciones = $this->request->getPost('evidencia_descripcion') ?? [];
        $evidenciaIds = $this->request->getPost('evidencia_id') ?? [];
        $files = $this->request->getFiles();

        $dir = FCPATH . 'uploads/inspecciones/investigacion_accidente/evidencias/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($descripciones as $i => $desc) {
            if (empty(trim($desc))) continue;

            $existenteId = $evidenciaIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;

            $imagenPath = $existente['imagen'] ?? null;
            if (isset($files['evidencia_imagen'][$i]) && $files['evidencia_imagen'][$i]->isValid() && !$files['evidencia_imagen'][$i]->hasMoved()) {
                $file = $files['evidencia_imagen'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $imagenPath = 'uploads/inspecciones/investigacion_accidente/evidencias/' . $fileName;
            }

            $this->evidenciaModel->insert([
                'id_investigacion' => $idInv,
                'descripcion' => trim($desc),
                'imagen' => $imagenPath,
                'orden' => $i + 1,
            ]);
        }
    }

    private function saveMedidas(int $idInv): void
    {
        $this->medidaModel->deleteByInvestigacion($idInv);

        $tipos = $this->request->getPost('medida_tipo') ?? [];
        $descripciones = $this->request->getPost('medida_descripcion') ?? [];
        $responsables = $this->request->getPost('medida_responsable') ?? [];
        $fechas = $this->request->getPost('medida_fecha') ?? [];
        $estados = $this->request->getPost('medida_estado') ?? [];

        foreach ($descripciones as $i => $desc) {
            if (empty(trim($desc))) continue;
            $this->medidaModel->insert([
                'id_investigacion' => $idInv,
                'tipo_medida' => $tipos[$i] ?? 'fuente',
                'descripcion' => trim($desc),
                'responsable' => $responsables[$i] ?? null,
                'fecha_cumplimiento' => !empty($fechas[$i]) ? $fechas[$i] : null,
                'estado' => $estados[$i] ?? 'pendiente',
            ]);
        }
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inv = $this->invModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inv['id_cliente']);
        $consultor = $inv['id_consultor'] ? $consultantModel->find($inv['id_consultor']) : null;
        $testigos = $this->testigoModel->getByInvestigacion($id);
        $evidencias = $this->evidenciaModel->getByInvestigacion($id);
        $medidas = $this->medidaModel->getByInvestigacion($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos evidencia a base64
        foreach ($evidencias as &$e) {
            $e['imagen_base64'] = '';
            if (!empty($e['imagen'])) {
                $fotoPath = FCPATH . $e['imagen'];
                if (file_exists($fotoPath)) {
                    $e['imagen_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        // Firmas a base64
        $firmas = [];
        foreach (['jefe', 'copasst', 'sst'] as $tipo) {
            $campo = "firma_{$tipo}";
            $firmas[$tipo] = '';
            if (!empty($inv[$campo])) {
                $fPath = FCPATH . $inv[$campo];
                if (file_exists($fPath)) {
                    $mime = mime_content_type($fPath);
                    $firmas[$tipo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fPath));
                }
            }
        }

        $data = [
            'inv' => $inv,
            'cliente' => $cliente,
            'consultor' => $consultor,
            'testigos' => $testigos,
            'evidencias' => $evidencias,
            'medidas' => $medidas,
            'logoBase64' => $logoBase64,
            'firmas' => $firmas,
        ];

        $html = view('inspecciones/investigacion_accidente/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/investigacion_accidente/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'investigacion_' . $inv['tipo_evento'] . '_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inv['ruta_pdf']) && file_exists(FCPATH . $inv['ruta_pdf'])) {
            unlink(FCPATH . $inv['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        return $pdfPath;
    }

    private function uploadToReportes(array $inv, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inv['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];
        $tipoLabel = $inv['tipo_evento'] === 'incidente' ? 'INCIDENTE' : 'ACCIDENTE';

        $existente = $reporteModel
            ->where('id_cliente', $inv['id_cliente'])
            ->where('id_report_type', 7)
            ->where('id_detailreport', 38)
            ->like('observaciones', 'inv_accidente_id:' . $inv['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'investigacion_' . $inv['tipo_evento'] . '_' . $inv['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte' => "INVESTIGACIÓN {$tipoLabel} - " . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inv['fecha_evento'],
            'id_detailreport' => 38,
            'id_report_type' => 7,
            'id_cliente' => $inv['id_cliente'],
            'estado' => 'CERRADO',
            'observaciones' => 'Generado automaticamente. inv_accidente_id:' . $inv['id'],
            'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
