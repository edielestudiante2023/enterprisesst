<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\CronogcapacitacionModel;
use App\Models\IndicadorSSTModel;
use App\Models\ResponsableSSTModel;
use App\Services\CronogramaIAService;
use App\Services\PTAGeneratorService;
use App\Services\IADocumentacionService;
use Config\Database;

/**
 * Controlador para visualizar y generar documentos del SG-SST
 */
class DocumentosSSTController extends BaseController
{
    protected $db;
    protected ClientModel $clienteModel;

    // Tipos de documentos disponibles
    public const TIPOS_DOCUMENTO = [
        'programa_capacitacion' => [
            'nombre' => 'Programa de Capacitacion',
            'descripcion' => 'Documento formal del programa de capacitacion en SST',
            'flujo' => 'secciones_ia', // Usa editor de secciones con IA
            'secciones' => [
                ['numero' => 1, 'nombre' => 'Introduccion', 'key' => 'introduccion'],
                ['numero' => 2, 'nombre' => 'Objetivo General', 'key' => 'objetivo_general'],
                ['numero' => 3, 'nombre' => 'Objetivos Especificos', 'key' => 'objetivos_especificos'],
                ['numero' => 4, 'nombre' => 'Alcance', 'key' => 'alcance'],
                ['numero' => 5, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
                ['numero' => 6, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
                ['numero' => 7, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
                ['numero' => 8, 'nombre' => 'Metodologia', 'key' => 'metodologia'],
                ['numero' => 9, 'nombre' => 'Cronograma de Capacitaciones', 'key' => 'cronograma'],
                ['numero' => 10, 'nombre' => 'Plan de Trabajo Anual', 'key' => 'plan_trabajo'],
                ['numero' => 11, 'nombre' => 'Indicadores', 'key' => 'indicadores'],
                ['numero' => 12, 'nombre' => 'Recursos', 'key' => 'recursos'],
                ['numero' => 13, 'nombre' => 'Evaluacion y Seguimiento', 'key' => 'evaluacion'],
            ]
        ]
    ];

    // Mapeo de tipos de documento a codigos
    public const CODIGOS_DOCUMENTO = [
        'programa_capacitacion' => ['tipo' => 'PRG', 'tema' => 'CAP'],
        'politica_sst' => ['tipo' => 'POL', 'tema' => 'SST'],
        'plan_emergencias' => ['tipo' => 'PLA', 'tema' => 'EME'],
        'programa_emo' => ['tipo' => 'PRG', 'tema' => 'EMO'],
        'matriz_peligros' => ['tipo' => 'MTZ', 'tema' => 'PEL'],
        'procedimiento_investigacion' => ['tipo' => 'PRO', 'tema' => 'INV'],
        'programa_inspecciones' => ['tipo' => 'PRG', 'tema' => 'INS'],
        'programa_epp' => ['tipo' => 'PRG', 'tema' => 'EPP'],
    ];

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Genera codigo unico para documento usando SP
     * Formato: TIPO-TEMA-XXX (ej: PRG-CAP-001)
     */
    protected function generarCodigoDocumento(int $idCliente, string $tipoDocumento): string
    {
        $codigos = self::CODIGOS_DOCUMENTO[$tipoDocumento] ?? ['tipo' => 'DOC', 'tema' => 'GEN'];

        try {
            // Llamar al Stored Procedure
            $this->db->query("CALL sp_generar_codigo_documento(?, ?, ?, @codigo)", [
                $idCliente,
                $codigos['tipo'],
                $codigos['tema']
            ]);

            $result = $this->db->query("SELECT @codigo as codigo")->getRow();

            if ($result && !empty($result->codigo)) {
                return $result->codigo;
            }
        } catch (\Exception $e) {
            log_message('warning', 'Error al generar codigo con SP: ' . $e->getMessage());
        }

        // Fallback: generar codigo manualmente si falla el SP
        $consecutivo = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->countAllResults() + 1;

        return $codigos['tipo'] . '-' . $codigos['tema'] . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Vista para generar documento por secciones con IA
     */
    public function generarConIA(string $tipo, int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        if (!isset(self::TIPOS_DOCUMENTO[$tipo])) {
            return redirect()->back()->with('error', 'Tipo de documento no valido');
        }

        $tipoDoc = self::TIPOS_DOCUMENTO[$tipo];
        $anio = (int)date('Y');

        // Obtener contexto del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Verificar si ya existe el documento
        $documentoExistente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipo)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        // Obtener datos para las secciones
        $cronogramaService = new CronogramaIAService();
        $ptaService = new PTAGeneratorService();
        $indicadorModel = new IndicadorSSTModel();
        $responsableModel = new ResponsableSSTModel();

        $resumenCronograma = $cronogramaService->getResumenCronograma($idCliente, $anio);
        $resumenPTA = $ptaService->getResumenPTA($idCliente, $anio);
        $indicadores = $indicadorModel->getByCliente($idCliente, true, 'capacitacion');
        $responsables = $responsableModel->getByCliente($idCliente);

        // Preparar secciones con contenido existente si hay documento
        $secciones = $tipoDoc['secciones'];
        $contenidoExistente = [];

        if ($documentoExistente) {
            $contenidoExistente = json_decode($documentoExistente['contenido'], true);

            // Normalizar secciones existentes para eliminar duplicados
            if (!empty($contenidoExistente['secciones'])) {
                $contenidoExistente['secciones'] = $this->normalizarSecciones($contenidoExistente['secciones'], $tipo);

                // Mapear contenido existente a las secciones (buscar por key o por titulo)
                foreach ($secciones as &$seccion) {
                    foreach ($contenidoExistente['secciones'] as $secExistente) {
                        $keyMatch = isset($secExistente['key']) && $secExistente['key'] === $seccion['key'];
                        $tituloMatch = isset($secExistente['titulo']) && stripos($secExistente['titulo'], $seccion['nombre']) !== false;

                        if ($keyMatch || $tituloMatch) {
                            $seccion['contenido'] = $secExistente['contenido'] ?? '';
                            $seccion['aprobado'] = $secExistente['aprobado'] ?? false;
                            break;
                        }
                    }
                }
                unset($seccion); // Romper referencia para evitar duplicación en loops posteriores
            }
        }

        // Calcular si todas las secciones estan listas (guardadas y aprobadas)
        $totalSecciones = count($secciones);
        $seccionesGuardadas = 0;
        $seccionesAprobadas = 0;

        foreach ($secciones as $seccion) {
            $contenido = $seccion['contenido'] ?? '';
            if (is_array($contenido)) {
                $contenido = $contenido['contenido'] ?? '';
            }

            if (!empty($contenido)) {
                $seccionesGuardadas++;
            }
            if (!empty($seccion['aprobado'])) {
                $seccionesAprobadas++;
            }
        }

        // El boton Vista Previa solo se habilita si:
        // 1. El documento existe en la base de datos
        // 2. TODAS las secciones estan guardadas
        // 3. TODAS las secciones estan aprobadas
        $documentoExisteEnBD = !empty($documentoExistente);
        $todasSeccionesListas = $documentoExisteEnBD &&
                                ($seccionesGuardadas === $totalSecciones) &&
                                ($seccionesAprobadas === $totalSecciones) &&
                                ($totalSecciones > 0);

        $data = [
            'titulo' => 'Generar ' . $tipoDoc['nombre'] . ' con IA',
            'cliente' => $cliente,
            'tipo' => $tipo,
            'tipoDoc' => $tipoDoc,
            'secciones' => $secciones,
            'anio' => $anio,
            'estandares' => $estandares,
            'contexto' => $contexto,
            'documento' => $documentoExistente,
            'resumenCronograma' => $resumenCronograma,
            'resumenPTA' => $resumenPTA,
            'indicadores' => $indicadores,
            'responsables' => $responsables,
            'totalSecciones' => $totalSecciones,
            'seccionesGuardadas' => $seccionesGuardadas,
            'seccionesAprobadas' => $seccionesAprobadas,
            'todasSeccionesListas' => $todasSeccionesListas
        ];

        return view('documentos_sst/generar_con_ia', $data);
    }

    /**
     * Genera una seccion con IA (AJAX)
     */
    public function generarSeccionIA()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $this->request->getPost('tipo');
        $seccionKey = $this->request->getPost('seccion');
        $anio = $this->request->getPost('anio') ?? (int)date('Y');
        $contextoAdicional = $this->request->getPost('contexto_adicional') ?? '';

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        // Obtener contexto
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Si hay contexto adicional, usar el servicio de IA real (OpenAI)
        if (!empty(trim($contextoAdicional))) {
            $contenido = $this->generarConIAReal($seccionKey, $cliente, $contexto, $estandares, $anio, $contextoAdicional);
        } else {
            // Sin contexto adicional, usar plantillas estaticas
            $contenido = $this->generarContenidoSeccion($seccionKey, $cliente, $contexto, $estandares, $anio, $contextoAdicional);
        }

        return $this->response->setJSON([
            'success' => true,
            'contenido' => $contenido
        ]);
    }

    /**
     * Genera contenido usando el servicio de IA real (OpenAI)
     */
    protected function generarConIAReal(string $seccion, array $cliente, ?array $contexto, int $estandares, int $anio, string $contextoAdicional): string
    {
        // Obtener el nombre de la seccion
        $tipoDoc = self::TIPOS_DOCUMENTO['programa_capacitacion'];
        $nombreSeccion = $seccion;
        $numeroSeccion = 1;
        foreach ($tipoDoc['secciones'] as $sec) {
            if ($sec['key'] === $seccion) {
                $nombreSeccion = $sec['nombre'];
                $numeroSeccion = $sec['numero'];
                break;
            }
        }

        // Construir prompt base segun la seccion
        $promptBase = $this->getPromptBaseParaSeccion($seccion, $estandares);

        // Preparar datos para el servicio de IA
        $datosIA = [
            'seccion' => [
                'numero_seccion' => $numeroSeccion,
                'nombre_seccion' => $nombreSeccion
            ],
            'documento' => [
                'tipo_nombre' => 'Programa',
                'nombre' => 'Programa de Capacitacion en SST'
            ],
            'cliente' => $cliente,
            'contexto' => $contexto,
            'prompt_base' => $promptBase,
            'contexto_adicional' => $contextoAdicional
        ];

        // Llamar al servicio de IA
        $iaService = new IADocumentacionService();
        $resultado = $iaService->generarSeccion($datosIA);

        if ($resultado['success']) {
            return $resultado['contenido'];
        }

        // Si falla la IA, caer en la plantilla estatica
        log_message('warning', 'IA falló para sección ' . $seccion . ': ' . ($resultado['error'] ?? 'Error desconocido'));
        return $this->generarContenidoSeccion($seccion, $cliente, $contexto, $estandares, $anio, '');
    }

    /**
     * Obtiene el prompt base para una seccion especifica
     */
    protected function getPromptBaseParaSeccion(string $seccion, int $estandares): string
    {
        $prompts = [
            'introduccion' => "Genera una introducción para el Programa de Capacitación en SST. Debe incluir:
- Justificación de por qué la empresa necesita este programa
- Contexto de la actividad económica y sus riesgos
- Mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019)
- Compromiso de la alta dirección
IMPORTANTE: Ajusta la extensión según el tamaño de empresa ({$estandares} estándares)",

            'objetivo_general' => "Genera el objetivo general del Programa de Capacitación. Debe ser un objetivo SMART (específico, medible, alcanzable, relevante, temporal) relacionado con la capacitación en SST.",

            'objetivos_especificos' => "Genera los objetivos específicos del programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: 2-3 objetivos básicos
- 21 estándares: 3-4 objetivos
- 60 estándares: 4-5 objetivos
Deben ser SMART y relacionados con los peligros identificados de la empresa.",

            'alcance' => "Define el alcance del programa. Debe especificar:
- A quién aplica (trabajadores directos, contratistas si aplica)
- Áreas o procesos cubiertos
- Sedes incluidas
IMPORTANTE: Para empresas de 7 estándares, el alcance es simple. Máximo 5-6 ítems para 7 est, 8 ítems para 21 est, 10 ítems para 60 est.",

            'marco_legal' => "Lista el marco normativo aplicable al programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: MÁXIMO 4-5 normas
- 21 estándares: MÁXIMO 6-8 normas
- 60 estándares: Según aplique

PROHIBIDO: NO uses tablas Markdown. Solo usa formato de lista con viñetas o negritas.",

            'definiciones' => "Genera un glosario de términos técnicos para el programa.
CANTIDAD:
- 7 estándares: MÁXIMO 8 términos esenciales
- 21 estándares: MÁXIMO 12 términos
- 60 estándares: 12-15 términos
Definiciones basadas en normativa colombiana.",

            'responsabilidades' => "Define los roles y responsabilidades para el programa.
ROLES SEGÚN ESTÁNDARES:
- 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGÍA SST -no COPASST-, Trabajadores)
- 21 estándares: 5-6 roles (incluye COPASST)
- 60 estándares: Todos los roles necesarios
ADVERTENCIA: Si son 7 estándares, NUNCA mencionar COPASST, usar 'Vigía de SST'",

            'metodologia' => "Describe la metodología de capacitación. Incluye:
- Tipos de capacitación (teórica, práctica)
- Métodos de enseñanza
- Materiales y recursos
- Evaluación del aprendizaje",

            'cronograma' => "Genera el cronograma de capacitaciones para el año.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Actividades TRIMESTRALES o SEMESTRALES
- 21 estándares: Actividades BIMESTRALES o TRIMESTRALES
- 60 estándares: Actividades MENSUALES
Usa formato de tabla Markdown con columnas: Mes | Tema | Duración | Dirigido a",

            'plan_trabajo' => "Resume las actividades del Plan de Trabajo Anual relacionadas con capacitación. Incluye distribución por ciclo PHVA y estado de avance.",

            'indicadores' => "Define los indicadores de gestión para el programa.
CANTIDAD:
- 7 estándares: 2-3 indicadores simples
- 21 estándares: 4-5 indicadores
- 60 estándares: 6-8 indicadores completos
Incluye fórmula, meta y periodicidad para cada uno.",

            'recursos' => "Identifica los recursos necesarios para el programa.
PROPORCIONALIDAD:
- 7 estándares: Recursos MÍNIMOS (tiempo del responsable, materiales básicos)
- 21 estándares: Recursos moderados
- 60 estándares: Recursos completos
Categorías: Humanos, Físicos, Financieros",

            'evaluacion' => "Define el mecanismo de seguimiento y evaluación del programa.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Seguimiento TRIMESTRAL o SEMESTRAL
- 21 estándares: Seguimiento BIMESTRAL o TRIMESTRAL
- 60 estándares: Según complejidad
Incluye criterios de evaluación y responsables."
        ];

        return $prompts[$seccion] ?? "Genera el contenido para la sección '{$seccion}' del Programa de Capacitación en SST.";
    }

