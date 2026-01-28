<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClienteContextoSstModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Responsabilidades del Representante Legal en el SG-SST"
 * Estandar 1.1.2 - Resolucion 0312/2019
 *
 * El Representante Legal tiene las siguientes responsabilidades segun Decreto 1072/2015:
 * - Definir, firmar y divulgar la politica de SST
 * - Asignar responsables del SG-SST
 * - Rendir cuentas
 * - Asignar recursos
 * - Garantizar participacion de trabajadores
 * - Etc.
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 * Firma: Digital (Representante Legal)
 */
class PzresponsabilidadesRepLegalController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'responsabilidades_rep_legal_sgsst';
    protected const NOMBRE_DOCUMENTO = 'Responsabilidades del Representante Legal en el SG-SST';
    protected const CODIGO_TIPO = 'RES';
    protected const CODIGO_TEMA = 'REP';

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Genera el documento automaticamente desde el contexto del cliente.
     */
    public function crear(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        // Verificar si ya existe
        $documentoExistente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', self::TIPO_DOCUMENTO)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if ($documentoExistente) {
            return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-rep-legal/{$anio}"));
        }

        // Obtener contexto del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return redirect()->back()->with('error', 'Debe configurar el contexto SST del cliente antes de generar este documento.');
        }

        // Obtener datos del consultor asignado
        $consultor = null;
        $consultorModel = new ConsultantModel();
        $idConsultor = $contexto['id_consultor_responsable'] ?? null;
        if ($idConsultor) {
            $consultor = $consultorModel->find($idConsultor);
        }
        if (!$consultor) {
            $consultor = $consultorModel->where('id_cliente', $idCliente)->first();
        }
        if (!$consultor && !empty($cliente['id_consultor'])) {
            $consultor = $consultorModel->find($cliente['id_consultor']);
        }

        // Construir contenido del documento
        $contenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        // Generar codigo
        $codigoDocumento = $this->generarCodigo($idCliente);

        // Insertar documento con estado pendiente de firma
        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente' => $idCliente,
            'tipo_documento' => self::TIPO_DOCUMENTO,
            'codigo' => $codigoDocumento,
            'titulo' => self::NOMBRE_DOCUMENTO,
            'anio' => $anio,
            'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'version' => 1,
            'estado' => 'pendiente_firma',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $idDocumento = $this->db->insertID();

        // Crear version inicial
        $session = session();
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $idDocumento,
            'id_cliente' => $idCliente,
            'codigo' => $codigoDocumento,
            'titulo' => self::NOMBRE_DOCUMENTO,
            'anio' => $anio,
            'version' => 1,
            'version_texto' => '1.0',
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => 'Elaboracion inicial del documento',
            'contenido_snapshot' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'estado' => 'pendiente_firma',
            'autorizado_por' => $session->get('nombre_usuario') ?? 'Sistema',
            'autorizado_por_id' => $session->get('id_usuario'),
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-rep-legal/{$anio}"))
            ->with('success', 'Documento de Responsabilidades del Representante Legal generado exitosamente.');
    }

    /**
     * Muestra la vista previa del documento
     */
    public function ver(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', self::TIPO_DOCUMENTO)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado. Genere primero las Responsabilidades del Representante Legal.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Obtener historial de versiones
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electronicas
        $firmasElectronicas = $this->obtenerFirmasElectronicas($documento['id_documento']);

        $data = [
            'titulo' => self::NOMBRE_DOCUMENTO . ' - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas
        ];

        return view('documentos_sst/responsabilidades_rep_legal', $data);
    }

    /**
     * Construye el contenido del documento desde el contexto
     */
    protected function construirContenido(array $cliente, array $contexto, ?array $consultor, int $anio): array
    {
        $nombreRepLegal = $contexto['representante_legal_nombre'] ?? $cliente['nombre_cliente'];
        $cedulaRepLegal = $contexto['representante_legal_cedula'] ?? '';
        $nombreCliente = $cliente['nombre_cliente'] ?? '';
        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);

        // Responsabilidades del Representante Legal segun Decreto 1072/2015 Art. 2.2.4.6.8
        $responsabilidades = [
            'Definir, firmar y divulgar la politica de Seguridad y Salud en el Trabajo (SST) a traves de documento escrito.',
            'Asignar y comunicar responsabilidades en SST a todos los niveles de la organizacion.',
            'Rendir cuentas a las personas que conforman la organizacion sobre el desempeno del SG-SST.',
            'Definir y asignar los recursos financieros, tecnicos y de personal para el diseno, implementacion, revision, evaluacion y mejora del SG-SST.',
            'Garantizar la consulta y participacion de los trabajadores en la identificacion de peligros y control de riesgos.',
            'Garantizar la capacitacion de los trabajadores en aspectos de SST.',
            'Garantizar la disponibilidad de personal competente para liderar el SG-SST.',
            'Direccionar el SG-SST garantizando el cumplimiento de la normatividad vigente.',
            'Integrar los aspectos de SST al conjunto de sistemas de gestion de la organizacion.',
            'Adoptar medidas eficaces para identificar peligros, evaluar y valorar riesgos.',
        ];

        // Agregar responsabilidades adicionales segun estandares
        if ($estandares >= 21) {
            $responsabilidades[] = 'Garantizar el funcionamiento del Comite Paritario de Seguridad y Salud en el Trabajo (COPASST).';
            $responsabilidades[] = 'Garantizar el funcionamiento del Comite de Convivencia Laboral.';
        } else {
            $responsabilidades[] = 'Garantizar el funcionamiento del Vigia de Seguridad y Salud en el Trabajo.';
        }

        $responsabilidades[] = 'Realizar la evaluacion anual del SG-SST.';
        $responsabilidades[] = 'Implementar acciones correctivas, preventivas y de mejora segun los resultados de la evaluacion.';
        $responsabilidades[] = 'Investigar todos los accidentes e incidentes de trabajo.';

        return [
            'secciones' => [
                [
                    'key' => 'objeto',
                    'titulo' => '1. OBJETO',
                    'contenido' => "Establecer las responsabilidades del Representante Legal de <strong>{$nombreCliente}</strong> en el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.",
                    'aprobado' => true
                ],
                [
                    'key' => 'alcance',
                    'titulo' => '2. ALCANCE',
                    'contenido' => "Este documento aplica al Representante Legal de <strong>{$nombreCliente}</strong> y establece sus responsabilidades especificas en materia de Seguridad y Salud en el Trabajo.",
                    'aprobado' => true
                ],
                [
                    'key' => 'responsabilidades',
                    'titulo' => '3. RESPONSABILIDADES',
                    'contenido' => $this->formatearResponsabilidades($responsabilidades),
                    'aprobado' => true
                ],
                [
                    'key' => 'compromiso',
                    'titulo' => '4. COMPROMISO',
                    'contenido' => "Yo, <strong>{$nombreRepLegal}</strong>, identificado(a) con documento de identidad <strong>{$cedulaRepLegal}</strong>, en calidad de Representante Legal de <strong>{$nombreCliente}</strong>, declaro conocer y asumir las responsabilidades descritas en el presente documento, comprometiendome a su cumplimiento efectivo.",
                    'aprobado' => true
                ],
            ],
            'empresa' => [
                'nombre' => $nombreCliente,
                'nit' => $cliente['nit_cliente'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => $estandares,
            'representante_legal' => [
                'nombre' => $nombreRepLegal,
                'cedula' => $cedulaRepLegal
            ],
            'responsabilidades_lista' => $responsabilidades,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Formatea la lista de responsabilidades como HTML
     */
    protected function formatearResponsabilidades(array $responsabilidades): string
    {
        $html = '<p>El Representante Legal tiene las siguientes responsabilidades:</p><ol style="line-height: 1.8;">';
        foreach ($responsabilidades as $resp) {
            $html .= '<li style="margin-bottom: 8px;">' . $resp . '</li>';
        }
        $html .= '</ol>';
        return $html;
    }

    /**
     * Genera codigo unico usando stored procedure
     */
    protected function generarCodigo(int $idCliente): string
    {
        $query = $this->db->query(
            "CALL sp_generar_codigo_documento(?, ?, ?, @codigo)",
            [$idCliente, self::CODIGO_TIPO, self::CODIGO_TEMA]
        );
        $query->getResult();

        if (method_exists($query, 'freeResult')) {
            $query->freeResult();
        }
        while ($this->db->connID->next_result()) {
            $this->db->connID->store_result();
        }

        $result = $this->db->query("SELECT @codigo as codigo")->getRow();
        return $result->codigo ?? (self::CODIGO_TIPO . '-' . self::CODIGO_TEMA . '-001');
    }

    /**
     * Regenera el documento con datos actualizados
     */
    public function regenerar(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', self::TIPO_DOCUMENTO)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado']);
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contexto SST no configurado']);
        }

        // Actualizar contexto si hay cambios
        $nuevoRepLegalNombre = $this->request->getPost('representante_legal_nombre');
        $nuevoRepLegalCedula = $this->request->getPost('representante_legal_cedula');

        $datosActualizar = [];
        if ($nuevoRepLegalNombre && $nuevoRepLegalNombre !== ($contexto['representante_legal_nombre'] ?? '')) {
            $datosActualizar['representante_legal_nombre'] = $nuevoRepLegalNombre;
        }
        if ($nuevoRepLegalCedula !== null && $nuevoRepLegalCedula !== ($contexto['representante_legal_cedula'] ?? '')) {
            $datosActualizar['representante_legal_cedula'] = $nuevoRepLegalCedula;
        }

        if (!empty($datosActualizar)) {
            $datosActualizar['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('tbl_cliente_contexto_sst')
                ->where('id_cliente', $idCliente)
                ->update($datosActualizar);
            $contexto = $contextoModel->getByCliente($idCliente);
        }

        // Obtener consultor
        $consultor = null;
        $consultorModel = new ConsultantModel();
        $idConsultor = $contexto['id_consultor_responsable'] ?? null;
        if ($idConsultor) {
            $consultor = $consultorModel->find($idConsultor);
        }
        if (!$consultor) {
            $consultor = $consultorModel->where('id_cliente', $idCliente)->first();
        }

        $nuevoContenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion de datos del representante legal';

        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $documento['id_documento'])
            ->update([
                'contenido' => json_encode($nuevoContenido, JSON_UNESCAPED_UNICODE),
                'version' => $nuevaVersion,
                'estado' => 'pendiente_firma',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'vigente')
            ->update(['estado' => 'historico']);

        $session = session();
        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $documento['id_documento'],
            'id_cliente' => $idCliente,
            'codigo' => $documento['codigo'],
            'titulo' => self::NOMBRE_DOCUMENTO,
            'anio' => $anio,
            'version' => $nuevaVersion,
            'version_texto' => $versionTexto,
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => $descripcionCambio,
            'contenido_snapshot' => json_encode($nuevoContenido, JSON_UNESCAPED_UNICODE),
            'estado' => 'pendiente_firma',
            'autorizado_por' => $session->get('nombre_usuario') ?? 'Sistema',
            'autorizado_por_id' => $session->get('id_usuario'),
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado !=', 'firmado')
            ->update(['estado' => 'cancelada']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento actualizado a version ' . $versionTexto . '. Pendiente de firma.',
            'nueva_version' => $versionTexto
        ]);
    }

    /**
     * Obtiene las firmas electronicas del documento
     */
    protected function obtenerFirmasElectronicas(int $idDocumento): array
    {
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

        return $firmasElectronicas;
    }
}
