<?php

namespace App\Controllers;

use App\Models\EppCategoriaModel;
use App\Models\EppMaestroModel;
use App\Models\EppClienteModel;
use App\Services\MatrizEppIaService;
use App\Services\MatrizEppFotoService;
use CodeIgniter\Controller;
use Config\Database;

/**
 * Matriz de EPP y Dotacion - Catalogo maestro global y matriz por cliente.
 *
 * Hito A: CRUD del maestro, categorias, autocompletar IA, subida de foto.
 * Hitos B/C/D pendientes: asignacion masiva, matriz cliente, resync, clase doc.
 */
class MatrizEppController extends Controller
{
    protected EppCategoriaModel $categoriaModel;
    protected EppMaestroModel   $maestroModel;
    protected EppClienteModel   $clienteModel;

    public function __construct()
    {
        $this->categoriaModel = new EppCategoriaModel();
        $this->maestroModel   = new EppMaestroModel();
        $this->clienteModel   = new EppClienteModel();
    }

    // ========================= MAESTRO =========================

    public function maestroIndex()
    {
        $filtros = [
            'id_categoria' => $this->request->getGet('id_categoria'),
            'tipo'         => $this->request->getGet('tipo'),
            'ia_generado'  => $this->request->getGet('ia_generado'),
            'q'            => trim((string)$this->request->getGet('q')),
        ];
        $items = $this->maestroModel->listar($filtros);

        // Agrupar por categoria_nombre para pintar por bloques
        $grupos = [];
        foreach ($items as $it) {
            $grupos[$it['categoria_nombre'] ?? 'Sin categoria'][] = $it;
        }

        return view('matriz_epp/maestro_index', [
            'titulo'     => 'Catalogo Maestro de EPP y Dotacion',
            'categorias' => $this->categoriaModel->activas(),
            'grupos'     => $grupos,
            'filtros'    => $filtros,
            'totalItems' => count($items),
        ]);
    }

    public function maestroNuevo()
    {
        return view('matriz_epp/maestro_form', [
            'titulo'     => 'Nuevo elemento del catalogo',
            'categorias' => $this->categoriaModel->activas(),
            'item'       => null,
        ]);
    }

    public function maestroEditar(int $idEpp)
    {
        $item = $this->maestroModel->conCategoria($idEpp);
        if (!$item) {
            return redirect()->to(base_url('matrizEpp/maestro'))->with('error', 'Elemento no encontrado');
        }
        return view('matriz_epp/maestro_form', [
            'titulo'     => 'Editar elemento del catalogo',
            'categorias' => $this->categoriaModel->activas(),
            'item'       => $item,
        ]);
    }

    public function maestroGuardar()
    {
        $idEpp = (int)$this->request->getPost('id_epp');
        $data = [
            'id_categoria'      => (int)$this->request->getPost('id_categoria'),
            'elemento'          => trim((string)$this->request->getPost('elemento')),
            'norma'             => trim((string)$this->request->getPost('norma')),
            'mantenimiento'     => trim((string)$this->request->getPost('mantenimiento')),
            'frecuencia_cambio' => trim((string)$this->request->getPost('frecuencia_cambio')),
            'motivos_cambio'    => trim((string)$this->request->getPost('motivos_cambio')),
            'momentos_uso'      => trim((string)$this->request->getPost('momentos_uso')),
            'ia_generado'       => (int)$this->request->getPost('ia_generado') ? 1 : 0,
        ];

        if ($data['id_categoria'] <= 0 || $data['elemento'] === '') {
            return redirect()->back()->withInput()->with('error', 'Categoria y elemento son obligatorios');
        }

        if ($idEpp > 0) {
            $this->maestroModel->update($idEpp, $data);
            $nuevoId = $idEpp;
        } else {
            $data['activo'] = 1;
            $nuevoId = (int)$this->maestroModel->insert($data);
        }

        // Foto opcional
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fotoSvc = new MatrizEppFotoService();
            $res = $fotoSvc->guardar(
                $nuevoId,
                $file->getTempName(),
                $file->getMimeType() ?: '',
                (int)$file->getSize()
            );
            if ($res['ok']) {
                $this->maestroModel->update($nuevoId, ['foto_path' => $res['path']]);
            } else {
                return redirect()->to(base_url('matrizEpp/maestro/' . $nuevoId . '/editar'))
                    ->with('error', 'Elemento guardado pero la foto fallo: ' . $res['error']);
            }
        }

