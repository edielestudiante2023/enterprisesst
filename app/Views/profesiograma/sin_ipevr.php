<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:25px 30px;border-radius:15px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
.page-header h1{font-size:1.6rem;margin:0 0 4px;font-weight:700;}
.page-header .subtitulo{opacity:.85;font-size:.95rem;}
.block-card{background:#fff;border:none;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.1);text-align:center;padding:60px 40px;max-width:600px;margin:40px auto;}
.block-card .big-icon{font-size:5rem;color:#f59e0b;margin-bottom:20px;}
.block-card h2{font-size:1.4rem;color:var(--primary-dark);margin-bottom:15px;}
.block-card p{color:#666;font-size:1rem;line-height:1.6;}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;padding:10px 25px;border-radius:8px;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <div class="page-header">
    <h1><i class="fa-solid fa-stethoscope me-2"></i><?= esc($titulo) ?></h1>
    <div class="subtitulo"><?= esc($cliente['nombre_cliente']) ?> (ID <?= (int)$cliente['id_cliente'] ?>)</div>
  </div>

  <div class="block-card">
    <div class="big-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <h2>No es posible generar el Profesiograma</h2>
    <p>
      Primero debe completar y <strong>aprobar la Matriz IPEVR GTC 45</strong> de este cliente.
      El profesiograma se construye cruzando los peligros identificados en la IPEVR
      con los examenes medicos ocupacionales del catalogo.
    </p>
    <p class="text-muted mb-4" style="font-size:.9rem;">
      <i class="fa-solid fa-info-circle me-1"></i>
      La matriz debe estar en estado <strong>Vigente</strong> o <strong>Aprobada</strong>.
    </p>
    <a href="<?= base_url('ipevr/cliente/' . $cliente['id_cliente']) ?>" class="btn btn-gold">
      <i class="fa-solid fa-shield-halved me-1"></i> Ir a Matriz IPEVR
    </a>
  </div>
</div>
</body>
</html>
