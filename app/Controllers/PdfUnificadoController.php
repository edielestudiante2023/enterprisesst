<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
use App\Models\ClientPoliciesModel;
use App\Models\DocumentVersionModel;
use App\Models\PolicyTypeModel;
use App\Libraries\AccessLibrary;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfUnificadoController extends Controller
{
    /**
     * Mapeo: id_acceso => policy_type_id
     * Cada id_acceso corresponde a un policy_type_id en la BD y una vista de documento.
     */
    private array $documentMapping = [
        1  => ['policy_type_id' => 1,  'view' => 'client/sgsst/1planear/p1_1_1asignacion_responsable'],
        2  => ['policy_type_id' => 2,  'view' => 'client/sgsst/1planear/p1_1_2asignacion_responsabilidades'],
        3  => ['policy_type_id' => 3,  'view' => 'client/sgsst/1planear/p1_1_3vigia'],
        4  => ['policy_type_id' => 4,  'view' => 'client/sgsst/1planear/p1_1_4exoneracion_cocolab'],
        5  => ['policy_type_id' => 5,  'view' => 'client/sgsst/1planear/p1_1_5registro_asistencia'],
        15 => ['policy_type_id' => 15, 'view' => 'client/sgsst/1planear/p1_2_1prgcapacitacion'],
        16 => ['policy_type_id' => 16, 'view' => 'client/sgsst/1planear/p1_2_2prginduccion'],
        17 => ['policy_type_id' => 17, 'view' => 'client/sgsst/1planear/p1_2_3ftevaluacioninduccion'],
        18 => ['policy_type_id' => 18, 'view' => 'client/sgsst/1planear/p2_1_1politicasst'],
        19 => ['policy_type_id' => 19, 'view' => 'client/sgsst/1planear/p2_1_2politicaalcohol'],
        20 => ['policy_type_id' => 20, 'view' => 'client/sgsst/1planear/p2_1_3politicaemergencias'],
        21 => ['policy_type_id' => 21, 'view' => 'client/sgsst/1planear/p2_1_4politicaepps'],
        23 => ['policy_type_id' => 23, 'view' => 'client/sgsst/1planear/p2_1_6reghigsegind'],
        24 => ['policy_type_id' => 24, 'view' => 'client/sgsst/1planear/p2_2_1objetivos'],
        25 => ['policy_type_id' => 25, 'view' => 'client/sgsst/1planear/p2_5_1documentacion'],
        26 => ['policy_type_id' => 26, 'view' => 'client/sgsst/1planear/p2_5_2rendiciondecuentas'],
        28 => ['policy_type_id' => 28, 'view' => 'client/sgsst/1planear/p2_5_4manproveedores'],
        31 => ['policy_type_id' => 31, 'view' => 'client/sgsst/1planear/h1_1_3repoaccidente'],
        36 => ['policy_type_id' => 36, 'view' => 'client/sgsst/1planear/h1_1_7identfpeligriesg'],
    ];

    /**
     * Vista frontend: muestra lista de documentos y botón para generar PDF unificado.
     */
    public function index($idClienteParam = null)
    {
        $session = session();

        // Determinar id_cliente: parámetro URL (consultant/admin) o sesión (cliente)
        $clientId = $idClienteParam ?? $session->get('id_cliente');

        if (!$clientId) {
            return redirect()->to('/login');
        }

        $clientModel     = new ClientModel();
        $consultantModel = new ConsultantModel();
        $contractModel   = new ContractModel();

        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/dashboard')->with('error', 'Cliente no encontrado.');
        }

        $consultant = $consultantModel->find($client['id_consultor']);
        $firstContractDate = $contractModel->getFirstContractDate($clientId);

        // Obtener accesos según estándar del cliente
        $standard  = $client['estandares'] ?? 'Mensual';
        $accessIds = AccessLibrary::getAccessesByStandard($standard);

        // Filtrar solo los que están en documentMapping
        $availableDocs = [];
        foreach ($accessIds as $idAcceso) {
            if (isset($this->documentMapping[$idAcceso])) {
                $access = AccessLibrary::getAccess($idAcceso);
                if ($access) {
                    $availableDocs[$idAcceso] = $access;
                }
            }
        }

        // Agrupar por dimensión PHVA
        $order   = ['Planear', 'Hacer', 'Verificar', 'Actuar'];
        $grouped = [];
        foreach ($order as $dim) {
            $grouped[$dim] = [];
        }
        foreach ($availableDocs as $id => $doc) {
            $grouped[$doc['dimension']][$id] = $doc;
        }
        $grouped = array_filter($grouped);

        $data = [
            'client'            => $client,
            'consultant'        => $consultant,
            'firstContractDate' => $firstContractDate,
            'groupedDocs'       => $grouped,
            'totalDocs'         => count($availableDocs),
        ];

        return view('client/pdf_unificado', $data);
    }

    /**
     * Genera y descarga el PDF unificado (fusión de todos los documentos).
     */
    public function generarPdfUnificado()
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $session  = session();
        $clientId = $this->request->getPost('id_cliente') ?? $session->get('id_cliente');

        if (!$clientId) {
            return $this->response->setStatusCode(400)->setBody('Cliente no especificado.');
        }

        $clientModel     = new ClientModel();
        $consultantModel = new ConsultantModel();
        $contractModel   = new ContractModel();

        $client = $clientModel->find($clientId);
        if (!$client) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado.');
        }

        $consultant        = $consultantModel->find($client['id_consultor']);
        $firstContractDate = $contractModel->getFirstContractDate($clientId);

        // Obtener accesos según estándar
        $standard  = $client['estandares'] ?? 'Mensual';
        $accessIds = AccessLibrary::getAccessesByStandard($standard);

        // Directorio temporal
        $tempDir = WRITEPATH . 'uploads/temp_pdfs/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $pdfFiles = [];

        try {
            foreach ($accessIds as $idAcceso) {
                if (!isset($this->documentMapping[$idAcceso])) {
                    continue;
                }

                $pdfContent = $this->generarPdfDirecto($idAcceso, $clientId, $client, $consultant, $firstContractDate);

                if ($pdfContent) {
                    $filename = 'doc_' . $idAcceso . '_' . uniqid() . '.pdf';
                    $filepath = $tempDir . $filename;
                    file_put_contents($filepath, $pdfContent);
                    $pdfFiles[] = [
                        'path'      => $filepath,
                        'id_acceso' => $idAcceso,
                    ];
                }
            }

            if (empty($pdfFiles)) {
                return $this->response->setStatusCode(500)->setBody('No se generó ningún PDF.');
            }

            // Fusionar todos los PDFs
            $mergedPdf = $this->fusionarPdfs($pdfFiles);

            // Limpiar temporales
            $this->limpiarDirectorioTemp($tempDir);

            // Descargar
            $nombreArchivo = 'SG-SST_' . preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']) . '_' . date('Y-m-d') . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->setBody($mergedPdf);

        } catch (\Exception $e) {
            // Limpiar en caso de error
            $this->limpiarDirectorioTemp($tempDir);
            log_message('error', 'PdfUnificado error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Error generando PDF: ' . $e->getMessage());
        }
    }

    /**
     * Genera un PDF individual con DOMPDF para un documento específico.
     */
    private function generarPdfDirecto(int $idAcceso, int $clientId, array $client, ?array $consultant, ?string $firstContractDate): ?string
    {
        $mapping       = $this->documentMapping[$idAcceso];
        $policyTypeId  = $mapping['policy_type_id'];
        $viewName      = $mapping['view'];

        $policiesModel = new ClientPoliciesModel();
        $versionModel  = new DocumentVersionModel();
        $policyTypeModel = new PolicyTypeModel();

        // Obtener datos del documento
        $clientPolicy = $policiesModel->where('client_id', $clientId)
                                      ->where('policy_type_id', $policyTypeId)
                                      ->first();

        $latestVersion = $versionModel->where('client_id', $clientId)
                                      ->where('policy_type_id', $policyTypeId)
                                      ->orderBy('version_number', 'DESC')
                                      ->first();

        $policyType = $policyTypeModel->find($policyTypeId);

        if (!$clientPolicy || !$latestVersion || !$policyType) {
            return null;
        }

        // Si hay fecha de primer contrato, usarla como created_at
        if ($firstContractDate) {
            $latestVersion['created_at'] = $firstContractDate;
        }

        $allVersions = $versionModel->where('client_id', $clientId)
                                    ->where('policy_type_id', $policyTypeId)
                                    ->orderBy('version_number', 'ASC')
                                    ->findAll();

        // Ajustar fechas de todas las versiones
        if ($firstContractDate) {
            foreach ($allVersions as &$v) {
                $v['created_at'] = $firstContractDate;
            }
        }

        // Renderizar vista HTML
        $data = [
            'client'        => $client,
            'consultant'    => $consultant,
            'clientPolicy'  => $clientPolicy,
            'policyType'    => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions'   => $allVersions,
        ];

        $html = view($viewName, $data);

        // Convertir a PDF con DOMPDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Fusiona múltiples PDFs en uno solo usando FPDI/TCPDF.
     */
    private function fusionarPdfs(array $pdfFiles): string
    {
        $pdf = new Fpdi();
        $pdf->setAutoPageBreak(false);

        foreach ($pdfFiles as $pdfFile) {
            $pageCount = $pdf->setSourceFile($pdfFile['path']);
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Limpia archivos PDF del directorio temporal.
     */
    private function limpiarDirectorioTemp(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '*.pdf');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
