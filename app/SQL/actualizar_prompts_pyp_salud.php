<?php
/**
 * Actualiza los prompts de las secciones de PyP Salud para que usen
 * los datos reales del contexto (actividades e indicadores)
 */

echo "=== ACTUALIZANDO PROMPTS DE PYP SALUD ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

$updates = [
    'cronograma' => 'Genera el cronograma de actividades del Programa de Promoción y Prevención en Salud para {EMPRESA} para el año {ANIO}.

IMPORTANTE: USA LAS ACTIVIDADES REALES listadas en el contexto de "ACTIVIDADES DE PROMOCIÓN Y PREVENCIÓN EN SALUD".
NO inventes actividades. Toma las actividades del Plan de Trabajo y organízalas en formato de cronograma.

Presenta en formato tabla markdown:
| Actividad | Responsable | Frecuencia | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic |

Marca con X los meses donde está programada cada actividad según las fechas del contexto.',

    'indicadores' => 'Define los indicadores del Programa de Promoción y Prevención en Salud para {EMPRESA}.

IMPORTANTE: USA LOS INDICADORES REALES listados en el contexto de "INDICADORES DE PROMOCIÓN Y PREVENCIÓN EN SALUD".
NO inventes indicadores. Usa los que ya están configurados en el sistema.

Para cada indicador del contexto, presenta:
- Nombre del indicador
- Tipo (resultado, proceso, estructura)
- Fórmula de cálculo
- Meta establecida
- Periodicidad de medición

Si no hay indicadores en el contexto, genera los indicadores básicos de PyP Salud.',

    'objetivos_especificos' => 'Genera los objetivos específicos del Programa de Promoción y Prevención en Salud para {EMPRESA}.

IMPORTANTE: Los objetivos deben DERIVARSE de las ACTIVIDADES REALES listadas en el contexto de "ACTIVIDADES DE PROMOCIÓN Y PREVENCIÓN EN SALUD".

Cada objetivo debe:
- Estar alineado con una o más actividades del Plan de Trabajo
- Ser específico, medible, alcanzable, relevante y temporal (SMART)
- Usar verbos en infinitivo (Implementar, Desarrollar, Ejecutar, Realizar)

Genera mínimo 5 objetivos específicos basados en las actividades registradas.
Presentar en formato de lista numerada.'
];

foreach ($conexiones as $entorno => $config) {
    echo "=== " . strtoupper($entorno) . " ===\n";
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conectado\n";

        foreach ($updates as $seccion => $prompt) {
            $sql = "UPDATE tbl_doc_secciones_config
                    SET prompt_ia = :prompt
                    WHERE id_tipo_config = (
                        SELECT id_tipo_config
                        FROM tbl_doc_tipo_configuracion
                        WHERE tipo_documento = 'programa_promocion_prevencion_salud'
                    ) AND seccion_key = :seccion";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['prompt' => $prompt, 'seccion' => $seccion]);
            $affected = $stmt->rowCount();
            echo "  ✅ {$seccion}: {$affected} fila(s) actualizada(s)\n";
        }
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "✅ Prompts actualizados para usar datos reales del contexto\n";
