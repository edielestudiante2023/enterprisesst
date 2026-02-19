<?php
$db = new mysqli("localhost", "root", "", "empresas_sst");
$db->set_charset("utf8mb4");

echo "=== Clientes con nombre FOCUN ===\n";
$r = $db->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE nombre_cliente LIKE '%FOCUN%'");
while ($row = $r->fetch_assoc()) {
    printf("id_cliente: %d | %s\n", $row["id_cliente"], $row["nombre_cliente"]);
}

echo "\n=== Columnas de tbl_users ===\n";
$r = $db->query("SHOW COLUMNS FROM tbl_users");
while ($row = $r->fetch_assoc()) {
    echo $row["Field"] . " | " . $row["Type"] . "\n";
}

echo "\n=== Usuarios con rol 'client' ===\n";
$r = $db->query("SELECT * FROM tbl_users WHERE role = 'client' LIMIT 10");
while ($row = $r->fetch_assoc()) {
    echo "user_id: " . $row["id"] . " | " . ($row["username"] ?? $row["email"] ?? "?") . " | role: " . $row["role"];
    if (isset($row["id_cliente"])) echo " | id_cliente: " . $row["id_cliente"];
    echo "\n";
}

echo "\n=== Carpetas raiz por cliente ===\n";
$r = $db->query("SELECT id_carpeta, id_cliente, nombre FROM tbl_doc_carpetas WHERE id_carpeta_padre IS NULL AND visible = 1 ORDER BY id_cliente");
while ($row = $r->fetch_assoc()) {
    printf("id_cliente: %d | carpeta_raiz: %d | %s\n", $row["id_cliente"], $row["id_carpeta"], $row["nombre"]);
}

echo "\n=== Documentos por id_cliente (conteo) ===\n";
$r = $db->query("SELECT id_cliente, COUNT(*) as total FROM tbl_documentos_sst GROUP BY id_cliente ORDER BY id_cliente");
while ($row = $r->fetch_assoc()) {
    printf("id_cliente: %d -> %d documentos\n", $row["id_cliente"], $row["total"]);
}

$db->close();
