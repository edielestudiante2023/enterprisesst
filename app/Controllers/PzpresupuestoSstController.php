<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Controlador para Módulo de Presupuesto SST (1.1.3)
 * Asignación de recursos para el SG-SST
 * @updated 2026-01-29
 */
class PzpresupuestoSstController extends BaseController
{
    protected $db;
    protected $session;

    // ID del estándar Res. 0312/2019 asociado al presupuesto (1.1.3)
    protected const ID_ESTANDAR_PRESUPUESTO = 3;

    // Cache de datos del documento
    protected $datosDocumento = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    /**
     * Obtiene los datos del documento desde tbl_doc_plantillas (tabla unificada)
     * Centraliza el control documental en la base de datos
     * @updated 2026-01-30 - Migrado a tabla unificada tbl_doc_plantillas
     */
    protected function getDatosDocumento(): array
    {
        if ($this->datosDocumento !== null) {
            return $this->datosDocumento;
        }

        // Buscar por id_estandar en tabla unificada
        $plantilla = $this->db->table('tbl_doc_plantillas')
            ->where('id_estandar', self::ID_ESTANDAR_PRESUPUESTO)
            ->where('activo', 1)
            ->get()->getRowArray();

        // Fallback: buscar por codigo_sugerido o tipo_documento
        if (!$plantilla) {
            $plantilla = $this->db->table('tbl_doc_plantillas')
                ->where('tipo_documento', 'presupuesto_sst')
                ->where('activo', 1)
                ->get()->getRowArray();
        }

        // Segundo fallback: buscar por código base FT-SST
        if (!$plantilla) {
            $plantilla = $this->db->table('tbl_doc_plantillas')
                ->where('codigo_sugerido', 'FT-SST')
                ->where('activo', 1)
                ->get()->getRowArray();
        }

        if ($plantilla) {
            // IMPORTANTE: codigo_sugerido es el PREFIJO (ej: FT-SST)
            // El código completo con consecutivo se genera en otro lugar
            $this->datosDocumento = [
                'codigo' => $plantilla['codigo_sugerido'] ?? 'FT-SST',
                'nombre' => $plantilla['nombre'] ?? 'Asignacion de recursos para el SG-SST',
                'descripcion' => $plantilla['descripcion'] ?? '',
                'version' => $plantilla['version'] ?? '001'
            ];
        } else {
            // Valores por defecto si no existe en BD
            // NOTA: Usar solo el prefijo, NO hardcodear consecutivo
            $this->datosDocumento = [
                'codigo' => 'FT-SST',
                'nombre' => 'Asignacion de recursos para el SG-SST',
                'descripcion' => 'Presupuesto anual de recursos para el SG-SST',
                'version' => '001'
            ];
        }

        return $this->datosDocumento;
    }

    /**
     * Genera el código completo del documento con consecutivo
     * Formato: CODIGO_BASE-XXX (ej: FT-SST-001)
     *
     * @param int $idCliente ID del cliente
     * @return string Código completo del documento
     */
    protected function generarCodigoCompleto(int $idCliente): string
    {
        $codigoBase = $this->getDatosDocumento()['codigo'];

        // Contar cuántos presupuestos tiene este cliente
        $consecutivo = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->countAllResults() + 1;

        // Si ya existe un presupuesto, usar ese consecutivo
        if ($consecutivo > 1) {
            $consecutivo = 1; // El presupuesto es único por cliente/año, usar 001
        }

        return $codigoBase . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Vista principal del presupuesto
     */
    public function index($idCliente, $anio = null)
    {
        // Año por defecto: actual
        $anio = $anio ?? date('Y');

        // Obtener datos del cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener o crear presupuesto para este año
        $presupuesto = $this->getOrCreatePresupuesto($idCliente, $anio);

        // Obtener categorías maestras
        $categorias = $this->db->table('tbl_presupuesto_categorias')
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        // Obtener ítems del presupuesto con detalles mensuales
        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);

        // Calcular totales
        $totales = $this->calcularTotales($items);

        // Meses para el encabezado (desde mes_inicio)
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);

