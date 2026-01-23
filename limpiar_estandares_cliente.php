<?php
/**
 * Script para limpiar estándares de un cliente y poder regenerarlos
 * Uso: http://localhost/enterprisesst/limpiar_estandares_cliente.php?cliente=11
 */

$idCliente = $_GET['cliente'] ?? null;

if (!$idCliente) {
    die('Error: Debes especificar el ID del cliente. Ejemplo: ?cliente=11');
}

$mysqli = new mysqli('localhost', 'root', '', 'empresas_sst');

if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

echo "<h2>Limpieza de Estándares - Cliente ID: $idCliente</h2>";

// Verificar cuántos registros hay
$result = $mysqli->query("SELECT COUNT(*) as total FROM tbl_cliente_estandares WHERE id_cliente = $idCliente");
$row = $result->fetch_assoc();
$total = $row['total'];

echo "<p>Registros encontrados: <strong>$total</strong></p>";

if ($total > 0) {
    // Eliminar los registros
    $sql = "DELETE FROM tbl_cliente_estandares WHERE id_cliente = $idCliente";

    if ($mysqli->query($sql)) {
        $deleted = $mysqli->affected_rows;
        echo "<p style='color:green'>✓ Se eliminaron <strong>$deleted</strong> registros de estándares del cliente.</p>";
        echo "<p>Ahora puedes ir al dashboard del cliente y hacer clic en 'Inicializar Estándares del Cliente' para regenerarlos con los criterios actualizados.</p>";
        echo "<p><a href='/enterprisesst/public/index.php/estandares/$idCliente' class='btn'>Ir al Dashboard de Estándares</a></p>";
    } else {
        echo "<p style='color:red'>✗ Error al eliminar: " . $mysqli->error . "</p>";
    }
} else {
    echo "<p style='color:blue'>ℹ No hay estándares para este cliente. Puedes inicializarlos directamente.</p>";
    echo "<p><a href='/enterprisesst/public/index.php/estandares/$idCliente'>Ir al Dashboard de Estándares</a></p>";
}

$mysqli->close();
?>
