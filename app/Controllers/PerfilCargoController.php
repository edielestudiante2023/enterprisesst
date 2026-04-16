<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\PerfilCargoIAService;
use App\Models\ClientModel;
use App\Models\CargoClienteModel;
use App\Models\PerfilCargoModel;
use App\Models\PerfilCargoCompetenciaModel;
use App\Models\PerfilCargoIndicadorModel;
use App\Models\PerfilCargoFuncionSSTClienteModel;
use App\Models\PerfilCargoFuncionTHClienteModel;
use App\Models\PerfilCargoAcuseModel;
use App\Models\TrabajadorModel;
use Dompdf\Dompdf;

/**
 * Controlador del modulo Perfiles de Cargo.
 *
 * Ver: docs/MODULO_PERFILES_CARGO/ARQUITECTURA.md §8
 */
class PerfilCargoController extends Controller
{
    private PerfilCargoIAService $iaService;

    public function __construct()
    {
        $this->iaService = new PerfilCargoIAService();
    }

    private function requireSession(): bool
    {
        return (bool)session()->get('isLoggedIn');
    }

    private function resolverCliente(int $idCliente): ?array
    {
        $m = new ClientModel();
        $c = $m->find($idCliente);
        return $c ?: null;
    }

    /* ============================================================
     * LISTADO
     * ============================================================ */

    /**
     * GET /perfiles-cargo/{id_cliente}
     */
    public function index(int $idCliente)
    {
        if (!$this->requireSession()) return redirect()->to('/login');

        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');

        $cargoModel  = new CargoClienteModel();
        $perfilModel = new PerfilCargoModel();
        $trabModel   = new TrabajadorModel();

        $cargos  = $cargoModel->porCliente($idCliente);
        $perfiles = $perfilModel->porCliente($idCliente);

        // Indexar perfiles por cargo
        $perfilPorCargo = [];
        foreach ($perfiles as $p) $perfilPorCargo[(int)$p['id_cargo_cliente']] = $p;

        // Conteo trabajadores por cargo
        $conteoTrabajadores = [];
        foreach ($cargos as $c) {
            $conteoTrabajadores[(int)$c['id']] = $trabModel->contarPorCargo((int)$c['id']);
        }

        return view('perfiles_cargo/index', [
            'cliente'            => $cliente,
            'cargos'             => $cargos,
            'perfilPorCargo'     => $perfilPorCargo,
            'conteoTrabajadores' => $conteoTrabajadores,
        ]);
    }

    /* ============================================================
     * CREAR (o reutilizar) PERFIL
     * ============================================================ */

    /**
     * POST /perfiles-cargo/{id_cliente}/crear
     * body: id_cargo_cliente
     */
    public function crear(int $idCliente)
    {
        if (!$this->requireSession()) return redirect()->to('/login');

        $idCargo = (int)$this->request->getPost('id_cargo_cliente');
        if ($idCargo <= 0) {
            return redirect()->back()->with('error', 'Debe seleccionar un cargo.');
        }

        $perfilModel = new PerfilCargoModel();
        $existente = $perfilModel->porCargo($idCargo);

        if ($existente) {
            return redirect()->to("/perfiles-cargo/{$idCliente}/editor/{$existente['id_perfil_cargo']}")
                ->with('info', 'Ya existia un perfil para este cargo, se abrio el existente.');
        }

        $idPerfil = $perfilModel->crearVacio($idCliente, $idCargo);

        // Precarga de competencias desde matriz (si existen en tbl_cliente_competencia_cargo)
        $compModel = new PerfilCargoCompetenciaModel();
        $n = $compModel->precargarDesdeMatriz($idPerfil, $idCargo);

        $msg = 'Perfil creado.';
        if ($n > 0) $msg .= " Se precargaron {$n} competencias desde el diccionario del cliente.";

        return redirect()->to("/perfiles-cargo/{$idCliente}/editor/{$idPerfil}")->with('success', $msg);
    }

    /* ============================================================
     * EDITOR
     * ============================================================ */

    /**
     * GET /perfiles-cargo/{id_cliente}/editor/{id_perfil}
     */
    public function editor(int $idCliente, int $idPerfil)
    {
        if (!$this->requireSession()) return redirect()->to('/login');

        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');

        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->buscarPorId($idPerfil);
        if (!$perfil || (int)$perfil['id_cliente'] !== $idCliente) {
            return redirect()->to("/perfiles-cargo/{$idCliente}")->with('error', 'Perfil no encontrado');
        }

        $cargoModel = new CargoClienteModel();
        $cargo = $cargoModel->find((int)$perfil['id_cargo_cliente']);

        $indicadorModel = new PerfilCargoIndicadorModel();
        $sstModel       = new PerfilCargoFuncionSSTClienteModel();
        $thModel        = new PerfilCargoFuncionTHClienteModel();

        return view('perfiles_cargo/editor', [
            'cliente'      => $cliente,
            'perfil'       => $perfil,
            'cargo'        => $cargo,
            'indicadores'  => $indicadorModel->porPerfil($idPerfil),
            'funcionesSST' => $sstModel->porCliente($idCliente),
            'funcionesTH'  => $thModel->porCliente($idCliente),
        ]);
    }

