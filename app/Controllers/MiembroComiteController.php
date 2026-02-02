<?php

namespace App\Controllers;

use App\Models\TipoComiteModel;
use App\Models\ComiteModel;
use App\Models\MiembroComiteModel;
use App\Models\ActaModel;
use App\Models\ActaAsistenteModel;
use App\Models\ActaCompromisoModel;
use App\Models\ActaTokenModel;
use App\Models\ClientModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controlador para acceso de miembros del comité
 * Acceso limitado: solo ven actas de SU comité
 */
class MiembroComiteController extends BaseController
{
    protected $comiteModel;
    protected $miembroModel;
    protected $actaModel;
    protected $asistentesModel;
    protected $compromisosModel;
    protected $tokenModel;

    protected $miembroActual;
    protected $comitesDelMiembro;

    public function __construct()
    {
        $this->comiteModel = new ComiteModel();
        $this->miembroModel = new MiembroComiteModel();
        $this->actaModel = new ActaModel();
        $this->asistentesModel = new ActaAsistenteModel();
        $this->compromisosModel = new ActaCompromisoModel();
        $this->tokenModel = new ActaTokenModel();
    }

    /**
     * Validar acceso por token
     */
    protected function validarAcceso(string $token): bool
    {
        $tokenData = $this->tokenModel->validarToken($token);

        if (!$tokenData || $tokenData['tipo'] !== 'acceso_miembro') {
            return false;
        }

        $this->miembroActual = $this->miembroModel->find($tokenData['id_miembro']);

        if (!$this->miembroActual || $this->miembroActual['estado'] !== 'activo') {
            return false;
        }

        // Obtener todos los comités a los que pertenece
        $this->comitesDelMiembro = $this->miembroModel->getComitesPorEmail(
            $this->miembroActual['email'],
            $this->miembroActual['id_cliente']
        );

        return true;
    }