    /**
     * Genera contenido para una seccion especifica
     * @param string $seccion Clave de la seccion
     * @param array $cliente Datos del cliente
     * @param array|null $contexto Contexto SST del cliente
     * @param int $estandares Nivel de estandares aplicables
     * @param int $anio Año del documento
     * @param string $contextoAdicional Instrucciones adicionales del usuario para la IA
     */
    protected function generarContenidoSeccion(string $seccion, array $cliente, ?array $contexto, int $estandares, int $anio, string $contextoAdicional = ''): string
    {
        // Si hay contexto adicional del usuario, se puede usar para personalizar el contenido
        // Por ahora lo incluimos como nota al generar (en futuro puede enviarse a un servicio de IA real)
        $nombreEmpresa = $cliente['nombre_cliente'];
        $nivel = $estandares <= 7 ? 'basico (hasta 10 trabajadores, riesgo I, II o III)' :
                ($estandares <= 21 ? 'intermedio (11 a 50 trabajadores, riesgo I, II o III)' :
                'avanzado (mas de 50 trabajadores o riesgo IV y V)');

        switch ($seccion) {
            case 'introduccion':
                return "{$nombreEmpresa} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, especificamente la Resolucion 0312 de 2019 que establece los Estandares Minimos del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), ha desarrollado el presente Programa de Capacitacion.\n\n" .
                       "La empresa aplica los estandares de nivel {$nivel}, lo cual determina los requisitos minimos de capacitacion que deben cumplirse.\n\n" .
                       "La capacitacion es un elemento fundamental del SG-SST que permite a los trabajadores conocer los peligros y riesgos asociados a su labor, asi como las medidas de prevencion y control para evitar accidentes de trabajo y enfermedades laborales.";

            case 'objetivo_general':
                return "Desarrollar competencias en Seguridad y Salud en el Trabajo en todos los niveles de {$nombreEmpresa}, mediante la ejecucion de actividades de formacion y capacitacion que permitan la prevencion de accidentes de trabajo y enfermedades laborales, cumpliendo con los requisitos legales establecidos en la Resolucion 0312 de 2019.";

            case 'objetivos_especificos':
                $objetivos = [
                    "Realizar induccion y reinduccion en SST a todos los trabajadores",
                    "Capacitar a los trabajadores sobre los peligros y riesgos asociados a sus actividades",
                    "Formar a los integrantes del COPASST/Vigia SST en sus funciones y responsabilidades",
                    "Entrenar a los brigadistas de emergencias en prevencion y atencion de situaciones de emergencia"
                ];
                if ($estandares > 21) {
                    $objetivos[] = "Desarrollar competencias en los trabajadores para la identificacion de peligros y valoracion de riesgos";
                    $objetivos[] = "Promover estilos de vida y trabajo saludables";
                }
                return "- " . implode("\n- ", $objetivos);

            case 'alcance':
                return "Este programa aplica a todos los trabajadores de {$nombreEmpresa}, incluyendo trabajadores directos, contratistas, subcontratistas y visitantes que realicen actividades en las instalaciones de la empresa.";

            case 'marco_legal':
                return "El presente programa se fundamenta en la siguiente normatividad:\n\n" .
                       "- Ley 9 de 1979: Codigo Sanitario Nacional\n" .
                       "- Resolucion 2400 de 1979: Disposiciones sobre vivienda, higiene y seguridad en los establecimientos de trabajo\n" .
                       "- Decreto 1295 de 1994: Organizacion y administracion del Sistema General de Riesgos Profesionales\n" .
                       "- Ley 1562 de 2012: Sistema de Gestion de Seguridad y Salud en el Trabajo\n" .
                       "- Decreto 1072 de 2015: Decreto Unico Reglamentario del Sector Trabajo (Capitulo 6)\n" .
                       "- Resolucion 0312 de 2019: Estandares Minimos del SG-SST";

            case 'definiciones':
                return "**Capacitacion:** Proceso mediante el cual se desarrollan competencias, habilidades y destrezas en los trabajadores.\n\n" .
                       "**Induccion:** Capacitacion inicial que recibe el trabajador al ingresar a la empresa sobre aspectos generales y especificos de SST.\n\n" .
                       "**Reinduccion:** Capacitacion periodica para actualizar conocimientos y reforzar conceptos de SST.\n\n" .
                       "**Entrenamiento:** Proceso de aprendizaje practico que permite desarrollar habilidades especificas.\n\n" .
                       "**Competencia:** Capacidad demostrada para aplicar conocimientos y habilidades.";

            case 'responsabilidades':
                // Obtener responsables reales de la base de datos
                $responsableModel = new ResponsableSSTModel();
                $responsables = $responsableModel->getByCliente($cliente['id_cliente']);

                $organo = $estandares <= 10 ? 'Vigia de SST' : 'COPASST';

                // Si hay responsables registrados, usar sus datos
                if (!empty($responsables)) {
                    $contenidoResp = $responsableModel->generarContenidoParaDocumento($cliente['id_cliente'], $estandares);

                    // Agregar las funciones de cada rol
                    $contenidoResp .= "\n**Funciones en el Programa de Capacitacion:**\n\n";
                    $contenidoResp .= "**Alta Direccion:**\n" .
                           "- Asignar los recursos necesarios para la ejecucion del programa\n" .
                           "- Garantizar la participacion de los trabajadores en las capacitaciones\n\n" .
                           "**Responsable del SG-SST:**\n" .
                           "- Planificar y coordinar las actividades de capacitacion\n" .
                           "- Realizar seguimiento al cumplimiento del cronograma\n" .
                           "- Evaluar la efectividad de las capacitaciones\n" .
                           "- Mantener los registros de asistencia y evaluacion\n\n" .
                           "**{$organo}:**\n" .
                           "- Participar en las actividades de capacitacion\n" .
                           "- Proponer temas de capacitacion segun las necesidades identificadas\n" .
                           "- Verificar el cumplimiento del programa\n\n" .
                           "**Trabajadores:**\n" .
                           "- Asistir a las capacitaciones programadas\n" .
                           "- Aplicar los conocimientos adquiridos en su labor diaria\n" .
                           "- Participar activamente en las actividades de formacion";

                    return $contenidoResp;
                }

                // Si no hay responsables, mostrar plantilla genérica con aviso
                return "[PENDIENTE: Registrar responsables del SG-SST en el modulo de Responsables]\n\n" .
                       "**Alta Direccion:**\n" .
                       "- Asignar los recursos necesarios para la ejecucion del programa\n" .
                       "- Garantizar la participacion de los trabajadores en las capacitaciones\n\n" .
                       "**Responsable del SG-SST:**\n" .
                       "- Planificar y coordinar las actividades de capacitacion\n" .
                       "- Realizar seguimiento al cumplimiento del cronograma\n" .
                       "- Evaluar la efectividad de las capacitaciones\n" .
                       "- Mantener los registros de asistencia y evaluacion\n\n" .
                       "**{$organo}:**\n" .
                       "- Participar en las actividades de capacitacion\n" .
                       "- Proponer temas de capacitacion segun las necesidades identificadas\n" .
                       "- Verificar el cumplimiento del programa\n\n" .
                       "**Trabajadores:**\n" .
                       "- Asistir a las capacitaciones programadas\n" .
                       "- Aplicar los conocimientos adquiridos en su labor diaria\n" .
                       "- Participar activamente en las actividades de formacion";

            case 'metodologia':
                return "Las capacitaciones se desarrollaran utilizando las siguientes metodologias:\n\n" .
                       "**Capacitaciones Teoricas:**\n" .
                       "- Presentaciones interactivas\n" .
                       "- Material audiovisual\n" .
                       "- Documentos de apoyo\n\n" .
                       "**Capacitaciones Practicas:**\n" .
                       "- Talleres demostrativos\n" .
                       "- Simulacros\n" .
                       "- Ejercicios practicos en campo\n\n" .
                       "**Evaluacion:**\n" .
                       "- Evaluacion escrita al finalizar cada capacitacion\n" .
                       "- Evaluacion practica cuando aplique\n" .
                       "- Retroalimentacion individual";

            case 'cronograma':
                // Obtener cronograma real
                $cronogramaModel = new CronogcapacitacionModel();
                $cronogramas = $cronogramaModel
                    ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion')
                    ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
                    ->where('id_cliente', $cliente['id_cliente'])
                    ->where('YEAR(fecha_programada)', $anio)
                    ->orderBy('fecha_programada', 'ASC')
                    ->findAll();

                if (empty($cronogramas)) {
                    return "[PENDIENTE: Generar cronograma de capacitaciones en el modulo Generador IA]";
                }

                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $contenido = "| Mes | Tema | Duracion | Dirigido a | Responsable |\n";
                $contenido .= "|-----|------|----------|------------|-------------|\n";

                foreach ($cronogramas as $c) {
                    $mes = (int)date('n', strtotime($c['fecha_programada']));
                    $contenido .= "| {$meses[$mes]} | {$c['capacitacion']} | " . ($c['horas_de_duracion_de_la_capacitacion'] ?? 1) . "h | " . ($c['perfil_de_asistentes'] ?? 'Todos') . " | " . ($c['nombre_del_capacitador'] ?? 'Responsable SST') . " |\n";
                }

                return $contenido;

            case 'plan_trabajo':
                // Obtener datos del Plan de Trabajo Anual
                $ptaService = new PTAGeneratorService();
                $resumenPTA = $ptaService->getResumenPTA($cliente['id_cliente'], $anio);

                if (empty($resumenPTA['actividades'])) {
                    return "[PENDIENTE: Generar Plan de Trabajo Anual en el modulo Generador IA]";
                }

                $contenidoPTA = "El Plan de Trabajo Anual establece las actividades a desarrollar para el cumplimiento de los objetivos del SG-SST.\n\n";
                $contenidoPTA .= "**Resumen del Plan de Trabajo {$anio}:**\n\n";
                $contenidoPTA .= "- Total de actividades: {$resumenPTA['total']}\n";
                $contenidoPTA .= "- Actividades completadas: {$resumenPTA['cerradas']}\n";
                $contenidoPTA .= "- Actividades en proceso: {$resumenPTA['en_proceso']}\n";
                $contenidoPTA .= "- Actividades pendientes: {$resumenPTA['abiertas']}\n";
                $contenidoPTA .= "- Porcentaje de avance: {$resumenPTA['porcentaje_avance']}%\n\n";

                $contenidoPTA .= "**Distribucion por ciclo PHVA:**\n\n";
                $contenidoPTA .= "| Ciclo | Cantidad |\n";
                $contenidoPTA .= "|-------|----------|\n";
                foreach ($resumenPTA['por_phva'] as $ciclo => $cantidad) {
                    $contenidoPTA .= "| {$ciclo} | {$cantidad} |\n";
                }
                $contenidoPTA .= "\n";

                // Mostrar actividades principales
                $contenidoPTA .= "**Actividades programadas:**\n\n";
                $contenidoPTA .= "| Actividad | Responsable | Fecha | PHVA | Estado |\n";
                $contenidoPTA .= "|-----------|-------------|-------|------|--------|\n";

                $contador = 0;
                foreach ($resumenPTA['actividades'] as $act) {
                    if ($contador >= 15) { // Limitar a 15 actividades para no hacer muy largo
                        $contenidoPTA .= "| ... | ... | ... | ... | ... |\n";
                        break;
                    }
                    $fecha = !empty($act['fecha_propuesta']) ? date('d/m/Y', strtotime($act['fecha_propuesta'])) : 'Por definir';
                    $estado = ucfirst(strtolower($act['estado_actividad'] ?? 'Abierta'));
                    $phva = $act['phva_plandetrabajo'] ?? 'HACER';
                    $contenidoPTA .= "| " . substr($act['actividad_plandetrabajo'] ?? 'N/A', 0, 50) . " | " .
                                     ($act['responsable_actividad'] ?? 'Por asignar') . " | " .
                                     $fecha . " | " . $phva . " | " . $estado . " |\n";
                    $contador++;
                }

                return $contenidoPTA;

            case 'indicadores':
                $indicadorModel = new IndicadorSSTModel();
                $indicadores = $indicadorModel->getByCliente($cliente['id_cliente'], true, 'capacitacion');

                if (empty($indicadores)) {
                    return "[PENDIENTE: Generar indicadores en el modulo Generador IA]";
                }

                $contenido = "El cumplimiento del programa se medira a traves de los siguientes indicadores:\n\n";
                foreach ($indicadores as $ind) {
                    $contenido .= "**{$ind['nombre_indicador']}**\n";
                    if (!empty($ind['formula'])) {
                        $contenido .= "- Formula: {$ind['formula']}\n";
                    }
                    if (!empty($ind['meta'])) {
                        $contenido .= "- Meta: {$ind['meta']}{$ind['unidad_medida']}\n";
                    }
                    $contenido .= "- Periodicidad: " . ucfirst($ind['periodicidad'] ?? 'trimestral') . "\n\n";
                }
                return $contenido;

            case 'recursos':
                // Obtener datos del Plan de Trabajo Anual filtrados por tipo de servicio "Programa de Capacitacion"
                $ptaService = new PTAGeneratorService();
                $resumenPTA = $ptaService->getResumenPTA(
                    $cliente['id_cliente'],
                    $anio,
                    PTAGeneratorService::TIPOS_SERVICIO['PROGRAMA_CAPACITACION']
                );

                $contenidoRecursos = "Para la ejecucion del programa de capacitacion se requieren los siguientes recursos:\n\n" .
                       "**Recursos Humanos:**\n" .
                       "- Responsable del SG-SST\n" .
                       "- Capacitadores internos y/o externos\n" .
                       "- ARL (Administradora de Riesgos Laborales)\n\n" .
                       "**Recursos Fisicos:**\n" .
                       "- Sala de capacitaciones o espacio adecuado\n" .
                       "- Equipos audiovisuales (computador, proyector)\n" .
                       "- Material didactico\n\n" .
                       "**Recursos Financieros:**\n" .
                       "- Presupuesto asignado por la alta direccion para actividades de capacitacion\n\n";

                // Agregar actividades del PTA que pertenecen al Programa de Capacitación
                if (!empty($resumenPTA['actividades'])) {
                    $contenidoRecursos .= "**Actividades del Plan de Trabajo Anual - Programa de Capacitacion:**\n\n";
                    $contenidoRecursos .= "| Actividad | Responsable | Estado |\n";
                    $contenidoRecursos .= "|-----------|-------------|--------|\n";

                    foreach ($resumenPTA['actividades'] as $act) {
                        $estado = ucfirst(strtolower($act['estado_actividad'] ?? 'Abierta'));
                        $contenidoRecursos .= "| " . ($act['actividad_plandetrabajo'] ?? 'N/A') . " | " .
                                             ($act['responsable_actividad'] ?? 'Por asignar') . " | " .
                                             $estado . " |\n";
                    }
                }

                return $contenidoRecursos;

            case 'evaluacion':
                return "El programa sera evaluado trimestralmente considerando:\n\n" .
                       "- Cumplimiento del cronograma de capacitaciones\n" .
                       "- Cobertura de trabajadores capacitados\n" .
                       "- Resultados de las evaluaciones aplicadas\n" .
                       "- Aplicacion de conocimientos en el trabajo\n\n" .
                       "Los resultados de la evaluacion seran presentados en las reuniones del COPASST/Vigia SST y serviran para realizar ajustes al programa segun las necesidades identificadas.";

            default:
                return "[Seccion no definida]";
        }
    }

