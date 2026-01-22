<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use CodeIgniter\Controller;
use ZipArchive;

/**
 * Controlador para descargar documentación de un cliente
 * filtrada por las fechas del contrato o rango de fechas
 */
class DocumentacionContratoController extends Controller
{
    protected $contractModel;
    protected $clientModel;
    protected $reporteModel;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->clientModel = new ClientModel();
        $this->reporteModel = new ReporteModel();
    }

    /**
     * Muestra la vista de selección de contrato o rango de fechas
     */
    public function seleccionarDocumentacion($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        // Obtener todos los contratos del cliente ordenados por fecha
        $contracts = $this->contractModel
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_fin', 'DESC')
            ->findAll();

        return view('contracts/seleccionar_documentacion', [
            'client' => $client,
            'contracts' => $contracts
        ]);
    }

    /**
     * Filtra y muestra la documentación según la selección (contrato o fechas)
     */
    public function filtrarDocumentacion($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        $filtroTipo = $this->request->getGet('filtro_tipo');
        $fechaInicio = null;
        $fechaFin = null;
        $contract = null;
        $periodoLabel = '';

        if ($filtroTipo === 'contrato') {
            // Filtrar por contrato específico
            $idContrato = $this->request->getGet('id_contrato');
            $contract = $this->contractModel->find($idContrato);

            if (!$contract || $contract['id_cliente'] != $idCliente) {
                return redirect()->to('/contracts/seleccionar-documentacion/' . $idCliente)
                    ->with('error', 'Contrato no válido');
            }

            // Agregar datos del cliente al contrato
            $contract['nombre_cliente'] = $client['nombre_cliente'];
            $contract['nit_cliente'] = $client['nit_cliente'];
            $contract['id_consultor'] = $client['id_consultor'];

            $fechaInicio = $contract['fecha_inicio'];
            $fechaFin = $contract['fecha_fin'];
            $periodoLabel = 'Contrato ' . $contract['numero_contrato'];

        } elseif ($filtroTipo === 'fechas') {
            // Filtrar por rango de fechas personalizado
            $fechaInicio = $this->request->getGet('fecha_desde');
            $fechaFin = $this->request->getGet('fecha_hasta');

            if (!$fechaInicio || !$fechaFin) {
                return redirect()->to('/contracts/seleccionar-documentacion/' . $idCliente)
                    ->with('error', 'Debe seleccionar un rango de fechas válido');
            }

            // Verificar si es una anualidad completa
            $yearInicio = date('Y', strtotime($fechaInicio));
            $yearFin = date('Y', strtotime($fechaFin));
            if ($fechaInicio === $yearInicio . '-01-01' && $fechaFin === $yearFin . '-12-31' && $yearInicio === $yearFin) {
                $periodoLabel = 'Anualidad ' . $yearInicio;
            } else {
                $periodoLabel = date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin));
            }

            // Crear un "contrato virtual" para la vista
            $contract = [
                'id_contrato' => null,
                'id_cliente' => $idCliente,
                'numero_contrato' => $periodoLabel,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'nombre_cliente' => $client['nombre_cliente'],
                'nit_cliente' => $client['nit_cliente'],
                'id_consultor' => $client['id_consultor']
            ];
        } else {
            return redirect()->to('/contracts/seleccionar-documentacion/' . $idCliente)
                ->with('error', 'Seleccione un tipo de filtro');
        }

        // Obtener reportes del cliente dentro del rango de fechas
        $reportes = $this->reporteModel
            ->select('tbl_reporte.*, detail_report.detail_report, report_type_table.report_type')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->where('tbl_reporte.id_cliente', $idCliente)
            ->where('DATE(tbl_reporte.created_at) >=', $fechaInicio)
            ->where('DATE(tbl_reporte.created_at) <=', $fechaFin)
            ->orderBy('tbl_reporte.created_at', 'ASC')
            ->findAll();

        // Verificar cuáles archivos existen físicamente
        $archivosValidos = [];
        $tamanoTotal = 0;

        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $tamano = filesize($rutaArchivo);
                $tamanoTotal += $tamano;
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => $tamano,
                    'existe' => true
                ];
            } else {
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => 0,
                    'existe' => false
                ];
            }
        }

        return view('contracts/previsualizar_documentacion', [
            'contract' => $contract,
            'archivos' => $archivosValidos,
            'tamanoTotal' => $tamanoTotal,
            'totalReportes' => count($reportes),
            'archivosExistentes' => count(array_filter($archivosValidos, fn($a) => $a['existe'])),
            'fromReportList' => true,
            'filtroTipo' => $filtroTipo,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Descarga documentación filtrada (por contrato o fechas)
     */
    public function descargarFiltrado($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        $filtroTipo = $this->request->getGet('filtro_tipo');
        $fechaInicio = null;
        $fechaFin = null;
        $nombrePeriodo = '';

        if ($filtroTipo === 'contrato') {
            $idContrato = $this->request->getGet('id_contrato');
            $contract = $this->contractModel->find($idContrato);

            if (!$contract || $contract['id_cliente'] != $idCliente) {
                return redirect()->to('/reportList')->with('msg', 'Contrato no válido');
            }

            $fechaInicio = $contract['fecha_inicio'];
            $fechaFin = $contract['fecha_fin'];
            $nombrePeriodo = $contract['numero_contrato'];

        } elseif ($filtroTipo === 'fechas') {
            $fechaInicio = $this->request->getGet('fecha_desde');
            $fechaFin = $this->request->getGet('fecha_hasta');

            if (!$fechaInicio || !$fechaFin) {
                return redirect()->to('/reportList')->with('msg', 'Rango de fechas no válido');
            }

            $yearInicio = date('Y', strtotime($fechaInicio));
            $yearFin = date('Y', strtotime($fechaFin));
            if ($fechaInicio === $yearInicio . '-01-01' && $fechaFin === $yearFin . '-12-31' && $yearInicio === $yearFin) {
                $nombrePeriodo = $yearInicio;
            } else {
                $nombrePeriodo = date('Ymd', strtotime($fechaInicio)) . '_' . date('Ymd', strtotime($fechaFin));
            }
        } else {
            return redirect()->to('/reportList')->with('msg', 'Tipo de filtro no válido');
        }

        // Obtener reportes del cliente dentro del rango de fechas
        $reportes = $this->reporteModel
            ->where('id_cliente', $idCliente)
            ->where('DATE(created_at) >=', $fechaInicio)
            ->where('DATE(created_at) <=', $fechaFin)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        if (empty($reportes)) {
            return redirect()->back()->with('msg', 'No hay documentos en el período seleccionado');
        }

        // Recolectar archivos existentes
        $archivosParaZip = [];
        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                // Limpiar el título: remover caracteres que crean carpetas o son inválidos
                $tituloLimpio = $reporte['titulo_reporte'];
                $tituloLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $tituloLimpio);
                $tituloLimpio = preg_replace('/_{2,}/', '_', $tituloLimpio); // Múltiples _ a uno solo
                $tituloLimpio = trim($tituloLimpio, '_');

                $archivosParaZip[] = [
                    'ruta' => $rutaArchivo,
                    'nombre' => $tituloLimpio . '_' . date('Y-m-d', strtotime($reporte['created_at'])) . '.' . pathinfo($rutaArchivo, PATHINFO_EXTENSION)
                ];
            }
        }

        if (empty($archivosParaZip)) {
            return redirect()->back()->with('msg', 'No se encontraron archivos físicos para descargar');
        }

        // Crear ZIP
        $nombreCliente = preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']);
        $nombreZip = 'Documentacion_' . $nombreCliente . '_' . $nombrePeriodo . '_' . date('Y-m-d') . '.zip';
        $rutaZip = WRITEPATH . 'uploads/' . $nombreZip;

        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('msg', 'No se pudo crear el archivo ZIP');
        }

        $contador = [];
        foreach ($archivosParaZip as $archivo) {
            $nombreEnZip = $archivo['nombre'];
            if (isset($contador[$nombreEnZip])) {
                $contador[$nombreEnZip]++;
                $partes = pathinfo($nombreEnZip);
                $nombreEnZip = $partes['filename'] . '_' . $contador[$nombreEnZip] . '.' . $partes['extension'];
            } else {
                $contador[$nombreEnZip] = 1;
            }
            $zip->addFile($archivo['ruta'], $nombreEnZip);
        }

        $zip->close();

        if (!file_exists($rutaZip)) {
            return redirect()->back()->with('msg', 'Error al crear el archivo ZIP');
        }

        register_shutdown_function(function() use ($rutaZip) {
            if (file_exists($rutaZip)) {
                @unlink($rutaZip);
            }
        });

        return $this->response->download($rutaZip, null)->setFileName($nombreZip);
    }

    /**
     * Vista previa de los documentos del contrato
     */
    public function previsualizarDocumentacion($idContrato)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener contrato con datos del cliente
        $contract = $this->contractModel
            ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente, tbl_clientes.id_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
            ->find($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $contract['id_consultor'] != $idConsultor) {
            return redirect()->to('/contracts')->with('error', 'No tiene permisos');
        }

        // Obtener reportes del cliente dentro del rango de fechas del contrato
        $reportes = $this->reporteModel
            ->select('tbl_reporte.*, detail_report.detail_report, report_type_table.report_type')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->where('tbl_reporte.id_cliente', $contract['id_cliente'])
            ->where('DATE(tbl_reporte.created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(tbl_reporte.created_at) <=', $contract['fecha_fin'])
            ->orderBy('tbl_reporte.created_at', 'ASC')
            ->findAll();

        // Verificar cuáles archivos existen físicamente
        $archivosValidos = [];
        $tamanoTotal = 0;

        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            // Convertir URL a ruta de archivo
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $tamano = filesize($rutaArchivo);
                $tamanoTotal += $tamano;
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => $tamano,
                    'existe' => true
                ];
            } else {
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => 0,
                    'existe' => false
                ];
            }
        }

        return view('contracts/previsualizar_documentacion', [
            'contract' => $contract,
            'archivos' => $archivosValidos,
            'tamanoTotal' => $tamanoTotal,
            'totalReportes' => count($reportes),
            'archivosExistentes' => count(array_filter($archivosValidos, fn($a) => $a['existe']))
        ]);
    }

    /**
     * Vista previa de documentación por ID de cliente (busca el último contrato)
     */
    public function previsualizarPorCliente($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        // Obtener el último contrato del cliente
        $contract = $this->contractModel
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_fin', 'DESC')
            ->first();

        if (!$contract) {
            return redirect()->to('/reportList')->with('msg', 'El cliente no tiene contratos registrados');
        }

        // Agregar datos del cliente al contrato
        $contract['nombre_cliente'] = $client['nombre_cliente'];
        $contract['nit_cliente'] = $client['nit_cliente'];
        $contract['id_consultor'] = $client['id_consultor'];

        // Obtener reportes del cliente dentro del rango de fechas del contrato
        $reportes = $this->reporteModel
            ->select('tbl_reporte.*, detail_report.detail_report, report_type_table.report_type')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->where('tbl_reporte.id_cliente', $idCliente)
            ->where('DATE(tbl_reporte.created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(tbl_reporte.created_at) <=', $contract['fecha_fin'])
            ->orderBy('tbl_reporte.created_at', 'ASC')
            ->findAll();

        // Verificar cuáles archivos existen físicamente
        $archivosValidos = [];
        $tamanoTotal = 0;

        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $tamano = filesize($rutaArchivo);
                $tamanoTotal += $tamano;
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => $tamano,
                    'existe' => true
                ];
            } else {
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => 0,
                    'existe' => false
                ];
            }
        }

        return view('contracts/previsualizar_documentacion', [
            'contract' => $contract,
            'archivos' => $archivosValidos,
            'tamanoTotal' => $tamanoTotal,
            'totalReportes' => count($reportes),
            'archivosExistentes' => count(array_filter($archivosValidos, fn($a) => $a['existe'])),
            'fromReportList' => true
        ]);
    }

    /**
     * Descarga documentación por ID de cliente (busca el último contrato)
     */
    public function descargarPorCliente($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        // Obtener el último contrato del cliente
        $contract = $this->contractModel
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_fin', 'DESC')
            ->first();

        if (!$contract) {
            return redirect()->to('/reportList')->with('msg', 'El cliente no tiene contratos registrados');
        }

        // Obtener reportes del cliente dentro del rango de fechas
        $reportes = $this->reporteModel
            ->where('id_cliente', $idCliente)
            ->where('DATE(created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(created_at) <=', $contract['fecha_fin'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        if (empty($reportes)) {
            return redirect()->to('/reportList')->with('msg', 'No hay documentos en el período del contrato');
        }

        // Recolectar archivos existentes
        $archivosParaZip = [];
        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                // Limpiar el título: remover caracteres que crean carpetas o son inválidos
                $tituloLimpio = $reporte['titulo_reporte'];
                $tituloLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $tituloLimpio);
                $tituloLimpio = preg_replace('/_{2,}/', '_', $tituloLimpio); // Múltiples _ a uno solo
                $tituloLimpio = trim($tituloLimpio, '_');

                $archivosParaZip[] = [
                    'ruta' => $rutaArchivo,
                    'nombre' => $tituloLimpio . '_' . date('Y-m-d', strtotime($reporte['created_at'])) . '.' . pathinfo($rutaArchivo, PATHINFO_EXTENSION)
                ];
            }
        }

        if (empty($archivosParaZip)) {
            return redirect()->to('/reportList')->with('msg', 'No se encontraron archivos físicos para descargar');
        }

        // Crear ZIP
        $nombreCliente = preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']);
        $nombreZip = 'Documentacion_' . $nombreCliente . '_' . $contract['numero_contrato'] . '_' . date('Y-m-d') . '.zip';
        $rutaZip = WRITEPATH . 'uploads/' . $nombreZip;

        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->to('/reportList')->with('msg', 'No se pudo crear el archivo ZIP');
        }

        $contador = [];
        foreach ($archivosParaZip as $archivo) {
            $nombreEnZip = $archivo['nombre'];
            if (isset($contador[$nombreEnZip])) {
                $contador[$nombreEnZip]++;
                $partes = pathinfo($nombreEnZip);
                $nombreEnZip = $partes['filename'] . '_' . $contador[$nombreEnZip] . '.' . $partes['extension'];
            } else {
                $contador[$nombreEnZip] = 1;
            }
            $zip->addFile($archivo['ruta'], $nombreEnZip);
        }

        $zip->close();

        if (!file_exists($rutaZip)) {
            return redirect()->to('/reportList')->with('msg', 'Error al crear el archivo ZIP');
        }

        register_shutdown_function(function() use ($rutaZip) {
            if (file_exists($rutaZip)) {
                @unlink($rutaZip);
            }
        });

        return $this->response->download($rutaZip, null)->setFileName($nombreZip);
    }

    /**
     * Descarga todos los documentos del contrato en un ZIP
     */
    public function descargarDocumentacion($idContrato)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener contrato con datos del cliente
        $contract = $this->contractModel
            ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente, tbl_clientes.id_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
            ->find($idContrato);

        if (!$contract) {
            return redirect()->back()->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $contract['id_consultor'] != $idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos');
        }

        // Obtener reportes del cliente dentro del rango de fechas
        $reportes = $this->reporteModel
            ->where('id_cliente', $contract['id_cliente'])
            ->where('DATE(created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(created_at) <=', $contract['fecha_fin'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        if (empty($reportes)) {
            return redirect()->back()->with('warning', 'No hay documentos en el período del contrato');
        }

        // Recolectar archivos existentes
        $archivosParaZip = [];
        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                // Limpiar el título: remover caracteres que crean carpetas o son inválidos
                $tituloLimpio = $reporte['titulo_reporte'];
                $tituloLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $tituloLimpio);
                $tituloLimpio = preg_replace('/_{2,}/', '_', $tituloLimpio); // Múltiples _ a uno solo
                $tituloLimpio = trim($tituloLimpio, '_');

                $archivosParaZip[] = [
                    'ruta' => $rutaArchivo,
                    'nombre' => $tituloLimpio . '_' . date('Y-m-d', strtotime($reporte['created_at'])) . '.' . pathinfo($rutaArchivo, PATHINFO_EXTENSION)
                ];
            }
        }

        if (empty($archivosParaZip)) {
            return redirect()->back()->with('warning', 'No se encontraron archivos físicos para descargar');
        }

        // Crear ZIP
        $nombreCliente = preg_replace('/[^a-zA-Z0-9]/', '_', $contract['nombre_cliente']);
        $nombreZip = 'Documentacion_' . $nombreCliente . '_' . $contract['numero_contrato'] . '_' . date('Y-m-d') . '.zip';
        $rutaZip = WRITEPATH . 'uploads/' . $nombreZip;

        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP');
        }

        // Agregar archivos al ZIP con nombres descriptivos
        $contador = [];
        foreach ($archivosParaZip as $archivo) {
            $nombreEnZip = $archivo['nombre'];
            // Evitar nombres duplicados
            if (isset($contador[$nombreEnZip])) {
                $contador[$nombreEnZip]++;
                $partes = pathinfo($nombreEnZip);
                $nombreEnZip = $partes['filename'] . '_' . $contador[$nombreEnZip] . '.' . $partes['extension'];
            } else {
                $contador[$nombreEnZip] = 1;
            }
            $zip->addFile($archivo['ruta'], $nombreEnZip);
        }

        $zip->close();

        if (!file_exists($rutaZip)) {
            return redirect()->back()->with('error', 'Error al crear el archivo ZIP');
        }

        // Programar eliminación del ZIP temporal
        register_shutdown_function(function() use ($rutaZip) {
            if (file_exists($rutaZip)) {
                @unlink($rutaZip);
            }
        });

        return $this->response->download($rutaZip, null)->setFileName($nombreZip);
    }
}
