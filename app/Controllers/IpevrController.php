<?php

namespace App\Controllers;

use App\Models\IpevrMatrizModel;
use App\Models\IpevrFilaModel;
use App\Models\IpevrControlCambiosModel;
use App\Models\Gtc45CatalogoModel;
use App\Models\ProcesoClienteModel;
use App\Models\CargoClienteModel;
use App\Models\TareaClienteModel;
use App\Models\ZonaClienteModel;
use CodeIgniter\Controller;
use Config\Database;

/**
 * IPEVR GTC 45 — Controller principal (PC / escritorio).
 *
 * Esqueleto Fase 3b.1: metodos declarados pero sin logica de negocio.
 * La implementacion real entra en 3b.2 (maestros), 3b.3 (editor), 3b.4 (IA),
 * 3b.6 (export) y 3b.7 (versionamiento/firmas).
 */
class IpevrController extends Controller
{
    protected IpevrMatrizModel $matrizModel;
    protected IpevrFilaModel $filaModel;
    protected IpevrControlCambiosModel $ccModel;
    protected Gtc45CatalogoModel $catalogo;

    public function __construct()
    {
        $this->matrizModel = new IpevrMatrizModel();
        $this->filaModel   = new IpevrFilaModel();
        $this->ccModel     = new IpevrControlCambiosModel();
        $this->catalogo    = new Gtc45CatalogoModel();
    }

    public function listarPorCliente(int $idCliente)
    {
        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        $matrices = $this->matrizModel->porCliente($idCliente);

        // Enriquecer cada matriz con el conteo de filas
        foreach ($matrices as &$m) {
            $m['total_filas'] = $this->filaModel->contarPorMatriz((int)$m['id']);
        }
        unset($m);

        return view('ipevr/index', [
            'titulo'   => 'Matriz IPEVR - GTC 45',
            'cliente'  => $cliente,
            'matrices' => $matrices,
            'estados'  => IpevrMatrizModel::ESTADOS,
        ]);
    }

    public function editorPc(int $idMatriz)
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

        $procesoModel = new \App\Models\ProcesoClienteModel();
        $cargoModel   = new \App\Models\CargoClienteModel();
        $tareaModel   = new \App\Models\TareaClienteModel();
        $zonaModel    = new \App\Models\ZonaClienteModel();

