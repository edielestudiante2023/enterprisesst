<?php
$m = new mysqli('localhost', 'root', '', 'empresas_sst');
if ($m->connect_error) {
    die("Error: " . $m->connect_error . "\n");
}
$r = $m->query("SHOW TABLES LIKE 'tbl_pta_cliente_audit'");
echo "Tabla encontrada: " . $r->num_rows . "\n";
if ($r->num_rows > 0) {
    $desc = $m->query("DESCRIBE tbl_pta_cliente_audit");
    while ($row = $desc->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "LA TABLA NO EXISTE.\n";
    // Intentar crearla directamente con MySQLi
    $sql = "CREATE TABLE IF NOT EXISTS tbl_pta_cliente_audit (
        id_audit INT AUTO_INCREMENT PRIMARY KEY,
        id_ptacliente INT,
        id_cliente INT,
        accion ENUM('INSERT','UPDATE','DELETE','BULK_UPDATE'),
        campo_modificado VARCHAR(100),
        valor_anterior TEXT,
        valor_nuevo TEXT,
        id_usuario INT,
        nombre_usuario VARCHAR(255),
        email_usuario VARCHAR(255),
        rol_usuario VARCHAR(50),
        ip_address VARCHAR(45),
        user_agent TEXT,
        metodo VARCHAR(100),
        descripcion TEXT,
        fecha_accion DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    if ($m->query($sql)) {
        echo "TABLA CREADA EXITOSAMENTE via MySQLi.\n";
    } else {
        echo "ERROR al crear: " . $m->error . "\n";
    }
}
$m->close();
