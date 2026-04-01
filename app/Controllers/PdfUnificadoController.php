<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
use App\Models\ClientPoliciesModel;
use App\Models\DocumentVersionModel;
use App\Models\PolicyTypeModel;
use App\Models\VigiaModel;
use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfUnificadoController extends Controller
{
    private $clientModel;
    private $consultantModel;
    private $contractModel;
    private $clientPoliciesModel;
    private $versionModel;
    private $policyTypeModel;
    private $vigiaModel;

    private array $documentMapping = [
        1  => ['policy_type_id' => 1,  'view' => 'client/sgsst/1planear/p1_1_1asignacion_responsable',      'nombre' => 'Asignación de Responsable'],
        2  => ['policy_type_id' => 2,  'view' => 'client/sgsst/1planear/p1_1_2asignacion_responsabilidades', 'nombre' => 'Asignación de Responsabilidades'],
        3  => ['policy_type_id' => 3,  'view' => 'client/sgsst/1planear/p1_1_3vigia',                        'nombre' => 'Asignación de Vigía'],
        4  => ['policy_type_id' => 4,  'view' => 'client/sgsst/1planear/p1_1_4exoneracion_cocolab',          'nombre' => 'Exoneración COCOLAB'],
        5  => ['policy_type_id' => 5,  'view' => 'client/sgsst/1planear/p1_1_5registro_asistencia',          'nombre' => 'Registro de Asistencia'],
        15 => ['policy_type_id' => 15, 'view' => 'client/sgsst/1planear/p1_2_1prgcapacitacion',              'nombre' => 'Programa de Capacitación'],
        16 => ['policy_type_id' => 16, 'view' => 'client/sgsst/1planear/p1_2_2prginduccion',                 'nombre' => 'Programa de Inducción'],
        17 => ['policy_type_id' => 17, 'view' => 'client/sgsst/1planear/p1_2_3ftevaluacioninduccion',        'nombre' => 'Evaluación de Inducción'],
        18 => ['policy_type_id' => 18, 'view' => 'client/sgsst/1planear/p2_1_1politicasst',                  'nombre' => 'Política de SST'],
        19 => ['policy_type_id' => 19, 'view' => 'client/sgsst/1planear/p2_1_2politicaalcohol',              'nombre' => 'Política de Alcohol'],
        20 => ['policy_type_id' => 20, 'view' => 'client/sgsst/1planear/p2_1_3politicaemergencias',          'nombre' => 'Política de Emergencias'],
        23 => ['policy_type_id' => 23, 'view' => 'client/sgsst/1planear/p2_1_6reghigsegind',                 'nombre' => 'Reglamento de Higiene'],
        24 => ['policy_type_id' => 24, 'view' => 'client/sgsst/1planear/p2_2_1objetivos',                    'nombre' => 'Objetivos del SG-SST'],
        25 => ['policy_type_id' => 25, 'view' => 'client/sgsst/1planear/p2_5_1documentacion',                'nombre' => 'Documentos del SG-SST'],
        26 => ['policy_type_id' => 26, 'view' => 'client/sgsst/1planear/p2_5_2rendiciondecuentas',           'nombre' => 'Rendición de Cuentas'],
        28 => ['policy_type_id' => 28, 'view' => 'client/sgsst/1planear/p2_5_4manproveedores',               'nombre' => 'Manual de Proveedores'],
        31 => ['policy_type_id' => 31, 'view' => 'client/sgsst/1planear/h1_1_3repoaccidente',                'nombre' => 'Reporte de Accidente'],
        36 => ['policy_type_id' => 36, 'view' => 'client/sgsst/1planear/h1_1_7identfpeligriesg',             'nombre' => 'Identificación de Peligros'],
    ];

    public function __construct()
    {
        $this->clientModel         = new ClientModel();
        $this->consultantModel     = new ConsultantModel();
        $this->contractModel       = new ContractModel();
        $this->clientPoliciesModel = new ClientPoliciesModel();
        $this->versionModel        = new DocumentVersionModel();
        $this->policyTypeModel     = new PolicyTypeModel();
        $this->vigiaModel          = new VigiaModel();
    }

    public function index($idClienteParam = null)
    {
        helper('access_library');

        $clientId = getEffectiveClientId($idClienteParam);

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        $accesos = get_accesses_by_standard($client['estandares'] ?? 'Mensual');

        $orden = ['Planear', 'Hacer', 'Verificar', 'Actuar'];
        usort($accesos, function ($a, $b) use ($orden) {
            return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
        });

        $accesosConPdf = array_filter($accesos, function ($acceso) {
            return isset($this->documentMapping[$acceso['id_acceso']]) && $acceso['dimension'] !== 'Indicadores';
        });

        return view('client/pdf_unificado', [
            'client'          => $client,
            'accesos'         => $accesosConPdf,
            'totalDocumentos' => count($accesosConPdf),
        ]);
    }

    public function generarPdfUnificado()
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        helper('access_library');

        $idClientePost = $this->request->getPost('id_cliente');
        $clientId = getEffectiveClientId($idClientePost);

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        $consultant        = $this->consultantModel->find($client['id_consultor'] ?? 0);
        $firstContractDate = $this->resolverFechaContrato($clientId, $client);

        $accesos = get_accesses_by_standard($client['estandares'] ?? 'Mensual');

        $orden = ['Planear', 'Hacer', 'Verificar', 'Actuar'];
        usort($accesos, function ($a, $b) use ($orden) {
            return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
        });

        $accesosConPdf = array_filter($accesos, function ($acceso) {
            return isset($this->documentMapping[$acceso['id_acceso']]) && $acceso['dimension'] !== 'Indicadores';
        });

        // Filtrar por documentos seleccionados
        $seleccionados = $this->request->getPost('documentos');
        if (!empty($seleccionados)) {
            $seleccionados = array_map('intval', $seleccionados);
            $accesosConPdf = array_filter($accesosConPdf, function ($acceso) use ($seleccionados) {
                return in_array((int) $acceso['id_acceso'], $seleccionados);
            });
        }

        $tempDir = WRITEPATH . 'uploads/temp_pdfs/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $pdfFiles = [];
        $errores  = [];

        foreach ($accesosConPdf as $acceso) {
            $idAcceso = $acceso['id_acceso'];
            if (!isset($this->documentMapping[$idAcceso])) {
                continue;
            }

            $mapping = $this->documentMapping[$idAcceso];
            $pdfPath = $tempDir . 'doc_' . $idAcceso . '_' . uniqid() . '.pdf';

            try {
                $pdfContent = $this->generarPdfDirecto($idAcceso, $clientId, $client, $consultant, $firstContractDate);

                if ($pdfContent) {
                    file_put_contents($pdfPath, $pdfContent);
                    $pdfFiles[] = [
                        'path'      => $pdfPath,
                        'nombre'    => $mapping['nombre'],
                        'id_acceso' => $idAcceso,
                    ];
                }
            } catch (\Exception $e) {
                $errores[] = $mapping['nombre'] . ': ' . $e->getMessage();
                log_message('error', 'PdfUnificado: doc ' . $idAcceso . ' error: ' . $e->getMessage());
            }
        }

        if (empty($pdfFiles)) {
            $this->limpiarDirectorioTemp($tempDir);
            $errorMsg = 'No se pudo generar ningún documento PDF.';
            if (!empty($errores)) {
                $errorMsg .= ' Errores: ' . implode(', ', array_slice($errores, 0, 3));
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        try {
            $pdfFinal = $this->fusionarPdfs($pdfFiles);
            $this->limpiarDirectorioTemp($tempDir);

            $nombreArchivo = 'SG-SST_' . preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']) . '_' . date('Y-m-d') . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->setBody($pdfFinal);

        } catch (\Exception $e) {
            $this->limpiarDirectorioTemp($tempDir);
            log_message('error', 'Error fusionando PDFs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al fusionar los documentos: ' . $e->getMessage());
        }
    }

    private function resolverFechaContrato($clientId, $client): ?string
    {
        $fecha = $this->contractModel->getFirstContractDate($clientId);

        if ($fecha && ($fecha === '0000-00-00' || strtotime($fecha) <= 86400)) {
            $fecha = null;
        }

        if (!$fecha && !empty($client['fecha_ingreso'])) {
            $ts = strtotime($client['fecha_ingreso']);
            $fecha = ($ts > 86400) ? $client['fecha_ingreso'] : null;
        }

        return $fecha;
    }

    private function generarPdfDirecto($idAcceso, $clientId, $client, $consultant, $firstContractDate)
    {
        if (!isset($this->documentMapping[$idAcceso])) {
            return null;
        }

        $mapping      = $this->documentMapping[$idAcceso];
        $policyTypeId = $mapping['policy_type_id'];
        $viewPath     = $mapping['view'];

        $clientPolicy = $this->clientPoliciesModel
            ->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$clientPolicy) {
            return null;
        }

        $policyType = $this->policyTypeModel->find($policyTypeId);

        $latestVersion = $this->versionModel
            ->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$latestVersion) {
            return null;
        }

        $allVersions = $this->versionModel
            ->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Sobreescribir fechas con la del primer contrato
        if ($firstContractDate) {
            $latestVersion['created_at'] = $firstContractDate;
            foreach ($allVersions as &$version) {
                $version['created_at'] = $firstContractDate;
            }
            unset($version);
        } else {
            $latestVersion['created_at']   = null;
            $latestVersion['sin_contrato'] = true;
            foreach ($allVersions as &$version) {
                $version['created_at']   = null;
                $version['sin_contrato'] = true;
            }
            unset($version);
        }

        // Formatear fecha
        if ($latestVersion['created_at']) {
            try {
                $latestVersion['created_at'] = Time::parse($latestVersion['created_at'], 'America/Bogota')
                    ->toLocalizedString('d MMMM yyyy');
            } catch (\Exception $e) {
                $latestVersion['created_at']   = null;
                $latestVersion['sin_contrato'] = true;
            }
        }

        if (empty($latestVersion['created_at']) && !empty($latestVersion['sin_contrato'])) {
            $latestVersion['created_at'] = 'PENDIENTE DE CONTRATO';
        }

        // Datos especiales para Vigía
        $latestVigia = null;
        if ($idAcceso === 3) {
            $latestVigia = $this->vigiaModel
                ->where('id_cliente', $clientId)
                ->orderBy('created_at', 'ASC')
                ->first();
        }

        $data = [
            'client'        => $client,
            'consultant'    => $consultant,
            'clientPolicy'  => $clientPolicy,
            'policyType'    => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions'   => $allVersions,
            'latestVigia'   => $latestVigia,
        ];

        if (!file_exists(APPPATH . 'Views/' . $viewPath . '.php')) {
            log_message('warning', 'Vista no encontrada: ' . $viewPath);
            return null;
        }

        $html = view($viewPath, $data);

        // Inyectar header corporativo y normalizar fuentes
        $nuevoHeader = $this->buildPdfHeader($client, $policyType, $latestVersion);
        $html = $this->inyectarHeader($html, $nuevoHeader);
        $html = $this->normalizarFuentes($html);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function inyectarHeader(string $html, string $nuevoHeader): string
    {
        // Patrón A: <div class="centered-content">...</div>
        if (preg_match('/<div\s+class="centered-content">/i', $html)) {
            return preg_replace(
                '/<div\s+class="centered-content">.*?<\/div>/si',
                $nuevoHeader,
                $html,
                1
            );
        }

        // Patrón B: <table> inmediatamente después de <body>
        return preg_replace(
            '/(<body[^>]*>)\s*<table\b[^>]*>.*?<\/table>/si',
            '$1' . "\n" . $nuevoHeader,
            $html,
            1
        );
    }

    private function normalizarFuentes(string $html): string
    {
        $css = '<style>
body, p, li, td, th, span, div, blockquote, pre,
h1, h2, h3, h4, h5, h6,
.alfa-title, .beta-subtitle, .beta-parrafo,
.gamma-lista, .delta-lista, .zeta-table,
.container, .centered-content {
    font-size: 11px !important;
    font-family: Arial, sans-serif !important;
    line-height: 1.5 !important;
}
h1, h2, h3, h4, h5, h6 {
    font-weight: bold !important;
    margin-top: 12px !important;
    margin-bottom: 6px !important;
}
.signature img, .signature-container img {
    max-width: 180px !important;
    max-height: 110px !important;
    width: auto !important;
    height: auto !important;
}
footer {
    page-break-before: always !important;
}
</style>';
        return str_replace('</head>', $css . '</head>', $html);
    }

    private function buildPdfHeader(array $client, ?array $policyType, array $latestVersion): string
    {
        $logoSrc  = '';
        $logoPath = FCPATH . 'uploads/' . ($client['logo'] ?? '');
        if (!empty($client['logo']) && file_exists($logoPath)) {
            $mime    = mime_content_type($logoPath);
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $logoHtml = $logoSrc
            ? '<img src="' . $logoSrc . '" alt="Logo" style="max-width:85px; max-height:65px; width:auto; height:auto;">'
            : '';

        $docCode  = trim(($latestVersion['document_type'] ?? '') . '-' . ($latestVersion['acronym'] ?? ''), '-');
        $docTitle = strtoupper($policyType['type_name'] ?? '');
        $version  = $latestVersion['version_number'] ?? '1';
        $fecha    = $latestVersion['created_at'] ?? '';
        $esPendiente = str_contains($fecha, 'PENDIENTE');

        $fechaHtml = $esPendiente
            ? '<span style="color:red; font-weight:bold;">PENDIENTE DE CONTRATO</span>'
            : esc($fecha);

        $td = 'border:1px solid black; padding:8px; font-size:11px; font-family:Arial, sans-serif;';

        return '
<div style="margin-bottom:16px;">
  <table style="width:100%; border-collapse:collapse; border:1px solid black;">
    <tr>
      <td rowspan="2" style="' . $td . ' width:15%; text-align:center; vertical-align:middle;">
        ' . $logoHtml . '
      </td>
      <td style="' . $td . ' width:55%; text-align:center; font-weight:bold; vertical-align:middle;">
        SISTEMA DE GESTI&Oacute;N EN SEGURIDAD Y SALUD EN EL TRABAJO
      </td>
      <td style="' . $td . ' width:30%; font-weight:bold; vertical-align:middle;">
        ' . esc($docCode) . '
      </td>
    </tr>
    <tr>
      <td style="' . $td . ' text-align:center; font-weight:bold; vertical-align:middle;">
        ' . $docTitle . '
      </td>
      <td style="' . $td . ' vertical-align:middle; line-height:1.8;">
        Versi&oacute;n: ' . esc($version) . '<br>
        Fecha: ' . $fechaHtml . '
      </td>
    </tr>
  </table>
</div>';
    }

    private function fusionarPdfs(array $pdfFiles): string
    {
        $pdf = new Fpdi();
        $pdf->setAutoPageBreak(false);

        foreach ($pdfFiles as $pdfFile) {
            try {
                $pageCount = $pdf->setSourceFile($pdfFile['path']);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $pdf->importPage($i);
                    $size       = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error importando PDF ' . ($pdfFile['nombre'] ?? '') . ': ' . $e->getMessage());
                continue;
            }
        }

        return $pdf->Output('', 'S');
    }

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
