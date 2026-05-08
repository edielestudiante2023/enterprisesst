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

        // Si nos pasaron id_carpeta, EXCLUIR los ya vinculados del resultado
        // (no mostrarlos para evitar confusion).
        $yaVinculados = [];
        if ($idCarpeta > 0) {
            $vModel = new CarpetaReporteVinculoModel();
            $existentes = $vModel->where('id_carpeta', $idCarpeta)->findAll();
            foreach ($existentes as $v) $yaVinculados[] = (int) $v['id_reporte'];
            if (!empty($yaVinculados)) {
                $builder->whereNotIn('r.id_reporte', $yaVinculados);
            }
        }

        $rows = $builder->get()->getResultArray();

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
            ];
        }

        return $this->response->setJSON(['results' => $items, 'pagination' => ['more' => false]]);
    }

    /**
     * Crea uno o varios vinculos carpeta-reporte.
     * POST: id_carpeta (int), id_reporte (array o int), observacion (opcional).
     * El form envia id_reporte como arreglo cuando el Select2 es multiple.
     */
    public function agregar()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idCarpeta = (int) $this->request->getPost('id_carpeta');
        $observacion = trim((string) $this->request->getPost('observacion'));
        $rawIds = $this->request->getPost('id_reporte');

        // Aceptar tanto array (multiple Select2) como un solo valor (compat)
        $idsReportes = [];
        if (is_array($rawIds)) {
            foreach ($rawIds as $r) {
                $r = (int) $r;
                if ($r > 0) $idsReportes[] = $r;
            }
        } else {
            $r = (int) $rawIds;
            if ($r > 0) $idsReportes[] = $r;
        }
        $idsReportes = array_values(array_unique($idsReportes));

        if ($idCarpeta <= 0 || empty($idsReportes)) {
            return redirect()->back()->with('error', 'Selecciona al menos un reporte para vincular.');
        }

        $carpeta = (new DocCarpetaModel())->find($idCarpeta);
        if (!$carpeta) {
            return redirect()->back()->with('error', 'Carpeta no encontrada.');
        }
        $idClienteCarpeta = (int) $carpeta['id_cliente'];

        // Cargar todos los reportes solicitados de un golpe y validar ownership.
        $reportes = $this->db->table('tbl_reporte')
            ->whereIn('id_reporte', $idsReportes)
            ->get()->getResultArray();
        $reportesPorId = [];
        foreach ($reportes as $r) $reportesPorId[(int) $r['id_reporte']] = $r;

        $vModel = new CarpetaReporteVinculoModel();
        $vinculados = 0;
        $omitidosOtroCliente = 0;
        $omitidosYaExisten = 0;
        $omitidosNoEncontrados = 0;

        foreach ($idsReportes as $idReporte) {
            $r = $reportesPorId[$idReporte] ?? null;
            if (!$r) { $omitidosNoEncontrados++; continue; }

            if ((int) $r['id_cliente'] !== $idClienteCarpeta) {
                log_message('warning', '[VinculoReporte] cross-cliente: carpeta_cliente='
                    . $idClienteCarpeta . ' reporte=' . $idReporte
                    . ' cliente_reporte=' . $r['id_cliente']
                    . ' user=' . session()->get('id_usuario'));
                $omitidosOtroCliente++;
                continue;
            }
            if ($vModel->existeVinculo($idCarpeta, $idReporte)) {
                $omitidosYaExisten++;
                continue;
            }

            $vModel->insert([
                'id_carpeta'  => $idCarpeta,
                'id_reporte'  => $idReporte,
                'id_cliente'  => $idClienteCarpeta,
                'observacion' => $observacion ?: null,
                'created_by'  => session()->get('id_usuario'),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $vinculados++;
        }

        // Construir mensaje resumen
        if ($vinculados === 0) {
            return redirect()->back()->with('warning', 'No se vinculo ningun reporte (todos ya existian o no pertenecian al cliente).');
        }

        $partes = ["{$vinculados} vinculado(s)"];
        if ($omitidosYaExisten > 0)     $partes[] = "{$omitidosYaExisten} ya existia(n)";
        if ($omitidosOtroCliente > 0)   $partes[] = "{$omitidosOtroCliente} de otro cliente (omitido)";
        if ($omitidosNoEncontrados > 0) $partes[] = "{$omitidosNoEncontrados} no encontrado(s)";

        return redirect()->back()->with('success', implode(' · ', $partes));
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
