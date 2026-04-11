<?php

namespace App\Controllers;

use App\Models\InvestigacionAccidenteModel;
use App\Models\InvestigacionTestigoModel;
use App\Models\InvestigacionEvidenciaModel;
use App\Models\InvestigacionMedidaModel;
use App\Models\MiembroComiteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

class MiembroInvestigacionAccidenteController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected InvestigacionAccidenteModel $invModel;
    protected InvestigacionTestigoModel $testigoModel;
    protected InvestigacionEvidenciaModel $evidenciaModel;
    protected InvestigacionMedidaModel $medidaModel;
    protected MiembroComiteModel $miembroModel;

    public function __construct()
    {
        $this->invModel = new InvestigacionAccidenteModel();
        $this->testigoModel = new InvestigacionTestigoModel();
        $this->evidenciaModel = new InvestigacionEvidenciaModel();
        $this->medidaModel = new InvestigacionMedidaModel();
        $this->miembroModel = new MiembroComiteModel();
    }

    private function getMiembroCopasst(): ?array
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        if (!$email || !$idCliente) return null;

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);
        if (!$miembro) return null;

        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        $esCopasst = false;
        foreach ($comites as $c) {
            if (($c['codigo'] ?? '') === 'COPASST') {
                $esCopasst = true;
                break;
            }
        }
        if (!$esCopasst) return null;

        $miembro['id_cliente'] = $idCliente;
        return $miembro;
    }

    public function list()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso');
        }

        $investigaciones = $this->invModel
            ->select('tbl_investigacion_accidente.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_investigacion_accidente.id_consultor', 'left')
            ->where('tbl_investigacion_accidente.id_cliente', $miembro['id_cliente'])
            ->orderBy('tbl_investigacion_accidente.fecha_evento', 'DESC')
            ->findAll();

        foreach ($investigaciones as &$inv) {
            if ($inv['creado_por_tipo'] === 'miembro' && $inv['id_miembro']) {
                $m = $this->miembroModel->find($inv['id_miembro']);
                $inv['nombre_creador'] = $m['nombre_completo'] ?? 'Miembro';
            } else {
                $inv['nombre_creador'] = $inv['nombre_consultor'] ?? 'Consultor';
            }
        }

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/investigacion_list', [
                'investigaciones' => $investigaciones,
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
            ]),
            'title' => 'Investigación AT/IT',
            'miembro' => $miembro,
        ]);
    }

    public function create()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso');
        }

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/investigacion_form', [
                'title' => 'Nueva Investigación',
                'inv' => null,
                'testigos' => [],
                'evidencias' => [],
                'medidas' => [],
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
                'idCliente' => $miembro['id_cliente'],
            ]),
            'title' => 'Nueva Investigación',
            'miembro' => $miembro,
        ]);
    }

    public function store()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            $rules = [
                'tipo_evento' => 'required|in_list[accidente,incidente]',
                'fecha_evento' => 'required|valid_date',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getPostData();
        $data['id_cliente'] = $miembro['id_cliente'];
        $data['id_miembro'] = $miembro['id_miembro'];
        $data['creado_por_tipo'] = 'miembro';
        $data['estado'] = 'borrador';

        $this->invModel->insert($data);
        $id = $this->invModel->getInsertID();

        $this->saveTestigos($id);
        $this->saveEvidencias($id);
        $this->saveMedidas($id);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($id);
        }

        return redirect()->to('/miembro/inspecciones/investigacion-accidente/edit/' . $id)
            ->with('msg', 'Investigación guardada como borrador');
    }

    public function edit($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inv = $this->invModel->find($id);
        if (!$inv || (int)$inv['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        if ($inv['estado'] !== 'borrador') {
            return redirect()->to('/miembro/inspecciones/investigacion-accidente/view/' . $id);
        }

        if ($inv['creado_por_tipo'] === 'miembro' && (int)$inv['id_miembro'] !== (int)$miembro['id_miembro']) {
            return redirect()->to('/miembro/inspecciones/investigacion-accidente')->with('error', 'Solo puedes editar tus propias investigaciones');
        }

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/investigacion_form', [
                'title' => 'Editar Investigación',
                'inv' => $inv,
                'testigos' => $this->testigoModel->getByInvestigacion($id),
                'evidencias' => $this->evidenciaModel->getByInvestigacion($id),
                'medidas' => $this->medidaModel->getByInvestigacion($id),
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
                'idCliente' => $miembro['id_cliente'],
            ]),
            'title' => 'Editar Investigación',
            'miembro' => $miembro,
        ]);
    }

    public function update($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inv = $this->invModel->find($id);
        if (!$inv || (int)$inv['id_cliente'] !== (int)$miembro['id_cliente'] || $inv['estado'] !== 'borrador') {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada o no editable', 404);
            }
            return redirect()->to('/miembro/inspecciones/investigacion-accidente')->with('error', 'No se puede editar');
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

        return redirect()->to('/miembro/inspecciones/investigacion-accidente/edit/' . $id)
            ->with('msg', 'Investigación actualizada');
    }

    public function view($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inv = $this->invModel->find($id);
        if (!$inv || (int)$inv['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $realizadoPor = null;
        if ($inv['creado_por_tipo'] === 'miembro' && $inv['id_miembro']) {
            $m = $this->miembroModel->find($inv['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro COPASST';
        }

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/investigacion_view', [
                'title' => 'Ver Investigación',
                'inv' => $inv,
                'cliente' => $clientModel->find($inv['id_cliente']),
                'consultor' => $inv['id_consultor'] ? $consultantModel->find($inv['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'testigos' => $this->testigoModel->getByInvestigacion($id),
                'evidencias' => $this->evidenciaModel->getByInvestigacion($id),
                'medidas' => $this->medidaModel->getByInvestigacion($id),
            ]),
            'title' => 'Ver Investigación',
            'miembro' => $miembro,
        ]);
    }

    public function finalizar($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inv = $this->invModel->find($id);
        if (!$inv || (int)$inv['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/investigacion-accidente')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->invModel->update($id, [
            'estado' => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inv = $this->invModel->find($id);
        $this->uploadToReportes($inv, $pdfPath);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);
        if (!empty($cliente['id_consultor'])) {
            $this->notificarConsultor($cliente, $miembro, $inv, $pdfPath);
        }

        return redirect()->to('/miembro/inspecciones/investigacion-accidente/view/' . $id)
            ->with('msg', 'Investigación finalizada y PDF generado.');
    }

    public function generatePdf($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inv = $this->invModel->find($id);
        if (!$inv || (int)$inv['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/investigacion-accidente');
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

    // ===== MÉTODOS PRIVADOS =====

    private function getPostData(): array
    {
        $tipo = $this->request->getPost('tipo_evento');
        return [
            'tipo_evento' => $tipo,
            'gravedad' => $tipo === 'accidente' ? $this->request->getPost('gravedad') : null,
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
            'parte_cuerpo_lesionada' => $tipo === 'accidente' ? $this->request->getPost('parte_cuerpo_lesionada') : null,
            'tipo_lesion' => $tipo === 'accidente' ? $this->request->getPost('tipo_lesion') : null,
            'agente_accidente' => $tipo === 'accidente' ? $this->request->getPost('agente_accidente') : null,
            'mecanismo_accidente' => $tipo === 'accidente' ? $this->request->getPost('mecanismo_accidente') : null,
            'dias_incapacidad' => $tipo === 'accidente' ? $this->request->getPost('dias_incapacidad') : null,
            'numero_furat' => $tipo === 'accidente' ? $this->request->getPost('numero_furat') : null,
            'potencial_danio' => $tipo === 'incidente' ? $this->request->getPost('potencial_danio') : null,
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

        $realizadoPor = null;
        if ($inv['creado_por_tipo'] === 'miembro' && $inv['id_miembro']) {
            $m = $this->miembroModel->find($inv['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro COPASST';
        }

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        foreach ($evidencias as &$e) {
            $e['imagen_base64'] = '';
            if (!empty($e['imagen'])) {
                $fotoPath = FCPATH . $e['imagen'];
                if (file_exists($fotoPath)) {
                    $e['imagen_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

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
            'realizadoPor' => $realizadoPor,
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
            'observaciones' => 'Generado por miembro COPASST. inv_accidente_id:' . $inv['id'],
            'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    private function notificarConsultor(array $cliente, array $miembro, array $inv, string $pdfPath): bool
    {
        $consultorModel = new ConsultantModel();
        $consultor = $consultorModel->find($cliente['id_consultor']);

        if (!$consultor || empty($consultor['correo_consultor'])) return false;

        $fecha = date('d/m/Y', strtotime($inv['fecha_evento']));
        $tipoLabel = $inv['tipo_evento'] === 'incidente' ? 'Incidente' : 'Accidente';

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Nueva Investigación de {$tipoLabel} - COPASST</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$consultor['nombre_consultor']}</strong>,</p>
                <p>Un miembro del COPASST ha finalizado una investigación de {$tipoLabel} de trabajo:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <p style='margin: 5px 0;'><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p style='margin: 5px 0;'><strong>Tipo:</strong> {$tipoLabel}</p>
                    <p style='margin: 5px 0;'><strong>Fecha evento:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Realizada por:</strong> {$miembro['nombre_completo']}</p>
                    <p style='margin: 5px 0;'><strong>Cargo:</strong> {$miembro['cargo']}</p>
                </div>
                <p>El PDF ha sido generado y registrado en el sistema de reportes.</p>
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>Este es un mensaje automático del sistema EnterpriseSST.</p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>EnterpriseSST - Sistema de Gestión de Seguridad y Salud en el Trabajo</p>
            </div>
        </div>";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject("Nueva Investigación {$tipoLabel} COPASST - {$cliente['nombre_cliente']} - {$fecha}");
            $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor investigación COPASST: ' . $e->getMessage());
            return false;
        }
    }
}
