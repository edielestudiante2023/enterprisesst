<?php
$m = mysqli_init();
mysqli_ssl_set($m, null, null, null, null, null);
mysqli_real_connect($m,'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','cycloid_userdb',getenv('DB_PROD_PASS'),'empresas_sst',25060,null,MYSQLI_CLIENT_SSL);

$r = mysqli_query($m, 'DESCRIBE tbl_actas');
while ($row = mysqli_fetch_assoc($r)) echo $row['Field'] . PHP_EOL;
