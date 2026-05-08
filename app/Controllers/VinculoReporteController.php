<?php

namespace App\Controllers;

use App\Models\CarpetaReporteVinculoModel;
use App\Models\DocCarpetaModel;
use CodeIgniter\Controller;

/**
 * Controlador para vincular reportes existentes (tbl_reporte) a carpetas de
 * documentacion (tbl_doc_carpetas) sin duplicar archivos.
 *
 * NO crea PDFs ni copia archivos. Solo persiste un vinculo logico (id_carpeta,
 * id_reporte) en tbl_carpeta_reporte_vinculo. La carpeta muestra el reporte
 * como referencia apuntando al `enlace` original del reporte.
 *
 * Endpoints:
 *   GET  /documentacion/vinculo/reportes-disponibles/{idCliente}   - JSON para Select2
 *   POST /documentacion/vinculo/agregar                            - Crea vinculo
 *   POST /documentacion/vinculo/{idVinculo}/quitar                 - Elimina vinculo
 */
class VinculoReporteController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Devuelve JSON con todos los reportes del cliente, listos para alimentar Select2.
     * Soporta busqueda con ?q=texto.
     */
    public function reportesDisponibles(int $idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'No autenticado'])->setStatusCode(401);
        }

        $q = trim((string) $this->request->getGet('q'));
        $idCarpeta = (int) ($this->request->getGet('id_carpeta') ?? 0);

        $builder = $this->db->table('tbl_reporte r')
            ->select('r.id_reporte, r.titulo_reporte, r.enlace, r.estado, r.created_at,
                      rt.report_type, dr.detail_report')
            ->join('report_type_table rt', 'rt.id_report_type = r.id_report_type', 'left')
            ->join('detail_report dr', 'dr.id_detailreport = r.id_detailreport', 'left')
            ->where('r.id_cliente', $idCliente)
            ->orderBy('r.updated_at', 'DESC')
            ->limit(100);

        if ($q !== '') {
            $builder->groupStart()
                ->like('r.titulo_reporte', $q)
                ->orLike('rt.report_type', $q)
                ->orLike('dr.detail_report', $q)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();

        // Si nos pasaron id_carpeta, marcar los que ya estan vinculados para que
        // el frontend pueda deshabilitarlos en el Select2.
        $yaVinculados = [];
        if ($idCarpeta > 0) {
            $vModel = new CarpetaReporteVinculoModel();
            $existentes = $vModel->where('id_carpeta', $idCarpeta)->findAll();
            foreach ($existentes as $v) $yaVinculados[(int) $v['id_reporte']] = true;
        }

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'id'             => (int) $r['id_reporte'],
                'text'           => $r['titulo_reporte'],
                'tipo_reporte'   => $r['report_type'] ?? '',
                'detalle'        => $r['detail_report'] ?? '',
                'estado'         => $r['estado'] ?? '',
                'fecha'          => $r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '',
                'enlace'         => $r['enlace'] ?? '',
                'ya_vinculado'   => isset($yaVinculados[(int) $r['id_reporte']]),
            ];
        }

        return $this->response->setJSON(['results' => $items, 'pagination' => ['more' => false]]);
    }

    /**
     * Crea un vinculo carpeta-reporte. POST: id_carpeta, id_reporte, observacion?
     */
    public function agregar()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idCarpeta = (int) $this->request->getPost('id_carpeta');
        $idReporte = (int) $this->request->getPost('id_reporte');
        $observacion = trim((string) $this->request->getPost('observacion'));

        if ($idCarpeta <= 0 || $idReporte <= 0) {
            return redirect()->back()->with('error', 'Datos invalidos para el vinculo.');
        }

        // Validar que la carpeta exista y obtener su id_cliente
        $carpeta = (new DocCarpetaModel())->find($idCarpeta);
        if (!$carpeta) {
            return redirect()->back()->with('error', 'Carpeta no encontrada.');
        }

        // Validar que el reporte pertenezca al MISMO cliente que la carpeta
        $reporte = $this->db->table('tbl_reporte')
            ->where('id_reporte', $idReporte)
            ->get()->getRowArray();
        if (!$reporte) {
            return redirect()->back()->with('error', 'Reporte no encontrado.');
        }
        if ((int) $reporte['id_cliente'] !== (int) $carpeta['id_cliente']) {
            log_message('warning', '[VinculoReporte] intento de vincular reporte de otro cliente: '
                . "carpeta_cliente={$carpeta['id_cliente']} reporte_cliente={$reporte['id_cliente']} "
                . "user_id=" . session()->get('id_usuario'));
            return redirect()->back()->with('error', 'El reporte no pertenece al cliente de la carpeta.');
        }

        $vModel = new CarpetaReporteVinculoModel();
        if ($vModel->existeVinculo($idCarpeta, $idReporte)) {
            return redirect()->back()->with('warning', 'El reporte ya estaba vinculado a esta carpeta.');
        }

        $vModel->insert([
            'id_carpeta'  => $idCarpeta,
            'id_reporte'  => $idReporte,
            'id_cliente'  => (int) $carpeta['id_cliente'],
            'observacion' => $observacion ?: null,
            'created_by'  => session()->get('id_usuario'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Reporte vinculado a la carpeta.');
    }

    /**
     * Elimina un vinculo. NO toca el reporte ni el archivo.
     */
    public function quitar(int $idVinculo)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $vModel = new CarpetaReporteVinculoModel();
        $vinculo = $vModel->find($idVinculo);
        if (!$vinculo) {
            return redirect()->back()->with('error', 'Vinculo no encontrado.');
        }

        $vModel->delete($idVinculo);
        return redirect()->back()->with('success', 'Vinculo eliminado. El reporte original sigue intacto en el reportList.');
    }
}
