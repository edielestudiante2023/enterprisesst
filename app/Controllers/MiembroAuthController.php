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

        return view('actas/miembro_auth/dashboard', [
            'miembro' => $miembro,
            'cliente' => $cliente,
            'comites' => $comites
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

        return view('actas/miembro_auth/comite', [
            'miembro' => $miembro,
            'miembroEnComite' => $miembroEnComite,
            'cliente' => $cliente,
            'comite' => $comite,
            'actas' => $actas
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

            foreach ($asistentes as $idMiembro) {
                $miembroData = $this->miembroModel->find($idMiembro);
                if ($miembroData) {
                    $this->asistentesModel->insert([
                        'id_acta' => $idActa,
                        'id_miembro' => $idMiembro,
                        'nombre_completo' => $miembroData['nombre_completo'],
                        'cargo' => $miembroData['cargo'],
                        'email' => $miembroData['email'],
                        'tipo_miembro' => $miembroData['tipo_miembro'],
                        'representacion' => $miembroData['representacion'],
                        'rol_comite' => $miembroData['rol_comite'],
                        'asistio' => isset($asistio[$idMiembro]) ? 1 : 0,
                        'justificacion_ausencia' => $justificacion[$idMiembro] ?? null
                    ]);
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
