<?php

namespace App\Controllers;

use App\Models\ResponsableSSTModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;

/**
 * Controlador para gestionar responsables del SG-SST
 */
class ResponsablesSSTController extends BaseController
{
    protected ResponsableSSTModel $responsableModel;
    protected ClientModel $clienteModel;

    public function __construct()
    {
        $this->responsableModel = new ResponsableSSTModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Lista responsables de un cliente
     */
    public function index(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener contexto SST para saber estándares aplicables
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Obtener responsables agrupados
        $responsablesAgrupados = $this->responsableModel->getByClienteAgrupados($idCliente);

        // Verificar roles obligatorios
        $verificacion = $this->responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        $data = [
            'titulo' => 'Responsables SG-SST',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estandares' => $estandares,
            'responsablesAgrupados' => $responsablesAgrupados,
            'verificacion' => $verificacion,
            'tiposRol' => ResponsableSSTModel::TIPOS_ROL
        ];

        return view('responsables_sst/index', $data);
    }

    /**
     * Formulario para crear nuevo responsable
     */
    public function crear(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $data = [
            'titulo' => 'Agregar Responsable',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'tiposRol' => $this->getRolesDisponibles($estandares),
            'responsable' => null
        ];

        return view('responsables_sst/formulario', $data);
    }

    /**
     * Formulario para editar responsable
     */
    public function editar(int $idCliente, int $idResponsable)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $responsable = $this->responsableModel->find($idResponsable);
        if (!$responsable || $responsable['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Responsable no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $data = [
            'titulo' => 'Editar Responsable',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'tiposRol' => $this->getRolesDisponibles($estandares),
            'responsable' => $responsable
        ];

        return view('responsables_sst/formulario', $data);
    }

    /**
     * Guarda responsable (crear o actualizar)
     */
    public function guardar(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $idResponsable = $this->request->getPost('id_responsable');

        $datos = [
            'id_cliente' => $idCliente,
            'tipo_rol' => $this->request->getPost('tipo_rol'),
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'tipo_documento' => $this->request->getPost('tipo_documento') ?? 'CC',
            'numero_documento' => $this->request->getPost('numero_documento'),
            'cargo' => $this->request->getPost('cargo'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'licencia_sst_numero' => $this->request->getPost('licencia_sst_numero'),
            'licencia_sst_vigencia' => $this->request->getPost('licencia_sst_vigencia') ?: null,
            'formacion_sst' => $this->request->getPost('formacion_sst'),
            'fecha_inicio' => $this->request->getPost('fecha_inicio') ?: null,
            'fecha_fin' => $this->request->getPost('fecha_fin') ?: null,
            'acta_nombramiento' => $this->request->getPost('acta_nombramiento'),
            'observaciones' => $this->request->getPost('observaciones'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        // Validación básica
        if (empty($datos['nombre_completo']) || empty($datos['numero_documento']) || empty($datos['tipo_rol'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nombre, documento y tipo de rol son obligatorios'
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Nombre, documento y tipo de rol son obligatorios');
        }

        try {
            if ($idResponsable) {
                // Actualizar
                $this->responsableModel->update($idResponsable, $datos);
                $mensaje = 'Responsable actualizado correctamente';
            } else {
                // Crear
                $this->responsableModel->insert($datos);
                $mensaje = 'Responsable agregado correctamente';
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => $mensaje]);
            }

            return redirect()->to("responsables-sst/{$idCliente}")->with('success', $mensaje);

        } catch (\Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $error]);
            }
            return redirect()->back()->withInput()->with('error', $error);
        }
    }

    /**
     * Elimina un responsable
     */
    public function eliminar(int $idCliente, int $idResponsable)
    {
        $responsable = $this->responsableModel->find($idResponsable);

        if (!$responsable || $responsable['id_cliente'] != $idCliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Responsable no encontrado']);
            }
            return redirect()->back()->with('error', 'Responsable no encontrado');
        }

        try {
            $this->responsableModel->delete($idResponsable);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Responsable eliminado']);
            }

            return redirect()->to("responsables-sst/{$idCliente}")->with('success', 'Responsable eliminado');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * API: Obtiene responsables para documentos
     */
    public function apiObtener(int $idCliente)
    {
        $responsables = $this->responsableModel->getByCliente($idCliente);

        return $this->response->setJSON([
            'success' => true,
            'data' => $responsables
        ]);
    }

    /**
     * API: Verifica roles obligatorios
     */
    public function apiVerificar(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $verificacion = $this->responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        return $this->response->setJSON([
            'success' => true,
            'data' => $verificacion
        ]);
    }

    /**
     * Migra datos del contexto antiguo
     */
    public function migrar(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        if (!$contexto) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay contexto SST para migrar'
            ]);
        }

        $migrados = $this->responsableModel->migrarDesdeContexto($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Migración completada',
            'migrados' => $migrados
        ]);
    }

    /**
     * Obtiene roles disponibles según estándares
     *
     * Según Resolución 0312/2019:
     * - 7 estándares (< 10 trabajadores): Solo Vigía SST, NO COPASST
     * - 21 estándares (10-50 trabajadores): COPASST obligatorio, NO Vigía
     * - 60 estándares (> 50 trabajadores o Riesgo IV-V): COPASST + Comité Convivencia
     */
    private function getRolesDisponibles(int $estandares): array
    {
        $todos = ResponsableSSTModel::TIPOS_ROL;

        if ($estandares <= 7) {
            // 7 estándares: Solo Vigía SST, NO COPASST ni Comité Convivencia
            // Tampoco requiere Responsable interno del SG-SST (lo gestiona consultor externo)
            unset(
                $todos['responsable_sgsst'],
                $todos['copasst_presidente'],
                $todos['copasst_secretario'],
                $todos['copasst_representante_empleador'],
                $todos['copasst_representante_trabajadores'],
                $todos['copasst_suplente_empleador'],
                $todos['copasst_suplente_trabajadores'],
                $todos['comite_convivencia_presidente'],
                $todos['comite_convivencia_secretario'],
                $todos['comite_convivencia_miembro']
            );
        } elseif ($estandares <= 21) {
            // 21 estándares: COPASST obligatorio, NO Vigía
            // Comité de Convivencia es obligatorio para todas las empresas
            unset(
                $todos['vigia_sst'],
                $todos['vigia_sst_suplente']
            );
        } else {
            // 60 estándares: COPASST + Comité Convivencia, NO Vigía
            unset(
                $todos['vigia_sst'],
                $todos['vigia_sst_suplente']
            );
        }

        return $todos;
    }
}
