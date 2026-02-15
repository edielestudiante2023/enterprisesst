<?php
/**
 * Script para corregir códigos de documentos (DOC-GEN-001 → códigos correctos)
 *
 * Ejecutar LOCAL: php app/SQL/corregir_codigos_documentos.php
 * Ejecutar PROD:  php app/SQL/corregir_codigos_documentos.php --prod
 *
 * PROBLEMA:
 * - Sistema no consulta Factory para obtener códigos
 * - Solo busca en tbl_doc_plantillas (tabla legacy)
 * - Políticas nuevas no estaban en tbl_doc_plantillas
 * - Resultado: documentos generados con "DOC-GEN-001"
 *
 * SOLUCIÓN:
 * 1. Agregar códigos faltantes a tbl_doc_plantillas
 * 2. Corregir documentos existentes en tbl_documentos_sst
 * 3. Sincronizar discrepancias (POL-ADT → POL-ALC)
 *
 * Ver documentación completa en:
 * docs/MODULO_NUMERALES_SGSST/TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md
 */

// Detectar entorno
$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    // Configuracion PRODUCCION (DigitalOcean)
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ];
    echo "*** MODO PRODUCCION ***\n";
} else {
    // Configuracion LOCAL
    $config = [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ];
    echo "*** MODO LOCAL ***\n";
}

// ========================================
// SQL 1: Agregar códigos faltantes a tbl_doc_plantillas
// ========================================
$sqlAgregarCodigos = <<<'SQL'
-- Agregar códigos faltantes para políticas del numeral 2.1.1
-- id_tipo = 1 (Política)
-- orden: secuencial desde 100 para evitar conflictos
INSERT INTO tbl_doc_plantillas
(id_tipo, tipo_documento, codigo_sugerido, nombre, descripcion, activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at)
VALUES
(1, 'politica_violencias_genero', 'POL-VGE',
 'Política de Prevención del Acoso Sexual y Violencias de Género',
 'Política basada en Ley 1257 de 2008',
 1, 100, 1, 1, 1, NOW(), NOW()),
(1, 'politica_discriminacion', 'POL-DIS',
 'Política de Prevención de la Discriminación, Maltrato y Violencia',
 'Política para garantizar igualdad de trato y oportunidades',
 1, 101, 1, 1, 1, NOW(), NOW()),
