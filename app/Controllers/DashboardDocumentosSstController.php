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

    /**
     * POST /admin/dashboard-documentos-sst/solicitar-eliminacion
     * El consultor envía la solicitud con motivo → genera token → manda email a Edison
     */
    public function solicitarEliminacion()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $idDocumento = (int) $this->request->getPost('id_documento');
        $motivo      = trim($this->request->getPost('motivo') ?? '');

        if (!$idDocumento || strlen($motivo) < 10) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        $db = \Config\Database::connect();

        // Verificar que existe el documento
        $doc = $db->table('tbl_documentos_sst d')
            ->select('d.*, c.nombre_cliente')
            ->join('tbl_clientes c', 'c.id_cliente = d.id_cliente', 'left')
            ->where('d.id_documento', $idDocumento)
            ->get()->getRowArray();

        if (!$doc) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado']);
        }

        // Verificar que no haya solicitud pendiente para este documento
        $pendiente = $db->table('tbl_solicitudes_eliminacion_doc')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'pendiente')
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if ($pendiente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe una solicitud pendiente para este documento']);
        }

        $token      = bin2hex(random_bytes(32));
        $expiresAt  = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $solicitadoPor = $session->get('nombre') ?? $session->get('email') ?? 'Consultor';

        $db->table('tbl_solicitudes_eliminacion_doc')->insert([
            'id_documento'     => $idDocumento,
            'titulo_documento' => $doc['titulo'],
            'codigo_documento' => $doc['codigo'],
            'nombre_cliente'   => $doc['nombre_cliente'],
            'motivo'           => $motivo,
            'solicitado_por'   => $solicitadoPor,
            'token'            => $token,
            'estado'           => 'pendiente',
            'expires_at'       => $expiresAt,
        ]);

        // Enviar email a Edison con enlace de aprobación
        $urlAprobar = base_url('admin/dashboard-documentos-sst/aprobar-eliminacion/' . $token);
        $this->enviarEmailEliminacion($doc, $motivo, $solicitadoPor, $urlAprobar);

        return $this->response->setJSON(['success' => true, 'message' => 'Solicitud enviada. Edison recibirá un email para aprobar la eliminación.']);
    }

    /**
     * GET /admin/dashboard-documentos-sst/aprobar-eliminacion/{token}
     * Edison abre el link → se elimina el documento sin rastro
     */
    public function aprobarEliminacion(string $token)
    {
        $db = \Config\Database::connect();

        $solicitud = $db->table('tbl_solicitudes_eliminacion_doc')
            ->where('token', $token)
            ->where('estado', 'pendiente')
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$solicitud) {
            return $this->response->setBody('
                <html><body style="font-family:sans-serif;text-align:center;padding:60px">
                <h2 style="color:#dc3545">&#10060; Token inválido o expirado</h2>
                <p>Esta solicitud ya fue procesada, expiró, o el enlace no es válido.</p>
                </body></html>
            ');
        }

        $idDocumento = (int) $solicitud['id_documento'];

        // Hard delete: eliminar en orden para respetar FK
        $db->table('tbl_doc_firma_solicitudes')->where('id_documento', $idDocumento)->delete();
        $db->table('tbl_doc_versiones_sst')->where('id_documento', $idDocumento)->delete();
        $db->table('tbl_documentos_sst')->where('id_documento', $idDocumento)->delete();

        // Marcar solicitud como aprobada
        $db->table('tbl_solicitudes_eliminacion_doc')
            ->where('id_solicitud', $solicitud['id_solicitud'])
            ->update(['estado' => 'aprobada', 'aprobado_at' => date('Y-m-d H:i:s')]);

        return $this->response->setBody('
            <html><body style="font-family:sans-serif;text-align:center;padding:60px">
            <h2 style="color:#198754">&#10003; Documento eliminado correctamente</h2>
            <p><strong>' . esc($solicitud['codigo_documento']) . ' — ' . esc($solicitud['titulo_documento']) . '</strong></p>
            <p>Cliente: ' . esc($solicitud['nombre_cliente']) . '</p>
            <p>Motivo: ' . esc($solicitud['motivo']) . '</p>
            <p>Solicitado por: ' . esc($solicitud['solicitado_por']) . '</p>
            <p style="color:#6c757d;font-size:0.9rem">El documento ha sido eliminado permanentemente sin dejar registro en el sistema.</p>
            </body></html>
        ');
    }

    private function enviarEmailEliminacion(array $doc, string $motivo, string $solicitadoPor, string $urlAprobar): void
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'Cycloid Talent');
            $email->setSubject('⚠️ Solicitud de eliminación de documento SST — ' . $doc['codigo']);
            $email->addTo('edison.cuervo@cycloidtalent.com', 'Edison Cuervo');
            $email->addContent('text/html', '
                <div style="font-family:sans-serif;max-width:600px;margin:auto;padding:30px">
                    <h2 style="color:#dc3545">Solicitud de Eliminación de Documento</h2>
                    <table style="width:100%;border-collapse:collapse">
                        <tr><td style="padding:8px;font-weight:bold;width:140px">Documento:</td><td style="padding:8px">' . htmlspecialchars($doc['codigo']) . ' — ' . htmlspecialchars($doc['titulo']) . '</td></tr>
                        <tr style="background:#f8f9fa"><td style="padding:8px;font-weight:bold">Cliente:</td><td style="padding:8px">' . htmlspecialchars($doc['nombre_cliente']) . '</td></tr>
                        <tr><td style="padding:8px;font-weight:bold">Solicitado por:</td><td style="padding:8px">' . htmlspecialchars($solicitadoPor) . '</td></tr>
                        <tr style="background:#f8f9fa"><td style="padding:8px;font-weight:bold">Motivo:</td><td style="padding:8px">' . nl2br(htmlspecialchars($motivo)) . '</td></tr>
                    </table>
                    <div style="margin-top:30px;text-align:center">
                        <a href="' . $urlAprobar . '" style="background:#dc3545;color:#fff;padding:14px 32px;text-decoration:none;border-radius:6px;font-size:16px;font-weight:bold">
                            Aprobar Eliminación
                        </a>
                    </div>
                    <p style="color:#6c757d;font-size:0.85rem;margin-top:20px">
                        Este enlace expira en 48 horas. Si no reconoces esta solicitud, ignora este correo.
                    </p>
                </div>
            ');

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $sendgrid->send($email);
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email eliminacion doc: ' . $e->getMessage());
        }
    }
}
