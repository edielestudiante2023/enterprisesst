<?php

namespace App\Services;

/**
 * Servicio para auto-configuración de las tablas y datos de estándares mínimos
 * Este servicio detecta automáticamente si faltan tablas o datos y los crea
 */
class EstandaresSetupService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Verifica y configura todo lo necesario para el módulo de estándares
     * @return array Estado de la configuración
     */
    public function verificarYConfigurar(): array
    {
        $resultado = [
            'tablas_creadas' => [],
            'datos_insertados' => [],
            'errores' => [],
            'listo' => false
        ];

        try {
            // 1. Verificar/crear tabla de estándares mínimos
            if (!$this->tablaExiste('tbl_estandares_minimos')) {
                $this->crearTablaEstandaresMinimos();
                $resultado['tablas_creadas'][] = 'tbl_estandares_minimos';
            }

            // 2. Verificar/crear tabla de cliente_estandares
            if (!$this->tablaExiste('tbl_cliente_estandares')) {
                $this->crearTablaClienteEstandares();
                $resultado['tablas_creadas'][] = 'tbl_cliente_estandares';
            }

            // 3. Verificar/crear tabla de transiciones
            if (!$this->tablaExiste('tbl_cliente_transiciones')) {
                $this->crearTablaTransiciones();
                $resultado['tablas_creadas'][] = 'tbl_cliente_transiciones';
            }

            // 4. Verificar si hay datos en estándares mínimos
            $countEstandares = $this->db->table('tbl_estandares_minimos')->countAll();
            if ($countEstandares < 60) {
                $this->insertarEstandaresMinimos();
                $resultado['datos_insertados'][] = '60 estándares mínimos Res. 0312/2019';
            }

            $resultado['listo'] = true;

        } catch (\Exception $e) {
            $resultado['errores'][] = $e->getMessage();
            log_message('error', 'EstandaresSetupService: ' . $e->getMessage());
        }

        return $resultado;
    }

    /**
     * Verifica si una tabla existe
     */
    protected function tablaExiste(string $tabla): bool
    {
        $query = $this->db->query("SHOW TABLES LIKE '{$tabla}'");
        return $query->getNumRows() > 0;
    }

    /**
     * Crea la tabla tbl_estandares_minimos
     */
    protected function crearTablaEstandaresMinimos(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tbl_estandares_minimos` (
            `id_estandar` INT NOT NULL AUTO_INCREMENT,
            `item` VARCHAR(10) NOT NULL COMMENT 'Número del estándar: 1.1.1, 2.1.1, etc.',
            `nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del estándar',
            `ciclo_phva` ENUM('PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR') NOT NULL,
            `categoria` ENUM('I', 'II', 'III', 'IV', 'V', 'VI', 'VII') NOT NULL,
            `categoria_nombre` VARCHAR(100) NOT NULL,
            `peso_porcentual` DECIMAL(4,2) NOT NULL,
            `aplica_7` TINYINT(1) NOT NULL DEFAULT 0,
            `aplica_21` TINYINT(1) NOT NULL DEFAULT 0,
            `aplica_60` TINYINT(1) NOT NULL DEFAULT 1,
            `documentos_sugeridos` TEXT NULL,
            `activo` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_estandar`),
            UNIQUE KEY `uk_item` (`item`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }

    /**
     * Crea la tabla tbl_cliente_estandares
     */
    protected function crearTablaClienteEstandares(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tbl_cliente_estandares` (
            `id_cliente_estandar` INT NOT NULL AUTO_INCREMENT,
            `id_cliente` INT NOT NULL,
            `id_estandar` INT NOT NULL,
            `estado` ENUM('no_aplica', 'pendiente', 'en_proceso', 'cumple', 'no_cumple') NOT NULL DEFAULT 'pendiente',
            `calificacion` DECIMAL(5,2) NULL DEFAULT 0,
            `fecha_cumplimiento` DATE NULL,
            `evidencia_path` VARCHAR(255) NULL,
            `observaciones` TEXT NULL,
            `verificado_por` INT NULL,
            `fecha_verificacion` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_cliente_estandar`),
            UNIQUE KEY `uk_cliente_estandar` (`id_cliente`, `id_estandar`),
            KEY `idx_cliente` (`id_cliente`),
            KEY `idx_estandar` (`id_estandar`),
            KEY `idx_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }

    /**
     * Crea la tabla tbl_cliente_transiciones
     */
    protected function crearTablaTransiciones(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tbl_cliente_transiciones` (
            `id_transicion` INT NOT NULL AUTO_INCREMENT,
            `id_cliente` INT NOT NULL,
            `nivel_anterior` TINYINT NOT NULL,
            `nivel_nuevo` TINYINT NOT NULL,
            `motivo` VARCHAR(255) NOT NULL,
            `fecha_deteccion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `fecha_completado` DATETIME NULL,
            `estado` ENUM('detectado', 'en_proceso', 'completado') NOT NULL DEFAULT 'detectado',
            `aplicado_por` INT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_transicion`),
            KEY `idx_cliente` (`id_cliente`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }

    /**
     * Inserta los 60 estándares mínimos de la Resolución 0312/2019
     */
    protected function insertarEstandaresMinimos(): void
    {
        // Limpiar tabla primero
        $this->db->table('tbl_estandares_minimos')->truncate();

        $estandares = $this->getEstandaresData();

        foreach ($estandares as $estandar) {
            $this->db->table('tbl_estandares_minimos')->insert($estandar);
        }
    }

    /**
     * Inicializa los 60 estándares para un cliente
     * Los que aplican según el nivel quedan como 'pendiente'
     * Los que no aplican quedan como 'no_aplica'
     *
     * Se necesitan los 60 registros para:
     * - Calcular correctamente el % de cumplimiento según fórmula del Ministerio
     * - Permitir transiciones de nivel (7→21→60) sin perder datos
     * - Mantener trazabilidad completa
     *
     * @param int $idCliente
     * @param int $nivelEstandares 7, 21 o 60
     * @return array
     */
    public function inicializarEstandaresCliente(int $idCliente, int $nivelEstandares = 60): array
    {
        $resultado = [
            'success' => false,
            'total_registros' => 0,
            'aplicables' => 0,
            'mensaje' => ''
        ];

        try {
            // Verificar que existan los estándares base
            $countBase = $this->db->table('tbl_estandares_minimos')->countAll();
            if ($countBase < 60) {
                $this->insertarEstandaresMinimos();
            }

            // Verificar si ya tiene estándares
            $existentes = $this->db->table('tbl_cliente_estandares')
                ->where('id_cliente', $idCliente)
                ->countAllResults();

            if ($existentes > 0) {
                $resultado['mensaje'] = 'El cliente ya tiene estándares inicializados';
                $resultado['total_registros'] = $existentes;
                $resultado['success'] = true;
                return $resultado;
            }

            // Determinar campo de aplicabilidad según nivel
            $campoAplica = match($nivelEstandares) {
                7 => 'aplica_7',
                21 => 'aplica_21',
                default => 'aplica_60'
            };

            // Obtener TODOS los estándares
            $estandares = $this->db->table('tbl_estandares_minimos')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            // Insertar los 60 registros
            $insertados = 0;
            $aplicables = 0;
            foreach ($estandares as $estandar) {
                // Determinar si aplica según el nivel del cliente
                $aplica = (bool) $estandar[$campoAplica];

                $data = [
                    'id_cliente' => $idCliente,
                    'id_estandar' => $estandar['id_estandar'],
                    'estado' => $aplica ? 'pendiente' : 'no_aplica',
                    'calificacion' => 0
                ];

                $this->db->table('tbl_cliente_estandares')->insert($data);
                $insertados++;
                if ($aplica) {
                    $aplicables++;
                }
            }

            $resultado['success'] = true;
            $resultado['total_registros'] = $insertados;
            $resultado['aplicables'] = $aplicables;
            $resultado['mensaje'] = "Se inicializaron 60 estándares ({$aplicables} aplicables para nivel {$nivelEstandares})";

        } catch (\Exception $e) {
            $resultado['mensaje'] = 'Error: ' . $e->getMessage();
            log_message('error', 'inicializarEstandaresCliente: ' . $e->getMessage());
        }

        return $resultado;
    }

    /**
     * Retorna los datos de los 60 estándares mínimos
     */
    protected function getEstandaresData(): array
    {
        return [
            // CICLO PLANEAR - CATEGORÍA I: RECURSOS (10%)
            ['item' => '1.1.1', 'nombre' => 'Responsable del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["ACT-RSP","FOR-DES"]'],
            ['item' => '1.1.2', 'nombre' => 'Responsabilidades en el Sistema de Gestión de Seguridad y Salud en el Trabajo – SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["DOC-RSP","MAN-SST"]'],
            ['item' => '1.1.3', 'nombre' => 'Asignación de recursos para el Sistema de Gestión en Seguridad y Salud en el Trabajo – SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-PRE","ACT-PRE"]'],
            ['item' => '1.1.4', 'nombre' => 'Afiliación al Sistema General de Riesgos Laborales', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-AFI"]'],
            ['item' => '1.1.5', 'nombre' => 'Pago de pensión trabajadores de alto riesgo', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-PEN"]'],
            ['item' => '1.1.6', 'nombre' => 'Conformación COPASST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["ACT-COP","FOR-VOT","FOR-INS"]'],
            ['item' => '1.1.7', 'nombre' => 'Capacitación COPASST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-CAP","FOR-ASI"]'],
            ['item' => '1.1.8', 'nombre' => 'Conformación Comité de Convivencia', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 0.50, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["ACT-CON","FOR-VOT"]'],
            ['item' => '1.2.1', 'nombre' => 'Programa Capacitación promoción y prevención PYP', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 2.00, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRG-CAP","FOR-CRO"]'],
            ['item' => '1.2.2', 'nombre' => 'Capacitación, Inducción y Reinducción en Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST, actividades de Promoción y Prevención PyP', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 2.00, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-IND","FOR-ASI","FOR-EVA"]'],
            ['item' => '1.2.3', 'nombre' => 'Responsables del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST con curso (50 horas)', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'I', 'categoria_nombre' => 'Recursos', 'peso_porcentual' => 2.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-CER"]'],

            // CICLO PLANEAR - CATEGORÍA II: GESTIÓN INTEGRAL DEL SG-SST (15%)
            ['item' => '2.1.1', 'nombre' => 'Política del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST firmada, fechada y comunicada al COPASST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["POL-SST"]'],
            ['item' => '2.2.1', 'nombre' => 'Objetivos definidos, claros, medibles, cuantificables, con metas, documentados, revisados del SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["OBJ-SST"]'],
            ['item' => '2.3.1', 'nombre' => 'Evaluación e identificación de prioridades', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-EVI","INF-EVI"]'],
            ['item' => '2.4.1', 'nombre' => 'Plan que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 2.00, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PLA-TRA"]'],
            ['item' => '2.5.1', 'nombre' => 'Archivo o retención documental del Sistema de Gestión en Seguridad y Salud en el Trabajo SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 0.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-DOC","FOR-LMD"]'],
            ['item' => '2.6.1', 'nombre' => 'Rendición sobre el desempeño', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["INF-RDC","FOR-RDC"]'],
            ['item' => '2.7.1', 'nombre' => 'Matriz legal', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 2.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["MTZ-LEG"]'],
            ['item' => '2.8.1', 'nombre' => 'Mecanismos de comunicación, auto reporte en Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-COM","FOR-AUT"]'],
            ['item' => '2.9.1', 'nombre' => 'Identificación, evaluación, para adquisición de productos y servicios en Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-ADQ","FOR-EVP"]'],
            ['item' => '2.10.1', 'nombre' => 'Evaluación y selección de proveedores y contratistas', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 2.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-CON","MTZ-CON","FOR-EVC"]'],
            ['item' => '2.11.1', 'nombre' => 'Gestión del cambio', 'ciclo_phva' => 'PLANEAR', 'categoria' => 'II', 'categoria_nombre' => 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-CAM","FOR-GDC"]'],

            // CICLO HACER - CATEGORÍA III: GESTIÓN DE LA SALUD (20%)
            ['item' => '3.1.1', 'nombre' => 'Evaluación Médica Ocupacional', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-EMO","FOR-PRO"]'],
            ['item' => '3.1.2', 'nombre' => 'Actividades de Promoción y Prevención en Salud', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRG-MED","PRG-PYP"]'],
            ['item' => '3.1.3', 'nombre' => 'Información al médico de los perfiles de cargo', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-PRO","MTZ-PRO"]'],
            ['item' => '3.1.4', 'nombre' => 'Realización de los exámenes médicos ocupacionales: pre ingreso, periódicos', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-CRO","FOR-APT"]'],
            ['item' => '3.1.5', 'nombre' => 'Custodia de Historias Clínicas', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-CUS","ACT-CUS"]'],
            ['item' => '3.1.6', 'nombre' => 'Restricciones y recomendaciones médico laborales', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-REC","FOR-SEG"]'],
            ['item' => '3.1.7', 'nombre' => 'Estilos de vida y entornos saludables (controles tabaquismo, alcoholismo, farmacodependencia y otros)', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRG-EVS","POL-ADI"]'],
            ['item' => '3.1.8', 'nombre' => 'Agua potable, servicios sanitarios y disposición de basuras', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-INS","FOR-HIG"]'],
            ['item' => '3.1.9', 'nombre' => 'Eliminación adecuada de residuos sólidos, líquidos o gaseosos', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PLA-RES","FOR-RES"]'],
            ['item' => '3.2.1', 'nombre' => 'Reporte de los accidentes de trabajo y enfermedad laboral a la ARL, EPS y Dirección Territorial del Ministerio de Trabajo', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 2.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-REP","FOR-FUR"]'],
            ['item' => '3.2.2', 'nombre' => 'Investigación de Accidentes, Incidentes y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 2.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-INV","FOR-INV"]'],
            ['item' => '3.2.3', 'nombre' => 'Registro y análisis estadístico de Incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-EST","MTZ-EST"]'],
            ['item' => '3.3.1', 'nombre' => 'Medición de la severidad de los Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-IND"]'],
            ['item' => '3.3.2', 'nombre' => 'Medición de la frecuencia de los Incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-IND"]'],
            ['item' => '3.3.3', 'nombre' => 'Medición de la mortalidad de Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-IND"]'],
            ['item' => '3.3.4', 'nombre' => 'Medición de la prevalencia de incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-IND"]'],
            ['item' => '3.3.5', 'nombre' => 'Medición de la incidencia de Incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-IND"]'],
            ['item' => '3.3.6', 'nombre' => 'Medición del ausentismo por incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'ciclo_phva' => 'HACER', 'categoria' => 'III', 'categoria_nombre' => 'Gestión de la Salud', 'peso_porcentual' => 1.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-AUS","MTZ-AUS"]'],

            // CICLO HACER - CATEGORÍA IV: GESTIÓN DE PELIGROS Y RIESGOS (30%)
            ['item' => '4.1.1', 'nombre' => 'Metodología para la identificación, evaluación y valoración de peligros', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 4.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-IPR","GUA-GTC"]'],
            ['item' => '4.1.2', 'nombre' => 'Identificación de peligros con participación de todos los niveles de la empresa', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 4.00, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["MTZ-PEL","FOR-REP"]'],
            ['item' => '4.1.3', 'nombre' => 'Identificación y priorización de la naturaleza de los peligros (Metodología adicional, cancerígenos y otros)', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 3.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["MTZ-PRI","INF-PRI"]'],
            ['item' => '4.1.4', 'nombre' => 'Realización mediciones ambientales, químicos, físicos y biológicos', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 4.00, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["INF-MED","FOR-MED"]'],
            ['item' => '4.2.1', 'nombre' => 'Se implementan las medidas de prevención y control de peligros', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 2.50, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["MTZ-CON","FOR-SEG"]'],
            ['item' => '4.2.2', 'nombre' => 'Se verifica aplicación de las medidas de prevención y control', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-VER","INF-VER"]'],
            ['item' => '4.2.3', 'nombre' => 'Hay procedimientos, instructivos, fichas, protocolos', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRO-SEG","INS-SEG","PRT-SEG"]'],
            ['item' => '4.2.4', 'nombre' => 'Inspección con el COPASST o Vigía', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRG-INS","FOR-INS"]'],
            ['item' => '4.2.5', 'nombre' => 'Mantenimiento periódico de instalaciones, equipos, máquinas, herramientas', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PRG-MAN","FOR-MAN"]'],
            ['item' => '4.2.6', 'nombre' => 'Entrega de Elementos de Protección Personal EPP, se verifica con contratistas y subcontratistas', 'ciclo_phva' => 'HACER', 'categoria' => 'IV', 'categoria_nombre' => 'Gestión de Peligros y Riesgos', 'peso_porcentual' => 2.50, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["MTZ-EPP","FOR-ENT","PRO-EPP"]'],

            // CICLO HACER - CATEGORÍA V: GESTIÓN DE AMENAZAS (10%)
            ['item' => '5.1.1', 'nombre' => 'Se cuenta con el Plan de Prevención y Preparación ante emergencias', 'ciclo_phva' => 'HACER', 'categoria' => 'V', 'categoria_nombre' => 'Gestión de Amenazas', 'peso_porcentual' => 5.00, 'aplica_7' => 1, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["PLA-EME","MTZ-AME"]'],
            ['item' => '5.1.2', 'nombre' => 'Brigada de prevención conformada, capacitada y dotada', 'ciclo_phva' => 'HACER', 'categoria' => 'V', 'categoria_nombre' => 'Gestión de Amenazas', 'peso_porcentual' => 5.00, 'aplica_7' => 0, 'aplica_21' => 1, 'aplica_60' => 1, 'documentos_sugeridos' => '["ACT-BRI","FOR-DOT","PRG-BRI"]'],

            // CICLO VERIFICAR - CATEGORÍA VI: VERIFICACIÓN DEL SG-SST (5%)
            ['item' => '6.1.1', 'nombre' => 'Indicadores estructura, proceso y resultado', 'ciclo_phva' => 'VERIFICAR', 'categoria' => 'VI', 'categoria_nombre' => 'Verificación del SG-SST', 'peso_porcentual' => 1.25, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["MTZ-IND","FOR-FIC"]'],
            ['item' => '6.1.2', 'nombre' => 'Las empresa adelanta auditoría por lo menos una vez al año', 'ciclo_phva' => 'VERIFICAR', 'categoria' => 'VI', 'categoria_nombre' => 'Verificación del SG-SST', 'peso_porcentual' => 1.25, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PLA-AUD","INF-AUD","FOR-AUD"]'],
            ['item' => '6.1.3', 'nombre' => 'Revisión anual por la alta dirección, resultados y alcance de la auditoría', 'ciclo_phva' => 'VERIFICAR', 'categoria' => 'VI', 'categoria_nombre' => 'Verificación del SG-SST', 'peso_porcentual' => 1.25, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["ACT-REV","INF-REV"]'],
            ['item' => '6.1.4', 'nombre' => 'Planificar auditoría con el COPASST', 'ciclo_phva' => 'VERIFICAR', 'categoria' => 'VI', 'categoria_nombre' => 'Verificación del SG-SST', 'peso_porcentual' => 1.25, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["ACT-PLA","FOR-PLA"]'],

            // CICLO ACTUAR - CATEGORÍA VII: MEJORAMIENTO (10%)
            ['item' => '7.1.1', 'nombre' => 'Definir acciones de Promoción y Prevención con base en resultados del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'ciclo_phva' => 'ACTUAR', 'categoria' => 'VII', 'categoria_nombre' => 'Mejoramiento', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PLA-ACC","FOR-ACC"]'],
            ['item' => '7.1.2', 'nombre' => 'Toma de medidas correctivas, preventivas y de mejora', 'ciclo_phva' => 'ACTUAR', 'categoria' => 'VII', 'categoria_nombre' => 'Mejoramiento', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-ACC","MTZ-ACC"]'],
            ['item' => '7.1.3', 'nombre' => 'Ejecución de acciones preventivas, correctivas y de mejora de la investigación de incidentes, accidentes de trabajo y enfermedad laboral', 'ciclo_phva' => 'ACTUAR', 'categoria' => 'VII', 'categoria_nombre' => 'Mejoramiento', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["FOR-SEG","INF-SEG"]'],
            ['item' => '7.1.4', 'nombre' => 'Implementar medidas y acciones correctivas de autoridades y de ARL', 'ciclo_phva' => 'ACTUAR', 'categoria' => 'VII', 'categoria_nombre' => 'Mejoramiento', 'peso_porcentual' => 2.50, 'aplica_7' => 0, 'aplica_21' => 0, 'aplica_60' => 1, 'documentos_sugeridos' => '["PLA-MEJ","FOR-MEJ"]'],
        ];
    }
}
