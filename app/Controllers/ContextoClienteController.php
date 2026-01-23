<?php

namespace App\Controllers;

use App\Models\ClienteContextoSstModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use CodeIgniter\Controller;

class ContextoClienteController extends Controller
{
    protected $contextoModel;
    protected $clienteModel;
    protected $consultorModel;

    public function __construct()
    {
        $this->contextoModel = new ClienteContextoSstModel();
        $this->clienteModel = new ClientModel();
        $this->consultorModel = new ConsultantModel();
    }

    /**
     * Selector de cliente para ver/editar contexto
     */
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Obtener todos los clientes activos (sin filtrar por consultor)
        $clientes = $this->clienteModel
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente', 'ASC')
            ->findAll();

        // Obtener el contexto SST de cada cliente
        $clientesConContexto = [];
        foreach ($clientes as $cliente) {
            $contexto = $this->contextoModel->getByCliente($cliente['id_cliente']);
            $cliente['contexto'] = $contexto;
            $cliente['tiene_contexto'] = !empty($contexto);
            $clientesConContexto[] = $cliente;
        }

        return view('contexto/seleccionar_cliente', [
            'clientes' => $clientesConContexto
        ]);
    }

    /**
     * Ver/Editar contexto de un cliente
     */
    public function ver(int $idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/contexto')->with('error', 'Cliente no encontrado');
        }

        $contexto = $this->contextoModel->getByCliente($idCliente);
        $sedes = $this->getSedes($idCliente);
        $peligrosDisponibles = $this->getPeligrosDisponibles();
        $consultores = $this->consultorModel->orderBy('nombre_consultor', 'ASC')->findAll();

        return view('contexto/formulario', [
            'cliente' => $cliente,
            'contexto' => $contexto,
            'sedes' => $sedes,
            'peligrosDisponibles' => $peligrosDisponibles,
            'arls' => $this->getARLs(),
            'sectores' => $this->getSectoresEconomicos(),
            'consultores' => $consultores
        ]);
    }

    /**
     * Guardar contexto SST
     */
    public function guardar()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idCliente = $this->request->getPost('id_cliente');

        // Validar
        $rules = [
            'id_cliente' => 'required|numeric',
            'total_trabajadores' => 'required|numeric|greater_than[0]',
            'estandares_aplicables' => 'required|in_list[7,21,60]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Obtener valores del formulario
        $nuevoTrabajadores = (int) $this->request->getPost('total_trabajadores');
        $nivelesRiesgo = $this->request->getPost('niveles_riesgo_arl') ?? [];
        $estandaresAplicables = (int) $this->request->getPost('estandares_aplicables') ?: 60;

        // Calcular el riesgo máximo para compatibilidad con campo anterior
        $ordenRiesgo = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5];
        $riesgoMaximo = 'I';
        foreach ($nivelesRiesgo as $nivel) {
            if (($ordenRiesgo[$nivel] ?? 0) > ($ordenRiesgo[$riesgoMaximo] ?? 0)) {
                $riesgoMaximo = $nivel;
            }
        }

        // Detectar cambio de nivel de estándares
        $contextoAnterior = $this->contextoModel->getByCliente($idCliente);
        $nivelAnterior = $contextoAnterior['estandares_aplicables'] ?? 60;
        $cambioNivel = [
            'cambio_detectado' => ($nivelAnterior != $estandaresAplicables),
            'nivel_anterior' => $nivelAnterior,
            'nivel_nuevo' => $estandaresAplicables
        ];

        // Preparar datos
        $datos = [
            'sector_economico' => $this->request->getPost('sector_economico'),
            'codigo_ciiu_secundario' => $this->request->getPost('codigo_ciiu_secundario'),
            'nivel_riesgo_arl' => $riesgoMaximo,
            'niveles_riesgo_arl' => json_encode($nivelesRiesgo),
            'estandares_aplicables' => $estandaresAplicables,
            'arl_actual' => $this->request->getPost('arl_actual'),
            'total_trabajadores' => $nuevoTrabajadores,
            'trabajadores_directos' => $this->request->getPost('trabajadores_directos') ?? $nuevoTrabajadores,
            'trabajadores_temporales' => $this->request->getPost('trabajadores_temporales') ?? 0,
            'contratistas_permanentes' => $this->request->getPost('contratistas_permanentes') ?? 0,
            'numero_sedes' => $this->request->getPost('numero_sedes') ?? 1,
            'turnos_trabajo' => json_encode($this->request->getPost('turnos_trabajo') ?? []),
            'id_consultor_responsable' => $this->request->getPost('id_consultor_responsable'),
            'tiene_copasst' => $this->request->getPost('tiene_copasst') ? 1 : 0,
            'tiene_vigia_sst' => $this->request->getPost('tiene_vigia_sst') ? 1 : 0,
            'tiene_comite_convivencia' => $this->request->getPost('tiene_comite_convivencia') ? 1 : 0,
            'tiene_brigada_emergencias' => $this->request->getPost('tiene_brigada_emergencias') ? 1 : 0,
            'peligros_identificados' => json_encode($this->request->getPost('peligros') ?? []),
            // Contexto y observaciones (información cualitativa para IA)
            'observaciones_contexto' => $this->request->getPost('observaciones_contexto'),
            // Firmantes de documentos
            'requiere_delegado_sst' => $this->request->getPost('requiere_delegado_sst') ? 1 : 0,
            'delegado_sst_nombre' => $this->request->getPost('delegado_sst_nombre'),
            'delegado_sst_cargo' => $this->request->getPost('delegado_sst_cargo'),
            'delegado_sst_email' => $this->request->getPost('delegado_sst_email'),
            'delegado_sst_cedula' => $this->request->getPost('delegado_sst_cedula'),
            'representante_legal_nombre' => $this->request->getPost('representante_legal_nombre'),
            'representante_legal_cargo' => $this->request->getPost('representante_legal_cargo'),
            'representante_legal_email' => $this->request->getPost('representante_legal_email'),
            'representante_legal_cedula' => $this->request->getPost('representante_legal_cedula')
        ];

        // Guardar contexto
        $resultado = $this->contextoModel->saveContexto($idCliente, $datos);

        if ($resultado) {
            // Guardar historial si hubo cambio de nivel
            if ($cambioNivel['cambio_detectado']) {
                $this->guardarHistorialCambio($idCliente, $cambioNivel);
            }

            $mensaje = "Contexto SST guardado correctamente. Nivel de estándares: {$estandaresAplicables}";

            if ($cambioNivel['cambio_detectado']) {
                $mensaje .= ". ATENCION: Se detecto cambio de {$cambioNivel['nivel_anterior']} a {$cambioNivel['nivel_nuevo']} estándares. ";
                $mensaje .= "Revise las transiciones pendientes en el modulo de Estandares.";
            }

            return redirect()->to("/contexto/{$idCliente}")->with('success', $mensaje);
        }

        return redirect()->back()->withInput()->with('error', 'Error al guardar el contexto');
    }

    /**
     * API: Obtener contexto como JSON (para IA)
     */
    public function getContextoJson(int $idCliente)
    {
        if (!$this->request->isAJAX() && !session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(403);
        }

        $cliente = $this->clienteModel->find($idCliente);
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $sedes = $this->getSedes($idCliente);

        if (!$cliente) {
            return $this->response->setJSON(['error' => 'Cliente no encontrado']);
        }

        // Construir contexto completo para IA
        $contextoCompleto = [
            'empresa' => [
                'razon_social' => $cliente['nombre_cliente'],
                'nit' => $cliente['nit_cliente'],
                'direccion' => $cliente['direccion_cliente'],
                'ciudad' => $cliente['ciudad_cliente'],
                'representante_legal' => $cliente['nombre_rep_legal'],
                'cedula_rep_legal' => $cliente['cedula_rep_legal']
            ],
            'clasificacion' => [
                'actividad_economica' => $cliente['codigo_actividad_economica'] ?? '',
                'sector_economico' => $contexto['sector_economico'] ?? '',
                'nivel_riesgo_arl' => $contexto['nivel_riesgo_arl'] ?? 'I',
                'arl' => $contexto['arl_actual'] ?? '',
                'estandares_aplicables' => $contexto['estandares_aplicables'] ?? 60
            ],
            'estructura' => [
                'total_trabajadores' => $contexto['total_trabajadores'] ?? 1,
                'trabajadores_directos' => $contexto['trabajadores_directos'] ?? 1,
                'trabajadores_temporales' => $contexto['trabajadores_temporales'] ?? 0,
                'contratistas' => $contexto['contratistas_permanentes'] ?? 0,
                'numero_sedes' => $contexto['numero_sedes'] ?? 1,
                'turnos' => json_decode($contexto['turnos_trabajo'] ?? '[]', true)
            ],
            'sst' => [
                'responsable_nombre' => $contexto['responsable_sgsst_nombre'] ?? '',
                'responsable_cargo' => $contexto['responsable_sgsst_cargo'] ?? '',
                'licencia_numero' => $contexto['licencia_sst_numero'] ?? '',
                'licencia_vigencia' => $contexto['licencia_sst_vigencia'] ?? '',
                'tiene_copasst' => (bool) ($contexto['tiene_copasst'] ?? false),
                'tiene_vigia' => (bool) ($contexto['tiene_vigia_sst'] ?? false),
                'tiene_comite_convivencia' => (bool) ($contexto['tiene_comite_convivencia'] ?? false),
                'tiene_brigada' => (bool) ($contexto['tiene_brigada_emergencias'] ?? false)
            ],
            'peligros' => json_decode($contexto['peligros_identificados'] ?? '[]', true),
            'observaciones_contexto' => $contexto['observaciones_contexto'] ?? '',
            'sedes' => array_map(function($sede) {
                return [
                    'nombre' => $sede['nombre_sede'],
                    'direccion' => $sede['direccion'],
                    'ciudad' => $sede['ciudad'],
                    'trabajadores' => $sede['trabajadores_sede'],
                    'es_principal' => (bool) $sede['es_sede_principal']
                ];
            }, $sedes)
        ];

        return $this->response->setJSON($contextoCompleto);
    }

    /**
     * Obtener sedes del cliente
     */
    private function getSedes(int $idCliente): array
    {
        $db = \Config\Database::connect();
        return $db->table('tbl_cliente_sedes')
                  ->where('id_cliente', $idCliente)
                  ->where('activo', 1)
                  ->orderBy('es_sede_principal', 'DESC')
                  ->orderBy('nombre_sede', 'ASC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Guardar historial de cambio de contexto
     */
    private function guardarHistorialCambio(int $idCliente, array $cambio): void
    {
        $db = \Config\Database::connect();
        $db->table('tbl_cliente_contexto_historial')->insert([
            'id_cliente' => $idCliente,
            'campo_modificado' => 'estandares_aplicables',
            'valor_anterior' => $cambio['nivel_anterior'],
            'valor_nuevo' => $cambio['nivel_nuevo'],
            'impacto' => "Cambio de {$cambio['nivel_anterior']} a {$cambio['nivel_nuevo']} estándares aplicables",
            'usuario_id' => session()->get('id_usuario')
        ]);
    }

    /**
     * Lista de ARLs en Colombia
     */
    private function getARLs(): array
    {
        return [
            'Positiva',
            'Sura',
            'Colmena',
            'Seguros Bolívar',
            'Axa Colpatria',
            'Liberty',
            'Mapfre',
            'Equidad Seguros',
            'La Previsora'
        ];
    }

    /**
     * Lista de sectores económicos
     */
    private function getSectoresEconomicos(): array
    {
        return [
            'Agricultura, ganadería, caza, silvicultura y pesca',
            'Explotación de minas y canteras',
            'Industrias manufactureras',
            'Suministro de electricidad, gas, vapor y aire acondicionado',
            'Construcción',
            'Comercio al por mayor y al por menor',
            'Transporte y almacenamiento',
            'Alojamiento y servicios de comida',
            'Información y comunicaciones',
            'Actividades financieras y de seguros',
            'Actividades inmobiliarias',
            'Actividades profesionales, científicas y técnicas',
            'Actividades de servicios administrativos y de apoyo',
            'Administración pública y defensa',
            'Educación',
            'Actividades de atención de la salud humana',
            'Actividades artísticas, de entretenimiento y recreación',
            'Otras actividades de servicios'
        ];
    }

    /**
     * Lista de peligros disponibles por categoría
     */
    private function getPeligrosDisponibles(): array
    {
        return [
            'Físicos' => [
                'ruido' => 'Ruido (continuo, intermitente, impacto)',
                'iluminacion' => 'Iluminación (deficiencia, exceso)',
                'vibracion' => 'Vibración (cuerpo entero, segmentaria)',
                'temperaturas_altas' => 'Temperaturas extremas - Calor',
                'temperaturas_bajas' => 'Temperaturas extremas - Frío',
                'presion_atmosferica' => 'Presión atmosférica (normal, ajustada)',
                'radiaciones_ionizantes' => 'Radiaciones ionizantes (rayos X, gamma)',
                'radiaciones_no_ionizantes' => 'Radiaciones no ionizantes (UV, láser, infrarrojo)'
            ],
            'Químicos' => [
                'polvos_organicos' => 'Polvos orgánicos',
                'polvos_inorganicos' => 'Polvos inorgánicos',
                'fibras' => 'Fibras',
                'gases_vapores' => 'Gases y vapores',
                'humos_metalicos' => 'Humos metálicos',
                'humos_no_metalicos' => 'Humos no metálicos',
                'material_particulado' => 'Material particulado',
                'liquidos_quimicos' => 'Líquidos (nieblas y rocíos)'
            ],
            'Biológicos' => [
                'virus' => 'Virus',
                'bacterias' => 'Bacterias',
                'hongos' => 'Hongos',
                'parasitos' => 'Parásitos',
                'picaduras' => 'Picaduras (insectos, arañas)',
                'mordeduras' => 'Mordeduras (serpientes, roedores)',
                'fluidos_biologicos' => 'Fluidos o excrementos'
            ],
            'Biomecánicos' => [
                'postura_prolongada' => 'Postura prolongada (sentado, de pie)',
                'postura_forzada' => 'Postura forzada (fuera de ángulos de confort)',
                'postura_antigravitacional' => 'Postura anti gravitacional',
                'movimiento_repetitivo' => 'Movimiento repetitivo',
                'manipulacion_cargas' => 'Esfuerzo (manipulación manual de cargas)'
            ],
            'Psicosociales' => [
                'gestion_organizacional' => 'Gestión organizacional (estilo de mando, evaluación)',
                'condiciones_tarea' => 'Características de la tarea (carga mental, monotonía)',
                'jornada_trabajo' => 'Jornada de trabajo (pausas, trabajo nocturno)',
                'interfaz_persona_tarea' => 'Interfaz persona-tarea (conocimientos, habilidades)',
                'condiciones_medio_ambiente' => 'Condiciones del medio ambiente de trabajo'
            ],
            'Condiciones de Seguridad' => [
                'mecanico' => 'Mecánico (elementos de máquinas, herramientas)',
                'electrico' => 'Eléctrico (alta y baja tensión, estática)',
                'locativo' => 'Locativo (superficies, orden, almacenamiento)',
                'tecnologico' => 'Tecnológico (explosión, fuga, incendio)',
                'accidentes_transito' => 'Accidentes de tránsito',
                'publicos' => 'Públicos (robos, asaltos, atentados)',
                'trabajo_alturas' => 'Trabajo en alturas',
                'espacios_confinados' => 'Espacios confinados'
            ],
            'Fenómenos Naturales' => [
                'sismo' => 'Sismo / Terremoto',
                'inundacion' => 'Inundación',
                'vendaval' => 'Vendaval',
                'derrumbe' => 'Derrumbe / Deslizamiento',
                'precipitaciones' => 'Precipitaciones (lluvias, granizo)',
                'tormenta_electrica' => 'Tormenta eléctrica'
            ]
        ];
    }
}
