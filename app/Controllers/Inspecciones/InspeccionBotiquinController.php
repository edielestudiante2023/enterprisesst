<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionBotiquinModel;
use App\Models\ElementoBotiquinModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\MantenimientoModel;
use App\Models\VencimientosMantenimientoModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\InspeccionVersionTrait;

class InspeccionBotiquinController extends BaseController
{
    use AutosaveJsonTrait;
    use InspeccionVersionTrait;
    protected InspeccionBotiquinModel $inspeccionModel;
    protected ElementoBotiquinModel $elementoModel;

    /**
     * 32 elementos fijos del botiquín tipo B (NTC 4198).
     * Cada clave corresponde a un row en tbl_elemento_botiquin.
     */
    public const ELEMENTOS = [
        // Grupo 1: Materiales de curación (10)
        'gasas_limpias'      => ['label' => 'Gasas limpias (Paq x100)',            'min' => 1,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 1],
        'gasas_esteriles'    => ['label' => 'Gasas esteriles (Paq por 3)',         'min' => 20, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 2],
        'aposito'            => ['label' => 'Aposito o compresas no esteriles',    'min' => 4,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 3],
        'esparadrapo'        => ['label' => 'Esparadrapo de tela rollo 4"',        'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 4],
        'bajalenguas'        => ['label' => 'Bajalenguas (Paq por 20)',            'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 5],
        'venda_elastica_2x5' => ['label' => 'Venda elastica 2x5 yardas',          'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 6],
        'venda_elastica_3x5' => ['label' => 'Venda elastica 3x5 yardas',          'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 7],
        'venda_elastica_5x5' => ['label' => 'Venda elastica 5x5 yardas',          'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 8],
        'venda_algodon_3x5'  => ['label' => 'Venda de algodon 3x5 yardas',        'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 9],
        'venda_algodon_5x5'  => ['label' => 'Venda de algodon 5x5 yardas',        'min' => 2,  'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 10],
        // Grupo 2: Antisépticos y soluciones (3, con vencimiento)
        'clorhexidina'       => ['label' => 'Clorhexidina/yodopovidona (Galon)',   'min' => 1,  'venc' => true,  'grupo' => 'Antisepticos y soluciones', 'orden' => 11],
        'solucion_salina'    => ['label' => 'Solucion salina 250/500cc',           'min' => 5,  'venc' => true,  'grupo' => 'Antisepticos y soluciones', 'orden' => 12],
        'alcohol'            => ['label' => 'Alcohol antiseptico 275ml',           'min' => 1,  'venc' => true,  'grupo' => 'Antisepticos y soluciones', 'orden' => 13],
        // Grupo 3: Protección e instrumental (5)
        'guantes_latex'      => ['label' => 'Guantes latex examen (Caja x100)',    'min' => 1,  'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 14],
        'termometro'         => ['label' => 'Termometro mercurio/digital',         'min' => 1,  'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 15],
        'tijeras'            => ['label' => 'Tijeras',                             'min' => 1,  'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 16],
        'linterna'           => ['label' => 'Linterna',                            'min' => 1,  'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 17],
        'pilas'              => ['label' => 'Pilas de repuesto (Par)',              'min' => 4,  'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 18],
        // Grupo 4: Equipos de inmovilización (7)
        'tabla_espinal'      => ['label' => 'Tabla espinal larga/camilla',         'min' => 1,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 19, 'special' => 'tabla_espinal'],
        'collar_adulto'      => ['label' => 'Collar cervical adulto',              'min' => 2,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 20, 'special' => 'collares'],
        'collar_nino'        => ['label' => 'Collar cervical nino',                'min' => 2,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 21, 'special' => 'collares'],
        'inmov_sup_adulto'   => ['label' => 'Inmovilizador sup. adulto',           'min' => 1,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 22, 'special' => 'inmovilizadores'],
        'inmov_inf_adulto'   => ['label' => 'Inmovilizador inf. adulto',           'min' => 1,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 23, 'special' => 'inmovilizadores'],
        'inmov_sup_nino'     => ['label' => 'Inmovilizador sup. nino',             'min' => 1,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 24, 'special' => 'inmovilizadores'],
        'inmov_inf_nino'     => ['label' => 'Inmovilizador inf. nino',             'min' => 1,  'venc' => false, 'grupo' => 'Equipos de inmovilizacion', 'orden' => 25, 'special' => 'inmovilizadores'],
        // Grupo 5: Otros (3)
        'vasos_desechables'  => ['label' => 'Vasos desechables (Paq x25)',         'min' => 1,  'venc' => false, 'grupo' => 'Otros',                     'orden' => 26],
        'tensiometro'        => ['label' => 'Tensiometro',                         'min' => 1,  'venc' => false, 'grupo' => 'Otros',                     'orden' => 27],
        'fonendoscopio'      => ['label' => 'Fonendoscopio',                       'min' => 1,  'venc' => false, 'grupo' => 'Otros',                     'orden' => 28],
        // Grupo 6: Medicamentos (3)
        'acetaminofen'       => ['label' => 'Acetaminofen 500mg (Sobre x10)',      'min' => 2,  'venc' => false, 'grupo' => 'Medicamentos',              'orden' => 29, 'medicamento' => true],
        'hidroxido_aluminio' => ['label' => 'Hidroxido de aluminio (Sobre x10)',   'min' => 1,  'venc' => false, 'grupo' => 'Medicamentos',              'orden' => 30, 'medicamento' => true],
        'asa_tabletas'       => ['label' => 'ASA tabletas 100mg (Sobre x10)',      'min' => 1,  'venc' => false, 'grupo' => 'Medicamentos',              'orden' => 31, 'medicamento' => true],
        // Grupo 7: Emergencia (1)
        'mascara_rcp'        => ['label' => 'Mascara/barrera para RCP',            'min' => 2,  'venc' => false, 'grupo' => 'Elementos de emergencia',   'orden' => 32],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionBotiquinModel();
        $this->elementoModel = new ElementoBotiquinModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_inspeccion_botiquin.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_botiquin.id_consultor', 'left')
                ->orderBy('tbl_inspeccion_botiquin.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Inspeccion de Botiquin Tipo B',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin/list', $data),
            'title'   => 'Botiquin',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Botiquin',
            'inspeccion'  => null,
            'idCliente'  => $idCliente,
            'elementos'  => self::ELEMENTOS,
            'elementosData' => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin/form', $data),
            'title'   => 'Nuevo Botiquin',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $inspeccionData = $this->getInspeccionPostData($userId);
        $inspeccionData['estado'] = 'borrador';

        // Fotos del botiquín
        $inspeccionData['foto_1'] = $this->uploadFoto('foto_1', 'uploads/inspecciones/botiquin/fotos/');
        $inspeccionData['foto_2'] = $this->uploadFoto('foto_2', 'uploads/inspecciones/botiquin/fotos/');

        // Fotos equipos especiales
        $inspeccionData['foto_tabla_espinal'] = $this->uploadFoto('foto_tabla_espinal', 'uploads/inspecciones/botiquin/fotos/');
        $inspeccionData['foto_collares'] = $this->uploadFoto('foto_collares', 'uploads/inspecciones/botiquin/fotos/');
        $inspeccionData['foto_inmovilizadores'] = $this->uploadFoto('foto_inmovilizadores', 'uploads/inspecciones/botiquin/fotos/');

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $this->saveElementos($idInspeccion);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        return redirect()->to('/inspecciones/botiquin/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin')->with('error', 'Inspeccion no encontrada');
        }

        // Indexar elementos por clave para restaurar en el form
        $elementosRaw = $this->elementoModel->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $data = [
            'title'         => 'Editar Inspeccion de Botiquin',
            'inspeccion'     => $inspeccion,
            'idCliente'     => $inspeccion['id_cliente'],
            'elementos'     => self::ELEMENTOS,
            'elementosData' => $elementosData,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin/form', $data),
            'title'   => 'Editar Botiquin',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/botiquin')->with('error', 'No se puede editar');
        }

        $userId = session()->get('user_id');
        $updateData = $this->getInspeccionPostData($userId);

        // Fotos — solo si se sube nueva
        $campos_foto = ['foto_1', 'foto_2', 'foto_tabla_espinal', 'foto_collares', 'foto_inmovilizadores'];
        foreach ($campos_foto as $campo) {
            $nuevaFoto = $this->uploadFoto($campo, 'uploads/inspecciones/botiquin/fotos/');
            if ($nuevaFoto) {
                // Borrar foto anterior
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $updateData[$campo] = $nuevaFoto;
            }
        }

        $this->inspeccionModel->update($id, $updateData);
        $this->saveElementos($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/botiquin/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $elementosRaw = $this->elementoModel->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $data = [
            'title'         => 'Ver Inspeccion de Botiquin',
            'inspeccion'     => $inspeccion,
            'cliente'       => $clientModel->find($inspeccion['id_cliente']),
            'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
            'elementos'     => self::ELEMENTOS,
            'elementosData' => $elementosData,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin/view', $data),
            'title'   => 'Ver Botiquin',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin')->with('error', 'No encontrada');
        }

        // Generar pendientes automáticos
        $this->generarPendientes($id);

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
        $this->syncVencimiento($inspeccion);

        // Registrar versión en sistema documental SST
        try {
            $idDoc = $this->registrarVersionDocumento(
                (int)$inspeccion['id_cliente'],
                'inspeccion_botiquin',
                'Inspecciones de Botiquín',
                json_encode($inspeccion),
                "Inspección realizada el {$inspeccion['fecha_inspeccion']}",
                'IB-' . $inspeccion['id_cliente'],
                (int)date('Y', strtotime($inspeccion['fecha_inspeccion']))
            );
            if ($idDoc) {
                $this->inspeccionModel->update($id, ['id_documento_sst' => $idDoc]);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'registrarVersionDocumento botiquin failed: ' . $e->getMessage());
        }

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN BOTIQUÍN',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionBotiquin'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/botiquin/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="botiquin_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin')->with('error', 'No encontrada');
        }
        // Doc 25: allow deletion regardless of estado — no guard

        // Eliminar fotos
        $campos_foto = ['foto_1', 'foto_2', 'foto_tabla_espinal', 'foto_collares', 'foto_inmovilizadores'];
        foreach ($campos_foto as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/botiquin')->with('msg', 'Inspeccion eliminada');
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/botiquin')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/botiquin/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/botiquin/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN BOTIQUÍN',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionBotiquin'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/botiquin/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/botiquin/view/{$id}")->with('error', $result['error']);
    }

    // ===== MÉTODOS PRIVADOS =====

    private function getInspeccionPostData(int $userId): array
    {
        return [
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'id_consultor'           => $userId,
            'fecha_inspeccion'       => $this->request->getPost('fecha_inspeccion'),
            'ubicacion_botiquin'     => $this->request->getPost('ubicacion_botiquin'),
            'instalado_pared'        => $this->request->getPost('instalado_pared') ?: 'SI',
            'libre_obstaculos'       => $this->request->getPost('libre_obstaculos') ?: 'SI',
            'lugar_visible'          => $this->request->getPost('lugar_visible') ?: 'SI',
            'con_senalizacion'       => $this->request->getPost('con_senalizacion') ?: 'SI',
            'tipo_botiquin'          => $this->request->getPost('tipo_botiquin') ?: 'LONA',
            'estado_botiquin'        => $this->request->getPost('estado_botiquin') ?: 'BUEN ESTADO',
            'obs_tabla_espinal'      => $this->request->getPost('obs_tabla_espinal'),
            'estado_collares'        => $this->request->getPost('estado_collares') ?: 'BUEN ESTADO',
            'estado_inmovilizadores' => $this->request->getPost('estado_inmovilizadores') ?: 'BUEN ESTADO',
            'recomendaciones'        => $this->request->getPost('recomendaciones'),
        ];
    }

    private function uploadFoto(string $fieldName, string $dir): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        return $dir . $fileName;
    }

    private function saveElementos(int $idInspeccion): void
    {
        $this->elementoModel->deleteByInspeccion($idInspeccion);

        foreach (self::ELEMENTOS as $clave => $config) {
            $cantidad = (int)($this->request->getPost('elem_' . $clave . '_cantidad') ?? 0);
            $estado = $this->request->getPost('elem_' . $clave . '_estado') ?? 'BUEN ESTADO';
            $vencimiento = null;
            if ($config['venc']) {
                $vencimiento = $this->request->getPost('elem_' . $clave . '_vencimiento') ?: null;
            }

            $this->elementoModel->insert([
                'id_inspeccion'    => $idInspeccion,
                'clave'            => $clave,
                'cantidad'         => $cantidad,
                'estado'           => $estado,
                'fecha_vencimiento'=> $vencimiento,
                'orden'            => $config['orden'],
            ]);
        }
    }

    /**
     * Auto-genera pendientes basados en los elementos del botiquín.
     * Reemplaza el webhook de Google Apps Script.
     */
    private function generarPendientes(int $id): void
    {
        $elementos = $this->elementoModel->getByInspeccion($id);
        $inspeccion = $this->inspeccionModel->find($id);
        $pendientes = [];
        $tieneMedicamentoFaltante = false;
        $hoy = date('Y-m-d');

        foreach ($elementos as $elem) {
            $clave = $elem['clave'];
            $config = self::ELEMENTOS[$clave] ?? null;
            if (!$config) continue;

            $label = $config['label'];
            $min = $config['min'];
            $cantidad = (int)$elem['cantidad'];
            $estado = $elem['estado'];
            $vencimiento = $elem['fecha_vencimiento'] ?? null;

            // Cantidad insuficiente
            if ($cantidad < $min) {
                $pendientes[] = "{$label} - Presenta {$cantidad} unidades (minimo requerido: {$min})";
                if (!empty($config['medicamento'])) {
                    $tieneMedicamentoFaltante = true;
                }
            }

            // Estado malo
            if (in_array($estado, ['ESTADO REGULAR', 'MAL ESTADO', 'SIN EXISTENCIAS'])) {
                $pendientes[] = "{$label} - Estado: {$estado}";
            }

            // Vencido
            if ($config['venc'] && !empty($vencimiento) && $vencimiento < $hoy) {
                $pendientes[] = "{$label} - VENCIDO (fecha: {$vencimiento})";
            }
        }

        // Equipos especiales del master
        if (!empty($inspeccion['estado_collares']) && in_array($inspeccion['estado_collares'], ['ESTADO REGULAR', 'MAL ESTADO'])) {
            $pendientes[] = "Collares cervicales - Estado: {$inspeccion['estado_collares']}";
        }
        if (!empty($inspeccion['estado_inmovilizadores']) && in_array($inspeccion['estado_inmovilizadores'], ['ESTADO REGULAR', 'MAL ESTADO'])) {
            $pendientes[] = "Inmovilizadores - Estado: {$inspeccion['estado_inmovilizadores']}";
        }

        // Disclaimer medicamentos
        if ($tieneMedicamentoFaltante) {
            $pendientes[] = "NOTA: Los medicamentos deben ser suministrados bajo prescripcion medica. Cycloid SAS no se hace responsable del uso indebido de los mismos.";
        }

        $textoFinal = !empty($pendientes) ? implode("\n", $pendientes) : 'Sin pendientes - Botiquin completo';

        $this->inspeccionModel->update($id, [
            'pendientes_generados' => $textoFinal,
        ]);
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $elementosRaw = $this->elementoModel->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos a base64
        $fotosBase64 = [];
        $camposFoto = ['foto_1', 'foto_2', 'foto_tabla_espinal', 'foto_collares', 'foto_inmovilizadores'];
        foreach ($camposFoto as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $cliente,
            'consultor'     => $consultor,
            'elementos'     => self::ELEMENTOS,
            'elementosData' => $elementosData,
            'logoBase64'    => $logoBase64,
            'fotosBase64'   => $fotosBase64,
        ];

        $html = view('inspecciones/botiquin/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/botiquin/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'botiquin_' . $id . '_' . date('Ymd_His') . '.pdf';
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

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 13)
            ->like('observaciones', 'insp_bot_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'botiquin_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION BOTIQUIN - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 13,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_bot_id:' . $inspeccion['id'],
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
     * Sincroniza la fecha de vencimiento más próxima de los antisépticos
     * con tbl_vencimientos_mantenimientos. Keyword: 'botiqu'.
     */
    private function syncVencimiento(array $inspeccion): void
    {
        // Obtener la fecha más próxima a vencer de los 3 antisépticos
        $elementos = $this->elementoModel->getByInspeccion($inspeccion['id']);
        $fechaMin = null;

        foreach ($elementos as $elem) {
            $config = self::ELEMENTOS[$elem['clave']] ?? null;
            if (!$config || !$config['venc']) continue;
            if (empty($elem['fecha_vencimiento'])) continue;

            if ($fechaMin === null || $elem['fecha_vencimiento'] < $fechaMin) {
                $fechaMin = $elem['fecha_vencimiento'];
            }
        }

        if (empty($fechaMin)) {
            return;
        }

        $mantenimientoModel = new MantenimientoModel();
        $vencimientoModel = new VencimientosMantenimientoModel();

        $mantenimiento = $mantenimientoModel
            ->like('detalle_mantenimiento', 'botiqu', 'both')
            ->first();

        if (!$mantenimiento) {
            log_message('warning', "syncVencimiento botiquin: No se encontro tipo de mantenimiento con keyword 'botiqu' en tbl_mantenimientos");
            return;
        }

        $idMantenimiento = $mantenimiento['id_mantenimiento'];

        $existente = $vencimientoModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_mantenimiento', $idMantenimiento)
            ->where('estado_actividad', 'sin ejecutar')
            ->first();

        if ($existente) {
            $vencimientoModel->update($existente['id_vencimientos_mmttos'], [
                'fecha_vencimiento' => $fechaMin,
                'id_consultor'      => $inspeccion['id_consultor'],
                'observaciones'     => 'Actualizado desde inspeccion botiquin #' . $inspeccion['id'],
            ]);
        } else {
            $vencimientoModel->insert([
                'id_mantenimiento'  => $idMantenimiento,
                'id_cliente'        => $inspeccion['id_cliente'],
                'id_consultor'      => $inspeccion['id_consultor'],
                'fecha_vencimiento' => $fechaMin,
                'estado_actividad'  => 'sin ejecutar',
                'observaciones'     => 'Auto-generado desde inspeccion botiquin #' . $inspeccion['id'],
            ]);
        }
    }
}
