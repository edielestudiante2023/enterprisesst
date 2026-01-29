<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClienteContextoSstModel;
use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador para el documento "Responsabilidades del Representante Legal y Vigia/Delegado SST"
 * Estandar 1.1.2 - Resolucion 0312/2019
 *
 * DOCUMENTO COMBINADO segun nivel de estandares:
 * - 7 estandares: Resp. Rep. Legal + Vigia SST (2 firmantes)
 * - 21/60 estandares: Resp. Rep. Legal + Delegado SST (2 firmantes)
 *
 * El Representante Legal tiene responsabilidades segun Decreto 1072/2015 Art. 2.2.4.6.8
 * El Vigia/Delegado SST tiene responsabilidades de apoyo y facilitacion del SG-SST
 *
 * Patron B: Documento simple auto-generado desde contexto del cliente (sin IA)
 * Firmas: Digital (Representante Legal + Vigia/Delegado segun estandares)
 */
class PzresponsabilidadesRepLegalController extends Controller
{
    protected $db;
    protected ClientModel $clienteModel;

    // Configuracion del documento
    protected const TIPO_DOCUMENTO = 'responsabilidades_rep_legal_sgsst';
    protected const CODIGO_TIPO = 'RES';
    protected const CODIGO_TEMA = 'REP';

    // Nombres dinamicos segun estandares y configuracion
    protected const NOMBRE_DOC_SOLO_REP_LEGAL = 'Responsabilidades del Representante Legal en el SG-SST';
    protected const NOMBRE_DOC_7_EST = 'Responsabilidades del Representante Legal y Vigia SST';
    protected const NOMBRE_DOC_21_60_EST = 'Responsabilidades del Representante Legal y Delegado SST';

