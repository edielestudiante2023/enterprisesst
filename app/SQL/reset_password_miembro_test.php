<?php
/**
 * Resetear contraseña de un miembro para pruebas
 * Uso: php app/SQL/reset_password_miembro_test.php
 */
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_MR2SLvzRh3i_7o9fEHN',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$idUsuario = 45; // docampo@ardurra.com - Daniel Eduardo Ocampo - secretario COPASST
$nuevaPassword = 'Prueba2026*';
$hash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

// Guardar password actual para restaurar después
$actual = $pdo->query("SELECT password FROM tbl_usuarios WHERE id_usuario = {$idUsuario}")->fetchColumn();
echo "Password hash actual guardado (primeros 20): " . substr($actual, 0, 20) . "...\n";

$stmt = $pdo->prepare("UPDATE tbl_usuarios SET password = ? WHERE id_usuario = ?");
$stmt->execute([$hash, $idUsuario]);

echo "\n=== CREDENCIALES DE PRUEBA ===\n";
echo "URL:      https://dashboard.cycloidtalent.com/login\n";
echo "Email:    docampo@ardurra.com\n";
echo "Password: {$nuevaPassword}\n";
echo "Miembro:  Daniel Eduardo Ocampo Salazar (Secretario COPASST - ARDURRA)\n";
echo "\nDespues de probar, restaurar password con:\n";
echo "  UPDATE tbl_usuarios SET password = '{$actual}' WHERE id_usuario = {$idUsuario};\n";