(1, 'politica_desconexion_laboral', 'POL-DES',
 'Política de Desconexión Laboral',
 'Política basada en Ley 2191 de 2022',
 1, 102, 1, 1, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    codigo_sugerido = VALUES(codigo_sugerido),
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo = 1,
    updated_at = NOW();
SQL;

// ========================================
// SQL 2: Sincronizar discrepancia POL-ADT → POL-ALC
// ========================================
$sqlSincronizarAlcohol = <<<'SQL'
-- Actualizar código de politica_alcohol_drogas para coincidir con Factory
UPDATE tbl_doc_plantillas
SET codigo_sugerido = 'POL-ALC'
WHERE tipo_documento = 'politica_alcohol_drogas'
  AND codigo_sugerido = 'POL-ADT';
SQL;

// ========================================
// SQL 3: Corregir documentos existentes con DOC-GEN-001
// ========================================
$sqlCorregirDocumentos = <<<'SQL'
-- Corregir documentos generados con código genérico DOC-GEN-001

-- Política de Violencias de Género
UPDATE tbl_documentos_sst
SET codigo = 'POL-VGE-001'
WHERE tipo_documento = 'politica_violencias_genero'
  AND codigo = 'DOC-GEN-001';

-- Política de Discriminación
UPDATE tbl_documentos_sst
SET codigo = 'POL-DIS-001'
WHERE tipo_documento = 'politica_discriminacion'
  AND codigo = 'DOC-GEN-001';

-- Política de Desconexión Laboral (por si fue generada antes del fix)
UPDATE tbl_documentos_sst
SET codigo = 'POL-DES-001'
WHERE tipo_documento = 'politica_desconexion_laboral'
  AND codigo = 'DOC-GEN-001';
SQL;

// ========================================
// SQL 4: Corregir discrepancia POL-ADT → POL-ALC en documentos
// ========================================
$sqlCorregirAlcoholDocs = <<<'SQL'
-- Actualizar documentos de alcohol_drogas que usan código antiguo
UPDATE tbl_documentos_sst
SET codigo = REPLACE(codigo, 'POL-ADT', 'POL-ALC')
WHERE tipo_documento = 'politica_alcohol_drogas'
  AND codigo LIKE 'POL-ADT%';
SQL;

// ========================================
// Función de ejecución
// ========================================
function ejecutar($config, $sqls) {
    echo "\n========================================================\n";
    echo "  CORRECCION DE CODIGOS DE DOCUMENTOS\n";
    echo "========================================================\n";
    echo "\nProblema: Documentos con codigo DOC-GEN-001\n";
    echo "Causa: Factory no se consulta, faltan registros en tbl_doc_plantillas\n";
    echo "Solucion: Agregar codigos + corregir documentos existentes\n\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        // SSL para produccion
        if (!empty($config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado a {$config['host']}:{$config['port']}\n\n";

        // Ejecutar cada SQL
        foreach ($sqls as $descripcion => $sql) {
            echo "Ejecutando: $descripcion... ";
            try {
                $stmt = $pdo->exec($sql);
                $affected = $stmt === false ? 0 : $stmt;
                echo "OK ($affected filas afectadas)\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "(ya existe, sin cambios)\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

        // ========================================
        // Verificación
        // ========================================
        echo "\n========================================================\n";
        echo "  VERIFICACION\n";
        echo "========================================================\n";

        // 1. Verificar tbl_doc_plantillas
        echo "\n1. Códigos en tbl_doc_plantillas (políticas 2.1.1):\n";
        $stmt = $pdo->query("
            SELECT tipo_documento, codigo_sugerido
            FROM tbl_doc_plantillas
            WHERE tipo_documento IN (
                'politica_alcohol_drogas',
                'politica_acoso_laboral',
                'politica_violencias_genero',
                'politica_discriminacion',
                'politica_desconexion_laboral'
            )
            ORDER BY tipo_documento
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$row['tipo_documento']}: {$row['codigo_sugerido']}\n";
        }

        // 2. Verificar documentos corregidos
        echo "\n2. Documentos con código genérico (debería estar vacío):\n";
        $stmt = $pdo->query("
            SELECT tipo_documento, codigo, COUNT(*) as total
            FROM tbl_documentos_sst
            WHERE codigo = 'DOC-GEN-001'
              AND tipo_documento LIKE 'politica_%'
            GROUP BY tipo_documento, codigo
        ");
        $found = false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   ⚠️ {$row['tipo_documento']}: {$row['codigo']} ({$row['total']} docs)\n";
            $found = true;
        }
        if (!$found) {
            echo "   ✅ Sin documentos con DOC-GEN-001\n";
        }

        // 3. Verificar documentos corregidos por tipo
        echo "\n3. Documentos corregidos (código específico):\n";
        $stmt = $pdo->query("
            SELECT tipo_documento, codigo, COUNT(*) as total
            FROM tbl_documentos_sst
            WHERE tipo_documento IN (
                'politica_violencias_genero',
                'politica_discriminacion',
                'politica_desconexion_laboral',
                'politica_alcohol_drogas'
            )
            GROUP BY tipo_documento, codigo
            ORDER BY tipo_documento, codigo
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $icon = strpos($row['codigo'], 'DOC-GEN') !== false ? '⚠️' : '✅';
            echo "   $icon {$row['tipo_documento']}: {$row['codigo']} ({$row['total']} docs)\n";
        }

        // 4. Resumen Factory vs BD
        echo "\n4. Comparación Factory vs tbl_doc_plantillas:\n";
        $factoryCodes = [
            'politica_alcohol_drogas' => 'POL-ALC',
            'politica_acoso_laboral' => 'POL-ACO',
            'politica_violencias_genero' => 'POL-VGE',
            'politica_discriminacion' => 'POL-DIS',
            'politica_desconexion_laboral' => 'POL-DES'
        ];

        foreach ($factoryCodes as $tipo => $codigoFactory) {
            $stmt = $pdo->prepare("
                SELECT codigo_sugerido
                FROM tbl_doc_plantillas
                WHERE tipo_documento = ?
            ");
            $stmt->execute([$tipo]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $codigoBD = $row['codigo_sugerido'] ?? 'NO EXISTE';

            $match = $codigoBD === $codigoFactory ? '✅' : '⚠️';
            echo "   $match $tipo\n";
            echo "      Factory: $codigoFactory | BD: $codigoBD\n";
        }

        echo "\n========================================================\n";
        echo "  COMPLETADO\n";
        echo "========================================================\n";
        echo "\nSiguiente paso: Modificar DocumentosSSTController\n";
        echo "Ver: docs/MODULO_NUMERALES_SGSST/TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md\n\n";

    } catch (PDOException $e) {
        echo "Error de conexion: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ========================================
// Ejecutar
// ========================================
$sqls = [
    '1. Agregar códigos faltantes a tbl_doc_plantillas' => $sqlAgregarCodigos,
    '2. Sincronizar código alcohol_drogas (POL-ADT → POL-ALC)' => $sqlSincronizarAlcohol,
    '3. Corregir documentos con DOC-GEN-001' => $sqlCorregirDocumentos,
    '4. Corregir documentos alcohol_drogas (POL-ADT → POL-ALC)' => $sqlCorregirAlcoholDocs
];

ejecutar($config, $sqls);
