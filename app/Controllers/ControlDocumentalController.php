<?php

namespace App\Controllers;

use App\Models\DocDocumentoModel;
use App\Models\DocSeccionModel;
use App\Models\DocVersionModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

/**
 * Control Documental ISO
 * Maneja versionamiento, historial de cambios y gestión de estados de documentos
 */
class ControlDocumentalController extends Controller
{
    protected $documentoModel;
    protected $seccionModel;
    protected $versionModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->documentoModel = new DocDocumentoModel();
        $this->seccionModel = new DocSeccionModel();
        $this->versionModel = new DocVersionModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Ver historial de versiones de un documento
     */
    public function historial($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $versiones = $this->versionModel->getByDocumento($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        return view('control_documental/historial', [
            'documento' => $documento,
            'versiones' => $versiones,
            'cliente' => $cliente
        ]);
    }

    /**
     * Ver detalle de una versión específica
     */
    public function verVersion($idVersion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $version = $this->versionModel->find($idVersion);

        if (!$version) {
            return redirect()->back()->with('error', 'Versión no encontrada');
        }

        $documento = $this->documentoModel->getCompleto($version['id_documento']);
        $snapshot = $this->versionModel->getSnapshot($idVersion);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        return view('control_documental/ver_version', [
            'documento' => $documento,
            'version' => $version,
            'snapshot' => $snapshot,
            'cliente' => $cliente
        ]);
    }

    /**
     * Comparar dos versiones
     */
    public function comparar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $v1 = $this->request->getGet('v1');
        $v2 = $this->request->getGet('v2');

        if (!$v1 || !$v2) {
            return redirect()->back()->with('error', 'Seleccione dos versiones para comparar');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $version1 = $this->versionModel->find($v1);
        $version2 = $this->versionModel->find($v2);
        $diferencias = $this->versionModel->comparar($v1, $v2);

        return view('control_documental/comparar', [
            'documento' => $documento,
            'version1' => $version1,
            'version2' => $version2,
            'diferencias' => $diferencias
        ]);
    }

    /**
     * Formulario para crear nueva versión
     */
    public function nuevaVersion($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $versionActual = $documento['version_actual'] ?? '1.0';

        // Calcular siguiente versión
        $partes = explode('.', $versionActual);
        $mayor = (int)$partes[0];
        $menor = (int)($partes[1] ?? 0);

        $siguientesMenor = $mayor . '.' . ($menor + 1);
        $siguientesMayor = ($mayor + 1) . '.0';

        return view('control_documental/nueva_version', [
            'documento' => $documento,
            'cliente' => $cliente,
            'versionActual' => $versionActual,
            'siguienteMenor' => $siguientesMenor,
            'siguienteMayor' => $siguientesMayor
        ]);
    }

    /**
     * Crear nueva versión del documento
     */
    public function crearVersion($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $tipoCambio = $this->request->getPost('tipo_cambio');
        $descripcion = $this->request->getPost('descripcion_cambio');
        $autorizadoPor = session()->get('nombre') ?? session()->get('email');

        // Validar que el documento esté en estado que permita versionar
        $documento = $this->documentoModel->find($idDocumento);

        if (!in_array($documento['estado'], ['aprobado', 'en_revision'])) {
            return redirect()->back()->with('error', 'El documento debe estar aprobado o en revisión para crear nueva versión');
        }

        // Guardar snapshot del contenido actual
        $contenidoJson = $this->seccionModel->getContenidoJson($idDocumento);

        // Crear la versión
        $resultado = $this->versionModel->crearVersion($idDocumento, $tipoCambio, $descripcion, $autorizadoPor);

        if (!empty($resultado)) {
            // Actualizar versión actual en el documento
            $nuevaVersion = $resultado['nueva_version'] ?? $this->calcularNuevaVersion($documento['version_actual'], $tipoCambio);

            $this->documentoModel->update($idDocumento, [
                'version_actual' => $nuevaVersion,
                'estado' => 'borrador' // Volver a borrador para edición
            ]);

            return redirect()->to("/control-documental/historial/{$idDocumento}")
                            ->with('success', "Nueva versión {$nuevaVersion} creada exitosamente");
        }

        return redirect()->back()->with('error', 'Error al crear nueva versión');
    }

    /**
     * Restaurar versión anterior
     */
    public function restaurar($idVersion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $autorizadoPor = session()->get('nombre') ?? session()->get('email');

        $version = $this->versionModel->find($idVersion);

        if (!$version) {
            return redirect()->back()->with('error', 'Versión no encontrada');
        }

        $resultado = $this->versionModel->restaurar($idVersion, $autorizadoPor);

        if ($resultado) {
            return redirect()->to("/control-documental/historial/{$version['id_documento']}")
                            ->with('success', 'Versión restaurada exitosamente');
        }

        return redirect()->back()->with('error', 'Error al restaurar versión');
    }

    /**
     * Marcar versión como obsoleta
     */
    public function marcarObsoleto($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $motivo = $this->request->getPost('motivo');

        $this->documentoModel->update($idDocumento, [
            'estado' => 'obsoleto'
        ]);

        // Registrar en historial
        $this->versionModel->insert([
            'id_documento' => $idDocumento,
            'version' => 'OBSOLETO',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => "Documento marcado como obsoleto. Motivo: {$motivo}",
            'autorizado_por' => session()->get('nombre') ?? session()->get('email'),
            'estado' => 'obsoleto'
        ]);

        return redirect()->back()->with('success', 'Documento marcado como obsoleto');
    }

    /**
     * Aprobar documento y crear versión vigente
     */
    public function aprobar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->find($idDocumento);

        if ($documento['estado'] !== 'pendiente_firma') {
            return redirect()->back()->with('error', 'El documento debe tener todas las firmas para aprobar');
        }

        // Guardar snapshot
        $contenidoJson = $this->seccionModel->getContenidoJson($idDocumento);

        // Marcar versión anterior como obsoleta
        $this->versionModel->where('id_documento', $idDocumento)
                          ->where('estado', 'vigente')
                          ->set(['estado' => 'obsoleto'])
                          ->update();

        // Crear versión vigente
        $this->versionModel->insert([
            'id_documento' => $idDocumento,
            'version' => $documento['version_actual'],
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Versión aprobada y vigente',
            'contenido_snapshot' => $contenidoJson,
            'autorizado_por' => session()->get('nombre'),
            'estado' => 'vigente'
        ]);

        // Actualizar estado del documento
        $this->documentoModel->update($idDocumento, [
            'estado' => 'aprobado',
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
            'aprobado_por' => session()->get('id_usuario')
        ]);

        return redirect()->to("/documentacion/ver/{$idDocumento}")
                        ->with('success', 'Documento aprobado y versión vigente creada');
    }

