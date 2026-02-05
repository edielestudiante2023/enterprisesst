<?php
/**
 * Ejecutar creación del módulo de recomposición de comités
 *
 * Acceder vía: /ejecutar-sql-recomposicion
 * O ejecutar directamente desde el navegador
 */

// Cargar el framework de CodeIgniter
$pathToIndex = dirname(dirname(dirname(__FILE__))) . '/public/index.php';
if (!file_exists($pathToIndex)) {
    // Alternativa: ejecutar standalone
    require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';
}

// Conectar a la base de datos
$db = \Config\Database::connect();

$results = [];
$errors = [];

try {
    // 1. Crear tabla de recomposiciones
    $sql1 = "CREATE TABLE IF NOT EXISTS `tbl_recomposiciones_comite` (
        `id_recomposicion` INT AUTO_INCREMENT PRIMARY KEY,
        `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales (proceso original)',
        `id_cliente` INT NOT NULL COMMENT 'FK a tbl_cliente',
        `fecha_recomposicion` DATE NOT NULL,
        `numero_recomposicion` INT DEFAULT 1 COMMENT 'Número secuencial de recomposición en este proceso',
        `id_candidato_saliente` INT NOT NULL COMMENT 'FK a tbl_candidatos_comite',
        `motivo_salida` ENUM(
            'terminacion_contrato',
            'renuncia_voluntaria',
            'sancion_disciplinaria',
            'violacion_confidencialidad',
            'inasistencia_reiterada',
            'incumplimiento_funciones',
            'fallecimiento',
            'otro'
        ) NOT NULL,
        `motivo_detalle` TEXT DEFAULT NULL,
        `fecha_efectiva_salida` DATE NOT NULL,
        `id_candidato_entrante` INT DEFAULT NULL,
        `tipo_ingreso` ENUM(
            'siguiente_votacion',
            'designacion_empleador',
            'asamblea_extraordinaria'
        ) NOT NULL,
        `entrante_nombres` VARCHAR(100) DEFAULT NULL,
        `entrante_apellidos` VARCHAR(100) DEFAULT NULL,
        `entrante_documento` VARCHAR(20) DEFAULT NULL,
        `entrante_cargo` VARCHAR(100) DEFAULT NULL,
        `entrante_email` VARCHAR(150) DEFAULT NULL,
        `entrante_telefono` VARCHAR(20) DEFAULT NULL,
        `estado` ENUM('borrador', 'pendiente_firmas', 'firmado', 'cancelado') DEFAULT 'borrador',
        `id_documento` INT DEFAULT NULL,
        `observaciones` TEXT DEFAULT NULL,
        `justificacion_legal` TEXT DEFAULT NULL,
        `created_by` INT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_proceso` (`id_proceso`),
        INDEX `idx_cliente` (`id_cliente`),
        INDEX `idx_fecha` (`fecha_recomposicion`),
        INDEX `idx_estado` (`estado`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Registro de recomposiciones de comités SST'";

    $db->query($sql1);
    $results[] = "✅ Tabla tbl_recomposiciones_comite creada exitosamente";

    // 2. Agregar columnas a tbl_candidatos_comite
    $columnas = [
        "estado_miembro" => "ADD COLUMN `estado_miembro` ENUM('activo', 'retirado', 'reemplazado') DEFAULT 'activo' COMMENT 'Estado actual como miembro del comité'",
        "fecha_ingreso_comite" => "ADD COLUMN `fecha_ingreso_comite` DATE DEFAULT NULL COMMENT 'Fecha de ingreso al comité'",
        "fecha_retiro_comite" => "ADD COLUMN `fecha_retiro_comite` DATE DEFAULT NULL COMMENT 'Fecha de retiro del comité'",
        "es_recomposicion" => "ADD COLUMN `es_recomposicion` TINYINT(1) DEFAULT 0 COMMENT 'Si ingresó por recomposición'",
        "id_recomposicion_ingreso" => "ADD COLUMN `id_recomposicion_ingreso` INT DEFAULT NULL COMMENT 'FK a tbl_recomposiciones_comite'",
        "posicion_votacion" => "ADD COLUMN `posicion_votacion` INT DEFAULT NULL COMMENT 'Posición en la votación original'"
    ];

    foreach ($columnas as $columna => $alterSql) {
        // Verificar si la columna ya existe
        $check = $db->query("SHOW COLUMNS FROM `tbl_candidatos_comite` LIKE '{$columna}'");
        if ($check->getNumRows() == 0) {
            $db->query("ALTER TABLE `tbl_candidatos_comite` {$alterSql}");
            $results[] = "✅ Columna '{$columna}' agregada a tbl_candidatos_comite";
        } else {
            $results[] = "⏭️ Columna '{$columna}' ya existe, omitida";
        }
    }

    // 3. Crear índices
    try {
        $db->query("CREATE INDEX `idx_estado_miembro` ON `tbl_candidatos_comite` (`id_proceso`, `estado_miembro`, `representacion`)");
        $results[] = "✅ Índice idx_estado_miembro creado";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            $results[] = "⏭️ Índice idx_estado_miembro ya existe";
        } else {
            $errors[] = "⚠️ Error creando índice: " . $e->getMessage();
        }
    }

    try {
        $db->query("CREATE INDEX `idx_posicion_votacion` ON `tbl_candidatos_comite` (`id_proceso`, `representacion`, `posicion_votacion`)");
        $results[] = "✅ Índice idx_posicion_votacion creado";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            $results[] = "⏭️ Índice idx_posicion_votacion ya existe";
        } else {
            $errors[] = "⚠️ Error creando índice: " . $e->getMessage();
        }
    }

    // 4. Actualizar posición de votación para candidatos existentes
    $procesos = $db->query("SELECT DISTINCT id_proceso FROM tbl_candidatos_comite WHERE representacion = 'trabajador'")->getResultArray();
    foreach ($procesos as $proc) {
        $candidatos = $db->query("
            SELECT id_candidato
            FROM tbl_candidatos_comite
            WHERE id_proceso = ? AND representacion = 'trabajador'
            ORDER BY votos_obtenidos DESC, id_candidato ASC
        ", [$proc['id_proceso']])->getResultArray();

        $pos = 1;
        foreach ($candidatos as $c) {
            $db->query("UPDATE tbl_candidatos_comite SET posicion_votacion = ? WHERE id_candidato = ?", [$pos, $c['id_candidato']]);
            $pos++;
        }
    }
    $results[] = "✅ Posiciones de votación actualizadas para " . count($procesos) . " procesos";

} catch (\Exception $e) {
    $errors[] = "❌ Error: " . $e->getMessage();
}

// Mostrar resultados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Módulo Recomposición</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-database-gear me-2"></i>
                    Instalación: Módulo de Recomposición de Comités
                </h4>
            </div>
            <div class="card-body">
                <h5 class="text-success mb-3">Resultados:</h5>
                <ul class="list-group mb-4">
                    <?php foreach ($results as $r): ?>
                    <li class="list-group-item"><?= $r ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($errors)): ?>
                <h5 class="text-danger mb-3">Errores:</h5>
                <ul class="list-group mb-4">
                    <?php foreach ($errors as $e): ?>
                    <li class="list-group-item list-group-item-danger"><?= $e ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>Siguiente paso:</strong> Ya puedes acceder al módulo de recomposición desde cualquier proceso de comité completado.
                </div>

                <a href="<?= base_url('comites-elecciones') ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1"></i> Ir a Comités
                </a>
            </div>
        </div>
    </div>
</body>
</html>
