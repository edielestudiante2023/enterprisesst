# Sincronizacion Local - Produccion
## Instrucciones para ejecutar cambios en ambos entornos

---

## CREDENCIALES

### Base de Datos LOCAL (XAMPP)
```
Host: localhost
Port: 3306
Database: empresas_sst
Username: root
Password: (vacio)
```

### Base de Datos PRODUCCION (DigitalOcean)
```
Host: db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com
Port: 25060
Database: empresas_sst
Username: cycloid_userdb
Password: AVNS_iDypWizlpMRwHIORJGG
SSL Mode: REQUIRED
```

---

## METODO 1: Script PHP para ejecutar SQL en ambos entornos

Crear archivo `app/SQL/ejecutar_migracion.php`:

```php
<?php
/**
 * Script para ejecutar SQL en LOCAL y PRODUCCION
 * Uso: php ejecutar_migracion.php nombre_archivo.sql
 */

if ($argc < 2) {
    echo "Uso: php ejecutar_migracion.php <archivo.sql>\n";
    exit(1);
}

$archivoSQL = $argv[1];

if (!file_exists($archivoSQL)) {
    echo "Error: Archivo no encontrado: {$archivoSQL}\n";
    exit(1);
}

$sql = file_get_contents($archivoSQL);

// Configuracion de conexiones
$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ],
    'PRODUCCION' => [
        'dsn' => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    ]
];

echo "===========================================\n";
echo "Ejecutando: {$archivoSQL}\n";
echo "===========================================\n\n";

foreach ($conexiones as $nombre => $config) {
    echo "--- {$nombre} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "Conectado OK\n";

        // Ejecutar SQL (soporta multiples statements)
        $pdo->exec($sql);
        echo "SQL ejecutado OK\n\n";

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "===========================================\n";
echo "Proceso completado\n";
echo "===========================================\n";
```

### Uso:
```bash
cd c:\xampp\htdocs\enterprisesst\app\SQL
php ejecutar_migracion.php crear_tablas_documentos.sql
```

---

## METODO 2: Funciones PHP para usar en cualquier script

```php
<?php
/**
 * Funciones helper para conexion a BD
 * Incluir en scripts que necesiten ejecutar en ambos entornos
 */

function getConexionLocal(): PDO
{
    return new PDO(
        'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

function getConexionProduccion(): PDO
{
    return new PDO(
        'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'cycloid_userdb',
        'AVNS_iDypWizlpMRwHIORJGG',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    );
}

function ejecutarEnAmbos(string $sql): array
{
    $resultados = [];

    // LOCAL
    try {
        $pdo = getConexionLocal();
        $pdo->exec($sql);
        $resultados['local'] = ['success' => true, 'message' => 'OK'];
    } catch (PDOException $e) {
        $resultados['local'] = ['success' => false, 'message' => $e->getMessage()];
    }

    // PRODUCCION
    try {
        $pdo = getConexionProduccion();
        $pdo->exec($sql);
        $resultados['produccion'] = ['success' => true, 'message' => 'OK'];
    } catch (PDOException $e) {
        $resultados['produccion'] = ['success' => false, 'message' => $e->getMessage()];
    }

    return $resultados;
}
```

---

## METODO 3: Comando CLI personalizado (CodeIgniter Spark)

Crear archivo `app/Commands/SyncDatabase.php`:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncDatabase extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:sync';
    protected $description = 'Ejecuta SQL en local y produccion';
    protected $usage       = 'db:sync <archivo.sql>';

    public function run(array $params)
    {
        if (empty($params[0])) {
            CLI::error('Debe especificar un archivo SQL');
            return;
        }

        $archivo = APPPATH . 'SQL/' . $params[0];

        if (!file_exists($archivo)) {
            CLI::error("Archivo no encontrado: {$archivo}");
            return;
        }

        $sql = file_get_contents($archivo);

        CLI::write('Ejecutando en LOCAL...', 'yellow');
        $this->ejecutarLocal($sql);

        CLI::write('Ejecutando en PRODUCCION...', 'yellow');
        $this->ejecutarProduccion($sql);

        CLI::write('Sincronizacion completada', 'green');
    }

    private function ejecutarLocal(string $sql): void
    {
        try {
            $pdo = new \PDO(
                'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
                'root', ''
            );
            $pdo->exec($sql);
            CLI::write('  LOCAL: OK', 'green');
        } catch (\PDOException $e) {
            CLI::write('  LOCAL: ' . $e->getMessage(), 'red');
        }
    }

    private function ejecutarProduccion(string $sql): void
    {
        try {
            $pdo = new \PDO(
                'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
                'cycloid_userdb',
                'AVNS_iDypWizlpMRwHIORJGG',
                [\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
            );
            $pdo->exec($sql);
            CLI::write('  PRODUCCION: OK', 'green');
        } catch (\PDOException $e) {
            CLI::write('  PRODUCCION: ' . $e->getMessage(), 'red');
        }
    }
}
```

### Uso:
```bash
cd c:\xampp\htdocs\enterprisesst
php spark db:sync crear_tablas_documentos.sql
```

---

## EJEMPLO: Crear las tablas de documentos

1. Crear archivo `app/SQL/crear_tablas_documentos.sql`:

```sql
-- ============================================
-- Tablas para sistema de generacion de documentos
-- Fecha: Enero 2026
-- ============================================

