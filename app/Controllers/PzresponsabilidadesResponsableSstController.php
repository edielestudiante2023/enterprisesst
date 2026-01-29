<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClienteContextoSstModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Responsabilidades del Responsable del SG-SST"
 * Estandar 1.1.2 - Resolucion 0312/2019
 *
 * El Responsable del SG-SST (Consultor) tiene responsabilidades especificas
 * segun Decreto 1072/2015 Art. 2.2.4.6.8 y Resolucion 0312/2019
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 * Firma: Consultor SST (firma automatica desde su perfil)
 */
class PzresponsabilidadesResponsableSstController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'responsabilidades_responsable_sgsst';
    protected const NOMBRE_DOCUMENTO = 'Responsabilidades del Responsable del SG-SST';
    protected const CODIGO_TIPO = 'RES';
    protected const CODIGO_TEMA = 'SST';

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
            return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-responsable-sst/{$anio}"));
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

        if (!$consultor) {
            return redirect()->back()->with('error', 'Debe asignar un consultor/responsable SST antes de generar este documento.');
        }

        // Construir contenido del documento
        $contenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        // Generar codigo
        $codigoDocumento = $this->generarCodigo($idCliente);

        // Insertar documento con estado aprobado (firma del consultor ya existe en perfil)
        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente' => $idCliente,
            'tipo_documento' => self::TIPO_DOCUMENTO,
            'codigo' => $codigoDocumento,
            'titulo' => self::NOMBRE_DOCUMENTO,
            'anio' => $anio,
            'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'version' => 1,
            'estado' => 'aprobado', // Aprobado porque usa firma existente del consultor
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
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
            'estado' => 'vigente',
            'autorizado_por' => $session->get('nombre_usuario') ?? 'Sistema',
            'autorizado_por_id' => $session->get('id_usuario'),
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-responsable-sst/{$anio}"))
            ->with('success', 'Documento de Responsabilidades del Responsable SG-SST generado exitosamente.');
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
            return redirect()->back()->with('error', 'Documento no encontrado. Genere primero las Responsabilidades del Responsable SG-SST.');
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

        $data = [
            'titulo' => self::NOMBRE_DOCUMENTO . ' - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor
        ];

        return view('documentos_sst/responsabilidades_responsable_sst', $data);
    }

    /**
     * Construye el contenido del documento desde el contexto
     */
    protected function construirContenido(array $cliente, array $contexto, ?array $consultor, int $anio): array
    {
        $nombreCliente = $cliente['nombre_cliente'] ?? '';
        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);

        $nombreConsultor = $consultor['nombre_consultor'] ?? '';
        $cedulaConsultor = $consultor['cedula_consultor'] ?? '';
        $licenciaConsultor = $consultor['numero_licencia'] ?? '';

        // Responsabilidades del Responsable del SG-SST segun Decreto 1072/2015
        $responsabilidades = [
            'Planificar, organizar, dirigir, desarrollar y aplicar el SG-SST y como minimo una vez al ano realizar su evaluacion.',
            'Informar a la alta direccion sobre el funcionamiento y los resultados del SG-SST.',
            'Promover la participacion de todos los miembros de la organizacion en la implementacion del SG-SST.',
            'Coordinar con los jefes de las areas, la elaboracion y actualizacion de la matriz de identificacion de peligros, evaluacion y valoracion de riesgos.',
            'Validar o construir con los jefes de las areas, los planes de accion y hacer seguimiento a su cumplimiento.',
            'Promover la comprension de la politica en todos los niveles de la organizacion.',
            'Gestionar los recursos para cumplir con el plan de trabajo en SST y hacer seguimiento a los indicadores.',
            'Coordinar las necesidades de capacitacion en materia de prevencion segun los riesgos prioritarios.',
            'Apoyar la investigacion de los incidentes, accidentes de trabajo y enfermedades laborales.',
            'Participar de las reuniones del Comite de Seguridad y Salud en el Trabajo.',
        ];

        // Agregar responsabilidades adicionales segun estandares
        if ($estandares >= 21) {
            $responsabilidades[] = 'Coordinar el funcionamiento del COPASST y el Comite de Convivencia Laboral.';
            $responsabilidades[] = 'Gestionar el cumplimiento de los estandares minimos de la Resolucion 0312/2019.';
        } else {
            $responsabilidades[] = 'Coordinar el funcionamiento del Vigia de Seguridad y Salud en el Trabajo.';
        }

        $responsabilidades[] = 'Implementar y mantener las disposiciones necesarias en materia de prevencion, preparacion y respuesta ante emergencias.';
        $responsabilidades[] = 'Orientar a la alta gerencia y trabajadores sobre el cumplimiento normativo en SST.';
        $responsabilidades[] = 'Mantener actualizada la documentacion del SG-SST.';

        return [
            'secciones' => [
                [
                    'key' => 'objeto',
                    'titulo' => '1. OBJETO',
                    'contenido' => "Establecer las responsabilidades del Responsable del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) de <strong>{$nombreCliente}</strong>, de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.",
                    'aprobado' => true
                ],
                [
                    'key' => 'alcance',
                    'titulo' => '2. ALCANCE',
                    'contenido' => "Este documento aplica al profesional designado como Responsable del SG-SST de <strong>{$nombreCliente}</strong> y establece sus responsabilidades especificas en materia de Seguridad y Salud en el Trabajo.",
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
                    'contenido' => "Yo, <strong>{$nombreConsultor}</strong>, identificado(a) con documento de identidad <strong>{$cedulaConsultor}</strong>, con licencia de SST No. <strong>{$licenciaConsultor}</strong>, en calidad de Responsable del SG-SST de <strong>{$nombreCliente}</strong>, declaro conocer y asumir las responsabilidades descritas en el presente documento, comprometiendome a su cumplimiento efectivo.",
                    'aprobado' => true
                ],
            ],
            'empresa' => [
                'nombre' => $nombreCliente,
                'nit' => $cliente['nit_cliente'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => $estandares,
            'responsable_sst' => [
                'nombre' => $nombreConsultor,
                'cedula' => $cedulaConsultor,
                'licencia' => $licenciaConsultor
            ],
            'responsabilidades_lista' => $responsabilidades,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            // Indicador para templates: este documento solo lleva firma del consultor
            'solo_firma_consultor' => true
        ];
    }

    /**
     * Formatea la lista de responsabilidades como HTML
     */
    protected function formatearResponsabilidades(array $responsabilidades): string
    {
        $html = '<p>El Responsable del SG-SST tiene las siguientes responsabilidades:</p><ol style="line-height: 1.8;">';
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

        // Actualizar consultor si hay cambios
        $nuevoIdConsultor = $this->request->getPost('id_consultor_responsable');
        if ($nuevoIdConsultor && $nuevoIdConsultor !== ($contexto['id_consultor_responsable'] ?? '')) {
            $this->db->table('tbl_cliente_contexto_sst')
                ->where('id_cliente', $idCliente)
                ->update([
                    'id_consultor_responsable' => $nuevoIdConsultor,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
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

        if (!$consultor) {
            return $this->response->setJSON(['success' => false, 'message' => 'Debe asignar un consultor']);
        }

        $nuevoContenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion de datos del responsable SST';

        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $documento['id_documento'])
            ->update([
                'contenido' => json_encode($nuevoContenido, JSON_UNESCAPED_UNICODE),
                'version' => $nuevaVersion,
                'estado' => 'aprobado',
                'fecha_aprobacion' => date('Y-m-d H:i:s'),
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
            'estado' => 'vigente',
            'autorizado_por' => $session->get('nombre_usuario') ?? 'Sistema',
            'autorizado_por_id' => $session->get('id_usuario'),
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento actualizado a version ' . $versionTexto,
            'nueva_version' => $versionTexto
        ]);
    }
}
