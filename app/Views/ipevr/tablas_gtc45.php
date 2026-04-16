<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary-dark:#1c2437;--gold-primary:#bd9751;}
body{background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);font-family:'Segoe UI',sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),#2c3e50,var(--gold-primary));color:#fff;padding:20px 25px;border-radius:12px;margin-bottom:20px;}
.page-header h1{font-size:1.4rem;margin:0;font-weight:700;}
.tabla-card{background:#fff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:20px;margin-bottom:20px;}
.tabla-card h5{color:var(--primary-dark);font-weight:700;margin-bottom:12px;border-bottom:2px solid var(--gold-primary);padding-bottom:6px;}
table.gtc{font-size:.85rem;border-collapse:collapse;width:100%;}
table.gtc th{background:var(--primary-dark);color:#fff;padding:8px 10px;text-align:center;font-weight:600;}
table.gtc td{border:1px solid #d1d5db;padding:8px 10px;vertical-align:top;}
table.gtc .codigo{font-weight:700;text-align:center;width:60px;}
table.gtc .valor{text-align:center;font-weight:700;width:50px;}
.nr-i{background:#c0392b;color:#fff;font-weight:700;text-align:center;}
.nr-ii{background:#e67e22;color:#fff;font-weight:700;text-align:center;}
.nr-iii{background:#f1c40f;color:#333;font-weight:700;text-align:center;}
.nr-iv{background:#27ae60;color:#fff;font-weight:700;text-align:center;}
.matriz-cruzada th{font-size:.75rem;}
.matriz-cruzada td{text-align:center;font-size:.75rem;font-weight:700;padding:6px;}
</style>
</head>
<body>
<div class="container py-4">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1><i class="fa-solid fa-table-list me-2"></i><?= esc($titulo) ?></h1>
      <small class="opacity-75">GTC 45 (Primera actualización) — Guía para la identificación de peligros y valoración de riesgos</small>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('ipevr/tablas-gtc45/xlsx') ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Descargar Excel</a>
      <a href="<?= base_url('ipevr/tablas-gtc45/pdf') ?>" target="_blank" class="btn btn-danger btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>Descargar PDF</a>
      <a href="https://posipedia.com.co/wp-content/uploads/2021/04/15-MARZO-.-MATERIAL-DE-APOYO-PREVENCIO%CC%81N-DE-PELIGROS-EN-EL-ADMINISTRACIO%CC%81N-PUBLICA-GENERALIDADES.pdf" target="_blank" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-book me-1"></i>Norma completa</a>
    </div>
  </div>

  <!-- TABLA ND -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-gauge-high me-2"></i>Tabla 2. Determinación del Nivel de Deficiencia (ND)</h5>
    <table class="gtc">
      <thead><tr><th>Nivel</th><th>ND</th><th>Significado</th></tr></thead>
      <tbody>
      <?php foreach ($catalogo['nd'] as $n): ?>
        <tr><td class="codigo"><?= esc($n['nombre']) ?></td><td class="valor"><?= (int)$n['valor'] ?></td><td><?= esc($n['descripcion']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TABLA NE -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-clock me-2"></i>Tabla 3. Determinación del Nivel de Exposición (NE)</h5>
    <table class="gtc">
      <thead><tr><th>Nivel</th><th>NE</th><th>Significado</th></tr></thead>
      <tbody>
      <?php foreach ($catalogo['ne'] as $n): ?>
        <tr><td class="codigo"><?= esc($n['nombre']) ?></td><td class="valor"><?= (int)$n['valor'] ?></td><td><?= esc($n['descripcion']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TABLA NP -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-chart-bar me-2"></i>Tabla 5. Significado de los diferentes niveles de probabilidad (NP = ND × NE)</h5>
    <table class="gtc">
      <thead><tr><th>Nivel</th><th>Rango NP</th><th>Significado</th></tr></thead>
      <tbody>
      <?php foreach ($catalogo['np'] as $n): ?>
        <tr><td class="codigo"><?= esc($n['nombre']) ?></td><td class="valor"><?= (int)$n['rango_min'] ?> – <?= (int)$n['rango_max'] ?></td><td><?= esc($n['descripcion']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TABLA NC -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-heart-pulse me-2"></i>Tabla 6. Determinación del Nivel de Consecuencias (NC)</h5>
    <table class="gtc">
      <thead><tr><th>Nivel</th><th>NC</th><th>Daños personales</th></tr></thead>
      <tbody>
      <?php foreach ($catalogo['nc'] as $n): ?>
        <tr><td class="codigo"><?= esc($n['nombre']) ?></td><td class="valor"><?= (int)$n['valor'] ?></td><td><?= esc($n['danos_personales']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TABLA 7: MATRIZ CRUZADA NP x NC -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-table-cells me-2"></i>Tabla 7. Determinación del Nivel de Riesgo (NR = NP × NC)</h5>
    <?php
      $npVals = [['MA',40],['A',20],['M',8],['B',4]]; // valores representativos altos
      $npVals2 = [['MA',24],['A',10],['M',6],['B',2]]; // valores representativos bajos
      $ncVals = [['M',100],['MG',60],['G',25],['L',10]];

      function nrClass($nr) {
        if ($nr >= 600) return 'nr-i';
        if ($nr >= 150) return 'nr-ii';
        if ($nr >= 40) return 'nr-iii';
        return 'nr-iv';
      }
      function nrLabel($nr) {
        if ($nr >= 600) return 'I';
        if ($nr >= 150) return 'II';
        if ($nr >= 40) return 'III';
        return 'IV';
      }
    ?>
    <table class="gtc matriz-cruzada">
      <thead>
        <tr>
          <th rowspan="2">NC \ NP</th>
          <th colspan="2">Muy Alto (MA)<br>40–24</th>
          <th colspan="2">Alto (A)<br>20–10</th>
          <th colspan="2">Medio (M)<br>8–6</th>
          <th colspan="2">Bajo (B)<br>4–2</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($ncVals as [$ncCod,$ncVal]): ?>
        <tr>
          <td class="codigo"><?= $ncCod ?> (<?= $ncVal ?>)</td>
          <?php foreach ([[40,24],[20,10],[8,6],[4,2]] as [$hi,$lo]): ?>
            <?php $nrHi = $hi*$ncVal; $nrLo = $lo*$ncVal; ?>
            <td colspan="2" class="<?= nrClass($nrHi) ?>">
              <?= nrLabel($nrHi) ?><br><?= number_format($nrHi) ?>–<?= number_format($nrLo) ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TABLA 8: SIGNIFICADO -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-triangle-exclamation me-2"></i>Tabla 8. Significado del Nivel de Riesgo</h5>
    <table class="gtc">
      <thead><tr><th>Nivel</th><th>Valor NR</th><th>Significado</th></tr></thead>
      <tbody>
      <?php foreach ($catalogo['nr'] as $n): ?>
        <tr>
          <td class="<?= nrClass((int)$n['rango_max']) ?>" style="width:60px"><?= esc($n['nombre']) ?></td>
          <td class="valor"><?= number_format((int)$n['rango_min']) ?> – <?= number_format((int)$n['rango_max']) ?></td>
          <td><?= esc($n['significado']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TABLA 9: ACEPTABILIDAD -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-check-double me-2"></i>Tabla 9. Aceptabilidad del Riesgo</h5>
    <table class="gtc">
      <thead><tr><th>Nivel de Riesgo</th><th>Significado</th></tr></thead>
      <tbody>
      <?php foreach ($catalogo['nr'] as $n): ?>
        <tr>
          <td class="<?= nrClass((int)$n['rango_max']) ?>" style="width:80px"><?= esc($n['nombre']) ?></td>
          <td><?= esc($n['aceptabilidad']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- CLASIFICACIONES -->
  <div class="tabla-card">
    <h5><i class="fa-solid fa-tags me-2"></i>Clasificación de Peligros GTC 45 (7 categorías)</h5>
    <div class="row g-3">
    <?php foreach ($catalogo['clasificaciones'] as $cl): ?>
      <div class="col-md-4 col-lg-3">
        <div class="border rounded p-2">
          <strong><?= esc($cl['nombre']) ?></strong>
          <ul class="small mb-0 mt-1" style="padding-left:16px">
          <?php foreach ($catalogo['peligros'] as $p):
            if ((int)$p['id_clasificacion'] === (int)$cl['id']): ?>
              <li><?= esc($p['nombre']) ?></li>
          <?php endif; endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
