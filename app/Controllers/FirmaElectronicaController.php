<?php

namespace App\Controllers;

use App\Models\DocFirmaModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use CodeIgniter\Controller;

/**
 * Firma Electronica tipo DocuSeal
 * Flujo: Delegado SST (opcional) → Representante Legal
 * Trabaja con tbl_documentos_sst y tbl_doc_versiones_sst
 */
class FirmaElectronicaController extends Controller
{
    protected $firmaModel;
    protected $clienteModel;
    protected $contextoModel;
    protected $db;

    public function __construct()
    {
        $this->firmaModel = new DocFirmaModel();
        $this->clienteModel = new ClientModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Obtiene documento SST completo desde tbl_documentos_sst
     */
    private function getDocumentoSST(int $idDocumento): ?array
    {
        return $this->db->table('tbl_documentos_sst')
                       ->where('id_documento', $idDocumento)
                       ->get()
                       ->getRowArray();
    }

    /**
     * Solicitar firma para un documento
     */
    public function solicitar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->getDocumentoSST($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        if (!in_array($documento['estado'], ['borrador', 'generado', 'aprobado', 'en_revision', 'pendiente_firma'])) {
            return redirect()->back()->with('error', 'El documento debe estar guardado para solicitar firmas');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);
        $estadoFirmas = $this->firmaModel->getEstadoFirmas($idDocumento);

        $requiereDelegado = (bool) ($contexto['requiere_delegado_sst'] ?? false);

        return view('firma/solicitar', [
            'documento' => $documento,
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estadoFirmas' => $estadoFirmas,
            'requiereDelegado' => $requiereDelegado
        ]);
    }

    /**
     * Crear solicitud de firma (POST)
     * Crea el flujo completo: Delegado SST (si aplica) → Representante Legal
     */
    public function crearSolicitud()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idDocumento = $this->request->getPost('id_documento');
        $documento = $this->getDocumentoSST($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);

        // Verificar si hay delegado/vigia configurado con datos completos
        $tieneSegundoFirmante = !empty(trim($contexto['delegado_sst_nombre'] ?? ''))
                             && !empty(trim($contexto['delegado_sst_email'] ?? ''));

        $orden = 1;
        $solicitudesCreadas = [];
        $solicitudDelegadoCreada = false;

        // 1. Crear solicitud para Delegado/Vigia SST (solo si tiene datos completos)
        if ($tieneSegundoFirmante) {
            $datosDelegado = [
                'id_documento' => $idDocumento,
                'firmante_tipo' => 'delegado_sst',
                'firmante_email' => $contexto['delegado_sst_email'],
                'firmante_nombre' => $contexto['delegado_sst_nombre'],
                'firmante_cargo' => $contexto['delegado_sst_cargo'] ?? 'Delegado SST',
                'firmante_documento' => $contexto['delegado_sst_cedula'] ?? '',
                'orden_firma' => $orden++,
                'estado' => 'pendiente'
            ];

            $idDelegado = $this->firmaModel->crearSolicitud($datosDelegado);
            if ($idDelegado) {
                $solicitudesCreadas[] = $this->firmaModel->find($idDelegado);
                $solicitudDelegadoCreada = true;
                $this->firmaModel->registrarAudit($idDelegado, 'solicitud_creada', [
                    'creado_por' => session()->get('id_usuario'),
                    'tipo' => 'delegado_sst'
                ]);
            }
        }

        // 2. Crear solicitud para Representante Legal (siempre)
        // Estado 'esperando' solo si realmente se creó solicitud del delegado
        $datosRepLegal = [
            'id_documento' => $idDocumento,
            'firmante_tipo' => 'representante_legal',
            'firmante_email' => $contexto['representante_legal_email'] ?? '',
            'firmante_nombre' => $contexto['representante_legal_nombre'] ?? 'Representante Legal',
            'firmante_cargo' => $contexto['representante_legal_cargo'] ?? 'Representante Legal',
            'firmante_documento' => $contexto['representante_legal_cedula'] ?? '',
            'orden_firma' => $orden,
            'estado' => $solicitudDelegadoCreada ? 'esperando' : 'pendiente'
        ];

        $idRepLegal = $this->firmaModel->crearSolicitud($datosRepLegal);
        if ($idRepLegal) {
            $solicitudesCreadas[] = $this->firmaModel->find($idRepLegal);
            $this->firmaModel->registrarAudit($idRepLegal, 'solicitud_creada', [
                'creado_por' => session()->get('id_usuario'),
                'tipo' => 'representante_legal'
            ]);
        }

        if (!empty($solicitudesCreadas)) {
            // Cambiar estado del documento a pendiente_firma
            $this->db->table('tbl_documentos_sst')
                    ->where('id_documento', $idDocumento)
                    ->update(['estado' => 'pendiente_firma']);

            // Enviar correo al primer firmante
            $primerFirmante = $solicitudesCreadas[0];
            $this->enviarCorreoFirma($primerFirmante, $documento);

            $this->firmaModel->registrarAudit($primerFirmante['id_solicitud'], 'email_enviado', [
                'email' => $primerFirmante['firmante_email']
            ]);

            return redirect()->to("/firma/estado/{$idDocumento}")
                            ->with('success', 'Solicitud de firma enviada a ' . $primerFirmante['firmante_nombre']);
        }

        return redirect()->back()->with('error', 'Error al crear solicitud. Verifique que los datos de firmantes esten configurados en el contexto del cliente.');
    }