    /**
     * Dashboard del miembro - ver sus comités
     */
    public function index(string $token)
    {
        if (!$this->validarAcceso($token)) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acceso denegado o enlace expirado.',
                'sugerencia' => 'Solicite un nuevo enlace de acceso al consultor.'
            ]);
        }

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($this->miembroActual['id_cliente']);

        // Obtener estadísticas de cada comité
        foreach ($this->comitesDelMiembro as &$membresia) {
            $membresia['stats'] = $this->actaModel->getEstadisticas($membresia['id_comite'], date('Y'));
            $membresia['compromisos_pendientes'] = count(
                $this->compromisosModel->getByResponsable($this->miembroActual['email'], $cliente['id_cliente'])
            );
        }

        return view('actas/miembro/index', [
            'token' => $token,
            'miembro' => $this->miembroActual,
            'cliente' => $cliente,
            'comites' => $this->comitesDelMiembro
        ]);
    }

    /**
     * Ver actas de un comité específico
     */
    public function verComite(string $token, int $idComite)
    {
        if (!$this->validarAcceso($token)) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acceso denegado o enlace expirado.'
            ]);
        }

        // Verificar que el miembro pertenece a este comité
        $perteneceAlComite = false;
        foreach ($this->comitesDelMiembro as $c) {
            if ($c['id_comite'] == $idComite) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'No tiene acceso a este comité.'
            ]);
        }

        $comite = $this->comiteModel->getConDetalles($idComite);
        $actas = $this->actaModel->getByComite($idComite, date('Y'));

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        // Verificar permisos del miembro en este comité
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $idComite)
            ->where('email', $this->miembroActual['email'])
            ->where('estado', 'activo')
            ->first();

        return view('actas/miembro/comite', [
            'token' => $token,
            'miembro' => $this->miembroActual,
            'miembroEnComite' => $miembroEnComite,
            'cliente' => $cliente,
            'comite' => $comite,
            'actas' => $actas
        ]);
    }

    /**
     * Ver detalle de un acta
     */
    public function verActa(string $token, int $idActa)
    {
        if (!$this->validarAcceso($token)) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acceso denegado o enlace expirado.'
            ]);
        }

        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        // Verificar que el miembro pertenece al comité del acta
        $perteneceAlComite = false;
        foreach ($this->comitesDelMiembro as $c) {
            if ($c['id_comite'] == $acta['id_comite']) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'No tiene acceso a esta acta.'
            ]);
        }

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        return view('actas/miembro/ver_acta', [
            'token' => $token,
            'miembro' => $this->miembroActual,
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta
        ]);
    }

    /**
     * Crear nueva acta (solo si tiene permiso)
     */
    public function nuevaActa(string $token, int $idComite)
    {
        if (!$this->validarAcceso($token)) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acceso denegado o enlace expirado.'
            ]);
        }

        // Verificar permiso para crear actas
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $idComite)
            ->where('email', $this->miembroActual['email'])
            ->where('estado', 'activo')
            ->first();

        if (!$miembroEnComite || !$miembroEnComite['puede_crear_actas']) {
            return redirect()->back()->with('error', 'No tiene permiso para crear actas');
        }

        $comite = $this->comiteModel->getConDetalles($idComite);
        $miembros = $this->miembroModel->getActivosPorComite($idComite);

        $plantillaModel = new \App\Models\PlantillaOrdenDiaModel();
        $plantilla = $plantillaModel->getDefaultPorTipo($comite['id_tipo']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($comite['id_cliente']);

        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($idComite);

        return view('actas/miembro/nueva_acta', [
            'token' => $token,
            'miembro' => $this->miembroActual,
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
    public function guardarActa(string $token, int $idComite)
    {
        if (!$this->validarAcceso($token)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        // Verificar permiso
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $idComite)
            ->where('email', $this->miembroActual['email'])
            ->where('estado', 'activo')
            ->first();

        if (!$miembroEnComite || !$miembroEnComite['puede_crear_actas']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso']);
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
            // Agregar asistentes
            $this->asistentesModel->agregarDesdeMiembros($idActa, $idComite);

            // Procesar asistencia
            $asistio = $this->request->getPost('asistio') ?? [];
            $asistentes = $this->asistentesModel->getByActa($idActa);

            foreach ($asistentes as $asistente) {
                if (!in_array($asistente['id_miembro'], $asistio)) {
                    $this->asistentesModel->marcarAusente($asistente['id_asistente']);
                }
            }

            // Calcular quórum
            $quorumPresente = $this->asistentesModel->calcularQuorumPresente($idActa);
            $hayQuorum = $this->asistentesModel->hayQuorum($idActa);

            $this->actaModel->update($idActa, [
                'quorum_presente' => $quorumPresente,
                'hay_quorum' => $hayQuorum ? 1 : 0
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Acta creada exitosamente',
                'redirect' => "/miembro/{$token}/acta/{$idActa}/editar"
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al crear el acta']);
    }

    /**
     * Editar acta (solo si tiene permiso y el acta está en borrador)
     */
    public function editarActa(string $token, int $idActa)
    {
        if (!$this->validarAcceso($token)) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acceso denegado o enlace expirado.'
            ]);
        }

        $acta = $this->actaModel->getConDetalles($idActa);

        if (!$acta) {
            return redirect()->back()->with('error', 'Acta no encontrada');
        }

        // Verificar que pertenece al comité
        $perteneceAlComite = false;
        foreach ($this->comitesDelMiembro as $c) {
            if ($c['id_comite'] == $acta['id_comite']) {
                $perteneceAlComite = true;
                break;
            }
        }

        if (!$perteneceAlComite) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'No tiene acceso a esta acta.'
            ]);
        }

        // Verificar que el acta es editable
        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return redirect()->back()->with('error', 'Esta acta ya no puede editarse');
        }

        // Verificar que el miembro asistió a la reunión
        $asistenciaDelMiembro = $this->asistentesModel
            ->where('id_acta', $idActa)
            ->where('email', $this->miembroActual['email'])
            ->where('asistio', 1)
            ->first();

        if (!$asistenciaDelMiembro) {
            return redirect()->back()->with('error', 'Solo los asistentes pueden editar el acta');
        }

        $comite = $this->comiteModel->getConDetalles($acta['id_comite']);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($acta['id_cliente']);

        $compromisosPendientes = $this->compromisosModel->getPendientesActasAnteriores($acta['id_comite']);

        return view('actas/miembro/editar_acta', [
            'token' => $token,
            'miembro' => $this->miembroActual,
            'cliente' => $cliente,
            'comite' => $comite,
            'acta' => $acta,
            'compromisosPendientes' => $compromisosPendientes
        ]);
    }

    /**
     * Cerrar acta y enviar a firmas (solo si tiene permiso)
     */
    public function cerrarActa(string $token, int $idActa)
    {
        if (!$this->validarAcceso($token)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $acta = $this->actaModel->find($idActa);

        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acta no encontrada']);
        }

        // Verificar permiso para cerrar
        $miembroEnComite = $this->miembroModel
            ->where('id_comite', $acta['id_comite'])
            ->where('email', $this->miembroActual['email'])
            ->where('estado', 'activo')
            ->first();

        if (!$miembroEnComite || !$miembroEnComite['puede_cerrar_actas']) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tiene permiso para cerrar actas']);
        }

        if (!in_array($acta['estado'], ['borrador', 'en_edicion'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Esta acta ya fue cerrada']);
        }

        // Verificar quórum
        if (!$this->asistentesModel->hayQuorum($idActa)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No hay quórum suficiente']);
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
                        'notificacion_enviada_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Acta cerrada. Se enviarán las solicitudes de firma.',
                'redirect' => "/miembro/{$token}/acta/{$idActa}"
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al cerrar el acta']);
    }

    /**
     * Ver compromisos asignados al miembro
     */
    public function misCompromisos(string $token)
    {
        if (!$this->validarAcceso($token)) {
            return view('actas/publico/token_invalido', [
                'mensaje' => 'Acceso denegado o enlace expirado.'
            ]);
        }

        $compromisos = $this->compromisosModel->getByResponsable(
            $this->miembroActual['email'],
            $this->miembroActual['id_cliente']
        );

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($this->miembroActual['id_cliente']);

        return view('actas/miembro/mis_compromisos', [
            'token' => $token,
            'miembro' => $this->miembroActual,
            'cliente' => $cliente,
            'compromisos' => $compromisos
        ]);
    }
}
