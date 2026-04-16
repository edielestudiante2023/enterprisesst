<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ClientModel;
use App\Models\CargoClienteModel;
use App\Models\CompetenciaClienteModel;
use App\Models\CompetenciaNivelClienteModel;
use App\Models\CompetenciaEscalaClienteModel;
use App\Models\ClienteCompetenciaCargoModel;

/**
 * Diccionario de Competencias por cliente.
 * Acceso via dashboard del consultor.
 * Ver: docs/MODULO_DICCIONARIO_COMPETENCIAS/ARQUITECTURA.md
 */
class DiccionarioCompetenciasController extends Controller
{
    private const FAMILIAS = [
        'logro'              => 'Logro y accion',
        'ayuda_servicio'     => 'Ayuda y servicio',
        'influencia'         => 'Influencia',
        'gerenciales'        => 'Gerenciales',
        'cognitivas'         => 'Cognitivas',
        'eficacia_personal'  => 'Eficacia personal',
    ];

    private function requireSession(): ?int
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return null;
        }
        return (int)($session->get('id_usuario') ?? 0);
    }

    private function resolverCliente(int $idCliente): ?array
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        return $cliente ?: null;
    }

    /**
     * Listado del diccionario del cliente, agrupado por familia.
     * GET /diccionario-competencias/{id_cliente}
     */
    public function index(int $idCliente)
    {
        if (!$this->requireSession()) {
            return redirect()->to('/login');
        }

        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) {
            return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');
        }

        $competenciaModel = new CompetenciaClienteModel();
        $nivelModel       = new CompetenciaNivelClienteModel();

        $agrupadas     = $competenciaModel->porClienteAgrupadas($idCliente);
        $nivelesPorComp = $nivelModel->porCliente($idCliente);

        // Ordenar familias segun el orden canonico
        $ordenadas = [];
        foreach (self::FAMILIAS as $key => $label) {
            if (isset($agrupadas[$key])) {
                $ordenadas[$key] = $agrupadas[$key];
            }
        }
        foreach ($agrupadas as $key => $lista) {
            if (!isset($ordenadas[$key])) {
                $ordenadas[$key] = $lista;
            }
        }

        return view('diccionario_competencias/index', [
            'cliente'        => $cliente,
            'familias'       => self::FAMILIAS,
            'agrupadas'      => $ordenadas,
            'nivelesPorComp' => $nivelesPorComp,
            'total'          => $competenciaModel->contarPorCliente($idCliente),
        ]);
    }

    /**
     * Edicion de la escala 1-5 del cliente.
     * GET /diccionario-competencias/{id_cliente}/escala
     */
    public function escala(int $idCliente)
    {
        if (!$this->requireSession()) {
            return redirect()->to('/login');
        }
        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) {
            return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');
        }

        $escalaModel = new CompetenciaEscalaClienteModel();
        $escala = $escalaModel->porCliente($idCliente);

        return view('diccionario_competencias/escala', [
            'cliente' => $cliente,
            'escala'  => $escala,
        ]);
    }

    /**
     * POST /diccionario-competencias/{id_cliente}/escala/guardar
     * Actualiza los 5 niveles de la escala del cliente.
     */
    public function escalaGuardar(int $idCliente)
    {
        if (!$this->requireSession()) {
            return redirect()->to('/login');
        }
        $escalaModel = new CompetenciaEscalaClienteModel();
        $niveles = $this->request->getPost('niveles') ?? [];

        foreach ($niveles as $nivel => $datos) {
            $existente = $escalaModel->where('id_cliente', $idCliente)
                ->where('nivel', (int)$nivel)
                ->first();
            $payload = [
                'nombre'      => trim((string)($datos['nombre'] ?? '')),
                'etiqueta'    => trim((string)($datos['etiqueta'] ?? '')),
                'descripcion' => trim((string)($datos['descripcion'] ?? '')),
            ];
            if ($existente) {
                $escalaModel->update($existente['id_escala'], $payload);
            } else {
                $escalaModel->insert(array_merge($payload, [
                    'id_cliente' => $idCliente,
                    'nivel'      => (int)$nivel,
                ]));
            }
        }
        return redirect()->to("/diccionario-competencias/{$idCliente}/escala")
            ->with('ok', 'Escala actualizada');
    }

    /**
     * Matriz cargo <-> competencia del cliente.
     * GET /diccionario-competencias/{id_cliente}/matriz
     */
    public function matriz(int $idCliente)
    {
        if (!$this->requireSession()) {
            return redirect()->to('/login');
        }
        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) {
            return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');
        }

        $cargoModel       = new CargoClienteModel();
        $competenciaModel = new CompetenciaClienteModel();
        $matrizModel      = new ClienteCompetenciaCargoModel();

        $cargos       = $cargoModel->porCliente($idCliente, true);
        $competencias = $competenciaModel->porCliente($idCliente, true);
        $asignaciones = $matrizModel->porCliente($idCliente);

        // Indexar por (cargo, competencia) para el render rapido
        $idx = [];
        foreach ($asignaciones as $a) {
            $idx[$a['id_cargo_cliente']][$a['id_competencia']] = $a;
        }

        return view('diccionario_competencias/matriz', [
            'cliente'      => $cliente,
            'cargos'       => $cargos,
            'competencias' => $competencias,
            'familias'     => self::FAMILIAS,
            'asignaciones' => $idx,
        ]);
    }

    /**
     * POST /diccionario-competencias/{id_cliente}/matriz/guardar
     * Body: id_cargo_cliente, id_competencia, nivel_requerido, observacion
     * Upsert de una asignacion. Respuesta JSON.
     */
    public function matrizGuardar(int $idCliente)
    {
        if (!$this->requireSession()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'No autenticado']);
        }

        $idCargo   = (int)$this->request->getPost('id_cargo_cliente');
        $idComp    = (int)$this->request->getPost('id_competencia');
        $nivel     = (int)$this->request->getPost('nivel_requerido');
        $obs       = $this->request->getPost('observacion');
        $obs       = is_string($obs) ? trim($obs) : null;

        if ($idCargo <= 0 || $idComp <= 0 || $nivel < 1 || $nivel > 5) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok'    => false,
                'error' => 'Parametros invalidos (nivel debe estar entre 1 y 5)',
            ]);
        }

        // Validar propiedad cliente
        $cargoModel = new CargoClienteModel();
        $cargo = $cargoModel->find($idCargo);
        if (!$cargo || (int)$cargo['id_cliente'] !== $idCliente) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Cargo no pertenece al cliente']);
        }
        $competenciaModel = new CompetenciaClienteModel();
        $comp = $competenciaModel->find($idComp);
        if (!$comp || (int)$comp['id_cliente'] !== $idCliente) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Competencia no pertenece al cliente']);
        }

        $matrizModel = new ClienteCompetenciaCargoModel();
        $matrizModel->upsertAsignacion($idCliente, $idCargo, $idComp, $nivel, $obs ?: null);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * GET /diccionario-competencias/{id_cliente}/clientes-origen
     * Devuelve JSON con los clientes que ya tienen diccionario poblado,
     * excluyendo el destino. Usado por el modal de clonado.
     */
    public function clientesOrigen(int $idCliente)
    {
        if (!$this->requireSession()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_competencia_cliente cc')
            ->select('cc.id_cliente, c.nombre_cliente, COUNT(*) as total')
            ->join('tbl_clientes c', 'c.id_cliente = cc.id_cliente', 'inner')
            ->where('cc.activo', 1)
            ->where('cc.id_cliente !=', $idCliente)
            ->groupBy('cc.id_cliente')
            ->orderBy('c.nombre_cliente', 'ASC')
            ->get()
            ->getResultArray();
        return $this->response->setJSON(['ok' => true, 'clientes' => $rows]);
    }

    /**
     * POST /diccionario-competencias/{id_cliente}/clonar-desde/{id_origen}
     * Body: incluir_matriz (0/1), forzar (0/1)
     *
     * Clona escala + competencias + niveles del cliente origen al destino.
     * Si incluir_matriz=1, intenta clonar asignaciones cargo-competencia
     * haciendo match por nombre_cargo (case-insensitive, trim).
     */
    public function clonarDesde(int $idCliente, int $idOrigen)
    {
        if (!$this->requireSession()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'No autenticado']);
        }
        if ($idOrigen === $idCliente) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'Origen y destino no pueden ser el mismo cliente']);
        }

        $incluirMatriz = (int)$this->request->getPost('incluir_matriz') === 1;
        $forzar        = (int)$this->request->getPost('forzar') === 1;

        // Usamos query builder raw para bypass TenantScopedModel en lecturas cross-cliente
        $db = \Config\Database::connect();

        // Validar origen tenga competencias
        $totalOrigen = (int)$db->table('tbl_competencia_cliente')
            ->where('id_cliente', $idOrigen)->where('activo', 1)
            ->countAllResults();
        if ($totalOrigen === 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'El cliente origen no tiene competencias']);
        }

        // Validar destino vacio (o forzar)
        $totalDestino = (int)$db->table('tbl_competencia_cliente')
            ->where('id_cliente', $idCliente)
            ->countAllResults();
        if ($totalDestino > 0 && !$forzar) {
            return $this->response->setStatusCode(409)->setJSON([
                'ok'    => false,
                'error' => "El cliente destino ya tiene {$totalDestino} competencias. Activa forzar para reemplazar.",
            ]);
        }

        try {
            $db->transBegin();

            if ($totalDestino > 0 && $forzar) {
                // CASCADE borra niveles y asignaciones
                $db->table('tbl_cliente_competencia_cargo')->where('id_cliente', $idCliente)->delete();
                $db->table('tbl_competencia_cliente')->where('id_cliente', $idCliente)->delete();
                $db->table('tbl_competencia_escala_cliente')->where('id_cliente', $idCliente)->delete();
            }

            // 1) Escala
            $escalaOrigen = $db->table('tbl_competencia_escala_cliente')
                ->where('id_cliente', $idOrigen)
                ->orderBy('nivel', 'ASC')->get()->getResultArray();
            foreach ($escalaOrigen as $e) {
                $db->table('tbl_competencia_escala_cliente')->insert([
                    'id_cliente'  => $idCliente,
                    'nivel'       => (int)$e['nivel'],
                    'nombre'      => $e['nombre'],
                    'etiqueta'    => $e['etiqueta'],
                    'descripcion' => $e['descripcion'],
                ]);
            }

            // 2) Competencias + niveles
            $mapaComp = [];
            $compsOrigen = $db->table('tbl_competencia_cliente')
                ->where('id_cliente', $idOrigen)
                ->orderBy('numero', 'ASC')->get()->getResultArray();
            foreach ($compsOrigen as $c) {
                $db->table('tbl_competencia_cliente')->insert([
                    'id_cliente'     => $idCliente,
                    'numero'         => (int)$c['numero'],
                    'codigo'         => $c['codigo'],
                    'nombre'         => $c['nombre'],
                    'definicion'     => $c['definicion'],
                    'pregunta_clave' => $c['pregunta_clave'],
                    'familia'        => $c['familia'],
                    'activo'         => (int)$c['activo'],
                ]);
                $nuevoId = (int)$db->insertID();
                $mapaComp[(int)$c['id_competencia']] = $nuevoId;

                $niveles = $db->table('tbl_competencia_nivel_cliente')
                    ->where('id_competencia', (int)$c['id_competencia'])
                    ->orderBy('nivel_numero', 'ASC')->get()->getResultArray();
                foreach ($niveles as $n) {
                    $db->table('tbl_competencia_nivel_cliente')->insert([
                        'id_competencia'       => $nuevoId,
                        'nivel_numero'         => (int)$n['nivel_numero'],
                        'titulo_corto'         => $n['titulo_corto'],
                        'descripcion_conducta' => $n['descripcion_conducta'],
                    ]);
                }
            }

            // 3) Matriz cargo-competencia (opcional)
            $matrizCopiadas = 0;
            $matrizOmitidas = 0;
            if ($incluirMatriz) {
                $cargosOrigen  = $db->table('tbl_cargos_cliente')->where('id_cliente', $idOrigen)->get()->getResultArray();
                $cargosDestino = $db->table('tbl_cargos_cliente')->where('id_cliente', $idCliente)->get()->getResultArray();

                $normalizar = fn($s) => mb_strtolower(trim((string)$s));
                $indexDestino = [];
                foreach ($cargosDestino as $cd) {
                    $indexDestino[$normalizar($cd['nombre_cargo'])] = (int)$cd['id'];
                }
                $mapaCargo = [];
                foreach ($cargosOrigen as $co) {
                    $k = $normalizar($co['nombre_cargo']);
                    if (isset($indexDestino[$k])) {
                        $mapaCargo[(int)$co['id']] = $indexDestino[$k];
                    }
                }

                $asignaciones = $db->table('tbl_cliente_competencia_cargo')
                    ->where('id_cliente', $idOrigen)->get()->getResultArray();
                foreach ($asignaciones as $a) {
                    $idCargoO = (int)$a['id_cargo_cliente'];
                    $idCompO  = (int)$a['id_competencia'];
                    if (isset($mapaCargo[$idCargoO], $mapaComp[$idCompO])) {
                        $db->table('tbl_cliente_competencia_cargo')->insert([
                            'id_cliente'       => $idCliente,
                            'id_cargo_cliente' => $mapaCargo[$idCargoO],
                            'id_competencia'   => $mapaComp[$idCompO],
                            'nivel_requerido'  => (int)$a['nivel_requerido'],
                            'observacion'      => $a['observacion'],
                        ]);
                        $matrizCopiadas++;
                    } else {
                        $matrizOmitidas++;
                    }
                }
            }

            $db->transCommit();

            return $this->response->setJSON([
                'ok' => true,
                'resumen' => [
                    'escala'           => count($escalaOrigen),
                    'competencias'     => count($compsOrigen),
                    'matriz_copiadas'  => $matrizCopiadas,
                    'matriz_omitidas'  => $matrizOmitidas,
                ],
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'clonarDesde error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'ok'    => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /diccionario-competencias/{id_cliente}/matriz/eliminar
     * Body: id_cargo_cliente, id_competencia
     */
    public function matrizEliminar(int $idCliente)
    {
        if (!$this->requireSession()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'No autenticado']);
        }
        $idCargo = (int)$this->request->getPost('id_cargo_cliente');
        $idComp  = (int)$this->request->getPost('id_competencia');

        $matrizModel = new ClienteCompetenciaCargoModel();
        $matrizModel->where('id_cliente', $idCliente)
            ->where('id_cargo_cliente', $idCargo)
            ->where('id_competencia', $idComp)
            ->delete();

        return $this->response->setJSON(['ok' => true]);
    }
}
