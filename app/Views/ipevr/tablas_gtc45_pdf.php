<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:9pt;color:#222;}
h1{font-size:14pt;color:#1c2437;margin:0 0 8mm;text-align:center;}
h2{font-size:11pt;color:#1c2437;margin:8mm 0 3mm;border-bottom:2px solid #bd9751;padding-bottom:2mm;}
table{width:100%;border-collapse:collapse;margin-bottom:5mm;}
th{background:#1c2437;color:#fff;padding:4px 6px;text-align:center;font-size:8pt;}
td{border:1px solid #999;padding:4px 6px;font-size:8pt;vertical-align:top;}
.cod{font-weight:bold;text-align:center;width:50px;}
.val{text-align:center;font-weight:bold;width:40px;}
.nr-i{background:#c0392b;color:#fff;font-weight:bold;text-align:center;}
.nr-ii{background:#e67e22;color:#fff;font-weight:bold;text-align:center;}
.nr-iii{background:#f1c40f;color:#333;font-weight:bold;text-align:center;}
.nr-iv{background:#27ae60;color:#fff;font-weight:bold;text-align:center;}
</style></head><body>
<h1>TABLAS DE EVALUACIÓN DEL RIESGO — GTC 45</h1>

<h2>Tabla 2. Nivel de Deficiencia (ND)</h2>
<table><tr><th>Nivel</th><th>ND</th><th>Significado</th></tr>
<?php foreach($catalogo['nd'] as $n): ?>
<tr><td class="cod"><?=htmlspecialchars($n['nombre'])?></td><td class="val"><?=(int)$n['valor']?></td><td><?=htmlspecialchars($n['descripcion'])?></td></tr>
<?php endforeach;?></table>

<h2>Tabla 3. Nivel de Exposición (NE)</h2>
<table><tr><th>Nivel</th><th>NE</th><th>Significado</th></tr>
<?php foreach($catalogo['ne'] as $n): ?>
<tr><td class="cod"><?=htmlspecialchars($n['nombre'])?></td><td class="val"><?=(int)$n['valor']?></td><td><?=htmlspecialchars($n['descripcion'])?></td></tr>
<?php endforeach;?></table>

<h2>Tabla 5. Niveles de Probabilidad (NP = ND × NE)</h2>
<table><tr><th>Nivel</th><th>Rango</th><th>Significado</th></tr>
<?php foreach($catalogo['np'] as $n): ?>
<tr><td class="cod"><?=htmlspecialchars($n['nombre'])?></td><td class="val"><?=(int)$n['rango_min']?>–<?=(int)$n['rango_max']?></td><td><?=htmlspecialchars($n['descripcion'])?></td></tr>
<?php endforeach;?></table>

<h2>Tabla 6. Nivel de Consecuencias (NC)</h2>
<table><tr><th>Nivel</th><th>NC</th><th>Daños personales</th></tr>
<?php foreach($catalogo['nc'] as $n): ?>
<tr><td class="cod"><?=htmlspecialchars($n['nombre'])?></td><td class="val"><?=(int)$n['valor']?></td><td><?=htmlspecialchars($n['danos_personales'])?></td></tr>
<?php endforeach;?></table>

<h2>Tabla 7. Determinación del Nivel de Riesgo (NR = NP × NC)</h2>
<?php
function nrClassPdf($nr){if($nr>=600)return'nr-i';if($nr>=150)return'nr-ii';if($nr>=40)return'nr-iii';return'nr-iv';}
function nrLabelPdf($nr){if($nr>=600)return'I';if($nr>=150)return'II';if($nr>=40)return'III';return'IV';}
$ncVals=[['M',100],['MG',60],['G',25],['L',10]];
$npRanges=[[40,24],[20,10],[8,6],[4,2]];
?>
<table>
<tr><th>NC \ NP</th><th>MA (40-24)</th><th>A (20-10)</th><th>M (8-6)</th><th>B (4-2)</th></tr>
<?php foreach($ncVals as[$cod,$val]):?>
<tr><td class="cod"><?=$cod?> (<?=$val?>)</td>
<?php foreach($npRanges as[$hi,$lo]):$nrH=$hi*$val;$nrL=$lo*$val;?>
<td class="<?=nrClassPdf($nrH)?>"><?=nrLabelPdf($nrH)?> <?=number_format($nrH)?>–<?=number_format($nrL)?></td>
<?php endforeach;?></tr>
<?php endforeach;?></table>

<h2>Tabla 8. Significado del Nivel de Riesgo</h2>
<table><tr><th>Nivel</th><th>Valor NR</th><th>Significado</th></tr>
<?php foreach($catalogo['nr'] as $n): ?>
<tr><td class="<?=nrClassPdf((int)$n['rango_max'])?>"><?=htmlspecialchars($n['nombre'])?></td><td class="val"><?=number_format((int)$n['rango_min'])?>–<?=number_format((int)$n['rango_max'])?></td><td><?=htmlspecialchars($n['significado'])?></td></tr>
<?php endforeach;?></table>

<h2>Tabla 9. Aceptabilidad del Riesgo</h2>
<table><tr><th>Nivel</th><th>Significado</th></tr>
<?php foreach($catalogo['nr'] as $n): ?>
<tr><td class="<?=nrClassPdf((int)$n['rango_max'])?>"><?=htmlspecialchars($n['nombre'])?></td><td><?=htmlspecialchars($n['aceptabilidad'])?></td></tr>
<?php endforeach;?></table>

</body></html>