    /**
     * POST /perfiles-cargo/{id_perfil}/guardar
     * body JSON: { campos_perfil + indicadores[] + funciones_especificas[] }
     */
    public function guardar(int $idPerfil)
    {
        if (!$this->requireSession()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);
        }

        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->find($idPerfil);
        if (!$perfil) {
            return $this->response->setJSON(['ok' => false, 'error' => 'perfil no encontrado']);
        }

        $input = $this->obtenerInput();

        // Campos escalares + JSON encode para campos de tipo JSON
        $camposEscalares = ['objetivo_cargo','reporta_a','colaboradores_a_cargo','edad_min','estado_civil','genero','validacion_educacion_experiencia','aprobador_nombre','aprobador_cargo','aprobador_cedula','fecha_aprobacion'];
        $camposJson = ['condiciones_laborales','factores_riesgo','formacion_educacion','conocimiento_complementario','experiencia_laboral','funciones_especificas'];

        $data = [];
        foreach ($camposEscalares as $f) {
            if (array_key_exists($f, $input)) $data[$f] = $input[$f] === '' ? null : $input[$f];
        }
        foreach ($camposJson as $f) {
            if (array_key_exists($f, $input)) {
                $data[$f] = $input[$f] === null || $input[$f] === '' ? null : json_encode($input[$f], JSON_UNESCAPED_UNICODE);
            }
        }

        if (isset($input['estado']) && in_array($input['estado'], ['borrador','generado','aprobado','firmado','obsoleto'], true)) {
            $data['estado'] = $input['estado'];
        }

        if (!empty($data)) {
            $perfilModel->update($idPerfil, $data);
        }

        // Indicadores (si vinieron)
        if (isset($input['indicadores']) && is_array($input['indicadores'])) {
            $indicadorModel = new PerfilCargoIndicadorModel();
            $generadoIa = !empty($input['indicadores_generados_ia']);
            $indicadorModel->reemplazarTodos($idPerfil, $input['indicadores'], $generadoIa);
        }

