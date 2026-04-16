<?php

namespace App\Controllers;

use App\Models\IpevrMatrizModel;
use App\Models\IpevrFilaModel;
use App\Models\Gtc45CatalogoModel;
use App\Models\ProcesoClienteModel;
use App\Models\CargoClienteModel;
use App\Models\TareaClienteModel;
use App\Models\ZonaClienteModel;
use CodeIgniter\Controller;
use Config\Database;

/**
 * IPEVR GTC 45 — Controller PWA (movil/tablet).
 */
class IpevrPwaController extends Controller
{
    protected IpevrMatrizModel $matrizModel;
    protected IpevrFilaModel $filaModel;
    protected Gtc45CatalogoModel $catalogo;

    public function __construct()
    {
        $this->matrizModel = new IpevrMatrizModel();
        $this->filaModel   = new IpevrFilaModel();
        $this->catalogo    = new Gtc45CatalogoModel();
    }

    public function editor(int $idMatriz)
    {
        $matriz = $this->matrizModel->find($idMatriz);
        if (!$matriz) {
            return $this->response->setStatusCode(404)->setBody('Matriz no encontrada');
        }

        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $matriz['id_cliente'])
            ->get()->getRowArray();

        $filas = $this->filaModel->porMatriz($idMatriz);

        return view('ipevr/editor_pwa', [
            'titulo'   => 'IPEVR v' . $matriz['version'],
            'matriz'   => $matriz,
            'cliente'  => $cliente,
            'filas'    => $filas,
            'catalogo' => $this->catalogo->bundleFrontend(),
            'maestros' => [
                'procesos' => (new ProcesoClienteModel())->porCliente((int)$matriz['id_cliente'], true),
                'cargos'   => (new CargoClienteModel())->porCliente((int)$matriz['id_cliente'], true),
                'tareas'   => (new TareaClienteModel())->porCliente((int)$matriz['id_cliente'], true),
                'zonas'    => (new ZonaClienteModel())->porCliente((int)$matriz['id_cliente'], true),
            ],
        ]);
    }

    /**
     * Autosave de fila en PWA. Reutiliza la logica de IpevrController::filaUpsert.
     * Se invoca desde la cola offline (ipevr_pwa_queue.js) o directamente al tocar "Siguiente".
     */
    public function autosave()
    {
        // Delegamos al controller PC reutilizando la logica de filaUpsert
        $ctrl = new \App\Controllers\IpevrController();
        $ctrl->initController($this->request, $this->response, \Config\Services::logger());
        return $ctrl->filaUpsert();
    }

    /**
     * Endpoint de verificacion de sync. Devuelve el conteo actual de filas.
     */
    public function sync()
    {
        $idMatriz = (int)$this->request->getGet('id_matriz');
        if (!$idMatriz) {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_matriz requerido']);
        }
        return $this->response->setJSON([
            'ok' => true,
            'total' => $this->filaModel->contarPorMatriz($idMatriz),
            'server_time' => date('c'),
        ]);
    }
}