        // Obtener contexto del cliente (para emails de rep legal y delegado)
        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        return view('documentos_sst/presupuesto_sst', [
            'cliente' => $cliente,
            'presupuesto' => $presupuesto,
            'categorias' => $categorias,
            'items' => $items,
            'totales' => $totales,
            'meses' => $meses,
            'anio' => $anio,
            'anios_disponibles' => range(2026, 2030),
            'contexto' => $contexto,
            'itemsArray' => $items,
            'codigoDocumento' => $this->generarCodigoCompleto($idCliente),
            'versionDocumento' => $this->getDatosDocumento()['version'],
            'tituloDocumento' => $this->getDatosDocumento()['nombre']
        ]);
    }

    /**
     * Vista preview del presupuesto (formato vertical con botones de exportar)
     * Esta vista muestra el documento en formato vertical como PDF/Word
     * con botones para PDF, Word, Excel y Solicitar Firmas
     *
     * @updated 2026-01-31 - Integrado con sistema unificado de firmas (FirmaElectronicaController)
     */
    public function preview($idCliente, $anio = null)
    {
        $anio = $anio ?? date('Y');

        // Obtener cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener presupuesto
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->to("/documentos-sst/presupuesto/{$idCliente}/{$anio}")
                ->with('error', 'No existe presupuesto para este año. Créelo primero.');
        }

        // Obtener contexto del cliente
        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?? [];

        // Obtener datos del consultor (múltiples métodos de búsqueda)
        $consultor = null;
        // 1. Intentar desde la sesión
        $idConsultor = session('id_consultor');
        if ($idConsultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $idConsultor)
                ->get()->getRowArray();
        }
        // 2. Si no, buscar por id_cliente
        if (!$consultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_cliente', $idCliente)
                ->get()->getRowArray();
        }
        // 3. Si no, buscar por cliente.id_consultor
        if (!$consultor && !empty($cliente['id_consultor'])) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $cliente['id_consultor'])
                ->get()->getRowArray();
        }
        $consultor = $consultor ?? [];

        // Obtener items con detalles
        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);

        // Agrupar items por categoria
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = [
                    'nombre' => $item['categoria_nombre'],
                    'items' => []
                ];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }

        // Sincronizar con tbl_documentos_sst para sistema de firmas unificado
        $documento = $this->getOrCreateDocumentoSST($idCliente, $anio, $presupuesto, $totales);

        // Obtener firmas electrónicas del documento
        $firmasElectronicas = $this->obtenerFirmasElectronicas($documento['id_documento']);

        // Obtener historial de versiones para Control de Cambios
        $versiones = $this->obtenerVersiones($documento['id_documento']);

        return view('documentos_sst/presupuesto_preview', [
            'cliente' => $cliente,
            'presupuesto' => $presupuesto,
            'documento' => $documento,
            'anio' => $anio,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales' => $totales,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'firmasElectronicas' => $firmasElectronicas,
            'versiones' => $versiones,
            'codigoDocumento' => $this->generarCodigoCompleto($idCliente),
            'versionDocumento' => $this->getDatosDocumento()['version'],
            'tituloDocumento' => $this->getDatosDocumento()['nombre']
        ]);
    }

    /**
     * Obtiene el documento SST asociado al presupuesto para usar en el preview
     * Primero sincroniza para asegurar que existe, luego actualiza contenido con totales
     *
     * @param int $idCliente
     * @param int $anio
     * @param array $presupuesto
     * @param array $totales
     * @return array El documento de tbl_documentos_sst
     */
    protected function getOrCreateDocumentoSST(int $idCliente, int $anio, array $presupuesto, array $totales): array
    {
        // Primero asegurar que existe el documento
        $this->sincronizarConDocumentosSST($idCliente, $anio, $presupuesto, false);

        $tipoDocumento = 'presupuesto_sst';

        // Obtener documento
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if ($documento) {
            // Actualizar contenido con totales actuales
            $contenido = json_encode([
                'id_presupuesto' => $presupuesto['id_presupuesto'],
                'total_presupuestado' => $totales['general_presupuestado'] ?? 0,
                'total_ejecutado' => $totales['general_ejecutado'] ?? 0,
                'categorias' => array_keys($totales['por_categoria'] ?? [])
            ], JSON_UNESCAPED_UNICODE);

            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $documento['id_documento'])
                ->update([
                    'contenido' => $contenido,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Recargar documento actualizado
            $documento = $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $documento['id_documento'])
                ->get()
                ->getRowArray();
        }

        return $documento;
    }

    /**
     * Obtiene las firmas electronicas del documento desde el sistema unificado
     */
    protected function obtenerFirmasElectronicas(int $idDocumento): array
    {
        $firmasElectronicas = [];
        $solicitudesFirma = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->where('estado', 'firmado')
            ->get()
            ->getResultArray();

        foreach ($solicitudesFirma as $sol) {
            $evidencia = $this->db->table('tbl_doc_firma_evidencias')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->get()
                ->getRowArray();
            $firmasElectronicas[$sol['firmante_tipo']] = [
                'solicitud' => $sol,
                'evidencia' => $evidencia
            ];
        }

        return $firmasElectronicas;
    }

    /**
     * Obtiene el historial de versiones del documento para el Control de Cambios
     */
    protected function obtenerVersiones(int $idDocumento): array
    {
        return $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $idDocumento)
            ->orderBy('fecha_autorizacion', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene o crea presupuesto para cliente/año
     * También sincroniza con tbl_documentos_sst para el sistema de control documental
     *
     * @updated 2026-01-31 - Integrado con tbl_documentos_sst
     */
    private function getOrCreatePresupuesto($idCliente, $anio)
    {
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        $esNuevo = false;
        if (!$presupuesto) {
            // Crear nuevo presupuesto
            $this->db->table('tbl_presupuesto_sst')->insert([
                'id_cliente' => $idCliente,
                'anio' => $anio,
                'mes_inicio' => 1, // Enero por defecto
                'estado' => 'borrador'
            ]);

            $idPresupuesto = $this->db->insertID();
            $presupuesto = $this->db->table('tbl_presupuesto_sst')
                ->where('id_presupuesto', $idPresupuesto)
                ->get()->getRowArray();
            $esNuevo = true;
        }

        // Sincronizar con tbl_documentos_sst para control documental
        $this->sincronizarConDocumentosSST($idCliente, $anio, $presupuesto, $esNuevo);

        return $presupuesto;
    }

    /**
     * Sincroniza el presupuesto con tbl_documentos_sst (tabla unificada de control documental)
     * Se llama al crear presupuesto y al cambiar estado
     *
     * @param int $idCliente
     * @param int $anio
     * @param array $presupuesto
     * @param bool $esNuevo Si es true, crea el documento; si false, solo actualiza estado
     */
    protected function sincronizarConDocumentosSST(int $idCliente, int $anio, array $presupuesto, bool $esNuevo = false): void
    {
        $tipoDocumento = 'presupuesto_sst';
        $datosDoc = $this->getDatosDocumento();
        // Usar código completo con consecutivo (FT-SST-001) en lugar de solo el prefijo
        $codigoCompleto = $this->generarCodigoCompleto($idCliente);

        // Buscar documento existente
        $documentoExistente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        // Mapear estado del presupuesto a estado del documento
        $estadoPresupuesto = trim($presupuesto['estado'] ?? 'borrador');
        $estadoDocumento = match($estadoPresupuesto) {
            'aprobado' => 'firmado',
            'pendiente_firma' => 'pendiente_firma',
            'en_revision' => 'en_revision',
            'cerrado' => 'firmado',
            default => 'aprobado' // borrador = listo para firmas
        };

        // Contenido JSON (referencia al presupuesto)
        $contenido = json_encode([
            'id_presupuesto' => $presupuesto['id_presupuesto'],
            'tipo' => 'referencia_presupuesto'
        ], JSON_UNESCAPED_UNICODE);

        if ($documentoExistente) {
            // Actualizar estado y código si es necesario
            $actualizaciones = ['updated_at' => date('Y-m-d H:i:s')];

            if ($documentoExistente['estado'] !== $estadoDocumento) {
                $actualizaciones['estado'] = $estadoDocumento;

                // Si el nuevo estado es firmado o aprobado, actualizar fecha_aprobacion
                if (in_array($estadoDocumento, ['firmado', 'aprobado'])) {
                    // Usar fecha del presupuesto si existe, sino fecha actual
                    $fechaAprobacion = $presupuesto['fecha_aprobacion'] ?? date('Y-m-d H:i:s');
                    $actualizaciones['fecha_aprobacion'] = $fechaAprobacion;
                }
            }

            // Corregir código si tiene el formato antiguo (FT-SST-004) o solo prefijo
            if ($documentoExistente['codigo'] !== $codigoCompleto) {
                $actualizaciones['codigo'] = $codigoCompleto;
            }

            if (count($actualizaciones) > 1) {
                $this->db->table('tbl_documentos_sst')
                    ->where('id_documento', $documentoExistente['id_documento'])
                    ->update($actualizaciones);
            }
        } else {
            // Crear nuevo documento en tbl_documentos_sst
            $this->db->table('tbl_documentos_sst')->insert([
                'id_cliente' => $idCliente,
                'tipo_documento' => $tipoDocumento,
                'codigo' => $codigoCompleto,
                'titulo' => $datosDoc['nombre'],
                'anio' => $anio,
                'contenido' => $contenido,
                'version' => 1,
                'estado' => $estadoDocumento,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $idDocumento = $this->db->insertID();

            // Crear versión inicial
            $this->db->table('tbl_doc_versiones_sst')->insert([
                'id_documento' => $idDocumento,
                'id_cliente' => $idCliente,
                'codigo' => $codigoCompleto,
                'titulo' => $datosDoc['nombre'],
                'anio' => $anio,
                'version' => 1,
                'version_texto' => '1.0',
                'tipo_cambio' => 'mayor',
                'descripcion_cambio' => 'Elaboracion inicial del presupuesto SST',
                'contenido_snapshot' => $contenido,
                'estado' => 'vigente',
                'autorizado_por' => session()->get('nombre_usuario') ?? 'Sistema',
                'autorizado_por_id' => session()->get('id_usuario'),
                'fecha_autorizacion' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Obtiene ítems del presupuesto con detalles mensuales
     */
    private function getItemsConDetalles($idPresupuesto, $anio)
    {
        $items = $this->db->table('tbl_presupuesto_items i')
            ->select('i.*, c.nombre as categoria_nombre, c.codigo as categoria_codigo')
            ->join('tbl_presupuesto_categorias c', 'c.id_categoria = i.id_categoria')
            ->where('i.id_presupuesto', $idPresupuesto)
            ->where('i.activo', 1)
            ->orderBy('c.orden', 'ASC')
            ->orderBy('i.orden', 'ASC')
            ->get()->getResultArray();

        // Agregar detalles mensuales a cada ítem
        foreach ($items as &$item) {
            $detalles = $this->db->table('tbl_presupuesto_detalle')
                ->where('id_item', $item['id_item'])
                ->where('anio', $anio)
                ->get()->getResultArray();

            // Indexar por mes
            $item['detalles'] = [];
            $item['total_presupuestado'] = 0;
            $item['total_ejecutado'] = 0;

            foreach ($detalles as $det) {
                $item['detalles'][$det['mes']] = $det;
                $item['total_presupuestado'] += floatval($det['presupuestado']);
                $item['total_ejecutado'] += floatval($det['ejecutado']);
            }
        }

        return $items;
    }

    /**
     * Calcula totales por categoría y general
     */
    private function calcularTotales($items)
    {
        $totales = [
            'por_categoria' => [],
            'por_mes' => [],
            'general_presupuestado' => 0,
            'general_ejecutado' => 0
        ];

        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];

            // Inicializar categoría
            if (!isset($totales['por_categoria'][$cat])) {
                $totales['por_categoria'][$cat] = [
                    'presupuestado' => 0,
                    'ejecutado' => 0,
                    'por_mes' => []
                ];
            }

            // Sumar totales de categoría
            $totales['por_categoria'][$cat]['presupuestado'] += $item['total_presupuestado'];
            $totales['por_categoria'][$cat]['ejecutado'] += $item['total_ejecutado'];

            // Sumar por mes
            foreach ($item['detalles'] as $mes => $det) {
                if (!isset($totales['por_mes'][$mes])) {
                    $totales['por_mes'][$mes] = ['presupuestado' => 0, 'ejecutado' => 0];
                }
                $totales['por_mes'][$mes]['presupuestado'] += floatval($det['presupuestado']);
                $totales['por_mes'][$mes]['ejecutado'] += floatval($det['ejecutado']);

                // Por mes dentro de categoría
                if (!isset($totales['por_categoria'][$cat]['por_mes'][$mes])) {
                    $totales['por_categoria'][$cat]['por_mes'][$mes] = ['presupuestado' => 0, 'ejecutado' => 0];
                }
                $totales['por_categoria'][$cat]['por_mes'][$mes]['presupuestado'] += floatval($det['presupuestado']);
                $totales['por_categoria'][$cat]['por_mes'][$mes]['ejecutado'] += floatval($det['ejecutado']);
            }

            // General
            $totales['general_presupuestado'] += $item['total_presupuestado'];
            $totales['general_ejecutado'] += $item['total_ejecutado'];
        }

        return $totales;
    }

    /**
     * Genera array de meses para el presupuesto
     */
    private function getMesesPresupuesto($mesInicio, $anio)
    {
        $nombresMeses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        $meses = [];
        $mesActual = $mesInicio;
        $anioActual = $anio;

        for ($i = 0; $i < 12; $i++) {
            $meses[] = [
                'numero' => $mesActual,
                'anio' => $anioActual,
                'nombre' => $nombresMeses[$mesActual] . '-' . substr($anioActual, 2)
            ];

            $mesActual++;
            if ($mesActual > 12) {
                $mesActual = 1;
                $anioActual++;
            }
        }

        return $meses;
    }

    /**
     * AJAX: Agregar nuevo ítem al presupuesto
     */
    public function agregarItem()
    {
        $idPresupuesto = $this->request->getPost('id_presupuesto');
        $idCategoria = $this->request->getPost('id_categoria');
        $codigoItem = $this->request->getPost('codigo_item');
        $actividad = $this->request->getPost('actividad');
        $valorInicial = $this->request->getPost('valor_inicial');

        // Validar
        if (!$idPresupuesto || !$idCategoria || !$actividad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        // Obtener datos del presupuesto para el año
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        // Obtener último orden
        $maxOrden = $this->db->table('tbl_presupuesto_items')
            ->selectMax('orden')
            ->where('id_presupuesto', $idPresupuesto)
            ->where('id_categoria', $idCategoria)
            ->get()->getRow()->orden ?? 0;

        // Insertar ítem
        $this->db->table('tbl_presupuesto_items')->insert([
            'id_presupuesto' => $idPresupuesto,
            'id_categoria' => $idCategoria,
            'codigo_item' => $codigoItem,
            'actividad' => $actividad,
            'descripcion' => '',
            'orden' => $maxOrden + 1
        ]);

        $idItem = $this->db->insertID();

        // Obtener meses seleccionados y valor
        $mesesJson = $this->request->getPost('meses');
        $meses = json_decode($mesesJson, true) ?? [];
        $valorInicial = floatval(str_replace([',', '$', ' '], '', $valorInicial ?? '0'));
        $anioPresupuesto = intval($presupuesto['anio']);

        // Crear registro de detalle para cada mes seleccionado
        foreach ($meses as $mes) {
            $mes = intval($mes);
            if ($mes >= 1 && $mes <= 12) {
                $this->db->table('tbl_presupuesto_detalle')->insert([
                    'id_item' => $idItem,
                    'mes' => $mes,
                    'anio' => $anioPresupuesto,
                    'presupuestado' => $valorInicial,
                    'ejecutado' => 0
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'id_item' => $idItem,
            'message' => 'Ítem agregado correctamente con ' . count($meses) . ' meses'
        ]);
    }

    /**
     * AJAX: Actualizar monto (presupuestado o ejecutado)
     */
    public function actualizarMonto()
    {
        $idItem = $this->request->getPost('id_item');
        $mes = $this->request->getPost('mes');
        $anio = $this->request->getPost('anio');
        $tipo = $this->request->getPost('tipo'); // 'presupuestado' o 'ejecutado'
        $valor = $this->request->getPost('valor');

        // Validar
        if (!$idItem || !$mes || !$anio || !$tipo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        // Limpiar valor
        $valor = floatval(str_replace([',', '$', ' '], '', $valor));

        // Buscar o crear registro de detalle
        $detalle = $this->db->table('tbl_presupuesto_detalle')
            ->where('id_item', $idItem)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if ($detalle) {
            // Actualizar
            $this->db->table('tbl_presupuesto_detalle')
                ->where('id_detalle', $detalle['id_detalle'])
                ->update([$tipo => $valor]);
        } else {
            // Insertar
            $this->db->table('tbl_presupuesto_detalle')->insert([
                'id_item' => $idItem,
                'mes' => $mes,
                'anio' => $anio,
                $tipo => $valor
            ]);
        }

        // Recalcular totales del ítem
        $totalesItem = $this->db->table('tbl_presupuesto_detalle')
            ->selectSum('presupuestado', 'total_presupuestado')
            ->selectSum('ejecutado', 'total_ejecutado')
            ->where('id_item', $idItem)
            ->get()->getRowArray();

        return $this->response->setJSON([
            'success' => true,
            'total_presupuestado' => floatval($totalesItem['total_presupuestado'] ?? 0),
            'total_ejecutado' => floatval($totalesItem['total_ejecutado'] ?? 0)
        ]);
    }

    /**
     * AJAX: Eliminar ítem
     */
    public function eliminarItem()
    {
        $idItem = $this->request->getPost('id_item');

        if (!$idItem) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de ítem requerido'
            ]);
        }

        // Soft delete
        $this->db->table('tbl_presupuesto_items')
            ->where('id_item', $idItem)
            ->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ítem eliminado'
        ]);
    }

    /**
     * AJAX: Actualizar ítem (actividad/descripción)
     */
    public function actualizarItem()
    {
        $idItem = $this->request->getPost('id_item');
        $actividad = $this->request->getPost('actividad');
        $descripcion = $this->request->getPost('descripcion');

        if (!$idItem || !$actividad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        $this->db->table('tbl_presupuesto_items')
            ->where('id_item', $idItem)
            ->update([
                'actividad' => $actividad,
                'descripcion' => $descripcion
            ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ítem actualizado'
        ]);
    }

    /**
     * Cambiar estado del presupuesto
     * También sincroniza el estado con tbl_documentos_sst
     *
     * @updated 2026-01-31 - Sincroniza con tbl_documentos_sst
     */
    public function cambiarEstado($idPresupuesto, $nuevoEstado)
    {
        $estadosValidos = ['borrador', 'pendiente_firma', 'aprobado', 'cerrado'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            return redirect()->back()->with('error', 'Estado no válido');
        }

        // Obtener presupuesto para sincronizar
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        $datos = ['estado' => $nuevoEstado];

        if ($nuevoEstado === 'aprobado') {
            $datos['fecha_aprobacion'] = date('Y-m-d H:i:s');
            $datos['firmado_por'] = $this->session->get('nombre_usuario') ?? 'Usuario';
        }

        // Si vuelve a borrador, limpiar tokens
        if ($nuevoEstado === 'borrador') {
            $datos['token_firma'] = null;
            $datos['token_expiracion'] = null;
        }

        $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->update($datos);

        // Sincronizar estado con tbl_documentos_sst
        $presupuesto['estado'] = $nuevoEstado; // Actualizar para sincronizar
        $this->sincronizarConDocumentosSST(
            $presupuesto['id_cliente'],
            $presupuesto['anio'],
            $presupuesto,
            false
        );

        return redirect()->back()->with('success', 'Estado actualizado a ' . $nuevoEstado);
    }

    /**
     * Exportar a PDF (formato vertical con firmas de aprobacion)
     * @updated 2026-01-31 - Incluye firmas electrónicas del sistema unificado
     * @updated 2026-02-02 - Incluye consultor para firma Elaboró
     */
    public function exportarPdf($idCliente, $anio)
    {
        // Obtener datos
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        // Obtener contexto del cliente (para nombres de firmantes)
        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?? [];

        // Obtener datos del consultor (múltiples métodos de búsqueda)
        $consultor = null;

        // 1. Intentar desde la sesión
        $idConsultor = session('id_consultor');
        if ($idConsultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $idConsultor)
                ->get()->getRowArray();
        }

        // 2. Si no, buscar por id_cliente
        if (!$consultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_cliente', $idCliente)
                ->get()->getRowArray();
        }

        // 3. Si no, buscar por cliente.id_consultor
        if (!$consultor && !empty($cliente['id_consultor'])) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $cliente['id_consultor'])
                ->get()->getRowArray();
        }

        $consultor = $consultor ?? [];

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);

        // Agrupar ítems por categoría
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = [
                    'nombre' => $item['categoria_nombre'],
                    'items' => []
                ];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }

        // Obtener documento de tbl_documentos_sst para firmas electrónicas
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'presupuesto_sst')
            ->where('anio', $anio)
            ->get()->getRowArray();

        // Obtener firmas electrónicas y versiones si existe el documento
        $firmasElectronicas = [];
        $versiones = [];
        if ($documento) {
            $firmasElectronicas = $this->obtenerFirmasElectronicas($documento['id_documento']);
            $versiones = $this->obtenerVersiones($documento['id_documento']);
        }

        // Convertir logo a base64 para DOMPDF
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        $html = view('documentos_sst/presupuesto_pdf', [
            'cliente' => $cliente,
            'presupuesto' => $presupuesto,
            'documento' => $documento,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales' => $totales,
            'meses' => $meses,
            'anio' => $anio,
            'logoBase64' => $logoBase64,
            'firmasElectronicas' => $firmasElectronicas,
            'versiones' => $versiones,
            'codigoDocumento' => $this->generarCodigoCompleto($idCliente),
            'versionDocumento' => $this->getDatosDocumento()['version'],
            'tituloDocumento' => $this->getDatosDocumento()['nombre']
        ]);

        // Generar PDF con DOMPDF - formato VERTICAL (portrait) carta
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = $this->getDatosDocumento()['codigo'] . "_Presupuesto_{$cliente['nombre_cliente']}_{$anio}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Exportar a Word (formato vertical con firmas de aprobacion)
     * @updated 2026-01-31 - Incluye firmas electrónicas del sistema unificado
     */
    public function exportarWord($idCliente, $anio)
    {
        // Obtener datos
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        // Obtener contexto del cliente (para nombres de firmantes)
        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?? [];

        // Obtener datos del consultor (múltiples métodos de búsqueda)
        $consultor = null;

        // 1. Intentar desde la sesión
        $idConsultor = session('id_consultor');
        if ($idConsultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $idConsultor)
                ->get()->getRowArray();
        }

        // 2. Si no, buscar por id_cliente
        if (!$consultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_cliente', $idCliente)
                ->get()->getRowArray();
        }

        // 3. Si no, buscar por cliente.id_consultor
        if (!$consultor && !empty($cliente['id_consultor'])) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $cliente['id_consultor'])
                ->get()->getRowArray();
        }

        $consultor = $consultor ?? [];

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);

        // Agrupar ítems por categoría
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = [
                    'nombre' => $item['categoria_nombre'],
                    'items' => []
                ];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }

        // Obtener documento de tbl_documentos_sst para firmas electrónicas
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'presupuesto_sst')
            ->where('anio', $anio)
            ->get()->getRowArray();

        // Obtener firmas electrónicas y versiones si existe el documento
        $firmasElectronicas = [];
        $versiones = [];
        if ($documento) {
            $firmasElectronicas = $this->obtenerFirmasElectronicas($documento['id_documento']);
            $versiones = $this->obtenerVersiones($documento['id_documento']);
        }

        // Convertir logo a base64 para Word
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        $html = view('documentos_sst/presupuesto_word', [
            'cliente' => $cliente,
            'presupuesto' => $presupuesto,
            'documento' => $documento,
            'contexto' => $contexto,
            'consultor' => $consultor,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales' => $totales,
            'meses' => $meses,
            'anio' => $anio,
            'logoBase64' => $logoBase64,
            'firmasElectronicas' => $firmasElectronicas,
            'versiones' => $versiones,
            'codigoDocumento' => $this->generarCodigoCompleto($idCliente),
            'versionDocumento' => $this->getDatosDocumento()['version'],
            'tituloDocumento' => $this->getDatosDocumento()['nombre']
        ]);

        $filename = $this->getDatosDocumento()['codigo'] . "_Presupuesto_{$cliente['nombre_cliente']}_{$anio}.doc";

        return $this->response
            ->setHeader('Content-Type', 'application/msword')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($html);
    }

    /**
     * Exportar a Excel (matriz completa con todos los meses)
     */
    public function exportarExcel($idCliente, $anio)
    {
        // Obtener datos
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);

        // Agrupar items por categoria
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = [
                    'nombre' => $item['categoria_nombre'],
                    'items' => []
                ];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }

        // Crear hoja de calculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presupuesto ' . $anio);

        // Estilos
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a5f7a']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $subHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2980b9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $categoriaStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e8f4f8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $subtotalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'd4edda']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a5f7a']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        // Encabezado del documento
        $sheet->setCellValue('A1', 'SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO');
        $sheet->mergeCells('A1:' . $this->getColLetter(3 + count($meses) * 2 + 1) . '1');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->setCellValue('A2', $this->getDatosDocumento()['nombre'] . ' - AÑO ' . $anio);
        $sheet->mergeCells('A2:' . $this->getColLetter(3 + count($meses) * 2 + 1) . '2');
        $sheet->getStyle('A2')->applyFromArray($headerStyle);

        $sheet->setCellValue('A3', 'Empresa: ' . $cliente['nombre_cliente'] . ' | NIT: ' . ($cliente['nit'] ?? 'N/A') . ' | Codigo: ' . $this->getDatosDocumento()['codigo']);
        $sheet->mergeCells('A3:' . $this->getColLetter(3 + count($meses) * 2 + 1) . '3');

        // Encabezados de columnas
        $row = 5;
        $sheet->setCellValue('A' . $row, 'Item');
        $sheet->setCellValue('B' . $row, 'Actividad');
        $sheet->setCellValue('C' . $row, 'Descripcion');

        $col = 4;
        foreach ($meses as $mes) {
            $sheet->setCellValue($this->getColLetter($col) . $row, $mes['nombre']);
            $sheet->mergeCells($this->getColLetter($col) . $row . ':' . $this->getColLetter($col + 1) . $row);
            $col += 2;
        }
        $sheet->setCellValue($this->getColLetter($col) . $row, 'TOTAL');
        $sheet->mergeCells($this->getColLetter($col) . $row . ':' . $this->getColLetter($col + 1) . $row);

        $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // Sub-encabezados (Presup./Ejec.)
        $row++;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');

        $col = 4;
        foreach ($meses as $mes) {
            $sheet->setCellValue($this->getColLetter($col) . $row, 'Presup.');
            $sheet->setCellValue($this->getColLetter($col + 1) . $row, 'Ejec.');
            $col += 2;
        }
        $sheet->setCellValue($this->getColLetter($col) . $row, 'Presup.');
        $sheet->setCellValue($this->getColLetter($col + 1) . $row, 'Ejec.');

        $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($subHeaderStyle);

        // Datos
        $row++;
        foreach ($itemsPorCategoria as $codigoCat => $categoria) {
            // Fila de categoria
            $sheet->setCellValue('A' . $row, $codigoCat . '. ' . $categoria['nombre']);
            $sheet->mergeCells('A' . $row . ':' . $this->getColLetter(3 + count($meses) * 2 + 1) . $row);
            $sheet->getStyle('A' . $row . ':' . $this->getColLetter(3 + count($meses) * 2 + 1) . $row)->applyFromArray($categoriaStyle);
            $row++;

            // Items de la categoria
            foreach ($categoria['items'] as $item) {
                $sheet->setCellValue('A' . $row, $item['codigo_item']);
                $sheet->setCellValue('B' . $row, $item['actividad']);
                $sheet->setCellValue('C' . $row, $item['descripcion'] ?? '');

                $col = 4;
                foreach ($meses as $mes) {
                    $detalle = $item['detalles'][$mes['numero']] ?? null;
                    $presup = $detalle ? floatval($detalle['presupuestado']) : 0;
                    $ejec = $detalle ? floatval($detalle['ejecutado']) : 0;

                    $sheet->setCellValue($this->getColLetter($col) . $row, $presup);
                    $sheet->setCellValue($this->getColLetter($col + 1) . $row, $ejec);
                    $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $col += 2;
                }

                $sheet->setCellValue($this->getColLetter($col) . $row, $item['total_presupuestado']);
                $sheet->setCellValue($this->getColLetter($col + 1) . $row, $item['total_ejecutado']);
                $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');

                $row++;
            }

            // Subtotal de categoria
            $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0, 'por_mes' => []];
            $sheet->setCellValue('A' . $row, 'Subtotal ' . $codigoCat);
            $sheet->mergeCells('A' . $row . ':C' . $row);

            $col = 4;
            foreach ($meses as $mes) {
                $totMes = $totCat['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
                $sheet->setCellValue($this->getColLetter($col) . $row, $totMes['presupuestado']);
                $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totMes['ejecutado']);
                $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $col += 2;
            }
            $sheet->setCellValue($this->getColLetter($col) . $row, $totCat['presupuestado']);
            $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totCat['ejecutado']);
            $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($subtotalStyle);
            $row++;
        }

        // Total general
        $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
        $sheet->mergeCells('A' . $row . ':C' . $row);

        $col = 4;
        foreach ($meses as $mes) {
            $totMes = $totales['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
            $sheet->setCellValue($this->getColLetter($col) . $row, $totMes['presupuestado']);
            $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totMes['ejecutado']);
            $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $col += 2;
        }
        $sheet->setCellValue($this->getColLetter($col) . $row, $totales['general_presupuestado']);
        $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totales['general_ejecutado']);
        $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($totalStyle);

        // Ajustar anchos de columna
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        for ($i = 4; $i <= $col + 1; $i++) {
            $sheet->getColumnDimension($this->getColLetter($i))->setWidth(12);
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $filename = $this->getDatosDocumento()['codigo'] . "_Presupuesto_{$cliente['nombre_cliente']}_{$anio}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Convierte numero de columna a letra (1=A, 2=B, etc)
     */
    private function getColLetter($num)
    {
        $letter = '';
        while ($num > 0) {
            $num--;
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intval($num / 26);
        }
        return $letter;
    }

    /**
     * AJAX: Obtener totales actualizados
     */
    public function getTotales($idPresupuesto)
    {
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return $this->response->setJSON(['success' => false]);
        }

        $items = $this->getItemsConDetalles($idPresupuesto, $presupuesto['anio']);
        $totales = $this->calcularTotales($items);

        return $this->response->setJSON([
            'success' => true,
            'totales' => $totales
        ]);
    }

    /**
     * Copiar presupuesto de otro año
     */
    public function copiarDeAnio($idCliente, $anioOrigen, $anioDestino)
    {
        // Obtener presupuesto origen
        $presupuestoOrigen = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anioOrigen)
            ->get()->getRowArray();

        if (!$presupuestoOrigen) {
            return redirect()->back()->with('error', 'Presupuesto origen no encontrado');
        }

        // Verificar que no exista el destino o esté vacío
        $presupuestoDestino = $this->getOrCreatePresupuesto($idCliente, $anioDestino);

        // Obtener ítems origen
        $itemsOrigen = $this->db->table('tbl_presupuesto_items')
            ->where('id_presupuesto', $presupuestoOrigen['id_presupuesto'])
            ->where('activo', 1)
            ->get()->getResultArray();

        foreach ($itemsOrigen as $item) {
            // Copiar ítem
            $this->db->table('tbl_presupuesto_items')->insert([
                'id_presupuesto' => $presupuestoDestino['id_presupuesto'],
                'id_categoria' => $item['id_categoria'],
                'codigo_item' => $item['codigo_item'],
                'actividad' => $item['actividad'],
                'descripcion' => $item['descripcion'],
                'orden' => $item['orden']
            ]);

            $nuevoIdItem = $this->db->insertID();

            // Copiar detalles (solo presupuestado, no ejecutado)
            $detallesOrigen = $this->db->table('tbl_presupuesto_detalle')
                ->where('id_item', $item['id_item'])
                ->get()->getResultArray();

            foreach ($detallesOrigen as $det) {
                $this->db->table('tbl_presupuesto_detalle')->insert([
                    'id_item' => $nuevoIdItem,
                    'mes' => $det['mes'],
                    'anio' => $anioDestino,
                    'presupuestado' => $det['presupuestado'],
                    'ejecutado' => 0 // Ejecutado inicia en 0
                ]);
            }
        }

        return redirect()->to("/documentos-sst/presupuesto/{$idCliente}/{$anioDestino}")
            ->with('success', "Presupuesto copiado de {$anioOrigen} a {$anioDestino}");
    }

    /**
     * Enviar presupuesto para aprobacion via email (SendGrid)
     * Acepta campos del formulario: id_presupuesto, email_representante, email_responsable
     */
    public function enviarAprobacion()
    {
        $idPresupuesto = $this->request->getPost('id_presupuesto');
        $idCliente = $this->request->getPost('id_cliente');
        $anio = $this->request->getPost('anio');

        // Emails del formulario (override)
        $emailRepLegalForm = $this->request->getPost('email_representante');
        $emailResponsableForm = $this->request->getPost('email_responsable');

        // Obtener presupuesto
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        // Obtener cliente
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $presupuesto['id_cliente'])
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener contexto del cliente
        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $presupuesto['id_cliente'])
            ->get()->getRowArray();

        // Usar emails del formulario si se proporcionaron, sino usar del contexto
        $emailRepLegal = !empty($emailRepLegalForm) ? $emailRepLegalForm : ($contexto['representante_legal_email'] ?? $cliente['email'] ?? null);
        $nombreRepLegal = $contexto['representante_legal_nombre'] ?? $cliente['representante_legal'] ?? 'Representante Legal';

        $emailResponsable = !empty($emailResponsableForm) ? $emailResponsableForm : ($contexto['responsable_sst_email'] ?? $contexto['delegado_sst_email'] ?? null);
        $nombreResponsable = $contexto['responsable_sst_nombre'] ?? $contexto['delegado_sst_nombre'] ?? 'Responsable SST';

        if (!$emailRepLegal) {
            return redirect()->back()->with('error', 'El email del Representante Legal es requerido');
        }

        // Generar token unico para firma
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Guardar token en presupuesto
        $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->update([
                'estado' => 'pendiente_firma',
                'token_firma' => $token,
                'token_expiracion' => $expiracion
            ]);

        // Obtener datos del presupuesto para el email
        $items = $this->getItemsConDetalles($idPresupuesto, $presupuesto['anio']);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $presupuesto['anio']);

        // URL del dashboard del cliente
        $urlDashboard = base_url('client/aprobaciones-pendientes');

        // URL de firma directa
        $urlFirma = base_url("presupuesto/aprobar/{$token}");

        // Enviar email al Representante Legal
        $enviado = $this->enviarEmailSendGrid(
            $emailRepLegal,
            $nombreRepLegal,
            $cliente,
            $presupuesto,
            $items,
            $totales,
            $meses,
            $urlFirma,
            'Se requiere su aprobacion y firma digital del presupuesto SST.',
            false,
            $urlDashboard
        );

        if (!$enviado) {
            // Revertir a borrador si falla el envio
            $this->db->table('tbl_presupuesto_sst')
                ->where('id_presupuesto', $idPresupuesto)
                ->update([
                    'estado' => 'borrador',
                    'token_firma' => null,
                    'token_expiracion' => null
                ]);
            return redirect()->back()->with('error', 'Error al enviar el correo. Verifique la configuracion de SendGrid.');
        }

        // Enviar copia al Responsable SST si hay email
        if (!empty($emailResponsable)) {
            $this->enviarEmailSendGrid(
                $emailResponsable,
                $nombreResponsable,
                $cliente,
                $presupuesto,
                $items,
                $totales,
                $meses,
                $urlFirma,
                'El Representante Legal debe aprobar este presupuesto. Se le envia copia informativa.',
                true, // Es copia informativa
                $urlDashboard
            );
        }

        return redirect()->to("documentos-sst/presupuesto/preview/{$cliente['id_cliente']}/{$presupuesto['anio']}")
            ->with('success', 'Solicitud de firma enviada correctamente a ' . $emailRepLegal);
    }

    /**
     * Envia email con SendGrid
     */
    private function enviarEmailSendGrid($email, $nombre, $cliente, $presupuesto, $items, $totales, $meses, $urlFirma, $mensaje, $esCopia = false, $urlDashboard = null)
    {
        // Usar env() de CodeIgniter para leer .env
        $apiKey = env('SENDGRID_API_KEY', '');

        if (empty($apiKey)) {
            log_message('error', 'SendGrid API Key no configurada');
            return false;
        }

        // Construir HTML del email
        $htmlEmail = view('documentos_sst/email_presupuesto', [
            'nombre' => $nombre,
            'cliente' => $cliente,
            'presupuesto' => $presupuesto,
            'items' => $items,
            'totales' => $totales,
            'meses' => $meses,
            'urlFirma' => $urlFirma,
            'urlDashboard' => $urlDashboard,
            'mensaje' => $mensaje,
            'esCopia' => $esCopia,
            'anio' => $presupuesto['anio']
        ]);

        $subject = $esCopia
            ? "[Copia] Presupuesto SST {$presupuesto['anio']} - {$cliente['nombre_cliente']}"
            : "Solicitud de Aprobacion: Presupuesto SST {$presupuesto['anio']} - {$cliente['nombre_cliente']}";

        // Email remitente (debe estar verificado en SendGrid)
        $fromEmail = env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com');
        $fromName = env('SENDGRID_FROM_NAME', 'Enterprise SST');

        // Datos para SendGrid API
        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $email, 'name' => $nombre]],
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlEmail]
            ]
        ];

        // Llamar API de SendGrid
        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para desarrollo local

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log para debug
        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('error', "SendGrid Error - HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }

        // SendGrid retorna 202 si fue aceptado
        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Pagina de firma digital del presupuesto
     */
    public function paginaFirma($token)
    {
        // Buscar presupuesto por token (sin filtrar estado para mejor debugging)
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('token_firma', $token)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return view('documentos_sst/firma_error', [
                'mensaje' => 'El enlace de firma no es valido o ya fue utilizado.'
            ]);
        }

        // Obtener estado (manejar null, empty string, etc)
        $estadoActual = trim($presupuesto['estado'] ?? '');
        if (empty($estadoActual)) {
            $estadoActual = 'borrador'; // Default si esta vacio
        }

        // Verificar estado
        if ($estadoActual === 'aprobado') {
            // Ya fue aprobado, redirigir a consulta
            $tokenConsulta = $presupuesto['token_consulta'] ?? $token;
            return redirect()->to(base_url("presupuesto/consulta/{$tokenConsulta}"));
        }

        if ($estadoActual === 'borrador') {
            return view('documentos_sst/firma_error', [
                'mensaje' => 'Este presupuesto aun esta en borrador. El administrador debe enviarlo para aprobacion.'
            ]);
        }

        if ($estadoActual !== 'pendiente_firma') {
            return view('documentos_sst/firma_error', [
                'mensaje' => "El presupuesto no esta disponible para firma. Estado actual: {$estadoActual}"
            ]);
        }

        // Verificar expiracion
        if (!empty($presupuesto['token_expiracion']) && strtotime($presupuesto['token_expiracion']) < time()) {
            return view('documentos_sst/firma_error', [
                'mensaje' => 'El enlace de firma ha expirado. Solicite un nuevo enlace.'
            ]);
        }

        // Obtener datos
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $presupuesto['id_cliente'])
            ->get()->getRowArray();

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $presupuesto['anio']);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $presupuesto['anio']);

        // Agrupar items por categoria
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = [
                    'nombre' => $item['categoria_nombre'],
                    'items' => []
                ];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }

        return view('documentos_sst/presupuesto_firma', [
            'presupuesto' => $presupuesto,
            'cliente' => $cliente,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales' => $totales,
            'meses' => $meses,
            'anio' => $presupuesto['anio'],
            'token' => $token
        ]);
    }

    /**
     * Procesar firma digital del presupuesto
     */
    public function procesarFirma()
    {
        $token = $this->request->getPost('token');
        $firmaNombre = $this->request->getPost('firma_nombre');
        $firmaCedula = $this->request->getPost('firma_cedula');
        $firmaImagen = $this->request->getPost('firma_imagen'); // Base64 del canvas

        // Validar token
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('token_firma', $token)
            ->where('estado', 'pendiente_firma')
            ->get()->getRowArray();

        if (!$presupuesto) {
            return $this->response->setJSON(['success' => false, 'message' => 'Token no valido']);
        }

        // Verificar expiracion
        if (strtotime($presupuesto['token_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El enlace ha expirado']);
        }

        // Guardar imagen de firma
        $rutaFirma = null;
        if ($firmaImagen) {
            $firmaData = explode(',', $firmaImagen);
            $firmaDecoded = base64_decode(end($firmaData));
            $nombreArchivo = 'firma_presupuesto_' . $presupuesto['id_presupuesto'] . '_' . time() . '.png';
            $rutaFirma = 'uploads/firmas/' . $nombreArchivo;

            // Crear directorio si no existe
            if (!is_dir(FCPATH . 'uploads/firmas')) {
                mkdir(FCPATH . 'uploads/firmas', 0755, true);
            }

            file_put_contents(FCPATH . $rutaFirma, $firmaDecoded);
        }

        // Actualizar presupuesto
        $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $presupuesto['id_presupuesto'])
            ->update([
                'estado' => 'aprobado',
                'fecha_aprobacion' => date('Y-m-d H:i:s'),
                'firmado_por' => $firmaNombre,
                'cedula_firmante' => $firmaCedula,
                'firma_imagen' => $rutaFirma,
                'ip_firma' => $this->request->getIPAddress(),
                'token_firma' => null,
                'token_expiracion' => null
            ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Presupuesto aprobado y firmado correctamente'
        ]);
    }

    /**
     * Vista de solo lectura del presupuesto para clientes
     * Acceso mediante token de consulta
     */
    public function vistaCliente($token)
    {
        // Buscar presupuesto por token de consulta
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('token_consulta', $token)
            ->get()->getRowArray();

        // Si no hay token de consulta, buscar por token de firma (para presupuestos ya aprobados)
        if (!$presupuesto) {
            $presupuesto = $this->db->table('tbl_presupuesto_sst')
                ->where('token_firma', $token)
                ->get()->getRowArray();
        }

        // Tambien permitir acceso por ID + NIT del cliente (como alternativa)
        if (!$presupuesto && strlen($token) < 20) {
            // Formato: idPresupuesto-nitCliente
            $parts = explode('-', $token);
            if (count($parts) >= 2) {
                $idPresupuesto = intval($parts[0]);
                $nitCliente = $parts[1];

                $presupuesto = $this->db->table('tbl_presupuesto_sst p')
                    ->select('p.*')
                    ->join('tbl_clientes c', 'c.id_cliente = p.id_cliente')
                    ->where('p.id_presupuesto', $idPresupuesto)
                    ->where('c.nit', $nitCliente)
                    ->get()->getRowArray();
            }
        }

        if (!$presupuesto) {
            return view('documentos_sst/firma_error', [
                'mensaje' => 'El enlace de consulta no es valido o el presupuesto no existe.'
            ]);
        }

        // Obtener datos
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $presupuesto['id_cliente'])
            ->get()->getRowArray();

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $presupuesto['anio']);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $presupuesto['anio']);

        // Agrupar items por categoria
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = [
                    'nombre' => $item['categoria_nombre'],
                    'items' => []
                ];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }

        return view('documentos_sst/presupuesto_cliente', [
            'presupuesto' => $presupuesto,
            'cliente' => $cliente,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales' => $totales,
            'meses' => $meses,
            'anio' => $presupuesto['anio']
        ]);
    }

    /**
     * Generar token de consulta para compartir con el cliente
     */
    public function generarTokenConsulta()
    {
        $idPresupuesto = $this->request->getPost('id_presupuesto');

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return $this->response->setJSON(['success' => false, 'message' => 'Presupuesto no encontrado']);
        }

        // Generar token si no existe
        $tokenConsulta = $presupuesto['token_consulta'];
        if (empty($tokenConsulta)) {
            $tokenConsulta = bin2hex(random_bytes(16)); // Token mas corto para consulta

            $this->db->table('tbl_presupuesto_sst')
                ->where('id_presupuesto', $idPresupuesto)
                ->update(['token_consulta' => $tokenConsulta]);
        }

        $urlConsulta = base_url("presupuesto/consulta/{$tokenConsulta}");

        return $this->response->setJSON([
            'success' => true,
            'token' => $tokenConsulta,
            'url' => $urlConsulta
        ]);
    }

    /**
     * Crear nueva versión del documento presupuesto SST
     * Solo se puede crear nueva versión si el documento ya fue firmado/aprobado
     *
     * @param int $idCliente
     * @param int $anio
     * @return \CodeIgniter\HTTP\Response
     */
    public function crearNuevaVersion($idCliente, $anio)
    {
        // Obtener documento de tbl_documentos_sst
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'presupuesto_sst')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$documento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Documento no encontrado'
            ]);
        }

        // Solo permitir nueva versión si está firmado o aprobado
        if (!in_array($documento['estado'], ['firmado', 'aprobado'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo se puede crear nueva versión de documentos firmados o aprobados. Estado actual: ' . $documento['estado']
            ]);
        }

        // Obtener presupuesto para el contenido
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        if (!$presupuesto) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Presupuesto no encontrado'
            ]);
        }

        // Obtener items y calcular totales para el snapshot
        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);

        // Calcular nueva versión
        $versionActual = (int)$documento['version'];
        $nuevaVersion = $versionActual + 1;
        $versionTexto = $nuevaVersion . '.0';

        // Obtener descripción del cambio del request
        $descripcionCambio = $this->request->getPost('descripcion_cambio')
            ?? 'Actualizacion del presupuesto SST';

        // Nuevo contenido con totales actualizados
        $nuevoContenido = json_encode([
            'id_presupuesto' => $presupuesto['id_presupuesto'],
            'total_presupuestado' => $totales['general_presupuestado'] ?? 0,
            'total_ejecutado' => $totales['general_ejecutado'] ?? 0,
            'categorias' => array_keys($totales['por_categoria'] ?? []),
            'version' => $nuevaVersion
        ], JSON_UNESCAPED_UNICODE);

        // Actualizar documento principal - queda pendiente de firma
        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $documento['id_documento'])
            ->update([
                'contenido' => $nuevoContenido,
                'version' => $nuevaVersion,
                'estado' => 'pendiente_firma',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Marcar versión anterior como histórico
        $this->db->table('tbl_doc_versiones_sst')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado', 'vigente')
            ->update(['estado' => 'historico']);

        // Crear nueva versión en estado pendiente_firma
        $session = session();
        $datosDoc = $this->getDatosDocumento();

        $this->db->table('tbl_doc_versiones_sst')->insert([
            'id_documento' => $documento['id_documento'],
            'id_cliente' => $idCliente,
            'codigo' => $documento['codigo'],
            'titulo' => $datosDoc['nombre'],
            'anio' => $anio,
            'version' => $nuevaVersion,
            'version_texto' => $versionTexto,
            'tipo_cambio' => 'mayor',
            'descripcion_cambio' => $descripcionCambio,
            'contenido_snapshot' => $nuevoContenido,
            'estado' => 'pendiente_firma',
            'autorizado_por' => $session->get('nombre_usuario') ?? 'Sistema',
            'autorizado_por_id' => $session->get('id_usuario'),
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Actualizar estado del presupuesto en su tabla propia
        $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $presupuesto['id_presupuesto'])
            ->update([
                'estado' => 'pendiente_firma',
                'fecha_aprobacion' => null
            ]);

        // Invalidar firmas anteriores pendientes (el documento cambió)
        $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->where('estado !=', 'firmado')
            ->update(['estado' => 'cancelada']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento actualizado a versión ' . $versionTexto . '. Pendiente de firma.',
            'nueva_version' => $versionTexto,
            'id_documento' => $documento['id_documento']
        ]);
    }
}
