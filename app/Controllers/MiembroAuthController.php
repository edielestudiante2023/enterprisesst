<?php

namespace App\Controllers;

use App\Models\ComiteModel;
use App\Models\MiembroComiteModel;
use App\Models\ActaModel;
use App\Models\ActaAsistenteModel;
use App\Models\ActaCompromisoModel;
use App\Models\ClientModel;

/**
 * Controlador para miembros autenticados (con login)
 * Acceso restringido solo al ecosistema de actas
 */
class MiembroAuthController extends BaseController
{
    protected $comiteModel;
    protected $miembroModel;
    protected $actaModel;
    protected $asistentesModel;
    protected $compromisosModel;

    public function __construct()
    {
        $this->comiteModel = new ComiteModel();
        $this->miembroModel = new MiembroComiteModel();
        $this->actaModel = new ActaModel();
        $this->asistentesModel = new ActaAsistenteModel();
        $this->compromisosModel = new ActaCompromisoModel();
    }

    /**
     * Obtener datos del miembro logueado
     */
    private function getMiembroLogueado(): ?array
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        if (!$email || !$idCliente) {
            return null;
        }

        return $this->miembroModel->getByEmailYCliente($email, $idCliente);
    }

    /**
     * Dashboard del miembro - ver sus comités
     */
    public function dashboard()
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        if (!$email || !$idCliente) {
            return redirect()->to('/login')->with('msg', 'Sesion invalida');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        // Obtener todos los comités a los que pertenece este miembro
        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);

        // Obtener estadísticas de cada comité
        foreach ($comites as &$comiteItem) {
            $comiteItem['stats'] = $this->actaModel->getEstadisticas($comiteItem['id_comite'], date('Y'));
            $comiteItem['compromisos_pendientes'] = count(
                $this->compromisosModel->getByResponsable($email, $idCliente)
            );
        }

        // Obtener el primer miembro para mostrar info general
        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);

        // Verificar si es miembro COPASST
        $esCopasst = false;
        foreach ($comites as $c) {
            if (($c['codigo'] ?? '') === 'COPASST') {
                $esCopasst = true;
                break;
            }
        }

        return view('actas/miembro_auth/dashboard', [
            'miembro' => $miembro,
            'cliente' => $cliente,
            'comites' => $comites,
            'esCopasst' => $esCopasst
        ]);
    }

    /**
     * Ver actas de un comité específico
     */
    public function verComite(int $idComite)
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        // Verificar que el miembro pertenece a este comité
        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        $perteneceAlComite = false;

        foreach ($comites as $c) {
            if ($c['id_comite'] == $idComite) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a este comite');
        }

        $comite = $this->comiteModel->getConDetalles($idComite);
        $actas = $this->actaModel->getByComite($idComite, date('Y'));

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);

        // Verificar permisos del miembro en este comité
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $idComite)
            ->where('email', $email)
            ->where('estado', 'activo')
            ->first();

        $estadisticas = $this->actaModel->getEstadisticas($idComite, date('Y'));
        $compromisosPendientes = $this->compromisosModel->getByComite($idComite, 'pendiente');

        return view('actas/miembro_auth/comite', [
            'miembro' => $miembro,
            'miembroEnComite' => $miembroEnComite,
            'cliente' => $cliente,
            'comite' => $comite,
            'actas' => $actas,
            'estadisticas' => $estadisticas,
            'compromisosPendientes' => $compromisosPendientes
        ]);
    }

    /**
     * Ver detalle de un acta
     */
    public function verActa(int $idActa)
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->to('/miembro/dashboard')->with('error', 'Acta no encontrada');
        }

        // Verificar que el miembro pertenece al comité del acta
        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        $perteneceAlComite = false;

        foreach ($comites as $c) {
            if ($c['id_comite'] == $acta['id_comite']) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);
        $asistentes = $this->asistentesModel->getByActa($idActa);
        $compromisos = $this->compromisosModel->getByActa($idActa) ?? [];

        // Obtener permisos del miembro en este comité
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $acta['id_comite'])
            ->where('email', $email)
            ->where('estado', 'activo')
            ->first();

        return view('actas/miembro_auth/ver_acta', [
            'miembro' => $miembro,
            'miembroEnComite' => $miembroEnComite,
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'asistentes' => $asistentes,
            'compromisos' => $compromisos
        ]);
    }

    /**
     * Descargar PDF del acta
     */
    public function descargarPDF(int $idActa)
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        // Verificar acceso
        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        $perteneceAlComite = false;

        foreach ($comites as $c) {
            if ($c['id_comite'] == $acta['id_comite']) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }

        // Redirigir al PDF público del acta
        return redirect()->to("/actas/comite/{$acta['id_comite']}/acta/{$idActa}/pdf");
    }

    /**
     * Ver compromisos asignados al miembro
     */
    public function misCompromisos()
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);

        $compromisos = $this->compromisosModel->getByResponsable($email, $idCliente);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        return view('actas/miembro_auth/compromisos', [
            'miembro' => $miembro,
            'cliente' => $cliente,
            'compromisos' => $compromisos
        ]);
    }

    /**
     * Ver compromisos de un comité específico
     */
    public function compromisosComite(int $idComite)
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        // Verificar que el miembro pertenece a este comité
        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        $perteneceAlComite = false;

        foreach ($comites as $c) {
            if ($c['id_comite'] == $idComite) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a este comite');
        }

        $comite = $this->comiteModel->getConDetalles($idComite);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);

        $compromisos = $this->compromisosModel->getByComite($idComite);
        $miembros = $this->miembroModel->getActivosPorComite($idComite);

        // Estadísticas
        $stats = [
            'pendientes' => count(array_filter($compromisos, fn($c) => $c['estado'] === 'pendiente')),
            'en_progreso' => count(array_filter($compromisos, fn($c) => $c['estado'] === 'en_proceso')),
            'completados' => count(array_filter($compromisos, fn($c) => $c['estado'] === 'cumplido')),
            'vencidos' => count(array_filter($compromisos, fn($c) => $c['estado'] === 'vencido'))
        ];

        return view('actas/miembro_auth/compromisos_comite', [
            'miembro' => $miembro,
            'cliente' => $cliente,
            'comite' => $comite,
            'compromisos' => $compromisos,
            'stats' => $stats,
            'miembros' => $miembros
        ]);
    }

    /**
     * Formulario para crear nueva acta
     */
    public function nuevaActa(int $idComite)
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        // Verificar que el miembro pertenece a este comité
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $idComite)
            ->where('email', $email)
            ->where('estado', 'activo')
            ->first();

        if (!$miembroEnComite) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a este comite');
        }

        // Verificar permiso para crear actas
        if (empty($miembroEnComite['puede_crear_actas'])) {
            return redirect()->to('/miembro/comite/' . $idComite)->with('error', 'No tienes permiso para crear actas');
        }

        $comite = $this->comiteModel->getConDetalles($idComite);
        $miembros = $this->miembroModel->getActivosPorComite($idComite);

        $plantillaModel = new \App\Models\PlantillaOrdenDiaModel();
        $plantilla = $plantillaModel->getDefaultPorTipo($comite['id_tipo']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($idComite);

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);

        return view('actas/miembro_auth/nueva_acta', [
            'miembro' => $miembro,
            'miembroEnComite' => $miembroEnComite,
            'cliente' => $cliente,
            'comite' => $comite,
            'miembros' => $miembros,
            'plantilla' => $plantilla,
            'compromisosPendientes' => $compromisosPendientes
        ]);
    }

    /**
     * Guardar acta creada por miembro
     */
    public function guardarActa(int $idComite)
    {
        $session = session();
        $email = $session->get('email_miembro');

        // Verificar permiso
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $idComite)
            ->where('email', $email)
            ->where('estado', 'activo')
            ->first();

        if (!$miembroEnComite || empty($miembroEnComite['puede_crear_actas'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso para crear actas']);
        }

        $comite = $this->comiteModel->find($idComite);

        // Preparar orden del día
        $ordenDelDia = [];
        $puntosOrden = $this->request->getPost('orden_punto') ?? [];
        $temasOrden = $this->request->getPost('orden_tema') ?? [];

        foreach ($puntosOrden as $i => $punto) {
            if (!empty($temasOrden[$i])) {
                $ordenDelDia[] = [
                    'punto' => (int) $punto,
                    'tema' => $temasOrden[$i]
                ];
            }
        }

        $data = [
            'id_comite' => $idComite,
            'id_cliente' => $comite['id_cliente'],
            'tipo_acta' => $this->request->getPost('tipo_acta') ?: 'ordinaria',
            'fecha_reunion' => $this->request->getPost('fecha_reunion'),
            'hora_inicio' => $this->request->getPost('hora_inicio'),
            'hora_fin' => $this->request->getPost('hora_fin'),
            'lugar' => $this->request->getPost('lugar'),
            'modalidad' => $this->request->getPost('modalidad') ?: 'presencial',
            'enlace_virtual' => $this->request->getPost('enlace_virtual'),
            'orden_del_dia' => $ordenDelDia,
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha'),
            'proxima_reunion_hora' => $this->request->getPost('proxima_reunion_hora'),
            'proxima_reunion_lugar' => $this->request->getPost('proxima_reunion_lugar'),
            'created_by' => $miembroEnComite['id_miembro']
        ];

        $idActa = $this->actaModel->crearActa($data);

        if ($idActa) {
            // Registrar asistentes
            $asistentes = $this->request->getPost('asistentes') ?? [];
            $asistio = $this->request->getPost('asistio') ?? [];
            $justificacion = $this->request->getPost('justificacion') ?? [];
            $orden = 1;

            foreach ($asistentes as $idMiembro) {
                // Manejar asesor SST externo (id virtual: 'asesor_X')
                if (is_string($idMiembro) && str_starts_with($idMiembro, 'asesor_')) {
                    if (isset($asistio[$idMiembro])) {
                        $idResponsable = (int) str_replace('asesor_', '', $idMiembro);
                        $this->asistentesModel->agregarAsesorExterno($idActa, $idResponsable);
                    }
                    continue;
                }

                $miembroData = $this->miembroModel->find($idMiembro);
                if ($miembroData) {
                    $this->asistentesModel->insert([
                        'id_acta' => $idActa,
                        'id_miembro' => $idMiembro,
                        'nombre_completo' => $miembroData['nombre_completo'],
                        'cargo' => $miembroData['cargo'],
                        'email' => $miembroData['email'],
                        'tipo_asistente' => 'miembro',
                        'asistio' => isset($asistio[$idMiembro]) ? 1 : 0,
                        'justificacion_ausencia' => $justificacion[$idMiembro] ?? null,
                        'orden_firma' => $orden,
                        'estado_firma' => 'pendiente'
                    ]);
                    $orden++;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Acta creada exitosamente',
                'id_acta' => $idActa,
                'redirect' => base_url('miembro/acta/' . $idActa)
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al crear el acta']);
    }

    /**
     * Verifica que el miembro logueado pertenece al comité del acta.
     * Devuelve el miembro (row) si tiene acceso, o null si no.
     */
    private function validarAccesoActa(int $idActa): ?array
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');

        $acta = $this->actaModel->find($idActa);
        if (!$acta) return null;

        $miembro = $this->miembroModel
            ->where('id_comite', $acta['id_comite'])
            ->where('email', $email)
            ->where('estado', 'activo')
            ->first();

        return $miembro ?: null;
    }

    /**
     * Editar acta - muestra el formulario (paridad con consultor)
     */
    public function editarActa(int $idActa)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }

        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->to('/miembro/dashboard')->with('error', 'Acta no encontrada');
        }

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->to('/miembro/acta/' . $idActa)->with('error', 'Esta acta ya no puede editarse');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);
        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($acta['id_comite']);
        $miembros = $this->miembroModel->getActivosPorComite($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);

        // URLs de la vista editar_acta.php en contexto miembro
        $urlBase = base_url('miembro');
        return view('actas/editar_acta', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'compromisos' => $acta['compromisos'] ?? [],
            'compromisosPendientes' => $compromisosPendientes,
            'miembros' => $miembros,
            'asistentes' => $asistentes,
            'urlBreadcrumbComites' => $urlBase . '/dashboard',
            'urlBreadcrumbComite'  => $urlBase . '/comite/' . $comite['id_comite'],
            'urlCompromisos'       => $urlBase . '/comite/' . $comite['id_comite'] . '/compromisos',
            'urlActualizar'        => $urlBase . '/actas/editar/' . $idActa,
            'urlEnviarFirmas'      => $urlBase . '/acta/' . $idActa . '/enviar-firmas',
            'urlCerrar'            => $urlBase . '/acta/' . $idActa . '/cerrar',
            'urlFirmas'            => $urlBase . '/acta/' . $idActa . '/firmas',
            'urlVerActa'           => $urlBase . '/acta/' . $idActa,
            'urlVolverComite'      => $urlBase . '/comite/' . $comite['id_comite'],
        ]);
    }

    /**
     * Actualizar acta (POST) - paridad con consultor
     */
    public function actualizarActa(int $idActa)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }

        $acta = $this->actaModel->find($idActa);

        if (!$acta || !in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Acta no encontrada o no editable');
        }

        // Orden del día
        $ordenPuntos = $this->request->getPost('orden_punto') ?? [];
        $ordenTemas  = $this->request->getPost('orden_tema') ?? [];
        $ordenDia = [];
        foreach ($ordenPuntos as $i => $punto) {
            $tema = $ordenTemas[$i] ?? '';
            if (!empty(trim($tema))) {
                $ordenDia[] = ['punto' => (int) $punto, 'tema' => trim($tema)];
            }
        }

        // Desarrollo
        $desarrolloPost = $this->request->getPost('desarrollo') ?? [];
        $desarrollo = [];
        foreach ($desarrolloPost as $punto => $descripcion) {
            $desarrollo[(int) $punto] = $descripcion;
        }

        $this->actaModel->update($idActa, [
            'orden_del_dia' => json_encode($ordenDia, JSON_UNESCAPED_UNICODE),
            'desarrollo' => json_encode($desarrollo),
            'conclusiones' => $this->request->getPost('conclusiones'),
            'observaciones' => $this->request->getPost('observaciones'),
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha') ?: null,
            'proxima_reunion_hora' => $this->request->getPost('proxima_reunion_hora') ?: null,
            'proxima_reunion_lugar' => $this->request->getPost('proxima_reunion_lugar') ?: null,
        ]);

        // Asistencia
        $asistio = $this->request->getPost('asistio') ?? [];
        $asistentesActa = $this->asistentesModel->getByActa($idActa);
        foreach ($asistentesActa as $asistente) {
            if (($asistente['tipo_asistente'] ?? '') === 'asesor') continue;
            $marcado = in_array((string)$asistente['id_miembro'], $asistio, true);
            if ($marcado && !$asistente['asistio']) {
                $this->asistentesModel->marcarPresente($asistente['id_asistente']);
            } elseif (!$marcado && $asistente['asistio']) {
                $this->asistentesModel->marcarAusente($asistente['id_asistente']);
            }
        }

        // Recalcular quórum
        $quorumPresente = $this->asistentesModel->calcularQuorumPresente($idActa);
        $hayQuorum = $this->asistentesModel->hayQuorum($idActa);
        $this->actaModel->update($idActa, [
            'quorum_presente' => $quorumPresente,
            'hay_quorum' => $hayQuorum ? 1 : 0
        ]);

        // Compromisos existentes
        $compromisosIds    = $this->request->getPost('compromiso_id') ?? [];
        $compromisosDesc   = $this->request->getPost('compromiso_descripcion') ?? [];
        $compromisosResp   = $this->request->getPost('compromiso_responsable') ?? [];
        $compromisosEstado = $this->request->getPost('compromiso_estado') ?? [];
        $compromisosFecha  = $this->request->getPost('compromiso_fecha') ?? [];
        foreach ($compromisosIds as $i => $idCompromiso) {
            if (!empty($idCompromiso)) {
                $updateData = [
                    'descripcion' => $compromisosDesc[$i] ?? '',
                    'estado' => $compromisosEstado[$i] ?? 'pendiente',
                    'fecha_vencimiento' => $compromisosFecha[$i] ?? null,
                ];
                if (!empty($compromisosResp[$i])) {
                    $asistente = $this->asistentesModel->find($compromisosResp[$i]);
                    if ($asistente) {
                        $updateData['responsable_nombre'] = $asistente['nombre_completo'];
                        $updateData['responsable_email'] = $asistente['email'];
                    }
                }
                $this->compromisosModel->update($idCompromiso, $updateData);
            }
        }

        // Compromisos nuevos
        $nuevosDesc  = $this->request->getPost('nuevo_compromiso_descripcion') ?? [];
        $nuevosResp  = $this->request->getPost('nuevo_compromiso_responsable') ?? [];
        $nuevosFecha = $this->request->getPost('nuevo_compromiso_fecha') ?? [];
        foreach ($nuevosDesc as $i => $desc) {
            if (!empty($desc) && !empty($nuevosResp[$i])) {
                $asistente = $this->asistentesModel->find($nuevosResp[$i]);
                $this->compromisosModel->crearCompromiso([
                    'id_acta' => $idActa,
                    'id_comite' => $acta['id_comite'],
                    'id_cliente' => $acta['id_cliente'],
                    'descripcion' => $desc,
                    'responsable_nombre' => $asistente['nombre_completo'] ?? '',
                    'responsable_email' => $asistente['email'] ?? null,
                    'fecha_compromiso' => $acta['fecha_reunion'],
                    'fecha_vencimiento' => $nuevosFecha[$i] ?? date('Y-m-d', strtotime('+30 days'))
                ]);
            }
        }

        return redirect()->to('/miembro/actas/editar/' . $idActa)
            ->with('success', 'Acta actualizada correctamente');
    }

    /**
     * Enviar acta a firmas (miembro) - paridad con consultor
     */
    public function enviarAFirmas(int $idActa)
    {
        $miembroEnComite = $this->validarAccesoActa($idActa);
        if (!$miembroEnComite) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }

        $acta = $this->actaModel->find($idActa);

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Esta acta ya fue cerrada');
        }

        if (empty($this->asistentesModel->getPresentes($idActa))) {
            return redirect()->back()->with('error', 'Debe marcar al menos un asistente antes de enviar el acta');
        }

        if ($this->actaModel->cerrarYEnviarAFirmas($idActa, $miembroEnComite['id_miembro'])) {
            $asistentes = $this->asistentesModel->getPresentes($idActa);
            $actaActualizada = $this->actaModel->find($idActa);
            $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

            // Delegar el envío de emails al método privado del ActasController
            $actasCtrl = new \App\Controllers\ActasController();
            $reflection = new \ReflectionClass($actasCtrl);
            $enviarEmail = $reflection->getMethod('enviarEmailFirmaActa');
            $enviarEmail->setAccessible(true);

            $emailsEnviados = [];
            $emailsFallidos = [];
            $sinEmail = [];

            foreach ($asistentes as $asistente) {
                if (!empty($asistente['email'])) {
                    $token = bin2hex(random_bytes(32));
                    $this->asistentesModel->update($asistente['id_asistente'], [
                        'token_firma' => $token,
                        'token_expira' => date('Y-m-d H:i:s', strtotime('+7 days')),
                        'estado_firma' => 'pendiente'
                    ]);

                    $ok = $enviarEmail->invoke($actasCtrl, $asistente, $token, $actaActualizada, $comite);

                    if ($ok) {
                        $this->asistentesModel->update($asistente['id_asistente'], [
                            'notificacion_enviada_at' => date('Y-m-d H:i:s')
                        ]);
                        $emailsEnviados[] = $asistente['email'];
                    } else {
                        $emailsFallidos[] = $asistente['email'];
                    }
                } else {
                    $sinEmail[] = $asistente['nombre_completo'];
                }
            }

            $mensaje = 'Acta cerrada y enviada a firmas.';
            if (!empty($emailsEnviados)) $mensaje .= ' Emails enviados a: ' . implode(', ', $emailsEnviados) . '.';
            if (!empty($emailsFallidos)) $mensaje .= ' FALLIDOS: ' . implode(', ', $emailsFallidos) . '.';
            if (!empty($sinEmail))       $mensaje .= ' Sin email: ' . implode(', ', $sinEmail) . '.';

            return redirect()->to('/miembro/acta/' . $idActa . '/firmas')->with('success', $mensaje);
        }

        return redirect()->back()->with('error', 'Error al cerrar el acta');
    }

    /**
     * Estado de firmas del acta (miembro)
     */
    public function estadoFirmas(int $idActa)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }

        $acta = $this->actaModel->getConDetalles($idActa);
        if (!$acta) {
            return redirect()->to('/miembro/dashboard')->with('error', 'Acta no encontrada');
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);
        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);
        $asistentes = $this->asistentesModel->getByActa($idActa);

        return view('actas/estado_firmas', [
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'asistentes' => $asistentes,
            'esMiembro' => true,
        ]);
    }

    /**
     * Crea una instancia de ActasController inicializada con el request/response actual.
     */
    private function actasController(): \App\Controllers\ActasController
    {
        $ctrl = new \App\Controllers\ActasController();
        $ctrl->initController($this->request, $this->response, \Config\Services::logger());
        return $ctrl;
    }

    /**
     * Reenviar notificación de firma a todos los pendientes (miembro)
     */
    public function reenviarTodos(int $idActa)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }
        return $this->actasController()->reenviarTodos($idActa);
    }

    /**
     * Reenviar notificación a un asistente específico (miembro)
     */
    public function reenviarAsistente(int $idActa, int $idAsistente)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso']);
        }
        return $this->actasController()->reenviarAsistente($idActa, $idAsistente);
    }

    /**
     * Cancelar firma de un asistente (miembro)
     */
    public function cancelarFirmaAsistente(int $idActa, int $idAsistente)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso']);
        }
        return $this->actasController()->cancelarFirmaAsistente($idActa, $idAsistente);
    }

    /**
     * Exportar Word del acta (miembro)
     */
    public function exportarWord(int $idActa)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso a esta acta');
        }
        return $this->actasController()->exportarWord($idActa);
    }

    /**
     * Solicitar reapertura del acta (miembro)
     * Reutiliza el flujo del consultor: crea registro en tbl_acta_solicitudes_reapertura
     * y envía email al consultor asignado al cliente con token de aprobación.
     */
    public function solicitarReapertura(int $idActa)
    {
        if (!$this->validarAccesoActa($idActa)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso a esta acta']);
        }
        // Delegar al ActasController que ya tiene toda la lógica (crea solicitud + email con token)
        return $this->actasController()->solicitarReapertura($idActa);
    }

    /**
     * Cerrar acta y enviar a firmas
     */
    public function cerrarActa(int $idActa)
    {
        $session = session();
        $email = $session->get('email_miembro');

        $acta = $this->actaModel->find($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        // Verificar permiso para cerrar
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $acta['id_comite'])
            ->where('email', $email)
            ->where('estado', 'activo')
            ->first();

        if (!$miembroEnComite || empty($miembroEnComite['puede_cerrar_actas'])) {
            return redirect()->back()->with('error', 'No tienes permiso para cerrar actas');
        }

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Esta acta ya fue cerrada o enviada a firmas');
        }

        // Verificar quórum
        if (!$this->asistentesModel->hayQuorum($idActa)) {
            return redirect()->back()->with('error', 'No hay quorum suficiente para cerrar el acta');
        }

        if ($this->actaModel->cerrarYEnviarAFirmas($idActa, $miembroEnComite['id_miembro'])) {
            // Programar notificaciones de firma
            $asistentes = $this->asistentesModel->getPresentes($idActa);
            $actaActualizada = $this->actaModel->find($idActa);

            $notificacionModel = new \App\Models\ActaNotificacionModel();

            foreach ($asistentes as $asistente) {
                if (!empty($asistente['email'])) {
                    $notificacionModel->programarSolicitudFirma(
                        $idActa,
                        $asistente['id_asistente'],
                        $asistente['email'],
                        $asistente['nombre_completo'],
                        $acta['id_cliente'],
                        $actaActualizada['numero_acta']
                    );

                    $this->asistentesModel->update($asistente['id_asistente'], [
                        'notificacion_enviada' => 1,
                        'fecha_notificacion' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            return redirect()->to('/miembro/acta/' . $idActa)->with('success', 'Acta cerrada y enviada a firmas exitosamente');
        }

        return redirect()->back()->with('error', 'Error al cerrar el acta');
    }
}
