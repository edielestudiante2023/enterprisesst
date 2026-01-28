<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClienteContextoSstModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Responsabilidades de Trabajadores y Contratistas en el SG-SST"
 * Estandar 1.1.2 - Resolucion 0312/2019
 *
 * Los trabajadores tienen responsabilidades especificas segun Decreto 1072/2015
 * Art. 2.2.4.6.10 (Responsabilidades de los Trabajadores)
 *
 * FORMATO ESPECIAL: Este documento se imprime y se firma fisicamente
 * durante la induccion de los trabajadores. Incluye una pagina separada
 * con tabla de firmas para multiples trabajadores.
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 */
class PzresponsabilidadesTrabajadoresController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'responsabilidades_trabajadores_sgsst';
    protected const NOMBRE_DOCUMENTO = 'Responsabilidades de Trabajadores y Contratistas en el SG-SST';
    protected const CODIGO_TIPO = 'RES';
    protected const CODIGO_TEMA = 'TRA';

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
            return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-trabajadores/{$anio}"));
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

        // Construir contenido del documento
        $contenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        // Generar codigo
        $codigoDocumento = $this->generarCodigo($idCliente);

        // Insertar documento con estado aprobado (documento para imprimir)
        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente' => $idCliente,
            'tipo_documento' => self::TIPO_DOCUMENTO,
            'codigo' => $codigoDocumento,
            'titulo' => self::NOMBRE_DOCUMENTO,
            'anio' => $anio,
            'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'version' => 1,
            'estado' => 'aprobado', // Documento para imprimir, no requiere firma electronica
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

        return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-trabajadores/{$anio}"))
            ->with('success', 'Documento de Responsabilidades de Trabajadores generado exitosamente.');
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
            return redirect()->back()->with('error', 'Documento no encontrado. Genere primero las Responsabilidades de Trabajadores.');
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

        return view('documentos_sst/responsabilidades_trabajadores', $data);
    }

    /**
     * Construye el contenido del documento desde el contexto
     */
    protected function construirContenido(array $cliente, array $contexto, ?array $consultor, int $anio): array
    {
        $nombreCliente = $cliente['nombre_cliente'] ?? '';
        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);

        // Responsabilidades de los Trabajadores segun Decreto 1072/2015 Art. 2.2.4.6.10
        $responsabilidades = [
            'Procurar el cuidado integral de su salud.',
            'Suministrar informacion clara, veraz y completa sobre su estado de salud.',
            'Cumplir las normas, reglamentos e instrucciones del SG-SST de la empresa.',
            'Informar oportunamente al empleador acerca de los peligros y riesgos latentes en su sitio de trabajo.',
            'Participar en las actividades de capacitacion en Seguridad y Salud en el Trabajo definidas en el plan de capacitacion.',
            'Participar y contribuir al cumplimiento de los objetivos del SG-SST.',
            'Reportar inmediatamente todo accidente de trabajo o incidente.',
            'Usar adecuadamente los equipos de proteccion personal (EPP) suministrados por la empresa.',
            'Conocer y cumplir la politica de seguridad y salud en el trabajo.',
            'Participar en la identificacion de peligros, evaluacion y valoracion de riesgos.',
        ];

        // Agregar responsabilidades adicionales segun tipo de empresa
        if ($estandares >= 21) {
            $responsabilidades[] = 'Participar en las actividades del COPASST cuando sea convocado.';
            $responsabilidades[] = 'Elegir libremente sus representantes ante el COPASST.';
        } else {
            $responsabilidades[] = 'Colaborar con el Vigia de Seguridad y Salud en el Trabajo.';
        }

        $responsabilidades[] = 'Asistir a las capacitaciones programadas por la empresa en temas de SST.';
        $responsabilidades[] = 'Cumplir con las medidas de prevencion y proteccion establecidas.';
        $responsabilidades[] = 'Participar en los simulacros de emergencia programados.';

        // Calcular filas de firma segun estandares
        $filasDesFirma = $estandares >= 60 ? 20 : 15;

        return [
            'secciones' => [
                [
                    'key' => 'objeto',
                    'titulo' => '1. OBJETO',
                    'contenido' => "Establecer las responsabilidades de los trabajadores y contratistas de <strong>{$nombreCliente}</strong> en el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.",
                    'aprobado' => true
                ],
                [
                    'key' => 'alcance',
                    'titulo' => '2. ALCANCE',
                    'contenido' => "Este documento aplica a todos los trabajadores directos, contratistas y subcontratistas que realicen actividades para <strong>{$nombreCliente}</strong>, independientemente de su forma de contratacion.",
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
                    'contenido' => "Los trabajadores y contratistas abajo firmantes declaran conocer y asumir las responsabilidades descritas en el presente documento, comprometiendose a su cumplimiento efectivo. La firma del presente documento se realiza durante el proceso de induccion en SST.",
                    'aprobado' => true
                ],
            ],
            'empresa' => [
                'nombre' => $nombreCliente,
                'nit' => $cliente['nit_cliente'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => $estandares,
            'responsabilidades_lista' => $responsabilidades,
            'filas_firma' => $filasDesFirma,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'tipo_firma' => 'fisica', // Indicador de que requiere firma fisica
            'instrucciones_uso' => 'Este documento debe imprimirse y firmarse durante la induccion de cada trabajador.'
        ];
    }

    /**
     * Formatea la lista de responsabilidades como HTML
     */
    protected function formatearResponsabilidades(array $responsabilidades): string
    {
        $html = '<p>Todo trabajador, contratista o subcontratista tiene las siguientes responsabilidades:</p><ol style="line-height: 1.8;">';
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

        // Obtener consultor
        $consultor = null;
        $consultorModel = new ConsultantModel();
        $idConsultor = $contexto['id_consultor_responsable'] ?? null;
        if ($idConsultor) {
            $consultor = $consultorModel->find($idConsultor);
        }

        $nuevoContenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);

        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion del documento';

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
