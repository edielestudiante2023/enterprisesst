<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=empresas_sst;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Carpetas raíz del cliente 11
$anos = $pdo->query("SELECT id_carpeta, nombre FROM tbl_doc_carpetas WHERE id_cliente = 11 AND id_carpeta_padre IS NULL ORDER BY id_carpeta")->fetchAll(PDO::FETCH_ASSOC);
echo "Raíces cliente 11:\n";
foreach ($anos as $a) echo "  id={$a['id_carpeta']} | {$a['nombre']}\n";

// Carpetas 2.1.1 del cliente 11 con su año
$rows = $pdo->query("
    SELECT c.id_carpeta, c.codigo, c.orden, raiz.nombre AS raiz_nombre
    FROM tbl_doc_carpetas c
    JOIN tbl_doc_carpetas planear ON planear.id_carpeta = c.id_carpeta_padre
    JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = planear.id_carpeta_padre
    WHERE c.id_cliente = 11 AND c.codigo = '2.1.1'
    ORDER BY c.id_carpeta
")->fetchAll(PDO::FETCH_ASSOC);
echo "\nCarpetas 2.1.1 del cliente 11:\n";
foreach ($rows as $r) echo "  id={$r['id_carpeta']} orden={$r['orden']} año={$r['raiz_nombre']}\n";

// Carpetas 1.2.4 del cliente 11 con su año
$rows2 = $pdo->query("
    SELECT c.id_carpeta, c.codigo, c.orden, raiz.nombre AS raiz_nombre
    FROM tbl_doc_carpetas c
    JOIN tbl_doc_carpetas planear ON planear.id_carpeta = c.id_carpeta_padre
    JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = planear.id_carpeta_padre
    WHERE c.id_cliente = 11 AND c.codigo = '1.2.4'
    ORDER BY c.id_carpeta
")->fetchAll(PDO::FETCH_ASSOC);
echo "\nCarpetas 1.2.4 del cliente 11:\n";
foreach ($rows2 as $r) echo "  id={$r['id_carpeta']} orden={$r['orden']} año={$r['raiz_nombre']}\n";