    /**
     * Normaliza las secciones eliminando duplicados, ordenando y asegurando estructura correcta
     */
    private function normalizarSecciones(array $secciones, string $tipo): array
    {
        $tipoDoc = self::TIPOS_DOCUMENTO[$tipo] ?? null;
        if (!$tipoDoc) {
            return $secciones;
        }

        // Indexar secciones existentes por key
        $seccionesPorKey = [];
        foreach ($secciones as $sec) {
            $secKey = $sec['key'] ?? null;
            $secTitulo = $sec['titulo'] ?? '';

            // Si no tiene key, intentar encontrar el key basado en el titulo
            if (!$secKey) {
                foreach ($tipoDoc['secciones'] as $defSec) {
                    if (stripos($secTitulo, $defSec['nombre']) !== false) {
                        $secKey = $defSec['key'];
                        break;
                    }
                }
            }

            if ($secKey) {
                // Si ya existe esta seccion, actualizar con contenido mas reciente
                if (isset($seccionesPorKey[$secKey])) {
                    if (!empty($sec['contenido'])) {
                        $seccionesPorKey[$secKey]['contenido'] = $sec['contenido'];
                    }
                    if (!empty($sec['aprobado'])) {
                        $seccionesPorKey[$secKey]['aprobado'] = $sec['aprobado'];
                    }
                } else {
                    $sec['key'] = $secKey;
                    $seccionesPorKey[$secKey] = $sec;
                }
            }
        }

        // Reconstruir array ordenado segun la estructura definida
        $seccionesOrdenadas = [];
        foreach ($tipoDoc['secciones'] as $defSec) {
            $key = $defSec['key'];
            if (isset($seccionesPorKey[$key])) {
                // Usar seccion existente pero con titulo actualizado
                $seccion = $seccionesPorKey[$key];
                $seccion['titulo'] = $defSec['numero'] . '. ' . strtoupper($defSec['nombre']);
                $seccion['key'] = $key;
                $seccionesOrdenadas[] = $seccion;
            } else {
                // Crear seccion vacia
                $seccionesOrdenadas[] = [
                    'titulo' => $defSec['numero'] . '. ' . strtoupper($defSec['nombre']),
                    'contenido' => '',
                    'key' => $key,
                    'aprobado' => false
                ];
            }
        }

        return $seccionesOrdenadas;
    }

