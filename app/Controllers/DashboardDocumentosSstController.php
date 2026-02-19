<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\Controller;

class DashboardDocumentosSstController extends Controller
{
    public function index()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $clientModel = new ClientModel();
        $data['clientes'] = $clientModel->where('estado', 'activo')->orderBy('nombre_cliente', 'ASC')->findAll();

        return view('admin/dashboard_documentos_sst', $data);
    }

    public function getData()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('tbl_documentos_sst d');
        $builder->select('d.id_documento, d.tipo_documento, d.codigo, d.titulo, d.anio, d.version, d.estado, d.created_at, d.updated_at, c.nombre_cliente, c.nit_cliente, c.id_cliente');
        $builder->join('tbl_clientes c', 'c.id_cliente = d.id_cliente', 'left');

        $idCliente = $this->request->getGet('id_cliente');
        $fechaDesde = $this->request->getGet('fecha_desde');
        $fechaHasta = $this->request->getGet('fecha_hasta');
        $estado = $this->request->getGet('estado');

        if (!empty($idCliente)) {
            $builder->where('d.id_cliente', $idCliente);
        }
        if (!empty($fechaDesde)) {
            $builder->where('d.created_at >=', $fechaDesde . ' 00:00:00');
        }
        if (!empty($fechaHasta)) {
            $builder->where('d.created_at <=', $fechaHasta . ' 23:59:59');
        }
        if (!empty($estado)) {
            $builder->where('d.estado', $estado);
        }

        $builder->orderBy('d.updated_at', 'DESC');
        $documentos = $builder->get()->getResultArray();

        // Estadísticas por estado
        $builderStats = $db->table('tbl_documentos_sst d');
        $builderStats->select('d.estado, COUNT(*) as total');
        if (!empty($idCliente)) {
            $builderStats->where('d.id_cliente', $idCliente);
        }
        if (!empty($fechaDesde)) {
            $builderStats->where('d.created_at >=', $fechaDesde . ' 00:00:00');
        }
        if (!empty($fechaHasta)) {
            $builderStats->where('d.created_at <=', $fechaHasta . ' 23:59:59');
        }
        $builderStats->groupBy('d.estado');
        $statsResult = $builderStats->get()->getResultArray();

        $estadisticas = [
            'borrador'        => 0,
            'generado'        => 0,
            'pendiente_firma' => 0,
            'aprobado'        => 0,
            'firmado'         => 0,
            'obsoleto'        => 0,
        ];
        $total = 0;
        foreach ($statsResult as $row) {
            $estadisticas[$row['estado']] = (int) $row['total'];
            $total += (int) $row['total'];
        }
        $estadisticas['total'] = $total;

        return $this->response->setJSON([
            'success'      => true,
            'documentos'   => $documentos,
            'estadisticas' => $estadisticas,
        ]);
    }
}