    /**
     * Obtiene el nombre del documento segun nivel de estandares, configuracion de delegado
     * y si tiene segundo firmante configurado
     */
    protected function getNombreDocumento(int $estandares, bool $requiereDelegado = false, bool $tieneSegundoFirmante = true): string
    {
        // Si no tiene segundo firmante configurado, solo Rep. Legal
        if (!$tieneSegundoFirmante) {
            return self::NOMBRE_DOC_SOLO_REP_LEGAL;
        }
        // Si tiene Delegado SST configurado, usar nombre con Delegado
        if ($requiereDelegado || $estandares >= 21) {
            return self::NOMBRE_DOC_21_60_EST;
        }
        return self::NOMBRE_DOC_7_EST;
    }

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

        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);
        $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
        $tieneSegundoFirmante = !empty(trim($contexto['delegado_sst_nombre'] ?? ''));
        $nombreDocumento = $this->getNombreDocumento($estandares, $requiereDelegado, $tieneSegundoFirmante);

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
            'titulo' => $nombreDocumento,
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
            'titulo' => $nombreDocumento,
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
            ->with('success', $nombreDocumento . ' generado exitosamente.');
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

        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);
        $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
        $tieneSegundoFirmante = !empty(trim($contexto['delegado_sst_nombre'] ?? ''));
        $nombreDocumento = $this->getNombreDocumento($estandares, $requiereDelegado, $tieneSegundoFirmante);

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
            'titulo' => $nombreDocumento . ' - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas,
            'nombreDocumento' => $nombreDocumento
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
        $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

        // Obtener datos del segundo firmante desde delegado_sst_* (siempre)
        // En la practica, Vigia y Delegado usan los mismos campos
        $nombreSegundoFirmante = trim($contexto['delegado_sst_nombre'] ?? '');
        $cedulaSegundoFirmante = trim($contexto['delegado_sst_cedula'] ?? '');

        // NUEVO: Detectar si hay segundo firmante configurado
        // Si no hay nombre del segundo firmante, el documento solo lleva firma del Rep. Legal
        $tieneSegundoFirmante = !empty($nombreSegundoFirmante);

        // Determinar rol del segundo firmante (solo si existe):
        // - Si requiere_delegado_sst = true O estandares >= 21: Delegado SST
        // - Si estandares <= 7 sin delegado: Vigia SST
        $esDelegado = $requiereDelegado || $estandares >= 21;
        $rolSegundoFirmante = $tieneSegundoFirmante ? ($esDelegado ? 'Delegado SST' : 'Vigia SST') : null;

        // Responsabilidades del Representante Legal segun Decreto 1072/2015 Art. 2.2.4.6.8
        $responsabilidadesRepLegal = [
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

        // Agregar responsabilidades adicionales segun estandares y configuracion de Delegado
        // Solo agregar responsabilidad de COPASST/Vigia/Delegado si hay segundo firmante configurado
        if ($tieneSegundoFirmante) {
            if ($estandares >= 21) {
                // 21 o 60 estandares: COPASST y Comite de Convivencia
                $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Comite Paritario de Seguridad y Salud en el Trabajo (COPASST).';
                $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Comite de Convivencia Laboral.';
            } elseif ($esDelegado) {
                // Menos de 21 estandares pero con Delegado SST configurado
                $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Delegado de Seguridad y Salud en el Trabajo.';
            } else {
                // 7 estandares con Vigia SST
                $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Vigia de Seguridad y Salud en el Trabajo.';
            }
        }
        // Si no tiene segundo firmante, no se agrega responsabilidad de Vigia/Delegado/COPASST

        $responsabilidadesRepLegal[] = 'Realizar la evaluacion anual del SG-SST.';
        $responsabilidadesRepLegal[] = 'Implementar acciones correctivas, preventivas y de mejora segun los resultados de la evaluacion.';
        $responsabilidadesRepLegal[] = 'Investigar todos los accidentes e incidentes de trabajo.';

        // Responsabilidades del Vigia SST (7 estandares)
        $responsabilidadesVigia = [
            'Proponer a la administracion de la empresa la adopcion de medidas y el desarrollo de actividades para procurar y mantener la salud en los lugares de trabajo.',
            'Proponer y participar en actividades de capacitacion en seguridad y salud en el trabajo dirigidas a trabajadores, supervisores y directivos.',
            'Colaborar con los funcionarios de entidades gubernamentales de seguridad y salud en el trabajo en las actividades que estos adelanten.',
            'Vigilar el desarrollo de las actividades de medicina, higiene y seguridad industrial, asi como las de medio ambiente laboral.',
            'Colaborar en el analisis de las causas de los accidentes de trabajo y enfermedades profesionales y proponer medidas correctivas.',
            'Visitar periodicamente los lugares de trabajo para inspeccionar los ambientes, equipos y operaciones realizadas por los trabajadores.',
            'Estudiar y considerar las sugerencias de los trabajadores en materia de medicina, higiene y seguridad industrial.',
            'Servir como organismo de coordinacion entre empleador y trabajadores en la solucion de problemas relativos a la SST.',
            'Solicitar periodicamente informes sobre accidentalidad y enfermedades profesionales.',
            'Mantener un archivo de las actas de cada reunion y demas actividades desarrolladas.',
        ];

        // Responsabilidades del Delegado SST (21 y 60 estandares)
        $responsabilidadesDelegado = [
            // Coordinacion y Comunicacion
            'Servir como enlace entre la empresa y el consultor externo responsable del SG-SST.',
            'Comunicar las directrices del consultor a los trabajadores y viceversa.',
            'Coordinar las reuniones y actividades relacionadas con el SG-SST al interior de la empresa.',
            // Gestion Documental
            'Recopilar y organizar la documentacion requerida para el SG-SST.',
            'Mantener actualizados los registros y evidencias de las actividades del sistema.',
            'Facilitar el acceso a la informacion cuando sea requerida por el consultor o autoridades.',
            // Apoyo en Actividades del SG-SST
            'Apoyar la divulgacion de politicas, procedimientos y comunicaciones del SG-SST.',
            'Colaborar en la organizacion de capacitaciones, simulacros e inspecciones.',
            'Asistir en la recoleccion de firmas y participacion de trabajadores en actividades del SG-SST.',
            // Seguimiento y Monitoreo
            'Realizar seguimiento al cumplimiento de las actividades programadas en el plan de trabajo.',
            'Reportar al consultor cualquier novedad, incidente o situacion que afecte la SST.',
            'Apoyar en la verificacion del cumplimiento de controles operacionales.',
            // Participacion Activa
            'Participar activamente en el COPASST o Comite de Convivencia segun aplique.',
            'Promover la cultura de prevencion y autocuidado entre los trabajadores.',
            'Fomentar la participacion de los trabajadores en el reporte de condiciones inseguras.',
            // Cumplimiento Legal
            'Apoyar en el cumplimiento de los requisitos legales aplicables en materia de SST.',
            'Facilitar las auditorias e inspecciones de entidades de control.',
        ];

        // Seleccionar responsabilidades segun rol (Delegado o Vigia) - solo si hay segundo firmante
        $responsabilidadesSegundoRol = $tieneSegundoFirmante
            ? ($esDelegado ? $responsabilidadesDelegado : $responsabilidadesVigia)
            : [];

        // Textos dinamicos segun si hay segundo firmante y su rol
        if (!$tieneSegundoFirmante) {
            // Solo Representante Legal
            $tituloObjeto = "Establecer las responsabilidades del Representante Legal de <strong>{$nombreCliente}</strong> en el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.";
            $tituloAlcance = "Este documento aplica al Representante Legal de <strong>{$nombreCliente}</strong>, estableciendo sus responsabilidades especificas en materia de Seguridad y Salud en el Trabajo.";
        } elseif ($esDelegado) {
            // Representante Legal + Delegado SST
            $tituloObjeto = "Establecer las responsabilidades del Representante Legal y del Delegado SST de <strong>{$nombreCliente}</strong> en el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.";
            $tituloAlcance = "Este documento aplica al Representante Legal y al Delegado SST de <strong>{$nombreCliente}</strong>, estableciendo sus responsabilidades especificas en materia de Seguridad y Salud en el Trabajo.";
        } else {
            // Representante Legal + Vigia SST
            $tituloObjeto = "Establecer las responsabilidades del Representante Legal y del Vigia de Seguridad y Salud en el Trabajo de <strong>{$nombreCliente}</strong> en el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), de conformidad con el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.";
            $tituloAlcance = "Este documento aplica al Representante Legal y al Vigia de Seguridad y Salud en el Trabajo de <strong>{$nombreCliente}</strong>, estableciendo sus responsabilidades especificas en materia de Seguridad y Salud en el Trabajo.";
        }

        // Construir secciones base (siempre presentes)
        $secciones = [
            [
                'key' => 'objeto',
                'titulo' => '1. OBJETO',
                'contenido' => $tituloObjeto,
                'aprobado' => true
            ],
            [
                'key' => 'alcance',
                'titulo' => '2. ALCANCE',
                'contenido' => $tituloAlcance,
                'aprobado' => true
            ],
            [
                'key' => 'responsabilidades_rep_legal',
                'titulo' => '3. RESPONSABILIDADES DEL REPRESENTANTE LEGAL',
                'contenido' => $this->formatearResponsabilidades($responsabilidadesRepLegal, 'El Representante Legal tiene las siguientes responsabilidades segun el Decreto 1072 de 2015, Articulo 2.2.4.6.8:'),
                'aprobado' => true
            ],
        ];

        // Agregar secciones del segundo firmante solo si existe
        if ($tieneSegundoFirmante) {
            $tituloResponsabilidadesSegundo = $esDelegado
                ? '4. RESPONSABILIDADES DEL DELEGADO SST'
                : '4. RESPONSABILIDADES DEL VIGIA SST';

            $introResponsabilidadesSegundo = $esDelegado
                ? 'El Delegado SST, como facilitador interno del Sistema de Gestion de Seguridad y Salud en el Trabajo, tiene las siguientes responsabilidades:'
                : 'El Vigia de Seguridad y Salud en el Trabajo tiene las siguientes responsabilidades segun la Resolucion 2013 de 1986 y el Decreto 1072 de 2015:';

            $secciones[] = [
                'key' => 'responsabilidades_segundo_rol',
                'titulo' => $tituloResponsabilidadesSegundo,
                'contenido' => $this->formatearResponsabilidades($responsabilidadesSegundoRol, $introResponsabilidadesSegundo),
                'aprobado' => true
            ];

            // Compromiso Rep Legal (numeracion dinamica)
            $secciones[] = [
                'key' => 'compromiso_rep_legal',
                'titulo' => '5. COMPROMISO DEL REPRESENTANTE LEGAL',
                'contenido' => "Yo, <strong>{$nombreRepLegal}</strong>, identificado(a) con documento de identidad <strong>{$cedulaRepLegal}</strong>, en calidad de Representante Legal de <strong>{$nombreCliente}</strong>, declaro conocer y asumir las responsabilidades descritas en el presente documento, comprometiendome a su cumplimiento efectivo.",
                'aprobado' => true
            ];

            // Compromiso segundo firmante
            $secciones[] = [
                'key' => 'compromiso_segundo_rol',
                'titulo' => '6. COMPROMISO DEL ' . strtoupper($rolSegundoFirmante),
                'contenido' => "Yo, <strong>{$nombreSegundoFirmante}</strong>, identificado(a) con documento de identidad <strong>{$cedulaSegundoFirmante}</strong>, en calidad de {$rolSegundoFirmante} de <strong>{$nombreCliente}</strong>, declaro conocer y asumir las responsabilidades descritas en el presente documento, comprometiendome a su cumplimiento efectivo.",
                'aprobado' => true
            ];
        } else {
            // Sin segundo firmante: solo compromiso del Rep Legal
            $secciones[] = [
                'key' => 'compromiso_rep_legal',
                'titulo' => '4. COMPROMISO DEL REPRESENTANTE LEGAL',
                'contenido' => "Yo, <strong>{$nombreRepLegal}</strong>, identificado(a) con documento de identidad <strong>{$cedulaRepLegal}</strong>, en calidad de Representante Legal de <strong>{$nombreCliente}</strong>, declaro conocer y asumir las responsabilidades descritas en el presente documento, comprometiendome a su cumplimiento efectivo.",
                'aprobado' => true
            ];
        }

        return [
            'secciones' => $secciones,
            'empresa' => [
                'nombre' => $nombreCliente,
                'nit' => $cliente['nit_cliente'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => $estandares,
            'requiere_delegado' => $requiereDelegado,
            'es_delegado' => $esDelegado,
            'tiene_segundo_firmante' => $tieneSegundoFirmante,
            'rol_segundo_firmante' => $rolSegundoFirmante,
            'representante_legal' => [
                'nombre' => $nombreRepLegal,
                'cedula' => $cedulaRepLegal
            ],
            'segundo_firmante' => $tieneSegundoFirmante ? [
                'nombre' => $nombreSegundoFirmante,
                'cedula' => $cedulaSegundoFirmante,
                'rol' => $rolSegundoFirmante
            ] : null,
            'responsabilidades_rep_legal' => $responsabilidadesRepLegal,
            'responsabilidades_segundo_rol' => $responsabilidadesSegundoRol,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            // Flag para templates: solo firma del Rep. Legal si no hay segundo firmante
            'solo_firma_rep_legal' => !$tieneSegundoFirmante
        ];
    }

    /**
     * Formatea la lista de responsabilidades como HTML
     */
    protected function formatearResponsabilidades(array $responsabilidades, string $intro = ''): string
    {
        $html = '';
        if ($intro) {
            $html .= '<p>' . $intro . '</p>';
        }
        $html .= '<ol style="line-height: 1.8;">';
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
        $nuevoSegundoNombre = $this->request->getPost('segundo_firmante_nombre');
        $nuevoSegundoCedula = $this->request->getPost('segundo_firmante_cedula');

        $datosActualizar = [];
        if ($nuevoRepLegalNombre && $nuevoRepLegalNombre !== ($contexto['representante_legal_nombre'] ?? '')) {
            $datosActualizar['representante_legal_nombre'] = $nuevoRepLegalNombre;
        }
        if ($nuevoRepLegalCedula !== null && $nuevoRepLegalCedula !== ($contexto['representante_legal_cedula'] ?? '')) {
            $datosActualizar['representante_legal_cedula'] = $nuevoRepLegalCedula;
        }

        // Actualizar datos del segundo firmante (Vigia/Delegado SST)
        // Siempre usar campos delegado_sst_* ya que Vigia y Delegado comparten el mismo origen de datos
        if ($nuevoSegundoNombre && $nuevoSegundoNombre !== ($contexto['delegado_sst_nombre'] ?? '')) {
            $datosActualizar['delegado_sst_nombre'] = $nuevoSegundoNombre;
        }
        if ($nuevoSegundoCedula !== null && $nuevoSegundoCedula !== ($contexto['delegado_sst_cedula'] ?? '')) {
            $datosActualizar['delegado_sst_cedula'] = $nuevoSegundoCedula;
        }

        if (!empty($datosActualizar)) {
            $datosActualizar['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('tbl_cliente_contexto_sst')
                ->where('id_cliente', $idCliente)
                ->update($datosActualizar);
            $contexto = $contextoModel->getByCliente($idCliente);
        }

        // Recalcular estandares despues de posible actualizacion
        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);
        $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
        $tieneSegundoFirmante = !empty(trim($contexto['delegado_sst_nombre'] ?? ''));
        $nombreDocumento = $this->getNombreDocumento($estandares, $requiereDelegado, $tieneSegundoFirmante);

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
                'titulo' => $nombreDocumento,
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
            'titulo' => $nombreDocumento,
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
