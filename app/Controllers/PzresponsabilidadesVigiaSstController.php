<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClienteContextoSstModel;
use App\Models\ResponsableSSTModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Responsabilidades del Vigia de SST"
 * Estandar 1.1.2 - Resolucion 0312/2019
 *
 * IMPORTANTE: Este documento SOLO aplica para empresas con 7 estandares
 * (menos de 10 trabajadores, riesgo I, II o III)
 *
 * Para empresas con 21 o 60 estandares se usa COPASST, no Vigia
 *
 * El Vigia de SST tiene responsabilidades especificas segun:
 * - Decreto 1072/2015 Art. 2.2.4.1.6
 * - Resolucion 0312/2019 Art. 5
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 * Firma: Digital (Vigia SST)
 */
class PzresponsabilidadesVigiaSstController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'responsabilidades_vigia_sgsst';
    protected const NOMBRE_DOCUMENTO = 'Responsabilidades del Vigia de Seguridad y Salud en el Trabajo';
    protected const CODIGO_TIPO = 'RES';
    protected const CODIGO_TEMA = 'VIG';

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Genera el documento automaticamente desde el contexto del cliente.
     * SOLO para empresas con 7 estandares
     */
    public function crear(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Verificar contexto y estandares
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return redirect()->back()->with('error', 'Debe configurar el contexto SST del cliente antes de generar este documento.');
        }

        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);

        // IMPORTANTE: Solo aplica para 7 estandares
        if ($estandares > 7) {
            return redirect()->back()->with('error', 'Este documento solo aplica para empresas con 7 estandares. Para empresas con ' . $estandares . ' estandares, el Vigia de SST no aplica; se debe conformar el COPASST.');
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
            return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-vigia-sst/{$anio}"));
        }

        // Obtener datos del Vigia desde responsables_sst
        $vigia = $this->obtenerVigia($idCliente);
        if (!$vigia) {
            return redirect()->back()->with('error', 'Debe registrar primero el Vigia de SST en el modulo de Responsables SST.');
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
        $contenido = $this->construirContenido($cliente, $contexto, $vigia, $consultor, $anio);

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

        return redirect()->to(base_url("documentos-sst/{$idCliente}/responsabilidades-vigia-sst/{$anio}"))
            ->with('success', 'Documento de Responsabilidades del Vigia SST generado exitosamente.');
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
            return redirect()->back()->with('error', 'Documento no encontrado. Genere primero las Responsabilidades del Vigia SST.');
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

        // Obtener vigia actual
        $vigia = $this->obtenerVigia($idCliente);

        $data = [
            'titulo' => self::NOMBRE_DOCUMENTO . ' - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'vigia' => $vigia,
            'firmasElectronicas' => $firmasElectronicas
        ];

        return view('documentos_sst/responsabilidades_vigia_sst', $data);
    }

    /**
     * Obtiene el Vigia SST del cliente
     */
    protected function obtenerVigia(int $idCliente): ?array
    {
        // Buscar en tbl_responsables_sst con rol de Vigia
        $vigia = $this->db->table('tbl_responsables_sst r')
            ->join('tbl_roles_sst rol', 'r.id_rol = rol.id_rol')
            ->where('r.id_cliente', $idCliente)
            ->where('r.activo', 1)
            ->groupStart()
                ->like('rol.codigo_rol', 'vigia', 'both')
                ->orLike('rol.nombre_rol', 'vigia', 'both')
            ->groupEnd()
            ->select('r.*, rol.nombre_rol')
            ->get()
            ->getRowArray();

        return $vigia;
    }

    /**
     * Construye el contenido del documento desde el contexto
     */
    protected function construirContenido(array $cliente, array $contexto, array $vigia, ?array $consultor, int $anio): array
    {
        $nombreCliente = $cliente['nombre_cliente'] ?? '';
        $nombreVigia = $vigia['nombre_completo'] ?? '';
        $cedulaVigia = $vigia['numero_documento'] ?? '';
        $cargoVigia = $vigia['cargo'] ?? 'Vigia de Seguridad y Salud en el Trabajo';

        // Responsabilidades del Vigia SST segun Decreto 1072/2015 y Res. 0312/2019
        $responsabilidades = [
            'Proponer a la administracion de la empresa o establecimiento de trabajo la adopcion de medidas y el desarrollo de actividades que procuren y mantengan la salud en los lugares y ambientes de trabajo.',
            'Proponer y participar en actividades de capacitacion en seguridad y salud en el trabajo.',
            'Colaborar con los funcionarios de entidades gubernamentales de salud ocupacional en las actividades que estos adelanten en la empresa.',
            'Vigilar el desarrollo de las actividades que en materia de medicina, higiene y seguridad industrial debe realizar la empresa.',
            'Colaborar en el analisis de las causas de los accidentes de trabajo y enfermedades profesionales.',
            'Proponer al empleador las medidas correctivas a que haya lugar para evitar la ocurrencia de accidentes de trabajo y enfermedades profesionales.',
            'Visitar periodicamente los lugares de trabajo e inspeccionar los ambientes, maquinas, equipos, aparatos y las operaciones realizadas por el personal.',
            'Informar al empleador sobre la existencia de factores de riesgo y sugerir las medidas correctivas y de control.',
            'Estudiar y considerar las sugerencias que presenten los trabajadores en materia de medicina, higiene y seguridad industrial.',
            'Servir como organismo de coordinacion entre empleador y los trabajadores en la solucion de los problemas relativos a la SST.',
        ];

        $responsabilidades[] = 'Participar en las actividades de promocion, divulgacion e informacion sobre SST entre los trabajadores.';
        $responsabilidades[] = 'Tramitar los reclamos de los trabajadores relacionados con la SST.';
        $responsabilidades[] = 'Solicitar periodicamente a la empresa informes sobre accidentalidad y enfermedades profesionales.';

        return [
            'secciones' => [
                [
                    'key' => 'objeto',
                    'titulo' => '1. OBJETO',
                    'contenido' => "Establecer las responsabilidades del Vigia de Seguridad y Salud en el Trabajo de <strong>{$nombreCliente}</strong>, de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.",
                    'aprobado' => true
                ],
                [
                    'key' => 'alcance',
                    'titulo' => '2. ALCANCE',
                    'contenido' => "Este documento aplica al trabajador designado como Vigia de Seguridad y Salud en el Trabajo de <strong>{$nombreCliente}</strong> y establece sus responsabilidades especificas en materia de SST.",
                    'aprobado' => true
                ],
                [
                    'key' => 'marco_legal',
                    'titulo' => '3. MARCO LEGAL',
                    'contenido' => '<ul style="line-height: 1.8;">
                        <li><strong>Decreto 1072 de 2015</strong> - Articulo 2.2.4.1.6: Vigia de Seguridad y Salud en el Trabajo</li>
                        <li><strong>Resolucion 0312 de 2019</strong> - Articulo 5: Estandares minimos para empresas con menos de 10 trabajadores</li>
                        <li><strong>Resolucion 2013 de 1986</strong> - Organizacion y funcionamiento del Vigia de SST</li>
                    </ul>',
                    'aprobado' => true
                ],
                [
                    'key' => 'responsabilidades',
                    'titulo' => '4. RESPONSABILIDADES',
                    'contenido' => $this->formatearResponsabilidades($responsabilidades),
                    'aprobado' => true
                ],
                [
                    'key' => 'compromiso',
                    'titulo' => '5. COMPROMISO',
                    'contenido' => "Yo, <strong>{$nombreVigia}</strong>, identificado(a) con documento de identidad <strong>{$cedulaVigia}</strong>, en calidad de Vigia de Seguridad y Salud en el Trabajo de <strong>{$nombreCliente}</strong>, declaro conocer y asumir las responsabilidades descritas en el presente documento, comprometiendome a su cumplimiento efectivo.",
                    'aprobado' => true
                ],
            ],
            'empresa' => [
                'nombre' => $nombreCliente,
                'nit' => $cliente['nit_cliente'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => 7, // Solo aplica para 7 estandares
            'vigia' => [
                'nombre' => $nombreVigia,
                'cedula' => $cedulaVigia,
                'cargo' => $cargoVigia
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
        $html = '<p>El Vigia de Seguridad y Salud en el Trabajo tiene las siguientes responsabilidades:</p><ol style="line-height: 1.8;">';
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

        $vigia = $this->obtenerVigia($idCliente);
        if (!$vigia) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vigia SST no encontrado']);
        }

        // Obtener consultor
        $consultor = null;
        $consultorModel = new ConsultantModel();
        $idConsultor = $contexto['id_consultor_responsable'] ?? null;
        if ($idConsultor) {
            $consultor = $consultorModel->find($idConsultor);
        }

        $nuevoContenido = $this->construirContenido($cliente, $contexto, $vigia, $consultor, $anio);

        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion de datos del Vigia SST';

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
