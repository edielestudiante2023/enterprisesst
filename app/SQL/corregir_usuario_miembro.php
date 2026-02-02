<?php
/**
 * Script para corregir el usuario proyectoperfilesafiancol@gmail.com
 * Cambia tipo_usuario de 'client' a 'miembro' para restringir acceso
 *
 * Ejecutar desde: http://localhost/enterprisesst/public/sql-runner
 * O desde terminal: php app/SQL/corregir_usuario_miembro.php
 */

// Si se ejecuta desde CLI
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../vendor/autoload.php';

    $app = \Config\Services::codeigniter();
    $app->initialize();
}

$db = \Config\Database::connect();

echo "<h2>Corrigiendo usuario miembro</h2>";

// Buscar el usuario
$usuario = $db->table('usuarios')
    ->where('email', 'proyectoperfilesafiancol@gmail.com')
    ->get()
    ->getRowArray();

if (!$usuario) {
    echo "<p style='color: red;'>Usuario no encontrado</p>";
    exit;
}

echo "<p>Usuario encontrado:</p>";
echo "<ul>";
echo "<li>ID: " . $usuario['id_usuario'] . "</li>";
echo "<li>Email: " . $usuario['email'] . "</li>";
echo "<li>Nombre: " . $usuario['nombre_completo'] . "</li>";
echo "<li>Tipo actual: <strong>" . $usuario['tipo_usuario'] . "</strong></li>";
echo "</ul>";

if ($usuario['tipo_usuario'] === 'miembro') {
    echo "<p style='color: green;'>El usuario ya tiene tipo_usuario='miembro'. No se requiere cambio.</p>";
    exit;
}

// Actualizar a tipo miembro
$db->table('usuarios')
    ->where('id_usuario', $usuario['id_usuario'])
    ->update(['tipo_usuario' => 'miembro']);

echo "<p style='color: green; font-weight: bold;'>Usuario actualizado correctamente a tipo_usuario='miembro'</p>";

// Verificar cambio
$usuarioActualizado = $db->table('usuarios')
    ->where('id_usuario', $usuario['id_usuario'])
    ->get()
    ->getRowArray();

echo "<p>Verificacion:</p>";
echo "<ul>";
echo "<li>Tipo ahora: <strong>" . $usuarioActualizado['tipo_usuario'] . "</strong></li>";
echo "</ul>";

echo "<p>El usuario ahora solo tendra acceso al portal de miembros (/miembro/dashboard)</p>";
