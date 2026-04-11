<?php

namespace App\Controllers;

use App\Models\PausaActivaModel;
use App\Models\PausaActivaRegistroModel;
use App\Models\MiembroComiteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

class MiembroPausasActivasController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected PausaActivaModel $pausaModel;
    protected PausaActivaRegistroModel $registroModel;
    protected MiembroComiteModel $miembroModel;

    public function __construct()
    {
        $this->pausaModel = new PausaActivaModel();
        $this->registroModel = new PausaActivaRegistroModel();
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
            if (($c['codigo'] ?? '') === 'COPASST') { $esCopasst = true; break; }
        }
        if (!$esCopasst) return null;

        $miembro['id_cliente'] = $idCliente;
        return $miembro;
    }

    public function list()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso');

        $inspecciones = $this->pausaModel
            ->select('tbl_pausas_activas.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_pausas_activas.id_consultor', 'left')
            ->where('tbl_pausas_activas.id_cliente', $miembro['id_cliente'])
            ->orderBy('tbl_pausas_activas.fecha_actividad', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_registros'] = $this->registroModel->where('id_pausa', $insp['id'])->countAllResults(false);
            if ($insp['creado_por_tipo'] === 'miembro' && $insp['id_miembro']) {
                $m = $this->miembroModel->find($insp['id_miembro']);
                $insp['nombre_creador'] = $m['nombre_completo'] ?? 'Miembro';
            } else {
                $insp['nombre_creador'] = $insp['nombre_consultor'] ?? 'Consultor';
            }
        }

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/pausas_activas_list', [
                'inspecciones' => $inspecciones,
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
            ]),
            'title' => 'Pausas Activas',
            'miembro' => $miembro,
        ]);
    }

    public function create()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/pausas_activas_form', [
                'title' => 'Nueva Pausa Activa',
                'inspeccion' => null,
                'registros' => [],
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
                'idCliente' => $miembro['id_cliente'],
            ]),
            'title' => 'Nueva Pausa Activa',
            'miembro' => $miembro,
        ]);
    }

    public function store()
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['fecha_actividad' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $this->pausaModel->insert([
            'id_cliente'      => $miembro['id_cliente'],
            'id_miembro'      => $miembro['id_miembro'],
            'creado_por_tipo' => 'miembro',
            'fecha_actividad' => $this->request->getPost('fecha_actividad'),
            'observaciones'   => $this->request->getPost('observaciones'),
            'estado'          => 'borrador',
        ]);
        $id = $this->pausaModel->getInsertID();
        $detailIds = $this->saveRegistros($id);

        if ($isAutosave) return $this->autosaveJsonSuccess($id, ['detail_ids' => $detailIds]);

        return redirect()->to('/miembro/inspecciones/pausas-activas/edit/' . $id)->with('msg', 'Guardada como borrador');
    }

    public function edit($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente'] || $inspeccion['estado'] !== 'borrador') {
            return redirect()->to('/miembro/inspecciones/pausas-activas')->with('error', 'No encontrada o no editable');
        }

        if ($inspeccion['creado_por_tipo'] === 'miembro' && (int)$inspeccion['id_miembro'] !== (int)$miembro['id_miembro']) {
            return redirect()->to('/miembro/inspecciones/pausas-activas')->with('error', 'Solo puedes editar tus propios registros');
        }

        $clienteModel = new ClientModel();

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/pausas_activas_form', [
                'title' => 'Editar Pausa Activa',
                'inspeccion' => $inspeccion,
                'registros' => $this->registroModel->getByPausa($id),
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
                'idCliente' => $miembro['id_cliente'],
            ]),
            'title' => 'Editar Pausa Activa',
            'miembro' => $miembro,
        ]);
    }

    public function update($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente'] || $inspeccion['estado'] !== 'borrador') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 404);
            return redirect()->to('/miembro/inspecciones/pausas-activas');
        }

        $this->pausaModel->update($id, [
            'fecha_actividad' => $this->request->getPost('fecha_actividad'),
            'observaciones'   => $this->request->getPost('observaciones'),
        ]);

        $detailIds = $this->saveRegistros($id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);

        return redirect()->to('/miembro/inspecciones/pausas-activas/edit/' . $id)->with('msg', 'Actualizada');
    }

    public function view($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/pausas-activas');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $realizadoPor = null;
        if ($inspeccion['creado_por_tipo'] === 'miembro' && $inspeccion['id_miembro']) {
            $m = $this->miembroModel->find($inspeccion['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro COPASST';
        }

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/pausas_activas_view', [
                'inspeccion' => $inspeccion,
                'cliente' => $clientModel->find($inspeccion['id_cliente']),
                'consultor' => $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'registros' => $this->registroModel->getByPausa($id),
            ]),
            'title' => 'Ver Pausa Activa',
            'miembro' => $miembro,
        ]);
    }

    public function finalizar($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/pausas-activas');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->pausaModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $inspeccion = $this->pausaModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);
        if (!empty($cliente['id_consultor'])) {
            $this->notificarConsultor($cliente, $miembro, $inspeccion);
        }

        return redirect()->to('/miembro/inspecciones/pausas-activas/view/' . $id)->with('msg', 'Pausa activa finalizada.');
    }

    public function generatePdf($id)
    {
        $miembro = $this->getMiembroCopasst();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/pausas-activas');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="pausas_activas_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ===== PRIVADOS =====

    private function saveRegistros(int $idPausa): array
    {
        $tipos = $this->request->getPost('registro_tipo') ?? [];
        $registroIds = $this->request->getPost('registro_id') ?? [];

        $existentes = [];
        foreach ($this->registroModel->getByPausa($idPausa) as $r) {
            $existentes[$r['id']] = $r;
        }
        $this->registroModel->deleteByPausa($idPausa);

        $dir = FCPATH . 'uploads/inspecciones/pausas_activas/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $files = $this->request->getFiles();
        $newIds = [];

        foreach ($tipos as $i => $tipo) {
            if (empty(trim($tipo))) continue;

            $existenteId = $registroIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;

            $imagenPath = $existente['imagen'] ?? null;
            if (isset($files['registro_imagen'][$i]) && $files['registro_imagen'][$i]->isValid() && !$files['registro_imagen'][$i]->hasMoved()) {
                $file = $files['registro_imagen'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $imagenPath = 'uploads/inspecciones/pausas_activas/' . $fileName;
            }

            $this->registroModel->insert([
                'id_pausa'   => $idPausa,
                'tipo_pausa' => trim($tipo),
                'imagen'     => $imagenPath,
                'orden'      => $i + 1,
            ]);
            $newIds[] = $this->registroModel->getInsertID();
        }
        return $newIds;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->pausaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null;
        $registros = $this->registroModel->getByPausa($id);

        $realizadoPor = null;
        if ($inspeccion['creado_por_tipo'] === 'miembro' && $inspeccion['id_miembro']) {
            $m = $this->miembroModel->find($inspeccion['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro COPASST';
        }

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        foreach ($registros as &$r) {
            $r['imagen_base64'] = '';
            if (!empty($r['imagen']) && file_exists(FCPATH . $r['imagen'])) {
                $r['imagen_base64'] = $this->fotoABase64ParaPdf(FCPATH . $r['imagen']);
            }
        }

        $html = view('inspecciones/pausas_activas/pdf', [
            'inspeccion' => $inspeccion, 'cliente' => $cliente, 'consultor' => $consultor,
            'realizadoPor' => $realizadoPor, 'registros' => $registros, 'logoBase64' => $logoBase64,
        ]);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/pausas_activas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'pausas_activas_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        return $pdfPath;
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'pausas_activas_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte'  => 'PAUSAS ACTIVAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_actividad'],
            'id_detailreport' => 10, 'id_report_type' => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por miembro COPASST. pausas_activas_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)->where('id_detailreport', 10)
            ->like('observaciones', 'pausas_activas_id:' . $inspeccion['id'])->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    private function notificarConsultor(array $cliente, array $miembro, array $inspeccion): bool
    {
        $consultor = (new ConsultantModel())->find($cliente['id_consultor']);
        if (!$consultor || empty($consultor['correo_consultor'])) return false;

        $fecha = date('d/m/Y', strtotime($inspeccion['fecha_actividad']));
        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Nuevo Registro de Pausas Activas - COPASST</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$consultor['nombre_consultor']}</strong>,</p>
                <p>Un miembro del COPASST ha registrado pausas activas:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <p style='margin: 5px 0;'><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Realizada por:</strong> {$miembro['nombre_completo']}</p>
                </div>
                <p>El PDF ha sido generado y registrado en el sistema de reportes.</p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>EnterpriseSST - Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
            </div>
        </div>";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject("Pausas Activas COPASST - {$cliente['nombre_cliente']} - {$fecha}");
            $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
            $email->addContent("text/html", $mensaje);
            $response = (new \SendGrid(getenv('SENDGRID_API_KEY')))->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor pausas activas: ' . $e->getMessage());
            return false;
        }
    }
}
