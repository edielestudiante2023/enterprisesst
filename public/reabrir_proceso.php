<?php
/**
 * Reabrir proceso electoral - Script mejorado
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

// Obtener ID del proceso desde URL o usar 1 por defecto
$idProceso = isset($_GET['id']) ? (int)$_GET['id'] : 1;

echo "<h1>🔄 Reabrir Proceso Electoral</h1>";
echo "<style>
    body{font-family:Arial;padding:20px;max-width:800px;margin:0 auto;}
    .ok{color:green;font-weight:bold;}
    .error{color:red;}
    .info{color:blue;}
    .card{background:#f5f5f5;padding:15px;border-radius:8px;margin:15px 0;}
    button{background:#0d6efd;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;font-size:16px;}
    button:hover{background:#0b5ed7;}
</style>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Mostrar todos los procesos
    echo "<h2>📋 Procesos existentes:</h2>";
    $procesos = $pdo->query("SELECT * FROM tbl_procesos_electorales ORDER BY id_proceso DESC")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;width:100%;'>";
    echo "<tr style='background:#333;color:white;'><th>ID</th><th>Cliente</th><th>Tipo</th><th>Año</th><th>Estado Actual</th><th>Acción</th></tr>";

    foreach ($procesos as $p) {
        $esActual = ($p['id_proceso'] == $idProceso) ? "style='background:#fff3cd;'" : "";
        echo "<tr $esActual>";
        echo "<td><strong>{$p['id_proceso']}</strong></td>";
        echo "<td>{$p['id_cliente']}</td>";
        echo "<td>{$p['tipo_comite']}</td>";
        echo "<td>{$p['anio']}</td>";
        echo "<td><span style='color:" . ($p['estado'] == 'votacion' ? 'green' : 'orange') . "'><strong>{$p['estado']}</strong></span></td>";
        echo "<td><a href='?id={$p['id_proceso']}&action=reabrir'>Reabrir este</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    // Si se solicita reabrir
    if (isset($_GET['action']) && $_GET['action'] == 'reabrir') {
        echo "<div class='card'>";
        echo "<h3>🔧 Reabriendo proceso ID: $idProceso</h3>";

        // Obtener estado actual
        $proceso = $pdo->query("SELECT * FROM tbl_procesos_electorales WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);

        if (!$proceso) {
            echo "<p class='error'>❌ Proceso $idProceso no encontrado</p>";
        } else {
            echo "<p>Estado anterior: <strong style='color:orange;'>{$proceso['estado']}</strong></p>";

            // Reabrir a votación
            $stmt = $pdo->prepare("UPDATE tbl_procesos_electorales
                SET estado = 'votacion',
                    fecha_escrutinio = NULL,
                    fecha_completado = NULL
                WHERE id_proceso = ?");
            $stmt->execute([$idProceso]);

            echo "<p class='ok'>✅ Proceso reabierto exitosamente!</p>";
            echo "<p>Estado nuevo: <strong style='color:green;'>votacion</strong></p>";

            echo "<h3>🔗 Enlaces:</h3>";
            echo "<ul>";
            echo "<li><a href='comites-elecciones/{$proceso['id_cliente']}/proceso/$idProceso'>📊 Ver proceso</a></li>";
            echo "<li><a href='votar/{$proceso['enlace_votacion']}'>🗳️ Enlace de votación</a></li>";
            echo "<li><a href='comites-elecciones/proceso/$idProceso/censo'>📋 Censo de votantes</a></li>";
            echo "</ul>";

            echo "<p><a href='reabrir_proceso.php'>← Volver a ver todos los procesos</a></p>";
        }
        echo "</div>";
    } else {
        echo "<div class='card'>";
        echo "<p class='info'>👆 Haz clic en 'Reabrir este' en la fila del proceso que deseas reabrir a estado <strong>votación</strong>.</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>
