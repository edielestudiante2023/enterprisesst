<?php

namespace App\Controllers;

use App\Models\DocDocumentoModel;
use App\Models\DocSeccionModel;
use App\Models\DocFirmaModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ExportacionDocumentoController extends Controller
{
    protected $documentoModel;
    protected $seccionModel;
    protected $firmaModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->documentoModel = new DocDocumentoModel();
        $this->seccionModel = new DocSeccionModel();
        $this->firmaModel = new DocFirmaModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Exportar documento como PDF
     */
    public function pdf($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $secciones = $this->seccionModel->getByDocumento($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $firmas = $this->firmaModel->getEstadoFirmas($idDocumento);

        // Determinar si es borrador o documento oficial
        $esBorrador = $documento['estado'] !== 'aprobado';

        // Generar HTML para el PDF
        $html = view('documentacion/export/pdf_template', [
            'documento' => $documento,
            'secciones' => $secciones,
            'cliente' => $cliente,
            'firmas' => $firmas,
            'esBorrador' => $esBorrador
        ], ['saveData' => false]);

        // TODO: Implementar generación de PDF con DOMPDF o similar
        // Por ahora retornamos el HTML
        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html);
    }

    /**
     * Exportar documento como PDF borrador (con marca de agua)
     */
    public function pdfBorrador($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $secciones = $this->seccionModel->getByDocumento($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        $html = view('documentacion/export/pdf_template', [
            'documento' => $documento,
            'secciones' => $secciones,
            'cliente' => $cliente,
            'firmas' => null,
            'esBorrador' => true
        ], ['saveData' => false]);

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html);
    }

    /**
     * Exportar documento como Word (.docx)
     */
    public function word($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $secciones = $this->seccionModel->getByDocumento($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        // Generar contenido para Word
        $contenido = $this->generarContenidoWord($documento, $secciones, $cliente);

        // Registrar exportación
        $this->registrarExportacion($idDocumento, 'word');

        // TODO: Implementar generación de .docx con PHPWord
        // Por ahora retornamos como texto plano descargable
        $nombreArchivo = "{$documento['codigo']}_v{$documento['version_actual']}.txt";

        return $this->response
            ->setHeader('Content-Type', 'text/plain')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$nombreArchivo}\"")
            ->setBody($contenido);
    }

    /**
     * Genera contenido formateado para Word
     */
    private function generarContenidoWord(array $documento, array $secciones, array $cliente): string
    {
        $contenido = "";

        // Encabezado
        $contenido .= strtoupper($documento['nombre']) . "\n";
        $contenido .= str_repeat("=", strlen($documento['nombre'])) . "\n\n";
        $contenido .= "Código: {$documento['codigo']}\n";
        $contenido .= "Versión: {$documento['version_actual']}\n";
        $contenido .= "Empresa: {$cliente['nombre_cliente']}\n";
        $contenido .= "Fecha: " . date('Y-m-d') . "\n";
        $contenido .= "\n" . str_repeat("-", 60) . "\n\n";

        // Secciones
        foreach ($secciones as $seccion) {
            $contenido .= "{$seccion['numero_seccion']}. {$seccion['nombre_seccion']}\n";
            $contenido .= str_repeat("-", strlen($seccion['nombre_seccion']) + 3) . "\n\n";
            $contenido .= $seccion['contenido'] . "\n\n";
        }

        // Pie
        $contenido .= str_repeat("-", 60) . "\n";
        $contenido .= "Documento generado por EnterpriseSST - Copia del cliente\n";
        $contenido .= "Este documento es editable para uso interno de la empresa.\n";

        return $contenido;
    }

    /**
     * Exportar múltiples documentos como ZIP
     */
    public function zip($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $carpeta = $this->request->getGet('carpeta');
        $formato = $this->request->getGet('formato') ?? 'pdf';

        // Obtener documentos
        if ($carpeta) {
            $documentos = $this->documentoModel->getByCarpeta($carpeta);
        } else {
            $documentos = $this->documentoModel->getByCliente($idCliente, 'aprobado');
        }

        if (empty($documentos)) {
            return redirect()->back()->with('error', 'No hay documentos para exportar');
        }

        // TODO: Implementar generación de ZIP
        return redirect()->back()->with('info', 'Función de exportación ZIP en desarrollo');
    }

    /**
     * Registra exportación en historial
     */
    private function registrarExportacion(int $idDocumento, string $formato): void
    {
        $db = \Config\Database::connect();

        $db->table('tbl_doc_exportaciones')->insert([
            'id_documento' => $idDocumento,
            'formato' => $formato,
            'exportado_por' => session()->get('id_usuario'),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Descargar última versión del documento
     */
    public function descargar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->find($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        // Si está aprobado, descargar PDF oficial
        if ($documento['estado'] === 'aprobado') {
            return $this->pdf($idDocumento);
        }

        // Si no, descargar borrador
        return $this->pdfBorrador($idDocumento);
    }

    /**
     * Vista previa para impresión
     */
    public function vistaImpresion($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $secciones = $this->seccionModel->getByDocumento($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $firmas = $this->firmaModel->getEstadoFirmas($idDocumento);

        return view('documentacion/export/vista_impresion', [
            'documento' => $documento,
            'secciones' => $secciones,
            'cliente' => $cliente,
            'firmas' => $firmas
        ]);
    }

    /**
     * Historial de exportaciones de un documento
     */
    public function historial($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->find($idDocumento);

        $exportaciones = $this->db->table('tbl_doc_exportaciones')
            ->select('tbl_doc_exportaciones.*, tbl_usuarios.nombre as usuario_nombre')
            ->join('tbl_usuarios', 'tbl_usuarios.id_usuario = tbl_doc_exportaciones.exportado_por', 'left')
            ->where('id_documento', $idDocumento)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('documentacion/export/historial', [
            'documento' => $documento,
            'exportaciones' => $exportaciones
        ]);
    }
}
