<?php
/**
 * Script temporal para verificar configuración de programa_capacitacion en producción
 * Uso: scp al servidor y ejecutar con php
 */
$lines = file(".env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach($lines as $l) {
    $l = trim($l);
    if($l === "" || $l[0] === "#") continue;
    $parts = explode(" = ", $l, 2);
    if(count($parts) === 2) $env[trim($parts[0])] = trim($parts[1]);
}
$host = $env["database.default.hostname"] ?? "localhost";
$user = $env["database.default.username"] ?? "root";
$pass = $env["database.default.password"] ?? "";
$port = (int)($env["database.default.port"] ?? 3306);

$conn = mysqli_init();
$conn->ssl_set(null, null, "/www/ca/ca-certificate_cycloid.crt", null, null);
$conn->real_connect($host, $user, $pass, "empresas_sst", $port, null, MYSQLI_CLIENT_SSL);
if($conn->connect_error) { echo "ERROR: ".$conn->connect_error; exit(1); }

echo "=== tbl_doc_tipo_configuracion ===\n";
$r = $conn->query("SELECT id_tipo_config, tipo_documento, nombre, flujo, activo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_capacitacion'");
if($r->num_rows == 0) { echo "NO EXISTE\n"; } else { while($row = $r->fetch_assoc()) echo json_encode($row)."\n"; }

echo "\n=== tbl_doc_secciones_config ===\n";
$r2 = $conn->query("SELECT id_seccion_config, nombre, seccion_key, orden, IF(prompt_ia IS NULL OR prompt_ia='','VACIO','TIENE') as tiene_prompt FROM tbl_doc_secciones_config WHERE id_tipo_config = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento='programa_capacitacion') ORDER BY orden");
if(!$r2 || $r2->num_rows == 0) { echo "NO HAY SECCIONES\n"; } else { while($row = $r2->fetch_assoc()) echo json_encode($row)."\n"; }

echo "\n=== tbl_doc_firmantes_config ===\n";
$r3 = $conn->query("SELECT * FROM tbl_doc_firmantes_config WHERE id_tipo_config = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento='programa_capacitacion') ORDER BY orden");
if(!$r3 || $r3->num_rows == 0) { echo "NO HAY FIRMANTES\n"; } else { while($row = $r3->fetch_assoc()) echo json_encode($row)."\n"; }

$conn->close();
