<?php

namespace App\Controllers;

use App\Models\ProfesiogramaCatalogoModel;
use App\Models\ProfesiogramaClienteModel;
use App\Models\IpevrMatrizModel;
use App\Models\IpevrFilaModel;
use App\Models\Gtc45CatalogoModel;
use App\Models\CargoClienteModel;
use App\Libraries\ProfesiogramaIaSugeridor;
use App\Libraries\ProfesiogramaExportXlsx;
use CodeIgniter\Controller;
use Config\Database;

class ProfesiogramaController extends Controller
{
    protected ProfesiogramaCatalogoModel $catalogoModel;
    protected ProfesiogramaClienteModel $profModel;
    protected CargoClienteModel $cargoModel;

    public function __construct()
    {
        $this->catalogoModel = new ProfesiogramaCatalogoModel();
        $this->profModel     = new ProfesiogramaClienteModel();
        $this->cargoModel    = new CargoClienteModel();
    }

    // ─── Vista principal: cargos con resumen de examenes ─────────────
    public function index(int $idCliente)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        // Verificar que exista IPEVR vigente o aprobada
        $matrizModel = new IpevrMatrizModel();
        $ipevr = $matrizModel->where('id_cliente', $idCliente)
            ->whereIn('estado', ['vigente', 'aprobada'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$ipevr) {
            return view('profesiograma/sin_ipevr', [
                'titulo'  => 'Profesiograma',
                'cliente' => $cliente,
            ]);
        }

        // Cargos del cliente
        $cargos = $this->cargoModel->porCliente($idCliente);

        // Resumen de examenes asignados por cargo
        $resumenRaw = $this->profModel->resumenPorCliente($idCliente);
        $resumen = []; // id_cargo => ['ingreso'=>N, 'periodico'=>N, 'retiro'=>N, 'total'=>N]
        foreach ($resumenRaw as $r) {
            $idC = (int)($r['id_cargo'] ?? 0);
            if (!isset($resumen[$idC])) {
                $resumen[$idC] = ['ingreso' => 0, 'periodico' => 0, 'retiro' => 0, 'cambio_cargo' => 0, 'total' => 0];
            }
            $resumen[$idC][$r['momento']] = (int)$r['total'];
            $resumen[$idC]['total'] += (int)$r['total'];
        }

        return view('profesiograma/index', [
            'titulo'  => 'Profesiograma',
            'cliente' => $cliente,
            'cargos'  => $cargos,
            'resumen' => $resumen,
            'ipevr'   => $ipevr,
        ]);
    }

    // ─── Editor por cargo: examenes asignados + catalogo ─────────────
    public function editorCargo(int $idCliente, int $idCargo)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        $cargo = $this->cargoModel->find($idCargo);
        if (!$cargo) {
            return $this->response->setStatusCode(404)->setBody('Cargo no encontrado');
        }

        $asignados = $this->profModel->examenesCargoConCatalogo($idCliente, $idCargo);
        $catalogo  = $this->catalogoModel->activos();

        // Construir mapa de asignaciones: examen_id => [momento => row]
        $mapaAsignados = [];
        foreach ($asignados as $a) {
            $mapaAsignados[(int)$a['id_examen']][$a['momento']] = $a;
        }