    /**
     * Enviar correo con enlace de firma
     */
    private function enviarCorreoFirma(array $solicitud, array $documento): bool
    {
        $urlFirma = base_url("firma/firmar/{$solicitud['token']}");
        $tipoFirmante = match($solicitud['firmante_tipo']) {
            'delegado_sst' => 'Delegado SST',
            'representante_legal' => 'Representante Legal',
            'elaboro' => 'Elaboro',
            'reviso' => 'Reviso',
            default => ucfirst($solicitud['firmante_tipo'])
        };

        $nombreDoc = $documento['titulo'] ?? $documento['nombre'] ?? 'Documento SST';
        $codigoDoc = $documento['codigo'] ?? '';
        $versionDoc = $documento['version'] ?? '1';

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma Electronica</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$solicitud['firmante_nombre']}</strong>,</p>
                <p>Se requiere su firma electronica como <strong>{$tipoFirmante}</strong> para el siguiente documento del Sistema de Gestion de Seguridad y Salud en el Trabajo:</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p><strong>Documento:</strong> {$nombreDoc}</p>
                    <p><strong>Codigo:</strong> {$codigoDoc}</p>
                    <p><strong>Version:</strong> {$versionDoc}</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #3B82F6; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Documento
                    </a>
                </div>

                <p style='color: #666; font-size: 12px;'>O copie este enlace en su navegador:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 12px;'>{$urlFirma}</p>

                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>
                    <strong>Importante:</strong> Este enlace es personal e intransferible. No lo comparta con nadie.<br>
                    El enlace expirara en 7 dias.
                </p>
            </div>
        </div>
        ";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $email->setSubject("Solicitud de Firma: {$codigoDoc} - {$nombreDoc}");
            $email->addTo($solicitud['firmante_email'], $solicitud['firmante_nombre']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            $statusCode = $response->statusCode();
            log_message('info', "SendGrid firma email enviado a {$solicitud['firmante_email']} - Status: {$statusCode}");

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email de firma via SendGrid: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar al consultor asignado que todas las firmas del documento están completas
     */
    private function notificarConsultorFirmasCompletas(int $idDocumento, array $documento): void
    {
        try {
            // Obtener id_cliente del documento
            $doc = $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->get()
                ->getRowArray();

            if (!$doc) return;

            // Obtener consultor asignado al cliente
            $cliente = $this->db->table('tbl_clientes')
                ->where('id_cliente', $doc['id_cliente'])
                ->get()
                ->getRowArray();

            if (!$cliente || empty($cliente['id_consultor'])) return;

            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $cliente['id_consultor'])
                ->get()
                ->getRowArray();

            if (!$consultor || empty($consultor['correo_consultor'])) return;

            $nombreDoc = $doc['titulo'] ?? 'Documento SST';
            $codigoDoc = $doc['codigo'] ?? '';
            $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';
            $urlDocumentacion = base_url('documentacion/' . $doc['id_cliente']);

            // Obtener resumen de firmantes
            $firmantes = $this->db->table('tbl_doc_firma_solicitudes')
                ->select('firmante_nombre, firmante_tipo, fecha_firma')
                ->where('id_documento', $idDocumento)
                ->where('estado', 'firmado')
                ->orderBy('orden_firma', 'ASC')
                ->get()
                ->getResultArray();

            $listaFirmantes = '';
            foreach ($firmantes as $f) {
                $tipo = match($f['firmante_tipo']) {
                    'delegado_sst' => 'Delegado SST',
                    'representante_legal' => 'Representante Legal',
                    'elaboro' => 'Elaboró',
                    'reviso' => 'Revisó',
                    default => ucfirst($f['firmante_tipo'])
                };
                $fecha = date('d/m/Y H:i', strtotime($f['fecha_firma']));
                $listaFirmantes .= "<tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>{$f['firmante_nombre']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>{$tipo}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>{$fecha}</td>
                </tr>";
            }

            $mensaje = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #059669 0%, #10B981 100%); padding: 20px; text-align: center;'>
                    <h2 style='color: white; margin: 0;'>Firmas Completadas</h2>
                </div>
                <div style='padding: 30px; background: #f8f9fa;'>
                    <p>Estimado/a <strong>{$consultor['nombre_consultor']}</strong>,</p>
                    <p>Le informamos que <strong>todas las firmas han sido completadas</strong> para el siguiente documento:</p>

                    <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10B981;'>
                        <p style='margin: 5px 0;'><strong>Cliente:</strong> {$nombreCliente}</p>
                        <p style='margin: 5px 0;'><strong>Documento:</strong> {$nombreDoc}</p>
                        <p style='margin: 5px 0;'><strong>Codigo:</strong> {$codigoDoc}</p>
                    </div>

                    <h3 style='color: #374151; font-size: 16px;'>Firmantes:</h3>
                    <table style='width: 100%; border-collapse: collapse; background: white; border-radius: 8px;'>
                        <thead>
                            <tr style='background: #e5e7eb;'>
                                <th style='padding: 8px; text-align: left;'>Nombre</th>
                                <th style='padding: 8px; text-align: left;'>Rol</th>
                                <th style='padding: 8px; text-align: left;'>Fecha firma</th>
                            </tr>
                        </thead>
                        <tbody>{$listaFirmantes}</tbody>
                    </table>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$urlDocumentacion}' style='background: #059669; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                            Ver en el Aplicativo
                        </a>
                    </div>

                    <p style='color: #666; font-size: 12px;'>El documento ya cuenta con todas las firmas electronicas requeridas y esta listo para su aprobacion final en el aplicativo.</p>
                </div>
            </div>
            ";

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $email->setSubject("Firmas Completadas: {$codigoDoc} - {$nombreDoc} ({$nombreCliente})");
            $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            log_message('info', "Email firmas completas enviado a consultor {$consultor['correo_consultor']} - Status: {$response->statusCode()}");

        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor firmas completas: ' . $e->getMessage());
        }
    }

    /**
     * Genera PDF del documento firmado y lo publica en tbl_reporte (reportList)
     */
    private function publicarDocumentoFirmado(int $idDocumento): void
    {
        try {
            $doc = $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->get()
                ->getRowArray();

            if (!$doc) return;

            $cliente = $this->db->table('tbl_clientes')
                ->where('id_cliente', $doc['id_cliente'])
                ->get()
                ->getRowArray();

            if (!$cliente) return;

            // Generar PDF usando la misma lógica de DocumentosSSTController::exportarPDF
            $contenido = json_decode($doc['contenido'], true);

            // Logo base64
            $logoBase64 = '';
            if (!empty($cliente['logo'])) {
                $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoMime = mime_content_type($logoPath);
                    $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
                }
            }

            // Versiones
            $versiones = $this->db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $idDocumento)
                ->orderBy('fecha_autorizacion', 'ASC')
                ->get()
                ->getResultArray();

            // Responsables
            $responsableModel = new \App\Models\ResponsableSSTModel();
            $responsables = $responsableModel->getByCliente($doc['id_cliente']);

            // Contexto
            $contextoModel = new \App\Models\ClienteContextoSstModel();
            $contexto = $contextoModel->getByCliente($doc['id_cliente']);

            // Consultor y firma
            $consultor = null;
            $firmaConsultorBase64 = '';
            $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
            if ($idConsultor) {
                $consultorModel = new \App\Models\ConsultantModel();
                $consultor = $consultorModel->find($idConsultor);
                if (!empty($consultor['firma_consultor'])) {
                    $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                    if (file_exists($firmaPath)) {
                        $firmaData = file_get_contents($firmaPath);
                        $firmaMime = mime_content_type($firmaPath);
                        $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                    }
                }
            }

            // Firmas electrónicas
            $firmasElectronicas = [];
            $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
                ->where('id_documento', $idDocumento)
                ->where('estado', 'firmado')
                ->get()
                ->getResultArray();

            foreach ($solicitudesFirma as $sol) {
                $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                    ->where('id_solicitud', $sol['id_solicitud'])
                    ->get()
                    ->getRowArray();
                $firmasElectronicas[$sol['firmante_tipo']] = [
                    'solicitud' => $sol,
                    'evidencia' => $evidencia
                ];
            }

            $data = [
                'titulo' => $doc['titulo'],
                'cliente' => $cliente,
                'documento' => $doc,
                'contenido' => $contenido,
                'anio' => $doc['anio'],
                'logoBase64' => $logoBase64,
                'versiones' => $versiones,
                'responsables' => $responsables,
                'contexto' => $contexto,
                'consultor' => $consultor,
                'firmaConsultorBase64' => $firmaConsultorBase64,
                'firmasElectronicas' => $firmasElectronicas
            ];

            // Renderizar HTML y generar PDF
            $html = view('documentos_sst/pdf_template', $data);
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            // Guardar archivo en uploads/{nit}/
            $nit = $cliente['nit_cliente'] ?? $doc['id_cliente'];
            $uploadDir = FCPATH . 'uploads/' . $nit;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . url_title(($doc['codigo'] ?? 'DOC') . '_' . $doc['titulo'], '-', true) . '.pdf';
            $filePath = $uploadDir . '/' . $fileName;
            file_put_contents($filePath, $pdfOutput);

            $enlace = base_url('uploads/' . $nit . '/' . $fileName);

            // Obtener ID del detail_report "Documento SG-SST"
            $detailReport = $this->db->table('detail_report')
                ->where('detail_report', 'Documento SG-SST')
                ->get()
                ->getRowArray();
            $idDetailReport = $detailReport['id_detailreport'] ?? 2; // fallback: Documentos de Gestión

            // report_type = 12 (Reportes SST)
            $idReportType = 12;
            $codigoBusqueda = $doc['codigo'] ?? $doc['titulo'];

            // Contar publicaciones anteriores de este documento para numerar
            $publicacionesAnteriores = $this->db->table('tbl_reporte')
                ->where("titulo_reporte COLLATE utf8mb4_general_ci LIKE '%" . $this->db->escapeLikeString($codigoBusqueda) . "%'", null, false)
                ->where('id_cliente', $doc['id_cliente'])
                ->where('id_detailreport', $idDetailReport)
                ->countAllResults();

            $numPublicacion = $publicacionesAnteriores + 1;
            $fechaPublicacion = date('d/m/Y H:i');

            $tituloReporte = ($doc['codigo'] ?? '') . ' - ' . $doc['titulo']
                . ' (v' . $doc['version'] . ' - Firmado)'
                . ' - Pub. #' . $numPublicacion . ' ' . $fechaPublicacion;

            // Siempre insertar nuevo registro para mantener historial de publicaciones
            $this->db->table('tbl_reporte')->insert([
                'titulo_reporte' => $tituloReporte,
                'id_detailreport' => $idDetailReport,
                'id_report_type' => $idReportType,
                'id_cliente' => $doc['id_cliente'],
                'enlace' => $enlace,
                'estado' => 'CERRADO',
                'observaciones' => 'Publicación #' . $numPublicacion . '. Documento generado automaticamente al completar firmas electronicas. Año: ' . $doc['anio'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Guardar enlace del PDF en tbl_documentos_sst para acceso directo
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update(['updated_at' => date('Y-m-d H:i:s')]);

            // Guardar enlace en la versión vigente
            $this->db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $idDocumento)
                ->where('estado', 'vigente')
                ->update(['archivo_pdf' => $enlace]);

            log_message('info', "Documento firmado publicado en reportList: {$enlace}");

        } catch (\Exception $e) {
            log_message('error', 'Error publicando documento firmado: ' . $e->getMessage());
        }
    }

    /**
     * Crea automaticamente la version del documento cuando todas las firmas estan completas
     * Flujo: Generar → Firmar → Aprobar automatico
     */
    private function aprobarDocumentoAutomatico(int $idDocumento): void
    {
        try {
            $documento = $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->get()
                ->getRowArray();

            if (!$documento) return;

            // Verificar si ya tiene version vigente (evitar duplicados)
            $versionExistente = $this->db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $idDocumento)
                ->where('estado', 'vigente')
                ->countAllResults();

            if ($versionExistente > 0) {
                log_message('info', "Documento {$idDocumento} ya tiene version vigente, omitiendo aprobacion automatica");
                return;
            }

            // Calcular version (primera aprobacion = 1.0)
            $versionesPrevias = $this->db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $idDocumento)
                ->countAllResults();

            if ($versionesPrevias === 0) {
                $nuevaVersion = 1;
                $versionTexto = '1.0';
            } else {
                // Obtener ultima version
                $ultimaVersion = $this->db->table('tbl_doc_versiones_sst')
                    ->selectMax('version')
                    ->where('id_documento', $idDocumento)
                    ->get()
                    ->getRow();
                $nuevaVersion = ($ultimaVersion && $ultimaVersion->version) ? (int)$ultimaVersion->version + 1 : 2;
                $versionTexto = $nuevaVersion . '.0';
            }

            $this->db->transStart();

            // Marcar versiones anteriores como obsoletas
            $this->db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $idDocumento)
                ->update(['estado' => 'obsoleto']);

            // Crear nueva version
            $this->db->table('tbl_doc_versiones_sst')->insert([
                'id_documento' => $idDocumento,
                'id_cliente' => $documento['id_cliente'],
                'codigo' => $documento['codigo'] ?? null,
                'titulo' => $documento['titulo'],
                'anio' => $documento['anio'],
                'version' => $nuevaVersion,
                'version_texto' => $versionTexto,
                'tipo_cambio' => 'menor',
                'descripcion_cambio' => 'Documento aprobado automaticamente tras firma electronica',
                'contenido_snapshot' => $documento['contenido'],
                'estado' => 'vigente',
                'autorizado_por' => 'Sistema (Firma Electronica)',
                'autorizado_por_id' => null,
                'fecha_autorizacion' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Actualizar documento principal con estado 'firmado' (ya viene asi) y version
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update([
                    'version' => $nuevaVersion,
                    'fecha_aprobacion' => date('Y-m-d H:i:s'),
                    'motivo_version' => 'Aprobado tras firma electronica completa',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en transaccion de aprobacion automatica');
            }

            log_message('info', "Documento {$idDocumento} aprobado automaticamente - Version {$versionTexto}");

        } catch (\Exception $e) {
            log_message('error', 'Error en aprobacion automatica: ' . $e->getMessage());
        }
    }

    /**
     * Vista pública para firmar (acceso por token)
     */
    public function firmar($token)
    {
        $validacion = $this->firmaModel->validarToken($token);

        if (!$validacion['valido']) {
            return view('firma/error', ['error' => $validacion['error']]);
        }

        $solicitud = $validacion['solicitud'];

        // Registrar que se abrió el link
        $this->firmaModel->registrarAudit($solicitud['id_solicitud'], 'link_abierto', [
            'ip' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ]);

        $documento = $this->getDocumentoSST($solicitud['id_documento']);

        return view('firma/firmar', [
            'solicitud' => $solicitud,
            'documento' => $documento,
            'token' => $token
        ]);
    }

    /**
     * Procesar firma (POST público)
     */
    public function procesarFirma()
    {
        $token = $this->request->getPost('token');
        $validacion = $this->firmaModel->validarToken($token);

        if (!$validacion['valido']) {
            return $this->response->setJSON(['success' => false, 'error' => $validacion['error']]);
        }

        $solicitud = $validacion['solicitud'];

        // Validar aceptación de términos
        if ($this->request->getPost('acepto_terminos') !== '1') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Debe aceptar los terminos para firmar'
            ]);
        }

        // Calcular hash del documento
        $documento = $this->getDocumentoSST($solicitud['id_documento']);
        $hashDocumento = hash('sha256', $documento['contenido'] ?? json_encode($documento));

        // Preparar evidencia
        $tipoFirma = $this->request->getPost('tipo_firma') ?? 'draw';
        $firmaImagen = $this->request->getPost('firma_imagen');

        // Si es upload, procesar archivo
        if ($tipoFirma === 'upload') {
            $firmaFile = $this->request->getFile('firma_file');
            if ($firmaFile && $firmaFile->isValid()) {
                $firmaImagen = 'data:' . $firmaFile->getMimeType() . ';base64,' . base64_encode(file_get_contents($firmaFile->getTempName()));
            }
        }

        $evidencia = [
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'geolocalizacion' => $this->request->getPost('geolocalizacion'),
            'tipo_firma' => $tipoFirma === 'canvas' ? 'draw' : $tipoFirma,
            'firma_imagen' => $firmaImagen,
            'hash_documento' => $hashDocumento
        ];

        // Validar que tengamos firma
        if (empty($firmaImagen)) {
            log_message('error', 'Firma vacía para solicitud: ' . $solicitud['id_solicitud']);
            return $this->response->setJSON(['success' => false, 'error' => 'La imagen de firma está vacía']);
        }

        $resultado = $this->firmaModel->registrarFirma($solicitud['id_solicitud'], $evidencia);

        if ($resultado) {
            // Verificar si hay siguiente firmante en la cadena
            $siguienteFirmante = $this->firmaModel->getSiguienteFirmante($solicitud['id_documento']);

            if ($siguienteFirmante) {
                // Activar siguiente firmante
                $this->firmaModel->update($siguienteFirmante['id_solicitud'], [
                    'estado' => 'pendiente'
                ]);

                // Enviar correo al siguiente firmante
                $this->enviarCorreoFirma($siguienteFirmante, $documento);

                $this->firmaModel->registrarAudit($siguienteFirmante['id_solicitud'], 'email_enviado', [
                    'activado_por_firma_de' => $solicitud['firmante_nombre'],
                    'email' => $siguienteFirmante['firmante_email']
                ]);
            }

            // Verificar si todas las firmas están completas
            if ($this->firmaModel->firmasCompletas($solicitud['id_documento'])) {
                // Cambiar estado del documento a firmado
                $this->db->table('tbl_documentos_sst')
                        ->where('id_documento', $solicitud['id_documento'])
                        ->update(['estado' => 'firmado', 'updated_at' => date('Y-m-d H:i:s')]);

                $this->firmaModel->registrarAudit($solicitud['id_solicitud'], 'documento_firmado_completo', [
                    'id_documento' => $solicitud['id_documento']
                ]);

                // Notificar al consultor que todas las firmas están completas
                $this->notificarConsultorFirmasCompletas($solicitud['id_documento'], $documento);

                // Generar PDF y publicar en reportList automáticamente
                $this->publicarDocumentoFirmado($solicitud['id_documento']);

                // Crear version del documento automaticamente (aprobacion automatica)
                $this->aprobarDocumentoAutomatico($solicitud['id_documento']);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento firmado exitosamente'
            ]);
        }

        // Log para debugging
        log_message('error', 'Firma no procesada para solicitud: ' . $solicitud['id_solicitud']);

        // Intentar obtener el error de la base de datos
        $db = \Config\Database::connect();
        $dbError = $db->error();
        if (!empty($dbError['message'])) {
            log_message('error', 'DB Error: ' . $dbError['message']);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al procesar firma. Por favor intente de nuevo.']);
    }

    /**
     * Confirmación de firma exitosa
     */
    public function confirmacion($token)
    {
        $solicitud = $this->firmaModel->getByToken($token);

        if (!$solicitud || $solicitud['estado'] !== 'firmado') {
            return redirect()->to('/');
        }

        return view('firma/confirmacion', [
            'solicitud' => $solicitud
        ]);
    }

    /**
     * Estado de firmas de un documento
     */
    public function estado($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->getDocumentoSST($idDocumento);
        $cliente = $documento ? $this->clienteModel->find($documento['id_cliente']) : null;
        $solicitudes = $this->firmaModel->getByDocumento($idDocumento);
        $estadoFirmas = $this->firmaModel->getEstadoFirmas($idDocumento);

        // Obtener evidencias para firmas completadas
        $evidencias = [];
        foreach ($solicitudes as $sol) {
            if ($sol['estado'] === 'firmado') {
                $evidencias[$sol['id_solicitud']] = $this->firmaModel->getEvidencia($sol['id_solicitud']);
            }
        }

        return view('firma/estado', [
            'documento' => $documento,
            'cliente' => $cliente,
            'solicitudes' => $solicitudes,
            'estadoFirmas' => $estadoFirmas,
            'evidencias' => $evidencias
        ]);
    }

    /**
     * Reenviar solicitud de firma
     */
    public function reenviar($idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $solicitud = $this->firmaModel->find($idSolicitud);
        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada');
        }

        $nuevoToken = $this->firmaModel->reenviar($idSolicitud);

        // Obtener datos actualizados y documento
        $solicitudActualizada = $this->firmaModel->find($idSolicitud);
        $documento = $this->getDocumentoSST($solicitud['id_documento']);

        if ($documento && !empty($solicitudActualizada['firmante_email'])) {
            $this->enviarCorreoFirma($solicitudActualizada, $documento);
            $this->firmaModel->registrarAudit($idSolicitud, 'email_reenviado', [
                'email' => $solicitudActualizada['firmante_email'],
                'nuevo_token' => substr($nuevoToken, 0, 8) . '...'
            ]);
        }

        return redirect()->back()->with('success', 'Solicitud reenviada exitosamente');
    }

    /**
     * Cancelar solicitud de firma
     */
    public function cancelar($idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $solicitud = $this->firmaModel->find($idSolicitud);
        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada');
        }

        $this->firmaModel->cancelar($idSolicitud);

        // Si se cancelan todas las firmas, devolver estado del documento
        $documento = $this->getDocumentoSST($solicitud['id_documento']);
        if ($documento && $documento['estado'] === 'pendiente_firma') {
            $pendientes = $this->firmaModel
                ->where('id_documento', $solicitud['id_documento'])
                ->whereIn('estado', ['pendiente', 'esperando'])
                ->countAllResults();

            if ($pendientes === 0) {
                $this->db->table('tbl_documentos_sst')
                        ->where('id_documento', $solicitud['id_documento'])
                        ->update(['estado' => 'aprobado']);
            }
        }

        return redirect()->back()->with('success', 'Solicitud cancelada');
    }

    /**
     * Ver audit log de una solicitud
     */
    public function auditLog($idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $solicitud = $this->firmaModel->find($idSolicitud);
        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada');
        }

        $auditLog = $this->firmaModel->getAuditLog($idSolicitud);
        $evidencia = $this->firmaModel->getEvidencia($idSolicitud);
        $documento = $this->getDocumentoSST($solicitud['id_documento']);

        return view('firma/audit_log', [
            'solicitud' => $solicitud,
            'auditLog' => $auditLog,
            'evidencia' => $evidencia,
            'documento' => $documento
        ]);
    }

    /**
     * Verificar documento firmado (público)
     */
    public function verificar($codigoVerificacion)
    {
        log_message('info', "Verificar: Buscando código {$codigoVerificacion}");

        // Buscar documento por código de verificación
        // Obtener IDs únicos de documentos que tienen solicitudes firmadas
        $documentosConFirmas = $this->db->table('tbl_doc_firma_solicitudes')
            ->select('DISTINCT(id_documento) as id_documento')
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        log_message('info', "Verificar: Encontrados " . count($documentosConFirmas) . " documentos con firmas");

        $documentoEncontrado = null;
        $solicitudEncontrada = null;
        $codigoVerificacionUpper = strtoupper($codigoVerificacion);

        foreach ($documentosConFirmas as $doc) {
            $codigoCheck = $this->firmaModel->generarCodigoVerificacion($doc['id_documento']);
            log_message('info', "Verificar: Doc {$doc['id_documento']} -> Código generado: {$codigoCheck}");

            if ($codigoCheck === $codigoVerificacionUpper) {
                $documentoEncontrado = $this->getDocumentoSST($doc['id_documento']);
                $solicitudEncontrada = $this->firmaModel
                    ->where('id_documento', $doc['id_documento'])
                    ->where('estado', 'firmado')
                    ->first();
                log_message('info', "Verificar: ¡Encontrado! Doc ID: {$doc['id_documento']}");
                break;
            }
        }

        // Fallback: buscar por token directo
        if (!$documentoEncontrado) {
            log_message('info', "Verificar: No encontrado por código, buscando por token directo");
            $solicitudEncontrada = $this->firmaModel
                ->where('token', $codigoVerificacion)
                ->where('estado', 'firmado')
                ->first();

            if ($solicitudEncontrada) {
                $documentoEncontrado = $this->getDocumentoSST($solicitudEncontrada['id_documento']);
                log_message('info', "Verificar: Encontrado por token, Doc ID: {$solicitudEncontrada['id_documento']}");
            }
        }

        if (!$documentoEncontrado) {
            log_message('warning', "Verificar: Documento NO encontrado para código {$codigoVerificacion}");
            return view('firma/verificacion', ['valido' => false]);
        }

        $cliente = $this->clienteModel->find($documentoEncontrado['id_cliente']);
        $todasFirmas = $this->firmaModel->getByDocumento($documentoEncontrado['id_documento']);
        $evidencias = $this->firmaModel->getEvidenciasPorDocumento($documentoEncontrado['id_documento']);
        $codigoVerif = $this->firmaModel->generarCodigoVerificacion($documentoEncontrado['id_documento']);

        // Generar QR code
        $qrImage = $this->generarQR(base_url("firma/verificar/{$codigoVerif}"));

        return view('firma/verificacion', [
            'valido' => true,
            'documento' => $documentoEncontrado,
            'cliente' => $cliente,
            'firmas' => $todasFirmas,
            'evidencias' => $evidencias,
            'codigoVerificacion' => $codigoVerif,
            'qrImage' => $qrImage
        ]);
    }

    /**
     * Genera imagen QR como data URI
     */
    private function generarQR(string $url): string
    {
        try {
            $options = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
                'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
                'scale' => 5,
                'outputBase64' => true,
            ]);

            $qrcode = new \chillerlan\QRCode\QRCode($options);
            return $qrcode->render($url);
        } catch (\Exception $e) {
            log_message('error', 'Error generando QR: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Descargar certificado de verificación en PDF
     */
    public function certificadoPDF($idDocumento)
    {
        $documento = $this->getDocumentoSST($idDocumento);
        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $firmas = $this->firmaModel->getByDocumento($idDocumento);
        $evidencias = $this->firmaModel->getEvidenciasPorDocumento($idDocumento);
        $codigoVerif = $this->firmaModel->generarCodigoVerificacion($idDocumento);
        $qrImage = $this->generarQR(base_url("firma/verificar/{$codigoVerif}"));

        $html = view('firma/certificado_pdf', [
            'documento' => $documento,
            'cliente' => $cliente,
            'firmas' => $firmas,
            'evidencias' => $evidencias,
            'codigoVerificacion' => $codigoVerif,
            'qrImage' => $qrImage
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = "Certificado_Firma_{$documento['codigo']}_{$codigoVerif}.pdf";
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Firmar internamente (usuario del sistema)
     */
    public function firmarInterno($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $tipoFirma = $this->request->getPost('tipo_firma'); // elaboro, reviso
        $documento = $this->getDocumentoSST($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        // Crear solicitud interna
        $datos = [
            'id_documento' => $idDocumento,
            'firmante_tipo' => $tipoFirma,
            'firmante_interno_id' => session()->get('id_usuario'),
            'firmante_nombre' => session()->get('nombre') ?? 'Usuario del Sistema',
            'firmante_cargo' => session()->get('cargo') ?? 'Consultor SST'
        ];

        $idSolicitud = $this->firmaModel->crearSolicitud($datos);

        // Calcular hash del documento
        $hashDocumento = hash('sha256', $documento['contenido'] ?? json_encode($documento));

        // Firmar inmediatamente
        $evidencia = [
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'tipo_firma' => 'internal',
            'firma_imagen' => null,
            'hash_documento' => $hashDocumento
        ];

        $this->firmaModel->registrarFirma($idSolicitud, $evidencia);

        return redirect()->back()->with('success', "Documento firmado como '{$tipoFirma}'");
    }
}