-- Tabla principal de documentos generados
CREATE TABLE IF NOT EXISTS tbl_doc_generados (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    codigo_documento VARCHAR(20) NOT NULL,
    tipo_documento VARCHAR(50) NOT NULL,
    nombre_documento VARCHAR(255) NOT NULL,
    version INT DEFAULT 1,
    estado ENUM('borrador', 'revision', 'aprobado', 'firmado', 'obsoleto') DEFAULT 'borrador',
    id_carpeta INT NULL,
    fecha_aprobacion DATE NULL,
    aprobado_por INT NULL,
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de secciones de cada documento
CREATE TABLE IF NOT EXISTS tbl_doc_secciones (
    id_seccion INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    seccion_key VARCHAR(50) NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    orden INT DEFAULT 0,
    tipo ENUM('ia', 'fijo', 'tabla', 'manual') DEFAULT 'ia',
    contenido_generado LONGTEXT NULL,
    contexto_adicional TEXT NULL,
    contenido_editado LONGTEXT NULL,
    contenido_final LONGTEXT NULL,
    prompt_usado TEXT NULL,
    modelo_ia VARCHAR(50) NULL,
    tokens_usados INT NULL,
    regeneraciones INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_documento (id_documento),
    UNIQUE KEY uk_documento_seccion (id_documento, seccion_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de versiones de documentos
CREATE TABLE IF NOT EXISTS tbl_doc_versiones (
    id_version INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    version INT NOT NULL,
    contenido_completo LONGTEXT NOT NULL,
    motivo_cambio VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    INDEX idx_documento (id_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

2. Ejecutar:
```bash
php app/SQL/ejecutar_migracion.php app/SQL/crear_tablas_documentos.sql
```

---

## VERIFICAR SINCRONIZACION

Script para verificar que las tablas existen en ambos entornos:

```php
<?php
// verificar_tablas.php

$tablas = [
    'tbl_doc_generados',
    'tbl_doc_secciones',
    'tbl_doc_versiones'
];

echo "=== Verificando tablas ===\n\n";

// LOCAL
echo "LOCAL:\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');
    foreach ($tablas as $tabla) {
        $result = $pdo->query("SHOW TABLES LIKE '{$tabla}'")->rowCount();
        echo "  {$tabla}: " . ($result ? "OK" : "NO EXISTE") . "\n";
    }
} catch (PDOException $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\nPRODUCCION:\n";
try {
    $pdo = new PDO(
        'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst',
        'cycloid_userdb',
        'AVNS_iDypWizlpMRwHIORJGG',
        [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
    );
    foreach ($tablas as $tabla) {
        $result = $pdo->query("SHOW TABLES LIKE '{$tabla}'")->rowCount();
        echo "  {$tabla}: " . ($result ? "OK" : "NO EXISTE") . "\n";
    }
} catch (PDOException $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}
```

---

## NOTAS IMPORTANTES

1. **SSL en Produccion**: DigitalOcean requiere SSL. Usar `PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false` para evitar errores de certificado.

2. **Stored Procedures**: Para crear SP, usar DELIMITER en el archivo SQL o ejecutar sin DELIMITER si es un solo statement.

3. **Backup antes de cambios**: Siempre hacer backup de produccion antes de ejecutar migraciones:
   ```bash
   mysqldump -h db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com -P 25060 -u cycloid_userdb -p empresas_sst > backup_$(date +%Y%m%d).sql
   ```

4. **Orden de ejecucion**: Siempre ejecutar primero en LOCAL, verificar que funciona, luego en PRODUCCION.