        return view('ipevr/editor_pc', [
            'titulo'   => 'Editor Matriz IPEVR - v' . $matriz['version'],
            'matriz'   => $matriz,
            'cliente'  => $cliente,
            'filas'    => $filas,
            'catalogo' => $this->catalogo->bundleFrontend(),
            'maestros' => [
                'procesos' => $procesoModel->porCliente((int)$matriz['id_cliente'], true),
                'cargos'   => $cargoModel->porCliente((int)$matriz['id_cliente'], true),
                'tareas'   => $tareaModel->porCliente((int)$matriz['id_cliente'], true),
                'zonas'    => $zonaModel->porCliente((int)$matriz['id_cliente'], true),
            ],
        ]);
    }

    public function tablasGtc45()
    {
        return view('ipevr/tablas_gtc45', [
            'titulo'  => 'Tablas de Referencia GTC 45',
            'catalogo' => $this->catalogo->bundleFrontend(),
        ]);
    }

    public function tablasGtc45Xlsx()
    {
        $lib = new \App\Libraries\IpevrExportXlsx();
        $sp = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sp->getProperties()->setTitle('Tablas GTC 45');
        $lib2 = new class { use \App\Libraries\IpevrExportXlsxTablasHelper; };
        // Reusar la hoja de tablas directamente
        $catalogo = $this->catalogo->bundleFrontend();
        $sheet = $sp->getActiveSheet();
        $sheet->setTitle('Tablas GTC 45');
        $r = 1;
        $sheet->setCellValue('A'.$r, 'TABLAS DE EVALUACION DEL RIESGO - GTC 45');
        $sheet->getStyle('A'.$r)->getFont()->setBold(true)->setSize(14);
        $r += 2;
        $secciones = [
            ['Tabla 2: Nivel de Deficiencia (ND)', $catalogo['nd'], ['codigo','nombre','valor','descripcion']],
            ['Tabla 3: Nivel de Exposicion (NE)', $catalogo['ne'], ['codigo','nombre','valor','descripcion']],
            ['Tabla 5: Nivel de Probabilidad (NP)', $catalogo['np'], ['codigo','nombre','rango_min','rango_max','descripcion']],
            ['Tabla 6: Nivel de Consecuencia (NC)', $catalogo['nc'], ['codigo','nombre','valor','danos_personales']],
            ['Tabla 8: Significado del Nivel de Riesgo', $catalogo['nr'], ['codigo','nombre','rango_min','rango_max','significado','aceptabilidad']],
        ];
        foreach ($secciones as [$titulo, $datos, $cols]) {
            $sheet->setCellValue('A'.$r, $titulo);
            $sheet->getStyle('A'.$r)->getFont()->setBold(true)->setSize(11);
            $r++;
            foreach ($cols as $i => $c) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1).$r, strtoupper($c));
            }
            $sheet->getStyle('A'.$r.':'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cols)).$r)->getFont()->setBold(true);
            $r++;
            foreach ($datos as $fila) {
                foreach ($cols as $i => $c) {
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1).$r, $fila[$c] ?? '');
                }
                $r++;
            }
            $r++;
        }
        for ($c = 1; $c <= 6; $c++) $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        $tmp = tempnam(sys_get_temp_dir(), 'gtc45_') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sp))->save($tmp);
        return $this->response->download($tmp, null)->setFileName('Tablas_GTC45_' . date('Ymd') . '.xlsx');
    }

    public function tablasGtc45Pdf()
    {
        $catalogo = $this->catalogo->bundleFrontend();
        $html = view('ipevr/tablas_gtc45_pdf', ['catalogo' => $catalogo]);
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $tmp = tempnam(sys_get_temp_dir(), 'gtc45_') . '.pdf';
        file_put_contents($tmp, $dompdf->output());
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="Tablas_GTC45_' . date('Ymd') . '.pdf"')
            ->setBody(file_get_contents($tmp));
    }

    // ============ ACELERADOR DE PLAN MAESTRO ============

    public function acelerador(int $idMatriz)
    {
        $matriz = $this->matrizModel->find($idMatriz);
        if (!$matriz) return $this->response->setStatusCode(404)->setBody('Matriz no encontrada');

        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')->where('id_cliente', $matriz['id_cliente'])->get()->getRowArray();
        $filas = $this->filaModel->porMatriz($idMatriz);

        $mapClasif = array_column($this->catalogo->clasificaciones(), 'nombre', 'id');
        $mapNR = array_column($this->catalogo->nivelesRiesgo(), null, 'id');

        // Enriquecer filas
        foreach ($filas as &$f) {
            $f['_clasificacion'] = $mapClasif[$f['id_clasificacion']] ?? '';
            $nr = $mapNR[$f['id_nivel_riesgo']] ?? null;
            $f['_nivel'] = $nr['nombre'] ?? '';
            $f['_color'] = $nr['color_hex'] ?? '#999';
            $f['_aceptabilidad'] = $f['aceptabilidad'] ?? '';
        }
        unset($f);

        return view('ipevr/acelerador', [
            'titulo'  => 'Acelerador de Plan Maestro',
            'matriz'  => $matriz,
            'cliente' => $cliente,
            'filas'   => $filas,
        ]);
    }

    public function aceleradorGenerar()
    {
        $tipo = (string)$this->request->getPost('tipo'); // pta|capacitaciones|inspecciones|pve
        $idMatriz = (int)$this->request->getPost('id_matriz');
        $matriz = $this->matrizModel->find($idMatriz);
        if (!$matriz) return $this->response->setJSON(['ok' => false, 'error' => 'Matriz no encontrada']);

        $db = Database::connect();
        $idCliente = (int)$matriz['id_cliente'];
        $filas = $this->filaModel->porMatriz($idMatriz);
        $ctx = $db->table('tbl_cliente_contexto_sst')->where('id_cliente', $idCliente)->get()->getRowArray() ?: [];
        $cliente = $db->table('tbl_clientes')->where('id_cliente', $idCliente)->get()->getRowArray() ?: [];

        $mapClasif = array_column($this->catalogo->clasificaciones(), 'nombre', 'id');
        $resumenPeligros = [];
        foreach ($filas as $f) {
            $cl = $mapClasif[$f['id_clasificacion']] ?? 'Otro';
            $resumenPeligros[$cl][] = $f['descripcion_peligro'] ?? '';
        }

        $contextoTexto = "Empresa: " . ($cliente['nombre_cliente'] ?? '') . "\n"
            . "Sector: " . ($ctx['sector_economico'] ?? 'no especificado') . "\n"
            . "Trabajadores: " . ($ctx['total_trabajadores'] ?? '') . "\n"
            . "Filas en la matriz IPEVR: " . count($filas) . "\n"
            . "Peligros por clasificacion:\n";
        foreach ($resumenPeligros as $cl => $pels) {
            $contextoTexto .= "  - {$cl}: " . implode('; ', array_unique(array_filter($pels))) . "\n";
        }

        $apiKey = env('OPENAI_API_KEY', '');
        if (!$apiKey) return $this->response->setJSON(['ok' => false, 'error' => 'OPENAI_API_KEY no configurada']);

        $prompts = $this->aceleradorPrompts($tipo, $contextoTexto);
        if (!$prompts) return $this->response->setJSON(['ok' => false, 'error' => 'Tipo no válido']);

        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $prompts['system']],
                ['role' => 'user',   'content' => $prompts['user']],
            ],
            'temperature' => 0.4,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) return $this->response->setJSON(['ok' => false, 'error' => "HTTP {$code}"]);

        $json = json_decode($resp, true);
        $contenido = json_decode($json['choices'][0]['message']['content'] ?? '{}', true);

        // Todos devuelven sugerencias para preview; guardar es POST aparte
        return $this->response->setJSON(['ok' => true, 'sugerencias' => $contenido]);
    }

    protected function aceleradorPrompts(string $tipo, string $contexto): ?array
    {
        $base = "Eres un consultor experto en SG-SST colombiano. Basandote en la matriz IPEVR del cliente, genera sugerencias.\n\n";

        switch ($tipo) {
            case 'pta':
                return [
                    'system' => $base . "Genera actividades para el Plan de Trabajo Anual (PTA) basandote en las medidas de intervencion de la matriz IPEVR.\n"
                        . "Responde SOLO con JSON: {\"actividades\": [{\"actividad\": \"...\", \"tipo_servicio\": \"Programa|Procedimiento|Capacitacion|Medicion\", \"phva\": \"PLANEAR|HACER|VERIFICAR|ACTUAR\", \"numeral\": \"ej: 2.4.1\", \"responsable_sugerido\": \"Cycloid Talent - Cliente\"}]}\n"
                        . "Genera entre 10 y 25 actividades diversas que cubran los peligros criticos.",
                    'user' => $contexto,
                ];
            case 'capacitaciones':
                return [
                    'system' => $base . "Genera capacitaciones sugeridas para el cronograma de capacitaciones del SG-SST basandote en los peligros identificados.\n"
                        . "Responde SOLO con JSON: {\"capacitaciones\": [{\"nombre\": \"...\", \"objetivo\": \"...\", \"perfil_asistentes\": \"TODOS|OPERATIVOS|ADMINISTRATIVOS|BRIGADISTAS\", \"horas\": 1}]}\n"
                        . "Genera entre 8 y 15 capacitaciones relevantes para este sector y estos peligros.",
                    'user' => $contexto,
                ];
            case 'inspecciones':
                return [
                    'system' => $base . "Sugiere inspecciones de seguridad que deberia realizar la empresa basandote en los riesgos criticos (Nivel I y II) de su matriz IPEVR.\n"
                        . "Responde SOLO con JSON: {\"inspecciones\": [{\"nombre\": \"...\", \"que_inspeccionar\": \"...\", \"frecuencia\": \"Mensual|Trimestral|Semestral\", \"responsable_sugerido\": \"...\", \"normativa\": \"...\"}]}\n"
                        . "Genera entre 5 y 12 tipos de inspeccion.",
                    'user' => $contexto,
                ];
            case 'pve':
                return [
                    'system' => $base . "Sugiere Programas de Vigilancia Epidemiologica (PVE) que deberia implementar la empresa basandote en las clasificaciones de peligro de su matriz IPEVR.\n"
                        . "Responde SOLO con JSON: {\"pve\": [{\"nombre\": \"...\", \"peligro_asociado\": \"...\", \"poblacion_objetivo\": \"...\", \"actividades_principales\": \"...\", \"periodicidad\": \"...\", \"normativa\": \"...\"}]}\n"
                        . "Genera solo los PVE que apliquen segun los peligros reales del cliente.",
                    'user' => $contexto,
                ];
        }
        return null;
    }

    public function aceleradorGuardar()
    {
        $tipo = (string)$this->request->getPost('tipo');
        $idCliente = (int)$this->request->getPost('id_cliente');
        $datosJson = (string)$this->request->getPost('datos');
        $datos = json_decode($datosJson, true);

        if (!$idCliente || !is_array($datos) || empty($datos)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Datos inválidos']);
        }

        if ($tipo === 'pta') {
            return $this->aceleradorGuardarPta($idCliente, $datos);
        } elseif ($tipo === 'capacitaciones') {
            return $this->aceleradorGuardarCapacitaciones($idCliente, $datos);
        }
        return $this->response->setJSON(['ok' => false, 'error' => 'Tipo no soporta guardado']);
    }

    protected function aceleradorGuardarPta(int $idCliente, array $actividades): \CodeIgniter\HTTP\Response
    {
        $db = Database::connect();
        $insertados = 0;
        foreach ($actividades as $a) {
            $fecha = $a['_fecha'] ?? date('Y-m-d', strtotime('+30 days'));
            $db->table('tbl_pta_cliente')->insert([
                'id_cliente' => $idCliente,
                'tipo_servicio' => 'IPEVR',
                'phva_plandetrabajo' => $a['phva'] ?? 'HACER',
                'numeral_plandetrabajo' => $a['numeral'] ?? '',
                'actividad_plandetrabajo' => $a['actividad'] ?? '',
                'responsable_sugerido_plandetrabajo' => $a['responsable_sugerido'] ?? 'Cycloid Talent - Cliente',
                'fecha_propuesta' => $fecha,
                'estado_actividad' => 'ABIERTA',
                'porcentaje_avance' => 0,
            ]);
            $insertados++;
        }
        return $this->response->setJSON(['ok' => true, 'insertados' => $insertados, 'tipo' => 'pta']);
    }

    protected function aceleradorGuardarCapacitaciones(int $idCliente, array $capacitaciones): \CodeIgniter\HTTP\Response
    {
        $db = Database::connect();
        $insertados = 0;
        foreach ($capacitaciones as $c) {
            $nombre = $c['nombre'] ?? '';
            $existe = $db->table('capacitaciones_sst')->where('capacitacion', $nombre)->get()->getRowArray();
            if ($existe) {
                $idCap = (int)$existe['id_capacitacion'];
            } else {
                $db->table('capacitaciones_sst')->insert([
                    'capacitacion' => $nombre,
                    'objetivo_capacitacion' => $c['objetivo'] ?? '',
                ]);
                $idCap = (int)$db->insertID();
            }
            $fecha = $c['_fecha'] ?? date('Y-m-d', strtotime('+30 days'));
            $db->table('tbl_cronog_capacitacion')->insert([
                'id_capacitacion' => $idCap,
                'id_cliente' => $idCliente,
                'fecha_programada' => $fecha,
                'estado' => 'PROGRAMADA',
                'perfil_de_asistentes' => $c['perfil_asistentes'] ?? 'TODOS',
                'horas_de_duracion_de_la_capacitacion' => (int)($c['horas'] ?? 1),
            ]);
            $insertados++;
        }
        return $this->response->setJSON(['ok' => true, 'insertados' => $insertados, 'tipo' => 'capacitaciones']);
    }

    public function crear()
    {
        $idCliente = (int)$this->request->getPost('id_cliente');
        $nombre    = trim((string)$this->request->getPost('nombre'));
        $version   = trim((string)$this->request->getPost('version')) ?: '001';

        if (!$idCliente) {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_cliente requerido']);
        }
        if ($nombre === '') {
            $nombre = 'Matriz IPEVR GTC 45';
        }

        // Verificar que no exista ya esa version para ese cliente
        $existe = $this->matrizModel
            ->where('id_cliente', $idCliente)
            ->where('version', $version)
            ->first();
        if ($existe) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => "Ya existe una matriz version {$version} para este cliente",
            ]);
        }

        $id = (int)$this->matrizModel->insert([
            'id_cliente'     => $idCliente,
            'nombre'         => $nombre,
            'version'        => $version,
            'estado'         => 'borrador',
            'fecha_creacion' => date('Y-m-d'),
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'id' => $id,
            'redirect' => base_url("ipevr/matriz/{$id}/editar"),
        ]);
    }

    public function filaUpsert()
    {
        $req = $this->request;
        $idMatriz = (int)$req->getPost('id_matriz');
        if (!$idMatriz || !$this->matrizModel->find($idMatriz)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Matriz no encontrada']);
        }

        $id = (int)$req->getPost('id');
        $nullOrInt = fn($v) => ($v === null || $v === '' ? null : (int)$v);
        $str = fn($k) => ($req->getPost($k) !== null ? trim((string)$req->getPost($k)) : null);

        // Cargos expuestos: aceptar string separado por coma o array
        $cargosRaw = $req->getPost('cargos_expuestos');
        if (is_array($cargosRaw)) {
            $cargos = array_values(array_filter(array_map('trim', $cargosRaw), fn($v) => $v !== ''));
        } else {
            $cargos = array_values(array_filter(array_map('trim', explode(',', (string)$cargosRaw)), fn($v) => $v !== ''));
        }

        $data = [
            'id_matriz'           => $idMatriz,
            // Seccion 1
            'id_proceso'          => $nullOrInt($req->getPost('id_proceso')),
            'proceso_texto'       => $str('proceso_texto'),
            'id_zona'             => $nullOrInt($req->getPost('id_zona')),
            'zona_texto'          => $str('zona_texto'),
            'actividad'           => $str('actividad'),
            'id_tarea'            => $nullOrInt($req->getPost('id_tarea')),
            'tarea_texto'         => $str('tarea_texto'),
            'rutinaria'           => (int)$req->getPost('rutinaria') === 1 ? 1 : 0,
            'cargos_expuestos'    => json_encode($cargos, JSON_UNESCAPED_UNICODE),
            'num_expuestos'       => (int)$req->getPost('num_expuestos'),
            // Seccion 2
            'id_peligro_catalogo' => $nullOrInt($req->getPost('id_peligro_catalogo')),
            'descripcion_peligro' => $str('descripcion_peligro'),
            'id_clasificacion'    => $nullOrInt($req->getPost('id_clasificacion')),
            'efectos_posibles'    => $str('efectos_posibles'),
            // Seccion 3
            'control_fuente'      => $str('control_fuente'),
            'control_medio'       => $str('control_medio'),
            'control_individuo'   => $str('control_individuo'),
            // Seccion 4
            'id_nd'               => $nullOrInt($req->getPost('id_nd')),
            'id_ne'               => $nullOrInt($req->getPost('id_ne')),
            'id_nc'               => $nullOrInt($req->getPost('id_nc')),
            // Seccion 5
            'peor_consecuencia'   => $str('peor_consecuencia'),
            'requisito_legal'     => $str('requisito_legal'),
            // Seccion 6
            'medida_eliminacion'  => $str('medida_eliminacion'),
            'medida_sustitucion'  => $str('medida_sustitucion'),
            'medida_ingenieria'   => $str('medida_ingenieria'),
            'medida_administrativa' => $str('medida_administrativa'),
            'medida_epp'          => $str('medida_epp'),
            'origen_fila'         => $req->getPost('origen_fila') ?: 'manual',
        ];

        // Calculo NP y NR en backend
        $db = Database::connect();
        $nd = $data['id_nd'] ? $db->table('tbl_gtc45_nivel_deficiencia')->where('id', $data['id_nd'])->get()->getRowArray() : null;
        $ne = $data['id_ne'] ? $db->table('tbl_gtc45_nivel_exposicion')->where('id', $data['id_ne'])->get()->getRowArray() : null;
        $nc = $data['id_nc'] ? $db->table('tbl_gtc45_nivel_consecuencia')->where('id', $data['id_nc'])->get()->getRowArray() : null;

        $np = ($nd && $ne) ? ((int)$nd['valor'] * (int)$ne['valor']) : null;
        $nr = ($np !== null && $nc) ? ($np * (int)$nc['valor']) : null;

        $data['np'] = $np;
        $data['nr'] = $nr;
        $data['id_np'] = null;
        $data['id_nivel_riesgo'] = null;
        $data['aceptabilidad'] = null;

        if ($np !== null) {
            $interpNP = $this->catalogo->interpretarNP($np);
            if ($interpNP) $data['id_np'] = (int)$interpNP['id'];
        }
        if ($nr !== null) {
            $interpNR = $this->catalogo->interpretarNR($nr);
            if ($interpNR) {
                $data['id_nivel_riesgo'] = (int)$interpNR['id'];
                $data['aceptabilidad'] = $interpNR['aceptabilidad'];
            }
        }

        if ($id > 0) {
            $existe = $this->filaModel->find($id);
            if (!$existe || (int)$existe['id_matriz'] !== $idMatriz) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Fila no pertenece a esta matriz']);
            }
            $this->filaModel->update($id, $data);
        } else {
            $data['orden'] = $this->filaModel->siguienteOrden($idMatriz);
            $data['estado_fila'] = 'borrador';
            $id = (int)$this->filaModel->insert($data);
        }

        return $this->response->setJSON([
            'ok' => true,
            'id' => $id,
            'fila' => $this->filaModel->find($id),
        ]);
    }

    public function filaEliminar(int $id)
    {
        $fila = $this->filaModel->find($id);
        if (!$fila) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Fila no encontrada']);
        }
        $this->filaModel->delete($id);
        return $this->response->setJSON(['ok' => true, 'id' => $id]);
    }

    public function sugerirIa(int $idMatriz)
    {
        $matriz = $this->matrizModel->find($idMatriz);
        if (!$matriz) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Matriz no encontrada']);
        }
        if ($matriz['estado'] === 'vigente' || $matriz['estado'] === 'historica') {
            return $this->response->setJSON(['ok' => false, 'error' => 'No se puede modificar una matriz vigente/historica']);
        }

        $cantidadRaw = (int)$this->request->getPost('cantidad');
        $modoAuto = ($cantidadRaw === 0);
        $cantidad = $modoAuto ? 0 : max(5, min(50, $cantidadRaw ?: 25));

        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')->where('id_cliente', $matriz['id_cliente'])->get()->getRowArray();
        $ctx = $db->table('tbl_cliente_contexto_sst')->where('id_cliente', $matriz['id_cliente'])->get()->getRowArray();

        if (!$ctx) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'contexto_faltante',
                'message' => 'Debe diligenciar primero el Contexto del Cliente antes de generar filas con IA.',
                'url_contexto' => base_url('contexto/' . $matriz['id_cliente']),
            ]);
        }

        $procesoModel = new \App\Models\ProcesoClienteModel();
        $cargoModel   = new \App\Models\CargoClienteModel();
        $tareaModel   = new \App\Models\TareaClienteModel();
        $zonaModel    = new \App\Models\ZonaClienteModel();
        $maestros = [
            'procesos' => $procesoModel->porCliente((int)$matriz['id_cliente'], true),
            'cargos'   => $cargoModel->porCliente((int)$matriz['id_cliente'], true),
            'tareas'   => $tareaModel->porCliente((int)$matriz['id_cliente'], true),
            'zonas'    => $zonaModel->porCliente((int)$matriz['id_cliente'], true),
        ];

        $sugeridor = new \App\Libraries\IpevrIaSugeridor();
        if (!$sugeridor->disponible()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'OPENAI_API_KEY no configurada']);
        }

        $resultado = $sugeridor->sugerir(
            $ctx ?: [],
            $cliente ?: [],
            $this->catalogo->bundleFrontend(),
            $maestros,
            $cantidad,
            $modoAuto
        );

        if (!$resultado['ok']) {
            return $this->response->setJSON(['ok' => false, 'error' => $resultado['error']]);
        }

        // Mapas para resolver codigos -> ids
        $mapClasif = array_column($this->catalogo->clasificaciones(), 'id', 'codigo');
        $mapND     = array_column($this->catalogo->nivelesDeficiencia(), null, 'codigo');
        $mapNE     = array_column($this->catalogo->nivelesExposicion(), null, 'codigo');
        $mapNC     = array_column($this->catalogo->nivelesConsecuencia(), null, 'codigo');

        $ordenInicial = $this->filaModel->siguienteOrden($idMatriz);
        $insertadas = 0;
        $rechazadas = 0;
        $errores = [];

        foreach ($resultado['filas'] as $i => $fila) {
            $ndCode = $fila['nd'] ?? null;
            $neCode = $fila['ne'] ?? null;
            $ncCode = $fila['nc'] ?? null;
            $clasifCode = $fila['clasificacion'] ?? null;

            if (!isset($mapND[$ndCode]) || !isset($mapNE[$neCode]) || !isset($mapNC[$ncCode])) {
                $rechazadas++;
                $errores[] = "Fila {$i}: ND/NE/NC desconocido ({$ndCode}/{$neCode}/{$ncCode})";
                continue;
            }

            $idNd = (int)$mapND[$ndCode]['id'];
            $idNe = (int)$mapNE[$neCode]['id'];
            $idNc = (int)$mapNC[$ncCode]['id'];
            $np   = (int)$mapND[$ndCode]['valor'] * (int)$mapNE[$neCode]['valor'];
            $nr   = $np * (int)$mapNC[$ncCode]['valor'];
            $interpNP = $this->catalogo->interpretarNP($np);
            $interpNR = $this->catalogo->interpretarNR($nr);

            $medidas = $fila['medidas'] ?? [];
            $cargos = $fila['cargos_expuestos'] ?? [];
            if (!is_array($cargos)) $cargos = [(string)$cargos];

            $data = [
                'id_matriz'           => $idMatriz,
                'orden'               => $ordenInicial++,
                'proceso_texto'       => (string)($fila['proceso'] ?? ''),
                'zona_texto'          => (string)($fila['zona'] ?? ''),
                'actividad'           => (string)($fila['actividad'] ?? ''),
                'tarea_texto'         => (string)($fila['tarea'] ?? ''),
                'rutinaria'           => !empty($fila['rutinaria']) ? 1 : 0,
                'cargos_expuestos'    => json_encode($cargos, JSON_UNESCAPED_UNICODE),
                'num_expuestos'       => (int)($fila['num_expuestos'] ?? 0),
                'descripcion_peligro' => (string)($fila['peligro_descripcion'] ?? ''),
                'id_clasificacion'    => $mapClasif[$clasifCode] ?? null,
                'efectos_posibles'    => (string)($fila['efectos_posibles'] ?? ''),
                'control_fuente'      => (string)($fila['control_fuente'] ?? ''),
                'control_medio'       => (string)($fila['control_medio'] ?? ''),
                'control_individuo'   => (string)($fila['control_individuo'] ?? ''),
                'id_nd'               => $idNd,
                'id_ne'               => $idNe,
                'np'                  => $np,
                'id_np'               => $interpNP ? (int)$interpNP['id'] : null,
                'id_nc'               => $idNc,
                'nr'                  => $nr,
                'id_nivel_riesgo'     => $interpNR ? (int)$interpNR['id'] : null,
                'aceptabilidad'       => $interpNR['aceptabilidad'] ?? null,
                'peor_consecuencia'   => (string)($fila['peor_consecuencia'] ?? ''),
                'requisito_legal'     => (string)($fila['requisito_legal'] ?? ''),
                'medida_eliminacion'  => (string)($medidas['eliminacion'] ?? ''),
                'medida_sustitucion'  => (string)($medidas['sustitucion'] ?? ''),
                'medida_ingenieria'   => (string)($medidas['ingenieria'] ?? ''),
                'medida_administrativa' => (string)($medidas['administrativo'] ?? ''),
                'medida_epp'          => (string)($medidas['epp'] ?? ''),
                'origen_fila'         => 'ia',
                'estado_fila'         => 'borrador',
            ];
            try {
                $this->filaModel->insert($data);
                $insertadas++;
            } catch (\Throwable $e) {
                $rechazadas++;
                $errores[] = "Fila {$i}: " . $e->getMessage();
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'cantidad_solicitada' => $cantidad,
            'cantidad_generada'   => count($resultado['filas']),
            'insertadas' => $insertadas,
            'rechazadas' => $rechazadas,
            'errores'    => $errores,
        ]);
    }

    public function exportarXlsx(int $idMatriz)
    {
        $bundle = $this->cargarBundleExport($idMatriz);
        if (!$bundle['ok']) {
            return $this->response->setStatusCode(404)->setBody($bundle['error']);
        }
        $lib = new \App\Libraries\IpevrExportXlsx();
        $path = $lib->generar($bundle['matriz'], $bundle['cliente'], $bundle['filas'], $bundle['catalogo'], $bundle['maestros']);
        $nombre = 'Matriz_IPEVR_v' . $bundle['matriz']['version'] . '_' . date('Ymd') . '.xlsx';
        return $this->response->download($path, null)->setFileName($nombre);
    }

    public function exportarPdf(int $idMatriz)
    {
        $bundle = $this->cargarBundleExport($idMatriz);
        if (!$bundle['ok']) {
            return $this->response->setStatusCode(404)->setBody($bundle['error']);
        }
        $lib = new \App\Libraries\IpevrExportPdf();
        $path = $lib->generar($bundle['matriz'], $bundle['cliente'], $bundle['filas'], $bundle['catalogo'], $bundle['maestros']);
        $nombre = 'Matriz_IPEVR_v' . $bundle['matriz']['version'] . '_' . date('Ymd') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombre . '"')
            ->setBody(file_get_contents($path));
    }

    protected function cargarBundleExport(int $idMatriz): array
    {
        $matriz = $this->matrizModel->find($idMatriz);
        if (!$matriz) return ['ok' => false, 'error' => 'Matriz no encontrada'];

        $db = Database::connect();
        $cliente = $db->table('tbl_clientes')->where('id_cliente', $matriz['id_cliente'])->get()->getRowArray() ?: [];
        $filas = $this->filaModel->porMatriz($idMatriz);
        $maestros = [
            'procesos' => (new \App\Models\ProcesoClienteModel())->porCliente((int)$matriz['id_cliente'], false),
            'cargos'   => (new \App\Models\CargoClienteModel())->porCliente((int)$matriz['id_cliente'], false),
            'tareas'   => (new \App\Models\TareaClienteModel())->porCliente((int)$matriz['id_cliente'], false),
            'zonas'    => (new \App\Models\ZonaClienteModel())->porCliente((int)$matriz['id_cliente'], false),
        ];
        return [
            'ok' => true,
            'matriz' => $matriz,
            'cliente' => $cliente,
            'filas' => $filas,
            'catalogo' => $this->catalogo->bundleFrontend(),
            'maestros' => $maestros,
        ];
    }

    /**
     * Transicion de estado + snapshot + control de cambios.
     *
     * POST params:
     *   - accion: 'enviar_revision' | 'aprobar' | 'marcar_historica' | 'nueva_version'
     *   - descripcion: texto del cambio (obligatorio si accion es nueva_version o aprobar)
     *
     * Transiciones validas:
     *   borrador  -> revision
     *   revision  -> aprobada -> vigente (auto en la misma accion)
     *   vigente   -> historica (al crear nueva version)
     *
     * Firmas: la integracion profunda con el sistema de firmas existente queda
     * como punto de extension documentado. Por ahora, al aprobar se marca
     * elaborado_por/aprobado_por con los datos del POST.
     */
    public function nuevaVersion(int $idMatriz)
    {
        $matriz = $this->matrizModel->find($idMatriz);
        if (!$matriz) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Matriz no encontrada']);
        }

        $accion = (string)$this->request->getPost('accion');
        $descripcion = trim((string)$this->request->getPost('descripcion'));
        $usuario = (string)$this->request->getPost('usuario') ?: 'sistema';

        $estadoActual = $matriz['estado'];

        switch ($accion) {
            case 'enviar_revision':
                if ($estadoActual !== 'borrador') {
                    return $this->response->setJSON(['ok' => false, 'error' => 'Solo se puede enviar a revision desde borrador']);
                }
                $this->matrizModel->update($idMatriz, ['estado' => 'revision']);
                $this->ccModel->insert([
                    'id_matriz' => $idMatriz,
                    'version' => $matriz['version'],
                    'descripcion' => $descripcion ?: 'Enviada a revision',
                    'fecha' => date('Y-m-d'),
                    'usuario' => $usuario,
                ]);
                return $this->response->setJSON(['ok' => true, 'nuevo_estado' => 'revision']);

            case 'aprobar':
                if ($estadoActual !== 'revision') {
                    return $this->response->setJSON(['ok' => false, 'error' => 'Solo se puede aprobar desde revision']);
                }
                // Snapshot de las filas en JSON para trazabilidad
                $filas = $this->filaModel->porMatriz($idMatriz);
                $snapshot = [
                    'matriz' => $matriz,
                    'filas'  => $filas,
                    'fecha'  => date('c'),
                ];
                $this->matrizModel->update($idMatriz, [
                    'estado' => 'vigente',
                    'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                    'fecha_aprobacion' => date('Y-m-d'),
                    'aprobado_por' => $this->request->getPost('aprobado_por') ?: $usuario,
                ]);
                $this->ccModel->insert([
                    'id_matriz' => $idMatriz,
                    'version' => $matriz['version'],
                    'descripcion' => $descripcion ?: 'Matriz aprobada y marcada vigente',
                    'fecha' => date('Y-m-d'),
                    'usuario' => $usuario,
                ]);
                return $this->response->setJSON(['ok' => true, 'nuevo_estado' => 'vigente']);

            case 'nueva_version':
                if ($estadoActual !== 'vigente') {
                    return $this->response->setJSON(['ok' => false, 'error' => 'Solo se puede crear nueva version desde una matriz vigente']);
                }
                // Marcar actual como historica
                $this->matrizModel->update($idMatriz, ['estado' => 'historica']);
                // Crear copia en borrador con version siguiente
                $nuevaVersion = str_pad((string)((int)$matriz['version'] + 1), 3, '0', STR_PAD_LEFT);
                $nuevoId = (int)$this->matrizModel->insert([
                    'id_cliente' => $matriz['id_cliente'],
                    'nombre' => $matriz['nombre'],
                    'version' => $nuevaVersion,
                    'estado' => 'borrador',
                    'fecha_creacion' => date('Y-m-d'),
                ]);
                // Copiar filas
                $filas = $this->filaModel->porMatriz($idMatriz);
                foreach ($filas as $f) {
                    unset($f['id'], $f['created_at'], $f['updated_at']);
                    $f['id_matriz'] = $nuevoId;
                    $f['origen_fila'] = 'importada';
                    $this->filaModel->insert($f);
                }
                $this->ccModel->insert([
                    'id_matriz' => $nuevoId,
                    'version' => $nuevaVersion,
                    'descripcion' => $descripcion ?: 'Nueva version creada desde v' . $matriz['version'],
                    'fecha' => date('Y-m-d'),
                    'usuario' => $usuario,
                ]);
                return $this->response->setJSON([
                    'ok' => true,
                    'nuevo_id_matriz' => $nuevoId,
                    'nueva_version' => $nuevaVersion,
                    'redirect' => base_url("ipevr/matriz/{$nuevoId}/editar"),
                ]);

            default:
                return $this->response->setJSON(['ok' => false, 'error' => 'Accion desconocida. Usar: enviar_revision | aprobar | nueva_version']);
        }
    }

    /**
     * Endpoint JSON con el bundle completo de catalogos GTC 45 (para frontend JS).
     * Disponible desde Fase 1 porque solo depende de los seeds.
     */
    public function catalogoJson()
    {
        return $this->response->setJSON([
            'ok' => true,
            'catalogo' => $this->catalogo->bundleFrontend(),
        ]);
    }
}
