<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionSenalizacionModel;
use App\Models\ItemSenalizacionModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Traits\ImagenCompresionTrait;

class InspeccionSenalizacionController extends BaseController
{
    use ImagenCompresionTrait;
    protected InspeccionSenalizacionModel $inspeccionModel;
    protected ItemSenalizacionModel $itemModel;

    /**
     * 37 ítems fijos de señalización agrupados por categoría.
     */
    public const ITEMS_DEFINITION = [
        ['nombre' => 'Rutas de evacuacion', 'grupo' => 'Evacuacion y Emergencias', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Salidas de emergencia', 'grupo' => 'Evacuacion y Emergencias', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Punto de encuentro', 'grupo' => 'Evacuacion y Emergencias', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Planos de evacuacion', 'grupo' => 'Evacuacion y Emergencias', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Extintores senalizacion', 'grupo' => 'Equipos de Emergencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Gabinetes contra incendio', 'grupo' => 'Equipos de Emergencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Alarmas contra incendios', 'grupo' => 'Equipos de Emergencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Botiquin de primeros auxilios', 'grupo' => 'Equipos de Emergencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Camilla de emergencia', 'grupo' => 'Equipos de Emergencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Numeros de emergencia', 'grupo' => 'Equipos de Emergencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Libres de humo de tabaco', 'grupo' => 'Salud y Convivencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Prohibido fumar', 'grupo' => 'Salud y Convivencia', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Cerca perimetral', 'grupo' => 'Seguridad Perimetral', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Cebo desratizacion', 'grupo' => 'Seguridad Perimetral', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Manejo de mascotas', 'grupo' => 'Seguridad Perimetral', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Videovigilancia', 'grupo' => 'Seguridad Perimetral', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Limite de velocidad', 'grupo' => 'Senalizacion Vial', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Senales de transito internas', 'grupo' => 'Senalizacion Vial', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Parqueaderos discapacidad', 'grupo' => 'Senalizacion Vial', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Zona de carga vehiculos electricos', 'grupo' => 'Senalizacion Vial', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Identificacion oficina de administracion', 'grupo' => 'Identificacion de Areas', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Identificacion zonas sociales o comunales', 'grupo' => 'Identificacion de Areas', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Servicios sanitarios comunes', 'grupo' => 'Identificacion de Areas', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Cuarto de basuras y reciclaje', 'grupo' => 'Manejo de Residuos', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Residuos Ordinarios', 'grupo' => 'Manejo de Residuos', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Residuos Reciclables', 'grupo' => 'Manejo de Residuos', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Cuarto de bombas', 'grupo' => 'Cuartos Tecnicos', 'default' => 'NO APLICA'],
        ['nombre' => 'Cuarto electrico', 'grupo' => 'Cuartos Tecnicos', 'default' => 'NO APLICA'],
        ['nombre' => 'Planta electrica', 'grupo' => 'Cuartos Tecnicos', 'default' => 'NO APLICA'],
        ['nombre' => 'No ingresar Solo personal autorizado', 'grupo' => 'Cuartos Tecnicos', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Alto voltaje Peligro', 'grupo' => 'Cuartos Tecnicos', 'default' => 'NO CUMPLE'],
        ['nombre' => 'Capacidad maxima del ascensor', 'grupo' => 'Ascensores', 'default' => 'NO APLICA'],
        ['nombre' => 'Certificado revision ascensor', 'grupo' => 'Ascensores', 'default' => 'NO APLICA'],
        ['nombre' => 'No usar ascensor en caso de incendio', 'grupo' => 'Ascensores', 'default' => 'NO APLICA'],
        ['nombre' => 'Piscinas Profundidades visibles', 'grupo' => 'Piscinas', 'default' => 'NO APLICA'],
        ['nombre' => 'Piscinas Equipos de rescate', 'grupo' => 'Piscinas', 'default' => 'NO APLICA'],
        ['nombre' => 'Piscinas Aforo', 'grupo' => 'Piscinas', 'default' => 'NO APLICA'],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionSenalizacionModel();
        $this->itemModel = new ItemSenalizacionModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_inspeccion_senalizacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_senalizacion.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_senalizacion.id_consultor', 'left')
                ->orderBy('tbl_inspeccion_senalizacion.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Inspección de Señalización',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/senalizacion/list', $data),
            'title'   => 'Señalización',
        ]);
    }

    public function create($idCliente = null)
    {
        $itemsGrouped = [];
        foreach (self::ITEMS_DEFINITION as $i => $def) {
            $itemsGrouped[$def['grupo']][] = [
                'id'                  => '',
                'nombre_item'         => $def['nombre'],
                'grupo'               => $def['grupo'],
                'estado_cumplimiento' => $def['default'],
                'foto'                => '',
                'orden'               => $i + 1,
            ];
        }

        $data = [
            'title'        => 'Nueva Inspección de Señalización',
            'inspeccion'   => null,
            'idCliente'    => $idCliente,
            'itemsGrouped' => $itemsGrouped,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/senalizacion/form', $data),
            'title'   => 'Nueva Señalización',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $inspeccionData = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'id_consultor'     => $userId,
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'borrador',
        ];

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $this->saveItems($idInspeccion);
        $this->recalcularCalificacion($idInspeccion);

        return redirect()->to('/inspecciones/senalizacion/edit/' . $idInspeccion)
            ->with('msg', 'Inspección guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/senalizacion')->with('error', 'Inspección no encontrada');
        }

        // Doc 25: si estaba completo, revertir a borrador para re-edición
        if ($inspeccion['estado'] === 'completo') {
            $this->inspeccionModel->update($id, ['estado' => 'borrador']);
            $inspeccion['estado'] = 'borrador';
        }

        $data = [
            'title'        => 'Editar Inspección de Señalización',
            'inspeccion'   => $inspeccion,
            'idCliente'    => $inspeccion['id_cliente'],
            'itemsGrouped' => $this->itemModel->getByInspeccionGrouped($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/senalizacion/form', $data),
            'title'   => 'Editar Señalización',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/senalizacion')->with('error', 'No se puede editar');
        }

        // Doc 25: si estaba completo, revertir a borrador
        if ($inspeccion['estado'] === 'completo') {
            $this->inspeccionModel->update($id, ['estado' => 'borrador']);
        }

        $this->inspeccionModel->update($id, [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ]);

        $this->saveItems($id);
        $this->recalcularCalificacion($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/senalizacion/edit/' . $id)
            ->with('msg', 'Inspección actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/senalizacion')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'        => 'Ver Inspección de Señalización',
            'inspeccion'   => $inspeccion,
            'cliente'      => $clientModel->find($inspeccion['id_cliente']),
            'consultor'    => $consultantModel->find($inspeccion['id_consultor']),
            'itemsGrouped' => $this->itemModel->getByInspeccionGrouped($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/senalizacion/view', $data),
            'title'   => 'Ver Señalización',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/senalizacion')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/senalizacion/view/' . $id)
            ->with('msg', 'Inspección finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/senalizacion')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="senalizacion_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/senalizacion')->with('error', 'No encontrada');
        }

        $items = $this->itemModel->getByInspeccion($id);
        foreach ($items as $item) {
            if (!empty($item['foto']) && file_exists(FCPATH . $item['foto'])) {
                unlink(FCPATH . $item['foto']);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/senalizacion')->with('msg', 'Inspección eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

    private function saveItems(int $idInspeccion): void
    {
        $itemIds = $this->request->getPost('item_id') ?? [];
        $itemNombres = $this->request->getPost('item_nombre') ?? [];
        $itemGrupos = $this->request->getPost('item_grupo') ?? [];
        $itemEstados = $this->request->getPost('item_estado') ?? [];

        $existentes = [];
        foreach ($this->itemModel->getByInspeccion($idInspeccion) as $item) {
            $existentes[$item['id']] = $item;
        }

        $this->itemModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/senalizacion/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();

        foreach ($itemNombres as $i => $nombre) {
            if (empty(trim($nombre))) continue;

            $existenteId = $itemIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;

            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['item_foto'][$i]) && $files['item_foto'][$i]->isValid() && !$files['item_foto'][$i]->hasMoved()) {
                $file = $files['item_foto'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $fotoPath = 'uploads/inspecciones/senalizacion/fotos/' . $fileName;
            }

            $this->itemModel->insert([
                'id_inspeccion'      => $idInspeccion,
                'nombre_item'        => trim($nombre),
                'grupo'              => $itemGrupos[$i] ?? '',
                'estado_cumplimiento' => $itemEstados[$i] ?? 'NO CUMPLE',
                'foto'               => $fotoPath,
                'orden'              => $i + 1,
            ]);
        }
    }

    private function recalcularCalificacion(int $idInspeccion): void
    {
        $items = $this->itemModel->getByInspeccion($idInspeccion);

        $noAplica = 0;
        $noCumple = 0;
        $parcial = 0;
        $total = 0;

        foreach ($items as $item) {
            switch ($item['estado_cumplimiento']) {
                case 'NO APLICA':             $noAplica++; break;
                case 'NO CUMPLE':             $noCumple++; break;
                case 'CUMPLE PARCIALMENTE':   $parcial++;  break;
                case 'CUMPLE TOTALMENTE':     $total++;    break;
            }
        }

        $denominador = $noCumple + $parcial + $total;
        $calificacion = $denominador > 0
            ? round(100 * (0.5 * $parcial + $total) / $denominador, 2)
            : 0;

        $descripcion = $this->getDescripcionCualitativa($calificacion);

        $this->inspeccionModel->update($idInspeccion, [
            'calificacion'            => $calificacion,
            'descripcion_cualitativa' => $descripcion,
            'conteo_no_aplica'        => $noAplica,
            'conteo_no_cumple'        => $noCumple,
            'conteo_parcial'          => $parcial,
            'conteo_total'            => $total,
        ]);
    }

    private function getDescripcionCualitativa(float $calificacion): string
    {
        if ($calificacion <= 40) return 'Nivel critico: Cumplimiento muy bajo, requiere acciones inmediatas.';
        if ($calificacion <= 60) return 'Nivel bajo: Riesgo alto, se recomienda plan de mejora urgente.';
        if ($calificacion <= 80) return 'Nivel medio: Cumplimiento aceptable pero con oportunidades claras de mejora.';
        if ($calificacion <= 90) return 'Nivel bueno: Cumplimiento alto, se sugiere mantener y reforzar.';
        return 'Nivel excelente: Cumplimiento optimo, refleja madurez y compromiso.';
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $items = $this->itemModel->getByInspeccion($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        foreach ($items as &$item) {
            $item['foto_base64'] = '';
            if (!empty($item['foto'])) {
                $fotoPath = FCPATH . $item['foto'];
                if (file_exists($fotoPath)) {
                    $item['foto_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $itemsGrouped = [];
        foreach ($items as $item) {
            $itemsGrouped[$item['grupo']][] = $item;
        }

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'items'        => $items,
            'itemsGrouped' => $itemsGrouped,
            'logoBase64'   => $logoBase64,
        ];

        $html = view('inspecciones/senalizacion/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/senalizacion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'senalizacion_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 11)
            ->like('observaciones', 'insp_senal_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'senalizacion_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION SENALIZACION - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 11,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_senal_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
