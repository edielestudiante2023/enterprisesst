<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClienteContextoSstModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Asignacion de Responsable del SG-SST"
 * Estandar 1.1.1 - Resolucion 0312/2019
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 */
class PzasignacionresponsableSstController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'asignacion_responsable_sgsst';
    protected const NOMBRE_DOCUMENTO = 'Asignacion de Responsable del SG-SST';
    protected const CODIGO_TIPO = 'ASG';
    protected const CODIGO_TEMA = 'RES';

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Genera el documento automaticamente desde el contexto del cliente.
     * No usa IA ni editor de secciones.
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
            return redirect()->to(base_url("documentos-sst/{$idCliente}/asignacion-responsable-sst/{$anio}"));
        }

        // Obtener contexto del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return redirect()->back()->with('error', 'Debe configurar el contexto SST del cliente antes de generar este documento.');
        }

        // Obtener datos del consultor asignado (responsable SST)
        $consultor = null;
        $consultorModel = new ConsultantModel();

        // Primero intentar con id_consultor_responsable del contexto
        $idConsultor = $contexto['id_consultor_responsable'] ?? null;
        if ($idConsultor) {
            $consultor = $consultorModel->find($idConsultor);
        }

        // Si no hay, buscar consultor asignado al cliente en tbl_consultor
        if (!$consultor) {
            $consultor = $consultorModel->where('id_cliente', $idCliente)->first();
        }

        // Si aún no hay, intentar con id_consultor del cliente
        if (!$consultor && !empty($cliente['id_consultor'])) {
            $consultor = $consultorModel->find($cliente['id_consultor']);
        }

        // Construir contenido del documento
        $contenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        // Generar codigo
        $codigoDocumento = $this->generarCodigo($idCliente);

        // Insertar documento con estado aprobado (no requiere edicion)
        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente' => $idCliente,
            'tipo_documento' => self::TIPO_DOCUMENTO,
            'codigo' => $codigoDocumento,
            'titulo' => self::NOMBRE_DOCUMENTO,
            'anio' => $anio,
            'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'version' => 1,
            'estado' => 'aprobado',
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

        return redirect()->to(base_url("documentos-sst/{$idCliente}/asignacion-responsable-sst/{$anio}"))
            ->with('success', 'Documento de Asignacion de Responsable generado exitosamente.');
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
            return redirect()->back()->with('error', 'Documento no encontrado. Genere primero la Asignacion de Responsable.');
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

        // Lista de consultores para el modal de actualizar datos
        $consultorModelLista = new ConsultantModel();
        $listaConsultores = $consultorModelLista->orderBy('nombre_consultor', 'ASC')->findAll();

        $data = [
            'titulo' => self::NOMBRE_DOCUMENTO . ' - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas,
            'listaConsultores' => $listaConsultores
        ];

        return view('documentos_sst/asignacion_responsable', $data);
    }

    /**
     * Construye el contenido del documento desde el contexto
     */
    protected function construirContenido(array $cliente, array $contexto, ?array $consultor, int $anio): array
    {
        // Datos del representante legal desde contexto
        $nombreRepLegal = $contexto['representante_legal_nombre'] ?? $cliente['nombre_cliente'];
        $cedulaRepLegal = $contexto['representante_legal_cedula'] ?? '';

        // Datos de la empresa
        $nombreCliente = $cliente['nombre_cliente'] ?? '';

        // Datos del consultor/responsable SST (campos de tbl_consultor)
        $nombreConsultor = $consultor['nombre_consultor'] ?? '';
        $cedulaConsultor = $consultor['cedula_consultor'] ?? '';
        $licenciaConsultor = $consultor['numero_licencia'] ?? '';  // Campo correcto: numero_licencia

        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);
        $empresaConsultora = 'CYCLOID TALENT S.A.S';

        // Texto con datos en negrita usando HTML
        // Nota: numero_licencia ya incluye fecha (ej: "4241 de 19/08/2022")
        $textoAsignacion = "<strong>{$nombreRepLegal}</strong> con documento de identidad <strong>{$cedulaRepLegal}</strong> como representante legal de <strong>{$nombreCliente}</strong>, "
            . "nombro a la empresa <strong>{$empresaConsultora}</strong>, y esta a su vez asignando como responsable al profesional en Seguridad y Salud en el Trabajo, "
            . "<strong>{$nombreConsultor}</strong> con documento de identidad <strong>{$cedulaConsultor}</strong>, con numero de licencia <strong>{$licenciaConsultor}</strong>"
            . ", a quien se le confia la responsabilidad de asesorar en la administracion e implementacion del Sistema de Gestion "
            . "de Seguridad y Salud en el Trabajo (SG-SST) en la propiedad horizontal.";

        $textoAlcance = "Esto incluye la orientacion en la planificacion, organizacion y direccion de las evaluaciones del sistema, "
            . "la presentacion de informes detallados sobre su desempeno y resultados al consejo de administracion, administrador y asamblea de propietarios, "
            . "asi como la provision de lineamientos para su actualizacion continua conforme a la normatividad vigente.";

        return [
            'secciones' => [
                [
                    'key' => 'asignacion_designacion',
                    'titulo' => '1. ASIGNACION Y DESIGNACION',
                    'contenido' => $textoAsignacion,
                    'aprobado' => true
                ],
                [
                    'key' => 'alcance_responsabilidades',
                    'titulo' => '2. ALCANCE DE RESPONSABILIDADES',
                    'contenido' => $textoAlcance,
                    'aprobado' => true
                ],
                [
                    'key' => 'vigencia',
                    'titulo' => '3. VIGENCIA',
                    'contenido' => "El presente documento tiene vigencia a partir de su fecha de expedicion y permanecera vigente mientras se mantenga la relacion contractual.",
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
            'responsable_sst' => [
                'nombre' => $nombreConsultor,
                'cedula' => $cedulaConsultor,
                'licencia' => $licenciaConsultor
            ]
        ];
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

        // Liberar resultados del SP para evitar errores de "commands out of sync"
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
     * Regenera el documento con datos actualizados del contexto/consultor.
     * Crea una nueva version del documento.
     * Permite editar representante legal y consultor directamente desde el modal.
     */
    public function regenerar(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        // Obtener documento existente
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', self::TIPO_DOCUMENTO)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado']);
        }

        // Obtener contexto actual
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contexto SST no configurado']);
        }

        // Actualizar contexto con datos editados del formulario
        $nuevoRepLegalNombre = $this->request->getPost('representante_legal_nombre');
        $nuevoRepLegalCedula = $this->request->getPost('representante_legal_cedula');
        $nuevoIdConsultor = $this->request->getPost('id_consultor_responsable');

        // Si hay cambios, actualizar tbl_cliente_contexto_sst
        $datosActualizar = [];
        if ($nuevoRepLegalNombre && $nuevoRepLegalNombre !== ($contexto['representante_legal_nombre'] ?? '')) {
            $datosActualizar['representante_legal_nombre'] = $nuevoRepLegalNombre;
        }
        if ($nuevoRepLegalCedula !== null && $nuevoRepLegalCedula !== ($contexto['representante_legal_cedula'] ?? '')) {
            $datosActualizar['representante_legal_cedula'] = $nuevoRepLegalCedula;
        }
        if ($nuevoIdConsultor && $nuevoIdConsultor !== ($contexto['id_consultor_responsable'] ?? '')) {
            $datosActualizar['id_consultor_responsable'] = $nuevoIdConsultor;
        }

        // Aplicar actualizaciones al contexto
        if (!empty($datosActualizar)) {
            $datosActualizar['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('tbl_cliente_contexto_sst')
                ->where('id_cliente', $idCliente)
                ->update($datosActualizar);

            // Recargar contexto actualizado
            $contexto = $contextoModel->getByCliente($idCliente);
        }

        // Obtener consultor actualizado
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

        // Construir nuevo contenido con datos actualizados
        $nuevoContenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        // Obtener version actual y calcular nueva
        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        // Obtener descripcion del cambio del request
        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion de datos de representante legal y/o consultor SST';

        // Actualizar documento principal - queda pendiente de firma
        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $documento['id_documento'])
            ->update([
                'contenido' => json_encode($nuevoContenido, JSON_UNESCAPED_UNICODE),
                'version' => $nuevaVersion,
                'estado' => 'pendiente_firma',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Marcar version anterior como historico
        $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'vigente')
            ->update(['estado' => 'historico']);

        // Crear nueva version en estado pendiente_firma
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

        // Invalidar firmas anteriores (el documento cambio)
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
