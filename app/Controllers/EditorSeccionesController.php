<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class EditorSeccionesController extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Lista de documentos SST con sus versiones
     */
    public function index()
    {
        // Obtener lista de clientes para filtro
        $clientes = $this->db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente')
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente')
            ->get()
            ->getResultArray();

        // Obtener documentos con info de versión vigente
        $documentos = $this->db->table('tbl_documentos_sst d')
            ->select('d.id_documento, d.id_cliente, d.tipo_documento, d.codigo, d.titulo, d.anio, d.estado, d.version, d.updated_at, c.nombre_cliente, v.id_version, v.version_texto, v.estado as estado_version')
            ->join('tbl_clientes c', 'c.id_cliente = d.id_cliente', 'left')
            ->join('tbl_doc_versiones_sst v', 'v.id_documento = d.id_documento AND v.estado = "vigente"', 'left')
            ->where('d.contenido IS NOT NULL')
            ->where('d.contenido !=', '')
            ->orderBy('d.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/editor_secciones/index', [
            'documentos' => $documentos,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Formulario de edición de secciones de un documento
     */
    public function edit($idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst d')
            ->select('d.*, c.nombre_cliente')
            ->join('tbl_clientes c', 'c.id_cliente = d.id_cliente', 'left')
            ->where('d.id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            session()->setFlashdata('error', 'Documento no encontrado');
            return redirect()->to(site_url('admin/editor-secciones'));
        }

        $contenido = json_decode($documento['contenido'], true);
        $secciones = $contenido['secciones'] ?? [];

        // Obtener versión vigente si existe
        $versionVigente = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->get()
            ->getRowArray();

        return view('admin/editor_secciones/edit', [
            'documento' => $documento,
            'secciones' => $secciones,
            'versionVigente' => $versionVigente,
        ]);
    }

    /**
     * Guardar cambios en secciones (actualiza documento + snapshot vigente)
     */
    public function update($idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            session()->setFlashdata('error', 'Documento no encontrado');
            return redirect()->to(site_url('admin/editor-secciones'));
        }

        $contenido = json_decode($documento['contenido'], true);
        $seccionesPost = $this->request->getPost('secciones') ?? [];

        // Actualizar cada sección con el contenido del POST
        if (isset($contenido['secciones'])) {
            foreach ($contenido['secciones'] as $idx => &$seccion) {
                $key = $seccion['key'] ?? '';
                if ($key && isset($seccionesPost[$key])) {
                    $seccion['contenido'] = $seccionesPost[$key];
                }
            }
            unset($seccion);
        }

        $contenidoJson = json_encode($contenido, JSON_UNESCAPED_UNICODE);
        $ahora = date('Y-m-d H:i:s');

        // 1. Actualizar tbl_documentos_sst.contenido
        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->update([
                'contenido' => $contenidoJson,
                'updated_at' => $ahora,
            ]);

        // 2. Actualizar snapshot de la versión vigente
        $versionVigente = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->get()
            ->getRowArray();

        if ($versionVigente) {
            $updateData = [
                'contenido_snapshot' => $contenidoJson,
            ];
            // Regenerar hash si existía
            if (!empty($versionVigente['hash_documento'])) {
                $updateData['hash_documento'] = hash('sha256', $contenidoJson);
            }

            $this->db->table('tbl_doc_versiones_sst')
                ->where('id_version', $versionVigente['id_version'])
                ->update($updateData);
        }

        session()->setFlashdata('success', 'Secciones actualizadas correctamente en documento y snapshot vigente');
        return redirect()->to(site_url('admin/editor-secciones/edit/' . $idDocumento));
    }
}
