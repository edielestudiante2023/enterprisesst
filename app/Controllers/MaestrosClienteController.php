<?php

namespace App\Controllers;

use App\Models\ProcesoClienteModel;
use App\Models\CargoClienteModel;
use App\Models\TareaClienteModel;
use App\Models\ZonaClienteModel;
use CodeIgniter\Controller;
use Config\Database;

/**
 * Maestros por cliente: procesos, cargos, tareas, zonas.
 * Reutilizable por IPEVR, PTA, indicadores, etc.
 */
class MaestrosClienteController extends Controller
{
    protected ProcesoClienteModel $procesoModel;
    protected CargoClienteModel $cargoModel;
    protected TareaClienteModel $tareaModel;
    protected ZonaClienteModel $zonaModel;

    public function __construct()
    {
        $this->procesoModel = new ProcesoClienteModel();
        $this->cargoModel   = new CargoClienteModel();
        $this->tareaModel   = new TareaClienteModel();
        $this->zonaModel    = new ZonaClienteModel();
    }

    public function index(int $idCliente)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        $sedes = $db->table('tbl_cliente_sedes')
            ->where('id_cliente', $idCliente)
            ->orderBy('nombre_sede', 'ASC')
            ->get()->getResultArray();

        return view('maestros_cliente/index', [
            'titulo'   => 'Maestros del cliente',
            'cliente'  => $cliente,
            'sedes'    => $sedes,
            'procesos' => $this->procesoModel->porCliente($idCliente, false),
            'cargos'   => $this->cargoModel->porCliente($idCliente, false),
            'tareas'   => $this->tareaModel->porCliente($idCliente, false),
            'zonas'    => $this->zonaModel->porCliente($idCliente, false),
        ]);
    }

    // ---------- PROCESOS ----------
    public function procesoUpsert()
    {
        $data = [
            'id_cliente'     => (int)$this->request->getPost('id_cliente'),
            'nombre_proceso' => trim((string)$this->request->getPost('nombre_proceso')),
            'tipo'           => $this->request->getPost('tipo') ?: null,
            'descripcion'    => $this->request->getPost('descripcion') ?: null,
            'activo'         => 1,
        ];
        if (!$data['id_cliente'] || $data['nombre_proceso'] === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_cliente y nombre_proceso son requeridos']);
        }
        $id = (int)$this->request->getPost('id');
        if ($id > 0) {
            $this->procesoModel->update($id, $data);
        } else {
            $id = (int)$this->procesoModel->insert($data);
        }
        return $this->response->setJSON(['ok' => true, 'id' => $id, 'registro' => $this->procesoModel->find($id)]);
    }

    public function procesoEliminar(int $id)
    {
        $this->procesoModel->update($id, ['activo' => 0]);
        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    // ---------- CARGOS ----------
    public function cargoUpsert()
    {
        $data = [
            'id_cliente'    => (int)$this->request->getPost('id_cliente'),
            'id_proceso'    => $this->request->getPost('id_proceso') ? (int)$this->request->getPost('id_proceso') : null,
            'nombre_cargo'  => trim((string)$this->request->getPost('nombre_cargo')),
            'num_ocupantes' => (int)$this->request->getPost('num_ocupantes'),
            'descripcion'   => $this->request->getPost('descripcion') ?: null,
            'activo'        => 1,
        ];
        if (!$data['id_cliente'] || $data['nombre_cargo'] === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_cliente y nombre_cargo son requeridos']);
        }
        $id = (int)$this->request->getPost('id');
        if ($id > 0) {
            $this->cargoModel->update($id, $data);
        } else {
            $id = (int)$this->cargoModel->insert($data);
        }
        return $this->response->setJSON(['ok' => true, 'id' => $id, 'registro' => $this->cargoModel->find($id)]);
    }

    public function cargoEliminar(int $id)
    {
        $this->cargoModel->update($id, ['activo' => 0]);
        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    // ---------- TAREAS ----------
    public function tareaUpsert()
    {
        $data = [
            'id_cliente'   => (int)$this->request->getPost('id_cliente'),
            'id_proceso'   => $this->request->getPost('id_proceso') ? (int)$this->request->getPost('id_proceso') : null,
            'nombre_tarea' => trim((string)$this->request->getPost('nombre_tarea')),
            'rutinaria'    => $this->request->getPost('rutinaria') !== null ? (int)$this->request->getPost('rutinaria') : 1,
            'descripcion'  => $this->request->getPost('descripcion') ?: null,
            'activo'       => 1,
        ];
        if (!$data['id_cliente'] || $data['nombre_tarea'] === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_cliente y nombre_tarea son requeridos']);
        }
        $id = (int)$this->request->getPost('id');
        if ($id > 0) {
            $this->tareaModel->update($id, $data);
        } else {
            $id = (int)$this->tareaModel->insert($data);
        }
        return $this->response->setJSON(['ok' => true, 'id' => $id, 'registro' => $this->tareaModel->find($id)]);
    }

    public function tareaEliminar(int $id)
    {
        $this->tareaModel->update($id, ['activo' => 0]);
        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    // ---------- ZONAS ----------
    public function zonaUpsert()
    {
        $data = [
            'id_cliente'  => (int)$this->request->getPost('id_cliente'),
            'id_sede'     => $this->request->getPost('id_sede') ? (int)$this->request->getPost('id_sede') : null,
            'nombre_zona' => trim((string)$this->request->getPost('nombre_zona')),
            'descripcion' => $this->request->getPost('descripcion') ?: null,
            'activo'      => 1,
        ];
        if (!$data['id_cliente'] || $data['nombre_zona'] === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_cliente y nombre_zona son requeridos']);
        }
        $id = (int)$this->request->getPost('id');
        if ($id > 0) {
            $this->zonaModel->update($id, $data);
        } else {
            $id = (int)$this->zonaModel->insert($data);
        }
        return $this->response->setJSON(['ok' => true, 'id' => $id, 'registro' => $this->zonaModel->find($id)]);
    }

    public function zonaEliminar(int $id)
    {
        $this->zonaModel->update($id, ['activo' => 0]);
        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    // ---------- PLANTILLAS CSV ----------

    protected array $plantillas = [
        'proceso' => [
            'headers' => ['nombre_proceso', 'tipo', 'descripcion'],
            'ejemplo' => [
                ['Administrativo y Financiero', 'apoyo', 'Gestion contable, tesoreria, presupuesto y compras.'],
                ['Produccion', 'misional', 'Proceso principal de transformacion o prestacion del servicio.'],
                ['Talento Humano y SG-SST', 'apoyo', 'Seleccion, contratacion, nomina, bienestar, seguridad y salud en el trabajo.'],
            ],
            'instrucciones' => "# PLANTILLA PROCESOS - GTC 45\n# Columnas: nombre_proceso (obligatorio), tipo (estrategico|misional|apoyo|evaluacion), descripcion (opcional)\n# Segun la GTC 45, identifique TODOS los procesos de la organizacion.\n# Piense en: que hace la empresa (misional), como se planifica (estrategico), que la soporta (apoyo), como se evalua (evaluacion).\n",
        ],
        'cargo' => [
            'headers' => ['nombre_cargo', 'nombre_proceso', 'num_ocupantes', 'descripcion'],
            'ejemplo' => [
                ['Gerente General', 'Direccion Estrategica', '1', 'Maxima autoridad administrativa.'],
                ['Auxiliar de Bodega', 'Almacen y Logistica', '4', 'Recepcion, almacenamiento y despacho.'],
                ['Asesor Comercial', 'Comercial y Ventas', '6', 'Atencion y captacion de clientes.'],
            ],
            'instrucciones' => "# PLANTILLA CARGOS - GTC 45\n# Columnas: nombre_cargo (obligatorio), nombre_proceso (debe coincidir con un proceso ya registrado), num_ocupantes, descripcion\n# Liste TODOS los cargos de la organizacion. Cada cargo se asocia a un proceso.\n# La GTC 45 requiere identificar los cargos para determinar quienes estan expuestos a cada peligro.\n",
        ],
        'tarea' => [
            'headers' => ['nombre_tarea', 'nombre_proceso', 'rutinaria', 'descripcion'],
            'ejemplo' => [
                ['Digitar informes contables', 'Administrativo y Financiero', '1', 'Trabajo frente a pantalla por periodos prolongados.'],
                ['Descargue de mercancias', 'Almacen y Logistica', '1', 'Manipulacion manual de cargas de 5 a 25 kg.'],
                ['Inventario fisico semestral', 'Almacen y Logistica', '0', 'Conteo en estanterias altas con escalera.'],
            ],
            'instrucciones' => "# PLANTILLA TAREAS - GTC 45\n# Columnas: nombre_tarea (obligatorio), nombre_proceso (debe coincidir con proceso ya registrado), rutinaria (1=Si, 0=No), descripcion\n# Segun la GTC 45, una tarea RUTINARIA se realiza de forma habitual/diaria.\n# Una tarea NO RUTINARIA es esporadica (inventarios, mantenimientos, emergencias).\n# Describa brevemente QUE hace el trabajador y CON QUE (herramientas, equipos, sustancias).\n",
        ],
        'zona' => [
            'headers' => ['nombre_zona', 'descripcion'],
            'ejemplo' => [
                ['Oficina Administrativa', 'Puestos de trabajo con pantalla, area climatizada.'],
                ['Bodega Principal', 'Estanterias de 4 niveles, pasillos de picking, montacargas.'],
                ['Zona de Cargue y Descargue', 'Muelle, acceso vehicular, manipulacion de cargas.'],
            ],
            'instrucciones' => "# PLANTILLA ZONAS - GTC 45\n# Columnas: nombre_zona (obligatorio), descripcion (opcional)\n# Segun la GTC 45, identifique TODAS las zonas o lugares donde se realizan actividades.\n# Incluya: oficinas, bodegas, areas de produccion, talleres, vehiculos, zonas comunes, etc.\n",
        ],
    ];

    public function plantillaCsv(string $tipo)
    {
        if (!isset($this->plantillas[$tipo])) {
            return $this->response->setStatusCode(404)->setBody('Tipo no valido');
        }
        $pl = $this->plantillas[$tipo];
        $csv = $pl['instrucciones'];
        $csv .= implode(',', $pl['headers']) . "\n";
        foreach ($pl['ejemplo'] as $fila) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $fila)) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', "attachment; filename=\"plantilla_{$tipo}.csv\"")
            ->setBody("\xEF\xBB\xBF" . $csv); // BOM UTF-8 para Excel
    }

    public function cargarCsv()
    {
        $tipo = (string)$this->request->getPost('tipo');
        $idCliente = (int)$this->request->getPost('id_cliente');
        $file = $this->request->getFile('archivo_csv');

        if (!$idCliente || !$tipo || !isset($this->plantillas[$tipo])) {
            return $this->response->setJSON(['ok' => false, 'error' => 'tipo e id_cliente requeridos']);
        }
        if (!$file || !$file->isValid() || $file->getExtension() !== 'csv') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Archivo CSV invalido']);
        }

        $contenido = file_get_contents($file->getTempName());
        $contenido = mb_convert_encoding($contenido, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);
        $lineas = array_filter(explode("\n", str_replace("\r", "", $contenido)), fn($l) => trim($l) !== '' && $l[0] !== '#');
        $lineas = array_values($lineas);

        if (count($lineas) < 2) {
            return $this->response->setJSON(['ok' => false, 'error' => 'El CSV debe tener al menos el header y una fila de datos']);
        }

        $headers = str_getcsv(array_shift($lineas));
        $headers = array_map(fn($h) => trim(strtolower($h)), $headers);
        $esperados = $this->plantillas[$tipo]['headers'];

        // Validar que los headers requeridos existan
        $faltantes = array_diff($esperados, $headers);
        if (!empty($faltantes)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Columnas faltantes: ' . implode(', ', $faltantes)]);
        }

        // Mapa de procesos por nombre (para resolver FK)
        $mapProcNombre = [];
        foreach ($this->procesoModel->porCliente($idCliente, true) as $p) {
            $mapProcNombre[mb_strtolower(trim($p['nombre_proceso']))] = (int)$p['id'];
        }

        $insertados = 0;
        $errores = [];

        foreach ($lineas as $i => $linea) {
            $cols = str_getcsv($linea);
            $row = [];
            foreach ($headers as $j => $h) {
                $row[$h] = trim($cols[$j] ?? '');
            }

            try {
                switch ($tipo) {
                    case 'proceso':
                        if ($row['nombre_proceso'] === '') { $errores[] = "Fila " . ($i + 2) . ": nombre_proceso vacio"; continue 2; }
                        $tipoP = in_array($row['tipo'] ?? '', ['estrategico','misional','apoyo','evaluacion']) ? $row['tipo'] : null;
                        $this->procesoModel->insert([
                            'id_cliente' => $idCliente, 'nombre_proceso' => $row['nombre_proceso'],
                            'tipo' => $tipoP, 'descripcion' => $row['descripcion'] ?? null, 'activo' => 1,
                        ]);
                        $insertados++;
                        break;

                    case 'cargo':
                        if ($row['nombre_cargo'] === '') { $errores[] = "Fila " . ($i + 2) . ": nombre_cargo vacio"; continue 2; }
                        $idProc = $mapProcNombre[mb_strtolower($row['nombre_proceso'] ?? '')] ?? null;
                        $this->cargoModel->insert([
                            'id_cliente' => $idCliente, 'id_proceso' => $idProc,
                            'nombre_cargo' => $row['nombre_cargo'],
                            'num_ocupantes' => (int)($row['num_ocupantes'] ?? 0),
                            'descripcion' => $row['descripcion'] ?? null, 'activo' => 1,
                        ]);
                        $insertados++;
                        break;

                    case 'tarea':
                        if ($row['nombre_tarea'] === '') { $errores[] = "Fila " . ($i + 2) . ": nombre_tarea vacio"; continue 2; }
                        $idProc = $mapProcNombre[mb_strtolower($row['nombre_proceso'] ?? '')] ?? null;
                        $this->tareaModel->insert([
                            'id_cliente' => $idCliente, 'id_proceso' => $idProc,
                            'nombre_tarea' => $row['nombre_tarea'],
                            'rutinaria' => (int)($row['rutinaria'] ?? 1),
                            'descripcion' => $row['descripcion'] ?? null, 'activo' => 1,
                        ]);
                        $insertados++;
                        break;

                    case 'zona':
                        if ($row['nombre_zona'] === '') { $errores[] = "Fila " . ($i + 2) . ": nombre_zona vacio"; continue 2; }
                        $this->zonaModel->insert([
                            'id_cliente' => $idCliente, 'id_sede' => null,
                            'nombre_zona' => $row['nombre_zona'],
                            'descripcion' => $row['descripcion'] ?? null, 'activo' => 1,
                        ]);
                        $insertados++;
                        break;
                }
            } catch (\Throwable $e) {
                $errores[] = "Fila " . ($i + 2) . ": " . $e->getMessage();
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'insertados' => $insertados,
            'errores' => $errores,
            'total_leidas' => count($lineas),
        ]);
    }
}
