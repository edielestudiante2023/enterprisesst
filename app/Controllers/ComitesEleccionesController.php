<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ComiteModel;
use App\Models\TipoComiteModel;
use Config\Database;

/**
 * Controlador para el Sistema de Conformación de Comités SST
 * Gestiona: COPASST, COCOLAB, Brigada de Emergencias, Vigía SST
 *
 * Flujos:
 * - COPASST/COCOLAB: Votación de trabajadores + Designación de empleador
 * - Brigada: Voluntariado + Designación directa
 * - Vigía: Designación directa (empresas 1-9 trabajadores)
 */
class ComitesEleccionesController extends BaseController
{
    protected $db;
    protected ClientModel $clienteModel;
    protected ComiteModel $comiteModel;
    protected TipoComiteModel $tipoComiteModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
        $this->comiteModel = new ComiteModel();
        $this->tipoComiteModel = new TipoComiteModel();
    }

    /**
     * Dashboard principal de procesos electorales/conformación
     * Muestra todos los procesos del cliente y permite crear nuevos
     */
    public function dashboard(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener todos los procesos del cliente
        $procesos = $this->db->table('tbl_procesos_electorales pe')
            ->select('pe.*, tc.nombre as nombre_comite')
            ->join('tbl_tipos_comite tc', 'pe.tipo_comite = tc.codigo', 'left')
            ->where('pe.id_cliente', $idCliente)
            ->orderBy('pe.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Obtener comités existentes del cliente
        $comitesExistentes = $this->db->table('tbl_comites c')
            ->select('c.*, tc.nombre as tipo_nombre')
            ->join('tbl_tipos_comite tc', 'c.id_tipo = tc.id_tipo', 'left')
            ->where('c.id_cliente', $idCliente)
            ->where('c.estado', 'activo')
            ->get()
            ->getResultArray();

        // Tipos de comité disponibles
        $tiposComite = $this->tipoComiteModel->where('activo', 1)->findAll();

        // Determinar si puede crear cada tipo de proceso (basado en número de trabajadores)
        $numTrabajadores = $cliente['trabajadores'] ?? 10;
        $puedeCrear = [
            'COPASST' => $numTrabajadores >= 10,
            'VIGIA' => $numTrabajadores < 10,
            'COCOLAB' => true, // Siempre disponible
            'BRIGADA' => true  // Siempre disponible
        ];

        return view('comites_elecciones/dashboard', [
            'cliente' => $cliente,
            'procesos' => $procesos,
            'comitesExistentes' => $comitesExistentes,
            'tiposComite' => $tiposComite,
            'puedeCrear' => $puedeCrear,
            'numTrabajadores' => $numTrabajadores
        ]);
    }

    /**
     * Formulario para crear nuevo proceso electoral/conformación
     */
    public function nuevoProceso(int $idCliente, string $tipoComite = null)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Validar tipo de comité
        $tiposValidos = ['COPASST', 'COCOLAB', 'BRIGADA', 'VIGIA'];
        if ($tipoComite && !in_array($tipoComite, $tiposValidos)) {
            return redirect()->back()->with('error', 'Tipo de comité no válido');
        }

        // Obtener número de trabajadores para determinar plazas
        $numTrabajadores = $cliente['trabajadores'] ?? 10;

        // Plazas según normativa (Resolución 2013 de 1986)
        $plazasPorDefecto = $this->calcularPlazas($numTrabajadores, $tipoComite);

        // Verificar si ya existe un proceso activo de este tipo
        $procesoExistente = null;
        if ($tipoComite) {
            $procesoExistente = $this->db->table('tbl_procesos_electorales')
                ->where('id_cliente', $idCliente)
                ->where('tipo_comite', $tipoComite)
                ->where('anio', date('Y'))
                ->whereNotIn('estado', ['completado', 'cancelado'])
                ->get()
                ->getRowArray();
        }

        return view('comites_elecciones/nuevo_proceso', [
            'cliente' => $cliente,
            'tipoComite' => $tipoComite,
            'numTrabajadores' => $numTrabajadores,
            'plazasPorDefecto' => $plazasPorDefecto,
            'procesoExistente' => $procesoExistente,
            'anioActual' => date('Y')
        ]);
    }

    /**
     * Guardar nuevo proceso electoral
     */
    public function guardarProceso()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipoComite = $this->request->getPost('tipo_comite');
        $anio = $this->request->getPost('anio') ?? date('Y');
        $plazasPrincipales = $this->request->getPost('plazas_principales') ?? 2;
        $plazasSuplentes = $this->request->getPost('plazas_suplentes') ?? 2;

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Validaciones
        if (!in_array($tipoComite, ['COPASST', 'COCOLAB', 'BRIGADA', 'VIGIA'])) {
            return redirect()->back()->with('error', 'Tipo de comité no válido');
        }

        // Verificar que no exista proceso activo
        $procesoExistente = $this->db->table('tbl_procesos_electorales')
            ->where('id_cliente', $idCliente)
            ->where('tipo_comite', $tipoComite)
            ->where('anio', $anio)
            ->whereNotIn('estado', ['completado', 'cancelado'])
            ->get()
            ->getRowArray();

        if ($procesoExistente) {
            return redirect()->back()->with('error', 'Ya existe un proceso activo de ' . $tipoComite . ' para el año ' . $anio);
        }

        // Crear o buscar comité
        $comite = $this->obtenerOCrearComite($idCliente, $tipoComite, $anio);

        // Estado inicial según tipo
        $estadoInicial = ($tipoComite === 'VIGIA' || $tipoComite === 'BRIGADA')
            ? 'designacion_empleador'
            : 'configuracion';

        // Período del comité (2 años)
        $fechaInicioPeriodo = $this->request->getPost('fecha_inicio_periodo') ?? date('Y-m-d');
        $fechaFinPeriodo = date('Y-m-d', strtotime($fechaInicioPeriodo . ' +2 years -1 day'));

        // Obtener consultor de sesión
        $idConsultor = session()->get('id_consultor') ?? session()->get('user_id');

        // Insertar proceso
        $this->db->table('tbl_procesos_electorales')->insert([
            'id_cliente' => $idCliente,
            'id_comite' => $comite['id_comite'] ?? null,
            'tipo_comite' => $tipoComite,
            'anio' => $anio,
            'estado' => $estadoInicial,
            'plazas_principales' => $plazasPrincipales,
            'plazas_suplentes' => $plazasSuplentes,
            'fecha_inicio_periodo' => $fechaInicioPeriodo,
            'fecha_fin_periodo' => $fechaFinPeriodo,
            'id_consultor' => $idConsultor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $idProceso = $this->db->insertID();

        return redirect()->to("/comites-elecciones/$idCliente/proceso/$idProceso")
            ->with('success', 'Proceso de conformación de ' . $tipoComite . ' creado exitosamente');
    }

    /**
     * Ver/Configurar proceso específico
     */
    public function verProceso(int $idCliente, int $idProceso)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Obtener candidatos del proceso (nueva tabla Fase 2)
        $candidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->orderBy('representacion', 'ASC')
            ->orderBy('tipo_plaza', 'ASC')
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        // Separar candidatos por representación
        $candidatosTrabajadores = array_values(array_filter($candidatos, fn($c) => $c['representacion'] === 'trabajador'));
        $candidatosEmpleador = array_values(array_filter($candidatos, fn($c) => $c['representacion'] === 'empleador'));

        // Obtener participantes del proceso (tabla legacy, mantener para compatibilidad)
        $participantes = $this->db->table('tbl_participantes_comite')
            ->where('id_proceso', $idProceso)
            ->orderBy('representacion', 'ASC')
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        // Separar por representación (legacy)
        $trabajadores = array_filter($participantes, fn($p) => $p['representacion'] === 'trabajador');
        $empleador = array_filter($participantes, fn($p) => $p['representacion'] === 'empleador');

        // Obtener jurados si aplica
        $jurados = [];
        if (in_array($proceso['tipo_comite'], ['COPASST', 'COCOLAB'])) {
            $jurados = $this->db->table('tbl_jurados_proceso')
                ->where('id_proceso', $idProceso)
                ->where('estado', 'activo')
                ->orderBy("FIELD(rol, 'presidente', 'secretario', 'escrutador', 'testigo')")
                ->get()
                ->getResultArray();
        }

        // Obtener voluntarios si es brigada
        $voluntarios = [];
        if ($proceso['tipo_comite'] === 'BRIGADA') {
            $voluntarios = $this->db->table('tbl_voluntarios_brigada')
                ->where('id_proceso', $idProceso)
                ->get()
                ->getResultArray();
        }

        // Estadísticas de votación
        $estadisticasVotacion = null;
        if ($proceso['estado'] === 'votacion' || $proceso['estado'] === 'escrutinio') {
            $estadisticasVotacion = $this->db->table('tbl_votos_eleccion')
                ->selectCount('id_voto', 'total_votos')
                ->where('id_proceso', $idProceso)
                ->get()
                ->getRowArray();
        }

        // Documentos del proceso
        $documentos = $this->db->table('tbl_documentos_proceso_electoral')
            ->where('id_proceso', $idProceso)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('comites_elecciones/ver_proceso', [
            'cliente' => $cliente,
            'proceso' => $proceso,
            'candidatos' => $candidatos,
            'candidatosTrabajadores' => $candidatosTrabajadores,
            'candidatosEmpleador' => $candidatosEmpleador,
            'participantes' => $participantes,
            'trabajadores' => $trabajadores,
            'empleador' => $empleador,
            'jurados' => $jurados,
            'voluntarios' => $voluntarios,
            'estadisticasVotacion' => $estadisticasVotacion,
            'documentos' => $documentos
        ]);
    }

    /**
     * Cambiar estado del proceso
     */
    public function cambiarEstado(int $idProceso, string $nuevoEstado)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Validar transición de estado
        $transicionesValidas = $this->getTransicionesValidas($proceso['estado']);
        if (!in_array($nuevoEstado, $transicionesValidas)) {
            return redirect()->back()->with('error', 'Transición de estado no válida');
        }

        // Acciones especiales según estado
        $datosActualizar = [
            'estado' => $nuevoEstado,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        switch ($nuevoEstado) {
            case 'votacion':
                // Verificar que haya candidatos aprobados (obligatorio)
                $totalCandidatos = $this->db->table('tbl_candidatos_comite')
                    ->where('id_proceso', $idProceso)
                    ->where('representacion', 'trabajador')
                    ->where('estado', 'aprobado')
                    ->countAllResults();

                if ($totalCandidatos === 0) {
                    return redirect()->back()->with('error', 'Debe haber al menos un candidato aprobado para iniciar la votacion.');
                }

                // Nota: Votantes pueden agregarse en cualquier momento durante la votación

                // Generar enlace de votación si no existe
                if (empty($proceso['enlace_votacion'])) {
                    $datosActualizar['enlace_votacion'] = bin2hex(random_bytes(16));
                    $datosActualizar['fecha_inicio_votacion'] = date('Y-m-d H:i:s');
                    $datosActualizar['fecha_fin_votacion'] = date('Y-m-d H:i:s', strtotime('+24 hours'));
                }
                break;
            case 'escrutinio':
                $datosActualizar['fecha_escrutinio'] = date('Y-m-d H:i:s');
                // Determinar elegidos automáticamente
                $this->determinarElegidos($idProceso, $proceso);
                break;
            case 'completado':
                $datosActualizar['fecha_completado'] = date('Y-m-d H:i:s');
                // Vincular miembros al comité real
                $this->vincularMiembrosComite($idProceso, $proceso);
                break;
        }

        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update($datosActualizar);

        return redirect()->back()->with('success', 'Estado actualizado a: ' . $nuevoEstado);
    }

    // =========================================================================
    // MÉTODOS AUXILIARES
    // =========================================================================

    /**
     * Calcular plazas según número de trabajadores y tipo de comité
     * Basado en Resolución 2013 de 1986 y normativa vigente
     */
    protected function calcularPlazas(int $numTrabajadores, ?string $tipoComite): array
    {
        // Para Vigía SST (1-9 trabajadores) no hay plazas múltiples
        if ($tipoComite === 'VIGIA') {
            return ['principales' => 1, 'suplentes' => 1];
        }

        // COPASST según tamaño de empresa
        if ($tipoComite === 'COPASST') {
            if ($numTrabajadores >= 10 && $numTrabajadores <= 49) {
                return ['principales' => 1, 'suplentes' => 1];
            } elseif ($numTrabajadores >= 50 && $numTrabajadores <= 499) {
                return ['principales' => 2, 'suplentes' => 2];
            } elseif ($numTrabajadores >= 500 && $numTrabajadores <= 999) {
                return ['principales' => 3, 'suplentes' => 3];
            } elseif ($numTrabajadores >= 1000) {
                return ['principales' => 4, 'suplentes' => 4];
            }
        }

        // COCOLAB: Resolución 652 de 2012 / 3461 de 2025
        if ($tipoComite === 'COCOLAB') {
            if ($numTrabajadores <= 19) {
                return ['principales' => 1, 'suplentes' => 1];
            } else {
                return ['principales' => 2, 'suplentes' => 2];
            }
        }

        // Brigada: Sin límite fijo, depende de necesidades
        if ($tipoComite === 'BRIGADA') {
            return ['principales' => 5, 'suplentes' => 0]; // Sugerido
        }

        // Por defecto
        return ['principales' => 2, 'suplentes' => 2];
    }

    /**
     * Obtener o crear comité para el proceso
     */
    protected function obtenerOCrearComite(int $idCliente, string $tipoComite, int $anio): ?array
    {
        // Buscar tipo de comité
        $tipo = $this->tipoComiteModel->where('codigo', $tipoComite)->first();
        if (!$tipo) {
            return null;
        }

        // Buscar comité existente activo
        $comite = $this->db->table('tbl_comites')
            ->where('id_cliente', $idCliente)
            ->where('id_tipo', $tipo['id_tipo'])
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$comite) {
            // Crear nuevo comité
            $this->db->table('tbl_comites')->insert([
                'id_cliente' => $idCliente,
                'id_tipo' => $tipo['id_tipo'],
                'estado' => 'activo',
                'fecha_conformacion' => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+2 years -1 day')),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $comite = [
                'id_comite' => $this->db->insertID()
            ];
        }

        return $comite;
    }

    /**
     * Obtener transiciones de estado válidas
     */
    protected function getTransicionesValidas(string $estadoActual): array
    {
        $transiciones = [
            'configuracion' => ['inscripcion', 'cancelado'],
            'inscripcion' => ['votacion', 'configuracion', 'cancelado'],
            'votacion' => ['escrutinio', 'cancelado'],
            'escrutinio' => ['designacion_empleador', 'cancelado'],
            'designacion_empleador' => ['firmas', 'cancelado'],
            'firmas' => ['completado', 'cancelado'],
            'completado' => [],
            'cancelado' => ['configuracion'] // Permite reactivar
        ];

        return $transiciones[$estadoActual] ?? [];
    }

    /**
     * Obtener etiqueta amigable de estado
     */
    public static function getEtiquetaEstado(string $estado): array
    {
        $etiquetas = [
            'configuracion' => ['texto' => 'Configuración', 'clase' => 'bg-secondary'],
            'inscripcion' => ['texto' => 'Inscripción de Candidatos', 'clase' => 'bg-info'],
            'votacion' => ['texto' => 'Votación en Curso', 'clase' => 'bg-primary'],
            'escrutinio' => ['texto' => 'Escrutinio', 'clase' => 'bg-warning text-dark'],
            'designacion_empleador' => ['texto' => 'Designación Empleador', 'clase' => 'bg-info'],
            'firmas' => ['texto' => 'Pendiente de Firmas', 'clase' => 'bg-warning text-dark'],
            'completado' => ['texto' => 'Completado', 'clase' => 'bg-success'],
            'cancelado' => ['texto' => 'Cancelado', 'clase' => 'bg-danger']
        ];

        return $etiquetas[$estado] ?? ['texto' => $estado, 'clase' => 'bg-secondary'];
    }

    // =========================================================================
    // FASE 2: INSCRIPCIÓN DE CANDIDATOS
    // =========================================================================

    /**
     * Mostrar formulario de inscripción de candidato
     */
    public function inscribirCandidato(int $idProceso, string $representacion = 'trabajador')
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Validar que el proceso esté en estado de inscripción, votación (o designación para empleador)
        $estadosPermitidos = ['inscripcion', 'configuracion', 'votacion'];
        if ($representacion === 'empleador') {
            $estadosPermitidos[] = 'designacion_empleador';
        }

        if (!in_array($proceso['estado'], $estadosPermitidos)) {
            return redirect()->back()->with('error', 'El proceso no está en etapa de inscripción');
        }

        // Validar representación
        if (!in_array($representacion, ['trabajador', 'empleador'])) {
            $representacion = 'trabajador';
        }

        // Obtener candidatos ya inscritos para este proceso y representación
        $candidatosInscritos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', $representacion)
            ->whereNotIn('estado', ['rechazado'])
            ->get()
            ->getResultArray();

        // Calcular plazas disponibles
        $plazasTotal = ($proceso['plazas_principales'] + $proceso['plazas_suplentes']);
        $plazasOcupadas = count($candidatosInscritos);
        $plazasDisponibles = $plazasTotal - $plazasOcupadas;

        // Determinar si requiere certificado 50h
        $requiereCertificado50h = in_array($proceso['tipo_comite'], ['COPASST', 'VIGIA']);

        return view('comites_elecciones/inscribir_candidato', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'representacion' => $representacion,
            'candidatosInscritos' => $candidatosInscritos,
            'plazasTotal' => $plazasTotal,
            'plazasDisponibles' => $plazasDisponibles,
            'requiereCertificado50h' => $requiereCertificado50h
        ]);
    }

    /**
     * Guardar candidato con foto
     */
    public function guardarCandidato()
    {
        $idProceso = $this->request->getPost('id_proceso');
        $representacion = $this->request->getPost('representacion');

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Validar estado (incluye votacion para permitir reemplazos durante votación)
        $estadosPermitidos = ['inscripcion', 'configuracion', 'votacion'];
        if ($representacion === 'empleador') {
            $estadosPermitidos[] = 'designacion_empleador';
        }

        if (!in_array($proceso['estado'], $estadosPermitidos)) {
            return redirect()->back()->with('error', 'El proceso no permite inscripciones en este estado');
        }

        // Validar documento único en el proceso
        $documentoIdentidad = $this->request->getPost('documento_identidad');
        $existente = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('documento_identidad', $documentoIdentidad)
            ->get()
            ->getRowArray();

        if ($existente) {
            return redirect()->back()
                ->with('error', 'Ya existe un candidato con este documento de identidad en el proceso')
                ->withInput();
        }

        // Procesar foto del candidato
        $rutaFoto = null;
        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $nombreFoto = 'candidato_' . $idProceso . '_' . $documentoIdentidad . '_' . time() . '.' . $foto->getExtension();
            $rutaDestino = FCPATH . 'uploads/candidatos/' . $proceso['id_cliente'] . '/';

            if (!is_dir($rutaDestino)) {
                mkdir($rutaDestino, 0755, true);
            }

            $foto->move($rutaDestino, $nombreFoto);
            $rutaFoto = 'uploads/candidatos/' . $proceso['id_cliente'] . '/' . $nombreFoto;
        }

        // Procesar certificado 50 horas (si aplica)
        $rutaCertificado = null;
        $certificado = $this->request->getFile('archivo_certificado_50h');
        if ($certificado && $certificado->isValid() && !$certificado->hasMoved()) {
            $nombreCert = 'cert50h_' . $documentoIdentidad . '_' . time() . '.' . $certificado->getExtension();
            $rutaCert = FCPATH . 'uploads/certificados/' . $proceso['id_cliente'] . '/';

            if (!is_dir($rutaCert)) {
                mkdir($rutaCert, 0755, true);
            }

            $certificado->move($rutaCert, $nombreCert);
            $rutaCertificado = 'uploads/certificados/' . $proceso['id_cliente'] . '/' . $nombreCert;
        }

        // Determinar estado inicial
        // Para representantes del empleador: directo a 'designado'
        // Para trabajadores: directo a 'aprobado' (listo para votación)
        // Nota: El certificado 50h se verifica DESPUÉS de ser elegido, no al inscribirse
        $estadoInicial = 'aprobado'; // Candidatos quedan listos para votación
        if ($representacion === 'empleador') {
            $estadoInicial = 'designado';
        } elseif (in_array($proceso['tipo_comite'], ['BRIGADA', 'VIGIA'])) {
            $estadoInicial = 'designado'; // Sin votación, designación directa
        }

        // Insertar candidato
        $this->db->table('tbl_candidatos_comite')->insert([
            'id_proceso' => $idProceso,
            'id_cliente' => $proceso['id_cliente'],
            'nombres' => $this->request->getPost('nombres'),
            'apellidos' => $this->request->getPost('apellidos'),
            'documento_identidad' => $documentoIdentidad,
            'tipo_documento' => $this->request->getPost('tipo_documento') ?? 'CC',
            'cargo' => $this->request->getPost('cargo'),
            'area' => $this->request->getPost('area'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'foto' => $rutaFoto,
            'representacion' => $representacion,
            'tipo_plaza' => $this->request->getPost('tipo_plaza') ?? 'principal',
            'estado' => $estadoInicial,
            'tiene_certificado_50h' => $this->request->getPost('tiene_certificado_50h') ? 1 : 0,
            'archivo_certificado_50h' => $rutaCertificado,
            'fecha_certificado_50h' => $this->request->getPost('fecha_certificado_50h') ?: null,
            'institucion_certificado' => $this->request->getPost('institucion_certificado'),
            'observaciones' => $this->request->getPost('observaciones'),
            'inscrito_por' => session()->get('user_id'),
            'fecha_inscripcion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $tipoTexto = $representacion === 'empleador' ? 'Representante del empleador' : 'Candidato';
        return redirect()->to("/comites-elecciones/{$proceso['id_cliente']}/proceso/{$idProceso}")
            ->with('success', "$tipoTexto inscrito exitosamente");
    }

    /**
     * Obtener lista de candidatos por proceso (AJAX)
     */
    public function listaCandidatos(int $idProceso)
    {
        $candidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->orderBy('representacion', 'ASC')
            ->orderBy('tipo_plaza', 'ASC')
            ->orderBy('apellidos', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'candidatos' => $candidatos
        ]);
    }

    /**
     * Aprobar candidato inscrito
     */
    public function aprobarCandidato(int $idCandidato)
    {
        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return redirect()->back()->with('error', 'Candidato no encontrado');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $candidato['id_proceso'])
            ->get()
            ->getRowArray();

        // Validar certificado 50h para COPASST
        if (in_array($proceso['tipo_comite'], ['COPASST', 'VIGIA'])) {
            if (!$candidato['tiene_certificado_50h']) {
                return redirect()->back()->with('error', 'El candidato requiere certificado de 50 horas SST para ' . $proceso['tipo_comite']);
            }
        }

        $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->update([
                'estado' => 'aprobado',
                'fecha_aprobacion' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->back()->with('success', 'Candidato aprobado exitosamente');
    }

    /**
     * Rechazar candidato inscrito
     */
    public function rechazarCandidato(int $idCandidato)
    {
        $motivo = $this->request->getPost('motivo_rechazo') ?? 'Sin especificar';

        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return redirect()->back()->with('error', 'Candidato no encontrado');
        }

        $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->update([
                'estado' => 'rechazado',
                'motivo_rechazo' => $motivo,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->back()->with('success', 'Candidato rechazado');
    }

    /**
     * Eliminar candidato (solo si no ha sido elegido)
     */
    public function eliminarCandidato(int $idCandidato)
    {
        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return redirect()->back()->with('error', 'Candidato no encontrado');
        }

        // Solo permitir eliminar si no ha sido elegido
        if (in_array($candidato['estado'], ['elegido', 'designado'])) {
            return redirect()->back()->with('error', 'No se puede eliminar un candidato elegido o designado');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $candidato['id_proceso'])
            ->get()
            ->getRowArray();

        // Si el candidato tiene votos, limpiarlos antes de eliminar
        if (!empty($candidato['votos_obtenidos']) && $candidato['votos_obtenidos'] > 0) {
            $this->db->table('tbl_votos_comite')
                ->where('id_candidato', $idCandidato)
                ->delete();

            $this->db->table('tbl_procesos_electorales')
                ->where('id_proceso', $candidato['id_proceso'])
                ->set('votos_emitidos', 'votos_emitidos - ' . (int)$candidato['votos_obtenidos'], false)
                ->update();
        }

        // Eliminar foto si existe
        if ($candidato['foto'] && file_exists(FCPATH . $candidato['foto'])) {
            unlink(FCPATH . $candidato['foto']);
        }

        // Eliminar certificado si existe
        if ($candidato['archivo_certificado_50h'] && file_exists(FCPATH . $candidato['archivo_certificado_50h'])) {
            unlink(FCPATH . $candidato['archivo_certificado_50h']);
        }

        $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->delete();

        return redirect()->to("/comites-elecciones/{$proceso['id_cliente']}/proceso/{$proceso['id_proceso']}")
            ->with('success', 'Candidato eliminado');
    }

    /**
     * Ver detalle de candidato (AJAX)
     */
    public function verCandidato(int $idCandidato)
    {
        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return $this->response->setJSON(['success' => false, 'message' => 'Candidato no encontrado']);
        }

        return $this->response->setJSON([
            'success' => true,
            'candidato' => $candidato
        ]);
    }

    /**
     * Editar candidato
     */
    public function editarCandidato(int $idCandidato)
    {
        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return redirect()->back()->with('error', 'Candidato no encontrado');
        }

        // No permitir editar si ya fue elegido
        if (in_array($candidato['estado'], ['elegido', 'no_elegido'])) {
            return redirect()->back()->with('error', 'No se puede editar un candidato despues del escrutinio');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $candidato['id_proceso'])
            ->get()
            ->getRowArray();

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        return view('comites_elecciones/editar_candidato', [
            'candidato' => $candidato,
            'proceso' => $proceso,
            'cliente' => $cliente,
            'requiereCertificado50h' => in_array($proceso['tipo_comite'], ['COPASST', 'VIGIA'])
        ]);
    }

    /**
     * Actualizar candidato
     */
    public function actualizarCandidato(int $idCandidato)
    {
        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return redirect()->back()->with('error', 'Candidato no encontrado');
        }

        // No permitir editar si ya fue elegido
        if (in_array($candidato['estado'], ['elegido', 'no_elegido'])) {
            return redirect()->back()->with('error', 'No se puede editar un candidato despues del escrutinio');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $candidato['id_proceso'])
            ->get()
            ->getRowArray();

        // Procesar nueva foto si se subio
        $rutaFoto = $candidato['foto'];
        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            // Eliminar foto anterior
            if ($candidato['foto'] && file_exists(FCPATH . $candidato['foto'])) {
                unlink(FCPATH . $candidato['foto']);
            }

            $nombreFoto = 'candidato_' . $proceso['id_proceso'] . '_' . $candidato['documento_identidad'] . '_' . time() . '.' . $foto->getExtension();
            $rutaDestino = FCPATH . 'uploads/candidatos/' . $proceso['id_cliente'] . '/';

            if (!is_dir($rutaDestino)) {
                mkdir($rutaDestino, 0755, true);
            }

            $foto->move($rutaDestino, $nombreFoto);
            $rutaFoto = 'uploads/candidatos/' . $proceso['id_cliente'] . '/' . $nombreFoto;
        }

        // Actualizar datos
        $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->update([
                'nombres' => $this->request->getPost('nombres'),
                'apellidos' => $this->request->getPost('apellidos'),
                'cargo' => $this->request->getPost('cargo'),
                'area' => $this->request->getPost('area'),
                'email' => $this->request->getPost('email'),
                'telefono' => $this->request->getPost('telefono'),
                'foto' => $rutaFoto,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->to("/comites-elecciones/{$proceso['id_cliente']}/proceso/{$proceso['id_proceso']}")
            ->with('success', 'Candidato actualizado correctamente');
    }

    // =========================================================================
    // FASE 3: SISTEMA DE VOTACION ELECTRONICA
    // =========================================================================

    /**
     * Iniciar periodo de votacion - genera enlace y configura fechas
     */
    public function iniciarVotacion(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Verificar que haya candidatos aprobados (esto sí es obligatorio)
        $totalCandidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->where('estado', 'aprobado')
            ->countAllResults();

        if ($totalCandidatos === 0) {
            return redirect()->back()->with('error', 'Debe haber al menos un candidato aprobado para iniciar la votacion.');
        }

        // Nota: Los votantes pueden agregarse en cualquier momento, incluso durante la votación

        // Generar codigo unico para enlace de votacion
        $codigoVotacion = bin2hex(random_bytes(16));

        // Configurar periodo de votacion (24 horas por defecto)
        $fechaInicio = date('Y-m-d H:i:s');
        $fechaFin = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update([
                'estado' => 'votacion',
                'enlace_votacion' => $codigoVotacion,
                'fecha_inicio_votacion' => $fechaInicio,
                'fecha_fin_votacion' => $fechaFin,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->back()->with('success', 'Votacion iniciada. Enlace generado.');
    }

    /**
     * Gestionar censo de votantes
     */
    public function censovotantes(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Obtener votantes registrados
        $votantes = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->orderBy('apellidos', 'ASC')
            ->get()
            ->getResultArray();

        // Estadisticas
        $totalVotantes = count($votantes);
        $yaVotaron = count(array_filter($votantes, fn($v) => $v['ha_votado']));

        return view('comites_elecciones/censo_votantes', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'votantes' => $votantes,
            'totalVotantes' => $totalVotantes,
            'yaVotaron' => $yaVotaron
        ]);
    }

    /**
     * Agregar votante al censo
     */
    public function agregarVotante()
    {
        $idProceso = $this->request->getPost('id_proceso');

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $documento = $this->request->getPost('documento_identidad');

        // Verificar que no exista
        $existe = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->where('documento_identidad', $documento)
            ->get()
            ->getRowArray();

        if ($existe) {
            return redirect()->back()->with('error', 'Este votante ya esta registrado');
        }

        // Generar token unico
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $this->db->table('tbl_votantes_proceso')->insert([
            'id_proceso' => $idProceso,
            'id_cliente' => $proceso['id_cliente'],
            'nombres' => $this->request->getPost('nombres'),
            'apellidos' => $this->request->getPost('apellidos'),
            'documento_identidad' => $documento,
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'cargo' => $this->request->getPost('cargo'),
            'area' => $this->request->getPost('area'),
            'token_acceso' => $token,
            'token_expira' => $expira,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Actualizar contador
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->set('total_votantes', 'total_votantes + 1', false)
            ->update();

        return redirect()->back()->with('success', 'Votante agregado al censo');
    }

    /**
     * Importar votantes desde CSV o lista
     */
    public function importarVotantes()
    {
        $idProceso = $this->request->getPost('id_proceso');
        $lista = $this->request->getPost('lista_votantes');

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Parsear lista (formato: documento;nombres;apellidos;email;cargo)
        $lineas = explode("\n", trim($lista));
        $importados = 0;
        $errores = 0;

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            $campos = str_getcsv($linea, ';');
            if (count($campos) < 3) {
                $errores++;
                continue;
            }

            $documento = trim($campos[0]);
            $nombres = trim($campos[1]);
            $apellidos = trim($campos[2]);
            $email = isset($campos[3]) ? trim($campos[3]) : null;
            $cargo = isset($campos[4]) ? trim($campos[4]) : null;

            // Verificar que no exista
            $existe = $this->db->table('tbl_votantes_proceso')
                ->where('id_proceso', $idProceso)
                ->where('documento_identidad', $documento)
                ->get()
                ->getRowArray();

            if ($existe) {
                $errores++;
                continue;
            }

            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+48 hours'));

            $this->db->table('tbl_votantes_proceso')->insert([
                'id_proceso' => $idProceso,
                'id_cliente' => $proceso['id_cliente'],
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'documento_identidad' => $documento,
                'email' => $email,
                'cargo' => $cargo,
                'token_acceso' => $token,
                'token_expira' => $expira,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $importados++;
        }

        // Actualizar contador total
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->set('total_votantes', 'total_votantes + ' . $importados, false)
            ->update();

        return redirect()->back()->with('success', "Importados: $importados votantes. Errores/duplicados: $errores");
    }

    /**
     * Descargar plantilla CSV para importar votantes
     */
    public function descargarPlantillaCSV(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Crear contenido CSV con BOM para Excel
        $bom = "\xEF\xBB\xBF"; // UTF-8 BOM
        $contenido = $bom;
        $contenido .= "documento;nombres;apellidos;email;cargo;area\n";
        $contenido .= "1234567890;Juan Carlos;Rodriguez Perez;juan.rodriguez@empresa.com;Operario;Produccion\n";
        $contenido .= "1234567891;Maria Elena;Lopez Garcia;maria.lopez@empresa.com;Auxiliar;Administrativa\n";
        $contenido .= "1234567892;Pedro Antonio;Martinez Silva;pedro.martinez@empresa.com;Tecnico;Mantenimiento\n";

        $nombreArchivo = 'plantilla_votantes_' . $proceso['tipo_comite'] . '_' . date('Y') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
            ->setBody($contenido);
    }

    /**
     * Importar votantes desde archivo CSV
     */
    public function importarCSV()
    {
        $idProceso = $this->request->getPost('id_proceso');
        $separador = $this->request->getPost('separador') ?: ';';
        $tieneEncabezado = $this->request->getPost('tiene_encabezado') ? true : false;

        if ($separador === '\\t') {
            $separador = "\t";
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $archivo = $this->request->getFile('archivo_csv');

        if (!$archivo || !$archivo->isValid()) {
            return redirect()->back()->with('error', 'Archivo no valido o no se pudo subir');
        }

        // Leer contenido del archivo
        $contenido = file_get_contents($archivo->getTempName());

        // Detectar y remover BOM si existe
        $bom = pack('H*', 'EFBBBF');
        $contenido = preg_replace("/^$bom/", '', $contenido);

        // Convertir a UTF-8 si es necesario
        if (!mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
        }

        $lineas = preg_split('/\r\n|\r|\n/', $contenido);
        $importados = 0;
        $errores = 0;
        $detallesErrores = [];

        $tokenExpira = date('Y-m-d H:i:s', strtotime('+7 days'));

        foreach ($lineas as $numLinea => $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            // Saltar encabezado si corresponde
            if ($tieneEncabezado && $numLinea === 0) {
                continue;
            }

            $campos = str_getcsv($linea, $separador);

            if (count($campos) < 3) {
                $errores++;
                $detallesErrores[] = "Linea " . ($numLinea + 1) . ": formato incorrecto";
                continue;
            }

            $documento = trim($campos[0] ?? '');
            $nombres = trim($campos[1] ?? '');
            $apellidos = trim($campos[2] ?? '');
            $email = trim($campos[3] ?? '');
            $cargo = trim($campos[4] ?? '');
            $area = trim($campos[5] ?? '');

            // Validar documento
            if (empty($documento) || !preg_match('/^[0-9]+$/', $documento)) {
                $errores++;
                $detallesErrores[] = "Linea " . ($numLinea + 1) . ": documento invalido '$documento'";
                continue;
            }

            // Validar nombres
            if (empty($nombres) || empty($apellidos)) {
                $errores++;
                $detallesErrores[] = "Linea " . ($numLinea + 1) . ": nombres o apellidos vacios";
                continue;
            }

            // Verificar duplicado
            $existe = $this->db->table('tbl_votantes_proceso')
                ->where('id_proceso', $idProceso)
                ->where('documento_identidad', $documento)
                ->get()
                ->getRowArray();

            if ($existe) {
                $errores++;
                $detallesErrores[] = "Linea " . ($numLinea + 1) . ": documento $documento ya existe";
                continue;
            }

            // Insertar votante
            $token = bin2hex(random_bytes(16));

            $this->db->table('tbl_votantes_proceso')->insert([
                'id_proceso' => $idProceso,
                'id_cliente' => $proceso['id_cliente'],
                'documento_identidad' => $documento,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'email' => $email ?: null,
                'cargo' => $cargo ?: null,
                'area' => $area ?: null,
                'token_acceso' => $token,
                'token_expira' => $tokenExpira,
                'ha_votado' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $importados++;
        }

        // Actualizar contador
        $total = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->countAllResults();

        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update(['total_votantes' => $total]);

        // Mensaje de resultado
        $mensaje = "Importacion completada: $importados votantes agregados.";
        if ($errores > 0) {
            $mensaje .= " $errores errores encontrados.";
            if (count($detallesErrores) <= 5) {
                $mensaje .= " Detalles: " . implode('; ', $detallesErrores);
            }
        }

        return redirect()->back()->with($errores > 0 ? 'warning' : 'success', $mensaje);
    }

    /**
     * Enviar enlace de votacion por email a UN votante
     */
    public function enviarEnlaceVotante(int $idVotante)
    {
        $votante = $this->db->table('tbl_votantes_proceso')
            ->where('id_votante', $idVotante)
            ->get()
            ->getRowArray();

        if (!$votante) {
            return redirect()->back()->with('error', 'Votante no encontrado');
        }

        if (empty($votante['email'])) {
            return redirect()->back()->with('error', 'El votante no tiene email registrado');
        }

        if ($votante['ha_votado']) {
            return redirect()->back()->with('warning', 'Este votante ya emitio su voto');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $votante['id_proceso'])
            ->get()
            ->getRowArray();

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        $resultado = $this->enviarEmailVotacion($votante, $proceso, $cliente);

        if ($resultado['success']) {
            // Registrar envio
            $this->db->table('tbl_votantes_proceso')
                ->where('id_votante', $idVotante)
                ->update(['email_enviado' => 1, 'fecha_email' => date('Y-m-d H:i:s')]);

            return redirect()->back()->with('success', 'Email enviado a ' . $votante['email']);
        } else {
            return redirect()->back()->with('error', 'Error enviando email: ' . $resultado['error']);
        }
    }

    /**
     * Enviar enlaces de votacion a TODOS los votantes pendientes
     */
    public function enviarEnlacesTodos(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Obtener votantes que no han votado y tienen email
        $votantes = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->where('ha_votado', 0)
            ->where('email IS NOT NULL', null, false)
            ->where('email !=', '')
            ->get()
            ->getResultArray();

        if (empty($votantes)) {
            return redirect()->back()->with('warning', 'No hay votantes pendientes con email para notificar');
        }

        $enviados = 0;
        $errores = 0;

        foreach ($votantes as $votante) {
            $resultado = $this->enviarEmailVotacion($votante, $proceso, $cliente);

            if ($resultado['success']) {
                $this->db->table('tbl_votantes_proceso')
                    ->where('id_votante', $votante['id_votante'])
                    ->update(['email_enviado' => 1, 'fecha_email' => date('Y-m-d H:i:s')]);
                $enviados++;
            } else {
                $errores++;
            }

            // Pequeña pausa para no saturar SendGrid
            usleep(100000); // 0.1 segundos
        }

        $mensaje = "Emails enviados: $enviados";
        if ($errores > 0) {
            $mensaje .= ". Errores: $errores";
        }

        return redirect()->back()->with($errores > 0 ? 'warning' : 'success', $mensaje);
    }

    /**
     * Metodo interno para enviar email de votacion via SendGrid
     */
    private function enviarEmailVotacion($votante, $proceso, $cliente): array
    {
        $sendgridApiKey = getenv('SENDGRID_API_KEY');

        if (empty($sendgridApiKey) || $sendgridApiKey === 'SG.xxxxxx') {
            return ['success' => false, 'error' => 'SendGrid API Key no configurada'];
        }

        $enlaceVotacion = base_url('votar/emitir/' . $votante['token_acceso']);
        $fechaLimite = date('d/m/Y H:i', strtotime($proceso['fecha_fin_votacion']));

        $htmlContent = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #0d6efd; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>Proceso Electoral {$proceso['tipo_comite']} {$proceso['anio']}</h1>
                <p style='margin: 10px 0 0;'>{$cliente['nombre_cliente']}</p>
            </div>

            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado(a) <strong>{$votante['nombres']} {$votante['apellidos']}</strong>,</p>

                <p>Ha sido habilitado(a) para participar en el proceso electoral de conformacion del <strong>{$proceso['tipo_comite']}</strong>.</p>

                <p>Para emitir su voto, haga clic en el siguiente boton:</p>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$enlaceVotacion}'
                       style='background: #0d6efd; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-size: 18px; display: inline-block;'>
                        VOTAR AHORA
                    </a>
                </div>

                <p style='color: #666; font-size: 14px;'>
                    <strong>Importante:</strong><br>
                    - Este enlace es personal e intransferible<br>
                    - Solo puede votar una vez<br>
                    - Fecha limite: <strong>{$fechaLimite}</strong>
                </p>

                <p style='color: #999; font-size: 12px; margin-top: 20px;'>
                    Si el boton no funciona, copie y pegue este enlace en su navegador:<br>
                    <a href='{$enlaceVotacion}'>{$enlaceVotacion}</a>
                </p>
            </div>

            <div style='background: #333; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p style='margin: 0;'>Este es un mensaje automatico del sistema de votacion electronica</p>
                <p style='margin: 5px 0 0;'>Su voto es secreto y anonimo</p>
            </div>
        </div>";

        $emailData = [
            'personalizations' => [
                [
                    'to' => [
                        ['email' => $votante['email'], 'name' => $votante['nombres'] . ' ' . $votante['apellidos']]
                    ],
                    'subject' => "Votacion {$proceso['tipo_comite']} - {$cliente['nombre_cliente']}"
                ]
            ],
            'from' => [
                'email' => 'no-reply@cycloidtalent.com',
                'name' => 'Sistema Electoral SST'
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlContent]
            ]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $sendgridApiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => "HTTP $httpCode: $response"];
        }
    }

    /**
     * Pagina de acceso a votacion (sin autenticacion de sesion)
     * El votante ingresa su documento de identidad para acceder
     */
    public function votarAcceso(string $enlace)
    {
        // Buscar proceso por enlace de votacion
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('enlace_votacion', $enlace)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'Enlace de votacion invalido.',
                'tipo' => 'invalido'
            ]);
        }

        if ($proceso['estado'] !== 'votacion') {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'El proceso de votacion no esta activo.',
                'tipo' => 'no_activo'
            ]);
        }

        // Verificar periodo de votacion
        $ahora = time();
        $inicio = strtotime($proceso['fecha_inicio_votacion']);
        $fin = strtotime($proceso['fecha_fin_votacion']);

        if ($ahora < $inicio) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'La votacion aun no ha comenzado. Inicia el ' . date('d/m/Y H:i', $inicio),
                'tipo' => 'no_iniciado'
            ]);
        }

        if ($ahora > $fin) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'El periodo de votacion ha finalizado.',
                'tipo' => 'finalizado'
            ]);
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        return view('comites_elecciones/acceso_votacion', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'enlace' => $enlace
        ]);
    }

    /**
     * Validar documento del votante y redirigir a votacion
     */
    public function validarVotante()
    {
        $enlace = $this->request->getPost('enlace');
        $documento = trim($this->request->getPost('documento'));

        // Buscar proceso
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('enlace_votacion', $enlace)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado.');
        }

        // Buscar votante en el censo
        $votante = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $proceso['id_proceso'])
            ->where('documento_identidad', $documento)
            ->get()
            ->getRowArray();

        if (!$votante) {
            return redirect()->back()->with('error', 'Su documento no se encuentra en el censo electoral de este proceso. Contacte al administrador.');
        }

        // Verificar si ya voto
        if ($votante['ha_votado']) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'Usted ya ha ejercido su derecho al voto en este proceso.',
                'tipo' => 'ya_voto'
            ]);
        }

        // Redirigir a la pagina de votacion con su token individual
        return redirect()->to(base_url('votar/emitir/' . $votante['token_acceso']));
    }

    /**
     * Pagina publica de votacion (sin autenticacion de sesion)
     */
    public function votarPublico(string $token)
    {
        // Buscar votante por token
        $votante = $this->db->table('tbl_votantes_proceso')
            ->where('token_acceso', $token)
            ->get()
            ->getRowArray();

        if (!$votante) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'Enlace de votacion invalido o expirado.',
                'tipo' => 'invalido'
            ]);
        }

        // Verificar si ya voto
        if ($votante['ha_votado']) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'Usted ya ha ejercido su derecho al voto en este proceso.',
                'tipo' => 'ya_voto'
            ]);
        }

        // Verificar expiracion del token
        if (strtotime($votante['token_expira']) < time()) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'El enlace de votacion ha expirado.',
                'tipo' => 'expirado'
            ]);
        }

        // Obtener proceso
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $votante['id_proceso'])
            ->get()
            ->getRowArray();

        if (!$proceso || $proceso['estado'] !== 'votacion') {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'El proceso de votacion no esta activo.',
                'tipo' => 'no_activo'
            ]);
        }

        // Verificar periodo de votacion
        $ahora = time();
        $inicio = strtotime($proceso['fecha_inicio_votacion']);
        $fin = strtotime($proceso['fecha_fin_votacion']);

        if ($ahora < $inicio) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'La votacion aun no ha comenzado. Inicia el ' . date('d/m/Y H:i', $inicio),
                'tipo' => 'no_iniciado'
            ]);
        }

        if ($ahora > $fin) {
            return view('comites_elecciones/error_votacion', [
                'mensaje' => 'El periodo de votacion ha finalizado.',
                'tipo' => 'finalizado'
            ]);
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Obtener candidatos aprobados para votar
        $candidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $proceso['id_proceso'])
            ->where('representacion', 'trabajador')
            ->where('estado', 'aprobado')
            ->orderBy('apellidos', 'ASC')
            ->get()
            ->getResultArray();

        return view('comites_elecciones/votar', [
            'votante' => $votante,
            'proceso' => $proceso,
            'cliente' => $cliente,
            'candidatos' => $candidatos,
            'token' => $token
        ]);
    }

    /**
     * Registrar voto (POST desde pagina publica)
     */
    public function registrarVoto()
    {
        $token = $this->request->getPost('token');
        $idCandidato = $this->request->getPost('id_candidato');

        // Validar votante
        $votante = $this->db->table('tbl_votantes_proceso')
            ->where('token_acceso', $token)
            ->get()
            ->getRowArray();

        if (!$votante || $votante['ha_votado']) {
            return redirect()->to('/votar/' . $token)->with('error', 'Voto no valido');
        }

        // Validar candidato
        $candidato = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->where('id_proceso', $votante['id_proceso'])
            ->where('estado', 'aprobado')
            ->get()
            ->getRowArray();

        if (!$candidato) {
            return redirect()->to('/votar/' . $token)->with('error', 'Candidato no valido');
        }

        // Generar hash anonimo del votante
        $hashVotante = hash('sha256', $votante['id_votante'] . $votante['documento_identidad'] . $votante['id_proceso']);

        // Registrar voto anonimo
        $this->db->table('tbl_votos_comite')->insert([
            'id_proceso' => $votante['id_proceso'],
            'id_candidato' => $idCandidato,
            'hash_votante' => $hashVotante,
            'fecha_voto' => date('Y-m-d H:i:s'),
            'ip_origen' => $this->request->getIPAddress()
        ]);

        // Marcar votante como que ya voto
        $this->db->table('tbl_votantes_proceso')
            ->where('id_votante', $votante['id_votante'])
            ->update([
                'ha_votado' => 1,
                'fecha_voto' => date('Y-m-d H:i:s'),
                'ip_voto' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

        // Actualizar conteo de candidato
        $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $idCandidato)
            ->set('votos_obtenidos', 'votos_obtenidos + 1', false)
            ->update();

        // Actualizar contador del proceso
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $votante['id_proceso'])
            ->set('votos_emitidos', 'votos_emitidos + 1', false)
            ->update();

        return view('comites_elecciones/voto_exitoso', [
            'votante' => $votante
        ]);
    }

    /**
     * Ver resultados de votacion
     */
    public function resultadosVotacion(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Obtener candidatos con votos
        $candidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->whereIn('estado', ['aprobado', 'elegido', 'no_elegido'])
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        // Calcular porcentajes
        $totalVotos = array_sum(array_column($candidatos, 'votos_obtenidos'));
        foreach ($candidatos as &$c) {
            $c['porcentaje'] = $totalVotos > 0 ? round(($c['votos_obtenidos'] / $totalVotos) * 100, 2) : 0;
        }

        // Estadisticas de participacion
        $totalVotantes = $proceso['total_votantes'] ?? 0;
        $votosEmitidos = $proceso['votos_emitidos'] ?? 0;
        $participacion = $totalVotantes > 0 ? round(($votosEmitidos / $totalVotantes) * 100, 2) : 0;

        return view('comites_elecciones/resultados_votacion', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'candidatos' => $candidatos,
            'totalVotos' => $totalVotos,
            'totalVotantes' => $totalVotantes,
            'votosEmitidos' => $votosEmitidos,
            'participacion' => $participacion
        ]);
    }

    /**
     * Finalizar votacion y determinar elegidos
     */
    public function finalizarVotacion(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Obtener candidatos ordenados por votos
        $candidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->where('estado', 'aprobado')
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        $plazasPrincipales = $proceso['plazas_principales'];
        $plazasSuplentes = $proceso['plazas_suplentes'];

        // Marcar elegidos
        $i = 0;
        foreach ($candidatos as $c) {
            if ($i < $plazasPrincipales) {
                // Plaza principal
                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $c['id_candidato'])
                    ->update([
                        'estado' => 'elegido',
                        'tipo_plaza' => 'principal'
                    ]);
            } elseif ($i < ($plazasPrincipales + $plazasSuplentes)) {
                // Plaza suplente
                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $c['id_candidato'])
                    ->update([
                        'estado' => 'elegido',
                        'tipo_plaza' => 'suplente'
                    ]);
            } else {
                // No elegido
                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $c['id_candidato'])
                    ->update(['estado' => 'no_elegido']);
            }
            $i++;
        }

        // Cambiar estado del proceso a designacion_empleador
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update([
                'estado' => 'designacion_empleador',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->to("/comites-elecciones/{$proceso['id_cliente']}/proceso/{$idProceso}")
            ->with('success', 'Votacion finalizada. Representantes de trabajadores elegidos.');
    }

    /**
     * Determinar elegidos según votos obtenidos
     */
    protected function determinarElegidos(int $idProceso, array $proceso): void
    {
        // Obtener candidatos de trabajadores ordenados por votos
        $candidatos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->where('estado', 'aprobado')
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        $plazasPrincipales = (int) $proceso['plazas_principales'];
        $plazasSuplentes = (int) $proceso['plazas_suplentes'];

        $i = 0;
        foreach ($candidatos as $c) {
            if ($i < $plazasPrincipales) {
                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $c['id_candidato'])
                    ->update(['estado' => 'elegido', 'tipo_plaza' => 'principal']);
            } elseif ($i < ($plazasPrincipales + $plazasSuplentes)) {
                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $c['id_candidato'])
                    ->update(['estado' => 'elegido', 'tipo_plaza' => 'suplente']);
            } else {
                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $c['id_candidato'])
                    ->update(['estado' => 'no_elegido']);
            }
            $i++;
        }
    }

    /**
     * Vincular miembros elegidos al comité real
     */
    protected function vincularMiembrosComite(int $idProceso, array $proceso): void
    {
        // Obtener o crear el comité
        $tipo = $this->tipoComiteModel->where('codigo', $proceso['tipo_comite'])->first();
        if (!$tipo) return;

        $comite = $this->db->table('tbl_comites')
            ->where('id_cliente', $proceso['id_cliente'])
            ->where('id_tipo', $tipo['id_tipo'])
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$comite) {
            // Crear el comité
            $this->db->table('tbl_comites')->insert([
                'id_cliente' => $proceso['id_cliente'],
                'id_tipo' => $tipo['id_tipo'],
                'estado' => 'activo',
                'fecha_conformacion' => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+2 years -1 day')),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $idComite = $this->db->insertID();
        } else {
            $idComite = $comite['id_comite'];
        }

        // Actualizar proceso con el id_comite
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update(['id_comite' => $idComite]);

        // Obtener todos los candidatos elegidos (trabajadores y empleador)
        $elegidos = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->whereIn('estado', ['elegido', 'designado'])
            ->get()
            ->getResultArray();

        // Limpiar miembros anteriores del comité (si aplica)
        $this->db->table('tbl_miembros_comite')
            ->where('id_comite', $idComite)
            ->delete();

        // Insertar nuevos miembros
        foreach ($elegidos as $e) {
            $this->db->table('tbl_miembros_comite')->insert([
                'id_comite' => $idComite,
                'id_candidato' => $e['id_candidato'],
                'nombres' => $e['nombres'],
                'apellidos' => $e['apellidos'],
                'documento_identidad' => $e['documento_identidad'],
                'cargo' => $e['cargo'],
                'email' => $e['email'],
                'telefono' => $e['telefono'],
                'representacion' => $e['representacion'],
                'tipo_miembro' => $e['tipo_plaza'],
                'foto' => $e['foto'],
                'tiene_certificado_50h' => $e['tiene_certificado_50h'],
                'fecha_ingreso' => date('Y-m-d'),
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Completar proceso - genera acta y vincula al comité
     */
    public function completarProceso(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Vincular miembros al comité
        $this->vincularMiembrosComite($idProceso, $proceso);

        // Actualizar estado
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update([
                'estado' => 'completado',
                'fecha_completado' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->to("/comites-elecciones/{$proceso['id_cliente']}/proceso/{$idProceso}")
            ->with('success', 'Proceso completado. Comité conformado exitosamente.');
    }

    /**
     * Administración de procesos electorales - Vista general
     * Permite ver todos los procesos y gestionar estados
     */
    public function administrarProcesos()
    {
        // Verificar que sea admin o consultant
        $session = session();
        $rolId = $session->get('rol_id');

        if (!in_array($rolId, [1, 2])) { // 1=admin, 2=consultant
            return redirect()->to('/dashboard')->with('error', 'No tiene permisos para acceder a esta sección');
        }

        // Obtener todos los procesos con información del cliente
        $procesos = $this->db->table('tbl_procesos_electorales pe')
            ->select('pe.*, c.nombre_cliente, c.nit, tc.nombre as nombre_comite')
            ->join('tbl_cliente c', 'pe.id_cliente = c.id_cliente', 'left')
            ->join('tbl_tipos_comite tc', 'pe.tipo_comite = tc.codigo', 'left')
            ->orderBy('pe.id_proceso', 'DESC')
            ->get()
            ->getResultArray();

        // Definir estados posibles y sus transiciones permitidas
        $estadosInfo = [
            'configuracion' => ['label' => 'Configuración', 'color' => 'secondary', 'icon' => 'cog'],
            'inscripcion' => ['label' => 'Inscripción', 'color' => 'info', 'icon' => 'user-plus'],
            'votacion' => ['label' => 'Votación', 'color' => 'primary', 'icon' => 'vote-yea'],
            'escrutinio' => ['label' => 'Escrutinio', 'color' => 'warning', 'icon' => 'calculator'],
            'designacion_empleador' => ['label' => 'Designación Empleador', 'color' => 'info', 'icon' => 'user-tie'],
            'firmas' => ['label' => 'Firmas', 'color' => 'success', 'icon' => 'signature'],
            'completado' => ['label' => 'Completado', 'color' => 'success', 'icon' => 'check-circle'],
            'cancelado' => ['label' => 'Cancelado', 'color' => 'danger', 'icon' => 'times-circle']
        ];

        return view('comites_elecciones/admin_procesos', [
            'procesos' => $procesos,
            'estadosInfo' => $estadosInfo
        ]);
    }

    /**
     * Reabrir proceso electoral a un estado anterior (AJAX)
     */
    public function reabrirProceso()
    {
        // Verificar que sea admin o consultant
        $session = session();
        $rolId = $session->get('rol_id');

        if (!in_array($rolId, [1, 2])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta acción'
            ]);
        }

        $idProceso = $this->request->getPost('id_proceso');
        $nuevoEstado = $this->request->getPost('nuevo_estado');

        // Validar estados permitidos para reabrir
        $estadosPermitidos = ['configuracion', 'inscripcion', 'votacion', 'escrutinio', 'designacion_empleador', 'firmas'];

        if (!in_array($nuevoEstado, $estadosPermitidos)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Estado no válido'
            ]);
        }

        // Obtener proceso actual
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Proceso no encontrado'
            ]);
        }

        $estadoAnterior = $proceso['estado'];

        // Preparar datos para actualizar según el estado destino
        $updateData = [
            'estado' => $nuevoEstado,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Limpiar fechas según el estado al que se regresa
        if ($nuevoEstado === 'votacion') {
            $updateData['fecha_escrutinio'] = null;
            $updateData['fecha_completado'] = null;
        } elseif ($nuevoEstado === 'inscripcion') {
            $updateData['fecha_inicio_votacion'] = null;
            $updateData['fecha_fin_votacion'] = null;
            $updateData['fecha_escrutinio'] = null;
            $updateData['fecha_completado'] = null;
        } elseif ($nuevoEstado === 'configuracion') {
            $updateData['fecha_inicio_inscripcion'] = null;
            $updateData['fecha_fin_inscripcion'] = null;
            $updateData['fecha_inicio_votacion'] = null;
            $updateData['fecha_fin_votacion'] = null;
            $updateData['fecha_escrutinio'] = null;
            $updateData['fecha_completado'] = null;
        }

        // Actualizar proceso
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update($updateData);

        // Registrar en log (opcional)
        $this->db->table('tbl_log_procesos')->insert([
            'id_proceso' => $idProceso,
            'accion' => 'reabrir',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado,
            'usuario_id' => $session->get('user_id'),
            'ip' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Proceso reabierto exitosamente de '{$estadoAnterior}' a '{$nuevoEstado}'",
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado
        ]);
    }

    /**
     * Cancelar proceso electoral (AJAX)
     */
    public function cancelarProcesoElectoral()
    {
        // Verificar que sea admin o consultant
        $session = session();
        $rolId = $session->get('rol_id');

        if (!in_array($rolId, [1, 2])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta acción'
            ]);
        }

        $idProceso = $this->request->getPost('id_proceso');
        $motivo = $this->request->getPost('motivo') ?? 'Sin especificar';

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Proceso no encontrado'
            ]);
        }

        $estadoAnterior = $proceso['estado'];

        // Actualizar proceso
        $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->update([
                'estado' => 'cancelado',
                'observaciones' => $motivo,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Registrar en log
        $this->db->table('tbl_log_procesos')->insert([
            'id_proceso' => $idProceso,
            'accion' => 'cancelar',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => 'cancelado',
            'observaciones' => $motivo,
            'usuario_id' => $session->get('user_id'),
            'ip' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Proceso cancelado exitosamente'
        ]);
    }

    // =========================================================================
    // JURADOS DE VOTACION
    // =========================================================================

    /**
     * Agregar jurado a un proceso electoral (AJAX)
     */
    public function agregarJurado()
    {
        $idProceso = $this->request->getPost('id_proceso');
        $documento = trim($this->request->getPost('documento_identidad'));
        $nombres = trim($this->request->getPost('nombres'));
        $apellidos = trim($this->request->getPost('apellidos'));
        $cargo = trim($this->request->getPost('cargo'));
        $email = trim($this->request->getPost('email'));
        $telefono = trim($this->request->getPost('telefono'));
        $rol = $this->request->getPost('rol') ?? 'escrutador';

        // Validar campos requeridos
        if (empty($idProceso) || empty($documento) || empty($nombres) || empty($apellidos)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Documento, nombres y apellidos son requeridos'
            ]);
        }

        // Obtener proceso
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Proceso no encontrado'
            ]);
        }

        // Verificar si ya existe el jurado en este proceso
        $existe = $this->db->table('tbl_jurados_proceso')
            ->where('id_proceso', $idProceso)
            ->where('documento_identidad', $documento)
            ->get()
            ->getRowArray();

        if ($existe) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Esta persona ya esta registrada como jurado en este proceso'
            ]);
        }

        // Validar rol unico para presidente y secretario
        if (in_array($rol, ['presidente', 'secretario'])) {
            $rolExiste = $this->db->table('tbl_jurados_proceso')
                ->where('id_proceso', $idProceso)
                ->where('rol', $rol)
                ->where('estado', 'activo')
                ->get()
                ->getRowArray();

            if ($rolExiste) {
                $rolLabel = $rol == 'presidente' ? 'Presidente' : 'Secretario';
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Ya existe un $rolLabel asignado a este proceso"
                ]);
            }
        }

        // Insertar jurado
        $this->db->table('tbl_jurados_proceso')->insert([
            'id_proceso' => $idProceso,
            'id_cliente' => $proceso['id_cliente'],
            'documento_identidad' => $documento,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'cargo' => $cargo,
            'email' => $email,
            'telefono' => $telefono,
            'rol' => $rol,
            'estado' => 'activo',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $idJurado = $this->db->insertID();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Jurado agregado exitosamente',
            'jurado' => [
                'id_jurado' => $idJurado,
                'nombre_completo' => "$nombres $apellidos",
                'documento' => $documento,
                'rol' => $rol
            ]
        ]);
    }

    /**
     * Obtener jurados de un proceso (AJAX)
     */
    public function obtenerJurados(int $idProceso)
    {
        $jurados = $this->db->table('tbl_jurados_proceso')
            ->where('id_proceso', $idProceso)
            ->where('estado', 'activo')
            ->orderBy('FIELD(rol, "presidente", "secretario", "escrutador", "testigo")')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'jurados' => $jurados
        ]);
    }

    /**
     * Eliminar jurado (AJAX)
     */
    public function eliminarJurado(int $idJurado)
    {
        $jurado = $this->db->table('tbl_jurados_proceso')
            ->where('id_jurado', $idJurado)
            ->get()
            ->getRowArray();

        if (!$jurado) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Jurado no encontrado'
            ]);
        }

        // Eliminar (o marcar como inactivo)
        $this->db->table('tbl_jurados_proceso')
            ->where('id_jurado', $idJurado)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Jurado eliminado exitosamente'
        ]);
    }

    /**
     * Buscar trabajador para agregar como jurado (AJAX)
     * Busca en votantes del proceso o en trabajadores del cliente
     */
    public function buscarTrabajadorJurado(int $idProceso)
    {
        $documento = trim($this->request->getGet('documento'));

        if (empty($documento)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ingrese un documento'
            ]);
        }

        // Buscar primero en votantes del proceso
        $votante = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->where('documento_identidad', $documento)
            ->get()
            ->getRowArray();

        if ($votante) {
            return $this->response->setJSON([
                'success' => true,
                'encontrado' => true,
                'trabajador' => [
                    'documento_identidad' => $votante['documento_identidad'],
                    'nombres' => $votante['nombres'],
                    'apellidos' => $votante['apellidos'],
                    'cargo' => $votante['cargo'],
                    'email' => $votante['email'],
                    'telefono' => $votante['telefono'] ?? ''
                ]
            ]);
        }

        // Si no se encuentra
        return $this->response->setJSON([
            'success' => true,
            'encontrado' => false,
            'message' => 'Trabajador no encontrado en el censo. Puede ingresarlo manualmente.'
        ]);
    }

    // =========================================================================
    // ACTA DE CONSTITUCIÓN DEL COMITÉ
    // =========================================================================

    /**
     * Generar Acta de Constitución del Comité (Preview Web)
     */
    public function generarActaConstitucion(int $idProceso)
    {
        $data = $this->obtenerDatosActa($idProceso);

        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Buscar o crear el documento en tbl_documentos_sst
        $documento = $this->obtenerOCrearDocumentoActa($data['proceso'], $data['cliente'], $data);
        $data['documento'] = $documento;

        // Obtener firmas electrónicas si existen
        $data['firmasElectronicas'] = $this->obtenerFirmasElectronicas($documento['id_documento']);

        return view('comites_elecciones/acta_constitucion_preview', $data);
    }

    /**
     * Obtener firmas electrónicas de un documento
     */
    private function obtenerFirmasElectronicas(int $idDocumento): array
    {
        $firmasElectronicas = [];

        $solicitudes = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudes as $sol) {
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

    /**
     * Obtener o crear registro del Acta de Constitución en tbl_documentos_sst
     */
    private function obtenerOCrearDocumentoActa(array $proceso, array $cliente, ?array $datosActa = null): array
    {
        $tipoDocumento = 'acta_constitucion_' . strtolower($proceso['tipo_comite']);
        $anio = $proceso['anio'];
        $idCliente = $cliente['id_cliente'];

        // Buscar documento existente
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if ($documento) {
            return $documento;
        }

        // Generar snapshot de contenido via Factory
        $contenidoJson = null;
        try {
            $tipoDocObj = \App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory::crear($tipoDocumento);
            if ($datosActa && method_exists($tipoDocObj, 'buildContenidoSnapshot')) {
                $contenidoJson = $tipoDocObj->buildContenidoSnapshot($datosActa);
            }
        } catch (\Exception $e) {
            log_message('warning', "No se pudo generar snapshot para {$tipoDocumento}: " . $e->getMessage());
        }

        // Crear nuevo documento
        $titulo = 'Acta de Constitucion ' . $proceso['tipo_comite'] . ' ' . $anio;
        $codigo = 'FT-SST-013';

        $nuevoDocumento = [
            'id_cliente' => $idCliente,
            'tipo_documento' => $tipoDocumento,
            'titulo' => $titulo,
            'codigo' => $codigo,
            'anio' => $anio,
            'contenido' => $contenidoJson,
            'version' => 1,
            'estado' => 'generado',
            'observaciones' => 'Generado automaticamente desde proceso electoral ID: ' . $proceso['id_proceso'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('id_usuario') ?? session()->get('id_consultor') ?? null
        ];

        $this->db->table('tbl_documentos_sst')->insert($nuevoDocumento);
        $nuevoDocumento['id_documento'] = $this->db->insertID();

        // Crear version inicial 1.0 en el sistema de versionamiento
        try {
            $versionService = new \App\Services\DocumentoVersionService();
            $usuarioId = (int)(session()->get('id_usuario') ?? session()->get('id_consultor') ?? 0);
            $usuarioNombre = session()->get('nombre') ?? 'Sistema';
            $versionService->crearVersionInicial(
                $nuevoDocumento['id_documento'],
                $usuarioId,
                $usuarioNombre,
                'Generacion automatica desde proceso electoral ID: ' . $proceso['id_proceso']
            );
        } catch (\Exception $e) {
            log_message('error', "Error creando version inicial para {$tipoDocumento}: " . $e->getMessage());
        }

        return $nuevoDocumento;
    }

    /**
     * Generar PDF del Acta de Constitución
     */
    public function generarActaConstitucionPDF(int $idProceso)
    {
        $data = $this->obtenerDatosActa($idProceso);

        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Generar HTML
        $html = view('comites_elecciones/acta_constitucion_pdf', $data);

        // Configurar Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $tipoComite = $data['proceso']['tipo_comite'];
        $anio = $data['proceso']['anio'];
        $filename = "Acta_Constitucion_{$tipoComite}_{$anio}.pdf";

        // Enviar al navegador
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Descargar PDF del Acta de Constitución
     */
    public function descargarActaConstitucion(int $idProceso)
    {
        $data = $this->obtenerDatosActa($idProceso);

        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Generar HTML
        $html = view('comites_elecciones/acta_constitucion_pdf', $data);

        // Configurar Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $tipoComite = $data['proceso']['tipo_comite'];
        $anio = $data['proceso']['anio'];
        $filename = "Acta_Constitucion_{$tipoComite}_{$anio}.pdf";

        // Descargar
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Exportar Acta de Constitución a Word
     */
    public function exportarActaWord(int $idProceso)
    {
        $data = $this->obtenerDatosActa($idProceso);

        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Generar HTML
        $html = view('comites_elecciones/acta_constitucion_word', $data);

        // Nombre del archivo
        $tipoComite = $data['proceso']['tipo_comite'];
        $anio = $data['proceso']['anio'];
        $filename = "Acta_Constitucion_{$tipoComite}_{$anio}.doc";

        // Enviar como documento Word
        return $this->response
            ->setHeader('Content-Type', 'application/msword')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($html);
    }

    /**
     * Obtener todos los datos necesarios para el Acta de Constitución
     */
    private function obtenerDatosActa(int $idProceso): ?array
    {
        // Proceso
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return null;
        }

        // Cliente
        $cliente = $this->clienteModel->find($proceso['id_cliente']);

        // Jurados
        $jurados = $this->db->table('tbl_jurados_proceso')
            ->where('id_proceso', $idProceso)
            ->where('estado', 'activo')
            ->orderBy("FIELD(rol, 'presidente', 'secretario', 'escrutador', 'testigo')")
            ->get()
            ->getResultArray();

        // Votantes que votaron
        $votantes = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->where('ha_votado', 1)
            ->orderBy('apellidos', 'ASC')
            ->get()
            ->getResultArray();

        // Todos los votantes del censo
        $censoCompleto = $this->db->table('tbl_votantes_proceso')
            ->where('id_proceso', $idProceso)
            ->orderBy('apellidos', 'ASC')
            ->get()
            ->getResultArray();

        // Candidatos trabajadores (elegidos)
        $candidatosTrabajadores = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->whereIn('estado', ['elegido', 'aprobado'])
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        // Candidatos empleador (designados)
        $candidatosEmpleador = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'empleador')
            ->whereIn('estado', ['designado', 'aprobado'])
            ->orderBy('tipo_plaza', 'ASC')
            ->orderBy('id_candidato', 'ASC')
            ->get()
            ->getResultArray();

        // Todos los candidatos para resultados de votación
        $resultadosVotacion = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->where('votos_obtenidos >', 0)
            ->orderBy('votos_obtenidos', 'DESC')
            ->get()
            ->getResultArray();

        // Estadísticas
        $totalVotos = $this->db->table('tbl_votos_comite')
            ->where('id_proceso', $idProceso)
            ->countAllResults();

        $totalVotantes = count($censoCompleto);
        $votaronCount = count($votantes);
        $participacion = $totalVotantes > 0 ? round(($votaronCount / $totalVotantes) * 100, 1) : 0;

        // Separar elegidos en principales y suplentes
        $principales = [];
        $suplentes = [];
        $pos = 1;
        foreach ($candidatosTrabajadores as $c) {
            if ($pos <= $proceso['plazas_principales']) {
                $principales[] = $c;
            } elseif ($pos <= $proceso['plazas_principales'] + $proceso['plazas_suplentes']) {
                $suplentes[] = $c;
            }
            $pos++;
        }

        // Separar empleador en principales y suplentes
        $empleadorPrincipales = array_filter($candidatosEmpleador, fn($c) => $c['tipo_plaza'] === 'principal');
        $empleadorSuplentes = array_filter($candidatosEmpleador, fn($c) => $c['tipo_plaza'] === 'suplente');

        // Contexto SST para obtener responsable
        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $proceso['id_cliente'])
            ->get()
            ->getRowArray();

        // Obtener documento del acta (si existe)
        $tipoDocumento = 'acta_constitucion_' . strtolower($proceso['tipo_comite']);
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $proceso['id_cliente'])
            ->where('tipo_documento', $tipoDocumento)
            ->where('anio', $proceso['anio'])
            ->get()
            ->getRowArray();

        // Obtener firmas electrónicas si existe el documento
        $firmasElectronicas = [];
        if ($documento) {
            $solicitudes = $this->db->table('tbl_doc_firma_solicitudes')
                ->where('id_documento', $documento['id_documento'])
                ->where('estado', 'firmado')
                ->get()
                ->getResultArray();

            foreach ($solicitudes as $sol) {
                $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                    ->where('id_solicitud', $sol['id_solicitud'])
                    ->get()
                    ->getRowArray();

                $firmasElectronicas[$sol['firmante_tipo']] = [
                    'solicitud' => $sol,
                    'evidencia' => $evidencia
                ];
            }
        }

        return [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'jurados' => $jurados,
            'votantes' => $votantes,
            'censoCompleto' => $censoCompleto,
            'candidatosTrabajadores' => $candidatosTrabajadores,
            'candidatosEmpleador' => $candidatosEmpleador,
            'resultadosVotacion' => $resultadosVotacion,
            'principales' => $principales,
            'suplentes' => $suplentes,
            'empleadorPrincipales' => array_values($empleadorPrincipales),
            'empleadorSuplentes' => array_values($empleadorSuplentes),
            'totalVotos' => $totalVotos,
            'totalVotantes' => $totalVotantes,
            'votaronCount' => $votaronCount,
            'participacion' => $participacion,
            'contexto' => $contexto,
            'documento' => $documento,
            'firmasElectronicas' => $firmasElectronicas,
            'fechaActual' => date('Y-m-d'),
            'codigoDocumento' => 'FT-SST-013',
            'versionDocumento' => '1'
        ];
    }

    /**
     * Mostrar formulario para solicitar firmas del Acta de Constitución
     * Incluye: Jurados, Rep Legal, Delegado SST, Miembros Comité (empleador y trabajadores)
     */
    public function solicitarFirmasActa(int $idProceso)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = $this->obtenerDatosActa($idProceso);
        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Obtener o crear documento del acta en tbl_documentos_sst
        $documento = $this->obtenerOCrearDocumentoActa($data['proceso'], $data['cliente'], $data);

        // Obtener solicitudes de firma existentes
        $solicitudesExistentes = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->get()
            ->getResultArray();

        // Indexar por tipo+identificador para verificar cuáles ya existen
        $firmasExistentes = [];
        foreach ($solicitudesExistentes as $sol) {
            $firmasExistentes[$sol['firmante_tipo']] = $sol;
        }

        // Preparar lista de firmantes disponibles
        $firmantesDisponibles = [];

        // 1. JURADOS DE VOTACIÓN
        foreach ($data['jurados'] as $jurado) {
            $tipoFirma = 'jurado_' . $jurado['id_jurado'];
            $firmantesDisponibles[] = [
                'grupo' => 'Jurados de Votación',
                'tipo' => $tipoFirma,
                'nombre' => $jurado['nombres'] . ' ' . $jurado['apellidos'],
                'cargo' => ucfirst($jurado['rol']),
                'cedula' => $jurado['cedula'] ?? '',
                'email' => $jurado['email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes[$tipoFirma]),
                'estado_firma' => $firmasExistentes[$tipoFirma]['estado'] ?? null
            ];
        }

        // 2. REPRESENTANTE LEGAL
        if (!empty($data['contexto']['representante_legal_nombre'])) {
            $firmantesDisponibles[] = [
                'grupo' => 'Aprobación Empresarial',
                'tipo' => 'representante_legal',
                'nombre' => $data['contexto']['representante_legal_nombre'],
                'cargo' => $data['contexto']['representante_legal_cargo'] ?? 'Representante Legal',
                'cedula' => $data['contexto']['representante_legal_cedula'] ?? '',
                'email' => $data['contexto']['representante_legal_email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes['representante_legal']),
                'estado_firma' => $firmasExistentes['representante_legal']['estado'] ?? null
            ];
        }

        // 3. DELEGADO/VIGÍA SST
        if (!empty($data['contexto']['delegado_sst_nombre'])) {
            $firmantesDisponibles[] = [
                'grupo' => 'Aprobación Empresarial',
                'tipo' => 'delegado_sst',
                'nombre' => $data['contexto']['delegado_sst_nombre'],
                'cargo' => $data['contexto']['delegado_sst_cargo'] ?? 'Delegado SST',
                'cedula' => $data['contexto']['delegado_sst_cedula'] ?? '',
                'email' => $data['contexto']['delegado_sst_email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes['delegado_sst']),
                'estado_firma' => $firmasExistentes['delegado_sst']['estado'] ?? null
            ];
        }

        // 4. MIEMBROS COMITÉ - EMPLEADOR (Principales)
        foreach ($data['empleadorPrincipales'] as $idx => $miembro) {
            $tipoFirma = 'empleador_principal_' . $miembro['id_candidato'];
            $firmantesDisponibles[] = [
                'grupo' => 'Miembros del Comité - Empleador (Principales)',
                'tipo' => $tipoFirma,
                'nombre' => $miembro['nombres'] . ' ' . $miembro['apellidos'],
                'cargo' => $miembro['cargo'] ?? 'Representante del Empleador',
                'cedula' => $miembro['cedula'] ?? '',
                'email' => $miembro['email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes[$tipoFirma]),
                'estado_firma' => $firmasExistentes[$tipoFirma]['estado'] ?? null
            ];
        }

        // 5. MIEMBROS COMITÉ - EMPLEADOR (Suplentes)
        foreach ($data['empleadorSuplentes'] as $idx => $miembro) {
            $tipoFirma = 'empleador_suplente_' . $miembro['id_candidato'];
            $firmantesDisponibles[] = [
                'grupo' => 'Miembros del Comité - Empleador (Suplentes)',
                'tipo' => $tipoFirma,
                'nombre' => $miembro['nombres'] . ' ' . $miembro['apellidos'],
                'cargo' => $miembro['cargo'] ?? 'Representante del Empleador (Suplente)',
                'cedula' => $miembro['cedula'] ?? '',
                'email' => $miembro['email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes[$tipoFirma]),
                'estado_firma' => $firmasExistentes[$tipoFirma]['estado'] ?? null
            ];
        }

        // 6. MIEMBROS COMITÉ - TRABAJADORES (Principales)
        foreach ($data['principales'] as $idx => $miembro) {
            $tipoFirma = 'trabajador_principal_' . $miembro['id_candidato'];
            $firmantesDisponibles[] = [
                'grupo' => 'Miembros del Comité - Trabajadores (Principales)',
                'tipo' => $tipoFirma,
                'nombre' => $miembro['nombres'] . ' ' . $miembro['apellidos'],
                'cargo' => $miembro['cargo'] ?? 'Representante de los Trabajadores',
                'cedula' => $miembro['cedula'] ?? '',
                'email' => $miembro['email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes[$tipoFirma]),
                'estado_firma' => $firmasExistentes[$tipoFirma]['estado'] ?? null
            ];
        }

        // 7. MIEMBROS COMITÉ - TRABAJADORES (Suplentes)
        foreach ($data['suplentes'] as $idx => $miembro) {
            $tipoFirma = 'trabajador_suplente_' . $miembro['id_candidato'];
            $firmantesDisponibles[] = [
                'grupo' => 'Miembros del Comité - Trabajadores (Suplentes)',
                'tipo' => $tipoFirma,
                'nombre' => $miembro['nombres'] . ' ' . $miembro['apellidos'],
                'cargo' => $miembro['cargo'] ?? 'Representante de los Trabajadores (Suplente)',
                'cedula' => $miembro['cedula'] ?? '',
                'email' => $miembro['email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes[$tipoFirma]),
                'estado_firma' => $firmasExistentes[$tipoFirma]['estado'] ?? null
            ];
        }

        // Agrupar firmantes por grupo
        $firmantesAgrupados = [];
        foreach ($firmantesDisponibles as $f) {
            $firmantesAgrupados[$f['grupo']][] = $f;
        }

        return view('comites_elecciones/solicitar_firmas_acta', [
            'proceso' => $data['proceso'],
            'cliente' => $data['cliente'],
            'documento' => $documento,
            'firmantesAgrupados' => $firmantesAgrupados,
            'solicitudesExistentes' => $solicitudesExistentes,
            'totalFirmantes' => count($firmantesDisponibles),
            'firmadosCount' => count(array_filter($solicitudesExistentes, fn($s) => $s['estado'] === 'firmado')),
            'pendientesCount' => count(array_filter($solicitudesExistentes, fn($s) => in_array($s['estado'], ['pendiente', 'esperando'])))
        ]);
    }

    /**
     * Crear solicitudes de firma para el Acta de Constitución (POST)
     */
    public function crearSolicitudesActa()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idProceso = $this->request->getPost('id_proceso');
        $firmantesSeleccionados = $this->request->getPost('firmantes') ?? [];

        if (empty($firmantesSeleccionados)) {
            return redirect()->back()->with('error', 'Debe seleccionar al menos un firmante');
        }

        $data = $this->obtenerDatosActa($idProceso);
        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Obtener documento
        $documento = $this->obtenerOCrearDocumentoActa($data['proceso'], $data['cliente'], $data);
        $idDocumento = $documento['id_documento'];

        // Preparar mapa de firmantes con sus datos
        $mapaFirmantes = $this->construirMapaFirmantes($data);

        $solicitudesCreadas = [];
        $orden = 1;

        // Obtener el orden máximo existente
        $maxOrden = $this->db->table('tbl_doc_firma_solicitudes')
            ->selectMax('orden_firma')
            ->where('id_documento', $idDocumento)
            ->get()
            ->getRow();

        if ($maxOrden && $maxOrden->orden_firma) {
            $orden = (int)$maxOrden->orden_firma + 1;
        }

        foreach ($firmantesSeleccionados as $tipoFirmante) {
            if (!isset($mapaFirmantes[$tipoFirmante])) {
                continue;
            }

            $firmante = $mapaFirmantes[$tipoFirmante];

            // Verificar que no exista ya
            $existe = $this->db->table('tbl_doc_firma_solicitudes')
                ->where('id_documento', $idDocumento)
                ->where('firmante_tipo', $tipoFirmante)
                ->countAllResults();

            if ($existe > 0) {
                continue;
            }

            // Generar token único
            $token = bin2hex(random_bytes(32));

            $datosSolicitud = [
                'id_documento' => $idDocumento,
                'firmante_tipo' => $tipoFirmante,
                'firmante_email' => $firmante['email'],
                'firmante_nombre' => $firmante['nombre'],
                'firmante_cargo' => $firmante['cargo'],
                'firmante_documento' => $firmante['cedula'],
                'orden_firma' => $orden++,
                'estado' => 'pendiente',
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('tbl_doc_firma_solicitudes')->insert($datosSolicitud);
            $idSolicitud = $this->db->insertID();

            if ($idSolicitud) {
                $solicitud = $this->db->table('tbl_doc_firma_solicitudes')
                    ->where('id_solicitud', $idSolicitud)
                    ->get()
                    ->getRowArray();
                $solicitudesCreadas[] = $solicitud;

                // Enviar correo de firma
                $this->enviarCorreoFirmaActa($solicitud, $documento, $data['proceso'], $data['cliente']);
            }
        }

        // Actualizar estado del documento
        if (!empty($solicitudesCreadas)) {
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update(['estado' => 'pendiente_firma']);
        }

        $count = count($solicitudesCreadas);
        return redirect()->to("/comites-elecciones/proceso/{$idProceso}/firmas")
            ->with('success', "Se enviaron {$count} solicitudes de firma electrónica");
    }

    /**
     * Construir mapa de firmantes con sus datos
     */
    private function construirMapaFirmantes(array $data): array
    {
        $mapa = [];

        // Jurados
        foreach ($data['jurados'] as $jurado) {
            $tipo = 'jurado_' . $jurado['id_jurado'];
            $mapa[$tipo] = [
                'nombre' => $jurado['nombres'] . ' ' . $jurado['apellidos'],
                'cargo' => ucfirst($jurado['rol']),
                'cedula' => $jurado['cedula'] ?? '',
                'email' => $jurado['email'] ?? ''
            ];
        }

        // Representante Legal
        if (!empty($data['contexto']['representante_legal_nombre'])) {
            $mapa['representante_legal'] = [
                'nombre' => $data['contexto']['representante_legal_nombre'],
                'cargo' => $data['contexto']['representante_legal_cargo'] ?? 'Representante Legal',
                'cedula' => $data['contexto']['representante_legal_cedula'] ?? '',
                'email' => $data['contexto']['representante_legal_email'] ?? ''
            ];
        }

        // Delegado SST
        if (!empty($data['contexto']['delegado_sst_nombre'])) {
            $mapa['delegado_sst'] = [
                'nombre' => $data['contexto']['delegado_sst_nombre'],
                'cargo' => $data['contexto']['delegado_sst_cargo'] ?? 'Delegado SST',
                'cedula' => $data['contexto']['delegado_sst_cedula'] ?? '',
                'email' => $data['contexto']['delegado_sst_email'] ?? ''
            ];
        }

        // Empleador Principales
        foreach ($data['empleadorPrincipales'] as $m) {
            $tipo = 'empleador_principal_' . $m['id_candidato'];
            $mapa[$tipo] = [
                'nombre' => $m['nombres'] . ' ' . $m['apellidos'],
                'cargo' => $m['cargo'] ?? 'Representante del Empleador',
                'cedula' => $m['cedula'] ?? '',
                'email' => $m['email'] ?? ''
            ];
        }

        // Empleador Suplentes
        foreach ($data['empleadorSuplentes'] as $m) {
            $tipo = 'empleador_suplente_' . $m['id_candidato'];
            $mapa[$tipo] = [
                'nombre' => $m['nombres'] . ' ' . $m['apellidos'],
                'cargo' => $m['cargo'] ?? 'Representante del Empleador (Suplente)',
                'cedula' => $m['cedula'] ?? '',
                'email' => $m['email'] ?? ''
            ];
        }

        // Trabajadores Principales
        foreach ($data['principales'] as $m) {
            $tipo = 'trabajador_principal_' . $m['id_candidato'];
            $mapa[$tipo] = [
                'nombre' => $m['nombres'] . ' ' . $m['apellidos'],
                'cargo' => $m['cargo'] ?? 'Representante de los Trabajadores',
                'cedula' => $m['cedula'] ?? '',
                'email' => $m['email'] ?? ''
            ];
        }

        // Trabajadores Suplentes
        foreach ($data['suplentes'] as $m) {
            $tipo = 'trabajador_suplente_' . $m['id_candidato'];
            $mapa[$tipo] = [
                'nombre' => $m['nombres'] . ' ' . $m['apellidos'],
                'cargo' => $m['cargo'] ?? 'Representante de los Trabajadores (Suplente)',
                'cedula' => $m['cedula'] ?? '',
                'email' => $m['email'] ?? ''
            ];
        }

        return $mapa;
    }

    /**
     * Enviar correo de firma para el Acta de Constitución
     */
    private function enviarCorreoFirmaActa(array $solicitud, array $documento, array $proceso, array $cliente): bool
    {
        $urlFirma = base_url("firma/firmar/{$solicitud['token']}");
        $tipoComite = $proceso['tipo_comite'];
        $anio = $proceso['anio'];
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Empresa';

        // Formatear tipo de firmante para mostrar
        $tipoFirmanteDisplay = $this->formatearTipoFirmante($solicitud['firmante_tipo']);

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma Electronica</h2>
                <p style='color: #e0e0e0; margin: 5px 0 0 0;'>Acta de Constitucion {$tipoComite}</p>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$solicitud['firmante_nombre']}</strong>,</p>
                <p>Se requiere su firma electronica como <strong>{$tipoFirmanteDisplay}</strong> para el Acta de Constitucion del {$tipoComite} de la empresa <strong>{$nombreCliente}</strong>.</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0d6efd;'>
                    <p style='margin: 5px 0;'><strong>Documento:</strong> Acta de Constitucion {$tipoComite}</p>
                    <p style='margin: 5px 0;'><strong>Codigo:</strong> {$documento['codigo']}</p>
                    <p style='margin: 5px 0;'><strong>Año:</strong> {$anio}</p>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> {$nombreCliente}</p>
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
            $email->setSubject("Solicitud de Firma: Acta de Constitucion {$tipoComite} - {$nombreCliente}");
            $email->addTo($solicitud['firmante_email'], $solicitud['firmante_nombre']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            $statusCode = $response->statusCode();
            log_message('info', "Email firma acta enviado a {$solicitud['firmante_email']} - Status: {$statusCode}");

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email firma acta: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatear tipo de firmante para mostrar
     */
    private function formatearTipoFirmante(string $tipo): string
    {
        if (str_starts_with($tipo, 'jurado_')) {
            return 'Jurado de Votación';
        }
        if ($tipo === 'representante_legal') {
            return 'Representante Legal';
        }
        if ($tipo === 'delegado_sst') {
            return 'Delegado/Vigía SST';
        }
        if (str_starts_with($tipo, 'empleador_principal_')) {
            return 'Representante del Empleador (Principal)';
        }
        if (str_starts_with($tipo, 'empleador_suplente_')) {
            return 'Representante del Empleador (Suplente)';
        }
        if (str_starts_with($tipo, 'trabajador_principal_')) {
            return 'Representante de los Trabajadores (Principal)';
        }
        if (str_starts_with($tipo, 'trabajador_suplente_')) {
            return 'Representante de los Trabajadores (Suplente)';
        }
        return ucfirst(str_replace('_', ' ', $tipo));
    }

    /**
     * Ver estado de firmas del Acta de Constitución
     */
    public function estadoFirmasActa(int $idProceso)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = $this->obtenerDatosActa($idProceso);
        if (!$data) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $documento = $this->obtenerOCrearDocumentoActa($data['proceso'], $data['cliente'], $data);

        $solicitudes = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('orden_firma', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener evidencias para firmas completadas
        $evidencias = [];
        foreach ($solicitudes as $sol) {
            if ($sol['estado'] === 'firmado') {
                $evidencias[$sol['id_solicitud']] = $this->db->table('tbl_doc_firma_evidencias')
                    ->where('id_solicitud', $sol['id_solicitud'])
                    ->get()
                    ->getRowArray();
            }
        }

        // Estadísticas
        $totalSolicitudes = count($solicitudes);
        $firmados = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'firmado'));
        $pendientes = count(array_filter($solicitudes, fn($s) => in_array($s['estado'], ['pendiente', 'esperando'])));

        return view('comites_elecciones/estado_firmas_acta', [
            'proceso' => $data['proceso'],
            'cliente' => $data['cliente'],
            'documento' => $documento,
            'solicitudes' => $solicitudes,
            'evidencias' => $evidencias,
            'totalSolicitudes' => $totalSolicitudes,
            'firmados' => $firmados,
            'pendientes' => $pendientes,
            'porcentaje' => $totalSolicitudes > 0 ? round(($firmados / $totalSolicitudes) * 100) : 0
        ]);
    }

    // =========================================================
    // MÓDULO DE RECOMPOSICIÓN DE COMITÉS
    // =========================================================

    /**
     * Listar recomposiciones de un proceso
     */
    public function listarRecomposiciones(int $idProceso)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        $cliente = $this->db->table('tbl_cliente')
            ->where('id_cliente', $proceso['id_cliente'])
            ->get()
            ->getRowArray();

        // Obtener recomposiciones
        $recomposiciones = $this->db->table('tbl_recomposiciones_comite r')
            ->select('r.*,
                      cs.nombres as saliente_nombres, cs.apellidos as saliente_apellidos, cs.documento_identidad as saliente_documento,
                      ce.nombres as entrante_nombres_db, ce.apellidos as entrante_apellidos_db, ce.documento_identidad as entrante_documento_db')
            ->join('tbl_candidatos_comite cs', 'cs.id_candidato = r.id_candidato_saliente', 'left')
            ->join('tbl_candidatos_comite ce', 'ce.id_candidato = r.id_candidato_entrante', 'left')
            ->where('r.id_proceso', $idProceso)
            ->orderBy('r.fecha_recomposicion', 'DESC')
            ->get()
            ->getResultArray();

        return view('comites_elecciones/recomposicion/listar', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'recomposiciones' => $recomposiciones
        ]);
    }

    /**
     * Formulario para nueva recomposición
     */
    public function nuevaRecomposicion(int $idProceso)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso || !in_array($proceso['estado'], ['completado', 'firmas'])) {
            return redirect()->back()->with('error', 'Solo se puede recomponer comités completados');
        }

        $cliente = $this->db->table('tbl_cliente')
            ->where('id_cliente', $proceso['id_cliente'])
            ->get()
            ->getRowArray();

        // Obtener miembros actuales del comité (activos)
        $miembrosActuales = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->whereIn('estado', ['elegido', 'designado'])
            ->where('estado_miembro', 'activo')
            ->orderBy('representacion', 'ASC')
            ->orderBy('tipo_plaza', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener candidatos disponibles para reemplazo (no elegidos, ordenados por votos)
        $candidatosDisponibles = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->where('estado', 'no_elegido')
            ->where('estado_miembro', 'activo')
            ->orderBy('posicion_votacion', 'ASC')
            ->get()
            ->getResultArray();

        // Motivos de salida
        $motivosSalida = [
            'terminacion_contrato' => 'Terminación del contrato de trabajo',
            'renuncia_voluntaria' => 'Renuncia voluntaria al comité',
            'sancion_disciplinaria' => 'Sanción disciplinaria por falta grave',
            'violacion_confidencialidad' => 'Violación del deber de confidencialidad',
            'inasistencia_reiterada' => 'Inasistencia a más de 3 reuniones consecutivas',
            'incumplimiento_funciones' => 'Incumplimiento reiterado de obligaciones',
            'fallecimiento' => 'Fallecimiento',
            'otro' => 'Otro motivo'
        ];

        return view('comites_elecciones/recomposicion/nueva', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'miembrosActuales' => $miembrosActuales,
            'candidatosDisponibles' => $candidatosDisponibles,
            'motivosSalida' => $motivosSalida
        ]);
    }

    /**
     * API: Obtener siguiente candidato en votación
     */
    public function getSiguienteEnVotacion(int $idProceso)
    {
        $siguiente = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->where('estado', 'no_elegido')
            ->where('estado_miembro', 'activo')
            ->orderBy('posicion_votacion', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'success' => true,
            'siguiente' => $siguiente
        ]);
    }

    /**
     * Guardar recomposición
     */
    public function guardarRecomposicion()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idProceso = $this->request->getPost('id_proceso');
        $idCandidatoSaliente = $this->request->getPost('id_candidato_saliente');
        $motivoSalida = $this->request->getPost('motivo_salida');
        $motivoDetalle = $this->request->getPost('motivo_detalle');
        $fechaSalida = $this->request->getPost('fecha_salida');
        $fechaRecomposicion = $this->request->getPost('fecha_recomposicion');
        $tipoIngreso = $this->request->getPost('tipo_ingreso');
        $idCandidatoEntrante = $this->request->getPost('id_candidato_entrante');

        // Datos del nuevo miembro (si es empleador o no hay candidatos)
        $entranteNuevo = [
            'nombres' => $this->request->getPost('entrante_nombres'),
            'apellidos' => $this->request->getPost('entrante_apellidos'),
            'documento' => $this->request->getPost('entrante_documento'),
            'cargo' => $this->request->getPost('entrante_cargo'),
            'email' => $this->request->getPost('entrante_email'),
            'telefono' => $this->request->getPost('entrante_telefono')
        ];

        // Validaciones básicas
        if (!$idProceso || !$idCandidatoSaliente || !$motivoSalida || !$fechaRecomposicion) {
            return redirect()->back()->with('error', 'Faltan campos obligatorios')->withInput();
        }

        // Obtener proceso
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        if (!$proceso) {
            return redirect()->back()->with('error', 'Proceso no encontrado');
        }

        // Obtener número de recomposición
        $numRecomp = $this->db->table('tbl_recomposiciones_comite')
            ->where('id_proceso', $idProceso)
            ->countAllResults() + 1;

        $this->db->transStart();

        try {
            // Si es candidato entrante existente, verificar que exista
            if ($tipoIngreso === 'siguiente_votacion' && $idCandidatoEntrante) {
                $candidatoEntrante = $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $idCandidatoEntrante)
                    ->get()
                    ->getRowArray();

                if (!$candidatoEntrante) {
                    throw new \Exception('Candidato entrante no encontrado');
                }
            }

            // Si es designación de empleador y hay datos nuevos, crear candidato
            if ($tipoIngreso === 'designacion_empleador' && !empty($entranteNuevo['documento'])) {
                $nuevoId = $this->db->table('tbl_candidatos_comite')->insert([
                    'id_proceso' => $idProceso,
                    'id_cliente' => $proceso['id_cliente'],
                    'nombres' => $entranteNuevo['nombres'],
                    'apellidos' => $entranteNuevo['apellidos'],
                    'documento_identidad' => $entranteNuevo['documento'],
                    'cargo' => $entranteNuevo['cargo'],
                    'email' => $entranteNuevo['email'],
                    'telefono' => $entranteNuevo['telefono'],
                    'representacion' => 'empleador',
                    'tipo_plaza' => 'principal', // Se ajustará según el saliente
                    'estado' => 'designado',
                    'estado_miembro' => 'activo',
                    'es_recomposicion' => 1,
                    'fecha_ingreso_comite' => $fechaRecomposicion
                ]);
                $idCandidatoEntrante = $this->db->insertID();
            }

            // Crear registro de recomposición
            $this->db->table('tbl_recomposiciones_comite')->insert([
                'id_proceso' => $idProceso,
                'id_cliente' => $proceso['id_cliente'],
                'fecha_recomposicion' => $fechaRecomposicion,
                'numero_recomposicion' => $numRecomp,
                'id_candidato_saliente' => $idCandidatoSaliente,
                'motivo_salida' => $motivoSalida,
                'motivo_detalle' => $motivoDetalle,
                'fecha_efectiva_salida' => $fechaSalida ?: $fechaRecomposicion,
                'id_candidato_entrante' => $idCandidatoEntrante,
                'tipo_ingreso' => $tipoIngreso,
                'entrante_nombres' => $entranteNuevo['nombres'],
                'entrante_apellidos' => $entranteNuevo['apellidos'],
                'entrante_documento' => $entranteNuevo['documento'],
                'entrante_cargo' => $entranteNuevo['cargo'],
                'entrante_email' => $entranteNuevo['email'],
                'entrante_telefono' => $entranteNuevo['telefono'],
                'estado' => 'borrador',
                'created_by' => session()->get('user_id')
            ]);
            $idRecomposicion = $this->db->insertID();

            // Actualizar estado del miembro saliente
            $this->db->table('tbl_candidatos_comite')
                ->where('id_candidato', $idCandidatoSaliente)
                ->update([
                    'estado_miembro' => 'retirado',
                    'fecha_retiro_comite' => $fechaSalida ?: $fechaRecomposicion
                ]);

            // Actualizar estado del miembro entrante
            if ($idCandidatoEntrante) {
                // Obtener tipo de plaza del saliente
                $saliente = $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $idCandidatoSaliente)
                    ->get()
                    ->getRowArray();

                $this->db->table('tbl_candidatos_comite')
                    ->where('id_candidato', $idCandidatoEntrante)
                    ->update([
                        'estado' => $saliente['representacion'] === 'trabajador' ? 'elegido' : 'designado',
                        'estado_miembro' => 'activo',
                        'tipo_plaza' => $saliente['tipo_plaza'],
                        'es_recomposicion' => 1,
                        'id_recomposicion_ingreso' => $idRecomposicion,
                        'fecha_ingreso_comite' => $fechaRecomposicion
                    ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            return redirect()->to("comites-elecciones/proceso/{$idProceso}/recomposicion/{$idRecomposicion}")
                ->with('success', 'Recomposición registrada exitosamente');

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ver detalle de una recomposición
     */
    public function verRecomposicion(int $idProceso, int $idRecomposicion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $recomposicion = $this->db->table('tbl_recomposiciones_comite')
            ->where('id_recomposicion', $idRecomposicion)
            ->get()
            ->getRowArray();

        if (!$recomposicion) {
            return redirect()->back()->with('error', 'Recomposición no encontrada');
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        $cliente = $this->db->table('tbl_cliente')
            ->where('id_cliente', $proceso['id_cliente'])
            ->get()
            ->getRowArray();

        // Obtener datos del saliente
        $saliente = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $recomposicion['id_candidato_saliente'])
            ->get()
            ->getRowArray();

        // Obtener datos del entrante
        $entrante = null;
        if ($recomposicion['id_candidato_entrante']) {
            $entrante = $this->db->table('tbl_candidatos_comite')
                ->where('id_candidato', $recomposicion['id_candidato_entrante'])
                ->get()
                ->getRowArray();
        }

        // Obtener miembros actuales del comité para el acta
        $miembrosActuales = $this->obtenerMiembrosComiteActual($idProceso, $recomposicion);

        return view('comites_elecciones/recomposicion/ver', [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'recomposicion' => $recomposicion,
            'saliente' => $saliente,
            'entrante' => $entrante,
            'miembrosActuales' => $miembrosActuales
        ]);
    }

    /**
     * Obtener miembros actuales del comité con marcas (A)/(B)
     */
    private function obtenerMiembrosComiteActual(int $idProceso, array $recomposicion): array
    {
        $miembros = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->whereIn('estado', ['elegido', 'designado'])
            ->where('estado_miembro', 'activo')
            ->orderBy('representacion', 'ASC')
            ->orderBy('tipo_plaza', 'DESC')
            ->orderBy('id_candidato', 'ASC')
            ->get()
            ->getResultArray();

        // Agregar marca (A) continuante o (B) nuevo
        foreach ($miembros as &$m) {
            $m['marca'] = $m['es_recomposicion'] ? 'B' : 'A';
            $m['tipo_nombramiento'] = $m['es_recomposicion']
                ? 'Nombramiento en recomposición: ' . ($m['fecha_ingreso_comite'] ?? date('Y-m-d'))
                : 'Nombramiento inicial: ' . ($m['fecha_ingreso_comite'] ?? $m['fecha_inscripcion']);
        }

        return $miembros;
    }

    /**
     * Generar PDF del acta de recomposición
     */
    public function generarActaRecomposicionPdf(int $idProceso, int $idRecomposicion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = $this->obtenerDatosActaRecomposicion($idProceso, $idRecomposicion);

        if (!$data) {
            return redirect()->back()->with('error', 'Datos de recomposición no encontrados');
        }

        // Obtener firmas electrónicas si el documento existe
        $firmasElectronicas = [];
        if (!empty($data['recomposicion']['id_documento'])) {
            $solicitudes = $this->db->table('tbl_doc_firma_solicitudes')
                ->where('id_documento', $data['recomposicion']['id_documento'])
                ->where('estado', 'firmado')
                ->get()
                ->getResultArray();

            foreach ($solicitudes as $sol) {
                $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                    ->where('id_solicitud', $sol['id_solicitud'])
                    ->get()
                    ->getRowArray();

                $firmasElectronicas[$sol['firmante_tipo']] = [
                    'nombre' => $sol['firmante_nombre'],
                    'cargo' => $sol['firmante_cargo'],
                    'cedula' => $sol['firmante_documento'],
                    'fecha_firma' => $sol['fecha_firma'],
                    'firma_imagen' => $evidencia['firma_imagen'] ?? null
                ];
            }
        }
        $data['firmasElectronicas'] = $firmasElectronicas;

        // Generar HTML
        $html = view('comites_elecciones/recomposicion/acta_pdf', $data);

        // Generar PDF con DOMPDF
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = "Acta_Recomposicion_{$data['proceso']['tipo_comite']}_{$data['proceso']['anio']}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', "inline; filename=\"{$filename}\"")
            ->setBody($dompdf->output());
    }

    /**
     * Obtener datos para el acta de recomposición
     */
    private function obtenerDatosActaRecomposicion(int $idProceso, int $idRecomposicion): ?array
    {
        $recomposicion = $this->db->table('tbl_recomposiciones_comite')
            ->where('id_recomposicion', $idRecomposicion)
            ->get()
            ->getRowArray();

        if (!$recomposicion) {
            return null;
        }

        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()
            ->getRowArray();

        $cliente = $this->db->table('tbl_cliente')
            ->where('id_cliente', $proceso['id_cliente'])
            ->get()
            ->getRowArray();

        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $proceso['id_cliente'])
            ->get()
            ->getRowArray();

        $saliente = $this->db->table('tbl_candidatos_comite')
            ->where('id_candidato', $recomposicion['id_candidato_saliente'])
            ->get()
            ->getRowArray();

        $entrante = null;
        if ($recomposicion['id_candidato_entrante']) {
            $entrante = $this->db->table('tbl_candidatos_comite')
                ->where('id_candidato', $recomposicion['id_candidato_entrante'])
                ->get()
                ->getRowArray();
        }

        // Miembros actuales con marcas
        $miembrosEmpleador = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'empleador')
            ->whereIn('estado', ['elegido', 'designado'])
            ->where('estado_miembro', 'activo')
            ->orderBy('tipo_plaza', 'DESC')
            ->get()
            ->getResultArray();

        $miembrosTrabajadores = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->whereIn('estado', ['elegido', 'designado'])
            ->where('estado_miembro', 'activo')
            ->orderBy('tipo_plaza', 'DESC')
            ->get()
            ->getResultArray();

        // Agregar marcas
        foreach ($miembrosEmpleador as &$m) {
            $m['marca'] = $m['es_recomposicion'] ? 'B' : 'A';
        }
        foreach ($miembrosTrabajadores as &$m) {
            $m['marca'] = $m['es_recomposicion'] ? 'B' : 'A';
        }

        // Motivos legibles
        $motivosTexto = [
            'terminacion_contrato' => 'la terminación del contrato de trabajo',
            'renuncia_voluntaria' => 'renuncia voluntaria presentada',
            'sancion_disciplinaria' => 'imposición de sanción disciplinaria por falta grave',
            'violacion_confidencialidad' => 'violación del deber de confidencialidad',
            'inasistencia_reiterada' => 'inasistencia a más de tres (3) reuniones consecutivas',
            'incumplimiento_funciones' => 'incumplimiento reiterado de sus obligaciones',
            'fallecimiento' => 'fallecimiento',
            'otro' => 'otros motivos'
        ];

        // Combinar todos los miembros actuales
        $miembrosActuales = array_merge($miembrosTrabajadores, $miembrosEmpleador);

        return [
            'proceso' => $proceso,
            'cliente' => $cliente,
            'contexto' => $contexto,
            'recomposicion' => $recomposicion,
            'saliente' => $saliente,
            'entrante' => $entrante,
            'miembrosEmpleador' => $miembrosEmpleador,
            'miembrosTrabajadores' => $miembrosTrabajadores,
            'miembrosActuales' => $miembrosActuales,
            'motivoTexto' => $motivosTexto[$recomposicion['motivo_salida']] ?? 'motivos justificados',
            'codigoDocumento' => 'FT-SST-055',
            'versionDocumento' => '1'
        ];
    }

    /**
     * Solicitar firmas para acta de recomposición
     * Firmantes: Nuevo integrante, Representante Legal, Delegado SST (si existe)
     */
    public function solicitarFirmasRecomposicion(int $idProceso, int $idRecomposicion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = $this->obtenerDatosActaRecomposicion($idProceso, $idRecomposicion);
        if (!$data) {
            return redirect()->back()->with('error', 'Recomposición no encontrada');
        }

        // Obtener o crear documento de recomposición
        $documento = $this->obtenerOCrearDocumentoRecomposicion($data);

        // Obtener solicitudes existentes
        $solicitudesExistentes = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->get()
            ->getResultArray();

        $firmasExistentes = [];
        foreach ($solicitudesExistentes as $sol) {
            $firmasExistentes[$sol['firmante_tipo']] = $sol;
        }

        // Preparar firmantes (solo 3)
        $firmantesDisponibles = [];

        // 1. NUEVO INTEGRANTE (Obligatorio)
        $nombreEntrante = $data['entrante']
            ? $data['entrante']['nombres'] . ' ' . $data['entrante']['apellidos']
            : trim(($data['recomposicion']['entrante_nombres'] ?? '') . ' ' . ($data['recomposicion']['entrante_apellidos'] ?? ''));
        $cedulaEntrante = $data['entrante']
            ? $data['entrante']['documento_identidad']
            : ($data['recomposicion']['entrante_documento'] ?? '');
        $cargoEntrante = $data['entrante']
            ? $data['entrante']['cargo']
            : ($data['recomposicion']['entrante_cargo'] ?? '');
        $emailEntrante = $data['entrante']
            ? ($data['entrante']['email'] ?? '')
            : ($data['recomposicion']['entrante_email'] ?? '');

        $firmantesDisponibles[] = [
            'grupo' => 'Nuevo Integrante del Comité',
            'tipo' => 'nuevo_integrante',
            'nombre' => $nombreEntrante,
            'cargo' => $cargoEntrante,
            'cedula' => $cedulaEntrante,
            'email' => $emailEntrante,
            'ya_solicitado' => isset($firmasExistentes['nuevo_integrante']),
            'estado_firma' => $firmasExistentes['nuevo_integrante']['estado'] ?? null,
            'obligatorio' => true
        ];

        // 2. DELEGADO SST (Si existe) - Firma segundo
        if (!empty($data['contexto']['delegado_sst_nombre'])) {
            $firmantesDisponibles[] = [
                'grupo' => 'Aprobación Empresarial',
                'tipo' => 'delegado_sst',
                'nombre' => $data['contexto']['delegado_sst_nombre'],
                'cargo' => $data['contexto']['delegado_sst_cargo'] ?? 'Delegado SST',
                'cedula' => $data['contexto']['delegado_sst_cedula'] ?? '',
                'email' => $data['contexto']['delegado_sst_email'] ?? '',
                'ya_solicitado' => isset($firmasExistentes['delegado_sst']),
                'estado_firma' => $firmasExistentes['delegado_sst']['estado'] ?? null,
                'obligatorio' => true
            ];
        }

        // 3. REPRESENTANTE LEGAL (Obligatorio) - Firma último por jerarquía
        if (!empty($data['contexto']['representante_legal_nombre']) || !empty($data['cliente']['nombre_rep_legal'])) {
            $firmantesDisponibles[] = [
                'grupo' => 'Aprobación Empresarial',
                'tipo' => 'representante_legal',
                'nombre' => $data['contexto']['representante_legal_nombre'] ?? $data['cliente']['nombre_rep_legal'] ?? '',
                'cargo' => 'Representante Legal',
                'cedula' => $data['contexto']['representante_legal_cedula'] ?? $data['cliente']['cedula_rep_legal'] ?? '',
                'email' => $data['contexto']['representante_legal_email'] ?? $data['cliente']['email_rep_legal'] ?? '',
                'ya_solicitado' => isset($firmasExistentes['representante_legal']),
                'estado_firma' => $firmasExistentes['representante_legal']['estado'] ?? null,
                'obligatorio' => true
            ];
        }

        // Agrupar por grupo
        $firmantesAgrupados = [];
        foreach ($firmantesDisponibles as $f) {
            $firmantesAgrupados[$f['grupo']][] = $f;
        }

        return view('comites_elecciones/recomposicion/solicitar_firmas', [
            'proceso' => $data['proceso'],
            'cliente' => $data['cliente'],
            'recomposicion' => $data['recomposicion'],
            'documento' => $documento,
            'firmantesAgrupados' => $firmantesAgrupados,
            'solicitudesExistentes' => $solicitudesExistentes,
            'totalFirmantes' => count($firmantesDisponibles),
            'firmadosCount' => count(array_filter($solicitudesExistentes, fn($s) => $s['estado'] === 'firmado')),
            'pendientesCount' => count(array_filter($solicitudesExistentes, fn($s) => in_array($s['estado'], ['pendiente', 'esperando'])))
        ]);
    }

    /**
     * Obtener o crear documento de recomposición en tbl_documentos_sst
     */
    private function obtenerOCrearDocumentoRecomposicion(array $data): array
    {
        $tipoDocumento = 'acta_recomposicion_' . strtolower($data['proceso']['tipo_comite']);
        $idCliente = $data['cliente']['id_cliente'];
        $idRecomposicion = $data['recomposicion']['id_recomposicion'];

        // Buscar documento existente por titulo especifico de esta recomposicion
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->like('titulo', 'Recomposicion #' . $data['recomposicion']['numero_recomposicion'])
            ->get()
            ->getRowArray();

        if ($documento) {
            return $documento;
        }

        // Generar snapshot de contenido via Factory
        $contenidoJson = null;
        try {
            $tipoDocObj = \App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory::crear($tipoDocumento);
            if (method_exists($tipoDocObj, 'buildContenidoSnapshot')) {
                $contenidoJson = $tipoDocObj->buildContenidoSnapshot($data);
            }
        } catch (\Exception $e) {
            log_message('warning', "No se pudo generar snapshot para {$tipoDocumento}: " . $e->getMessage());
        }

        // Crear nuevo documento
        $tipoComiteNombre = [
            'COPASST' => 'COPASST',
            'COCOLAB' => 'Comité de Convivencia Laboral',
            'BRIGADA' => 'Brigada de Emergencias',
            'VIGIA' => 'Vigía SST'
        ][$data['proceso']['tipo_comite']] ?? $data['proceso']['tipo_comite'];

        $titulo = "Acta de Recomposicion #{$data['recomposicion']['numero_recomposicion']} - {$tipoComiteNombre} {$data['proceso']['anio']}";
        $codigo = $data['proceso']['tipo_comite'] === 'COCOLAB' ? 'FT-SST-155' : 'FT-SST-156';

        $nuevoDocumento = [
            'id_cliente' => $idCliente,
            'tipo_documento' => $tipoDocumento,
            'titulo' => $titulo,
            'codigo' => $codigo,
            'anio' => $data['proceso']['anio'],
            'contenido' => $contenidoJson,
            'version' => 1,
            'estado' => 'borrador',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('tbl_documentos_sst')->insert($nuevoDocumento);
        $nuevoDocumento['id_documento'] = $this->db->insertID();

        // Vincular con recomposición
        $this->db->table('tbl_recomposiciones_comite')
            ->where('id_recomposicion', $idRecomposicion)
            ->update(['id_documento' => $nuevoDocumento['id_documento']]);

        // Crear version inicial 1.0 en el sistema de versionamiento
        try {
            $versionService = new \App\Services\DocumentoVersionService();
            $usuarioId = (int)(session()->get('id_usuario') ?? session()->get('id_consultor') ?? 0);
            $usuarioNombre = session()->get('nombre') ?? 'Sistema';
            $versionService->crearVersionInicial(
                $nuevoDocumento['id_documento'],
                $usuarioId,
                $usuarioNombre,
                'Recomposicion #' . $data['recomposicion']['numero_recomposicion'] . ' - Proceso electoral ID: ' . $data['proceso']['id_proceso']
            );
        } catch (\Exception $e) {
            log_message('error', "Error creando version inicial para {$tipoDocumento}: " . $e->getMessage());
        }

        return $nuevoDocumento;
    }

    /**
     * Crear solicitudes de firma para recomposición
     */
    public function crearSolicitudesFirmaRecomposicion()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idProceso = $this->request->getPost('id_proceso');
        $idRecomposicion = $this->request->getPost('id_recomposicion');
        $idDocumento = $this->request->getPost('id_documento');
        $firmantesSeleccionados = $this->request->getPost('firmantes') ?? [];

        if (empty($firmantesSeleccionados)) {
            return redirect()->back()->with('error', 'Debe seleccionar al menos un firmante');
        }

        $data = $this->obtenerDatosActaRecomposicion($idProceso, $idRecomposicion);
        if (!$data) {
            return redirect()->back()->with('error', 'Recomposición no encontrada');
        }

        $orden = 1;
        $solicitudesCreadas = 0;

        foreach ($firmantesSeleccionados as $tipoFirmante => $info) {
            // Verificar que no exista ya
            $existe = $this->db->table('tbl_doc_firma_solicitudes')
                ->where('id_documento', $idDocumento)
                ->where('firmante_tipo', $tipoFirmante)
                ->get()
                ->getRow();

            if ($existe) {
                continue;
            }

            // Generar token único
            $token = bin2hex(random_bytes(32));

            $solicitud = [
                'id_documento' => $idDocumento,
                'token' => $token,
                'firmante_tipo' => $tipoFirmante,
                'firmante_nombre' => $info['nombre'],
                'firmante_email' => $info['email'],
                'firmante_cargo' => $info['cargo'],
                'firmante_documento' => $info['cedula'],
                'orden_firma' => $orden,
                'estado' => $orden === 1 ? 'pendiente' : 'esperando',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('tbl_doc_firma_solicitudes')->insert($solicitud);
            $solicitud['id_solicitud'] = $this->db->insertID();

            // Enviar correo si es el primero
            if ($orden === 1 && !empty($info['email'])) {
                $this->enviarCorreoFirmaRecomposicion($solicitud, $data);
            }

            $orden++;
            $solicitudesCreadas++;
        }

        // Actualizar estado de recomposición
        if ($solicitudesCreadas > 0) {
            $this->db->table('tbl_recomposiciones_comite')
                ->where('id_recomposicion', $idRecomposicion)
                ->update(['estado' => 'pendiente_firmas']);
        }

        return redirect()
            ->to("comites-elecciones/proceso/{$idProceso}/recomposicion/{$idRecomposicion}/firmas/estado")
            ->with('success', "Se crearon {$solicitudesCreadas} solicitudes de firma");
    }

    /**
     * Enviar correo de firma para recomposición
     */
    private function enviarCorreoFirmaRecomposicion(array $solicitud, array $data): bool
    {
        $tipoComite = [
            'COPASST' => 'COPASST',
            'COCOLAB' => 'Comité de Convivencia Laboral',
            'BRIGADA' => 'Brigada de Emergencias',
            'VIGIA' => 'Vigía SST'
        ][$data['proceso']['tipo_comite']] ?? $data['proceso']['tipo_comite'];

        $nombreCliente = $data['cliente']['nombre_cliente'];
        $enlaceFirma = base_url("firma/firmar/{$solicitud['token']}");

        $tipoFirmanteDisplay = match($solicitud['firmante_tipo']) {
            'nuevo_integrante' => 'Nuevo Integrante del Comité',
            'representante_legal' => 'Representante Legal',
            'delegado_sst' => 'Delegado SST',
            default => $solicitud['firmante_cargo']
        };

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma Electronica</h2>
                <p style='color: #e0e0e0; margin: 5px 0 0 0;'>Acta de Recomposicion - {$tipoComite}</p>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$solicitud['firmante_nombre']}</strong>,</p>
                <p>Se requiere su firma electronica como <strong>{$tipoFirmanteDisplay}</strong> para el Acta de Recomposicion del {$tipoComite} de la empresa <strong>{$nombreCliente}</strong>.</p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <p style='margin: 5px 0;'><strong>Documento:</strong> Acta de Recomposicion #{$data['recomposicion']['numero_recomposicion']}</p>
                    <p style='margin: 5px 0;'><strong>Comité:</strong> {$tipoComite}</p>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> {$nombreCliente}</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$enlaceFirma}' style='background: #dc3545; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                        Firmar Documento
                    </a>
                </div>

                <p style='font-size: 12px; color: #666;'>Este enlace es personal e intransferible. No lo comparta con terceros.</p>
            </div>
            <div style='background: #333; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                EnterpriseSST - Sistema de Gestión de Seguridad y Salud en el Trabajo
            </div>
        </div>";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $email->setSubject("Solicitud de Firma: Acta de Recomposicion - {$nombreCliente}");
            $email->addTo($solicitud['firmante_email'], $solicitud['firmante_nombre']);
            $email->addContent("text/html", $mensaje);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY') ?: 'SG.placeholder');
            $response = $sendgrid->send($email);

            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error enviando correo firma recomposicion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Estado de firmas de recomposición
     */
    public function estadoFirmasRecomposicion(int $idProceso, int $idRecomposicion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = $this->obtenerDatosActaRecomposicion($idProceso, $idRecomposicion);
        if (!$data) {
            return redirect()->back()->with('error', 'Recomposición no encontrada');
        }

        // Obtener documento
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $data['recomposicion']['id_documento'])
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        // Obtener solicitudes
        $solicitudes = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('orden_firma', 'ASC')
            ->get()
            ->getResultArray();

        // Obtener evidencias
        $evidencias = [];
        foreach ($solicitudes as $sol) {
            $ev = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            if ($ev) {
                $evidencias[$sol['id_solicitud']] = $ev;
            }
        }

        $totalSolicitudes = count($solicitudes);
        $firmados = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'firmado'));
        $pendientes = count(array_filter($solicitudes, fn($s) => in_array($s['estado'], ['pendiente', 'esperando'])));
        $porcentaje = $totalSolicitudes > 0 ? round(($firmados / $totalSolicitudes) * 100) : 0;

        return view('comites_elecciones/recomposicion/estado_firmas', [
            'proceso' => $data['proceso'],
            'cliente' => $data['cliente'],
            'recomposicion' => $data['recomposicion'],
            'documento' => $documento,
            'solicitudes' => $solicitudes,
            'evidencias' => $evidencias,
            'totalSolicitudes' => $totalSolicitudes,
            'firmados' => $firmados,
            'pendientes' => $pendientes,
            'porcentaje' => $porcentaje
        ]);
    }

    /**
     * Reenviar correo de firma para recomposición
     */
    public function reenviarFirmaRecomposicion(int $idSolicitud)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Obtener la solicitud
        $solicitud = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_solicitud', $idSolicitud)
            ->get()
            ->getRowArray();

        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada');
        }

        // Verificar que no esté firmada
        if ($solicitud['estado'] === 'firmado') {
            return redirect()->back()->with('error', 'Esta solicitud ya fue firmada');
        }

        // Obtener documento para saber el proceso y recomposición
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $solicitud['id_documento'])
            ->get()
            ->getRowArray();

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        // Obtener recomposición
        $recomposicion = $this->db->table('tbl_recomposiciones_comite')
            ->where('id_documento', $documento['id_documento'])
            ->get()
            ->getRowArray();

        if (!$recomposicion) {
            return redirect()->back()->with('error', 'Recomposición no encontrada');
        }

        // Obtener datos completos
        $data = $this->obtenerDatosActaRecomposicion($recomposicion['id_proceso'], $recomposicion['id_recomposicion']);
        if (!$data) {
            return redirect()->back()->with('error', 'Error al obtener datos de recomposición');
        }

        // Generar nuevo token y extender fecha de expiración
        $nuevoToken = bin2hex(random_bytes(32));
        $nuevaExpiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Actualizar solicitud: nuevo token, nueva expiración, estado pendiente
        $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_solicitud', $idSolicitud)
            ->update([
                'token' => $nuevoToken,
                'estado' => 'pendiente',
                'fecha_expiracion' => $nuevaExpiracion
            ]);

        // Actualizar la solicitud local para el correo
        $solicitud['token'] = $nuevoToken;
        $solicitud['estado'] = 'pendiente';

        // Reenviar correo
        $enviado = $this->enviarCorreoFirmaRecomposicion($solicitud, $data);

        if ($enviado) {
            return redirect()->back()->with('success', "Correo reenviado exitosamente a {$solicitud['firmante_nombre']}. El firmante ya puede firmar.");
        } else {
            return redirect()->back()->with('error', 'Error al reenviar el correo');
        }
    }
}
