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
use App\Libraries\InspeccionEmailNotifier;
use App\Models\VencimientosMantenimientoModel;
use App\Models\CicloVisitaModel;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\InspeccionVersionTrait;

class ActaVisitaController extends BaseController
{
    use AutosaveJsonTrait;
    use InspeccionVersionTrait;
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

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        $redirect = $this->request->getPost('ir_a_firmas') ? '/inspecciones/acta-visita/firma/' . $id : '/inspecciones/acta-visita/edit/' . $id;

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
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/view', $data),
            'title'   => 'Ver Acta',
        ]);
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

        // Determinar qué firmas se necesitan
        $firmantes = [];
        foreach ($integrantes as $integrante) {
            if (strtoupper($integrante['rol']) === 'ADMINISTRADOR') {
                $firmantes[] = ['tipo' => 'administrador', 'nombre' => $integrante['nombre'], 'firmado' => !empty($acta['firma_administrador'])];
            }
            if (stripos($integrante['rol'], 'VIG') !== false) {
                $firmantes[] = ['tipo' => 'vigia', 'nombre' => $integrante['nombre'], 'firmado' => !empty($acta['firma_vigia'])];
            }
        }

        // Consultor siempre firma
        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find(session()->get('user_id'));
        $firmantes[] = [
            'tipo'    => 'consultor',
            'nombre'  => $consultor['nombre_consultor'] ?? session()->get('nombre_usuario'),
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

        // Verificar firma del administrador si hay integrante con rol ADMINISTRADOR
        $integrantes = $this->integranteModel->getByActa($id);
        foreach ($integrantes as $integrante) {
            if (strtoupper($integrante['rol']) === 'ADMINISTRADOR' && empty($acta['firma_administrador'])) {
                return $this->response->setJSON(['success' => false, 'error' => 'Falta la firma del administrador']);
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
                $mime = mime_content_type($fotoPath);
                $fotosBase64[] = [
                    'data'        => 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath)),
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
}