        return redirect()->to(base_url('matrizEpp/maestro'))
            ->with('success', 'Elemento guardado correctamente');
    }

    public function maestroDesactivar(int $idEpp)
    {
        $this->maestroModel->update($idEpp, ['activo' => 0]);
        return $this->response->setJSON(['ok' => true]);
    }

    public function maestroAutocompletar()
    {
        $elemento    = trim((string)$this->request->getPost('elemento'));
        $idCategoria = (int)$this->request->getPost('id_categoria');

        $catNombre = '';
        $catTipo   = 'EPP';
        if ($idCategoria > 0) {
            $cat = $this->categoriaModel->find($idCategoria);
            if ($cat) {
                $catNombre = $cat['nombre'];
                $catTipo   = $cat['tipo'];
            }
        }

        $ia = new MatrizEppIaService();
        $res = $ia->autocompletar($elemento, $catNombre, $catTipo);
        return $this->response->setJSON($res);
    }

    // ========================= ASIGNACION MASIVA =========================

    /**
     * POST /matrizEpp/asignarACliente
     * Body: { id_cliente, ids_epp[], sobrescribir }
     *
     * Copia el snapshot completo del maestro a tbl_epp_cliente.
     * Si existe y sobrescribir=0: se omite.
     * Si existe y sobrescribir=1: UPDATE con snapshot fresco del maestro (marca sincronizado=1).
     */
    public function asignarACliente()
    {
        $idCliente   = (int)$this->request->getPost('id_cliente');
        $idsEpp      = $this->request->getPost('ids_epp') ?? [];
        $sobrescribir = (int)$this->request->getPost('sobrescribir') ? 1 : 0;

        if ($idCliente <= 0 || empty($idsEpp) || !is_array($idsEpp)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Datos incompletos']);
        }

        $insertados = 0;
        $actualizados = 0;
        $omitidos = 0;
        $errores = [];

        $db = Database::connect();
        $db->transStart();

        foreach ($idsEpp as $idEpp) {
            $idEpp = (int)$idEpp;
            if ($idEpp <= 0) continue;

            $maestro = $this->maestroModel->find($idEpp);
            if (!$maestro) {
                $errores[] = "id_epp {$idEpp} no existe en maestro";
                continue;
            }

            $snapshot = [
                'id_cliente'           => $idCliente,
                'id_epp'               => $idEpp,
                'elemento'             => $maestro['elemento'],
                'norma'                => $maestro['norma'],
                'mantenimiento'        => $maestro['mantenimiento'],
                'frecuencia_cambio'    => $maestro['frecuencia_cambio'],
                'motivos_cambio'       => $maestro['motivos_cambio'],
                'momentos_uso'         => $maestro['momentos_uso'],
                'sincronizado_maestro' => 1,
                'fecha_ultima_sync'    => date('Y-m-d H:i:s'),
                'activo'               => 1,
            ];

            $existente = $this->clienteModel
                ->where('id_cliente', $idCliente)
                ->where('id_epp', $idEpp)
                ->first();

            if ($existente) {
                if ($sobrescribir) {
                    $this->clienteModel->update($existente['id'], $snapshot);
                    $actualizados++;
                } else {
                    $omitidos++;
                }
            } else {
                $this->clienteModel->insert($snapshot);
                $insertados++;
            }
        }

        $db->transComplete();

        return $this->response->setJSON([
            'ok'           => $db->transStatus(),
            'insertados'   => $insertados,
            'actualizados' => $actualizados,
            'omitidos'     => $omitidos,
            'errores'      => $errores,
        ]);
    }

    /**
     * GET /matrizEpp/clientesJson
     * Lista para Select2 del modal (devuelve id_cliente + nombre_cliente).
     */
    public function clientesJson()
    {
        $db = Database::connect();
        $rows = $db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->get()->getResultArray();
        return $this->response->setJSON(['ok' => true, 'clientes' => $rows]);
    }

    // ========================= MATRIZ CLIENTE =========================

    public function clienteMatriz(int $idCliente)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();
        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        $filas = $this->clienteModel->matrizCliente($idCliente);

        // Agrupar por categoria_nombre manteniendo orden
        $grupos = [];
        foreach ($filas as $f) {
            $grupos[$f['categoria_nombre'] ?? 'Sin categoria'][] = $f;
        }

        return view('matriz_epp/cliente_matriz', [
            'titulo'   => 'Matriz de EPP y Dotación',
            'cliente'  => $cliente,
            'grupos'   => $grupos,
            'total'    => count($filas),
        ]);
    }

    public function clienteEditarInline(int $idCliente, int $idEpp)
    {
        $campo = (string)$this->request->getPost('campo');
        $valor = (string)$this->request->getPost('valor');

        $permitidos = ['elemento', 'norma', 'mantenimiento', 'frecuencia_cambio', 'motivos_cambio', 'momentos_uso', 'observacion_cliente'];
        if (!in_array($campo, $permitidos, true)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Campo no permitido']);
        }

        $fila = $this->clienteModel
            ->where('id_cliente', $idCliente)
            ->where('id_epp', $idEpp)
            ->first();
        if (!$fila) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Asignación no encontrada']);
        }

        $data = [$campo => $valor];
        // observacion_cliente NO desincroniza del maestro (es campo propio del cliente)
        if ($campo !== 'observacion_cliente') {
            $data['sincronizado_maestro'] = 0;
        }

        $this->clienteModel->update($fila['id'], $data);

        return $this->response->setJSON([
            'ok' => true,
            'sincronizado_maestro' => (int)($data['sincronizado_maestro'] ?? $fila['sincronizado_maestro']),
        ]);
    }

    /**
     * GET /matrizEpp/cliente/{id_cliente}/item/{id_epp}/diffMaestro
     * Devuelve valores actuales del cliente vs. valores vigentes del maestro,
     * y una lista de campos con diferencias.
     */
    public function clienteDiffMaestro(int $idCliente, int $idEpp)
    {
        $cliente = $this->clienteModel
            ->where('id_cliente', $idCliente)
            ->where('id_epp', $idEpp)
            ->first();
        $maestro = $this->maestroModel->find($idEpp);

        if (!$cliente || !$maestro) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Ítem no encontrado']);
        }

        $campos = ['elemento', 'norma', 'mantenimiento', 'frecuencia_cambio', 'motivos_cambio', 'momentos_uso'];
        $cambios = [];
        foreach ($campos as $c) {
            if (($cliente[$c] ?? '') !== ($maestro[$c] ?? '')) {
                $cambios[] = $c;
            }
        }

        return $this->response->setJSON([
            'ok'      => true,
            'cliente' => array_intersect_key($cliente, array_flip($campos)),
            'maestro' => array_intersect_key($maestro, array_flip($campos)),
            'cambios' => $cambios,
            'sincronizado_maestro' => (int)$cliente['sincronizado_maestro'],
        ]);
    }

    /**
     * POST /matrizEpp/cliente/{id_cliente}/item/{id_epp}/resincronizar
     * Sobrescribe los 6 campos técnicos con los valores actuales del maestro.
     * NO toca observacion_cliente.
     */
    public function clienteResincronizarItem(int $idCliente, int $idEpp)
    {
        $fila = $this->clienteModel
            ->where('id_cliente', $idCliente)
            ->where('id_epp', $idEpp)
            ->first();
        $maestro = $this->maestroModel->find($idEpp);

        if (!$fila || !$maestro) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Ítem no encontrado']);
        }

        $this->clienteModel->update($fila['id'], [
            'elemento'             => $maestro['elemento'],
            'norma'                => $maestro['norma'],
            'mantenimiento'        => $maestro['mantenimiento'],
            'frecuencia_cambio'    => $maestro['frecuencia_cambio'],
            'motivos_cambio'       => $maestro['motivos_cambio'],
            'momentos_uso'         => $maestro['momentos_uso'],
            'sincronizado_maestro' => 1,
            'fecha_ultima_sync'    => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * POST /matrizEpp/cliente/{id_cliente}/resincronizarTodos
     * Body: { ids_epp[] } — ids seleccionados tras ver el diff global.
     */
    public function clienteResincronizarTodos(int $idCliente)
    {
        $ids = $this->request->getPost('ids_epp') ?? [];
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Sin ids a resincronizar']);
        }

        $actualizados = 0;
        $db = Database::connect();
        $db->transStart();

        foreach ($ids as $idEpp) {
            $idEpp = (int)$idEpp;
            if ($idEpp <= 0) continue;
            $fila = $this->clienteModel
                ->where('id_cliente', $idCliente)
                ->where('id_epp', $idEpp)
                ->first();
            $maestro = $this->maestroModel->find($idEpp);
            if (!$fila || !$maestro) continue;

            $this->clienteModel->update($fila['id'], [
                'elemento'             => $maestro['elemento'],
                'norma'                => $maestro['norma'],
                'mantenimiento'        => $maestro['mantenimiento'],
                'frecuencia_cambio'    => $maestro['frecuencia_cambio'],
                'motivos_cambio'       => $maestro['motivos_cambio'],
                'momentos_uso'         => $maestro['momentos_uso'],
                'sincronizado_maestro' => 1,
                'fecha_ultima_sync'    => date('Y-m-d H:i:s'),
            ]);
            $actualizados++;
        }

        $db->transComplete();
        return $this->response->setJSON(['ok' => $db->transStatus(), 'actualizados' => $actualizados]);
    }

    /**
     * GET /matrizEpp/cliente/{id_cliente}/diffMasivoJson
     * Devuelve, para el cliente, todos los ítems con diferencias respecto al maestro,
     * con su diff detallado para el modal global.
     */
    public function clienteDiffMasivo(int $idCliente)
    {
        $filas = $this->clienteModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->findAll();

        $campos = ['elemento', 'norma', 'mantenimiento', 'frecuencia_cambio', 'motivos_cambio', 'momentos_uso'];
        $diffs = [];
        foreach ($filas as $f) {
            $m = $this->maestroModel->find((int)$f['id_epp']);
            if (!$m) continue;
            $cambios = [];
            foreach ($campos as $c) {
                if (($f[$c] ?? '') !== ($m[$c] ?? '')) {
                    $cambios[] = $c;
                }
            }
            if (!empty($cambios)) {
                $diffs[] = [
                    'id_epp'  => (int)$f['id_epp'],
                    'elemento_cliente' => $f['elemento'],
                    'elemento_maestro' => $m['elemento'],
                    'cambios' => $cambios,
                ];
            }
        }

        return $this->response->setJSON(['ok' => true, 'diffs' => $diffs, 'total' => count($diffs)]);
    }

    public function clienteQuitarItem(int $idCliente, int $idEpp)
    {
        $fila = $this->clienteModel
            ->where('id_cliente', $idCliente)
            ->where('id_epp', $idEpp)
            ->first();
        if ($fila) {
            $this->clienteModel->delete($fila['id']);
        }
        return $this->response->setJSON(['ok' => true]);
    }

    // ========================= CATEGORIAS =========================

    public function categoriaGuardar()
    {
        $id = (int)$this->request->getPost('id_categoria');
        $data = [
            'nombre' => trim((string)$this->request->getPost('nombre')),
            'tipo'   => in_array($this->request->getPost('tipo'), ['EPP', 'DOTACION'], true)
                ? $this->request->getPost('tipo') : 'EPP',
            'orden'  => (int)$this->request->getPost('orden'),
        ];
        if ($data['nombre'] === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Nombre requerido']);
        }
        if ($id > 0) {
            $this->categoriaModel->update($id, $data);
        } else {
            $data['activo'] = 1;
            $id = (int)$this->categoriaModel->insert($data);
        }
        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    public function categoriaDesactivar(int $id)
    {
        $this->categoriaModel->update($id, ['activo' => 0]);
        return $this->response->setJSON(['ok' => true]);
    }
}