        return $this->response->setJSON(['ok' => true, 'id_perfil_cargo' => $idPerfil]);
    }

    /* ============================================================
     * COMPETENCIAS (Select2 + guardado)
     * ============================================================ */

    /**
     * GET /perfiles-cargo/{id_cliente}/competencias/buscar?q=texto
     * Devuelve JSON compatible con Select2: {results:[{id,text,familia}]}
     */
    public function competenciasBuscar(int $idCliente)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['results' => []]);

        $q = trim((string)$this->request->getGet('q'));
        $db = \Config\Database::connect();
        $builder = $db->table('tbl_competencia_cliente')
            ->select('id_competencia, nombre, codigo, familia')
            ->where('id_cliente', $idCliente)
            ->where('activo', 1);
        if ($q !== '') {
            $builder->groupStart()
                ->like('nombre', $q)
                ->orLike('codigo', $q)
                ->groupEnd();
        }
        $rows = $builder->orderBy('nombre', 'ASC')->limit(50)->get()->getResultArray();

        $results = [];
        foreach ($rows as $r) {
            $label = $r['nombre'];
            if (!empty($r['codigo'])) $label = '[' . $r['codigo'] . '] ' . $label;
            $results[] = [
                'id'      => (int)$r['id_competencia'],
                'text'    => $label,
                'familia' => $r['familia'] ?? '',
            ];
        }
        return $this->response->setJSON(['results' => $results]);
    }

    /**
     * GET /perfiles-cargo/{id_perfil}/competencias
     * Devuelve las competencias asignadas al perfil con JOIN al catalogo.
     */
    public function competenciasListar(int $idPerfil)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);

        $compModel = new PerfilCargoCompetenciaModel();
        return $this->response->setJSON([
            'ok'    => true,
            'items' => $compModel->porPerfil($idPerfil),
        ]);
    }

    /**
     * POST /perfiles-cargo/{id_perfil}/competencias/guardar
     * body JSON: { items: [{id_competencia, nivel_requerido, observacion, orden}] }
     */
    public function competenciasGuardar(int $idPerfil)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);

        $perfilModel = new PerfilCargoModel();
        if (!$perfilModel->find($idPerfil)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'perfil no encontrado']);
        }

        $input = $this->obtenerInput();
        $items = is_array($input['items'] ?? null) ? $input['items'] : [];

        // Sanear
        $limpios = [];
        foreach ($items as $i => $it) {
            $idComp = (int)($it['id_competencia'] ?? 0);
            $nivel  = (int)($it['nivel_requerido'] ?? 0);
            if ($idComp <= 0) continue;
            if ($nivel < 1 || $nivel > 5) $nivel = 3;
            $limpios[] = [
                'id_competencia'  => $idComp,
                'nivel_requerido' => $nivel,
                'observacion'     => $it['observacion'] ?? null,
                'orden'           => (int)($it['orden'] ?? ($i + 1)),
            ];
        }

        $compModel = new PerfilCargoCompetenciaModel();
        $compModel->reemplazarTodas($idPerfil, $limpios);

        return $this->response->setJSON(['ok' => true, 'total' => count($limpios)]);
    }

    /* ============================================================
     * IA (3 endpoints)
     * ============================================================ */

    /** POST /perfiles-cargo/ia/objetivo */
    public function iaObjetivo()
    {
        $input = $this->obtenerInput();
        $resp = $this->iaService->generarObjetivoCargo(
            (string)($input['nombre_cargo'] ?? ''),
            is_array($input['funciones'] ?? null) ? $input['funciones'] : [],
            (string)($input['area'] ?? '')
        );
        return $this->response->setJSON($resp);
    }

    /** POST /perfiles-cargo/ia/indicadores */
    public function iaIndicadores()
    {
        $input = $this->obtenerInput();
        $resp = $this->iaService->generarIndicadores(
            (string)($input['nombre_cargo'] ?? ''),
            is_array($input['funciones'] ?? null) ? $input['funciones'] : [],
            (string)($input['objetivo'] ?? '')
        );
        return $this->response->setJSON($resp);
    }

    /** POST /perfiles-cargo/ia/funciones */
    public function iaFunciones()
    {
        $input = $this->obtenerInput();
        $resp = $this->iaService->sugerirFunciones(
            (string)($input['nombre_cargo'] ?? ''),
            (string)($input['area'] ?? ''),
            isset($input['objetivo']) ? (string)$input['objetivo'] : null
        );
        return $this->response->setJSON($resp);
    }

    /* ============================================================
     * FUNCIONES TRANSVERSALES SST / TH por cliente
     * ============================================================ */

    /** GET /perfiles-cargo/{id_cliente}/funciones-transversales */
    public function funcionesTransversalesIndex(int $idCliente)
    {
        if (!$this->requireSession()) return redirect()->to('/login');
        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');

        $sstModel = new PerfilCargoFuncionSSTClienteModel();
        $thModel  = new PerfilCargoFuncionTHClienteModel();

        return view('perfiles_cargo/funciones_transversales', [
            'cliente'      => $cliente,
            'funcionesSST' => $sstModel->porCliente($idCliente, false), // incluir inactivas
            'funcionesTH'  => $thModel->porCliente($idCliente, false),
        ]);
    }

    /** POST /perfiles-cargo/{id_cliente}/funciones-transversales/sst */
    public function funcionesTransversalesGuardarSST(int $idCliente)
    {
        return $this->guardarFuncionesTransversales($idCliente, 'sst');
    }

    /** POST /perfiles-cargo/{id_cliente}/funciones-transversales/th */
    public function funcionesTransversalesGuardarTH(int $idCliente)
    {
        return $this->guardarFuncionesTransversales($idCliente, 'th');
    }

    private function guardarFuncionesTransversales(int $idCliente, string $tipo)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);
        if (!in_array($tipo, ['sst', 'th'], true)) return $this->response->setJSON(['ok' => false, 'error' => 'tipo invalido']);

        $input = $this->obtenerInput();
        $items = is_array($input['items'] ?? null) ? $input['items'] : [];

        $model = $tipo === 'sst' ? new PerfilCargoFuncionSSTClienteModel() : new PerfilCargoFuncionTHClienteModel();
        try {
            $model->reemplazarTodas($idCliente, $items);
            $total = count($model->porCliente($idCliente, true));
            return $this->response->setJSON(['ok' => true, 'total' => $total]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ============================================================
     * TRABAJADORES — CRUD + importador CSV
     * ============================================================ */

    /** GET /perfiles-cargo/{id_cliente}/trabajadores */
    public function trabajadoresIndex(int $idCliente)
    {
        if (!$this->requireSession()) return redirect()->to('/login');
        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');

        $trabModel  = new TrabajadorModel();
        $cargoModel = new CargoClienteModel();

        return view('perfiles_cargo/trabajadores', [
            'cliente'     => $cliente,
            'trabajadores'=> $trabModel->porCliente($idCliente),
            'cargos'      => $cargoModel->porCliente($idCliente),
        ]);
    }

    /** POST /perfiles-cargo/{id_cliente}/trabajadores/guardar */
    public function trabajadorGuardar(int $idCliente)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);
        $input = $this->obtenerInput();
        $id    = (int)($input['id_trabajador'] ?? 0);

        $data = [
            'id_cliente'       => $idCliente,
            'id_cargo_cliente' => !empty($input['id_cargo_cliente']) ? (int)$input['id_cargo_cliente'] : null,
            'nombres'          => trim((string)($input['nombres'] ?? '')),
            'apellidos'        => trim((string)($input['apellidos'] ?? '')),
            'tipo_documento'   => $input['tipo_documento'] ?? 'CC',
            'cedula'           => trim((string)($input['cedula'] ?? '')),
            'email'            => trim((string)($input['email'] ?? '')) ?: null,
            'telefono'         => trim((string)($input['telefono'] ?? '')) ?: null,
            'fecha_ingreso'    => $input['fecha_ingreso'] ?? null,
            'activo'           => 1,
        ];
        if ($data['nombres'] === '' || $data['apellidos'] === '' || $data['cedula'] === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Nombres, apellidos y cedula son obligatorios']);
        }

        $m = new TrabajadorModel();
        try {
            if ($id > 0) {
                $m->update($id, $data);
                return $this->response->setJSON(['ok' => true, 'id_trabajador' => $id]);
            } else {
                $m->insert($data);
                return $this->response->setJSON(['ok' => true, 'id_trabajador' => (int)$m->getInsertID()]);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /** POST /perfiles-cargo/{id_cliente}/trabajadores/eliminar */
    public function trabajadorEliminar(int $idCliente)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);
        $id = (int)($this->obtenerInput()['id_trabajador'] ?? 0);
        if ($id <= 0) return $this->response->setJSON(['ok' => false, 'error' => 'id invalido']);
        $m = new TrabajadorModel();
        $trab = $m->find($id);
        if (!$trab || (int)$trab['id_cliente'] !== $idCliente) {
            return $this->response->setJSON(['ok' => false, 'error' => 'trabajador no existe']);
        }
        try {
            $m->delete($id);
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            // RESTRICT fallara si tiene acuses; desactivar en lugar de borrar
            $m->update($id, ['activo' => 0]);
            return $this->response->setJSON(['ok' => true, 'desactivado' => true]);
        }
    }

    /**
     * GET /perfiles-cargo/{id_cliente}/trabajadores/plantilla-csv
     * Descarga una plantilla CSV con cabecera, ejemplos y lista de cargos del cliente.
     */
    public function trabajadoresPlantillaCsv(int $idCliente)
    {
        if (!$this->requireSession()) return redirect()->to('/login');

        $cliente = $this->resolverCliente($idCliente);
        if (!$cliente) return redirect()->to('/consultantDashboard')->with('error', 'Cliente no encontrado');

        $cargoModel = new CargoClienteModel();
        $cargos = $cargoModel->porCliente($idCliente);

        // Primer cargo como ejemplo (si existe)
        $idCargoEjemplo = !empty($cargos) ? (int)$cargos[0]['id'] : '';

        // BOM UTF-8 para Excel
        $csv = "\xEF\xBB\xBF";
        // Cabecera
        $csv .= "tipo_documento,cedula,nombres,apellidos,email,telefono,fecha_ingreso,id_cargo_cliente\n";
        // Ejemplos
        $csv .= "CC,1234567890,Pedro Andres,Garcia Sanchez,pgarcia@ejemplo.com,3001111111,2024-01-15,{$idCargoEjemplo}\n";
        $csv .= "CC,9876543210,Ana Maria,Torres Ruiz,amtorres@ejemplo.com,3002222222,2023-06-01,\n";
        // Bloque de referencia: lista de cargos del cliente
        $csv .= "\n";
        $csv .= "# ==========================================================\n";
        $csv .= "# CARGOS DISPONIBLES PARA EL CLIENTE: " . ($cliente['nombre_cliente'] ?? '') . "\n";
        $csv .= "# Use el id_cargo_cliente (primera columna) en la columna correspondiente del bloque superior.\n";
        $csv .= "# Dejar vacio si el trabajador aun no tiene cargo asignado.\n";
        $csv .= "# ==========================================================\n";
        $csv .= "# id_cargo_cliente,nombre_cargo\n";
        foreach ($cargos as $c) {
            // Escapar comillas y comas en el nombre del cargo
            $nombre = str_replace('"', '""', $c['nombre_cargo']);
            $csv .= "# {$c['id']},\"{$nombre}\"\n";
        }
        $csv .= "#\n";
        $csv .= "# NOTAS:\n";
        $csv .= "# - Primera fila = cabecera (no cambiar nombres de columnas)\n";
        $csv .= "# - Separador: coma o punto-coma (deteccion automatica)\n";
        $csv .= "# - Campos obligatorios: cedula, nombres, apellidos\n";
        $csv .= "# - tipo_documento: CC, CE, PA, TI o PEP (CC por defecto)\n";
        $csv .= "# - fecha_ingreso: formato YYYY-MM-DD (opcional)\n";
        $csv .= "# - Si la cedula ya existe, el registro se ACTUALIZA (no duplica)\n";
        $csv .= "# - Elimine estas lineas de comentario antes de subir el archivo\n";

        $slug = preg_replace('/[^a-z0-9]+/i', '_', strtolower($cliente['nombre_cliente'] ?? 'cliente'));
        $filename = "plantilla_trabajadores_{$slug}.csv";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($csv);
    }

    /**
     * POST /perfiles-cargo/{id_cliente}/trabajadores/importar
     * multipart file=archivo (CSV separador , o ;)
     * columnas esperadas: tipo_documento,cedula,nombres,apellidos,email,telefono,fecha_ingreso,id_cargo_cliente
     */
    public function trabajadoresImportar(int $idCliente)
    {
        if (!$this->requireSession()) return redirect()->to('/login');
        $file = $this->request->getFile('archivo');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Archivo invalido o no enviado.');
        }

        $contenido = file_get_contents($file->getTempName());
        if ($contenido === false) {
            return redirect()->back()->with('error', 'No se pudo leer el archivo.');
        }

        // Detectar separador
        $primeraLinea = strtok($contenido, "\n");
        $sep = (substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',')) ? ';' : ',';

        $lineas = preg_split('/\r\n|\n|\r/', trim($contenido));
        $header = str_getcsv(array_shift($lineas), $sep);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $trabModel = new TrabajadorModel();
        $creados = 0; $actualizados = 0; $errores = [];

        foreach ($lineas as $nLinea => $linea) {
            if (trim($linea) === '') continue;
            $cols = str_getcsv($linea, $sep);
            if (count($cols) < count($header)) $cols = array_pad($cols, count($header), '');
            $row = array_combine($header, array_slice($cols, 0, count($header)));

            $cedula = trim((string)($row['cedula'] ?? ''));
            $nombres = trim((string)($row['nombres'] ?? ''));
            $apellidos = trim((string)($row['apellidos'] ?? ''));
            if ($cedula === '' || $nombres === '' || $apellidos === '') {
                $errores[] = 'Fila ' . ($nLinea + 2) . ': faltan campos obligatorios';
                continue;
            }

            $data = [
                'id_cliente'       => $idCliente,
                'nombres'          => $nombres,
                'apellidos'        => $apellidos,
                'tipo_documento'   => strtoupper(trim((string)($row['tipo_documento'] ?? 'CC'))) ?: 'CC',
                'cedula'           => $cedula,
                'email'            => trim((string)($row['email'] ?? '')) ?: null,
                'telefono'         => trim((string)($row['telefono'] ?? '')) ?: null,
                'fecha_ingreso'    => !empty($row['fecha_ingreso']) ? $row['fecha_ingreso'] : null,
                'id_cargo_cliente' => !empty($row['id_cargo_cliente']) ? (int)$row['id_cargo_cliente'] : null,
                'activo'           => 1,
            ];

            try {
                $existente = $trabModel->buscarPorCedula($idCliente, $cedula);
                if ($existente) {
                    $trabModel->update($existente['id_trabajador'], $data);
                    $actualizados++;
                } else {
                    $trabModel->insert($data);
                    $creados++;
                }
            } catch (\Throwable $e) {
                $errores[] = 'Fila ' . ($nLinea + 2) . ': ' . $e->getMessage();
            }
        }

        $msg = "Importacion completa. Nuevos: {$creados}, actualizados: {$actualizados}";
        if (!empty($errores)) $msg .= ', errores: ' . count($errores);
        return redirect()->to("/perfiles-cargo/{$idCliente}/trabajadores")
            ->with('success', $msg)
            ->with('errores_import', $errores);
    }

    /* ============================================================
     * FIRMAS — Aprobador inline + Acuses por trabajador
     * ============================================================ */

    /**
     * POST /perfiles-cargo/{id_perfil}/aprobador/firmar
     * body JSON: { nombre, cargo, cedula, firma_base64 }
     *
     * Guarda la firma como base64 directo en la columna firma_aprobador_base64.
     */
    public function aprobadorFirmar(int $idPerfil)
    {
        if (!$this->requireSession()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);
        }
        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->find($idPerfil);
        if (!$perfil) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Perfil no existe']);
        }

        $input  = $this->obtenerInput();
        $nombre = trim((string)($input['nombre'] ?? ''));
        $cargo  = trim((string)($input['cargo'] ?? ''));
        $cedula = trim((string)($input['cedula'] ?? ''));
        $firma  = (string)($input['firma_base64'] ?? '');

        if ($nombre === '')  return $this->response->setJSON(['ok' => false, 'error' => 'El nombre del aprobador es obligatorio']);
        if ($cargo === '')   return $this->response->setJSON(['ok' => false, 'error' => 'El cargo del aprobador es obligatorio']);
        if ($firma === '')   return $this->response->setJSON(['ok' => false, 'error' => 'La firma es obligatoria']);

        // Limpiar prefijo data:image/...;base64, si viene
        if (strpos($firma, 'data:image') === 0) {
            $firma = preg_replace('/^data:image\/[a-zA-Z0-9+.\-]+;base64,/', '', $firma);
        }

        // Validar que sea base64 decodeable
        $bin = base64_decode($firma, true);
        if ($bin === false || strlen($bin) < 100) {
            return $this->response->setJSON(['ok' => false, 'error' => 'La firma no es una imagen base64 valida']);
        }
        // Limite razonable: 5 MB base64 (= 3.75 MB imagen)
        if (strlen($firma) > 5 * 1024 * 1024) {
            return $this->response->setJSON(['ok' => false, 'error' => 'La firma excede el tamano maximo permitido (5 MB)']);
        }

        try {
            $perfilModel->update($idPerfil, [
                'aprobador_nombre'       => $nombre,
                'aprobador_cargo'        => $cargo,
                'aprobador_cedula'       => $cedula ?: null,
                'firma_aprobador_base64' => $firma,
                'fecha_aprobacion'       => date('Y-m-d'),
                'estado'                 => 'aprobado',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'aprobadorFirmar update error: ' . $e->getMessage());
            return $this->response->setJSON(['ok' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
        }

        return $this->response->setJSON(['ok' => true, 'mensaje' => 'Perfil aprobado y firmado correctamente']);
    }

    /** GET /perfiles-cargo/{id_perfil}/acuses */
    public function acusesIndex(int $idPerfil)
    {
        if (!$this->requireSession()) return redirect()->to('/login');
        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->buscarPorId($idPerfil);
        if (!$perfil) return redirect()->to('/consultantDashboard')->with('error', 'Perfil no encontrado');

        $cliente   = $this->resolverCliente((int)$perfil['id_cliente']);
        $cargoModel= new CargoClienteModel();
        $cargo     = $cargoModel->find((int)$perfil['id_cargo_cliente']);
        $trabModel = new TrabajadorModel();
        $acuseModel= new PerfilCargoAcuseModel();

        // Mapa id_cargo_cliente => nombre_cargo (todos los cargos del cliente)
        $cargosMap = [];
        foreach ($cargoModel->porCliente((int)$perfil['id_cliente'], false) as $c) {
            $cargosMap[(int)$c['id']] = $c['nombre_cargo'];
        }

        // Ordenar trabajadores: primero los del cargo del perfil, luego por apellidos
        $trabajadoresCliente = $trabModel->porCliente((int)$perfil['id_cliente']);
        $idCargoPerfil = (int)$perfil['id_cargo_cliente'];
        usort($trabajadoresCliente, function($a, $b) use ($idCargoPerfil) {
            $aMatch = ((int)$a['id_cargo_cliente'] === $idCargoPerfil) ? 0 : 1;
            $bMatch = ((int)$b['id_cargo_cliente'] === $idCargoPerfil) ? 0 : 1;
            if ($aMatch !== $bMatch) return $aMatch - $bMatch;
            return strcmp($a['apellidos'] . $a['nombres'], $b['apellidos'] . $b['nombres']);
        });

        return view('perfiles_cargo/acuses', [
            'cliente'      => $cliente,
            'perfil'       => $perfil,
            'cargo'        => $cargo,
            'trabajadoresCargo' => $trabModel->porCargo((int)$perfil['id_cargo_cliente']),
            'trabajadoresCliente' => $trabajadoresCliente,
            'cargosMap'    => $cargosMap,
            'acuses'       => $acuseModel->porPerfil($idPerfil),
            'stats'        => $acuseModel->contarPorPerfil($idPerfil),
        ]);
    }

    /** POST /perfiles-cargo/{id_perfil}/acuses/generar */
    public function acusesGenerar(int $idPerfil)
    {
        if (!$this->requireSession()) return $this->response->setJSON(['ok' => false, 'error' => 'no_auth']);
        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->find($idPerfil);
        if (!$perfil) return $this->response->setJSON(['ok' => false, 'error' => 'perfil no existe']);

        $input = $this->obtenerInput();
        $ids = is_array($input['ids_trabajadores'] ?? null) ? array_map('intval', $input['ids_trabajadores']) : [];
        if (empty($ids)) return $this->response->setJSON(['ok' => false, 'error' => 'sin trabajadores']);

        $trabModel = new TrabajadorModel();
        $trabajadores = [];
        foreach ($ids as $id) {
            $t = $trabModel->find($id);
            if ($t && (int)$t['id_cliente'] === (int)$perfil['id_cliente']) $trabajadores[] = $t;
        }

        $cargoModel = new CargoClienteModel();
        $cargo = $cargoModel->find((int)$perfil['id_cargo_cliente']);
        $cargoDesc = $cargo['nombre_cargo'] ?? '';

        $acuseModel = new PerfilCargoAcuseModel();
        $res = $acuseModel->generarLote($idPerfil, null, $trabajadores, $cargoDesc);

        return $this->response->setJSON(['ok' => true] + $res);
    }

    /** GET /perfil-acuse/{token} — vista publica sin sesion */
    public function acusePublico(string $token)
    {
        $acuseModel = new PerfilCargoAcuseModel();
        $acuse = $acuseModel->porToken($token);
        if (!$acuse) {
            return view('perfiles_cargo/acuse_error', ['mensaje' => 'Link invalido o expirado.']);
        }
        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->buscarPorId((int)$acuse['id_perfil_cargo']);
        if (!$perfil) return view('perfiles_cargo/acuse_error', ['mensaje' => 'Perfil no encontrado.']);

        $cliente = $this->resolverCliente((int)$perfil['id_cliente']);
        if (!$cliente) return view('perfiles_cargo/acuse_error', ['mensaje' => 'Cliente no encontrado.']);

        $cargoModel = new CargoClienteModel();
        $cargo = $cargoModel->find((int)$perfil['id_cargo_cliente']);

        $indicadorModel = new PerfilCargoIndicadorModel();
        $compModel      = new PerfilCargoCompetenciaModel();
        $sstModel       = new PerfilCargoFuncionSSTClienteModel();
        $thModel        = new PerfilCargoFuncionTHClienteModel();

        return view('perfiles_cargo/acuse_publico', [
            'acuse'        => $acuse,
            'cliente'      => $cliente,
            'perfil'       => $perfil,
            'cargo'        => $cargo,
            'indicadores'  => $indicadorModel->porPerfil((int)$acuse['id_perfil_cargo']),
            'competencias' => $compModel->porPerfil((int)$acuse['id_perfil_cargo']),
            'funcionesSST' => $sstModel->porCliente((int)$perfil['id_cliente']),
            'funcionesTH'  => $thModel->porCliente((int)$perfil['id_cliente']),
        ]);
    }

    /** POST /perfil-acuse/{token}/firmar */
    public function acuseFirmar(string $token)
    {
        $acuseModel = new PerfilCargoAcuseModel();
        $acuse = $acuseModel->porToken($token);
        if (!$acuse) return $this->response->setJSON(['ok' => false, 'error' => 'token invalido']);
        if ($acuse['estado'] === 'firmado') return $this->response->setJSON(['ok' => false, 'error' => 'ya firmado']);

        $firma = (string)$this->request->getPost('firma_base64');
        if ($firma === '') {
            $raw = $this->request->getBody();
            $j = json_decode($raw, true);
            $firma = (string)($j['firma_base64'] ?? '');
        }
        if ($firma === '') return $this->response->setJSON(['ok' => false, 'error' => 'firma vacia']);

        if (strpos($firma, 'data:image') === 0) {
            $firma = preg_replace('/^data:image\/\w+;base64,/', '', $firma);
        }

        $acuseModel->marcarFirmado(
            (int)$acuse['id_acuse'],
            $firma,
            (string)$this->request->getIPAddress(),
            (string)($this->request->getUserAgent() ?? '')
        );

        return $this->response->setJSON(['ok' => true]);
    }

    /* ============================================================
     * PDFs
     * ============================================================ */

    /** Carga el logo del cliente como base64 para embeber en el PDF (Dompdf). */
    private function cargarLogoBase64(?array $cliente): string
    {
        if (empty($cliente['logo'])) return '';
        $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
        if (!file_exists($logoPath)) return '';
        $logoData = @file_get_contents($logoPath);
        if ($logoData === false) return '';
        $logoMime = @mime_content_type($logoPath) ?: 'image/png';
        return 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
    }

    /** GET /perfiles-cargo/{id_perfil}/pdf */
    public function pdfPerfil(int $idPerfil)
    {
        if (!$this->requireSession()) return redirect()->to('/login');
        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->buscarPorId($idPerfil);
        if (!$perfil) return redirect()->back()->with('error', 'Perfil no encontrado');

        $cliente    = $this->resolverCliente((int)$perfil['id_cliente']);
        $cargoModel = new CargoClienteModel();
        $cargo      = $cargoModel->find((int)$perfil['id_cargo_cliente']);
        $indicadorModel = new PerfilCargoIndicadorModel();
        $compModel      = new PerfilCargoCompetenciaModel();
        $sstModel       = new PerfilCargoFuncionSSTClienteModel();
        $thModel        = new PerfilCargoFuncionTHClienteModel();

        $vigencia = !empty($perfil['fecha_aprobacion']) ? $perfil['fecha_aprobacion'] : date('Y-m-d');

        $html = view('perfiles_cargo/pdf_perfil', [
            'cliente'      => $cliente,
            'perfil'       => $perfil,
            'cargo'        => $cargo,
            'indicadores'  => $indicadorModel->porPerfil($idPerfil),
            'competencias' => $compModel->porPerfil($idPerfil),
            'funcionesSST' => $sstModel->porCliente((int)$perfil['id_cliente']),
            'funcionesTH'  => $thModel->porCliente((int)$perfil['id_cliente']),
            'logoBase64'   => $this->cargarLogoBase64($cliente),
            'codigo'       => 'FT-SST-100',
            'version'      => '001',
            'vigencia'     => $vigencia,
        ]);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $nombre = 'perfil_' . preg_replace('/[^a-z0-9]+/i', '_', $cargo['nombre_cargo'] ?? 'cargo') . '.pdf';
        $dompdf->stream($nombre, ['Attachment' => false]);
        exit;
    }

    /** GET /perfil-acuse/{token}/pdf — PDF individual del acuse firmado */
    public function pdfAcuse(string $token)
    {
        $acuseModel = new PerfilCargoAcuseModel();
        $acuse = $acuseModel->porToken($token);
        if (!$acuse) return redirect()->to('/')->with('error', 'Acuse no encontrado');

        $perfilModel = new PerfilCargoModel();
        $perfil = $perfilModel->buscarPorId((int)$acuse['id_perfil_cargo']);
        $cliente    = $this->resolverCliente((int)$perfil['id_cliente']);
        $cargoModel = new CargoClienteModel();
        $cargo      = $cargoModel->find((int)$perfil['id_cargo_cliente']);
        $indicadorModel = new PerfilCargoIndicadorModel();
        $compModel      = new PerfilCargoCompetenciaModel();
        $sstModel       = new PerfilCargoFuncionSSTClienteModel();
        $thModel        = new PerfilCargoFuncionTHClienteModel();

        $vigencia = !empty($perfil['fecha_aprobacion']) ? $perfil['fecha_aprobacion'] : date('Y-m-d');

        $html = view('perfiles_cargo/pdf_acuse', [
            'acuse'        => $acuse,
            'cliente'      => $cliente,
            'perfil'       => $perfil,
            'cargo'        => $cargo,
            'indicadores'  => $indicadorModel->porPerfil((int)$acuse['id_perfil_cargo']),
            'competencias' => $compModel->porPerfil((int)$acuse['id_perfil_cargo']),
            'funcionesSST' => $sstModel->porCliente((int)$perfil['id_cliente']),
            'funcionesTH'  => $thModel->porCliente((int)$perfil['id_cliente']),
            'logoBase64'   => $this->cargarLogoBase64($cliente),
            'codigo'       => 'FT-SST-100',
            'version'      => '001',
            'vigencia'     => $vigencia,
        ]);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('acuse_' . substr($token, 0, 8) . '.pdf', ['Attachment' => false]);
        exit;
    }

    private function obtenerInput(): array
    {
        $raw = $this->request->getBody();
        if (is_string($raw) && $raw !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) return $json;
        }
        return $this->request->getPost() ?: [];
    }
}