    /**
     * Guarda una seccion editada (AJAX)
     */
    public function guardarSeccion()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $this->request->getPost('tipo');
        $seccionKey = $this->request->getPost('seccion');
        $contenido = $this->request->getPost('contenido');
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        // Obtener informacion de la seccion desde TIPOS_DOCUMENTO
        $tipoDoc = self::TIPOS_DOCUMENTO[$tipo] ?? null;
        $nombreSeccion = $seccionKey;
        $numeroSeccion = 0;
        if ($tipoDoc) {
            foreach ($tipoDoc['secciones'] as $s) {
                if ($s['key'] === $seccionKey) {
                    $nombreSeccion = $s['nombre'];
                    $numeroSeccion = $s['numero'];
                    break;
                }
            }
        }

        // Obtener o crear documento
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipo)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        $contenidoDoc = $documento ? json_decode($documento['contenido'], true) : ['secciones' => []];

        // Actualizar seccion - buscar por key O por titulo (formato antiguo "N. NOMBRE")
        $encontrado = false;
        $tituloAntiguo = $numeroSeccion . '. ' . strtoupper($nombreSeccion);

        foreach ($contenidoDoc['secciones'] as $idx => &$sec) {
            $secKey = $sec['key'] ?? '';
            $secTitulo = $sec['titulo'] ?? '';

            // Buscar por key exacto o por titulo antiguo
            if ($secKey === $seccionKey ||
                stripos($secTitulo, $nombreSeccion) !== false ||
                $secTitulo === $tituloAntiguo) {

                // Actualizar la seccion manteniendo compatibilidad
                $sec['key'] = $seccionKey;
                $sec['titulo'] = $numeroSeccion . '. ' . strtoupper($nombreSeccion);
                $sec['contenido'] = $contenido;
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            $contenidoDoc['secciones'][] = [
                'key' => $seccionKey,
                'titulo' => $numeroSeccion . '. ' . strtoupper($nombreSeccion),
                'contenido' => $contenido,
                'aprobado' => false
            ];
        }

        // Normalizar secciones para eliminar duplicados
        $contenidoDoc['secciones'] = $this->normalizarSecciones($contenidoDoc['secciones'], $tipo);

        // Guardar
        if ($documento) {
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $documento['id_documento'])
                ->update([
                    'contenido' => json_encode($contenidoDoc, JSON_UNESCAPED_UNICODE),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Generar codigo unico usando SP
            $codigoDocumento = $this->generarCodigoDocumento($idCliente, $tipo);

            $this->db->table('tbl_documentos_sst')->insert([
                'id_cliente' => $idCliente,
                'tipo_documento' => $tipo,
                'codigo' => $codigoDocumento,
                'titulo' => self::TIPOS_DOCUMENTO[$tipo]['nombre'] ?? 'Documento SST',
                'anio' => $anio,
                'contenido' => json_encode($contenidoDoc, JSON_UNESCAPED_UNICODE),
                'version' => 1,
                'estado' => 'borrador',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Seccion guardada']);
    }

    /**
     * Aprueba una seccion (AJAX)
     */
    public function aprobarSeccion()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $this->request->getPost('tipo');
        $seccionKey = $this->request->getPost('seccion');
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipo)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado']);
        }

        $contenidoDoc = json_decode($documento['contenido'], true);

        // Obtener informacion de la seccion desde TIPOS_DOCUMENTO
        $tipoDoc = self::TIPOS_DOCUMENTO[$tipo] ?? null;
        $nombreSeccion = $seccionKey;
        $numeroSeccion = 0;
        if ($tipoDoc) {
            foreach ($tipoDoc['secciones'] as $s) {
                if ($s['key'] === $seccionKey) {
                    $nombreSeccion = $s['nombre'];
                    $numeroSeccion = $s['numero'];
                    break;
                }
            }
        }

        $tituloAntiguo = $numeroSeccion . '. ' . strtoupper($nombreSeccion);
        $encontrado = false;

        foreach ($contenidoDoc['secciones'] as &$sec) {
            $secKey = $sec['key'] ?? '';
            $secTitulo = $sec['titulo'] ?? '';

            // Buscar por key exacto o por titulo antiguo
            if ($secKey === $seccionKey ||
                stripos($secTitulo, $nombreSeccion) !== false ||
                $secTitulo === $tituloAntiguo) {

                $sec['key'] = $seccionKey; // Asegurar que tenga key para futuras busquedas
                $sec['aprobado'] = true;
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            return $this->response->setJSON(['success' => false, 'message' => 'Seccion no encontrada en el documento']);
        }

        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $documento['id_documento'])
            ->update([
                'contenido' => json_encode($contenidoDoc, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Seccion aprobada']);
    }

    /**
     * Genera PDF del documento
     */
    public function generarPDF(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        $data = [
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio']
        ];

        return view('documentos_sst/programa_capacitacion', $data);
    }

    /**
     * Muestra el Programa de Capacitacion generado
     */
    public function programaCapacitacion(int $idCliente, int $anio)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'programa_capacitacion')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->to(base_url('generador-ia/' . $idCliente))
                ->with('error', 'Documento no encontrado. Genere primero el Programa de Capacitacion.');
        }

        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones para eliminar duplicados
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], 'programa_capacitacion');
        }

        // Obtener historial de versiones para la tabla de Control de Cambios
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente para las firmas
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);

        // Obtener contexto SST para datos adicionales
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Obtener datos del consultor asignado
        $consultor = null;
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);
        }

        // Obtener firmas electrónicas del documento
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
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
            'titulo' => 'Programa de Capacitacion - ' . $cliente['nombre_cliente'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $anio,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas
        ];

        return view('documentos_sst/programa_capacitacion', $data);
    }

    /**
     * Exporta el documento a PDF usando Dompdf
     */
    public function exportarPDF(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        // Preparar logo como base64 para el PDF
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Obtener historial de versiones
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($documento['id_cliente']);

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($documento['id_cliente']);

        // Obtener datos del consultor asignado
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);

            // Preparar firma del consultor como base64 para PDF
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        // Obtener firmas electrónicas del documento para el PDF
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
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio'],
            'logoBase64' => $logoBase64,
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64,
            'firmasElectronicas' => $firmasElectronicas
        ];

        // Renderizar la vista del PDF
        $html = view('documentos_sst/pdf_template', $data);

        // Crear instancia de Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $nombreArchivo = $documento['codigo'] . '_' . url_title($documento['titulo'], '-', true) . '.pdf';

        // Descargar
        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    }

    /**
     * Publica el documento como PDF en tbl_reporte (reportList) para consulta rápida
     */
    public function publicarPDF(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

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
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($documento['id_cliente']);

        // Contexto
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($documento['id_cliente']);

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
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio'],
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
        $nit = $cliente['nit_cliente'] ?? $documento['id_cliente'];
        $uploadDir = FCPATH . 'uploads/' . $nit;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . url_title(($documento['codigo'] ?? 'DOC') . '_' . $documento['titulo'], '-', true) . '.pdf';
        $filePath = $uploadDir . '/' . $fileName;
        file_put_contents($filePath, $pdfOutput);

        $enlace = base_url('uploads/' . $nit . '/' . $fileName);

        // Obtener ID del detail_report "Documento SG-SST"
        $detailReport = $this->db->table('detail_report')
            ->where('detail_report', 'Documento SG-SST')
            ->get()
            ->getRowArray();
        $idDetailReport = $detailReport['id_detailreport'] ?? 2;

        // Verificar si ya existe un reporte para este documento (evitar duplicados)
        $codigoBusqueda = $documento['codigo'] ?? $documento['titulo'];
        $existente = $this->db->table('tbl_reporte')
            ->like('titulo_reporte', $codigoBusqueda)
            ->where('id_cliente', $documento['id_cliente'])
            ->where('id_detailreport', $idDetailReport)
            ->get()
            ->getRowArray();

        $idReportType = 12; // Reportes SST

        $estadoDoc = $documento['estado'] ?? 'borrador';
        $tituloReporte = ($documento['codigo'] ?? '') . ' - ' . $documento['titulo'] . ' (v' . ($documento['version'] ?? '1') . ')';

        if ($existente) {
            // Actualizar el reporte existente con el nuevo PDF
            $this->db->table('tbl_reporte')
                ->where('id_reporte', $existente['id_reporte'])
                ->update([
                    'titulo_reporte' => $tituloReporte,
                    'enlace' => $enlace,
                    'estado' => 'CERRADO',
                    'observaciones' => 'PDF actualizado manualmente. Estado: ' . $estadoDoc . '. Año: ' . $documento['anio'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Insertar nuevo registro
            $this->db->table('tbl_reporte')->insert([
                'titulo_reporte' => $tituloReporte,
                'id_detailreport' => $idDetailReport,
                'id_report_type' => $idReportType,
                'id_cliente' => $documento['id_cliente'],
                'enlace' => $enlace,
                'estado' => 'CERRADO',
                'observaciones' => 'Documento publicado manualmente. Estado: ' . $estadoDoc . '. Año: ' . $documento['anio'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Guardar enlace PDF en la versión vigente
        $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'vigente')
            ->update(['archivo_pdf' => $enlace]);

        return redirect()->to('documentacion/' . $documento['id_cliente'])
            ->with('success', 'Documento publicado exitosamente en Reportes. Ya es consultable desde reportList.');
    }

    /**
     * Exporta el documento a Word (.doc) usando HTML compatible
     */
    public function exportarWord(int $idDocumento)
    {
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $cliente = $this->clienteModel->find($documento['id_cliente']);
        $contenido = json_decode($documento['contenido'], true);

        // Normalizar secciones
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $documento['tipo_documento']);
        }

        // Preparar logo como base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Obtener versiones del documento
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('version', 'DESC')
            ->orderBy('version_texto', 'DESC')
            ->get()
            ->getResultArray();

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($documento['id_cliente']);

        // Obtener datos del consultor asignado
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);

            // Preparar firma del consultor como base64 para Word
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        $data = [
            'titulo' => $documento['titulo'],
            'cliente' => $cliente,
            'documento' => $documento,
            'contenido' => $contenido,
            'anio' => $documento['anio'],
            'logoBase64' => $logoBase64,
            'versiones' => $versiones,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64
        ];

        // Renderizar la vista HTML para Word
        $html = view('documentos_sst/word_template', $data);

        // Nombre del archivo
        $nombreArchivo = ($documento['codigo'] ?? 'documento') . '_' . url_title($documento['titulo'], '-', true) . '.doc';

        // Headers para descarga como Word
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        echo $html;
        exit;
    }

    /**
     * Aprueba el documento completo y crea una nueva version
     */
    public function aprobarDocumento()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $tipoCambio = $this->request->getPost('tipo_cambio') ?? 'menor';
        $descripcionCambio = $this->request->getPost('descripcion_cambio');

        if (empty($idDocumento) || empty($descripcionCambio)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan datos requeridos'
            ]);
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Documento no encontrado'
            ]);
        }

        // Obtener usuario actual
        $session = session();
        $usuarioId = $session->get('id_usuario');
        $usuarioNombre = $session->get('nombre_usuario') ?? 'Usuario del sistema';

        // Si el documento tiene un motivo_version guardado (de iniciarNuevaVersion), usarlo
        // si no se proporciono otra descripcion
        if (empty($descripcionCambio) && !empty($documento['motivo_version'])) {
            $descripcionCambio = $documento['motivo_version'];
        }

        // Verificar si es la primera aprobacion del documento (no hay versiones previas)
        $versionesPrevias = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->countAllResults();

        // Calcular nueva version
        $versionActual = (int)$documento['version'];

        if ($versionesPrevias === 0) {
            // Primera aprobacion: siempre version 1.0
            $nuevaVersion = 1;
            $versionTexto = '1.0';
        } elseif ($tipoCambio === 'mayor') {
            // Obtener la version mayor mas reciente
            $ultimaVersionMayor = $this->db->table('tbl_doc_versiones_sst')
                ->selectMax('version')
                ->where('id_documento', $idDocumento)
                ->get()
                ->getRow();

            $nuevaVersion = ($ultimaVersionMayor && $ultimaVersionMayor->version) ? (int)$ultimaVersionMayor->version + 1 : $versionActual + 1;
            $versionTexto = $nuevaVersion . '.0';
        } else {
            // Version menor: incrementar el decimal de la version actual
            $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(version_texto, '.', -1) AS UNSIGNED)) as max_decimal
                    FROM tbl_doc_versiones_sst
                    WHERE id_documento = ? AND version = ?";
            $ultimoDecimal = $this->db->query($sql, [$idDocumento, $versionActual])->getRow();

            $decimal = ($ultimoDecimal && $ultimoDecimal->max_decimal !== null) ? (int)$ultimoDecimal->max_decimal + 1 : 1;
            $nuevaVersion = $versionActual;
            $versionTexto = $versionActual . '.' . $decimal;
        }

        try {
            $this->db->transStart();

            // Marcar versiones anteriores como obsoletas
            $this->db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $idDocumento)
                ->update(['estado' => 'obsoleto']);

            // Insertar nueva version con snapshot del contenido
            // Incluir datos del cliente, codigo, titulo y anio para facilitar consultas
            $this->db->table('tbl_doc_versiones_sst')->insert([
                'id_documento' => $idDocumento,
                'id_cliente' => $documento['id_cliente'],
                'codigo' => $documento['codigo'] ?? null,
                'titulo' => $documento['titulo'],
                'anio' => $documento['anio'],
                'version' => $nuevaVersion,
                'version_texto' => $versionTexto,
                'tipo_cambio' => $tipoCambio,
                'descripcion_cambio' => $descripcionCambio,
                'contenido_snapshot' => $documento['contenido'],
                'estado' => 'vigente',
                'autorizado_por' => $usuarioNombre,
                'autorizado_por_id' => $usuarioId,
                'fecha_autorizacion' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $idVersion = $this->db->insertID();

            // Actualizar documento principal
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update([
                    'version' => $nuevaVersion,
                    'estado' => 'aprobado',
                    'fecha_aprobacion' => date('Y-m-d H:i:s'),
                    'aprobado_por' => $usuarioId,
                    'motivo_version' => $descripcionCambio,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transaccion');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento aprobado correctamente',
                'version' => $versionTexto,
                'id_version' => $idVersion
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al aprobar documento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Inicia el proceso de nueva version: cambia estado a borrador y redirige a edicion
     */
    public function iniciarNuevaVersion()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $tipoCambio = $this->request->getPost('tipo_cambio') ?? 'menor';
        $descripcionCambio = $this->request->getPost('descripcion_cambio');

        if (empty($idDocumento) || empty($descripcionCambio)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan datos requeridos'
            ]);
        }

        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Documento no encontrado'
            ]);
        }

        // Calcular cual sera la proxima version para mostrar al usuario
        $versionActual = (int)$documento['version'];
        if ($tipoCambio === 'mayor') {
            $proximaVersion = ($versionActual + 1) . '.0';
        } else {
            // Buscar el ultimo decimal de esta version
            $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(version_texto, '.', -1) AS UNSIGNED)) as max_decimal
                    FROM tbl_doc_versiones_sst
                    WHERE id_documento = ? AND version = ?";
            $ultimoDecimal = $this->db->query($sql, [$idDocumento, $versionActual])->getRow();
            $decimal = ($ultimoDecimal && $ultimoDecimal->max_decimal !== null) ? (int)$ultimoDecimal->max_decimal + 1 : 1;
            $proximaVersion = $versionActual . '.' . $decimal;
        }

        try {
            // Cambiar estado a borrador y guardar el motivo pendiente
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update([
                    'estado' => 'borrador',
                    'motivo_version' => $descripcionCambio,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // URL de redireccion a la pantalla de edicion
            $urlEdicion = base_url('documentos/generar/programa_capacitacion/' . $documento['id_cliente'] . '?anio=' . $documento['anio']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento listo para edicion. La proxima version sera v' . $proximaVersion,
                'proxima_version' => $proximaVersion,
                'tipo_cambio' => $tipoCambio,
                'redirect_url' => $urlEdicion
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al iniciar nueva version: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene el historial de versiones del documento
     */
    public function historialVersiones(int $idDocumento)
    {
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('fecha_autorizacion', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'versiones' => $versiones
        ]);
    }

    /**
     * Restaura una version anterior del documento
     */
    public function restaurarVersion()
    {
        $idVersion = $this->request->getPost('id_version');

        if (empty($idVersion)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de version no proporcionado'
            ]);
        }

        $version = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_version', $idVersion)
            ->get()
            ->getRowArray();

        if (!$version) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Version no encontrada'
            ]);
        }

        try {
            // Restaurar contenido del snapshot
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $version['id_documento'])
                ->update([
                    'contenido' => $version['contenido_snapshot'],
                    'estado' => 'borrador', // Vuelve a borrador para nueva revision
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Version restaurada. El documento esta ahora en estado borrador para revision.'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al restaurar version: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Descarga el PDF de una version especifica
     */
    public function descargarVersionPDF(int $idVersion)
    {
        $version = $this->db->table('tbl_doc_versiones_sst v')
            ->select('v.*, d.id_cliente, d.codigo, d.titulo, d.tipo_documento, d.anio')
            ->join('tbl_documentos_sst d', 'd.id_documento = v.id_documento')
            ->where('v.id_version', $idVersion)
            ->get()
            ->getRowArray();

        if (!$version) {
            return redirect()->back()->with('error', 'Version no encontrada');
        }

        $cliente = $this->clienteModel->find($version['id_cliente']);
        $contenido = json_decode($version['contenido_snapshot'], true);

        // Normalizar secciones
        if (!empty($contenido['secciones'])) {
            $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], $version['tipo_documento']);
        }

        // Preparar logo como base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // Obtener versiones hasta esta version
        $versiones = $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $version['id_documento'])
            ->where('fecha_autorizacion <=', $version['fecha_autorizacion'])
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener responsables del cliente
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($version['id_cliente']);

        // Obtener contexto SST
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($version['id_cliente']);

        // Obtener datos del consultor asignado
        $consultor = null;
        $firmaConsultorBase64 = '';
        $idConsultor = $contexto['id_consultor_responsable'] ?? $cliente['id_consultor'] ?? null;
        if ($idConsultor) {
            $consultorModel = new \App\Models\ConsultantModel();
            $consultor = $consultorModel->find($idConsultor);

            // Preparar firma del consultor como base64 para PDF
            if (!empty($consultor['firma_consultor'])) {
                $firmaPath = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaPath)) {
                    $firmaData = file_get_contents($firmaPath);
                    $firmaMime = mime_content_type($firmaPath);
                    $firmaConsultorBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode($firmaData);
                }
            }
        }

        $data = [
            'titulo' => $version['titulo'],
            'cliente' => $cliente,
            'documento' => [
                'codigo' => $version['codigo'],
                'version' => $version['version'],
                'created_at' => $version['fecha_autorizacion'],
                'estado' => 'aprobado'
            ],
            'contenido' => $contenido,
            'anio' => $version['anio'],
            'logoBase64' => $logoBase64,
            'esVersionHistorica' => true,
            'versionTexto' => $version['version_texto'],
            'versiones' => $versiones,
            'responsables' => $responsables,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmaConsultorBase64' => $firmaConsultorBase64
        ];

        $html = view('documentos_sst/pdf_template', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $nombreArchivo = $version['codigo'] . '_v' . $version['version_texto'] . '_' . url_title($version['titulo'], '-', true) . '.pdf';

        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    }
}
