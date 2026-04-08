<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Certificacion de No Alto Riesgo"
 * Estandar 1.1.5 - Resolucion 0312/2019
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 * La empresa certifica que no realiza actividades de alto riesgo segun Decreto 2090/2003
 */
class PzcertificacionAltoRiesgoController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'certificacion_no_alto_riesgo';
    protected const NOMBRE_DOCUMENTO = 'Certificacion de No Alto Riesgo';
    protected const CODIGO_TIPO = 'CRT';
    protected const CODIGO_TEMA = 'AR';

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
            return redirect()->to(base_url("documentos-sst/{$idCliente}/certificacion-alto-riesgo/{$anio}"));
        }

        // Obtener contexto del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return redirect()->back()->with('error', 'Debe configurar el contexto SST del cliente antes de generar este documento.');
        }

        // Construir contenido del documento
        $contenido = $this->construirContenido($cliente, $contexto, $anio);

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

        return redirect()->to(base_url("documentos-sst/{$idCliente}/certificacion-alto-riesgo/{$anio}"))
            ->with('success', 'Certificacion de No Alto Riesgo generada exitosamente.');
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
            return redirect()->back()->with('error', 'Documento no encontrado. Genere primero la Certificacion de No Alto Riesgo.');
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

        // Obtener firmas electronicas
        $firmasElectronicas = $this->obtenerFirmasElectronicas($documento['id_documento']);

        // Datos del Delegado SST
        $delegadoNombre = trim($contexto['delegado_sst_nombre'] ?? '');
        $delegadoCargo = trim($contexto['delegado_sst_cargo'] ?? 'Responsable del SG-SST');
        $requiereDelegado = !empty($delegadoNombre);

        $data = [
            'titulo' => self::NOMBRE_DOCUMENTO . ' - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'firmasElectronicas' => $firmasElectronicas,
            'delegadoNombre' => $delegadoNombre,
            'delegadoCargo' => $delegadoCargo,
            'requiereDelegado' => $requiereDelegado
        ];

        return view('documentos_sst/certificacion_alto_riesgo', $data);
    }

    /**
     * Construye el contenido del documento desde el contexto
     */
    protected function construirContenido(array $cliente, array $contexto, int $anio): array
    {
        $nombreCliente = $cliente['nombre_cliente'] ?? '';
        $nitCliente = $cliente['nit_cliente'] ?? '';
        $direccionCliente = $cliente['direccion_cliente'] ?? $contexto['direccion'] ?? '';

        $nombreRepLegal = $contexto['representante_legal_nombre'] ?? $cliente['nombre_cliente'];
        $cedulaRepLegal = $contexto['representante_legal_cedula'] ?? '';

        // Fecha formateada
        setlocale(LC_TIME, 'es_CO.UTF-8', 'es_ES.UTF-8', 'spanish');
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $mes = $meses[(int)date('m') - 1];
        $fechaTexto = "Bogota, " . date('d') . " de {$mes} de " . date('Y');

        $textoCertificacion = "Por medio de la presente, nos permitimos informar que, conforme al numeral 1.1.5 – "
            . "Identificacion de trabajadores de alto riesgo y cotizacion de pension especial, establecido en la "
            . "normatividad vigente del Sistema General de Pensiones, la empresa <strong>{$nombreCliente}</strong> "
            . "manifiesta que no realiza ninguna actividad catalogada como de alto riesgo, segun lo establecido en el "
            . "Decreto 2090 de 2003 y demas disposiciones aplicables.";

        $textoComplemento = "En ese sentido, actualmente no se cuenta con trabajadores vinculados a actividades "
            . "consideradas como de alto riesgo para efectos de cotizacion a pension especial.";

        $textoCierre = "Esta certificacion se expide a solicitud de parte interesada y para los fines que estime convenientes.";

        return [
            'secciones' => [
                [
                    'key' => 'encabezado',
                    'titulo' => '',
                    'contenido' => $fechaTexto . "\nA quien corresponda:",
                    'aprobado' => true
                ],
                [
                    'key' => 'certificacion',
                    'titulo' => '',
                    'contenido' => $textoCertificacion,
                    'aprobado' => true
                ],
                [
                    'key' => 'complemento',
                    'titulo' => '',
                    'contenido' => $textoComplemento,
                    'aprobado' => true
                ],
                [
                    'key' => 'cierre',
                    'titulo' => '',
                    'contenido' => $textoCierre,
                    'aprobado' => true
                ],
            ],
            'empresa' => [
                'nombre' => $nombreCliente,
                'nit' => $nitCliente,
                'direccion' => $direccionCliente
            ],
            'vigencia' => $anio,
            'fecha_expedicion' => $fechaTexto,
            'representante_legal' => [
                'nombre' => $nombreRepLegal,
                'cedula' => $cedulaRepLegal
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
     * Regenera el documento con datos actualizados del contexto.
     * Crea una nueva version del documento.
     * Permite editar representante legal directamente desde el modal.
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

        // Obtener contexto actual
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contexto SST no configurado']);
        }

        // Actualizar contexto con datos editados del formulario
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

        // Construir nuevo contenido con datos actualizados
        $nuevoContenido = $this->construirContenido($cliente, $contexto, $anio);

        // Obtener version actual y calcular nueva
        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion de datos de representante legal';

        // Actualizar documento principal
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

        // Crear nueva version
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

        // Invalidar firmas anteriores
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
    protected function obtenerFirmasElectronicas(int $idDocumento, array $contexto = [], array $cliente = []): array
    {
        $firmaModel = new \App\Models\DocFirmaModel();
        return $firmaModel->obtenerFirmasElectronicasValidadas($idDocumento, $contexto, $cliente);
    }
}
