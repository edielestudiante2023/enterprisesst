<?php
// Script para actualizar SP en producción

$dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);

    echo "Conectado a producción\n";

    // Ejecutar DROP primero
    $pdo->exec('DROP PROCEDURE IF EXISTS sp_generar_carpetas_por_nivel');
    echo "✓ DROP ejecutado\n";

    // El SP completo actualizado
    $sp = <<<'SQL'
CREATE PROCEDURE sp_generar_carpetas_por_nivel(
    IN p_id_cliente INT,
    IN p_anio INT,
    IN p_nivel_estandares INT
)
BEGIN
    DECLARE v_id_raiz INT;
    DECLARE v_id_planear INT;
    DECLARE v_id_hacer INT;
    DECLARE v_id_verificar INT;
    DECLARE v_id_actuar INT;

    IF NOT EXISTS (SELECT 1 FROM tbl_clientes WHERE id_cliente = p_id_cliente) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cliente no existe';
    END IF;

    IF p_nivel_estandares NOT IN (7, 21, 60) THEN
        SET p_nivel_estandares = 60;
    END IF;

    DELETE FROM tbl_doc_carpetas WHERE id_cliente = p_id_cliente AND nombre LIKE CONCAT('SG-SST ', p_anio, '%');

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, NULL, CONCAT('SG-SST ', p_anio), NULL, 1, 'raiz', 'folder-root');
    SET v_id_raiz = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
    VALUES (p_id_cliente, v_id_raiz, '1. PLANEAR', '1', 1, 'phva', 'clipboard-list', '#3B82F6');
    SET v_id_planear = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
    VALUES (p_id_cliente, v_id_raiz, '2. HACER', '2', 2, 'phva', 'play-circle', '#10B981');
    SET v_id_hacer = LAST_INSERT_ID();

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
        VALUES (p_id_cliente, v_id_raiz, '3. VERIFICAR', '3', 3, 'phva', 'check-circle', '#F59E0B');
        SET v_id_verificar = LAST_INSERT_ID();

        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
        VALUES (p_id_cliente, v_id_raiz, '4. ACTUAR', '4', 4, 'phva', 'refresh', '#EF4444');
        SET v_id_actuar = LAST_INSERT_ID();
    END IF;

    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.1. Recursos', '1.1', 1, 'categoria', 'users');
    END IF;

    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.2. Politica y Objetivos SST', '1.2', 2, 'categoria', 'target');
    END IF;

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.3. Evaluacion Inicial', '1.3', 3, 'categoria', 'clipboard-check');
    END IF;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.4. Plan de Trabajo Anual', '1.4', 4, 'categoria', 'calendar');

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.5. Identificacion de Peligros (IPEVR)', '1.5', 5, 'categoria', 'alert-triangle');

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.6. Requisitos Legales', '1.6', 6, 'categoria', 'book-open');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.7. Mecanismos de Comunicacion', '1.7', 7, 'categoria', 'message-circle');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.8. Adquisiciones y Contratacion', '1.8', 8, 'categoria', 'shopping-cart');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.9. Gestion del Cambio', '1.9', 9, 'categoria', 'refresh-cw');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.10. Rendicion de Cuentas', '1.10', 10, 'categoria', 'bar-chart');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.11. Control Documental', '1.11', 11, 'categoria', 'folder');
    END IF;

    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.1. Condiciones de Salud', '2.1', 1, 'categoria', 'heart');
    END IF;

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.2. Riesgo Psicosocial', '2.2', 2, 'categoria', 'brain');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.3. Acoso Laboral y Convivencia', '2.3', 3, 'categoria', 'users');
    END IF;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.4. Capacitacion SST', '2.4', 4, 'categoria', 'book');

    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.5. Induccion y Reinduccion', '2.5', 5, 'categoria', 'user-plus');
    END IF;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.6. Medidas de Prevencion y Control', '2.6', 6, 'categoria', 'shield');

    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.7. EPP', '2.7', 7, 'categoria', 'hard-hat');
    END IF;

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.8. Inspecciones de Seguridad', '2.8', 8, 'categoria', 'search');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.9. Mantenimiento', '2.9', 9, 'categoria', 'tool');
    END IF;

    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.10. Plan de Emergencias', '2.10', 10, 'categoria', 'alert-circle');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.11. Brigada de Emergencias', '2.11', 11, 'categoria', 'users');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.12. COPASST / Vigia SST', '2.12', 12, 'categoria', 'users');
    END IF;

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.13. Investigacion AT/EL', '2.13', 13, 'categoria', 'file-text');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.14. Reglamentos', '2.14', 14, 'categoria', 'file');
    END IF;

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.1. Indicadores del SG-SST', '3.1', 1, 'categoria', 'activity');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.2. Auditoria Interna', '3.2', 2, 'categoria', 'clipboard');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.3. Revision por la Alta Direccion', '3.3', 3, 'categoria', 'eye');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.4. Investigacion de AT/EL (Informes)', '3.4', 4, 'categoria', 'file-text');
    END IF;

    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.1. Plan de Mejoramiento', '4.1', 1, 'categoria', 'trending-up');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.2. Acciones Correctivas y Preventivas', '4.2', 2, 'categoria', 'check-circle');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.3. Mejora Continua', '4.3', 3, 'categoria', 'refresh-cw');
    END IF;

    SELECT v_id_raiz AS id_carpeta_raiz;
END
SQL;

    $pdo->exec($sp);
    echo "✓ SP creado en producción\n";

    // También crear la tabla de mapeo si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_doc_plantilla_carpeta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_plantilla VARCHAR(50) NOT NULL,
            codigo_carpeta VARCHAR(10) NOT NULL,
            descripcion VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_plantilla_carpeta (codigo_plantilla)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabla tbl_doc_plantilla_carpeta verificada\n";

    // Insertar mapeos
    $mapeos = [
        ['PRG-CAP', '2.4', 'Programa capacitación'],
        ['PLA-PTA', '1.4', 'Plan de trabajo anual'],
        ['PRO-IPVR', '1.5', 'Procedimiento IPVR'],
        ['FOR-INS', '2.6', 'Formato inspección seguridad'],
        ['FOR-INC', '2.6', 'Formato reporte incidentes'],
        ['FOR-ASI', '2.4', 'Formato asistencia capacitación'],
        ['MTZ-IPE', '1.5', 'Matriz IPEVR'],
    ];

    $stmt = $pdo->prepare('INSERT IGNORE INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion) VALUES (?, ?, ?)');
    foreach ($mapeos as $m) {
        $stmt->execute($m);
    }
    echo "✓ Mapeos plantilla-carpeta insertados\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