        return view('profesiograma/editor_cargo', [
            'titulo'        => 'Profesiograma - ' . $cargo['nombre_cargo'],
            'cliente'       => $cliente,
            'cargo'         => $cargo,
            'catalogo'      => $catalogo,
            'mapaAsignados' => $mapaAsignados,
        ]);
    }

    // ─── AJAX: asignar/actualizar examen a cargo ─────────────────────
    public function asignarExamen()
    {
        $id         = (int)$this->request->getPost('id');
        $idCliente  = (int)$this->request->getPost('id_cliente');
        $idCargo    = (int)$this->request->getPost('id_cargo');
        $idExamen   = (int)$this->request->getPost('id_examen');
        $momento    = $this->request->getPost('momento');
        $frecuencia = $this->request->getPost('frecuencia') ?: null;
        $obligatorio = (int)$this->request->getPost('obligatorio');
        $observaciones = $this->request->getPost('observaciones') ?: null;

        if (!$idCliente || !$idCargo || !$idExamen || !$momento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Faltan campos obligatorios']);
        }

        $data = [
            'id_cliente'    => $idCliente,
            'id_cargo'      => $idCargo,
            'id_examen'     => $idExamen,
            'momento'       => $momento,
            'frecuencia'    => $frecuencia,
            'obligatorio'   => $obligatorio,
            'observaciones' => $observaciones,
            'activo'        => 1,
        ];

        if ($id > 0) {
            $this->profModel->update($id, $data);
        } else {
            // Verificar si ya existe (upsert)
            $existente = $this->profModel
                ->where('id_cliente', $idCliente)
                ->where('id_cargo', $idCargo)
                ->where('id_examen', $idExamen)
                ->where('momento', $momento)
                ->first();

            if ($existente) {
                $id = (int)$existente['id'];
                $this->profModel->update($id, $data);
            } else {
                $data['origen'] = $this->request->getPost('origen') ?: 'manual';
                $id = (int)$this->profModel->insert($data);
            }
        }

        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    // ─── AJAX: quitar examen (soft-delete) ───────────────────────────
    public function quitarExamen(int $id)
    {
        $row = $this->profModel->find($id);
        if (!$row) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No encontrado']);
        }
        $this->profModel->desactivar($id);
        return $this->response->setJSON(['ok' => true]);
    }

    // ─── Generacion automatica desde IPEVR ───────────────────────────
    public function generarDesdeIpevr(int $idCliente)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Cliente no encontrado']);
        }

        // Buscar IPEVR vigente o aprobada
        $matrizModel = new IpevrMatrizModel();
        $ipevr = $matrizModel->where('id_cliente', $idCliente)
            ->whereIn('estado', ['vigente', 'aprobada'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$ipevr) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'No existe Matriz IPEVR vigente o aprobada. Primero complete y apruebe la Matriz IPEVR GTC 45.',
            ]);
        }

        // Leer filas IPEVR
        $filaModel = new IpevrFilaModel();
        $filas = $filaModel->porMatriz((int)$ipevr['id']);

        if (empty($filas)) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'La Matriz IPEVR no tiene filas registradas.',
            ]);
        }

        // Cargar catalogo de clasificaciones GTC45 (codigo por id)
        $gtc = new Gtc45CatalogoModel();
        $clasificaciones = $gtc->clasificaciones();
        $mapaCodigos = []; // id => codigo
        foreach ($clasificaciones as $cl) {
            $mapaCodigos[(int)$cl['id']] = $cl['codigo'];
        }

        // Cargar cargos del cliente (nombre => id)
        $cargosCliente = $this->cargoModel->porCliente($idCliente);
        $mapaCargos = []; // nombre_normalizado => id
        foreach ($cargosCliente as $c) {
            $mapaCargos[mb_strtolower(trim($c['nombre_cargo']))] = (int)$c['id'];
        }

        // Extraer pares (cargo, clasificacion) desde IPEVR
        $paresCargoClas = []; // cargo_id => [clasificacion_codigo, ...]
        foreach ($filas as $fila) {
            $cargosJson = $fila['cargos_expuestos'] ?? '[]';
            $cargos = json_decode($cargosJson, true) ?: [];
            $idClas = (int)($fila['id_clasificacion'] ?? 0);
            $codigoClas = $mapaCodigos[$idClas] ?? null;

            if (!$codigoClas) continue;

            foreach ($cargos as $cargoNombre) {
                $key = mb_strtolower(trim($cargoNombre));
                $idCargo = $mapaCargos[$key] ?? null;
                if (!$idCargo) continue; // Solo cargos registrados en maestros

                if (!isset($paresCargoClas[$idCargo])) {
                    $paresCargoClas[$idCargo] = [];
                }
                if (!in_array($codigoClas, $paresCargoClas[$idCargo])) {
                    $paresCargoClas[$idCargo][] = $codigoClas;
                }
            }
        }

        // Cruzar contra catalogo de examenes
        $catalogoExamenes = $this->catalogoModel->activos();
        $insertados = 0;
        $duplicados = 0;

        foreach ($paresCargoClas as $idCargo => $clasificacionesCargo) {
            foreach ($catalogoExamenes as $examen) {
                $aplica = json_decode($examen['clasificaciones_aplica'] ?? '[]', true) ?: [];

                // Verificar si alguna clasificacion del cargo coincide
                $coincide = !empty(array_intersect($clasificacionesCargo, $aplica));
                if (!$coincide) continue;

                // Determinar momentos: siempre ingreso y periodico; retiro si aplica
                $momentos = ['ingreso', 'periodico'];
                if ((int)$examen['aplica_retiro'] === 1) {
                    $momentos[] = 'retiro';
                }

                foreach ($momentos as $momento) {
                    // Verificar si ya existe
                    $existe = $this->profModel
                        ->where('id_cliente', $idCliente)
                        ->where('id_cargo', $idCargo)
                        ->where('id_examen', (int)$examen['id'])
                        ->where('momento', $momento)
                        ->first();

                    if ($existe) {
                        // Reactivar si estaba desactivado
                        if ((int)$existe['activo'] === 0) {
                            $this->profModel->update($existe['id'], ['activo' => 1, 'origen' => 'ipevr']);
                            $insertados++;
                        } else {
                            $duplicados++;
                        }
                        continue;
                    }

                    $this->profModel->insert([
                        'id_cliente'  => $idCliente,
                        'id_cargo'    => $idCargo,
                        'id_examen'   => (int)$examen['id'],
                        'momento'     => $momento,
                        'frecuencia'  => $examen['frecuencia_sugerida'] ?? 'anual',
                        'obligatorio' => 1,
                        'origen'      => 'ipevr',
                        'activo'      => 1,
                    ]);
                    $insertados++;
                }
            }
        }

        return $this->response->setJSON([
            'ok'         => true,
            'insertados' => $insertados,
            'duplicados' => $duplicados,
            'cargos'     => count($paresCargoClas),
            'mensaje'    => "Se generaron {$insertados} asignaciones para " . count($paresCargoClas) . " cargos ({$duplicados} ya existian).",
        ]);
    }

    // ─── AJAX: sugerir examenes con IA ─────────────────────────────
    public function sugerirIa(int $idCliente, int $idCargo)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Cliente no encontrado']);
        }

        $cargo = $this->cargoModel->find($idCargo);
        if (!$cargo) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Cargo no encontrado']);
        }

        // Contexto SST del cliente
        $contexto = $db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$contexto) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay contexto SST del cliente. Complete primero el contexto.']);
        }

        // IPEVR vigente/aprobada
        $matrizModel = new IpevrMatrizModel();
        $ipevr = $matrizModel->where('id_cliente', $idCliente)
            ->whereIn('estado', ['vigente', 'aprobada'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$ipevr) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No existe IPEVR vigente o aprobada.']);
        }

        // Filas IPEVR que mencionan este cargo
        $filaModel = new IpevrFilaModel();
        $todasFilas = $filaModel->porMatriz((int)$ipevr['id']);

        $cargoNombre = mb_strtolower(trim($cargo['nombre_cargo']));
        $filasDelCargo = [];
        $clasificacionesCargo = [];

        $gtc = new Gtc45CatalogoModel();
        $clasificacionesGtc = $gtc->clasificaciones();
        $mapaCodigos = [];
        foreach ($clasificacionesGtc as $cl) {
            $mapaCodigos[(int)$cl['id']] = $cl['codigo'];
        }

        foreach ($todasFilas as $fila) {
            $cargos = json_decode($fila['cargos_expuestos'] ?? '[]', true) ?: [];
            $mencionaCargo = false;
            foreach ($cargos as $c) {
                if (mb_strtolower(trim($c)) === $cargoNombre) {
                    $mencionaCargo = true;
                    break;
                }
            }
            if ($mencionaCargo) {
                $filasDelCargo[] = $fila;
                $cod = $mapaCodigos[(int)($fila['id_clasificacion'] ?? 0)] ?? null;
                if ($cod && !in_array($cod, $clasificacionesCargo)) {
                    $clasificacionesCargo[] = $cod;
                }
            }
        }

        // Examenes ya asignados
        $yaAsignados = $this->profModel->porCargo($idCliente, $idCargo);

        // Catalogo
        $catalogoExamenes = $this->catalogoModel->activos();

        // Llamar IA
        $ia = new ProfesiogramaIaSugeridor();
        if (!$ia->disponible()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'OPENAI_API_KEY no configurada en el servidor.']);
        }

        $resultado = $ia->sugerir(
            $cliente, $contexto, $cargo, $clasificacionesCargo,
            $filasDelCargo, $catalogoExamenes, $yaAsignados
        );

        if (!$resultado['ok']) {
            return $this->response->setJSON($resultado);
        }

        return $this->response->setJSON([
            'ok'          => true,
            'sugerencias' => $resultado['sugerencias'],
            'peligros'    => $clasificacionesCargo,
            'filas_ipevr' => count($filasDelCargo),
        ]);
    }

    // ─── AJAX: aplicar sugerencias de IA (insertar examenes) ─────────
    public function aplicarSugerenciasIa()
    {
        $idCliente = (int)$this->request->getPost('id_cliente');
        $idCargo   = (int)$this->request->getPost('id_cargo');
        $sugerencias = $this->request->getPost('sugerencias'); // JSON string

        if (!$idCliente || !$idCargo || !$sugerencias) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Faltan datos']);
        }

        $items = json_decode($sugerencias, true);
        if (!is_array($items)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'JSON invalido']);
        }

        $insertados = 0;
        $duplicados = 0;

        foreach ($items as $item) {
            $idExamen = (int)($item['id_examen'] ?? 0);
            $momentos = $item['momentos'] ?? [];
            $frecuencia = $item['frecuencia'] ?? 'anual';
            $obligatorio = (int)($item['obligatorio'] ?? 1);
            $observaciones = $item['observaciones'] ?? '';

            if (!$idExamen || empty($momentos)) continue;

            foreach ($momentos as $momento) {
                $existe = $this->profModel
                    ->where('id_cliente', $idCliente)
                    ->where('id_cargo', $idCargo)
                    ->where('id_examen', $idExamen)
                    ->where('momento', $momento)
                    ->first();

                if ($existe) {
                    if ((int)$existe['activo'] === 0) {
                        $this->profModel->update($existe['id'], [
                            'activo' => 1, 'origen' => 'ia',
                            'frecuencia' => $frecuencia,
                            'observaciones' => $observaciones,
                        ]);
                        $insertados++;
                    } else {
                        $duplicados++;
                    }
                    continue;
                }

                $this->profModel->insert([
                    'id_cliente'    => $idCliente,
                    'id_cargo'      => $idCargo,
                    'id_examen'     => $idExamen,
                    'momento'       => $momento,
                    'frecuencia'    => $frecuencia,
                    'obligatorio'   => $obligatorio,
                    'observaciones' => $observaciones,
                    'origen'        => 'ia',
                    'activo'        => 1,
                ]);
                $insertados++;
            }
        }

        return $this->response->setJSON([
            'ok'         => true,
            'insertados' => $insertados,
            'duplicados' => $duplicados,
        ]);
    }

    // ─── Exportar Excel ─────────────────────────────────────────────
    public function exportarXlsx(int $idCliente)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        $cargos = $this->cargoModel->porCliente($idCliente);
        $catalogo = $this->catalogoModel->activos();

        // Todas las asignaciones activas del cliente con datos del catalogo
        $asignaciones = $db->table('tbl_profesiograma_cliente p')
            ->select('p.*, c.nombre as examen_nombre, c.tipo_examen')
            ->join('tbl_profesiograma_examenes_catalogo c', 'c.id = p.id_examen')
            ->where('p.id_cliente', $idCliente)
            ->where('p.activo', 1)
            ->get()->getResultArray();

        $export = new ProfesiogramaExportXlsx();
        $tmpPath = $export->generar($cliente, $cargos, $asignaciones, $catalogo);

        $nombre = 'Profesiograma_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente'] ?? '') . '_' . date('Ymd') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombre . '"')
            ->setBody(file_get_contents($tmpPath));
    }

    // ─── AJAX: catalogo de examenes para selects ─────────────────────
    public function catalogoJson()
    {
        return $this->response->setJSON([
            'ok'       => true,
            'examenes' => $this->catalogoModel->activos(),
        ]);
    }
}
