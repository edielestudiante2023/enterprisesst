<?php

namespace App\Controllers;

use App\Models\InspeccionLocativaModel;
use App\Models\HallazgoLocativoModel;
use App\Models\MiembroComiteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

/**
 * Inspecciones locativas para miembros COPASST (autenticados por login)
 */
class MiembroInspeccionController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected InspeccionLocativaModel $inspeccionModel;
    protected HallazgoLocativoModel $hallazgoModel;
    protected MiembroComiteModel $miembroModel;

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionLocativaModel();
        $this->hallazgoModel = new HallazgoLocativoModel();
        $this->miembroModel = new MiembroComiteModel();
    }

    /**
     * Obtener miembro logueado y verificar que sea COPASST
     */
    private function getMiembroCopasst(): ?array
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        if (!$email || !$idCliente) {
            return null;
        }

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);
        if (!$miembro) {
            return null;
        }

        // Verificar pertenencia a COPASST
        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        $esCopasst = false;
        foreach ($comites as $c) {
            if (($c['codigo'] ?? '') === 'COPASST') {
                $esCopasst = true;
                break;
            }
        }

        if (!$esCopasst) {
            return null;
        }

        $miembro['id_cliente'] = $idCliente;
        return $miembro;
    }

    /**
     * Listado de inspecciones del cliente
     */
    public function list()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a inspecciones');
        }

        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_locativa.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_locativa.id_consultor', 'left')
            ->where('tbl_inspeccion_locativa.id_cliente', $miembro['id_cliente'])
            ->orderBy('tbl_inspeccion_locativa.fecha_inspeccion', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_hallazgos'] = $this->hallazgoModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
            // Nombre del creador
            if ($insp['creado_por_tipo'] === 'miembro' && $insp['id_miembro']) {
                $m = $this->miembroModel->find($insp['id_miembro']);
                $insp['nombre_creador'] = $m['nombre_completo'] ?? 'Miembro';
            } else {
                $insp['nombre_creador'] = $insp['nombre_consultor'] ?? 'Consultor';
            }
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/list', [
                'inspecciones' => $inspecciones,
                'cliente' => $cliente,
                'miembro' => $miembro,
            ]),
            'title' => 'Inspecciones Locativas',
            'miembro' => $miembro,
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/form', [
                'title' => 'Nueva Inspección Locativa',
                'inspeccion' => null,
                'hallazgos' => [],
                'cliente' => $cliente,
                'miembro' => $miembro,
                'idCliente' => $miembro['id_cliente'],
            ]),
            'title' => 'Nueva Inspección',
            'miembro' => $miembro,
        ]);
    }

    /**
     * Guardar nueva inspección
     */
    public function store()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard');
        }

        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            $rules = [
                'fecha_inspeccion' => 'required|valid_date',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $inspeccionData = [
            'id_cliente'       => $miembro['id_cliente'],
            'id_consultor'     => null,
            'id_miembro'       => $miembro['id_miembro'],
            'creado_por_tipo'  => 'miembro',
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'borrador',
        ];

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $detailIds = $this->saveHallazgos($idInspeccion);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/miembro/inspecciones/locativa/edit/' . $idInspeccion)
            ->with('msg', 'Inspección guardada como borrador');
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard');
        }

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/locativa')->with('error', 'Inspección no encontrada');
        }

        if ($inspeccion['estado'] !== 'borrador') {
            return redirect()->to('/miembro/inspecciones/locativa/view/' . $id);
        }

        // Solo puede editar sus propias inspecciones
        if ($inspeccion['creado_por_tipo'] === 'miembro' && (int)$inspeccion['id_miembro'] !== (int)$miembro['id_miembro']) {
            return redirect()->to('/miembro/inspecciones/locativa')->with('error', 'Solo puedes editar tus propias inspecciones');
        }

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/form', [
                'title' => 'Editar Inspección',
                'inspeccion' => $inspeccion,
                'hallazgos' => $this->hallazgoModel->getByInspeccion($id),
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
                'idCliente' => $miembro['id_cliente'],
            ]),
            'title' => 'Editar Inspección',
            'miembro' => $miembro,
        ]);
    }

    /**
     * Actualizar inspección existente
     */
    public function update($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard');
        }

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente'] || $inspeccion['estado'] !== 'borrador') {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada o no editable', 404);
            }
            return redirect()->to('/miembro/inspecciones/locativa')->with('error', 'No se puede editar');
        }

        $this->inspeccionModel->update($id, [
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ]);

        $detailIds = $this->saveHallazgos($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/miembro/inspecciones/locativa/edit/' . $id)
            ->with('msg', 'Inspección actualizada');
    }

    /**
     * Vista de solo lectura
     */
    public function view($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard');
        }

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/locativa')->with('error', 'Inspección no encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Nombre del creador
        $realizadoPor = null;
        if ($inspeccion['creado_por_tipo'] === 'miembro' && $inspeccion['id_miembro']) {
            $m = $this->miembroModel->find($inspeccion['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro COPASST';
        }

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/view', [
                'title' => 'Ver Inspección',
                'inspeccion' => $inspeccion,
                'cliente' => $clientModel->find($inspeccion['id_cliente']),
                'consultor' => $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'hallazgos' => $this->hallazgoModel->getByInspeccion($id),
            ]),
            'title' => 'Ver Inspección',
            'miembro' => $miembro,
        ]);
    }

    /**
     * Finalizar inspección: generar PDF + subir a reportes + email al consultor
     */
    public function finalizar($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard');
        }

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/locativa')->with('error', 'Inspección no encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        // Enviar email al consultor asignado al cliente
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);
        if (!empty($cliente['id_consultor'])) {
            $this->notificarConsultor($cliente, $miembro, $inspeccion, $pdfPath);
        }

        return redirect()->to('/miembro/inspecciones/locativa/view/' . $id)
            ->with('msg', 'Inspección finalizada y PDF generado.');
    }

    /**
     * Servir PDF inline
     */
    public function generatePdf($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) {
            return redirect()->to('/miembro/dashboard');
        }

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/locativa');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="inspeccion_locativa_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ===== MÉTODOS PRIVADOS =====

    /**
     * Guardar hallazgos desde POST con fotos
     */
    private function saveHallazgos(int $idInspeccion): array
    {
        $descripciones = $this->request->getPost('hallazgo_descripcion') ?? [];
        $estados = $this->request->getPost('hallazgo_estado') ?? [];
        $observaciones = $this->request->getPost('hallazgo_observaciones') ?? [];
        $hallazgoIds = $this->request->getPost('hallazgo_id') ?? [];

        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->hallazgoModel->getByInspeccion($idInspeccion) as $h) {
            $existentes[$h['id']] = $h;
            $existentesPorOrden[(int)$h['orden']] = $h;
        }

        $this->hallazgoModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/locativas/hallazgos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();
        $newIds = [];

        foreach ($descripciones as $i => $descripcion) {
            if (empty(trim($descripcion))) {
                continue;
            }

            $existenteId = $hallazgoIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;
            if (!$existente) {
                $existente = $existentesPorOrden[$i + 1] ?? null;
            }

            $imagenPath = $existente['imagen'] ?? null;
            if (isset($files['hallazgo_imagen'][$i]) && $files['hallazgo_imagen'][$i]->isValid() && !$files['hallazgo_imagen'][$i]->hasMoved()) {
                $file = $files['hallazgo_imagen'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $imagenPath = 'uploads/inspecciones/locativas/hallazgos/' . $fileName;
            }

            $correccionPath = $existente['imagen_correccion'] ?? null;
            if (isset($files['hallazgo_correccion'][$i]) && $files['hallazgo_correccion'][$i]->isValid() && !$files['hallazgo_correccion'][$i]->hasMoved()) {
                $file = $files['hallazgo_correccion'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $correccionPath = 'uploads/inspecciones/locativas/hallazgos/' . $fileName;
            }

            $this->hallazgoModel->insert([
                'id_inspeccion'     => $idInspeccion,
                'descripcion'       => trim($descripcion),
                'imagen'            => $imagenPath,
                'imagen_correccion' => $correccionPath,
                'fecha_hallazgo'    => $existente['fecha_hallazgo'] ?? date('Y-m-d'),
                'fecha_correccion'  => !empty($correccionPath) ? date('Y-m-d') : ($existente['fecha_correccion'] ?? null),
                'estado'            => $estados[$i] ?? 'ABIERTO',
                'observaciones'     => $observaciones[$i] ?? null,
                'orden'             => $i + 1,
            ]);
            $newIds[] = $this->hallazgoModel->getInsertID();
        }

        return $newIds;
    }

    /**
     * Generar PDF con DOMPDF
     */
    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null;
        $hallazgos = $this->hallazgoModel->getByInspeccion($id);

        // Nombre del realizador
        $realizadoPor = null;
        if ($inspeccion['creado_por_tipo'] === 'miembro' && $inspeccion['id_miembro']) {
            $m = $this->miembroModel->find($inspeccion['id_miembro']);
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

        foreach ($hallazgos as &$h) {
            $h['imagen_base64'] = '';
            if (!empty($h['imagen'])) {
                $fotoPath = FCPATH . $h['imagen'];
                if (file_exists($fotoPath)) {
                    $h['imagen_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
            $h['correccion_base64'] = '';
            if (!empty($h['imagen_correccion'])) {
                $fotoPath = FCPATH . $h['imagen_correccion'];
                if (file_exists($fotoPath)) {
                    $h['correccion_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'realizadoPor' => $realizadoPor,
            'hallazgos'    => $hallazgos,
            'logoBase64'   => $logoBase64,
        ];

        $html = view('inspecciones/inspeccion_locativa/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/locativas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'inspeccion_locativa_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Registra el PDF en tbl_reporte
     */
    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();

        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) {
            return false;
        }

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 10)
            ->like('observaciones', 'insp_locativa_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'inspeccion_locativa_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION LOCATIVA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 10,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por miembro COPASST. insp_locativa_id:' . $inspeccion['id'],
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
     * Notificar al consultor asignado que se finalizó una inspección
     */
    private function notificarConsultor(array $cliente, array $miembro, array $inspeccion, string $pdfPath): bool
    {
        $consultorModel = new ConsultantModel();
        $consultor = $consultorModel->find($cliente['id_consultor']);

        if (!$consultor || empty($consultor['correo_consultor'])) {
            return false;
        }

        $fecha = date('d/m/Y', strtotime($inspeccion['fecha_inspeccion']));

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Nueva Inspección Locativa - COPASST</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$consultor['nombre_consultor']}</strong>,</p>
                <p>Un miembro del COPASST ha finalizado una inspección locativa:</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <p style='margin: 5px 0;'><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p style='margin: 5px 0;'><strong>Fecha inspección:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Realizada por:</strong> {$miembro['nombre_completo']}</p>
                    <p style='margin: 5px 0;'><strong>Cargo:</strong> {$miembro['cargo']}</p>
                </div>

                <p>El PDF ha sido generado y registrado en el sistema de reportes.</p>

                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>
                    Este es un mensaje automático del sistema EnterpriseSST.
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
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject("Nueva Inspección Locativa COPASST - {$cliente['nombre_cliente']} - {$fecha}");
            $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor inspección COPASST: ' . $e->getMessage());
            return false;
        }
    }
}