    /**
     * Obtener historial de cambios (AJAX)
     */
    public function getHistorialJson($idDocumento)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $versiones = $this->versionModel->getByDocumento($idDocumento);

        return $this->response->setJSON([
            'success' => true,
            'versiones' => $versiones
        ]);
    }

    /**
     * Calcula la nueva versión según el tipo de cambio
     */
    protected function calcularNuevaVersion(string $versionActual, string $tipoCambio): string
    {
        $partes = explode('.', $versionActual);
        $mayor = (int)$partes[0];
        $menor = (int)($partes[1] ?? 0);

        if ($tipoCambio === 'mayor') {
            return ($mayor + 1) . '.0';
        } else {
            return $mayor . '.' . ($menor + 1);
        }
    }

    /**
     * Genera encabezado estándar ISO para documento
     */
    public function generarEncabezado($idDocumento)
    {
        $documento = $this->documentoModel->getCompleto($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        $encabezado = [
            'empresa' => $cliente['nombre_cliente'] ?? '',
            'nit' => $cliente['nit'] ?? '',
            'sistema' => 'SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO',
            'documento' => $documento['nombre'] ?? '',
            'codigo' => $documento['codigo'] ?? '',
            'version' => $documento['version_actual'] ?? '1.0',
            'fecha_vigencia' => $documento['fecha_vigencia'] ?? date('Y-m-d'),
            'pagina' => '1 de X'
        ];

        return $this->response->setJSON($encabezado);
    }

    /**
     * Genera tabla de control de cambios
     */
    public function tablaControlCambios($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $versiones = $this->versionModel->getByDocumento($idDocumento);

        return view('control_documental/tabla_cambios', [
            'documento' => $documento,
            'versiones' => $versiones
        ]);
    }
}
